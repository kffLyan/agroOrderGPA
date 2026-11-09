<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>STRUK KASIR &middot; {{ $company['name'] }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: auto;
            margin: 0;
        }

        @media print {
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .thermal-canvas {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 auto !important;
                padding: 4mm 3mm !important;
                box-shadow: none !important;
                border: none !important;
                box-sizing: border-box !important;
            }

            .thermal-canvas.width-58 {
                width: 58mm !important;
                max-width: 58mm !important;
                margin: 0 auto !important;
                padding: 3mm 2mm !important;
            }
        }

        @media screen {
            body {
                background: #f1f5f9;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding-bottom: 40px;
            }

            .thermal-canvas {
                width: 80mm;
                min-height: 100mm;
                margin: 24px auto;
                background: #ffffff;
                box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.1), 0 0 1px rgba(0, 0, 0, 0.15);
                border: 1px solid #e2e8f0;
                padding: 6mm 5mm;
                border-radius: 6px;
                transition: width 0.2s ease;
            }

            .thermal-canvas.width-58 {
                width: 58mm;
                padding: 4mm 3mm;
            }
        }

        .receipt-font {
            font-family: 'JetBrains Mono', 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.4;
            color: #111827;
        }

        .receipt-font-compact {
            font-size: 10px;
            line-height: 1.3;
        }

        .dotted-line {
            border-top: 1px dashed #9ca3af;
            margin: 6px 0;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <!-- Floating Toolbar (Screen Only) -->
    <div class="no-print sticky top-0 z-30 w-full border-b border-line bg-surface/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                @php
                    $currentUser = auth()->user();
                    $backUrl = match($currentUser?->role) {
                        'SEKRETARIS' => route('sekretaris.surat-jalan'),
                        'DIREKTUR'   => route('direktur.piutang.index'),
                        'ARMADA'     => route('armada.dashboard'),
                        'KOORDINATOR'=> route('koordinator.dashboard'),
                        'KLIEN'      => route('klien.dokumen'),
                        default      => url('/dashboard'),
                    };
                    $backLabel = match($currentUser?->role) {
                        'SEKRETARIS' => 'Kembali ke Surat Jalan',
                        'DIREKTUR'   => 'Kembali ke Piutang',
                        'ARMADA'     => 'Kembali ke Dasbor',
                        'KOORDINATOR'=> 'Kembali ke Dasbor',
                        default      => 'Kembali ke Dokumen',
                    };
                @endphp
                <a href="{{ $backUrl }}"
                    onclick="if (window.history.length > 1) { window.history.back(); return false; } else { window.close(); setTimeout(function() { window.location.href = '{{ $backUrl }}'; }, 150); return false; }"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-strong bg-white px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-sunken">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    <span>{{ $backLabel }}</span>
                </a>
                <span class="rounded bg-brand/10 px-2.5 py-1 text-xs font-extrabold uppercase tracking-wider text-brand">
                    STRUK KASIR THERMAL
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Toggle Ukuran Kertas Thermal -->
                <div class="inline-flex rounded-lg border border-line bg-white p-0.5 text-xs shadow-xs" id="width-toggle">
                    <button type="button" onclick="setReceiptWidth(80)" id="btn-80"
                        class="rounded-md bg-brand px-2.5 py-1 font-bold text-white transition-colors">
                        80 mm (Standar)
                    </button>
                    <button type="button" onclick="setReceiptWidth(58)" id="btn-58"
                        class="rounded-md px-2.5 py-1 font-semibold text-ink-body hover:text-ink transition-colors">
                        58 mm (Mini)
                    </button>
                </div>

                <!-- Tombol ke Format Faktur A4 -->
                <a href="{{ route('prints.invoice', ['invoice' => $invoice_number]) }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-strong bg-white px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-sunken">
                    <x-gpa.icon name="file-text" class="h-3.5 w-3.5 text-ink-body" />
                    Format Faktur Resmi (A4)
                </a>

                <!-- Tombol Print -->
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-4 py-1.5 text-xs font-bold text-accent shadow transition-all hover:bg-brand-hover">
                    <x-gpa.icon name="printer" class="h-3.5 w-3.5" />
                    Cetak Struk (Ctrl+P)
                </button>
            </div>
        </div>
    </div>

    <!-- Canvas Struk Kertas Kasir Thermal -->
    <main id="receipt-container" class="thermal-canvas receipt-font">
        <!-- HEADER USAHA -->
        <header class="text-center pb-1">
            <div class="flex items-center justify-center gap-1.5 mb-1">
                <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="h-6 w-6 object-contain" />
                <span class="text-[11px] font-bold tracking-wider uppercase text-black">GREEN PASUNDAN</span>
            </div>
            <h1 class="text-xs font-extrabold uppercase tracking-wide text-black">
                {{ $company['name'] }}
            </h1>
            <p class="receipt-font-compact text-gray-600 mt-0.5">
                {{ $company['address'] }}
            </p>
            <p class="receipt-font-compact text-gray-600">
                Telp/WA: {{ $company['phone'] }}
            </p>
        </header>

        <div class="dotted-line"></div>

        <!-- METADATA STRUK -->
        <div class="receipt-font-compact space-y-0.5 text-gray-800">
            <div class="flex justify-between">
                <span class="text-gray-500">No. Struk</span>
                <span class="font-bold font-mono text-black">{{ $receipt_number }}</span>
            </div>
            @if (!empty($po_number) && $po_number !== '-')
                <div class="flex justify-between">
                    <span class="text-gray-500">No. PO</span>
                    <span class="font-mono text-black">{{ $po_number }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-gray-500">Tanggal</span>
                <span class="text-black">{{ $date_formatted }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Kasir</span>
                <span class="font-medium text-black">{{ $cashier }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Pelanggan</span>
                <span class="font-medium text-black">{{ $client_name }}</span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- TABEL RINCIAN KOMODITAS -->
        <div class="space-y-1.5 my-1.5">
            @forelse ($items as $item)
                <div>
                    <div class="font-semibold text-black leading-snug">
                        {{ $item['name'] }}
                    </div>
                    <div class="flex justify-between receipt-font-compact text-gray-600 pl-2">
                        <span>{{ $item['qty_fmt'] }} &times; {{ $item['price_fmt'] }}</span>
                        <span class="font-mono font-bold text-black">{{ $item['total_fmt'] }}</span>
                    </div>
                </div>
            @empty
                <div class="text-center italic text-gray-500 py-1">
                    Tidak ada rincian komoditas.
                </div>
            @endforelse
        </div>

        <div class="dotted-line"></div>

        <!-- RINGKASAN TOTAL & PEMBAYARAN -->
        <div class="space-y-1 text-black">
            <div class="flex justify-between text-gray-600 receipt-font-compact">
                <span>Subtotal</span>
                <span class="font-mono text-black">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            @if (($shipping_cost ?? 0) > 0)
                <div class="flex justify-between text-gray-600 receipt-font-compact">
                    <span>Ongkir Chiller</span>
                    <span class="font-mono text-black">Rp {{ number_format($shipping_cost, 0, ',', '.') }}</span>
                </div>
            @endif

            @if (($tax_amount ?? 0) > 0)
                <div class="flex justify-between text-gray-600 receipt-font-compact">
                    <span>{{ $tax_label ?? 'PPN' }}</span>
                    <span class="font-mono text-black">Rp {{ number_format($tax_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between text-sm font-extrabold border-t border-dashed border-gray-400 pt-1.5 mt-1 text-black">
                <span>TOTAL</span>
                <span class="font-mono">Rp {{ number_format($grand_total, 0, ',', '.') }}</span>
            </div>

            <div class="dotted-line"></div>

            <!-- DETAIL BAYAR -->
            <div class="receipt-font-compact space-y-0.5 text-gray-800">
                <div class="flex justify-between">
                    <span class="text-gray-500">Metode Bayar</span>
                    <span class="font-semibold text-black">{{ $payment_method_label ?? str_replace('_', ' ', $payment_method) }}</span>
                </div>

                @if ($payment_method === 'CASH' || $payment_method === 'TUNAI' || str_contains(strtoupper($payment_method_label ?? ''), 'TUNAI'))
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tunai Diterima</span>
                        <span class="font-mono text-black">Rp {{ $cash_received ?: number_format($grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-bold">Kembalian</span>
                        <span class="font-mono font-bold text-black">Rp {{ $cash_change ?: '0' }}</span>
                    </div>
                @endif

                @if ($payment_ref)
                    <div class="flex justify-between text-[9px] text-gray-500 font-mono pt-0.5">
                        <span>No. Ref</span>
                        <span>{{ $payment_ref }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- FOOTER PESAN STRUK -->
        <footer class="text-center receipt-font-compact space-y-0.5 pt-1 text-gray-600">
            <p class="font-bold text-black">Terima Kasih</p>
            <p class="text-[9.5px]">Sayuran Segar Berkualitas Mitra Petani Ciwidey</p>
            <p class="text-[8.5px] text-gray-400 mt-1">Simpan struk ini sebagai bukti pembayaran sah</p>
        </footer>
    </main>

    <script>
        function setReceiptWidth(width) {
            const container = document.getElementById('receipt-container');
            const btn80 = document.getElementById('btn-80');
            const btn58 = document.getElementById('btn-58');

            if (width === 58) {
                container.classList.add('width-58');
                btn58.classList.add('bg-brand', 'text-white', 'font-bold');
                btn58.classList.remove('text-ink-body');
                btn80.classList.remove('bg-brand', 'text-white', 'font-bold');
                btn80.classList.add('text-ink-body');
            } else {
                container.classList.remove('width-58');
                btn80.classList.add('bg-brand', 'text-white', 'font-bold');
                btn80.classList.remove('text-ink-body');
                btn58.classList.remove('bg-brand', 'text-white', 'font-bold');
                btn58.classList.add('text-ink-body');
            }
        }

        if (new URLSearchParams(window.location.search).get('print') === '1' || new URLSearchParams(window.location.search).has('autoprint')) {
            window.addEventListener('load', () => setTimeout(() => window.print(), 350));
        }
    </script>
</body>

</html>
