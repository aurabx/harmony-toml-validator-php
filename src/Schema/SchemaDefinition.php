<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Schema;

class SchemaDefinition
{
    /**
     * @param SchemaTable[] $tables
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public readonly array $tables = [],
        public readonly array $metadata = [],
    ) {
    }

    /**
     * Get schema version
     */
    public function getVersion(): ?string
    {
        return $this->metadata['version'] ?? null;
    }

    /**
     * Get schema description
     */
    public function getDescription(): ?string
    {
        return $this->metadata['description'] ?? null;
    }

    /**
     * Find a table by exact name (non-pattern match)
     */
    public function getTable(string $name): ?SchemaTable
    {
        foreach ($this->tables as $table) {
            if ($table->name === $name && !$table->isPattern()) {
                return $table;
            }
        }
        return null;
    }

    /**
     * Find tables that match a given instance name (handles patterns)
     * @return SchemaTable[]
     */
    public function getTablesMatching(string $instanceName): array
    {
        $matching = [];
        foreach ($this->tables as $table) {
            if ($table->matches($instanceName)) {
                $matching[] = $table;
            }
        }
        return $matching;
    }

    /**
     * Get all tables (pattern and non-pattern)
     * @return SchemaTable[]
     */
    public function getAllTables(): array
    {
        return $this->tables;
    }
}
