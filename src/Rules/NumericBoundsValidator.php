<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\ConstraintViolationException;

class NumericBoundsValidator implements Validator
{
    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void {
        // Only validate if value is numeric
        if (!is_numeric($value)) {
            return;
        }

        $numericValue = is_int($value) ? $value : (float) $value;
        $fieldPath = $context->getFieldPath();

        $min = $field->getMin();
        if ($min !== null && $numericValue < $min) {
            throw ConstraintViolationException::min($fieldPath, $numericValue, $min);
        }

        $max = $field->getMax();
        if ($max !== null && $numericValue > $max) {
            throw ConstraintViolationException::max($fieldPath, $numericValue, $max);
        }
    }
}
