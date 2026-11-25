@if($search)
    <div class="mb-2 flex items-center gap-2 text-sm text-gray-500">
        <span>Menampilkan hasil pencarian untuk</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-medium">"{{ $search }}"</span>
        <span>&mdash; {{ $requests->total() }} hasil ditemukan</span>
    </div>
@else
    <p class="text-gray-400 text-xs mb-4">Total {{ $requests->total() }} data</p>
@endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-separate border-spacing-0" style="table-layout:fixed;">
                        <colgroup>
                            <col style="width: 5%;">
                            <col style="width: 29%;">
                            <col style="width: 15%;">
                            <col style="width: 20%;">
                            <col style="width: 22%;">
                            <col style="width: 9%;">
                        </colgroup>
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-xs align-middle">
                                <th class="px-3 py-3 rounded-l-lg whitespace-nowrap text-center">No</th>
                                <th class="px-3 py-3 whitespace-nowrap text-left">No MR</th>
                                <th class="px-3 py-3 whitespace-nowrap text-left">Date</th>
                                <th class="px-3 py-3 whitespace-nowrap text-left">Charge To</th>
                                <th class="px-3 py-3 whitespace-nowrap text-center">Status</th>
                                <th class="px-1 py-3 rounded-r-lg text-center whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($requests as $req)
                            @php
                                $detailPayload = [
                                    'id' => $req->id,
                                    'no' => $requests->firstItem() + $loop->index,
                                    'no_mr' => $req->no_mr ?? '-',
                                    'date' => $req->date->format('d-m-Y'),
                                    'charge_to' => $req->charge_to,
                                    'items' => $req->items->map(fn ($item) => [
                                        'description' => $item->description,
                                        'quantity' => $item->quantity,
                                        'unit' => $item->unit,
                                        'remarks' => $item->remarks,
                                    ])->values(),
                                    'approver_a' => $req->approverA->name ?? '-',
                                    'is_approved_a' => $req->is_approved_by_a,
                                    'approved_a_at' => $req->approved_a_at ? $req->approved_a_at->format('d-m-Y H:i') : null,
                                    'is_rejected_a' => $req->is_rejected_by_a,
                                    'rejection_a_reason' => $req->rejection_a_reason,
                                    'approver_c' => $req->approverC->name ?? '-',
                                    'is_approved_c' => $req->is_approved_by_c,
                                    'approved_c_at' => $req->approved_c_at ? $req->approved_c_at->format('d-m-Y H:i') : null,
                                    'is_rejected_c' => $req->is_rejected_by_c,
                                    'rejection_c_reason' => $req->rejection_c_reason,
                                    'is_rejected_finance' => $req->is_rejected_by_finance,
                                    'finance_rejection_reason' => $req->finance_rejection_reason,
                                    'finance_rejector' => $req->financeRejector->name ?? '-',
                                    'status' => $req->status,
                                    'is_overdue' => $req->is_overdue,
                                    'overdue_reason' => $req->overdue_reason,
                                    'is_paid' => ! is_null($req->paid_at),
                                    'paid_by' => $req->paidByUser->name ?? '-',
                                    'paid_at' => $req->paid_at ? $req->paid_at->format('d-m-Y H:i') : null,
                                    'created_by' => $req->creator->name ?? '-',
                                    'created_at' => $req->created_at ? $req->created_at->format('d-m-Y H:i') : '-',
                                    'created_signature' => $req->created_signature,
                                    'approved_a_signature' => $req->approved_a_signature,
                                    'approved_c_signature' => $req->approved_c_signature,
                                    'paid_signature' => $req->paid_signature,
                                    'can_sign_a' => (auth()->user()->isApproverA() || auth()->user()->isAdmin()) && $req->created_signature && ! $req->is_approved_by_a && ! $req->is_rejected_by_a,
                                    'can_sign_c' => (auth()->user()->isApproverC() || auth()->user()->isAdmin()) && $req->is_approved_by_a && ! $req->is_approved_by_c && ! $req->is_rejected_by_c,
                                    'can_sign_finance' => (auth()->user()->isFinance() || auth()->user()->isAdmin()) && $req->is_approved && ! $req->paid_at && ! $req->is_rejected_by_finance,
                                    'can_sign_prepared' => (auth()->user()->id === $req->created_by || auth()->user()->isAdmin()) && ! $req->created_signature,
                                    'approve_url' => route('material-requests.approve', $req),
                                    'mark_paid_url' => route('material-requests.mark-paid', $req),
                                    'sign_prepared_url' => route('material-requests.sign-prepared', $req),
                                    'print_url' => route('material-requests.print', $req),
                                ];
                            @endphp
                            <tr id="mr-row-{{ $req->id }}" style="height:64px"
                                onclick="window.location.href = '{{ route('material-requests.show', $req) }}'"
                                data-href="{{ route('material-requests.show', $req) }}"
                                class="cursor-pointer transition hover:bg-gray-50/80">
                                <td class="px-3 py-3.5 text-center text-gray-500">{{ $requests->firstItem() + $loop->index }}</td>
                                <td class="px-3 py-3.5 font-medium text-gray-800 truncate" title="{{ $req->no_mr }}">{{ $req->no_mr ?? '-' }}</td>
                                <td class="px-3 py-3.5 text-gray-600 whitespace-nowrap">{{ $req->date->format('d-m-Y') }}</td>
                                <td class="px-3 py-3.5 font-medium text-gray-800 text-left">
                                    <div title="{{ $req->charge_to }}" class="truncate">{{ $req->charge_to ?? '-' }}</div>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @php
                                        $signedCount = $req->approvals->whereNotNull('signed_at')->count();
                                        $totalCount = $req->approvals->count();
                                        $colors = [
                                            'Done' => 'bg-green-100 text-green-700',
                                            'Pending Setup' => 'bg-amber-100 text-amber-800',
                                            'Pending Payment' => 'bg-blue-100 text-blue-700',
                                            'Pending Approval 1' => 'bg-gray-100 text-gray-600',
                                            'Pending Approval 2' => 'bg-yellow-100 text-yellow-700',
                                            'Rejected' => 'bg-red-100 text-red-700',
                                            'Rejected (Approval 1)' => 'bg-red-100 text-red-700',
                                            'Rejected (Approval 2)' => 'bg-red-100 text-red-700',
                                            'Rejected (Finance)' => 'bg-red-100 text-red-700',
                                        ];
                                        $statusColor = $colors[$req->status] ?? (str_starts_with($req->status, 'Rejected') ? 'bg-red-100 text-red-700' : (str_starts_with($req->status, 'Pending') ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600'));
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap {{ $statusColor }}"
                                          @if($req->is_overdue) title="{{ $req->overdue_reason }}" @endif>
                                        {{ $req->status }}@if($totalCount > 1 && $req->status !== 'Done' && $req->status !== 'Pending Setup' && !str_starts_with($req->status, 'Rejected')) ({{ $signedCount }}/{{ $totalCount }})@endif
                                    </span>
                                </td>
                                <td class="px-2 py-3.5" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('material-requests.show', $req) }}" title="Lihat Detail"
                                           class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('material-requests.print', $req) }}" target="_blank" title="Cetak / PDF"
                                           class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z"/>
                                            </svg>
                                        </a>
                                        @if(auth()->user()->isInput() || auth()->user()->isAdmin())
                                            @if($req->is_rejected)
                                                <a href="{{ route('material-requests.edit', $req) }}" title="Edit & Ajukan Ulang"
                                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-amber-600 hover:text-amber-700 hover:bg-amber-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-400">{{ $search ? 'No MRs match your search' : 'No MR data yet' }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
