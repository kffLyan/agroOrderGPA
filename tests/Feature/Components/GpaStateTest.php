<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaStateTest extends TestCase
{
    public function test_ready_state_renders_slot_only(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="ready"><p>Data pesanan</p></x-gpa.state>
        BLADE);

        $this->assertStringContainsString('Data pesanan', $html);
        $this->assertStringNotContainsString('Belum ada data', $html);
        $this->assertStringNotContainsString('Gagal memuat data', $html);
    }

    public function test_loading_state_announces_status_and_renders_skeleton(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="loading" :rows="4" />
        BLADE);

        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('Memuat data, mohon tunggu.', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_loading_state_uses_single_skeleton_for_short_data(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="loading" :rows="1" :lines="3" />
        BLADE);

        $this->assertStringNotContainsString('h-9 bg-surface-raised', $html);
        $this->assertSame(3, substr_count($html, 'animate-pulse'));
    }

    public function test_empty_state_shows_default_heading_and_description(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="empty" />
        BLADE);

        $this->assertStringContainsString('Belum ada data', $html);
        $this->assertStringContainsString('border-dashed', $html);
        $this->assertStringNotContainsString('role="alert"', $html);
    }

    public function test_empty_state_accepts_custom_copy_and_icon(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="empty" title="Belum ada galeri" description="Unggah foto kegiatan" icon="camera" />
        BLADE);

        $this->assertStringContainsString('Belum ada galeri', $html);
        $this->assertStringContainsString('Unggah foto kegiatan', $html);
    }

    public function test_empty_state_renders_action_slot(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="empty">
                <x-slot:action>
                    <x-gpa.btn href="/klien/keranjang" variant="primary" size="sm">Mulai Pesan</x-gpa.btn>
                </x-slot:action>
            </x-gpa.state>
        BLADE);

        $this->assertStringContainsString('Mulai Pesan', $html);
    }

    public function test_error_state_is_alert_with_retry_action(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="error" />
        BLADE);

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Gagal memuat data', $html);
        $this->assertStringContainsString('Muat Ulang Data', $html);
        $this->assertStringContainsString('text-danger', $html);
    }

    public function test_error_state_uses_custom_retry_label(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="error" retry-label="Coba Lagi" />
        BLADE);

        $this->assertStringContainsString('Coba Lagi', $html);
    }

    public function test_escapes_custom_copy(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.state state="empty" title="<script>alert(1)</script>" />
        BLADE);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
