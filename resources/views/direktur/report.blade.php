@extends('layouts.direktur')

@section('title', 'Laporan Penjualan Eksekutif // Arsip Kuartal IV 2026')

@section('content')
    @php
        $ledgerFont = 'font-mono tracking-[0.2px]';
    @endphp

    <div class="flex flex-col gap-5"
        x-data="directorReport(@js($commodityList), @js($clientList), @js($auditEntries), @js($sealData))">

        {{-- ---------- Banner arsip terkunci ---------- --}}
        <section class="relative overflow-hidden rounded-xl bg-brand p-6 text-white shadow-card">
            <div class="relative flex flex-col gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1 text-[10px] font-bold tracking-[0.88px] text-ink gpa-meta">
                        <x-gpa.icon name="lock" class="h-3 w-3" />
                        STATUS: LAPORAN TELAH DIKUNCI
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[10px] font-semibold tracking-[0.88px] text-white/80 gpa-meta">
                        <x-gpa.icon name="shield" class="h-3 w-3" />
                        CERTIFICATE OF AUTHENTICITY
                    </span>
                </div>

                <div class="flex flex-col gap-3">
                    <h1 class="font-inter text-2xl font-extrabold uppercase tracking-[0.6px] text-white sm:text-[28px]">
                        Laporan Rekapitulasi Penjualan Eksekutif
                    </h1>
                    <p class="max-w-3xl text-[13px] leading-6 text-white/70">
                        Periode <span class="font-semibold text-white">{{ $lockPeriod }}</span> telah
                        dikunci permanen pada
                        <span class="font-semibold text-accent">{{ $lockedAt }}</span>
                        oleh <span class="font-semibold text-accent">{{ $lockedBy }}</span>,
                        Direktur Utama. Seluruh transaksi, Surat Jalan, Timbangan Aktual, dan
                        Faktur Tempo berstatus <span class="font-semibold text-white">read-only</span> dan
                        dilindungi hash kriptografi:
                    </p>
                    <p class="{{ $ledgerFont }} max-w-4xl break-all rounded-lg bg-brand-deep px-3 py-2 text-[11px] text-accent">
                        SHA-256: {{ $sha256Hash }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="{{ route('direktur.reports.download') }}" target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-[11px] font-bold tracking-[0.88px] text-ink shadow-sub transition-colors hover:bg-accent-deep gpa-meta">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                        CETAK DOKUMEN LAPORAN RESMI (PDF)
                    </a>
                    <a href="{{ route('direktur.reports.export') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-4 py-2 text-[11px] font-bold tracking-[0.88px] text-white transition-colors hover:bg-white/20 gpa-meta">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                        UNDUH BUKU BESAR (CSV / EXCEL)
                    </a>
                    <button type="button" @click="revealAudit()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-4 py-2 text-[11px] font-bold tracking-[0.88px] text-white transition-colors hover:bg-white/20 gpa-meta">
                        <x-gpa.icon name="clipboard" class="h-3.5 w-3.5" />
                        LIHAT LOG AUDIT FORENSIK
                    </button>
                    <button type="button" @click="verifyIntegrity()"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-4 py-2 text-[11px] font-bold tracking-[0.88px] text-white transition-colors hover:bg-white/20 gpa-meta">
                        <x-gpa.icon name="badge-check" class="h-3.5 w-3.5" />
                        VERIFY SHA-256
                    </button>
                </div>
            </div>
        </section>

        {{-- ---------- Filter beku ---------- --}}
        <section class="rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-ink-subtle">
                    <x-gpa.icon name="filter" class="h-4 w-4" />
                </span>
                <span class="text-[10px] font-bold uppercase tracking-[1.08px] text-ink-subtle gpa-micro-bold">
                    FILTER FROZEN — AUDIT ARCHIVE
                </span>

                <div class="flex min-w-0 flex-wrap items-center gap-2 rounded-lg border border-line-faint bg-surface-base px-3 py-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">PERIODE:</span>
                    <span class="text-[12px] font-semibold text-ink">Kuartal IV (Okt - Des 2026)</span>
                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-warning-cream text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border">
                        LOCKED
                    </span>
                </div>

                <div class="flex min-w-0 flex-wrap items-center gap-2 rounded-lg border border-line-faint bg-surface-base px-3 py-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">SEGMENTASI:</span>
                    <span class="text-[12px] font-semibold text-ink">Semua Klien B2B</span>
                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line">
                        FROZEN
                    </span>
                </div>

                <div class="flex min-w-0 flex-wrap items-center gap-2 rounded-lg border border-line-faint bg-surface-base px-3 py-2">
                    <span class="text-[10px] font-bold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">KOMODITAS:</span>
                    <span class="text-[12px] font-semibold text-ink">5 Komoditas Inti</span>
                    <span class="rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-surface-track text-ink-body outline outline-1 -outline-offset-1 outline-line">
                        FROZEN
                    </span>
                </div>
            </div>
        </section>

        {{-- ---------- Empat kartu KPI ---------- --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="KPI eksekutif">
            {{-- Card 1: Omzet Disahkan --}}
            <article class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        OMZET DISAHKAN (NETTO RIIL)
                    </h2>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-accent text-ink">
                        FINAL
                    </span>
                </div>

                <p class="font-inter text-[22px] font-extrabold leading-7 text-ink">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] leading-5">
                    <span class="text-ink-body">Target Q4: Rp {{ number_format($quarterTarget, 0, ',', '.') }}</span>
                    <span class="text-success-deep">({{ $revenuePercent }}% Tercapai)</span>
                </div>

                <div class="mt-auto flex flex-wrap items-center justify-between gap-x-3 border-t border-line-faint pt-3 text-[10px] leading-4">
                    <span class="font-semibold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        Diskon Kontrak Sah:
                    </span>
                    <span class="text-right font-semibold text-warning">
                        -Rp 14.800.000
                    </span>
                </div>
            </article>

            {{-- Card 2: Total Volume Tera Sah --}}
            <article class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        TOTAL VOLUME TERA SAH
                    </h2>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-accent text-ink">
                        100% VALID
                    </span>
                </div>

                <p class="font-inter text-[22px] font-extrabold leading-7 text-ink">
                    {{ number_format($totalTonnageKg, 0, ',', '.') }} kg
                </p>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] leading-5">
                    <span class="text-ink-body">Ekuivalen: {{ number_format($totalTonnageKg / 1000, 2, '.', '') }} Tonase Realisasi</span>
                </div>

                <div class="mt-auto flex flex-wrap items-center justify-between gap-x-3 border-t border-line-faint pt-3 text-[10px] leading-4">
                    <span class="font-semibold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        Rasio Pasokan:
                    </span>
                    <span class="text-right font-semibold text-ink">
                        68% Binaan (26.15 T) | 32% Buffer (12.30 T)
                    </span>
                </div>
            </article>

            {{-- Card 3: Cash Inflow Realisasi --}}
            <article class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        CASH INFLOW REALISASI
                    </h2>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-accent text-ink">
                        {{ $settledPercent }}% SETTLED
                    </span>
                </div>

                <p class="font-inter text-[22px] font-extrabold leading-7 text-success-deep">
                    Rp {{ number_format($cashInflow, 0, ',', '.') }}
                </p>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] leading-5">
                    <span class="text-ink-body">Piutang Berjalan Tempo:</span>
                    <span class="text-warning">Rp {{ number_format($openReceivables, 0, ',', '.') }}</span>
                </div>

                <div class="mt-auto flex flex-wrap items-center justify-between gap-x-3 border-t border-line-faint pt-3 text-[10px] leading-4">
                    <span class="font-semibold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        Kolektibilitas / NPL:
                    </span>
                    <span class="text-right font-semibold text-success-deep">
                        BAD DEBT: 0.0% (LANCAR)
                    </span>
                </div>
            </article>

            {{-- Card 4: Deviasi Susut & Retur --}}
            <article class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        DEVIASI SUSUT &amp; RETUR
                    </h2>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] bg-warning text-canvas">
                        KLAIM TUNTAS
                    </span>
                </div>

                <p class="font-inter text-[22px] font-extrabold leading-7 text-ink">
                    Rp {{ number_format($shrinkageClaim, 0, ',', '.') }}
                </p>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px] leading-5">
                    <span class="text-ink-body">Ambang Toleransi:</span>
                    <span class="text-success-deep">{{ $tolerancePercent }}% (&lt; 2.0% Toleransi)</span>
                </div>

                <div class="mt-auto flex flex-wrap items-center justify-between gap-x-3 border-t border-line-faint pt-3 text-[10px] leading-4">
                    <span class="font-semibold uppercase tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                        Susut: Rp 3.25M
                    </span>
                    <span class="text-right font-semibold text-ink-quiet">
                        Retur Fisik: Rp 4.15M
                    </span>
                </div>
            </article>
        </section>

        {{-- ---------- Tabel 1: Komoditas ---------- --}}
        <section class="overflow-hidden rounded-xl border border-line-hair bg-surface shadow-sub">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <h2 class="text-[13px] font-bold leading-5 text-ink">Breakdown Analitik 5 Komoditas Inti (Estimasi PO vs Tera Sah Gudang)</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-warning-cream px-2.5 py-1 text-[9px] font-bold tracking-[0.88px] text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border">
                        TERKUNCI
                    </span>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-base">
                            <th scope="col" class="sticky left-0 z-10 bg-surface-base px-4 py-3 text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Komoditas Agribisnis
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Volume PO (kg)
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Tera Sah (kg)
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Deviasi Susut
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Harga Rata-rata/kg
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Omzet Realisasi
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Kontribusi
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Gross Margin
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Status Audit
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($commodityList as $row)
                            <tr class="border-b border-line-faint transition-colors hover:bg-surface-base">
                                <th scope="row"
                                    class="sticky left-0 z-10 bg-surface px-4 py-3 text-[12px] font-semibold text-ink">
                                    {{ $row['name'] }}
                                </th>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['po_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-ink {{ $ledgerFont }}">{{ $row['tera_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-warning {{ $ledgerFont }}">{{ $row['deviation_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body">{{ $row['price_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-ink">{{ $row['revenue_label'] }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="flex items-center justify-end gap-2">
                                        <span class="hidden h-1.5 w-12 overflow-hidden rounded-full bg-surface-track sm:block"
                                            aria-hidden="true">
                                            <span class="block h-full rounded-full bg-brand-line"
                                                :style="`width: ${contributionWidth(commodities[{{ $loop->index }}])}`"></span>
                                        </span>
                                        <span class="text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['contribution_label'] }}</span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-[12px] text-success-deep">{{ $row['margin_label'] }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span
                                        class="rounded-full bg-accent/50 px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] text-success-deep outline outline-1 -outline-offset-1 outline-accent-deep">
                                        100% AUDITED
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-sm font-medium text-ink-subtle">
                                    Belum ada data komoditas dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-surface-shell">
                        <tr class="border-t-2 border-brand-line">
                            <th scope="row" class="sticky left-0 z-10 bg-surface-shell px-4 py-3 text-[12px] font-bold text-ink">
                                TOTAL KONSOLIDASI
                            </th>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ number_format($poTotal, 0, ',', '.') }} kg</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ number_format($teraTotal, 0, ',', '.') }} kg</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-warning {{ $ledgerFont }}">-{{ number_format($shrinkageTotal, 0, ',', '.') }} kg (-{{ round(($shrinkageTotal / ($poTotal ?: 1)) * 100, 2) }}%)</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink">Rp {{ number_format(round($commRev / ($teraTotal ?: 1)), 0, ',', '.') }} (Avg)</td>
                            <td class="px-4 py-3 text-right text-[13px] font-extrabold text-ink">Rp {{ number_format($commRev, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">100.0%</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-success-deep">{{ round($marginTotal / (count($commodityList) ?: 1), 1) }}% (Avg)</td>
                            <td class="px-4 py-3 text-right">
                                <span
                                    class="rounded-full bg-accent px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] text-ink">
                                    FINAL
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- ---------- Tabel 2: Klien B2B ---------- --}}
        <section class="overflow-hidden rounded-xl border border-line-hair bg-surface shadow-sub">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line-hair px-4 py-3">
                <div class="flex min-w-0 items-center gap-2.5">
                    <h2 class="text-[13px] font-bold leading-5 text-ink">Rekapitulasi Penjualan Klien B2B &amp; Status Pembayaran Tempo (TOP)</h2>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded-full bg-accent px-2.5 py-1 text-[9px] font-bold tracking-[0.88px] text-ink">
                        AR-AUDIT COMPLETED
                    </span>
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="border-b border-line-hair bg-surface-base">
                            <th scope="col" class="sticky left-0 z-10 bg-surface-base px-4 py-3 text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Klien / Mitra B2B
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Frek. PO
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Volume Tera Sah (kg)
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Total Tagihan Bruto
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Settled (Inflow Sah)
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Sisa Piutang Berjalan
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Ketentuan Tempo
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-[10px] font-bold uppercase leading-4 tracking-[0.88px] text-ink-subtle gpa-micro-bold">
                                Status Piutang
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clientList as $row)
                            <tr class="border-b border-line-faint transition-colors hover:bg-surface-base">
                                <th scope="row" class="sticky left-0 z-10 bg-surface px-4 py-3">
                                    <span class="block text-[12px] font-semibold leading-5 text-ink">{{ $row['name'] }}</span>
                                    <span class="block text-[10px] leading-4 text-ink-quiet {{ $ledgerFont }}">{{ $row['code'] }}</span>
                                </th>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['po_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body {{ $ledgerFont }}">{{ $row['volume_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink">{{ $row['gross_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-success-deep">{{ $row['settled_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] font-semibold text-warning">{{ $row['receivable_label'] }}</td>
                                <td class="px-4 py-3 text-right text-[12px] text-ink-body">{{ $row['term_label'] }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span @class([
                                        'rounded-full px-2 py-0.5 text-[9px] font-bold tracking-[0.88px]',
                                        'bg-accent/50 text-success-deep outline outline-1 -outline-offset-1 outline-accent-deep' => $row['status_tone'] === 'lunas',
                                        'bg-warning-cream text-warning-deep outline outline-1 -outline-offset-1 outline-warning-border' => $row['status_tone'] !== 'lunas',
                                    ])>
                                        {{ $row['status_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-sm font-medium text-ink-subtle">
                                    Belum ada data rekapitulasi penjualan klien B2B.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-surface-shell">
                        <tr class="border-t-2 border-brand-line">
                            <th scope="row" class="sticky left-0 z-10 bg-surface-shell px-4 py-3 text-[12px] font-bold text-ink">
                                TOTAL REKAPITULASI
                            </th>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ $cPoTotal }} PO</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink {{ $ledgerFont }}">{{ number_format($cVolTotal, 0, ',', '.') }} kg</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink">Rp {{ number_format($cGrossTotal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-success-deep">Rp {{ number_format($cSettledTotal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-warning">Rp {{ number_format($cRecTotal, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-[12px] font-bold text-ink">RATA-RATA: {{ $avgTerm }} HARI</td>
                            <td class="px-4 py-3 text-right">
                                <span
                                    class="rounded-full bg-accent px-2 py-0.5 text-[9px] font-bold tracking-[0.88px] text-ink">
                                    AUDITED &amp; VERIFIED
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- ---------- Daftar Transaksi Pesanan Sah ---------- --}}
        @if(isset($orders) && count($orders) > 0)
        <section class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-4 shadow-sub">
            <header class="flex items-center justify-between border-b border-line-board/60 pb-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-ink">Daftar Transaksi Pesanan Sah Terkunci</h2>
                <span class="text-xs text-ink-quiet">{{ count($orders) }} Pesanan Tercatat</span>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-line-board text-ink-subtle">
                            <th class="py-2 px-3 font-semibold">No. Pesanan</th>
                            <th class="py-2 px-3 font-semibold">Klien B2B</th>
                            <th class="py-2 px-3 font-semibold">Tgl Pengiriman</th>
                            <th class="py-2 px-3 font-semibold text-right">Nilai Transaksi</th>
                            <th class="py-2 px-3 font-semibold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-board/40">
                        @foreach($orders as $ord)
                            <tr class="hover:bg-surface-shell">
                                <td class="py-2.5 px-3 font-mono font-bold text-ink">{{ $ord->order_number }}</td>
                                <td class="py-2.5 px-3 text-ink">{{ $ord->user?->company_name ?? $ord->user?->name ?? 'Klien' }}</td>
                                <td class="py-2.5 px-3 text-ink-body">{{ $ord->target_delivery_date ? date('d M Y', strtotime($ord->target_delivery_date)) : '-' }}</td>
                                <td class="py-2.5 px-3 text-right font-semibold text-ink">Rp {{ number_format($ord->grand_total, 0, ',', '.') }}</td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-accent/40 text-success-deep">{{ $ord->status }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- ---------- Lembar Pengesahan Digital Direksi & Sertifikat Audit ---------- --}}
        <section class="rounded-2xl border border-line bg-surface p-5 sm:p-6 shadow-card">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-brand/20 bg-brand-soft text-brand-deep">
                        <x-gpa.icon name="qrcode" class="h-5 w-5" />
                    </span>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-ink">Lembar Pengesahan Digital Direksi &amp; Sertifikat Audit</h2>
                        <p class="text-xs font-semibold text-brand-line">Nomor Sertifikasi: GPA-AUDIT-CERT-2026-Q4</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5 text-emerald-600" />
                        Periode Terkunci Permanen // Audit Sah
                    </span>
                </div>
            </header>

            <div class="mt-5 grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-12 lg:gap-8 items-start">
                {{-- Kolom 1: Otoritas & Penandatangan --}}
                <div class="space-y-3 lg:col-span-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-ink-subtle">Penandatangan Utama</span>
                        <p class="mt-0.5 text-sm font-bold text-ink">{{ $lockedBy }}</p>
                        <p class="text-xs text-ink-muted">Direktur Utama Green Pasundan Agriculture</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-ink-subtle">Waktu Penguncian Buku</span>
                        <p class="mt-0.5 text-xs font-semibold text-ink {{ $ledgerFont }}">{{ $lockedAt }}</p>
                    </div>
                </div>

                {{-- Kolom 2: Integritas Kriptografis --}}
                <div class="space-y-3 lg:col-span-5">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-ink-subtle">Sertifikasi &amp; Standar Enkripsi</span>
                        <p class="mt-0.5 text-xs font-semibold text-ink">X.509 v3 / RSA 4096-bit (SHA-256 Digest)</p>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-ink-subtle">Hash Kriptografis Konsolidasi</span>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="min-w-0 flex-1 truncate rounded-xl border border-line bg-surface-muted/60 px-3 py-2 text-xs font-mono font-semibold text-ink-strong" title="{{ $sha256Hash }}">
                                {{ $sha256Hash }}
                            </span>
                            <button type="button" @click="copyHash()"
                                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-line bg-surface text-ink-subtle hover:bg-surface-muted hover:text-ink transition-colors"
                                title="Salin Hash SHA-256">
                                <x-gpa.icon name="copy" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Kolom 3: Aksi Unduh & Cetak --}}
                <div class="flex flex-col gap-2.5 lg:col-span-3">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-ink-subtle">Ekspor &amp; Cetak Arsip</span>
                    <button type="button" @click="exportWorkbook()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-line bg-surface px-4 py-2.5 text-xs font-bold text-ink hover:bg-surface-muted transition-colors shadow-sm">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5 text-ink-subtle" />
                        Download Excel (.xlsx)
                    </button>
                    <button type="button" @click="printSeal()"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand hover:bg-brand-hover px-4 py-2.5 text-xs font-bold text-surface transition-colors shadow-sm">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                        Cetak Pengesahan (.pdf)
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection
