@extends('layouts.coordinator')

@section('title', 'Tambah Komoditas Baru')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    {{-- Header --}}
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-slate-900">Tambah Komoditas Baru</h1>
        <p class="text-xs text-slate-500">Form pengisian master komoditas hasil tani untuk katalog pesanan.</p>
    </div>

    {{-- Form Tambah Komoditas --}}
    <form method="POST" action="{{ route('koordinator.products.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf

        {{-- SKU & Nama --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="sku" class="block text-xs font-semibold text-slate-700 mb-1">
                    Kode SKU <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="sku" name="sku" value="{{ old('sku', $suggestedSku) }}" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                <span class="text-2xs text-slate-400">Kode unik komoditas (misal: GPA-VGT-TMT01)</span>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Komoditas <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Tomat Beef Lembang" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>
        </div>

        {{-- Grade, Satuan, Harga, Min Order --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
                <label for="grade" class="block text-xs font-semibold text-slate-700 mb-1">
                    Grade Mutu <span class="text-rose-500">*</span>
                </label>
                <select id="grade" name="grade" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    <option value="Grade A" {{ old('grade') === 'Grade A' ? 'selected' : '' }}>Grade A (Super/Ekspor)</option>
                    <option value="Grade B" {{ old('grade') === 'Grade B' ? 'selected' : '' }}>Grade B (Pasar Retail)</option>
                    <option value="Grade C" {{ old('grade') === 'Grade C' ? 'selected' : '' }}>Grade C (Olahan/Industri)</option>
                </select>
            </div>

            <div>
                <label for="unit" class="block text-xs font-semibold text-slate-700 mb-1">
                    Satuan Ukur <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="unit" name="unit" value="{{ old('unit', 'kg') }}" required placeholder="kg / ikat"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div>
                <label for="base_price" class="block text-xs font-semibold text-slate-700 mb-1">
                    Harga Dasar (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="base_price" name="base_price" value="{{ old('base_price', 15000) }}" min="0" step="500" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div>
                <label for="minimum_order" class="block text-xs font-semibold text-slate-700 mb-1">
                    Min. Pemesanan <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="minimum_order" name="minimum_order" value="{{ old('minimum_order', 5) }}" min="0.1" step="any" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>
        </div>

        {{-- Preset Gambar atau Upload --}}
        <div class="rounded border border-slate-200 bg-slate-50 p-4 space-y-3">
            <div class="text-xs font-semibold text-slate-800">Foto / Gambar Komoditas</div>

            <div>
                <label class="block text-2xs font-medium text-slate-600 mb-1">Opsi 1: Pilih Preset Bawaan GPA</label>
                <select name="preset_image" class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs">
                    <option value="">-- Tanpa Preset (Atau Unggah Sendiri Di Bawah) --</option>
                    <option value="broccoli.jpg" {{ old('preset_image') === 'broccoli.jpg' ? 'selected' : '' }}>Brokoli Hijau (broccoli.jpg)</option>
                    <option value="carrot.jpg" {{ old('preset_image') === 'carrot.jpg' ? 'selected' : '' }}>Wortel Segar (carrot.jpg)</option>
                    <option value="strawberry.jpg" {{ old('preset_image') === 'strawberry.jpg' ? 'selected' : '' }}>Stroberi Ciwidey (strawberry.jpg)</option>
                    <option value="tomato.jpg" {{ old('preset_image') === 'tomato.jpg' ? 'selected' : '' }}>Tomat Segar (tomato.jpg)</option>
                </select>
            </div>

            <div>
                <label class="block text-2xs font-medium text-slate-600 mb-1">Opsi 2: Unggah File Gambar (JPG / PNG / WEBP, Max 5MB)</label>
                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"
                    class="w-full text-xs text-slate-600 file:mr-2 file:rounded file:border-0 file:bg-slate-200 file:px-2.5 file:py-1 file:text-xs file:font-semibold hover:file:bg-slate-300">
            </div>
        </div>

        {{-- Deskripsi --}}
        <div>
            <label for="description" class="block text-xs font-semibold text-slate-700 mb-1">
                Deskripsi Singkat / Spesifikasi
            </label>
            <textarea id="description" name="description" rows="3" placeholder="Informasi asal petani, metode panen, standar kemasan..."
                class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">{{ old('description') }}</textarea>
        </div>

        {{-- Status Katalog --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Publikasi Katalog</label>
            <div class="flex items-center gap-6 text-xs">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} class="text-emerald-600">
                    <span class="font-medium text-emerald-800">Aktif (Langsung tayang di katalog klien)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }} class="text-slate-600">
                    <span class="text-slate-600">Nonaktif (Draft / Arsip)</span>
                </label>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('koordinator.products.index') }}" class="rounded border border-slate-300 bg-white px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                Batal / Kembali
            </a>
            <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-xs font-bold text-white hover:bg-emerald-700">
                Simpan Komoditas
            </button>
        </div>
    </form>
</div>
@endsection
