<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceRequest;
use App\Models\InvoiceSetting;
use App\Models\Order;
use App\Models\Shipment;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InvoiceService
{
    protected function hasGd(): bool
    {
        return extension_loaded('gd');
    }

    /** Hardcoded – not editable from admin */
    protected const COMPANY_NAME = 'Verity Gadgets';
    protected const TAGLINE = 'A Division of Verity Trade Global Limited';
    protected const QR_BASE_URL = 'https://veritytrade.ng/connect/';

    /** Get uninvoiced orders in a shipment for a customer */
    public function getUninvoicedOrdersForShipment(int $shipmentId, int $userId): Collection
    {
        return Order::where('shipment_id', $shipmentId)
            ->where('user_id', $userId)
            ->whereDoesntHave('invoice')
            ->whereIn('status', ['processing', 'shipped', 'delivered'])
            ->orderBy('id')
            ->get();
    }

    /** Get shipments with uninvoiced orders for a customer */
    public function getUninvoicedShipmentsForUser(int $userId): Collection
    {
        return Shipment::whereHas('orders', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->whereDoesntHave('invoice')
                ->whereIn('status', ['processing', 'shipped', 'delivered']);
        })
            ->with(['orders' => fn ($q) => $q->where('user_id', $userId)->whereDoesntHave('invoice')])
            ->orderByDesc('id')
            ->get();
    }

    /** Build invoice view data (reusable for HTML preview and PDF) */
    public function buildPreviewData(): array
    {
        $settings = InvoiceSetting::get();
        $useImages = $this->hasGd();
        $qrImageData = $this->generateQrDataUri(self::QR_BASE_URL);
        $logoDataUri = $this->getLogoDataUri($settings->logo_path);
        $logoUrl = $this->getLogoAssetUrl();
        $iconLocation = $useImages ? $this->getIconDataUri('location') : null;
        $iconEmail = $useImages ? $this->getIconDataUri('email') : null;
        $iconPhone = $useImages ? $this->getIconDataUri('phone') : null;

        return [
            'companyName' => self::COMPANY_NAME,
            'tagline' => self::TAGLINE,
            'companyAddress' => $settings->company_address,
            'companyPhone' => $settings->company_phone,
            'companyEmail' => $settings->company_email,
            'logoDataUri' => $logoDataUri,
            'logoUrl' => $logoUrl,
            'iconLocation' => $iconLocation,
            'iconEmail' => $iconEmail,
            'iconPhone' => $iconPhone,
            'invoiceNumber' => 'VT-' . now()->format('Y') . '-' . str_pad('1', 4, '0', STR_PAD_LEFT),
            'invoiceDate' => now()->format('d M Y'),
            'customerName' => 'Suleman Godson',
            'customerPhone' => '09039470532',
            'customerAddress' => 'Nasarawa',
            'items' => [
                ['model' => 'iPhone X', 'specification' => '256GB · Grade A · 95%', 'qty' => 1, 'unit_price' => 350000, 'total_price' => 350000],
                ['model' => 'Charger', 'specification' => 'Fast charger', 'qty' => 1, 'unit_price' => 15000, 'total_price' => 15000],
            ],
            'subtotal' => 365000,
            'waybill' => 15000,
            'grandTotal' => 380000,
            'copyright' => $settings->copyright ?? 'Verity Trade Global Limited',
            'qrImageUrl' => $qrImageData,
            'status' => 'Pending',
        ];
    }

    /** Generate a preview PDF with sample data (for admin to see how invoice looks) */
    public function previewPdf(): Response
    {
        $data = $this->buildPreviewData();
        $html = view('pdf.invoice', $data)->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('invoice-preview.pdf', ['Attachment' => false]);
    }

    /** Generate invoice for a collection of orders (same shipment, same customer) */
    public function generateForOrders(Collection $orders, ?int $generatedBy = null): Invoice
    {
        if ($orders->isEmpty()) {
            throw new \InvalidArgumentException('At least one order is required.');
        }
        $orders->load(['user', 'shipment']);
        $first = $orders->first();
        $customer = $first->user;
        $shipmentId = $first->shipment_id;

        $invoice = new Invoice();
        $invoice->uuid = Str::uuid()->toString();
        $invoice->invoice_number = $this->generateInvoiceNumber();
        $invoice->user_id = $customer->id;
        $invoice->generated_by = $generatedBy;

        $subtotal = 0;
        $waybill = 0;
        $items = [];

        foreach ($orders as $order) {
            $amount = (float) $order->total_amount_ngn;
            $subtotal += $amount;
            if ($order->pays_logistics) {
                $waybill = 10000;
            }
            $items[] = [
                'model' => $this->extractModelFromOrder($order),
                'specification' => $this->extractSpecificationFromOrder($order),
                'qty' => 1,
                'unit_price' => $amount,
                'total_price' => $amount,
            ];
        }
        $grandTotal = $subtotal + $waybill;

        $settings = InvoiceSetting::get();
        $useImages = $this->hasGd();
        $qrImageData = $this->generateQrDataUri(self::QR_BASE_URL);
        $logoDataUri = $this->getLogoDataUri($settings->logo_path);
        $logoUrl = $this->getLogoAssetUrl();
        $iconLocation = $useImages ? $this->getIconDataUri('location') : null;
        $iconEmail = $useImages ? $this->getIconDataUri('email') : null;
        $iconPhone = $useImages ? $this->getIconDataUri('phone') : null;
        $customerAddress = $this->formatCustomerAddress($customer);

        $html = view('pdf.invoice', [
            'companyName' => self::COMPANY_NAME,
            'tagline' => self::TAGLINE,
            'companyAddress' => $settings->company_address,
            'companyPhone' => $settings->company_phone,
            'companyEmail' => $settings->company_email,
            'logoDataUri' => $logoDataUri,
            'logoUrl' => $logoUrl,
            'iconLocation' => $iconLocation,
            'iconEmail' => $iconEmail,
            'iconPhone' => $iconPhone,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => now()->format('d M Y'),
            'customerName' => $customer?->name ?? 'Customer',
            'customerPhone' => $customer?->phone ?? '',
            'customerAddress' => $customerAddress,
            'items' => $items,
            'subtotal' => $subtotal,
            'waybill' => $waybill,
            'grandTotal' => $grandTotal,
            'copyright' => $settings->copyright ?? 'Verity Trade Global Limited',
            'qrImageUrl' => $qrImageData,
            'status' => $orders->max(fn ($o) => $o->payment_status === 'paid' ? 1 : 0) ? 'Paid' : 'Pending',
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $dir = 'invoices';
        $slug = Str::slug($first->verity_tracking_code ?? $first->shipment?->chinese_tracking_code ?? $first->id);
        $filename = 'invoice-' . $invoice->invoice_number . '-' . $slug . '.pdf';
        $path = $dir . '/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        $invoice->amount = $grandTotal;
        $invoice->details_json = [
            'subtotal' => $subtotal,
            'waybill' => $waybill,
            'order_ids' => $orders->pluck('id')->toArray(),
            'shipment_id' => $shipmentId,
        ];
        $invoice->pdf_path = $path;
        $invoice->save();

        foreach ($orders as $order) {
            $order->update(['invoice_id' => $invoice->id]);
        }

        $invoiceRequest = InvoiceRequest::where('shipment_id', $shipmentId)->first();
        if ($invoiceRequest) {
            $invoiceRequest->update(['status' => 'generated', 'invoice_id' => $invoice->id]);
        }

        return $invoice;
    }

    /** @deprecated Use generateForOrders */
    public function generateForOrder(Order $order, ?int $generatedBy = null): Invoice
    {
        $orders = $this->getUninvoicedOrdersForShipment($order->shipment_id, $order->user_id);
        if ($orders->isEmpty()) {
            throw new \InvalidArgumentException('No uninvoiced orders in this shipment.');
        }
        return $this->generateForOrders($orders, $generatedBy);
    }

    protected function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'VT-' . $year . '-';
        $last = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');
        $seq = 1;
        if ($last && preg_match('/-' . $year . '-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }
        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function getLogoAssetUrl(): ?string
    {
        foreach (['png', 'svg', 'jpg', 'jpeg', 'gif', 'webp'] as $ext) {
            if (file_exists(public_path('images/invoice/logo.' . $ext))) {
                return asset('images/invoice/logo.' . $ext);
            }
        }
        return null;
    }

    protected function getIconDataUri(string $name): ?string
    {
        $extensions = ['png', 'svg', 'jpg', 'jpeg'];
        $basePath = public_path('images/invoice-icons');
        foreach ($extensions as $ext) {
            $path = $basePath . '/' . $name . '.' . $ext;
            if (file_exists($path)) {
                $mime = match (strtolower($ext)) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'svg' => 'image/svg+xml',
                    default => 'image/png',
                };
                $data = base64_encode((string) file_get_contents($path));
                return "data:{$mime};base64,{$data}";
            }
        }
        return null;
    }

    protected function getLogoDataUri(?string $settingsLogoPath): ?string
    {
        $basePath = public_path('images/invoice');
        foreach (['png', 'svg', 'jpg', 'jpeg', 'gif', 'webp'] as $ext) {
            $path = $basePath . '/logo.' . $ext;
            if (file_exists($path)) {
                $mime = match (strtolower($ext)) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'svg' => 'image/svg+xml',
                    default => 'image/png',
                };
                $data = base64_encode((string) file_get_contents($path));
                return "data:{$mime};base64,{$data}";
            }
        }
        return null;
    }

    protected function generateQrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => true,
        ]);
        return (new QRCode($options))->render($url);
    }

    protected function formatCustomerAddress($customer): string
    {
        if (! $customer) {
            return '';
        }
        $parts = array_filter([$customer->address, $customer->city, $customer->state]);
        return implode(', ', $parts) ?: '';
    }

    protected function extractModelFromOrder(Order $order): string
    {
        $parsed = Order::parseDescription($order->full_description ?? '');
        if (! empty($parsed['product_name'])) {
            return $parsed['product_name'];
        }
        return $order->product_name ?: 'Order';
    }

    protected function extractSpecificationFromOrder(Order $order): string
    {
        if ($order->spec_summary) {
            return $order->spec_summary;
        }
        $desc = $order->full_description ?? '';
        $parts = [];
        if (preg_match('/(\d+\s*GB)/i', $desc, $m)) {
            $parts[] = trim($m[1]);
        }
        if (preg_match('/(?:battery|bat\.?)\s*[:\s]*(\d+%?)/i', $desc, $m) || preg_match('/(\d+%)\s*(?:battery|health)/i', $desc, $m)) {
            $parts[] = trim($m[1]);
        }
        if (preg_match('/(?:grade|condition)\s*[:\s]*([A-Da-d])/i', $desc, $m) || preg_match('/([A-Da-d])\s*(?:grade)/i', $desc, $m)) {
            $parts[] = 'Grade ' . strtoupper($m[1]);
        }
        return ! empty($parts) ? implode(' · ', $parts) : '—';
    }
}
