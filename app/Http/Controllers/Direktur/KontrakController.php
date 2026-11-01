<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Requests\Direktur\SimpanKontrakRequest;
use App\Http\Requests\Direktur\PerbaruiKontrakRequest;
use App\Http\Resources\Direktur\PersetujuanKontrakResource;
use App\Models\Contract;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\TataKelolaService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller otorisasi dan manajemen kontrak kemitraan B2B untuk Direktur Utama
class KontrakController extends Controller
{
    public function __construct(
        protected TataKelolaService $tataKelolaService
    ) {}

    // Mengambil antrean kontrak kemitraan B2B yang menunggu verifikasi dan evaluasi kelayakan margin
    public function approval(Request $request)
    {
        $pendingContracts = Contract::whereIn('status', ['PENDING', 'PENDING_APPROVAL'])
            ->with(['user', 'product'])
            ->get();

        $settings = $this->tataKelolaService->getSettings();

        $rows = $pendingContracts->map(
            fn (Contract $contract) => (new PersetujuanKontrakResource($contract, $settings))->resolve()
        )->all();

        return view('direktur.kontrak.approval', compact('rows'));
    }

    // Melakukan pengesahan serentak (batch approval) kontrak terpilih dan otomatis mengaktifkan status kemitraan klien
    public function batchApprove(Request $request)
    {
        $contractIds = $request->input('selected_ids', []);

        if (empty($contractIds)) {
            return back()->with('error', 'Pilih minimal satu kontrak untuk disetujui.');
        }

        $approvedCount = 0;
        $directorName = Auth::guard('web')->user()?->name ?: 'Direktur Utama';

        foreach ($contractIds as $contractId) {
            $contract = Contract::find($contractId);
            if ($contract && in_array($contract->status, ['PENDING', 'PENDING_APPROVAL'], true)) {
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
                $this->tataKelolaService->addAuditLog(
                    'BATCH_APPROVAL_KONTRAK',
                    $contract->contract_number,
                    "Persetujuan kontrak kemitraan B2B {$contract->contract_number} ({$clientName}) disahkan oleh Direktur Utama.",
                    $directorName,
                    'DIR-01',
                    'APPROVED'
                );
            }
        }

        $message = "Sebanyak {$approvedCount} kontrak kemitraan B2B berhasil disetujui secara serentak.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message, 'count' => $approvedCount]);
        }

        return redirect()->route('direktur.kontrak.persetujuan')->with('success', $message);
    }

    // Menolak pengajuan draf kontrak kemitraan yang dinilai tidak memenuhi batas margin atau skor kredit
    public function reject(Request $request, Contract $contract)
    {
        $reason = $request->input('rejection_reason', 'Margin di bawah threshold ketentuan Direksi atau riwayat kredit tidak memenuhi kualifikasi.');

        $contract->update([
            'status' => 'TERMINATED',
        ]);

        $this->tataKelolaService->addAuditLog(
            'KONTRAK_DITOLAK',
            $contract->contract_number,
            "Pengajuan kontrak {$contract->contract_number} ditolak Direktur. Alasan: {$reason}",
            Auth::guard('web')->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'REJECTED'
        );

        $message = "Kontrak {$contract->contract_number} berhasil ditolak. Alasan tercatat di audit log.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // Mengembalikan draf kontrak ke tim legal untuk penyesuaian klausul termin pembayaran atau volume komitmen
    public function requestRevision(Request $request, Contract $contract)
    {
        $notes = $request->input('revision_notes', 'Harap sesuaikan tenor TOP menjadi maksimal 30 hari atau tingkatkan kuota komitmen.');

        $this->tataKelolaService->addAuditLog(
            'REVISI_KONTRAK_DIMINTA',
            $contract->contract_number,
            "Direktur meminta revisi pada draf kontrak {$contract->contract_number}. Catatan: {$notes}",
            Auth::guard('web')->user()?->name ?: 'Direktur Utama',
            'DIR-01 [CLEARANCE L4]',
            'PENDING_REVISION'
        );

        $message = "Permintaan revisi untuk draf {$contract->contract_number} berhasil dikirimkan ke Tim Legal & Sales B2B.";
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // Menampilkan seluruh master kontrak kemitraan B2B dengan filter status operasional dan pencarian kata kunci
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
                return $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('contract_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByRaw("CASE WHEN status = 'PENDING_APPROVAL' THEN 0 WHEN status = 'ACTIVE' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('direktur.kontrak.index', compact('contracts', 'statusFilter', 'search', 'statusCounts'));
    }

    // Menyiapkan formulir pembuatan draf kontrak baru dengan nomor kontrak otomatis yang terstandarisasi
    public function create()
    {
        $clients = User::where('role', 'KLIEN')->where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        $nextSequence = str_pad((string) (Contract::count() + 1), 3, '0', STR_PAD_LEFT);
        $suggestedNumber = 'CTR-GPA-' . date('Ym') . '-' . $nextSequence;

        return view('direktur.kontrak.create', compact('clients', 'products', 'suggestedNumber'));
    }

    // Menerbitkan kontrak kemitraan baru dan mencatat parameter harga tetap ke log audit keamanan
    public function store(SimpanKontrakRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $directorId = Auth::guard('web')->id();
            $status = $validatedData['status'] ?? 'ACTIVE';

            $contract = Contract::create([
                'user_id'                    => $validatedData['user_id'],
                'product_id'                 => $validatedData['product_id'],
                'contract_number'            => $validatedData['contract_number'],
                'fixed_price_per_kg'         => $validatedData['fixed_price_per_kg'],
                'top_days'                   => $validatedData['top_days'],
                'committed_volume_per_cycle' => $validatedData['committed_volume_per_cycle'],
                'status'                     => $status,
                'approved_by'                => $status === 'ACTIVE' ? $directorId : null,
                'approved_at'                => $status === 'ACTIVE' ? now() : null,
            ]);

            if ($status === 'ACTIVE') {
                $clientUser = User::find($validatedData['user_id']);
                if ($clientUser && $clientUser->client_type !== 'B2B_KONTRAK') {
                    $clientUser->update(['client_type' => 'B2B_KONTRAK']);
                }
            }

            $this->tataKelolaService->addAuditLog(
                'PEMBUATAN_KONTRAK',
                $contract->contract_number,
                "Penerbitan kontrak baru {$contract->contract_number} ({$contract->product->name} - Rp " . number_format($contract->fixed_price_per_kg, 0, ',', '.') . "/kg).",
                Auth::guard('web')->user()?->name ?: 'Direktur Utama',
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            $message = $status === 'ACTIVE'
                ? "Kontrak {$contract->contract_number} berhasil diterbitkan dan langsung diaktifkan."
                : "Draf kontrak {$contract->contract_number} berhasil disimpan dan menunggu pengesahan.";

            return redirect()->route('direktur.kontrak.index')->with('success', $message);
        } catch (Exception $exception) {
            return back()->withInput()->withErrors(['error' => 'Gagal membuat kontrak: ' . $exception->getMessage()]);
        }
    }

    // Menampilkan lembar detail resmi klausul kontrak beserta riwayat transaksi pesanan terkait
    public function show(Contract $contract)
    {
        $contract->loadMissing(['user', 'product', 'approver']);

        $recentOrders = Order::where('user_id', $contract->user_id)
            ->whereHas('orderItems', function ($query) use ($contract) {
                $query->where('product_id', $contract->product_id);
            })
            ->with(['orderItems.product'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('direktur.kontrak.show', compact('contract', 'recentOrders'));
    }

    // Menampilkan formulir penyuntingan klausul kontrak seperti harga kesepakatan dan termin pembayaran
    public function edit(Contract $contract)
    {
        $contract->loadMissing(['user', 'product']);

        return view('direktur.kontrak.edit', compact('contract'));
    }

    // Memperbarui parameter klausul kontrak kemitraan dan mencatat pengesahan jika status berubah aktif
    public function update(PerbaruiKontrakRequest $request, Contract $contract)
    {
        try {
            $validatedData = $request->validated();

            if ($validatedData['status'] === 'ACTIVE' && $contract->status !== 'ACTIVE') {
                $validatedData['approved_by'] = Auth::guard('web')->id();
                $validatedData['approved_at'] = now();
            }

            $contract->update($validatedData);

            $this->tataKelolaService->addAuditLog(
                'UPDATE_KONTRAK',
                $contract->contract_number,
                "Pembaruan klausul kontrak {$contract->contract_number}.",
                Auth::guard('web')->user()?->name ?: 'Direktur Utama',
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            return redirect()->route('direktur.kontrak.index')
                ->with('success', "Kontrak {$contract->contract_number} berhasil diperbarui.");
        } catch (Exception $exception) {
            return back()->withInput()->withErrors(['error' => 'Gagal memperbarui kontrak: ' . $exception->getMessage()]);
        }
    }

    // Otorisasi dan pengesahan kontrak individual oleh Direktur Utama
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

            $this->tataKelolaService->addAuditLog(
                'APPROVAL_KONTRAK',
                $contract->contract_number,
                "Kontrak kemitraan {$contract->contract_number} disetujui & disahkan oleh Direktur Utama.",
                Auth::guard('web')->user()?->name ?: 'Direktur Utama',
                'DIR-01 [CLEARANCE L4]',
                'SEALED'
            );

            $message = "Kontrak {$contract->contract_number} resmi disetujui dan diberlakukan.";
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            if (request()->headers->has('referer') && url()->previous() !== url()->current()) {
                return back()->with('success', $message);
            }

            return redirect()->route('direktur.kontrak.index')->with('success', $message);
        } catch (Exception $exception) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
            }
            return back()->withErrors(['error' => 'Gagal menyetujui kontrak: ' . $exception->getMessage()]);
        }
    }

    // Menghentikan masa berlaku kontrak secara permanen (TERMINATED) melalui wewenang Direktur
    public function terminate(Contract $contract)
    {
        try {
            $contract->update([
                'status' => 'TERMINATED',
            ]);

            $this->tataKelolaService->addAuditLog(
                'TERMINASI_KONTRAK',
                $contract->contract_number,
                "Kontrak {$contract->contract_number} dinonaktifkan permanen oleh Direktur.",
                Auth::guard('web')->user()?->name ?: 'Direktur Utama',
                'DIR-01 [CLEARANCE L4]',
                'TERMINATED'
            );

            return redirect()->route('direktur.kontrak.index')
                ->with('success', "Kontrak {$contract->contract_number} telah dinonaktifkan (TERMINATED).");
        } catch (Exception $exception) {
            return back()->withErrors(['error' => 'Gagal menghentikan kontrak: ' . $exception->getMessage()]);
        }
    }

    // Menghapus data kontrak kemitraan dari basis data jika draf tidak lagi digunakan
    public function destroy(Contract $contract)
    {
        try {
            $contractNumber = $contract->contract_number;
            $contract->delete();

            return redirect()->route('direktur.kontrak.index')
                ->with('success', "Kontrak {$contractNumber} berhasil dihapus dari sistem.");
        } catch (Exception $exception) {
            return back()->withErrors(['error' => 'Gagal menghapus kontrak: ' . $exception->getMessage()]);
        }
    }
}
