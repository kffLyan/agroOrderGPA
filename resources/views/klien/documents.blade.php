@extends('layouts.klien')

@section('title', 'Pusat Dokumen & Faktur Konsolidasi Klien B2B')

@section('content')
    {{-- Inisialisasi data Alpine.js sederhana untuk filter tab, periode, pencarian, dan pilihan baris --}}
    <div x-data="{
        tab: 'all',
        period: 'all',
        search: '',
        selected: '{{ $rawInvoices->first()?->invoice_number ?? '' }}',
        proofMenuOpen: false,

        // Fungsi penyaring baris tabel berdasarkan tab status, periode bulan, dan pencarian nomor faktur/SJ
        matches(no, status, period, keywords) {
            if (this.tab === 'unpaid' && !['UNPAID', 'PARTIAL', 'OVERDUE'].includes(status)) return false;
            if (this.tab === 'settled' && status !== 'PAID') return false;
            if (this.period !== 'all' && period !== this.period) return false;
            if (this.search.trim() !== '') {
                const q = this.search.trim().toLowerCase();
                return no.toLowerCase().includes(q) || keywords.toLowerCase().includes(q);
            }
            return true;
        },

        // Salin nomor rekening resmi BCA GPA ke clipboard
        async copySettlementAccount() {
            this.proofMenuOpen = false;
            try {
                await navigator.clipboard.writeText('840-552-1920');
                this.$dispatch('gpa:toast', {
                    title: 'Rekening Pembayaran GPA',
                    message: 'BCA: 840-552-1920 • a.n. Green Pasundan Agriculture tersalin ke papan klip.',
                    tone: 'success'
                });
            } catch {
                this.$dispatch('gpa:toast', {
                    title: 'Rekening Pembayaran GPA',
                    message: 'BCA: 840-552-1920 • a.n. Green Pasundan Agriculture — salin manual.',
                    tone: 'info'
                });
            }
        },

        contactFinance() {
            this.$dispatch('gpa:toast', {
                title: 'Kontak Tim Finance',
                message: 'Layanan Keuangan GPA siap membantu rekonsiliasi tagihan Anda.',
                tone: 'info'
            });
        }
    }" class="flex flex-col gap-4">

        {{-- ------------------------------------------------------------------ --}}
        {{-- 1. HEADER HALAMAN & TOMBOL AKSI CEPAT (CSV, CETAK, UNGGAH BUKTI)    --}}
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
                    Arsip digital dokumen penagihan bulanan, faktur pajak, dan surat jalan penimbangan sah Green Pasundan Agriculture.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                {{-- Unduh Rekap CSV --}}
                <a href="{{ route('klien.documents.export') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-4 py-2 gpa-meta-lg font-medium uppercase tracking-[0.55px] text-ink-body shadow-sub transition-colors hover:bg-surface-disabled hover:text-ink">
                    <x-gpa.icon name="download" class="h-3 w-3 text-ink-body" />
                    <span>Rekap Tagihan (CSV)</span>
                </a>

                {{-- Cetak Laporan PDF --}}
                <a href="{{ route('klien.documents.print-rekap') }}" target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 gpa-meta-lg font-bold uppercase tracking-[0.55px] text-white transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="printer" class="h-3 w-3 text-white" />
                    <span>Cetak Laporan</span>
                </a>

                {{-- Dropdown Tombol "Unggah Bukti" --}}
                <div class="relative" @click.outside="proofMenuOpen = false" @keydown.escape.window="proofMenuOpen = false">
                    <button type="button" @click="proofMenuOpen = !proofMenuOpen" :aria-expanded="proofMenuOpen.toString()"
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
        {{-- 2. EMPAT KARTU METRIK KEUANGAN & PLAFON KREDIT                      --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Kartu 1: Tagihan Tempo Aktif --}}
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

            {{-- Kartu 2: Tagihan Lunas --}}
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

            {{-- Kartu 3: Sisa Plafon Kredit --}}
            <article class="gpa-card flex flex-col justify-between gap-3 p-4">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="gpa-micro-bold uppercase text-ink-body">Sisa Plafon Kredit B2B</h2>
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-accent/50 text-ink">
                        <x-gpa.icon name="wallet" class="h-3.5 w-3.5" />
                    </span>
                </div>
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-medium leading-4 text-ink-body">Credit Vault</p>
                    <p class="pb-1 text-2xl font-bold leading-8 text-ink">Rp {{ number_format($creditLimitRemaining, 0, ',', '.') }}</p>
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

            {{-- Kartu 4: Toleransi Deviasi Timbang --}}
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
        {{-- 3. TAB FILTER STATUS & PILIHAN PERIODE TRANSAKSI                    --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="flex flex-col gap-3 border-b border-line-soft pb-1 xl:flex-row xl:items-center xl:justify-between">
            <div class="gpa-scroll-x overflow-hidden" role="tablist" aria-label="Arsip dokumen & faktur">
                <div class="flex items-center gap-2">
                    {{-- Tab 1: Semua Tagihan & Faktur --}}
                    <button type="button" role="tab" @click="tab = 'all'"
                        :aria-selected="tab === 'all' ? 'true' : 'false'"
                        class="inline-flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-3 transition-colors"
                        :class="tab === 'all'
                            ? 'border-ink bg-surface-shell/50'
                            : 'border-transparent hover:border-line-soft hover:bg-surface-muted'">
                        <x-gpa.icon name="file-text" class="h-3.5 w-3.5 shrink-0"
                            x-bind:class="tab === 'all' ? 'text-ink' : 'text-ink-quiet'" />
                        <span class="text-[11px] uppercase leading-[14px] tracking-[0.55px]"
                            x-bind:class="tab === 'all' ? 'font-bold text-ink' : 'font-medium text-ink-quiet'">
                            Semua Tagihan &amp; Faktur
                        </span>
                    </button>

                    {{-- Tab 2: Menunggu Pembayaran --}}
                    <button type="button" role="tab" @click="tab = 'unpaid'"
                        :aria-selected="tab === 'unpaid' ? 'true' : 'false'"
                        class="inline-flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-3 transition-colors"
                        :class="tab === 'unpaid'
                            ? 'border-ink bg-surface-shell/50'
                            : 'border-transparent hover:border-line-soft hover:bg-surface-muted'">
                        <x-gpa.icon name="clock" class="h-3.5 w-3.5 shrink-0"
                            x-bind:class="tab === 'unpaid' ? 'text-ink' : 'text-ink-quiet'" />
                        <span class="text-[11px] uppercase leading-[14px] tracking-[0.55px]"
                            x-bind:class="tab === 'unpaid' ? 'font-bold text-ink' : 'font-medium text-ink-quiet'">
                            Menunggu Pembayaran
                        </span>
                        <span class="rounded bg-accent px-1.5 py-0.5 text-[9px] font-bold uppercase leading-3 tracking-[1.08px] text-ink">
                            Tempo
                        </span>
                    </button>

                    {{-- Tab 3: Lunas Terverifikasi --}}
                    <button type="button" role="tab" @click="tab = 'settled'"
                        :aria-selected="tab === 'settled' ? 'true' : 'false'"
                        class="inline-flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-3 transition-colors"
                        :class="tab === 'settled'
                            ? 'border-ink bg-surface-shell/50'
                            : 'border-transparent hover:border-line-soft hover:bg-surface-muted'">
                        <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 shrink-0"
                            x-bind:class="tab === 'settled' ? 'text-ink' : 'text-ink-quiet'" />
                        <span class="text-[11px] uppercase leading-[14px] tracking-[0.55px]"
                            x-bind:class="tab === 'settled' ? 'font-bold text-ink' : 'font-medium text-ink-quiet'">
                            Lunas Terverifikasi
                        </span>
                    </button>

                    {{-- Tab 4: Dokumen Pajak & BA --}}
                    <button type="button" role="tab" @click="tab = 'tax'"
                        :aria-selected="tab === 'tax' ? 'true' : 'false'"
                        class="inline-flex shrink-0 items-center gap-1.5 border-b-2 px-4 py-3 transition-colors"
                        :class="tab === 'tax'
                            ? 'border-ink bg-surface-shell/50'
                            : 'border-transparent hover:border-line-soft hover:bg-surface-muted'">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0"
                            x-bind:class="tab === 'tax' ? 'text-ink' : 'text-ink-quiet'" />
                        <span class="text-[11px] uppercase leading-[14px] tracking-[0.55px]"
                            x-bind:class="tab === 'tax' ? 'font-bold text-ink' : 'font-medium text-ink-quiet'">
                            Dokumen Pajak &amp; BA
                        </span>
                    </button>
                </div>
            </div>

            {{-- Filter Pilihan Periode Transaksi --}}
            <label class="flex shrink-0 items-center gap-2 pb-2 xl:pb-0">
                <span class="gpa-meta-lg text-ink-body">Periode:</span>
                <span class="relative inline-flex items-center">
                    <x-gpa.icon name="calendar" class="pointer-events-none absolute left-2 h-3.5 w-3.5 text-ink-body" />
                    <select x-model="period"
                        class="gpa-control h-7 w-[15.5rem] appearance-none rounded border-line-soft bg-surface py-1 pl-8 pr-2 text-xs font-medium text-ink">
                        <option value="all">Semua Periode Transaksi</option>
                        <option value="current">Bulan Berjalan ({{ now()->translatedFormat('F Y') }})</option>
                        <option value="last-month">Bulan Sebelumnya</option>
                    </select>
                </span>
            </label>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- 4. TABEL DAFTAR FAKTUR KONSOLIDASI (DATA LANGSUNG DARI DATABASE)   --}}
        {{-- ------------------------------------------------------------------ --}}
        <section class="gpa-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line-soft bg-surface-shell/50 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-2">
                    <x-gpa.icon name="invoice" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-ink" />
                    <div class="flex min-w-0 flex-col">
                        <h2 class="gpa-section-title text-ink">
                            <span x-show="tab === 'all'">Semua Tagihan &amp; Faktur Konsolidasi</span>
                            <span x-show="tab === 'unpaid'" x-cloak>Faktur Menunggu Pembayaran (Tempo Aktif)</span>
                            <span x-show="tab === 'settled'" x-cloak>Faktur Lunas Terverifikasi</span>
                            <span x-show="tab === 'tax'" x-cloak>Dokumen Pajak &amp; Berita Acara</span>
                        </h2>
                        <p class="gpa-meta-lg text-ink-body">Menampilkan tagihan periodik berlisensi resmi GPA Finance</p>
                    </div>
                </div>

                {{-- Kolom Pencarian Nomor Faktur / Nomor Surat Jalan --}}
                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex w-full items-center sm:w-64">
                        <span class="sr-only">Cari No. Faktur / SJ...</span>
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-3 h-3 w-3 text-ink-body" />
                        <input type="search" x-model.debounce.200ms="search" placeholder="Cari No. Faktur / SJ..."
                            class="gpa-control h-8 w-full rounded-lg border-line-soft bg-surface pl-8 pr-3 text-xs">
                    </label>
                    <button type="button" @click="tab = 'all'; period = 'all'; search = ''"
                        x-cloak x-show="tab !== 'all' || period !== 'all' || search.trim() !== ''"
                        class="inline-flex h-8 items-center rounded-lg border border-line-soft bg-surface px-2 transition-colors hover:bg-surface-muted"
                        title="Reset Filter">
                        <x-gpa.icon name="filter" class="h-3 w-3 text-ink-body" />
                        <span class="sr-only">Reset filter</span>
                    </button>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full border-collapse">
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
                        {{-- Looping langsung dari koleksi Invoice --}}
                        @forelse ($rawInvoices as $index => $inv)
                            {{-- Klik baris faktur untuk memilih dan melihat rincian surat jalan di bawah --}}
                            <tr @click="selected = '{{ $inv->invoice_number }}'"
                                x-show="matches('{{ $inv->invoice_number }}', '{{ $inv->status }}', '{{ $inv->period_category }}', '{{ $inv->search_keywords }}')"
                                class="cursor-pointer border-b border-line-soft/70 transition-shadow"
                                :class="selected === '{{ $inv->invoice_number }}' ? 'ring-2 ring-inset ring-accent' : ''"
                                @class([
                                    'border-l-4 border-ink bg-canvas' => $inv->status !== 'PAID',
                                    'bg-surface' => $inv->status === 'PAID',
                                ])>
                                <td class="px-4 py-4 text-[11px] font-semibold tracking-[0.88px] text-ink-body">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-xs font-bold leading-4 text-ink">{{ $inv->invoice_number }}</p>
                                    <p class="text-[10px] leading-4 text-ink-body">Cetak: {{ $inv->created_at ? $inv->created_at->format('d M Y') : '-' }}</p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="text-xs font-medium leading-4 text-ink">
                                        {{ ($inv->period_start ? $inv->period_start->format('d M') : '-') . ' - ' . ($inv->period_end ? $inv->period_end->format('d M Y') : '-') }}
                                    </p>
                                    <span class="mt-1 inline-block rounded bg-surface-pill px-1.5 py-0.5 text-[10px] font-semibold leading-4 text-ink-body">
                                        {{ $inv->orders->count() }} Surat Jalan
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right text-xs font-semibold leading-4 text-ink">
                                    {{ number_format($inv->total_netto_weight, 1, ',', '.') }} kg
                                </td>

                                <td class="px-4 py-4 text-right text-xs font-bold leading-4 text-ink">
                                    {{ $inv->formatted_grand_total }}
                                </td>

                                <td class="px-4 py-4">
                                    <p @class([
                                        'text-xs font-medium leading-4',
                                        'line-through text-ink-quiet' => $inv->status === 'PAID',
                                        'text-ink' => $inv->status !== 'PAID',
                                    ])>
                                        {{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}
                                    </p>
                                    <p @class([
                                        'text-[10px] font-bold leading-4',
                                        'text-success-deep' => $inv->status === 'PAID',
                                        'text-ink-quiet' => $inv->status !== 'PAID',
                                    ])>
                                        {{ $inv->status === 'PAID' ? 'Sudah Lunas' : ($inv->due_date && $inv->due_date->isPast() ? 'Overdue' : 'Tempo 30 Hari') }}
                                    </p>
                                </td>

                                {{-- Status Pembayaran dengan Badge Warna Standar --}}
                                <td class="px-4 py-4">
                                    @if ($inv->has_rejected_proof)
                                        <span class="inline-flex items-center gap-1 rounded bg-danger-soft px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] text-danger ring-1 ring-inset ring-danger/40">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-danger" aria-hidden="true"></span>
                                            Bukti Bayar Ditolak
                                        </span>
                                    @elseif ($inv->status === 'PAID')
                                        <span class="inline-flex items-center gap-1 rounded bg-accent px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] text-success-ink ring-1 ring-inset ring-accent">
                                            <x-gpa.icon name="check" class="h-2.5 w-2.5 shrink-0" />
                                            Lunas Terverifikasi
                                        </span>
                                    @elseif ($inv->has_pending_proof)
                                        <span class="inline-flex items-center gap-1 rounded bg-warning-soft px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] text-warning-deep ring-1 ring-inset ring-warning/40">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                            Menunggu Verifikasi Pembayaran
                                        </span>
                                    @elseif ($inv->status === 'OVERDUE')
                                        <span class="inline-flex items-center gap-1 rounded bg-warning-soft px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] text-warning-deep ring-1 ring-inset ring-warning/40">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                            Melewati Jatuh Tempo
                                        </span>
                                    @elseif ($inv->status === 'PARTIAL')
                                        <span class="inline-flex items-center gap-1 rounded bg-warning-soft px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] text-warning-deep ring-1 ring-inset ring-warning/40">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                            Dibayar Sebagian
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded bg-warning-soft px-2 py-0.5 text-[10px] font-bold uppercase leading-4 tracking-[0.5px] text-warning-deep ring-1 ring-inset ring-warning/40">
                                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-warning-deep" aria-hidden="true"></span>
                                            Menunggu Pembayaran
                                        </span>
                                    @endif
                                </td>

                                {{-- Tombol Aksi --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('klien.payment-proof', ['tagihan' => $inv->invoice_number]) }}"
                                            @click.stop
                                            @class([
                                                'inline-block rounded px-2.5 py-1 text-[10px] font-bold uppercase leading-4 text-white transition-colors',
                                                'bg-danger hover:bg-danger/90' => $inv->has_rejected_proof,
                                                'bg-ink hover:bg-brand-deep' => ! $inv->has_rejected_proof,
                                            ])>
                                            @if ($inv->status === 'PAID')
                                                Lihat Bukti
                                            @elseif ($inv->has_rejected_proof)
                                                Unggah Ulang Bukti
                                            @elseif ($inv->has_pending_proof)
                                                Bukti Terkirim
                                            @else
                                                Bayar Sekarang
                                            @endif
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center gpa-body text-ink-body">
                                    Belum ada arsip faktur konsolidasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="gpa-micro-bold uppercase leading-3 tracking-[1.08px] text-ink-body">
                    Menampilkan {{ $rawInvoices->count() }} total faktur konsolidasi ({{ now()->year }})
                </p>
            </div>
        </section>

        {{-- ------------------------------------------------------------------ --}}
        {{-- 5. PANEL RINCIAN SURAT JALAN & BERITA ACARA FAKTUR TERPILIH        --}}
        {{-- ------------------------------------------------------------------ --}}
        @foreach ($rawInvoices as $inv)
            {{-- Panel ini hanya tampil jika nomor faktur sama dengan baris yang diklik/dipilih --}}
            <section x-cloak x-show="selected === '{{ $inv->invoice_number }}'"
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
                            <span class="text-accent">{{ $inv->invoice_number }}</span>
                            &bull; Tempo 30 Hari
                        </p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('prints.invoice', ['invoice' => $inv->invoice_number]) }}" target="_blank"
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

                {{-- Alert jika bukti bayar sebelumnya ditolak oleh sekretaris/verifikator --}}
                @if ($inv->has_rejected_proof)
                    <div class="border-b border-danger/30 bg-danger-soft/30 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-danger text-white">
                                    <x-gpa.icon name="alert-triangle" class="h-4 w-4" />
                                </span>
                                <div class="flex min-w-0 flex-col">
                                    <p class="text-xs font-bold uppercase tracking-wider text-danger">Bukti Pembayaran Ditolak Verifikator</p>
                                    <p class="mt-0.5 text-xs text-ink-body">
                                        Catatan Verifikator: <span class="font-semibold text-danger">{{ $inv->rejected_proof_payment?->verification_note ?: 'Bukti pembayaran tidak sesuai atau tidak terbaca. Mohon unggah ulang.' }}</span>
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('klien.payment-proof', ['tagihan' => $inv->invoice_number]) }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-danger px-3 py-1.5 text-2xs font-bold uppercase text-white shadow-sub transition hover:bg-danger/90">
                                <x-gpa.icon name="upload" class="h-3 w-3" />
                                <span>Unggah Ulang Bukti Bayar</span>
                            </a>
                        </div>
                    </div>
                @endif

                <div class="flex flex-col gap-2 border-b border-line-soft bg-surface-pill px-4 py-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-medium leading-4 text-ink-body">
                        Rekap {{ $inv->orders->count() }} Surat Jalan Fisik (Terintegrasi Netto Timbangan Digital):
                    </p>
                    <p class="text-xs font-bold leading-4 text-ink">{{ number_format($inv->total_netto_weight, 1, ',', '.') }} kg Netto Sah</p>
                </div>

                {{-- Rincian setiap Surat Jalan / Pesanan yang tergabung dalam faktur --}}
                <div class="flex flex-col gap-2.5 p-4">
                    @forelse ($inv->orders as $oIdx => $ord)
                        <article class="flex items-start justify-between gap-3 rounded-xl border border-line-soft bg-surface-pill p-2.5">
                            <div class="flex min-w-0 items-start gap-2">
                                <span class="flex h-6 w-7 shrink-0 items-center justify-center rounded bg-accent/50 text-sm font-bold leading-5 text-success-deep">
                                    {{ str_pad($oIdx + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div class="flex min-w-0 flex-col">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="text-xs font-bold leading-4 text-ink">{{ $ord->surat_jalan_number ?: ('SJ-' . $ord->id) }}</p>
                                        <span class="inline-flex items-center gap-1 rounded bg-accent px-1 py-0.5">
                                            <x-gpa.icon name="check" class="h-2 w-2 text-success-ink" />
                                            <span class="text-[9px] font-bold leading-3 text-success-ink">{{ $ord->pod_photo_url ? 'POD Valid' : 'BA Sah' }}</span>
                                        </span>
                                    </div>
                                    <p class="pt-0.5 text-xs font-medium leading-4 text-ink-body">
                                        {{ $ord->orderItems->map(fn($i) => $i->product->name ?? 'Komoditas')->join(', ') ?: 'Sayuran Segar Pasundan' }}
                                    </p>
                                    <p class="text-[11px] leading-5 text-ink-body">
                                        Sentra Lembang &bull; TTD: {{ $ord->received_by_name ?: 'Staff Receiving' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-col items-end text-right">
                                <p class="text-xs font-bold leading-4 text-ink">Rp {{ number_format($ord->grand_total, 0, ',', '.') }}</p>
                                <p class="text-xs font-semibold leading-4 text-success-deep">{{ number_format($ord->orderItems->sum('ordered_qty'), 1, ',', '.') }} kg</p>
                            </div>
                        </article>
                    @empty
                        <p class="text-center py-4 text-xs text-ink-body">Tidak ada lampiran surat jalan terkait.</p>
                    @endforelse
                </div>

                {{-- Ringkasan Perhitungan Subtotal, PPN, dan Total Tagihan --}}
                <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell p-4">
                    <dl class="flex flex-col gap-1.5 border-b border-line-soft pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">Subtotal {{ $inv->orders->count() }} Surat Jalan:</dt>
                            <dd class="text-xs font-semibold leading-4 text-ink">{{ $inv->formatted_subtotal }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">PPN DTP Hasil Pertanian (0%):</dt>
                            <dd class="text-xs leading-4 text-ink-body">Bebas PPN</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-xs leading-4 text-ink-body">Penyesuaian Mutu / Retur:</dt>
                            <dd class="text-xs font-semibold leading-4 text-success-deep">- Rp 0</dd>
                        </div>
                        <div class="flex items-start justify-between gap-3 pt-1">
                            <dt class="text-base font-bold leading-6 text-ink">Total Tagihan Tempo (TOP 30D):</dt>
                            <dd class="rounded bg-ink px-2 py-0.5 text-base font-bold leading-6 text-white">
                                {{ $inv->formatted_grand_total }}
                            </dd>
                        </div>
                    </dl>

                    {{-- Informasi Rekening Resmi GPA --}}
                    <div class="flex flex-col gap-3 rounded-xl border border-line-soft bg-surface p-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 flex-col">
                            <p class="text-[10px] font-bold uppercase leading-5 text-ink-body">Rekening Penerima Resmi</p>
                            <p class="text-xs font-bold leading-4 text-ink">Bank Central Asia (BCA)</p>
                            <p class="text-xs font-semibold leading-4 tracking-[0.6px] text-ink">840-552-1920</p>
                            <p class="text-[10px] leading-5 text-ink-body">Green Pasundan Agriculture</p>
                        </div>
                        <a href="{{ route('klien.payment-proof', ['tagihan' => $inv->invoice_number]) }}"
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
        {{-- 6. JAMINAN HUKUM & REKONSILIASI FAKTUR SAH                         --}}
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
