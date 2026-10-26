@props(['title', 'description' => null, 'time' => null, 'duration' => 5, 'type' => null, 'tone' => null])

@once
    <style>
        .gpa-toast { animation: gpa-toast 5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes gpa-toast {
            0% { opacity: 0; transform: translateY(-14px) scale(0.95); }
            6% { opacity: 1; transform: translateY(0) scale(1); }
            92% { opacity: 1; transform: translateY(0) scale(1); }
            100% { opacity: 0; transform: translateY(-12px) scale(0.95); visibility: hidden; }
        }
        @media (prefers-reduced-motion: reduce) { .gpa-toast { animation-name: none; } }
    </style>
@endonce

<div class="gpa-toast fixed right-4 top-16 z-[99999] w-[calc(100%-2rem)] max-w-[460px] pointer-events-auto" style="animation-duration: {{ $duration }}s">
    <x-gpa.notification-banner :title="$title" :description="$description" :time="$time" :type="$type ?? $tone" class="shadow-lg">
        {{ $slot }}
        @if ($slot->isEmpty())
            <button type="button" aria-label="Tutup" onclick="this.closest('.gpa-toast').remove()" class="rounded p-0.5 text-base leading-none text-ink-body/50 hover:text-ink transition-colors">&times;</button>
        @endif
    </x-gpa.notification-banner>
</div>
