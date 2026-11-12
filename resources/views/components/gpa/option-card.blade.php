@props([
    'type' => 'checkbox',
    'name',
    'value' => null,
    'id' => null,
    'title' => null,
    'meta' => null,
    'note' => null,
    'description' => null,
    'badge' => null,
    'checked' => false,
    'required' => false,
    'disabled' => false,
    'compact' => false,
    'model' => null,
])

<label {{ $attributes->merge(['class' => 'block cursor-pointer']) }}>
    <input type="{{ $type }}" name="{{ $name }}" @if ($value !== null) value="{{ $value }}" @endif
        @checked($checked) @required($required) @disabled($disabled)
        @if ($model) x-model="{{ $model }}" @endif
        class="gpa-check {{ $type === 'radio' ? 'gpa-check--radio' : '' }} peer" />

    <span
        class="flex items-start gap-2.5 rounded-lg bg-surface shadow-card transition-all duration-100 hover:bg-surface-muted peer-checked:bg-brand-soft peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand peer-disabled:cursor-not-allowed peer-disabled:opacity-55 {{ $compact ? 'p-2.5' : 'p-3' }}">
        <span class="flex-1 space-y-1">
            <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="text-xs font-semibold text-ink">{{ $title }}</span>
                @isset($badge)
                    {{ $badge }}
                @endisset
            </span>

            @if ($meta)
                <span class="block gpa-mono-xs font-medium text-ink-muted">{{ $meta }}</span>
            @endif

            @if ($description)
                <span class="block text-2xs leading-relaxed text-ink-muted">{{ $description }}</span>
            @endif

            @isset($body)
                <span class="block text-2xs leading-relaxed text-ink-muted">{{ $body }}</span>
            @endisset

            @if ($note)
                <span class="block gpa-mono-xs text-ink-subtle">{{ $note }}</span>
            @endif
        </span>
    </span>
</label>
