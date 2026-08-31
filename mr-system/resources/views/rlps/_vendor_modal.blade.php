<div x-show="showVendorModal" x-cloak class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50">
    <div x-show="showVendorModal"
         class="fixed inset-0 bg-gray-500 opacity-75 transform transition-all"
         @click="showVendorModal = false"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-75"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-75"
         x-transition:leave-end="opacity-0"></div>

    <div x-show="showVendorModal"
         class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full sm:mx-auto relative"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95">

        <div class="px-6 py-5">
            <h3 class="text-base font-semibold text-gray-800 mb-1">Add Quotation</h3>
            <p class="text-xs text-gray-400 mb-4">Description, PN, Qty &amp; UOM follow the RRP Items above &mdash; just pick a vendor and fill in the price.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor <span class="text-red-500">*</span></label>
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-left hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition">
                            <span :class="newVendor.vendor_id ? 'text-gray-700' : 'text-gray-400'"
                                  x-text="newVendor.vendor_id ? vendorNameById(newVendor.vendor_id) : 'Select vendor'"></span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute z-20 mt-1.5 w-full max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg py-1">
                            <template x-if="vendorOptions.length === 0">
                                <p class="px-3 py-2 text-xs text-gray-400">No vendors yet. Add one in Master Data &rarr; Vendors.</p>
                            </template>
                            <template x-for="opt in vendorOptions" :key="opt.id">
                                <button type="button" @click="newVendor.vendor_id = opt.id; open = false"
                                        class="w-full flex items-center justify-between gap-2 text-left px-3 py-2 text-sm transition"
                                        :class="String(newVendor.vendor_id) === String(opt.id) ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-700 hover:bg-gray-50'">
                                    <span class="truncate" x-text="opt.vendor_name"></span>
                                    <svg x-show="String(newVendor.vendor_id) === String(opt.id)" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Brand <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newVendor.brand" placeholder="e.g. Kitz" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Estimate <span class="text-red-500">*</span></label>
                    <input type="text" x-model="newVendor.delivery_estimate" placeholder="e.g. 7 days" class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <p class="mt-1.5 text-xs text-gray-400">How long the vendor estimates delivery will take.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Price per Item <span class="text-red-500">*</span></label>
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="border border-gray-200 rounded-lg p-3">
                                <p class="text-xs font-medium text-gray-700 truncate" x-text="item.description || '(No description)'"></p>
                                <p class="text-[11px] text-gray-400 mb-2">
                                    <span x-text="'PN: ' + (item.pn || '-')"></span>
                                    &middot;
                                    <span x-text="'Qty: ' + (item.qty || 0) + ' ' + (item.uom || '')"></span>
                                </p>
                                <input type="text" inputmode="numeric" placeholder="Price"
                                       :value="formatThousands(newVendor.prices[idx])"
                                       @input="newVendor.prices[idx] = parseThousands($event.target.value); $event.target.value = formatThousands(newVendor.prices[idx])"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-left">
                            </div>
                        </template>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">Fill in a price for at least one item. Leave the rest blank if this vendor isn't quoting them.</p>
                </div>

                <p x-show="vendorModalError" x-cloak x-text="vendorModalError" class="text-xs text-red-600"></p>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
            <button type="button" @click="showVendorModal = false"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="button" @click="addVendor()"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-700 transition shadow-sm">
                Add
            </button>
        </div>
    </div>
</div>
