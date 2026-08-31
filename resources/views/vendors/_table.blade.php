@if($search)
    <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
        <span>Showing results for</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-medium">"{{ $search }}"</span>
        <span>&mdash; {{ $vendors->total() }} results found</span>
    </div>
@endif

<div class="overflow-x-auto">
    <table class="w-full text-sm text-left border-separate border-spacing-0">
        <thead>
            <tr class="bg-gray-50 text-gray-600 uppercase text-xs align-middle">
                <th class="px-4 py-3 rounded-l-lg whitespace-nowrap">No</th>
                <th class="px-4 py-3 whitespace-nowrap">Code Vendor</th>
                <th class="px-4 py-3 min-w-[160px]">Vendor Name</th>
                <th class="px-4 py-3 whitespace-nowrap">Brand</th>
                <th class="px-4 py-3 min-w-[200px]">Address</th>
                <th class="px-4 py-3 whitespace-nowrap">Email</th>
                <th class="px-4 py-3 whitespace-nowrap">Phone</th>
                <th class="px-4 py-3 rounded-r-lg text-center whitespace-nowrap">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($vendors as $vendor)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3.5 text-gray-500">{{ $vendors->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-indigo-50 text-indigo-700 font-semibold text-xs">
                        {{ $vendor->vendor_code }}
                    </span>
                </td>
                <td class="px-4 py-3.5 text-gray-800 font-medium">{{ $vendor->vendor_name }}</td>
                <td class="px-4 py-3.5 text-gray-600 whitespace-nowrap">
                    @if($vendor->brand)
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-medium">
                            {{ $vendor->brand }}
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td class="px-4 py-3.5 text-gray-600">{{ $vendor->vendor_address ?? '-' }}</td>
                <td class="px-4 py-3.5 text-gray-600 whitespace-nowrap">
                    @if($vendor->email)
                        <span class="inline-flex items-center gap-1 text-xs">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $vendor->email }}
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td class="px-4 py-3.5 text-gray-600 whitespace-nowrap">
                    @if($vendor->phone)
                        <span class="inline-flex items-center gap-1 text-xs">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $vendor->phone }}
                        </span>
                    @else
                        -
                    @endif
                </td>
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
                                <a href="{{ route('vendors.edit', $vendor->id) }}"
                                   class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>

                                <form id="delete-form-{{ $vendor->id }}" class="hidden"
                                      action="{{ route('vendors.destroy', $vendor->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button"
                                        @click="rowOpen = false; openConfirm('Delete This Vendor?', 'Vendor {{ addslashes($vendor->vendor_name) }} will be permanently deleted.', 'delete-form-{{ $vendor->id }}')"
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
                <td colspan="8" class="text-center py-12 text-gray-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                        </svg>
                        <span>{{ $search ? 'No vendors match your search' : 'No vendor data yet' }}</span>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $vendors->links() }}
</div>
