/**
 * AgroOrder GPA Custom Dialog Component
 * Menggantikan browser native alert(), confirm(), dan prompt()
 * agar tidak muncul pop up "127.0.0.1:8000 says" / "localhost says".
 */

let activeDialog = null;

const ICONS = {
    calendar: `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>`,
    danger: `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>`,
    warning: `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>`,
    edit: `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>`,
    info: `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>`,
    check: `<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>`,
};

function ensureDialogElements() {
    let container = document.getElementById('gpa-dialog-root');
    if (!container) {
        container = document.createElement('div');
        container.id = 'gpa-dialog-root';
        container.className = 'fixed inset-0 z-[999999] hidden items-center justify-center p-4 transition-all duration-200';
        container.setAttribute('role', 'dialog');
        container.setAttribute('aria-modal', 'true');
        container.innerHTML = `
            <div id="gpa-dialog-backdrop" class="fixed inset-0 z-0 bg-[#0c2401]/75 backdrop-blur-[3px] opacity-0 transition-opacity duration-200"></div>
            <div id="gpa-dialog-panel" class="relative z-10 w-full max-w-md transform scale-95 opacity-0 overflow-hidden rounded-2xl border border-line bg-surface p-6 shadow-2xl transition-all duration-200 sm:p-7">
                <div class="flex items-start gap-3.5">
                    <div id="gpa-dialog-icon-wrapper" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border">
                        <span id="gpa-dialog-icon"></span>
                    </div>
                    <div class="min-w-0 flex-1 pt-0.5">
                        <h3 id="gpa-dialog-title" class="text-base font-bold text-ink sm:text-lg"></h3>
                        <p id="gpa-dialog-message" class="mt-1.5 text-xs sm:text-sm leading-relaxed text-ink-muted"></p>
                    </div>
                    <button id="gpa-dialog-close" type="button" aria-label="Tutup" class="-mr-1.5 -mt-1.5 inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-subtle hover:bg-surface-muted hover:text-ink transition-colors cursor-pointer">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>
                <div id="gpa-dialog-input-wrapper" class="mt-4 hidden">
                    <input id="gpa-dialog-input" type="text" class="w-full rounded-xl border border-line bg-surface-muted/60 px-4 py-2.5 text-sm font-semibold text-ink placeholder:text-ink-subtle focus:border-brand focus:bg-surface focus:outline-none focus:ring-2 focus:ring-brand/30 transition-all" />
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-2.5">
                    <button id="gpa-dialog-cancel" type="button" class="inline-flex items-center justify-center rounded-xl border border-line bg-surface px-4 py-2.5 text-xs font-semibold text-ink-muted hover:bg-surface-muted hover:text-ink transition-colors cursor-pointer">Batal</button>
                    <button id="gpa-dialog-confirm" type="button" class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-xs font-bold transition-all shadow-sm cursor-pointer">Lanjutkan</button>
                </div>
            </div>
        `;
        document.body.appendChild(container);
    }

    // Attach static events if not already bound
    if (!container.dataset.gpaDialogBound) {
        container.dataset.gpaDialogBound = '1';

        const backdrop = container.querySelector('#gpa-dialog-backdrop');
        const closeBtn = container.querySelector('#gpa-dialog-close');
        const cancelBtn = container.querySelector('#gpa-dialog-cancel');
        const confirmBtn = container.querySelector('#gpa-dialog-confirm');
        const input = container.querySelector('#gpa-dialog-input');

        const cancelHandler = (e) => {
            if (e) e.preventDefault();
            closeActiveDialog(false);
        };
        const confirmHandler = (e) => {
            if (e) e.preventDefault();
            closeActiveDialog(true);
        };

        if (backdrop) backdrop.addEventListener('click', cancelHandler);
        if (closeBtn) closeBtn.addEventListener('click', cancelHandler);
        if (cancelBtn) cancelBtn.addEventListener('click', cancelHandler);
        if (confirmBtn) confirmBtn.addEventListener('click', confirmHandler);

        if (input) {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    confirmHandler(e);
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    cancelHandler(e);
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && activeDialog) {
                e.preventDefault();
                cancelHandler(e);
            }
        });
    }

    return container;
}

function openDialog(options) {
    return new Promise((resolve) => {
        const container = ensureDialogElements();
        const backdrop = container.querySelector('#gpa-dialog-backdrop');
        const panel = container.querySelector('#gpa-dialog-panel');
        const iconWrapper = container.querySelector('#gpa-dialog-icon-wrapper');
        const iconSpan = container.querySelector('#gpa-dialog-icon');
        const titleEl = container.querySelector('#gpa-dialog-title');
        const messageEl = container.querySelector('#gpa-dialog-message');
        const closeBtn = container.querySelector('#gpa-dialog-close');
        const inputWrapper = container.querySelector('#gpa-dialog-input-wrapper');
        const inputEl = container.querySelector('#gpa-dialog-input');
        const cancelBtn = container.querySelector('#gpa-dialog-cancel');
        const confirmBtn = container.querySelector('#gpa-dialog-confirm');

        const type = options.type || 'confirm';
        const tone = options.tone || 'brand';

        activeDialog = {
            options,
            resolve,
            container,
            backdrop,
            panel,
            inputEl,
        };

        // Text & Content
        titleEl.textContent = options.title || (type === 'prompt' ? 'Input Diperlukan' : (type === 'alert' ? 'Pemberitahuan' : 'Konfirmasi Tindakan'));
        messageEl.textContent = options.message || '';

        // Input Setup for Prompt
        if (type === 'prompt') {
            inputWrapper.classList.remove('hidden');
            inputEl.type = options.inputType || 'text';
            inputEl.value = options.defaultValue !== undefined ? options.defaultValue : '';
            inputEl.placeholder = options.placeholder || '';
        } else {
            inputWrapper.classList.add('hidden');
        }

        // Buttons
        if (type === 'alert') {
            cancelBtn.classList.add('hidden');
            confirmBtn.textContent = options.confirmText || 'Mengerti';
        } else {
            cancelBtn.classList.remove('hidden');
            cancelBtn.textContent = options.cancelText || 'Batal';
            confirmBtn.textContent = options.confirmText || (type === 'prompt' ? 'Simpan' : 'Ya, Lanjutkan');
        }

        // Tones & Icon Styling
        iconWrapper.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border';
        confirmBtn.className = 'inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-xs font-bold transition-all shadow-sm cursor-pointer';
        cancelBtn.className = 'inline-flex items-center justify-center rounded-xl border border-line bg-surface px-4 py-2.5 text-xs font-semibold text-ink-muted hover:bg-surface-muted hover:text-ink transition-colors cursor-pointer';
        closeBtn.className = '-mr-1.5 -mt-1.5 inline-flex h-8 w-8 items-center justify-center rounded-lg text-ink-subtle hover:bg-surface-muted hover:text-ink transition-colors cursor-pointer';

        if (tone === 'danger') {
            iconWrapper.classList.add('border-red-200', 'bg-red-50', 'text-red-600');
            iconSpan.innerHTML = options.icon ? (ICONS[options.icon] || ICONS.danger) : ICONS.danger;
            confirmBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'text-white', 'hover:shadow-red-500/20');
        } else if (tone === 'warning') {
            iconWrapper.classList.add('border-amber-200', 'bg-amber-50', 'text-amber-700');
            iconSpan.innerHTML = options.icon ? (ICONS[options.icon] || ICONS.warning) : ICONS.warning;
            confirmBtn.classList.add('bg-amber-600', 'hover:bg-amber-700', 'text-white', 'hover:shadow-amber-500/20');
        } else if (tone === 'success') {
            iconWrapper.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
            iconSpan.innerHTML = options.icon ? (ICONS[options.icon] || ICONS.check) : ICONS.check;
            confirmBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
        } else {
            // Brand default
            iconWrapper.classList.add('border-brand/20', 'bg-brand-soft', 'text-brand-deep');
            iconSpan.innerHTML = options.icon ? (ICONS[options.icon] || ICONS.calendar) : (type === 'prompt' ? ICONS.calendar : ICONS.info);
            confirmBtn.classList.add('bg-brand', 'hover:bg-brand-hover', 'text-surface');
        }

        // Show Dialog
        container.classList.remove('hidden');
        container.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        // Smooth Entrance
        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            panel.classList.remove('scale-95', 'opacity-0');
            panel.classList.add('scale-100', 'opacity-100');

            if (type === 'prompt') {
                inputEl.focus();
                inputEl.select();
            } else {
                confirmBtn.focus();
            }
        });
    });
}

function closeActiveDialog(isConfirm) {
    if (!activeDialog) return;

    const { options, resolve, container, backdrop, panel, inputEl } = activeDialog;
    activeDialog = null;

    // Smooth Exit
    backdrop.classList.remove('opacity-100');
    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100', 'opacity-100');
    panel.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        container.classList.add('hidden');
        container.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');

        if (options.type === 'prompt') {
            resolve(isConfirm ? inputEl.value : null);
        } else if (options.type === 'confirm') {
            resolve(isConfirm);
        } else {
            resolve();
        }
    }, 180);
}

/**
 * Global helper functions
 */
window.$prompt = function (options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    return openDialog({
        type: 'prompt',
        ...options,
    });
};

window.$confirm = function (options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    return openDialog({
        type: 'confirm',
        ...options,
    });
};

window.$alert = function (options) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    return openDialog({
        type: 'alert',
        ...options,
    });
};

/**
 * Otomatis menangani form dan tautan yang memiliki atribut `data-gpa-confirm`
 */
document.addEventListener(
    'submit',
    async (e) => {
        const form = e.target;
        if (!form || !form.getAttribute) return;

        const confirmMsg = form.getAttribute('data-gpa-confirm');
        if (confirmMsg && !form.dataset.gpaConfirmed) {
            e.preventDefault();
            e.stopPropagation();

            const title = form.getAttribute('data-gpa-title') || 'Konfirmasi Tindakan';
            const tone = form.getAttribute('data-gpa-tone') || 'danger';
            const okText = form.getAttribute('data-gpa-ok') || 'Lanjutkan';

            const confirmed = await window.$confirm({
                title,
                message: confirmMsg,
                tone,
                confirmText: okText,
                cancelText: 'Batal',
            });

            if (confirmed) {
                form.dataset.gpaConfirmed = 'true';
                if (form.requestSubmit) {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        }
    },
    true
);

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureDialogElements);
    } else {
        ensureDialogElements();
    }
}

export default {
    open: openDialog,
    prompt: window.$prompt,
    confirm: window.$confirm,
    alert: window.$alert,
};
