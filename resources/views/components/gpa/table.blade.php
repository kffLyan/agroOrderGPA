@props([
    'columns' => [],
    'rows' => [],
    'caption' => null,
    'density' => 'md',
    'empty' => 'Belum ada data pada periode ini.',
    'rowKey' => null,
])

@php
    $aligns = [
        'left' => 'text-left',
        'right' => 'text-right',
        'center' => 'text-center',
    ];

    $numeric = 'gpa-mono-xs tabular-nums text-right';
    $pad = $density === 'sm' ? 'px-3 py-1.5' : 'px-3 py-2.5';
    $cellBase = 'align-middle text-2xs leading-relaxed text-ink-body';
@endphp

<div {{ $attributes->merge(['class' => 'gpa-panel overflow-hidden']) }}>
    @if ($rows === [])
        <x-gpa.state state="empty" title="Tidak ada baris data" :description="$empty" icon="search" />
    @else
        <div class="gpa-scroll-x">
            <table class="w-full border-collapse">
                @if ($caption)
                    <caption class="sr-only">{{ $caption }}</caption>
                @endif

                <thead>
                    <tr class="border-b border-line bg-surface-muted">
                        @foreach ($columns as $column)
                            <th scope="col"
                                @class([
                                    'gpa-mono-xs font-semibold uppercase tracking-wider text-ink',
                                    $aligns[$column['align'] ?? ($column['numeric'] ?? false ? 'right' : 'left')] ?? $aligns['left'],
                                    $pad,
                                    ($column['headClass'] ?? ''),
                                ])>{{ $column['label'] ?? $column['key'] ?? '' }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-line-faint">
                    @foreach ($rows as $index => $row)
                        @php
                            $rowId = $rowKey ? data_get($row, $rowKey) : null;
                        @endphp

                        <tr @if ($rowId) id="row-{{ $rowId }}" @endif
                            @class([
                                'transition-colors hover:bg-surface-muted',
                                ($row['_class'] ?? ''),
                            ])>
                            @foreach ($columns as $column)
                                @php
                                    $key = $column['key'] ?? null;

                                    if (($column['render'] ?? null) instanceof \Closure) {
                                        $content = $column['render']($row, $index);
                                        $isHtml = true;
                                    } else {
                                        $content = $key === null ? '' : data_get($row, $key);
                                        $isHtml = (bool) ($column['raw'] ?? false);
                                    }
                                @endphp

                                <td @class([
                                    $cellBase,
                                    $pad,
                                    ($column['numeric'] ?? false ? $numeric : ($aligns[$column['align'] ?? 'left'] ?? $aligns['left'])),
                                    ($column['class'] ?? ''),
                                ])>
                                    @if ($isHtml)
                                        {!! $content !!}
                                    @elseif ($content === null || $content === '')
                                        <span class="text-ink-subtle">&mdash;</span>
                                    @else
                                        {{ $content }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>