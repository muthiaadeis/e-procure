@php
    // Vendor master list: kumpulan vendor unik dari semua item RRP ini (urut sesuai kemunculan pertama).
    // Delivery estimate diambil dari kemunculan pertama vendor tsb.
    $vendorMasterList = collect();
    foreach ($rlp->items as $i) {
        foreach ($i->vendors as $v) {
            if (! $vendorMasterList->has($v->vendor_id)) {
                $vendorMasterList->put($v->vendor_id, [
                    'vendor_id' => $v->vendor_id,
                    'delivery_estimate' => $v->delivery_estimate,
                ]);
            }
        }
    }
    $vendorMasterList = $vendorMasterList->values();

    $initialItems = old('items', $rlp->items->map(function ($i) use ($vendorMasterList) {
        $quotesByVendorId = $i->vendors->keyBy('vendor_id');

        // quotes sejajar urutannya dengan $vendorMasterList — kalau item ini dulu gak
        // punya penawaran dari vendor tsb (data lama), harganya dikosongkan dan perlu diisi ulang.
        $quotes = $vendorMasterList->map(function ($masterVendor) use ($quotesByVendorId) {
            $match = $quotesByVendorId->get($masterVendor['vendor_id']);
            return [
                'u_price' => $match ? (string) (int) round($match->u_price) : '',
            ];
        })->values()->all();

        $selectedIndex = null;
        if ($i->selected_vendor_id) {
            $idx = $vendorMasterList->search(fn ($mv) => $mv['vendor_id'] === $i->selected_vendor_id);
            $selectedIndex = $idx === false ? null : $idx;
        }

        return [
            'job_code_id' => $i->job_code_id,
            'description' => $i->description,
            'pn' => $i->pn,
            'qty' => (string) $i->qty,
            'uom' => $i->uom,
            'part_catalog_u_price' => (string) (int) round($i->part_catalog_u_price),
            'selected_vendor_index' => $selectedIndex,
            'quotes' => $quotes,
        ];
    })->values()->all());

    $initialVendors = old('vendors', $vendorMasterList->all());

    $initialCosts = old('costs', $rlp->costs->map(fn ($c) => [
        'job_description' => $c->job_description,
        'quantity' => (string) (int) $c->quantity,
        'u_price' => (string) (int) round($c->u_price),
    ])->values()->all());
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Edit Local Purchase (RRP) — {{ $rlp->no_rlp }}</h1>
                <p class="text-sm text-gray-500 mt-1">Update quotation and vendor comparison details.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('rlps.index') }}" class="hover:text-indigo-600 transition">RRP</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Edit</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8"
                 x-data="rlpForm({
                    vendorOptions: {{ json_encode($vendorOptions) }},
                    jobCodeOptions: {{ json_encode($jobCodeOptions) }},
                    vendors: {{ json_encode($initialVendors) }},
                    items: {{ json_encode($initialItems) }},
                    costs: {{ json_encode($initialCosts) }}
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
            </div>
        </div>
    </div>

    @include('rlps._script')
</x-app-layout>
