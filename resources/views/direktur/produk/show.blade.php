@extends('layouts.direktur')

@section('title', 'Rincian Komoditas: ' . $product->name . ' // Direktur AgroOrder GPA')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    {{-- Breadcrumb & Aksi Atas --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('direktur.produk.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-surface-shell">
            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
            Kembali ke Katalog Komoditas
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('direktur.produk.edit', $product->id) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-ink px-3.5 py-1.5 text-xs font-bold text-accent shadow-sub hover:bg-brand-deep transition">
                <x-gpa.icon name="settings" class="h-3.5 w-3.5" />
                Ubah Data Komoditas
            </a>
        </div>
    </div>

    {{-- Kartu Profil Komoditas Utama --}}
    <section class="rounded-2xl border border-line-hair bg-surface p-6 shadow-card space-y-6">
        <div class="flex flex-col md:flex-row items-start gap-6">
            {{-- Foto Produk Besar --}}
            <div class="w-full md:w-64 shrink-0 overflow-hidden rounded-xl border border-line-board bg-surface-muted shadow-sub">
                <div class="h-48 w-full overflow-hidden relative">
                    <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}"
                        class="h-full w-full object-cover"
                        x-on:error="$event.target.src = '{{ asset('gpaleaves.png') }}'">
                    <span class="absolute top-2 left-2 rounded bg-brand-deep/90 px-2 py-0.5 gpa-mono-xs font-bold text-accent shadow-sm">
                        {{ $product->sku }}
                    </span>
                    <span class="absolute bottom-2 right-2 rounded px-2 py-0.5 text-[10px] font-bold shadow-sm {{ $product->is_active ? 'bg-accent text-success-ink' : 'bg-red-100 text-red-800' }}">
                        {{ $product->is_active ? 'Katalog Aktif' : 'Non-Aktif' }}
                    </span>
                </div>
            </div>

            {{-- Informasi Pokok --}}
            <div class="flex-1 min-w-0 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line-hair pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-accent px-2 py-0.5 gpa-micro-bold text-brand-deep">
                                {{ $product->grade }}
                            </span>
                            <span class="text-xs text-ink-body font-mono">Satuan: {{ strtoupper($product->unit) }}</span>
                        </div>
                        <h1 class="text-xl font-bold text-ink mt-1">{{ $product->name }}</h1>
                    </div>

                    <div class="text-right">
                        <span class="text-[10px] font-semibold text-ink-body uppercase">Harga Dasar (Franco):</span>
                        <p class="text-xl font-extrabold text-brand-strong">
                            Rp {{ number_format($product->base_price, 0, ',', '.') }}
                            <span class="text-xs font-medium text-ink-body">/ {{ $product->unit }}</span>
                        </p>
                    </div>
                </div>

                {{-- Spesifikasi Teknis --}}
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div class="rounded-lg bg-surface-shell p-2.5 border border-line-faint">
                        <dt class="text-[10px] font-bold text-ink-body uppercase">Min. Order (MOQ)</dt>
                        <dd class="text-sm font-bold text-ink mt-0.5">{{ number_format($product->minimum_order, 0, ',', '.') }} {{ $product->unit }}</dd>
                    </div>

                    <div class="rounded-lg bg-surface-shell p-2.5 border border-line-faint">
                        <dt class="text-[10px] font-bold text-ink-body uppercase">Stok Fisik Siap Kirim</dt>
                        <dd class="text-sm font-bold {{ $availableStock > 0 ? 'text-success-deep' : 'text-danger' }} mt-0.5">
                            {{ number_format($availableStock, 0, ',', '.') }} {{ $product->unit }}
                        </dd>
                    </div>

                    <div class="rounded-lg bg-surface-shell p-2.5 border border-line-faint">
                        <dt class="text-[10px] font-bold text-ink-body uppercase">Total Panen Masuk</dt>
                        <dd class="text-sm font-bold text-ink mt-0.5">{{ number_format($totalHarvested, 0, ',', '.') }} {{ $product->unit }}</dd>
                    </div>
                </dl>

                {{-- Deskripsi Mutu --}}
                <div class="rounded-lg bg-surface-shell p-3 border border-line-faint text-xs">
                    <h3 class="font-bold text-ink mb-1">Standar Mutu &amp; Keterangan Produk:</h3>
                    <p class="text-ink-body leading-relaxed">
                        {{ $product->description ?: 'Komoditas hortikultura segar unggulan binaan Green Pasundan Agriculture bersertifikat standar higienis dan bebas residu pestisida.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Bagian Batch Panen Aktif --}}
    <section class="rounded-2xl border border-line-hair bg-surface p-6 shadow-card space-y-4">
        <header class="flex items-center justify-between border-b border-line-hair pb-3">
            <div>
                <h2 class="text-base font-bold text-ink">Histori Batch Panen Masuk</h2>
                <p class="text-xs text-ink-body">Rincian pasokan dari kebun petani binaan dan buffer stock luar.</p>
            </div>
            <span class="rounded bg-surface-shell px-2.5 py-1 text-xs font-bold text-ink">
                {{ $product->harvestBatches->count() }} Batch Terdata
            </span>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-line-board text-[10px] uppercase text-ink-body font-semibold">
                        <th class="py-2.5 px-3">Kode Batch</th>
                        <th class="py-2.5 px-3">Tanggal Panen</th>
                        <th class="py-2.5 px-3">Asal / Sentra</th>
                        <th class="py-2.5 px-3">Tipe Pasokan</th>
                        <th class="py-2.5 px-3 text-right">Kuantitas Awal</th>
                        <th class="py-2.5 px-3 text-right">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse ($product->harvestBatches as $batch)
                        <tr class="hover:bg-surface-shell/40">
                            <td class="py-2.5 px-3 font-mono font-bold text-ink">{{ $batch->batch_code }}</td>
                            <td class="py-2.5 px-3 text-ink-body">{{ $batch->harvest_date ? \Carbon\Carbon::parse($batch->harvest_date)->format('d M Y') : '-' }}</td>
                            <td class="py-2.5 px-3 text-ink">{{ $batch->origin_location ?? 'Ciwidey / Lembang' }}</td>
                            <td class="py-2.5 px-3">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $batch->source_type === 'PETANI_BINAAN' ? 'bg-accent text-brand-deep' : 'bg-surface-track text-ink-body' }}">
                                    {{ $batch->source_type === 'PETANI_BINAAN' ? 'Petani Binaan' : 'Buffer Luar' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono">{{ number_format($batch->initial_quantity, 0, ',', '.') }} {{ $product->unit }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold {{ $batch->available_quantity > 0 ? 'text-success-deep' : 'text-danger' }}">
                                {{ number_format($batch->available_quantity, 0, ',', '.') }} {{ $product->unit }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-xs text-ink-body">
                                Belum ada batch panen yang tercatat untuk komoditas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Bagian Kontrak Kemitraan Terikat --}}
    <section class="rounded-2xl border border-line-hair bg-surface p-6 shadow-card space-y-4">
        <header class="flex items-center justify-between border-b border-line-hair pb-3">
            <div>
                <h2 class="text-base font-bold text-ink">Kontrak Kemitraan B2B Terikat</h2>
                <p class="text-xs text-ink-body">Daftar klien korporat dengan kuota pasokan terkunci untuk komoditas ini.</p>
            </div>
            <span class="rounded bg-surface-shell px-2.5 py-1 text-xs font-bold text-ink">
                {{ $product->contracts->count() }} Kontrak Terbit
            </span>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-line-board text-[10px] uppercase text-ink-body font-semibold">
                        <th class="py-2.5 px-3">No. Kontrak</th>
                        <th class="py-2.5 px-3">Klien / Buyer</th>
                        <th class="py-2.5 px-3 text-right">Harga Kontrak Terkunci</th>
                        <th class="py-2.5 px-3 text-right">Komitmen Tonase</th>
                        <th class="py-2.5 px-3 text-center">Status Kontrak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse ($product->contracts as $contract)
                        <tr class="hover:bg-surface-shell/40">
                            <td class="py-2.5 px-3 font-mono font-bold text-ink">
                                <a href="{{ route('direktur.kontrak.show', $contract->id) }}" class="text-brand hover:underline">
                                    {{ $contract->contract_number }}
                                </a>
                            </td>
                            <td class="py-2.5 px-3 text-ink">
                                {{ $contract->user?->company_name ?: $contract->user?->name ?: 'Klien GPA' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-ink">
                                Rp {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }} / kg
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-ink">
                                {{ number_format($contract->committed_volume_per_cycle, 0, ',', '.') }} kg / siklus
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="rounded px-2 py-0.5 text-[10px] font-bold {{ $contract->status === 'ACTIVE' ? 'bg-accent text-success-ink' : 'bg-surface-shell text-ink-body' }}">
                                    {{ $contract->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-xs text-ink-body">
                                Belum ada kontrak kemitraan B2B yang mengikat komoditas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
