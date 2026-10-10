@extends('layouts.coordinator')

@section('title', 'Laporan Retur')

@section('content')
    @php
        $batchColumns = [
            ['label' => 'No', 'alignment' => 'text-center'],
            ['label' => 'Kode Batch'],
            ['label' => 'Tanggal / Jam'],
            ['label' => 'Poktan / Petani Binaan'],
            ['label' => 'Komoditas'],
            ['label' => 'Bruto Tebas (kg)', 'alignment' => 'text-right'],
            ['label' => 'Netto Timbang (kg)', 'alignment' => 'text-right'],
            ['label' => 'Susut (kg / %)', 'alignment' => 'text-right'],
            ['label' => 'Status Tera', 'alignment' => 'text-center'],
            ['label' => 'Petugas QC / Timbang'],
        ];

        $mutationColumns = [
            ['label' => 'No', 'alignment' => 'text-center'],
            ['label' => 'ID Referensi'],
            ['label' => 'Sumber / Klien'],
            ['label' => 'Komoditas'],
            ['label' => 'Masuk / Keluar (kg)', 'alignment' => 'text-right'],
            ['label' => 'Retur / Selisih (kg)', 'alignment' => 'text-right'],
            ['label' => 'Keterangan / Tindakan Disposisi'],
            ['label' => 'Status', 'alignment' => 'text-center'],
        ];
    @endphp

    <div class="coordinator-returns-page space-y-6"
        x-data="coordinatorReturns(
            @js($report['batch_rows']),
            @js($report['mutation_rows']),
            @js($report['filters'])
        )">
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $report['header']['title'] }}
                </h1>
            </div>
            <div class="no-print flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="exportCsv()"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-3 gpa-meta-lg font-bold text-ink outline outline-1 outline-line-board shadow-sub transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                    Ekspor CSV
                </button>
                <a href="{{ route('prints.return-act') }}"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-3 gpa-meta-lg font-bold text-ink outline outline-1 outline-line-board shadow-sub transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="file-text" class="h-3.5 w-3.5" />
                    Contoh BAP Retur
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-ink px-3 gpa-meta-lg font-bold text-white shadow-sub outline outline-1 outline-brand-line transition-colors hover:bg-ink-muted">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    Cetak Laporan
                </button>
            </div>

            <div class="no-print flex w-full flex-wrap items-end gap-3 border-t border-line-board/40 pt-4">
                <label class="flex flex-col gap-1">
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-body">Periode</span>
                    <select x-model="draftPeriod"
                        class="h-9 rounded-lg border border-line-board bg-surface px-3 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-accent/40">
                        @foreach ($report['filters']['periods'] as $period)
                            <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="flex flex-col gap-1">
                    <span class="gpa-micro-bold uppercase tracking-wider text-ink-body">Sentra Hub</span>
                    <select x-model="draftHub"
                        class="h-9 rounded-lg border border-line-board bg-surface px-3 text-xs text-ink focus:border-brand focus:outline-none focus:ring-2 focus:ring-accent/40">
                        @foreach ($report['filters']['hubs'] as $hub)
                            <option value="{{ $hub['value'] }}">{{ $hub['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <button type="button" @click="applyFilters()"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-brand px-4 gpa-meta-lg font-semibold text-white transition-colors hover:bg-brand-hover">
                    <x-gpa.icon name="filter" class="h-3.5 w-3.5" />
                    Terapkan
                </button>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="gpa-panel flex flex-col justify-between gap-3 p-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase tracking-wider text-ink-body">Total Panen Diterima</h2>
                    <x-gpa.icon name="package" class="h-4 w-4 text-ink-body" />
                </div>
                <p class="font-inter text-2xl font-bold tracking-tight text-ink">
                    <span x-text="numberFormat(summary().gross)">42.850</span>
                    <span class="gpa-meta font-medium text-ink-body">kg</span>
                </p>
                <footer class="flex justify-between gap-2 border-t border-line-soft pt-2 gpa-micro text-ink-body">
                    <span>Bruto hasil panen</span>
                    <span class="font-semibold text-ink">Tercatat</span>
                </footer>
            </article>

            <article class="gpa-panel flex flex-col justify-between gap-3 p-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase tracking-wider text-ink-body">Netto Timbangan Sah</h2>
                    <x-gpa.icon name="scale" class="h-4 w-4 text-success-deep" />
                </div>
                <p class="font-inter text-2xl font-bold tracking-tight text-ink">
                    <span x-text="numberFormat(summary().net)">42.380</span>
                    <span class="gpa-meta font-medium text-ink-body">kg</span>
                </p>
                <footer class="flex justify-between gap-2 border-t border-line-soft pt-2 gpa-micro text-ink-body">
                    <span>Tera digital</span>
                    <span class="font-semibold text-success-deep">100% Valid</span>
                </footer>
            </article>

            <article class="gpa-panel flex flex-col justify-between gap-3 p-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase tracking-wider text-ink-body">Deviasi / Susut</h2>
                    <x-gpa.icon name="chart" class="h-4 w-4 text-ink-body" />
                </div>
                <p class="font-inter text-2xl font-bold tracking-tight text-ink">
                    <span x-text="'-' + numberFormat(summary().lossPercent, 2)">-1,09</span>%
                    <span class="gpa-meta font-medium text-ink-body">(<span x-text="'-' + numberFormat(summary().loss)">-470</span> kg)</span>
                </p>
                <footer class="flex justify-between gap-2 border-t border-line-soft pt-2 gpa-micro text-ink-body">
                    <span>Toleransi &lt; 2.0%</span>
                    <span class="rounded border border-line-board px-1.5 py-0.5 font-semibold uppercase text-success-deep">Toleransi Aman</span>
                </footer>
            </article>

            <article class="gpa-panel flex flex-col justify-between gap-3 p-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase tracking-wider text-ink-body">Total Retur Lapangan</h2>
                    <x-gpa.icon name="alert-triangle" class="h-4 w-4 text-warning-deep" />
                </div>
                <p class="font-inter text-2xl font-bold tracking-tight text-warning-deep">
                    <span x-text="numberFormat(summary().loss)">470</span>
                    <span class="gpa-meta font-medium text-ink-body">kg</span>
                </p>
                <footer class="flex justify-between gap-2 border-t border-line-soft pt-2 gpa-micro text-ink-body">
                    <span><span x-text="summary().mutationCount">4</span> Catatan Mutasi</span>
                    <span class="rounded border border-warning/50 bg-warning-soft/40 px-1.5 py-0.5 font-semibold uppercase text-warning-deep">Terlampir</span>
                </footer>
            </article>
        </section>

        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-board/40 px-4 py-3">
                <h2 class="gpa-meta-lg font-semibold uppercase text-ink">
                    Rekapitulasi Pasokan Panen &amp; Timbangan Netto
                </h2>
                <p class="gpa-note text-ink-body">Periode Timbang Shift Pagi: 06:00–11:00 WIB</p>
            </div>
            <div class="report-table-wrap gpa-scroll-x">
                <table class="w-full min-w-[1120px] border-collapse text-left">
                    <caption class="sr-only">Rekap pasokan panen dan timbangan netto</caption>
                    <thead class="border-b-2 border-brand bg-surface-shell">
                        <tr>
                            @foreach ($batchColumns as $column)
                                <th scope="col"
                                    @class([
                                        'whitespace-nowrap px-3 py-2 gpa-micro-bold uppercase tracking-wider text-ink-strong',
                                        $column['alignment'] ?? '',
                                    ])>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        @foreach ($report['batch_rows'] as $index => $row)
                            <tr
                                x-show="matchesRow(@js($row))"
                                @class([
                                    'transition-colors hover:bg-surface-shell',
                                    'bg-surface-shell/40' => $index % 2 === 1,
                                ])>
                                <td class="px-3 py-2 text-center gpa-micro text-ink-quiet">
                                    {{ sprintf('%02d', $index + 1) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 font-mono text-[11px] font-bold text-ink">
                                    {{ $row['code'] }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 font-mono text-[10px] text-ink-body">
                                    {{ $row['datetime'] }}
                                </td>
                                <td class="px-3 py-2">
                                    <p class="whitespace-nowrap text-xs font-semibold text-ink">{{ $row['farmer'] }}</p>
                                    <p class="whitespace-nowrap gpa-micro text-ink-quiet">{{ $row['farmer_note'] }}</p>
                                </td>
                                <td class="px-3 py-2">
                                    <p class="whitespace-nowrap text-xs font-medium text-ink">{{ $row['commodity'] }}</p>
                                    <p class="whitespace-nowrap gpa-micro text-ink-quiet">{{ $row['commodity_note'] }}</p>
                                </td>
                                <td class="px-3 py-2 text-right font-mono text-xs tabular-nums text-ink-body">
                                    {{ number_format($row['gross'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right font-mono text-xs font-bold tabular-nums text-ink">
                                    {{ number_format($row['net'], 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-right font-mono text-xs tabular-nums text-ink-body">
                                    -{{ number_format($row['loss'], 0, ',', '.') }} (-{{ number_format($row['loss_percent'], 2, ',', '.') }}%)
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span @class([
                                        'inline-flex whitespace-nowrap px-1.5 py-0.5 gpa-micro-bold',
                                        'bg-brand text-white' => $row['status_tone'] === 'valid',
                                        'border border-warning/50 bg-warning-soft/40 text-warning-deep' => $row['status_tone'] === 'return',
                                    ])>
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2">
                                    <p class="text-xs text-ink-body">{{ $row['officer'] }}</p>
                                    <p class="gpa-micro text-ink-quiet">{{ $row['officer_code'] }}</p>
                                </td>
                            </tr>
                        @endforeach
                        <tr x-show="visibleBatchRows().length === 0">
                            <td colspan="10" class="px-3 py-8 text-center gpa-note text-ink-muted">Tidak ada data timbang pada filter ini.</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t-2 border-brand bg-surface-shell">
                        <tr>
                            <th colspan="5" class="px-3 py-2 text-left gpa-micro-bold uppercase text-ink-strong">
                                Total Akumulasi Periode
                            </th>
                            <td
                                class="px-3 py-2 text-right font-mono text-xs font-bold tabular-nums text-ink-strong"
                                x-text="numberFormat(summary().gross)">42.850</td>
                            <td
                                class="px-3 py-2 text-right font-mono text-xs font-bold tabular-nums text-ink-strong"
                                x-text="numberFormat(summary().net)">42.380</td>
                            <td class="whitespace-nowrap px-3 py-2 text-right font-mono text-xs font-bold tabular-nums text-ink-strong">
                                <span x-text="'-' + numberFormat(summary().loss)">-470</span>
                                (<span x-text="'-' + numberFormat(summary().lossPercent, 2)">-1,09</span>%)
                            </td>
                            <td colspan="2" class="px-3 py-2 text-center gpa-micro text-ink-body"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-board/40 px-4 py-3">
                <h2 class="gpa-meta-lg font-semibold uppercase text-ink">
                    Rekapitulasi Mutasi Buffer Stock &amp; Retur Lapangan
                </h2>
                <p class="gpa-note text-ink-body"><span x-text="visibleMutationRows().length">4</span> catatan mutasi</p>
            </div>
            <div class="report-table-wrap gpa-scroll-x">
                <table class="w-full min-w-[980px] border-collapse text-left">
                    <caption class="sr-only">Rekap mutasi buffer stock dan retur lapangan</caption>
                    <thead class="border-b-2 border-brand bg-surface-shell">
                        <tr>
                            @foreach ($mutationColumns as $column)
                                <th scope="col"
                                    @class([
                                        'whitespace-nowrap px-3 py-2 gpa-micro-bold uppercase tracking-wider text-ink-strong',
                                        $column['alignment'] ?? '',
                                    ])>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-soft">
                        @foreach ($report['mutation_rows'] as $index => $row)
                            <tr
                                x-show="matchesRow(@js($row))"
                                @class([
                                    'transition-colors hover:bg-surface-shell',
                                    'bg-surface-shell/40' => $index % 2 === 1,
                                ])>
                                <td class="px-3 py-2 text-center gpa-micro text-ink-quiet">
                                    {{ sprintf('%02d', $index + 1) }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 font-mono text-[11px] font-bold text-ink">
                                    {{ $row['id'] }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-xs font-medium text-ink">
                                    {{ $row['source'] }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-xs text-ink-body">
                                    {{ $row['commodity'] }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2 text-right font-mono text-xs tabular-nums text-ink-body">
                                    {{ $row['movement'] }}
                                </td>
                                <td @class([
                                    'whitespace-nowrap px-3 py-2 text-right font-mono text-xs tabular-nums',
                                    'font-bold text-warning-deep' => $row['return'] < 0,
                                    'text-ink-body' => $row['return'] === 0,
                                ])>
                                    @if ($row['return'] < 0)
                                        -{{ number_format(abs($row['return']), 0, ',', '.') }} kg
                                    @else
                                        0 kg
                                    @endif
                                </td>
                                <td class="min-w-[260px] px-3 py-2 text-[11px] leading-4 text-ink-body">
                                    {{ $row['description'] }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <span @class([
                                        'inline-flex whitespace-nowrap px-1.5 py-0.5 gpa-micro-bold',
                                        'bg-brand text-white' => $row['status_tone'] === 'valid',
                                        'border border-warning/50 bg-warning-soft/40 text-warning-deep' => $row['status_tone'] === 'return',
                                        'border border-line-board bg-surface-shell text-ink-body' => $row['status_tone'] === 'muted',
                                    ])>
                                        {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        <tr x-show="visibleMutationRows().length === 0">
                            <td colspan="8" class="px-3 py-8 text-center gpa-note text-ink-muted">
                                Tidak ada catatan mutasi pada filter ini.
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t-2 border-brand bg-surface-shell">
                        <tr>
                            <th colspan="4"
                                class="px-3 py-2 text-left gpa-micro-bold uppercase text-ink-strong">
                                Total Mutasi Tercatat
                            </th>
                            <td
                                class="px-3 py-2 text-right font-mono text-xs font-bold tabular-nums text-ink-strong"
                                x-text="numberFormat(summary().movement) + ' kg'">57.290 kg</td>
                            <td
                                class="px-3 py-2 text-right font-mono text-xs font-bold tabular-nums text-warning-deep"
                                x-text="numberFormat(summary().returned) + ' kg'">-190 kg</td>
                            <td colspan="2" class="px-3 py-2 text-center gpa-micro text-ink-body"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
@endsection
