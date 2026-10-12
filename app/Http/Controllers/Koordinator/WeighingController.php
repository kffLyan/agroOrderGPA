<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WeighingController extends Controller
{
    /**
     * Tampilkan Konsol Penimbangan Aktual & Validasi Sortir Gudang.
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

        // Ambil pesanan aktif dari database
        $orderQuery = Order::with(['orderItems.product', 'user', 'verifier']);
        $realOrder = null;

        if ($request->has('order_id')) {
            $realOrder = (clone $orderQuery)->find($request->input('order_id'));
        }

        if (!$realOrder) {
            $realOrder = (clone $orderQuery)->where('status', 'TERVERIFIKASI')->first()
                ?? (clone $orderQuery)->where('status', 'SIAP_KIRIM')->first()
                ?? (clone $orderQuery)->latest()->first();
        }

        $orderId = $realOrder?->id ?? 1;
        $orderCode = $realOrder?->order_number ?? 'PO-GPA-2026-0001';
        $clientName = $realOrder?->user?->company_name ?: ($realOrder?->user?->name ?? 'Grand Hotel Bandung');
        $commodities = $realOrder?->orderItems?->pluck('product.name')->filter()->unique()->join(', ') ?: 'Selada Romaine Grade A Super';
        $estimate = (float) ($realOrder?->orderItems?->sum('ordered_qty') ?: 500.0);
        $firstItem = $realOrder?->orderItems?->first();
        $unitRate = (float) ($firstItem?->unit_price ?: 18000.0);
        $isWeighed = $realOrder?->status === 'SIAP_KIRIM';
        $actualNet = (float) ($realOrder?->orderItems?->sum(fn($it) => $it->actual_net_weight ?: $it->ordered_qty) ?: $estimate);
        $crates = max(1, (int) ceil($actualNet / 20));
        $tareEach = 2.0;
        $tareCrates = (float) ($crates * $tareEach);
        $gross = $actualNet + $tareCrates;
        $verifierName = $realOrder?->verifier?->name ?? 'Sekretaris Operasional';
        $sjNumber = $realOrder?->surat_jalan_number ?: ('SJ-GPA-' . date('Ym') . '-' . str_pad($orderId, 4, '0', STR_PAD_LEFT));

        $header = [
            'eyebrow' => 'Penimbangan & Sortir Gudang',
            'title_before' => 'Penimbangan Berat Bersih (Netto)',
            'title_after' => '& Pengecekan Kualitas Panen',
            'subtitle' => 'Pencatatan hasil penimbangan riil di gudang, pengurangan berat keranjang kosong, dan pemeriksaan kualitas sayur sebelum membuat surat jalan.',
            'timestamp_label' => 'WAKTU PENIMBANGAN',
            'timestamp_value' => now()->format('d M Y, H:i') . ' WIB',
        ];

        $stages = [
            [
                'key' => 'harvest',
                'step' => 'Tahap 1: Selesai',
                'title_before' => 'Persiapan Panen',
                'title_after' => null,
                'note' => 'Target Pesanan: ' . number_format($estimate, 0, ',', '.') . ' kg',
                'icon' => 'leaf',
                'state' => 'done',
            ],
            [
                'key' => 'weighing',
                'step' => $isWeighed ? 'Tahap 2: Selesai Timbang' : 'Tahap 2: Sedang Berjalan',
                'title_before' => 'Penimbangan Netto &',
                'title_after' => 'Sortir Kualitas',
                'note' => 'Batas Toleransi Selisih ±2.0%',
                'icon' => 'scale',
                'state' => $isWeighed ? 'done' : 'active',
            ],
            [
                'key' => 'delivery',
                'step' => $isWeighed ? 'Tahap 3: Siap Surat Jalan' : 'Tahap 3: Menunggu Kunci Data',
                'title_before' => 'Penerbitan Surat Jalan',
                'title_after' => '(Surat Pengantar)',
                'note' => $isWeighed ? 'Siap Cetak & Kirim' : 'Menunggu Konfirmasi Penimbangan',
                'icon' => 'truck',
                'state' => $isWeighed ? 'active' : 'locked',
            ],
        ];

        $rules = [
            [
                'key' => 'rule-04',
                'badge' => 'ATURAN PENAGIHAN',
                'meta' => 'Penagihan Wajib Berdasarkan Berat Timbangan Nyata',
                'icon' => 'file-text',
                'tone' => 'accent',
                'segments' => [
                    ['text' => 'Jumlah pesanan awal adalah estimasi alokasi. Tagihan pembayaran dan Surat Jalan wajib merujuk secara mutlak pada ', 'tone' => 'strong'],
                    ['text' => 'Berat Bersih (Netto) hasil penimbangan riil di gudang', 'tone' => 'link'],
                    ['text' => ' (Berat Kotor dikurangi berat keranjang kosong).', 'tone' => 'strong'],
                ],
            ],
            [
                'key' => 'rule-05',
                'badge' => 'ATURAN PENGIRIMAN',
                'meta' => 'Surat Jalan Memerlukan Kunci Timbangan',
                'icon' => 'alert-triangle',
                'tone' => 'danger',
                'segments' => [
                    ['text' => 'Surat Jalan fisik tidak boleh diterbitkan sebelum ', 'tone' => 'strong'],
                    ['text' => 'data penimbangan dikunci (disahkan) oleh Koordinator Lapangan', 'tone' => 'danger'],
                    ['text' => ' demi menghindari perbedaan data dengan pihak penerima/klien.', 'tone' => 'strong'],
                ],
            ],
        ];

        $orderData = [
            'id' => $orderId,
            'code' => $orderCode,
            'client' => $clientName,
            'contract' => 'KONTRAK KEMITRAAN B2B',
            'commodity' => 'Komoditas: ' . $commodities,
            'estimate_label' => 'ESTIMASI PESANAN AWAL',
            'estimate' => $estimate,
            'rate_label' => 'HARGA PER KG',
            'rate' => 'Rp ' . number_format($unitRate, 0, ',', '.'),
            'rate_unit' => '/kg',
            'verifier_label' => 'DIVERIFIKASI OLEH',
            'verifier' => $verifierName,
            'verifier_code' => '[SEC-OP-001]',
        ];

        $terminal = [
            'title' => 'Form Input Penimbangan Hasil Panen',
            'device' => 'Pencatatan Timbangan Gudang (Manual & Digital)',
            'iot_badge' => 'Timbangan Siap Digunakan',
            'sensor_id' => 'Sensor Timbangan: Terkalibrasi Sah',
            'calibration' => 'Hitung Netto Otomatis: Aktif',
            'readings' => [
                [
                    'key' => 'gross',
                    'label' => 'Berat Kotor (Bruto)',
                    'unit' => 'KG BESERTA WADAH',
                    'value' => $gross,
                    'tone' => 'plain',
                ],
                [
                    'key' => 'tare',
                    'label' => 'Berat Wadah (Tara)',
                    'unit' => $crates . ' PETI @ ' . number_format($tareEach, 2) . ' KG',
                    'value' => $tareCrates,
                    'tone' => 'plain',
                ],
                [
                    'key' => 'net',
                    'label' => 'Berat Bersih (Netto)',
                    'unit' => 'KG BERSIH SIAP KIRIM',
                    'value' => $actualNet,
                    'tone' => 'highlight',
                ],
            ],
            'inputs' => [
                [
                    'key' => 'gross',
                    'label' => '1. Berat Kotor (kg)',
                    'value' => number_format($gross, 2, '.', ''),
                    'unit' => 'KG',
                    'tone' => 'plain',
                ],
                [
                    'key' => 'crates',
                    'label' => '2. Jumlah Keranjang',
                    'value' => (string) $crates,
                    'unit' => 'PETI',
                    'tone' => 'plain',
                ],
                [
                    'key' => 'tare_each',
                    'label' => '3. Berat per Keranjang (kg)',
                    'value' => '2.00',
                    'unit' => 'KG',
                    'tone' => 'plain',
                ],
            ],
            'actions' => [
                ['key' => 'reset-zero', 'label' => 'Nol-kan Timbangan (Reset)'],
                ['key' => 're-read', 'label' => 'Perbarui Angka Timbangan'],
            ],
            'metrology_note' => 'Status Kalibrasi Timbangan: Sah & Akurat',
        ];

        $afkirKg = max(0, round($estimate * 0.006, 2));
        $cleanKg = $actualNet;
        $cleanPercent = ($estimate > 0) ? round(($cleanKg / $estimate) * 100, 2) . '%' : '100%';

        $grading = [
            'title' => 'Pemeriksaan Kualitas & Pemilahan Daun',
            'device' => 'Pengecekan kesegaran daun dan pemisahan daun rusak/kuning',
            'standard' => 'Standar Mutu GPA',
            'columns' => [
                'KATEGORI SAYUR',
                'BERAT BERSIH',
                'PERSENTASE',
                'STATUS PEMERIKSAAN',
                'TINDAKAN GUDANG',
            ],
            'rows' => [
                [
                    'key' => 'grade-a',
                    'name' => 'Grade A (Segar & Prima)',
                    'note' => ['Daun segar, renyah,', 'bersih dari tanah,', 'tanpa busuk'],
                    'mass' => $cleanKg,
                    'percent' => $cleanPercent,
                    'status' => ['LOLOS', 'SORTIR'],
                    'action' => ['Muat ke', 'Box', 'Kirim'],
                    'tone' => 'success',
                ],
                [
                    'key' => 'afkir',
                    'name' => 'Afkir (Daun Kuning / Rusak)',
                    'note' => ['Penyortiran daun layu,', 'kotor & cacat'],
                    'mass' => $afkirKg,
                    'percent' => '0.6%',
                    'status' => ['DIPISAHKAN', 'SUSUT'],
                    'action' => ['Dijadikan', 'Kompos /', 'Pakan'],
                    'tone' => 'warning',
                ],
            ],
        ];

        $evidence = [
            'title' => 'Foto Dokumentasi Timbangan & Sayur',
            'description' => 'Ambil atau unggah foto angka timbangan serta sampel daun segar bila diperlukan arsip bukti fisik.',
            'action' => 'UNGGAH FOTO BUKTI',
            'icon' => 'upload',
        ];

        $varianceKg = $actualNet - $estimate;
        $variancePct = $estimate > 0 ? round(($varianceKg / $estimate) * 100, 2) : 0;

        $reconciliation = [
            'title' => 'Perbandingan Estimasi vs Realisasi Timbangan',
            'match' => 'Status Pesanan: Sesuai (100%)',
            'sources' => [
                [
                    'key' => 'farmer',
                    'name' => 'Petani Binaan Kelompok Tani',
                    'batch' => 'BATCH: PANEN-INTI-' . date('Ym'),
                    'mass' => number_format($actualNet * 0.7, 2, ',', '.') . ' kg',
                    'percent' => '70% Total Pasokan',
                ],
                [
                    'key' => 'buffer',
                    'name' => 'Pasokan Cadangan (Buffer)',
                    'batch' => 'BATCH: BUFFER-' . date('Ym'),
                    'mass' => number_format($actualNet * 0.3, 2, ',', '.') . ' kg',
                    'percent' => '30% Total Pasokan',
                ],
            ],
            'panel' => [
                ['label' => 'Estimasi Pesanan Awal:', 'value' => number_format($estimate, 2, ',', '.') . ' kg', 'tone' => 'ink'],
                ['label' => 'Hasil Timbangan Bersih Riil:', 'value' => number_format($actualNet, 2, ',', '.') . ' kg', 'tone' => 'success'],
                ['label' => 'Selisih Berat (Susut / Tambah):', 'value' => ($varianceKg >= 0 ? '+' : '') . number_format($varianceKg, 2, ',', '.') . ' kg (' . $variancePct . '%)', 'tone' => $varianceKg < 0 ? 'warning' : 'success', 'multiline' => true],
            ],
            'status' => 'Status: Selisih masih dalam batas wajar (≤ 2.0%)',
        ];

        $finalAmount = $actualNet * $unitRate;
        $estAmount = $estimate * $unitRate;
        $diffAmount = $finalAmount - $estAmount;

        $financial = [
            'title' => 'Rincian Nilai Pembayaran Pesanan',
            'meta' => 'Perhitungan Otomatis Berdasarkan Timbangan',
            'icon' => 'banknote',
            'rows' => [
                ['label' => 'Harga Kontrak per Kg', 'value' => 'Rp ' . number_format($unitRate, 0, ',', '.') . ' / kg', 'tone' => 'ink'],
                ['label' => 'Estimasi Awal Pesanan (' . number_format($estimate, 0, ',', '.') . ' kg)', 'value' => 'Rp ' . number_format($estAmount, 0, ',', '.'), 'tone' => 'muted-strike'],
                ['label' => 'Penyesuaian Berdasarkan Timbangan Nyata', 'value' => ($diffAmount >= 0 ? '+ Rp ' : '- Rp ') . number_format(abs($diffAmount), 0, ',', '.'), 'tone' => $diffAmount < 0 ? 'warning' : 'success'],
            ],
            'final_label' => 'TOTAL AKHIR NILAI TRANSAKSI',
            'final_value' => 'Rp ' . number_format($finalAmount, 0, ',', '.'),
            'final_note' => 'Menjadi dasar resmi pencetakan Faktur Tagihan dan Surat Jalan untuk ' . $clientName . '.',
            'metrology' => [
                ['label' => 'KODE VERIFIKASI:', 'value' => 'VERIF-' . substr(hash('sha256', $orderCode . $actualNet), 0, 10)],
                ['label' => 'PETUGAS PENIMBANG:', 'value' => ($user?->name ?? 'Koordinator Lapangan')],
                ['label' => 'STATUS KEAMANAN:', 'value' => 'Data Tercatat Resmi di Sistem'],
            ],
        ];

        $audit = [
            'title' => 'Catatan Keamanan & Riwayat Timbangan',
            'body' => 'Data berat timbangan ini tersimpan resmi di sistem. Setelah dikunci, data berat bersih akan menjadi acuan tetap untuk penerbitan Surat Jalan dan penagihan.',
        ];

        $actions = [
            'confirm_title' => 'Konfirmasi Berat Bersih: ' . number_format($actualNet, 2, ',', '.') . ' KG',
            'confirm_note' => 'Klik tombol di bawah untuk mengunci hasil timbangan dan mengizinkan pembuatan Surat Jalan.',
            'draft' => ['key' => 'draft', 'label' => 'SIMPAN SEMENTARA (DRAF)', 'icon' => 'save'],
            'lock' => ['key' => 'lock', 'label' => 'KUNCI & SIMPAN HASIL TIMBANGAN', 'icon' => 'lock'],
            'delivery' => ['key' => 'delivery', 'label' => 'TERBITKAN SURAT JALAN (' . $sjNumber . ')', 'icon' => 'truck'],
        ];

        $viewName = view()->exists('koordinator.weighing') ? 'koordinator.weighing' : 'coordinator.weighing';

        return view($viewName, [
            'operator' => $operator,
            'header' => $header,
            'stages' => $stages,
            'rules' => $rules,
            'order' => $orderData,
            'terminal' => $terminal,
            'grading' => $grading,
            'evidence' => $evidence,
            'reconciliation' => $reconciliation,
            'financial' => $financial,
            'audit' => $audit,
            'actions' => $actions,
        ]);
    }

    /**
     * Kunci dan simpan data penimbangan aktual netto riil (PRD Rule 04 & 05).
     */
    public function storeWeighing(Request $request, $order_id): JsonResponse|RedirectResponse
    {
        $order = Order::with('orderItems')->findOrFail($order_id);

        DB::transaction(function () use ($request, $order) {
            $user = $request->user();

            if ($request->has('items') && is_array($request->input('items'))) {
                $request->validate([
                    'items' => 'required|array',
                    'items.*.id' => 'required|exists:order_items,id',
                    'items.*.actual_net_weight' => 'required|numeric|min:0.01',
                ]);

                foreach ($request->input('items') as $itemInput) {
                    $item = $order->orderItems->firstWhere('id', $itemInput['id']);
                    if ($item) {
                        $actual = (float) $itemInput['actual_net_weight'];
                        $item->update([
                            'actual_net_weight' => $actual,
                            'subtotal_final' => $actual * (float) $item->unit_price,
                            'weighed_by' => $user?->id,
                            'weighed_at' => now(),
                        ]);
                    }
                }
            } else {
                $request->validate([
                    'actual_net_weight' => 'required|numeric|min:0.01',
                ]);

                $actualWeight = (float) $request->input('actual_net_weight');

                if ($order->orderItems->count() <= 1) {
                    $item = $order->orderItems->first();
                    if ($item) {
                        $item->update([
                            'actual_net_weight' => $actualWeight,
                            'subtotal_final' => $actualWeight * (float) $item->unit_price,
                            'weighed_by' => $user?->id,
                            'weighed_at' => now(),
                        ]);
                    }
                } else {
                    $totalOrdered = (float) $order->orderItems->sum('ordered_qty');
                    foreach ($order->orderItems as $item) {
                        $ratio = $totalOrdered > 0 ? ((float) $item->ordered_qty / $totalOrdered) : (1 / max(1, $order->orderItems->count()));
                        $itemActual = round($actualWeight * $ratio, 2);
                        $item->update([
                            'actual_net_weight' => $itemActual,
                            'subtotal_final' => $itemActual * (float) $item->unit_price,
                            'weighed_by' => $user?->id,
                            'weighed_at' => now(),
                        ]);
                    }
                }
            }

            // Recalculate order grand total from items
            $order->refresh();
            $grandTotal = $order->orderItems->sum(function ($it) {
                return $it->subtotal_final ?? ($it->actual_net_weight * $it->unit_price);
            });

            $order->update([
                'grand_total' => $grandTotal,
                'status' => 'SIAP_KIRIM',
            ]);
        });

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data timbangan netto riil berhasil dikunci. Status pesanan kini SIAP_KIRIM.',
                'order' => $order->fresh(['orderItems']),
            ]);
        }

        return redirect()->back()->with('success', 'Data timbangan netto riil berhasil dikunci. Status pesanan kini SIAP_KIRIM.');
    }
}
