@if($errors->any())
    <div class="m-6 mb-0 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="p-6 sm:p-8 space-y-8">

    {{-- Purchase Order No + Supplier Order --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Purchase Order Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Order No</label>
                <input type="text" value="{{ $isEdit ? $purchaseOrder->po_no : $nextPoNo }}" readonly tabindex="-1"
                       class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm text-sm text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                <p class="mt-1.5 text-xs text-gray-400">PO No is generated automatically.</p>
            </div>

            <div class="border border-gray-200 rounded-xl p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 mb-3">Supplier Order</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Our Reference</label>
                        <input type="text" name="our_reference"
                               value="{{ old('our_reference', $isEdit ? $purchaseOrder->our_reference : '') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Supplier No</label>
                        <input type="text" name="supplier_no"
                               value="{{ old('supplier_no', $isEdit ? $purchaseOrder->supplier_no : '') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Our Order Date</label>
                        <input type="date" name="our_order_date"
                               value="{{ old('our_order_date', $isEdit && $purchaseOrder->our_order_date ? $purchaseOrder->our_order_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Revision</label>
                        <input type="text" name="revision"
                               value="{{ old('revision', $isEdit ? $purchaseOrder->revision : '0') }}"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- To/Attn & Invoice Address --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border border-gray-200 rounded-xl p-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">To :</label>
                <textarea name="to_address" rows="2" placeholder="Supplier name &amp; address..."
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('to_address', $isEdit ? $purchaseOrder->to_address : '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Attn :</label>
                <input type="text" name="attn" placeholder="Attention to..."
                       value="{{ old('attn', $isEdit ? $purchaseOrder->attn : '') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>
        </div>

        <div class="border border-gray-200 rounded-xl p-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Invoice Address :</label>
            <textarea name="invoice_address" rows="5" placeholder="Invoice address..."
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('invoice_address', $isEdit ? $purchaseOrder->invoice_address : '') }}</textarea>
        </div>
    </div>

    {{-- Subject block & Final Delivery Address --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="border border-gray-200 rounded-xl p-4 space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                <input type="text" name="subject"
                       value="{{ old('subject', $isEdit ? $purchaseOrder->subject : '') }}"
                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Project Description</label>
                <textarea name="project_description" rows="2"
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('project_description', $isEdit ? $purchaseOrder->project_description : '') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Contact Number</label>
                    <input type="text" name="contact_number"
                           value="{{ old('contact_number', $isEdit ? $purchaseOrder->contact_number : '') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Client</label>
                    <input type="text" name="client"
                           value="{{ old('client', $isEdit ? $purchaseOrder->client : '') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>
        </div>

        <div class="border border-gray-200 rounded-xl p-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Final Delivery Address</label>
            <textarea name="final_delivery_address" rows="7" placeholder="Delivery address..."
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('final_delivery_address', $isEdit ? $purchaseOrder->final_delivery_address : '') }}</textarea>
        </div>
    </div>

    {{-- Items --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-[11px] font-bold mr-1.5 align-middle">1</span>
                    PO Items
                </h3>
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
                <table class="w-full text-sm min-w-[1000px] table-fixed divide-y divide-gray-200">
                    <colgroup>
                        <col class="w-10">     {{-- No --}}
                        <col class="w-[30%]">  {{-- Description --}}
                        <col class="w-16">     {{-- Qty --}}
                        <col class="w-16">     {{-- UOM (dikecilin) --}}
                        <col class="w-32">     {{-- Brand --}}
                        <col class="w-36">     {{-- Unit Price (dibesarin) --}}
                        <col class="w-32">     {{-- Total Price --}}
                        <col class="w-9">      {{-- Action --}}
                    </colgroup>
                    <thead>
                        <tr class="bg-indigo-50/60 divide-x divide-indigo-100">
                            <th class="px-2 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">No</th>
                            <th class="px-3 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">Description</th>
                            <th class="px-2 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">Qty</th>
                            <th class="px-2 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">UOM</th>
                            <th class="px-3 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">Brand</th>
                            <th class="px-3 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">Unit Price</th>
                            <th class="px-3 py-3 text-xs font-semibold text-indigo-700 uppercase tracking-wide text-center">Total Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        <template x-for="(item, index) in items" :key="index">
                            <tr :id="'po-item-row-' + index" class="group align-top hover:bg-gray-50/70 transition divide-x divide-gray-100">
                                <td class="px-2 py-2.5 text-center text-xs font-semibold text-indigo-600 pt-3.5" x-text="index + 1"></td>

                                {{-- Description (bisa lebih dari 1 baris deskripsi dalam 1 row) --}}
                                <td class="px-2 py-2.5">
                                    <textarea :name="'items[' + index + '][description]'" rows="3" required
                                              x-model="item.description"
                                              placeholder="Enter description... "
                                              class="w-full max-w-full box-border resize-y rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-sm text-gray-700 placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition break-words"></textarea>
                                </td>

                                {{-- Qty --}}
                                <td class="px-1.5 py-2.5">
                                    <textarea rows="1" inputmode="numeric" required
                                              :value="formatThousands(item.qty)"
                                              @input="item.qty = parseThousands($event.target.value)"
                                              @focus="$event.target.select()"
                                              placeholder="0"
                                              class="w-full max-w-full box-border resize-none rounded-md border border-gray-200 bg-white px-1 py-1.5 text-sm text-gray-700 text-center placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition break-all"></textarea>
                                    <input type="hidden" :name="'items[' + index + '][qty]'" :value="item.qty">
                                </td>

                                {{-- UOM --}}
                                <td class="px-1.5 py-2.5">
                                    <textarea :name="'items[' + index + '][uom]'" rows="1" required
                                              x-model="item.uom" placeholder="Unit"
                                              class="w-full max-w-full box-border resize-y rounded-md border border-gray-200 bg-white px-1 py-1.5 text-sm text-gray-700 text-center placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition break-words"></textarea>
                                </td>

                                {{-- Brand --}}
                                <td class="px-2 py-2.5">
                                    <textarea :name="'items[' + index + '][brand]'" rows="1"
                                              x-model="item.brand" placeholder="Brand"
                                              class="w-full max-w-full box-border resize-y rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-gray-700 placeholder:text-gray-400 hover:border-gray-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition break-words"></textarea>
                                </td>

                                {{-- Unit Price --}}
                                <td class="px-2 py-2.5">
                                    <textarea rows="1" inputmode="numeric" required
                                              :value="formatThousands(item.price)"
                                              @input="item.price = parseThousands($event.target.value)"
                                              @focus="$event.target.select()"
                                              placeholder="0"
                                              class="w-full max-w-full box-border resize-y rounded-md border border-indigo-200 bg-white px-2 py-1.5 text-sm text-gray-700 text-left placeholder:text-gray-400 hover:border-indigo-300 focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition break-all"></textarea>
                                    <input type="hidden" :name="'items[' + index + '][price]'" :value="item.price">
                                </td>

                                {{-- Total Price --}}
                                <td class="px-2 py-2.5 text-left pt-3.5">
                                    <span class="font-semibold text-indigo-700 text-sm block break-all" x-text="formatRupiah(itemTotal(item))"></span>
                                </td>

                                {{-- Action --}}
                                <td class="px-1 py-2.5 text-center">
                                    <button type="button" @click="removeItem(index)"
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
    </div>

    {{-- Summary --}}
    <div class="flex flex-col md:flex-row gap-6 items-stretch justify-end">
        <div class="md:w-5/12 flex flex-col justify-end gap-3">
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 flex items-center justify-start gap-4">
                <p class="text-xs text-gray-500 w-32 shrink-0">Total</p>
                <p class="text-sm font-semibold text-gray-900 break-all text-left" x-text="formatRupiah(subtotal)"></p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 flex items-center justify-start gap-4">
                <label class="inline-flex items-center gap-2 text-xs text-gray-600 font-medium cursor-pointer w-32 shrink-0">
                    <input type="checkbox" name="use_ppn" value="1" x-model="use_ppn"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                    <span x-text="'PPN (' + ppn_percent + '%)'"></span>
                </label>
                <p class="text-sm font-semibold text-gray-900 break-all text-left" x-text="formatRupiah(ppnAmount)"></p>
            </div>

            <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 flex items-center justify-start gap-4">
                <p class="text-sm font-bold text-gray-800 w-32 shrink-0">Grand Total</p>
                <p class="text-base font-bold text-indigo-700 break-all text-left" x-text="formatRupiah(grandTotal)"></p>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-5 px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
    <a href="{{ route('purchase-orders.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
        Cancel
    </a>
    <button type="submit"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
        {{ $isEdit ? 'Update' : 'Save' }}
    </button>
</div>
