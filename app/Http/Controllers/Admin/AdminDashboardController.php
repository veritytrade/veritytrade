<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

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
        return view('admin.dashboard');
    }
}
