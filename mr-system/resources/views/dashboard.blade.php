<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Summary of this month's procurement activity.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-600 font-medium">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ now()->translatedFormat('F Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </span>
                    @if($mrTrend != 0)
                        <span class="inline-flex items-center gap-0.5 text-xs font-semibold {{ $mrTrend >= 0 ? 'text-green-600' : 'text-red-500' }}">
                            <svg class="w-3 h-3 {{ $mrTrend < 0 ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                            {{ abs($mrTrend) }}%
                        </span>
                    @else
                        <span class="text-xs font-semibold text-gray-400">— 0%</span>
                    @endif
                </div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Material Request</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalMr) }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.25 8.25l-9-4.5-9 4.5m18 0v9l-9 4.5m9-13.5l-9 4.5m0 9l-9-4.5v-9m9 13.5v-9m-9-4.5l9 4.5"/>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold text-gray-400">— 0%</span>
                </div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Total Local Purchase</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalRlp) }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <span class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 21V9.75l8.25-6 8.25 6V21m-16.5 0h16.5"/>
                        </svg>
                    </span>
                    <span class="text-xs font-semibold text-gray-400">— 0%</span>
                </div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Active Vendors</p>
                <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalVendor) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Trend chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-gray-800">Monthly Procurement Trend</h3>
                    <span class="text-xs text-gray-400">Material Requests per month</span>
                </div>

                @php $max = max(1, $trend->max('total')); @endphp
                <div class="flex items-end justify-between gap-3 h-48">
                    @foreach($trend as $point)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="w-full flex items-end justify-center h-40">
                                <div class="w-8 sm:w-10 rounded-t-md bg-indigo-500/80 hover:bg-indigo-600 transition-all"
                                     style="height: {{ $point['total'] > 0 ? max(6, round(($point['total'] / $max) * 100)) : 3 }}%"
                                     title="{{ $point['label'] }}: {{ $point['total'] }}"></div>
                            </div>
                            <span class="text-xs text-gray-400">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Aktivitas terbaru --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Recent Activity</h3>
                    <a href="{{ route('material-requests.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">View All</a>
                </div>

                <div class="space-y-4">
                    @forelse($recentMr as $req)
                        @php
                            $badge = match(true) {
                                $req->status === 'Done' => 'bg-green-100 text-green-700',
                                str_contains($req->status, 'Rejected') => 'bg-red-100 text-red-700',
                                $req->is_overdue => 'bg-red-100 text-red-700',
                                default => 'bg-yellow-100 text-yellow-700',
                            };
                            $badgeLabel = $req->is_overdue && $req->status !== 'Done' ? 'Late' : $req->status;
                        @endphp
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ $req->no_mr ?? '—' }}</p>
                                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badge }}">{{ $badgeLabel }}</span>
                                </div>
                                <p class="text-xs text-gray-500 truncate">{{ $req->charge_to }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $req->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
