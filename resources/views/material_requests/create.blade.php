<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Add Material Request (MR)</h1>
                <p class="text-sm text-gray-500 mt-1">Create a new material or inventory request.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('material-requests.index') }}" class="hover:text-indigo-600 transition">MR</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Add New</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
            items: [{ description: '', quantity: '', unit: '', remarks: '' }],
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

                @if($errors->any())
                    <div class="m-6 mb-0 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('material-requests.store') }}" method="POST">
                    @csrf

                    <div class="p-6 sm:p-8 space-y-8">
                        {{-- Request details --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-4">
                                Request Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        No MR <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v16l-4-2-3 2-3-2-3 2-3-2V4a1 1 0 011-1z"/>
                                            </svg>
                                        </span>
                                        <input type="text" name="no_mr" value="{{ old('no_mr') }}" required
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                                    </div>
                                    <p class="mt-1.5 text-xs text-gray-400">Enter Material Request number.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Charge To</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M13 21V11l6 3v7M9 9v.01M9 12v.01M9 15v.01"/>
                                            </svg>
                                        </span>
                                        <input type="text" name="charge_to" value="{{ old('charge_to') }}" required
                                               placeholder=""
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
                                                        <select :name="'items[' + index + '][unit]'" x-model="item.unit" required
                                                                class="w-full rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-700 hover:border-gray-300 focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition">
                                                            <option value="" disabled>Select</option>
                                                            <template x-for="opt in ['Pcs','Unit','Set','Box','Roll','Meter','Kg','Liter','Sheet','Rod']" :key="opt">
                                                                <option :value="opt" x-text="opt"></option>
                                                            </template>
                                                        </select>
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

                    <div class="flex items-center justify-end gap-5 px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
                        <a href="{{ route('material-requests.index') }}"
                           onclick="event.preventDefault(); window.location.replace('{{ route('material-requests.index') }}')"
                           class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
