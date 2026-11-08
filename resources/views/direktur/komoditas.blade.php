@extends('layouts.direktur')

@section('title', 'Volume Komoditas // Stok Panen & Kapasitas Pasokan')

@section('content')
    @php
        $weeklyRows = $weeklyRows ?? [];
        $allocationsList = $allocationsList ?? [];
        $maxWeekly = !empty($weeklyRows) ? max(array_column($weeklyRows, 'total')) : 0;
        $maxWeekly = $maxWeekly > 0 ? $maxWeekly : 1;
        $weeklyChartTicks = [100, 75, 50, 25, 0];
        $weeklyRangeLabel = !empty($weeklyRows)
            ? $weeklyRows[0]['week'] . ' - ' . $weeklyRows[array_key_last($weeklyRows)]['week']
            : '—';
        $peakWeek = !empty($weeklyRows) ? ($weeklyRows[array_search(max(array_column($weeklyRows, 'total')), array_column($weeklyRows, 'total'))] ?? null) : null;
        $totalCommitment = (float) collect($allocationsList)->sum('commitment');
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorVolume(@js($commodityRows ?? []), @js($weeklyRows), @js($allocationsList))">

        {{-- Page header + range badge + aksi rekonsiliasi --}}
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-4 sm:p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <h1 class="font-sans text-2xl sm:text-3xl font-bold leading-8 sm:leading-10 tracking-[-0.01em] text-ink">
                    Monitoring Volume Komoditas, Stok Panen<br class="hidden sm:inline">&amp; Kapasitas Pasokan
                </h1>
            </div>

            <div class="flex w-full flex-col sm:w-auto sm:flex-row sm:items-center gap-2.5 sm:gap-3">
                <span
                    class="inline-flex w-full sm:w-auto h-10 items-center justify-center gap-2 rounded-lg bg-surface px-3 outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="gpa-meta text-success-deep">RANGE:</span>
                    <span class="gpa-meta font-bold text-ink">W{{ date('W') - 4 }} - W{{ date('W') }} MTD</span>
                    <span class="h-3 w-3 rounded-sm bg-success-deep" aria-hidden="true"></span>
                </span>

                <a href="{{ route('direktur.komoditas.export') }}"
                    class="inline-flex w-full sm:w-auto h-10 items-center justify-center gap-2 rounded-lg bg-surface px-4 text-ink shadow-sub outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="download" class="h-4 w-4 shrink-0 text-success-deep" />
                    <span class="text-sm font-semibold leading-5 text-ink font-inter">
                        UNDUH NERACA VOLUME (CSV)
                    </span>
                </a>
            </div>
        </section>

        {{-- Pita KPI neraca volume --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Card 1: Total Volume Terjual --}}
            <article class="flex h-full flex-col justify-between gap-3 overflow-hidden rounded-2xl bg-surface p-5 shadow-card">
                <header class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-success-deep">TOTAL VOLUME TERJUAL (MTD)</h2>
                    <span class="inline-flex shrink-0 items-center justify-center rounded-md px-1.5 py-3 bg-surface-track text-ink">
                        <x-gpa.icon name="scale" class="h-4 w-4" />
                    </span>
                </header>

                <p class="flex flex-wrap items-baseline gap-x-2">
                    <span class="font-mono text-[28px] font-bold leading-7 text-ink">{{ number_format($totalSoldTon ?? 0, 2, '.', '') }}</span>
                    <span class="font-mono text-xs font-medium leading-4 text-success-deep">Ton</span>
                </p>

                <p class="flex flex-wrap items-center gap-3">
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-success-deep">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                        +14.2% MoM
                    </span>
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-ink">
                        vs kuartal 420 T
                    </span>
                </p>

                <footer class="flex items-center justify-between gap-2 border-t border-surface-track pt-2.5">
                    <span class="gpa-micro-bold text-success-deep">TARGET ACHIEVED</span>
                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold bg-accent text-ink">
                        {{ round((($totalSoldTon ?? 0) / 420.0) * 100, 1) }}%
                    </span>
                </footer>
            </article>

            {{-- Card 2: Sumber Pasokan --}}
            <article class="flex h-full flex-col justify-between gap-3 overflow-hidden rounded-2xl bg-surface p-5 shadow-card">
                <header class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-success-deep">SUMBER PASOKAN PANEN</h2>
                    <span class="inline-flex shrink-0 items-center justify-center rounded-md px-1.5 py-3 bg-accent/40 text-ink-strong">
                        <x-gpa.icon name="leaf" class="h-4 w-4" />
                    </span>
                </header>

                <p class="flex flex-wrap items-baseline gap-x-2">
                    <span class="font-mono text-[28px] font-bold leading-7 text-ink">{{ $binaanPercent ?? 82 }}%</span>
                    <span class="font-mono text-xs font-medium leading-4 text-success-deep">Binaan</span>
                    <span class="font-mono text-[28px] font-bold leading-7 text-ink">: {{ $bufferPercent ?? 18 }}%</span>
                    <span class="font-mono text-xs font-medium leading-4 text-success-deep">Buffer</span>
                </p>

                <p class="flex flex-wrap items-center gap-3">
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-ink">
                        Binaan: {{ number_format($binaanTon ?? 0, 2, '.', '') }} T
                    </span>
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-ink">
                        Buffer: {{ number_format($bufferTon ?? 0, 2, '.', '') }} T
                    </span>
                </p>

                <footer class="flex items-center justify-between gap-2 border-t border-surface-track pt-2.5">
                    <span class="gpa-micro-bold text-success-deep">ALOKASI MANDATORI</span>
                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold bg-accent text-ink-strong outline outline-1 -outline-offset-1 outline-accent-deep">
                        RATIO OPTIMAL
                    </span>
                </footer>
            </article>

            {{-- Card 3: Susut Gudang & Sortir --}}
            <article class="flex h-full flex-col justify-between gap-3 overflow-hidden rounded-2xl bg-surface p-5 shadow-card">
                <header class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-success-deep">SUSUT GUDANG &amp; SORTIR</h2>
                    <span class="inline-flex shrink-0 items-center justify-center rounded-md px-1.5 py-3 bg-surface-track text-ink">
                        <x-gpa.icon name="gauge" class="h-4 w-4" />
                    </span>
                </header>

                <p class="flex flex-wrap items-baseline gap-x-2">
                    <span class="font-mono text-[28px] font-bold leading-7 text-ink">{{ number_format($shrinkagePercent ?? 0.7, 1, '.', '') }}%</span>
                    <span class="font-mono text-xs font-medium leading-4 text-success-deep">Deviasi Rata-rata</span>
                </p>

                <p class="flex flex-wrap items-center gap-3">
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-ink">
                        Plafon Toleransi Maksimal:
                    </span>
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-ink">
                        Maks 2.0%
                    </span>
                </p>

                <footer class="flex items-center justify-between gap-2 border-t border-surface-track pt-2.5">
                    <span class="gpa-micro-bold text-success-deep">STATUS PENYIMPANAN</span>
                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold bg-accent text-ink">
                        {{ ($shrinkagePercent ?? 0.7) <= 1.0 ? 'SANGAT AMAN' : 'PERHATIAN' }}
                    </span>
                </footer>
            </article>

            {{-- Card 4: Safety Stock Buffer --}}
            <article class="flex h-full flex-col justify-between gap-3 overflow-hidden rounded-2xl bg-surface p-5 shadow-card">
                <header class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-success-deep">SAFETY STOCK BUFFER CADANGAN</h2>
                    <span class="inline-flex shrink-0 items-center justify-center rounded-md px-1.5 py-3 bg-accent/40 text-ink-strong">
                        <x-gpa.icon name="shield" class="h-4 w-4" />
                    </span>
                </header>

                <p class="flex flex-wrap items-baseline gap-x-2">
                    <span class="font-mono text-[28px] font-bold leading-7 text-ink">{{ number_format($safetyStockBufferTon ?? 0, 2, '.', '') }}</span>
                    <span class="font-mono text-xs font-medium leading-4 text-success-deep">Ton Siap Pakai</span>
                </p>

                <p class="flex flex-wrap items-center gap-3">
                    <span class="flex items-center gap-1.5 font-mono text-xs leading-4 text-success-deep">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                        Zero-Deficit Flag Verified
                    </span>
                </p>

                <footer class="flex items-center justify-between gap-2 border-t border-surface-track pt-2.5">
                    <span class="gpa-micro-bold text-success-deep">ANTI-OVERSELLING GUARD</span>
                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold bg-accent text-ink-strong outline outline-1 -outline-offset-1 outline-accent-deep">
                        100% AKTIF
                    </span>
                </footer>
            </article>
        </section>

        {{-- Neraca volume lima komoditas inti --}}
        <section class="flex w-full flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card sm:p-5">
            <header class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-inter text-lg font-semibold leading-6 text-ink">Neraca Volume &amp; Komposisi</h2>
                    <p class="mt-0.5 gpa-note text-ink-subtle">Ringkasan 5 komoditas inti</p>
                </div>
            </header>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($commodityRows ?? [] as $row)
                    <button type="button" @click="inspectCommodity(@js($row))"
                        class="flex min-w-0 flex-col gap-4 rounded-xl bg-surface-shell p-4 text-left transition-colors hover:bg-surface-muted focus:outline-none focus-visible:ring-2 focus-visible:ring-success-deep">
                        <span class="flex min-w-0 items-start justify-between gap-2">
                            <span class="min-w-0">
                                <span class="block gpa-micro font-medium text-ink-subtle">{{ $row['code'] ?? '' }}</span>
                                <span class="mt-1 block truncate text-sm font-semibold leading-5 text-ink">{{ $row['name'] ?? '' }}</span>
                                <span class="mt-0.5 block truncate text-[11px] leading-4 text-ink-subtle">{{ $row['grade'] ?? '' }}</span>
                            </span>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ ($row['share_tone'] ?? '') === 'caution' ? 'bg-warning-cream text-warning-caution' : 'bg-success-soft text-success-deep' }}">
                                {{ $row['share_label'] ?? '' }}
                            </span>
                        </span>

                        <span class="border-t border-line-faint pt-3">
                            <span class="block text-[10px] leading-4 text-ink-subtle">Realisasi</span>
                            <span class="mt-0.5 block text-lg font-bold leading-6 text-ink">{{ $row['value_label'] ?? '' }} <span class="text-xs font-medium text-ink-subtle">ton</span></span>
                        </span>

                        <span class="grid grid-cols-2 gap-2 text-[11px] leading-4">
                            <span class="rounded-lg bg-surface px-2.5 py-2">
                                <span class="block text-ink-subtle">Binaan</span>
                                <span class="mt-0.5 block font-semibold text-ink">{{ $row['farmer_label'] ?? '' }}</span>
                            </span>
                            <span class="rounded-lg bg-surface px-2.5 py-2">
                                <span class="block text-ink-subtle">Buffer</span>
                                <span class="mt-0.5 block font-semibold text-ink">{{ $row['buffer_label'] ?? '' }}</span>
                            </span>
                        </span>

                        <span class="flex items-center justify-between gap-2 border-t border-line-faint pt-3">
                            <span class="text-[11px] text-ink-subtle">Sisa kuota bebas</span>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ ($row['headroom_tone'] ?? '') === 'danger' ? 'bg-danger-soft/40 text-danger' : 'bg-surface text-ink' }}">
                                {{ $row['headroom_label'] ?? '' }}
                            </span>
                        </span>

                        <span class="w-full rounded-full px-2 py-1 text-center text-[10px] font-semibold {{ ($row['status_tone'] ?? '') === 'caution' ? 'bg-warning-cream text-warning-caution' : 'bg-success-soft text-success-deep' }}">
                            {{ $row['status'] ?? '' }}
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Grafik mingguan + matriks alokasi kontrak B2B --}}
        <section class="grid items-stretch gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="flex min-h-[420px] flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <header class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-line-board">
                    <div class="flex min-w-0 flex-col gap-1">
                        <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Tren Mingguan Volume Permintaan vs Kapasitas Panen</h2>
                        <p class="text-xs leading-4 text-ink-body">Komparasi demand kontrak vs yield riil petani vs safety buffer reserve (Periode 8 Pekan: {{ $weeklyRows[0]['week'] ?? '' }} s/d {{ end($weeklyRows)['week'] ?? '' }}).</p>
                    </div>

                    <ul class="flex flex-wrap items-center gap-3">
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm bg-ink-strong" aria-hidden="true"></span>
                            <span class="gpa-micro-bold text-ink">Demand</span>
                        </li>
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm bg-accent-deep" aria-hidden="true"></span>
                            <span class="gpa-micro-bold text-ink">Yield Binaan</span>
                        </li>
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-sm bg-accent" aria-hidden="true"></span>
                            <span class="gpa-micro-bold text-ink">Reserve</span>
                        </li>
                    </ul>
                </header>

                <div class="flex min-h-[240px] flex-1 gap-2">
                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="relative min-h-0 flex-1">
                            @foreach ($weeklyChartTicks as $tick)
                                <div class="absolute inset-x-0 border-t border-line-board/60"
                                    style="top: {{ 100 - $tick }}%" aria-hidden="true"></div>
                            @endforeach

                            <div class="absolute inset-0 flex items-stretch gap-2">
                                @foreach ($weeklyRows as $row)
                                    @php
                                        $yieldTons = round($row['total'] * $row['yield'] / 100, 2);
                                        $reserveTons = round($row['total'] * $row['reserve'] / 100, 2);
                                        $demandTons = round($row['total'] - $yieldTons - $reserveTons, 2);
                                        $barPercent = round(($row['total'] / $maxWeekly) * 100, 1);
                                        $isForecast = $row['status'] === 'forecast';
                                        $isRunning = $row['status'] === 'running';
                                    @endphp
                                    <div class="relative min-w-0 flex-1">
                                        <button type="button" @click="inspectWeek(@js($row))"
                                            class="absolute inset-x-0 bottom-0 mx-auto flex w-full max-w-10 flex-col justify-end overflow-hidden rounded-t bg-surface-track {{ $isForecast ? 'opacity-70' : '' }} {{ $isRunning ? 'shadow-[0_0_0_2px_#aabd06]' : '' }}"
                                            style="height: {{ $barPercent }}%"
                                            title="{{ $isForecast ? '~' : '' }}{{ number_format($row['total'], 1, ',', '.') }} ton">
                                            <span class="w-full {{ $isForecast ? 'bg-accent/60' : 'bg-accent' }}"
                                                style="height: {{ $row['total'] > 0 ? round($reserveTons / $row['total'] * 100, 2) : 0 }}%"></span>
                                            <span class="w-full {{ $isForecast ? 'bg-accent-deep/60' : 'bg-accent-deep' }}"
                                                style="height: {{ $row['total'] > 0 ? round($yieldTons / $row['total'] * 100, 2) : 0 }}%"></span>
                                            <span class="w-full {{ $isForecast ? 'bg-ink-strong/60' : 'bg-ink-strong' }}"
                                                style="height: {{ $row['total'] > 0 ? round($demandTons / $row['total'] * 100, 2) : 0 }}%"></span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex h-6 shrink-0 items-end gap-2">
                            @foreach ($weeklyRows as $row)
                                @php
                                    $isForecast = $row['status'] === 'forecast';
                                    $isRunning = $row['status'] === 'running';
                                @endphp
                                <span class="min-w-0 flex-1 truncate text-center text-[9px] font-bold leading-3 tracking-[1.08px] font-mono {{ $isRunning ? 'text-ink' : 'text-success-deep' }}"
                                    title="{{ $row['week'] }}{{ $isRunning ? ' [AKTIF]' : ($isForecast ? ' (EST)' : '') }}">
                                    {{ $row['week'] }}{{ $isRunning ? ' [AKTIF]' : ($isForecast ? ' (EST)' : '') }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    @if ($weeklyRows)
                        <div class="flex w-10 shrink-0 flex-col">
                            <div class="relative min-h-0 flex-1">
                                @foreach ($weeklyChartTicks as $tick)
                                    @php
                                        $tickValue = $maxWeekly * $tick / 100;
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

                <div class="flex flex-col gap-2">
                    <p class="gpa-micro-bold text-ink">Puncak Demand: {{ $peakWeek ? ($peakWeek['week'] . ' (' . number_format($peakWeek['total'], 0, '.', '') . ' T)') : 'â€”' }}</p>
                    <p class="gpa-micro-bold text-ink">SUMBER DATA: DATABASE NYATA KOPERASI GPA</p>
                </div>
            </div>

            <div class="flex h-full flex-col gap-4 rounded-2xl bg-surface p-4 shadow-card sm:p-5">
                <header class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Matriks Alokasi Kontrak B2B</h2>
                        <p class="mt-0.5 text-xs leading-4 text-ink-subtle">{{ count($allocationsList) }} mitra aktif</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-success-soft px-2.5 py-1 text-[10px] font-semibold text-success-deep">
                        {{ $totalTon > 0 ? round(($totalCommitment / $totalTon) * 100, 1) : 0 }}% serap
                    </span>
                </header>

                <div class="flex flex-1 flex-col divide-y divide-line-faint">
                    @forelse ($allocationsList as $row)
                        <button type="button" @click="inspectPartner(@js($row))"
                            class="flex items-center justify-between gap-3 py-3 text-left transition-colors first:pt-0 last:pb-0 hover:bg-surface-shell focus:outline-none focus-visible:ring-2 focus-visible:ring-success-deep">
                            <span class="flex min-w-0 flex-col gap-1">
                                <span class="truncate font-sans text-sm font-semibold leading-5 text-ink">{{ $row['name'] }}</span>
                                <span class="truncate text-[11px] leading-4 text-ink-subtle">{{ $row['sentra'] }}</span>
                                <span class="w-fit rounded-full bg-surface-shell px-2 py-0.5 text-[10px] font-medium text-ink-body">
                                    {{ $row['priority'] }}
                                </span>
                            </span>
                            <span class="shrink-0 text-right">
                                <span class="block font-mono text-sm font-bold leading-5 text-ink">{{ number_format($row['commitment'], 2, '.', '') }}</span>
                                <span class="block text-[10px] leading-4 text-ink-subtle">ton / minggu</span>
                                <x-gpa.icon name="chevron-right" class="ml-auto mt-1 h-3 w-3 text-ink-subtle" />
                            </span>
                        </button>
                    @empty
                        <p class="py-6 text-center text-xs text-ink-body">Belum ada komitmen kontrak B2B aktif dalam sistem.</p>
                    @endforelse
                </div>

                <footer class="flex items-center justify-between gap-3 rounded-xl bg-surface-shell px-3 py-2.5">
                    <span class="text-[11px] font-medium text-ink-subtle">Total komitmen</span>
                    <span class="text-right text-sm font-bold text-ink">
                        {{ number_format($totalCommitment, 2, '.', '') }} <span class="text-[10px] font-medium text-ink-subtle">ton / minggu</span>
                    </span>
                </footer>
            </div>
        </section>
    </div>
@endsection
