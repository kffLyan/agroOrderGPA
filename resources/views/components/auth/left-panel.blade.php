{{--
    Left trust panel shared by the login & register pages.
    Structure: bg #153A01, decorative blur orbs, eyebrow badge, headline,
    lead, four highlight cards, and a compliance footer bar.
--}}
<section
    class="relative flex flex-col justify-center overflow-hidden border-b border-[#245204] bg-brand p-8 md:p-12 lg:col-span-5 lg:border-b-0 lg:border-r lg:p-14">
    <div class="pointer-events-none absolute left-[244px] top-[-96px] h-96 w-96 rounded-full bg-[rgba(78,122,3,0.20)] blur-[32px]"
        aria-hidden="true"></div>
    <div class="pointer-events-none absolute left-0 top-[491px] h-80 w-80 rounded-full bg-[rgba(170,189,6,0.10)] blur-[20px]"
        aria-hidden="true"></div>

    <div class="relative flex flex-col gap-3 pb-10">
        {{-- System Identity Badge --}}

        <div class="flex flex-col gap-3 pt-3">
            <h1 class="text-[30px] font-bold leading-9 text-white">
                Portal Terpadu Rantai Pasok<br>Agribisnis
            </h1>
            <p class="max-w-lg text-sm leading-5 text-surface-disabled/80">
                Satu platform digital untuk logistik
                pertanian, penimbangan presisi, dan
                verifikasi mutu real-time.
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
                    <h2 class="text-sm font-semibold leading-5 text-white">Data Rantai Pasok Terpusat</h2>
                    <p class="text-xs leading-4 text-line-board">
                        Data dari lahan produksi, hub
                        konsolidasi, hingga titik bongkar
                        pembeli selalu tersinkron.
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
                        <h2 class="text-sm font-semibold leading-5 text-white">Penimbangan Akurat</h2>
                    </div>
                    <p class="text-xs leading-4 text-line-board">
                        Jembatan timbang digital bersertifikat
                        metrologi legal mengurangi selisih
                        berat barang.
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
                        <h2 class="text-sm font-semibold leading-5 text-white">Riwayat Audit Permanen</h2>
                    </div>
                    <p class="text-xs leading-4 text-line-board">
                        Setiap perubahan manifest, mutu, dan
                        faktur tercatat permanen serta tidak
                        dapat diubah.
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
                        <h2 class="text-sm font-semibold leading-5 text-white">Akses Sesuai Peran</h2>
                    </div>
                    <p class="text-xs leading-4 text-line-board">
                        Setiap peran hanya mendapat akses
                        sesuai tugasnya demi tata kelola
                        yang rapi.
                    </p>
                </div>
            </div>
        </div>
    </div>

</section>
