@extends('layouts.klien')

@section('title', 'Dashboard Ringkasan & Operasional Klien B2B')

@php
    $user = auth()->user();
    $contractNumber = $contractNumber ?? ($contract ? $contract->contract_number : ($isContractClient ? ('CTR-B2B-' . str_pad($user->id, 4, '0', STR_PAD_LEFT)) : 'REGULER-HARIAN'));
    $dockName = $dockName ?? ('DOCK UTAMA — ' . ($user->company_name ?: ($user->name ?: 'KLIEN GPA')));
    $creditBarWidth = $creditBarWidth ?? (max(0, min(100, $creditPercent ?? 0)) . '%');
    $ordersTable = $ordersTable ?? collect();
    $reorderData = $reorderData ?? [];
@endphp

@section('content')
    <div x-data="clientDashboard(@js($reorderData))" class="flex flex-col gap-5">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Kop halaman  ringkasan kontrak --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="gpa-card flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:p-6">
            <div class="min-w-0 flex flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="gpa-section-title text-ink">
                        {{ $isContractClient ? 'Dashboard Ringkasan & Operasional Klien B2B' : 'Dashboard Ringkasan & Operasional Klien Reguler' }}
                    </h1>
                    <span
                        class="rounded-full bg-accent px-2.5 py-0.5 gpa-micro-bold text-success-ink">
                        {{ $isContractClient ? 'Kontrak Aktif' : 'Klien Reguler' }}
                    </span>
                </div>
                <dl class="flex flex-col gap-1 gpa-meta-lg text-ink-body">
                    <div class="flex flex-wrap items-center gap-x-2">
                        <dt class="text-ink-body">ID Kontrak:</dt>
                        <dd class="font-medium text-ink">{{ $contractNumber }}</dd>
                        <span class="text-ink-body" aria-hidden="true">&bull;</span>
                        <dt class="text-ink-body">Gudang Dock Utama:</dt>
                        <dd class="font-medium uppercase text-ink">{{ $dockName }}</dd>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5 text-ink-body">
                        <span class="inline-block h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <dt>Live Sync:</dt>
                        <dd class="font-medium text-ink-body" x-text="syncedAt">{{ $syncedAt->format('H:i:s') }} WIB</dd>
                    </div>
                </dl>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('klien.documents') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-surface-pill px-3.5 py-2 transition-colors hover:bg-surface-disabled">
                    <x-gpa.icon name="download" class="h-3 w-3 text-ink" />
                    <span class="gpa-meta-lg text-ink">Unduh Statement Bulanan</span>
                </a>
                <a href="{{ route('klien.catalog') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-ink px-4 py-2 shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                    <span class="gpa-meta-lg font-bold text-white">Buat Pesanan Baru / PO</span>
                </a>
            </div>
        </section>

        @if (!empty($rejectedPayment))
            {{-- Banner Notifikasi Bukti Pembayaran Ditolak Sekretariat --}}
            <section class="rounded-2xl border-2 border-danger/60 bg-danger-soft/30 p-4 sm:p-5 shadow-card transition-all">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-danger text-white shadow-sub">
                            <x-gpa.icon name="alert-triangle" class="h-6 w-6 text-white" />
                        </span>
                        <div class="flex flex-col gap-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded bg-danger px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider text-white">
                                    Bukti Pembayaran Ditolak Verifikator
                                </span>
                                <span class="text-xs font-semibold text-danger">
                                    Ref: {{ $rejectedPayment->payment_reference ?: ('PAY-' . $rejectedPayment->id) }}
                                </span>
                                <span class="text-xs text-ink-quiet">
                                    &bull; Tagihan: <strong class="text-ink">{{ $rejectedPayment->invoice?->invoice_number ?: ($rejectedPayment->order?->order_number ?: 'Faktur Klien') }}</strong>
                                </span>
                            </div>
                            <h2 class="font-sans text-base font-extrabold text-ink sm:text-lg">
                                Bukti Pembayaran Belum Dapat Disahkan oleh Sekretariat GPA
                            </h2>
                            <p class="text-xs text-ink-body leading-relaxed max-w-2xl">
                                Alasan penolakan: <strong class="text-danger">"{{ $rejectedPayment->verification_note ?: 'Bukti transfer tidak valid atau dana belum masuk rekening koran.' }}"</strong>. Silakan konfirmasi ulang transfer Anda dan unggah bukti baru agar pesanan segera diproses.
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a href="{{ route('klien.payment-proof', ['tagihan' => $rejectedPayment->invoice?->invoice_number, 'order_id' => $rejectedPayment->order_id]) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-danger px-4 py-2.5 font-sans text-xs font-bold text-white shadow-lg transition-transform hover:scale-105 hover:bg-danger/90">
                            <x-gpa.icon name="upload" class="h-4 w-4 text-white" />
                            <span>Unggah Ulang Bukti Bayar</span>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        @if (!$isContractClient)
            {{-- ---------------------------------------------------------------- --}}
            {{-- Banner Notifikasi Khusus Klien Reguler: Ajukan Kontrak B2B / Status Pengajuan --}}
            {{-- ---------------------------------------------------------------- --}}
            @if ($pendingContract)
                {{-- Banner Status Pengajuan Sedang Ditinjau --}}
                <section class="relative overflow-hidden rounded-2xl border border-warning/40 bg-gradient-to-r from-warning/15 via-warning/5 to-surface p-5 lg:p-6 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning/20 text-warning-deep">
                                <x-gpa.icon name="clock" class="h-5 w-5 animate-pulse" />
                            </span>
                            <div class="flex flex-col gap-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-warning/30 px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-warning-deep">
                                        Menunggu Pengesahan Direktur
                                    </span>
                                    <span class="font-mono text-xs font-semibold text-ink">No. PKS: {{ $pendingContract->contract_number }}</span>
                                </div>
                                <h2 class="text-base font-bold text-ink">Pengajuan Kontrak Kemitraan B2B Sedang Ditinjau Direktur Utama</h2>
                                <p class="max-w-3xl text-xs leading-relaxed text-ink-body">
                                    Proposal kemitraan untuk komoditas <strong>{{ $pendingContract->product?->name }}</strong> (Komitmen <strong>{{ number_format($pendingContract->committed_volume_per_cycle, 0, ',', '.') }} kg / siklus</strong>, Termin Pembayaran <strong>TOP {{ $pendingContract->top_days }} Hari</strong>) telah terkirim pada {{ $pendingContract->created_at->format('d M Y') }} dan sedang dalam tahap evaluasi Direktur Utama GPA. Setelah disetujui, akun Anda otomatis ditingkatkan menjadi Klien Kontrak B2B.
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <a href="https://wa.me/6281112233445?text=Halo%20Direksi%20GPA,%20saya%20telah%20mengajukan%20kontrak%20B2B%20nomor%20{{ $pendingContract->contract_number }}" target="_blank"
                                class="inline-flex items-center gap-2 rounded-xl bg-surface-pill px-4 py-2.5 font-sans text-xs font-bold text-ink outline outline-1 outline-line-hair transition-all hover:bg-surface-disabled">
                                <x-gpa.icon name="message-square" class="h-4 w-4 text-success-deep" />
                                <span>Konfirmasi via WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </section>
            @else
                {{-- Banner Penawaran Kemitraan Kontrak B2B --}}
                <section class="relative overflow-hidden rounded-2xl border border-brand/30 bg-gradient-to-r from-brand-deep via-brand/90 to-[#1b4306] p-5 lg:p-6 text-white shadow-card">
                    <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-accent/10 blur-2xl"></div>
                    <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent outline outline-1 outline-white/20">
                                <x-gpa.icon name="file-text" class="h-6 w-6" />
                            </span>
                            <div class="flex flex-col gap-1.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-accent/20 px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase tracking-wider text-accent outline outline-1 outline-accent/30">
                                        Peluang Kemitraan Kontrak B2B
                                    </span>
                                    <span class="font-mono text-xs text-white/80">Status Akun: Klien Reguler (Spot Market)</span>
                                </div>
                                <h2 class="text-base font-bold text-white lg:text-lg">Tingkatkan Akun ke Kemitraan B2B Kontrak Resmi</h2>
                                <p class="max-w-3xl text-xs leading-relaxed text-white/90">
                                    Dapatkan fasilitas kemitraan prioritas: <strong>Harga Komoditas Terkunci (Bebas Fluktuasi)</strong>, <strong>Term of Payment (Tempo 14–30 Hari)</strong>, serta jaminan alokasi pasokan harian langsung dari kebun binaan GPA. Pengajuan akan ditinjau dan disahkan langsung oleh <strong>Direktur Utama GPA</strong>.
                                </p>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <button type="button" @click="showContractModal = true"
                                class="inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2.5 font-sans text-xs font-bold text-ink shadow-lg transition-transform hover:scale-[1.02] active:scale-[0.98]">
                                <x-gpa.icon name="plus" class="h-4 w-4" />
                                <span>Ajukan Kontrak Kemitraan B2B</span>
                            </button>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Empat kartu ringkasan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:min-h-[17.125rem]">
            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Plafon Kredit &amp; Tempo (Top 30D)</h2>
                        <x-gpa.icon name="gauge" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-ink">Rp {{ number_format($availableCredit, 0, ',', '.') }}</p>
                    <p class="gpa-body text-ink-body">
                        Sisa kuota dari total
                        <span class="font-medium text-ink">Rp {{ number_format($creditLimit, 0, ',', '.') }}</span>
                    </p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-start justify-between gap-2">
                            <p class="gpa-micro-bold text-success-deep">Status: {{ $availableCredit > ($creditLimit * 0.3) ? 'Aman (TOP 30D)' : 'Perlu Pelunasan' }}</p>
                            <p class="gpa-micro text-ink-body">{{ number_format($creditPercent, 1, ',', '') }}% Tersedia</p>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-surface-pill" role="img"
                            aria-label="Kuota tersedia {{ number_format($creditPercent, 1, ',', '') }} persen">
                            <div class="h-full rounded-full bg-success-deep"
                                style="width: {{ $creditBarWidth }}"></div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Pesanan In-Flight Pipeline</h2>
                        <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-0.5 text-ink">{{ $pipelineCount }} Pesanan Aktif</p>
                    <p class="gpa-meta-lg font-bold text-success-deep">Valuasi: Rp {{ number_format($pipelineValue, 0, ',', '.') }}</p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1">
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Menunggu Verifikasi:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">{{ ($stagesCount['verifikasi'] ?? 0) }} PO</dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Alokasi Panen:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">{{ ($stagesCount['panen'] ?? 0) }} PO</dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Sortir &amp; Kemas:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">{{ ($stagesCount['kemas'] ?? 0) }} PO</dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Pengiriman Armada:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">{{ ($stagesCount['kirim'] ?? 0) }} Armada</dd>
                        </div>
                    </dl>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Serapan Kuota Panen (Bulan Ini)</h2>
                        <x-gpa.icon name="leaf" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="flex flex-wrap items-baseline gap-1 text-ink">
                        <span class="gpa-figure">{{ number_format($totalKgHarvest, 0, ',', '.') }} kg</span>
                        @if ($targetKg > 0)
                            <span class="font-inter text-sm text-ink-body">/ {{ number_format($targetKg, 0, ',', '.') }} kg</span>
                        @endif
                    </p>
                    <p class="gpa-body font-bold text-success-deep">
                        {{ number_format($harvestPercent, 1, ',', '') }}% kuota alokasi bulanan terpenuhi
                    </p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <div class="flex flex-col gap-1">
                        @if (!empty($commodityBreakdown))
                            <div class="flex flex-wrap gap-x-6">
                                @foreach (array_slice($commodityBreakdown, 0, 2, true) as $cName => $cWeight)
                                    <p class="gpa-micro text-ink-body">
                                        {{ $cName }}: {{ number_format($cWeight, 0, ',', '.') }} kg
                                    </p>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap gap-x-6">
                                @foreach (array_slice($commodityBreakdown, 2, null, true) as $cName => $cWeight)
                                    <p class="gpa-micro text-ink-body">
                                        {{ $cName }}: {{ number_format($cWeight, 0, ',', '.') }} kg
                                    </p>
                                @endforeach
                            </div>
                        @else
                            <p class="gpa-micro text-ink-body italic">Belum ada realisasi serapan kuota panen bulan ini.</p>
                        @endif
                        <div class="mt-0.5 flex items-start justify-between gap-2 border-t border-line-soft/40 pt-1.5">
                            <p class="gpa-micro font-semibold text-ink">Deviasi Timbang Riil:</p>
                            <p class="gpa-micro font-bold text-success-deep">
                                0,4% (&lt; 1,0% Toleransi Aman)
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="gpa-card flex flex-col justify-between gap-4 p-5 xl:p-6">
                <div class="flex flex-col gap-1">
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="gpa-micro text-ink-body">Ketepatan Waktu &amp; Mutu Pasokan Segar</h2>
                        <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                    </div>
                    <p class="gpa-figure pt-1 text-success-deep">100%</p>
                    <p class="gpa-body text-ink-body">Pengiriman Tepat Waktu &amp; Lengkap</p>
                </div>
                <div class="border-t border-line-soft/60 pt-3">
                    <dl class="flex flex-col gap-1">
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Insiden Kritis Cold-Chain:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                                0 Insiden
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">Durasi Bongkar Dock:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink">
                                28 Menit (Avg)
                            </dd>
                        </div>
                        <div class="flex items-start justify-between gap-2">
                            <dt class="gpa-micro text-ink-body">QC Reject Rate Lapangan:</dt>
                            <dd class="shrink-0 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-success-deep">
                                0,0% (Target &lt;&lt; 1%)
                            </dd>
                        </div>
                    </dl>
                </div>
            </article>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Pemberitahuan penagihan (hanya tampil jika ada tagihan aktif) --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($latestInvoice)
            <section id="dokumen"
                class="flex flex-col gap-4 rounded-r-xl border-l-4 border-warning bg-warning-wash p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="mt-0.5 h-[18px] w-1 shrink-0 bg-warning" aria-hidden="true"></span>
                    <div class="flex min-w-0 flex-col gap-0.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-sm font-bold leading-5 text-warning-deep">
                                Tagihan {{ $latestInvoice->invoice_number }} Menunggu Pembayaran
                            </h2>
                            <span class="rounded bg-warning px-2 py-0.5 gpa-micro-bold text-warning-soft">
                                {{ $latestInvoice->due_date ? 'Jatuh Tempo: ' . $latestInvoice->due_date->format('d M Y') : 'Jatuh Tempo: TOP 30 Hari' }}
                            </span>
                        </div>
                        <p class="gpa-body text-ink-body">
                            Total kewajiban tempo: Rp {{ number_format((float) $latestInvoice->grand_total, 0, ',', '.') }} dengan netto timbangan sah.
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('klien.documents') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-3 py-1.5 transition-colors hover:bg-surface-disabled hover:text-ink">
                        <x-gpa.icon name="invoice" class="h-2.5 w-2.5" />
                        <span class="gpa-meta-lg">Unduh Faktur PDF</span>
                    </a>
                    <a href="{{ route('klien.payment-proof') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3.5 py-1.5 shadow-sub transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="upload" class="h-3 w-3.5 text-white" />
                        <span class="gpa-meta-lg font-bold text-white">Unggah Bukti Transfer</span>
                    </a>
                </div>
            </section>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Live tracking --}}
        {{-- ---------------------------------------------------------------- --}}
        @if ($inTransitOrder)
            <section class="gpa-card flex flex-col gap-6 p-5 lg:p-6">
                <div class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <h2 class="gpa-section-title text-ink">
                                Live Pelacakan Armada Pengiriman: {{ $inTransitOrder->surat_jalan_number ?: 'SJ-' . str_pad($inTransitOrder->id, 5, '0', STR_PAD_LEFT) }}
                            </h2>
                        </div>
                        <p class="gpa-body text-ink-body">
                            Chiller Truck {{ $inTransitOrder->vehicle_plate_number ?: 'GPA Cold-Chain' }} bergerak menuju fasilitas cold-storage Anda.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="inline-flex items-center gap-2 rounded-lg bg-brand px-3 py-1.5 ring-1 ring-inset ring-accent/30">
                            <x-gpa.icon name="thermometer" class="h-3.5 w-3.5 text-accent" />
                            <div class="flex flex-col">
                                <span class="gpa-meta-lg text-accent">Telemetri Chiller:</span>
                                <span class="flex items-baseline gap-1.5">
                                    <span class="text-xs font-bold leading-4 text-accent">+4.2°C</span>
                                    <span class="gpa-meta-lg font-bold text-accent">[OPTIMAL]</span>
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col rounded-lg bg-surface-pill px-3 py-1.5">
                            <span class="gpa-meta-lg text-ink">Supir:</span>
                            <span class="text-xs font-bold leading-4 text-ink">{{ $inTransitOrder->driver?->name ?: 'Supir Logistik GPA' }}</span>
                        </div>
                    </div>
                </div>

                <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-white">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-ink" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                            Fase 1 &bull; Selesai
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Verifikasi PO</p>
                        <p class="gpa-body text-ink-body">Disetujui oleh Sekretariat GPA &amp; alokasi kuota panen dikunci.</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-white">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-ink" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                            Fase 2 &bull; Selesai
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Sortir &amp; Grading</p>
                        <p class="gpa-body text-ink-body">QC Grade Super di Packing House Lembang dinyatakan lolos 100%.</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-white">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-ink" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-success-deep">
                            Fase 3 &bull; Selesai
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Penimbangan Tera</p>
                        <p class="gpa-body text-ink-body">Netto sah ditimbang digital sebelum armada diberangkatkan.</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-accent-edge bg-surface-shell p-3">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-accent text-ink ring-4 ring-brand/20">
                                <x-gpa.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            <span class="h-0.5 flex-1 bg-success-deep" aria-hidden="true"></span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-ink">
                            <span class="h-1.5 w-1.5 rounded-full bg-ink" aria-hidden="true"></span>
                            Fase 4 &bull; Dalam Perjalanan
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink">Armada On Road</p>
                        <p class="gpa-body font-semibold text-ink">Suhu muatan terpantau stabil, armada menuju receiving dock.</p>
                        <p class="pt-1 text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-success-deep">Sedang Berlangsung</p>
                    </li>

                    <li class="flex flex-col gap-1 rounded-xl border border-transparent p-3 opacity-60">
                        <div class="flex items-center gap-2 pb-2">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-surface-disabled text-ink-quiet ring-0">
                                <span class="gpa-meta-lg">5</span>
                            </span>
                        </div>
                        <p class="flex items-center gap-1.5 gpa-micro-bold text-ink-body">
                            Fase 5 &bull; Menunggu Tiba
                        </p>
                        <p class="pt-0.5 text-sm font-bold leading-5 text-ink-quiet">Bongkar Muat Dock Klien</p>
                        <p class="gpa-body text-ink-body">Pemeriksaan timbang ulang serah-terima dan tanda tangan digital BAST.</p>
                    </li>
                </ol>
            </section>
        @else
            <section class="gpa-card flex flex-col gap-4 p-5 lg:p-6">
                <div class="flex items-center justify-between border-b border-line-soft pb-3">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-ink-quiet" aria-hidden="true"></span>
                        <h2 class="gpa-section-title text-ink">Live Pelacakan Armada Pengiriman</h2>
                    </div>
                    <span class="text-xs text-ink-quiet font-medium">Status Armada: Standby</span>
                </div>
                <div class="py-6 text-center text-sm text-ink-body">
                    <x-gpa.icon name="truck" class="mx-auto h-8 w-8 text-ink-quiet mb-2" />
                    <p class="font-medium text-ink">Tidak ada armada pengiriman yang sedang aktif menuju dock Anda saat ini.</p>
                    <p class="text-xs text-ink-body mt-1">Status pelacakan real-time akan aktif otomatis ketika armada logistik diberangkatkan dari Sentra GPA.</p>
                </div>
            </section>
        @endif

        {{-- ---------------------------------------------------------------- --}}
        {{-- Tabel pesanan berjalan --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="pesanan-berjalan" class="gpa-card overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-line-soft p-5 lg:flex-row lg:items-center lg:justify-between lg:p-6">
                <div class="flex min-w-0 flex-col gap-1">
                    <h2 class="gpa-section-title text-ink">Daftar Pesanan Berjalan (In-Flight Orders)</h2>
                    <p class="gpa-body text-ink-body">
                        Monitoring status pemenuhan komoditas secara real-time dari ladang panen ke dock Anda.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <label for="filter-status" class="gpa-micro text-ink-body">Filter Status:</label>
                    <select id="filter-status" x-model="statusFilter"
                        class="gpa-control h-auto w-auto min-w-[13rem] rounded-lg border-line-soft bg-canvas py-1.5 pr-8 text-xs">
                        <template x-for="option in statusOptions(@js($ordersTable))" :key="option.value">
                            <option :value="option.value" x-text="option.label"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full border-collapse">
                    <caption class="sr-only">Daftar pesanan berjalan beserta status pemenuhan komoditas</caption>
                    <thead>
                        <tr class="border-b border-line-soft bg-surface-shell">
                            <th scope="col" class="px-4 py-3.5 text-left gpa-micro-bold text-ink-body">No PO / Order</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Tanggal PO</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Rincian Komoditas &amp; Estimasi</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Berat Riil (Netto Sah)</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Supir &amp; No SJ</th>
                            <th scope="col" class="px-4 py-3 text-left gpa-micro-bold text-ink-body">Status Pesanan</th>
                            <th scope="col" class="px-4 py-3 text-right gpa-micro-bold text-ink-body">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="order in filteredOrders(@js($ordersTable))" :key="order.po">
                            <tr class="border-b border-line-soft/60 align-top">
                                <td class="px-4 py-4 text-sm font-bold leading-5 text-ink">
                                    <span x-text="order.po"></span>
                                </td>
                                <td class="px-4 py-4 gpa-meta text-ink-body"><span x-text="order.date"></span></td>
                                <td class="px-4 py-3.5">
                                    <p class="text-sm font-medium leading-5 text-ink"><span x-text="order.commodities"></span></p>
                                    <p class="gpa-body text-ink-body"><span x-text="order.packaging"></span></p>
                                </td>
                                <td class="px-4 py-4">
                                    <template x-if="order.weight">
                                        <div class="flex flex-col">
                                            <p class="text-sm font-bold leading-5 text-ink">
                                                <span x-text="order.weight"></span>
                                                <template x-if="order.weight_note">
                                                    <span class="font-normal text-ink-body" x-text="order.weight_note"></span>
                                                </template>
                                            </p>
                                            <p class="gpa-micro text-success-deep"><span x-text="order.scale_note"></span></p>
                                        </div>
                                    </template>
                                    <template x-if="! order.weight && order.weight_note">
                                        <span class="rounded bg-surface-pill px-2 py-0.5 gpa-micro text-ink-body"
                                            x-text="order.weight_note"></span>
                                    </template>
                                </td>
                                <td class="px-4 py-3.5">
                                    <template x-if="order.driver">
                                        <div class="flex flex-col">
                                            <p class="gpa-body font-medium text-ink"><span x-text="order.driver"></span></p>
                                            <template x-if="order.sj">
                                                <p class="text-[9px] font-bold uppercase leading-3 tracking-[0.12em] text-ink-body">
                                                    <span x-text="order.sj"></span>
                                                </p>
                                            </template>
                                            <template x-if="order.driver_note">
                                                <p class="gpa-micro font-semibold uppercase leading-3 text-ink-quiet">
                                                    <span x-text="order.driver_note"></span>
                                                </p>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="! order.driver">
                                        <p class="gpa-body italic text-ink-body"><span x-text="order.driver_note"></span></p>
                                    </template>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 gpa-micro-bold"
                                        :class="{
                                            'bg-accent text-ink ring-1 ring-inset ring-accent-edge': order.status_tone === 'accent',
                                            'bg-surface-pill text-ink-body ring-1 ring-inset ring-line-board': order.status_tone === 'neutral',
                                            'bg-warning-soft text-warning-deep ring-1 ring-inset ring-warning/40': order.status_tone === 'warning',
                                        }">
                                        <span class="h-1.5 w-1.5 rounded-full"
                                            :class="{
                                                'bg-ink': order.status_tone === 'accent',
                                                'bg-ink-body': order.status_tone === 'neutral',
                                                'bg-warning-deep': order.status_tone === 'warning',
                                            }"></span>
                                        <span x-text="order.status"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col items-end gap-1">
                                        <a :href="order.url || '{{ route('klien.orders.index') }}'"
                                            class="rounded bg-ink px-2.5 py-1 gpa-micro-bold text-white transition-colors hover:bg-brand-deep">
                                            Detail PO
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p x-cloak x-show="filteredOrders(@js($ordersTable)).length === 0"
                class="px-6 py-10 text-center gpa-body text-ink-body">
                Tidak ada pesanan berjalan saat ini.
            </p>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Quick reorder --}}
        {{-- ---------------------------------------------------------------- --}}
        <section id="quick-reorder" class="gpa-card flex flex-col gap-4 p-5 lg:p-6">
            <div class="flex flex-col gap-3 border-b border-line-soft pb-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0 flex flex-col gap-1">
                    <div class="flex items-center gap-2">
                        <x-gpa.icon name="badge-check" class="h-4 w-5 shrink-0 text-success-deep" />
                        <h2 class="gpa-section-title text-ink">Quick Reorder Kontrak (Pesan Cepat Kuota Harian)</h2>
                    </div>
                    <p class="gpa-body text-ink-body">
                        Reorder otomatis komoditas rutin dengan harga kontrak tetap tanpa perlu membuat PO manual dari awal.
                    </p>
                </div>
                <span
                    class="w-fit rounded-lg border border-line-soft bg-surface-shell px-3 py-1 gpa-micro-bold text-success-deep">
                    Harga Kontrak Dikunci: Berlisensi GPA
                </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($reorderData as $item)
                    <article class="flex flex-col justify-between gap-4 rounded-xl border border-line-soft bg-surface p-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="gpa-micro-bold text-success-deep">{{ $item['grade'] }}</p>
                                <x-gpa.icon name="package" class="h-4 w-4 shrink-0 text-ink-quiet" />
                            </div>
                            <h3 class="text-base font-bold leading-6 text-ink">{{ $item['name'] }}</h3>
                            <p class="flex items-baseline gap-1">
                                <span class="font-inter text-lg font-bold leading-6 text-ink">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                                <span class="font-inter text-xs text-ink-body">/ kg</span>
                            </p>
                            <p class="flex items-baseline gap-1 gpa-micro text-ink-body">
                                <span>Sisa Alokasi Kontrak:</span>
                                <span class="font-bold text-ink">{{ number_format($item['remaining'], 0, ',', '.') }} kg</span>
                            </p>
                        </div>

                        <div class="border-t border-line-soft/60 pt-3">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="gpa-micro text-ink-body">Jumlah (Kg):</span>
                                    <div class="flex items-center overflow-hidden rounded bg-canvas ring-1 ring-inset ring-line-board">
                                        <button type="button" @click="step(@js($item), -1)"
                                            aria-label="Kurangi {{ $item['name'] }}"
                                            class="px-2 py-0.5 font-mono text-sm leading-5 text-ink transition-colors hover:bg-surface-muted">
                                            &minus;
                                        </button>
                                        <input type="number" inputmode="numeric" min="0"
                                            max="{{ $item['remaining'] }}" step="{{ $item['step'] }}"
                                            value="{{ $item['initial_qty'] }}"
                                            x-model.number="quantities['{{ $item['key'] }}']"
                                            @change="setQuantity(@js($item), $event.target.value)"
                                            aria-label="Jumlah kilogram {{ $item['name'] }}"
                                            class="w-12 border-x border-line-soft bg-transparent py-0.5 text-center font-mono text-[11px] font-bold leading-[14px] text-ink focus:outline-none focus:ring-1 focus:ring-inset focus:ring-brand [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" />
                                        <button type="button" @click="step(@js($item), 1)"
                                            aria-label="Tambah {{ $item['name'] }}"
                                            class="px-2 py-0.5 font-mono text-sm leading-5 text-ink transition-colors hover:bg-surface-muted">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <button type="button" @click="addToDraft(@js($item))"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-ink px-3 py-2 transition-colors hover:bg-brand-deep">
                                    <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-white" />
                                    <span class="gpa-meta-lg font-bold text-white">Tambah ke Draft PO</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-8 text-center text-xs text-ink-body italic">
                        Belum ada komoditas aktif untuk quick reorder saat ini.
                    </div>
                @endforelse
            </div>

            {{-- Ringkasan draft PO --}}
            <div x-cloak x-show="draft.length > 0" x-transition
                class="flex flex-col gap-3 rounded-xl border border-accent-edge bg-surface-shell p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h3 class="gpa-label text-ink">Draft PO Berjalan (<span x-text="draftCount()">0</span> item)</h3>
                    <ul class="mt-1.5 flex flex-col gap-0.5">
                        <template x-for="line in draft" :key="line.key">
                            <li class="flex items-center gap-2 gpa-body text-ink-body">
                                <span class="font-medium text-ink" x-text="line.name"></span>
                                <span>&bull;</span>
                                <span x-text="line.qty + ' kg'"></span>
                                <span>&bull;</span>
                                <span class="font-medium text-ink" x-text="money(line.total)"></span>
                                <button type="button" @click="removeFromDraft(line.key)"
                                    class="ml-1 inline-flex items-center gap-1 text-ink-quiet transition-colors hover:text-danger"
                                    :aria-label="'Hapus ' + line.name + ' dari draft'">
                                    <x-gpa.icon name="x" class="h-3 w-3" />
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div class="flex shrink-0 flex-col items-start gap-2 lg:items-end">
                    <p class="gpa-meta-lg text-ink-body">
                        Total <span class="font-bold text-ink" x-text="draftWeight() + ' kg'"></span>
                        &bull;
                        <span class="font-bold text-success-deep" x-text="money(draftTotal())"></span>
                    </p>
                    <a href="{{ route('klien.cart') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 transition-colors hover:bg-brand-deep">
                        <x-gpa.icon name="send" class="h-3.5 w-3.5 text-white" />
                        <span class="gpa-meta-lg font-bold text-white">Buka Keranjang PO &amp; Checkout</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Modal Pengajuan Kontrak Kemitraan B2B (Khusus Klien Reguler) --}}
        {{-- ---------------------------------------------------------------- --}}
        @if (!$isContractClient && !$pendingContract)
            <div x-cloak x-show="showContractModal" 
                class="fixed inset-0 z-50 flex min-h-full items-center justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                
                <div @click.away="showContractModal = false"
                    class="relative my-auto flex max-h-[92vh] w-full max-w-xl flex-col rounded-2xl bg-surface p-5 sm:p-6 shadow-2xl outline outline-1 outline-line-board/50"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100">
                    
                    <header class="flex shrink-0 items-start justify-between border-b border-line-hair pb-3.5">
                        <div class="flex flex-col gap-1">
                            <span class="font-mono text-[10px] font-bold uppercase tracking-wider text-success-deep">
                                FORMULIR PERJANJIAN KERJA SAMA (PKS) B2B &bull; RESMI GPA
                            </span>
                            <h3 class="text-lg font-bold text-ink">Pengajuan Kontrak Kemitraan B2B &amp; Plafon Tempo</h3>
                            <p class="text-xs text-ink-body">
                                Lengkapi keterangan entitas usaha, penanggung jawab (PIC), dan nomor WhatsApp aktif untuk verifikasi langsung Direktur Utama GPA.
                            </p>
                        </div>
                        <button type="button" @click="showContractModal = false" class="rounded-lg p-1 text-ink-quiet hover:bg-surface-disabled hover:text-ink">
                            <x-gpa.icon name="x" class="h-5 w-5" />
                        </button>
                    </header>

                    <form action="{{ route('klien.contracts.propose') }}" method="POST" class="mt-3 flex flex-1 flex-col gap-3.5 overflow-y-auto pr-1 gpa-scrollbar">
                        @csrf

                        {{-- SEKSI 1: IDENTITAS ENTITAS USAHA (Tabel users) --}}
                        <div class="rounded-xl border border-line-hair bg-surface-shell/40 p-3.5 space-y-2.5">
                            <div class="flex items-center gap-1.5 border-b border-line-hair pb-1.5">
                                <x-gpa.icon name="building" class="h-3.5 w-3.5 text-brand-deep" />
                                <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">1. Identitas Badan Usaha / Pemohon</h4>
                            </div>

                            <div>
                                <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                    Nama Resmi Badan Usaha / Perusahaan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="company_name" required
                                    value="{{ old('company_name', Auth::user()->company_name) }}"
                                    placeholder="Contoh: PT Boga Mandiri Sejahtera / Hotel Grand Pasundan / Resto Dapur Nusantara"
                                    class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                            </div>

                            <div>
                                <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                    Alamat Kantor / Titik Bongkar Muat Utama <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="address" required
                                    value="{{ old('address', Auth::user()->address) }}"
                                    placeholder="Contoh: Jl. Sukajadi No. 128, Bandung, Jawa Barat (Gudang Central Kitchen)"
                                    class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                            </div>
                        </div>

                        {{-- SEKSI 2: KREDENSIAL PIC & NO WHATSAPP (Tabel users) --}}
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3.5 space-y-2.5">
                            <div class="flex items-center justify-between border-b border-emerald-200 pb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <x-gpa.icon name="user-check" class="h-3.5 w-3.5 text-emerald-800" />
                                    <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-emerald-950">2. Penanggung Jawab (PIC) &amp; Verifikasi WhatsApp</h4>
                                </div>
                                <span class="rounded bg-emerald-200/80 px-2 py-0.5 font-mono text-[9px] font-extrabold text-emerald-900">
                                    WAJIB AKTIF
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                        Nama PIC Pengadaan <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="pic_name" required
                                        value="{{ old('pic_name', Auth::user()->pic_name ?? Auth::user()->name) }}"
                                        placeholder="Contoh: Hendra Kusuma (Purchasing)"
                                        class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                                </div>

                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">
                                        Nomor WhatsApp PIC <span class="text-danger">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" name="pic_phone" required
                                            value="{{ old('pic_phone', Auth::user()->pic_phone ?? Auth::user()->phone) }}"
                                            placeholder="Contoh: 081234567890"
                                            class="h-9 w-full rounded-lg border border-line-strong bg-surface pl-3 pr-8 font-mono text-xs text-ink focus:border-brand focus:ring-0">
                                        <x-gpa.icon name="phone" class="pointer-events-none absolute right-2.5 top-2.5 h-3.5 w-3.5 text-emerald-700" />
                                    </div>
                                </div>
                            </div>

                            <p class="text-[10px] text-emerald-900 leading-tight">
                                &bull; Nomor WhatsApp di atas akan dihubungi oleh Tim Kemitraan GPA dalam 1x24 jam untuk verifikasi draf PKS dan kelayakan fasilitas tempo.
                            </p>
                        </div>

                        {{-- SEKSI 3: KLAUSUL PASOKAN & TERMIN (Tabel contracts) --}}
                        <div class="rounded-xl border border-line-hair bg-surface-shell/40 p-3.5 space-y-2.5">
                            <div class="flex items-center gap-1.5 border-b border-line-hair pb-1.5">
                                <x-gpa.icon name="package" class="h-3.5 w-3.5 text-brand-deep" />
                                <h4 class="font-mono text-xs font-bold uppercase tracking-wider text-ink">3. Klausul Pasokan Komoditas &amp; Finansial</h4>
                            </div>

                            <div>
                                <label class="mb-1 block font-mono text-xs font-semibold text-ink">Komoditas Utama yang Dikontrakkan <span class="text-danger">*</span></label>
                                <select name="product_id" required class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                                    <option value="">-- Pilih Komoditas --</option>
                                    @foreach ($allAvailableProducts as $prod)
                                        <option value="{{ $prod->id }}">
                                            {{ $prod->name }} (Rp {{ number_format($prod->base_price, 0, ',', '.') }}/kg) - Min Order {{ $prod->minimum_order }} kg
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">Estimasi Komitmen Volume (Kg) <span class="text-danger">*</span></label>
                                    <input type="number" name="committed_volume_per_cycle" min="50" step="10" required placeholder="Contoh: 500"
                                        class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 font-mono text-xs text-ink focus:border-brand focus:ring-0">
                                    <span class="mt-0.5 block text-[10px] text-ink-quiet">Minimal 50 kg per siklus kirim</span>
                                </div>
                                <div>
                                    <label class="mb-1 block font-mono text-xs font-semibold text-ink">Term of Payment (TOP) <span class="text-danger">*</span></label>
                                    <select name="top_days" required class="h-9 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                                        <option value="14">TOP 14 Hari Kalender</option>
                                        <option value="30" selected>TOP 30 Hari Kalender (Standar)</option>
                                        <option value="45">TOP 45 Hari (Kemitraan Khusus)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- SEKSI 4: HOTLINE WHATSAPP KEMITRAAN GPA --}}
                        <div class="flex items-center justify-between rounded-xl border border-line-hair bg-surface-pill p-2.5 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                                    <x-gpa.icon name="message-square" class="h-3.5 w-3.5 text-white" />
                                </span>
                                <div>
                                    <p class="font-bold text-ink">Hotline WhatsApp Tim Kemitraan GPA</p>
                                    <p class="text-[10px] text-ink-body">Konsultasi langsung PKS: <strong>+62 811-1223-3445</strong></p>
                                </div>
                            </div>
                            <a href="https://wa.me/6281112233445?text=Halo%20Tim%20Kemitraan%20GPA,%20saya%20ingin%20berkonsultasi%20mengenai%20kontrak%20B2B" target="_blank" rel="noopener noreferrer"
                                class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-surface px-2.5 py-1 font-mono text-[10px] font-bold text-emerald-800 border border-emerald-300 hover:bg-emerald-50 transition">
                                <x-gpa.icon name="phone" class="h-3 w-3 text-emerald-700" />
                                <span>Hubungi GPA</span>
                            </a>
                        </div>

                        <footer class="mt-2 flex shrink-0 items-center justify-end gap-2 border-t border-line-hair pt-3">
                            <button type="button" @click="showContractModal = false" class="rounded-lg bg-surface-disabled px-4 py-2 font-mono text-xs font-semibold text-ink hover:bg-surface-muted transition">
                                Batal
                            </button>
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-4 py-2 font-mono text-xs font-bold text-accent shadow-sm hover:bg-brand-deep transition">
                                <x-gpa.icon name="send" class="h-3.5 w-3.5 text-accent" />
                                <span>Kirim Proposal ke Direktur</span>
                            </button>
                        </footer>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
