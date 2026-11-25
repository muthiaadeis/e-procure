<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0 max-w-full">
                <h1 class="font-bold text-2xl text-gray-800 leading-tight break-all">{{ $purchaseOrder->po_no }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $purchaseOrder->subject ?? '-' }}
                    @if($purchaseOrder->rlp)
                        · dari RRP
                        <a href="{{ route('rlps.index') }}" class="text-indigo-600 hover:underline font-medium break-all">
                            {{ $purchaseOrder->rlp->no_rlp }}
                        </a>
                    @else
                        · <span class="text-gray-400">Pesanan Langsung</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($purchaseOrder->purchaseRequest)
                    <a href="{{ route('purchase-requests.show', $purchaseOrder->purchaseRequest) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold rounded-lg hover:bg-amber-100 transition shadow-xs">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PR Doc ({{ $purchaseOrder->purchaseRequest->no_request }})
                    </a>
                @elseif(auth()->user()->isInput() && $purchaseOrder->is_approved && ! $purchaseOrder->is_rejected)
                    <a href="{{ route('purchase-requests.create', ['from_po' => $purchaseOrder->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg hover:bg-amber-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Dokumentasi PR
                    </a>
                @endif
                @if((auth()->user()->isInput() || auth()->user()->isAdmin()) && $purchaseOrder->is_rejected)
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit & Ajukan Ulang</span>
                    </a>
                @endif
                <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Cetak / PDF
                </a>
                @php
                    $isFromDashboard = request('from') === 'dashboard' || str_contains(url()->previous(), route('dashboard'));
                    $backUrl = $isFromDashboard ? route('dashboard') : route('purchase-orders.index');
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
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Purchase Order No</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseOrder->po_no }}">{{ $purchaseOrder->po_no }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Our Reference</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseOrder->our_reference }}">{{ $purchaseOrder->our_reference ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Supplier No</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseOrder->supplier_no }}">{{ $purchaseOrder->supplier_no ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Our Order Date</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->our_order_date?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Revision</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->revision ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Client</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseOrder->client }}">{{ $purchaseOrder->client ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Charge To</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $purchaseOrder->charge_to }}">{{ $purchaseOrder->charge_to ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Prepared By</p>
                        <p class="font-semibold text-gray-800 truncate" title="{{ $purchaseOrder->creator->name ?? '-' }}">{{ $purchaseOrder->creator->name ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Status</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->status }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">To</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->to_address ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-2">Attn</p>
                        <p class="text-sm text-gray-700">{{ $purchaseOrder->attn ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Invoice Address</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->invoice_address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Subject</p>
                        <p class="text-sm text-gray-700">{{ $purchaseOrder->subject ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-2">Project Description</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->project_description ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-2">Contact Number</p>
                        <p class="text-sm text-gray-700">{{ $purchaseOrder->contact_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Final Delivery Address</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->final_delivery_address ?? '-' }}</p>
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
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Qty</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">UOM</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Brand</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Unit Price</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->line_no }}</td>
                                    <td class="px-4 py-3 text-gray-700 whitespace-pre-line">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->uom }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->brand ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format((float) $item->total_price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 sm:px-8 py-5 border-t border-gray-100 flex justify-end">
                    <div class="w-full sm:w-64 space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Total</span>
                            <span>Rp {{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($purchaseOrder->use_ppn)
                            <div class="flex justify-between text-gray-500">
                                <span>PPN {{ rtrim(rtrim(number_format((float) $purchaseOrder->ppn_percent, 2, '.', ''), '0'), '.') }}%</span>
                                <span>Rp {{ number_format((float) $purchaseOrder->ppn_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-1.5 border-t border-gray-200 font-semibold text-gray-800">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format((float) $purchaseOrder->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approvals --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <x-document-approval-manager type="po" :document="$purchaseOrder" />
            </div>
        </div>
    </div>
</x-app-layout>
