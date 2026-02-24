<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Admin Login Controller
|--------------------------------------------------------------------------
| Handles login/logout specifically for admin users.
|--------------------------------------------------------------------------
*/

class AdminLoginController extends Controller
{
    // Show admin login form
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // Handle login attempt
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            // Ensure user has dashboard role
            if ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('staff')) {

                // Ensure approved
                if (!$user->is_approved) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Account not approved.']);
                }

                return redirect()->route('admin.dashboard');
            }

            // If user is not admin
            Auth::logout();
            return back()->withErrors(['email' => 'Unauthorized access.']);
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    // Logout admin
    public function logout(Request $request)
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}


