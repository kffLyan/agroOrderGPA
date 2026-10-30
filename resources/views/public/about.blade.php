@extends('layouts.public')

@section('title', 'Tentang GPA | AgroOrder GPA')

@section('content')

    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-2/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="space-y-6 text-center md:text-left">

                <h1 class="mx-auto max-w-4xl text-center text-3xl font-extrabold leading-tight sm:mx-auto sm:text-3xl md:mx-0 md:text-left md:text-5xl md:leading-[1.05]">
                    <span class="block text-white">Membangun</span>
                    <span class="block text-accent underline underline-offset-4">Kepastian Pasokan</span>
                    <span class="block text-white">& Transparansi Radikal Agribisnis Indonesia</span>
                </h1>

                <p class="mx-auto max-w-3xl text-center text-sm leading-relaxed text-white/90 md:mx-0 md:text-left md:text-lg">
                    Latar belakang pendirian PT Green Pasundan Agriculture (AgroOrder GPA) sebagai solusi atas masalah struktural rantai pasok agribisnis: fluktuasi pasokan liar, susut timbangan diam-diam (<em class="text-white font-bold">phantom weight</em>), dan mutasi kualitas komoditas segar di sektor HORECA (Hotel, Restaurant, Catering) dan ritel modern.
                </p>

                <ul class="flex flex-wrap justify-center gap-2 md:justify-start">
                    <li class="inline-flex items-center gap-1.5 rounded-lg bg-brand-deep/80 px-3 py-1.5 outline outline-1 outline-accent/30 outline-offset-[-1px] gpa-mono-xs text-white/90">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        Standar Jaminan Berat Riil
                    </li>
                    <li class="inline-flex items-center gap-1.5 rounded-lg bg-brand-deep/80 px-3 py-1.5 outline outline-1 outline-accent/30 outline-offset-[-1px] gpa-mono-xs text-white/90">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        Timbangan Tera Legal Metrologi (UU No. 2/1981)
                    </li>
                    <li class="inline-flex items-center gap-1.5 rounded-lg bg-brand-deep/80 px-3 py-1.5 outline outline-1 outline-accent/30 outline-offset-[-1px] gpa-mono-xs text-white/90">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        Dawn-Run Subuh Logistics 03:00 - 05:30 WIB
                    </li>
                </ul>

                <div class="flex w-full max-w-sm flex-col gap-3 pt-2">
                    <x-gpa.btn href="#tentang-gpa" variant="accent" size="xl" block>
                        <x-slot:icon>
                            <x-gpa.icon name="navigation" />
                        </x-slot:icon>
                        Jelajahi 4 Pilar Operasional
                    </x-gpa.btn>
                    <x-gpa.btn href="#komoditas" variant="inverse" size="xl" block>
                        <x-slot:icon>
                            <x-gpa.icon name="clipboard" />
                        </x-slot:icon>
                        Lihat Spesifikasi Mutu
                    </x-gpa.btn>
                </div>

                {{-- PIPELINE COMPONENT --}}
                <div class="mt-8 rounded-2xl border border-white/15 bg-brand-deep/80 p-5 shadow-sub sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            Supply Pipeline Architecture
                        </p>
                        <span class="rounded bg-brand px-2 py-0.5 gpa-mono-xs font-bold text-accent">REV 2025.1</span>
                    </div>

                    <ol class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <li class="rounded-xl border border-white/10 bg-brand/70 p-3.5">
                            <span class="gpa-mono-xs font-bold text-accent">01</span>
                            <h3 class="mt-1 text-xs font-bold text-white">Sentra Kelompok Tani</h3>
                            <p class="mt-0.5 text-2xs text-white/70">Ciwidey, Lembang & Pangalengan</p>
                            <p class="mt-2 gpa-mono-xs text-accent">1.400 mdpl</p>
                        </li>
                        <li class="rounded-xl border border-white/10 bg-brand/70 p-3.5">
                            <span class="gpa-mono-xs font-bold text-accent">02</span>
                            <h3 class="mt-1 text-xs font-bold text-white">Central Hub & Tera Lab</h3>
                            <p class="mt-0.5 text-2xs text-white/70">Buffer Cold Storage 0° - 4°C</p>
                            <p class="mt-2 gpa-mono-xs text-accent">IoT Tera Sah</p>
                        </li>
                        <li class="rounded-xl border border-white/10 bg-brand/70 p-3.5">
                            <span class="gpa-mono-xs font-bold text-accent">03</span>
                            <h3 class="mt-1 text-xs font-bold text-white">Dawn-Run Reefer Fleet</h3>
                            <p class="mt-0.5 text-2xs text-white/70">03:00 - 05:30 WIB Subuh Dispatch</p>
                            <p class="mt-2 gpa-mono-xs text-accent">Reefer Truck</p>
                        </li>
                        <li class="rounded-xl border border-white/10 bg-brand/70 p-3.5">
                            <span class="gpa-mono-xs font-bold text-accent">04</span>
                            <h3 class="mt-1 text-xs font-bold text-white">Receiving Kitchen Dock</h3>
                            <p class="mt-0.5 text-2xs text-white/70">Bukti Serah Terima Digital Instan</p>
                            <p class="mt-2 gpa-mono-xs text-accent">Zero-Shrink</p>
                        </li>
                    </ol>

                    <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-3 gpa-mono-xs text-white/70">
                        <span>STATUS AUDIT: TERVERIFIKASI AMAN</span>
                        <span class="font-bold text-accent">100% DAPAT DILACAK</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS SECTION (DYNAMIC FROM DATABASE) --}}
    <section aria-label="Ringkasan kapasitas" class="border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 md:py-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Fokus Komoditas</dt>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ $totalProducts }} Komoditas</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Inti Standar Horeca Teruji</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Susut Berat Fiktif</dt>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">0% Toleransi</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Selisih Timbang Fiktif Dihapus</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Kapasitas Pasokan</dt>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ number_format($totalCapacity / 1000, 1) }} Ton</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Kapasitas Pasokan Terkelola</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Ketepatan Pengiriman</dt>
                        <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">99.6% Subuh</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Pasokan Dapur Komersial Selalu Tepat Waktu</p>
                </div>
            </dl>
        </div>
    </section>

    {{-- 4 PILAR OPERASIONAL --}}
    <section id="tentang-gpa" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col items-center gap-3 border-b-2 border-brand-strong pb-3 text-center">
                <div>
                    <h2 class="mt-1 text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Arsitektur 4 Pilar Operasional Hulu-ke-Hilir
                    </h2>
                </div>
            </div>

            <ol class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">01</span>
                            <span class="rounded bg-canvas px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-line-hair gpa-mono-xs font-semibold uppercase text-success">Hulu Produksi</span>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Kemitraan Petani Binaan Dataran Tinggi</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Ekosistem binaan di Ciwidey, Lembang, dan Pangalengan dengan kontrak off-take jaminan harga dasar, pendampingan GAP, dan audit residu berkala.</p>
                    </div>
                    <dl class="space-y-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Lokasi Kebun:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Bandung & Lembang</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Sistem Tanam:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">GAP Standardized</dd>
                        </div>
                    </dl>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">02</span>
                            <span class="rounded bg-canvas px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-line-hair gpa-mono-xs font-semibold uppercase text-success">Rantai Dingin</span>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Buffer Stock Mandiri & Kontrol Termal</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Kapasitas cold buffer di Sentral Hub Utama dengan regulasi suhu presisi 0°C hingga 4°C untuk menjaga kerenyahan jaringan selada.</p>
                    </div>
                    <dl class="space-y-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Kapasitas Hub:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Buffer 45 Ton</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Suhu Preserve:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">0.0°C - 4.0°C</dd>
                        </div>
                    </dl>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">03</span>
                            <span class="rounded bg-canvas px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-line-hair gpa-mono-xs font-semibold uppercase text-success">Tera Metrologi</span>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Stasiun Timbangan Tera Legal & Jaminan Berat Riil</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Penimbangan bersertifikat metrologi resmi Ditjen Metrologi RI dengan reduksi tara otomatis. Menghapus risiko selisih timbangan fiktif.</p>
                    </div>
                    <dl class="space-y-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Segel Tera:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Sah Ditjen Metrologi</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Toleransi Susut:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">&lt; 1.5% Netto</dd>
                        </div>
                    </dl>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">04</span>
                            <span class="rounded bg-canvas px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-line-hair gpa-mono-xs font-semibold uppercase text-success">Last-Mile Subuh</span>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Armada Pendingin &amp; Bukti Kirim Digital Aman</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Jadwal pengiriman subuh 03:00 - 05:30 WIB tepat sebelum shift operasional dapur dimulai. Dilengkapi validasi bukti serah terima digital dua sisi.</p>
                    </div>
                    <dl class="space-y-1.5 border-t border-line-hair pt-3">
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Jadwal Pengiriman:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">03:00 - 05:30 WIB</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3">
                            <dt class="gpa-mono-xs text-ink-subtle">Validasi:</dt>
                            <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Bukti Kirim Digital Sah</dd>
                        </div>
                    </dl>
                </li>
            </ol>
        </div>
    </section>

    {{-- SPESIFIKASI KOMODITAS INTI (DYNAMIC FROM DATABASE) --}}
    <section id="komoditas" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col items-center gap-3 border-b-2 border-brand-strong pb-3 text-center">
                <div>
                    <h2 class="text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Spesifikasi Komoditas Inti Standar GPA
                    </h2>
                    <p class="mx-auto mt-1 max-w-3xl text-center text-xs text-ink-body">
                        Tabel komparasi mutu & parameter ketat penerimaan bahan baku segar untuk standardisasi rantai pasok industri kuliner.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-line-hair bg-white shadow-sub">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-line-hair bg-surface-muted gpa-mono-xs font-bold text-ink-strong">
                        <tr>
                            <th class="p-3.5">Kode & Nama Komoditas</th>
                            <th class="p-3.5">Grade Mutu Fisik</th>
                            <th class="p-3.5">Min. Order (MOQ)</th>
                            <th class="p-3.5">Kontrol Cold Storage</th>
                            <th class="p-3.5">Harga Dasar Franco</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @foreach ($products as $product)
                            <tr class="transition hover:bg-canvas">
                                <td class="p-3.5">
                                    <p class="gpa-mono-xs font-bold text-brand-strong">{{ $product->sku }}</p>
                                    <p class="font-bold text-ink">{{ $product->name }}</p>
                                </td>
                                <td class="p-3.5">
                                    <span class="rounded bg-brand px-2 py-0.5 font-mono text-[10px] font-bold text-accent-deep">
                                        {{ $product->grade ?? 'Grade A' }}
                                    </span>
                                    <p class="mt-1 text-2xs text-ink-body">{{ $product->description }}</p>
                                </td>
                                <td class="p-3.5 gpa-mono-xs font-semibold text-ink-strong">
                                    {{ number_format($product->minimum_order, 0, ',', '.') }} {{ ucfirst($product->unit) }}
                                </td>
                                <td class="p-3.5 gpa-mono-xs text-brand-strong">
                                    2.0°C - 5.0°C<br>
                                    <span class="text-2xs text-ink-subtle">Pre-Cooled Chamber</span>
                                </td>
                                <td class="p-3.5 gpa-mono-xs font-bold text-brand-strong">
                                    Rp {{ number_format($product->base_price, 0, ',', '.') }} / {{ $product->unit }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col gap-2 px-1 gpa-mono-xs md:flex-row md:items-center md:justify-between">
                <p class="text-ink-body">* Seluruh timbangan diuji dan disegel oleh Dinas Standardisasi Metrologi Legal.</p>
                <p class="font-bold text-success">Toleransi Kerusakan Transit &lt; 0.5% (Garansi Penggantian)</p>
            </div>
        </div>
    </section>

    {{-- LEGALITAS & KEPATUHAN --}}
    <section id="legalitas" class="scroll-mt-36 border-y border-line-hair bg-[#FFFBEB]">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col items-center gap-2 border-b-2 border-brand-strong pb-3 text-center">
                <h2 class="text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Legalitas Badan Usaha, Sertifikasi & Kepatuhan Pajak
                </h2>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                <dl class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                    <div class="rounded-xl border border-line-hair bg-surface px-5 py-5">
                        <dt class="gpa-mono-xs font-bold uppercase tracking-wide text-success">NOMOR INDUK BERUSAHA (NIB)</dt>
                        <dd class="mt-2 text-lg font-bold text-brand-strong gpa-mono-xs">1908230048192</dd>
                        <dd class="mt-1.5 text-xs leading-relaxed text-ink-body">NIB resmi OSS RBA dengan izin operasional komoditas hortikultura & perdagangan besar agrikultur.</dd>
                    </div>

                    <div class="rounded-xl border border-line-hair bg-surface px-5 py-5">
                        <dt class="gpa-mono-xs font-bold uppercase tracking-wide text-success">KLASIFIKASI BAKU (KBLI)</dt>
                        <dd class="mt-2 text-lg font-bold text-brand-strong gpa-mono-xs">KBLI 46311 & 46312</dd>
                        <dd class="mt-1.5 text-xs leading-relaxed text-ink-body">Perdagangan Besar Buah-Buahan dan Perdagangan Besar Sayur-Sayuran untuk distribusi komersial.</dd>
                    </div>

                    <div class="rounded-xl border border-line-hair bg-surface px-5 py-5">
                        <dt class="gpa-mono-xs font-bold uppercase tracking-wide text-success">SERTIFIKASI TERA METROLOGI</dt>
                        <dd class="mt-2 text-lg font-bold text-brand-strong gpa-mono-xs">UU No. 2 Tahun 1981</dd>
                        <dd class="mt-1.5 text-xs leading-relaxed text-ink-body">Kalibrasi berkala timbangan digital berstempel resmi Balai Standardisasi Metrologi Legal Kemendag RI.</dd>
                    </div>

                    <div class="rounded-xl border border-line-hair bg-surface px-5 py-5">
                        <dt class="gpa-mono-xs font-bold uppercase tracking-wide text-success">KEPATUHAN PAJAK & FAKTUR</dt>
                        <dd class="mt-2 text-lg font-bold text-brand-strong gpa-mono-xs">PKP Terdaftar & e-Faktur</dd>
                        <dd class="mt-1.5 text-xs leading-relaxed text-ink-body">NPWP Badan resmi dengan penerbitan Faktur Pajak PPN elektronik audit-ready bagi mitra korporat.</dd>
                    </div>
                </dl>

                <div class="flex flex-col justify-between gap-6 rounded-xl border-2 border-brand-strong bg-surface-muted p-5 shadow-sub">
                    <div class="space-y-3">
                        <p class="flex items-center gap-2 gpa-mono-xs font-bold text-brand-strong">
                            <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                            Verifikasi Compliance Resmi
                        </p>
                        <h3 class="text-lg font-extrabold leading-snug text-brand-strong">
                            Paket Dokumen Due Diligence Vendor
                        </h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Kelengkapan NIB, Akta Pendirian, SK Menkumham, NPWP Badan, PKP, dan sertifikat tera siap diunduh untuk kebutuhan pendaftaran vendor korporasi.
                        </p>
                    </div>

                    <div>
                        <x-gpa.btn :href="route('public.kontak')" variant="inverse" size="md" block>
                            <x-gpa.icon name="download" class="h-4 w-4" />
                            Minta Berkas Legalitas
                        </x-gpa.btn>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ALUR SIKLUS 24 JAM --}}
    <section id="rantai-pasok" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col items-center gap-3 border-b-2 border-brand-strong pb-3 text-center">
                <div>
                    <h2 class="text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Alur Siklus Eksekusi Pasokan 24 Jam HORECA
                    </h2>
                    <p class="mt-1 text-xs text-ink-body">
                        SOP pergerakan komoditas dengan kepastian waktu dan temperatur terkontrol dari hulu ke receiving kitchen.
                    </p>
                </div>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">01</span>
                            <span class="gpa-mono-xs font-bold text-success">13:00 WIB</span>
                        </div>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Cut-Off PO & Alokasi Fisik</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Penguncian pesanan harian B2B dan verifikasi ketersediaan kuota fisik kebun binaan.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs font-semibold uppercase text-ink-body">GATEWAY 1: ORDER LOCK</p>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">02</span>
                            <span class="gpa-mono-xs font-bold text-success">15:00 - 18:00 WIB</span>
                        </div>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Intake, Sortir & Timbang Tera</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Penerimaan hasil panen di packhouse, sortasi grade mutu, dan penimbangan bersih berstempel tera.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs font-semibold uppercase text-ink-body">GATEWAY 2: NETTO TERA</p>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">03</span>
                            <span class="gpa-mono-xs font-bold text-success">03:00 - 05:30 WIB</span>
                        </div>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Dawn Line-Haul Dispatch</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Pengiriman subuh armada berpendingin menuju receiving dock hotel dan restoran.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs font-semibold uppercase text-ink-body">GATEWAY 3: SUBUH ROUTE</p>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">04</span>
                            <span class="gpa-mono-xs font-bold text-success">06:00 WIB</span>
                        </div>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Penerimaan Dok &amp; Bukti Kirim Digital</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Pemeriksaan fisik timbangan oleh staff receiving kitchen, penandatanganan bukti kirim digital, dan faktur sah.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs font-semibold uppercase text-ink-body">TAHAP 4: PENAGIHAN RESMI</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- CTA SECTION --}}
    <section id="cta" class="bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 pb-12 sm:px-6">
            <div class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="pointer-events-none absolute -right-24 top-1/4 h-72 w-72 rounded-full bg-accent/15 blur-3xl" aria-hidden="true"></div>

                <div class="relative mx-auto max-w-3xl space-y-4 text-center">
                    <h2 class="text-center text-2xl font-extrabold leading-tight md:text-3xl">Mulai Kemitraan Pasokan Berkualitas Bersama GPA</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">
                        Dapatkan jaminan berat riil 100%, stabilitas harga kontrak, dan ketepatan kirim subuh setiap hari untuk operasional komersial Anda.
                    </p>
                </div>

                <div class="relative mt-8 flex flex-col items-center gap-4 border-t border-white/10 pt-6 text-center">
                    <div class="flex w-full max-w-sm flex-col gap-3">
                        <x-gpa.btn :href="route('public.catalog')" variant="accent" size="xl" block>
                            Buka Katalog Komoditas
                        </x-gpa.btn>
                        <x-gpa.btn :href="route('public.mitra-kontrak')" variant="inverse" size="xl" block>
                            Skema Kemitraan Kontrak
                        </x-gpa.btn>
                    </div>

                    <p class="gpa-mono-xs text-center">
                        <span class="block font-semibold text-accent">Hotline Meja Pengadaan:</span>
                        <span class="mt-1 block font-bold text-white">+62 811-1223-3445 (Desk Sekretariat)</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

@endsection
