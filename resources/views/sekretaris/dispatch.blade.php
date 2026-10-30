@extends('layouts.sekretaris')

@section('title', 'Surat Jalan')

@php
    $rows = $rows ?? [];
@endphp

@section('content')
    <div class="space-y-6" x-data="secretaryDispatch(@js($rows))"
        @gpa:search.window="query = $event.detail">
        <section class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="flex items-center gap-1 gpa-note text-success-deep">
                    <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                    Surat Jalan &amp; Pengiriman
                </p>

                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-brand">
                    Penerbitan Surat Jalan Resmi &amp;<br />
                    Penugasan Supir Pengantar
                </h1>

                <p class="mt-3 max-w-3xl text-sm leading-5 text-ink-body">
                    Penerbitan surat jalan resmi dan penugasan armada pengantaran untuk pesanan yang telah terverifikasi lunas.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('sekretaris.pod.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-full bg-accent px-4 py-1.5 text-xs font-bold text-ink shadow-xs transition-transform hover:scale-105 ring-1 ring-inset ring-accent-edge">
                    <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 text-success-deep" />
                    <span>Monitoring Bukti Kirim (PoD) &rarr;</span>
                </a>
                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-surface-pill py-1.5 pl-3 pr-3 text-[10px] font-bold uppercase text-ink-body ring-1 ring-inset ring-line-board">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span>SOP Rilis Lunas</span>
                </span>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="flex min-h-[16.4rem] flex-col justify-between gap-10 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Surat Jalan Siap Terbit
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/30">
                        <x-gpa.icon name="file-text" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <div class="py-3">
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($readyOrdersCount ?? 0) }} Dokumen
                    </p>
                    <p class="mt-1 text-xs leading-4 text-ink-body">
                        Pesanan siap dikirimkan kepada mitra klien
                    </p>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                        Pembayaran Lunas
                    </p>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                </div>
            </article>

            <article class="flex min-h-[16.4rem] flex-col justify-between gap-10 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Dalam Perjalanan Supir
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-track">
                        <x-gpa.icon name="truck" class="h-[15px] w-[15px] shrink-0 text-ink" />
                    </span>
                </div>

                <div class="py-3">
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($shippingOrdersCount ?? 0) }} Mobil
                    </p>
                    <p class="mt-1 text-xs leading-4 text-ink-body">
                        Sayur dalam perjalanan menuju lokasi pelanggan
                    </p>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">
                        Sedang Jalan
                    </p>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-ink-quiet" aria-hidden="true"></span>
                </div>
            </article>

            <article class="flex min-h-[16.4rem] flex-col justify-between gap-10 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Menunggu Bukti Terima
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-warning-soft/30">
                        <x-gpa.icon name="package" class="h-[15px] w-[15px] shrink-0 text-warning-deep" />
                    </span>
                </div>

                <div class="py-3">
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($shippingOrdersCount ?? 0) }}
                        <br />Pengiriman
                    </p>
                    <p class="mt-1 text-xs leading-4 font-inter text-ink-body">
                        Menunggu foto bukti terima dan tanda tangan klien
                    </p>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                    <p class="gpa-micro-bold tracking-[1.08px] text-warning">
                        Verifikasi Penerimaan
                    </p>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-warning" aria-hidden="true"></span>
                </div>
            </article>

            <article class="flex min-h-[16.4rem] flex-col justify-between gap-10 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Selesai Terkirim
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-pill">
                        <x-gpa.icon name="check-circle" class="h-[15px] w-[15px] shrink-0 text-ink" />
                    </span>
                </div>

                <div class="py-3">
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($doneOrdersCount ?? 0) }} Pesanan
                    </p>
                    <p class="mt-1 text-xs leading-4 text-ink-body">
                        Surat jalan selesai dan siap masuk ke tagihan faktur
                    </p>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                    <p class="gpa-micro-bold tracking-[1.08px] text-ink-quiet">
                        Transaksi Tuntas
                    </p>
                    <span class="h-2 w-2 shrink-0 rounded-full bg-ink-quiet" aria-hidden="true"></span>
                </div>
            </article>
        </div>

        <section class="relative overflow-hidden rounded-2xl bg-surface p-6 shadow-card">
            <span class="absolute inset-y-0 left-0 w-2 bg-success-deep" aria-hidden="true"></span>

            <div class="flex flex-wrap items-center justify-between gap-6">
                <div class="flex min-w-0 items-start gap-4 pl-2">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-accent/40 outline outline-1 outline-accent">
                        <x-gpa.icon name="lock" class="h-5 w-5 shrink-0 text-ink" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded bg-ink px-2 py-0.5 gpa-micro-bold uppercase tracking-[1px] text-accent">
                                SOP Pengiriman
                            </span>
                            <h2 class="font-sans text-lg font-bold leading-6 text-ink">Aturan Wajib Pembuatan Surat Jalan</h2>
                        </div>

                        <p class="mt-1.5 max-w-3xl text-sm leading-[1.42rem] text-ink-body">
                            Surat jalan fisik diterbitkan oleh Sekretaris setelah pembayaran dinyatakan lunas dan diverifikasi berdasarkan mutasi rekening bank atau QRIS resmi GPA. Surat jalan menjadi dasar legalitas pengantaran kargo oleh supir armada logistik ke titik bongkar muat klien.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 border-l border-line-soft pl-4 text-right">
                    <p class="gpa-micro font-semibold uppercase tracking-[1.08px] text-ink-quiet">
                        Protokol Validasi:
                    </p>
                    <p class="gpa-meta-lg font-bold tracking-[0.88px] text-success-deep">PEMBAYARAN LUNAS</p>
                    <p class="mt-1 gpa-micro font-semibold tracking-[1.08px] text-ink-body">Kalibrasi Aktif 2026</p>
                </div>
            </div>
        </section>

        <section class="gpa-panel overflow-hidden rounded-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft bg-surface-shell/40 p-6">
                <div class="min-w-0">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold leading-6 text-ink">
                        <x-gpa.icon name="send" class="h-[18px] w-[18px] shrink-0 text-ink" />
                        Antrean Surat Jalan Siap Terbit &amp; Penugasan Supir
                    </h2>
                    <p class="mt-0.5 text-xs leading-4 text-ink-body">Pilih pesanan untuk otorisasi pelepasan armada atau cetak langsung format resmi Surat Jalan A4.</p>
                </div>

                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="warehouse = 'Semua Gudang'; handleWarehouse()" :class="
                        warehouse === 'Semua Gudang'
                            ? 'bg-accent text-success-ink outline outline-1 outline-success-deep'
                            : 'bg-surface-pill text-ink outline outline-1 outline-line-board hover:bg-surface-disabled'
                    "
                        class="rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Semua Gudang
                    </button>
                    <button type="button" @click="warehouse = 'Gudang Sentra Cibitung'; handleWarehouse()" :class="
                        warehouse === 'Gudang Sentra Cibitung'
                            ? 'bg-accent text-success-ink outline outline-1 outline-success-deep'
                            : 'bg-surface-pill text-ink outline outline-1 outline-line-board hover:bg-surface-disabled'
                    "
                        class="rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Gudang Sentra Cibitung
                    </button>
                    <button type="button" @click="warehouse = 'Gudang Lembang'; handleWarehouse()" :class="
                        warehouse === 'Gudang Lembang'
                            ? 'bg-accent text-success-ink outline outline-1 outline-success-deep'
                            : 'bg-surface-pill text-ink outline outline-1 outline-line-board hover:bg-surface-disabled'
                    "
                        class="rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                        Gudang Lembang
                    </button>

                    <span class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-ink">
                        <span x-text="readyCount() + ' Siap Rilis'"></span>
                    </span>
                    <span class="rounded bg-surface-disabled px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body">
                        <span x-text="pendingCount() + ' Tertunda'"></span>
                    </span>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-track">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">ID Pesanan &amp; Klien</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Estimasi Awal</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Kuantitas Pesanan</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Status Pembayaran</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Armada &amp; Supir</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body text-right">Aksi Penerbitan</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft">
                        @foreach ($rows as $row)
                            <tr x-show="matchesFilter(@js($row))" @click="select(@js($row['id']))"
                                :class="rowClass(@js($row))" class="cursor-pointer transition-colors">
                                <td class="px-4 py-4">
                                    <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $row['id'] }}</p>
                                    <p class="mt-0.5 pt-0.5 font-sans text-sm font-semibold leading-5 text-ink">
                                        {{ $row['client'] }}
                                    </p>
                                    <p class="mt-1 text-xs leading-4 text-ink-body">{{ $row['dock'] }}</p>
                                    <p x-cloak x-show="isReleased(@js($row['id']))"
                                        class="mt-1 inline-flex rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">
                                        Sudah Terbit
                                    </p>
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    <span class="gpa-note text-ink-body">{{ $row['estimate'] }}</span>
                                </td>

                                <td class="px-4 py-4">
                                    @if (! empty($row['netto']))
                                        <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $row['netto'] }}</p>
                                        <p class="gpa-micro text-danger">{{ $row['deviation'] }}</p>
                                        <p class="mt-1 text-2xs leading-4 text-ink-quiet">{{ $row['revision'] }}</p>
                                    @else
                                        <span @class([
                                            'inline-flex rounded px-2 py-0.5 gpa-micro-bold uppercase tracking-[1px]',
                                            $row['netto_chip']['class'],
                                        ])>{{ $row['netto_chip']['label'] }}</span>
                                        <p class="mt-1 text-2xs font-medium leading-4 text-warning">{{ $row['netto_note'] }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                        $row['status_chip']['class'],
                                    ])>
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                        {{ $row['status_chip']['label'] }}
                                    </span>
                                    <p class="mt-1 font-mono text-xs leading-4 text-ink-body">{{ $row['tera'] }}</p>
                                </td>

                                <td class="px-4 py-4" @click.stop>
                                    {{-- KONDISI 1: SUDAH DITERBITKAN -> TERKUNCI PERMANEN (TIDAK BISA DIUBAH) --}}
                                    <template x-if="isReleased(@js($row['id'])) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(@js($row['order_status']))">
                                        <div class="space-y-1.5 min-w-[210px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-2xs font-bold uppercase tracking-wider text-ink-muted">Supir Bertugas:</span>
                                                <span class="inline-flex items-center gap-1 rounded bg-surface-pill px-1.5 py-0.5 text-2xs font-semibold text-ink-quiet" title="Penugasan armada terkunci permanen setelah Surat Jalan terbit">
                                                    <x-gpa.icon name="lock" class="h-2.5 w-2.5 text-ink-muted" />
                                                    Terkunci
                                                </span>
                                            </div>

                                            <div class="rounded-lg border border-line-board/70 bg-surface-shell/40 px-3 py-2 shadow-xs">
                                                <p class="text-xs font-bold text-ink" x-text="@js($row['driver']) || 'Belum Ditugaskan'"></p>
                                                <div class="mt-1 flex items-center justify-between text-2xs text-ink-body">
                                                    <span class="font-mono text-brand font-semibold">Plat: {{ $row['plate'] ?: 'B 9014 KPA' }}</span>
                                                    <span class="inline-flex items-center gap-0.5 text-success-deep font-semibold">
                                                        <x-gpa.icon name="check" class="h-2.5 w-2.5" />
                                                        Sah Terbit
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- KONDISI 2: SEBELUM TERBIT -> BEBAS MEMILIH SUPIR DARI AKUN ARMADA --}}
                                    <template x-if="!isReleased(@js($row['id'])) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(@js($row['order_status']))">
                                        <div class="space-y-1.5 min-w-[210px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-2xs font-bold uppercase tracking-wider text-ink-muted">Tugaskan Supir:</span>
                                                <span class="font-mono text-2xs font-semibold text-brand" title="Plat Armada">{{ $row['plate'] ?: 'B 9014 KPA' }}</span>
                                            </div>

                                            <div class="relative">
                                                <select
                                                    @change="updateDriver(@js($row['order_id']), $event.target.value, @js($row))"
                                                    class="w-full rounded-lg border border-line-board bg-white py-1.5 pl-2.5 pr-7 text-xs font-semibold text-ink shadow-sm transition-colors hover:border-brand focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                                                    <option value="" disabled {{ empty($row['driver_id']) ? 'selected' : '' }}>-- Pilih Supir Armada --</option>
                                                    @foreach ($drivers as $drv)
                                                        <option value="{{ $drv->id }}" {{ ($row['driver_id'] ?? null) == $drv->id ? 'selected' : '' }}>
                                                            {{ $drv->name }} ({{ $drv->phone ?: 'Armada' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center justify-between text-2xs text-ink-body">
                                                <span class="inline-flex items-center gap-1 font-medium">
                                                    <x-gpa.icon name="truck" class="h-3 w-3 text-brand" />
                                                    {{ $row['armada'] ?: 'Armada Logistik GPA' }}
                                                </span>
                                                <span class="font-medium text-warning-deep">Pilih sebelum terbit</span>
                                            </div>
                                        </div>
                                    </template>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- SESI 1: Menunggu Penerbitan (Tombol Terbitkan Surat Jalan saja) --}}
                                        <template x-if="!isReleased(@js($row['id'])) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(@js($row['order_status']))">
                                            <div>
                                                @if ($row['order_status'] === 'LUNAS' || $row['state'] === 'ready')
                                                    <button type="button" @click.stop="issue(@js($row))"
                                                        class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3 py-2 text-ink shadow-sub outline outline-1 outline-success-deep transition-colors hover:bg-accent-deep gpa-meta font-bold tracking-[0.88px]">
                                                        <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                        Terbitkan Surat Jalan
                                                    </button>
                                                @else
                                                    <span
                                                        class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-2 text-ink-quiet opacity-80 outline outline-1 outline-line-board/60 gpa-meta font-bold tracking-[0.88px]"
                                                        title="Menunggu verifikasi pembayaran lunas oleh Sekretaris">
                                                        <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                                                        Menunggu Lunas
                                                    </span>
                                                @endif
                                            </div>
                                        </template>

                                        {{-- SESI 2: Surat Jalan Sudah Terbit (Tombol Cetak Surat Jalan muncul setelah diterbitkan Sekre) --}}
                                        <template x-if="isReleased(@js($row['id'])) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(@js($row['order_status']))">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1 rounded bg-accent/40 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                                    <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                                                    Diterbitkan
                                                </span>
                                                <a href="{{ route('prints.surat-jalan', ['order_id' => $row['order_id'] ?? null]) }}" target="_blank" @click.stop
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-3.5 py-2 text-white shadow transition-colors hover:bg-brand-hover gpa-meta font-bold tracking-[0.88px]">
                                                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                                                    Cetak Surat Jalan
                                                </a>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft bg-surface-shell px-4 py-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + visibleCount() + ' dari ' + {{ count($rows) }} + ' dokumen dalam antrean dispatch'">
                </p>
                <p class="gpa-note text-ink-body">
                    Total Terbit Sesi Ini:
                    <span class="font-bold text-success-deep" x-text="issuedCount() + ' Dokumen'"></span>
                </p>
            </div>
        </section>


        <section
            class="sticky bottom-4 z-30 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-ink p-4 shadow-2xl ring-1 ring-white/10 outline outline-2 outline-accent/40">
            <div class="flex items-center gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent">
                    <x-gpa.icon name="lock" class="h-5 w-5 shrink-0 text-ink" />
                </span>

                <div>
                    <h2 class="font-sans text-lg font-bold leading-[1.4rem] text-accent">Pusat Kendali Otorisasi &amp; Pelepasan Armada</h2>
                    <p class="text-xs leading-4 text-surface-disabled">
                        Penerbitan surat jalan akan mengesahkan dokumen pengiriman resmi, menugaskan supir armada, mencatat log keberangkatan armada, dan mengirimkan notifikasi siap terima ke pihak klien.
                    </p>
                    <template x-if="selected">
                        <p class="mt-1 text-2xs text-accent font-medium">
                            Pesanan Terpilih: <strong class="text-white" x-text="selected.id"></strong> &bull; 
                            Supir Ditugaskan: <strong class="text-white" x-text="selected.driver || 'Belum Ditugaskan'"></strong> &bull; 
                            Plat Armada: <strong class="text-white font-mono" x-text="selected.plate || 'B 9014 KPA'"></strong>
                        </p>
                    </template>
                    <p x-cloak x-show="selected && !isReady(selected) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(selected.order_status)"
                        class="mt-1 inline-flex rounded bg-danger px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-white">
                        Menunggu Verifikasi Pembayaran Lunas
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button type="button" @click="saveDraft()"
                    class="rounded-lg bg-white/10 px-5 py-2.5 text-center font-mono text-xs font-semibold tracking-[0.88px] text-canvas outline outline-1 outline-line-board/40 transition-colors hover:bg-white/15">
                    Simpan Draf Saja
                </button>

                {{-- SESI 1: Belum Terbit -> Terbitkan --}}
                <template x-if="selected && !isReleased(selected.id) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(selected.order_status)">
                    <button type="button" @click="confirmRelease()" :disabled="! selected || (!isReady(selected) && selected.state !== 'ready')"
                        :class="! selected || (!isReady(selected) && selected.state !== 'ready')
                            ? 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'
                            : 'bg-accent text-ink hover:bg-accent-deep'"
                        class="inline-flex items-center gap-2 rounded-lg px-6 py-2.5 font-mono text-xs font-bold tracking-[0.88px] shadow-sub transition-colors">
                        <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                        <span>Kunci Data &amp; Terbitkan Surat Jalan Resmi</span>
                    </button>
                </template>

                {{-- SESI 2: Sudah Terbit -> Cetak Surat Jalan --}}
                <template x-if="selected && (isReleased(selected.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(selected.order_status))">
                    <a :href="'/cetak/surat-jalan?order_id=' + (selected.order_id || '')" target="_blank"
                        class="inline-flex items-center gap-2 rounded-lg bg-accent px-6 py-2.5 font-mono text-xs font-bold tracking-[0.88px] text-ink shadow-sub hover:bg-accent-deep transition-colors">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                        <span>Cetak Surat Jalan (Nota/A5)</span>
                    </a>
                </template>
            </div>
        </section>
    </div>
@endsection