{{-- Header RRP --}}
<div>
    <h3 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-100">
        RRP Data
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                No RRP <span class="text-red-500">*</span>
            </label>
            <input type="text" name="no_rlp" value="{{ old('no_rlp', $rlp->no_rlp ?? '') }}" required
                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <p class="mt-1.5 text-xs text-gray-400">Enter RRP number.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Date</label>
            <input type="text"
                   value="{{ isset($rlp) ? ($rlp->date ? $rlp->date->format('d-m-Y') : '-') : now()->format('d-m-Y') }}"
                   readonly tabindex="-1"
                   class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm text-sm text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300">
            <p class="mt-1.5 text-xs text-gray-400">Date is set automatically.</p>
        </div>
    </div>
</div>

{{-- STEP 1: RRP Items --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-1 pb-2 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-[11px] font-bold mr-1.5 align-middle">1</span>
                RRP Items
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Pick the Job Code first — Description, PN, and Part Catalog price fill in automatically. Add every item you need before moving to Vendors below.</p>
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
        <template x-for="(item, index) in items" :key="item.uid">
                        <div class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-400" x-text="'Item ' + (index + 1)"></p>
                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" title="Delete item"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Job Code <span class="text-red-500">*</span></label>
                    <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false; search = ''">
                        <button type="button" @click="open = !open; $nextTick(() => open && $refs.jcSearch && $refs.jcSearch.focus())"
                                class="w-full flex items-center justify-between gap-2 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-left hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition">
                            <span class="truncate" :class="item.job_code_id ? 'text-gray-700' : 'text-gray-400'"
                                  x-text="item.job_code_id ? jobCodeLabel(item.job_code_id) : 'Select job code'"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute z-20 mt-2 w-full sm:w-[28rem] max-h-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl">
                            <div class="p-2 border-b border-gray-100">
                                <input type="text" x-ref="jcSearch" x-model="search" @click.stop
                                       placeholder="Search job code or description..."
                                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="max-h-64 overflow-y-auto py-1">
                                <template x-if="jobCodeOptions.length === 0">
                                    <p class="px-4 py-3 text-xs text-gray-400">No job codes yet. Add one in Master Data &rarr; Job Code.</p>
                                </template>
                                <template x-if="jobCodeOptions.length > 0 && filteredJobCodeOptions(search).length === 0">
                                    <p class="px-4 py-3 text-xs text-gray-400">No matching job codes.</p>
                                </template>
                                <template x-for="opt in filteredJobCodeOptions(search)" :key="opt.id">
                                    <button type="button" @click="selectJobCode(item, opt.id); open = false; search = ''"
                                            class="w-full flex items-center justify-between gap-2 text-left px-4 py-2.5 text-sm transition"
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
                        </div>
                        <input type="hidden" :name="'items[' + index + '][job_code_id]'" x-model="item.job_code_id" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Description <span class="text-gray-400 font-normal">(auto)</span></label>
                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 cursor-not-allowed truncate">
                            <span x-text="item.description || '—'"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">PN <span class="text-gray-400 font-normal">(auto)</span></label>
                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 cursor-not-allowed truncate">
                            <span x-text="item.pn || '—'"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Quantity</label>
                        <input type="text" inputmode="numeric"
                               :name="'items[' + index + '][qty]'"
                               x-model="item.qty"
                               @input="item.qty = item.qty.replace(/[^0-9]/g, '')"
                               @blur="item.qty = String(roundInt(item.qty))"
                               placeholder="1"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">UOM</label>
                        <input type="text" :name="'items[' + index + '][uom]'" required
                               x-model="item.uom" placeholder="Pcs"
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Catalog U/Price <span class="text-gray-400 font-normal">(auto)</span></label>
                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 cursor-not-allowed text-left">
                            Rp <span x-text="formatThousands(item.part_catalog_u_price)"></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Catalog Ext/Price</label>
                        <div class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 cursor-not-allowed text-left">
                            Rp <span x-text="formatThousands(itemPartCatalogExt(item))"></span>
                        </div>
                    </div>
                </div>

                {{-- Revenue preview per item --}}
                <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                    <template x-if="itemVendorsWithQuotes(item).length === 0">
                        <span class="text-gray-400">Revenue will calculate automatically once vendor prices are entered in Step 2 below.</span>
                    </template>
                    <template x-if="itemVendorsWithQuotes(item).length > 0">
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-700">
                                    Revenue preview:
                                    <span class="font-bold ml-1" :class="itemRevenueExt(item) < 0 ? 'text-red-600' : 'text-emerald-600'"
                                          x-text="'Rp ' + formatThousands(itemRevenueExt(item))"></span>
                                    <span class="text-[11px] text-gray-400 font-normal ml-1" x-show="!selectedVendorObj">(Best potential)</span>
                                    <span class="text-[11px] text-indigo-600 font-semibold ml-1" x-show="selectedVendorObj">(Selected Winner)</span>
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5 pt-0.5" x-show="itemVendorsWithQuotes(item).length > 1 && !selectedVendorObj">
                                <template x-for="vq in itemVendorsWithQuotes(item)" :key="vq.uid">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] bg-gray-50 border border-gray-200 text-gray-600">
                                        <span x-text="vq.name + ':'"></span>
                                        <strong :class="vq.revenue < 0 ? 'text-red-600' : 'text-emerald-700'" x-text="'Rp ' + formatThousands(vq.revenue)"></strong>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Carries the globally-selected winning vendor onto every item (backend needs it per item) --}}
                <template x-if="selectedVendorIndex !== null">
                    <input type="hidden" :name="'items[' + index + '][selected_vendor_index]'" :value="selectedVendorIndex">
                </template>
            </div>
        </template>
    </div>

    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 mt-4 inline-block">
        <p class="text-xs text-gray-500 mb-0.5">Grand Total (Part Catalog)</p>
        <p class="text-sm font-semibold text-gray-900">Rp <span x-text="formatThousands(partCatalogTotalExt)"></span></p>
    </div>
</div>

{{-- STEP 2: Vendors — one full-width form per vendor, filled in against the items above --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-1 pb-2 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[11px] font-bold mr-1.5 align-middle"
                      :class="itemsReady ? 'bg-indigo-600' : 'bg-gray-300'">2</span>
                Vendors
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Add a vendor and fill in its price for every item above. Add as many vendors as you want to compare, then pick the winner — if you add a new item later, come back here and fill in its price too.</p>
        </div>
        <button type="button" @click="addVendor()" :disabled="!itemsReady"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 border text-xs font-semibold rounded-lg transition shrink-0"
                :class="itemsReady ? 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-gray-50 border-gray-200 text-gray-300 cursor-not-allowed'">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Vendor
        </button>
    </div>

    <template x-if="!itemsReady">
        <div class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center text-xs text-gray-400">
            Complete every item in Step 1 first (Job Code, Quantity, UOM) — the Vendors form unlocks once all items are filled in.
        </div>
    </template>

    <div class="space-y-5 mt-4" x-show="itemsReady" x-cloak>
        <template x-for="(vendor, vIndex) in vendors" :key="vendor.uid">
            <div class="rounded-xl border p-5 transition"
                 :class="selectedVendorUid === vendor.uid ? 'border-indigo-300 bg-indigo-50/40 ring-1 ring-indigo-100' : 'border-gray-200'">

                <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Vendor <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{ open: false, search: '' }" @click.outside="open = false; search = ''">
                                <button type="button" @click="open = !open; $nextTick(() => open && $refs.vSearch && $refs.vSearch.focus())"
                                        class="w-full flex items-center justify-between gap-2 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-left hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition">
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
                                     class="absolute z-20 mt-2 w-full sm:w-96 max-h-80 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl">
                                    <div class="p-2 border-b border-gray-100">
                                        <input type="text" x-ref="vSearch" x-model="search" @click.stop
                                               placeholder="Search vendor..."
                                               class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    <div class="max-h-64 overflow-y-auto py-1">
                                        <template x-if="vendorOptions.length === 0">
                                            <p class="px-4 py-3 text-xs text-gray-400">No vendors yet. Add one in Master Data &rarr; Vendors.</p>
                                        </template>
                                        <template x-if="vendorOptions.length > 0 && filteredVendorOptions(search).length === 0">
                                            <p class="px-4 py-3 text-xs text-gray-400">No matching vendors.</p>
                                        </template>
                                        <template x-for="opt in filteredVendorOptions(search)" :key="opt.id">
                                            <button type="button" @click="vendor.vendor_id = opt.id; open = false; search = ''"
                                                    class="w-full flex items-center justify-between gap-2 text-left px-4 py-2.5 text-sm transition"
                                                    :class="String(vendor.vendor_id) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50'">
                                                <span class="truncate" x-text="opt.vendor_name"></span>
                                                <svg x-show="String(vendor.vendor_id) === String(opt.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Delivery Estimate <span class="text-red-500">*</span></label>
                            <input type="text" x-model="vendor.delivery_estimate" placeholder="e.g. 7 days"
                                   class="w-full rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm py-3 px-4">
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        {{-- Vendor Revenue Badge (shows immediately when prices are entered) --}}
                        <div x-show="vendorHasQuotes(vendor)" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold border"
                             :class="vendorRevenue(vendor) >= 0 ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'">
                            <span class="text-gray-500 font-normal">Est. Revenue:</span>
                            <span class="font-bold" x-text="'Rp ' + formatThousands(vendorRevenue(vendor))"></span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold"
                                  :class="vendorRevenue(vendor) >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                  x-show="partCatalogTotalExt > 0"
                                  x-text="vendorRevenueMargin(vendor) + '%'"></span>
                        </div>

                        <button type="button" @click="selectedVendorUid = (selectedVendorUid === vendor.uid ? null : vendor.uid)"
                                class="inline-flex items-center gap-1.5 px-3 py-3 rounded-xl text-xs font-semibold border transition whitespace-nowrap"
                                :class="selectedVendorUid === vendor.uid ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50'">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="selectedVendorUid === vendor.uid ? 'Winning Vendor' : 'Pick as Winner'"></span>
                        </button>
                        <button type="button" @click="removeVendor(vIndex)" title="Delete vendor"
                                class="inline-flex items-center gap-1.5 px-4 py-3 rounded-xl bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition shrink-0 whitespace-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>

                {{-- Discount & PPN 11% — opsional, cuma diisi kalau vendor kasih diskon/kena pajak --}}
                <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-end gap-4">
                    <div class="w-36">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Discount (%) <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input type="text" inputmode="decimal" x-model="vendor.discount_percent"
                                @input="vendor.discount_percent = vendor.discount_percent.replace(/[^0-9.]/g, '')"
                                placeholder="0"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none pb-2.5">
                        <input type="checkbox" x-model="vendor.use_ppn"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        PPN 11% <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                </div>

                {{-- Price per item for this vendor --}}
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">Item</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 w-24">Qty</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 w-36">U/Price</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 w-36">Ext/Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, iIndex) in items" :key="item.uid">
                                <tr class="border-t border-gray-100"
                                    :class="(vendor.quotes[item.uid] === '' || vendor.quotes[item.uid] === undefined) ? 'bg-amber-50/50' : ''">
                                    <td class="px-3 py-2 align-top">
                                        <p class="text-gray-700 font-medium truncate" x-text="item.job_code_id ? jobCodeLabel(item.job_code_id) : '—'"></p>
                                        <p class="text-gray-400 text-xs truncate" x-text="item.description || ''"></p>
                                        <span x-show="vendor.quotes[item.uid] === '' || vendor.quotes[item.uid] === undefined"
                                              class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-700">Needs price</span>
                                    </td>
                                    <td class="px-3 py-2 align-top pt-3 text-gray-600" x-text="(item.qty || 0) + ' ' + (item.uom || '')"></td>
                                    <td class="px-3 py-2 align-top">
                                        <input type="text" inputmode="numeric"
                                               :value="formatThousands(vendor.quotes[item.uid])"
                                               @input="vendor.quotes[item.uid] = parseThousands($event.target.value)"
                                               placeholder="0"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                        <input type="hidden" :name="'items[' + iIndex + '][vendors][' + vIndex + '][vendor_id]'" :value="vendor.vendor_id">
                                        <input type="hidden" :name="'items[' + iIndex + '][vendors][' + vIndex + '][delivery_estimate]'" :value="vendor.delivery_estimate">
                                        <input type="hidden" :name="'items[' + iIndex + '][vendors][' + vIndex + '][u_price]'" :value="vendor.quotes[item.uid]">
                                        <input type="hidden" :name="'items[' + iIndex + '][vendors][' + vIndex + '][discount_percent]'" :value="vendor.discount_percent">
                                        <input type="hidden" :name="'items[' + iIndex + '][vendors][' + vIndex + '][use_ppn]'" :value="vendor.use_ppn ? 1 : 0">
                                    </td>
                                    <td class="px-3 py-2 align-top pt-3">
                                        <div class="font-medium text-gray-700" x-text="'Rp ' + formatThousands(vendorItemExt(vendor, item))"></div>
                                        <template x-if="vendorItemRevenue(vendor, item) !== null">
                                            <div class="text-[11px] mt-0.5 font-medium" :class="vendorItemRevenue(vendor, item) >= 0 ? 'text-emerald-600' : 'text-red-500'">
                                                <span class="text-gray-400 font-normal">Rev:</span> Rp <span x-text="formatThousands(vendorItemRevenue(vendor, item))"></span>
                                            </div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr x-show="Number(vendor.discount_percent) > 0 || vendor.use_ppn">
                                <td colspan="3" class="px-3 py-1.5 text-right text-xs text-gray-500">Subtotal</td>
                                <td class="px-3 py-1.5 text-xs text-gray-700 font-medium" x-text="'Rp ' + formatThousands(vendorGrandTotal(vendor))"></td>
                            </tr>
                            <tr x-show="Number(vendor.discount_percent) > 0">
                                <td colspan="3" class="px-3 py-1.5 text-right text-xs text-gray-500">
                                    Discount (<span x-text="vendor.discount_percent"></span>%)
                                </td>
                                <td class="px-3 py-1.5 text-xs text-red-600 font-medium" x-text="'- Rp ' + formatThousands(vendorDiscountAmount(vendor))"></td>
                            </tr>
                            <tr x-show="vendor.use_ppn">
                                <td colspan="3" class="px-3 py-1.5 text-right text-xs text-gray-500">PPN 11%</td>
                                <td class="px-3 py-1.5 text-xs text-gray-700 font-medium" x-text="'+ Rp ' + formatThousands(vendorPpnAmount(vendor))"></td>
                            </tr>
                            <tr class="border-t border-gray-200">
                                <td colspan="3" class="px-3 py-2 text-right text-xs font-semibold text-gray-700">Total Vendor Cost</td>
                                <td class="px-3 py-2 text-xs font-bold text-gray-900" x-text="'Rp ' + formatThousands(vendorFinalTotal(vendor))"></td>
                            </tr>
                            <tr class="border-t border-gray-200" :class="vendorRevenue(vendor) >= 0 ? 'bg-emerald-50/70' : 'bg-red-50/70'">
                                <td colspan="3" class="px-3 py-2.5 text-right text-xs font-bold" :class="vendorRevenue(vendor) >= 0 ? 'text-emerald-900' : 'text-red-900'">
                                    Estimated Revenue (Profit)
                                </td>
                                <td class="px-3 py-2.5 font-bold text-sm" :class="vendorRevenue(vendor) >= 0 ? 'text-emerald-700' : 'text-red-600'">
                                    <span x-text="'Rp ' + formatThousands(vendorRevenue(vendor))"></span>
                                    <span class="text-[11px] font-normal block" x-show="partCatalogTotalExt > 0" x-text="'(' + vendorRevenueMargin(vendor) + '% margin)'"></span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </template>

        <div x-show="vendors.length === 0" class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-xs text-gray-400">
            No vendors yet. Click "Add Vendor" to add one and start filling in prices.
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5" x-show="itemsReady" x-cloak>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">Grand Total (Part Catalog)</p>
            <p class="text-sm font-semibold text-gray-900">Rp <span x-text="formatThousands(partCatalogTotalExt)"></span></p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">Vendor Cost</p>
            <div class="text-sm font-semibold text-gray-900">
                <template x-if="selectedVendorObj">
                    <span>
                        Rp <span x-text="formatThousands(selectedVendorGrandTotal)"></span>
                        <span class="text-xs font-normal text-indigo-600 block text-[11px]" x-text="'(Winning: ' + (vendorNameById(selectedVendorObj.vendor_id) || 'Vendor') + ')'"></span>
                    </span>
                </template>
                <template x-if="!selectedVendorObj && bestRevenueVendor">
                    <span>
                        Rp <span x-text="formatThousands(vendorFinalTotal(bestRevenueVendor))"></span>
                        <span class="text-xs font-normal text-emerald-700 block text-[11px]" x-text="'(Lowest / Best: ' + (vendorNameById(bestRevenueVendor.vendor_id) || 'Vendor') + ')'"></span>
                    </span>
                </template>
                <template x-if="!selectedVendorObj && !bestRevenueVendor">
                    <span class="text-gray-400 font-normal">Enter prices above</span>
                </template>
            </div>
        </div>
        <div class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3">
            <p class="text-xs text-gray-500 mb-0.5">Grand Total (Revenue)</p>
            <div class="text-sm font-semibold" :class="revenueExtPrice < 0 ? 'text-red-600' : 'text-emerald-600'">
                <template x-if="selectedVendorObj">
                    <span>
                        Rp <span x-text="formatThousands(revenueExtPrice)"></span>
                        <span class="text-xs font-normal text-indigo-600 block text-[11px]">(Winning Vendor)</span>
                    </span>
                </template>
                <template x-if="!selectedVendorObj && bestRevenueVendor">
                    <span>
                        Rp <span x-text="formatThousands(revenueExtPrice)"></span>
                        <span class="text-xs font-normal text-emerald-700 block text-[11px]" x-text="'(Best: ' + (vendorNameById(bestRevenueVendor.vendor_id) || 'Vendor') + ')'"></span>
                    </span>
                </template>
                <template x-if="!selectedVendorObj && !bestRevenueVendor">
                    <span class="text-gray-400 font-normal">Enter prices above</span>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- STEP 3: WUR Cost Estimasi — one row per item, quantity synced automatically --}}
<div class="mt-8">
    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-indigo-600 text-white text-[11px] font-bold mr-1.5 align-middle">3</span>
                WUR Cost Estimasi
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">One row per RRP item — Quantity is synced automatically from Step 1. Job Description starts from the item above but you can retype it, and price is filled in here.</p>
        </div>
    </div>

    <template x-if="items.length === 0">
        <div class="rounded-xl border border-dashed border-gray-200 px-4 py-8 text-center text-xs text-gray-400">
            Add RRP items in Step 1 first.
        </div>
    </template>

    <div class="overflow-x-auto rounded-lg border border-gray-200" x-show="items.length > 0" x-cloak>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium text-gray-600">Job Description</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-32">Quantity <span class="font-normal text-gray-400">(auto)</span></th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-36">U/Price</th>
                    <th class="px-4 py-2 text-left font-medium text-gray-600 w-40">Total</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in items" :key="'wur-' + item.uid">
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-3">
                            <input type="text" :name="'costs[' + index + '][job_description]'"
                                   x-model="item.wur_job_description" placeholder="Job description"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </td>
                        <td class="px-4 py-3 text-gray-500" x-text="(item.qty || 0) + ' ' + (item.uom || '')"></td>
                        <td class="px-4 py-3">
                            <input type="text" inputmode="numeric"
                                   :value="formatThousands(item.wur_u_price)"
                                   @input="item.wur_u_price = parseThousands($event.target.value)"
                                   placeholder="0"
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <input type="hidden" :name="'costs[' + index + '][quantity]'" :value="item.qty">
                            <input type="hidden" :name="'costs[' + index + '][u_price]'" :value="item.wur_u_price">
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-700" x-text="'Rp ' + formatThousands(itemWurTotal(item))"></td>
                    </tr>
                </template>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr class="border-t border-gray-200">
                    <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">WUR Grand Total</td>
                    <td class="px-4 py-3 font-bold text-gray-900" x-text="'Rp ' + formatThousands(wurGrandTotal)"></td>
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
