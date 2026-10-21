@extends('layouts.direktur')

@section('title', 'Penjualan // Analisis Revenue Eksekutif')

@section('content')
    @php
        $scopesData = $scopesData ?? [];
        $trendRows = $trendRows ?? [];
        $channelRows = $channelRows ?? [];
        $settlementRows = $settlementRows ?? [];
        $totalChVal = array_sum(array_column($channelRows, 'value'));
        $settlementTotal = array_sum(array_column($settlementRows, 'value'));
        $bep = 65000000;
        $ceiling = !empty($trendRows) ? max(array_column($trendRows, 'value')) : 120000000;
        $ceiling = $ceiling > 0 ? $ceiling * 1.2 : 120000000;
        $peakRow = !empty($trendRows) ? ($trendRows[array_search(max(array_column($trendRows, 'value')), array_column($trendRows, 'value'))] ?? null) : null;
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorSales(['MTD', 'WTD', 'YTD'], @js($trendRows), @js($portfolioRows ?? []))">

        {{-- Page header + scope pita KPI --}}
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <p class="gpa-meta text-xs font-bold uppercase tracking-wider text-accent-deep">
                    Monitoring Penjualan &amp; Analisis Revenue Eksekutif
                </p>
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Analisis Revenue Eksekutif
                </h1>
            </div>

            <div class="flex shrink-0 flex-col items-stretch gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface-track px-3 py-2 outline outline-1 -outline-offset-1 outline-line-board">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        @foreach ($scopesData as $key => $scope)
                            <span @if ($key !== 'MTD') x-cloak @endif
                                x-show="activeScopeKey() === '{{ $key }}'"
                                class="gpa-meta font-bold text-ink">{{ $scope['period'] }}</span>
                        @endforeach
                    </span>

                    @foreach ($scopesData as $key => $scope)
                        <button type="button" @click="setScope('{{ $key }}')"
                            :class="scopeClass('{{ $key }}')"
                            class="rounded-lg px-3 py-2 gpa-note font-semibold">{{ $scope['tab'] }}</button>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('direktur.sales.export') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface px-4 py-2.5 outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-surface-shell">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                        <span class="text-xs font-semibold leading-4 text-ink font-inter">Unduh Data Penjualan (CSV / Excel)</span>
                    </a>
                    <a href="{{ route('direktur.reports.download') }}" target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink-strong px-4 py-2.5 text-white outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-brand-hover">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                        <span class="text-xs font-semibold leading-4 text-white font-inter">Cetak Laporan Penjualan (PDF)</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- KPI band per cakupan periode --}}
        <section class="flex flex-col gap-3">
            @foreach ($scopesData as $key => $sc)
                @php
                    $targetPct = round(($sc['revenue'] / ($sc['target'] ?: 1)) * 100, 1);
                    $paidPct = round(($sc['revenue'] / ($sc['billing'] ?: 1)) * 100, 1);
                    $sisa = max(0, $sc['target'] - $sc['revenue']);
                @endphp
                <div @if ($key !== 'MTD') x-cloak @endif
                    x-show="activeScopeKey() === '{{ $key }}'" class="flex flex-col gap-3">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        {{-- Card 1: Revenue --}}
                        <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                            <header class="flex items-start justify-between gap-2">
                                <h2 class="gpa-note font-semibold text-success-deep">{{ $sc['label'] }}</h2>
                                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                                    {{ $sc['badge'] }}
                                </span>
                            </header>

                            <p class="font-inter text-2xl font-bold leading-8 text-ink">
                                Rp {{ number_format($sc['revenue'], 0, ',', '.') }}
                            </p>

                            <p class="flex flex-wrap items-center gap-1.5">
                                <span class="gpa-meta text-success-deep font-medium">Target: Rp {{ number_format($sc['target'], 0, ',', '.') }}</span>
                                <span class="gpa-meta text-ink font-bold">{{ $targetPct }}%</span>
                            </p>

                            <div class="flex flex-col gap-2">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full bg-ink-strong"
                                        style="width: {{ max(2, min(100, $targetPct)) }}%"></div>
                                </div>

                                <p class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="gpa-note text-warning-caution font-semibold">Defisit: Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                                    <span class="gpa-note text-success-deep font-medium">{{ $sc['days'] }} Hari Buku</span>
                                </p>
                            </div>
                        </article>

                        {{-- Card 2: Laba Kotor --}}
                        <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                            <header class="flex items-start justify-between gap-2">
                                <h2 class="gpa-note font-semibold text-success-deep">LABA KOTOR</h2>
                                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                                    {{ $sc['trend_badge'] }}
                                </span>
                            </header>

                            <p class="font-inter text-2xl font-bold leading-8 text-ink">
                                {{ number_format($sc['margin'], 1, ',', '.') }}%
                            </p>

                            <p class="flex flex-wrap items-center gap-1.5">
                                <span class="gpa-meta text-success-deep font-medium">Floor Min: 15.0%</span>
                                <span class="gpa-meta text-ink font-bold">Laba Bersih Sah</span>
                            </p>

                            <div class="flex flex-col gap-2">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full bg-success-deep"
                                        style="width: {{ round(($sc['margin'] / 30) * 100, 1) }}%"></div>
                                </div>

                                <p class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="gpa-note text-success-deep font-medium">Gross:</span>
                                    <span class="gpa-note text-ink font-bold">Rp {{ number_format($sc['gross'], 0, ',', '.') }}</span>
                                </p>
                            </div>
                        </article>

                        {{-- Card 3: Transaksi Selesai --}}
                        <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                            <header class="flex items-start justify-between gap-2">
                                <h2 class="gpa-note font-semibold text-success-deep">TRANSAKSI SELESAI</h2>
                                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                                    {{ number_format($sc['settled'], 1, ',', '.') }}%
                                </span>
                            </header>

                            <p class="font-inter text-2xl font-bold leading-8 text-ink">
                                Terpenuhi
                            </p>

                            <p class="flex flex-wrap items-center gap-1.5">
                                <span class="gpa-meta text-success-deep font-medium">AOV Rata-rata:</span>
                                <span class="gpa-meta text-ink font-bold">Rp {{ number_format($sc['aov'], 0, ',', '.') }}</span>
                            </p>

                            <div class="flex flex-col gap-2">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full bg-ink-strong"
                                        style="width: {{ $sc['settled'] }}%"></div>
                                </div>

                                <p class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="gpa-note text-success-deep font-medium">Fisik Tera:</span>
                                    <span class="gpa-note text-ink font-bold">{{ number_format($sc['volume_kg'], 0, ',', '.') }} kg</span>
                                </p>
                            </div>
                        </article>

                        {{-- Card 4: Tagihan Terbayar --}}
                        <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                            <header class="flex items-start justify-between gap-2">
                                <h2 class="gpa-note font-semibold text-success-deep">TAGIHAN TERBAYAR</h2>
                                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                                    LANCAR
                                </span>
                            </header>

                            <p class="font-inter text-2xl font-bold leading-8 text-ink">
                                Kas Masuk
                            </p>

                            <p class="flex flex-wrap items-center gap-1.5">
                                <span class="gpa-meta text-success-deep font-medium">Total Billed:</span>
                                <span class="gpa-meta text-ink font-bold">Rp {{ number_format($sc['billing'], 0, ',', '.') }}</span>
                            </p>

                            <div class="flex flex-col gap-2">
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full bg-success-deep"
                                        style="width: {{ min(100, $paidPct) }}%"></div>
                                </div>

                                <p class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="gpa-note text-success-deep font-medium">Open:</span>
                                    <span class="gpa-note text-warning-caution font-semibold">Rp {{ number_format($sc['outstanding'], 0, ',', '.') }}</span>
                                </p>
                            </div>
                        </article>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- Grafik realisasi mingguan vs ceiling & BEP --}}
        <section class="flex flex-col gap-6 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Direksi (W40 - W44)</h2>
                </div>

                <ul class="flex flex-wrap items-center gap-3">
                    <li class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2 shrink-0 rounded bg-ink-strong" aria-hidden="true"></span>
                        <span class="gpa-note text-ink">Realisasi</span>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2 shrink-0 rounded bg-accent-deep" aria-hidden="true"></span>
                        <span class="gpa-note text-ink">Ceiling</span>
                    </li>
                    <li class="flex items-center gap-1.5">
                        <span class="h-2.5 w-2 shrink-0 rounded bg-warning-caution" aria-hidden="true"></span>
                        <span class="gpa-note text-ink">BEP</span>
                    </li>
                </ul>
            </header>

            <div class="relative pt-8">
                <span
                    class="absolute left-0 top-0 rounded bg-accent px-2 py-1 gpa-note text-ink-strong">Target Cap: Rp 120M / Mgg</span>

                <div class="flex h-10 items-end gap-3">
                    @foreach ($trendRows as $row)
                        <button type="button" @click="inspectWeek(@js($row))"
                            class="flex-1 text-center">
                            <span class="gpa-note font-bold text-success-deep">{{ $row['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="relative mt-1 h-44">
                    <div class="absolute inset-x-0 top-0 border-t border-dashed border-ink-strong" aria-hidden="true"></div>
                    <div class="absolute inset-x-0 border-t border-dashed border-warning-caution"
                        style="bottom: {{ round($bep / $ceiling * 100, 1) }}%" aria-hidden="true"></div>

                    <span
                        class="absolute left-0 z-10 -translate-y-full rounded bg-warning-cream px-2 py-1 gpa-note text-warning-caution"
                        style="bottom: {{ round($bep / $ceiling * 100, 1) }}%">BEP Baseline: Rp 65M</span>

                    <div class="flex h-full items-end gap-3">
                        @foreach ($trendRows as $row)
                            <div class="flex h-full flex-1 items-end justify-center">
                                <div class="relative h-full w-12 rounded bg-surface-track">
                                    <div class="absolute inset-x-0 bottom-0 rounded {{ $row['fill'] }}"
                                        style="height: {{ $row['share'] }}%"></div>
                                    @if ($row['status'] === 'running')
                                        <div class="absolute inset-x-0 border-t-2 border-dashed border-ink-strong"
                                            style="bottom: {{ $row['share'] }}%" aria-hidden="true"></div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-1 flex gap-3">
                    @foreach ($trendRows as $row)
                        <p class="flex-1 text-center gpa-meta-lg font-bold text-ink">{{ $row['week'] }}</p>
                    @endforeach
                </div>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 pt-4">
                <p class="gpa-note font-bold text-ink">Puncak Realisasi: {{ $peakRow && $peakRow['value'] > 0 ? ($peakRow['week'] . ' (' . $peakRow['label'] . ')') : '—' }}</p>
            </footer>
        </section>

        {{-- Komposisi revenue per kanal --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0 space-y-1">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Komposisi Revenue Saluran</h2>
                </div>

                <p class="gpa-note font-bold text-ink">Konsolidasi {{ count($channelRows) }} Kanal: Rp {{ number_format($totalChVal, 0, ',', '.') }}</p>
            </header>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($channelRows as $row)
                    <button type="button" @click="inspectChannel(@js($row))"
                        class="flex flex-col gap-3 rounded-xl bg-surface-shell p-4 text-left outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <span class="flex items-center gap-2">
                            <span class="h-3 w-3 shrink-0 rounded-full {{ $row['dot'] }}" aria-hidden="true"></span>
                            <span class="font-sans text-base font-semibold leading-6 text-ink">{{ $row['name'] }}</span>
                        </span>

                        <span class="gpa-meta-lg font-bold text-ink">{{ $row['share_label'] }}</span>

                        <span class="flex flex-col gap-1">
                            <span class="gpa-note text-success-deep">Nominal Realisasi:</span>
                            <span class="gpa-meta-lg font-bold text-ink">{{ $row['value_label'] }}</span>
                        </span>

                        <span class="h-2 w-full overflow-hidden rounded-full bg-surface-track" aria-hidden="true">
                            <span class="block h-full rounded-full {{ $row['dot'] }}"
                                style="width: {{ $row['share'] }}%"></span>
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Neraca portofolio 5 komoditas inti --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board pb-4">
                <div class="min-w-0 space-y-1">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Kinerja Portofolio Finansial: 5 Komoditas Inti GPA</h2>
                </div>

                <button type="button" @click="verifyPortfolio()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-surface-track px-3 py-2 outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-note font-bold text-ink">VERIFIED</span>
                </button>
            </header>

            <div class="gpa-scroll-x overflow-x-auto py-1">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">Kinerja Portofolio Finansial: 5 Komoditas Inti GPA</caption>
                    <thead>
                        <tr class="border-b border-line-board bg-surface-track">
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-left">KOMODITAS &amp; SKU</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-left">VOLUME (KG)</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-left">HARGA (IDR/KG)</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-left">REALISASI (IDR/KG)</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-right">TOTAL OMZET</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-right">GROSS PROFIT</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-right">MARGIN</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-right">% KONTRIBUSI</th>
                            <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink text-right">DIREKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($portfolioRows ?? [] as $row)
                            <tr class="border-b border-line-board/60">
                                <th scope="row" class="px-3 py-4 text-left align-middle font-normal">
                                    <span class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                                        <span class="font-inter text-xs font-bold leading-4 text-ink">{{ $row['name'] }}</span>
                                    </span>
                                    <span class="mt-0.5 block gpa-note font-semibold text-success-deep">{{ $row['sku'] }}</span>
                                </th>
                                <td class="px-3 py-4 align-middle">
                                    <span class="gpa-meta font-medium text-ink">{{ $row['volume_label'] }}</span>
                                    <span class="mt-0.5 block gpa-note text-ink-body">kg</span>
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <span class="gpa-meta font-medium text-ink">{{ $row['list_price_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 align-middle">
                                    <span class="gpa-meta font-medium text-success-deep">{{ $row['price_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span class="gpa-meta-lg font-bold text-ink">{{ $row['revenue_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span class="gpa-meta-lg font-bold text-success-deep">{{ $row['gross_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span
                                        class="inline-block rounded px-2 py-0.5 text-[11px] font-semibold leading-4 gpa-meta {{ ($row['status_tone'] ?? '') === 'caution' ? 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution' : 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep' }}">{{ $row['margin_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <span class="gpa-meta-lg font-bold text-ink">{{ $row['share_label'] }}</span>
                                </td>
                                <td class="px-3 py-4 text-right align-middle">
                                    <button type="button" @click="inspectCommodity(@js($row))"
                                        class="inline-flex flex-col items-end gap-1">
                                        <span
                                            class="inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ ($row['status_tone'] ?? '') === 'caution' ? 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution' : 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep' }}">{{ $row['status'] }}</span>
                                        <span class="h-1.5 w-16 overflow-hidden rounded-full bg-surface-track" aria-hidden="true">
                                            <span class="block h-full rounded-full bg-success-deep"
                                                style="width: {{ $row['bar_percent'] }}%"></span>
                                        </span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-ink-quiet bg-surface-track">
                            <th scope="row" class="px-3 py-3 text-left align-middle gpa-meta-lg font-bold text-ink">
                                Total 5 Komoditas
                            </th>
                            <td class="px-3 py-3 align-middle">
                                <span class="gpa-meta-lg font-bold text-ink">{{ number_format($volTotal ?? 0, 0, ',', '.') }} kg</span>
                            </td>
                            <td class="px-3 py-3 text-center align-middle gpa-note text-ink-body">-</td>
                            <td class="px-3 py-3 text-center align-middle gpa-note text-ink-body">-</td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-meta-lg font-bold text-ink">Rp {{ number_format($revTotal ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-meta-lg font-bold text-ink-strong">Rp {{ number_format($grossTotal ?? 0, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="inline-block rounded bg-ink-strong px-2 py-0.5 text-[11px] font-semibold leading-4 gpa-meta text-accent">{{ round((($grossTotal ?? 0) / (($revTotal ?? 1) ?: 1)) * 100, 1) }}%</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-meta-lg font-bold text-success-deep">100.0%</span>
                            </td>
                            <td class="px-3 py-3 text-right align-middle">
                                <span class="gpa-note font-bold text-success-deep">VERIFIED</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 pt-3">
                <p class="gpa-note font-bold text-ink">MARGIN FLOOR: 15.0%</p>
            </footer>
        </section>

        {{-- Rekonsiliasi cash inflow --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0 space-y-1">
                    <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Rekonsiliasi Cash Inflow &amp; Distribusi Metode Pembayaran</h2>
                </div>

                <p class="gpa-note font-bold text-ink">TOTAL CASH INFLOW: Rp {{ number_format($settlementTotal, 0, ',', '.') }}</p>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($settlementRows as $row)
                    <button type="button" @click="inspectMethod(@js($row))"
                        class="flex flex-col gap-2 rounded-xl bg-surface p-4 text-left transition-colors hover:bg-surface-shell">
                        <span class="flex items-start justify-between gap-2">
                            <span class="font-sans text-base font-semibold leading-6 text-ink">{{ $row['name'] }}</span>
                            <span
                                class="shrink-0 rounded bg-surface-track px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta text-ink">{{ $row['share_label'] }}</span>
                        </span>

                        <span class="font-inter text-sm font-bold leading-5 text-ink">{{ $row['value_label'] }}</span>

                        <span class="flex items-start gap-1.5">
                            <x-gpa.icon name="check-circle"
                                class="mt-0.5 h-3 w-3 shrink-0 text-success-deep" />
                            <span class="gpa-body text-success-deep">{{ $row['note'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-surface-track p-4 outline outline-1 -outline-offset-1 outline-line-board">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ink-strong">
                        <x-gpa.icon name="leaf" class="h-4 w-4 text-accent" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-sans text-base font-bold leading-6 text-ink">Rekapitulasi Kas Harian</span>
                        <span class="mt-0.5 block gpa-note text-success-deep">Seluruh rekap kas masuk harian ditandatangani secara digital oleh Direksi Keuangan.</span>
                    </span>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button type="button" @click="sealRecap()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3 py-2 gpa-note font-bold text-ink outline outline-1 -outline-offset-1 outline-accent-deep transition-colors hover:bg-accent-deep hover:text-ink">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0" />
                        Tanda Tangan
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection
