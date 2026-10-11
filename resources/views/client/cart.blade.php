@extends('layouts.dashboard')

@section('title', 'Keranjang & Draft Purchase Order')

@php
    $stepBadge = fn (string $step) => 'flex h-7 w-7 shrink-0 items-center justify-center rounded bg-ink text-white gpa-meta-lg font-bold';
@endphp

@section('content')
    @php
        $cartDefaults = [
            'date' => $delivery['date']['value'],
            'window' => $delivery['window']['value'],
            'driver_note' => $driverNote['value'],
            'dock' => collect($docks)->firstWhere('selected', true)['key'] ?? 'ciracas',
            'payment' => collect($paymentMethods)->firstWhere('selected', true)['key'] ?? 'top30',
            'status' => $summary['status'],
            'submitted_status' => $summary['submitted_status'],
        ];
    @endphp

    <div x-data="clientCart(@js($commodities), @js($demoLines), @js($cartDefaults))"
        class="flex flex-col gap-4">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Breadcrumb + status telemetry --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <nav class="flex flex-wrap items-center gap-2 gpa-meta text-ink-quiet" aria-label="Breadcrumb">
                @foreach ($draft['breadcrumb'] as $crumb)
                    <span>{{ $crumb }}</span>
                    <span aria-hidden="true">/</span>
                @endforeach
                <span class="gpa-meta-lg font-bold text-ink">{{ $draft['title'] }}</span>
            </nav>

            <div class="flex items-center gap-2">
                <span
                    class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                    {{ $draft['telemetry'] }}
                </span>
                <span class="gpa-micro text-ink-quiet">{{ $draft['sync'] }}</span>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Judul halaman + batas submit --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex min-w-0 flex-col gap-1">
                <h1 class="font-sans text-2xl font-bold leading-8 text-ink lg:text-[2rem] lg:leading-10">
                    {{ $draft['heading'] }}
                </h1>
                <p class="max-w-3xl text-sm leading-5 text-ink-body">{{ $draft['subheading'] }}</p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <p class="gpa-micro text-ink-quiet">{{ $draft['cutoff_label'] }}</p>
                <span class="rounded bg-ink px-2.5 py-1 gpa-meta-lg font-bold text-white">
                    {{ $draft['cutoff_value'] }}
                </span>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Dua kolom: item & logistik  |  ringkasan estimasi PO --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_22rem] xl:gap-6">
            <div class="flex min-w-0 flex-col gap-6">
                {{-- ---------------------------------------------------------------- --}}
                {{-- 01 Daftar item keranjang --}}
                {{-- ---------------------------------------------------------------- --}}
                <section class="gpa-card overflow-hidden">
                    <header class="flex flex-col gap-3 border-b border-line-hair bg-surface-muted/70 p-5 lg:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="{{ $stepBadge($itemsSection['step']) }}">{{ $itemsSection['step'] }}</span>
                            <div class="min-w-0">
                                <h2 class="font-sans text-[17px] font-semibold leading-6 text-ink">
                                    {{ $itemsSection['title'] }}
                                </h2>
                                <p class="gpa-body text-ink-body">
                                    <span class="font-medium text-ink" x-text="itemCount()"></span>
                                    {{ $itemsSection['subtitle'] }}
                                </p>
                            </div>
                        </div>

                    </header>

                    <div class="flex flex-col">
                        <template x-for="line in lines" :key="`cart-${line.key}`">
                            <div class="flex flex-col gap-6 border-b border-line-hair p-5 lg:flex-row lg:justify-between lg:p-6">
                                <div class="flex min-w-0 items-start gap-3.5">
                                    <span
                                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl border border-line-hair bg-surface-pill">
                                        <x-gpa.icon name="leaf" class="h-6 w-6 text-ink" />
                                    </span>

                                    <div class="flex min-w-0 flex-col gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded bg-ink px-1.5 py-0.5 gpa-micro-bold text-white">
                                                SKU: <span x-text="line.sku"></span>
                                            </span>
                                            <span
                                                class="rounded bg-accent px-1.5 py-0.5 gpa-micro-bold text-success-ink">
                                                <span x-text="line.grade"></span>
                                            </span>
                                        </div>

                                        <h3 class="font-sans text-base font-bold leading-6 text-ink" x-text="line.name"></h3>
                                        <p class="max-w-md gpa-body leading-4 text-ink-body" x-text="line.packaging"></p>

                                        <div class="flex flex-wrap items-center gap-3 pt-1">
                                            <p class="gpa-meta-lg font-semibold text-ink">
                                                <span x-text="money(line.price)"></span>/kg
                                            </p>
                                            <span class="font-inter text-ink-quiet" aria-hidden="true">&bull;</span>
                                            <p class="flex items-center gap-1.5"
                                                :class="bufferTone(line) === 'caution' ? 'text-warning-caution' : 'text-success'">
                                                <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
                                                <span class="gpa-micro-bold">
                                                    Stok Buffer: <span x-text="line.stock"></span> kg<br />
                                                    [<span x-text="bufferState(line)"></span>]
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex shrink-0 flex-col items-stretch gap-2 lg:items-end">
                                    <div class="flex flex-col items-stretch gap-1 lg:items-end">
                                        <p class="gpa-micro text-right text-ink-quiet">Estimasi Kuantitas</p>

                                        <div class="flex items-center justify-end gap-2 pt-1">
                                            <div
                                                class="flex h-9 items-center overflow-hidden rounded-l-lg border border-line-hair bg-surface focus-within:border-brand">
                                                <label class="sr-only" :for="`qty-${line.key}`">Volume kilogram</label>
                                                <input :id="`qty-${line.key}`" type="number"
                                                    :value="line.qty"
                                                    @input="setQty(line.key, $event.target.value)"
                                                    class="w-20 border-0 bg-transparent px-2 text-right text-base font-bold text-ink outline-none gpa-mono-xs">
                                                <span class="px-1 opacity-0" aria-hidden="true">x</span>
                                            </div>
                                            <div
                                                class="flex h-9 items-center rounded-r-lg border-y border-r border-line-hair bg-surface-muted px-2.5 gpa-meta-lg font-bold text-ink-body">
                                                KG
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-3">
                                        <button type="button" @click="stepBy(line.key, -1)"
                                            aria-label="Kurangi volume"
                                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-line-hair bg-surface text-ink transition-colors hover:border-brand">
                                            <x-gpa.icon name="x" class="h-3 w-3" />
                                        </button>
                                        <button type="button" @click="stepBy(line.key, 1)"
                                            aria-label="Tambah volume"
                                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-line-hair bg-surface text-ink transition-colors hover:border-brand">
                                            <x-gpa.icon name="plus" class="h-3 w-3" />
                                        </button>
                                    </div>

                                    <div class="flex flex-col items-end gap-0.5 pt-1">
                                        <p class="gpa-micro text-right text-ink-quiet">Subtotal Estimasi</p>
                                        <p class="gpa-meta-lg font-bold text-ink" x-text="money(line.qty * line.price)"></p>
                                        <p class="gpa-micro text-right text-ink-quiet" x-text="packText(line)"></p>
                                    </div>

                                    <button type="button" @click="removeLine(line.key)"
                                        class="inline-flex w-fit items-center gap-1.5 rounded-lg px-2 py-1.5 gpa-micro-bold text-ink-quiet transition-colors hover:bg-danger-soft hover:text-danger lg:self-end">
                                        <x-gpa.icon name="trash" class="h-3 w-3" />
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="empty()" x-cloak class="flex flex-col items-center gap-2 p-10 text-center">
                            <x-gpa.icon name="cart" class="h-6 w-6 text-ink-quiet" />
                            <p class="gpa-section-title text-ink">Keranjang masih kosong</p>
                            <p class="max-w-sm gpa-body text-ink-body">
                                Tambahkan komoditas dari Katalog untuk membuat draft purchase order baru.
                            </p>
                            <button type="button" @click="addFromCatalog()"
                                class="mt-1 inline-flex items-center gap-2 rounded-lg border border-line-hair bg-surface-shell px-3.5 py-2 transition-colors hover:border-brand">
                                <x-gpa.icon name="grid" class="h-3.5 w-3.5 text-ink" />
                                <span class="gpa-meta-lg text-ink">Buka Katalog</span>
                            </button>
                        </div>
                    </div>

                    <footer class="flex flex-col gap-2 border-t border-line-hair bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button" @click="addFromCatalog()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-success px-3.5 py-2 transition-colors hover:bg-success-soft">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-ink" />
                            <span class="gpa-meta-lg font-bold uppercase text-ink">Tambah Komoditas Lain dari Katalog</span>
                        </button>

                        <button type="button" @click="clearCart()"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 transition-colors hover:bg-danger-soft">
                            <x-gpa.icon name="trash" class="h-3.5 w-3.5 text-ink-quiet" />
                            <span class="gpa-meta-lg uppercase text-ink-quiet">Kosongkan Keranjang</span>
                        </button>
                    </footer>
                </section>

                {{-- ---------------------------------------------------------------- --}}
                {{-- 02 Parameter pengiriman & logistik --}}
                {{-- ---------------------------------------------------------------- --}}
                <section class="gpa-card flex flex-col gap-6 p-5 lg:p-6">
                    <div class="flex items-center gap-2.5 border-b border-line-hair pb-4">
                        <span class="{{ $stepBadge($delivery['step']) }}">{{ $delivery['step'] }}</span>
                        <div class="min-w-0">
                            <h2 class="font-sans text-[17px] font-semibold leading-6 text-ink">
                                {{ $delivery['title'] }}
                            </h2>
                            <p class="gpa-body text-ink-body">{{ $delivery['subtitle'] }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <label for="tanggal-pengiriman" class="gpa-label">{{ $delivery['date']['label'] }}</label>
                            <div class="relative">
                                <input id="tanggal-pengiriman" type="text" x-model="deliveryDate"
                                    class="gpa-control h-10 pr-9 font-bold">
                                <x-gpa.icon name="calendar" class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                            </div>
                            <p class="gpa-hint">{{ $delivery['date']['hint'] }}</p>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="jendela-dock" class="gpa-label">{{ $delivery['window']['label'] }}</label>
                            <div class="relative">
                                <input id="jendela-dock" type="text" x-model="dockWindow" class="gpa-control h-10 pr-9">
                                <x-gpa.icon name="clock" class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                            </div>
                            <p class="gpa-hint font-semibold text-success-ink">{{ $delivery['window']['hint'] }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <p class="gpa-label">{{ $delivery['dock_label'] }}</p>

                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($docks as $dock)
                                <button type="button" @click="dock = @js($dock['key'])" role="radio"
                                    :aria-checked="dock === @js($dock['key'])"
                                    class="flex flex-col items-start gap-3 rounded-xl p-3.5 text-left transition-colors"
                                    :class="dock === @js($dock['key'])
                                        ? 'bg-surface-muted shadow-sub outline outline-2 outline-ink'
                                        : 'bg-surface outline outline-1 outline-line-hair hover:outline-brand'">
                                    <span class="flex items-start gap-3">
                                        <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                            :class="dock === @js($dock['key']) ? 'bg-ink' : 'bg-surface-pill'">
                                            <span class="h-1.5 w-1.5 rounded-full bg-white"
                                                :class="dock === @js($dock['key']) ? '' : 'opacity-0'"></span>
                                        </span>

                                        <span class="flex min-w-0 flex-col gap-1.5">
                                            <span class="w-fit rounded bg-ink px-1.5 gpa-micro-bold text-white"
                                                x-show="dock === @js($dock['key'])">{{ $dock['chip'] }}</span>
                                            <span class="w-fit rounded bg-surface-pill px-1.5 gpa-micro-bold text-ink-quiet"
                                                x-show="dock !== @js($dock['key'])" x-cloak>{{ $dock['chip'] }}</span>

                                            <span class="font-inter text-sm font-bold leading-5 text-ink">{{ $dock['name'] }}</span>
                                            <span class="font-inter text-xs leading-4 text-ink-body">{{ $dock['address'] }}</span>
                                            <span class="gpa-micro text-ink-quiet">{{ $dock['access'] }}</span>
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative flex flex-col gap-3 rounded-xl border border-line-hair bg-surface-shell p-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand">
                                <x-gpa.icon name="user" class="h-4 w-4 text-accent" />
                            </span>
                            <div class="flex min-w-0 flex-col">
                                <p class="gpa-micro text-ink-quiet">{{ $receiving['label'] }}</p>
                                <p class="mt-0.5 font-inter text-sm font-bold leading-5 text-ink">
                                    <span x-text="@js($receiving['roster'])[pic]?.name"></span>
                                    (<span x-text="@js($receiving['roster'])[pic]?.role"></span>)
                                </p>
                                <p class="gpa-meta text-ink-quiet">
                                    Kontak Operasional:
                                    <span class="text-ink-body" x-text="@js($receiving['roster'])[pic]?.phone"></span>
                                    • Handover Digital Sign Off Ready
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="togglePic()"
                            class="shrink-0 self-end rounded-lg border border-line-hair bg-surface px-3 py-2 gpa-meta-lg font-bold text-ink transition-colors hover:border-brand sm:self-auto">
                            {{ $receiving['action'] }}
                        </button>

                        <div x-show="picOpen" x-cloak @click.outside="picOpen = false"
                            class="absolute right-3 top-full z-20 mt-1 flex w-[16rem] flex-col gap-1 rounded-xl border border-line-hair bg-surface p-1.5 shadow-pop">
                            @foreach ($receiving['roster'] as $index => $person)
                                <button type="button" @click="changePic(@js($index))"
                                    class="flex flex-col items-start rounded-lg px-2.5 py-2 text-left transition-colors hover:bg-surface-shell">
                                    <span class="font-inter text-xs font-bold text-ink">{{ $person['name'] }}</span>
                                    <span class="gpa-micro text-ink-quiet">{{ $person['role'] }}</span>
                                    <span class="gpa-meta text-ink-body">{{ $person['phone'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="catatan-supir" class="gpa-label">{{ $driverNote['label'] }}</label>
                        <textarea id="catatan-supir" rows="3" x-model="driverNote"
                            class="gpa-control min-h-[5.5rem] font-sans text-sm leading-[1.6]"></textarea>
                        <p class="gpa-hint">{{ $driverNote['hint'] }}</p>
                    </div>
                </section>
            </div>

            {{-- ------------------------------------------------------------------ --}}
            {{-- Ringkasan estimasi PO --}}
            {{-- ------------------------------------------------------------------ --}}
            <div class="flex min-w-0 flex-col gap-3 xl:sticky xl:top-24">
                <section class="gpa-card flex flex-col gap-4 p-5 lg:p-6">
                    <header class="flex flex-col gap-2 border-b border-line-hair pb-4">
                        <div>
                            <h2 class="font-sans text-[17px] font-bold leading-5 text-ink">{{ $summary['title'] }}</h2>
                            <p class="gpa-micro mt-1.5 text-ink-quiet">{{ $draft['po_number'] }}</p>
                        </div>
                        <span
                            class="w-fit rounded bg-surface-muted px-2 py-0.5 ring-1 ring-inset ring-success gpa-micro-bold text-ink">
                            <span x-text="itemCount()"></span> Komoditas
                        </span>
                    </header>

                    <dl class="flex flex-col gap-3 border-b border-line-hair pb-4">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="flex items-center gap-1.5 gpa-body text-ink-body">
                                <x-gpa.icon name="scale" class="h-3.5 w-3.5 text-ink-quiet" />
                                Total Estimasi Tonase
                            </dt>
                            <dd class="shrink-0 text-right gpa-meta-lg font-bold text-ink">
                                <span x-text="totalWeight()"></span> kg<br />
                                (<span x-text="totalTons()"></span> Ton)
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-body text-ink-body">
                                Subtotal Komoditas<br />(<span x-text="itemCount()"></span> SKU)
                            </dt>
                            <dd class="shrink-0 text-right gpa-meta-lg font-bold text-ink" x-text="money(subtotal())"></dd>
                        </div>

                        <div class="flex items-start justify-between gap-3">
                            <dt class="flex items-center gap-1.5 gpa-body text-ink-body">
                                {{ $summary['shipping_label'] }}
                                <x-gpa.icon name="info" class="h-3 w-3 text-success" />
                            </dt>
                            <dd class="shrink-0 text-right">
                                <p class="gpa-meta-lg font-bold text-success" x-text="money(shipping())"></p>
                                <p class="gpa-micro text-ink-quiet">{{ $summary['shipping_note'] }}</p>
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-body text-ink-quiet">{{ $summary['tax_label'] }}</dt>
                            <dd>
                                <span
                                    class="inline-block rounded bg-surface-pill px-1.5 py-0.5 gpa-micro-bold text-ink-quiet">
                                    {{ $summary['tax_chip'] }}
                                </span>
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-1.5 border-y border-line-hair bg-surface-muted/50 px-4 py-4">
                        <p class="gpa-micro-bold text-ink-quiet">{{ $summary['total_label'] }}</p>
                        <p class="font-sans text-xl font-extrabold leading-7 text-ink" x-text="money(grandTotal())"></p>
                        <p class="gpa-body text-ink-body">{{ $summary['total_note'] }}</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="gpa-micro-bold uppercase text-ink">{{ $summary['payment_label'] }}</p>
                            <span class="rounded bg-ink px-1.5 gpa-micro-bold text-white">{{ $summary['payment_chip'] }}</span>
                        </div>

                        <div class="flex flex-col gap-2" role="radiogroup" aria-label="{{ $summary['payment_label'] }}">
                            @foreach ($paymentMethods as $method)
                                <button type="button" role="radio" @click="payment = @js($method['key'])"
                                    :aria-checked="payment === @js($method['key'])"
                                    class="flex items-start gap-2.5 rounded-lg p-2.5 text-left transition-colors"
                                    :class="payment === @js($method['key'])
                                        ? 'bg-surface-muted outline outline-2 outline-ink'
                                        : 'bg-surface outline outline-1 outline-line-hair hover:outline-brand'">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                        :class="payment === @js($method['key']) ? 'bg-ink' : 'bg-surface-pill'">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white"
                                            :class="payment === @js($method['key']) ? '' : 'opacity-0'"></span>
                                    </span>

                                    <span class="flex min-w-0 flex-col gap-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="gpa-meta-lg font-bold text-ink">{{ $method['name'] }}</span>
                                            <span x-show="payment === @js($method['key'])"
                                                x-cloak class="rounded bg-accent px-1.5 gpa-micro-bold text-ink">
                                                {{ $method['chip'] ?? 'Terpilih' }}
                                            </span>
                                        </span>
                                        <span
                                            class="gpa-hint leading-3 {{ $method['note_tone'] === 'success' ? 'font-semibold text-success-ink' : 'text-ink-quiet' }}">
                                            {{ $method['note'] }}
                                        </span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 pt-2">
                        <button type="button" @click="submitPo()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-ink px-4 py-3 shadow-pop transition-colors hover:bg-brand-deep">
                            <x-gpa.icon name="send" class="h-4 w-4 text-white" />
                            <span class="font-sans text-sm font-bold uppercase tracking-tight text-white">
                                {{ $summary['submit'] }}
                            </span>
                        </button>

                        <button type="button" @click="saveDraft()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-surface-pill px-4 py-2.5 text-ink-body transition-colors hover:bg-surface-disabled hover:text-ink">
                            <x-gpa.icon name="save" class="h-3 w-3.5 text-white" />
                            <span class="gpa-meta-lg font-bold uppercase text-white">{{ $summary['save'] }}</span>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-line-hair pt-3">
                        <p class="gpa-micro text-ink-quiet">
                            Status PO:<br />
                            <span class="text-ink" x-text="statusLabel">{{ $summary['status'] }}</span>
                        </p>
                        <p class="gpa-micro text-right text-ink">
                            SLA Respon Admin: <span class="font-bold">{{ $summary['sla'] }}</span>
                        </p>
                    </div>
                </section>

                <section
                    class="flex items-start gap-2.5 rounded-xl border border-line-hair bg-surface-muted/70 p-3">
                    <x-gpa.icon name="badge-check" class="mt-0.5 h-4 w-4 shrink-0 text-success" />
                    <div class="flex min-w-0 flex-col">
                        <p class="gpa-meta-lg font-bold text-ink">{{ $guarantee['title'] }}</p>
                        <p class="mt-1 text-xs leading-4 text-ink-quiet">{{ $guarantee['body'] }}</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
