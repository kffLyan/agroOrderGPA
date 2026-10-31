@extends('layouts.sekretaris')

@section('title', 'Dashboard Sekretaris Operasional')

@php
    $queueRows = $queueRows ?? [];
    $stockRows = $stockRows ?? [];
@endphp

@section('content')
    <div class="space-y-6">
        <section class="gpa-panel overflow-hidden">
            <div class="border-b border-line-soft px-6 py-6">
                <h1 class="max-w-4xl font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Dashboard Konsol Operasional &amp; Tata Kelola Administrasi
            </div>

            <div class="flex flex-wrap gap-2 px-6 py-5">
                <a href="{{ route('sekretaris.verifikasi') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-full bg-surface px-3.5 shadow-sub outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="badge-check" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-meta-lg font-semibold tracking-[0.28px] text-ink">Batch Validasi ({{ $pendingOrdersCount ?? 0 }} PO Masuk)</span>
                </a>

                <a href="{{ route('sekretaris.pembayaran') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-full bg-surface px-3.5 shadow-sub outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="refresh" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-meta-lg font-semibold tracking-[0.28px] text-ink">Antrean Bukti Transfer ({{ $pendingPaymentsCount ?? 0 }} Menunggu)</span>
                </a>

                <a href="{{ route('sekretaris.surat-jalan') }}"
                    class="inline-flex h-9 items-center gap-1.5 rounded-full bg-surface px-3.5 shadow-sub outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="receipt" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    <span class="gpa-meta-lg font-semibold tracking-[0.28px] text-ink">Siap Terbit Surat Jalan ({{ $readyOrdersCount ?? 0 }} PO)</span>
                </a>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Kartu 1: Total Pesanan Masuk --}}
            <article class="flex flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <p class="gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                        Total Pesanan Masuk
                    </p>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/50 p-1.5 text-success-deep">
                        <x-gpa.icon name="gauge" class="h-[15px] w-[15px]" />
                    </span>
                </div>

                <div>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <span class="font-sans text-[2rem] font-bold leading-10 tracking-[-0.01em] text-ink">
                            {{ $allOrdersCount ?? 0 }}
                        </span>
                        <span class="text-sm font-medium text-ink-body">
                            Pesanan Terjadwal
                        </span>
                    </div>
                    <p class="mt-2 text-xs leading-4">
                        <span class="font-bold text-ink">Hari ini: </span>
                        <span class="text-ink-body">{{ $totalTodayCount ?? 0 }} PO baru masuk via web portal</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                    <p class="gpa-note text-ink-body">Target Layanan</p>
                    <span class="gpa-meta-lg font-bold tracking-[0.28px] text-success-deep">Target &lt; 2 Jam</span>
                </div>
            </article>

            {{-- Kartu 2: Menunggu Verifikasi PO --}}
            <article class="flex flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <p class="gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                        Menunggu Verifikasi PO
                    </p>
                    <span class="shrink-0 rounded border border-warning/40 bg-warning-wash px-1.5 py-0.5 gpa-micro font-bold text-warning-deep">
                        PRIORITAS
                    </span>
                </div>

                <div>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <span class="font-sans text-[2rem] font-bold leading-10 tracking-[-0.01em] text-warning-deep">
                            {{ $pendingOrdersCount ?? 0 }}
                        </span>
                        <span class="text-sm font-medium text-ink-body">
                            PO Butuh Tindakan
                        </span>
                    </div>
                    <p class="mt-2 text-xs leading-4">
                        <span class="font-bold text-ink">Cut-off subuh: </span>
                        <span class="text-ink-body">Kunci stok sebelum alokasi sortir kebun</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                    <p class="gpa-note text-ink-body">Antrean Terlama</p>
                    <span class="gpa-meta-lg font-bold tracking-[0.28px] text-warning-deep">{{ ($pendingOrdersCount ?? 0) > 0 ? 'Prioritas Antrean' : 'Antrean Bersih' }}</span>
                </div>
            </article>

            {{-- Kartu 3: Penimbangan & Siap Kirim --}}
            <article class="flex flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <p class="gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                        Penimbangan &amp; Siap Kirim
                    </p>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/50 p-1.5 text-success-deep">
                        <x-gpa.icon name="truck" class="h-[15px] w-[15px]" />
                    </span>
                </div>

                <div>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <span class="font-sans text-[2rem] font-bold leading-10 tracking-[-0.01em] text-ink">
                            {{ ($readyOrdersCount ?? 0) + ($shippingOrdersCount ?? 0) }}
                        </span>
                        <span class="text-sm font-medium text-ink-body">
                            Armada Terjadwal
                        </span>
                    </div>
                    <p class="mt-2 text-xs leading-4">
                        <span class="font-bold text-success-deep">Netto Tera: </span>
                        <span class="text-ink-body">Deviasi &lt; 0.5% batas aman metrologi sah</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                    <p class="gpa-note text-ink-body">Status Armada</p>
                    <span class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">{{ $shippingOrdersCount ?? 0 }} Dalam Perjalanan</span>
                </div>
            </article>

            {{-- Kartu 4: Verifikasi Pembayaran --}}
            <article class="flex flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex items-start justify-between gap-3">
                    <p class="gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                        Verifikasi Pembayaran
                    </p>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-pill p-1.5 text-ink">
                        <x-gpa.icon name="banknote" class="h-[15px] w-[15px]" />
                    </span>
                </div>

                <div>
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <span class="font-sans text-[2rem] font-bold leading-10 tracking-[-0.01em] text-ink">
                            {{ $pendingPaymentsCount ?? 0 }}
                        </span>
                        <span class="text-sm font-medium text-ink-body">
                            Struk / Mutasi Bank
                        </span>
                    </div>
                    <p class="mt-2 text-xs leading-4">
                        <span class="font-bold text-ink">Klien Reguler: </span>
                        <span class="text-ink-body">Pencocokan saldo rekening koran BCA &amp; Mandiri</span>
                    </p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                    <p class="gpa-note text-ink-body">Penyelesaian</p>
                    <span class="gpa-meta-lg font-bold tracking-[0.28px] text-success-deep">100% Hari Ini</span>
                </div>
            </article>
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]">
            <div class="min-w-0 space-y-4">
                <section class="gpa-panel overflow-hidden">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft px-6 py-5">
                        <div class="min-w-0">
                            <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                                Antrean Pesanan Menunggu Verifikasi Administrasi (PO Baru)
                            </h2>
                        </div>
                        <span class="shrink-0 rounded bg-surface-pill px-2 py-1 gpa-micro text-ink-body">
                            Prioritas Pengiriman Hari Ini
                        </span>
                    </div>

                    <div class="gpa-scroll-x">
                        <table class="w-full border-collapse text-left">
                            <thead class="bg-surface-shell">
                                <tr class="border-b border-line-soft">
                                    <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body first:pl-6">
                                        No. PO &amp; Klien
                                    </th>
                                    <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                                        Rincian Komoditas &amp; Tonase
                                    </th>
                                    <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                                        Status &amp; Catatan
                                    </th>
                                    <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body last:pr-6 text-right">
                                        Aksi Cepat
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line-faint">
                                @foreach ($queueRows as $row)
                                    <tr x-show="isRowVisible(@js($row))" class="transition-colors hover:bg-surface-shell/60">
                                        <td class="px-4 py-4 align-top first:pl-6">
                                            <div class="flex flex-col gap-0.5">
                                                <span class="gpa-note text-ink-subtle">{{ $row['po'] }}</span>
                                                <span class="text-xs font-bold leading-5 text-ink">{{ $row['client'] }}</span>
                                                @if (! empty($row['po_chip']))
                                                    <span
                                                        class="mt-0.5 w-fit rounded px-1.5 py-0.5 gpa-micro {{ $row['po_chip']['class'] }}">
                                                        {{ $row['po_chip']['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <p class="gpa-note {{ $row['terms']['class'] }}">{{ $row['terms']['label'] }}</p>
                                            <ul class="mt-1 flex flex-col gap-0.5">
                                                @foreach ($row['items'] as $item)
                                                    <li class="text-xs {{ $item['class'] }}">
                                                        {{ $item['name'] }}
                                                        <span class="gpa-note text-ink-body">({{ $item['weight'] }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-1 gpa-note text-success-deep">Est: {{ $row['estimate'] }}</p>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <span
                                                class="inline-flex items-center gap-1 rounded border px-1.5 py-0.5 gpa-micro {{ $row['status']['class'] }}">
                                                <x-gpa.icon :name="$row['status']['icon']" class="h-3 w-3 shrink-0" />
                                                {{ $row['status']['label'] }}
                                            </span>
                                            <p class="mt-1 gpa-note {{ $row['status_note']['class'] }}">
                                                {{ $row['status_note']['label'] }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4 align-top last:pr-6">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @foreach ($row['actions'] as $action)
                                                    @php
                                                        $actionClass = match ($action['tone']) {
                                                            'ink' => 'bg-ink text-white hover:bg-brand-deep',
                                                            'warning' => 'bg-warning-deep text-white hover:bg-warning',
                                                            default => 'bg-surface-pill text-ink-body hover:bg-surface-disabled hover:text-ink',
                                                        };
                                                    @endphp
                                                    <a href="{{ $action['url'] ?? route('sekretaris.verifikasi') }}"
                                                        class="inline-flex h-7 items-center justify-center rounded px-2 gpa-micro transition-colors {{ $actionClass }}">
                                                        {{ $action['label'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                <tr x-cloak x-show="visibleRows(@js($queueRows)).length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center">
                                        <p class="text-xs font-semibold text-ink">Tidak ada PO yang cocok dengan pencarian.</p>
                                        <p class="mt-1 gpa-note text-ink-quiet">Coba nomor PO lain, nama klien, atau nama komoditas.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft bg-surface-shell px-6 py-3">
                        <p class="gpa-note text-ink-body"
                            x-text="'Menampilkan ' + visibleRows(@js($queueRows)).length + ' dari ' + pendingCount(@js($queueRows)) + ' antrean prioritas cut-off 16:00 WIB'">
                        </p>
                        <div class="flex items-center gap-3">
                            <button type="button" x-cloak x-show="resolved.length > 0" @click="resolved = []"
                                class="gpa-note text-success-deep underline underline-offset-2 hover:text-success">
                                <span x-text="'Tampilkan ' + resolved.length + ' PO Diproses'"></span>
                            </button>
                            <a href="{{ route('sekretaris.verifikasi') }}"
                                class="gpa-note text-ink-body underline underline-offset-2 hover:text-ink">
                                Buka Semua Antrean PO &rarr;
                            </a>
                        </div>
                    </div>
                </section>

                <section class="gpa-panel p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold text-ink">Pipeline Operasional Siklus Pesanan Hari Ini</h2>
                            <p class="mt-1 text-xs text-ink-body">Siklus hidup pesanan dari pemesanan klien hingga penagihan piutang dan penerbitan faktur sah.</p>
                        </div>
                        <span class="shrink-0 rounded bg-surface-pill px-2 py-1 gpa-micro-bold text-ink">
                            Realtime Tracking
                        </span>
                    </div>

                    <ol class="grid grid-cols-2 gap-3 pt-4 sm:grid-cols-3 xl:grid-cols-5">
                        <li @class([
                            'rounded-xl border bg-surface-shell p-3',
                            'border-warning/60 bg-warning-soft/20' => ($pendingOrdersCount ?? 0) > 0,
                            'border-line-soft' => ($pendingOrdersCount ?? 0) <= 0,
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full gpa-micro-bold bg-warning-soft text-warning-deep">
                                    1
                                </span>
                                <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                            </div>
                            <p class="mt-3 font-sans text-lg font-bold leading-6 text-ink">{{ $pendingOrdersCount ?? 0 }} PO</p>
                            <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">Verifikasi PO</p>
                            <p class="mt-0.5 gpa-meta text-warning-deep font-semibold">Sekretariat GPA</p>
                        </li>

                        <li @class([
                            'rounded-xl border bg-surface-shell p-3',
                            'border-success-deep/40' => ($verifiedOrdersCount ?? 0) > 0,
                            'border-line-soft' => ($verifiedOrdersCount ?? 0) <= 0,
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full gpa-micro-bold bg-surface-pill text-ink">
                                    2
                                </span>
                                <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                            </div>
                            <p class="mt-3 font-sans text-lg font-bold leading-6 text-ink">{{ $verifiedOrdersCount ?? 0 }} PO</p>
                            <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">Alokasi Panen</p>
                            <p class="mt-0.5 gpa-meta text-ink-body">Sentra Kebun</p>
                        </li>

                        <li @class([
                            'rounded-xl border bg-surface-shell p-3',
                            'border-success-deep/40' => ($readyOrdersCount ?? 0) > 0,
                            'border-line-soft' => ($readyOrdersCount ?? 0) <= 0,
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full gpa-micro-bold bg-surface-pill text-ink">
                                    3
                                </span>
                                <x-gpa.icon name="scale" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                            </div>
                            <p class="mt-3 font-sans text-lg font-bold leading-6 text-ink">{{ $readyOrdersCount ?? 0 }} PO</p>
                            <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">Sortir &amp; Timbang</p>
                            <p class="mt-0.5 gpa-meta text-ink-body">Tera Netto Sah</p>
                        </li>

                        <li @class([
                            'rounded-xl border bg-surface-shell p-3',
                            'border-success-deep/40 bg-accent/10' => ($shippingOrdersCount ?? 0) > 0,
                            'border-line-soft' => ($shippingOrdersCount ?? 0) <= 0,
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <span @class([
                                    'inline-flex h-6 w-6 items-center justify-center rounded-full gpa-micro-bold font-bold',
                                    'bg-accent text-success-ink' => ($shippingOrdersCount ?? 0) > 0,
                                    'bg-surface-pill text-ink' => ($shippingOrdersCount ?? 0) <= 0,
                                ])>
                                    4
                                </span>
                                <x-gpa.icon name="truck" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                            </div>
                            <p class="mt-3 font-sans text-lg font-bold leading-6 text-ink">{{ $shippingOrdersCount ?? 0 }} Truk</p>
                            <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">Armada Chiller</p>
                            <p class="mt-0.5 gpa-meta text-success-deep font-semibold">Suhu 2 - 6&deg;C</p>
                        </li>

                        <li @class([
                            'rounded-xl border bg-surface-shell p-3',
                            'border-success-deep/40' => ($completedOrdersCount ?? 0) > 0,
                            'border-line-soft' => ($completedOrdersCount ?? 0) <= 0,
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full gpa-micro-bold bg-surface-pill text-ink">
                                    5
                                </span>
                                <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                            </div>
                            <p class="mt-3 font-sans text-lg font-bold leading-6 text-ink">{{ $completedOrdersCount ?? 0 }} PO</p>
                            <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">Selesai Dock</p>
                            <p class="mt-0.5 gpa-meta text-ink-body">BAST Digital Sah</p>
                        </li>
                    </ol>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                        <p class="flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <span class="gpa-note text-ink-body">Ketepatan Pengiriman Subuh GPA:</span>
                            <span class="gpa-note text-ink">99.4% On-Time</span>
                        </p>
                        <p class="gpa-note text-ink">Window Bongkar: 04.00 - 06.00 WIB</p>
                    </div>
                </section>
            </div>

            <div class="min-w-0 space-y-4">
                <section class="gpa-panel p-6">
                    <div class="flex items-start justify-between gap-3 border-b border-line-soft pb-3">
                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold text-ink">Status Kesiapan Buffer Stock Panen</h2>
                        </div>
                        <x-gpa.icon name="package" class="h-3 w-3 shrink-0 text-ink-body" />
                    </div>

                    <ul class="space-y-4 pt-4">
                        @foreach ($stockRows as $row)
                            <li class="rounded-xl border p-2.5 {{ $row['card_class'] }}">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="flex items-center gap-1.5 text-xs font-bold leading-5 text-ink">
                                        @if ($row['alert'])
                                            <x-gpa.icon name="alert-triangle" class="h-3.5 w-3.5 shrink-0 text-danger" />
                                        @endif
                                        {{ $row['name'] }}
                                    </p>
                                    <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro {{ $row['chip']['class'] }}">
                                        {{ $row['chip']['label'] }}
                                    </span>
                                </div>

                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-pill"
                                    role="img" aria-label="{{ $row['chip']['label'] }}">
                                    <div class="h-full rounded-full {{ $row['fill_class'] }}" style="width: {{ $row['percent'] }}%"></div>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                    @foreach ($row['foot'] as $note)
                                        <p class="gpa-note {{ $note['class'] }}">{{ $note['label'] }}</p>
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                        <p class="gpa-note text-ink-body">Pembaruan telemetri otomatis pasca timbangan tera.</p>
                        <a href="{{ route('sekretaris.stok') }}"
                            class="gpa-meta-lg font-semibold tracking-[0.88px] text-success-deep hover:text-success">
                            Lihat Detail Stok Gudang &rarr;
                        </a>
                    </div>
                </section>

                <section class="gpa-panel p-6">
                    <div class="flex items-center gap-2 border-b border-line-soft pb-3">
                        <x-gpa.icon name="alert-circle" class="h-4 w-[17px] shrink-0 text-warning" />
                        <h2 class="font-sans text-lg font-bold text-ink">Peringatan &amp; Catatan Operasional Kritis</h2>
                    </div>

                    <ul class="space-y-3 pt-3">
                        <li x-show="! dismissed.includes(0)"
                            class="rounded-xl border-l-4 border-warning bg-surface-shell p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex min-w-0 items-start gap-2">
                                    <x-gpa.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0 text-warning-deep" />
                                    <p class="gpa-meta-lg font-bold leading-[0.875rem] tracking-[0.88px] text-ink">
                                        Mendekati Batas Cut-Off Subuh (16:00 WIB)
                                    </p>
                                </div>
                                <span class="shrink-0 gpa-note font-bold text-warning-deep">Perhatian Jadwal</span>
                            </div>
                            <p class="mt-1 pl-6 text-xs leading-[1.375rem] text-ink-body">
                                Terdapat {{ $pendingOrdersCount ?? 3 }} pesanan masuk yang belum diverifikasi. Harap segera kunci stok sebelum jadwal sortasi malam.
                            </p>
                            <button type="button" @click="dismiss(0)"
                                class="mt-1 pl-6 gpa-note text-ink-quiet underline underline-offset-2 hover:text-ink">
                                Arsipkan
                            </button>
                        </li>

                        <li x-show="! dismissed.includes(1)"
                            class="rounded-xl border-l-4 border-accent bg-surface-shell p-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex min-w-0 items-start gap-2">
                                    <x-gpa.icon name="banknote" class="mt-0.5 h-4 w-4 shrink-0 text-accent-deep" />
                                    <p class="gpa-meta-lg font-bold leading-[0.875rem] tracking-[0.88px] text-ink">
                                        Pencocokan Bukti Transfer Manual
                                    </p>
                                </div>
                                <span class="shrink-0 gpa-note font-bold text-success-deep">{{ $pendingPaymentsCount ?? 2 }} Menunggu Verifikasi</span>
                            </div>
                            <p class="mt-1 pl-6 text-xs leading-[1.375rem] text-ink-body">
                                Unggahan bukti transfer klien reguler memerlukan konfirmasi pencocokan dengan mutasi rekening giro BCA GPA.
                            </p>
                            <button type="button" @click="dismiss(1)"
                                class="mt-1 pl-6 gpa-note text-ink-quiet underline underline-offset-2 hover:text-ink">
                                Arsipkan
                            </button>
                        </li>

                        <li x-cloak x-show="dismissed.includes(0) && dismissed.includes(1)"
                            class="rounded-xl border border-dashed border-line-board bg-surface-shell px-3 py-6 text-center">
                            <p class="text-xs font-semibold text-ink">Tidak ada peringatan aktif.</p>
                            <p class="mt-1 gpa-note text-ink-quiet">Seluruh notifikasi telah diarsipkan.</p>
                        </li>
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                        <p class="flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
                            <span class="gpa-note text-ink-body">Sistem pemantauan aktif 24/7</span>
                        </p>
                        <a href="{{ route('sekretaris.laporan') }}"
                            class="gpa-note text-ink underline underline-offset-2 hover:text-success-deep">
                            Arsip Notifikasi &rarr;
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
