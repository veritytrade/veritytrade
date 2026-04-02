<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return back()->with('error', 'Enter something to search (VTP/VTD ref, listing ID, customer, order ID, shipment, invoice, or supplier refs).');
        }

        // Ops refs from WhatsApp (deal / ingested product) — never exposes source URL on the storefront
        if (preg_match('/^VTP(\d+)$/i', $q, $m)) {
            $product = Product::query()->find((int) $m[1]);
            if ($product) {
                return redirect()->route('admin.products.show', $product);
            }
        }
        if (preg_match('/^VTD(\d+)$/i', $q, $m)) {
            $deal = Deal::query()->find((int) $m[1]);
            if ($deal) {
                return redirect()->route('admin.deals.edit', $deal);
            }
        }

        $productBySource = Product::query()->where('source_item_id', $q)->first();
        if (! $productBySource && strlen($q) >= 8) {
            $productBySource = Product::query()
                ->where('source_item_id', 'like', '%' . $q . '%')
                ->orderByDesc('id')
                ->first();
        }
        if ($productBySource) {
            return redirect()->route('admin.products.show', $productBySource);
        }

        // 0. Supplier references on orders (fastest recovery path in operations)
        $supplierOrder = Order::where('supplier_order_number', $q)
            ->orWhere('supplier_logistics_code', $q)
            ->orWhere('supplier_order_number', 'like', '%' . $q . '%')
            ->orWhere('supplier_logistics_code', 'like', '%' . $q . '%')
            ->orderByDesc('id')
            ->first();
        if ($supplierOrder) {
            return redirect()->route('admin.orders.show', $supplierOrder);
        }

        // 1. Customer by email / username / name -> Customer 360
        $user = null;
        if (filter_var($q, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $q)->first();
        }
        if (!$user) {
            $user = User::where('username', $q)
                ->orWhere('email', 'like', '%' . $q . '%')
                ->orWhere('username', 'like', '%' . $q . '%')
                ->orWhere('name', 'like', '%' . $q . '%')
                ->orderByDesc('id')
                ->first();
        }
        if ($user) {
            return redirect()->route('admin.customers.show', ['q' => $user->email ?: $user->username]);
        }

        // 2. Order by numeric id
        if (ctype_digit($q)) {
            $order = Order::find((int) $q);
            if ($order) {
                return redirect()->route('admin.orders.show', $order);
            }
        }

        // 3. Shipment by Chinese tracking code (partial)
        $shipment = Shipment::where('chinese_tracking_code', 'like', '%' . $q . '%')
            ->orderByDesc('id')
            ->first();
        if ($shipment) {
            return redirect()->route('admin.shipments.show', $shipment);
        }

        // 4. Invoice by invoice number (partial)
        $invoice = Invoice::where('invoice_number', 'like', '%' . $q . '%')
            ->orderByDesc('id')
            ->first();
        if ($invoice) {
            return redirect()->route('admin.invoices.index', ['invoice_number' => $invoice->invoice_number]);
        }

        return back()->with('error', 'No match. Try VTP (product) / VTD (deal) refs, marketplace listing id, customer email, order id, shipment, invoice, or supplier refs.');
    }
}

