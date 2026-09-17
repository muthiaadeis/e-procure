<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Edit Purchase Request (PR)</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $purchaseRequest->no_request }}</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('purchase-requests.index') }}" class="hover:text-indigo-600 transition">PR</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Edit</span>
            </nav>
        </div>
    </x-slot>

    @php($isEdit = true)

    @php
        $itemsForJs = old('items') ?: $purchaseRequest->items->map(fn($i) => [
            'description'    => $i->description,
            'line_table'     => $i->line_table,
            'line_sub_table' => $i->line_sub_table,
            'qty'            => (string) $i->qty,
            'unit'           => $i->unit,
            'price'          => (string) $i->price,
            'remarks'        => $i->remarks,
        ])->values()->all();
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseRequestForm({
                    items: {{ Js::from($itemsForJs) }},
                    use_ppn: {{ Js::from(old('use_ppn', $purchaseRequest->use_ppn)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', (string) $purchaseRequest->ppn_percent)) }}
                 })">
                <form action="{{ route('purchase-requests.update', $purchaseRequest) }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    @method('PUT')
                    @include('purchase_requests._form')
                </form>
            </div>
        </div>
    </div>

    @include('purchase_requests._script')
</x-app-layout>
