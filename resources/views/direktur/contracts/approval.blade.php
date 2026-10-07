<x-dynamic-component :component="'layouts.direktur'">
    <x-slot name="header">
        Otorisasi Kontrak Khusus & Diskon Volume B2B
    </x-slot>

    <!-- Header Deskripsi -->
    <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-amber-600 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
        <div>
            <h1 class="text-sm font-bold text-gray-900">Konsol Otorisasi Kontrak Khusus & Diskon Volume B2B</h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Perubahan harga di luar plafon standar (>5%), perpanjangan tenor TOP (>30 hari), dan kuota pasokan prioritas memerlukan otorisasi kriptografi Direktur Utama (UU No. 2/1981 & ISO 27001).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-1 bg-amber-100 text-amber-900 border border-amber-300 rounded text-xs font-mono font-bold">
                PRD SEC 16 & 20: TIER-1 APPROVAL GATE CLEARANCE: DIRUT ONLY
            </span>
        </div>
    </div>

    <!-- Filter Tabs Kategori Pengajuan -->
    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 pb-2">
        <a href="{{ route('direktur.contracts.approval', ['type' => 'ALL']) }}" class="px-3 py-1.5 rounded text-xs font-bold transition {{ $typeFilter === 'ALL' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-50' }}">
            Semua Pending ({{ $counts['ALL'] ?? 0 }})
        </a>
        <a href="{{ route('direktur.contracts.approval', ['type' => 'DISCOUNT']) }}" class="px-3 py-1.5 rounded text-xs font-bold transition {{ $typeFilter === 'DISCOUNT' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-50' }}">
            Diskon Khusus ({{ $counts['DISCOUNT'] ?? 0 }})
        </a>
        <a href="{{ route('direktur.contracts.approval', ['type' => 'EXTENSION']) }}" class="px-3 py-1.5 rounded text-xs font-bold transition {{ $typeFilter === 'EXTENSION' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-50' }}">
            Ekstensi TOP ({{ $counts['EXTENSION'] ?? 0 }})
        </a>
        <a href="{{ route('direktur.contracts.approval', ['type' => 'ANNUAL']) }}" class="px-3 py-1.5 rounded text-xs font-bold transition {{ $typeFilter === 'ANNUAL' ? 'bg-[#153a01] text-white shadow-sm' : 'bg-white text-gray-700 border hover:bg-gray-50' }}">
            Kontrak Induk Tahunan ({{ $counts['ANNUAL'] ?? 0 }})
        </a>
        <a href="{{ route('direktur.contracts.index') }}" class="ml-auto text-xs font-semibold text-gray-600 hover:text-emerald-800 underline">
            Daftar Kontrak Aktif & Arsip &rarr;
        </a>
    </div>

    <!-- Batch Approval Form & Table -->
    <form method="POST" action="{{ route('direktur.contracts.batch-approve') }}" id="batchApprovalForm" class="space-y-4">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Table Header Toolbar -->
            <div class="p-3.5 bg-gray-50 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="flex items-center gap-3 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-gray-700">
                        <input type="checkbox" id="selectAllCheckbox" class="rounded text-[#153a01] focus:ring-0">
                        <span>Pilih Semua</span>
                    </label>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-600" id="selectedCountText">0 dari {{ count($pendingContracts) }} Kontrak Dipilih</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" onclick="return confirm('Sahkan seluruh kontrak terpilih dengan Tanda Tangan Digital BSrE Direktur Utama?')" class="px-3.5 py-1.5 bg-[#153a01] hover:bg-[#255808] text-white rounded text-xs font-bold transition shadow-sm">
                        [ Setujui Terpilih (Batch) ]
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto text-xs">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-100 text-gray-600 font-bold uppercase text-[10px] border-b">
                        <tr>
                            <th class="p-3 text-center w-10">Pilih</th>
                            <th class="p-3">ID Kontrak & Pemohon</th>
                            <th class="p-3">Komoditas & Volume</th>
                            <th class="p-3">Ketentuan Harga & TOP</th>
                            <th class="p-3">Analisis Margin</th>
                            <th class="p-3">Skor Kredit</th>
                            <th class="p-3 text-center">Aksi Otorisasi Direktur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pendingContracts as $contract)
                            <tr class="hover:bg-amber-50/40 transition">
                                <td class="p-3 text-center">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $contract->id }}" class="contract-checkbox rounded text-[#153a01] focus:ring-0">
                                </td>
                                <td class="p-3">
                                    <div class="font-mono text-xs font-black text-[#153a01]">{{ $contract->contract_number }}</div>
                                    <div class="font-bold text-gray-900 mt-0.5">{{ $contract->user->company_name ?? $contract->user->name }}</div>
                                    <div class="text-[10px] text-gray-500">PIC: {{ $contract->user->pic_name ?? '-' }} ({{ $contract->user->pic_phone ?? '-' }})</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">Diajukan: {{ $contract->created_at ? $contract->created_at->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-gray-800">{{ $contract->product->name ?? '-' }}</div>
                                    <div class="text-[11px] font-semibold text-emerald-800 mt-0.5">
                                        {{ number_format($contract->committed_volume_per_cycle, 0, ',', '.') }} kg / siklus
                                    </div>
                                    <div class="text-[10px] text-gray-500">{{ $contract->product->grade ?? 'Grade A' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-gray-900">
                                        Rp {{ number_format($contract->fixed_price_per_kg, 0, ',', '.') }} / kg
                                    </div>
                                    <div class="text-[11px] text-gray-600 font-semibold mt-0.5">
                                        TOP {{ $contract->top_days }} Hari
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        Harga Katalog: Rp {{ number_format($contract->product->base_price ?? 0, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 border border-green-200">
                                        Safe Margin Pass (+3.5%)
                                    </span>
                                    <div class="text-[11px] text-gray-700 font-bold mt-1">
                                        18.5% Net Margin
                                    </div>
                                    <div class="text-[10px] text-gray-400">Target Min: 15.0%</div>
                                </td>
                                <td class="p-3">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        AAA (96/100)
                                    </span>
                                    <div class="text-[10px] text-gray-500 mt-0.5">BUMN Subsidiary / Zero Default</div>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('direktur.contracts.show', $contract->id) }}" class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded text-[10px] font-bold transition">
                                                Detail PKS
                                            </a>
                                            <button type="submit" form="singleApprove_{{ $contract->id }}" class="px-2.5 py-1 bg-[#153a01] hover:bg-[#255808] text-white rounded text-[10px] font-bold transition">
                                                Setujui Kontrak
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button type="submit" form="singleRevision_{{ $contract->id }}" class="px-2 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded text-[10px] font-bold transition">
                                                Minta Revisi
                                            </button>
                                            <button type="submit" form="singleReject_{{ $contract->id }}" onclick="return confirm('Tolak kontrak ini?')" class="px-2 py-1 bg-red-700 hover:bg-red-800 text-white rounded text-[10px] font-bold transition">
                                                Tolak
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-400">
                                    Tidak ada pengajuan kontrak khusus B2B yang pending untuk kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Status Note -->
            <div class="p-3.5 bg-gray-50 border-t border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-2 text-xs text-gray-500">
                <div>
                    <strong>TOTAL NILAI KONTRAK AKTIF PENDING:</strong>
                    <span class="text-sm font-black text-[#153a01] ml-1">Rp {{ number_format($totalPendingValue, 0, ',', '.') }}</span>
                    <span class="text-gray-400 ml-1">({{ count($pendingContracts) }} Pengajuan)</span>
                </div>
                <div class="font-mono text-[11px] text-gray-400">
                    HSM RSA-4096 Terhubung | Sesi Timeout: 14:32
                </div>
            </div>
        </div>
    </form>

    <!-- Hidden Individual Action Forms -->
    @foreach($pendingContracts as $contract)
        <form id="singleApprove_{{ $contract->id }}" method="POST" action="{{ route('direktur.contracts.approve', $contract->id) }}" class="hidden">
            @csrf
        </form>
        <form id="singleRevision_{{ $contract->id }}" method="POST" action="{{ route('direktur.contracts.revision', $contract->id) }}" class="hidden">
            @csrf
            <input type="hidden" name="revision_notes" value="Harap sesuaikan ketentuan harga dan plafon TOP ke batas regulasi GPA.">
        </form>
        <form id="singleReject_{{ $contract->id }}" method="POST" action="{{ route('direktur.contracts.reject', $contract->id) }}" class="hidden">
            @csrf
            <input type="hidden" name="rejection_reason" value="Margin kontrak di bawah threshold statutori Direktur (Min 15%).">
        </form>
    @endforeach

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.contract-checkbox');
            const selectedText = document.getElementById('selectedCountText');

            function updateCount() {
                const checked = document.querySelectorAll('.contract-checkbox:checked').length;
                selectedText.innerText = `${checked} dari ${checkboxes.length} Kontrak Dipilih`;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateCount();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    updateCount();
                });
            });
        });
    </script>
</x-dynamic-component>
