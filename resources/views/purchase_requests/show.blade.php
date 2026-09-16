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
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    <h3 class="text-sm font-semibold text-gray-800">Approval</h3>
                    @if(!auth()->user()->canSignApproval())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/70">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Read-only: digital signatures require an authorized Approver account
                        </span>
                    @endif
                </div>

                @foreach($purchaseRequest->approvals->groupBy('sort_order') as $stageApprovals)
                    <div class="mb-6 last:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">{{ $stageApprovals->first()->stage_label }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($stageApprovals as $approval)
                                @php
                                    $locked = $purchaseRequest->approvals->where('sort_order', '<', $approval->sort_order)->contains(fn($a) => is_null($a->signed_at));
                                    $canSign = auth()->user() && auth()->user()->canSignApproval($approval->role_label);
                                @endphp
                                <div class="border border-gray-200 rounded-xl p-4 text-center flex flex-col items-center">
                                    <p class="text-xs text-gray-500 mb-2">{{ $approval->role_label }}</p>

                                    @if($approval->isSigned())
                                        <img src="{{ $approval->signature }}" alt="signature" class="h-16 object-contain mb-1">
                                        <p class="text-sm font-semibold text-gray-800">{{ $approval->signer->name ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ $approval->signed_at->format('d-m-Y H:i') }}</p>
                                    @elseif($locked)
                                        <div class="h-16 flex items-center justify-center text-gray-300 text-xs">Waiting for previous stage</div>
                                        <button type="button" disabled
                                                class="mt-2 text-xs font-semibold text-gray-300 border border-gray-200 px-3 py-1.5 rounded-lg cursor-not-allowed">
                                            Sign
                                        </button>
                                    @elseif(!$canSign)
                                        <div class="h-16 flex items-center justify-center text-gray-400 text-xs">Awaiting approver signature</div>
                                        <span class="mt-2 inline-flex items-center gap-1 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200/70 px-2.5 py-1 rounded-lg">
                                            <svg class="w-3 h-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Approver Only
                                        </span>
                                    @else
                                        <div class="h-16 flex items-center justify-center text-gray-300 text-xs">Not signed yet</div>
                                        <button type="button"
                                                @click="openSign({{ $approval->id }}, {{ Js::from($approval->role_label) }}, {{ Js::from(route('purchase-requests.sign', [$purchaseRequest, $approval])) }})"
                                                class="mt-2 text-xs font-semibold text-indigo-600 border border-indigo-200 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                                            Sign
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Signature modal --}}
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/40" @click="closeModal()"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
                <h3 class="font-semibold text-gray-800 mb-1">Digital Signature</h3>
                <p class="text-sm text-gray-500 mb-4" x-text="approvalLabel"></p>

                <canvas id="signature-canvas" class="w-full h-48 border border-dashed border-gray-300 rounded-lg touch-none"></canvas>

                <div class="flex items-center justify-between mt-4">
                    <button type="button" @click="clearPad()" class="text-sm font-semibold text-gray-500 hover:text-gray-700">
                        Clear
                    </button>
                    <div class="flex gap-3">
                        <button type="button" @click="closeModal()" class="text-sm font-semibold text-gray-500 hover:text-gray-700">
                            Cancel
                        </button>
                        <button type="button" @click="submitSignature()"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                            Sign Document
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
                        this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });
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
