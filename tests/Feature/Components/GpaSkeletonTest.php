<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaSkeletonTest extends TestCase
{
    public function test_renders_requested_number_of_lines_by_default(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.skeleton :lines="5" />
        BLADE);

        $this->assertSame(5, substr_count($html, 'animate-pulse'));
    }

    public function test_is_hidden_from_assistive_technology(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.skeleton :lines="2" />
        BLADE);

        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_table_variant_renders_header_strip_and_row_count(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.skeleton variant="table" :rows="4" />
        BLADE);

        $this->assertSame(4, substr_count($html, 'border-t border-line-faint'));
        $this->assertStringContainsString('h-9 bg-surface-raised', $html);
    }

    public function test_card_variant_wraps_each_row_in_panel(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.skeleton variant="card" :rows="3" />
        BLADE);

        $this->assertSame(3, substr_count($html, 'gpa-panel p-4'));
    }

    public function test_block_variant_renders_single_block(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.skeleton variant="block" />
        BLADE);

        $this->assertStringContainsString('h-24 w-full', $html);
        $this->assertSame(1, substr_count($html, 'animate-pulse'));
    }

    public function test_animation_can_be_disabled(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.skeleton :lines="3" :animated="false" />
        BLADE);

        $this->assertStringNotContainsString('animate-pulse', $html);
    }
}
