<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait PresentsProductCatalog
{
    public function getKeyAttribute(): string
    {
        return Str::slug($this->name ?? '', '_');
    }

    public function getCategoryAttribute(): string
    {
        $nameLower = strtolower($this->name ?? '');
        if (str_contains($nameLower, 'tomat') || str_contains($nameLower, 'beef')) {
            return 'veg_fruit';
        }
        if (str_contains($nameLower, 'brokoli') || str_contains($nameLower, 'kol') || str_contains($nameLower, 'kubis')) {
            return 'veg_flower';
        }
        if (str_contains($nameLower, 'stroberi') || str_contains($nameLower, 'strawberry') || str_contains($nameLower, 'berry')) {
            return 'fruit_fresh';
        }
        return 'veg_leaf';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'veg_fruit'  => 'Sayuran Buah',
            'veg_flower' => 'Sayuran Brasika / Bunga',
            'fruit_fresh'=> 'Buah Segar',
            default      => 'Sayuran Daun',
        };
    }

    public function getLatinAttribute(): string
    {
        return match ($this->category) {
            'veg_fruit'  => 'Solanum lycopersicum',
            'veg_flower' => 'Brassica oleracea',
            'fruit_fresh'=> 'Fragaria × ananassa',
            default      => 'Lactuca sativa L.',
        };
    }

    public function getImagePathAttribute(): string
    {
        if (!empty($this->image_url)) {
            $clean = ltrim($this->image_url, '/');
            if (file_exists(public_path($clean))) {
                return $clean;
            }
            if (file_exists(public_path('storage/' . $clean))) {
                return 'storage/' . $clean;
            }
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                return $this->image_url;
            }
        }

        $nameLower = strtolower($this->name ?? '');
        if (str_contains($nameLower, 'selada') || str_contains($nameLower, 'lettuce') || str_contains($nameLower, 'romaine')) {
            return 'lettuce.jpg';
        }
        if (str_contains($nameLower, 'brokoli') || str_contains($nameLower, 'broccoli')) {
            return 'broccoli.jpg';
        }
        if (str_contains($nameLower, 'tomat') || str_contains($nameLower, 'tomato')) {
            return 'tomato.jpg';
        }
        if (str_contains($nameLower, 'stroberi') || str_contains($nameLower, 'strawberry') || str_contains($nameLower, 'berry')) {
            return 'strawberry.jpg';
        }
        if (str_contains($nameLower, 'kol') || str_contains($nameLower, 'cabbage') || str_contains($nameLower, 'kubis')) {
            return 'cabbage.jpg';
        }

        return 'gpaleaves.png';
    }

    public function getFormattedBasePriceAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->base_price ?? 0), 0, ',', '.');
    }

    public function getFormattedMinimumOrderAttribute(): string
    {
        return number_format((float) ($this->minimum_order ?? 0), 0, ',', '.') . ' ' . ($this->unit ?: 'kg');
    }

    public function getAvailableAttribute(): bool
    {
        return $this->stock > 0;
    }

    public function getStockLabelAttribute(): string
    {
        return number_format($this->stock, 0, ',', '.') . ' ' . ($this->unit ?: 'kg');
    }

    public function getStockNoteAttribute(): ?string
    {
        return ($this->stock < 500 && $this->available) ? 'Terbatas' : null;
    }

    public function getStockToneAttribute(): string
    {
        return $this->stock > 500 ? 'safe' : ($this->available ? 'caution' : 'muted');
    }

    public function getMoqAttribute(): float
    {
        return (float) ($this->minimum_order ?? 10);
    }

    public function getStepAttribute(): float
    {
        return max(1, $this->moq);
    }

    public function getInitialQtyAttribute(): float
    {
        return $this->step;
    }

    public function getBadgesAttribute(): array
    {
        return [$this->grade ?: 'Grade A', 'Horeca Standard'];
    }

    public function getPackagingAttribute(): string
    {
        return "Krat Standar {$this->moq} " . ($this->unit ?: 'kg');
    }

    public function getBotanicalAttribute(): string
    {
        return $this->name ?? '';
    }

    public function getSubtitleAttribute(): string
    {
        return "{$this->name} · Franco Gudang Ciwidey";
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->base_price ?? 0);
    }

    public function getPriceLabelAttribute(): string
    {
        return 'Harga Satuan';
    }

    public function getHubCodeAttribute(): string
    {
        return 'GPA-HUB-01';
    }

    public function getOriginAttribute(): string
    {
        return 'Gudang Konsolidasi Ciwidey & Lembang';
    }

    public function getFrancoAttribute(): string
    {
        return 'Franco Gudang Ciwidey';
    }

    public function getColdChainAttribute(): string
    {
        return '4-7°C Cold Storage Room';
    }

    public function getCrateKgAttribute(): int
    {
        return (int) $this->moq;
    }

    public function getPackLabelAttribute(): string
    {
        return 'Krat / Kemasan';
    }

    public function getExtraAttribute(): array
    {
        return [
            'label' => 'Standar Mutu',
            'value' => $this->description ?: 'Bebas Residu & Timbangan Tera Sah',
        ];
    }
}
