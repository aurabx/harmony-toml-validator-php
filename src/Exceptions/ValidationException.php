<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public function __construct(
        string $message,
        protected readonly string $fieldPath = '',
        protected readonly string $ruleName = '',
        protected readonly mixed $actualValue = null,
        protected readonly mixed $expectedConstraint = null,
    ) {
        parent::__construct($message);
    }

    public function getFieldPath(): string
    {
        return $this->fieldPath;
    }

    public function getRuleName(): string
    {
        return $this->ruleName;
    }

    public function getActualValue(): mixed
    {
        return $this->actualValue;
    }

    public function getExpectedConstraint(): mixed
    {
        return $this->expectedConstraint;
    }

    /**
     * Format the validation error with full context
     */
    public function getFormattedError(): string
    {
        $parts = [$this->getMessage()];

        if ($this->fieldPath) {
            $parts[] = "Field: {$this->fieldPath}";
        }

        if ($this->ruleName) {
            $parts[] = "Rule: {$this->ruleName}";
        }

        if ($this->actualValue !== null) {
            $value = is_scalar($this->actualValue) 
                ? $this->actualValue 
                : json_encode($this->actualValue);
            $parts[] = "Actual: {$value}";
        }

        if ($this->expectedConstraint !== null) {
            $expected = is_scalar($this->expectedConstraint) 
                ? $this->expectedConstraint 
                : json_encode($this->expectedConstraint);
            $parts[] = "Expected: {$expected}";
        }

        return implode(' | ', $parts);
    }
}
