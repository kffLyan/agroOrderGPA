@extends('layouts.coordinator')

@section('title', 'Edit Komoditas: ' . $product->name)

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    {{-- Header --}}
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-xl font-bold text-slate-900">Edit Komoditas: {{ $product->name }}</h1>
        <p class="text-xs text-slate-500">Perbarui spesifikasi, harga, atau status tayang di katalog klien.</p>
    </div>

    {{-- Form Edit Komoditas --}}
    <form method="POST" action="{{ route('koordinator.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        {{-- SKU & Nama --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="sku" class="block text-xs font-semibold text-slate-700 mb-1">
                    Kode SKU <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Komoditas <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
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
                    <option value="Grade A" {{ old('grade', $product->grade) === 'Grade A' ? 'selected' : '' }}>Grade A (Super/Ekspor)</option>
                    <option value="Grade A+" {{ old('grade', $product->grade) === 'Grade A+' ? 'selected' : '' }}>Grade A+ (Premium Plus)</option>
                    <option value="Grade B" {{ old('grade', $product->grade) === 'Grade B' ? 'selected' : '' }}>Grade B (Pasar Retail)</option>
                    <option value="Grade C" {{ old('grade', $product->grade) === 'Grade C' ? 'selected' : '' }}>Grade C (Olahan/Industri)</option>
                </select>
            </div>

            <div>
                <label for="unit" class="block text-xs font-semibold text-slate-700 mb-1">
                    Satuan Ukur <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div>
                <label for="base_price" class="block text-xs font-semibold text-slate-700 mb-1">
                    Harga Dasar (Rp) <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="base_price" name="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="500" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>

            <div>
                <label for="minimum_order" class="block text-xs font-semibold text-slate-700 mb-1">
                    Min. Pemesanan <span class="text-rose-500">*</span>
                </label>
                <input type="number" id="minimum_order" name="minimum_order" value="{{ old('minimum_order', $product->minimum_order) }}" min="0.1" step="any" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-xs font-mono focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
            </div>
        </div>

        {{-- Gambar Saat Ini & Opsi Penggantian --}}
        <div class="rounded border border-slate-200 bg-slate-50 p-4 space-y-3">
            <div class="text-xs font-semibold text-slate-800">Foto / Gambar Komoditas</div>

            @if ($product->image_url)
                <div class="flex items-center gap-3">
                    <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}" class="h-14 w-14 rounded border border-slate-300 object-cover">
                    <div class="text-2xs text-slate-500">
                        Foto saat ini: <code class="font-mono text-slate-700">{{ $product->image_url }}</code>
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-2xs font-medium text-slate-600 mb-1">Ganti dengan Preset Bawaan GPA</label>
                <select name="preset_image" class="w-full rounded border border-slate-300 bg-white px-3 py-1.5 text-xs">
                    <option value="">-- Tetap Gunakan Gambar Saat Ini --</option>
                    <option value="broccoli.jpg">Brokoli Hijau (broccoli.jpg)</option>
                    <option value="carrot.jpg">Wortel Segar (carrot.jpg)</option>
                    <option value="strawberry.jpg">Stroberi Ciwidey (strawberry.jpg)</option>
                    <option value="tomato.jpg">Tomat Segar (tomato.jpg)</option>
                </select>
            </div>

            <div>
                <label class="block text-2xs font-medium text-slate-600 mb-1">Atau Unggah File Baru (JPG / PNG / WEBP, Max 5MB)</label>
                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp"
                    class="w-full text-xs text-slate-600 file:mr-2 file:rounded file:border-0 file:bg-slate-200 file:px-2.5 file:py-1 file:text-xs file:font-semibold hover:file:bg-slate-300">
            </div>
        </div>

        {{-- Deskripsi --}}
        <div>
            <label for="description" class="block text-xs font-semibold text-slate-700 mb-1">
                Deskripsi Singkat / Spesifikasi
            </label>
            <textarea id="description" name="description" rows="3"
                class="w-full rounded border border-slate-300 px-3 py-2 text-xs focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">{{ old('description', $product->description) }}</textarea>
        </div>

        {{-- Status Katalog --}}
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Publikasi Katalog</label>
            <div class="flex items-center gap-6 text-xs">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="is_active" value="1" {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'checked' : '' }} class="text-emerald-600">
                    <span class="font-medium text-emerald-800">Aktif (Tampil di katalog klien)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="is_active" value="0" {{ old('is_active', $product->is_active ? '1' : '0') == '0' ? 'checked' : '' }} class="text-slate-600">
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
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
