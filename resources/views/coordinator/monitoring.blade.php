@extends('layouts.coordinator')

@section('title', 'Monitoring & Validasi Proof of Delivery (PoD)')

@section('content')
    @php
        $mandate = $monitoringData['mandate'];
        $kpis = $monitoringData['kpis'];
        $dispatch = $monitoringData['dispatch'];
        $dossier = $monitoringData['dossier'];
        $kg = fn ($value) => rtrim(rtrim(number_format((float) $value, 1, '.', ''), '0'), '.');
        $defaultEntry = $dossier['entries'][$dispatch['default_sj']];
        $checkLabels = [
            $dossier['checks'][0]['key'] => $dossier['checks'][0]['label'],
            $dossier['checks'][1]['key'] => $dossier['checks'][1]['label'],
            $dossier['checks'][2]['key'] => $dossier['checks'][2]['label'].' ('.$kg($defaultEntry['net_kg']).' kg)',
            $dossier['checks'][3]['key'] => $dossier['checks'][3]['label'].' < '.$kg($defaultEntry['gps_tolerance_m']).'m Dari Dock',
        ];
    @endphp

    <div class="space-y-4"
        x-data="coordinatorMonitoring(@js($dispatch['rows']), @js($dossier), @js([
            'defaultSj' => $dispatch['default_sj'],
            'dropoffBase' => $dispatch['dropoff_base'],
            'dropoffTotal' => $dispatch['dropoff_total'],
        ]))">
        {{-- PRD Rule 06 & 12: hard gate PoD --}}
        <section class="gpa-card flex flex-wrap items-start justify-between gap-4 border border-line-hair border-l-4 border-l-brand p-4">
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-9 shrink-0 items-center justify-center rounded-lg bg-brand text-accent">
                    <x-gpa.icon :name="$mandate['icon']" class="h-4 w-5 shrink-0" />
                </span>
                <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="gpa-section-title text-ink">{{ $mandate['title'] }}</h1>
                        <span
                            class="shrink-0 rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink outline outline-1 outline-success-deep">
                            {{ $mandate['badge'] }}
                        </span>
                    </div>
                    <p class="max-w-4xl text-sm leading-5 text-ink-body">
                        {{ $mandate['body_lead'] }} <strong class="font-semibold text-ink">{{ $mandate['body_emphasis'] }}</strong> {{ $mandate['body_tail'] }}
                    </p>
                </div>
            </div>

            <div class="shrink-0 text-right">
                <p class="gpa-micro-bold text-ink-quiet">{{ $mandate['protocol_label'] }}</p>
                <p class="mt-1 rounded bg-surface-shell px-4 py-1 font-mono text-[11px] font-bold text-ink outline outline-1 outline-line-board/50">
                    {{ $mandate['protocol_value'] }}
                </p>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                <article class="gpa-card flex flex-col justify-between gap-2 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <h2 @class([
                            'max-w-[11rem] gpa-micro-bold leading-3',
                            'text-ink-body' => $kpi['label_tone'] === 'body',
                            'text-warning-deep' => $kpi['label_tone'] === 'warning-deep',
                        ])>{{ $kpi['label'] }}</h2>
                        <x-gpa.icon :name="$kpi['icon']"
                            @class([
                                'block h-5 w-5 shrink-0',
                                'text-success-deep' => $kpi['icon_tone'] === 'success-deep',
                                'text-danger' => $kpi['icon_tone'] === 'danger',
                            ]) />
                    </div>

                    <p class="flex items-baseline gap-2">
                        <span @class([
                            'font-inter text-3xl font-bold leading-10',
                            'text-ink' => $kpi['key'] !== 'retur',
                            'text-danger' => $kpi['key'] === 'retur',
                        ])
                            @if ($kpi['key'] === 'dropoff')
                                x-text="dropoffValue()"
                            @endif>{{ $kpi['value'] }}</span>

                        <span @class([
                            'text-xs',
                            'font-medium' => $kpi['key'] !== 'retur',
                            'text-ink-body' => $kpi['unit_tone'] === 'body',
                            'font-medium text-danger' => $kpi['unit_tone'] === 'danger',
                        ])
                            @if ($kpi['key'] === 'dropoff')
                                x-text="dropoffUnit()"
                            @endif>{{ $kpi['unit'] }}</span>
                    </p>

                    <footer class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        <span @class([
                            'inline-flex items-center gap-1.5 gpa-micro-bold',
                            'text-success-deep' => $kpi['foot_left_tone'] === 'success-deep',
                            'text-ink-body' => $kpi['foot_left_tone'] === 'body',
                            'text-danger' => $kpi['foot_left_tone'] === 'danger',
                        ])>
                            @if ($kpi['foot_dot'])
                                <span class="h-3 w-1 rounded-full bg-accent" aria-hidden="true"></span>
                            @endif
                            <span @if ($kpi['key'] === 'dropoff') x-text="dropoffNote()" @endif>{{ $kpi['foot_left'] }}</span>
                        </span>
                        <span @class([
                            'gpa-micro-bold',
                            'text-success-deep' => $kpi['foot_right_tone'] === 'success-deep',
                            'text-ink-quiet' => $kpi['foot_right_tone'] === 'muted',
                            'text-warning-caution' => $kpi['foot_right_tone'] === 'warning-caution',
                        ])>{{ $kpi['foot_right'] }}</span>
                    </footer>
                </article>
            @endforeach
        </section>

        {{-- Dispatch log + PoD dossier --}}
        <section class="grid gap-4 2xl:grid-cols-[minmax(0,1fr)_23rem]">
            {{-- Dispatch log --}}
            <div class="gpa-card flex flex-col gap-4 p-4">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/40 pb-3">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title flex items-center gap-2 text-ink">
                            <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-success-deep" />
                            {{ $dispatch['title'] }}
                        </h2>
                        <p class="mt-1 font-mono text-[11px] font-medium leading-4 text-ink-body">{{ $dispatch['subtitle'] }}</p>
                    </div>
                    <button type="button" @click="refreshTelemetry()"
                        class="shrink-0 rounded-lg bg-surface-shell px-3 py-1 gpa-note text-ink outline outline-1 outline-line-board/50 transition-colors hover:bg-surface-muted">
                        {{ $dispatch['badge'] }}
                    </button>
                </header>

                <div class="gpa-scroll-x overflow-x-auto">
                    <table class="w-full min-w-[56rem] border-collapse">
                        <caption class="sr-only">{{ $dispatch['title'] }}</caption>
                        <thead>
                            <tr class="border-b border-line-board/40 bg-surface-shell">
                                @foreach ($dispatch['columns'] as $position => $column)
                                    <th scope="col"
                                        class="px-3 py-2 gpa-micro-bold text-ink-body {{ $position === 2 ? 'text-right' : ($position === 4 ? 'text-center' : 'text-left') }}">
                                        {{ $column }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dispatch['rows'] as $row)
                                <tr @click="select(@js($row['sj']))"
                                    class="cursor-pointer border-b border-line-hair/70 align-middle transition-colors hover:bg-surface-shell"
                                    :class="isActive(@js($row['sj'])) ? 'bg-accent/20' : ''">
                                    <th scope="row"
                                        class="border-l-4 border-transparent px-3 py-3 text-left align-middle"
                                        :class="isActive(@js($row['sj'])) ? '!border-brand' : ''">
                                        <span class="block text-xs font-bold leading-4 text-ink">{{ $row['driver'] }}</span>
                                        <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $row['vehicle'] }}</span>
                                        <span @class([
                                            'mt-1 block gpa-note font-semibold',
                                            'text-success-deep' => $row['arrival_tone'] === 'success-deep',
                                            'text-warning-deep' => $row['arrival_tone'] === 'warning-deep',
                                        ])>{{ $row['arrival'] }}</span>
                                    </th>
                                    <td class="px-3 py-3 align-middle">
                                        <span class="block font-mono text-[11px] font-bold leading-4 text-ink">{{ $row['sj'] }}</span>
                                        <span @class([
                                            'mt-0.5 block text-xs leading-4',
                                            'font-bold text-ink' => $row['customer_tone'] === 'strong',
                                            'font-medium text-ink-body' => $row['customer_tone'] === 'body',
                                        ])>{{ $row['customer'] }}</span>
                                        @if ($row['discrepancy'])
                                            <span class="mt-0.5 block gpa-note font-semibold text-danger">{{ $row['discrepancy'] }}</span>
                                        @else
                                            <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $row['destination'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-right align-middle">
                                        <span @class([
                                            'block font-mono text-[11px] font-bold leading-4',
                                            'text-danger' => $row['manifest_kg'] !== null,
                                            'text-ink' => $row['manifest_kg'] === null,
                                        ]) x-text="cargoLabel(@js($row['sj']))">{{ $kg($row['cargo_kg']) }} kg</span>
                                        <span @class([
                                                'mt-0.5 block gpa-note',
                                                'line-through' => $row['manifest_kg'] !== null,
                                            ])
                                            :class="[cargoNoteTone(@js($row['sj'])), manifestStrike(@js($row['sj']))]"
                                            x-text="cargoNote(@js($row['sj']))">{{ $row['manifest_kg'] !== null ? 'Manifest: '.$kg($row['manifest_kg']).' kg' : $row['commodity'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center align-middle">
                                        <span class="inline-flex items-center rounded px-2 py-1 outline outline-1 gpa-micro-bold"
                                            :class="statusTone(@js($row['sj']))" x-text="statusLabel(@js($row['sj']))">{{ $row['status'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <div class="flex flex-col items-center gap-1">
                                            @foreach ($row['actions'] as $action)
                                                <button type="button" @click.stop="rowAction(@js($action['key']), @js($row['sj']))"
                                                    @class([
                                                        'inline-flex w-full items-center justify-center gap-1.5 rounded px-2 py-1.5 gpa-meta-lg transition-colors',
                                                        'bg-brand text-accent shadow-sub outline outline-1 outline-brand-line hover:bg-brand-hover' => $action['variant'] === 'primary',
                                                        'bg-surface text-ink outline outline-1 outline-line-board/70 hover:bg-surface-muted' => $action['variant'] === 'outline',
                                                        'bg-surface-shell text-ink outline outline-1 outline-line-board/40 hover:bg-surface-muted' => $action['variant'] === 'soft',
                                                        'bg-surface text-danger outline outline-1 outline-danger/50 hover:bg-danger-soft' => $action['variant'] === 'danger',
                                                    ])>
                                                    <x-gpa.icon :name="$action['icon']"
                                                        @class([
                                                            'h-3 w-3 shrink-0',
                                                            'text-accent' => $action['variant'] === 'primary',
                                                        ]) />
                                                    <span class="font-semibold">{{ $action['label'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <footer class="space-y-1 border-t border-line-board/40 pt-2">
                    <p class="flex items-center gap-2 gpa-micro-bold text-ink-quiet">
                        <span class="h-2 w-2 rounded-full bg-accent" aria-hidden="true"></span>
                        {{ $dispatch['footer_left'] }}
                    </p>
                    <p class="gpa-micro-bold text-ink-quiet">{{ $dispatch['footer_right'] }}</p>
                </footer>
            </div>

            {{-- PoD verification dossier --}}
            <div class="gpa-card flex flex-col gap-3 p-4">
                <header class="flex flex-wrap items-start justify-between gap-2 border-b border-line-board/40 pb-2">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title flex items-center gap-2 text-ink">
                            <x-gpa.icon :name="$dossier['icon']" class="h-4 w-4 shrink-0 text-success-deep" />
                            {{ $dossier['title'] }}
                        </h2>
                        <p class="mt-1 font-mono text-[11px] font-medium leading-4 text-ink-body">{{ $dossier['subtitle'] }}</p>
                    </div>
                    <span class="shrink-0 rounded px-2 py-0.5 gpa-micro-bold text-ink outline outline-1"
                        :class="hasDossier() ? 'bg-accent outline-success-deep' : 'bg-surface-shell text-ink-quiet outline-line-board/50'"
                        x-text="hasDossier() ? @js($dossier['badge']) : 'BERKAS MENUNGGU'">{{ $dossier['badge'] }}</span>
                </header>

                <div x-cloak x-show="!hasDossier()"
                    class="space-y-1 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/40">
                    <p class="flex items-center gap-1.5 gpa-micro-bold text-warning-deep">
                        <x-gpa.icon name="clock" class="h-3.5 w-3.5 shrink-0" />
                        {{ $dossier['empty_title'] }}
                    </p>
                    <p class="text-xs leading-5 text-ink-body">{{ $dossier['empty_body'] }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40"
                    :class="hasDossier() ? '' : 'opacity-50'">
                    <div class="min-w-0">
                        <p class="gpa-micro-bold text-ink-quiet">{{ $dossier['reference_label'] }}</p>
                        <p class="font-mono text-[11px] font-bold text-ink" x-text="reference()">{{ $defaultEntry['ref'] }}</p>
                    </div>
                    <div class="min-w-0 text-right">
                        <p class="gpa-micro-bold text-ink-quiet">{{ $dossier['sj_label'] }}</p>
                        <p class="font-mono text-[11px] font-bold text-success-deep" x-text="associatedSj()">{{ $defaultEntry['sj'] }}</p>
                    </div>
                </div>

                <div x-show="hasDossier()" class="grid gap-2 sm:grid-cols-2 2xl:grid-cols-1">
                    @foreach ($defaultEntry['exhibits'] as $index => $exhibit)
                        <article class="space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="gpa-micro-bold text-ink" x-text="exhibitField({{ $index }}, 'code')">{{ $exhibit['code'] }}</h3>
                                <p class="gpa-micro-bold text-success-deep" x-text="exhibitField({{ $index }}, 'meta')">{{ $exhibit['meta'] }}</p>
                            </div>
                            <div class="overflow-hidden rounded-lg bg-surface-track outline outline-1 outline-line-board/60">
                                <div class="flex aspect-[156/174] w-full flex-col items-center justify-center gap-2 p-2">
                                    <x-gpa.icon :name="$exhibit['icon']" class="h-5 w-5 text-ink-quiet" />
                                    <p class="gpa-note text-center text-ink-quiet">BUKTI FOTOGRAFI {{ $index + 1 }} DARI {{ count($defaultEntry['exhibits']) }}</p>
                                </div>
                                <div class="flex items-center justify-between gap-2 bg-brand/85 px-1.5 py-1.5">
                                    <span class="truncate gpa-note text-white" x-text="exhibitField({{ $index }}, 'caption')">{{ $exhibit['caption'] }}</span>
                                    <span class="shrink-0 gpa-micro-bold text-accent" x-text="exhibitField({{ $index }}, 'status')">{{ $exhibit['status'] }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="hasDossier()"
                    class="space-y-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/60">
                    <p class="gpa-micro-bold text-ink">{{ $dossier['checks_label'] }}</p>
                    <div class="space-y-1.5">
                        @foreach ($dossier['checks'] as $check)
                            <button type="button" @click="toggleCheck(@js($check['key']))"
                                class="flex w-full items-start gap-2 text-left">
                                <span class="mt-0.5 inline-flex h-4 w-4 shrink-0 items-center justify-center rounded"
                                    :class="checkTone(@js($check['key']))">
                                    <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                                </span>
                                <span class="text-xs leading-5 text-ink" x-text="checkLabel(@js($check['key']))">{{ $checkLabels[$check['key']] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div x-show="hasDossier()" class="space-y-1">
                    <p class="gpa-micro-bold text-ink-quiet">{{ $dossier['notes_label'] }}</p>
                    <textarea x-model="notes" rows="3"
                        class="w-full resize-none rounded-lg border border-line-board bg-surface p-2 text-xs leading-5 text-ink outline-none transition-colors focus:border-brand focus:ring-2 focus:ring-accent/30"
                        placeholder="Catatan koordinator gudang untuk audit trail...">{{ $defaultEntry['notes'] }}</textarea>
                </div>

                <footer class="space-y-2 border-t border-line-board/40 pt-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/60">
                        <p class="font-mono text-[11px] font-bold text-ink">{{ $dossier['decision_label'] }}</p>
                        <div class="flex flex-wrap items-center gap-4">
                            @foreach ($dossier['decision'] as $decision)
                                <button type="button" @click="setDecision(@js($decision['key']))"
                                    class="inline-flex items-center gap-1.5"
                                    :aria-pressed="isDecision(@js($decision['key'])) ? 'true' : 'false'">
                                    <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                        :class="isDecision(@js($decision['key'])) ? 'bg-brand' : 'bg-surface outline outline-2 outline-line-board'">
                                        <span x-show="isDecision(@js($decision['key']))" class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                                    </span>
                                    <span @class([
                                        'font-mono text-[11px] font-semibold',
                                        'text-success-deep' => $decision['tone'] === 'success-deep',
                                        'text-danger' => $decision['tone'] === 'danger',
                                    ])>{{ $decision['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" @click="dossierAction(@js($dossier['cta']['key']))"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 text-accent shadow-pop outline outline-1 outline-brand-line transition-colors hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-surface-disabled disabled:text-ink-quiet disabled:shadow-sub disabled:outline-line-board"
                        :disabled="!canValidate()">
                        <x-gpa.icon :name="$dossier['cta']['icon']" class="h-4 w-4 shrink-0 text-accent" />
                        <span class="gpa-label font-bold text-accent">{{ $dossier['cta']['label'] }}</span>
                    </button>

                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($dossier['buttons'] as $button)
                            <button type="button" @click="dossierAction(@js($button['key']))"
                                @class([
                                    'inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors',
                                    'bg-surface text-ink outline outline-1 outline-line-board/70 hover:bg-surface-muted' => $button['variant'] === 'outline',
                                    'bg-surface text-danger outline outline-1 outline-danger/50 hover:bg-danger-soft' => $button['variant'] === 'danger',
                                ])>
                                <x-gpa.icon :name="$button['icon']" class="h-3.5 w-3.5 shrink-0" />
                                {{ $button['label'] }}
                            </button>
                        @endforeach
                    </div>
                </footer>
            </div>
        </section>
    </div>
@endsection
