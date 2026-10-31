<?php

namespace App\Http\Controllers\Direktur;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller Manajemen Komoditas & Kebijakan Harga untuk Direktur Utama.
 *
 * Tugas Utama:
 * 1. Menampilkan seluruh katalog komoditas & penetapan batas harga dasar koperasi GPA.
 * 2. Menambah komoditas baru dengan otoritas eksekutif (*ROOT-ACCESS*).
 * 3. Memantau integrasi kontrak terikat dan stok panen riil.
 * 4. Mengubah status tayang komoditas di katalog publik.
 */
class ProdukController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Tampilkan seluruh katalog komoditas & penetapan harga dasar GPA.
     */
    public function index(Request $request): View
    {
        $catalogData = $this->productService->getCatalogData($request);

        return view('direktur.produk.index', $catalogData);
    }

    /**
     * Formulir penambahan komoditas baru ke katalog resmi oleh Direksi.
     */
    public function create(): View
    {
        $suggestedSku = $this->productService->getSuggestedSku();

        return view('direktur.produk.create', compact('suggestedSku'));
    }

    /**
     * Simpan komoditas baru ke basis data dengan hak otoritas Direktur.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $product = $this->productService->storeProduct(
                $validatedData,
                $request->file('image_file'),
                'DIREKTUR',
                $directorName,
                'DIR-01 [ROOT-ACCESS]'
            );

            return redirect()->route('direktur.produk.index')
                ->with('success', "Komoditas {$product->name} berhasil ditambahkan ke katalog resmi GPA.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail rincian komoditas, stok riil panen & kontrak terikat.
     */
    public function show(Product $product): View
    {
        $detailData = $this->productService->getProductDetailData($product);

        return view('direktur.produk.show', $detailData);
    }

    /**
     * Formulir penyuntingan komoditas oleh Direktur.
     */
    public function edit(Product $product): View
    {
        return view('direktur.produk.edit', compact('product'));
    }

    /**
     * Perbarui data komoditas resmi.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validatedData = $request->validated();

        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $updatedProduct = $this->productService->updateProduct(
                $product,
                $validatedData,
                $request->file('image_file'),
                'DIREKTUR',
                $directorName,
                'DIR-01 [ROOT-ACCESS]'
            );

            return redirect()->route('direktur.produk.index')
                ->with('success', "Data komoditas {$updatedProduct->name} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Mengaktifkan atau menonaktifkan status tayang komoditas.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $updatedProduct = $this->productService->toggleProductStatus($product, 'DIREKTUR', $directorName);
            $statusLabel = $updatedProduct->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return back()->with('success', "Status komoditas {$updatedProduct->name} berhasil {$statusLabel}.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengubah status komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus atau menonaktifkan komoditas dari peredaran transaksi.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $this->productService->deleteProduct($product, 'DIREKTUR', $directorName);

            return redirect()->route('direktur.produk.index')
                ->with('success', "Komoditas {$product->name} berhasil dinonaktifkan dari katalog operasional.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus komoditas: ' . $e->getMessage());
        }
    }
}
