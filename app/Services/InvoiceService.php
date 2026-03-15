<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceRequest;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRGdImagePNG;
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
    protected const COMPANY_EMAIL = 'info@veritytrade.ng';
    protected const COMPANY_PHONE = '+234 708 411 7779';
    protected const COPYRIGHT = 'Verity Trade Global Limited';
    protected const QR_BASE_URL = 'https://veritytrade.ng/connect/';

    /** Normalize stored pdf_path to relative path (invoices/xxx.pdf) for consistent read/write. */
    protected function normalizeStoredPdfPath(?string $raw): ?string
    {
        if ($raw === null || $raw === '' || str_contains($raw, '..')) {
            return null;
        }
        $path = str_replace(['\\', "\0"], ['/', ''], trim((string) $raw));
        $path = ltrim($path, '/');
        foreach (['storage/app/public/', 'app/public/'] as $needle) {
            $pos = stripos($path, $needle);
            if ($pos !== false) {
                $path = substr($path, $pos + strlen($needle));
                break;
            }
        }
        return $path !== '' ? $path : null;
    }

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

    /** Get all shipments that have orders for this customer (invoiced or not), for generate/regenerate UI */
    public function getShipmentsForUser(int $userId): Collection
    {
        return Shipment::whereHas('orders', fn ($q) => $q->where('user_id', $userId))
            ->with(['orders' => fn ($q) => $q->where('user_id', $userId)->with('invoice')])
            ->orderByDesc('id')
            ->get();
    }

    /** Get existing invoice for this shipment + customer (if any); same invoice number is reused on regenerate */
    public function getExistingInvoiceForShipmentAndUser(int $shipmentId, int $userId): ?Invoice
    {
        return Invoice::where('user_id', $userId)
            ->whereHas('orders', fn ($q) => $q->where('shipment_id', $shipmentId))
            ->first();
    }

    /**
     * Resolve invoice PDF to full filesystem path for streaming (admin + customer download).
     * Uses base_path and multiple path strategies so the file is found regardless of stored path format.
     */
    public function resolveInvoicePdfPath(Invoice $invoice): ?string
    {
        $invoiceNumber = (string) ($invoice->invoice_number ?? '');
        $root = base_path('storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public');
        $pathsToTry = [];
        $normalized = $this->normalizeStoredPdfPath($invoice->pdf_path);
        if ($normalized !== null) {
            $pathsToTry[] = $normalized;
        }
        $pathsToTry[] = 'invoices/invoice-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $invoiceNumber) . '.pdf';

        foreach (array_unique($pathsToTry) as $path) {
            $path = ltrim(str_replace(['\\', "\0"], ['/', ''], (string) $path), '/');
            if ($path === '' || str_contains($path, '..')) {
                continue;
            }
            $fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
            $real = @realpath($fullPath);
            if ($real !== false && is_file($real)) {
                $rootReal = @realpath($root);
                if ($rootReal !== false && str_starts_with($real, $rootReal)) {
                    return $real;
                }
            }
        }

        $disk = Storage::disk('public');
        foreach ($pathsToTry as $path) {
            $path = ltrim(str_replace(['\\', "\0"], ['/', ''], (string) $path), '/');
            if (($path !== '' && ! str_contains($path, '..')) && $disk->exists($path) && method_exists($disk, 'path')) {
                $fullPath = $disk->path($path);
                if (is_file($fullPath)) {
                    return $fullPath;
                }
            }
        }

        $invoicesDir = $root . DIRECTORY_SEPARATOR . 'invoices';
        if (is_dir($invoicesDir)) {
            $name = 'invoice-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $invoiceNumber) . '.pdf';
            $byName = $invoicesDir . DIRECTORY_SEPARATOR . $name;
            if (is_file($byName)) {
                return $byName;
            }
        }

        return null;
    }

    /** Build invoice view data (reusable for HTML preview and PDF) */
    public function buildPreviewData(): array
    {
        $useImages = $this->hasGd();
        $qrImageData = $this->generateQrDataUri(self::QR_BASE_URL);
        $logoDataUri = $this->getLogoDataUri(null);
        $logoUrl = $this->getLogoAssetUrl();
        $iconLocation = $this->getIconDataUri('location');
        $iconEmail = $this->getIconDataUri('email');
        $iconPhone = $this->getIconDataUri('phone');

        return [
            'companyName' => self::COMPANY_NAME,
            'tagline' => self::TAGLINE,
            'companyAddress' => '',
            'companyPhone' => self::COMPANY_PHONE,
            'companyEmail' => self::COMPANY_EMAIL,
            'logoDataUri' => $logoDataUri,
            'logoUrl' => $logoUrl,
            'iconLocation' => $iconLocation,
            'iconEmail' => $iconEmail,
            'iconPhone' => $iconPhone,
            'invoiceNumber' => 'VG-' . now()->format('Ym') . '-0001',
            'invoiceDate' => now()->format('d M Y'),
            'customerName' => 'Suleman Godson',
            'customerPhone' => '09039470532',
            'customerAddress' => 'Nasarawa',
            'items' => [
                ['model' => 'iPhone X · 256GB', 'specification' => 'Grade A · 95%', 'qty' => 1, 'unit_price' => 350000, 'total_price' => 350000],
                ['model' => 'Charger', 'specification' => 'Fast charger', 'qty' => 1, 'unit_price' => 15000, 'total_price' => 15000],
            ],
            'subtotal' => 365000,
            'grandTotal' => 365000,
            'outstandingBalance' => 50000,
            'copyright' => self::COPYRIGHT,
            'qrImageUrl' => $qrImageData,
            'status' => 'Unpaid',
        ];
    }

    /** Generate a preview PDF with sample data (for admin to see how invoice looks) */
    public function previewPdf(): Response
    {
        $data = $this->buildPreviewData();
        return $this->streamPdfFromData($data);
    }

    /** Stream PDF from invoice view data */
    public function streamPdfFromData(array $data): Response
    {
        $html = view('pdf.invoice', $data)->render();
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('invoice-preview.pdf', ['Attachment' => false]);
    }

    /** Build invoice view data from orders (for preview, no save) */
    public function buildInvoiceDataFromOrders(Collection $orders): array
    {
        if ($orders->isEmpty()) {
            return $this->buildPreviewData();
        }
        $orders->load(['user', 'shipment']);
        $first = $orders->first();
        $customer = $first->user;
        $useImages = $this->hasGd();
        $qrImageData = $this->generateQrDataUri(self::QR_BASE_URL);
        $logoDataUri = $this->getLogoDataUri(null);
        $logoUrl = $this->getLogoAssetUrl();

        $subtotal = 0;
        $outstandingBalance = 0;
        $items = [];
        foreach ($orders as $order) {
            $amount = (float) $order->total_amount_ngn;
            $subtotal += $amount;
            $outstandingBalance += (float) ($order->outstanding_balance_ngn ?? 0);
            $items[] = [
                'model' => $this->extractModelFromOrder($order),
                'specification' => $this->extractSpecificationFromOrder($order),
                'qty' => 1,
                'unit_price' => $amount,
                'total_price' => $amount,
            ];
        }
        $grandTotal = $subtotal;

        return [
            'companyName' => self::COMPANY_NAME,
            'tagline' => self::TAGLINE,
            'companyAddress' => '',
            'companyPhone' => self::COMPANY_PHONE,
            'companyEmail' => self::COMPANY_EMAIL,
            'logoDataUri' => $logoDataUri,
            'logoUrl' => $logoUrl,
            'iconLocation' => $this->getIconDataUri('location'),
            'iconEmail' => $this->getIconDataUri('email'),
            'iconPhone' => $this->getIconDataUri('phone'),
            'invoiceNumber' => 'VG-PREVIEW-' . now()->format('YmdHis'),
            'invoiceDate' => now()->format('d M Y'),
            'customerName' => $customer?->name ?? 'Customer',
            'customerPhone' => $customer ? $customer->getDisplayPhone() : '',
            'customerAddress' => $this->formatCustomerAddress($customer),
            'items' => $items,
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'outstandingBalance' => $outstandingBalance,
            'copyright' => self::COPYRIGHT,
            'qrImageUrl' => $qrImageData,
            'status' => $outstandingBalance <= 0 ? 'Paid' : 'Unpaid',
        ];
    }

    /** Generate or regenerate invoice. If orders already have an invoice for this shipment+user, regenerates (same number, overwrites PDF). */
    public function generateForOrders(Collection $orders, ?int $generatedBy = null): Invoice
    {
        if ($orders->isEmpty()) {
            throw new \InvalidArgumentException('At least one order is required.');
        }
        $first = $orders->first();
        $shipmentId = $first->shipment_id;
        $userId = $first->user_id;
        $orderIds = $orders->pluck('id')->toArray();
        $alreadyInvoiced = Order::whereIn('id', $orderIds)->whereNotNull('invoice_id')->exists();

        if ($alreadyInvoiced) {
            $existing = $this->getExistingInvoiceForShipmentAndUser($shipmentId, $userId);
            if ($existing) {
                $ordersForInvoice = Order::where('invoice_id', $existing->id)->orderBy('id')->get();
                if ($ordersForInvoice->isNotEmpty()) {
                    return DB::transaction(function () use ($existing, $ordersForInvoice, $generatedBy) {
                        return $this->doRegenerateInvoice($existing, $ordersForInvoice, $generatedBy);
                    });
                }
            }
            throw new \InvalidArgumentException('One or more of these orders already have an invoice. Please refresh the page.');
        }

        return DB::transaction(function () use ($orders, $generatedBy) {
            return $this->doGenerateForOrders($orders, $generatedBy);
        });
    }

    /** Regenerate PDF for an existing invoice (same number, same path; overwrites file so customer dashboard gets updated PDF). */
    protected function doRegenerateInvoice(Invoice $invoice, Collection $orders, ?int $generatedBy = null): Invoice
    {
        $orders->load(['user', 'shipment']);
        $first = $orders->first();
        $customer = $first->user;
        $shipmentId = $first->shipment_id;

        $subtotal = 0;
        $outstandingBalance = 0;
        $items = [];
        foreach ($orders as $order) {
            $amount = (float) $order->total_amount_ngn;
            $subtotal += $amount;
            $outstandingBalance += (float) ($order->outstanding_balance_ngn ?? 0);
            $items[] = [
                'model' => $this->extractModelFromOrder($order),
                'specification' => $this->extractSpecificationFromOrder($order),
                'qty' => 1,
                'unit_price' => $amount,
                'total_price' => $amount,
            ];
        }
        $grandTotal = $subtotal;

        $qrImageData = $this->generateQrDataUri(self::QR_BASE_URL);
        $logoDataUri = $this->getLogoDataUri(null);
        $logoUrl = $this->getLogoAssetUrl();
        $iconLocation = $this->getIconDataUri('location');
        $iconEmail = $this->getIconDataUri('email');
        $iconPhone = $this->getIconDataUri('phone');
        $customerAddress = $this->formatCustomerAddress($customer);

        $html = view('pdf.invoice', [
            'companyName' => self::COMPANY_NAME,
            'tagline' => self::TAGLINE,
            'companyAddress' => '',
            'companyPhone' => self::COMPANY_PHONE,
            'companyEmail' => self::COMPANY_EMAIL,
            'logoDataUri' => $logoDataUri,
            'logoUrl' => $logoUrl,
            'iconLocation' => $iconLocation,
            'iconEmail' => $iconEmail,
            'iconPhone' => $iconPhone,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => now()->format('d M Y'),
            'customerName' => $customer?->name ?? 'Customer',
            'customerPhone' => $customer ? $customer->getDisplayPhone() : '',
            'customerAddress' => $customerAddress,
            'items' => $items,
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'outstandingBalance' => $outstandingBalance,
            'copyright' => self::COPYRIGHT,
            'qrImageUrl' => $qrImageData,
            'status' => $outstandingBalance <= 0 ? 'Paid' : 'Unpaid',
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $path = $this->normalizeStoredPdfPath($invoice->pdf_path);
        if ($path === null) {
            $dir = 'invoices';
            $filename = 'invoice-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $invoice->invoice_number) . '.pdf';
            $path = $dir . '/' . $filename;
        }
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');
        $invoice->pdf_path = $path;

        $disk = Storage::disk('public');
        if (! $disk->exists('invoices')) {
            $disk->makeDirectory('invoices');
        }
        $written = $disk->put($path, $pdf->output());
        if (! $written) {
            throw new \RuntimeException('Failed to write invoice PDF to storage: ' . $path);
        }

        $invoice->amount = $grandTotal;
        $invoice->details_json = [
            'subtotal' => $subtotal,
            'order_ids' => $orders->pluck('id')->toArray(),
            'shipment_id' => $shipmentId,
        ];
        if ($generatedBy !== null) {
            $invoice->generated_by = $generatedBy;
        }
        $invoice->save();

        return $invoice;
    }

    protected function doGenerateForOrders(Collection $orders, ?int $generatedBy = null): Invoice
    {
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
        $outstandingBalance = 0;
        $items = [];

        foreach ($orders as $order) {
            $amount = (float) $order->total_amount_ngn;
            $subtotal += $amount;
            $outstandingBalance += (float) ($order->outstanding_balance_ngn ?? 0);
            $items[] = [
                'model' => $this->extractModelFromOrder($order),
                'specification' => $this->extractSpecificationFromOrder($order),
                'qty' => 1,
                'unit_price' => $amount,
                'total_price' => $amount,
            ];
        }
        $grandTotal = $subtotal;

        $useImages = $this->hasGd();
        $qrImageData = $this->generateQrDataUri(self::QR_BASE_URL);
        $logoDataUri = $this->getLogoDataUri(null);
        $logoUrl = $this->getLogoAssetUrl();
        $iconLocation = $this->getIconDataUri('location');
        $iconEmail = $this->getIconDataUri('email');
        $iconPhone = $this->getIconDataUri('phone');
        $customerAddress = $this->formatCustomerAddress($customer);

        $html = view('pdf.invoice', [
            'companyName' => self::COMPANY_NAME,
            'tagline' => self::TAGLINE,
            'companyAddress' => '',
            'companyPhone' => self::COMPANY_PHONE,
            'companyEmail' => self::COMPANY_EMAIL,
            'logoDataUri' => $logoDataUri,
            'logoUrl' => $logoUrl,
            'iconLocation' => $iconLocation,
            'iconEmail' => $iconEmail,
            'iconPhone' => $iconPhone,
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => now()->format('d M Y'),
            'customerName' => $customer?->name ?? 'Customer',
            'customerPhone' => $customer ? $customer->getDisplayPhone() : '',
            'customerAddress' => $customerAddress,
            'items' => $items,
            'subtotal' => $subtotal,
            'grandTotal' => $grandTotal,
            'outstandingBalance' => $outstandingBalance,
            'copyright' => self::COPYRIGHT,
            'qrImageUrl' => $qrImageData,
            'status' => $outstandingBalance <= 0 ? 'Paid' : 'Unpaid',
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');

        $dir = 'invoices';
        $filename = 'invoice-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '', $invoice->invoice_number) . '.pdf';
        $path = $dir . '/' . $filename;

        $disk = Storage::disk('public');
        if (! $disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }
        $written = $disk->put($path, $pdf->output());
        if (! $written) {
            throw new \RuntimeException('Failed to write invoice PDF to storage: ' . $path);
        }

        $invoice->amount = $grandTotal;
        $invoice->details_json = [
            'subtotal' => $subtotal,
            'order_ids' => $orders->pluck('id')->toArray(),
            'shipment_id' => $shipmentId,
        ];
        $invoice->pdf_path = $path;
        $invoice->save();

        foreach ($orders as $order) {
            $order->update(['invoice_id' => $invoice->id]);
        }

        $invoiceRequest = InvoiceRequest::where('shipment_id', $shipmentId)
            ->where('user_id', $customer->id)
            ->first();
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
        $ym = now()->format('Ym');
        $prefix = 'VG-' . $ym . '-';
        $last = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('invoice_number');
        $seq = 1;
        if ($last && preg_match('/-' . $ym . '-(\d+)$/', $last, $m)) {
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
        // PNG renders reliably in DomPDF; SVG can be clipped/unsupported. Use PNG when GD available.
        $options = new QROptions([
            'outputInterface' => $this->hasGd() ? QRGdImagePNG::class : QRMarkupSVG::class,
            'outputBase64' => true,
        ]);
        if ($this->hasGd()) {
            $options->scale = 6;
        }
        return (new QRCode($options))->render($url);
    }

    protected function formatCustomerAddress($customer): string
    {
        if (! $customer) {
            return '';
        }
        $parts = array_filter([
            $customer->getDisplayAddress(),
            $customer->getDisplayCity(),
            $customer->getDisplayState(),
        ]);
        return implode(', ', $parts) ?: '';
    }

    protected function extractModelFromOrder(Order $order): string
    {
        $parsed = Order::parseDescription($order->full_description ?? '');
        $model = $parsed['product_name'] ?: $order->product_name ?: 'Order';
        return trim($model);
    }

    protected function extractSpecificationFromOrder(Order $order): string
    {
        $parsed = Order::parseDescription($order->full_description ?? '');
        $size = $parsed['storage'] ?? $parsed['memory'] ?? null;
        $appearancePct = $parsed['appearance_pct'] ?? null;
        $defectGrade = $parsed['defect_grade'] ?? null;

        $parts = [];
        if ($size) {
            $parts[] = trim($size);
        }
        if ($defectGrade) {
            $parts[] = $defectGrade;
        }
        if ($appearancePct !== null) {
            $parts[] = $appearancePct . '%';
        }

        if (! empty($parts)) {
            return implode(' · ', $parts);
        }

        if ($order->spec_summary) {
            return $order->spec_summary;
        }
        $desc = $order->full_description ?? '';
        if (preg_match('/(?:grade|condition)\s*[:\s]*([A-Da-dSs])/i', $desc, $m) || preg_match('/Grade\s+([A-Da-dSs])/i', $desc, $m)) {
            $parts[] = 'Grade ' . strtoupper($m[1]);
        }
        if (preg_match('/(?:appearance|battery)\s*[:\s]*(\d+)\s*%/i', $desc, $m)) {
            $parts[] = $m[1] . '%';
        }
        return ! empty($parts) ? implode(' · ', $parts) : '—';
    }
}
