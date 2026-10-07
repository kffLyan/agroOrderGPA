<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Rincian Akun: {{ $user->name }}
    </x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="flex items-center justify-between">
            <a href="{{ route('direktur.users.index') }}" class="text-xs text-[#153a01] hover:underline font-semibold flex items-center gap-1">
                &larr; Kembali ke Daftar Akun
            </a>
        </div>
        <div class="bg-white p-6 rounded shadow-sm text-xs space-y-4">
            <div class="flex justify-between items-start border-b pb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-gray-500 font-mono text-[11px]">{{ $user->email }}</p>
                </div>
                <div class="text-right">
                    <span class="px-2.5 py-1 rounded font-bold text-xs 
                        @if($user->role === 'DIREKTUR') bg-purple-100 text-purple-800
                        @elseif($user->role === 'SEKRETARIS') bg-blue-100 text-blue-800
                        @elseif($user->role === 'KOORDINATOR') bg-amber-100 text-amber-800
                        @elseif($user->role === 'ARMADA') bg-indigo-100 text-indigo-800
                        @else bg-green-100 text-green-800 @endif">
                        {{ $user->role }}
                    </span>
                    <div class="mt-1">
                        @if($user->is_active)
                            <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded font-semibold text-[10px]">AKTIF</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded font-semibold text-[10px]">NON-AKTIF</span>
                        @endif
                    </div>
                </div>
            </div>

            <table class="w-full text-left">
                <tr class="border-b"><th class="py-2 text-gray-500 w-40">Nomor Telepon:</th><td class="font-mono">{{ $user->phone }}</td></tr>
                <tr class="border-b"><th class="py-2 text-gray-500">Perusahaan / Mitra:</th><td>{{ $user->company_name ?? '-' }}</td></tr>
                <tr class="border-b"><th class="py-2 text-gray-500">Tipe Klien:</th><td>{{ $user->client_type ?? '-' }}</td></tr>
                <tr class="border-b"><th class="py-2 text-gray-500">Alamat:</th><td>{{ $user->address ?? '-' }}</td></tr>
                <tr class="border-b"><th class="py-2 text-gray-500">PIC:</th><td>{{ $user->pic_name ? $user->pic_name . ' (' . $user->pic_phone . ')' : '-' }}</td></tr>
                <tr><th class="py-2 text-gray-500">Terdaftar Sejak:</th><td>{{ $user->created_at->format('d M Y H:i') }}</td></tr>
            </table>

            <div class="pt-3 border-t flex justify-end space-x-2">
                <a href="{{ route('direktur.users.edit', $user->id) }}" class="px-3 py-1.5 bg-[#153a01] hover:bg-[#0f2801] text-white rounded font-bold transition">Ubah Akun</a>
            </div>
        </div>
    </div>
</x-dynamic-component>
