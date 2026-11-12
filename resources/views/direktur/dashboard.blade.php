@extends('layouts.direktur')

@section('title', 'Dashboard Direktur')

@section('content')
    @php
        $weeklyChartCeiling = max(
            1,
            max(array_column($weeklyTrends ?? [], 'revenue') ?: [0]),
            max(array_column($weeklyTrends ?? [], 'target') ?: [0]),
        ) * 1.2;
        $weeklyChartCoordinates = [];
        $weeklyTargetPoints = [];
        foreach ($weeklyTrends ?? [] as $index => $week) {
            $x = (($index + 0.5) / count($weeklyTrends)) * 1000;
            $revenueShare = $weeklyChartCeiling > 0 ? min(100, max(0, ($week['revenue'] / $weeklyChartCeiling) * 100)) : 0;
            $revenueY = 100 - $revenueShare;
            $targetY = 100 - min(100, max(0, ($week['target'] / $weeklyChartCeiling) * 100));
            $weeklyChartCoordinates[] = ['x' => $x, 'y' => $revenueY, 'share' => $revenueShare];
            $weeklyTargetPoints[] = number_format($x, 2, '.', '') . ',' . number_format($targetY, 2, '.', '');
        }
        $weeklyRevenuePath = '';
        foreach ($weeklyChartCoordinates as $index => $point) {
            if ($index === 0) {
                $weeklyRevenuePath = 'M ' . number_format($point['x'], 2, '.', '') . ' ' . number_format($point['y'], 2, '.', '');
                continue;
            }

            $previous = $weeklyChartCoordinates[$index - 1];
            $beforePrevious = $weeklyChartCoordinates[$index - 2] ?? $previous;
            $next = $weeklyChartCoordinates[$index + 1] ?? $point;
            $controlOneX = $previous['x'] + (($point['x'] - $beforePrevious['x']) / 6);
            $controlOneY = $previous['y'] + (($point['y'] - $beforePrevious['y']) / 6);
            $controlTwoX = $point['x'] - (($next['x'] - $previous['x']) / 6);
            $controlTwoY = $point['y'] - (($next['y'] - $previous['y']) / 6);
            $weeklyRevenuePath .= ' C ' . number_format($controlOneX, 2, '.', '') . ' ' . number_format($controlOneY, 2, '.', '')
                . ', ' . number_format($controlTwoX, 2, '.', '') . ' ' . number_format($controlTwoY, 2, '.', '')
                . ', ' . number_format($point['x'], 2, '.', '') . ' ' . number_format($point['y'], 2, '.', '');
        }
        $weeklyRevenueAreaPath = $weeklyChartCoordinates
            ? $weeklyRevenuePath . ' L ' . number_format(end($weeklyChartCoordinates)['x'], 2, '.', '') . ' 100 L '
            . number_format($weeklyChartCoordinates[0]['x'], 2, '.', '') . ' 100 Z'
            : '';
        $weeklyChartTicks = [100, 75, 50, 25, 0];
        $weeklyRevenueColor = '#15803D';
        $weeklyTargetColor = '#B45309';
        $weeklyChartTickLabel = static function (float $value): string {
            if ($value >= 1000000000) {
                return number_format($value / 1000000000, 1, ',', '.') . ' M';
            }
            if ($value >= 1000000) {
                return number_format($value / 1000000, 0, ',', '.') . ' jt';
            }

            return number_format($value / 1000, 0, ',', '.') . ' rb';
        };
        $topProductMax = max(array_column($topProductsData ?? [], 'ton') ?: [0]);
        $topProductChartTicks = [100, 75, 50, 25, 0];
        $channelCards = [
            [
                'label' => 'Horeca & Inflight',
                'data' => $channelData['horeca'] ?? ['share' => 0, 'ton' => 0],
                'trend' => $weeklyChannelTrends['horeca'] ?? [],
                'color' => '#0F766E',
            ],
            [
                'label' => 'Ritel Modern',
                'data' => $channelData['retail'] ?? ['share' => 0, 'ton' => 0],
                'trend' => $weeklyChannelTrends['retail'] ?? [],
                'color' => '#D97706',
            ],
            [
                'label' => 'Reguler WA',
                'data' => $channelData['manual'] ?? ['share' => 0, 'ton' => 0],
                'trend' => $weeklyChannelTrends['manual'] ?? [],
                'color' => '#DB2777',
            ],
        ];
        foreach ($channelCards as $index => $channel) {
            $values = array_column($channel['trend'], 'ton');
            $minValue = $values ? min($values) : 0;
            $valueRange = $values ? max($values) - $minValue : 0;
            $points = [];
            foreach ($values as $pointIndex => $value) {
                $x = count($values) > 1 ? ($pointIndex / (count($values) - 1)) * 100 : 50;
                $y = $valueRange > 0 ? 90 - ((($value - $minValue) / $valueRange) * 75) : 52;
                $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }
            $channelCards[$index]['sparkline'] = implode(' ', $points);
        }
        $makeKpiSparkline = static function (array $trends, string $key): string {
            $values = array_column($trends, $key);
            if (count($values) < 2) {
                return '';
            }

            $minValue = min($values);
            $valueRange = max($values) - $minValue;
            $points = [];
            foreach ($values as $index => $value) {
                $x = ($index / (count($values) - 1)) * 100;
                $y = $valueRange > 0 ? 34 - ((($value - $minValue) / $valueRange) * 28) : 20;
                $points[] = number_format($x, 2, '.', '') . ',' . number_format($y, 2, '.', '');
            }

            return implode(' ', $points);
        };
        $revenueSparkline = $makeKpiSparkline($weeklyTrends ?? [], 'revenue');
        $harvestSparkline = $makeKpiSparkline($weeklyTrends ?? [], 'harvest_ton');
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="{ showOverdueAuditModal: false, showDispensationModal: false, dispensationGranted: false }">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-4 sm:p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <p class="gpa-meta text-xs font-bold uppercase tracking-wider text-accent-deep">Dasbor Eksekutif Direktur
                </p>
                <h1 class="font-sans text-2xl sm:text-3xl font-bold leading-8 sm:leading-10 tracking-[-0.01em] text-ink">
                    Ringkasan Kinerja Eksekutif Direktur
                </h1>
            </div>

            <div class="flex w-full flex-col sm:w-auto sm:flex-row sm:items-center gap-2.5 sm:gap-3">
                <a href="{{ route('direktur.laporan.export') }}"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-surface px-4 py-2.5 text-ink shadow-sub outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                    <span class="text-center text-xs font-semibold leading-4 text-ink">Unduh Buku Besar (CSV)</span>
                </a>
                <a href="{{ route('direktur.laporan.download') }}" target="_blank"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-white shadow-sub outline outline-1 -outline-offset-1 outline-success transition-colors hover:bg-brand-hover">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                    <span class="text-center text-xs font-semibold leading-4 text-white">Cetak Laporan Eksekutif
                        (PDF)</span>
                </a>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4" aria-label="KPI eksekutif">
            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl bg-surface p-4 shadow-card sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xs font-semibold leading-4 text-ink-body">Omzet Tahun Ini</h2>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent text-brand">
                        <x-gpa.icon name="banknote" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-1 break-words text-[22px] font-bold leading-7 tracking-tight text-ink">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-[11px] leading-4 text-ink-subtle">
                    Target Rp {{ number_format($targetRevenue, 0, ',', '.') }}
                    <span class="ml-1 font-semibold text-success-deep">{{ $revenuePercent }}% tercapai</span>
                </p>
                <div class="mt-auto pt-3">
                    @if ($revenueSparkline)
                        <svg class="h-8 w-full text-brand" viewBox="0 0 100 40" preserveAspectRatio="none"
                            role="img" aria-label="Tren omzet lima pekan terakhir">
                            <polyline points="{{ $revenueSparkline }}" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                vector-effect="non-scaling-stroke" />
                        </svg>
                    @else
                        <div class="h-8" aria-hidden="true"></div>
                    @endif
                </div>
            </article>

            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl bg-surface p-4 shadow-card sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xs font-semibold leading-4 text-ink-body">Volume Komoditas</h2>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-success-soft text-success-deep">
                        <x-gpa.icon name="package" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-1 break-words text-[22px] font-bold leading-7 tracking-tight text-ink">
                    {{ number_format($totalTonnageKg, 0, ',', '.') }} <span class="text-base font-semibold">kg</span>
                </p>
                <p class="mt-1 text-[11px] leading-4 text-ink-subtle">
                    Binaan {{ $binaanPercent }}% <span class="mx-1">&middot;</span> Buffer {{ $bufferPercent }}%
                </p>
                <div class="mt-auto pt-3">
                    @if ($harvestSparkline)
                        <svg class="h-8 w-full text-success-deep" viewBox="0 0 100 40" preserveAspectRatio="none"
                            role="img" aria-label="Tren realisasi panen lima pekan terakhir">
                            <polyline points="{{ $harvestSparkline }}" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                vector-effect="non-scaling-stroke" />
                        </svg>
                    @else
                        <div class="h-8" aria-hidden="true"></div>
                    @endif
                </div>
            </article>

            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl bg-surface p-4 shadow-card sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xs font-semibold leading-4 text-ink-body">Tagihan Pelanggan</h2>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-warning-cream text-warning-caution">
                        <x-gpa.icon name="alert-triangle" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-1 break-words text-[22px] font-bold leading-7 tracking-tight text-ink">
                    Rp {{ number_format($totalReceivables, 0, ',', '.') }}
                </p>
                <p class="mt-1 text-[11px] leading-4 text-ink-subtle">
                    {{ $nearDueCount }} pelanggan jatuh tempo &le;7 hari
                </p>
                <div class="mt-auto flex items-center justify-between gap-2 pt-3 text-[10px] leading-4">
                    <span class="text-ink-subtle">Jatuh tempo dekat</span>
                    <span class="text-right font-semibold text-warning-caution">
                        Rp {{ number_format($nearDueAmount, 0, ',', '.') }}
                    </span>
                </div>
            </article>

            <article class="flex min-w-0 flex-col overflow-hidden rounded-2xl bg-surface p-4 shadow-card sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xs font-semibold leading-4 text-ink-body">Ketepatan Pengiriman</h2>
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent text-success-deep">
                        <x-gpa.icon name="check-circle" class="h-4 w-4" />
                    </span>
                </div>
                <p class="mt-1 break-words text-[22px] font-bold leading-7 tracking-tight text-ink">
                    {{ $slaPercent }}%
                </p>
                <p class="mt-1 text-[11px] leading-4 text-ink-subtle">
                    Pesanan selesai tanpa retur
                </p>
                <div class="mt-auto pt-3">
                    <div class="h-1.5 overflow-hidden rounded-full bg-surface-track" role="presentation">
                        <div class="h-full rounded-full bg-success-deep" style="width: {{ min(100, $slaPercent) }}%"></div>
                    </div>
                    <p class="mt-1.5 text-[10px] leading-4 text-ink-subtle">{{ $returnCount }} pesanan dengan catatan/retur</p>
                </div>
            </article>
        </section>

        <section class="flex flex-col items-stretch gap-3 rounded-xl border border-brand/20 bg-surface-shell px-3 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-4"
            aria-label="Pengajuan persetujuan kontrak">
            <div class="flex min-w-0 items-start gap-3 sm:items-center">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-accent text-brand">
                    <x-gpa.icon name="shield" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-ink">Pengajuan Persetujuan</p>
                    <p class="break-words text-[11px] leading-4 text-ink-subtle">
                        {{ $pendingCount }} kontrak menunggu &middot; Nilai Rp {{ number_format($pendingValue, 0, ',', '.') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('direktur.kontrak.persetujuan') }}"
                class="inline-flex w-full items-center justify-center gap-1 rounded-lg bg-brand px-3 py-2.5 text-xs font-bold text-white transition-colors hover:bg-brand-hover sm:w-auto sm:shrink-0">
                Tinjau Pengajuan ({{ $pendingCount }})
                <x-gpa.icon name="arrow-right" class="h-3 w-3" />
            </a>
        </section>

        {{-- Tren omzet mingguan dan pembagian komoditas --}}
        <section class="flex flex-col gap-4">
            <div class="grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
                <div class="flex min-w-0 flex-col gap-4">
                    {{-- Weekly revenue & volume --}}
                    <div class="gpa-panel flex min-w-0 flex-col gap-5 p-4 sm:p-6">
                        <header class="flex flex-wrap items-start justify-between gap-4 pb-4">
                            <div class="min-w-0">
                                <h2 class="gpa-section-title text-ink">Omzet dan Target Mingguan</h2>
                                <p class="mt-1 gpa-note text-ink-subtle">Tren 5 pekan terakhir</p>
                            </div>
                            <ul class="flex flex-wrap items-center gap-3 pt-1">
                                <li class="flex items-center gap-1.5">
                                    <span class="h-0.5 w-4 shrink-0" style="background-color: {{ $weeklyRevenueColor }}"
                                        aria-hidden="true"></span>
                                    <span class="gpa-note text-ink">Omzet</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <span class="h-0.5 w-4 shrink-0 border-t-2 border-dashed"
                                        style="border-color: {{ $weeklyTargetColor }}"
                                        aria-hidden="true"></span>
                                    <span class="gpa-note text-ink">Target</span>
                                </li>
                            </ul>
                        </header>

                        <div class="flex min-w-0 gap-3 sm:gap-5">
                            <div class="relative min-w-0 w-full flex-1">
                                <div class="relative h-52 sm:h-60" role="group"
                                    aria-label="Grafik garis omzet dan target untuk lima pekan terakhir">
                                    @foreach ($weeklyChartTicks as $tick)
                                        <div class="absolute inset-x-0 border-t border-line-board/60"
                                            style="top: {{ 100 - $tick }}%" aria-hidden="true"></div>
                                    @endforeach

                                    @if ($weeklyTrends ?? [])
                                        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 1000 100"
                                            preserveAspectRatio="none" aria-hidden="true">
                                            <defs>
                                                <linearGradient id="director-weekly-area" x1="0" x2="0" y1="0" y2="1">
                                                    <stop offset="0%" stop-color="{{ $weeklyRevenueColor }}" stop-opacity="0.24" />
                                                    <stop offset="100%" stop-color="{{ $weeklyRevenueColor }}" stop-opacity="0.01" />
                                                </linearGradient>
                                            </defs>
                                            <polyline points="{{ implode(' ', $weeklyTargetPoints) }}" fill="none"
                                                stroke="{{ $weeklyTargetColor }}" stroke-width="2" stroke-dasharray="7 6"
                                                vector-effect="non-scaling-stroke" />
                                            <path d="{{ $weeklyRevenueAreaPath }}" fill="url(#director-weekly-area)" />
                                            <path d="{{ $weeklyRevenuePath }}" fill="none" stroke="{{ $weeklyRevenueColor }}" stroke-width="2.5"
                                                vector-effect="non-scaling-stroke" />
                                        </svg>
                                    @else
                                        <p class="absolute inset-0 flex items-center justify-center text-xs text-ink-body">
                                            Belum ada riwayat omzet mingguan.
                                        </p>
                                    @endif

                                    @foreach ($weeklyTrends as $index => $week)
                                        @php
                                            $pointX = (($index + 0.5) / count($weeklyTrends)) * 100;
                                            $chartShare = $weeklyChartCoordinates[$index]['share'];
                                        @endphp
                                        <div class="group absolute inset-y-0 z-10 -translate-x-1/2"
                                            style="left: {{ number_format($pointX, 2, '.', '') }}%">
                                            <span
                                                class="pointer-events-none absolute bottom-0 left-1/2 w-px -translate-x-1/2 opacity-0 transition-opacity group-hover:opacity-100"
                                                style="background-color: {{ $weeklyRevenueColor }}"
                                                style="height: {{ number_format($chartShare, 2, '.', '') }}%"></span>
                                            <span class="absolute left-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2"
                                                style="top: {{ number_format(100 - $chartShare, 2, '.', '') }}%">
                                                <span
                                                    class="absolute left-1/2 top-1/2 h-3 w-3 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-white shadow-sm"
                                                    style="background-color: {{ $weeklyRevenueColor }}"></span>
                                                <span
                                                    class="pointer-events-none absolute bottom-full left-1/2 mb-2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-ink px-2.5 py-1.5 text-left text-white shadow-lg group-hover:block">
                                                    <span class="block gpa-micro">{{ $week['week'] }}</span>
                                                    <span class="block gpa-note font-semibold">Omzet Rp
                                                        {{ number_format($week['revenue'], 0, ',', '.') }}</span>
                                                    <span class="block gpa-micro">Target Rp
                                                        {{ number_format($week['target'], 0, ',', '.') }}</span>
                                                </span>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-3 flex">
                                    @foreach ($weeklyTrends as $week)
                                        <div class="min-w-0 flex-1 text-center">
                                            <p class="gpa-micro uppercase tracking-wider text-ink-subtle">{{ $week['week'] }}
                                            </p>
                                            <p class="mt-1 truncate gpa-micro text-ink"
                                                title="Omzet Rp {{ number_format($week['revenue'], 0, ',', '.') }} · Target Rp {{ number_format($week['target'], 0, ',', '.') }}">
                                                {{ number_format($week['pct'], 0) }}% target
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="relative h-52 w-12 shrink-0 sm:h-60">
                                @foreach ($weeklyChartTicks as $tick)
                                    @php
                                        $tickValue = $weeklyChartCeiling * $tick / 100;
                                    @endphp
                                    <span class="absolute right-0 -translate-y-1/2 gpa-micro text-ink-subtle"
                                        style="top: {{ 100 - $tick }}%">
                                        {{ $tick === 0 ? '0' : $weeklyChartTickLabel($tickValue) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5 Komoditas Utama --}}
                <div class="gpa-panel flex min-h-[280px] min-w-0 flex-col gap-4 p-4 sm:p-6">
                    <header class="pb-3">
                        <h2 class="gpa-section-title text-ink">5 Komoditas Utama</h2>
                    </header>

                    <div class="flex min-h-0 flex-1 flex-col">
                        <div class="flex min-h-0 flex-1 flex-col">
                            <div class="flex min-h-0 min-w-0 flex-1 gap-2 pb-2">
                                <div class="flex min-w-0 flex-1 flex-col">
                                    <div class="relative min-h-[180px] min-w-0 w-full flex-1">
                                        @foreach ($topProductChartTicks as $tick)
                                            <div class="absolute inset-x-0 border-t border-line-board/60"
                                                style="top: {{ 100 - $tick }}%" aria-hidden="true"></div>
                                        @endforeach

                                        @if ($topProductsData)
                                            <div class="absolute inset-0 flex items-stretch gap-2">
                                                @foreach ($topProductsData as $tp)
                                                    <div class="relative min-w-0 flex-1"
                                                        title="{{ $tp['name'] }}: {{ number_format($tp['ton'], 2, ',', '.') }} ton">
                                                        <div class="absolute inset-x-0 bottom-0 mx-auto max-w-10 rounded-t {{ $tp['color'] }}"
                                                            style="height: {{ $topProductMax > 0 ? ($tp['ton'] / $topProductMax) * 100 : 0 }}%"
                                                            role="img"
                                                            aria-label="{{ $tp['name'] }}: {{ number_format($tp['ton'], 2, ',', '.') }} ton">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="absolute inset-0 flex items-center justify-center text-xs text-ink-body">
                                                Belum ada data komoditas.
                                            </p>
                                        @endif
                                    </div>

                                    @if ($topProductsData)
                                        <div class="flex h-6 shrink-0 items-end gap-2">
                                            @foreach ($topProductsData as $idx => $tp)
                                                <span class="min-w-0 flex-1 truncate text-center gpa-micro text-ink-subtle"
                                                    title="{{ $tp['name'] }}">{{ $tp['name'] }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                @if ($topProductsData)
                                    <div class="flex w-10 shrink-0 flex-col">
                                        <div class="relative min-h-0 flex-1">
                                            @foreach ($topProductChartTicks as $tick)
                                                @php
                                                    $tickValue = $topProductMax * $tick / 100;
                                                @endphp
                                                <span class="absolute right-0 -translate-y-1/2 gpa-micro text-ink-subtle"
                                                    style="top: {{ 100 - $tick }}%">
                                                    {{ number_format($tickValue, 1, ',', '.') }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <div class="h-6 shrink-0"></div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Saluran penjualan --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                @foreach ($channelCards as $channel)
                    <article
                        class="grid min-w-0 grid-cols-1 items-center gap-y-1 rounded-xl bg-surface p-3 shadow-card sm:min-h-36 sm:grid-cols-[minmax(0,1fr)_120px] sm:grid-rows-[auto_1fr_auto] sm:gap-x-3 sm:gap-y-2 sm:p-5">
                        <p class="truncate gpa-note font-semibold uppercase tracking-[0.12em] text-ink-subtle sm:tracking-[0.18em]">
                            {{ $channel['label'] }}</p>
                        <svg class="h-8 w-full sm:col-start-2 sm:row-span-3 sm:h-24" viewBox="0 0 100 80" preserveAspectRatio="none"
                            role="img" aria-label="Tren tonase lima pekan untuk {{ $channel['label'] }}">
                            @if ($channel['sparkline'])
                                <polyline points="{{ $channel['sparkline'] }}" fill="none" stroke="{{ $channel['color'] }}"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    vector-effect="non-scaling-stroke" />
                            @endif
                        </svg>
                        <p class="self-end font-sans text-2xl font-bold leading-none text-ink sm:text-3xl">
                            {{ number_format($channel['data']['share'], 0) }}%
                        </p>
                        <p class="gpa-note font-semibold uppercase tracking-wider text-success-deep">
                            {{ number_format($channel['data']['ton'], 2, ',', '.') }} ton
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Pemantauan tagihan --}}
        <section class="flex flex-col gap-5 rounded-2xl bg-surface p-4 shadow-card sm:p-5">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-brand">
                        <x-gpa.icon name="receipt" class="h-5 w-5" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold leading-6 text-ink sm:text-lg">Pemantauan Tagihan Pelanggan</h2>
                        <p class="mt-0.5 text-xs leading-4 text-ink-subtle">Ringkasan piutang berdasarkan umur tagihan</p>
                    </div>
                </div>
                <div class="rounded-xl bg-surface-shell px-4 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-ink-subtle">Total belum dibayar</p>
                    <p class="mt-0.5 text-base font-bold leading-5 text-ink">
                        Rp {{ number_format($totalReceivables, 0, ',', '.') }}
                    </p>
                </div>
            </header>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <article class="flex min-h-[132px] flex-col justify-between gap-3 rounded-xl bg-success-soft/60 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xs font-semibold leading-4 text-ink-body">Belum jatuh tempo</h3>
                            <p class="mt-0.5 text-[10px] leading-4 text-ink-subtle">0–15 hari</p>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-success-deep">
                            <x-gpa.icon name="clock" class="h-4 w-4" />
                        </span>
                    </div>
                    <div>
                        <p class="break-words text-lg font-bold leading-6 text-ink">
                            Rp {{ number_format($bucket0_15, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] font-semibold leading-4 text-success-deep">Pembayaran lancar</p>
                    </div>
                </article>

                <article class="flex min-h-[132px] flex-col justify-between gap-3 rounded-xl bg-surface-shell p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xs font-semibold leading-4 text-ink-body">Jatuh tempo</h3>
                            <p class="mt-0.5 text-[10px] leading-4 text-ink-subtle">16–30 hari</p>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-ink-body">
                            <x-gpa.icon name="calendar" class="h-4 w-4" />
                        </span>
                    </div>
                    <div>
                        <p class="break-words text-lg font-bold leading-6 text-ink">
                            Rp {{ number_format($bucket16_30, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] font-semibold leading-4 text-ink-body">Pengingat tempo terkirim</p>
                    </div>
                </article>

                <article class="flex min-h-[132px] flex-col justify-between gap-3 rounded-xl bg-warning-cream p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xs font-semibold leading-4 text-ink-body">Lewat jatuh tempo</h3>
                            <p class="mt-0.5 text-[10px] leading-4 text-ink-subtle">1–14 hari</p>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-warning-caution">
                            <x-gpa.icon name="alert-triangle" class="h-4 w-4" />
                        </span>
                    </div>
                    <div>
                        <p class="break-words text-lg font-bold leading-6 text-ink">
                            Rp {{ number_format($bucket31_45, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] font-semibold leading-4 text-warning-caution">Perlu tindak lanjut</p>
                    </div>
                </article>

                <article class="flex min-h-[132px] flex-col justify-between gap-3 rounded-xl bg-danger-soft/40 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xs font-semibold leading-4 text-ink-body">Terlambat lebih dari 15 hari</h3>
                            <p class="mt-0.5 text-[10px] leading-4 text-ink-subtle">Risiko tinggi</p>
                        </div>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface text-danger">
                            <x-gpa.icon name="alert-circle" class="h-4 w-4" />
                        </span>
                    </div>
                    <div>
                        <p class="break-words text-lg font-bold leading-6 text-ink">
                            Rp {{ number_format($bucketOver45, 0, ',', '.') }}
                        </p>
                        <p class="mt-1 text-[10px] font-semibold leading-4 text-danger">
                            {{ $worstOverdueInvoice ? 'Pesanan baru ditahan otomatis' : 'Tidak ada tagihan kritis' }}
                        </p>
                    </div>
                </article>
            </div>

            @if ($worstOverdueInvoice)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-warning-cream p-3 sm:p-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface text-warning-caution">
                            <x-gpa.icon name="alert-triangle" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold leading-4 text-ink">
                                {{ $worstOverdueInvoice->user?->company_name ?: $worstOverdueInvoice->user?->name }}
                            </p>
                            <p class="mt-0.5 text-[11px] leading-4 text-ink-subtle">
                                {{ $worstOverdueInvoice->invoice_number }}
                                <span class="mx-1">&middot;</span>
                                <span class="font-semibold text-warning-caution">Rp {{ number_format($worstOverdueInvoice->grand_total, 0, ',', '.') }}</span>
                            </p>
                            <p class="mt-1 text-[10px] font-semibold leading-4 text-danger">Pengiriman ditahan sampai tagihan lunas</p>
                        </div>
                    </div>
                    <div class="flex w-full shrink-0 gap-2 sm:w-auto">
                        <button type="button" @click="showOverdueAuditModal = true"
                            class="flex-1 rounded-lg bg-surface px-3 py-2 text-center text-[11px] font-semibold leading-4 text-ink shadow-sub transition-colors hover:bg-surface-shell sm:flex-none">
                            Lihat catatan
                        </button>
                        <button type="button" @click="showDispensationModal = true"
                            class="flex-1 rounded-lg bg-brand px-3 py-2 text-center text-[11px] font-semibold leading-4 text-white transition-colors hover:bg-brand-hover sm:flex-none">
                            Ajukan pengecualian
                        </button>
                    </div>
                </div>
            @endif

            @if ($worstOverdueInvoice)
                {{-- Modal Lihat Catatan Audit CV Mitra Boga --}}
                <div x-cloak x-show="showOverdueAuditModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 overscroll-contain"
                    x-effect="
                        document.body.classList.toggle('overflow-hidden', showOverdueAuditModal);
                        document.documentElement.classList.toggle('overflow-hidden', showOverdueAuditModal);
                    "
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showOverdueAuditModal = false">
                    <div class="relative w-full max-w-lg transform-gpu overflow-hidden rounded-2xl bg-surface p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.85)] ring-1 ring-white/10 border border-line-board"
                        @click.outside="showOverdueAuditModal = false">

                        <div class="flex items-start justify-between gap-4 border-b border-line-board/60 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-warning-caution/20 text-warning-deep">
                                        <x-gpa.icon name="alert-triangle" class="h-4 w-4" />
                                    </span>
                                    <h3 class="font-sans text-lg font-bold text-ink">
                                        Catatan Audit Kepatuhan Piutang
                                    </h3>
                                </div>
                                <p class="text-xs text-ink-body">
                                    {{ $worstOverdueInvoice->user?->company_name ?: $worstOverdueInvoice->user?->name }}
                                </p>
                            </div>
                            <button type="button" @click="showOverdueAuditModal = false"
                                class="rounded-lg p-1.5 text-ink-quiet transition hover:bg-surface-shell hover:text-ink">
                                <x-gpa.icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="my-4 space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-line-hair bg-surface-shell p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-ink-quiet">Nomor
                                        Faktur</span>
                                    <p class="text-sm font-bold font-mono text-ink mt-0.5">
                                        {{ $worstOverdueInvoice->invoice_number }}</p>
                                </div>
                                <div class="rounded-xl border border-line-hair bg-surface-shell p-3">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-ink-quiet">Nominal
                                        Tertunggak</span>
                                    <p class="text-sm font-bold font-mono text-danger mt-0.5">Rp
                                        {{ number_format($worstOverdueInvoice->grand_total, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-line-hair bg-surface-shell p-3 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-ink-quiet">Status
                                        Kepatuhan</span>
                                    <span
                                        class="rounded bg-danger/10 px-2 py-0.5 text-[10px] font-bold text-danger font-mono">DITAHAN
                                        OTOMATIS</span>
                                </div>
                                <p class="text-xs text-ink-body leading-relaxed pt-1">
                                    Faktur telah melewati batas jatuh tempo
                                    ({{ $worstOverdueInvoice->due_date ? \Illuminate\Support\Carbon::parse($worstOverdueInvoice->due_date)->format('d M Y') : 'Terlambat' }}).
                                    Berdasarkan tata kelola risiko likuiditas GPA, pembuatan Surat Jalan baru dan realisasi PO
                                    ditahan otomatis hingga pembayaran selesai dikliringkan atau diberikan dispensasi resmi oleh
                                    Direktur Utama.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-line-board/60 pt-4">
                            <a href="{{ route('direktur.piutang.index') }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-surface px-3 py-2 text-xs font-bold text-ink outline outline-1 outline-line-board hover:bg-surface-shell transition">
                                <x-gpa.icon name="invoice" class="h-3.5 w-3.5" />
                                Buka Modul Piutang
                            </a>
                            <button type="button" @click="showOverdueAuditModal = false"
                                class="rounded-lg bg-surface-track px-4 py-2 text-xs font-bold text-ink hover:bg-surface-disabled transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Modal Ajukan Pengecualian / Dispensasi CV Mitra Boga --}}
                <div x-cloak x-show="showDispensationModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 overscroll-contain"
                    x-effect="
                        document.body.classList.toggle('overflow-hidden', showDispensationModal);
                        document.documentElement.classList.toggle('overflow-hidden', showDispensationModal);
                    "
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @keydown.escape.window="showDispensationModal = false">
                    <div class="relative w-full max-w-lg transform-gpu overflow-hidden rounded-2xl bg-surface p-6 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.85)] ring-1 ring-white/10 border border-line-board"
                        @click.outside="showDispensationModal = false">

                        <div class="flex items-start justify-between gap-4 border-b border-line-board/60 pb-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                        <x-gpa.icon name="shield" class="h-4 w-4" />
                                    </span>
                                    <h3 class="font-sans text-lg font-bold text-ink">
                                        Otorisasi Dispensi Tenor Khusus
                                    </h3>
                                </div>
                                <p class="text-xs text-ink-body">
                                    Pemberian izin sementara pengiriman pesanan baru untuk
                                    {{ $worstOverdueInvoice->user?->company_name ?: $worstOverdueInvoice->user?->name }}
                                </p>
                            </div>
                            <button type="button" @click="showDispensationModal = false"
                                class="rounded-lg p-1.5 text-ink-quiet transition hover:bg-surface-shell hover:text-ink">
                                <x-gpa.icon name="x" class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="my-4 space-y-3">
                            <div class="rounded-xl border border-line-hair bg-surface-shell p-3 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-ink-body">Faktur Terkait:</span>
                                    <span class="font-mono font-bold text-ink">{{ $worstOverdueInvoice->invoice_number }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-ink-body">Total Tagihan:</span>
                                    <span class="font-mono font-bold text-danger">Rp
                                        {{ number_format($worstOverdueInvoice->grand_total, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-ink-body">Rekomendasi Dispensi:</span>
                                    <span class="font-mono font-bold text-success-deep">+7 Hari Kalender</span>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-ink">
                                    Catatan Persetujuan Direktur Utama:
                                </label>
                                <textarea rows="3"
                                    class="w-full rounded-xl border border-line-hair bg-surface p-3 text-xs text-ink focus:border-brand focus:outline-none"
                                    placeholder="Catatan dispensasi...">Dispensasi khusus untuk kesinambungan pasokan bahan baku katering industri mitra strategis GPA.</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-line-board/60 pt-4">
                            <button type="button" @click="showDispensationModal = false"
                                class="rounded-lg bg-surface px-4 py-2 text-xs font-bold text-ink outline outline-1 outline-line-board hover:bg-surface-shell transition">
                                Batalkan
                            </button>
                            <button type="button"
                                @click="dispensationGranted = true; showDispensationModal = false; $dispatch('gpa:toast', { title: 'Dispensi Disetujui', message: 'Toleransi tenor 7 hari untuk {{ $worstOverdueInvoice->user?->company_name ?: $worstOverdueInvoice->user?->name }} berhasil diotorisasi.', tone: 'success' })"
                                class="rounded-lg bg-brand px-4 py-2 text-xs font-bold text-white shadow-sub hover:bg-brand-hover transition">
                                Konfirmasi Otorisasi Dispensi
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        {{-- Ringkasan Pesanan & Kontrak Terbaru --}}
        <section class="grid gap-6 lg:grid-cols-2" x-data="{ orderFilter: 'ALL' }">
            <div class="rounded-2xl bg-surface p-6 shadow-card space-y-4">
                <div class="flex flex-wrap items-center justify-between border-b border-line-board/60 pb-3 gap-2">
                    <div>
                        <h2 class="gpa-section-title text-ink">Supervisi Pesanan Terkini</h2>
                        <span class="text-xs text-ink-quiet">Pengawasan Transaksi B2B Kontrak &amp; Reguler</span>
                    </div>

                    {{-- Tombol Filter Kategori Klien --}}
                    <div class="flex items-center gap-1 rounded-lg bg-surface-shell p-1 border border-line-hair text-[11px]">
                        <button type="button" @click="orderFilter = 'ALL'"
                            :class="orderFilter === 'ALL' ? 'bg-surface text-ink font-bold shadow-xs' : 'text-ink-quiet hover:text-ink'"
                            class="rounded px-2.5 py-1 transition-colors">
                            Semua
                        </button>
                        <button type="button" @click="orderFilter = 'B2B_KONTRAK'"
                            :class="orderFilter === 'B2B_KONTRAK' ? 'bg-brand text-white font-bold shadow-xs' : 'text-ink-quiet hover:text-ink'"
                            class="rounded px-2.5 py-1 transition-colors">
                            Kontrak B2B
                        </button>
                        <button type="button" @click="orderFilter = 'REGULER'"
                            :class="orderFilter === 'REGULER' ? 'bg-accent-deep text-white font-bold shadow-xs' : 'text-ink-quiet hover:text-ink'"
                            class="rounded px-2.5 py-1 transition-colors">
                            Klien Reguler
                        </button>
                    </div>
                </div>
                <div class="divide-y divide-line-board/40 max-h-[460px] overflow-y-auto pr-1">
                    @forelse($recentOrders ?? [] as $order)
                        @php
                            $isB2b = ($order->user?->client_type === 'B2B_KONTRAK');
                            $clientTypeKey = $isB2b ? 'B2B_KONTRAK' : 'REGULER';
                        @endphp
                        <a href="{{ route('direktur.inspeksi-pesanan.show', $order->id) }}"
                            x-show="orderFilter === 'ALL' || orderFilter === '{{ $clientTypeKey }}'"
                            title="Klik untuk buka lembar inspeksi pengawasan 360°"
                            class="group -mx-2 flex items-center justify-between rounded-xl px-2 py-2.5 text-xs transition-colors hover:bg-surface-shell">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-mono font-bold text-ink group-hover:text-brand flex items-center gap-1.5">
                                        {{ $order->order_number }}
                                        <x-gpa.icon name="external-link" class="h-3 w-3 opacity-0 group-hover:opacity-100 transition-opacity text-brand" />
                                    </span>
                                    @if($isB2b)
                                        <span class="rounded bg-accent/40 px-1.5 py-0.2 text-[9px] font-bold text-success-deep outline outline-1 outline-success-deep font-mono">B2B KONTRAK</span>
                                    @else
                                        <span class="rounded bg-surface-shell px-1.5 py-0.2 text-[9px] font-bold text-ink-quiet outline outline-1 outline-line-board font-mono">REGULER</span>
                                    @endif
                                </div>
                                <span class="text-ink-quiet block text-[11px]">{{ $order->user?->company_name ?? $order->user?->name ?? 'Klien' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-ink">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                <span class="block text-[10px] text-accent-deep font-bold">{{ $order->status }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="py-4 text-center text-xs text-ink-quiet">Belum ada pesanan terbaru.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-surface p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-line-board/60 pb-3">
                    <h2 class="gpa-section-title text-ink">Kontrak Kemitraan Terkini</h2>
                    <span class="text-xs text-ink-quiet">Perjanjian PKS B2B</span>
                </div>
                <div class="divide-y divide-line-board/40">
                    @forelse($recentContracts ?? [] as $contract)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-mono font-bold text-ink">{{ $contract->contract_number }}</span>
                                <span
                                    class="text-ink-quiet block text-[11px]">{{ $contract->user?->company_name ?? $contract->user?->name ?? 'Klien' }}
                                    &bull; {{ $contract->product?->name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-ink">Rp
                                    {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }}/kg</span>
                                <span
                                    class="block text-[10px] font-bold {{ $contract->status === 'ACTIVE' ? 'text-success-deep' : 'text-warning-caution' }}">{{ $contract->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-ink-quiet">Belum ada kontrak tercatat.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
