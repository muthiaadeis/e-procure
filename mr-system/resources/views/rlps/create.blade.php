<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add RRP
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8"
                 x-data="rlpForm({
                    vendorOptions: {{ json_encode($vendorOptions) }},
                    jobCodeOptions: {{ json_encode($jobCodeOptions) }},
                    items: {{ json_encode(old('items', [])) }},
                    vendors: {{ json_encode(old('vendors', [])) }},
                    costs: {{ json_encode(old('costs', [])) }},
                    selectedVendorIndex: {{ old('selected_vendor_index') !== null ? (int) old('selected_vendor_index') : 'null' }}
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

                <form action="{{ route('rlps.store') }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    @include('rlps._form')
                </form>

                @include('rlps._vendor_modal')
            </div>
        </div>
    </div>

    @include('rlps._script')
</x-app-layout>
