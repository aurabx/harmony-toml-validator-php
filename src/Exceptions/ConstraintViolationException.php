<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Exceptions;

class ConstraintViolationException extends ValidationException
{
    /**
     * @param array<mixed> $allowedValues
     */
    public static function enum(string $fieldPath, mixed $actualValue, array $allowedValues): self
    {
        $valueStr = is_scalar($actualValue) ? (string) $actualValue : json_encode($actualValue);
        $allowedStr = implode(', ', array_map('strval', $allowedValues));
        $message = "Invalid value '{$valueStr}' for '{$fieldPath}'. Allowed values: {$allowedStr}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'enum',
            actualValue: $actualValue,
            expectedConstraint: $allowedValues
        );
    }

    public static function min(string $fieldPath, int|float $actualValue, int|float $min): self
    {
        $message = "Value {$actualValue} at '{$fieldPath}' is below minimum {$min}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'min',
            actualValue: $actualValue,
            expectedConstraint: $min
        );
    }

    public static function max(string $fieldPath, int|float $actualValue, int|float $max): self
    {
        $message = "Value {$actualValue} at '{$fieldPath}' exceeds maximum {$max}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'max',
            actualValue: $actualValue,
            expectedConstraint: $max
        );
    }

    public static function minItems(string $fieldPath, int $actualCount, int $minItems): self
    {
        $message = "Array at '{$fieldPath}' has {$actualCount} items, minimum is {$minItems}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'min_items',
            actualValue: $actualCount,
            expectedConstraint: $minItems
        );
    }

    public static function maxItems(string $fieldPath, int $actualCount, int $maxItems): self
    {
        $message = "Array at '{$fieldPath}' has {$actualCount} items, maximum is {$maxItems}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'max_items',
            actualValue: $actualCount,
            expectedConstraint: $maxItems
        );
    }

    public static function pattern(string $fieldPath, string $actualValue, string $pattern): self
    {
        $message = "Value '{$actualValue}' at '{$fieldPath}' does not match pattern: {$pattern}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'pattern',
            actualValue: $actualValue,
            expectedConstraint: $pattern
        );
    }
}
