@php
    $initialItems = old('items', $rlp->items->map(fn ($i) => [
        'description' => $i->description,
        'pn' => $i->pn,
        'qty' => (int) $i->qty,
        'uom' => $i->uom,
        'job_code_id' => $i->job_code_id,
        'part_catalog_u_price' => (float) $i->part_catalog_u_price,
    ])->values()->all());

    $initialVendors = old('vendors', $rlp->vendors->map(fn ($v) => [
        'vendor_id' => $v->vendor_id,
        'vendor_name' => $v->vendor_name,
        'item_name' => $v->item_name,
        'brand' => $v->brand,
        'part_number' => $v->part_number,
        'qty' => (int) $v->qty,
        'u_price' => (float) $v->u_price,
        'delivery_estimate' => $v->delivery_estimate,
    ])->values()->all());

    $initialCosts = old('costs', $rlp->costs->map(fn ($c) => [
        'job_description' => $c->job_description,
        'quantity' => (float) $c->quantity,
        'u_price' => (float) $c->u_price,
    ])->values()->all());

    $initialSelectedIndex = old('selected_vendor_index');
    if ($initialSelectedIndex === null && $rlp->selected_vendor_id) {
        $idx = $rlp->vendors->search(fn ($v) => $v->id === $rlp->selected_vendor_id);
        $initialSelectedIndex = $idx === false ? null : $idx;
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit RRP — {{ $rlp->no_rlp }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8"
                 x-data="rlpForm({
                    vendorOptions: {{ json_encode($vendorOptions) }},
                    jobCodeOptions: {{ json_encode($jobCodeOptions) }},
                    items: {{ json_encode($initialItems) }},
                    vendors: {{ json_encode($initialVendors) }},
                    costs: {{ json_encode($initialCosts) }},
                    selectedVendorIndex: {{ $initialSelectedIndex !== null ? (int) $initialSelectedIndex : 'null' }}
                 })">

                @if($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('rlps.update', $rlp) }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    @method('PUT')
                    @include('rlps._form', ['rlp' => $rlp])
                </form>

                @include('rlps._vendor_modal')
            </div>
        </div>
    </div>

    @include('rlps._script')
</x-app-layout>
