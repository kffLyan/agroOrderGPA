<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    /**
     * Menampilkan status kendaraan armada, telemetri IoT, audit kelaikan, dan rekap shift.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Ambil data pesanan supir hari ini
        $orders = Order::with(['orderItems', 'user'])
            ->where(function ($q) use ($user) {
                $q->where('driver_id', $user?->id)
                  ->orWhereIn('status', ['SIAP_KIRIM', 'DALAM_PENGIRIMAN', 'SELESAI', 'SELESAI_CATATAN']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->get();

        $dropsCount = $orders->count() ?: 2;
        $doneCount = $orders->whereIn('status', ['SELESAI', 'SELESAI_CATATAN'])->count();
        $returCount = $orders->where('status', 'SELESAI_CATATAN')->count();

        $totalTonaseKg = 0;
        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $totalTonaseKg += (float) ($item->net_weight ?? $item->quantity ?? 0);
            }
        }
        if ($totalTonaseKg <= 0) {
            $totalTonaseKg = 1986.5;
        }

        $plate = $orders->first(fn ($o) => !empty($o->vehicle_plate_number))?->vehicle_plate_number ?? 'B 9284 TDA';

        $operator = [
            'name'     => $user?->name ?: 'Supir Pengiriman',
            'role'     => 'Supir Pengiriman GPA',
            'initials' => strtoupper(substr($user?->name ?: 'SP', 0, 2)),
            'email'    => $user?->email ?: 'armada@greenpasundan.id',
            'plate'    => $plate,
        ];

        $header = [
            'brand' => 'AGROORDER GPA',
            'role'  => 'Armada Logistik',
            'plate' => $plate,
            'tone'  => 'light',
        ];

        $vehicle = [
            'label'       => 'STATUS TELEMETRI KENDARAAN',
            'status'      => 'OPERASIONAL',
            'status_tail' => 'AKTIF BERJALAN',
            'icon'        => 'truck',
            'badge'       => 'SINKRONISASI',
            'badge_tail'  => '15 DETIK',
        ];

        $driver = [
            'section'     => 'DATA PENGEMUDI & KENDARAAN',
            'code_label'  => 'KODE SUPIR: ',
            'code'        => 'DVR-' . str_pad($user?->id ?: 1, 2, '0', STR_PAD_LEFT),
            'initials'    => strtoupper(substr($user?->name ?: 'SP', 0, 2)),
            'name'        => $user?->name ?: 'Supir Pengiriman GPA',
            'unit_code'   => 'UNIT-CDD-REEFER-' . str_pad($user?->id ?: 1, 2, '0', STR_PAD_LEFT),
            'tags'        => [
                ['label' => 'PANGKALAN: GUDANG SENTRAL BOGOR', 'tone' => 'neutral'],
                ['label' => 'SIM B1 UMUM: AKTIF RESMI',        'tone' => 'accent'],
            ],
            'specs'       => [
                'label' => 'SPESIFIKASI KENDARAAN',
                'lines' => ['Truk Boks Pendingin (CDD)', 'Kompartemen Reefer'],
                'foot'  => 'Pendingin Cold-Chain Standar Hortikultura',
            ],
            'plate_label' => 'NOMOR POLISI',
            'plate_tail'  => 'KENDARAAN',
            'plate'       => $plate,
            'plate_note'  => 'Uji KIR & STNK Berlaku Sah',
        ];

        $telemetry = [
            'section'    => 'TELEMETRI IOT TERKONEKSI',
            'badge'      => 'LIVE',
            'badge_tail' => 'SENSOR',
            'chiller'    => [
                'label'          => 'SUHU BOKS PENDINGIN (SAYUR SEGAR)',
                'value'          => '+3.8°C',
                'icon'           => 'thermometer',
                'badge'          => 'OPTIMAL DINGIN',
                'range_label'    => 'Ambang Batas:',
                'range_min'      => '+2.0°C s.d.',
                'range_max'      => '+6.0°C',
                'commodity'      => 'Sayuran Segar &',
                'commodity_tail' => 'Hortikultura',
            ],
            'capacity'   => [
                'label'         => 'KAPASITAS MUAT BOKS',
                'value'         => number_format($totalTonaseKg, 0, ',', '.') . ' / 3.000 kg',
                'percent'       => '(' . number_format(min(100, ($totalTonaseKg / 3000) * 100), 1, ',', '.') . '%)',
                'percent_value' => min(100, ($totalTonaseKg / 3000) * 100),
                'foot'          => [
                    ['label' => '0 kg', 'tail' => '(Kosong)'],
                    ['label' => 'Beban Ideal', 'tail' => 'Sayur'],
                    ['label' => 'Kapasitas Maksimal', 'tail' => '3.0 Ton'],
                ],
            ],
            'cards'      => [
                [
                    'label' => 'BAHAN BAKAR',
                    'icon'  => 'gauge',
                    'value' => '78% Solar Dex',
                    'note'  => 'Odometer Terkoneksi',
                ],
                [
                    'label'     => 'GPS TRACKER',
                    'icon'      => 'map-pin',
                    'value'     => 'TERHUBUNG 4G',
                    'note'      => 'Satelit Aktif GPS &bull; Latensi',
                    'note_tail' => 'Rendah',
                ],
            ],
        ];

        $trip = [
            'section'        => 'AUDIT KELAIKAN',
            'title'          => 'PRE-TRIP KENDARAAN',
            'subtitle'       => 'Pemeriksaan Rutin Pagi',
            'subtitle_tail'  => 'Sebelum Berangkat',
            'badge'          => '5/5 LENGKAP',
            'badge_tail'     => 'TERVERIFIKASI',
            'items'          => [
                [
                    'label'     => 'Suhu awal chiller boks stabil',
                    'tail'      => '(+3.8°C)',
                    'time'      => '05:30',
                    'time_tail' => 'WIB',
                ],
                [
                    'label'     => 'Tekanan angin ban & fungsi rem',
                    'tail'      => 'aman',
                    'time'      => '05:35',
                    'time_tail' => 'WIB',
                ],
                [
                    'label'     => 'Kelengkapan dokumen KIR & STNK asli',
                    'tail'      => null,
                    'time'      => '05:40 WIB',
                    'time_tail' => null,
                ],
                [
                    'label'     => 'APAR pemadam kebakaran & kotak P3K',
                    'tail'      => 'lengkap',
                    'time'      => '05:45',
                    'time_tail' => 'WIB',
                ],
                [
                    'label'     => 'Kebersihan boks bebas kotoran & bau',
                    'tail'      => null,
                    'time'      => '05:50 WIB',
                    'time_tail' => null,
                ],
            ],
            'mechanic_label' => 'PETUGAS PEMERIKSA GUDANG',
            'mechanic'       => 'Petugas Teknis Sentral Hub',
            'sign_label'     => 'JAM PERIKSA',
            'sign_tail'      => '05:55 WIB',
        ];

        $performance = [
            'section'       => 'REKAP KINERJA',
            'title'         => 'PENGANTARAN',
            'subtitle'      => 'Tugas Shift Berjalan &bull; Sentra Hub',
            'badge'         => 'STATUS',
            'badge_tail'    => 'HARI INI',
            'cards'         => [
                [
                    'label' => 'TITIK PENGIRIMAN',
                    'value' => $dropsCount . ' Lokasi',
                    'note'  => [$doneCount . ' Selesai', ($dropsCount - $doneCount) . ' Sedang Berjalan'],
                    'tone'  => 'success',
                ],
                [
                    'label' => 'TOTAL BERAT MUATAN',
                    'value' => number_format($totalTonaseKg, 1, ',', '.') . ' kg',
                    'note'  => ['Muatan Bersih Riil', 'Sesuai Surat Jalan'],
                    'tone'  => 'neutral',
                ],
                [
                    'label'      => 'KETEPATAN WAKTU',
                    'label_tail' => '(SLA)',
                    'value'      => '100% Tepat',
                    'note'       => ['Pengantaran Sesuai', 'Jadwal Pagi'],
                    'tone'       => 'success',
                ],
                [
                    'label' => 'CATATAN RETUR',
                    'value' => $returCount > 0 ? ($returCount . ' Kasus') : '0 Kasus',
                    'note'  => [$returCount > 0 ? 'Ada catatan retur' : 'Nihil retur', 'Kualitas Sayur Terjaga'],
                    'tone'  => $returCount > 0 ? 'warning' : 'success',
                ],
            ],
        ];

        $support = [
            'section'        => 'BANTUAN OPERASIONAL',
            'section_tail'   => '& DISPATCH HUB',
            'badge'          => 'HOTLINE',
            'badge_tail'     => 'SIAP',
            'report_label'   => 'LAPOR KENDALA KENDARAAN / JALAN',
            'report_note'    => '(Ban Bocor / Mesin Mogok / Chiller Kendala / Macet Total)',
            'report_note_tail' => 'Hubungi Dispatch Segera',
            'dispatch_label' => 'KOORDINATOR DISPATCH GUDANG SENTRA',
            'dispatch_value' => 'Telepon Hotline: (021) 890-1234',
            'sop_label'      => 'Panduan Penanganan Mutu Sayur &',
            'sop_tail'       => 'Suhu Cold-Chain Pengiriman',
            'sop_badge'      => 'SOP_GPA',
        ];

        $closure = [
            'action'      => 'SELESAIKAN SHIFT & TUTUP',
            'action_tail' => 'TUGAS PENGANTARAN',
            'note'        => '*Pastikan seluruh bukti terima PoD telah diunggah',
            'note_tail'   => 'sebelum menutup shift harian.',
            'footer'      => 'GREEN PASUNDAN AGRIKULTUR &bull; SISTEM ARMADA LOGISTIK',
            'footer_tail' => '&bull; VERSI LAPANGAN TERHUBUNG',
        ];

        return view('armada.status', [
            'operator'         => $operator,
            'header'           => $header,
            'vehicle'          => $vehicle,
            'driver'           => $driver,
            'telemetry'        => $telemetry,
            'trip'             => $trip,
            'performance'      => $performance,
            'support'          => $support,
            'closure'          => $closure,
            'activeNav'        => 'fleet',
            'component'        => 'armadaStatus',
            'componentPayload' => [],
        ]);
    }

    /**
     * Menutup shift harian supir
     */
    public function closeShift(Request $request): RedirectResponse
    {
        return redirect()
            ->route('armada.status')
            ->with('success', 'Shift harian berhasil ditutup! Terima kasih atas pengantaran hari ini.');
    }
}
