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

    @php($isEdit = true)

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseOrderForm({
                    items: {{ Js::from(old('items', $itemsForJs)) }},
                    use_ppn: {{ Js::from(old('use_ppn', $purchaseOrder->use_ppn)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', (string) $purchaseOrder->ppn_percent)) }}
                 })">
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
