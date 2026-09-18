@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between gap-3 flex-wrap">
        <p class="text-xs text-gray-500">
            Showing <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-gray-700">{{ $paginator->total() }}</span> results
        </p>

        <div class="inline-flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-300 cursor-not-allowed" aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            @endif

            {{-- Current page indicator (no clickable page numbers, just prev/next) --}}
            <span class="inline-flex items-center justify-center px-3 h-8 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-semibold">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-300 cursor-not-allowed" aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
