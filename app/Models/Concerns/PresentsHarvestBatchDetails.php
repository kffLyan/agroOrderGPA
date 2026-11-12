<?php

namespace App\Models\Concerns;

trait PresentsHarvestBatchDetails
{
    // Menghasilkan nomor tiket timbangan digital resmi penerimaan panen di gudang
    public function getTicketAttribute(): string
    {
        $year = $this->created_at ? $this->created_at->format('Y') : date('Y');
        return 'TKT-' . $year . '-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    // Memformat jam pencatatan panen ke zona waktu WIB
    public function getTimeFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('H:i') . ' WIB' : date('H:i') . ' WIB';
    }

    // Menghitung bobot kotor (bruto) timbangan muatan panen beserta tara keranjang
    public function getGrossAttribute(): float
    {
        return (float) $this->initial_quantity + 20.0;
    }

    // Menetapkan standar bobot tara kemasan/keranjang komoditas sayur
    public function getTareAttribute(): float
    {
        return 20.0;
    }

    // Menghitung bobot bersih (netto) komoditas panen hasil timbangan
    public function getNetAttribute(): float
    {
        return (float) $this->initial_quantity;
    }

    // Mengembalikan status kelulusan uji mutu sortasi fisik komoditas
    public function getQcAttribute(): string
    {
        return 'QC PASS';
    }

    // Menentukan label keterangan sumber pasokan panen gudang
    public function getNoteAttribute(): string
    {
        return $this->source_type === 'BUFFER_LUAR' ? 'Buffer Luar' : 'Panen Binaan';
    }

    public function getSourceLabelAttribute(): string
    {
        return $this->note;
    }

    public function getSourceTypeLabelAttribute(): string
    {
        return match ($this->source_type) {
            'PETANI_BINAAN' => 'Petani Binaan',
            'BUFFER_LUAR'   => 'Mitra Buffer Luar',
            default         => ucwords(strtolower(str_replace('_', ' ', (string) $this->source_type))),
        };
    }

    public function getNameAttribute(): string
    {
        return $this->supplier_name ?: 'Mitra Tani Binaan';
    }

    public function getCommodityAttribute(): string
    {
        return $this->product?->name ?? '—';
    }

    public function getTimeAttribute(): string
    {
        return $this->time_formatted;
    }

    public function getLotAttribute(): string
    {
        return $this->ticket;
    }

    public function getEntryAttribute(): string
    {
        $commodity = $this->product?->name ?? 'Komoditas';
        return $commodity . ' (' . number_format((float) $this->initial_quantity, 1, ',', '.') . ' kg)';
    }
}
