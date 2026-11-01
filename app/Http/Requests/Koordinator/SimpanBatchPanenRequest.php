<?php

namespace App\Http\Requests\Koordinator;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

// Form request validasi pencatatan batch panen komoditas
class SimpanBatchPanenRequest extends FormRequest
{
    // Otorisasi peran koordinator, sekretaris, dan direktur
    public function authorize(): bool
    {
        return $this->user() !== null && in_array($this->user()->role, ['KOORDINATOR', 'SEKRETARIS', 'DIREKTUR'], true);
    }

    // Normalisasi input sebelum validasi dijalankan
    protected function prepareForValidation(): void
    {
        $idProduk = $this->input('product_id');
        if (!$idProduk && $this->filled('commodity')) {
            $kunciKomoditas = $this->input('commodity');
            $produk = Product::where('slug', $kunciKomoditas)
                ->orWhere('name', 'like', '%' . str_replace('-', ' ', $kunciKomoditas) . '%')
                ->first();
            $idProduk = $produk?->id;
        }

        if (!$idProduk) {
            $idProduk = Product::first()?->id;
        }

        $tipeSumber = $this->input('source_type');
        if (!in_array($tipeSumber, ['PETANI_BINAAN', 'BUFFER_LUAR'], true)) {
            $kategori = (string) $this->input('category', '');
            $tipeSumber = str_contains(strtoupper($kategori), 'BUFFER') ? 'BUFFER_LUAR' : 'PETANI_BINAAN';
        }

        $this->merge([
            'product_id'       => $idProduk,
            'source_type'      => $tipeSumber,
            'supplier_name'    => $this->input('supplier_name', $this->input('vendor', 'Petani Binaan')),
            'batch_date'       => $this->input('batch_date', $this->input('date', now()->toDateString())),
            'initial_quantity' => (float) $this->input('initial_quantity', $this->input('quantity', 0)),
            'grade'            => $this->input('grade', 'Grade A'),
            'qc_notes'         => $this->input('qc_notes', $this->input('notes', 'Lolos sortir manual, mutu terverifikasi')),
        ]);
    }

    // Aturan validasi data batch panen
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
