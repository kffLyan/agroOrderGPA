<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Terbitkan Kontrak Kemitraan B2B & Harga Khusus
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('direktur.contracts.index') }}" class="text-xs text-[#153a01] hover:underline font-semibold flex items-center gap-1">
                &larr; Kembali ke Daftar Kontrak
            </a>
        </div>
        @if($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-900 text-xs rounded-r-lg shadow-sm">
                <strong>Gagal Menyimpan Kontrak:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-xs">
            <div class="mb-4 pb-3 border-b border-gray-100">
                <h3 class="font-bold text-sm text-gray-900 flex items-center gap-2">
                    Formulir Perjanjian Kerja Sama (PKS)
                </h3>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    Sebagai Direktur, kontrak yang Anda terbitkan dapat langsung diaktifkan secara sah atau disimpan sebagai draf.
                </p>
            </div>

            <form action="{{ route('direktur.contracts.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Pilihan Klien Mitra -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Pilih Klien Mitra B2B <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                        <option value="">-- Pilih Akun Perusahaan / Klien --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('user_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->company_name ?? $client->name }} (Akun: {{ $client->name }} - {{ $client->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Pilihan Komoditas -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Komoditas Sayuran Dataran Tinggi <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" required class="w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                        <option value="">-- Pilih Komoditas Sayuran --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Katalog Dasar: Rp {{ number_format($product->base_price, 0, ',', '.') }}/{{ $product->unit }}) - {{ $product->grade ?? 'Grade A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nomor Kontrak PKS -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Nomor Perjanjian Kontrak (PKS) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', $suggestedNumber ?? '') }}"
                           placeholder="Contoh: CTR-GPA-202610-001" required
                           class="w-full text-xs font-mono border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    <span class="text-[10px] text-gray-400 mt-0.5 block">Format standar: CTR-GPA-TAHUNBULAN-NOMOR</span>
                </div>

                <!-- Parameter 3 Kolom: Harga Tetap, TOP, Komitmen -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">
                            Harga Tetap (Rp/Kg) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="fixed_price_per_kg" value="{{ old('fixed_price_per_kg', 14000) }}" min="1000" required
                               class="w-full text-xs font-semibold border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">
                            Termin TOP (Hari) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="top_days" value="{{ old('top_days', 30) }}" min="0" required
                               class="w-full text-xs font-semibold border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">
                            Komitmen Vol (Kg) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="committed_volume_per_cycle" value="{{ old('committed_volume_per_cycle', 800) }}" min="0.1" required
                               class="w-full text-xs font-semibold border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    </div>
                </div>

                <!-- Status Pengesahan Awal -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Status Pengesahan Langsung
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center p-3 border border-emerald-300 bg-emerald-50/50 rounded-lg cursor-pointer">
                            <input type="radio" name="status" value="ACTIVE" checked class="text-emerald-600 focus:ring-emerald-500">
                            <span class="ml-2">
                                <strong class="block text-emerald-950">Langsung Aktifkan (ACTIVE)</strong>
                                <span class="text-[10px] text-emerald-700">Disahkan langsung oleh Anda sebagai Direktur</span>
                            </span>
                        </label>

                        <label class="flex items-center p-3 border border-amber-300 bg-amber-50/50 rounded-lg cursor-pointer">
                            <input type="radio" name="status" value="PENDING_APPROVAL" {{ old('status') === 'PENDING_APPROVAL' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                            <span class="ml-2">
                                <strong class="block text-amber-950">Simpan sebagai Draf</strong>
                                <span class="text-[10px] text-amber-700">Status Menunggu Persetujuan</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-2">
                    <a href="{{ route('direktur.contracts.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-[#153a01] hover:bg-[#0f2801] text-white rounded-lg font-bold shadow-sm transition">
                        Simpan Kontrak PKS
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
