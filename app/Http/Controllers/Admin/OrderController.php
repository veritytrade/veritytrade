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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $queue = trim((string) $request->query('queue', 'operations'));
        if (! in_array($queue, ['sourcing', 'operations'], true)) {
            $queue = 'operations';
        }

        $query = Order::with(['user', 'shipment', 'invoice', 'currentStageOverride'])
            ->where('status', '!=', 'delivered')
            ->orderByRaw('shipment_id IS NULL DESC')
            ->latest('id');

        if ($queue === 'sourcing') {
            // Sourcing queue: orders that still need supplier mapping and are not in shipment flow yet.
            $query->whereNull('shipment_id')
                ->where(function ($q): void {
                    $q->whereNull('supplier_order_number')
                        ->orWhere('supplier_order_number', '')
                        ->orWhereNull('supplier_logistics_code')
                        ->orWhere('supplier_logistics_code', '');
                });
        } else {
            // Operations queue: legacy shipment-flow orders OR fully mapped supplier orders.
            $query->where(function ($q): void {
                $q->whereNotNull('shipment_id')
                    ->orWhere(function ($mapped): void {
                        $mapped->whereNotNull('supplier_order_number')
                            ->where('supplier_order_number', '!=', '')
                            ->whereNotNull('supplier_logistics_code')
                            ->where('supplier_logistics_code', '!=' , '');
                    });
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($customer = trim((string) $request->query('customer'))) {
            $query->whereHas('user', function ($q) use ($customer): void {
                $q->where('email', 'like', '%' . $customer . '%')
                    ->orWhere('username', 'like', '%' . $customer . '%')
                    ->orWhere('name', 'like', '%' . $customer . '%');
            });
        }

        if ($logistics = trim((string) $request->query('logistics'))) {
            $query->whereHas('shipment', function ($q) use ($logistics): void {
                $q->where('logistics_company', 'like', '%' . $logistics . '%');
            });
        }

        if ($request->boolean('unassigned')) {
            $query->whereNull('shipment_id');
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders', 'queue'));
    }

    public function updateSupplierMapping(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_logistics_code' => ['required', 'string', 'max:120', Rule::unique('orders', 'supplier_logistics_code')->ignore($order->id)],
            'return_url' => ['nullable', 'string', 'max:2000'],
        ]);

        $order->update([
            'supplier_logistics_code' => trim((string) $validated['supplier_logistics_code']),
            'mapping_status' => 'mapped',
            'mapped_at' => now(),
            'mapped_by' => auth()->id(),
        ]);

        $returnUrl = (string) ($validated['return_url'] ?? '');
        if ($returnUrl !== '' && Str::startsWith($returnUrl, [url('/admin/orders'), '/admin/orders'])) {
            return redirect($returnUrl)
                ->with('success', 'Supplier logistics code saved.')
                ->with('highlight_order_id', $order->id);
        }

        return back()->with('success', 'Supplier logistics code saved.')
            ->with('highlight_order_id', $order->id);
    }

    public function create(): View
    {
        $customers = User::whereHas('role', fn ($q) => $q->where('name', 'customer'))
            ->where('is_approved', true)
            ->orderBy('name')
            ->get();
        $shipments = Shipment::where('status', 'active')->orderBy('id', 'desc')->get();
        $supplierPlatforms = Order::supplierPlatforms();
        return view('admin.orders.create', compact('customers', 'shipments', 'supplierPlatforms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $description = $request->input('gadget_description', '');
        $parsed = Order::parseDescription($description);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'gadget_description' => 'required|string|max:5000',
            'supplier_platform' => ['nullable', Rule::in(array_keys(Order::supplierPlatforms()))],
            'supplier_order_number' => 'nullable|string|max:120|unique:orders,supplier_order_number',
            'supplier_logistics_code' => 'nullable|string|max:120|unique:orders,supplier_logistics_code',
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
        $supplierOrderNumber = trim((string) ($valid['supplier_order_number'] ?? ''));
        $supplierLogisticsCode = trim((string) ($valid['supplier_logistics_code'] ?? ''));
        $mappingStatus = null;
        $mappedAt = null;
        $mappedBy = null;
        if ($supplierLogisticsCode !== '') {
            $mappingStatus = 'mapped';
            $mappedAt = now();
            $mappedBy = auth()->id();
        } elseif ($supplierOrderNumber !== '') {
            $mappingStatus = 'pending_logistics';
        }

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
            'supplier_platform' => $valid['supplier_platform'] ?? null,
            'supplier_order_number' => $supplierOrderNumber !== '' ? $supplierOrderNumber : null,
            'supplier_logistics_code' => $supplierLogisticsCode !== '' ? $supplierLogisticsCode : null,
            'mapping_status' => $mappingStatus,
            'mapped_at' => $mappedAt,
            'mapped_by' => $mappedBy,
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
        $users = User::query()
            ->where(function ($q) use ($order): void {
                $q->whereHas('role', fn ($r) => $r->where('name', 'customer'))
                    ->orWhere('id', $order->user_id);
            })
            ->orderBy('name')
            ->get();
        $supplierPlatforms = Order::supplierPlatforms();
        return view('admin.orders.edit', compact('order', 'users', 'supplierPlatforms'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $valid = $request->validate([
            'user_id' => 'required|exists:users,id',
            'supplier_platform' => ['nullable', Rule::in(array_keys(Order::supplierPlatforms()))],
            'supplier_order_number' => ['nullable', 'string', 'max:120', Rule::unique('orders', 'supplier_order_number')->ignore($order->id)],
            'supplier_logistics_code' => ['nullable', 'string', 'max:120', Rule::unique('orders', 'supplier_logistics_code')->ignore($order->id)],
            'product_name' => 'required|string|max:255',
            'spec_summary' => 'nullable|string|max:500',
            'full_description' => 'nullable|string|max:5000',
            'total_amount_ngn' => 'required|numeric|min:0',
            'outstanding_balance_ngn' => 'nullable|numeric|min:0',
            'logistics_type' => 'nullable|string|in:within_lagos,outside_lagos,combined',
            'status' => 'required|string|in:pending,pending_approval,processing,shipped,delivered,cancelled',
            'payment_slips' => 'nullable|array|max:5',
            'payment_slips.*' => [File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(5 * 1024)],
        ], [
            'payment_slips.max' => 'Maximum 5 images or PDFs allowed.',
            'payment_slips.*.max' => 'Each slip must not exceed 5MB.',
        ]);

        $shipmentId = $order->shipment_id;
        $currentStageId = $order->current_stage_id;
        if (!empty($shipmentId) || !empty($currentStageId)) {
            $shipment = !empty($shipmentId) ? Shipment::with('currentStage')->find($shipmentId) : null;
            $valid['status'] = Order::deriveStatusFromStage(
                $shipmentId,
                $currentStageId,
                $shipment
            );
        } else {
            $valid['status'] = in_array($valid['status'] ?? 'processing', ['shipped', 'delivered'])
                ? 'processing'
                : ($valid['status'] ?? 'processing');
        }

        $valid['outstanding_balance_ngn'] = (float) ($valid['outstanding_balance_ngn'] ?? 0);
        $valid['logistics_type'] = $valid['logistics_type'] ?? 'within_lagos';
        $valid['supplier_order_number'] = trim((string) ($valid['supplier_order_number'] ?? '')) ?: null;
        $valid['supplier_logistics_code'] = trim((string) ($valid['supplier_logistics_code'] ?? '')) ?: null;
        if ($valid['supplier_logistics_code']) {
            $valid['mapping_status'] = 'mapped';
            $valid['mapped_at'] = now();
            $valid['mapped_by'] = auth()->id();
        } elseif ($valid['supplier_order_number']) {
            $valid['mapping_status'] = 'pending_logistics';
            $valid['mapped_at'] = null;
            $valid['mapped_by'] = null;
        } else {
            $valid['mapping_status'] = null;
            $valid['mapped_at'] = null;
            $valid['mapped_by'] = null;
        }
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

    /** Download invoice PDF for this order (serve content directly like customer download). */
    public function downloadInvoice(Order $order, InvoiceService $invoiceService): Response|RedirectResponse
    {
        if (! $order->invoice_id || ! $order->invoice) {
            return back()->with('error', 'No invoice for this order.');
        }
        $invoice = $order->invoice;
        $invoiceNumber = (string) ($invoice->invoice_number ?? '');
        $filename = 'invoice-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $invoiceNumber) . '.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        try {
            $content = $invoiceService->getInvoicePdfContent($invoice);
            if ($content !== null && $content !== '') {
                return response($content, 200, $headers);
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return back()->with('error', 'Invoice file not found.');
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
