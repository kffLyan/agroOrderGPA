<div x-data="gpaToast" class="pointer-events-none fixed inset-x-0 bottom-0 z-50 flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
    @gpa:toast.window="push($event.detail)">
    <template x-for="item in items" :key="item.id">
        <div :role="item.tone === 'danger' ? 'alert' : 'status'"
            class="pointer-events-auto flex w-full max-w-sm animate-toast-in items-start gap-2.5 rounded-lg bg-surface px-3 py-2.5 shadow-pop">
            <span class="mt-px flex h-5 w-5 shrink-0 items-center justify-center rounded-sm border"
                :class="item.tone === 'danger' ? 'border-danger/40 bg-danger-soft text-danger' : (item.tone === 'success' ? 'border-success/40 bg-success-soft text-success' : 'border-line bg-surface-muted text-ink-muted')">
                <span x-text="item.tone === 'danger' ? '!' : (item.tone === 'success' ? 'OK' : 'i')"
                    class="gpa-mono-xs font-bold"></span>
            </span>
            <div class="min-w-0 flex-1">
                <p class="gpa-label text-ink" x-text="item.title"></p>
                <p class="mt-0.5 text-2xs leading-relaxed text-ink-muted" x-text="item.message" x-show="item.message"></p>
            </div>
            <button type="button" @click="dismiss(item.id)" aria-label="Tutup notifikasi"
                class="-mr-1 -mt-0.5 rounded p-1 text-ink-subtle transition-colors hover:text-ink">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    stroke-linecap="round" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </template>
</div>
