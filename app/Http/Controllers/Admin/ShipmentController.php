<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\TrackingStage;
use App\Services\SkyCargoLogisticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $stage = TrackingStage::find($valid['current_stage_id']);
        $shipment->update([
            'current_stage_id' => $valid['current_stage_id'],
            'updated_by' => auth()->id(),
        ]);

        if ($stage && $stage->name === 'Delivered') {
            $shipment->orders()->whereNull('current_stage_id')->update(['status' => 'delivered']);
        }

        return back()->with('success', 'Shipment stage updated.');
    }

    public function applyStageToAllOrders(Request $request, Shipment $shipment): RedirectResponse
    {
        $valid = $request->validate([
            'current_stage_id' => 'required|exists:tracking_stages,id',
        ]);

        $stage = TrackingStage::find($valid['current_stage_id']);
        $shipment->orders()->update(['current_stage_id' => null]);
        $shipment->update([
            'current_stage_id' => $valid['current_stage_id'],
            'updated_by' => auth()->id(),
        ]);

        if ($stage && $stage->name === 'Delivered') {
            $shipment->orders()->update(['status' => 'delivered']);
        }

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

        $count = count($result['tracks'] ?? []);
        $shipment->update([
            'carrier_tracks_json' => [
                'tracks' => $result['tracks'] ?? [],
                'fetched_at' => now()->toIso8601String(),
            ],
            'carrier_tracks_synced_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $msg = $count > 0
            ? "Carrier tracking refreshed ({$count} updates stored)."
            : 'Carrier responded; no tracking events were returned for this code.';

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

        $payload['tracks'] = $tracks;
        $payload['fetched_at'] = now()->toIso8601String();

        $shipment->update([
            'carrier_tracks_json' => $payload,
            'carrier_tracks_synced_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Marked: package collected by agent. Customer timeline updated.');
    }
}
