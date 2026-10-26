<?php

namespace App\Http\Requests\Koordinator;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreHarvestBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && in_array($this->user()->role, ['KOORDINATOR', 'SEKRETARIS', 'DIREKTUR']);
    }

    /**
     * Normalisasi input sebelum validasi dijalankan
     */
    protected function prepareForValidation(): void
    {
        $productId = $this->input('product_id');
        if (!$productId && $this->filled('commodity')) {
            $commodityKey = $this->input('commodity');
            $product = Product::where('slug', $commodityKey)
                ->orWhere('name', 'like', '%' . str_replace('-', ' ', $commodityKey) . '%')
                ->first();
            $productId = $product?->id;
        }

        if (!$productId) {
            $productId = Product::first()?->id;
        }

        $sourceType = $this->input('source_type');
        if (!in_array($sourceType, ['PETANI_BINAAN', 'BUFFER_LUAR'])) {
            $category = (string) $this->input('category', '');
            $sourceType = str_contains(strtoupper($category), 'BUFFER') ? 'BUFFER_LUAR' : 'PETANI_BINAAN';
        }

        $this->merge([
            'product_id'       => $productId,
            'source_type'      => $sourceType,
            'supplier_name'    => $this->input('supplier_name', $this->input('vendor', 'Petani Binaan')),
            'batch_date'       => $this->input('batch_date', $this->input('date', now()->toDateString())),
            'initial_quantity' => (float) $this->input('initial_quantity', $this->input('quantity', 0)),
            'grade'            => $this->input('grade', 'Grade A'),
            'qc_notes'         => $this->input('qc_notes', $this->input('notes', 'Lolos sortir manual, mutu terverifikasi')),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id'       => ['required', 'exists:products,id'],
            'source_type'      => ['required', 'in:PETANI_BINAAN,BUFFER_LUAR'],
            'supplier_name'    => ['required', 'string', 'max:100'],
            'batch_date'       => ['required', 'date'],
            'initial_quantity' => ['required', 'numeric', 'min:0.01'],
            'grade'            => ['nullable', 'string', 'max:50'],
            'qc_notes'         => ['nullable', 'string', 'max:255'],
        ];
    }
}
