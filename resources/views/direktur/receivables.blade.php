@extends('layouts.direktur')

@section('title', 'Piutang & Tagihan // Faktur Tempo & Risiko Kredit B2B')

@section('content')
    @php
        $stagesDef = [
            ['stage' => 'STAGE 01 (0-15 HARI)',      'status' => 'SANGAT AMAN', 'value' => $stage1Val, 'tone' => 'brand'],
            ['stage' => 'STAGE 02 (16-30 HARI)',     'status' => 'IN-SCHEDULE', 'value' => $stage2Val, 'tone' => 'success'],
            ['stage' => 'STAGE 03 (1-14 HARI LEWAT)', 'status' => 'WARNING',     'value' => $stage3Val, 'tone' => 'warning'],
            ['stage' => 'STAGE 04 (> 15 HARI OVERDUE)', 'status' => 'KRITIS',   'value' => $stage4Val, 'tone' => 'danger'],
        ];

        $agingTotal = array_sum(array_column($stagesDef, 'value'));
        $collectible = $agingTotal - $stagesDef[3]['value'];
        $lancar = $stage1Val + $stage2Val;
        $warning = $stage3Val;
        $kritis = $stage4Val;

        $mono = 'font-mono tracking-[0.88px]';
    @endphp

    <div class="flex flex-col gap-6 px-0 py-0"
        x-data="directorReceivables(@js($ledgerRows ?? []), {}, {}, {{ $activeContracts ?? 0 }})">

        {{-- ---------- Kop halaman ---------- --}}
        <header class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <h1 class="font-sans text-[28px] font-extrabold leading-9 tracking-[0.6px] text-ink sm:text-[32px] sm:leading-10">
                    Monitoring Piutang, Tagihan Tempo &amp; Manajemen Risiko Kredit Klien B2B
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded bg-surface-shell px-3 py-1 outline outline-1 -outline-offset-1 outline-line-board">
                    <x-gpa.icon name="calendar" class="h-3 w-3 text-ink-subtle" />
                    <span class="text-[11px] font-bold tracking-[0.88px] text-ink {{ $mono }}">
                        PERIODE: {{ strtoupper(now()->translatedFormat('F Y')) }}
                    </span>
                </span>
                <button type="button" @click="filterTopOnly()"
                    class="inline-flex items-center gap-1 rounded px-2.5 py-1 text-[12px] font-semibold text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="filter" class="h-3 w-3" />
                    Filter
                </button>
            </div>
        </header>

        {{-- ---------- Empat kartu KPI ---------- --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan risiko kredit">
            {{-- Card 1: Total Piutang B2B --}}
            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-[11px] font-semibold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                        TOTAL PIUTANG B2B
                    </h2>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-shell">
                        <x-gpa.icon name="invoice" class="h-3.5 w-3.5 text-ink" />
                    </span>
                </div>

                <p class="font-mono text-[28px] font-bold leading-[35px] text-ink">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</p>

                <div class="mt-auto flex items-center justify-between gap-3 border-t border-line-board pt-1">
                    <span class="inline-flex items-center gap-1 text-[12px] leading-4 text-ink-body">
                        {{ $activeContracts ?? 0 }} Klien Kontrak Aktif
                    </span>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-3 tracking-[1px] bg-accent text-ink">
                        100% TERMONITOR
                    </span>
                </div>
            </article>

            {{-- Card 2: Piutang Lancar --}}
            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-[11px] font-semibold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                        PIUTANG LANCAR (&lt; 15 HARI)
                    </h2>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/50">
                        <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 text-success-ink" />
                    </span>
                </div>

                <p class="font-mono text-[28px] font-bold leading-[35px] text-success-deep">Rp {{ number_format($lancar, 0, ',', '.') }}</p>

                <div class="mt-auto flex items-center justify-between gap-3 border-t border-line-board pt-1">
                    <span class="inline-flex items-center gap-1 text-[12px] leading-4 text-success-deep">
                        <span class="h-2 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
                        {{ round(($lancar / ($totalReceivable > 0 ? $totalReceivable : 1)) * 100, 1) }}% DARI TOTAL AR
                    </span>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-3 tracking-[1px] bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board">
                        NORMAL / AMAN
                    </span>
                </div>
            </article>

            {{-- Card 3: Warning Tempo --}}
            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-[11px] font-semibold uppercase leading-[14px] tracking-[0.55px] text-warning-caution {{ $mono }}">
                        WARNING TEMPO (&lt; 7 HARI)
                    </h2>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-soft">
                        <x-gpa.icon name="clock" class="h-3.5 w-3.5 text-warning-caution" />
                    </span>
                </div>

                <p class="font-mono text-[28px] font-bold leading-[35px] text-warning-caution">Rp {{ number_format($warning, 0, ',', '.') }}</p>

                <div class="mt-auto flex items-center justify-between gap-3 border-t border-line-board pt-1">
                    <span class="inline-flex items-center gap-1 text-[12px] leading-4 text-warning-caution">
                        <span class="h-2 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
                        {{ round(($warning / ($totalReceivable > 0 ? $totalReceivable : 1)) * 100, 1) }}% &bull; Faktur Jatuh Tempo
                    </span>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-3 tracking-[1px] bg-warning-soft text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution/30">
                        FOLLOW-UP SEKRE
                    </span>
                </div>
            </article>

            {{-- Card 4: Overdue Kritis --}}
            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-[11px] font-semibold uppercase leading-[14px] tracking-[0.55px] text-danger {{ $mono }}">
                        OVERDUE KRITIS (&gt; 30 HARI)
                    </h2>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-danger-soft">
                        <x-gpa.icon name="alert-triangle" class="h-3.5 w-3.5 text-danger" />
                    </span>
                </div>

                <p class="font-mono text-[28px] font-bold leading-[35px] text-danger">Rp {{ number_format($kritis, 0, ',', '.') }}</p>

                <div class="mt-auto flex items-center justify-between gap-3 border-t border-line-board pt-1">
                    <span class="inline-flex items-center gap-1 text-[12px] leading-4 text-danger">
                        <span class="h-2 w-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
                        {{ round(($kritis / ($totalReceivable > 0 ? $totalReceivable : 1)) * 100, 1) }}% &bull; Klien Terkunci
                    </span>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-3 tracking-[1px] bg-danger text-white">
                        AUTO-FREEZE AKTIF
                    </span>
                </div>
            </article>
        </section>

        {{-- ---------- Matriks umur piutang ---------- --}}
        <section class="rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-4 pb-2">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h2 class="text-[18px] font-bold leading-6 text-ink">Matriks Analisis Umur Piutang (Aging AR Schedule Breakdown)</h2>
                    </div>
                </div>

                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded bg-surface-shell px-3 py-1 outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="text-[11px] font-semibold tracking-[0.88px] text-success-deep {{ $mono }}">
                        Kolektibilitas Portofolio: {{ $agingTotal > 0 ? round(($collectible / $agingTotal) * 100, 1) : 100 }}%
                    </span>
                </span>
            </header>

            <div class="mt-4 flex flex-col gap-3">
                <div class="flex h-7 gap-0.5 overflow-hidden rounded-lg bg-surface-pill p-0.5 shadow-[inset_0_2px_4px_1px_rgb(0_0_0/0.05)] outline outline-1 -outline-offset-1 outline-line-board"
                    role="img" aria-label="Komposisi umur piutang">
                    @php
                        $stage1Share = $agingTotal > 0 ? round(($stagesDef[0]['value'] / $agingTotal) * 100, 1) : 0;
                        $stage2Share = $agingTotal > 0 ? round(($stagesDef[1]['value'] / $agingTotal) * 100, 1) : 0;
                        $stage3Share = $agingTotal > 0 ? round(($stagesDef[2]['value'] / $agingTotal) * 100, 1) : 0;
                        $stage4Share = $agingTotal > 0 ? round(($stagesDef[3]['value'] / $agingTotal) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center justify-center px-1 pb-0.5 bg-brand text-accent rounded-l" style="width: {{ $stage1Share }}%">
                        <span class="font-mono text-[16px] font-bold leading-6">{{ $stage1Share }}%</span>
                    </div>
                    <div class="flex items-center justify-center px-1 pb-0.5 bg-success-deep text-white" style="width: {{ $stage2Share }}%">
                        <span class="font-mono text-[16px] font-bold leading-6">{{ $stage2Share }}%</span>
                    </div>
                    <div class="flex items-center justify-center px-1 pb-0.5 bg-[#C6904A] text-white" style="width: {{ $stage3Share }}%">
                        <span class="font-mono text-[16px] font-bold leading-6">{{ $stage3Share }}%</span>
                    </div>
                    <div class="flex items-center justify-center px-1 pb-0.5 bg-danger text-white rounded-r" style="width: {{ $stage4Share }}%">
                        <span class="font-mono text-[16px] font-bold leading-6">{{ $stage4Share }}%</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-2 pt-1 sm:grid-cols-2 xl:grid-cols-4">
                    {{-- Stage 1 Card --}}
                    <article class="flex flex-col gap-0.5 rounded-lg border border-l-4 px-2 py-2 border-line-board border-l-brand bg-surface-shell">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink-body {{ $mono }}">
                                STAGE 01 (0-15 HARI)
                            </h3>
                            <span class="shrink-0 text-[9px] font-bold leading-3 tracking-[1.08px] text-success-deep {{ $mono }}">
                                SANGAT AMAN
                            </span>
                        </div>
                        <p class="pt-0.5 font-mono text-[16px] font-bold leading-6 text-ink">
                            Rp {{ number_format($stage1Val, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] leading-[16.5px] text-ink-body">{{ $stage1Share }}% Total AR &bull; In-Schedule</p>
                    </article>

                    {{-- Stage 2 Card --}}
                    <article class="flex flex-col gap-0.5 rounded-lg border border-l-4 px-2 py-2 border-line-board border-l-success-deep bg-surface-shell">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink-body {{ $mono }}">
                                STAGE 02 (16-30 HARI)
                            </h3>
                            <span class="shrink-0 text-[9px] font-bold leading-3 tracking-[1.08px] text-success-deep {{ $mono }}">
                                IN-SCHEDULE
                            </span>
                        </div>
                        <p class="pt-0.5 font-mono text-[16px] font-bold leading-6 text-ink">
                            Rp {{ number_format($stage2Val, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] leading-[16.5px] text-ink-body">{{ $stage2Share }}% Total AR &bull; Menuju Tempo</p>
                    </article>

                    {{-- Stage 3 Card --}}
                    <article class="flex flex-col gap-0.5 rounded-lg border border-l-4 px-2 py-2 border-line-board border-l-[#C6904A] bg-warning-cream">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-warning-caution {{ $mono }}">
                                STAGE 03 (1-14 HARI LEWAT)
                            </h3>
                            <span class="shrink-0 text-[9px] font-bold leading-3 tracking-[1.08px] text-warning-caution {{ $mono }}">
                                WARNING
                            </span>
                        </div>
                        <p class="pt-0.5 font-mono text-[16px] font-bold leading-6 text-warning-caution">
                            Rp {{ number_format($stage3Val, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] leading-[16.5px] text-warning-caution">{{ $stage3Share }}% Total AR &bull; Follow-up Sekre</p>
                    </article>

                    {{-- Stage 4 Card --}}
                    <article class="flex flex-col gap-0.5 rounded-lg border border-l-4 px-2 py-2 border-danger/40 border-l-danger bg-danger-soft/30">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-danger {{ $mono }}">
                                STAGE 04 (&gt; 15 HARI OVERDUE)
                            </h3>
                            <span class="shrink-0 text-[9px] font-bold leading-3 tracking-[1.08px] text-danger {{ $mono }}">
                                KRITIS
                            </span>
                        </div>
                        <p class="pt-0.5 font-mono text-[16px] font-bold leading-6 text-danger">
                            Rp {{ number_format($stage4Val, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] leading-[16.5px] text-danger">{{ $stage4Share }}% Total AR &bull; LOCKDOWN PO</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ---------- Buku besar faktur tempo ---------- --}}
        <section class="overflow-hidden rounded-2xl bg-surface shadow-card">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board bg-surface-shell/40 p-6">
                <div class="min-w-0">
                    <h2 class="text-[18px] font-bold leading-6 text-ink">Buku Besar Faktur Tempo Konsolidasi &amp; Evaluasi Plafon Kredit Klien</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative block w-56">
                        <span class="sr-only">Cari klien atau nomor surat jalan</span>
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-subtle" />
                        <input type="search" x-model="query" placeholder="Cari Klien / No. SJ..."
                            class="h-10 w-full rounded-lg border-0 bg-surface py-2 pl-9 pr-3 text-[12px] text-ink placeholder:text-ink-subtle focus:ring-2 focus:ring-inset focus:ring-success-deep" />
                    </label>

                    <div class="flex h-10 items-center gap-1 rounded-lg border border-line-board bg-surface px-3">
                        <x-gpa.icon name="filter" class="h-3.5 w-3.5 shrink-0 text-ink" />
                        <div class="flex items-center gap-1">
                            @foreach ([14, 30, 45] as $topDays)
                                <button type="button" @click="setTopFilter('{{ $topDays }}')"
                                    :class="topFilter === '{{ $topDays }}'
                                        ? 'bg-brand text-accent'
                                        : 'text-ink hover:bg-surface-shell'"
                                    class="rounded px-2 py-1 text-[12px] font-semibold leading-4 transition-colors">
                                    {{ $topDays }}
                                </button>
                            @endforeach
                            <button type="button" @click="setTopFilter('all')"
                                :class="topFilter === 'all' ? 'bg-brand text-accent' : 'text-ink hover:bg-surface-shell'"
                                class="rounded px-2 py-1 text-[12px] font-semibold leading-4 transition-colors">
                                Filter TOP
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-board bg-surface-shell">
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                                Klien Korporat B2B
                            </th>
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                                Surat Jalan (SJ) Terlampir
                            </th>
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                                Nilai Tagihan Tertutang
                            </th>
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                                Termin (TOP) &amp; Jatuh Tempo
                            </th>
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                                Status AR
                            </th>
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body {{ $mono }}">
                                Utilisasi Plafon Kredit
                            </th>
                            <th scope="col" class="px-4 py-3 text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink-body text-right {{ $mono }}">
                                Tindakan Direksi
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse (($ledgerRows ?? []) as $rowIndex => $row)
                            <tr x-show="rowVisible({{ $rowIndex }})"
                                @class([
                                    'border-l-4 border-danger/40 bg-danger-soft/20' => !empty($row['locked']),
                                    'border-t border-line-board hover:bg-surface-base/40' => empty($row['locked']),
                                ])>
                                <th scope="row" class="px-4 py-3">
                                    <span class="flex items-center gap-2.5">
                                        <span @class([
                                            'text-[14px] font-bold leading-5',
                                            'text-danger' => !empty($row['locked']),
                                            'text-ink' => empty($row['locked']),
                                        ])>{{ $row['name'] }}</span>
                                        @if (!empty($row['locked']))
                                            <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0 text-danger" />
                                        @endif
                                    </span>
                                    <span class="text-[9px] font-semibold leading-3 tracking-[1.08px] {{ !empty($row['locked']) ? 'text-danger' : 'text-success-deep' }} {{ $mono }}">
                                        {{ $row['client_id'] }}@if (!empty($row['locked'])) [LOCKED]@endif
                                    </span>
                                </th>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'font-mono text-[11px] font-semibold leading-[14px] tracking-[0.88px]',
                                        'text-danger' => !empty($row['locked']),
                                        'text-ink' => empty($row['locked']),
                                    ])>{{ $row['sj_count_label'] }}</span>
                                    <span class="block text-[9px] font-semibold leading-3 tracking-[1.08px] text-ink-body {{ $mono }}">
                                        {{ $row['sj_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'font-mono text-[16px] font-bold leading-6',
                                        'text-danger' => !empty($row['locked']),
                                        'text-ink' => empty($row['locked']),
                                    ])>{{ $row['outstanding_label'] }}</span>
                                    <span class="mt-2 block text-[9px] font-bold uppercase leading-3 tracking-[1.08px] {{ ($row['verification_tone'] ?? '') === 'warning' ? 'text-warning-caution' : (($row['verification_tone'] ?? '') === 'danger' ? 'text-danger' : 'text-success-deep') }} {{ $mono }}">
                                        {{ $row['verification'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'font-mono text-[11px] font-bold leading-[14px] tracking-[0.88px]',
                                        'text-danger' => !empty($row['locked']),
                                        'text-ink' => empty($row['locked']),
                                    ])>{{ $row['top_label'] }}</span>
                                    <span @class([
                                        'mt-0.5 block text-[12px] leading-4',
                                        'font-bold text-danger' => !empty($row['locked']),
                                        'text-ink-body' => empty($row['locked']) && ($row['days_remaining'] ?? 0) >= 0,
                                        'font-semibold text-warning-caution' => empty($row['locked']) && ($row['days_remaining'] ?? 0) < 0,
                                    ])>
                                        {{ $row['due_label'] }} {{ $row['days_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 rounded px-2.5 py-1 text-[16px] font-bold uppercase leading-6 {{ ($row['status_tone'] ?? '') === 'danger' ? 'bg-danger text-white' : (($row['status_tone'] ?? '') === 'warning' ? 'bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution' : 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-accent-deep') }} {{ $mono }}">
                                        <span @class([
                                            'h-1.5 w-1.5 shrink-0 rounded-full',
                                            'bg-success-deep' => ($row['status_tone'] ?? '') === 'success',
                                            'bg-warning-caution' => ($row['status_tone'] ?? '') === 'warning',
                                            'bg-white' => ($row['status_tone'] ?? '') === 'danger',
                                        ]) aria-hidden="true"></span>
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink {{ $mono }}">
                                            {{ $row['limit_label'] }}
                                        </span>
                                        <span class="text-[9px] font-bold leading-3 tracking-[1.08px] {{ ($row['status_tone'] ?? '') === 'danger' ? 'text-danger' : (($row['status_tone'] ?? '') === 'warning' ? 'text-warning-caution' : 'text-success-deep') }} {{ $mono }}">
                                            {{ $row['utilisation_note'] }}
                                        </span>
                                    </div>
                                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-surface-pill" role="img"
                                        aria-label="Utilisasi plafon {{ $row['utilisation_note'] }}">
                                        <div class="h-full rounded-full {{ ($row['status_tone'] ?? '') === 'danger' ? 'bg-danger' : (($row['status_tone'] ?? '') === 'warning' ? 'bg-[#C6904A]' : 'bg-success-deep') }}"
                                            style="width: {{ $row['utilisation_width'] }}%"></div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        @foreach ($row['actions'] as $actionIndex => $action)
                                            @php $isPrimary = $actionIndex === 0; @endphp
                                            <button type="button"
                                                @click="{{ match ($action) {
                                                    'Kunci Plafon' => 'toggleFreeze(rows[' . $rowIndex . '])',
                                                    'Lepas Freeze' => 'toggleFreeze(rows[' . $rowIndex . '])',
                                                    'Kirim Reminder WA' => 'sendReminder(rows[' . $rowIndex . '])',
                                                    'Restrukturisasi Tagihan' => 'restructure(rows[' . $rowIndex . '])',
                                                    default => 'inspectInvoice(rows[' . $rowIndex . '])',
                                                } }}"
                                                @class([
                                                    'rounded px-2.5 py-1.5 font-mono text-[11px] font-semibold leading-[14px] tracking-[0.88px] transition-colors',
                                                    'shadow-sub bg-danger text-white hover:bg-danger-ink' => $isPrimary && !empty($row['locked']),
                                                    'bg-accent-deep text-ink hover:bg-accent' => $isPrimary && empty($row['locked']) && ($row['status_tone'] ?? '') === 'warning',
                                                    'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-disabled' => ! $isPrimary || ($isPrimary && empty($row['locked']) && ($row['status_tone'] ?? '') !== 'warning'),
                                                ])>{{ $action }}</button>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-[12px] text-ink-body">
                                    Tidak ada faktur tempo aktif yang dapat dipantau.
                                </td>
                            </tr>
                        @endforelse

                        <tr x-show="visibleCount() === 0" x-cloak>
                            <td colspan="7" class="px-4 py-8 text-center text-[12px] text-ink-body">
                                Tidak ada faktur tempo yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board bg-surface-shell p-4">
                <p class="text-[11px] font-medium leading-[14px] tracking-[0.88px] text-ink-body {{ $mono }}"
                    x-text="shownLabel()">Menampilkan {{ count($ledgerRows ?? []) }} dari {{ $activeContracts ?? 0 }} Klien Kontrak Aktif B2B</p>
                <div class="flex items-center gap-2">
                    <button type="button" disabled
                        class="cursor-not-allowed rounded bg-surface px-3 py-1 text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink-body outline outline-1 -outline-offset-1 outline-line-board">
                        Prev
                    </button>
                    <span class="text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink {{ $mono }}">Halaman 1 / 1</span>
                    <button type="button"
                        class="rounded bg-surface px-3 py-1 text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink-body outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:text-ink">
                        Next
                    </button>
                </div>
            </footer>
        </section>

        {{-- ---------- Kebijakan pembekuan & protokol verifikasi ---------- --}}
        <section class="flex flex-col gap-4">
            <article class="flex flex-col justify-between gap-6 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-2">
                    <header class="flex items-center gap-2 pb-2">
                        <h2 class="text-[18px] font-bold leading-6 text-ink">Protokol Verifikasi Keuangan</h2>
                    </header>

                    <div class="flex flex-col gap-2 pt-2">
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2.5">
                            <span class="text-[12px] leading-4 text-ink">Antrean Verifikasi Masuk:</span>
                            <span class="text-[12px] font-bold leading-4 text-ink {{ $mono }}">3 Faktur</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2.5">
                            <span class="text-[12px] leading-4 text-ink">Total Nilai Kliring Pending:</span>
                            <span class="text-[12px] font-bold leading-4 text-success-deep {{ $mono }}">Rp 78.400.000</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-line-board pt-4">
                    <a href="{{ route('sekretaris.verification') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent-deep px-4 py-3 text-[16px] font-bold leading-6 text-ink shadow-sub transition-colors hover:bg-accent">
                        <x-gpa.icon name="clipboard" class="h-3.5 w-3.5 shrink-0" />
                        Buka Antrean Verifikasi Sekre
                    </a>
                    <p class="text-center font-mono text-[10px] leading-[15px] tracking-[0.88px] text-ink-body">
                        SLO VERIFIKASI MAKSIMAL: 1x24 JAM DARI TANGGAL MUTASI
                    </p>
                </div>
            </article>
        </section>
    </div>
@endsection
