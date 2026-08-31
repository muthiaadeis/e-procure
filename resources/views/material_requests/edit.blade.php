<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Edit Material Request</h1>
                <p class="text-sm text-gray-500 mt-1">Fix the details of a rejected material request.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('material-requests.index') }}" class="hover:text-indigo-600 transition">MR</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Edit</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-8"
         x-data="{
            charge_to: {{ Illuminate\Support\Js::from(old('charge_to', $materialRequest->charge_to)) }},
            items: {{ Illuminate\Support\Js::from(
                old('items', $materialRequest->items->map(fn ($item) => [
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'remarks' => $item->remarks,
                ])->values())
            ) }},
            original: {
                charge_to: {{ Illuminate\Support\Js::from($materialRequest->charge_to) }},
                items: {{ Illuminate\Support\Js::from(
                    $materialRequest->items->map(fn ($item) => [
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'remarks' => $item->remarks,
                    ])->values()
                ) }}
            },
            get isDirty() {
            return JSON.stringify({ charge_to: this.charge_to, items: this.items })
                !== JSON.stringify(this.original);
        },
            addItem() {
                this.items.push({ description: '', quantity: '', unit: '', remarks: '' });
                $nextTick(() => {
                    const el = document.getElementById('item-row-' + (this.items.length - 1));
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            }
         }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">

                @if($materialRequest->is_rejected)
                    <div class="m-6 mb-0 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        <span>This MR was previously <strong>rejected</strong>. If you save changes, it will automatically be resubmitted from Approval 1 for review.</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="m-6 mb-0 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('material-requests.update', $materialRequest->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="p-6 sm:p-8 space-y-8">
                        {{-- Request details --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-4">
                                Request Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">No MR</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v16l-4-2-3 2-3-2-3 2-3-2V4a1 1 0 011-1z"/>
                                            </svg>
                                        </span>
                                        <input type="text" value="{{ $materialRequest->no_mr }}" readonly tabindex="-1"
                                               class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm text-sm pl-10 text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-400">No MR is permanent and can't be changed.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Charge To</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M13 21V11l6 3v7M9 9v.01M9 12v.01M9 15v.01"/>
                                            </svg>
                                        </span>
                                        <input type="text" name="charge_to" x-model="charge_to" required
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MR items (description, quantity, unit, remarks per item) --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-800">Material Items</h3>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="items.length + ' item(s) added'"></p>
                                </div>
                                <button type="button" @click="addItem()"
                                        class="inline-flex items-center gap-1.5 text-indigo-600 border border-indigo-200 hover:bg-indigo-50 hover:border-indigo-300 px-3.5 py-2 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Item
                                </button>
                            </div>

                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm min-w-[640px] divide-y divide-gray-200">
                                        <thead>
                                            <tr class="bg-indigo-50/60 text-left divide-x divide-indigo-100">
                                                <th class="px-4 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide">Material Description</th>
                                                <th class="px-3 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide w-24">Qty</th>
                                                <th class="px-3 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide w-32">Unit</th>
                                                <th class="px-4 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide">Remarks</th>
                                                <th class="w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <template x-for="(item, index) in items" :key="index">
                                                <tr :id="'item-row-' + index" class="group align-top hover:bg-gray-50/70 transition divide-x divide-gray-100">
                                                    <td class="px-4 py-2.5">
                                                        <textarea :name="'items[' + index + '][description]'" rows="1" required
                                                                x-model="item.description"
                                                                placeholder=""
                                                                class="w-full resize-y min-h-[38px] rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-700 placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition"></textarea>
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <input type="number" :name="'items[' + index + '][quantity]'" min="1" required
                                                            x-model.number="item.quantity"
                                                            placeholder=""
                                                            class="w-full rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-700 placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition">
                                                    </td>
                                                    <td class="px-3 py-2.5">
                                                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                                            <button type="button" @click="open = !open"
                                                                    class="w-full flex items-center justify-between gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-left hover:border-gray-300 focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition">
                                                                <span class="truncate" :class="item.unit ? 'text-gray-700' : 'text-gray-400'"
                                                                      x-text="item.unit || 'Select'"></span>
                                                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                </svg>
                                                            </button>
                                                            <div x-show="open" x-cloak
                                                                 x-transition:enter="transition ease-out duration-100"
                                                                 x-transition:enter-start="opacity-0 scale-95"
                                                                 x-transition:enter-end="opacity-100 scale-100"
                                                                 class="absolute z-20 mt-1.5 w-36 max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg py-1">
                                                                <template x-for="opt in ['Pcs','Unit','Set','Box','Roll','Meter','Kg','Liter','Sheet','Rod']" :key="opt">
                                                                    <button type="button" @click="item.unit = opt; open = false"
                                                                            class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm transition"
                                                                            :class="item.unit === opt ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50'">
                                                                        <span x-text="opt"></span>
                                                                        <svg x-show="item.unit === opt" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                                        </svg>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                            <input type="hidden" :name="'items[' + index + '][unit]'" x-model="item.unit" required>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2.5">
                                                        <input type="text" :name="'items[' + index + '][remarks]'" required
                                                            x-model="item.remarks"
                                                            placeholder=""
                                                            class="w-full rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-700 placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition">
                                                    </td>
                                                    <td class="px-2 py-2.5 text-right">
                                                        <button type="button" @click="removeItem(index)"
                                                                x-show="items.length > 1"
                                                                title="Remove item"
                                                                class="opacity-0 group-hover:opacity-100 focus:opacity-100 w-7 h-7 inline-flex items-center justify-center rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <p class="mt-2.5 flex items-center gap-1.5 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Fill in the material details above. Click "Add Item" to add a new row.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
                        <span class="text-xs text-gray-400 mr-auto" x-show="!isDirty" x-cloak>No changes yet</span>
                        <a href="{{ route('material-requests.index') }}"
                           onclick="event.preventDefault(); window.location.replace('{{ route('material-requests.index') }}')"
                           class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
                            Cancel
                        </a>
                        <button type="submit"
                                :disabled="!isDirty"
                                :class="isDirty ? 'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white cursor-pointer' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
