<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0 max-w-full">
                <h1 class="font-bold text-2xl text-gray-800 leading-tight break-all">{{ $purchaseRequest->no_request }}</h1>
                <p class="text-sm text-gray-500 mt-1 break-all">{{ $purchaseRequest->title }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($purchaseRequest->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $purchaseRequest->purchaseOrder) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold rounded-lg hover:bg-emerald-100 transition shadow-xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PO ({{ $purchaseRequest->purchaseOrder->po_no }})
                    </a>
                @endif
                @if((auth()->user()->isInput() || auth()->user()->isAdmin()) && $purchaseRequest->is_rejected)
                    <a href="{{ route('purchase-requests.edit', $purchaseRequest) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition shadow-xs">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit & Ajukan Ulang</span>
                    </a>
                @endif
                <a href="{{ route('purchase-requests.print', $purchaseRequest) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Cetak / PDF
                </a>
                @php
                    $isFromDashboard = request('from') === 'dashboard' || str_contains(url()->previous(), route('dashboard'));
                    $backUrl = $isFromDashboard ? route('dashboard') : route('purchase-requests.index');
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
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Request No</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseRequest->no_request }}">{{ $purchaseRequest->no_request }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Date</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->date?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Job Location</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseRequest->job_location }}">{{ $purchaseRequest->job_location ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Client</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseRequest->client }}">{{ $purchaseRequest->client ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Charge To</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseRequest->charge_to }}">{{ $purchaseRequest->charge_to ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Job No</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseRequest->job_no }}">{{ $purchaseRequest->job_no ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Location Project</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseRequest->location_project }}">{{ $purchaseRequest->location_project ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Prepared By</p>
                        <p class="font-semibold text-gray-800 truncate" title="{{ $purchaseRequest->creator->name ?? '-' }}">{{ $purchaseRequest->creator->name ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Status</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->status }}</p>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 sm:px-8 pt-6 pb-2">
                    <h3 class="text-sm font-semibold text-gray-800">Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[860px]">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Line Item</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Qty</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Unit</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Price</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Total Price</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchaseRequest->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->line_no }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $item->line_table ?? '-' }}
                                        @if($item->line_sub_table)
                                            <span class="block text-xs text-gray-400">{{ $item->line_sub_table }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->unit }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format((float) $item->total_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 sm:px-8 py-5 border-t border-gray-100 flex flex-col sm:flex-row sm:justify-between gap-6">
                    <div class="max-w-md">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Note</p>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $purchaseRequest->note ?? '-' }}</p>
                    </div>
                    <div class="w-full sm:w-64 space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format((float) $purchaseRequest->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($purchaseRequest->use_ppn)
                            <div class="flex justify-between text-gray-500">
                                <span>PPN {{ rtrim(rtrim(number_format((float) $purchaseRequest->ppn_percent, 2, '.', ''), '0'), '.') }}%</span>
                                <span>Rp {{ number_format((float) $purchaseRequest->ppn_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-1.5 border-t border-gray-200 font-semibold text-gray-800">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format((float) $purchaseRequest->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approvals --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <x-document-approval-manager type="pr" :document="$purchaseRequest" />
            </div>
        </div>
    </div>
</x-app-layout>
