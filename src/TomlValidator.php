<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator;

use Runbeam\HarmonyTomlValidator\Schema\SchemaLoader;
use Runbeam\HarmonyTomlValidator\Schema\SchemaDefinition;
use Runbeam\HarmonyTomlValidator\Schema\SchemaTable;
use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\Rules\TypeValidator;
use Runbeam\HarmonyTomlValidator\Rules\RequiredValidator;
use Runbeam\HarmonyTomlValidator\Rules\EnumValidator;
use Runbeam\HarmonyTomlValidator\Rules\NumericBoundsValidator;
use Runbeam\HarmonyTomlValidator\Rules\ArrayValidator;
use Runbeam\HarmonyTomlValidator\Rules\PatternValidator;
use Runbeam\HarmonyTomlValidator\Exceptions\ValidationException;
use Yosymfony\Toml\Toml;

class TomlValidator
{
    private SchemaLoader $schemaLoader;

    public function __construct()
    {
        $this->schemaLoader = new SchemaLoader();
    }

    /**
     * Validate a TOML configuration file against a schema file
     *
     * @throws ValidationException
     */
    public function validateFile(string $configPath, string $schemaPath): void
    {
        if (!file_exists($configPath)) {
            throw new ValidationException("Config file not found: {$configPath}");
        }

        $content = file_get_contents($configPath);
        if ($content === false) {
            throw new ValidationException("Cannot read config file: {$configPath}");
        }

        $this->validateContent($content, $schemaPath);
    }

    /**
     * Validate TOML content against a schema file
     *
     * @throws ValidationException
     */
    public function validateContent(string $content, string $schemaPath): void
    {
        try {
            $parsed = Toml::parse($content);
        } catch (\Throwable $e) {
            throw new ValidationException("Invalid TOML: {$e->getMessage()}");
        }

        $schema = $this->schemaLoader->loadFromFile($schemaPath);
        $this->validateArray($parsed, $schema);
    }

    /**
     * Validate a parsed array against a schema definition
     *
     * @param array<string, mixed> $config
     * @throws ValidationException
     */
    public function validateArray(array $config, SchemaDefinition $schema): void
    {
        $context = new ValidationContext();

        // Validate all top-level tables
        foreach ($schema->getAllTables() as $tableSchema) {
            if ($tableSchema->isPattern()) {
                // For pattern tables, check all matching instances in config
                foreach ($config as $tableName => $tableData) {
                    if (is_array($tableData) && $tableSchema->matches($tableName)) {
                        $context->withPath($tableName, function () use ($tableData, $tableSchema, $context): void {
                            $this->validateTable($tableData, $tableSchema, $context);
                        });
                    }
                }
            } else {
                // For fixed tables, check if present in config
                if (isset($config[$tableSchema->name])) {
                    $context->withPath($tableSchema->name, function () use ($config, $tableSchema, $context): void {
                        $this->validateTable($config[$tableSchema->name], $tableSchema, $context);
                    });
                } elseif ($tableSchema->required) {
                    throw new ValidationException(
                        "Required table '{$tableSchema->name}' is missing"
                    );
                }
            }
        }
    }

    /**
     * Validate a table instance against a table schema
     *
     * @param mixed $tableData
     * @throws ValidationException
     */
    private function validateTable(mixed $tableData, SchemaTable $tableSchema, ValidationContext $context): void
    {
        if (!is_array($tableData)) {
            throw new ValidationException(
                "Table '{$context->getFieldPath()}' must be an object/table, got " . get_debug_type($tableData)
            );
        }

        // Validate all fields defined in schema
        foreach ($tableSchema->fields as $fieldSchema) {
            $fieldName = $fieldSchema->name;
            $value = $tableData[$fieldName] ?? null;

            $context->withPath($fieldName, function () use ($value, $fieldSchema, $tableData, $context): void {
                $this->validateField($value, $fieldSchema, $tableData, $context);
            });
        }
    }

    /**
     * Validate a single field value against a field schema
     *
     * @param mixed $value
     * @param array<string, mixed> $tableData
     * @throws ValidationException
     */
    private function validateField(
        mixed $value,
        SchemaField $fieldSchema,
        array $tableData,
        ValidationContext $context
    ): void {
        // Check if field is missing
        if (!isset($tableData[$fieldSchema->name])) {
            $requiredValidator = new RequiredValidator($tableData);
            $requiredValidator->validate(null, $fieldSchema, $context);
            return;
        }

        // Run all validators
        $validators = [
            new TypeValidator(),
            new RequiredValidator($tableData),
            new EnumValidator(),
            new NumericBoundsValidator(),
            new ArrayValidator(),
            new PatternValidator(),
        ];

        foreach ($validators as $validator) {
            $validator->validate($value, $fieldSchema, $context);
        }
    }
}
