<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaTabsTest extends TestCase
{
    public function test_renders_tablist_with_linked_panels(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs
                name="dokumentasi"
                :tabs="[
                    ['id' => 'semua', 'label' => 'Semua'],
                    ['id' => 'legalitas', 'label' => 'Legalitas'],
                ]" />
        BLADE);

        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('role="tab"', $html);
        $this->assertStringContainsString('role="tabpanel"', $html);
        $this->assertStringContainsString('aria-controls="dokumentasi-panel-legalitas"', $html);
        $this->assertStringContainsString('aria-labelledby="dokumentasi-tab-semua"', $html);
    }

    public function test_marks_first_tab_as_selected_by_default(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs :tabs="[['id' => 'semua', 'label' => 'Semua'], ['id' => 'legalitas', 'label' => 'Legalitas']]" />
        BLADE);

        $this->assertStringContainsString("active: 'semua'", $html);
        $this->assertStringContainsString('x-on:click="active = \'semua\'"', $html);
    }

    public function test_respects_explicit_active_tab(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs active="legalitas" :tabs="[['id' => 'semua'], ['id' => 'legalitas']]" />
        BLADE);

        $this->assertStringContainsString("active: 'legalitas'", $html);
    }

    public function test_falls_back_to_first_tab_when_active_is_unknown(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs active="tidak-ada" :tabs="[['id' => 'semua'], ['id' => 'legalitas']]" />
        BLADE);

        $this->assertStringContainsString("active: 'semua'", $html);
    }

    public function test_supports_keyboard_navigation(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs :tabs="[['id' => 'semua'], ['id' => 'legalitas']]" />
        BLADE);

        $this->assertStringContainsString('@keydown.right.prevent="move(1)"', $html);
        $this->assertStringContainsString('@keydown.left.prevent="move(-1)"', $html);
    }

    public function test_renders_panel_content_from_closure(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs
                :tabs="[
                    ['id' => 'semua', 'panel' => fn () => '<p>Panel semua</p>'],
                    ['id' => 'legalitas', 'panel' => fn () => '<p>Panel legalitas</p>'],
                ]" />
        BLADE);

        $this->assertStringContainsString('<p>Panel semua</p>', $html);
        $this->assertStringContainsString('<p>Panel legalitas</p>', $html);
    }

    public function test_shows_count_badge_only_when_count_provided(): void
    {
        $withCount = Blade::render(<<<'BLADE'
            <x-gpa.tabs :tabs="[['id' => 'semua', 'label' => 'Semua', 'count' => 12]]" />
        BLADE);

        $this->assertStringContainsString('12', $withCount);

        $withoutCount = Blade::render(<<<'BLADE'
            <x-gpa.tabs :tabs="[['id' => 'semua', 'label' => 'Semua']]" />
        BLADE);

        $this->assertStringNotContainsString('>0<', $withoutCount);
    }

    public function test_underline_variant_is_available(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs variant="underline" :tabs="[['id' => 'semua', 'label' => 'Semua']]" />
        BLADE);

        $this->assertStringContainsString('border-b-2', $html);
    }

    public function test_renders_nothing_when_no_tabs_supplied(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.tabs :tabs="[]" />
        BLADE);

        $this->assertStringNotContainsString('role="tablist"', $html);
    }
}
