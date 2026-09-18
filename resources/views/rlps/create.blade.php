<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Add Local Purchase (RRP)</h1>
                <p class="text-sm text-gray-500 mt-1">Create a new local purchase request with multi-vendor price comparison.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('rlps.index') }}" class="hover:text-indigo-600 transition">RRP</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Add New</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-8"
                 x-data="rlpForm({
                    vendorOptions: {{ json_encode($vendorOptions) }},
                    jobCodeOptions: {{ json_encode($jobCodeOptions) }},
                    vendors: {{ json_encode(old('vendors', [])) }},
                    items: {{ json_encode(old('items', [])) }},
                    costs: {{ json_encode(old('costs', [])) }}
                 })" x-init="initSignPad()">

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
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    @include('rlps._script')
</x-app-layout>
