<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-2xl text-gray-800 leading-tight">Purchase Order</h1>
        <p class="text-sm text-gray-500 mt-1">Manage purchase orders based on Material Requests.</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">
                <div class="flex flex-col items-center justify-center text-center px-6 py-20">
                    <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mb-5">
                        <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.5m6-1.5v1.5M5.25 8.25h13.5l-1.5 9.75a2.25 2.25 0 01-2.222 1.875H8.972a2.25 2.25 0 01-2.222-1.875l-1.5-9.75zM7.5 8.25V6a4.5 4.5 0 119 0v2.25" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-800">Purchase Order Module Coming Soon</h2>
                    <p class="text-sm text-gray-500 mt-2 max-w-md">
                        This feature is currently under development. You'll soon be able to create and manage
                        Purchase Orders directly from approved Material Requests.
                    </p>
                    <a href="{{ route('material-requests.index') }}"
                       class="mt-6 inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                        Back to Material Request
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
