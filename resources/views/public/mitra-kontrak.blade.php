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

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    @foreach ($hero['actions'] as $action)
                        @if (isset($action['modal']))
                            <x-gpa.btn type="button" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg"
                                x-on:click="$dispatch('gpa-modal-open', '{{ $action['modal'] }}')">
                                <x-slot:icon>
                                    <x-gpa.icon :name="$action['icon']" />
                                </x-slot:icon>
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @else
                            <x-gpa.btn :href="route($action['href'])" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg">
                                <x-slot:icon>
                                    <x-gpa.icon :name="$action['icon']" />
                                </x-slot:icon>
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @endif
                    @endforeach
                </div>
            </div>

            <section aria-label="{{ $hero['index']['title'] }}"
                class="mt-10 rounded-2xl border border-white/15 bg-brand-deep/70 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <p class="flex items-center gap-2 gpa-micro-bold tracking-wider text-accent">
                        <x-gpa.icon name="badge-check" class="h-4 w-4" />
                        {{ $hero['index']['title'] }}
                    </p>
                </div>

                <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($hero['index']['metrics'] as $metric)
                        <div class="rounded-xl border border-white/10 bg-brand/70 p-4">
                            <dt class="gpa-micro-bold tracking-wider text-white/60">{{ $metric['label'] }}</dt>
                            <dd class="mt-2 text-2xl font-extrabold leading-tight text-accent">{{ $metric['value'] }}</dd>
                            <p class="mt-1 gpa-mono-xs text-white/70">{{ $metric['note'] }}</p>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>
    </section>

    <section id="klien" class="scroll-mt-36 border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-ink-strong md:text-2xl">
                        {{ $clients['title'] }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-ink-body">{{ $clients['lead'] }}</p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @foreach ($clients['chips'] as $chip)
                        <span
                            class="inline-flex w-fit items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                            {{ $chip }}
                        </span>
                    @endforeach
                </div>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($clients['cards'] as $card)
                    <li
                        class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">
                                    {{ $card['sector'] }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold leading-snug text-ink-strong">{{ $card['title'] }}</h3>
                            <p class="text-xs leading-relaxed text-ink-body">{{ $card['body'] }}</p>

                            <dl class="space-y-1.5 rounded-lg border border-line-hair bg-canvas px-3 py-2.5">
                                @foreach ($card['details'] as $detail)
                                    <div class="flex items-baseline justify-between gap-3">
                                        <dt class="gpa-mono-xs text-ink-subtle">{{ $detail['label'] }}</dt>
                                        <dd @class([
                                            'gpa-mono-xs text-right font-semibold',
                                            'text-success' => ($detail['tone'] ?? null) === 'success',
                                            'text-ink-strong' => ($detail['tone'] ?? null) !== 'success',
                                        ])>{{ $detail['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-success-soft px-2.5 py-1 gpa-micro-bold text-success-deep">
                                <span class="h-1.5 w-1.5 rounded-full bg-success" aria-hidden="true"></span>
                                {{ $card['badge'] }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="petani" class="scroll-mt-36 border-b border-line-hair bg-surface-muted">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-ink-strong md:text-2xl">
                        {{ $poktan['title'] }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-ink-body">{{ $poktan['lead'] }}</p>
                </div>

                <div
                    class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                    {{ $poktan['total']['label'] }}:
                    <span class="font-bold text-ink-strong">{{ $poktan['total']['value'] }}</span>
                </div>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($poktan['cards'] as $card)
                    <li
                        class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-strong text-accent">
                                    <x-gpa.icon name="leaf" class="h-4 w-4" />
                                </span>
                                <span class="rounded-full bg-success-soft px-2.5 py-0.5 gpa-micro-bold text-success-deep">
                                    Binaan Aktif
                                </span>
                            </div>

                            <h3 class="text-base font-bold leading-snug text-ink-strong">{{ $card['name'] }}</h3>
                            <p class="gpa-mono-xs font-semibold uppercase tracking-wider text-success">{{ $card['region'] }}</p>

                            <ul class="flex flex-col gap-1.5">
                                @foreach ($card['commodities'] as $commodity)
                                    <li class="flex items-center gap-2 text-xs leading-relaxed text-ink-body">
                                        <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                        {{ $commodity }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <dl class="mt-auto space-y-1.5 border-t border-line-hair pt-3">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Luas Lahan</dt>
                                <dd class="gpa-mono-xs font-bold text-ink-strong">{{ $card['area'] }}</dd>
                            </div>
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="gpa-mono-xs text-ink-subtle">Anggota</dt>
                                <dd class="gpa-mono-xs font-bold text-ink-strong">{{ $card['members'] }}</dd>
                            </div>
                        </dl>
                    </li>
                @endforeach
            </ol>

            <p class="gpa-mono-xs text-ink-subtle">{{ $poktan['total']['note'] }}</p>
        </div>
    </section>

    <section id="alur-kontrak" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="mt-1 text-xl font-extrabold uppercase leading-tight text-ink-strong md:text-2xl">
                        {{ $onboarding['title'] }}
                    </h2>
                    <p class="mt-2 max-w-2xl text-xs leading-relaxed text-ink-body">{{ $onboarding['lead'] }}</p>
                </div>

                <span
                    class="inline-flex w-fit shrink-0 items-center rounded-full border border-line-hair bg-surface px-3 py-1 gpa-mono-xs text-ink-body">
                    {{ $onboarding['eyebrow'] }}
                </span>
            </div>

            <ol class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($onboarding['phases'] as $phase)
                    <li class="flex flex-col justify-between gap-4 rounded-xl border border-line-hair bg-surface p-5">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-3">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-strong gpa-mono-xs font-bold text-accent">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="gpa-mono-xs font-semibold uppercase tracking-wider text-success">
                                    {{ $phase['tag'] }}
                                </span>
                            </div>

                            <h3 class="text-sm font-bold leading-snug text-ink-strong">{{ $phase['title'] }}</h3>
                            <p class="text-xs leading-relaxed text-ink-body">{{ $phase['body'] }}</p>
                        </div>

                        <p class="border-t border-line-hair pt-3 gpa-mono-xs text-ink-subtle">
                            {{ $phase['meta']['label'] }}:
                            <span class="font-bold text-ink-strong">{{ $phase['meta']['value'] }}</span>
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
                            @if (isset($action['modal']))
                                <x-gpa.btn type="button" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg"
                                    x-on:click="$dispatch('gpa-modal-open', '{{ $action['modal'] }}')">
                                    {{ $action['label'] }}
                                </x-gpa.btn>
                            @else
                                <x-gpa.btn :href="route($action['href'])" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'" size="lg">
                                    {{ $action['label'] }}
                                </x-gpa.btn>
                            @endif
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

    <x-gpa.modal name="template-pks" :title="$templatePks['title']" :description="$templatePks['description']">
        <ul class="divide-y divide-line-faint overflow-hidden rounded-lg border border-line">
            @foreach ($templatePks['items'] as $item)
                <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                    <x-gpa.icon name="check-circle" class="mt-px h-4 w-4 shrink-0 text-success" />
                    <span class="gpa-mono-xs leading-relaxed text-ink-body">{{ $item }}</span>
                </li>
            @endforeach
        </ul>

        <x-gpa.notice tone="neutral" title="Verifikasi Kontrak">
            {{ $templatePks['footnote'] }}
        </x-gpa.notice>

        <x-slot:footer>
            <x-gpa.btn variant="ghost" size="md" x-on:click="$dispatch('gpa-modal-close', 'template-pks')">Tutup</x-gpa.btn>
            <x-gpa.btn :href="route('register')" variant="primary" size="md">
                <x-slot:icon>
                    <x-gpa.icon name="send" />
                </x-slot:icon>
                Daftar sebagai Mitra
            </x-gpa.btn>
        </x-slot:footer>
    </x-gpa.modal>

@endsection
