@extends('layouts.klien')

@section('title', 'Keranjang & Draft Purchase Order')

@php
    $user = $user ?? auth()->user();
    $isB2b = ($user->client_type === 'B2B_KONTRAK');

    $commodities = ($products ?? collect())->map(function ($product) use ($activeContracts, $stockMap) {
        $hasContract = $activeContracts->has($product->id);
        $contract = $activeContracts->get($product->id);
        $effectivePrice = $hasContract ? (float) $contract->fixed_price_per_kg : (float) $product->base_price;
        $stock = $stockMap[$product->id] ?? 0;
        $moq = (float) ($product->minimum_order ?? 10);

        return [
            'key'        => (string) $product->id,
            'id'         => $product->id,
            'sku'        => $product->sku ?? ('SKU-' . $product->id),
            'name'       => $product->name,
            'grade'      => $product->grade ?: 'Grade A',
            'packaging'  => "Krat Plastik Higienis 10 {$product->unit}",
            'origin'     => 'Lembang',
            'cold_chain' => '2-6°C',
            'price'      => $effectivePrice,
            'stock'      => $stock,
            'moq'        => $moq,
            'step'       => 1,
            'crate_kg'   => 10,
            'pack_label' => 'Krat',
        ];
    })->values()->all();

    $defaultDeliveryDate = now()->addDay()->format('Y-m-d');
    $defaultAddress = $user->address ?: 'Gudang Utama Klien (Dock Penerimaan)';

    $docks = [
        [
            'key'      => 'dock-utama',
            'name'     => $user->company_name ?: 'Gudang Utama Klien',
            'address'  => $defaultAddress,
            'chip'     => 'Dock Utama',
            'access'   => 'Akses Truk Engkel Box Chiller & CDD Long',
        ],
        [
            'key'      => 'dock-sentral',
            'name'     => 'Central Kitchen / Processing Plant',
            'address'  => ($user->address ?: 'Bandung Area') . ' (Hub Cabang)',
            'chip'     => 'Alternatif',
            'access'   => 'Ramp Hidrolik Cold Storage Standar HACCP',
        ],
    ];

    $demoLines = $demoLines ?? [];

    $cartDefaults = [
        'date'             => $defaultDeliveryDate,
        'window'           => '04:00 - 06:00 WIB (Subuh)',
        'driver_note'      => 'Harap konfirmasi ke PIC dock penerimaan 30 menit sebelum armada tiba.',
        'dock'             => 'dock-utama',
        'docks'            => $docks,
        'default_address'  => $defaultAddress,
        'payment'          => $isB2b ? 'TEMPO_TOP' : 'TRANSFER_BANK',
        'status'           => 'Draf Siap Diajukan',
        'submitted_status' => 'Menunggu Verifikasi Admin',
    ];
@endphp

    <div x-data="clientCart(@js($commodities), @js($demoLines), @js($cartDefaults))"
        class="flex flex-col gap-4">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Breadcrumb + status telemetry --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span
                    class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                    Live Connected
                </span>
                <span class="gpa-micro text-ink-quiet">Sinkronisasi Otomatis</span>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Judul halaman + batas submit --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="flex min-w-0 flex-col gap-1">
                <h1 class="font-sans text-2xl font-bold leading-8 text-ink lg:text-[2rem] lg:leading-10">
                    Keranjang &amp; Pengajuan Purchase Order
                </h1>
                <p class="max-w-3xl text-sm leading-5 text-ink-body">
                    Periksa alokasi komoditas panen, pilih titik bongkar muat dock, dan ajukan pesanan resmi.
                </p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <p class="gpa-micro text-ink-quiet">Batas Waktu Order Subuh:</p>
                <span class="rounded bg-ink px-2.5 py-1 gpa-meta-lg font-bold text-white">
                    Pukul 16:00 WIB
                </span>
            </div>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Peringatan Rule 04 --}}
        {{-- ---------------------------------------------------------------- --}}
        <section
            class="flex flex-col gap-3 rounded-xl border-l-4 border-warning-caution bg-warning-cream py-4 pl-4 pr-4 shadow-sub sm:flex-row sm:items-start sm:gap-3 sm:pl-6 sm:pr-6">
            <x-gpa.icon name="alert-triangle" class="mt-0.5 h-4 w-5 shrink-0 text-warning-caution" />

            <div class="flex min-w-0 flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded bg-warning-caution px-1.5 py-0.5 gpa-micro-bold text-white">
                        Rule 04 Operasional
                    </span>
                    <h2 class="font-sans text-[15px] font-bold leading-5 text-warning-caution">
                        Pemberitahuan Cut-Off Panen &amp; Distribusi Subuh
                    </h2>
                </div>
                <p class="gpa-body leading-5 text-ink">
                    Order yang masuk sebelum pukul 16:00 WIB dipanen sore hari dan tiba di dock gudang Anda pada jendela pukul 04:00 - 06:00 WIB subuh berikutnya dalam kondisi segar pre-cooled.
                </p>
            </div>
        </section>

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
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-ink text-white gpa-meta-lg font-bold">01</span>
                            <div class="min-w-0">
                                <h2 class="font-sans text-[17px] font-semibold leading-6 text-ink">
                                    Daftar Komoditas &amp; Volume Pesanan
                                </h2>
                                <p class="gpa-body text-ink-body">
                                    <span class="font-medium text-ink" x-text="itemCount()"></span>
                                    komoditas dalam draf PO
                                </p>
                            </div>
                        </div>

                        <span
                            class="w-fit rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-success-ink">
                            Harga Terkunci
                        </span>
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
                            <a href="{{ route('klien.catalog') }}"
                                class="mt-1 inline-flex items-center gap-2 rounded-lg border border-line-hair bg-surface-shell px-3.5 py-2 transition-colors hover:border-brand">
                                <x-gpa.icon name="grid" class="h-3.5 w-3.5 text-ink" />
                                <span class="gpa-meta-lg text-ink">Buka Katalog</span>
                            </a>
                        </div>
                    </div>

                    <footer class="flex flex-col gap-2 border-t border-line-hair bg-surface p-4 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('klien.catalog') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-success px-3.5 py-2 transition-colors hover:bg-success-soft">
                            <x-gpa.icon name="plus" class="h-3.5 w-3.5 text-ink" />
                            <span class="gpa-meta-lg font-bold uppercase text-ink">Tambah Komoditas Lain dari Katalog</span>
                        </a>

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
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-ink text-white gpa-meta-lg font-bold">02</span>
                        <div class="min-w-0">
                            <h2 class="font-sans text-[17px] font-semibold leading-6 text-ink">
                                Parameter Pengiriman &amp; Titik Bongkar Muat
                            </h2>
                            <p class="gpa-body text-ink-body">Pilih titik dock tujuan dan waktu kedatangan armada chiller</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <label for="tanggal-pengiriman" class="gpa-label">Tanggal Pengiriman</label>
                            <div class="relative">
                                <input id="tanggal-pengiriman" type="date" x-model="deliveryDate"
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                    class="gpa-control h-10 pr-9 font-bold">
                                <x-gpa.icon name="calendar" class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                            </div>
                            <p class="gpa-hint">Minimal H+1 sesuai siklus panen dan sortasi harian.</p>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="jendela-dock" class="gpa-label">Jendela Kedatangan Armada</label>
                            <div class="relative">
                                <select id="jendela-dock" x-model="dockWindow" class="gpa-control h-10 appearance-none pr-9 font-bold text-ink">
                                    <option value="04:00 - 06:00 WIB (Subuh)">04:00 - 06:00 WIB (Subuh — Rekomendasi Resto/Horeca)</option>
                                    <option value="07:00 - 09:00 WIB (Pagi)">07:00 - 09:00 WIB (Pagi — Pengiriman Reguler)</option>
                                </select>
                                <x-gpa.icon name="clock" class="pointer-events-none absolute right-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-ink-quiet" />
                            </div>
                            <p class="gpa-hint font-semibold text-success-ink">SLA Bongkar Cepat &lt; 30 Menit di Dock Penerimaan.</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <p class="gpa-label">Titik Gudang / Receiving Dock</p>

                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($docks as $dock)
                                <button type="button" @click="selectDock(@js($dock['key']), @js($dock['address']))" role="radio"
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
                                <p class="gpa-micro text-ink-quiet">PIC Penerimaan Barang Terverifikasi</p>
                                <p class="mt-0.5 font-inter text-sm font-bold leading-5 text-ink">
                                    <span x-text="@js($receivingRoster)[pic]?.name"></span>
                                    (<span x-text="@js($receivingRoster)[pic]?.role"></span>)
                                </p>
                                <p class="gpa-meta text-ink-quiet">
                                    Kontak Operasional:
                                    <span class="text-ink-body" x-text="@js($receivingRoster)[pic]?.phone"></span>
                                    • Handover Digital Sign Off Ready
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="togglePic()"
                            class="shrink-0 self-end rounded-lg border border-line-hair bg-surface px-3 py-2 gpa-meta-lg font-bold text-ink transition-colors hover:border-brand sm:self-auto">
                            Ganti PIC
                        </button>

                        <div x-show="picOpen" x-cloak @click.outside="picOpen = false"
                            class="absolute right-3 top-full z-20 mt-1 flex w-[16rem] flex-col gap-1 rounded-xl border border-line-hair bg-surface p-1.5 shadow-pop">
                            @foreach ($receivingRoster as $index => $person)
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
                        <label for="catatan-supir" class="gpa-label">Instruksi Khusus Supir Armada &amp; Catatan Gerbang</label>
                        <textarea id="catatan-supir" rows="3" x-model="driverNote"
                            class="gpa-control min-h-[5.5rem] font-sans text-sm leading-[1.6]"></textarea>
                        <p class="gpa-hint">Nomor kontak darurat supir akan otomatis dikirimkan via notifikasi WhatsApp saat armada diberangkatkan.</p>
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
                            <h2 class="font-sans text-[17px] font-bold leading-5 text-ink">Ringkasan Estimasi PO</h2>
                            <p class="gpa-micro mt-1.5 text-ink-quiet">DRAFT-{{ date('Ymd') }}-GPA</p>
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
                                Biaya Distribusi Logistik
                                <x-gpa.icon name="info" class="h-3 w-3 text-success" />
                            </dt>
                            <dd class="shrink-0 text-right">
                                <p class="gpa-meta-lg font-bold text-success" x-text="money(shipping())"></p>
                                <p class="gpa-micro text-ink-quiet">Gratis ongkir franco gudang GPA</p>
                            </dd>
                        </div>

                        <div class="flex items-start justify-between gap-3">
                            <dt class="gpa-body text-ink-quiet">PPN Hasil Pertanian (PPN DTP)</dt>
                            <dd>
                                <span
                                    class="inline-block rounded bg-surface-pill px-1.5 py-0.5 gpa-micro-bold text-ink-quiet">
                                    Bebas PPN 0%
                                </span>
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-col gap-1.5 border-y border-line-hair bg-surface-muted/50 px-4 py-4">
                        <p class="gpa-micro-bold text-ink-quiet">Total Nilai Estimasi PO</p>
                        <p class="font-sans text-xl font-extrabold leading-7 text-ink" x-text="money(grandTotal())"></p>
                        <p class="gpa-body text-ink-body">*Nilai tagihan final dihitung berdasarkan bobot netto riil pada timbangan digital packing house.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="gpa-micro-bold uppercase text-ink">Pilihan Metode Pembayaran</p>
                            <span class="rounded bg-ink px-1.5 gpa-micro-bold text-white">{{ $isB2b ? 'Fasilitas Kontrak' : 'Reguler' }}</span>
                        </div>

                        <div class="flex flex-col gap-2" role="radiogroup" aria-label="Pilihan Metode Pembayaran">
                            @if ($isB2b)
                                <button type="button" role="radio" @click="payment = 'TEMPO_TOP'"
                                    :aria-checked="payment === 'TEMPO_TOP'"
                                    class="flex items-start gap-2.5 rounded-lg p-2.5 text-left transition-colors"
                                    :class="payment === 'TEMPO_TOP'
                                        ? 'bg-surface-muted outline outline-2 outline-ink'
                                        : 'bg-surface outline outline-1 outline-line-hair hover:outline-brand'">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                        :class="payment === 'TEMPO_TOP' ? 'bg-ink' : 'bg-surface-pill'">
                                        <span class="h-1.5 w-1.5 rounded-full bg-white"
                                            :class="payment === 'TEMPO_TOP' ? '' : 'opacity-0'"></span>
                                    </span>

                                    <span class="flex min-w-0 flex-col gap-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="gpa-meta-lg font-bold text-ink">Term of Payment (Tempo 30 Hari B2B)</span>
                                            <span x-show="payment === 'TEMPO_TOP'"
                                                x-cloak class="rounded bg-accent px-1.5 gpa-micro-bold text-ink">
                                                Rekomendasi B2B
                                            </span>
                                        </span>
                                        <span class="gpa-hint leading-3 font-semibold text-success-ink">
                                            Tagihan diagregasi ke faktur konsolidasi akhir bulan.
                                        </span>
                                    </span>
                                </button>
                            @endif

                            <button type="button" role="radio" @click="payment = 'TRANSFER_BANK'"
                                :aria-checked="payment === 'TRANSFER_BANK'"
                                class="flex items-start gap-2.5 rounded-lg p-2.5 text-left transition-colors"
                                :class="payment === 'TRANSFER_BANK'
                                    ? 'bg-surface-muted outline outline-2 outline-ink'
                                    : 'bg-surface outline outline-1 outline-line-hair hover:outline-brand'">
                                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                    :class="payment === 'TRANSFER_BANK' ? 'bg-ink' : 'bg-surface-pill'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white"
                                        :class="payment === 'TRANSFER_BANK' ? '' : 'opacity-0'"></span>
                                </span>

                                <span class="flex min-w-0 flex-col gap-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="gpa-meta-lg font-bold text-ink">Transfer Bank BCA / Mandiri (H+0)</span>
                                        <span x-show="payment === 'TRANSFER_BANK'"
                                            x-cloak class="rounded bg-accent px-1.5 gpa-micro-bold text-ink">
                                            Langsung
                                        </span>
                                    </span>
                                    <span class="gpa-hint leading-3 text-ink-quiet">
                                        Unggah bukti transfer pembayaran langsung pada sistem.
                                    </span>
                                </span>
                            </button>

                            <button type="button" role="radio" @click="payment = 'QRIS'"
                                :aria-checked="payment === 'QRIS'"
                                class="flex items-start gap-2.5 rounded-lg p-2.5 text-left transition-colors"
                                :class="payment === 'QRIS'
                                    ? 'bg-surface-muted outline outline-2 outline-ink'
                                    : 'bg-surface outline outline-1 outline-line-hair hover:outline-brand'">
                                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                    :class="payment === 'QRIS' ? 'bg-ink' : 'bg-surface-pill'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white"
                                        :class="payment === 'QRIS' ? '' : 'opacity-0'"></span>
                                </span>

                                <span class="flex min-w-0 flex-col gap-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="gpa-meta-lg font-bold text-ink">QRIS Dinamis GPA (Instant Settlement)</span>
                                        <span x-show="payment === 'QRIS'"
                                            x-cloak class="rounded bg-accent px-1.5 gpa-micro-bold text-ink">
                                            Otomatis
                                        </span>
                                    </span>
                                    <span class="gpa-hint leading-3 text-ink-quiet">
                                        Konfirmasi verifikasi otomatis setelah scan barcode.
                                    </span>
                                </span>
                            </button>

                            <button type="button" role="radio" @click="payment = 'COD'"
                                :aria-checked="payment === 'COD'"
                                class="flex items-start gap-2.5 rounded-lg p-2.5 text-left transition-colors"
                                :class="payment === 'COD'
                                    ? 'bg-surface-muted outline outline-2 outline-ink'
                                    : 'bg-surface outline outline-1 outline-line-hair hover:outline-brand'">
                                <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full"
                                    :class="payment === 'COD' ? 'bg-ink' : 'bg-surface-pill'">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white"
                                        :class="payment === 'COD' ? '' : 'opacity-0'"></span>
                                </span>

                                <span class="flex min-w-0 flex-col gap-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="gpa-meta-lg font-bold text-ink">Cash on Delivery (Bayar Saat Timbang Dock)</span>
                                        <span x-show="payment === 'COD'"
                                            x-cloak class="rounded bg-accent px-1.5 gpa-micro-bold text-ink">
                                            Tunai
                                        </span>
                                    </span>
                                    <span class="gpa-hint leading-3 text-ink-quiet">
                                        Hanya berlaku untuk pengiriman reguler dengan serah terima fisik.
                                    </span>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Form Nyata Pengiriman Order ke Server --}}
                    <form id="order-submit-form" action="{{ route('klien.orders.store') }}" method="POST" enctype="multipart/form-data" class="pt-2">
                        @csrf
                        <input type="hidden" name="target_delivery_date" :value="deliveryDate" value="{{ $defaultDeliveryDate }}">
                        <input type="hidden" name="delivery_address" x-model="deliveryAddress" :value="deliveryAddress" value="{{ $defaultAddress }}">
                        <input type="hidden" name="payment_method" :value="payment" value="{{ $isB2b ? 'TEMPO_TOP' : 'TRANSFER_BANK' }}">

                        {{-- Hidden inputs untuk items --}}
                        <template x-for="(line, idx) in lines" :key="`input-${line.key}`">
                            <div>
                                <input type="hidden" :name="`items[${idx}][product_id]`" :value="line.id || (commodities.find(c => c.key === line.key)?.id) || (parseInt(String(line.key).replace(/\D/g, '')) || 1)">
                                <input type="hidden" :name="`items[${idx}][quantity]`" :value="line.qty">
                            </div>
                        </template>

                        {{-- File input untuk bukti transfer jika bayar via transfer/qris --}}
                        <div x-show="payment === 'TRANSFER_BANK'" x-cloak class="mb-3 flex flex-col gap-1 rounded-lg border border-line-soft bg-surface-shell p-3">
                            <label class="gpa-micro-bold text-ink" for="payment_proof">Unggah Bukti Transfer (Opsional saat PO):</label>
                            <input type="file" id="payment_proof" name="payment_proof" accept="image/*,application/pdf"
                                class="text-xs file:mr-2 file:rounded file:border-0 file:bg-ink file:px-2.5 file:py-1 file:text-xs file:font-semibold file:text-white hover:file:bg-brand-deep">
                            <span class="gpa-hint">Format JPG/PNG/PDF maks 5 MB. Bukti dapat diunggah belakangan di menu Dokumen.</span>
                        </div>

                        <div class="flex flex-col gap-2">
                            <button type="submit" :disabled="empty()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-ink px-4 py-3 shadow-pop transition-colors hover:bg-brand-deep disabled:cursor-not-allowed disabled:opacity-50">
                                <x-gpa.icon name="send" class="h-4 w-4 text-white" />
                                <span class="font-sans text-sm font-bold uppercase tracking-tight text-white">
                                    Ajukan PO ke Admin
                                </span>
                            </button>

                            <button type="button" @click="saveDraft()"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-surface-pill px-4 py-2.5 text-ink-body transition-colors hover:bg-surface-disabled hover:text-ink">
                                <x-gpa.icon name="save" class="h-3 w-3.5 text-ink-quiet" />
                                <span class="gpa-meta-lg font-bold uppercase text-ink">Simpan Draf Pesanan</span>
                            </button>
                        </div>
                    </form>

                    <div class="flex items-center justify-between gap-3 border-t border-line-hair pt-3">
                        <p class="gpa-micro text-ink-quiet">
                            Status PO:<br />
                            <span class="text-ink" x-text="statusLabel">Draf Siap Diajukan</span>
                        </p>
                        <p class="gpa-micro text-right text-ink">
                            SLA Respon Admin: <span class="font-bold">&lt; 2 Jam Kerja</span>
                        </p>
                    </div>
                </section>

                <section
                    class="flex items-start gap-2.5 rounded-xl border border-line-hair bg-surface-muted/70 p-3">
                    <x-gpa.icon name="badge-check" class="mt-0.5 h-4 w-4 shrink-0 text-success" />
                    <div class="flex min-w-0 flex-col">
                        <p class="gpa-meta-lg font-bold text-ink">Jaminan Cold-Chain &amp; Standar Mutu GPA</p>
                        <p class="mt-1 text-xs leading-4 text-ink-quiet">Komoditas dilindungi pendingin 2-6°C sejak pemanenan. Apabila terjadi deviasi mutu lebih dari 2%, klaim retur dapat diajukan saat serah terima dock.</p>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
