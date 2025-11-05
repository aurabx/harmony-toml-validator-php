<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\ValidationException;

interface Validator
{
    /**
     * Validate a value against a schema field
     *
     * @param mixed $value The value to validate
     * @throws ValidationException
     */
    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void;
}
