@extends('layouts.sekretaris')

@section('title', 'Stok')

@php
    $rows = $rows ?? [];
@endphp

@section('content')
    <div class="space-y-6"
        x-data="secretaryInventory(@js($rows), @js(['atp' => number_format($totalAtp ?? 0, 0, ',', '.') . ' kg', 'criticalThreshold' => 20]))"
        @gpa:search.window="query = $event.detail">
        <section class="border-b border-line-soft pb-6">
            <h1 class="font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-ink">
                Kesiapan Stok Sayur &amp; Alokasi Pemesanan
            </h1>
            <p class="mt-3 max-w-4xl text-sm leading-[1.42rem] text-ink-body">
                Pemantauan persediaan sayur segar dari kebun binaan dan pasokan cadangan mitra untuk memenuhi pesanan klien secara tepat waktu.
            </p>
        </section>

        <div class="flex flex-wrap gap-2">
            <button type="button" @click="handleAction({label: 'Sinkronkan Data Panen', tone: 'primary', message: 'Data hasil panen terbaru dari kebun telah disegarkan.'})"
                class="inline-flex h-10 items-center gap-1.5 rounded-lg px-4 shadow-sub transition-colors gpa-meta-lg font-bold tracking-[0.88px] bg-brand text-accent outline outline-1 outline-accent/50 hover:bg-brand-hover">
                <span class="h-3 w-3 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                Sinkronkan Data Panen
            </button>
            <button type="button" @click="handleAction({label: 'Unduh Laporan Stok (CSV)', tone: 'neutral', message: 'Laporan stok komoditas berhasil diunduh.'})"
                class="inline-flex h-10 items-center gap-1.5 rounded-lg px-4 shadow-sub transition-colors gpa-meta-lg font-bold tracking-[0.88px] bg-surface text-ink outline outline-1 outline-line-board hover:bg-surface-shell">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                Unduh Laporan Stok (CSV)
            </button>
            <button type="button" @click="handleAction({label: 'Pemeriksaan Stok Kritis', tone: 'neutral', message: 'Menampilkan daftar sayur dengan sisa kuota menipis.'})"
                class="inline-flex h-10 items-center gap-1.5 rounded-lg px-4 shadow-sub transition-colors gpa-meta-lg font-bold tracking-[0.88px] bg-surface text-ink outline outline-1 outline-line-board hover:bg-surface-shell">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-ink" aria-hidden="true"></span>
                Pemeriksaan Stok Kritis
            </button>
        </div>

        <section class="gpa-panel rounded-xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft pb-3">
                <h2 class="flex items-center gap-2 gpa-meta-lg font-bold tracking-[0.35px] text-ink">
                    <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                    Aturan Ketersediaan Sayur
                </h2>
                <span class="shrink-0 rounded bg-accent px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-ink">
                    Sistem Otomatis Aktif
                </span>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                <article class="flex items-start gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/30">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/40 text-success-deep">
                        <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <h3 class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">Pencegahan Kehabisan Stok</h3>
                            <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold tracking-[1.08px] bg-accent text-success-ink">AKTIF</span>
                        </div>
                        <p class="mt-1.5 text-xs leading-4 text-ink-body">Pesanan hanya dapat disetujui jika stok panen di kebun atau pasokan cadangan mitra mencukupi.</p>
                    </div>
                </article>

                <article class="flex items-start gap-2 rounded-lg bg-surface-shell p-2 outline outline-1 outline-line-board/30">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-warning-soft text-warning-deep">
                        <x-gpa.icon name="refresh" class="h-3.5 w-3.5 shrink-0 text-warning-deep" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <h3 class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">Pasokan Cadangan Otomatis</h3>
                            <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold tracking-[1.08px] bg-warning-soft text-warning-ink">SIAGA</span>
                        </div>
                        <p class="mt-1.5 text-xs leading-4 text-ink-body">Jika hasil panen kebun utama menipis di bawah 20%, sistem otomatis mengaktifkan kuota dari mitra tani binaan.</p>
                    </div>
                </article>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-xl bg-surface p-4 shadow-card outline outline-1 outline-accent/30">
                <span class="pointer-events-none absolute -right-4 -top-4 h-12 w-12 rounded-full bg-accent/30" aria-hidden="true"></span>

                <div class="flex items-start gap-2">
                    <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                    <h3 class="gpa-micro uppercase tracking-[0.55px] font-bold text-ink-body">TOTAL PASOKAN SAYUR</h3>
                </div>

                <p class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] text-ink">
                    {{ number_format($totalStock ?? 0, 0, ',', '.') }}
                    <span class="ml-1 font-mono text-sm font-normal text-ink-body">kg</span>
                </p>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-1 border-t border-line-soft pt-2">
                    <p class="gpa-note text-ink-body">
                        <span class="font-bold text-ink-body">Total Keseluruhan Pasokan</span>
                    </p>
                </div>
            </article>

            <article class="relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-xl bg-surface p-4 shadow-card">
                <div class="flex items-start gap-2">
                    <x-gpa.icon name="lock" class="h-4 w-4 shrink-0 text-warning-deep" />
                    <h3 class="gpa-micro uppercase tracking-[0.55px] font-bold text-ink-body">TERKUNCI PESANAN</h3>
                </div>

                <p class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] text-warning-deep">
                    {{ number_format($totalLocked ?? 0, 0, ',', '.') }}
                    <span class="ml-1 font-mono text-sm font-normal text-ink-body">kg</span>
                </p>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-1 border-t border-line-soft pt-2">
                    <p class="gpa-note text-ink-body">
                        <span class="font-bold text-ink-body">Alokasi Pesanan Klien B2B</span>
                    </p>
                </div>
            </article>

            <article class="relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-xl bg-surface p-4 shadow-card">
                <div class="flex items-start gap-2">
                    <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                    <h3 class="gpa-micro uppercase tracking-[0.55px] font-bold text-ink-body">KUOTA BEBAS DIPESAN</h3>
                </div>

                <p class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] text-success-deep">
                    {{ number_format($totalAtp ?? 0, 0, ',', '.') }}
                    <span class="ml-1 font-mono text-sm font-normal text-ink-body">kg</span>
                </p>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-1 border-t border-line-soft pt-2">
                    <p class="gpa-note text-ink-body">
                        <span class="font-bold text-ink-body">Siap Dijual ke Klien</span>
                    </p>
                </div>
            </article>

            <article class="relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-xl bg-surface p-4 shadow-card">
                <div class="flex items-start gap-2">
                    <x-gpa.icon name="refresh" class="h-4 w-4 shrink-0 text-ink" />
                    <h3 class="gpa-micro uppercase tracking-[0.55px] font-bold text-ink-body">CADANGAN MITRA</h3>
                </div>

                <p class="mt-3 font-sans text-3xl font-extrabold leading-9 tracking-[-0.01em] text-ink">
                    100%
                    <span class="ml-1 font-mono text-sm font-normal text-ink-body">Siap</span>
                </p>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-1 border-t border-line-soft pt-2">
                    <p class="gpa-note text-ink-body">
                        <span class="font-bold text-ink-body">Jejaring Petani Siaga</span>
                    </p>
                </div>
            </article>
        </div>

        <section class="gpa-panel flex flex-wrap items-end justify-between gap-4 px-4 py-4">
            <div class="grid flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <label class="block min-w-0" for="filter-source">
                    <span class="mb-1.5 flex items-center gap-1.5 gpa-micro uppercase tracking-[0.55px] text-ink-body">
                        <x-gpa.icon name="package" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                        Sumber Pasokan
                    </span>
                    <span class="relative block">
                        <select id="filter-source" x-model="source" @change="handleFilterChange()"
                            class="block h-10 w-full appearance-none rounded-lg border-line-board bg-surface pr-9 text-xs font-semibold text-ink focus:border-brand focus:ring-brand">
                            <option value="Semua Sumber Pasokan">Semua Sumber Pasokan</option>
                            <option value="Kebun Binaan">Kebun Binaan</option>
                            <option value="Mitra Petani">Mitra Petani</option>
                        </select>
                        <x-gpa.icon name="chevron-down"
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-quiet" />
                    </span>
                </label>

                <label class="block min-w-0" for="filter-status">
                    <span class="mb-1.5 flex items-center gap-1.5 gpa-micro uppercase tracking-[0.55px] text-ink-body">
                        <x-gpa.icon name="filter" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                        Status Kuota
                    </span>
                    <span class="relative block">
                        <select id="filter-status" x-model="status" @change="handleFilterChange()"
                            class="block h-10 w-full appearance-none rounded-lg border-line-board bg-surface pr-9 text-xs font-semibold text-ink focus:border-brand focus:ring-brand">
                            <option value="Semua Status Stok">Semua Status Stok</option>
                            <option value="Stok Aman">Stok Aman</option>
                            <option value="Perlu Tambahan">Perlu Tambahan</option>
                        </select>
                        <x-gpa.icon name="chevron-down"
                            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-quiet" />
                    </span>
                </label>

                <label class="flex h-10 cursor-pointer items-center gap-2 self-end rounded-lg px-1 text-xs font-semibold text-ink"
                    for="filter-critical">
                    <input type="checkbox" id="filter-critical" x-model="criticalOnly" @change="toggleCritical()"
                        class="gpa-check">
                    <span>Hanya Tampilkan Kuota &lt; 20%</span>
                </label>
            </div>

            <button type="button" @click="bulkLock()"
                class="inline-flex h-11 shrink-0 items-center gap-2 rounded-lg bg-ink px-4 text-accent shadow-sub outline outline-1 outline-accent/40 transition-colors hover:bg-brand-hover gpa-meta-lg font-bold tracking-[0.88px]">
                <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0" />
                Kunci Kuota Massal
            </button>
        </section>

        <section class="gpa-panel overflow-hidden rounded-xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/60 bg-surface-shell px-4 py-3">
                <div class="flex min-w-0 flex-wrap items-center gap-2">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        <x-gpa.icon name="gauge" class="h-[18px] w-[18px] shrink-0 text-success-deep" />
                        Daftar Ketersediaan Sayur Segar Per Komoditas
                    </h2>
                    <span class="shrink-0 rounded bg-surface-pill px-2 py-0.5 gpa-micro-bold text-ink">
                        {{ count($rows) }} Komoditas Aktif
                    </span>
                </div>
                <p class="gpa-micro shrink-0 uppercase tracking-[1.08px] text-ink-quiet">
                    Pembaruan: {{ now()->format('d M Y, H:i') }} WIB
                </p>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[1180px] border-collapse text-left">
                    <thead class="border-b border-line-board bg-surface-pill/40">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink">Nama Sayur &amp; Mutu</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink">Hasil Panen Kebun</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink">Cadangan Mitra</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink">Total Pasokan</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink text-right">Terkunci Pesanan</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink text-right">Sisa Bebas Dipesan</th>
                            <th scope="col" class="px-4 py-3 gpa-micro-bold uppercase tracking-[0.88px] text-ink">Status &amp; Tindakan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-faint">
                        @foreach ($rows as $row)
                            <tr x-show="matchesFilter(@js($row))" class="align-top transition-colors hover:bg-surface-shell/60">
                                <td class="px-4 py-3">
                                    <p class="font-sans text-sm font-bold leading-5 text-ink">{{ $row['name'] }}</p>
                                    <p class="mt-1 gpa-note text-ink-quiet">{{ $row['sku'] }}</p>
                                    <p class="gpa-note text-ink-quiet">{{ $row['grade'] }}</p>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $row['binawan_value'] }}</p>
                                    <p class="gpa-note text-ink-quiet">{{ $row['binawan_share'] }}</p>
                                    <div class="mt-1.5 h-1.5 w-full max-w-[9rem] overflow-hidden rounded-full bg-surface-pill">
                                        <div class="h-full rounded-full bg-success-deep"
                                            :style="'width: ' + {{ $row['binawan_percent'] }} + '%'"></div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] {{ $row['buffer_class'] }}">
                                        {{ $row['buffer_value'] }}
                                    </p>
                                    <p class="gpa-note text-ink-body">{{ $row['buffer_partner'] }}</p>
                                    @if (! empty($row['buffer_badge']))
                                        <span @class([
                                            'mt-1 inline-flex rounded px-1.5 py-0.5 gpa-micro-bold',
                                            $row['buffer_badge']['class'],
                                        ])>{{ $row['buffer_badge']['label'] }}</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $row['total_value'] }}</p>
                                    <p class="gpa-note text-ink-quiet">Gudang + Kebun</p>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-danger">{{ $row['locked_value'] }}</p>
                                    <span class="mt-1 inline-flex rounded bg-danger-soft/10 px-1.5 py-0.5 gpa-micro-bold text-danger-ink">
                                        {{ $row['locked_note'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <p class="gpa-meta-lg font-bold tracking-[0.28px] {{ $row['atp_class'] }}">
                                        {{ $row['atp_value'] }}
                                    </p>
                                    <span class="mt-1 inline-flex rounded bg-accent/20 px-1.5 py-0.5 gpa-micro-bold text-ink-body">
                                        {{ $row['atp_note'] }}
                                    </span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded px-2 py-0.5 gpa-micro-bold',
                                        $row['status']['class'],
                                    ])>
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                        {{ $row['status']['label'] }}
                                    </span>

                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        <button type="button" @click="detail(@js($row))"
                                            class="inline-flex h-7 items-center gap-1 rounded bg-surface-track px-2 text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-shell gpa-micro-bold uppercase tracking-[0.88px]">
                                            <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                            Detail
                                        </button>
                                        <button type="button" @click="rowAction(@js($row))"
                                            @class([
                                                'inline-flex h-7 items-center gap-1 rounded px-2 transition-colors gpa-micro-bold uppercase tracking-[0.88px]',
                                                $row['action']['class'],
                                            ])>{{ $row['action']['label'] }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        <tr x-cloak x-show="visibleCount() === 0">
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="text-xs font-semibold text-ink">Tidak ada komoditas pada filter ini.</p>
                                <p class="mt-1 gpa-note text-ink-quiet">
                                    Ubah pilihan filter atau matikan filter kuota di bawah 20%.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board/60 bg-surface-shell px-4 py-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + visibleCount() + ' dari ' + {{ count($rows) }} + ' komoditas operasional aktif'">
                </p>
                <p class="gpa-note text-ink-body">
                    Total Kuota Bebas (ATP): <span x-text="atpFooter()"></span>
                </p>
                <p class="w-full uppercase tracking-[1.08px] text-ink-quiet gpa-micro">
                    Pencatatan resmi gudang sayur sentra.
                </p>
            </div>
        </section>

        <div class="grid items-start gap-4 xl:grid-cols-2">
            <section class="gpa-panel overflow-hidden rounded-xl">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft px-4 py-3">
                    <h2 class="font-sans text-lg font-bold text-ink">Riwayat Mutasi &amp; Penguncian Stok</h2>
                    <span class="shrink-0 rounded bg-ink px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                        Pembaruan Langsung
                    </span>
                </div>

                <ul class="divide-y divide-line-soft">
                    <li class="flex items-start gap-3 px-4 py-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent/40">
                            <x-gpa.icon name="lock" class="h-4 w-4 shrink-0 text-success-deep" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <h3 class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">Penguncian Stok Pesanan Masuk</h3>
                                <span class="shrink-0 rounded bg-surface-pill px-1.5 py-0.5 gpa-micro-bold text-ink">
                                    PO-TERVERIFIKASI
                                </span>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-ink-body">Stok sayur berhasil dialokasikan dari kebun untuk pemenuhan pesanan.</p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="gpa-note font-bold text-success-deep font-semibold">Hari Ini</p>
                            <p class="mt-1 uppercase tracking-[1.08px] text-ink-quiet gpa-micro">Pesanan Sah</p>
                        </div>
                    </li>

                    <li class="flex items-start gap-3 px-4 py-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-surface-pill">
                            <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-ink" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <h3 class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">Penerimaan Panen Sayur Baru</h3>
                                <span class="shrink-0 rounded bg-surface-pill px-1.5 py-0.5 gpa-micro-bold text-ink">
                                    PANEN-HARIAN
                                </span>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-ink-body">Sayur segar hasil panen masuk ke gudang sortir dan lolos pemeriksaan.</p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="gpa-note font-bold text-success-deep font-semibold">Pagi Ini</p>
                            <p class="mt-1 uppercase tracking-[1.08px] text-ink-quiet gpa-micro">Lolos Sortir</p>
                        </div>
                    </li>
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft px-4 py-3">
                    <p class="gpa-note text-ink-quiet">Menampilkan riwayat mutasi stok aktif</p>
                    <button type="button" @click="syncLog()"
                        class="inline-flex items-center gap-1 gpa-note text-success-deep underline underline-offset-2 hover:text-success">
                        Lihat Semua Riwayat
                        <x-gpa.icon name="arrow-right" class="h-3 w-3" />
                    </button>
                </div>
            </section>

            <section class="gpa-panel overflow-hidden rounded-xl">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft px-4 py-3">
                    <h2 class="font-sans text-lg font-bold text-ink">Kesiapan Pengiriman Armada</h2>
                    <span class="shrink-0 rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                        Armada Siap
                    </span>
                </div>

                <ul class="divide-y divide-line-soft">
                    <li>
                        <button type="button" @click="readinessCheck({title: 'Timbangan Gudang Siap', note: 'Timbangan digital telah dinolkan dan terkalibrasi akurat.', badge: {label: 'Siap', class: 'bg-accent text-success-ink'}})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-shell/60">
                            <x-gpa.icon name="scale" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span class="min-w-0 flex-1">
                                <span class="block font-sans text-sm font-semibold leading-5 text-ink">
                                    Timbangan Gudang Siap
                                </span>
                                <span class="mt-0.5 block text-xs leading-5 text-ink-body">Timbangan digital telah dinolkan dan terkalibrasi akurat.</span>
                            </span>
                            <span class="shrink-0 rounded px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] bg-accent text-success-ink">
                                Siap
                            </span>
                        </button>
                    </li>

                    <li>
                        <button type="button" @click="readinessCheck({title: 'Mobil Boks Berpendingin Standby', note: 'Suhu ruang pendingin mobil terjaga segar (2°C - 6°C).', badge: {label: 'Suhu Dingin Aman', class: 'bg-accent text-success-ink'}})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-shell/60">
                            <x-gpa.icon name="truck" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span class="min-w-0 flex-1">
                                <span class="block font-sans text-sm font-semibold leading-5 text-ink">
                                    Mobil Boks Berpendingin Standby
                                </span>
                                <span class="mt-0.5 block text-xs leading-5 text-ink-body">Suhu ruang pendingin mobil terjaga segar (2°C - 6°C).</span>
                            </span>
                            <span class="shrink-0 rounded px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] bg-accent text-success-ink">
                                Suhu Dingin Aman
                            </span>
                        </button>
                    </li>

                    <li>
                        <button type="button" @click="readinessCheck({title: 'Kesiapan Surat Jalan', note: 'Surat jalan siap dicetak begitu timbangan gudang dikunci.', badge: {label: 'Siap Cetak', class: 'bg-accent text-success-ink'}})"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-surface-shell/60">
                            <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span class="min-w-0 flex-1">
                                <span class="block font-sans text-sm font-semibold leading-5 text-ink">
                                    Kesiapan Surat Jalan
                                </span>
                                <span class="mt-0.5 block text-xs leading-5 text-ink-body">Surat jalan siap dicetak begitu timbangan gudang dikunci.</span>
                            </span>
                            <span class="shrink-0 rounded px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] bg-accent text-success-ink">
                                Siap Cetak
                            </span>
                        </button>
                    </li>
                </ul>

                <div class="border-t border-line-soft px-4 py-4">
                    <button type="button" @click="releaseDo()"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-ink px-4 text-accent shadow-sub outline outline-1 outline-accent/40 transition-colors hover:bg-brand-hover gpa-meta-lg font-bold tracking-[0.88px]">
                        Ke Menu Surat Jalan
                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5 shrink-0" />
                    </button>
                </div>
            </section>
        </div>
    </div>
@endsection