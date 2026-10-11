@extends('layouts.staff')

@section('title', 'Verifikasi Pembayaran')

@section('content')
    @php
        $default = $queue['rows'][0] ?? null;
    @endphp

    <div class="space-y-6" x-data="secretaryPayments(@js($queue['rows']), @js($queue['filters']), @js($inspection['operator_code']), @js($audit['total_transactions']))">
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="min-w-0">
                <h1 class="mt-2 font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $heading['title_before'] }}<br />
                    {{ $heading['title_after'] }}
                </h1>
            </div>

            <div class="flex shrink-0 items-center gap-3 rounded-xl bg-surface px-4 py-3 shadow-card outline outline-1 outline-line-hair">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                <span class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $heading['badge_label'] }}</span>
                <span class="gpa-meta-lg font-bold tracking-[0.88px] text-success-deep">{{ $heading['badge'] }}</span>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="flex min-h-[15.5rem] flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink-body">
                            {{ $metric['label'] }}
                        </h2>
                        <span @class(['shrink-0 rounded-lg p-1.5', $metric['icon_tile']])}>
                            <x-gpa.icon :name="$metric['icon']" class="h-[15px] w-[15px] shrink-0 {{ $metric['icon_class'] }}" />
                        </span>
                    </div>

                    <div>
                        <p class="flex flex-wrap items-baseline gap-2">
                            <span @class(['font-sans text-3xl font-bold leading-10 tracking-[-0.01em]', $metric['value_class']])}>
                                {{ $metric['value'] }}
                            </span>
                            @if (! empty($metric['unit']))
                                <span class="gpa-note text-ink-body">{{ $metric['unit'] }}</span>
                            @endif
                        </p>
                        <p @class(['mt-1', $metric['detail_class']])}>{{ $metric['detail'] }}</p>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                        <p @class(['gpa-micro-bold tracking-[1.08px]', $metric['foot_class']])}>
                            {{ $metric['foot_label'] }}
                        </p>
                        <span @class(['inline-flex shrink-0 rounded px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px]', $metric['chip']['class']])}>
                            {{ $metric['chip']['label'] }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1fr)_30rem]">
            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-3">

                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold leading-6 text-ink">{{ $queue['title'] }}</h2>
                        </div>
                    </div>

                    <button type="button" @click="sync()"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-surface-shell px-3 py-2 outline outline-1 outline-line-hair transition-colors hover:bg-surface-track gpa-micro-bold uppercase tracking-[1.08px] text-ink-body">
                        <x-gpa.icon name="refresh" class="h-[15px] w-[15px] shrink-0 text-success-deep" />
                        {{ $queue['sync_label'] }}
                        <span class="font-mono text-ink-quiet">{{ $queue['sync_duration'] }}</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($queue['filters'] as $filter)
                        <button type="button" @click="setChannel(@js($filter['key']))"
                            :class="channel === @js($filter['key'])
                                ? 'bg-brand text-white'
                                : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                            class="rounded-full px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] transition-colors">
                            {{ $filter['label'] }}
                            <span class="font-mono opacity-80" x-text="'(' + filterCount(@js($filter['key'])) + ')'">{{ count($queue['rows']) }}</span>
                        </button>
                    @endforeach
                </div>

                <ul class="flex flex-col gap-2.5">
                    @foreach ($queue['rows'] as $row)
                        <li>
                            <button type="button" @click="inspect(@js($row))" :class="rowClass(@js($row))"
                                class="flex w-full flex-col gap-2 rounded-xl p-4 text-left transition-colors">
                                <span class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="rounded bg-brand px-2 py-1 gpa-micro-bold tracking-[1.08px] text-accent">{{ $row['id'] }}</span>
                                        <span class="font-mono text-2xs font-medium tracking-[0.88px] text-ink-quiet">{{ $row['invoice'] }}</span>
                                    </span>

                                    <span @if (! $loop->first) style="display:none" @endif
                                        x-show="paymentId === @js($row['id'])"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-success px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-accent">
                                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-accent-deep" aria-hidden="true"></span>
                                        Inspeksi Aktif
                                    </span>

                                    <span style="display:none" x-show="isSettled(@js($row))"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-success-deep">
                                        Settled Lunas
                                    </span>

                                    <span style="display:none" x-show="isRejected(@js($row))"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-danger-soft px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-danger-ink outline outline-1 outline-danger/60">
                                        Ditolak
                                    </span>

                                    <span @if ($loop->first) style="display:none" @endif
                                        x-show="paymentId !== @js($row['id']) && ! isSettled(@js($row)) && ! isRejected(@js($row))"
                                        class="rounded-full bg-surface-pill px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body outline outline-1 outline-line-board">
                                        Pending
                                    </span>
                                </span>

                                <span class="flex flex-wrap items-end justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block font-sans text-sm font-bold leading-5 text-ink">{{ $row['client'] }}</span>
                                        <span class="mt-0.5 flex items-center gap-1.5 font-mono text-2xs font-medium tracking-[0.88px] text-ink-quiet">
                                            <x-gpa.icon name="clock" class="h-3 w-3 shrink-0" />
                                            {{ $row['channel_label'] }} &bull; Jam {{ $row['time'] }} WIB
                                        </span>
                                    </span>

                                    <span class="text-right">
                                        <span class="block font-mono text-sm font-bold tracking-[0.88px] {{ $row['status'] === 'inspect' ? 'text-brand' : 'text-ink' }}">
                                            {{ $row['amount_label'] }}
                                        </span>
                                        <span @class([
                                            'mt-0.5 block gpa-micro-bold uppercase tracking-[1.08px]',
                                            $row['status'] === 'inspect' ? 'text-success-deep' : 'text-ink-quiet',
                                        ])>{{ $row['evidence'] }}</span>
                                    </span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-surface-shell px-4 py-3">
                    <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-body"
                        x-text="'Memori Antrean: ' + visibleRows().length + ' dari ' + queue.length + ' Ditampilkan'">
                        {{ $queue['footer_left'] }}
                    </p>
                </div>
            </section>

            <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card outline outline-2 outline-success">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft pb-4">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold leading-6 text-ink">{{ $inspection['title'] }}</h2>
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep">
                                <span x-text="inspectionRef">Ref Kasir: {{ $default['id'] }}</span><span>{{ $inspection['ref_suffix'] }}</span>
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep">
                        <x-gpa.icon name="shield" class="h-3 w-3 shrink-0" />
                        {{ $inspection['chip'] }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-hair sm:grid-cols-4">
                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['meta']['client'] }}</p>
                        <p class="mt-1 font-sans text-sm font-bold leading-5 text-ink" x-text="field(selected, 'client_short')">{{ $default['client_short'] }}</p>
                    </div>

                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['meta']['invoice'] }}</p>
                        <p class="mt-1 font-mono text-xs font-bold tracking-[0.88px] text-ink" x-text="field(selected, 'invoice')">{{ $default['invoice'] }}</p>
                    </div>

                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['meta']['amount'] }}</p>
                        <p class="mt-1 font-mono text-sm font-bold tracking-[0.88px] text-brand" x-text="field(selected, 'amount_label')">{{ $default['amount_label'] }}</p>
                    </div>

                    <div>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['meta']['due'] }}</p>
                        <p class="mt-1 font-sans text-sm font-bold leading-5 text-warning-caution" x-text="field(selected, 'due')">{{ $default['due'] }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="flex flex-col gap-3 rounded-xl bg-surface p-3 outline outline-1 outline-line-hair">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <x-gpa.icon name="file-text" class="h-4 w-4 shrink-0 text-ink" />
                                <p class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-ink">{{ $inspection['slip_title'] }}</p>
                            </div>
                            <span class="shrink-0 rounded bg-surface-track px-2 py-1 gpa-micro-bold tracking-[1.08px] text-ink-body"
                                x-text="field(selected, 'evidence_type')">{{ $default['evidence_type'] }}</span>
                        </div>

                        <div class="flex flex-col items-center gap-2 rounded-lg bg-brand-deep p-3 text-center outline outline-1 outline-success">
                            <x-gpa.icon name="receipt" class="h-6 w-6 shrink-0 text-accent" />
                            <p class="font-mono text-2xs font-bold tracking-[0.88px] text-accent" x-text="field(selected, 'evidence_file')">{{ $default['evidence_file'] }}</p>

                            <dl class="w-full space-y-0.5 text-left font-mono text-[10px] leading-4 text-white/80">
                                <div>
                                    <dt class="inline text-white/50">Pengirim: </dt>
                                    <dd class="inline" x-text="field(selected, 'slip_sender')">{{ $default['slip_sender'] }}</dd>
                                </div>
                                <div>
                                    <dt class="inline text-white/50">Rek Asal: </dt>
                                    <dd class="inline" x-text="field(selected, 'slip_account')">{{ $default['slip_account'] }}</dd>
                                </div>
                                <div>
                                    <dt class="inline text-white/50">Nominal: </dt>
                                    <dd class="inline" x-text="field(selected, 'slip_nominal')">{{ $default['slip_nominal'] }}</dd>
                                </div>
                                <div>
                                    <dt class="inline text-white/50">Stempel: </dt>
                                    <dd class="inline" x-text="field(selected, 'slip_stamp')">{{ $default['slip_stamp'] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <p class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep"
                                x-text="field(selected, 'evidence_note')">{{ $default['evidence_note'] }}</p>
                            <button type="button" @click="openProof(selected)"
                                class="shrink-0 font-sans text-xs font-bold text-success-deep underline decoration-success-deep/40 underline-offset-2">
                                {{ $inspection['slip_footer'] }}
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 rounded-xl bg-success-soft/40 p-3 outline outline-1 outline-success">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <x-gpa.icon name="check-circle" class="h-4 w-4 shrink-0 text-success-deep" />
                                <p class="gpa-micro-bold uppercase leading-4 tracking-[1.08px] text-success-deep">{{ $inspection['mutation_title'] }}</p>
                            </div>
                            <span class="shrink-0 rounded bg-accent px-2 py-1 gpa-micro-bold uppercase tracking-[1.08px] text-success-deep outline outline-1 outline-accent-deep"
                                x-text="mutationChip">{{ $inspection['mutation_chip'] }}</span>
                        </div>

                        <dl class="flex flex-col gap-2 rounded-lg bg-surface p-3">
                            <div class="flex items-center justify-between gap-2">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['mutation_fields']['account'] }}</dt>
                                <dd class="font-mono text-2xs font-bold tracking-[0.88px] text-ink" x-text="field(selected, 'giro_account')">{{ $default['giro_account'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['mutation_fields']['time'] }}</dt>
                                <dd class="font-mono text-2xs font-bold tracking-[0.88px] text-ink" x-text="field(selected, 'giro_time')">{{ $default['giro_time'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['mutation_fields']['entry'] }}</dt>
                                <dd class="gpa-micro-bold uppercase tracking-[1.08px] text-success-deep" x-text="field(selected, 'giro_entry')">{{ $default['giro_entry'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                                <dt class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['mutation_fields']['amount'] }}</dt>
                                <dd class="font-mono text-sm font-bold tracking-[0.88px] text-brand" x-text="field(selected, 'giro_nominal')">{{ $default['giro_nominal'] }}</dd>
                            </div>
                        </dl>

                        <p class="rounded-lg bg-accent/40 px-3 py-2 gpa-note font-medium text-ink-body outline outline-1 outline-accent-deep/50"
                            x-text="mutationNote">{{ $inspection['mutation_note'] }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink">{{ $inspection['note_label'] }}</p>
                        <p class="gpa-micro-bold uppercase tracking-[1.08px] text-ink-quiet">{{ $inspection['note_stamp'] }}</p>
                    </div>

                    <textarea x-model="note" rows="2"
                        class="w-full rounded-lg bg-surface p-3 text-xs leading-5 text-ink outline outline-1 outline-line-hair focus:outline-2 focus:outline-success-deep">{{ $default['note'] }}</textarea>

                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <button type="button" @click="saveNote()"
                            class="inline-flex items-center gap-2 rounded-xl bg-surface-track px-4 py-2.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink transition-colors hover:bg-surface-disabled">
                            <x-gpa.icon name="save" class="h-[15px] w-[15px] shrink-0" />
                            Simpan Catatan Audit
                        </button>

                        <button type="button" @click="reject()"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl px-4 gpa-meta-lg font-bold tracking-[0.88px] text-danger outline outline-2 outline-danger transition-colors hover:bg-danger-soft">
                            <x-gpa.icon name="x" class="h-4 w-4 shrink-0" />
                            {{ $inspection['reject_label'] }}
                        </button>

                        <button type="button" @click="approve()" :disabled="! checkComplete || ! selected"
                            :class="checkComplete
                                ? 'bg-accent-deep text-ink hover:bg-accent'
                                : 'cursor-not-allowed bg-surface-disabled text-ink-quiet opacity-70'"
                            class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-xl px-4 gpa-meta-lg font-bold tracking-[0.88px] transition-colors sm:flex-none">
                            <x-gpa.icon name="check" class="h-4 w-4 shrink-0" />
                            {{ $inspection['approve_label'] }}
                        </button>
                    </div>
                </div>
            </section>
        </div>

        <section class="flex flex-col gap-4 rounded-2xl bg-surface p-6 shadow-card">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft pb-4">
                <div class="flex min-w-0 items-center gap-3">

                    <div class="min-w-0">
                        <h2 class="font-sans text-lg font-bold leading-6 text-ink">{{ $audit['title'] }}</h2>
                    </div>
                </div>

            </div>

            <div class="gpa-scroll-x rounded-xl outline outline-1 outline-line-board/60">
                <table class="w-full min-w-[1180px] border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-shell">
                        <tr>
                            @foreach ($audit['columns'] as $column)
                                <th scope="col" class="px-4 py-3 gpa-meta font-semibold uppercase tracking-[0.55px] text-ink-body">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-soft">
                        @foreach ($audit['rows'] as $row)
                            <tr @class(['transition-colors hover:bg-surface-shell/50', $row['status'] === 'rejected' ? 'bg-danger-soft/20' : ''])>
                                <td class="px-4 py-4 font-mono text-2xs font-medium tracking-[0.88px] text-ink-quiet">
                                    {{ $row['timestamp'] }}
                                </td>

                                <td class="px-4 py-4 font-mono text-xs font-bold tracking-[0.88px] text-ink">{{ $row['id'] }}</td>

                                <td class="px-4 py-4 font-mono text-2xs font-medium tracking-[0.88px] text-ink-body">{{ $row['invoice'] }}</td>

                                <td class="px-4 py-4 font-sans text-sm font-semibold leading-5 text-ink">{{ $row['client'] }}</td>

                                <td class="px-4 py-4 font-mono text-xs font-bold tracking-[0.88px] {{ $row['amount_class'] }}">
                                    {{ $row['amount_label'] }}
                                </td>

                                <td class="px-4 py-4">
                                    <span @class([
                                        'inline-flex rounded-full px-2.5 py-1 gpa-micro-bold uppercase tracking-[1.08px]',
                                        $row['status_class'],
                                    ])>{{ $row['status_label'] }}</span>
                                </td>

                                <td class="px-4 py-4 font-mono text-2xs font-bold tracking-[0.88px] text-ink-body">{{ $row['operator'] }}</td>

                                <td @class([
                                    'px-4 py-4 gpa-note leading-5',
                                    $row['status'] === 'rejected' ? 'text-danger' : 'text-ink-body',
                                ])>{{ $row['note'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft pt-3">
                <p class="gpa-note text-ink-body"
                    x-text="'Menampilkan ' + {{ count($audit['rows']) }} + ' dari ' + auditTotal + ' Transaksi Rekonsiliasi Hari Ini'">
                    Menampilkan {{ count($audit['rows']) }} dari {{ $audit['total_transactions'] }} Transaksi Rekonsiliasi Hari Ini
                </p>

                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="goToPage(auditPage - 1)"
                        class="rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body transition-colors hover:bg-surface-track">
                        {{ $audit['pagination']['previous'] }}
                    </button>

                    @foreach ($audit['pagination']['pages'] as $page)
                        <button type="button" @click="goToPage({{ $page }})"
                            :class="auditPage === {{ $page }} ? 'bg-brand text-white' : 'bg-surface-track text-ink-body hover:bg-surface-disabled'"
                            class="h-8 min-w-8 rounded-lg px-2 gpa-micro-bold tracking-[1.08px] transition-colors">{{ $page }}</button>
                    @endforeach

                    <button type="button" @click="goToPage(auditPage + 1)"
                        class="rounded-lg px-3 py-1.5 gpa-micro-bold uppercase tracking-[1.08px] text-ink-body transition-colors hover:bg-surface-track">
                        {{ $audit['pagination']['next'] }}
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection
