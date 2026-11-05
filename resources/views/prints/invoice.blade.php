<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>INVOICE {{ $invoice['id'] ?? 'GPA' }} &middot; Green Pasundan Agriculture</title>
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

            .a4-canvas {
                margin: 0 auto !important;
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        @media screen {
            body {
                background: #eef2ea;
            }

            .a4-canvas {
                width: 210mm;
                min-height: 297mm;
                margin: 20px auto;
                background: #ffffff;
                box-shadow: 0 12px 36px -12px rgba(20, 58, 0, 0.25);
            }
        }

        .a4-canvas {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #191c18;
            padding: 24px 32px;
            box-sizing: border-box;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #1f2937;
        }

        .stamp-rotate {
            transform: rotate(-7deg);
        }
    </style>
</head>

<body>
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
                    FAKTUR / INVOICE PENAGIHAN
                </span>
            </div>

            <div class="flex items-center gap-2">
                <!-- Toggle Tampilan Format: Kontrak vs Reguler -->
                <div class="inline-flex rounded-lg border border-line bg-white p-0.5 text-xs shadow-sm">
                    <a href="{{ request()->fullUrlWithQuery(['format' => 'kontrak']) }}"
                        class="rounded-md px-2.5 py-1 font-semibold transition-colors {{ !empty($is_contract) ? 'bg-brand text-white shadow-sm' : 'text-ink-body hover:text-ink' }}">
                        Format Kontrak (B2B Split Kuota)
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['format' => 'reguler']) }}"
                        class="rounded-md px-2.5 py-1 font-semibold transition-colors {{ empty($is_contract) ? 'bg-brand text-white shadow-sm' : 'text-ink-body hover:text-ink' }}">
                        Format Reguler (Standar Tunggal)
                    </a>
                </div>

                <!-- Format Struk Kasir Thermal (Khusus Reguler / Walk-In) -->
                <a href="{{ route('prints.thermal-receipt', ['invoice' => $invoice['id']]) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-accent bg-accent/20 px-3 py-1.5 text-xs font-bold text-success-deep shadow-xs transition-colors hover:bg-accent/30">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5 text-success-deep" />
                    Struk Kasir (Thermal 80mm)
                </a>

                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded bg-brand px-4 py-1.5 text-xs font-bold text-white shadow transition-all hover:bg-brand-strong">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    Cetak Dokumen (Ctrl+P)
                </button>
            </div>
        </div>
    </div>

    <main class="a4-canvas">
        <!-- KOP SURAT ASLI GREEN PASUNDAN AGRICULTURE DARI CONTOH RESMI -->
        <header class="flex items-start justify-between border-b-2 border-brand pb-3">
            <div class="flex items-start gap-3">
                <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-14 w-14 shrink-0 object-contain">
                <div>
                    <h1 class="text-base font-black tracking-wider text-brand">
                        GREEN PASUNDAN AGRICULTURE
                    </h1>
                    <p class="text-[7.5pt] font-semibold text-ink leading-tight">
                        {{ $brand ?? 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)' }} &middot; {{ $company['legal_number'] ?? 'Badan Hukum : Nomor AHU-0004853.AH.01.29.TH 2024' }}
                    </p>
                    <p class="text-[7.5pt] leading-tight text-ink-body">
                        {{ $company['address'] ?? 'Kp. Lebakwangi RT. 001 RW. 009 Desa Panundaan Kecamatan Ciwidey Kabupaten Bandung (40973)' }}
                    </p>
                    <p class="text-[7.5pt] leading-tight text-ink-body font-medium">
                        Tlp/ Whatsapp : {{ $company['phone'] ?? '081321422231' }}
                    </p>
                </div>
            </div>

            <!-- Lambang Koperasi Indonesia -->
            <div class="hidden sm:flex flex-col items-center justify-center px-2">
                <svg class="h-12 w-12 text-yellow-600" viewBox="0 0 100 100" fill="currentColor">
                    <circle cx="50" cy="50" r="45" fill="#fef08a" stroke="#ca8a04" stroke-width="3"/>
                    <circle cx="50" cy="50" r="34" fill="#ffffff" stroke="#16a34a" stroke-width="2"/>
                    <path d="M50 20 L58 38 L78 38 L62 50 L68 70 L50 58 L32 70 L38 50 L22 38 L42 38 Z" fill="#ca8a04"/>
                    <circle cx="50" cy="50" r="8" fill="#dc2626"/>
                </svg>
                <span class="mt-0.5 text-[6.5pt] font-extrabold uppercase tracking-widest text-ink">KOPERASI INDONESIA</span>
            </div>

            <!-- Metadata Faktur / Invoice di Sisi Kanan -->
            <div class="text-right">
                <h2 class="text-2xl font-black uppercase tracking-wider text-brand">
                    INVOICE
                </h2>
                <table class="mt-1 text-[8pt] text-left">
                    <tr>
                        <td class="pr-2 font-semibold text-ink-muted">Nomor PO</td>
                        <td class="font-mono font-bold text-ink">: {{ $invoice['po_number'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="pr-2 font-semibold text-ink-muted">Hari/ Tanggal</td>
                        <td class="font-medium text-ink">: {{ $invoice['issued'] ?? date('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td class="pr-2 font-semibold text-ink-muted">Nomor Invoice</td>
                        <td class="font-mono font-bold text-brand">: {{ $invoice['id'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="pr-2 font-semibold text-ink-muted">Jatuh Tempo</td>
                        <td class="font-medium text-ink">: {{ $invoice['due'] ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </header>

        <!-- Kotak Informasi Penerima (To & Office) Sesuai Desain Asli -->
        <div class="mt-3 flex border border-ink text-[8.5pt]">
            <div class="w-1/2 border-r border-ink bg-white px-3 py-1 font-semibold text-ink">
                <span>To : </span>
                <span class="font-bold">{{ $client['to'] ?? ($client['name'] ?? 'Mitra Klien') }}</span>
            </div>
            <div class="w-1/2 bg-white px-3 py-1 font-semibold text-ink">
                <span>Office : </span>
                <span class="font-bold">{{ $client['office'] ?? ($client['name'] ?? 'Perusahaan Klien') }}</span>
            </div>
        </div>

        @if (!empty($is_contract))
            {{-- ========================================================================= --}}
            {{-- FORMAT KLIEN KONTRAK: TABEL 1 (SAYUR GUDANG) & TABEL 2 (SAYUR KONTRAK)    --}}
            {{-- ========================================================================= --}}

            {{-- TABEL 1: SAYUR GUDANG / PASOKAN NON-KONTRAK --}}
            @if (!empty($hasWarehouseItems) && !empty($warehouse['items']))
                <div class="mt-3">
                    <table class="table-bordered text-[7.8pt]">
                        <thead>
                            <tr class="bg-[#1e3a8a] text-white font-bold">
                                <th class="py-1 px-2 text-center" style="width: 14%;">Tanggal</th>
                                <th class="py-1 px-2 text-left" style="width: 44%;">Nama Barang</th>
                                <th class="py-1 px-2 text-right" style="width: 12%;">Qty (Kg)</th>
                                <th class="py-1 px-2 text-right" style="width: 14%;">Harga (Rp)</th>
                                <th class="py-1 px-2 text-right" style="width: 16%;">Jumlah (Rp)</th>
                            </tr>
                            <tr class="bg-gray-100 font-bold text-ink">
                                <td colspan="5" class="py-0.5 px-2 font-bold">{{ $warehouse['title'] ?? 'Sayur Gudang Pasirimpun' }}</td>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentDate = null; @endphp
                            @foreach ($warehouse['items'] as $wItem)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-0.5 px-2 text-center font-mono">
                                        @if ($currentDate !== $wItem['date'])
                                            {{ $wItem['date'] }}
                                            @php $currentDate = $wItem['date']; @endphp
                                        @endif
                                    </td>
                                    <td class="py-0.5 px-2 text-ink font-medium">{{ $wItem['name'] }}</td>
                                    <td class="py-0.5 px-2 text-right font-mono">{{ number_format($wItem['qty'], 1, ',', '.') }}</td>
                                    <td class="py-0.5 px-2 text-right font-mono">{{ number_format($wItem['price'], 0, ',', '.') }}</td>
                                    <td class="py-0.5 px-2 text-right font-mono font-semibold">
                                        <span class="float-left text-ink-muted">Rp</span>
                                        {{ number_format($wItem['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-[#fef08a] font-bold text-ink">
                                <td colspan="4" class="py-1 px-2 text-center uppercase tracking-wider font-extrabold">SUBTOTAL</td>
                                <td class="py-1 px-2 text-right font-mono font-extrabold">
                                    <span class="float-left">Rp</span>
                                    {{ number_format($warehouse['subtotal'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif

            {{-- TABEL 2: SAYUR KONTRAK (ALOKASI KUOTA & REGULER PERSIS SEPERTI DI PDF) --}}
            @if (!empty($hasContractItems) && !empty($contract['pages']))
                <div class="mt-4">
                    <table class="table-bordered text-[7.5pt]">
                        <thead>
                            <tr class="bg-[#be185d] text-white font-bold">
                                <th rowspan="2" class="py-1 px-2 text-center" style="width: 10%;">Tanggal</th>
                                <th rowspan="2" class="py-1 px-2 text-left" style="width: 24%;">Nama Barang</th>
                                <th rowspan="2" class="py-1 px-2 text-center" style="width: 9%;">All Qty Order (Kg)</th>
                                <th colspan="3" class="py-0.5 px-2 text-center bg-[#9d174d]" style="width: 25%;">Kontrak</th>
                                <th colspan="3" class="py-0.5 px-2 text-center bg-[#831843]" style="width: 22%;">Reguler</th>
                                <th rowspan="2" class="py-1 px-2 text-center" style="width: 10%;">TOTAL</th>
                            </tr>
                            <tr class="bg-[#be185d] text-white font-bold">
                                <th class="py-0.5 px-1 text-center" style="width: 7%;">Qty</th>
                                <th class="py-0.5 px-1 text-center" style="width: 8%;">Harga</th>
                                <th class="py-0.5 px-1 text-center" style="width: 10%;">Jumlah</th>
                                <th class="py-0.5 px-1 text-center" style="width: 6%;">Qty</th>
                                <th class="py-0.5 px-1 text-center" style="width: 7%;">Harga</th>
                                <th class="py-0.5 px-1 text-center" style="width: 9%;">Jumlah</th>
                            </tr>
                            <tr class="bg-gray-100 font-bold text-ink">
                                <td colspan="10" class="py-0.5 px-2 font-bold">{{ $contract['title'] ?? 'Sayur Lettuce' }}</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($contract['pages'] as $pageGroup)
                                @foreach ($pageGroup['rows'] as $rIdx => $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-0.5 px-1 text-center font-mono">
                                            {{ $rIdx === 0 ? $pageGroup['date'] : '' }}
                                        </td>
                                        <td class="py-0.5 px-2 font-medium text-ink">{{ $row['name'] }}</td>
                                        <td class="py-0.5 px-1 text-center font-mono font-semibold">{{ number_format($row['all_qty'], 1, ',', '.') }}</td>
                                        <td class="py-0.5 px-1 text-center font-mono">{{ $row['c_qty'] ? number_format($row['c_qty'], 1, ',', '.') : '-' }}</td>
                                        <td class="py-0.5 px-1 text-right font-mono">{{ $row['c_price'] ? 'Rp ' . number_format($row['c_price'], 0, ',', '.') : '-' }}</td>
                                        <td class="py-0.5 px-1 text-right font-mono">{{ $row['c_amount'] ? 'Rp ' . number_format($row['c_amount'], 0, ',', '.') : '-' }}</td>
                                        <td class="py-0.5 px-1 text-center font-mono">{{ $row['r_qty'] ? number_format($row['r_qty'], 1, ',', '.') : '-' }}</td>
                                        <td class="py-0.5 px-1 text-right font-mono">{{ $row['r_price'] ? 'Rp ' . number_format($row['r_price'], 0, ',', '.') : '-' }}</td>
                                        <td class="py-0.5 px-1 text-right font-mono">{{ $row['r_amount'] ? 'Rp ' . number_format($row['r_amount'], 0, ',', '.') : '-' }}</td>
                                        <td class="py-0.5 px-1 text-right font-mono font-bold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <!-- Subtotal per Tanggal -->
                                <tr class="bg-gray-100 font-bold text-ink">
                                    <td colspan="2" class="py-0.5 px-2 text-right">Subtotal {{ $pageGroup['date'] }}:</td>
                                    <td class="py-0.5 px-1 text-center font-mono">{{ number_format($pageGroup['subtotal']['all_qty'], 1, ',', '.') }}</td>
                                    <td class="py-0.5 px-1 text-center font-mono">{{ number_format($pageGroup['subtotal']['c_qty'], 1, ',', '.') }}</td>
                                    <td class="py-0.5 px-1 text-right font-mono">-</td>
                                    <td class="py-0.5 px-1 text-right font-mono">Rp {{ number_format($pageGroup['subtotal']['c_amount'], 0, ',', '.') }}</td>
                                    <td class="py-0.5 px-1 text-center font-mono">{{ number_format($pageGroup['subtotal']['r_qty'], 1, ',', '.') }}</td>
                                    <td class="py-0.5 px-1 text-right font-mono">-</td>
                                    <td class="py-0.5 px-1 text-right font-mono">Rp {{ number_format($pageGroup['subtotal']['r_amount'], 0, ',', '.') }}</td>
                                    <td class="py-0.5 px-1 text-right font-mono font-bold">Rp {{ number_format($pageGroup['subtotal']['total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <!-- Subtotal Kontrak Keseluruhan -->
                            <tr class="bg-[#db2777] font-bold text-white">
                                <td colspan="2" class="py-1 px-2 text-center uppercase tracking-wider font-extrabold">SUBTOTAL</td>
                                <td class="py-1 px-1 text-center font-mono font-extrabold">{{ number_format($contract['grand_subtotal']['all_qty'], 1, ',', '.') }}</td>
                                <td class="py-1 px-1 text-center font-mono font-extrabold">{{ number_format($contract['grand_subtotal']['c_qty'], 1, ',', '.') }}</td>
                                <td class="py-1 px-1 text-right font-mono font-extrabold">Rp</td>
                                <td class="py-1 px-1 text-right font-mono font-extrabold">{{ number_format($contract['grand_subtotal']['c_amount'], 0, ',', '.') }}</td>
                                <td class="py-1 px-1 text-center font-mono font-extrabold">{{ number_format($contract['grand_subtotal']['r_qty'], 1, ',', '.') }}</td>
                                <td class="py-1 px-1 text-right font-mono font-extrabold">Rp</td>
                                <td class="py-1 px-1 text-right font-mono font-extrabold">{{ number_format($contract['grand_subtotal']['r_amount'], 0, ',', '.') }}</td>
                                <td class="py-1 px-1 text-right font-mono font-extrabold">Rp {{ number_format($contract['grand_subtotal']['total'], 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        @else
            {{-- ========================================================================= --}}
            {{-- FORMAT KLIEN REGULER: SATU TABEL STANDAR YANG BERSIH TANPA SPLIT KUOTA    --}}
            {{-- ========================================================================= --}}
            <div class="mt-3">
                <table class="table-bordered text-[8pt]">
                    <thead>
                        <tr class="bg-[#1e3a8a] text-white font-bold">
                            <th class="py-1.5 px-2 text-center" style="width: 5%;">No</th>
                            <th class="py-1.5 px-2 text-center" style="width: 14%;">Tanggal</th>
                            <th class="py-1.5 px-2 text-left" style="width: 45%;">Nama Komoditas / Barang</th>
                            <th class="py-1.5 px-2 text-right" style="width: 12%;">Qty (Kg)</th>
                            <th class="py-1.5 px-2 text-right" style="width: 12%;">Harga (Rp)</th>
                            <th class="py-1.5 px-2 text-right" style="width: 12%;">Jumlah (Rp)</th>
                        </tr>
                        <tr class="bg-gray-100 font-bold text-ink">
                            <td colspan="6" class="py-1 px-2 font-bold">{{ $regular['title'] ?? 'Daftar Pasokan Komoditas Reguler' }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($regular['items'] ?? [] as $idx => $rItem)
                            <tr class="hover:bg-gray-50">
                                <td class="py-1 px-2 text-center font-mono">{{ $idx + 1 }}</td>
                                <td class="py-1 px-2 text-center font-mono">{{ $rItem['date'] }}</td>
                                <td class="py-1 px-2 text-ink font-semibold">{{ $rItem['name'] }}</td>
                                <td class="py-1 px-2 text-right font-mono">{{ number_format($rItem['qty'], 1, ',', '.') }}</td>
                                <td class="py-1 px-2 text-right font-mono">{{ number_format($rItem['price'], 0, ',', '.') }}</td>
                                <td class="py-1 px-2 text-right font-mono font-semibold">
                                    <span class="float-left text-ink-muted">Rp</span>
                                    {{ number_format($rItem['amount'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-[#fef08a] font-bold text-ink">
                            <td colspan="5" class="py-1 px-2 text-center uppercase tracking-wider font-extrabold">SUBTOTAL</td>
                            <td class="py-1 px-2 text-right font-mono font-extrabold">
                                <span class="float-left">Rp</span>
                                {{ number_format($regular['subtotal'] ?? $grand_total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        {{-- BARIS TOTAL TAGIHAN GABUNGAN --}}
        <div class="mt-3">
            <table class="table-bordered text-[8.5pt]">
                <tfoot>
                    @if (($shipping_cost ?? 0) > 0 || ($tax_amount ?? 0) > 0)
                        <tr class="bg-gray-50 text-ink">
                            <td class="py-1 px-3 text-right font-medium" style="width: 75%;">
                                Subtotal Komoditas
                            </td>
                            <td class="py-1 px-3 text-right font-mono font-semibold" style="width: 25%;">
                                Rp {{ number_format($subtotal ?? ($grand_total - ($shipping_cost ?? 0) - ($tax_amount ?? 0)), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if (($shipping_cost ?? 0) > 0)
                        <tr class="bg-gray-50 text-ink">
                            <td class="py-1 px-3 text-right font-medium" style="width: 75%;">
                                Biaya Distribusi &amp; Logistik Armada Chiller
                            </td>
                            <td class="py-1 px-3 text-right font-mono font-semibold" style="width: 25%;">
                                Rp {{ number_format($shipping_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if (isset($tax_label))
                        <tr class="bg-gray-50 text-ink">
                            <td class="py-1 px-3 text-right font-medium" style="width: 75%;">
                                {{ $tax_label }}
                            </td>
                            <td class="py-1 px-3 text-right font-mono font-semibold" style="width: 25%;">
                                @if (($tax_amount ?? 0) > 0)
                                    Rp {{ number_format($tax_amount, 0, ',', '.') }}
                                @else
                                    Rp 0 (Bebas)
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr class="bg-[#1e3a8a] text-white font-black">
                        <td class="py-2 px-3 text-right uppercase tracking-widest font-black" style="width: 75%;">
                            TOTAL TAGIHAN
                        </td>
                        <td class="py-2 px-3 text-right font-mono font-black text-[10.5pt] bg-[#172554]" style="width: 25%;">
                            Rp {{ number_format($grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- FOOTER PEMBAYARAN & TANDA TANGAN ASLI SESUAI DOKUMEN GPA -->
        <footer class="mt-6 flex items-start justify-between border-t border-ink pt-3 text-[8pt]">
            <!-- Instruksi Pembayaran Bank BCA Resmi -->
            <div class="w-7/12 leading-relaxed">
                <p class="font-semibold text-ink">Setelah tagihan diterima, silahkan transfer ke:</p>
                <div class="my-1 rounded border border-gray-300 bg-gray-50 p-2 font-mono">
                    <p class="font-bold text-ink">Rekening BCA {{ $company['bank_account'] ?? '6768489174' }}</p>
                    <p class="font-semibold text-brand">A.N {{ $company['bank_holder'] ?? 'Green Pasundan Agriculture' }}</p>
                </div>
                <p class="text-ink-body">Silakan kirimkan informasi jika pembayaran telah dilakukan</p>
                <p class="font-semibold text-brand">Terima kasih atas kerjasamanya</p>
            </div>

            <!-- Tanda Tangan & Cap Stempel Bulat Koperasi GPA Asli -->
            <div class="relative w-4/12 text-center">
                <p class="font-semibold text-ink">Hormat Kami</p>
                <p class="text-[7.5pt] font-medium text-ink-body">Koperasi Produsen Green Pasundan Agriculture</p>

                <!-- Stamp Bulat Koperasi GPA Asli -->
                <div class="relative my-1 flex items-center justify-center">
                    <svg class="h-24 w-24 text-blue-700/85 stamp-rotate" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="100" cy="100" r="95" stroke="currentColor" stroke-width="3" stroke-dasharray="6 3"/>
                        <circle cx="100" cy="100" r="88" stroke="currentColor" stroke-width="2"/>
                        <circle cx="100" cy="100" r="58" stroke="currentColor" stroke-width="1.5"/>
                        <path id="stamp-top-p" d="M 24,100 A 76,76 0 1,1 176,100" fill="none"/>
                        <path id="stamp-bot-p" d="M 176,100 A 76,76 0 0,1 24,100" fill="none"/>
                        <text fill="currentColor" font-size="9" font-weight="bold" letter-spacing="2">
                            <textPath href="#stamp-top-p" startOffset="50%" text-anchor="middle">
                                KOPERASI PRODUSEN GREEN PASUNDAN
                            </textPath>
                        </text>
                        <text fill="currentColor" font-size="8.5" font-weight="bold" letter-spacing="3">
                            <textPath href="#stamp-bot-p" startOffset="50%" text-anchor="middle">
                                CIWIDEY BANDUNG
                            </textPath>
                        </text>
                        <polygon points="100,72 103,82 114,82 105,89 108,99 100,93 92,99 95,89 86,82 97,82" fill="currentColor"/>
                        <text x="100" y="118" text-anchor="middle" fill="currentColor" font-size="11" font-weight="bold" letter-spacing="1">GPA</text>
                        <text x="100" y="130" text-anchor="middle" fill="currentColor" font-size="6.5" font-weight="semibold">RESMI</text>
                    </svg>
                </div>

                <p class="font-extrabold uppercase tracking-wider text-ink underline text-[9pt]">
                    {{ $company['signer_name'] ?? 'AGUS DIKI PERMANA' }}
                </p>
                <p class="text-[7pt] text-ink-muted">Ketua Pengurus Koperasi GPA</p>
            </div>
        </footer>
    </main>
</body>

</html>
