<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Resmi Penjualan GPA — {{ $startDate }} s.d {{ $endDate }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 20px 30px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #153a01;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #153a01;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 2px 0 0;
            font-size: 13px;
            color: #333;
            font-weight: normal;
        }
        .header p {
            margin: 3px 0 0;
            font-size: 10px;
            color: #666;
        }
        .meta-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 8px 12px;
            background-color: #f9fbf8;
            border: 1px solid #d4e2cd;
            border-radius: 4px;
        }
        .meta-box div {
            font-size: 11px;
        }
        .summary-cards {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
        }
        .summary-card {
            flex: 1;
            border: 1px solid #ddd;
            padding: 8px 10px;
            border-radius: 4px;
            background-color: #fafafa;
        }
        .summary-card .label {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: bold;
            color: #666;
        }
        .summary-card .value {
            font-size: 13px;
            font-weight: bold;
            color: #153a01;
            margin-top: 3px;
        }
        h3.section-title {
            font-size: 11px;
            text-transform: uppercase;
            color: #153a01;
            margin: 14px 0 6px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #bbb;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background-color: #eef4eb;
            color: #153a01;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .signature-section {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .signature-box {
            width: 220px;
            text-align: center;
        }
        .signature-space {
            height: 60px;
        }
        .no-print {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 4px;
        }
        @media print {
            body { margin: 10mm 15mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" style="padding: 7px 15px; background-color: #153a01; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 12px;">
            Cetak Dokumen / Simpan PDF
        </button>
        <button onclick="window.close()" style="padding: 7px 15px; background-color: #666; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">
            Tutup Jendela
        </button>
        <span style="margin-left: 15px; font-size: 11px; color: #555;">
            Tip: Gunakan opsi "Save as PDF" di dialog print peramban untuk mengunduh arsip PDF resmi.
        </span>
    </div>

    <!-- Kop Surat Koperasi GPA -->
    <div class="header">
        <h1>KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)</h1>
        <h2>Laporan Eksekutif Rekapitulasi Penjualan Komoditas Sayuran</h2>
        <p>Sentra Agribisnis Sayuran Segar Dataran Tinggi — Desa Panundaan, Kec. Ciwidey, Kab. Bandung, Jawa Barat</p>
    </div>

    <!-- Kotak Metadata Laporan -->
    <div class="meta-box">
        <div>
            <strong>Periode Penjualan:</strong> {{ date('d F Y', strtotime($startDate)) }} s.d {{ date('d F Y', strtotime($endDate)) }}
            @if(!empty($clientFilterName))
                <br><strong>Filter Mitra:</strong> {{ $clientFilterName }}
            @endif
        </div>
        <div style="text-align: right;">
            <strong>Dicetak Oleh:</strong> Direktur GPA (H. Ridwan Permana)<br>
            <strong>Waktu Cetak:</strong> {{ date('d F Y, H:i') }} WIB
        </div>
    </div>

    <!-- Ringkasan Finansial Eksekutif -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total Omzet Penjualan</div>
            <div class="value">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card">
            <div class="label">Total Tonase Terjual</div>
            <div class="value">{{ number_format($totalTonnageKg, 1, ',', '.') }} Kg</div>
        </div>
        <div class="summary-card">
            <div class="label">Jumlah Transaksi</div>
            <div class="value">{{ $orders->count() }} Pesanan</div>
        </div>
    </div>

    <!-- Bagian 1: Rekapitulasi Per Komoditas Sayuran -->
    <h3 class="section-title">1. Rekapitulasi Realisasi Penjualan Per Komoditas</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th>Komoditas Sayuran</th>
                <th class="text-right">Total Bobot (Kg)</th>
                <th class="text-right">Rata-rata Harga / Kg</th>
                <th class="text-right">Total Nilai Penjualan</th>
                <th class="text-center" style="width: 80px;">Pangsa Omzet</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commoditySummary as $index => $summary)
                @php
                    $pct = $totalRevenue > 0 ? round(($summary->total_sales / $totalRevenue) * 100, 1) : 0;
                    $avgPrice = $summary->total_weight > 0 ? $summary->total_sales / $summary->total_weight : 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $summary->product->name ?? 'Komoditas #' . $summary->product_id }}</strong></td>
                    <td class="text-right">{{ number_format($summary->total_weight, 1, ',', '.') }} Kg</td>
                    <td class="text-right">Rp {{ number_format($avgPrice, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($summary->total_sales, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $pct }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #999; padding: 12px;">Tidak ada transaksi komoditas pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian 2: Rincian Lengkap Transaksi Pesanan -->
    <h3 class="section-title">2. Rincian Daftar Transaksi Pemesanan</h3>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 25px;">No</th>
                <th>No. Pesanan</th>
                <th>Tanggal</th>
                <th>Klien Mitra Pembeli</th>
                <th>Komoditas & Bobot</th>
                <th>Status</th>
                <th class="text-right">Total Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                @php
                    $amount = (float) ($order->grand_total ?? $order->estimated_total);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-mono" style="font-weight: bold;">{{ $order->order_number }}</td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>{{ $order->user->company_name ?? $order->user->name }}</td>
                    <td>
                        @foreach($order->orderItems as $item)
                            <div>{{ $item->product->name ?? 'Sayuran' }}: {{ number_format($item->actual_net_weight ?? $item->ordered_qty, 1) }} Kg</div>
                        @endforeach
                    </td>
                    <td>{{ $order->status }}</td>
                    <td class="text-right" style="font-weight: bold;">Rp {{ number_format($amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #999; padding: 15px;">Tidak ada transaksi pada periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #fafafa;">
                <td colspan="6" class="text-right">TOTAL KESELURUHAN OMZET:</td>
                <td class="text-right">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Lembar Tanda Tangan Legalitas Pengesahan -->
    <div class="signature-section">
        <div class="signature-box">
            <div>Dibuat oleh,</div>
            <div style="font-weight: bold; margin-top: 2px;">Sekretariat Operasional GPA</div>
            <div class="signature-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">Ibu Rina Marlina</div>
            <div style="font-size: 9px; color: #555;">Staf Administrasi & Verifikasi</div>
        </div>

        <div class="signature-box">
            <div>Ciwidey, {{ date('d F Y') }}</div>
            <div style="font-weight: bold; margin-top: 2px;">Mengetahui & Menyetujui,</div>
            <div class="signature-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">H. Ridwan Permana</div>
            <div style="font-size: 9px; color: #555;">Direktur / Owner Koperasi GPA</div>
        </div>
    </div>

</body>
</html>
