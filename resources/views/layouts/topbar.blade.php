<header x-data="{
    // Search state
    searchQuery: '',
    searchResults: [],
    searchLoading: false,
    searchOpen: false,
    async performSearch() {
        const q = this.searchQuery.trim();
        if (q.length < 2) {
            this.searchResults = [];
            this.searchOpen = false;
            return;
        }
        this.searchLoading = true;
        this.searchOpen = true;
        try {
            const res = await fetch(`{{ route('search.quick') }}?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            this.searchResults = data.results || [];
        } catch (e) {
            this.searchResults = [];
        } finally {
            this.searchLoading = false;
        }
    },
    clearSearch() {
        this.searchQuery = '';
        this.searchResults = [];
        this.searchOpen = false;
    },

    // Notification state
    notificationOpen: false,
    notifications: [],
    unreadCount: 0,
    notificationLoading: false,
    async loadNotifications() {
        this.notificationLoading = true;
        try {
            const res = await fetch(`{{ route('notifications.feed') }}`);
            const data = await res.json();
            this.notifications = data.notifications || [];
            this.unreadCount = data.unread_count || 0;
        } catch (e) {
            this.notifications = [];
        } finally {
            this.notificationLoading = false;
        }
    },
    markAllAsRead() {
        this.unreadCount = 0;
    },

    // Help modal state
    helpOpen: false,
    helpTab: 'workflow',

    init() {
        this.loadNotifications();
        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                this.$refs.globalSearchInput?.focus();
            }
        });
    }
}" class="sticky top-0 z-20 bg-white border-b border-gray-100 h-16 flex items-center gap-4 px-4 lg:px-8">

    {{-- Spacer buat tombol hamburger mobile --}}
    <div class="w-10 lg:hidden shrink-0"></div>

    {{-- 1. Search Bar with Live Autocomplete Dropdown --}}
    <div class="relative flex-1 max-w-md" @click.outside="searchOpen = false">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
            <template x-if="!searchLoading">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
            </template>
            <template x-if="searchLoading">
                <svg class="animate-spin w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </template>
        </span>

        <input x-ref="globalSearchInput"
               type="text"
               x-model="searchQuery"
               @input.debounce.250ms="performSearch()"
               @focus="if(searchQuery.trim().length >= 2) searchOpen = true"
               @keydown.escape="searchOpen = false"
               placeholder="Cari dokumen (MR, RLP, PR, PO, Vendor)..."
               class="w-full bg-gray-50 border-gray-200 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 rounded-lg text-sm pl-10 pr-16 py-2 placeholder:text-gray-400 transition shadow-sm">

        {{-- Right Controls: Clear or Shortcut Key --}}
        <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
            <button x-show="searchQuery.length > 0"
                    x-cloak
                    @click="clearSearch()"
                    type="button"
                    class="p-1 rounded text-gray-400 hover:text-gray-600 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-semibold text-gray-400 bg-gray-100 border border-gray-200 rounded shadow-xs">
                Ctrl K
            </kbd>
        </div>

        {{-- Search Results Dropdown --}}
        <div x-show="searchOpen"
             x-cloak
             x-transition:enter="ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">

            <div class="p-2 border-b border-gray-100 flex items-center justify-between text-xs text-gray-500 bg-gray-50/75">
                <span class="font-medium">Hasil Pencarian</span>
                <span x-text="searchResults.length + ' item ditemukan'"></span>
            </div>

            <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                <template x-for="(item, idx) in searchResults" :key="idx">
                    <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-indigo-50/50 transition group">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold border shrink-0"
                              :class="item.badge_class"
                              x-text="item.badge">
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800 truncate group-hover:text-indigo-600 transition" x-text="item.title"></p>
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 shrink-0" x-text="item.status"></span>
                            </div>
                            <p class="text-xs text-gray-500 truncate" x-text="item.subtitle"></p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </template>

                <div x-show="searchResults.length === 0 && !searchLoading" class="py-8 text-center px-4">
                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-gray-500 font-medium">Tidak ada dokumen yang sesuai dengan kata kunci.</p>
                </div>
            </div>

            <div class="p-2 bg-gray-50 text-[11px] text-gray-400 text-center border-t border-gray-100">
                Tekan <kbd class="px-1 py-0.5 bg-white border border-gray-200 rounded font-mono text-[10px]">Esc</kbd> untuk menutup
            </div>
        </div>
    </div>

    <div class="flex items-center gap-1 ml-auto shrink-0">

        {{-- 2. Notification Bell with Dropdown Panel --}}
        <div class="relative" @click.outside="notificationOpen = false">
            <button @click="notificationOpen = !notificationOpen"
                    type="button"
                    title="Notifikasi"
                    class="relative w-9 h-9 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-show="unreadCount > 0"
                      x-cloak
                      class="absolute top-1.5 right-1.5 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-[10px] font-bold text-white flex items-center justify-center ring-2 ring-white"
                      x-text="unreadCount">
                </span>
            </button>

            {{-- Notification Dropdown --}}
            <div x-show="notificationOpen"
                 x-cloak
                 x-transition:enter="ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-100"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50">

                <div class="p-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/75">
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-sm text-gray-800">Notifikasi Pengadaan</h4>
                        <span x-show="unreadCount > 0"
                              x-cloak
                              class="px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[11px] font-bold"
                              x-text="unreadCount + ' Baru'">
                        </span>
                    </div>
                    <button x-show="unreadCount > 0"
                            x-cloak
                            @click="markAllAsRead()"
                            type="button"
                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        Tandai dibaca
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                    <template x-for="item in notifications" :key="item.id">
                        <a :href="item.url" class="flex items-start gap-3 p-3.5 hover:bg-gray-50 transition block">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5"
                                  :class="item.badge_class"
                                  x-text="item.badge">
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <p class="text-xs font-semibold text-gray-800 truncate" x-text="item.title"></p>
                                    <span class="text-[10px] text-gray-400 shrink-0" x-text="item.time"></span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-2 mt-0.5" x-text="item.message"></p>
                            </div>
                        </a>
                    </template>

                    <div x-show="notifications.length === 0" class="py-8 text-center px-4">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-xs text-gray-500 font-medium">Semua dokumen dalam kondisi terkini. Tidak ada notifikasi tertunda.</p>
                    </div>
                </div>

                <div class="p-2.5 bg-gray-50 text-center border-t border-gray-100">
                    <a href="{{ route('material-requests.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        Lihat Seluruh Material Request &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- 3. Help Center (?) Icon with Interactive Modal --}}
        <div>
            <button @click="helpOpen = true"
                    type="button"
                    title="Pusat Bantuan & Panduan Sistem"
                    class="w-9 h-9 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-50 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <circle cx="12" cy="17" r="0.75" fill="currentColor"/>
                </svg>
            </button>

            {{-- Help Modal --}}
            <template x-teleport="body">
                <div x-show="helpOpen"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     @keydown.escape.window="helpOpen = false">

                    {{-- Backdrop --}}
                    <div x-show="helpOpen"
                         x-transition:enter="ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="helpOpen = false"
                         class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"></div>

                    {{-- Modal Content --}}
                    <div x-show="helpOpen"
                         x-transition:enter="ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="relative w-full max-w-xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-10">

                        {{-- Modal Header --}}
                        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/75">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Pusat Bantuan & Panduan Sistem</h3>
                                    <p class="text-xs text-gray-500">e-Procurement Management System</p>
                                </div>
                            </div>
                            <button @click="helpOpen = false" type="button" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Modal Tabs --}}
                        <div class="px-6 pt-3 border-b border-gray-100 flex gap-4 text-xs font-semibold">
                            <button @click="helpTab = 'workflow'"
                                    :class="helpTab === 'workflow' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                                    class="pb-2.5 border-b-2 transition">
                                Alur Dokumen (Workflow)
                            </button>
                            <button @click="helpTab = 'shortcuts'"
                                    :class="helpTab === 'shortcuts' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                                    class="pb-2.5 border-b-2 transition">
                                Pintasan Keyboard
                            </button>
                            <button @click="helpTab = 'support'"
                                    :class="helpTab === 'support' ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700'"
                                    class="pb-2.5 border-b-2 transition">
                                Kontak Bantuan
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="p-6 max-h-96 overflow-y-auto">

                            {{-- Tab 1: Workflow --}}
                            <div x-show="helpTab === 'workflow'" class="space-y-4">
                                <div class="p-3 rounded-xl bg-indigo-50/50 border border-indigo-100 flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        MR
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-indigo-900">1. Material Request (MR)</h4>
                                        <p class="text-xs text-gray-600 mt-0.5">Pengajuan permintaan barang dari proyek/lapangan. Memerlukan persetujuan Approver A dan Approver C sebelum diproses pembayaran oleh Finance.</p>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-purple-50/50 border border-purple-100 flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-purple-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        RLP
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-purple-900">2. Request for Local Purchase (RRP)</h4>
                                        <p class="text-xs text-gray-600 mt-0.5">Pengadaan lokal cepat yang menyertakan perbandingan penawaran harga vendor, biaya tambahan, serta otorisasi berjenjang.</p>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-amber-50/50 border border-amber-100 flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-amber-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        PR
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-amber-900">3. Purchase Request (PR)</h4>
                                        <p class="text-xs text-gray-600 mt-0.5">Permohonan resmi pengadaan kebutuhan operasional ke bagian pengadaan dengan alur persetujuan terstruktur.</p>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-emerald-50/50 border border-emerald-100 flex items-start gap-3">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        PO
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-xs text-emerald-900">4. Purchase Order (PO)</h4>
                                        <p class="text-xs text-gray-600 mt-0.5">Penerbitan surat pesanan resmi kepada vendor yang telah lolos verifikasi harga, termin pembayaran, dan tanda tangan bertahap.</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Tab 2: Keyboard Shortcuts --}}
                            <div x-show="helpTab === 'shortcuts'" class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-100 text-xs">
                                    <span class="font-medium text-gray-700">Fokus ke Pencarian Cepat Dokumen</span>
                                    <div class="flex items-center gap-1">
                                        <kbd class="px-2 py-1 bg-white border border-gray-200 rounded font-mono font-semibold shadow-xs">Ctrl</kbd>
                                        <span class="text-gray-400">+</span>
                                        <kbd class="px-2 py-1 bg-white border border-gray-200 rounded font-mono font-semibold shadow-xs">K</kbd>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-100 text-xs">
                                    <span class="font-medium text-gray-700">Tutup Modal / Kotak Pencarian</span>
                                    <kbd class="px-2 py-1 bg-white border border-gray-200 rounded font-mono font-semibold shadow-xs">Esc</kbd>
                                </div>
                            </div>

                            {{-- Tab 3: Support Contact --}}
                            <div x-show="helpTab === 'support'" class="space-y-3 text-xs text-gray-600">
                                <p class="leading-relaxed">Jika Anda mengalami kendala operasional, error sistem, atau membutuhkan perubahan wewenang tanda tangan approval, silakan hubungi:</p>
                                <div class="p-4 rounded-xl bg-gray-50 border border-gray-100 space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="font-semibold text-gray-800">Email:</span>
                                        <span class="text-gray-600">support@eprocure.internal</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span class="font-semibold text-gray-800">Jam Operasional:</span>
                                        <span class="text-gray-600">Senin – Jumat, 08:00 – 17:00 WIB</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-100 flex justify-end">
                            <button @click="helpOpen = false" type="button" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                Mengerti & Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- User Profile Dropdown --}}
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="ml-1 flex items-center gap-2 pl-1 pr-2 py-1 rounded-full hover:bg-gray-50 transition">
                    <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-sm flex items-center justify-center">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </span>
                    <span class="hidden md:block text-sm text-gray-600 font-medium">{{ Auth::user()->name }}</span>
                    <svg class="hidden md:block w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7l5 5 5-5"/>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>

