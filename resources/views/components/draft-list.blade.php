@props(['form'])

@php
    $drafts = \App\Models\Draft::where('user_id', auth()->id())
        ->where('form', $form)
        ->latest('updated_at')
        ->get();
@endphp

@if($drafts->isNotEmpty())
    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 overflow-hidden">
        <div class="flex items-center gap-2 px-5 py-3 border-b border-amber-200 bg-amber-100/60">
            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <h3 class="text-sm font-semibold text-amber-900">Draft belum selesai ({{ $drafts->count() }})</h3>
        </div>

        <ul class="divide-y divide-amber-100">
            @foreach($drafts as $draft)
                <li class="flex flex-wrap items-center gap-3 px-5 py-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 truncate">
                            {{ $draft->title ?: 'Belum ada nomor dokumen' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Terakhir diedit {{ $draft->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    <a href="{{ $draft->resumeUrl() }}"
                       class="inline-flex items-center px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                        Lanjutkan
                    </a>

                    <form action="{{ route('drafts.remove', $draft) }}" method="POST"
                          onsubmit="return confirm('Hapus draft ini? Isinya tidak bisa dikembalikan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                            Hapus
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endif
