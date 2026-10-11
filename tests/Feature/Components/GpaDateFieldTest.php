<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class GpaDateFieldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        View::share('errors', new ViewErrorBag);
    }

    public function test_renders_date_input_bound_to_name(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal_pengambilan" label="Tanggal Pengambilan" value="2026-03-10" />
        BLADE);

        $this->assertStringContainsString('type="date"', $html);
        $this->assertStringContainsString('name="tanggal_pengambilan"', $html);
        $this->assertStringContainsString('value="2026-03-10"', $html);
        $this->assertStringContainsString('Tanggal Pengambilan', $html);
    }

    public function test_generates_matching_id_and_label_for(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal_pengambilan" label="Tanggal" />
        BLADE);

        $this->assertStringContainsString('id="date-tanggal_pengambilan"', $html);
        $this->assertStringContainsString('for="date-tanggal_pengambilan"', $html);
    }

    public function test_respects_custom_id(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" id="custom-date" />
        BLADE);

        $this->assertStringContainsString('id="custom-date"', $html);
        $this->assertStringContainsString('for="custom-date"', $html);
    }

    public function test_applies_min_and_max_boundaries(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" min="2026-01-01" max="2026-12-31" />
        BLADE);

        $this->assertStringContainsString('min="2026-01-01"', $html);
        $this->assertStringContainsString('max="2026-12-31"', $html);
    }

    public function test_marks_required_field_for_accessibility(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" required />
        BLADE);

        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('(wajib diisi)', $html);
        $this->assertStringContainsString('text-danger', $html);
    }

    public function test_renders_time_window_select_when_windows_given(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field
                name="tanggal_pengambilan"
                label="Tanggal"
                :windows="['06:00-09:00', '09:00-12:00']"
                window-name="jendela_pengambilan"
                window-value="09:00-12:00" />
        BLADE);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('name="jendela_pengambilan"', $html);
        $this->assertStringContainsString('06:00-09:00', $html);
        $this->assertStringContainsString('09:00-12:00', $html);
    }

    public function test_marks_selected_time_window(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field
                name="tanggal"
                label="Tanggal"
                :windows="['Pagi', 'Siang']"
                window-name="jendela"
                window-value="Siang" />
        BLADE);

        $this->assertMatchesRegularExpression('/<option value="Siang"\s+selected/', $html);
        $this->assertStringNotContainsString('value="Pagi" selected', $html);
    }

    public function test_supports_key_value_windows(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field
                name="tanggal"
                label="Tanggal"
                :windows="['pagi' => '06:00-09:00']"
                window-name="jendela"
                window-value="pagi" />
        BLADE);

        $this->assertStringContainsString('value="pagi"', $html);
        $this->assertStringContainsString('06:00-09:00', $html);
    }

    public function test_omits_select_when_no_windows_given(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" />
        BLADE);

        $this->assertStringNotContainsString('<select', $html);
    }

    public function test_shows_hint_when_no_validation_error(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" hint="H-1 sebelum pengiriman" />
        BLADE);

        $this->assertStringContainsString('H-1 sebelum pengiriman', $html);
        $this->assertStringContainsString('gpa-hint', $html);
    }

    public function test_renders_validation_error_instead_of_hint(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" hint="H-1 sebelum pengiriman" />
        BLADE);

        $this->assertStringContainsString('gpa-hint', $html);

        View::share('errors', $this->errorBag(['tanggal' => 'Tanggal wajib diisi.']));

        $withError = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="Tanggal" hint="H-1 sebelum pengiriman" />
        BLADE);

        $this->assertStringNotContainsString('H-1 sebelum pengiriman', $withError);
        $this->assertStringContainsString('Tanggal wajib diisi.', $withError);
        $this->assertStringContainsString('aria-invalid="true"', $withError);
    }

    public function test_reports_time_window_error_separately(): void
    {
        View::share('errors', $this->errorBag(['jendela' => 'Pilih jendela pengambilan.']));

        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field
                name="tanggal" label="Tanggal"
                :windows="['Pagi', 'Siang']" window-name="jendela" />
        BLADE);

        $this->assertStringContainsString('Pilih jendela pengambilan.', $html);
        $this->assertStringContainsString('date-jendela-error', $html);
    }

    private function errorBag(array $messages): ViewErrorBag
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag($messages));

        return $bag;
    }

    public function test_escapes_label_copy(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.date-field name="tanggal" label="<script>alert(1)</script>" />
        BLADE);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
