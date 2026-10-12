@extends('layouts.direktur')

@section('title', 'Ubah Komoditas: ' . $product->name . ' // Direktur AgroOrder GPA')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    {{-- Breadcrumb / Tombol Kembali --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('direktur.products.index') }}"
            class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition hover:bg-surface-shell">
            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
            Kembali ke Katalog Komoditas
        </a>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-xs text-red-800 shadow-sm">
            <strong class="font-bold">Gagal Menyimpan Perubahan:</strong>
            <ul class="mt-1 list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Formulir Edit Komoditas --}}
    <div class="rounded-2xl border border-line-hair bg-surface p-6 shadow-card"
        x-data="{
            selectedPreset: '{{ old('preset_image', $product->image_url && !str_starts_with($product->image_url, 'storage/') ? $product->image_url : '') }}',
            hasUpload: false,
            previewUrl: null,
            handleFileChange(e) {
                const file = e.target.files[0];
                if (file) {
                    this.hasUpload = true;
                    this.previewUrl = URL.createObjectURL(file);
                } else {
                    this.hasUpload = false;
                    this.previewUrl = null;
                }
            }
        }">
        <header class="border-b border-line-hair pb-4 mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="font-sans text-xl font-bold text-ink">Ubah Data Komoditas: {{ $product->name }}</h1>
                <p class="text-xs text-ink-body mt-0.5">
                    Perbarui SKU, grade mutu, harga dasar franco, batas minimum order, atau perbarui foto produk.
                </p>
            </div>
            <span class="rounded bg-accent px-2 py-1 gpa-mono-xs font-bold text-brand-deep">
                ID: PRD-{{ $product->id }}
            </span>
        </header>

        <form action="{{ route('direktur.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- SKU --}}
                <div>
                    <label for="sku" class="block font-bold text-ink mb-1">Kode SKU Komoditas *</label>
                    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required
                        class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs font-mono font-bold text-ink focus:border-brand focus:ring-1 focus:ring-brand">
                    <p class="mt-1 text-[11px] text-ink-body">Format unik kode penamaan standar GPA.</p>
                </div>

                {{-- Nama Komoditas --}}
                <div>
                    <label for="name" class="block font-bold text-ink mb-1">Nama Komoditas Pertanian *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs text-ink focus:border-brand focus:ring-1 focus:ring-brand">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Satuan Unit --}}
                <div>
                    <label for="unit" class="block font-bold text-ink mb-1">Satuan Dasar *</label>
                    <select id="unit" name="unit" required
                        class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs text-ink focus:border-brand focus:ring-1 focus:ring-brand">
                        <option value="kg" {{ old('unit', $product->unit) === 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="pack" {{ old('unit', $product->unit) === 'pack' ? 'selected' : '' }}>Pack / Mika (pack)</option>
                        <option value="ikat" {{ old('unit', $product->unit) === 'ikat' ? 'selected' : '' }}>Ikat (ikat)</option>
                        <option value="krat" {{ old('unit', $product->unit) === 'krat' ? 'selected' : '' }}>Krat Plastik (krat)</option>
                    </select>
                </div>

                {{-- Grade Mutu --}}
                <div>
                    <label for="grade" class="block font-bold text-ink mb-1">Grade Mutu *</label>
                    <select id="grade" name="grade" required
                        class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs text-ink focus:border-brand focus:ring-1 focus:ring-brand">
                        <option value="Grade A" {{ old('grade', $product->grade) === 'Grade A' ? 'selected' : '' }}>Grade A (Standar Horeca)</option>
                        <option value="Grade Super" {{ old('grade', $product->grade) === 'Grade Super' ? 'selected' : '' }}>Grade Super (Premium)</option>
                        <option value="Grade B" {{ old('grade', $product->grade) === 'Grade B' ? 'selected' : '' }}>Grade B (Reguler)</option>
                        <option value="Grade Ekspor" {{ old('grade', $product->grade) === 'Grade Ekspor' ? 'selected' : '' }}>Grade Ekspor</option>
                    </select>
                </div>

                {{-- Harga Dasar (Base Price) --}}
                <div>
                    <label for="base_price" class="block font-bold text-ink mb-1">Harga Dasar (Rp) *</label>
                    <input type="number" id="base_price" name="base_price" value="{{ old('base_price', (int)$product->base_price) }}" min="0" step="100" required
                        class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs font-mono font-bold text-ink focus:border-brand focus:ring-1 focus:ring-brand">
                    <p class="mt-1 text-[11px] text-ink-body">Franco Gudang Penerima.</p>
                </div>

                {{-- Minimum Order (MOQ) --}}
                <div>
                    <label for="minimum_order" class="block font-bold text-ink mb-1">Min. Order (MOQ) *</label>
                    <input type="number" id="minimum_order" name="minimum_order" value="{{ old('minimum_order', (int)$product->minimum_order) }}" min="0.1" step="0.5" required
                        class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs font-mono font-bold text-ink focus:border-brand focus:ring-1 focus:ring-brand">
                </div>
            </div>

            {{-- Bagian Pemilihan / Upload Foto Produk --}}
            <div class="rounded-xl border border-line-faint bg-surface-shell p-4 space-y-4">
                <div class="flex items-start justify-between border-b border-line-faint pb-3">
                    <div>
                        <h2 class="font-bold text-ink text-sm">Foto Produk Komoditas</h2>
                        <p class="text-[11px] text-ink-body">Foto saat ini dan opsi penggantian gambar.</p>
                    </div>

                    {{-- Preview Foto Saat Ini --}}
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-semibold text-ink-body">Foto Aktif:</span>
                        <div class="h-14 w-20 overflow-hidden rounded-lg border border-line-board bg-surface-muted shadow-sub">
                            <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}"
                                class="h-full w-full object-cover"
                                x-on:error="$event.target.src = '{{ asset('gpaleaves.png') }}'">
                        </div>
                    </div>
                </div>

                {{-- Preset Gambar --}}
                <div>
                    <span class="block font-semibold text-ink mb-2">Ganti dengan Gambar Komoditas Preset:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
                        @foreach ($presets as $p)
                            <label class="relative cursor-pointer rounded-lg border p-1.5 transition-all text-center flex flex-col items-center gap-1.5"
                                :class="selectedPreset === '{{ $p['path'] }}' && !hasUpload ? 'border-brand ring-2 ring-brand bg-white' : 'border-line-board bg-surface hover:bg-surface-muted'">
                                <input type="radio" name="preset_image" value="{{ $p['path'] }}"
                                    x-model="selectedPreset"
                                    @click="hasUpload = false; previewUrl = null"
                                    class="sr-only">
                                <div class="h-16 w-full overflow-hidden rounded bg-surface-muted">
                                    <img src="{{ asset($p['path']) }}" alt="{{ $p['label'] }}" class="h-full w-full object-cover">
                                </div>
                                <span class="text-[10px] font-semibold text-ink leading-tight truncate w-full">{{ $p['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Upload Custom File --}}
                <div class="border-t border-line-faint pt-3">
                    <label for="image_file" class="block font-semibold text-ink mb-1">Atau Unggah File Foto Baru Sendiri (Opsional):</label>
                    <input type="file" id="image_file" name="image_file" accept="image/*"
                        @change="handleFileChange($event)"
                        class="w-full text-xs text-ink file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-brand file:text-white hover:file:bg-brand-deep cursor-pointer">
                    <p class="mt-1 text-[11px] text-ink-body">Biarkan kosong jika tidak ingin mengubah foto saat ini.</p>

                    {{-- Preview Container --}}
                    <div x-show="hasUpload && previewUrl" class="mt-3 flex items-center gap-3" x-cloak>
                        <span class="text-[11px] font-semibold text-ink-body">Preview Upload Baru:</span>
                        <div class="h-16 w-24 overflow-hidden rounded-lg border border-line-board">
                            <img :src="previewUrl" alt="Preview Upload Baru" class="h-full w-full object-cover">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Deskripsi & Standar Mutu --}}
            <div>
                <label for="description" class="block font-bold text-ink mb-1">Deskripsi Spesifikasi &amp; Standar Mutu Metrologi</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full rounded border-line-board bg-surface-shell px-3 py-2 text-xs text-ink focus:border-brand focus:ring-1 focus:ring-brand">{{ old('description', $product->description) }}</textarea>
            </div>

            {{-- Status Keaktifan --}}
            <div>
                <span class="block font-bold text-ink mb-1">Status Keaktifan di Katalog *</span>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="1" {{ old('is_active', (string)(int)$product->is_active) === '1' ? 'checked' : '' }}
                            class="text-brand focus:ring-brand">
                        <span class="font-semibold text-ink">AKTIF (Dapat Dipesan di Katalog Klien)</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="is_active" value="0" {{ old('is_active', (string)(int)$product->is_active) === '0' ? 'checked' : '' }}
                            class="text-brand focus:ring-brand">
                        <span class="font-semibold text-ink-body">NON-AKTIF (Disembunyikan Sementara)</span>
                    </label>
                </div>
            </div>

            {{-- Tombol Aksi Form --}}
            <div class="flex items-center justify-end gap-3 border-t border-line-hair pt-4">
                <a href="{{ route('direktur.products.index') }}"
                    class="rounded-lg border border-line-board bg-surface px-4 py-2 font-semibold text-ink hover:bg-surface-shell transition">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-lg bg-ink px-5 py-2 font-bold text-accent shadow-sub hover:bg-brand-deep transition flex items-center gap-2">
                    <x-gpa.icon name="save" class="h-3.5 w-3.5" />
                    Simpan Perubahan Komoditas
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
