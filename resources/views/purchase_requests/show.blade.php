<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">{{ $purchaseRequest->no_request }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $purchaseRequest->title }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-requests.print', $purchaseRequest) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                    Print
                </a>
                @if($purchaseRequest->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $purchaseRequest->purchaseOrder) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-50 border border-green-200 text-green-700 text-sm font-semibold rounded-lg hover:bg-green-100 transition">
                        Documentation for PO: {{ $purchaseRequest->purchaseOrder->po_no }}
                    </a>
                @endif
                <a href="{{ route('purchase-requests.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="prSignPad()">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition
                     class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm shadow-sm">
                    <span class="flex-1 font-medium">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
                </div>
            @endif

            {{-- Header info --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Request No</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->no_request }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Date</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->date?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Job Location</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->job_location ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Client</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->client ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Job No</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->job_no ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Location Project</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->location_project ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Prepared By</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->creator->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Status</p>
                        <p class="font-semibold text-gray-800">{{ $purchaseRequest->status }}</p>
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
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Line Item</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-center">Qty</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Unit</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Price</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Total Price</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($purchaseRequest->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->line_no }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        {{ $item->line_table ?? '-' }}
                                        @if($item->line_sub_table)
                                            <span class="block text-xs text-gray-400">{{ $item->line_sub_table }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $item->unit }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800">Rp {{ number_format((float) $item->total_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $item->remarks ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 sm:px-8 py-5 border-t border-gray-100 flex flex-col sm:flex-row sm:justify-between gap-6">
                    <div class="max-w-md">
                        <p class="text-xs uppercase tracking-wide text-gray-400 mb-1">Note</p>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ $purchaseRequest->note ?? '-' }}</p>
                    </div>
                    <div class="w-full sm:w-64 space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format((float) $purchaseRequest->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($purchaseRequest->use_ppn)
                            <div class="flex justify-between text-gray-500">
                                <span>PPN {{ rtrim(rtrim(number_format((float) $purchaseRequest->ppn_percent, 2, '.', ''), '0'), '.') }}%</span>
                                <span>Rp {{ number_format((float) $purchaseRequest->ppn_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between pt-1.5 border-t border-gray-200 font-semibold text-gray-800">
                            <span>Grand Total</span>
                            <span>Rp {{ number_format((float) $purchaseRequest->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Approvals --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Approval Workflow</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Sequential digital signature stages for this Purchase Request.</p>
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

                {{-- All approvals displayed side-by-side (sejajar) in a responsive grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($purchaseRequest->approvals as $approval)
                        @php
                            $locked = $purchaseRequest->approvals->where('sort_order', '<', $approval->sort_order)->contains(fn($a) => is_null($a->signed_at));
                            $canSign = auth()->user() && auth()->user()->canSignApproval($approval->role_label);
                            $isSigned = $approval->isSigned();
                        @endphp
                        <div class="relative flex flex-col justify-between rounded-2xl border transition p-5 {{ $isSigned ? 'bg-emerald-50/20 border-emerald-200 ring-1 ring-emerald-200/50' : ($locked ? 'bg-gray-50/50 border-gray-200' : ($canSign ? 'bg-white border-indigo-300 ring-2 ring-indigo-100 shadow-sm' : 'bg-gray-50/30 border-gray-200')) }}">
                            {{-- Top Header: Stage Tag + Stage Number --}}
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $isSigned ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $approval->stage_label }}
                                    </span>
                                    <span class="text-[10px] font-semibold text-gray-400">
                                        Step {{ $approval->sort_order }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-gray-800 leading-snug">
                                    {{ $approval->role_label }}
                                </h4>
                            </div>

                            {{-- Center: Signature / Action Canvas Area (Roomy & spacious) --}}
                            <div class="my-4 py-2 border-y border-dashed border-gray-200 min-h-[110px] flex flex-col items-center justify-center">
                                @if($isSigned)
                                    <div class="w-full flex flex-col items-center justify-center">
                                        <img src="{{ $approval->signature }}" alt="signature" class="h-20 sm:h-22 object-contain filter drop-shadow-sm">
                                    </div>
                                @elseif($locked)
                                    <div class="flex flex-col items-center justify-center text-center text-gray-400 py-3">
                                        <svg class="w-6 h-6 mb-1 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span class="text-xs font-medium">Waiting for Step {{ $approval->sort_order - 1 }}</span>
                                    </div>
                                @elseif(!$canSign)
                                    <div class="flex flex-col items-center justify-center text-center py-3">
                                        <span class="text-xs text-gray-400 mb-1.5">Awaiting approver</span>
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200/70 px-2.5 py-1 rounded-lg">
                                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Approver Only
                                        </span>
                                    </div>
                                @else
                                    <div class="w-full flex flex-col items-center justify-center py-2">
                                        <span class="text-xs text-indigo-600 font-medium mb-2">Ready for signature</span>
                                        <button type="button"
                                                @click="openSign({{ $approval->id }}, {{ Js::from($approval->role_label) }}, {{ Js::from(route('purchase-requests.sign', [$purchaseRequest, $approval])) }})"
                                                class="w-full inline-flex items-center justify-center gap-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 py-2.5 px-3.5 rounded-xl shadow-sm transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                            Sign Document
                                        </button>
                                    </div>
                                @endif
                            </div>

                            {{-- Bottom: Signer info & timestamp --}}
                            <div class="text-center pt-1">
                                @if($isSigned)
                                    <p class="text-xs font-bold text-gray-800 truncate">{{ $approval->signer->name ?? '-' }}</p>
                                    <p class="text-[11px] text-emerald-600 font-medium mt-0.5">{{ $approval->signed_at->format('d-m-Y H:i') }}</p>
                                @else
                                    <p class="text-xs font-medium text-gray-400">Pending</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
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
        function prSignPad() {
            return {
                modalOpen: false,
                approvalLabel: '',
                pad: null,

                openSign(id, label, action) {
                    this.approvalLabel = label;
                    this.modalOpen = true;
                    document.getElementById('sign-form').action = action;

                    this.$nextTick(() => {
                        const canvas = document.getElementById('signature-canvas');
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        canvas.width = canvas.offsetWidth * ratio;
                        canvas.height = canvas.offsetHeight * ratio;
                        canvas.getContext('2d').scale(ratio, ratio);
                        this.pad = new SignaturePad(canvas, {
                            backgroundColor: 'rgb(255,255,255)',
                            minWidth: 1.8,
                            maxWidth: 3.8,
                            penColor: 'rgb(15, 23, 42)'
                        });
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
