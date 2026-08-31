<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
{
    $search = $request->query('search');

    $vendors = Vendor::query()
        ->when($search, function ($query) use ($search) {
            $query->where('vendor_code', 'like', "%{$search}%")
                ->orWhere('vendor_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    if ($request->ajax() || $request->wantsJson()) {
        return view('vendors._table', compact('vendors', 'search'));
    }

    return view('vendors.index', compact('vendors', 'search'));
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateVendor($request);

        Vendor::create([
            'vendor_code' => Vendor::generateNextCode(),
            'vendor_name' => $validated['vendor_name'],
            'vendor_address' => $validated['vendor_address'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('vendors.index')->with('success', 'Vendor added successfully.');
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $this->validateVendor($request, $vendor->id);

        // vendor_code auto & tetap, tidak diubah lewat form
        $vendor->update([
            'vendor_name' => $validated['vendor_name'],
            'vendor_address' => $validated['vendor_address'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    private function validateVendor(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'vendor_name' => 'required|string|max:255',
            'vendor_address' => 'nullable|string',
            'email' => 'nullable|email|max:255|unique:vendors,email' . ($ignoreId ? ",$ignoreId" : ''),
            'phone' => 'nullable|string|max:50',
        ]);
    }
}
