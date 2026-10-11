<?php

namespace Tests\Feature\Components;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class GpaUploadTest extends TestCase
{
    public function test_renders_native_file_input_with_array_name_when_multiple(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" />
        BLADE);

        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="berkas[]"', $html);
        $this->assertStringContainsString('multiple', $html);
    }

    public function test_uses_plain_name_for_single_upload(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="dokumen" :multiple="false" />
        BLADE);

        $this->assertStringContainsString('name="dokumen"', $html);
        $this->assertStringNotContainsString('name="dokumen[]"', $html);
    }

    public function test_applies_accept_attribute_and_default_hint(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" accept="application/pdf" />
        BLADE);

        $this->assertStringContainsString('accept="application/pdf"', $html);
        $this->assertStringContainsString('8 MB', $html);
        $this->assertStringContainsString('aria-describedby="berkas-hint"', $html);
    }

    public function test_uses_custom_hint_and_size_limit(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" :max-size-mb="2" hint="Maksimal 2 MB" />
        BLADE);

        $this->assertStringContainsString('Maksimal 2 MB', $html);
    }

    public function test_binds_alpine_upload_component_with_config(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" accept="image/*" :multiple="false" :max-size-mb="4" />
        BLADE);

        $this->assertStringContainsString('gpaUpload({', $html);
        $this->assertStringContainsString('"accept":"image/*"', $html);
        $this->assertStringContainsString('"multiple":false', $html);
        $this->assertStringContainsString('"maxSizeMb":4', $html);
    }

    public function test_marks_required_field_for_accessibility(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" label="Unggah KTP" required />
        BLADE);

        $this->assertStringContainsString('Unggah KTP', $html);
        $this->assertStringContainsString('text-danger', $html);
    }

    public function test_supports_drag_and_drop_and_file_list(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" />
        BLADE);

        $this->assertStringContainsString('x-on:drop.prevent="handleDrop($event)"', $html);
        $this->assertStringContainsString('x-on:dragover.prevent', $html);
        $this->assertStringContainsString('x-for="(file, index) in files"', $html);
        $this->assertStringContainsString('x-on:click="remove(index)"', $html);
    }

    public function test_file_list_is_hidden_until_files_selected(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" />
        BLADE);

        $this->assertStringContainsString('x-show="hasFiles"', $html);
        $this->assertStringContainsString('x-cloak', $html);
    }

    public function test_rejection_message_is_announced(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-gpa.upload name="berkas" />
        BLADE);

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('x-text="error"', $html);
    }
}
