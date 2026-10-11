<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaTableTest extends TestCase
{
    public function test_renders_header_cells_for_every_column(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table
                :columns="[
                    ['key' => 'sku', 'label' => 'SKU'],
                    ['key' => 'berat', 'label' => 'Berat', 'numeric' => true],
                ]"
                :rows="[['sku' => 'VEG-ROM-01', 'berat' => 120]]"
                caption="Daftar produk" />
        BLADE);

        $this->assertStringContainsString('<table', $html);
        $this->assertStringContainsString('SKU', $html);
        $this->assertStringContainsString('Berat', $html);
        $this->assertStringContainsString('VEG-ROM-01', $html);
        $this->assertStringContainsString('120', $html);
        $this->assertStringContainsString('Daftar produk', $html);
    }

    public function test_escapes_cell_values_by_default(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table :columns="[['key' => 'catatan']]" :rows="[['catatan' => '<script>alert(1)</script>']]" />
        BLADE);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_renders_untrusted_html_only_when_column_opts_in(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table
                :columns="[['key' => 'status', 'raw' => true]]"
                :rows="[['status' => '<span class="x">Aktif</span>']]" />
        BLADE);

        $this->assertStringContainsString('<span class="x">Aktif</span>', $html);
    }

    public function test_uses_closure_renderer_for_rich_cells(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table
                :columns="[
                    ['key' => 'status', 'render' => fn ($row) => '<b>'.$row['status'].'</b>'],
                ]"
                :rows="[['status' => 'Terkirim']]" />
        BLADE);

        $this->assertStringContainsString('<b>Terkirim</b>', $html);
    }

    public function test_falls_back_to_dash_for_missing_values(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table :columns="[['key' => 'catatan']]" :rows="[['catatan' => null]]" />
        BLADE);

        $this->assertStringContainsString('&mdash;', $html);
    }

    public function test_shows_empty_state_when_rows_are_missing(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table :columns="[['key' => 'sku', 'label' => 'SKU']]" :rows="[]" empty="Belum ada produk." />
        BLADE);

        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringContainsString('Belum ada produk.', $html);
    }

    public function test_applies_row_key_anchor_and_extra_row_class(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table
                :columns="[['key' => 'sku', 'label' => 'SKU']]"
                :rows="[['id' => 7, 'sku' => 'VEG-ROM-01', '_class' => 'bg-accent']]"
                row-key="id" />
        BLADE);

        $this->assertStringContainsString('id="row-7"', $html);
        $this->assertStringContainsString('bg-accent', $html);
    }

    public function test_numeric_columns_align_right_in_monospace(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.table :columns="[['key' => 'harga', 'numeric' => true]]" :rows="[['harga' => 15000]]" />
        BLADE);

        $this->assertStringContainsString('gpa-mono-xs', $html);
        $this->assertStringContainsString('text-right', $html);
    }
}
