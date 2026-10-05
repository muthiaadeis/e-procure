// resources/js/draft.js
// Autosave draft untuk form dokumen. Aktif di <form data-draft="...">.

const SAVE_DELAY = 1500;
const SKIP_NAMES = new Set(['_token', '_method', 'signature']);
const SKIP_TYPES = new Set(['file', 'password', 'submit', 'button', 'hidden']);

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const headers = () => ({
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-CSRF-TOKEN': csrf(),
    'X-Requested-With': 'XMLHttpRequest',
});

// Field yang dikelola Alpine (x-model / name dinamis) disimpan lewat "state", bukan lewat field.
function isAlpineManaged(el) {
    if (/\[\d+\]/.test(el.name)) return true;
    return Array.from(el.attributes).some(
        (a) => a.name.startsWith('x-model') || a.name === ':name' || a.name === 'x-bind:name'
    );
}

function collectFields(form) {
    const out = {};
    Array.from(form.elements).forEach((el) => {
        if (!el.name || el.disabled || el.readOnly) return;
        if (SKIP_NAMES.has(el.name) || SKIP_TYPES.has(el.type)) return;
        if (isAlpineManaged(el)) return;

        if (el.type === 'checkbox') out[el.name] = el.checked;
        else if (el.type === 'radio') { if (el.checked) out[el.name] = el.value; }
        else out[el.name] = el.value;
    });
    return out;
}

function applyFields(form, fields) {
    Object.entries(fields || {}).forEach(([name, val]) => {
        form.querySelectorAll('[name="' + CSS.escape(name) + '"]').forEach((el) => {
            if (el.type === 'checkbox') el.checked = !!val;
            else if (el.type === 'radio') el.checked = el.value === val;
            else el.value = val;
            el.dispatchEvent(new Event('input', { bubbles: true }));
            el.dispatchEvent(new Event('change', { bubbles: true }));
        });
    });
}

const collectState = (data, keys) =>
    Object.fromEntries(keys.map((k) => [k, JSON.parse(JSON.stringify(data[k] ?? null))]));

function applyState(data, state, keys) {
    keys.forEach((k) => { if (state && k in state) data[k] = state[k]; });
}

function ui() {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:60;display:flex;flex-direction:column;align-items:flex-end;gap:8px;font-family:inherit;';
    const pill = document.createElement('div');
    pill.style.cssText = 'font-size:12px;color:#6b7280;background:#fff;border:1px solid #e5e7eb;border-radius:999px;padding:4px 12px;box-shadow:0 1px 2px rgba(0,0,0,.05);display:none;';
    wrap.appendChild(pill);
    document.body.appendChild(wrap);

    return {
        status(text) { pill.textContent = text; pill.style.display = 'block'; },
        prompt(whenText, onResume, onDiscard) {
            const card = document.createElement('div');
            card.style.cssText = 'max-width:340px;background:#fffbeb;border:1px solid #fde68a;color:#92400e;border-radius:12px;padding:14px 16px;font-size:13px;box-shadow:0 10px 15px -3px rgba(0,0,0,.1);';
            card.innerHTML = '<div style="font-weight:600;margin-bottom:4px;">Ada draft yang belum selesai</div>'
                + '<div style="margin-bottom:10px;">Terakhir disimpan ' + whenText + '. Mau dilanjutkan?</div>';
            const row = document.createElement('div');
            row.style.cssText = 'display:flex;gap:8px;';
            const mk = (label, primary, fn) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = label;
                b.style.cssText = 'font-size:12px;font-weight:600;padding:6px 12px;border-radius:8px;cursor:pointer;'
                    + (primary ? 'background:#4f46e5;color:#fff;border:1px solid #4f46e5;' : 'background:#fff;color:#4b5563;border:1px solid #d1d5db;');
                b.addEventListener('click', () => { card.remove(); fn(); });
                return b;
            };
            row.append(mk('Lanjutkan', true, onResume), mk('Mulai baru', false, onDiscard));
            card.appendChild(row);
            wrap.prepend(card);
        },
    };
}

function initDraft(form) {
    const cfg = {
        form: form.dataset.draft,
        context: form.dataset.draftContext || '',
        url: form.dataset.draftUrl,
        keys: (form.dataset.draftState || '').split(',').map((s) => s.trim()).filter(Boolean),
        skipPrompt: form.dataset.draftSkip === '1',
        skipPrompt: form.dataset.draftSkip === '1',
        resume: form.dataset.draftResume === '1',
    };
    const root = form.closest('[x-data]');
    const data = root ? window.Alpine.$data(root) : {};
    const widget = ui();

    const snapshot = () => JSON.stringify({ fields: collectFields(form), state: collectState(data, cfg.keys) });

    let baseline = snapshot();   // kondisi form waktu baru dibuka (kosong / prefill)
    let last = baseline;
    let timer = null;
    let busy = false;            // lagi restore
    let submitted = false;
    let hasDraft = false;

    const query = () => '?form=' + encodeURIComponent(cfg.form) + '&context=' + encodeURIComponent(cfg.context);
    const clock = () => new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    async function discard() {
        hasDraft = false;
        try { await fetch(cfg.url + query(), { method: 'DELETE', headers: headers() }); } catch (e) { /* diam aja */ }
    }

    async function save(keepalive = false) {
        if (busy || submitted) return;
        const snap = snapshot();
        if (snap === last) return;

        if (snap === baseline) {          // user balikin form ke kondisi awal
            last = snap;
            if (hasDraft) discard();
            return;
        }

        last = snap;
        try {
            const res = await fetch(cfg.url, {
                method: 'POST',
                keepalive,
                headers: headers(),
                body: JSON.stringify({ form: cfg.form, context: cfg.context, payload: snap }),
            });
            if (!res.ok) throw new Error(res.status);
            hasDraft = true;
            widget.status('Draft tersimpan ' + clock());
        } catch (e) {
            last = null;
            widget.status('Draft gagal tersimpan');
        }
    }

    const schedule = () => {
        if (busy || submitted) return;
        clearTimeout(timer);
        timer = setTimeout(() => save(), SAVE_DELAY);
    };

    const flush = () => { clearTimeout(timer); save(true); };

    function restore(payload) {
        busy = true;
        try {
            const parsed = JSON.parse(payload);
            applyState(data, parsed.state, cfg.keys);
            window.Alpine.nextTick(() => {
                applyFields(form, parsed.fields);
                busy = false;
                last = snapshot();
                hasDraft = true;
                widget.status('Draft dipulihkan');
            });
        } catch (e) {
            busy = false;
        }
    }

    // ---- listener autosave ----
    form.addEventListener('input', schedule);
    form.addEventListener('change', schedule);
    window.Alpine.effect(() => { collectState(data, cfg.keys); schedule(); });   // perubahan state Alpine (add item, dropdown, dst)
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') flush(); });
    window.addEventListener('pagehide', flush);
    form.addEventListener('submit', (e) => {
        if (e.defaultPrevented) return;   // gagal validasi client-side, draft tetap jalan
        submitted = true;
        clearTimeout(timer);
    });

    // ---- cek draft yang sudah ada ----
    if (cfg.skipPrompt) return;   // halaman ini hasil redirect validasi error (old input sudah ada)

    fetch(cfg.url + query(), { headers: headers() })
        .then((r) => (r.ok ? r.json() : null))
        .then((res) => {
            if (!res || !res.exists) return;
            if (cfg.resume) { restore(res.payload); return; }   // datang dari list draft: langsung pulihkan
            const when = new Date(res.updated_at).toLocaleString('id-ID', {
                day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
            });
            widget.prompt(when, () => restore(res.payload), discard);
        })
        .catch(() => {});
}

document.addEventListener('alpine:initialized', () => {
    const form = document.querySelector('form[data-draft]');
    if (form) initDraft(form);
});
