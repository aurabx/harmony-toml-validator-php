<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Schema;

class SchemaTable
{
    /**
     * @param SchemaField[] $fields
     */
    public function __construct(
        public readonly string $name,
        public readonly array $fields = [],
        public readonly bool $required = false,
        public readonly bool $pattern = false,
        public readonly ?string $patternConstraint = null,
        public readonly ?string $description = null,
    ) {
    }

    /**
     * Get a field by name
     */
    public function getField(string $name): ?SchemaField
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }
        return null;
    }

    /**
     * Check if table is pattern-based (e.g., network.*)
     */
    public function isPattern(): bool
    {
        return $this->pattern;
    }

    /**
     * Get the base table name without pattern (e.g., "network" from "network.*")
     */
    public function getBaseName(): string
    {
        if ($this->isPattern()) {
            return rtrim($this->name, '.*');
        }
        return $this->name;
    }

    /**
     * Check if a table instance name matches this table definition (for patterns)
     * e.g., "network.default" matches "network.*"
     */
    public function matches(string $instanceName): bool
    {
        if (!$this->isPattern()) {
            return $this->name === $instanceName;
        }

        $baseName = $this->getBaseName();
        if (!str_starts_with($instanceName, $baseName . '.')) {
            return false;
        }

        if ($this->patternConstraint === null) {
            return true;
        }

        // Extract the variable part after the base name
        $suffix = substr($instanceName, strlen($baseName) + 1);
        return (bool) preg_match('/' . $this->patternConstraint . '/', $suffix);
    }
}
