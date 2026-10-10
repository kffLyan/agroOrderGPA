@extends('layouts.staff')

@section('title', 'Stok')

@section('content')
    <div class="space-y-6"
        x-data="secretaryInventory(@js($ledger['rows']), @js(['atp' => $ledger['atp_total'], 'criticalThreshold' => $ledger['critical_threshold']]))">
        <section class="border-b border-line-soft pb-6">
            <h1 class="font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                {{ $heading['title'] }}
            </h1>
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
                        'bg-ink' => $action['tone'] !== 'primary' && !$loop->first,
                    ]) aria-hidden="true"></span>
                    {{ $action['label'] }}
                </button>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article @class([
                    'relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-xl bg-surface p-4 shadow-card',
                    'outline outline-1 outline-accent/30' => !empty($metric['glow']),
                ])>
                    @if (!empty($metric['glow']))
                        <span class="pointer-events-none absolute -right-4 -top-4 h-12 w-12 rounded-full bg-accent/30"
                            aria-hidden="true"></span>
                    @endif

                    <div class="flex items-start gap-2">
                        <x-gpa.icon :name="$metric['icon']" class="h-4 w-4 shrink-0 {{ $metric['icon_class'] }}" />
                        <h3 @class([
                            'gpa-micro uppercase tracking-[0.55px]',
                            'font-bold' => !empty($metric['label_class']),
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
                        <p
                            class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] {{ $metric['value_class'] ?? 'text-ink' }}">
                            {{ $metric['value'] }}
                            <span
                                class="ml-1 font-mono text-sm font-normal {{ $metric['unit_class'] }}">{{ $metric['unit'] }}</span>
                        </p>
                    @endif

                    @if (!empty($metric['badge']))
                        <span @class([
                            'mt-2 inline-flex w-fit items-center gap-1 rounded px-2 py-0.5 gpa-micro-bold tracking-[1.08px]',
                            $metric['badge']['class'],
                        ])>{{ $metric['badge']['label'] }}</span>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-1 border-t border-line-soft pt-2">
                        @if (!empty($metric['foot']))
                            @foreach ($metric['foot'] as $foot)
                                <p @class(['gpa-note', $foot['class'] ?? ''])>
                                    @if (!empty($foot['label']) && !empty($foot['value']))
                                        <span class="text-ink-body">{{ $foot['label'] }}</span>
                                        <span class="ml-1 font-bold {{ $foot['value_class'] }}">{{ $foot['value'] }}</span>
                                    @else
                                        <span @class(['font-bold', $foot['class'] ?? ''])>{{ $foot['label'] }}</span>
                                    @endif
                                </p>
                            @endforeach
                        @endif

                        @if (!empty($metric['foot_label']))
                            <p class="gpa-note text-ink-body">{{ $metric['foot_label'] }}</p>
                        @endif

                        @if (!empty($metric['foot_chip']))
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

                <label
                    class="flex h-10 cursor-pointer items-center gap-2 self-end rounded-lg px-1 text-xs font-semibold text-ink"
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
            <div
                class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 bg-surface-shell px-4 py-3">
                <div class="flex min-w-0 flex-wrap items-center gap-2">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        {{ $ledger['title'] }}
                    </h2>
                </div>
                <p class="gpa-micro shrink-0 uppercase tracking-[1.08px] text-ink-quiet">{{ $ledger['mutasi'] }}</p>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[1180px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-shell/70">
                            <th scope="col" rowspan="2"
                                class="px-4 py-3 align-bottom gpa-micro-bold uppercase tracking-[0.88px] text-ink">
                                {{ $ledger['columns']['sku'] }}
                            </th>
                            <th scope="col" colspan="3"
                                class="border-l border-line-soft px-4 py-3 text-center gpa-micro-bold uppercase tracking-[0.88px] text-ink-quiet">
                                {{ $ledger['groups']['sources'] }}
                            </th>
                            <th scope="col" colspan="2"
                                class="border-l border-line-soft px-4 py-3 text-center gpa-micro-bold uppercase tracking-[0.88px] text-success-deep">
                                {{ $ledger['groups']['allocation'] }}
                            </th>
                            <th scope="col" rowspan="2"
                                class="border-l border-line-soft px-4 py-3 align-bottom gpa-micro-bold uppercase tracking-[0.88px] text-ink">
                                {{ $ledger['columns']['actions'] }}
                            </th>
                        </tr>
                        <tr class="border-b border-line-board bg-surface-pill/40">
                            <th scope="col"
                                class="px-4 py-2 text-right gpa-micro-bold uppercase tracking-[0.88px] text-ink-body">
                                {{ $ledger['columns']['binawan'] }}
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-right gpa-micro-bold uppercase tracking-[0.88px] text-ink-body">
                                {{ $ledger['columns']['buffer'] }}
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-right gpa-micro-bold uppercase tracking-[0.88px] text-ink">
                                {{ $ledger['columns']['total'] }}
                            </th>
                            <th scope="col"
                                class="border-l border-line-soft px-4 py-2 text-right gpa-micro-bold uppercase tracking-[0.88px] text-ink-body">
                                {{ $ledger['columns']['locked'] }}
                            </th>
                            <th scope="col"
                                class="px-4 py-2 text-right gpa-micro-bold uppercase tracking-[0.88px] text-success-deep">
                                {{ $ledger['columns']['atp'] }}
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-faint">
                        @foreach ($ledger['rows'] as $row)
                            <tr x-show="matchesFilter(@js($row))" class="align-top transition-colors hover:bg-surface-shell/60">
                                <td class="px-4 py-4">
                                    <p class="font-sans text-sm font-bold leading-5 text-ink">{{ $row['name'] }}</p>
                                    <p class="mt-0.5 font-mono text-[10px] font-medium tracking-[0.6px] text-ink-quiet">
                                        {{ $row['sku'] }}
                                    </p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['grade'] }}</p>
                                </td>

                                <td class="border-l border-line-soft px-4 py-4 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $row['binawan_value'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-quiet">{{ $row['binawan_share'] }}</p>
                                    <div class="ml-auto mt-1.5 h-1.5 w-full max-w-[9rem] overflow-hidden rounded-full bg-surface-pill">
                                        <div class="h-full rounded-full bg-success-deep"
                                            :style="'width: ' + {{ $row['binawan_percent'] }} + '%'"></div>
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] {{ $row['buffer_class'] }}">
                                        {{ $row['buffer_value'] }}
                                    </p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['buffer_partner'] }}</p>
                                    @if (!empty($row['buffer_badge']))
                                        <span @class([
                                            'mt-1 inline-flex rounded px-1.5 py-0.5 gpa-micro-bold',
                                            $row['buffer_badge']['class'],
                                        ])>{{ $row['buffer_badge']['label'] }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <p class="font-sans text-base font-extrabold leading-6 tracking-[-0.01em] text-ink">
                                        {{ $row['total_value'] }}
                                    </p>
                                    <p class="mt-0.5 gpa-note text-ink-quiet">Gudang + Kebun</p>
                                </td>

                                <td class="border-l border-line-soft px-4 py-4 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-danger">{{ $row['locked_value'] }}</p>
                                    <span
                                        class="mt-1 inline-flex rounded bg-danger-soft/10 px-1.5 py-0.5 gpa-micro-bold text-danger-ink">
                                        {{ $row['locked_note'] }}
                                    </span>
                                </td>

                                <td class="bg-accent/10 px-4 py-4 text-right">
                                    <p
                                        class="font-sans text-base font-extrabold leading-6 tracking-[-0.01em] {{ $row['atp_class'] }}">
                                        {{ $row['atp_value'] }}
                                    </p>
                                    <span class="mt-1 inline-flex rounded bg-surface px-1.5 py-0.5 gpa-micro-bold {{ $row['atp_class'] }}">
                                        {{ $row['atp_note'] }}
                                    </span>
                                </td>

                                <td class="border-l border-line-soft px-4 py-4">
                                    <div class="flex flex-col items-start gap-2.5">
                                        <span @class([
                                            'inline-flex items-center gap-1 rounded px-2 py-0.5 gpa-micro-bold',
                                            $row['status']['class'],
                                        ])>
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-70"
                                                aria-hidden="true"></span>
                                            {{ $row['status']['label'] }}
                                        </span>

                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <button type="button" @click="detail(@js($row))"
                                                class="inline-flex h-7 items-center gap-1.5 rounded border border-line-soft bg-surface-pill px-2.5 gpa-micro-bold uppercase tracking-[0.88px] text-ink-body transition-colors hover:border-line-board hover:bg-surface-disabled hover:text-ink">
                                                <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                                Detail
                                            </button>
                                            <button type="button" @click="rowAction(@js($row))" @class([
                                                'inline-flex h-7 items-center gap-1.5 rounded px-2.5 gpa-micro-bold uppercase tracking-[0.88px] transition-colors',
                                                $row['action']['class'],
                                            ])>
                                                <x-gpa.icon :name="$row['action']['icon'] ?? 'bolt'" class="h-3 w-3 shrink-0" />
                                                {{ $row['action']['label'] }}
                                            </button>
                                        </div>
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

            <div
                class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 bg-surface-shell px-4 py-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + visibleCount() + ' dari ' + {{ count($ledger['rows']) }} + ' komoditas operasional aktif'">
                </p>
                <p class="gpa-note text-ink-body">
                    Total Kuota Bebas (ATP): <span x-text="atpFooter()"></span>
                </p>
            </div>
        </section>

        <div class="grid items-start gap-4 xl:grid-cols-2">
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
