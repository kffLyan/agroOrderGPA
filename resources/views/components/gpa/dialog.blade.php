{{--
    AgroOrder GPA Custom Dialog Mount Point
    Dialog container otomatis dibuat oleh gpa-dialog.js jika belum ada di DOM,
    namun komponen ini memastikan class styling Tailwind dikompilasi sempurna.
--}}
<div id="gpa-dialog-root" class="fixed inset-0 z-[999999] hidden items-center justify-center p-4 transition-all duration-200" role="dialog" aria-modal="true">
    <div id="gpa-dialog-backdrop" class="fixed inset-0 z-0 bg-[#0c2401]/75 backdrop-blur-[3px] opacity-0 transition-opacity duration-200"></div>
    <div id="gpa-dialog-panel" class="relative z-10 w-full max-w-md transform scale-95 opacity-0 overflow-hidden rounded-2xl border border-line bg-surface p-6 shadow-2xl transition-all duration-200 sm:p-7">
        <div class="flex items-start gap-3.5">
            <div id="gpa-dialog-icon-wrapper" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-brand/20 bg-brand-soft text-brand-deep">
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
</div>
