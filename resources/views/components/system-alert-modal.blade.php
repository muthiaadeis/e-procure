{{-- resources/views/components/system-alert-modal.blade.php --}}
{{-- Modal alert & confirm kustom yang identik dengan modal logout / sidebar --}}
<div id="system-dialog-root"
     class="fixed inset-0 flex items-center justify-center bg-gray-900/50 px-4 transition-opacity duration-150 ease-out opacity-0 pointer-events-none"
     style="display: none; z-index: 99999;"
     aria-modal="true" role="dialog">

    <div id="system-dialog-card"
         class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center transform scale-95 transition-all duration-150 ease-out opacity-0">

        {{-- Icon container bulat --}}
        <div id="system-dialog-icon-wrapper" class="flex items-center justify-center w-12 h-12 rounded-full mx-auto bg-amber-50">
            <div id="system-dialog-icon-slot">
                {{-- Default warning icon --}}
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
        </div>

        {{-- Judul --}}
        <h3 id="system-dialog-title" class="text-lg font-semibold text-gray-800 text-center mt-4">
            Perhatian
        </h3>

        {{-- Pesan teks --}}
        <p id="system-dialog-message" class="text-sm text-gray-500 text-center mt-1 leading-relaxed whitespace-pre-line break-words max-h-72 overflow-y-auto px-1">
            Pesan pemberitahuan
        </p>

        {{-- Tombol aksi --}}
        <div class="mt-6 flex gap-3" id="system-dialog-actions">
            <button type="button" id="system-dialog-cancel-btn"
                    class="hidden flex-1 py-2.5 text-sm font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition cursor-pointer">
                Batal
            </button>
            <button type="button" id="system-dialog-confirm-btn"
                    class="w-full py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const ICONS = {
        warning: `<svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>`,
        error: `<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
        danger: `<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`,
        success: `<svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`,
        info: `<svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`
    };

    const ICON_WRAPPER_CLASSES = {
        warning: 'bg-amber-50',
        error: 'bg-red-50',
        danger: 'bg-red-50',
        success: 'bg-emerald-50',
        info: 'bg-indigo-50'
    };

    let dialogQueue = [];
    let isDialogOpen = false;
    let isCurrentConfirm = false;
    let currentResolver = null;

    function getElements() {
        return {
            root: document.getElementById('system-dialog-root'),
            card: document.getElementById('system-dialog-card'),
            iconWrapper: document.getElementById('system-dialog-icon-wrapper'),
            iconSlot: document.getElementById('system-dialog-icon-slot'),
            title: document.getElementById('system-dialog-title'),
            message: document.getElementById('system-dialog-message'),
            cancelBtn: document.getElementById('system-dialog-cancel-btn'),
            confirmBtn: document.getElementById('system-dialog-confirm-btn')
        };
    }

    function detectType(message) {
        const text = String(message || '').toLowerCase();
        // Cek validasi / warning terlebih dahulu (agar "salah satu" tidak memicu error)
        if (/harap|mohon|silakan|belum|lengkapi|minimal|wajib|perlu|pilih|perhatian|warning|uom|catatan/i.test(text)) {
            return 'warning';
        }
        if (/berhasil|sukses|success|selesai|disetujui/i.test(text)) {
            return 'success';
        }
        if (/\berror\b|\bgagal\b|\brusak\b|\bkesalahan\b|\bfailed\b|\binvalid\b/i.test(text)) {
            return 'error';
        }
        return 'info';
    }

    function detectDefaultTitle(type) {
        switch (type) {
            case 'warning': return 'Perhatian';
            case 'error': return 'Terjadi Kesalahan';
            case 'danger': return 'Konfirmasi Tindakan';
            case 'success': return 'Berhasil';
            default: return 'Pemberitahuan';
        }
    }

    function processQueue() {
        if (isDialogOpen || dialogQueue.length === 0) return;
        const item = dialogQueue.shift();
        show(item);
    }

    function show(item) {
        const els = getElements();
        if (!els.root) return;

        isDialogOpen = true;
        currentResolver = item.resolve;
        isCurrentConfirm = Boolean(item.isConfirm);

        const isConfirm = isCurrentConfirm;
        const type = item.type || detectType(item.message);
        const title = item.title || (isConfirm ? 'Konfirmasi' : detectDefaultTitle(type));
        const confirmText = item.confirmText || (isConfirm ? 'Ya, Lanjutkan' : 'Mengerti');
        const cancelText = item.cancelText || 'Batal';

        // Teks & icon
        els.title.textContent = title;
        els.message.textContent = item.message;
        els.iconSlot.innerHTML = ICONS[type] || ICONS.info;

        // Background wrapper icon (persis seperti logout: w-12 h-12 rounded-full)
        els.iconWrapper.className = `flex items-center justify-center w-12 h-12 rounded-full mx-auto ${ICON_WRAPPER_CLASSES[type] || 'bg-indigo-50'}`;

        // Styling tombol confirm
        els.confirmBtn.textContent = confirmText;
        if (isConfirm) {
            els.cancelBtn.classList.remove('hidden');
            els.cancelBtn.textContent = cancelText;

            if (type === 'danger' || type === 'error') {
                els.confirmBtn.className = 'flex-1 py-2.5 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition cursor-pointer';
            } else {
                els.confirmBtn.className = 'flex-1 py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer';
            }
        } else {
            els.cancelBtn.classList.add('hidden');
            if (type === 'error' || type === 'danger') {
                els.confirmBtn.className = 'w-full py-2.5 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition cursor-pointer';
            } else {
                els.confirmBtn.className = 'w-full py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer';
            }
        }

        // Tampilkan modal
        els.root.style.cssText = 'display: flex; z-index: 99999;';

        // Animasi transisi masuk
        requestAnimationFrame(() => {
            els.root.classList.remove('opacity-0', 'pointer-events-none');
            els.root.classList.add('opacity-100', 'pointer-events-auto');

            els.card.classList.remove('opacity-0', 'scale-95');
            els.card.classList.add('opacity-100', 'scale-100');

            setTimeout(() => {
                els.confirmBtn.focus();
            }, 50);
        });
    }

    function close(result) {
        if (!isDialogOpen) return;
        const els = getElements();

        if (els.root && els.card) {
            els.root.classList.remove('opacity-100', 'pointer-events-auto');
            els.root.classList.add('opacity-0', 'pointer-events-none');

            els.card.classList.remove('opacity-100', 'scale-100');
            els.card.classList.add('opacity-0', 'scale-95');
        }

        setTimeout(() => {
            if (els.root) els.root.style.cssText = 'display: none; z-index: 99999;';
            isDialogOpen = false;
            const resolver = currentResolver;
            currentResolver = null;
            if (resolver) resolver(result);
            processQueue();
        }, 150);
    }

    function initHandlers() {
        const els = getElements();
        if (!els.root) return;

        els.confirmBtn?.addEventListener('click', () => close(true));
        els.cancelBtn?.addEventListener('click', () => close(false));

        // Klik area backdrop di luar card menutup dialog
        els.root?.addEventListener('click', (e) => {
            if (e.target === els.root) {
                close(false);
            }
        });

        // Tombol Escape & Enter
        window.addEventListener('keydown', (e) => {
            if (!isDialogOpen) return;
            if (e.key === 'Escape') {
                e.preventDefault();
                close(false);
            } else if (e.key === 'Enter' && !isCurrentConfirm) {
                e.preventDefault();
                close(true);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHandlers);
    } else {
        initHandlers();
    }

    // Public API
    window.showAlert = function(message, options = {}) {
        return new Promise((resolve) => {
            dialogQueue.push({
                message: String(message || ''),
                title: options.title || null,
                type: options.type || null,
                confirmText: options.confirmText || 'Mengerti',
                isConfirm: false,
                resolve
            });
            processQueue();
        });
    };

    window.showConfirm = function(message, options = {}) {
        return new Promise((resolve) => {
            dialogQueue.push({
                message: String(message || ''),
                title: options.title || 'Konfirmasi',
                type: options.danger ? 'danger' : (options.type || 'warning'),
                confirmText: options.confirmText || 'Ya, Lanjutkan',
                cancelText: options.cancelText || 'Batal',
                isConfirm: true,
                resolve
            });
            processQueue();
        });
    };

    // Override alert bawaan browser
    window.alert = function(message, options = {}) {
        return window.showAlert(message, options);
    };

    window.modalAlert = window.showAlert;
    window.modalConfirm = window.showConfirm;
})();
</script>
