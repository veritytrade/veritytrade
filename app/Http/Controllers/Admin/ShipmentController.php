<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CustomerShipmentLogisticsUpdate;
use App\Models\Shipment;
use App\Models\TrackingStage;
use App\Models\User;
use App\Services\SkyCargoLogisticsService;
use App\Support\CarrierTrackTimestamp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shipment::withCount('orders')
            ->with('currentStage')
            ->where('status', '!=', 'completed')
            ->latest('id');

        if ($logistics = trim((string) $request->query('logistics'))) {
            $query->where('logistics_company', 'like', '%' . $logistics . '%');
        }

        $shipments = $query->paginate(15)->withQueryString();

        return view('admin.shipments.index', compact('shipments'));
    }

    public function create(): View
    {
        $stages = TrackingStage::orderBy('position')->get();
        return view('admin.shipments.create', compact('stages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'chinese_tracking_code' => 'required|string|max:255',
            'logistics_company' => 'required|in:skycargo,fish-logistics,other',
            'current_stage_id' => 'nullable|exists:tracking_stages,id',
        ]);

        $valid['created_by'] = auth()->id();
        $valid['updated_by'] = auth()->id();
        $valid['status'] = 'active';
        if (empty($valid['current_stage_id'])) {
            $valid['current_stage_id'] = TrackingStage::where('name', 'Processing')->value('id');
        }
        Shipment::create($valid);

        return redirect()->route('admin.shipments.index')->with('success', 'Shipment created.');
    }

    public function show(Shipment $shipment): View
    {
        $shipment->load(['orders.user', 'orders.invoice', 'orders.currentStageOverride', 'currentStage']);
        foreach ($shipment->orders as $order) {
            $order->setRelation('shipment', $shipment);
        }
        $stages = TrackingStage::orderBy('position')->get();

        return view('admin.shipments.show', compact('shipment', 'stages'));
    }

    public function edit(Shipment $shipment): View
    {
        $stages = TrackingStage::orderBy('position')->get();
        return view('admin.shipments.edit', compact('shipment', 'stages'));
    }

    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $valid = $request->validate([
            'chinese_tracking_code' => 'required|string|max:255',
            'logistics_company' => 'required|in:skycargo,fish-logistics,other',
            'status' => 'required|in:active,completed',
            'waybill_outstanding_ngn' => 'nullable|numeric|min:0|max:10000',
        ]);

        $valid['updated_by'] = auth()->id();
        $valid['waybill_outstanding_ngn'] = $request->filled('waybill_outstanding_ngn')
            ? (float) $valid['waybill_outstanding_ngn']
            : null;
        $shipment->update($valid);

        return redirect()->route('admin.shipments.show', $shipment)->with('success', 'Shipment updated.');
    }

    public function updateStage(Request $request, Shipment $shipment): RedirectResponse
    {
        $valid = $request->validate([
            'current_stage_id' => 'required|exists:tracking_stages,id',
        ]);

        $previousStage = $shipment->currentStage;
        $stage = TrackingStage::find($valid['current_stage_id']);
        $shipment->update([
            'current_stage_id' => $valid['current_stage_id'],
            'updated_by' => auth()->id(),
        ]);
        $shipment->setRelation('currentStage', $stage);

        if ($stage && $stage->name === 'Delivered') {
            $shipment->orders()->whereNull('current_stage_id')->update(['status' => 'delivered']);
        }
        $this->notifyCustomersOnStageChange($shipment, $previousStage, $stage);

        return back()->with('success', 'Shipment stage updated.');
    }

    public function applyStageToAllOrders(Request $request, Shipment $shipment): RedirectResponse
    {
        $valid = $request->validate([
            'current_stage_id' => 'required|exists:tracking_stages,id',
        ]);

        $previousStage = $shipment->currentStage;
        $stage = TrackingStage::find($valid['current_stage_id']);
        $shipment->orders()->update(['current_stage_id' => null]);
        $shipment->update([
            'current_stage_id' => $valid['current_stage_id'],
            'updated_by' => auth()->id(),
        ]);
        $shipment->setRelation('currentStage', $stage);

        if ($stage && $stage->name === 'Delivered') {
            $shipment->orders()->update(['status' => 'delivered']);
        }
        $this->notifyCustomersOnStageChange($shipment, $previousStage, $stage);

        return back()->with('success', 'Stage applied to all orders.');
    }

    public function refreshCarrierTracking(Shipment $shipment, SkyCargoLogisticsService $skyCargo): RedirectResponse
    {
        $code = trim((string) $shipment->chinese_tracking_code);
        if ($code === '') {
            return back()->with('error', 'Set a Chinese tracking code before refreshing carrier data.');
        }

        $provider = strtolower(trim((string) $shipment->logistics_company));
        if ($provider === 'other') {
            return back()->with('error', 'Carrier refresh is available only for SkyCargo and Fish Logistics.');
        }

        $result = $skyCargo->fetchTracks($provider, $code);
        if ($result === null) {
            return back()->with('error', 'Could not reach carrier or invalid response. Check the tracking code or try again later.');
        }

        $previousPayload = is_array($shipment->carrier_tracks_json) ? $shipment->carrier_tracks_json : [];
        $previousTracks = $previousPayload['tracks'] ?? [];
        if (! is_array($previousTracks)) {
            $previousTracks = [];
        }

        $tracks = $result['tracks'] ?? [];
        if (! is_array($tracks)) {
            $tracks = [];
        }
        self::sortCarrierTracksNewestFirst($tracks);

        $count = count($tracks);
        $shipment->update([
            'carrier_tracks_json' => [
                'tracks' => $tracks,
                'fetched_at' => now()->toIso8601String(),
            ],
            'carrier_tracks_synced_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $msg = $count > 0
            ? "Carrier tracking refreshed ({$count} updates stored)."
            : 'Carrier responded; no tracking events were returned for this code.';

        $this->notifyCustomersOnCarrierRowsAdded($shipment, $previousTracks, $tracks);

        return back()->with('success', $msg);
    }

    public function markAgentCollected(Shipment $shipment): RedirectResponse
    {
        $payload = is_array($shipment->carrier_tracks_json) ? $shipment->carrier_tracks_json : [];
        $tracks = $payload['tracks'] ?? [];
        if (! is_array($tracks)) {
            $tracks = [];
        }

        $markerText = 'Package collected by Verity agent from logistics warehouse';
        $alreadyMarked = collect($tracks)->contains(function ($track) use ($markerText) {
            if (! is_array($track)) {
                return false;
            }
            return str_contains(strtolower((string) ($track['en'] ?? '')), strtolower($markerText));
        });

        if (! $alreadyMarked) {
            $tracks[] = [
                'en' => $markerText,
                'cn' => '',
                'at' => now()->format('Y-m-d H:i:s'),
                'meta' => [
                    'stage_hint' => 'arrived_nigeria',
                    'subsection' => 'Agent pickup',
                    'source' => 'admin',
                ],
            ];
        }

        self::sortCarrierTracksNewestFirst($tracks);

        $payload['tracks'] = $tracks;
        $payload['fetched_at'] = now()->toIso8601String();

        $shipment->update([
            'carrier_tracks_json' => $payload,
            'carrier_tracks_synced_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Marked: package collected by agent. Customer timeline updated.');
    }

    /**
     * @param  array<int, mixed>  $tracks
     */
    private static function sortCarrierTracksNewestFirst(array &$tracks): void
    {
        $tracks = array_values(array_filter($tracks, static fn ($row) => is_array($row)));
        usort($tracks, [CarrierTrackTimestamp::class, 'compareTracksNewestFirst']);
    }

    /**
     * Only notify while shipment is still in transit and before "Sent to Final Destination".
     */
    private function canSendTransitUpdateEmails(Shipment $shipment): bool
    {
        if (! feature_enabled('enable_logistics_update_emails', true)) {
            return false;
        }

        if (strtolower((string) $shipment->status) === 'completed') {
            return false;
        }

        $dispatchedPos = (int) (TrackingStage::where('name', 'Sent to Final Destination')->value('position') ?? 6);
        $sentPos = (int) (TrackingStage::where('name', 'Sent to Logistics')->value('position') ?? 2);
        $currentPos = (int) optional($shipment->currentStage)->position;
        if ($currentPos <= 0) {
            return false;
        }

        return $currentPos >= $sentPos && $currentPos < $dispatchedPos;
    }

    /**
     * @return array<int, \App\Models\User>
     */
    private function shipmentEmailRecipients(Shipment $shipment): array
    {
        $orders = $shipment->orders()->with('user')->get();
        $users = [];
        foreach ($orders as $order) {
            if (strtolower((string) $order->status) === 'delivered') {
                continue;
            }
            $user = $order->user;
            if (! $user) {
                continue;
            }
            $email = trim((string) $user->email);
            if ($email === '') {
                continue;
            }
            $users[$user->id] = $user;
        }

        return array_values($users);
    }

    private function orderSummaryLine(Shipment $shipment, User $user): ?string
    {
        try {
            $shipment->loadMissing('orders');
            $order = $shipment->orders->firstWhere('user_id', $user->id);
            if (! $order) {
                return null;
            }
            $name = trim((string) ($order->product_name ?? ''));
            if ($name !== '') {
                return $name;
            }

            return 'Order #'.$order->id;
        } catch (\Throwable) {
            return null;
        }
    }

    private function notifyCustomersOnStageChange(Shipment $shipment, ?TrackingStage $previousStage, ?TrackingStage $newStage): void
    {
        if (! $newStage || ! $this->canSendTransitUpdateEmails($shipment)) {
            return;
        }
        if ((int) ($previousStage?->id ?? 0) === (int) $newStage->id) {
            return;
        }

        $recipients = $this->shipmentEmailRecipients($shipment);
        if ($recipients === []) {
            return;
        }

        $from = mail_from();
        $trackingUrl = route('dashboard.tracking');
        $appName = (string) config('app.name', 'VerityTrade');
        foreach ($recipients as $user) {
            try {
                $recipientName = trim((string) ($user->name ?? ''));
                if ($recipientName === '') {
                    $recipientName = 'Customer';
                }
                Mail::to($user->email)->send(new CustomerShipmentLogisticsUpdate(
                    kind: 'stage',
                    recipientName: $recipientName,
                    newStageName: $newStage->name,
                    previousStageName: $previousStage?->name,
                    latestLine: null,
                    latestAt: null,
                    trackingUrl: $trackingUrl,
                    appName: $appName,
                    fromMailbox: $from,
                    orderSummary: $this->orderSummaryLine($shipment, $user),
                ));
            } catch (\Throwable $e) {
                Log::error('Failed to send shipment stage update email.', [
                    'shipment_id' => $shipment->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<int, mixed>  $oldTracks
     * @param  array<int, mixed>  $newTracks
     */
    private function notifyCustomersOnCarrierRowsAdded(Shipment $shipment, array $oldTracks, array $newTracks): void
    {
        if (! $this->canSendTransitUpdateEmails($shipment)) {
            return;
        }

        $oldNorm = array_values(array_filter($oldTracks, static fn ($row) => is_array($row)));
        $newNorm = array_values(array_filter($newTracks, static fn ($row) => is_array($row)));
        if (! $this->hasNewCarrierRows($oldNorm, $newNorm)) {
            return;
        }

        $recipients = $this->shipmentEmailRecipients($shipment);
        if ($recipients === []) {
            return;
        }

        $latest = $newNorm[0] ?? [];
        $latestLine = trim((string) ($latest['en'] ?? $latest['cn'] ?? ''));
        if ($latestLine === '') {
            $latestLine = 'New logistics activity was recorded.';
        }
        $latestAt = trim((string) ($latest['at'] ?? ''));
        $trackingUrl = route('dashboard.tracking');
        $from = mail_from();
        $appName = (string) config('app.name', 'VerityTrade');

        foreach ($recipients as $user) {
            try {
                $recipientName = trim((string) ($user->name ?? ''));
                if ($recipientName === '') {
                    $recipientName = 'Customer';
                }
                Mail::to($user->email)->send(new CustomerShipmentLogisticsUpdate(
                    kind: 'carrier',
                    recipientName: $recipientName,
                    newStageName: null,
                    previousStageName: null,
                    latestLine: $latestLine,
                    latestAt: $latestAt !== '' ? $latestAt : null,
                    trackingUrl: $trackingUrl,
                    appName: $appName,
                    fromMailbox: $from,
                    orderSummary: $this->orderSummaryLine($shipment, $user),
                ));
            } catch (\Throwable $e) {
                Log::error('Failed to send logistics row update email.', [
                    'shipment_id' => $shipment->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $oldTracks
     * @param  array<int, array<string, mixed>>  $newTracks
     */
    private function hasNewCarrierRows(array $oldTracks, array $newTracks): bool
    {
        if (count($newTracks) > count($oldTracks)) {
            return true;
        }
        if ($newTracks === []) {
            return false;
        }

        $fingerprint = static function (array $row): string {
            $at = trim((string) ($row['at'] ?? ''));
            $en = trim((string) ($row['en'] ?? ''));
            $cn = trim((string) ($row['cn'] ?? ''));
            return strtolower($at.'|'.$en.'|'.$cn);
        };

        $oldSet = [];
        foreach ($oldTracks as $row) {
            $oldSet[$fingerprint($row)] = true;
        }
        foreach ($newTracks as $row) {
            if (! isset($oldSet[$fingerprint($row)])) {
                return true;
            }
        }

        return false;
    }
}
