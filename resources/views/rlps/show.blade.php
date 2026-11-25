<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0 max-w-full">
                <h1 class="font-bold text-2xl text-gray-800 leading-tight break-all">{{ $rlp->no_rlp }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Tanggal: <span class="font-medium text-gray-700">{{ $rlp->date?->format('d-m-Y') ?? '-' }}</span>
                    @if($rlp->purchaseOrder)
                        · PO:
                        <a href="{{ route('purchase-orders.show', $rlp->purchaseOrder) }}" class="text-indigo-600 hover:underline font-semibold">
                            {{ $rlp->purchaseOrder->po_no }}
                        </a>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($rlp->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $rlp->purchaseOrder) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-lg hover:bg-emerald-100 transition shadow-xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PO ({{ $rlp->purchaseOrder->po_no }})
                    </a>
                @elseif(auth()->user()->isInput() && $rlp->is_approved && ! $rlp->is_rejected)
                    <a href="{{ route('purchase-orders.create', ['from_rlp' => $rlp->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat PO dari RRP
                    </a>
                @endif

                @if((auth()->user()->isInput() || auth()->user()->isAdmin()) && $rlp->is_rejected)
                    <a href="{{ route('rlps.edit', $rlp) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition shadow-xs">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit & Ajukan Ulang</span>
                    </a>
                @endif

                <a href="{{ route('rlps.print', $rlp) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Cetak / PDF
                </a>
                @php
                    $isFromDashboard = request('from') === 'dashboard' || str_contains(url()->previous(), route('dashboard'));
                    $backUrl = $isFromDashboard ? route('dashboard') : route('rlps.index');
                @endphp
                <a href="{{ $backUrl }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-xs">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                </div>
            @endif

            {{-- Header info --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8 space-y-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">No RRP</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $rlp->no_rlp }}">{{ $rlp->no_rlp }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Date</p>
                        <p class="font-semibold text-gray-800">{{ $rlp->date?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Prepared By</p>
                        <p class="font-semibold text-gray-800 truncate" title="{{ $rlp->creator->name ?? '-' }}">{{ $rlp->creator->name ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Status</p>
                        @php
                            $signedCount = $rlp->approvals->whereNotNull('signed_at')->count();
                            $totalCount = $rlp->approvals->count();
                            $statusColor = match($rlp->status) {
                                'Done', 'Completed' => 'bg-green-100 text-green-700',
                                'Pending Setup' => 'bg-amber-100 text-amber-800',
                                'In Progress' => 'bg-blue-100 text-blue-700',
                                'Rejected' => 'bg-red-100 text-red-700',
                                default => str_starts_with($rlp->status, 'Pending') ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                            {{ $rlp->status }}@if($totalCount > 1 && $rlp->status !== 'Done' && $rlp->status !== 'Pending Setup' && !str_starts_with($rlp->status, 'Rejected')) ({{ $signedCount }}/{{ $totalCount }})@endif
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Revenue Ext Price</p>
                        <p class="font-semibold {{ $rlp->revenue_ext_price < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            Rp {{ number_format((float) $rlp->revenue_ext_price, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Total Part Catalog</p>
                        <p class="font-semibold text-gray-800">
                            Rp {{ number_format((float) $rlp->part_catalog_ext_price, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">WUR Grand Total</p>
                        <p class="font-semibold text-gray-800">
                            Rp {{ number_format((float) $rlp->wur_grand_total, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Total Items</p>
                        <p class="font-semibold text-gray-800">{{ $rlp->items->count() }} Item</p>
                    </div>
                </div>
            </div>

            {{-- Items & Comparison --}}
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 sm:px-8 pt-6 pb-2">
                    <h3 class="text-sm font-semibold text-gray-800">Daftar Item & Perbandingan Vendor</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[880px]">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-12 text-center">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Job Code</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Deskripsi / P/N</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center w-24">Qty / UOM</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Part Catalog</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Selected Vendor</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Vendor Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($rlp->items as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $item->jobCode->job_code ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        <div class="font-medium whitespace-pre-line">{{ $item->description }}</div>
                                        @if($item->pn)
                                            <span class="text-xs text-gray-400">P/N: {{ $item->pn }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700 font-semibold">{{ $item->qty }} {{ $item->uom }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 tabular-nums">
                                        <div>Rp {{ number_format((float) $item->part_catalog_ext_price, 0, ',', '.') }}</div>
                                        <span class="text-[11px] text-gray-400">@ Rp {{ number_format((float) $item->part_catalog_u_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 font-medium">
                                        {{ $item->selectedVendor->vendor_name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 tabular-nums">
                                        Rp {{ number_format((float) ($item->selectedVendor->ext_price ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada item dalam dokumen RRP ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 sm:px-8 py-5 border-t border-gray-100 flex justify-end">
                    <div class="w-full sm:w-72 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Total Part Catalog</span>
                            <span>Rp {{ number_format((float) $rlp->part_catalog_ext_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>WUR Grand Total</span>
                            <span>Rp {{ number_format((float) $rlp->wur_grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 font-semibold {{ $rlp->revenue_ext_price < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                            <span>Revenue (Ext)</span>
                            <span>Rp {{ number_format((float) $rlp->revenue_ext_price, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approvals --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <x-document-approval-manager type="rlp" :document="$rlp" />
            </div>
        </div>
    </div>
</x-app-layout>

