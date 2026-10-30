@extends('layouts.coordinator')

@section('title', 'Suplai Stok Panen // Koordinator')

@section('content')
    <div class="mx-auto flex max-w-shell flex-col gap-6">
        {{-- Header Halaman --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Suplai Stok Panen</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Penerimaan Pasokan Panen Masuk
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">
                        Pencatatan pasokan masuk dari petani binaan maupun buffer ke dalam sistem stok gudang.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="#form-intake"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="plus" class="h-3.5 w-3.5 shrink-0" />
                        Input Pasokan Baru
                    </a>
                    <a href="{{ route('koordinator.products.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="grid" class="h-3.5 w-3.5 shrink-0" />
                        Master Komoditas
                    </a>
                </div>
            </header>

            {{-- Ringkasan Metrik Gudang --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">TOTAL STOK FISIK</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($totalStock, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">Total di gudang GPA</p>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">STOK PETANI BINAAN</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($totalBinaan, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">Pasokan kemitraan inti</p>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">STOK BUFFER LUAR</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($totalBuffer, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">Penyangga kekurangan</p>
                    </div>
                </article>

                <article class="flex h-full flex-col justify-between gap-4 rounded-2xl bg-surface p-5 shadow-card">
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="gpa-micro-bold text-ink-body">ALOKASI TERIKAT PO</h3>
                        </div>
                        <p class="gpa-figure text-[22px] leading-7 text-ink">{{ number_format($totalReserved, 1, ',', '.') }} <span class="text-base font-semibold">kg</span></p>
                        <p class="text-[11px] leading-4 text-ink-body">{{ $activePoCount }} pesanan antrean masuk</p>
                    </div>
                </article>
            </div>
        </section>

        {{-- SECTION 1: Formulir Input Pasokan Panen --}}
        <section id="form-intake" class="rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Formulir Input Pasokan Panen</h2>
                    <p class="mt-1 text-xs text-ink-body">Mencatat lot panen baru ke dalam sistem penerimaan stok gudang</p>
                </div>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">BUKU STOK PANEN</span>
            </header>

            <form method="POST" action="{{ route('koordinator.stock.store') }}" class="px-6 py-5 space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <label for="product_id" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Pilih Komoditas <span class="text-red-600">*</span></label>
                        <select id="product_id" name="product_id" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="">-- Pilih Komoditas --</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}" {{ request('product_id') == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->name }} ({{ $prod->grade ?: 'Grade A' }}) - {{ $prod->sku }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="source_type" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Tipe Sumber Pasokan <span class="text-red-600">*</span></label>
                        <select id="source_type" name="source_type" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="PETANI_BINAAN">Petani Binaan Inti</option>
                            <option value="BUFFER_LUAR">Buffer Luar</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="supplier_name" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kelompok Tani / Supplier <span class="text-red-600">*</span></label>
                        <input type="text" id="supplier_name" name="supplier_name" list="suppliers-list" placeholder="Contoh: Poktan Sukamaju" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        <datalist id="suppliers-list">
                            @foreach ($distinctSuppliers as $sup)
                                <option value="{{ $sup }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="flex flex-col gap-1.5">
                        <label for="batch_date" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Tanggal Panen <span class="text-red-600">*</span></label>
                        <input type="date" id="batch_date" name="batch_date" value="{{ date('Y-m-d') }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="initial_quantity" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kuantitas Masuk (kg) <span class="text-red-600">*</span></label>
                        <input type="number" id="initial_quantity" name="initial_quantity" min="0.1" step="any" placeholder="Contoh: 250" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="grade" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Hasil Uji Mutu (Grade)</label>
                        <select id="grade" name="grade"
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="Grade A">Grade A</option>
                            <option value="Grade B">Grade B</option>
                            <option value="Grade C">Grade C</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="qc_notes" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Catatan Pemeriksaan Mutu (QC)</label>
                    <input type="text" id="qc_notes" name="qc_notes" placeholder="Contoh: Lolos sortir manual, kadar air optimal"
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                </div>

                <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-board/60 pt-4">
                    <button type="reset"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        Reset
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                        Simpan Pasokan Panen
                    </button>
                </footer>
            </form>
        </section>

        {{-- SECTION 2: Kesiapan Stok & Alokasi PO --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Kesiapan Stok Komoditas &amp; Alokasi PO</h2>
                    <p class="mt-1 text-xs text-ink-body">Perbandingan stok fisik gudang terhadap alokasi pesanan klien</p>
                </div>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ count($commodities) }} Komoditas
                </span>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Komoditas</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Grade</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Pasokan Binaan</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Buffer Siaga</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Total Stok Fisik</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Alokasi PO</th>
                            <th scope="col" class="px-3 py-3 text-right text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Stok Bebas</th>
                            <th scope="col" class="px-3 py-3 text-center text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($commodities as $c)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                <td class="px-3 py-3.5">
                                    <p class="text-[12px] font-semibold leading-5 text-ink">{{ $c['name'] }}</p>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="inline-block rounded bg-surface-shell px-1.5 py-0.5 font-mono text-[10px] font-semibold leading-[15px] text-ink outline outline-1 -outline-offset-1 outline-line-board">{{ $c['grade'] }}</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[11px] leading-5 text-ink">{{ number_format($c['tiles'][0]['value'] ?? 0, 1, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[11px] leading-5 text-ink-body">{{ number_format($c['tiles'][1]['value'] ?? 0, 1, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 text-ink">{{ number_format($c['total'], 1, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[11px] leading-5 text-ink-body">{{ number_format($c['tiles'][2]['value'] ?? 0, 1, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-right">
                                    <span class="font-mono text-[12px] font-semibold leading-5 {{ $c['rtp'] < 0 ? 'text-red-700' : 'text-ink' }}">{{ number_format($c['rtp'], 1, ',', '.') }} kg</span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    @if ($c['rtp'] < 0)
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-red-50 text-red-700 outline outline-1 -outline-offset-1 outline-red-200">DEFISIT</span>
                                    @elseif ($c['utilization'] > 80)
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">KETAT ({{ $c['utilization'] }}%)</span>
                                    @else
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-accent text-ink outline outline-1 -outline-offset-1 outline-success-deep">AMAN</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada komoditas aktif</p>
                                        <p class="text-xs text-ink-body">Tambahkan komoditas pada menu Manajemen Komoditas.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- SECTION 3: Log Riwayat Batch Panen --}}
        <section class="flex flex-col gap-3 rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-6 py-4">
                <div class="min-w-0">
                    <h2 class="gpa-section-title text-ink">Log Pasokan Panen Masuk Terbaru</h2>
                    <p class="mt-1 text-xs text-ink-body">Riwayat batch panen terakhir</p>
                </div>
                <span class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold leading-[15px] gpa-meta bg-surface-track text-ink outline outline-1 -outline-offset-1 outline-line-board">
                    {{ count($logItems) }} Record Terakhir
                </span>
            </header>

            <div class="gpa-scroll-x overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="border-b border-line-board">
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kode Lot</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Waktu Pencatatan</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Rincian Pasokan Masuk</th>
                            <th scope="col" class="px-3 py-3 text-left text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Catatan Mutu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line-hair">
                        @forelse ($logItems as $item)
                            <tr class="align-top transition-colors hover:bg-surface-shell/50">
                                <td class="px-3 py-3.5">
                                    <span class="font-mono text-[11px] font-semibold leading-5 text-ink">{{ $item['lot'] }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="text-[11px] leading-4 text-ink-body">{{ $item['time'] }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="text-[12px] font-semibold leading-5 text-ink">{{ $item['entry'] }}</span>
                                </td>
                                <td class="px-3 py-3.5">
                                    <span class="text-[11px] leading-4 text-ink-body">{{ $item['note'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-10 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center gap-2">
                                        <x-gpa.icon name="package" class="h-7 w-7 text-ink-body" />
                                        <p class="text-sm font-semibold text-ink">Belum ada log pasokan panen</p>
                                        <p class="text-xs text-ink-body">Gunakan formulir di atas untuk mencatat batch panen pertama.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

