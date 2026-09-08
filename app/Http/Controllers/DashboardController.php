<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Rlp;
use App\Models\Vendor;

class DashboardController extends Controller
{
    public function index()
    {
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
            ->concat(MaterialRequest::latest('created_at')->take(4)->get()->map(function ($m) {
                return (object) [
                    'type' => 'MR',
                    'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                    'icon_color' => 'text-indigo-600 bg-indigo-50',
                    'title' => $m->no_mr ?? 'MR-' . $m->id,
                    'subtitle' => $m->charge_to ?? 'Material Request',
                    'created_at' => $m->created_at,
                    'url' => route('material-requests.show', $m),
                    'status' => $m->status ?? 'Active',
                    'is_overdue' => (bool) ($m->is_overdue ?? false),
                ];
            }))
            ->concat(Rlp::latest('created_at')->take(4)->get()->map(function ($r) {
                return (object) [
                    'type' => 'RLP',
                    'badge_class' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'icon_color' => 'text-purple-600 bg-purple-50',
                    'title' => $r->no_rlp ?? 'RLP-' . $r->id,
                    'subtitle' => 'Local Purchase',
                    'created_at' => $r->created_at,
                    'url' => route('rlps.index'),
                    'status' => $r->approved_by ? 'Approved' : ($r->reviewed_by ? 'Reviewed' : 'Draft'),
                    'is_overdue' => false,
                ];
            }))
            ->concat(PurchaseRequest::latest('created_at')->take(4)->get()->map(function ($p) {
                return (object) [
                    'type' => 'PR',
                    'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'icon_color' => 'text-amber-600 bg-amber-50',
                    'title' => $p->no_request ?? 'PR-' . $p->id,
                    'subtitle' => $p->title ?? ($p->client ?? 'Purchase Request'),
                    'created_at' => $p->created_at,
                    'url' => route('purchase-requests.show', $p),
                    'status' => 'Pending',
                    'is_overdue' => false,
                ];
            }))
            ->concat(PurchaseOrder::latest('created_at')->take(4)->get()->map(function ($po) {
                return (object) [
                    'type' => 'PO',
                    'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'icon_color' => 'text-emerald-600 bg-emerald-50',
                    'title' => $po->po_no ?? 'PO-' . $po->id,
                    'subtitle' => $po->project_description ?? ($po->client ?? 'Purchase Order'),
                    'created_at' => $po->created_at,
                    'url' => route('purchase-orders.show', $po),
                    'status' => 'Active',
                    'is_overdue' => false,
                ];
            }))
            ->sortByDesc('created_at')
            ->take(6)
            ->values();

        return view('dashboard', compact(
            'totalMr',
            'totalRlp',
            'totalPr',
            'totalPo',
            'totalAll',
            'totalVendor',
            'mrTrend',
            'rlpTrend',
            'prTrend',
            'poTrend',
            'totalTrend',
            'trend',
            'distribution',
            'recentMr',
            'recentActivities'
        ));
    }
}

