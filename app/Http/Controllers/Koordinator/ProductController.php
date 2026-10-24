<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\HarvestBatch;
use App\Models\Product;
use App\Services\GovernanceService;
use App\Services\StockService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
     * Tampilkan daftar komoditas master hasil tani untuk Koordinator Lapangan.
     */
    public function index(Request $request): View
    {
        $query = Product::with(['harvestBatches'])->orderBy('id', 'asc');

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
        $totalStockKg = (float) HarvestBatch::sum('available_quantity');

        return view('koordinator.products.index', compact(
            'products',
            'totalProducts',
            'activeProducts',
            'avgPrice',
            'totalStockKg'
        ));
    }

    /**
     * Formulir penambahan komoditas baru ke master data & katalog publik.
     */
    public function create(): View
    {
        $nextId = (Product::max('id') ?: 0) + 1;
        $suggestedSku = 'GPA-VGT-' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

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
            $imageUrl = null;

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
                'slug'          => Str::slug(trim($validated['name'])),
                'unit'          => strtolower(trim($validated['unit'])),
                'grade'         => trim($validated['grade']),
                'base_price'    => $validated['base_price'],
                'minimum_order' => $validated['minimum_order'],
                'image_url'     => $imageUrl,
                'description'   => $validated['description'] ?? null,
                'is_active'     => (bool) $validated['is_active'],
            ]);

            $operatorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $this->governanceService->addAuditLog(
                'COMMODITY_CREATED_BY_KOORDINATOR',
                'PRD-' . $product->id,
                "Koordinator Lapangan ({$operatorName}) menambahkan komoditas baru '{$product->name}' (SKU: {$product->sku}) ke master produk.",
                $operatorName,
                'KL-SENTRA-01',
                'VERIFIED'
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
        $product->load(['harvestBatches.inputter']);
        $availableStock = (float) $product->harvestBatches->sum('available_quantity');
        $totalHarvested = (float) $product->harvestBatches->sum('initial_quantity');

        return view('koordinator.products.show', compact('product', 'availableStock', 'totalHarvested'));
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
            $imageUrl = $product->image_url;

            if ($request->hasFile('image_file')) {
                $file = $request->file('image_file');
                $filename = 'prod_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('products', $filename, 'public');
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

            $operatorName = Auth::user()?->name ?: 'Koordinator Lapangan';
            $this->governanceService->addAuditLog(
                'COMMODITY_UPDATED_BY_KOORDINATOR',
                'PRD-' . $product->id,
                "Koordinator Lapangan ({$operatorName}) memperbarui komoditas '{$product->name}' (SKU: {$product->sku}).",
                $operatorName,
                'KL-SENTRA-01',
                'VERIFIED'
            );

            return redirect()->route('koordinator.products.index')
                ->with('success', "Data komoditas '{$product->name}' berhasil diperbarui.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui komoditas: ' . $e->getMessage());
        }
    }

    /**
     * Beralih status aktif/nonaktif komoditas (Katalog Publik Terupdate Real-Time).
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $newStatus = !$product->is_active;
        $product->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        $operatorName = Auth::user()?->name ?: 'Koordinator Lapangan';

        $this->governanceService->addAuditLog(
            'COMMODITY_STATUS_TOGGLED_BY_KOORDINATOR',
            'PRD-' . $product->id,
            "Koordinator Lapangan ({$operatorName}) {$statusText} komoditas '{$product->name}'.",
            $operatorName,
            'KL-SENTRA-01',
            'VERIFIED'
        );

        return redirect()->back()->with('success', "Status komoditas '{$product->name}' berhasil {$statusText}. Katalog publik terupdate.");
    }

    /**
     * Hapus / nonaktifkan komoditas.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->update(['is_active' => false]);

        return redirect()->route('koordinator.products.index')
            ->with('success', "Komoditas '{$name}' telah dinonaktifkan dari katalog operasional.");
    }
}
