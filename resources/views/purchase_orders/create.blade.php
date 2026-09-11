<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Add Purchase Order</h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if ($fromPurchaseRequest)
                        Generated from PR <span class="font-semibold text-indigo-600">{{ $fromPurchaseRequest->no_request }}</span> — please review before saving.
                    @else
                        Create a new purchase order.
                    @endif
                </p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-indigo-600 transition">PO</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Add New</span>
            </nav>
        </div>
    </x-slot>

    @php($isEdit = false)

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseOrderForm({
                    items: {{ Js::from(old('items', $prefill['items'] ?? [])) }},
                    use_ppn: {{ Js::from(old('use_ppn', true)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', '11')) }}
                 })">
                <form action="{{ route('purchase-orders.store') }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    <input type="hidden" name="purchase_request_id"
                           value="{{ old('purchase_request_id', $prefill['purchase_request_id'] ?? '') }}">
                    @include('purchase_orders._form')
                </form>
            </div>
        </div>
    </div>

    @include('purchase_orders._script')
</x-app-layout>
