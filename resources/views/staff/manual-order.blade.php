@extends('layouts.staff')

@section('title', 'Input Order Manual')

@section('content')
    <div class="space-y-6"
        x-data="secretaryManualOrder(@js($commodities), @js($catalog), @js($logistics), @js($payment), @js($source), @js($client))">

        {{-- Policy banner: Supply Chain Guardrails --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-line-soft px-6 py-5">
                <div class="min-w-0">
                    <p class="gpa-eyebrow">{{ $policy['eyebrow'] }}</p>
                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                        <h1 class="gpa-figure text-brand">Input Order Manual</h1>
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-warning-soft px-2 py-0.5 gpa-micro-bold text-warning-caution">
                            <span class="h-1.5 w-1.5 rounded-full bg-warning-caution" aria-hidden="true"></span>
                            {{ $policy['status'] }}
                        </span>
                    </div>
                    <p class="mt-2 max-w-4xl text-sm leading-[1.4rem] text-ink-body">{{ $policy['body'] }}</p>
                </div>

                <span
                    class="shrink-0 rounded-xl bg-surface px-4 py-2 text-right shadow-sub outline outline-1 outline-line-board">
                    <span class="block gpa-micro text-ink-quiet">Supply Chain Guardrails</span>
                    <span class="block gpa-meta-lg font-bold tracking-[0.28px] text-success-deep">
                        {{ $policy['rules'] }}
                    </span>
                </span>
            </div>
        </section>

        {{-- 01 // Sumber Order --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-6 py-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand gpa-micro-bold text-accent">01</span>
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">{{ $source['label'] }}</h2>
                        <p class="gpa-note text-ink-quiet">{{ $source['hint'] }}</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-success-ink">
                    Wajib Terisi
                </span>
            </div>

            <div class="grid gap-5 px-6 py-5">
                <fieldset>
                    <legend class="gpa-label">Kanal Penerimaan Order</legend>
                    <div class="mt-2 grid gap-2 md:grid-cols-3">
                        @foreach ($source['channels'] as $channel)
                            <label
                                class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-line-faint bg-surface px-3 py-3 transition-colors hover:border-brand">
                                <input type="radio" name="order-channel" value="{{ $channel['key'] }}"
                                    x-model="channel" class="gpa-check gpa-check--radio mt-0.5">
                                <span class="min-w-0">
                                    <span class="block gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                        {{ $channel['label'] }}
                                    </span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet">{{ $channel['note'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="gpa-label" for="manual-reference">{{ $source['reference_label'] }}</label>
                        <input id="manual-reference" type="text" x-model="source.reference" class="gpa-control mt-1.5"
                            spellcheck="false">
                        <span class="gpa-hint mt-1">{{ $source['reference_hint'] }}</span>
                    </div>

                    <div>
                        <label class="gpa-label" for="manual-received">{{ $source['received_label'] }}</label>
                        <input id="manual-received" type="text" x-model="source.received" class="gpa-control mt-1.5"
                            spellcheck="false">
                        <span class="gpa-hint mt-1"> dicatat saat order masuk.</span>
                    </div>

                    <div>
                        <span class="gpa-label">{{ $source['validator_label'] }}</span>
                        <p
                            class="mt-1.5 flex items-center gap-2 rounded-lg bg-surface-shell px-3 py-2 gpa-meta-lg font-bold tracking-[0.07em] text-brand">
                            <x-gpa.icon name="badge-check" class="h-4 w-4 shrink-0 text-success-deep" />
                            {{ $source['validator'] }}
                        </p>
                        <span class="gpa-hint mt-1">{{ $source['warehouse_label'] }}: {{ $source['warehouse'] }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- 02 // Data Klien --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-6 py-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand gpa-micro-bold text-accent">02</span>
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">{{ $client['label'] }}</h2>
                        <p class="gpa-note text-ink-quiet">{{ $client['hint'] }}</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full bg-success-soft px-2.5 py-1 gpa-micro-bold text-success-ink">
                    {{ $client['code'] }}
                </span>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div class="inline-flex flex-wrap gap-1.5 rounded-lg bg-surface-shell p-1" role="group"
                    aria-label="Mode registrasi klien">
                    @foreach ($client['modes'] as $mode)
                        <button type="button" @click="clientMode = @js($mode['key'])"
                            :class="clientMode === @js($mode['key'])
                                ? 'bg-brand text-accent shadow-sub'
                                : 'text-ink-body hover:bg-surface'"
                            class="rounded px-3 py-1.5 gpa-meta-lg font-semibold tracking-[0.07em] transition-colors">
                            {{ $mode['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="gpa-label" for="manual-client-name">Nama Klien / Perusahaan</label>
                        <input id="manual-client-name" type="text" x-model="client.name" class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1">Segmen: {{ $client['segment'] }}</span>
                    </div>

                    <div>
                        <label class="gpa-label" for="manual-client-contact">{{ $client['contact_label'] }}</label>
                        <input id="manual-client-contact" type="text" x-model="client.contact" class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1">Telepon: <span x-text="client.phone"></span></span>
                    </div>

                    <div class="md:col-span-2">
                        <label class="gpa-label" for="manual-client-address">{{ $client['address_label'] }}</label>
                        <input id="manual-client-address" type="text" x-model="client.address" class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1">Batas armada: {{ $client['dock'] }}</span>
                    </div>
                </div>

                {{-- Rule 02 -- Batas kredit B2B --}}
                <div class="rounded-xl border border-line-faint bg-surface-shell p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="wallet" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span class="gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                {{ $client['credit']['label'] }}
                            </span>
                        </div>

                        <span class="inline-flex items-center gap-1 rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-ink">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                            {{ $client['credit']['status'] }}
                        </span>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <p class="gpa-micro text-ink-quiet">{{ $client['credit']['used_label'] }}</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-ink"
                                x-text="compactRupiah(creditUsed)"></p>
                        </div>
                        <div>
                            <p class="gpa-micro text-ink-quiet">{{ $client['credit']['remaining_label'] }}</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-success-deep"
                                x-text="rupiah(creditRemaining)"></p>
                        </div>
                        <div>
                            <p class="gpa-micro text-ink-quiet">{{ $client['credit']['limit_label'] }}</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-ink"
                                x-text="rupiah(creditLimit)"></p>
                        </div>
                    </div>

                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-surface-track" role="img"
                        :aria-label="'Realisasi pemakaian kredit ' + creditPercent + ' persen'">
                        <div class="h-full rounded-full transition-all duration-300"
                            :class="creditOk ? 'bg-accent-deep' : 'bg-danger'"
                            :style="'width: ' + Math.min(creditPercent, 100) + '%'"></div>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                        <span class="gpa-note text-ink-quiet">
                            <span x-text="'Realisasi Pemakaian: ' + creditPercent + '%'"></span>
                            • <span x-text="'Sisa Setelah Order: ' + rupiah(creditAfter)"></span>
                        </span>
                        <span class="gpa-note"
                            :class="creditOk ? 'text-success-deep' : 'text-danger-ink'"
                            x-text="creditOk ? 'Rule 02 Terpenuhi' : 'Melebihi Plafon'"></span>
                    </div>
                </div>
            </div>
        </section>

        {{-- 03 // Komoditas --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-6 py-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand gpa-micro-bold text-accent">03</span>
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">Komoditas</h2>
                        <p class="gpa-note text-ink-quiet">Basis stok sistem dipakai untuk mencegah overselling (Rule 03).</p>
                    </div>
                </div>

                <span x-cloak x-show="! ready"
                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-danger-soft px-2.5 py-1 gpa-micro-bold text-danger-ink">
                    <x-gpa.icon name="alert-triangle" class="h-3 w-3 shrink-0" />
                    <span x-text="blockers().length + ' validasi gagal'"></span>
                </span>
            </div>

            <div class="gpa-scroll-x px-6 py-5">
                <table class="w-full min-w-[64rem] border-collapse text-left">
                    <caption class="sr-only">Daftar komoditas order manual beserta stok sistem dan subtotal.</caption>
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="pb-2 pr-3 gpa-micro text-ink-quiet">Komoditas</th>
                            <th scope="col" class="pb-2 pr-3 gpa-micro text-ink-quiet">SKU &amp; Grade</th>
                            <th scope="col" class="pb-2 pr-3 gpa-micro text-ink-quiet">Min. Order</th>
                            <th scope="col" class="pb-2 pr-3 gpa-micro text-ink-quiet">Stok Sistem</th>
                            <th scope="col" class="pb-2 pr-3 text-right gpa-micro text-ink-quiet">Qty</th>
                            <th scope="col" class="pb-2 pr-3 text-right gpa-micro text-ink-quiet">Harga</th>
                            <th scope="col" class="pb-2 pr-3 text-right gpa-micro text-ink-quiet">Subtotal</th>
                            <th scope="col" class="pb-2 gpa-micro text-ink-quiet">Status</th>
                            <th scope="col" class="pb-2 w-10"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-line-faint">
                        <template x-for="(row, index) in rows" :key="row.key">
                            <tr class="align-top">
                                <td class="py-3 pr-3">
                                    <span class="block text-sm font-bold leading-5 text-ink" x-text="row.name"></span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet" x-text="row.unit"></span>
                                </td>

                                <td class="py-3 pr-3">
                                    <span class="block gpa-mono-xs text-brand" x-text="row.sku"></span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet" x-text="row.grade"></span>
                                </td>

                                <td class="py-3 pr-3">
                                    <span class="block gpa-mono-xs text-ink" x-text="number(row.min_order) + ' kg'"></span>
                                    <span class="mt-0.5 block gpa-note" :class="minClass(row)"
                                        x-text="minLabel(row)"></span>
                                </td>

                                <td class="py-3 pr-3">
                                    <span class="block gpa-mono-xs text-ink" x-text="number(row.stock) + ' kg'"></span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet"
                                        x-text="'Binaan ' + number(row.stock_bud) + ' / Buffer ' + number(row.stock_buffer)"></span>
                                </td>

                                <td class="py-3 pr-3 text-right">
                                    <input type="number" min="0" step="1" x-model.number="row.quantity"
                                        class="gpa-control w-24 text-right" :aria-label="'Qty ' + row.name">
                                </td>

                                <td class="py-3 pr-3 text-right">
                                    <span class="block gpa-mono-xs text-ink" x-text="rupiah(row.price)"></span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet">per kg</span>
                                </td>

                                <td class="py-3 pr-3 text-right">
                                    <span class="block gpa-mono-xs font-bold text-ink"
                                        x-text="rupiah(rowSubtotal(row))"></span>
                                </td>

                                <td class="py-3 pr-3">
                                    <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 gpa-micro-bold"
                                        :class="stockClass(row)">
<x-gpa.icon x-show="stockOk(row)" name="check-circle" class="h-3 w-3 shrink-0" />
                                        <x-gpa.icon x-cloak x-show="! stockOk(row)" name="alert-triangle"
                                            class="h-3 w-3 shrink-0" />
                                        <span x-text="stockLabel(row)"></span>
                                    </span>
                                </td>

                                <td class="py-3 text-right">
                                    <button type="button" @click="removeRow(index)"
                                        class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-ink-quiet transition-colors hover:bg-danger-soft hover:text-danger-ink"
                                        :aria-label="'Hapus ' + row.name">
                                        <x-gpa.icon name="trash" class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <p x-cloak x-show="rows.length === 0"
                    class="mt-4 rounded-xl border border-dashed border-line-board bg-surface-shell px-3 py-8 text-center">
                    <span class="block text-xs font-semibold text-ink">Belum ada baris komoditas.</span>
                    <span class="mt-1 block gpa-note text-ink-quiet">
                        Tambahkan minimal satu komoditas sebelum menyimpan draf PO.
                    </span>
                </p>
            </div>

            <div
                class="flex flex-wrap items-center justify-between gap-3 border-t border-line-soft bg-surface-shell px-6 py-4">
                <div class="flex items-center gap-2">
                    <label class="gpa-label" for="manual-catalog">Tambah Baris</label>
                    <select id="manual-catalog" x-model="selectedCatalog" class="gpa-control w-56">
                        <option value="">Pilih Komoditas Katalog...</option>
                        @foreach ($catalog as $item)
                            <option value="{{ $item['key'] }}">{{ $item['name'] }} ({{ $item['sku'] }})</option>
                        @endforeach
                    </select>
                    <button type="button" @click="addRow(selectedCatalog)" :disabled="! selectedCatalog"
                        class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-brand px-3.5 text-accent transition-colors hover:bg-brand-hover disabled:cursor-not-allowed disabled:bg-surface-disabled disabled:text-ink-subtle">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5" />
                        <span class="gpa-meta-lg font-bold tracking-[0.07em]">Tambah</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-surface px-2.5 py-1 gpa-micro-bold text-ink-body">
                        <x-gpa.icon name="scale" class="h-3 w-3 shrink-0 text-success-deep" />
                        <span x-text="'Total ' + number(totalWeight, 1) + ' kg'"></span>
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 gpa-micro-bold"
                        :class="ready ? 'bg-success-soft text-success-ink' : 'bg-danger-soft text-danger-ink'">
                        <x-gpa.icon x-show="ready" name="check-circle" class="h-3 w-3 shrink-0" />
                        <x-gpa.icon x-cloak x-show="! ready" name="alert-triangle" class="h-3 w-3 shrink-0" />
                        <span x-text="ready ? 'Evaluasi Stok [PASS]' : 'Evaluasi Stok [FAIL]'"></span>
                    </span>
                </div>
            </div>
        </section>

        {{-- 04 // Logistik --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-6 py-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand gpa-micro-bold text-accent">04</span>
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">{{ $logistics['label'] }}</h2>
                        <p class="gpa-note text-ink-quiet">{{ $logistics['hint'] }}</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-success-ink">
                    {{ $logistics['route_note'] }}
                </span>
            </div>

            <div class="grid gap-4 px-6 py-5 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label class="gpa-label" for="manual-date">{{ $logistics['date_label'] }}</label>
                    <input id="manual-date" type="date" x-model="logistics.date" class="gpa-control mt-1.5">
                </div>

                <div>
                    <label class="gpa-label" for="manual-arrival">{{ $logistics['arrival_label'] }}</label>
                    <select id="manual-arrival" x-model="logistics.arrival" class="gpa-control mt-1.5">
                        @foreach ($logistics['arrival_options'] as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="gpa-label" for="manual-route">{{ $logistics['route_label'] }}</label>
                    <input id="manual-route" type="text" x-model="logistics.route" class="gpa-control mt-1.5"
                        spellcheck="false">
                </div>

                <div>
                    <label class="gpa-label" for="manual-fleet">{{ $logistics['fleet_label'] }}</label>
                    <input id="manual-fleet" type="text" x-model="logistics.fleet" class="gpa-control mt-1.5"
                        spellcheck="false">
                    <span class="gpa-hint mt-1">{{ $logistics['fleet_note'] }}</span>
                </div>

                <div class="md:col-span-2 xl:col-span-3">
                    <label class="gpa-label" for="manual-notes">{{ $logistics['notes_label'] }}</label>
                    <textarea id="manual-notes" rows="2" x-model="logistics.notes"
                        class="gpa-control mt-1.5 font-sans text-xs leading-5"></textarea>
                </div>

                <div>
                    <span class="gpa-label">{{ $logistics['fee_label'] }}</span>
                    <p
                        class="mt-1.5 flex items-center gap-2 rounded-lg bg-surface-shell px-3 py-2 gpa-mono-xs font-bold text-brand">
                        <span x-text="rupiah(fee)"></span>
                        <span class="rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">
                            {{ $logistics['fee_note'] }}
                        </span>
                    </p>
                </div>
            </div>
        </section>

        {{-- 05 // Pembayaran --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line-soft px-6 py-5">
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand gpa-micro-bold text-accent">05</span>
                    <div class="min-w-0">
                        <h2 class="gpa-section-title text-ink">{{ $payment['label'] }}</h2>
                        <p class="gpa-note text-ink-quiet">{{ $payment['hint'] }}</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full bg-warning-soft px-2.5 py-1 gpa-micro-bold text-warning-caution">
                    Rule 11 &amp; 15
                </span>
            </div>

            <div class="grid gap-5 px-6 py-5 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <fieldset>
                    <legend class="gpa-label">Metode Pembayaran</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach ($payment['methods'] as $method)
                            <label
                                class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-line-faint bg-surface px-3 py-3 transition-colors hover:border-brand">
                                <input type="radio" name="payment-method" value="{{ $method['key'] }}"
                                    x-model="paymentMethod" class="gpa-check gpa-check--radio mt-0.5">
                                <span class="min-w-0">
                                    <span class="block gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                        {{ $method['label'] }}
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <div class="rounded-xl border border-line-faint bg-surface-shell p-4">
                    <p class="gpa-label">{{ $payment['evidence_label'] }}</p>

                    <div x-show="evidence.file" class="mt-2 flex items-start gap-2">
                        <x-gpa.icon name="file-text" class="mt-0.5 h-4 w-4 shrink-0 text-success-deep" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate gpa-mono-xs font-bold text-ink" x-text="evidence.file"></p>
                            <p class="gpa-note text-ink-quiet">
                                <span x-text="evidence.size"></span> • SHA-256
                                <span x-text="evidence.hash"></span>
                            </p>
                            <p class="mt-1 inline-flex items-center gap-1 rounded bg-success-soft px-1.5 py-0.5 gpa-micro-bold text-success-ink"
                                x-text="evidence.hash_note"></p>
                        </div>
                    </div>

                    <p x-cloak x-show="! evidence.file" class="mt-2 gpa-note text-danger-ink">
                        Belum ada bukti pembayaran yang dilampirkan.
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <button type="button" @click="viewEvidence()"
                            class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-brand px-3 text-accent transition-colors hover:bg-brand-hover">
                            <x-gpa.icon name="eye" class="h-3.5 w-3.5" />
                            <span class="gpa-micro-bold">Lihat Bukti</span>
                        </button>
                        <button type="button" @click="removeEvidence()"
                            class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-surface px-3 text-ink-body outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                            <x-gpa.icon name="trash" class="h-3.5 w-3.5" />
                            <span class="gpa-micro-bold">Hapus</span>
                        </button>
                    </div>

                    <span class="gpa-hint mt-2">{{ $payment['upload_hint'] }}</span>
                </div>
            </div>
        </section>

        {{-- Ringkasan & Aksi --}}
        <section class="gpa-panel overflow-hidden">
            <div class="grid gap-5 px-6 py-5 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <p class="gpa-eyebrow">Ringkasan Order // Belum Termasuk PPN</p>

                    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl bg-surface-shell px-3 py-2.5">
                            <p class="gpa-micro text-ink-quiet">Total Berat</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-ink" x-text="kg(totalWeight)"></p>
                        </div>
                        <div class="rounded-xl bg-surface-shell px-3 py-2.5">
                            <p class="gpa-micro text-ink-quiet">Subtotal Komoditas</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-ink" x-text="rupiah(subtotal)"></p>
                        </div>
                        <div class="rounded-xl bg-surface-shell px-3 py-2.5">
                            <p class="gpa-micro text-ink-quiet">Ongkos Kirim</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-ink" x-text="rupiah(fee)"></p>
                        </div>
                        <div class="rounded-xl bg-brand px-3 py-2.5">
                            <p class="gpa-micro text-accent">Total Estimasi</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-accent" x-text="rupiah(total)"></p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                    <button type="button" @click="cancel()"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-4 text-ink-body outline outline-1 outline-line-board transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                        <span class="gpa-meta-lg font-bold tracking-[0.07em]">Batal / Kembali</span>
                    </button>

                    <button type="button" @click="saveDraft()"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-surface px-4 text-brand outline outline-1 outline-brand-line transition-colors hover:bg-surface-muted">
                        <x-gpa.icon name="save" class="h-3.5 w-3.5" />
                        <span class="gpa-meta-lg font-bold tracking-[0.07em]">Simpan Draf PO</span>
                    </button>

                    <button type="button" @click="verify()"
                        class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-brand px-4 text-accent shadow-sub transition-colors hover:bg-brand-hover">
                        Verifikasi &amp; Teruskan
                        <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5" />
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection