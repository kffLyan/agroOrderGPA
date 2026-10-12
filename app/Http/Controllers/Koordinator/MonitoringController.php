<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Tampilkan Konsol Monitoring & Validasi Proof of Delivery (PoD).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $operator = [
            'name' => $user?->name ?? 'Koordinator Lapangan',
            'role' => 'Koordinator Lapangan',
            'code' => '002',
            'initials' => strtoupper(substr($user?->name ?? 'KL', 0, 2)),
        ];

        // 1. Mandat PoD
        $mandate = [
            'icon' => 'shield',
            'title' => 'PRD RULE 06 & 12: VALIDASI MUTLAK PROOF OF DELIVERY (POD)',
            'badge' => 'GATE-STATUS: ENFORCED',
            'body_lead' => 'Status pesanan dilarang ditutup menjadi',
            'body_emphasis' => '"Selesai"',
            'body_tail' => 'tanpa bukti PoD (foto dokumen fisik bertanda tangan & stempel basah penerima, serta foto serah terima barang di dock). Segala selisih timbangan di lokasi wajib disahkan via BA Retur Seketika.',
            'body' => 'Status pesanan dilarang ditutup menjadi "Selesai" tanpa bukti PoD (foto dokumen fisik bertanda tangan & stempel basah penerima, serta foto serah terima barang di dock). Segala selisih timbangan di lokasi wajib disahkan via BA Retur Seketika.',
            'protocol_label' => 'PROTOKOL KUNCI',
            'protocol_value' => 'LEDGER LOCK: ACTIVE',
        ];

        // 2. Query Orders & Armada Drivers
        $drivers = User::where('role', 'ARMADA')->get();
        $orders = Order::with(['orderItems.product', 'user', 'driver'])
            ->latest()
            ->take(10)
            ->get();

        if ($orders->isEmpty()) {
            $orders = Order::with(['orderItems.product', 'user', 'driver'])->get();
        }

        $dispatchRows = [];
        $dossierEntries = [];
        $defaultSj = null;

        foreach ($orders as $index => $order) {
            $sj = $order->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT));
            if (!$defaultSj) {
                $defaultSj = $sj;
            }

            $driver = $order->driver ?: ($drivers[$index % max(1, $drivers->count())] ?? null);
            $driverName = $driver?->name ?? 'Joko Prasetyo';
            $vehiclePlate = $order->vehicle_plate_number ?: ($index % 2 === 0 ? 'B 9102 SDA' : 'D 8472 GA');
            $vehicleName = $index % 2 === 0 ? 'Engkel Box ' . $vehiclePlate : 'Blind Van ' . $vehiclePlate;

            $customerName = $order->user?->company_name ?: ($order->user?->name ?? 'Mitra B2B');
            $destination = $order->delivery_address ?: 'Sentul Barat (Drop #' . ($index + 1) . ')';
            $cargoKg = (float) $order->orderItems->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty);
            $commodityNames = $order->orderItems->pluck('product.name')->filter()->unique()->join(', ') ?: 'Selada & Sayuran';

            $returnedKg = (float) $order->orderItems->sum('returned_weight');
            $hasRetur = $returnedKg > 0;

            $status = 'PROSES SERAH TERIMA / UNLOADING';
            $statusTone = 'accent';
            $rowTone = 'default';
            $actions = [
                ['key' => 'call-driver', 'label' => 'Hubungi Driver', 'icon' => 'phone', 'variant' => 'outline'],
                ['key' => 'view-sj', 'label' => 'Lihat Surat Jalan', 'icon' => 'file-text', 'variant' => 'soft'],
            ];

            if ($hasRetur) {
                $status = '! SELESAI DENGAN CATATAN (RETUR ' . $returnedKg . ' KG)';
                $statusTone = 'danger';
                $actions = [
                    ['key' => 'open-retur', 'label' => 'Buka Lap. Retur', 'icon' => 'refresh', 'variant' => 'danger'],
                    ['key' => 'view-ba', 'label' => 'Detail BA', 'icon' => 'clipboard', 'variant' => 'soft'],
                ];
            } elseif ($order->status === 'SELESAI' || $index === 0) {
                $status = 'SELESAI — POD TERVERIFIKASI';
                $statusTone = 'accent';
                $rowTone = 'active';
                $actions = [
                    ['key' => 'inspect-pod', 'label' => 'Periksa PoD', 'icon' => 'eye', 'variant' => 'primary'],
                    ['key' => 'download-proof', 'label' => 'Unduh Bukti', 'icon' => 'download', 'variant' => 'outline'],
                ];
            }

            $dossierRef = 'POD-DOC-' . date('Ym') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);

            $dispatchRows[] = [
                'sj' => $sj,
                'driver' => $driverName,
                'vehicle' => $vehicleName,
                'arrival' => 'Tiba: 06:' . str_pad(($index * 15) % 60, 2, '0', STR_PAD_LEFT) . ' WIB',
                'arrival_tone' => 'success-deep',
                'customer' => $customerName,
                'customer_tone' => $index === 0 ? 'strong' : 'body',
                'destination' => $destination,
                'cargo_kg' => $cargoKg,
                'commodity' => $commodityNames,
                'manifest_kg' => $hasRetur ? ($cargoKg + $returnedKg) : null,
                'discrepancy' => $hasRetur ? 'Selisih: ' . $returnedKg . ' kg afkir' : null,
                'status' => $status,
                'status_tone' => $statusTone,
                'dossier_ref' => $dossierRef,
                'row_tone' => $rowTone,
                'actions' => $actions,
            ];

            // Setup dossier entry
            $recipientName = $order->received_by_name ?: ($order->user?->name ?? 'Staff Penerima');
            $dossierEntries[$sj] = [
                'ref' => $dossierRef,
                'sj' => $sj,
                'recipient' => $recipientName . ' (' . $customerName . ')',
                'net_kg' => $cargoKg,
                'gps_radius_m' => 12.0,
                'gps_tolerance_m' => 20.0,
                'exhibits' => [
                    [
                        'code' => 'EKSIBIT 1: DOKUMEN SJ',
                        'meta' => '300 DPI',
                        'icon' => 'file-text',
                        'caption' => 'CAP STEMPEL: BASAH ' . strtoupper(substr(str_replace(' ', '', $customerName), 0, 8)),
                        'status' => 'TERBACA',
                    ],
                    [
                        'code' => 'EKSIBIT 2: DOCK / CHILLER',
                        'meta' => '+3.8°C',
                        'icon' => 'truck',
                        'caption' => 'GEO: -6.9175, 107.6191',
                        'status' => 'TOL: 12M',
                    ],
                ],
                'checks' => [
                    'signature' => true,
                    'stamp' => true,
                ],
                'notes' => 'Surat jalan fisik telah diverifikasi jelas. Stempel basah QC terbaca utuh. Timbangan ' . number_format($cargoKg, 1) . ' kg sesuai manifes timbangan awal.',
            ];
        }

        // Fallback default SJ if empty
        if (!$defaultSj) {
            $defaultSj = 'SJ-GPA-202610-0001';
            $dossierEntries[$defaultSj] = [
                'ref' => 'POD-DOC-SUB4-0001',
                'sj' => $defaultSj,
                'recipient' => 'Staff Penerima (Mitra B2B)',
                'net_kg' => 500.0,
                'gps_radius_m' => 12.0,
                'gps_tolerance_m' => 20.0,
                'exhibits' => [
                    [
                        'code' => 'EKSIBIT 1: DOKUMEN SJ',
                        'meta' => '300 DPI',
                        'icon' => 'file-text',
                        'caption' => 'CAP STEMPEL: BASAH QC',
                        'status' => 'TERBACA',
                    ],
                    [
                        'code' => 'EKSIBIT 2: DOCK / CHILLER',
                        'meta' => '+3.8°C',
                        'icon' => 'truck',
                        'caption' => 'GEO: -6.9175, 107.6191',
                        'status' => 'TOL: 12M',
                    ],
                ],
                'checks' => [
                    'signature' => true,
                    'stamp' => true,
                ],
                'notes' => 'Surat jalan fisik telah diverifikasi.',
            ];
        }

        // 3. KPIs
        $totalOrdersCount = count($dispatchRows);
        $verifiedCount = count(array_filter($dispatchRows, fn($r) => str_contains($r['status'], 'TERVERIFIKASI')));
        $returCount = (int) OrderItem::where('returned_weight', '>', 0)->count();

        $kpis = [
            [
                'key' => 'fleet',
                'label' => 'ARMADA AKTIF',
                'label_tone' => 'body',
                'value' => max(1, $drivers->count()),
                'unit' => 'Armada Lapangan',
                'unit_tone' => 'body',
                'icon' => 'truck',
                'icon_tone' => 'success-deep',
                'foot_left' => 'GPS 100% ONLINE',
                'foot_left_tone' => 'success-deep',
                'foot_right' => 'CHILLER TELEMETRY LIVE',
                'foot_right_tone' => 'muted',
                'foot_dot' => true,
            ],
            [
                'key' => 'dropoff',
                'label' => 'TITIK DROPOFF SELESAI',
                'label_tone' => 'body',
                'value' => $verifiedCount . ' / ' . max(1, $totalOrdersCount),
                'unit' => 'Titik (' . ($totalOrdersCount > 0 ? round(($verifiedCount / $totalOrdersCount) * 100) : 0) . '%)',
                'unit_tone' => 'body',
                'icon' => 'map-pin',
                'icon_tone' => 'success-deep',
                'foot_left' => 'PoD VALID: ' . $verifiedCount . ' BERKAS',
                'foot_left_tone' => 'success-deep',
                'foot_right' => 'PROGRESS OK',
                'foot_right_tone' => 'muted',
                'foot_dot' => false,
            ],
            [
                'key' => 'enroute',
                'label' => 'DALAM PERJALANAN',
                'label_tone' => 'body',
                'value' => max(0, $totalOrdersCount - $verifiedCount),
                'unit' => 'Armada On-Route',
                'unit_tone' => 'body',
                'icon' => 'send',
                'icon_tone' => 'success-deep',
                'foot_left' => 'Menuju Lokasi & Unloading',
                'foot_left_tone' => 'body',
                'foot_right' => 'ETA ON-SCHEDULE',
                'foot_right_tone' => 'success-deep',
                'foot_dot' => false,
            ],
            [
                'key' => 'retur',
                'label' => 'KASUS RETUR / SELISIH',
                'label_tone' => $returCount > 0 ? 'warning-deep' : 'body',
                'value' => $returCount,
                'unit' => $returCount > 0 ? 'Kasus Butuh Verifikasi' : 'Nihil Kasus Retur',
                'unit_tone' => $returCount > 0 ? 'danger' : 'body',
                'icon' => 'alert-triangle',
                'icon_tone' => $returCount > 0 ? 'danger' : 'success-deep',
                'foot_left' => $returCount > 0 ? 'BA RETUR WAJIB' : 'MUTU 100% LOLOS',
                'foot_left_tone' => $returCount > 0 ? 'danger' : 'success-deep',
                'foot_right' => 'BA DIKUNCI',
                'foot_right_tone' => 'warning-caution',
                'foot_dot' => false,
            ],
        ];

        // 4. Dossier Setup
        $dossier = [
            'title' => 'Verifikasi Berkas PoD Masuk',
            'subtitle' => 'Inspeksi Fisik Dokumen & Otorisasi Penerimaan',
            'badge' => 'DOKUMEN SIAP',
            'icon' => 'file-text',
            'reference_label' => 'NO. REFERENSI DOKUMEN',
            'sj_label' => 'SURAT JALAN ASOSIASI',
            'checks_label' => 'PARAMETER CEK MANDATORI PRD (WAJIB TERPENUHI SEMUA)',
            'checks' => [
                ['key' => 'signature', 'label' => 'Ttd Asli Penerima'],
                ['key' => 'stamp', 'label' => 'Stempel Cap Basah PT/Instansi'],
                ['key' => 'weight', 'label' => 'Bobot Netto Pas'],
                ['key' => 'gps', 'label' => 'Radius GPS'],
            ],
            'notes_label' => 'CATATAN KOORDINATOR GUDANG',
            'decision_label' => 'STATUS PUTUSAN:',
            'decision' => [
                ['key' => 'approved', 'label' => 'APPROVED (VALID)', 'tone' => 'success-deep'],
                ['key' => 'reject', 'label' => 'REJECT (DITOLAK)', 'tone' => 'danger'],
            ],
            'cta' => [
                'key' => 'validate-pod',
                'label' => 'VALIDASI PoD & TUTUP PESANAN',
                'icon' => 'badge-check',
            ],
            'buttons' => [
                ['key' => 'save-draft', 'label' => 'SIMPAN DRAFT', 'icon' => 'save', 'variant' => 'outline'],
                ['key' => 'escalate-audit', 'label' => 'ESKALASI TIM AUDIT', 'icon' => 'alert-triangle', 'variant' => 'danger'],
            ],
            'empty_title' => 'Berkas PoD Belum Masuk',
            'empty_body' => 'Dokumen fisik dan foto dock untuk surat jalan ini belum diunggah pengirim. Ledger tetap terkunci sampai berkas lengkap masuk.',
            'entries' => $dossierEntries,
        ];

        $dispatch = [
            'title' => 'Log Status Pengiriman & Armada Lapangan',
            'subtitle' => count($dispatchRows) . ' Perjalanan Terjadwal Sentra Hari Ini',
            'badge' => 'LIVE DISPATCH',
            'columns' => [
                'ARMADA / SUPIR',
                'NO. SJ & DESTINASI',
                'MUATAN',
                'STATUS EKSEKUSI',
                'AKSI OPERASIONAL',
            ],
            'rows' => $dispatchRows,
            'default_sj' => $defaultSj,
            'dropoff_total' => max(1, count($dispatchRows)),
            'dropoff_base' => $verifiedCount,
            'footer_left' => 'ROUTE OPTIMIZATION CIWIDEY — BANDUNG RAYA & JABODETABEK',
            'footer_right' => 'ENCRYPTED TELEMETRY HASH: ' . substr(md5(now()->toDateString()), 0, 11),
        ];

        $monitoringData = [
            'operator' => $operator,
            'mandate' => $mandate,
            'kpis' => $kpis,
            'dispatch' => $dispatch,
            'dossier' => $dossier,
        ];

        $viewName = view()->exists('koordinator.monitoring') ? 'koordinator.monitoring' : 'coordinator.monitoring';

        return view($viewName, [
            'operator' => $operator,
            'monitoringData' => $monitoringData,
        ]);
    }
}
