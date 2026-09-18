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
        $rlps = Rlp::with(
            'items.jobCode',
            'items.vendors.vendor',
            'items.selectedVendor',
            'creator',
            'reviewer',
            'acknowledger',
            'approver',
            'purchaseOrder'
        )->latest()->paginate(10);

        return view('rlps.index', compact('rlps'));
    }

    public function create()
    {
        $vendorOptions = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name']);
        $jobCodeOptions = JobCode::orderBy('job_code')->get(['id', 'job_code', 'description', 'price', 'part_number']);

        return view('rlps.create', compact('vendorOptions', 'jobCodeOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRlp($request);

        $rlp = DB::transaction(function () use ($validated) {
            $rlp = Rlp::create([
                'no_rlp' => $validated['no_rlp'],
                'date' => now(), // tanggal otomatis, bukan input manual
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($rlp, $validated['items']);
            $this->syncCosts($rlp, $validated['costs'] ?? []);

            return $rlp;
        });

        return redirect()->route('rlps.index', ['auto_open' => $rlp->id, 'auto_sign' => 1])->with('success', 'RRP added successfully.');
    }

    public function edit(Rlp $rlp)
    {
        $rlp->load('items.jobCode', 'items.vendors', 'costs');
        $vendorOptions = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name']);
        $jobCodeOptions = JobCode::orderBy('job_code')->get(['id', 'job_code', 'description', 'price', 'part_number']);

        return view('rlps.edit', compact('rlp', 'vendorOptions', 'jobCodeOptions'));
    }

    public function update(Request $request, Rlp $rlp)
    {
        $validated = $this->validateRlp($request, $rlp->id);

        DB::transaction(function () use ($validated, $rlp) {
            $rlp->items()->delete(); // cascade otomatis hapus rlp_vendors di bawahnya
            $rlp->costs()->delete();

            $rlp->update([
                'no_rlp' => $validated['no_rlp'],
                // 'date' sengaja tidak diubah — tanggal RRP tetap tanggal pertama kali dibuat.
            ]);

            $this->syncItems($rlp, $validated['items']);
            $this->syncCosts($rlp, $validated['costs'] ?? []);
        });

        return redirect()->route('rlps.index')->with('success', 'RRP updated successfully.');
    }



    public function destroy(Rlp $rlp)
    {
        $rlp->delete();

        return redirect()->route('rlps.index')->with('success', 'RRP deleted successfully.');
    }

    public function printPdf(Rlp $rlp)
    {
        $rlp->load('items.jobCode', 'items.selectedVendor', 'costs', 'creator', 'reviewer', 'acknowledger', 'approver');

        return view('rlps.print', compact('rlp'));
    }

    public function show(Rlp $rlp)
    {
        return redirect()->route('rlps.index', ['auto_open' => $rlp->id]);
    }

    public function signPrepared(Request $request, Rlp $rlp)
    {
        $user = auth()->user();
        abort_unless($user->id === $rlp->created_by || $user->isAdmin() || $user->isApprover(), 403, "Only the creator or an administrator can sign this request.");

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $rlp->update([
            'created_signature' => $validated['signature'],
        ]);

        return back()->with('success', 'Signature recorded successfully.');
    }

    // Tahap 1: Review By
    public function review(Request $request, Rlp $rlp)
    {
        $user = auth()->user();

        abort_unless($user->isRlpReviewer() || $user->isAdmin() || $user->isApprover(), 403, "You don't have permission to review this RRP.");
        abort_unless($rlp->created_signature, 403, "This RRP hasn't been signed by the preparer yet.");
        abort_if($rlp->is_reviewed, 403, 'This RRP has already been reviewed.');

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $rlp->update([
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'reviewed_signature' => $validated['signature'],
        ]);

        return redirect()->route('rlps.index')->with('success', 'RRP reviewed and signed successfully.');
    }

    // Tahap 2: Acknowledge By
    public function acknowledge(Request $request, Rlp $rlp)
    {
        $user = auth()->user();

        abort_unless($user->isRlpAcknowledger() || $user->isAdmin() || $user->isApprover(), 403, "You don't have permission to acknowledge this RRP.");
        abort_unless($rlp->is_reviewed, 403, "This RRP hasn't been reviewed yet.");
        abort_if($rlp->is_acknowledged, 403, 'This RRP has already been acknowledged.');

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $rlp->update([
            'acknowledged_by' => $user->id,
            'acknowledged_at' => now(),
            'acknowledged_signature' => $validated['signature'],
        ]);

        return redirect()->route('rlps.index')->with('success', 'RRP acknowledged and signed successfully.');
    }

    // Tahap 3: Approved By
    public function approve(Request $request, Rlp $rlp)
    {
        $user = auth()->user();

        abort_unless($user->isRlpApprover() || $user->isAdmin() || $user->isApprover(), 403, "You don't have permission to approve this RRP.");
        abort_unless($rlp->is_acknowledged, 403, "This RRP hasn't been acknowledged yet.");
        abort_if($rlp->is_approved, 403, 'This RRP has already been approved.');

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $rlp->update([
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approved_signature' => $validated['signature'],
        ]);

        return redirect()->route('rlps.index')->with('success', 'RRP approved and signed successfully.');
    }

    private function validateRlp(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'no_rlp' => 'required|string|max:50|unique:rlps,no_rlp' . ($ignoreId ? ",$ignoreId" : ''),

            'items' => 'required|array|min:1',
            'items.*.job_code_id' => 'required|exists:job_codes,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.uom' => 'required|string|max:50',
            'items.*.selected_vendor_index' => 'nullable|integer|min:0',

            'items.*.vendors' => 'required|array|min:1',
            'items.*.vendors.*.vendor_id' => 'required|exists:vendors,id',
            'items.*.vendors.*.u_price' => 'required|numeric|min:0',
            'items.*.vendors.*.delivery_estimate' => 'required|string|max:100',
            'items.*.vendors.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.vendors.*.use_ppn' => 'nullable|boolean',

            'costs' => 'nullable|array',
            'costs.*.job_description' => 'nullable|string|max:255',
            'costs.*.quantity' => 'nullable|integer|min:0',
            'costs.*.u_price' => 'nullable|numeric|min:0',
        ]);
    }

    private function syncItems(Rlp $rlp, array $items): void
    {
        $totalCatalogExt = 0;
        $totalRevenueExt = 0;

        foreach ($items as $itemData) {
            // Description, PN, dan harga catalog SELALU diambil dari master Job Code —
            // bukan dari input form — supaya konsisten dan gak bisa dipalsukan dari client.
            $jobCode = JobCode::find($itemData['job_code_id']);
            $uPrice = $jobCode->price ?? 0;
            $catalogExt = $uPrice * $itemData['qty'];
            $totalCatalogExt += $catalogExt;

            $item = $rlp->items()->create([
                'job_code_id' => $itemData['job_code_id'],
                'description' => $jobCode->description ?? '-',
                'pn' => $jobCode->part_number ?? null,
                'qty' => $itemData['qty'],
                'uom' => $itemData['uom'],
                'part_catalog_u_price' => $uPrice,
                'part_catalog_ext_price' => $catalogExt,
            ]);

            $vendorModels = [];
            foreach ($itemData['vendors'] as $vendorData) {
                $vendorMaster = Vendor::find($vendorData['vendor_id']);
                $extPrice = $vendorData['u_price'] * $itemData['qty'];

                $vendorModels[] = $item->vendors()->create([
                    'vendor_id' => $vendorData['vendor_id'],
                    'vendor_name' => $vendorMaster?->vendor_name ?? '-',
                    'u_price' => $vendorData['u_price'],
                    'ext_price' => $extPrice,
                    'delivery_estimate' => $vendorData['delivery_estimate'],
                    'discount_percent' => $vendorData['discount_percent'] ?? null,
                    'use_ppn' => !empty($vendorData['use_ppn']),
                ]);
            }

            $selectedIndex = $itemData['selected_vendor_index'] ?? null;
            $revenueExt = 0;

            if ($selectedIndex !== null && isset($vendorModels[$selectedIndex])) {
                $selectedVendor = $vendorModels[$selectedIndex];

                // Ext/Price tetap disimpan mentah (qty x harga), tapi Revenue dihitung
                // dari harga FINAL vendor terpilih setelah Discount % dan PPN 11% (kalau diisi).
                $finalExt = (float) $selectedVendor->ext_price;
                if ($selectedVendor->discount_percent) {
                    $finalExt -= $finalExt * ((float) $selectedVendor->discount_percent / 100);
                }
                if ($selectedVendor->use_ppn) {
                    $finalExt += $finalExt * 0.11;
                }

                // Revenue = harga part catalog dikurang harga final vendor terpilih
                // (selisih antara harga jual/katalog dengan harga beli ke vendor).
                $revenueExt = $catalogExt - $finalExt;

                $item->update([
                    'selected_vendor_id' => $selectedVendor->id,
                    'revenue_ext_price' => $revenueExt,
                ]);
            }

            $totalRevenueExt += $revenueExt;
        }

        $rlp->update([
            'part_catalog_ext_price' => $totalCatalogExt,
            'revenue_ext_price' => $totalRevenueExt,
        ]);
    }

    private function syncCosts(Rlp $rlp, array $costs): void
    {
        $grandTotal = 0;

        foreach ($costs as $cost) {
            $jobDescription = trim($cost['job_description'] ?? '');
            $quantity = $cost['quantity'] ?? 0;
            $uPrice = $cost['u_price'] ?? 0;

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
}
