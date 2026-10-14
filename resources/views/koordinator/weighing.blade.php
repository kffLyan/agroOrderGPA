@extends('layouts.coordinator')

@section('title', 'Penimbangan Aktual & Validasi Sortir')

@section('content')
    <div class="space-y-6"
        x-data="coordinatorWeighing(@js([
            'estimate' => $estimate ?? 0,
            'rate' => $unitRate ?? 15000,
            'gross' => $gross ?? 0,
            'crates' => (int) ($crates ?? 1),
            'tareEach' => (float) ($tareEach ?? 2),
            'tolerance' => 2.0,
            'orderId' => $orderId ?? null,
            'lockUrl' => isset($orderId) ? route('koordinator.weighing.store', $orderId) : null,
        ]))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-center justify-between gap-4 border-b border-line-board/30 pb-6">
            <div class="min-w-0">
                <p class="gpa-eyebrow">Penimbangan &amp; Sortir Gudang</p>
                <h1 class="mt-1 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    Penimbangan Berat Bersih (Netto)<br>
                    &amp; Pengecekan Kualitas Panen
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-5 text-ink-body">
                    Pencatatan hasil penimbangan riil di gudang, pengurangan berat keranjang kosong, dan pemeriksaan kualitas sayur sebelum membuat surat jalan.
                </p>
            </div>

            <div class="shrink-0 rounded-lg bg-surface px-3 py-2 outline outline-1 -outline-offset-1 outline-line-board/50">
                <p class="text-right text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet gpa-meta">
                    WAKTU PENIMBANGAN
                </p>
                <p class="mt-0.5 text-right gpa-meta-lg font-semibold text-ink">
                    {{ now()->format('d M Y') }},<br>{{ now()->format('H:i') }} WIB
                </p>
            </div>
        </section>

        {{-- Stage tracker --}}
        <section class="gpa-panel p-4">
            <div class="grid gap-4 md:grid-cols-3">
                {{-- Stage 1: Persiapan Panen --}}
                <article class="flex items-center gap-4 rounded-lg p-2 transition-colors bg-surface-shell outline outline-1 -outline-offset-1 outline-line-board/30">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-deep">
                        <x-gpa.icon name="leaf" class="h-[18px] w-[18px] shrink-0 text-surface" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-2xs font-bold uppercase leading-3 tracking-[0.45px] text-success-deep">Tahap 1: Selesai</p>
                        <p class="text-lg leading-6 font-semibold text-ink">
                            Persiapan Panen<br>
                        </p>
                        <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-ink-body">Target Pesanan: {{ number_format($estimate ?? 0, 0, ',', '.') }} kg</p>
                    </div>
                </article>

                {{-- Stage 2: Penimbangan Netto & Sortir Kualitas --}}
                <article @class([
                    'flex items-center gap-4 rounded-lg p-2 transition-colors',
                    'bg-surface-shell outline outline-1 -outline-offset-1 outline-line-board/30' => $isWeighed ?? false,
                    'bg-accent/40 py-2 outline outline-2 -outline-offset-2 outline-success-deep' => !($isWeighed ?? false),
                ])>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success-deep">
                        <x-gpa.icon name="scale" class="h-[18px] w-[18px] shrink-0 text-surface" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-2xs font-bold uppercase leading-3 tracking-[0.45px] text-success-deep">
                            {{ ($isWeighed ?? false) ? 'Tahap 2: Selesai Timbang' : 'Tahap 2: Sedang Berjalan' }}
                        </p>
                        <p @class([
                            'text-lg leading-6',
                            'font-semibold text-ink' => $isWeighed ?? false,
                            'font-bold text-ink' => !($isWeighed ?? false),
                        ])>
                            Penimbangan Netto &amp;<br>Sortir Kualitas
                        </p>
                        <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-ink-body">Batas Toleransi Selisih &plusmn;2.0%</p>
                    </div>
                </article>

                {{-- Stage 3: Penerbitan Surat Jalan --}}
                <article @class([
                    'flex items-center gap-4 rounded-lg p-2 transition-colors',
                    'bg-accent/40 py-2 outline outline-2 -outline-offset-2 outline-success-deep' => $isWeighed ?? false,
                    'bg-surface-shell py-2 opacity-70 outline outline-1 -outline-offset-1 outline-line-board/30' => !($isWeighed ?? false),
                ])>
                    <span @class([
                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg',
                        'bg-success-deep' => $isWeighed ?? false,
                        'bg-surface-pill' => !($isWeighed ?? false),
                    ])>
                        <x-gpa.icon name="truck" class="h-[18px] w-[18px] shrink-0 {{ ($isWeighed ?? false) ? 'text-surface' : 'text-ink-quiet' }}" />
                    </span>
                    <div class="min-w-0">
                        <p @class([
                            'text-2xs font-bold uppercase leading-3 tracking-[0.45px]',
                            'text-success-deep' => $isWeighed ?? false,
                            'text-ink-quiet' => !($isWeighed ?? false),
                        ])>
                            {{ ($isWeighed ?? false) ? 'Tahap 3: Siap Surat Jalan' : 'Tahap 3: Menunggu Kunci Data' }}
                        </p>
                        <p @class([
                            'text-lg leading-6',
                            'font-bold text-ink' => $isWeighed ?? false,
                            'font-semibold text-ink-quiet' => !($isWeighed ?? false),
                        ])>
                            Penerbitan Surat Jalan<br>(Surat Pengantar)
                        </p>
                        <p @class([
                            'text-2xs font-semibold leading-3 tracking-[1.08px]',
                            'text-ink-body' => $isWeighed ?? false,
                            'text-ink-quiet' => !($isWeighed ?? false),
                        ])>
                            {{ ($isWeighed ?? false) ? 'Siap Cetak & Kirim' : 'Menunggu Konfirmasi Penimbangan' }}
                        </p>
                    </div>
                </article>
            </div>
        </section>

        {{-- PRD rules --}}
        <section class="grid gap-4 md:grid-cols-2">
            {{-- Rule 04 --}}
            <article class="flex items-start gap-2 rounded-xl bg-surface p-4 shadow-card border-l-4 border-success-deep">
                <span class="shrink-0 rounded bg-accent p-1 pt-[9px]">
                    <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-success-ink" />
                </span>
                <div class="min-w-0 space-y-0.5">
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[1px] bg-accent text-success-ink">
                            ATURAN PENAGIHAN
                        </span>
                        <span class="text-2xs font-bold uppercase leading-3 tracking-[0.45px] text-ink-body">
                            Penagihan Wajib Berdasarkan Berat Timbangan Nyata
                        </span>
                    </div>
                    <p class="text-xs leading-4">
                        <span class="font-semibold text-ink">Jumlah pesanan awal adalah estimasi alokasi. Tagihan pembayaran dan Surat Jalan wajib merujuk secara mutlak pada </span>
                        <span class="font-semibold underline text-success-deep">Berat Bersih (Netto) hasil penimbangan riil di gudang</span>
                        <span class="font-semibold text-ink"> (Berat Kotor dikurangi berat keranjang kosong).</span>
                    </p>
                </div>
            </article>

            {{-- Rule 05 --}}
            <article class="flex items-start gap-2 rounded-xl bg-surface p-4 shadow-card border-l-4 border-line-board/40">
                <span class="shrink-0 rounded bg-accent p-1 pt-[9px]">
                    <x-gpa.icon name="alert-triangle" class="h-4 w-4 shrink-0 text-danger-ink" />
                </span>
                <div class="min-w-0 space-y-0.5">
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[1px] bg-danger-soft text-danger-ink">
                            ATURAN PENGIRIMAN
                        </span>
                        <span class="text-2xs font-bold uppercase leading-3 tracking-[0.45px] text-danger">
                            Surat Jalan Memerlukan Kunci Timbangan
                        </span>
                    </div>
                    <p class="text-xs leading-4">
                        <span class="font-semibold text-ink">Surat Jalan fisik tidak boleh diterbitkan sebelum </span>
                        <span class="font-bold text-danger">data penimbangan dikunci (disahkan) oleh Koordinator Lapangan</span>
                        <span class="font-semibold text-ink"> demi menghindari perbedaan data dengan pihak penerima/klien.</span>
                    </p>
                </div>
            </article>
        </section>

        {{-- Order banner --}}
        <section class="relative flex flex-col justify-center gap-4 rounded-xl bg-brand p-4 outline outline-1 -outline-offset-1 outline-success-deep/30">
            <div class="flex flex-wrap items-center gap-4">
                <span class="rounded-lg bg-success-deep px-3 py-1.5 gpa-meta-lg font-bold text-white">
                    {{ $orderCode ?? 'PO-GPA-2026-0001' }}
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-lg leading-6 font-bold text-white">{{ $clientName ?? 'Mitra B2B' }}</p>
                        <span class="rounded-full bg-accent px-2 py-0.5 text-2xs font-bold tracking-[1.08px] text-success-ink gpa-micro-bold">
                            KONTRAK KEMITRAAN B2B
                        </span>
                    </div>
                    <p class="gpa-meta text-brand-line/70 text-white/70">Komoditas: {{ $commodities ?? 'Selada Romaine' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <div class="border-l border-ink-quiet/30 pl-4">
                    <p class="text-2xs font-medium leading-3 tracking-[0.88px] text-surface-pill gpa-meta">ESTIMASI PESANAN AWAL</p>
                    <p class="text-lg leading-6 font-bold text-white gpa-figure">
                        <span x-text="kg(estimate)">{{ number_format($estimate ?? 0, 2, '.', '') }}</span>
                        <span class="ml-1 text-xs font-normal">kg</span>
                    </p>
                </div>

                <div class="border-l border-ink-quiet/30 pl-4">
                    <p class="text-2xs font-medium leading-3 tracking-[0.88px] text-surface-pill gpa-meta">HARGA PER KG</p>
                    <p class="text-lg leading-6 font-bold text-accent gpa-figure">
                        Rp {{ number_format($unitRate ?? 0, 0, ',', '.') }}<span class="ml-1 text-2xs font-normal text-accent">/kg</span>
                    </p>
                </div>

                <div class="border-l border-ink-quiet/30 pl-4">
                    <p class="text-2xs font-medium leading-3 tracking-[0.88px] text-surface-pill gpa-meta">DIVERIFIKASI OLEH</p>
                    <p class="gpa-meta-lg font-bold text-white">{{ $verifierName ?? 'Sekretaris Operasional' }}</p>
                    <p class="gpa-meta-lg font-bold text-brand-line/70">[SEC-OP-001]</p>
                </div>
            </div>
        </section>

        {{-- Terminal + QC grading --}}
        <div class="grid items-start gap-6 xl:grid-cols-2">
            {{-- Terminal Input Penimbangan Massa Fisik --}}
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/30 pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <x-gpa.icon name="scale" class="h-5 w-5 shrink-0 text-success-deep" />
                        <div class="min-w-0">
                            <h2 class="text-lg leading-6 font-semibold text-ink">Form Input Penimbangan Hasil Panen</h2>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                Pencatatan Timbangan Gudang (Manual &amp; Digital)
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-accent py-1 pl-2.5 pr-2.5 outline outline-1 -outline-offset-1 outline-success-deep">
                        <span class="h-2 w-1.5 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <span class="text-2xs font-bold tracking-[1.08px] text-success-ink gpa-micro-bold">Timbangan Siap Digunakan</span>
                    </span>
                </div>

                {{-- Dark readout --}}
                <div class="flex flex-col gap-2 rounded-xl bg-ink p-4 outline outline-1 -outline-offset-1 outline-success-deep/40">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-2xs font-semibold tracking-[1.08px] text-surface-pill">Sensor Timbangan: Terkalibrasi Sah</p>
                        <p class="text-2xs font-semibold tracking-[1.08px] text-accent">Hitung Netto Otomatis: Aktif</p>
                    </div>

                    <div class="flex items-start justify-center gap-3 sm:gap-4">
                        {{-- Box 1: Gross --}}
                        <div class="flex flex-1 flex-col items-center gap-1 px-1 pb-1.5 pt-1">
                            <p class="text-center text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-surface-pill">
                                Berat Kotor (Bruto)
                            </p>
                            <p class="text-center text-2xl sm:text-[28px] font-bold text-white leading-tight">
                                <span x-text="kg(gross)">{{ number_format($gross ?? 0, 2, '.', '') }}</span>
                            </p>
                            <p class="text-center text-2xs font-semibold leading-3 tracking-[1.08px] text-accent">
                                KG BESERTA WADAH
                            </p>
                        </div>

                        {{-- Box 2: Tare --}}
                        <div class="flex flex-1 flex-col items-center gap-1 px-1 pb-1.5 pt-1 border-x border-ink-quiet/30">
                            <p class="text-center text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-surface-pill">
                                Berat Wadah (Tara)
                            </p>
                            <p class="text-center text-2xl sm:text-[28px] font-bold text-surface-pill leading-tight">
                                <span x-text="kg(tareTotal())">{{ number_format($tareCrates ?? 0, 2, '.', '') }}</span>
                            </p>
                            <p class="text-center text-2xs font-semibold leading-3 tracking-[1.08px] text-ink-quiet">
                                <span x-text="crates + ' PETI @ ' + kg(tareEach) + ' KG'">{{ ($crates ?? 1) . ' PETI @ ' . number_format($tareEach ?? 2, 2) . ' KG' }}</span>
                            </p>
                        </div>

                        {{-- Box 3: Net --}}
                        <div class="flex flex-1 flex-col items-center gap-1 px-1 pb-1.5 pt-1 rounded-lg bg-brand/60">
                            <p class="text-center text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-accent font-bold">
                                Berat Bersih (Netto)
                            </p>
                            <p class="text-center text-2xl sm:text-[28px] font-bold text-accent leading-tight">
                                <span x-text="kg(net())">{{ number_format($actualNet ?? 0, 2, '.', '') }}</span>
                            </p>
                            <p class="text-center text-2xs font-bold leading-3 tracking-[1.08px] text-white">
                                KG BERSIH SIAP KIRIM
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Form Input Manual Penimbangan --}}
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-ink">
                            Isi Parameter Penimbangan Manual:
                        </p>
                        <span class="rounded bg-accent/60 px-2 py-0.5 text-2xs font-bold text-success-ink">
                            Bisa Diedit Manual
                        </span>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        {{-- Input 1: Berat Kotor (Bruto) --}}
                        <div class="space-y-1">
                            <label for="weigh-gross" class="block text-xs font-bold text-ink">
                                1. Berat Kotor (Bruto)
                            </label>
                            <p class="text-2xs text-ink-quiet">Barang + Keranjang (kg)</p>
                            <div class="relative mt-1">
                                <input id="weigh-gross" type="number" step="0.01" min="0"
                                    :disabled="locked"
                                    x-model.number="gross"
                                    placeholder="Contoh: 51.00"
                                    class="h-11 w-full rounded-lg bg-surface py-2 pl-3 pr-12 text-sm font-bold text-ink outline outline-1 outline-line-board transition-colors focus:outline-2 focus:outline-success-deep disabled:cursor-not-allowed disabled:bg-surface-shell disabled:opacity-70">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-ink-quiet">
                                    KG
                                </span>
                            </div>
                        </div>

                        {{-- Input 2: Jumlah Keranjang --}}
                        <div class="space-y-1">
                            <label for="weigh-crates" class="block text-xs font-bold text-ink">
                                2. Jumlah Keranjang
                            </label>
                            <p class="text-2xs text-ink-quiet">Banyaknya peti/wadah</p>
                            <div class="relative mt-1">
                                <input id="weigh-crates" type="number" step="1" min="1"
                                    :disabled="locked"
                                    x-model.number="crates"
                                    placeholder="Contoh: 3"
                                    class="h-11 w-full rounded-lg bg-surface py-2 pl-3 pr-14 text-sm font-bold text-ink outline outline-1 outline-line-board transition-colors focus:outline-2 focus:outline-success-deep disabled:cursor-not-allowed disabled:bg-surface-shell disabled:opacity-70">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-ink-quiet">
                                    PETI
                                </span>
                            </div>
                        </div>

                        {{-- Input 3: Berat Tiap Keranjang --}}
                        <div class="space-y-1">
                            <label for="weigh-tare-each" class="block text-xs font-bold text-ink">
                                3. Berat Tiap Keranjang
                            </label>
                            <p class="text-2xs text-ink-quiet">Wadah kosong (default 2 kg)</p>
                            <div class="relative mt-1">
                                <input id="weigh-tare-each" type="number" step="0.01" min="0"
                                    :disabled="locked"
                                    x-model.number="tareEach"
                                    placeholder="Contoh: 2.00"
                                    class="h-11 w-full rounded-lg bg-surface py-2 pl-3 pr-12 text-sm font-bold text-ink outline outline-1 outline-line-board transition-colors focus:outline-2 focus:outline-success-deep disabled:cursor-not-allowed disabled:bg-surface-shell disabled:opacity-70">
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-ink-quiet">
                                    KG
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Banner Rumus Penjelasan Netto --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-success-deep/30 bg-accent/30 p-2.5 text-xs text-ink">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="scale" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span>
                                Rumus Otomatis: <strong>Berat Bersih (Netto)</strong> = Berat Kotor (<span class="font-bold" x-text="kg(gross) + ' kg'"></span>) &minus; Total Wadah (<span class="font-bold" x-text="kg(tareTotal()) + ' kg'"></span>)
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-ink-body">=</span>
                            <span class="rounded bg-ink px-2 py-0.5 font-mono text-xs font-bold text-accent" x-text="kg(net()) + ' KG NETTO'"></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-line-board/30 pt-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" @click="act('reset-zero')" :disabled="locked"
                            class="rounded-lg px-3 py-1.5 text-center text-xs font-semibold text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-shell disabled:cursor-not-allowed disabled:opacity-50">
                            Nol-kan Timbangan (Reset)
                        </button>
                        <button type="button" @click="act('re-read')" :disabled="locked"
                            class="rounded-lg px-3 py-1.5 text-center text-xs font-semibold text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-shell disabled:cursor-not-allowed disabled:opacity-50">
                            Perbarui Angka Timbangan
                        </button>
                    </div>

                    <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold">
                        Status Kalibrasi Timbangan: Sah &amp; Akurat
                    </p>
                </div>
            </section>

            {{-- Form Quality Control & Grading Sortir Fisik --}}
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/30 pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <x-gpa.icon name="eye" class="h-5 w-5 shrink-0 text-success-deep" />
                        <div class="min-w-0">
                            <h2 class="text-lg leading-6 font-semibold text-ink">Pemeriksaan Kualitas &amp; Pemilahan Daun</h2>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                Pengecekan kesegaran daun dan pemisahan daun rusak/kuning
                            </p>
                        </div>
                    </div>

                    <span class="shrink-0 rounded bg-accent px-1.5 py-0.5 text-[10px] font-bold uppercase leading-3 tracking-[1px] text-success-ink">
                        Standar Mutu GPA
                    </span>
                </div>

                <div class="overflow-hidden rounded-xl outline outline-1 -outline-offset-1 outline-line-board/40">
                    <div class="bg-surface-shell">
                        <div class="flex items-center">
                            <div class="w-[126px] shrink-0 px-3 py-3 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body">KATEGORI SAYUR</div>
                            <div class="w-[74px] shrink-0 px-3 py-3 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body">BERAT BERSIH</div>
                            <div class="w-[89px] shrink-0 px-3 py-3 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body">PERSENTASE</div>
                            <div class="w-[85px] shrink-0 px-3 py-3 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body">STATUS PEMERIKSAAN</div>
                            <div class="w-[106px] flex-1 text-right px-3 py-3 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink-body">TINDAKAN GUDANG</div>
                        </div>
                    </div>

                    {{-- Row 1: Grade A --}}
                    <div class="flex items-center border-t border-line-board/30 bg-surface">
                        <div class="w-[126px] shrink-0 space-y-0.5 px-3 py-3">
                            <div class="flex items-center gap-1">
                                <span class="h-2.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                                <span class="text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink">Grade A (Segar &amp; Prima)</span>
                            </div>
                            <p class="text-2xs leading-3 tracking-[1.08px] text-ink-quiet">Daun segar, renyah,</p>
                            <p class="text-2xs leading-3 tracking-[1.08px] text-ink-quiet">bersih dari tanah,</p>
                            <p class="text-2xs leading-3 tracking-[1.08px] text-ink-quiet">tanpa busuk</p>
                        </div>
                        <div class="w-[74px] shrink-0 px-3 py-3">
                            <p class="text-[11px] font-bold leading-[14px] tracking-[0.88px] text-ink">
                                <span x-text="kg(gradingMass('grade-a'))">{{ number_format($cleanKg ?? 0, 2, '.', '') }}</span>
                                <span class="ml-0.5 text-2xs font-normal">kg</span>
                            </p>
                        </div>
                        <div class="w-[89px] shrink-0 px-3 py-3">
                            <p class="text-[11px] font-bold leading-[14px] tracking-[0.88px] text-success-deep" x-text="gradingPercent('grade-a')">{{ $cleanPercent ?? '100%' }}</p>
                        </div>
                        <div class="w-[85px] shrink-0 px-3 py-3">
                            <span class="inline-flex w-[53px] flex-col rounded px-2 py-1 text-2xs font-bold leading-3 tracking-[1.08px] bg-accent text-success-ink">
                                <span>LOLOS</span>
                                <span>SORTIR</span>
                            </span>
                        </div>
                        <div class="w-[106px] flex-1 px-3 py-3 text-right">
                            <p class="text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-success-deep">
                                <span class="block">Muat ke</span>
                                <span class="block">Box</span>
                                <span class="block">Kirim</span>
                            </p>
                        </div>
                    </div>

                    {{-- Row 2: Afkir --}}
                    <div class="flex items-center border-t border-line-board/30 bg-surface-shell/30">
                        <div class="w-[126px] shrink-0 space-y-0.5 px-3 py-3">
                            <div class="flex items-center gap-1">
                                <span class="h-2.5 w-1.5 shrink-0 rounded-full bg-warning" aria-hidden="true"></span>
                                <span class="text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-warning-deep">Afkir (Daun Kuning / Rusak)</span>
                            </div>
                            <p class="text-2xs leading-3 tracking-[1.08px] text-ink-quiet">Penyortiran daun layu,</p>
                            <p class="text-2xs leading-3 tracking-[1.08px] text-ink-quiet">kotor &amp; cacat</p>
                        </div>
                        <div class="w-[74px] shrink-0 px-3 py-3">
                            <p class="text-[11px] font-bold leading-[14px] tracking-[0.88px] text-warning-deep">
                                <span x-text="kg(gradingMass('afkir'))">{{ number_format($afkirKg ?? 0, 2, '.', '') }}</span>
                                <span class="ml-0.5 text-2xs font-normal">kg</span>
                            </p>
                        </div>
                        <div class="w-[89px] shrink-0 px-3 py-3">
                            <p class="text-[11px] font-bold leading-[14px] tracking-[0.88px] text-warning-deep" x-text="gradingPercent('afkir')">0.6%</p>
                        </div>
                        <div class="w-[85px] shrink-0 px-3 py-3">
                            <span class="inline-flex w-[53px] flex-col rounded px-2 py-1 text-2xs font-bold leading-3 tracking-[1.08px] bg-warning-soft text-warning-ink">
                                <span>DIPISAHKAN</span>
                                <span>SUSUT</span>
                            </span>
                        </div>
                        <div class="w-[106px] flex-1 px-3 py-3 text-right">
                            <p class="text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-warning-deep">
                                <span class="block">Dijadikan</span>
                                <span class="block">Kompos /</span>
                                <span class="block">Pakan</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 -outline-offset-1 outline-ink-quiet/50">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="flex h-14 w-8 shrink-0 items-center justify-center rounded-lg bg-surface-disabled outline outline-1 -outline-offset-1 outline-line-board">
                            <x-gpa.icon name="upload" class="h-6 w-6 shrink-0 text-ink-quiet" />
                        </span>

                        <div class="min-w-0">
                            <p class="text-lg leading-6 font-semibold text-ink">Foto Dokumentasi Timbangan &amp; Sayur</p>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                Ambil atau unggah foto angka timbangan serta sampel daun segar bila diperlukan arsip bukti fisik.
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="uploadSample()"
                        class="shrink-0 rounded-lg bg-surface px-6 py-2 text-center text-[11px] font-semibold leading-[14px] tracking-[0.88px] text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <span x-text="sampleUploaded ? 'SAMPLE TERSIMPAN' : 'UNGGAH FOTO BUKTI'">UNGGAH FOTO BUKTI</span>
                    </button>
                </div>
            </section>
        </div>

        {{-- Rekonsiliasi + Finansial + Audit --}}
        <div class="grid items-start gap-6 xl:grid-cols-2">
            {{-- Rekonsiliasi Sumber Pasokan --}}
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-board/30 pb-2">
                    <div class="flex min-w-0 items-center gap-2">
                        <x-gpa.icon name="package" class="h-[18px] w-[18px] shrink-0 text-success-deep" />
                        <h2 class="text-lg leading-6 font-semibold text-ink">Perbandingan Estimasi vs Realisasi Timbangan</h2>
                    </div>

                    <p class="shrink-0 text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold">
                        Status Pesanan: Sesuai (100%)
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board/30">
                        <div class="min-w-0">
                            <p class="gpa-meta-lg font-bold text-ink">Petani Binaan Kelompok Tani</p>
                            <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-ink-quiet">BATCH: PANEN-INTI-{{ date('Ym') }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="gpa-meta-lg font-bold text-ink">{{ number_format(($actualNet ?? 0) * 0.7, 2, ',', '.') }} kg</p>
                            <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-success-deep">70% Total Pasokan</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board/30">
                        <div class="min-w-0">
                            <p class="gpa-meta-lg font-bold text-ink">Pasokan Cadangan (Buffer)</p>
                            <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-ink-quiet">BATCH: BUFFER-{{ date('Ym') }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="gpa-meta-lg font-bold text-ink">{{ number_format(($actualNet ?? 0) * 0.3, 2, ',', '.') }} kg</p>
                            <p class="text-2xs font-semibold leading-3 tracking-[1.08px] text-success-deep">30% Total Pasokan</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-1 rounded-xl bg-surface-shell p-4 outline outline-1 -outline-offset-1 outline-line-board/40">
                    <div class="flex items-center justify-between gap-3 pb-1">
                        <p class="gpa-meta font-medium text-ink-body">Estimasi Pesanan Awal:</p>
                        <p class="text-right gpa-meta-lg font-bold text-ink">{{ number_format($estimate ?? 0, 2, ',', '.') }} kg</p>
                    </div>

                    <div class="flex items-center justify-between gap-3 pb-1">
                        <p class="gpa-meta font-medium text-ink-body">Hasil Timbangan Bersih Riil:</p>
                        <p class="text-right gpa-meta-lg font-bold text-success-deep">{{ number_format($actualNet ?? 0, 2, ',', '.') }} kg</p>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-line-board/40 pb-1 pt-2">
                        <p class="gpa-meta font-medium text-ink-body">Selisih Berat (Susut / Tambah):</p>
                        <p @class([
                            'text-right gpa-meta-lg font-bold',
                            'text-warning-deep' => ($varianceKg ?? 0) < 0,
                            'text-success-deep' => ($varianceKg ?? 0) >= 0,
                        ])>
                            <span x-text="varianceText()">{{ (($varianceKg ?? 0) >= 0 ? '+' : '') . number_format($varianceKg ?? 0, 2, ',', '.') . ' kg (' . ($variancePct ?? 0) . '%)' }}</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-1 pt-1">
                        <x-gpa.icon name="check-circle" class="h-[10px] w-[10px] shrink-0 text-success-deep" />
                        <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold"
                            x-text="toleranceText()">Status: Selisih masih dalam batas wajar (&le; 2.0%)</p>
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-6">
                {{-- Perhitungan Finansial Otomatis --}}
                <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 outline outline-2 -outline-offset-2 outline-success-deep/50">
                    <div class="flex items-center gap-2 border-b border-line-board/30 pb-2">
                        <x-gpa.icon name="banknote" class="h-4 w-4 shrink-0 text-success-deep" />
                        <div class="min-w-0">
                            <h2 class="text-lg leading-6 font-semibold text-ink">Rincian Nilai Pembayaran Pesanan</h2>
                            <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-success-deep gpa-micro-bold">
                                Perhitungan Otomatis Berdasarkan Timbangan
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pb-2">
                        <div class="flex items-center justify-between gap-3">
                            <p class="gpa-meta font-medium text-ink-body">Harga Kontrak per Kg</p>
                            <p class="text-right gpa-meta-lg font-semibold text-ink">Rp {{ number_format($unitRate ?? 0, 0, ',', '.') }} / kg</p>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p class="gpa-meta font-medium text-ink-body">Estimasi Awal Pesanan ({{ number_format($estimate ?? 0, 0, ',', '.') }} kg)</p>
                            <p class="text-right gpa-meta-lg font-semibold line-through text-ink-quiet">Rp {{ number_format($estAmount ?? 0, 0, ',', '.') }}</p>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <p @class([
                                'gpa-meta font-medium',
                                'text-warning-deep' => ($diffAmount ?? 0) < 0,
                                'text-ink-body' => ($diffAmount ?? 0) >= 0,
                            ])>Penyesuaian Berdasarkan Timbangan Nyata</p>
                            <p @class([
                                'text-right gpa-meta-lg font-semibold',
                                'text-warning-deep' => ($diffAmount ?? 0) < 0,
                                'text-ink' => ($diffAmount ?? 0) >= 0,
                            ])>{{ (($diffAmount ?? 0) >= 0 ? '+ Rp ' : '- Rp ') . number_format(abs($diffAmount ?? 0), 0, ',', '.') }}</p>
                        </div>

                        <div class="flex flex-col gap-1 border-t border-line-board/40 pt-2">
                            <p class="text-2xs font-bold uppercase leading-3 tracking-[0.45px] text-success-deep gpa-micro-bold">
                                TOTAL AKHIR NILAI TRANSAKSI
                            </p>
                            <p class="text-3xl leading-10 font-extrabold text-ink" x-text="finalValue()">Rp {{ number_format($finalAmount ?? 0, 0, ',', '.') }}</p>
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">
                                Menjadi dasar resmi pencetakan Faktur Tagihan dan Surat Jalan untuk {{ $clientName ?? 'Mitra B2B' }}.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1 rounded-lg bg-surface-shell p-2 outline outline-1 -outline-offset-1 outline-line-board/30">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">KODE VERIFIKASI:</p>
                            <p class="text-right text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink gpa-micro-bold">
                                VERIF-{{ substr(hash('sha256', ($orderCode ?? '') . ($actualNet ?? 0)), 0, 10) }}
                            </p>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">PETUGAS PENIMBANG:</p>
                            <p class="text-right text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink gpa-micro-bold">
                                {{ auth()->user()?->name ?? 'Koordinator Lapangan' }}
                            </p>
                        </div>
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-quiet">STATUS KEAMANAN:</p>
                            <p class="text-right text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink gpa-micro-bold">
                                Data Tercatat Resmi di Sistem
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Integritas Audit Forensik --}}
                <section class="flex flex-col gap-1 rounded-xl bg-surface p-4 shadow-card">
                    <div class="flex items-center gap-1">
                        <x-gpa.icon name="shield" class="h-[13px] w-[13px] shrink-0 text-success-deep" />
                        <p class="text-2xs font-bold uppercase leading-3 tracking-[1.08px] text-ink gpa-micro-bold">Catatan Keamanan &amp; Riwayat Timbangan</p>
                    </div>

                    <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-ink-body">Data berat timbangan ini tersimpan resmi di sistem. Setelah dikunci, data berat bersih akan menjadi acuan tetap untuk penerbitan Surat Jalan dan penagihan.</p>
                </section>
            </div>
        </div>

        {{-- Lock bar --}}
        <section class="relative flex flex-col justify-center gap-4 rounded-2xl bg-ink p-4 outline outline-1 -outline-offset-1 outline-success-deep/40">
            <div class="flex flex-wrap items-center gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent">
                    <x-gpa.icon name="lock" class="h-[22px] w-[22px] shrink-0 text-success-ink" />
                </span>

                <div class="min-w-0">
                    <p class="gpa-meta-lg font-bold text-white">
                        <span x-text="confirmTitle()">Konfirmasi Berat Bersih: {{ number_format($actualNet ?? 0, 2, ',', '.') }} KG</span>
                    </p>
                    <p class="text-2xs font-semibold uppercase leading-3 tracking-[1.08px] text-accent gpa-micro-bold">
                        <span x-show="! locked">Klik tombol di bawah untuk mengunci hasil timbangan dan mengizinkan pembuatan Surat Jalan.</span>
                        <span x-cloak x-show="locked">Data terkunci permanen. Surat Jalan siap diterbitkan.</span>
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <button type="button" @click="act('draft')" :disabled="locked"
                    class="rounded-lg px-12 py-2.5 text-center text-[11px] font-semibold uppercase leading-[14px] tracking-[0.88px] text-white outline outline-1 -outline-offset-1 outline-ink-quiet transition-colors hover:bg-white/5 disabled:cursor-not-allowed disabled:opacity-50">
                    SIMPAN SEMENTARA (DRAF)
                </button>

                <button type="button" @click="act('lock')" :disabled="locked"
                    class="inline-flex items-center gap-1 rounded-lg bg-accent px-6 py-2.5 shadow-pop transition-colors hover:bg-accent-deep disabled:cursor-not-allowed disabled:opacity-70">
                    <x-gpa.icon name="lock" class="h-3 w-3 shrink-0 text-ink" />
                    <span class="text-[11px] font-bold uppercase leading-[14px] tracking-[0.55px] text-ink" x-text="locked ? 'DATA TERKUNCI' : 'KUNCI & SIMPAN HASIL TIMBANGAN'">
                        KUNCI &amp; SIMPAN HASIL TIMBANGAN
                    </span>
                </button>

                <button type="button" @click="act('delivery')" :disabled="! locked"
                    :class="locked
                        ? 'bg-accent text-ink hover:bg-accent-deep'
                        : 'cursor-not-allowed bg-surface-disabled/20 text-ink-quiet outline outline-1 -outline-offset-1 outline-ink-quiet/30'"
                    class="inline-flex items-center gap-1 rounded-lg px-4 py-2.5 transition-colors">
                    <x-gpa.icon name="truck" class="h-4 w-3 shrink-0" />
                    <span class="text-[11px] font-medium uppercase leading-[14px] tracking-[0.88px]">TERBITKAN SURAT JALAN ({{ $sjNumber ?? '' }})</span>
                </button>
            </div>
        </section>
    </div>
@endsection
