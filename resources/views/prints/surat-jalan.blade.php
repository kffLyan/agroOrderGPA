<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan {{ $doc['number'] ?? 'SJ-GPA' }} &middot; Green Pasundan Agriculture</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A5 portrait;
            margin: 6mm 8mm;
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

            .surat-canvas {
                margin: 0 auto !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                background: transparent !important;
            }
        }

        @media screen {
            body {
                background: #ecefe6;
            }

            .surat-canvas {
                width: 148mm; /* Ukuran Resmi Nota NCR / Half-Folio A5 */
                min-height: 210mm;
                margin: 20px auto;
                background: #fbf8ea; /* Warna Khas Kertas Nota NCR Asli di Foto */
                box-shadow: 0 12px 36px -12px rgba(20, 58, 0, 0.35);
                border: 1px solid #e5e0c5;
            }

            .surat-canvas.mode-white {
                background: #ffffff !important;
                border: 1px solid #d1d5db;
            }
        }

        .surat-canvas {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #191c18;
            padding: 16px 20px;
            box-sizing: border-box;
        }

        /* Zebra Striping Sesuai Format Nota Fisik Asli */
        .zebra-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #1f2937;
        }

        .zebra-table th {
            background-color: #27272a;
            color: #ffffff;
            border: 1px solid #1f2937;
            padding: 5px 8px;
            font-size: 8.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .zebra-table td {
            border: 1px solid #9ca3af;
            padding: 6px 10px;
            font-size: 9pt;
            height: 25px;
        }

        .zebra-table tr:nth-child(even) td {
            background-color: #e5e7eb; /* Abu-abu striping */
        }

        .zebra-table tr:nth-child(odd) td {
            background-color: #fefce8; /* Krem/kuning muda striping */
        }
    </style>
</head>

<body x-data="{ paperMode: 'ncr' }">
    <!-- Floating Toolbar (No Print) -->
    <div class="no-print sticky top-0 z-30 border-b border-line bg-surface/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-[210mm] items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-1.5 rounded border border-line-strong bg-white px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-sunken">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    Kembali
                </a>
                <span class="rounded bg-brand/10 px-2.5 py-1 text-xs font-bold text-brand">
                    SURAT JALAN PENGIRIMAN
                </span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Toggle Tampilan Kertas -->
                <button type="button" @click="paperMode = (paperMode === 'ncr' ? 'white' : 'ncr')"
                    class="rounded border border-line bg-white px-3 py-1.5 text-xs font-medium text-ink hover:bg-surface-sunken">
                    Mode: <span class="font-bold" x-text="paperMode === 'ncr' ? 'Kertas Nota NCR Asli (Kuning)' : 'Kertas Putih Standar'"></span>
                </button>

                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded bg-brand px-4 py-1.5 text-xs font-bold text-white shadow transition-all hover:bg-brand-strong">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    Cetak Surat Jalan (Ctrl+P)
                </button>
            </div>
        </div>
    </div>

    <main class="surat-canvas" :class="{ 'mode-white': paperMode === 'white' }">
        <!-- KOP SURAT ASLI GREEN PASUNDAN AGRICULTURE DARI FOTO RESMI -->
        <header class="flex items-start justify-between border-b-2 border-ink pb-2.5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-14 w-14 shrink-0 object-contain">
            </div>

            <div class="flex-1 text-center px-2">
                <p class="text-[8pt] font-extrabold uppercase tracking-widest text-ink">
                    {{ $brand ?? 'KOPERASI PRODUSEN GREEN PASUNDAN Agriculture (GPA)' }}
                </p>
                <h1 class="text-[13pt] font-black uppercase tracking-wider text-ink -mt-0.5">
                    GREEN PASUNDAN AGRICULTURE
                </h1>
                <p class="text-[7.5pt] font-semibold text-ink leading-tight">
                    {{ $company['legal_number'] ?? 'Badan Hukum : Nomor AHU-0004853.AH.01.29.TH 2024' }}
                </p>
                <p class="text-[7.5pt] font-medium text-ink leading-tight">
                    {{ $company['address'] ?? 'Kp. Batukasur RT. 001 RW. 019 Desa Panundaan Kec. Ciwidey' }}
                </p>
                <p class="text-[7.5pt] font-medium text-ink leading-tight">
                    {{ $company['regency'] ?? 'Kabupaten Bandung (40973)' }} Tlp/ Whatsapp : {{ $company['phone'] ?? '0812-2323-9133 / 0813-2142-2231' }}
                </p>
            </div>

            <!-- Lambang Koperasi Indonesia di Kanan -->
            <div class="flex items-center justify-end">
                <svg class="h-14 w-14 text-yellow-600 shrink-0" viewBox="0 0 100 100" fill="currentColor">
                    <circle cx="50" cy="50" r="46" fill="#fef08a" stroke="#ca8a04" stroke-width="3"/>
                    <circle cx="50" cy="50" r="35" fill="#ffffff" stroke="#16a34a" stroke-width="2"/>
                    <path d="M50 18 L59 36 L79 36 L63 49 L69 69 L50 57 L31 69 L37 49 L21 36 L41 36 Z" fill="#ca8a04"/>
                    <circle cx="50" cy="50" r="9" fill="#dc2626"/>
                </svg>
            </div>
        </header>

        <!-- Garis Pemisah Ganda Khas Surat Jalan -->
        <div class="border-b border-ink mt-0.5 mb-3"></div>

        <!-- Metadata Surat Jalan Sesuai Format Dokumen Asli -->
        <section class="flex items-start justify-between text-[9pt]">
            <div class="w-7/12 space-y-1">
                <div class="flex items-baseline gap-2">
                    <span class="text-base font-black tracking-wider text-ink">SURAT JALAN</span>
                    <span class="text-ink font-semibold">No:</span>
                    <span class="font-mono font-bold text-ink underline decoration-dotted underline-offset-4">
                        {{ $doc['number'] ?? '-' }}
                    </span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="w-24 text-ink font-semibold">Kepada Yth</span>
                    <span class="text-ink">:</span>
                    <span class="font-bold text-ink text-[10.5pt]">
                        {{ $doc['to'] ?? 'Mitra Klien GPA' }}
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="w-24 text-ink font-semibold">Alamat</span>
                    <span class="text-ink">:</span>
                    <span class="text-ink font-medium">
                        {{ $doc['address'] ?? 'Alamat Tujuan Pengiriman' }}
                    </span>
                </div>
                @if (isset($doc['vehicle']))
                    <div class="flex items-baseline gap-2">
                        <span class="w-24 text-ink-muted text-[8pt]">Armada/Nopol</span>
                        <span class="text-ink-muted text-[8pt]">:</span>
                        <span class="text-ink-muted font-mono text-[8pt]">
                            {{ $doc['vehicle'] }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Tanggal Pengiriman di Kanan Sejajar Format Asli -->
            <div class="w-5/12 text-right">
                <div class="inline-flex items-baseline gap-2">
                    <span class="text-ink font-semibold">Tanggal :</span>
                    <span class="font-bold text-[10.5pt] border-b border-ink px-2 font-mono">
                        {{ $doc['date'] ?? date('d-m-Y') }}
                    </span>
                </div>
                @if (isset($doc['po']))
                    <p class="text-[7.5pt] text-ink-muted mt-1 font-mono">Ref PO: {{ $doc['po'] }}</p>
                @endif
            </div>
        </section>

        <!-- TABEL MUATAN ZEBRA 3 KOLOM SESUAI BENTUK SURAT JALAN ASLI -->
        <section class="mt-4">
            <table class="zebra-table">
                <thead>
                    <tr>
                        <th style="width: 8%;" class="text-center">No</th>
                        <th style="width: 72%;" class="text-left">Nama Barang</th>
                        <th style="width: 20%;" class="text-center">Qty (Kg)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $itemsList = $items ?? [];
                        $maxRows = max(10, count($itemsList)); // 10 baris nota NCR standar
                    @endphp

                    @for ($i = 0; $i < $maxRows; $i++)
                        @php $item = $itemsList[$i] ?? null; @endphp
                        <tr>
                            <td class="text-center font-semibold text-ink">
                                {{ $item ? ($item['no'] ?? ($i + 1)) : '' }}
                            </td>
                            <td class="font-medium text-ink">
                                @if ($item)
                                    <span class="font-semibold text-ink text-[9pt]">{{ $item['name'] }}</span>
                                @else
                                    &nbsp;
                                @endif
                            </td>
                            <td class="text-center font-bold text-ink">
                                @if ($item)
                                    <span class="font-mono text-[9.5pt]">{{ number_format($item['qty'], 1, ',', '.') }}</span>
                                @else
                                    &nbsp;
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </section>

        <!-- 3 KOLOM TANDA TANGAN (Tanda Terima, Kernet, Supir) -->
        <section class="mt-4 flex items-start justify-between border-t border-line-hair pt-2">
            <!-- Kolom 1: Tanda Terima -->
            <div class="w-3/12 text-center">
                <p class="text-[8.5pt] font-bold text-ink">{{ $signatures['receiver']['label'] ?? 'Tanda Terima' }}</p>
                <div class="relative mt-1 h-14 flex flex-col items-center justify-end">
                    <span class="text-[8pt] text-ink-muted mb-0.5 font-mono">
                        Tgl: {{ $signatures['receiver']['date'] ?? date('d/m/y') }}
                    </span>
                    <div class="w-full border-b border-ink mt-1"></div>
                </div>
                <p class="mt-1 text-[7.5pt] font-semibold text-ink">
                    {{ $signatures['receiver']['name'] ?? 'Penerima Barang' }}
                </p>
                <p class="text-[6.5pt] text-ink-muted">Nama &amp; Cap Toko/Klien</p>
            </div>

            <!-- Kolom 2: Kernet -->
            <div class="w-3/12 text-center">
                <p class="text-[8.5pt] font-bold text-ink">{{ $signatures['co_driver']['label'] ?? 'Kernet Armada' }}</p>
                <div class="mt-1 h-14 flex flex-col items-center justify-end">
                    <div class="w-full border-b border-ink"></div>
                </div>
                <p class="mt-1 text-[7.5pt] font-semibold text-ink">
                    {{ $signatures['co_driver']['name'] ?? 'Kernet Logistik' }}
                </p>
                <p class="text-[6.5pt] text-ink-muted">(Pembantu Pengemudi)</p>
            </div>

            <!-- Kolom 3: Supir -->
            <div class="w-3/12 text-center">
                <p class="text-[8.5pt] font-bold text-ink">{{ $signatures['driver']['label'] ?? 'Supir Armada' }}</p>
                <div class="relative mt-1 h-14 flex flex-col items-center justify-end">
                    <div class="w-full border-b border-ink mt-1"></div>
                </div>
                <p class="mt-1 text-[7.5pt] font-semibold text-ink">
                    {{ $signatures['driver']['name'] ?? 'Supir Ekspedisi GPA' }}
                </p>
                <p class="text-[6.5pt] text-ink-muted">(Pengemudi Armada GPA)</p>
            </div>
        </section>

        <!-- FOOTER CATATAN KAKI DARI DOKUMEN RESMI -->
        <footer class="mt-4 border-t border-dashed border-gray-400 pt-1.5 text-center">
            <p class="text-[7.5pt] italic font-semibold text-ink-body">
                "{{ $footer ?? 'Terimakasih atas kepercayaan anda, semoga tetap menjadi pelanggan setia' }}"
            </p>
        </footer>
    </main>
</body>

</html>
