<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Tambah Akun Pengguna Baru
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('direktur.users.index') }}" class="text-xs text-[#153a01] hover:underline font-semibold flex items-center gap-1">
                &larr; Kembali ke Daftar Akun
            </a>
        </div>
        @if($errors->any())
            <div class="p-3 bg-red-100 border border-red-400 text-red-800 text-xs rounded">
                <strong>Gagal Menyimpan Akun:</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-6 rounded shadow-sm text-xs">
            <form action="{{ route('direktur.users.store') }}" method="POST" class="space-y-3.5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Rina Marlina" required class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Alamat Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="user@greenpasundan.id" required class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nomor Telepon / WA *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="081234567890" required class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Wewenang Peran *</label>
                        <select name="role" id="role_select" required class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                            <option value="">-- Pilih Peran --</option>
                            <option value="DIREKTUR" {{ old('role') === 'DIREKTUR' ? 'selected' : '' }}>DIREKTUR</option>
                            <option value="SEKRETARIS" {{ old('role') === 'SEKRETARIS' ? 'selected' : '' }}>SEKRETARIS</option>
                            <option value="KOORDINATOR" {{ old('role') === 'KOORDINATOR' ? 'selected' : '' }}>KOORDINATOR</option>
                            <option value="ARMADA" {{ old('role') === 'ARMADA' ? 'selected' : '' }}>ARMADA</option>
                            <option value="KLIEN" {{ old('role') === 'KLIEN' ? 'selected' : '' }}>KLIEN</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Keaktifan *</label>
                        <select name="is_active" required class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                            <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>AKTIF</option>
                            <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>NON-AKTIF</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Kata Sandi Awal *</label>
                        <input type="password" name="password" placeholder="Minimal 8 karakter" required class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Jenis Klien (Khusus Role KLIEN)</label>
                        <select name="client_type" class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                            <option value="">-- Bukan Klien / Opsional --</option>
                            <option value="REGULER" {{ old('client_type') === 'REGULER' ? 'selected' : '' }}>REGULER</option>
                            <option value="B2B_KONTRAK" {{ old('client_type') === 'B2B_KONTRAK' ? 'selected' : '' }}>B2B_KONTRAK</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Nama Perusahaan / Entitas Usaha (Opsional)</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" placeholder="PT AEON Mall / CV Berkah" class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">
                </div>

                <div>
                    <label class="block font-bold text-gray-700 mb-1">Alamat Domisili / Pengiriman</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap..." class="w-full text-xs border-gray-300 rounded focus:ring-[#153a01]">{{ old('address') }}</textarea>
                </div>

                <div class="pt-2 flex justify-end space-x-2">
                    <a href="{{ route('direktur.users.index') }}" class="px-3 py-1.5 bg-gray-200 text-gray-700 rounded font-bold hover:bg-gray-300">Batal</a>
                    <button type="submit" class="px-4 py-1.5 bg-[#153a01] hover:bg-[#0f2801] text-white font-bold rounded">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
