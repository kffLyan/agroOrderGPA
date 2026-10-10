@extends('layouts.public')

@section('title', 'Galeri | AgroOrder GPA')

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-brand text-white">
        <div class="pointer-events-none absolute -left-32 -top-32 h-80 w-80 rounded-full bg-success/30 blur-3xl"
            aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 top-1/3 h-96 w-96 rounded-full bg-accent/15 blur-3xl"
            aria-hidden="true"></div>

        <div class="relative mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-16">
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_448px] lg:items-start">
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

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        @foreach ($hero['actions'] as $action)
                            <x-gpa.btn :href="$action['href']" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'"
                                size="lg">
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @endforeach
                    </div>
                </div>

                <aside aria-label="Live feed arsip"
                    class="rounded-2xl border border-white/15 bg-brand-deep/70 p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                        <p class="gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            {{ $hero['feed']['title'] }}
                        </p>
                    </div>

                    <dl class="mt-4 flex flex-col gap-2.5">
                        @foreach ($hero['feed']['rows'] as $row)
                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-brand/70 px-3.5 py-3">
                                <dt class="gpa-mono-xs uppercase tracking-wider text-white/50">{{ $row['label'] }}</dt>
                                <dd class="text-right">
                                    <span class="block text-sm font-bold leading-5 text-white">{{ $row['value'] }}</span>
                                    <span class="block gpa-mono-xs leading-4 text-white/60">{{ $row['note'] }}</span>
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-white/10 pt-4">
                        <span class="flex items-center gap-1.5 gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                            <span class="h-1.5 w-1.5 rounded-full bg-accent" aria-hidden="true"></span>
                            {{ $hero['feed']['status'] }}
                        </span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- RINGKASAN ARSIP --}}
    <section aria-label="Ringkasan arsip dokumentasi" class="border-b border-line-hair bg-canvas">
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

    @php
        $entriesJs = '['.implode(', ', array_map(fn (array $card) => sprintf(
            "{category: '%s', search: '%s'}",
            $card['category'],
            str_replace(['\\', "'"], ['\\\\', "\\'"], $card['search']),
        ), $entries)).']';

        $pageBase = 'inline-flex h-8 min-w-8 items-center justify-center gap-1.5 rounded border px-3 font-mono text-2xs font-bold uppercase tracking-wider transition-colors';
    @endphp

    {{-- ARSIP DOKUMENTASI --}}
    <section id="arsip" class="scroll-mt-36 bg-surface-muted" x-data="{
        archiveTab: 'semua',
        archiveQuery: '',
        archiveEntries: {!! $entriesJs !!},
        archiveMatches(category, search) {
            const query = this.archiveQuery.trim().toLowerCase();

            return (this.archiveTab === 'semua' || this.archiveTab === category)
                && (query === '' || search.toLowerCase().includes(query));
        },
        get archiveMatchCount() {
            return this.archiveEntries.filter((entry) => this.archiveMatches(entry.category, entry.search)).length;
        },
    }">
        <div class="border-b border-line-hair bg-surface">
            <div
                class="mx-auto flex w-full max-w-6xl flex-col gap-3 px-4 py-3.5 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 lg:pb-0">
                    @foreach ($filter['tabs'] as $tab)
                        <button type="button" @click="archiveTab = '{{ $tab['key'] }}'"
                            class="shrink-0 whitespace-nowrap rounded-lg px-3 py-1.5 font-mono text-xs leading-4 transition-colors"
                            :class="archiveTab === '{{ $tab['key'] }}'
                                ? 'bg-brand font-bold text-accent shadow-sm'
                                : 'border border-line-hair bg-white font-medium text-ink-body hover:border-line-board'">
                            {{ $tab['label'] }} ({{ $tab['count'] }})
                        </button>
                    @endforeach
                </div>

                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-72">
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-subtle" />
                        <input type="search" x-model="archiveQuery" name="archive-query"
                            aria-label="Cari arsip dokumentasi" placeholder="{{ $filter['searchPlaceholder'] }}"
                            class="h-9 w-full rounded-lg border border-line-hair bg-white pl-9 pr-3 font-mono text-xs text-ink placeholder:font-sans placeholder:text-ink-subtle focus:border-brand focus:outline-none" />
                    </div>

                    <select aria-label="Urutan arsip"
                        class="h-9 rounded-lg border border-line-hair bg-white px-3 font-mono text-xs text-ink-body focus:border-brand focus:outline-none">
                        <option>{{ $filter['sortLabel'] }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6 md:py-14">
            <div class="flex flex-col gap-2 border-b-2 border-brand-strong pb-3 sm:flex-row sm:items-end sm:justify-between">
                <p class="flex items-center gap-2 gpa-mono-xs font-bold uppercase tracking-wider text-brand-strong">
                    <span class="h-2 w-2 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
                    <span>MENAMPILKAN <span x-text="archiveMatchCount">{{ $summary['visible'] }}</span> DARI {{ $summary['total'] }} {{ $summary['unit'] }}</span>
                </p>
            </div>

            <ul class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @php
                    $entryIcons = ['kebun' => 'leaf', 'gudang' => 'building', 'mutu' => 'gauge', 'logistik' => 'truck'];
                @endphp

                @foreach ($entries as $card)
                    <li class="flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-5 shadow-sub"
                        x-show="archiveMatches('{{ $card['category'] }}', '{{ $card['search'] }}')" x-transition.opacity>
                        <div
                            class="flex h-44 flex-col items-center justify-center gap-2 rounded-xl border border-line-hair bg-[linear-gradient(171deg,#F6F8F2_0%,#EAF2D7_100%)] p-4 text-center">
                            <x-gpa.icon :name="$entryIcons[$card['category']]" class="h-6 w-6 text-success" />
                            <span
                                class="rounded-md border border-success/30 bg-white/80 px-2.5 py-1 font-mono text-xs font-bold leading-4 text-brand-strong">
                                {{ $card['image'] }}
                            </span>
                            <span class="font-mono text-[10px] leading-[15px] text-success">{{ $card['dim'] }}</span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span class="gpa-mono-xs text-ink-subtle">{{ $card['meta'] }}</span>
                        </div>

                        <h3 class="text-base font-bold leading-snug text-brand-strong">{{ $card['title'] }}</h3>

                        <p class="text-[13px] leading-relaxed text-ink-body">{{ $card['body'] }}</p>

                        <div
                            class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-line-hair pt-3">
                            <x-gpa.btn variant="secondary" size="sm"
                                @click="$dispatch('gpa:toast', { title: '{{ $card['action'] }}', message: '{{ $card['image'] }} - arsip penuh tersedia bagi mitra kontrak terverifikasi (data simulasi demo).', tone: 'info' })">
                                <x-slot:icon>
                                    <x-gpa.icon name="file-text" />
                                </x-slot:icon>
                                {{ $card['action'] }}
                            </x-gpa.btn>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div x-show="archiveMatchCount === 0" x-cloak x-transition.opacity
                class="mt-6 flex flex-col items-center gap-2 rounded-xl border border-dashed border-line-board bg-white px-4 py-10 text-center">
                <x-gpa.icon name="search" class="h-6 w-6 text-ink-subtle" />
                <p class="gpa-mono-xs font-bold uppercase tracking-wider text-ink-body">Tidak ada entri arsip yang cocok
                </p>
                <p class="text-xs text-ink-subtle">Ubah kata kunci pencarian atau pilih tab dokumentasi lainnya.</p>
            </div>

            <nav aria-label="Navigasi halaman arsip"
                class="mt-6 flex flex-col gap-4 rounded-xl border border-line-hair bg-surface p-4 shadow-sub sm:flex-row sm:items-center sm:justify-between">
                <p class="gpa-mono-xs font-semibold uppercase tracking-wider text-ink-body">
                    {{ $pagination['summary'] }}
                </p>

                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="{{ $pageBase }} cursor-not-allowed border-line-faint bg-surface-muted text-ink-subtle">
                        <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                        {{ $pagination['prev'] }}
                    </span>

                    @foreach ($pagination['pages'] as $page)
                        @if ($page === 'gap')
                            <span class="px-1 gpa-mono-xs text-ink-subtle" aria-hidden="true">…</span>
                        @elseif ($page === $pagination['current'])
                            <span aria-current="page"
                                class="{{ $pageBase }} border-brand bg-brand text-accent">{{ $page }}</span>
                        @else
                            <span class="{{ $pageBase }} border-line-soft bg-surface text-ink-body">{{ $page }}</span>
                        @endif
                    @endforeach

                    <span class="{{ $pageBase }} cursor-not-allowed border-line-faint bg-surface-muted text-ink-subtle">
                        {{ $pagination['next'] }}
                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5" />
                    </span>
                </div>
            </nav>
        </div>
    </section>

    {{-- PROTOKOL VERIFIKASI --}}
    <section id="protokol" class="scroll-mt-36 bg-canvas">
        <div class="mx-auto w-full max-w-6xl px-4 pb-4 sm:px-6">
            <div
                class="relative overflow-hidden rounded-3xl border-2 border-brand-strong bg-brand p-8 text-white shadow-pop md:p-12">
                <div class="pointer-events-none absolute -right-24 top-1/4 h-72 w-72 rounded-full bg-accent/15 blur-3xl"
                    aria-hidden="true"></div>

                <div class="relative max-w-3xl space-y-4">
                    <h2 class="text-2xl font-extrabold leading-tight md:text-3xl">{{ $callToAction['title'] }}</h2>
                    <p class="text-sm leading-relaxed text-white/90 md:text-base">{{ $callToAction['lead'] }}</p>

                    <ul class="flex flex-wrap gap-x-6 gap-y-2 pt-1">
                        @foreach ($callToAction['checks'] as $check)
                            <li class="flex items-center gap-2 gpa-mono-xs font-bold uppercase tracking-wider text-accent">
                                <x-gpa.icon name="check" class="h-4 w-4 shrink-0" />
                                {{ $check }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative mt-8 flex flex-wrap items-center gap-3 border-t border-white/10 pt-6">
                    @foreach ($callToAction['actions'] as $action)
                        @if (isset($action['modal']))
                            <x-gpa.btn type="button" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'"
                                size="lg" x-on:click="$dispatch('gpa-modal-open', '{{ $action['modal'] }}')">
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @else
                            <x-gpa.btn :href="route($action['href'])" :variant="$action['variant'] === 'accent' ? 'accent' : 'inverse'"
                                size="lg">
                                {{ $action['label'] }}
                            </x-gpa.btn>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-gpa.modal name="profil-arsip" :title="$archiveProfile['title']" :description="$archiveProfile['description']">
        <ul class="divide-y divide-line-faint overflow-hidden rounded-lg border border-line">
            @foreach ($archiveProfile['items'] as $item)
                <li class="flex items-start gap-2.5 bg-surface px-3 py-2.5">
                    <x-gpa.icon name="download" class="mt-px h-4 w-4 shrink-0 text-success" />
                    <span class="gpa-mono-xs leading-relaxed text-ink-body">{{ $item }}</span>
                </li>
            @endforeach
        </ul>

        <x-gpa.notice tone="neutral" title="Catatan Arsip">
            {{ $archiveProfile['footnote'] }}
        </x-gpa.notice>

        <x-slot:footer>
            <x-gpa.btn variant="ghost" size="md" x-on:click="$dispatch('gpa-modal-close', 'profil-arsip')">Tutup
            </x-gpa.btn>
            <x-gpa.btn :href="route('register')" variant="primary" size="md">
                <x-slot:icon>
                    <x-gpa.icon name="send" />
                </x-slot:icon>
                Ajukan Kunjungan
            </x-gpa.btn>
        </x-slot:footer>
    </x-gpa.modal>

@endsection
