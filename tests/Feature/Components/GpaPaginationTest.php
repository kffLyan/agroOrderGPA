<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaPaginationTest extends TestCase
{
    public function test_renders_nothing_when_single_page(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="1" :last="1" :total="4" :per-page="10" />
        BLADE);

        $this->assertStringNotContainsString('<nav', $html);
    }

    public function test_reports_record_range_and_total(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="2" :last="5" :total="48" :per-page="10" unit="pesanan aktif" />
        BLADE);

        $this->assertStringContainsString('11&ndash;20', $html);
        $this->assertStringContainsString('48', $html);
        $this->assertStringContainsString('pesanan aktif', $html);
    }

    public function test_marks_current_page_with_aria_current(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="3" :last="5" :total="48" :per-page="10" />
        BLADE);

        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('>3</span>', $html);
    }

    public function test_builds_links_from_url_pattern(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination
                :current="2" :last="4" :total="40" :per-page="10"
                url-pattern="/klien/pesanan?page=:page" />
        BLADE);

        $this->assertStringContainsString('/klien/pesanan?page=1', $html);
        $this->assertStringContainsString('/klien/pesanan?page=3', $html);
        $this->assertStringContainsString('rel="prev"', $html);
        $this->assertStringContainsString('rel="next"', $html);
    }

    public function test_disables_previous_on_first_page_and_next_on_last_page(): void
    {
        $first = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="1" :last="4" :total="40" :per-page="10" url-pattern="/x?page=:page" />
        BLADE);

        $this->assertStringNotContainsString('rel="prev"', $first);
        $this->assertStringContainsString('rel="next"', $first);

        $last = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="4" :last="4" :total="40" :per-page="10" url-pattern="/x?page=:page" />
        BLADE);

        $this->assertStringContainsString('rel="prev"', $last);
        $this->assertStringNotContainsString('rel="next"', $last);
    }

    public function test_collapses_long_ranges_with_ellipsis(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="10" :last="20" :total="200" :per-page="10" />
        BLADE);

        $this->assertStringContainsString('&hellip;', $html);
        $this->assertStringContainsString('>1</span>', $html);
        $this->assertStringContainsString('>20</span>', $html);
        $this->assertStringContainsString('>9</span>', $html);
        $this->assertStringContainsString('>11</span>', $html);
    }

    public function test_shows_every_page_for_short_ranges(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="3" :last="7" :total="70" :per-page="10" />
        BLADE);

        $this->assertStringNotContainsString('&hellip;', $html);
    }

    public function test_handles_zero_total_without_broken_range(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="1" :last="1" :total="0" :per-page="10" />
        BLADE);

        $this->assertStringNotContainsString('<nav', $html);
    }

    public function test_clamps_current_page_above_last_page(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="9" :last="3" :total="30" :per-page="10" />
        BLADE);

        $this->assertStringContainsString('>3</span>', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    public function test_has_navigation_landmark_label(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.pagination :current="2" :last="5" :total="48" :per-page="10" label="Navigasi halaman pesanan" />
        BLADE);

        $this->assertStringContainsString('aria-label="Navigasi halaman pesanan"', $html);
        $this->assertStringContainsString('role="navigation"', $html);
    }
}
