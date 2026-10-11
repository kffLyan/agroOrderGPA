@extends('layouts.staff')

@section('title', 'Surat Jalan')

@section('content')
    <div class="space-y-6" x-data="secretaryDispatch(@js($queue['rows']))">
        <section class="flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="flex items-center gap-1 gpa-note text-success-deep">
                </p>

                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-brand">
                    {{ $heading['title_before'] }}<br />
                    {{ $heading['title_after'] }}
                </h1>

            </div>

            <span
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-accent/50 py-1.5 pl-4 pr-4 outline outline-1 outline-success-deep">
                <span class="h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                <span class="gpa-micro-bold uppercase tracking-[0.45px] text-ink">{{ $heading['badge'] }}</span>
            </span>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="flex min-h-[16.4rem] flex-col justify-between gap-10 rounded-2xl bg-surface p-4 shadow-card">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="gpa-meta-lg font-semibold uppercase tracking-[0.88px] text-ink-body">
                            {{ $metric['label'] }}
                        </h2>
                        <span @class(['shrink-0 rounded-lg p-1.5', $metric['icon_tile']])>
                            <x-gpa.icon :name="$metric['icon']" class="h-[15px] w-[15px] shrink-0 {{ $metric['icon_class'] }}" />
                        </span>
                    </div>

                    <div class="py-3">
                        <p class="font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                            {{ $metric['value'] }}
                            @if (! empty($metric['value_after']))
                                <br />{{ $metric['value_after'] }}
                            @endif
                        </p>
                        <p class="mt-1 text-xs leading-4 {{ $metric['key'] === 'pod' ? 'font-inter' : '' }} text-ink-body">
                            {{ $metric['note'] }}
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                        <p @class(['gpa-micro-bold tracking-[1.08px]', $metric['foot_class']])>
                            {{ $metric['foot_label'] }}
                        </p>
                        <span @class(['h-2 w-2 shrink-0 rounded-full', $metric['foot_dot']]) aria-hidden="true"></span>
                    </div>
                </article>
            @endforeach
        </div>

        <section class="gpa-panel overflow-hidden rounded-2xl">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft bg-surface-shell/40 p-6">
                <div class="min-w-0">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold leading-6 text-ink">
                        {{ $queue['title'] }}
                    </h2>
                </div>

                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach ($queue['warehouses'] as $warehouse)
                        <button type="button" @click="warehouse = @js($warehouse); handleWarehouse()" :class="
                            warehouse === @js($warehouse)
                                ? 'bg-accent text-success-ink outline outline-1 outline-success-deep'
                                : 'bg-surface-pill text-ink outline outline-1 outline-line-board hover:bg-surface-disabled'
                        "
                            class="rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                            {{ $warehouse }}
                        </button>
                    @endforeach

                    <span class="rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-ink">
                        <span x-text="readyCount() + ' Siap Rilis'"></span>
                    </span>
                    <span class="rounded bg-surface-disabled px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body">
                        <span x-text="pendingCount() + ' Tertunda'"></span>
                    </span>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[1080px] border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-track">
                        <tr>
                            @foreach ($queue['columns'] as $key => $column)
                                <th scope="col" @class([
                                    'px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body',
                                    'text-right' => $key === 'action',
                                ])>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft">
                        @foreach ($queue['rows'] as $row)
                            <tr x-show="matchesFilter(@js($row))" @click="select(@js($row['id']))"
                                :class="rowClass(@js($row))" class="cursor-pointer transition-colors">
                                <td class="px-4 py-4">
                                    <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $row['id'] }}</p>
                                    <p class="mt-0.5 pt-0.5 font-sans text-sm font-semibold leading-5 text-ink">
                                        {{ $row['client'] }}
                                    </p>
                                    <p class="mt-1 text-xs leading-4 text-ink-body">{{ $row['dock'] }}</p>
                                    <p x-cloak x-show="isReleased(@js($row['id']))"
                                        class="mt-1 inline-flex rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">
                                        Sudah Terbit
                                    </p>
                                </td>

                                <td class="px-4 py-4 align-middle">
                                    <span class="gpa-note text-ink-body">{{ $row['estimate'] }}</span>
                                </td>

                                <td class="px-4 py-4">
                                    @if (! empty($row['netto']))
                                        <p class="gpa-meta-lg font-bold tracking-[0.88px] text-ink">{{ $row['netto'] }}</p>
                                        <p class="gpa-micro text-danger">{{ $row['deviation'] }}</p>
                                        <p class="mt-1 text-2xs leading-4 text-ink-quiet">{{ $row['revision'] }}</p>
                                    @else
                                        <span @class([
                                            'inline-flex rounded px-2 py-0.5 gpa-micro-bold uppercase tracking-[1px]',
                                            $row['netto_chip']['class'],
                                        ])>{{ $row['netto_chip']['label'] }}</span>
                                        <p class="mt-1 text-2xs font-medium leading-4 text-warning">{{ $row['netto_note'] }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex items-center gap-1 rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                        $row['status_chip']['class'],
                                    ])>
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-current opacity-70" aria-hidden="true"></span>
                                        {{ $row['status_chip']['label'] }}
                                    </span>
                                    <p class="mt-1 font-mono text-xs leading-4 text-ink-body">{{ $row['tera'] }}</p>
                                </td>

                                <td class="px-4 py-4">
                                    @if (! empty($row['armada']))
                                        <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">{{ $row['armada'] }}</p>
                                        <p class="gpa-micro-bold tracking-[1.08px] text-success-deep">Plat: {{ $row['plate'] }}</p>
                                        <p class="mt-1 text-xs leading-4 text-ink-body">Supir: {{ $row['driver'] }}</p>
                                    @else
                                        <p class="text-xs italic leading-4 text-ink-quiet">{{ $row['fleet_note'] }}</p>
                                        <p class="mt-1 text-2xs leading-4 text-ink-quiet">{{ $row['fleet_sub'] }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2">
                                        @if (! empty($row['armada']))
                                            <button type="button" @click.stop="issue(@js($row))"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-ink shadow-sub outline outline-1 outline-success-deep transition-colors hover:bg-accent-deep gpa-meta-lg font-bold tracking-[0.88px]">
                                                <x-gpa.icon name="file-text" class="h-3 w-3 shrink-0" />
                                                Terbitkan Surat Jalan
                                            </button>
                                        @else
                                            <span
                                                class="inline-flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-surface-pill px-4 py-2 text-ink-quiet opacity-80 outline outline-1 outline-line-board/60 gpa-meta-lg font-bold tracking-[0.88px]">
                                                <x-gpa.icon name="lock" class="h-3 w-3 shrink-0" />
                                                Kunci Estimasi Ditolak
                                            </span>
                                        @endif

                                        <a href="{{ route('prints.surat-jalan') }}" @click.stop
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-surface-pill px-4 py-2 text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-disabled">
                                            <x-gpa.icon name="printer" class="h-3 w-3 shrink-0" />
                                            <span class="gpa-meta-lg font-bold tracking-[0.88px]">Halaman Cetak A4</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft bg-surface-shell px-4 py-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + visibleCount() + ' dari ' + {{ count($queue['rows']) }} + ' dokumen dalam antrean dispatch'">
                </p>
                <p class="gpa-note text-ink-body">
                    Total Terbit Sesi Ini:
                    <span class="font-bold text-success-deep" x-text="issuedCount() + ' Dokumen'"></span>
                </p>
            </div>
        </section>

        <section
            class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-ink p-4 outline outline-2 outline-accent/40">
            <div class="flex items-center gap-4">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent">
                    <x-gpa.icon name="lock" class="h-5 w-5 shrink-0 text-ink" />
                </span>

                <div>
                    <h2 class="font-sans text-lg font-bold leading-[1.4rem] text-accent">{{ $release['title'] }}</h2>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button type="button" @click="saveDraft()"
                    class="rounded-lg bg-white/10 px-5 py-2.5 text-center font-mono text-xs font-semibold tracking-[0.88px] text-canvas outline outline-1 outline-line-board/40 transition-colors hover:bg-white/15">
                    {{ $release['draft_label'] }}
                </button>

                <button type="button" @click="confirmRelease()" :disabled="! selected || selected.state !== 'ready'"
                    :class="
                        ! selected || selected.state !== 'ready'
                            ? 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'
                            : 'bg-accent text-ink hover:bg-accent-deep'
                    "
                    class="inline-flex items-center gap-2 rounded-lg px-6 py-2.5 font-mono text-xs font-bold tracking-[0.88px] shadow-sub transition-colors">
                    <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                    <span>{{ $release['issue_label'] }}</span>
                </button>
            </div>
        </section>
    </div>
@endsection
