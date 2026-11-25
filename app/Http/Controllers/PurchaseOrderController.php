<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Rlp;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Draft;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['creator', 'approvals', 'rlp', 'purchaseRequest']);

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po_no', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('supplier_no', 'like', "%{$search}%");
            });
        }

        // User dengan role approval tidak dapat melihat PO yang masih berstatus Draft (belum diajukan/ditandatangani pembuat)
        if (! auth()->user()->isInput() && ! auth()->user()->isAdmin()) {
            $query->whereHas('approvals', function ($q) {
                $q->whereNotNull('signed_at');
            });
        }

        $purchaseOrders = $query->latest('our_order_date')->paginate(15)->withQueryString();

        return view('purchase_orders.index', compact('purchaseOrders', 'search'));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to create a Purchase Order.");

        $fromRlp = null;
        $prefill = [];
        $vendors = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name', 'vendor_code', 'vendor_address', 'phone', 'brand']);

        if ($request->filled('from_rlp')) {
            $fromRlp = Rlp::with(['items.selectedVendor.vendor', 'items.vendors.vendor', 'items.jobCode'])->find($request->query('from_rlp'));

            if ($fromRlp) {
                abort_if(
                    $fromRlp->is_rejected || ! $fromRlp->is_approved,
                    403,
                    'Purchase Order hanya dapat dibuat dari RRP yang telah disetujui penuh (Done) dan tidak ditolak.'
                );

                // Determine selected vendor details across items
                $selectedVendors = $fromRlp->items->map(fn ($i) => $i->selectedVendor)->filter();
                $firstSelectedVendor = $selectedVendors->first();
                $vendorModel = $firstSelectedVendor?->vendor;

                $vendorName = $firstSelectedVendor?->vendor_name ?? ($vendorModel?->vendor_name ?? '');
                $vendorAddress = $vendorModel?->vendor_address ?? '';
                $toAddress = $vendorName;
                if ($vendorAddress) {
                    $toAddress .= "\n" . $vendorAddress;
                }

                $itemsPrefill = [];
                $hasPpn = false;
                foreach ($fromRlp->items as $item) {
                    $sel = $item->selectedVendor;
                    $itemPrice = $sel ? (float) $sel->u_price : (float) $item->part_catalog_u_price;
                    $itemBrand = $sel?->vendor?->brand ?? '';
                    if ($sel && $sel->use_ppn) {
                        $hasPpn = true;
                    }

                    $itemsPrefill[] = [
                        'description' => $item->description . ($item->pn ? ' (PN: ' . $item->pn . ')' : ''),
                        'qty' => (string) (int) round((float) $item->qty),
                        'uom' => $item->uom,
                        'brand' => $itemBrand,
                        'price' => (string) (int) round($itemPrice),
                    ];
                }

                $prefill = [
                    'rlp_id' => $fromRlp->id,
                    'to_address' => $toAddress,
                    'attn' => $vendorModel?->brand ?? '',
                    'supplier_no' => $vendorModel?->vendor_code ?? '',
                    'contact_number' => $vendorModel?->phone ?? '',
                    'subject' => 'Purchase Order - RRP ' . $fromRlp->no_rlp,
                    'project_description' => 'Procurement based on multi-vendor comparison in RRP ' . $fromRlp->no_rlp,
                    'use_ppn' => $hasPpn,
                    'items' => $itemsPrefill,
                ];
            }
        }

        $defaultPoNo = PurchaseOrder::generateNextPoNo();
        $isEdit = false;

        return view('purchase_orders.create', compact('fromRlp', 'prefill', 'vendors', 'defaultPoNo', 'isEdit'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to create a Purchase Order.");

        $validated = $this->validatePo($request);

        if (!empty($validated['rlp_id'])) {
            $rlp = Rlp::find($validated['rlp_id']);
            if ($rlp) {
                abort_if(
                    $rlp->is_rejected || ! $rlp->is_approved,
                    403,
                    'Purchase Order hanya dapat dibuat dari RRP yang telah disetujui penuh (Done) dan tidak ditolak.'
                );
            }
        }

        $purchaseOrder = DB::transaction(function () use ($validated) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $poNo = PurchaseOrder::generateNextPoNo();

            $po = PurchaseOrder::create([
                'po_no' => $poNo,
                'rlp_id' => $validated['rlp_id'] ?? null,
                'our_reference' => $validated['our_reference'] ?? null,
                'supplier_no' => $validated['supplier_no'] ?? null,
                'our_order_date' => now(),
                'revision' => $validated['revision'] ?? null,
                'to_address' => $validated['to_address'] ?? null,
                'attn' => $validated['attn'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'project_description' => $validated['project_description'] ?? null,
                'contact_number' => $validated['contact_number'] ?? null,
                'client' => $validated['client'] ?? null,
                'charge_to' => $validated['charge_to'] ?? null,
                'final_delivery_address' => $validated['final_delivery_address'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
                'created_by' => auth()->id(),
                'created_signature' => $validated['signature'],
            ]);

            foreach ($itemsData as $item) {
                $po->items()->create($item);
            }

            // Otomatis daftarkan tanda tangan inputer sebagai Tahap 1 (Prepared By)
            \App\Models\DocumentApproval::create([
                'document_type' => 'po',
                'document_id' => $po->id,
                'step' => 1,
                'label' => 'Prepared By',
                'user_id' => auth()->id(),
                'signature' => $validated['signature'],
                'signed_at' => now(),
            ]);

            // Otomatis terapkan alur persetujuan dari template default jika sudah diatur
            \App\Models\ApprovalTemplate::applyTemplateTo('po', $po->id);

            return $po;
        });

        Draft::forget('purchase-orders', $request->input('rlp_id'));

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Dokumen Purchase Order berhasil dibuat dan diajukan untuk persetujuan.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        if (! auth()->user()->isInput() && ! auth()->user()->isAdmin() && $purchaseOrder->is_draft) {
            abort(403, 'Document is still a draft and has not been submitted for approval.');
        }

        $purchaseOrder->load(['items', 'approvals.user', 'creator', 'rlp', 'purchaseRequest']);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        abort_unless(auth()->user()->isInput() || auth()->user()->isAdmin(), 403, "You don't have permission to edit this Purchase Order.");
        abort_if($purchaseOrder->is_approved, 403, "Dokumen Purchase Order yang sudah disetujui / selesai (done) tidak dapat diedit lagi.");
        abort_unless($purchaseOrder->is_rejected, 403, "Purchase Order hanya dapat diedit setelah ditolak.");

        $purchaseOrder->load(['items', 'approvals.user', 'creator', 'rlp']);
        $vendors = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name', 'vendor_code', 'vendor_address', 'phone', 'brand']);

        $itemsForJs = old('items') ?: $purchaseOrder->items->map(fn($i) => [
            'description' => $i->description,
            'qty'         => (string) $i->qty,
            'uom'         => $i->uom,
            'brand'       => $i->brand,
            'price'       => (string) $i->price,
        ])->values()->all();

        $isEdit = true;

        return view('purchase_orders.edit', compact('purchaseOrder', 'itemsForJs', 'vendors', 'isEdit'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless(auth()->user()->isInput() || auth()->user()->isAdmin(), 403, "You don't have permission to edit this Purchase Order.");
        abort_if($purchaseOrder->is_approved, 403, "Dokumen Purchase Order yang sudah disetujui / selesai (done) tidak dapat diedit lagi.");
        abort_unless($purchaseOrder->is_rejected, 403, "Purchase Order hanya dapat diedit setelah ditolak.");

        $validated = $this->validatePo($request, $purchaseOrder->id);
        $wasRejected = $purchaseOrder->is_rejected;

        DB::transaction(function () use ($validated, $purchaseOrder, $wasRejected) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $updateData = [
                'our_reference' => $validated['our_reference'] ?? null,
                'supplier_no' => $validated['supplier_no'] ?? null,
                'revision' => $validated['revision'] ?? null,
                'to_address' => $validated['to_address'] ?? null,
                'attn' => $validated['attn'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'project_description' => $validated['project_description'] ?? null,
                'contact_number' => $validated['contact_number'] ?? null,
                'client' => $validated['client'] ?? null,
                'charge_to' => $validated['charge_to'] ?? null,
                'final_delivery_address' => $validated['final_delivery_address'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
            ];

            if (! empty($validated['signature'])) {
                $updateData['created_signature'] = $validated['signature'];
            }

            $purchaseOrder->update($updateData);

            $purchaseOrder->items()->delete();
            foreach ($itemsData as $item) {
                $purchaseOrder->items()->create($item);
            }

            if ($wasRejected) {
                // Reset semua tanda tangan persetujuan approver (step > 1) dan bersihkan catatan penolakan
                $purchaseOrder->approvals()->where('step', '>', 1)->update([
                    'signature' => null,
                    'signed_at' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]);
                $purchaseOrder->approvals()->update([
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]);
                // Refresh waktu Step 1 (Prepared By)
                $step1Data = ['signed_at' => now()];
                if (! empty($validated['signature'])) {
                    $step1Data['signature'] = $validated['signature'];
                }
                $purchaseOrder->approvals()->where('step', 1)->update($step1Data);
            }
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Dokumen Purchase Order berhasil diperbarui dan diajukan ulang untuk persetujuan dari awal.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        abort(403, 'Dokumen Purchase Order tidak dapat dihapus.');
    }

    // Buka halaman cetak A4 (Save as PDF lewat window.print()).
    public function printPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'approvals.user', 'creator']);

        return view('purchase_orders.print', compact('purchaseOrder'));
    }

    // Tanda tangan digital satu kotak approval (canvas -> base64 PNG).
    public function sign(Request $request, PurchaseOrder $purchaseOrder, $approvalId)
    {
        $approval = $purchaseOrder->approvals()->findOrFail($approvalId);

        $user = auth()->user();
        $canSignPrepared = $approval->stage === 'prepared' && $user && $user->id === $purchaseOrder->created_by;
        abort_unless(
            $user && ($user->canSignApproval($approval->role_label) || $canSignPrepared),
            403,
            'Only authorized approvers can digitally sign this Purchase Order.'
        );

        abort_if($approval->isSigned(), 403, 'This approval box has already been signed.');

        $stillLocked = $purchaseOrder->approvals()
            ->where('sort_order', '<', $approval->sort_order)
            ->whereNull('signed_at')
            ->exists();
        abort_if($stillLocked, 403, 'The previous approval stage must be completed first.');

        $validated = $request->validate([
            'signature' => 'required|string',
        ]);

        $approval->update([
            'user_id' => auth()->id(),
            'signature' => $validated['signature'],
            'signed_at' => now(),
        ]);

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Signed successfully: ' . $approval->role_label . '.');
    }

    private function validatePo(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'rlp_id' => 'nullable|exists:rlps,id',
            'our_reference' => 'nullable|string|max:255',
            'supplier_no' => 'nullable|string|max:255',
            'our_order_date' => 'nullable|date',
            'revision' => 'nullable|string|max:100',

            'to_address' => 'nullable|string',
            'attn' => 'nullable|string|max:255',
            'invoice_address' => 'nullable|string',

            'subject' => 'nullable|string|max:255',
            'project_description' => 'nullable|string',
            'contact_number' => 'nullable|string|max:100',
            'client' => 'nullable|string|max:255',
            'charge_to' => 'nullable|string|max:255',

            'final_delivery_address' => 'nullable|string',

            'use_ppn' => 'nullable|boolean',
            'ppn_percent' => 'nullable|numeric|min:0|max:100',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.uom' => 'required|string|max:50',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.price' => 'required|numeric|min:0',

            // Tanda tangan pembuat (Prepared By) wajib diisi saat membuat PO baru, opsional saat edit
            'signature' => $ignoreId ? 'nullable|string' : 'required|string',
        ]);
    }

    // Hitung total_price per item (qty x price) dan subtotal keseluruhan.
    // Selalu dihitung ulang di server, tidak percaya angka total dari client.
    private function prepareItems(array $items): array
    {
        $subtotal = 0;
        $itemsData = [];

        foreach ($items as $index => $item) {
            $qty = (float) $item['qty'];
            $price = (float) $item['price'];
            $totalPrice = $qty * $price;
            $subtotal += $totalPrice;

            $itemsData[] = [
                'line_no' => $index + 1,
                'description' => $item['description'],
                'qty' => $qty,
                'uom' => $item['uom'],
                'brand' => $item['brand'] ?? null,
                'price' => $price,
                'total_price' => $totalPrice,
            ];
        }

        return [$subtotal, $itemsData];
    }

    // PPN 11% bersifat opsional (checkbox), default aktif untuk PO.
    private function calculatePpn(float $subtotal, array $validated): array
    {
        $usePpn = ! empty($validated['use_ppn']);
        $ppnPercent = $usePpn ? (float) ($validated['ppn_percent'] ?? 11) : 0;
        $ppnAmount = $usePpn ? $subtotal * ($ppnPercent / 100) : 0;

        return [
            'use_ppn' => $usePpn,
            'ppn_percent' => $usePpn ? $ppnPercent : 11.00,
            'ppn_amount' => $ppnAmount,
        ];
    }
}
