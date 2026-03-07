<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        return view('admin.dashboard', compact('packagesInTransit'));
    }
}
