<?php

namespace App\Services;

use App\Models\HarvestBatch;
use App\Models\Product;
use App\Services\GovernanceService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        protected GovernanceService $governanceService,
        protected StockService $stockService
    ) {}

    /**
     * Mengambil daftar komoditas beserta metrik katalog dengan filter dinamis.
     */
    public function getCatalogData(Request $request): array
    {
        $query = Product::with(['harvestBatches', 'contracts'])->orderBy('id', 'asc');

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
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
        $avgPrice = (float) (Product::where('is_active', true)->avg('base_price') ?: 0);
        $totalStockKg = (float) HarvestBatch::sum('available_quantity');

        return compact(
            'products',
            'totalProducts',
            'activeProducts',
            'avgPrice',
            'totalStockKg'
        );
    }

    /**
     * Menghasilkan saran SKU komoditas berikutnya secara otomatis.
     */
    public function getSuggestedSku(): string
    {
        $nextId = (Product::max('id') ?: 0) + 1;
        return 'GPA-VGT-' . str_pad((string) $nextId, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Menyimpan komoditas baru ke basis data dan mencatat audit trail.
     */
    public function storeProduct(array $validated, ?UploadedFile $imageFile, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $imageUrl = null;

        if ($imageFile) {
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $path = $imageFile->storeAs('products', $filename, 'public');
            $imageUrl = 'storage/' . $path;
        } elseif (!empty($validated['preset_image'])) {
            $imageUrl = $validated['preset_image'];
        }

        $product = Product::create([
            'sku'           => strtoupper(trim($validated['sku'])),
            'name'          => trim($validated['name']),
            'slug'          => Str::slug(trim($validated['name'])),
            'unit'          => strtolower(trim($validated['unit'])),
            'grade'         => trim($validated['grade']),
            'base_price'    => $validated['base_price'],
            'minimum_order' => $validated['minimum_order'],
            'image_url'     => $imageUrl,
            'description'   => $validated['description'] ?? null,
            'is_active'     => (bool) $validated['is_active'],
        ]);

        $logCategory = ($actorRole === 'KOORDINATOR') ? 'COMMODITY_CREATED_BY_KOORDINATOR' : 'COMMODITY_CATALOG_CREATED';
        $logMessage = ($actorRole === 'KOORDINATOR')
            ? "Koordinator Lapangan ({$actorName}) menambahkan komoditas baru '{$product->name}' (SKU: {$product->sku}) ke master produk."
            : "Penambahan komoditas baru '{$product->name}' (SKU: {$product->sku}, Grade: {$product->grade}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") ke katalog resmi oleh {$actorName}.";

        $this->governanceService->addAuditLog(
            $logCategory,
            'PRD-' . $product->id,
            $logMessage,
            $actorName,
            $clearance,
            'SEALED'
        );

        return $product;
    }

    /**
     * Mengambil detail komoditas beserta stok riil untuk halaman rincian.
     */
    public function getProductDetailData(Product $product): array
    {
        $product->load(['harvestBatches.inputter', 'contracts.user']);
        $availableStock = (float) $product->harvestBatches->sum('available_quantity');
        $totalHarvested = (float) $product->harvestBatches->sum('initial_quantity');

        return compact('product', 'availableStock', 'totalHarvested');
    }

    /**
     * Memperbarui komoditas dan mencatat audit trail.
     */
    public function updateProduct(Product $product, array $validated, ?UploadedFile $imageFile, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $imageUrl = $product->image_url;

        if ($imageFile) {
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $path = $imageFile->storeAs('products', $filename, 'public');
            $imageUrl = 'storage/' . $path;
        } elseif (!empty($validated['preset_image'])) {
            $imageUrl = $validated['preset_image'];
        }

        $product->update([
            'sku'           => strtoupper(trim($validated['sku'])),
            'name'          => trim($validated['name']),
            'slug'          => Str::slug(trim($validated['name'])),
            'unit'          => strtolower(trim($validated['unit'])),
            'grade'         => trim($validated['grade']),
            'base_price'    => $validated['base_price'],
            'minimum_order' => $validated['minimum_order'],
            'image_url'     => $imageUrl,
            'description'   => $validated['description'] ?? null,
            'is_active'     => (bool) $validated['is_active'],
        ]);

        $logCategory = ($actorRole === 'KOORDINATOR') ? 'COMMODITY_UPDATED_BY_KOORDINATOR' : 'COMMODITY_CATALOG_UPDATED';
        $this->governanceService->addAuditLog(
            $logCategory,
            'PRD-' . $product->id,
            "Pembaruan data komoditas '{$product->name}' (SKU: {$product->sku}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") oleh {$actorName}.",
            $actorName,
            $clearance,
            'SEALED'
        );

        return $product;
    }

    /**
     * Mengubah status aktif/non-aktif komoditas.
     */
    public function toggleStatus(Product $product, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): bool
    {
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->governanceService->addAuditLog(
            'COMMODITY_STATUS_TOGGLED',
            'PRD-' . $product->id,
            "Komoditas {$product->name} {$statusText} oleh {$actorName}.",
            $actorName,
            $clearance,
            'SEALED'
        );

        return $newStatus;
    }

    /**
     * Menghapus komoditas atau menonaktifkannya jika sudah memiliki riwayat relasi.
     *
     * @return array{is_deleted: bool, message: string}
     */
    public function deleteProduct(Product $product, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): array
    {
        $hasBatches = $product->harvestBatches()->exists();
        $hasOrderItems = $product->orderItems()->exists();
        $hasContracts = $product->contracts()->exists();
        $name = $product->name;

        if ($hasBatches || $hasOrderItems || $hasContracts) {
            $product->update(['is_active' => false]);

            $this->governanceService->addAuditLog(
                'COMMODITY_DEACTIVATED',
                'PRD-' . $product->id,
                "Komoditas '{$name}' dinonaktifkan dari katalog B2B karena memiliki riwayat panen/pesanan/kontrak.",
                $actorName,
                $clearance,
                'SEALED'
            );

            return [
                'is_deleted' => false,
                'message'    => "Komoditas {$name} memiliki riwayat transaksi/kontrak, status berhasil diubah menjadi NON-AKTIF untuk menjaga integritas data.",
            ];
        }

        $product->delete();

        $this->governanceService->addAuditLog(
            'COMMODITY_DELETED',
            'PRD-' . $product->id,
            "Penghapusan permanen komoditas '{$name}' dari basis data oleh {$actorName}.",
            $actorName,
            $clearance,
            'SEALED'
        );

        return [
            'is_deleted' => true,
            'message'    => "Komoditas {$name} berhasil dihapus dari sistem.",
        ];
    }
}
