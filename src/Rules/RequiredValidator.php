<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Rules;

use Runbeam\HarmonyTomlValidator\Schema\SchemaField;
use Runbeam\HarmonyTomlValidator\ValidationContext;
use Runbeam\HarmonyTomlValidator\Exceptions\RequiredFieldException;

class RequiredValidator implements Validator
{
    /**
     * @param array<string, mixed> $allValues All values in the current context for required_if evaluation
     */
    public function __construct(
        private readonly array $allValues = []
    ) {
    }

    public function validate(
        mixed $value,
        SchemaField $field,
        ValidationContext $context
    ): void {
        // Skip if value is present
        if ($value !== null && $value !== '') {
            return;
        }

        // Check required_if condition first
        if ($field->requiredIf !== null) {
            if (!$this->evaluateCondition($field->requiredIf)) {
                return;
            }
            throw RequiredFieldException::create($context->getFieldPath(), $field->requiredIf);
        }

        // Check if field is required
        if ($field->required) {
            throw RequiredFieldException::create($context->getFieldPath());
        }
    }

    /**
     * Evaluate a condition like "enabled == true" or "backend == 's3'"
     */
    private function evaluateCondition(string $condition): bool
    {
        // Simple parser for conditions like "field == value"
        if (preg_match('/^(\w+)\s*==\s*(.+)$/', trim($condition), $matches)) {
            $fieldName = $matches[1];
            $expectedValue = trim($matches[2], '\'"');

            if (!isset($this->allValues[$fieldName])) {
                return false;
            }

            $actualValue = $this->allValues[$fieldName];

            // Handle different value types
            if ($expectedValue === 'true') {
                return $actualValue === true;
            } elseif ($expectedValue === 'false') {
                return $actualValue === false;
            } else {
                return (string) $actualValue === $expectedValue;
            }
        }

        // Simple check for "field exists"
        if (preg_match('/^(\w+)\s+exists$/', trim($condition), $matches)) {
            $fieldName = $matches[1];
            return isset($this->allValues[$fieldName]);
        }

        return false;
    }
}
