<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $purchaseRequests = $query->latest('date')->paginate(15)->withQueryString();

        return view('purchase_requests.index', compact('purchaseRequests', 'search'));
    }

    public function create(Request $request)
    {
        $nextNoRequest = PurchaseRequest::generateNextNoRequest();

        $fromPurchaseOrder = null;
        $prefill = [];

        if ($request->filled('from_po')) {
            $fromPurchaseOrder = PurchaseOrder::with('items')->find($request->query('from_po'));

            if ($fromPurchaseOrder) {
                $vendorName = $fromPurchaseOrder->to_address ? strtok($fromPurchaseOrder->to_address, "\n") : '';

                $prefill = [
                    'purchase_order_id' => $fromPurchaseOrder->id,
                    'title' => 'Item Allocation & Documentation for ' . $fromPurchaseOrder->po_no . ($fromPurchaseOrder->subject ? ' - ' . $fromPurchaseOrder->subject : ''),
                    'client' => $fromPurchaseOrder->client ?? '',
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

        return view('purchase_requests.create', compact('nextNoRequest', 'fromPurchaseOrder', 'prefill'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePr($request);

        $purchaseRequest = DB::transaction(function () use ($validated) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $pr = PurchaseRequest::create([
                'no_request' => PurchaseRequest::generateNextNoRequest(),
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'date' => now(),
                'title' => $validated['title'],
                'job_location' => $validated['job_location'] ?? null,
                'client' => $validated['client'] ?? null,
                'job_no' => $validated['job_no'] ?? null,
                'location_project' => $validated['location_project'] ?? null,
                'note' => $validated['note'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
                'created_by' => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $pr->items()->create($item);
            }

            $this->createApprovalSlots($pr);

            return $pr;
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase Request added successfully.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['items', 'approvals.signer', 'creator', 'purchaseOrder']);

        return view('purchase_requests.show', compact('purchaseRequest'));
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['items', 'approvals']);

        abort_unless($purchaseRequest->is_draft, 403, 'This PR can no longer be edited because the approval process has already started.');

        return view('purchase_requests.edit', compact('purchaseRequest'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load('approvals');
        abort_unless($purchaseRequest->is_draft, 403, 'This PR can no longer be edited because the approval process has already started.');

        $validated = $this->validatePr($request);

        DB::transaction(function () use ($validated, $purchaseRequest) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $purchaseRequest->update([
                'title' => $validated['title'],
                'job_location' => $validated['job_location'] ?? null,
                'client' => $validated['client'] ?? null,
                'job_no' => $validated['job_no'] ?? null,
                'location_project' => $validated['location_project'] ?? null,
                'note' => $validated['note'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
            ]);

            $purchaseRequest->items()->delete();
            foreach ($itemsData as $item) {
                $purchaseRequest->items()->create($item);
            }
            // Kotak approval TIDAK di-reset di sini karena is_draft artinya
            // memang belum ada satupun yang tanda tangan.
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)
            ->with('success', 'Purchase Request updated successfully.');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'Purchase Request deleted successfully.');
    }

    // Buka halaman cetak A4 (Save as PDF lewat window.print()).
    public function printPdf(PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->load(['items', 'approvals.signer', 'creator']);

        return view('purchase_requests.print', compact('purchaseRequest'));
    }

    // Tanda tangan digital satu kotak approval (canvas -> base64 PNG).
    public function sign(Request $request, PurchaseRequest $purchaseRequest, $approvalId)
    {
        $approval = $purchaseRequest->approvals()->findOrFail($approvalId);

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

    private function validatePr(Request $request): array
    {
        return $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'title' => 'required|string|max:255',
            'job_location' => 'nullable|string|max:255',
            'client' => 'nullable|string|max:255',
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

    // Bikin 8 kotak tanda tangan otomatis dari template saat PR pertama dibuat.
    private function createApprovalSlots(PurchaseRequest $purchaseRequest): void
    {
        foreach (PurchaseRequest::APPROVAL_TEMPLATE as $slot) {
            $purchaseRequest->approvals()->create($slot);
        }
    }
}
