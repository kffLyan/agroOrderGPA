<?php

namespace App\Http\Controllers\Armada;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PodController extends Controller
{
    /**
     * Menampilkan formulir dan konsol bukti terima digital (Proof of Delivery / PoD).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cari pesanan yang menjadi target PoD
        $targetOrderId = $request->query('order_id');
        $query = Order::with(['orderItems.product', 'user', 'driver']);

        if ($targetOrderId) {
            $currentOrder = $query->find($targetOrderId);
        } else {
            $currentOrder = $query->where(function ($q) use ($user) {
                $q->where('driver_id', $user?->id)
                  ->orWhereIn('status', ['DALAM_PENGIRIMAN', 'SIAP_KIRIM']);
            })
            ->whereNotIn('status', ['DRAFT', 'BATAL'])
            ->latest()
            ->first();
        }

        if (!$currentOrder) {
            $currentOrder = Order::with(['orderItems.product', 'user', 'driver'])->latest()->first();
        }

        $totalNetto = 0;
        if ($currentOrder) {
            foreach ($currentOrder->orderItems as $item) {
                $totalNetto += (float) ($item->net_weight ?? $item->quantity ?? 0);
            }
        }

        $plate = $currentOrder?->vehicle_plate_number ?: 'B 9284 TDA';

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

        $sjNum = $currentOrder?->surat_jalan_number
            ?: ('SJ-' . date('Ymd', strtotime($currentOrder?->created_at ?: now())) . '-' . str_pad($currentOrder?->id ?: 1, 4, '0', STR_PAD_LEFT));

        $target = [
            'order_id'       => $currentOrder?->id,
            'sj'             => $sjNum,
            'status'         => match ($currentOrder?->status) {
                'SELESAI', 'SELESAI_CATATAN' => 'DOKUMEN SELESAI',
                'DALAM_PENGIRIMAN'           => 'DOKUMEN AKTIF BERJALAN',
                default                      => 'SIAP PENYERAHAN',
            },
            'client_label'   => 'KLIEN PENERIMA',
            'client'         => $currentOrder?->user?->name ?: 'Klien Penerima Resmi',
            'client_legal'   => 'Green Pasundan Agrikultur B2B',
            'dock_label'     => 'TITIK BONGKAR MUAT',
            'dock'           => $currentOrder?->delivery_address ?: 'Gudang Penerima Klien',
            'dock_area'      => 'Area Penerimaan Barang',
            'weight_label'   => 'TOTAL MUATAN RESMI',
            'weight'         => number_format($totalNetto, 1, ',', '.'),
            'weight_unit'    => 'KG Bersih',
            'commodity'      => 'Sayuran Segar &',
            'commodity_tail' => 'Hortikultura Berkualitas',
            'pic_label'      => 'PIC PENERIMA RESMI',
            'pic'            => $currentOrder?->received_by_name ?: ($currentOrder?->user?->name ?: 'Bapak Hendra Kurniawan'),
            'pic_role'       => 'Penerima Barang / Kepala Gudang',
        ];

        $steps = [
            [
                'key'               => 'manifest',
                'step'              => '1',
                'title'             => 'Foto Fisik Surat Jalan',
                'title_tail'        => '',
                'badge'             => 'WAJIB CAP STEMPEL',
                'badge_tone'        => 'accent',
                'viewfinder_label'  => 'KAMERA RATIO 3:4 FOKUS JELAS',
                'status'            => 'SIAP DIFOTO',
                'subject'           => 'SURAT JALAN RESMI',
                'meta'              => [
                    ['text' => 'RESOLUSI STANDAR DOKUMEN', 'tone' => 'soft'],
                    ['text' => 'KONTRAS TINGGI', 'tone' => 'accent'],
                ],
                'placeholder_icon'  => 'file-text',
                'placeholder_title' => 'Posisikan Lembar Surat Jalan GPA',
                'placeholder_hint'  => 'Pastikan cap stempel & paraf tanda tangan penerima tampak jelas',
                'action'            => 'Ambil Foto Surat Jalan',
                'action_icon'       => 'camera',
                'checklist_title'   => 'VERIFIKASI SEBELUM SIMPAN:',
                'checklist'         => [
                    'Cap stempel basah penerima terbaca jelas',
                    'Tanda tangan penanggung jawab penerima ada',
                    'Nama terang tertulis jelas sesuai identitas',
                ],
            ],
            [
                'key'               => 'dock',
                'step'              => '2',
                'title'             => 'Foto Serah Terima & Muatan',
                'title_tail'        => '',
                'badge'             => 'TITIK LOKASI DOCK',
                'badge_tone'        => 'warning',
                'telemetry'         => [
                    ['label' => 'GPS: Terekam Otomatis', 'tone' => 'accent'],
                    ['label' => 'STATUS: LOKASI OK', 'tone' => 'solid'],
                ],
                'telemetry_meta'    => [
                    ['label' => 'WAKTU SERAH TERIMA:', 'label_tail' => date('d M Y, H:i \W\I\B')],
                    ['label' => 'SUHU CHILLER KENDARAAN:', 'label_tail' => '+3.2°C (Optimal)'],
                ],
                'viewfinder_label'  => 'KAMERA DOKUMEN',
                'dock_chip'         => 'AREA BONGKAR MUAT',
                'status'            => 'KAMERA SIAP MENANGKAP',
                'status_tail'       => 'PENCAHAYAAN CUKUP',
                'status_tail_note'  => '(TERANG)',
                'placeholder_icon'  => 'package',
                'placeholder_title' => 'Sorot Krat/Peti Sayur Bersama Penerima',
                'placeholder_hint'  => 'Muatan terlihat utuh berdampingan dengan perwakilan penerima',
                'action'            => 'Ambil Foto Muatan di Lokasi',
                'action_icon'       => 'camera',
            ],
            [
                'key'               => 'signature',
                'step'              => '3',
                'title'             => 'Tanda Tangan Digital',
                'title_tail'        => 'Penerima Barang',
                'badge'             => 'SENTUH LAYAR',
                'badge_tone'        => 'accent',
                'canvas_label'      => 'KANVAS KONFIRMASI PENERIMA',
                'canvas_clear'      => 'Hapus / Ulang',
                'identity_label'    => 'NAMA TERANG PENERIMA RESMI:',
                'identity_name'     => $currentOrder?->received_by_name ?: ($currentOrder?->user?->name ?: 'Hendra Kurniawan'),
                'identity_role'     => 'Penanggung Jawab Penerimaan Barang di Lokasi',
            ],
        ];

        $conditions = [
            [
                'key'         => 'clear',
                'tone'        => 'accent',
                'badge'       => 'UTUH SEMPURNA',
                'badge_tone'  => 'success',
                'title'       => 'Barang Diterima Utuh Tanpa Retur (' . number_format($totalNetto, 1, ',', '.') . ' KG)',
                'title_tone'  => 'ink',
                'description' => 'Suhu boks stabil, segel aman, timbangan bersih cocok sesuai surat jalan.',
            ],
            [
                'key'         => 'discrepancy',
                'tone'        => 'neutral',
                'badge'       => 'CATATAN KHUSUS',
                'badge_tone'  => 'warning',
                'title'       => 'Ada Selisih Berat / Catatan Retur Lapangan',
                'title_tone'  => 'warning',
                'description' => 'Penerima mencatat selisih timbangan atau ada bagian sayur yang rusak/dikembalikan.',
            ],
        ];

        $closure = [
            'label'          => 'Ada kendala serah terima muatan?',
            'hub_label'      => 'Hubungi Koordinator Sentra',
            'hub_phone'      => '(021) 890-1234',
            'action'         => 'VALIDASI & SELESAIKAN PENGIRIMAN',
            'action_tail'    => '(KUNCI BUKTI TERIMA)',
            'note'           => 'Otomatis mencatat status pesanan Selesai dan menerbitkan faktur penagihan resmi.',
            'arrival_action' => 'SERAH TERIMA & SELESAIKAN',
            'arrival_note'   => 'Muatan diserahkan ke pihak penerima, PoD sah, status: Selesai.',
        ];

        return view('armada.pod', [
            'operator'         => $operator,
            'header'           => $header,
            'target'           => $target,
            'steps'            => $steps,
            'condition_title'  => 'Status Kondisi Muatan & Retur',
            'conditions'       => $conditions,
            'closure'          => $closure,
            'activeNav'        => 'pod',
            'component'        => 'armadaPod',
            'componentPayload' => $steps,
        ]);
    }

    /**
     * Menyimpan data bukti serah terima (PoD) dan menyelesaikan pengiriman pesanan.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();

        $condition = $request->input('condition', 'clear');
        $receiverName = $request->input('received_by_name') ?: $request->input('signature');

        // Jika supir mengunggah file foto bukti
        $podPath = null;
        if ($request->hasFile('manifest_photo')) {
            $file = $request->file('manifest_photo');
            $filename = 'pod_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $podPath = 'uploads/pod/' . $filename;
        } elseif ($request->hasFile('dock_photo')) {
            $file = $request->file('dock_photo');
            $filename = 'dock_' . $order->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pod'), $filename);
            $podPath = 'uploads/pod/' . $filename;
        }

        // Simpan perubahan ke pesanan
        $order->status = ($condition === 'discrepancy') ? 'SELESAI_CATATAN' : 'SELESAI';
        $order->arrival_time = now();
        if ($receiverName) {
            $order->received_by_name = $receiverName;
        }
        if ($podPath) {
            $order->pod_photo_url = $podPath;
        }
        if (!$order->driver_id && $user) {
            $order->driver_id = $user->id;
        }
        $order->save();

        return redirect()
            ->route('armada.tasks')
            ->with('success', 'Bukti serah terima (PoD) berhasil disimpan! Pesanan #' . $order->order_number . ' telah selesai diantarkan.');
    }
}
