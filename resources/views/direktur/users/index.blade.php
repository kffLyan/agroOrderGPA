@extends('layouts.direktur')

@section('title', 'Kelola Akun Pengguna & RBAC // Matriks Otorisasi')

@section('content')
    @php
        $chipTone = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'neutral' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'accent' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'ink' => 'bg-ink text-accent',
        ];

        $statusTone = [
            'success' => 'text-success-deep',
            'ink' => 'text-ink',
        ];

        $policyChip = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'ink' => 'bg-ink text-accent',
        ];

        $cellTone = [
            'grant' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'plain' => 'bg-surface-track text-ink',
            'muted' => 'text-ink-body',
            'none' => 'text-ink',
            'dark' => 'bg-ink text-accent',
            'solo' => 'bg-ink text-accent outline outline-1 -outline-offset-1 outline-accent',
        ];

        $badgeTone = [
            'ink' => 'bg-ink text-accent',
            'track' => 'bg-surface-track text-ink-body',
        ];

        // Argumen Alpine disiapkan sebagai literal JS agar loop tabel tetap
        // bersih dan tidak membutuhkan directive `@php` tambahan per baris.
        $directoryArguments = array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $directory['rows'],
        );
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorAccess(@js($directory['rows']))">
        @if (session('success'))
            <div class="rounded-xl border border-accent bg-accent/20 px-4 py-3 text-xs font-semibold text-success-deep shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="check-circle" class="h-4 w-4 text-success-deep shrink-0" />
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs font-semibold text-red-800 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <x-gpa.icon name="alert-circle" class="h-4 w-4 text-red-600 shrink-0" />
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">{{ $header['eyebrow'] }}</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        {{ $header['title'] }}
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">{{ $header['subtitle'] }}</p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('direktur.users.rbac.export') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0" />
                        Unduh Matriks Akses (.CSV)
                    </a>
                    <a href="{{ route('direktur.users.create') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        + Tambah Pengguna Baru
                    </a>
                </div>
            </header>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($metrics as $metric)
                    <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="gpa-micro-bold text-ink-body">{{ $metric['eyebrow'] }}</h3>
                                <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $chipTone[$metric['chip_tone']] }}">
                                    {{ $metric['chip'] }}
                                </span>
                            </div>

                            <p class="gpa-figure text-[22px] leading-7 text-ink">{{ $metric['value'] }}</p>
                            <p class="text-[11px] leading-4 text-ink-body">{{ $metric['note'] }}</p>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                            @foreach ($metric['facts'] as $fact)
                                <span @class([
                                    'gpa-note',
                                    'font-bold text-success-deep' => $fact['tone'] === 'accent',
                                    'text-ink-body' => $fact['tone'] !== 'accent',
                                ])>{{ $fact['text'] }}</span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- RBAC matrix --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">{{ $matrix['title'] }}</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="syncRbacPolicy()"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded bg-surface-shell px-3 py-2 gpa-meta font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="refresh" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        {{ $matrix['action'] }}
                    </button>
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[68rem] border-collapse">
                    <caption class="sr-only">{{ $matrix['title'] }}</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            @foreach ($matrix['columns'] as $index => $column)
                                <th scope="col"
                                    @class([
                                        'px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em]',
                                        'text-left text-ink-body' => $index === 0,
                                        'text-center text-ink-body' => $index !== 0,
                                    ])>
                                    {{ $column['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @foreach ($matrix['rows'] as $row)
                            <tr @class([
                                'transition-colors hover:bg-surface-shell/50',
                                'bg-accent/25' => $row['highlight'],
                            ])>
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <p @class([
                                        'text-[12px] leading-5 text-ink',
                                        'font-bold' => $row['highlight'],
                                        'font-semibold' => ! $row['highlight'],
                                    ])>{{ $row['module'] }}</p>
                                    <p class="mt-0.5 text-[10px] leading-4 text-ink-body">{{ $row['detail'] }}</p>
                                </th>
                                @foreach ($row['cells'] as $cell)
                                    <td class="px-3 py-4 text-center align-top">
                                        <span @class([
                                            'inline-block rounded px-2 py-1 text-[11px] leading-4',
                                            'font-bold' => in_array($cell['kind'], ['none', 'dark', 'solo'], true),
                                            'font-semibold' => ! in_array($cell['kind'], ['none', 'dark', 'solo'], true),
                                            $cellTone[$cell['kind']],
                                        ])>{{ $cell['label'] }}</span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>


        {{-- User directory --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">{{ $directory['title'] }}</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex items-center" for="directory-search">
                        <span class="sr-only">{{ $directory['search_placeholder'] }}</span>
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input id="directory-search" type="search" x-model="userSearch"
                            placeholder="{{ $directory['search_placeholder'] }}"
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    @foreach ($directory['roles'] as $role)
                        <button type="button" @click="setRole({{ \Illuminate\Support\Js::from($role['key']) }})"
                            :class="roleClass({{ \Illuminate\Support\Js::from($role['key']) }})"
                            class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                            {{ $role['label'] }}
                        </button>
                    @endforeach
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">{{ $directory['title'] }}</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            @foreach ($directory['columns'] as $index => $column)
                                <th scope="col"
                                    @class([
                                        'px-3 py-3 text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body',
                                        'text-right' => $index === count($directory['columns']) - 1,
                                        'text-left' => $index !== count($directory['columns']) - 1,
                                    ])>
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @foreach ($directory['rows'] as $index => $row)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50"
                                x-show="matchesUser({{ $directoryArguments[$index] }})">
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <div class="flex items-start gap-2.5">
                                        <span @class([
                                            'mt-0.5 shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-micro',
                                            $badgeTone[$row['badge_tone']],
                                        ])>{{ $row['badge'] }}</span>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-bold leading-5 text-ink">{{ $row['name'] }}</p>
                                            <p class="mt-0.5 gpa-note font-semibold text-ink-body">{{ $row['code'] }}</p>
                                        </div>
                                    </div>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $row['role'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['division'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta font-semibold text-ink">{{ $row['nik'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta text-ink">{{ $row['hub'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <span @class([
                                        'inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta',
                                        $row['mfa_tone'] === 'accent' ? $chipTone['accent'] : 'bg-surface-track text-ink-body',
                                    ])>{{ $row['mfa'] }}</span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p @class([
                                        'gpa-meta font-bold',
                                        'text-success-deep' => $row['online'],
                                        'text-ink' => ! $row['online'],
                                    ])>{{ $row['login'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['login_meta'] }}</p>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <div class="flex flex-wrap items-center justify-end gap-1.5">
                                        <a href="{{ $row['show_url'] }}"
                                            title="Lihat Detail Akun"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="eye" class="h-3 w-3 shrink-0" />
                                            Detail
                                        </a>
                                        <a href="{{ $row['edit_url'] }}"
                                            title="Ubah Data & Hak Akses"
                                            class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                            <x-gpa.icon name="settings" class="h-3 w-3 shrink-0" />
                                            Edit
                                        </a>
                                        <form action="{{ $row['reset_pwd_url'] }}" method="POST" class="inline"
                                            onsubmit="return confirm('Reset kata sandi pengguna ini?')">
                                            @csrf
                                            <button type="submit"
                                                title="Reset Sandi ke password123"
                                                class="inline-flex items-center gap-1 rounded bg-surface-shell px-2 py-1 gpa-note font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                                                <x-gpa.icon name="key" class="h-3 w-3 shrink-0" />
                                                Reset
                                            </button>
                                        </form>
                                        @if ($row['id'] !== auth()->id())
                                            <form action="{{ $row['delete_url'] }}" method="POST" class="inline"
                                                onsubmit="return confirm('Hapus akun pengguna {{ addslashes($row['name']) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    title="Hapus Pengguna"
                                                    class="inline-flex items-center gap-1 rounded bg-red-50 text-red-700 px-2 py-1 gpa-note font-semibold outline outline-1 -outline-offset-1 outline-red-200 transition-colors hover:bg-red-100">
                                                    <x-gpa.icon name="trash" class="h-3 w-3 shrink-0 text-red-600" />
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="gpa-note font-semibold text-danger" x-show="visibleUsers().length === 0" x-cloak>
                Tidak ada pengguna yang cocok dengan pencarian atau filter role.
            </p>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
                <p class="flex items-start gap-2">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="gpa-note font-bold text-success-deep">{{ $directory['ledger_note'] }}</span>
                </p>
                <div class="flex items-center gap-2">
                    <span class="gpa-note text-ink-body">{{ $directory['shown_label'] }}</span>
                    <span class="gpa-micro-bold text-ink">{{ $directory['page_label'] }}</span>
                    <button type="button" @click="pageDirectory(-1)" disabled
                        class="inline-flex items-center gap-1 rounded bg-surface-track px-2 py-1 gpa-note font-semibold text-ink-body outline outline-1 -outline-offset-1 outline-line-board disabled:cursor-not-allowed disabled:opacity-45">
                        <x-gpa.icon name="arrow-left" class="h-3 w-3 shrink-0" />
                        {{ $directory['prev_label'] }}
                    </button>
                    <button type="button" @click="pageDirectory(1)"
                        class="inline-flex items-center gap-1 rounded bg-ink px-2 py-1 gpa-note font-semibold text-accent transition-opacity hover:opacity-90">
                        {{ $directory['next_label'] }}
                        <x-gpa.icon name="arrow-right" class="h-3 w-3 shrink-0" />
                    </button>
                </div>
            </footer>
        </section>

    </div>
@endsection
