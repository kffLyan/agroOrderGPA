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
            <div class="mx-auto max-w-4xl space-y-6 text-center md:mx-0 md:text-left">

                <h1 class="text-center text-4xl font-extrabold leading-tight sm:text-4xl md:text-left md:text-5xl md:leading-[1.05]">
                    <span class="block text-white">Rantai Pasok Terintegrasi Hulu-ke-Hilir</span>
                    <span class="block text-accent-deep underline underline-offset-4">AgroOrder GPA</span>
                </h1>

                <p class="mx-auto max-w-3xl text-center text-base leading-relaxed text-white/90 md:mx-0 md:text-left md:text-lg">
                    Sistem logistik rantai dingin dan standardisasi panen yang menjamin kesegaran komoditas hortikultura dari lahan binaan di Ciwidey dan Lembang hingga tiba di kitchen klien komersial Anda.
                </p>

                <div class="flex w-full max-w-sm flex-col gap-3 pt-2">
                    <x-gpa.btn :href="route('public.catalog')" variant="accent" size="xl" block>
                        <x-slot:icon>
                            <x-gpa.icon name="navigation" />
                        </x-slot:icon>
                        Lihat Katalog
                    </x-gpa.btn>
                    <x-gpa.btn :href="route('register')" variant="inverse" size="xl" block>
                        <x-slot:icon>
                            <x-gpa.icon name="clipboard" />
                        </x-slot:icon>
                        Daftar Sebagai Klien B2B
                    </x-gpa.btn>
                </div>
            </div>
        </div>
    </section>
    
    {{-- DIAGRAM 6 TAHAP --}}
    <section id="alur-pasok" class="scroll-mt-36 border-y border-white/10 bg-[#102C01]">
        <div class="mx-auto w-full max-w-6xl space-y-8 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 pb-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="mt-2 text-2xl font-extrabold leading-tight text-white sm:text-3xl">
                        Alur Eksekusi Rantai Pasok Terintegrasi
                    </h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[#E5F0A5]">Tahapan hulu ke hilir yang dipantau real-time oleh sistem AgroOrder GPA.</p>
                </div>
            </div>

            <ol class="relative grid gap-6 lg:grid-cols-6 lg:gap-5">
                <li aria-hidden="true" class="absolute left-2 right-2 top-[7px] hidden h-px bg-white/70 lg:block"></li>
                <li class="relative pt-8">
                    <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                    <p class="font-mono text-sm font-bold leading-5 text-[#D9E000]">01</p>
                    <h3 class="mt-2 text-sm font-bold leading-5 text-white">Panen Pagi &amp; Sortasi Kebun</h3>
                    <p class="mt-1 text-xs leading-5 text-[#E5F0A5]">Petik terencana pada dini hari dan sortir awal di kebun untuk menjaga kesegaran.</p>
                </li>
                <li class="relative pt-8">
                    <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                    <p class="font-mono text-sm font-bold leading-5 text-[#D9E000]">02</p>
                    <h3 class="mt-2 text-sm font-bold leading-5 text-white">Pra-Pendinginan di Hub Konsolidasi</h3>
                    <p class="mt-1 text-xs leading-5 text-[#E5F0A5]">Suhu inti komoditas diturunkan menuju 4°C sebelum disimpan.</p>
                </li>
                <li class="relative pt-8">
                    <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                    <p class="font-mono text-sm font-bold leading-5 text-[#D9E000]">03</p>
                    <h3 class="mt-2 text-sm font-bold leading-5 text-white">Inspeksi QC Produk</h3>
                    <p class="mt-1 text-xs leading-5 text-[#E5F0A5]">Kesegaran, kematangan, ukuran, dan kebersihan diperiksa; produk yang tidak sesuai dipisahkan.</p>
                </li>
                <li class="relative pt-8">
                    <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                    <p class="font-mono text-sm font-bold leading-5 text-[#D9E000]">04</p>
                    <h3 class="mt-2 text-sm font-bold leading-5 text-white">Buffer Cold Storage &amp; Labeling QR</h3>
                    <p class="mt-1 text-xs leading-5 text-[#E5F0A5]">Krat berlabel QR disimpan dalam cold room dengan rotasi FIFO sebelum dimuat.</p>
                </li>
                <li class="relative pt-8">
                    <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                    <p class="font-mono text-sm font-bold leading-5 text-[#D9E000]">05</p>
                    <h3 class="mt-2 text-sm font-bold leading-5 text-white">Distribusi Armada Berpendingin (Reefer)</h3>
                    <p class="mt-1 text-xs leading-5 text-[#E5F0A5]">Armada berpendingin berangkat subuh dengan pemantauan suhu dan GPS.</p>
                </li>
                <li class="relative pt-8">
                    <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                    <p class="font-mono text-sm font-bold leading-5 text-[#D9E000]">06</p>
                    <h3 class="mt-2 text-sm font-bold leading-5 text-white">Serah Terima Dok &amp; Bukti Kirim Digital</h3>
                    <p class="mt-1 text-xs leading-5 text-[#E5F0A5]">Mutu diperiksa bersama klien dan serah terima dicatat melalui e-PoD.</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- METRICS SUMMARY SECTION --}}
    <section aria-label="Ringkasan kontrol rantai pasok" class="border-b border-line-hair bg-canvas">
        <div class="w-full py-6 md:py-8">
            <dl class="grid sm:grid-cols-2 lg:grid-cols-4">
                <div class="px-4 py-4 sm:px-6 sm:py-0">
                    <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Kapasitas Buffer Aktif</dt>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ number_format($totalStock, 0, ',', '.') }} kg</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Stok siap salur dari panen hulu</p>
                </div>

                <div class="border-t border-line-hair px-4 py-4 sm:border-l sm:border-t-0 sm:px-6 sm:py-0">
                    <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Varietas Komoditas</dt>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ $totalProducts }} Item</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Komoditas aktif dalam katalog</p>
                </div>

                <div class="border-t border-line-hair px-4 py-4 sm:px-6 sm:py-4 lg:border-l lg:border-t-0 lg:py-0">
                    <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Kontrol Suhu Reefer</dt>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">2°C – 4°C</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Suhu rantai dingin telemetri</p>
                </div>

                <div class="border-t border-line-hair px-4 py-4 sm:border-l sm:px-6 sm:py-4 lg:border-t-0 lg:py-0">
                    <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Tingkat Ketepatan Pengiriman</dt>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">99.8%</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Tingkat ketepatan volume &amp; waktu</p>
                </div>
            </dl>
        </div>
    </section>


    {{-- RINCIAN EKSEKUSI TAHAP --}}
    <section id="tahap-pasok" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col items-center gap-2 pb-3 text-center">
                <h2 class="text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Rincian Eksekusi Enam Tahap Hulu-ke-Hilir
                </h2>
                <span class="gpa-mono-xs text-center text-ink-subtle">STANDAR OPERASIONAL PROSEDUR (SOP)</span>
            </div>

            <ol class="grid auto-rows-fr items-stretch gap-4 xl:grid-cols-2">
                {{-- STAGE 01 --}}
                <li class="h-full">
                    <article class="flex h-full flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                            <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">01</span>
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
                <li class="h-full">
                    <article class="flex h-full flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                            <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">02</span>
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
                <li class="h-full">
                    <article class="flex h-full flex-col gap-4 rounded-xl border-2 border-accent bg-brand-deep p-5 text-white shadow-pop">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                            <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">03</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-accent">QC INSPECTION</p>
                        </div>
                        <p class="inline-flex w-fit items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-brand-deep">
                            <x-gpa.icon name="alert-triangle" class="h-3 w-3" />
                            Pemeriksaan Mutu
                        </p>
                        <h3 class="text-base font-bold leading-snug text-white">Inspeksi QC Produk</h3>
                        <p class="text-xs leading-relaxed text-white/85">
                            Pemeriksaan visual dan organoleptik untuk menilai kesegaran, kematangan, ukuran, kebersihan, serta kondisi produk sebelum dikemas.
                        </p>
                        <div class="rounded-lg border border-accent/30 bg-brand/85 px-3 py-2.5">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">STANDAR INSPEKSI QC</p>
                            <p class="mt-1 gpa-mono-xs text-white/75">Produk cacat, rusak, atau tidak sesuai spesifikasi dipisahkan sebelum proses berikutnya.</p>
                        </div>
                        <dl class="mt-auto space-y-1.5 border-t border-white/10 pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-white/60">Fokus Pemeriksaan:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-accent">Kesegaran &amp; Kondisi</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-white/60">Tindak Lanjut:</dt>
                                <dd class="gpa-mono-xs text-right font-semibold text-white">Sortasi Produk</dd>
                            </div>
                        </dl>
                    </article>
                </li>

                {{-- STAGE 04 --}}
                <li class="h-full">
                    <article class="flex h-full flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                            <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">04</span>
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
                <li class="h-full">
                    <article class="flex h-full flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                            <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">05</span>
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
                <li class="h-full">
                    <article class="flex h-full flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3">
                            <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">06</span>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wide text-success">BUKTI SERAH TERIMA DIGITAL</p>
                        </div>
                        <h3 class="text-base font-bold leading-snug text-brand-strong">Serah Terima Dok &amp; Bukti Kirim Digital</h3>
                        <p class="text-xs leading-relaxed text-ink-body">
                            Chef/Purchasing klien memeriksa mutu bersama petugas armada, menandatangani bukti terima digital (e-PoD), dan klaim retur parsial seketika jika ada temuan.
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
    <section id="retur-parsial" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col items-center gap-3 pb-3 text-center">
                <div>
                    <h2 class="mt-1 text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Protokol Retur Parsial &amp; Klaim Mutu Seketika
                    </h2>
                    <p class="mx-auto mt-1 max-w-3xl text-center text-xs text-ink-body">Perlindungan 100% risiko untuk klien Horeca saat penerimaan pesanan di loading dock.</p>
                </div>
            </div>

            <ol class="grid gap-4 lg:grid-cols-3">
                <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <div class="flex items-center gap-3 pb-3">
                        <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">01</span>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Uji Fisik Bersama di Loading Dock</h3>
                    </div>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Pemeriksaan acak terhadap kemasan bersama armada logistik GPA. Pemeriksaan mencakup kesegaran visual, ketiadaan memar, dan kesesuaian grade komoditas.
                    </p>
                </li>

                <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <div class="flex items-center gap-3 pb-3">
                        <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">02</span>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Catat Retur Parsial di Bukti Kirim Digital</h3>
                    </div>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Bila terdapat krat komoditas yang tidak memenuhi standar mutu, kuantitas tersebut langsung ditandai pada aplikasi armada tanpa harus menolak seluruh kiriman.
                    </p>
                </li>

                <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <div class="flex items-center gap-3 pb-3">
                        <span class="rounded px-2 py-2 font-mono text-[14px] font-bold text-success bg-[#EAF2D7]">03</span>
                        <h3 class="text-sm font-bold leading-snug text-brand-strong">Opsi Penggantian H+1 atau Nota Kredit</h3>
                    </div>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Klien bebas memilih komoditas pengganti yang dikirimkan pada jadwal subuh berikutnya, atau langsung pemotongan nominal tagihan faktur (credit note).
                    </p>
                </li>
            </ol>
        </div>
    </section>

    {{-- INFRASTRUKTUR SECTION --}}
    <section id="infrastruktur" class="scroll-mt-36 border-y border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 text-center sm:px-6 md:py-14">
            <div class="mx-auto max-w-2xl space-y-2">
                <h2 class="text-center text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Fasilitas &amp; Kapasitas Infrastruktur Kami
                </h2>
                <p class="text-center text-sm text-ink-body">Infrastruktur terpusat di Ciwidey dan armada logistik berpendingin yang menjamin ketepatan pasokan harian.</p>
            </div>

            <dl class="grid gap-4 text-left md:grid-cols-3">
                <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <dt class="flex items-center gap-3 pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAF2D7] text-success">
                            <x-gpa.icon name="leaf" class="h-4 w-4" />
                        </span>
                        <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Hub Konsolidasi Ciwidey</span>
                    </dt>
                    <dd class="text-xl font-extrabold leading-tight text-brand-strong">Kapasitas 15 Ton / Hari</dd>
                    <p class="text-xs leading-relaxed text-ink-body">Fasilitas pra-pendinginan, sortir mekanik, dan penimbangan berizin resmi dari dinas metrologi.</p>
                </div>

                <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <dt class="flex items-center gap-3 pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAF2D7] text-success">
                            <x-gpa.icon name="navigation" class="h-4 w-4" />
                        </span>
                        <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Armada Cold-Chain Reefer</span>
                    </dt>
                    <dd class="text-xl font-extrabold leading-tight text-brand-strong">Unit Mobil Box Pendingin</dd>
                    <p class="text-xs leading-relaxed text-ink-body">Pendingin terisolasi dengan kompresor thermo ganda untuk menjaga kesegaran selama perjalanan antarkota.</p>
                </div>

                <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                    <dt class="flex items-center gap-3 pb-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#EAF2D7] text-success">
                            <x-gpa.icon name="check-circle" class="h-4 w-4" />
                        </span>
                        <span class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Telemetri IoT Rantai Pasok</span>
                    </dt>
                    <dd class="text-xl font-extrabold leading-tight text-brand-strong">Sensor Suhu &amp; Lokasi Real-Time</dd>
                    <p class="text-xs leading-relaxed text-ink-body">Pencatatan riwayat suhu kargo otomatis setiap menit dan integrasi langsung ke platform AgroOrder GPA.</p>
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
                    <h2 class="text-center text-2xl font-extrabold leading-tight md:text-3xl">Ingin Menjamin Pasokan Sayur Segar untuk Dapur Usaha Anda?</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">
                        Bergabunglah dengan puluhan hotel, restoran, katering, dan supermarket yang mempercayakan pengadaan hortikultura kepada AgroOrder GPA.
                    </p>
                </div>

                <div class="relative mt-8 flex flex-col items-center gap-3 pt-6">
                    <div class="flex w-full max-w-sm flex-col gap-3">
                        <x-gpa.btn :href="route('register')" variant="accent" size="xl" block>
                            <x-slot:icon>
                                <x-gpa.icon name="send" />
                            </x-slot:icon>
                            Daftar Sebagai Klien B2B
                        </x-gpa.btn>
                        <x-gpa.btn :href="route('public.kontak')" variant="inverse" size="xl" block>
                            <x-slot:icon>
                                <x-gpa.icon name="navigation" />
                            </x-slot:icon>
                            Hubungi Tim Pasokan Kami
                        </x-gpa.btn>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
