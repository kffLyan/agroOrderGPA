@extends('layouts.public')

@section('title', 'Mitra Kontrak | AgroOrder GPA')

@section('content')

    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="max-w-3xl space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full border border-accent/30 bg-brand-deep px-3 py-1 font-mono text-[11px] font-bold text-accent">
                    ENTERPRISE B2B ACCREDITATION REGISTRY
                </span>

                <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                    <span class="block text-white">Portofolio Kemitraan & Klien Kontrak Terverifikasi</span>
                    <span class="block text-accent underline underline-offset-4">AgroOrder GPA</span>
                </h1>

                <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                    Daftar klien korporat dan kelompok tani binaan yang berjalan dengan kontrak terverifikasi. Kepatuhan pasokan, mutu komoditas, dan ketepatan kirim dipantau lewat indikator berkala.
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <x-gpa.btn :href="route('register')" variant="accent" size="lg">
                        <x-slot:icon>
                            <x-gpa.icon name="navigation" />
                        </x-slot:icon>
                        Ajukan Draf Kontrak B2B
                    </x-gpa.btn>
                    <x-gpa.btn type="button" variant="inverse" size="lg"
                        x-on:click="$dispatch('gpa-modal-open', 'template-pks')">
                        <x-slot:icon>
                            <x-gpa.icon name="file-text" />
                        </x-slot:icon>
                        Format Template PKS
                    </x-gpa.btn>
                </div>
            </div>

            <section aria-label="Supply Verification Index"
                class="mt-10 rounded-2xl border border-white/15 bg-brand-deep/70 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <p class="flex items-center gap-2 gpa-micro-bold tracking-wider text-accent">
                        <x-gpa.icon name="badge-check" class="h-4 w-4" />
                        Supply Verification Index
                    </p>
                    <span class="gpa-mono-xs text-white/60">SVI-2025.2 // AUDIT REVIEW</span>
                </div>

                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border border-white/10 bg-brand/70 p-4">
                        <dt class="gpa-micro-bold tracking-wider text-white/60">Fulfillment SLA</dt>
                        <dd class="mt-2 text-2xl font-extrabold leading-tight text-accent">99.8%</dd>
                        <p class="mt-1 gpa-mono-xs text-white/70">Realisasi pasokan subuh</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-brand/70 p-4">
                        <dt class="gpa-micro-bold tracking-wider text-white/60">Klien B2B Aktif</dt>
                        <dd class="mt-2 text-2xl font-extrabold leading-tight text-accent">{{ $clients->count() }} Entitas</dd>
                        <p class="mt-1 gpa-mono-xs text-white/70">Terkontrak & Berjalan</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-brand/70 p-4">
                        <dt class="gpa-micro-bold tracking-wider text-white/60">Mitra Poktan Binaan</dt>
                        <dd class="mt-2 text-2xl font-extrabold leading-tight text-accent">{{ $poktan->count() }} Kelompok</dd>
                        <p class="mt-1 gpa-mono-xs text-white/70">Ciwidey, Lembang & Garut</p>
                    </div>

                    <div class="rounded-xl border border-white/10 bg-brand/70 p-4">
                        <dt class="gpa-micro-bold tracking-wider text-white/60">Reefer Telemetri</dt>
                        <dd class="mt-2 text-2xl font-extrabold leading-tight text-accent">2°C - 4°C</dd>
                        <p class="mt-1 gpa-mono-xs text-white/70">Pemantauan IoT 24 Jam</p>
                    </div>
                </dl>
            </section>
        </div>
    </section>

    {{-- KLIEN & KONTRAK AKTIF (DYNAMIC FROM DATABASE) --}}
    <section id="klien" class="scroll-mt-36 border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-ink-strong md:text-2xl">
                        Klien & Kontrak Aktif Terdaftar
                    </h2>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-ink-body">
                        Entitas bisnis mitra resmi yang berjalan dengan komitmen pasokan terikat, jaminan harga kontrak flat, dan syarat pembayaran tempo.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <span class="inline-flex w-fit items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                        {{ $clients->count() }} Klien B2B Kontrak
                    </span>
                    <span class="inline-flex w-fit items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-success font-bold">
                        100% Terverifikasi
                    </span>
                </div>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($clients as $index => $client)
                    @php
                        $clientName = $client->company_name ?: $client->name;
                        $firstContract = $client->contracts->first();
                        $contractNumber = $firstContract?->contract_number ?? ('KTR-' . date('Y') . '-00' . $client->id);
                        $productsList = $client->contracts->pluck('product.name')->filter()->unique()->join(', ') ?: 'Aneka Hortikultura';
                        $totalCommitted = $client->contracts->sum('committed_volume_per_cycle');
                        $topDays = $firstContract?->top_days ?? 30;
                    @endphp
                    <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="gpa-mono-xs text-ink-subtle">{{ $contractNumber }}</span>
                            </div>

                            <h3 class="text-base font-bold leading-snug text-ink-strong">{{ $clientName }}</h3>
                            <p class="text-xs leading-relaxed text-ink-body">
                                Kemitraan pasokan resmi komoditas hortikultura terstandarisasi untuk operasional {{ $clientName }}.
                            </p>

                            <dl class="space-y-1.5 rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="gpa-mono-xs text-ink-subtle">Komoditas Kontrak:</dt>
                                    <dd class="gpa-mono-xs text-right font-semibold text-ink-strong">{{ $productsList }}</dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="gpa-mono-xs text-ink-subtle">Volume Komitmen:</dt>
                                    <dd class="gpa-mono-xs text-right font-semibold text-ink-strong">
                                        {{ $totalCommitted > 0 ? number_format($totalCommitted, 0, ',', '.') : '0' }} kg / siklus
                                    </dd>
                                </div>
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="gpa-mono-xs text-ink-subtle">Term Pembayaran:</dt>
                                    <dd class="gpa-mono-xs text-right font-semibold text-success">TOP {{ $topDays }} Hari</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-success-soft px-2.5 py-1 gpa-micro-bold text-success-deep">
                                <span class="h-1.5 w-1.5 rounded-full bg-success" aria-hidden="true"></span>
                                Kontrak Aktif
                            </span>
                            <span class="gpa-mono-xs font-bold text-brand">SLA 99.8%</span>
                        </div>
                    </li>
                @empty
                    <li class="col-span-full rounded-xl border border-line-hair bg-surface p-8 text-center text-ink-subtle text-sm">
                        Belum ada daftar mitra kontrak yang aktif saat ini.
                    </li>
                @endforelse
            </ol>
        </div>
    </section>

    {{-- KELOMPOK TANI BINAAN (DYNAMIC FROM DATABASE) --}}
    <section id="petani" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-ink-strong md:text-2xl">
                        Jaringan Kelompok Tani Binaan GPA
                    </h2>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-ink-body">
                        Kelompok tani dan sentra produksi mitra binaan yang menerapkan Good Agricultural Practices (GAP) dan jadwal tanam terkoordinasi.
                    </p>
                </div>

                <div class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                    Total Sentra Binaan: <span class="font-bold text-ink-strong">{{ $poktan->count() }} Kelompok</span>
                </div>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($poktan as $item)
                    @php
                        $name = $item->supplier_name;
                        $region = 'Sentra Ciwidey & Lembang (1.200 - 1.400 mdpl)';
                        if (str_contains(strtolower($name), 'ciwidey') || str_contains(strtolower($name), 'panundaan') || str_contains(strtolower($name), 'alamendah')) {
                            $region = 'Sentra Ciwidey, Kab. Bandung (1.300 mdpl)';
                        } elseif (str_contains(strtolower($name), 'lembang')) {
                            $region = 'Sentra Lembang, Bandung Barat (1.350 mdpl)';
                        } elseif (str_contains(strtolower($name), 'garut') || str_contains(strtolower($name), 'samarang')) {
                            $region = 'Sentra Samarang, Garut (Greenhouse)';
                        } elseif (str_contains(strtolower($name), 'pangalengan')) {
                            $region = 'Sentra Pangalengan, Bandung (1.450 mdpl)';
                        }
                    @endphp
                    <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-strong text-accent">
                                    <x-gpa.icon name="leaf" class="h-4 w-4" />
                                </span>
                                <span class="rounded-full bg-success-soft px-2.5 py-0.5 gpa-micro-bold text-success-deep">
                                    Binaan Terverifikasi
                                </span>
                            </div>

                            <h3 class="text-base font-bold leading-snug text-ink-strong">{{ $name }}</h3>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wider text-success">{{ $region }}</p>

                            <p class="text-xs leading-relaxed text-ink-body">
                                Mitra penghasil komoditas hortikultura segar dengan jadwal petik terkoordinasi dan perlakuan pra-dingin sebelum masuk stasiun timbang.
                            </p>
                        </div>

                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Standar Budidaya:</dt>
                                <dd class="gpa-mono-xs font-bold text-ink-strong">Good Agricultural Practices (GAP)</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Audit Residu:</dt>
                                <dd class="gpa-mono-xs font-bold text-success">Lolos Uji Mutu</dd>
                            </div>
                        </dl>
                    </li>
                @empty
                    <li class="col-span-full rounded-xl border border-line-hair bg-surface p-8 text-center text-ink-subtle text-sm">
                        Belum ada data kelompok tani binaan saat ini.
                    </li>
                @endforelse
            </ol>
        </div>
    </section>

    {{-- ALUR ONBOARDING KONTRAK --}}
    <section id="alur-kontrak" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-ink-strong md:text-2xl">
                        Alur 4 Langkah Onboarding Kemitraan Kontrak
                    </h2>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-ink-body">
                        Prosedur terstruktur legal dan operasional mulai dari pengajuan spesifikasi komoditas hingga jadwal pengiriman perdana.
                    </p>
                </div>

                <span class="inline-flex w-fit shrink-0 items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                    SOP B2B ONBOARDING
                </span>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">01</span>
                        <h3 class="text-sm font-bold leading-snug text-ink-strong">Registrasi & Spesifikasi PO</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Pendaftaran akun klien, penentuan jenis komoditas, grade mutu, dan estimasi tonase mingguan.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs text-brand font-semibold">1 HARI KERJA</p>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">02</span>
                        <h3 class="text-sm font-bold leading-snug text-ink-strong">Uji Sampel & Audit Mutu</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Pengiriman sampel komoditas ke kitchen klien untuk evaluasi rasa, kerenyahan, dan masa simpan.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs text-brand font-semibold">2 HARI KERJA</p>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">03</span>
                        <h3 class="text-sm font-bold leading-snug text-ink-strong">Penandatanganan PKS & TOP</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Persetujuan kontrak harga flat kuartalan, batas kredit, dan termin pembayaran tempo resmi.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs text-brand font-semibold">LEGAL LOCK</p>
                </li>

                <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                    <div class="space-y-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">04</span>
                        <h3 class="text-sm font-bold leading-snug text-ink-strong">Pengiriman Subuh Perdana</h3>
                        <p class="text-xs leading-relaxed text-ink-body">Armada reefer mulai rute terjadwal dengan integrasi aplikasi driver dan penerimaan e-PoD.</p>
                    </div>
                    <p class="border-t border-line-hair pt-3 gpa-mono-xs text-success font-semibold">AKTIF OPERASIONAL</p>
                </li>
            </ol>
        </div>
    </section>

    {{-- MODAL TEMPLATE PKS --}}
    <x-gpa.modal name="template-pks" title="Format Template Perjanjian Kerja Sama (PKS)"
        description="Ringkasan klausul pokok dalam draf kontrak pasokan komersial PT Agro Pasti Ada.">
        <ul class="divide-y divide-line-faint overflow-hidden rounded-lg border border-line">
            <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                <x-gpa.icon name="check-circle" class="mt-px h-4 w-4 shrink-0 text-success" />
                <span class="text-xs leading-relaxed text-ink-body">Klausul Stabilitas Harga: Harga flat terkunci per kuartal tanpa terpengaruh fluktuasi pasar basah.</span>
            </li>
            <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                <x-gpa.icon name="check-circle" class="mt-px h-4 w-4 shrink-0 text-success" />
                <span class="text-xs leading-relaxed text-ink-body">Klausul Bobot Riil: Pembayaran dihitung murni berdasarkan berat timbangan sah saat serah terima dock.</span>
            </li>
            <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                <x-gpa.icon name="check-circle" class="mt-px h-4 w-4 shrink-0 text-success" />
                <span class="text-xs leading-relaxed text-ink-body">Klausul Jaminan Penggantian: Komoditas di bawah grade diganti 100% pada rute berikutnya atau kredit nota.</span>
            </li>
        </ul>

        <x-slot:footer>
            <x-gpa.btn variant="ghost" size="md" x-on:click="$dispatch('gpa-modal-close', 'template-pks')">Tutup</x-gpa.btn>
            <x-gpa.btn :href="route('register')" variant="primary" size="md">
                <x-slot:icon>
                    <x-gpa.icon name="send" />
                </x-slot:icon>
                Ajukan Registrasi Klien
            </x-gpa.btn>
        </x-slot:footer>
    </x-gpa.modal>

@endsection
