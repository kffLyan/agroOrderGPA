<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimpanProdukRequest;
use App\Http\Requests\PerbaruiProdukRequest;
use App\Models\Product;
use App\Services\ProductService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Controller pengelolaan data master komoditas panen petani dan katalog publik oleh Koordinator Lapangan
class ProdukController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // Menampilkan katalog komoditas master beserta ringkasan stok fisik di gudang sentra
    public function index(Request $request): View
    {
        $catalogData = $this->productService->getCatalogData($request);

        return view('koordinator.produk.index', $catalogData);
    }

    // Menyiapkan formulir pendaftaran komoditas baru dengan rekomendasi kode SKU unik otomatis
    public function create(): View
    {
        $suggestedSku = $this->productService->getSuggestedSku();

        return view('koordinator.produk.create', compact('suggestedSku'));
    }

    // Menyimpan komoditas panen baru ke database, mengunggah foto, dan mempublikasikannya ke katalog
    public function store(SimpanProdukRequest $request): RedirectResponse
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
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Gagal menambahkan komoditas: ' . $exception->getMessage());
        }
    }

    // Menampilkan detail spesifikasi komoditas, riwayat pasokan batch panen, dan pergerakan stok
    public function show(Product $product): View
    {
        $detailData = $this->productService->getProductDetailData($product);

        return view('koordinator.produk.show', $detailData);
    }

    // Menampilkan formulir penyuntingan data komoditas, grade kualitas, dan penetapan harga dasar
    public function edit(Product $product): View
    {
        return view('koordinator.produk.edit', compact('product'));
    }

    // Memperbarui atribut komoditas, mengganti foto jika ada, dan merekam log audit perubahannya
    public function update(PerbaruiProdukRequest $request, Product $product): RedirectResponse
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
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $exception->getMessage());
        }
    }

    // Mengubah status tayang komoditas secara instan (aktif/nonaktif) agar tidak bisa dipesan sementara
    public function toggleStatus(Product $product): RedirectResponse
    {
        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $updatedProduct = $this->productService->toggleProductStatus($product, 'KOORDINATOR', $actorName);
            $statusLabel = $updatedProduct->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return back()->with('success', "Status komoditas '{$updatedProduct->name}' berhasil {$statusLabel}.");
        } catch (Exception $exception) {
            return back()->with('error', 'Gagal mengubah status komoditas: ' . $exception->getMessage());
        }
    }

    // Menonaktifkan komoditas dari katalog secara aman (soft delete/nonaktif) tanpa menghapus data riwayat
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $this->productService->deleteProduct($product, 'KOORDINATOR', $actorName);

            return redirect()->route('koordinator.produk.index')
                ->with('success', "Komoditas '{$product->name}' telah dinonaktifkan dari katalog operasional.");
        } catch (Exception $exception) {
            return back()->with('error', 'Gagal menghapus komoditas: ' . $exception->getMessage());
        }
    }
}
