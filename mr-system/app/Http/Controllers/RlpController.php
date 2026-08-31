<?php

namespace App\Http\Controllers;

use App\Models\JobCode;
use App\Models\Rlp;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RlpController extends Controller
{
    public function index()
    {
        $rlps = Rlp::with('selectedVendor', 'items.jobCode')->latest()->paginate(10);

        return view('rlps.index', compact('rlps'));
    }

    public function create()
    {
        $vendorOptions = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name']);
        $jobCodeOptions = JobCode::orderBy('job_code')->get(['id', 'job_code', 'description', 'part_number', 'price']);

        return view('rlps.create', compact('vendorOptions', 'jobCodeOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRlp($request);

        DB::transaction(function () use ($validated) {
            $rlp = Rlp::create([
                'no_rlp' => $validated['no_rlp'],
                'date' => $validated['date'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($rlp, $validated['items']);
            $this->syncVendors($rlp, $validated['vendors']);
            $this->syncCosts($rlp, $validated['costs'] ?? []);
            $this->applySelectedVendor($rlp, $validated['selected_vendor_index'] ?? null);
        });

        return redirect()->route('rlps.index')->with('success', 'RRP added successfully.');
    }

    public function edit(Rlp $rlp)
    {
        $rlp->load('items.jobCode', 'vendors', 'costs');
        $vendorOptions = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name']);
        $jobCodeOptions = JobCode::orderBy('job_code')->get(['id', 'job_code', 'description', 'part_number', 'price']);

        return view('rlps.edit', compact('rlp', 'vendorOptions', 'jobCodeOptions'));
    }

    public function update(Request $request, Rlp $rlp)
    {
        $validated = $this->validateRlp($request, $rlp->id);

        DB::transaction(function () use ($validated, $rlp) {
            // Lepas dulu referensi selected_vendor supaya vendor lama aman dihapus
            $rlp->update(['selected_vendor_id' => null]);
            $rlp->items()->delete();
            $rlp->vendors()->delete();
            $rlp->costs()->delete();

            $rlp->update([
                'no_rlp' => $validated['no_rlp'],
                'date' => $validated['date'] ?? null,
            ]);

            $this->syncItems($rlp, $validated['items']);
            $this->syncVendors($rlp, $validated['vendors']);
            $this->syncCosts($rlp, $validated['costs'] ?? []);
            $this->applySelectedVendor($rlp, $validated['selected_vendor_index'] ?? null);
        });

        return redirect()->route('rlps.index')->with('success', 'RRP updated successfully.');
    }

    public function destroy(Rlp $rlp)
    {
        $rlp->delete();

        return redirect()->route('rlps.index')->with('success', 'RRP deleted successfully.');
    }

    private function validateRlp(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'no_rlp' => 'required|string|max:50|unique:rlps,no_rlp' . ($ignoreId ? ",$ignoreId" : ''),
            'date' => 'nullable|date',

            // Satu RLP sekarang bisa punya banyak baris item (deskripsi, PN, qty, uom, job code, part catalog)
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.pn' => 'nullable|string|max:100',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.uom' => 'required|string|max:50',
            'items.*.job_code_id' => 'nullable|exists:job_codes,id',
            'items.*.part_catalog_u_price' => 'required|numeric|min:0',

            'vendors' => 'required|array|min:1',
            'vendors.*.vendor_id' => 'required|exists:vendors,id',
            'vendors.*.item_name' => 'required|string|max:255',
            'vendors.*.brand' => 'required|string|max:255',
            'vendors.*.part_number' => 'nullable|string|max:100',
            'vendors.*.qty' => 'required|integer|min:1',
            'vendors.*.u_price' => 'required|numeric|min:0',
            'vendors.*.delivery_estimate' => 'required|string|max:100',
            'selected_vendor_index' => 'nullable|integer|min:0',

            'costs' => 'nullable|array',
            'costs.*.job_description' => 'nullable|string|max:255',
            'costs.*.quantity' => 'nullable|numeric|min:0',
            'costs.*.u_price' => 'nullable|numeric|min:0',
        ]);
    }

    private function syncItems(Rlp $rlp, array $items): void
    {
        $totalCatalogExt = 0;

        foreach ($items as $item) {
            $extPrice = $item['part_catalog_u_price'] * $item['qty'];
            $totalCatalogExt += $extPrice;

            $rlp->items()->create([
                'description' => $item['description'],
                'pn' => $item['pn'] ?? null,
                'qty' => $item['qty'],
                'uom' => $item['uom'],
                'job_code_id' => $item['job_code_id'] ?? null,
                'part_catalog_u_price' => $item['part_catalog_u_price'],
                'part_catalog_ext_price' => $extPrice,
            ]);
        }

        $rlp->update(['part_catalog_ext_price' => $totalCatalogExt]);
    }

    private function syncVendors(Rlp $rlp, array $vendors): void
    {
        foreach ($vendors as $vendor) {
            $vendorMaster = Vendor::find($vendor['vendor_id']);

            $rlp->vendors()->create([
                'vendor_id' => $vendor['vendor_id'],
                'vendor_name' => $vendorMaster?->vendor_name ?? '-',
                'item_name' => $vendor['item_name'],
                'brand' => $vendor['brand'],
                'part_number' => $vendor['part_number'] ?? null,
                'qty' => $vendor['qty'],
                'u_price' => $vendor['u_price'],
                'ext_price' => $vendor['u_price'] * $vendor['qty'],
                'delivery_estimate' => $vendor['delivery_estimate'],
            ]);
        }
    }

    private function syncCosts(Rlp $rlp, array $costs): void
    {
        $grandTotal = 0;

        foreach ($costs as $cost) {
            $jobDescription = trim($cost['job_description'] ?? '');
            $quantity = $cost['quantity'] ?? 0;
            $uPrice = $cost['u_price'] ?? 0;

            // Lewati baris kosong (tidak diisi sama sekali)
            if ($jobDescription === '' && (float) $quantity === 0.0 && (float) $uPrice === 0.0) {
                continue;
            }

            $total = $quantity * $uPrice;
            $grandTotal += $total;

            $rlp->costs()->create([
                'job_description' => $jobDescription !== '' ? $jobDescription : '-',
                'quantity' => $quantity,
                'u_price' => $uPrice,
                'total' => $total,
            ]);
        }

        $rlp->update(['wur_grand_total' => $grandTotal]);
    }

    private function applySelectedVendor(Rlp $rlp, $index): void
    {
        $vendors = $rlp->vendors()->orderBy('id')->get();

        if ($index === null || !isset($vendors[$index])) {
            $rlp->update([
                'selected_vendor_id' => null,
                'revenue_ext_price' => 0,
            ]);

            return;
        }

        $selected = $vendors[$index];

        // Revenue = harga vendor terpilih (Ext) dikurangi total harga part catalog (Ext)
        $rlp->update([
            'selected_vendor_id' => $selected->id,
            'revenue_ext_price' => $selected->ext_price - $rlp->part_catalog_ext_price,
        ]);
    }
}
