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

            .table-container {
                overflow: visible !important;
            }
        }

        @media screen {
            body {
                background: #f1f5ee;
                min-height: 100vh;
            }

            .print-page {
                width: 100%;
                max-width: 210mm;
                min-height: 297mm;
                margin: 16px auto 40px auto;
                background: #ffffff;
                border-radius: 6px;
                box-shadow: 0 16px 40px -12px rgba(20, 58, 0, 0.22), 0 2px 8px -2px rgba(0, 0, 0, 0.05);
                padding: 28px 34px;
            }
        }

        .bap-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 8pt;
        }

        .bap-table th {
            padding: 5px 7px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .bap-table td {
            padding: 4.5px 7px;
            vertical-align: middle;
        }
    </style>
</head>

<body class="flex flex-col items-center px-2 sm:px-4 py-4 font-sans text-ink antialiased">

    {{-- Top Action Bar (Hanya tampil di layar browser) --}}
    <header class="no-print mb-4 flex w-full max-w-[210mm] flex-wrap items-center justify-between gap-3 rounded-xl border border-line-hair bg-surface px-4 py-2.5 shadow-sm">
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
            class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-shell">
            <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5 text-ink-body" />
            <span>{{ $backLabel }}</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="hidden sm:inline-block text-[11px] font-medium text-ink-muted">
                Format Resmi A4 &bull; Legalitas Mutu &amp; Rekonsiliasi Kas GPA
            </span>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 rounded-lg bg-[#15803d] px-4 py-1.5 text-xs font-bold text-white shadow-sm transition-all hover:bg-[#166534] active:scale-95">
                <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                <span>Cetak Berita Acara (Ctrl+P)</span>
            </button>
        </div>
    </header>

    {{-- A4 Printable Sheet --}}
    <main class="print-page box-border flex flex-col justify-between">
        <div>
            {{-- KOP SURAT RESMI KOPERASI GPA --}}
            <header class="border-b-2 border-[#15803d] pb-3">
                <div class="flex items-start justify-between gap-4">
                    {{-- Logo & Identitas Badan Hukum --}}
                    <div class="flex items-start gap-3.5">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border-2 border-[#15803d]/40 bg-[#f4f7f2] p-1.5">
                            <img src="{{ asset('gpaleaves.png') }}" alt="Logo Koperasi GPA" class="h-full w-full object-contain">
                        </div>
                        <div>
                            <p class="text-[7.5pt] font-bold uppercase tracking-wider text-[#15803d]">Koperasi Produsen Hortikultura</p>
                            <h1 class="text-[12pt] font-black uppercase tracking-tight text-ink leading-tight">
                                {{ $brand }}
                            </h1>
                            <p class="text-[7.5pt] font-semibold text-ink-body">
                                Badan Hukum No. AHU-0001248.AH.01.26.TAHUN 2021 &middot; Sentra Logistik Dataran Tinggi Ciwidey
                            </p>
                            <p class="text-[7pt] text-ink-muted leading-tight mt-0.5">
                                Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey, Kab. Bandung 40973 &middot; Hotline/WA: 0812-2323-9133 / 0813-2142-2231
                            </p>
                        </div>
                    </div>

                    {{-- Box Identitas Dokumen Kanan --}}
                    <div class="min-w-[245px] shrink-0 rounded-lg border border-[#15803d] bg-[#f7f9f5] p-2.5 text-right">
                        <div class="flex items-center justify-between border-b border-[#15803d]/30 pb-1">
                            <span class="rounded bg-[#15803d] px-1.5 py-0.5 text-[6.5pt] font-black uppercase tracking-widest text-white">
                                FORM AUDIT QC
                            </span>
                            <span @class([
                                'rounded px-2 py-0.5 text-[7pt] font-black uppercase tracking-wider',
                                'bg-blue-100 text-blue-800 border border-blue-300' => $is_b2b,
                                'bg-emerald-100 text-emerald-800 border border-emerald-300' => ! $is_b2b,
                            ])>
                                {{ $client_type_label ?? ($is_b2b ? 'Mitra B2B (Kontrak / Tempo)' : 'Pelanggan Reguler (Retail / Tunai)') }}
                            </span>
                        </div>

                        <h2 class="mt-1 text-[9.5pt] font-black uppercase leading-tight text-ink">
                            {{ $document['title'] }}
                        </h2>
                        <p class="text-[7pt] font-medium text-ink-muted">(BAP-RET &amp; SELISIH TIMBANGAN)</p>

                        <div class="mt-1.5 flex items-center justify-between border-t border-dotted border-gray-300 pt-1 text-left">
                            <span class="text-[7pt] font-bold text-ink-muted">No. Register:</span>
                            <span class="font-mono text-[8.5pt] font-black text-[#15803d]">{{ $document['number'] }}</span>
                        </div>
                    </div>
                </div>
            </header>

            {{-- 1. DATA REFERENSI SURAT JALAN & LOKASI QC --}}
            <section class="mt-3 rounded-lg border border-gray-300 bg-[#f9fafb] p-2.5 text-[7.5pt]">
                <div class="mb-1.5 flex items-center justify-between border-b border-gray-200 pb-1">
                    <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-[#15803d] flex items-center gap-1.5">
                        <x-gpa.icon name="file-text" class="h-3 w-3" />
                        Data Referensi Pengiriman &amp; Lokasi Serah Terima
                    </h3>
                    <span class="font-mono text-[7pt] text-ink-muted">Waktu Audit: {{ $document['inspection'] }}</span>
                </div>

                <div class="grid grid-cols-2 gap-x-6 gap-y-1">
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-200 py-0.5">
                        <span class="text-ink-muted">Mengacu Surat Jalan:</span>
                        <span class="font-mono font-bold text-ink">{{ $document['delivery'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-200 py-0.5">
                        <span class="text-ink-muted">Nomor Purchase Order (PO):</span>
                        <span class="font-mono font-bold text-ink">{{ $document['order'] }}</span>
                    </div>

                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-200 py-0.5">
                        <span class="text-ink-muted">Penerima / Mitra Klien:</span>
                        <span class="font-bold text-ink">{{ $client['name'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-200 py-0.5">
                        <span class="text-ink-muted">PIC Receiving / Pemeriksa:</span>
                        <span class="font-semibold text-ink">{{ $client['contact'] }} ({{ $client['role'] }})</span>
                    </div>

                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-200 py-0.5">
                        <span class="text-ink-muted">Armada Logistik &amp; Pengemudi:</span>
                        <span class="font-semibold text-ink">{{ $document['vehicle'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between border-b border-dotted border-gray-200 py-0.5">
                        <span class="text-ink-muted">Segmentasi Skema:</span>
                        <span class="font-bold text-[#15803d]">{{ $client_type_label ?? ($is_b2b ? 'Mitra B2B (Kontrak / Tempo)' : 'Pelanggan Reguler (Retail / Tunai)') }}</span>
                    </div>

                    <div class="col-span-2 flex items-baseline justify-between pt-0.5">
                        <span class="text-ink-muted">Titik Bongkar / Dock QC:</span>
                        <span class="font-medium text-ink text-right">{{ $document['location'] }}</span>
                    </div>
                </div>
            </section>

            {{-- 2. TABEL REKONSILIASI FISIK TIMBANGAN & SORTASI MUTU --}}
            <section class="mt-3">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-[#15803d] flex items-center gap-1.5">
                        <x-gpa.icon name="check-circle" class="h-3 w-3" />
                        Rekonsiliasi Fisik Timbangan &amp; Hasil Uji Sortasi Dock
                    </h3>
                    <span class="text-[7pt] text-ink-muted">Satuan Berat Bersih: <strong>Kilogram (kg)</strong></span>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-300">
                    <table class="bap-table">
                        <thead>
                            <tr class="bg-[#1e3a8a] text-white text-[7pt] uppercase tracking-wider font-bold">
                                <th class="w-8 border-r border-blue-900 text-center">No</th>
                                <th class="w-20 border-r border-blue-900 text-left">Kode</th>
                                <th class="border-r border-blue-900 text-left">Nama Komoditas Sayur</th>
                                <th class="w-20 border-r border-blue-900 text-right">Kirim (kg)</th>
                                <th class="w-20 border-r border-blue-900 text-right bg-[#991b1b]">Retur (kg)</th>
                                <th class="w-22 border-r border-blue-900 text-right bg-[#15803d]">Diterima (kg)</th>
                                <th class="w-56 text-left">Alasan Penolakan / Catatan QC Lapangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr @class([
                                    'border-b border-gray-200',
                                    'bg-rose-50/40' => $item['returned'] > 0,
                                    'bg-gray-50/50' => $item['returned'] == 0 && $index % 2 === 1,
                                ])>
                                    <td class="border-r border-gray-200 text-center font-mono text-ink-body">{{ $index + 1 }}</td>
                                    <td class="border-r border-gray-200 font-mono font-bold text-ink-muted">{{ $item['code'] }}</td>
                                    <td class="border-r border-gray-200 font-bold text-ink">{{ $item['name'] }}</td>
                                    <td class="border-r border-gray-200 text-right font-mono font-medium text-ink-body">
                                        {{ number_format($item['shipped'], 1, ',', '.') }}
                                    </td>
                                    <td @class([
                                        'border-r border-gray-200 text-right font-mono font-black',
                                        'text-rose-700 bg-rose-50' => $item['returned'] > 0,
                                        'text-gray-400' => $item['returned'] == 0,
                                    ])>
                                        {{ number_format($item['returned'], 1, ',', '.') }}
                                    </td>
                                    <td class="border-r border-gray-200 text-right font-mono font-bold text-emerald-800 bg-emerald-50/50">
                                        {{ number_format($item['accepted'], 1, ',', '.') }}
                                    </td>
                                    <td class="leading-tight {{ $item['returned'] > 0 ? 'font-semibold text-rose-900' : 'text-ink-muted italic' }}">
                                        {{ $item['reason'] }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Baris Total Akumulasi --}}
                            <tr class="border-t-2 border-gray-400 bg-gray-100 font-bold text-[8pt]">
                                <th colspan="3" class="border-r border-gray-300 py-2 px-2 text-right uppercase tracking-wider text-ink">
                                    Total Akumulasi Fisik Muatan
                                </th>
                                <td class="border-r border-gray-300 py-2 px-2 text-right font-mono text-ink">
                                    {{ number_format($totals['shipped'], 1, ',', '.') }} kg
                                </td>
                                <td class="border-r border-gray-300 py-2 px-2 text-right font-mono font-black text-rose-700 bg-rose-100/60">
                                    {{ number_format($totals['returned'], 1, ',', '.') }} kg
                                </td>
                                <td class="border-r border-gray-300 py-2 px-2 text-right font-mono font-black text-emerald-800 bg-emerald-100/60">
                                    {{ number_format($totals['accepted'], 1, ',', '.') }} kg
                                </td>
                                <td class="py-2 px-2 text-[7pt] font-semibold text-ink-muted">
                                    Validasi timbangan digital dock penerimaan klien
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- 3. KESEPAKATAN KOMPENSASI RETUR & REKONSILIASI KEUANGAN --}}
            <section class="mt-3 rounded-lg border border-gray-300 bg-white p-3 shadow-2xs">
                <div class="mb-2 flex items-center justify-between border-b border-gray-200 pb-1">
                    <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-[#15803d] flex items-center gap-1.5">
                        <x-gpa.icon name="wallet" class="h-3 w-3" />
                        Kesepakatan Kompensasi Retur &amp; Rekonsiliasi Keuangan
                    </h3>
                    <span class="text-[7pt] font-semibold text-ink-muted">Mengacu pada SOP Tata Kelola Koperasi GPA</span>
                </div>

                <div class="grid grid-cols-12 items-stretch gap-3">
                    {{-- Kotak Kiri: Valuasi Finansial Retur --}}
                    <div class="col-span-5 flex flex-col justify-between rounded-lg border border-rose-200 bg-rose-50/50 p-2.5">
                        <div>
                            <span class="text-[6.5pt] font-black uppercase tracking-widest text-rose-800">
                                Total Valuasi Kompensasi Retur
                            </span>
                            <div class="mt-1 flex items-baseline justify-between gap-1">
                                <span class="font-mono text-[7.5pt] text-ink-body">
                                    {{ number_format($totals['returned'], 1, ',', '.') }} kg &times; Rp {{ number_format($settlement['returned_price'], 0, ',', '.') }}
                                </span>
                                <strong class="font-mono text-[11pt] font-black text-rose-700">
                                    Rp {{ number_format($settlement['returned_value'], 0, ',', '.') }}
                                </strong>
                            </div>
                        </div>
                        <div class="mt-2 border-t border-rose-200/80 pt-1 text-[6.5pt] text-rose-800/80">
                            Dasar Valuasi: <strong>{{ $settlement['price_reference'] }}</strong>
                        </div>
                    </div>

                    {{-- Kotak Kanan: Skema Penyelesaian yang Disepakati --}}
                    <div class="col-span-7 flex flex-col justify-between gap-1.5">
                        {{-- Opsi Terpilih --}}
                        <div class="rounded-lg border-2 border-[#15803d] bg-emerald-50/60 p-2">
                            <div class="flex items-start gap-1.5">
                                <span class="inline-flex h-4 w-4 shrink-0 items-center justify-center rounded bg-[#15803d] text-white text-[8pt] font-black">
                                    &check;
                                </span>
                                <div>
                                    <p class="text-[7.5pt] font-black text-emerald-950 leading-tight">
                                        {{ $settlement['selected_option_title'] ?? ('Opsi Terpilih: ' . $settlement['selected_option']) }}
                                    </p>
                                    <p class="mt-0.5 text-[7pt] text-emerald-900 leading-relaxed font-medium">
                                        {{ $settlement['selected_option_desc'] ?? ('Jadwal: ' . $settlement['schedule']) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Opsi Alternatif --}}
                        <div class="rounded-lg border border-gray-200 bg-gray-50/80 p-1.5 text-ink-muted">
                            <p class="text-[7pt] font-semibold text-ink-body leading-tight">
                                {{ $settlement['alternative_option_title'] ?? $settlement['alternative'] }}
                            </p>
                            <p class="mt-0.5 text-[6.5pt] leading-tight text-ink-muted">
                                {{ $settlement['alternative_option_desc'] ?? 'Penyesuaian administratif diselesaikan oleh Divisi Keuangan GPA.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- 4. KLAUSUL HUKUM PENGESAHAN --}}
            <section class="mt-2.5 rounded-lg border-l-4 border-[#15803d] bg-[#f7f9f5] py-1.5 px-3 text-[7pt] leading-relaxed text-ink-body">
                <p>
                    <strong class="text-ink">Klausul Pengesahan Resmi:</strong>
                    {{ $clause }}
                </p>
            </section>
        </div>

        {{-- 5. PENGESAHAN TRIPARTIT LAPANGAN (3 KOLOM RESMI) --}}
        <footer class="mt-4 border-t-2 border-gray-300 pt-2.5">
            <div class="mb-2 flex items-center justify-between">
                <h3 class="text-[7.5pt] font-bold uppercase tracking-wider text-[#15803d] flex items-center gap-1.5">
                    <x-gpa.icon name="shield" class="h-3 w-3" />
                    Pengesahan Tripartit Lapangan &amp; Berita Acara Serah Terima
                </h3>
                <span class="text-[7pt] font-semibold text-ink-muted">
                    Tanggal Pengesahan: <strong>{{ $document['date'] }}</strong>
                </span>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center text-[7.5pt]">
                {{-- Kolom 1: Pihak I (Konsumen / Klien) --}}
                <div class="flex h-32 flex-col justify-between rounded-lg border border-gray-300 bg-white p-2 shadow-2xs">
                    <div class="border-b border-gray-200 pb-1">
                        <span class="block text-[6.5pt] font-black uppercase tracking-wider text-ink-muted">PIHAK I (KONSUMEN)</span>
                        <p class="font-bold text-ink leading-tight">{{ $client['name'] }}</p>
                        <p class="text-[6.5pt] text-ink-muted">Staf Penerima / Purchasing Dock</p>
                    </div>

                    <div class="my-auto py-1">
                        <span class="inline-block rounded border border-dashed border-gray-300 px-3 py-1 text-[6.5pt] font-medium text-gray-400">
                            Stempel Basah &amp; Paraf Penerima
                        </span>
                    </div>

                    <div class="border-t border-dotted border-gray-300 pt-1">
                        <p class="font-bold text-ink underline leading-tight">{{ $client['contact'] }}</p>
                        <p class="text-[6.5pt] text-ink-muted">{{ $client['role'] }}</p>
                    </div>
                </div>

                {{-- Kolom 2: Pihak II (Pengangkut / Armada) --}}
                <div class="flex h-32 flex-col justify-between rounded-lg border border-gray-300 bg-white p-2 shadow-2xs">
                    <div class="border-b border-gray-200 pb-1">
                        <span class="block text-[6.5pt] font-black uppercase tracking-wider text-ink-muted">PIHAK II (DISTRIBUSI)</span>
                        <p class="font-bold text-ink leading-tight">Divisi Logistik &amp; Armada GPA</p>
                        <p class="text-[6.5pt] text-ink-muted">Pengangkut Chiller Dataran Tinggi</p>
                    </div>

                    <div class="my-auto py-1">
                        <span class="inline-block rounded border border-dashed border-gray-300 px-3 py-1 text-[6.5pt] font-medium text-gray-400">
                            Tanda Tangan Pengemudi
                        </span>
                    </div>

                    <div class="border-t border-dotted border-gray-300 pt-1">
                        <p class="font-bold text-ink underline leading-tight">{{ $driver['name'] }}</p>
                        <p class="text-[6.5pt] text-ink-muted">Supir ID: {{ $driver['id'] }} &bull; {{ $driver['vehicle'] }}</p>
                    </div>
                </div>

                {{-- Kolom 3: Pihak III (Verifikator QA GPA) --}}
                <div class="flex h-32 flex-col justify-between rounded-lg border-2 border-[#15803d] bg-[#f7f9f5] p-2 shadow-2xs">
                    <div class="border-b border-[#15803d]/20 pb-1">
                        <span class="block text-[6.5pt] font-black uppercase tracking-wider text-[#15803d]">PIHAK III (VERIFIKATOR)</span>
                        <p class="font-bold text-ink leading-tight">Quality Assurance Hub GPA</p>
                        <p class="text-[6.5pt] text-ink-muted">Sentra Konsolidasi Logistik Ciwidey</p>
                    </div>

                    <div class="my-auto py-1">
                        <span class="inline-block rounded-md border border-[#15803d] bg-white px-2.5 py-0.5 text-[6.5pt] font-black uppercase tracking-wider text-[#15803d] shadow-2xs">
                            &check; TERVERIFIKASI QA GPA
                        </span>
                    </div>

                    <div class="border-t border-dotted border-gray-300 pt-1">
                        <p class="font-bold text-ink underline leading-tight">{{ $coordinator['name'] }}</p>
                        <p class="text-[6.5pt] text-ink-muted">{{ $coordinator['role'] }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-2 text-center text-[6pt] text-gray-400">
                Dokumen ini merupakan Berita Acara Fisik Rekonsiliasi resmi Koperasi Produsen Agro Green Pasundan (GPA). Diterbitkan secara elektronik dan sah tanpa memerlukan legalisir tambahan bila telah berstempel dock.
            </div>
        </footer>
    </main>

</body>

</html>
