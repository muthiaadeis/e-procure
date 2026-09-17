<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Purchase Request (PR)</h1>
                <p class="text-sm text-gray-500 mt-1">Internal documentation detailing recipients and project allocation for ordered goods.</p>
            </div>
            <a href="{{ route('purchase-requests.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add PR
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
            confirmOpen: false,
            confirmMessage: '',
            confirmFormId: null,
            openConfirm(message, formId) {
                this.confirmMessage = message;
                this.confirmFormId = formId;
                this.confirmOpen = true;
            },
            submitConfirm() {
                this.confirmOpen = false;
                document.getElementById(this.confirmFormId).submit();
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="flex-shrink-0 text-green-500 hover:text-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-xl p-6 mb-4">
                <form method="GET" action="{{ route('purchase-requests.index') }}" class="flex items-center gap-2">
                    <div class="relative flex-1 sm:flex-none">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? '' }}"
                               placeholder="Search request no, title, client, or job no..."
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-full sm:w-80 pl-10 py-2">
                    </div>
                    <button type="submit" class="text-sm font-semibold text-gray-600 hover:text-indigo-600 px-3 py-2">Search</button>
                    @if(!empty($search))
                        <a href="{{ route('purchase-requests.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Reset</a>
                    @endif
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Request No</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Title</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Job No</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Grand Total</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600 w-40">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($purchaseRequests as $pr)
                                @php
                                    $signedCount = $pr->approvals->whereNotNull('signed_at')->count();
                                    $totalCount = $pr->approvals->count();
                                    $statusColor = match($pr->status) {
                                        'Completed' => 'bg-green-100 text-green-700',
                                        'In Progress' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <tr @click="window.location.href = '{{ route('purchase-requests.show', $pr) }}'"
                                    class="hover:bg-gray-50/70 transition cursor-pointer">
                                    <td class="px-4 py-3 font-semibold text-gray-800">
                                        <div>{{ $pr->no_request }}</div>
                                        @if($pr->purchaseOrder)
                                            <a href="{{ route('purchase-orders.show', $pr->purchaseOrder) }}"
                                               @click.stop
                                               class="inline-flex items-center gap-1 text-[11px] font-normal text-indigo-600 hover:text-indigo-800 transition">
                                                <span>PO: {{ $pr->purchaseOrder->po_no }}</span>
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $pr->date?->format('d-m-Y') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-700 max-w-[220px] truncate" title="{{ $pr->title }}">{{ $pr->title }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $pr->client ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $pr->job_no ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format((float) $pr->grand_total, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                            {{ $pr->status }} ({{ $signedCount }}/{{ $totalCount }})
                                        </span>
                                    </td>
                                    <td class="px-4 py-3" @click.stop>
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('purchase-requests.print', $pr) }}" target="_blank" title="Print"
                                               class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                                </svg>
                                            </a>
                                            @if($pr->is_draft)
                                                <form id="delete-pr-{{ $pr->id }}" action="{{ route('purchase-requests.destroy', $pr) }}" method="POST" class="hidden">
                                                    @csrf @method('DELETE')
                                                </form>
                                                <button type="button"
                                                        @click="openConfirm('Delete Purchase Request {{ $pr->no_request }}? This cannot be undone.', 'delete-pr-{{ $pr->id }}')"
                                                        title="Delete"
                                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">No purchase requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $purchaseRequests->links() }}
            </div>
        </div>

        {{-- Confirm delete modal --}}
        <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="absolute inset-0 bg-gray-900/40" @click="confirmOpen = false"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-sm w-full p-6">
                <h3 class="font-semibold text-gray-800 mb-2">Confirm</h3>
                <p class="text-sm text-gray-600 mb-6" x-text="confirmMessage"></p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="confirmOpen = false" class="text-sm font-semibold text-gray-500 hover:text-gray-700">Cancel</button>
                    <button type="button" @click="submitConfirm()" class="text-sm font-semibold bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Delete</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
