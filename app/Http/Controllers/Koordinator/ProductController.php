<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * Tampilkan daftar komoditas master hasil tani untuk Koordinator Lapangan.
     */
    public function index(Request $request): View
    {
        $data = $this->productService->getCatalogData($request);

        return view('koordinator.products.index', $data);
    }

    /**
     * Formulir penambahan komoditas baru ke master data & katalog publik.
     */
    public function create(): View
    {
        $suggestedSku = $this->productService->getSuggestedSku();

        return view('koordinator.products.create', compact('suggestedSku'));
    }

    /**
     * Simpan komoditas baru ke tabel products.
     */
    public function store(Request $request): RedirectResponse
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
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $product = $this->productService->storeProduct(
                $validated,
                $request->file('image_file'),
                'KOORDINATOR',
                $actorName,
                'KL-SENTRA-01'
            );

            return redirect()->route('koordinator.products.index')
                ->with('success', "Komoditas '{$product->name}' berhasil ditambahkan ke master data. Katalog publik otomatis terupdate.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail rincian komoditas dan riwayat batch pasokan panen.
     */
    public function show(Product $product): View
    {
        $data = $this->productService->getProductDetailData($product);

        return view('koordinator.products.show', $data);
    }

    /**
     * Formulir penyuntingan komoditas master.
     */
    public function edit(Product $product): View
    {
        return view('koordinator.products.edit', compact('product'));
    }

    /**
     * Perbarui data komoditas.
     */
    public function update(Request $request, Product $product): RedirectResponse
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
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $this->productService->updateProduct(
                $product,
                $validated,
                $request->file('image_file'),
                'KOORDINATOR',
                $actorName,
                'KL-SENTRA-01'
            );

            return redirect()->route('koordinator.products.index')
                ->with('success', "Data komoditas '{$product->name}' berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui data komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Beralih status aktif / non-aktif komoditas secara cepat.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
        $newStatus = $this->productService->toggleStatus($product, 'KOORDINATOR', $actorName, 'KL-SENTRA-01');
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status komoditas '{$product->name}' berhasil {$statusText}.");
    }

    /**
     * Hapus komoditas master jika belum ada relasi transaksi.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $actorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $result = $this->productService->deleteProduct($product, 'KOORDINATOR', $actorName, 'KL-SENTRA-01');

            return redirect()->route('koordinator.products.index')->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus komoditas: ' . $e->getMessage());
        }
    }
}
