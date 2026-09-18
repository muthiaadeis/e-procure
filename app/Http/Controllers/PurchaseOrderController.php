<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Rlp;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $purchaseOrders = $query->latest('our_order_date')->paginate(15)->withQueryString();

        return view('purchase_orders.index', compact('purchaseOrders', 'search'));
    }

    public function create(Request $request)
    {
        $fromRlp = null;
        $prefill = [];
        $vendors = Vendor::orderBy('vendor_name')->get(['id', 'vendor_name', 'vendor_code', 'vendor_address', 'phone', 'brand']);

        if ($request->filled('from_rlp')) {
            $fromRlp = Rlp::with(['items.selectedVendor.vendor', 'items.vendors.vendor', 'items.jobCode'])->find($request->query('from_rlp'));

            if ($fromRlp) {
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

        return view('purchase_orders.create', compact('fromRlp', 'prefill', 'vendors'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePo($request);

        $purchaseOrder = DB::transaction(function () use ($validated) {
            [$subtotal, $itemsData] = $this->prepareItems($validated['items']);
            $ppn = $this->calculatePpn($subtotal, $validated);

            $po = PurchaseOrder::create([
                'po_no' => $validated['po_no'],
                'rlp_id' => $validated['rlp_id'] ?? null,
                'our_reference' => $validated['our_reference'] ?? null,
                'supplier_no' => $validated['supplier_no'] ?? null,
                'our_order_date' => $validated['our_order_date'] ?? now(),
                'revision' => $validated['revision'] ?? null,
                'to_address' => $validated['to_address'] ?? null,
                'attn' => $validated['attn'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'project_description' => $validated['project_description'] ?? null,
                'contact_number' => $validated['contact_number'] ?? null,
                'client' => $validated['client'] ?? null,
                'final_delivery_address' => $validated['final_delivery_address'] ?? null,
                'subtotal' => $subtotal,
                'use_ppn' => $ppn['use_ppn'],
                'ppn_percent' => $ppn['ppn_percent'],
                'ppn_amount' => $ppn['ppn_amount'],
                'grand_total' => $subtotal + $ppn['ppn_amount'],
                'created_by' => auth()->id(),
            ]);

            foreach ($itemsData as $item) {
                $po->items()->create($item);
            }

            $this->createApprovalSlots($po);

            return $po;
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order added successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'approvals.signer', 'creator', 'rlp', 'purchaseRequest']);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        abort(403, 'Purchase Orders cannot be edited once created. Please delete and create a new one if changes are needed (only possible before any approval is signed).');
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort(403, 'Purchase Orders cannot be edited once created. Please delete and create a new one if changes are needed (only possible before any approval is signed).');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order deleted successfully.');
    }

    // Buka halaman cetak A4 (Save as PDF lewat window.print()).
    public function printPdf(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['items', 'approvals.signer', 'creator']);

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

    private function validatePo(Request $request): array
    {
        return $request->validate([
            'po_no' => 'required|string|max:100|unique:purchase_orders,po_no',
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

            'final_delivery_address' => 'nullable|string',

            'use_ppn' => 'nullable|boolean',
            'ppn_percent' => 'nullable|numeric|min:0|max:100',

            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.uom' => 'required|string|max:50',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
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

    // Bikin 7 kotak tanda tangan otomatis dari template saat PO pertama dibuat.
    private function createApprovalSlots(PurchaseOrder $purchaseOrder): void
    {
        foreach (PurchaseOrder::APPROVAL_TEMPLATE as $slot) {
            $purchaseOrder->approvals()->create($slot);
        }
    }
}
