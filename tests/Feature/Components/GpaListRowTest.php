<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaListRowTest extends TestCase
{
    public function test_renders_div_when_no_href_is_given(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.list-row title="Paket nanomaterials" subtitle="Berat aktual 120 kg" />
        BLADE);

        $this->assertStringContainsString('<div', $html);
        $this->assertStringNotContainsString('href=', $html);
        $this->assertStringContainsString('Paket nanomaterials', $html);
        $this->assertStringContainsString('Berat aktual 120 kg', $html);
    }

    public function test_renders_anchor_with_chevron_when_href_is_given(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.list-row href="/klien/pesanan/GPA-2401-0007" title="GPA-2401-0007" icon="package" />
        BLADE);

        $this->assertStringContainsString('href="/klien/pesanan/GPA-2401-0007"', $html);
        $this->assertStringContainsString('GPA-2401-0007', $html);
    }

    public function test_renders_badge_from_array_or_slot(): void
    {
        $array = Blade::render(<<<'BLADE'
            <x-gpa.list-row title="A" :badge="['label' => 'Terkirim', 'tone' => 'success']" />
        BLADE);

        $this->assertStringContainsString('Terkirim', $array);
        $this->assertStringContainsString('text-success', $array);

        $slot = Blade::render(<<<'BLADE'
            <x-gpa.list-row title="A">
                <x-slot:badge>Menunggu</x-slot:badge>
            </x-gpa.list-row>
        BLADE);

        $this->assertStringContainsString('Menunggu', $slot);
    }

    public function test_renders_meta_labels_and_values(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.list-row
                title="Selada Romaine"
                :meta="[['label' => 'Berat', 'value' => '120 kg'], ['label' => 'Harga', 'value' => 'Rp 18.500']]" />
        BLADE);

        $this->assertStringContainsString('Berat', $html);
        $this->assertStringContainsString('120 kg', $html);
        $this->assertStringContainsString('Harga', $html);
        $this->assertStringContainsString('Rp 18.500', $html);
    }

    public function test_escapes_slot_and_subtitle_content(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.list-row title="Judul <b>tebal</b>" subtitle="<script>alert(1)</script>" />
        BLADE);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
