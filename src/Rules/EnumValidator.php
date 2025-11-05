<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\ConstraintViolationException;

class EnumValidator implements Validator
{
    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void {
        $enum = $field->getEnum();
        if ($enum === null) {
            return;
        }

        if (!in_array($value, $enum, strict: true)) {
            throw ConstraintViolationException::enum(
                $context->getFieldPath(),
                $value,
                $enum
            );
        }
    }
}
