@once
    <style>
        @keyframes gpaToastIn {
            0% {
                opacity: 0;
                transform: translateY(-14px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes gpaToastOut {
            0% {
                opacity: 1;
                transform: translateY(0) scale(1);
                max-height: 140px;
                margin-bottom: 6px;
            }
            100% {
                opacity: 0;
                transform: translateY(-12px) scale(0.96);
                max-height: 0;
                margin-bottom: 0;
                padding-top: 0;
                padding-bottom: 0;
                overflow: hidden;
            }
        }
        .gpa-toast-item-in {
            animation: gpaToastIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .gpa-toast-item-out {
            animation: gpaToastOut 0.28s cubic-bezier(0.4, 0, 1, 1) forwards !important;
        }
        @media (prefers-reduced-motion: reduce) {
            .gpa-toast-item-in, .gpa-toast-item-out {
                animation: none !important;
            }
        }
    </style>
@endonce

<div x-data="gpaToast"
    class="pointer-events-none fixed left-3 right-3 top-14 sm:top-16 sm:left-auto sm:right-6 sm:w-full z-[99999] flex flex-col gap-1.5 sm:gap-2 max-w-sm sm:max-w-[400px] mx-auto sm:mx-0"
    @gpa:toast.window="push($event.detail)"
    @gpa:notify.window="push($event.detail)"
    x-init="
        @if (session('success'))
            push({ title: 'Berhasil', message: @js(session('success')), tone: 'success', time: 'Baru saja' });
        @endif
        @if (session('error'))
            push({ title: 'Terjadi Kesalahan', message: @js(session('error')), tone: 'danger', time: 'Baru saja' });
        @endif
        @if (session('status'))
            push({ title: 'Informasi', message: @js(session('status')), tone: 'info', time: 'Baru saja' });
        @endif
        @if (session('info'))
            push({ title: 'Informasi', message: @js(session('info')), tone: 'info', time: 'Baru saja' });
        @endif
        @if (session('warning'))
            push({ title: 'Peringatan', message: @js(session('warning')), tone: 'warning', time: 'Baru saja' });
        @endif
    ">
    <template x-for="item in items" :key="item.id">
        <div :role="item.tone === 'danger' || item.tone === 'error' ? 'alert' : 'status'"
            class="pointer-events-auto flex items-center sm:items-start gap-2.5 sm:gap-3 rounded-xl border border-l-[3px] sm:border-l-4 border-line-hair bg-white/95 backdrop-blur-md px-3 py-2 sm:px-3.5 sm:py-2.5 shadow-md sm:shadow-lg transition-all"
            :class="{
                'gpa-toast-item-out': item.exiting,
                'gpa-toast-item-in': !item.exiting,
                'border-l-success': item.tone === 'success',
                'border-l-danger': item.tone === 'danger' || item.tone === 'error',
                'border-l-amber-500': item.tone === 'warning',
                'border-l-sky-500': item.tone === 'info',
                'border-l-accent-deep': item.tone !== 'success' && item.tone !== 'danger' && item.tone !== 'error' && item.tone !== 'warning' && item.tone !== 'info'
            }">
            <div class="flex h-6 w-6 sm:h-7 sm:w-7 shrink-0 items-center justify-center rounded-lg bg-accent/30 sm:mt-0.5">
                <x-gpa.logo-mark class="h-3.5 sm:h-4 w-auto" />
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex items-baseline justify-between gap-1.5">
                    <p class="text-xs font-bold text-ink leading-tight truncate sm:text-sm" x-text="item.title"></p>
                    <time class="hidden sm:inline shrink-0 font-mono text-[10px] text-ink-body/60" x-text="item.time || 'Baru saja'"></time>
                </div>
                <p class="mt-0.5 text-[11px] sm:text-xs text-ink-body leading-snug sm:leading-relaxed line-clamp-2" x-text="item.message || item.description" x-show="item.message || item.description"></p>
            </div>

            <button type="button" @click="closeManual(item.id)" aria-label="Tutup notifikasi"
                class="shrink-0 self-center sm:self-start -mr-1 rounded p-1 text-base leading-none text-ink-body/50 transition-colors hover:text-ink">
                &times;
            </button>
        </div>
    </template>
</div>
