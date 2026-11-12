@props([
    'nodes',
])

<ol class="flex flex-col gap-2 lg:flex-row lg:items-stretch lg:gap-1.5">
    @foreach ($nodes as $node)
        <li @class([
            'flex flex-1 items-center gap-3 rounded-xl border px-3 py-3',
            'border-brand-strong bg-brand-strong text-white shadow-sub' => $node['active'],
            'border-line-hair bg-surface' => ! $node['active'],
        ])>
            <span @class([
                'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold',
                'bg-accent text-brand-deep' => $node['active'],
                'bg-surface-muted text-brand-strong' => ! $node['active'],
            ])>{{ $node['step'] }}</span>

            <span @class([
                'text-xs font-bold leading-snug',
                'text-white' => $node['active'],
                'text-brand-strong' => ! $node['active'],
            ])>{{ $node['label'] }}</span>
        </li>

        @unless ($loop->last)
            <li class="hidden items-center justify-center text-ink-subtle lg:flex" aria-hidden="true">
                <x-gpa.icon name="chevron-right" class="h-4 w-4" />
            </li>
        @endunless
    @endforeach
</ol>