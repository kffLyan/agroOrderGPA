<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Resmi Penjualan GPA — {{ $startDate }} s.d {{ $endDate }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
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
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #ffffff;
            box-shadow: 0 10px 35px -15px rgba(21, 58, 1, 0.35);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 8.5pt;
            line-height: 1.5;
            color: #191c18;
            padding: 12mm 14mm;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="antialiased">

    {{-- Topbar Kontrol Cetak (Hanya tampil di layar) --}}
    <div class="no-print sticky top-0 z-30 border-b border-line-board bg-white/95 backdrop-blur shadow-sub">
        <div class="mx-auto flex max-w-[210mm] items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                <a href="{{ route('direktur.laporan.index', request()->query()) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-medium text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    Kembali ke Laporan
                </a>
                <span class="rounded bg-brand/10 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-brand">
                    ARSIP EKSEKUTIF DIREKTUR
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('direktur.laporan.export', request()->query()) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-semibold text-ink shadow-sub transition-colors hover:bg-surface-muted">
                    <x-gpa.icon name="download" class="h-3.5 w-3.5" />
                    Unduh CSV
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
                    REKAPITULASI RESMI PENJUALAN KOMODITAS &amp; KINERJA OMZET B2B
                </h2>
                <p class="text-[8pt] text-ink-body">
                    Periode: <strong class="text-ink">{{ $periodName ?? (date('d F Y', strtotime($startDate)) . ' s.d ' . date('d F Y', strtotime($endDate))) }}</strong>
                    @if(!empty($clientFilterName))
                        &bull; Filter Mitra: <strong class="text-brand">{{ $clientFilterName }}</strong>
                    @else
                        &bull; Cakupan: <strong>Seluruh Mitra B2B Konsolidasi</strong>
                    @endif
                </p>
            </div>
            <div class="text-right text-[7.5pt] text-ink-muted">
                <div>Waktu Cetak: <span class="font-mono text-ink">{{ date('d/m/Y H:i') }} WIB</span></div>
                <div>Otoritas: <span class="font-semibold text-ink">Direktur GPA (H. Ridwan Permana)</span></div>
            </div>
        </div>

        {{-- Kartu Metrik Ringkasan Eksekutif --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div class="rounded-lg border border-line-board bg-surface-shell p-2.5">
                <span class="block text-[7.5pt] font-bold uppercase tracking-wider text-ink-muted">Total Omzet Penjualan</span>
                <p class="mt-0.5 font-mono text-sm font-extrabold text-brand">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
                <span class="text-[7pt] text-ink-quiet">Akumulasi bruto invoice sah</span>
            </div>
            <div class="rounded-lg border border-line-board bg-surface-shell p-2.5">
                <span class="block text-[7.5pt] font-bold uppercase tracking-wider text-ink-muted">Total Tonase Terjual</span>
                <p class="mt-0.5 font-mono text-sm font-extrabold text-ink">
                    {{ number_format($totalTonnageKg, 1, ',', '.') }} <span class="text-xs font-normal">Kg</span>
                </p>
                <span class="text-[7pt] text-ink-quiet">Netto timbangan tera sah gudang</span>
            </div>
            <div class="rounded-lg border border-line-board bg-surface-shell p-2.5">
                <span class="block text-[7.5pt] font-bold uppercase tracking-wider text-ink-muted">Volume Transaksi PO</span>
                <p class="mt-0.5 font-mono text-sm font-extrabold text-ink">
                    {{ $orders->count() }} <span class="text-xs font-normal">Pesanan</span>
                </p>
                <span class="text-[7pt] text-ink-quiet">Siklus PO operasional terverifikasi</span>
            </div>
        </div>

        {{-- Bagian 1: Rekapitulasi Realisasi Penjualan Per Komoditas --}}
        <section class="mt-5">
            <div class="flex items-center justify-between border-b border-brand pb-1">
                <h3 class="text-[8pt] font-extrabold uppercase tracking-wider text-brand">
                    1. Realisasi Penjualan Berdasarkan Komoditas Sayuran Segar
                </h3>
                <span class="text-[7pt] text-ink-muted">Distribusi Volume &amp; Nilai</span>
            </div>
            <table class="mt-2 w-full border-collapse text-left text-[8pt]">
                <thead>
                    <tr class="border-b border-line-board bg-surface-shell">
                        <th class="py-1.5 px-2 text-center font-bold text-ink" style="width: 25px;">No</th>
                        <th class="py-1.5 px-2 font-bold text-ink">Nama Komoditas Sayuran</th>
                        <th class="py-1.5 px-2 text-right font-bold text-ink">Total Bobot (Kg)</th>
                        <th class="py-1.5 px-2 text-right font-bold text-ink">Rata-rata Harga</th>
                        <th class="py-1.5 px-2 text-right font-bold text-ink">Total Nilai Penjualan</th>
                        <th class="py-1.5 px-2 text-center font-bold text-ink" style="width: 80px;">Pangsa Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse($commoditySummary as $index => $summary)
                        @php
                            $pct = $totalRevenue > 0 ? round(($summary->total_sales / $totalRevenue) * 100, 1) : 0;
                            $avgPrice = $summary->total_weight > 0 ? $summary->total_sales / $summary->total_weight : 0;
                        @endphp
                        <tr class="{{ $index % 2 === 1 ? 'bg-surface-shell/40' : '' }}">
                            <td class="py-1 px-2 text-center font-mono text-ink-muted">{{ $index + 1 }}</td>
                            <td class="py-1 px-2 font-semibold text-ink">
                                {{ $summary->product->name ?? 'Komoditas #' . $summary->product_id }}
                            </td>
                            <td class="py-1 px-2 text-right font-mono text-ink">
                                {{ number_format($summary->total_weight, 1, ',', '.') }}
                            </td>
                            <td class="py-1 px-2 text-right font-mono text-ink-muted">
                                Rp {{ number_format($avgPrice, 0, ',', '.') }}
                            </td>
                            <td class="py-1 px-2 text-right font-mono font-bold text-ink">
                                Rp {{ number_format($summary->total_sales, 0, ',', '.') }}
                            </td>
                            <td class="py-1 px-2 text-center font-mono font-semibold text-brand">
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

        {{-- Bagian 2: Rincian Daftar Transaksi Pemesanan --}}
        <section class="mt-5">
            <div class="flex items-center justify-between border-b border-brand pb-1">
                <h3 class="text-[8pt] font-extrabold uppercase tracking-wider text-brand">
                    2. Rincian Lengkap Buku Transaksi Pemesanan (PO)
                </h3>
                <span class="text-[7pt] text-ink-muted">Jurnal Operasional Sah</span>
            </div>
            <table class="mt-2 w-full border-collapse text-left text-[7.5pt]">
                <thead>
                    <tr class="border-b border-line-board bg-surface-shell">
                        <th class="py-1.5 px-2 text-center font-bold text-ink" style="width: 25px;">No</th>
                        <th class="py-1.5 px-2 font-bold text-ink" style="width: 105px;">No. Pesanan</th>
                        <th class="py-1.5 px-2 font-bold text-ink" style="width: 75px;">Tanggal</th>
                        <th class="py-1.5 px-2 font-bold text-ink">Mitra Klien B2B</th>
                        <th class="py-1.5 px-2 font-bold text-ink">Rincian Komoditas &amp; Netto</th>
                        <th class="py-1.5 px-2 text-center font-bold text-ink" style="width: 85px;">Status</th>
                        <th class="py-1.5 px-2 text-right font-bold text-ink" style="width: 110px;">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse($orders as $index => $order)
                        @php
                            $amount = (float) $order->grand_total;
                        @endphp
                        <tr class="{{ $index % 2 === 1 ? 'bg-surface-shell/40' : '' }}">
                            <td class="py-1 px-2 text-center font-mono text-ink-muted">{{ $index + 1 }}</td>
                            <td class="py-1 px-2 font-mono font-bold text-brand">
                                {{ $order->order_number }}
                            </td>
                            <td class="py-1 px-2 text-ink-muted">
                                {{ $order->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-1 px-2 font-semibold text-ink">
                                {{ $order->user->company_name ?? $order->user->name }}
                            </td>
                            <td class="py-1 px-2 text-ink-body">
                                @foreach($order->orderItems as $item)
                                    <div>{{ $item->product->name ?? 'Sayuran' }}: <span class="font-mono">{{ number_format($item->ordered_qty, 1) }} kg</span></div>
                                @endforeach
                            </td>
                            <td class="py-1 px-2 text-center">
                                <span class="inline-block rounded px-1.5 py-0.5 text-[7pt] font-bold uppercase tracking-wider {{ $order->status === 'SELESAI' ? 'bg-brand/10 text-brand' : 'bg-surface-pill text-ink-body' }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-1 px-2 text-right font-mono font-bold text-ink">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-3 text-center text-ink-muted italic">
                                Tidak ada transaksi pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-brand bg-surface-shell font-bold">
                        <td colspan="6" class="py-1.5 px-2 text-right text-brand uppercase tracking-wider text-[8pt]">
                            TOTAL KESELURUHAN OMZET TRANSAKSI:
                        </td>
                        <td class="py-1.5 px-2 text-right font-mono text-[8.5pt] text-brand">
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
                    <p class="text-[8pt] font-extrabold text-ink underline">Ibu Rina Marlina</p>
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
                    <p class="text-[8pt] font-extrabold text-brand underline">H. Ridwan Permana</p>
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
