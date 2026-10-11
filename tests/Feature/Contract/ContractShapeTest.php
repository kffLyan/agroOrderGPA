<?php

namespace Tests\Feature\Contract;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ContractShapeTest extends TestCase
{
    private const CONTRACT_DIR = __DIR__.'/../../../docs/contracts';

    private const TYPES = [
        'string', 'text', 'int', 'decimal', 'bool', 'datetime', 'date', 'enum', 'json',
    ];

    public static function contractFiles(): array
    {
        $files = glob(self::CONTRACT_DIR.'/*.json');

        if ($files === false || $files === []) {
            return [];
        }

        $cases = [];

        foreach ($files as $file) {
            $cases[basename($file)] = [$file];
        }

        ksort($cases);

        return $cases;
    }

    #[Test]
    #[DataProvider('contractFiles')]
    public function file_has_required_sections(string $file): void
    {
        $contract = $this->decode($file);

        foreach (['object', 'version', 'table', 'description', 'fields', 'example'] as $key) {
            $this->assertArrayHasKey($key, $contract, "$file harus punya bagian $key.");
        }

        $this->assertMatchesRegularExpression('/^[A-Z][A-Za-z]+$/', $contract['object']);
        $this->assertMatchesRegularExpression('/^\d+\.\d+$/', $contract['version']);
        $this->assertMatchesRegularExpression('/^[a-z_]+$/', $contract['table']);
        $this->assertNotEmpty($contract['fields']);
    }

    #[Test]
    #[DataProvider('contractFiles')]
    public function fields_are_unique_snake_case_and_typed(string $file): void
    {
        $contract = $this->decode($file);
        $seen = [];

        foreach ($contract['fields'] as $field) {
            $name = $field['name'] ?? null;

            $this->assertIsString($name, "$file punya field tanpa nama.");
            $this->assertMatchesRegularExpression('/^[a-z][a-z0-9_]*$/', $name, "$file: nama field harus snake_case.");
            $this->assertArrayNotHasKey($name, $seen, "$file: field $name duplikat.");
            $seen[$name] = true;

            $this->assertContains(
                $field['type'] ?? null,
                self::TYPES,
                "$file: field $name punya tipe yang tidak dikenal."
            );
            $this->assertIsBool($field['required'] ?? null, "$file: field $name harus punya required boolean.");
            $this->assertIsBool($field['nullable'] ?? null, "$file: field $name harus punya nullable boolean.");
            $this->assertFalse(
                $field['required'] === true && $field['nullable'] === true,
                "$file: field $name tidak bisa required sekaligus nullable."
            );
        }
    }

    #[Test]
    #[DataProvider('contractFiles')]
    public function every_enum_backed_field_declares_its_values(string $file): void
    {
        $contract = $this->decode($file);
        $enums = $contract['enums'] ?? [];
        $fields = [];

        foreach ($contract['fields'] as $field) {
            $fields[$field['name']] = $field['type'];
        }

        foreach ($enums as $name => $values) {
            $this->assertArrayHasKey($name, $fields, "$file: enum $name tidak punya field.");
            $this->assertSame('enum', $fields[$name], "$file: field $name harus bertipe enum.");
            $this->assertNotEmpty($values, "$file: enum $name kosong.");

            foreach ($values as $value) {
                $this->assertIsString($value);
            }
        }
    }

    #[Test]
    #[DataProvider('contractFiles')]
    public function example_matches_declared_fields(string $file): void
    {
        $contract = $this->decode($file);
        $example = $contract['example'];
        $enums = $contract['enums'] ?? [];

        $this->assertIsArray($example);

        $declared = [];

        foreach ($contract['fields'] as $field) {
            $declared[$field['name']] = $field;

            if (($field['required'] ?? false) === true) {
                $this->assertArrayHasKey($field['name'], $example, "$file: example wajib punya {$field['name']}.");
            }

            if (! array_key_exists($field['name'], $example)) {
                continue;
            }

            $value = $example[$field['name']];

            if ($value === null) {
                $this->assertTrue(
                    $field['nullable'] ?? false,
                    "$file: example punya null pada {$field['name']} yang tidak nullable."
                );

                continue;
            }

            $this->assertTrue(
                $this->typeAccepts($field['type'], $value),
                "$file: contoh {$field['name']} bukan tipe {$field['type']}."
            );

            if ($field['type'] === 'enum') {
                $this->assertContains(
                    $value,
                    $enums[$field['name']] ?? [],
                    "$file: nilai enum {$field['name']} tidak ada di daftar enums."
                );
            }
        }

        $this->assertSame(
            [],
            array_diff(array_keys($example), array_keys($declared)),
            "$file: example punya key yang tidak ada di fields."
        );
    }

    #[Test]
    public function object_names_are_unique(): void
    {
        $names = [];

        foreach (static::contractFiles() as [$file]) {
            $object = $this->decode($file)['object'];
            $this->assertArrayNotHasKey($object, $names, "Objek $object terduplikasi.");
            $names[$object] = $file;
        }

        $this->assertNotEmpty($names);
    }

    #[Test]
    public function readme_documents_every_contract(): void
    {
        $readme = (string) file_get_contents(self::CONTRACT_DIR.'/README.md');

        foreach (static::contractFiles() as [$file]) {
            $this->assertStringContainsString(
                basename($file),
                $readme,
                'README harus mendaftarkan '.basename($file).'.'
            );
        }
    }

    private function decode(string $file): array
    {
        $contract = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($contract, basename($file).' harus berisi JSON object.');

        return $contract;
    }

    private function typeAccepts(string $type, mixed $value): bool
    {
        return match ($type) {
            'bool' => is_bool($value),
            'int' => is_int($value),
            'decimal' => is_int($value) || is_float($value),
            'json' => is_array($value),
            'enum', 'string', 'text' => is_string($value),
            'date' => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1,
            'datetime' => is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $value) === 1,
            default => false,
        };
    }
}
