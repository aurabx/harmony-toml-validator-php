<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\TypeValidationException;

class TypeValidator implements Validator
{
    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void {
        $this->validateType($value, $field->type, $context->getFieldPath());
    }

    /**
     * Validate that a value matches the expected type
     *
     * @throws TypeValidationException
     */
    private function validateType(mixed $value, string $expectedType, string $fieldPath): void
    {
        $isValid = match ($expectedType) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'boolean' => is_bool($value),
            'float' => is_float($value) || is_int($value),
            'array' => is_array($value),
            'table' => is_array($value),
            default => throw new TypeValidationException(
                "Unknown type: {$expectedType}",
                $fieldPath,
                $expectedType,
                $value
            ),
        };

        if (!$isValid) {
            throw TypeValidationException::create($fieldPath, $expectedType, $value);
        }
    }
}
