@props(['title' => 'Segera Hadir', 'description' => 'This module is currently under development.'])

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 flex flex-col items-center justify-center text-center min-h-[420px]">
    <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 mb-5">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
        </svg>
    </div>
    <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
    <p class="text-sm text-gray-500 mt-1.5 max-w-sm">{{ $description }}</p>
    <span class="mt-5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold">
        In Development
    </span>
</div>
