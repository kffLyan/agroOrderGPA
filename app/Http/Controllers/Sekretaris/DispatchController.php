<?php

namespace App\Http\Controllers\Sekretaris;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatchController extends Controller
{
    /**
     * Menampilkan konsol Surat Jalan & Penugasan Armada Logistik
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
            'eyebrow'          => 'Surat Jalan & Pengiriman',
            'title_before'     => 'Penerbitan Surat Jalan Resmi &',
            'title_after'      => 'Penugasan Supir Pengantar',
            'subtitle_before'  => 'Pencetakan surat jalan resmi',
            'subtitle_format'  => '',
            'subtitle_after'   => 'berdasarkan hasil timbangan bersih nyata dari gudang.',
            'badge'            => 'Timbangan Gudang Terhubung & Siap',
        ];

        // Ambil pesanan dari database
        $drivers = User::where('role', 'ARMADA')->get();
        $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])
            ->whereIn('status', ['TERVERIFIKASI', 'SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI'])
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver', 'verifier'])->latest()->take(5)->get();
        }

        $readyOrders = $orders->where('status', 'SIAP_KIRIM');
        $shippingOrders = $orders->where('status', 'DALAM_PENGIRIMAN');
        $doneOrders = $orders->where('status', 'SELESAI');

        $metrics = [
            [
                'key'        => 'ready',
                'label'      => 'Surat Jalan Siap Terbit',
                'value'      => count($readyOrders) . ' Dokumen',
                'note'       => 'Berat bersih sayur telah diverifikasi koordinator gudang',
                'icon'       => 'file-text',
                'icon_tile'  => 'bg-accent/30',
                'icon_class' => 'text-success-deep',
                'foot_label' => 'Timbangan Valid',
                'foot_class' => 'text-success-deep',
                'foot_dot'   => 'bg-success-deep',
            ],
            [
                'key'        => 'transit',
                'label'      => 'Dalam Perjalanan Supir',
                'value'      => count($shippingOrders) . ' Mobil',
                'note'       => 'Sayur dalam perjalanan menuju lokasi pelanggan',
                'icon'       => 'truck',
                'icon_tile'  => 'bg-surface-track',
                'icon_class' => 'text-ink',
                'foot_label' => 'Sedang Jalan',
                'foot_class' => 'text-ink-quiet',
                'foot_dot'   => 'bg-ink-quiet',
            ],
            [
                'key'         => 'pod',
                'label'       => 'Menunggu Bukti Terima',
                'value'       => (string) count($shippingOrders),
                'value_after' => 'Pengiriman',
                'note'        => 'Menunggu foto bukti terima dan tanda tangan klien',
                'icon'        => 'package',
                'icon_tile'   => 'bg-warning-soft/30',
                'icon_class'  => 'text-warning-deep',
                'foot_label'  => 'Verifikasi Penerimaan',
                'foot_class'  => 'text-warning',
                'foot_dot'    => 'bg-warning',
            ],
            [
                'key'        => 'done',
                'label'      => 'Selesai Terkirim',
                'value'      => count($doneOrders) . ' Pesanan',
                'note'       => 'Surat jalan selesai dan siap masuk ke tagihan faktur',
                'icon'       => 'check-circle',
                'icon_tile'  => 'bg-surface-pill',
                'icon_class' => 'text-ink',
                'foot_label' => 'Transaksi Tuntas',
                'foot_class' => 'text-ink-quiet',
                'foot_dot'   => 'bg-ink-quiet',
            ],
        ];

        $rule05 = [
            'chip'           => 'Aturan Pengiriman',
            'title'          => 'Aturan Wajib Pembuatan Surat Jalan',
            'body_before'    => 'Tombol cetak Surat Jalan hanya aktif bila berat sayur telah berstatus',
            'gate'           => 'Selesai Ditimbang Gudang',
            'body_after'     => '. Hal ini untuk memastikan berat sayur yang tercantum di surat jalan tepat sesuai fisik yang dikirimkan.',
            'protocol_label' => 'Status Dokumen',
            'protocol_value' => 'Dokumen Resmi Sah',
            'protocol_note'  => 'Tercatat di Sistem',
        ];

        $rows = [];
        foreach ($orders->values() as $index => $order) {
            $clientName = $order->user?->company_name ?: ($order->user?->name ?: 'Mitra Klien GPA');
            $address = $order->delivery_address ?: 'Gudang Utama Klien';
            $estimateWeight = (float) $order->orderItems->sum('ordered_qty');
            $nettoWeight = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            if ($estimateWeight <= 0) $estimateWeight = 500.0;
            if ($nettoWeight <= 0) $nettoWeight = $estimateWeight;

            $diffKg = round($nettoWeight - $estimateWeight, 2);
            $diffPct = round(($diffKg / $estimateWeight) * 100, 2);
            $deviationStr = ($diffKg >= 0 ? '+' : '') . number_format($diffKg, 1, ',', '.') . " kg ({$diffPct}%)";

            $driver = $order->driver ?: ($drivers[$index % max(1, $drivers->count())] ?? null);
            $driverName = $driver?->name ?: 'Joko Prasetyo';
            $plateNumber = $order->vehicle_plate_number ?: ($index % 2 === 0 ? 'B 9421 TX' : 'D 8812 GA');
            $armadaName = $index % 2 === 0 ? 'Isuzu Elf Boks Pendingin' : 'Blind Van Chiller';

            $sjNumber = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            $isReady = $order->status === 'SIAP_KIRIM';

            $itemLines = $order->orderItems->map(function ($it, $lineIdx) {
                $est = (float) $it->ordered_qty;
                $act = (float) ($it->actual_net_weight ?: $it->ordered_qty);
                $diff = round($act - $est, 2);
                return [
                    'no'        => $lineIdx + 1,
                    'commodity' => $it->product?->name ?: 'Sayur Segar Grade A',
                    'lot'       => 'Wadah Keranjang GPA',
                    'estimate'  => number_format($est, 1, ',', '.') . ' kg',
                    'netto'     => number_format($act, 1, ',', '.') . ' kg',
                    'delta'     => ($diff >= 0 ? '+' : '') . number_format($diff, 1, ',', '.') . ' kg',
                ];
            })->all();

            if (empty($itemLines)) {
                $itemLines = [
                    [
                        'no'        => 1,
                        'commodity' => 'Selada Romaine Super',
                        'lot'       => 'Wadah Keranjang GPA',
                        'estimate'  => number_format($estimateWeight, 1, ',', '.') . ' kg',
                        'netto'     => number_format($nettoWeight, 1, ',', '.') . ' kg',
                        'delta'     => $deviationStr,
                    ]
                ];
            }

            $rows[] = [
                'id'          => $order->order_number,
                'client'      => $clientName,
                'warehouse'   => 'Gudang Pusat Sentra',
                'dock'        => $address,
                'state'       => $isReady ? 'ready' : 'locked',
                'estimate'    => number_format($estimateWeight, 1, ',', '.') . ' kg',
                'netto'       => number_format($nettoWeight, 1, ',', '.') . ' kg',
                'deviation'   => $deviationStr,
                'revision'    => null,
                'status_chip' => [
                    'label' => $isReady ? 'Timbangan Sah Siap Terbit' : 'Dalam Proses Timbang',
                    'class' => $isReady ? 'bg-accent text-success-ink outline outline-1 outline-success-deep' : 'bg-surface-pill text-ink-body',
                ],
                'tera'        => 'Petugas: ' . ($order->verifier?->name ?: 'Koordinator Gudang'),
                'armada'      => $armadaName,
                'plate'       => $plateNumber,
                'driver'      => $driverName,
                'sj'          => $sjNumber,
                'issued_at'   => ($order->updated_at ?: now())->format('d F Y (H:i') . ' WIB)',
                'handover_at' => 'Gudang Pusat Sentra // Siap Muat',
                'auth_key'    => 'GPA-VERIF-' . substr(hash('sha256', $sjNumber), 0, 8),
                'document'    => [
                    'client'       => $clientName,
                    'drop_point'   => $address,
                    'address'      => $address,
                    'pic'          => 'PIC Klien: ' . ($order->user?->name ?: 'Bagian Penerimaan') . ' (' . ($order->user?->phone ?: '0812-3456-7890') . ')',
                    'armada'       => $armadaName,
                    'plate'        => $plateNumber,
                    'driver'       => $driverName,
                    'sim'          => 'SIM B1: 8810293102',
                    'chiller'      => 'Suhu Ruang Mobil: 4°C - 6°C (Dingin & Segar)',
                    'officer'      => $order->verifier?->name ?: 'Koordinator Lapangan',
                    'officer_role' => 'Koordinator Penimbangan Gudang',
                    'officer_note' => 'Sayur telah disortir dan ditimbang sesuai standar mutu.',
                    'estimate'     => number_format($estimateWeight, 1, ',', '.') . ' kg',
                    'netto'        => number_format($nettoWeight, 1, ',', '.') . ' kg',
                    'delta'        => $deviationStr,
                    'lines'        => $itemLines,
                ],
            ];
        }

        $queue = [
            'title'      => 'Antrean Penerbitan Dokumen Surat Jalan',
            'subtitle'   => 'Daftar pesanan yang siap dicetak surat jalannya berdasarkan hasil timbangan nyata.',
            'chip'       => 'Total: ' . count($rows) . ' Pesanan',
            'columns'    => [
                'identity' => 'Pesanan & Klien',
                'estimate' => 'Estimasi Pesanan',
                'netto'    => 'Berat Bersih Nyata (Netto)',
                'status'   => 'Status Timbangan',
                'fleet'    => 'Armada & Supir',
                'action'   => 'Cetak Surat Jalan',
            ],
            'warehouses' => ['Semua Gudang', 'Gudang Sentra Cibitung', 'Gudang Lembang'],
            'rows'       => $rows,
        ];

        $letterhead = [
            'company'      => 'GREEN PASUNDAN AGRIKULTUR',
            'address'      => 'Distribusi Sayuran Segar & Hortikultura Berkualitas',
            'address_line' => 'Jl. Sentra Agribisnis No. 12, Jawa Barat',
            'contact'      => 'Telp: (021) 890-1234 | Email: operasional@agroorder.co.id',
            'doc_label'    => 'SURAT JALAN & PENGANTAR SAYUR',
            'columns'      => [
                'no'        => 'No',
                'commodity' => 'Nama Sayur Segar',
                'lot'       => 'Kemasan Wadah',
                'estimate'  => 'Estimasi Pesanan',
                'netto'     => 'Berat Bersih (Netto)',
                'delta'     => 'Selisih Berat',
            ],
            'lines_label'  => 'Rincian Sayur Bersih Hasil Timbangan Gudang:',
            'total_label'  => 'Total Berat Bersih:',
            'legal_label'  => 'Catatan Penerimaan:',
            'legal_note'   => 'Barang diterima dalam kondisi baik dan segar sesuai rincian timbangan di atas.',
            'stamp_top'    => 'Disahkan Oleh Gudang',
            'stamp_mid'    => 'Timbangan Bersih Sah',
            'stamp_low'    => 'Tercatat Resmi di Sistem',
        ];

        $security = [
            'preview_title' => 'Pratinjau Surat Jalan Resmi',
            'standard_chip' => 'Format Standar Resmi',
            'print_label'   => 'Cetak Surat Jalan',
            'secure_mark'   => '*SJ-RESMI-GPA-TERCATAT*',
            'auth_prefix'   => 'Kode Pengesahan:',
            'page'          => 'Halaman 1 dari 1 — Dokumen Resmi Pengiriman',
            'copies'        => 'Lembar 1: Pembeli / Lembar 2: Supir Pengantar / Lembar 3: Arsip Penagihan',
        ];

        $signatures = [
            [
                'label' => 'Penerima Barang',
                'role'  => '',
                'stamp' => '(Tanda Tangan & Cap Penerima)',
                'name'  => '(............................................)',
                'note'  => 'Nama Jelas & Cap Perusahaan',
            ],
            [
                'label' => 'Supir Pengantar',
                'role'  => 'Supir Pengiriman',
                'stamp' => '(Tanda tangan serah terima barang)',
                'name'  => 'Joko Prasetyo',
                'note'  => 'Mobil Boks Pendingin GPA',
            ],
            [
                'label' => 'Petugas Gudang',
                'role'  => 'Green Pasundan Agrikultur',
                'stamp' => '[Terverifikasi Sistem]',
                'name'  => $user?->name ?: 'Sekretaris Operasional',
                'note'  => '',
            ],
        ];

        $release = [
            'title'       => 'Terbitkan Surat Jalan Resmi Ini?',
            'body_before' => 'Tindakan ini akan mengesahkan surat jalan resmi berdasarkan berat timbangan riil',
            'body_after'  => 'dan mengalihkan status pesanan ke tahap Pengiriman oleh supir.',
            'draft_label' => 'Simpan Draf',
            'issue_label' => 'Konfirmasi & Terbitkan Surat Jalan',
        ];

        return view('sekretaris.dispatch', [
            'operator'   => $operator,
            'heading'    => $heading,
            'metrics'    => $metrics,
            'rule05'     => $rule05,
            'queue'      => $queue,
            'letterhead' => $letterhead,
            'security'   => $security,
            'signatures' => $signatures,
            'release'    => $release,
        ]);
    }
}
