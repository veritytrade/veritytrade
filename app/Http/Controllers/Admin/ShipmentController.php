<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\TrackingStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $shipments = Shipment::withCount('orders')
            ->with('currentStage')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

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
            'logistics_company' => 'required|string|max:255',
            'current_stage_id' => 'nullable|exists:tracking_stages,id',
        ]);

        $valid['created_by'] = auth()->id();
        $valid['updated_by'] = auth()->id();
        $valid['status'] = 'active';
        Shipment::create($valid);

        return redirect()->route('admin.shipments.index')->with('success', 'Shipment created.');
    }

    public function show(Shipment $shipment): View
    {
        $shipment->load(['orders.user', 'orders.invoice', 'orders.currentStageOverride', 'currentStage']);
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
            'logistics_company' => 'required|string|max:255',
            'current_stage_id' => 'nullable|exists:tracking_stages,id',
            'status' => 'required|in:active,completed',
            'waybill_outstanding_ngn' => 'nullable|numeric|min:0|max:10000',
        ]);

        $valid['updated_by'] = auth()->id();
        $valid['waybill_outstanding_ngn'] = $request->filled('waybill_outstanding_ngn')
            ? (float) $valid['waybill_outstanding_ngn']
            : null;
        $shipment->update($valid);

        $stage = $shipment->currentStage;
        if ($stage && (int) $stage->position === 6) {
            $shipment->orders()->whereNull('current_stage_id')->update(['status' => 'delivered']);
        }

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

        if ($stage && (int) $stage->position === 6) {
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

        if ($stage && (int) $stage->position === 6) {
            $shipment->orders()->update(['status' => 'delivered']);
        }

        return back()->with('success', 'Stage applied to all orders.');
    }
}
