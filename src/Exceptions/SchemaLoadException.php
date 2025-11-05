<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Exceptions;

use Exception;

class SchemaLoadException extends Exception
{
    public function __construct(string $message, protected readonly string $schemaPath = '')
    {
        parent::__construct($message);
    }

    public function getSchemaPath(): string
    {
        return $this->schemaPath;
    }
}
