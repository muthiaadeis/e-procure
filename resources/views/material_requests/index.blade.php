<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Material Request (MR)</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola dan pantau seluruh permintaan material.</p>
            </div>
            @if(auth()->user()->isInput())
                <a href="{{ route('material-requests.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah MR
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8" x-data="{
            confirmOpen: false,
            confirmTitle: '',
            confirmMessage: '',
            confirmColor: 'indigo',
            confirmIcon: 'check',
            confirmLabel: 'Ya, Lanjutkan',
            confirmFormId: null,
            openConfirm(title, message, color, icon, label, formId) {
                this.confirmTitle = title;
                this.confirmMessage = message;
                this.confirmColor = color;
                this.confirmIcon = icon;
                this.confirmLabel = label;
                this.confirmFormId = formId;
                this.confirmOpen = true;
            },
            submitConfirm() {
                this.confirmOpen = false;
                document.getElementById(this.confirmFormId).submit();
            },
            signModalOpen: false,
            signLabel: '',
            signPad: null,
            signRequired: false,
            signMode: 'upload',
            uploadedSignature: '',
            rememberSignature: true,
            initPad() {
                const setupPad = () => {
                    const canvas = document.getElementById('mr-signature-canvas');
                    if (!canvas) return;
                    if (!canvas.offsetWidth || !canvas.offsetHeight) {
                        requestAnimationFrame(setupPad);
                        return;
                    }
                    if (this.signPad) {
                        this.signPad.off();
                        this.signPad = null;
                    }
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
                };
                setupPad();
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
                                const r = data[i];
                                const g = data[i + 1];
                                const b = data[i + 2];
                                const a = data[i + 3];

                                if (a > 30) {
                                    const lum = (r * 299 + g * 587 + b * 114) / 1000;
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
            openSign(id, label, action, method = 'PATCH', required = false) {
                this.signLabel = label;
                this.signModalOpen = true;
                this.signRequired = required;
                const form = document.getElementById('mr-sign-form');
                form.action = action;
                document.getElementById('mr-sign-method-input').value = method;

                const saved = localStorage.getItem('eprocure_saved_signature');
                if (saved) {
                    this.uploadedSignature = saved;
                    this.signMode = 'upload';
                } else if (!this.uploadedSignature) {
                    this.signMode = 'draw';
                }

                this.$nextTick(() => {
                    if (this.signMode === 'draw') {
                        this.initPad();
                    }
                });
            },
            clearSignPad() {
                if (this.signPad) this.signPad.clear();
            },
            closeSignModal() {
                if (this.signRequired) return; // wajib ttd dulu, gak boleh ditutup
                this.signModalOpen = false;
                this.signPad = null;
            },
            signSubmitting: false,
            submitSignature() {
                let signatureData = '';
                if (this.signMode === 'upload') {
                    if (!this.uploadedSignature) {
                        alert('Please choose or upload a signature image first.');
                        return;
                    }
                    signatureData = this.uploadedSignature;
                } else {
                    if (!this.signPad || this.signPad.isEmpty()) {
                        alert('Please sign in the box first.');
                        return;
                    }
                    signatureData = this.signPad.toDataURL('image/png');
                }

                if (this.rememberSignature && signatureData) {
                    try {
                        localStorage.setItem('eprocure_saved_signature', signatureData);
                    } catch (e) {}
                }

                if (this.signSubmitting) return;
                this.signSubmitting = true;

                const form = document.getElementById('mr-sign-form');
                document.getElementById('mr-signature-data-input').value = signatureData;
                const formData = new FormData(form);

                // Dikirim via fetch (bukan form.submit()) biar halaman gak reload
                // sama sekali — jadi modal detail yang lagi kebuka gak ikut ketutup.
                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                })
                    .then(async (res) => {
                        const body = await res.json().catch(() => null);
                        if (!res.ok || !body || !body.success) {
                            throw new Error((body && body.message) || 'Failed to save signature.');
                        }
                        return body;
                    })
                    .then((body) => {
                        // Modal detail TETAP kebuka, cuma datanya di-refresh di tempat.
                        this.detailData = body.data;
                        this.signModalOpen = false;
                        this.signRequired = false;
                        this.signPad = null;
                        this.showToast(body.message);
                        window.dispatchEvent(new CustomEvent('mr-list-refresh'));
                    })
                    .catch((err) => {
                        alert(err.message || 'Failed to save signature. Please check your connection.');
                    })
                    .finally(() => {
                        this.signSubmitting = false;
                    });
            },
            toastMessage: null,
            toastTimer: null,
            showToast(message) {
                this.toastMessage = message;
                clearTimeout(this.toastTimer);
                this.toastTimer = setTimeout(() => { this.toastMessage = null; }, 3500);
            },
            detailOpen: false,
            detailData: {},
            openDetail(data) {
                this.detailData = data;
                this.detailOpen = true;
            },
            rejectOpen: false,
            rejectTitle: '',
            rejectMessage: '',
            rejectReason: '',
            rejectFormId: null,
            openReject(title, message, formId) {
                this.rejectTitle = title;
                this.rejectMessage = message;
                this.rejectReason = '';
                this.rejectFormId = formId;
                this.rejectOpen = true;
            },
            submitReject() {
                if (!this.rejectReason.trim()) return;
                const form = document.getElementById(this.rejectFormId);
                form.querySelector('[name=reason]').value = this.rejectReason;
                this.rejectOpen = false;
                form.submit();
            },
            init() {
                @php
                    $autoOpenReq = null;
                    if (request('auto_open')) {
                        $autoOpenTarget = request('auto_open');
                        $autoOpenReq = $requests->first(function($r) use ($autoOpenTarget) {
                            return $r->id == $autoOpenTarget || $r->no_mr == $autoOpenTarget;
                        });
                        if (! $autoOpenReq) {
                            $autoOpenReq = \App\Models\MaterialRequest::with(['items', 'approverA', 'approverC', 'rejectorA', 'rejectorC', 'financeRejector', 'paidByUser', 'creator'])
                                ->where('id', $autoOpenTarget)
                                ->orWhere('no_mr', $autoOpenTarget)
                                ->first();
                        }
                    }
                @endphp
                @if(isset($autoOpenReq) && $autoOpenReq)
                    @php
                        $autoPayload = [
                            'id' => $autoOpenReq->id,
                            'no' => 1,
                            'no_mr' => $autoOpenReq->no_mr ?? '-',
                            'date' => $autoOpenReq->date ? $autoOpenReq->date->format('d-m-Y') : '-',
                            'charge_to' => $autoOpenReq->charge_to,
                            'items' => $autoOpenReq->items->map(fn ($item) => [
                                'description' => $item->description,
                                'quantity' => $item->quantity,
                                'unit' => $item->unit,
                                'remarks' => $item->remarks,
                            ])->values(),
                            'approver_a' => $autoOpenReq->approverA->name ?? '-',
                            'is_approved_a' => $autoOpenReq->is_approved_by_a,
                            'approved_a_at' => $autoOpenReq->approved_a_at ? $autoOpenReq->approved_a_at->format('d-m-Y H:i') : null,
                            'is_rejected_a' => $autoOpenReq->is_rejected_by_a,
                            'rejection_a_reason' => $autoOpenReq->rejection_a_reason,
                            'approver_c' => $autoOpenReq->approverC->name ?? '-',
                            'is_approved_c' => $autoOpenReq->is_approved_by_c,
                            'approved_c_at' => $autoOpenReq->approved_c_at ? $autoOpenReq->approved_c_at->format('d-m-Y H:i') : null,
                            'is_rejected_c' => $autoOpenReq->is_rejected_by_c,
                            'rejection_c_reason' => $autoOpenReq->rejection_c_reason,
                            'is_rejected_finance' => $autoOpenReq->is_rejected_by_finance,
                            'finance_rejection_reason' => $autoOpenReq->finance_rejection_reason,
                            'finance_rejector' => $autoOpenReq->financeRejector->name ?? '-',
                            'status' => $autoOpenReq->status,
                            'is_overdue' => $autoOpenReq->is_overdue,
                            'overdue_reason' => $autoOpenReq->overdue_reason,
                            'is_paid' => ! is_null($autoOpenReq->paid_at),
                            'paid_by' => $autoOpenReq->paidByUser->name ?? '-',
                            'paid_at' => $autoOpenReq->paid_at ? $autoOpenReq->paid_at->format('d-m-Y H:i') : null,
                            'created_by' => $autoOpenReq->creator->name ?? '-',
                            'created_at' => $autoOpenReq->created_at ? $autoOpenReq->created_at->format('d-m-Y H:i') : '-',
                            'created_signature' => $autoOpenReq->created_signature,
                            'approved_a_signature' => $autoOpenReq->approved_a_signature,
                            'approved_c_signature' => $autoOpenReq->approved_c_signature,
                            'paid_signature' => $autoOpenReq->paid_signature,
                            'can_sign_a' => auth()->check() && (auth()->user()->isApproverA() || auth()->user()->isAdmin()) && $autoOpenReq->created_signature && ! $autoOpenReq->is_approved_by_a && ! $autoOpenReq->is_rejected_by_a,
                            'can_sign_c' => auth()->check() && (auth()->user()->isApproverC() || auth()->user()->isAdmin()) && $autoOpenReq->is_approved_by_a && ! $autoOpenReq->is_approved_by_c && ! $autoOpenReq->is_rejected_by_c,
                            'can_sign_finance' => auth()->check() && (auth()->user()->isFinance() || auth()->user()->isAdmin()) && $autoOpenReq->is_approved && ! $autoOpenReq->paid_at && ! $autoOpenReq->is_rejected_by_finance,
                            'can_sign_prepared' => auth()->check() && (auth()->id() === $autoOpenReq->created_by || auth()->user()->isAdmin()) && ! $autoOpenReq->created_signature,
                            'approve_url' => route('material-requests.approve', $autoOpenReq),
                            'mark_paid_url' => route('material-requests.mark-paid', $autoOpenReq),
                            'sign_prepared_url' => route('material-requests.sign-prepared', $autoOpenReq),
                            'print_url' => route('material-requests.print', $autoOpenReq),
                        ];
                    @endphp
                    this.$nextTick(() => {
                        this.openDetail({{ \Illuminate\Support\Js::from($autoPayload) }});
                        const targetRow = document.getElementById('mr-row-{{ $autoOpenReq->id }}');
                        if (targetRow) {
                            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            // highlight biru cuma sebentar, abis itu fade balik normal
                            setTimeout(() => {
                                targetRow.classList.remove('bg-indigo-50/80', 'ring-2', 'ring-indigo-500/50');
                                targetRow.classList.add('hover:bg-gray-50');
                            }, 2500);
                        }
                        // bersihin auto_open/auto_sign dari URL biar refresh gak nge-biru-in lagi
                        const cleanUrl = new URL(window.location.href);
                        cleanUrl.searchParams.delete('auto_open');
                        cleanUrl.searchParams.delete('auto_sign');
                        window.history.replaceState({}, '', cleanUrl);
                        @if(request('auto_sign') && $autoOpenReq->created_signature === null)
                            this.openSign({{ \Illuminate\Support\Js::from($autoPayload['id']) }}, 'Requested By (' + {{ \Illuminate\Support\Js::from($autoPayload['no_mr']) }} + ')', {{ \Illuminate\Support\Js::from($autoPayload['sign_prepared_url']) }}, 'POST', true);
                        @endif
                    });
                @endif
            }
        }">
        {{-- Toast buat notifikasi hasil sign/approve yang dikirim via AJAX (mr-sign-form),
             karena gak ada reload halaman jadi session('success') gak kepake di sini. --}}
        <div x-show="toastMessage"
             x-cloak
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="fixed top-5 right-5 z-[60] flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-lg max-w-sm">
            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </span>
            <span class="flex-1 font-medium" x-text="toastMessage"></span>
            <button type="button" @click="toastMessage = null" class="flex-shrink-0 text-green-500 hover:text-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <x-draft-list form="material-requests" />

            @if(session('success'))
                <div x-data="{ show: true }"
                     x-show="show"
                     x-init="setTimeout(() => show = false, 4000)"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="mb-4 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </span>
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="flex-shrink-0 text-green-500 hover:text-green-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-xl p-6">
                <div class="flex flex-wrap justify-between items-center gap-3 mb-4">
                    <div class="flex flex-wrap items-center gap-3">
                    <div class="relative"
                         x-data="{
                            q: '{{ addslashes($search) }}',
                            timer: null,
                            loading: false,
                            runSearch() {
                                clearTimeout(this.timer);
                                this.timer = setTimeout(() => {
                                    this.fetchResults();
                                }, 350);
                            },
                            fetchResults() {
                                this.loading = true;
                                const params = new URLSearchParams();
                                if (this.q) params.set('search', this.q);
                                @if(request('filter'))
                                params.set('filter', '{{ request('filter') }}');
                                @endif
                                const qs = params.toString();
                                const url = '{{ route('material-requests.index') }}' + (qs ? '?' + qs : '');
                                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(res => res.text())
                                    .then(html => {
                                        document.getElementById('mr-results').innerHTML = html;
                                        window.history.replaceState({}, '', url);
                                    })
                                    .finally(() => { this.loading = false; });
                            },
                            clearSearch() {
                                this.q = '';
                                this.fetchResults();
                            },
                            init() {
                                // Biar baris di tabel ikut ke-update abis ttd/approve/reject,
                                // tanpa perlu reload halaman.
                                window.addEventListener('mr-list-refresh', () => this.fetchResults());
                            }
                         }">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text"
                               x-model="q"
                               @input="runSearch()"
                               @keydown.enter.prevent="clearTimeout(timer); fetchResults()"
                               placeholder="Cari No. MR atau Charge To..."
                               autocomplete="off"
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-64 pl-10 pr-8 py-2">
                        <button type="button"
                                x-show="q"
                                x-cloak
                                @click="clearSearch()"
                                title="Hapus pencarian"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="relative w-56" x-data="{ filterOpen: false }" @click.outside="filterOpen = false">
                        @php
                            $filterLabels = [
                                'pending_approval' => 'Menunggu Persetujuan',
                                'overdue' => 'Melebihi Batas Waktu',
                                'rejected' => 'Ditolak',
                                'done' => 'Selesai',
                            ];
                            $currentFilterLabel = $filterLabels[request('filter')] ?? 'All MRs';
                        @endphp
                        <button type="button"
                                @click="filterOpen = !filterOpen"
                                class="w-full flex items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-gray-300 transition">
                            <span class="flex items-center gap-2 whitespace-nowrap">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4.5h18M6 9h12M9.75 13.5h4.5M11.25 18h1.5"/>
                                </svg>
                                {{ $currentFilterLabel }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="filterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="filterOpen"
                             x-cloak
                             x-transition:enter="ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute left-0 top-full mt-1.5 w-full bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-20 text-left">
                            <a href="{{ route('material-requests.index') }}"
                               class="flex items-center justify-between px-3.5 py-2 text-sm transition {{ ! request('filter') ? 'text-indigo-600 font-medium bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                All MRs
                                @if(! request('filter'))
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                            <a href="{{ route('material-requests.index', ['filter' => 'pending_approval']) }}"
                            class="flex items-center justify-between px-3.5 py-2 text-sm transition {{ request('filter') === 'pending_approval' ? 'text-indigo-600 font-medium bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                Pending Approval
                                @if(request('filter') === 'pending_approval')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                            <a href="{{ route('material-requests.index', ['filter' => 'overdue']) }}"
                               class="flex items-center justify-between px-3.5 py-2 text-sm transition {{ request('filter') === 'overdue' ? 'text-indigo-600 font-medium bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                Overdue
                                @if(request('filter') === 'overdue')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                            <a href="{{ route('material-requests.index', ['filter' => 'done']) }}"
                               class="flex items-center justify-between px-3.5 py-2 text-sm transition {{ request('filter') === 'done' ? 'text-indigo-600 font-medium bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                Done
                                @if(request('filter') === 'done')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                            <a href="{{ route('material-requests.index', ['filter' => 'rejected']) }}"
                               class="flex items-center justify-between px-3.5 py-2 text-sm transition {{ request('filter') === 'rejected' ? 'text-indigo-600 font-medium bg-indigo-50' : 'text-gray-600 hover:bg-gray-50' }}">
                                Rejected
                                @if(request('filter') === 'rejected')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
                </div>
                <div id="mr-results">
                    @include('material_requests._results', ['requests' => $requests, 'search' => $search])
                </div>

            </div>
        </div>

        {{-- Modal Detail --}}
        <div x-show="detailOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-8"
             style="display: none;">
            <div x-show="detailOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="if (!signModalOpen) detailOpen = false"
                 class="fixed inset-0 bg-gray-900/50"></div>

            <div class="relative min-h-full flex items-center justify-center">
            <div x-show="detailOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 @keydown.escape.window="if (!signModalOpen) detailOpen = false"
                 @click.outside="if (!signModalOpen) detailOpen = false"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-3xl sm:max-w-4xl p-6 sm:p-7">

                <div class="flex items-center justify-between mb-1">
                    <h3 class="text-lg font-semibold text-gray-800">Material Request Detail</h3>
                    <button type="button" @click="detailOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-between mb-1 pb-4 border-b border-gray-100">
                    <p class="text-sm font-medium text-indigo-600" x-text="detailData.no_mr"></p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium"
                          :class="{
                            'bg-green-100 text-green-700': detailData.status === 'Done',
                            'bg-blue-100 text-blue-700': detailData.status === 'Pending Payment',
                            'bg-yellow-100 text-yellow-700': detailData.status === 'Pending Approval 2',
                            'bg-gray-100 text-gray-600': detailData.status === 'Pending Approval 1',
                            'bg-red-100 text-red-700': detailData.status === 'Rejected (Approval 1)' || detailData.status === 'Ditolak (Approval 2)' || detailData.status === 'Ditolak (Finance)'
                          }"
                          x-text="detailData.status"></span>
                </div>
                <p class="text-xs text-red-600 font-medium mb-4" x-show="detailData.is_overdue" x-text="'⚠ ' + detailData.overdue_reason"></p>

                <div class="space-y-4 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Date</p>
                        <p class="text-gray-800 font-medium" x-text="detailData.date"></p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Charge To</p>
                        <p class="text-gray-800 font-medium" x-text="detailData.charge_to"></p>
                    </div>
                </div>

                <div>
                    <p class="text-gray-400 text-xs mb-1.5">Material Items</p>
                    <div class="border border-gray-100 rounded-lg divide-y divide-gray-100 max-h-48 overflow-y-auto">
                        <template x-for="(item, index) in detailData.items" :key="index">
                            <div class="p-3">
                                <p class="text-gray-800 font-medium text-sm" x-text="(index + 1) + '. ' + item.description"></p>
                                <p class="text-gray-500 text-xs mt-0.5" x-text="item.quantity + ' ' + item.unit"></p>
                                <p class="text-gray-400 text-xs mt-0.5" x-show="item.remarks" x-text="'Note: ' + item.remarks"></p>
                            </div>
                        </template>
                        <p class="p-3 text-xs text-gray-400 text-center" x-show="!detailData.items || detailData.items.length === 0">No items</p>
                    </div>
                </div>

                {{-- BARU: siapa yang input --}}
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-gray-400 text-xs mb-0.5">Input by</p>
                    <p class="text-gray-800 font-medium" x-text="detailData.created_by"></p>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="detailData.created_at"></p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-1">
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Approval 1</p>
                        <p class="text-gray-800 font-medium" x-text="detailData.approver_a || 'Approver 1'"></p>
                        <p class="text-xs text-green-600 mt-0.5"
                        x-show="detailData.is_approved_a"
                        x-text="'✓ Approved ' + detailData.approved_a_at"></p>
                        <p class="text-xs text-red-600 mt-0.5"
                        x-show="detailData.is_rejected_a"
                        x-text="'✗ Rejected: ' + detailData.rejection_a_reason"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-show="!detailData.is_approved_a && !detailData.is_rejected_a">Awaiting approval</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Approval 2</p>
                        <p class="text-gray-800 font-medium" x-text="detailData.approver_c || 'Approver 2'"></p>
                        <p class="text-xs text-green-600 mt-0.5"
                        x-show="detailData.is_approved_c"
                        x-text="'✓ Approved ' + detailData.approved_c_at"></p>
                        <p class="text-xs text-red-600 mt-0.5"
                        x-show="detailData.is_rejected_c"
                        x-text="'✗ Rejected: ' + detailData.rejection_c_reason"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-show="!detailData.is_approved_c && !detailData.is_rejected_c">Awaiting approval</p>
                    </div>
                </div>

                {{-- Finance sekarang SELALU tampil (dulu cuma muncul kalau ditolak) --}}
                <div class="pt-3 border-t border-gray-100">
                    <p class="text-gray-400 text-xs mb-0.5">Finance</p>
                    <template x-if="detailData.is_rejected_finance">
                        <div>
                            <p class="text-gray-800 font-medium text-xs" x-text="'Rejected by ' + detailData.finance_rejector"></p>
                            <p class="text-xs text-red-600 mt-0.5" x-text="'✗ Reason: ' + detailData.finance_rejection_reason"></p>
                        </div>
                    </template>
                    <template x-if="!detailData.is_rejected_finance && detailData.is_paid">
                        <div>
                            <p class="text-gray-800 font-medium" x-text="detailData.paid_by"></p>
                            <p class="text-xs text-green-600 mt-0.5" x-text="'✓ Paid ' + detailData.paid_at"></p>
                        </div>
                    </template>
                    <template x-if="!detailData.is_rejected_finance && !detailData.is_paid">
                        <p class="text-xs text-gray-400 mt-0.5">Menunggu pembayaran</p>
                    </template>
                </div>

                {{-- Approval Workflow Grid (4 Cards) --}}
                <div class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Alur Persetujuan</h4>
                        <span class="text-[10px] text-gray-400">Tanda tangan digital berurutan</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        {{-- Card 1: Requested By --}}
                        <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                             :class="detailData.created_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : 'bg-gray-50/20 border-gray-200'">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                          :class="detailData.created_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'">
                                        Prepared
                                    </span>
                                    <span class="text-[8px] font-medium text-gray-400">Step 1</span>
                                </div>
                                <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Requested By">Requested By</h5>
                            </div>

                            <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[96px] sm:min-h-[110px] flex flex-col items-center justify-center">
                                <template x-if="detailData.created_signature">
                                    <img :src="detailData.created_signature" alt="signature" data-sig-fit class="h-20 sm:h-24 w-full object-contain filter drop-shadow-xs p-1">
                                </template>
                                <template x-if="!detailData.created_signature && detailData.can_sign_prepared">
                                    <button type="button"
                                            @click="openSign(detailData.id, 'Requested By (' + detailData.no_mr + ')', detailData.sign_prepared_url, 'POST')"
                                            class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1.5 px-3 rounded shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Tanda Tangani
                                    </button>
                                </template>
                                <template x-if="!detailData.created_signature && !detailData.can_sign_prepared">
                                    <span class="text-[9px] text-gray-400">Created</span>
                                </template>
                            </div>

                            <div class="text-center">
                                <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.created_by"></p>
                                <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-text="detailData.created_at"></p>
                            </div>
                        </div>

                        {{-- Card 2: Approval 1 --}}
                        <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                             :class="detailData.is_rejected_a ? 'bg-red-50/20 border-red-200' : (detailData.approved_a_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : (detailData.can_sign_a ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200'))">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                          :class="detailData.is_rejected_a ? 'bg-red-100 text-red-800' : (detailData.approved_a_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600')">
                                        Review
                                    </span>
                                    <span class="text-[8px] font-medium text-gray-400">Step 2</span>
                                </div>
                                <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Approval 1">Approval 1</h5>
                            </div>

                            <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[96px] sm:min-h-[110px] flex flex-col items-center justify-center">
                                <template x-if="detailData.approved_a_signature">
                                    <img :src="detailData.approved_a_signature" alt="signature" data-sig-fit class="h-20 sm:h-24 w-full object-contain filter drop-shadow-xs p-1">
                                </template>
                                <template x-if="!detailData.approved_a_signature && detailData.is_rejected_a">
                                    <div class="text-center px-1">
                                        <span class="text-[9px] font-bold text-red-600 block">Rejected</span>
                                        <span class="text-[8px] text-red-500 line-clamp-2" x-text="detailData.rejection_a_reason"></span>
                                    </div>
                                </template>
                                <template x-if="!detailData.approved_a_signature && !detailData.is_rejected_a && detailData.can_sign_a">
                                    <button type="button"
                                            @click="openSign(detailData.id, 'Approval 1 (' + detailData.no_mr + ')', detailData.approve_url, 'PATCH')"
                                            class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1.5 px-3 rounded shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Tanda Tangani
                                    </button>
                                </template>
                                <template x-if="!detailData.approved_a_signature && !detailData.is_rejected_a && !detailData.can_sign_a">
                                    <span class="text-[9px] text-gray-400" x-text="detailData.is_approved_a ? 'Approved' : 'Pending'"></span>
                                </template>
                            </div>

                            <div class="text-center">
                                <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.approver_a || 'Approver 1'"></p>
                                <p class="text-[9px] text-emerald-600 font-medium mt-0.5 leading-none" x-show="detailData.approved_a_at" x-text="detailData.approved_a_at"></p>
                                <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-show="!detailData.approved_a_at">Menunggu</p>
                            </div>
                        </div>

                        {{-- Card 3: Approval 2 --}}
                        <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                             :class="detailData.is_rejected_c ? 'bg-red-50/20 border-red-200' : (detailData.approved_c_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : (detailData.can_sign_c ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200'))">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                          :class="detailData.is_rejected_c ? 'bg-red-100 text-red-800' : (detailData.approved_c_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600')">
                                        Approve
                                    </span>
                                    <span class="text-[8px] font-medium text-gray-400">Step 3</span>
                                </div>
                                <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Approval 2">Approval 2</h5>
                            </div>

                            <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[96px] sm:min-h-[110px] flex flex-col items-center justify-center">
                                <template x-if="detailData.approved_c_signature">
                                    <img :src="detailData.approved_c_signature" alt="signature" data-sig-fit class="h-20 sm:h-24 w-full object-contain filter drop-shadow-xs p-1">
                                </template>
                                <template x-if="!detailData.approved_c_signature && detailData.is_rejected_c">
                                    <div class="text-center px-1">
                                        <span class="text-[9px] font-bold text-red-600 block">Rejected</span>
                                        <span class="text-[8px] text-red-500 line-clamp-2" x-text="detailData.rejection_c_reason"></span>
                                    </div>
                                </template>
                                <template x-if="!detailData.approved_c_signature && !detailData.is_rejected_c && !detailData.is_approved_a">
                                    <span class="text-[9px] text-gray-400">Terkunci (Tahap 2)</span>
                                </template>
                                <template x-if="!detailData.approved_c_signature && !detailData.is_rejected_c && detailData.is_approved_a && detailData.can_sign_c">
                                    <button type="button"
                                            @click="openSign(detailData.id, 'Approval 2 (' + detailData.no_mr + ')', detailData.approve_url, 'PATCH')"
                                            class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1.5 px-3 rounded shadow-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Tanda Tangani
                                    </button>
                                </template>
                                <template x-if="!detailData.approved_c_signature && !detailData.is_rejected_c && detailData.is_approved_a && !detailData.can_sign_c">
                                    <span class="text-[9px] text-gray-400" x-text="detailData.is_approved_c ? 'Approved' : 'Pending'"></span>
                                </template>
                            </div>

                            <div class="text-center">
                                <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.approver_c || 'Approver 2'"></p>
                                <p class="text-[9px] text-emerald-600 font-medium mt-0.5 leading-none" x-show="detailData.approved_c_at" x-text="detailData.approved_c_at"></p>
                                <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-show="!detailData.approved_c_at">Menunggu</p>
                            </div>
                        </div>

                        {{-- Card 4: Finance --}}
                        <div class="relative flex flex-col justify-between rounded-xl border p-2.5 transition"
                             :class="detailData.is_rejected_finance ? 'bg-red-50/20 border-red-200' : (detailData.paid_signature ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : (detailData.can_sign_finance ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200'))">
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider"
                                          :class="detailData.is_rejected_finance ? 'bg-red-100 text-red-800' : (detailData.paid_signature ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600')">
                                        Payment
                                    </span>
                                    <span class="text-[8px] font-medium text-gray-400">Step 4</span>
                                </div>
                                <h5 class="text-[11px] font-bold text-gray-800 truncate" title="Finance">Finance</h5>
                            </div>

                            <div class="my-1.5 py-1 border-y border-dashed border-gray-200/80 min-h-[96px] sm:min-h-[110px] flex flex-col items-center justify-center">
                                <template x-if="detailData.paid_signature">
                                    <img :src="detailData.paid_signature" alt="signature" data-sig-fit class="h-20 sm:h-24 w-full object-contain filter drop-shadow-xs p-1">
                                </template>
                                <template x-if="!detailData.paid_signature && detailData.is_rejected_finance">
                                    <div class="text-center px-1">
                                        <span class="text-[9px] font-bold text-red-600 block">Rejected</span>
                                        <span class="text-[8px] text-red-500 line-clamp-2" x-text="detailData.finance_rejection_reason"></span>
                                    </div>
                                </template>
                                <template x-if="!detailData.paid_signature && !detailData.is_rejected_finance && !detailData.is_approved_c">
                                    <span class="text-[9px] text-gray-400">Terkunci (Tahap 3)</span>
                                </template>
                                <template x-if="!detailData.paid_signature && !detailData.is_rejected_finance && detailData.is_approved_c && detailData.can_sign_finance">
                                    <button type="button"
                                            @click="openSign(detailData.id, 'Finance Payment (' + detailData.no_mr + ')', detailData.mark_paid_url, 'PATCH')"
                                            class="inline-flex items-center justify-center gap-1 text-[10px] font-semibold text-white bg-green-600 hover:bg-green-700 active:bg-green-800 py-1 px-2 rounded shadow-xs transition">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        Tanda Tangani
                                    </button>
                                </template>
                                <template x-if="!detailData.paid_signature && !detailData.is_rejected_finance && detailData.is_approved_c && !detailData.can_sign_finance">
                                    <span class="text-[9px] text-gray-400" x-text="detailData.is_paid ? 'Paid' : 'Pending'"></span>
                                </template>
                            </div>

                            <div class="text-center">
                                <p class="text-[10px] font-semibold text-gray-800 truncate" x-text="detailData.paid_by || 'Finance'"></p>
                                <p class="text-[9px] text-emerald-600 font-medium mt-0.5 leading-none" x-show="detailData.paid_at" x-text="detailData.paid_at"></p>
                                <p class="text-[9px] text-gray-400 mt-0.5 leading-none" x-show="!detailData.paid_at">Menunggu</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                    <a :href="detailData.print_url" target="_blank"
                       class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg text-xs font-semibold shadow-xs transition">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.318 2.226c.079.554-.36 1.052-.92 1.052H6.94c-.56 0-.998-.498-.92-1.052L6.34 18m11.318 0h1.093c1.036 0 1.875-.84 1.875-1.875V9.375c0-1.036-.84-1.875-1.875-1.875H4.875C3.839 7.5 3 8.34 3 9.375v6.75c0 1.035.84 1.875 1.875 1.875H6.34m10.94 0H6.34m9.94-11.25V4.875c0-1.036-.84-1.875-1.875-1.875H8.625C7.59 3 6.75 3.84 6.75 4.875v2.625"/>
                        </svg>
                        Cetak / PDF
                    </a>
                    <button type="button"
                            @click="detailOpen = false"
                            class="px-5 py-2 rounded-lg text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                        Tutup
                    </button>
                </div>
            </div>
            </div>
        </div>

        {{-- Modal Reject, butuh alasan penolakan --}}
        <div x-show="rejectOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4"
             style="display: none;">
            <div x-show="rejectOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="rejectOpen = false"
                 class="absolute inset-0 bg-gray-900/50"></div>

            <div x-show="rejectOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 @keydown.escape.window="rejectOpen = false"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

                <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full bg-red-100">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 mb-1 text-center" x-text="rejectTitle"></h3>
                <p class="text-sm text-gray-500 mb-4 text-center" x-text="rejectMessage"></p>

                <label class="block text-xs font-medium text-gray-600 mb-1.5">Alasan Penolakan</label>
                <textarea x-model="rejectReason" rows="3" required
                        placeholder="Tulis alasan mengapa MR ini ditolak..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                <p class="mt-1.5 mb-6 text-xs" :class="rejectReason.trim() ? 'text-gray-400' : 'text-red-500'">
                    <span x-show="!rejectReason.trim()">Alasan penolakan wajib diisi sebelum MR dapat ditolak.</span>
                    <span x-show="rejectReason.trim()" x-cloak>&nbsp;</span>
                </p>

                <div class="flex items-center justify-center gap-3">
                    <button type="button"
                            @click="rejectOpen = false"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitReject()"
                            :disabled="!rejectReason.trim()"
                            :class="rejectReason.trim() ? 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white cursor-pointer' : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition">
                        Ya, Tolak
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal konfirmasi custom, menggantikan confirm() bawaan browser --}}
        <div x-show="confirmOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center px-4"
             style="display: none;">
            <div x-show="confirmOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="confirmOpen = false"
                 class="absolute inset-0 bg-gray-900/50"></div>

            <div x-show="confirmOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 @keydown.escape.window="confirmOpen = false"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">

                <div class="mx-auto mb-4 flex items-center justify-center w-14 h-14 rounded-full"
                     :class="{
                        'bg-green-100': confirmColor === 'green',
                        'bg-red-100': confirmColor === 'red',
                        'bg-indigo-100': confirmColor === 'indigo'
                     }">
                    <svg x-show="confirmIcon === 'check'" class="w-7 h-7"
                         :class="{ 'text-green-600': confirmColor === 'green', 'text-red-600': confirmColor === 'red', 'text-indigo-600': confirmColor === 'indigo' }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="confirmIcon === 'trash'" class="w-7 h-7"
                         :class="{ 'text-green-600': confirmColor === 'green', 'text-red-600': confirmColor === 'red', 'text-indigo-600': confirmColor === 'indigo' }"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-800 mb-1" x-text="confirmTitle"></h3>
                <p class="text-sm text-gray-500 mb-6" x-text="confirmMessage"></p>

                <div class="flex items-center justify-center gap-3">
                    <button type="button"
                            @click="confirmOpen = false"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 active:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="button"
                            @click="submitConfirm()"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-white transition"
                            :class="{
                                'bg-green-600 hover:bg-green-700 active:bg-green-800': confirmColor === 'green',
                                'bg-red-600 hover:bg-red-700 active:bg-red-800': confirmColor === 'red',
                                'bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800': confirmColor === 'indigo'
                            }"
                            x-text="confirmLabel">
                    </button>
                </div>
            </div>
        </div>

        {{-- Digital Signature Modal --}}
        <div x-show="signModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-xs" @click="closeSignModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 sm:p-8">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Tanda Tangan Digital</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Menandatangani sebagai: <strong class="text-indigo-600" x-text="signLabel"></strong></p>
                    </div>
                    <button type="button" x-show="!signRequired" @click="closeSignModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div x-show="signRequired" x-cloak class="flex items-start gap-2 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-lg px-3 py-2.5 mb-4">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>Data Anda telah disimpan. Silakan tanda tangan sebagai Requested By sebelum melanjutkan.</span>
                </div>

                {{-- Dua Pilihan Metode Tanda Tangan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    {{-- Pilihan 1: Upload Gambar --}}
                    <label class="relative flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition select-none"
                           :class="signMode === 'upload' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-950 shadow-xs ring-1 ring-indigo-500/20' : 'border-gray-200 hover:border-gray-300 bg-white text-gray-700'">
                        <input type="radio" name="sign_method_mr_modal" value="upload" x-model="signMode" class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                 :class="signMode === 'upload' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold leading-snug">Pilihan 1: Upload Gambar</p>
                                <p class="text-[11px] text-gray-500 leading-none mt-0.5">Upload file foto / scan TTD</p>
                            </div>
                        </div>
                    </label>

                    {{-- Pilihan 2: Tulis / Coret Manual --}}
                    <label class="relative flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition select-none"
                           :class="signMode === 'draw' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-950 shadow-xs ring-1 ring-indigo-500/20' : 'border-gray-200 hover:border-gray-300 bg-white text-gray-700'">
                        <input type="radio" name="sign_method_mr_modal" value="draw" x-model="signMode" @change="$nextTick(() => initPad())" class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                 :class="signMode === 'draw' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold leading-snug">Pilihan 2: Tulis / Coret Tangan</p>
                                <p class="text-[11px] text-gray-500 leading-none mt-0.5">Tanda tangan langsung di layar</p>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Mode 1: Upload Signature --}}
                <div x-show="signMode === 'upload'" x-cloak class="space-y-3">
                    <div class="border-2 border-dashed border-gray-300 hover:border-indigo-500 rounded-xl p-5 text-center bg-gray-50/50 transition cursor-pointer relative"
                         @click="$refs.sigFileInputMr.click()">
                        <input type="file"
                               x-ref="sigFileInputMr"
                               @change="handleSigFile($event)"
                               accept="image/png,image/jpeg,image/jpg,image/webp"
                               class="hidden">

                        <template x-if="!uploadedSignature">
                            <div class="py-6">
                                <div class="w-12 h-12 mx-auto rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">Klik untuk upload foto / scan tanda tangan</p>
                                <p class="text-xs text-gray-400 mt-1">Format PNG, JPG, JPEG (latar kertas putih otomatis dibuat transparan)</p>
                            </div>
                        </template>

                        <template x-if="uploadedSignature">
                            <div class="py-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pratinjau Tanda Tangan (Siap Digunakan)</p>
                                <div class="max-w-xs mx-auto p-3 bg-white rounded-lg border border-gray-200 shadow-inner flex items-center justify-center min-h-[120px]">
                                    <img :src="uploadedSignature" alt="Signature preview" class="max-h-28 max-w-full object-contain filter drop-shadow-xs">
                                </div>
                                <p class="text-xs text-indigo-600 font-medium mt-2 hover:underline">Klik di sini jika ingin mengganti gambar</p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Mode 2: Draw on Canvas --}}
                <div x-show="signMode === 'draw'" x-cloak class="space-y-2">
                    <div class="relative bg-gray-50/50 rounded-xl border border-gray-200 p-2">
                        <canvas id="mr-signature-canvas" class="w-full h-64 sm:h-72 bg-white rounded-lg touch-none shadow-inner cursor-crosshair"></canvas>
                        <div class="absolute bottom-6 left-6 right-6 border-b border-gray-300 pointer-events-none flex justify-between items-end pb-1">
                            <span class="text-[11px] text-gray-400 font-normal">Tanda tangan di atas garis ini</span>
                            <span class="text-[11px] text-gray-400 font-normal">✕</span>
                        </div>
                    </div>
                </div>

                {{-- Option to remember signature in browser --}}
                <div class="mt-3 flex items-center gap-2">
                    <input type="checkbox" id="remember_sig_mr_modal" x-model="rememberSignature" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 text-xs">
                    <label for="remember_sig_mr_modal" class="text-xs text-gray-600 cursor-pointer select-none">
                        Simpan tanda tangan ini di browser untuk pemakaian berikutnya
                    </label>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 mt-5">
                    <div>
                        <button type="button" x-show="signMode === 'draw'" @click="clearSignPad()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 px-3.5 py-2 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus Tanda Tangan
                        </button>
                        <button type="button" x-show="signMode === 'upload' && uploadedSignature" @click="uploadedSignature = ''" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 px-3.5 py-2 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus Gambar
                        </button>
                    </div>
                    <div class="flex gap-2.5">
                        <button type="button" x-show="!signRequired" @click="closeSignModal()" class="text-xs font-semibold text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="button" @click="submitSignature()"
                                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow-sm transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan & Tanda Tangani
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden shared submit form for the signature --}}
        <form id="mr-sign-form" method="POST" action="">
            @csrf
            <input type="hidden" name="_method" id="mr-sign-method-input" value="PATCH">
            <input type="hidden" name="signature" id="mr-signature-data-input">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</x-app-layout>
