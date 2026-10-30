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
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $pendingDb = Contract::whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->with(['user', 'product'])
            ->get();

        $settings = $this->governanceService->getSettings();

        $rows = $pendingDb->map(
            fn (Contract $c) => (new \App\Http\Resources\Direktur\ContractApprovalResource($c, $settings))->resolve()
        )->all();

        return view('direktur.contracts.approval', compact('rows'));
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
        $directorName = Auth::guard('web')->user()?->name ?: 'Direktur Utama';

        foreach ($contractIds as $id) {
            $contract = Contract::find($id);
            if ($contract && in_array($contract->status, ['PENDING', 'PENDING_APPROVAL'])) {
                $contract->update([
                    'status'      => 'ACTIVE',
                    'approved_by' => Auth::guard('web')->id(),
                    'approved_at' => now(),
                ]);

                if ($contract->user && $contract->user->client_type !== 'B2B_KONTRAK') {
                    $contract->user->update(['client_type' => 'B2B_KONTRAK']);
                }

                $approvedCount++;

                $clientName = $contract->user ? ($contract->user->company_name ?: $contract->user->name) : 'Klien';
                $this->governanceService->addAuditLog(
                    'BATCH_APPROVAL_KONTRAK',
                    $contract->contract_number,
                    "Persetujuan kontrak kemitraan B2B {$contract->contract_number} ({$clientName}) disahkan oleh Direktur Utama.",
                    $directorName,
                    'DIR-01',
                    'APPROVED'
                );
            }
        }

        $msg = "Sebanyak {$approvedCount} kontrak kemitraan B2B berhasil disetujui secara serentak.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg, 'count' => $approvedCount]);
        }

        return redirect()->route('direktur.contracts.approval')
            ->with('success', $msg);
    }

    /**
     * Menolak Pengajuan Kontrak
     */
    public function reject(Request $request, Contract $contract)
    {
        $reason = $request->input('rejection_reason', 'Margin di bawah threshold ketentuan Direksi atau riwayat kredit tidak memenuhi kualifikasi.');

        $contract->update([
            'status' => 'TERMINATED',
        ]);

        $this->governanceService->addAuditLog(
            'KONTRAK_DITOLAK',
            $contract->contract_number,
            "Pengajuan kontrak {$contract->contract_number} ditolak Direktur. Alasan: {$reason}",
            Auth::guard('web')->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'REJECTED'
        );

        $msg = "Kontrak {$contract->contract_number} berhasil ditolak. Alasan tercatat di audit log.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Meminta Revisi Klausul Kontrak
     */
    public function requestRevision(Request $request, Contract $contract)
    {
        $notes = $request->input('revision_notes', 'Harap sesuaikan tenor TOP menjadi maksimal 30 hari atau tingkatkan kuota komitmen.');

        $this->governanceService->addAuditLog(
            'REVISI_KONTRAK_DIMINTA',
            $contract->contract_number,
            "Direktur meminta revisi pada draf kontrak {$contract->contract_number}. Catatan: {$notes}",
            Auth::guard('web')->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'PENDING_REVISION'
        );

        $msg = "Permintaan revisi untuk draf {$contract->contract_number} berhasil dikirimkan ke Tim Legal & Sales B2B.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
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

            if ($status === 'ACTIVE') {
                $clientUser = User::find($validated['user_id']);
                if ($clientUser && $clientUser->client_type !== 'B2B_KONTRAK') {
                    $clientUser->update(['client_type' => 'B2B_KONTRAK']);
                }
            }

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
    public function show(Contract $contract)
    {
        $contract->loadMissing(['user', 'product', 'approver']);

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
    public function edit(Contract $contract)
    {
        $contract->loadMissing(['user', 'product']);

        return view('direktur.contracts.edit', compact('contract'));
    }

    /**
     * Memperbarui parameter kontrak kemitraan
     */
    public function update(UpdateContractRequest $request, Contract $contract)
    {
        try {
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
    public function approve(Contract $contract)
    {
        try {
            $contract->update([
                'status'      => 'ACTIVE',
                'approved_by' => Auth::guard('web')->id(),
                'approved_at' => now(),
            ]);

            if ($contract->user && $contract->user->client_type !== 'B2B_KONTRAK') {
                $contract->user->update(['client_type' => 'B2B_KONTRAK']);
            }

            $this->governanceService->addAuditLog(
                'APPROVAL_KONTRAK',
                $contract->contract_number,
                "Kontrak kemitraan {$contract->contract_number} disetujui & disahkan oleh Direktur Utama. TTD Digital BSrE tersemat.",
                Auth::guard('web')->user()->name,
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            $msg = "Kontrak {$contract->contract_number} resmi disetujui dan diberlakukan.";
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            if (request()->headers->has('referer') && url()->previous() !== url()->current()) {
                return back()->with('success', $msg);
            }

            return redirect()->route('direktur.contracts.index')
                ->with('success', $msg);
        } catch (Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => 'Gagal menyetujui kontrak: ' . $e->getMessage()]);
        }
    }

    /**
     * Menghentikan kontrak secara resmi oleh Direktur
     */
    public function terminate(Contract $contract)
    {
        try {
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
    public function destroy(Contract $contract)
    {
        try {
            $contractNumber = $contract->contract_number;
            $contract->delete();

            return redirect()->route('direktur.contracts.index')
                ->with('success', "Kontrak {$contractNumber} berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus kontrak: ' . $e->getMessage()]);
        }
    }
}
