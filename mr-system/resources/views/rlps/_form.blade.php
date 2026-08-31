{{-- Header RLP --}}
<div>
    <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">
        RRP Data
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">No RRP</label>
            <input type="text" name="no_rlp" value="{{ old('no_rlp', $rlp->no_rlp ?? '') }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Date</label>
            <input type="date" name="date" value="{{ old('date', isset($rlp) && $rlp->date ? $rlp->date->format('Y-m-d') : '') }}"
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
        </div>
    </div>
</div>

{{-- RLP Items (Description, PN, Qty, UOM, Job Code, Part Catalog) --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-1 pb-2 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">RRP Items</h3>
            <p class="text-xs text-gray-400 mt-0.5">One RRP number can contain multiple descriptions &amp; job codes.</p>
        </div>
        <button type="button" @click="addItem()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 transition shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Item
        </button>
    </div>

    <div class="space-y-4 mt-4">
        <template x-for="(item, index) in items" :key="index">
            <div class="border border-gray-200 rounded-lg p-4 relative">
                <button type="button" @click="removeItem(index)" x-show="items.length > 1" title="Delete item"
                        class="absolute top-3 right-3 inline-flex items-center justify-center w-7 h-7 rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>

                <p class="text-xs font-semibold text-gray-400 mb-3" x-text="'Item ' + (index + 1)"></p>

                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Description</label>
                    <textarea :name="'items[' + index + '][description]'" rows="2" required
                              x-model="item.description" placeholder="Material / part description"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">PN <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" :name="'items[' + index + '][pn]'"
                               x-model="item.pn" placeholder="Optional"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Quantity</label>
                        <input type="number" min="1" step="1" required
                               :name="'items[' + index + '][qty]'"
                               x-model.number="item.qty" @blur="item.qty = roundInt(item.qty)"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">UOM</label>
                        <input type="text" :name="'items[' + index + '][uom]'" required
                               x-model="item.uom" placeholder="Pcs"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Part Catalog U/Price</label>
                        <input type="text" inputmode="numeric" required
                               :value="formatThousands(item.part_catalog_u_price)"
                               @input="item.part_catalog_u_price = parseThousands($event.target.value); $event.target.value = formatThousands(item.part_catalog_u_price)"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-left">
                        <input type="hidden" :name="'items[' + index + '][part_catalog_u_price]'" :value="item.part_catalog_u_price">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Job Code <span class="text-gray-400 font-normal">(optional)</span></label>
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                            <button type="button" @click="open = !open"
                                    class="w-full flex items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-left hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition">
                                <span class="truncate" :class="item.job_code_id ? 'text-gray-700' : 'text-gray-400'"
                                      x-text="item.job_code_id ? jobCodeLabel(item.job_code_id) : 'No job code'"></span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-cloak
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute z-20 mt-1.5 w-72 max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg py-1">
                                <button type="button" @click="clearJobCode(item); open = false"
                                        class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm transition"
                                        :class="!item.job_code_id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-500 hover:bg-gray-50'">
                                    <span>No job code</span>
                                </button>
                                <template x-if="jobCodeOptions.length === 0">
                                    <p class="px-3 py-2 text-xs text-gray-400">No job codes yet. Add one in Master Data &rarr; Job Code.</p>
                                </template>
                                <template x-for="opt in jobCodeOptions" :key="opt.id">
                                    <button type="button" @click="selectJobCode(item, opt); open = false"
                                            class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm transition"
                                            :class="String(item.job_code_id) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50'">
                                        <span class="truncate">
                                            <span class="font-medium" x-text="opt.job_code"></span>
                                            <span class="text-gray-400" x-show="opt.description" x-text="' — ' + opt.description"></span>
                                        </span>
                                        <svg x-show="String(item.job_code_id) === String(opt.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" :name="'items[' + index + '][job_code_id]'" x-model="item.job_code_id">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Part Catalog Ext/Price</label>
                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600 text-left">
                            Rp <span x-text="formatMoney((Number(item.part_catalog_u_price) || 0) * (Number(item.qty) || 0))"></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="flex justify-end mt-3">
        <p class="text-sm text-gray-600">Total Part Catalog: <span class="font-semibold text-gray-800">Rp <span x-text="formatMoney(partCatalogTotalExt)"></span></span></p>
    </div>
</div>

{{-- Vendor Quotation (Tabel Penawaran) --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-1 pb-2 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Vendor Quotation</h3>
            <p class="text-xs text-gray-400 mt-0.5">Compare quotations from vendors, then combine the winning one into this RRP.</p>
        </div>
        <button type="button" @click="openVendorModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Quotation
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 mt-4">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-center font-medium text-gray-600 w-14">Select</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-48">Vendor</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Item Name</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Brand</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">PN <span class="text-gray-400 font-normal normal-case">(optional)</span></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-20">Qty</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-32">U/Price</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-32">Ext/Price</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-36">Delivery Estimate</th>
                    <th class="px-4 py-2 text-center font-medium text-gray-600 w-14">Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(vendor, index) in vendors" :key="index">
                    <tr class="border-t border-gray-100" :class="selectedVendorIndex === index ? 'bg-indigo-50/60' : ''">
                        <td class="px-4 py-3 text-center align-top pt-4">
                            <input type="radio" :name="'selected_vendor_index'" :value="index"
                                   x-model.number="selectedVendorIndex"
                                   title="Set as winning vendor"
                                   class="text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 align-top">
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-left hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition">
                                    <span class="truncate" :class="vendor.vendor_id ? 'text-gray-700' : 'text-gray-400'"
                                          x-text="vendor.vendor_id ? vendorNameById(vendor.vendor_id) : 'Select vendor'"></span>
                                    <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     class="absolute z-20 mt-1.5 w-56 max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg py-1">
                                    <template x-if="vendorOptions.length === 0">
                                        <p class="px-3 py-2 text-xs text-gray-400">No vendors yet. Add one in Master Data &rarr; Vendors.</p>
                                    </template>
                                    <template x-for="opt in vendorOptions" :key="opt.id">
                                        <button type="button" @click="vendor.vendor_id = opt.id; open = false"
                                                class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm transition"
                                                :class="String(vendor.vendor_id) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50'">
                                            <span class="truncate" x-text="opt.vendor_name"></span>
                                            <svg x-show="String(vendor.vendor_id) === String(opt.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                                <input type="hidden" :name="'vendors[' + index + '][vendor_id]'" x-model="vendor.vendor_id" required>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-top pt-4 text-gray-700">
                            <span x-text="vendor.item_name"></span>
                            <input type="hidden" :name="'vendors[' + index + '][item_name]'" :value="vendor.item_name">
                        </td>
                        <td class="px-4 py-3 align-top">
                            <input type="text" :name="'vendors[' + index + '][brand]'" required
                                   x-model="vendor.brand" placeholder="Brand"
                                   class="w-32 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </td>
                        <td class="px-4 py-3 align-top pt-4 text-gray-700">
                            <span x-text="vendor.part_number || '-'"></span>
                            <input type="hidden" :name="'vendors[' + index + '][part_number]'" :value="vendor.part_number">
                        </td>
                        <td class="px-4 py-3 align-top pt-4 text-gray-700 text-right">
                            <span x-text="vendor.qty"></span>
                            <input type="hidden" :name="'vendors[' + index + '][qty]'" :value="vendor.qty">
                        </td>
                        <td class="px-4 py-3 align-top">
                            <input type="number" step="0.01" min="0" required
                                   :name="'vendors[' + index + '][u_price]'"
                                   x-model.number="vendor.u_price"
                                   class="w-28 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-right">
                        </td>
                        <td class="px-4 py-3 align-top pt-4 font-medium text-gray-700" x-text="'Rp ' + formatMoney(vendorExt(vendor))"></td>
                        <td class="px-4 py-3 align-top">
                            <input type="text" :name="'vendors[' + index + '][delivery_estimate]'" required
                                   x-model="vendor.delivery_estimate" placeholder="e.g. 7 days"
                                   class="w-32 rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </td>
                        <td class="px-4 py-3 text-center align-top pt-4">
                            <button type="button" @click="removeVendor(index)" title="Delete"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </template>
                <tr x-show="vendors.length === 0">
                    <td colspan="10" class="px-4 py-6 text-center text-gray-400 text-sm">
                        No quotations yet. Click "Add Quotation" to add one.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <p class="mt-1.5 text-xs text-gray-400">Item Name, PN and Qty are taken automatically from the RRP Items above &mdash; only pick a vendor and fill in the price. Select one quotation (radio button) as the winning vendor &mdash; it becomes the basis for the Revenue calculation.</p>
</div>

{{-- Revenue --}}
<div class="mt-8 bg-indigo-50 border border-indigo-100 rounded-lg p-5">
    <h3 class="text-sm font-semibold text-gray-800 mb-1">Revenue</h3>
    <p class="text-xs text-gray-500 mb-4">Selected vendor's Ext/Price minus total Part Catalog Ext/Price.</p>
    <div class="bg-white rounded-lg p-4 border border-indigo-100 max-w-xs">
        <div class="text-xs text-gray-500 mb-1">Revenue (Ext/Price)</div>
        <div class="text-2xl font-semibold" :class="revenueExtPrice < 0 ? 'text-red-600' : 'text-emerald-600'"
             x-text="'Rp ' + formatMoney(revenueExtPrice)"></div>
    </div>
    <p class="mt-3 text-xs text-amber-600" x-show="!selectedVendor" x-cloak>
        Select a vendor first so Revenue can be calculated.
    </p>
</div>

{{-- WUR Cost Estimasi --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-800">WUR Cost Estimasi</h3>
        <button type="button" @click="addCostRow()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-50 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Row
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Job Description</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-28">Quantity</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-36">U/Price</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-40">Total</th>
                    <th class="px-4 py-2 text-center font-medium text-gray-600 w-20">Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(cost, index) in costs" :key="index">
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3">
                            <input type="text" :name="'costs[' + index + '][job_description]'"
                                   x-model="cost.job_description" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0" :name="'costs[' + index + '][quantity]'"
                                   x-model.number="cost.quantity"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-right">
                        </td>
                        <td class="px-4 py-3">
                            <input type="number" step="0.01" min="0" :name="'costs[' + index + '][u_price]'"
                                   x-model.number="cost.u_price"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-right">
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700"
                            x-text="'Rp ' + formatMoney((Number(cost.quantity) || 0) * (Number(cost.u_price) || 0))"></td>
                        <td class="px-4 py-3 text-center">
                            <button type="button" @click="removeCostRow(index)" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                Delete
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr class="border-t border-gray-200">
                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">Grand Total</td>
                    <td class="px-4 py-3 font-semibold text-gray-900" x-text="'Rp ' + formatMoney(wurGrandTotal)"></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="flex justify-end gap-3 pt-6 mt-8 border-t border-gray-100">
    <a href="{{ route('rlps.index') }}"
       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
        Cancel
    </a>
    <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 transition shadow-sm">
        {{ isset($rlp) ? 'Update RRP' : 'Save RRP' }}
    </button>
</div>
