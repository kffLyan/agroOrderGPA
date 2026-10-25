@extends('layouts.direktur')

@section('title', 'Dashboard Direktur')

@section('content')
    <div class="mx-auto flex max-w-[1280px] flex-col gap-6">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <p class="gpa-meta text-xs font-bold uppercase tracking-wider text-accent-deep">Dasbor Eksekutif Direktur</p>
                <h1 class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Ringkasan Kinerja Eksekutif Direktur
                </h1>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <a href="{{ route('direktur.reports.export') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface px-4 py-2.5 text-ink shadow-sub outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                    <span class="text-center text-xs font-semibold leading-4 text-ink">Unduh Buku Besar (CSV)</span>
                </a>
                <a href="{{ route('direktur.reports.download') }}" target="_blank"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-white shadow-sub outline outline-1 -outline-offset-1 outline-success transition-colors hover:bg-brand-hover">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 shrink-0" />
                    <span class="text-center text-xs font-semibold leading-4 text-white">Cetak Laporan Eksekutif (PDF)</span>
                </a>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            {{-- Card 1: Revenue --}}
            <article class="relative flex h-full flex-col justify-between overflow-hidden rounded-2xl p-6 bg-surface shadow-card">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-success-deep">OMZET Q4 TAHUN INI</h2>
                        <x-gpa.icon name="banknote" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure text-[30px] leading-9 text-ink">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">+14.2% MoM</span>
                        <span class="gpa-meta-lg text-ink-body">Target: Rp {{ number_format($targetRevenue, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="pt-4">
                    <div class="flex flex-col gap-1 border-t border-surface-track pt-3">
                        <div class="flex items-start justify-between gap-2">
                            <span class="gpa-note text-ink-body">Pencapaian target</span>
                            <span class="gpa-note font-bold text-ink">{{ $revenuePercent }}% ({{ $paidRatio }}% Terbayar)</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                            <div class="h-full rounded-full bg-success-deep" style="width: {{ min(100, $revenuePercent) }}%"></div>
                        </div>
                    </div>
                </div>
            </article>

            {{-- Card 2: Volume --}}
            <article class="relative flex h-full flex-col justify-between overflow-hidden rounded-2xl p-6 bg-surface shadow-card">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-success-deep">JUMLAH KOMODITAS</h2>
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure text-[30px] leading-9 text-ink">
                        {{ number_format($totalTonnageKg, 0, ',', '.') }}
                        <span class="gpa-meta-lg font-normal text-success-deep">kg</span>
                    </p>
                    <p class="text-xs font-bold leading-4">
                        <span class="text-ink-body">Binaan: </span>
                        <span class="text-ink font-mono">{{ $binaanPercent }}%</span>
                        <span class="text-ink-body"> | Buffer: </span>
                        <span class="text-ink font-mono">{{ $bufferPercent }}%</span>
                    </p>
                </div>
                <div class="pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-bold text-success-deep">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                            Timbangan sudah ditera
                        </span>
                        <span class="gpa-note font-semibold text-ink-body">UU No. 2/1981</span>
                    </div>
                </div>
            </article>

            {{-- Card 3: Receivables --}}
            <article class="relative flex h-full flex-col justify-between overflow-hidden rounded-2xl p-6 bg-surface shadow-card">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-warning-caution">TAGIHAN PELANGGAN</h2>
                        <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-warning-caution" />
                    </div>
                    <p class="gpa-figure text-[30px] leading-9 text-ink">
                        Rp {{ number_format($totalReceivables, 0, ',', '.') }}
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-warning-cream text-warning-caution outline outline-1 -outline-offset-1 outline-warning-caution">
                            {{ $nearDueCount }} pelanggan jatuh tempo &le;7 hari
                        </span>
                        <span class="gpa-meta-lg text-ink-body">{{ $activeContractsCount }} Kontrak Aktif</span>
                    </div>
                </div>
                <div class="pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-bold text-warning-caution">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                            Sudah tertagih: {{ $paidRatio }}%
                        </span>
                        <span class="gpa-note font-semibold text-ink-body">Jatuh tempo dekat: Rp {{ number_format($nearDueAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </article>

            {{-- Card 4: SLA --}}
            <article class="relative flex h-full flex-col justify-between overflow-hidden rounded-2xl p-6 bg-surface shadow-card">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-success-deep">TINGKAT KETEPATAN (SLA)</h2>
                        <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure text-[30px] leading-9 text-ink">
                        {{ $slaPercent }}%
                        <span class="gpa-meta-lg font-normal text-success-deep">Tuntas</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">
                            SLA TERPENUHI
                        </span>
                        <span class="gpa-meta-lg text-ink-body">Zero-Defect Armada &bull; Retur Lapangan {{ $returnCount > 0 ? '0.8%' : '0.0%' }}</span>
                    </div>
                </div>
                <div class="pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="inline-flex items-center gap-1 gpa-note font-bold text-success-deep">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                            MUTU PILIHAN A/B+
                        </span>
                        <span class="gpa-note font-semibold text-ink-body">Suhu pengiriman terjaga</span>
                    </div>
                </div>
            </article>

            {{-- Card 5: Authorization (Highlight) --}}
            <article class="relative flex h-full flex-col justify-between overflow-hidden rounded-2xl p-6 bg-surface-shell shadow-sub outline outline-2 -outline-offset-2 outline-brand">
                <span class="absolute right-0 top-0 rounded-b-lg bg-accent-deep px-3 py-0.5 gpa-micro-bold text-ink">WAJIB</span>

                <div class="flex flex-col gap-2 pt-4">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold text-danger">PENGAJUAN PERSETUJUAN</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-danger" />
                    </div>
                    <p class="gpa-figure text-[30px] leading-9 text-ink">
                        {{ $pendingCount }} Kontrak
                    </p>
                    <p class="text-xs font-bold leading-4">
                        <span class="text-ink-body">Total Nilai: </span>
                        <span class="text-ink font-mono">Rp {{ number_format($pendingValue, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="pt-4">
                    <div class="flex flex-col gap-2 border-t border-line-board/60 pt-3">
                        <a href="{{ route('direktur.contracts.approval') }}"
                            class="inline-flex items-center justify-center gap-1 rounded bg-brand px-3 py-1.5 gpa-meta font-bold text-accent transition-colors hover:bg-brand-hover">
                            Tinjau Pengajuan ({{ $pendingCount }})
                            <x-gpa.icon name="arrow-right" class="h-2.5 w-2.5 shrink-0" />
                        </a>
                    </div>
                </div>
            </article>
        </section>

        {{-- Tren omzet mingguan dan pembagian komoditas --}}
        <section class="grid gap-4 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
            {{-- Weekly revenue & volume --}}
            <div class="gpa-panel flex flex-col justify-between gap-6 p-6">
                <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">Omzet dan Target Mingguan</h2>
                    </div>
                    <ul class="flex flex-wrap items-center gap-3">
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2 shrink-0 rounded bg-brand" aria-hidden="true"></span>
                            <span class="gpa-note text-ink">Omzet Selesai</span>
                        </li>
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2 shrink-0 rounded bg-accent-deep" aria-hidden="true"></span>
                            <span class="gpa-note text-ink">Minggu Berjalan</span>
                        </li>
                        <li class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2 shrink-0 rounded bg-surface-disabled border border-line-board" aria-hidden="true"></span>
                            <span class="gpa-note text-ink">Kapasitas Target</span>
                        </li>
                    </ul>
                </header>

                <div class="flex flex-col gap-4">
                    @forelse ($weeklyTrends as $wt)
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="gpa-meta-lg font-bold text-ink">
                                    {{ $wt['week'] }}{{ $wt['is_running'] ? ' (berjalan)' : '' }}
                                    <span class="font-normal text-ink-muted">&bull; Panen {{ number_format($wt['harvest_ton'], 1, ',', '.') }} ton &bull; Target Rp {{ number_format($wt['target'] / 1000000, 0) }} jt</span>
                                </p>
                                <div class="flex items-center gap-2">
                                    <span class="gpa-meta font-bold text-ink">
                                        Omzet: Rp {{ number_format($wt['revenue'], 0, ',', '.') }}
                                    </span>
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold font-mono {{ $wt['pct'] >= 100 ? 'bg-success-soft text-success-deep' : ($wt['pct'] > 0 ? 'bg-accent/40 text-brand' : 'bg-surface-disabled text-ink-body') }}">
                                        {{ $wt['pct'] }}%
                                    </span>
                                </div>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                <div class="h-full rounded-full transition-all duration-300 {{ $wt['is_running'] ? 'bg-accent-deep' : 'bg-brand' }}"
                                    style="width: {{ min(100, $wt['pct']) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-ink-body">Belum ada riwayat pesanan mingguan.</p>
                    @endforelse
                </div>

                <footer>
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-board pt-4">
                        <p class="gpa-note font-bold text-ink">SUMBER DATA: DATABASE RIIL KOPERASI GPA</p>
                    </div>
                </footer>
            </div>

            {{-- 5 Komoditas Utama & Saluran Penjualan --}}
            <div class="gpa-panel flex flex-col justify-between gap-4 p-6">
                <header class="border-b border-line-board/60 pb-3">
                    <h2 class="gpa-section-title text-ink">5 Komoditas Utama &amp; Saluran Penjualan</h2>
                </header>

                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3">
                        @forelse ($topProductsData as $idx => $tp)
                            <div class="flex flex-col gap-1">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="gpa-meta font-bold text-ink">{{ $idx + 1 }}. {{ $tp['name'] }}</p>
                                    <p class="gpa-meta font-semibold text-success-deep">{{ number_format($tp['ton'], 2, ',', '.') }} ton ({{ $tp['share'] }}%)</p>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full {{ $tp['color'] }}" style="width: {{ max(2, min(100, $tp['share'])) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-ink-body">Belum ada data komoditas.</p>
                        @endforelse
                    </div>

                    <div class="flex flex-col gap-2 border-t border-line-board pt-6">
                        <p class="gpa-micro-bold text-ink">SALURAN PENJUALAN</p>
                        <div class="flex items-start gap-2">
                            <div class="flex flex-1 flex-col items-center gap-0.5 rounded bg-surface-shell p-2 text-center outline outline-1 -outline-offset-1 outline-line-board">
                                <p class="text-[10px] leading-[15px] text-ink-body gpa-meta">Horeca &amp; Inflight</p>
                                <p class="pt-0.5 text-[14px] font-bold leading-5 text-ink gpa-meta">{{ $channelData['horeca']['share'] ?? 0 }}%</p>
                                <p class="text-[9px] leading-[13.5px] text-success-deep gpa-meta">{{ number_format($channelData['horeca']['ton'] ?? 0, 2, ',', '.') }} ton</p>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-0.5 rounded bg-surface-shell p-2 text-center outline outline-1 -outline-offset-1 outline-line-board">
                                <p class="text-[10px] leading-[15px] text-ink-body gpa-meta">Ritel Modern</p>
                                <p class="pt-0.5 text-[14px] font-bold leading-5 text-ink gpa-meta">{{ $channelData['retail']['share'] ?? 0 }}%</p>
                                <p class="text-[9px] leading-[13.5px] text-success-deep gpa-meta">{{ number_format($channelData['retail']['ton'] ?? 0, 2, ',', '.') }} ton</p>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-0.5 rounded bg-surface-shell p-2 text-center outline outline-1 -outline-offset-1 outline-line-board">
                                <p class="text-[10px] leading-[15px] text-ink-body gpa-meta">Reguler WA</p>
                                <p class="pt-0.5 text-[14px] font-bold leading-5 text-ink gpa-meta">{{ $channelData['manual']['share'] ?? 0 }}%</p>
                                <p class="text-[9px] leading-[13.5px] text-success-deep gpa-meta">{{ number_format($channelData['manual']['ton'] ?? 0, 2, ',', '.') }} ton</p>
                            </div>
                        </div>
                    </div>
                </div>

                <footer>
                    <div class="flex flex-wrap items-start justify-between gap-2 border-t border-line-board pt-4"></div>
                </footer>
            </div>
        </section>

        {{-- Pemantauan tagihan --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Pemantauan Tagihan Pelanggan</h2>
                </div>
                <p class="gpa-meta font-bold text-success-deep">
                    TOTAL TAGIHAN BELUM DIBAYAR: Rp {{ number_format($totalReceivables, 0, ',', '.') }}
                </p>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {{-- Bucket 1 --}}
                <article class="flex flex-col gap-1 rounded-xl p-3 outline outline-1 -outline-offset-1 bg-surface-shell text-success-deep outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="gpa-meta font-bold">BELUM JATUH TEMPO (0 - 15 HARI)</h3>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 opacity-70" />
                    </div>
                    <p class="text-lg font-bold leading-6 font-inter text-ink">
                        Rp {{ number_format($bucket0_15, 0, ',', '.') }}
                    </p>
                    <p class="gpa-meta-lg leading-4 text-ink-body">Pembayaran lancar sesuai tempo</p>
                    <p class="pt-1 text-[10px] font-semibold leading-[15px] gpa-meta text-success-deep">STATUS: LANCAR</p>
                </article>

                {{-- Bucket 2 --}}
                <article class="flex flex-col gap-1 rounded-xl p-3 outline outline-1 -outline-offset-1 bg-surface-shell text-ink outline-line-board">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="gpa-meta font-bold">JATUH TEMPO (16 - 30 HARI)</h3>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 opacity-70" />
                    </div>
                    <p class="text-lg font-bold leading-6 font-inter text-ink">
                        Rp {{ number_format($bucket16_30, 0, ',', '.') }}
                    </p>
                    <p class="gpa-meta-lg leading-4 text-ink-body">Pengingat tempo terkirim otomatis</p>
                    <p class="pt-1 text-[10px] font-semibold leading-[15px] gpa-meta text-ink">STATUS: DIINGATKAN</p>
                </article>

                {{-- Bucket 3 --}}
                <article class="flex flex-col gap-1 rounded-xl p-3 outline outline-1 -outline-offset-1 bg-warning-cream text-warning-caution outline-warning-caution">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="gpa-meta font-bold">LEWAT JATUH TEMPO (1 - 14 HARI)</h3>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 opacity-70" />
                    </div>
                    <p class="text-lg font-bold leading-6 font-inter text-ink">
                        Rp {{ number_format($bucket31_45, 0, ',', '.') }}
                    </p>
                    <p class="gpa-meta-lg leading-4 text-ink-body">Surat teguran pertama diterbitkan</p>
                    <p class="pt-1 text-[10px] font-semibold leading-[15px] gpa-meta text-warning-caution">PERLU TINDAKAN</p>
                </article>

                {{-- Bucket 4 --}}
                <article class="flex flex-col gap-1 rounded-xl p-3 outline outline-1 -outline-offset-1 bg-danger-soft/40 text-danger outline-danger">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="gpa-meta font-bold">TERLAMBAT LEBIH DARI 15 HARI</h3>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 opacity-70" />
                    </div>
                    <p class="text-lg font-bold leading-6 font-inter text-ink">
                        Rp {{ number_format($bucketOver45, 0, ',', '.') }}
                    </p>
                    <p class="gpa-meta-lg leading-4 text-ink-body">{{ $worstOverdueInvoice ? '1 Klien (' . ($worstOverdueInvoice->user?->name ?: 'Klien') . ')' : '0 Klien' }}</p>
                    <p class="pt-1 text-[10px] font-semibold leading-[15px] gpa-meta text-danger">PESANAN BARU: DITAHAN OTOMATIS</p>
                </article>
            </div>

            @if ($worstOverdueInvoice)
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-surface-shell p-3 outline outline-1 -outline-offset-1 outline-line-board">
                    <p class="flex items-start gap-2">
                        <x-gpa.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0 text-warning-caution" />
                        <span class="text-xs leading-4 text-ink">
                            {{ $worstOverdueInvoice->user?->company_name ?: $worstOverdueInvoice->user?->name }} memiliki faktur terlambat ({{ $worstOverdueInvoice->invoice_number }}) sebesar Rp {{ number_format($worstOverdueInvoice->grand_total, 0, ',', '.') }}. Pengiriman baru <strong class="font-bold">DITAHAN OTOMATIS</strong> sampai tagihan diselesaikan.
                        </span>
                    </p>
                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" @click="openAuditNote()"
                            class="rounded bg-surface-track px-2.5 py-1 text-center text-[12px] font-bold leading-4 text-ink gpa-meta outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-disabled">
                            Lihat catatan
                        </button>
                        <button type="button" @click="dispensation()"
                            class="rounded bg-ink px-2.5 py-1 text-center text-[12px] font-bold leading-4 text-white gpa-meta transition-opacity hover:opacity-90">
                            Ajukan pengecualian
                        </button>
                    </div>
                </div>
            @endif
        </section>

        {{-- Ringkasan Pesanan & Kontrak Terbaru --}}
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl bg-surface p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-line-board/60 pb-3">
                    <h2 class="gpa-section-title text-ink">Pesanan B2B Terkini</h2>
                    <span class="text-xs text-ink-quiet">Pengawasan Transaksi</span>
                </div>
                <div class="divide-y divide-line-board/40">
                    @forelse($recentOrders ?? [] as $order)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-mono font-bold text-ink">{{ $order->order_number }}</span>
                                <span class="text-ink-quiet block text-[11px]">{{ $order->user?->company_name ?? $order->user?->name ?? 'Klien' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-ink">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                <span class="block text-[10px] text-accent-deep font-bold">{{ $order->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-ink-quiet">Belum ada pesanan terbaru.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-surface p-6 shadow-card space-y-4">
                <div class="flex items-center justify-between border-b border-line-board/60 pb-3">
                    <h2 class="gpa-section-title text-ink">Kontrak Kemitraan Terkini</h2>
                    <span class="text-xs text-ink-quiet">Perjanjian PKS B2B</span>
                </div>
                <div class="divide-y divide-line-board/40">
                    @forelse($recentContracts ?? [] as $contract)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-mono font-bold text-ink">{{ $contract->contract_number }}</span>
                                <span class="text-ink-quiet block text-[11px]">{{ $contract->user?->company_name ?? $contract->user?->name ?? 'Klien' }} &bull; {{ $contract->product?->name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-ink">Rp {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }}/kg</span>
                                <span class="block text-[10px] font-bold {{ $contract->status === 'ACTIVE' ? 'text-success-deep' : 'text-warning-caution' }}">{{ $contract->status }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-ink-quiet">Belum ada kontrak tercatat.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
