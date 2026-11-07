<?php

namespace App\Http\Resources\Direktur;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuBesarPiutangResource extends JsonResource
{
    // Konstruktor resource buku besar piutang dengan parameter daftar klien yang dibekukan
    public function __construct(
        $resource,
        protected array $frozenClients = []
    ) {
        parent::__construct($resource);
    }

    // Mengubah entitas klien B2B menjadi data baris neraca umur piutang dan utilisasi plafon kredit
    public function toArray(Request $request): array
    {
        $client = $this->resource;

        // Ambil faktur yang belum lunas atau lewat jatuh tempo
        $unpaidInvoices = $client->invoices->whereIn('status', ['UNPAID', 'OVERDUE']);
        $totalUnpaidAmount = (float) $unpaidInvoices->sum('grand_total');

        // Ambil kontrak aktif dan tentukan plafon kredit serta batas TOP
        $activeContract = $client->contracts->firstWhere('status', 'ACTIVE');
        $creditLimit = (float) $client->credit_limit;
        $topDays = $activeContract ? (int) $activeContract->top_days : 30;
        $totalExposure = $totalUnpaidAmount;
        $limitUtilizationRate = $creditLimit > 0 ? round(($totalExposure / $creditLimit) * 100, 1) : 0;

        $explicitFreeze = null;
        if (array_key_exists($client->id, $this->frozenClients)) {
            $explicitFreeze = $this->frozenClients[$client->id];
        } elseif (array_key_exists((string) $client->id, $this->frozenClients)) {
            $explicitFreeze = $this->frozenClients[(string) $client->id];
        }

        if ($explicitFreeze !== null) {
            $isFrozen = (bool) $explicitFreeze;
        } else {
            $isFrozen = ($limitUtilizationRate > 100);
        }

        $isDispensation = (!$isFrozen && $limitUtilizationRate > 100);

        // Kumpulkan daftar nomor surat jalan dan cari tanggal jatuh tempo paling awal
        $suratJalanList = [];
        $earliestDueDate = null;
        foreach ($unpaidInvoices as $invoice) {
            if (!$earliestDueDate || ($invoice->due_date && $invoice->due_date < $earliestDueDate)) {
                $earliestDueDate = $invoice->due_date;
            }
            foreach ($invoice->orders as $order) {
                if ($order->surat_jalan_number) {
                    $suratJalanList[] = $order->surat_jalan_number;
                }
            }
        }

        // Hitung sisa hari jatuh tempo dari tanggal hari ini
        $today = now()->startOfDay();
        $dueDate = $earliestDueDate ? Carbon::parse($earliestDueDate)->startOfDay() : $today->copy()->addDays($topDays);
        $daysRemaining = (int) round($today->diffInDays($dueDate, false));

        // Tentukan label dan warna status piutang
        $statusLabel = 'LANCAR';
        $statusTone = 'success';
        if ($isFrozen) {
            $statusLabel = 'AUTO-FREEZE PO';
            $statusTone = 'danger';
        } elseif ($isDispensation) {
            $statusLabel = 'DISPENSASI DIREKTUR';
            $statusTone = 'warning';
        } elseif ($daysRemaining < 0) {
            $statusLabel = 'WARNING TEMPO';
            $statusTone = 'warning';
        }

        // Tentukan status verifikasi dan kode unik klien
        if ($isFrozen) {
            $verificationTone = 'danger';
            $verificationLabel = 'DEFISIT PLAFON';
        } elseif ($isDispensation) {
            $verificationTone = 'warning';
            $verificationLabel = 'DISPENSASI PLAFON';
        } elseif ($daysRemaining < 0) {
            $verificationTone = 'warning';
            $verificationLabel = 'BUTUH KONFIRMASI WA';
        } else {
            $verificationTone = 'success';
            $verificationLabel = 'TERVERIFIKASI FINANCE';
        }

        $cleanClientName = preg_replace('/[^A-Za-z]/', '', (string) $client->name) ?: 'KLI';
        $clientCode = 'B2B-' . strtoupper(substr($cleanClientName, 0, 3)) . '-' . str_pad((string) $client->id, 3, '0', STR_PAD_LEFT);

        $invoicesData = $unpaidInvoices->map(fn ($inv) => [
            'id'             => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'grand_total'    => (float) $inv->grand_total,
            'total_label'    => 'Rp ' . number_format($inv->grand_total, 0, ',', '.'),
            'due_date'       => $inv->due_date ? Carbon::parse($inv->due_date)->translatedFormat('d M Y') : '—',
            'status'         => $inv->status,
            'print_url'      => route('prints.invoice', ['invoice' => $inv->invoice_number]),
        ])->values()->all();

        if ($isFrozen) {
            $actions = ['Restrukturisasi Tagihan', 'Lepas Freeze'];
        } elseif ($isDispensation) {
            $actions = ['Detail Faktur', 'Kunci Plafon'];
        } elseif ($daysRemaining < 0) {
            $actions = ['Detail Faktur', 'Kirim Reminder WA', 'Kunci Plafon'];
        } else {
            $actions = ['Detail Faktur', 'Kunci Plafon'];
        }

        return [
            'id'                => $client->id,
            'name'              => $client->company_name ?: $client->name,
            'client_id'         => 'ID-KLIEN:' . $clientCode,
            'client_code'       => $clientCode,
            'locked'            => $isFrozen,
            'sj_count'          => count($suratJalanList),
            'sj_count_label'    => count($suratJalanList) > 0 ? (count($suratJalanList) . ' Surat Jalan') : 'Belum Ada SJ',
            'sj_label'          => !empty($suratJalanList) ? implode(', ', array_slice($suratJalanList, 0, 3)) : '—',
            'outstanding'       => $totalUnpaidAmount,
            'outstanding_label' => 'Rp ' . number_format($totalUnpaidAmount, 0, ',', '.'),
            'verification'      => $verificationLabel,
            'verification_tone' => $verificationTone,
            'top_days'          => $topDays,
            'top_label'         => 'TOP ' . $topDays . ' HARI',
            'due_date'          => $dueDate,
            'due_label'         => $dueDate->translatedFormat('d M Y'),
            'days_remaining'    => $daysRemaining,
            'days_label'        => $daysRemaining < 0 ? '(! OVERDUE ' . abs($daysRemaining) . ' HARI)' : ($daysRemaining === 0 ? '(Jatuh Tempo Hari Ini)' : '(' . $daysRemaining . ' Hari Tersisa)'),
            'status_label'      => $statusLabel,
            'status_tone'       => $statusTone,
            'limit'             => $creditLimit,
            'exposure'          => $totalExposure,
            'limit_label'       => 'Rp ' . round($totalExposure / 1000000) . 'M / ' . round($creditLimit / 1000000) . 'M',
            'utilisation'       => $limitUtilizationRate,
            'utilisation_label' => round($limitUtilizationRate) . '%',
            'utilisation_note'  => $limitUtilizationRate > 100 ? round($limitUtilizationRate) . '% (OVER)' : round($limitUtilizationRate) . '%',
            'utilisation_width' => min(100, $limitUtilizationRate),
            'over_limit'        => $limitUtilizationRate > 100,
            'invoices'          => $invoicesData,
            'actions'           => $actions,
        ];
    }
}
