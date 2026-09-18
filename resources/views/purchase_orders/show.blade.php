<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">{{ $purchaseOrder->po_no }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $purchaseOrder->subject ?? '-' }}
                    @if($purchaseOrder->rlp)
                        · from RRP
                        <a href="{{ route('rlps.index') }}" class="text-indigo-600 hover:underline font-medium">
                            {{ $purchaseOrder->rlp->no_rlp }}
                        </a>
                    @else
                        · <span class="text-gray-400">Direct Order</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if($purchaseOrder->purchaseRequest)
                    <a href="{{ route('purchase-requests.show', $purchaseOrder->purchaseRequest) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 border border-amber-200 text-amber-800 text-sm font-semibold rounded-lg hover:bg-amber-100 transition shadow-xs">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        PR Doc ({{ $purchaseOrder->purchaseRequest->no_request }})
                    </a>
                @else
                    <a href="{{ route('purchase-requests.create', ['from_po' => $purchaseOrder->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-600 text-white text-sm font-semibold rounded-lg hover:bg-amber-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Generate PR Documentation
                    </a>
                @endif
                <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                    Print
                </a>
                <a href="{{ route('purchase-orders.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="poSignPad()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                </div>
            @endif

            {{-- Header info --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Purchase Order No</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->po_no }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Our Reference</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->our_reference ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Supplier No</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->supplier_no ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Our Order Date</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->our_order_date?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Revision</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->revision ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Client</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->client ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Prepared By</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->creator->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Status</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseOrder->status }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">To</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->to_address ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-2">Attn</p>
                        <p class="text-sm text-gray-700">{{ $purchaseOrder->attn ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Invoice Address</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->invoice_address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Subject</p>
                        <p class="text-sm text-gray-700">{{ $purchaseOrder->subject ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-2">Project Description</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->project_description ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-2">Contact Number</p>
                        <p class="text-sm text-gray-700">{{ $purchaseOrder->contact_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Final Delivery Address</p>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $purchaseOrder->final_delivery_address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="bg-white shadow-sm rounded-2xl overflow-hidden">
                <div class="px-6 sm:px-8 pt-6 pb-2">
                    <h3 class="text-sm font-semibold text-gray-800">Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[860px]">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">No</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Qty</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">UOM</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Brand</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Unit Price</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Total Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchaseOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->line_no }}</td>
                                    <td class="px-4 py-3 text-gray-700 whitespace-pre-line">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->uom }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->brand ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format((float) $item->total_price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 sm:px-8 py-5 border-t border-gray-100 flex justify-end">
                    <div class="w-full sm:w-64 space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Total</span>
                            <span>Rp {{ number_format((float) $purchaseOrder->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($purchaseOrder->use_ppn)
                            <div class="flex justify-between text-gray-500">
                                <span>PPN {{ rtrim(rtrim(number_format((float) $purchaseOrder->ppn_percent, 2, '.', ''), '0'), '.') }}%</span>
                                <span>Rp {{ number_format((float) $purchaseOrder->ppn_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-1.5 border-t border-gray-200 font-semibold text-gray-800">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format((float) $purchaseOrder->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approvals --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Approval Workflow</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Sequential digital signature stages for this Purchase Order.</p>
                    </div>
                    @if(!auth()->user()->canSignApproval())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/70">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Read-only: digital signatures require an authorized Approver account
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200/70">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Approver Account Active
                        </span>
                    @endif
                </div>

                {{-- All approvals displayed side-by-side in a compact responsive grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-3.5">
                    @foreach($purchaseOrder->approvals as $approval)
                        @php
                            $locked = $purchaseOrder->approvals->where('sort_order', '<', $approval->sort_order)->contains(fn($a) => is_null($a->signed_at));
                            $canSign = auth()->user() && auth()->user()->canSignApproval($approval->role_label);
                            $isSigned = $approval->isSigned();
                        @endphp
                        <div class="relative flex flex-col justify-between rounded-xl border transition p-3.5 {{ $isSigned ? 'bg-emerald-50/15 border-emerald-200 ring-1 ring-emerald-200/40' : ($locked ? 'bg-gray-50/40 border-gray-200' : ($canSign ? 'bg-white border-indigo-300 ring-1 ring-indigo-200 shadow-xs' : 'bg-gray-50/20 border-gray-200')) }}">
                            {{-- Top Header: Stage Tag + Stage Number --}}
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider {{ $isSigned ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $approval->stage_label }}
                                    </span>
                                    <span class="text-[9px] font-medium text-gray-400">
                                        Step {{ $approval->sort_order }}
                                    </span>
                                </div>
                                <h4 class="text-xs font-bold text-gray-800 truncate" title="{{ $approval->role_label }}">
                                    {{ $approval->role_label }}
                                </h4>
                            </div>

                            {{-- Center: Signature / Action Canvas Area (Compact) --}}
                            <div class="my-2 py-1.5 border-y border-dashed border-gray-200/80 min-h-[72px] flex flex-col items-center justify-center">
                                @if($isSigned)
                                    <div class="w-full flex items-center justify-center">
                                        <img src="{{ $approval->signature }}" alt="signature" class="h-12 sm:h-14 object-contain filter drop-shadow-xs">
                                    </div>
                                @elseif($locked)
                                    <div class="flex flex-col items-center justify-center text-center text-gray-400 py-1">
                                        <svg class="w-4 h-4 mb-0.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span class="text-[10px] text-gray-400">Locked (Step {{ $approval->sort_order - 1 }})</span>
                                    </div>
                                @elseif(!$canSign)
                                    <div class="flex flex-col items-center justify-center text-center py-1">
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-amber-700 bg-amber-50 border border-amber-200/70 px-2 py-0.5 rounded-md">
                                            <svg class="w-2.5 h-2.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Approver Only
                                        </span>
                                    </div>
                                @else
                                    <div class="w-full flex flex-col items-center justify-center py-1">
                                        <button type="button"
                                                @click="openSign({{ $approval->id }}, {{ Js::from($approval->role_label) }}, {{ Js::from(route('purchase-orders.sign', [$purchaseOrder, $approval])) }})"
                                                class="w-full inline-flex items-center justify-center gap-1 text-[11px] font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-1.5 px-2.5 rounded-lg shadow-xs transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Sign
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- Bottom: Signer info & timestamp --}}
                            <div class="text-center">
                                @if($isSigned)
                                    <p class="text-[11px] font-semibold text-gray-800 truncate" title="{{ $approval->signer->name }}">{{ $approval->signer->name ?? '-' }}</p>
                                    <p class="text-[10px] text-emerald-600 font-medium leading-none mt-0.5">{{ $approval->signed_at->format('d-m-Y H:i') }}</p>
                                @else
                                    <p class="text-[10px] text-gray-400 leading-none">Pending</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Vendor Acceptance (diisi/ditandatangani manual oleh supplier di dokumen cetak) --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Vendor Acceptance</h3>
                <p class="text-xs text-gray-400">This section is signed manually by the supplier on the printed document.</p>
            </div>
        </div>

        {{-- Signature modal --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
            <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-xs" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-6 sm:p-8">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Digital Signature Pad</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Signing as: <strong class="text-indigo-600" x-text="approvalLabel"></strong></p>
                    </div>
                    <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Spacious Canvas --}}
                <div class="relative bg-gray-50/50 rounded-xl border border-gray-200 p-2">
                    <canvas id="signature-canvas" class="w-full h-72 sm:h-80 bg-white rounded-lg touch-none shadow-inner cursor-crosshair"></canvas>
                    <div class="absolute bottom-6 left-6 right-6 border-b border-gray-300 pointer-events-none flex justify-between items-end pb-1">
                        <span class="text-[11px] text-gray-400 font-normal">Sign above this line</span>
                        <span class="text-[11px] text-gray-400 font-normal">✕</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 mt-5">
                    <button type="button" @click="clearPad()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 px-3.5 py-2 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear Signature
                    </button>
                    <div class="flex gap-2.5">
                        <button type="button" @click="closeModal()" class="text-xs font-semibold text-gray-600 hover:text-gray-800 px-4 py-2 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="button" @click="submitSignature()"
                                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow-sm transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save & Sign Document
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden shared submit form for the signature --}}
        <form id="sign-form" method="POST" action="">
            @csrf
            <input type="hidden" name="signature" id="signature-data-input">
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        function poSignPad() {
            return {
                modalOpen: false,
                approvalLabel: '',
                pad: null,

                openSign(id, label, action) {
                    this.approvalLabel = label;
                    this.modalOpen = true;
                    document.getElementById('sign-form').action = action;

                    this.$nextTick(() => {
                        const setupPad = () => {
                            const canvas = document.getElementById('signature-canvas');
                            if (!canvas) return;
                            if (!canvas.offsetWidth || !canvas.offsetHeight) {
                                requestAnimationFrame(setupPad);
                                return;
                            }
                            if (this.pad) {
                                this.pad.off();
                                this.pad = null;
                            }
                            const ratio = Math.max(window.devicePixelRatio || 1, 1);
                            canvas.width = canvas.offsetWidth * ratio;
                            canvas.height = canvas.offsetHeight * ratio;
                            canvas.getContext('2d').scale(ratio, ratio);
                            if (window.SignaturePad) {
                                this.pad = new SignaturePad(canvas, {
                                    backgroundColor: 'rgb(255,255,255)',
                                    minWidth: 1.8,
                                    maxWidth: 3.8,
                                    penColor: 'rgb(15, 23, 42)'
                                });
                            }
                        };
                        setupPad();
                    });
                },

                clearPad() {
                    if (this.pad) this.pad.clear();
                },

                closeModal() {
                    this.modalOpen = false;
                    this.pad = null;
                },

                submitSignature() {
                    if (!this.pad || this.pad.isEmpty()) {
                        alert('Please sign in the box first.');
                        return;
                    }
                    document.getElementById('signature-data-input').value = this.pad.toDataURL('image/png');
                    document.getElementById('sign-form').submit();
                },
            };
        }
    </script>
</x-app-layout>
