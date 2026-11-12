@props([
    'title',
    'id' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-between gap-2 pb-1']) }}>
    <h3 @if ($id) id="{{ $id }}" @endif class="gpa-label uppercase tracking-wider text-ink">{{ $title }}</h3>
    <div class="flex shrink-0 items-center gap-2">
        {{ $meta ?? '' }}
    </div>
</div>
