@extends('layouts.public')

@section('title', 'Rantai Pasok Terintegrasi | AgroOrder GPA')

@section('content')

    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">

            <div class="max-w-4xl space-y-6">

                <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                    @foreach ($hero['headline'] as $line)
                        <span @class([
                            'block',
                            'text-accent underline underline-offset-4' => ($line['tone'] ?? null) === 'accent',
                            'text-white' => ($line['tone'] ?? null) !== 'accent',
                        ])>{{ $line['text'] }}</span>
                    @endforeach
                </h1>

                <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                    {{ $hero['lead'] }}
                </p>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @foreach ($hero['actions'] as $action)
                        <x-gpa.btn :href="$action['href']" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'"
                            size="lg">
                            <x-slot:icon>
                                <x-gpa.icon :name="$action['variant'] === 'accent' ? 'navigation' : 'clipboard'" />
                            </x-slot:icon>
                            {{ $action['label'] }}
                        </x-gpa.btn>
                    @endforeach
                </div>
            </div>

            <x-public.supply-blueprint :blueprint="$hero['blueprint']" />
        </div>
    </section>

    <section aria-label="Ringkasan kontrol rantai pasok" class="border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 md:py-8">
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($metrics as $metric)
                    <div class="gpa-panel p-4 shadow-sub">
                        <div class="flex items-center justify-between gap-2">
                            <dt class="gpa-mono-xs font-semibold uppercase tracking-wide text-ink-body">
                                {{ $metric['label'] }}</dt>
                            <x-gpa.icon :name="$metric['icon']" class="h-3.5 w-3.5 shrink-0 text-success" />
                        </div>
                        <dd class="mt-2 text-2xl font-extrabold leading-tight text-brand-strong">{{ $metric['value'] }}</dd>
                        <p class="mt-1 text-xs font-medium text-ink-body">{{ $metric['note'] }}</p>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section id="alur-pasok" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        Diagram Alur Eksekusi PRD Pasokan Terintegrasi
                    </h2>
                </div>

                <span
                    class="inline-flex w-fit shrink-0 items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                    6 Tahap Berhasil Diverifikasi Sistem
                </span>
            </div>

            <x-public.node-rail :nodes="$nodes" />
        </div>
    </section>

    <section id="tahap-pasok" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-2 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Rincian Eksekusi Enam Tahap Hulu-ke-Hilir
                </h2>
            </div>

            <ol class="grid gap-4 xl:grid-cols-2">
                @foreach ($stages as $stage)
                    <li>
                        <x-public.stage-card :stage="$stage" />
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="kontrol-mutu" class="scroll-mt-36 border-y border-line-hair bg-warning-wash">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-2 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    Spesifikasi Standar Kontrol Mutu &amp; Cold-Chain
                </h2>
            </div>

            <div class="overflow-hidden rounded-xl border border-line-hair bg-surface shadow-sub">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-brand px-4 py-3 gpa-mono-xs">
                    <p class="flex items-center gap-2 font-bold uppercase tracking-wider text-white">
                        <span class="h-2 w-2 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        {{ $qualityControl['title'] }}
                    </p>
                </div>

                @php
                    $qcColumns = $qualityControl['columns'];

                    $qcRenderers = [
                        'metric' => fn($row) => view('public.partials.qc-metric', ['value' => $row['metric']]),
                        'status' => fn($row) => view('public.partials.qc-status', ['status' => $row['status']]),
                    ];

                    foreach ($qcColumns as $index => $column) {
                        if (isset($qcRenderers[$column['key']])) {
                            $qcColumns[$index]['render'] = $qcRenderers[$column['key']];
                        }
                    }
                @endphp

                <x-gpa.table :columns="$qcColumns" :rows="$qualityControl['rows']" density="sm"
                    class="rounded-none shadow-none"
                    caption="Parameter ambang batas kontrol mutu dan cold-chain AgroOrder GPA untuk seluruh komoditas inti." />

                <div
                    class="flex flex-col gap-1 border-t border-line-hair px-4 py-3 gpa-mono-xs md:flex-row md:items-center md:justify-between">
                    <p class="text-ink-body">{{ $qualityControl['source'] }}</p>
                    <p class="font-bold text-success">{{ $qualityControl['auditReady'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="retur-parsial" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-5 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                        {{ $returnProtocol['title'] }}
                    </h2>
                </div>

            </div>

            <ol class="grid gap-4 lg:grid-cols-3">
                @foreach ($returnProtocol['steps'] as $index => $step)
                    <li class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5">
                        <div class="flex items-center gap-3 border-b border-line-hair pb-3">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <h3 class="text-sm font-bold leading-snug text-brand-strong">{{ $step['title'] }}</h3>
                        </div>

                        <p class="text-xs leading-relaxed text-ink-body">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>

            <div
                class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface-muted p-5 shadow-sub md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <p class="gpa-mono-xs font-bold uppercase tracking-wider text-brand-strong">
                        {{ $returnProtocol['stamp']['label'] }}</p>
                    <p class="text-xs leading-relaxed text-ink-body">{{ $returnProtocol['stamp']['note'] }}</p>
                </div>

                <p
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-brand-strong px-3 py-1 gpa-mono-xs font-bold text-accent">
                    <x-gpa.icon name="badge-check" class="h-3.5 w-3.5" />
                    {{ $returnProtocol['stamp']['status'] }}
                </p>
            </div>
        </div>
    </section>

    <section id="infrastruktur" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 text-center sm:px-6 md:py-14">
            <div class="mx-auto max-w-2xl space-y-2">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    {{ $infrastructure['title'] }}
                </h2>
                <p class="text-sm text-ink-body">{{ $infrastructure['lead'] }}</p>
            </div>

            <dl class="grid gap-4 text-left md:grid-cols-3">
                @foreach ($infrastructure['cards'] as $card)
                    <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <dt class="flex items-center gap-3 border-b border-line-hair pb-3">
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-strong text-accent">
                                <x-gpa.icon :name="$card['icon']" class="h-4 w-4" />
                            </span>
                            <span
                                class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">{{ $card['label'] }}</span>
                        </dt>

                        <dd class="text-xl font-extrabold leading-tight text-brand-strong">{{ $card['value'] }}</dd>
                        <p class="text-xs leading-relaxed text-ink-body">{{ $card['body'] }}</p>
                        <p class="mt-auto pt-2 text-2xs font-medium text-ink-subtle">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    <section id="cta" class="bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 pb-4 sm:px-6">
            <div
                class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="pointer-events-none absolute -right-24 top-1/4 h-72 w-72 rounded-full bg-accent/15 blur-3xl"
                    aria-hidden="true"></div>

                <div class="relative mx-auto max-w-3xl space-y-4 text-center">

                    <h2 class="text-2xl font-extrabold leading-tight md:text-3xl">{{ $callToAction['headline'] }}</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">{{ $callToAction['body'] }}</p>
                </div>

                <div class="relative mt-8 flex flex-wrap items-center justify-center gap-3 border-t border-white/10 pt-6">
                    @foreach ($callToAction['actions'] as $action)
                        @if (isset($action['modal']))
                            <x-gpa.btn type="button" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg"
                                x-on:click="$dispatch('gpa-modal-open', '{{ $action['modal'] }}')">
                                <x-slot:icon>
                                    <x-gpa.icon name="download" />
                                </x-slot:icon>
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @else
                            <x-gpa.btn :href="route($action['route'])" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg">
                                <x-slot:icon>
                                    <x-gpa.icon name="send" />
                                </x-slot:icon>
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-gpa.modal name="sop" :title="$callToAction['procedure']['title']"
        :description="$callToAction['procedure']['description']">
        <ul class="divide-y divide-line-faint overflow-hidden rounded-lg border border-line">
            @foreach ($callToAction['procedure']['items'] as $item)
                <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                    <x-gpa.icon name="check-circle" class="mt-px h-4 w-4 shrink-0 text-success" />
                    <span class="gpa-mono-xs leading-relaxed text-ink-body">{{ $item }}</span>
                </li>
            @endforeach
        </ul>

        <x-gpa.notice tone="neutral" title="Verifikasi Berkas">
            {{ $callToAction['procedure']['footnote'] }}
        </x-gpa.notice>

        <x-slot:footer>
            <x-gpa.btn variant="ghost" size="md" x-on:click="$dispatch('gpa-modal-close', 'sop')">Tutup</x-gpa.btn>
            <x-gpa.btn :href="route('register')" variant="primary" size="md">
                <x-slot:icon>
                    <x-gpa.icon name="send" />
                </x-slot:icon>
                Daftar sebagai Mitra
            </x-gpa.btn>
        </x-slot:footer>
    </x-gpa.modal>
@endsection
