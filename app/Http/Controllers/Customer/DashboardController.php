<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Tracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private function ensureCustomer(): ?RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('staff')) {
            return redirect()->route('admin.dashboard');
        }

        return null;
    }

    public function index(): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }

        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)
            ->latest('id')
            ->take(10)
            ->get();

        $orderIds = $orders->pluck('id');

        $invoices = Invoice::whereIn('order_id', $orderIds)
            ->latest('id')
            ->take(10)
            ->get();

        $trackingEvents = Tracking::whereIn('order_id', $orderIds)
            ->latest('event_time')
            ->take(15)
            ->get();

        return view('customer.dashboard', compact('orders', 'invoices', 'trackingEvents'));
    }

    public function orders(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }

        $orders = Order::where('user_id', auth()->id())
            ->latest('id')
            ->paginate(15);

        return view('customer.orders', compact('orders'));
    }

    public function tracking(): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }

        $orderIds = Order::where('user_id', auth()->id())->pluck('id');
        $trackingEvents = Tracking::whereIn('order_id', $orderIds)
            ->latest('event_time')
            ->paginate(20);

        return view('customer.tracking', compact('trackingEvents'));
    }

    public function invoices(): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }

        $orderIds = Order::where('user_id', auth()->id())->pluck('id');
        $invoices = Invoice::whereIn('order_id', $orderIds)
            ->latest('id')
            ->paginate(20);

        return view('customer.invoices', compact('invoices'));
    }
}
