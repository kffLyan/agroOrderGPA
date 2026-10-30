@extends('layouts.public')

@section('title', 'Beranda | AgroOrder GPA')

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden border-b border-accent-deep/20 bg-brand text-white">
        <div class="pointer-events-none absolute -left-16 top-0 h-72 w-72 rounded-full bg-success/20 blur-3xl sm:left-[200px] sm:h-96 sm:w-96"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 left-6 h-64 w-64 rounded-full bg-accent-deep/10 blur-3xl sm:left-10 sm:top-[460px] sm:h-80 sm:w-80"
            aria-hidden="true"></div>

        <div
            class="relative mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 py-10 sm:px-6 sm:py-14 lg:gap-10 lg:py-20">
            <div class="flex w-full max-w-3xl flex-col items-center gap-5 text-center sm:gap-6 md:items-start md:text-left">

                <h1 class="text-center text-3xl font-extrabold leading-tight sm:text-3xl sm:leading-9 md:text-left lg:text-[44px] lg:leading-10">
                    <span class="block">Sistem Rantai Pasok &amp;</span>
                    <span class="block">Pemesanan Agribisnis Terpadu:</span>
                    <span class="block text-accent-deep underline underline-offset-4">Dari Petani Hingga Dapur Komersial</span>
                </h1>

                <p class="max-w-[672px] text-sm leading-6 text-[#D9E4C0] sm:text-base sm:leading-7 lg:text-lg">
                    Solusi terintegrasi agrikultur: pasokan panen langsung kelompok tani binaan, manajemen buffer stock anti-selisih, bukti serah terima digital (e-PoD), dan penagihan tempo B2B yang presisi dan akuntabel.
                </p>

                <div class="flex w-full max-w-sm flex-col gap-3 pt-1 sm:pt-2">
                    <x-gpa.btn :href="route('public.catalog')" variant="accent" size="xl" block
                        class="whitespace-normal text-center">
                        <x-slot:icon>
                            <x-gpa.icon name="cart" />
                        </x-slot:icon>
                        Eksplorasi Katalog
                    </x-gpa.btn>
                    <x-gpa.btn href="#galeri" variant="inverse" size="xl" block
                        class="whitespace-normal text-center">
                        <x-slot:icon>
                            <x-gpa.icon name="camera" />
                        </x-slot:icon>
                        Lihat Galeri
                    </x-gpa.btn>
                </div>

                <div class="flex w-full items-start gap-3 rounded-xl border border-accent-deep/40 bg-brand-deep/80 p-3.5 sm:p-4">
                    <x-gpa.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-accent-deep" />
                    <div class="flex flex-col gap-1.5">
                        <p class="font-mono text-[11px] font-bold leading-[19.5px] text-white sm:text-xs">Protokol Anti-Overselling:</p>
                        <p class="text-[11px] leading-[18px] text-[#D9E4C0] sm:text-xs sm:leading-[19.5px]">Setiap DO (Delivery Order) divalidasi langsung terhadap timbangan fisik sebelum rute pengiriman logistik subuh diterbitkan.</p>
                    </div>
                </div>
            </div>

            {{-- FARM-TO-TABLE STEPPER --}}
            <section aria-labelledby="farm-to-table-heading" class="w-full rounded-2xl border border-accent-deep/40 bg-brand-deep/80 p-6 shadow-2xl sm:p-8 lg:p-10">
                <div class="mb-8 max-w-2xl sm:mb-10">
                    <p class="font-mono text-xs font-bold uppercase tracking-[0.16em] text-accent-deep">GPA CORE FLOW ENGINE</p>
                    <h2 id="farm-to-table-heading" class="mt-3 text-3xl font-extrabold leading-tight text-white sm:text-4xl">Farm-to-Table Core Architecture</h2>
                </div>

                <ol class="relative grid gap-6 md:grid-cols-4 md:gap-8">
                    <li aria-hidden="true" class="absolute left-2 right-2 top-[7px] hidden h-px bg-[#D9D6C8] md:block"></li>
                    <li class="relative pt-8">
                        <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                        <p class="font-mono text-sm font-bold leading-5 text-accent-deep">01</p>
                        <h3 class="mt-3 text-base font-bold leading-6 text-white">Petani Binaan &amp; Buffer</h3>
                        <p class="mt-2 text-sm leading-6 text-[#D9E4C0]">Panen terencana dari kelompok tani binaan dan buffer stock menjaga kontinuitas pasokan.</p>
                    </li>
                    <li class="relative pt-8">
                        <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                        <p class="font-mono text-sm font-bold leading-5 text-accent-deep">02</p>
                        <h3 class="mt-3 text-base font-bold leading-6 text-white">Sortasi &amp; Persiapan</h3>
                        <p class="mt-2 text-sm leading-6 text-[#D9E4C0]">Komoditas disortir, disiapkan sesuai standar mutu, lalu dikemas untuk distribusi.</p>
                    </li>
                    <li class="relative pt-8">
                        <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                        <p class="font-mono text-sm font-bold leading-5 text-accent-deep">03</p>
                        <h3 class="mt-3 text-base font-bold leading-6 text-white">Pengiriman &amp; Bukti Kirim Digital</h3>
                        <p class="mt-2 text-sm leading-6 text-[#D9E4C0]">Pesanan dikirim ke receiving dock dengan dokumentasi foto dan serah terima dua sisi.</p>
                    </li>
                    <li class="relative pt-8">
                        <span class="absolute left-0 top-0 h-4 w-4 rounded-full bg-[#D9E000]" aria-hidden="true"></span>
                        <p class="font-mono text-sm font-bold leading-5 text-accent-deep">04</p>
                        <h3 class="mt-3 text-base font-bold leading-6 text-white">Faktur B2B</h3>
                        <p class="mt-2 text-sm leading-6 text-[#D9E4C0]">Penagihan disusun berdasarkan bobot aktual dan bukti serah terima digital.</p>
                    </li>
                </ol>
            </section>
        </div>
    </section>

    {{-- SOP & METODOLOGI OPERASIONAL --}}
    <section class="pb-14 pt-8 sm:pb-16 sm:pt-10 lg:pb-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:gap-10 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <h2 class="text-center text-2xl font-extrabold leading-tight text-[#121A0F] sm:text-3xl sm:leading-10 lg:text-[36px]">Prinsip Operasional Rantai Pasok Terintegrasi</h2>
                <p class="max-w-[672px] text-sm leading-6 text-[#5C6B57] sm:text-base">Enam tahapan terkoordinasi secara ketat demi menjamin mutu hortikultura dan akuntabilitas transaksi harian bagi mitra komersial.</p>
            </div>

            <ol class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold text-success bg-[#EAF2D7]">TAHAP 01</span>
                            <span class="font-mono text-xs text-[#5C6B57]">HULU PERTANIAN</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Pasokan Terjamin & Buffer Stock</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Jaringan petani binaan dataran tinggi Ciwidey dan Lembang memastikan ketersediaan komoditas segar tanpa terputus. Dilengkapi buffer stock cadangan pada cold storage hub.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Safety Stock Buffer: 15% Terkunci</span>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold text-success bg-[#EAF2D7]">TAHAP 02</span>
                            <span class="font-mono text-xs text-[#5C6B57]">PESANAN MASUK</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Pemesanan Multi-Kanal B2B</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Pemesanan diajukan secara fleksibel melalui portal web B2B terotentikasi atau koordinasi langsung meja pengadaan sebelum batas cut-off.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Cut-Off Pemesanan: Pukul 13:00 WIB</span>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold text-success bg-[#EAF2D7]">TAHAP 03</span>
                            <span class="font-mono text-xs text-[#5C6B57]">ALOKASI FISIK</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Verifikasi & Kontrol Kuota</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Order diperiksa terhadap batas kredit kontrak dan kuota pasokan fisik yang tersedia. Menjamin pesanan yang disetujui pasti terkirim 100%.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Validasi DO: Sistem Otomatis</span>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold text-success bg-[#EAF2D7]">TAHAP 04</span>
                            <span class="font-mono text-xs text-[#5C6B57]">QC & METROLOGI</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Sortir Mutu & Timbangan Riil</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Sortasi manual lapis dua di gudang konsolidasi. Penimbangan menggunakan timbangan digital tersertifikasi metrologi legal sah Kementerian Perdagangan RI.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Akurasi Tera: Metrologi Sah RI</span>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold text-success bg-[#EAF2D7]">TAHAP 05</span>
                            <span class="font-mono text-xs text-[#5C6B57]">LOGISTIK SUBUH</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Distribusi Armada Berpendingin</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Armada reefer berangkat subuh pukul 03:00 - 05:30 WIB. Menghindari kemacetan dan menjaga kesegaran selada serta sayuran dingin tiba sebelum dapur hotel buka.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Waktu Tiba: Subuh 03:00 - 05:30 WIB</span>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold text-success bg-[#EAF2D7]">TAHAP 06</span>
                            <span class="font-mono text-xs text-[#5C6B57]">PENERIMAAN &amp; BUKTI KIRIM</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Serah Terima Digital (Bukti Kirim Sah)</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Penerimaan di receiving dock diverifikasi lewat foto fisik, timbangan ulang, dan persetujuan digital. Faktur final dicetak otomatis tanpa selisih klaim susut.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Jaminan Selisih: 0% Susut Timbangan</span>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    {{-- KATALOG PANEN TERKINI (DYNAMIC FROM DATABASE) --}}
    <section id="komoditas" class="scroll-mt-28 border-y border-line-hair bg-[#EFF4DC] py-14 sm:scroll-mt-36 sm:py-16 lg:py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:gap-10 sm:px-6">
            <div class="flex flex-col items-center gap-6 border-b border-line-hair pb-6 text-center">
                <div class="flex flex-col items-center gap-2 text-center">
                    <h2 class="text-center text-2xl font-extrabold leading-tight text-[#121A0F] sm:text-3xl sm:leading-10 lg:text-[36px]">Komoditas Inti Pertanian GPA</h2>
                    <p class="mx-auto max-w-[672px] text-center text-sm leading-6 text-[#5C6B57] sm:text-base">Daftar komoditas siap pesan langsung oleh pembeli korporat maupun publik secara transparan berbasis stok panen riil.</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center justify-center gap-2.5">
                    <span class="rounded-lg border border-success/30 bg-white px-3 py-1.5 font-mono text-xs font-semibold leading-4 text-brand">
                        Total: {{ $products->count() }} Komoditas Aktif
                    </span>
                    <span class="rounded-lg bg-brand px-3 py-1.5 font-mono text-xs font-bold leading-4 text-accent-deep">
                        Toleransi Susut &lt;1.5%
                    </span>
                </div>
            </div>

            <ul class="grid grid-cols-2 gap-3 sm:gap-4 lg:gap-6 xl:grid-cols-3">
                @forelse ($products as $product)
                    @php
                        $stock = (float) $product->harvestBatches->sum('available_quantity');
                        $nameLower = strtolower($product->name);
                        $image = 'gpaleaves.png';
                        if (str_contains($nameLower, 'selada') || str_contains($nameLower, 'lettuce') || str_contains($nameLower, 'romaine')) {
                            $image = 'lettuce.jpg';
                        } elseif (str_contains($nameLower, 'brokoli') || str_contains($nameLower, 'broccoli')) {
                            $image = 'broccoli.jpg';
                        } elseif (str_contains($nameLower, 'tomat') || str_contains($nameLower, 'tomato')) {
                            $image = 'tomato.jpg';
                        } elseif (str_contains($nameLower, 'stroberi') || str_contains($nameLower, 'strawberry') || str_contains($nameLower, 'berry')) {
                            $image = 'strawberry.jpg';
                        } elseif (str_contains($nameLower, 'kol') || str_contains($nameLower, 'cabbage') || str_contains($nameLower, 'kubis')) {
                            $image = 'cabbage.jpg';
                        } elseif ($product->image_url && file_exists(public_path($product->image_url))) {
                            $image = $product->image_url;
                        }
                    @endphp
                    <li class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
                        <div class="aspect-[4/3] w-full shrink-0 overflow-hidden bg-surface-muted sm:aspect-[16/10]">
                            <img src="{{ asset($image) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover">
                        </div>

                        <div class="flex flex-1 flex-col justify-between gap-3 p-3 sm:gap-4 sm:p-4 lg:p-5">
                            <div class="flex flex-col gap-3">
                                <div class="flex flex-col gap-1.5 pt-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-3">
                                    <div class="flex min-w-0 flex-col gap-0.5">
                                        <span class="truncate font-mono text-[10px] leading-4 text-[#5C6B57] sm:text-xs">{{ $product->sku }}</span>
                                        <h3 class="text-sm font-bold leading-5 text-brand sm:text-base lg:text-lg lg:leading-[24.75px]">{{ $product->name }}</h3>
                                    </div>
                                    <span class="w-fit shrink-0 rounded-full bg-brand px-2 py-0.5 text-center font-mono text-[9px] font-bold leading-[14px] text-accent-deep sm:px-2.5 sm:text-[11px] sm:leading-[16.5px]">
                                        {{ $product->grade ?? 'Grade A' }}
                                    </span>
                                </div>

                                <p class="line-clamp-3 text-[11px] leading-[18px] text-[#5C6B57] sm:text-xs sm:leading-[19.5px] lg:line-clamp-none">{{ $product->description }}</p>

                                <div class="hidden flex-col gap-1.5 border-t border-line-hair pt-2 sm:flex">
                                    <div class="grid grid-cols-2 gap-2">
                                        <span class="font-mono text-[10px] leading-4 text-[#5C6B57] sm:text-xs">Min. Pemesanan:</span>
                                        <span class="font-mono text-[10px] font-bold leading-4 text-[#121A0F] sm:text-xs">
                                            {{ number_format($product->minimum_order, 0, ',', '.') }} {{ ucfirst($product->unit) }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 items-center gap-2">
                                        <span class="font-mono text-[10px] leading-4 text-[#5C6B57] sm:text-xs">Status Stok Live:</span>
                                        @if ($stock > 500)
                                            <span class="flex items-center gap-1.5 justify-self-start">
                                                <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                                                <span class="font-mono text-[10px] font-bold leading-4 text-brand sm:text-xs">Tersedia (Ready Hub)</span>
                                            </span>
                                        @elseif ($stock > 0)
                                            <span class="justify-self-start rounded bg-[#FFFBEB] px-1.5 py-0.5 font-mono text-[9px] font-bold leading-4 text-[#704602] outline outline-1 outline-[#FCD34D] [outline-offset:-1px] sm:px-2 sm:text-[11px]">
                                                Tersedia ({{ number_format($stock, 0, ',', '.') }} {{ $product->unit }})
                                            </span>
                                        @else
                                            <span class="font-mono text-[10px] font-bold leading-4 text-danger sm:text-xs">Stok Menipis / PO</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-line-hair pt-2.5 sm:pt-3">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                                    <p class="flex items-center gap-1">
                                        <span class="font-mono text-sm font-bold leading-5 text-brand sm:text-base sm:leading-6">
                                            Rp {{ number_format($product->base_price, 0, ',', '.') }}/{{ $product->unit }}
                                        </span>
                                    </p>
                                    <x-gpa.btn :href="route('public.catalog')" variant="accent" size="sm"
                                        class="w-full shrink-0 sm:w-auto">
                                        Pesan
                                    </x-gpa.btn>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="col-span-full rounded-2xl border border-line-hair bg-white p-8 text-center text-[#5C6B57] text-sm">
                        Belum ada komoditas panen yang tersedia saat ini.
                    </li>
                @endforelse

            </ul>

            <div
                class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border-2 border-accent-deep bg-brand p-5 shadow-md sm:p-6">
                <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-success/30 blur-md sm:right-[-80px] sm:top-[-40px]"
                    aria-hidden="true"></div>
                <div class="relative flex flex-col gap-2">
                    <h3 class="pt-1 text-lg font-extrabold leading-6 text-white sm:text-xl sm:leading-[25px]">
                        Permintaan Khusus atau Tonase Besar?
                    </h3>
                    <p class="text-xs leading-[19.5px] text-[#D9E4C0]">
                        GPA melayani pengadaan sayuran daun, bumbu basah, umbi-umbian, dan buah musiman lainnya dengan spesifikasi kontrak pasokan jangka panjang.
                    </p>
                    <ul class="flex flex-col gap-2 pt-2 sm:flex-row sm:flex-wrap sm:gap-x-4">
                        <li class="flex items-center gap-2">
                            <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-accent-deep" />
                            <span class="font-mono text-xs leading-4 text-[#D9E4C0]">Kontrak harga flat per kuartal</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-accent-deep" />
                            <span class="font-mono text-xs leading-4 text-[#D9E4C0]">Standar sertifikasi residu pestisida aman</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-accent-deep" />
                            <span class="font-mono text-xs leading-4 text-[#D9E4C0]">Jadwal kirim subuh terdedikasi</span>
                        </li>
                    </ul>
                </div>
                <div class="pt-2 sm:pt-4">
                    <x-gpa.btn :href="route('public.kontak')" variant="accent" size="md" block
                        class="sm:w-auto">
                        Hubungi Desk Pengadaan
                    </x-gpa.btn>
                </div>
            </div>
        </div>
    </section>

    {{-- PROTOKOL AKUNTABILITAS B2B --}}
    <section class="py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <h2 class="text-center text-2xl font-extrabold leading-tight text-[#121A0F] sm:text-3xl sm:leading-10 lg:text-[36px]">Keunggulan Tata Kelola &amp; Akuntabilitas Anti-Selisih</h2>
                <p class="max-w-[672px] text-sm leading-6 text-[#5C6B57] sm:text-base">Tiga pilar fundamental dalam sistem operasi GPA yang menjamin transparansi fisik dan administrasi keuangan bagi mitra bisnis.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="scale" class="h-5 w-5 text-brand" />
                        </span>
                        <h3 class="text-xl font-bold leading-7 text-brand">Berat Riil Timbangan Akurat</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Penimbangan di gudang konsolidasi menggunakan timbangan tera resmi yang terkalibrasi berkala oleh Balai Metrologi. Angka timbangan tercatat langsung tanpa rekayasa manual.</p>
                        <ul class="flex flex-col gap-2">
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Tera sah Balai Standardisasi Metrologi Legal</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Pencatatan netto otomatis tanpa selisih tara wadah</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4">
                        <div class="border-t border-line-hair pt-4">
                            <p class="font-mono text-xs font-bold leading-4 text-success">Jaminan 100% Berat Riil (Anti Susut Fiktif)</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="check-circle" class="h-5 w-5 text-brand" />
                        </span>
                        <h3 class="text-xl font-bold leading-7 text-brand">Bukti Serah Terima Terintegrasi (e-PoD)</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Setiap serah terima di pintu kitchen mitra diverifikasi via aplikasi driver mobile: dokumentasi foto komoditas, foto nota timbang fisik, dan tanda tangan digital receiving staff.</p>
                        <ul class="flex flex-col gap-2">
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Validasi ganda saat serah terima dock receiving</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Bukti kirim tersimpan rapi dan aman di sistem arsip</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4">
                        <div class="border-t border-line-hair pt-4">
                            <p class="font-mono text-xs font-bold leading-4 text-success">100% Terverifikasi Digital</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="banknote" class="h-5 w-5 text-brand" />
                        </span>
                        <h3 class="text-xl font-bold leading-7 text-brand">Fleksibilitas Pembayaran B2B (TOP)</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Dukungan termin pembayaran Term of Payment (TOP 14 hingga 45 hari) khusus untuk mitra korporat resmi dengan invoice audit-ready dan rekonsiliasi tanpa sengketa faktur.</p>
                        <ul class="flex flex-col gap-2">
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Faktur pajak elektronik PKP terdaftar resmi</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Rekonsiliasi tagihan otomatis tanpa selisih</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4">
                        <div class="border-t border-line-hair pt-4">
                            <p class="font-mono text-xs font-bold leading-4 text-success">Faktur Tagihan Korporat Resmi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PORTFOLIO KONTRAK AKTIF (DYNAMIC FROM DATABASE) --}}
    <section id="mitra-kontrak" class="scroll-mt-36 border-t border-line-hair bg-[#F5F0E3] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-6 border-b border-line-hair pb-6 text-center">
                <div class="flex flex-col items-center gap-2 text-center">
                    <h2 class="text-center text-[36px] font-extrabold leading-10 text-[#121A0F]">Mitra & Klien Kontrak Terverifikasi</h2>
                    <p class="mx-auto max-w-[672px] text-center text-base leading-6 text-[#5C6B57]">Entitas komersial terkemuka yang mempercayakan kontinuitas pasokan segar harian, stabilitas harga kuartalan, dan jaminan berat riil 100%.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4 lg:grid-cols-6">
                @php
                    $clientLogos = [
                        'aeon' => 'images/clients/aeon.jpg',
                        'aerofood' => 'images/clients/aerofood.jpg',
                        'bogarasa' => 'images/clients/bogarasa.jpg',
                        'nusantara' => 'images/clients/nusantara.jpg',
                        'pangrango' => 'images/clients/pangrango.jpg',
                        'resto-sunda' => 'images/clients/resto-sunda.jpg',
                        'restosunda' => 'images/clients/resto-sunda.jpg',
                        'ritelindo' => 'images/clients/ritelindo.jpg',
                    ];
                    $clientList = ($clients ?? collect())->isNotEmpty()
                        ? $clients
                        : (isset($contracts) && $contracts->isNotEmpty()
                            ? $contracts->map(fn($c) => $c->user)->filter()->unique('id')
                            : collect());
                @endphp
                @forelse ($clientList as $client)
                    @php
                        $clientName = $client->company_name ?: $client->name;
                        $logo = $client->logo ?? $client->logo_url ?? $client->image ?? $client->brand_logo ?? null;
                        $key = strtolower(str_replace([' ', '.', '-', '_'], '', $clientName));
                        if (str_contains($key, 'aerofood')) {
                            $logo = 'images/clients/aerofood.jpg';
                        } elseif (!$logo) {
                            foreach ($clientLogos as $name => $path) {
                                if (str_contains($key, str_replace([' ', '.', '-', '_'], '', $name))) {
                                    $logo = $path;
                                    break;
                                }
                            }
                        }
                        if (!$logo && isset($client->id)) {
                            $files = ['aeon.jpg', 'aerofood.jpg', 'bogarasa.jpg', 'nusantara.jpg', 'pangrango.jpg', 'resto-sunda.jpg', 'ritelindo.jpg'];
                            $logo = 'images/clients/' . $files[($client->id - 1) % count($files)];
                        }
                    @endphp
                    <div class="flex h-32 items-center justify-center rounded-xl border border-line-hair bg-white p-3 transition hover:shadow-card sm:h-36 md:h-38 lg:h-40">
                        @if ($logo)
                            <img src="{{ asset($logo) }}" alt="{{ $clientName }}" class="max-h-24 w-auto object-contain sm:max-h-26 md:max-h-28 lg:max-h-30" loading="lazy">
                        @else
                            <span class="text-center text-[11px] font-semibold uppercase tracking-wide text-ink-body sm:text-xs">
                                {{ \Illuminate\Support\Str::limit($clientName, 14) }}
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full rounded-2xl border border-line-hair bg-white p-8 text-center text-[#5C6B57] text-sm">
                        Belum ada data kemitraan kontrak komersial aktif saat ini.
                    </div>
                @endforelse

                <div
                    class="col-span-full flex flex-col gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand">
                            <x-gpa.icon name="file-text" class="h-4 w-4 text-accent-deep" />
                        </span>
                        <div class="flex flex-col gap-0.5">
                            <p class="text-sm font-bold leading-5 text-brand">Ingin Menjadi Mitra Kontrak Komersial?</p>
                            <p class="text-xs leading-4 text-[#5C6B57]">Dapatkan kepastian alokasi komoditas harian, harga flat kuartalan, dan fasilitas TOP resmi B2B.</p>
                        </div>
                    </div>
                    <x-gpa.btn :href="route('public.mitra-kontrak')" variant="inverse" size="md"
                        class="shrink-0">
                        Ajukan Kemitraan Kontrak
                    </x-gpa.btn>
                </div>
            </div>
        </div>
    </section>

    {{-- SPESIFIKASI SEKTOR INDUSTRI --}}
    <section class="py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <h2 class="text-center text-2xl font-extrabold leading-tight text-[#121A0F] sm:text-3xl sm:leading-10 lg:text-[36px]">Standar Pasokan Sesuai Karakter Sektor Bisnis</h2>
                <p class="max-w-[672px] text-sm leading-6 text-[#5C6B57] sm:text-base">Setiap industri menuntut standar sortasi, jendela pengiriman, dan perlakuan cold chain yang berbeda. GPA menyelaraskan seluruh parameter.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="building" class="h-5 w-5 text-brand" />
                        </span>
                        <div class="flex flex-col gap-0.5 pt-[7px]">
                            <h3 class="text-lg font-bold leading-7 text-brand">Hotel, Resort & Fine Dining</h3>
                        </div>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Sortasi visual grade A super tanpa cacat fisik. Kuntum brokoli rapat, selada romaine renyah bersih tanpa lumpur, siap racik salad bar.</p>
                        <ul class="flex flex-col gap-1.5 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Estetika & keseragaman ukuran tinggi</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Pengiriman subuh sebelum dapur aktif</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Kemasan higienis berlabel tanggal panen</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-xs font-bold leading-4 text-brand">Fokus: Estetika & Kesegaran Maksimal</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="clipboard" class="h-5 w-5 text-brand" />
                        </span>
                        <div class="flex flex-col gap-0.5 pt-[7px]">
                            <h3 class="text-lg font-bold leading-7 text-brand">Inflight Catering & Dapur Sentral</h3>
                        </div>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Kepatuhan audit residu pestisida ketat dan HACCP. Menjamin stabilitas pasokan harian dengan toleransi selisih bobot 0%.</p>
                        <ul class="flex flex-col gap-1.5 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Audit mikrobiologi & uji residu MRL</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Jadwal kirim ketat bandara (CGK Hub)</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Integrasi barcode lot penelusuran panen</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-xs font-bold leading-4 text-brand">Fokus: Higienitas & Kepatuhan Regulasi</p>
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="cart" class="h-5 w-5 text-brand" />
                        </span>
                        <div class="flex flex-col gap-0.5 pt-[7px]">
                            <h3 class="text-lg font-bold leading-7 text-brand">Supermarket & Modern Groceries</h3>
                        </div>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Sayuran pra-cuci dan pra-kemas dalam clamshell/tray berlubang udara. Siap pajang langsung di rak pendingin display konsumen ritel.</p>
                        <ul class="flex flex-col gap-1.5 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Daya simpan (shelf-life) terukur 5-7 hari</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Barcode retail EAN-13 siap scan POS</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Kontinuitas pasokan harian tanpa putus</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-xs font-bold leading-4 text-brand">Fokus: Shelf-Life & Kemasan Display</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- GALERI & DOKUMENTASI OPERASIONAL --}}
    <section id="galeri" class="scroll-mt-36 border-t border-line-hair bg-[#EFF4DC] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:px-6" x-data="{ activeItem: null }">
            <div class="flex flex-col items-center gap-6 border-b border-line-hair pb-6 text-center">
                <div class="flex flex-col items-center gap-2 text-center">
                    <h2 class="text-center text-[36px] font-extrabold leading-10 text-[#121A0F]">Transparansi Lapangan & Rantai Pasok</h2>
                    <p class="mx-auto max-w-[672px] text-center text-base leading-6 text-[#5C6B57]">Arsip dokumentasi nyata dari kebun binaan, proses sortasi packing house, penimbangan tera metrologi, hingga armada distribusi subuh.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-1 sm:grid-cols-2 lg:grid-cols-3">
                <article class="relative cursor-pointer overflow-hidden md:group"
                    @click.stop="activeItem = activeItem === 1 ? null : 1">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('lettuce.jpg') }}" alt="Panen Selada Hidroponik Ciwidey"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 1 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Ciwidey · 1.300 mdpl</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Panen Selada &amp; Daun Salad di Lahan Ciwidey</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">Pemetikan selada keriting dan romaine dilakukan pada pagi hari sebelum sinar matahari terik untuk menjaga kadar air sel tanaman tetap maksimal.</p>
                        </div>
                    </div>
                </article>

                <article class="relative cursor-pointer overflow-hidden md:group"
                    @click.stop="activeItem = activeItem === 2 ? null : 2">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('broccoli.jpg') }}" alt="Kebun Brokoli Lembang"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 2 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Lembang · 1.350 mdpl</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Sentra Brokoli &amp; Brasika Mitra Lembang</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">Kelompok tani binaan menerapkan Good Agricultural Practices (GAP) dengan pengawasan jadwal tanam agar masa panen tidak saling tumpang tindih.</p>
                        </div>
                    </div>
                </article>

                <article class="relative cursor-pointer overflow-hidden md:group"
                    @click.stop="activeItem = activeItem === 3 ? null : 3">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('tomato.jpg') }}" alt="Tomat Beef Greenhouse Ciwidey"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 3 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Greenhouse · Ciwidey</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Kultivasi Tomat Beef Berkualitas Restoran</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">Tomat dipetik saat tingkat kematangan 80% dengan ketebalan daging buah terjamin agar tidak pecah selama distribusi logistik ke jaringan burger dan hotel.</p>
                        </div>
                    </div>
                </article>

                <article class="relative cursor-pointer overflow-hidden md:group"
                    @click.stop="activeItem = activeItem === 4 ? null : 4">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('images/activities/panen.jpg') }}" alt="Panen Selada Segar"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 4 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Kebun Sayuran</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Panen</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Panen Selada Segar</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">Selada segar dipilih langsung dari kebun untuk menjaga kualitas daun sebelum memasuki proses penanganan berikutnya.</p>
                        </div>
                    </div>
                </article>

                <article class="relative cursor-pointer overflow-hidden md:group"
                    @click.stop="activeItem = activeItem === 5 ? null : 5">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('images/activities/muatan.jpg') }}" alt="Pemuatan Sayuran untuk Distribusi"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 5 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Persiapan Distribusi</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Logistik</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Pemuatan Sayuran untuk Distribusi</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">Sayuran yang sudah dikemas ditata pada kendaraan angkut sebagai bagian dari proses pengiriman kepada pelanggan.</p>
                        </div>
                    </div>
                </article>

                <article class="relative cursor-pointer overflow-hidden md:group"
                    @click.stop="activeItem = activeItem === 6 ? null : 6">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('images/activities/inspeksi.jpeg') }}" alt="Inspeksi Tanaman di Area Budidaya"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 6 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Area Budidaya</span>
                                <span class="rounded-full bg-accent/90 px-2 py-0.5 gpa-micro-bold text-brand-strong">Inspeksi</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Inspeksi Tanaman di Area Budidaya</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">Petugas memeriksa tanaman dan mencatat hasil pengamatan sebagai bagian dari pemantauan proses budidaya.</p>
                        </div>
                    </div>
                </article>
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col gap-1">
                    <p class="text-base font-bold leading-6 text-brand">Kunjungi Fasilitas Gudang & Kebun GPA</p>
                    <p class="text-xs leading-4 text-[#5C6B57]">Kami membuka pintu bagi tim purchasing dan executive chef untuk verifikasi langsung standar mutu dan legalitas tera.</p>
                </div>
                <x-gpa.btn :href="route('public.about') . '#legalitas'" variant="inverse" size="md"
                    class="shrink-0">
                    Pelajari Legalitas & Tata Kelola
                </x-gpa.btn>
            </div>
        </div>
    </section>

@endsection
