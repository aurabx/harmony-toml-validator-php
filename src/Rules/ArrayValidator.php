<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\ConstraintViolationException;
use Runbeam\HarmonyTomlValidator\Exceptions\TypeValidationException;

class ArrayValidator implements Validator
{
    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void {
        if ($field->type !== 'array') {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $fieldPath = $context->getFieldPath();
        $count = count($value);

        $minItems = $field->getMinItems();
        if ($minItems !== null && $count < $minItems) {
            throw ConstraintViolationException::minItems($fieldPath, $count, $minItems);
        }

        $maxItems = $field->getMaxItems();
        if ($maxItems !== null && $count > $maxItems) {
            throw ConstraintViolationException::maxItems($fieldPath, $count, $maxItems);
        }

        // Validate array item types if specified
        $itemType = $field->getArrayItemType();
        if ($itemType !== null) {
            $this->validateItemTypes($value, $itemType, $fieldPath);
        }
    }

    /**
     * Validate that all array items match the expected type
     *
     * @param array<mixed> $array
     * @throws TypeValidationException
     */
    private function validateItemTypes(array $array, string $itemType, string $fieldPath): void
    {
        foreach ($array as $index => $item) {
            $itemPath = "{$fieldPath}[{$index}]";

            $isValid = match ($itemType) {
                'string' => is_string($item),
                'integer' => is_int($item),
                'boolean' => is_bool($item),
                'float' => is_float($item) || is_int($item),
                'array' => is_array($item),
                'table' => is_array($item),
                default => true,
            };

            if (!$isValid) {
                throw TypeValidationException::create($itemPath, $itemType, $item);
            }
        }
    }
}
