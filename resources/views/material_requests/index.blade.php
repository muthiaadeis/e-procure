<x-app-layout>
    <x-slot name="header">
        <h1 class="font-bold text-2xl text-gray-800 leading-tight">Material Request</h1>
        <p class="text-sm text-gray-500 mt-1">Manage and track your material requests.</p>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Material Request (MR)</h1>
                <p class="text-sm text-gray-500 mt-1">Manage and track your material requests.</p>
            </div>
            @if(auth()->user()->isInput())
                <a href="{{ route('material-requests.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add MR
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
            confirmLabel: 'Yes, Continue',
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
                            'approved_a_at' => $autoOpenReq->approved_a_at ? $autoOpenReq->approved_a_at->format('d-m-Y') : null,
                            'is_rejected_a' => $autoOpenReq->is_rejected_by_a,
                            'rejection_a_reason' => $autoOpenReq->rejection_a_reason,
                            'approver_c' => $autoOpenReq->approverC->name ?? '-',
                            'is_approved_c' => $autoOpenReq->is_approved_by_c,
                            'approved_c_at' => $autoOpenReq->approved_c_at ? $autoOpenReq->approved_c_at->format('d-m-Y') : null,
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
                            'paid_at' => $autoOpenReq->paid_at ? $autoOpenReq->paid_at->format('d-m-Y') : null,
                            'created_by' => $autoOpenReq->creator->name ?? '-',
                            'created_at' => $autoOpenReq->created_at ? $autoOpenReq->created_at->format('d-m-Y H:i') : '-',
                        ];
                    @endphp
                    this.$nextTick(() => {
                        this.openDetail({{ \Illuminate\Support\Js::from($autoPayload) }});
                        const targetRow = document.getElementById('mr-row-{{ $autoOpenReq->id }}');
                        if (targetRow) {
                            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                @endif
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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
                               placeholder="Search by MR No. or Charge To..."
                               autocomplete="off"
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm w-64 pl-10 pr-8 py-2">
                        <button type="button"
                                x-show="q"
                                x-cloak
                                @click="clearSearch()"
                                title="Clear search"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="relative w-56" x-data="{ filterOpen: false }" @click.outside="filterOpen = false">
                        @php
                            $filterLabels = [
                                'pending_approval' => 'Pending Approval',
                                'overdue' => 'Overdue',
                                'rejected' => 'Rejected',
                                'done' => 'Done',
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
                    @if(auth()->user()->isInput())
                        <a href="{{ route('material-requests.create') }}"
                           class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add MR
                        </a>
                    @endif
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
                 @click="detailOpen = false"
                 class="fixed inset-0 bg-gray-900/50"></div>

            <div class="relative min-h-full flex items-center justify-center">
            <div x-show="detailOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                 @keydown.escape.window="detailOpen = false"
                 @click.outside="detailOpen = false"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">

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
                    <div class="border border-gray-100 rounded-lg divide-y divide-gray-100 max-h-56 overflow-y-auto">
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

                <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-400 text-xs mb-0.5">Approval 1</p>
                        <p class="text-gray-800 font-medium" x-text="detailData.approver_a"></p>
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
                        <p class="text-gray-800 font-medium" x-text="detailData.approver_c"></p>
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
                        <p class="text-xs text-gray-400 mt-0.5">Awaiting payment</p>
                    </template>
                </div>
            </div>

                <div class="mt-6 pt-4 border-t border-gray-100">
                    <button type="button"
                            @click="detailOpen = false"
                            class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 active:bg-gray-800 transition">
                        Close
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

                <label class="block text-xs font-medium text-gray-600 mb-1.5">Rejection Reason</label>
                <textarea x-model="rejectReason" rows="3" required
                        placeholder="Write the reason this MR is being rejected..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500 text-sm"></textarea>
                <p class="mt-1.5 mb-6 text-xs" :class="rejectReason.trim() ? 'text-gray-400' : 'text-red-500'">
                    <span x-show="!rejectReason.trim()">A reason is required before the MR can be rejected.</span>
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
                        Yes, Reject
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
    </div>
</x-app-layout>
