{{-- resources/views/components/document-approval-manager.blade.php --}}
@props([
    'type', // 'mr', 'rlp', 'po', 'pr'
    'document', // The document model
    'approvals' => null, // Collection of DocumentApproval
])

@php
    $approvals = $approvals ?? $document->approvals;
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $currentUserId = auth()->id();
    // Ambil semua pengguna approver untuk dropdown Super Admin (termasuk creator jika ada)
    $assignedUserIds = $approvals->pluck('user_id')->filter()->unique();
    if (!empty($document->created_by)) {
        $assignedUserIds->push($document->created_by);
    }
    $approverUsers = \App\Models\User::where('is_admin', false)
        ->where(fn($q) => $q->where('role', 'approver')->orWhere('is_approver', true)->orWhereIn('id', $assignedUserIds))
        ->orderBy('name')
        ->get(['id', 'name', 'email', 'jabatan']);
    if ($approverUsers->isEmpty()) {
        $approverUsers = \App\Models\User::where('is_admin', false)->orderBy('name')->get(['id', 'name', 'email', 'jabatan']);
    }

    // Ambil semua label jabatan yang tersedia dari tabel approval_slot_holders
    $labelOptions = \App\Models\ApprovalSlotHolder::select('role_label')
        ->distinct()
        ->orderBy('role_label')
        ->pluck('role_label');

    $defaultTemplateSteps = \App\Models\ApprovalTemplate::where('document_type', $type)
        ->orderBy('step')
        ->with('user')
        ->get()
        ->map(fn($t) => [
            'id' => null,
            'step' => $t->step,
            'label' => $t->label,
            'user_id' => $t->user_id,
            'is_signed' => false,
            'is_rejected' => false,
            'signer_name' => $t->user?->name ?? 'Approver',
        ])->values();

    $existingSteps = $approvals->map(fn($a) => [
        'id' => $a->id,
        'step' => $a->step,
        'label' => $a->label,
        'user_id' => $a->user_id,
        'is_signed' => $a->isSigned(),
        'is_rejected' => $a->isRejected(),
        'signer_name' => $a->user?->name ?? 'Approver',
    ])->values();
@endphp

<div x-data="{
    // Modal Admin Atur Penandatangan
    adminModalOpen: false,
    defaultTemplate: {{ Js::from($defaultTemplateSteps) }},
    steps: {{ Js::from($existingSteps->isEmpty() ? [['id' => null, 'label' => 'Reviewer', 'user_id' => $approverUsers->first()?->id ?? '', 'is_signed' => false, 'is_rejected' => false, 'signer_name' => '']] : $existingSteps) }},
    applyDefaultTemplate() {
        if (!this.defaultTemplate || this.defaultTemplate.length === 0) {
            alert('Belum ada template default yang diatur untuk dokumen ini. Silakan atur terlebih dahulu di menu Administrasi -> Template Approval.');
            return;
        }
        // Pertahankan Step 1 (Prepared By)
        const step1 = this.steps.find(s => s.step === 1 || s.label === 'Prepared By');
        const newSteps = step1 ? [step1] : [];
        this.defaultTemplate.forEach((tpl, idx) => {
            newSteps.push({
                id: null,
                step: newSteps.length + 1,
                label: tpl.label,
                user_id: tpl.user_id,
                is_signed: false,
                is_rejected: false,
                signer_name: tpl.signer_name
            });
        });
        this.steps = newSteps;
    },
    addStep() {
        const nextStepNum = this.steps.length + 1;
        const defaultLabel = nextStepNum === 2 ? 'Reviewer' : (nextStepNum === 3 ? 'Approver 1' : (nextStepNum === 4 ? 'Approver 2' : 'Management'));
        this.steps.push({
            id: null,
            label: defaultLabel,
            user_id: '{{ $approverUsers->first()?->id ?? '' }}',
            is_signed: false,
            is_rejected: false,
            signer_name: ''
        });
    },
    async removeStep(index) {
        if (this.steps.length <= 1) {
            alert('Minimal harus ada 1 tahap persetujuan.');
            return;
        }
        if (this.steps[index].label === 'Prepared By') {
            alert('Tahap Pembuat Dokumen (Prepared By) tidak dapat dihapus.');
            return;
        }
        if (this.steps[index].is_signed) {
            const signerName = this.steps[index].signer_name || 'Approver';
            const ok = await window.showConfirm('Tahap ini sudah ditandatangani oleh ' + signerName + '. Menghapus tahap ini akan membatalkan tanda tangan tersebut. Lanjutkan?', {
                title: 'Hapus Tahap Approval',
                danger: true,
                confirmText: 'Ya, Hapus'
            });
            if (!ok) {
                return;
            }
        }
        this.steps.splice(index, 1);
    },
    saveSigners(e) {
        const userIds = this.steps.map(s => String(s.user_id || '').trim()).filter(Boolean);
        const duplicates = userIds.filter((item, index) => userIds.indexOf(item) !== index);
        if (duplicates.length > 0) {
            e.preventDefault();
            alert('Satu orang tidak boleh menandatangani dua tahap. Pastikan setiap tahap memiliki penandatangan yang berbeda.');
            return false;
        }
    },

    // Modal Tolak Dokumen Approver
    rejectModalOpen: false,
    rejectApprovalId: null,
    rejectLabel: '',
    rejectActionUrl: '',
    rejectReason: '',

    openRejectModal(approvalId, label, actionUrl) {
        this.rejectApprovalId = approvalId;
        this.rejectLabel = label;
        this.rejectActionUrl = actionUrl;
        this.rejectReason = '';
        this.rejectModalOpen = true;
    },

    submitReject() {
        if (!this.rejectReason.trim()) {
            alert('Mohon masukkan alasan penolakan dokumen.');
            return;
        }
        const form = document.getElementById('doc-approval-reject-form-{{ $type }}-{{ $document->id }}');
        form.action = this.rejectActionUrl;
        document.getElementById('doc-approval-reject-reason-{{ $type }}-{{ $document->id }}').value = this.rejectReason;
        form.submit();
    },

    // Modal Tanda Tangan Digital Approver
    signModalOpen: false,
    signApprovalId: null,
    signLabel: '',
    signActionUrl: '',
    signMode: 'upload',
    uploadedSignature: '',
    signPad: null,

    openSignModal(approvalId, label, actionUrl) {
        this.signApprovalId = approvalId;
        this.signLabel = label;
        this.signActionUrl = actionUrl;
        this.signModalOpen = true;

        // Default mode: Upload Foto (Kiri)
        this.signMode = 'upload';

        const saved = localStorage.getItem('eprocure_saved_signature');
        if (saved) {
            this.uploadedSignature = saved;
        }

        this.$nextTick(() => {
            if (this.signMode === 'draw') {
                this.initSignPad();
            }
        });
    },

    clearSavedSignature() {
        this.uploadedSignature = '';
        try {
            localStorage.removeItem('eprocure_saved_signature');
        } catch(e) {}
    },

    initSignPad() {
        setTimeout(() => {
            const canvas = document.getElementById('doc-approval-canvas-{{ $type }}-{{ $document->id }}');
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            const width = rect.width || canvas.offsetWidth || 380;
            const height = rect.height || canvas.offsetHeight || 160;
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
            }
        }, 50);
    },

    clearPad() {
        if (this.signPad) this.signPad.clear();
    },

    // Fungsi bantuan untuk memotong (crop) margin transparan / kosong tanda tangan
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
                // Deteksi tinta jika tidak transparan dan bukan putih bersih
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

        // Jika kosong sama sekali
        if (maxX < minX || maxY < minY) {
            return sourceCanvas.toDataURL('image/png');
        }

        // Berikan padding secukupnya di sekeliling goresan tanda tangan
        const pad = Math.round(Math.max(maxX - minX, maxY - minY) * 0.05) + 6;
        const cropX = Math.max(0, minX - pad);
        const cropY = Math.max(0, minY - pad);
        const cropW = Math.min(w - cropX, (maxX - minX) + pad * 2);
        const cropH = Math.min(h - cropY, (maxY - minY) + pad * 2);

        // Skala ke resolusi optimal agar proporsional dan tidak pecah / tidak terlalu raksasa
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

                // Hilangkan background putih
                const imgData = tempCtx.getImageData(0, 0, tempCanvas.width, tempCanvas.height);
                const d = imgData.data;
                for (let i = 0; i < d.length; i += 4) {
                    const lum = (d[i] * 299 + d[i + 1] * 587 + d[i + 2] * 114) / 1000;
                    if (lum > 220) {
                        d[i + 3] = Math.round(d[i + 3] * (1 - Math.min(1, Math.max(0, (lum - 220) / 25))));
                    }
                }
                tempCtx.putImageData(imgData, 0, 0);

                // Crop margin putih/kosong
                this.uploadedSignature = this.cropSignature(tempCanvas);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    },

    submitSignature() {
        let sigData = '';
        if (this.signMode === 'upload') {
            if (!this.uploadedSignature) {
                alert('Pilih atau upload gambar tanda tangan terlebih dahulu.');
                return;
            }
            sigData = this.uploadedSignature;
        } else {
            if (!this.signPad || this.signPad.isEmpty()) {
                alert('Silakan tanda tangani pada kotak canvas terlebih dahulu.');
                return;
            }
            const rawCanvas = document.getElementById('doc-approval-canvas-{{ $type }}-{{ $document->id }}');
            sigData = this.cropSignature(rawCanvas);
        }

        try {
            localStorage.setItem('eprocure_saved_signature', sigData);
        } catch (e) {}

        const form = document.getElementById('doc-approval-sign-form-{{ $type }}-{{ $document->id }}');
        form.action = this.signActionUrl;
        document.getElementById('doc-approval-sig-input-{{ $type }}-{{ $document->id }}').value = sigData;
        form.submit();
    }
}" class="mt-6 pt-5 border-t border-gray-100">

    @php
        $rejectedApproval = $approvals->first(fn($a) => $a->isRejected());
        $hasConfiguredApprovals = $approvals->contains(fn($a) => $a->step > 1);
        $myActionableApproval = $hasConfiguredApprovals ? $approvals->first(fn($a) => $a->canBeSignedBy(auth()->user())) : null;
    @endphp

    {{-- Banner Notifikasi Jika Belum Diatur oleh Super Admin --}}
    @if(! $hasConfiguredApprovals && ! $rejectedApproval)
        <div class="mb-5 p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h5 class="text-sm font-bold text-amber-900">
                            Menunggu Pengaturan Persetujuan (Pending Setup)
                        </h5>
                        <span class="text-xs text-amber-700 font-semibold px-2 py-0.5 rounded bg-amber-100 border border-amber-300">
                            Status: Pending Setup
                        </span>
                    </div>
                    <p class="text-xs text-amber-800 mt-1">
                        @if($isAdmin)
                            Dokumen ini baru saja diajukan oleh inputer dan saat ini sedang menunggu Anda (Super Admin) untuk menentukan daftar alur pihak persetujuan (approver).
                        @else
                            Dokumen ini telah berhasil dibuat dan disimpan. Saat ini dokumen sedang menunggu Super Admin mengatur daftar penandatangan / pihak persetujuan sebelum dapat diproses.
                        @endif
                    </p>
                    @if($isAdmin)
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <template x-if="defaultTemplate && defaultTemplate.length > 0">
                                <button type="button" @click="adminModalOpen = true; applyDefaultTemplate()"
                                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Terapkan Template Default</span>
                                </button>
                            </template>
                            <button type="button" @click="adminModalOpen = true"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-700 hover:bg-amber-800 text-white rounded-lg text-xs font-semibold shadow-xs transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <span>Atur Manual</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Banner Notifikasi Jika Dokumen Ditolak --}}
    @if($rejectedApproval)
        <div class="mb-5 p-4 rounded-xl border border-red-200 bg-red-50 text-red-900 shadow-xs">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5 shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h5 class="text-sm font-bold text-red-800">
                            Dokumen Ditolak pada Tahap {{ $rejectedApproval->step }}: {{ $rejectedApproval->label }}
                        </h5>
                        <span class="text-xs text-red-600 font-medium">
                            {{ $rejectedApproval->rejected_at?->format('d M Y, H:i') }}
                        </span>
                    </div>
                    <p class="text-xs text-red-700 mt-1">
                        Ditolak oleh: <span class="font-semibold">{{ $rejectedApproval->user?->name ?? 'Approver' }}</span>
                    </p>
                    @if($rejectedApproval->rejection_reason)
                        <div class="mt-2.5 p-3 bg-white rounded-lg border border-red-200 text-xs text-gray-800 shadow-2xs">
                            <span class="font-bold text-red-800 block mb-0.5">Alasan Penolakan:</span>
                            <p class="whitespace-pre-line text-gray-700">{{ $rejectedApproval->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Header Bagian Persetujuan --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h4 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                <span>Alur Persetujuan & Tanda Tangan Digital</span>
                @if(! $hasConfiguredApprovals)
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                        Pending Setup
                    </span>
                @elseif($rejectedApproval)
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                        ✕ Ditolak
                    </span>
                @elseif($approvals->isNotEmpty())
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $approvals->every(fn($a) => $a->isSigned()) ? 'bg-green-100 text-green-700' : 'bg-indigo-50 text-indigo-700' }}">
                        {{ $approvals->filter(fn($a) => $a->isSigned())->count() }}/{{ $approvals->count() }} Selesai
                    </span>
                @endif
            </h4>
            <p class="text-xs text-gray-500 mt-0.5">Batas waktu tiap tahap adalah 2 hari. Jika melebihi batas waktu, sistem otomatis melanjutkan ke tahap berikutnya.</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Tombol Pengaturan Super Admin --}}
            @if($isAdmin)
                <button type="button" @click="adminModalOpen = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 {{ ! $hasConfiguredApprovals ? 'bg-amber-600 hover:bg-amber-700' : 'bg-gray-800 hover:bg-gray-900' }} text-white rounded-lg text-xs font-semibold shadow-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ ! $hasConfiguredApprovals ? 'Atur Penandatangan' : 'Kelola Penandatangan' }}</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Kasus 1: Belum ada approval yang diatur oleh Super Admin --}}
    @if($approvals->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-6 text-center bg-gray-50/50">
            <div class="w-10 h-10 mx-auto rounded-full bg-amber-50 text-amber-600 flex items-center justify-center mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h5 class="text-xs font-semibold text-gray-700">Belum Ada Penandatangan yang Ditetapkan</h5>
            <p class="text-[11px] text-gray-500 mt-1 max-w-md mx-auto">
                Kolom tanda tangan untuk dokumen ini akan muncul setelah Super Admin menentukan siapa saja petugas persetujuan yang bertugas.
            </p>
            @if($isAdmin)
                <button type="button" @click="adminModalOpen = true"
                        class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium shadow-xs transition">
                    + Tetapkan Penandatangan Sekarang
                </button>
            @endif
        </div>
    @else
        {{-- Kasus 2: Grid Kotak Tanda Tangan Dinamis --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
            @foreach($approvals as $approval)
                @php
                    $isSigned = $approval->isSigned();
                    $isRejected = $approval->isRejected();
                    $isUnlocked = $approval->isUnlocked();
                    $isOverdue = $approval->isOverdue();
                    $canSign = $approval->canBeSignedBy(auth()->user());
                    $assignedUser = $approval->user;
                @endphp
                <div class="approval-stage-card relative flex flex-col justify-between rounded-xl border p-3 transition {{ $isRejected ? 'bg-red-50/25 border-red-200 ring-1 ring-red-200/50' : ($isSigned ? 'bg-emerald-50/20 border-emerald-200 ring-1 ring-emerald-200/50' : ($canSign ? 'bg-white border-indigo-400 ring-2 ring-indigo-100 shadow-sm' : ($isUnlocked ? 'bg-white border-gray-200' : 'bg-gray-50/50 border-gray-200 opacity-80'))) }}">
                    {{-- Header Kotak --}}
                    <div>
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $isRejected ? 'bg-red-100 text-red-800' : ($isSigned ? 'bg-emerald-100 text-emerald-800' : ($isOverdue ? 'bg-red-100 text-red-700' : ($isUnlocked ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500'))) }}">
                                Tahap {{ $approval->step }}
                            </span>
                            @if($isRejected)
                                <span class="text-[8px] font-bold text-red-700 bg-red-50 border border-red-200 px-1 py-0.5 rounded">✕ Ditolak</span>
                            @elseif($isOverdue && ! $isSigned)
                                <span class="text-[8px] font-bold text-red-600 bg-red-50 border border-red-200 px-1 py-0.5 rounded">Overdue >2 Hari</span>
                            @elseif($isSigned)
                                <span class="text-[8px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1 py-0.5 rounded">✓ Disetujui</span>
                            @else
                                <span class="text-[8px] text-gray-400">Batas: 2 Hari</span>
                            @endif
                        </div>
                        <h5 class="text-xs font-bold text-gray-800 truncate" title="{{ $approval->label }}">
                            {{ $approval->label }}
                        </h5>
                    </div>

                    {{-- Area Tanda Tangan / Tombol --}}
                    <div class="my-2 py-1.5 border-y border-dashed border-gray-200 min-h-[96px] flex flex-col items-center justify-center">
                        @if($isRejected)
                            <div class="text-center py-1 px-2">
                                <div class="w-7 h-7 mx-auto rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <span class="text-[11px] font-bold text-red-700 block">Ditolak</span>
                                @if($approval->rejection_reason)
                                    <p class="text-[9px] text-red-600 line-clamp-2 mt-0.5" title="{{ $approval->rejection_reason }}">
                                        "{{ $approval->rejection_reason }}"
                                    </p>
                                @endif
                            </div>
                        @elseif($isSigned && !empty($approval->signature))
                            <img src="{{ $approval->signature }}" alt="Tanda Tangan" style="max-height: 76px; max-width: 100%; object-fit: contain;" class="p-1 filter drop-shadow-xs">
                        @elseif($canSign)
                            <div class="w-full flex flex-col gap-1.5">
                                <button type="button"
                                        @click="openSignModal({{ $approval->id }}, '{{ addslashes($approval->label) }}', '{{ route('documents.approvals.sign', ['type' => $type, 'id' => $document->id, 'approvalId' => $approval->id]) }}')"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                    Tanda Tangani
                                </button>
                                <button type="button"
                                        @click="openRejectModal({{ $approval->id }}, '{{ addslashes($approval->label) }}', '{{ route('documents.approvals.reject', ['type' => $type, 'id' => $document->id, 'approvalId' => $approval->id]) }}')"
                                        class="w-full inline-flex items-center justify-center gap-1 px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-[11px] font-semibold transition">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Tolak Dokumen
                                </button>
                            </div>
                        @elseif(! $isUnlocked)
                            <div class="text-center text-gray-400 py-1">
                                <svg class="w-4 h-4 mx-auto mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span class="text-[10px]">Terkunci (Tahap {{ $approval->step - 1 }})</span>
                            </div>
                        @else
                            <div class="text-center text-gray-400 py-1">
                                <span class="text-[10px] text-gray-500">Menunggu tanda tangan</span>
                                @if($assignedUser)
                                    <p class="text-[9px] text-gray-400 mt-0.5">{{ $assignedUser->name }}</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Informasi Petugas & Timestamp --}}
                    <div class="text-center">
                        <p class="text-[10px] font-semibold text-gray-800 truncate" title="{{ $assignedUser->name ?? '-' }}">
                            {{ $assignedUser->name ?? '-' }}
                        </p>
                        @if(!empty($assignedUser?->jabatan))
                            <p class="text-[9px] text-gray-500 truncate mt-0.5" title="{{ $assignedUser->jabatan }}">
                                {{ $assignedUser->jabatan }}
                            </p>
                        @endif
                        @if($isRejected)
                            <p class="text-[9px] text-red-600 font-medium leading-none mt-0.5">
                                {{ $approval->rejected_at?->format('d-m-Y H:i') }}
                            </p>
                        @elseif($isSigned)
                            <p class="text-[9px] text-emerald-600 font-medium leading-none mt-0.5">
                                {{ $approval->signed_at->format('d-m-Y H:i') }}
                            </p>
                        @else
                            <p class="text-[9px] text-gray-400 mt-0.5 leading-none">
                                {{ $isOverdue ? 'Overdue' : 'Menunggu' }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach

            @if($approvals->count() === 1)
                <div class="col-span-full p-3.5 rounded-xl border border-dashed border-gray-300 bg-gray-50/70 text-center">
                    <p class="text-xs text-gray-600">
                        Dokumen telah dibuat & ditandatangani oleh <strong>{{ $approvals->first()->user?->name ?? 'Pembuat Dokumen' }}</strong> (Prepared By).
                        @if($isAdmin)
                            Silakan klik tombol <strong>Kelola Penandatangan</strong> di atas untuk menetapkan tahapan petugas approval berikutnya (Reviewer, Approver, dll.).
                        @else
                            Menunggu Super Admin menetapkan alur persetujuan lebih lanjut untuk dokumen ini.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    @endif

    {{-- MODAL ADMIN: ATUR PENANDATANGAN DOKUMEN --}}
    @if($isAdmin)
        <template x-teleport="body">
            <div x-show="adminModalOpen" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 min-h-screen">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-xl w-full p-6 text-left my-8"
                     @click.outside="adminModalOpen = false">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Atur Penandatangan Dokumen</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Tentukan tahapan dan petugas yang berhak menandatangani dokumen ini.</p>
                        </div>
                        <button type="button" @click="adminModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none p-1">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('admin.documents.approvals.store', ['type' => $type, 'id' => $document->id]) }}" @submit="saveSigners($event)" class="space-y-4">
                        @csrf

                        <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                            <template x-for="(step, index) in steps" :key="step.id ? 'step_' + step.id : 'idx_' + index">
                                <div class="flex items-center gap-2 p-3 rounded-xl border border-gray-200 bg-gray-50/50">
                                    {{-- ID Approval jika sudah tersimpan di database --}}
                                    <input type="hidden" :name="'steps[' + index + '][id]'" :value="step.id || ''">

                                    <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0"
                                          x-text="index + 1"></span>

                                    {{-- Input Label / Nama Tahap --}}
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <label class="block text-[10px] uppercase font-bold text-gray-400">Label Jabatan / Tahap</label>
                                            <template x-if="step.label === 'Prepared By'">
                                                <span class="text-[9px] font-semibold text-indigo-600 bg-indigo-50 px-1 py-0.2 rounded">Pembuat Dokumen</span>
                                            </template>
                                        </div>
                                        <template x-if="step.label === 'Prepared By'">
                                            <input type="text" :name="'steps[' + index + '][label]'" :value="step.label" readonly
                                                   class="w-full text-xs rounded-lg border-gray-200 bg-gray-100 text-gray-600 py-1.5 cursor-not-allowed">
                                        </template>
                                        <template x-if="step.label !== 'Prepared By'">
                                            <select :name="'steps[' + index + '][label]'" x-model="step.label" required
                                                    class="w-full text-xs rounded-lg border-gray-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="" disabled>-- Pilih Jabatan / Tahap --</option>
                                                @foreach($labelOptions as $lbl)
                                                    <option value="{{ $lbl }}">{{ $lbl }}</option>
                                                @endforeach
                                            </select>
                                        </template>
                                    </div>

                                    {{-- Dropdown Petugas Approval --}}
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <label class="block text-[10px] uppercase font-bold text-gray-400">Petugas Penyetuju</label>
                                            <template x-if="step.is_signed">
                                                <span class="text-[9px] font-semibold text-emerald-600 bg-emerald-50 px-1 py-0.2 rounded">Sudah TTD</span>
                                            </template>
                                        </div>
                                        <template x-if="step.label === 'Prepared By'">
                                            <div class="w-full text-xs rounded-lg border border-gray-200 bg-gray-100 py-1.5 px-2 text-gray-700 truncate">
                                                <span x-text="step.signer_name || 'Pembuat Dokumen'"></span>
                                                <input type="hidden" :name="'steps[' + index + '][user_id]'" :value="step.user_id">
                                            </div>
                                        </template>
                                        <template x-if="step.label !== 'Prepared By'">
                                            <select :name="'steps[' + index + '][user_id]'" x-model="step.user_id" required
                                                    class="w-full text-xs rounded-lg border-gray-300 py-1.5 focus:ring-indigo-500 focus:border-indigo-500">
                                                @foreach($approverUsers as $appUser)
                                                    <option value="{{ $appUser->id }}">{{ $appUser->name }} ({{ $appUser->jabatan ? $appUser->jabatan . ' • ' : '' }}{{ $appUser->email }})</option>
                                                @endforeach
                                            </select>
                                        </template>
                                    </div>

                                    {{-- Tombol Hapus Step --}}
                                    <template x-if="step.label === 'Prepared By'">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-300 shrink-0" title="Tahap Pembuat Dokumen tidak dapat dihapus">
                                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                    </template>
                                    <template x-if="step.label !== 'Prepared By'">
                                        <button type="button" @click="removeStep(index)" title="Hapus Tahap"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 shrink-0 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="addStep()"
                                    class="flex-1 py-2 border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-xl text-xs font-semibold text-gray-600 hover:text-indigo-600 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Tambah Tahap</span>
                            </button>
                            <template x-if="defaultTemplate && defaultTemplate.length > 0">
                                <button type="button" @click="applyDefaultTemplate()"
                                        title="Muat ulang tahapan dari template default"
                                        class="px-3 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 cursor-pointer shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span>Terapkan Template Default</span>
                                </button>
                            </template>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                            <button type="button" @click="adminModalOpen = false"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                Simpan Penandatangan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    @endif

    {{-- MODAL APPROVER: TANDA TANGAN DIGITAL --}}
    <template x-teleport="body">
        <div x-show="signModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 min-h-screen">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-left my-8"
                 @click.outside="signModalOpen = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tanda Tangan Digital</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Menandatangani sebagai: <strong class="text-indigo-600" x-text="signLabel"></strong></p>
                    </div>
                    <button type="button" @click="signModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none p-1">&times;</button>
                </div>

                {{-- Pilihan Mode: Upload Foto (KIRI) & Coret Langsung (KANAN) --}}
                <div class="grid grid-cols-2 gap-2.5 mb-4">
                    {{-- KIRI: Upload Foto --}}
                    <label class="p-2.5 rounded-xl border-2 cursor-pointer text-center text-xs font-bold transition flex items-center justify-center gap-1.5"
                           :class="signMode === 'upload' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 ring-1 ring-indigo-500/20' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                           @click="signMode = 'upload'">
                        <input type="radio" name="sig_mode_{{ $type }}_{{ $document->id }}" value="upload" x-model="signMode" class="hidden">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Upload Foto</span>
                    </label>

                    {{-- KANAN: Coret Langsung --}}
                    <label class="p-2.5 rounded-xl border-2 cursor-pointer text-center text-xs font-bold transition flex items-center justify-center gap-1.5"
                           :class="signMode === 'draw' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 ring-1 ring-indigo-500/20' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                           @click="signMode = 'draw'; $nextTick(() => initSignPad())">
                        <input type="radio" name="sig_mode_{{ $type }}_{{ $document->id }}" value="draw" x-model="signMode" @change="$nextTick(() => initSignPad())" class="hidden">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Coret Langsung</span>
                    </label>
                </div>

                {{-- Mode 1: Upload (KIRI) --}}
                <div x-show="signMode === 'upload'" class="space-y-3">
                    <div class="border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-xl p-4 text-center bg-gray-50/50 cursor-pointer relative transition"
                         @click="$refs.fileInputDoc.click()">
                        <input type="file" x-ref="fileInputDoc" @change="handleFileUpload($event)" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">
                        <template x-if="!uploadedSignature">
                            <div class="py-4">
                                <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-xs font-semibold text-gray-700">Klik untuk memilih gambar tanda tangan</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">PNG / JPG (Latar kertas putih otomatis transparan)</p>
                            </div>
                        </template>
                        <template x-if="uploadedSignature">
                            <div class="py-1">
                                <div class="h-28 w-full flex items-center justify-center bg-white rounded-lg border border-gray-200 p-2 shadow-inner">
                                    <img :src="uploadedSignature" alt="Preview TTD" style="max-height: 90px; max-width: 100%; object-fit: contain;" class="filter drop-shadow-xs">
                                </div>
                                <div class="flex items-center justify-center gap-3 mt-2.5">
                                    <span class="text-[11px] text-indigo-600 font-medium hover:underline">Klik untuk ganti gambar</span>
                                    <span class="text-gray-300">•</span>
                                    <button type="button" @click.stop="clearSavedSignature()" class="text-[11px] text-red-500 hover:text-red-700 font-medium hover:underline">Hapus / Reset</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Mode 2: Draw (KANAN) --}}
                <div x-show="signMode === 'draw'" class="space-y-3">
                    <div class="relative bg-gray-50/70 rounded-xl border border-gray-200 p-2">
                        <canvas id="doc-approval-canvas-{{ $type }}-{{ $document->id }}"
                                style="height: 160px; width: 100%; display: block;"
                                class="bg-white rounded-lg touch-none shadow-inner cursor-crosshair"></canvas>
                        <div class="absolute bottom-4 left-4 right-4 border-b border-gray-300 pointer-events-none flex justify-between items-end pb-1">
                            <span class="text-[10px] text-gray-400 font-normal">Goreskan tanda tangan di atas garis ini</span>
                            <span class="text-[10px] text-gray-400 font-normal">✕</span>
                        </div>
                        <button type="button" @click="clearPad()" class="absolute top-3 right-3 text-[10px] font-semibold text-gray-500 hover:text-red-600 px-2 py-1 rounded bg-gray-100 hover:bg-red-50 border border-gray-200 transition">
                            Bersihkan
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-400 text-center">Gunakan mouse, trackpad, atau sentuhan layar</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100 mt-4">
                    <button type="button" @click="signModalOpen = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" @click="submitSignature()"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Simpan & Bubuhkan TTD
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Form tersembunyi untuk submit tanda tangan --}}
    <form id="doc-approval-sign-form-{{ $type }}-{{ $document->id }}" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="signature" id="doc-approval-sig-input-{{ $type }}-{{ $document->id }}" value="">
    </form>

    {{-- MODAL APPROVER: TOLAK DOKUMEN --}}
    <template x-teleport="body">
        <div x-show="rejectModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4 min-h-screen">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-left my-8"
                 @click.outside="rejectModalOpen = false">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Tolak Dokumen</h3>
                            <p class="text-xs text-gray-500">Menolak persetujuan sebagai: <strong class="text-red-600" x-text="rejectLabel"></strong></p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg leading-none p-1">&times;</button>
                </div>

                <div class="p-3 bg-red-50 border border-red-200 rounded-xl mb-4 text-xs text-red-800">
                    <p class="font-semibold mb-1">Perhatian:</p>
                    <p class="text-[11px] leading-relaxed">Menolak dokumen ini akan membatalkan/menghentikan alur persetujuan dokumen ini dan statusnya akan berubah menjadi <strong class="font-bold">Rejected</strong>.</p>
                </div>

                <div class="space-y-1.5 mb-4">
                    <label class="block text-xs font-bold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea x-model="rejectReason"
                              rows="3"
                              required
                              placeholder="Masukkan alasan penolakan dokumen..."
                              class="w-full text-xs rounded-xl border-gray-300 focus:ring-red-500 focus:border-red-500 placeholder-gray-400"></textarea>
                    <p class="text-[10px] text-gray-400">Alasan ini akan tercatat dalam sistem dan ditampilkan pada halaman detail dokumen.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="rejectModalOpen = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" @click="submitReject()"
                            class="px-5 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-semibold rounded-lg shadow-sm transition inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Ya, Tolak Dokumen
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- Form tersembunyi untuk submit penolakan --}}
    <form id="doc-approval-reject-form-{{ $type }}-{{ $document->id }}" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="reason" id="doc-approval-reject-reason-{{ $type }}-{{ $document->id }}" value="">
    </form>
</div>

