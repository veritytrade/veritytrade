<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceRequest;
use App\Models\Order;

/*
|--------------------------------------------------------------------------
| Admin Dashboard Controller
|--------------------------------------------------------------------------
| Displays the admin dashboard.
|--------------------------------------------------------------------------
*/

class AdminDashboardController extends Controller
{
    public function index()
    {
        $packagesInTransit = Order::whereNotNull('shipment_id')
            ->where('status', '!=', 'delivered')
            ->where('status', '!=', 'cancelled')
            ->count();

        $ordersPendingApproval = Order::where('status', 'pending_approval')->count();

        $ordersWithoutShipment = Order::whereNull('shipment_id')
            ->whereNotIn('status', ['cancelled', 'delivered'])
            ->count();

        $pendingInvoiceRequestsCount = InvoiceRequest::where('status', 'pending')
            ->whereHas('shipment')
            ->count();

        $staleInvoiceRequestsCount = InvoiceRequest::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        $ordersOnCompletedShipmentsNotDelivered = Order::where('status', '!=', 'delivered')
            ->whereHas('shipment', function ($q): void {
                $q->where('status', 'completed');
            })
            ->count();

        return view('admin.dashboard', compact(
            'packagesInTransit',
            'ordersPendingApproval',
            'ordersWithoutShipment',
            'pendingInvoiceRequestsCount',
            'staleInvoiceRequestsCount',
            'ordersOnCompletedShipmentsNotDelivered'
        ));
    }
}
