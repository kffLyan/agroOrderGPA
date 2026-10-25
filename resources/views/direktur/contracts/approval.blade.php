@extends('layouts.direktur')

@section('title', 'Persetujuan Kontrak // Otorisasi Tier-1')

@section('content')
    @php
        $rows = $rows ?? [];
        $totalVal = !empty($rows) ? array_sum(array_column($rows, 'value')) : 0;
        $exceedCount = !empty($rows) ? count(array_filter($rows, fn ($r) => !empty($r['exceeds_plafon']))) : 0;
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorApproval(@js($rows))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Persetujuan Kontrak Khusus, Diskon Volume &amp; Perpanjangan TOP B2B
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <p class="flex flex-col rounded-lg bg-surface px-3 py-2 text-right outline outline-1 -outline-offset-1 outline-line-board">
                    <span class="gpa-note text-ink-body">PERIODE LAPORAN</span>
                    <span class="gpa-meta-lg font-bold text-ink">PERIODE: {{ strtoupper(now()->translatedFormat('F Y')) }}</span>
                </p>
                <a href="{{ route('direktur.contracts.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface px-3.5 py-2.5 text-ink shadow-sm outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="file-text" class="h-3.5 w-3.5 shrink-0 text-ink-body" />
                    <span class="text-xs font-semibold leading-4 text-ink">Semua Arsip Kontrak</span>
                </a>
                <button type="button" @click="printAuthorization()"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 shadow-sub outline outline-1 -outline-offset-1 outline-success transition-colors hover:bg-brand-hover">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-white" />
                    <span class="text-xs font-semibold leading-4 text-white">Cetak Antrian Otorisasi</span>
                </button>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Card 1: Pengajuan Pending --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-warning-caution">Pengajuan Pending</h2>
                        <x-gpa.icon name="clock" class="h-4 w-4 shrink-0 text-warning-caution" />
                    </div>
                    <p class="gpa-figure text-[26px] leading-8 text-ink">{{ count($rows) }} Pengajuan</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution">
                        GATE L4
                    </span>
                    <span class="gpa-note font-semibold text-warning-caution">
                        Menunggu tanda tangan digital
                    </span>
                </div>
            </article>

            {{-- Card 2: Nilai Kontrak Pending --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-ink">Nilai Kontrak Pending</h2>
                        <x-gpa.icon name="banknote" class="h-4 w-4 shrink-0 text-ink" />
                    </div>
                    <p class="gpa-figure text-[26px] leading-8 text-ink">Rp {{ number_format($totalVal, 0, ',', '.') }}</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent-deep text-white outline outline-1 -outline-offset-1 outline-accent-deep">
                        TIER-1
                    </span>
                    <span class="gpa-note font-semibold text-ink-body">
                        Seluruh nilai Needs Directors
                    </span>
                </div>
            </article>

            {{-- Card 3: Pelanggaran Plafon --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-danger">Pelanggaran Plafon</h2>
                        <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-danger" />
                    </div>
                    <p class="gpa-figure text-[26px] leading-8 text-ink">{{ $exceedCount }} Pengajuan</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-danger-soft/40 text-danger outline outline-1 -outline-offset-1 outline-danger">
                        ESCALATED
                    </span>
                    <span class="gpa-note font-semibold text-danger">
                        Di atas plafon diskon atau TOP default
                    </span>
                </div>
            </article>

            {{-- Card 4: Sesi Tanda Tangan --}}
            <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-success-deep">Sesi Tanda Tangan</h2>
                        <x-gpa.icon name="lock" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure text-[26px] leading-8 text-ink">14:32</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                        HSM OK
                    </span>
                    <span class="gpa-note font-semibold text-success-deep">
                        HSM RSA-4096 // SHA-256 aktif
                    </span>
                </div>
            </article>
        </section>

        {{-- Authorization queue --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-4 border-b border-line-board pb-4">
                <div class="min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded bg-danger px-2 py-0.5 gpa-note font-bold text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-white" aria-hidden="true"></span>
                            PERLU OTORISASI DIREKTUR
                        </span>
                    </div>
                    <h2 class="gpa-section-title text-ink">Daftar Pengajuan Kontrak Khusus &amp; Diskon Volume B2B</h2>
                </div>

                <div class="flex shrink-0 flex-col gap-1">
                    <p class="inline-flex items-center gap-2 rounded bg-surface-shell px-3 py-1.5 outline outline-1 -outline-offset-1 outline-line-board">
                        <span class="gpa-note text-ink-body">SELEKSI BATCH:</span>
                        <span class="gpa-meta font-bold text-ink" x-text="selectedLabel()">0 / {{ count($rows) }} Terpilih</span>
                        <span class="gpa-meta font-bold text-success-deep" x-text="selectedValueLabel()">Rp 0</span>
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="approveSelected()" :disabled="selected.length === 0"
                            class="inline-flex items-center gap-1.5 rounded bg-accent px-3.5 py-1.5 gpa-meta-lg font-bold text-ink shadow-sub outline outline-1 -outline-offset-1 outline-brand-deep/30 transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60">
                            Approve Terpilih (Batch)
                        </button>
                        <button type="button" @click="selectAll()"
                            class="inline-flex items-center rounded bg-surface-shell px-3 py-1.5 text-center gpa-meta-lg font-medium text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                            Pilih Semua
                        </button>
                    </div>
                </div>
            </header>

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 py-3">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Filter</span>
                    <button type="button" @click="setFilter('all')"
                        :class="filterClass('all')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Semua Pending ({{ count($rows) }})
                    </button>
                    <button type="button" @click="setFilter('discount')"
                        :class="filterClass('discount')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Diskon Khusus ({{ count(array_filter($rows, fn ($r) => ($r['filter_key'] ?? '') === 'discount')) }})
                    </button>
                    <button type="button" @click="setFilter('top')"
                        :class="filterClass('top')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Ekstensi TOP ({{ count(array_filter($rows, fn ($r) => ($r['filter_key'] ?? '') === 'top')) }})
                    </button>
                    <button type="button" @click="setFilter('annual')"
                        :class="filterClass('annual')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        Kontrak Induk Tahunan ({{ count(array_filter($rows, fn ($r) => ($r['filter_key'] ?? '') === 'annual')) }})
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <label class="relative flex items-center" for="approval-search">
                        <span class="sr-only">Cari klien, kontrak, atau komoditas...</span>
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input id="approval-search" type="search" x-model="search"
                            placeholder="Cari klien, kontrak, atau komoditas..."
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    <span class="inline-flex items-center gap-1 gpa-note font-semibold text-success-deep">
                        <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                        HSM RSA-4096 Terhubung
                    </span>
                    <span class="gpa-note text-ink-body" aria-hidden="true">|</span>
                    <span class="inline-flex items-center gap-1 gpa-note font-semibold text-success-deep">
                        <x-gpa.icon name="clock" class="h-3 w-3 shrink-0" />
                        Sesi Timeout: 14:32
                    </span>
                </div>
            </div>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <caption class="sr-only">Daftar Pengajuan Kontrak Khusus &amp; Diskon Volume B2B</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="w-9 py-3 pr-2">
                                <span class="sr-only">Pilih pengajuan</span>
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                ID KONTRAK &amp; PEMOHON
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                KOMODITAS &amp; VOLUME
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                KETENTUAN HARGA &amp; TOP
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                ANALISIS MARGIN
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-left">
                                SKOR KREDIT
                            </th>
                            <th scope="col" class="px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body text-right">
                                AKSI OTORISASI DIREKTUR
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($rows as $index => $row)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50"
                                x-show="matches('{{ $row['filter_key'] ?? '' }}', {{ $index }})">
                                <td class="py-4 pr-2 align-top">
                                    <input type="checkbox" class="gpa-check m-0" value="{{ $row['id'] }}"
                                        x-model="selected">
                                </td>
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <div class="flex items-start gap-2">
                                        <span class="min-w-0">
                                            <span class="block text-[13px] font-semibold leading-5 text-ink">
                                                {{ $row['client'] }}
                                            </span>
                                            <span class="mt-0.5 block text-[11px] leading-4 text-ink-body">
                                                {{ $row['region'] }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="mt-2 flex flex-col gap-0.5 text-[11px] leading-4 text-ink-body">
                                        <p class="font-mono font-medium text-ink">{{ $row['contract'] }}</p>
                                        <p>PIC: <span class="font-semibold text-ink">{{ $row['submitted_by'] }}</span></p>
                                        @if(!empty($row['pic_phone']))
                                            <p class="inline-flex items-center gap-1 font-mono text-[10px] font-bold text-emerald-800">
                                                <x-gpa.icon name="phone" class="h-3 w-3 shrink-0 text-emerald-600" />
                                                <span>{{ $row['pic_phone'] }}</span>
                                            </p>
                                        @endif
                                        <p class="text-ink-quiet text-[10px]">{{ $row['submitted_at'] }}</p>
                                    </div>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[13px] font-medium leading-5 text-ink">{{ $row['commodity'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $row['volume'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $row['segment'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    @if (!empty($row['price_strike']))
                                        <p class="text-[13px] font-semibold leading-5 text-ink">
                                             {{ $row['price'] }}
                                            <span class="text-[11px] font-normal text-ink-body line-through">
                                                {{ $row['price_strike'] }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="text-[13px] font-semibold leading-5 text-ink">{{ $row['price'] }}</p>
                                    @endif
                                    <p @class([
                                        'mt-1 text-[11px] leading-4',
                                        'font-medium text-warning-caution' => ($row['exception_tone'] ?? '') === 'caution',
                                        'text-ink-body' => ($row['exception_tone'] ?? '') !== 'caution',
                                    ])>{{ $row['exception'] }}</p>
                                    <p class="text-[11px] leading-4 text-ink-body">{{ $row['term'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[13px] font-semibold leading-5 text-success-deep">{{ $row['margin'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">
                                        Minimum {{ number_format($row['margin_minimum'], 1, '.', '') }}%
                                    </p>
                                    <p class="mt-1 flex items-start gap-1 text-[11px] leading-4 text-success-deep">
                                        <x-gpa.icon name="check-circle" class="mt-0.5 h-3 w-3 shrink-0" />
                                        <span>{{ $row['margin_flag'] }}</span>
                                    </p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[13px] font-semibold leading-5 text-ink">{{ $row['score'] }}</p>
                                    <p class="mt-1 text-[11px] leading-4 text-ink-body">{{ $row['score_note'] }}</p>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <div class="flex flex-wrap items-center justify-end gap-x-2 gap-y-1.5">
                                        @if (!empty($row['wa_link']))
                                            <a href="{{ $row['wa_link'] }}" target="_blank" rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1 rounded-md bg-emerald-50 border border-emerald-300 px-2 py-1.5 text-[11px] font-bold text-emerald-800 transition-colors hover:bg-emerald-100 hover:text-emerald-900 shadow-sm"
                                                title="Hubungi PIC via WhatsApp untuk konfirmasi pengajuan">
                                                <x-gpa.icon name="phone" class="h-3 w-3 shrink-0 text-emerald-700" />
                                                <span>WhatsApp PIC</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('direktur.contracts.show', $row['id']) }}"
                                            class="inline-flex items-center gap-1.5 rounded-md border border-line-board px-2.5 py-1.5 text-[11px] font-medium leading-4 text-ink transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="file-text" class="h-3.5 w-3.5 shrink-0" />
                                            Detail PKS
                                        </a>
                                        <button type="button" @click="approve({{ $row['id'] }})"
                                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-[11px] font-semibold leading-4 transition-opacity hover:opacity-90"
                                            @class([
                                                'bg-ink text-white' => ($row['approve_tone'] ?? 'solid') === 'solid',
                                                'bg-accent text-ink' => ($row['approve_tone'] ?? '') === 'accent',
                                            ])>
                                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                                            {{ $row['approve_label'] }}
                                        </button>
                                        <button type="button" @click="revise({{ $row['id'] }})"
                                            class="text-[11px] font-medium leading-4 text-ink-body underline-offset-2 transition-colors hover:text-ink hover:underline">
                                            Minta Revisi
                                        </button>
                                        <button type="button" @click="reject({{ $row['id'] }})"
                                            class="text-[11px] font-medium leading-4 text-danger underline-offset-2 transition-colors hover:underline">
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-xs text-ink-body">
                                    Tidak ada pengajuan kontrak pending otorisasi saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="gpa-note font-semibold text-danger" x-show="visibleCount() === 0" x-cloak>
                Tidak ada pengajuan yang cocok dengan filter atau kata kunci pencarian.
            </p>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
                <p class="flex items-start gap-2">
                </p>
                <div class="flex flex-col items-end gap-1">
                    <p class="gpa-note font-bold text-ink">TOTAL NILAI KONTRAK AKTIF PENDING: Rp {{ number_format($totalVal, 0, ',', '.') }} ({{ count($rows) }} Pengajuan)</p>
                    <p class="gpa-note text-ink-body" x-text="visibleLabel()">Menampilkan {{ count($rows) }} dari {{ count($rows) }} Pengajuan Pending</p>
                </div>
            </footer>
        </section>
    </div>
@endsection
