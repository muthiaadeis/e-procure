<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Rekapitulasi Rencana Pengadaan (RRP)</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola dokumen RRP, perbandingan harga vendor, dan alur persetujuan.</p>
            </div>
            @if(auth()->user()->isInput())
                <a href="{{ route('rlps.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah RRP
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-draft-list form="rlps" />

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
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                </div>
            @endif

            {{-- Search Bar --}}
            <div class="bg-white shadow-sm rounded-xl p-6 mb-4">
                <form method="GET" action="{{ route('rlps.index') }}" class="flex items-center gap-2">
                    <div class="relative flex-1 sm:flex-none">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Cari No. RRP..."
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-full sm:w-80 pl-10 py-2">
                    </div>
                    <button type="submit" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 px-3 py-2">Cari</button>
                    @if(!empty($search))
                        <a href="{{ route('rlps.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
                    @endif
                </form>
            </div>

            {{-- Tabel RRP --}}
            <div id="rlp-table-container" class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" style="table-layout:fixed; min-width:980px;">
                        <colgroup>
                            <col style="width: 50px;">
                            <col style="width: 220px;">
                            <col style="width: 110px;">
                            <col>
                            <col style="width: 160px;">
                            <col style="width: 170px;">
                            <col style="width: 100px;">
                        </colgroup>
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-left">No</th>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-left">No RRP</th>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-left">Date</th>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-left">Selected Vendor</th>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-center">Revenue (Ext)</th>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-center">Status</th>
                                <th class="px-3 py-3 whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-gray-600 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($rlps as $rlp)
                                @php
                                    $vendorNames = $rlp->items->map(fn ($i) => $i->selectedVendor->vendor_name ?? null)->filter()->unique()->values();
                                    $vendorDisplay = '-';
                                    if ($vendorNames->isNotEmpty()) {
                                        $vendorDisplay = $vendorNames->count() === 1
                                            ? $vendorNames->first()
                                            : $vendorNames->first() . ' +' . ($vendorNames->count() - 1) . ' more';
                                    }

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
                                <tr onclick="window.location.href = '{{ route('rlps.show', $rlp) }}'"
                                    data-href="{{ route('rlps.show', $rlp) }}"
                                    class="hover:bg-gray-50/70 transition cursor-pointer">
                                    <td class="px-3 py-3.5 text-gray-500">{{ $rlps->firstItem() + $loop->index }}</td>
                                    <td class="px-3 py-3.5 font-medium text-gray-800">
                                        <a href="{{ route('rlps.show', $rlp) }}" class="hover:text-indigo-600 transition block max-w-[210px] truncate" title="{{ $rlp->no_rlp }}">
                                            {{ $rlp->no_rlp }}
                                        </a>
                                        @if($rlp->purchaseOrder)
                                        <div class="mt-1">
                                            <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                PO: {{ $rlp->purchaseOrder->po_no }}
                                            </span>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3.5 text-gray-600 whitespace-nowrap">{{ $rlp->date ? $rlp->date->format('d-m-Y') : '-' }}</td>
                                    <td class="px-3 py-3.5 text-gray-600">
                                        <div title="{{ $vendorDisplay }}" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word;">
                                            {{ $vendorDisplay }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5 text-center font-medium whitespace-nowrap tabular-nums {{ $rlp->revenue_ext_price < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        Rp {{ number_format($rlp->revenue_ext_price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center whitespace-nowrap px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                            {{ $rlp->status }}@if($totalCount > 1 && $rlp->status !== 'Done' && $rlp->status !== 'Pending Setup' && !str_starts_with($rlp->status, 'Rejected')) ({{ $signedCount }}/{{ $totalCount }})@endif
                                        </span>
                                    </td>
                                    <td class="px-2 py-3.5" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-center gap-1">
                                            @if((auth()->user()->isInput() || auth()->user()->isAdmin()) && $rlp->is_rejected)
                                                <a href="{{ route('rlps.edit', $rlp) }}" title="Edit & Ajukan Ulang"
                                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-amber-600 hover:text-amber-700 hover:bg-amber-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            <a href="{{ route('rlps.print', $rlp) }}" target="_blank" title="Cetak / PDF"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                                </svg>
                                            </a>
                                            <a href="{{ route('rlps.show', $rlp) }}" title="Lihat Detail"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                                        Belum ada dokumen RRP yang terdaftar.
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
    </div>
</x-app-layout>
