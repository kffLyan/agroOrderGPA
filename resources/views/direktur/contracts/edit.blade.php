<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Ubah Parameter Kontrak: {{ $contract->contract_number }}
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('direktur.contracts.index') }}" class="text-xs text-[#153a01] hover:underline font-semibold flex items-center gap-1">
                &larr; Kembali ke Daftar Kontrak
            </a>
        </div>
        @if($errors->any())
            <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-900 text-xs rounded-r-lg shadow-sm">
                <strong>Gagal Memperbarui Kontrak:</strong>
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
                    Perubahan Klausul Finansial dan Status
                </h3>
                <p class="text-[11px] text-gray-500 mt-0.5">
                    Pengubahan harga kontrak akan langsung berpengaruh pada kalkulasi pesanan baru mitra terkait.
                </p>
            </div>

            <form action="{{ route('direktur.contracts.update', $contract->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Informasi Entitas Mitra (Readonly) -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Klien Mitra B2B:</label>
                    <input type="text" disabled
                           value="{{ $contract->user->company_name ?? $contract->user->name }} ({{ $contract->user->name }})"
                           class="w-full text-xs bg-gray-100/80 border border-gray-300 rounded-lg p-2.5 text-gray-700">
                </div>

                <!-- Informasi Komoditas (Readonly) -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">Komoditas Sayuran Kontrak:</label>
                    <input type="text" disabled
                           value="{{ $contract->product->name }} (Katalog: Rp {{ number_format($contract->product->base_price, 0, ',', '.') }}/{{ $contract->product->unit }}) - {{ $contract->product->grade ?? 'Grade A' }}"
                           class="w-full text-xs bg-gray-100/80 border border-gray-300 rounded-lg p-2.5 text-gray-700">
                </div>

                <!-- Parameter 3 Kolom: Harga Tetap, TOP, Komitmen -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">
                            Harga Tetap (Rp/Kg) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="fixed_price_per_kg" value="{{ old('fixed_price_per_kg', $contract->fixed_price_per_kg) }}" min="1000" required
                               class="w-full text-xs font-semibold border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">
                            Termin TOP (Hari) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="top_days" value="{{ old('top_days', $contract->top_days) }}" min="0" required
                               class="w-full text-xs font-semibold border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">
                            Komitmen Vol (Kg) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="committed_volume_per_cycle" value="{{ old('committed_volume_per_cycle', $contract->committed_volume_per_cycle) }}" min="0.1" required
                               class="w-full text-xs font-semibold border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                    </div>
                </div>

                <!-- Status Kontrak (Enum Valid) -->
                <div>
                    <label class="block font-bold text-gray-700 mb-1">
                        Status Kontrak PKS <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required class="w-full text-xs border border-gray-300 rounded-lg p-2.5 focus:ring-1 focus:ring-[#153a01] focus:border-[#153a01]">
                        <option value="ACTIVE" {{ old('status', $contract->status) === 'ACTIVE' ? 'selected' : '' }}>
                            ACTIVE — Berlaku & Disahkan
                        </option>
                        <option value="PENDING_APPROVAL" {{ old('status', $contract->status) === 'PENDING_APPROVAL' ? 'selected' : '' }}>
                            PENDING_APPROVAL — Menunggu Persetujuan Direktur
                        </option>
                        <option value="EXPIRED" {{ old('status', $contract->status) === 'EXPIRED' ? 'selected' : '' }}>
                            EXPIRED — Masa Kontrak Habis
                        </option>
                        <option value="TERMINATED" {{ old('status', $contract->status) === 'TERMINATED' ? 'selected' : '' }}>
                            TERMINATED — Dibatalkan / Dihentikan Resmi
                        </option>
                    </select>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-2">
                    <a href="{{ route('direktur.contracts.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold transition">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2 bg-[#153a01] hover:bg-[#0f2801] text-white rounded-lg font-bold shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
