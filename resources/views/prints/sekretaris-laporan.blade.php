<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jurnal Rekapitulasi Operasional &amp; Penimbangan — Koperasi GPA</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .a4-canvas {
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .excel-table {
                border-collapse: collapse !important;
                width: 100% !important;
                border: 1px solid #94a3b8 !important;
            }

            .excel-table th, .excel-table td {
                border: 1px solid #cbd5e1 !important;
            }

            .excel-table thead th {
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
            }

            .excel-table tfoot tr {
                background-color: #f8fafc !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            .signature-section {
                page-break-inside: avoid;
            }
        }

        @media screen {
            body {
                background: #f4f6f0;
            }
        }

        .a4-canvas {
            width: 297mm;
            min-height: 210mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 10px 35px -15px rgba(21, 58, 1, 0.35);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 8pt;
            line-height: 1.45;
            color: #191c18;
            padding: 10mm 12mm;
            box-sizing: border-box;
        }

        .excel-table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #94a3b8;
        }

        .excel-table th, .excel-table td {
            border: 1px solid #cbd5e1;
        }
    </style>
</head>
<body class="antialiased">

    {{-- Topbar Kontrol Cetak (Layar Saja) --}}
    <div class="no-print sticky top-0 z-30 border-b border-line-board bg-white/95 backdrop-blur shadow-sub">
        <div class="mx-auto flex max-w-[297mm] items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                @php
                    $currentUser = auth()->user();
                    $backUrl = match($currentUser?->role) {
                        'DIREKTUR'   => route('direktur.laporan.index'),
                        'SEKRETARIS' => route('sekretaris.laporan'),
                        default      => url('/dashboard'),
                    };
                    $backLabel = match($currentUser?->role) {
                        'DIREKTUR'   => 'Kembali ke Laporan Direktur',
                        default      => 'Kembali ke Laporan',
                    };
                @endphp
                <a href="{{ $backUrl }}"
                    onclick="if (window.history.length > 1) { window.history.back(); return false; } else { window.close(); setTimeout(function() { window.location.href = '{{ $backUrl }}'; }, 150); return false; }"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-medium text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    <span>{{ $backLabel }}</span>
                </a>
                <span class="rounded bg-brand/10 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-brand">
                    JURNAL REKAPITULASI RESMI (A4 LANDSCAPE)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sekretaris.laporan.export') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-sub transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                    Unduh Excel (CSV)
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-3.5 py-1.5 text-xs font-bold text-white shadow-sub transition-colors hover:bg-brand-deep">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    Cetak Dokumen (Ctrl+P)
                </button>
            </div>
        </div>
    </div>

    {{-- Lembar Cetak A4 --}}
    <main class="a4-canvas">
        {{-- Kop Surat Resmi Koperasi GPA --}}
        <header class="flex items-start justify-between gap-4 border-b-2 border-brand pb-4">
            <div class="flex items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-brand/30 bg-surface-shell p-1.5">
                    <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-9 w-9 object-contain">
                </div>
                <div>
                    <h1 class="text-sm font-extrabold uppercase tracking-wide text-ink">
                        KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)
                    </h1>
                    <p class="mt-0.5 text-[8pt] text-ink-body">
                        Sentra Distribusi Sayuran Segar Priangan &bull; Badan Hukum: AHU-0001294.AH.01.26.TAHUN 2024<br>
                        Desa Panundaan, Kec. Ciwidey, Kab. Bandung, Jawa Barat 40973 &bull; Hotline Operasional: +62 811-2345-6789
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-block rounded bg-brand px-2 py-0.5 text-[7.5pt] font-extrabold uppercase tracking-wider text-white">
                    JURNAL REKAPITULASI
                </span>
                <p class="mt-1 font-mono text-[7.5pt] font-semibold text-ink-muted">
                    NO: REG/SEC-GPA/{{ date('Ym') }}/{{ str_pad(count($journalRows), 4, '0', STR_PAD_LEFT) }}
                </p>
                <p class="text-[7.5pt] font-bold text-brand">
                    STATUS: TERVERIFIKASI SAH
                </p>
            </div>
        </header>

        {{-- Judul & Metadata Laporan --}}
        <div class="mt-4 flex items-center justify-between border-b border-line-board pb-3">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-brand">
                    Jurnal Rekapitulasi Operasional Penimbangan &amp; Pengiriman Sayur
                </h2>
                <p class="text-[8pt] text-ink-body">
                    Periode Buku: <strong class="text-ink">Bulan Berjalan (Tahun {{ date('Y') }})</strong>
                    &bull; Verifikator: <strong class="text-brand">{{ $operatorName }}</strong> (Sekretariat Operasional)
                </p>
            </div>
            <div class="text-right text-[7.5pt] text-ink-muted">
                <div>Waktu Cetak: <span class="font-mono text-ink">{{ date('d/m/Y H:i') }} WIB</span></div>
                <div>Legalitas: <span class="font-semibold text-ink">Arsip Transaksi Tera Gudang</span></div>
            </div>
        </div>

        {{-- Kartu Metrik Ringkasan Operasional (Spreadsheet Dashboard) --}}
        <div class="mt-4 grid grid-cols-4 gap-3">
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7pt] font-bold uppercase tracking-wider text-ink-muted">Total Netto Tera</span>
                <p class="mt-0.5 font-mono text-xs font-extrabold text-ink">
                    {{ number_format($totalNet, 1, ',', '.') }} <span class="text-[7.5pt] font-normal">Kg</span>
                </p>
                <span class="text-[6.5pt] text-ink-quiet">Bobot riil timbang gudang</span>
            </div>
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7pt] font-bold uppercase tracking-wider text-ink-muted">Estimasi Awal PO</span>
                <p class="mt-0.5 font-mono text-xs font-extrabold text-ink">
                    {{ number_format($totalEstimate, 0, ',', '.') }} <span class="text-[7.5pt] font-normal">Kg</span>
                </p>
                <span class="text-[6.5pt] text-ink-quiet">Permintaan buyer B2B</span>
            </div>
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7pt] font-bold uppercase tracking-wider text-ink-muted">Deviasi Bobot</span>
                <p class="mt-0.5 font-mono text-xs font-extrabold {{ $overallDeviation < -1 ? 'text-red-700' : 'text-brand' }}">
                    {{ ($overallDeviation >= 0 ? '+' : '') . number_format($overallDeviation, 2, '.', '') }}%
                </p>
                <span class="text-[6.5pt] text-ink-quiet">Toleransi susut &lt; 0.5%</span>
            </div>
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7pt] font-bold uppercase tracking-wider text-ink-muted">Total Nilai Transaksi</span>
                <p class="mt-0.5 font-mono text-xs font-extrabold text-brand">
                    Rp {{ number_format($totalValue, 0, ',', '.') }}
                </p>
                <span class="text-[6.5pt] text-ink-quiet">Grand total penagihan sah</span>
            </div>
        </div>

        @php
            $direkturName = $direkturName ?? 'Direktur GPA';
        @endphp

        {{-- Tabel Jurnal Transaksi (Excel Sheet View) --}}
        <section class="mt-4">
            <div class="flex items-center justify-between border-b border-brand pb-1">
                <h3 class="text-[8pt] font-extrabold uppercase tracking-wider text-brand">
                    Daftar Jurnal Transaksi, Surat Jalan &amp; Penimbangan Nyata (Spreadsheet)
                </h3>
                <span class="text-[7pt] text-ink-muted font-mono">{{ count($journalRows) }} Baris Transaksi Terkonsolidasi</span>
            </div>
            <table class="excel-table mt-2 w-full border-collapse text-left text-[7.5pt]">
                <thead>
                    <tr class="bg-slate-100 text-slate-800">
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt]" style="width: 28px;">No</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]" style="width: 110px;">No. PO / Invoice</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]" style="width: 110px;">Surat Jalan</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]">Mitra Klien Pembeli</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]">Komoditas</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt]" style="width: 75px;">Est. (Kg)</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt]" style="width: 80px;">Netto (Kg)</th>
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt]" style="width: 65px;">Deviasi</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt]" style="width: 105px;">Total Nilai</th>
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt]" style="width: 75px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journalRows as $idx => $row)
                        <tr class="{{ $idx % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }} hover:bg-emerald-50/40">
                            <td class="py-1.5 px-2 text-center font-mono text-ink-muted">{{ $row['no'] }}</td>
                            <td class="py-1.5 px-2 font-mono font-bold text-brand">{{ $row['invoice'] }}</td>
                            <td class="py-1.5 px-2 font-mono text-ink-muted">{{ $row['sj'] }}</td>
                            <td class="py-1.5 px-2 font-semibold text-ink">{{ $row['client'] }}</td>
                            <td class="py-1.5 px-2 text-ink-body">{{ $row['commodity'] }}</td>
                            <td class="py-1.5 px-2 text-right font-mono text-ink-muted">{{ number_format($row['estimate'], 0, ',', '.') }}</td>
                            <td class="py-1.5 px-2 text-right font-mono font-bold text-ink">{{ number_format($row['net'], 1, ',', '.') }}</td>
                            <td class="py-1.5 px-2 text-center font-mono font-semibold {{ str_contains($row['deviation_label'], '-') ? 'text-red-700' : 'text-brand' }}">
                                {{ $row['deviation_label'] }}
                            </td>
                            <td class="py-1.5 px-2 text-right font-mono font-bold text-ink">
                                Rp {{ number_format($row['value'], 0, ',', '.') }}
                            </td>
                            <td class="py-1.5 px-2 text-center">
                                <span class="inline-block rounded px-1.5 py-0.5 text-[6.5pt] font-bold uppercase tracking-wider {{ $row['status_label'] === 'Lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $row['status_label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-3 text-center text-ink-muted italic">
                                Belum ada catatan jurnal operasional pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-700 bg-slate-100 font-bold border-b-[3px] border-double border-slate-800">
                        <td colspan="5" class="py-2 px-2 text-right text-brand uppercase tracking-wider text-[7.5pt]">
                            TOTAL KONSOLIDASI OPERASIONAL:
                        </td>
                        <td class="py-2 px-2 text-right font-mono text-ink">{{ number_format($totalEstimate, 0, ',', '.') }}</td>
                        <td class="py-2 px-2 text-right font-mono text-brand">{{ number_format($totalNet, 1, ',', '.') }}</td>
                        <td class="py-2 px-2 text-center font-mono text-brand">
                            {{ ($overallDeviation >= 0 ? '+' : '') . number_format($overallDeviation, 2, '.', '') }}%
                        </td>
                        <td class="py-2 px-2 text-right font-mono text-brand text-[8pt]">
                            Rp {{ number_format($totalValue, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        {{-- Lembar Tanda Tangan & Legalitas Pengesahan --}}
        <div class="signature-section mt-8 border-t border-line-board pt-4">
            <div class="grid grid-cols-2 gap-8">
                <div class="text-center">
                    <p class="text-[7.5pt] text-ink-muted">Dibuat &amp; Direkapitulasi Oleh,</p>
                    <p class="text-[8pt] font-bold text-ink">Sekretariat Operasional GPA</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="rounded border border-dashed border-line-board px-3 py-1 font-mono text-[7pt] text-ink-quiet">
                            VERIFIKASI TIMBANG SAH
                        </span>
                    </div>
                    <p class="text-[8pt] font-extrabold text-ink underline">{{ $operatorName }}</p>
                    <p class="text-[7pt] text-ink-muted">Staf Administrasi &amp; Verifikasi Operasional</p>
                </div>
                <div class="text-center">
                    <p class="text-[7.5pt] text-ink-muted">Ciwidey, {{ date('d F Y') }} &bull; Mengetahui &amp; Menyetujui,</p>
                    <p class="text-[8pt] font-bold text-brand">Direktur Utama Koperasi GPA</p>
                    <div class="h-16 flex items-center justify-center">
                        <div class="rounded border-2 border-brand/50 px-3 py-1 text-center">
                            <span class="block font-mono text-[6.5pt] font-extrabold tracking-widest text-brand uppercase">SAH // KOPERASI GPA</span>
                            <span class="block text-[6pt] text-ink-quiet">AUDIT-TRAIL: VERIFIED</span>
                        </div>
                    </div>
                    <p class="text-[8pt] font-extrabold text-brand underline">{{ $direkturName }}</p>
                    <p class="text-[7pt] text-ink-muted">Direktur / Owner Koperasi Produsen GPA</p>
                </div>
            </div>
            <div class="mt-4 border-t border-line-hair pt-2 text-center text-[6.5pt] text-ink-quiet font-mono">
                JURNAL REKAPITULASI RESMI KOPERASI PRODUSEN AGRO GREEN PASUNDAN &bull; TERCATAT ELEKTRONIK &bull; GENERATED {{ date('d-m-Y H:i:s') }}
            </div>
        </div>
    </main>

</body>
</html>
