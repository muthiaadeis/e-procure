@csrf

<div class="p-6 sm:p-8 space-y-8">
    <div>
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Vendor Details</h3>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor Code</label>
                @isset($vendor)
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-semibold text-xs">
                            {{ $vendor->vendor_code }}
                        </span>
                        <span class="text-xs text-gray-400">auto, can't be changed</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-400 italic">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generated automatically once saved
                    </div>
                @endisset
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor Name</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                        </svg>
                    </span>
                    <input id="vendor_name" name="vendor_name" type="text" required autofocus
                           value="{{ old('vendor_name', $vendor->vendor_name ?? '') }}"
                           placeholder=""
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                </div>
                <x-input-error :messages="$errors->get('vendor_name')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Vendor Address</label>
                <textarea id="vendor_address" name="vendor_address" rows="3"
                          placeholder=""
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('vendor_address', $vendor->vendor_address ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('vendor_address')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input id="email" name="email" type="email"
                               value="{{ old('email', $vendor->email ?? '') }}"
                               placeholder=""
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </span>
                        <input id="phone" name="phone" type="text"
                               value="{{ old('phone', $vendor->phone ?? '') }}"
                               placeholder=""
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                    </div>
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-5 px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
    <a href="{{ route('vendors.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
        Cancel
    </a>
    @isset($vendor)
        <button type="submit" data-update-submit disabled
                class="inline-flex items-center gap-2 bg-gray-200 text-gray-400 cursor-not-allowed px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
            Update
        </button>
    @else
        <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
            Save
        </button>
    @endisset
</div>

@isset($vendor)
    <script>
        (function () {
            const form = document.currentScript.closest('form');
            const submitBtn = form.querySelector('[data-update-submit]');
            if (!form || !submitBtn) return;

            const serialize = () => new URLSearchParams(new FormData(form)).toString();
            const initial = serialize();

            const applyState = (dirty) => {
                submitBtn.disabled = !dirty;
                submitBtn.classList.toggle('bg-gray-200', !dirty);
                submitBtn.classList.toggle('text-gray-400', !dirty);
                submitBtn.classList.toggle('cursor-not-allowed', !dirty);
                submitBtn.classList.toggle('bg-indigo-600', dirty);
                submitBtn.classList.toggle('hover:bg-indigo-700', dirty);
                submitBtn.classList.toggle('active:bg-indigo-800', dirty);
                submitBtn.classList.toggle('text-white', dirty);
                submitBtn.classList.toggle('cursor-pointer', dirty);
            };

            const check = () => applyState(serialize() !== initial);

            form.addEventListener('input', check);
            form.addEventListener('change', check);
        })();
    </script>
@endisset
