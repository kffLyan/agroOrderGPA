<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HarvestPlanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $operator = [
            'name'   => $user?->name ?? 'Koordinator',
            'role'   => 'Koordinator Lapangan',
            'id'     => $user ? 'KL-' . str_pad($user->id, 4, '0', STR_PAD_LEFT) : 'KL-0000',
            'avatar' => strtoupper(substr($user?->name ?? 'K', 0, 1)),
        ];

        $orders = Order::with(['orderItems.product','user'])
            ->whereIn('status', ['TERVERIFIKASI','SIAP_KIRIM'])
            ->latest()->take(10)->get();

        $totalOrders    = $orders->count();
        $siapKirimCount = $orders->where('status','SIAP_KIRIM')->count();

        $queueRows = $orders->values()->map(function ($order, $index) {
            $isSiap    = $order->status === 'SIAP_KIRIM';
            $stage     = $isSiap ? 4 : 2;
            $stageTone = $isSiap ? 'accent-soft' : 'pill';
            $totalQty  = (float) $order->orderItems->sum('ordered_qty');
            $kratTotal = max(1, (int) ceil($totalQty / 20));
            $kratDone  = $isSiap ? $kratTotal : (int) ceil($kratTotal * 0.7);
            $product   = $order->orderItems->first()?->product;
            $bay       = 'STG-BAY-' . chr(65 + ($index % 4)) . ($index % 2 + 1);
            $station   = 'MEJA ' . str_pad($index % 5 + 1, 2, '0', STR_PAD_LEFT);

            return [
                'po'             => $order->order_number,
                'bay'            => $bay,
                'station'        => $station,
                'buyer'          => $order->user?->name ?? '-',
                'commodity'      => $product?->name ?? 'Komoditas',
                'commodity_note' => '[Grade A]',
                'commodity_tone' => 'success',
                'krat_done'      => $kratDone,
                'krat_total'     => $kratTotal,
                'est_kg'         => $totalQty,
                'stage'          => $stage,
                'stage_tone'     => $stageTone,
                'afkir'          => null,
                'action'         => $isSiap ? 'select' : 'inspect',
                'action_label'   => $isSiap ? 'Pilih' : 'Inspeksi',
                'row_tone'       => $isSiap ? 'default' : 'active',
            ];
        })->all();

        $fallbackRow = [
            'po' => 'BELUM-ADA-ORDER','bay' => '-','station' => '-',
            'buyer' => 'Belum ada order aktif','commodity' => '-',
            'commodity_note' => null,'commodity_tone' => 'body',
            'krat_done' => 0,'krat_total' => 0,'est_kg' => 0,
            'stage' => 1,'stage_tone' => 'pill',
            'afkir' => null,'action' => 'select','action_label' => 'Pilih','row_tone' => 'default',
        ];

        $pctDone = $totalOrders > 0 ? round(($siapKirimCount / $totalOrders) * 100, 1) : 0;
        $totalKg = (float) $orders->flatMap->orderItems->sum('ordered_qty');

        $harvestPlanData = [
            'operator' => $operator,
            'mandate'  => [
                'title'  => 'MANDAT ALUR SORTIR DAN PRE-COOLING SEBELUM DOKUMEN DISPATCH (PRD RULE 04 & 09)',
                'badge'  => 'MANDATORY ENFORCED',
                'stages' => [
                    ['stage'=>'TAHAP 01','title'=>'Inspeksi Visual Sortir','body'=>'Cek ketat kebusukan >2%, eliminasi daun kuning, ulat, telur hama, dan residu lumpur tanah.','sop'=>'SOP: MAX AFKIR 2.0%','tag'=>'GRADE A/B FILTER','icon'=>'eye'],
                    ['stage'=>'TAHAP 02','title'=>'Pembersihan & Trimming','body'=>'Potong rata pangkal bonggol maksimal 1.5 cm; lap embun basah berlebih pencegahan pembusukan dingin.','sop'=>'BONGGOL: 1.0 - 1.5 CM','tag'=>'DRY-WIPED','icon'=>'leaf'],
                    ['stage'=>'TAHAP 03','title'=>'Krat Cold-Chain Steril','body'=>'Wadah berlubang standar tara 2.00 kg sanitasi food-grade desinfeksi klorin 50ppm suhu pra-isi +4C.','sop'=>'TARA STANDARD: 2.0 KG','tag'=>'SANITIZED << 5C','icon'=>'package'],
                    ['stage'=>'TAHAP 04','title'=>'Timbangan Netto Sah','body'=>'Penerbitan barcode batch timbang digital terintegrasi ledger dan segel strapping pallet berlapis.','sop'=>'RFID / BARCODE LOT','tag'=>'DISPATCH READY','icon'=>'scale'],
                ],
            ],
            'kpis' => [
                ['key'=>'packing',     'label'=>'TOTAL PO DALAM PACKING',    'value'=>$totalOrders,    'unit'=>'Batch Aktif',             'unit_tone'=>'body',    'note'=>'Order aktif di sistem',                                              'note_tone'=>'muted','progress'=>min(100,(int)($pctDone)),'bar'=>'bg-ink',        'icon'=>'package',    'icon_chip'=>'shell',      'icon_tone'=>'ink'],
                ['key'=>'sorted',      'label'=>'SELESAI SORTIR & PACKING',  'value'=>$siapKirimCount, 'unit'=>'Selesai ('.$pctDone.'%)', 'unit_tone'=>'success', 'note'=>number_format($totalKg,2,'.',',').' kg menuju antrean timbang',      'note_tone'=>'body', 'progress'=>min(100,(int)$pctDone),  'bar'=>'bg-success-deep','icon'=>'badge-check', 'icon_chip'=>'accent-soft','icon_tone'=>'success-deep'],
                ['key'=>'crates',      'label'=>'KETERSEDIAAN KRAT STERIL',  'value'=>'280',           'unit'=>'/ 350 Krat',              'unit_tone'=>'body',    'note'=>'Tara @2.0 kg - Staging Bay A',                                       'note_tone'=>'muted','progress'=>80,                       'bar'=>'bg-accent-edge', 'icon'=>'package',    'icon_chip'=>'shell',      'icon_tone'=>'ink'],
                ['key'=>'temperature', 'label'=>'SUHU RUANG PRE-COOLING',    'value'=>'+3.8C',         'unit'=>null,'chip_label'=>'NORMAL','unit_tone'=>'body',   'note'=>'ISO 22000 Standard (+2C ~ +5C)',                                      'note_tone'=>'muted','progress'=>60,                       'bar'=>'bg-accent',      'icon'=>'thermometer','icon_chip'=>'accent-soft','icon_tone'=>'ink'],
            ],
            'queue' => [
                'title'        => 'Antrean Batch Packing Berjalan',
                'subtitle'     => 'Staging zone meja pembersihan, sortir mutu, dan pelabelan peti',
                'badge'        => 'LIVE REFRESH: 5S',
                'columns'      => ['BATCH & PO ID','BUYER & KOMODITAS','KRAT & BOBOT','STATUS TAHAP','AKSI'],
                'stages'       => [
                    1 => ['chip'=>'TAHAP 1: TRIMMING',         'tone'=>'pill'],
                    2 => ['chip'=>'TAHAP 2: SORTIR & QC',      'tone'=>'pill'],
                    3 => ['chip'=>'TAHAP 3: MENUNGGU TIMBANGAN','tone'=>'accent'],
                    4 => ['chip'=>'SIAP TIMBANG',               'tone'=>'accent-soft'],
                ],
                'rows'         => $queueRows ?: [$fallbackRow],
                'footer_left'  => 'MEMPERLIHATKAN ' . count($queueRows) . ' ORDER OPERASIONAL',
                'footer_right' => 'STANDARD PACKING OPERATIONAL SPEED: 18 KRAT / JAM',
                'total_batches'=> $totalOrders,
                'done_batches' => $siapKirimCount,
                'weigh_kg'     => $totalKg,
                'weight_target'=> $totalKg * 1.1,
            ],
            'station' => [
                'eyebrow'        => 'DETAIL WORKSTATION AKTIF',
                'tab_prefix'     => 'WORKSTATION',
                'barcode_prefix' => 'KRAT-GPA-SBG-' . now()->format('m') . '-' . now()->year,
                'pallet_tier'    => '20 Krat (Tier 4x5)',
                'steps' => [
                    ['title'=>'1. Label Krat Terpasang (Barcode/RFID)','icon'=>'qrcode', 'chip'=>'TERVALIDASI','prefix_label'=>'Prefix:','notes'=>['Warna: Kuning Cold-Chain (Food Grade) - Tara: 2.00 kg / Peti']],
                    ['title'=>'2. Kebersihan Wadah (HACCP Food-Grade)', 'icon'=>'shield', 'chip'=>'LOLOS',     'checks'=>['Bebas residu jamur/tanah','Disinfektan klorin 50ppm'],'notes'=>['Suhu peti sebelum diisi: +4.1C (maks 5.0C)']],
                    ['title'=>'3. Pembagian Lot & Konfigurasi Pallet',  'icon'=>'package','chip'=>'TERPASANG', 'pallets'=>true,'notes'=>['Strapping pita hitam 2 titik & cover termal foil terpasang rapi']],
                ],
                'qc_note' => ['label'=>'CATATAN KHUSUS PENGAWAS QC:','text'=>'"Pangkal daun dipotong rata 1.0 cm, kondisi selada renyah, embun permukaan telah dilap bersih, siap ditimbang netto resmi."'],
                'buttons' => [
                    ['key'=>'print-label','label'=>'Cetak Label Krat',   'icon'=>'printer',       'variant'=>'outline'],
                    ['key'=>'flag-afkir', 'label'=>'Tandai Afkir Tinggi','icon'=>'alert-triangle','variant'=>'warning'],
                ],
                'cta' => ['key'=>'transfer','label'=>'TRANSFER KE MEJA TIMBANGAN NETTO >>','icon'=>'scale'],
            ],
            'qc' => [
                'title'      => 'Log Uji Petik Mutu & Sortir Afkir Meja Grading',
                'subtitle'   => 'Sampel acak dari batch aktif (' . now()->format('d M Y') . ')',
                'avg_label'  => 'RATA-RATA SUSUT TRIMMING',
                'avg_value'  => '0.89%',
                'avg_note'   => '(Toleransi SOP: Max 2.00%)',
                'inspector'  => 'Inspektor: ' . ($user?->name ?? 'QC Officer'),
                'sensor_note'=> 'Sensor Calibrated',
                'tolerance'  => 2.00,
                'samples'    => [
                    ['krat'=>'KRAT SAMPEL #04','status'=>'PASS QC','gross'=>22.12,'tara'=>2.00,'trim'=>0.18,'trim_percent'=>0.89],
                    ['krat'=>'KRAT SAMPEL #18','status'=>'PASS QC','gross'=>22.05,'tara'=>2.00,'trim'=>0.22,'trim_percent'=>1.09],
                    ['krat'=>'KRAT SAMPEL #33','status'=>'PASS QC','gross'=>22.18,'tara'=>2.00,'trim'=>0.14,'trim_percent'=>0.69],
                ],
            ],
        ];

        $viewName = view()->exists('koordinator.harvest-plan') ? 'koordinator.harvest-plan' : 'coordinator.harvest-plan';
        return view($viewName, ['operator' => $operator, 'harvestPlanData' => $harvestPlanData]);
    }
}