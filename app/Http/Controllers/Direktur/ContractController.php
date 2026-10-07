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
        $typeFilter = $request->query('type', 'ALL'); // ALL, DISCOUNT, EXTENSION, ANNUAL

        $query = Contract::where('status', 'PENDING_APPROVAL')->with(['user', 'product']);

        if ($typeFilter === 'DISCOUNT') {
            $query->where('fixed_price_per_kg', '<', 14000);
        } elseif ($typeFilter === 'EXTENSION') {
            $query->where('top_days', '>', 30);
        } elseif ($typeFilter === 'ANNUAL') {
            $query->where('committed_volume_per_cycle', '>=', 4000);
        }

        $pendingContracts = $query->orderBy('created_at', 'desc')->get();

        $counts = [
            'ALL'       => Contract::where('status', 'PENDING_APPROVAL')->count(),
            'DISCOUNT'  => Contract::where('status', 'PENDING_APPROVAL')->where('fixed_price_per_kg', '<', 14000)->count(),
            'EXTENSION' => Contract::where('status', 'PENDING_APPROVAL')->where('top_days', '>', 30)->count(),
            'ANNUAL'    => Contract::where('status', 'PENDING_APPROVAL')->where('committed_volume_per_cycle', '>=', 4000)->count(),
        ];

        $totalPendingValue = (float) $pendingContracts->sum(fn ($c) => (float) ($c->fixed_price_per_kg * $c->committed_volume_per_cycle));

        return view('direktur.contracts.approval', compact('pendingContracts', 'typeFilter', 'counts', 'totalPendingValue'));
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
