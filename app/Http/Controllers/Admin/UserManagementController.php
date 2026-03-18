<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function staffIndex()
    {
        $staffRoles = Role::whereIn('name', ['admin', 'staff'])->orderBy('name')->get();

        $staffUsers = User::with(['role'])
            ->whereHas('role', function ($query) {
                $query->whereIn('name', ['admin', 'staff']);
            })
            ->orderByDesc('id')
            ->get();

        return view('admin.staff.index', compact('staffRoles', 'staffUsers'));
    }

    public function registeredUsers()
    {
        try {
            $usersQuery = User::with(['role'])->orderByDesc('id');
            $superAdminRoleId = Role::where('name', 'super_admin')->value('id');
            if ($superAdminRoleId) {
                $usersQuery->where('role_id', '!=', $superAdminRoleId);
            }

            $users = $usersQuery->get();
        } catch (\Throwable $e) {
            $users = collect();
            report($e);
        }

        return view('admin.registered-users.index', compact('users'));
    }

    /**
     * Read-only Customer 360 page: search by email or WhatsApp name and see profile, orders, shipments, and invoices.
     */
    public function customer360(Request $request)
    {
        $query = trim((string) $request->query('q'));
        $orderStatus = trim((string) $request->query('order_status'));
        $user = null;
        $orders = collect();
        $shipments = collect();
        $invoices = collect();
        $approxOutstanding = 0.0;

        if ($query !== '') {
            $user = User::where('email', $query)
                ->orWhere('username', $query)
                ->orWhere('username', 'like', '%' . $query . '%')
                ->orWhere('name', 'like', '%' . $query . '%')
                ->orderByDesc('id')
                ->first();

            if ($user) {
                $orders = Order::with(['shipment.currentStage'])
                    ->where('user_id', $user->id)
                    ->orderByDesc('id');

                if (in_array($orderStatus, ['pending_approval', 'processing', 'shipped', 'delivered', 'cancelled', 'pending'], true)) {
                    $orders->where('status', $orderStatus);
                }

                $orders = $orders->get();

                $shipments = $orders->pluck('shipment')->filter()->unique('id')->values();

                $invoices = Invoice::where('user_id', $user->id)
                    ->orderByDesc('id')
                    ->get();

                $approxOutstanding = (float) $orders->sum(function (Order $order): float {
                    return (float) ($order->outstanding_balance_ngn ?? 0);
                });
            }
        }

        return view('admin.customers.show', [
            'query' => $query,
            'orderStatus' => $orderStatus,
            'user' => $user,
            'orders' => $orders,
            'shipments' => $shipments,
            'invoices' => $invoices,
            'approxOutstanding' => $approxOutstanding,
        ]);
    }

    public function assignRoleByEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($request->integer('role_id'));
        if (!in_array($role->name, ['admin', 'staff'], true)) {
            abort(403, 'Only admin or staff roles can be assigned here.');
        }

        $user = User::where('email', $request->input('email'))->firstOrFail();
        if ($user->hasRole('super_admin')) {
            abort(403, 'Super admin account cannot be reassigned from this panel.');
        }

        $before = $user->getSafeAttributesForAudit();
        $user->assignRole($role);
        Audit::log('assign_role', 'users', $user->id, $before, $user->fresh()->getSafeAttributesForAudit());

        return back()->with('success', 'Role assigned successfully.');
    }

    public function removeRole(User $user): RedirectResponse
    {
        if ($user->hasRole('super_admin')) {
            abort(403, 'Super admin role cannot be changed here.');
        }

        $customerRole = Role::where('name', 'customer')->first();
        if (!$customerRole) {
            return back()->with('error', 'Customer role is missing.');
        }

        $before = $user->toArray();
        $user->assignRole($customerRole);
        Audit::log('remove_staff_role', 'users', $user->id, $before, $user->fresh()->toArray());

        return back()->with('success', 'Role removed. User is now a customer.');
    }

    public function approve(User $user): RedirectResponse
    {
        if ($user->hasRole('super_admin')) {
            abort(403, 'Super admin approval cannot be changed here.');
        }

        $before = $user->getSafeAttributesForAudit();
        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        Audit::log('approve_user', 'users', $user->id, $before, $user->fresh()->getSafeAttributesForAudit());

        return back()->with('success', 'User approved successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->hasRole('super_admin')) {
            abort(403, 'Super admin account cannot be deleted from this panel.');
        }

        if ((int) auth()->id() === (int) $user->id) {
            abort(403, 'You cannot delete your own account from this panel.');
        }

        $before = $user->getSafeAttributesForAudit();
        $user->delete();
        Audit::log('delete_user', 'users', $user->id, $before, null);

        return back()->with('success', 'User deleted successfully.');
    }
}
