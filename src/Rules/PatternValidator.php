<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\ConstraintViolationException;

class PatternValidator implements Validator
{
    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void {
        $pattern = $field->getPattern();
        if ($pattern === null) {
            return;
        }

        if (!is_string($value)) {
            return;
        }

        if (!preg_match('/' . $pattern . '/', $value)) {
            throw ConstraintViolationException::pattern(
                $context->getFieldPath(),
                $value,
                $pattern
            );
        }
    }
}
