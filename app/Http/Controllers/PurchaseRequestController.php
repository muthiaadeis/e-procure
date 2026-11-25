<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Draft;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['creator', 'approvals', 'purchaseOrder']);

        $search = trim((string) $request->query('search'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_request', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('client', 'like', "%{$search}%")
                    ->orWhere('job_no', 'like', "%{$search}%");
            });
        }

        // User dengan role approval tidak dapat melihat PR yang masih berstatus Draft (belum diajukan/ditandatangani pembuat)
        if (! auth()->user()->isInput() && ! auth()->user()->isAdmin()) {
            $query->whereHas('approvals', function ($q) {
                $q->whereNotNull('signed_at');
            });
        }

        $purchaseRequests = $query->latest('date')->paginate(15)->withQueryString();

        return view('purchase_requests.index', compact('purchaseRequests', 'search'));
    }

    public function create(Request $request)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to create a Purchase Request.");

        $fromPurchaseOrder = null;
        $prefill = [];

        if ($request->filled('from_po')) {
            $fromPurchaseOrder = PurchaseOrder::with('items')->find($request->query('from_po'));

            if ($fromPurchaseOrder) {
                abort_if(
                    $fromPurchaseOrder->is_rejected || ! $fromPurchaseOrder->is_approved,
                    403,
                    'Purchase Request hanya dapat dibuat dari Purchase Order yang telah disetujui penuh (Done) dan tidak ditolak.'
                );

                $vendorName = $fromPurchaseOrder->to_address ? strtok($fromPurchaseOrder->to_address, "\n") : '';

                $prefill = [
                    'purchase_order_id' => $fromPurchaseOrder->id,
                    'title' => 'Item Allocation & Documentation for ' . $fromPurchaseOrder->po_no . ($fromPurchaseOrder->subject ? ' - ' . $fromPurchaseOrder->subject : ''),
                    'client' => $fromPurchaseOrder->client ?? '',
                    'charge_to' => $fromPurchaseOrder->charge_to ?? '',
                    'note' => 'Internal documentation for items ordered in ' . $fromPurchaseOrder->po_no . ($vendorName ? ' from ' . $vendorName : '') . '.' . ($fromPurchaseOrder->project_description ? ' Order details: ' . $fromPurchaseOrder->project_description : ''),
                    'use_ppn' => $fromPurchaseOrder->use_ppn,
                    'ppn_percent' => (string) $fromPurchaseOrder->ppn_percent,
                    'items' => $fromPurchaseOrder->items->map(fn ($item) => [
                        'description' => $item->description,
                        'line_table' => '',
                        'line_sub_table' => '',
                        'qty' => (string) (int) round((float) $item->qty),
                        'unit' => $item->uom ?? 'Pcs',
                        'price' => (string) (int) round((float) $item->price),
                        'remarks' => '',
                    ])->all(),
                ];
            }
        }

        $defaultNoRequest = PurchaseRequest::generateNextNoRequest();
        $isEdit = false;

        return view('purchase_requests.create', compact('fromPurchaseOrder', 'prefill', 'defaultNoRequest', 'isEdit'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to create a Purchase Request.");

        $validated = $this->validatePr($request);

        if (!empty($validated['purchase_order_id'])) {
            $po = PurchaseOrder::find($validated['purchase_order_id']);
            if ($po) {
                abort_if(
                    $po->is_rejected || ! $po->is_approved,
                    403,
                    'Purchase Request hanya dapat dibuat dari Purchase Order yang telah disetujui penuh (Done) dan tidak ditolak.'
                );
            }
        }

        $purchaseRequest = DB::transaction(function () use ($validated) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $noRequest = PurchaseRequest::generateNextNoRequest();

            $pr = PurchaseRequest::create([
                'no_request' => $noRequest,
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'date' => now(),
                'title' => $validated['title'],
                'job_location' => $validated['job_location'] ?? null,
                'client' => $validated['client'] ?? null,
                'charge_to' => $validated['charge_to'] ?? null,
                'job_no' => $validated['job_no'] ?? null,
                'location_project' => $validated['location_project'] ?? null,
                'note' => $validated['note'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
                'created_by' => auth()->id(),
                'created_signature' => $validated['signature'],
            ]);

            foreach ($itemsData as $item) {
                $pr->items()->create($item);
            }

            // Otomatis daftarkan tanda tangan inputer sebagai Tahap 1 (Prepared By)
            \App\Models\DocumentApproval::create([
                'document_type' => 'pr',
                'document_id' => $pr->id,
                'step' => 1,
                'label' => 'Prepared By',
                'user_id' => auth()->id(),
                'signature' => $validated['signature'],
                'signed_at' => now(),
            ]);

            // Otomatis terapkan alur persetujuan dari template default jika sudah diatur
            \App\Models\ApprovalTemplate::applyTemplateTo('pr', $pr->id);

            return $pr;
        });

        Draft::forget('purchase-requests', $request->input('purchase_order_id'));

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Dokumen Purchase Request berhasil dibuat dan diajukan untuk persetujuan.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        if (! auth()->user()->isInput() && ! auth()->user()->isAdmin() && $purchaseRequest->is_draft) {
            abort(403, 'Document is still a draft and has not been submitted for approval.');
        }

        $purchaseRequest->load(['items', 'approvals.user', 'creator', 'purchaseOrder']);

        return view('purchase_requests.show', compact('purchaseRequest'));
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        abort_unless(auth()->user()->isInput() || auth()->user()->isAdmin(), 403, "You don't have permission to edit this Purchase Request.");
        abort_if($purchaseRequest->is_approved, 403, "Dokumen Purchase Request yang sudah disetujui / selesai (done) tidak dapat diedit lagi.");
        abort_unless($purchaseRequest->is_rejected, 403, "Purchase Request hanya dapat diedit setelah ditolak.");

        $purchaseRequest->load(['items', 'approvals.user', 'creator', 'purchaseOrder']);

        $itemsForJs = old('items') ?: $purchaseRequest->items->map(fn($i) => [
            'description'    => $i->description,
            'line_table'     => $i->line_table,
            'line_sub_table' => $i->line_sub_table,
            'qty'            => (string) $i->qty,
            'unit'           => $i->unit,
            'price'          => (string) $i->price,
            'remarks'        => $i->remarks,
        ])->values()->all();

        $isEdit = true;

        return view('purchase_requests.edit', compact('purchaseRequest', 'itemsForJs', 'isEdit'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        abort_unless(auth()->user()->isInput() || auth()->user()->isAdmin(), 403, "You don't have permission to edit this Purchase Request.");
        abort_if($purchaseRequest->is_approved, 403, "Dokumen Purchase Request yang sudah disetujui / selesai (done) tidak dapat diedit lagi.");
        abort_unless($purchaseRequest->is_rejected, 403, "Purchase Request hanya dapat diedit setelah ditolak.");

        $validated = $this->validatePr($request, $purchaseRequest->id);
        $wasRejected = $purchaseRequest->is_rejected;

        DB::transaction(function () use ($validated, $purchaseRequest, $wasRejected) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $updateData = [
                'title' => $validated['title'],
                'job_location' => $validated['job_location'] ?? null,
                'client' => $validated['client'] ?? null,
                'charge_to' => $validated['charge_to'] ?? null,
                'job_no' => $validated['job_no'] ?? null,
                'location_project' => $validated['location_project'] ?? null,
                'note' => $validated['note'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
            ];

            if (! empty($validated['signature'])) {
                $updateData['created_signature'] = $validated['signature'];
            }

            $purchaseRequest->update($updateData);

            $purchaseRequest->items()->delete();
            foreach ($itemsData as $item) {
                $purchaseRequest->items()->create($item);
            }

            if ($wasRejected) {
                // Reset semua tanda tangan persetujuan approver (step > 1) dan bersihkan catatan penolakan
                $purchaseRequest->approvals()->where('step', '>', 1)->update([
                    'signature' => null,
                    'signed_at' => null,
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]);
                $purchaseRequest->approvals()->update([
                    'rejected_at' => null,
                    'rejection_reason' => null,
                ]);
                // Refresh waktu Step 1 (Prepared By)
                $step1Data = ['signed_at' => now()];
                if (! empty($validated['signature'])) {
                    $step1Data['signature'] = $validated['signature'];
                }
                $purchaseRequest->approvals()->where('step', 1)->update($step1Data);
            }
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Dokumen Purchase Request berhasil diperbarui dan diajukan ulang untuk persetujuan dari awal.');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        abort(403, 'Dokumen Purchase Request tidak dapat dihapus.');
    }

    // Buka halaman cetak A4 (Save as PDF lewat window.print()).
    public function printPdf(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['items', 'approvals.user', 'creator']);

        return view('purchase_requests.print', compact('purchaseRequest'));
    }

    // Tanda tangan digital satu kotak approval (canvas -> base64 PNG).
    public function sign(Request $request, PurchaseRequest $purchaseRequest, $approvalId)
    {
        $approval = $purchaseRequest->approvals()->findOrFail($approvalId);

        $user = auth()->user();
        $canSignPrepared = $approval->stage === 'prepared' && $user && $user->id === $purchaseRequest->created_by;
        abort_unless(
            $user && ($user->canSignApproval($approval->role_label) || $canSignPrepared),
            403,
            'Only authorized approvers can digitally sign this Purchase Request.'
        );

        abort_if($approval->isSigned(), 403, 'This approval box has already been signed.');

        // Tahap sebelumnya (sort_order lebih kecil) harus selesai semua dulu.
        $stillLocked = $purchaseRequest->approvals()
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

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Signed successfully: ' . $approval->role_label . '.');
    }

    private function validatePr(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'title' => 'required|string|max:255',
            'job_location' => 'nullable|string|max:255',
            'client' => 'nullable|string|max:255',
            'charge_to' => 'nullable|string|max:255',
            'job_no' => 'nullable|string|max:100',
            'location_project' => 'nullable|string|max:255',
            'note' => 'nullable|string',

            'use_ppn' => 'nullable|boolean',
            'ppn_percent' => 'nullable|numeric|min:0|max:100',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.line_table' => 'nullable|string|max:255',
            'items.*.line_sub_table' => 'nullable|string|max:255',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.remarks' => 'nullable|string',

            // Tanda tangan pembuat (Prepared By) wajib diisi saat membuat PR baru, opsional saat edit
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
                'line_table' => $item['line_table'] ?? null,
                'line_sub_table' => $item['line_sub_table'] ?? null,
                'qty' => $qty,
                'unit' => $item['unit'],
                'price' => $price,
                'total_price' => $totalPrice,
                'remarks' => $item['remarks'] ?? null,
            ];
        }

        return [$subtotal, $itemsData];
    }

    // PPN 11% bersifat opsional (checkbox). Persentasenya bisa diubah kalau
    // suatu saat tarifnya berubah, tapi defaultnya 11%.
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
