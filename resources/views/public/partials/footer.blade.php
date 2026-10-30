<footer id="kontak" class="mt-14 scroll-mt-36 bg-brand text-white">
    <div class="border-t border-white/10">
        <div class="mx-auto w-full max-w-6xl space-y-10 px-4 py-12 sm:px-6 md:py-14">
            <div class="grid gap-10 md:grid-cols-3">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent text-brand-deep">
                            <x-gpa.icon name="leaf" class="h-5 w-5" />
                        </span>
                        <span class="text-xl font-extrabold uppercase leading-none">AgroOrder GPA</span>
                    </div>

                    <p class="max-w-md text-sm leading-relaxed text-white/90">
                        Mitra rantai pasok hortikultura terstandarisasi untuk Horeka, Katering Korporat, dan Ritel Modern dengan standar jaminan berat riil anti-susut, metrologi legal, dan logistik subuh.
                    </p>

                    <dl class="space-y-1 pt-2 gpa-mono-xs">
                        <dd class="text-white/80">GREEN PASUNDAN AGRICULTURE</dd>
                        <dd class="text-white/80">Badan Hukum Kemenkumham RI No. AHU-0029141.AH.01.01.TAHUN 2023</dd>
                        <dd class="text-white/80">NIB: 1908230048192 | KBLI 46311 (Perdagangan Besar Buah & Sayur)</dd>
                    </dl>
                </div>

                <div class="space-y-3">
                    <h2 class="gpa-mono-xs font-bold uppercase tracking-wider text-white">Hub Operasional</h2>

                    <ul class="space-y-4">
                        <li class="space-y-1">
                            <p class="flex items-center gap-2.5 gpa-mono-xs font-bold text-accent">
                                <x-gpa.icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                                Gudang Konsolidasi Ciwidey
                            </p>
                            <p class="pl-6 text-2xs leading-relaxed text-white/80">
                                Desa Panundaan RT 03/02, Ciwidey, Kab. Bandung, Jawa Barat 40973
                            </p>
                        </li>
                        <li class="space-y-1">
                            <p class="flex items-center gap-2.5 gpa-mono-xs font-bold text-accent">
                                <x-gpa.icon name="map-pin" class="h-3.5 w-3.5 shrink-0" />
                                STA Konsolidasi Lembang
                            </p>
                            <p class="pl-6 text-2xs leading-relaxed text-white/80">
                                Jl. Raya Tangkuban Parahu, Lembang, Kab. Bandung Barat, Jawa Barat 40391
                            </p>
                        </li>
                    </ul>

                    <p class="pt-1 text-2xs leading-relaxed text-white/90">
                        Hotline Pengadaan: +62 811-1223-3445 (Desk Sekretariat)<br>
                        Email Resmi: operasional@greenpasundan.id
                    </p>
                </div>

                <div class="space-y-3">
                    <h2 class="gpa-mono-xs font-bold uppercase tracking-wider text-white">Navigasi Publik</h2>

                    <ul class="space-y-2.5">
                        <li>
                            <a href="{{ route('public.home') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Beranda Utama</a>
                        </li>
                        <li>
                            <a href="{{ route('public.about') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Tentang Korporat</a>
                        </li>
                        <li>
                            <a href="{{ route('public.supply-chain') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Rantai Pasok PRD</a>
                        </li>
                        <li>
                            <a href="{{ route('public.catalog') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Katalog Komoditas</a>
                        </li>
                        <li>
                            <a href="{{ route('public.mitra-kontrak') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Mitra & Klien Kontrak</a>
                        </li>
                        <li>
                            <a href="{{ route('public.gallery') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Dokumentasi Galeri</a>
                        </li>
                        <li>
                            <a href="{{ route('public.kontak') }}" class="gpa-mono-xs text-white/80 transition-colors hover:text-accent">Kontak Layanan</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col gap-4 border-t border-white/10 pt-8 text-2xs text-white/60 sm:flex-row sm:items-center sm:justify-between">
                <p>Hak Cipta © {{ date('Y') }} Green Pasundan Agriculture. Dilindungi Undang-Undang.</p>
                <p>Metrologi Tera Sah Balai Standardisasi Metrologi Legal Kementerian Perdagangan RI.</p>
            </div>
        </div>
    </div>
</footer>