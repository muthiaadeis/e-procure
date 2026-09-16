<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Procurement Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Analytics overview and procurement activity across all modules (MR, RRP, PO, PR).</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('vendors.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs text-gray-600 font-medium hover:bg-gray-50 transition shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span><strong class="text-gray-800">{{ number_format($totalVendor) }}</strong> Active Vendors</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs text-gray-600 font-medium shadow-sm">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ now()->format('F Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- 4 Stat Cards: MR, RLP, PR, PO --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- Material Request --}}
            <a href="{{ route('material-requests.index') }}" class="group block bg-white rounded-xl border border-gray-100 hover:border-indigo-200 shadow-sm hover:shadow transition p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center text-indigo-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </span>
                    <span class="inline-flex items-center text-xs font-semibold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100/70">
                        {{ number_format($mrThisMonth) }} this month
                    </span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Material Request (MR)</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalMr) }}</p>
                    <span class="text-xs text-indigo-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                        View <span aria-hidden="true">&rarr;</span>
                    </span>
                </div>
            </a>

            {{-- Local Purchase (RLP) --}}
            <a href="{{ route('rlps.index') }}" class="group block bg-white rounded-xl border border-gray-100 hover:border-purple-200 shadow-sm hover:shadow transition p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 rounded-xl bg-purple-50 group-hover:bg-purple-100 flex items-center justify-center text-purple-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 8.25l-9-4.5-9 4.5m18 0v9l-9 4.5m9-13.5l-9 4.5m0 9l-9-4.5v-9m9 13.5v-9m-9-4.5l9 4.5"/>
                        </svg>
                    </span>
                    <span class="inline-flex items-center text-xs font-semibold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-full border border-purple-100/70">
                        {{ number_format($rlpThisMonth) }} this month
                    </span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Local Purchase (RRP)</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalRlp) }}</p>
                    <span class="text-xs text-purple-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                        View <span aria-hidden="true">&rarr;</span>
                    </span>
                </div>
            </a>

            {{-- Purchase Order (PO) --}}
            <a href="{{ route('purchase-orders.index') }}" class="group block bg-white rounded-xl border border-gray-100 hover:border-emerald-200 shadow-sm hover:shadow transition p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 rounded-xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center text-emerald-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                        </svg>
                    </span>
                    <span class="inline-flex items-center text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-100/70">
                        {{ number_format($poThisMonth) }} this month
                    </span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Purchase Order (PO)</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPo) }}</p>
                    <span class="text-xs text-emerald-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                        View <span aria-hidden="true">&rarr;</span>
                    </span>
                </div>
            </a>

            {{-- Purchase Request (PR) --}}
            <a href="{{ route('purchase-requests.index') }}" class="group block bg-white rounded-xl border border-gray-100 hover:border-amber-200 shadow-sm hover:shadow transition p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-50 group-hover:bg-amber-100 flex items-center justify-center text-amber-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.994-4.708 2.6-7.253a1.125 1.125 0 00-1.11-1.35H5.25M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                    </span>
                    <span class="inline-flex items-center text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-100/70">
                        {{ number_format($prThisMonth) }} this month
                    </span>
                </div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Purchase Request (PR)</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPr) }}</p>
                    <span class="text-xs text-amber-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                        View <span aria-hidden="true">&rarr;</span>
                    </span>
                </div>
            </a>

        </div>

        {{-- Main Section: Multi-Module Monthly Trend Chart + Distribution Donut --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 1. Multi-Module Monthly Trend Chart (lg:col-span-2) --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                                <span>Procurement Trends Across All Documents</span>
                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-xs font-semibold">Last 6 Months</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Monthly issuance comparison of MR, RLP, PR, and PO documents.</p>
                        </div>

                        {{-- Tipe Grafik Toggle (Batang vs Garis) --}}
                        <div class="inline-flex p-1 bg-gray-100 rounded-lg text-xs font-medium text-gray-600">
                            <button type="button" id="btnChartBar" class="chart-type-btn px-3 py-1.5 rounded-md bg-white text-gray-800 font-semibold shadow-sm transition">
                                Bar
                            </button>
                            <button type="button" id="btnChartLine" class="chart-type-btn px-3 py-1.5 rounded-md text-gray-500 hover:text-gray-800 transition">
                                Line
                            </button>
                        </div>
                    </div>

                    {{-- Filter Tabs (All / MR / RLP / PR / PO) --}}
                    <div class="flex flex-wrap items-center gap-1.5 mb-5">
                        <button type="button" class="filter-tab active px-3 py-1 rounded-lg text-xs font-medium border border-indigo-600 bg-indigo-600 text-white shadow-sm transition" data-filter="all">
                            All Documents ({{ $totalAll }})
                        </button>
                        <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition" data-filter="mr">
                            <span class="inline-block w-2 h-2 rounded-full bg-indigo-500 mr-1.5"></span>MR ({{ $totalMr }})
                        </button>
                        <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition" data-filter="rlp">
                            <span class="inline-block w-2 h-2 rounded-full bg-purple-500 mr-1.5"></span>Local Purchase ({{ $totalRlp }})
                        </button>
                        <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition" data-filter="pr">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>PR ({{ $totalPr }})
                        </button>
                        <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition" data-filter="po">
                            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1.5"></span>PO ({{ $totalPo }})
                        </button>
                    </div>

                    {{-- Chart Container --}}
                    <div class="relative w-full h-72 sm:h-80">
                        <canvas id="procurementTrendChart"></canvas>
                    </div>
                </div>

                {{-- Chart Footer Metrics --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 mt-4 border-t border-gray-100">
                    <div class="p-2.5 rounded-lg bg-indigo-50/50 border border-indigo-100/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-indigo-700">Material Request</span>
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        </div>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('mr') }} <span class="text-[10px] text-gray-500 font-normal">documents</span></p>
                    </div>

                    <div class="p-2.5 rounded-lg bg-purple-50/50 border border-purple-100/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-purple-700">Local Purchase</span>
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        </div>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('rlp') }} <span class="text-[10px] text-gray-500 font-normal">documents</span></p>
                    </div>

                    <div class="p-2.5 rounded-lg bg-amber-50/50 border border-amber-100/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-amber-700">Purchase Request</span>
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        </div>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('pr') }} <span class="text-[10px] text-gray-500 font-normal">documents</span></p>
                    </div>

                    <div class="p-2.5 rounded-lg bg-emerald-50/50 border border-emerald-100/60">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold text-emerald-700">Purchase Order</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        </div>
                        <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('po') }} <span class="text-[10px] text-gray-500 font-normal">documents</span></p>
                    </div>
                </div>
            </div>

            {{-- 2. Distribution Donut Chart (lg:col-span-1) --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="font-bold text-gray-800 text-lg">Document Distribution</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Proportion of all procurement documents.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                            Total: {{ number_format($totalAll) }}
                        </span>
                    </div>

                    {{-- Donut canvas --}}
                    <div class="relative flex items-center justify-center my-3" style="min-height: 200px;">
                        <canvas id="procurementDistributionChart" class="max-w-[210px] max-h-[210px]"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-2xl font-extrabold text-gray-800">{{ number_format($totalAll) }}</span>
                            <span class="text-[11px] text-gray-400 font-medium">Total Documents</span>
                        </div>
                    </div>
                </div>

                {{-- Legend & Proportions breakdown --}}
                <div class="space-y-2.5 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            Material Request (MR)
                        </span>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-gray-800">{{ number_format($totalMr) }}</span>
                            <span class="text-gray-400 text-[11px]">({{ $totalAll > 0 ? round(($totalMr / $totalAll) * 100, 1) : 0 }}%)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            Local Purchase (RLP)
                        </span>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-gray-800">{{ number_format($totalRlp) }}</span>
                            <span class="text-gray-400 text-[11px]">({{ $totalAll > 0 ? round(($totalRlp / $totalAll) * 100, 1) : 0 }}%)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            Purchase Request (PR)
                        </span>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-gray-800">{{ number_format($totalPr) }}</span>
                            <span class="text-gray-400 text-[11px]">({{ $totalAll > 0 ? round(($totalPr / $totalAll) * 100, 1) : 0 }}%)</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-2 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            Purchase Order (PO)
                        </span>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="text-gray-800">{{ number_format($totalPo) }}</span>
                            <span class="text-gray-400 text-[11px]">({{ $totalAll > 0 ? round(($totalPo / $totalAll) * 100, 1) : 0 }}%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Row: Monthly Data Table & Recent Activities --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Monthly Activity Table (lg:col-span-2) --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Monthly Document Breakdown</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Recap of document quantities over the last 6 months.</p>
                    </div>
                    <span class="text-xs text-gray-400">Last updated: {{ now()->format('d M Y H:i') }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/75 text-xs text-gray-500 uppercase font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3 px-5">Month</th>
                                <th class="py-3 px-4 text-center">MR</th>
                                <th class="py-3 px-4 text-center">RLP</th>
                                <th class="py-3 px-4 text-center">PR</th>
                                <th class="py-3 px-4 text-center">PO</th>
                                <th class="py-3 px-5 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach($trend as $row)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="py-3.5 px-5 font-medium text-gray-800">
                                        {{ $row['full_label'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block min-w-[28px] py-0.5 px-2 rounded font-semibold text-xs {{ $row['mr'] > 0 ? 'bg-indigo-50 text-indigo-700' : 'text-gray-300' }}">
                                            {{ $row['mr'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block min-w-[28px] py-0.5 px-2 rounded font-semibold text-xs {{ $row['rlp'] > 0 ? 'bg-purple-50 text-purple-700' : 'text-gray-300' }}">
                                            {{ $row['rlp'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block min-w-[28px] py-0.5 px-2 rounded font-semibold text-xs {{ $row['pr'] > 0 ? 'bg-amber-50 text-amber-700' : 'text-gray-300' }}">
                                            {{ $row['pr'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-block min-w-[28px] py-0.5 px-2 rounded font-semibold text-xs {{ $row['po'] > 0 ? 'bg-emerald-50 text-emerald-700' : 'text-gray-300' }}">
                                            {{ $row['po'] }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5 text-right font-bold text-gray-900">
                                        {{ $row['total'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50/75 border-t border-gray-100 font-semibold text-xs text-gray-800">
                            <tr>
                                <td class="py-3 px-5 uppercase">6-Month Total</td>
                                <td class="py-3 px-4 text-center text-indigo-700">{{ $trend->sum('mr') }}</td>
                                <td class="py-3 px-4 text-center text-purple-700">{{ $trend->sum('rlp') }}</td>
                                <td class="py-3 px-4 text-center text-amber-700">{{ $trend->sum('pr') }}</td>
                                <td class="py-3 px-4 text-center text-emerald-700">{{ $trend->sum('po') }}</td>
                                <td class="py-3 px-5 text-right text-gray-900">{{ $trend->sum('total') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Recent Activities (lg:col-span-1) --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Recent Document Activities</h3>
                        <a href="{{ route('material-requests.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">All MRs &rarr;</a>
                    </div>

                    <div class="space-y-3.5">
                        @forelse($recentActivities as $activity)
                            <a href="{{ $activity->url }}" class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 transition block">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 {{ $activity->icon_color }}">
                                    {{ $activity->type }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $activity->title }}</p>
                                        <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $activity->badge_class }}">
                                            {{ $activity->type }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 truncate mt-0.5">{{ $activity->subtitle }}</p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-6">No recent document activities.</p>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-center text-xs">
                    <a href="{{ route('purchase-requests.index') }}" class="py-2 px-3 rounded-lg bg-gray-50 hover:bg-amber-50 hover:text-amber-700 text-gray-600 font-medium transition">
                        Open PR &rarr;
                    </a>
                    <a href="{{ route('purchase-orders.index') }}" class="py-2 px-3 rounded-lg bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 font-medium transition">
                        Open PO &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"><\/script>');
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Raw Trend Data
            const labels = @json($trend->pluck('label'));
            const fullLabels = @json($trend->pluck('full_label'));
            const mrData = @json($trend->pluck('mr'));
            const rlpData = @json($trend->pluck('rlp'));
            const prData = @json($trend->pluck('pr'));
            const poData = @json($trend->pluck('po'));

            // Datasets configuration
            const datasets = [
                {
                    key: 'mr',
                    label: 'Material Request (MR)',
                    data: mrData,
                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 2,
                    borderRadius: 6,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    key: 'rlp',
                    label: 'Local Purchase (RLP)',
                    data: rlpData,
                    backgroundColor: 'rgba(168, 85, 247, 0.85)',
                    borderColor: 'rgb(168, 85, 247)',
                    borderWidth: 2,
                    borderRadius: 6,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    key: 'pr',
                    label: 'Purchase Request (PR)',
                    data: prData,
                    backgroundColor: 'rgba(245, 158, 11, 0.85)',
                    borderColor: 'rgb(245, 158, 11)',
                    borderWidth: 2,
                    borderRadius: 6,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    key: 'po',
                    label: 'Purchase Order (PO)',
                    data: poData,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 2,
                    borderRadius: 6,
                    tension: 0.35,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }
            ];

            // 1. Initialise Trend Chart
            const trendCtx = document.getElementById('procurementTrendChart').getContext('2d');
            let currentChartType = 'bar';

            let trendChart = new Chart(trendCtx, {
                type: currentChartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                borderRadius: 3,
                                usePointStyle: false,
                                font: {
                                    family: "'Figtree', system-ui, sans-serif",
                                    size: 11,
                                    weight: '500'
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { family: "'Figtree', system-ui, sans-serif", size: 12, weight: '600' },
                            bodyFont: { family: "'Figtree', system-ui, sans-serif", size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                title: function (items) {
                                    if (items.length > 0) {
                                        return fullLabels[items[0].dataIndex] || items[0].label;
                                    }
                                    return '';
                                },
                                footer: function (items) {
                                    let sum = 0;
                                    items.forEach(function (item) {
                                        sum += item.raw;
                                    });
                                    return 'Total Documents: ' + sum;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { family: "'Figtree', system-ui, sans-serif", size: 11, weight: '500' },
                                color: '#6b7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: { family: "'Figtree', system-ui, sans-serif", size: 11 },
                                color: '#9ca3af'
                            },
                            grid: {
                                color: 'rgba(243, 244, 246, 1)',
                                drawBorder: false
                            }
                        }
                    }
                }
            });

            // Filter Tabs Click Handlers
            const filterTabs = document.querySelectorAll('.filter-tab');
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    filterTabs.forEach(t => {
                        t.classList.remove('active', 'bg-indigo-600', 'text-white', 'border-indigo-600');
                        t.classList.add('bg-white', 'text-gray-600', 'border-gray-200');
                    });
                    this.classList.add('active', 'bg-indigo-600', 'text-white', 'border-indigo-600');
                    this.classList.remove('bg-white', 'text-gray-600', 'border-gray-200');

                    const filter = this.dataset.filter;
                    trendChart.data.datasets.forEach(ds => {
                        if (filter === 'all' || ds.key === filter) {
                            ds.hidden = false;
                        } else {
                            ds.hidden = true;
                        }
                    });
                    trendChart.update();
                });
            });

            // Chart Type Buttons (Bar vs Line)
            const btnBar = document.getElementById('btnChartBar');
            const btnLine = document.getElementById('btnChartLine');

            function setChartType(type) {
                if (currentChartType === type) return;
                currentChartType = type;

                if (type === 'bar') {
                    btnBar.classList.add('bg-white', 'text-gray-800', 'font-semibold', 'shadow-sm');
                    btnBar.classList.remove('text-gray-500');
                    btnLine.classList.remove('bg-white', 'text-gray-800', 'font-semibold', 'shadow-sm');
                    btnLine.classList.add('text-gray-500');
                } else {
                    btnLine.classList.add('bg-white', 'text-gray-800', 'font-semibold', 'shadow-sm');
                    btnLine.classList.remove('text-gray-500');
                    btnBar.classList.remove('bg-white', 'text-gray-800', 'font-semibold', 'shadow-sm');
                    btnBar.classList.add('text-gray-500');
                }

                trendChart.config.type = type;
                trendChart.data.datasets.forEach(ds => {
                    ds.fill = (type === 'line');
                    if (type === 'line') {
                        // Soft transparent fill for line
                        if (ds.key === 'mr') ds.backgroundColor = 'rgba(99, 102, 241, 0.1)';
                        if (ds.key === 'rlp') ds.backgroundColor = 'rgba(168, 85, 247, 0.1)';
                        if (ds.key === 'pr') ds.backgroundColor = 'rgba(245, 158, 11, 0.1)';
                        if (ds.key === 'po') ds.backgroundColor = 'rgba(16, 185, 129, 0.1)';
                    } else {
                        if (ds.key === 'mr') ds.backgroundColor = 'rgba(99, 102, 241, 0.85)';
                        if (ds.key === 'rlp') ds.backgroundColor = 'rgba(168, 85, 247, 0.85)';
                        if (ds.key === 'pr') ds.backgroundColor = 'rgba(245, 158, 11, 0.85)';
                        if (ds.key === 'po') ds.backgroundColor = 'rgba(16, 185, 129, 0.85)';
                    }
                });
                trendChart.update();
            }

            btnBar.addEventListener('click', () => setChartType('bar'));
            btnLine.addEventListener('click', () => setChartType('line'));

            // 2. Initialise Distribution Donut Chart
            const distCtx = document.getElementById('procurementDistributionChart').getContext('2d');
            const distLabels = @json($distribution['labels']);
            const distData = @json($distribution['data']);
            const distColors = @json($distribution['colors']);

            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: distLabels,
                    datasets: [{
                        data: distData,
                        backgroundColor: distColors,
                        hoverOffset: 6,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { family: "'Figtree', system-ui, sans-serif", size: 12 },
                            bodyFont: { family: "'Figtree', system-ui, sans-serif", size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = context.raw || 0;
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${val} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>

