<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use Illuminate\Http\Request;

class MaterialRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = MaterialRequest::with(['items', 'approverA', 'approverC', 'rejectorA', 'rejectorC', 'financeRejector', 'paidByUser', 'creator']);

        $filter = $request->query('filter');
        $search = trim((string) $request->query('search'));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('no_mr', 'like', "%{$search}%")
                    ->orWhere('charge_to', 'like', "%{$search}%");
            });
        }

        if ($filter === 'rejected') {
            $query->where(function ($q) {
                $q->whereNotNull('rejected_a_at')
                    ->orWhereNotNull('rejected_c_at')
                    ->orWhereNotNull('finance_rejected_at');
            });
        } elseif ($filter === 'pending_approval') {
            $query->whereNull('rejected_a_at')
                ->whereNull('rejected_c_at')
                ->whereNull('finance_rejected_at')
                ->where(function ($q) {
                    $q->whereNull('approved_a_at')
                        ->orWhereNull('approved_c_at');
                });
        } elseif ($filter === 'overdue') {
            $deadlineA = now()->subDays(MaterialRequest::APPROVAL_1_DEADLINE_DAYS);
            $deadlineC = now()->subDays(MaterialRequest::APPROVAL_2_DEADLINE_DAYS);
            $deadlineD = now()->subDays(MaterialRequest::PAYMENT_DEADLINE_DAYS);

            $query->where(function ($q) use ($deadlineA, $deadlineC, $deadlineD) {
                $q->where(function ($q1) use ($deadlineA) {
                    $q1->whereNull('approved_a_at')
                        ->whereNull('rejected_a_at')
                        ->whereDate('date', '<=', $deadlineA);
                })
                ->orWhere(function ($q2) use ($deadlineC) {
                    $q2->whereNotNull('approved_a_at')
                        ->whereNull('approved_c_at')
                        ->whereNull('rejected_c_at')
                        ->where('approved_a_at', '<=', $deadlineC);
                })
                ->orWhere(function ($q3) use ($deadlineD) {
                    $q3->whereNotNull('approved_a_at')
                        ->whereNotNull('approved_c_at')
                        ->whereNull('finance_rejected_at')
                        ->whereNull('paid_at')
                        ->where('approved_c_at', '<=', $deadlineD);
                });
            });
        } elseif ($filter === 'done') {
            $query->whereNull('rejected_a_at')
                ->whereNull('rejected_c_at')
                ->whereNull('finance_rejected_at')
                ->whereNotNull('approved_a_at')
                ->whereNotNull('approved_c_at')
                ->whereNotNull('paid_at');
        }

        $requests = $query->latest('date')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('material_requests._results', compact('requests', 'search'));
        }

        return view('material_requests.index', compact('requests', 'search'));
    }

    public function show(Request $request, MaterialRequest $materialRequest)
    {
        $params = [
            'search' => $materialRequest->no_mr,
            'auto_open' => $materialRequest->id,
        ];

        if ($request->filled('filter')) {
            $params['filter'] = $request->query('filter');
        }

        return redirect()->route('material-requests.index', $params);
    }

    public function create()
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to add a Material Request.");

        // No MR is auto-generated and shown read-only on the form.
        $nextNoMr = MaterialRequest::generateNextNoMr();

        return view('material_requests.create', compact('nextNoMr'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to add a Material Request.");

        $validated = $request->validate([
            'charge_to' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.remarks' => 'required|string',
        ]);

        // No MR is always generated on the server, never trusted from client
        // input, so it can't be edited/tampered with via the form.
        $materialRequest = MaterialRequest::create([
            'no_mr' => MaterialRequest::generateNextNoMr(),
            'date' => now(),
            'charge_to' => $validated['charge_to'],
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            $materialRequest->items()->create($item);
        }

        return redirect()->route('material-requests.index')
            ->with('success', 'Material Request added successfully.');
    }

    public function edit(MaterialRequest $materialRequest)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to edit this Material Request.");
        abort_unless($materialRequest->is_rejected, 403, "MR can only be edited after it's rejected.");

        $materialRequest->load('items');

        return view('material_requests.edit', compact('materialRequest'));
    }

    public function update(Request $request, MaterialRequest $materialRequest)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to edit this Material Request.");
        abort_unless($materialRequest->is_rejected, 403, "MR can only be edited after it's rejected.");

        $validated = $request->validate([
            'charge_to' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.remarks' => 'required|string',
        ]);

        $wasRejected = $materialRequest->is_rejected;

        // No MR is permanent once created; the date isn't changed on update —
        // both stay as whatever they were when this MR was first created.
        $updateData = [
            'charge_to' => $validated['charge_to'],
        ];

        if ($wasRejected) {
            $updateData = array_merge($updateData, [
                'approved_a_by' => null,
                'approved_a_at' => null,
                'rejected_a_by' => null,
                'rejected_a_at' => null,
                'rejection_a_reason' => null,
                'approved_c_by' => null,
                'approved_c_at' => null,
                'rejected_c_by' => null,
                'rejected_c_at' => null,
                'rejection_c_reason' => null,
                'paid_by' => null,
                'paid_at' => null,
                'finance_rejected_by' => null,
                'finance_rejected_at' => null,
                'finance_rejection_reason' => null,
            ]);
        }

        $materialRequest->update($updateData);

        $materialRequest->items()->delete();
        foreach ($validated['items'] as $item) {
            $materialRequest->items()->create($item);
        }

        return redirect()->route('material-requests.index')
            ->with('success', $wasRejected
                ? 'MR updated successfully and resubmitted for approval from the start.'
                : 'Material Request updated successfully.');
    }

    public function destroy(MaterialRequest $materialRequest)
    {
        abort_unless(auth()->user()->isInput(), 403, "You don't have permission to delete this Material Request.");

        $materialRequest->delete();

        return redirect()->route('material-requests.index')
            ->with('success', 'Material Request deleted successfully.');
    }

    public function approve(MaterialRequest $materialRequest)
    {
        $user = auth()->user();

        if ($user->isApproverA()) {
            abort_if($materialRequest->is_approved_by_a, 403, 'This MR has already been approved at Approval 1.');
            abort_if($materialRequest->is_rejected_by_a, 403, 'This MR has already been rejected at Approval 1.');

            $materialRequest->update([
                'approved_a_by' => $user->id,
                'approved_a_at' => now(),
            ]);

            return redirect()->route('material-requests.index')
                ->with('success', 'MR approved successfully (Approval 1).');
        }

        if ($user->isApproverC()) {
            abort_unless($materialRequest->is_approved_by_a, 403, "This MR hasn't been approved at Approval 1 yet.");
            abort_if($materialRequest->is_approved_by_c, 403, 'This MR has already been approved at Approval 2.');
            abort_if($materialRequest->is_rejected_by_c, 403, 'This MR has already been rejected at Approval 2.');

            $materialRequest->update([
                'approved_c_by' => $user->id,
                'approved_c_at' => now(),
            ]);

            return redirect()->route('material-requests.index')
                ->with('success', 'MR approved successfully (Approval 2).');
        }

        abort(403, "You don't have permission to approve.");
    }

    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($user->isApproverA()) {
            abort_if($materialRequest->is_approved_by_a, 403, "This MR has already been approved at Approval 1 and can't be rejected.");
            abort_if($materialRequest->is_rejected_by_a, 403, 'This MR has already been rejected at Approval 1.');

            $materialRequest->update([
                'rejected_a_by' => $user->id,
                'rejected_a_at' => now(),
                'rejection_a_reason' => $validated['reason'],
            ]);

            return redirect()->route('material-requests.index')
                ->with('success', 'MR rejected successfully.');
        }

        if ($user->isApproverC()) {
            abort_unless($materialRequest->is_approved_by_a, 403, "This MR hasn't been approved at Approval 1 yet.");
            abort_if($materialRequest->is_approved_by_c, 403, "This MR has already been approved at Approval 2 and can't be rejected.");
            abort_if($materialRequest->is_rejected_by_c, 403, 'This MR has already been rejected at Approval 2.');

            $materialRequest->update([
                'rejected_c_by' => $user->id,
                'rejected_c_at' => now(),
                'rejection_c_reason' => $validated['reason'],
            ]);

            return redirect()->route('material-requests.index')
                ->with('success', 'MR rejected successfully.');
        }

        if ($user->isFinance()) {
            abort_unless($materialRequest->is_approved, 403, "This MR hasn't finished the approval process yet.");
            abort_if($materialRequest->paid_at, 403, "This MR is already Done and can't be rejected.");
            abort_if($materialRequest->is_rejected_by_finance, 403, 'This MR has already been rejected by Finance.');

            $materialRequest->update([
                'finance_rejected_by' => $user->id,
                'finance_rejected_at' => now(),
                'finance_rejection_reason' => $validated['reason'],
            ]);

            return redirect()->route('material-requests.index')
                ->with('success', 'MR rejected successfully.');
        }

        abort(403, "You don't have permission to reject this MR.");
    }

    public function markPaid(MaterialRequest $materialRequest)
    {
        $user = auth()->user();

        abort_unless($user->isFinance(), 403, "You don't have permission to change the payment status.");
        abort_unless($materialRequest->is_approved, 403, "This MR hasn't finished the approval process yet.");
        abort_if($materialRequest->is_rejected_by_finance, 403, 'This MR has already been rejected by Finance.');

        $materialRequest->update([
            'paid_by' => $user->id,
            'paid_at' => now(),
        ]);

        return redirect()->route('material-requests.index')
            ->with('success', 'Status changed to Done successfully.');
    }

    // Printable A4 view of a single MR. Opens in a new tab; the user hits
    // "Save as PDF" from the browser's print dialog (window.print()), so no
    // extra PDF library/dependency is required on the server.
    public function printPdf(MaterialRequest $materialRequest)
    {
        $materialRequest->load(['items', 'approverA', 'approverC', 'creator']);

        return view('material_requests.print', compact('materialRequest'));
    }
}
