@extends('layouts.staff')

@section('title', 'Dashboard Sekretaris')

@section('content')
    <div class="space-y-6">
        <section class="gpa-panel overflow-hidden">
            <div class="border-b border-line-soft px-6 py-6">
                <h1 class="max-w-4xl font-sans text-3xl font-bold leading-10 tracking-[-0.01em] text-ink">
                    {{ $heading['title'] }}
                </h1>
                <p class="mt-2 max-w-4xl text-sm leading-[1.4rem] text-ink-body">
                    {{ $heading['subtitle'] }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2 px-6 py-5">
                @foreach ($toolbar as $item)
                    <button type="button" @click="run(@js($item['label']), @js($item['message']))"
                        class="inline-flex h-9 items-center gap-1.5 rounded-full bg-surface px-3.5 shadow-sub outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <x-gpa.icon :name="$item['icon']" class="h-3.5 w-3.5 shrink-0 text-success-deep" />
                        <span class="gpa-meta-lg font-semibold tracking-[0.28px] text-ink">{{ $item['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <article class="flex flex-col justify-between gap-5 rounded-2xl bg-surface p-6 shadow-card">
                    <div class="flex items-start justify-between gap-3">
                        <p class="gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body">
                            {{ $metric['label'] }}
                        </p>

                        @if (! empty($metric['badge']))
                            <span
                                class="shrink-0 rounded border px-1.5 py-0.5 gpa-micro {{ $metric['badge']['class'] }}">
                                {{ $metric['badge']['label'] }}
                            </span>
                        @else
                            <span
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg p-1.5 {{ $metric['icon_class'] }}">
                                <x-gpa.icon :name="$metric['icon']" class="h-[15px] w-[15px]" />
                            </span>
                        @endif
                    </div>

                    <div>
                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span class="font-sans text-[2rem] font-bold leading-10 tracking-[-0.01em] {{ $metric['value_class'] }}">
                                {{ $metric['value'] }}
                            </span>
                            <span class="text-sm {{ $metric['unit_class'] ?? 'font-medium' }} text-ink-body">
                                {{ $metric['unit'] }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs leading-4">
                            @foreach ($metric['note'] as $segment)
                                <span class="{{ $segment['class'] }}">{{ $segment['text'] }}</span>
                            @endforeach
                        </p>
                    </div>

                    <div
                        class="flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                        @if ($metric['foot']['label'] !== '')
                            <p class="gpa-note text-ink-body">{{ $metric['foot']['label'] }}</p>
                        @else
                            <span aria-hidden="true"></span>
                        @endif

                        <div class="flex items-center gap-2">
                            @if (! empty($metric['foot']['chip']))
                                <span
                                    class="inline-flex items-center gap-1 rounded border border-line-board bg-surface-pill px-1.5 py-0.5 gpa-micro-bold text-ink">
                                    <x-gpa.icon :name="$metric['foot']['chip']['icon']" class="h-3 w-3" />
                                    {{ $metric['foot']['chip']['label'] }}
                                </span>
                            @endif
                            <span
                                class="gpa-meta-lg font-bold tracking-[0.28px] {{ $metric['foot']['value_class'] }}">
                                {{ $metric['foot']['value'] }}
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]">
            <div class="min-w-0 space-y-4">
                <section class="gpa-panel overflow-hidden">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft px-6 py-5">
                        <div class="min-w-0">
                            <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                                <x-gpa.icon name="shield" class="h-4 w-4 shrink-0 text-success-deep" />
                                {{ $queue['title'] }}
                            </h2>
                            <p class="mt-1 text-xs text-ink-body">{{ $queue['description'] }}</p>
                        </div>
                        <span class="shrink-0 rounded bg-surface-pill px-2 py-1 gpa-micro text-ink-body">
                            {{ $queue['chip'] }}
                        </span>
                    </div>

                    <div class="gpa-scroll-x">
                        <table class="w-full min-w-[700px] border-collapse text-left">
                            <thead class="bg-surface-shell">
                                <tr class="border-b border-line-soft">
                                    @foreach ($queue['columns'] as $key => $label)
                                        <th scope="col"
                                            class="px-4 py-3 gpa-meta-lg uppercase leading-[0.875rem] tracking-[0.55px] text-ink-body first:pl-6 last:pr-6 {{ $key === 'action' ? 'text-right' : '' }}">
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line-faint">
                                @foreach ($queue['rows'] as $row)
                                    <tr x-show="isRowVisible(@js($row))" class="transition-colors hover:bg-surface-shell/60">
                                        <td class="px-4 py-4 align-top first:pl-6">
                                            <div class="flex flex-col gap-0.5">
                                                <span class="gpa-note text-ink-subtle">{{ $row['po'] }}</span>
                                                <span class="text-xs font-bold leading-5 text-ink">{{ $row['client'] }}</span>
                                                @if (! empty($row['po_chip']))
                                                    <span
                                                        class="mt-0.5 w-fit rounded px-1.5 py-0.5 gpa-micro {{ $row['po_chip']['class'] }}">
                                                        {{ $row['po_chip']['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <p class="gpa-note {{ $row['terms']['class'] }}">{{ $row['terms']['label'] }}</p>
                                            <ul class="mt-1 flex flex-col gap-0.5">
                                                @foreach ($row['items'] as $item)
                                                    <li class="text-xs {{ $item['class'] }}">
                                                        {{ $item['name'] }}
                                                        <span class="gpa-note text-ink-body">({{ $item['weight'] }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-1 gpa-note text-success-deep">Est: {{ $row['estimate'] }}</p>
                                        </td>
                                        <td class="px-4 py-4 align-top">
                                            <span
                                                class="inline-flex items-center gap-1 rounded border px-1.5 py-0.5 gpa-micro {{ $row['status']['class'] }}">
                                                <x-gpa.icon :name="$row['status']['icon']" class="h-3 w-3 shrink-0" />
                                                {{ $row['status']['label'] }}
                                            </span>
                                            <p class="mt-1 gpa-note {{ $row['status_note']['class'] }}">
                                                {{ $row['status_note']['label'] }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-4 align-top last:pr-6">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @foreach ($row['actions'] as $action)
                                                    @php
                                                        $actionClass = match ($action['tone']) {
                                                            'ink' => 'bg-ink text-white hover:bg-brand-deep',
                                                            'warning' => 'bg-warning-deep text-white hover:bg-warning',
                                                            default => 'bg-surface-pill text-ink-body hover:bg-surface-disabled hover:text-ink',
                                                        };
                                                    @endphp
                                                    <button type="button" @click="handle(@js($row['po']), @js($action))"
                                                        class="inline-flex h-7 items-center justify-center rounded px-2 gpa-micro transition-colors {{ $actionClass }}">
                                                        {{ $action['label'] }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                <tr x-cloak x-show="visibleRows(@js($queue['rows'])).length === 0">
                                    <td colspan="4" class="px-6 py-10 text-center">
                                        <p class="text-xs font-semibold text-ink">Tidak ada PO yang cocok dengan pencarian.</p>
                                        <p class="mt-1 gpa-note text-ink-quiet">Coba nomor PO lain, nama klien, atau nama komoditas.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft bg-surface-shell px-6 py-3">
                        <p class="gpa-note text-ink-body"
                            x-text="'Menampilkan ' + visibleRows(@js($queue['rows'])).length + ' dari ' + pendingCount(@js($queue['rows'])) + ' antrean prioritas cut-off 16:00 WIB'">
                        </p>
                        <div class="flex items-center gap-3">
                            <button type="button" x-cloak x-show="resolved.length > 0" @click="resolved = []"
                                class="gpa-note text-success-deep underline underline-offset-2 hover:text-success">
                                <span x-text="'Tampilkan ' + resolved.length + ' PO Diproses'"></span>
                            </button>
                            <button type="button"
                                @click="run('Antrean PO', 'Modul Verifikasi Pesanan membuka seluruh antrean order masuk.')"
                                class="gpa-note text-ink-body underline underline-offset-2 hover:text-ink">
                                {{ $queue['footer_right'] }} &rarr;
                            </button>
                        </div>
                    </div>
                </section>

                <section class="gpa-panel p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold text-ink">{{ $pipeline['title'] }}</h2>
                            <p class="mt-1 text-xs text-ink-body">{{ $pipeline['description'] }}</p>
                        </div>
                        <span class="shrink-0 rounded bg-surface-pill px-2 py-1 gpa-micro-bold text-ink">
                            {{ $pipeline['chip'] }}
                        </span>
                    </div>

                    <ol class="grid grid-cols-2 gap-3 pt-4 sm:grid-cols-3 xl:grid-cols-5">
                        @foreach ($pipeline['stages'] as $stage)
                            <li @class([
                                'rounded-xl border bg-surface-shell p-3',
                                'border-success-deep/40' => $stage['active'] ?? false,
                                'border-line-soft' => ! ($stage['active'] ?? false),
                            ])>
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-full gpa-micro-bold {{ $stage['node_class'] }}">
                                        {{ $stage['node'] }}
                                    </span>
                                    <x-gpa.icon :name="$stage['icon']" class="h-3.5 w-3.5 shrink-0 text-ink-quiet" />
                                </div>
                                <p class="mt-3 font-sans text-lg font-bold leading-6 text-ink">{{ $stage['value'] }}</p>
                                <p class="gpa-meta-lg font-semibold tracking-[0.88px] text-ink">{{ $stage['label'] }}</p>
                                <p class="mt-0.5 gpa-meta {{ $stage['note_class'] }}">{{ $stage['note'] }}</p>
                            </li>
                        @endforeach
                    </ol>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                        <p class="flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                            <span class="gpa-note text-ink-body">{{ $pipeline['footer_label'] }}</span>
                            <span class="gpa-note text-ink">{{ $pipeline['footer_value'] }}</span>
                        </p>
                        <p class="gpa-note text-ink">{{ $pipeline['footer_sla'] }}</p>
                    </div>
                </section>
            </div>

            <div class="min-w-0 space-y-4">
                <section class="gpa-panel p-6">
                    <div class="flex items-start justify-between gap-3 border-b border-line-soft pb-3">
                        <div class="min-w-0">
                            <h2 class="font-sans text-lg font-bold text-ink">{{ $stock['title'] }}</h2>
                            <p class="mt-1 text-xs text-ink-body">{{ $stock['description'] }}</p>
                        </div>
                        <x-gpa.icon :name="$stock['icon']" class="h-3 w-3 shrink-0 text-ink-body" />
                    </div>

                    <ul class="space-y-4 pt-4">
                        @foreach ($stock['rows'] as $row)
                            <li class="rounded-xl border p-2.5 {{ $row['card_class'] }}">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="flex items-center gap-1.5 text-xs font-bold leading-5 text-ink">
                                        @if ($row['alert'])
                                            <x-gpa.icon name="alert-triangle" class="h-3.5 w-3.5 shrink-0 text-danger" />
                                        @endif
                                        {{ $row['name'] }}
                                    </p>
                                    <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro {{ $row['chip']['class'] }}">
                                        {{ $row['chip']['label'] }}
                                    </span>
                                </div>

                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-pill"
                                    role="img" aria-label="{{ $row['chip']['label'] }}">
                                    <div class="h-full rounded-full {{ $row['fill_class'] }}" style="width: {{ $row['percent'] }}%"></div>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                                    @foreach ($row['foot'] as $note)
                                        <p class="gpa-note {{ $note['class'] }}">{{ $note['label'] }}</p>
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3">
                        <p class="gpa-note text-ink-body">{{ $stock['footer_left'] }}</p>
                        <button type="button"
                            @click="run('Alokasi Stok', 'Modul Stok membuka alokasi buffer panen per gudang transit.')"
                            class="gpa-meta-lg font-semibold tracking-[0.88px] text-success-deep hover:text-success">
                            {{ $stock['footer_right'] }} &rarr;
                        </button>
                    </div>
                </section>

                <section class="gpa-panel p-6">
                    <div class="flex items-center gap-2 border-b border-line-soft pb-3">
                        <x-gpa.icon name="alert-circle" class="h-4 w-[17px] shrink-0 text-warning" />
                        <h2 class="font-sans text-lg font-bold text-ink">{{ $alerts['title'] }}</h2>
                    </div>

                    <ul class="space-y-3 pt-3">
                        @foreach ($alerts['items'] as $index => $item)
                            <li x-show="! dismissed.includes({{ $index }})"
                                class="rounded-xl border-l-4 bg-surface-shell p-3 {{ $item['card_class'] }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex min-w-0 items-start gap-2">
                                        <x-gpa.icon :name="$item['icon']" class="mt-0.5 h-4 w-4 shrink-0 {{ $item['icon_class'] }}" />
                                        <p class="gpa-meta-lg font-bold leading-[0.875rem] tracking-[0.88px] text-ink">
                                            {{ $item['title'] }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 gpa-note {{ $item['value_class'] }}">{{ $item['value'] }}</span>
                                </div>
                                <p class="mt-1 pl-6 text-xs leading-[1.375rem] text-ink-body">{{ $item['body'] }}</p>
                                <button type="button" @click="dismiss({{ $index }})"
                                    class="mt-1 pl-6 gpa-note text-ink-quiet underline underline-offset-2 hover:text-ink">
                                    Arsipkan
                                </button>
                            </li>
                        @endforeach

                        <li x-cloak x-show="visibleAlerts(@js($alerts['items'])).length === 0"
                            class="rounded-xl border border-dashed border-line-board bg-surface-shell px-3 py-6 text-center">
                            <p class="text-xs font-semibold text-ink">Tidak ada peringatan aktif.</p>
                            <p class="mt-1 gpa-note text-ink-quiet">Seluruh notifikasi telah diarsipkan.</p>
                        </li>
                    </ul>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-line-faint pt-3">
                        <p class="flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success" aria-hidden="true"></span>
                            <span class="gpa-note text-ink-body">{{ $alerts['footer_left'] }}</span>
                        </p>
                        <button type="button"
                            @click="run('Arsip Notifikasi', 'Riwayat peringatan operasional dibuka.')"
                            class="gpa-note text-ink underline underline-offset-2 hover:text-success-deep">
                            {{ $alerts['footer_right'] }} &rarr;
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection