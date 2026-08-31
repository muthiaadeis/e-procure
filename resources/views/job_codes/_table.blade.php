@if($search)
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <span>Showing results for</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-medium">"{{ $search }}"</span>
        <span>&mdash; {{ $jobCodes->total() }} results found</span>
    </div>
@endif

<div class="overflow-x-auto">
    <table class="w-full text-sm text-left border-separate border-spacing-0">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-xs align-middle">
                <th class="px-4 py-3 rounded-l-lg text-center whitespace-nowrap">No</th>
                <th class="px-4 py-3 whitespace-nowrap">Job Code</th>
                <th class="px-4 py-3 min-w-[220px]">Description</th>
                <th class="px-4 py-3 text-center whitespace-nowrap">Price</th>
                <th class="px-4 py-3 text-center whitespace-nowrap">Part Number</th>
                <th class="px-4 py-3 rounded-r-lg text-center whitespace-nowrap">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($jobCodes as $jc)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3.5 text-gray-500 text-center">{{ $jobCodes->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 font-semibold text-xs">
                        {{ $jc->job_code }}
                    </span>
                </td>
                <td class="px-4 py-3.5 text-gray-600">{{ $jc->description ?? '-' }}</td>
                <td class="px-4 py-3.5 text-gray-800 font-medium whitespace-nowrap text-center">Rp {{ number_format($jc->price, 2) }}</td>
                <td class="px-4 py-3.5 text-gray-600 whitespace-nowrap text-center">{{ $jc->part_number ?? '-' }}</td>
                <td class="px-4 py-3.5">
                    <div class="flex items-center justify-center">
                        <div class="relative" x-data="{ rowOpen: false }" @click.outside="rowOpen = false">
                            <button type="button" @click="rowOpen = !rowOpen" title="Actions"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </button>

                            <div x-show="rowOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 z-20 mt-1 w-36 rounded-lg border border-gray-100 bg-white shadow-lg py-1">
                                <a href="{{ route('job-codes.edit', $jc->id) }}"
                                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>

                                <form id="delete-form-{{ $jc->id }}" class="hidden"
                                      action="{{ route('job-codes.destroy', $jc->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button"
                                        @click="rowOpen = false; openConfirm('Delete This Job Code?', 'Job code {{ addslashes($jc->job_code) }} will be permanently deleted.', 'delete-form-{{ $jc->id }}')"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-12 text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                        </svg>
                        <span>{{ $search ? 'No job codes match your search' : 'No job code data yet' }}</span>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $jobCodes->links() }}
</div>
