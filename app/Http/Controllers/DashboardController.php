<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Rlp;
use App\Models\User;
use App\Models\Vendor;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ==========================================
        // DATA KHUSUS "UNTUK SAYA" (PERSONAL DASHBOARD)
        // ==========================================
        $myPendingApprovals = collect();
        $myRecentSigned = collect();

        if ($user && ($user->isApprover() || ! $user->isAdmin())) {
            $myPendingApprovals = DocumentApproval::where('user_id', $user->id)
                ->whereNull('signed_at')
                ->whereNull('rejected_at')
                ->with('user')
                ->get()
                ->filter(fn ($appr) => $appr->isUnlocked())
                ->sortByDesc(fn ($appr) => $appr->isOverdue() ? 1 : 0)
                ->values();

            $myRecentSigned = DocumentApproval::where('user_id', $user->id)
                ->whereNotNull('signed_at')
                ->with('user')
                ->latest('signed_at')
                ->take(6)
                ->get();
        }

        // Dokumen yang dibuat oleh user saat ini
        $myCreatedDocs = collect();
        $allMyCreatedDocs = collect();
        if ($user) {
            $myMrs = MaterialRequest::with('approvals')->where('created_by', $user->id)->latest('created_at')->take(100)->get();
            $myRlps = Rlp::with('approvals')->where('created_by', $user->id)->latest('created_at')->take(100)->get();
            $myPos = PurchaseOrder::with('approvals')->where('created_by', $user->id)->latest('created_at')->take(100)->get();
            $myPrs = PurchaseRequest::with('approvals')->where('created_by', $user->id)->latest('created_at')->take(100)->get();

            $myCreatedDocs = collect()
                ->concat($myMrs->map(fn ($m) => (object) [
                    'type' => 'MR',
                    'module' => 'Material Request',
                    'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'title' => $m->no_mr ?? ('MR #' . $m->id),
                    'subtitle' => $m->charge_to ?? 'Material Request',
                    'created_at' => $m->created_at,
                    'url' => route('material-requests.show', $m) . '?from=dashboard',
                    'edit_url' => $m->is_rejected ? route('material-requests.edit', $m) : null,
                    'status' => $m->status,
                    'is_rejected' => (bool) $m->is_rejected,
                    'is_overdue' => (bool) ($m->is_overdue ?? false),
                    'rejection_reason' => $m->approvals->first(fn ($a) => $a->isRejected())?->rejection_reason,
                    'signed_count' => $m->approvals->whereNotNull('signed_at')->count(),
                    'total_count' => $m->approvals->count(),
                ]))
                ->concat($myRlps->map(fn ($r) => (object) [
                    'type' => 'RRP',
                    'module' => 'Local Purchase',
                    'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'title' => $r->no_rlp ?? ('RRP #' . $r->id),
                    'subtitle' => 'Local Purchase',
                    'created_at' => $r->created_at,
                    'url' => route('rlps.show', $r) . '?from=dashboard',
                    'edit_url' => $r->is_rejected ? route('rlps.edit', $r) : null,
                    'status' => $r->status,
                    'is_rejected' => (bool) $r->is_rejected,
                    'is_overdue' => (bool) ($r->is_overdue ?? false),
                    'rejection_reason' => $r->approvals->first(fn ($a) => $a->isRejected())?->rejection_reason,
                    'signed_count' => $r->approvals->whereNotNull('signed_at')->count(),
                    'total_count' => $r->approvals->count(),
                ]))
                ->concat($myPos->map(fn ($po) => (object) [
                    'type' => 'PO',
                    'module' => 'Purchase Order',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'title' => $po->po_no ?? ('PO #' . $po->id),
                    'subtitle' => $po->project_description ?? ($po->client ?? 'Purchase Order'),
                    'created_at' => $po->created_at,
                    'url' => route('purchase-orders.show', $po) . '?from=dashboard',
                    'edit_url' => $po->is_rejected ? route('purchase-orders.edit', $po) : null,
                    'status' => $po->status,
                    'is_rejected' => (bool) $po->is_rejected,
                    'is_overdue' => (bool) ($po->is_overdue ?? false),
                    'rejection_reason' => $po->approvals->first(fn ($a) => $a->isRejected())?->rejection_reason,
                    'signed_count' => $po->approvals->whereNotNull('signed_at')->count(),
                    'total_count' => $po->approvals->count(),
                ]))
                ->concat($myPrs->map(fn ($pr) => (object) [
                    'type' => 'PR',
                    'module' => 'Purchase Request',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'title' => $pr->no_request ?? ('PR #' . $pr->id),
                    'subtitle' => $pr->title ?? ($pr->client ?? 'Purchase Request'),
                    'created_at' => $pr->created_at,
                    'url' => route('purchase-requests.show', $pr) . '?from=dashboard',
                    'edit_url' => $pr->is_rejected ? route('purchase-requests.edit', $pr) : null,
                    'status' => $pr->status,
                    'is_rejected' => (bool) $pr->is_rejected,
                    'is_overdue' => (bool) ($pr->is_overdue ?? false),
                    'rejection_reason' => $pr->approvals->first(fn ($a) => $a->isRejected())?->rejection_reason,
                    'signed_count' => $pr->approvals->whereNotNull('signed_at')->count(),
                    'total_count' => $pr->approvals->count(),
                ]))
                ->sortByDesc('created_at')
                ->values();

            // Saring list dokumen untuk dashboard inputer: hanya yang masih aktif/berjalan atau ditolak (Done dikecualikan)
            $allMyCreatedDocs = $myCreatedDocs;
            $myCreatedDocs = $allMyCreatedDocs->filter(fn ($d) => $d->status !== 'Done')->values();
        }

        // Data khusus Super Admin (Pending Setup Approver, Overdue Global, Ditolak Global)
        $adminPendingSetup = collect();
        $adminOverdueList = collect();
        $adminRejectedList = collect();

        if ($user && $user->isAdmin()) {
            $unassignedMrs = MaterialRequest::with(['approvals', 'creator'])->whereDoesntHave('approvals', fn ($q) => $q->where('step', '>', 1))->latest('created_at')->take(50)->get();
            $unassignedRlps = Rlp::with(['approvals', 'creator'])->whereDoesntHave('approvals', fn ($q) => $q->where('step', '>', 1))->latest('created_at')->take(50)->get();
            $unassignedPos = PurchaseOrder::with(['approvals', 'creator'])->whereDoesntHave('approvals', fn ($q) => $q->where('step', '>', 1))->latest('created_at')->take(50)->get();
            $unassignedPrs = PurchaseRequest::with(['approvals', 'creator'])->whereDoesntHave('approvals', fn ($q) => $q->where('step', '>', 1))->latest('created_at')->take(50)->get();

            $adminPendingSetup = collect()
                ->concat($unassignedMrs->map(fn ($m) => (object) [
                    'type' => 'MR',
                    'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'title' => $m->no_mr ?? ('MR #' . $m->id),
                    'creator' => $m->creator->name ?? 'Staff',
                    'created_at' => $m->created_at,
                    'url' => route('material-requests.show', $m) . '?from=dashboard',
                ]))
                ->concat($unassignedRlps->map(fn ($r) => (object) [
                    'type' => 'RRP',
                    'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'title' => $r->no_rlp ?? ('RRP #' . $r->id),
                    'creator' => $r->creator->name ?? 'Staff',
                    'created_at' => $r->created_at,
                    'url' => route('rlps.show', $r) . '?from=dashboard',
                ]))
                ->concat($unassignedPos->map(fn ($po) => (object) [
                    'type' => 'PO',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'title' => $po->po_no ?? ('PO #' . $po->id),
                    'creator' => $po->creator->name ?? 'Staff',
                    'created_at' => $po->created_at,
                    'url' => route('purchase-orders.show', $po) . '?from=dashboard',
                ]))
                ->concat($unassignedPrs->map(fn ($pr) => (object) [
                    'type' => 'PR',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'title' => $pr->no_request ?? ('PR #' . $pr->id),
                    'creator' => $pr->creator->name ?? 'Staff',
                    'created_at' => $pr->created_at,
                    'url' => route('purchase-requests.show', $pr) . '?from=dashboard',
                ]))
                ->sortByDesc('created_at')
                ->values();

            $adminOverdueList = DocumentApproval::whereNull('signed_at')
                ->whereNull('rejected_at')
                ->with('user')
                ->get()
                ->filter(fn ($a) => $a->isOverdue())
                ->take(100)
                ->values();

            $adminRejectedList = DocumentApproval::whereNotNull('rejected_at')
                ->with('user')
                ->latest('rejected_at')
                ->take(6)
                ->get();
        }

        // Metrik angka untuk user yang login
        $myStats = [
            'role_type' => $user?->isAdmin() ? 'admin' : ($user?->isApprover() ? 'approver' : 'input'),
            // Approver
            'pending_signatures_count' => $myPendingApprovals->count(),
            'overdue_signatures_count' => $myPendingApprovals->filter(fn ($a) => $a->isOverdue())->count(),
            'signed_this_month_count' => $user ? DocumentApproval::where('user_id', $user->id)->whereNotNull('signed_at')->whereMonth('signed_at', now()->month)->whereYear('signed_at', now()->year)->count() : 0,
            'total_signed_count' => $user ? DocumentApproval::where('user_id', $user->id)->whereNotNull('signed_at')->count() : 0,
            // Creator / Input
            'my_total_created' => $user ? (MaterialRequest::where('created_by', $user->id)->count()
                + Rlp::where('created_by', $user->id)->count()
                + PurchaseOrder::where('created_by', $user->id)->count()
                + PurchaseRequest::where('created_by', $user->id)->count()) : 0,
            'my_in_progress' => $allMyCreatedDocs->filter(fn ($d) => ! $d->is_rejected && $d->status !== 'Done')->count(),
            'my_rejected' => $allMyCreatedDocs->filter(fn ($d) => $d->is_rejected)->count(),
            'my_done' => $allMyCreatedDocs->filter(fn ($d) => $d->status === 'Done')->count(),
            // Super Admin
            'admin_unassigned_count' => $adminPendingSetup->count(),
            'admin_overdue_count' => $adminOverdueList->count(),
            'admin_rejected_count' => $adminRejectedList->count(),
        ];

        // Total counts for each document type
        $totalMr = MaterialRequest::count();
        $totalRlp = Rlp::count();
        $totalPr = PurchaseRequest::count();
        $totalPo = PurchaseOrder::count();
        $totalVendor = Vendor::count();
        $totalAll = $totalMr + $totalRlp + $totalPr + $totalPo;

        // Growth trends (this month vs last month)
        $mrThisMonth = MaterialRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $mrLastMonth = MaterialRequest::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $mrTrend = $mrLastMonth > 0
            ? round((($mrThisMonth - $mrLastMonth) / $mrLastMonth) * 100)
            : ($mrThisMonth > 0 ? 100 : 0);

        $rlpThisMonth = Rlp::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $rlpLastMonth = Rlp::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $rlpTrend = $rlpLastMonth > 0
            ? round((($rlpThisMonth - $rlpLastMonth) / $rlpLastMonth) * 100)
            : ($rlpThisMonth > 0 ? 100 : 0);

        $prThisMonth = PurchaseRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $prLastMonth = PurchaseRequest::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $prTrend = $prLastMonth > 0
            ? round((($prThisMonth - $prLastMonth) / $prLastMonth) * 100)
            : ($prThisMonth > 0 ? 100 : 0);

        $poThisMonth = PurchaseOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $poLastMonth = PurchaseOrder::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $poTrend = $poLastMonth > 0
            ? round((($poThisMonth - $poLastMonth) / $poLastMonth) * 100)
            : ($poThisMonth > 0 ? 100 : 0);

        $totalThisMonth = $mrThisMonth + $rlpThisMonth + $prThisMonth + $poThisMonth;
        $totalLastMonth = $mrLastMonth + $rlpLastMonth + $prLastMonth + $poLastMonth;
        $totalTrend = $totalLastMonth > 0
            ? round((($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100)
            : ($totalThisMonth > 0 ? 100 : 0);

        // Monthly trends across all 4 procurement modules for the last 6 months
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $trend = $months->map(function ($month) {
            $mr = MaterialRequest::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $rlp = Rlp::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $pr = PurchaseRequest::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            $po = PurchaseOrder::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();

            return [
                'label' => $month->translatedFormat('M'),
                'full_label' => $month->translatedFormat('F Y'),
                'label' => $month->format('M'),
                'full_label' => $month->format('F Y'),
                'mr' => $mr,
                'rlp' => $rlp,
                'pr' => $pr,
                'po' => $po,
                'total' => $mr + $rlp + $pr + $po,
            ];
        });

        // Distribution data for donut chart
        $distribution = [
            'labels' => ['Material Request', 'Local Purchase', 'Purchase Request', 'Purchase Order'],
            'data' => [$totalMr, $totalRlp, $totalPr, $totalPo],
            'colors' => ['#6366f1', '#a855f7', '#f59e0b', '#10b981'],
        ];

        // Recent MRs (for backward compatibility)
        $recentMr = MaterialRequest::latest('created_at')->take(4)->get();

        // Unified recent activities across all procurement modules
        $recentActivities = collect()
            ->concat(MaterialRequest::with('approvals')->latest('created_at')->take(25)->get()->map(function ($m) {
                return (object) [
                    'type' => 'MR',
                    'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'icon_color' => 'text-indigo-600 bg-indigo-50',
                    'title' => $m->no_mr ?? 'MR-' . $m->id,
                    'subtitle' => $m->charge_to ?? 'Material Request',
                    'created_at' => $m->created_at,
                    'url' => route('material-requests.show', $m) . '?from=dashboard',
                    'status' => $m->status ?? 'Active',
                    'is_overdue' => (bool) ($m->is_overdue ?? false),
                ];
            }))
            ->concat((function () {
                $q = Rlp::with('approvals')->latest('created_at');
                if (! auth()->user()->isInput() && ! auth()->user()->isAdmin()) {
                    $q->whereNotNull('created_signature');
                }
                return $q->take(25)->get();
            })()->map(function ($r) {
                return (object) [
                    'type' => 'RLP',
                    'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'icon_color' => 'text-purple-600 bg-purple-50',
                    'title' => $r->no_rlp ?? 'RLP-' . $r->id,
                    'subtitle' => 'Local Purchase',
                    'created_at' => $r->created_at,
                    'url' => route('rlps.show', $r) . '?from=dashboard',
                    'status' => $r->status,
                    'is_overdue' => (bool) ($r->is_overdue ?? false),
                ];
            }))
            ->concat((function () {
                $q = PurchaseRequest::with('approvals')->latest('created_at');
                if (! auth()->user()->isInput() && ! auth()->user()->isAdmin()) {
                    $q->whereHas('approvals', fn ($sub) => $sub->whereNotNull('signed_at'));
                }
                return $q->take(25)->get();
            })()->map(function ($p) {
                return (object) [
                    'type' => 'PR',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'icon_color' => 'text-amber-600 bg-amber-50',
                    'title' => $p->no_request ?? 'PR-' . $p->id,
                    'subtitle' => $p->title ?? ($p->client ?? 'Purchase Request'),
                    'created_at' => $p->created_at,
                    'url' => route('purchase-requests.show', $p) . '?from=dashboard',
                    'status' => $p->status,
                    'is_overdue' => (bool) ($p->is_overdue ?? false),
                ];
            }))
            ->concat((function () {
                $q = PurchaseOrder::with('approvals')->latest('created_at');
                if (! auth()->user()->isInput() && ! auth()->user()->isAdmin()) {
                    $q->whereHas('approvals', fn ($sub) => $sub->whereNotNull('signed_at'));
                }
                return $q->take(25)->get();
            })()->map(function ($po) {
                return (object) [
                    'type' => 'PO',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'icon_color' => 'text-emerald-600 bg-emerald-50',
                    'title' => $po->po_no ?? 'PO-' . $po->id,
                    'subtitle' => $po->project_description ?? ($po->client ?? 'Purchase Order'),
                    'created_at' => $po->created_at,
                    'url' => route('purchase-orders.show', $po) . '?from=dashboard',
                    'status' => $po->status,
                    'is_overdue' => (bool) ($po->is_overdue ?? false),
                ];
            }))
            ->sortByDesc('created_at')
            ->take(100)
            ->values();

        return view('dashboard', compact(
            'totalMr',
            'totalRlp',
            'totalPr',
            'totalPo',
            'totalAll',
            'totalVendor',
            'mrThisMonth',
            'rlpThisMonth',
            'prThisMonth',
            'poThisMonth',
            'mrTrend',
            'rlpTrend',
            'prTrend',
            'poTrend',
            'totalTrend',
            'trend',
            'distribution',
            'recentMr',
            'recentActivities',
            'myPendingApprovals',
            'myRecentSigned',
            'myCreatedDocs',
            'adminPendingSetup',
            'adminOverdueList',
            'adminRejectedList',
            'myStats'
        ));
    }
}

