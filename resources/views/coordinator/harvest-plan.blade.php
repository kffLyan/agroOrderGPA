@extends('layouts.coordinator')

@section('title', 'Rencana Panen & Persiapan Pesanan')

@section('content')
    @php
        $mandate = $harvestPlanData['mandate'];
        $kpis = $harvestPlanData['kpis'];
        $queue = $harvestPlanData['queue'];
        $station = $harvestPlanData['station'];
        $qc = $harvestPlanData['qc'];
        $sortedKpi = collect($kpis)->firstWhere('key', 'sorted');
        $temperatureKpi = collect($kpis)->firstWhere('key', 'temperature');
        $totalBatches = $queue['total_batches'];
        $baseDone = $queue['done_batches'] - collect($queue['rows'])->where('stage', 4)->count();
        $baseWeighKg = $queue['weigh_kg'] - collect($queue['rows'])->where('stage', '>=', 3)->sum('est_kg');
        $stageToneClasses = [
            'pill' => 'bg-surface-pill text-ink-body outline-line-board/40',
            'accent' => 'bg-accent text-ink outline-success-deep',
            'accent-soft' => 'bg-accent/50 text-success-deep outline-success/30',
        ];
        $stageDotClasses = [
            'accent' => 'bg-success-deep',
            'accent-soft' => 'bg-success-deep',
            'pill' => 'bg-ink-quiet',
        ];
    @endphp

    <div class="space-y-6"
        x-data="coordinatorHarvestPlan(@js($queue['rows']), @js($queue['stages']), @js($station), @js($qc), @js([
            'totalBatches' => $totalBatches,
            'baseDone' => $baseDone,
            'baseWeighKg' => $baseWeighKg,
        ]))">
        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <h1 class="mt-2 font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                    Sortir, Sanitasi Krat &amp; Pre-Cooling
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-3 text-ink outline outline-1 outline-line-board shadow-sub">
                    <x-gpa.icon name="thermometer" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-meta-lg font-bold">Pre-Cooling {{ $temperatureKpi['value'] }}</span>
                </span>
                <span
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-ink px-3 text-accent shadow-sub outline outline-1 outline-brand-line">
                    <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0" />
                    <span class="gpa-meta-lg font-bold">Cold-Chain Aktif</span>
                </span>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                @php($progress = $kpi['key'] === 'sorted' ? $sortedKpi['progress'] : $kpi['progress'])
                <article class="gpa-panel flex flex-col gap-2 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">{{ $kpi['label'] }}</h2>
                        <span @class([
                            'shrink-0 rounded-lg p-1.5',
                            'bg-accent/40' => $kpi['icon_chip'] === 'accent-soft',
                            'bg-surface-shell' => $kpi['icon_chip'] === 'shell',
                        ])>
                            <x-gpa.icon :name="$kpi['icon']"
                                @class([
                                    'block h-4 w-4',
                                    'text-success-deep' => $kpi['icon_tone'] === 'success-deep',
                                    'text-ink' => $kpi['icon_tone'] === 'ink',
                                ]) />
                        </span>
                    </div>

                    <p class="flex items-baseline gap-1.5">
                        @if ($kpi['key'] === 'sorted')
                            <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="doneCount()">{{ $kpi['value'] }}</span>
                        @else
                            <span class="font-mono text-3xl font-bold leading-10 text-ink">{{ $kpi['value'] }}</span>
                        @endif

                        @if ($kpi['key'] === 'sorted')
                            <span class="gpa-meta-lg font-bold text-success-deep" x-text="doneLabel()">{{ $kpi['unit'] }}</span>
                        @elseif ($kpi['chip_label'] ?? null)
                            <span class="rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-ink">{{ $kpi['chip_label'] }}</span>
                        @else
                            <span @class([
                                'gpa-meta-lg font-bold',
                                'text-ink-body' => $kpi['unit_tone'] === 'body',
                            ])>{{ $kpi['unit'] }}</span>
                        @endif
                    </p>

                    <div class="mt-auto space-y-2 pt-2">
                        @if ($kpi['key'] === 'sorted')
                            <p class="gpa-note text-ink-body" x-text="weighNote()">{{ $kpi['note'] }}</p>
                        @else
                            <p @class([
                                'gpa-note',
                                'text-ink-quiet' => $kpi['note_tone'] === 'muted',
                                'text-ink-body' => $kpi['note_tone'] === 'body',
                            ])>{{ $kpi['note'] }}</p>
                        @endif

                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-pill" role="presentation">
                            <div @class([
                                'h-full rounded-full',
                                $kpi['bar'],
                            ]) :style="`width: ${progressFor(@js($kpi['key']), {{ $progress }})}%`"></div>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- Packing queue + active workstation --}}
        <section class="grid gap-4 2xl:grid-cols-2">
            {{-- Queue --}}
            <div class="gpa-panel flex flex-col gap-4 p-4">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft/60 pb-3">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">{{ $queue['title'] }}</h2>
                    </div>
                    <button type="button" @click="refreshQueue()"
                        class="shrink-0 rounded bg-surface-shell px-2 py-0.5 gpa-note text-ink-body outline outline-1 outline-line-board/50 transition-colors hover:bg-surface-muted">
                        {{ $queue['badge'] }}
                    </button>
                </header>

                <div class="gpa-scroll-x -mx-4 overflow-x-auto">
                    <table class="w-full min-w-[46rem] border-collapse">
                        <caption class="sr-only">{{ $queue['title'] }}</caption>
                        <thead>
                            <tr class="border-y border-line-board/40 bg-surface-shell">
                                @foreach ($queue['columns'] as $position => $column)
                                    <th scope="col"
                                        class="px-3 py-3 gpa-micro-bold text-ink-body {{ $position === count($queue['columns']) - 1 ? 'text-right' : 'text-left' }}">
                                        {{ $column }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($queue['rows'] as $row)
                                @php($rowPo = \Illuminate\Support\Js::from($row['po']))
                                <tr class="border-b border-line-hair/70" :class="isActive({{ $rowPo }}) ? 'bg-accent/10' : ''">
                                    <th scope="row" class="border-l-4 border-transparent px-3 py-3 text-left align-top"
                                        :class="isActive({{ $rowPo }}) ? '!border-brand' : ''">
                                        <span class="block gpa-meta font-bold text-ink">#{{ $row['po'] }}</span>
                                        <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $row['bay'] }} // {{ $row['station'] }}</span>
                                    </th>
                                    <td class="px-3 py-3 align-top">
                                        <span class="block text-xs font-bold leading-4 text-ink">{{ $row['buyer'] }}</span>
                                        <span @class([
                                            'mt-0.5 block gpa-note',
                                            'text-success-deep' => $row['commodity_tone'] === 'success',
                                            'text-ink-body' => $row['commodity_tone'] === 'body',
                                        ]) x-text="commodityLabel({{ $rowPo }})">{{ trim($row['commodity'].' '.($row['commodity_note'] ?? '')) }}</span>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="block gpa-meta-lg font-bold text-ink">{{ $row['krat_done'] }}/{{ $row['krat_total'] }} Krat</span>
                                        <span class="mt-0.5 block gpa-note text-ink-body">Est: {{ number_format($row['est_kg'], 2, '.', ',') }} kg</span>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="inline-flex items-center gap-1.5 rounded px-2 py-1 outline outline-1 gpa-micro-bold"
                                            :class="stageTone({{ $rowPo }})">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="stageDot({{ $rowPo }})"
                                                aria-hidden="true"></span>
                                            <span x-text="stageLabel({{ $rowPo }})">{{ $queue['stages'][$row['stage']]['chip'] }}</span>
                                        </span>
                                        <span class="mt-1 block gpa-note" x-show="afkirOf({{ $rowPo }})"
                                            :class="afkirTone({{ $rowPo }})" x-text="afkirOf({{ $rowPo }})">{{ $row['afkir'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right align-top">
                                        <button type="button" @click="select({{ $rowPo }})"
                                            class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors"
                                            :class="actionVariant({{ $rowPo }})">
                                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 shrink-0" ::class="actionIconTone({{ $rowPo }})" />
                                            <span x-text="actionLabel({{ $rowPo }})">{{ $row['action_label'] }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <p class="gpa-note text-ink-body">{{ $queue['footer_left'] }}</p>
                    <p class="gpa-note text-ink">{{ $queue['footer_right'] }}</p>
                </footer>
            </div>

            {{-- Active workstation --}}
            <div class="relative flex flex-col gap-4 overflow-hidden rounded-2xl bg-surface p-4 shadow-card">
                <header class="space-y-1 pt-6">
                    <h2 class="gpa-section-title text-ink" x-text="buyer()">{{ $queue['rows'][0]['buyer'] }}</h2>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="gpa-meta font-bold text-ink" x-text="poLabel()">#{{ $queue['rows'][0]['po'] }}</span>
                        <span class="text-line-board" aria-hidden="true">&middot;</span>
                        <span class="gpa-meta font-medium text-ink-body" x-text="commodityGrade()">Komoditas: {{ trim($queue['rows'][0]['commodity'].' '.($queue['rows'][0]['commodity_note'] ?? '')) }}</span>
                    </div>
                    <p class="inline-flex rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 outline-line-board/40"
                        x-text="targetLabel()">Target: {{ $queue['rows'][0]['krat_total'] }} Krat / {{ number_format($queue['rows'][0]['est_kg'], 2, '.', ',') }} kg Gross Target</p>
                </header>

                <div class="space-y-2">
                    @foreach ($station['steps'] as $index => $step)
                        <article @class([
                            'space-y-1.5 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40',
                            'space-y-2' => $index === 1 || $index === 2,
                        ])>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="flex items-center gap-1.5 gpa-meta font-bold text-ink">
                                    <x-gpa.icon :name="$step['icon']" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                                    {{ $step['title'] }}
                                </h3>
                                <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-ink">{{ $step['chip'] }}</span>
                            </div>

                            @if ($step['prefix_label'] ?? null)
                                <p class="flex flex-wrap items-center gap-1.5">
                                    <span class="gpa-meta text-ink-body">{{ $step['prefix_label'] }}</span>
                                    <span class="gpa-meta font-bold text-ink" x-text="barcodePrefix()">{{ $station['barcode_prefix'] }}-{{ substr($queue['rows'][0]['po'], -4) }}-[01..{{ $queue['rows'][0]['krat_total'] }}]</span>
                                </p>
                            @endif

                            @foreach ($step['checks'] ?? [] as $check)
                                <p class="flex items-center gap-1.5 gpa-note text-ink-body">
                                    <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0 text-success-deep" />
                                    {{ $check }}
                                </p>
                            @endforeach

                            @if ($step['pallets'] ?? false)
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach ([1, 2] as $pallet)
                                        <div class="rounded bg-surface p-2 outline outline-1 outline-line-hair"
                                            x-show="palletCount() >= {{ $pallet }}">
                                            <p class="gpa-micro-bold text-ink" x-text="`PALLET #0{{ $pallet }}`">PALLET #0{{ $pallet }}</p>
                                            <p class="gpa-note font-semibold text-ink-body" x-text="palletLabel({{ $pallet }})">{{ str($station['pallet_tier'])->before(':') }}: {{ $station['pallet_tier'] }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @foreach ($step['notes'] as $note)
                                <p class="gpa-note leading-4 {{ $index === 1 ? 'text-success-deep' : 'text-ink-quiet' }}">{{ $note }}</p>
                            @endforeach
                        </article>
                    @endforeach

                    <div class="space-y-1 rounded-lg bg-surface-track p-2 outline outline-1 outline-line-board/30">
                        <p class="gpa-micro-bold text-ink-quiet">{{ $station['qc_note']['label'] }}</p>
                        <p class="text-xs italic leading-4 text-ink">{{ $station['qc_note']['text'] }}</p>
                    </div>
                </div>

                <footer class="space-y-3 border-t border-line-board/40 pt-4">
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($station['buttons'] as $button)
                            <button type="button" @click="stationAction(@js($button['key']))"
                                @class([
                                    'inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors',
                                    'bg-surface text-ink outline outline-1 outline-line-board/70 hover:bg-surface-muted' => $button['variant'] === 'outline',
                                    'bg-surface text-warning outline outline-1 outline-warning/60 hover:bg-warning-cream' => $button['variant'] === 'warning',
                                ])>
                                <x-gpa.icon :name="$button['icon']"
                                    @class([
                                        'h-3.5 w-3.5 shrink-0',
                                        'text-ink' => $button['variant'] === 'outline',
                                        'text-warning' => $button['variant'] === 'warning',
                                    ]) />
                                {{ $button['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <button type="button" @click="stationAction(@js($station['cta']['key']))"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 shadow-pop outline outline-1 outline-brand-line transition-colors hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-surface-disabled disabled:text-ink-quiet disabled:outline-line-board"
                        :disabled="!canTransfer()">
                        <x-gpa.icon name="scale" class="h-4 w-4 shrink-0 text-accent" />
                        <span class="gpa-label font-bold text-accent" x-text="ctaLabel()">{{ $station['cta']['label'] }}</span>
                    </button>
                </footer>
            </div>
        </section>

        {{-- QC sampling log --}}
        <section class="gpa-panel flex flex-col gap-4 p-4">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft/60 pb-3">
                <div class="min-w-0">
                    <h2 class="gpa-section-title flex items-center gap-2 font-inter text-ink">
                        {{ $qc['title'] }}
                    </h2>
                </div>

                <div class="flex shrink-0 items-center gap-2 rounded-lg bg-accent/30 px-3 py-1.5 outline outline-1 outline-success-deep">
                    <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    <div>
                        <p class="gpa-micro-bold text-success-deep">{{ $qc['avg_label'] }}</p>
                        <p class="flex items-baseline gap-1.5">
                            <span class="font-mono text-sm font-bold text-ink" x-text="avgTrim()">{{ $qc['avg_value'] }}</span>
                            <span class="font-inter text-xs text-ink-body">{{ $qc['avg_note'] }}</span>
                        </p>
                    </div>
                </div>
            </header>

            <div class="grid gap-4 pt-1 md:grid-cols-3">
                @foreach ($qc['samples'] as $sample)
                    @php($net = $sample['gross'] - $sample['tara'] - $sample['trim'])
                    <article class="flex cursor-pointer flex-col justify-between gap-3 rounded-lg bg-surface-shell p-4 outline outline-1 outline-line-board/40 transition-colors hover:bg-surface-muted"
                        @click="inspectSample(@js($sample['krat']))">
                        <header class="flex items-center justify-between gap-2 border-b border-line-soft/60 pb-2">
                            <h3 class="gpa-meta font-bold text-ink">{{ $sample['krat'] }}</h3>
                            <span class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink">{{ $sample['status'] }}</span>
                        </header>

                        <div class="space-y-1">
                            <p class="flex items-center justify-between gap-2">
                                <span class="gpa-meta text-ink-body">Bruto Awal:</span>
                                <span class="gpa-meta font-bold text-ink">{{ number_format($sample['gross'], 2, '.', ',') }} kg</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="gpa-meta text-ink-body">Tara Krat Steril:</span>
                                <span class="gpa-meta font-semibold text-ink-body">{{ number_format($sample['tara'], 2, '.', ',') }} kg</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="gpa-meta text-warning">Trimming Afkir:</span>
                                <span class="gpa-meta font-bold text-warning">{{ number_format($sample['trim'], 2, '.', ',') }} kg ({{ number_format($sample['trim_percent'], 2, '.', ',') }}%)</span>
                            </p>
                            <p class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                                <span class="gpa-meta font-bold text-ink">Netto Bersih Siap:</span>
                                <span class="font-mono text-sm font-bold text-success-deep">{{ number_format($net, 2, '.', ',') }} kg</span>
                            </p>
                        </div>

                        <footer class="border-t border-line-hair pt-2 gpa-note text-ink-quiet">
                            {{ $qc['inspector'] }} <span aria-hidden="true">&middot;</span> {{ $qc['sensor_note'] }}
                        </footer>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
