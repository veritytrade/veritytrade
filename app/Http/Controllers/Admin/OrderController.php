<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPaymentSlip;
use App\Models\Shipment;
use App\Services\InvoiceService;
use App\Models\TrackingStage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['user', 'shipment', 'invoice', 'currentStageOverride'])->latest('id');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $customers = User::whereHas('role', fn ($q) => $q->where('name', 'customer'))
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
        $shipments = Shipment::where('status', 'active')->orderBy('id', 'desc')->get();
        return view('admin.orders.create', compact('customers', 'shipments'));
    }

    public function store(Request $request): RedirectResponse
    {
        $description = $request->input('gadget_description', '');
        $parsed = Order::parseDescription($description);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'gadget_description' => 'required|string|max:5000',
            'outstanding_balance_ngn' => 'nullable|numeric|min:0',
            'logistics_type' => 'nullable|string|in:within_lagos,outside_lagos,combined',
            'shipment_id' => 'nullable|exists:shipments,id',
            'payment_slips' => 'nullable|array|max:5',
            'payment_slips.*' => [File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(5 * 1024)],
        ];

        if (! $parsed['has_price_in_description']) {
            $rules['total_amount_ngn'] = 'required|numeric|min:0';
        } else {
            $rules['total_amount_ngn'] = 'nullable|numeric|min:0';
        }

        $valid = $request->validate($rules, [
            'payment_slips.max' => 'Maximum 5 images or PDFs allowed.',
            'payment_slips.*.max' => 'Each slip must not exceed 5MB.',
        ]);

        $totalAmount = $parsed['has_price_in_description']
            ? ($parsed['price_ngn'] ?? (float) ($valid['total_amount_ngn'] ?? 0))
            : (float) ($valid['total_amount_ngn'] ?? 0);

        $productName = $parsed['product_name'] ?: trim(explode("\n", $description)[0] ?? '');
        if ($productName === '') {
            $productName = 'Order';
        }

        $outstanding = (float) ($valid['outstanding_balance_ngn'] ?? 0);
        $paidAmount = $totalAmount - $outstanding;
        $paymentStatus = $outstanding <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');

        $shipment = !empty($valid['shipment_id']) ? Shipment::with('currentStage')->find($valid['shipment_id']) : null;
        $status = $shipment
            ? Order::deriveStatusFromStage($valid['shipment_id'], null, $shipment)
            : 'processing';

        $order = Order::create([
            'user_id' => $valid['user_id'],
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'product_name' => $productName,
            'spec_summary' => null,
            'full_description' => $description,
            'total_amount_ngn' => $totalAmount,
            'outstanding_balance_ngn' => $outstanding,
            'payment_status' => $paymentStatus,
            'logistics_type' => $valid['logistics_type'] ?? 'within_lagos',
            'status' => $status,
            'shipment_id' => $valid['shipment_id'] ?? null,
            'current_stage_id' => null,
        ]);

        $slips = $request->file('payment_slips');
        if (is_array($slips)) {
            $slips = array_slice(array_filter($slips), 0, 5);
            $dir = 'order_payment_slips/' . $order->id;
            foreach ($slips as $i => $file) {
                $path = $file->store($dir, 'public');
                OrderPaymentSlip::create([
                    'order_id' => $order->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('admin.orders.index')->with('success', 'Order created. Verity code auto-generated.');
    }

    public function show(Order $order, InvoiceService $invoiceService): View
    {
        $order->load(['user', 'shipment', 'currentStageOverride', 'shipment.currentStage', 'paymentSlips', 'invoiceRequests', 'invoice']);
        $shipments = Shipment::where('status', 'active')->orderBy('id', 'desc')->get();
        $stages = TrackingStage::orderBy('position')->get();
        $uninvoicedOrders = $order->shipment_id
            ? $invoiceService->getUninvoicedOrdersForShipment($order->shipment_id, $order->user_id)
            : collect();
        return view('admin.orders.show', compact('order', 'shipments', 'stages', 'uninvoicedOrders'));
    }

    public function edit(Order $order): View
    {
        $order->load(['user', 'shipment', 'currentStageOverride', 'paymentSlips']);
        $users = User::orderBy('name')->get();
        $shipments = Shipment::where('status', 'active')->orderBy('id', 'desc')->get();
        $stages = TrackingStage::orderBy('position')->get();
        return view('admin.orders.edit', compact('order', 'users', 'shipments', 'stages'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $valid = $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_name' => 'required|string|max:255',
            'spec_summary' => 'nullable|string|max:500',
            'full_description' => 'nullable|string|max:5000',
            'total_amount_ngn' => 'required|numeric|min:0',
            'outstanding_balance_ngn' => 'nullable|numeric|min:0',
            'logistics_type' => 'nullable|string|in:within_lagos,outside_lagos,combined',
            'status' => 'required|string|in:pending,pending_approval,processing,shipped,delivered,cancelled',
            'shipment_id' => 'nullable|exists:shipments,id',
            'current_stage_id' => 'nullable|exists:tracking_stages,id',
            'payment_slips' => 'nullable|array|max:5',
            'payment_slips.*' => [File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(5 * 1024)],
        ], [
            'payment_slips.max' => 'Maximum 5 images or PDFs allowed.',
            'payment_slips.*.max' => 'Each slip must not exceed 5MB.',
        ]);

        if (array_key_exists('current_stage_id', $valid) && $valid['current_stage_id'] === '') {
            $valid['current_stage_id'] = null;
        }
        if (array_key_exists('shipment_id', $valid) && $valid['shipment_id'] === '') {
            $valid['shipment_id'] = null;
        }

        if (!empty($valid['shipment_id']) || !empty($valid['current_stage_id'])) {
            $shipment = !empty($valid['shipment_id']) ? Shipment::with('currentStage')->find($valid['shipment_id']) : null;
            $valid['status'] = Order::deriveStatusFromStage(
                $valid['shipment_id'] ?? null,
                $valid['current_stage_id'] ?? null,
                $shipment
            );
        } else {
            $valid['status'] = in_array($valid['status'] ?? 'processing', ['shipped', 'delivered'])
                ? 'processing'
                : ($valid['status'] ?? 'processing');
        }

        $valid['outstanding_balance_ngn'] = (float) ($valid['outstanding_balance_ngn'] ?? 0);
        $valid['logistics_type'] = $valid['logistics_type'] ?? 'within_lagos';
        $totalAmount = (float) ($valid['total_amount_ngn'] ?? 0);
        $outstanding = $valid['outstanding_balance_ngn'];
        $paidAmount = $totalAmount - $outstanding;
        $valid['payment_status'] = $outstanding <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');
        $order->update($valid);

        $slips = $request->file('payment_slips');
        if (is_array($slips) && !empty($slips)) {
            $existing = $order->paymentSlips()->count();
            $remaining = 5 - $existing;
            $slips = array_slice(array_filter($slips), 0, $remaining);
            $dir = 'order_payment_slips/' . $order->id;
            $sortStart = $order->paymentSlips()->max('sort_order') + 1;
            foreach ($slips as $i => $file) {
                $path = $file->store($dir, 'public');
                OrderPaymentSlip::create([
                    'order_id' => $order->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'sort_order' => $sortStart + $i,
                ]);
            }
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated.');
    }

    public function approve(Order $order): RedirectResponse
    {
        if ($order->status !== 'pending_approval') {
            return back()->with('error', 'Only pending approval orders can be approved.');
        }
        $order->update(['status' => 'processing']);
        return back()->with('success', 'Order approved. You can now assign it to a shipment.');
    }

    public function generateInvoice(Order $order, InvoiceService $invoiceService): RedirectResponse
    {
        if (! $order->shipment_id) {
            return back()->with('error', 'Order must be assigned to a shipment before generating invoice.');
        }
        $orders = $invoiceService->getUninvoicedOrdersForShipment($order->shipment_id, $order->user_id);
        $regenerated = false;
        if ($orders->isEmpty()) {
            $existing = $invoiceService->getExistingInvoiceForShipmentAndUser($order->shipment_id, $order->user_id);
            if (! $existing) {
                return back()->with('status', 'Invoice already generated for this shipment.');
            }
            $orders = Order::where('invoice_id', $existing->id)->orderBy('id')->get();
            if ($orders->isEmpty()) {
                return back()->with('error', 'Existing invoice has no orders.');
            }
            $regenerated = true;
        }
        try {
            $invoice = $invoiceService->generateForOrders($orders, auth()->id());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
        $message = $regenerated
            ? 'Invoice ' . $invoice->invoice_number . ' regenerated. PDF updated; customer can download the new file.'
            : 'Invoice ' . $invoice->invoice_number . ' generated for ' . $orders->count() . ' item(s). Customer can download from their dashboard.';
        return back()->with('success', $message);
    }

    public function assignShipment(Request $request, Order $order): RedirectResponse
    {
        if ($order->status === 'pending_approval') {
            return back()->with('error', 'Approve the order first before assigning to a shipment.');
        }
        $valid = $request->validate([
            'shipment_id' => 'nullable|exists:shipments,id',
        ]);

        $shipment = isset($valid['shipment_id']) ? Shipment::find($valid['shipment_id']) : null;
        $order->assignToShipment($shipment);

        return back()->with('success', 'Shipment assignment updated.');
    }

    public function overrideStage(Request $request, Order $order): RedirectResponse
    {
        if ($order->status === 'pending_approval') {
            return back()->with('error', 'Approve the order first before overriding stage.');
        }
        $valid = $request->validate([
            'current_stage_id' => 'nullable|exists:tracking_stages,id',
        ]);

        $order->update([
            'current_stage_id' => $valid['current_stage_id'] ?: null,
        ]);
        $order->syncStatusFromStage();

        return back()->with('success', 'Order stage override updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        foreach ($order->paymentSlips as $slip) {
            if (Storage::disk('public')->exists($slip->file_path)) {
                Storage::disk('public')->delete($slip->file_path);
            }
        }
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}
