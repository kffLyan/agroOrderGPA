@extends('layouts.public')

@section('title', 'Hubungi Kami | AgroOrder GPA')

@section('content')

    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_384px] lg:items-start">
                <div class="flex flex-col items-start gap-6">
                    <span class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-brand-deep px-3 py-1 font-mono text-[11px] font-bold text-accent">
                        COMMERCIAL PROCUREMENT &amp; PARTNERSHIP
                    </span>

                    <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                        <span class="block text-white">Konsultasi Pasokan Komersial &amp; Kemitraan</span>
                        <span class="block text-accent underline underline-offset-4">AgroOrder GPA</span>
                    </h1>

                    <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                        Diskusikan kebutuhan hortikultura berkala dapur usaha Anda bersama tim procurement kami. Kami siap menyusun jadwal kirim, uji sampel mutu, dan penetapan harga kontrak flat kuartalan.
                    </p>
                </div>

                <aside aria-label="Komitmen waktu tanggap" class="rounded-2xl border border-white/15 bg-brand-deep/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            SLA RESPON CEPAT
                        </p>
                        <span class="flex items-center gap-1.5 gpa-mono-xs font-bold uppercase tracking-wider text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                            AKTIF 24 JAM
                        </span>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-white/80">
                        Permohonan draf PKS, permintaan sampel, atau pengajuan jadwal kirim dijawab maksimal <strong class="font-bold text-accent">2 jam kerja</strong> oleh Account Manager B2B kami.
                    </p>
                </aside>
            </div>
        </div>
    </section>

    {{-- DIREKTORI & FORMULIR --}}
    <section id="direktori" class="scroll-mt-36 border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-14">
            <div class="grid gap-6 lg:grid-cols-2 lg:items-start">

                {{-- KOLOM KIRI: DIREKTORI KANTOR & HUB --}}
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                        <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                            Direktori Kantor &amp; Hub Operasional
                        </h2>
                    </div>

                    {{-- CARD 1: KANTOR PUSAT & GUDANG KONSOLIDASI --}}
                    <article class="rounded-2xl border border-line-hair bg-white p-5 shadow-sub md:p-6">
                        <header class="flex items-start justify-between gap-3 border-b border-line-hair pb-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canvas">
                                    <x-gpa.icon name="building" class="h-5 w-5 text-success" />
                                </span>
                                <h3 class="text-base font-bold leading-snug text-brand-strong">
                                    Hub Konsolidasi &amp; Gudang Ciwidey
                                </h3>
                            </div>
                        </header>

                        <div class="mt-4 flex flex-col gap-3">
                            <div class="space-y-1">
                                <p class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">Alamat Fasilitas:</p>
                                <p class="text-sm leading-relaxed text-[#2C3527]">
                                    Jl. Raya Ciwidey - Patengan KM 4, Panundaan, Kec. Ciwidey, Kabupaten Bandung, Jawa Barat 40973
                                </p>
                            </div>
                            <div class="border-t border-line-hair pt-3 space-y-1">
                                <p class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">Fasilitas Utama:</p>
                                <p class="text-xs leading-relaxed text-ink-body">
                                    Stasiun Timbang Digital Bersertifikasi Tera, Ruang Pra-Pendinginan (Pre-Cooling 4°C), Cold Storage Buffer Kapasitas 15 Ton, dan Loading Dock Armada Box Pendingin.
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-line-hair bg-[#EFF4DC] p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">Operasional Penerimaan Petik</p>
                                <span class="rounded-full bg-success px-2 py-0.5 gpa-mono-xs font-bold text-white">SETIAP HARI</span>
                            </div>
                            <p class="mt-1.5 font-mono text-lg font-bold text-brand-strong">04:30 – 11:00 WIB</p>
                            <p class="mt-2 border-t border-line-hair pt-2 text-xs leading-relaxed text-ink-body">
                                Penerimaan panen segar harian dari kelompok tani binaan kawasan Ciwidey &amp; Lembang.
                            </p>
                        </div>
                    </article>

                    {{-- CARD 2: SALURAN KOMUNIKASI --}}
                    <article class="rounded-2xl border border-line-hair bg-white p-5 shadow-sub md:p-6">
                        <header class="flex items-center gap-3 border-b border-line-hair pb-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canvas">
                                <x-gpa.icon name="phone" class="h-5 w-5 text-success" />
                            </span>
                            <h3 class="text-base font-bold leading-snug text-brand-strong">
                                Saluran Komunikasi Resmi
                            </h3>
                        </header>

                        <ul class="mt-4 flex flex-col gap-2.5">
                            <li class="flex flex-col gap-1 rounded-lg border border-line-hair bg-white/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <div>
                                    <p class="text-sm font-semibold leading-5 text-brand-strong">WhatsApp Hotline B2B</p>
                                    <p class="gpa-mono-xs mt-0.5 text-ink-subtle">Layanan Konsultasi &amp; Info Draf Kontrak</p>
                                </div>
                                <p class="font-mono text-xs font-bold text-success sm:text-right">+62 821-2345-6789</p>
                            </li>
                            <li class="flex flex-col gap-1 rounded-lg border border-line-hair bg-white/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <div>
                                    <p class="text-sm font-semibold leading-5 text-brand-strong">Email Pengadaan &amp; Tender</p>
                                    <p class="gpa-mono-xs mt-0.5 text-ink-subtle">Permohonan Penawaran &amp; Dokumen Legalitas</p>
                                </div>
                                <p class="font-mono text-xs font-bold text-success sm:text-right">procurement@agropastiada.co.id</p>
                            </li>
                            <li class="flex flex-col gap-1 rounded-lg border border-line-hair bg-white/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <div>
                                    <p class="text-sm font-semibold leading-5 text-brand-strong">Helpdesk Dispatch Logistik</p>
                                    <p class="gpa-mono-xs mt-0.5 text-ink-subtle">Monitoring Armada Pengiriman Subuh</p>
                                </div>
                                <p class="font-mono text-xs font-bold text-success sm:text-right">+62 811-9876-5432</p>
                            </li>
                        </ul>
                    </article>
                </div>

                {{-- KOLOM KANAN: FORMULIR KONSULTASI B2B --}}
                <div id="formulir" class="scroll-mt-36 flex flex-col">
                    <div class="flex w-full flex-col gap-6 rounded-2xl border-2 border-success/80 bg-white p-6 shadow-sub md:p-8">
                        <div class="space-y-2 border-b border-line-hair pb-5">
                            <span class="rounded-full bg-success-soft px-2.5 py-0.5 gpa-micro-bold text-success-deep">
                                FORMULIR PERMOHONAN RESMI
                            </span>
                            <h2 class="text-lg font-extrabold leading-snug text-brand-strong md:text-xl">
                                Ajukan Permohonan Pasokan &amp; Kontrak
                            </h2>
                            <p class="text-sm leading-relaxed text-ink-body">
                                Isi formulir di bawah ini untuk memulai pengkajian kebutuhan pasokan komoditas segar bagi entitas bisnis Anda.
                            </p>
                        </div>

                        <form class="flex flex-col gap-5"
                            x-on:submit.prevent="$dispatch('gpa:toast', { title: 'Permohonan Kemitraan Terkirim', message: 'Tim representatif GPA akan menghubungi Anda dalam waktu maksimal 2 jam kerja.', tone: 'success' })">
                            
                            <div class="flex flex-col gap-4 sm:flex-row">
                                <div class="sm:flex-1">
                                    <label for="kontak-pic" class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                        Nama Penanggung Jawab (PIC) <span class="text-success">*</span>
                                    </label>
                                    <input id="kontak-pic" name="pic" type="text" required placeholder="Contoh: Chef Hendra / Bpk. Rudi"
                                        class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                                </div>

                                <div class="sm:flex-1">
                                    <label for="kontak-company" class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                        Nama Perusahaan / Unit Usaha <span class="text-success">*</span>
                                    </label>
                                    <input id="kontak-company" name="company" type="text" required placeholder="Contoh: Grand Hotel Bandung"
                                        class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                                </div>
                            </div>

                            <div class="flex flex-col gap-4 sm:flex-row">
                                <div class="sm:flex-1">
                                    <label for="kontak-phone" class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                        Nomor Telepon / WhatsApp <span class="text-success">*</span>
                                    </label>
                                    <input id="kontak-phone" name="phone" type="tel" required placeholder="Contoh: 08123456789"
                                        class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                                </div>

                                <div class="sm:flex-1">
                                    <label for="kontak-email" class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                        Alamat Email Resmi <span class="text-success">*</span>
                                    </label>
                                    <input id="kontak-email" name="email" type="email" required placeholder="Contoh: purchasing@hotel.com"
                                        class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                                </div>
                            </div>

                            <div>
                                <label for="kontak-type" class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                    Kategori Entitas Bisnis <span class="text-success">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <select id="kontak-type" name="business_type" required
                                        class="w-full appearance-none rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 pr-10 text-sm text-ink focus:border-success focus:outline-none">
                                        <option value="" selected disabled>Pilih Jenis Usaha Anda</option>
                                        <option value="hotel">Hotel Bintang &amp; Resort</option>
                                        <option value="resto">Restoran, Cafe &amp; Jaringan F&amp;B</option>
                                        <option value="katering">Katering Korporat / Rumah Sakit</option>
                                        <option value="supermarket">Supermarket &amp; Retail Modern</option>
                                        <option value="industri">Industri Pengolahan Makanan</option>
                                    </select>
                                    <x-gpa.icon name="chevron-down" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
                                </div>
                            </div>

                            {{-- KOMODITAS DINAMIS DARI DATABASE --}}
                            <fieldset>
                                <legend class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                    Komoditas yang Diminati
                                </legend>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($products as $product)
                                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-line-hair bg-canvas/50 px-3 py-2 text-sm text-ink-body hover:bg-canvas">
                                            <input type="checkbox" name="komoditas[]" value="{{ $product->name }}"
                                                class="h-4 w-4 rounded border-line-hair accent-success" />
                                            {{ $product->name }}
                                        </label>
                                    @endforeach
                                </div>

                                <label for="kontak-volume" class="gpa-mono-xs mt-4 block font-bold uppercase tracking-wider text-[#5E6953]">
                                    Estimasi Kebutuhan Rutin (kg / Siklus Kirim)
                                </label>
                                <input id="kontak-volume" name="volume" type="text" placeholder="Contoh: 300 kg / minggu"
                                    class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                            </fieldset>

                            {{-- PREFERENSI PEMBAYARAN --}}
                            <fieldset>
                                <legend class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                    Preferensi Termin Pembayaran (TOP)
                                </legend>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-line-hair bg-canvas/50 px-3 py-2 text-sm text-ink-body">
                                        <input type="radio" name="pembayaran" value="TOP 14 Hari" checked class="h-4 w-4 border-line-hair accent-success" />
                                        Tempo 14 Hari (TOP 14)
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-line-hair bg-canvas/50 px-3 py-2 text-sm text-ink-body">
                                        <input type="radio" name="pembayaran" value="TOP 30 Hari" class="h-4 w-4 border-line-hair accent-success" />
                                        Tempo 30 Hari (TOP 30)
                                    </label>
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-line-hair bg-canvas/50 px-3 py-2 text-sm text-ink-body">
                                        <input type="radio" name="pembayaran" value="Cash On Delivery" class="h-4 w-4 border-line-hair accent-success" />
                                        Bayar di Tempat (COD Dock)
                                    </label>
                                </div>
                            </fieldset>

                            <div>
                                <label for="kontak-catatan" class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                    Catatan Khusus / Spesifikasi Tambahan
                                </label>
                                <textarea id="kontak-catatan" name="catatan" rows="3"
                                    placeholder="Tuliskan spesifikasi potongan, jadwal jam tiba, atau permohonan sampel..."
                                    class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm leading-relaxed text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none"></textarea>
                            </div>

                            <div class="flex flex-col gap-3 border-t border-line-hair pt-5">
                                <x-gpa.btn type="submit" variant="accent" size="lg" block>
                                    <x-slot:icon>
                                        <x-gpa.icon name="send" />
                                    </x-slot:icon>
                                    Kirim Permohonan Pasokan
                                </x-gpa.btn>

                                <p class="text-center text-xs leading-relaxed text-ink-subtle">
                                    Data Anda terlindungi oleh kebijakan kerahasiaan komersial Green Pasundan Agriculture.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- DENAH AKSES LOGISTIK --}}
    <section id="denah" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Skema Rute Pengiriman &amp; Akses Logistik Subuh
                </h2>
                <span class="gpa-mono-xs text-ink-subtle">ESTIMASI WAKTU LOGISTIK</span>
            </div>

            <div class="rounded-2xl border border-line-hair bg-white p-5 shadow-sub md:p-8">
                <div class="rounded-xl border-2 border-success/40 bg-[#EFF4DC]/40 p-4 md:p-6">
                    <div class="flex flex-col items-stretch gap-3 md:flex-row md:items-center md:gap-0">
                        {{-- NODE 1 --}}
                        <div class="rounded-xl border-2 border-success bg-white p-4 md:w-52 md:shrink-0">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-success">04:30 WIB</p>
                            <p class="mt-2 text-sm font-extrabold leading-snug text-brand-strong">Kebun Ciwidey</p>
                            <p class="mt-1 text-xs leading-relaxed text-ink-body">Petik daun &amp; sortasi lapang</p>
                            <p class="mt-2.5 rounded border border-line-hair bg-canvas px-2 py-1 gpa-mono-xs font-semibold text-ink-subtle">Mitra Petani GAP</p>
                        </div>

                        <div class="flex flex-col items-center gap-1 px-2 py-1 md:w-28 md:shrink-0 md:px-0">
                            <span class="gpa-mono-xs text-center font-semibold text-ink-body">60 Menit</span>
                            <span class="font-mono text-sm font-bold tracking-widest text-success">════►</span>
                            <span class="gpa-mono-xs text-center text-ink-subtle">Insulasi</span>
                        </div>

                        {{-- NODE 2 --}}
                        <div class="rounded-xl border-2 border-brand-deep bg-brand p-4 md:w-52 md:shrink-0">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">06:00 - 15:00 WIB</p>
                            <p class="mt-2 text-sm font-extrabold leading-snug text-white">Hub Konsolidasi</p>
                            <p class="mt-1 text-xs leading-relaxed text-white/80">Pre-cooling, QC &amp; Tera Timbang</p>
                            <p class="mt-2.5 rounded border border-white/20 bg-white/10 px-2 py-1 gpa-mono-xs font-semibold text-white/80">Cold Room 4°C</p>
                        </div>

                        <div class="flex flex-col items-center gap-1 px-2 py-1 md:w-28 md:shrink-0 md:px-0">
                            <span class="gpa-mono-xs text-center font-semibold text-ink-body">Dispatch 03:00</span>
                            <span class="font-mono text-sm font-bold tracking-widest text-success">════►</span>
                            <span class="gpa-mono-xs text-center text-ink-subtle">Reefer Truck</span>
                        </div>

                        {{-- NODE 3 --}}
                        <div class="rounded-xl border-2 border-success bg-white p-4 md:w-52 md:shrink-0">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-success">05:30 - 06:30 WIB</p>
                            <p class="mt-2 text-sm font-extrabold leading-snug text-brand-strong">Dock Kitchen Klien</p>
                            <p class="mt-1 text-xs leading-relaxed text-ink-body">Serah terima &amp; e-PoD digital</p>
                            <p class="mt-2.5 rounded border border-line-hair bg-canvas px-2 py-1 gpa-mono-xs font-semibold text-ink-subtle">Hotel &amp; Resto</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-line-hair bg-canvas/40 p-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-success">Jalur Prioritas Bebas Macet</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-body">Pengiriman meluncur dini hari melintasi tol Cipularang sehingga tiba sebelum jam operasional dapur restoran.</p>
                    </div>
                    <div class="rounded-xl border border-line-hair bg-canvas/40 p-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-success">Kontrol Suhu Kargo 4°C</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-body">Suhu kabin box mobil terus dimonitor sensor telemetri dari titik muat hingga proses bongkar di tujuan.</p>
                    </div>
                    <div class="rounded-xl border border-line-hair bg-canvas/40 p-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-success">Garansi Penggantian</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-ink-body">Jika sayuran mengalami kerusakan selama perjalanan, penggantian atau pemotongan tagihan langsung diproses.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
