@extends('layouts.public')

@section('title', 'Beranda | AgroOrder GPA')

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden border-b border-accent-deep/20 bg-brand text-white">
        <div class="pointer-events-none absolute left-[576px] top-0 h-96 w-96 rounded-full bg-success/20 blur-2xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-10 top-[460px] h-80 w-80 rounded-full bg-accent-deep/10 blur-xl"
            aria-hidden="true"></div>

        <div
            class="relative mx-auto flex w-full max-w-6xl flex-col gap-6 px-4 py-20 sm:px-6 lg:grid lg:grid-cols-[minmax(0,1fr)_490px] lg:items-start">
            <div class="flex flex-col items-start gap-6">

                <h1 class="text-3xl font-extrabold leading-9 sm:text-[44px] sm:leading-10">
                    @foreach ($hero['headline'] as $line)
                        <span @class([
                            'block',
                            'text-accent-deep underline' => $line['tone'] === 'accent',
                        ])>{!! nl2br(e($line['text'])) !!}</span>
                    @endforeach
                </h1>

                <p class="max-w-[672px] text-lg leading-7 text-[#D9E4C0]">{{ $hero['lead'] }}</p>

                <div class="flex flex-wrap items-center gap-3.5 pt-2">
                    @foreach ($hero['actions'] as $action)
                        @if ($action['variant'] === 'accent')
                            <a href="{{ route($action['href']) }}"
                                class="inline-flex items-center gap-2 rounded-xl bg-accent-deep px-6 py-3.5 font-mono text-xs font-bold text-brand shadow-lg">
                                <x-gpa.icon :name="$action['icon']" class="h-4 w-4 shrink-0 text-brand" />
                                {{ $action['label'] }}
                            </a>
                        @else
                            <a href="{{ $action['href'] }}"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-3.5 font-mono text-xs font-bold text-white outline outline-1 outline-accent-deep/50 [outline-offset:-1px]">
                                <x-gpa.icon :name="$action['icon']" class="h-4 w-4 shrink-0 text-accent-deep" />
                                {{ $action['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>

                <div class="flex w-full items-start gap-3 rounded-xl border border-accent-deep/40 bg-brand-deep/80 p-4">
                    <x-gpa.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-accent-deep" />
                    <div class="flex flex-col gap-1.5">
                        <p class="font-mono text-xs font-bold leading-[19.5px] text-white">{{ $hero['note']['title'] }}</p>
                        <p class="text-xs leading-[19.5px] text-[#D9E4C0]">{{ $hero['note']['body'] }}</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 rounded-2xl border-2 border-accent-deep/50 bg-white p-6 shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-line-hair pb-3">
                    <div>
                        <p class="text-sm font-bold leading-5 text-brand">{{ $hero['schematic']['title'] }}</p>
                    </div>
                </div>

                <ol class="flex flex-col gap-2.5">
                    @foreach ($hero['schematic']['flow'] as $index => $row)
                        <li @class([
                            'flex items-start justify-between gap-3 rounded-xl p-3',
                            'border border-line-hair bg-[#F8FAF3]' => !($row['highlighted'] ?? false),
                            'border-2 border-brand bg-[#EAF2D7]' => $row['highlighted'] ?? false,
                        ])>
                            <div class="flex items-start gap-3">
                                <span @class([
                                    'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded font-mono text-xs font-bold',
                                    'bg-brand text-accent-deep' => !($row['highlighted'] ?? false),
                                    'bg-accent-deep text-brand' => $row['highlighted'] ?? false,
                                ])>{{ $row['step'] }}</span>
                                <div>
                                    <p @class([
                                        'text-xs leading-4 text-brand',
                                        'font-bold' => !($row['highlighted'] ?? false),
                                        'font-extrabold' => $row['highlighted'] ?? false,
                                    ])>{{ $row['title'] }}</p>
                                    <p @class([
                                        'text-[11px] leading-[16.5px]',
                                        'text-[#5C6B57]' => !($row['highlighted'] ?? false),
                                        'font-medium text-[#225608]' => $row['highlighted'] ?? false,
                                    ])>{{ $row['body'] }}</p>
                                </div>
                            </div>
                            <span @class([
                                'shrink-0 rounded px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px]',
                                'bg-[#EAF2D7] text-brand' => $row['tone'] === 'light',
                                'bg-success text-white' => $row['tone'] === 'success',
                                'bg-[#D9E4C0] text-brand' => $row['tone'] === 'muted',
                                'bg-brand text-accent-deep' => $row['tone'] === 'ink',
                            ])>{{ $row['tag'] }}</span>
                        </li>
                        @if (!$loop->last)
                            <li class="flex justify-center"><span class="block h-3.5 w-2.5 bg-success"></span></li>
                        @endif
                    @endforeach
                </ol>

                <div class="flex items-center justify-between gap-3 border-t border-line-hair pt-3">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-accent-deep" aria-hidden="true"></span>
                        <span class="font-mono text-[10px] font-bold leading-[15px] text-success">
                            {{ $hero['schematic']['footer']['label'] }}</span>
                    </span>
                    <span class="font-mono text-[10px] font-bold leading-[15px] text-brand">
                        {{ $hero['schematic']['footer']['value'] }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- SOP & METODOLOGI OPERASIONAL --}}
    <section class="pb-20 pt-10">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-accent-deep bg-[#EAF2D7] px-4 py-1.5">
                    <x-gpa.icon :name="$sop['icon']" class="h-3.5 w-3.5 shrink-0 text-brand" />
                    <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">
                        {{ $sop['eyebrow'] }}</span>
                </span>
                <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">{{ $sop['headline'] }}</h2>
                <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">{{ $sop['lead'] }}</p>
            </div>

            <ol class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($sop['cards'] as $card)
                    <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2.5">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand font-mono text-xs font-bold text-accent-deep">
                                    {{ $card['step'] }}</span>
                                <span class="font-mono text-[11px] font-bold uppercase leading-[16.5px] text-success">
                                    {{ $card['stage'] }}</span>
                            </div>
                            <h3 class="text-base font-bold leading-6 text-brand">{{ $card['title'] }}</h3>
                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">{!! nl2br(e($card['body'])) !!}</p>
                        </div>
                        <div class="pt-4">
                            <div class="flex items-center justify-between gap-2 border-t border-line-hair pt-2">
                                <span class="font-mono text-[11px] leading-[16.5px] text-[#5C6B57]">
                                    {{ $card['metric']['label'] }}</span>
                                <span class="font-mono text-[11px] font-bold leading-[16.5px] text-brand">
                                    {{ $card['metric']['value'] }}</span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- KATALOG PANEN TERKINI --}}
    <section id="komoditas" class="scroll-mt-36 border-y border-line-hair bg-[#EFF4DC] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col gap-6 border-b border-line-hair pb-6 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2">
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border border-accent-deep bg-[#EAF2D7] px-3 py-1">
                        <x-gpa.icon :name="$catalog['icon']" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">
                            {{ $catalog['eyebrow'] }}</span>
                    </span>
                    <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">{{ $catalog['headline'] }}</h2>
                    <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">{{ $catalog['lead'] }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                    @foreach ($catalog['chips'] as $chip)
                        @if ($chip['tone'] === 'light')
                            <span
                                class="rounded-lg border border-success/30 bg-white px-3 py-1.5 font-mono text-xs font-semibold leading-4 text-brand">
                                {{ $chip['label'] }}</span>
                        @else
                            <span class="rounded-lg bg-brand px-3 py-1.5 font-mono text-xs font-bold leading-4 text-accent-deep">
                                {{ $chip['label'] }}</span>
                        @endif
                    @endforeach
                </div>
            </div>

            <ul class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($catalog['cards'] as $card)
                    <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex flex-col gap-0.5 pt-1.5">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">{{ $card['sku'] }}</span>
                                    <h3 class="text-lg font-bold leading-[24.75px] text-brand">{{ $card['name'] }}</h3>
                                </div>
                                <span @class([
                                    'shrink-0 rounded-full px-2.5 py-0.5 text-center font-mono text-[11px] font-bold leading-[16.5px]',
                                    'bg-brand text-accent-deep' => $card['gradeTone'] === 'ink',
                                    'bg-accent text-brand outline outline-1 outline-success/30 [outline-offset:-1px]' => $card['gradeTone'] === 'accent',
                                ])>{{ $card['grade'] }}</span>
                            </div>

                            <div
                                class="flex h-36 flex-col items-center justify-center gap-1 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-3 text-center">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                                    <x-gpa.icon name="leaf" class="h-4 w-4 text-success" />
                                </span>
                                <span class="font-mono text-xs font-bold leading-4 text-brand">{{ $card['image'] }}</span>
                                <span class="font-mono text-[10px] leading-[15px] text-success">{{ $card['caption'] }}</span>
                            </div>

                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">{!! nl2br(e($card['body'])) !!}</p>

                            <div class="flex flex-col gap-1.5 border-t border-line-hair pt-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Min. Pemesanan:</span>
                                    <span class="font-mono text-xs font-bold leading-4 text-[#121A0F]">
                                        {{ $card['minOrder'] }}</span>
                                </div>
                                <div class="grid grid-cols-2 items-center gap-2">
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">Status Stok Live:</span>
                                    @if ($card['statusTone'] === 'warning')
                                        <span
                                            class="justify-self-start rounded bg-[#FFFBEB] px-2 py-0.5 font-mono text-[11px] font-bold leading-4 text-[#704602] outline outline-1 outline-[#FCD34D] [outline-offset:-1px]">
                                            {{ $card['status'] }}</span>
                                    @else
                                        <span class="flex items-center gap-1.5 justify-self-start">
                                            <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                                            <span class="font-mono text-xs font-bold leading-4 text-brand">
                                                {{ $card['status'] }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="pt-5">
                            <div class="flex items-center justify-between gap-3 border-t border-line-hair pt-3">
                                <p class="flex items-baseline gap-1">
                                    <span class="font-mono text-base font-bold leading-6 text-brand">
                                        {{ $card['price'] }}</span>
                                    <span class="font-mono text-xs leading-4 text-[#5C6B57]">/ kg</span>
                                </p>
                                <a href="{{ route('public.catalog') }}"
                                    class="shrink-0 rounded-xl bg-accent-deep px-4 py-2 font-mono text-xs font-bold leading-4 text-brand shadow-sm">
                                    Pesan Komoditas</a>
                            </div>
                        </div>
                    </li>
                @endforeach

                <li
                    class="relative flex flex-col justify-between gap-4 overflow-hidden rounded-2xl border-2 border-accent-deep bg-brand p-6 shadow-md">
                    <div class="pointer-events-none absolute left-[272px] top-[240px] h-40 w-40 rounded-full bg-success/30 blur-md"
                        aria-hidden="true"></div>
                    <div class="relative flex flex-col gap-2">
                        <h3 class="pt-1 text-xl font-extrabold leading-[25px] text-white">
                            {!! nl2br(e($catalog['banner']['title'])) !!}
                        </h3>
                        <p class="text-xs leading-[19.5px] text-[#D9E4C0]">{!! nl2br(e($catalog['banner']['body'])) !!}</p>
                        <ul class="flex flex-col gap-2 pt-2">
                            @foreach ($catalog['banner']['points'] as $point)
                                <li class="flex items-center gap-2">
                                    <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-accent-deep" />
                                    <span class="font-mono text-xs leading-4 text-[#D9E4C0]">{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="relative border-t border-accent-deep/20 pt-5">
                        <a href="#kontak"
                            class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-accent-deep px-4 py-2.5 font-mono text-xs font-bold leading-4 text-brand">
                            <x-gpa.icon name="headset" class="h-3.5 w-3.5 shrink-0 text-brand" />
                            {{ $catalog['banner']['action'] }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </section>

    {{-- PROTOKOL AKUNTABILITAS B2B --}}
    <section class="py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-accent-deep bg-[#EAF2D7] px-4 py-1.5">
                    <x-gpa.icon :name="$governance['icon']" class="h-3.5 w-3.5 shrink-0 text-brand" />
                    <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">
                        {{ $governance['eyebrow'] }}</span>
                </span>
                <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">{{ $governance['headline'] }}</h2>
                <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">{{ $governance['lead'] }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($governance['cards'] as $card)
                    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-7 shadow-sm">
                        <div class="flex flex-col gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                                <x-gpa.icon :name="$card['icon']" class="h-5 w-5 text-brand" />
                            </span>
                            <h3 class="text-xl font-bold leading-7 text-brand">{{ $card['title'] }}</h3>
                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">{!! nl2br(e($card['body'])) !!}</p>
                            <ul class="flex flex-col gap-2">
                                @foreach ($card['bullets'] as $bullet)
                                    <li class="flex items-start gap-2.5">
                                        <x-gpa.icon name="check" class="mt-1 h-3 w-3 shrink-0 text-success" />
                                        <span class="font-mono text-xs leading-4 text-[#121A0F]">{{ $bullet }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="pt-4">
                            <div class="border-t border-line-hair pt-4">
                                <p class="font-mono text-xs font-bold leading-4 text-success">{{ $card['badge'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- PORTFOLIO KONTRAK AKTIF --}}
    <section id="mitra-kontrak" class="scroll-mt-36 border-t border-line-hair bg-[#F5F0E3] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col gap-6 border-b border-line-hair pb-6 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2">
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border border-accent-deep bg-[#EAF2D7] px-3 py-1">
                        <x-gpa.icon :name="$partners['icon']" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">
                            {{ $partners['eyebrow'] }}</span>
                    </span>
                    <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">{{ $partners['headline'] }}</h2>
                    <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">{{ $partners['lead'] }}</p>
                </div>
                <div class="shrink-0 rounded-xl border border-accent-deep/30 bg-brand p-3">
                    @foreach ($partners['sla'] as $line)
                        <p class="font-mono text-xs leading-4 text-white">
                            {{ $line['label'] }}
                            <span @class(['text-accent-deep' => $line['accent']])>{{ $line['value'] }}</span>
                        </p>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($partners['cards'] as $index => $card)
                    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2.5">
                                <span @class([
                                    'rounded px-2 py-0.5 font-mono text-[10px] font-bold leading-[15px]',
                                    'bg-[#EAF2D7] text-brand' => $card['contractTone'] === 'light',
                                    'bg-brand text-accent-deep' => $card['contractTone'] === 'ink',
                                ])>{{ $card['contract'] }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono text-xs font-bold',
                                    'bg-brand text-accent-deep' => $index % 2 === 0,
                                    'bg-success text-white' => $index % 2 === 1,
                                ])>{{ $card['initials'] }}</span>
                                <h3 class="text-base font-bold leading-6 text-brand">{{ $card['name'] }}</h3>
                            </div>
                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">{!! nl2br(e($card['body'])) !!}</p>
                            <div class="flex flex-col gap-1 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                                @foreach ($card['details'] as $detail)
                                    <div class="grid grid-cols-2 gap-2">
                                        <span class="font-mono text-xs leading-4 text-[#5C6B57]">{{ $detail['label'] }}</span>
                                        <span @class([
                                            'font-mono text-xs font-bold leading-4',
                                            'text-[#121A0F]' => $detail['tone'] === 'default',
                                            'text-success' => $detail['tone'] === 'success',
                                        ])>{{ $detail['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="pt-4">
                            <div class="flex items-center justify-between gap-2 border-t border-line-hair pt-2">
                                <p class="font-mono text-[11px] leading-[16.5px] text-[#5C6B57]">Kepatuhan SLA:
                                    <span class="text-brand">{{ $card['slaValue'] }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div
                    class="flex flex-col gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm sm:col-span-2 sm:flex-row sm:items-center sm:justify-between xl:col-span-3">
                    <div class="flex items-center gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand">
                            <x-gpa.icon name="file-text" class="h-4 w-4 text-accent-deep" />
                        </span>
                        <div class="flex flex-col gap-0.5">
                            <p class="text-sm font-bold leading-5 text-brand">{{ $partners['cta']['title'] }}</p>
                            <p class="text-xs leading-4 text-[#5C6B57]">{{ $partners['cta']['body'] }}</p>
                        </div>
                    </div>
                    <a href="#kontak"
                        class="shrink-0 rounded-xl bg-brand px-5 py-2.5 text-center font-mono text-xs font-bold leading-4 text-accent-deep shadow-sm">
                        {{ $partners['cta']['action'] }}</a>
                </div>
            </div>
        </div>
    </section>

    {{-- SPESIFIKASI SEKTOR INDUSTRI --}}
    <section class="py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6">
            <div class="flex flex-col items-center gap-3 text-center">
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-accent-deep bg-[#EAF2D7] px-4 py-1.5">
                    <x-gpa.icon :name="$sectors['icon']" class="h-3.5 w-3.5 shrink-0 text-brand" />
                    <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">
                        {{ $sectors['eyebrow'] }}</span>
                </span>
                <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">{{ $sectors['headline'] }}</h2>
                <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">{{ $sectors['lead'] }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($sectors['cards'] as $card)
                    <div class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4">
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#EAF2D7]">
                                <x-gpa.icon :name="$card['icon']" class="h-5 w-5 text-brand" />
                            </span>
                            <div class="flex flex-col gap-0.5 pt-[7px]">
                                <span class="font-mono text-[10px] font-bold uppercase leading-[15px] text-success">
                                    {{ $card['code'] }}</span>
                                <h3 class="text-lg font-bold leading-7 text-brand">{{ $card['title'] }}</h3>
                            </div>
                            <p class="text-xs leading-[19.5px] text-[#5C6B57]">{!! nl2br(e($card['body'])) !!}</p>
                            <ul class="flex flex-col gap-1.5 rounded-xl border border-line-hair bg-[#F8FAF3] p-3">
                                @foreach ($card['items'] as $item)
                                    <li class="flex items-center gap-2">
                                        <x-gpa.icon name="check" class="h-3 w-3 shrink-0 text-success" />
                                        <span class="font-mono text-xs leading-4 text-[#121A0F]">{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="pt-4">
                            <div class="border-t border-line-hair pt-3">
                                <p class="font-mono text-xs font-bold leading-4 text-brand">{{ $card['focus'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- GALERI & DOKUMENTASI OPERASIONAL --}}
    <section id="galeri" class="scroll-mt-36 border-t border-line-hair bg-[#EFF4DC] py-20">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:px-6" x-data="{ galleryTab: 'semua' }">
            <div class="flex flex-col gap-6 border-b border-line-hair pb-6 md:flex-row md:items-end md:justify-between">
                <div class="flex flex-col gap-2">
                    <span
                        class="inline-flex w-fit items-center gap-1.5 rounded-full border border-accent-deep bg-[#EAF2D7] px-3 py-1">
                        <x-gpa.icon :name="$gallery['icon']" class="h-3.5 w-3.5 shrink-0 text-brand" />
                        <span class="font-mono text-xs font-bold uppercase leading-4 tracking-[0.6px] text-brand">
                            {{ $gallery['eyebrow'] }}</span>
                    </span>
                    <h2 class="text-[36px] font-extrabold leading-10 text-[#121A0F]">{{ $gallery['headline'] }}</h2>
                    <p class="max-w-[672px] text-base leading-6 text-[#5C6B57]">{{ $gallery['lead'] }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    @foreach ($gallery['chips'] as $chip)
                        @if ($chip['tone'] === 'light')
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-success/30 bg-white px-3 py-1.5">
                                <span class="h-2 w-2 rounded-full bg-success" aria-hidden="true"></span>
                                <span class="font-mono text-xs font-semibold leading-4 text-brand">{{ $chip['label'] }}</span>
                            </span>
                        @else
                            <span class="rounded-lg bg-brand px-3 py-1.5 font-mono text-xs font-bold leading-4 text-accent-deep">
                                {{ $chip['label'] }}</span>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-b border-line-hair pb-3">
                @foreach ($gallery['tabs'] as $tab)
                    <button type="button" @click="galleryTab = '{{ $tab['key'] }}'"
                        class="rounded-lg px-3.5 py-1.5 font-mono text-xs leading-4 transition-colors" :class="galleryTab === '{{ $tab['key'] }}'
                                ? 'bg-brand font-bold text-accent-deep shadow-sm'
                                : 'border border-line-hair bg-white font-medium text-[#5C6B57]'">
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>

            <ul class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($gallery['cards'] as $card)
                    <li class="flex flex-col justify-between gap-4 rounded-2xl border border-line-hair bg-white p-5"
                        x-show="galleryTab === 'semua' || galleryTab === '{{ $card['category'] }}'" x-transition.opacity>
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2 border-b border-line-hair pb-2">
                                <span @class([
                                    'rounded px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase leading-[15px]',
                                    'bg-brand text-accent-deep' => $card['categoryTone'] === 'ink',
                                    'bg-success text-white' => $card['categoryTone'] === 'success',
                                ])>{{ $card['categoryLabel'] }}</span>
                                <span class="font-mono text-xs font-medium leading-4 text-success">
                                    {{ $card['location'] }}</span>
                            </div>

                            <div
                                class="flex h-36 flex-col items-center justify-center gap-1 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-3 text-center">
                                @php
                                    $galleryIcons = ['petani' => 'leaf', 'fasilitas' => 'building', 'mutu' => 'gauge', 'logistik' => 'truck'];
                                @endphp
                                <x-gpa.icon :name="$galleryIcons[$card['category']]" class="h-6 w-6 text-success" />
                                <span class="font-mono text-xs font-bold leading-4 text-brand">{{ $card['image'] }}</span>
                                <span class="font-mono text-[9px] leading-[13.5px] text-success">{{ $card['caption'] }}</span>
                            </div>

                            <div class="flex flex-col gap-1">
                                <h3 class="text-base font-bold leading-6 text-brand">{{ $card['title'] }}</h3>
                                <p class="text-xs leading-4 text-[#5C6B57]">{{ $card['body'] }}</p>
                            </div>
                        </div>

                        <div class="pt-4">
                            <div class="flex items-center justify-between gap-2 border-t border-line-hair pt-2">
                                <p class="font-mono text-[10px] leading-[15px]">
                                    <span class="text-[#5C6B57]">{{ $card['meta']['label'] }}</span>
                                    <span class="text-brand">{{ $card['meta']['value'] }}</span>
                                </p>
                                <p class="font-mono text-[10px] font-bold leading-[15px] text-success">
                                    {{ $card['footer'] }}
                                </p>
                            </div>
                        </div>
                    </li>
                @endforeach

                <li class="flex flex-col gap-4 rounded-2xl border border-line-hair bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-bold leading-6 text-brand">{{ $gallery['cta']['title'] }}</p>
                        <p class="text-xs leading-4 text-[#5C6B57]">{{ $gallery['cta']['body'] }}</p>
                    </div>
                    <a href="{{ url('/tentang-gpa') }}#legalitas"
                        class="mt-auto flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand px-5 py-2.5 font-mono text-xs font-bold leading-4 text-accent-deep shadow-sm">
                        {{ $gallery['cta']['action'] }}</a>
                </li>
            </ul>
        </div>
    </section>

@endsection
