{{--
    Invoice PDF - Minimal design for reliable DomPDF rendering.
    Brand palette: Primary Blue #1F5FBF, Primary Green #36B37E, Dark Text #2C3E50,
    Light Gray #F3F4F6, Border Gray #E5E7EB, Accent Green #34A853.
--}}
@php
    $companyName = $companyName ?? 'Verity Gadgets';
    $tagline = $tagline ?? 'A Division of Verity Trade Global Limited';
    $companyAddress = $companyAddress ?? '';
    $companyPhone = $companyPhone ?? '+234 708 411 7779';
    $companyEmail = $companyEmail ?? 'info@veritytrade.ng';
    $invoiceNumber = $invoiceNumber ?? 'VT-' . now()->format('Y') . '-0001';
    $invoiceDate = $invoiceDate ?? now()->format('d M Y');
    $customerName = $customerName ?? '';
    $customerPhone = $customerPhone ?? '';
    $customerAddress = $customerAddress ?? '';
    $items = $items ?? [];
    $subtotal = (float) ($subtotal ?? 0);
    $waybill = (float) ($waybill ?? 0);
    $grandTotal = (float) ($grandTotal ?? ($subtotal + $waybill));
    $copyright = $copyright ?? 'Verity Trade Global Limited';
    $logoDataUri = $logoDataUri ?? null;
    $logoUrl = $logoUrl ?? $logoDataUri;
    $qrImageUrl = $qrImageUrl ?? null;
    $status = $status ?? 'Paid';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice {{ $invoiceNumber }}</title>
<style type="text/css">
@page { margin: 0 5mm 5mm 5mm; }
html, body { margin: 0; padding: 0; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #2C3E50; padding: 0 28px 20px 28px; background: #FFFFFF; }
table { width: 100%; border-collapse: collapse; }
td { padding: 8px; vertical-align: top; }
.th { background-color: #1F5FBF; color: #ffffff; font-weight: bold; padding: 10px; }
.bdr { border-bottom: 1px solid #E5E7EB; }
.totals { width: 280px; margin-left: auto; margin-top: 15px; }
.total-row { padding: 5px 0; }
.total-final { background-color: #2F9E6E; color: #ffffff; font-weight: bold; padding: 12px; margin-top: 8px; }
.status-badge { color: #34A853; font-weight: bold; }
</style>
</head>
<body>

{{-- Top divider: on top of logo (z-index). Header pulled up into divider. --}}
<div style="position:relative;">
<div style="position:relative;z-index:2;height:24px;background-color:#245efe;margin:0 -80px 0 -80px;"></div>
<div style="position:relative;z-index:1;top:-40px;">
<table style="width:100%;border-collapse:collapse;" cellpadding="0" cellspacing="0">
<tr>
<td style="width:55%;vertical-align:middle;padding:0;">
<table style="border-collapse:collapse;position:relative;left:-50px;" cellpadding="0" cellspacing="0"><tr>
<td style="padding:0;vertical-align:middle;width:1%;">
    @if($logoDataUri ?? $logoUrl)
        <img src="{{ $logoDataUri ?? $logoUrl }}" alt="Logo" style="height:180px;width:180px;object-fit:contain;display:block;" />
    @else
        <div style="height:180px;width:180px;background-color:#1F5FBF;color:#fff;font-size:56px;font-weight:bold;line-height:180px;text-align:center;">V</div>
    @endif
</td>
<td style="padding:0;vertical-align:middle;">
    <div style="position:relative;left:-20px;margin-left:-20px;">
        <strong style="font-size:20px;color:#2C3E50;letter-spacing:4px;display:block;white-space:nowrap;">{{ strtoupper($companyName) }}</strong>
        <span style="font-size:12px;color:#2C3E50;display:block;white-space:nowrap;margin-top:6px;">{{ $tagline }}</span>
    </div>
</td>
</tr></table>
</td>
<td style="width:45%;vertical-align:middle;padding:0;text-align:right;font-size:12px;line-height:1.6;color:#2C3E50;">
    {{ $companyEmail }}<br>
    {{ $companyPhone }}<br>
    Saki-Ogbooro Road<br>
    Saki, Oyo State, Nigeria
</td>
</tr>
</table>

{{-- Divider beneath header: Primary Green, full bleed to edges --}}
<div style="position:relative;top:-47px;margin:5px -80px 0 -80px;"><div style="height:24px;background-color:#12c06b;"></div></div>
<h2 style="margin:0 0 15px 0;font-size:20px;color:#2C3E50;">INVOICE</h2>

<table>
<tr>
<td style="width:55%;">
    <strong>Bill To</strong><br>
    <strong>{{ $customerName }}</strong><br>
    @if($customerPhone){{ $customerPhone }}<br>@endif
    @if($customerAddress){{ $customerAddress }}@endif
</td>
<td style="width:45%;text-align:right;">
    <strong>Invoice Number:</strong> {{ $invoiceNumber }}<br>
    <strong>Date:</strong> {{ $invoiceDate }}<br>
    <strong>Status:</strong> <span class="status-badge">{{ ucfirst($status) }}</span>
</td>
</tr>
</table>

<table style="margin-top:20px;">
<thead>
<tr>
<td class="th">Product</td>
<td class="th">Specification</td>
<td class="th" style="text-align:center;">Qty</td>
<td class="th" style="text-align:right;">Unit Price</td>
<td class="th" style="text-align:right;">Total</td>
</tr>
</thead>
<tbody>
@foreach($items as $item)
<tr class="bdr">
<td>{{ $item['model'] ?? $item['product_name'] ?? $item['description'] ?? 'Item' }}</td>
<td>{{ $item['specification'] ?? $item['spec_summary'] ?? '—' }}</td>
<td style="text-align:center;">{{ $item['qty'] ?? 1 }}</td>
<td style="text-align:right;">&#8358;{{ number_format((float) ($item['unit_price'] ?? 0), 2) }}</td>
<td style="text-align:right;">&#8358;{{ number_format((float) ($item['total_price'] ?? 0), 2) }}</td>
</tr>
@endforeach
</tbody>
</table>

<table class="totals">
<tr><td class="total-row">Subtotal</td><td class="total-row" style="text-align:right;">&#8358;{{ number_format($subtotal, 2) }}</td></tr>
@if($waybill > 0)
<tr><td class="total-row">Waybill</td><td class="total-row" style="text-align:right;">&#8358;{{ number_format($waybill, 2) }}</td></tr>
@endif
<tr><td colspan="2"><div class="total-final"><table style="width:100%;"><tr><td>TOTAL</td><td style="text-align:right;">&#8358;{{ number_format($grandTotal, 2) }}</td></tr></table></div></td></tr>
</table>

<div style="margin-top:25px;padding:15px 0 0 0;border-top:1px solid #E5E7EB;">
<ul style="margin:0 0 5px 20px;padding:0;">
<li>Device sourced and verified by Verity Gadgets.</li>
<li>Please reference your Invoice Number for any inquiry.</li>
</ul>
{{-- Thank you + banner in ONE table: no gap between rows (DomPDF ignores margins) --}}
<div style="margin:0 -80px 0 -80px;">
<table style="width:100%;border-collapse:collapse;" cellpadding="0" cellspacing="0">
<tr>
<td style="padding:0 80px 0 80px;">
<table style="width:100%;border-collapse:collapse;" cellpadding="0" cellspacing="0">
<tr>
<td>
<strong style="color:#2F9E6E;">Thank you for choosing Verity Gadgets.</strong><br>
<span style="font-size:11px;color:#2C3E50;opacity:0.8;">Verified Value. Visible Quality.</span>
</td>
<td style="text-align:right;width:150px;">
    @if($qrImageUrl)
        <img src="{{ $qrImageUrl }}" alt="QR" style="width:90px;height:90px;" />
    @endif
<br><span style="font-size:10px;color:#2C3E50;opacity:0.8;">Scan for Support</span>
</td>
</tr>
</table>
</td>
</tr>
<tr>
<td colspan="1" style="padding:0;height:4px;background-color:#1F5FBF;line-height:0;font-size:0;"></td>
</tr>
</table>
</div>
</div>
<p style="text-align:center;font-size:11px;color:#2C3E50;opacity:0.8;margin:10px 0;padding:0 80px;">{{ $copyright }}</p>

</body>
</html>
