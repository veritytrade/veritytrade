<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DealController extends Controller
{
    // Show all deals (admin index)
    public function index()
    {
        // Automatically remove expired deals
        Deal::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $deals = Deal::with('images')
                    ->orderBy('position', 'desc')
                    ->orderBy('expires_at', 'desc')
                    ->get();

        return view('admin.deals.index', compact('deals'));
    }

    // Show create form
    public function create()
    {
        return view('admin.deals.create');
    }

    // Store new deal
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price_display' => 'nullable|string|max:100',
            'whatsapp_message' => 'nullable|string|max:500',
            'expires_at' => 'required|date|after:now',
            'is_active' => 'nullable|boolean',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // --- CLEAN DESCRIPTION LOGIC (remove Model/Price lines) ---
        $cleanDesc = preg_replace('/^Model\s*[:\-].*$/im', '', $request->description);
        $cleanDesc = preg_replace('/^(?:Price|Cost|Amount)\s*[:\-].*$/im', '', $cleanDesc);
        $cleanDesc = preg_replace('/^[^\S\n]*[📱💰⚠️✅]*\s*(?:Price|Cost|Amount)\s*[:\-].*$/imu', '', $cleanDesc);
        $cleanDesc = preg_replace('/^\s+|\s+$/m', '', $cleanDesc);
        $cleanDesc = preg_replace('/\n{2,}/', "\n", $cleanDesc);
        $cleanDesc = trim($cleanDesc);

        // ✅ FIXED: Save ACTUAL expires_at value (was validation string)
        $deal = Deal::create([
            'title' => $request->title,
            'description' => $cleanDesc,
            'price_display' => $request->price_display,
            'whatsapp_message' => $request->whatsapp_message,
            'expires_at' => $request->expires_at, // ✅ CRITICAL FIX
            // New deals default to active unless explicitly unchecked.
            'is_active' => $request->boolean('is_active', true),
            'position' => Deal::max('position') + 1,
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('deals', 'public');
                
                DealImage::create([
                    'deal_id' => $deal->id,
                    'image_path' => $path,
                    'position' => $index,
                ]);
            }
        }

        return redirect()->route('admin.deals.index')
                       ->with('success', 'Hot deal created successfully!');
    }

    // Show edit form
    public function edit(Deal $deal)
    {
        return view('admin.deals.edit', compact('deal'));
    }

    // Update deal
    public function update(Request $request, Deal $deal)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price_display' => 'nullable|string|max:100',
            'whatsapp_message' => 'nullable|string|max:500',
            'expires_at' => 'required|date|after:now',
            'is_active' => 'nullable|boolean',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'expires_at.after' => 'Expiry date must be in the future',
        ]);

        // --- CLEAN DESCRIPTION LOGIC ---
        $cleanDesc = preg_replace('/^Model\s*[:\-].*$/im', '', $request->description);
        $cleanDesc = preg_replace('/^(?:Price|Cost|Amount)\s*[:\-].*$/im', '', $cleanDesc);
        $cleanDesc = preg_replace('/^[^\S\n]*[📱💰⚠️✅]*\s*(?:Price|Cost|Amount)\s*[:\-].*$/imu', '', $cleanDesc);
        $cleanDesc = preg_replace('/^\s+|\s+$/m', '', $cleanDesc);
        $cleanDesc = preg_replace('/\n{2,}/', "\n", $cleanDesc);
        $cleanDesc = trim($cleanDesc);

        // ✅ FIXED: Added expires_at (was MISSING)
        $deal->update([
            'title' => $request->title,
            'description' => $cleanDesc,
            'price_display' => $request->price_display,
            'whatsapp_message' => $request->whatsapp_message,
            'expires_at' => $request->expires_at, // ✅ CRITICAL FIX
            // Keep or update active flag; default to true if not present.
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('deals', 'public');
                
                DealImage::create([
                    'deal_id' => $deal->id,
                    'image_path' => $path,
                    'position' => $deal->images->count() + $index,
                ]);
            }
        }

        return redirect()->route('admin.deals.edit', $deal)
                       ->with('success', 'Hot deal updated successfully!');
    }

    // Delete deal
    public function destroy(Deal $deal)
    {
        $deal->delete();
        return back()->with('success', 'Hot deal deleted.');
    }

    // Toggle active status
    public function toggle(Deal $deal)
    {
        $deal->is_active = !$deal->is_active;
        $deal->save();
        return back()->with('success', 'Status updated.');
    }

    // Delete single image
    public function deleteImage(Deal $deal, int $imageId)
    {
        $image = DealImage::query()
            ->where('deal_id', $deal->id)
            ->where('id', $imageId)
            ->firstOrFail();

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
        
        $image->delete();
        return back()->with('success', 'Image deleted.');
    }
}