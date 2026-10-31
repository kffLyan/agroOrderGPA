@extends('layouts.sekretaris')

@section('title', 'Input Order Manual')

@section('content')
    @php
        $user = auth()->user();
        $code = $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001';
        $clientCode = $clientCode ?? ($clientUser ? ('CLI-' . str_pad($clientUser->id, 4, '0', STR_PAD_LEFT)) : '—');
        $sourceData = $sourceData ?? [];
        $source = $source ?? $sourceData;
        $clientData = $clientData ?? [];
        $logisticsData = $logisticsData ?? [];
        $paymentData = $paymentData ?? [];
    @endphp

    <script>
        window.gpaManualOrderStoreUrl = "{{ route('sekretaris.pesanan-manual.store') }}";
    </script>

    <div class="space-y-6"
        x-data="secretaryManualOrder(@js($commodities ?? []), @js($catalog ?? []), @js($logisticsData), @js($paymentData), @js($sourceData), @js($clientData), @js($clients ?? []), @js($paymentChannels ?? []))">

        {{-- Policy banner: Supply Chain Guardrails --}}
        <section class="gpa-panel overflow-hidden">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-line-soft px-6 py-5">
                <div class="min-w-0">
                    <p class="gpa-eyebrow">Input Pesanan Manual</p>
                    <div class="mt-1.5 flex flex-wrap items-center gap-2">
                        <h1 class="gpa-figure text-brand">Input Order Manual</h1>
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-warning-soft px-2 py-0.5 gpa-micro-bold text-warning-caution">
                            <span class="h-1.5 w-1.5 rounded-full bg-warning-caution" aria-hidden="true"></span>
                            Status: Pengisian Formulir
                        </span>
                    </div>
                    <p class="mt-2 max-w-4xl text-sm leading-[1.4rem] text-ink-body">Pencatatan pesanan darurat dari pembeli via telepon atau WhatsApp. Seluruh jumlah pesanan yang dimasukkan akan dicek ketersediaan stoknya sebelum disahkan.</p>
                </div>

                <span
                    class="shrink-0 rounded-xl bg-surface px-4 py-2 text-right shadow-sub outline outline-1 outline-line-board">
                    <span class="block gpa-micro text-ink-quiet">Supply Chain Guardrails</span>
                    <span class="block gpa-meta-lg font-bold tracking-[0.28px] text-success-deep">
                        Cek Batas Kredit • Cek Stok Sayur • Validasi Netto
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
                        <h2 class="gpa-section-title text-ink">Sumber Pesanan</h2>
                        <p class="gpa-note text-ink-quiet">Kanal masuk pemesanan dari pelanggan.</p>
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
                        <h2 class="gpa-section-title text-ink">Data Pembeli</h2>
                        <p class="gpa-note text-ink-quiet">Pilih pembeli terdaftar atau masukkan data pembeli baru.</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full px-2.5 py-1 gpa-micro-bold transition-colors"
                    :class="clientMode === 'new' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-success-soft text-success-ink'"
                    x-text="clientMode === 'new' ? 'BARU / NON-KONTRAK' : (client.code || '{{ $clientCode }}')">
                    {{ $clientCode }}
                </span>
            </div>

            <div class="space-y-5 px-6 py-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex flex-wrap gap-1.5 rounded-lg bg-surface-shell p-1" role="group"
                        aria-label="Mode registrasi klien">
                        <button type="button" @click="setClientMode('registered')"
                            :class="clientMode === 'registered'
                                ? 'bg-brand text-accent shadow-sub'
                                : 'text-ink-body hover:bg-surface'"
                            class="rounded px-3 py-1.5 gpa-meta-lg font-semibold tracking-[0.07em] transition-colors">
                            Pembeli Terdaftar
                        </button>
                        <button type="button" @click="setClientMode('new')"
                            :class="clientMode === 'new'
                                ? 'bg-brand text-accent shadow-sub'
                                : 'text-ink-body hover:bg-surface'"
                            class="rounded px-3 py-1.5 gpa-meta-lg font-semibold tracking-[0.07em] transition-colors">
                            Pembeli Baru
                        </button>
                    </div>

                    <div x-show="clientMode === 'registered'" class="flex items-center gap-2">
                        <label class="gpa-label text-ink-quiet" for="manual-client-select">Pilih Mitra:</label>
                        <select id="manual-client-select" @change="selectClient($event.target.value)"
                            class="gpa-control min-w-[260px] font-sans text-xs font-bold text-brand">
                            @foreach ($clients as $cl)
                                <option value="{{ $cl['id'] }}" :selected="selectedClientId == {{ $cl['id'] }}">
                                    {{ $cl['name'] }} ({{ $cl['code'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-cloak x-show="clientMode === 'new'" class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-900">
                            <x-gpa.icon name="user" class="h-3.5 w-3.5 text-amber-700" />
                            Pembeli Retail / Non-PKS (Spot Buyer)
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="gpa-label" for="manual-client-name">Nama Klien / Perusahaan</label>
                        <input id="manual-client-name" type="text" x-model="client.name"
                            :placeholder="clientMode === 'new' ? 'Contoh: Resto Sunda / Bpk. Hendra' : 'Nama Perusahaan Mitra'"
                            class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1" x-text="clientMode === 'new' ? 'Kategori: Pembeli Baru / Retail / Umum' : 'Segmen: Hotel, Resto & Katering'"></span>
                    </div>

                    <div>
                        <label class="gpa-label" for="manual-client-contact">Penanggung Jawab / PIC Dapur</label>
                        <input id="manual-client-contact" type="text" x-model="client.contact"
                            :placeholder="clientMode === 'new' ? 'Nama PIC / Pemesan' : 'Nama PIC'"
                            class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1" x-show="clientMode === 'registered'">Telepon: <span x-text="client.phone"></span></span>
                        <span class="gpa-hint mt-1 text-amber-800" x-cloak x-show="clientMode === 'new'">PIC Penerima Sayur di Lokasi</span>
                    </div>

                    <div x-cloak x-show="clientMode === 'new'">
                        <label class="gpa-label" for="manual-client-phone">No. Handphone / WhatsApp</label>
                        <input id="manual-client-phone" type="text" x-model="client.phone"
                            placeholder="Contoh: 0812-3456-7890"
                            class="gpa-control mt-1.5 font-mono text-xs">
                        <span class="gpa-hint mt-1">Konfirmasi pengantaran &amp; e-nota</span>
                    </div>

                    <div :class="clientMode === 'new' ? 'md:col-span-1 xl:col-span-1' : 'md:col-span-2 xl:col-span-2'">
                        <label class="gpa-label" for="manual-client-address">Alamat Pengiriman Sayur</label>
                        <input id="manual-client-address" type="text" x-model="client.address"
                            :placeholder="clientMode === 'new' ? 'Alamat lengkap pengantaran / Ambil Sendiri di Kebun' : 'Alamat Pengiriman'"
                            class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1" x-text="clientMode === 'new' ? 'Akses armada: Mobil Boks / Pick-up / Motor' : 'Batas armada: Akses Armada: Mobil Boks Pendingin'"></span>
                    </div>
                </div>

                {{-- Batas kredit B2B (Hanya berlaku untuk Pembeli Terdaftar / Mitra Kontrak) --}}
                <div x-show="clientMode === 'registered'" class="rounded-xl border border-line-faint bg-surface-shell p-4 transition-all">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <x-gpa.icon name="wallet" class="h-4 w-4 shrink-0 text-success-deep" />
                            <span class="gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                Batas Plafon Kredit Pembeli
                            </span>
                        </div>

                        <span class="inline-flex items-center gap-1 rounded-full bg-success-soft px-2 py-0.5 gpa-micro-bold text-success-ink">
                            <x-gpa.icon name="check-circle" class="h-3 w-3 shrink-0" />
                            Kredit Aktif &amp; Aman
                        </span>
                    </div>

                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <p class="gpa-micro text-ink-quiet">Kredit Terpakai</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-ink"
                                x-text="compactRupiah(creditUsed)"></p>
                        </div>
                        <div>
                            <p class="gpa-micro text-ink-quiet">Sisa Plafon Kredit Tersedia</p>
                            <p class="gpa-figure text-[1.25rem] leading-6 text-success-deep"
                                x-text="rupiah(creditRemaining)"></p>
                        </div>
                        <div>
                            <p class="gpa-micro text-ink-quiet">Batas Maksimal Kredit</p>
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
                            x-text="creditOk ? 'Plafon Kredit Aman' : 'Melebihi Plafon'"></span>
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
                        <p class="gpa-note text-ink-quiet">Basis stok sistem dipakai untuk mencegah penjualan melebihi kapasitas panen (anti-overselling).</p>
                    </div>
                </div>

                <span x-cloak x-show="! ready"
                    class="inline-flex shrink-0 items-center gap-1 rounded-full bg-danger-soft px-2.5 py-1 gpa-micro-bold text-danger-ink">
                    <x-gpa.icon name="alert-triangle" class="h-3 w-3 shrink-0" />
                    <span x-text="blockers().length + ' validasi gagal'"></span>
                </span>
            </div>

            <div class="gpa-scroll-x px-6 py-5">
                <table class="w-full border-collapse text-left">
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
                    <select id="manual-catalog" x-model="selectedCatalog" class="gpa-control min-w-[16rem]">
                        <option value="">Pilih Komoditas Katalog...</option>
                        @foreach ($catalog as $item)
                            <option value="{{ $item['key'] }}">{{ $item['name'] }} ({{ $item['sku'] }}) - Stok {{ number_format($item['stock'], 0, ',', '.') }} kg</option>
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
                        <h2 class="gpa-section-title text-ink">Jadwal &amp; Rencana Pengiriman</h2>
                        <p class="gpa-note text-ink-quiet">Atur tanggal target pengiriman, estimasi waktu tiba, dan instruksi penanganan sayuran.</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-success-ink">
                    Pengiriman Sayuran Segar
                </span>
            </div>

            <div class="space-y-4 px-6 py-5">
                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label class="gpa-label" for="manual-date">Tanggal Rencana Pengiriman</label>
                        <input id="manual-date" type="date" x-model="logistics.date" class="gpa-control mt-1.5">
                        <span class="gpa-hint mt-1">Standar panen subuh kirim pagi (H+1)</span>
                    </div>

                    <div>
                        <label class="gpa-label" for="manual-arrival">Waktu Tiba di Lokasi Klien</label>
                        <select id="manual-arrival" x-model="logistics.arrival" class="gpa-control mt-1.5">
                            <option value="Pagi Hari (05:00 - 08:00 WIB)">Pagi Hari (05:00 - 08:00 WIB) - Resto &amp; Hotel</option>
                            <option value="Siang Hari (10:00 - 12:00 WIB)">Siang Hari (10:00 - 12:00 WIB)</option>
                            <option value="Sore Hari (14:00 - 16:00 WIB)">Sore Hari (14:00 - 16:00 WIB)</option>
                        </select>
                        <span class="gpa-hint mt-1">Estimasi serah terima di dapur klien</span>
                    </div>

                    <div>
                        <label class="gpa-label" for="manual-route">Rute / Koridor Distribusi</label>
                        <input id="manual-route" type="text" x-model="logistics.route" class="gpa-control mt-1.5"
                            placeholder="Contoh: Koridor Ciwidey - Bandung / Jakarta" spellcheck="false">
                        <span class="gpa-hint mt-1">Koridor logistik sayuran GPA</span>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_auto]">
                    <div>
                        <label class="gpa-label" for="manual-notes">Catatan Khusus Pengiriman &amp; Penanganan</label>
                        <textarea id="manual-notes" rows="2" x-model="logistics.notes"
                            placeholder="Contoh: Pisahkan krat sayur daun, jangan ditumpuk lebih dari 3 krat. Serahkan langsung ke Chef Dapur."
                            class="gpa-control mt-1.5 font-sans text-xs leading-5"></textarea>
                    </div>

                    <div class="min-w-[260px]">
                        <label class="gpa-label" for="manual-fee">Biaya Kirim / Ongkir (Input Manual)</label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-ink-quiet">Rp</span>
                            <input id="manual-fee" type="number" x-model.number="logistics.fee" min="0" step="5000"
                                class="gpa-control pl-10 font-sans text-sm font-bold text-brand" placeholder="0">
                        </div>
                        <div class="mt-1 flex flex-wrap gap-1 text-[11px]">
                            <button type="button" @click="logistics.fee = 0" class="rounded bg-surface-pill px-2 py-0.5 font-semibold text-ink-body hover:bg-line-soft">Rp 0</button>
                            <button type="button" @click="logistics.fee = 50000" class="rounded bg-surface-pill px-2 py-0.5 font-semibold text-ink-body hover:bg-line-soft">50rb</button>
                            <button type="button" @click="logistics.fee = 75000" class="rounded bg-surface-pill px-2 py-0.5 font-semibold text-ink-body hover:bg-line-soft">75rb</button>
                            <button type="button" @click="logistics.fee = 100000" class="rounded bg-surface-pill px-2 py-0.5 font-semibold text-ink-body hover:bg-line-soft">100rb</button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 rounded-lg bg-surface-shell px-3.5 py-2 text-xs text-ink-quiet border border-line-faint">
                    <x-gpa.icon name="info" class="h-4 w-4 shrink-0 text-brand" />
                    <span>Penugasan armada riil (truk berpendingin &amp; nomor plat polisi) serta pengemudi resmi GPA dilakukan pada menu <strong>Pengiriman (Dispatch)</strong> setelah pesanan disetujui.</span>
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
                        <h2 class="gpa-section-title text-ink">Cara Pembayaran &amp; Bukti Transfer</h2>
                        <p class="gpa-note text-ink-quiet">Sesuaikan dengan kanal pembayaran resmi GPA (ditetapkan oleh Direktur) atau skema termin khusus B2B.</p>
                    </div>
                </div>

                <span class="shrink-0 rounded-full bg-accent px-2.5 py-1 gpa-micro-bold text-success-ink">
                    Gateway &amp; Rekening Resmi Direksi
                </span>
            </div>

            <div class="grid gap-6 px-6 py-5 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
                {{-- Kolom Kiri: Pemilihan Metode / Kanal Pembayaran --}}
                <div class="space-y-4">
                    {{-- Tab Pemilih Kategori: Kanal Resmi vs Tunai/Cash vs Termin B2B --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-soft pb-3">
                        <div class="inline-flex flex-wrap gap-1.5 rounded-lg bg-surface-shell p-1" role="group"
                            aria-label="Kategori Cara Pembayaran">
                            <button type="button" @click="paymentCategory = 'official'; selectChannel(selectedChannelId)"
                                :class="paymentCategory === 'official'
                                    ? 'bg-brand text-accent shadow-sub'
                                    : 'text-ink-body hover:bg-surface'"
                                class="rounded px-3 py-1.5 gpa-meta-lg font-semibold tracking-[0.07em] transition-colors">
                                Kanal Resmi GPA (Bank / QRIS)
                            </button>
                            <button type="button" @click="selectCashMethod()"
                                :class="paymentCategory === 'cash'
                                    ? 'bg-brand text-accent shadow-sub'
                                    : 'text-ink-body hover:bg-surface'"
                                class="inline-flex items-center gap-1.5 rounded px-3 py-1.5 gpa-meta-lg font-semibold tracking-[0.07em] transition-colors">
                                <x-gpa.icon name="banknote" class="h-4 w-4" />
                                <span>Tunai / Cash (Kasir)</span>
                            </button>
                            <button type="button" @click="paymentCategory = 'b2b'; selectB2bMethod('cod')"
                                :class="paymentCategory === 'b2b'
                                    ? 'bg-brand text-accent shadow-sub'
                                    : 'text-ink-body hover:bg-surface'"
                                class="rounded px-3 py-1.5 gpa-meta-lg font-semibold tracking-[0.07em] transition-colors">
                                Termin Khusus B2B / COD / SK Direktur
                            </button>
                        </div>

                        <span class="gpa-note text-ink-quiet">
                            <template x-if="paymentCategory === 'cash'">
                                <span class="font-bold text-success-deep flex items-center gap-1">
                                    <x-gpa.icon name="check-circle" class="h-3.5 w-3.5" />
                                    Kas Masuk Langsung H+0
                                </span>
                            </template>
                            <template x-if="paymentCategory !== 'cash'">
                                <span>Status Kanal: <strong class="text-success-deep" x-text="channels.length + ' Aktif Disetujui Direksi'"></strong></span>
                            </template>
                        </span>
                    </div>

                    {{-- MODE 1: Kanal Resmi GPA (Sesuai Konfigurasi Direktur & Payment Proof) --}}
                    <div x-show="paymentCategory === 'official'" class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="gpa-label">Pilih Rekening Tujuan / Kanal Resmi:</label>
                                <span class="gpa-note text-brand font-semibold">Tersinkronisasi dengan Portal Klien</span>
                            </div>

                            {{-- Grid Kartu Kanal Pembayaran --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                <template x-for="ch in channels" :key="ch.id">
                                    <button type="button" @click="selectChannel(ch.id)"
                                        class="flex items-center gap-2.5 rounded-xl border p-2.5 text-left transition-all"
                                        :class="selectedChannelId === ch.id 
                                            ? 'border-brand bg-brand text-white shadow-sub ring-2 ring-accent' 
                                            : 'border-line-soft bg-surface text-ink hover:border-line-board hover:bg-surface-muted'">
                                        <div class="flex h-10 w-14 shrink-0 items-center justify-center rounded-lg bg-white p-1.5 shadow-xs border border-line-soft overflow-hidden">
                                            <template x-if="ch.logo_url">
                                                <img :src="ch.logo_url" :alt="ch.name" class="h-full w-full object-contain" />
                                            </template>
                                            <template x-if="!ch.logo_url">
                                                <span class="font-mono text-xs font-extrabold text-ink"
                                                    x-text="ch.bank_code || (ch.type === 'QRIS' ? 'QRIS' : (ch.type === 'EWALLET' ? 'WALLET' : 'BANK'))">
                                                </span>
                                            </template>
                                        </div>
                                        <div class="min-w-0 flex flex-col">
                                            <span class="font-sans text-xs font-bold truncate" :class="selectedChannelId === ch.id ? 'text-white' : 'text-ink'" x-text="ch.name"></span>
                                            <span class="font-mono text-[10px] truncate" :class="selectedChannelId === ch.id ? 'text-accent' : 'text-ink-quiet'" x-text="ch.account_number"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Kartu Detail Penerima Terpilih (Identik dengan Payment Proof Klien & Direktur) --}}
                        <div class="flex flex-col gap-3 rounded-xl bg-brand p-4 ring-1 ring-inset ring-accent/30 text-white">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="flex h-12 w-20 shrink-0 items-center justify-center rounded-xl bg-white p-2 shadow-sub border border-white/30 overflow-hidden">
                                        <template x-if="selectedChannel?.logo_url">
                                            <img :src="selectedChannel.logo_url" :alt="selectedChannel.name" class="h-full w-full object-contain" />
                                        </template>
                                        <template x-if="!selectedChannel?.logo_url">
                                            <span class="font-mono text-sm font-extrabold text-ink"
                                                x-text="selectedChannel?.bank_code || (selectedChannel?.type === 'QRIS' ? 'QRIS' : (selectedChannel?.type === 'EWALLET' ? 'WALLET' : 'BANK'))">
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex min-w-0 flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-accent"
                                                x-text="selectedChannel?.type === 'QRIS' ? 'QRIS Standar Nasional Indonesia (ASPI)' : (selectedChannel?.type === 'EWALLET' ? 'Dompet Digital Bisnis Resmi' : 'Rekening Giro / Virtual Account Resmi')">
                                            </span>
                                            <span class="rounded bg-accent/20 px-1.5 py-0.2 text-[9px] font-semibold text-accent">TERVERIFIKASI</span>
                                        </div>
                                        <p class="font-mono text-base font-bold tracking-wide text-white"
                                            x-text="selectedChannel?.name + ' : ' + selectedChannel?.account_number">
                                        </p>
                                        <p class="text-xs text-line-soft">
                                            a.n. <strong class="text-white" x-text="selectedChannel?.account_name"></strong>
                                        </p>
                                    </div>
                                </div>

                                <button type="button" @click="copyAccount()"
                                    class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-accent px-4 py-2 font-sans text-xs font-bold uppercase tracking-wider text-ink transition-transform hover:scale-105 hover:bg-accent-deep shadow-sub">
                                    <x-gpa.icon name="copy" class="h-3.5 w-3.5 text-ink" />
                                    <span x-text="selectedChannel?.type === 'QRIS' ? 'Salin NMID' : (selectedChannel?.type === 'EWALLET' ? 'Salin No. HP/ID' : 'Salin Rekening')"></span>
                                </button>
                            </div>

                            {{-- Pratinjau Barcode jika memilih QRIS --}}
                            <div x-show="selectedChannel?.type === 'QRIS'" class="mt-1 flex flex-col sm:flex-row items-center gap-4 rounded-xl bg-surface/10 p-3.5 border border-accent/20">
                                <div class="group relative h-28 w-28 shrink-0 rounded-xl bg-white p-1.5 flex items-center justify-center shadow-lg border border-accent/40 cursor-pointer overflow-hidden transition-all hover:scale-105"
                                    @click="fullQrisModal = (selectedChannel?.image_url || '/storage/payment-channels/qris-gpa-default.png')">
                                    <img :src="selectedChannel?.image_url || '/storage/payment-channels/qris-gpa-default.png'" 
                                        onerror="this.onerror=null;this.src='/storage/payment-channels/qris-gpa-default.png'"
                                        alt="Barcode QRIS Resmi GPA" class="h-full w-full object-contain rounded-lg">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-xl">
                                        <span class="text-[10px] font-bold text-white bg-black/70 px-2 py-1 rounded">Perbesar</span>
                                    </div>
                                </div>

                                <div class="flex flex-col text-xs min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-accent">Scan QRIS GPA via M-Banking / Dompet Digital:</p>
                                        <span class="rounded bg-accent/20 px-1.5 py-0.2 text-[9px] font-semibold text-accent">INSTAN</span>
                                    </div>
                                    <p class="mt-0.5 text-line-soft text-[11px] leading-relaxed">
                                        Pindai QRIS via BCA Mobile, Livin Mandiri, BRImo, BNI Mobile, BSI Mobile, GoPay, OVO, ShopeePay, atau DANA.
                                    </p>
                                    <p class="mt-1 font-mono text-[11px] text-accent font-semibold" x-text="'NMID: ' + selectedChannel?.account_number"></p>
                                    
                                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                        <button type="button" @click="fullQrisModal = (selectedChannel?.image_url || '/storage/payment-channels/qris-gpa-default.png')"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 bg-white/10 px-3 py-1.5 font-sans text-xs font-semibold text-white hover:bg-white/20 transition-all">
                                            <x-gpa.icon name="eye" class="h-3.5 w-3.5 text-white" />
                                            Perbesar Barcode
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <p class="text-[11px] leading-relaxed text-line-soft" x-text="selectedChannel?.instructions || 'Sertakan nomor referensi pesanan di berita transfer perbankan.'"></p>
                        </div>
                    </div>

                    {{-- MODE 3: Tunai / Cash di Kasir / Meja Sekretariat --}}
                    <div x-cloak x-show="paymentCategory === 'cash'" class="space-y-4">
                        <div class="rounded-2xl border border-accent/40 bg-gradient-to-br from-brand/95 via-brand to-brand-deep p-5 text-white shadow-sub">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-brand shadow-sub">
                                        <x-gpa.icon name="banknote" class="h-5 w-5 text-brand" />
                                    </span>
                                    <div>
                                        <h3 class="font-sans text-sm font-bold text-white">Kasir Tunai / Meja Sekretariat GPA</h3>
                                        <p class="text-[11px] text-white/70">Penerimaan fisik langsung di loket / packhouse saat pesanan dicatat</p>
                                    </div>
                                </div>
                                <span class="rounded-full bg-accent/20 px-2.5 py-1 text-[10px] font-bold tracking-wider text-accent uppercase">
                                    Kas Masuk: Lunas H+0
                                </span>
                            </div>

                            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                                {{-- Total Tagihan --}}
                                <div class="rounded-xl bg-white/10 p-3.5 border border-white/10 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[11px] font-semibold text-white/70 uppercase tracking-wider block">Total Tagihan</span>
                                        <span class="mt-1.5 font-mono text-2xl font-black text-white block" x-text="rupiah(total)"></span>
                                    </div>
                                    <span class="text-[10px] text-white/60 mt-2 block">Termasuk ongkir flat (Rp 75.000)</span>
                                </div>

                                {{-- Input Nominal Diterima --}}
                                <div class="rounded-xl bg-white/15 p-3.5 border border-accent/60 ring-2 ring-accent/30 sm:col-span-2">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label for="cash-received-input" class="text-[11px] font-bold text-accent uppercase tracking-wider flex items-center gap-1.5">
                                            <x-gpa.icon name="calculator" class="h-3.5 w-3.5" />
                                            <span>Uang Tunai Diterima dari Pembeli:</span>
                                        </label>
                                        <button type="button" @click="setCashExact()"
                                            class="rounded bg-accent px-2 py-0.5 text-[10px] font-bold text-brand uppercase hover:bg-accent-deep transition-colors shadow-xs">
                                            Uang Pas
                                        </button>
                                    </div>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 font-mono text-sm font-bold text-white/70">Rp</span>
                                        <input id="cash-received-input" type="number" min="0" step="1000" x-model.number="cashAmount"
                                            class="w-full rounded-lg border border-white/20 bg-black/30 py-2.5 pl-10 pr-3 font-mono text-xl font-bold text-white placeholder-white/40 focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                                            placeholder="0" />
                                    </div>

                                    {{-- Preset Cepat Nominal Uang Pecahan --}}
                                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                        <span class="text-[10px] text-white/60 mr-1">Preset Cepat:</span>
                                        <button type="button" @click="setCashExact()"
                                            class="rounded-md border border-white/20 bg-white/10 px-2 py-0.5 font-mono text-[11px] font-medium text-white hover:bg-white/20 transition-colors">
                                            Uang Pas
                                        </button>
                                        <button type="button" @click="roundCashUp(50000)"
                                            class="rounded-md border border-white/20 bg-white/10 px-2 py-0.5 font-mono text-[11px] font-medium text-white hover:bg-white/20 transition-colors">
                                            Bulatkan 50rb
                                        </button>
                                        <button type="button" @click="roundCashUp(100000)"
                                            class="rounded-md border border-white/20 bg-white/10 px-2 py-0.5 font-mono text-[11px] font-medium text-white hover:bg-white/20 transition-colors">
                                            Bulatkan 100rb
                                        </button>
                                        <button type="button" @click="addCashPreset(50000)"
                                            class="rounded-md border border-white/20 bg-white/10 px-2 py-0.5 font-mono text-[11px] font-medium text-accent hover:bg-white/20 transition-colors">
                                            +50.000
                                        </button>
                                        <button type="button" @click="addCashPreset(100000)"
                                            class="rounded-md border border-white/20 bg-white/10 px-2 py-0.5 font-mono text-[11px] font-medium text-accent hover:bg-white/20 transition-colors">
                                            +100.000
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Panel Perhitungan Kembalian / Kekurangan --}}
                            <div class="mt-4 pt-3.5 border-t border-white/10">
                                <template x-if="total <= 0">
                                    <div class="flex items-center gap-2 rounded-xl bg-white/10 p-3 text-xs text-white/80">
                                        <x-gpa.icon name="info" class="h-4 w-4 text-accent shrink-0" />
                                        <span>Tambahkan komoditas pada bagian 03 di atas untuk menghitung total tagihan kasir.</span>
                                    </div>
                                </template>

                                <template x-if="total > 0 && isCashSufficient">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl bg-success-deep/30 border border-success-ink/40 p-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent text-brand">
                                                <x-gpa.icon name="check-circle" class="h-6 w-6 text-brand" />
                                            </span>
                                            <div>
                                                <span class="text-xs uppercase tracking-wider font-extrabold text-accent">
                                                    Uang Tunai Diterima Cukup
                                                </span>
                                                <p class="font-mono text-xs text-white/90 mt-0.5">
                                                    Diterima: <strong class="text-white" x-text="rupiah(cashReceived)"></strong> • Tagihan: <span x-text="rupiah(total)"></span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="text-left sm:text-right bg-black/25 px-4 py-2.5 rounded-xl border border-white/15">
                                            <span class="text-[10px] uppercase tracking-wider text-accent font-bold block">
                                                Uang Kembalian Pembeli:
                                            </span>
                                            <span class="font-mono text-2xl font-black text-accent" x-text="rupiah(cashChange)"></span>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="total > 0 && !isCashSufficient">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl bg-danger-soft/20 border border-danger/40 p-4">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger text-white">
                                                <x-gpa.icon name="alert-triangle" class="h-5 w-5" />
                                            </span>
                                            <div>
                                                <span class="text-xs uppercase tracking-wider font-bold text-danger-ink">
                                                    Uang Tunai Masih Kurang
                                                </span>
                                                <p class="text-[11px] text-white/80 mt-0.5">
                                                    Nominal yang diserahkan pembeli belum mencukupi total tagihan.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="text-left sm:text-right bg-black/30 px-3.5 py-2 rounded-xl border border-danger/30">
                                            <span class="text-[10px] uppercase tracking-wider text-danger-ink font-bold block">
                                                Kekurangan Pembayaran:
                                            </span>
                                            <span class="font-mono text-lg font-bold text-danger-ink" x-text="'- ' + rupiah(cashShortage)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Catatan / Tanda Terima Kasir --}}
                            <div class="mt-3.5 flex items-center gap-2 text-xs text-white/80">
                                <x-gpa.icon name="receipt" class="h-4 w-4 text-accent shrink-0" />
                                <span>Pencatatan kasir akan otomatis disahkan dengan status <strong>LUNAS</strong> dan bukti bayar dicetak pada faktur Surat Jalan.</span>
                            </div>
                        </div>
                    </div>

                    {{-- MODE 2: Termin Khusus B2B / COD / SK Direktur --}}
                    <div x-cloak x-show="paymentCategory === 'b2b'" class="space-y-4">
                        <div class="grid gap-2.5 sm:grid-cols-3">
                            <label
                                class="flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 transition-colors"
                                :class="paymentMethod === 'cod' ? 'border-brand bg-surface-shell ring-1 ring-brand' : 'border-line-faint bg-surface hover:border-brand'">
                                <input type="radio" name="payment-method-b2b" value="cod"
                                    @click="selectB2bMethod('cod')" :checked="paymentMethod === 'cod'" class="gpa-check gpa-check--radio mt-0.5">
                                <span class="min-w-0">
                                    <span class="block gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                        COD (Tunai di Tempat)
                                    </span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet">Pelunasan saat armada serah terima</span>
                                </span>
                            </label>

                            <label
                                class="flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 transition-colors"
                                :class="clientMode === 'new'
                                    ? 'opacity-50 cursor-not-allowed bg-surface-muted border-line-faint'
                                    : (paymentMethod === 'top14' ? 'border-brand bg-surface-shell ring-1 ring-brand' : 'border-line-faint bg-surface hover:border-brand')">
                                <input type="radio" name="payment-method-b2b" value="top14" :disabled="clientMode === 'new'"
                                    @click="if (clientMode !== 'new') selectB2bMethod('top14')" :checked="paymentMethod === 'top14'" class="gpa-check gpa-check--radio mt-0.5">
                                <span class="min-w-0">
                                    <span class="block gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                        Tempo 14 Hari (TOP)
                                    </span>
                                    <span class="mt-0.5 block gpa-note" :class="clientMode === 'new' ? 'text-amber-800 font-semibold' : 'text-ink-quiet'"
                                        x-text="clientMode === 'new' ? 'Nonaktif untuk Pembeli Baru' : 'Khusus mitra kontrak aktif plafon'">
                                    </span>
                                </span>
                            </label>

                            <label
                                class="flex cursor-pointer items-start gap-2.5 rounded-xl border p-3 transition-colors"
                                :class="paymentMethod === 'custom' ? 'border-brand bg-surface-shell ring-1 ring-brand' : 'border-line-faint bg-surface hover:border-brand'">
                                <input type="radio" name="payment-method-b2b" value="custom"
                                    @click="selectB2bMethod('custom')" :checked="paymentMethod === 'custom'" class="gpa-check gpa-check--radio mt-0.5">
                                <span class="min-w-0">
                                    <span class="block gpa-meta-lg font-bold tracking-[0.07em] text-ink">
                                        SK Khusus Direktur
                                    </span>
                                    <span class="mt-0.5 block gpa-note text-ink-quiet">Disposisi &amp; kesepakatan direksi</span>
                                </span>
                            </label>
                        </div>

                        {{-- Form Keterangan Termin Kustom / SK Direktur --}}
                        <div x-cloak x-show="paymentMethod === 'custom'" class="rounded-xl border border-accent-deep/30 bg-accent/20 p-3.5">
                            <div class="flex items-center gap-2">
                                <x-gpa.icon name="shield" class="h-4 w-4 text-brand" />
                                <label class="gpa-label text-brand" for="payment-custom-note">Otorisasi &amp; Keterangan Termin Khusus (SK Direktur)</label>
                            </div>
                            <p class="mt-1 gpa-note text-ink-body">
                                Masukkan nomor memo / disposisi persetujuan Direktur Utama atas termin pembayaran khusus ini.
                            </p>
                            <input id="payment-custom-note" type="text" x-model="paymentCustomNote"
                                placeholder="Contoh: Tempo 30 Hari - Disposisi Memo Direksi No. 042/GPA/DIR/X/2026"
                                class="gpa-control mt-2 border-brand-line bg-surface" spellcheck="false">
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Berkas Bukti Pembayaran & Rekonsiliasi --}}
                <div class="rounded-xl border border-line-faint bg-surface-shell p-4 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-line-soft pb-2.5">
                            <p class="gpa-label" x-text="paymentCategory === 'cash' ? 'Lampiran Kuitansi / Bukti Fisik Kasir (Opsional)' : 'Unggah Bukti Transfer / Chat Pemesanan'"></p>
                            <span class="gpa-note text-ink-quiet">Maks. 5 MB (JPG/PNG/PDF)</span>
                        </div>

                        {{-- Area Drag & Drop / Pilih Berkas --}}
                        <div class="mt-3 relative rounded-xl border-2 border-dashed border-line-board bg-surface p-4 text-center transition-colors"
                            :class="dragActive ? 'border-brand bg-accent/20' : 'hover:border-brand'"
                            @dragover.prevent="onDragOver()" @dragenter.prevent="onDragOver()"
                            @dragleave.prevent="dragActive = false" @drop.prevent="onDrop($event)">
                            <input type="file" x-ref="proofFileInput" @change="onFileInput($event)"
                                accept="image/jpeg,image/png,image/jpg,application/pdf" class="hidden">

                            <div x-show="!evidence.file" class="flex flex-col items-center py-2">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-accent text-ink">
                                    <x-gpa.icon name="upload" class="h-5 w-5" />
                                </span>
                                <p class="mt-2 text-xs font-bold text-ink">Tarik berkas bukti bayar ke sini</p>
                                <p class="mt-0.5 text-[11px] text-ink-quiet">atau klik tombol di bawah untuk memilih file</p>
                                <button type="button" @click="$refs.proofFileInput.click()"
                                    class="mt-2.5 inline-flex items-center gap-1.5 rounded-lg bg-brand px-3 py-1.5 text-xs font-bold text-accent transition-colors hover:bg-brand-hover">
                                    <x-gpa.icon name="upload" class="h-3.5 w-3.5" />
                                    <span>Pilih Berkas Bukti</span>
                                </button>
                            </div>

                            {{-- Pratinjau Berkas Terunggah --}}
                            <div x-show="evidence.file" class="flex flex-col gap-2 py-1 text-left">
                                <div class="flex items-start gap-2.5">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-brand text-accent">
                                        <x-gpa.icon name="file-text" class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate font-mono text-xs font-bold text-ink" x-text="evidence.file"></p>
                                        <p class="gpa-note text-ink-quiet">
                                            <span x-text="evidence.size"></span> • <span x-text="evidence.hash"></span>
                                        </p>
                                        <span class="mt-1 inline-flex items-center gap-1 rounded bg-success-soft px-1.5 py-0.5 gpa-micro-bold text-success-ink"
                                            x-text="evidence.hash_note"></span>
                                    </div>
                                </div>

                                <div class="mt-2 flex flex-wrap items-center gap-2 border-t border-line-soft pt-2">
                                    <button type="button" @click="$refs.proofFileInput.click()"
                                        class="inline-flex h-7 items-center gap-1 rounded-lg bg-accent px-2.5 text-xs font-bold text-ink hover:bg-accent-deep">
                                        <x-gpa.icon name="upload" class="h-3 w-3" />
                                        Ganti Berkas
                                    </button>
                                    <button type="button" @click="viewEvidence()"
                                        class="inline-flex h-7 items-center gap-1 rounded-lg bg-brand px-2.5 text-xs font-bold text-accent hover:bg-brand-hover">
                                        <x-gpa.icon name="eye" class="h-3 w-3" />
                                        Lihat Pratinjau
                                    </button>
                                    <button type="button" @click="removeEvidence()"
                                        class="inline-flex h-7 items-center gap-1 rounded-lg bg-surface px-2.5 text-xs font-bold text-danger-ink border border-line-board hover:bg-danger-soft">
                                        <x-gpa.icon name="trash" class="h-3 w-3" />
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Metadata Pengirim --}}
                        <div class="mt-3.5 space-y-2.5">
                            <div>
                                <label class="gpa-label" for="manual-sender-bank">Bank / Dompet Pengirim:</label>
                                <input id="manual-sender-bank" type="text" x-model="senderBank"
                                    placeholder="Contoh: Bank BCA / Mandiri / QRIS" class="gpa-control mt-1">
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2">
                                <div>
                                    <label class="gpa-label" for="manual-sender-acc">Rekening / No. HP:</label>
                                    <input id="manual-sender-acc" type="text" x-model="senderAccount"
                                        placeholder="No rekening atau no HP..." class="gpa-control mt-1 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="gpa-label" for="manual-ref-pay">No. Referensi Bank:</label>
                                    <input id="manual-ref-pay" type="text" x-model="paymentReference"
                                        placeholder="Contoh: TRX-9281..." class="gpa-control mt-1 font-mono text-xs">
                                </div>
                            </div>
                        </div>
                    </div>

                    <span class="gpa-hint mt-3 block border-t border-line-soft pt-2">
                        Bukti pembayaran akan disimpan di vault sistem dan dapat direkonsiliasi langsung oleh Direktur dan Kasir.
                    </span>
                </div>
            </div>
        </section>

        {{-- Ringkasan & Aksi --}}
        <section class="gpa-panel overflow-hidden">
            <div class="space-y-5 px-6 py-5">
                {{-- Baris 1: Kartu Metrik Ringkasan (Luas, Rapi, & Elegan) --}}
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <p class="gpa-eyebrow">Ringkasan Order // Belum Termasuk PPN</p>
                        <span class="gpa-note text-ink-quiet font-mono text-[11px]">Formula: Subtotal Komoditas + Ongkos Kirim</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5 lg:grid-cols-4">
                        <div class="flex flex-col justify-between rounded-xl border border-line-soft bg-surface-shell p-4 shadow-xs">
                            <div class="flex items-center justify-between">
                                <span class="gpa-micro font-bold uppercase tracking-wider text-ink-quiet">Total Berat</span>
                                <x-gpa.icon name="scale" class="h-4 w-4 text-ink-quiet" />
                            </div>
                            <p class="mt-2 text-xl font-bold leading-7 text-ink" x-text="kg(totalWeight)"></p>
                            <span class="mt-1 gpa-note text-ink-quiet">Kapasitas muatan panen</span>
                        </div>

                        <div class="flex flex-col justify-between rounded-xl border border-line-soft bg-surface-shell p-4 shadow-xs">
                            <div class="flex items-center justify-between">
                                <span class="gpa-micro font-bold uppercase tracking-wider text-ink-quiet">Subtotal Sayur</span>
                                <x-gpa.icon name="shopping-bag" class="h-4 w-4 text-ink-quiet" />
                            </div>
                            <p class="mt-2 text-xl font-bold leading-7 text-ink whitespace-nowrap" x-text="rupiah(subtotal)"></p>
                            <span class="mt-1 gpa-note text-ink-quiet" x-text="rows.length + ' komoditas dipilih'"></span>
                        </div>

                        <div class="flex flex-col justify-between rounded-xl border border-line-soft bg-surface-shell p-4 shadow-xs">
                            <div class="flex items-center justify-between">
                                <span class="gpa-micro font-bold uppercase tracking-wider text-ink-quiet">Ongkos Kirim</span>
                                <x-gpa.icon name="truck" class="h-4 w-4 text-ink-quiet" />
                            </div>
                            <p class="mt-2 text-xl font-bold leading-7 text-ink whitespace-nowrap" x-text="rupiah(fee)"></p>
                            <span class="mt-1 gpa-note font-semibold text-success-deep">Tarif Bebas Kirim GPA</span>
                        </div>

                        <div class="flex flex-col justify-between rounded-xl border border-brand bg-brand p-4 text-white shadow-sub">
                            <div class="flex items-center justify-between">
                                <span class="gpa-micro font-bold uppercase tracking-wider text-accent">Total Estimasi</span>
                                <x-gpa.icon name="wallet" class="h-4 w-4 text-accent" />
                            </div>
                            <p class="mt-2 font-mono text-2xl font-extrabold leading-tight text-accent whitespace-nowrap" x-text="rupiah(total)"></p>
                            <span class="mt-1 font-sans text-xs text-white/80">Tagihan siap ditagihkan</span>
                        </div>
                    </div>
                </div>

                {{-- Baris 2: Toolbar Aksi (Leluasa, Tidak Berdesakan) --}}
                <div class="flex flex-col gap-3 border-t border-line-soft pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2 text-xs text-ink-quiet">
                        <x-gpa.icon name="shield-check" class="h-4 w-4 shrink-0 text-success-deep" />
                        <span>Pastikan ketersediaan stok panen dan nominal kasir telah sesuai sebelum verifikasi.</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5 sm:justify-end">
                        <button type="button" @click="cancel()" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-line-board bg-surface px-4 text-xs font-bold text-ink-body transition-colors hover:bg-surface-muted disabled:opacity-50">
                            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                            <span>Batal / Kembali</span>
                        </button>

                        <button type="button" @click="saveDraft()" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-brand-line bg-surface px-4 text-xs font-bold text-brand transition-colors hover:bg-surface-muted disabled:opacity-50">
                            <x-gpa.icon name="save" class="h-3.5 w-3.5" />
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Draf PO'"></span>
                        </button>

                        {{-- Tombol Khusus Kasir: Simpan & Cetak Struk Kasir Langsung --}}
                        <button type="button" @click="saveAndPrintReceipt()" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-accent bg-accent/30 px-4 text-xs font-bold text-success-deep shadow-xs transition-all hover:bg-accent/50 disabled:opacity-50">
                            <x-gpa.icon name="printer" class="h-4 w-4 text-success-deep" />
                            <span>Simpan &amp; Cetak Struk</span>
                        </button>

                        <button type="button" @click="verify()" :disabled="isSubmitting"
                            class="inline-flex h-10 items-center gap-1.5 rounded-lg bg-brand px-5 text-xs font-bold text-accent shadow-sub transition-colors hover:bg-brand-hover disabled:opacity-50">
                            <span x-text="isSubmitting ? 'Memproses Pesanan...' : 'Verifikasi & Teruskan'"></span>
                            <x-gpa.icon name="arrow-right" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Modal Sukses Simpan & Opsi Cetak Struk Kasir / Faktur --}}
        <div x-cloak x-show="savedOrderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            @keydown.escape.window="savedOrderModal = null">
            <div class="relative max-w-md w-full rounded-2xl bg-white p-6 shadow-2xl flex flex-col gap-4 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-accent text-brand shadow-sub">
                    <x-gpa.icon name="check-circle" class="h-8 w-8 text-brand" />
                </div>

                <div>
                    <span class="rounded bg-accent px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-widest text-ink">
                        Pesanan Berhasil Diverifikasi
                    </span>
                    <h3 class="mt-2 font-sans text-lg font-extrabold text-ink">
                        Diteruskan ke Penerbitan Surat Jalan
                    </h3>
                    <p class="font-mono text-xs font-bold text-success-deep mt-1" x-text="savedOrderModal?.order_number"></p>
                </div>

                <div class="rounded-xl border border-line-soft bg-surface-shell p-3 text-left space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-ink-quiet">Pelanggan:</span>
                        <span class="font-bold text-ink" x-text="savedOrderModal?.client_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-quiet">Total Tagihan:</span>
                        <span class="font-mono font-extrabold text-brand text-sm" x-text="rupiah(savedOrderModal?.grand_total || 0)"></span>
                    </div>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 pt-1">
                    <a :href="savedOrderModal?.receipt_url" target="_blank"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-brand px-4 py-2.5 text-xs font-bold text-accent shadow-sub hover:bg-brand-hover transition-colors">
                        <x-gpa.icon name="printer" class="h-4 w-4" />
                        Cetak Struk Kasir
                    </a>
                    <a :href="savedOrderModal?.invoice_url" target="_blank"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-line-board bg-surface px-4 py-2.5 text-xs font-bold text-ink hover:bg-surface-muted transition-colors">
                        <x-gpa.icon name="file-text" class="h-4 w-4" />
                        Cetak Faktur (A4)
                    </a>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-line-soft pt-3 text-xs">
                    <button type="button" @click="window.location.reload()"
                        class="font-semibold text-ink-body hover:text-ink">
                        + Input Pesanan Baru
                    </button>
                    <a :href="savedOrderModal?.redirect_url || '{{ route('sekretaris.surat-jalan') }}'"
                        class="font-bold text-brand hover:underline">
                        Buka Penerbitan Surat Jalan &rarr;
                    </a>
                </div>
            </div>
        </div>

        {{-- Modal Pratinjau Barcode QRIS --}}
        <div x-cloak x-show="fullQrisModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            @keydown.escape.window="fullQrisModal = null">
            <div class="relative max-w-sm w-full rounded-2xl bg-white p-5 text-center shadow-2xl flex flex-col items-center gap-3">
                <button type="button" @click="fullQrisModal = null"
                    class="absolute right-3 top-3 rounded-full bg-surface-shell p-1.5 text-ink hover:bg-surface-muted"
                    aria-label="Tutup modal QRIS">
                    <x-gpa.icon name="x" class="h-5 w-5" />
                </button>
                <h3 class="font-sans text-base font-bold text-ink">Barcode QRIS Resmi GPA</h3>
                <p class="text-xs text-ink-quiet">Pindai kode QR ini menggunakan BCA Mobile, Livin Mandiri, BRImo, GoPay, OVO, atau DANA.</p>
                <div class="h-64 w-64 rounded-xl border border-line-soft p-2 bg-white flex items-center justify-center">
                    <img :src="fullQrisModal" alt="Barcode QRIS Full" class="h-full w-full object-contain">
                </div>
                <div class="flex items-center gap-2 w-full pt-1">
                    <a :href="fullQrisModal" download="QRIS-AgroOrder-GPA.png"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand px-3 py-2 text-xs font-bold text-accent hover:bg-brand-hover">
                        <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                        Unduh QRIS
                    </a>
                    <button type="button" @click="fullQrisModal = null"
                        class="rounded-lg border border-line-board bg-surface px-4 py-2 text-xs font-semibold text-ink hover:bg-surface-muted">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Pratinjau Bukti Pembayaran --}}
        <div x-cloak x-show="evidenceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
            @keydown.escape.window="evidenceModal = false">
            <div class="relative max-w-lg w-full rounded-2xl bg-white p-5 text-center shadow-2xl flex flex-col items-center gap-3">
                <button type="button" @click="evidenceModal = false"
                    class="absolute right-3 top-3 rounded-full bg-surface-shell p-1.5 text-ink hover:bg-surface-muted"
                    aria-label="Tutup modal bukti">
                    <x-gpa.icon name="x" class="h-5 w-5" />
                </button>
                <h3 class="font-sans text-base font-bold text-ink" x-text="evidence.file || 'Pratinjau Bukti Transfer'"></h3>
                <div class="max-h-96 w-full rounded-xl border border-line-soft p-2 bg-surface flex items-center justify-center overflow-hidden">
                    <template x-if="evidence.previewUrl">
                        <img :src="evidence.previewUrl" alt="Pratinjau Bukti" class="max-h-80 w-auto object-contain rounded">
                    </template>
                </div>
                <button type="button" @click="evidenceModal = false"
                    class="rounded-lg bg-brand px-5 py-2 text-xs font-semibold text-accent hover:bg-brand-hover">
                    Tutup Pratinjau
                </button>
            </div>
        </div>
    </div>
@endsection