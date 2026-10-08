<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaModalTest extends TestCase
{
    public function test_renders_dialog_semantics_and_title_linkage(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.modal name="detail-pesanan" title="Rincian Pesanan" description="Data berat aktual">
                <p>Isi dialog</p>
            </x-gpa.modal>
        BLADE);

        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-modal="true"', $html);
        $this->assertStringContainsString('aria-labelledby="detail-pesanan-title"', $html);
        $this->assertStringContainsString('aria-describedby="detail-pesanan-description"', $html);
        $this->assertStringContainsString('Rincian Pesanan', $html);
        $this->assertStringContainsString('Isi dialog', $html);
    }

    public function test_opens_and_closes_through_custom_window_events(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.modal name="detail-pesanan" title="Rincian" />
        BLADE);

        $this->assertStringContainsString('x-on:gpa-modal-open.window', $html);
        $this->assertStringContainsString('x-on:gpa-modal-close.window', $html);
        $this->assertStringContainsString("'detail-pesanan'", $html);
    }

    public function test_supports_escape_key_and_scroll_lock(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.modal name="detail-pesanan" title="Rincian" />
        BLADE);

        $this->assertStringContainsString('x-on:keydown.escape.window="open = false"', $html);
        $this->assertStringContainsString("document.body.classList.toggle('overflow-hidden', open)", $html);
    }

    public function test_hides_markup_until_opened(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.modal name="detail-pesanan" title="Rincian" />
        BLADE);

        $this->assertStringContainsString('x-cloak', $html);
    }

    public function test_close_button_is_labelled(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.modal name="detail-pesanan" title="Rincian" />
        BLADE);

        $this->assertStringContainsString('aria-label="Tutup dialog"', $html);
        $this->assertStringContainsString('x-on:click="open = false"', $html);
    }

    public function test_backdrop_close_can_be_disabled(): void
    {
        $open = Blade::render(<<<'BLADE'
            <x-gpa.modal name="konfirmasi" title="Konfirmasi" :close-on-backdrop="false" />
        BLADE);

        $this->assertStringNotContainsString('x-on:click="open = false"', substr($open, 0, (int) strpos($open, 'role="dialog"')));

        $closable = Blade::render(<<<'BLADE'
            <x-gpa.modal name="konfirmasi" title="Konfirmasi" />
        BLADE);

        $this->assertStringContainsString('x-on:click="open = false"', substr($closable, 0, (int) strpos($closable, 'role="dialog"')));
    }

    public function test_renders_footer_slot_and_size_variant(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.modal name="detail" title="Rincian" size="xl">
                <x-slot:footer>
                    <x-gpa.btn variant="primary" size="sm">Simpan</x-gpa.btn>
                </x-slot:footer>
            </x-gpa.modal>
        BLADE);

        $this->assertStringContainsString('sm:max-w-4xl', $html);
        $this->assertStringContainsString('Simpan', $html);
    }
}
