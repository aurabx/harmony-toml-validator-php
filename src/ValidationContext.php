<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator;

class ValidationContext
{
    /** @var string[] */
    private array $pathStack = [];

    public function pushPath(string $name): void
    {
        $this->pathStack[] = $name;
    }

    public function popPath(): void
    {
        array_pop($this->pathStack);
    }

    public function getCurrentPath(): string
    {
        return implode('.', $this->pathStack);
    }

    /**
     * Get the current field path for error reporting
     */
    public function getFieldPath(): string
    {
        $path = $this->getCurrentPath();
        return $path ?: '';
    }

    /**
     * Execute a closure with a temporary path segment
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    public function withPath(string $name, callable $callback): mixed
    {
        $this->pushPath($name);
        try {
            return $callback();
        } finally {
            $this->popPath();
        }
    }
}
