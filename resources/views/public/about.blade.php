@extends('layouts.public')

@section('title', 'Tentang GPA | AgroOrder GPA')

@section('content')

    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-2/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="space-y-6">

                <h1 class="max-w-4xl text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                    @foreach ($hero['headline'] as $line)
                        <span @class([
                            'block',
                            'text-accent underline underline-offset-4' => ($line['tone'] ?? null) === 'accent',
                            'text-white' => ($line['tone'] ?? null) !== 'accent',
                        ])>{!! nl2br(e($line['text'])) !!}</span>
                    @endforeach
                </h1>

                <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                    {{ str($hero['lead'])->before($hero['emphasis'])->trim() }}
                    <em class="text-white">{{ $hero['emphasis'] }}</em>
                    {{ str($hero['lead'])->after($hero['emphasis'])->trim() }}
                </p>

                <ul class="flex flex-wrap gap-2">
                    @foreach ($hero['assurances'] as $assurance)
                        <li
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-deep/80 px-3 py-1.5 outline outline-1 outline-accent/30 outline-offset-[-1px] gpa-mono-xs text-white/90">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                            {{ $assurance }}
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @foreach ($hero['actions'] as $action)
                        <x-gpa.btn :href="str_starts_with($action['href'], '#') ? $action['href'] : route($action['href'])"
                            :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg">
                            <x-slot:icon>
                                <x-gpa.icon :name="$action['variant'] === 'accent' ? 'navigation' : 'clipboard'" />
                            </x-slot:icon>
                            {{ $action['label'] }}
                        </x-gpa.btn>
                    @endforeach
                </div>

                <x-public.supply-pipeline :pipeline="$hero['pipeline']" />
            </div>
        </div>
    </section>

    <section aria-label="Ringkasan kapasitas" class="border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 md:py-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($stats as $stat)
                    <div class="gpa-panel p-4 shadow-sub">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">
                                {{ $stat['label'] }}
                            </dt>
                            <x-gpa.icon name="dot" class="h-3 w-3 shrink-0 text-success" />
                        </div>
                        <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ $stat['value'] }}</dd>
                        <p class="mt-1 text-xs font-medium text-ink-body">{{ $stat['note'] }}</p>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section id="tentang-gpa" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Arsitektur 4 Pilar Operasional Hulu-ke-Hilir
                    </h2>
                </div>
            </div>

            <ol class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($pillars as $pillar)
                    <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">
                                    {{ $pillar['step'] }}
                                </span>
                                <span
                                    class="rounded bg-canvas px-2 py-0.5 outline outline-1 outline-offset-[-1px] outline-line-hair gpa-mono-xs font-semibold uppercase text-success">
                                    {{ $pillar['tag'] }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold leading-snug text-brand-strong">{{ $pillar['title'] }}</h3>
                            <p class="text-xs leading-relaxed text-ink-body">{{ $pillar['body'] }}</p>
                        </div>

                        <dl class="space-y-1.5 border-t border-line-hair pt-3">
                            @foreach ($pillar['meta'] as $meta)
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="gpa-mono-xs text-ink-subtle">{{ $meta['label'] }}</dt>
                                    <dd class="gpa-mono-xs text-right font-semibold text-brand-strong">{{ $meta['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="komoditas" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Spesifikasi 5 Komoditas Inti Standar GPA
                    </h2>
                    <p class="mt-1 text-xs text-ink-body">
                        Tabel komparasi mutu &amp; parameter ketat penerimaan bahan baku segar untuk standardisasi rantai
                        pasok industri kuliner.
                    </p>
                </div>

            </div>

            @php
                $specColumns = $commodities['columns'];

                $renderers = [
                    'commodity' => fn($row) => view('public.partials.spec-cell', ['variant' => 'commodity', 'value' => $row['commodity']]),
                    'storage' => fn($row) => view('public.partials.spec-cell', ['variant' => 'storage', 'value' => $row['storage']]),
                    'certification' => fn($row) => view('public.partials.spec-cell', ['variant' => 'certification', 'value' => $row['certification']]),
                ];

                foreach ($specColumns as $index => $column) {
                    if (isset($renderers[$column['key']])) {
                        $specColumns[$index]['render'] = $renderers[$column['key']];
                    }
                }
            @endphp

            <x-gpa.table :columns="$specColumns" :rows="$commodities['rows']" density="sm"
                caption="Spesifikasi lima komoditas inti standar GPA: kode, grade mutu, metode timbang, kontrol cold storage, dan sertifikasi."
                empty="Spesifikasi komoditas belum tersedia." class="shadow-sub" />

            <div class="flex flex-col gap-2 px-1 gpa-mono-xs md:flex-row md:items-center md:justify-between">
                <p class="text-ink-body">{{ $commodities['note'] }}</p>
                <p class="font-bold text-success">{{ $commodities['guarantee'] }}</p>
            </div>
        </div>
    </section>

    <section id="legalitas" class="scroll-mt-36 border-y border-line-hair bg-warning-wash">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-2 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Legalitas Badan Usaha, Sertifikasi &amp; Kepatuhan Pajak
                </h2>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                <dl class="grid gap-4 sm:grid-cols-2 lg:col-span-2">
                    @foreach ($legalities['documents'] as $document)
                        <div class="rounded-xl border border-line-hair bg-surface px-5 py-5">
                            <dt class="gpa-mono-xs font-bold uppercase tracking-wide text-success">{{ $document['label'] }}
                            </dt>
                            <dd class="mt-2 text-lg font-bold text-brand-strong gpa-mono-xs">{{ $document['value'] }}</dd>
                            <dd class="mt-1.5 text-xs leading-relaxed text-ink-body">{{ $document['body'] }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div
                    class="flex flex-col justify-between gap-6 rounded-xl border-2 border-brand-strong bg-surface-muted p-5 shadow-sub">
                    <div class="space-y-3">
                        <p class="flex items-center gap-2 gpa-mono-xs font-bold text-brand-strong">
                            <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                            Verifikasi Compliance Resmi
                        </p>
                        <h3 class="text-lg font-extrabold leading-snug text-brand-strong">
                            {{ $legalities['package']['title'] }}
                        </h3>
                        <p class="text-xs leading-relaxed text-ink-body">{{ $legalities['package']['body'] }}</p>
                    </div>

                    <div class="space-y-3">
                        <x-gpa.btn type="button" variant="primary" size="md" block
                            x-on:click="$dispatch('gpa-modal-open', 'legalitas')">
                            <x-slot:icon>
                                <x-gpa.icon name="download" />
                            </x-slot:icon>
                            {{ $legalities['package']['action'] }}
                        </x-gpa.btn>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="rantai-pasok" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Alur Siklus Eksekusi Pasokan 24 Jam HORECA
                    </h2>
                    <p class="mt-1 text-xs text-ink-body">
                        SOP pergerakan komoditas dengan kepastian waktu dan temperatur terkontrol dari hulu ke receiving
                        kitchen.
                    </p>
                </div>

            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($cycle['steps'] as $step)
                    <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">
                                    {{ $step['step'] }}
                                </span>
                                <span class="gpa-mono-xs font-bold text-success">{{ $step['time'] }}</span>
                            </div>

                            <h3 class="text-sm font-bold leading-snug text-brand-strong">{{ $step['title'] }}</h3>
                            <p class="text-xs leading-relaxed text-ink-body">{{ $step['body'] }}</p>
                        </div>

                        <p class="border-t border-line-hair pt-3 gpa-mono-xs font-semibold uppercase text-ink-body">
                            {{ $step['gateway'] }}
                        </p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="cta" class="bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 pb-4 sm:px-6">
            <div
                class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="pointer-events-none absolute -right-24 top-1/4 h-72 w-72 rounded-full bg-accent/15 blur-3xl"
                    aria-hidden="true"></div>

                <div class="relative max-w-3xl space-y-4">
                    <h2 class="text-2xl font-extrabold leading-tight md:text-3xl">{{ $callToAction['headline'] }}</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">{{ $callToAction['body'] }}</p>
                </div>

                <div
                    class="relative mt-8 flex flex-col gap-4 border-t border-white/10 pt-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        @foreach ($callToAction['actions'] as $action)
                            <x-gpa.btn :href="route($action['href'])" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg">
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @endforeach
                    </div>

                    <p class="gpa-mono-xs lg:text-right">
                        <span class="block font-semibold text-accent">{{ $callToAction['hotline']['label'] }}</span>
                        <span class="mt-1 block font-bold text-white">{{ $callToAction['hotline']['value'] }}</span>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <x-gpa.modal name="legalitas" title="Paket Kelengkapan Legalitas Korporasi"
        description="Daftar berkas yang disertakan pada paket profil dan legalitas untuk proses onboarding vendor.">
        <ul class="divide-y divide-line-faint overflow-hidden rounded-lg border border-line">
            @foreach ($legalities['package']['checklist'] as $item)
                <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                    <x-gpa.icon name="check-circle" class="mt-px h-4 w-4 shrink-0 text-success" />
                    <span class="text-xs leading-relaxed text-ink-body">{{ $item }}</span>
                </li>
            @endforeach
        </ul>

        <x-gpa.notice tone="neutral" title="Verifikasi Berkas">
            Checksum SHA-256 berkas diverifikasi otomatis saat proses onboarding. Anda menerima tautan unduhan paket
            dari tim Supply Chain GPA.
            <span class="mt-1 block font-semibold text-ink">{{ $legalities['package']['checksum'] }}</span>
        </x-gpa.notice>

        <x-slot:footer>
            <x-gpa.btn variant="ghost" size="md" x-on:click="$dispatch('gpa-modal-close', 'legalitas')">Tutup</x-gpa.btn>
            <x-gpa.btn :href="route('register')" variant="primary" size="md">
                <x-slot:icon>
                    <x-gpa.icon name="download" />
                </x-slot:icon>
                Daftar sebagai Mitra
            </x-gpa.btn>
        </x-slot:footer>
    </x-gpa.modal>
@endsection
