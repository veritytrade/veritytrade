<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceRequest;
use App\Models\InvoiceSetting;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class InvoiceSettingsController extends Controller
{
    public function generateIndex(Request $request, InvoiceService $invoiceService): View
    {
        $email = $request->old('email', $request->query('email'));
        $user = null;
        $shipments = collect();
        if ($email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $shipments = $invoiceService->getUninvoicedShipmentsForUser($user->id);
            }
        }
        $pendingRequests = InvoiceRequest::with(['shipment', 'user'])
            ->where('status', 'pending')
            ->whereHas('shipment')
            ->latest()
            ->get();

        return view('admin.invoice-settings.generate', compact('email', 'user', 'shipments', 'pendingRequests'));
    }

    public function generateForShipment(Request $request, InvoiceService $invoiceService): RedirectResponse
    {
        $valid = $request->validate([
            'shipment_id' => 'required|exists:shipments,id',
            'user_id' => 'required|exists:users,id',
        ]);
        $orders = $invoiceService->getUninvoicedOrdersForShipment($valid['shipment_id'], $valid['user_id']);
        if ($orders->isEmpty()) {
            return back()->with('error', 'No uninvoiced orders in this shipment.');
        }
        $invoice = $invoiceService->generateForOrders($orders, auth()->id());

        return redirect()->route('admin.invoice-settings.generate', ['email' => User::find($valid['user_id'])->email])
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated for ' . $orders->count() . ' item(s).');
    }

    public function edit(InvoiceService $invoiceService): View
    {
        $setting = InvoiceSetting::get();
        $previewData = $invoiceService->buildPreviewData();
        return view('admin.invoice-settings.edit', compact('setting', 'previewData'));
    }

    public function previewHtml(InvoiceService $invoiceService)
    {
        $data = $invoiceService->buildPreviewData();
        return response()->view('pdf.invoice', $data)->header('Content-Type', 'text/html');
    }

    public function preview(InvoiceService $invoiceService): Response
    {
        return $invoiceService->previewPdf();
    }

    public function update(Request $request): RedirectResponse
    {
        $valid = $request->validate([
            'company_address' => 'nullable|string|max:500',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'copyright' => 'nullable|string|max:255',
        ]);

        $setting = InvoiceSetting::get();
        $setting->update(array_filter($valid));

        return back()->with('success', 'Invoice settings updated.');
    }
}
