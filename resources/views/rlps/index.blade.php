<x-app-layout>
    <x-slot name="header">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-bold text-2xl text-gray-800 leading-tight">Local Purchase</h1>
            <p class="text-sm text-gray-500 mt-1">Manage Request for Retail Purchase (RRP) and vendor comparisons.</p>
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

                                    $detailPayload = [
                                        'no_rlp' => $rlp->no_rlp,
                                        'date' => $rlp->date ? $rlp->date->format('d-m-Y') : '-',
                                        'created_by' => $rlp->creator->name ?? null,
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
                                                    @if($rlp->purchaseOrder)
                                                        <a href="{{ route('purchase-orders.show', $rlp->purchaseOrder) }}"
                                                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50 transition">
                                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                                                            </svg>
                                                            View PO
                                                        </a>
                                                    @else
                                                        <a href="{{ route('purchase-orders.create', ['from_rlp' => $rlp->id]) }}"
                                                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-indigo-700 hover:bg-indigo-50 font-medium transition">
                                                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            Generate PO
                                                        </a>
                                                    @endif

                                                    <a href="{{ route('rlps.edit', $rlp) }}"
                                                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
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
                                                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
                                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Delete
                                                    </button>
                                                    <a href="{{ route('rlps.print', $rlp) }}" target="_blank"
                                                        class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
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
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 my-8">

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
                </div>
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                    <p class="text-xs text-gray-400" x-show="detailData.created_by">
                        Created by <span class="text-gray-600 font-medium" x-text="detailData.created_by"></span>
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

                    <div class="pt-4 mt-2 border-t border-gray-100 flex items-center justify-between">
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
    </div>
</x-app-layout>
