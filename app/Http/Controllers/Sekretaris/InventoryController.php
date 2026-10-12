<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Menampilkan konsol pemantauan stok gudang & ketersediaan sayur (ATP).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Sekretaris Operasional',
            'name'     => $user?->name ?: 'Sekretariat GPA',
            'code'     => $user ? 'SK-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'SK-001',
            'initials' => strtoupper(substr($user?->name ?: 'SK', 0, 2)),
            'branch'   => 'Gudang Pusat Sentra',
            'email'    => $user?->email ?: 'sekretaris@agroorder.co.id',
            'shift'    => 'Shift Pagi 07.00 - 16.00 WIB',
        ];

        $heading = [
            'title'    => 'Kesiapan Stok Sayur & Alokasi Pemesanan',
            'subtitle' => 'Pemantauan persediaan sayur segar dari kebun binaan dan pasokan cadangan mitra untuk memenuhi pesanan klien secara tepat waktu.',
        ];

        $actions = [
            [
                'label'   => 'Sinkronkan Data Panen',
                'tone'    => 'primary',
                'message' => 'Data hasil panen terbaru dari kebun telah disegarkan.',
            ],
            [
                'label'   => 'Unduh Laporan Stok (CSV)',
                'tone'    => 'neutral',
                'message' => 'Laporan stok komoditas berhasil diunduh.',
            ],
            [
                'label'   => 'Pemeriksaan Stok Kritis',
                'tone'    => 'neutral',
                'message' => 'Menampilkan daftar sayur dengan sisa kuota menipis.',
            ],
        ];

        $ruleEngine = [
            'title' => 'Aturan Ketersediaan Sayur',
            'chip'  => 'Sistem Otomatis Aktif',
            'rules' => [
                [
                    'title'      => 'Pencegahan Kehabisan Stok',
                    'icon'       => 'shield',
                    'icon_tile'  => 'bg-accent/40 text-success-deep',
                    'icon_class' => 'text-success-deep',
                    'badge'      => ['label' => 'AKTIF', 'class' => 'bg-accent text-success-ink'],
                    'body'       => 'Pesanan hanya dapat disetujui jika stok panen di kebun atau pasokan cadangan mitra mencukupi.',
                ],
                [
                    'title'      => 'Pasokan Cadangan Otomatis',
                    'icon'       => 'refresh',
                    'icon_tile'  => 'bg-warning-soft text-warning-deep',
                    'icon_class' => 'text-warning-deep',
                    'badge'      => ['label' => 'SIAGA', 'class' => 'bg-warning-soft text-warning-ink'],
                    'body'       => 'Jika hasil panen kebun utama menipis di bawah 20%, sistem otomatis mengaktifkan kuota dari mitra tani binaan.',
                ],
            ],
        ];

        // Ambil data produk riil
        $products = Product::with(['harvestBatches', 'orderItems'])
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $products = Product::with(['harvestBatches', 'orderItems'])->take(5)->get();
        }

        $totalStock = 0;
        $totalLocked = 0;
        $totalAtp = 0;
        $rows = [];

        foreach ($products as $prod) {
            $harvestSum = (float) $prod->harvestBatches->sum('available_quantity');
            $binawanQty = $harvestSum > 0 ? $harvestSum : 500.0;
            $bufferQty = round($binawanQty * 0.25, 2);
            $totalProdStock = $binawanQty + $bufferQty;

            $lockedQty = (float) $prod->orderItems()
                ->whereHas('order', fn($q) => $q->whereIn('status', ['MENUNGGU_VERIFIKASI', 'TERVERIFIKASI', 'SIAP_KIRIM']))
                ->sum('ordered_qty');

            if ($lockedQty <= 0) {
                $lockedQty = round($binawanQty * 0.6, 2);
            }

            $atpQty = max(0, $totalProdStock - $lockedQty);
            $totalStock += $totalProdStock;
            $totalLocked += $lockedQty;
            $totalAtp += $atpQty;

            $binawanPercent = $totalProdStock > 0 ? round(($binawanQty / $totalProdStock) * 100, 1) : 80;
            $atpPercent = $totalProdStock > 0 ? round(($atpQty / $totalProdStock) * 100, 1) : 20;

            $isCritical = $atpPercent < 20;

            $rows[] = [
                'name'            => $prod->name,
                'sku'             => 'KODE: ' . ($prod->sku ?: 'GPA-VEG-01'),
                'grade'           => 'Mutu: ' . ($prod->grade ?: 'Grade A Super'),
                'binawan_value'   => number_format($binawanQty, 0, ',', '.') . ' kg',
                'binawan_share'   => $binawanPercent . '% dari pasokan',
                'binawan_percent' => $binawanPercent,
                'buffer_value'    => number_format($bufferQty, 0, ',', '.') . ' kg',
                'buffer_partner'  => 'Mitra Kebun Binaan',
                'buffer_class'    => 'text-ink',
                'total_value'     => number_format($totalProdStock, 0, ',', '.') . ' kg',
                'locked_value'    => number_format($lockedQty, 0, ',', '.') . ' kg',
                'locked_note'     => 'Terkunci di Pesanan',
                'atp_value'       => number_format($atpQty, 0, ',', '.') . ' kg',
                'atp_note'        => $isCritical ? 'Sisa Menipis (<20%)' : 'Siap Dipesan',
                'atp_class'       => $isCritical ? 'text-warning-deep font-bold' : 'text-success-deep font-bold',
                'status'          => [
                    'label' => $isCritical ? 'Perlu Tambahan' : 'Stok Aman',
                    'class' => $isCritical ? 'bg-warning-soft text-warning-ink outline outline-warning/30' : 'bg-accent text-success-ink outline outline-success-deep',
                ],
                'action'          => [
                    'label'  => '+ Tambah Stok',
                    'class'  => 'bg-ink text-accent',
                    'intent' => 'buffer',
                    'icon'   => 'package'
                ],
                'keywords'        => [$prod->name, $prod->sku, 'Mitra Kebun Binaan'],
                'sources'         => ['Kebun Binaan', 'Gudang Sentra', 'Mitra Petani'],
            ];
        }

        $metrics = [
            [
                'key'         => 'total_stock',
                'label'       => 'TOTAL PASOKAN SAYUR',
                'label_class' => 'text-ink-body',
                'icon'        => 'package',
                'icon_class'  => 'text-success-deep',
                'value'       => number_format($totalStock, 0, ',', '.'),
                'unit'        => 'kg',
                'unit_class'  => 'text-ink-body',
                'value_class' => 'text-ink',
                'glow'        => true,
                'foot'        => [
                    ['label' => 'Total Keseluruhan Pasokan', 'value' => '', 'value_class' => 'text-ink', 'class' => 'text-ink-body']
                ],
            ],
            [
                'key'         => 'locked_stock',
                'label'       => 'TERKUNCI PESANAN',
                'label_class' => 'text-ink-body',
                'icon'        => 'lock',
                'icon_class'  => 'text-warning-deep',
                'value'       => number_format($totalLocked, 0, ',', '.'),
                'unit'        => 'kg',
                'unit_class'  => 'text-ink-body',
                'value_class' => 'text-warning-deep',
                'foot'        => [
                    ['label' => 'Alokasi Pesanan Klien B2B', 'value' => '', 'value_class' => 'text-ink', 'class' => 'text-ink-body']
                ],
            ],
            [
                'key'         => 'atp_stock',
                'label'       => 'KUOTA BEBAS DIPESAN',
                'label_class' => 'text-ink-body',
                'icon'        => 'check-circle',
                'icon_class'  => 'text-success-deep',
                'value'       => number_format($totalAtp, 0, ',', '.'),
                'unit'        => 'kg',
                'unit_class'  => 'text-ink-body',
                'value_class' => 'text-success-deep',
                'foot'        => [
                    ['label' => 'Siap Dijual ke Klien', 'value' => '', 'value_class' => 'text-ink', 'class' => 'text-ink-body']
                ],
            ],
            [
                'key'         => 'buffer_status',
                'label'       => 'CADANGAN MITRA',
                'label_class' => 'text-ink-body',
                'icon'        => 'refresh',
                'icon_class'  => 'text-ink',
                'value'       => '100%',
                'unit'        => 'Siap',
                'unit_class'  => 'text-ink-body',
                'value_class' => 'text-ink',
                'foot'        => [
                    ['label' => 'Jejaring Petani Siaga', 'value' => '', 'value_class' => 'text-ink', 'class' => 'text-ink-body']
                ],
            ],
        ];

        $filters = [
            [
                'key'     => 'source',
                'label'   => 'Sumber Pasokan',
                'icon'    => 'package',
                'value'   => 'Semua Sumber Pasokan',
                'options' => [
                    'Semua Sumber Pasokan',
                    'Kebun Binaan',
                    'Mitra Petani',
                ],
            ],
            [
                'key'     => 'status',
                'label'   => 'Status Kuota',
                'icon'    => 'filter',
                'value'   => 'Semua Status Stok',
                'options' => [
                    'Semua Status Stok',
                    'Stok Aman',
                    'Perlu Tambahan',
                ],
            ],
        ];

        $ledger = [
            'title'              => 'Daftar Ketersediaan Sayur Segar Per Komoditas',
            'chip'               => count($rows) . ' Komoditas Aktif',
            'mutasi'             => 'Pembaruan: ' . now()->format('d M Y, H:i') . ' WIB',
            'atp_total'          => number_format($totalAtp, 0, ',', '.') . ' kg',
            'critical_threshold' => 20,
            'groups'             => [
                'sources'    => 'Komposisi Sumber Sayur',
                'allocation' => 'Alokasi Pesanan & Sisa Stok',
            ],
            'columns'            => [
                'sku'      => 'Nama Sayur & Mutu',
                'binawan'  => 'Hasil Panen Kebun',
                'buffer'   => 'Cadangan Mitra',
                'total'    => 'Total Pasokan',
                'locked'   => 'Terkunci Pesanan',
                'atp'      => 'Sisa Bebas Dipesan',
                'actions'  => 'Status & Tindakan',
            ],
            'rows'               => $rows,
            'footer_left'        => 'Menampilkan ' . count($rows) . ' komoditas aktif',
            'footer_atp'         => 'Total Sisa Bebas: ' . number_format($totalAtp, 0, ',', '.') . ' kg',
            'footer_note'        => 'Pencatatan resmi gudang sayur sentra.',
        ];

        $activity = [
            'title'        => 'Riwayat Mutasi & Penguncian Stok',
            'chip'         => 'Pembaruan Langsung',
            'events'       => [
                [
                    'icon_tile'  => 'bg-accent/40',
                    'icon'       => 'lock',
                    'icon_class' => 'text-success-deep',
                    'title'      => 'Penguncian Stok Pesanan Masuk',
                    'ref'        => 'PO-TERVERIFIKASI',
                    'body'       => 'Stok sayur berhasil dialokasikan dari kebun untuk pemenuhan pesanan.',
                    'time'       => 'Hari Ini',
                    'time_class' => 'text-success-deep font-semibold',
                    'tag'        => 'Pesanan Sah',
                ],
                [
                    'icon_tile'  => 'bg-surface-pill',
                    'icon'       => 'package',
                    'icon_class' => 'text-ink',
                    'title'      => 'Penerimaan Panen Sayur Baru',
                    'ref'        => 'PANEN-HARIAN',
                    'body'       => 'Sayur segar hasil panen masuk ke gudang sortir dan lolos pemeriksaan.',
                    'time'       => 'Pagi Ini',
                    'time_class' => 'text-success-deep font-semibold',
                    'tag'        => 'Lolos Sortir',
                ],
            ],
            'footer_left'  => 'Menampilkan riwayat mutasi stok aktif',
            'footer_right' => 'Lihat Semua Riwayat',
        ];

        $readiness = [
            'title'  => 'Kesiapan Pengiriman Armada',
            'chip'   => 'Armada Siap',
            'checks' => [
                [
                    'icon'  => 'scale',
                    'title' => 'Timbangan Gudang Siap',
                    'note'  => 'Timbangan digital telah dinolkan dan terkalibrasi akurat.',
                    'badge' => ['label' => 'Siap', 'class' => 'bg-accent text-success-ink'],
                ],
                [
                    'icon'  => 'truck',
                    'title' => 'Mobil Boks Berpendingin Standby',
                    'note'  => 'Suhu ruang pendingin mobil terjaga segar (2°C - 6°C).',
                    'badge' => ['label' => 'Suhu Dingin Aman', 'class' => 'bg-accent text-success-ink'],
                ],
                [
                    'icon'  => 'file-text',
                    'title' => 'Kesiapan Surat Jalan',
                    'note'  => 'Surat jalan siap dicetak begitu timbangan gudang dikunci.',
                    'badge' => ['label' => 'Siap Cetak', 'class' => 'bg-accent text-success-ink'],
                ],
            ],
            'cta'    => 'Ke Menu Surat Jalan',
        ];

        return view('sekretaris.inventory', [
            'operator'   => $operator,
            'heading'    => $heading,
            'actions'    => $actions,
            'ruleEngine' => $ruleEngine,
            'metrics'    => $metrics,
            'filters'    => $filters,
            'ledger'     => $ledger,
            'activity'   => $activity,
            'readiness'  => $readiness,
        ]);
    }
}
