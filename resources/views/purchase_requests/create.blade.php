<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Tambah Purchase Request (PR)</h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if ($fromPurchaseOrder)
                        Documenting item allocation for PO <span class="font-semibold text-indigo-600">{{ $fromPurchaseOrder->po_no }}</span> — specify internal project, client, and recipient details.
                    @else
                        Dokumentasikan alokasi barang, penugasan proyek, dan penerima.
                    @endif
                </p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('purchase-requests.index') }}" class="hover:text-indigo-600 transition">PR</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Tambah Baru</span>
            </nav>
        </div>
    </x-slot>

    @php($isEdit = false)

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseRequestForm({
                    items: {{ Js::from(old('items', $prefill['items'] ?? [])) }},
                    use_ppn: {{ Js::from(old('use_ppn', $prefill['use_ppn'] ?? false)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', $prefill['ppn_percent'] ?? '11')) }}
                 })">
                <form action="{{ route('purchase-requests.store') }}" method="POST" @submit="handleSubmit($event)"
                        data-draft="purchase-requests"
                        data-draft-context="{{ $prefill['purchase_order_id'] ?? '' }}"
                        data-draft-url="{{ url('drafts') }}"
                        data-draft-state="items,use_ppn"
                        data-draft-skip="{{ count(session()->getOldInput()) ? 1 : 0 }}"
                        data-draft-resume="{{ request()->boolean('resume') ? 1 : 0 }}">
                    @csrf
                    <input type="hidden" name="purchase_order_id"
                           value="{{ old('purchase_order_id', $prefill['purchase_order_id'] ?? '') }}">
                    @include('purchase_requests._form')
                </form>
            </div>
        </div>
    </div>

    @include('purchase_requests._script')
</x-app-layout>
