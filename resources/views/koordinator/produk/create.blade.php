@extends('layouts.koordinator')

@section('title', 'Tambah Komoditas Baru')

@section('content')
<div class="mx-auto flex max-w-4xl flex-col gap-6">
    {{-- Header --}}
    <section class="flex flex-col gap-4">
        <header class="flex flex-wrap items-start justify-between gap-4 rounded-2xl bg-surface p-4 sm:p-6 shadow-card">
            <div class="min-w-0 space-y-1">
                <p class="gpa-micro-bold uppercase tracking-wider text-ink-body">Manajemen Komoditas</p>
                <h1 class="font-sans text-xl font-bold leading-7 tracking-[-0.01em] text-ink sm:text-2xl sm:leading-8">
                    Tambah Komoditas Baru
                </h1>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('koordinator.produk.index') }}"
                    class="inline-flex items-center gap-1.5 rounded px-2.5 py-1.5 sm:px-3 sm:py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 shrink-0" />
                    Kembali
                </a>
            </div>
        </header>
    </section>

    {{-- Form Tambah Komoditas --}}
    <section class="rounded-2xl bg-surface shadow-card overflow-hidden">
        <header class="flex flex-wrap items-start justify-between gap-3 border-b border-line-board/60 px-4 py-3 sm:px-6 sm:py-4">
            <div class="min-w-0">
                <h2 class="gpa-section-title text-ink">Form Komoditas</h2>
            </div>
        </header>

        <form method="POST" action="{{ route('koordinator.produk.store') }}" enctype="multipart/form-data" class="px-4 py-4 sm:px-6 sm:py-5 space-y-5">
            @csrf

            {{-- SKU & Nama --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-1.5">
                    <label for="sku" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                        Kode SKU <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $suggestedSku) }}" required
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                    <span class="text-[10px] text-ink-body">Kode unik komoditas</span>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="name" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                        Nama Komoditas <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Tomat Beef Lembang" required
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                </div>
            </div>

            {{-- Grade, Satuan, Harga, Min Order --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="flex flex-col gap-1.5">
                    <label for="grade" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                        Grade Mutu <span class="text-red-600">*</span>
                    </label>
                    <select id="grade" name="grade" required
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        <option value="Grade A" {{ old('grade') === 'Grade A' ? 'selected' : '' }}>Grade A</option>
                        <option value="Grade A+" {{ old('grade') === 'Grade A+' ? 'selected' : '' }}>Grade A+</option>
                        <option value="Grade B" {{ old('grade') === 'Grade B' ? 'selected' : '' }}>Grade B</option>
                        <option value="Grade C" {{ old('grade') === 'Grade C' ? 'selected' : '' }}>Grade C</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="unit" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                        Satuan <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="unit" name="unit" value="{{ old('unit', 'kg') }}" required placeholder="kg / ikat"
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="base_price" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                        Harga Dasar (Rp) <span class="text-red-600">*</span>
                    </label>
                    <input type="number" id="base_price" name="base_price" value="{{ old('base_price') }}" min="0" step="100" required
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="minimum_order" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                        Min. Order <span class="text-red-600">*</span>
                    </label>
                    <input type="number" id="minimum_order" name="minimum_order" value="{{ old('minimum_order') }}" min="0.1" step="any" required
                        class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink font-mono outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                </div>
            </div>

            {{-- Gambar --}}
            <div class="rounded-lg border border-line-hair p-4 space-y-3 bg-surface-shell/30">
                <p class="text-xs font-semibold text-ink">Foto / Gambar Komoditas</p>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-semibold text-ink-body">Opsi 1: Pilih Preset</label>
                    <select name="preset_image" class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">
                        <option value="">-- Tanpa Preset --</option>
                        <option value="broccoli.jpg" {{ old('preset_image') === 'broccoli.jpg' ? 'selected' : '' }}>Brokoli</option>
                        <option value="carrot.jpg" {{ old('preset_image') === 'carrot.jpg' ? 'selected' : '' }}>Wortel</option>
                        <option value="strawberry.jpg" {{ old('preset_image') === 'strawberry.jpg' ? 'selected' : '' }}>Stroberi</option>
                        <option value="tomato.jpg" {{ old('preset_image') === 'tomato.jpg' ? 'selected' : '' }}>Tomat</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-semibold text-ink-body">Opsi 2: Unggah File (JPG/PNG/WEBP, Max 5MB)</label>
                    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"
                        class="w-full text-[11px] text-ink file:mr-2 file:rounded file:border-0 file:bg-surface-shell file:px-2.5 file:py-1 file:text-[11px] file:font-semibold file:text-ink file:outline file:outline-1 file:-outline-offset-1 file:outline-line-board hover:file:bg-surface-muted">
                </div>
            </div>

            {{-- Deskripsi --}}
            <div class="flex flex-col gap-1.5">
                <label for="description" class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">
                    Deskripsi Singkat / Spesifikasi
                </label>
                <textarea id="description" name="description" rows="3" placeholder="Informasi asal petani, metode panen, standar kemasan..."
                    class="rounded bg-surface-shell py-2 px-3 text-[12px] leading-5 text-ink outline outline-1 -outline-offset-1 outline-line-board focus:outline-2 focus:-outline-offset-2 focus:outline-success-deep">{{ old('description') }}</textarea>
            </div>

            {{-- Status --}}
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-semibold uppercase leading-4 tracking-[0.14em] text-ink-body">Status Publikasi</label>
                <div class="flex flex-wrap items-center gap-4 text-[11px]">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="text-ink">
                        <span class="text-ink">Aktif (Tampil di katalog)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }} class="text-ink">
                        <span class="text-ink-body">Nonaktif (Draft)</span>
                    </label>
                </div>
            </div>

            {{-- Actions --}}
            <footer class="flex flex-wrap items-center justify-end gap-2 border-t border-line-board/60 pt-4">
                <a href="{{ route('koordinator.produk.index') }}"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-surface-shell text-ink outline outline-1 -outline-offset-1 outline-line-board hover:bg-surface-muted">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded px-3 py-2 gpa-meta font-semibold transition-opacity hover:opacity-90 bg-ink text-accent shadow-sub">
                    <x-gpa.icon name="check" class="h-3.5 w-3.5 shrink-0" />
                    Simpan Komoditas
                </button>
            </footer>
        </form>
    </section>
</div>
@endsection
