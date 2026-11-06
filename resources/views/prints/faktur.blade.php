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
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            .table-scroll-container {
                overflow: visible !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        @media screen {
            body {
                background-color: #f1f5ee;
                min-height: 100vh;
            }

            .a4-canvas {
                width: 100%;
                max-width: 210mm;
                min-height: auto;
                margin: 12px auto;
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 8px 30px -8px rgba(20, 58, 0, 0.15), 0 2px 8px -2px rgba(0, 0, 0, 0.05);
                padding: 16px 20px;
            }

            @media (min-width: 768px) {
                .a4-canvas {
                    min-height: 297mm;
                    margin: 24px auto 48px auto;
                    border-radius: 4px;
                    padding: 28px 36px;
                    box-shadow: 0 16px 48px -12px rgba(20, 58, 0, 0.22);
                }
            }
        }

        .a4-canvas {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #191c18;
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

<body class="text-ink antialiased">
    <!-- Floating Toolbar (No Print) -->
    <div class="no-print sticky top-0 z-30 border-b border-line bg-surface/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-[210mm] flex-col gap-2.5 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-4">
            <!-- Sisi Kiri: Tombol Kembali & Info Dokumen -->
            <div class="flex items-center justify-between gap-2 sm:justify-start">
                @php
                    $currentUser = auth()->user();
                    $backUrl = match($currentUser?->role) {
                        'SEKRETARIS' => route('sekretaris.faktur'),
                        'DIREKTUR'   => route('direktur.piutang'),
                        default      => route('klien.dokumen'),
                    };
                    $backLabel = match($currentUser?->role) {
                        'SEKRETARIS' => 'Kembali ke Faktur',
                        'DIREKTUR'   => 'Kembali ke Piutang',
                        default      => 'Kembali ke Dokumen',
                    };
                @endphp
                <a href="{{ $backUrl }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-strong bg-white px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-sunken">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    <span>{{ $backLabel }}</span>
                </a>
                <div class="flex items-center gap-1.5">
                    <span class="rounded bg-brand/10 px-2 py-0.5 text-[11px] font-bold text-brand">
                        FAKTUR / INVOICE PENAGIHAN
                    </span>
                    @if (($invoice['status'] ?? '') === 'PAID')
                        <span class="rounded border border-emerald-600 bg-emerald-100 px-2 py-0.5 text-[10px] font-extrabold text-emerald-800">
                            ✓ LUNAS / PAID
                        </span>
                    @elseif (($invoice['status'] ?? '') === 'OVERDUE')
                        <span class="rounded border border-rose-600 bg-rose-100 px-2 py-0.5 text-[10px] font-extrabold text-rose-800">
                            ! JATUH TEMPO / OVERDUE
                        </span>
                    @else
                        <span class="rounded border border-amber-600 bg-amber-100 px-2 py-0.5 text-[10px] font-extrabold text-amber-800">
                            BELUM LUNAS / UNPAID
                        </span>
                    @endif
                    <span class="font-mono text-xs font-extrabold text-ink sm:hidden">
                        {{ $invoice['id'] ?? '-' }}
                    </span>
                </div>
            </div>

            <!-- Sisi Kanan: Aksi & Tombol Cetak (Responsif Mobile) -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <!-- Tombol Cetak / PDF -->
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-3.5 py-1.5 text-xs font-bold text-white shadow transition-all hover:bg-brand-strong active:scale-95">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    <span>Cetak / PDF</span>
                </button>
            </div>
        </div>
    </div>

    <main class="a4-canvas">
        <!-- KOP SURAT ASLI GREEN PASUNDAN AGRICULTURE DARI CONTOH RESMI -->
        <header class="flex flex-col sm:flex-row items-start justify-between border-b-2 border-brand pb-3 gap-3">
            <div class="flex items-start gap-3">
                <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-12 w-12 sm:h-14 sm:w-14 shrink-0 object-contain">
                <div>
                    <h1 class="text-sm sm:text-base font-black tracking-wider text-brand">
                        GREEN PASUNDAN AGRICULTURE
                    </h1>
                    <p class="text-[7pt] sm:text-[7.5pt] font-semibold text-ink leading-tight">
                        {{ $brand ?? 'KOPERASI PRODUSEN AGRO GREEN PASUNDAN (GPA)' }} &middot; {{ $company['legal_number'] ?? 'Badan Hukum : Nomor AHU-0004853.AH.01.29.TH 2024' }}
                    </p>
                    <p class="text-[7pt] sm:text-[7.5pt] leading-tight text-ink-body mt-0.5">
                        {{ $company['address'] ?? 'Kp. Lebakwangi RT. 001 RW. 009 Desa Panundaan Kecamatan Ciwidey Kabupaten Bandung (40973)' }}
                    </p>
                    <p class="text-[7pt] sm:text-[7.5pt] leading-tight text-ink-body font-medium">
                        Tlp/ Whatsapp : {{ $company['phone'] ?? '081321422231' }}
                    </p>
                </div>
            </div>

            <!-- Metadata Faktur / Invoice di Sisi Kanan -->
            <div class="w-full sm:w-auto text-left sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-line-hair">
                <div class="flex items-center justify-between sm:justify-end gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-black uppercase tracking-wider text-brand">
                        INVOICE
                    </h2>
                    @if (($invoice['status'] ?? '') === 'PAID')
                        <span class="inline-flex items-center gap-1 rounded border-2 border-emerald-600 bg-emerald-50 px-2.5 py-0.5 text-[8pt] font-black uppercase tracking-wider text-emerald-700 shadow-2xs">
                            ✓ LUNAS / PAID
                        </span>
                    @elseif (($invoice['status'] ?? '') === 'OVERDUE')
                        <span class="inline-flex items-center gap-1 rounded border-2 border-rose-600 bg-rose-50 px-2.5 py-0.5 text-[8pt] font-black uppercase tracking-wider text-rose-700 shadow-2xs">
                            ! LEWAT JATUH TEMPO / OVERDUE
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded border-2 border-amber-600 bg-amber-50 px-2.5 py-0.5 text-[8pt] font-black uppercase tracking-wider text-amber-700 shadow-2xs">
                            BELUM LUNAS / UNPAID
                        </span>
                    @endif
                </div>
                <table class="mt-1 text-[7.5pt] sm:text-[8pt] text-left sm:ml-auto">
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
                    <tr>
                        <td class="pr-2 font-semibold text-ink-muted">Status Tagihan</td>
                        <td class="font-bold">: 
                            @if (($invoice['status'] ?? '') === 'PAID')
                                <span class="text-emerald-700 font-extrabold">LUNAS / PAID</span>
                            @elseif (($invoice['status'] ?? '') === 'OVERDUE')
                                <span class="text-rose-700 font-extrabold">LEWAT JATUH TEMPO / OVERDUE</span>
                            @else
                                <span class="text-amber-700 font-extrabold">BELUM LUNAS / UNPAID</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </header>

        <!-- Kotak Informasi Penerima (To & Office) -->
        <div class="mt-3 flex flex-col sm:flex-row border border-ink text-[8pt] sm:text-[8.5pt]">
            <div class="w-full sm:w-1/2 border-b sm:border-b-0 sm:border-r border-ink bg-white px-3 py-1 font-semibold text-ink">
                <span class="text-ink-muted">To : </span>
                <span class="font-bold">{{ $client['to'] ?? ($client['name'] ?? 'Mitra Klien') }}</span>
            </div>
            <div class="w-full sm:w-1/2 bg-white px-3 py-1 font-semibold text-ink">
                <span class="text-ink-muted">Office : </span>
                <span class="font-bold">{{ $client['office'] ?? ($client['name'] ?? 'Perusahaan Klien') }}</span>
            </div>
        </div>

        {{-- TABEL RINCIAN KOMODITAS (FORMAT STANDAR RESMI GPA) --}}
        <div class="mt-3 table-scroll-container overflow-x-auto">
            <table class="table-bordered text-[8pt] min-w-[500px] sm:min-w-full">
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
                        <td colspan="6" class="py-1 px-2 font-bold">{{ $regular['title'] ?? ($table_title ?? 'Daftar Pasokan Komoditas') }}</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items ?? ($regular['items'] ?? []) as $idx => $rItem)
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
                    @empty
                        <tr>
                            <td colspan="6" class="py-3 text-center text-ink-muted italic">Tidak ada rincian komoditas pada faktur ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-[#fef08a] font-bold text-ink">
                        <td colspan="5" class="py-1 px-2 text-center uppercase tracking-wider font-extrabold">SUBTOTAL</td>
                        <td class="py-1 px-2 text-right font-mono font-extrabold">
                            <span class="float-left">Rp</span>
                            {{ number_format($subtotal ?? ($regular['subtotal'] ?? $grand_total), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- BARIS TOTAL TAGIHAN GABUNGAN --}}
        <div class="mt-3 table-scroll-container overflow-x-auto">
            <table class="table-bordered text-[8pt] sm:text-[8.5pt] min-w-[500px] sm:min-w-full">
                <tfoot>
                    @if (($shipping_cost ?? 0) > 0 || ($tax_amount ?? 0) > 0)
                        <tr class="bg-gray-50 text-ink">
                            <td class="py-1 px-3 text-right font-medium" style="width: 70%;">
                                Subtotal Komoditas
                            </td>
                            <td class="py-1 px-3 text-right font-mono font-semibold" style="width: 30%;">
                                Rp {{ number_format($subtotal ?? ($grand_total - ($shipping_cost ?? 0) - ($tax_amount ?? 0)), 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if (($shipping_cost ?? 0) > 0)
                        <tr class="bg-gray-50 text-ink">
                            <td class="py-1 px-3 text-right font-medium" style="width: 70%;">
                                Biaya Distribusi &amp; Logistik Armada Chiller
                            </td>
                            <td class="py-1 px-3 text-right font-mono font-semibold" style="width: 30%;">
                                Rp {{ number_format($shipping_cost, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if (isset($tax_label))
                        <tr class="bg-gray-50 text-ink">
                            <td class="py-1 px-3 text-right font-medium" style="width: 70%;">
                                {{ $tax_label }}
                            </td>
                            <td class="py-1 px-3 text-right font-mono font-semibold" style="width: 30%;">
                                @if (($tax_amount ?? 0) > 0)
                                    Rp {{ number_format($tax_amount, 0, ',', '.') }}
                                @else
                                    Rp 0 (Bebas)
                                @endif
                            </td>
                        </tr>
                    @endif
                    <tr class="bg-[#1e3a8a] text-white font-black">
                        <td class="py-2 px-3 text-right uppercase tracking-widest font-black" style="width: 70%;">
                            TOTAL TAGIHAN
                        </td>
                        <td class="py-2 px-3 text-right font-mono font-black text-[10pt] sm:text-[10.5pt] bg-[#172554]" style="width: 30%;">
                            Rp {{ number_format($grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- FOOTER PEMBAYARAN & TANDA TANGAN ASLI SESUAI DOKUMEN GPA -->
        <footer class="mt-6 flex flex-col sm:flex-row items-start justify-between border-t border-ink pt-3 gap-5 text-[8pt]">
            <!-- Instruksi Pembayaran Bank BCA Resmi -->
            <div class="w-full sm:w-7/12 leading-relaxed">
                @if (($invoice['status'] ?? '') === 'PAID')
                    <div class="my-1.5 rounded-lg border-2 border-emerald-500 bg-emerald-50/80 p-3 shadow-2xs">
                        <div class="flex items-center gap-2 text-emerald-800">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white font-bold text-xs">✓</span>
                            <p class="font-black text-xs uppercase tracking-wider">TAGIHAN TELAH LUNAS / PAID</p>
                        </div>
                        <p class="text-[7.5pt] text-emerald-800 mt-1.5 font-medium leading-relaxed">
                            Seluruh kewajiban pembayaran faktur ini telah diverifikasi sah oleh Divisi Keuangan Koperasi Green Pasundan Agriculture. Dokumen ini berlaku sebagai bukti pelunasan tagihan resmi.
                        </p>
                    </div>
                @else
                    <p class="font-semibold text-ink">Setelah tagihan diterima, silakan transfer ke rekening resmi:</p>
                    <div class="my-1.5 rounded-lg border border-gray-300 bg-gray-50 p-2.5 font-mono shadow-2xs">
                        <div class="flex items-center gap-2">
                            <span class="inline-block px-1.5 py-0.5 rounded bg-blue-700 text-white text-[9px] font-sans font-bold">BCA</span>
                            <p class="font-bold text-ink text-xs sm:text-[13px]">{{ $company['bank_account'] ?? '6768489174' }}</p>
                        </div>
                        <p class="text-xs font-semibold text-brand mt-1">A.N {{ $company['bank_holder'] ?? 'Green Pasundan Agriculture' }}</p>
                    </div>
                    <p class="text-ink-body">Silakan kirimkan konfirmasi/bukti transfer jika pembayaran telah dilakukan.</p>
                @endif
                <p class="font-bold text-brand mt-1">Terima kasih atas kerjasamanya.</p>
            </div>

            <!-- Tanda Tangan & Cap Stempel Bulat Koperasi GPA Asli -->
            <div class="w-full sm:w-4/12 text-center pt-2 sm:pt-0">
                <p class="font-semibold text-ink">Hormat Kami,</p>
                <p class="text-[7.5pt] font-medium text-ink-body">Koperasi Produsen Green Pasundan Agriculture</p>

                <!-- Stamp Bulat Koperasi GPA Asli -->
                <div class="relative my-1 flex items-center justify-center">
                    <svg class="h-20 w-20 sm:h-24 sm:w-24 text-blue-700/85 stamp-rotate" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
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

                <p class="font-extrabold uppercase tracking-wider text-ink underline text-[8.5pt] sm:text-[9pt]">
                    {{ $company['signer_name'] ?? 'AGUS DIKI PERMANA' }}
                </p>
                <p class="text-[7pt] text-ink-muted">Ketua Pengurus Koperasi GPA</p>
            </div>
        </footer>
    </main>
    <script>
        if (new URLSearchParams(window.location.search).get('print') === '1') {
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 300);
            });
        }
    </script>
</body>

</html>
