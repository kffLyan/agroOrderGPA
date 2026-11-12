<?php

namespace App\Http\Controllers\Direktur;

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

// Controller penetapan harga dasar, katalog komoditas, dan kebijakan mutu sayuran oleh Direktur Utama
class ProdukController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // Menampilkan seluruh katalog komoditas beserta batasan harga dasar dan minimum kuota order
    public function index(Request $request): View
    {
        $catalogData = $this->productService->getCatalogData($request);

        return view('direktur.produk.index', $catalogData);
    }

    // Menyiapkan formulir pendaftaran komoditas baru dengan rekomendasi nomor SKU unik otomatis
    public function create(): View
    {
        $suggestedSku = $this->productService->getSuggestedSku();

        return view('direktur.produk.create', compact('suggestedSku'));
    }

    // Menyimpan komoditas baru ke katalog resmi dengan hak akses eksekutif dan pencatatan audit log
    public function store(SimpanProdukRequest $request): RedirectResponse
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
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Gagal menambahkan komoditas: ' . $exception->getMessage());
        }
    }

    // Menampilkan rincian detail spesifikasi komoditas, neraca stok fisik, dan riwayat panen terkait
    public function show(Product $product): View
    {
        $detailData = $this->productService->getProductDetailData($product);

        return view('direktur.produk.show', $detailData);
    }

    // Menampilkan formulir pengubahan informasi komoditas, penetapan harga dasar, dan foto produk
    public function edit(Product $product): View
    {
        return view('direktur.produk.edit', compact('product'));
    }

    // Memperbarui informasi komoditas resmi dan menyinkronkan perubahan ke katalog transaksi
    public function update(PerbaruiProdukRequest $request, Product $product): RedirectResponse
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
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $exception->getMessage());
        }
    }

    // Mengubah status aktif komoditas untuk menentukan apakah tampil di katalog pemesanan klien
    public function toggleStatus(Product $product): RedirectResponse
    {
        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $updatedProduct = $this->productService->toggleProductStatus($product, 'DIREKTUR', $directorName);
            $statusLabel = $updatedProduct->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return back()->with('success', "Status komoditas {$updatedProduct->name} berhasil {$statusLabel}.");
        } catch (Exception $exception) {
            return back()->with('error', 'Gagal mengubah status komoditas: ' . $exception->getMessage());
        }
    }

    // Menghapus atau menonaktifkan komoditas dari katalog operasional jika sudah tidak dibudidayakan
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $this->productService->deleteProduct($product, 'DIREKTUR', $directorName);

            return redirect()->route('direktur.produk.index')
                ->with('success', "Komoditas {$product->name} berhasil dinonaktifkan dari katalog operasional.");
        } catch (Exception $exception) {
            return back()->with('error', 'Gagal menghapus komoditas: ' . $exception->getMessage());
        }
    }
}
