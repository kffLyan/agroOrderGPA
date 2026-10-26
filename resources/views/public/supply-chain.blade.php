@extends('layouts.public')

@section('title', 'Rantai Pasok Terintegrasi | AgroOrder GPA')

@section('content')

    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="max-w-4xl space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-brand-deep px-3 py-1 font-mono text-[11px] font-bold text-accent">
                    COLD-CHAIN LOGISTICS & HARVEST INTEGRATION
                </span>

                <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                    <span class="block text-white">Rantai Pasok Terintegrasi Hulu-ke-Hilir</span>
                    <span class="block text-accent underline underline-offset-4">AgroOrder GPA</span>
                </h1>

                <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                    Sistem logistik rantai dingin dan standardisasi panen yang menjamin kesegaran komoditas hortikultura dari lahan binaan di Ciwidey dan Lembang hingga tiba di kitchen klien komersial Anda.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <x-gpa.btn :href="route('public.catalog')" variant="accent" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="navigation" />
                        </x-slot:icon>
                        Lihat Katalog Komoditas
                    </x-gpa.btn>
                    <x-gpa.btn :href="route('register')" variant="inverse" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="clipboard" />
                        </x-slot:icon>
                        Daftar Sebagai Klien B2B
                    </x-gpa.btn>
                </div>
            </div>

            {{-- BLUEPRINT OVERVIEW CARD --}}
            <section aria-labelledby="blueprint-heading"
                class="mt-8 space-y-4 rounded-2xl border-2 border-accent/50 bg-brand-deep/90 p-5 shadow-pop md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 pb-3">
                    <h2 id="blueprint-heading"
                        class="flex items-center gap-2 gpa-mono-xs font-bold uppercase tracking-wider text-white/90">
                        <span class="h-2.5 w-2.5 rounded-full bg-accent" aria-hidden="true"></span>
                        BLUEPRINT ARSITEKTUR RANTAI PASOK DINGIN
                    </h2>
                    <span class="rounded bg-brand px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-accent/30 gpa-mono-xs text-accent">
                        GPA-SCM-REV2
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-3 rounded-xl border border-accent/25 bg-brand/85 px-3 py-3">
                    <p class="gpa-mono-xs text-white/80">Kebun Mitra Ciwidey &amp; Lembang (1.200 - 1.400 mdpl)</p>
                    <span class="text-accent" aria-hidden="true">&rarr;</span>
                    <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">Gudang Konsolidasi &amp; Armada Reefer B2B</p>
                </div>

                <dl class="grid gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-brand/85 px-3 py-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/15 text-accent">
                            <x-gpa.icon name="leaf" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <dt class="gpa-micro text-white/60">Stok Siap Salur Real-Time</dt>
                            <dd class="mt-1 text-lg font-extrabold leading-tight text-white">{{ number_format($totalStock, 0, ',', '.') }} kg</dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-brand/85 px-3 py-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-accent/15 text-accent">
                            <x-gpa.icon name="check-circle" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <dt class="gpa-micro text-white/60">Total Batch &amp; Komoditas Terverifikasi</dt>
                            <dd class="mt-1 text-lg font-extrabold leading-tight text-white">{{ $totalBatches }} Batch / {{ $totalProducts }} Komoditas</dd>
                        </div>
                    </div>
                </dl>

                <dl class="grid gap-3 border-t border-white/10 pt-4 sm:grid-cols-3">
                    <div class="space-y-0.5">
                        <dt class="gpa-micro text-white/55">Protokol Suhu</dt>
                        <dd class="gpa-mono-xs font-semibold text-white">2°C – 4°C Terjaga</dd>
                    </div>
                    <div class="space-y-0.5">
                        <dt class="gpa-micro text-white/55">Target Waktu Kirim</dt>
                        <dd class="gpa-mono-xs font-semibold text-white">Subuh 04:00 – 06:30 WIB</dd>
                    </div>
                    <div class="space-y-0.5">
                        <dt class="gpa-micro text-white/55">Toleransi Susut Dock</dt>
                        <dd class="gpa-mono-xs font-semibold text-white">&lt; 1% (Tera Sah)</dd>
                    </div>
                </dl>

                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-accent/25 bg-brand px-3 py-2 gpa-mono-xs">
                    <p class="text-white/70">Status Monitoring Cold-Chain</p>
                    <p class="font-bold text-accent">AKTIF &amp; TERHUBUNG TELEMETRI IOT</p>
                </div>
            </section>
        </div>
    </section>

    {{-- METRICS SUMMARY SECTION --}}
    <section aria-label="Ringkasan kontrol rantai pasok" class="border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 md:py-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Kapasitas Buffer Aktif</dt>
                        <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ number_format($totalStock, 0, ',', '.') }} kg</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Stok siap salur dari panen hulu</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Varietas Komoditas</dt>
                        <x-gpa.icon name="badge-check" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ $totalProducts }} Item</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Komoditas aktif dalam katalog</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Kontrol Suhu Reefer</dt>
                        <x-gpa.icon name="check-circle" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">2°C – 4°C</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Suhu rantai dingin telemetri</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Fulfillment SLA</dt>
                        <x-gpa.icon name="clipboard" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">99.8%</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Tingkat ketepatan volume &amp; waktu</p>
                </div>
            </dl>
        </div>
    </section>

    {{-- DIAGRAM 6 TAHAP --}}
    <section id="alur-pasok" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Diagram Alur Eksekusi Rantai Pasok Terintegrasi
                    </h2>
                    <p class="mt-1 text-xs text-ink-body">Tahapan hulu ke hilir yang dipantau real-time oleh sistem AgroOrder GPA.</p>
                </div>

                <span class="inline-flex w-fit shrink-0 items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                    6 Tahap Terverifikasi Sistem
                </span>
            </div>

            <ol class="flex flex-col gap-2 lg:flex-row lg:items-stretch lg:gap-1.5">
                <li class="flex flex-1 items-center gap-3 rounded-xl border border-brand-strong bg-brand-strong text-white shadow-sub px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold bg-accent text-brand-deep">01</span>
                    <span class="text-xs font-bold leading-snug text-white">Petik &amp; Sortasi Kebun</span>
                </li>
                <li class="hidden items-center justify-center text-ink-subtle lg:flex" aria-hidden="true">
                    <x-gpa.icon name="chevron-right" class="h-4 w-4" />
                </li>
                <li class="flex flex-1 items-center gap-3 rounded-xl border border-line-hair bg-surface px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold bg-surface-muted text-brand-strong">02</span>
                    <span class="text-xs font-bold leading-snug text-brand-strong">Pre-Cooling Hub</span>
                </li>
                <li class="hidden items-center justify-center text-ink-subtle lg:flex" aria-hidden="true">
                    <x-gpa.icon name="chevron-right" class="h-4 w-4" />
                </li>
                <li class="flex flex-1 items-center gap-3 rounded-xl border border-line-hair bg-surface px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold bg-surface-muted text-brand-strong">03</span>
                    <span class="text-xs font-bold leading-snug text-brand-strong">QC &amp; Tera Timbang</span>
                </li>
                <li class="hidden items-center justify-center text-ink-subtle lg:flex" aria-hidden="true">
                    <x-gpa.icon name="chevron-right" class="h-4 w-4" />
                </li>
                <li class="flex flex-1 items-center gap-3 rounded-xl border border-line-hair bg-surface px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold bg-surface-muted text-brand-strong">04</span>
                    <span class="text-xs font-bold leading-snug text-brand-strong">Buffer Cold Storage</span>
                </li>
                <li class="hidden items-center justify-center text-ink-subtle lg:flex" aria-hidden="true">
                    <x-gpa.icon name="chevron-right" class="h-4 w-4" />
                </li>
                <li class="flex flex-1 items-center gap-3 rounded-xl border border-line-hair bg-surface px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold bg-surface-muted text-brand-strong">05</span>
                    <span class="text-xs font-bold leading-snug text-brand-strong">Distribusi Reefer</span>
                </li>
                <li class="hidden items-center justify-center text-ink-subtle lg:flex" aria-hidden="true">
                    <x-gpa.icon name="chevron-right" class="h-4 w-4" />
                </li>
                <li class="flex flex-1 items-center gap-3 rounded-xl border border-line-hair bg-surface px-3 py-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg gpa-mono-xs font-bold bg-surface-muted text-brand-strong">06</span>
                    <span class="text-xs font-bold leading-snug text-brand-strong">e-PoD &amp; Docking Klien</span>
                </li>
            </ol>
        </div>
    </section>

    {{-- RINCIAN EKSEKUSI TAHAP --}}
    <section id="tahap-pasok" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-2 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Rincian Eksekusi Enam Tahap Hulu-ke-Hilir
                </h2>
                <span class="gpa-mono-xs text-ink-subtle">STANDAR OPERASIONAL PROSEDUR (SOP)</span>
            </div>

            <ol class="grid gap-4 xl:grid-cols-2">
                {{-- STAGE 01 --}}
                <li>
                    <article class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase bg-brand-strong text-accent">01</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-success">HARVEST &amp; SORT 05:00 WIB</p>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Panen Pagi &amp; Sortasi Kebun</h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Petik dilakukan pada dini hari sebelum sinar matahari terik guna mencegah penguapan sel tanaman. Petani mitra memisahkan daun rusak di kebun.
                        </p>
                        <div class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand">STANDAR GAP (GOOD AGRICULTURAL PRACTICES)</p>
                            <p class="mt-1 gpa-mono-xs text-ink-body">Waktu petik terkontrol, higienitas keranjang panen bebas kontak tanah.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Lokasi Hulu:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Kebun Binaan Ciwidey &amp; Lembang</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Target Waktu:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">04:30 – 07:00 WIB</dd>
                            </div>
                        </dl>
                    </article>
                </li>

                {{-- STAGE 02 --}}
                <li>
                    <article class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase bg-brand-strong text-accent">02</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-success">PRE-COOLING BLAST</p>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Pra-Pendinginan di Hub Konsolidasi</h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Komoditas langsung masuk ruang pre-cooling untuk menurunkan suhu inti tanaman dari suhu ladang (20°C) ke suhu stabil 4°C dalam tempo di bawah 90 menit.
                        </p>
                        <div class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand">HEAT REMOVAL RAPID PROCESS</p>
                            <p class="mt-1 gpa-mono-xs text-ink-body">Mencegah respirasi cepat pascapanen agar kerenyahan sayur bertahan hingga 7 hari.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Suhu Sasaran:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">4°C (±1°C)</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Fasilitas:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Cold Hub Ciwidey &amp; Lembang</dd>
                            </div>
                        </dl>
                    </article>
                </li>

                {{-- STAGE 03 (CRITICAL) --}}
                <li>
                    <article class="flex flex-col gap-4 rounded-xl border-2 border-accent bg-brand-deep text-white shadow-pop p-5">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase bg-accent text-brand-deep">03</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-accent">CRITICAL GATE // TERA SAH</p>
                        </div>
                        <p class="inline-flex w-fit items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-brand-deep">
                            <x-gpa.icon name="alert-triangle" class="h-3 w-3" />
                            Critical Checkpoint Mutu
                        </p>
                        <h3 class="text-base font-bold leading-snug text-white">Inspeksi QC &amp; Tera Timbangan Digital</h3>
                        <p class="text-xs leading-relaxed text-white/85">
                            Pemeriksaan organoleptik, uji kadar air, ukuran lingkar, bebas ulat/hama, dan penimbangan dengan alat tera legal bersertifikasi metrologi.
                        </p>
                        <div class="rounded-lg border border-accent/30 bg-brand/85 px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">SERTIFIKASI TERA METROLOGI</p>
                            <p class="mt-1 gpa-mono-xs text-white/75">Toleransi susut 0%. Faktur ditagihkan hanya berdasarkan bobot riil saat timbang.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-white/10 pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-white/60">Toleransi Cacat:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-accent">&le; 2% Maksimal</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-white/60">Status Tera:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-white">Segel Metrologi Valid 2026</dd>
                            </div>
                        </dl>
                    </article>
                </li>

                {{-- STAGE 04 --}}
                <li>
                    <article class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase bg-brand-strong text-accent">04</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-success">FIFO BUFFER LOT STORAGE</p>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Buffer Cold Storage &amp; Labeling QR</h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Krat sayuran diberi label QR batch dan disimpan dalam cold room bertemperatur presisi dengan rotasi FIFO (First-In, First-Out) sebelum jadwal muat.
                        </p>
                        <div class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand">BATCH TRACEABILITY QR</p>
                            <p class="mt-1 gpa-mono-xs text-ink-body">Setiap krat dapat dilacak nama petani asal, tanggal petik, dan riwayat suhunya.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Kapasitas Buffer:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">{{ number_format($totalStock, 0, ',', '.') }} kg</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Rotasi Stok:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-success">Maksimal 24 Jam FIFO</dd>
                            </div>
                        </dl>
                    </article>
                </li>

                {{-- STAGE 05 --}}
                <li>
                    <article class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase bg-brand-strong text-accent">05</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-success">FLEET DISPATCH 03:00 WIB</p>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Distribusi Armada Berpendingin (Reefer)</h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Armada mobil berinsulasi pendingin berangkat subuh melintasi jalur logistik Bandung - Jabodetabek, dilengkapi GPS telemetri suhu kabin kargo.
                        </p>
                        <div class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand">MONITORING IOT WAKTU NYATA</p>
                            <p class="mt-1 gpa-mono-xs text-ink-body">Peringatan otomatis berbunyi jika suhu kabin melewati ambang batas 5°C.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Jadwal Keberangkatan:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">02:30 – 03:30 WIB</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Estimasi Tiba:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-success">Sebelum 06:30 WIB di Kitchen</dd>
                            </div>
                        </dl>
                    </article>
                </li>

                {{-- STAGE 06 --}}
                <li>
                    <article class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg gpa-mono-xs font-bold uppercase bg-brand-strong text-accent">06</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-success">DIGITAL E-POD RECEIPT</p>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Serah Terima Dock &amp; e-PoD Klien</h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Chef/Purchasing klien memeriksa mutu bersama driver, menandatangani bukti terima digital (e-PoD), dan klaim retur parsial seketika jika ada temuan.
                        </p>
                        <div class="rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand">BERITA ACARA DIGITAL RESMI</p>
                            <p class="mt-1 gpa-mono-xs text-ink-body">Faktur terintegrasi langsung dengan kuantitas yang disetujui di dock penerimaan.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Metode Timbang:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">Gross minus Tare (Berat Bersih)</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Klaim Retur:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-success">Langsung di Tempat</dd>
                            </div>
                        </dl>
                    </article>
                </li>
            </ol>
        </div>
    </section>

    {{-- KONTROL MUTU & RETUR PARSIAL --}}
    <section id="retur-parsial" class="scroll-mt-36 border-t border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Protokol Retur Parsial &amp; Klaim Mutu Seketika
                    </h2>
                    <p class="mt-1 text-xs text-ink-body">Perlindungan 100% risiko untuk klien Horeca saat penerimaan pesanan di loading dock.</p>
                </div>
            </div>

            <ol class="grid gap-4 lg:grid-cols-3">
                <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <div class="flex items-center gap-3 border-b border-line-hair pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">01</span>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Uji Fisik Bersama di Loading Dock</h3>
                    </div>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Pemeriksaan acak terhadap kemasan bersama driver logistik GPA. Pemeriksaan mencakup kesegaran visual, ketiadaan memar, dan kesesuaian grade komoditas.
                    </p>
                </li>

                <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <div class="flex items-center gap-3 border-b border-line-hair pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">02</span>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Input Retur Parsial Langsung di e-PoD</h3>
                    </div>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Bila terdapat krat komoditas yang tidak memenuhi standar mutu, kuantitas tersebut langsung ditandai pada aplikasi driver tanpa harus menolak seluruh kiriman.
                    </p>
                </li>

                <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <div class="flex items-center gap-3 border-b border-line-hair pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">03</span>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Opsi Penggantian H+1 atau Nota Kredit</h3>
                    </div>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Klien bebas memilih komoditas pengganti yang dikirimkan pada jadwal subuh berikutnya, atau langsung pemotongan nominal tagihan faktur (credit note).
                    </p>
                </li>
            </ol>

            <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface-muted p-5 shadow-sub md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand-strong">Jaminan Mutu Tanpa Sengketa</p>
                    <p class="text-xs leading-relaxed text-ink-body">Seluruh penyesuaian dicatat digital dan diverifikasi secara transparan pada dashboard klien.</p>
                </div>
                <p class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-brand-strong px-3 py-1 gpa-mono-xs font-bold text-accent">
                    <x-gpa.icon name="badge-check" class="h-3.5 w-3.5" />
                    SOP ZERO DISPUTE POLICY
                </p>
            </div>
        </div>
    </section>

    {{-- INFRASTRUKTUR SECTION --}}
    <section id="infrastruktur" class="scroll-mt-36 border-y border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 text-center sm:px-6 md:py-14">
            <div class="mx-auto max-w-2xl space-y-2">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Fasilitas &amp; Kapasitas Infrastruktur Kami
                </h2>
                <p class="text-sm text-ink-body">Infrastruktur terpusat di Ciwidey dan armada logistik berpendingin yang menjamin ketepatan pasokan harian.</p>
            </div>

            <dl class="grid gap-4 text-left md:grid-cols-3">
                <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <dt class="flex items-center gap-3 border-b border-line-hair pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong text-accent">
                            <x-gpa.icon name="leaf" class="h-4 w-4" />
                        </span>
                        <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Hub Konsolidasi Ciwidey</span>
                    </dt>
                    <dd class="text-xl font-extrabold leading-tight text-brand-strong">Kapasitas 15 Ton / Hari</dd>
                    <p class="text-xs leading-relaxed text-ink-body">Fasilitas pra-pendinginan, sortir mekanik, dan penimbangan berizin resmi dari dinas metrologi.</p>
                    <p class="mt-auto pt-2 text-2xs font-medium text-ink-subtle">Lokasi: Ciwidey, Kab. Bandung (1.300 mdpl)</p>
                </div>

                <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <dt class="flex items-center gap-3 border-b border-line-hair pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong text-accent">
                            <x-gpa.icon name="navigation" class="h-4 w-4" />
                        </span>
                        <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Armada Cold-Chain Reefer</span>
                    </dt>
                    <dd class="text-xl font-extrabold leading-tight text-brand-strong">Unit Mobil Box Pendingin</dd>
                    <p class="text-xs leading-relaxed text-ink-body">Pendingin terisolasi dengan kompresor thermo ganda untuk menjaga kesegaran selama perjalanan antarkota.</p>
                    <p class="mt-auto pt-2 text-2xs font-medium text-ink-subtle">Jadwal: Keberangkatan subuh tiap hari</p>
                </div>

                <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <dt class="flex items-center gap-3 border-b border-line-hair pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong text-accent">
                            <x-gpa.icon name="check-circle" class="h-4 w-4" />
                        </span>
                        <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Telemetri IoT Rantai Pasok</span>
                    </dt>
                    <dd class="text-xl font-extrabold leading-tight text-brand-strong">Sensor Suhu &amp; Lokasi Real-Time</dd>
                    <p class="text-xs leading-relaxed text-ink-body">Pencatatan riwayat suhu kargo otomatis setiap menit dan integrasi langsung ke platform AgroOrder GPA.</p>
                    <p class="mt-auto pt-2 text-2xs font-medium text-ink-subtle">Platform: Cloud IoT Monitoring 24/7</p>
                </div>
            </dl>
        </div>
    </section>

    {{-- CTA SECTION --}}
    <section id="cta" class="bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="pointer-events-none absolute -right-24 top-1/4 h-72 w-72 rounded-full bg-accent/15 blur-3xl"
                    aria-hidden="true"></div>

                <div class="relative mx-auto max-w-3xl space-y-4 text-center">
                    <h2 class="text-2xl font-extrabold leading-tight md:text-3xl">Ingin Menjamin Pasokan Sayur Segar untuk Dapur Usaha Anda?</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">
                        Bergabunglah dengan puluhan hotel, restoran, katering, dan supermarket yang mempercayakan pengadaan hortikultura kepada AgroOrder GPA.
                    </p>
                </div>

                <div class="relative mt-8 flex flex-wrap items-center justify-center gap-3 border-t border-white/10 pt-6">
                    <x-gpa.btn :href="route('register')" variant="accent" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="send" />
                        </x-slot:icon>
                        Daftar Sebagai Klien B2B
                    </x-gpa.btn>
                    <x-gpa.btn :href="route('public.kontak')" variant="inverse" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="navigation" />
                        </x-slot:icon>
                        Hubungi Tim Pasokan Kami
                    </x-gpa.btn>
                </div>
            </div>
        </div>
    </section>

@endsection
