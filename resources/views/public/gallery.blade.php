@extends('layouts.public')

@section('title', 'Galeri & Dokumentasi Operasional | AgroOrder GPA')

@section('content')

    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_448px] lg:items-start">
                <div class="flex flex-col items-start gap-6">
                    <span class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-brand-deep px-3 py-1 font-mono text-[11px] font-bold text-accent">
                        FIELD &amp; COLD-CHAIN DOCUMENTATION
                    </span>

                    <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                        <span class="block text-white">Arsip Dokumentasi Operasional</span>
                        <span class="block text-accent underline underline-offset-4">AgroOrder GPA</span>
                    </h1>

                    <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                        Rekaman visual aktivitas hulu hingga hilir: jadwal petik kebun binaan di Ciwidey, penanganan pra-pendinginan, audit kontrol mutu, dan pengiriman armada reefer bersuhu terjaga.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <x-gpa.btn href="#arsip" variant="accent" size="lg">
                            <x-slot:icon>
                                <x-gpa.icon name="search" />
                            </x-slot:icon>
                            Jelajahi Arsip Foto
                        </x-gpa.btn>
                        <x-gpa.btn :href="route('public.catalog')" variant="inverse" size="lg">
                            <x-slot:icon>
                                <x-gpa.icon name="navigation" />
                            </x-slot:icon>
                            Katalog Komoditas
                        </x-gpa.btn>
                    </div>
                </div>

                <aside aria-label="Live feed arsip" class="rounded-2xl border border-white/15 bg-brand-deep/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            STATUS OPERASIONAL REAL-TIME
                        </p>
                        <span class="gpa-mono-xs text-white/60">LIVE METRICS</span>
                    </div>

                    <dl class="mt-4 flex flex-col gap-2.5">
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-brand/70 px-3.5 py-3">
                            <dt class="gpa-mono-xs uppercase tracking-wider text-white/50">Stok Buffer Gudang</dt>
                            <dd class="text-right">
                                <span class="block text-sm font-bold leading-5 text-white">{{ number_format($totalVolume, 0, ',', '.') }} kg</span>
                                <span class="block gpa-mono-xs leading-4 text-white/60">Siap Kirim</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-brand/70 px-3.5 py-3">
                            <dt class="gpa-mono-xs uppercase tracking-wider text-white/50">Komoditas Terdaftar</dt>
                            <dd class="text-right">
                                <span class="block text-sm font-bold leading-5 text-white">{{ $totalProducts }} Varietas</span>
                                <span class="block gpa-mono-xs leading-4 text-white/60">Aktif &amp; Terverifikasi</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-brand/70 px-3.5 py-3">
                            <dt class="gpa-mono-xs uppercase tracking-wider text-white/50">Total Batch Panen</dt>
                            <dd class="text-right">
                                <span class="block text-sm font-bold leading-5 text-white">{{ $totalBatches }} Batch</span>
                                <span class="block gpa-mono-xs leading-4 text-white/60">Lot Terpantau</span>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-white/10 pt-4">
                        <span class="flex items-center gap-1.5 gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                            SISTEM LOGISTIK TERHUBUNG
                        </span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- STATS STRIP --}}
    <section aria-label="Ringkasan arsip dokumentasi" class="border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 md:py-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Sentra Lahan Produksi</dt>
                        <x-gpa.icon name="leaf" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">1.200 - 1.400 mdpl</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Ciwidey, Lembang &amp; Garut</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Suhu Ruang Cold Storage</dt>
                        <x-gpa.icon name="gauge" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">2°C – 4°C</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Kontrol kelembapan 90-95%</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Pemberangkatan Armada</dt>
                        <x-gpa.icon name="truck" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">03:00 WIB</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Pengiriman subuh harian</p>
                </div>

                <div class="gpa-panel p-4 shadow-sub">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Tingkat Ketepatan SLA</dt>
                        <x-gpa.icon name="badge-check" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">99.8%</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Kepatuhan pasokan B2B</p>
                </div>
            </dl>
        </div>
    </section>

    {{-- ARSIP DOKUMENTASI & FILTER ALPINE --}}
    <section id="arsip" class="scroll-mt-36 bg-surface-muted" x-data="{
        activeTab: 'semua',
        searchQuery: '',
        matches(category, tags) {
            const matchesCat = this.activeTab === 'semua' || this.activeTab === category;
            const matchesSearch = this.searchQuery.trim() === '' || tags.toLowerCase().includes(this.searchQuery.toLowerCase());
            return matchesCat && matchesSearch;
        }
    }">
        <div class="border-b border-line-hair bg-surface">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 px-4 py-3.5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0">
                    <button type="button" @click="activeTab = 'semua'"
                        class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 font-mono text-xs leading-4 transition-colors"
                        :class="activeTab === 'semua' ? 'bg-brand font-bold text-accent shadow-sm' : 'border border-line-hair bg-white font-medium text-ink-body hover:border-line-board'">
                        Semua Arsip
                    </button>
                    <button type="button" @click="activeTab = 'kebun'"
                        class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 font-mono text-xs leading-4 transition-colors"
                        :class="activeTab === 'kebun' ? 'bg-brand font-bold text-accent shadow-sm' : 'border border-line-hair bg-white font-medium text-ink-body hover:border-line-board'">
                        Kebun &amp; Panen
                    </button>
                    <button type="button" @click="activeTab = 'gudang'"
                        class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 font-mono text-xs leading-4 transition-colors"
                        :class="activeTab === 'gudang' ? 'bg-brand font-bold text-accent shadow-sm' : 'border border-line-hair bg-white font-medium text-ink-body hover:border-line-board'">
                        Gudang &amp; Cold Storage
                    </button>
                    <button type="button" @click="activeTab = 'mutu'"
                        class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 font-mono text-xs leading-4 transition-colors"
                        :class="activeTab === 'mutu' ? 'bg-brand font-bold text-accent shadow-sm' : 'border border-line-hair bg-white font-medium text-ink-body hover:border-line-board'">
                        Inspeksi Mutu QC
                    </button>
                    <button type="button" @click="activeTab = 'logistik'"
                        class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 font-mono text-xs leading-4 transition-colors"
                        :class="activeTab === 'logistik' ? 'bg-brand font-bold text-accent shadow-sm' : 'border border-line-hair bg-white font-medium text-ink-body hover:border-line-board'">
                        Armada Logistik
                    </button>
                </div>

                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-72">
                        <x-gpa.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
                        <input type="search" x-model="searchQuery" aria-label="Cari arsip dokumentasi" placeholder="Cari foto, komoditas, lokasi..."
                            class="h-9 w-full rounded-lg border border-line-hair bg-white pl-9 pr-3 font-mono text-xs text-ink placeholder:font-sans placeholder:text-ink-subtle focus:border-brand focus:outline-none" />
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-2 border-b-2 border-brand-strong pb-3 sm:flex-row sm:items-end sm:justify-between">
                <p class="flex items-center gap-2 gpa-mono-xs font-bold uppercase tracking-wider text-brand-strong">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
                    <span>GALERI KEGIATAN &amp; FASILITAS OPERASIONAL</span>
                </p>
                <span class="gpa-mono-xs text-ink-subtle">AGROORDER GPA ARCHIVES</span>
            </div>

            <ul class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {{-- CARD 1: KEBUN --}}
                <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                    x-show="matches('kebun', 'kebun panen selada hidroponik ciwidey')" x-transition.opacity>
                    <div class="relative h-48 overflow-hidden rounded-xl border border-line-hair bg-surface-muted">
                        <img src="{{ asset('lettuce.jpg') }}" alt="Panen Selada Hidroponik Ciwidey" class="h-full w-full object-cover" />
                        <span class="absolute bottom-2 left-2 rounded-md bg-brand-deep/80 px-2 py-0.5 gpa-mono-xs text-accent">
                            Ciwidey · 1.300 mdpl
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="gpa-mono-xs text-ink-subtle">SOP PANEN SUBUH · 05:00 WIB</span>
                        <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                    </div>
                    <h3 class="text-base font-bold leading-snug text-brand-strong">Panen Selada &amp; Daun Salad di Lahan Ciwidey</h3>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Pemetikan selada keriting dan romaine dilakukan pada pagi hari sebelum sinar matahari terik untuk menjaga kadar air sel tanaman tetap maksimal.
                    </p>
                </li>

                {{-- CARD 2: KEBUN --}}
                <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                    x-show="matches('kebun', 'brokoli sayur brasika lembang kebun')" x-transition.opacity>
                    <div class="relative h-48 overflow-hidden rounded-xl border border-line-hair bg-surface-muted">
                        <img src="{{ asset('broccoli.jpg') }}" alt="Kebun Brokoli Lembang" class="h-full w-full object-cover" />
                        <span class="absolute bottom-2 left-2 rounded-md bg-brand-deep/80 px-2 py-0.5 gpa-mono-xs text-accent">
                            Lembang · 1.350 mdpl
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="gpa-mono-xs text-ink-subtle">KULTIVASI GAP · BEBAS RESIDU</span>
                        <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                    </div>
                    <h3 class="text-base font-bold leading-snug text-brand-strong">Sentra Brokoli &amp; Brasika Mitra Lembang</h3>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Kelompok tani binaan menerapkan Good Agricultural Practices (GAP) dengan pengawasan jadwal tanam agar masa panen tidak saling tumpang tindih.
                    </p>
                </li>

                {{-- CARD 3: KEBUN / BUAH --}}
                <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                    x-show="matches('kebun', 'tomat beef greenhouse ciwidey')" x-transition.opacity>
                    <div class="relative h-48 overflow-hidden rounded-xl border border-line-hair bg-surface-muted">
                        <img src="{{ asset('tomato.jpg') }}" alt="Tomat Beef Greenhouse" class="h-full w-full object-cover" />
                        <span class="absolute bottom-2 left-2 rounded-md bg-brand-deep/80 px-2 py-0.5 gpa-mono-xs text-accent">
                            Greenhouse · Ciwidey
                        </span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="gpa-mono-xs text-ink-subtle">SORTASI DAGING BUAH &amp; BRIX</span>
                        <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                    </div>
                    <h3 class="text-base font-bold leading-snug text-brand-strong">Kultivasi Tomat Beef Berkualitas Restoran</h3>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Tomat dipetik saat tingkat kematangan 80% dengan ketebalan daging buah terjamin agar tidak pecah selama distribusi logistik ke jaringan burger dan hotel.
                    </p>
                </li>

                {{-- CARD 4: GUDANG --}}
                <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                    x-show="matches('gudang', 'gudang pre-cooling cold storage hub ciwidey')" x-transition.opacity>
                    <div class="relative flex h-48 flex-col items-center justify-center rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-4 text-center">
                        <x-gpa.icon name="building" class="h-10 w-10 text-success" />
                        <span class="mt-2 font-mono text-sm font-bold text-brand-strong">Hub Konsolidasi Ciwidey</span>
                        <span class="font-mono text-xs text-success">Kapasitas 15 Ton / Hari</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="gpa-mono-xs text-ink-subtle">PRE-COOLING BLAST CHAMBER</span>
                        <span class="rounded-full bg-brand-strong/10 px-2 py-0.5 gpa-micro-bold text-brand-strong">Gudang</span>
                    </div>
                    <h3 class="text-base font-bold leading-snug text-brand-strong">Ruang Pra-Pendinginan Cepat &amp; Buffer Cold Storage</h3>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Menurunkan panas lapang dari daun sayur dalam 90 menit setelah tiba dari ladang guna menghentikan degradasi nutrisi dan layu daun.
                    </p>
                </li>

                {{-- CARD 5: MUTU --}}
                <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                    x-show="matches('mutu', 'qc tera timbangan inspeksi mutu metrologi')" x-transition.opacity>
                    <div class="relative flex h-48 flex-col items-center justify-center rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-4 text-center">
                        <x-gpa.icon name="gauge" class="h-10 w-10 text-success" />
                        <span class="mt-2 font-mono text-sm font-bold text-brand-strong">Stasiun Tera &amp; QC Mutu</span>
                        <span class="font-mono text-xs text-success">Tera Sah Bersertifikat</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="gpa-mono-xs text-ink-subtle">TOLERANSI CACAT &le; 2%</span>
                        <span class="rounded-full bg-accent/20 px-2 py-0.5 gpa-micro-bold text-brand-strong">Mutu QC</span>
                    </div>
                    <h3 class="text-base font-bold leading-snug text-brand-strong">Inspeksi Grade A &amp; Penimbangan Digital Legal</h3>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Setiap krat melalui uji organoleptik, pembersihan sisa akar, dan penimbangan dengan alat tera resmi yang disegel oleh dinas metrologi.
                    </p>
                </li>

                {{-- CARD 6: LOGISTIK --}}
                <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                    x-show="matches('logistik', 'armada mobil box pendingin reefer logistik')" x-transition.opacity>
                    <div class="relative flex h-48 flex-col items-center justify-center rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-4 text-center">
                        <x-gpa.icon name="truck" class="h-10 w-10 text-success" />
                        <span class="mt-2 font-mono text-sm font-bold text-brand-strong">Armada Reefer Box Cold-Chain</span>
                        <span class="font-mono text-xs text-success">Suhu Kargo 2°C - 4°C Terjaga</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="gpa-mono-xs text-ink-subtle">DISPATCH SUBUH · BANDUNG - JKT</span>
                        <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Logistik</span>
                    </div>
                    <h3 class="text-base font-bold leading-snug text-brand-strong">Armada Mobil Box Berpendingin &amp; IoT Tracker</h3>
                    <p class="text-xs leading-relaxed text-ink-body">
                        Unit armada pengangkut dilengkapi unit pendingin thermo ganda dan sensor nirkabel yang memonitor suhu kargo hingga tiba di dock kitchen pelanggan.
                    </p>
                </li>
            </ul>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="relative mx-auto max-w-3xl space-y-4 text-center">
                    <h2 class="text-2xl font-extrabold leading-tight md:text-3xl">Tertarik Mengunjungi Sentra Produksi Kami?</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">
                        Klien B2B dan calon mitra korporat dapat menjadwalkan kunjungan lapangan (farm visit) dan audit hub konsolidasi bersama tim agraria AgroOrder GPA.
                    </p>
                </div>

                <div class="relative mt-8 flex flex-wrap items-center justify-center gap-3 border-t border-white/10 pt-6">
                    <x-gpa.btn :href="route('public.kontak')" variant="accent" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="navigation" />
                        </x-slot:icon>
                        Jadwalkan Kunjungan / Hubungi Kami
                    </x-gpa.btn>
                    <x-gpa.btn :href="route('register')" variant="inverse" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="clipboard" />
                        </x-slot:icon>
                        Daftar Klien Kontrak
                    </x-gpa.btn>
                </div>
            </div>
        </div>
    </section>

@endsection
