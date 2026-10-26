<?php

namespace App\Http\Controllers\Direktur;

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
     * Tampilkan seluruh katalog komoditas & penetapan harga dasar GPA.
     */
    public function index(Request $request): View
    {
        $data = $this->productService->getCatalogData($request);

        return view('direktur.products.index', $data);
    }

    /**
     * Formulir penambahan komoditas baru ke katalog resmi.
     */
    public function create(): View
    {
        $suggestedSku = $this->productService->getSuggestedSku();

        return view('direktur.products.create', compact('suggestedSku'));
    }

    /**
     * Simpan komoditas baru ke basis data.
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
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $product = $this->productService->storeProduct(
                $validated,
                $request->file('image_file'),
                'DIREKTUR',
                $directorName,
                'DIR-01 [ROOT-ACCESS]'
            );

            return redirect()->route('direktur.products.index')
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
        $data = $this->productService->getProductDetailData($product);

        return view('direktur.products.show', $data);
    }

    /**
     * Formulir penyuntingan komoditas.
     */
    public function edit(Product $product): View
    {
        return view('direktur.products.edit', compact('product'));
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
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $product = $this->productService->updateProduct(
                $product,
                $validated,
                $request->file('image_file'),
                'DIREKTUR',
                $directorName,
                'DIR-01 [ROOT-ACCESS]'
            );

            return redirect()->route('direktur.products.index')
                ->with('success', "Data komoditas {$product->name} berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Hapus komoditas atau nonaktifkan jika memiliki transaksi terikat.
     */
    public function destroy(Product $product): RedirectResponse
    {
        try {
            $directorName = Auth::user()?->name ?: 'Direktur Utama';
            $result = $this->productService->deleteProduct($product, 'DIREKTUR', $directorName, 'DIR-01 [ROOT-ACCESS]');

            return redirect()->route('direktur.products.index')->with('success', $result['message']);
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses penghapusan: ' . $e->getMessage());
        }
    }

    /**
     * Beralih status aktif / non-aktif secara cepat.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $directorName = Auth::user()?->name ?: 'Direktur Utama';
        $newStatus = $this->productService->toggleStatus($product, 'DIREKTUR', $directorName, 'DIR-01 [ROOT-ACCESS]');
        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status komoditas {$product->name} berhasil {$statusText}.");
    }
}
