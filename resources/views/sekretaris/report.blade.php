@extends('layouts.sekretaris')

@section('title', 'Rekap Laporan')

@@php
    $journalRows = $journalRows ?? [];
    $totalEstimate = $totalEstimate ?? 0;
    $totalNet = $totalNet ?? 0;
    $totalValue = $totalValue ?? 0;
    $overallDeviation = $overallDeviation ?? 0;
    $hash = 'LAP-GPA-' . date('Ym') . '-VERIF';

    $filterConfig = [
        ['key' => 'period', 'value' => 'Bulan Berjalan'],
        ['key' => 'client', 'value' => 'Semua Pembeli & Mitra B2B'],
        ['key' => 'commodity', 'value' => 'Semua Sayuran Segar'],
        ['key' => 'payment', 'value' => 'Semua Status Tagihan'],
    ];

    $tabConfig = [
        ['key' => 'sales', 'label' => 'Rekapitulasi Penjualan & Pesanan'],
        ['key' => 'weighing', 'label' => 'Volume & Analisis Timbangan'],
        ['key' => 'dispatch', 'label' => 'Surat Jalan & Pengiriman Supir'],
        ['key' => 'aging', 'label' => 'Pemantauan Umur Tagihan (Tempo)'],
    ];
@endphp

@section('content')
    <div class="space-y-6" x-data="secretaryReports(@js($journalRows), @js($filterConfig), @js($tabConfig), {{ count($journalRows) }}, @js($hash))"
        @gpa:search.window="query = $event.detail">
        <section class="flex flex-col gap-5 border-b border-line-board pb-6 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                    <x-gpa.icon name="chart" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    Rekapitulasi Laporan
                </p>

                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Laporan Rekapitulasi Operasional,<br />
                    Pengiriman Sayur &amp; Penjualan
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-5 text-ink-body">
                    Rekapitulasi data transaksi harian, volume timbangan nyata, dan surat jalan pengiriman untuk pelaporan resmi ke Direktur.
                </p>
            </div>

            <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center xl:flex-col xl:items-stretch">
                <div class="flex flex-col gap-2 sm:flex-row">
                    <button type="button" @click="act('sync')"
                        class="inline-flex items-center gap-2 rounded-xl bg-surface px-4 py-2.5 outline outline-1 outline-line-board transition-colors hover:bg-surface-shell gpa-meta-lg font-bold tracking-[0.88px] text-ink">
                        <x-gpa.icon name="refresh" class="h-4 w-4 shrink-0 text-success-deep" />
                        Segarkan Data Transaksi
                    </button>
                    <button type="button" @click="act('export')"
                        class="inline-flex items-center gap-2 rounded-xl bg-surface px-4 py-2.5 outline outline-1 outline-line-board transition-colors hover:bg-surface-shell gpa-meta-lg font-bold tracking-[0.88px] text-ink">
                        <x-gpa.icon name="download" class="h-4 w-4 shrink-0 text-success-deep" />
                        Unduh Rekap (CSV / Excel)
                    </button>
                </div>

                <button type="button" @click="act('print')"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand px-4 py-2.5 outline outline-1 outline-accent-deep transition-colors hover:bg-brand-deep gpa-meta-lg font-bold tracking-[0.88px] text-accent">
                    <x-gpa.icon name="printer" class="h-4 w-4 shrink-0" />
                    Cetak Laporan Resmi (PDF)
                </button>
            </div>
        </section>

        <section class="gpa-panel flex flex-col gap-4 p-4 shadow-card xl:flex-row xl:items-end xl:justify-between">
            <div class="grid flex-1 min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <label class="block min-w-0" for="filter-period">
                    <span class="mb-1.5 block truncate gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                        Periode Laporan
                    </span>
                    <div class="relative">
                        <select id="filter-period" x-model="selected.period"
                            class="block h-10 w-full appearance-none rounded-lg border border-line-board bg-surface pl-3 pr-8 text-xs font-semibold text-ink transition-colors hover:border-brand focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                            <option value="Bulan Berjalan">Bulan Berjalan</option>
                            <option value="Minggu Ini">Minggu Ini</option>
                            <option value="Hari Ini">Hari Ini</option>
                            <option value="Bulan Lalu">Bulan Lalu</option>
                        </select>
                        <x-gpa.icon name="chevron-down"
                            class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                    </div>
                </label>

                <label class="block min-w-0" for="filter-client">
                    <span class="mb-1.5 block truncate gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                        Kategori Pembeli
                    </span>
                    <div class="relative">
                        <select id="filter-client" x-model="selected.client"
                            class="block h-10 w-full appearance-none rounded-lg border border-line-board bg-surface pl-3 pr-8 text-xs font-semibold text-ink transition-colors hover:border-brand focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                            <option value="Semua Pembeli &amp; Mitra B2B">Semua Pembeli &amp; Mitra B2B</option>
                            <option value="Hotel &amp; Restoran">Hotel &amp; Restoran</option>
                            <option value="Katering Industri">Katering Industri</option>
                            <option value="Supermarket &amp; Retail">Supermarket &amp; Retail</option>
                        </select>
                        <x-gpa.icon name="chevron-down"
                            class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                    </div>
                </label>

                <label class="block min-w-0" for="filter-commodity">
                    <span class="mb-1.5 block truncate gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                        Jenis Sayur
                    </span>
                    <div class="relative">
                        <select id="filter-commodity" x-model="selected.commodity"
                            class="block h-10 w-full appearance-none rounded-lg border border-line-board bg-surface pl-3 pr-8 text-xs font-semibold text-ink transition-colors hover:border-brand focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                            <option value="Semua Sayuran Segar">Semua Sayuran Segar</option>
                            <option value="Selada Romaine">Selada Romaine</option>
                            <option value="Tomat Beef">Tomat Beef</option>
                            <option value="Brokoli Super">Brokoli Super</option>
                            <option value="Bawang Merah">Bawang Merah</option>
                        </select>
                        <x-gpa.icon name="chevron-down"
                            class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                    </div>
                </label>

                <label class="block min-w-0" for="filter-payment">
                    <span class="mb-1.5 block truncate gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                        Status Tagihan
                    </span>
                    <div class="relative">
                        <select id="filter-payment" x-model="selected.payment"
                            class="block h-10 w-full appearance-none rounded-lg border border-line-board bg-surface pl-3 pr-8 text-xs font-semibold text-ink transition-colors hover:border-brand focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                            <option value="Semua Status Tagihan">Semua Status Tagihan</option>
                            <option value="Lunas">Lunas</option>
                            <option value="Tempo 14 Hari">Tempo 14 Hari</option>
                            <option value="Tempo 30 Hari">Tempo 30 Hari</option>
                            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                        </select>
                        <x-gpa.icon name="chevron-down"
                            class="pointer-events-none absolute right-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                    </div>
                </label>
            </div>

            <div class="flex shrink-0 items-center gap-2 self-end">
                <button type="button" @click="applyFilters()"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-accent-deep px-4 gpa-micro-bold uppercase tracking-[1.08px] text-ink shadow-sub transition-colors hover:bg-accent outline outline-1 outline-success-deep">
                    <x-gpa.icon name="filter" class="h-3.5 w-3.5 shrink-0" />
                    Terapkan Filter
                </button>

                <button type="button" @click="resetFilters()"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-surface-shell px-3.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body transition-colors hover:bg-surface-track hover:text-ink outline outline-1 outline-line-board">
                    <x-gpa.icon name="refresh" class="h-3.5 w-3.5 shrink-0" />
                    Reset
                </button>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        TOTAL BERAT SAYUR TERKIRIM
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/60">
                        <x-gpa.icon name="package" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-2xl font-bold tracking-[-0.01em] text-ink">
                            {{ number_format(($totalNet ?? 0) / 1000, 2, ',', '.') }}
                        </span>
                        <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-accent text-success-deep outline outline-1 outline-success-deep">
                            Stabil
                        </span>
                    </p>
                    <p class="mt-1 gpa-note text-ink-body">Ton &bull; {{ count($journalRows) }} pesanan sayur terkirim</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">Target Bulanan</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">100% Tercapai</p>
                </div>
            </article>

            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        TOTAL NILAI PENJUALAN
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-brand">
                        <x-gpa.icon name="invoice" class="h-[15px] w-[15px] shrink-0 text-accent" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-2xl font-bold tracking-[-0.01em] text-brand">
                            Rp {{ number_format($totalValue ?? 0, 0, ',', '.') }}
                        </span>
                        <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-brand text-accent outline outline-1 outline-accent-deep">
                            Netto Riil
                        </span>
                    </p>
                    <p class="mt-1 gpa-note text-ink-body">Dihitung dari timbangan riil</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">{{ count($journalRows) }} Transaksi</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Tercatat Resmi</p>
                </div>
            </article>

            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        KETEPATAN PENGIRIMAN
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-success-soft/50">
                        <x-gpa.icon name="truck" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-2xl font-bold tracking-[-0.01em] text-success-deep">
                            98.5%
                        </span>
                        <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-surface-track text-ink outline outline-1 outline-line-board">
                            Lancar
                        </span>
                    </p>
                    <p class="mt-1 gpa-note text-ink-body">Tepat Waktu &bull; Sayur tiba pagi hari sesuai jadwal</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">Jadwal Tiba</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">0 Komplain</p>
                </div>
            </article>

            <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                        RATA-RATA SUSUT TIMBANGAN
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-warning-soft/50">
                        <x-gpa.icon name="scale" class="h-[15px] w-[15px] shrink-0 text-warning-caution" />
                    </span>
                </div>

                <div>
                    <p class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-2xl font-bold tracking-[-0.01em] text-warning-caution">
                            {{ abs($overallDeviation ?? 0) }}%
                        </span>
                        <span class="inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] bg-warning-soft text-warning-deep outline outline-1 outline-warning/30">
                            Aman
                        </span>
                    </p>
                    <p class="mt-1 gpa-note text-ink-body">Susut &bull; Susut alami pemilahan daun segar</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">Batas Aman</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">Di Bawah 1.0%</p>
                </div>
            </article>
        </div>

        <section class="flex flex-col gap-4 rounded-2xl bg-surface-shell p-4 outline outline-1 outline-line-board/60 xl:flex-row">
            <div class="flex shrink-0 flex-col justify-between gap-4 rounded-xl bg-brand p-6 outline outline-1 outline-success-deep xl:w-[299px]">
                <div>
                    <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0" />
                        Prinsip Kepatuhan
                    </p>

                    <h2 class="mt-3 font-sans text-lg font-bold leading-6 text-white">Standar Transparansi &amp; Pencatatan</h2>

                    <p class="mt-2 text-xs leading-5 text-accent/80">
                        Aturan pencatatan untuk memastikan kejujuran timbangan antara petani, gudang sentra, dan pelanggan.
                    </p>
                </div>

                <p class="border-t border-ink/40 pt-3 font-mono text-2xs font-semibold tracking-[0.88px] text-accent">
                    Sistem AgroOrder GPA — Data Terverifikasi
                </p>
            </div>

            <div class="grid flex-1 gap-3 lg:grid-cols-3">
                <article class="flex flex-col justify-between gap-3 rounded-xl bg-surface p-4 shadow-card">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                Aturan 1
                            </span>
                            <span class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">Timbangan Nyata</span>
                        </div>

                        <h3 class="mt-3 font-sans text-base font-bold leading-5 text-ink">Tagihan Sesuai Berat Nyata</h3>

                        <p class="mt-1.5 gpa-note leading-5 text-ink-body">Nilai tagihan wajib dihitung berdasarkan hasil timbangan bersih setelah daun kuning dipisahkan di gudang.</p>
                    </div>

                    <p class="flex items-start gap-2 border-t border-line-soft pt-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-success-deep">
                        <x-gpa.icon name="check-circle" class="mt-px h-3.5 w-3.5 shrink-0" />
                        Semua pesanan merujuk ke timbangan gudang
                    </p>
                </article>

                <article class="flex flex-col justify-between gap-3 rounded-xl bg-surface p-4 shadow-card">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                Aturan 2
                            </span>
                            <span class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">Toleransi Wajar</span>
                        </div>

                        <h3 class="mt-3 font-sans text-base font-bold leading-5 text-ink">Batas Selisih Susut Sayur</h3>

                        <p class="mt-1.5 gpa-note leading-5 text-ink-body">Penyusutan daun segar wajar dipantau sistem. Jika melebihi batas 2.5%, sistem memberikan peringatan untuk evaluasi panen.</p>
                    </div>

                    <p class="flex items-start gap-2 border-t border-line-soft pt-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-success-deep">
                        <x-gpa.icon name="check-circle" class="mt-px h-3.5 w-3.5 shrink-0" />
                        Rata-rata susut masih di bawah batas toleransi
                    </p>
                </article>

                <article class="flex flex-col justify-between gap-3 rounded-xl bg-surface p-4 shadow-card">
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                Aturan 3
                            </span>
                            <span class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">Kelengkapan Berkas</span>
                        </div>

                        <h3 class="mt-3 font-sans text-base font-bold leading-5 text-ink">Sinkronisasi Surat Jalan &amp; Faktur</h3>

                        <p class="mt-1.5 gpa-note leading-5 text-ink-body">Nomor pesanan, surat jalan yang ditandatangani supir, dan faktur penagihan saling terhubung tanpa perbedaan.</p>
                    </div>

                    <p class="flex items-start gap-2 border-t border-line-soft pt-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-success-deep">
                        <x-gpa.icon name="check-circle" class="mt-px h-3.5 w-3.5 shrink-0" />
                        100% berkas tersinkronisasi rapi
                    </p>
                </article>
            </div>
        </section>

        <div class="flex flex-wrap gap-1 border-b border-line-board">
            <button type="button" @click="setTab('sales')"
                :class="tab === 'sales'
                    ? 'bg-surface text-ink border-b-2 border-brand'
                    : 'text-ink-quiet border-b-2 border-transparent hover:text-ink-body'"
                class="-mb-px inline-flex items-center gap-2 rounded-t-lg px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                <x-gpa.icon name="invoice" class="h-4 w-4 shrink-0" />
                Rekapitulasi Penjualan &amp; Pesanan
            </button>
            <button type="button" @click="setTab('weighing')"
                :class="tab === 'weighing'
                    ? 'bg-surface text-ink border-b-2 border-brand'
                    : 'text-ink-quiet border-b-2 border-transparent hover:text-ink-body'"
                class="-mb-px inline-flex items-center gap-2 rounded-t-lg px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                <x-gpa.icon name="scale" class="h-4 w-4 shrink-0" />
                Volume &amp; Analisis Timbangan
            </button>
            <button type="button" @click="setTab('dispatch')"
                :class="tab === 'dispatch'
                    ? 'bg-surface text-ink border-b-2 border-brand'
                    : 'text-ink-quiet border-b-2 border-transparent hover:text-ink-body'"
                class="-mb-px inline-flex items-center gap-2 rounded-t-lg px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                <x-gpa.icon name="truck" class="h-4 w-4 shrink-0" />
                Surat Jalan &amp; Pengiriman Supir
            </button>
            <button type="button" @click="setTab('aging')"
                :class="tab === 'aging'
                    ? 'bg-surface text-ink border-b-2 border-brand'
                    : 'text-ink-quiet border-b-2 border-transparent hover:text-ink-body'"
                class="-mb-px inline-flex items-center gap-2 rounded-t-lg px-4 py-3 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                <x-gpa.icon name="chart" class="h-4 w-4 shrink-0" />
                Pemantauan Umur Tagihan (Tempo)
            </button>
        </div>

        <section class="flex flex-col overflow-hidden rounded-2xl bg-surface shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 bg-surface-shell px-4 py-2.5">
                <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink">
                    <x-gpa.icon name="receipt" class="h-[15px] w-[15px] shrink-0" />
                    Buku Catatan Transaksi Penjualan
                </p>

                <p class="flex flex-wrap items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                    <span x-text="'Menampilkan ' + visibleRows().length + ' dari ' + {{ count($journalRows) }} + ' Transaksi'">
                        Menampilkan {{ count($journalRows) }} Transaksi Terkini
                    </span>
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="text-success-deep">Data Timbangan Resmi Sah</span>
                </p>
            </div>

            <div style="display:none" x-show="activeTab">
                <div class="gpa-scroll-x">
                    <table class="w-full min-w-[1320px] border-collapse text-left">
                        <thead class="bg-brand text-accent">
                            <tr>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">#</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px]">No Faktur / Surat Jalan</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px]">Pembeli &amp; Lokasi</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px]">Nama Sayur</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">Estimasi Awal</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">Netto Nyata</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">Selisih</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">Nilai Total (Rp)</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">Status Bayar</th>
                                <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-right">Dokumen</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-line-soft">
                            @foreach ($journalRows as $row)
                                <tr x-show="isVisible(@js($row))" @class(['transition-colors hover:bg-surface-shell/60', $loop->even ? 'bg-canvas' : ''])>
                                    <td class="px-3 py-4 text-right font-mono text-2xs font-bold tracking-[0.88px] text-ink-quiet">
                                        {{ $row['no'] }}
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="block font-mono text-2xs font-bold tracking-[0.88px] text-ink">{{ $row['invoice'] }}</span>
                                        <span class="mt-0.5 block font-mono text-2xs tracking-[0.88px] text-ink-quiet">{{ $row['sj'] }}</span>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="block font-sans text-sm font-bold leading-5 text-ink">{{ $row['client'] }}</span>
                                        <span class="mt-0.5 flex items-center gap-1.5 font-mono text-2xs tracking-[0.88px] text-ink-quiet">
                                            <x-gpa.icon name="map-pin" class="h-3 w-3 shrink-0" />
                                            {{ $row['location'] }}
                                        </span>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="flex items-center gap-2 font-sans text-xs font-semibold leading-5 text-ink-body">
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                                            {{ $row['commodity'] }}
                                        </span>
                                    </td>

                                    <td class="px-3 py-4 text-right font-mono text-2xs tracking-[0.88px] text-ink-body"
                                        x-text="kgInt({{ $row['estimate'] }}) + ' kg'">{{ $row['estimate_label'] }}</td>

                                    <td class="px-3 py-4 text-right">
                                        <span class="rounded bg-surface-shell px-2 py-1 font-mono text-2xs font-bold tracking-[0.88px] text-ink"
                                            x-text="kg({{ $row['net'] }}) + ' kg'">{{ $row['net_label'] }}</span>
                                    </td>

                                    <td class="px-3 py-4 text-right">
                                        <span class="block font-mono text-2xs font-bold tracking-[0.88px] {{ $row['deviation_class'] }}"
                                            :class="deviationClass({{ $row['estimate'] }}, {{ $row['net'] }})"
                                            x-text="deviation({{ $row['estimate'] }}, {{ $row['net'] }}) + '%'">{{ $row['deviation_label'] }}%</span>
                                        <span style="display:none" x-show="isBlocked(@js($row))"
                                            class="mt-1 inline-flex rounded bg-warning-soft px-1.5 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-warning-deep">
                                            Blokir Mediasi
                                        </span>
                                    </td>

                                    <td class="px-3 py-4 text-right font-mono text-xs font-bold tracking-[0.88px] text-ink"
                                        x-text="rupiah({{ $row['value'] }})">{{ $row['value_label'] }}</td>

                                    <td class="px-3 py-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                            $row['status_class'],
                                        ])>{{ $row['status_label'] }}</span>
                                    </td>

                                    <td class="px-3 py-4">
                                        <span class="flex items-center justify-end gap-1.5">
                                            @foreach (['PO Reg', 'Surat Jalan', 'Tiket Timbangan'] as $position => $file)
                                                @if ($position < $row['audit_files'])
                                                    <button type="button" @click="openAudit(@js($row), @js($file))"
                                                        class="inline-flex items-center gap-1 rounded bg-accent/50 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-success/40">
                                                        <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                        {{ $file }}
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded bg-surface-pill px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">
                                                        <x-gpa.icon name="x" class="h-3 w-3 shrink-0" />
                                                        Menunggu
                                                    </span>
                                                @endif
                                            @endforeach
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t-2 border-brand bg-surface-shell px-4 py-3">
                    <p class="flex items-center gap-2 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body"
                        x-text="'Total Rekapitulasi (' + visibleRows().length + ' Transaksi Terpilih):'">
                        Total Rekapitulasi ({{ count($journalRows) }} Transaksi Terpilih):
                    </p>

                    <div class="flex flex-wrap items-center gap-4">
                        <p class="font-mono text-2xs tracking-[0.88px] text-ink-body"
                            x-text="kgInt(totalEstimate) + ' kg'">{{ number_format($totalEstimate, 0, ',', '.') }} kg</p>
                        <p class="font-mono text-2xs font-bold tracking-[0.88px] text-ink"
                            x-text="kg(totalNet) + ' kg'">{{ number_format($totalNet, 1, ',', '.') }} kg</p>
                        <p class="font-mono text-2xs font-bold tracking-[0.88px] text-warning-caution"
                            x-text="totalDeviation + '%'">{{ number_format($overallDeviation, 2, '.', '') }}%</p>
                        <p class="font-mono text-sm font-bold tracking-[0.88px] text-brand"
                            x-text="rupiah(totalValue)">{{ 'Rp '.number_format($totalValue, 0, ',', '.') }}</p>
                        <span class="inline-flex items-center gap-1.5 rounded bg-brand px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                            <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                            Tercatat di Sistem
                        </span>
                    </div>
                </div>
            </div>

            <div style="display:none" x-show="! activeTab"
                class="flex flex-col items-center gap-2 px-6 py-12 text-center">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-lg bg-surface-track">
                    <x-gpa.icon name="chart" class="h-5 w-5 shrink-0 text-ink-quiet" />
                </span>
                <p class="font-sans text-base font-bold leading-6 text-ink" x-text="activeTabLabel">Rekapitulasi Penjualan &amp; Pesanan</p>
                <p class="max-w-md text-xs leading-5 text-ink-body">
                    Ringkasan tab ini belum dimuat pada MVP. Jurnal buku transaksi riil dan subtotal timbangan tampil pada Tab 1.
                </p>
            </div>
        </section>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-track">
                            <x-gpa.icon name="scale" class="h-[18px] w-[18px] shrink-0 text-ink" />
                        </span>

                        <h2 class="font-sans text-lg font-bold leading-6 text-ink">Analisis Selisih Timbangan Panen</h2>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                        <x-gpa.icon name="badge-check" class="h-[15px] w-[15px] shrink-0" />
                        Timbangan Sah Akurat
                    </span>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-board/40">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Total Estimasi Awal</p>
                        <p class="mt-1.5 font-mono text-xl font-bold tracking-[-0.01em] text-ink">
                            {{ number_format(($totalEstimate ?? 0) / 1000, 2, ',', '.') }} Ton
                        </p>
                        <p class="mt-1 gpa-note text-ink-body">Target Pesanan Awal</p>
                    </div>

                    <div class="rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-board/40">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Berat Bersih Riil (Netto)</p>
                        <p class="mt-1.5 font-mono text-xl font-bold tracking-[-0.01em] text-success-deep">
                            {{ number_format(($totalNet ?? 0) / 1000, 2, ',', '.') }} Ton
                        </p>
                        <p class="mt-1 gpa-note text-ink-body">Timbangan Gudang</p>
                    </div>

                    <div class="rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-board/40">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Rata-rata Selisih Susut</p>
                        <p class="mt-1.5 font-mono text-xl font-bold tracking-[-0.01em] text-warning-caution">
                            {{ (($overallDeviation ?? 0) >= 0 ? '+' : '') . number_format($overallDeviation ?? 0, 2, '.', '') }}%
                        </p>
                        <p class="mt-1 gpa-note text-ink-body">Susut Alami Sortir Sayur</p>
                    </div>
                </div>

                <p class="gpa-note leading-5 text-ink-body">
                    Hasil pemeriksaan menunjukkan tingkat penyusutan sayur selama proses sortir dan pengiriman berada dalam batas yang sangat baik dan wajar. Semua data tercatat resmi tanpa rekayasa.
                </p>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Standar Mutu: Terpenuhi</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">Status: Sah &amp; Aman</p>
                </div>
            </section>

            <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        <h2 class="font-sans text-lg font-bold leading-6 text-ink">Kesesuaian Dokumen Transaksi</h2>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                        <x-gpa.icon name="check-circle" class="h-[15px] w-[15px] shrink-0" />
                        Lengkap 100%
                    </span>
                </div>

                <ul class="flex flex-col gap-2">
                    <li class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/30">
                        <span class="flex min-w-0 items-center gap-2.5 gpa-note leading-5 text-ink-body">
                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                            Surat Jalan ditandatangani supir &amp; penerima
                        </span>
                        <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                            Lengkap &amp; Valid
                        </span>
                    </li>

                    <li class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/30">
                        <span class="flex min-w-0 items-center gap-2.5 gpa-note leading-5 text-ink-body">
                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                            Hasil timbangan gudang tercatat resmi
                        </span>
                        <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                            Tersinkronisasi
                        </span>
                    </li>

                    <li class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/30">
                        <span class="flex min-w-0 items-center gap-2.5 gpa-note leading-5 text-ink-body">
                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                            Faktur penagihan sesuai berat nyata
                        </span>
                        <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                            Sesuai
                        </span>
                    </li>

                    <li class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 outline-line-board/30">
                        <span class="flex min-w-0 items-center gap-2.5 gpa-note leading-5 text-ink-body">
                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                            Pencocokan mutasi kas dan rekening bank
                        </span>
                        <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                            Tercatat Rapi
                        </span>
                    </li>
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Pemeriksaan Berkas: Otomatis</p>
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">Tingkat Kepatuhan: 100%</p>
                </div>
            </section>
        </div>

        <section class="flex flex-col gap-5 rounded-2xl bg-surface p-6 shadow-card">
            <div class="flex flex-col gap-4 border-b border-line-soft pb-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex min-w-0 items-start gap-3.5">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand shadow-sub outline outline-1 outline-success-deep">
                        <x-gpa.icon name="lock" class="h-5 w-5 shrink-0 text-accent" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2 font-mono text-2xs font-bold tracking-[0.88px] text-ink">
                            <span>Kode Pengesahan Laporan Resmi:</span>
                            <span class="rounded bg-surface-shell px-2.5 py-1 font-mono text-xs text-success-deep outline outline-1 outline-line-board">{{ $hash }}</span>
                        </div>

                        <p class="mt-2 max-w-4xl text-xs leading-5 text-ink-body">
                            Laporan ini merupakan rekapitulasi resmi transaksi PT Guna Panen Agro yang siap diserahkan kepada Direksi.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2.5">
                <button type="button" @click="act('download')"
                    class="inline-flex h-10 items-center gap-2 rounded-xl px-4 gpa-micro-bold uppercase tracking-[1.08px] transition-colors shadow-sub bg-surface-shell text-ink-body outline outline-1 outline-line-board hover:bg-surface-track hover:text-ink">
                    <x-gpa.icon name="download" class="h-4 w-4 shrink-0" />
                    Unduh Laporan Lengkap (.xlsx)
                </button>
                <button type="button" @click="act('pdf')"
                    class="inline-flex h-10 items-center gap-2 rounded-xl px-4 gpa-micro-bold uppercase tracking-[1.08px] transition-colors shadow-sub bg-surface-shell text-ink-body outline outline-1 outline-line-board hover:bg-surface-track hover:text-ink">
                    <x-gpa.icon name="printer" class="h-4 w-4 shrink-0" />
                    Cetak Format Resmi (.pdf)
                </button>
                <button type="button" @click="act('send')"
                    class="inline-flex h-10 items-center gap-2 rounded-xl px-4 gpa-micro-bold uppercase tracking-[1.08px] transition-colors shadow-sub bg-brand text-accent outline outline-1 outline-success-deep hover:bg-brand-deep">
                    <x-gpa.icon name="send" class="h-4 w-4 shrink-0" />
                    Kirim Laporan ke Dasbor Direktur
                </button>
            </div>
        </section>
    </div>
@endsectionon