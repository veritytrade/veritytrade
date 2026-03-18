<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
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
            return back()->with('error', 'Enter something to search (email, WhatsApp name, shipment, order, or invoice).');
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

        return back()->with('error', 'No matching customer, order, shipment, or invoice found.');
    }
}

