{{-- resources/views/admin/approval-slots/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-bold text-2xl text-gray-800 leading-tight">Template Alur Persetujuan (Default Approval)</h1>
            <p class="text-sm text-gray-500 mt-1">
                Tentukan alur persetujuan standar untuk masing-masing dokumen. Ketika staf/inputer membuat dokumen baru, tahapan tanda tangan berikut akan otomatis terpasang sehingga proses persetujuan langsung berjalan.
            </p>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6"
         x-data="{
             activeTab: 'mr',
             templates: {{ Js::from($templates) }},
             users: {{ Js::from($users) }},
             defaultLabels: {
                 mr: ['Reviewer', 'Approver 1', 'Finance Control'],
                 rlp: ['Reviewer', 'Acknowledge By', 'Approved By'],
                 po: ['Reviewer', 'Approver 1', 'Management'],
                 pr: ['Reviewer', 'Approver 1', 'Management']
             },

             addStep(type) {
                 if (!this.templates[type]) {
                     this.templates[type] = [];
                 }
                 const stepNum = this.templates[type].length + 2;
                 const presets = this.defaultLabels[type] || ['Approver'];
                 const defaultLabel = presets[stepNum - 2] || ('Approver ' + (stepNum - 1));
                 const defaultUserId = this.users.length > 0 ? this.users[0].id : '';

                 this.templates[type].push({
                     step: stepNum,
                     label: defaultLabel,
                     user_id: defaultUserId
                 });
             },

             removeStep(type, index) {
                 this.templates[type].splice(index, 1);
                 // Re-index step numbers
                 this.templates[type].forEach((s, idx) => {
                     s.step = idx + 2;
                 });
             },

             saveTemplates(e) {
                 const types = ['mr', 'rlp', 'po', 'pr'];
                 const typeNames = { mr: 'Material Request (MR)', rlp: 'RRP', po: 'Purchase Order (PO)', pr: 'Purchase Request (PR)' };
                 for (let i = 0; i < types.length; i++) {
                     const t = types[i];
                     const steps = this.templates[t] || [];
                     const userIds = steps.map(s => String(s.user_id || '').trim()).filter(Boolean);
                     const duplicates = userIds.filter((item, index) => userIds.indexOf(item) !== index);
                     if (duplicates.length > 0) {
                         e.preventDefault();
                         this.activeTab = t;
                         alert('Satu orang tidak boleh menandatangani dua tahap pada template dokumen ' + (typeNames[t] || t.toUpperCase()) + '. Pastikan setiap tahap memiliki approver yang berbeda.');
                         return false;
                     }
                 }
             }
         }">

        {{-- Success alert --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                 class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
            </div>
        @endif

        {{-- Error alert --}}
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm shadow-xs">
                <p class="font-semibold">Pengaturan gagal disimpan:</p>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Info Banner --}}
        <div class="p-4 bg-indigo-50/70 border border-indigo-100 text-indigo-900 rounded-xl text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
            <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="space-y-0.5">
                <p class="font-semibold text-indigo-950">Cara Kerja Template Alur Persetujuan:</p>
                <p class="text-indigo-800/90 text-xs">
                    Tahap 1 selalu diisi otomatis oleh <strong>Pembuat Dokumen (Prepared By)</strong> saat formulir disubmit.
                    Tahap 2, 3, dst. yang Anda atur di bawah akan otomatis terpasang pada dokumen baru. Anda tetap dapat menyesuaikan/mengganti approver pada dokumen tertentu secara langsung via tombol <em>Kelola Penandatangan</em>.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.approval-slots.update') }}" @submit="saveTemplates($event)" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Navigasi Tab Dokumen --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-2">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach ($documentTypes as $typeKey => $doc)
                        <button type="button"
                                @click="activeTab = '{{ $typeKey }}'"
                                :class="activeTab === '{{ $typeKey }}' ? 'bg-indigo-600 text-white shadow-xs font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 font-medium'"
                                class="flex flex-col items-center justify-center p-3 rounded-xl transition text-center cursor-pointer">
                            <span class="text-sm font-bold">{{ $doc['name'] }}</span>
                            <span class="text-[11px] mt-0.5 opacity-80"
                                  x-text="templates['{{ $typeKey }}'] && templates['{{ $typeKey }}'].length > 0 ? (templates['{{ $typeKey }}'].length + 1) + ' Tahap Alur' : 'Manual (Pending Setup)'"></span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Konten Tab Per Modul Dokumen --}}
            @foreach ($documentTypes as $typeKey => $doc)
                <div x-show="activeTab === '{{ $typeKey }}'" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-xs p-6 space-y-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
                        <div>
                            <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <span>{{ $doc['name'] }}</span>
                                <span class="text-xs font-normal px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-mono">
                                    {{ strtoupper($typeKey) }}
                                </span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-1">{{ $doc['description'] }}</p>
                        </div>
                        <button type="button"
                                @click="addStep('{{ $typeKey }}')"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span>Tambah Tahap Approval</span>
                        </button>
                    </div>

                    {{-- Timeline / Urutan Alur Persetujuan --}}
                    <div class="space-y-3">
                        {{-- Tahap 1: Prepared By (Permanen) --}}
                        <div class="flex items-center gap-3 p-3.5 bg-gray-50/80 rounded-xl border border-gray-200/70 opacity-90">
                            <span class="w-8 h-8 rounded-lg bg-gray-200 text-gray-700 font-bold text-xs flex items-center justify-center shrink-0">
                                1
                            </span>
                            <div class="flex-1 min-w-0 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 items-center">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Tahap 1</span>
                                    <span class="text-xs font-semibold text-gray-800">Prepared By (Pembuat Dokumen)</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider">Petugas Default</span>
                                    <span class="text-xs text-gray-600 italic">Otomatis user pembuat dokumen</span>
                                </div>
                            </div>
                            <span class="text-xs text-gray-400 font-medium px-2 py-1 bg-gray-100 rounded">Permanen</span>
                        </div>

                        {{-- Tahap 2, 3, dst (Dinamis dari template) --}}
                        <template x-for="(step, index) in templates['{{ $typeKey }}']" :key="index">
                            <div class="flex items-center gap-3 p-3.5 bg-white rounded-xl border border-indigo-100 shadow-2xs hover:border-indigo-300 transition">
                                <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0"
                                      x-text="index + 2">
                                </span>

                                <div class="flex-1 min-w-0 grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4">
                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">
                                            Nama Tahap / Jabatan
                                        </label>
                                        <input type="text"
                                               :name="'templates[{{ $typeKey }}][' + index + '][label]'"
                                               x-model="step.label"
                                               required
                                               placeholder="Contoh: Reviewer, Approver 1, GM, dll"
                                               class="w-full text-xs font-medium rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-3">
                                    </div>

                                    <div>
                                        <label class="block text-[10px] uppercase font-bold text-gray-400 mb-1 tracking-wider">
                                            Approver Default
                                        </label>
                                        <select :name="'templates[{{ $typeKey }}][' + index + '][user_id]'"
                                                x-model="step.user_id"
                                                required
                                                class="w-full text-xs font-medium rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-3">
                                            <template x-for="u in users" :key="u.id">
                                                <option :value="u.id" x-text="u.name + ' (' + (u.jabatan ? u.jabatan + ' • ' : '') + (u.role || 'Staff') + ')'" :selected="step.user_id == u.id"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <button type="button"
                                        @click="removeStep('{{ $typeKey }}', index)"
                                        title="Hapus Tahap Ini"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition shrink-0 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        {{-- Kosong / Belum Ada Tahap Lanjutan --}}
                        <div x-show="!templates['{{ $typeKey }}'] || templates['{{ $typeKey }}'].length === 0"
                             class="p-6 text-center rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                            <p class="text-xs text-gray-500">
                                Belum ada tahapan persetujuan lanjutan yang diatur untuk {{ $doc['name'] }}.
                            </p>
                            <p class="text-[11px] text-gray-400 mt-0.5">
                                Setiap dokumen baru akan masuk ke status <strong>Pending Setup</strong> dan harus ditentukan penandatangannya secara manual oleh Super Admin.
                            </p>
                            <button type="button"
                                    @click="addStep('{{ $typeKey }}')"
                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition cursor-pointer">
                                + Pasang Tahap Persetujuan Default Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Tombol Simpan --}}
            <div class="flex items-center justify-between gap-4 pt-2">
                <p class="text-xs text-gray-500">
                    Perubahan template akan otomatis diterapkan ke setiap dokumen yang baru dibuat setelah ini. Dokumen yang sudah berjalan sebelumnya tidak akan terpengaruh.
                </p>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition shadow-xs cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Semua Template</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
