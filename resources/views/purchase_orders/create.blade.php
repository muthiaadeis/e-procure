<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Tambah Purchase Order (PO)</h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if ($fromRlp)
                        Generated from RRP <span class="font-semibold text-indigo-600">{{ $fromRlp->no_rlp }}</span> — quotation and selected vendor details prefilled.
                    @else
                        Direct Purchase Order — create an order directly for a single vendor without RRP comparison.
                    @endif
                </p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-indigo-600 transition">PO</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Tambah Baru</span>
            </nav>
        </div>
    </x-slot>

    @php($isEdit = false)

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseOrderForm({
                    items: {{ Js::from(old('items', $prefill['items'] ?? [])) }},
                    use_ppn: {{ Js::from(old('use_ppn', $prefill['use_ppn'] ?? true)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', '11')) }}
                 })">
                <form action="{{ route('purchase-orders.store') }}" method="POST" @submit="handleSubmit($event)"
                        data-draft="purchase-orders"
                        data-draft-context="{{ $prefill['rlp_id'] ?? '' }}"
                        data-draft-url="{{ url('drafts') }}"
                        data-draft-state="items,use_ppn"
                        data-draft-skip="{{ count(session()->getOldInput()) ? 1 : 0 }}"
                        data-draft-resume="{{ request()->boolean('resume') ? 1 : 0 }}">
                    @csrf
                    <input type="hidden" name="rlp_id"
                           value="{{ old('rlp_id', $prefill['rlp_id'] ?? '') }}">
                    @include('purchase_orders._form')
                </form>
            </div>
        </div>
    </div>

    @include('purchase_orders._script')
</x-app-layout>
