<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPaymentSlip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class OrderController extends Controller
{
    private function ensureCustomer(): ?RedirectResponse
    {
        $user = auth()->user();
        if ($user->hasRole('super_admin') || $user->hasRole('admin') || $user->hasRole('staff')) {
            return redirect()->route('admin.dashboard');
        }
        return null;
    }

    public function create(): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }
        return view('customer.orders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }

        $description = $request->input('gadget_description', '');
        $parsed = Order::parseDescription($description);

        $rules = [
            'gadget_description' => 'required|string|max:5000',
            'outstanding_balance_ngn' => 'nullable|numeric|min:0',
            'logistics_type' => 'required|string|in:within_lagos,outside_lagos,combined',
            'payment_slips.*' => [File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(5 * 1024)],
        ];

        if (! $parsed['has_price_in_description']) {
            $rules['total_amount_ngn'] = 'required|numeric|min:0';
        } else {
            $rules['total_amount_ngn'] = 'nullable|numeric|min:0';
        }

        $valid = $request->validate($rules, [
            'payment_slips.*.max' => 'Each payment slip must not exceed 5MB.',
        ]);

        $totalAmount = $parsed['has_price_in_description']
            ? ($parsed['price_ngn'] ?? (float) ($valid['total_amount_ngn'] ?? 0))
            : (float) ($valid['total_amount_ngn'] ?? 0);

        $productName = $parsed['product_name'] ?: trim(explode("\n", $description)[0] ?? '');
        if ($productName === '') {
            $productName = 'Order';
        }

        $outstanding = (float) ($valid['outstanding_balance_ngn'] ?? 0);
        $paidAmount = $totalAmount - $outstanding;

        $paymentStatus = $outstanding <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');

        $order = Order::create([
            'user_id' => auth()->id(),
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'product_name' => $productName,
            'spec_summary' => null,
            'full_description' => $description,
            'total_amount_ngn' => $totalAmount,
            'outstanding_balance_ngn' => $outstanding,
            'payment_status' => $paymentStatus,
            'logistics_type' => $valid['logistics_type'] ?? 'within_lagos',
            'status' => 'pending_approval',
        ]);

        $slips = $request->file('payment_slips');
        if (is_array($slips)) {
            $slips = array_slice(array_filter($slips), 0, 5);
            $dir = 'order_payment_slips/' . $order->id;
            foreach ($slips as $i => $file) {
                $path = $file->store($dir, 'public');
                OrderPaymentSlip::create([
                    'order_id' => $order->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('dashboard.orders')->with('status', 'Order submitted for approval. Ref: ' . $order->verity_tracking_code);
    }

    public function edit(Order $order): View|RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        if (! $order->canCustomerEdit()) {
            return redirect()->route('dashboard.orders')->with('error', 'Order can no longer be edited.');
        }
        $order->load('paymentSlips');
        return view('customer.orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        if (! $order->canCustomerEdit()) {
            return redirect()->route('dashboard.orders')->with('error', 'Order can no longer be edited.');
        }

        $description = $request->input('gadget_description', '');
        $parsed = Order::parseDescription($description);

        $rules = [
            'gadget_description' => 'required|string|max:5000',
            'outstanding_balance_ngn' => 'nullable|numeric|min:0',
            'logistics_type' => 'required|string|in:within_lagos,outside_lagos,combined',
            'payment_slips.*' => [File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])->max(5 * 1024)],
        ];

        if (! $parsed['has_price_in_description']) {
            $rules['total_amount_ngn'] = 'required|numeric|min:0';
        } else {
            $rules['total_amount_ngn'] = 'nullable|numeric|min:0';
        }

        $valid = $request->validate($rules, [
            'payment_slips.*.max' => 'Each payment slip must not exceed 5MB.',
        ]);

        $totalAmount = $parsed['has_price_in_description']
            ? ($parsed['price_ngn'] ?? (float) ($valid['total_amount_ngn'] ?? $order->total_amount_ngn))
            : (float) ($valid['total_amount_ngn'] ?? 0);

        $productName = $parsed['product_name'] ?: trim(explode("\n", $description)[0] ?? '');
        if ($productName === '') {
            $productName = 'Order';
        }

        $outstanding = (float) ($valid['outstanding_balance_ngn'] ?? 0);
        $paidAmount = $totalAmount - $outstanding;
        $paymentStatus = $outstanding <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending');

        $order->update([
            'product_name' => $productName,
            'full_description' => $description,
            'total_amount_ngn' => $totalAmount,
            'outstanding_balance_ngn' => $outstanding,
            'payment_status' => $paymentStatus,
            'logistics_type' => $valid['logistics_type'] ?? 'within_lagos',
        ]);

        $slips = $request->file('payment_slips');
        if (is_array($slips) && ! empty($slips)) {
            $existing = $order->paymentSlips()->count();
            $remaining = 5 - $existing;
            $slips = array_slice(array_filter($slips), 0, $remaining);
            $dir = 'order_payment_slips/' . $order->id;
            $sortStart = $order->paymentSlips()->max('sort_order') + 1;
            foreach ($slips as $i => $file) {
                $path = $file->store($dir, 'public');
                OrderPaymentSlip::create([
                    'order_id' => $order->id,
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'sort_order' => $sortStart + $i,
                ]);
            }
        }

        return redirect()->route('dashboard.orders')->with('status', 'Order updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        if ($redirect = $this->ensureCustomer()) {
            return $redirect;
        }
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        if (! $order->canCustomerDelete()) {
            return redirect()->route('dashboard.orders')->with('error', 'Order can no longer be deleted.');
        }

        foreach ($order->paymentSlips as $slip) {
            if (Storage::disk('public')->exists($slip->file_path)) {
                Storage::disk('public')->delete($slip->file_path);
            }
        }
        $order->delete();

        return redirect()->route('dashboard.orders')->with('status', 'Order cancelled.');
    }
}
