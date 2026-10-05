@extends('layouts.director')

@section('title', 'Pengaturan Tata Kelola // Kebijakan & Audit Trail')

@section('content')
    @php
        $metricChip = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'neutral' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'ink' => 'bg-ink text-accent outline outline-1 -outline-offset-1 outline-ink',
        ];

        $statusTone = [
            'success' => 'text-success-deep',
            'ink' => 'text-ink',
            'warning' => 'text-warning-caution',
            'danger' => 'text-danger',
        ];

        $factTone = [
            'ink' => 'text-ink',
            'success' => 'text-success-deep',
            'warning' => 'text-warning-caution',
            'danger' => 'text-danger',
        ];

        $actionTone = [
            'success' => 'bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep',
            'neutral' => 'bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board',
            'dark' => 'bg-ink text-accent outline outline-1 -outline-offset-1 outline-ink',
            'danger' => 'bg-danger-soft/40 text-danger outline outline-1 -outline-offset-1 outline-danger',
        ];

        $actionArguments = array_map(
            static fn (array $row): string => (string) \Illuminate\Support\Js::from($row),
            $audit['rows'],
        );

        $auditActions = array_values(array_unique(array_column($audit['rows'], 'action')));
    @endphp

    <div class="mx-auto flex max-w-[1280px] flex-col gap-6"
        x-data="directorGovernance(@js($audit['rows']), @js($audit))">

        {{-- Page header --}}
        <section class="flex flex-wrap items-start justify-between gap-4 border-b border-line-board/80 pb-4">
            <div class="min-w-0 space-y-1">
                <p class="gpa-eyebrow flex items-center gap-2">
                    <x-gpa.icon name="settings" class="h-3.5 w-3.5 text-success-deep" />
                    {{ $header['eyebrow'] }}
                </p>
                <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                    {{ $header['title'] }}
                </h1>
                <p class="max-w-3xl text-sm leading-5 text-ink-body">{{ $header['subtitle'] }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" @click="exportAudit()"
                    class="inline-flex items-center gap-1.5 rounded bg-surface-shell px-3 py-2 gpa-meta font-semibold text-ink outline outline-1 -outline-offset-1 outline-line-board transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                    {{ $header['export_label'] }}
                </button>
                <button type="button" @click="verifyIntegrity()"
                    class="inline-flex items-center gap-1.5 rounded bg-accent px-3 py-2 gpa-meta font-bold text-ink shadow-sub outline outline-1 -outline-offset-1 outline-success-deep transition-opacity hover:opacity-90">
                    <x-gpa.icon name="shield" class="h-3.5 w-3.5 shrink-0" />
                    {{ $header['integrity_label'] }}
                </button>
                <button type="button" @click="lockPeriod()"
                    class="inline-flex items-center gap-1.5 rounded bg-ink px-3 py-2 gpa-meta font-bold text-accent shadow-sub transition-opacity hover:opacity-90">
                    <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0" />
                    {{ $header['lock_label'] }}
                </button>
            </div>
        </section>

        {{-- Metric row --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="gpa-micro-bold text-ink-body">{{ $metric['eyebrow'] }}</h2>
                            <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $metricChip[$metric['chip_tone']] }}">
                                {{ $metric['chip'] }}
                            </span>
                        </div>

                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ $metric['value'] }}</p>
                        <p class="text-[11px] leading-4 text-ink-body">{{ $metric['note'] }}</p>

                        @if (isset($metric['bar_width']))
                            <div class="flex flex-col gap-1">
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-surface-track" role="presentation">
                                    <div class="h-full rounded-full bg-success-deep" style="width: {{ $metric['bar_width'] }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-surface-track pt-3">
                        <span class="gpa-note text-ink-body">{{ $metric['foot_label'] }}</span>
                        <span class="gpa-note font-bold {{ $statusTone[$metric['foot_tone']] }}">
                            {{ $metric['foot_value'] }}
                        </span>
                    </div>

                    @if (! empty($metric['foot_note']))
                        <p class="gpa-note font-semibold text-success-deep">{{ $metric['foot_note'] }}</p>
                    @endif
                </article>
            @endforeach
        </section>

        {{-- Rule engine parameters --}}
        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">{{ $parameters['title'] }}</h2>
                    <p class="mt-1 gpa-meta font-medium text-ink-body">{{ $parameters['subtitle'] }}</p>
                </div>

                <button type="button" @click="editParameters()"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded bg-ink px-3.5 py-2 gpa-meta font-bold text-accent transition-opacity hover:opacity-90">
                    <x-gpa.icon name="lock" class="h-3.5 w-3.5 shrink-0" />
                    {{ $parameters['action'] }}
                </button>
            </header>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($parameters['cards'] as $parameter)
                    <article class="flex h-full flex-col gap-3 rounded-xl bg-surface-shell/60 p-4 outline outline-1 -outline-offset-1 outline-line-board">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded bg-surface-track px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta text-ink">
                                {{ $parameter['rule'] }}
                            </span>
                            <span class="rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $actionTone[$parameter['status_tone']] }}">
                                {{ $parameter['status'] }}
                            </span>
                        </div>

                        <h3 class="text-[13px] font-bold leading-5 text-ink">{{ $parameter['title'] }}</h3>
                        <p class="text-[11px] leading-4 text-ink-body">{{ $parameter['description'] }}</p>

                        <dl class="flex flex-col gap-1.5 border-t border-line-hair pt-3">
                            @foreach ($parameter['facts'] as $fact)
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="gpa-note text-ink-body">{{ $fact['label'] }}</dt>
                                    <dd class="gpa-meta text-right font-bold {{ $factTone[$fact['tone'] ?? 'ink'] }}">
                                        {{ $fact['value'] }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>

                        <footer class="mt-auto flex items-center justify-between gap-2 border-t border-line-hair pt-3">
                            <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                                <x-gpa.icon name="bolt" class="h-3 w-3 shrink-0 text-success-deep" />
                                {{ $parameter['foot_note'] }}
                            </span>
                            <span class="gpa-micro-bold text-ink-body">{{ $parameter['config_id'] }}</span>
                        </footer>
                    </article>
                @endforeach
            </div>

            <p class="flex items-start gap-2 border-t border-line-board/60 pt-3">
                <x-gpa.icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-ink-body" />
                <span class="gpa-note text-ink-body">{{ $parameters['action_note'] }}</span>
            </p>
        </section>

        {{-- Audit trail --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 pb-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">{{ $audit['title'] }}</h2>
                    <p class="mt-1 gpa-meta font-medium text-ink-body">{{ $audit['subtitle'] }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <label class="relative flex items-center" for="audit-search">
                        <span class="sr-only">{{ $audit['search_placeholder'] }}</span>
                        <x-gpa.icon name="search"
                            class="pointer-events-none absolute left-2.5 h-3.5 w-3.5 text-ink-body" />
                        <input id="audit-search" type="search" x-model="search"
                            placeholder="{{ $audit['search_placeholder'] }}"
                            class="w-56 rounded bg-surface-shell py-1.5 pl-8 pr-2.5 text-[11px] leading-4 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </label>

                    <button type="button" @click="setAction('all')"
                        :class="filterClass('all')"
                        class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                        {{ $audit['filter_label'] }}
                    </button>
                    @foreach ($auditActions as $auditAction)
                        <button type="button" @click="setAction({{ \Illuminate\Support\Js::from($auditAction) }})"
                            :class="filterClass({{ \Illuminate\Support\Js::from($auditAction) }})"
                            class="rounded px-2.5 py-1 text-center outline outline-1 -outline-offset-1 gpa-note transition-colors">
                            {{ $auditAction }}
                        </button>
                    @endforeach
                </div>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full min-w-[72rem] border-collapse">
                    <caption class="sr-only">{{ $audit['title'] }}</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col"
                                class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                {{ $audit['columns'][0] }}
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                {{ $audit['columns'][1] }}
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                {{ $audit['columns'][2] }}
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                {{ $audit['columns'][3] }}
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                {{ $audit['columns'][4] }}
                            </th>
                            <th scope="col"
                                class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                                {{ $audit['columns'][5] }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @foreach ($audit['rows'] as $index => $row)
                            <tr @class([
                                'align-top transition-colors hover:bg-surface-shell/50',
                                'bg-danger-soft/25' => $row['blocked'],
                            ]) x-show="matches({{ $actionArguments[$index] }})">
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta font-bold text-ink">{{ $row['date'] }}</p>
                                    <p class="mt-0.5 gpa-note text-ink-body">{{ $row['time'] }}</p>
                                </td>
                                <th scope="row" class="px-3 py-4 text-left align-top font-normal">
                                    <p class="text-[13px] font-semibold leading-5 text-ink">{{ $row['actor'] }}</p>
                                    <p class="mt-0.5 text-[11px] leading-4 text-ink-body">{{ $row['actor_meta'] }}</p>
                                </th>
                                <td class="px-3 py-4 align-top">
                                    <span class="inline-block rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta {{ $actionTone[$row['action_tone']] }}">
                                        {{ $row['action'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="gpa-meta font-semibold text-ink">{{ $row['document'] }}</p>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <p class="max-w-md text-[12px] leading-5 text-ink-body">{{ $row['detail'] }}</p>
                                </td>
                                <td class="px-3 py-4 text-right align-top">
                                    <p class="gpa-meta font-semibold text-success-deep">{{ $row['hash'] }}</p>
                                    <p class="mt-0.5 gpa-note font-bold {{ $row['blocked'] ? 'text-danger' : 'text-ink-body' }}">
                                        {{ $row['block'] }}
                                    </p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="gpa-note font-semibold text-danger" x-show="visibleCount() === 0" x-cloak>
                Tidak ada entri audit yang cocok dengan pencarian atau filter kategori.
            </p>

            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-line-board/60 pt-3">
                <p class="flex items-start gap-2">
                    <span class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                    <span class="gpa-note font-bold text-success-deep">{{ $audit['ledger_status'] }}</span>
                </p>
                <div class="flex flex-col items-end gap-1">
                    <p class="gpa-note text-ink-body">{{ $audit['page_label'] }}</p>
                    <p class="gpa-note font-bold text-ink">{{ $audit['shown_label'] }}</p>
                </div>
            </footer>
        </section>

        {{-- Critical controls --}}
        <section class="grid gap-4 lg:grid-cols-2">
            @foreach ($controls as $control)
                <article @class([
                    'flex h-full flex-col gap-3 rounded-2xl bg-surface p-6 shadow-card',
                    'outline outline-2 -outline-offset-2 outline-danger' => $control['protocol_tone'] === 'danger',
                    'outline outline-2 -outline-offset-2 outline-success-deep' => $control['protocol_tone'] === 'success',
                ])>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="gpa-micro-bold {{ $statusTone[$control['protocol_tone']] }}">
                            {{ $control['protocol'] }}
                        </h2>
                        <span class="gpa-note font-bold {{ $statusTone[$control['status_tone']] }}">
                            {{ $control['status'] }}
                        </span>
                    </div>

                    <h3 class="gpa-section-title text-ink">{{ $control['title'] }}</h3>
                    <p class="text-[12px] leading-5 text-ink-body">{{ $control['description'] }}</p>

                    <dl class="flex flex-col gap-2 border-t border-line-hair pt-3">
                        @foreach ($control['facts'] as $fact)
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <dt class="gpa-note text-ink-body">{{ $fact['label'] }}</dt>
                                <dd class="gpa-meta text-right font-bold {{ $factTone[$fact['tone'] ?? 'ink'] }}">
                                    {{ $fact['value'] }}
                                </dd>
                            </div>
                        @endforeach
                    </dl>

                    <footer class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-line-hair pt-4">
                        <span class="inline-flex items-center gap-1 gpa-note font-semibold text-ink-body">
                            <x-gpa.icon name="key" class="h-3 w-3 shrink-0" />
                            {{ $control['authority'] }}
                        </span>
                        <button type="button"
                            @click="{{ $control['key'] === 'freeze' ? 'toggleFreeze()' : 'configureSuccession()' }}"
                            @class([
                                'inline-flex items-center gap-1.5 rounded px-3.5 py-2 gpa-meta-lg font-bold shadow-sub transition-opacity hover:opacity-90',
                                'bg-danger text-white' => $control['key'] === 'freeze',
                                'bg-ink text-accent' => $control['key'] !== 'freeze',
                            ])>
                            <x-gpa.icon :name="$control['key'] === 'freeze' ? 'lock' : 'users'"
                                class="h-3.5 w-3.5 shrink-0" />
                            {{ $control['action'] }}
                        </button>
                    </footer>
                </article>
            @endforeach
        </section>
    </div>
@endsection
