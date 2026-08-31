@csrf

<div class="p-6 sm:p-8 space-y-8">
    <div>
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Job Code Details</h3>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Job Code</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                        </svg>
                    </span>
                    <input id="job_code" name="job_code" type="text" required autofocus
                           value="{{ old('job_code', $jobCode->job_code ?? '') }}"
                           placeholder=""
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10 uppercase">
                </div>
                <x-input-error :messages="$errors->get('job_code')" class="mt-2" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          placeholder=""
                          class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('description', $jobCode->description ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Price</label>
                    <div class="relative"
                         x-data="{
                            raw: '{{ old('price', $jobCode->price ?? '') }}',
                            formatted: '',
                            formatNumber(val) {
                                val = val.toString().replace(/[^0-9]/g, '');
                                if (!val) return '';
                                return new Intl.NumberFormat('id-ID').format(val);
                            },
                            onInput(e) {
                                let digits = e.target.value.replace(/[^0-9]/g, '');
                                this.raw = digits;
                                this.formatted = this.formatNumber(digits);
                                e.target.value = this.formatted;
                            }
                         }"
                         x-init="raw = String(Math.round(parseFloat(raw) || 0)); if (raw === '0') raw = ''; formatted = formatNumber(raw)">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none text-sm font-medium">
                            Rp
                        </span>
                        <input type="text" inputmode="numeric" x-model="formatted" @input="onInput($event)"
                               placeholder=""
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                        <input type="hidden" name="price" x-model="raw">
                    </div>
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Part Number</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.71 6.71 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <input id="part_number" name="part_number" type="text"
                               value="{{ old('part_number', $jobCode->part_number ?? '') }}"
                               placeholder=""
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-10">
                    </div>
                    <x-input-error :messages="$errors->get('part_number')" class="mt-2" />
                </div>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end gap-5 px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
    <a href="{{ route('job-codes.index') }}"
       class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
        Cancel
    </a>
    @isset($jobCode)
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

@isset($jobCode)
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
