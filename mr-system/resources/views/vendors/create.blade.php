<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Add Vendor</h1>
                <p class="text-sm text-gray-500 mt-1">Fill in the vendor's details. Vendor code is generated automatically.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('vendors.index') }}" class="hover:text-indigo-600 transition">Vendor</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Add New</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">

                @if($errors->any())
                    <div class="m-6 mb-0 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('vendors.store') }}">
                    @include('vendors._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
