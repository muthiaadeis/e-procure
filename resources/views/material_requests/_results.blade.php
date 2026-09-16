@if($search)
    <div class="mb-2 flex items-center gap-2 text-sm text-gray-500">
        <span>Showing result for</span>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 font-medium">"{{ $search }}"</span>
        <span>&mdash; {{ $requests->total() }} results found</span>
    </div>
@else
    <p class="text-gray-400 text-xs mb-4">Total {{ $requests->total() }} data</p>
@endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-separate border-spacing-0">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 uppercase text-xs align-middle">
                                <th class="px-4 py-3 rounded-l-lg whitespace-nowrap">No</th>
                                <th class="px-4 py-3 whitespace-nowrap">No MR</th>
                                <th class="px-4 py-3 whitespace-nowrap">Date</th>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[160px]">Charge To</th>
                                <th class="px-4 py-3 whitespace-nowrap min-w-[200px]">Approval</th>
                                <th class="px-4 py-3 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 rounded-r-lg text-center whitespace-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($requests as $req)
                            @php
                                $detailPayload = [
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
                                    'approved_a_at' => $req->approved_a_at ? $req->approved_a_at->format('d-m-Y') : null,
                                    'is_rejected_a' => $req->is_rejected_by_a,
                                    'rejection_a_reason' => $req->rejection_a_reason,
                                    'approver_c' => $req->approverC->name ?? '-',
                                    'is_approved_c' => $req->is_approved_by_c,
                                    'approved_c_at' => $req->approved_c_at ? $req->approved_c_at->format('d-m-Y') : null,
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
                                    'paid_at' => $req->paid_at ? $req->paid_at->format('d-m-Y') : null,
                                    'created_by' => $req->creator->name ?? '-',
                                    'created_at' => $req->created_at ? $req->created_at->format('d-m-Y H:i') : '-',
                                ];
                            @endphp
                            <tr id="mr-row-{{ $req->id }}"
                                @click="openDetail({{ \Illuminate\Support\Js::from($detailPayload) }})"
                                class="cursor-pointer transition {{ request('auto_open') && ($req->id == request('auto_open') || $req->no_mr == request('auto_open')) ? 'bg-indigo-50/80 ring-2 ring-indigo-500/50' : 'hover:bg-gray-50' }}">
                                <td class="px-4 py-3.5 text-gray-500">{{ $requests->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-3.5 font-medium text-gray-800 truncate">{{ $req->no_mr ?? '-' }}</td>
                                <td class="px-4 py-3.5 text-gray-600 whitespace-nowrap">{{ $req->date->format('d-m-Y') }}</td>
                                <td class="px-4 py-3.5 font-medium text-gray-800 truncate">{{ $req->charge_to }}</td>
                                <td class="px-4 py-3.5">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="text-gray-400 font-medium w-14 shrink-0">Appr. 1</span>
                                            @if($req->is_rejected_by_a)
                                                <span class="text-red-600 font-medium whitespace-nowrap">✗ Rejected · {{ $req->rejected_a_at->format('d-m-Y') }}</span>
                                            @elseif($req->is_approved_by_a)
                                                <span class="text-green-600 font-medium truncate">✓ {{ $req->approverA->name ?? '-' }}</span>
                                            @else
                                                <span class="text-gray-400">Pending</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs">
                                            <span class="text-gray-400 font-medium w-14 shrink-0">Appr. 2</span>
                                            @if($req->is_rejected_by_c)
                                                <span class="text-red-600 font-medium whitespace-nowrap">✗ Rejected · {{ $req->rejected_c_at->format('d-m-Y') }}</span>
                                            @elseif($req->is_approved_by_c)
                                                <span class="text-green-600 font-medium truncate">✓ {{ $req->approverC->name ?? '-' }}</span>
                                            @else
                                                <span class="text-gray-400">Pending</span>
                                            @endif
                                        </div>
                                        @if($req->is_rejected_by_finance)
                                            <div class="flex items-center gap-1.5 text-xs">
                                                <span class="text-gray-400 font-medium w-14 shrink-0">Finance</span>
                                                <span class="text-red-600 font-medium truncate">✗ Rejected by {{ $req->financeRejector->name ?? '-' }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $colors = [
                                            'Done' => 'bg-green-100 text-green-700',
                                            'Pending Payment' => 'bg-blue-100 text-blue-700',
                                            'Pending Approval 1' => 'bg-gray-100 text-gray-600',
                                            'Pending Approval 2' => 'bg-yellow-100 text-yellow-700',
                                            'Rejected (Approval 1)' => 'bg-red-100 text-red-700',
                                            'Rejected (Approval 2)' => 'bg-red-100 text-red-700',
                                            'Rejected (Finance)' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $colors[$req->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $req->status }}
                                    </span>
                                    @if($req->is_overdue)
                                        <div class="mt-1 inline-flex items-center gap-1 text-xs text-red-600 font-medium">
                                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                            </svg>
                                            <span>{{ $req->overdue_reason }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5" @click.stop>
                                    @php
                                        $canApproveA = auth()->user()->isApproverA() && ! $req->is_approved_by_a && ! $req->is_rejected_by_a;
                                        $canApproveC = auth()->user()->isApproverC() && $req->is_approved_by_a && ! $req->is_approved_by_c && ! $req->is_rejected_by_c;
                                        $canApprove = $canApproveA || $canApproveC;
                                        $canRejectApproval = $canApprove; // approver yang sama juga bisa reject di tahapnya
                                        $approveLabel = 'Approve';
                                        $canMarkPaid = auth()->user()->isFinance() && $req->is_approved && $req->status !== 'Done' && ! $req->is_rejected_by_finance;
                                        $canRejectFinance = $canMarkPaid;
                                        $canEdit = auth()->user()->isInput() && $req->is_rejected;
                                        $canDelete = auth()->user()->isInput();

                                        // Hitung total aksi yang beneran akan muncul di menu.
                                        // Kalau cuma 1, tampilkan langsung sebagai icon (gak usah titik tiga).
                                        // Kalau 2 atau lebih, baru dikumpulkan jadi dropdown titik tiga.
                                        $actionCount = ($canApprove ? 1 : 0) + ($canRejectApproval ? 1 : 0)
                                            + ($canMarkPaid ? 1 : 0) + ($canRejectFinance ? 1 : 0)
                                            + ($canEdit ? 1 : 0) + ($canDelete ? 1 : 0);
                                        $hasAnyAction = $actionCount > 0;
                                    @endphp
                                    <div class="flex items-center justify-center gap-2" x-data="{ menuOpen: false }">
    <a href="{{ route('material-requests.print', $req->id) }}"
   target="_blank"
   title="Print / PDF"
   class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.318 2.226c.079.554-.36 1.052-.92 1.052H6.94c-.56 0-.998-.498-.92-1.052L6.34 18m11.318 0h1.093c1.036 0 1.875-.84 1.875-1.875V9.375c0-1.036-.84-1.875-1.875-1.875H4.875C3.839 7.5 3 8.34 3 9.375v6.75c0 1.035.84 1.875 1.875 1.875H6.34m10.94 0H6.34m9.94-11.25V4.875c0-1.036-.84-1.875-1.875-1.875H8.625C7.59 3 6.75 3.84 6.75 4.875v2.625"/>
    </svg>
</a>

    @if($actionCount === 1)
        {{-- Cuma satu aksi yang tersedia: langsung jadi icon tunggal, gak usah titik tiga --}}
        @if($canApprove)
            <form id="approve-form-{{ $req->id }}" class="hidden"
                  action="{{ route('material-requests.approve', $req->id) }}" method="POST">
                @csrf
                @method('PATCH')
            </form>
            <button type="button"
                    title="{{ $approveLabel }}"
                    @click="openConfirm(
                        'Approve This MR?',
                        'You are about to approve the MR for {{ addslashes($req->charge_to) }}.',
                        'indigo',
                        'check',
                        'Yes, Approve',
                        'approve-form-{{ $req->id }}'
                    )"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75l2.25 2.25L15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
            </button>
        @elseif($canMarkPaid)
            <form id="mark-paid-form-{{ $req->id }}" class="hidden"
                  action="{{ route('material-requests.mark-paid', $req->id) }}" method="POST">
                @csrf
                @method('PATCH')
            </form>
            <button type="button"
                    title="Mark Paid"
                    @click="openConfirm(
                        'Mark as Paid?',
                        'The MR for {{ addslashes($req->charge_to) }} will be marked as paid.',
                        'green',
                        'check',
                        'Yes, Mark Paid',
                        'mark-paid-form-{{ $req->id }}'
                    )"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
        @elseif($canEdit)
            <a href="{{ route('material-requests.edit', $req->id) }}"
               title="Edit"
               class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
        @elseif($canDelete)
            <form id="delete-form-{{ $req->id }}" class="hidden"
                  action="{{ route('material-requests.destroy', $req->id) }}" method="POST">
                @csrf
                @method('DELETE')
            </form>
            <button type="button"
                    title="Delete"
                    @click="openConfirm(
                        'Delete This Record?',
                        'The MR data for {{ addslashes($req->charge_to) }} will be permanently deleted and cannot be recovered.',
                        'red',
                        'trash',
                        'Yes, Delete',
                        'delete-form-{{ $req->id }}'
                    )"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        @endif
    @elseif($actionCount >= 2)
    <div class="relative" x-data="{
            menuOpen: false,
            menuStyle: '',
            updateMenuPos() {
                const r = $refs['menuBtn{{ $req->id }}'].getBoundingClientRect();
                this.menuStyle = 'top:' + (r.bottom + 6) + 'px; left:' + (r.right - 192) + 'px;';
            }
         }"
         @scroll.window.passive="if (menuOpen) updateMenuPos()"
         @resize.window.passive="if (menuOpen) updateMenuPos()">
        <button type="button"
                title="Menu"
                x-ref="menuBtn{{ $req->id }}"
                @click="updateMenuPos(); menuOpen = !menuOpen;"
                class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 6a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4zm0 8a2 2 0 110-4 2 2 0 010 4z"/>
            </svg>
        </button>

        <template x-teleport="body">
        <div x-show="menuOpen"
             x-cloak
             @click.outside="menuOpen = false"
             x-transition:enter="ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             :style="menuStyle"
             class="fixed w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50 text-left">

            @if($canApprove)
                <form id="approve-form-{{ $req->id }}" class="hidden"
                      action="{{ route('material-requests.approve', $req->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                </form>
                <button type="button"
                        @click="menuOpen = false; openConfirm(
                            'Approve This MR?',
                            'You are about to approve the MR for {{ addslashes($req->charge_to) }}.',
                            'indigo',
                            'check',
                            'Yes, Approve',
                            'approve-form-{{ $req->id }}'
                        )"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75l2.25 2.25L15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                    {{ $approveLabel }}
                </button>
            @endif

            @if($canRejectApproval)
                <form id="reject-approval-form-{{ $req->id }}" class="hidden"
                      action="{{ route('material-requests.reject', $req->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reason" value="">
                </form>
                <button type="button"
                        @click="menuOpen = false; openReject(
                            'Reject This MR?',
                            'You are about to reject the MR for {{ addslashes($req->charge_to) }}. Write the reason below.',
                            'reject-approval-form-{{ $req->id }}'
                        )"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reject
                </button>
            @endif

            @if($canMarkPaid)
                <form id="mark-paid-form-{{ $req->id }}" class="hidden"
                      action="{{ route('material-requests.mark-paid', $req->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                </form>
                <button type="button"
                        @click="menuOpen = false; openConfirm(
                            'Mark as Paid?',
                            'The MR for {{ addslashes($req->charge_to) }} will be marked as paid.',
                            'green',
                            'check',
                            'Yes, Mark Paid',
                            'mark-paid-form-{{ $req->id }}'
                        )"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-green-600 hover:bg-green-50 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Mark Paid
                </button>
            @endif

            @if($canRejectFinance)
                <form id="reject-finance-form-{{ $req->id }}" class="hidden"
                      action="{{ route('material-requests.reject', $req->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="reason" value="">
                </form>
                <button type="button"
                        @click="menuOpen = false; openReject(
                            'Reject Payment For This MR?',
                            'The MR for {{ addslashes($req->charge_to) }} will be rejected by Finance. Write the reason below.',
                            'reject-finance-form-{{ $req->id }}'
                        )"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reject
                </button>
            @endif

            @if(auth()->user()->isInput())
                @if($canApprove || $canRejectApproval || $canMarkPaid || $canRejectFinance)
                    <div class="my-1 border-t border-gray-100"></div>
                @endif

                @if($canEdit)
                    <a href="{{ route('material-requests.edit', $req->id) }}"
                       class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                    <div class="my-1 border-t border-gray-100"></div>
                @endif

                <form id="delete-form-{{ $req->id }}" class="hidden"
                      action="{{ route('material-requests.destroy', $req->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
                <button type="button"
                        @click="menuOpen = false; openConfirm(
                            'Delete This Record?',
                            'The MR data for {{ addslashes($req->charge_to) }} will be permanently deleted and cannot be recovered.',
                            'red',
                            'trash',
                            'Yes, Delete',
                            'delete-form-{{ $req->id }}'
                        )"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            @endif
        </div>
        </template>
    </div>
    @endif
</div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-400">{{ $search ? 'No MRs match your search' : 'No MR data yet' }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
