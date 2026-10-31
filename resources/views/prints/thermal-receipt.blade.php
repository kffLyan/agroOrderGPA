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

            /* Kunci ukuran struk tetap 80mm/58mm meskipun dicetak di kertas A4 atau disimpan ke PDF */
            .thermal-canvas {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 8mm auto !important;
                padding: 5mm 4mm !important;
                box-shadow: none !important;
                border: 1px dashed #94a3b8 !important;
                box-sizing: border-box !important;
            }

            .thermal-canvas.width-58 {
                width: 58mm !important;
                max-width: 58mm !important;
                margin: 8mm auto !important;
                padding: 3mm 2mm !important;
            }
        }

        @media screen {
            body {
                background: #eef2ea;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding-bottom: 40px;
            }

            .thermal-canvas {
                width: 80mm;
                min-height: 120mm;
                margin: 24px auto;
                background: #ffffff;
                box-shadow: 0 10px 30px -8px rgba(0, 0, 0, 0.25), 0 0 1px rgba(0, 0, 0, 0.1);
                border: 1px solid #e2e8f0;
                padding: 6mm 5mm;
                border-radius: 4px;
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
            line-height: 1.35;
            color: #111827;
        }

        .receipt-font-compact {
            font-size: 10px;
            line-height: 1.25;
        }

        .dotted-line {
            border-top: 1px dashed #6b7280;
            margin: 6px 0;
        }

        .double-line {
            border-top: 2px dashed #111827;
            margin: 8px 0;
        }

        .receipt-logo {
            filter: grayscale(100%) contrast(150%) brightness(85%);
            -webkit-filter: grayscale(100%) contrast(150%) brightness(85%);
            max-height: 42px;
            width: auto;
            margin: 0 auto 6px auto;
            display: block;
        }
    </style>
</head>

<body class="font-sans antialiased">
    <!-- Floating Toolbar (Screen Only) -->
    <div class="no-print sticky top-0 z-30 w-full border-b border-line bg-surface/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-4 py-2.5">
            <div class="flex items-center gap-2">
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-line-strong bg-white px-3 py-1.5 text-xs font-semibold text-ink transition-colors hover:bg-surface-sunken">
                    <x-gpa.icon name="arrow-left" class="h-3.5 w-3.5" />
                    Kembali
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
        <header class="text-center">
            <!-- LOGO RESMI GPA (MONOKROM / HITAM-ABU SESUAI FORMAT THERMAL POS) -->
            <div class="mb-1.5 flex justify-center">
                <img src="{{ asset('gpaleaves.png') }}" alt="Logo GPA" class="receipt-logo h-10 w-10 object-contain" />
            </div>
            <h1 class="text-sm font-extrabold uppercase tracking-wide text-black">
                {{ $company['name'] }}
            </h1>
            <p class="receipt-font-compact text-gray-700 mt-0.5">
                {{ $company['address'] }}
            </p>
            <p class="receipt-font-compact text-gray-700">
                Telp/WA: {{ $company['phone'] }}
            </p>
            <p class="receipt-font-compact text-gray-500 text-[9px] mt-0.5">
                {{ $company['legal'] }}
            </p>
        </header>

        <div class="double-line"></div>

        <!-- METADATA STRUK -->
        <div class="receipt-font-compact space-y-0.5 text-gray-800">
            <div class="flex justify-between">
                <span>No. Struk</span>
                <span class="font-bold font-mono">{{ $receipt_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>No. PO</span>
                <span class="font-mono">{{ $po_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu</span>
                <span>{{ $date_formatted }}</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir</span>
                <span class="font-semibold">{{ $cashier }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan</span>
                <span class="font-semibold">{{ $client_name }}</span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- TABEL RINCIAN KOMODITAS -->
        <div class="space-y-2 my-2">
            @forelse ($items as $item)
                <div>
                    <div class="font-bold text-black leading-snug">
                        {{ $item['name'] }}
                    </div>
                    <div class="flex justify-between text-gray-700 receipt-font-compact pl-1">
                        <span>{{ $item['qty_fmt'] }} &times; {{ $item['price_fmt'] }}</span>
                        <span class="font-bold font-mono text-black">Rp {{ $item['total_fmt'] }}</span>
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
        <div class="space-y-1 text-black font-semibold">
            <div class="flex justify-between text-gray-700 font-normal">
                <span>Subtotal</span>
                <span class="font-mono">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            @if (($shipping_cost ?? 0) > 0)
                <div class="flex justify-between text-gray-700 font-normal">
                    <span>Ongkir Chiller</span>
                    <span class="font-mono">Rp {{ number_format($shipping_cost, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between text-gray-700 font-normal">
                <span>{{ $tax_label ?? 'PPN' }}</span>
                <span class="font-mono">
                    @if (($tax_amount ?? 0) > 0)
                        Rp {{ number_format($tax_amount, 0, ',', '.') }}
                    @else
                        Rp 0 (Bebas)
                    @endif
                </span>
            </div>

            <div class="flex justify-between text-base font-extrabold border-t border-black/30 pt-1 text-black">
                <span>TOTAL</span>
                <span class="font-mono">Rp {{ number_format($grand_total, 0, ',', '.') }}</span>
            </div>

            @if ($payment_method === 'CASH' || $payment_method === 'TUNAI')
                <div class="flex justify-between text-gray-800 pt-0.5">
                    <span>Uang Tunai (CASH)</span>
                    <span class="font-mono">Rp {{ $cash_received ?: number_format($grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-800 font-bold">
                    <span>Kembalian</span>
                    <span class="font-mono">Rp {{ $cash_change ?: '0' }}</span>
                </div>
            @else
                <div class="flex justify-between text-gray-800 pt-0.5">
                    <span>Metode Bayar</span>
                    <span class="font-mono font-bold">{{ $payment_method }}</span>
                </div>
            @endif
        </div>

        <div class="double-line"></div>

        <!-- STATUS PELUNASAN -->
        <div class="text-center space-y-1 my-2">
            <div class="inline-block border border-black px-3 py-0.5 font-bold uppercase tracking-widest text-xs">
                *** {{ $payment_status }} ***
            </div>
            @if ($payment_ref)
                <p class="receipt-font-compact text-gray-600 font-mono text-[9.5px]">
                    Ref: {{ $payment_ref }}
                </p>
            @endif
        </div>

        <div class="dotted-line"></div>

        <!-- FOOTER PESAN STRUK -->
        <footer class="text-center receipt-font-compact space-y-1 pt-1 text-gray-700">
            <p class="font-semibold text-black">
                * Sayuran Segar Langsung Petani Ciwidey *
            </p>
            <p>
                Terima kasih atas kunjungan &amp; kepercayaan Anda!
            </p>
            <p class="text-[9px] text-gray-500">
                Barang yang sudah dibeli telah ditimbang dan dicek bersama. Simpan struk ini sebagai bukti pembayaran yang sah.
            </p>
            <p class="pt-2 font-mono text-[8px] text-gray-400">
                --- AgroOrder GPA &bull; {{ date('Y') }} ---
            </p>
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
    </script>
</body>

</html>
