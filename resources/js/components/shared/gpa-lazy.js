const original = new WeakMap();

const errorHtml = `
    <div class="flex flex-col items-center gap-3 rounded-2xl border border-line-hair bg-white p-8 text-center">
        <p class="text-sm text-ink-body">Gagal memuat data.</p>
        <button type="button" data-lazy-retry class="rounded-lg bg-accent px-4 py-2 font-mono text-xs font-bold uppercase tracking-wider text-brand">Coba Lagi</button>
    </div>
`;

async function load(el) {
    if (!original.has(el)) {
        original.set(el, el.innerHTML);
    }

    el.innerHTML = original.get(el);
    el.setAttribute('aria-busy', 'true');
    window.gpaProgress?.start();

    try {
        const res = await fetch(el.dataset.lazySrc, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!res.ok) {
            throw new Error(res.status);
        }

        el.innerHTML = await res.text();
    } catch {
        el.innerHTML = errorHtml;
        el.querySelector('[data-lazy-retry]').addEventListener('click', () => load(el));
    }

    el.removeAttribute('aria-busy');
    window.gpaProgress?.done();
}

document.querySelectorAll('[data-lazy-src]').forEach(load);
