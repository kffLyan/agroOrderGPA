@extends('layouts.staff')

@section('title', 'Rekap Laporan')

@section('content')
    @php
        $totals = $journal['totals'];
    @endphp

    <div class="space-y-6" x-data="secretaryReports(@js($journal['rows']), @js($filters), @js($tabs), @js($journal['total_transactions']), @js($seal['hash']))">
        <section class="flex flex-col gap-5 border-b border-line-board pb-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                    <x-gpa.icon name="chart" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    {{ $heading['eyebrow'] }}
                </p>

                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $heading['title_before'] }}<br />
                    {{ $heading['title_after'] }}
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-5 text-ink-body">{{ $heading['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center xl:flex-col xl:items-stretch">
                <div class="flex flex-col gap-2 sm:flex-row">
                    @foreach (array_slice($heading['actions'], 0, 2) as $action)
                        <button type="button" @click="act(@js($action['key']))"
                            class="inline-flex items-center gap-2 rounded-xl bg-surface px-4 py-2.5 outline outline-1 outline-line-board transition-colors hover:bg-surface-shell gpa-meta-lg font-bold tracking-[0.88px] text-ink">
                            <x-gpa.icon :name="$action['icon']" class="h-4 w-4 shrink-0 text-success-deep" />
                            {{ $action['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach (array_slice($heading['actions'], 2) as $action)
                    <button type="button" @click="act(@js($action['key']))"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-4 py-2.5 outline outline-1 outline-accent-deep transition-colors hover:bg-brand-deep gpa-meta-lg font-bold tracking-[0.88px] text-accent">
                        <x-gpa.icon :name="$action['icon']" class="h-4 w-4 shrink-0" />
                        {{ $action['label'] }}
                    </button>
                @endforeach
            </div>
        </section>

        <section class="flex flex-col gap-4 rounded-xl bg-surface p-4 shadow-card outline outline-1 outline-line-board/50 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($filters as $filter)
                    <label class="flex min-w-0 flex-col gap-1.5">
                        <span class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $filter['label'] }}</span>
                        <select x-model="selected[@js($filter['key'])]"
                            class="h-10 w-full rounded-lg bg-surface px-3 outline outline-1 outline-line-board focus:outline-2 focus:outline-success-deep xl:w-[221px]">
                            @foreach ($filter['options'] as $option)
                                <option value="{{ $option }}" @selected($option === $filter['value'])>{{ $option }}</option>
                            @endforeach
                        </select>
                    </label>
                @endforeach
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="applyFilters()"
                    class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-accent-deep px-4 outline outline-1 outline-success-deep transition-colors hover:bg-accent gpa-micro-bold uppercase tracking-[1.08px] text-ink lg:flex-none">
                    <x-gpa.icon name="filter" class="h-[15px] w-[15px] shrink-0" />
                    Terapkan Filter
                </button>

                <button type="button" @click="resetFilters()"
                    class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-surface-shell px-4 outline outline-1 outline-line-board transition-colors hover:bg-surface-track gpa-micro-bold uppercase tracking-[1.08px] text-ink-body lg:flex-none">
                    <x-gpa.icon name="refresh" class="h-[15px] w-[15px] shrink-0" />
                    Reset
                </button>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                            {{ $metric['label'] }}
                        </h2>
                        <span @class(['shrink-0 rounded-lg p-1.5', $metric['icon_tile']])}>
                            <x-gpa.icon :name="$metric['icon']" class="h-[15px] w-[15px] shrink-0 {{ $metric['icon_class'] }}" />
                        </span>
                    </div>

                    <div>
                        <p class="flex flex-wrap items-center gap-2">
                            <span @class(['font-mono text-2xl font-bold tracking-[-0.01em]', $metric['value_class']])}>
                                {{ $metric['value'] }}
                            </span>
                            <span @class(['inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px]', $metric['chip']['class']])}>
                                {{ $metric['chip']['label'] }}
                            </span>
                        </p>

                        @if (! empty($metric['unit']))
                            <p class="mt-1 gpa-note text-ink-body">{{ $metric['unit'] }} &bull; {{ $metric['detail'] }}</p>
                        @else
                            <p @class(['mt-1', $metric['detail_class']])}>{{ $metric['detail'] }}</p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                        <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">{{ $metric['foot_left'] }}</p>
                        <p @class(['gpa-micro-bold uppercase tracking-[1.08px]', $metric['foot_class']])}>{{ $metric['foot_right'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="flex flex-col gap-4 rounded-2xl bg-surface-shell p-4 outline outline-1 outline-line-board/60 xl:flex-row">
            <div class="flex shrink-0 flex-col justify-between gap-4 rounded-xl bg-brand p-6 outline outline-1 outline-success-deep xl:w-[299px]">
                <div>
                    <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0" />
                        {{ $standards['eyebrow'] }}
                    </p>

                    <h2 class="mt-3 font-sans text-lg font-bold leading-6 text-white">{{ $standards['title'] }}</h2>

                    <p class="mt-2 text-xs leading-5 text-accent/80">{{ $standards['body'] }}</p>
                </div>

                <p class="border-t border-ink/40 pt-3 font-mono text-2xs font-semibold tracking-[0.88px] text-accent">
                    {{ $standards['footer'] }}
                </p>
            </div>

            <div class="grid flex-1 gap-3 lg:grid-cols-3">
                @foreach ($standards['rules'] as $rule)
                    <article class="flex flex-col justify-between gap-3 rounded-xl bg-surface p-4 shadow-card">
                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                    {{ $rule['chip'] }}
                                </span>
                                <span class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">{{ $rule['tag'] }}</span>
                            </div>

                            <h3 class="mt-3 font-sans text-base font-bold leading-5 text-ink">{{ $rule['title'] }}</h3>

                            <p class="mt-1.5 gpa-note leading-5 text-ink-body">{{ $rule['body'] }}</p>
                        </div>

                        <p class="flex items-start gap-2 border-t border-line-soft pt-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-success-deep">
                            <x-gpa.icon name="check-circle" class="mt-px h-3.5 w-3.5 shrink-0" />
                            {{ $rule['note'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap gap-1 border-b border-line-board">
            @foreach ($tabs as $index => $tab)
                <button type="button" @click="setTab(@js($tab['key']))"
                    :class="tab === @js($tab['key'])
                        ? 'bg-surface text-ink border-b-2 border-brand'
                        : 'text-ink-quiet border-b-2 border-transparent hover:text-ink-body'"
                    class="-mb-px inline-flex items-center gap-2 rounded-t-lg px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                    <x-gpa.icon :name="$tab['icon']" class="h-4 w-4 shrink-0" />
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        <section class="flex flex-col overflow-hidden rounded-2xl bg-surface shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 bg-surface-shell px-4 py-2.5">
                <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink">
                    <x-gpa.icon name="receipt" class="h-[15px] w-[15px] shrink-0" />
                    {{ $journal['title'] }}
                </p>

                <p class="flex flex-wrap items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                    <span x-text="'Menampilkan ' + visibleRows().length + ' dari ' + {{ $journal['total_transactions'] }} + ' Transaksi'">
                        {{ $journal['meta_left'] }}
                    </span>
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="text-success-deep">{{ $journal['meta_right'] }}</span>
                </p>
            </div>

            <div style="display:none" x-show="activeTab">
                <div class="gpa-scroll-x">
                    <table class="w-full min-w-[1320px] border-collapse text-left">
                        <thead class="bg-brand text-accent">
                            <tr>
                                @foreach ($journal['columns'] as $column)
                                    <th scope="col"
                                        class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] {{ $loop->first || $loop->iteration >= 5 ? 'text-right' : '' }}">
                                        {{ $column }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-line-soft">
                            @foreach ($journal['rows'] as $row)
                                <tr x-show="isVisible(@js($row))" @class(['transition-colors hover:bg-surface-shell/60', $loop->even ? 'bg-canvas' : ''])>
                                    <td class="px-3 py-4 text-right font-mono text-2xs font-bold tracking-[0.88px] text-ink-quiet">
                                        {{ $row['no'] }}
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="block font-mono text-2xs font-bold tracking-[0.88px] text-ink">{{ $row['invoice'] }}</span>
                                        <span class="mt-0.5 block font-mono text-2xs tracking-[0.88px] text-ink-quiet">{{ $row['sj'] }}</span>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="block font-sans text-sm font-bold leading-5 text-ink">{{ $row['client'] }}</span>
                                        <span class="mt-0.5 flex items-center gap-1.5 font-mono text-2xs tracking-[0.88px] text-ink-quiet">
                                            <x-gpa.icon name="map-pin" class="h-3 w-3 shrink-0" />
                                            {{ $row['location'] }}
                                        </span>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="flex items-center gap-2 font-sans text-xs font-semibold leading-5 text-ink-body">
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                                            {{ $row['commodity'] }}
                                        </span>
                                    </td>

                                    <td class="px-3 py-4 text-right font-mono text-2xs tracking-[0.88px] text-ink-body"
                                        x-text="kgInt({{ $row['estimate'] }}) + ' kg'">{{ $row['estimate_label'] }}</td>

                                    <td class="px-3 py-4 text-right">
                                        <span class="rounded bg-surface-shell px-2 py-1 font-mono text-2xs font-bold tracking-[0.88px] text-ink"
                                            x-text="kg({{ $row['net'] }}) + ' kg'">{{ $row['net_label'] }}</span>
                                    </td>

                                    <td class="px-3 py-4 text-right">
                                        <span class="block font-mono text-2xs font-bold tracking-[0.88px] {{ $row['deviation_class'] }}"
                                            :class="deviationClass({{ $row['estimate'] }}, {{ $row['net'] }})"
                                            x-text="deviation({{ $row['estimate'] }}, {{ $row['net'] }}) + '%'">{{ $row['deviation_label'] }}%</span>
                                        <span style="display:none" x-show="isBlocked(@js($row))"
                                            class="mt-1 inline-flex rounded bg-warning-soft px-1.5 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-warning-deep">
                                            Blokir Mediasi
                                        </span>
                                    </td>

                                    <td class="px-3 py-4 text-right font-mono text-xs font-bold tracking-[0.88px] text-ink"
                                        x-text="rupiah({{ $row['value'] }})">{{ $row['value_label'] }}</td>

                                    <td class="px-3 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                            $row['status_class'],
                                        ])>{{ $row['status_label'] }}</span>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="flex items-center justify-end gap-1.5">
                                            @foreach (['PO Reg', 'Surat Jalan', 'Tiket Timbangan'] as $position => $file)
                                                @if ($position < $row['audit_files'])
                                                    <button type="button" @click="openAudit(@js($row), @js($file))"
                                                        class="inline-flex items-center gap-1 rounded bg-accent/50 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-success/40">
                                                        <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                        {{ $file }}
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded bg-surface-pill px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                                                        <x-gpa.icon name="x" class="h-3 w-3 shrink-0" />
                                                        Menunggu
                                                    </span>
                                                @endif
                                            @endforeach
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t-2 border-brand bg-surface-shell px-4 py-3">
                    <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body"
                        x-text="'{{ $journal['total_label'] }} (' + visibleRows().length + ' Transaksi Terpilih):'">
                        {{ $journal['total_label'] }} ({{ $totals['count'] }} Transaksi Terpilih):
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <p class="font-mono text-2xs tracking-[0.88px] text-ink-body"
                            x-text="kgInt(totalEstimate) + ' kg'">{{ number_format($totals['estimate'], 0, ',', '.') }} kg</p>
                        <p class="font-mono text-2xs font-bold tracking-[0.88px] text-ink"
                            x-text="kg(totalNet) + ' kg'">{{ number_format($totals['net'], 1, ',', '.') }} kg</p>
                        <p class="font-mono text-2xs font-bold tracking-[0.88px] text-warning-caution"
                            x-text="totalDeviation + '%'">{{ number_format($totals['deviation'], 2, '.', '') }}%</p>
                        <p class="font-mono text-sm font-bold tracking-[0.88px] text-brand"
                            x-text="rupiah(totalValue)">{{ 'Rp '.number_format($totals['value'], 0, ',', '.') }}</p>
                        <span class="inline-flex items-center gap-1.5 rounded bg-brand px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                            <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                            {{ $journal['total_lock'] }}
                        </span>
                    </div>
                </div>
            </div>

            <div style="display:none" x-show="! activeTab"
                class="flex flex-col items-center gap-2 px-6 py-12 text-center">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-surface-track">
                    <x-gpa.icon name="chart" class="h-5 w-5 shrink-0 text-ink-quiet" />
                </span>
                <p class="font-sans text-base font-bold leading-6 text-ink" x-text="activeTabLabel">{{ $tabs[0]['label'] }}</p>
                <p class="max-w-md text-xs leading-5 text-ink-body">
                    Ringkasan tab ini belum dimuat pada MVP. Jurnal buku transaksi riil dan subtotal timbangan tampil pada Tab 1.
                </p>
            </div>
        </section>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-track">
                            <x-gpa.icon name="scale" class="h-[18px] w-[18px] shrink-0 text-ink" />
                        </span>

                        <h2 class="font-sans text-lg font-bold leading-6 text-ink">{{ $deviation['title'] }}</h2>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                        <x-gpa.icon name="badge-check" class="h-[15px] w-[15px] shrink-0" />
                        {{ $deviation['badge'] }}
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ($deviation['stats'] as $stat)
                        <div class="rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-board/40">
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $stat['label'] }}</p>
                            <p @class(['mt-1.5 font-mono text-xl font-bold tracking-[-0.01em]', $stat['value_class']])}>
                                {{ $stat['value'] }}
                            </p>
                            <p class="mt-1 gpa-note text-ink-body">{{ $stat['note'] }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="gpa-note leading-5 text-ink-body">
                    @foreach ($deviation['body'] as $segment)
                        @if ($segment['type'] === 'mono')
                            <span class="font-mono font-bold text-ink">{{ $segment['value'] }}</span>
                        @else
                            {{ $segment['value'] }}
                        @endif
                    @endforeach
                </p>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $deviation['foot_left'] }}</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">{{ $deviation['foot_right'] }}</p>
                </div>
            </section>

            <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        <h2 class="font-sans text-lg font-bold leading-6 text-ink">{{ $validation['title'] }}</h2>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                        <x-gpa.icon name="check-circle" class="h-[15px] w-[15px] shrink-0" />
                        {{ $validation['badge'] }}
                    </span>
                </div>

                <ul class="flex flex-col gap-2">
                    @foreach ($validation['rows'] as $item)
                        <li class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/30">
                            <span class="flex min-w-0 items-center gap-2.5 gpa-note leading-5 text-ink-body">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                                {{ $item['label'] }}
                            </span>
                            <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                {{ $item['status'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $validation['foot_left'] }}</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">{{ $validation['foot_right'] }}</p>
                </div>
            </section>
        </div>

        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card xl:flex-row xl:items-center xl:justify-between">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand outline outline-1 outline-success-deep">
                    <x-gpa.icon name="lock" class="h-5 w-5 shrink-0 text-accent" />
                </span>

                <div class="min-w-0">
                    <p class="flex flex-wrap items-center gap-2 font-mono text-2xs font-bold tracking-[0.88px] text-ink">
                        {{ $seal['label'] }}
                        <span class="rounded bg-surface-shell px-2 py-1 text-success-deep outline outline-1 outline-line-board">{{ $seal['hash'] }}</span>
                    </p>

                    <p class="mt-1.5 max-w-2xl gpa-note leading-5 text-ink-body">{{ $seal['body'] }}</p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                @foreach ($seal['actions'] as $action)
                    <button type="button" @click="act(@js($action['key']))"
                        @class([
                            'inline-flex items-center gap-2 rounded-xl px-4 py-2.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors',
                            'bg-brand text-accent outline outline-1 outline-success-deep hover:bg-brand-deep' => $action['variant'] === 'brand',
                            'bg-surface-shell text-ink-body outline outline-1 outline-line-board hover:bg-surface-track' => $action['variant'] === 'ghost',
                        ])>
                        <x-gpa.icon :name="$action['icon']" class="h-4 w-4 shrink-0" />
                        {{ $action['label'] }}
                    </button>
                @endforeach
            </div>
        </section>
    </div>
@endsection