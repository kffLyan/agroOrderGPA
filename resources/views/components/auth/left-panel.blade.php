{{--
    Left trust panel shared by the login & register pages.
    Structure: bg #153A01, decorative blur orbs, eyebrow badge, headline,
    lead, four highlight cards, and a compliance footer bar.
--}}
<section
    class="relative flex flex-col justify-between overflow-hidden border-b border-[#245204] bg-brand p-8 md:p-12 lg:col-span-5 lg:border-b-0 lg:border-r lg:p-14">
    <div class="pointer-events-none absolute left-[244px] top-[-96px] h-96 w-96 rounded-full bg-[rgba(78,122,3,0.20)] blur-[32px]"
        aria-hidden="true"></div>
    <div class="pointer-events-none absolute left-0 top-[491px] h-80 w-80 rounded-full bg-[rgba(170,189,6,0.10)] blur-[20px]"
        aria-hidden="true"></div>

    <div class="relative flex flex-col gap-3 pb-10">
        {{-- System Identity Badge --}}
        <span
            class="inline-flex w-fit items-center rounded bg-[#0F2801] px-2.5 py-1 outline outline-1 outline-accent-deep/30 [outline-offset:-1px]">
            <span class="h-[11.67px] w-[9.33px] bg-accent-deep" aria-hidden="true"></span>
            <span class="pl-2 font-mono text-[10px] font-semibold uppercase leading-[15px] tracking-[0.5px] text-accent-deep">
                Command &amp; Traceability System
            </span>
        </span>

        <div class="flex flex-col gap-3 pt-3">
            <h1 class="text-[30px] font-bold leading-9 text-white">
                Portal Terpadu Rantai Pasok<br>Agribisnis
            </h1>
            <p class="max-w-lg text-sm leading-5 text-surface-disabled/80">
                Infrastruktur digital enterprise untuk pengelolaan logistik<br>
                agrikultur end-to-end, penimbangan metrologi presisi, dan<br>
                verifikasi sertifikasi batch real-time.
            </p>
        </div>

        {{-- Highlight Cards --}}
        <div class="flex max-w-lg flex-col gap-4 pt-5">
            <div
                class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                <span class="pt-0.5">
                    <span
                        class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                        <span class="material-symbols-outlined text-[11px] text-accent-deep">database</span>
                    </span>
                </span>
                <div class="flex flex-col gap-1.5 pl-3">
                    <h2 class="text-sm font-semibold leading-5 text-white">Single Source of Truth Rantai Pasok</h2>
                    <p class="text-xs leading-4 text-line-board">
                        Sinkronisasi data multi-titik dari lahan produksi, hub<br>
                        konsolidasi, hingga titik bongkar pembeli.
                    </p>
                </div>
            </div>

            <div
                class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                <span class="pt-0.5">
                    <span
                        class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                        <span class="material-symbols-outlined text-[11px] text-accent-deep">scale</span>
                    </span>
                </span>
                <div class="flex flex-col gap-1.5 pl-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold leading-5 text-white">Kepatuhan Penimbangan Riil</h2>
                        <span
                            class="rounded bg-success px-1.5 py-0.5 font-mono text-[9px] font-medium leading-[13.5px] text-white">
                            Rule 04 &amp; 05 Net Weight
                        </span>
                    </div>
                    <p class="text-xs leading-4 text-line-board">
                        Integrasi jembatan timbang digital bersertifikasi metrologi legal<br>
                        mencegah deviasi tara.
                    </p>
                </div>
            </div>

            <div
                class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                <span class="pt-0.5">
                    <span
                        class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                        <span class="material-symbols-outlined text-[11px] text-accent-deep">description</span>
                    </span>
                </span>
                <div class="flex flex-col gap-1.5 pl-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold leading-5 text-white">Audit Trail Forensik Kriptografis</h2>
                        <span
                            class="rounded bg-accent-deep px-1.5 py-0.5 font-mono text-[9px] font-bold leading-[13.5px] text-brand">
                            SHA-256 (Rule 09)
                        </span>
                    </div>
                    <p class="text-xs leading-4 text-line-board">
                        Setiap mutasi manifest, penyesuaian mutu, dan faktur<br>
                        ditandatangani hash permanen.
                    </p>
                </div>
            </div>

            <div
                class="flex items-start gap-3 rounded bg-[#0F2801]/70 p-3.5 outline outline-1 outline-[#2D5C07] [outline-offset:-1px]">
                <span class="pt-0.5">
                    <span
                        class="flex h-5 w-5 items-center justify-center rounded bg-accent-deep/20 outline outline-1 outline-accent-deep [outline-offset:-1px]">
                        <span class="material-symbols-outlined text-[11px] text-accent-deep">group</span>
                    </span>
                </span>
                <div class="flex flex-col gap-1.5 pl-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold leading-5 text-white">Otorisasi Berbasis Peran</h2>
                        <span
                            class="rounded bg-[#245204] px-1.5 py-0.5 font-mono text-[9px] font-medium leading-[13.5px] text-accent outline outline-1 outline-accent-deep/30 [outline-offset:-1px]">
                            RBAC 5 Aktor
                        </span>
                    </div>
                    <p class="text-xs leading-4 text-line-board">
                        Pemisahan wewenang operasional ketat untuk integritas tata<br>
                        kelola korporat.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Compliance Footer Bar --}}
    <div class="relative flex items-center justify-between gap-2 self-stretch border-t border-[#245204] pt-4">
        <span class="flex items-center">
            <span class="h-3 w-2.5 bg-accent-deep" aria-hidden="true"></span>
            <span class="pl-2 font-mono text-[11px] leading-[16.5px] text-[#A6D389]/90">
                ISO 27001 &amp; Metrologi RI 2024
            </span>
        </span>
        <span class="font-mono text-[11px] font-semibold leading-[16.5px] tracking-[0.55px] text-accent-deep">
            ENC: AES-GCM 256
        </span>
    </div>
</section>
