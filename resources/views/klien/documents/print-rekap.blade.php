<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekapitulasi Faktur &amp; Tagihan Resmi — {{ $user->company_name ?: $user->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000000;
            background-color: #ffffff;
            margin: 0;
            padding: 16px 20px;
            line-height: 1.35;
        }

        /* Panel Kontrol Layar (Tidak Tercetak) */
        .no-print {
            background-color: #1a1a1a;
            color: #ffffff;
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25);
        }

        .no-print .btn-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-print {
            background-color: #ffffff;
            color: #000000;
            border: 1px solid #000000;
            padding: 7px 18px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.15s ease;
        }

        .btn-print:hover {
            background-color: #e5e5e5;
        }

        .btn-close {
            background-color: transparent;
            color: #ffffff;
            border: 1px solid #777777;
            padding: 7px 14px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-close:hover {
            background-color: #333333;
        }

        .print-tip {
            font-size: 11px;
            color: #cccccc;
        }

        /* Kop Surat Resmi Monokrom Formal */
        .kop-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000000;
            padding-bottom: 10px;
            margin-bottom: 12px;
            position: relative;
        }

        .kop-header::after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #000000;
        }

        .kop-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .kop-logo-box {
            width: 44px;
            height: 44px;
            border: 2px solid #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000000;
            font-weight: 900;
            font-size: 20px;
            letter-spacing: 1px;
        }

        .kop-info h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            color: #000000;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .kop-info h2 {
            margin: 2px 0 0;
            font-size: 10.5px;
            font-weight: 700;
            color: #333333;
            letter-spacing: 0.3px;
        }

        .kop-info p {
            margin: 3px 0 0;
            font-size: 9px;
            color: #444444;
            line-height: 1.3;
        }

        .kop-cert {
            text-align: right;
            border-left: 1px solid #999999;
            padding-left: 14px;
        }

        .kop-cert .cert-chip {
            display: inline-block;
            border: 1px solid #000000;
            padding: 2px 7px;
            font-weight: 700;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
        }

        .kop-cert p {
            margin: 4px 0 0;
            font-size: 8.5px;
            color: #444444;
            font-family: monospace;
        }

        /* Dokumen Title & Profil */
        .doc-title-bar {
            text-align: center;
            margin-bottom: 12px;
            padding-top: 4px;
        }

        .doc-title-bar h2 {
            margin: 0;
            font-size: 13.5px;
            font-weight: 900;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .doc-title-bar p {
            margin: 3px 0 0;
            font-size: 9px;
            color: #444444;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .meta-card {
            border: 1px solid #000000;
            background-color: #ffffff;
            padding: 8px 12px;
        }

        .meta-card-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000000;
            border-bottom: 1px solid #888888;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 2px 0;
            font-size: 9px;
            vertical-align: top;
        }

        .meta-table td.label {
            width: 32%;
            color: #444444;
        }

        .meta-table td.value {
            font-weight: 700;
            color: #000000;
        }

        /* Kartu Metrik Ringkasan Monokrom */
        .summary-tiles {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .tile {
            border: 1px solid #000000;
            background-color: #ffffff;
            padding: 8px 10px;
            border-left: 4px solid #000000;
        }

        .tile .tile-label {
            font-size: 8.5px;
            text-transform: uppercase;
            font-weight: 700;
            color: #444444;
            letter-spacing: 0.4px;
        }

        .tile .tile-val {
            font-size: 13.5px;
            font-weight: 900;
            color: #000000;
            margin-top: 3px;
            font-family: "Courier New", Courier, monospace;
        }

        .tile .tile-sub {
            font-size: 8px;
            color: #555555;
            margin-top: 1px;
        }

        /* Tabel Data Transaksi Monokrom Tegas */
        .table-wrap {
            margin-bottom: 12px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #333333;
            padding: 5px 6px;
            text-align: left;
        }

        table.data-table th {
            background-color: #f2f2f2;
            color: #000000;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.3px;
        }

        table.data-table tfoot td {
            background-color: #f2f2f2;
            font-weight: 900;
            color: #000000;
            border-top: 2px solid #000000;
            font-size: 9.5px;
        }

        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-mono { font-family: "Courier New", Courier, monospace; }

        .status-badge {
            display: inline-block;
            padding: 1.5px 5px;
            border: 1px solid #000000;
            font-weight: 700;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background-color: #ffffff;
            color: #000000;
        }

        /* Rekening Resmi & Ketentuan Pembayaran */
        .notes-box {
            border: 1px solid #000000;
            background-color: #ffffff;
            padding: 8px 12px;
            margin-bottom: 16px;
        }

        .notes-box h4 {
            margin: 0 0 3px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #000000;
            border-bottom: 1px dashed #777777;
            padding-bottom: 2px;
        }

        .notes-box p {
            margin: 0 0 3px;
            font-size: 8.5px;
            color: #333333;
            line-height: 1.35;
        }

        /* Lembar Tanda Tangan */
        .signatures {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .sig-block {
            text-align: center;
            border: 1px solid #555555;
            padding: 8px 6px;
            background-color: #ffffff;
        }

        .sig-role {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555555;
            margin-bottom: 2px;
        }

        .sig-org {
            font-size: 9px;
            font-weight: 800;
            color: #000000;
        }

        .sig-space {
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 4px 0;
            position: relative;
        }

        .sig-stamp {
            border: 1px solid #000000;
            color: #000000;
            padding: 2px 8px;
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sig-name {
            font-size: 9px;
            font-weight: 800;
            color: #000000;
            border-top: 1px solid #000000;
            padding-top: 3px;
            display: inline-block;
            min-width: 140px;
        }

        .sig-title {
            font-size: 7.5px;
            color: #555555;
            margin-top: 1px;
        }

        /* Footer Cetak */
        .doc-footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #000000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 8px;
            color: #444444;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- Panel Kontrol Layar (Tidak Tercetak) --}}
    <div class="no-print">
        <div class="print-tip">
            <strong>Format Monokrom Cetak Resmi (Black &amp; White High-Contrast):</strong>
            Didesain khusus untuk printer laserjet kantor, arsip legal, dan rekonsiliasi akuntansi. Pilih <em>"Save as PDF"</em> pada jendela cetak browser.
        </div>
        <div class="btn-group">
            <button onclick="window.print()" class="btn-print">
                &#128438; Cetak Dokumen / Simpan PDF
            </button>
            <a href="{{ route('klien.documents') }}" class="btn-close">
                Kembali ke Pusat Dokumen
            </a>
        </div>
    </div>

    {{-- Kop Surat Resmi GPA Monokrom --}}
    <header class="kop-header">
        <div class="kop-brand">
            <div class="kop-logo-box">GPA</div>
            <div class="kop-info">
                <h1>PT GREEN PASUNDAN AGRICULTURE</h1>
                <h2>Divisi Administrasi Penagihan, Faktur Konsolidasi &amp; Pajak Pertanian B2B</h2>
                <p>
                    Sentra Logistik Lembang: Jl. Raya Kolonel Masturi No. 108, Lembang, Bandung Barat 40391<br>
                    Website: agroorder.gpa.co.id &bull; Surel: finance@greenpasundan.id &bull; Hotline Keuangan: +62 811-2345-6789
                </p>
            </div>
        </div>
        <div class="kop-cert">
            <span class="cert-chip">Rekonsiliasi Sah</span>
            <p>NPWP: 48.912.840.1-421.000</p>
            <p>Giro BCA: 840-552-1920</p>
        </div>
    </header>

    {{-- Judul Dokumen --}}
    <div class="doc-title-bar">
        <h2>REKAPITULASI RESMI FAKTUR KONSOLIDASI &amp; STATUS TAGIHAN TEMPO</h2>
        <p>Arsip Digital Penagihan Bulanan Sah, Berita Acara Timbang Gudang &amp; Bukti Rekonsiliasi Bank PT Green Pasundan Agriculture</p>
    </div>

    {{-- Identitas Mitra & Legalitas Dokumen --}}
    <section class="meta-grid">
        <div class="meta-card">
            <div class="meta-card-title">Profil Perusahaan Mitra / Klien Penerima Tagihan</div>
            <table class="meta-table">
                <tr>
                    <td class="label">Nama Perusahaan</td>
                    <td class="value">: {{ $user->company_name ?: $user->name }}</td>
                </tr>
                <tr>
                    <td class="label">PIC Finance / Purchasing</td>
                    <td class="value">: {{ $user->pic_name ?: $user->name }} ({{ $user->pic_phone ?: ($user->phone ?: '-') }})</td>
                </tr>
                <tr>
                    <td class="label">Alamat Kantor / Penagihan</td>
                    <td class="value">: {{ $user->address ?: 'Alamat Terdaftar Perusahaan Mitra' }}</td>
                </tr>
                <tr>
                    <td class="label">Fasilitas Kredit Tempo</td>
                    <td class="value">: {{ $user->client_type === 'B2B_KONTRAK' ? 'Term of Payment (TOP 30 Hari B2B)' : 'Reguler Direct Transfer' }}</td>
                </tr>
            </table>
        </div>

        <div class="meta-card">
            <div class="meta-card-title">Parameter Arsip &amp; Dokumen</div>
            <table class="meta-table">
                <tr>
                    <td class="label">Nomor Registrasi</td>
                    <td class="value font-mono">: {{ $docNumber }}</td>
                </tr>
                <tr>
                    <td class="label">Tanggal Cetak</td>
                    <td class="value">: {{ $printDate }}</td>
                </tr>
                <tr>
                    <td class="label">Total Dokumen Faktur</td>
                    <td class="value">: {{ $invoices->count() }} Faktur Konsolidasi</td>
                </tr>
                <tr>
                    <td class="label">Keabsahan Dokumen</td>
                    <td class="value">: <strong>TERVALIDASI SISTEM KEUANGAN GPA</strong></td>
                </tr>
            </table>
        </div>
    </section>

    {{-- Kartu Ringkasan Metrik Monokrom --}}
    <section class="summary-tiles">
        <div class="tile">
            <div class="tile-label">Total Tagihan Terbit</div>
            <div class="tile-val">Rp {{ number_format($totalGrand, 0, ',', '.') }}</div>
            <div class="tile-sub">Akumulasi Faktur Konsolidasi</div>
        </div>
        <div class="tile">
            <div class="tile-label">Tagihan Lunas Sah</div>
            <div class="tile-val">Rp {{ number_format($paidTotal, 0, ',', '.') }}</div>
            <div class="tile-sub">Terverifikasi Rekening Koran</div>
        </div>
        <div class="tile">
            <div class="tile-label">Sisa Tagihan Berjalan</div>
            <div class="tile-val">
                Rp {{ number_format($unpaidTotal, 0, ',', '.') }}
            </div>
            <div class="tile-sub">{{ $unpaidTotal > 0 ? 'Menunggu Pelunasan' : 'Nihil / Tertib Bayar' }}</div>
        </div>
        <div class="tile">
            <div class="tile-label">Realisasi Tonase Netto</div>
            <div class="tile-val">{{ number_format($totalNetto, 1, ',', '.') }} Kg</div>
            <div class="tile-sub">Bobot Sah Timbangan Digital</div>
        </div>
    </section>

    {{-- Tabel Data Faktur Konsolidasi --}}
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 25px;">No</th>
                    <th style="width: 110px;">No. Faktur</th>
                    <th style="width: 90px;">Tanggal Terbit</th>
                    <th>Periode Konsolidasi Transaksi</th>
                    <th class="text-center" style="width: 90px;">Surat Jalan</th>
                    <th class="text-right" style="width: 95px;">Netto Sah (Kg)</th>
                    <th class="text-right" style="width: 115px;">Total Nilai (Rp)</th>
                    <th style="width: 95px;">Jatuh Tempo</th>
                    <th class="text-center" style="width: 105px;">Status Pelunasan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $idx => $inv)
                    @php
                        $nettoKg = (float) $inv->orders->sum(function ($o) {
                            return $o->orderItems->sum(fn ($i) => $i->actual_net_weight ?? $i->ordered_qty);
                        });

                        $statusLabel = match ($inv->status) {
                            'PAID' => 'LUNAS SAH',
                            'OVERDUE' => 'OVERDUE (JATUH TEMPO)',
                            'PARTIAL' => 'SEBAGIAN',
                            default => 'MENUNGGU BAYAR',
                        };
                    @endphp
                    <tr>
                        <td class="text-center font-mono">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="font-mono">{{ $inv->invoice_number }}</strong>
                        </td>
                        <td>{{ $inv->created_at->format('d/m/Y') }}</td>
                        <td>
                            {{ $inv->period_start ? $inv->period_start->format('d M Y') : '-' }} s.d {{ $inv->period_end ? $inv->period_end->format('d M Y') : '-' }}
                        </td>
                        <td class="text-center font-mono font-medium">
                            {{ $inv->orders->count() }} Dokumen SJ
                        </td>
                        <td class="text-right font-mono font-bold">
                            {{ number_format($nettoKg, 1, ',', '.') }}
                        </td>
                        <td class="text-right font-mono font-bold">
                            Rp {{ number_format($inv->grand_total, 0, ',', '.') }}
                        </td>
                        <td class="font-mono">
                            {{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="status-badge">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 16px; color: #555; font-style: italic;">
                            Belum ada catatan faktur konsolidasi resmi yang diterbitkan untuk akun ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN FAKTUR:</td>
                    <td class="text-right font-mono">{{ number_format($totalNetto, 1, ',', '.') }} Kg</td>
                    <td class="text-right font-mono">Rp {{ number_format($totalGrand, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Rekening Resmi & Ketentuan Pembayaran --}}
    <section class="notes-box">
        <h4>Informasi Pembayaran &amp; Rekening Resmi Settlement GPA:</h4>
        <p>
            Pembayaran tagihan resmi transfer antar-bank atau giro korporat ditujukan ke rekening utama perbankan GPA:<br>
            <strong>Bank Central Asia (BCA) — No. Rekening: 840-552-1920</strong> a.n. <strong>PT Green Pasundan Agriculture</strong> (Cabang Setiabudi Bandung).
        </p>
        <p style="font-size: 8px; color: #555;">
            * Mohon mencantumkan Nomor Faktur di kolom Berita Acara Transfer untuk kelancaran rekonsiliasi otomatis perbankan. Slip transfer dapat diunggah melalui menu Bukti Bayar pada portal AgroOrder GPA.
        </p>
    </section>

    {{-- Lembar Pengesahan Sah (Tanda Tangan) Monokrom --}}
    <section class="signatures">
        <div class="sig-block">
            <div class="sig-role">Pihak Mitra / Klien</div>
            <div class="sig-org">{{ $user->company_name ?: 'Perusahaan Mitra' }}</div>
            <div class="sig-space">
                <span class="sig-stamp">Tanda Tangan &amp; Cap Basah</span>
            </div>
            <div class="sig-name">{{ $user->pic_name ?: $user->name }}</div>
            <div class="sig-title">Kepala Bagian Keuangan / Purchasing</div>
        </div>

        <div class="sig-block">
            <div class="sig-role">Verifikasi Dokumen &amp; Pajak</div>
            <div class="sig-org">Divisi Penagihan GPA</div>
            <div class="sig-space">
                <span class="sig-stamp">Faktur Sah Tervalidasi</span>
            </div>
            <div class="sig-name">Rina Marlina, S.Ak.</div>
            <div class="sig-title">Staf Administrasi &amp; Penagihan B2B</div>
        </div>

        <div class="sig-block">
            <div class="sig-role">Otorisasi Keuangan &amp; Kas</div>
            <div class="sig-org">PT Green Pasundan Agriculture</div>
            <div class="sig-space">
                <span class="sig-stamp">GPA FINANCE APPROVED</span>
            </div>
            <div class="sig-name">Siti Aminah, S.E.</div>
            <div class="sig-title">Sekretaris Utama &amp; Finance Controller</div>
        </div>
    </section>

    {{-- Footer Cetak --}}
    <footer class="doc-footer">
        <div>
            Dicetak secara resmi melalui sistem AgroOrder GPA pada {{ $printDate }}.
        </div>
        <div class="font-mono">
            Halaman 1 dari 1 &bull; Arsip Elektronik Penagihan No. {{ $docNumber }}
        </div>
    </footer>

    @if (request()->query('auto_print'))
        <script>
            window.addEventListener('load', () => {
                window.print();
            });
        </script>
    @endif
</body>
</html>
