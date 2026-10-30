@extends('layouts.klien')

@section('title', 'Keranjang & Draft Purchase Order')

@php
    $user = $user ?? auth()->user();
    $isB2b = $isB2b ?? ($user->client_type === 'B2B_KONTRAK');
    $commodities = $commodities ?? [];
    $defaultDeliveryDate = $defaultDeliveryDate ?? now()->addDay()->format('Y-m-d');
    $defaultAddress = $defaultAddress ?? ($user->address ?: '');
    $docks = $docks ?? [
        [
            'key'      => 'dock-utama',
            'name'     => $user->company_name ?: ($user->name ?: 'Alamat Terdaftar'),
            'address'  => $defaultAddress,
            'chip'     => 'Alamat Utama',
            'access'   => 'Akses Pengiriman Terdaftar',
        ],
    ];
    $receivingRoster = $receivingRoster ?? [
        [
            'name'  => $user->pic_name ?: $user->name,
            'role'  => 'PIC Penerima Klien',
            'phone' => $user->pic_phone ?: ($user->phone ?: '—'),
        ],
    ];
    $demoLines = $demoLines ?? [];
    $cartDefaults = $cartDefaults ?? [
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

@section('content')
    <div x-data="clientCart(@js($commodities), @js($demoLines), @js($cartDefaults))"
        class="flex flex-col gap-4">
        {{-- ---------------------------------------------------------------- --}}
        {{-- Breadcrumb + status telemetry --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span
                    class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-success-ink">
                    Tersinkronisasi
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
        {{-- Peringatan Standar Operasional Pengiriman --}}
        {{-- ---------------------------------------------------------------- --}}
        <section
            class="flex flex-col gap-3 rounded-xl border-l-4 border-warning-caution bg-warning-cream py-4 pl-4 pr-4 shadow-sub sm:flex-row sm:items-start sm:gap-3 sm:pl-6 sm:pr-6">
            <x-gpa.icon name="alert-triangle" class="mt-0.5 h-4 w-5 shrink-0 text-warning-caution" />

            <div class="flex min-w-0 flex-col gap-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded bg-warning-caution px-1.5 py-0.5 gpa-micro-bold text-white">
                        Standar Operasional Pengiriman
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
                            <p class="gpa-hint font-semibold text-success-ink">Target Bongkar Cepat &lt; 30 Menit di Dock Penerimaan.</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="gpa-label">Titik Gudang / Receiving Dock</p>
                            <div class="flex items-center gap-1 rounded-lg border border-line-hair bg-surface-shell p-1">
                                <button type="button" @click="dockMode = 'preset'"
                                    :class="dockMode === 'preset' ? 'bg-surface font-bold text-ink shadow-sm' : 'text-ink-quiet hover:text-ink'"
                                    class="rounded-md px-2.5 py-1 text-xs transition">
                                    Alamat Terdaftar
                                </button>
                                <button type="button" @click="dockMode = 'custom'"
                                    :class="dockMode === 'custom' ? 'bg-surface font-bold text-ink shadow-sm' : 'text-ink-quiet hover:text-ink'"
                                    class="rounded-md px-2.5 py-1 text-xs transition">
                                    + Titik / Alamat Kustom
                                </button>
                            </div>
                        </div>

                        {{-- Mode Alamat Profil / Preset --}}
                        <div x-show="dockMode === 'preset'" class="flex flex-col gap-3">
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

                            <div>
                                <label class="gpa-hint mb-1 block font-medium text-ink-body">Rincian / Penyesuaian Alamat Pengiriman:</label>
                                <textarea x-model="deliveryAddress" rows="2"
                                    placeholder="Rincian jalan, nomor gedung, atau patokan bongkar muat..."
                                    class="w-full rounded-lg border border-line-strong bg-surface p-2.5 text-xs text-ink focus:border-brand focus:ring-0"></textarea>
                            </div>
                        </div>

                        {{-- Mode Alamat / Titik Gudang Kustom Baru --}}
                        <div x-show="dockMode === 'custom'" x-cloak class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface-shell/60 p-4">
                            <div>
                                <label class="gpa-label mb-1 block">Nama Titik Gudang / Cabang Tujuan *</label>
                                <input type="text" x-model="customDockName" placeholder="Contoh: Gudang Cabang Dago / Resto Kitchen Pasteur"
                                    class="h-10 w-full rounded-lg border border-line-strong bg-surface px-3 text-xs text-ink focus:border-brand focus:ring-0">
                            </div>
                            <div>
                                <label class="gpa-label mb-1 block">Alamat Lengkap Titik Bongkar Muat *</label>
                                <textarea x-model="customDockAddress" rows="2"
                                    placeholder="Jl. Raya No. XX, Kelurahan, Kecamatan, Kota / Patokan Pintu Masuk Truk..."
                                    class="w-full rounded-lg border border-line-strong bg-surface p-2.5 text-xs text-ink focus:border-brand focus:ring-0"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- PIC Penerimaan Barang --}}
                    <div class="relative flex flex-col gap-3 rounded-xl border border-line-hair bg-surface-shell p-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand">
                                <x-gpa.icon name="user" class="h-4 w-4 text-accent" />
                            </span>
                            <div class="flex min-w-0 flex-col">
                                <p class="gpa-micro text-ink-quiet">
                                    <span x-show="picMode === 'preset'">PIC Penerimaan Terdaftar</span>
                                    <span x-show="picMode === 'custom'" class="font-bold text-brand">PIC Kustom (Pesanan Ini)</span>
                                </p>
                                <p class="mt-0.5 truncate font-inter text-sm font-bold leading-5 text-ink">
                                    <template x-if="picMode === 'preset'">
                                        <span>
                                            <span x-text="@js($receivingRoster)[pic]?.name"></span>
                                            (<span x-text="@js($receivingRoster)[pic]?.role"></span>)
                                        </span>
                                    </template>
                                    <template x-if="picMode === 'custom'">
                                        <span x-text="customPicName ? customPicName + ' (PIC Khusus)' : 'Belum diisi (Klik Ganti/Kustom)'"></span>
                                    </template>
                                </p>
                                <p class="gpa-meta text-ink-quiet">
                                    Kontak Operasional:
                                    <span class="font-semibold text-ink-body"
                                        x-text="picMode === 'preset' ? @js($receivingRoster)[pic]?.phone : (customPicPhone || '—')"></span>
                                    • Handover Digital Sign Off Ready
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="togglePic()"
                                class="shrink-0 self-end rounded-lg border border-line-hair bg-surface px-3 py-2 gpa-meta-lg font-bold text-ink transition-colors hover:border-brand sm:self-auto">
                                <span x-text="picMode === 'custom' ? 'Ubah PIC Kustom' : 'Ganti / Kustom PIC'"></span>
                            </button>
                        </div>

                        <div x-show="picOpen" x-cloak @click.outside="picOpen = false"
                            class="absolute right-3 top-full z-30 mt-1 flex w-80 flex-col gap-2 rounded-xl border border-line-hair bg-surface p-3 shadow-2xl">
                            <div class="border-b border-line-hair pb-2">
                                <p class="text-xs font-bold text-ink">Pilih atau Kustom PIC Penerima</p>
                                <p class="text-[10px] text-ink-quiet">Petugas yang menerima dan menandatangani surat jalan pengiriman.</p>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] font-bold uppercase text-ink-quiet">PIC Terdaftar di Profil:</span>
                                @foreach ($receivingRoster as $index => $person)
                                    <button type="button" @click="changePic(@js($index))"
                                        class="flex flex-col items-start rounded-lg border border-transparent p-2 text-left transition-colors hover:border-line-hair hover:bg-surface-shell"
                                        :class="picMode === 'preset' && pic === @js($index) ? 'bg-surface-muted border-line-hair' : ''">
                                        <span class="font-inter text-xs font-bold text-ink">{{ $person['name'] }}</span>
                                        <span class="gpa-micro text-ink-quiet">{{ $person['role'] }}</span>
                                        <span class="gpa-meta text-ink-body">{{ $person['phone'] }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="flex flex-col gap-2 border-t border-line-hair pt-2">
                                <span class="text-[10px] font-bold uppercase text-brand">Atau Masukkan PIC Kustom / Baru:</span>
                                <div>
                                    <label class="mb-0.5 block text-[10px] text-ink-quiet">Nama Lengkap PIC</label>
                                    <input type="text" x-model="customPicName" placeholder="Contoh: Bpk. Rudi Hermawan"
                                        @input="picMode = 'custom'"
                                        class="h-8 w-full rounded border border-line-strong bg-surface px-2.5 text-xs text-ink focus:border-brand focus:ring-0">
                                </div>
                                <div>
                                    <label class="mb-0.5 block text-[10px] text-ink-quiet">Nomor Telepon / WhatsApp PIC</label>
                                    <input type="text" x-model="customPicPhone" placeholder="Contoh: 0812-3456-7890"
                                        @input="picMode = 'custom'"
                                        class="h-8 w-full rounded border border-line-strong bg-surface px-2.5 text-xs text-ink focus:border-brand focus:ring-0">
                                </div>
                                <button type="button" @click="picMode = 'custom'; picOpen = false"
                                    :disabled="!customPicName"
                                    class="w-full rounded bg-ink py-1.5 text-xs font-bold text-white hover:bg-brand-deep disabled:opacity-50">
                                    Terapkan PIC Kustom Ini
                                </button>
                            </div>
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

                    {{-- Informasi Alur Pembayaran GPA (Sesuai SOP Verifikasi Sekretaris) --}}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="gpa-micro-bold uppercase text-ink">Alur &amp; Skema Pembayaran GPA</p>
                            <span class="rounded bg-ink px-1.5 gpa-micro-bold text-white">{{ $isB2b ? 'Fasilitas Kontrak B2B' : 'Klien Reguler' }}</span>
                        </div>

                        @if ($isB2b)
                            <div class="flex flex-col gap-2 rounded-xl border border-line-hair bg-surface-shell/80 p-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-success/20 text-success-deep">
                                        <x-gpa.icon name="shield-check" class="h-3.5 w-3.5" />
                                    </span>
                                    <p class="gpa-meta-lg font-bold text-ink">Term of Payment (Tempo 30 Hari)</p>
                                </div>
                                <p class="text-xs leading-relaxed text-ink-body">
                                    Sebagai mitra B2B Kontrak, pesanan akan langsung dialokasikan ke gudang packing dan armada pengiriman segera setelah diverifikasi oleh Sekretaris Operasional. Seluruh tagihan diagregasi ke faktur konsolidasi bulanan.
                                </p>
                            </div>
                        @else
                            <div class="flex flex-col gap-3 rounded-xl border border-line-hair bg-surface-shell/80 p-3.5">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand/20 text-brand">
                                        <x-gpa.icon name="info" class="h-3.5 w-3.5" />
                                    </span>
                                    <p class="gpa-meta-lg font-bold text-ink">Verifikasi Kuota Panen &amp; Pembayaran</p>
                                </div>
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex items-start gap-2">
                                        <span class="rounded bg-success/20 px-1 font-mono text-[10px] font-bold text-success-deep">01</span>
                                        <p class="text-ink-body"><strong>Pengajuan Draf PO:</strong> Anda mengirimkan draf kebutuhan komoditas.</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="rounded bg-warning/30 px-1 font-mono text-[10px] font-bold text-warning-deep">02</span>
                                        <p class="text-ink-body"><strong>Verifikasi Sekretaris:</strong> Admin memeriksa kuota panen riil kebun (&lt; 2 jam kerja).</p>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <span class="rounded bg-brand/20 px-1 font-mono text-[10px] font-bold text-brand">03</span>
                                        <p class="text-ink-body"><strong>Penerbitan Invoice &amp; Pembayaran:</strong> Setelah disetujui, nomor Faktur/Invoice resmi terbit. Anda kemudian melakukan transfer dan mengunggah bukti bayar pada menu <em>Dokumen &amp; Faktur</em>.</p>
                                    </div>
                                </div>
                                <p class="border-t border-line-hair pt-2 text-[11px] text-ink-quiet">
                                    *Pembayaran dilakukan setelah Sekretaris memverifikasi ketersediaan pasokan panen.
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Form Nyata Pengiriman Order ke Server --}}
                    <form id="order-submit-form" action="{{ route('klien.orders.store') }}" method="POST" class="pt-2"
                        @submit="handleOrderSubmit($event)">
                        @csrf
                        <input type="hidden" name="submission_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                        <input type="hidden" name="target_delivery_date" :value="deliveryDate" value="{{ $defaultDeliveryDate }}">
                        <input type="hidden" name="delivery_address" :value="getFinalDeliveryAddress()">
                        <input type="hidden" name="payment_method" value="{{ $isB2b ? 'TEMPO_TOP' : 'TRANSFER_BANK' }}">

                        {{-- Hidden inputs untuk items --}}
                        <template x-for="(line, idx) in lines" :key="`input-${line.key}`">
                            <div>
                                <input type="hidden" :name="`items[${idx}][product_id]`" :value="line.id || (commodities.find(c => c.key === line.key)?.id) || (parseInt(String(line.key).replace(/\D/g, '')) || 1)">
                                <input type="hidden" :name="`items[${idx}][quantity]`" :value="line.qty">
                            </div>
                        </template>

                        <div class="flex flex-col gap-2">
                            <button type="submit" :disabled="empty() || isSubmitting"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-ink px-4 py-3 shadow-pop transition-colors hover:bg-brand-deep disabled:cursor-not-allowed disabled:opacity-50">
                                <template x-if="!isSubmitting">
                                    <span class="inline-flex items-center gap-2">
                                        <x-gpa.icon name="send" class="h-4 w-4 text-white" />
                                        <span class="font-sans text-sm font-bold uppercase tracking-tight text-white">
                                            Ajukan PO ke Admin
                                        </span>
                                    </span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span class="inline-flex items-center gap-2">
                                        <svg class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="font-sans text-sm font-bold uppercase tracking-tight text-white">
                                            Mengajukan PO ke Admin...
                                        </span>
                                    </span>
                                </template>
                            </button>

                            <button type="button" @click="saveDraft()" :disabled="isSubmitting"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-surface-pill px-4 py-2.5 text-ink-body transition-colors hover:bg-surface-disabled hover:text-ink disabled:opacity-50">
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
                            Waktu Respon Admin: <span class="font-bold">&lt; 2 Jam Kerja</span>
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
