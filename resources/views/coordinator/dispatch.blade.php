@extends('layouts.coordinator')

@section('title', 'Surat Jalan & Dispatch Armada Logistik')

@section('content')
    @php
        $header = $dispatchData['header'];
        $sop = $dispatchData['sop'];
        $kpis = $dispatchData['kpis'];
        $queue = $dispatchData['queue'];
        $manifest = $dispatchData['manifest'];
        $batch = $dispatchData['batch'];
        $readyDocs = collect($kpis)->firstWhere('key', 'ready_docs')['value'] ?? 4;
        $actionIcons = [
            'handover' => 'send',
            'reprint' => 'printer',
            'track' => 'map-pin',
        ];
    @endphp

    <div class="space-y-6"
        x-data="coordinatorDispatch(@js($queue['items']), @js($manifest['rows']), @js($manifest['filters']), @js($readyDocs))">
        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="truck" class="h-4 w-4 text-success-deep" />
                    {{ $header['eyebrow'] }}
                </p>
                <h1 class="mt-2 font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                    {{ $header['title_before'] }}<br class="hidden sm:block">
                    {{ $header['title_after'] }}
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-5 text-ink-body">{{ $header['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @foreach ($header['actions'] as $action)
                    <button type="button" @click="act(@js($action['key']))"
                        @class([
                            'inline-flex h-10 items-center gap-1.5 rounded-lg px-3 transition-colors',
                            'bg-surface text-ink outline outline-1 outline-line-board shadow-sub hover:bg-surface-muted' => $action['variant'] === 'ghost',
                            'bg-ink text-white outline outline-1 outline-brand-line shadow-sub hover:bg-ink-muted' => $action['variant'] === 'ink',
                        ])>
                        <x-gpa.icon :name="$action['icon']"
                            @class([
                                'h-3.5 w-3.5 shrink-0',
                                'text-ink' => $action['variant'] === 'ghost',
                                'text-accent' => $action['variant'] === 'ink',
                            ]) />
                        <span class="gpa-meta-lg font-bold">
                            {{ $action['label'] }}
                            @if (($action['badge'] ?? null) === 'thermal')
                                <span x-text="`(${thermal})`">(4)</span>
                            @endif
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Rule 05 integrity banner --}}
        <section
            class="flex flex-col gap-4 rounded-xl border border-l-4 border-warning bg-warning-cream p-4 shadow-sub lg:flex-row lg:items-start lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="shrink-0 rounded-lg bg-warning p-1.5">
                    <x-gpa.icon name="alert-triangle" class="h-4 w-4 text-warning-soft" />
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="gpa-micro-bold text-warning-deep">{{ $sop['title'] }}</h2>
                        <span
                            class="rounded bg-warning/10 px-1.5 py-0.5 gpa-micro-bold text-warning outline outline-1 outline-warning/30">
                            {{ $sop['badge'] }}
                        </span>
                    </div>
                    <p class="mt-1.5 max-w-4xl text-xs font-medium leading-4 text-ink">{{ $sop['body'] }}</p>
                </div>
            </div>

            <div class="shrink-0 rounded bg-surface px-3 py-1 outline outline-1 outline-line-hair">
                <p class="gpa-micro-bold text-warning">{{ $sop['cert_prefix'] }}</p>
                <p class="gpa-micro-bold text-warning">{{ $sop['cert_value'] }}</p>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                <article class="gpa-panel flex flex-col gap-2 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">{{ $kpi['label'] }}</h2>
                        <span @class([
                            'shrink-0 rounded-lg p-1.5',
                            'bg-accent/40' => $kpi['icon_chip'] === 'accent-soft',
                            'bg-accent' => $kpi['icon_chip'] === 'accent',
                            'bg-surface-shell' => $kpi['icon_chip'] === 'shell',
                            'bg-warning-soft/30' => $kpi['icon_chip'] === 'warning-soft',
                        ])>
                            <x-gpa.icon :name="$kpi['icon']"
                                @class([
                                    'block h-4 w-4',
                                    'text-success-deep' => in_array($kpi['icon_tone'], ['success-deep', 'success-ink'], true),
                                    'text-warning-deep' => $kpi['icon_tone'] === 'warning-deep',
                                    'text-ink' => $kpi['icon_tone'] === 'ink',
                                ]) />
                        </span>
                    </div>

                    <p class="flex items-baseline gap-1.5">
                        @if ($kpi['key'] === 'ready_docs')
                            <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="docCount()">{{ $kpi['value'] }}</span>
                        @else
                            <span @class([
                                'font-mono text-3xl font-bold leading-10',
                                'text-ink' => $kpi['value_tone'] === 'ink',
                                'text-success-deep' => $kpi['value_tone'] === 'success',
                            ])>{{ $kpi['value'] }}</span>
                        @endif
                        <span @class([
                            'gpa-meta-lg font-bold',
                            'text-ink-body' => $kpi['unit_tone'] === 'quiet',
                            'text-success-deep' => $kpi['unit_tone'] === 'success',
                            'text-ink' => $kpi['unit_tone'] === 'ink',
                        ])>{{ $kpi['unit'] }}</span>
                    </p>

                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        @if ($kpi['key'] === 'ready_docs')
                            <p class="gpa-note text-ink-quiet">{{ $kpi['foot_label'] }}</p>
                            <p class="gpa-meta-lg font-bold text-success-deep" x-text="`${kg(tonase())} KG`">{{ $kpi['foot_value'] }}</p>
                        @elseif ($kpi['key'] === 'fleet')
                            <div class="flex flex-wrap items-center gap-1.5">
                                @foreach ($kpi['foot_chips'] as $chip)
                                    <span
                                        class="rounded bg-surface-shell px-1.5 py-0.5 gpa-note text-ink-body outline outline-1 outline-line-board/40">
                                        {{ $chip }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif ($kpi['key'] === 'departure')
                            <p class="gpa-note text-ink-quiet">{{ $kpi['foot_label'] }}</p>
                            <span class="rounded bg-accent/50 px-1.5 py-0.5 gpa-micro-bold text-ink">{{ $kpi['foot_chip'] }}</span>
                        @else
                            <p class="gpa-note text-ink-quiet">{{ $kpi['foot_label'] }}</p>
                            <p class="inline-flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                                @if ($kpi['foot_dot'] ?? false)
                                    <span class="h-1.5 w-1.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                                @endif
                                {{ $kpi['foot_chip'] }}
                            </p>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Locked weighing print queue --}}
        <section class="space-y-4">
            <header class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="rounded-lg bg-brand p-1.5">
                        <x-gpa.icon name="printer" class="h-4 w-4 text-accent" />
                    </span>
                    <h2 class="gpa-section-title text-ink">{{ $queue['title'] }}</h2>
                </div>
                <p class="gpa-note text-ink-quiet" x-text="queueMeta()">{{ $queue['meta'] }}</p>
            </header>

            <div class="grid gap-4 2xl:grid-cols-2">
                @foreach ($queue['items'] as $item)
                    <article class="gpa-panel flex flex-col justify-between gap-4 p-4">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-4 border-b border-line-soft/60 pb-3">
                                <div class="min-w-0">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-success-deep bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                                        {{ $item['status_chip'] }}
                                    </span>
                                    <p class="mt-2 font-sans text-lg font-bold leading-6 text-ink">{{ $item['po'] }}</p>
                                    <p class="mt-0.5 text-xs leading-4 text-ink-body">{{ $item['customer'] }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="gpa-note text-ink-quiet">NETTO AKTUAL SAH</p>
                                    <p class="mt-0.5">
                                        <span class="font-mono text-sm font-bold text-ink">{{ number_format($item['net'], 2, '.', ',') }}</span>
                                        <span class="gpa-meta-lg font-bold text-ink">KG</span>
                                    </p>
                                    <p class="gpa-note mt-0.5 text-success-deep">
                                        Tara: {{ number_format($item['tara'], 2, '.', ',') }} KG | Gross:
                                        {{ number_format($item['gross'], 2, '.', ',') }} KG
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/30 sm:flex-row sm:gap-8">
                                <div class="min-w-0">
                                    <p class="gpa-micro-bold text-ink-quiet">SUPIR BERTUGAS</p>
                                    <p class="mt-0.5 text-xs font-semibold text-ink">{{ $item['driver'] }}</p>
                                    <p class="gpa-note mt-0.5 text-ink-quiet">{{ $item['driver_id'] }}</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="gpa-micro-bold text-ink-quiet">ALOKASI KENDARAAN</p>
                                    <p class="mt-0.5 text-xs font-semibold text-ink">{{ $item['vehicle'] }}</p>
                                    <p class="gpa-meta-lg mt-0.5 font-bold text-success-deep">{{ $item['plate'] }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 px-1">
                                <p class="text-xs leading-4 text-ink-body">{{ $item['commodity'] }}</p>
                                <p class="gpa-note text-ink-quiet">{{ $item['dock'] }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft/60 pt-4">
                            <p class="gpa-note text-ink-quiet">LOCK STAMP: {{ $item['lock_stamp'] }}</p>
                            <button type="button" @click="issue(@js($item['sj']))"
                                :disabled="isIssued(@js($item['sj'])) || !canIssue(@js($item['sj']))"
                                class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-accent px-4 shadow-sub outline outline-1 outline-success-deep transition-colors hover:bg-accent-deep disabled:cursor-not-allowed disabled:bg-surface-pill disabled:text-ink-quiet disabled:outline-line-board">
                                <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-ink" />
                                <span class="gpa-meta-lg font-bold text-ink"
                                    x-text="isIssued(@js($item['sj'])) ? @js($item['issued_label']) : @js($item['issue_label'])">{{ $item['issue_label'] }}</span>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Dispatch manifest --}}
        <section class="gpa-panel p-4">
            <header class="flex flex-wrap items-start justify-between gap-4 border-b border-line-soft/60 pb-3">
                <div class="min-w-0">
                    <h2 class="gpa-section-title flex items-center gap-2 text-ink">
                        <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        {{ $manifest['title'] }}
                    </h2>
                    <p class="mt-1 max-w-3xl text-xs leading-4 text-ink-body">{{ $manifest['subtitle'] }}</p>
                </div>

                <div class="relative shrink-0">
                    <span class="gpa-micro-bold text-ink-quiet">{{ $manifest['filter_label'] }}</span>
                    <button type="button" @click="filterOpen = !filterOpen" @click.outside="filterOpen = false"
                        :aria-expanded="filterOpen ? 'true' : 'false'"
                        class="mt-1 flex h-8 w-full items-center justify-between gap-2 rounded border border-line-board bg-surface-shell px-2 outline outline-1 outline-line-board/60 transition-colors hover:bg-surface-muted sm:w-56">
                        <span class="gpa-meta-lg font-bold text-ink" x-text="filterLabel()">{{ $manifest['filter_default'] }}</span>
                        <x-gpa.icon name="chevron-down" class="h-3 w-3 shrink-0 text-ink-body" />
                    </button>

                    <div x-cloak x-show="filterOpen" x-transition
                        class="absolute right-0 z-20 mt-1 w-56 rounded-lg border border-line-board bg-surface p-1 shadow-pop">
                        @foreach ($manifest['filters'] as $option)
                            <button type="button" @click="setFilter(@js($option['key']))"
                                class="flex w-full items-center justify-between gap-2 rounded px-2 py-1.5 text-left transition-colors"
                                :class="filter === @js($option['key']) ? 'bg-ink gpa-meta-lg font-bold text-accent' : 'gpa-meta-lg font-medium text-ink-body hover:bg-surface-shell'">
                                <span x-text="filterLabel(@js($option['key']))">{{ $option['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </header>

            <div class="gpa-scroll-x -mx-4 overflow-x-auto">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">{{ $manifest['title'] }}</caption>
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-shell/70">
                            @foreach ($manifest['columns'] as $position => $column)
                                <th scope="col"
                                    class="px-3 py-4 gpa-micro-bold text-ink-quiet {{ $position === count($manifest['columns']) - 1 ? 'text-right' : 'text-left' }}">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($manifest['rows'] as $row)
                            @php($rowId = \Illuminate\Support\Js::from($row['id']))
                            <tr @class([
                                'border-b border-line-hair/70',
                                'bg-accent/10' => $row['row_tone'] === 'highlight',
                            ]) x-show="isVisible({{ $rowId }})">
                                <th scope="row" class="px-3 py-4 text-left align-top">
                                    <span class="gpa-meta font-bold text-ink">{{ $row['id'] }}</span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet">REF: {{ $row['ref'] }}</span>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <span class="block text-xs font-semibold text-ink">{{ $row['driver'] }}</span>
                                    <span class="mt-0.5 block gpa-note text-success-deep">{{ $row['plate'] }} ({{ $row['vehicle'] }})</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span @class([
                                        'gpa-meta-lg font-bold',
                                        'text-success-deep' => $row['net_tone'] === 'success',
                                        'text-ink' => $row['net_tone'] === 'ink',
                                    ])>{{ number_format($row['net'], 2, '.', ',') }} KG</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span class="block gpa-meta font-medium text-ink">{{ $row['time'] }}</span>
                                    <span @class([
                                        'mt-0.5 block gpa-note',
                                        'text-success-deep' => $row['slot_tone'] === 'success',
                                        'text-ink' => $row['slot_tone'] === 'ink',
                                    ])>{{ $row['slot'] }}</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span @class([
                                            'rounded px-1.5 py-0.5 gpa-micro-bold',
                                            'bg-accent text-success-ink outline outline-1 outline-success/40' => $row['copies_tone'] === 'ok',
                                            'bg-surface-pill text-ink-quiet' => $row['copies_tone'] === 'muted',
                                            'bg-accent/50 text-success-deep outline outline-1 outline-success/40' => $row['copies_tone'] === 'active',
                                        ])>{{ $row['copies'] }}</span>
                                        <span @class([
                                            'inline-flex items-center gap-1 rounded px-1.5 py-0.5 gpa-micro-bold',
                                            'bg-surface-pill text-ink outline outline-1 outline-line-board/40' => $row['qr_icon'],
                                            'bg-accent/50 text-success-deep' => ! $row['qr_icon'],
                                        ])>
                                            @if ($row['qr_icon'])
                                                <x-gpa.icon name="qrcode" class="h-3 w-3 shrink-0" />
                                            @endif
                                            {{ $row['qr'] }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 outline outline-1"
                                        :class="state({{ $rowId }}) === 'moving'
                                            ? 'bg-brand text-accent outline-brand/40'
                                            : 'bg-accent/30 text-ink outline-success/30'">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="state({{ $rowId }}) === 'moving' ? 'bg-accent' : 'bg-success-deep'"
                                            aria-hidden="true"></span>
                                        <span class="gpa-note" x-text="dockText({{ $rowId }})">{{ $row['dock'] }}</span>
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <button type="button" @click="actRow({{ $rowId }})"
                                        class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg px-3 transition-colors"
                                        :class="actionVariant({{ $rowId }})">
                                        @foreach ($actionIcons as $actionKey => $actionIcon)
                                            <x-gpa.icon :name="$actionIcon"
                                                x-show="actionOf({{ $rowId }}) === '{{ $actionKey }}'"
                                                ::class="actionIconTone({{ $rowId }})"
                                                @class([
                                                    'h-3.5 w-3.5 shrink-0',
                                                    'x-cloak' => $actionKey !== $row['action'],
                                                ]) />
                                        @endforeach
                                        <span class="gpa-meta-lg font-bold"
                                            x-text="actionLabel({{ $rowId }})">{{ $row['action_label'] }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Batch operations --}}
        <section class="gpa-panel flex flex-wrap items-center justify-between gap-4 p-4">
            <div class="flex items-center gap-3">
                <span
                    class="flex h-10 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-shell outline outline-1 outline-line-board/40">
                    <x-gpa.icon name="clipboard" class="h-4 w-4 text-ink" />
                </span>
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">{{ $batch['title'] }}</h2>
                    <p class="mt-1 max-w-xl text-xs leading-4 text-ink-body">{{ $batch['subtitle'] }}</p>
                </div>
            </div>

            <div class="grid w-full gap-2 sm:w-auto sm:grid-cols-2">
                @foreach ($batch['actions'] as $action)
                    <button type="button" @click="batch(@js($action['key']))"
                        @class([
                            'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg px-4 transition-colors',
                            'bg-surface text-ink outline outline-1 outline-line-board/60 shadow-sub hover:bg-surface-muted' => $action['variant'] === 'ghost',
                            'bg-ink text-white outline outline-1 outline-brand-line shadow-sub hover:bg-ink-muted' => $action['variant'] === 'ink',
                            'bg-accent text-ink outline outline-1 outline-success-deep shadow-pop hover:bg-accent-deep' => $action['variant'] === 'accent',
                            'sm:col-span-2' => $action['wide'] ?? false,
                        ])>
                        <x-gpa.icon :name="$action['icon']"
                            @class([
                                'h-3.5 w-3.5 shrink-0',
                                'text-ink' => $action['variant'] !== 'ink',
                                'text-accent' => $action['variant'] === 'ink',
                            ]) />
                        <span class="gpa-meta-lg font-bold">{{ $action['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>
    </div>
@endsection
