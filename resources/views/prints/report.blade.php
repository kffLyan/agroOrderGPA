<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Resmi Penjualan GPA — {{ $startDate }} s.d {{ $endDate }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 landscape;
            margin: 6mm 8mm;
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
                table-layout: auto !important;
                border: 1px solid #94a3b8 !important;
            }

            .excel-table th, .excel-table td {
                border: 1px solid #cbd5e1 !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
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

    @php
        $direkturName = \App\Models\User::where('role', 'DIREKTUR')->first()?->name ?? 'Ahmad Sanusi, S.P.';
        $sekretarisName = \App\Models\User::where('role', 'SEKRETARIS')->first()?->name ?? 'Nurhayati, S.Ak';
    @endphp

    {{-- Topbar Kontrol Cetak (Hanya tampil di layar) --}}
    <div class="no-print sticky top-0 z-30 border-b border-line-board bg-white/95 backdrop-blur shadow-sub">
        <div class="mx-auto flex max-w-[297mm] items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                <a href="{{ route('direktur.laporan.index', request()->query()) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-medium text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    Kembali ke Laporan
                </a>
                <span class="rounded bg-brand/10 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-brand">
                    ARSIP EKSEKUTIF DIREKTUR (A4 LANDSCAPE)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('direktur.laporan.export', array_merge(request()->query(), ['format' => 'excel'])) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-sub transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                    Unduh Excel (.xls)
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
                        Sentra Agribisnis Sayuran Segar Dataran Tinggi Priangan &bull; Badan Hukum: AHU-0001294.AH.01.26.TAHUN 2024<br>
                        Desa Panundaan, Kec. Ciwidey, Kab. Bandung, Jawa Barat 40973 &bull; Hotline: +62 811-2345-6789
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-block rounded bg-brand px-2 py-0.5 text-[7.5pt] font-extrabold uppercase tracking-wider text-white">
                    LAPORAN EKSEKUTIF
                </span>
                <p class="mt-1 font-mono text-[7.5pt] font-semibold text-ink-muted">
                    NO: REG/DIR-GPA/{{ date('Ym') }}/{{ str_pad($orders->count(), 4, '0', STR_PAD_LEFT) }}
                </p>
                <p class="text-[7.5pt] font-bold text-brand">
                    STATUS: DISAHKAN // IMMUTABLE
                </p>
            </div>
        </header>

        {{-- Judul & Metadata Laporan --}}
        <div class="mt-4 flex items-center justify-between border-b border-line-board pb-3">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-brand">
                    REKAPITULASI RESMI PENJUALAN KOMODITAS &amp; KINERJA OMZET KONSOLIDASI (B2B &amp; REGULER)
                </h2>
                <p class="text-[8pt] text-ink-body">
                    Periode: <strong class="text-ink">{{ $periodName ?? (date('d F Y', strtotime($startDate)) . ' s.d ' . date('d F Y', strtotime($endDate))) }}</strong>
                    @if(!empty($clientFilterName))
                        &bull; Filter Entitas: <strong class="text-brand">{{ $clientFilterName }}</strong>
                    @else
                        &bull; Cakupan: <strong>Konsolidasi Mitra B2B &amp; Transaksi Reguler (Kasir / QRIS / Bank)</strong>
                    @endif
                </p>
            </div>
            <div class="text-right text-[7.5pt] text-ink-muted">
                <div>Waktu Cetak: <span class="font-mono text-ink">{{ date('d/m/Y H:i') }} WIB</span></div>
                <div>Otoritas: <span class="font-semibold text-ink">Direktur GPA ({{ $direkturName }})</span></div>
            </div>
        </div>

        {{-- Kartu Metrik Ringkasan Eksekutif (Spreadsheet Dashboard) --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7.5pt] font-bold uppercase tracking-wider text-ink-muted">Total Omzet Penjualan</span>
                <p class="mt-0.5 font-mono text-sm font-extrabold text-brand">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <span class="text-[7pt] text-ink-quiet">Akumulasi bruto invoice sah</span>
            </div>
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7.5pt] font-bold uppercase tracking-wider text-ink-muted">Total Tonase Terjual</span>
                <p class="mt-0.5 font-mono text-sm font-extrabold text-ink">
                    {{ number_format($totalTonnageKg, 1, ',', '.') }} <span class="text-xs font-normal">Kg</span>
                </p>
                <span class="text-[7pt] text-ink-quiet">Netto timbangan tera sah gudang</span>
            </div>
            <div class="rounded-lg border border-slate-300 bg-slate-50/70 p-2.5 shadow-xs">
                <span class="block text-[7.5pt] font-bold uppercase tracking-wider text-ink-muted">Volume Transaksi PO</span>
                <p class="mt-0.5 font-mono text-sm font-extrabold text-ink">
                    {{ $orders->count() }} <span class="text-xs font-normal">Pesanan</span>
                </p>
                <span class="text-[7pt] text-ink-quiet">Siklus PO operasional terverifikasi</span>
            </div>
        </div>

        {{-- Bagian 1: Rekapitulasi Realisasi Penjualan Per Komoditas (Spreadsheet Sheet 1) --}}
        <section class="mt-4">
            <div class="flex items-center justify-between border-b border-brand pb-1">
                <h3 class="text-[8pt] font-extrabold uppercase tracking-wider text-brand">
                    1. Realisasi Penjualan Berdasarkan Komoditas Sayuran Segar (Tabel Distribusi)
                </h3>
                <span class="text-[7pt] text-ink-muted font-mono">Distribusi Volume &amp; Nilai</span>
            </div>
            <table class="excel-table mt-2 w-full border-collapse text-left text-[7.5pt]">
                <thead>
                    <tr class="bg-slate-100 text-slate-800">
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt]" style="width: 28px;">No</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]" style="min-width: 180px;">Nama Komoditas Sayuran</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 110px;">Total Bobot (Kg)</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 120px;">Rata-rata Harga</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 140px;">Total Nilai Penjualan</th>
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 90px;">Pangsa Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commoditySummary as $index => $summary)
                        @php
                            $pct = $totalRevenue > 0 ? round(($summary->total_sales / $totalRevenue) * 100, 1) : 0;
                            $avgPrice = $summary->total_weight > 0 ? $summary->total_sales / $summary->total_weight : 0;
                        @endphp
                        <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }} hover:bg-emerald-50/40">
                            <td class="py-1.5 px-2 text-center font-mono text-ink-muted">{{ $index + 1 }}</td>
                            <td class="py-1.5 px-2 font-semibold text-ink leading-tight">
                                {{ $summary->product->name ?? 'Komoditas #' . $summary->product_id }}
                            </td>
                            <td class="py-1.5 px-2 text-right font-mono text-ink whitespace-nowrap">
                                {{ number_format($summary->total_weight, 1, ',', '.') }}
                            </td>
                            <td class="py-1.5 px-2 text-right font-mono text-ink-muted whitespace-nowrap">
                                Rp {{ number_format($avgPrice, 0, ',', '.') }}
                            </td>
                            <td class="py-1.5 px-2 text-right font-mono font-bold text-ink whitespace-nowrap">
                                Rp {{ number_format($summary->total_sales, 0, ',', '.') }}
                            </td>
                            <td class="py-1.5 px-2 text-center font-mono font-semibold text-brand whitespace-nowrap">
                                {{ $pct }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-3 text-center text-ink-muted italic">
                                Belum ada transaksi komoditas tercatat pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        {{-- Bagian 2: Rincian Daftar Transaksi Pemesanan (Spreadsheet Sheet 2) --}}
        <section class="mt-5">
            <div class="flex items-center justify-between border-b border-brand pb-1">
                <h3 class="text-[8pt] font-extrabold uppercase tracking-wider text-brand">
                    2. Rincian Lengkap Buku Transaksi Pemesanan Konsolidasi (B2B &amp; Reguler)
                </h3>
                <span class="text-[7pt] text-ink-muted font-mono">Jurnal Operasional Sah &bull; Bebas Singkatan Underscore</span>
            </div>
            <table class="excel-table mt-2 w-full border-collapse text-left text-[7.5pt]">
                <thead>
                    <tr class="bg-slate-100 text-slate-800">
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt]" style="width: 28px;">No</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 120px;">No. Pesanan</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 75px;">Tanggal</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]" style="min-width: 135px;">Entitas / Pembeli</th>
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 100px;">Kategori</th>
                        <th class="py-2 px-2 font-bold uppercase tracking-wider text-[7pt]" style="min-width: 160px;">Komoditas &amp; Netto</th>
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 105px;">Metode Bayar</th>
                        <th class="py-2 px-2 text-center font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 120px;">Status Transaksi</th>
                        <th class="py-2 px-2 text-right font-bold uppercase tracking-wider text-[7pt] whitespace-nowrap" style="width: 115px;">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $index => $order)
                        @php
                            $amount = (float) $order->grand_total;
                            $statusToneClass = match($order->status) {
                                'SELESAI'             => 'bg-emerald-100 text-emerald-800',
                                'SELESAI_CATATAN'     => 'bg-amber-100 text-amber-800',
                                'DALAM_PENGIRIMAN'    => 'bg-blue-100 text-blue-800',
                                'SIAP_KIRIM'          => 'bg-sky-100 text-sky-800',
                                'LUNAS'               => 'bg-teal-100 text-teal-800',
                                'MENUNGGU_VERIFIKASI' => 'bg-yellow-100 text-yellow-800',
                                'MENUNGGU_PEMBAYARAN' => 'bg-orange-100 text-orange-800',
                                'BATAL'               => 'bg-rose-100 text-rose-800',
                                default               => 'bg-slate-100 text-slate-700',
                            };
                            $clientCat = $order->client_type_label ?? ($order->user?->client_type_label ?? ($order->user?->is_b2b ? 'Mitra B2B Kontrak' : 'Pelanggan Reguler'));
                            $payMethod = $order->payment_method_label ?? ($order->payment?->method_label ?? str_replace('_', ' ', $order->payment_method ?? 'Kredit Tempo TOP'));
                        @endphp
                        <tr class="{{ $index % 2 === 1 ? 'bg-slate-50/70' : 'bg-white' }} hover:bg-emerald-50/40">
                            <td class="py-1.5 px-2 text-center font-mono text-ink-muted">{{ $index + 1 }}</td>
                            <td class="py-1.5 px-2 font-mono font-bold text-brand whitespace-nowrap text-[7pt]">
                                {{ $order->order_number }}
                            </td>
                            <td class="py-1.5 px-2 text-ink-muted font-mono whitespace-nowrap text-[7pt]">
                                {{ $order->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-1.5 px-2 font-semibold text-ink leading-tight">
                                {{ $order->user->company_name ?? $order->user->name }}
                            </td>
                            <td class="py-1.5 px-2 text-center whitespace-nowrap">
                                <span class="text-[7pt] font-semibold {{ str_contains($clientCat, 'Reguler') ? 'text-amber-800 font-bold' : 'text-emerald-800' }}">
                                    {{ $clientCat }}
                                </span>
                            </td>
                            <td class="py-1.5 px-2 text-ink-body text-[7pt] leading-tight">
                                @foreach($order->orderItems as $item)
                                    <div>{{ $item->product->name ?? 'Sayuran' }}: <span class="font-mono font-semibold">{{ number_format($item->ordered_qty, 1) }} kg</span></div>
                                @endforeach
                            </td>
                            <td class="py-1.5 px-2 text-center">
                                <span class="inline-block rounded border border-slate-300 bg-slate-50 px-1 py-0.5 font-mono text-[6.5pt] text-slate-700 leading-tight whitespace-normal break-words">
                                    {{ $payMethod }}
                                </span>
                            </td>
                            <td class="py-1.5 px-2 text-center">
                                <span class="inline-block rounded px-1.5 py-0.5 text-[6.5pt] font-bold uppercase leading-tight whitespace-normal break-words {{ $statusToneClass }}">
                                    {{ $order->status_label ?? str_replace('_', ' ', $order->status) }}
                                </span>
                            </td>
                            <td class="py-1.5 px-2 text-right font-mono font-bold text-ink whitespace-nowrap text-[7.5pt]">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-3 text-center text-ink-muted italic">
                                Tidak ada transaksi pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-700 bg-slate-100 font-bold border-b-[3px] border-double border-slate-800">
                        <td colspan="8" class="py-2 px-2 text-right text-brand uppercase tracking-wider text-[7.5pt]">
                            TOTAL KESELURUHAN OMZET TRANSAKSI:
                        </td>
                        <td class="py-2 px-2 text-right font-mono text-[8pt] text-brand">
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>

        {{-- Lembar Tanda Tangan & Legalitas Pengesahan --}}
        <div class="signature-section mt-8 border-t border-line-board pt-4">
            <div class="grid grid-cols-2 gap-8">
                <div class="text-center">
                    <p class="text-[7.5pt] text-ink-muted">Dibuat &amp; Diverifikasi Oleh,</p>
                    <p class="text-[8pt] font-bold text-ink">Sekretariat Operasional GPA</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="rounded border border-dashed border-line-board px-3 py-1 font-mono text-[7pt] text-ink-quiet">
                            VERIFIKASI SISTEM ELEKTRONIK
                        </span>
                    </div>
                    <p class="text-[8pt] font-extrabold text-ink underline">{{ $sekretarisName }}</p>
                    <p class="text-[7pt] text-ink-muted">Staf Administrasi &amp; Verifikasi Kontrak</p>
                </div>
                <div class="text-center">
                    <p class="text-[7.5pt] text-ink-muted">Ciwidey, {{ date('d F Y') }} &bull; Mengetahui &amp; Mengesahkan,</p>
                    <p class="text-[8pt] font-bold text-brand">Direktur Utama Koperasi GPA</p>
                    <div class="h-16 flex items-center justify-center">
                        <div class="rounded border-2 border-brand/50 px-3 py-1 text-center">
                            <span class="block font-mono text-[6.5pt] font-extrabold tracking-widest text-brand uppercase">SAH // KOPERASI GPA</span>
                            <span class="block text-[6pt] text-ink-quiet">HASH: 0x8F9C4A217B1E90D4</span>
                        </div>
                    </div>
                    <p class="text-[8pt] font-extrabold text-brand underline">{{ $direkturName }}</p>
                    <p class="text-[7pt] text-ink-muted">Direktur / Owner Koperasi Produsen GPA</p>
                </div>
            </div>
            <div class="mt-4 border-t border-line-hair pt-2 text-center text-[6.5pt] text-ink-quiet font-mono">
                DOKUMEN RESMI TERCATAT PADA SISTEM AGROORDER GPA &bull; BERLAKU SEBAGAI LAPORAN KINERJA PENJUALAN EKSEKUTIF SAH &bull; GENERATED {{ date('d-m-Y H:i:s') }}
            </div>
        </div>
    </main>

</body>
</html>
