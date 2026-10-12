<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Product;
use App\Services\GovernanceService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Exception;

class ProductController extends Controller
{
    protected GovernanceService $governanceService;
    protected StockService $stockService;

    public function __construct(GovernanceService $governanceService, StockService $stockService)
    {
        $this->governanceService = $governanceService;
        $this->stockService = $stockService;
    }

    /**
     * Tampilkan seluruh katalog komoditas & manajemen stok metrologi GPA
     */
    public function index(Request $request)
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

        // Query produk dengan filter
        $query = Product::with(['harvestBatches', 'contracts'])->orderBy('id', 'asc');

        if ($request->filled('search')) {
            $s = $request->query('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('sku', 'like', "%{$s}%")
                  ->orWhere('grade', 'like', "%{$s}%");
            });
        }

        if ($request->filled('grade') && $request->grade !== 'all') {
            $query->where('grade', $request->grade);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === '1' || $request->status === 'active');
        }

        $products = $query->get();
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $avgPrice = Product::where('is_active', true)->avg('base_price') ?: 0;

        // Hitung total stok fisik seluruh komoditas
        $totalStockKg = (float) HarvestBatch::sum('available_quantity');

        $metrics = [
            [
                'label'     => 'Total Komoditas',
                'value'     => $totalProducts . ' Komoditas',
                'note'      => $activeProducts . ' Aktif di Katalog B2B',
                'icon'      => 'grid',
                'tone'      => 'brand',
            ],
            [
                'label'     => 'Ketersediaan Pasokan',
                'value'     => number_format($totalStockKg, 0, ',', '.') . ' kg',
                'note'      => 'Buffer & Petani Binaan Siap Kirim',
                'icon'      => 'package',
                'tone'      => 'success',
            ],
            [
                'label'     => 'Rata-rata Harga Dasar',
                'value'     => 'Rp ' . number_format($avgPrice, 0, ',', '.'),
                'note'      => 'Harga Franco Gudang Ciwidey/Lembang',
                'icon'      => 'banknote',
                'tone'      => 'accent',
            ],
            [
                'label'     => 'Standar Mutu & Metrologi',
                'value'     => 'Grade A & Super',
                'note'      => 'Sertifikasi Tera Bebas Residu',
                'icon'      => 'shield',
                'tone'      => 'dark',
            ],
        ];

        return view('direktur.products.index', compact(
            'operator',
            'products',
            'metrics',
            'totalProducts',
            'activeProducts'
        ));
    }

    /**
     * Formulir penambahan komoditas baru ke katalog
     */
    public function create()
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $user?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($user?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($user?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $user?->email ?: 'direktur@greenpasundan.id',
        ];

        // Hitung perkiraan SKU berikutnya
        $nextId = (Product::max('id') ?: 0) + 1;
        $suggestedSku = 'GPA-VGT-' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

        $presets = [
            ['path' => 'lettuce.jpg', 'label' => 'Selada Romaine / Daun Hidroponik'],
            ['path' => 'broccoli.jpg', 'label' => 'Brokoli Super Lembang'],
            ['path' => 'tomato.jpg', 'label' => 'Tomat Beef Greenhouse Garut'],
            ['path' => 'strawberry.jpg', 'label' => 'Stroberi Sweet Ciwidey'],
            ['path' => 'cabbage.jpg', 'label' => 'Kol Putih Pangalengan'],
            ['path' => 'gpaleaves.png', 'label' => 'Logo Daun Resmi GPA'],
        ];

        return view('direktur.products.create', compact('operator', 'suggestedSku', 'presets'));
    }

    /**
     * Simpan komoditas baru ke basis data
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'           => ['required', 'string', 'max:30', 'unique:products,sku'],
            'name'          => ['required', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:20'],
            'grade'         => ['required', 'string', 'max:20'],
            'base_price'    => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0.1'],
            'image_file'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'preset_image'  => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'is_active'     => ['required'],
        ]);

        try {
            $imageUrl = null;

            // Handle upload gambar jika ada file
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
                $imageUrl = 'storage/' . $path;
            } elseif (!empty($validated['preset_image'])) {
                $imageUrl = $validated['preset_image'];
            }

            $product = Product::create([
                'sku'           => strtoupper(trim($validated['sku'])),
                'name'          => trim($validated['name']),
                'unit'          => strtolower(trim($validated['unit'])),
                'grade'         => trim($validated['grade']),
                'base_price'    => $validated['base_price'],
                'minimum_order' => $validated['minimum_order'],
                'image_url'     => $imageUrl,
                'description'   => $validated['description'] ?? null,
                'is_active'     => (bool) $validated['is_active'],
            ]);

            $this->governanceService->addAuditLog(
                'COMMODITY_CATALOG_CREATED',
                'PRD-' . $product->id,
                "Penambahan komoditas baru '{$product->name}' (SKU: {$product->sku}, Grade: {$product->grade}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") ke katalog resmi oleh Direktur Utama.",
                Auth::user()?->name ?: 'Direktur Utama',
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.products.index')
                ->with('success', "Komoditas {$product->name} berhasil ditambahkan ke katalog resmi GPA.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail rincian komoditas, stok riil panen & kontrak terikat
     */
    public function show(Product $product)
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $user?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($user?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($user?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $user?->email ?: 'direktur@greenpasundan.id',
        ];

        $product->load(['harvestBatches', 'contracts.user']);
        $availableStock = (float) $product->harvestBatches->sum('available_quantity');
        $totalHarvested = (float) $product->harvestBatches->sum('initial_quantity');

        return view('direktur.products.show', compact('operator', 'product', 'availableStock', 'totalHarvested'));
    }

    /**
     * Formulir penyuntingan komoditas
     */
    public function edit(Product $product)
    {
        $user = Auth::user();

        $operator = [
            'role'     => 'Direktur Utama',
            'name'     => $user?->name ?: 'Direksi GPA',
            'code'     => 'DIR-' . str_pad($user?->id ?: 1, 3, '0', STR_PAD_LEFT),
            'initials' => strtoupper(substr($user?->name ?: 'DU', 0, 2)),
            'branch'   => 'Kantor Pusat GPA - Eksekutif',
            'email'    => $user?->email ?: 'direktur@greenpasundan.id',
        ];

        $presets = [
            ['path' => 'lettuce.jpg', 'label' => 'Selada Romaine / Daun Hidroponik'],
            ['path' => 'broccoli.jpg', 'label' => 'Brokoli Super Lembang'],
            ['path' => 'tomato.jpg', 'label' => 'Tomat Beef Greenhouse Garut'],
            ['path' => 'strawberry.jpg', 'label' => 'Stroberi Sweet Ciwidey'],
            ['path' => 'cabbage.jpg', 'label' => 'Kol Putih Pangalengan'],
            ['path' => 'gpaleaves.png', 'label' => 'Logo Daun Resmi GPA'],
        ];

        return view('direktur.products.edit', compact('operator', 'product', 'presets'));
    }

    /**
     * Perbarui data komoditas
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku'           => ['required', 'string', 'max:30', Rule::unique('products', 'sku')->ignore($product->id)],
            'name'          => ['required', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:20'],
            'grade'         => ['required', 'string', 'max:20'],
            'base_price'    => ['required', 'numeric', 'min:0'],
            'minimum_order' => ['required', 'numeric', 'min:0.1'],
            'image_file'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'preset_image'  => ['nullable', 'string', 'max:255'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'is_active'     => ['required'],
        ]);

        try {
            $imageUrl = $product->image_url;

            // Handle upload gambar jika ada file baru
            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
                $imageUrl = 'storage/' . $path;
            } elseif ($request->filled('preset_image')) {
                $imageUrl = $validated['preset_image'];
            }

            $product->update([
                'sku'           => strtoupper(trim($validated['sku'])),
                'name'          => trim($validated['name']),
                'unit'          => strtolower(trim($validated['unit'])),
                'grade'         => trim($validated['grade']),
                'base_price'    => $validated['base_price'],
                'minimum_order' => $validated['minimum_order'],
                'image_url'     => $imageUrl,
                'description'   => $validated['description'] ?? null,
                'is_active'     => (bool) $validated['is_active'],
            ]);

            $this->governanceService->addAuditLog(
                'COMMODITY_CATALOG_UPDATED',
                'PRD-' . $product->id,
                "Pembaruan data komoditas '{$product->name}' (SKU: {$product->sku}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") oleh Direktur Utama.",
                Auth::user()?->name ?: 'Direktur Utama',
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.products.index')
                ->with('success', "Data komoditas {$product->name} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Hapus komoditas atau nonaktifkan jika memiliki transaksi terikat
     */
    public function destroy(Product $product)
    {
        try {
            $hasBatches = $product->harvestBatches()->exists();
            $hasOrderItems = $product->orderItems()->exists();
            $hasContracts = $product->contracts()->exists();

            $name = $product->name;

            if ($hasBatches || $hasOrderItems || $hasContracts) {
                // Jangan hapus paksa agar tidak merusak relasi integritas database
                $product->update(['is_active' => false]);

                $this->governanceService->addAuditLog(
                    'COMMODITY_DEACTIVATED',
                    'PRD-' . $product->id,
                    "Komoditas '{$name}' dinonaktifkan dari katalog B2B karena memiliki riwayat panen/pesanan/kontrak.",
                    Auth::user()?->name ?: 'Direktur Utama',
                    'DIR-01 [ROOT-ACCESS]',
                    'SEALED'
                );

                return redirect()->route('direktur.products.index')
                    ->with('success', "Komoditas {$name} memiliki riwayat transaksi/kontrak, status berhasil diubah menjadi NON-AKTIF untuk menjaga integritas data.");
            }

            $product->delete();

            $this->governanceService->addAuditLog(
                'COMMODITY_DELETED',
                'PRD-' . $product->id,
                "Penghapusan permanen komoditas '{$name}' dari basis data oleh Direktur Utama.",
                Auth::user()?->name ?: 'Direktur Utama',
                'DIR-01 [ROOT-ACCESS]',
                'SEALED'
            );

            return redirect()->route('direktur.products.index')
                ->with('success', "Komoditas {$name} berhasil dihapus dari sistem.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses penghapusan: ' . $e->getMessage());
        }
    }

    /**
     * Beralih status aktif / non-aktif secara cepat
     */
    public function toggleStatus(Product $product)
    {
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->governanceService->addAuditLog(
            'COMMODITY_STATUS_TOGGLED',
            'PRD-' . $product->id,
            "Komoditas {$product->name} {$statusText} oleh Direktur Utama.",
            Auth::user()?->name ?: 'Direktur Utama',
            'DIR-01 [ROOT-ACCESS]',
            'SEALED'
        );

        return back()->with('success', "Status komoditas {$product->name} berhasil {$statusText}.");
    }
}
