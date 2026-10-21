@extends('layouts.klien')

@section('title', 'Pusat Dokumen & Faktur Konsolidasi Klien B2B')

@php
    $rawInvoices = $rawInvoices ?? ($invoices ?? collect());
    $user = $user ?? Auth::guard('web')->user();

    // Hitung metrik keuangan
    $unpaidAmount = (float) $rawInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('grand_total');
    $settledAmount = (float) $rawInvoices->where('status', 'PAID')->sum('grand_total');

    $tabs = [
        ['label' => 'Semua Tagihan & Faktur', 'value' => 'all', 'icon' => 'file-text', 'badge' => ''],
        ['label' => 'Menunggu Pembayaran', 'value' => 'unpaid', 'icon' => 'clock', 'badge' => 'Tempo'],
        ['label' => 'Lunas Terverifikasi', 'value' => 'settled', 'icon' => 'check-circle', 'badge' => ''],
        ['label' => 'Dokumen Pajak & BA', 'value' => 'tax', 'icon' => 'shield', 'badge' => ''],
    ];

    $periods = [
        ['value' => 'all', 'label' => 'Semua Periode Transaksi'],
        ['value' => 'current', 'label' => 'Bulan Berjalan (' . now()->translatedFormat('F Y') . ')'],
        ['value' => 'last-month', 'label' => 'Bulan Sebelumnya'],
    ];

    $invoices = $rawInvoices->map(function ($inv, $idx) {
        $totalNetto = (float) ($inv->orders ? $inv->orders->sum(function ($o) {
            return $o->orderItems ? $o->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty) : 0;
        }) : 0);

        $statusText = match ($inv->status) {
            'UNPAID'   => 'Menunggu Pembayaran',
            'PARTIAL'  => 'Dibayar Sebagian',
            'PAID'     => 'Lunas Terverifikasi',
            'OVERDUE'  => 'Melewati Jatuh Tempo',
            default    => $inv->status,
        };

        $statusTone = match ($inv->status) {
            'UNPAID'   => 'warning',
            'PARTIAL'  => 'warning',
            'PAID'     => 'accent',
            'OVERDUE'  => 'warning',
            default    => 'muted',
        };

        $isPaid = $inv->status === 'PAID';

        $attachments = $inv->orders ? $inv->orders->map(function ($ord, $oIdx) {
            $itemNames = $ord->orderItems ? $ord->orderItems->map(fn($i) => $i->product->name ?? 'Komoditas')->join(', ') : '';
            $kg = (float) ($ord->orderItems ? $ord->orderItems->sum(fn($i) => $i->actual_net_weight ?? $i->ordered_qty) : 0);

            return [
                'no'           => str_pad($oIdx + 1, 2, '0', STR_PAD_LEFT),
                'sj'           => $ord->surat_jalan_number ?: ('SJ-' . $ord->id),
                'date'         => $ord->target_delivery_date ? $ord->target_delivery_date->format('d/m/Y') : ($ord->created_at ? $ord->created_at->format('d/m/Y') : '-'),
                'hub'          => 'Sentra Lembang',
                'commodity'    => $itemNames ?: 'Sayuran Segar Pasundan',
                'weight_label' => number_format($kg, 1, ',', '.') . ' kg',
                'amount_label' => 'Rp ' . number_format($ord->grand_total ?: $ord->estimated_total, 0, ',', '.'),
                'pod_chip'     => $ord->pod_photo_url ? 'POD Valid' : 'BA Sah',
                'ttd'          => $ord->received_by_name ?: 'Staff Receiving',
            ];
        })->all() : [];

        return [
            'no'                => str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
            'po'                => $inv->invoice_number,
            'printed'           => $inv->created_at ? $inv->created_at->format('d M Y') : '-',
            'period_label'      => ($inv->period_start ? $inv->period_start->format('d M') : '-') . ' - ' . ($inv->period_end ? $inv->period_end->format('d M Y') : '-'),
            'sj_chip'           => ($inv->orders ? $inv->orders->count() : 0) . ' Surat Jalan',
            'sj_chip_muted'     => false,
            'netto_value'       => number_format($totalNetto, 1, ',', '.'),
            'netto_unit'        => 'kg',
            'netto_weight'      => 600,
            'total_label'       => 'Rp ' . number_format($inv->grand_total, 0, ',', '.'),
            'total_weight'      => 700,
            'due'               => $inv->due_date ? $inv->due_date->format('d M Y') : '-',
            'due_muted'         => $isPaid,
            'due_struck'        => $isPaid,
            'due_note'          => $isPaid ? 'Sudah Lunas' : ($inv->due_date && $inv->due_date->isPast() ? 'Overdue' : 'Tempo 30 Hari'),
            'due_note_tone'     => $isPaid ? 'success' : 'default',
            'status'            => $statusText,
            'status_tone'       => $statusTone,
            'status_kind'       => $isPaid ? 'check' : 'dot',
            'status_raw'        => $inv->status,
            'row_tone'          => $isPaid ? 'plain' : 'active',
            'tempo_days'        => 30,
            'sj_count'          => $inv->orders ? $inv->orders->count() : 0,
            'netto_net_label'   => number_format($totalNetto, 1, ',', '.') . ' kg Netto Sah',
            'subtotal_label'    => 'Rp ' . number_format($inv->subtotal_amount ?: $inv->grand_total, 0, ',', '.'),
            'ppn_label'         => 'PPN DTP Hasil Pertanian (0%)',
            'ppn_value'         => 'Bebas PPN',
            'credit_label'      => 'Penyesuaian Mutu / Retur',
            'credit_value'      => '- Rp 0',
            'grand_total_label' => 'Rp ' . number_format($inv->grand_total, 0, ',', '.'),
            'attachments'       => $attachments,
            'actions'           => [
                [
                    'shape'   => 'label',
                    'label'   => $isPaid ? 'Lihat Bukti' : 'Bayar Sekarang',
                    'variant' => 'solid',
                    'icon'    => 'upload',
                    'href'    => route('klien.payment-proof', ['tagihan' => $inv->invoice_number]),
                ],
            ],
        ];
    })->values()->all();

    $archiveCounts = [
        'all'     => count($invoices),
        'unpaid'  => $rawInvoices->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->count(),
        'settled' => $rawInvoices->where('status', 'PAID')->count(),
        'tax'     => count($invoices),
    ];

    $pagination = [
        'page'        => 1,
        'per_page'    => max(1, count($invoices)),
        'total'       => count($invoices),
        'total_pages' => 1,
        'last_page'   => 1,
    ];
@endphp

@section('content')
    <div x-data="clientDocuments(@js([
        'invoices' => $invoices,
        'tabs' => $tabs,
        'counts' => $archiveCounts,
        'periods' => $periods,
        'pagination' => $pagination,
    ]))" class="flex flex-col gap-4">
        {{-- ------------------------------------------------------------------ --}}
        {{-- Kop halaman + aksi unduh rekap pajak & rekonsiliasi --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex min-w-0 flex-col gap-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="gpa-section-title text-ink">Pusat Dokumen &amp; Faktur Konsolidasi</h1>
                    <span
                        class="rounded-full bg-accent px-2.5 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[0.1em] text-ink ring-1 ring-inset ring-accent">
                        Faktur Resmi &amp; Berita Acara
                    </span>
                </div>
                <p class="max-w-[56rem] text-sm leading-5 text-ink-body">
                    Arsip digital dokumen penagihan bulanan, faktur pajak, dan surat jalan penimbangan sah PT Green Pasundan Agriculture.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('klien.documents.export') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-4 py-2 gpa-meta-lg font-medium uppercase tracking-[0.55px] text-ink-body shadow-sub transition-colors hover:bg-surface-disabled hover:text-ink">
                    <x-gpa.icon name="download" class="h-3 w-3 text-ink-body" />
                    <span>Rekap Tagihan (CSV)</span>
                </a>
                <a href="{{ route('klien.documents.print-rekap') }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 gpa-meta-lg font-bold uppercase tracking-[0.55px] text-white transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="printer" class="h-3 w-3 text-white" />
                    <span>Cetak Laporan</span>
                </a>

                {{-- Dropdown "Unggah Bukti": jalan pintas ke formulir bukti pembayaran --}}
                <div class="relative" @click.outside="proofMenuOpen = false" @keydown.escape.window="proofMenuOpen = false">
                    <button type="button" @click="toggleProofMenu()" :aria-expanded="proofMenuOpen.toString()"
                        aria-haspopup="true"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-ink shadow-sub transition-colors hover:bg-accent-deep">
                        <x-gpa.icon name="upload" class="h-3 w-3 text-ink" />
                        <span class="gpa-meta-lg font-bold uppercase tracking-[0.55px] text-ink">Unggah Bukti</span>
                        <span :class="proofMenuOpen ? 'rotate-180' : ''" class="transition-transform">
                            <x-gpa.icon name="chevron-down" class="h-3 w-3 text-ink" />
                        </span>
                    </button>

                    <div x-cloak x-show="proofMenuOpen" x-transition.origin.top.right
                        class="absolute right-0 z-30 mt-2 w-[19rem] overflow-hidden rounded-xl border border-line-soft bg-surface shadow-pop">
                        <a href="{{ route('klien.payment-proof') }}"
                            class="flex items-start gap-2.5 border-b border-line-soft px-3 py-2.5 transition-colors hover:bg-surface-shell">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent text-ink">
                                <x-gpa.icon name="upload" class="h-3.5 w-3.5" />
                            </span>
                            <span class="flex min-w-0 flex-col">
                                <span class="text-xs font-bold leading-4 text-ink">Unggah Bukti Bayar Tagihan</span>
                                <span class="text-[10px] leading-4 text-ink-body">Lampirkan bukti transfer bank</span>
                            </span>
                        </a>

                        <button type="button" @click="copySettlementAccount()"
                            class="flex w-full items-start gap-2.5 px-3 py-2.5 text-left transition-colors hover:bg-surface-shell">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-pill text-ink-body">
                                <x-gpa.icon name="copy" class="h-3.5 w-3.5" />
                            </span>
                            <span class="flex min-w-0 flex-col">
                                <span class="text-xs font-bold leading-4 text-ink">Salin Rekening BCA GPA</span>
                                <span class="text-[10px] leading-4 text-ink-body">BCA: 840-552-1920 (GPA Rekening Utama)</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Empat kartu metrik payable, settled, vault, adjustment --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="gpa-card flex flex-col justify-between gap-3 p-4">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-ink-body">Total Tagihan Tempo Aktif</h2>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-warning-soft text-warning-deep">
                        <x-gpa.icon name="clock" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-medium leading-4 text-ink-body">Outstanding Payable</p>
                    <p class="pb-1 text-2xl font-bold leading-8 text-ink">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</p>
                </div>
                <dl class="flex items-start justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <dt class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                        Status
                    </dt>
                    <dd class="text-right text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-ink">
                        {{ $unpaidAmount > 0 ? 'Menunggu Pelunasan' : 'Nihil / Lancar' }}
                    </dd>
                </dl>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-4">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-ink-body">Total Tagihan Lunas</h2>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-accent text-ink">
                        <x-gpa.icon name="check-circle" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-medium leading-4 text-ink-body">Settled Invoices</p>
                    <p class="pb-1 text-2xl font-bold leading-8 text-ink">Rp {{ number_format($settledAmount, 0, ',', '.') }}</p>
                </div>
                <dl class="flex items-start justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <dt class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                        Verifikasi
                    </dt>
                    <dd class="text-right text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-ink">
                        100% Tervalidasi
                    </dd>
                </dl>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-4">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-ink-body">Sisa Plafon Kredit B2B</h2>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-accent/50 text-ink">
                        <x-gpa.icon name="wallet" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-medium leading-4 text-ink-body">Credit Vault</p>
                    <p class="pb-1 text-2xl font-bold leading-8 text-ink">Rp {{ number_format(max(0, 50000000 - $unpaidAmount), 0, ',', '.') }}</p>
                </div>
                <dl class="flex items-start justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <dt class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                        Fasilitas
                    </dt>
                    <dd class="text-right text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-ink">
                        {{ ($user && $user->client_type === 'B2B_KONTRAK') ? 'TOP 30 Hari' : 'Reguler' }}
                    </dd>
                </dl>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-3 p-4">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-ink-body">Deviasi &amp; Rekonsiliasi Timbang</h2>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-surface-pill text-ink">
                        <x-gpa.icon name="scale" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-medium leading-4 text-ink-body">Netto Sah vs PO</p>
                    <p class="pb-1 text-2xl font-bold leading-8 text-ink">0.34%</p>
                </div>
                <dl class="flex items-start justify-between gap-2 border-t border-line-soft/60 pt-3">
                    <dt class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                        Toleransi Aman
                    </dt>
                    <dd class="text-right text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-success-deep">
                        &lt; 1.0% Sesuai SOP
                    </dd>
                </dl>
            </article>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Tab arsip dokumen + filter periode rekap --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="flex flex-col gap-3 border-b border-line-soft pb-1 xl:flex-row xl:items-center xl:justify-between">
            <div class="gpa-scroll-x overflow-hidden" role="tablist" aria-label="Arsip dokumen & faktur">
                <div class="flex items-center gap-2">
                    @foreach ($tabs as $tab)
                        <button type="button" role="tab" @click="selectTab(@js($tab['value']))"
                            :aria-selected="tab === @js($tab['value']) ? 'true' : 'false'"
                            class="inline-flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-3 transition-colors"
                            :class="tab === @js($tab['value'])
                                ? 'border-ink bg-surface-shell/50'
                                : 'border-transparent hover:border-line-soft hover:bg-surface-muted'">
                            <x-gpa.icon :name="$tab['icon']" class="h-3.5 w-3.5 shrink-0"
                                x-bind:class="tab === @js($tab['value']) ? 'text-ink' : 'text-ink-quiet'" />
                            <span class="text-[11px] uppercase leading-[14px] tracking-[0.55px]"
                                x-bind:class="tab === @js($tab['value']) ? 'font-bold text-ink' : 'font-medium text-ink-quiet'">
                                {{ $tab['label'] }}
                            </span>
                            @if (! empty($tab['badge']))
                                <span class="rounded bg-accent px-1.5 py-0.5 text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink">
                                    {{ $tab['badge'] }}
                                </span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <label class="flex shrink-0 items-center gap-2 pb-2 xl:pb-0">
                <span class="gpa-meta-lg text-ink-body">Periode:</span>
                <span class="relative inline-flex items-center">
                    <x-gpa.icon name="calendar" class="pointer-events-none absolute left-2 h-3.5 w-3.5 text-ink-body" />
                    <select x-model="period"
                        class="gpa-control h-7 w-[15.5rem] appearance-none rounded border-line-soft bg-surface py-1 pl-8 pr-2 text-xs font-medium text-ink">
                        @foreach ($periods as $period)
                            <option value="{{ $period['value'] }}">{{ $period['label'] }}</option>
                        @endforeach
                    </select>
                </span>
            </label>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Tabel faktur konsolidasi --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line-soft bg-surface-shell/50 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-2">
                    <x-gpa.icon name="invoice" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-ink" />
                    <div class="flex min-w-0 flex-col">
                        <h2 class="gpa-section-title text-ink" x-text="archiveLabel()">
                            {{ $tabs[0]['label'] }}
                        </h2>
                        <p class="gpa-meta-lg text-ink-body">Menampilkan tagihan periodik berlisensi resmi GPA Finance</p>
                        <p x-cloak x-show="tabNotice()" x-text="tabNotice()" class="pt-1 text-[9px] font-semibold uppercase leading-3 tracking-[0.12em] text-ink-quiet"></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex w-full items-center sm:w-64">
                        <span class="sr-only">Cari No. Faktur / SJ...</span>
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-3 h-3 w-3 text-ink-body" />
                        <input type="search" x-model.debounce.200ms="search" placeholder="Cari No. Faktur / SJ..."
                            class="gpa-control h-8 w-full rounded-lg border-line-soft bg-surface pl-8 pr-3 text-xs">
                    </label>
                    <button type="button" @click="resetFilters()" x-cloak x-show="hasFilters()"
                        class="inline-flex h-8 items-center rounded-lg border border-line-soft bg-surface px-2 transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="filter" class="h-3 w-3 text-ink-body" />
                        <span class="sr-only">Reset filter periode dan pencarian</span>
                    </button>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[68rem] border-collapse">
                    <caption class="sr-only">Daftar faktur konsolidasi tempo bulanan beserta netto riil, jatuh tempo, dan status pembayaran</caption>
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-pill">
                            <th scope="col" class="w-[3.25rem] px-4 py-3 text-left gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">No</th>
                            <th scope="col" class="w-[8.5rem] px-4 py-3 text-left gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">No. Faktur / Tanggal</th>
                            <th scope="col" class="w-[7.5rem] px-4 py-3 text-left gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">Periode &amp; Dokumen</th>
                            <th scope="col" class="w-[6.25rem] px-4 py-3 text-right gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">Bobot Netto</th>
                            <th scope="col" class="w-[7rem] px-4 py-3 text-right gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">Total Nilai (Rp)</th>
                            <th scope="col" class="w-[6.5rem] px-4 py-3 text-left gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">Jatuh Tempo</th>
                            <th scope="col" class="w-[10rem] px-4 py-3 text-left gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">Status Pembayaran</th>
                            <th scope="col" class="w-[9rem] px-4 py-3 text-center gpa-micro-bold uppercase tracking-[0.45px] text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr @click="selectInvoice(@js($invoice['po']))"
                                x-show="isVisible(@js($invoice))"
                                class="cursor-pointer border-b border-line-soft/70 transition-shadow"
                                :class="isSelected(@js($invoice['po'])) ? 'ring-2 ring-inset ring-accent' : ''"
                                @class([
                                    'border-l-4 border-ink bg-canvas' => $invoice['row_tone'] === 'active',
                                    'bg-surface' => $invoice['row_tone'] !== 'active',
                                    'opacity-80' => $invoice['row_tone'] === 'muted',
                                ])>
                                <td class="px-4 py-4 text-[11px] font-semibold tracking-[0.88px] text-ink-body">
                                    {{ $invoice['no'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold leading-4 text-ink">{{ $invoice['po'] }}</p>
                                    <p class="text-[10px] leading-4 text-ink-body">Cetak: {{ $invoice['printed'] }}</p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-xs font-medium leading-4 text-ink">{{ $invoice['period_label'] }}</p>
                                    <span @class([
                                        'mt-1 inline-block rounded px-1.5 py-0.5 text-[10px] leading-4',
                                        'bg-surface-pill font-semibold text-ink-body' => ! $invoice['sj_chip_muted'],
                                        'font-normal text-ink-quiet' => $invoice['sj_chip_muted'],
                                    ])>{{ $invoice['sj_chip'] }}</span>
                                </td>

                                <td class="px-4 py-4 text-right text-xs font-semibold leading-4 text-ink">
                                    {{ $invoice['netto_value'] }} {{ $invoice['netto_unit'] }}
                                </td>

                                <td class="px-4 py-4 text-right text-xs font-bold leading-4 text-ink">
                                    {{ $invoice['total_label'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <p @class([
                                        'text-xs font-medium leading-4',
                                        'text-ink' => ! $invoice['due_muted'],
                                        'text-ink-body' => $invoice['due_muted'] && ! $invoice['due_struck'],
                                        'text-ink-quiet' => $invoice['due_muted'],
                                        'line-through' => $invoice['due_struck'],
                                    ])>{{ $invoice['due'] }}</p>
                                    <p @class([
                                        'text-[10px] font-bold leading-4',
                                        'text-success-deep' => $invoice['due_note_tone'] === 'success',
                                        'text-ink-quiet' => $invoice['due_note_tone'] !== 'success',
                                    ])>{{ $invoice['due_note'] }}</p>
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] ring-1 ring-inset',
                                        'bg-accent text-success-ink ring-accent' => $invoice['status_tone'] === 'accent',
                                        'bg-warning-soft text-warning-deep ring-warning/40' => $invoice['status_tone'] === 'warning',
                                        'bg-surface-pill text-ink-quiet ring-line-board' => $invoice['status_tone'] === 'muted',
                                    ])>
                                        @if ($invoice['status_kind'] === 'dot')
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                        @elseif ($invoice['status_kind'] === 'check')
                                            <x-gpa.icon name="check" class="h-2.5 w-2.5 shrink-0" />
                                        @endif
                                        {{ $invoice['status'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @foreach ($invoice['actions'] as $action)
                                            <a href="{{ $action['href'] ?? route('klien.payment-proof', ['tagihan' => $invoice['po']]) }}"
                                                @click.stop
                                                class="inline-block rounded bg-ink px-2.5 py-1 text-[10px] font-bold uppercase leading-4 text-white transition-colors hover:bg-brand-deep">
                                                {{ $action['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p x-cloak x-show="visibleCount() === 0" class="px-6 py-10 text-center gpa-body text-ink-body">
                Tidak ada faktur yang cocok dengan filter aktif.
            </p>

            <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="gpa-micro-bold uppercase leading-3 tracking-[1.08px] text-ink-body" x-text="summaryLabel()">
                    Menampilkan {{ $pagination['per_page'] }} dari {{ $pagination['total'] }} total faktur konsolidasi (2026)
                </p>

                <nav class="flex items-center gap-1" aria-label="Navigasi halaman faktur">
                    <button type="button" @click="page > 1 && goToPage(page - 1)" :disabled="page === 1"
                        class="rounded bg-surface px-2 py-1 text-xs leading-4 text-ink-body outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-50">
                        Sebelumnya
                    </button>

                    <template x-for="number in pageNumbers()" :key="number">
                        <button type="button" @click="goToPage(number)"
                            class="rounded px-2 py-1 text-xs leading-4 transition-colors"
                            :class="page === number ? 'bg-ink font-bold text-white' : 'bg-surface-pill text-ink-body hover:bg-surface-disabled'"
                            :aria-current="page === number ? 'page' : null"
                            x-text="number"></button>
                    </template>

                    @if ($pagination['last_page'] > 3)
                        <span class="px-1 text-xs leading-4 text-ink-body" aria-hidden="true">...</span>
                    @endif

                    <button type="button" @click="goToPage(page + 1)" :disabled="page === lastPage"
                        class="rounded bg-surface px-2 py-1 text-xs leading-4 text-ink-body outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-50">
                        Selanjutnya
                    </button>
                </nav>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- Panel lampiran faktur resmi (berikutan dengan baris tabel terpilih) --}}
        {{-- ------------------------------------------------------------------ --}}
        @foreach ($invoices as $invoice)
            <section x-cloak x-show="isSelected(@js($invoice['po']))"
                class="gpa-card overflow-hidden ring-2 ring-inset ring-ink">
                <div class="flex flex-col gap-2 bg-brand p-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 flex-col gap-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="gpa-section-title text-white">Lampiran Rekap Surat Jalan &amp; Berita Acara Fisik</h2>
                            <span class="rounded bg-accent px-1.5 py-1 text-[9px] font-bold uppercase leading-3 text-ink">
                                Faktur Resmi GPA
                            </span>
                        </div>
                        <p class="text-[9px] font-semibold uppercase leading-3 tracking-[1.08px] text-accent/60">
                            No. Faktur:
                            <span class="text-accent">{{ $invoice['po'] }}</span>
                            &bull; Tempo {{ $invoice['tempo_days'] }} Hari
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('prints.invoice', ['invoice' => $invoice['po']]) }}" target="_blank"
                            title="Cetak Faktur Resmi A4"
                            class="inline-flex items-center gap-1 rounded-lg bg-ink px-2.5 py-1 text-white transition-colors hover:bg-brand-deep text-2xs font-bold uppercase">
                            <x-gpa.icon name="printer" class="h-3 w-3" />
                            Cetak Faktur A4
                        </a>
                        <a href="{{ route('prints.surat-jalan') }}" target="_blank"
                            title="Cetak Surat Jalan Fisik"
                            class="inline-flex items-center gap-1 rounded-lg bg-ink px-2.5 py-1 text-white transition-colors hover:bg-brand-deep text-2xs font-bold uppercase">
                            <x-gpa.icon name="file-text" class="h-3 w-3" />
                            Cetak SJ
                        </a>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-b border-line-soft bg-surface-pill px-4 py-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-medium leading-4 text-ink-body">
                        Rekap {{ $invoice['sj_count'] }} Surat Jalan Fisik (Terintegrasi Netto Timbangan Digital):
                    </p>
                    <p class="text-xs font-bold leading-4 text-ink">{{ $invoice['netto_net_label'] }}</p>
                </div>

                <div class="flex flex-col gap-2.5 p-4">
                    @foreach ($invoice['attachments'] as $item)
                        <article class="flex items-start justify-between gap-3 rounded-xl border border-line-soft bg-surface-pill p-2.5">
                            <div class="flex min-w-0 items-start gap-2">
                                <span class="flex h-6 w-7 shrink-0 items-center justify-center rounded bg-accent/50 text-sm font-bold leading-5 text-success-deep">
                                    {{ $item['no'] }}
                                </span>
                                <div class="flex min-w-0 flex-col">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="text-xs font-bold leading-4 text-ink">{{ $item['sj'] }}</p>
                                        <span class="inline-flex items-center gap-1 rounded bg-accent px-1 py-0.5">
                                            <x-gpa.icon name="check" class="h-2 w-2 text-success-ink" />
                                            <span class="text-[9px] font-bold leading-3 text-success-ink">{{ $item['pod_chip'] }}</span>
                                        </span>
                                    </div>
                                    <p class="pt-0.5 text-xs font-medium leading-4 text-ink-body">{{ $item['commodity'] }}</p>
                                    <p class="text-[11px] leading-5 text-ink-body">
                                        {{ $item['hub'] }} &bull; TTD: {{ $item['ttd'] }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-col items-end text-right">
                                <p class="text-xs font-bold leading-4 text-ink">{{ $item['amount_label'] }}</p>
                                <p class="text-xs font-semibold leading-4 text-success-deep">{{ $item['weight_label'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell p-4">
                    <dl class="flex flex-col gap-1.5 border-b border-line-soft pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">Subtotal {{ $invoice['sj_count'] }} Surat Jalan:</dt>
                            <dd class="text-xs font-semibold leading-4 text-ink">{{ $invoice['subtotal_label'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">{{ $invoice['ppn_label'] }}:</dt>
                            <dd class="text-xs leading-4 text-ink-body">{{ $invoice['ppn_value'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">{{ $invoice['credit_label'] }}:</dt>
                            <dd class="text-xs font-semibold leading-4 text-success-deep">{{ $invoice['credit_value'] }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 pt-1">
                            <dt class="text-base font-bold leading-6 text-ink">Total Tagihan Tempo (TOP 30D):</dt>
                            <dd class="rounded bg-ink px-2 py-0.5 text-base font-bold leading-6 text-white">
                                {{ $invoice['grand_total_label'] }}
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-3 rounded-xl border border-line-soft bg-surface p-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 flex-col">
                            <p class="text-[10px] font-bold uppercase leading-5 text-ink-body">Rekening Penerima Resmi</p>
                            <p class="text-xs font-bold leading-4 text-ink">Bank Central Asia (BCA)</p>
                            <p class="text-xs font-semibold leading-4 tracking-[0.6px] text-ink">840-552-1920</p>
                            <p class="text-[10px] leading-5 text-ink-body">PT Green Pasundan Agriculture</p>
                        </div>
                        <a href="{{ route('klien.payment-proof', ['tagihan' => $invoice['po']]) }}"
                            class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-accent px-3 py-1.5 text-ink transition-colors hover:bg-accent-deep">
                            <x-gpa.icon name="upload" class="h-3 w-3 text-ink" />
                            <span class="text-[11px] font-bold uppercase leading-[14px] tracking-[0.88px] text-ink">
                                Unggah Bukti Transfer
                            </span>
                        </a>
                    </div>
                </div>
            </section>
        @endforeach

        {{-- ------------------------------------------------------------------ --}}
        {{-- Jaminan validasi hukum & timbangan riil --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card mt-6 flex flex-col gap-3 p-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand">
                    <x-gpa.icon name="shield" class="h-6 w-6 text-accent" />
                </span>
                <div class="flex min-w-0 flex-col gap-0.5">
                    <h2 class="gpa-section-title text-ink">Jaminan Hukum &amp; Rekonsiliasi Faktur Sah GPA</h2>
                    <p class="max-w-[42rem] text-xs leading-4 text-ink-body">
                        Faktur konsolidasi tempo ini dijamin oleh Berita Acara timbang sah digital dan faktur pajak elektronik resmi.
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="contactFinance()"
                    class="rounded-lg border border-line-soft bg-surface px-3 py-1.5 text-[11px] font-semibold uppercase leading-[14px] tracking-[0.88px] text-ink transition-colors hover:bg-surface-muted">
                    Hubungi Finance GPA
                </button>
                <button type="button" @click="contactFinance()"
                    class="rounded-lg bg-ink px-3 py-1.5 text-[11px] font-bold uppercase leading-[14px] tracking-[0.88px] text-white transition-colors hover:bg-brand-deep">
                    Konfirmasi Pelunasan
                </button>
            </div>
        </section>
    </div>
@endsection
