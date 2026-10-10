@extends('layouts.coordinator')

@section('title', 'Penimbangan Aktual & Validasi Sortir')

@section('content')
    <div class="space-y-6"
        x-data="coordinatorWeighing(@js([
            'estimate' => $order['estimate'],
            'rate' => 15000,
            'gross' => $terminal['readings'][0]['value'],
            'crates' => (int) $terminal['inputs'][1]['value'],
            'tareEach' => (float) $terminal['inputs'][2]['value'],
            'tolerance' => 2.0,
        ]))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <h1 class="mt-1 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $header['title_before'] }}<br>
                    {{ $header['title_after'] }}
                </h1>
            </div>

            <div class="shrink-0 rounded-lg bg-surface px-3 py-2 outline outline-1 -outline-offset-1 outline-line-board/50">
                <p class="text-right text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet gpa-meta">
                    {{ $header['timestamp_label'] }}
                </p>
                <p class="mt-0.5 text-right gpa-meta-lg font-semibold text-ink">
                    {{ str_replace(', ', ',<br>', $header['timestamp_value']) }}
                </p>
            </div>
        </section>

        {{-- Stage tracker --}}
        <section class="gpa-panel p-4">
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($stages as $stage)
                    <article @class([
                        'flex items-center gap-4 rounded-lg p-2 transition-colors',
                        'bg-surface-shell outline outline-1 -outline-offset-1 outline-line-board/30' => $stage['state'] === 'done',
                        'bg-accent/40 py-2 outline outline-2 -outline-offset-2 outline-success-deep' => $stage['state'] === 'active',
                        'bg-surface-shell py-2 opacity-70 outline outline-1 -outline-offset-1 outline-line-board/30' => $stage['state'] === 'locked',
                    ])>
                        <span @class([
                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg',
                            'bg-success-deep' => $stage['state'] !== 'locked',
                            'bg-surface-pill' => $stage['state'] === 'locked',
                        ])>
                            <x-gpa.icon :name="$stage['icon']"
                                class="h-[18px] w-[18px] shrink-0 {{ $stage['state'] === 'locked' ? 'text-ink-quiet' : 'text-surface' }}" />
                        </span>

                        <div class="min-w-0">
                            <p @class([
                                'text-2xs font-bold uppercase leading-3 tracking-[0.45px]',
                                'text-success-deep' => $stage['state'] !== 'locked',
                                'text-ink-quiet' => $stage['state'] === 'locked',
                            ])>{{ $stage['step'] }}</p>
                            <p @class([
                                'text-lg leading-6',
                                'font-bold text-ink' => $stage['state'] === 'active',
                                'font-semibold text-ink' => $stage['state'] === 'done',
                                'font-semibold text-ink-quiet' => $stage['state'] === 'locked',
                            ])>
                                {{ $stage['title_before'] }}<br>
                                {{ $stage['title_after'] }}
                            </p>
                            <p @class([
                                'text-2xs font-semibold leading-3 tracking-[1.08px]',
                                'text-ink-body' => $stage['state'] !== 'locked',
                                'text-ink-quiet' => $stage['state'] === 'locked',
                            ])>{{ $stage['note'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Order banner --}}
        <section class="relative flex flex-col justify-center gap-4 rounded-xl bg-brand p-4 outline outline-1 -outline-offset-1 outline-success-deep/30">
            <div class="flex flex-wrap items-center gap-4">
                <span class="rounded-lg bg-success-deep px-3 py-1.5 gpa-meta-lg font-bold text-white">
                    {{ $order['code'] }}
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-lg leading-6 font-bold text-white">{{ $order['client'] }}</p>
                        <span class="rounded-full bg-accent px-2 py-0.5 text-2xs font-bold tracking-[1.08px] text-success-ink gpa-micro-bold">
                            {{ $order['contract'] }}
                        </span>
                    </div>
                    <p class="gpa-meta text-brand-line/70 text-white/70">{{ $order['commodity'] }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <div class="border-l border-ink-quiet/30 pl-4">
                    <p class="text-2xs font-medium leading-3 tracking-[0.88px] text-surface-pill gpa-meta">{{ $order['estimate_label'] }}</p>
                    <p class="text-lg leading-6 font-bold text-white gpa-figure">
                        <span x-text="kg(estimate)">{{ number_format($order['estimate'], 2, '.', '') }}</span>
                        <span class="ml-1 text-xs font-normal">kg</span>
                    </p>
                </div>

                <div class="border-l border-ink-quiet/30 pl-4">
                    <p class="text-2xs font-medium leading-3 tracking-[0.88px] text-surface-pill gpa-meta">{{ $order['rate_label'] }}</p>
                    <p class="text-lg leading-6 font-bold text-accent gpa-figure">{{ $order['rate'] }}<span
                            class="ml-1 text-2xs font-normal text-accent">{{ $order['rate_unit'] }}</span></p>
                </div>

                <div class="border-l border-ink-quiet/30 pl-4">
                    <p class="text-2xs font-medium leading-3 tracking-[0.88px] text-surface-pill gpa-meta">{{ $order['verifier_label'] }}</p>
                    <p class="gpa-meta-lg font-bold text-white">{{ $order['verifier'] }}</p>
                    <p class="gpa-meta-lg font-bold text-brand-line/70">{{ $order['verifier_code'] }}</p>
                </div>
            </div>
        </section>

        {{-- Terminal + QC grading --}}
        <div class="grid items-stretch gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            {{-- Terminal Input Penimbangan Massa Fisik --}}
            <section class="flex h-full min-w-0 flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/30 pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="min-w-0">
                            <h2 class="text-lg leading-6 font-semibold text-ink">{{ $terminal['title'] }}</h2>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $terminal['device'] }}
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-accent py-1 pl-2.5 pr-2.5 outline outline-1 -outline-offset-1 outline-success-deep">
                        <span class="h-2 w-1.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <span class="text-2xs font-bold tracking-[1.08px] text-success-ink gpa-micro-bold">{{ $terminal['iot_badge'] }}</span>
                    </span>
                </div>

                {{-- Dark readout --}}
                <div class="flex flex-col gap-2 rounded-xl bg-ink p-4 outline outline-1 -outline-offset-1 outline-success-deep/40">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-2xs font-semibold tracking-[1.08px] text-surface-pill">{{ $terminal['sensor_id'] }}</p>
                        <p class="text-2xs font-semibold tracking-[1.08px] text-accent">{{ $terminal['calibration'] }}</p>
                    </div>

                    <div class="flex items-start justify-center gap-4">
                        @foreach ($terminal['readings'] as $reading)
                            <div @class([
                                'flex flex-1 flex-col items-center gap-1 px-1 pb-1.5 pt-1',
                                'rounded-lg border-l border-ink-quiet/20 bg-brand/60' => $reading['tone'] === 'highlight',
                            ])>
                                <p @class([
                                    'text-center text-2xs font-semibold uppercase leading-3 tracking-[1.08px]',
                                    'font-bold text-accent' => $reading['tone'] === 'highlight',
                                    'text-surface-pill' => $reading['tone'] !== 'highlight',
                                ])>{{ $reading['label'] }}</p>

                                <p @class([
                                    'text-center text-[28px] leading-5',
                                    'font-bold text-accent' => $reading['tone'] === 'highlight',
                                    'font-bold text-surface-pill' => $reading['key'] === 'tare',
                                    'font-bold text-white' => $reading['key'] === 'gross',
                                ])>
                                    <span x-text="readingValue(@js($reading['key']))">{{ number_format($reading['value'], 2, '.', '') }}</span>
                                </p>

                                <p @class([
                                    'text-center text-2xs font-semibold leading-3 tracking-[1.08px]',
                                    'font-bold text-white' => $reading['tone'] === 'highlight',
                                    'text-ink-quiet' => $reading['key'] === 'tare',
                                    'text-accent' => $reading['key'] === 'gross',
                                ])>{{ $reading['unit'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Inputs --}}
                <div class="flex flex-wrap items-start justify-center gap-4 pt-2">
                    @foreach ($terminal['inputs'] as $input)
                        <div class="w-[149px] space-y-1">
                            <label :for="'weigh-' + @js($input['key'])"
                                class="block text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $input['label'] }}
                            </label>

                            <div class="relative">
                                <input :id="'weigh-' + @js($input['key'])" type="text" inputmode="decimal"
                                    :disabled="locked"
                                    @class([
                                        'h-10 w-full overflow-hidden rounded-lg py-2.5 pl-3 pr-14 outline outline-1 -outline-offset-1 transition-colors',
                                        'bg-surface text-ink outline-line-board' => $input['tone'] === 'plain',
                                        'bg-surface-shell text-ink outline-line-board' => $input['tone'] === 'muted',
                                        'disabled:cursor-not-allowed disabled:opacity-60' => true,
                                    ])
                                    x-model.number="@js($input['key'])">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 gpa-meta font-bold text-ink-quiet">
                                    {{ $input['unit'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2 border-t border-line-board/30 pt-2">
                    <div class="flex flex-wrap items-center gap-1">
                        @foreach ($terminal['actions'] as $terminalAction)
                            <button type="button" @click="act(@js($terminalAction['key']))" :disabled="locked"
                                class="rounded-lg px-3 py-1.5 text-center text-[11px] font-semibold uppercase leading-[14px] tracking-[0.88px] text-ink outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-surface-shell disabled:cursor-not-allowed disabled:opacity-50">
                                {{ $terminalAction['label'] }}
                            </button>
                        @endforeach
                    </div>

                    <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold">
                        {{ $terminal['metrology_note'] }}
                    </p>
                </div>
            </section>

            {{-- Form Quality Control & Grading Sortir Fisik --}}
            <section class="flex h-full min-w-0 flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/30 pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="min-w-0">
                            <h2 class="text-lg leading-6 font-semibold text-ink">{{ $grading['title'] }}</h2>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $grading['device'] }}
                            </p>
                        </div>
                    </div>

                    <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[1px] text-success-ink">
                        {{ $grading['standard'] }}
                    </span>
                </div>

                <div class="gpa-scroll-x overflow-x-auto rounded-xl outline outline-1 -outline-offset-1 outline-line-board/40">
                    <div class="bg-surface-shell">
                        <div class="flex items-center">
                            @foreach ($grading['columns'] as $index => $column)
                                <div @class([
                                    'w-[126px] shrink-0 px-3 py-3 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body',
                                    'w-[74px]' => $index === 1,
                                    'w-[89px]' => $index === 2,
                                    'w-[85px]' => $index === 3,
                                    'w-[106px] flex-1 text-right' => $index === 4,
                                ])>{{ $column }}</div>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($grading['rows'] as $row)
                        @php $warning = $row['tone'] === 'warning'; @endphp
                        <div @class([
                            'flex items-center border-t border-line-board/30',
                            'bg-surface' => ! $warning,
                            'bg-surface-shell/30' => $warning,
                        ])>
                            <div class="w-[126px] shrink-0 space-y-0.5 px-3 py-3">
                                <div class="flex items-center gap-1">
                                    <span @class([
                                        'h-2.5 w-1.5 shrink-0 rounded-full',
                                        'bg-success-deep' => ! $warning,
                                        'bg-warning' => $warning,
                                    ]) aria-hidden="true"></span>
                                    <span @class([
                                        'text-[11px] font-semibold leading-[14px] tracking-[0.88px]',
                                        'text-ink' => ! $warning,
                                        'text-warning-deep' => $warning,
                                    ])>{{ $row['name'] }}</span>
                                </div>

                                @foreach ($row['note'] as $line)
                                    <p class="text-2xs leading-3 tracking-[1.08px] text-ink-quiet">{{ $line }}</p>
                                @endforeach
                            </div>

                            <div class="w-[74px] shrink-0 px-3 py-3">
                                <p @class([
                                    'text-[11px] font-bold leading-[14px] tracking-[0.88px]',
                                    'text-ink' => ! $warning,
                                    'text-warning-deep' => $warning,
                                ])>
                                    <span x-text="kg(gradingMass(@js($row['key'])))">{{ number_format($row['mass'], 2, '.', '') }}</span>
                                    <span class="ml-0.5 text-2xs font-normal">kg</span>
                                </p>
                            </div>

                            <div class="w-[89px] shrink-0 px-3 py-3">
                                <p @class([
                                    'text-[11px] font-bold leading-[14px] tracking-[0.88px]',
                                    'text-success-deep' => ! $warning,
                                    'text-warning-deep' => $warning,
                                ]) x-text="gradingPercent(@js($row['key']))">{{ $row['percent'] }}</p>
                            </div>

                            <div class="w-[85px] shrink-0 px-3 py-3">
                                <span @class([
                                    'inline-flex w-[53px] flex-col rounded px-2 py-1 text-2xs font-bold leading-3 tracking-[1.08px]',
                                    'bg-accent text-success-ink' => ! $warning,
                                    'bg-warning-soft text-warning-ink' => $warning,
                                ])>
                                    @foreach ($row['status'] as $line)
                                        <span>{{ $line }}</span>
                                    @endforeach
                                </span>
                            </div>

                            <div class="w-[106px] flex-1 px-3 py-3 text-right">
                                <p @class([
                                    'text-[11px] font-semibold leading-[14px] tracking-[0.88px]',
                                    'text-success-deep' => ! $warning,
                                    'text-warning-deep' => $warning,
                                ])>
                                    @foreach ($row['action'] as $line)
                                        <span class="block">{{ $line }}</span>
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 -outline-offset-1 outline-ink-quiet/50">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="flex h-14 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-disabled outline outline-1 -outline-offset-1 outline-line-board">
                            <x-gpa.icon :name="$evidence['icon']" class="h-6 w-6 shrink-0 text-ink-quiet" />
                        </span>

                        <div class="min-w-0">
                            <p class="text-lg leading-6 font-semibold text-ink">{{ $evidence['title'] }}</p>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $evidence['description'] }}
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="uploadSample()"
                        class="shrink-0 rounded-lg bg-surface px-6 py-2 text-center text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <span x-text="sampleUploaded ? 'SAMPLE TERSIMPAN' : @js($evidence['action'])">{{ $evidence['action'] }}</span>
                    </button>
                </div>
            </section>
        </div>

        {{-- Rekonsiliasi + Finansial + Audit --}}
        <div class="grid items-stretch gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            {{-- Rekonsiliasi Sumber Pasokan --}}
            <section class="flex h-full min-w-0 flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/30 pb-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <h2 class="text-lg leading-6 font-semibold text-ink">{{ $reconciliation['title'] }}</h2>
                    </div>

                    <p class="shrink-0 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold">
                        {{ $reconciliation['match'] }}
                    </p>
                </div>

                <div class="space-y-2">
                    @foreach ($reconciliation['sources'] as $source)
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board/30">
                            <div class="min-w-0">
                                <p class="gpa-meta-lg font-bold text-ink">{{ $source['name'] }}</p>
                                <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-ink-quiet">{{ $source['batch'] }}</p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="gpa-meta-lg font-bold text-ink">{{ $source['mass'] }}</p>
                                <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-success-deep">{{ $source['percent'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-1 rounded-xl bg-surface-shell p-4 outline outline-1 -outline-offset-1 outline-line-board/40">
                    @foreach ($reconciliation['panel'] as $row)
                        <div @class([
                            'flex items-center justify-between gap-3',
                            'border-t border-line-board/40 pb-1 pt-2' => $row === $reconciliation['panel'][2],
                            'pb-1' => ! ($row === $reconciliation['panel'][2]),
                        ])>
                            <p class="gpa-meta font-medium text-ink-body">{{ $row['label'] }}</p>
                            <p @class([
                                'text-right gpa-meta-lg font-bold',
                                'text-warning-deep' => $row['tone'] === 'warning',
                                'text-success-deep' => $row['tone'] === 'success',
                                'text-ink' => $row['tone'] === 'ink',
                            ])>
                                @if ($row['multiline'] ?? false)
                                    <span x-text="varianceText()">{{ $row['value'] }}</span>
                                @else
                                    {{ $row['value'] }}
                                @endif
                            </p>
                        </div>
                    @endforeach

                    <div class="flex items-center gap-1 pt-1">
                        <x-gpa.icon name="check-circle" class="h-[10px] w-[10px] shrink-0 text-success-deep" />
                        <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold"
                            x-text="toleranceText()">{{ $reconciliation['status'] }}</p>
                    </div>
                </div>
            </section>

            <div class="flex h-full min-w-0 flex-col">
                {{-- Perhitungan Finansial Otomatis --}}
                <section class="flex h-full min-w-0 flex-col gap-4 rounded-2xl bg-surface p-6 outline outline-2 -outline-offset-2 outline-success-deep/50">
                    <div class="flex items-center gap-2 border-b border-line-board/30 pb-2">
                        <div class="min-w-0">
                            <h2 class="text-lg leading-6 font-semibold text-ink">{{ $financial['title'] }}</h2>
                            <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold">
                                {{ $financial['meta'] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pb-2">
                        @foreach ($financial['rows'] as $index => $row)
                            <div class="flex items-center justify-between gap-3">
                                <p @class([
                                    'gpa-meta font-medium',
                                    'text-warning-deep' => $row['tone'] === 'warning',
                                    'text-ink-body' => $row['tone'] !== 'warning',
                                ])>{{ $row['label'] }}</p>

                                <p @class([
                                    'text-right gpa-meta-lg font-semibold',
                                    'line-through text-ink-quiet' => $row['tone'] === 'muted-strike',
                                    'font-semibold text-warning-deep' => $row['tone'] === 'warning',
                                    'text-ink' => $row['tone'] === 'ink',
                                ])>{{ $row['value'] }}</p>
                            </div>
                        @endforeach

                        <div class="flex flex-col gap-1 border-t border-line-board/40 pt-2">
                            <p class="text-2xs font-bold uppercase leading-3 tracking-[0.45px] text-success-deep gpa-micro-bold">
                                {{ $financial['final_label'] }}
                            </p>
                            <p class="text-3xl leading-10 font-extrabold text-ink" x-text="finalValue()">{{ $financial['final_value'] }}</p>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                {{ $financial['final_note'] }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board/30">
                        @foreach ($financial['metrology'] as $row)
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">{{ $row['label'] }}</p>
                                <p class="text-right text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink gpa-micro-bold">
                                    {{ $row['value'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </section>

            </div>
        </div>

        {{-- Lock bar --}}
        <section class="relative flex flex-col justify-center gap-4 rounded-2xl bg-ink p-4 outline outline-1 -outline-offset-1 outline-success-deep/40">
            <div class="flex flex-wrap items-center gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent">
                    <x-gpa.icon name="lock" class="h-[22px] w-[22px] shrink-0 text-success-ink" />
                </span>

                <div class="min-w-0">
                    <p class="gpa-meta-lg font-bold text-white">
                        <span x-text="confirmTitle()">{{ $actions['confirm_title'] }}</span>
                    </p>
                    <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-accent gpa-micro-bold">
                        <span x-show="! locked">{{ $actions['confirm_note'] }}</span>
                        <span x-cloak x-show="locked">Data terkunci permanen. Surat Jalan siap diterbitkan.</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <button type="button" @click="act('draft')" :disabled="locked"
                    class="rounded-lg px-12 py-2.5 text-center text-[11px] font-semibold uppercase leading-[14px] tracking-[0.88px] text-white outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-50">
                    {{ $actions['draft']['label'] }}
                </button>

                <button type="button" @click="act('lock')" :disabled="locked"
                    class="inline-flex items-center gap-1 rounded-lg bg-accent px-6 py-2.5 shadow-pop transition-colors hover:bg-accent-deep disabled:cursor-not-allowed disabled:opacity-70">
                    <x-gpa.icon name="lock" class="h-3 w-3 shrink-0 text-ink" />
                    <span class="text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink" x-text="locked ? 'DATA TERKUNCI' : @js($actions['lock']['label'])">
                        {{ $actions['lock']['label'] }}
                    </span>
                </button>

                <button type="button" @click="act('delivery')" :disabled="! locked"
                    :class="locked
                        ? 'bg-accent text-ink hover:bg-accent-deep'
                        : 'cursor-not-allowed bg-surface-disabled/20 text-ink-quiet outline outline-1 -outline-offset-1 outline-ink-quiet/30'"
                    class="inline-flex items-center gap-1 rounded-lg px-4 py-2.5 transition-colors">
                    <x-gpa.icon name="truck" class="h-4 w-3 shrink-0" />
                    <span class="text-[11px] font-medium uppercase leading-[14px] tracking-[0.88px]">{{ $actions['delivery']['label'] }}</span>
                </button>
            </div>
        </section>
    </div>
@endsection
