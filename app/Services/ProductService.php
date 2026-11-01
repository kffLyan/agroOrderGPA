<?php

namespace App\Services;

use App\Models\HarvestBatch;
use App\Models\Product;
use App\Services\TataKelolaService;
use App\Services\StockService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

// Layanan pengelolaan katalog komoditas pertanian, penetapan SKU, stok tersedia, dan audit tata kelola.
class ProductService
{
    public function __construct(
        protected TataKelolaService $tataKelolaService,
        protected StockService $stockService
    ) {}

    // Mengambil data katalog komoditas beserta agregasi statistik dan filter pencarian.
    public function getCatalogData(Request $request): array
    {
        $query = Product::with(['harvestBatches', 'contracts'])->orderBy('id', 'asc');

        if ($request->filled('search')) {
            $searchTerm = trim($request->query('search'));
            $query->where(function ($subQuery) use ($searchTerm) {
                $subQuery->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhere('grade', 'like', "%{$searchTerm}%");
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

    // Menghasilkan usulan kode SKU komoditas berikutnya dengan format sekuensial GPA-VGT-XX.
    public function getSuggestedSku(): string
    {
        $nextId = (Product::max('id') ?: 0) + 1;
        return 'GPA-VGT-' . str_pad((string) $nextId, 2, '0', STR_PAD_LEFT);
    }

    // Menyimpan data komoditas baru, menangani upload aset visual, dan mencatat riwayat audit.
    public function storeProduct(array $validatedData, ?UploadedFile $imageFile, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $imageUrl = null;

        // Menyimpan file gambar produk yang diunggah ke storage publik jika disertakan
        if ($imageFile) {
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $path = $imageFile->storeAs('products', $filename, 'public');
            $imageUrl = 'storage/' . $path;
        } elseif (!empty($validatedData['preset_image'])) {
            $imageUrl = $validatedData['preset_image'];
        }

        $product = Product::create([
            'sku'           => strtoupper(trim($validatedData['sku'])),
            'name'          => trim($validatedData['name']),
            'slug'          => Str::slug(trim($validatedData['name'])),
            'unit'          => strtolower(trim($validatedData['unit'])),
            'grade'         => trim($validatedData['grade']),
            'base_price'    => $validatedData['base_price'],
            'minimum_order' => $validatedData['minimum_order'],
            'image_url'     => $imageUrl,
            'description'   => $validatedData['description'] ?? null,
            'is_active'     => (bool) $validatedData['is_active'],
        ]);

        $logCategory = ($actorRole === 'KOORDINATOR') ? 'COMMODITY_CREATED_BY_KOORDINATOR' : 'COMMODITY_CATALOG_CREATED';
        $logMessage = ($actorRole === 'KOORDINATOR')
            ? "Koordinator Lapangan ({$actorName}) menambahkan komoditas baru '{$product->name}' (SKU: {$product->sku}) ke master produk."
            : "Penambahan komoditas baru '{$product->name}' (SKU: {$product->sku}, Grade: {$product->grade}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") ke katalog resmi oleh {$actorName}.";

        $this->tataKelolaService->addAuditLog(
            $logCategory,
            'PRD-' . $product->id,
            $logMessage,
            $actorName,
            $clearance,
            'SEALED'
        );

        return $product;
    }

    // Mengambil informasi detail komoditas beserta kalkulasi kuota stok panen riil di gudang.
    public function getProductDetailData(Product $product): array
    {
        $product->load(['harvestBatches.inputter', 'contracts.user']);
        $availableStock = (float) $product->harvestBatches->sum('available_quantity');
        $totalHarvested = (float) $product->harvestBatches->sum('initial_quantity');

        return compact('product', 'availableStock', 'totalHarvested');
    }

    // Memperbarui informasi komoditas, mengganti visual jika diunggah baru, dan mencatat audit perubahan.
    public function updateProduct(Product $product, array $validatedData, ?UploadedFile $imageFile, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $imageUrl = $product->image_url;

        if ($imageFile) {
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $path = $imageFile->storeAs('products', $filename, 'public');
            $imageUrl = 'storage/' . $path;
        } elseif (!empty($validatedData['preset_image'])) {
            $imageUrl = $validatedData['preset_image'];
        }

        $product->update([
            'sku'           => strtoupper(trim($validatedData['sku'])),
            'name'          => trim($validatedData['name']),
            'slug'          => Str::slug(trim($validatedData['name'])),
            'unit'          => strtolower(trim($validatedData['unit'])),
            'grade'         => trim($validatedData['grade']),
            'base_price'    => $validatedData['base_price'],
            'minimum_order' => $validatedData['minimum_order'],
            'image_url'     => $imageUrl,
            'description'   => $validatedData['description'] ?? null,
            'is_active'     => (bool) $validatedData['is_active'],
        ]);

        $logCategory = ($actorRole === 'KOORDINATOR') ? 'COMMODITY_UPDATED_BY_KOORDINATOR' : 'COMMODITY_CATALOG_UPDATED';
        $this->tataKelolaService->addAuditLog(
            $logCategory,
            'PRD-' . $product->id,
            "Pembaruan data komoditas '{$product->name}' (SKU: {$product->sku}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") oleh {$actorName}.",
            $actorName,
            $clearance,
            'SEALED'
        );

        return $product;
    }

    // Mengubah status aktif atau nonaktif komoditas serta mencatat riwayat perubahan ke audit log.
    public function toggleStatus(Product $product, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): bool
    {
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->tataKelolaService->addAuditLog(
            'COMMODITY_STATUS_TOGGLED',
            'PRD-' . $product->id,
            "Komoditas {$product->name} {$statusText} oleh {$actorName}.",
            $actorName,
            $clearance,
            'SEALED'
        );

        return $newStatus;
    }

    // Helper pengubah status aktif komoditas dengan mengembalikan instance model terbaru.
    public function toggleProductStatus(Product $product, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $this->toggleStatus($product, $actorRole, $actorName, $clearance);
        return $product->fresh();
    }

    // Menghapus entitas komoditas secara permanen atau soft-deactivate jika terikat riwayat transaksi.
    public function deleteProduct(Product $product, string $actorRole, string $actorName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): array
    {
        $hasBatches = $product->harvestBatches()->exists();
        $hasOrderItems = $product->orderItems()->exists();
        $hasContracts = $product->contracts()->exists();
        $productName = $product->name;

        // Jika sudah ada riwayat transaksi, cukup nonaktifkan agar data tidak rusak
        if ($hasBatches || $hasOrderItems || $hasContracts) {
            $product->update(['is_active' => false]);

            $this->tataKelolaService->addAuditLog(
                'COMMODITY_DEACTIVATED',
                'PRD-' . $product->id,
                "Komoditas '{$productName}' dinonaktifkan dari katalog B2B karena memiliki riwayat panen/pesanan/kontrak.",
                $actorName,
                $clearance,
                'SEALED'
            );

            return [
                'is_deleted' => false,
                'message'    => "Komoditas {$productName} memiliki riwayat transaksi/kontrak, status berhasil diubah menjadi NON-AKTIF untuk menjaga integritas data pembukuan.",
            ];
        }

        // Hapus permanen jika belum ada transaksi terkait
        $product->delete();

        $this->tataKelolaService->addAuditLog(
            'COMMODITY_DELETED',
            'PRD-' . $product->id,
            "Penghapusan permanen komoditas '{$productName}' dari basis data oleh {$actorName}.",
            $actorName,
            $clearance,
            'SEALED'
        );

        return [
            'is_deleted' => true,
            'message'    => "Komoditas {$productName} berhasil dihapus permanen dari sistem.",
        ];
    }
}
