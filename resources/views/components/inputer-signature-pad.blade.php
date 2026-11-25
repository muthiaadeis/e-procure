{{-- resources/views/components/inputer-signature-pad.blade.php --}}
@props([
    'name' => 'signature',
    'id' => 'inputer-signature-input',
    'title' => 'Prepared By — Tanda Tangan Digital',
    'required' => true,
])

<div x-data="{
    signMode: 'upload',
    uploadedSignature: '',
    rememberSignature: true,
    signPad: null,
    canvasId: 'inputer-sig-canvas-' + Math.random().toString(36).substring(2, 9),

    init() {
        const saved = localStorage.getItem('eprocure_saved_signature');
        if (saved) {
            this.uploadedSignature = saved;
            this.syncSignature();
        }
    },

    syncSignature() {
        let sigData = '';
        if (this.signMode === 'upload') {
            sigData = this.uploadedSignature || '';
        } else {
            if (this.signPad && !this.signPad.isEmpty()) {
                const canvas = document.getElementById(this.canvasId);
                if (canvas) {
                    sigData = this.cropSignature(canvas);
                }
            }
        }
        const input = document.getElementById('{{ $id }}');
        if (input) input.value = sigData;

        if (this.rememberSignature && sigData) {
            try {
                localStorage.setItem('eprocure_saved_signature', sigData);
            } catch (e) {}
        }
    },

    openDrawTab() {
        this.signMode = 'draw';
        this.$nextTick(() => {
            this.initSignPad();
        });
    },

    initSignPad() {
        setTimeout(() => {
            const canvas = document.getElementById(this.canvasId);
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            const width = rect.width || canvas.offsetWidth || 380;
            const height = rect.height || canvas.offsetHeight || 180;
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);

            if (this.signPad) {
                this.signPad.off();
                this.signPad = null;
            }
            if (window.SignaturePad) {
                this.signPad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(17, 24, 39)',
                    minWidth: 1.2,
                    maxWidth: 3.2,
                });
                this.signPad.addEventListener('endStroke', () => {
                    this.syncSignature();
                });
            }
        }, 60);
    },

    clearPad() {
        if (this.signPad) {
            this.signPad.clear();
            this.syncSignature();
        }
    },

    clearUploaded() {
        this.uploadedSignature = '';
        try {
            localStorage.removeItem('eprocure_saved_signature');
        } catch(e) {}
        this.syncSignature();
    },

    cropSignature(sourceCanvas) {
        const w = sourceCanvas.width;
        const h = sourceCanvas.height;
        const ctx = sourceCanvas.getContext('2d', { willReadFrequently: true });
        const imgData = ctx.getImageData(0, 0, w, h);
        const d = imgData.data;

        let minX = w, minY = h, maxX = -1, maxY = -1;
        for (let y = 0; y < h; y++) {
            for (let x = 0; x < w; x++) {
                const i = (y * w + x) * 4;
                const alpha = d[i + 3];
                if (alpha > 30) {
                    const lum = (d[i] * 299 + d[i + 1] * 587 + d[i + 2] * 114) / 1000;
                    if (lum < 235) {
                        if (x < minX) minX = x;
                        if (x > maxX) maxX = x;
                        if (y < minY) minY = y;
                        if (y > maxY) maxY = y;
                    }
                }
            }
        }

        if (maxX < minX || maxY < minY) {
            return sourceCanvas.toDataURL('image/png');
        }

        const pad = Math.round(Math.max(maxX - minX, maxY - minY) * 0.05) + 6;
        const cropX = Math.max(0, minX - pad);
        const cropY = Math.max(0, minY - pad);
        const cropW = Math.min(w - cropX, (maxX - minX) + pad * 2);
        const cropH = Math.min(h - cropY, (maxY - minY) + pad * 2);

        const maxDim = 600;
        let targetW = cropW;
        let targetH = cropH;
        if (targetW > maxDim || targetH > maxDim) {
            const ratio = Math.min(maxDim / targetW, maxDim / targetH);
            targetW = Math.round(targetW * ratio);
            targetH = Math.round(targetH * ratio);
        }

        const outCanvas = document.createElement('canvas');
        outCanvas.width = targetW;
        outCanvas.height = targetH;
        const outCtx = outCanvas.getContext('2d');
        outCtx.drawImage(sourceCanvas, cropX, cropY, cropW, cropH, 0, 0, targetW, targetH);

        return outCanvas.toDataURL('image/png');
    },

    handleFileUpload(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;
        if (!file.type.startsWith('image/')) {
            alert('Pilih file gambar tanda tangan (PNG/JPG).');
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
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    const lum = (d[i] * 299 + d[i + 1] * 587 + d[i + 2] * 114) / 1000;
                    if (lum > 220) {
                        d[i + 3] = Math.round(d[i + 3] * (1 - Math.min(1, Math.max(0, (lum - 220) / 25))));
                    }
                }
                tempCtx.putImageData(imgData, 0, 0);

                this.uploadedSignature = this.cropSignature(tempCanvas);
                this.syncSignature();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}" class="mt-6 pt-5 border-t border-gray-100">
    <div class="flex items-center justify-between mb-1">
        <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
            <span>{{ $title }}</span>
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </h3>
        <span class="text-xs text-indigo-600 bg-indigo-50 font-medium px-2 py-0.5 rounded-full">
            Pembuat Dokumen
        </span>
    </div>
    <p class="text-xs text-gray-400 mb-3.5">
        Upload foto tanda tangan atau tanda tangan langsung di bawah. Tanda tangan ini otomatis tersimpan sebagai <strong>Tahap 1 (Prepared By)</strong> pada dokumen.
    </p>

    {{-- Tab Switcher --}}
    <div class="flex items-center gap-1.5 mb-3 p-1 bg-gray-100 rounded-xl max-w-md">
        <button type="button"
                @click="signMode = 'upload'; syncSignature()"
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
        <input type="file" x-ref="sigFileInput" class="hidden" accept="image/png,image/jpeg,image/jpg,image/webp" @change="handleFileUpload($event)">

        <template x-if="uploadedSignature">
            <div class="relative bg-gray-50/70 border-2 border-dashed border-indigo-200 rounded-xl p-4 flex flex-col items-center justify-center min-h-[190px]">
                <div class="max-h-44 flex items-center justify-center bg-white p-3 rounded-lg shadow-xs border border-gray-100">
                    <img :src="uploadedSignature" alt="Signature Preview" style="max-height: 120px; max-width: 100%; object-fit: contain;" class="max-h-36 max-w-full object-contain filter drop-shadow-xs">
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <button type="button" @click="$refs.sigFileInput.click()" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-xs hover:bg-gray-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Ganti Gambar
                    </button>
                    <button type="button" @click="clearUploaded()" class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-xs hover:bg-red-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus / Reset
                    </button>
                </div>
            </div>
        </template>

        <template x-if="!uploadedSignature">
            <div @click="$refs.sigFileInput.click()" class="cursor-pointer border-2 border-dashed border-gray-300 hover:border-indigo-500 hover:bg-indigo-50/20 rounded-xl p-8 flex flex-col items-center justify-center text-center transition group">
                <div class="w-12 h-12 rounded-full bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 flex items-center justify-center mb-2.5 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 group-hover:text-indigo-600">Klik untuk upload foto / gambar tanda tangan</span>
                <span class="text-xs text-gray-400 mt-1">Mendukung format PNG, JPG, JPEG, WEBP (Latar kertas putih otomatis dibuat transparan)</span>
            </div>
        </template>
    </div>

    {{-- Mode 2: Tulis / Gambar Manual --}}
    <div x-show="signMode === 'draw'">
        <div class="relative bg-gray-50/50 rounded-xl border border-gray-200 p-2">
            <canvas :id="canvasId" style="height: 180px; width: 100%; display: block;" class="w-full bg-white rounded-lg touch-none shadow-inner cursor-crosshair"></canvas>
            <div class="absolute bottom-5 left-5 right-5 border-b border-gray-300 pointer-events-none flex justify-between items-end pb-1">
                <span class="text-[11px] text-gray-400 font-normal">Tanda tangan di atas garis ini</span>
                <span class="text-[11px] text-gray-400 font-normal">✕</span>
            </div>
        </div>
        <div class="mt-2.5 flex items-center justify-between">
            <p class="text-[11px] text-gray-400">Gunakan mouse, trackpad, atau layar sentuh</p>
            <button type="button" @click="clearPad()"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 px-3 py-1.5 rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Bersihkan Canvas
            </button>
        </div>
    </div>

    {{-- Ingat TTD Checkbox --}}
    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
        <input type="checkbox" id="{{ $id }}-remember" x-model="rememberSignature" @change="syncSignature()" class="rounded border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500">
        <label for="{{ $id }}-remember" class="text-xs text-gray-600 cursor-pointer select-none">
            Ingat tanda tangan ini di browser untuk pengisian dokumen berikutnya
        </label>
    </div>

    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="">
</div>

