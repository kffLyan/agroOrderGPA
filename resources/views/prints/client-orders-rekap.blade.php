<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekapitulasi Transaksi Pemesanan PO — {{ $user->company_name ?: $user->name }}</title>
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
            }

            .no-print {
                display: none !important;
            }

            .a4-landscape {
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

            .signatures {
                page-break-inside: avoid;
            }
        }

        @media screen {
            body {
                background: #f4f6f0;
            }
        }

        .a4-landscape {
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
    </style>
</head>
<body class="antialiased">

    {{-- Topbar Kontrol Layar (Tidak Tercetak) --}}
    <div class="no-print sticky top-0 z-30 border-b border-line-board bg-white/95 backdrop-blur shadow-sub">
        <div class="mx-auto flex max-w-[297mm] items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                <a href="{{ route('klien.pesanan.index') }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-board bg-surface px-3 py-1.5 text-xs font-medium text-ink transition-colors hover:bg-surface-shell">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    Kembali ke Daftar Pesanan
                </a>
                <span class="rounded bg-brand/10 px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-wider text-brand">
                    REKAP RESMI PURCHASE ORDER (A4 LANDSCAPE)
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('klien.pesanan.export', ['status' => $statusFilter]) }}"
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

    {{-- Kanvas A4 Landscape --}}
    <main class="a4-landscape">
        {{-- Kop Surat Resmi GPA --}}
        <header class="flex items-start justify-between gap-4 border-b-2 border-brand pb-3">
            <div class="flex items-start gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border border-brand/30 bg-surface-shell p-1.5">
                    <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-9 w-9 object-contain">
                </div>
                <div>
                    <h1 class="text-sm font-extrabold uppercase tracking-wide text-ink">
                        GREEN PASUNDAN AGRICULTURE
                    </h1>
                    <p class="text-[7.5pt] text-ink-body">
                        Divisi Distribusi Rantai Pasok Sayuran Segar &amp; Kemitraan Agribisnis B2B<br>
                        Sentra Logistik Lembang &amp; Ciwidey &bull; Desa Panundaan, Kab. Bandung 40973 &bull; Hotline: +62 811-2345-6789
                    </p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <span class="inline-block rounded bg-brand px-2 py-0.5 text-[7pt] font-extrabold uppercase tracking-wider text-white">
                    Cold-Chain Standard &bull; ISO 22000
                </span>
                <p class="mt-1 font-mono text-[7pt] font-semibold text-ink-muted">
                    REG: {{ $docNumber }}
                </p>
                <p class="text-[7pt] text-ink-muted">
                    Dicetak: <span class="font-mono text-ink">{{ $printDate }}</span>
                </p>
            </div>
        </header>

        {{-- Judul Dokumen --}}
        <div class="mt-3 flex items-center justify-between border-b border-line-board pb-2">
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-brand">
                    REKAPITULASI RESMI TRANSAKSI PEMESANAN (PURCHASE ORDER)
                </h2>
                <p class="text-[7.5pt] text-ink-body">
                    Lampiran Rekonsiliasi Pesanan, Realisasi Penimbangan Fisik Gudang &amp; Status Pengiriman Armada Chiller
                </p>
            </div>
            <div class="text-right">
                <span class="rounded border border-brand/40 bg-brand/5 px-2 py-0.5 font-mono text-[7pt] font-bold text-brand uppercase">
                    FILTER: {{ $statusFilter === 'all' ? 'SELURUH PESANAN' : strtoupper($statusFilter) }}
                </span>
            </div>
        </div>

        {{-- Parameter Mitra & Legalitas Dokumen --}}
        <section class="mt-3 grid grid-cols-2 gap-3 text-[7.5pt]">
            <div class="rounded-lg border border-line-board bg-surface-shell p-2">
                <p class="text-[7pt] font-bold uppercase tracking-wider text-brand">Profil Mitra / Klien Penerima</p>
                <div class="mt-1 grid grid-cols-3 gap-y-0.5">
                    <span class="text-ink-muted">Nama Entitas</span>
                    <span class="col-span-2 font-bold text-ink">: {{ $user->company_name ?: $user->name }}</span>

                    <span class="text-ink-muted">PIC / Kontak</span>
                    <span class="col-span-2 text-ink">: {{ $user->pic_name ?: $user->name }} ({{ $user->pic_phone ?: ($user->phone ?: '-') }})</span>

                    <span class="text-ink-muted">Alamat Dock</span>
                    <span class="col-span-2 text-ink truncate">: {{ $user->address ?: 'Gudang / Receiving Dock Utama Klien' }}</span>

                    <span class="text-ink-muted">Skema Kemitraan</span>
                    <span class="col-span-2 font-semibold text-brand">: {{ $user->client_type === 'B2B_KONTRAK' ? 'Mitra Kontrak B2B (Fasilitas TOP Tempo 30D)' : 'Mitra Reguler Spot Order' }}</span>
                </div>
            </div>

            <div class="rounded-lg border border-line-board bg-surface-shell p-2">
                <p class="text-[7pt] font-bold uppercase tracking-wider text-brand">Ringkasan Konsolidasi Rekap</p>
                <div class="mt-1 grid grid-cols-4 gap-2 text-center">
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Total PO</span>
                        <span class="font-mono text-xs font-bold text-ink">{{ $countOrders }}</span>
                    </div>
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Est. Pesan</span>
                        <span class="font-mono text-xs font-bold text-ink">{{ number_format($totalKgOrder, 1, ',', '.') }} kg</span>
                    </div>
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Netto Tera</span>
                        <span class="font-mono text-xs font-bold text-brand">{{ number_format($totalKgActual, 1, ',', '.') }} kg</span>
                    </div>
                    <div class="rounded border border-line-board bg-white p-1">
                        <span class="block text-[6.5pt] text-ink-muted uppercase">Total Nilai</span>
                        <span class="font-mono text-xs font-extrabold text-brand">Rp {{ number_format($totalValue, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tabel Data Transaksi Rekapitulasi Rinci --}}
        <section class="mt-3">
            <table class="w-full border-collapse text-left text-[7.5pt]">
                <thead>
                    <tr class="border-b border-line-board bg-surface-shell">
                        <th class="py-1 px-1.5 text-center font-bold text-ink" style="width: 25px;">No</th>
                        <th class="py-1 px-1.5 font-bold text-ink" style="width: 95px;">No. PO &amp; Tgl</th>
                        <th class="py-1 px-1.5 font-bold text-ink">Rincian Komoditas &amp; Estimasi</th>
                        <th class="py-1 px-1.5 text-right font-bold text-ink" style="width: 75px;">Pesan (Kg)</th>
                        <th class="py-1 px-1.5 text-right font-bold text-ink" style="width: 80px;">Netto (Kg)</th>
                        <th class="py-1 px-1.5 font-bold text-ink" style="width: 140px;">Surat Jalan &amp; Armada</th>
                        <th class="py-1 px-1.5 text-center font-bold text-ink" style="width: 90px;">Status Siklus</th>
                        <th class="py-1 px-1.5 text-right font-bold text-ink" style="width: 100px;">Total Nilai (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line-hair">
                    @forelse ($orders as $idx => $order)
                        @php
                            $orderedKg = (float) $order->orderItems->sum('ordered_qty');
                            $actualKg = $orderedKg;
                            $hasWeighed = true;
                            $amount = (float) $order->grand_total;

                            $commoditiesList = $order->orderItems->map(function ($item) {
                                return ($item->product->name ?? 'Komoditas') . ' (' . number_format($item->ordered_qty, 0, ',', '.') . ' kg)';
                            })->join(', ');

                            $statusLabel = match ($order->status) {
                                'MENUNGGU_VERIFIKASI' => 'MENUNGGU VERIFIKASI',
                                'MENUNGGU_PEMBAYARAN' => 'MENUNGGU PEMBAYARAN',
                                'LUNAS'               => 'LUNAS TERVERIFIKASI',
                                'SIAP_KIRIM'          => 'SURAT JALAN TERBIT',
                                'DALAM_PENGIRIMAN'    => 'DALAM PENGIRIMAN',
                                'SELESAI'             => 'SELESAI DITERIMA',
                                'SELESAI_CATATAN'     => 'SELESAI (CATATAN)',
                                'BATAL'               => 'DIBATALKAN',
                                default               => strtoupper($order->status),
                            };
                        @endphp
                        <tr class="{{ $idx % 2 === 1 ? 'bg-surface-shell/40' : '' }}">
                            <td class="py-1 px-1.5 text-center font-mono text-ink-muted">{{ $idx + 1 }}</td>
                            <td class="py-1 px-1.5">
                                <strong class="font-mono text-brand">{{ $order->order_number }}</strong><br>
                                <span class="text-[6.5pt] text-ink-muted">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-1 px-1.5 text-ink-body">
                                <span>{{ $commoditiesList ?: 'Komoditas Sayuran Segar Pasundan' }}</span>
                            </td>
                            <td class="py-1 px-1.5 text-right font-mono text-ink-muted">
                                {{ number_format($orderedKg, 1, ',', '.') }}
                            </td>
                            <td class="py-1 px-1.5 text-right font-mono font-bold text-ink">
                                {{ $hasWeighed ? number_format($actualKg, 1, ',', '.') : 'Antrean Timbang' }}
                            </td>
                            <td class="py-1 px-1.5 text-[7pt]">
                                @if ($order->surat_jalan_number)
                                    <strong class="font-mono text-ink">{{ $order->surat_jalan_number }}</strong><br>
                                    <span class="text-ink-muted">
                                        {{ $order->vehicle_plate_number ?: 'Truk Chiller' }} &bull; {{ $order->driver ? $order->driver->name : 'Supir GPA' }}
                                    </span>
                                @else
                                    <span class="italic text-ink-quiet">SJ Belum Terbit</span>
                                @endif
                            </td>
                            <td class="py-1 px-1.5 text-center">
                                <span class="inline-block rounded px-1.5 py-0.5 text-[6.5pt] font-bold uppercase tracking-wider {{ $order->status === 'SELESAI' ? 'bg-brand/10 text-brand' : 'bg-surface-pill text-ink-body' }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="py-1 px-1.5 text-right font-mono font-bold text-ink">
                                Rp {{ number_format($amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-3 text-center text-ink-muted italic">
                                Belum ada catatan riwayat transaksi pesanan untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-brand bg-surface-shell font-bold">
                        <td colspan="3" class="py-1 px-1.5 text-right text-brand uppercase tracking-wider text-[7.5pt]">
                            TOTAL KESELURUHAN KONSOLIDASI:
                        </td>
                        <td class="py-1 px-1.5 text-right font-mono text-ink">{{ number_format($totalKgOrder, 1, ',', '.') }} kg</td>
                        <td class="py-1 px-1.5 text-right font-mono text-brand">{{ number_format($totalKgActual, 1, ',', '.') }} kg</td>
                        <td colspan="2"></td>
                        <td class="py-1 px-1.5 text-right font-mono text-brand text-[8pt]">
                            Rp {{ number_format($totalValue, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </section>

        {{-- Ketentuan Standar Mutu & Catatan Operasional --}}
        <section class="mt-3 rounded border border-line-board bg-surface-shell/50 p-2 text-[7pt] text-ink-body">
            <h4 class="font-bold uppercase tracking-wider text-brand">Catatan Ketentuan &amp; Standar Mutu GPA (SOP Cold-Chain):</h4>
            <ol class="mt-0.5 list-decimal list-inside space-y-0.5">
                <li>Data bobot netto sah merupakan hasil penimbangan digital terkalibrasi di Packing House Lembang yang tercantum pada Surat Jalan resmi fisik.</li>
                <li>Untuk pesanan Klien Kontrak B2B dengan termin <em>Term of Payment (TOP)</em>, seluruh nominal tagihan sah diagregasi ke dalam Faktur Bulanan Konsolidasi.</li>
                <li>Batas toleransi deviasi susut perjalanan cold-chain maksimum 0.5% sesuai standar garansi mutu Green Pasundan Agriculture.</li>
            </ol>
        </section>

        {{-- Lembar Pengesahan Sah (Tanda Tangan) --}}
        <section class="signatures mt-4 border-t border-line-board pt-3 text-[7.5pt]">
            <div class="grid grid-cols-3 gap-6 text-center">
                <div>
                    <p class="text-ink-muted">Pihak Penerima / Mitra Klien</p>
                    <p class="font-bold text-ink">{{ $user->company_name ?: 'Perusahaan Mitra' }}</p>
                    <div class="h-12 flex items-center justify-center">
                        <span class="rounded border border-dashed border-line-board px-2 py-0.5 font-mono text-[6.5pt] text-ink-quiet">
                            CAP &amp; TANDA TANGAN
                        </span>
                    </div>
                    <p class="font-bold text-ink underline">{{ $user->pic_name ?: $user->name }}</p>
                    <p class="text-[6.5pt] text-ink-muted">Purchasing / Receiving Dock</p>
                </div>

                <div>
                    <p class="text-ink-muted">Verifikasi Gudang &amp; Logistik</p>
                    <p class="font-bold text-ink">Sentra Logistik Lembang GPA</p>
                    <div class="h-12 flex items-center justify-center">
                        <span class="rounded border border-dashed border-brand/50 px-2 py-0.5 font-mono text-[6.5pt] text-brand">
                            QC PASUNDAN VALIDATED
                        </span>
                    </div>
                    <p class="font-bold text-ink underline">Agus Hendrawan, S.Pt.</p>
                    <p class="text-[6.5pt] text-ink-muted">Koordinator Mutu &amp; Armada</p>
                </div>

                <div>
                    <p class="text-ink-muted">Otorisasi Keuangan &amp; Faktur</p>
                    <p class="font-bold text-brand">Green Pasundan Agriculture</p>
                    <div class="h-12 flex items-center justify-center">
                        <div class="rounded border border-brand/60 px-2 py-0.5 text-center">
                            <span class="block font-mono text-[6pt] font-extrabold tracking-wider text-brand">GPA FINANCE VERIFIED</span>
                        </div>
                    </div>
                    <p class="font-bold text-brand underline">Siti Aminah, S.E.</p>
                    <p class="text-[6.5pt] text-ink-muted">Administrasi &amp; Sekretariat Keuangan</p>
                </div>
            </div>
            <div class="mt-2 text-center text-[6pt] text-ink-quiet font-mono">
                DOKUMEN RESMI DICETAK MELALUI SISTEM AGROORDER GPA PADA {{ $printDate }} &bull; ARSIP ELEKTRONIK NO. {{ $docNumber }}
            </div>
        </section>
    </main>

    @if (request()->query('auto_print'))
        <script>
            window.addEventListener('load', () => {
                window.print();
            });
        </script>
    @endif
</body>
</html>
