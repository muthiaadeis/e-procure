<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">{{ $purchaseOrder->po_no }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $purchaseOrder->subject ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition">
                    Print
                </a>
                @if($purchaseOrder->is_draft)
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition shadow-sm">
                        Edit
                    </a>
                @endif
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
                <h3 class="text-sm font-semibold text-gray-800 mb-5">Approval</h3>

                @foreach($purchaseOrder->approvals->groupBy('sort_order') as $stageApprovals)
                    <div class="mb-6 last:mb-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">{{ $stageApprovals->first()->stage_label }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach($stageApprovals as $approval)
                                @php
                                    $locked = $purchaseOrder->approvals->where('sort_order', '<', $approval->sort_order)->contains(fn($a) => is_null($a->signed_at));
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
                                    @else
                                        <div class="h-16 flex items-center justify-center text-gray-300 text-xs">Not signed yet</div>
                                        <button type="button"
                                                @click="openSign({{ $approval->id }}, {{ Js::from($approval->role_label) }}, {{ Js::from(route('purchase-orders.sign', [$purchaseOrder, $approval])) }})"
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

            {{-- Vendor Acceptance (diisi/ditandatangani manual oleh supplier di dokumen cetak) --}}
            <div class="bg-white shadow-sm rounded-2xl p-6 sm:p-8">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Vendor Acceptance</h3>
                <p class="text-xs text-gray-400">This section is signed manually by the supplier on the printed document.</p>
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
