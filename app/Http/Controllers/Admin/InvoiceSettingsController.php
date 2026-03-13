<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceRequest;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceSettingsController extends Controller
{
    public function generateIndex(Request $request): RedirectResponse
    {
        $params = [];
        if ($email = $request->old('email', $request->query('email'))) {
            $params['email'] = $email;
        }
        return redirect()->route('admin.invoice-settings.edit', $params);
    }

    public function generateForShipment(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        if (! extension_loaded('gd')) {
            return back()->with('error', 'Invoice generation requires the PHP GD extension. Enable it in php.ini (uncomment extension=gd), then restart your web server.');
        }

        $valid = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'user_id' => 'required|exists:users,id',
        ]);
        $orders = $invoiceService->getUninvoicedOrdersForShipment($valid['shipment_id'], $valid['user_id']);
        if ($orders->isEmpty()) {
            return back()->with('error', 'No uninvoiced orders in this shipment. These orders may already have an invoice.');
        }
        try {
            $invoice = $invoiceService->generateForOrders($orders, auth()->id());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.invoice-settings.edit', ['email' => User::find($valid['user_id'])->email])
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated for ' . $orders->count() . ' item(s).');
    }

    public function edit(Request $request, InvoiceService $invoiceService): View
    {
        $email = $request->old('email', $request->query('email'));
        $selectedShipmentId = $request->query('shipment_id');
        $user = null;
        $shipments = collect();
        $pendingRequests = InvoiceRequest::with(['shipment', 'user'])
            ->where('status', 'pending')
            ->whereHas('shipment')
            ->latest()
            ->get();

        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $shipments = $invoiceService->getUninvoicedShipmentsForUser($user->id);
                if ($selectedShipmentId && $shipments->contains('id', (int) $selectedShipmentId)) {
                    // valid selection
                } elseif ($shipments->isNotEmpty()) {
                    $selectedShipmentId = $shipments->first()->id;
                }
            }
        }

        return view('admin.invoice-settings.edit', compact('email', 'user', 'shipments', 'selectedShipmentId', 'pendingRequests'));
    }

    public function previewHtml(Request $request, InvoiceService $invoiceService)
    {
        $userId = $request->query('user_id');
        $shipmentId = $request->query('shipment_id');

        if ($userId && $shipmentId) {
            $orders = $invoiceService->getUninvoicedOrdersForShipment((int) $shipmentId, (int) $userId);
            $data = $invoiceService->buildInvoiceDataFromOrders($orders);
        } else {
            $data = $invoiceService->buildPreviewData();
        }

        return response()->view('pdf.invoice', $data)->header('Content-Type', 'text/html');
    }

    public function preview(Request $request, InvoiceService $invoiceService): Response|RedirectResponse
    {
        if (! extension_loaded('gd')) {
            $params = [];
            if ($userId = $request->query('user_id')) {
                $user = User::find($userId);
                if ($user) {
                    $params['email'] = $user->email;
                    if ($sid = $request->query('shipment_id')) {
                        $params['shipment_id'] = $sid;
                    }
                }
            }
            return redirect()->route('admin.invoice-settings.edit', $params)
                ->with('error', 'PDF generation requires the PHP GD extension. Enable it in php.ini (uncomment extension=gd), then restart your web server. Use the HTML preview above in the meantime.');
        }

        $userId = $request->query('user_id');
        $shipmentId = $request->query('shipment_id');
        if ($userId && $shipmentId) {
            $orders = $invoiceService->getUninvoicedOrdersForShipment((int) $shipmentId, (int) $userId);
            $data = $invoiceService->buildInvoiceDataFromOrders($orders);
            return $invoiceService->streamPdfFromData($data);
        }
        return $invoiceService->previewPdf();
    }

}
