<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use App\Models\Rlp;
use App\Models\Vendor;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMr = MaterialRequest::count();
        $totalRlp = Rlp::count();
        $totalVendor = Vendor::count();

        $mrThisMonth = MaterialRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $mrLastMonth = MaterialRequest::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $mrTrend = $mrLastMonth > 0
            ? round((($mrThisMonth - $mrLastMonth) / $mrLastMonth) * 100)
            : ($mrThisMonth > 0 ? 100 : 0);

        // Tren pengadaan 6 bulan terakhir (jumlah MR per bulan)
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));
        $trend = $months->map(function ($month) {
            return [
                'label' => $month->translatedFormat('M'),
                'total' => MaterialRequest::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        });

        $recentMr = MaterialRequest::latest('created_at')->take(4)->get();

        return view('dashboard', compact('totalMr', 'totalRlp', 'totalVendor', 'mrTrend', 'trend', 'recentMr'));
    }
}
