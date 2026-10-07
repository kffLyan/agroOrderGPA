<?php

namespace App\Http\Controllers\Klien;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Product;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected OrderService $orderService;
    protected StockService $stockService;

    public function __construct(OrderService $orderService, StockService $stockService)
    {
        $this->orderService = $orderService;
        $this->stockService = $stockService;
    }

    /**
     * Menampilkan antarmuka keranjang belanja dan penyusunan draf PO mandiri
     */
    public function index(Request $request)
    {
        $user = Auth::guard('web')->user();
        $isB2b = $user->client_type === 'B2B_KONTRAK';

        // Ambil produk aktif beserta ketersediaan stok panen
        $products = Product::where('is_active', true)->get();

        $activeContracts = Contract::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get()
            ->keyBy('product_id');

        $commodities = $products->map(function ($product) use ($user, $activeContracts) {
            $hasContract = $activeContracts->has($product->id);
            $contract = $activeContracts->get($product->id);
            $effectivePrice = $hasContract ? (float) $contract->fixed_price_per_kg : (float) $product->base_price;
            $stock = $this->stockService->getAvailableStock($product->id);
            $moq = (float) ($product->minimum_order ?? 10);

            return [
                'key'        => (string) $product->id,
                'id'         => $product->id,
                'sku'        => $product->sku ?? ('SKU-' . $product->id),
                'name'       => $product->name,
                'grade'      => $product->grade ?: 'Grade A',
                'packaging'  => "Krat Plastik Higienis 10 {$product->unit}",
                'origin'     => 'Lembang',
                'cold_chain' => '2-6°C',
                'price'      => $effectivePrice,
                'stock'      => $stock,
                'moq'        => $moq,
                'step'       => 1,
                'crate_kg'   => 10,
                'pack_label' => 'Krat',
            ];
        })->values()->all();

        // Target pengiriman minimal H+1
        $defaultDeliveryDate = now()->addDay()->format('Y-m-d');

        $delivery = [
            'step'       => '02',
            'title'      => 'Parameter Pengiriman & Titik Bongkar Muat',
            'subtitle'   => 'Pilih titik dock tujuan dan waktu kedatangan armada chiller',
            'dock_label' => 'Titik Gudang / Receiving Dock',
            'date'       => [
                'label' => 'Tanggal Pengiriman',
                'value' => $defaultDeliveryDate,
                'hint'  => 'Minimal H+1 sesuai siklus panen dan sortasi harian.',
            ],
            'window'     => [
                'label' => 'Jendela Kedatangan Armada',
                'value' => '04:00 - 06:00 WIB (Subuh)',
                'hint'  => 'SLA Bongkar Cepat < 30 Menit di Dock Penerimaan.',
            ],
            'windows'    => [
                ['key' => '04:00 - 06:00 WIB (Subuh)', 'label' => '04:00 - 06:00 WIB (Subuh — Rekomendasi Resto/Horeca)', 'note' => 'Disarankan untuk Horeca'],
                ['key' => '07:00 - 09:00 WIB (Pagi)', 'label' => '07:00 - 09:00 WIB (Pagi — Pengiriman Reguler)', 'note' => 'Reguler Supermarket/Retail'],
            ],
        ];

        $driverNote = [
            'label' => 'Instruksi Khusus Supir Armada & Catatan Gerbang',
            'value' => 'Harap konfirmasi ke PIC dock penerimaan 30 menit sebelum armada tiba.',
            'hint'  => 'Nomor kontak darurat supir akan otomatis dikirimkan via notifikasi WhatsApp saat armada diberangkatkan.',
        ];

        $receiving = [
            'label'  => 'PIC Penerimaan Barang Terverifikasi',
            'action' => 'Ganti PIC',
            'roster' => [
                [
                    'name'  => $user->pic_name ?: $user->name,
                    'role'  => 'Kepala Gudang / Receiving',
                    'phone' => $user->pic_phone ?: ($user->phone ?: '0812-3456-7890'),
                ],
                [
                    'name'  => 'Staff Receiving Shift Subuh',
                    'role'  => 'Quality Checker Lapangan',
                    'phone' => '0821-9876-5432',
                ],
            ],
        ];

        $docks = [
            [
                'key'      => 'dock-utama',
                'name'     => $user->company_name ?: 'Gudang Utama Klien',
                'address'  => $user->address ?: 'Alamat Pengiriman Utama Klien',
                'chip'     => 'Dock Utama',
                'access'   => 'Akses Truk Engkel Box Chiller & CDD Long',
                'selected' => true,
            ],
            [
                'key'      => 'dock-sentral',
                'name'     => 'Central Kitchen / Processing Plant',
                'address'  => ($user->address ?: 'Bandung Area') . ' (Hub Cabang)',
                'chip'     => 'Alternatif',
                'access'   => 'Ramp Hidrolik Cold Storage Standar HACCP',
                'selected' => false,
            ],
        ];

        // Metode pembayaran: B2B Kontrak mendapat TEMPO_TOP, Reguler mendapat TRANSFER/QRIS/COD
        $paymentMethods = [];

        if ($isB2b) {
            $paymentMethods[] = [
                'key'       => 'TEMPO_TOP',
                'name'      => 'Term of Payment (Tempo 30 Hari B2B)',
                'note'      => 'Tagihan diagregasi ke faktur konsolidasi akhir bulan.',
                'note_tone' => 'success',
                'chip'      => 'Rekomendasi B2B',
                'selected'  => true,
            ];
        }

        $paymentMethods[] = [
            'key'       => 'TRANSFER_BANK',
            'name'      => 'Transfer Bank BCA / Mandiri (H+0)',
            'note'      => 'Unggah bukti transfer pembayaran langsung pada sistem.',
            'note_tone' => 'neutral',
            'chip'      => 'Langsung',
            'selected'  => !$isB2b,
        ];

        $paymentMethods[] = [
            'key'       => 'QRIS',
            'name'      => 'QRIS Dinamis GPA (Instant Settlement)',
            'note'      => 'Konfirmasi verifikasi otomatis setelah scan barcode.',
            'note_tone' => 'neutral',
            'chip'      => 'Otomatis',
            'selected'  => false,
        ];

        $paymentMethods[] = [
            'key'       => 'COD',
            'name'      => 'Cash on Delivery (Bayar Saat Timbang Dock)',
            'note'      => 'Hanya berlaku untuk pengiriman reguler dengan serah terima fisik.',
            'note_tone' => 'neutral',
            'chip'      => 'Tunai',
            'selected'  => false,
        ];

        $summary = [
            'title'            => 'Ringkasan Estimasi PO',
            'subtotal_label'   => 'Estimasi Subtotal',
            'shipping_label'   => 'Biaya Distribusi Logistik',
            'shipping_note'    => 'Gratis ongkir franco gudang GPA',
            'tax_label'        => 'PPN Hasil Pertanian (PPN DTP)',
            'tax_chip'         => 'Bebas PPN 0%',
            'total_label'      => 'Total Nilai Estimasi PO',
            'total_note'       => '*Nilai tagihan final dihitung berdasarkan bobot netto riil pada timbangan digital packing house.',
            'payment_label'    => 'Pilihan Metode Pembayaran',
            'payment_chip'     => $isB2b ? 'Fasilitas Kontrak' : 'Reguler',
            'submit'           => 'Ajukan PO ke Admin',
            'save'             => 'Simpan Draf Pesanan',
            'status'           => 'Draf Siap Diajukan',
            'submitted_status' => 'Menunggu Verifikasi Admin',
            'sla'              => '< 2 Jam Kerja',
        ];

        $draft = [
            'breadcrumb'   => ['Portal Klien', 'Katalog Komoditas'],
            'title'        => 'Penyusunan Draft PO',
            'heading'      => 'Keranjang & Pengajuan Purchase Order',
            'subheading'   => 'Periksa alokasi komoditas panen, pilih titik bongkar muat dock, dan ajukan pesanan resmi.',
            'telemetry'    => 'Live Connected',
            'sync'         => 'Sinkronisasi Otomatis',
            'cutoff_label' => 'Batas Waktu Order Subuh:',
            'cutoff_value' => 'Pukul 16:00 WIB',
            'po_number'    => 'DRAFT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
        ];

        $warning = [
            'chip'  => 'Rule 04 Operasional',
            'title' => 'Pemberitahuan Cut-Off Panen & Distribusi Subuh',
            'body'  => 'Order yang masuk sebelum pukul 16:00 WIB dipanen sore hari dan tiba di dock gudang Anda pada jendela pukul 04:00 - 06:00 WIB subuh berikutnya dalam kondisi segar pre-cooled.',
        ];

        $itemsSection = [
            'step'     => '01',
            'title'    => 'Daftar Komoditas & Volume Pesanan',
            'subtitle' => 'komoditas dalam draf PO',
            'chip'     => 'Harga Terkunci',
        ];

        $guarantee = [
            'title' => 'Jaminan Cold-Chain & Standar Mutu GPA',
            'body'  => 'Komoditas dilindungi pendingin 2-6°C sejak pemanenan. Apabila terjadi deviasi mutu lebih dari 2%, klaim retur dapat diajukan saat serah terima dock.',
        ];

        $demoLines = [];

        return view('klien.cart', compact(
            'commodities',
            'delivery',
            'driverNote',
            'receiving',
            'docks',
            'paymentMethods',
            'summary',
            'draft',
            'warning',
            'itemsSection',
            'guarantee',
            'demoLines',
            'user'
        ));
    }
}
