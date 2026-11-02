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
            $search = trim($request->query('search'));
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('grade', 'like', "%{$search}%");
            });
        }

        if ($request->filled('grade') && $request->grade !== 'all') {
            $query->where('grade', $request->grade);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('is_active', $request->status === '1' || $request->status === 'active');
        }

        $products       = $query->get();
        $totalProducts  = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $avgPrice       = (float) (Product::where('is_active', true)->avg('base_price') ?: 0);
        $totalStockKg   = (float) HarvestBatch::sum('available_quantity');

        return [
            'products'       => $products,
            'totalProducts'  => $totalProducts,
            'activeProducts' => $activeProducts,
            'avgPrice'       => $avgPrice,
            'totalStockKg'   => $totalStockKg,
        ];
    }

    // Menghasilkan usulan kode SKU komoditas berikutnya dengan format sekuensial GPA-VGT-XX.
    public function getSuggestedSku(): string
    {
        $nextId = (Product::max('id') ?: 0) + 1;
        return 'GPA-VGT-' . str_pad((string) $nextId, 2, '0', STR_PAD_LEFT);
    }

    // Menyimpan data komoditas baru, menangani upload aset visual, dan mencatat riwayat audit.
    public function storeProduct(array $data, ?UploadedFile $image, string $role, string $userName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $imageUrl = null;

        // Menyimpan file gambar produk yang diunggah ke storage publik jika disertakan
        if ($image) {
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $filename, 'public');
            $imageUrl = 'storage/' . $path;
        } elseif (!empty($data['preset_image'])) {
            $imageUrl = $data['preset_image'];
        }

        $product = Product::create([
            'sku'           => strtoupper(trim($data['sku'])),
            'name'          => trim($data['name']),
            'slug'          => Str::slug(trim($data['name'])),
            'unit'          => strtolower(trim($data['unit'])),
            'grade'         => trim($data['grade']),
            'base_price'    => $data['base_price'],
            'minimum_order' => $data['minimum_order'],
            'image_url'     => $imageUrl,
            'description'   => $data['description'] ?? null,
            'is_active'     => (bool) $data['is_active'],
        ]);

        $logCategory = ($role === 'KOORDINATOR') ? 'COMMODITY_CREATED_BY_KOORDINATOR' : 'COMMODITY_CATALOG_CREATED';
        $logMessage = ($role === 'KOORDINATOR')
            ? "Koordinator Lapangan ({$userName}) menambahkan komoditas baru '{$product->name}' (SKU: {$product->sku}) ke master produk."
            : "Penambahan komoditas baru '{$product->name}' (SKU: {$product->sku}, Grade: {$product->grade}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") ke katalog resmi oleh {$userName}.";

        $this->tataKelolaService->addAuditLog(
            $logCategory,
            'PRD-' . $product->id,
            $logMessage,
            $userName,
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

        return [
            'product'        => $product,
            'availableStock' => $availableStock,
            'totalHarvested' => $totalHarvested,
        ];
    }

    // Memperbarui informasi komoditas, mengganti visual jika diunggah baru, dan mencatat audit perubahan.
    public function updateProduct(Product $product, array $data, ?UploadedFile $image, string $role, string $userName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $imageUrl = $product->image_url;

        if ($image) {
            $filename = 'prod_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('products', $filename, 'public');
            $imageUrl = 'storage/' . $path;
        } elseif (!empty($data['preset_image'])) {
            $imageUrl = $data['preset_image'];
        }

        $product->update([
            'sku'           => strtoupper(trim($data['sku'])),
            'name'          => trim($data['name']),
            'slug'          => Str::slug(trim($data['name'])),
            'unit'          => strtolower(trim($data['unit'])),
            'grade'         => trim($data['grade']),
            'base_price'    => $data['base_price'],
            'minimum_order' => $data['minimum_order'],
            'image_url'     => $imageUrl,
            'description'   => $data['description'] ?? null,
            'is_active'     => (bool) $data['is_active'],
        ]);

        $logCategory = ($role === 'KOORDINATOR') ? 'COMMODITY_UPDATED_BY_KOORDINATOR' : 'COMMODITY_CATALOG_UPDATED';
        $this->tataKelolaService->addAuditLog(
            $logCategory,
            'PRD-' . $product->id,
            "Pembaruan data komoditas '{$product->name}' (SKU: {$product->sku}, Harga: Rp " . number_format($product->base_price, 0, ',', '.') . ") oleh {$userName}.",
            $userName,
            $clearance,
            'SEALED'
        );

        return $product;
    }

    // Mengubah status aktif atau nonaktif komoditas serta mencatat riwayat perubahan ke audit log.
    public function toggleStatus(Product $product, string $role, string $userName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): bool
    {
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->tataKelolaService->addAuditLog(
            'COMMODITY_STATUS_TOGGLED',
            'PRD-' . $product->id,
            "Komoditas {$product->name} {$statusText} oleh {$userName}.",
            $userName,
            $clearance,
            'SEALED'
        );

        return $newStatus;
    }

    // Helper pengubah status aktif komoditas dengan mengembalikan instance model terbaru.
    public function toggleProductStatus(Product $product, string $role, string $userName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): Product
    {
        $this->toggleStatus($product, $role, $userName, $clearance);
        return $product->fresh();
    }

    // Menghapus entitas komoditas secara permanen atau soft-deactivate jika terikat riwayat transaksi.
    public function deleteProduct(Product $product, string $role, string $userName, string $clearance = 'DIR-01 [ROOT-ACCESS]'): array
    {
        $hasBatches   = $product->harvestBatches()->exists();
        $hasOrders    = $product->orderItems()->exists();
        $hasContracts = $product->contracts()->exists();
        $productName  = $product->name;

        // Jika sudah ada riwayat transaksi, cukup nonaktifkan agar data tidak rusak
        if ($hasBatches || $hasOrders || $hasContracts) {
            $product->update(['is_active' => false]);

            $this->tataKelolaService->addAuditLog(
                'COMMODITY_DEACTIVATED',
                'PRD-' . $product->id,
                "Komoditas '{$productName}' dinonaktifkan dari katalog B2B karena memiliki riwayat panen/pesanan/kontrak.",
                $userName,
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
            "Penghapusan permanen komoditas '{$productName}' dari basis data oleh {$userName}.",
            $userName,
            $clearance,
            'SEALED'
        );

        return [
            'is_deleted' => true,
            'message'    => "Komoditas {$productName} berhasil dihapus permanen dari sistem.",
        ];
    }
}
