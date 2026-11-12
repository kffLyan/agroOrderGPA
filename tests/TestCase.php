<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        $testSettings = storage_path('app/governance_settings_testing.json');
        if (file_exists($testSettings)) {
            @unlink($testSettings);
        }

        parent::tearDown();
    }
}
