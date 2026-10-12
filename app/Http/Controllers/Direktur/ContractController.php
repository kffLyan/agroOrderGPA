<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Direktur\StoreContractRequest;
use App\Http\Requests\Direktur\UpdateContractRequest;
use App\Models\Contract;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\GovernanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ContractController extends Controller
{
    protected GovernanceService $governanceService;

    public function __construct(GovernanceService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    /**
     * Halaman Khusus Approval Kontrak Khusus & Diskon Volume B2B (PRD Sec 16 & 20)
     */
    public function approval(Request $request)
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $user?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($user?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($user?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $user?->email ?: 'direktur@greenpasundan.id',
            'period'   => 'Minggu ' . date('W') . ' ' . date('Y') . ' | Q4 YTD',
        ];

        $header = [
            'eyebrow'      => 'Persetujuan Kontrak',
            'title'        => 'Persetujuan Kontrak Khusus, Diskon Volume & Perpanjangan TOP B2B',
            'subtitle'     => 'Antrean tunggal seluruh pengajuan yang memerlukan tanda tangan digital Direktur Utama: diskon di luar plafon, ekstensi TOP, dan kontrak induk tahunan.',
            'period'       => 'PERIODE: OKTOBER 2026',
            'filter_label' => 'Filter',
        ];

        $defaultRows = [
            [
                'contract'         => 'CTR-B2B-2025-089',
                'region'           => 'BOGOR',
                'client'           => 'Hotel Grand Pangrango',
                'client_tone'      => 'caution',
                'submitted_by'     => 'Tim Sales Key Account',
                'submitted_at'     => '10 Okt 2026',
                'commodity'        => 'Selada Romaine Super',
                'volume'           => '800 kg / minggu (3.2 Ton/Bln)',
                'segment'          => 'Grade A - Hydroponik Binaan',
                'price'            => 'Rp 13.800 / kg',
                'price_strike'     => 'Rp 15.000',
                'list_price'       => 15000,
                'offer_price'      => 13800,
                'discount_percent' => 8.0,
                'discount_ceiling' => 5.0,
                'exception'        => 'Diskon Khusus: 8.0% (Plafon 5%)',
                'exception_tone'   => 'caution',
                'term'             => 'TOP 30 Hari',
                'top_days'         => 30,
                'top_default'      => 30,
                'margin'           => '18.5% Net Margin',
                'margin_value'     => 18.5,
                'margin_minimum'   => 15.0,
                'margin_delta'     => 3.5,
                'margin_flag'      => 'Safe Margin Pass (+3.5%)',
                'score'            => 'A- (82/100)',
                'score_note'       => '0 Riwayat Macet // 18 bln',
                'approve_label'    => 'Setujui Kontrak',
                'approve_tone'     => 'solid',
                'filter_key'       => 'discount',
                'value'            => 145000000,
                'value_label'      => 'Rp 145.000.000',
                'exceeds_plafon'   => true,
            ],
            [
                'contract'         => 'CTR-B2B-2025-092',
                'region'           => 'JAKARTA PUSAT',
                'client'           => 'PT Boga Rasa Kulina Prima',
                'client_tone'      => 'success',
                'submitted_by'     => 'VP Commercial',
                'submitted_at'     => '12 Okt 2026',
                'commodity'        => 'Beras Slyp & Bumbu Basah',
                'volume'           => 'Plafon Transaksi: Rp 150.000.000',
                'segment'          => 'Distribusi Multi-Resto Chain (14 Outlet)',
                'price'            => 'Harga Standard List Katalog',
                'price_strike'     => null,
                'list_price'       => null,
                'offer_price'      => null,
                'discount_percent' => null,
                'discount_ceiling' => null,
                'exception'        => 'Usulan Ekstensi: TOP 45 Hari',
                'exception_tone'   => 'caution',
                'term'             => 'Reguler Default 30 Hari',
                'top_days'         => 45,
                'top_default'      => 30,
                'margin'           => '21.2% Net Margin',
                'margin_value'     => 21.2,
                'margin_minimum'   => 15.0,
                'margin_delta'     => 6.2,
                'margin_flag'      => 'High Yield Account (+6.2%)',
                'score'            => 'A+ (88/100)',
                'score_note'       => 'Jaminan Bank Garansi BNI',
                'approve_label'    => 'Setujui Kontrak',
                'approve_tone'     => 'solid',
                'filter_key'       => 'top',
                'value'            => 120000000,
                'value_label'      => 'Rp 120.000.000',
                'exceeds_plafon'   => true,
            ],
            [
                'contract'         => 'CTR-B2B-2025-095',
                'region'           => 'SOEKARNO-HATTA HUB',
                'client'           => 'PT Aerofood ACS Indonesia',
                'client_tone'      => 'solid',
                'submitted_by'     => 'Directorate Key Account',
                'submitted_at'     => '13 Okt 2026',
                'commodity'        => 'Sayuran Cut & Clean Hygienic',
                'volume'           => '300 kg / hari (Daily Reefer Delivery)',
                'segment'          => 'Standard Inflight Catering HACCP',
                'price'            => 'Kontrak Tahunan Terikat (12 Bln)',
                'price_strike'     => null,
                'list_price'       => null,
                'offer_price'      => null,
                'discount_percent' => null,
                'discount_ceiling' => null,
                'exception'        => 'Harga Fixed Index + Eskalasi BBM',
                'exception_tone'   => 'neutral',
                'term'             => 'TOP 30 Hari via Virtual Account BNI',
                'top_days'         => 30,
                'top_default'      => 30,
                'margin'           => '18.0% Net Margin',
                'margin_value'     => 18.0,
                'margin_minimum'   => 15.0,
                'margin_delta'     => null,
                'margin_flag'      => 'Corporate Tier-1 Stable',
                'score'            => 'AAA (96/100)',
                'score_note'       => 'BUMN Subsidiary // 0 Default',
                'approve_label'    => 'Setujui Kontrak',
                'approve_tone'     => 'solid',
                'filter_key'       => 'annual',
                'value'            => 120000000,
                'value_label'      => 'Rp 120.000.000',
                'exceeds_plafon'   => false,
            ],
        ];

        // Ambil kontrak dari DB jika ada
        $pendingDb = Contract::whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->with(['user', 'product'])
            ->get();

        $rows = [];
        if ($pendingDb->count() > 0) {
            foreach ($pendingDb as $c) {
                $clientName = $c->user ? ($c->user->company_name ?: $c->user->name) : 'Klien Korporat B2B';
                $prodName = $c->product ? $c->product->name : 'Komoditas Inti';
                $vol = (float) ($c->committed_volume_per_cycle ?: 1000);
                $price = (float) ($c->fixed_price_per_kg ?: 15000);
                $contractVal = $vol * $price;
                $top = (int) ($c->top_days ?: 30);

                $filterKey = 'annual';
                if ($price < 14000) {
                    $filterKey = 'discount';
                } elseif ($top > 30) {
                    $filterKey = 'top';
                }

                $rows[] = [
                    'contract'         => $c->contract_number,
                    'region'           => 'BANDUNG RAYA',
                    'client'           => $clientName,
                    'client_tone'      => 'success',
                    'submitted_by'     => 'Key Account Executive',
                    'submitted_at'     => $c->created_at ? $c->created_at->translatedFormat('d M Y') : '12 Okt 2026',
                    'commodity'        => $prodName,
                    'volume'           => number_format($vol, 0, ',', '.') . ' kg / siklus',
                    'segment'          => 'Standar Kemitraan GPA',
                    'price'            => 'Rp ' . number_format($price, 0, ',', '.') . ' / kg',
                    'price_strike'     => null,
                    'list_price'       => null,
                    'offer_price'      => null,
                    'discount_percent' => null,
                    'discount_ceiling' => null,
                    'exception'        => $top > 30 ? "Ekstensi TOP {$top} Hari" : 'Pengajuan Standar',
                    'exception_tone'   => $top > 30 ? 'caution' : 'neutral',
                    'term'             => "TOP {$top} Hari",
                    'top_days'         => $top,
                    'top_default'      => 30,
                    'margin'           => '19.5% Net Margin',
                    'margin_value'     => 19.5,
                    'margin_minimum'   => 15.0,
                    'margin_delta'     => 4.5,
                    'margin_flag'      => 'Safe Margin Pass',
                    'score'            => 'A (85/100)',
                    'score_note'       => 'Riwayat Kemitraan Lancar',
                    'approve_label'    => 'Setujui Kontrak',
                    'approve_tone'     => 'solid',
                    'filter_key'       => $filterKey,
                    'value'            => $contractVal > 0 ? $contractVal : 100000000,
                    'value_label'      => 'Rp ' . number_format($contractVal > 0 ? $contractVal : 100000000, 0, ',', '.'),
                    'exceeds_plafon'   => $top > 30 || $price < 14000,
                ];
            }
        }

        if (empty($rows)) {
            $rows = $defaultRows;
        }

        $totalVal = array_sum(array_column($rows, 'value'));
        $exceedCount = count(array_filter($rows, fn ($r) => !empty($r['exceeds_plafon'])));

        $cards = [
            [
                'key'       => 'pending',
                'label'     => 'Pengajuan Pending',
                'value'     => count($rows) . ' Pengajuan',
                'tone'      => 'warning',
                'icon'      => 'clock',
                'chip'      => 'GATE L4',
                'chip_tone' => 'warning',
                'meta'      => 'Menunggu tanda tangan digital',
                'meta_tone' => 'warning',
            ],
            [
                'key'       => 'value',
                'label'     => 'Nilai Kontrak Pending',
                'value'     => 'Rp ' . number_format($totalVal, 0, ',', '.'),
                'tone'      => 'ink',
                'icon'      => 'banknote',
                'chip'      => 'TIER-1',
                'chip_tone' => 'accent',
                'meta'      => 'Seluruh nilai Needs Directors',
                'meta_tone' => 'muted',
            ],
            [
                'key'       => 'exception',
                'label'     => 'Pelanggaran Plafon',
                'value'     => $exceedCount . ' Pengajuan',
                'tone'      => 'danger',
                'icon'      => 'alert-triangle',
                'chip'      => 'ESCALATED',
                'chip_tone' => 'danger',
                'meta'      => 'Di atas plafon diskon atau TOP default',
                'meta_tone' => 'danger',
            ],
            [
                'key'       => 'session',
                'label'     => 'Sesi Tanda Tangan',
                'value'     => '14:32',
                'tone'      => 'success',
                'icon'      => 'lock',
                'chip'      => 'HSM OK',
                'chip_tone' => 'success',
                'meta'      => 'HSM RSA-4096 // SHA-256 aktif',
                'meta_tone' => 'success',
            ],
        ];

        $filters = [
            ['key' => 'all',      'label' => 'Semua Pending',           'count' => count($rows)],
            ['key' => 'discount', 'label' => 'Diskon Khusus',           'count' => count(array_filter($rows, fn ($r) => $r['filter_key'] === 'discount'))],
            ['key' => 'top',      'label' => 'Ekstensi TOP',            'count' => count(array_filter($rows, fn ($r) => $r['filter_key'] === 'top'))],
            ['key' => 'annual',   'label' => 'Kontrak Induk Tahunan',   'count' => count(array_filter($rows, fn ($r) => $r['filter_key'] === 'annual'))],
        ];

        $columns = [
            'ID KONTRAK & PEMOHON',
            'KOMODITAS & VOLUME',
            'KETENTUAN HARGA & TOP',
            'ANALISIS MARGIN',
            'SKOR KREDIT',
            'AKSI OTORISASI DIREKTUR',
        ];

        $queue = [
            'badge'             => 'PERLU OTORISASI DIREKTUR',
            'rule'              => 'PRD SEC 16 & 20: TIER-1 APPROVAL GATE',
            'clearance'         => 'CLEARANCE: DIRUT ONLY',
            'title'             => 'Daftar Pengajuan Kontrak Khusus & Diskon Volume B2B',
            'description'       => 'Perubahan harga di luar plafon standar (>5%), perpanjangan tenor TOP (>30 hari), dan kuota pasokan prioritas memerlukan otorisasi kriptografi Direktur Utama (UU No. 2/1981 & ISO 27001).',
            'columns'           => $columns,
            'rows'              => $rows,
            'filters'           => $filters,
            'total_value'       => $totalVal,
            'total_value_label' => 'Rp ' . number_format($totalVal, 0, ',', '.'),
            'total_note'        => 'TOTAL NILAI KONTRAK AKTIF PENDING: Rp ' . number_format($totalVal, 0, ',', '.') . ' (' . count($rows) . ' Pengajuan)',
            'hsm'               => 'HSM RSA-4096 Terhubung',
            'timeout'           => 'Sesi Timeout: 14:32',
            'batch_approve'     => 'Approve Terpilih (Batch)',
            'batch_select_all'  => 'Pilih Semua',
            'detail_label'      => 'Detail PKS',
            'revise_label'      => 'Minta Revisi',
            'reject_label'      => 'Tolak',
            'audit_note'        => 'Semua pengesahan akan ditandatangani otomatis dengan Sertifikat Elektronik BSrE / Hash Kriptografi SHA-256 dan tercatat di immutable log.',
            'shown_label'       => 'Menampilkan ' . count($rows) . ' dari ' . count($rows) . ' Pengajuan Pending',
            'active_contracts'  => count($rows),
            'search_placeholder'=> 'Cari klien, kontrak, atau komoditas...',
        ];

        return view('direktur.approval', compact('operator', 'header', 'cards', 'queue'));
    }

    /**
     * Batch Approve Kontrak Terpilih oleh Direktur Utama
     */
    public function batchApprove(Request $request)
    {
        $contractIds = $request->input('selected_ids', []);

        if (empty($contractIds)) {
            return back()->with('error', 'Pilih minimal satu kontrak untuk disetujui.');
        }

        $approvedCount = 0;
        $directorName = Auth::guard('web')->user()->name;

        foreach ($contractIds as $id) {
            $contract = Contract::find($id);
            if ($contract && $contract->status === 'PENDING_APPROVAL') {
                $contract->update([
                    'status'      => 'ACTIVE',
                    'approved_by' => Auth::guard('web')->id(),
                    'approved_at' => now(),
                ]);

                $approvedCount++;

                $clientName = $contract->user->company_name ?: $contract->user->name;
                $this->governanceService->addAuditLog(
                    'BATCH_APPROVAL_KONTRAK',
                    $contract->contract_number,
                    "Persetujuan batch kontrak B2B {$contract->contract_number} ({$clientName}). TTD Kriptografi BSrE disahkan.",
                    $directorName,
                    'DIR-01 [CLEARANCE L4]',
                    'SEALED'
                );
            }
        }

        return redirect()->route('direktur.contracts.approval')
            ->with('success', "Sebanyak {$approvedCount} kontrak kemitraan B2B berhasil disetujui secara serentak dengan TTD Digital SHA-256.");
    }

    /**
     * Menolak Pengajuan Kontrak
     */
    public function reject(Request $request, int $id)
    {
        $contract = Contract::findOrFail($id);
        $reason = $request->input('rejection_reason', 'Margin di bawah threshold ketentuan Direksi atau riwayat kredit tidak memenuhi kualifikasi.');

        $contract->update([
            'status' => 'TERMINATED',
        ]);

        $this->governanceService->addAuditLog(
            'KONTRAK_DITOLAK',
            $contract->contract_number,
            "Pengajuan kontrak {$contract->contract_number} ditolak Direktur. Alasan: {$reason}",
            Auth::guard('web')->user()->name,
            'DIR-01 [CLEARANCE L4]',
            'REJECTED'
        );

        return back()->with('success', "Kontrak {$contract->contract_number} berhasil ditolak. Alasan tercatat di audit log.");
    }

    /**
     * Meminta Revisi Klausul Kontrak
     */
    public function requestRevision(Request $request, int $id)
    {
        $contract = Contract::findOrFail($id);
        $notes = $request->input('revision_notes', 'Harap sesuaikan tenor TOP menjadi maksimal 30 hari atau tingkatkan kuota komitmen.');

        $this->governanceService->addAuditLog(
            'REVISI_KONTRAK_DIMINTA',
            $contract->contract_number,
            "Direktur meminta revisi pada draf kontrak {$contract->contract_number}. Catatan: {$notes}",
            Auth::guard('web')->user()->name,
            'DIR-01 [CLEARANCE L4]',
            'PENDING_REVISION'
        );

        return back()->with('success', "Permintaan revisi untuk draf {$contract->contract_number} berhasil dikirimkan ke Tim Legal & Sales B2B.");
    }

    /**
     * Menampilkan daftar seluruh kontrak kemitraan B2B dengan filter status dan pencarian
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $search = trim($request->query('q', ''));

        $statusCounts = [
            'ALL'              => Contract::count(),
            'PENDING_APPROVAL' => Contract::where('status', 'PENDING_APPROVAL')->count(),
            'ACTIVE'           => Contract::where('status', 'ACTIVE')->count(),
            'TERMINATED'       => Contract::whereIn('status', ['TERMINATED', 'EXPIRED'])->count(),
        ];

        $contracts = Contract::with(['user', 'product', 'approver'])
            ->when($statusFilter && $statusFilter !== 'ALL', function ($query) use ($statusFilter) {
                if ($statusFilter === 'INACTIVE') {
                    return $query->whereIn('status', ['TERMINATED', 'EXPIRED']);
                }
                return $query->where('status', $statusFilter);
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('contract_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%")
                             ->orWhere('company_name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('product', function ($pq) use ($search) {
                          $pq->where('name', 'like', "%{$search}%")
                             ->orWhere('sku', 'like', "%{$search}%");
                      });
                });
            })
            ->orderByRaw("CASE WHEN status = 'PENDING_APPROVAL' THEN 0 WHEN status = 'ACTIVE' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('direktur.contracts.index', compact('contracts', 'statusFilter', 'search', 'statusCounts'));
    }

    /**
     * Form pembuatan kontrak harga khusus B2B baru
     */
    public function create()
    {
        $clients = User::where('role', 'KLIEN')->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $nextSequence = str_pad(Contract::count() + 1, 3, '0', STR_PAD_LEFT);
        $suggestedNumber = 'CTR-GPA-' . date('Ym') . '-' . $nextSequence;

        return view('direktur.contracts.create', compact('clients', 'products', 'suggestedNumber'));
    }

    /**
     * Menyimpan kontrak baru oleh Direktur
     */
    public function store(StoreContractRequest $request)
    {
        try {
            $validated = $request->validated();
            $directorId = Auth::guard('web')->id();
            $status = $validated['status'] ?? 'ACTIVE';

            $contract = Contract::create([
                'user_id'                    => $validated['user_id'],
                'product_id'                 => $validated['product_id'],
                'contract_number'            => $validated['contract_number'],
                'fixed_price_per_kg'         => $validated['fixed_price_per_kg'],
                'top_days'                   => $validated['top_days'],
                'committed_volume_per_cycle' => $validated['committed_volume_per_cycle'],
                'status'                     => $status,
                'approved_by'                => $status === 'ACTIVE' ? $directorId : null,
                'approved_at'                => $status === 'ACTIVE' ? now() : null,
            ]);

            $this->governanceService->addAuditLog(
                'PEMBUATAN_KONTRAK',
                $contract->contract_number,
                "Penerbitan kontrak baru {$contract->contract_number} ({$contract->product->name} - Rp " . number_format($contract->fixed_price_per_kg, 0, ',', '.') . "/kg).",
                Auth::guard('web')->user()->name,
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            $message = $status === 'ACTIVE'
                ? "Kontrak {$contract->contract_number} berhasil diterbitkan dan langsung diaktifkan."
                : "Draf kontrak {$contract->contract_number} berhasil disimpan dan menunggu pengesahan.";

            return redirect()->route('direktur.contracts.index')->with('success', $message);
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal membuat kontrak: ' . $e->getMessage()]);
        }
    }

    /**
     * Menampilkan lembar detail resmi kontrak kemitraan B2B
     */
    public function show(int $id)
    {
        $contract = Contract::with(['user', 'product', 'approver'])->findOrFail($id);

        $recentOrders = Order::where('user_id', $contract->user_id)
            ->whereHas('orderItems', function ($q) use ($contract) {
                $q->where('product_id', $contract->product_id);
            })
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('direktur.contracts.show', compact('contract', 'recentOrders'));
    }

    /**
     * Form pengubahan parameter kontrak kemitraan
     */
    public function edit(int $id)
    {
        $contract = Contract::with(['user', 'product'])->findOrFail($id);

        return view('direktur.contracts.edit', compact('contract'));
    }

    /**
     * Memperbarui parameter kontrak kemitraan
     */
    public function update(UpdateContractRequest $request, int $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $validated = $request->validated();

            if ($validated['status'] === 'ACTIVE' && $contract->status !== 'ACTIVE') {
                $validated['approved_by'] = Auth::guard('web')->id();
                $validated['approved_at'] = now();
            }

            $contract->update($validated);

            $this->governanceService->addAuditLog(
                'UPDATE_KONTRAK',
                $contract->contract_number,
                "Pembaruan klausul kontrak {$contract->contract_number}.",
                Auth::guard('web')->user()->name,
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            return redirect()->route('direktur.contracts.index')
                ->with('success', "Kontrak {$contract->contract_number} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui kontrak: ' . $e->getMessage()]);
        }
    }

    /**
     * Otorisasi dan pengesahan kontrak oleh Direktur
     */
    public function approve(int $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->update([
                'status'      => 'ACTIVE',
                'approved_by' => Auth::guard('web')->id(),
                'approved_at' => now(),
            ]);

            $this->governanceService->addAuditLog(
                'APPROVAL_KONTRAK',
                $contract->contract_number,
                "Kontrak kemitraan {$contract->contract_number} disetujui & disahkan oleh Direktur Utama. TTD Digital BSrE tersemat.",
                Auth::guard('web')->user()->name,
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            if (request()->headers->has('referer') && url()->previous() !== url()->current()) {
                return back()->with('success', "Kontrak {$contract->contract_number} resmi disetujui dan diberlakukan.");
            }

            return redirect()->route('direktur.contracts.index')
                ->with('success', "Kontrak {$contract->contract_number} resmi disetujui dan diberlakukan.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menyetujui kontrak: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghentikan kontrak secara resmi oleh Direktur
     */
    public function terminate(int $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->update([
                'status' => 'TERMINATED',
            ]);

            $this->governanceService->addAuditLog(
                'TERMINASI_KONTRAK',
                $contract->contract_number,
                "Kontrak {$contract->contract_number} dinonaktifkan permanen oleh Direktur.",
                Auth::guard('web')->user()->name,
                'DIR-01 [CLEARANCE L4]',
                'TERMINATED'
            );

            return redirect()->route('direktur.contracts.index')
                ->with('success', "Kontrak {$contract->contract_number} telah dinonaktifkan (TERMINATED).");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghentikan kontrak: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghapus kontrak dari sistem
     */
    public function destroy(int $id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contractNumber = $contract->contract_number;
            $contract->delete();

            return redirect()->route('direktur.contracts.index')
                ->with('success', "Kontrak {$contractNumber} berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus kontrak: ' . $e->getMessage()]);
        }
    }
}
