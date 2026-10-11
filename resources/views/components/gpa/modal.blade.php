@props([
    'name' => 'gpa-modal',
    'title' => null,
    'description' => null,
    'size' => 'md',
    'open' => false,
    'closeOnBackdrop' => true,
])

@php
    $sizes = [
        'sm' => 'sm:max-w-md',
        'md' => 'sm:max-w-lg',
        'lg' => 'sm:max-w-2xl',
        'xl' => 'sm:max-w-4xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $headingId = $name.'-title';
@endphp

<div x-data="{ open: @js($open) }"
    x-on:gpa-modal-open.window="if ($event.detail === @js($name)) open = true"
    x-on:gpa-modal-close.window="if ($event.detail === @js($name)) open = false"
    x-on:keydown.escape.window="open = false"
    x-effect="document.body.classList.toggle('overflow-hidden', open)" {{ $attributes }}>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="presentation">
        <div @if ($closeOnBackdrop) x-on:click="open = false" @endif
            class="fixed inset-0 bg-brand-deep/70 backdrop-blur-[2px]"></div>

        <div class="relative flex min-h-full items-end justify-center p-0 sm:items-center sm:p-6">
            <div role="dialog" aria-modal="true" @if ($title) aria-labelledby="{{ $headingId }}" @endif
                @if ($description) aria-describedby="{{ $name }}-description" @endif
                class="relative w-full {{ $sizeClass }} max-w-full rounded-t-xl border border-line bg-surface shadow-card sm:rounded-xl">
                <div class="flex items-start justify-between gap-3 border-b border-line px-4 py-3 md:px-5">
                    <div class="min-w-0">
                        @if ($title)
                            <h2 id="{{ $headingId }}"
                                class="gpa-section-title text-base text-ink md:text-lg">{{ $title }}</h2>
                        @endif
                        @if ($description)
                            <p id="{{ $name }}-description" class="mt-1 text-2xs leading-relaxed text-ink-subtle">
                                {{ $description }}
                            </p>
                        @endif
                    </div>

                    <button type="button" x-on:click="open = false" aria-label="Tutup dialog"
                        class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-sm border border-line-soft bg-surface-pill text-ink-subtle transition-colors hover:border-line-board hover:text-ink">
                        <x-gpa.icon name="x" class="h-3.5 w-3.5" />
                    </button>
                </div>

                <div class="max-h-[70vh] space-y-4 overflow-y-auto p-4 md:p-5">
                    {{ $slot }}
                </div>

                @isset($footer)
                    <div class="flex flex-wrap items-center justify-end gap-2 border-t border-line bg-surface-muted px-4 py-3 md:px-5">
                        {{ $footer }}
                    </div>
                @endisset
            </div>
        </div>
    </div>
</div>