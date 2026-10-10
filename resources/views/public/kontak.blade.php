@extends('layouts.public')

@section('title', 'Kontak | AgroOrder GPA')

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_384px] lg:items-start">
                <div class="flex flex-col items-start gap-6">

                    <h1 class="text-3xl font-extrabold leading-tight sm:text-4xl md:text-5xl md:leading-[1.05]">
                        @foreach ($hero['headline'] as $line)
                            <span @class([
                                'block',
                                'text-accent underline underline-offset-4' => $line['tone'] === 'accent',
                                'text-white' => $line['tone'] !== 'accent',
                            ])>{{ $line['text'] }}</span>
                        @endforeach
                    </h1>

                    <p class="max-w-3xl text-base leading-relaxed text-white/90 md:text-lg">
                        {{ $hero['lead'] }}
                    </p>
                </div>

                <aside aria-label="Komitmen waktu tanggap"
                    class="rounded-2xl border border-white/15 bg-brand-deep/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            {{ $hero['sla']['commitment'] }}
                        </p>
                        <span class="flex items-center gap-1.5 gpa-mono-xs font-bold uppercase tracking-wider text-white">
                            <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                            {{ $hero['sla']['status'] }}
                        </span>
                    </div>

                    <p class="mt-4 text-sm leading-relaxed text-white/80">
                        {{ $hero['sla']['prefix'] }}
                        <strong class="font-bold text-white">{{ $hero['sla']['highlight'] }}</strong>
                        {{ $hero['sla']['suffix'] }}
                    </p>
                </aside>
            </div>
        </div>
    </section>

    {{-- DIREKTORI & FORMULIR --}}
    <section id="direktori" class="scroll-mt-36 border-b border-line-hair bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-14">
            <div class="grid gap-6 lg:grid-cols-2 lg:items-start">
                <div class="flex flex-col gap-4">
                    <div
                        class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                        <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                            {{ $directory['title'] }}
                        </h2>

                    </div>

                    @foreach ($directory['cards'] as $card)
                        <article class="rounded-2xl border border-line-hair bg-white p-5 shadow-sub md:p-6">
                            <header class="flex items-start justify-between gap-3 border-b border-line-hair pb-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-canvas">
                                        <x-gpa.icon :name="$card['icon']" class="h-5 w-5 text-success" />
                                    </span>
                                    <h3 class="text-base font-bold leading-snug text-brand-strong">
                                        {{ $card['title'] }}
                                    </h3>
                                </div>

                            </header>

                            <div class="mt-4 flex flex-col gap-3">
                                @foreach ($card['rows'] as $row)
                                    @php
                                        $isStack = $row['stack'] ?? false;
                                        $isMulti = count($row['cells']) > 1;
                                    @endphp
                                    <div @class([
                                        'flex flex-col gap-3',
                                        'sm:flex-row' => $isMulti && ! $isStack,
                                        'border-t border-line-hair pt-3' => $row['divided'] ?? false,
                                        'border-l-2 border-success pl-4' => ($row['accent'] ?? '') === 'left',
                                    ])>
                                        @foreach ($row['cells'] as $cell)
                                            <div @class(['sm:flex-1' => $isMulti && ! $isStack])>
                                                <p class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                                    {{ $cell['label'] }}
                                                </p>
                                                <div class="mt-1 space-y-0.5 text-sm leading-relaxed text-[#2C3527]">
                                                    @foreach ($cell['lines'] as $line)
                                                        <p>{{ $line }}</p>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>

                            @if (isset($card['highlight']))
                                <div class="mt-4 rounded-xl border border-line-hair bg-[#EFF4DC] p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                            {{ $card['highlight']['label'] }}
                                        </p>
                                        <span
                                            class="rounded-full bg-success px-2 py-0.5 gpa-mono-xs font-bold text-white">
                                            {{ $card['highlight']['badge'] }}
                                        </span>
                                    </div>
                                    <p class="mt-1.5 font-mono text-lg font-bold text-brand-strong">
                                        {{ $card['highlight']['value'] }}
                                    </p>
                                    <p class="mt-2 border-t border-line-hair pt-2 text-xs leading-relaxed text-ink-body">
                                        {{ $card['highlight']['note'] }}
                                    </p>
                                </div>
                            @endif

                            @if (isset($card['note']))
                                <div class="mt-4 rounded-xl border border-line-hair bg-[#EFF4DC]/60 p-4">
                                    <p class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                        {{ $card['note']['label'] }}
                                    </p>
                                    <p class="mt-1 text-sm font-semibold leading-relaxed text-brand-strong">
                                        {{ $card['note']['value'] }}
                                    </p>
                                </div>
                            @endif
                        </article>
                    @endforeach

                    <article class="rounded-2xl border border-line-hair bg-warning-cream p-5 shadow-sub md:p-6">
                        <header class="flex items-center gap-3 border-b border-line-hair pb-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white">
                                <x-gpa.icon :name="$directory['channels']['icon']" class="h-5 w-5 text-success" />
                            </span>
                            <h3 class="text-base font-bold leading-snug text-brand-strong">
                                {{ $directory['channels']['title'] }}
                            </h3>
                        </header>

                        <ul class="mt-4 flex flex-col gap-2.5">
                            @foreach ($directory['channels']['rows'] as $row)
                                <li
                                    class="flex flex-col gap-1 rounded-lg border border-line-hair bg-white/80 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                    <div>
                                        <p class="text-sm font-semibold leading-5 text-brand-strong">
                                            {{ $row['title'] }}
                                        </p>
                                        <p class="gpa-mono-xs mt-0.5 text-ink-subtle">{{ $row['subtitle'] }}</p>
                                    </div>
                                    <p class="font-mono text-xs font-bold text-success sm:text-right">
                                        {{ $row['value'] }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </article>
                </div>

                <div id="formulir" class="scroll-mt-36 flex flex-col">
                    <div
                        class="flex w-full flex-col gap-6 rounded-2xl border-2 border-success/80 bg-white p-6 shadow-sub md:p-8">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                        </div>

                        <div class="space-y-2 border-b border-line-hair pb-5">
                            <h2 class="text-lg font-extrabold leading-snug text-brand-strong md:text-xl">
                                {{ $form['title'] }}
                            </h2>
                            <p class="text-sm leading-relaxed text-ink-body">{{ $form['lead'] }}</p>
                        </div>

                        <form class="flex flex-col gap-5"
                            x-on:submit.prevent="$dispatch('gpa:toast', { title: 'Permohonan Kemitraan Terkirim', message: 'Tim representatif GPA akan menghubungi Anda dalam waktu maksimal 2 jam kerja.', tone: 'success' })">
                            @foreach ($form['rows'] as $row)
                                <div @class([
                                    'flex flex-col gap-4',
                                    'sm:flex-row' => count($row['fields']) > 1,
                                ])>
                                    @foreach ($row['fields'] as $field)
                                        <div @class(['sm:flex-1' => count($row['fields']) > 1])>
                                            <label for="kontak-{{ $field['name'] }}"
                                                class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                                {{ $field['label'] }}
                                                @if ($field['required'])
                                                    <span class="text-success">*</span>
                                                @endif
                                            </label>

                                            @if ($field['type'] === 'select')
                                                <div class="relative mt-1.5">
                                                    <select id="kontak-{{ $field['name'] }}"
                                                        name="{{ $field['name'] }}"
                                                        {{ $field['required'] ? 'required' : '' }}
                                                        class="w-full appearance-none rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 pr-10 text-sm text-ink focus:border-success focus:outline-none">
                                                        <option value="" selected disabled>{{ $field['placeholder'] }}</option>
                                                    </select>
                                                    <x-gpa.icon name="chevron-down"
                                                        class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
                                                </div>
                                            @else
                                                <input id="kontak-{{ $field['name'] }}" name="{{ $field['name'] }}"
                                                    type="{{ $field['type'] }}" {{ $field['required'] ? 'required' : '' }}
                                                    placeholder="{{ $field['placeholder'] }}"
                                                    class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            <fieldset>
                                <legend class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                    {{ $form['commodities']['label'] }}
                                </legend>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($form['commodities']['options'] as $option)
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-line-hair bg-canvas/50 px-3 py-2 text-sm text-ink-body">
                                            <input type="checkbox" name="komoditas[]"
                                                value="{{ $option['label'] }}" @checked($option['checked'])
                                                class="h-4 w-4 rounded border-line-hair accent-success" />
                                            {{ $option['label'] }}
                                        </label>
                                    @endforeach
                                </div>

                                <label for="kontak-volume"
                                    class="gpa-mono-xs mt-4 block font-bold uppercase tracking-wider text-[#5E6953]">
                                    {{ $form['commodities']['volumeLabel'] }}
                                </label>
                                <input id="kontak-volume" name="volume" type="text"
                                    placeholder="{{ $form['commodities']['volumePlaceholder'] }}"
                                    class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none" />
                            </fieldset>

                            <fieldset>
                                <legend class="gpa-mono-xs font-bold uppercase tracking-wider text-[#5E6953]">
                                    {{ $form['payments']['label'] }}
                                </legend>

                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($form['payments']['options'] as $option)
                                        <label
                                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-line-hair bg-canvas/50 px-3 py-2 text-sm text-ink-body">
                                            <input type="radio" name="pembayaran" value="{{ $option['label'] }}"
                                                @checked($option['checked']) class="h-4 w-4 border-line-hair accent-success" />
                                            {{ $option['label'] }}
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div>
                                <label for="kontak-catatan"
                                    class="gpa-mono-xs block font-bold uppercase tracking-wider text-[#5E6953]">
                                    {{ $form['notes']['label'] }}
                                </label>
                                <textarea id="kontak-catatan" name="catatan" rows="4"
                                    placeholder="{{ $form['notes']['placeholder'] }}"
                                    class="mt-1.5 w-full rounded-lg border border-line-hair bg-canvas/50 px-3.5 py-2.5 text-sm leading-relaxed text-ink placeholder:text-ink-subtle focus:border-success focus:outline-none"></textarea>
                            </div>

                            <div class="flex flex-col gap-3 border-t border-line-hair pt-5">
                                <x-gpa.btn type="submit" variant="accent" size="lg" block>
                                    <x-slot:icon>
                                        <x-gpa.icon name="send" />
                                    </x-slot:icon>
                                    {{ $form['submit'] }}
                                </x-gpa.btn>

                                <p class="text-center text-xs leading-relaxed text-ink-subtle">
                                    {{ $form['footnote'] }}
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- DENAH AKSES LOGISTIK --}}
    <section id="denah" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl space-y-6 px-4 py-12 sm:px-6 md:py-14">
            <div
                class="flex flex-col gap-3 border-b-2 border-brand-strong pb-3 md:flex-row md:items-end md:justify-between">
                <h2 class="text-xl font-extrabold uppercase leading-tight text-brand-strong md:text-2xl">
                    {{ $schematic['title'] }}
                </h2>

            </div>

            <div class="rounded-2xl border border-line-hair bg-white p-5 shadow-sub md:p-8">
                <div class="flex flex-col gap-2 border-b border-line-hair pb-4 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                </div>

                <div class="mt-5 rounded-xl border-2 border-success/40 bg-[#EFF4DC]/40 p-4 md:p-6">
                    <div class="flex flex-col items-stretch gap-3 md:flex-row md:items-center md:gap-0">
                        @foreach ($schematic['nodes'] as $index => $node)
                            <div @class([
                                'rounded-xl border-2 p-4 md:w-52 md:shrink-0',
                                'border-success bg-white' => $node['tone'] === 'light',
                                'border-brand-deep bg-brand' => $node['tone'] === 'dark',
                            ])>
                                <p @class([
                                    'gpa-mono-xs font-bold uppercase tracking-wider',
                                    'text-success' => $node['tone'] === 'light',
                                    'text-accent' => $node['tone'] === 'dark',
                                ])>{{ $node['label'] }}</p>

                                <p @class([
                                    'mt-2 text-sm font-extrabold leading-snug',
                                    'text-brand-strong' => $node['tone'] === 'light',
                                    'text-white' => $node['tone'] === 'dark',
                                ])>{{ $node['title'] }}</p>

                                <p @class([
                                    'mt-1 text-xs leading-relaxed',
                                    'text-ink-body' => $node['tone'] === 'light',
                                    'text-white/80' => $node['tone'] === 'dark',
                                ])>{{ $node['desc'] }}</p>

                                <p @class([
                                    'mt-2.5 rounded border px-2 py-1 gpa-mono-xs font-semibold',
                                    'border-line-hair bg-canvas text-ink-subtle' => $node['tone'] === 'light',
                                    'border-white/20 bg-white/10 text-white/80' => $node['tone'] === 'dark',
                                ])>{{ $node['chip'] }}</p>
                            </div>

                            @if (isset($schematic['arrows'][$index]))
                                <div class="flex flex-col items-center gap-1 px-2 py-1 md:w-28 md:shrink-0 md:px-0">
                                    <span class="gpa-mono-xs text-center font-semibold text-ink-body">
                                        {{ $schematic['arrows'][$index]['top'] }}
                                    </span>
                                    <span class="font-mono text-sm font-bold tracking-widest text-success">
                                        ════►
                                    </span>
                                    <span class="gpa-mono-xs text-center text-ink-subtle">
                                        {{ $schematic['arrows'][$index]['bottom'] }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($schematic['notes'] as $note)
                        <div class="rounded-xl border border-line-hair bg-canvas/40 p-4">
                            <p class="gpa-mono-xs font-bold uppercase tracking-wider text-success">
                                {{ $note['label'] }}
                            </p>
                            <p class="mt-1.5 text-xs leading-relaxed text-ink-body">{{ $note['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

@endsection
