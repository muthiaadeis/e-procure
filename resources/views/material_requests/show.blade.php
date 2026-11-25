<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div class="min-w-0 max-w-full">
                <h1 class="font-bold text-2xl text-gray-800 leading-tight break-all">{{ $materialRequest->no_mr }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Charge To: <span class="font-medium text-gray-700 break-all">{{ $materialRequest->charge_to ?? '-' }}</span>
                    · Tanggal: <span class="font-medium text-gray-700">{{ $materialRequest->date?->format('d-m-Y') ?? '-' }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if((auth()->user()->isInput() || auth()->user()->isAdmin()) && $materialRequest->is_rejected)
                    <a href="{{ route('material-requests.edit', $materialRequest) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition shadow-xs">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Edit & Ajukan Ulang</span>
                    </a>
                @endif
                <a href="{{ route('material-requests.print', $materialRequest) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition shadow-xs">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                    </svg>
                    Cetak / PDF
                </a>
                @php
                    $isFromDashboard = request('from') === 'dashboard' || str_contains(url()->previous(), route('dashboard'));
                    $backUrl = $isFromDashboard ? route('dashboard') : route('material-requests.index');
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
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Material Request No</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $materialRequest->no_mr }}">{{ $materialRequest->no_mr }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Date</p>
                        <p class="font-semibold text-gray-800">{{ $materialRequest->date?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Charge To</p>
                        <p class="font-semibold text-gray-800 break-all leading-snug" title="{{ $materialRequest->charge_to }}">{{ $materialRequest->charge_to ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Prepared By</p>
                        <p class="font-semibold text-gray-800 truncate" title="{{ $materialRequest->creator->name ?? '-' }}">{{ $materialRequest->creator->name ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Created At</p>
                        <p class="font-semibold text-gray-800">{{ $materialRequest->created_at?->format('d-m-Y H:i') ?? '-' }}</p>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Status</p>
                        <p class="font-semibold text-gray-800">{{ $materialRequest->status }}</p>
                    </div>
                </div>

                @if($materialRequest->is_overdue)
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span>{{ $materialRequest->overdue_reason }}</span>
                    </div>
                @endif
                @if($materialRequest->is_rejected_by_finance)
                    <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Ditolak oleh Finance: {{ $materialRequest->financeRejector->name ?? '-' }}@if($materialRequest->finance_rejection_reason) &mdash; Alasan: {{ $materialRequest->finance_rejection_reason }}@endif</span>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 sm:px-8 pt-6 pb-2">
                    <h3 class="text-sm font-semibold text-gray-800">Daftar Item Material</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[720px]">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-12 text-center">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Deskripsi Item</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center w-28">Jumlah</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase w-24">Satuan</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Catatan / Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($materialRequest->items as $index => $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 text-center">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-gray-800 font-medium whitespace-pre-line">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700 font-semibold">{{ $item->quantity }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->unit }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->remarks ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada item dalam permintaan material ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Approvals --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <x-document-approval-manager type="mr" :document="$materialRequest" />
            </div>
        </div>
    </div>
</x-app-layout>

