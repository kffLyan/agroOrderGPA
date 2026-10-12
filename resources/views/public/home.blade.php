@extends('layouts.public')

@section('title', 'Beranda | AgroOrder GPA')

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden border-b border-accent-deep/20 bg-brand text-white">
        <div class="pointer-events-none absolute left-[576px] top-0 h-96 w-96 rounded-full bg-success/20 blur-2xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-10 top-[460px] h-80 w-80 rounded-full bg-accent-deep/10 blur-xl"
            aria-hidden="true"></div>

        <div
            class="relative mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-20 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_490px] lg:items-start">
            <div class="flex flex-col items-start gap-6">

                <span class="inline-flex items-center gap-2 rounded-full border border-accent-deep/30 bg-brand-deep px-3 py-1 font-mono text-[11px] font-bold tracking-wider text-accent">
                    GPA PRODUCTION & DISTRIBUTION PROTOCOL V3.2
                </span>

                <h1 class="text-3xl font-extrabold leading-9 sm:text-[44px] sm:leading-10">
                    <span class="block">Sistem Rantai Pasok &</span>
                    <span class="block">Pemesanan Agribisnis Terpadu:</span>
                    <span class="block text-accent-deep underline">Dari Petani Hingga Dapur Komersial</span>
                </h1>

                <p class="max-w-[672px] text-lg leading-7 text-[#D9E4C0]">
                    Solusi terintegrasi agrikultur: pasokan panen langsung kelompok tani binaan, manajemen buffer stock anti-selisih, penimbangan riil gudang tera metrologi sah, bukti serah terima digital (e-PoD), dan penagihan tempo B2B yang presisi dan akuntabel.
                </p>

                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    <a href="{{ route('public.catalog') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-accent-deep px-6 py-3.5 font-mono text-xs font-bold text-brand shadow-lg">
                        <x-gpa.icon name="cart" class="h-4 w-4 shrink-0 text-brand" />
                        Eksplorasi Katalog Komoditas
                    </a>
                    <a href="#galeri"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-3.5 font-mono text-xs font-bold text-white outline outline-1 outline-accent-deep/50 [outline-offset:-1px]">
                        <x-gpa.icon name="camera" class="h-4 w-4 shrink-0 text-accent-deep" />
                        Lihat Galeri Operasional
                    </a>
                </div>

                <div class="flex w-full items-start gap-3 rounded-xl border border-accent-deep/40 bg-brand-deep/80 p-4">
                    <x-gpa.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-accent-deep" />
                    <div class="flex flex-col gap-1.5">
                        <p class="font-mono text-xs font-bold leading-[19.5px] text-white">Protokol Anti-Overselling:</p>
                        <p class="text-xs leading-[19.5px] text-[#D9E4C0]">Setiap DO (Delivery Order) divalidasi langsung terhadap timbangan fisik sebelum rute pengiriman logistik subuh diterbitkan.</p>
                    </div>
                </div>
            </div>

            {{-- SCHEMATIC CORE FLOW ENGINE --}}
            <div class="flex flex-col gap-4 rounded-2xl border-2 border-accent-deep/50 bg-white p-6 shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-line-hair pb-3">
                    <div>
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-subtle">[SCHEMATIC: GPA_CORE_FLOW_ENGINE]</span>
                        <p class="text-sm font-bold leading-5 text-brand">Farm-to-Table Core Architecture</p>
                    </div>
                    <span class="rounded bg-brand px-2 py-0.5 font-mono text-[10px] font-bold text-accent">ID: AGRO-FLOW-2025</span>
                </div>

                <ol class="flex flex-col gap-2.5">
                    <li class="flex items-start justify-between gap-3 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded bg-brand font-mono text-xs font-bold text-accent-deep">01</span>
                            <div>
                                <p class="text-xs font-bold leading-4 text-brand">Petani Binaan & Buffer</p>
                                <p class="text-[11px] leading-[16.5px] text-[#5C6B57]">Panen terencana di sentra binaan dan pengamanan buffer stock.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded bg-[#EAF2D7] px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px] text-brand">Hulu Terjamin</span>
                    </li>
                    <li class="flex justify-center"><span class="block h-3.5 w-0.5 bg-success/40"></span></li>

                    <li class="flex items-start justify-between gap-3 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded bg-brand font-mono text-xs font-bold text-accent-deep">02</span>
                            <div>
                                <p class="text-xs font-bold leading-4 text-brand">QC Timbang Riil</p>
                                <p class="text-[11px] leading-[16.5px] text-[#5C6B57]">Stasiun timbang tera metrologi sah & kalibrasi bluetooth real-time.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded bg-success px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px] text-white">Tera Metrologi</span>
                    </li>
                    <li class="flex justify-center"><span class="block h-3.5 w-0.5 bg-success/40"></span></li>

                    <li class="flex items-start justify-between gap-3 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded bg-brand font-mono text-xs font-bold text-accent-deep">03</span>
                            <div>
                                <p class="text-xs font-bold leading-4 text-brand">PoD Foto & Stempel Fisik</p>
                                <p class="text-[11px] leading-[16.5px] text-[#5C6B57]">Serah terima subuh via aplikasi e-PoD dua sisi di receiving dock.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded bg-[#D9E4C0] px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px] text-brand">Anti-Selisih</span>
                    </li>
                    <li class="flex justify-center"><span class="block h-3.5 w-0.5 bg-success/40"></span></li>

                    <li class="flex items-start justify-between gap-3 rounded-xl border-2 border-brand bg-[#EAF2D7] p-3">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded bg-accent-deep font-mono text-xs font-bold text-brand">04</span>
                            <div>
                                <p class="text-xs font-extrabold leading-4 text-brand">Faktur B2B (TOP / Manual)</p>
                                <p class="text-[11px] font-medium leading-[16.5px] text-[#225608]">Invoicing otomatis berbasis bobot aktual dan lampiran bukti digital.</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded bg-brand px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px] text-accent-deep">Audit-Ready</span>
                    </li>
                </ol>

                <div class="flex items-center justify-between gap-3 border-t border-line-hair pt-3">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        <span class="font-mono text-[10px] font-bold leading-[15px] text-success">Transparansi Fisik & Digital</span>
                    </span>
                    <span class="font-mono text-[10px] font-bold leading-[15px] text-brand">SLA Akurasi: 99.8%</span>
                </div>
            </div>
        </div>
    </section>

    {{-- SOP & METODOLOGI OPERASIONAL --}}
    <section class="pb-20 pt-10">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-accent-deep bg-[#EAF2D7] px-4 py-1.5">
                    <x-gpa.icon name="clipboard" class="h-3.5 w-3.5 shrink-0 text-brand" />
                    <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">SOP & METODOLOGI OPERASIONAL</span>
                </span>
                <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">Prinsip Operasional Rantai Pasok Terintegrasi</h2>
                <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">Enam tahapan terkoordinasi secara ketat demi menjamin mutu hortikultura dan akuntabilitas transaksi harian bagi mitra komersial.</p>
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
                            <span class="font-mono text-xs text-[#5C6B57]">INBOUND ORDER</span>
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
                            <span class="font-mono text-xs text-[#5C6B57]">RECEIVING & POD</span>
                        </div>
                        <h3 class="text-lg font-bold leading-6 text-brand">Serah Terima Digital (e-PoD)</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Penerimaan di receiving dock diverifikasi lewat foto fisik, timbangan ulang, dan persetujuan digital. Faktur final dicetak otomatis tanpa selisih klaim susut.</p>
                    </div>
                    <div class="border-t border-line-hair pt-3">
                        <span class="font-mono text-xs font-bold text-brand">Selisih Klaim: 0% Phantom Weight</span>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    {{-- KATALOG PANEN TERKINI (DYNAMIC FROM DATABASE) --}}
    <section id="komoditas" class="scroll-mt-36 border-y border-line-hair bg-[#EFF4DC] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col gap-6 border-b border-line-hair pb-6 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2">
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border border-accent-deep bg-[#EAF2D7] px-3 py-1">
                        <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">KATALOG PANEN TERKINI</span>
                    </span>
                    <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">Komoditas Inti Pertanian GPA</h2>
                    <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">Daftar komoditas siap pesan langsung oleh pembeli korporat maupun publik secara transparan berbasis stok panen riil.</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                    <span class="rounded-lg border border-success/30 bg-white px-3 py-1.5 font-mono text-xs font-semibold leading-4 text-brand">
                        Total: {{ $products->count() }} Komoditas Aktif
                    </span>
                    <span class="rounded-lg bg-brand px-3 py-1.5 font-mono text-xs font-bold leading-4 text-accent-deep">
                        Toleransi Susut &lt;1.5%
                    </span>
                </div>
            </div>

            <ul class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
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
                    <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex flex-col gap-0.5 pt-1.5">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">{{ $product->sku }}</span>
                                    <h3 class="text-lg font-bold leading-[24.75px] text-brand">{{ $product->name }}</h3>
                                </div>
                                <span class="shrink-0 rounded-full bg-brand px-2.5 py-0.5 text-center font-mono text-[11px] font-bold leading-[16.5px] text-accent-deep">
                                    {{ $product->grade ?? 'Grade A' }}
                                </span>
                            </div>

                            <div class="h-36 overflow-hidden rounded-xl border border-line-hair bg-surface-muted">
                                <img src="{{ asset($image) }}" alt="{{ $product->name }}"
                                    class="h-full w-full object-cover">
                            </div>

                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">{{ $product->description }}</p>

                            <div class="flex flex-col gap-1.5 border-t border-line-hair pt-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Min. Pemesanan:</span>
                                    <span class="font-mono text-xs font-bold leading-4 text-[#121A0F]">
                                        {{ number_format($product->minimum_order, 0, ',', '.') }} {{ ucfirst($product->unit) }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 items-center gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Status Stok Live:</span>
                                    @if ($stock > 500)
                                        <span class="flex items-center gap-1.5 justify-self-start">
                                            <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                                            <span class="font-mono text-xs font-bold leading-4 text-brand">Tersedia (Ready Hub)</span>
                                        </span>
                                    @elseif ($stock > 0)
                                        <span class="justify-self-start rounded bg-[#FFFBEB] px-2 py-0.5 font-mono text-[11px] font-bold leading-4 text-[#704602] outline outline-1 outline-[#FCD34D] [outline-offset:-1px]">
                                            Tersedia ({{ number_format($stock, 0, ',', '.') }} {{ $product->unit }})
                                        </span>
                                    @else
                                        <span class="font-mono text-xs font-bold leading-4 text-danger">Stok Menipis / PO</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div class="flex items-center justify-between gap-3 border-t border-line-hair pt-3">
                                <p class="flex items-baseline gap-1">
                                    <span class="font-mono text-base font-bold leading-6 text-brand">
                                        Rp {{ number_format($product->base_price, 0, ',', '.') }}
                                    </span>
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">/ {{ $product->unit }}</span>
                                </p>
                                <a href="{{ route('public.catalog') }}"
                                    class="shrink-0 rounded-xl bg-accent-deep px-4 py-2 font-mono text-xs font-bold leading-4 text-brand shadow-sm">
                                    Pesan Komoditas
                                </a>
                            </div>
                        </div>
                    </li>
                @endforeach

                <li
                    class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border-2 border-accent-deep bg-brand p-6 shadow-md">
                    <div class="pointer-events-none absolute left-[272px] top-[240px] h-40 w-40 rounded-full bg-success/30 blur-md"
                        aria-hidden="true"></div>
                    <div class="relative flex flex-col gap-2">
                        <span class="font-mono text-[11px] font-bold tracking-wider text-accent">[CUSTOM B2B COMMODITY]</span>
                        <h3 class="pt-1 text-xl font-extrabold leading-[25px] text-white">
                            Permintaan Khusus atau Tonase Besar?
                        </h3>
                        <p class="text-xs leading-[19.5px] text-[#D9E4C0]">
                            GPA melayani pengadaan sayuran daun, bumbu basah, umbi-umbian, dan buah musiman lainnya dengan spesifikasi kontrak pasokan jangka panjang.
                        </p>
                        <ul class="flex flex-col gap-2 pt-2">
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
                    <div class="pt-4">
                        <a href="{{ route('public.kontak') }}"
                            class="inline-block rounded-xl bg-accent px-5 py-2.5 font-mono text-xs font-bold leading-4 text-brand-deep shadow-sm">
                            Hubungi Desk Pengadaan
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    {{-- PROTOKOL AKUNTABILITAS B2B --}}
    <section class="py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-accent-deep bg-[#EAF2D7] px-4 py-1.5">
                    <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0 text-brand" />
                    <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">PROTOKOL AKUNTABILITAS B2B</span>
                </span>
                <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">Keunggulan Tata Kelola & Akuntabilitas Anti-Selisih</h2>
                <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">Tiga pilar fundamental dalam sistem operasi GPA yang menjamin transparansi fisik dan administrasi keuangan bagi mitra bisnis.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="scale" class="h-5 w-5 text-brand" />
                        </span>
                        <h3 class="text-xl font-bold leading-7 text-brand">Actual Weight as Single Truth</h3>
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
                            <p class="font-mono text-xs font-bold leading-4 text-success">Zero Phantom Weight Guarantee</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="check-circle" class="h-5 w-5 text-brand" />
                        </span>
                        <h3 class="text-xl font-bold leading-7 text-brand">Integrated Proof of Delivery</h3>
                        <p class="text-xs leading-[19.5px] text-[#5C6B57]">Setiap serah terima di pintu kitchen mitra diverifikasi via aplikasi driver mobile: dokumentasi foto komoditas, foto nota timbang fisik, dan tanda tangan digital receiving staff.</p>
                        <ul class="flex flex-col gap-2">
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Validasi ganda saat serah terima dock receiving</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                <span class="font-mono text-xs leading-4 text-[#121A0F]">Bukti e-PoD tersimpan permanen di arsip cloud</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-4">
                        <div class="border-t border-line-hair pt-4">
                            <p class="font-mono text-xs font-bold leading-4 text-success">100% Digital Verifiable</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="banknote" class="h-5 w-5 text-brand" />
                        </span>
                        <h3 class="text-xl font-bold leading-7 text-brand">Flexible B2B Settlement</h3>
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
                            <p class="font-mono text-xs font-bold leading-4 text-success">Corporate Invoicing Ready</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PORTFOLIO KONTRAK AKTIF (DYNAMIC FROM DATABASE) --}}
    <section id="mitra-kontrak" class="scroll-mt-36 border-t border-line-hair bg-[#F5F0E3] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col gap-6 border-b border-line-hair pb-6 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2">
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border border-accent-deep bg-[#EAF2D7] px-3 py-1">
                        <x-gpa.icon name="badge-check" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">PORTFOLIO KONTRAK AKTIF // B2B ENTERPRISE</span>
                    </span>
                    <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">Mitra & Klien Kontrak Terverifikasi</h2>
                    <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">Entitas komersial terkemuka yang mempercayakan kontinuitas pasokan segar harian, stabilitas harga kuartalan, dan jaminan berat riil 100%.</p>
                </div>
                <div class="shrink-0 rounded-xl border border-accent-deep/30 bg-brand p-3">
                    <p class="font-mono text-xs leading-4 text-white">
                        SLA Kepatuhan Pasokan: <span class="text-accent-deep font-bold">99.8%</span>
                    </p>
                    <p class="font-mono text-xs leading-4 text-white">
                        Model Pembayaran: <span class="text-white">TOP 14 - 45 Hari</span>
                    </p>
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($contracts as $index => $contract)
                    @php
                        $client = $contract->user;
                        $clientName = $client ? ($client->company_name ?: $client->name) : 'Mitra Korporat';
                        $words = explode(' ', $clientName);
                        $initials = '';
                        foreach (array_slice($words, 0, 3) as $w) {
                            $initials .= strtoupper(substr($w, 0, 1));
                        }
                    @endphp
                    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2.5">
                                <span class="rounded px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px] bg-[#EAF2D7] text-brand">
                                    {{ $contract->contract_number }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono text-xs font-bold',
                                    'bg-brand text-accent-deep' => $index % 2 === 0,
                                    'bg-success text-white' => $index % 2 === 1,
                                ])>{{ $initials ?: 'GPA' }}</span>
                                <h3 class="text-base font-bold leading-6 text-brand">{{ $clientName }}</h3>
                            </div>
                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">
                                Pasokan rutin komoditas {{ $contract->product->name ?? 'segar' }} terstandarisasi untuk operasional {{ $clientName }}.
                            </p>
                            <div class="flex flex-col gap-1 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Komoditas:</span>
                                    <span class="font-mono text-xs font-bold leading-4 text-[#121A0F]">{{ $contract->product->name ?? 'Aneka Komoditas' }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Volume Pasokan:</span>
                                    <span class="font-mono text-xs font-bold leading-4 text-[#121A0F]">
                                        {{ number_format($contract->committed_volume_per_cycle, 0, ',', '.') }} kg / siklus
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Term Finansial:</span>
                                    <span class="font-mono text-xs font-bold leading-4 text-success">TOP {{ $contract->top_days }} Hari</span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-4">
                            <div class="flex items-center justify-between gap-2 border-t border-line-hair pt-2">
                                <p class="font-mono text-[11px] leading-[16.5px] text-[#5C6B57]">Kepatuhan SLA:
                                    <span class="text-brand font-bold">99.8%</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div
                    class="flex flex-col gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm sm:col-span-2 sm:flex-row sm:items-center sm:justify-between xl:col-span-3">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand">
                            <x-gpa.icon name="file-text" class="h-4 w-4 text-accent-deep" />
                        </span>
                        <div class="flex flex-col gap-0.5">
                            <p class="text-sm font-bold leading-5 text-brand">Ingin Menjadi Mitra Kontrak Komersial?</p>
                            <p class="text-xs leading-4 text-[#5C6B57]">Dapatkan kepastian alokasi komoditas harian, harga flat kuartalan, dan fasilitas TOP resmi B2B.</p>
                        </div>
                    </div>
                    <a href="{{ route('public.mitra-kontrak') }}"
                        class="shrink-0 rounded-xl bg-brand px-5 py-2.5 text-center font-mono text-xs font-bold leading-4 text-accent-deep shadow-sm">
                        Ajukan Kemitraan Kontrak
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- SPESIFIKASI SEKTOR INDUSTRI --}}
    <section class="py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-accent-deep bg-[#EAF2D7] px-4 py-1.5">
                    <x-gpa.icon name="building" class="h-3.5 w-3.5 shrink-0 text-brand" />
                    <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">SPESIFIKASI SEKTOR INDUSTRI</span>
                </span>
                <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">Standar Pasokan Sesuai Karakter Sektor Bisnis</h2>
                <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">Setiap industri menuntut standar sortasi, jendela pengiriman, dan perlakuan cold chain yang berbeda. GPA menyelaraskan seluruh parameter.</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                            <x-gpa.icon name="building" class="h-5 w-5 text-brand" />
                        </span>
                        <div class="flex flex-col gap-0.5 pt-[7px]">
                            <span class="font-mono text-[10px] font-bold uppercase leading-[15px] text-success">SEKTOR 01 // HORECA</span>
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
                            <span class="font-mono text-[10px] font-bold uppercase leading-[15px] text-success">SEKTOR 02 // AIRLINE & INFLIGHT</span>
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
                            <span class="font-mono text-[10px] font-bold uppercase leading-[15px] text-success">SEKTOR 03 // MODERN RETAIL</span>
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
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:px-6" x-data="{ galleryTab: 'semua' }">
            <div class="flex flex-col gap-6 border-b border-line-hair pb-6 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2">
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border border-accent-deep bg-[#EAF2D7] px-3 py-1">
                        <x-gpa.icon name="camera" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">DOKUMENTASI OPERASIONAL</span>
                    </span>
                    <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">Transparansi Lapangan & Rantai Pasok</h2>
                    <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">Arsip dokumentasi nyata dari kebun binaan, proses sortasi packing house, penimbangan tera metrologi, hingga armada distribusi subuh.</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-lg border border-success/30 bg-white px-3 py-1.5">
                        <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                        <span class="font-mono text-xs font-semibold leading-4 text-brand">Audit Visual Lapangan Aktif</span>
                    </span>
                    <span class="rounded-lg bg-brand px-3 py-1.5 font-mono text-xs font-bold leading-4 text-accent-deep">
                        Standard Operasional Terverifikasi
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-line-hair pb-3">
                <button type="button" @click="galleryTab = 'semua'"
                    class="rounded-lg px-3.5 py-1.5 font-mono text-xs leading-4 transition-colors"
                    :class="galleryTab === 'semua' ? 'bg-brand font-bold text-accent-deep shadow-sm' : 'border border-line-hair bg-white font-medium text-[#5C6B57]'">
                    Semua Foto
                </button>
                <button type="button" @click="galleryTab = 'petani'"
                    class="rounded-lg px-3.5 py-1.5 font-mono text-xs leading-4 transition-colors"
                    :class="galleryTab === 'petani' ? 'bg-brand font-bold text-accent-deep shadow-sm' : 'border border-line-hair bg-white font-medium text-[#5C6B57]'">
                    Kebun & Panen
                </button>
                <button type="button" @click="galleryTab = 'mutu'"
                    class="rounded-lg px-3.5 py-1.5 font-mono text-xs leading-4 transition-colors"
                    :class="galleryTab === 'mutu' ? 'bg-brand font-bold text-accent-deep shadow-sm' : 'border border-line-hair bg-white font-medium text-[#5C6B57]'">
                    QC & Tera
                </button>
                <button type="button" @click="galleryTab = 'fasilitas'"
                    class="rounded-lg px-3.5 py-1.5 font-mono text-xs leading-4 transition-colors"
                    :class="galleryTab === 'fasilitas' ? 'bg-brand font-bold text-accent-deep shadow-sm' : 'border border-line-hair bg-white font-medium text-[#5C6B57]'">
                    Packing House
                </button>
                <button type="button" @click="galleryTab = 'logistik'"
                    class="rounded-lg px-3.5 py-1.5 font-mono text-xs leading-4 transition-colors"
                    :class="galleryTab === 'logistik' ? 'bg-brand font-bold text-accent-deep shadow-sm' : 'border border-line-hair bg-white font-medium text-[#5C6B57]'">
                    Armada Subuh
                </button>
            </div>

            <ul class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5"
                    x-show="galleryTab === 'semua' || galleryTab === 'petani'" x-transition.opacity>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase leading-[15px] bg-brand text-accent-deep">Kebun Binaan</span>
                            <span class="font-mono text-xs font-medium leading-4 text-success">Ciwidey & Lembang</span>
                        </div>
                        <div class="flex h-36 flex-col items-center justify-center gap-1 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-3 text-center">
                            <x-gpa.icon name="leaf" class="h-6 w-6 text-success" />
                            <span class="font-mono text-xs font-bold leading-4 text-brand">PANEN SEGAR HIJAU</span>
                            <span class="font-mono text-[9px] leading-[13.5px] text-success">Pemetikan subuh di kebun hidroponik mitra</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <h3 class="text-base font-bold leading-6 text-brand">Panen Selada & Sayuran Daun Hidroponik</h3>
                            <p class="text-xs leading-4 text-[#5C6B57]">Proses panen dini hari oleh petani binaan dengan standar pemotongan akar bersih dan sanitasi keranjang panen.</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-[10px] font-bold leading-[15px] text-success">Panen Subuh 04:00 WIB</p>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5"
                    x-show="galleryTab === 'semua' || galleryTab === 'mutu'" x-transition.opacity>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                            <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase leading-[15px] bg-success text-white">QC & Metrologi</span>
                            <span class="font-mono text-xs font-medium leading-4 text-success">Gudang Konsolidasi Ciwidey</span>
                        </div>
                        <div class="flex h-36 flex-col items-center justify-center gap-1 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-3 text-center">
                            <x-gpa.icon name="gauge" class="h-6 w-6 text-success" />
                            <span class="font-mono text-xs font-bold leading-4 text-brand">TIMBANGAN TERA DIGITAL</span>
                            <span class="font-mono text-[9px] leading-[13.5px] text-success">Segel tera metrologi legal sah</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <h3 class="text-base font-bold leading-6 text-brand">Penimbangan Riil & Kalibrasi Metrologi</h3>
                            <p class="text-xs leading-4 text-[#5C6B57]">Uji timbang netto dengan kompensasi tara otomatis untuk menjamin tidak ada perbedaan bobot invoice dan fisik.</p>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-[10px] font-bold leading-[15px] text-success">Zero Phantom Weight</p>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5"
                    x-show="galleryTab === 'semua' || galleryTab === 'fasilitas'" x-transition.opacity>
                    <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                        <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase leading-[15px] bg-brand text-accent-deep">Packing House</span>
                        <span class="font-mono text-xs font-medium leading-4 text-success">Panundaan Hub</span>
                    </div>
                    <div class="flex h-36 flex-col items-center justify-center gap-1 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-3 text-center">
                        <x-gpa.icon name="building" class="h-6 w-6 text-success" />
                        <span class="font-mono text-xs font-bold leading-4 text-brand">COLD STORAGE HUB</span>
                        <span class="font-mono text-[9px] leading-[13.5px] text-success">Pre-cooling chamber 4°C - 7°C</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="text-base font-bold leading-6 text-brand">Sortir Mutu & Cold Storage Buffer</h3>
                        <p class="text-xs leading-4 text-[#5C6B57]">Sortasi grade kematangan dan penyimpanan sementara pada suhu dingin terjaga guna mencegah dehidrasi komoditas.</p>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-[10px] font-bold leading-[15px] text-success">Thermal Control 4°C</p>
                    </div>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5"
                    x-show="galleryTab === 'semua' || galleryTab === 'logistik'" x-transition.opacity>
                    <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                        <span class="rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase leading-[15px] bg-success text-white">Armada Subuh</span>
                        <span class="font-mono text-xs font-medium leading-4 text-success">Rute Jalur Utama Jawa Barat</span>
                    </div>
                    <div class="flex h-36 flex-col items-center justify-center gap-1 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-3 text-center">
                        <x-gpa.icon name="truck" class="h-6 w-6 text-success" />
                        <span class="font-mono text-xs font-bold leading-4 text-brand">ARMADA REEFER COLD</span>
                        <span class="font-mono text-[9px] leading-[13.5px] text-success">Subuh dispatch 03:00 - 05:30 WIB</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <h3 class="text-base font-bold leading-6 text-brand">Distribusi Logistik Berpendingin</h3>
                        <p class="text-xs leading-4 text-[#5C6B57]">Pengiriman subuh tepat waktu langsung ke receiving dock dapur komersial, hotel, dan pusat distribusi mitra.</p>
                    </div>
                    <div class="pt-4 border-t border-line-hair">
                        <p class="font-mono text-[10px] font-bold leading-[15px] text-success">Dawn Line-Haul (SLA 99.6%)</p>
                    </div>
                </li>

                <li class="flex flex-col gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-bold leading-6 text-brand">Kunjungi Fasilitas Gudang & Kebun GPA</p>
                        <p class="text-xs leading-4 text-[#5C6B57]">Kami membuka pintu bagi tim purchasing dan executive chef untuk verifikasi langsung standar mutu dan legalitas tera.</p>
                    </div>
                    <a href="{{ route('public.about') }}#legalitas"
                        class="mt-auto flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand px-5 py-2.5 font-mono text-xs font-bold leading-4 text-accent-deep shadow-sm">
                        Pelajari Legalitas & Tata Kelola
                    </a>
                </li>
            </ul>
        </div>
    </section>

@endsection
