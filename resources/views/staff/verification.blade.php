@extends('layouts.staff')

@section('title', 'Verifikasi Pesanan')

@section('content')
    <div class="space-y-6" x-data="secretaryVerification(@js($queue['orders']), {{ count($checklist['items']) }})">
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-line-soft px-6 py-6">
                <div class="min-w-0">
                    <h1 class="font-sans text-3xl font-extrabold leading-10 tracking-[-0.01em] text-brand">
                        {{ $heading['title'] }}
                    </h1>
                </div>

                <div
                    class="shrink-0 rounded-xl bg-surface px-4 py-2 text-right shadow-sub outline outline-1 outline-line-board">
                    <p class="gpa-micro text-ink-quiet">{{ $heading['compliance_label'] }}</p>
                    <p class="gpa-meta-lg font-bold tracking-[0.28px] text-success-deep">
                        {{ $heading['compliance_value'] }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 px-6 py-4">
                @foreach ($steps as $step)
                    <button type="button" @click="handleStep(@js($step))" @class([
                        'inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-left transition-colors',
                        'bg-brand shadow-sub text-accent' => $step['active'],
                        'bg-surface text-ink-body outline outline-1 outline-line-board hover:bg-surface-muted' => !$step['active'],
                    ])>
                        <span class="h-2 w-2 shrink-0 rounded-full {{ $step['active'] ? 'bg-accent' : 'bg-line-board' }}"
                            aria-hidden="true"></span>
                        <span class="gpa-meta-lg font-semibold tracking-[0.88px]">
                            {{ $step['number'] }} {{ $step['label'] }}
                        </span>
                        @if ($step['active'])
                            <span x-text="'(' + pendingCount() + ' ' + @js($step['note']) + ')'"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </section>

        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5">
                    <h2 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        {{ $queue['title'] }}
                    </h2>
                    <span class="shrink-0 rounded-full bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                        {{ $queue['chip'] }}
                    </span>
                </div>

                <div class="px-6 pb-1">
                    <div class="inline-flex flex-wrap gap-1.5 rounded-lg bg-surface-shell p-1" role="group"
                        aria-label="Saring antrean pesanan">
                        @foreach ($queue['filters'] as $filter)
                            <button type="button" @click="filter = @js($filter['key'])"
                                :class="filterClass(@js($filter['key']))"
                                class="rounded px-2.5 py-1.5 gpa-micro transition-colors">
                                {{ $filter['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-2 p-4 pt-4">
                    @foreach ($queue['orders'] as $order)
                        <article x-show="matchesFilter(@js($order))" :class="cardClass(@js($order))"
                            class="group relative rounded-xl p-3 transition-colors">
                            <span x-cloak x-show="isSelected(@js($order['id']))"
                                class="absolute -top-2 right-4 rounded bg-success-deep px-2 py-0.5 gpa-micro-bold text-white">
                                Terpilih Inspeksi
                            </span>

                            <button type="button" @click="select(@js($order['id']))" class="w-full text-left">
                                <div class="flex items-start justify-between gap-3 pt-2">
                                    <div class="min-w-0">
                                        <p class="gpa-note text-success-deep">{{ $order['id'] }}</p>
                                        <p class="font-sans text-[0.9375rem] font-bold leading-5 text-ink">
                                            {{ $order['client'] }}
                                        </p>
                                        <p class="mt-0.5 text-xs leading-5 text-ink-body">{{ $order['meta'] }}</p>
                                    </div>

                                    @if (!empty($order['chip']))
                                        <span class="shrink-0 rounded px-1.5 py-0.5 gpa-micro-bold {{ $order['chip']['class'] }}">
                                            {{ $order['chip']['label'] }}
                                        </span>
                                    @else
                                        <x-gpa.icon name="shield" class="h-5 w-5 shrink-0 text-success-deep" />
                                    @endif
                                </div>

                                <div class="mt-2 grid grid-cols-2 gap-3 border-t border-line-soft pt-2">
                                    <div class="min-w-0">
                                        <p class="gpa-micro text-ink-quiet">{{ $order['queue_commodity_label'] }}</p>
                                        @foreach ($order['queue_commodities'] as $commodity)
                                            <p class="text-xs font-semibold leading-5 {{ $commodity['class'] }}">
                                                {{ $commodity['name'] }}
                                                <span class="gpa-note {{ $commodity['weight_class'] }}">
                                                    ({{ $commodity['weight'] }})
                                                </span>
                                            </p>
                                        @endforeach
                                    </div>

                                    <div class="text-right">
                                        <p class="gpa-micro text-ink-quiet">{{ $order['queue_estimate_label'] }}</p>
                                        <p class="gpa-meta-lg font-bold tracking-[0.28px] text-ink">
                                            {{ $order['queue_estimate'] }}
                                        </p>
                                        <p class="gpa-note {{ $order['queue_estimate_note']['class'] }}">
                                            {{ $order['queue_estimate_note']['label'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2 border-t border-line-soft pt-2">
                                    <span
                                        class="inline-flex items-center gap-1 rounded px-2 py-0.5 gpa-micro {{ $order['queue_status']['class'] }}">
                                        <x-gpa.icon :name="$order['queue_status']['icon']" class="h-3 w-3 shrink-0" />
                                        {{ $order['queue_status']['label'] }}
                                    </span>
                                    <span class="gpa-note text-ink-quiet">{{ $order['entered'] }}</span>
                                </div>
                            </button>
                        </article>
                    @endforeach

                    <p x-cloak x-show="visibleCount() === 0"
                        class="rounded-xl border border-dashed border-line-board bg-surface-shell px-3 py-8 text-center">
                        <span class="text-xs font-semibold text-ink">Tidak ada pesanan pada antrean ini.</span>
                        <span class="mt-1 block gpa-note text-ink-quiet">
                            Ubah filter kanal atau pulihkan pesanan yang sudah diproses.
                        </span>
                    </p>
                </div>

                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft bg-surface-shell px-6 py-3">
                    <p class="gpa-note text-ink-body"
                        x-text="'Menampilkan ' + visibleCount() + ' dari ' + pendingCount() + ' pesanan menunggu verifikasi'">
                    </p>
                    <button type="button" x-cloak x-show="resolved.length > 0" @click="restore()"
                        class="gpa-note text-success-deep underline underline-offset-2 hover:text-success">
                        <span x-text="'Tampilkan ' + resolved.length + ' PO Diproses'"></span>
                    </button>
                </div>
        </section>

        <section id="order-inspection" class="gpa-panel scroll-mt-24 p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft pb-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="gpa-note text-ink-quiet" x-text="selected.po_ref"></span>
                    </div>
                    <h2 class="mt-2 font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink"
                        x-text="selected.order_no"></h2>
                </div>

                <div class="shrink-0 text-right">
                    <span :class="selected.status.class"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 outline outline-1 outline-success-deep gpa-micro-bold">
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-success-deep" aria-hidden="true"></span>
                        <span x-text="selected.status.label"></span>
                    </span>
                    <p class="mt-1 gpa-note text-ink-quiet" x-text="selected.created"></p>
                </div>
            </div>

            <div class="mt-4 grid gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft md:grid-cols-3">
                <template x-for="block in selected.identity" :key="block.label">
                    <div class="min-w-0">
                        <p class="gpa-micro text-ink-quiet" x-text="block.label"></p>
                        <p class="mt-1 font-sans text-sm font-bold leading-5 text-ink" x-text="block.name"></p>
                        <p class="gpa-note mt-0.5" :class="block.code_class" x-text="block.code"></p>
                        <p class="mt-1 text-xs leading-5 text-ink-body" x-text="block.note"></p>
                    </div>
                </template>
            </div>

            <div class="mt-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="flex items-center gap-2 font-sans text-lg font-bold text-ink">
                        {{ $rule03['title'] }}
                    </h3>

                    <span :class="selected.rule03_status.class"
                        class="inline-flex items-center gap-1.5 rounded px-2.5 py-1 outline outline-1 outline-success-deep gpa-micro-bold">
                        <x-gpa.icon name="check" class="h-3 w-3 shrink-0" />
                        <span x-text="selected.rule03_status.label"></span>
                    </span>
                </div>

                <div class="gpa-scroll-x mt-3 rounded-xl outline outline-1 outline-line-soft">
                    <table class="w-full min-w-[880px] border-collapse text-left">
                        <thead class="border-b border-line-soft bg-surface-track">
                            <tr>
                                <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Komoditas &amp; Spesifikasi
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-center gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Permintaan (PO)
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-center gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Stok Sistem GPA
                                </th>
                                <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Rencana Alokasi Pasokan (Harvest + Buffer)
                                </th>
                                <th scope="col"
                                    class="px-4 py-3 text-right gpa-meta-lg uppercase tracking-[0.55px] text-ink-body">
                                    Status Validasi
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-line-faint">
                            <template x-for="line in selected.allocations" :key="line.name">
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-xs font-bold leading-5 text-ink" x-text="line.name"></p>
                                        <p class="mt-0.5 gpa-note text-ink-quiet" x-text="line.sku"></p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="gpa-meta-lg font-bold tracking-[0.28px] text-ink"
                                            x-text="line.request"></span>
                                        <span class="ml-1 gpa-note text-ink-quiet" x-text="line.request_unit"></span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="gpa-meta-lg font-bold tracking-[0.28px]" :class="line.system_class"
                                            x-text="line.system"></span>
                                        <span class="ml-1 gpa-note text-ink-quiet" x-text="line.system_unit"></span>
                                        <p class="mt-0.5 gpa-note" :class="line.surplus_class" x-text="line.surplus"></p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <template x-for="(step, index) in line.plan" :key="step.label">
                                            <div :class="index === 0 ? '' : 'mt-1.5'">
                                                <div class="flex items-baseline justify-between gap-2">
                                                    <p class="text-xs leading-5 text-ink" x-text="step.label"></p>
                                                    <p class="gpa-note shrink-0 text-ink" x-text="step.qty"></p>
                                                </div>
                                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-surface-pill">
                                                    <div class="h-full rounded-full" :class="step.fill"
                                                        :style="'width: ' + step.percent + '%'"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span :class="line.status.class"
                                            class="inline-flex items-center gap-1 rounded px-2 py-1 gpa-micro-bold">
                                            <x-gpa.icon name="lock" x-show="line.status.icon === 'lock'"
                                                class="h-3 w-3 shrink-0" />
                                            <x-gpa.icon name="alert-triangle" x-show="line.status.icon !== 'lock'"
                                                class="h-3 w-3 shrink-0" />
                                            <span x-text="line.status.label"></span>
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>

            <div
                class="mt-4 grid gap-4 rounded-xl bg-surface-shell p-4 outline outline-1 outline-line-soft sm:grid-cols-2 lg:grid-cols-4">
                <template x-for="cell in selected.pricing" :key="cell.label">
                    <div
                        :class="cell.highlight ? 'rounded-lg bg-surface px-3 py-2 outline outline-1 outline-success-deep/30' : ''">
                        <p class="gpa-micro text-ink-quiet" x-text="cell.label"></p>
                        <p class="mt-1 gpa-meta-lg font-bold tracking-[0.28px]"
                            :class="cell.highlight ? 'text-success-deep' : 'text-ink'">
                            <span x-text="cell.value"></span>
                            <span x-show="cell.suffix" class="gpa-note text-ink-body" x-text="cell.suffix"></span>
                        </p>
                        <p x-show="cell.note" class="mt-0.5 gpa-note text-ink-body" x-text="cell.note"></p>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-line-soft pt-4">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="reject()"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-danger outline outline-1 outline-danger transition-colors hover:bg-danger-soft gpa-meta-lg font-bold tracking-[0.88px]">
                        <x-gpa.icon name="x" class="h-3 w-3 shrink-0" />
                        Tolak Pesanan
                    </button>
                    <button type="button" @click="revise()"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg px-4 text-ink outline outline-1 outline-ink-quiet transition-colors hover:bg-surface-shell gpa-meta-lg font-semibold tracking-[0.88px]">
                        <x-gpa.icon name="info" class="h-3 w-3 shrink-0" />
                        Minta Revisi Klien
                    </button>
                </div>

                <button type="button" @click="verify()"
                    class="inline-flex h-11 items-center gap-2 rounded-lg bg-accent px-6 font-sans text-sm font-extrabold tracking-[0.35px] text-ink shadow-sub transition-colors hover:bg-accent-deep">
                    <x-gpa.icon name="check" class="h-4 w-3 shrink-0" />
                    Verifikasi &amp; Teruskan ke Koordinator Lapangan
                </button>
            </div>
        </section>

        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-line-soft px-6 py-5">
                <div class="min-w-0">
                    <h2 class="mt-2 font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink">
                        {{ $rule14['title'] }}
                    </h2>
                    <p class="mt-1.5 max-w-3xl text-xs leading-5 text-ink-body">
                        Akumulasi Delivery Order selesai kirim siap cetak faktur tagihan berkala untuk
                        <span class="font-semibold text-ink" x-text="selected.client"></span>
                        (Plafon Aktif: <span class="gpa-note text-success-deep" x-text="selected.plafon"></span>).
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" @click="draftInvoice()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-lg px-3 text-ink outline outline-1 outline-line-board transition-colors hover:bg-surface-shell gpa-meta-lg font-semibold tracking-[0.88px]">
                        <x-gpa.icon name="download" class="h-3 w-3 shrink-0" />
                        {{ $rule14['draft_label'] }}
                    </button>
                    <button type="button" @click="issueInvoice()"
                        class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-brand px-4 text-accent transition-colors hover:bg-brand-hover gpa-meta-lg font-bold tracking-[0.88px]">
                        <x-gpa.icon name="invoice" class="h-3 w-3 shrink-0 text-accent" />
                        {{ $rule14['issue_label'] }}
                    </button>
                </div>
            </div>

            <div class="gpa-scroll-x">
                <table class="w-full min-w-[880px] border-collapse text-left">
                    <thead class="border-b border-line-soft bg-surface-shell">
                        <tr>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-quiet">
                                No. Surat Jalan (SJ)
                            </th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-quiet">
                                Tanggal Kirim / Tiba
                            </th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-quiet">
                                Komoditas &amp; Netto Riil (Timbangan)
                            </th>
                            <th scope="col" class="px-4 py-3 gpa-meta-lg uppercase tracking-[0.55px] text-ink-quiet">
                                Tarif Kontrak
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-right gpa-meta-lg uppercase tracking-[0.55px] text-ink-quiet">
                                Subtotal Riil Tagihan
                            </th>
                            <th scope="col"
                                class="px-4 py-3 text-center gpa-meta-lg uppercase tracking-[0.55px] text-ink-quiet">
                                Status BAP Fisik
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-faint">
                        <template x-for="row in selected.deliveries" :key="row.sj">
                            <tr class="transition-colors hover:bg-surface-shell/60">
                                <td class="px-4 py-3">
                                    <span class="gpa-note text-success-deep" x-text="row.sj"></span>
                                </td>
                                <td class="px-4 py-3 text-xs text-ink" x-text="row.date"></td>
                                <td class="px-4 py-3">
                                    <p class="text-xs font-semibold leading-5 text-ink" x-text="row.commodity"></p>
                                    <p class="gpa-note mt-0.5 text-ink-body" x-text="row.netto"></p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="gpa-note text-ink-body" x-text="row.rate"></span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="gpa-note text-ink" x-text="row.subtotal"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                                        <x-gpa.icon name="check" class="h-2.5 w-2.5 shrink-0" />
                                        BAP TTD Lengkap
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-2 border-t-2 border-brand bg-surface-track px-6 py-3">
                <p class="gpa-note text-ink">Total Tagihan</p>
                <p class="font-sans text-lg font-extrabold tracking-[-0.01em] text-success-deep"
                    x-text="selected.deliveries_total"></p>
                <p class="gpa-note text-ink-quiet" x-text="selected.deliveries_held"></p>
            </div>
        </section>
    </div>
@endsection
