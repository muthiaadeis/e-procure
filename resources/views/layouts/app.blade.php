{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'e-Procure') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

        <style>
            #nav-progress {
                position: fixed; top: 0; left: 0; height: 3px; width: 0;
                z-index: 100; background: #4f46e5; opacity: 0; pointer-events: none;
                transition: width .3s ease, opacity .2s ease;
            }
            #page-root.is-loading { opacity: .55; pointer-events: none; transition: opacity .15s; }

            /* ========================================================
               INTERACTIVE TACTILE & HOVER ANIMATIONS
               Efek animasi tombol dan kolom saat di-hover dan dipencet
               ======================================================== */
            button:not(:disabled):not([class*="fixed"]):not([class*="absolute"]):not(#signature-pad):not(canvas),
            a[class*="px-"][class*="py-"]:not(:disabled):not(.no-press),
            a[class*="inline-flex"][class*="rounded"]:not(:disabled):not(.no-press),
            input[type="submit"]:not(:disabled),
            input[type="button"]:not(:disabled) {
                transition: transform 0.16s cubic-bezier(0.34, 1.56, 0.64, 1),
                            box-shadow 0.16s cubic-bezier(0.34, 1.56, 0.64, 1),
                            background-color 0.15s ease,
                            border-color 0.15s ease,
                            color 0.15s ease,
                            filter 0.15s ease !important;
                will-change: transform, box-shadow;
                cursor: pointer;
            }

            /* Efek saat kursor diarahkan ke tombol (Hover: terangkat halus & glowing) */
            button:not(:disabled):not([class*="fixed"]):not([class*="absolute"]):not(#signature-pad):not(canvas):hover,
            a[class*="px-"][class*="py-"]:hover:not(.no-press),
            a[class*="inline-flex"][class*="rounded"]:hover:not(.no-press),
            input[type="submit"]:not(:disabled):hover,
            input[type="button"]:not(:disabled):hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px -1px rgba(0, 0, 0, 0.12), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                filter: brightness(1.02);
            }

            /* Efek saat tombol diklik / ditekan (Active: sensasi ditekan / dipencet ke dalam) */
            button:not(:disabled):not([class*="fixed"]):not([class*="absolute"]):not(#signature-pad):not(canvas):active,
            a[class*="px-"][class*="py-"]:active:not(.no-press),
            a[class*="inline-flex"][class*="rounded"]:active:not(.no-press),
            input[type="submit"]:not(:disabled):active,
            input[type="button"]:not(:disabled):active {
                transform: translateY(1.5px) scale(0.97) !important;
                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.15) !important;
                filter: brightness(0.94);
            }

            /* Ikon tombol aksi baris tabel (w-8 h-8) */
            .w-8.h-8.inline-flex:hover,
            .w-7.h-7.inline-flex:hover {
                transform: translateY(-1.5px) scale(1.08) !important;
            }
            .w-8.h-8.inline-flex:active,
            .w-7.h-7.inline-flex:active {
                transform: translateY(1px) scale(0.92) !important;
            }

            /* Baris & Kolom Tabel Interaktif */
            tbody tr.cursor-pointer,
            tbody tr[onclick],
            tbody tr[data-href] {
                transition: background-color 0.15s ease,
                            transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1),
                            box-shadow 0.15s ease !important;
                cursor: pointer;
                position: relative;
            }

            tbody tr.cursor-pointer:hover,
            tbody tr[onclick]:hover,
            tbody tr[data-href]:hover {
                background-color: #f1f5f9 !important;
                box-shadow: inset 3.5px 0 0 0 #6366f1, 0 2px 8px -2px rgba(99, 102, 241, 0.12);
            }

            tbody tr.cursor-pointer:active,
            tbody tr[onclick]:active,
            tbody tr[data-href]:active {
                background-color: #e2e8f0 !important;
                transform: scale(0.998);
            }

            /* Kartu Kolom Tahapan Persetujuan & Stat Cards */
            .approval-stage-card,
            .stat-card-interactive {
                transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1),
                            box-shadow 0.2s ease,
                            border-color 0.2s ease,
                            background-color 0.2s ease !important;
            }

            .approval-stage-card:hover,
            .stat-card-interactive:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 20px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            }

            .approval-stage-card:active,
            .stat-card-interactive:active {
                transform: translateY(-1px) scale(0.99);
            }

            /* Kolom Input Form (Search, Date, Filter, Select) */
            input[type="text"]:not([readonly]),
            input[type="search"],
            input[type="date"],
            input[type="number"],
            select {
                transition: border-color 0.15s ease,
                            box-shadow 0.15s ease,
                            background-color 0.15s ease,
                            transform 0.12s ease !important;
            }

            input[type="text"]:not([readonly]):hover,
            input[type="search"]:hover,
            input[type="date"]:hover,
            input[type="number"]:hover,
            select:hover {
                border-color: #94a3b8;
            }

            input[type="text"]:not([readonly]):focus,
            input[type="search"]:focus,
            input[type="date"]:focus,
            input[type="number"]:focus,
            select:focus {
                transform: translateY(-0.5px);
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div id="nav-progress"></div>

        {{-- Custom modal dialog pengganti alert() bawaan browser/windows --}}
        <x-system-alert-modal />

        {{-- Pesan penolakan akses (403) yang mulus: muncul di pojok kanan atas, hilang sendiri --}}
        @if (session('access_error'))
            <div x-data="{ show: true }"
                 x-init="setTimeout(() => show = false, 6000)"
                 x-show="show"
                 x-transition.opacity.duration.300ms
                 style="z-index: 120;"
                 class="fixed top-4 right-4 w-[calc(100%-2rem)] max-w-sm flex items-start gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm shadow-lg">
                <span class="flex-shrink-0 w-7 h-7 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </span>
                <span class="flex-1 font-medium">{{ session('access_error') }}</span>
                <button type="button" @click="show = false" class="flex-shrink-0 text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        <div class="min-h-screen lg:pl-64">
            @include('layouts.sidebar')

            <div class="flex flex-col min-h-screen">
                @include('layouts.topbar')

                {{-- Bagian yang diganti waktu pindah menu (sidebar & topbar tetap diam) --}}
                <div id="page-root" class="flex flex-col flex-1">
                    {{-- Page Heading --}}
                    @isset($header)
                        <div class="bg-gray-50 px-4 lg:px-8 pt-8 pb-2">
                            {{ $header }}
                        </div>
                    @endisset

                    <!-- Page Content -->
                    <main class="flex-1 px-4 lg:px-8 py-6">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        {{-- Kunci tanggal (29-09-2026) dan harga (Rp 1.000.000) di semua tabel supaya
             tidak turun ke baris bawah. Jalan ulang otomatis kalau isi tabel berubah
             (misalnya hasil search / filter AJAX). --}}
        <script>
            (function () {
                var PATTERN = /^(Rp\s?-?[\d.,]+|\d{2}-\d{2}-\d{4}(\s\d{2}:\d{2})?)$/;
                var queued = false;

                function apply() {
                    queued = false;
                    document.querySelectorAll('td, th').forEach(function (cell) {
                        if (cell.children.length === 0 && PATTERN.test(cell.textContent.trim())) {
                            cell.style.whiteSpace = 'nowrap';
                        }
                    });
                }

                function schedule() {
                    if (queued) return;
                    queued = true;
                    requestAnimationFrame(apply);
                }

                apply();
                new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    <script>
        // Auto-fit tanda tangan di halaman detail: potong margin kosong di sekitar goresan
        // supaya gambar yang kecil / miring / banyak ruang kosong jadi pas & di tengah kotak.
        // Berlaku untuk semua <img data-sig-fit>, termasuk yang src-nya diisi Alpine (modal detail).
        (function () {
            function trimImg(img) {
                try {
                    var w = img.naturalWidth, h = img.naturalHeight;
                    if (!w || !h) return null;
                    var c = document.createElement('canvas');
                    c.width = w; c.height = h;
                    var ctx = c.getContext('2d', { willReadFrequently: true });
                    ctx.drawImage(img, 0, 0);
                    var d = ctx.getImageData(0, 0, w, h).data;
                    var minX = w, minY = h, maxX = -1, maxY = -1;
                    for (var y = 0; y < h; y++) {
                        for (var x = 0; x < w; x++) {
                            var i = (y * w + x) * 4;
                            if (d[i + 3] > 30 && (d[i] * 299 + d[i + 1] * 587 + d[i + 2] * 114) / 1000 < 235) {
                                if (x < minX) minX = x;
                                if (x > maxX) maxX = x;
                                if (y < minY) minY = y;
                                if (y > maxY) maxY = y;
                            }
                        }
                    }
                    if (maxX < 0) return null;
                    var pad = Math.round(Math.max(maxX - minX, maxY - minY) * 0.03) + 2;
                    var cx = Math.max(0, minX - pad), cy = Math.max(0, minY - pad);
                    var cw = Math.min(w - cx, (maxX - minX) + pad * 2);
                    var ch = Math.min(h - cy, (maxY - minY) + pad * 2);
                    var o = document.createElement('canvas');
                    o.width = cw; o.height = ch;
                    o.getContext('2d').drawImage(img, cx, cy, cw, ch, 0, 0, cw, ch);
                    return o.toDataURL('image/png');
                } catch (e) { return null; }
            }
            function process(img) {
                if (!img.matches || !img.matches('img[data-sig-fit]')) return;
                var src = img.getAttribute('src');
                if (!src || img.dataset.sigDone === src) return;
                var go = function () {
                    var cur = img.getAttribute('src');
                    if (!cur || img.dataset.sigDone === cur) return;
                    var out = trimImg(img);
                    img.dataset.sigDone = out || cur;
                    if (out) img.setAttribute('src', out);
                };
                if (img.complete && img.naturalWidth) go();
                else img.addEventListener('load', go, { once: true });
            }
            function scan(root) {
                if (root.nodeType !== 1) return;
                process(root);
                root.querySelectorAll && root.querySelectorAll('img[data-sig-fit]').forEach(process);
            }
            new MutationObserver(function (records) {
                records.forEach(function (r) {
                    if (r.type === 'attributes') process(r.target);
                    else r.addedNodes.forEach(scan);
                });
            }).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });
            document.addEventListener('DOMContentLoaded', function () { scan(document.body); });
        })();
    </script>

    <script>
        // ------------------------------------------------------------------
        // 1) Pindah menu tanpa reload sidebar & topbar
        //    Klik link di sidebar -> fetch halaman -> ganti isi #page-root saja.
        // ------------------------------------------------------------------
        (function () {
            var root = document.getElementById('page-root');
            var bar = document.getElementById('nav-progress');
            if (!root || !window.fetch || !window.DOMParser) return;

            // Bagian sidebar yang ikut disinkronkan (highlight menu aktif, badge draft, dst)
            var SIDEBAR_PARTS = ['aside nav', 'aside .border-t.shrink-0'];

            var currentKey = location.pathname + location.search;
            var abortCtl = null;
            var navToken = 0;

            // Script eksternal yang sudah termuat (signature_pad, chart.js, dll) tidak dimuat ulang
            var loaded = {};
            document.querySelectorAll('script[src]').forEach(function (s) { loaded[s.src] = true; });

            function progress(state) {
                if (!bar) return;
                if (state === 'start') {
                    bar.style.transition = 'none';
                    bar.style.width = '0';
                    bar.style.opacity = '1';
                    void bar.offsetWidth;
                    bar.style.transition = '';
                    bar.style.width = '70%';
                    root.classList.add('is-loading');
                } else if (state === 'done') {
                    bar.style.width = '100%';
                    root.classList.remove('is-loading');
                    setTimeout(function () { bar.style.opacity = '0'; }, 250);
                } else {
                    bar.style.opacity = '0';
                    root.classList.remove('is-loading');
                }
            }

            function hardNav(url, isPop) {
                if (isPop) location.reload();
                else location.href = url;
            }

            function loadExternal(src) {
                if (loaded[src]) return Promise.resolve();
                return new Promise(function (resolve) {
                    var s = document.createElement('script');
                    s.src = src;
                    s.onload = function () { loaded[src] = true; resolve(); };
                    s.onerror = function () { resolve(); };
                    document.head.appendChild(s);
                });
            }

            function closeMobileSidebar() {
                var aside = document.querySelector('aside');
                if (aside && window.Alpine) {
                    var d = window.Alpine.$data(aside);
                    if (d) d.mobileOpen = false;
                }
            }

            // Ganti isi halaman. Semua dijalankan SINKRON supaya fungsi dari <script> halaman
            // (misal rlpForm) sudah ada sebelum Alpine menginisialisasi x-data yang memakainya.
            function swap(doc, next) {
                document.title = doc.title || document.title;

                SIDEBAR_PARTS.forEach(function (sel) {
                    var cur = document.querySelector(sel);
                    var nu = doc.querySelector(sel);
                    if (cur && nu) cur.innerHTML = nu.innerHTML;
                });

                var domReady = [];
                var origAdd = document.addEventListener;
                var origWrite = document.write;
                // Halaman lama pakai DOMContentLoaded (mis. dashboard) -> jalankan manual setelah swap
                document.addEventListener = function (type, fn, opts) {
                    if (type === 'DOMContentLoaded') { domReady.push(fn); return; }
                    return origAdd.call(document, type, fn, opts);
                };
                document.write = function () {};

                try {
                    if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                        try { window.Alpine.destroyTree(root); } catch (e) {}
                    }

                    root.replaceChildren.apply(root, Array.prototype.slice.call(next.childNodes));

                    root.querySelectorAll('script').forEach(function (old) {
                        if (old.src) { old.remove(); return; } // sudah dimuat sebelum swap
                        var s = document.createElement('script');
                        if (old.type) s.type = old.type;
                        s.textContent = old.textContent;
                        old.replaceWith(s); // script inline langsung dieksekusi di sini
                    });

                    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                        try { window.Alpine.initTree(root); } catch (e) { console.error('Alpine initTree error:', e); }
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    document.addEventListener = origAdd;
                    document.write = origWrite;
                }

                setTimeout(function () {
                    domReady.forEach(function (fn) {
                        try { fn.call(document, new Event('DOMContentLoaded')); } catch (e) { console.error(e); }
                    });
                }, 0);
            }

            async function go(url, push, isPop) {
                var token = ++navToken;
                if (abortCtl) abortCtl.abort();
                abortCtl = new AbortController();
                progress('start');

                var res;
                try {
                    // Sengaja TIDAK kirim X-Requested-With: beberapa controller (MR, Vendor, Job Code)
                    // membalas partial/JSON kalau request-nya AJAX.
                    res = await fetch(url, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'text/html' },
                        signal: abortCtl.signal
                    });
                } catch (e) {
                    if (e.name === 'AbortError') return;
                    progress('reset');
                    return hardNav(url, isPop);
                }
                if (token !== navToken) return;

                var type = res.headers.get('content-type') || '';
                if (!res.ok || type.indexOf('text/html') === -1) {
                    progress('reset');
                    return hardNav(url, isPop);
                }

                var html = await res.text();
                if (token !== navToken) return;

                var doc = new DOMParser().parseFromString(html, 'text/html');
                var next = doc.getElementById('page-root');

                // Bukan halaman ber-layout app (mis. login / print), atau halaman form dengan
                // autosave draft (draft.js hanya aktif saat page load) -> reload biasa.
                if (!next || next.querySelector('form[data-draft]')) {
                    progress('reset');
                    return hardNav(res.url || url, isPop);
                }

                // Muat library eksternal dulu (berurutan), baru swap
                var srcs = [];
                next.querySelectorAll('script[src]').forEach(function (s) {
                    if (!loaded[s.src]) srcs.push(s.src);
                });
                for (var i = 0; i < srcs.length; i++) { await loadExternal(srcs[i]); }
                if (token !== navToken) return;

                // URL harus sudah baru sebelum script halaman jalan (beberapa baca window.location)
                if (push) history.pushState({ spa: true }, '', res.url || url);
                currentKey = location.pathname + location.search;

                swap(doc, next);
                window.scrollTo(0, 0);
                progress('done');

                if (window.__pollNotifications) window.__pollNotifications();
            }

            document.addEventListener('click', function (e) {
                if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                var a = e.target.closest ? e.target.closest('aside a[href]') : null;
                if (!a) return;
                if ((a.target && a.target !== '_self') || a.hasAttribute('download')) return;

                var url = new URL(a.href, location.href);
                if (url.origin !== location.origin) return;

                e.preventDefault();
                closeMobileSidebar();

                var same = url.pathname + url.search === location.pathname + location.search;
                go(url.href, !same, false);
            });

            window.addEventListener('popstate', function () {
                if (location.pathname + location.search === currentKey) return;
                go(location.href, false, true);
            });
        })();
    </script>

    {{-- Floating Toast Real-Time Dokumen Baru --}}
        <div id="live-doc-toast"
             style="z-index: 130;"
             class="fixed top-5 right-5 w-[calc(100%-2rem)] max-w-sm bg-white border border-indigo-200/90 rounded-2xl shadow-2xl p-4 flex items-start gap-3.5 transition-all duration-300 transform -translate-y-6 opacity-0 pointer-events-none">
            <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mt-0.5 shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-1.5 mb-1">
                    <span data-toast-badge class="px-1.5 py-0.5 rounded text-[10px] font-bold border bg-indigo-50 text-indigo-700 border-indigo-200">MR</span>
                    <span class="text-xs font-bold text-gray-900">Dokumen Baru Masuk</span>
                </div>
                <p data-toast-title class="text-xs font-bold text-indigo-600 truncate"></p>
                <p data-toast-message class="text-xs text-gray-500 mt-0.5 line-clamp-2"></p>
                <div class="mt-2.5 flex items-center gap-3">
                    <a data-toast-link href="#" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                        Buka Dokumen
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            <button type="button" onclick="window.dismissLiveToast()" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    <script>
        // ------------------------------------------------------------------
        // 2) Real-Time Sync: Dokumen baru otomatis masuk tanpa perlu reload
        // ------------------------------------------------------------------
        (function () {
            var INTERVAL_MS = 6000;
            var lastSystemVersion = null;
            var currentUserId = {{ auth()->id() ?? 'null' }};
            var isFirstCheck = true;

            function playNotificationChime() {
                try {
                    var AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    var ctx = new AudioCtx();
                    if (ctx.state === 'suspended') return;
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.4);
                } catch (e) {}
            }

            function showLiveToast(doc) {
                var toast = document.getElementById('live-doc-toast');
                if (!toast || !doc) return;

                var badge = toast.querySelector('[data-toast-badge]');
                var title = toast.querySelector('[data-toast-title]');
                var msg = toast.querySelector('[data-toast-message]');
                var link = toast.querySelector('[data-toast-link]');

                if (badge) {
                    badge.textContent = doc.type || 'DOC';
                    badge.className = 'px-1.5 py-0.5 rounded text-[10px] font-bold border ' + (doc.badge_class || 'bg-indigo-50 text-indigo-700 border-indigo-200');
                }
                if (title) title.textContent = doc.number || 'Dokumen Baru';
                if (msg) msg.textContent = 'Dibuat oleh ' + (doc.creator_name || 'Staff') + ' • ' + (doc.module_title || doc.type);
                if (link && doc.url) {
                    link.href = doc.url;
                }

                toast.classList.remove('-translate-y-6', 'opacity-0', 'pointer-events-none');
                toast.classList.add('translate-y-0', 'opacity-100');

                if (window.__toastHideTimer) clearTimeout(window.__toastHideTimer);
                window.__toastHideTimer = setTimeout(function () {
                    window.dismissLiveToast();
                }, 7000);
            }

            window.dismissLiveToast = function() {
                var toast = document.getElementById('live-doc-toast');
                if (toast) {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('-translate-y-6', 'opacity-0', 'pointer-events-none');
                }
            };

            async function refreshPageDataQuietly() {
                // Dispatch event untuk view yang punya custom handler (misal material requests)
                window.dispatchEvent(new CustomEvent('document-list-refresh'));
                window.dispatchEvent(new CustomEvent('mr-list-refresh'));

                var targetIds = [
                    'rlp-table-container',
                    'pr-table-container',
                    'po-table-container',
                    'dashboard-stats-container',
                    'dashboard-activities-container',
                    'dashboard-forme-container'
                ];

                var found = targetIds.filter(function (id) { return !!document.getElementById(id); });
                if (found.length === 0) return;

                try {
                    var res = await fetch(window.location.href, {
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) return;
                    var html = await res.text();
                    var doc = new DOMParser().parseFromString(html, 'text/html');

                    found.forEach(function (id) {
                        var currentEl = document.getElementById(id);
                        var newEl = doc.getElementById(id);
                        if (currentEl && newEl && currentEl.innerHTML !== newEl.innerHTML) {
                            currentEl.innerHTML = newEl.innerHTML;
                        }
                    });
                } catch (e) {}
            }

            async function poll() {
                var header = document.querySelector('header[x-data]');
                if (!header || !window.Alpine) return;
                var d = window.Alpine.$data(header);
                if (!d || d.notificationLoading) return;

                var data = await d.loadNotifications();
                if (!data) return;

                var version = data.system_version;
                if (!version) return;

                if (isFirstCheck) {
                    isFirstCheck = false;
                    lastSystemVersion = version;
                    return;
                }

                if (version !== lastSystemVersion) {
                    lastSystemVersion = version;

                    // Dokumen baru atau perubahan alur terdeteksi -> sinkronkan isi tabel/dashboard otomatis
                    refreshPageDataQuietly();

                    // Tampilkan toast & chime audio jika dokumen baru dibuat oleh pengguna lain
                    if (data.latest_document) {
                        var isMe = currentUserId && data.latest_document.creator_id === currentUserId;
                        if (!isMe) {
                            playNotificationChime();
                            showLiveToast(data.latest_document);
                        }
                    }
                }
            }

            window.__pollNotifications = poll;

            setInterval(function () {
                if (document.visibilityState === 'visible') poll();
            }, INTERVAL_MS);

            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'visible') poll();
            });
            window.addEventListener('focus', poll);
        })();
    </script>
    </body>
</html>
