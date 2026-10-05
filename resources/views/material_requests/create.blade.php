<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Tambah Material Request (MR)</h1>
                <p class="text-sm text-gray-500 mt-1">Buat pengajuan material atau inventaris baru.</p>
            </div>
            <nav class="text-sm text-gray-400 mt-1.5">
                <a href="{{ route('material-requests.index') }}" class="hover:text-indigo-600 transition">MR</a>
                <span class="mx-1.5">/</span>
                <span class="text-indigo-600 font-medium">Tambah Baru</span>
            </nav>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
            items: {{ Illuminate\Support\Js::from(
                old('items', [['description' => '', 'quantity' => '', 'unit' => '', 'remarks' => '']])
            ) }},
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
            },
            // ---------- Prepared By signature (upload gambar ATAU tulis manual) ----------
            signPad: null,
            signMode: 'upload',
            uploadedSignature: '',
            rememberSignature: true,
            initSignature() {
                // Ambil TTD yang pernah diingat di browser (dipakai bareng semua modul)
                try {
                    const saved = localStorage.getItem('eprocure_saved_signature');
                    if (saved) this.uploadedSignature = saved;
                } catch (e) {}
            },
            openDrawTab() {
                this.signMode = 'draw';
                this.$nextTick(() => this.setupCanvas());
            },
            setupCanvas(tries = 0) {
                const canvas = document.getElementById('mr-prepared-signature-canvas');
                if (!canvas) return;
                // canvas baru kelihatan setelah tab 'draw' tampil, jadi tunggu ukurannya ada
                if ((!canvas.offsetWidth || !canvas.offsetHeight) && tries < 30) {
                    requestAnimationFrame(() => this.setupCanvas(tries + 1));
                    return;
                }
                if (this.signPad) return; // sudah pernah disiapkan
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                if (window.SignaturePad) {
                    this.signPad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255,255,255)',
                        minWidth: 1.8,
                        maxWidth: 3.8,
                        penColor: 'rgb(15, 23, 42)'
                    });
                }
            },
            clearSignPad() {
                if (this.signPad) this.signPad.clear();
            },
            handleSigFile(event) {
                const file = event.target.files && event.target.files[0];
                if (!file) return;
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file (PNG, JPG, JPEG, WEBP).');
                    return;
                }
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const tempCanvas = document.createElement('canvas');
                        tempCanvas.width = img.naturalWidth || img.width;
                        tempCanvas.height = img.naturalHeight || img.height;
                        const tempCtx = tempCanvas.getContext('2d', { willReadFrequently: true });
                        tempCtx.drawImage(img, 0, 0);

                        const imgData = tempCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
                        const data = imgData.data;
                        const w = tempCanvas.width;
                        const h = tempCanvas.height;

                        let minX = w, minY = h, maxX = 0, maxY = 0;
                        let foundInk = false;

                        for (let y = 0; y < h; y++) {
                            for (let x = 0; x < w; x++) {
                                const i = (y * w + x) * 4;
                                const a = data[i + 3];
                                if (a > 30) {
                                    const lum = (data[i] * 299 + data[i + 1] * 587 + data[i + 2] * 114) / 1000;
                                    if (lum < 235) {
                                        foundInk = true;
                                        if (x < minX) minX = x;
                                        if (x > maxX) maxX = x;
                                        if (y < minY) minY = y;
                                        if (y > maxY) maxY = y;
                                    }
                                }
                            }
                        }

                        const pad = 12;
                        let cropX = 0, cropY = 0, cropW = w, cropH = h;
                        if (foundInk && maxX >= minX && maxY >= minY) {
                            cropX = Math.max(0, minX - pad);
                            cropY = Math.max(0, minY - pad);
                            cropW = Math.min(w - cropX, (maxX - minX) + pad * 2);
                            cropH = Math.min(h - cropY, (maxY - minY) + pad * 2);
                        }

                        const maxDim = 800;
                        let targetW = cropW;
                        let targetH = cropH;
                        if (targetW > maxDim || targetH > maxDim) {
                            const ratio = Math.min(maxDim / targetW, maxDim / targetH);
                            targetW = Math.round(targetW * ratio);
                            targetH = Math.round(targetH * ratio);
                        }

                        const finalCanvas = document.createElement('canvas');
                        finalCanvas.width = targetW;
                        finalCanvas.height = targetH;
                        const finalCtx = finalCanvas.getContext('2d', { willReadFrequently: true });
                        finalCtx.drawImage(tempCanvas, cropX, cropY, cropW, cropH, 0, 0, targetW, targetH);

                        // Background putih dibikin transparan biar TTD-nya bersih
                        const fData = finalCtx.getImageData(0, 0, targetW, targetH);
                        const fd = fData.data;
                        for (let i = 0; i < fd.length; i += 4) {
                            const lum = (fd[i] * 299 + fd[i + 1] * 587 + fd[i + 2] * 114) / 1000;
                            if (lum > 220) {
                                const fade = Math.min(1, Math.max(0, (lum - 220) / 25));
                                fd[i + 3] = Math.round(fd[i + 3] * (1 - fade));
                            }
                        }
                        finalCtx.putImageData(fData, 0, 0);

                        this.uploadedSignature = finalCanvas.toDataURL('image/png');
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            submitForm(event) {
                // Wajib ttd (Prepared By) dulu sebelum MR bisa disimpan, biar gak
                // ada MR yang kesimpan tapi belum ditandatangani (gampang kelupaan
                // kalau ttd-nya dipisah jadi langkah setelah simpan).
                let signatureData = '';
                if (this.signMode === 'upload') {
                    if (!this.uploadedSignature) {
                        event.preventDefault();
                        alert('Please choose or upload a signature image first before saving.');
                        return;
                    }
                    signatureData = this.uploadedSignature;
                } else {
                    if (!this.signPad || this.signPad.isEmpty()) {
                        event.preventDefault();
                        alert('Please sign as Prepared By first before saving.');
                        return;
                    }
                    signatureData = this.signPad.toDataURL('image/png');
                }

                if (this.rememberSignature && signatureData) {
                    try {
                        localStorage.setItem('eprocure_saved_signature', signatureData);
                    } catch (e) {}
                }
                document.getElementById('mr-create-signature-input').value = signatureData;
            }
        }" x-init="initSignature()">
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

                <form action="{{ route('material-requests.store') }}" method="POST" @submit="submitForm($event)"
                        data-draft="material-requests"
                        data-draft-url="{{ url('drafts') }}"
                        data-draft-state="items"
                        data-draft-skip="{{ count(session()->getOldInput()) ? 1 : 0 }}"
                        data-draft-resume="{{ request()->boolean('resume') ? 1 : 0 }}">
                    @csrf

                    <div class="p-6 sm:p-8 space-y-8">
                        {{-- Request details --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-4">
                                Detail Pengajuan
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
                                    <p class="mt-1.5 text-xs text-gray-400">Masukkan nomor Material Request.</p>
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
                                    <h3 class="text-sm font-semibold text-gray-800">Daftar Item Material</h3>
                                    <p class="text-xs text-gray-400 mt-0.5" x-text="items.length + ' item ditambahkan'"></p>
                                </div>
                                <button type="button" @click="addItem()"
                                        class="inline-flex items-center gap-1.5 text-indigo-600 border border-indigo-200 hover:bg-indigo-50 hover:border-indigo-300 px-3.5 py-2 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Item
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
                                                        <div class="relative"
                                                             x-data="{
                                                                open: false, menuTop: 0, menuLeft: 0, menuWidth: 0,
                                                                updatePosition() {
                                                                    const rect = $refs.unitTrigger.getBoundingClientRect();
                                                                    const menuHeight = 230; // perkiraan max-h-56 + padding
                                                                    const spaceBelow = window.innerHeight - rect.bottom;
                                                                    this.menuTop = (spaceBelow < menuHeight && rect.top > menuHeight)
                                                                        ? rect.top - menuHeight - 6
                                                                        : rect.bottom + 6;
                                                                    this.menuLeft = rect.left;
                                                                    this.menuWidth = rect.width;
                                                                }
                                                             }"
                                                             @click.outside="open = false"
                                                             @scroll.window.capture="if (open) updatePosition()"
                                                             @resize.window="if (open) updatePosition()">
                                                            <button type="button" x-ref="unitTrigger"
                                                                    @click="open = !open; if (open) updatePosition()"
                                                                    class="w-full flex items-center justify-between gap-1 rounded-md border border-gray-200 bg-white px-2 py-1.5 text-sm text-left hover:border-gray-300 focus:outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-200 transition">
                                                                <span class="truncate" :class="item.unit ? 'text-gray-700' : 'text-gray-400'"
                                                                      x-text="item.unit || 'Select'"></span>
                                                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                </svg>
                                                            </button>

                                                            {{-- position: fixed + reposisi mengikuti scroll, bukan langsung ketutup, biar gak kepotong overflow-x-auto punya wrapper tabel --}}
                                                            <div x-show="open" x-cloak
                                                                 x-transition:enter="transition ease-out duration-100"
                                                                 x-transition:enter-start="opacity-0 scale-95"
                                                                 x-transition:enter-end="opacity-100 scale-100"
                                                                 :style="'top:' + menuTop + 'px; left:' + menuLeft + 'px; min-width:' + menuWidth + 'px;'"
                                                                 style="display:none;"
                                                                 class="fixed z-50 max-h-56 overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg py-1">
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

                        {{-- Prepared By signature — required right here, before the MR can be saved,
                             so it can't end up saved without the preparer's signature. --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 mb-1">
                                Prepared By — Digital Signature <span class="text-red-500">*</span>
                            </h3>
                            <p class="text-xs text-gray-400 mb-3">Upload foto tanda tangan atau tanda tangan langsung di bawah. Wajib sebelum menyimpan.</p>

                            {{-- Tab Switcher --}}
                            <div class="flex items-center gap-1.5 mb-3 p-1 bg-gray-100 rounded-xl max-w-md">
                                <button type="button"
                                        @click="signMode = 'upload'"
                                        :class="signMode === 'upload' ? 'bg-white text-indigo-600 font-semibold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                                        class="flex-1 py-1.5 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Upload Gambar TTD
                                </button>
                                <button type="button"
                                        @click="openDrawTab()"
                                        :class="signMode === 'draw' ? 'bg-white text-indigo-600 font-semibold shadow-xs' : 'text-gray-600 hover:text-gray-900 font-medium'"
                                        class="flex-1 py-1.5 px-3 rounded-lg text-xs flex items-center justify-center gap-1.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    Tulis / Gambar Manual
                                </button>
                            </div>

                            {{-- Mode 1: Upload Gambar --}}
                            <div x-show="signMode === 'upload'" class="space-y-3">
                                <input type="file" x-ref="mrCreateSigFileInput" class="hidden" accept="image/png,image/jpeg,image/jpg,image/webp" @change="handleSigFile($event)">

                                <template x-if="uploadedSignature">
                                    <div class="relative bg-gray-50/70 border-2 border-dashed border-indigo-200 rounded-xl p-4 flex flex-col items-center justify-center min-h-[200px]">
                                        <div class="max-h-48 flex items-center justify-center bg-white p-3 rounded-lg shadow-xs border border-gray-100">
                                            <img :src="uploadedSignature" alt="Signature Preview" class="max-h-40 max-w-full object-contain">
                                        </div>
                                        <div class="flex items-center gap-2 mt-3">
                                            <button type="button" @click="$refs.mrCreateSigFileInput.click()" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-xs hover:bg-gray-50 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                                Ganti Gambar
                                            </button>
                                            <button type="button" @click="uploadedSignature = ''" class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-xs hover:bg-red-50 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!uploadedSignature">
                                    <div @click="$refs.mrCreateSigFileInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 hover:border-indigo-500 hover:bg-indigo-50/20 rounded-xl p-8 flex flex-col items-center justify-center text-center transition group">
                                        <div class="w-12 h-12 rounded-full bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 flex items-center justify-center mb-2.5 transition">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-600">Klik untuk upload foto / gambar TTD</span>
                                        <span class="text-xs text-gray-400 mt-1">Mendukung format PNG, JPG, JPEG, WEBP (Otomatis disesuaikan & diperjelas)</span>
                                    </div>
                                </template>
                            </div>

                            {{-- Mode 2: Tulis / Gambar Manual --}}
                            <div x-show="signMode === 'draw'">
                                <div class="relative bg-gray-50/50 rounded-xl border border-gray-200 p-2">
                                    <canvas id="mr-prepared-signature-canvas" class="w-full h-56 sm:h-64 bg-white rounded-lg touch-none shadow-inner cursor-crosshair"></canvas>
                                    <div class="absolute bottom-6 left-6 right-6 border-b border-gray-300 pointer-events-none flex justify-between items-end pb-1">
                                        <span class="text-[11px] text-gray-400 font-normal">Tanda tangan di atas garis ini</span>
                                        <span class="text-[11px] text-gray-400 font-normal">✕</span>
                                    </div>
                                </div>
                                <div class="mt-2.5">
                                    <button type="button" @click="clearSignPad()"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 px-3.5 py-2 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus Tanda Tangan
                                    </button>
                                </div>
                            </div>

                            {{-- Ingat TTD Checkbox --}}
                            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                                <input type="checkbox" id="mr-create-remember-sig" x-model="rememberSignature" class="rounded border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500">
                                <label for="mr-create-remember-sig" class="text-xs text-gray-600 cursor-pointer select-none">
                                    Ingat tanda tangan ini di browser untuk dokumen berikutnya
                                </label>
                            </div>

                            <input type="hidden" name="signature" id="mr-create-signature-input">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-5 px-6 sm:px-8 py-5 bg-gray-50 border-t border-gray-100">
                        <a href="{{ route('material-requests.index') }}"
                           onclick="event.preventDefault(); window.location.replace('{{ route('material-requests.index') }}')"
                           class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition">
                            Batal
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                            Simpan Dokumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</x-app-layout>
