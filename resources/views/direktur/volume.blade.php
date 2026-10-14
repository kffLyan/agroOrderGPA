@extends('layouts.direktur')

@section('title', 'Volume Komoditas // Stok Panen & Kapasitas Pasokan')

@section('content')
    @php
        $weeklyRows = [
            ['week' => 'W40', 'total' => 108.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W41', 'total' => 120.0, 'demand' => 68, 'yield' => 22, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W42', 'total' => 125.0, 'demand' => 72, 'yield' => 18, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W43', 'total' => 130.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'closed'],
            ['week' => 'W44', 'total' => 135.0, 'demand' => 74, 'yield' => 18, 'reserve' => 8,  'status' => 'running'],
            ['week' => 'W45', 'total' => 124.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'forecast'],
            ['week' => 'W46', 'total' => 127.0, 'demand' => 72, 'yield' => 18, 'reserve' => 10, 'status' => 'forecast'],
            ['week' => 'W47', 'total' => 130.0, 'demand' => 70, 'yield' => 20, 'reserve' => 10, 'status' => 'forecast'],
        ];

        $maxWeekly = max(array_column($weeklyRows, 'total'));
        $peakWeek = $weeklyRows[array_search($maxWeekly, array_column($weeklyRows, 'total'))];

        $allocationsList = [
            ['name' => 'PT Aerofood ACS',            'sentra' => 'CIANJUR (AGR-01)',     'commitment' => 28.50, 'priority' => 'PRIORITAS UTAMA'],
            ['name' => 'Royal Ambarrukmo',           'sentra' => 'CIWIDEY (AGR-04)',     'commitment' => 12.20, 'priority' => 'KONTRAK BULANAN'],
            ['name' => 'PT Segar Makmur Ritelindo',  'sentra' => 'LEMBANG (AGR-02)',     'commitment' => 18.40, 'priority' => 'DISTRIBUSI NASIONAL'],
            ['name' => 'PT Boga Rasa Kulina Prima',  'sentra' => 'PANGALENGAN (AGR-03)', 'commitment' => 14.60, 'priority' => 'FOOD SERVICE QSR'],
        ];

        $totalCommitment = array_sum(array_column($allocationsList, 'commitment'));
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorVolume(@js($commodityRows ?? []), @js($weeklyRows), @js($allocationsList))">

        {{-- Page header + range badge + aksi rekonsiliasi --}}
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Monitoring Volume Komoditas, Stok Panen<br>&amp; Kapasitas Pasokan
                </h1>
            </div>

            <div class="flex shrink-0 flex-col items-stretch gap-3">
                <span
                    class="inline-flex h-10 items-center gap-2 rounded-lg bg-surface px-3 outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="gpa-meta text-success-deep">RANGE:</span>
                    <span class="gpa-meta font-bold text-ink">W{{ date('W') - 4 }} - W{{ date('W') }} MTD</span>
                    <span class="h-3 w-3 rounded-sm bg-success-deep" aria-hidden="true"></span>
                </span>

                <button type="button" @click="reconcile()"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-accent px-4 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                    <x-gpa.icon name="scale" class="h-4 w-4 shrink-0" />
                    <span class="text-sm font-semibold leading-5 text-ink font-inter">
                        REKONSILIASI NERACA
                    </span>
                </button>
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
                    <h2 class="gpa-micro-bold uppercase text-success-deep">SUMBER PASOKAN (RULE 02)</h2>
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
                    <h2 class="gpa-micro-bold uppercase text-success-deep">SAFETY STOCK BUFFER (RULE 03)</h2>
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
        <section class="flex min-h-[420px] w-full flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board pb-3">
                <h2 class="font-inter text-lg font-semibold leading-6 text-ink">Neraca Volume &amp; Komposisi 5 Komoditas Inti</h2>
            </header>

            <div class="grid flex-1 grid-cols-1 gap-4 py-1 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($commodityRows ?? [] as $row)
                    <button type="button" @click="inspectCommodity(@js($row))"
                        class="flex h-full min-w-0 flex-col justify-between gap-6 rounded-xl bg-surface-shell p-4 text-left outline outline-1 -outline-offset-1 outline-line-board/60 transition-colors hover:bg-surface-muted">
                        <span class="flex flex-col gap-1">
                            <span class="flex items-center justify-between gap-2">
                                <span
                                    class="rounded px-1.5 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold text-ink outline outline-1 -outline-offset-1 outline-line-board bg-surface-shell">
                                    {{ $row['code'] ?? '' }}
                                </span>
                                <span class="text-[11px] font-bold leading-[14px] tracking-[0.88px] font-mono {{ ($row['share_tone'] ?? '') === 'caution' ? 'text-warning-caution' : 'text-success-deep' }}">
                                    {{ $row['share_label'] ?? '' }}
                                </span>
                            </span>

                            <span class="block pt-1.5 font-sans text-lg font-semibold leading-6 text-ink">
                                {{ $row['name'] ?? '' }}
                            </span>
                            <span class="block font-sans text-xs leading-4 text-ink-body">{{ $row['grade'] ?? '' }}</span>

                            <span class="mt-5 flex flex-col gap-2 border-y border-line-board/60 py-2">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs leading-4 text-ink-body">Realisasi:</span>
                                    <span class="text-right font-mono text-base font-bold leading-6 text-ink">
                                        {{ $row['value_label'] ?? '' }}<br>Ton
                                    </span>
                                </span>

                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs leading-4 text-success-deep">&bull; Binaan:</span>
                                    <span class="font-mono text-xs leading-4 text-ink">{{ $row['farmer_label'] ?? '' }}</span>
                                </span>

                                <span class="flex items-center justify-between gap-2">
                                    <span class="font-mono text-xs leading-4 text-success-deep">&bull; Buffer:</span>
                                    <span class="font-mono text-xs leading-4 text-ink">{{ $row['buffer_label'] ?? '' }}</span>
                                </span>

                                <span
                                    class="flex items-center justify-between gap-2 border-t border-surface-track pt-1">
                                    <span class="font-mono text-xs font-medium leading-4 text-ink-body">
                                        Sisa Kuota<br>Bebas:
                                    </span>
                                    <span
                                        class="rounded px-1.5 py-0.5 font-mono text-xs font-bold leading-4 {{ ($row['headroom_tone'] ?? '') === 'danger' ? 'bg-danger-soft/40 text-danger' : 'bg-surface-shell text-ink' }}">
                                        {{ $row['headroom_label'] ?? '' }}
                                    </span>
                                </span>
                            </span>
                        </span>

                        <span
                            class="w-full rounded px-1.5 py-1 text-center text-[9px] font-bold leading-3 gpa-micro-bold {{ ($row['status_tone'] ?? '') === 'caution' ? 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution' : 'bg-accent text-ink-strong outline outline-1 -outline-offset-1 outline-accent-deep' }}">
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
                        <h2 class="font-sans text-lg font-semibold leading-6 text-ink">Tren Mingguan Volume vs Kapasitas Panen (W40 - W47)</h2>
                        <p class="text-xs leading-4 text-ink-body">Komparasi demand kontrak vs yield riil petani vs safety buffer reserve.</p>
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

                <div class="flex items-end gap-3 border-b border-line-board px-2 pt-6 pb-2">
                    @foreach ($weeklyRows as $row)
                        @php
                            $yieldTons = round($row['total'] * $row['yield'] / 100, 2);
                            $reserveTons = round($row['total'] * $row['reserve'] / 100, 2);
                            $demandTons = round($row['total'] - $yieldTons - $reserveTons, 2);
                            $barPercent = round(($row['total'] / $maxWeekly) * 100, 1);
                            $isForecast = $row['status'] === 'forecast';
                            $isRunning = $row['status'] === 'running';
                        @endphp
                        <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                            <span
                                class="rounded px-1 py-0.5 text-[9px] font-bold leading-3 gpa-micro-bold {{ $isRunning ? 'bg-accent text-ink-strong' : ($isForecast ? 'text-success-deep' : 'text-ink') }}">
                                {{ $isForecast ? '~' : '' }}{{ number_format($row['total'], 0, '.', '') }} T
                            </span>

                            <div class="flex h-52 w-full max-w-[42px] items-end">
                                <button type="button" @click="inspectWeek(@js($row))"
                                    class="flex w-full flex-col justify-end overflow-hidden rounded-t bg-surface-track {{ $isForecast ? 'opacity-70' : '' }} {{ $isRunning ? 'shadow-[0_0_0_2px_#aabd06]' : '' }}"
                                    style="height: {{ $barPercent }}%">
                                    <span class="w-full {{ $isForecast ? 'bg-accent/60' : 'bg-accent' }}"
                                        style="height: {{ round($reserveTons / $row['total'] * 100, 2) }}%"></span>
                                    <span class="w-full {{ $isForecast ? 'bg-accent-deep/60' : 'bg-accent-deep' }}"
                                        style="height: {{ round($yieldTons / $row['total'] * 100, 2) }}%"></span>
                                    <span class="w-full {{ $isForecast ? 'bg-ink-strong/60' : 'bg-ink-strong' }}"
                                        style="height: {{ round($demandTons / $row['total'] * 100, 2) }}%"></span>
                                </button>
                            </div>

                            <span
                                class="text-[9px] font-bold leading-3 tracking-[1.08px] font-mono {{ $isRunning ? 'text-ink' : 'text-success-deep' }}">
                                {{ $row['week'] }}{{ $isRunning ? ' [AKTIF]' : ($isForecast ? ' (EST)' : '') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-2">
                    <p class="gpa-micro-bold text-ink">Puncak Demand: {{ $peakWeek['week'] }} ({{ number_format($peakWeek['total'], 0, '.', '') }} T)</p>
                    <p class="gpa-micro-bold text-ink">KONFIDENSI MODEL: 97.4%</p>
                </div>
            </div>

            <div class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-4">
                    <header class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-line-board">
                        <div class="flex min-w-0 flex-col gap-1">
                            <h2 class="font-sans text-lg font-semibold leading-6 text-ink">
                                Matriks Alokasi Kontrak B2B
                            </h2>
                        </div>

                        <span class="rounded bg-accent px-2 py-1 text-[9px] font-bold leading-3 gpa-micro-bold text-ink-strong">
                            {{ round(($totalCommitment / 80.80) * 100, 1) }}% SERAP
                        </span>
                    </header>

                    <div class="flex flex-col gap-3">
                        @foreach ($allocationsList as $row)
                            <button type="button" @click="inspectPartner(@js($row))"
                                class="flex items-center justify-between gap-3 rounded-xl bg-surface-shell p-3 text-left outline outline-1 -outline-offset-1 outline-line-board/60 transition-colors hover:bg-surface-muted">
                                <span class="flex min-w-0 flex-col gap-0.5">
                                    <span class="font-sans text-sm font-bold leading-5 text-ink">{{ $row['name'] }}</span>
                                    <span class="font-mono text-xs leading-4 text-success-deep">
                                        SENTRA PRODUKSI: {{ $row['sentra'] }}
                                    </span>
                                </span>

                                <span class="flex shrink-0 flex-col items-end gap-0.5">
                                    <span class="text-right font-mono text-sm font-bold leading-5 tracking-[0.28px] text-ink">
                                        {{ number_format($row['commitment'], 2, '.', '') }}
                                    </span>
                                    <span
                                        class="text-right font-mono text-xs font-bold leading-4 tracking-[0.28px] text-ink">
                                        Ton/mgg
                                    </span>
                                    <span class="text-right font-inter text-[9px] font-semibold leading-3 tracking-[1.08px] text-success-deep">
                                        {{ $row['priority'] }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <footer class="border-t border-line-board pt-3">
                    <div class="flex items-center justify-between gap-3 pt-3">
                        <span class="text-[11px] font-medium leading-[14px] tracking-[0.88px] font-mono text-success-deep">
                            TOTAL KOMITMEN 4 MITRA:
                        </span>
                        <span
                            class="rounded bg-accent/60 px-2 py-0.5 text-[11px] font-bold leading-[14px] tracking-[0.88px] font-mono text-ink outline outline-1 -outline-offset-1 outline-accent-deep">
                            {{ number_format($totalCommitment, 2, '.', '') }} Ton / Minggu
                        </span>
                    </div>
                </footer>
            </div>
        </section>
    </div>
@endsection
