<x-app-layout>
    <x-slot name="header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-bold text-2xl text-gray-800 leading-tight">Local Purchase (RRP)</h1>
            <p class="text-sm text-gray-500 mt-1">Manage Request for Retail Purchase and multi-vendor comparisons.</p>
        </div>
        <a href="{{ route('rlps.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add RRP
            </a>
        </div>
    </x-slot>

@php
    $autoOpenRlp = request('auto_open')
        ? ($rlps->firstWhere('id', request('auto_open')) ?? \App\Models\Rlp::with('items.jobCode', 'items.vendors.vendor', 'items.selectedVendor', 'creator', 'reviewer', 'acknowledger', 'approver', 'purchaseOrder')->find(request('auto_open')))
        : null;
@endphp

    <div class="py-8" x-data="{
            confirmOpen: false,
            confirmFormId: null,
            confirmMessage: '',
            openConfirm(message, formId) {
                this.confirmMessage = message;
                this.confirmFormId = formId;
                this.confirmOpen = true;
            },
            submitConfirm() {
                this.confirmOpen = false;
                document.getElementById(this.confirmFormId).submit();
            },
            detailOpen: false,
            detailData: {},
            openDetail(data) {
                this.detailData = data;
                this.detailOpen = true;
            },
            signModalOpen: false,
            signLabel: '',
            signPad: null,
            openSign(id, label, actionUrl, method = 'PATCH') {
                this.signLabel = label;
                const form = document.getElementById('rlp-sign-form');
                form.action = actionUrl;
                document.getElementById('rlp-sign-method-input').value = method;
                this.signModalOpen = true;

                this.$nextTick(() => {
                    const canvas = document.getElementById('rlp-signature-canvas');
                    if (!canvas) return;

                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    const ctx = canvas.getContext('2d');
                    ctx.scale(ratio, ratio);

                    if (this.signPad) {
                        this.signPad.clear();
                    } else if (window.SignaturePad) {
                        this.signPad = new SignaturePad(canvas, {
                            backgroundColor: 'rgb(255, 255, 255)',
                            penColor: 'rgb(17, 24, 39)',
                            minWidth: 1.2,
                            maxWidth: 3.2,
                        });
                    }
                });
            },
            clearSignPad() {
                if (this.signPad) {
                    this.signPad.clear();
                }
            },
            closeSignModal() {
                this.signModalOpen = false;
                this.clearSignPad();
            },
            submitSignature() {
                if (!this.signPad || this.signPad.isEmpty()) {
                    alert('Please provide a signature before saving.');
                    return;
                }
                const dataUrl = this.signPad.toDataURL('image/png');
                document.getElementById('rlp-signature-data-input').value = dataUrl;
                document.getElementById('rlp-sign-form').submit();
            },
            init() {
                @if($autoOpenRlp)
                    @php
                        $autoVendorNames = $autoOpenRlp->items->flatMap(fn ($i) => $i->vendors->pluck('vendor_name'))->filter()->unique();
                        $autoPayload = [
                            'id' => $autoOpenRlp->id,
                            'no_rlp' => $autoOpenRlp->no_rlp,
                            'date' => $autoOpenRlp->date ? $autoOpenRlp->date->format('d-m-Y') : '-',
                            'status' => $autoOpenRlp->status,
                            'items_count' => $autoOpenRlp->items->count(),
                            'vendors_count' => $autoVendorNames->count(),
                            'items' => $autoOpenRlp->items->map(function ($i) {
                                $lowestPrice = $i->vendors->pluck('u_price')->filter(fn ($p) => $p > 0)->min();
                                $savingsPct = $i->part_catalog_ext_price > 0
                                    ? round((-$i->revenue_ext_price / $i->part_catalog_ext_price) * 100, 1)
                                    : null;

                                return [
                                    'job_code' => $i->jobCode->job_code ?? '-',
                                    'description' => $i->description,
                                    'pn' => $i->pn,
                                    'qty' => $i->qty,
                                    'uom' => $i->uom,
                                    'part_catalog_u_price' => number_format($i->part_catalog_u_price, 0, ',', '.'),
                                    'part_catalog_ext_price' => number_format($i->part_catalog_ext_price, 0, ',', '.'),
                                    'revenue_ext_price' => number_format($i->revenue_ext_price, 0, ',', '.'),
                                    'revenue_is_negative' => $i->revenue_ext_price < 0,
                                    'savings_pct' => $i->selected_vendor_id ? $savingsPct : null,
                                    'vendors' => $i->vendors->map(fn ($v) => [
                                        'vendor_name' => $v->vendor_name,
                                        'brand' => $v->vendor->brand ?? null,
                                        'u_price' => number_format($v->u_price, 0, ',', '.'),
                                        'ext_price' => number_format($v->ext_price, 0, ',', '.'),
                                        'delivery_estimate' => $v->delivery_estimate,
                                        'is_selected' => $v->id === $i->selected_vendor_id,
                                        'is_lowest' => $i->vendors->count() > 1 && $lowestPrice !== null && (float) $v->u_price === (float) $lowestPrice,
                                    ])->values(),
                                ];
                            })->values(),
                            'part_catalog_total_ext' => number_format($autoOpenRlp->part_catalog_ext_price, 0, ',', '.'),
                            'revenue_ext_price' => number_format($autoOpenRlp->revenue_ext_price, 0, ',', '.'),
                            'revenue_is_negative' => $autoOpenRlp->revenue_ext_price < 0,
                            'wur_grand_total' => number_format($autoOpenRlp->wur_grand_total, 0, ',', '.'),
                            'has_po' => (bool) $autoOpenRlp->purchaseOrder,
                            'po_no' => $autoOpenRlp->purchaseOrder->po_no ?? null,
                            'po_url' => $autoOpenRlp->purchaseOrder ? route('purchase-orders.show', $autoOpenRlp->purchaseOrder) : route('purchase-orders.create', ['from_rlp' => $autoOpenRlp->id]),
                            'created_by' => $autoOpenRlp->creator->name ?? 'Staff',
                            'created_signature' => $autoOpenRlp->created_signature,
                            'can_sign_prepared' => auth()->check() && (auth()->id() === $autoOpenRlp->created_by || auth()->user()->isAdmin() || auth()->user()->isApprover()),
                            'sign_prepared_url' => route('rlps.sign-prepared', $autoOpenRlp),
                            'is_reviewed' => $autoOpenRlp->is_reviewed,
                            'reviewed_by' => $autoOpenRlp->reviewer->name ?? null,
                            'reviewed_at' => $autoOpenRlp->reviewed_at ? $autoOpenRlp->reviewed_at->format('d-m-Y H:i') : null,
                            'reviewed_signature' => $autoOpenRlp->reviewed_signature,
                            'can_sign_review' => auth()->check() && (auth()->user()->isRlpReviewer() || auth()->user()->isAdmin() || auth()->user()->isApprover()) && ! $autoOpenRlp->is_reviewed,
                            'review_url' => route('rlps.review', $autoOpenRlp),
                            'is_acknowledged' => $autoOpenRlp->is_acknowledged,
                            'acknowledged_by' => $autoOpenRlp->acknowledger->name ?? null,
                            'acknowledged_at' => $autoOpenRlp->acknowledged_at ? $autoOpenRlp->acknowledged_at->format('d-m-Y H:i') : null,
                            'acknowledged_signature' => $autoOpenRlp->acknowledged_signature,
                            'can_sign_acknowledge' => auth()->check() && (auth()->user()->isRlpAcknowledger() || auth()->user()->isAdmin() || auth()->user()->isApprover()) && $autoOpenRlp->is_reviewed && ! $autoOpenRlp->is_acknowledged,
                            'acknowledge_url' => route('rlps.acknowledge', $autoOpenRlp),
                            'is_approved' => $autoOpenRlp->is_approved,
                            'approved_by' => $autoOpenRlp->approver->name ?? null,
                            'approved_at' => $autoOpenRlp->approved_at ? $autoOpenRlp->approved_at->format('d-m-Y H:i') : null,
                            'approved_signature' => $autoOpenRlp->approved_signature,
                            'can_sign_approve' => auth()->check() && (auth()->user()->isRlpApprover() || auth()->user()->isAdmin() || auth()->user()->isApprover()) && $autoOpenRlp->is_acknowledged && ! $autoOpenRlp->is_approved,
                            'approve_url' => route('rlps.approve', $autoOpenRlp),
                            'print_url' => route('rlps.print', $autoOpenRlp),
                        ];
                    @endphp
                    this.$nextTick(() => {
                        this.openDetail({{ \Illuminate\Support\Js::from($autoPayload) }});
                    });
                @endif
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 4000)"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="flex-shrink-0 text-green-500 hover:text-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">No</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">No RRP</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Selected Vendor</th>
                                <th class="pl-8 pr-6 py-3 text-right font-medium text-gray-600">Revenue (Ext)</th>
                                <th class="pl-6 pr-6 py-3 text-center font-medium text-gray-600 w-32">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rlps as $rlp)
                                @php
                                    $vendorNames = $rlp->items->map(fn ($i) => $i->selectedVendor->vendor_name ?? null)->filter()->unique()->values();
                                    $vendorDisplay = '-';
                                    if ($vendorNames->isNotEmpty()) {
                                        $vendorDisplay = $vendorNames->count() === 1
                                            ? $vendorNames->first()
                                            : $vendorNames->first() . ' +' . ($vendorNames->count() - 1) . ' more';
                                    }

                                    $allVendorNames = $rlp->items->flatMap(fn ($i) => $i->vendors->pluck('vendor_name'))->filter()->unique();

                                    $canSignPrepared = auth()->check() && (auth()->id() === $rlp->created_by || auth()->user()->isAdmin() || auth()->user()->isApprover());
                                    $canSignReview = auth()->check() && (auth()->user()->isRlpReviewer() || auth()->user()->isAdmin() || auth()->user()->isApprover()) && ! $rlp->is_reviewed;
                                    $canSignAcknowledge = auth()->check() && (auth()->user()->isRlpAcknowledger() || auth()->user()->isAdmin() || auth()->user()->isApprover()) && $rlp->is_reviewed && ! $rlp->is_acknowledged;
                                    $canSignApprove = auth()->check() && (auth()->user()->isRlpApprover() || auth()->user()->isAdmin() || auth()->user()->isApprover()) && $rlp->is_acknowledged && ! $rlp->is_approved;

                                    $detailPayload = [
                                        'id' => $rlp->id,
                                        'no_rlp' => $rlp->no_rlp,
                                        'date' => $rlp->date ? $rlp->date->format('d-m-Y') : '-',
                                        'created_by' => $rlp->creator->name ?? null,
                                        'status' => $rlp->status,
                                        'created_by' => $rlp->creator->name ?? 'Staff',
                                        'created_signature' => $rlp->created_signature,
                                        'can_sign_prepared' => $canSignPrepared,
                                        'sign_prepared_url' => route('rlps.sign-prepared', $rlp),
                                        'is_reviewed' => $rlp->is_reviewed,
                                        'reviewed_by' => $rlp->reviewer->name ?? null,
                                        'reviewed_at' => $rlp->reviewed_at ? $rlp->reviewed_at->format('d-m-Y H:i') : null,
                                        'reviewed_signature' => $rlp->reviewed_signature,
                                        'can_sign_review' => $canSignReview,
                                        'review_url' => route('rlps.review', $rlp),
                                        'is_acknowledged' => $rlp->is_acknowledged,
                                        'acknowledged_by' => $rlp->acknowledger->name ?? null,
                                        'acknowledged_at' => $rlp->acknowledged_at ? $rlp->acknowledged_at->format('d-m-Y H:i') : null,
                                        'acknowledged_signature' => $rlp->acknowledged_signature,
                                        'can_sign_acknowledge' => $canSignAcknowledge,
                                        'acknowledge_url' => route('rlps.acknowledge', $rlp),
                                        'is_approved' => $rlp->is_approved,
                                        'approved_by' => $rlp->approver->name ?? null,
                                        'approved_at' => $rlp->approved_at ? $rlp->approved_at->format('d-m-Y H:i') : null,
                                        'approved_signature' => $rlp->approved_signature,
                                        'can_sign_approve' => $canSignApprove,
                                        'approve_url' => route('rlps.approve', $rlp),
                                        'print_url' => route('rlps.print', $rlp),
                                        'items_count' => $rlp->items->count(),
                                        'vendors_count' => $allVendorNames->count(),
                                        'items' => $rlp->items->map(function ($i) {
                                            $lowestPrice = $i->vendors->pluck('u_price')->filter(fn ($p) => $p > 0)->min();
                                            $savingsPct = $i->part_catalog_ext_price > 0
                                                ? round((-$i->revenue_ext_price / $i->part_catalog_ext_price) * 100, 1)
                                                : null;

                                            return [
                                                'job_code' => $i->jobCode->job_code ?? '-',
                                                'description' => $i->description,
                                                'pn' => $i->pn,
                                                'qty' => $i->qty,
                                                'uom' => $i->uom,
                                                'part_catalog_u_price' => number_format($i->part_catalog_u_price, 0, ',', '.'),
                                                'part_catalog_ext_price' => number_format($i->part_catalog_ext_price, 0, ',', '.'),
                                                'revenue_ext_price' => number_format($i->revenue_ext_price, 0, ',', '.'),
                                                'revenue_is_negative' => $i->revenue_ext_price < 0,
                                                'savings_pct' => $i->selected_vendor_id ? $savingsPct : null,
                                                'vendors' => $i->vendors->map(fn ($v) => [
                                                    'vendor_name' => $v->vendor_name,
                                                    'brand' => $v->vendor->brand ?? null,
                                                    'u_price' => number_format($v->u_price, 0, ',', '.'),
                                                    'ext_price' => number_format($v->ext_price, 0, ',', '.'),
                                                    'delivery_estimate' => $v->delivery_estimate,
                                                    'is_selected' => $v->id === $i->selected_vendor_id,
                                                    'is_lowest' => $i->vendors->count() > 1 && $lowestPrice !== null && (float) $v->u_price === (float) $lowestPrice,
                                                ])->values(),
                                            ];
                                        })->values(),
                                        'part_catalog_total_ext' => number_format($rlp->part_catalog_ext_price, 0, ',', '.'),
                                        'revenue_ext_price' => number_format($rlp->revenue_ext_price, 0, ',', '.'),
                                        'revenue_is_negative' => $rlp->revenue_ext_price < 0,
                                        'wur_grand_total' => number_format($rlp->wur_grand_total, 0, ',', '.'),
                                        'has_po' => (bool) $rlp->purchaseOrder,
                                        'po_no' => $rlp->purchaseOrder->po_no ?? null,
                                        'po_url' => $rlp->purchaseOrder ? route('purchase-orders.show', $rlp->purchaseOrder) : route('purchase-orders.create', ['from_rlp' => $rlp->id]),
                                    ];
                                @endphp
                                <tr @click="openDetail({{ \Illuminate\Support\Js::from($detailPayload) }})"
                                    class="border-t border-gray-100 hover:bg-gray-50/60 cursor-pointer transition">
                                    <td class="px-4 py-4 text-gray-500">{{ $rlps->firstItem() + $loop->index }}</td>
                                    <td class="px-4 py-4 font-medium text-gray-800">
                                        {{ $rlp->no_rlp }}
                                        @if($rlp->purchaseOrder)
                                            <span class="inline-flex items-center ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                PO: {{ $rlp->purchaseOrder->po_no }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center ml-1.5 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $rlp->is_approved ? 'bg-green-100 text-green-700' : ($rlp->is_acknowledged ? 'bg-indigo-100 text-indigo-700' : ($rlp->is_reviewed ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                                            {{ $rlp->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-gray-600">{{ $rlp->date ? $rlp->date->format('d-m-Y') : '-' }}</td>
                                    <td class="px-4 py-4 text-gray-600">{{ $vendorDisplay }}</td>
                                    <td class="pl-8 pr-6 py-4 text-right font-medium whitespace-nowrap {{ $rlp->revenue_ext_price < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        Rp {{ number_format($rlp->revenue_ext_price, 0, ',', '.') }}
                                    </td>
                                    <td class="pl-6 pr-6 py-4" @click.stop>
                                        <div class="flex items-center justify-center gap-3">
                                            <div class="relative" x-data="{ rowOpen: false, menuTop: 0, menuLeft: 0 }" @click.outside="rowOpen = false" @scroll.window="rowOpen = false">
                                                <button type="button"
                                                        @click="
                                                            rowOpen = !rowOpen;
                                                            if (rowOpen) {
                                                                const rect = $el.getBoundingClientRect();
                                                                menuTop = rect.bottom + 6;
                                                                menuLeft = Math.min(window.innerWidth - 184, Math.max(8, rect.right - 176));
                                                            }
                                                        "
                                                        title="Actions"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/>
                                                    </svg>
                                                </button>

                                                {{-- position: fixed, bukan absolute, biar gak kepotong overflow-x-auto punya wrapper tabel --}}
                                                <div x-show="rowOpen" x-cloak
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="opacity-0 scale-95"
                                                     x-transition:enter-end="opacity-100 scale-100"
                                                     :style="'top:' + menuTop + 'px; left:' + menuLeft + 'px;'"
                                                     style="display:none;"
                                                     class="fixed z-50 w-48 rounded-lg border border-gray-100 bg-white shadow-xl py-1.5">

                                                    {{-- Digital Signature Quick Actions --}}
                                                    @if($canSignReview)
                                                        <button type="button"
                                                                @click="rowOpen = false; openSign({{ $rlp->id }}, 'Review RRP ({{ $rlp->no_rlp }})', '{{ route('rlps.review', $rlp) }}', 'PATCH')"
                                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 transition">
                                                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                            Sign & Review
                                                        </button>
                                                    @elseif($canSignAcknowledge)
                                                        <button type="button"
                                                                @click="rowOpen = false; openSign({{ $rlp->id }}, 'Acknowledge RRP ({{ $rlp->no_rlp }})', '{{ route('rlps.acknowledge', $rlp) }}', 'PATCH')"
                                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 transition">
                                                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                            Sign & Acknowledge
                                                        </button>
                                                    @elseif($canSignApprove)
                                                        <button type="button"
                                                                @click="rowOpen = false; openSign({{ $rlp->id }}, 'Approve RRP ({{ $rlp->no_rlp }})', '{{ route('rlps.approve', $rlp) }}', 'PATCH')"
                                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 transition">
                                                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                            Sign & Approve
                                                        </button>
                                                    @elseif($canSignPrepared && ! $rlp->created_signature)
                                                        <button type="button"
                                                                @click="rowOpen = false; openSign({{ $rlp->id }}, 'Prepared By ({{ $rlp->no_rlp }})', '{{ route('rlps.sign-prepared', $rlp) }}', 'POST')"
                                                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50 transition">
                                                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                            </svg>
                                                            Sign Document
                                                        </button>
                                                    @endif

                                                    @if($rlp->purchaseOrder)
                                                        <a href="{{ route('purchase-orders.show', $rlp->purchaseOrder) }}"
                                                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50 transition">
                                                           class="flex items-center gap-2.5 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50 transition">
                                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                                            </svg>
                                                            View PO
                                                        </a>
                                                    @else
                                                        <a href="{{ route('purchase-orders.create', ['from_rlp' => $rlp->id]) }}"
                                                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-50 font-medium transition">
                                                           class="flex items-center gap-2.5 px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50 font-medium transition">
                                                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            Generate PO
                                                        </a>
                                                    @endif

                                                    <a href="{{ route('rlps.edit', $rlp) }}"
                                                       class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Edit
                                                    </a>

                                                    <form id="delete-rlp-{{ $rlp->id }}" action="{{ route('rlps.destroy', $rlp) }}" method="POST" class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                    <button type="button"
                                                            @click="rowOpen = false; openConfirm('Delete RRP {{ $rlp->no_rlp }}? This action cannot be undone.', 'delete-rlp-{{ $rlp->id }}')"
                                                            class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                    <a href="{{ route('rlps.print', $rlp) }}" target="_blank"
                                                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-12 0h12v6H6v-6z"/>
                                                            </svg>
                                                            Print
                                                        </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                        No RRP data yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rlps->hasPages())
                    <div class="px-4 py-3 border-t border-gray-100">
                        {{ $rlps->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal Detail --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-8" style="display: none;">
            <div x-show="detailOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="detailOpen = false"
                 class="fixed inset-0 bg-gray-900/50"></div>

            <div class="relative min-h-full flex items-center justify-center">
            <div x-show="detailOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 @keydown.escape.window="detailOpen = false"
                 @click.outside="detailOpen = false"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl sm:max-w-4xl p-6 sm:p-7 my-8">

                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-semibold text-gray-800">RRP Detail</h3>
                    <button type="button" @click="detailOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-medium text-indigo-600" x-text="detailData.no_rlp"></p>
                    <p class="text-xs text-gray-400" x-text="detailData.date"></p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-700': detailData.status === 'Approved',
                            'bg-blue-100 text-blue-700': detailData.status === 'Pending Approval',
                            'bg-indigo-100 text-indigo-700': detailData.status === 'Pending Acknowledgement',
                            'bg-yellow-100 text-yellow-700': detailData.status === 'Pending Review'
                          }"
                          x-text="detailData.status"></span>
                </div>
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                    <p class="text-xs text-gray-400" x-show="detailData.created_by">
                        Created by <span class="text-gray-600 font-medium" x-text="detailData.created_by"></span> &middot; <span x-text="detailData.date"></span>
                    </p>
                    <p class="text-xs text-gray-400 ml-auto">
                        <span x-text="detailData.items_count"></span> item(s) &middot;
                        <span x-text="detailData.vendors_count"></span> vendor(s) compared
                    </p>
                </div>

                <div class="space-y-4 text-sm max-h-[65vh] overflow-y-auto pr-1">
                    <template x-for="(item, index) in detailData.items" :key="index">
                        <div class="border border-gray-100 rounded-lg p-3">
                            <p class="text-gray-800 font-medium text-sm" x-text="(index + 1) + '. ' + item.description"></p>
                            <p class="text-gray-500 text-xs mt-0.5" x-text="item.qty + ' ' + item.uom + (item.pn ? ' · PN: ' + item.pn : '') + ' · Job Code: ' + item.job_code"></p>
                            <p class="text-gray-400 text-xs mt-0.5" x-text="'Catalog: Rp ' + item.part_catalog_u_price + ' / unit — Ext Rp ' + item.part_catalog_ext_price"></p>

                            <div class="mt-2 border border-gray-100 rounded-lg divide-y divide-gray-100">
                                <template x-for="(v, vIndex) in item.vendors" :key="vIndex">
                                    <div class="p-2 px-3" :class="v.is_selected ? 'bg-indigo-50/60' : ''">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-gray-700 text-xs font-medium truncate">
                                                <span x-text="v.vendor_name"></span>
                                                <span x-show="v.brand" class="text-gray-400 font-normal" x-text="' · ' + v.brand"></span>
                                            </p>
                                            <span class="flex items-center gap-1 shrink-0">
                                                <span x-show="v.is_lowest" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">Lowest Price</span>
                                                <span x-show="v.is_selected" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700">Selected</span>
                                            </span>
                                        </div>
                                        <p class="text-gray-400 text-xs mt-0.5" x-text="'Rp ' + v.u_price + ' → Ext Rp ' + v.ext_price + ' · Delivery: ' + v.delivery_estimate"></p>
                                    </div>
                                </template>
                                <p class="p-2 px-3 text-xs text-gray-400" x-show="!item.vendors || item.vendors.length === 0">No quotations</p>
                            </div>

                            <p class="text-xs mt-1.5 text-right">
                                <span x-show="item.savings_pct !== null" class="text-gray-400 mr-2">
                                    (<span x-text="Math.abs(item.savings_pct) + '% ' + (item.savings_pct < 0 ? 'over' : 'under') + ' catalog'"></span>)
                                </span>
                                Revenue: <span class="font-semibold" :class="item.revenue_is_negative ? 'text-red-600' : 'text-emerald-600'" x-text="'Rp ' + item.revenue_ext_price"></span>
                            </p>
                        </div>
                    </template>
                    <p class="text-xs text-gray-400 text-center" x-show="!detailData.items || detailData.items.length === 0">No items</p>

                    <div class="grid grid-cols-3 gap-4 pt-3 border-t border-gray-100">
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Part Catalog Total</p>
                            <p class="text-gray-800 font-semibold" x-text="'Rp ' + detailData.part_catalog_total_ext"></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">Revenue (Grand Total)</p>
                            <p class="font-semibold" :class="detailData.revenue_is_negative ? 'text-red-600' : 'text-emerald-600'" x-text="'Rp ' + detailData.revenue_ext_price"></p>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs mb-0.5">WUR Grand Total</p>
                            <p class="text-gray-800 font-semibold" x-text="'Rp ' + detailData.wur_grand_total"></p>
                        </div>
                    </div>

                    {{-- Approval Workflow Grid (4 Cards) --}}
                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Approval Workflow</h4>
                            <span class="text-[10px] text-gray-400">Sequential digital signatures</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            {{-- Card 1: Prepared By --}}
                            <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                                 :class="detailData.created_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : 'bg-gray-50/20 border-gray-200'">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                              :class="detailData.created_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                            Prepared
                                        </span>
                                        <span class="text-[8px] font-medium text-gray-400">Step 1</span>
                                    </div>
                                    <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Prepared By">Prepared By</h5>
                                </div>

                                <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[64px] flex flex-col items-center justify-center">
                                    <template x-if="detailData.created_signature">
                                        <img :src="detailData.created_signature" alt="signature" class="h-11 object-contain filter drop-shadow-xs">
                                    </template>
                                    <template x-if="!detailData.created_signature && detailData.can_sign_prepared">
                                        <button type="button"
                                                @click="openSign(detailData.id, 'Prepared By (' + detailData.no_rlp + ')', detailData.sign_prepared_url, 'POST')"
                                                class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1 px-2 rounded shadow-xs transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Sign
                                        </button>
                                    </template>
                                    <template x-if="!detailData.created_signature && !detailData.can_sign_prepared">
                                        <span class="text-[9px] text-gray-400">Created</span>
                                    </template>
                                </div>

                                <div class="text-center">
                                    <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.created_by"></p>
                                    <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-text="detailData.date"></p>
                                </div>
                            </div>

                            {{-- Card 2: Review By --}}
                            <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                                 :class="detailData.reviewed_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : (detailData.can_sign_review ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200')">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                              :class="detailData.reviewed_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                            Review
                                        </span>
                                        <span class="text-[8px] font-medium text-gray-400">Step 2</span>
                                    </div>
                                    <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Review By">Review By</h5>
                                </div>

                                <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[64px] flex flex-col items-center justify-center">
                                    <template x-if="detailData.reviewed_signature">
                                        <img :src="detailData.reviewed_signature" alt="signature" class="h-11 object-contain filter drop-shadow-xs">
                                    </template>
                                    <template x-if="!detailData.reviewed_signature && detailData.can_sign_review">
                                        <button type="button"
                                                @click="openSign(detailData.id, 'Review By (' + detailData.no_rlp + ')', detailData.review_url, 'PATCH')"
                                                class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1 px-2 rounded shadow-xs transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Sign
                                        </button>
                                    </template>
                                    <template x-if="!detailData.reviewed_signature && !detailData.can_sign_review">
                                        <span class="text-[9px] text-gray-400" x-text="detailData.is_reviewed ? 'Reviewed' : 'Pending'"></span>
                                    </template>
                                </div>

                                <div class="text-center">
                                    <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.reviewed_by || 'Reviewer'"></p>
                                    <p class="text-[9px] text-emerald-600 font-medium mt-0.5 leading-none" x-show="detailData.reviewed_at" x-text="detailData.reviewed_at"></p>
                                    <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-show="!detailData.reviewed_at">Awaiting</p>
                                </div>
                            </div>

                            {{-- Card 3: Acknowledge By --}}
                            <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                                 :class="detailData.acknowledged_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : (detailData.can_sign_acknowledge ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200')">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                              :class="detailData.acknowledged_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                            Acknowledge
                                        </span>
                                        <span class="text-[8px] font-medium text-gray-400">Step 3</span>
                                    </div>
                                    <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Acknowledge By">Acknowledge By</h5>
                                </div>

                                <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[64px] flex flex-col items-center justify-center">
                                    <template x-if="detailData.acknowledged_signature">
                                        <img :src="detailData.acknowledged_signature" alt="signature" class="h-11 object-contain filter drop-shadow-xs">
                                    </template>
                                    <template x-if="!detailData.acknowledged_signature && !detailData.is_reviewed">
                                        <span class="text-[9px] text-gray-400">Locked (Step 2)</span>
                                    </template>
                                    <template x-if="!detailData.acknowledged_signature && detailData.is_reviewed && detailData.can_sign_acknowledge">
                                        <button type="button"
                                                @click="openSign(detailData.id, 'Acknowledge By (' + detailData.no_rlp + ')', detailData.acknowledge_url, 'PATCH')"
                                                class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1 px-2 rounded shadow-xs transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Sign
                                        </button>
                                    </template>
                                    <template x-if="!detailData.acknowledged_signature && detailData.is_reviewed && !detailData.can_sign_acknowledge">
                                        <span class="text-[9px] text-gray-400" x-text="detailData.is_acknowledged ? 'Acknowledged' : 'Pending'"></span>
                                    </template>
                                </div>

                                <div class="text-center">
                                    <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.acknowledger || 'Acknowledger'"></p>
                                    <p class="text-[9px] text-emerald-600 font-medium mt-0.5 leading-none" x-show="detailData.acknowledged_at" x-text="detailData.acknowledged_at"></p>
                                    <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-show="!detailData.acknowledged_at">Awaiting</p>
                                </div>
                            </div>

                            {{-- Card 4: Approved By --}}
                            <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                                 :class="detailData.approved_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : (detailData.can_sign_approve ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200')">
                                <div>
                                    <div class="flex items-center justify-between gap-1 mb-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                              :class="detailData.approved_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                            Approve
                                        </span>
                                        <span class="text-[8px] font-medium text-gray-400">Step 4</span>
                                    </div>
                                    <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Approved By">Approved By</h5>
                                </div>

                                <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[64px] flex flex-col items-center justify-center">
                                    <template x-if="detailData.approved_signature">
                                        <img :src="detailData.approved_signature" alt="signature" class="h-11 object-contain filter drop-shadow-xs">
                                    </template>
                                    <template x-if="!detailData.approved_signature && !detailData.is_acknowledged">
                                        <span class="text-[9px] text-gray-400">Locked (Step 3)</span>
                                    </template>
                                    <template x-if="!detailData.approved_signature && detailData.is_acknowledged && detailData.can_sign_approve">
                                        <button type="button"
                                                @click="openSign(detailData.id, 'Approved By (' + detailData.no_rlp + ')', detailData.approve_url, 'PATCH')"
                                                class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1 px-2 rounded shadow-xs transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            Sign
                                        </button>
                                    </template>
                                    <template x-if="!detailData.approved_signature && detailData.is_acknowledged && !detailData.can_sign_approve">
                                        <span class="text-[9px] text-gray-400" x-text="detailData.is_approved ? 'Approved' : 'Pending'"></span>
                                    </template>
                                </div>

                                <div class="text-center">
                                    <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.approver || 'Approver'"></p>
                                    <p class="text-[9px] text-emerald-600 font-medium mt-0.5 leading-none" x-show="detailData.approved_at" x-text="detailData.approved_at"></p>
                                    <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-show="!detailData.approved_at">Awaiting</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 mt-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <a :href="detailData.print_url" target="_blank"
                               class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-xs font-semibold shadow-xs transition">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.318 2.226c.079.554-.36 1.052-.92 1.052H6.94c-.56 0-.998-.498-.92-1.052L6.34 18m11.318 0h1.093c1.036 0 1.875-.84 1.875-1.875V9.375c0-1.036-.84-1.875-1.875-1.875H4.875C3.839 7.5 3 8.34 3 9.375v6.75c0 1.035.84 1.875 1.875 1.875H6.34m10.94 0H6.34m9.94-11.25V4.875c0-1.036-.84-1.875-1.875-1.875H8.625C7.59 3 6.75 3.84 6.75 4.875v2.625"/>
                                </svg>
                                Print / PDF
                            </a>
                            <template x-if="detailData.has_po">
                                <a :href="detailData.po_url" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                    </svg>
                                    View Linked PO (<span x-text="detailData.po_no"></span>)
                                </a>
                            </template>
                            <template x-if="!detailData.has_po">
                                <a :href="detailData.po_url" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Generate Purchase Order (PO)
                                </a>
                            </template>
                        </div>
                        <button type="button" @click="detailOpen = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition ml-auto">
                            Close
                        </button>
                    </div>
                </div>
            </div>
            </div>
        </div>

        {{-- Modal konfirmasi hapus --}}
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
            <div x-show="confirmOpen"
                 class="fixed inset-0 bg-gray-500 opacity-75"
                 @click="confirmOpen = false"></div>

            <div x-show="confirmOpen"
                 class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-sm sm:w-full sm:mx-auto relative">
                <div class="px-6 py-5">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Confirmation</h3>
                    <p class="text-sm text-gray-600" x-text="confirmMessage"></p>
                </div>
                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                    <button type="button" @click="confirmOpen = false"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="button" @click="submitConfirm()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-red-700 transition shadow-sm">
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>

        {{-- Digital Signature Modal --}}
        <div x-show="signModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-xs" @click="closeSignModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 sm:p-8">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Digital Signature Pad</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Signing as: <strong class="text-indigo-600" x-text="signLabel"></strong></p>
                    </div>
                    <button type="button" @click="closeSignModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Spacious Canvas --}}
                <div class="relative bg-gray-50/50 rounded-xl border border-gray-200 p-2">
                    <canvas id="rlp-signature-canvas" class="w-full h-64 sm:h-72 bg-white rounded-lg touch-none shadow-inner cursor-crosshair"></canvas>
                    <div class="absolute bottom-6 left-6 right-6 border-b border-gray-300 pointer-events-none flex justify-between items-end pb-1">
                        <span class="text-[11px] text-gray-400 font-normal">Sign above this line</span>
                        <span class="text-[11px] text-gray-400 font-normal">✕</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 mt-5">
                    <button type="button" @click="clearSignPad()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 px-3.5 py-2 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear Signature
                    </button>
                    <div class="flex gap-2.5">
                        <button type="button" @click="closeSignModal()" class="text-xs font-semibold text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="button" @click="submitSignature()"
                                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow-sm transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save & Sign Document
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden shared submit form for the signature --}}
        <form id="rlp-sign-form" method="POST" action="">
            @csrf
            <input type="hidden" name="_method" id="rlp-sign-method-input" value="PATCH">
            <input type="hidden" name="signature" id="rlp-signature-data-input">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</x-app-layout>
