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
                    Pencetakan surat jalan resmi berdasarkan hasil timbangan bersih nyata dari gudang.
                </p>
            </div>

            <span
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-accent/50 py-1.5 pl-4 pr-4 outline outline-1 outline-success-deep">
                <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                <span class="gpa-micro-bold uppercase tracking-[0.45px] text-ink">Timbangan Gudang Terhubung &amp; Siap</span>
            </span>
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
                        Berat bersih sayur telah diverifikasi koordinator gudang
                    </p>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">
                        Timbangan Valid
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
                                SOP Rule 05
                            </span>
                            <h2 class="font-sans text-lg font-bold leading-6 text-ink">Aturan Wajib Pembuatan Surat Jalan</h2>
                        </div>

                        <p class="mt-1.5 max-w-3xl text-sm leading-[1.42rem] text-ink-body">
                            Surat jalan fisik dilarang keras dicetak berdasarkan angka estimasi awal pesanan. Surat jalan hanya sah apabila telah ditandatangani oleh petugas timbangan gudang, nomor polisi armada tercatat jelas, serta melalui gerbang verifikasi
                            <span
                                class="inline-flex items-center rounded bg-accent/40 px-2 py-1 gpa-meta-lg font-bold tracking-[0.88px] text-ink outline outline-1 outline-success-deep/30">
                                [HARD GATE: TIMBANGAN SAH]
                            </span>
                            sebelum armada diberangkatkan.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 border-l border-line-soft pl-4 text-right">
                    <p class="gpa-micro font-semibold uppercase tracking-[1.08px] text-ink-quiet">
                        Protokol Validasi:
                    </p>
                    <p class="gpa-meta-lg font-bold tracking-[0.88px] text-success-deep">ISO/IEC 17025 TERA</p>
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
                    <p class="mt-0.5 text-xs leading-4 text-ink-body">Klik salah satu pesanan untuk melihat pratinjau dokumen surat jalan resmi di bawah.</p>
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
                <table class="w-full min-w-[1080px] border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-track">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">ID Pesanan &amp; Klien</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Estimasi Awal</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Realisasi Timbang</th>
                            <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">Status Tera</th>
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

                                <td class="px-4 py-4">
                                    @if (! empty($row['armada']))
                                        <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">{{ $row['armada'] }}</p>
                                        <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">Plat: {{ $row['plate'] }}</p>
                                        <p class="mt-1 text-xs leading-4 text-ink-body">Supir: {{ $row['driver'] }}</p>
                                    @else
                                        <p class="text-xs italic leading-4 text-ink-quiet">{{ $row['fleet_note'] }}</p>
                                        <p class="mt-1 text-2xs leading-4 text-ink-quiet">{{ $row['fleet_sub'] }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (! empty($row['armada']))
                                            <button type="button" @click.stop="issue(@js($row))"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3 py-2 text-ink shadow-sub outline outline-1 outline-success-deep transition-colors hover:bg-accent-deep gpa-meta font-bold tracking-[0.88px]">
                                                <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                Terbitkan SJ
                                            </button>
                                        @else
                                            <span
                                                class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-2 text-ink-quiet opacity-80 outline outline-1 outline-line-board/60 gpa-meta font-bold tracking-[0.88px]">
                                                <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                                                Kunci Estimasi Ditolak
                                            </span>
                                        @endif

                                        <a href="{{ route('prints.surat-jalan') }}" target="_blank" @click.stop
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-2 text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-disabled gpa-meta font-bold tracking-[0.88px]">
                                            <x-gpa.icon name="printer" class="h-3 w-3 shrink-0" />
                                            Cetak SJ A4
                                        </a>
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

        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-2">
                <div class="flex min-w-0 items-center gap-2">
                    <x-gpa.icon name="file-text" class="h-4 w-5 shrink-0 text-success-deep" />
                    <h2 class="font-sans text-lg font-bold leading-6 text-ink">
                        Pratinjau Surat Jalan Resmi
                        (<span x-text="selected ? selected.sj : '—'"></span>)
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span
                        class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1px] text-success-ink outline outline-1 outline-success-deep">
                        STANDAR FORMULIR RESMI GPA-SJ-REV4
                    </span>
                    <a href="{{ route('prints.surat-jalan') }}" target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface-track px-3 py-1.5 transition-colors hover:bg-surface-disabled outline outline-1 outline-line-board">
                        <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0 text-ink" />
                        <span class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">Cetak Format Resmi A4</span>
                    </a>
                </div>
            </div>

            <div
                class="relative w-full max-w-[1020px] rounded-xl bg-surface-shell/70 p-8 shadow-inner outline outline-2 outline-line-soft">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-brand/20 pb-6">
                    <div>
                        <p class="font-sans text-[1.375rem] font-extrabold uppercase leading-none text-ink">
                            PT GEMA PANEN ARTHA
                        </p>
                        <p class="mt-1 text-xs leading-[1.1rem] text-ink-body">
                            Sentra Logistik &amp; Distribusi Agribisnis Terpadu Cibitung Blok B-14, Jawa Barat<br />
                            Telepon: (021) 8984-2201 | Email: dispatch@gemapanenartha.co.id<br />
                            NPWP: 01.345.678.9-412.000 | Izin Operasional Kementan RI: 2024/SL-AGRO/088
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="gpa-micro-bold uppercase tracking-[0.9px] text-ink-quiet">SURAT JALAN RESMI (DELIVERY ORDER)</p>
                        <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">No. <span x-text="selected?.sj"></span></p>
                        <p class="font-mono text-xs font-medium tracking-[0.88px] text-ink-body">
                            <span class="text-ink-body">Ref PO:</span>
                            <span class="text-ink" x-text="selected?.id"></span>
                        </p>
                        <p class="gpa-micro-bold mt-0.5 tracking-[1.08px] text-success-deep">
                            Tanggal Terbit: <span x-text="selected?.issued_at"></span>
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 border-b border-line-soft py-4 md:grid-cols-2">
                    <div class="rounded-lg bg-surface p-3 outline outline-1 outline-line-soft">
                        <p class="gpa-micro-bold uppercase tracking-[0.45px] text-ink-quiet">Penerima / Tujuan Drop Point:</p>
                        <p class="mt-0.5 pt-0.5 font-sans text-sm font-bold leading-5 text-ink" x-text="selected?.document.client"></p>
                        <p class="text-xs leading-[1.125rem] text-ink-body">
                            <span x-text="selected?.document.drop_point"></span><br />
                            <span x-text="selected?.document.address"></span><br />
                            <span x-text="selected?.document.pic"></span>
                        </p>
                    </div>

                    <div class="rounded-lg bg-surface p-3 outline outline-1 outline-line-soft">
                        <p class="gpa-micro-bold uppercase tracking-[0.45px] text-ink-quiet">Armada Pengangkut & Supir:</p>
                        <div class="mt-1 flex items-start justify-between gap-3">
                            <div>
                                <p class="font-sans text-sm font-bold leading-5 text-ink" x-text="selected?.document.armada"></p>
                                <p class="gpa-meta-lg font-bold tracking-[0.88px] text-success-deep">
                                    No. Polisi: <span x-text="selected?.document.plate"></span>
                                </p>
                                <p class="mt-0.5 pt-0.5 text-xs leading-4 text-ink-body">
                                    Nama Supir: <span x-text="selected?.document.driver"></span>
                                    (<span x-text="selected?.document.sim"></span>)
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="gpa-micro font-semibold tracking-[1.08px] text-ink-quiet">Suhu Chiller:</p>
                                <p class="gpa-meta-lg font-bold tracking-[1.08px] text-ink" x-text="selected?.document.chiller"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="py-4">
                    <p class="gpa-micro-bold uppercase tracking-[0.45px] text-ink-quiet">Rincian Komoditas &amp; Hasil Penimbangan Bersih (Netto)</p>

                    <div class="mt-2 overflow-hidden rounded">
                        <table class="w-full min-w-[720px] border-collapse text-left">
                            <thead class="border-y-2 border-brand/20 bg-surface-track">
                                <tr>
                                    <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-ink">No</th>
                                    <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-ink">Komoditas &amp; Spesifikasi</th>
                                    <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-ink">Batch Lot</th>
                                    <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-ink text-right">Estimasi (Kg)</th>
                                    <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-ink text-right">Netto Sah (Kg)</th>
                                    <th scope="col" class="px-3 py-3 gpa-micro-bold uppercase tracking-[1.08px] text-ink text-right">Selisih (+/-)</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-line-soft">
                                <template x-for="line in (selected?.document.lines ?? [])" :key="line.no">
                                    <tr>
                                        <td class="px-3 py-3 font-mono text-xs font-medium tracking-[0.88px] text-ink"
                                            x-text="line.no"></td>
                                        <td class="px-3 py-3">
                                            <p class="text-xs font-semibold leading-4 text-ink" x-text="line.name"></p>
                                            <p class="mt-0.5 text-2xs leading-4 text-ink-body" x-text="line.meta"></p>
                                        </td>
                                        <td class="px-3 py-3">
                                            <span class="gpa-micro font-semibold tracking-[1.08px] text-ink-quiet"
                                                x-text="line.lot"></span>
                                        </td>
                                        <td class="px-3 py-3 text-right font-mono text-xs font-medium tracking-[0.88px] text-ink-body"
                                            x-text="line.estimate"></td>
                                        <td class="px-3 py-3 text-right font-mono text-xs font-bold tracking-[0.88px] text-ink"
                                            x-text="line.netto ?? 'Timbang Berlangsung'"></td>
                                        <td class="px-3 py-3 text-right font-mono text-xs font-medium tracking-[0.88px] text-danger"
                                            x-text="line.delta ?? '—'"></td>
                                    </tr>
                                </template>
                            </tbody>

                            <tfoot class="border-t-2 border-brand/20 bg-surface-track">
                                <tr>
                                    <td colspan="3" class="px-3 py-2 text-right font-mono text-xs font-medium uppercase tracking-[0.88px] text-ink">
                                        TOTAL MUATAN ARMADA:
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-xs font-medium tracking-[0.88px] text-ink-quiet"
                                        x-text="selected?.document.estimate"></td>
                                    <td class="bg-accent/40 px-3 py-2 text-right font-mono text-xs font-bold tracking-[0.88px] text-ink">
                                        <span x-text="selected?.document.netto ?? 'Timbang Berlangsung'"></span>
                                    </td>
                                    <td class="px-3 py-2 text-right font-mono text-xs font-bold tracking-[0.88px] text-danger"
                                        x-text="selected?.document.delta ?? '—'"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="rounded bg-surface-track p-3 outline outline-1 outline-line-soft">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink">Klausul Hukum &amp; Ketentuan Penerimaan Fisik:</p>
                    <p class="mt-1 text-xs leading-[1.2rem] text-ink-body">
                        Penerimaan barang wajib dihitung berdasarkan timbangan sah tercantum di atas
                        (<span class="font-semibold text-ink" x-text="selected?.document.netto ?? 'Timbang Berlangsung'"></span>).
                        Kekurangan fisik akibat susut alami telah dikompensasi secara otomatis ke dalam sistem pemotongan tagihan
                        faktur tempo sesuai ketetapan SOP Kontrak GPA Pasal 14.
                    </p>
                </div>

                <div class="grid gap-4 border-t border-line-soft pt-10 sm:grid-cols-3">
                    <div class="flex min-h-[9rem] flex-col justify-between gap-1 rounded-lg bg-surface p-3 outline outline-1 outline-line-soft">
                        <div class="pt-[3px] text-center">
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Petugas Timbangan:</p>
                            <p class="mt-0.5 text-xs font-semibold leading-4 text-ink">Staf Gudang &amp; Penimbangan</p>
                        </div>

                        <p class="rounded bg-accent/30 py-1 text-center gpa-micro-bold uppercase tracking-[1px] text-success-deep outline outline-1 outline-accent">
                            TERA VALID - AUTOMATED
                        </p>

                        <div class="pt-1 text-center">
                            <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink"
                                x-text="selected?.document.officer ?? '—'"></p>
                            <p class="mt-0.5 text-xs leading-4 text-ink-quiet"
                                x-text="selected?.document.officer_note ?? '—'"></p>
                        </div>
                    </div>

                    <div class="flex min-h-[9rem] flex-col justify-between gap-1 rounded-lg bg-surface p-3 outline outline-1 outline-line-soft">
                        <div class="pt-[3px] text-center">
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Supir Pengantar:</p>
                            <p class="mt-0.5 text-xs font-semibold leading-4 text-ink">Armada Logistik GPA</p>
                        </div>

                        <p class="text-center font-mono italic text-2xs font-semibold tracking-[1.08px] text-ink-quiet">
                            (Tanda Tangan &amp; Nama Jelas)
                        </p>

                        <div class="pt-1 text-center">
                            <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink" x-text="selected?.document.driver ?? 'Joko Prasetyo'"></p>
                            <p class="mt-0.5 text-xs leading-4 text-ink-quiet">Membawa fisik &amp; cek segel cold chain</p>
                        </div>
                    </div>

                    <div class="flex min-h-[9rem] flex-col justify-between gap-1 rounded-lg bg-surface p-3 outline outline-1 outline-line-soft">
                        <div class="pt-[3px] text-center">
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">Penerima Barang:</p>
                            <p class="mt-0.5 text-xs font-semibold leading-4 text-ink">Perwakilan Drop Point / Klien</p>
                        </div>

                        <p class="text-center font-mono italic text-2xs font-semibold tracking-[1.08px] text-ink-quiet">
                            (Cap Stempel &amp; Tanda Tangan)
                        </p>

                        <div class="pt-1 text-center">
                            <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink" x-text="selected?.document.pic ?? '(Nama Jelas Penerima)'"></p>
                            <p class="mt-0.5 text-xs leading-4 text-ink-quiet">Wajib periksa fisik &amp; kesesuaian timbang</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-line-soft pt-6">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="shield" class="h-[15px] w-[15px] shrink-0 text-ink" />
                        <div>
                            <p class="gpa-meta-lg font-bold tracking-[1.1px] text-ink" x-text="'*' + (selected?.sj ?? '—') + '-SECURE*'"></p>
                            <p class="gpa-micro font-semibold tracking-[1.08px] text-ink-quiet">
                                Kunci Otorisasi Digital:
                                <span x-text="selected?.auth_key"></span>
                            </p>
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="gpa-micro font-semibold tracking-[1.08px] text-ink-quiet">Dokumen Sah Halaman 1 dari 1</p>
                        <p class="gpa-micro font-semibold tracking-[1.08px] text-line-board">Lembar 1: Asli Klien | Lembar 2: Supir | Lembar 3: Arsip Gudang</p>
                    </div>
                </div>

                <div
                    class="absolute right-8 top-28 -rotate-12 rounded-xl bg-accent/20 px-4 py-2 text-center outline outline-4 outline-success-deep/70">
                    <p class="gpa-micro-bold uppercase tracking-[1px] text-success-deep">SAH &amp; TERVERIFIKASI</p>
                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">GUDANG SENTRA GPA</p>
                    <p class="gpa-micro-bold tracking-[1.08px] text-success-ink">TERA PASAL 14</p>
                </div>
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
                        Penerbitan surat jalan akan mengunci data timbangan bersih
                        <span class="font-mono font-bold text-white" x-text="selected?.netto ?? 'Timbang Berlangsung'"></span>
                        secara permanen, mencatat log keberangkatan armada, dan mengirimkan notifikasi siap terima ke pihak klien.
                    </p>
                    <p x-cloak x-show="selected && selected.state !== 'ready'"
                        class="mt-1 inline-flex rounded bg-danger px-2 py-0.5 gpa-micro-bold uppercase tracking-[1.08px] text-white">
                        Hard Gate Aktif: Timbangan Belum Sah Tera
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button type="button" @click="saveDraft()"
                    class="rounded-lg bg-white/10 px-5 py-2.5 text-center font-mono text-xs font-semibold tracking-[0.88px] text-canvas outline outline-1 outline-line-board/40 transition-colors hover:bg-white/15">
                    Simpan Draf Saja
                </button>

                <button type="button" @click="confirmRelease()" :disabled="! selected || selected.state !== 'ready'"
                    :class="
                        ! selected || selected.state !== 'ready'
                            ? 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'
                            : 'bg-accent text-ink hover:bg-accent-deep'
                    "
                    class="inline-flex items-center gap-2 rounded-lg px-6 py-2.5 font-mono text-xs font-bold tracking-[0.88px] shadow-sub transition-colors">
                    <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                    <span>Kunci Data &amp; Terbitkan Surat Jalan Resmi</span>
                </button>
            </div>
        </section>
    </div>
@endsection