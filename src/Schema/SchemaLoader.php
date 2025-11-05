<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Schema;

use Runbeam\HarmonyTomlValidator\Exceptions\SchemaLoadException;
use Yosymfony\Toml\Toml;

class SchemaLoader
{
    /**
     * Load and parse a schema file
     *
     * @throws SchemaLoadException
     */
    public function loadFromFile(string $filePath): SchemaDefinition
    {
        if (!file_exists($filePath)) {
            throw new SchemaLoadException("Schema file not found: {$filePath}", $filePath);
        }

        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new SchemaLoadException("Cannot read schema file: {$filePath}", $filePath);
            }
            return $this->loadFromContent($content, $filePath);
        } catch (SchemaLoadException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new SchemaLoadException(
                "Error loading schema file {$filePath}: {$e->getMessage()}",
                $filePath
            );
        }
    }

    /**
     * Load and parse schema TOML content
     *
     * @throws SchemaLoadException
     */
    public function loadFromContent(string $content, ?string $filePath = null): SchemaDefinition
    {
        try {
            $parsed = Toml::parse($content);
        } catch (\Throwable $e) {
            throw new SchemaLoadException(
                "Invalid TOML in schema: {$e->getMessage()}",
                $filePath ?? 'unknown'
            );
        }

        return $this->buildSchemaDefinition($parsed);
    }

    /**
     * Build SchemaDefinition from parsed TOML
     *
     * @param array<string, mixed> $parsed
     * @throws SchemaLoadException
     */
    private function buildSchemaDefinition(array $parsed): SchemaDefinition
    {
        $metadata = $parsed['schema'] ?? [];
        $tables = [];

        if (!isset($parsed['table']) || !is_array($parsed['table'])) {
            throw new SchemaLoadException('Schema must contain [[table]] definitions');
        }

        foreach ($parsed['table'] as $tableData) {
            $tables[] = $this->parseTable($tableData);
        }

        return new SchemaDefinition($tables, $metadata);
    }

    /**
     * Parse a single table definition
     *
     * @param array<string, mixed> $tableData
     * @throws SchemaLoadException
     */
    private function parseTable(array $tableData): SchemaTable
    {
        $name = $tableData['name'] ?? null;
        if (!is_string($name)) {
            throw new SchemaLoadException('Table definition must have a "name" field');
        }

        $fields = [];
        if (isset($tableData['field']) && is_array($tableData['field'])) {
            foreach ($tableData['field'] as $fieldData) {
                $fields[] = $this->parseField($fieldData);
            }
        }

        return new SchemaTable(
            name: $name,
            fields: $fields,
            required: (bool) ($tableData['required'] ?? false),
            pattern: (bool) ($tableData['pattern'] ?? false),
            patternConstraint: $tableData['pattern_constraint'] ?? null,
            description: $tableData['description'] ?? null,
        );
    }

    /**
     * Parse a single field definition
     *
     * @param array<string, mixed> $fieldData
     * @throws SchemaLoadException
     */
    private function parseField(array $fieldData): SchemaField
    {
        $name = $fieldData['name'] ?? null;
        if (!is_string($name)) {
            throw new SchemaLoadException('Field definition must have a "name" field');
        }

        $type = $fieldData['type'] ?? null;
        if (!is_string($type)) {
            throw new SchemaLoadException("Field {$name} must have a \"type\" field");
        }

        $constraints = $this->extractConstraints($fieldData);

        return new SchemaField(
            name: $name,
            type: $type,
            required: (bool) ($fieldData['required'] ?? false),
            requiredIf: $fieldData['required_if'] ?? null,
            default: $fieldData['default'] ?? null,
            constraints: $constraints,
        );
    }

    /**
     * Extract all constraints from field data
     *
     * @param array<string, mixed> $fieldData
     * @return array<string, mixed>
     */
    private function extractConstraints(array $fieldData): array
    {
        $constraints = [];
        $constraintKeys = [
            'enum',
            'min',
            'max',
            'min_items',
            'max_items',
            'pattern',
            'array_item_type',
        ];

        foreach ($constraintKeys as $key) {
            if (isset($fieldData[$key])) {
                $constraints[$key] = $fieldData[$key];
            }
        }

        return $constraints;
    }
}
