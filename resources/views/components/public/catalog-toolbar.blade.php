@props([
    'toolbar',
])

<div class="gpa-panel p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-subtle">
                {{ $toolbar['categoryLabel'] }}</span>

            @foreach ($toolbar['categories'] as $category)
                <button type="button" @click="category = '{{ $category['value'] }}'"
                    :class="category === '{{ $category['value'] }}'
                        ? 'border-brand-deep bg-brand-deep text-white'
                        : 'border-line-board bg-surface text-ink-body hover:border-brand-strong hover:text-brand-strong'"
                    class="rounded-full border px-3 py-1.5 gpa-mono-xs font-semibold uppercase tracking-wide transition-colors">
                    {{ $category['label'] }}
                </button>
            @endforeach
        </div>

        <label class="inline-flex cursor-pointer items-center gap-2 text-xs font-medium text-ink-body">
            <input type="checkbox" x-model="availableOnly"
                class="h-4 w-4 rounded border-line-board text-brand-deep focus:ring-accent-deep">
            {{ $toolbar['availableLabel'] }}
        </label>
    </div>

    <div class="mt-4 flex flex-col gap-3 border-t border-line-hair pt-4 md:flex-row md:items-center md:justify-between">
        <form class="flex w-full items-center gap-2 md:max-w-md" @submit.prevent>
            <div class="relative flex-1">
                <x-gpa.icon name="search"
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
                <input type="search" x-model="query" name="q"
                    placeholder="{{ $toolbar['searchPlaceholder'] }}"
                    aria-label="{{ $toolbar['searchLabel'] }} komoditas"
                    class="h-9 w-full rounded border border-line-board bg-canvas pl-9 pr-3 text-xs text-ink outline-none placeholder:text-ink-subtle focus:border-brand-strong focus:ring-1 focus:ring-brand-strong">
            </div>

            <x-gpa.btn type="submit" variant="primary">{{ $toolbar['searchLabel'] }}</x-gpa.btn>
        </form>

        <div class="flex flex-wrap items-center justify-between gap-3 md:justify-end">
            <label class="inline-flex items-center gap-2">
                <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-subtle">
                    {{ $toolbar['sortLabel'] }}</span>
                <select x-model="sort"
                    class="h-9 rounded border border-line-board bg-canvas px-2 text-xs font-semibold text-ink outline-none focus:border-brand-strong focus:ring-1 focus:ring-brand-strong">
                    @foreach ($toolbar['sortOptions'] as $option)
                        <option value="{{ $option['value'] }}" @selected($option['value'] === 'stock')>
                            {{ $option['label'] }}</option>
                    @endforeach
                </select>
            </label>

            <p class="gpa-mono-xs font-semibold text-ink-body">
                {{ $toolbar['totalLabel'] }}
                <span x-text="visibleCount()">{{ $toolbar['total'] }}</span>
                {{ $toolbar['totalSuffix'] }}
            </p>
        </div>
    </div>
</div>
