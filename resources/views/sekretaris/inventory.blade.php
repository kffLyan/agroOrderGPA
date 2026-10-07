@extends('layouts.sekretaris')

@section('title', 'Stok')

@section('content')
    <div class="space-y-6"
        x-data="secretaryInventory(@js($ledger['rows']), @js(['atp' => $ledger['atp_total'], 'criticalThreshold' => $ledger['critical_threshold']]))"
        @gpa:search.window="query = $event.detail">
        <section class="border-b border-line-soft pb-6">
            <h1 class="font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                {{ $heading['title'] }}
            </h1>
            <p class="mt-3 max-w-4xl text-sm leading-[1.42rem] text-ink-body">{{ $heading['subtitle'] }}</p>
        </section>

        <div class="flex flex-wrap gap-2">
            @foreach ($actions as $action)
                <button type="button" @click="handleAction(@js($action))" @class([
                    'inline-flex h-10 items-center gap-1.5 rounded-lg px-4 shadow-sub transition-colors',
                    'gpa-meta-lg font-bold tracking-[0.88px]',
                    'bg-brand text-accent outline outline-1 outline-accent/50 hover:bg-brand-hover' => $action['tone'] === 'primary',
                    'bg-surface text-ink outline outline-1 outline-line-board hover:bg-surface-shell' => $action['tone'] !== 'primary',
                ])>
                    <span @class([
                        'h-2.5 w-2.5 shrink-0 rounded-full',
                        'h-3 w-3 bg-accent' => $action['tone'] === 'primary',
                        'bg-success-deep' => $action['tone'] !== 'primary' && $loop->first,
                        'bg-ink' => $action['tone'] !== 'primary' && ! $loop->first,
                    ]) aria-hidden="true"></span>
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>

        <section class="gpa-panel rounded-xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft pb-3">
                <h2 class="flex items-center gap-2 gpa-meta-lg font-bold tracking-[0.35px] text-ink">
                    <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                    {{ $ruleEngine['title'] }}
                </h2>
                <span class="shrink-0 rounded bg-accent px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-ink">
                    {{ $ruleEngine['chip'] }}
                </span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($ruleEngine['rules'] as $rule)
                    <article class="flex items-start gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/30">
                        <span @class([
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                            $rule['icon_tile'],
                        ])>
                            <x-gpa.icon :name="$rule['icon']" class="h-3.5 w-3.5 shrink-0 {{ $rule['icon_class'] }}" />
                        </span>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <h3 class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $rule['title'] }}</h3>
                                <span @class([
                                    'shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold tracking-[1.08px]',
                                    $rule['badge']['class'],
                                ])>{{ $rule['badge']['label'] }}</span>
                            </div>
                            <p class="mt-1.5 text-xs leading-4 text-ink-body">{{ $rule['body'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article @class([
                    'relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-xl bg-surface p-4 shadow-card',
                    'outline outline-1 outline-accent/30' => ! empty($metric['glow']),
                ])>
                    @if (! empty($metric['glow']))
                        <span class="pointer-events-none absolute -right-4 -top-4 h-12 w-12 rounded-full bg-accent/30"
                            aria-hidden="true"></span>
                    @endif

                    <div class="flex items-start gap-2">
                        <x-gpa.icon :name="$metric['icon']" class="h-4 w-4 shrink-0 {{ $metric['icon_class'] }}" />
                        <h3 @class([
                            'gpa-micro uppercase tracking-[0.55px]',
                            'font-bold' => ! empty($metric['label_class']),
                            $metric['label_class'],
                        ])>{{ $metric['label'] }}</h3>
                    </div>

                    @if ($metric['key'] === 'composition')
                        <p class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] text-ink">
                            <span>{{ $metric['value'] }}</span>
                            <span class="text-sm font-normal text-ink-body">vs</span>
                            <span class="ml-1 font-sans text-sm font-normal text-ink-body">{{ $metric['unit'] }}</span>
                            <br />
                            <span>{{ $metric['value_second'] }}</span>
                            <span class="ml-1 font-sans text-sm font-normal text-ink-body">{{ $metric['unit_second'] }}</span>
                        </p>
                    @else
                        <p class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] {{ $metric['value_class'] ?? 'text-ink' }}">
                            {{ $metric['value'] }}
                            <span class="ml-1 font-mono text-sm font-normal {{ $metric['unit_class'] }}">{{ $metric['unit'] }}</span>
                        </p>
                    @endif

                    @if (! empty($metric['badge']))
                        <span @class([
                            'mt-2 inline-flex w-fit items-center gap-1 rounded px-2 py-0.5 gpa-micro-bold tracking-[1.08px]',
                            $metric['badge']['class'],
                        ])>{{ $metric['badge']['label'] }}</span>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-1 border-t border-line-soft pt-2">
                        @if (! empty($metric['foot']))
                            @foreach ($metric['foot'] as $foot)
                                <p @class(['gpa-note', $foot['class'] ?? ''])>
                                    @if (! empty($foot['label']) && ! empty($foot['value']))
                                        <span class="text-ink-body">{{ $foot['label'] }}</span>
                                        <span class="ml-1 font-bold {{ $foot['value_class'] }}">{{ $foot['value'] }}</span>
                                    @else
                                        <span @class(['font-bold', $foot['class'] ?? ''])>{{ $foot['label'] }}</span>
                                    @endif
                                </p>
                            @endforeach
                        @endif

                        @if (! empty($metric['foot_label']))
                            <p class="gpa-note text-ink-body">{{ $metric['foot_label'] }}</p>
                        @endif

                        @if (! empty($metric['foot_chip']))
                            <span @class(['shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold', $metric['foot_chip']['class']])>
                                {{ $metric['foot_chip']['label'] }}
                            </span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <section class="gpa-panel flex flex-wrap items-end justify-between gap-4 px-4 py-4">
            <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($filters as $filter)
                    <label class="block min-w-0" for="filter-{{ $filter['key'] }}">
                        <span class="mb-1.5 flex items-center gap-1.5 gpa-micro uppercase tracking-[0.55px] text-ink-body">
                            <x-gpa.icon :name="$filter['icon']" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                            {{ $filter['label'] }}
                        </span>
                        <span class="relative block">
                            <select id="filter-{{ $filter['key'] }}" x-model="{{ $filter['key'] }}"
                                @change="handleFilterChange()"
                                class="block h-10 w-full appearance-none rounded-lg border-line-board bg-surface pr-9 text-xs font-semibold text-ink focus:border-brand focus:ring-brand">
                                @foreach ($filter['options'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <x-gpa.icon name="chevron-down"
                                class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-quiet" />
                        </span>
                    </label>
                @endforeach

                <label class="flex h-10 cursor-pointer items-center gap-2 self-end rounded-lg px-1 text-xs font-semibold text-ink"
                    for="filter-critical">
                    <input type="checkbox" id="filter-critical" x-model="criticalOnly" @change="toggleCritical()"
                        class="gpa-check">
                    <span>Hanya Tampilkan Kuota &lt; 20%</span>
                </label>
            </div>

            <button type="button" @click="bulkLock()"
                class="inline-flex h-11 shrink-0 items-center gap-2 rounded-lg bg-ink px-4 text-accent shadow-sub outline outline-1 outline-accent/40 transition-colors hover:bg-brand-hover gpa-meta-lg font-bold tracking-[0.88px]">
                <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0" />
                Kunci Kuota Massal
            </button>
        </section>

        <section class="gpa-panel overflow-hidden rounded-xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 bg-surface-shell px-4 py-3">
                <div class="flex min-w-0 flex-wrap items-center gap-2">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        <x-gpa.icon name="gauge" class="h-[18px] w-[18px] shrink-0 text-success-deep" />
                        {{ $ledger['title'] }}
                    </h2>
                    <span class="shrink-0 rounded bg-surface-pill px-2 py-0.5 gpa-micro-bold text-ink">
                        {{ $ledger['chip'] }}
                    </span>
                </div>
                <p class="gpa-micro shrink-0 uppercase tracking-[1.08px] text-ink-quiet">{{ $ledger['mutasi'] }}</p>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[1180px] border-collapse text-left">
                    <thead class="border-b border-line-board bg-surface-pill/40">
                        <tr>
                            @foreach ($ledger['columns'] as $key => $column)
                                <th scope="col" @class([
                                    'px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink',
                                    'text-right' => $key === 'locked' || $key === 'atp',
                                ])>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-faint">
                        @foreach ($ledger['rows'] as $row)
                            <tr x-show="matchesFilter(@js($row))" class="align-top transition-colors hover:bg-surface-shell/60">
                                <td class="px-4 py-3">
                                    <p class="font-sans text-sm font-bold leading-5 text-ink">{{ $row['name'] }}</p>
                                    <p class="mt-1 gpa-note text-ink-quiet">{{ $row['sku'] }}</p>
                                    <p class="gpa-note text-ink-quiet">{{ $row['grade'] }}</p>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $row['binawan_value'] }}</p>
                                    <p class="gpa-note text-ink-quiet">{{ $row['binawan_share'] }}</p>
                                    <div class="mt-1.5 h-1.5 w-full max-w-[9rem] overflow-hidden rounded-full bg-surface-pill">
                                        <div class="h-full rounded-full bg-success-deep"
                                            :style="'width: ' + {{ $row['binawan_percent'] }} + '%'"></div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] {{ $row['buffer_class'] }}">
                                        {{ $row['buffer_value'] }}
                                    </p>
                                    <p class="gpa-note text-ink-body">{{ $row['buffer_partner'] }}</p>
                                    @if (! empty($row['buffer_badge']))
                                        <span @class([
                                            'mt-1 inline-flex rounded px-1.5 py-0.5 gpa-micro-bold',
                                            $row['buffer_badge']['class'],
                                        ])>{{ $row['buffer_badge']['label'] }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $row['total_value'] }}</p>
                                    <p class="gpa-note text-ink-quiet">Gudang + Kebun</p>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-danger">{{ $row['locked_value'] }}</p>
                                    <span class="mt-1 inline-flex rounded bg-danger-soft/10 px-1.5 py-0.5 gpa-micro-bold text-danger-ink">
                                        {{ $row['locked_note'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] {{ $row['atp_class'] }}">
                                        {{ $row['atp_value'] }}
                                    </p>
                                    <span class="mt-1 inline-flex rounded bg-accent/20 px-1.5 py-0.5 gpa-micro-bold text-ink-body">
                                        {{ $row['atp_note'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded px-2 py-0.5 gpa-micro-bold',
                                        $row['status']['class'],
                                    ])>
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                        {{ $row['status']['label'] }}
                                    </span>

                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <button type="button" @click="detail(@js($row))"
                                            class="inline-flex h-7 items-center gap-1 rounded bg-surface-track px-2 text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-shell gpa-micro-bold uppercase tracking-[0.88px]">
                                            <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                            Detail
                                        </button>
                                        <button type="button" @click="rowAction(@js($row))"
                                            @class([
                                                'inline-flex h-7 items-center gap-1 rounded px-2 transition-colors gpa-micro-bold uppercase tracking-[0.88px]',
                                                $row['action']['class'],
                                            ])>{{ $row['action']['label'] }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        <tr x-cloak x-show="visibleCount() === 0">
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="text-xs font-semibold text-ink">Tidak ada komoditas pada filter ini.</p>
                                <p class="mt-1 gpa-note text-ink-quiet">
                                    Ubah pilihan filter atau matikan filter kuota di bawah 20%.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 bg-surface-shell px-4 py-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + visibleCount() + ' dari ' + {{ count($ledger['rows']) }} + ' komoditas operasional aktif'">
                </p>
                <p class="gpa-note text-ink-body">
                    Total Kuota Bebas (ATP): <span x-text="atpFooter()"></span>
                </p>
                <p class="w-full uppercase tracking-[1.08px] text-ink-quiet gpa-micro">
                    {{ $ledger['footer_note'] }}
                </p>
            </div>
        </section>

        <div class="grid items-start gap-4 xl:grid-cols-2">
            <section class="gpa-panel overflow-hidden rounded-xl">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft px-4 py-3">
                    <h2 class="font-sans text-lg font-bold text-ink">{{ $activity['title'] }}</h2>
                    <span class="shrink-0 rounded bg-ink px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                        {{ $activity['chip'] }}
                    </span>
                </div>

                <ul class="divide-y divide-line-soft">
                    @foreach ($activity['events'] as $event)
                        <li class="flex items-start gap-3 px-4 py-3">
                            <span @class([
                                'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg',
                                $event['icon_tile'],
                            ])>
                                <x-gpa.icon :name="$event['icon']" class="h-4 w-4 shrink-0 {{ $event['icon_class'] }}" />
                            </span>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h3 class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $event['title'] }}</h3>
                                    <span class="shrink-0 rounded bg-surface-pill px-1.5 py-0.5 gpa-micro-bold text-ink">
                                        {{ $event['ref'] }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs leading-5 text-ink-body">{{ $event['body'] }}</p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p @class(['gpa-note font-bold', $event['time_class']])>{{ $event['time'] }}</p>
                                <p class="mt-1 uppercase tracking-[1.08px] text-ink-quiet gpa-micro">{{ $event['tag'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft px-4 py-3">
                    <p class="gpa-note text-ink-quiet">{{ $activity['footer_left'] }}</p>
                    <button type="button" @click="syncLog()"
                        class="inline-flex items-center gap-1 gpa-note text-success-deep underline underline-offset-2 hover:text-success">
                        {{ $activity['footer_right'] }}
                        <x-gpa.icon name="arrow-right" class="h-3 w-3" />
                    </button>
                </div>
            </section>

            <section class="gpa-panel overflow-hidden rounded-xl">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft px-4 py-3">
                    <h2 class="font-sans text-lg font-bold text-ink">{{ $readiness['title'] }}</h2>
                    <span class="shrink-0 rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                        {{ $readiness['chip'] }}
                    </span>
                </div>

                <ul class="divide-y divide-line-soft">
                    @foreach ($readiness['checks'] as $check)
                        <li>
                            <button type="button" @click="readinessCheck(@js($check))"
                                class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-shell/60">
                                <x-gpa.icon :name="$check['icon']" class="h-4 w-4 shrink-0 text-success-deep" />
                                <span class="min-w-0 flex-1">
                                    <span class="block font-sans text-sm font-semibold leading-5 text-ink">
                                        {{ $check['title'] }}
                                    </span>
                                    <span class="mt-0.5 block text-xs leading-5 text-ink-body">{{ $check['note'] }}</span>
                                </span>
                                <span @class([
                                    'shrink-0 rounded px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px]',
                                    $check['badge']['class'],
                                ])>{{ $check['badge']['label'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="border-t border-line-soft px-4 py-4">
                    <button type="button" @click="releaseDo()"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-ink px-4 text-accent shadow-sub outline outline-1 outline-accent/40 transition-colors hover:bg-brand-hover gpa-meta-lg font-bold tracking-[0.88px]">
                        {{ $readiness['cta'] }}
                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 shrink-0" />
                    </button>
                </div>
            </section>
        </div>
    </div>
@endsection