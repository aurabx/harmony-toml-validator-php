<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Exceptions;

class TypeValidationException extends ValidationException
{
    public static function create(
        string $fieldPath,
        string $expectedType,
        mixed $actualValue
    ): self {
        $actualType = get_debug_type($actualValue);
        $message = "Type mismatch at '{$fieldPath}': expected {$expectedType}, got {$actualType}";

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'type',
            actualValue: $actualType,
            expectedConstraint: $expectedType
        );
    }
}
