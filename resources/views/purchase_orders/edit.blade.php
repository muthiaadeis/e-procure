<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Edit Purchase Order (PO)</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $purchaseOrder->po_no }}</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-indigo-600 transition">PO</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Edit</span>
            </nav>
        </div>
    </x-slot>

    @php
        $isEdit = true;
        $rej = $purchaseOrder->approvals->first(fn($a) => $a->isRejected());
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseOrderForm({
                    items: {{ Js::from(old('items', $itemsForJs)) }},
                    use_ppn: {{ Js::from(old('use_ppn', $purchaseOrder->use_ppn)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', (string) $purchaseOrder->ppn_percent)) }}
                 })">
                @if($purchaseOrder->is_rejected)
                    <div class="m-6 mb-0 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm shadow-xs">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        <div class="flex-1">
                            <p class="font-semibold text-amber-900">Dokumen Purchase Order ini sebelumnya <u>DITOLAK</u>{{ $rej ? ' pada tahap ' . $rej->label . ' (' . ($rej->user->name ?? 'Approver') . ')' : '' }}.</p>
                            @if($rej && $rej->rejection_reason)
                                <div class="mt-1.5 text-xs text-amber-900 bg-white/80 p-2.5 rounded-lg border border-amber-200">
                                    <span class="font-bold text-red-800">Alasan Penolakan:</span>
                                    <p class="mt-0.5 whitespace-pre-line">{{ $rej->rejection_reason }}</p>
                                </div>
                            @endif
                            <p class="mt-2 text-xs text-amber-700">Jika Anda menyimpan perubahan ini, dokumen Purchase Order akan <strong>otomatis diajukan ulang dari awal</strong> dan semua tanda tangan persetujuan sebelumnya akan di-reset.</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    @method('PUT')
                    @include('purchase_orders._form')
                </form>
            </div>
        </div>
    </div>

    @include('purchase_orders._script')
</x-app-layout>
