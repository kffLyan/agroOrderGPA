@extends('layouts.coordinator')

@section('title', 'Rencana Panen & Persiapan Pesanan')

@section('content')
    @php
        $baseDone = $siapKirimCount - collect($queueRows)->where('stage', 4)->count();
        $baseWeighKg = $totalKg - collect($queueRows)->where('stage', '>=', 3)->sum('est_kg');
        $queueStages = [
            1 => ['chip' => 'TAHAP 1: TRIMMING', 'tone' => 'pill'],
            2 => ['chip' => 'TAHAP 2: SORTIR & QC', 'tone' => 'pill'],
            3 => ['chip' => 'TAHAP 3: MENUNGGU TIMBANGAN', 'tone' => 'accent'],
            4 => ['chip' => 'SIAP TIMBANG', 'tone' => 'accent-soft'],
        ];
        $qcSamples = $qcSamples ?? [];
        $avgTrimValue = $avgTrimValue ?? '0.00%';
    @endphp

    <div class="space-y-6"
        x-data="coordinatorHarvestPlan(@js($queueRows), @js($queueStages), @js([
            'tab_prefix' => 'WORKSTATION',
            'barcode_prefix' => 'KRAT-GPA-SBG-' . now()->format('m') . '-' . now()->year,
            'cta' => ['label' => 'TRANSFER KE MEJA TIMBANGAN NETTO >>'],
        ]), @js([
            'subtitle' => 'Sampel acak dari batch aktif (' . now()->format('d M Y') . ')',
            'avg_value' => $avgTrimValue,
            'samples' => $qcSamples,
        ]), @js([
            'totalBatches' => $totalOrders,
            'baseDone' => $baseDone,
            'baseWeighKg' => $baseWeighKg,
        ]))">
        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="package" class="h-4 w-4 text-success-deep" />
                    PERSIAPAN PESANAN &amp; PACKING COLD-CHAIN
                </p>
                <h1 class="mt-2 font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                    Sortir, Sanitasi Krat &amp; Pre-Cooling
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-5 text-ink-body">
                    Hard gate kualitas wajib tuntas sebelum batch boleh masuk antrean timbangan netto dan dokumen dispatch.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-3 text-ink outline outline-1 outline-line-board shadow-sub">
                    <x-gpa.icon name="thermometer" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-meta-lg font-bold">Pre-Cooling +3.8C</span>
                </span>
                <span
                    class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-ink px-3 text-accent shadow-sub outline outline-1 outline-brand-line">
                    <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0" />
                    <span class="gpa-meta-lg font-bold">Cold-Chain Aktif</span>
                </span>
            </div>
        </section>

        {{-- Rule 04 & 09 mandate --}}
        <section class="rounded-xl border border-l-4 border-brand bg-surface p-4 shadow-sub">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft/60 pb-2">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 shrink-0 text-brand">
                        <x-gpa.icon name="shield" class="h-4 w-4" />
                    </span>
                    <h2 class="gpa-micro-bold text-ink-strong">MANDAT ALUR SORTIR DAN PRE-COOLING SEBELUM DOKUMEN DISPATCH (PRD RULE 04 &amp; 09)</h2>
                </div>
                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-brand px-2.5 py-0.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                    <span class="gpa-micro-bold text-accent">MANDATORY ENFORCED</span>
                </span>
            </header>

            <div class="grid gap-4 pt-2 sm:grid-cols-2 2xl:grid-cols-4">
                <article class="flex flex-col justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-success-deep">TAHAP 01</p>
                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        </div>
                        <h3 class="text-sm font-bold leading-5 text-ink">Inspeksi Visual Sortir</h3>
                        <p class="text-xs leading-5 text-ink-body">Cek ketat kebusukan &gt;2%, eliminasi daun kuning, ulat, telur hama, dan residu lumpur tanah.</p>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        <p class="gpa-note text-ink-quiet">SOP: MAX AFKIR 2.0%</p>
                        <p class="gpa-micro-bold text-success-deep">GRADE A/B FILTER</p>
                    </div>
                </article>

                <article class="flex flex-col justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-success-deep">TAHAP 02</p>
                            <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        </div>
                        <h3 class="text-sm font-bold leading-5 text-ink">Pembersihan &amp; Trimming</h3>
                        <p class="text-xs leading-5 text-ink-body">Potong rata pangkal bonggol maksimal 1.5 cm; lap embun basah berlebih pencegahan pembusukan dingin.</p>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        <p class="gpa-note text-ink-quiet">BONGGOL: 1.0 - 1.5 CM</p>
                        <p class="gpa-micro-bold text-success-deep">DRY-WIPED</p>
                    </div>
                </article>

                <article class="flex flex-col justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-success-deep">TAHAP 03</p>
                            <x-gpa.icon name="package" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        </div>
                        <h3 class="text-sm font-bold leading-5 text-ink">Krat Cold-Chain Steril</h3>
                        <p class="text-xs leading-5 text-ink-body">Wadah berlubang standar tara 2.00 kg sanitasi food-grade desinfeksi klorin 50ppm suhu pra-isi +4C.</p>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        <p class="gpa-note text-ink-quiet">TARA STANDARD: 2.0 KG</p>
                        <p class="gpa-micro-bold text-success-deep">SANITIZED &lt;&lt; 5C</p>
                    </div>
                </article>

                <article class="flex flex-col justify-between gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-success-deep">TAHAP 04</p>
                            <x-gpa.icon name="scale" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        </div>
                        <h3 class="text-sm font-bold leading-5 text-ink">Timbangan Netto Sah</h3>
                        <p class="text-xs leading-5 text-ink-body">Penerbitan barcode batch timbang digital terintegrasi ledger dan segel strapping pallet berlapis.</p>
                    </div>
                    <div class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                        <p class="gpa-note text-ink-quiet">RFID / BARCODE LOT</p>
                        <p class="gpa-micro-bold text-success-deep">DISPATCH READY</p>
                    </div>
                </article>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Card 1: Total PO Dalam Packing --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">TOTAL PO DALAM PACKING</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-shell">
                        <x-gpa.icon name="package" class="block h-4 w-4 text-ink" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink">{{ $totalOrders }}</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">Batch Aktif</span>
                </p>
                <div class="mt-auto space-y-2 pt-2">
                    <p class="gpa-note text-ink-quiet">Order aktif di sistem</p>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-pill" role="presentation">
                        <div class="h-full rounded-full bg-ink" :style="`width: ${progressFor('packing', {{ min(100, (int) $pctDone) }})}%`"></div>
                    </div>
                </div>
            </article>

            {{-- Card 2: Selesai Sortir & Packing --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">SELESAI SORTIR &amp; PACKING</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/40">
                        <x-gpa.icon name="badge-check" class="block h-4 w-4 text-success-deep" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink" x-text="doneCount()">{{ $siapKirimCount }}</span>
                    <span class="gpa-meta-lg font-bold text-success-deep" x-text="doneLabel()">Selesai ({{ $pctDone }}%)</span>
                </p>
                <div class="mt-auto space-y-2 pt-2">
                    <p class="gpa-note text-ink-body" x-text="weighNote()">{{ number_format($totalKg, 2, '.', ',') }} kg menuju antrean timbang</p>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-pill" role="presentation">
                        <div class="h-full rounded-full bg-success-deep" :style="`width: ${progressFor('sorted', {{ min(100, (int) $pctDone) }})}%`"></div>
                    </div>
                </div>
            </article>

            {{-- Card 3: Ketersediaan Krat Steril --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">KETERSEDIAAN KRAT STERIL</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-shell">
                        <x-gpa.icon name="package" class="block h-4 w-4 text-ink" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink">280</span>
                    <span class="gpa-meta-lg font-bold text-ink-body">/ 350 Krat</span>
                </p>
                <div class="mt-auto space-y-2 pt-2">
                    <p class="gpa-note text-ink-quiet">Tara @2.0 kg - Staging Bay A</p>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-pill" role="presentation">
                        <div class="h-full rounded-full bg-accent-edge" :style="`width: ${progressFor('crates', 80)}%`"></div>
                    </div>
                </div>
            </article>

            {{-- Card 4: Suhu Ruang Pre-Cooling --}}
            <article class="gpa-panel flex flex-col gap-2 p-4">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="max-w-[11rem] gpa-micro-bold leading-3 text-ink-body">SUHU RUANG PRE-COOLING</h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/40">
                        <x-gpa.icon name="thermometer" class="block h-4 w-4 text-ink" />
                    </span>
                </div>
                <p class="flex items-baseline gap-1.5">
                    <span class="font-mono text-3xl font-bold leading-10 text-ink">+3.8C</span>
                    <span class="rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-ink">NORMAL</span>
                </p>
                <div class="mt-auto space-y-2 pt-2">
                    <p class="gpa-note text-ink-quiet">ISO 22000 Standard (+2C ~ +5C)</p>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-pill" role="presentation">
                        <div class="h-full rounded-full bg-accent" :style="`width: ${progressFor('temperature', 60)}%`"></div>
                    </div>
                </div>
            </article>
        </section>

        {{-- Packing queue + active workstation --}}
        <section class="grid gap-4 2xl:grid-cols-2">
            {{-- Queue --}}
            <div class="gpa-panel flex flex-col gap-4 p-4">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft/60 pb-3">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">Antrean Batch Packing Berjalan</h2>
                        <p class="mt-1 text-xs leading-4 text-ink-body">Staging zone meja pembersihan, sortir mutu, dan pelabelan peti</p>
                    </div>
                    <button type="button" @click="refreshQueue()"
                        class="shrink-0 rounded bg-surface-shell px-2 py-0.5 gpa-note text-ink-body outline outline-1 outline-line-board/50 transition-colors hover:bg-surface-muted">
                        LIVE REFRESH: 5S
                    </button>
                </header>

                <div class="gpa-scroll-x -mx-4 overflow-x-auto">
                    <table class="w-full min-w-[46rem] border-collapse">
                        <caption class="sr-only">Antrean Batch Packing Berjalan</caption>
                        <thead>
                            <tr class="border-y border-line-board/40 bg-surface-shell">
                                <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink-body text-left">BATCH &amp; PO ID</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink-body text-left">BUYER &amp; KOMODITAS</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink-body text-left">KRAT &amp; BOBOT</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink-body text-left">STATUS TAHAP</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold text-ink-body text-right">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($queueRows as $row)
                                @php($rowPo = \Illuminate\Support\Js::from($row['po']))
                                <tr class="border-b border-line-hair/70" :class="isActive({{ $rowPo }}) ? 'bg-accent/10' : ''">
                                    <th scope="row" class="border-l-4 border-transparent px-3 py-3 text-left align-top"
                                        :class="isActive({{ $rowPo }}) ? '!border-brand' : ''">
                                        <span class="block gpa-meta font-bold text-ink">#{{ $row['po'] }}</span>
                                        <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $row['bay'] }} // {{ $row['station'] }}</span>
                                    </th>
                                    <td class="px-3 py-3 align-top">
                                        <span class="block text-xs font-bold leading-4 text-ink">{{ $row['buyer'] }}</span>
                                        <span @class([
                                            'mt-0.5 block gpa-note',
                                            'text-success-deep' => $row['commodity_tone'] === 'success',
                                            'text-ink-body' => $row['commodity_tone'] === 'body',
                                        ]) x-text="commodityLabel({{ $rowPo }})">{{ trim($row['commodity'].' '.($row['commodity_note'] ?? '')) }}</span>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="block gpa-meta-lg font-bold text-ink">{{ $row['krat_done'] }}/{{ $row['krat_total'] }} Krat</span>
                                        <span class="mt-0.5 block gpa-note text-ink-body">Est: {{ number_format($row['est_kg'], 2, '.', ',') }} kg</span>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="inline-flex items-center gap-1.5 rounded px-2 py-1 outline outline-1 gpa-micro-bold"
                                            :class="stageTone({{ $rowPo }})">
                                            <span class="h-1.5 w-1.5 rounded-full" :class="stageDot({{ $rowPo }})"
                                                 aria-hidden="true"></span>
                                            <span x-text="stageLabel({{ $rowPo }})">{{ $queueStages[$row['stage']]['chip'] }}</span>
                                        </span>
                                        <span class="mt-1 block gpa-note" x-show="afkirOf({{ $rowPo }})"
                                            :class="afkirTone({{ $rowPo }})" x-text="afkirOf({{ $rowPo }})">{{ $row['afkir'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-right align-top">
                                        <button type="button" @click="select({{ $rowPo }})"
                                            class="inline-flex h-8 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors"
                                            :class="actionVariant({{ $rowPo }})">
                                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 shrink-0" ::class="actionIconTone({{ $rowPo }})" />
                                            <span x-text="actionLabel({{ $rowPo }})">{{ $row['action_label'] }}</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <footer class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <p class="gpa-note text-ink-body">MEMPERLIHATKAN {{ count($queueRows) }} ORDER OPERASIONAL</p>
                    <p class="gpa-note text-ink">STANDARD PACKING OPERATIONAL SPEED: 18 KRAT / JAM</p>
                </footer>
            </div>

            {{-- Active workstation --}}
            <div class="relative flex flex-col gap-4 overflow-hidden rounded-2xl bg-surface p-4 shadow-card">
                <span class="absolute right-4 top-0 rounded-b-lg bg-brand px-3 py-1 gpa-micro-bold text-accent"
                    x-text="tabLabel()">WORKSTATION MEJA #{{ substr($queueRows[0]['station'] ?? '01', -2) }}</span>

                <header class="space-y-1 pt-6">
                    <p class="gpa-micro-bold text-success-deep">DETAIL WORKSTATION AKTIF</p>
                    <h2 class="gpa-section-title text-ink" x-text="buyer()">{{ $queueRows[0]['buyer'] ?? '' }}</h2>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <span class="gpa-meta font-bold text-ink" x-text="poLabel()">#{{ $queueRows[0]['po'] ?? '' }}</span>
                        <span class="text-line-board" aria-hidden="true">&middot;</span>
                        <span class="gpa-meta font-medium text-ink-body" x-text="commodityGrade()">Komoditas: {{ trim(($queueRows[0]['commodity'] ?? '').' '.($queueRows[0]['commodity_note'] ?? '')) }}</span>
                    </div>
                    <p class="inline-flex rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 outline-line-board/40"
                        x-text="targetLabel()">Target: {{ $queueRows[0]['krat_total'] ?? 0 }} Krat / {{ number_format($queueRows[0]['est_kg'] ?? 0, 2, '.', ',') }} kg Gross Target</p>
                </header>

                <div class="space-y-2">
                    {{-- Step 1 --}}
                    <article class="space-y-1.5 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="flex items-center gap-1.5 gpa-meta font-bold text-ink">
                                <x-gpa.icon name="qrcode" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                                1. Label Krat Terpasang (Barcode/RFID)
                            </h3>
                            <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-ink">TERVALIDASI</span>
                        </div>
                        <p class="flex flex-wrap items-center gap-1.5">
                            <span class="gpa-meta text-ink-body">Prefix:</span>
                            <span class="gpa-meta font-bold text-ink" x-text="barcodePrefix()">KRAT-GPA-SBG-{{ now()->format('m') }}-{{ now()->year }}-{{ substr($queueRows[0]['po'] ?? '', -4) }}-[01..{{ $queueRows[0]['krat_total'] ?? 0 }}]</span>
                        </p>
                        <p class="gpa-note leading-4 text-ink-quiet">Warna: Kuning Cold-Chain (Food Grade) - Tara: 2.00 kg / Peti</p>
                    </article>

                    {{-- Step 2 --}}
                    <article class="space-y-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="flex items-center gap-1.5 gpa-meta font-bold text-ink">
                                <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                                2. Kebersihan Wadah (HACCP Food-Grade)
                            </h3>
                            <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-ink">LOLOS</span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-note text-ink-body">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0 text-success-deep" />
                            Bebas residu jamur/tanah
                        </p>
                        <p class="flex items-center gap-1.5 gpa-note text-ink-body">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0 text-success-deep" />
                            Disinfektan klorin 50ppm
                        </p>
                        <p class="gpa-note leading-4 text-success-deep">Suhu peti sebelum diisi: +4.1C (maks 5.0C)</p>
                    </article>

                    {{-- Step 3 --}}
                    <article class="space-y-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/40">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="flex items-center gap-1.5 gpa-meta font-bold text-ink">
                                <x-gpa.icon name="package" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                                3. Pembagian Lot &amp; Konfigurasi Pallet
                            </h3>
                            <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-ink">TERPASANG</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded bg-surface p-2 outline outline-1 outline-line-hair" x-show="palletCount() >= 1">
                                <p class="gpa-micro-bold text-ink" x-text="`PALLET #01`">PALLET #01</p>
                                <p class="gpa-note font-semibold text-ink-body" x-text="palletLabel(1)">20 Krat (Tier 4x5)</p>
                            </div>
                            <div class="rounded bg-surface p-2 outline outline-1 outline-line-hair" x-show="palletCount() >= 2">
                                <p class="gpa-micro-bold text-ink" x-text="`PALLET #02`">PALLET #02</p>
                                <p class="gpa-note font-semibold text-ink-body" x-text="palletLabel(2)">20 Krat (Tier 4x5)</p>
                            </div>
                        </div>
                        <p class="gpa-note leading-4 text-ink-quiet">Strapping pita hitam 2 titik &amp; cover termal foil terpasang rapi</p>
                    </article>

                    <div class="space-y-1 rounded-lg bg-surface-track p-2 outline outline-1 outline-line-board/30">
                        <p class="gpa-micro-bold text-ink-quiet">CATATAN KHUSUS PENGAWAS QC:</p>
                        <p class="text-xs italic leading-4 text-ink">"Pangkal daun dipotong rata 1.0 cm, kondisi selada renyah, embun permukaan telah dilap bersih, siap ditimbang netto resmi."</p>
                    </div>
                </div>

                <footer class="space-y-3 border-t border-line-board/40 pt-4">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <button type="button" @click="stationAction('print-label')"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors bg-surface text-ink outline outline-1 outline-line-board/70 hover:bg-surface-muted">
                            <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-ink" />
                            Cetak Label Krat
                        </button>
                        <button type="button" @click="stationAction('flag-afkir')"
                            class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 gpa-meta-lg font-bold transition-colors bg-surface text-warning outline outline-1 outline-warning/60 hover:bg-warning-cream">
                            <x-gpa.icon name="alert-triangle" class="h-3.5 w-3.5 shrink-0 text-warning" />
                            Tandai Afkir Tinggi
                        </button>
                    </div>

                    <button type="button" @click="stationAction('transfer')"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 shadow-pop outline outline-1 outline-brand-line transition-colors hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-surface-disabled disabled:text-ink-quiet disabled:outline-line-board"
                        :disabled="!canTransfer()">
                        <x-gpa.icon name="scale" class="h-4 w-4 shrink-0 text-accent" />
                        <span class="gpa-label font-bold text-accent" x-text="ctaLabel()">TRANSFER KE MEJA TIMBANGAN NETTO &gt;&gt;</span>
                    </button>
                </footer>
            </div>
        </section>

        {{-- QC sampling log --}}
        <section class="gpa-panel flex flex-col gap-4 p-4">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft/60 pb-3">
                <div class="min-w-0">
                    <h2 class="gpa-section-title flex items-center gap-2 font-inter text-ink">
                        <x-gpa.icon name="clipboard" class="h-4 w-4 shrink-0 text-success-deep" />
                        Log Uji Petik Mutu &amp; Sortir Afkir Meja Grading
                    </h2>
                    <p class="mt-1 font-inter text-xs leading-4 text-ink-body" x-text="qcSubtitle()">Sampel acak dari batch aktif ({{ now()->format('d M Y') }})</p>
                </div>

                <div class="flex shrink-0 items-center gap-2 rounded-lg bg-accent/30 px-3 py-1.5 outline outline-1 outline-success-deep">
                    <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    <div>
                        <p class="gpa-micro-bold text-success-deep">RATA-RATA SUSUT TRIMMING</p>
                        <p class="flex items-baseline gap-1.5">
                            <span class="font-mono text-sm font-bold text-ink" x-text="avgTrim()">{{ $avgTrimValue }}</span>
                            <span class="font-inter text-xs text-ink-body">(Toleransi SOP: Max 2.00%)</span>
                        </p>
                    </div>
                </div>
            </header>

            <div class="grid gap-4 pt-1 md:grid-cols-3">
                @forelse ($qcSamples as $sample)
                    @php($net = $sample['gross'] - $sample['tara'] - $sample['trim'])
                    <article class="flex cursor-pointer flex-col justify-between gap-3 rounded-lg bg-surface-shell p-4 outline outline-1 outline-line-board/40 transition-colors hover:bg-surface-muted"
                        @click="inspectSample(@js($sample['krat']))">
                        <header class="flex items-center justify-between gap-2 border-b border-line-soft/60 pb-2">
                            <h3 class="gpa-meta font-bold text-ink">{{ $sample['krat'] }}</h3>
                            <span class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-ink">{{ $sample['status'] }}</span>
                        </header>

                        <div class="space-y-1">
                            <p class="flex items-center justify-between gap-2">
                                <span class="gpa-meta text-ink-body">Bruto Awal:</span>
                                <span class="gpa-meta font-bold text-ink">{{ number_format($sample['gross'], 2, '.', ',') }} kg</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="gpa-meta text-ink-body">Tara Krat Steril:</span>
                                <span class="gpa-meta font-semibold text-ink-body">{{ number_format($sample['tara'], 2, '.', ',') }} kg</span>
                            </p>
                            <p class="flex items-center justify-between gap-2">
                                <span class="gpa-meta text-warning">Trimming Afkir:</span>
                                <span class="gpa-meta font-bold text-warning">{{ number_format($sample['trim'], 2, '.', ',') }} kg ({{ number_format($sample['trim_percent'], 2, '.', ',') }}%)</span>
                            </p>
                            <p class="flex items-center justify-between gap-2 border-t border-line-soft/60 pt-2">
                                <span class="gpa-meta font-bold text-ink">Netto Bersih Siap:</span>
                                <span class="font-mono text-sm font-bold text-success-deep">{{ number_format($net, 2, '.', ',') }} kg</span>
                            </p>
                        </div>

                        <footer class="border-t border-line-hair pt-2 gpa-note text-ink-quiet">
                            Inspektor: {{ auth()->user()?->name ?? 'QC Officer' }} <span aria-hidden="true">&middot;</span> Sensor Calibrated
                        </footer>
                    </article>
                @empty
                    <div class="col-span-full rounded-lg bg-surface-shell p-6 text-center text-sm font-medium text-ink-quiet">
                        Belum ada sampel QC aktif pada batch panen hari ini.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
