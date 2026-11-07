<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berita Acara Retur {{ $document['number'] }} &middot; Koperasi GPA</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .print-page {
                width: 100% !important;
                max-width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }

        @media screen {
            body {
                background: #eef2ea;
                min-height: 100vh;
            }

            .print-page {
                width: 100%;
                max-width: 210mm;
                min-height: 297mm;
                margin: 16px auto 40px auto;
                background: #ffffff;
                border-radius: 4px;
                box-shadow: 0 12px 32px -8px rgba(0, 0, 0, 0.15), 0 2px 6px -2px rgba(0, 0, 0, 0.05);
                padding: 26px 32px;
            }
        }

        .bap-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 8pt;
        }

        .bap-table th {
            padding: 5px 6px;
            font-weight: 700;
        }

        .bap-table td {
            padding: 4px 6px;
            vertical-align: middle;
        }
    </style>
</head>

<body class="flex flex-col items-center px-2 sm:px-4 py-4 font-sans text-gray-900 antialiased">

    {{-- Top Action Bar (Layar Saja) --}}
    <header class="no-print mb-4 flex w-full max-w-[210mm] flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-300 bg-white px-4 py-2 shadow-sm">
        @php
            $currentUser = auth()->user();
            $backUrl = match($currentUser?->role) {
                'SEKRETARIS'  => route('sekretaris.monitoring-pod'),
                'KOORDINATOR' => route('koordinator.dashboard'),
                'ARMADA'      => route('armada.riwayat'),
                default       => route('klien.dokumen'),
            };
            $backLabel = match($currentUser?->role) {
                'SEKRETARIS'  => 'Kembali ke Monitoring PoD',
                'KOORDINATOR' => 'Kembali ke Dashboard',
                'ARMADA'      => 'Kembali ke Riwayat Armada',
                default       => 'Kembali ke Portal Klien',
            };
        @endphp

        <a href="{{ $backUrl }}"
            class="inline-flex items-center gap-1.5 rounded border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition-colors hover:bg-gray-100">
            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 text-gray-500" />
            <span>{{ $backLabel }}</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="hidden sm:inline-block text-[11px] font-medium text-gray-500">
                Format Legal Standar A4 &bull; Koperasi GPA
            </span>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded bg-gray-900 px-4 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-black active:scale-95">
                <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                <span>Print Berita Acara (Ctrl+P)</span>
            </button>
        </div>
    </header>

    {{-- A4 Printable Sheet --}}
    <main class="print-page box-border flex flex-col justify-between text-gray-900">
        <div>
            {{-- KOP SURAT FORMAL & RESMI (Monokrom Elegan) --}}
            <header class="border-b-2 border-black pb-2.5">
                <div class="flex items-start justify-between gap-4">
                    {{-- Logo & Identitas Badan Hukum --}}
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded border border-gray-400 p-1">
                            <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-full w-full object-contain grayscale contrast-125">
                        </div>
                        <div>
                            <h1 class="text-[11.5pt] font-black uppercase tracking-tight text-black leading-tight">
                                {{ $brand }}
                            </h1>
                            <p class="text-[7.5pt] font-bold text-gray-800">
                                Badan Hukum No. AHU-0001248.AH.01.26.TAHUN 2021 &middot; Sentra Logistik Dataran Tinggi Ciwidey
                            </p>
                            <p class="text-[7pt] text-gray-600 leading-tight mt-0.5">
                                Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey, Kab. Bandung 40973 &middot; Telp/WA: 0812-2323-9133 / 0813-2142-2231
                            </p>
                        </div>
                    </div>

                    {{-- Box Kanan: Nomor Register & Judul --}}
                    <div class="min-w-[240px] shrink-0 border border-black bg-white p-2 text-right">
                        <div class="flex items-center justify-between border-b border-gray-300 pb-1">
                            <span class="text-[7pt] font-bold uppercase tracking-wider text-gray-700">
                                BERKAS AUDIT QC
                            </span>
                            <span class="border border-gray-600 bg-gray-100 px-1.5 py-0.2 text-[6.5pt] font-bold uppercase tracking-wider text-black">
                                {{ $client_type_label ?? ($is_b2b ? 'Mitra B2B (Kontrak / Tempo)' : 'Pelanggan Reguler (Retail / Tunai)') }}
                            </span>
                        </div>

                        <h2 class="mt-1 text-[9pt] font-black uppercase tracking-wide text-black">
                            {{ $document['title'] }}
                        </h2>
                        <p class="text-[6.5pt] font-semibold text-gray-600 tracking-wider uppercase">(BAP-RETUR DAN REKONSILIASI FISIK)</p>

                        <div class="mt-1 flex items-center justify-between border-t border-gray-300 pt-1 text-left">
                            <span class="text-[7pt] font-medium text-gray-600">No. Register:</span>
                            <span class="font-mono text-[8pt] font-bold text-black">{{ $document['number'] }}</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- 1. DATA REFERENSI SURAT JALAN & LOKASI SERAH TERIMA --}}
            <section class="mt-2.5 border border-gray-400 bg-white p-2 text-[7.5pt]">
                <div class="mb-1 flex items-center justify-between border-b border-gray-300 pb-0.5">
                    <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-black">
                        I. Data Referensi Pengiriman &amp; Lokasi Serah Terima
                    </h3>
                    <span class="font-mono text-[7pt] text-gray-600">Waktu Audit: {{ $document['inspection'] }}</span>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-0.5">
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-300 py-0.5">
                        <span class="text-gray-600">Surat Jalan (SJ):</span>
                        <span class="font-mono font-bold text-black">{{ $document['delivery'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-300 py-0.5">
                        <span class="text-gray-600">Purchase Order (PO):</span>
                        <span class="font-mono font-bold text-black">{{ $document['order'] }}</span>
                    </div>

                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-300 py-0.5">
                        <span class="text-gray-600">Penerima / Mitra Klien:</span>
                        <span class="font-bold text-black">{{ $client['name'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-300 py-0.5">
                        <span class="text-gray-600">PIC Receiving Dock:</span>
                        <span class="font-semibold text-black">{{ $client['contact'] }} ({{ $client['role'] }})</span>
                    </div>

                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-300 py-0.5">
                        <span class="text-gray-600">Armada &amp; Pengemudi:</span>
                        <span class="font-semibold text-black">{{ $document['vehicle'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-300 py-0.5">
                        <span class="text-gray-600">Segmentasi Skema:</span>
                        <span class="font-bold text-black">{{ $client_type_label ?? ($is_b2b ? 'Mitra B2B (Kontrak / Tempo)' : 'Pelanggan Reguler (Retail / Tunai)') }}</span>
                    </div>

                    <div class="col-span-2 flex items-baseline justify-between pt-0.5">
                        <span class="text-gray-600">Titik Bongkar / Dock QC:</span>
                        <span class="font-medium text-black text-right">{{ $document['location'] }}</span>
                    </div>
                </div>
            </section>

            {{-- 2. TABEL REKONSILIASI FISIK TIMBANGAN & SORTASI MUTU --}}
            <section class="mt-2.5">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-black">
                        II. Rekonsiliasi Fisik Timbangan &amp; Sortasi Penolakan Dock
                    </h3>
                    <span class="text-[7pt] text-gray-600">Satuan: <strong>Kilogram (kg)</strong></span>
                </div>

                <div class="border border-black">
                    <table class="bap-table">
                        <thead>
                            <tr class="bg-gray-100 text-black border-b-2 border-black text-[7pt] uppercase tracking-wider font-bold">
                                <th class="w-8 border-r border-gray-400 text-center">No</th>
                                <th class="w-20 border-r border-gray-400 text-left">Kode</th>
                                <th class="border-r border-gray-400 text-left">Nama Komoditas</th>
                                <th class="w-20 border-r border-gray-400 text-right">Kirim (kg)</th>
                                <th class="w-20 border-r border-gray-400 text-right">Retur (kg)</th>
                                <th class="w-22 border-r border-gray-400 text-right">Diterima (kg)</th>
                                <th class="w-56 text-left">Keterangan / Alasan Penolakan QC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr @class([
                                    'border-b border-gray-300',
                                    'bg-gray-50' => $index % 2 === 1,
                                ])>
                                    <td class="border-r border-gray-300 text-center font-mono text-gray-700">{{ $index + 1 }}</td>
                                    <td class="border-r border-gray-300 font-mono text-gray-700">{{ $item['code'] }}</td>
                                    <td class="border-r border-gray-300 font-bold text-black">{{ $item['name'] }}</td>
                                    <td class="border-r border-gray-300 text-right font-mono text-gray-800">
                                        {{ number_format($item['shipped'], 1, ',', '.') }}
                                    </td>
                                    <td @class([
                                        'border-r border-gray-300 text-right font-mono',
                                        'font-black text-black underline' => $item['returned'] > 0,
                                        'text-gray-400' => $item['returned'] == 0,
                                    ])>
                                        {{ number_format($item['returned'], 1, ',', '.') }}
                                    </td>
                                    <td class="border-r border-gray-300 text-right font-mono font-bold text-black">
                                        {{ number_format($item['accepted'], 1, ',', '.') }}
                                    </td>
                                    <td class="leading-tight {{ $item['returned'] > 0 ? 'font-semibold text-black' : 'text-gray-500 italic' }}">
                                        {{ $item['reason'] }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Baris Total Akumulasi --}}
                            <tr class="border-t-2 border-black bg-gray-100 font-bold text-[7.5pt]">
                                <th colspan="3" class="border-r border-gray-400 py-1.5 px-2 text-right uppercase tracking-wider text-black">
                                    Total Akumulasi Fisik Muatan
                                </th>
                                <td class="border-r border-gray-400 py-1.5 px-2 text-right font-mono text-black">
                                    {{ number_format($totals['shipped'], 1, ',', '.') }} kg
                                </td>
                                <td class="border-r border-gray-400 py-1.5 px-2 text-right font-mono font-black text-black underline">
                                    {{ number_format($totals['returned'], 1, ',', '.') }} kg
                                </td>
                                <td class="border-r border-gray-400 py-1.5 px-2 text-right font-mono font-black text-black">
                                    {{ number_format($totals['accepted'], 1, ',', '.') }} kg
                                </td>
                                <td class="py-1.5 px-2 text-[6.5pt] font-semibold text-gray-600">
                                    Validasi timbangan tera digital dock penerimaan
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- 3. KESEPAKATAN KOMPENSASI RETUR & REKONSILIASI KEUANGAN --}}
            <section class="mt-2.5 border border-gray-400 bg-white p-2">
                <div class="mb-1 flex items-center justify-between border-b border-gray-300 pb-0.5">
                    <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-black">
                        III. Kesepakatan Kompensasi Retur &amp; Rekonsiliasi Keuangan
                    </h3>
                    <span class="text-[6.5pt] font-semibold text-gray-600">SOP Tata Kelola Keuangan GPA</span>
                </div>

                <div class="grid grid-cols-12 items-stretch gap-2.5 text-[7.5pt]">
                    {{-- Kotak Kiri: Valuasi Finansial Retur --}}
                    <div class="col-span-5 flex flex-col justify-between border border-gray-400 bg-gray-50 p-2">
                        <div>
                            <span class="text-[6.5pt] font-bold uppercase tracking-wider text-gray-700">
                                Total Valuasi Kompensasi Retur:
                            </span>
                            <div class="mt-0.5 flex items-baseline justify-between gap-1">
                                <span class="font-mono text-[7pt] text-gray-600">
                                    {{ number_format($totals['returned'], 1, ',', '.') }} kg &times; Rp {{ number_format($settlement['returned_price'], 0, ',', '.') }}
                                </span>
                                <strong class="font-mono text-[10pt] font-black text-black">
                                    Rp {{ number_format($settlement['returned_value'], 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                        <div class="mt-1 border-t border-gray-300 pt-0.5 text-[6.5pt] text-gray-600">
                            Dasar Acuan: <strong>{{ $settlement['price_reference'] }}</strong>
                        </div>
                    </div>

                    {{-- Kotak Kanan: Skema Penyelesaian yang Disepakati --}}
                    <div class="col-span-7 flex flex-col justify-between gap-1">
                        {{-- Opsi Terpilih --}}
                        <div class="border-2 border-black bg-white p-1.5">
                            <p class="font-bold text-black leading-tight text-[7.5pt]">
                                [ X ] {{ $settlement['selected_option_title'] ?? ('Opsi Terpilih: ' . $settlement['selected_option']) }}
                            </p>
                            <p class="mt-0.5 text-[7pt] text-gray-700 leading-tight">
                                {{ $settlement['selected_option_desc'] ?? ('Jadwal: ' . $settlement['schedule']) }}
                            </p>
                        </div>

                        {{-- Opsi Alternatif --}}
                        <div class="border border-gray-300 bg-gray-50 p-1 text-gray-600">
                            <p class="text-[6.5pt] font-semibold text-gray-700 leading-tight">
                                [ &nbsp; ] {{ $settlement['alternative_option_title'] ?? $settlement['alternative'] }}
                            </p>
                            <p class="text-[6pt] text-gray-500 leading-tight">
                                {{ $settlement['alternative_option_desc'] ?? 'Penyesuaian administratif diselesaikan oleh Divisi Keuangan GPA.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4. KLAUSUL HUKUM PENGESAHAN --}}
            <section class="mt-2 border-l-2 border-black bg-gray-50 py-1 px-2.5 text-[6.5pt] leading-normal text-gray-800">
                <p>
                    <strong class="text-black">Klausul Pengesahan:</strong>
                    {{ $clause }}
                </p>
            </section>
        </div>

        {{-- 5. PENGESAHAN TRIPARTIT LAPANGAN (3 Kolom Formal) --}}
        <footer class="mt-3 border-t-2 border-black pt-2">
            <div class="mb-1.5 flex items-center justify-between">
                <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-black">
                    IV. Pengesahan Tripartit Lapangan &amp; Berita Acara Serah Terima
                </h3>
                <span class="text-[7pt] text-gray-600">
                    Tanggal: <strong>{{ $document['date'] }}</strong>
                </span>
            </div>

            <div class="grid grid-cols-3 gap-2.5 text-center text-[7.5pt]">
                {{-- Kolom 1: Pihak I (Konsumen / Klien) --}}
                <div class="flex h-28 flex-col justify-between border border-gray-400 bg-white p-1.5">
                    <div class="border-b border-gray-200 pb-0.5">
                        <span class="block text-[6.5pt] font-bold uppercase tracking-wider text-gray-700">PIHAK I (KONSUMEN)</span>
                        <p class="font-bold text-black leading-tight">{{ $client['name'] }}</p>
                    </div>

                    <div class="my-auto py-0.5">
                        <span class="inline-block text-[6.5pt] text-gray-400">
                            ( Stempel &amp; Paraf Penerima )
                        </span>
                    </div>

                    <div class="border-t border-gray-300 pt-0.5">
                        <p class="font-bold text-black underline leading-tight">{{ $client['contact'] }}</p>
                        <p class="text-[6.5pt] text-gray-600">{{ $client['role'] }}</p>
                    </div>
                </div>

                {{-- Kolom 2: Pihak II (Pengangkut / Armada) --}}
                <div class="flex h-28 flex-col justify-between border border-gray-400 bg-white p-1.5">
                    <div class="border-b border-gray-200 pb-0.5">
                        <span class="block text-[6.5pt] font-bold uppercase tracking-wider text-gray-700">PIHAK II (PENGANGKUT)</span>
                        <p class="font-bold text-black leading-tight">Armada Logistik GPA</p>
                    </div>

                    <div class="my-auto py-0.5">
                        <span class="inline-block text-[6.5pt] text-gray-400">
                            ( Tanda Tangan Supir )
                        </span>
                    </div>

                    <div class="border-t border-gray-300 pt-0.5">
                        <p class="font-bold text-black underline leading-tight">{{ $driver['name'] }}</p>
                        <p class="text-[6.5pt] text-gray-600">ID: {{ $driver['id'] }} &bull; {{ $driver['vehicle'] }}</p>
                    </div>
                </div>

                {{-- Kolom 3: Pihak III (Verifikator QA GPA) --}}
                <div class="flex h-28 flex-col justify-between border-2 border-black bg-gray-50 p-1.5">
                    <div class="border-b border-gray-300 pb-0.5">
                        <span class="block text-[6.5pt] font-black uppercase tracking-wider text-black">PIHAK III (VERIFIKATOR)</span>
                        <p class="font-bold text-black leading-tight">Quality Assurance Hub GPA</p>
                    </div>

                    <div class="my-auto py-0.5">
                        <span class="inline-block border border-black bg-white px-2 py-0.5 text-[6.5pt] font-black uppercase tracking-wider text-black">
                            TERVERIFIKASI QA GPA
                        </span>
                    </div>

                    <div class="border-t border-gray-300 pt-0.5">
                        <p class="font-bold text-black underline leading-tight">{{ $coordinator['name'] }}</p>
                        <p class="text-[6.5pt] text-gray-600">{{ $coordinator['role'] }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-1.5 text-center text-[6pt] text-gray-500">
                Dokumen Berita Acara Rekonsiliasi resmi Koperasi Produsen Agro Green Pasundan (GPA). Sah bila telah ditandatangani dan berstempel dock.
            </div>
        </footer>
    </main>

</body>

</html>
