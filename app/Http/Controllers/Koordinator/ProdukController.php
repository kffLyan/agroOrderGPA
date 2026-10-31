<?php

namespace App\Http\Controllers\Koordinator;

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
 * Controller Manajemen Komoditas Hasil Tani (Produk) untuk Koordinator Lapangan.
 *
 * Tugas Utama:
 * 1. Menampilkan daftar seluruh sayuran/komoditas yang dipasok oleh petani binaan.
 * 2. Menambah komoditas baru ke katalog publik hasil panen.
 * 3. Menyunting informasi harga dasar, satuan, grade, dan foto komoditas.
 * 4. Mengubah status aktif/nonaktif komoditas (menghapus secara aman).
 */
class ProdukController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Menampilkan katalog komoditas master hasil panen untuk Koordinator.
     */
    public function index(Request $request): View
    {
        $catalogData = $this->productService->getCatalogData($request);

        return view('koordinator.produk.index', $catalogData);
    }

    /**
     * Menampilkan formulir penambahan komoditas baru.
     */
    public function create(): View
    {
        $suggestedSku = $this->productService->getSuggestedSku();

        return view('koordinator.produk.create', compact('suggestedSku'));
    }

    /**
     * Menyimpan komoditas baru ke database dan katalog publik.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();

        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $product = $this->productService->storeProduct(
                $validatedData,
                $request->file('image_file'),
                'KOORDINATOR',
                $actorName,
                'KL-SENTRA-01'
            );

            return redirect()->route('koordinator.produk.index')
                ->with('success', "Komoditas '{$product->name}' berhasil ditambahkan. Katalog publik otomatis terupdate.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan rincian detail komoditas dan riwayat batch panen.
     */
    public function show(Product $product): View
    {
        $detailData = $this->productService->getProductDetailData($product);

        return view('koordinator.produk.show', $detailData);
    }

    /**
     * Menampilkan formulir penyuntingan komoditas master.
     */
    public function edit(Product $product): View
    {
        return view('koordinator.produk.edit', compact('product'));
    }

    /**
     * Memperbarui data komoditas hasil tani.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validatedData = $request->validated();

        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $this->productService->updateProduct(
                $product,
                $validatedData,
                $request->file('image_file'),
                'KOORDINATOR',
                $actorName,
                'KL-SENTRA-01'
            );

            return redirect()->route('koordinator.produk.index')
                ->with('success', "Perubahan komoditas '{$product->name}' berhasil disimpan.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Mengaktifkan atau menonaktifkan status tayang komoditas di katalog.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $updatedProduct = $this->productService->toggleProductStatus($product, 'KOORDINATOR', $actorName);
            $statusLabel = $updatedProduct->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return back()->with('success', "Status komoditas '{$updatedProduct->name}' berhasil {$statusLabel}.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mengubah status komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus komoditas secara aman (soft toggle/nonaktif).
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $this->productService->deleteProduct($product, 'KOORDINATOR', $actorName);

            return redirect()->route('koordinator.produk.index')
                ->with('success', "Komoditas '{$product->name}' telah dinonaktifkan dari katalog operasional.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus komoditas: ' . $e->getMessage());
        }
    }
}
