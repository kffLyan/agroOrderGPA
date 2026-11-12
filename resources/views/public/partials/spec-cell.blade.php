@props([
    'variant',
    'value' => [],
])

@if ($variant === 'commodity')
    <div class="space-y-1">
        <p class="gpa-mono-xs font-bold text-success">{{ $value['sku'] }}</p>
        <p class="text-xs font-semibold leading-snug text-brand-strong">{{ $value['name'] }}</p>
        <p class="text-2xs leading-relaxed text-ink-body">{{ $value['origin'] }}</p>
    </div>
@elseif ($variant === 'storage')
    <div class="space-y-0.5">
        <p class="gpa-mono-xs text-brand-strong">{{ $value['range'] }}</p>
        <p class="text-2xs text-ink-body">{{ $value['note'] }}</p>
    </div>
@else
    <div class="flex flex-wrap gap-1">
        @foreach ($value as $cert)
            <span
                class="rounded bg-surface-muted px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-line-hair gpa-mono-xs font-semibold text-brand-strong">
                {{ $cert }}
            </span>
        @endforeach
    </div>
@endif