@extends('layouts.coordinator')

@section('title', 'Edit Komoditas // Koordinator')

@section('content')
    <div class="mx-auto flex max-w-4xl flex-col gap-6">
        {{-- Header --}}
        <section class="flex flex-col gap-4">
            <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-6 shadow-card">
                <div class="min-w-0 space-y-1">
                    <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Manajemen Komoditas</p>
                    <h1 class="font-sans text-2xl font-bold leading-8 tracking-[-0.01em] text-ink xl:text-[28px]">
                        Edit Komoditas
                    </h1>
                    <p class="text-xs text-ink-body max-w-2xl">
                        Perbarui spesifikasi, harga, gambar, dan status tayang komoditas di katalog klien.
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <a href="{{ route('koordinator.products.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                        Kembali
                    </a>
                    <a href="{{ route('koordinator.products.show', $product) }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        <x-gpa.icon name="eye" class="h-3.5 w-3.5 shrink-0" />
                        Lihat Detail
                    </a>
                </div>
            </header>
        </section>

        @if($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs text-red-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <x-gpa.icon name="alert-circle" class="h-4 w-4 text-red-600 shrink-0 mt-0.5" />
                    <div class="flex flex-col gap-1">
                        <p class="font-semibold">Gagal menyimpan perubahan</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <section class="rounded-2xl bg-surface shadow-card overflow-hidden">
            <header class="border-b border-line-board/60 px-6 py-4">
                <h2 class="gpa-section-title text-ink">Formulir Edit Komoditas</h2>
                <p class="mt-1 text-xs text-ink-body">Komoditas: <span class="font-semibold text-ink">{{ $product->name }}</span> · SKU: <span class="font-mono">{{ $product->sku }}</span></p>
            </header>

            <form method="POST" action="{{ route('koordinator.products.update', $product) }}" enctype="multipart/form-data" class="px-6 py-5 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5">
                        <label for="sku" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Kode SKU <span class="text-red-600">*</span></label>
                        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Nama Komoditas <span class="text-red-600">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="flex flex-col gap-1.5">
                        <label for="grade" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Grade Mutu <span class="text-red-600">*</span></label>
                        <select id="grade" name="grade" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="Grade A" {{ old('grade', $product->grade) === 'Grade A' ? 'selected' : '' }}>Grade A</option>
                            <option value="Grade A+" {{ old('grade', $product->grade) === 'Grade A+' ? 'selected' : '' }}>Grade A+</option>
                            <option value="Grade B" {{ old('grade', $product->grade) === 'Grade B' ? 'selected' : '' }}>Grade B</option>
                            <option value="Grade C" {{ old('grade', $product->grade) === 'Grade C' ? 'selected' : '' }}>Grade C</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="unit" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Satuan <span class="text-red-600">*</span></label>
                        <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="base_price" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Harga Dasar (Rp) <span class="text-red-600">*</span></label>
                        <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="500" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="minimum_order" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Min. Pemesanan <span class="text-red-600">*</span></label>
                        <input type="number" id="minimum_order" name="minimum_order" value="{{ old('minimum_order', $product->minimum_order) }}" min="0.1" step="any" required
                            class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    </div>
                </div>

                <div class="rounded-xl border border-line-board bg-surface-shell/40 p-4 space-y-3">
                    <p class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Foto / Gambar Komoditas</p>
                    @if ($product->image_url)
                        <div class="flex items-center gap-3">
                            <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="h-14 w-14 rounded-lg border border-line-board object-cover">
                            <p class="text-[11px] leading-4 text-ink-body">Gambar saat ini tersimpan</p>
                        </div>
                    @endif
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-semibold text-ink">Ganti dengan Preset Bawaan</label>
                        <select name="preset_image" class="rounded bg-surface py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                            <option value="">-- Tetap Gunakan Gambar Saat Ini --</option>
                            <option value="broccoli.jpg">Brokoli Hijau</option>
                            <option value="carrot.jpg">Wortel Segar</option>
                            <option value="strawberry.jpg">Stroberi Ciwidey</option>
                            <option value="tomato.jpg">Tomat Segar</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-semibold text-ink">Atau Unggah File Baru (JPG / PNG / WEBP, Max 5MB)</label>
                        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"
                            class="w-full text-[11px] text-ink-body file:mr-3 file:rounded file:border-0 file:bg-surface-shell file:px-2.5 file:py-1.5 file:text-[11px] file:font-semibold file:text-ink file:outline file:outline-1 file:-outline-offset-1 file:outline-line-board hover:file:bg-surface-muted">
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="description" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Deskripsi Singkat / Spesifikasi</label>
                    <textarea id="description" name="description" rows="4"
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board placeholder:text-ink-body/70 focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Status Publikasi Katalog</label>
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="1" {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="text-success-deep focus:ring-success-deep">
                            <span class="text-[12px] font-semibold text-ink">Aktif (Tampil di katalog)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="is_active" value="0" {{ old('is_active', $product->is_active ? '1' : '0') == '0' ? 'checked' : '' }} class="text-ink-body focus:ring-line-board">
                            <span class="text-[12px] text-ink-body">Nonaktif (Arsip)</span>
                        </label>
                    </div>
                </div>

                <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-board/60 pt-4">
                    <a href="{{ route('koordinator.products.index') }}"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                        <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                        Simpan Perubahan
                    </button>
                </footer>
            </form>
        </section>
    </div>
@endsection
