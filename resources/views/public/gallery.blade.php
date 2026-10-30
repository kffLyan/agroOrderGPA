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
                <div class="flex flex-col items-center gap-6 text-center md:items-start md:text-left">

                    <h1 class="text-center text-4xl font-extrabold leading-tight sm:text-4xl md:text-left md:text-5xl md:leading-[1.05]">
                        <span class="block text-white">Arsip Dokumentasi Operasional</span>
                        <span class="block text-accent underline underline-offset-4">AgroOrder GPA</span>
                    </h1>

                    <p class="mx-auto max-w-3xl text-center text-base leading-relaxed text-white/90 md:mx-0 md:text-left md:text-lg">
                        Rekaman visual aktivitas hulu hingga hilir: jadwal petik kebun binaan di Ciwidey, penanganan pra-pendinginan, audit kontrol mutu, dan pengiriman armada reefer bersuhu terjaga.
                    </p>

                    <div class="flex w-full max-w-sm flex-col gap-3 pt-2">
                        <x-gpa.btn href="#arsip" variant="accent" size="xl" block>
                            <x-slot:icon>
                                <x-gpa.icon name="search" />
                            </x-slot:icon>
                            Jelajahi Arsip Foto
                        </x-gpa.btn>
                        <x-gpa.btn :href="route('public.catalog')" variant="inverse" size="xl" block>
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
                        <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">Tingkat Ketepatan Pengiriman</dt>
                        <x-gpa.icon name="badge-check" class="h-3.5 w-3.5 shrink-0 text-success" />
                    </div>
                    <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">99.8%</dd>
                    <p class="mt-1 text-xs font-medium text-ink-body">Kepatuhan pasokan B2B</p>
                </div>
            </dl>
        </div>
    </section>

    {{-- ARSIP DOKUMENTASI & FILTER ALPINE --}}
    <section id="arsip" class="scroll-mt-36 bg-surface" x-data="{
        activeTab: 'semua',
        searchQuery: '',
        activeItem: null,
        init() {
            this.$watch('activeTab', () => { this.activeItem = null; });
            this.$watch('searchQuery', () => { this.activeItem = null; });
        },
        matches(category, tags) {
            const matchesCat = this.activeTab === 'semua' || this.activeTab === category;
            const matchesSearch = this.searchQuery.trim() === '' || tags.toLowerCase().includes(this.searchQuery.toLowerCase());
            return matchesCat && matchesSearch;
        }
    }" x-on:click.self="activeItem = null" x-on:keydown.escape.window="activeItem = null">
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

            <div class="mt-6 grid grid-cols-1 gap-1 sm:grid-cols-2 lg:grid-cols-3">
                {{-- CARD 1: KEBUN --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('kebun', 'kebun panen selada hidroponik ciwidey')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 1 ? null : 1">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('lettuce.jpg') }}" alt="Panen Selada Hidroponik Ciwidey" class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 1 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Ciwidey · 1.300 mdpl</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Panen Selada &amp; Daun Salad di Lahan Ciwidey</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Pemetikan selada keriting dan romaine dilakukan pada pagi hari sebelum sinar matahari terik untuk menjaga kadar air sel tanaman tetap maksimal.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 2: KEBUN --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('kebun', 'brokoli sayur brasika lembang kebun')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 2 ? null : 2">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('broccoli.jpg') }}" alt="Kebun Brokoli Lembang" class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 2 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Lembang · 1.350 mdpl</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Sentra Brokoli &amp; Brasika Mitra Lembang</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Kelompok tani binaan menerapkan Good Agricultural Practices (GAP) dengan pengawasan jadwal tanam agar masa panen tidak saling tumpang tindih.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 3: KEBUN / BUAH --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('kebun', 'tomat beef greenhouse ciwidey')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 3 ? null : 3">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('tomato.jpg') }}" alt="Tomat Beef Greenhouse" class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 3 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Greenhouse · Ciwidey</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Kebun</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Kultivasi Tomat Beef Berkualitas Restoran</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Tomat dipetik saat tingkat kematangan 80% dengan ketebalan daging buah terjamin agar tidak pecah selama distribusi logistik ke jaringan burger dan hotel.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 4: GUDANG --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('gudang', 'gudang pre-cooling cold storage hub ciwidey')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 4 ? null : 4">
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)]">
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                            <x-gpa.icon name="building" class="h-10 w-10 text-success" />
                            <span class="mt-2 font-mono text-sm font-bold text-brand-strong">Hub Konsolidasi Ciwidey</span>
                            <span class="font-mono text-xs text-success">Kapasitas 15 Ton / Hari</span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 4 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">PRE-COOLING BLAST CHAMBER</span>
                                <span class="rounded-full bg-brand-strong/90 px-2 py-0.5 gpa-micro-bold text-accent">Gudang</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Ruang Pra-Pendinginan Cepat &amp; Buffer Cold Storage</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Menurunkan panas lapang dari daun sayur dalam 90 menit setelah tiba dari ladang guna menghentikan degradasi nutrisi dan layu daun.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 5: MUTU --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('mutu', 'qc tera timbangan inspeksi mutu metrologi')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 5 ? null : 5">
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)]">
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                            <x-gpa.icon name="gauge" class="h-10 w-10 text-success" />
                            <span class="mt-2 font-mono text-sm font-bold text-brand-strong">Stasiun Tera &amp; QC Mutu</span>
                            <span class="font-mono text-xs text-success">Tera Sah Bersertifikat</span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 5 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">TOLERANSI CACAT &le; 2%</span>
                                <span class="rounded-full bg-accent/90 px-2 py-0.5 gpa-micro-bold text-brand-strong">Mutu QC</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Inspeksi Grade A &amp; Penimbangan Digital Legal</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Setiap krat melalui uji organoleptik, pembersihan sisa akar, dan penimbangan dengan alat tera resmi yang disegel oleh dinas metrologi.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 6: LOGISTIK --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('logistik', 'armada mobil box pendingin reefer logistik')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 6 ? null : 6">
                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)]">
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-4 text-center">
                            <x-gpa.icon name="truck" class="h-10 w-10 text-success" />
                            <span class="mt-2 font-mono text-sm font-bold text-brand-strong">Armada Reefer Box Cold-Chain</span>
                            <span class="font-mono text-xs text-success">Suhu Kargo 2°C - 4°C Terjaga</span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 6 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">DISPATCH SUBUH · BANDUNG - JKT</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Logistik</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Armada Mobil Box Berpendingin &amp; IoT Tracker</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Unit armada pengangkut dilengkapi unit pendingin thermo ganda dan sensor nirkabel yang memonitor suhu kargo hingga tiba di dock kitchen pelanggan.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 7: PANEN --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('kebun', 'panen selada segar kebun sayuran')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 7 ? null : 7">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('images/activities/panen.jpg') }}" alt="Panen Selada Segar"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 7 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Kebun Sayuran</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Panen</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Panen Selada Segar</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Selada segar dipilih langsung dari kebun untuk menjaga kualitas daun sebelum memasuki proses penanganan berikutnya.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 8: LOGISTIK --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('logistik', 'pemuatan sayuran distribusi kendaraan angkut')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 8 ? null : 8">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('images/activities/muatan.jpg') }}" alt="Pemuatan Sayuran untuk Distribusi"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 8 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Persiapan Distribusi</span>
                                <span class="rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-deep">Logistik</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Pemuatan Sayuran untuk Distribusi</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Sayuran yang sudah dikemas ditata pada kendaraan angkut sebagai bagian dari proses pengiriman kepada pelanggan.
                            </p>
                        </div>
                    </div>
                </article>

                {{-- CARD 9: INSPEKSI --}}
                <article class="relative overflow-hidden cursor-pointer md:group"
                    x-show="matches('mutu', 'inspeksi tanaman area budidaya pemeriksaan')" x-transition.opacity
                    @click.stop="activeItem = activeItem === 9 ? null : 9">
                    <div class="relative aspect-[4/3] w-full overflow-hidden">
                        <img src="{{ asset('images/activities/inspeksi.jpeg') }}" alt="Inspeksi Tanaman di Area Budidaya"
                            class="h-full w-full object-cover transition duration-500 md:group-hover:scale-105" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-4 transition-all duration-300"
                            :class="activeItem === 9 ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4 md:group-hover:opacity-100 md:group-hover:translate-y-0'">
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs text-accent">Area Budidaya</span>
                                <span class="rounded-full bg-accent/90 px-2 py-0.5 gpa-micro-bold text-brand-strong">Inspeksi</span>
                            </div>
                            <h3 class="text-sm font-bold leading-snug text-white sm:text-base">Inspeksi Tanaman di Area Budidaya</h3>
                            <p class="mt-1 text-xs leading-relaxed text-white/90">
                                Petugas memeriksa tanaman dan mencatat hasil pengamatan sebagai bagian dari pemantauan proses budidaya.
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="relative mx-auto max-w-3xl space-y-4 text-center">
                    <h2 class="text-center text-2xl font-extrabold leading-tight md:text-3xl">Tertarik Mengunjungi Sentra Produksi Kami?</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">
                        Klien B2B dan calon mitra korporat dapat menjadwalkan kunjungan lapangan (farm visit) dan audit hub konsolidasi bersama tim agraria AgroOrder GPA.
                    </p>
                </div>

                <div class="relative mt-8 flex flex-col items-center gap-3 border-t border-white/10 pt-6">
                    <div class="flex w-full max-w-sm flex-col gap-3">
                        <x-gpa.btn :href="route('public.kontak')" variant="accent" size="xl" block>
                            Hubungi Kami
                        </x-gpa.btn>
                        <x-gpa.btn :href="route('register')" variant="inverse" size="xl" block>
                            Daftar Klien Kontrak
                        </x-gpa.btn>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
