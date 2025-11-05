<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Schema;

class SchemaField
{
    /**
     * @param array<string, mixed> $constraints
     */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly bool $required = false,
        public readonly ?string $requiredIf = null,
        public readonly mixed $default = null,
        public readonly array $constraints = [],
    ) {
    }

    /**
     * Get a specific constraint value
     */
    public function getConstraint(string $name): mixed
    {
        return $this->constraints[$name] ?? null;
    }

    /**
     * Check if field has a specific constraint
     */
    public function hasConstraint(string $name): bool
    {
        return isset($this->constraints[$name]);
    }

    /**
     * Get enum constraint if present
     * @return array<mixed>|null
     */
    public function getEnum(): ?array
    {
        $enum = $this->getConstraint('enum');
        return is_array($enum) ? $enum : null;
    }

    /**
     * Get min constraint if present
     */
    public function getMin(): int|float|null
    {
        $min = $this->getConstraint('min');
        if ($min === null) {
            return null;
        }
        return is_int($min) || is_float($min) ? $min : null;
    }

    /**
     * Get max constraint if present
     */
    public function getMax(): int|float|null
    {
        $max = $this->getConstraint('max');
        if ($max === null) {
            return null;
        }
        return is_int($max) || is_float($max) ? $max : null;
    }

    /**
     * Get min_items constraint if present
     */
    public function getMinItems(): ?int
    {
        $min = $this->getConstraint('min_items');
        return is_int($min) ? $min : null;
    }

    /**
     * Get max_items constraint if present
     */
    public function getMaxItems(): ?int
    {
        $max = $this->getConstraint('max_items');
        return is_int($max) ? $max : null;
    }

    /**
     * Get pattern constraint if present
     */
    public function getPattern(): ?string
    {
        $pattern = $this->getConstraint('pattern');
        return is_string($pattern) ? $pattern : null;
    }

    /**
     * Get array item type if this is an array type
     */
    public function getArrayItemType(): ?string
    {
        return $this->type === 'array' ? $this->getConstraint('array_item_type') : null;
    }
}
