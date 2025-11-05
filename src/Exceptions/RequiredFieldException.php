<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Exceptions;

class RequiredFieldException extends ValidationException
{
    public static function create(string $fieldPath, ?string $condition = null): self
    {
        $message = "Required field '{$fieldPath}' is missing";

        if ($condition) {
            $message .= " (required when: {$condition})";
        }

        return new self(
            message: $message,
            fieldPath: $fieldPath,
            ruleName: 'required',
            expectedConstraint: $condition
        );
    }
}
