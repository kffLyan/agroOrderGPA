@extends('layouts.sekretaris')

@section('title', 'Surat Jalan')

@php
    $rows = $rows ?? [];
@endphp

@section('content')
    <div class="space-y-3" x-data="secretaryDispatch(@js($rows))"
        @gpa:search.window="query = $event.detail">
        <section class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-4 sm:p-6 shadow-card ring-1 ring-line-hair">
            <div class="min-w-0 flex-1">
                <p class="flex items-center gap-1 gpa-note text-success-deep">
                    Surat Jalan &amp; Pengiriman
                </p>

                <h1 class="mt-1 sm:mt-2 font-sans text-xl sm:text-2xl lg:text-3xl font-bold leading-tight sm:leading-10 tracking-[-0.01em] text-ink">
                    Penerbitan Surat Jalan Resmi &amp; Penugasan Supir Pengantar
                </h1>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('sekretaris.monitoring-pod') }}"
                    class="inline-flex items-center gap-1.5 rounded-full bg-accent px-4 py-1.5 text-xs font-bold text-ink shadow-xs transition-transform hover:scale-105 ring-1 ring-inset ring-accent-edge">
                    <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 text-success-deep" />
                    <span>Monitoring PoD Armada &rarr;</span>
                </a>
                <span
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-surface-pill py-1.5 pl-3 pr-3 text-[10px] font-bold uppercase text-ink-body ring-1 ring-inset ring-line-board">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span>SOP Rilis Lunas</span>
                </span>
            </div>
        </section>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex min-h-8 items-center justify-between gap-2">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Surat Jalan Siap Terbit
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-accent/30">
                        <x-gpa.icon name="file-text" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                    </span>
                </div>

                <div>
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($readyOrdersCount ?? 0) }} Dokumen
                    </p>
                </div>
            </article>

            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex min-h-8 items-center justify-between gap-2">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Dalam Perjalanan Supir
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-track">
                        <x-gpa.icon name="truck" class="h-[15px] w-[15px] shrink-0 text-ink" />
                    </span>
                </div>

                <div>
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($shippingOrdersCount ?? 0) }} Mobil
                    </p>
                </div>
            </article>

            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex min-h-8 items-center justify-between gap-2">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Menunggu Bukti Terima
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-warning-soft/30">
                        <x-gpa.icon name="package" class="h-[15px] w-[15px] shrink-0 text-warning-deep" />
                    </span>
                </div>

                <div>
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($shippingOrdersCount ?? 0) }}
                        Pengiriman
                    </p>
                </div>
            </article>

            <article class="flex flex-col gap-2 rounded-2xl bg-surface p-4 shadow-card">
                <div class="flex min-h-8 items-center justify-between gap-2">
                    <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                        Selesai Terkirim
                    </h2>
                    <span class="shrink-0 rounded-lg p-1.5 bg-surface-pill">
                        <x-gpa.icon name="check-circle" class="h-[15px] w-[15px] shrink-0 text-ink" />
                    </span>
                </div>

                <div>
                    <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                        {{ ($doneOrdersCount ?? 0) }} Pesanan
                    </p>
                </div>
            </article>
        </div>

        <section class="gpa-panel overflow-hidden rounded-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft bg-surface-shell/40 p-6">
                <div class="min-w-0">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold leading-6 text-ink">
                        Antrean Surat Jalan Siap Terbit &amp; Penugasan Supir
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded bg-accent px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-ink">
                        <span x-text="readyCount() + ' Siap Rilis'"></span>
                    </span>
                    <span class="rounded bg-surface-disabled px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body">
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
                        @foreach ($rows as $index => $row)
                            <tr x-data="{ r: rows[{{ $index }}] }"
                                x-show="isVisible(r)"
                                @click="select(r.id)"
                                :class="rowClass(r)"
                                class="cursor-pointer transition-colors">
                                <td class="px-4 py-4">
                                    <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink" x-text="r.id"></p>
                                    <p class="mt-0.5 pt-0.5 font-sans text-sm font-semibold leading-5 text-ink" x-text="r.client">
                                    </p>
                                    <p class="mt-1 text-xs leading-4 text-ink-body" x-text="r.dock"></p>
                                    <p x-cloak x-show="isReleased(r.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(r.order_status)"
                                        class="mt-1 inline-flex rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">
                                        Sudah Terbit
                                    </p>
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    <span class="gpa-note text-ink-body" x-text="r.estimate"></span>
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
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]"
                                        :class="r.status_chip ? r.status_chip.class : '{{ $row['status_chip']['class'] }}'">
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                        <span x-text="r.status_chip ? r.status_chip.label : '{{ $row['status_chip']['label'] }}'"></span>
                                    </span>
                                    <p class="mt-1 font-mono text-xs leading-4 text-ink-body">{{ $row['tera'] }}</p>
                                </td>

                                <td class="px-4 py-4" @click.stop>
                                    {{-- KONDISI 1: SUDAH DITERBITKAN -> TERKUNCI PERMANEN (TIDAK BISA DIUBAH) --}}
                                    <template x-if="isReleased(r.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(r.order_status)">
                                        <div class="space-y-1.5 min-w-[210px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-2xs font-bold uppercase tracking-wider text-ink-muted">Supir Bertugas:</span>
                                                <span class="inline-flex items-center gap-1 rounded bg-surface-pill px-1.5 py-0.5 text-2xs font-semibold text-ink-quiet" title="Penugasan armada terkunci permanen setelah Surat Jalan terbit">
                                                    <x-gpa.icon name="lock" class="h-2.5 w-2.5 text-ink-muted" />
                                                    Terkunci
                                                </span>
                                            </div>

                                            <div class="rounded-lg border border-line-board/70 bg-surface-shell/40 px-3 py-2 shadow-xs">
                                                <p class="text-xs font-bold text-ink" x-text="r.driver || r.driver_name || 'Belum Ditugaskan'"></p>
                                                <div class="mt-1 flex items-center justify-between text-2xs text-ink-body">
                                                    <span class="font-mono text-brand font-semibold" x-text="'Plat: ' + (r.plate && r.plate !== '—' ? r.plate : 'B 9014 KPA')"></span>
                                                    <span class="inline-flex items-center gap-0.5 text-success-deep font-semibold">
                                                        <x-gpa.icon name="check" class="h-2.5 w-2.5" />
                                                        Sah Terbit
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- KONDISI 2: SEBELUM TERBIT -> BEBAS MEMILIH SUPIR DARI AKUN ARMADA --}}
                                    <template x-if="!isReleased(r.id) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(r.order_status)">
                                        <div class="space-y-1.5 min-w-[210px]">
                                            <div class="flex items-center justify-between">
                                                <span class="text-2xs font-bold uppercase tracking-wider text-ink-muted">Tugaskan Supir:</span>
                                                <span class="font-mono text-2xs font-semibold text-brand" title="Plat Armada" x-text="r.plate && r.plate !== '—' ? r.plate : 'B 9014 KPA'"></span>
                                            </div>

                                            <div class="relative">
                                                <select
                                                    x-model="r.driver_id"
                                                    @change="updateDriver(r.order_id, $event.target.value, r)"
                                                    class="w-full rounded-lg border border-line-board bg-white py-1.5 pl-2.5 pr-7 text-xs font-semibold text-ink shadow-sm transition-colors hover:border-brand focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand">
                                                    <option value="" disabled :selected="!r.driver_id">-- Pilih Supir Armada --</option>
                                                    @foreach ($drivers as $drv)
                                                        <option value="{{ $drv->id }}">
                                                            {{ $drv->name }} ({{ $drv->phone ?: 'Armada' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex items-center justify-between text-2xs text-ink-body">
                                                <span class="inline-flex items-center gap-1 font-medium">
                                                    <x-gpa.icon name="truck" class="h-3 w-3 text-brand" />
                                                    <span x-text="r.armada || 'Armada Logistik GPA'"></span>
                                                </span>
                                                <span class="font-medium text-warning-deep">Pilih sebelum terbit</span>
                                            </div>
                                        </div>
                                    </template>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- SESI 1: Menunggu Penerbitan (Tombol Terbitkan Surat Jalan & Cetak Struk) --}}
                                        <template x-if="!isReleased(r.id) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(r.order_status)">
                                            <div>
                                                <template x-if="r.order_status === 'LUNAS' || r.state === 'ready'">
                                                    <div class="flex items-center gap-2">
                                                        <a :href="'/print/struk/' + r.id" target="_blank" @click.stop
                                                            title="Lihat &amp; Cetak Struk Kasir Thermal"
                                                            class="inline-flex items-center gap-1 rounded-lg border border-line-soft bg-surface px-2.5 py-2 text-xs font-bold text-ink shadow-xs transition-colors hover:bg-surface-muted hover:text-brand">
                                                            <x-gpa.icon name="receipt" class="h-3.5 w-3.5 text-brand" />
                                                            <span>Struk</span>
                                                        </a>

                                                        <button type="button" @click.stop="issue(r)"
                                                            class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-3 py-2 text-ink shadow-sub outline outline-1 outline-success-deep transition-colors hover:bg-accent-deep gpa-meta font-bold tracking-[0.88px]">
                                                            <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                            Terbitkan Surat Jalan
                                                        </button>
                                                    </div>
                                                </template>
                                                <template x-if="r.order_status !== 'LUNAS' && r.state !== 'ready'">
                                                    <span
                                                        class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-2 text-ink-quiet opacity-80 outline outline-1 outline-line-board/60 gpa-meta font-bold tracking-[0.88px]"
                                                        title="Menunggu verifikasi pembayaran lunas oleh Sekretaris">
                                                        <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                                                        Menunggu Lunas
                                                    </span>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- SESI 2: Surat Jalan Sudah Terbit (Tombol Cetak Surat Jalan muncul setelah diterbitkan Sekre) --}}
                                        <template x-if="isReleased(r.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(r.order_status)">
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center gap-1 rounded bg-accent/40 px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                                    <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                                                    Diterbitkan
                                                </span>
                                                <a :href="'/print/struk/' + r.id" target="_blank" @click.stop
                                                    title="Lihat &amp; Cetak Struk Kasir Thermal"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-line-soft bg-surface px-2.5 py-2 text-xs font-bold text-ink shadow-xs transition-colors hover:bg-surface-muted hover:text-brand">
                                                    <x-gpa.icon name="receipt" class="h-3.5 w-3.5 text-brand" />
                                                    <span>Struk</span>
                                                </a>

                                                <a :href="'/print/surat-jalan?order_id=' + r.order_id" target="_blank" @click.stop
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-3.5 py-2 text-white shadow transition-colors hover:bg-brand-hover gpa-meta font-bold tracking-[0.88px]">
                                                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                                                    Print
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

            <div class="flex flex-col gap-3 border-t border-line-soft bg-surface-shell px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="gpa-note text-ink-body" x-text="summaryLabel()">
                    Menampilkan 1-10 dari {{ count($rows) }} dokumen dalam antrean dispatch
                </p>

                <nav class="flex items-center gap-1" aria-label="Navigasi halaman pengiriman" x-show="lastPage > 1">
                    <button type="button" @click="goToPage(page - 1)" :disabled="page === 1"
                        class="rounded bg-surface px-2.5 py-1 text-xs font-semibold leading-4 text-ink-body outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed">
                        Sebelumnya
                    </button>

                    <template x-for="number in pageNumbers()" :key="number">
                        <button type="button" @click="goToPage(number)"
                            class="rounded px-2.5 py-1 text-xs font-semibold leading-4 transition-colors cursor-pointer"
                            :class="page === number ? 'bg-brand font-bold text-white' : 'bg-surface-pill text-ink-body hover:bg-surface-disabled'"
                            :aria-current="page === number ? 'page' : null"
                            x-text="number"></button>
                    </template>

                    <button type="button" @click="goToPage(page + 1)" :disabled="page === lastPage"
                        class="rounded bg-surface px-2.5 py-1 text-xs font-semibold leading-4 text-ink-body outline outline-1 outline-line-board transition-colors enabled:hover:bg-surface-muted disabled:opacity-40 cursor-pointer disabled:cursor-not-allowed">
                        Selanjutnya
                    </button>
                </nav>
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
                    <template x-if="selected">
                        <p class="mt-1 text-2xs text-accent font-medium">
                            Pesanan Terpilih: <strong class="text-white" x-text="selected.id"></strong> &bull;
                            Supir Ditugaskan: <strong class="text-white" x-text="selected.driver || selected.driver_name || 'Belum Ditugaskan'"></strong> &bull;
                            Plat Armada: <strong class="text-white font-mono" x-text="(selected.plate && selected.plate !== '—') ? selected.plate : 'B 9014 KPA'"></strong>
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

                {{-- SESI 1: Belum Terbit -> Terbitkan & Cetak Struk --}}
                <template x-if="selected && !isReleased(selected.id) && !['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(selected.order_status)">
                    <div class="flex flex-wrap items-center gap-2">
                        <template x-if="selected.order_status === 'LUNAS' || selected.state === 'ready'">
                            <a :href="'/print/struk/' + (selected.id || '')" target="_blank"
                                class="inline-flex items-center gap-2 rounded-lg border border-line-soft bg-surface px-4 py-2.5 font-mono text-xs font-bold tracking-[0.88px] text-ink shadow-xs hover:bg-surface-muted transition-colors">
                                <x-gpa.icon name="receipt" class="h-3.5 w-3.5 text-brand" />
                                <span>Cetak Struk Kasir</span>
                            </a>
                        </template>

                        <button type="button" @click="confirmRelease()" :disabled="! selected || (!isReady(selected) && selected.state !== 'ready')"
                            :class="! selected || (!isReady(selected) && selected.state !== 'ready')
                                ? 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'
                                : 'bg-accent text-ink hover:bg-accent-deep'"
                            class="inline-flex items-center gap-2 rounded-lg px-6 py-2.5 font-mono text-xs font-bold tracking-[0.88px] shadow-sub transition-colors">
                            <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                            <span>Kunci Data &amp; Terbitkan Surat Jalan Resmi</span>
                        </button>
                    </div>
                </template>

                {{-- SESI 2: Sudah Terbit -> Cetak Surat Jalan & Struk --}}
                <template x-if="selected && (isReleased(selected.id) || ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN'].includes(selected.order_status))">
                    <div class="flex flex-wrap items-center gap-2">
                        <a :href="'/print/struk/' + (selected.id || '')" target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg border border-line-soft bg-surface px-4 py-2.5 font-mono text-xs font-bold tracking-[0.88px] text-ink shadow-xs hover:bg-surface-muted transition-colors">
                            <x-gpa.icon name="receipt" class="h-3.5 w-3.5 text-brand" />
                            <span>Cetak Struk Kasir</span>
                        </a>

                        <a :href="'/print/surat-jalan?order_id=' + (selected.order_id || '')" target="_blank"
                            class="inline-flex items-center gap-2 rounded-lg bg-accent px-6 py-2.5 font-mono text-xs font-bold tracking-[0.88px] text-ink shadow-sub hover:bg-accent-deep transition-colors">
                            <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                            <span>Cetak Surat Jalan (Nota/A5)</span>
                        </a>
                    </div>
                </template>
            </div>
        </section>
    </div>
@endsection
