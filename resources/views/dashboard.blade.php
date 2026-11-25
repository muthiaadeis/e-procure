<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-bold text-2xl text-gray-800 leading-tight">Dashboard Pengadaan</h1>
                <p class="text-sm text-gray-500 mt-1">Ringkasan analitik dan aktivitas pengadaan di seluruh modul (MR, RRP, PO, PR).</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('vendors.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs text-gray-600 font-medium hover:bg-gray-50 transition shadow-xs">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span><strong class="text-gray-800">{{ number_format($totalVendor) }}</strong> Vendor Aktif</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-xs text-gray-600 font-medium shadow-xs">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ now()->format('F Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6"
         x-data="{
            activeDashboardTab: localStorage.getItem('dashboard_view_tab') || 'for_me',
            setTab(tab) {
                this.activeDashboardTab = tab;
                try { localStorage.setItem('dashboard_view_tab', tab); } catch(e){}
                if (tab === 'global') {
                    this.$nextTick(() => window.dispatchEvent(new Event('resize')));
                }
            }
         }">

        {{-- Top Segmented Navigation: 'Untuk Saya' vs 'Ringkasan Sistem' --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-white p-2.5 rounded-2xl border border-gray-100 shadow-xs">
            <div class="flex items-center p-1 bg-gray-100/90 rounded-xl text-xs font-semibold">
                {{-- Tab 1: Untuk Saya --}}
                <button type="button"
                        @click="setTab('for_me')"
                        :class="activeDashboardTab === 'for_me' ? 'bg-white text-indigo-700 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 shrink-0 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-bold">Untuk Saya</span>

                    @php
                        $user = auth()->user();
                        $attentionCount = 0;
                        if ($user->isAdmin()) {
                            $attentionCount = $myStats['admin_unassigned_count'] + $myStats['admin_overdue_count'];
                        } elseif ($user->isApprover()) {
                            $attentionCount = $myStats['pending_signatures_count'];
                        } else {
                            $attentionCount = $myStats['my_rejected'];
                        }
                    @endphp

                    @if($attentionCount > 0)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-red-100 text-red-700 border border-red-200">
                            {{ $attentionCount }}
                        </span>
                    @endif
                </button>

                {{-- Tab 2: Ringkasan Sistem --}}
                <button type="button"
                        @click="setTab('global')"
                        :class="activeDashboardTab === 'global' ? 'bg-white text-indigo-700 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-4 py-2 rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 shrink-0 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Ringkasan Pengadaan Sistem</span>
                </button>
            </div>

            {{-- User Role & Jabatan Indicator --}}
            <div class="flex items-center gap-2.5 px-3 py-1.5 text-xs text-gray-500">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="truncate">
                    Login sebagai: <strong class="text-gray-800 font-semibold">{{ auth()->user()->name }}</strong>
                    @if(auth()->user()->jabatan)
                        <span class="text-indigo-600 font-medium">({{ auth()->user()->jabatan }})</span>
                    @endif
                </span>
                <span class="ml-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'bg-purple-100 text-purple-700' : (auth()->user()->isApprover() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-700') }}">
                    {{ auth()->user()->isAdmin() ? 'Super Admin' : (auth()->user()->isApprover() ? 'Approver' : 'User Input') }}
                </span>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- VIEW TAB 1: UNTUK SAYA (PERSONALIZED DASHBOARD)                --}}
        {{-- ============================================================== --}}
        <div id="dashboard-forme-container" x-show="activeDashboardTab === 'for_me'" x-cloak class="space-y-6">

            {{-- Welcome Banner --}}
            <div class="rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-5 text-white"
                 style="background: linear-gradient(135deg, #3730a3 0%, #4338ca 50%, #6d28d9 100%) !important; color: #ffffff !important;">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-extrabold text-xl shrink-0 shadow-xs"
                         style="background: rgba(255, 255, 255, 0.22); border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-xl font-extrabold text-white" style="color: #ffffff !important;">
                                Halo, {{ auth()->user()->name }}!
                            </h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                                  style="background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                                {{ auth()->user()->jabatan ?? (auth()->user()->isAdmin() ? 'Super Admin' : (auth()->user()->isApprover() ? 'Approver' : 'User Input')) }}
                            </span>
                        </div>
                        <p class="text-xs mt-1 max-w-xl leading-relaxed" style="color: rgba(255, 255, 255, 0.9) !important;">
                            @if(auth()->user()->isAdmin())
                                Kelola alur persetujuan, pantau dokumen yang butuh perhatian admin, dan pastikan pengadaan berjalan tanpa hambatan.
                            @elseif(auth()->user()->isApprover())
                                @if($myStats['pending_signatures_count'] > 0)
                                    Anda memiliki <strong class="underline decoration-white/60 decoration-2" style="color: #ffffff;">{{ $myStats['pending_signatures_count'] }} dokumen</strong> yang saat ini menunggu tanda tangan persetujuan Anda.
                                @else
                                    Semua tugas tanda tangan Anda telah selesai. Tidak ada dokumen tertunda saat ini.
                                @endif
                            @else
                                Pantau kemajuan persetujuan dan status dokumen MR, RRP, PO, dan PR yang Anda ajukan.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if(auth()->user()->isApprover())
                        @if($myStats['pending_signatures_count'] > 0 && $myPendingApprovals->first())
                            <a href="{{ $myPendingApprovals->first()->document_url }}?from=dashboard"
                               class="px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-2 hover:bg-indigo-50"
                               style="background: #ffffff; color: #4338ca;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                <span>Tanda Tangani Dokumen Pertama ({{ $myStats['pending_signatures_count'] }}) &rarr;</span>
                            </a>
                        @else
                            <div class="px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5"
                                 style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); color: #ffffff;">
                                <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Semua Tugas Selesai</span>
                            </div>
                        @endif
                    @endif

                    @if(auth()->user()->isInput())
                        <a href="{{ route('material-requests.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 hover:bg-indigo-50"
                           style="background: #ffffff; color: #4338ca;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat MR Baru
                        </a>
                        <a href="{{ route('rlps.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 hover:bg-white/30 text-white"
                           style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.35); color: #ffffff;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Buat RRP Baru
                        </a>
                    @endif

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.approval-slots.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs flex items-center gap-1.5 hover:bg-indigo-50"
                           style="background: #ffffff; color: #4338ca;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                            Atur Template Persetujuan
                        </a>
                    @endif
                </div>
            </div>

            {{-- 4 Stat Cards: Personalisasi Berdasarkan Peran Pengguna --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @if(auth()->user()->isApprover())
                    {{-- Card 1: Menunggu Tanda Tangan Saya --}}
                    <div class="stat-card-interactive bg-white rounded-xl border {{ $myStats['pending_signatures_count'] > 0 ? 'border-amber-200 bg-amber-50/20' : 'border-gray-100' }} shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg {{ $myStats['pending_signatures_count'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $myStats['pending_signatures_count'] > 0 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }}">
                                Menunggu Aksi
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Perlu Tanda Tangan</p>
                        <p class="text-2xl font-bold {{ $myStats['pending_signatures_count'] > 0 ? 'text-amber-700' : 'text-gray-800' }} mt-1">
                            {{ $myStats['pending_signatures_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 2: Terlambat / Overdue (>2 Hari) --}}
                    <div class="stat-card-interactive bg-white rounded-xl border {{ $myStats['overdue_signatures_count'] > 0 ? 'border-red-200 bg-red-50/20' : 'border-gray-100' }} shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg {{ $myStats['overdue_signatures_count'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $myStats['overdue_signatures_count'] > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                Batas 2 Hari
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Terlewat (>2 Hari)</p>
                        <p class="text-2xl font-bold {{ $myStats['overdue_signatures_count'] > 0 ? 'text-red-700' : 'text-gray-800' }} mt-1">
                            {{ $myStats['overdue_signatures_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 3: Disetujui Saya Bulan Ini --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700">
                                Bulan Ini
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Disetujui Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ $myStats['signed_this_month_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 4: Total Riwayat Persetujuan Saya --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                Total Riwayat
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pernah TTD</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ $myStats['total_signed_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                @elseif(auth()->user()->isInput())
                    {{-- Inputer / Staff Cards --}}
                    {{-- Card 1: Pengajuan Sedang Berjalan --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">
                                Dalam Proses
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sedang Berjalan</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ $myStats['my_in_progress'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 2: Dokumen Ditolak (Perlu Revisi) --}}
                    <div class="stat-card-interactive bg-white rounded-xl border {{ $myStats['my_rejected'] > 0 ? 'border-red-200 bg-red-50/20' : 'border-gray-100' }} shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg {{ $myStats['my_rejected'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $myStats['my_rejected'] > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                Perlu Revisi
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Ditolak Approver</p>
                        <p class="text-2xl font-bold {{ $myStats['my_rejected'] > 0 ? 'text-red-700' : 'text-gray-800' }} mt-1">
                            {{ $myStats['my_rejected'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 3: Selesai / Disetujui Penuh --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-700">
                                Selesai
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Selesai (Done)</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ $myStats['my_done'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 4: Total Buatan Saya --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                Dokumen Saya
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Buatan Saya</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ $myStats['my_total_created'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                @else
                    {{-- Super Admin Cards --}}
                    {{-- Card 1: Perlu Setup Penandatangan --}}
                    <div class="stat-card-interactive bg-white rounded-xl border {{ $myStats['admin_unassigned_count'] > 0 ? 'border-purple-200 bg-purple-50/20' : 'border-gray-100' }} shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg {{ $myStats['admin_unassigned_count'] > 0 ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $myStats['admin_unassigned_count'] > 0 ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-600' }}">
                                Tindakan Admin
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Perlu Setup Approver</p>
                        <p class="text-2xl font-bold {{ $myStats['admin_unassigned_count'] > 0 ? 'text-purple-700' : 'text-gray-800' }} mt-1">
                            {{ $myStats['admin_unassigned_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 2: Terlambat di Sistem (>2 Hari) --}}
                    <div class="stat-card-interactive bg-white rounded-xl border {{ $myStats['admin_overdue_count'] > 0 ? 'border-red-200 bg-red-50/20' : 'border-gray-100' }} shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg {{ $myStats['admin_overdue_count'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $myStats['admin_overdue_count'] > 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600' }}">
                                Sistem
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Overdue di Sistem</p>
                        <p class="text-2xl font-bold {{ $myStats['admin_overdue_count'] > 0 ? 'text-red-700' : 'text-gray-800' }} mt-1">
                            {{ $myStats['admin_overdue_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 3: Dokumen Ditolak di Sistem --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-50 text-red-700">
                                Ditolak
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Dokumen Ditolak</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ $myStats['admin_rejected_count'] }} <span class="text-xs font-normal text-gray-500">dokumen</span>
                        </p>
                    </div>

                    {{-- Card 4: Pengguna Terdaftar --}}
                    <div class="stat-card-interactive bg-white rounded-xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-2">
                            <span class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                                Akun
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengguna Terdaftar</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">
                            {{ \App\Models\User::count() }} <span class="text-xs font-normal text-gray-500">akun</span>
                        </p>
                    </div>
                @endif
            </div>

            {{-- APPROVER SECTION: TUGAS MENUNGGU PERSETUJUAN ANDA --}}
            @if(auth()->user()->isApprover() || $myPendingApprovals->isNotEmpty())
                <div x-data="{
                    perPage: 10,
                    page: 1,
                    total: {{ $myPendingApprovals->count() }},
                    get totalPages() {
                        return Math.max(1, Math.ceil(this.total / this.perPage));
                    },
                    nextPage() {
                        if (this.page < this.totalPages) this.page++;
                    },
                    prevPage() {
                        if (this.page > 1) this.page--;
                    },
                    goToPage(p) {
                        if (p >= 1 && p <= this.totalPages) this.page = p;
                    },
                    isVisible(index) {
                        const start = (this.page - 1) * this.perPage;
                        return index >= start && index < start + this.perPage;
                    }
                }" class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                Dokumen Menunggu Tanda Tangan Anda
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar dokumen di mana tahap persetujuan telah terbuka dan menunggu aksi Anda.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $myPendingApprovals->count() > 0 ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-gray-100 text-gray-600' }}">
                            {{ $myPendingApprovals->count() }} Menunggu TTD
                        </span>
                    </div>

                    @if($myPendingApprovals->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm divide-y divide-gray-100">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="py-3.5 px-5">Dokumen</th>
                                        <th class="py-3.5 px-4">Tahap / Posisi Anda</th>
                                        <th class="py-3.5 px-4 text-center">Batas Waktu</th>
                                        <th class="py-3.5 px-4 text-center">Status</th>
                                        <th class="py-3.5 px-5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-700">
                                    @foreach($myPendingApprovals as $approval)
                                        @php
                                            $isOverdue = $approval->isOverdue();
                                            $doc = $approval->document;
                                        @endphp
                                        <tr x-show="isVisible({{ $loop->index }})"
                                            onclick="window.location.href = '{{ $approval->document_url }}?from=dashboard'"
                                            class="cursor-pointer hover:bg-amber-50/50 transition {{ $isOverdue ? 'bg-red-50/20' : '' }}">
                                            <td class="py-4 px-5">
                                                <div class="flex items-center gap-3">
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase {{ $approval->document_badge_class }}">
                                                        {{ $approval->document_module }}
                                                    </span>
                                                    <div>
                                                        <a href="{{ $approval->document_url }}?from=dashboard" class="font-bold text-gray-900 hover:text-indigo-600 transition block">
                                                            {{ $approval->document_number }}
                                                        </a>
                                                        <p class="text-xs text-gray-400">
                                                            Dibuat {{ $approval->created_at->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="py-4 px-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold flex items-center justify-center shrink-0">
                                                        {{ $approval->step }}
                                                    </span>
                                                    <span class="font-semibold text-gray-800 text-xs">
                                                        {{ $approval->label }}
                                                    </span>
                                                </div>
                                            </td>

                                            <td class="py-4 px-4 text-center whitespace-nowrap text-xs">
                                                @if($isOverdue)
                                                    <span class="inline-flex items-center gap-1 font-bold text-red-600 bg-red-50 border border-red-200 px-2.5 py-1 rounded-full text-[11px]">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                        Lewat Batas (>2 Hari)
                                                    </span>
                                                @else
                                                    <span class="text-gray-500 font-medium">
                                                        Jatuh tempo: {{ $approval->deadlineDate()->translatedFormat('d M Y') }}
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isOverdue ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">
                                                    {{ $isOverdue ? 'Segera TTD' : 'Perlu Tanda Tangan' }}
                                                </span>
                                            </td>

                                            <td class="py-4 px-5 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                                                <a href="{{ $approval->document_url }}?from=dashboard"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold {{ $isOverdue ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }} shadow-xs transition">
                                                    <span>Tanda Tangani Sekarang</span>
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Toolbar Pagination & Slide --}}
                        <div class="px-5 py-3.5 bg-gray-50/70 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3 text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <span>Tampilkan</span>
                                    <select x-model.number="perPage" @change="page = 1"
                                            class="text-xs font-semibold bg-white border border-gray-200 rounded-lg py-1 pl-3 pr-8 text-gray-700 shadow-2xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                        <option :value="10">10</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                    </select>
                                    <span>baris</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <div>
                                    Menampilkan <span class="font-bold text-gray-800" x-text="total === 0 ? 0 : (page - 1) * perPage + 1"></span> - 
                                    <span class="font-bold text-gray-800" x-text="Math.min(page * perPage, total)"></span> dari 
                                    <span class="font-bold text-gray-800" x-text="total"></span> dokumen
                                </div>
                            </div>

                            <div class="flex items-center gap-1" x-show="totalPages > 1">
                                <button type="button" @click="prevPage()" :disabled="page === 1"
                                        class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    <span>Sebelumnya</span>
                                </button>

                                <div class="flex items-center gap-1">
                                    <template x-for="p in totalPages" :key="p">
                                        <button type="button" @click="goToPage(p)"
                                                :class="page === p ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200'"
                                                class="w-7 h-7 rounded-lg text-xs flex items-center justify-center transition"
                                                x-text="p">
                                        </button>
                                    </template>
                                </div>

                                <button type="button" @click="nextPage()" :disabled="page === totalPages"
                                        class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1 font-medium">
                                    <span>Selanjutnya</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="py-12 px-6 text-center">
                            <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h4 class="font-bold text-gray-800 text-base">Tidak Ada Dokumen Menunggu Tanda Tangan</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
                                Semua tugas persetujuan Anda saat ini telah ditandatangani. Sistem akan memberi notifikasi otomatis ketika ada dokumen baru yang memerlukan persetujuan Anda.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Riwayat Persetujuan Saya Terakhir --}}
                @if($myRecentSigned->isNotEmpty())
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs p-5">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                    Riwayat Dokumen yang Telah Saya Setujui Terakhir
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Dokumen yang berhasil Anda tanda tangani baru-baru ini.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($myRecentSigned as $signed)
                                <a href="{{ $signed->document_url }}?from=dashboard" class="p-3.5 rounded-xl border border-gray-100 hover:border-indigo-200 bg-gray-50/40 hover:bg-white transition block group">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $signed->document_badge_class }}">
                                            {{ $signed->document_module }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-green-700 bg-green-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Ditandatangani
                                        </span>
                                    </div>
                                    <p class="text-sm font-bold text-gray-800 group-hover:text-indigo-600 transition truncate">
                                        {{ $signed->document_number }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">Sebagai: <strong>{{ $signed->label }}</strong></p>
                                    <p class="text-[11px] text-gray-400 mt-1">Pada {{ $signed->signed_at?->diffForHumans() ?? '-' }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            {{-- CREATOR / INPUTER SECTION: DOKUMEN SAYA --}}
            @if(auth()->user()->isInput() || $myCreatedDocs->isNotEmpty())
                {{-- Alert Khusus jika Ada Dokumen yang Ditolak --}}
                @php
                    $rejectedDocs = $myCreatedDocs->filter(fn($d) => $d->is_rejected);
                @endphp

                @if($rejectedDocs->isNotEmpty())
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 shadow-xs">
                        <div class="flex items-center gap-2.5 text-red-800 font-bold text-sm mb-3">
                            <span class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center shrink-0 text-red-700 font-bold">
                                !
                            </span>
                            <span>Perhatian: Ada {{ $rejectedDocs->count() }} Dokumen Buatan Anda yang Ditolak oleh Approver</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($rejectedDocs as $rej)
                                <div class="bg-white rounded-xl p-4 border border-red-100 shadow-2xs flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $rej->badge_class }}">
                                                {{ $rej->type }}
                                            </span>
                                            <span class="text-[10px] font-bold text-red-700 bg-red-100 px-2 py-0.5 rounded-full">
                                                Ditolak
                                            </span>
                                        </div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $rej->title }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $rej->subtitle }}</p>
                                        @if($rej->rejection_reason)
                                            <div class="mt-2 p-2 rounded-lg bg-red-50 text-red-700 text-xs italic">
                                                &ldquo;{{ $rej->rejection_reason }}&rdquo;
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-3 pt-2.5 border-t border-gray-100 flex items-center justify-end gap-2">
                                        @if($rej->edit_url)
                                            <a href="{{ $rej->edit_url }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition">
                                                Edit & Ajukan Ulang &rarr;
                                            </a>
                                        @endif
                                        <a href="{{ $rej->url }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                                            Lihat Dokumen
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Tabel Dokumen Pengajuan Saya --}}
                <div x-data="{
                    perPage: 10,
                    page: 1,
                    total: {{ $myCreatedDocs->count() }},
                    get totalPages() {
                        return Math.max(1, Math.ceil(this.total / this.perPage));
                    },
                    nextPage() {
                        if (this.page < this.totalPages) this.page++;
                    },
                    prevPage() {
                        if (this.page > 1) this.page--;
                    },
                    goToPage(p) {
                        if (p >= 1 && p <= this.totalPages) this.page = p;
                    },
                    isVisible(index) {
                        const start = (this.page - 1) * this.perPage;
                        return index >= start && index < start + this.perPage;
                    }
                }" class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-gray-50/50">
                        <div>
                            <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                Dokumen Pengajuan Saya (Aktif)
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">Memantau alur persetujuan dokumen yang sedang berjalan atau perlu revisi.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            Total: {{ $myCreatedDocs->count() }} Dokumen Aktif
                        </span>
                    </div>

                    @if($myCreatedDocs->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm divide-y divide-gray-100">
                                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <tr>
                                        <th class="py-3.5 px-5">Dokumen</th>
                                        <th class="py-3.5 px-4">Deskripsi / Proyek</th>
                                        <th class="py-3.5 px-4 text-center">Tanggal Dibuat</th>
                                        <th class="py-3.5 px-4 text-center">Status Alur</th>
                                        <th class="py-3.5 px-5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-gray-700">
                                    @foreach($myCreatedDocs as $doc)
                                        @php
                                            $statusClass = match(true) {
                                                $doc->is_rejected => 'bg-red-100 text-red-700',
                                                $doc->status === 'Done' => 'bg-green-100 text-green-700',
                                                $doc->status === 'Pending Setup' => 'bg-purple-100 text-purple-700',
                                                default => 'bg-indigo-100 text-indigo-700',
                                            };
                                        @endphp
                                        <tr x-show="isVisible({{ $loop->index }})" class="hover:bg-gray-50/60 transition">
                                            <td class="py-3.5 px-5">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $doc->badge_class }}">
                                                        {{ $doc->type }}
                                                    </span>
                                                    <a href="{{ $doc->url }}" class="font-bold text-gray-900 hover:text-indigo-600 transition">
                                                        {{ $doc->title }}
                                                    </a>
                                                </div>
                                            </td>

                                            <td class="py-3.5 px-4 max-w-xs truncate text-xs text-gray-600">
                                                {{ $doc->subtitle }}
                                            </td>

                                            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs text-gray-500">
                                                {{ $doc->created_at->format('d M Y') }}
                                            </td>

                                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                                    {{ $doc->status }}
                                                    @if($doc->total_count > 1 && $doc->status !== 'Done' && $doc->status !== 'Pending Setup' && !$doc->is_rejected)
                                                        <span class="ml-1 text-[10px] opacity-80">({{ $doc->signed_count }}/{{ $doc->total_count }})</span>
                                                    @endif
                                                </span>
                                            </td>

                                            <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($doc->is_rejected && $doc->edit_url)
                                                        <a href="{{ $doc->edit_url }}" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold transition">
                                                            Revisi &rarr;
                                                        </a>
                                                    @endif
                                                    <a href="{{ $doc->url }}" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                                                        Detail &rarr;
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Toolbar Pagination & Slide --}}
                        <div class="px-5 py-3.5 bg-gray-50/70 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3 text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <span>Tampilkan</span>
                                    <select x-model.number="perPage" @change="page = 1"
                                            class="text-xs font-semibold bg-white border border-gray-200 rounded-lg py-1 pl-3 pr-8 text-gray-700 shadow-2xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                        <option :value="10">10</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                    </select>
                                    <span>baris</span>
                                </div>
                                <span class="text-gray-300">|</span>
                                <div>
                                    Menampilkan <span class="font-bold text-gray-800" x-text="total === 0 ? 0 : (page - 1) * perPage + 1"></span> - 
                                    <span class="font-bold text-gray-800" x-text="Math.min(page * perPage, total)"></span> dari 
                                    <span class="font-bold text-gray-800" x-text="total"></span> dokumen
                                </div>
                            </div>

                            <div class="flex items-center gap-1" x-show="totalPages > 1">
                                <button type="button" @click="prevPage()" :disabled="page === 1"
                                        class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    <span>Sebelumnya</span>
                                </button>

                                <div class="flex items-center gap-1">
                                    <template x-for="p in totalPages" :key="p">
                                        <button type="button" @click="goToPage(p)"
                                                :class="page === p ? 'bg-indigo-600 text-white font-bold shadow-xs' : 'bg-white hover:bg-gray-100 text-gray-700 border border-gray-200'"
                                                class="w-7 h-7 rounded-lg text-xs flex items-center justify-center transition"
                                                x-text="p">
                                        </button>
                                    </template>
                                </div>

                                <button type="button" @click="nextPage()" :disabled="page === totalPages"
                                        class="px-2.5 py-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1 font-medium">
                                    <span>Selanjutnya</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="py-12 px-6 text-center">
                            <div class="w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h4 class="font-bold text-gray-800 text-base">Tidak Ada Dokumen Aktif</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
                                Seluruh dokumen pengajuan Anda telah selesai disetujui (Done) atau Anda belum membuat pengajuan baru.
                            </p>
                            <a href="{{ route('material-requests.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-600 font-bold hover:underline">
                                Buat Dokumen Pengajuan Baru &rarr;
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- SUPER ADMIN SECTION: DOKUMEN PERLU TINDAKAN ADMIN --}}
            @if(auth()->user()->isAdmin())
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- 1. Dokumen Belum Diatur Approver-nya --}}
                    <div x-data="{
                        perPage: 10,
                        page: 1,
                        total: {{ $adminPendingSetup->count() }},
                        get totalPages() {
                            return Math.max(1, Math.ceil(this.total / this.perPage));
                        },
                        nextPage() {
                            if (this.page < this.totalPages) this.page++;
                        },
                        prevPage() {
                            if (this.page > 1) this.page--;
                        },
                        goToPage(p) {
                            if (p >= 1 && p <= this.totalPages) this.page = p;
                        },
                        isVisible(index) {
                            const start = (this.page - 1) * this.perPage;
                            return index >= start && index < start + this.perPage;
                        }
                    }" class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-purple-50/30">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                        Dokumen Baru Perlu Atur Approver
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Dokumen yang baru disubmit pembuat dan belum ada penandatangan lanjutan.</p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                    {{ $adminPendingSetup->count() }}
                                </span>
                            </div>

                            <div class="divide-y divide-gray-100">
                                @forelse($adminPendingSetup as $doc)
                                    <div x-show="isVisible({{ $loop->index }})" class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $doc->badge_class }}">
                                                {{ $doc->type }}
                                            </span>
                                            <div>
                                                <p class="font-bold text-gray-900 text-xs">{{ $doc->title }}</p>
                                                <p class="text-[11px] text-gray-400">Dibuat oleh {{ $doc->creator }} &bull; {{ $doc->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ $doc->url }}" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-purple-600 hover:bg-purple-700 text-white transition shadow-2xs">
                                            Atur Approver &rarr;
                                        </a>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-gray-400">
                                        Semua dokumen sudah memiliki alur persetujuan.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Pagination Toolbar --}}
                        @if($adminPendingSetup->isNotEmpty())
                            <div class="px-4 py-3 bg-gray-50/70 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <select x-model.number="perPage" @change="page = 1"
                                            class="text-xs font-semibold bg-white border border-gray-200 rounded-lg py-1 pl-2.5 pr-7 text-gray-700 shadow-2xs focus:ring-1 focus:ring-purple-500 focus:border-purple-500 cursor-pointer">
                                        <option :value="10">10</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                    </select>
                                    <span>(<span x-text="total === 0 ? 0 : (page - 1) * perPage + 1"></span>-<span x-text="Math.min(page * perPage, total)"></span> dari <span x-text="total"></span>)</span>
                                </div>
                                <div class="flex items-center gap-1" x-show="totalPages > 1">
                                    <button type="button" @click="prevPage()" :disabled="page === 1"
                                            class="px-2 py-1 rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs">
                                        &larr;
                                    </button>
                                    <template x-for="p in totalPages" :key="p">
                                        <button type="button" @click="goToPage(p)"
                                                :class="page === p ? 'bg-purple-600 text-white font-bold' : 'bg-white text-gray-700 border border-gray-200'"
                                                class="w-6 h-6 rounded text-xs flex items-center justify-center"
                                                x-text="p">
                                        </button>
                                    </template>
                                    <button type="button" @click="nextPage()" :disabled="page === totalPages"
                                            class="px-2 py-1 rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs">
                                        &rarr;
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- 2. Dokumen Overdue dalam Sistem (>2 Hari) --}}
                    <div x-data="{
                        perPage: 10,
                        page: 1,
                        total: {{ $adminOverdueList->count() }},
                        get totalPages() {
                            return Math.max(1, Math.ceil(this.total / this.perPage));
                        },
                        nextPage() {
                            if (this.page < this.totalPages) this.page++;
                        },
                        prevPage() {
                            if (this.page > 1) this.page--;
                        },
                        goToPage(p) {
                            if (p >= 1 && p <= this.totalPages) this.page = p;
                        },
                        isVisible(index) {
                            const start = (this.page - 1) * this.perPage;
                            return index >= start && index < start + this.perPage;
                        }
                    }" class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden flex flex-col justify-between">
                        <div>
                            <div class="p-5 border-b border-gray-100 flex items-center justify-between bg-red-50/30">
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-red-600"></span>
                                        Monitoring Overdue Sistem (>2 Hari)
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Tahapan persetujuan yang telah melewati 2 hari dan diteruskan otomatis.</p>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    {{ $adminOverdueList->count() }}
                                </span>
                            </div>

                            <div class="divide-y divide-gray-100">
                                @forelse($adminOverdueList as $ov)
                                    <div x-show="isVisible({{ $loop->index }})" class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                        <div class="flex items-center gap-3">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border uppercase {{ $ov->document_badge_class }}">
                                                {{ $ov->document_module }}
                                            </span>
                                            <div>
                                                <p class="font-bold text-gray-900 text-xs">{{ $ov->document_number }}</p>
                                                <p class="text-[11px] text-red-600 font-medium">
                                                    Tahap '{{ $ov->label }}' oleh {{ $ov->user->name ?? 'Approver' }} melewati 2 hari
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ $ov->document_url }}?from=dashboard" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                                            Buka &rarr;
                                        </a>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-xs text-gray-400">
                                        Tidak ada tahapan persetujuan yang mengalami keterlambatan saat ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Pagination Toolbar --}}
                        @if($adminOverdueList->isNotEmpty())
                            <div class="px-4 py-3 bg-gray-50/70 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <select x-model.number="perPage" @change="page = 1"
                                            class="text-xs font-semibold bg-white border border-gray-200 rounded-lg py-1 pl-2.5 pr-7 text-gray-700 shadow-2xs focus:ring-1 focus:ring-red-500 focus:border-red-500 cursor-pointer">
                                        <option :value="10">10</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                    </select>
                                    <span>(<span x-text="total === 0 ? 0 : (page - 1) * perPage + 1"></span>-<span x-text="Math.min(page * perPage, total)"></span> dari <span x-text="total"></span>)</span>
                                </div>
                                <div class="flex items-center gap-1" x-show="totalPages > 1">
                                    <button type="button" @click="prevPage()" :disabled="page === 1"
                                            class="px-2 py-1 rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs">
                                        &larr;
                                    </button>
                                    <template x-for="p in totalPages" :key="p">
                                        <button type="button" @click="goToPage(p)"
                                                :class="page === p ? 'bg-red-600 text-white font-bold' : 'bg-white text-gray-700 border border-gray-200'"
                                                class="w-6 h-6 rounded text-xs flex items-center justify-center"
                                                x-text="p">
                                        </button>
                                    </template>
                                    <button type="button" @click="nextPage()" :disabled="page === totalPages"
                                            class="px-2 py-1 rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs">
                                        &rarr;
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- ============================================================== --}}
        {{-- VIEW TAB 2: RINGKASAN PENGADAAN SISTEM (GLOBAL ANALYTICS)     --}}
        {{-- ============================================================== --}}
        <div id="dashboard-global-container" x-show="activeDashboardTab === 'global'" x-cloak class="space-y-6">

            {{-- 4 Stat Cards: MR, RLP, PR, PO --}}
            <div id="dashboard-stats-container" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                {{-- Material Request --}}
                <a href="{{ route('material-requests.index') }}" class="group block bg-white rounded-xl border border-gray-100 hover:border-indigo-200 shadow-sm hover:shadow transition p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="w-10 h-10 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 flex items-center justify-center text-indigo-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </span>
                        <span class="inline-flex items-center text-xs font-semibold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-100/70">
                            {{ number_format($mrThisMonth) }} bulan ini
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Material Request (MR)</p>
                    <div class="flex items-baseline justify-between mt-1">
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalMr) }}</p>
                        <span class="text-xs text-indigo-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                            Lihat <span aria-hidden="true">&rarr;</span>
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
                            {{ number_format($rlpThisMonth) }} bulan ini
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Local Purchase (RRP)</p>
                    <div class="flex items-baseline justify-between mt-1">
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalRlp) }}</p>
                        <span class="text-xs text-purple-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                            Lihat <span aria-hidden="true">&rarr;</span>
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
                            {{ number_format($poThisMonth) }} bulan ini
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Purchase Order (PO)</p>
                    <div class="flex items-baseline justify-between mt-1">
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPo) }}</p>
                        <span class="text-xs text-emerald-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                            Lihat <span aria-hidden="true">&rarr;</span>
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
                            {{ number_format($prThisMonth) }} bulan ini
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Purchase Request (PR)</p>
                    <div class="flex items-baseline justify-between mt-1">
                        <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPr) }}</p>
                        <span class="text-xs text-amber-600 font-medium group-hover:translate-x-0.5 transition-transform flex items-center gap-0.5">
                            Lihat <span aria-hidden="true">&rarr;</span>
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
                                    <span>Tren Pengadaan Seluruh Dokumen</span>
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-xs font-semibold">6 Bulan Terakhir</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Perbandingan penerbitan bulanan dokumen MR, RRP, PR, dan PO.</p>
                            </div>

                            {{-- Tipe Grafik Toggle (Batang vs Garis) --}}
                            <div class="inline-flex p-1 bg-gray-100 rounded-lg text-xs font-medium text-gray-600">
                                <button type="button" id="btnChartBar" class="chart-type-btn px-3 py-1.5 rounded-md bg-white text-gray-800 font-semibold shadow-sm transition cursor-pointer">
                                    Batang
                                </button>
                                <button type="button" id="btnChartLine" class="chart-type-btn px-3 py-1.5 rounded-md text-gray-500 hover:text-gray-800 transition cursor-pointer">
                                    Garis
                                </button>
                            </div>
                        </div>

                        {{-- Filter Tabs (All / MR / RLP / PR / PO) --}}
                        <div class="flex flex-wrap items-center gap-1.5 mb-5">
                            <button type="button" class="filter-tab active px-3 py-1 rounded-lg text-xs font-medium border border-indigo-600 bg-indigo-600 text-white shadow-sm transition cursor-pointer" data-filter="all">
                                Semua Dokumen ({{ $totalAll }})
                            </button>
                            <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition cursor-pointer" data-filter="mr">
                                <span class="inline-block w-2 h-2 rounded-full bg-indigo-500 mr-1.5"></span>MR ({{ $totalMr }})
                            </button>
                            <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition cursor-pointer" data-filter="rlp">
                                <span class="inline-block w-2 h-2 rounded-full bg-purple-500 mr-1.5"></span>Local Purchase ({{ $totalRlp }})
                            </button>
                            <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition cursor-pointer" data-filter="pr">
                                <span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1.5"></span>PR ({{ $totalPr }})
                            </button>
                            <button type="button" class="filter-tab px-3 py-1 rounded-lg text-xs font-medium border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 transition cursor-pointer" data-filter="po">
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
                            <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('mr') }} <span class="text-[10px] text-gray-500 font-normal">dokumen</span></p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-purple-50/50 border border-purple-100/60">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-purple-700">Local Purchase</span>
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            </div>
                            <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('rlp') }} <span class="text-[10px] text-gray-500 font-normal">dokumen</span></p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-amber-50/50 border border-amber-100/60">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-amber-700">Purchase Request</span>
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            </div>
                            <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('pr') }} <span class="text-[10px] text-gray-500 font-normal">dokumen</span></p>
                        </div>

                        <div class="p-2.5 rounded-lg bg-emerald-50/50 border border-emerald-100/60">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-emerald-700">Purchase Order</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <p class="text-lg font-bold text-gray-800 mt-1">{{ $trend->sum('po') }} <span class="text-[10px] text-gray-500 font-normal">dokumen</span></p>
                        </div>
                    </div>
                </div>

                {{-- 2. Distribution Donut Chart (lg:col-span-1) --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg">Distribusi Dokumen</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Proporsi seluruh dokumen pengadaan.</p>
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
                                <span class="text-[11px] text-gray-400 font-medium">Total Dokumen</span>
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
                            <h3 class="font-semibold text-gray-800">Rincian Dokumen Bulanan</h3>
                            <p class="text-xs text-gray-500 mt-0.5">Rekap jumlah dokumen selama 6 bulan terakhir.</p>
                        </div>
                        <span class="text-xs text-gray-400">Terakhir diperbarui: {{ now()->format('d M Y H:i') }}</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50/75 text-xs text-gray-500 uppercase font-semibold border-b border-gray-100">
                                <tr>
                                    <th class="py-3 px-5">Bulan</th>
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
                                    <td class="py-3 px-5 uppercase">Total 6 Bulan</td>
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
                <div id="dashboard-activities-container"
                     x-data="{
                        perPage: 10,
                        page: 1,
                        total: {{ $recentActivities->count() }},
                        get totalPages() {
                            return Math.max(1, Math.ceil(this.total / this.perPage));
                        },
                        nextPage() {
                            if (this.page < this.totalPages) this.page++;
                        },
                        prevPage() {
                            if (this.page > 1) this.page--;
                        },
                        goToPage(p) {
                            if (p >= 1 && p <= this.totalPages) this.page = p;
                        },
                        isVisible(index) {
                            const start = (this.page - 1) * this.perPage;
                            return index >= start && index < start + this.perPage;
                        }
                     }"
                     class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                            <div>
                                <h3 class="font-semibold text-gray-800">Aktivitas Dokumen Terbaru</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Semua modul pengadaan terkini</p>
                            </div>
                            <a href="{{ route('material-requests.index') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">Semua MR &rarr;</a>
                        </div>

                        <div class="space-y-2.5">
                            @forelse($recentActivities as $activity)
                                <a href="{{ $activity->url }}"
                                   x-show="isVisible({{ $loop->index }})"
                                   class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 transition block">
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
                                <p class="text-sm text-gray-400 text-center py-6">Belum ada aktivitas dokumen terbaru.</p>
                            @endforelse
                        </div>

                        {{-- Pagination Toolbar --}}
                        @if($recentActivities->isNotEmpty())
                            <div class="pt-3 mt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div class="flex items-center gap-2 text-gray-500">
                                    <select x-model.number="perPage" @change="page = 1"
                                            class="text-xs font-semibold bg-white border border-gray-200 rounded-lg py-1 pl-2.5 pr-7 text-gray-700 shadow-2xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 cursor-pointer">
                                        <option :value="10">10</option>
                                        <option :value="50">50</option>
                                        <option :value="100">100</option>
                                    </select>
                                    <span>(<span x-text="total === 0 ? 0 : (page - 1) * perPage + 1"></span>-<span x-text="Math.min(page * perPage, total)"></span> dari <span x-text="total"></span>)</span>
                                </div>
                                <div class="flex items-center gap-1" x-show="totalPages > 1">
                                    <button type="button" @click="prevPage()" :disabled="page === 1"
                                            class="px-2 py-1 rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs">
                                        &larr;
                                    </button>
                                    <template x-for="p in totalPages" :key="p">
                                        <button type="button" @click="goToPage(p)"
                                                :class="page === p ? 'bg-indigo-600 text-white font-bold' : 'bg-white text-gray-700 border border-gray-200'"
                                                class="w-6 h-6 rounded text-xs flex items-center justify-center"
                                                x-text="p">
                                        </button>
                                    </template>
                                    <button type="button" @click="nextPage()" :disabled="page === totalPages"
                                            class="px-2 py-1 rounded border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs">
                                        &rarr;
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 mt-4 border-t border-gray-100 grid grid-cols-2 gap-2 text-center text-xs">
                        <a href="{{ route('purchase-requests.index') }}" class="py-2 px-3 rounded-lg bg-gray-50 hover:bg-amber-50 hover:text-amber-700 text-gray-600 font-medium transition">
                            Buka PR &rarr;
                        </a>
                        <a href="{{ route('purchase-orders.index') }}" class="py-2 px-3 rounded-lg bg-gray-50 hover:bg-emerald-50 hover:text-emerald-700 text-gray-600 font-medium transition">
                            Buka PO &rarr;
                        </a>
                    </div>
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

            // 1. Initialise Procurement Trend Chart
            const trendCtx = document.getElementById('procurementTrendChart').getContext('2d');
            let currentChartType = 'bar';

            const trendChart = new Chart(trendCtx, {
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
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    family: "'Figtree', system-ui, sans-serif",
                                    size: 11,
                                    weight: 500
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { family: "'Figtree', system-ui, sans-serif", size: 12, weight: 600 },
                            bodyFont: { family: "'Figtree', system-ui, sans-serif", size: 11 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                title: function (items) {
                                    const index = items[0].dataIndex;
                                    return fullLabels[index] || labels[index];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { family: "'Figtree', system-ui, sans-serif", size: 11 },
                                color: '#6b7280'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f3f4f6' },
                            ticks: {
                                precision: 0,
                                font: { family: "'Figtree', system-ui, sans-serif", size: 11 },
                                color: '#6b7280'
                            }
                        }
                    }
                }
            });

            // Filter Tabs Handler
            const filterTabs = document.querySelectorAll('.filter-tab');
            filterTabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const filter = this.getAttribute('data-filter');

                    filterTabs.forEach(t => {
                        t.classList.remove('active', 'border-indigo-600', 'bg-indigo-600', 'text-white');
                        t.classList.add('border-gray-200', 'bg-white', 'text-gray-600');
                    });
                    this.classList.add('active', 'border-indigo-600', 'bg-indigo-600', 'text-white');
                    this.classList.remove('border-gray-200', 'bg-white', 'text-gray-600');

                    if (filter === 'all') {
                        trendChart.data.datasets = datasets;
                    } else {
                        trendChart.data.datasets = datasets.filter(ds => ds.key === filter);
                    }
                    trendChart.update();
                });
            });

            // Bar vs Line Toggle Handler
            const btnBar = document.getElementById('btnChartBar');
            const btnLine = document.getElementById('btnChartLine');

            function setChartType(type) {
                if (type === currentChartType) return;
                currentChartType = type;

                if (type === 'bar') {
                    btnBar.className = 'chart-type-btn px-3 py-1.5 rounded-md bg-white text-gray-800 font-semibold shadow-sm transition cursor-pointer';
                    btnLine.className = 'chart-type-btn px-3 py-1.5 rounded-md text-gray-500 hover:text-gray-800 transition cursor-pointer';
                } else {
                    btnLine.className = 'chart-type-btn px-3 py-1.5 rounded-md bg-white text-gray-800 font-semibold shadow-sm transition cursor-pointer';
                    btnBar.className = 'chart-type-btn px-3 py-1.5 rounded-md text-gray-500 hover:text-gray-800 transition cursor-pointer';
                }

                trendChart.config.type = type;
                trendChart.data.datasets.forEach(ds => {
                    if (type === 'line') {
                        ds.fill = false;
                        ds.tension = 0.35;
                        ds.backgroundColor = ds.borderColor;
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
