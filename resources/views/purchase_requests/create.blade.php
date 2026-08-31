<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Add Purchase Request</h1>
                <p class="text-sm text-gray-500 mt-1">Create a new purchase request.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('purchase-requests.index') }}" class="hover:text-indigo-600 transition">PR</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Add New</span>
            </nav>
        </div>
    </x-slot>

    @php($isEdit = false)

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden"
                 x-data="purchaseRequestForm({
                    items: {{ Js::from(old('items', [])) }},
                    use_ppn: {{ Js::from(old('use_ppn', false)) }},
                    ppn_percent: {{ Js::from(old('ppn_percent', '11')) }}
                 })">
                <form action="{{ route('purchase-requests.store') }}" method="POST" @submit="handleSubmit($event)">
                    @csrf
                    @include('purchase_requests._form')
                </form>
            </div>
        </div>
    </div>

    @include('purchase_requests._script')
</x-app-layout>
