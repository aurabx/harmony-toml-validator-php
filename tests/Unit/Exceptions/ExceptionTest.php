<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use Runbeam\HarmonyTomlValidator\Exceptions\ValidationException;
use Runbeam\HarmonyTomlValidator\Exceptions\TypeValidationException;
use Runbeam\HarmonyTomlValidator\Exceptions\RequiredFieldException;
use Runbeam\HarmonyTomlValidator\Exceptions\ConstraintViolationException;
use Runbeam\HarmonyTomlValidator\Exceptions\SchemaLoadException;

final class ExceptionTest extends TestCase
{
    public function testValidationExceptionContainsAllContext(): void
    {
        $exception = new ValidationException(
            'Test error',
            'proxy.id',
            'type',
            12345,
            'string'
        );

        $this->assertSame('Test error', $exception->getMessage());
        $this->assertSame('proxy.id', $exception->getFieldPath());
        $this->assertSame('type', $exception->getRuleName());
        $this->assertSame(12345, $exception->getActualValue());
        $this->assertSame('string', $exception->getExpectedConstraint());
    }

    public function testValidationExceptionFormattedError(): void
    {
        $exception = new ValidationException(
            'Test error',
            'proxy.id',
            'type',
            12345,
            'string'
        );

        $formatted = $exception->getFormattedError();
        $this->assertStringContainsString('Test error', $formatted);
        $this->assertStringContainsString('proxy.id', $formatted);
        $this->assertStringContainsString('type', $formatted);
        $this->assertStringContainsString('12345', $formatted);
        $this->assertStringContainsString('string', $formatted);
    }

    public function testTypeValidationExceptionFactory(): void
    {
        $exception = TypeValidationException::create('proxy.id', 'string', 12345);

        $this->assertSame('proxy.id', $exception->getFieldPath());
        $this->assertSame('type', $exception->getRuleName());
        $this->assertStringContainsString('string', $exception->getMessage());
    }

    public function testRequiredFieldExceptionWithoutCondition(): void
    {
        $exception = RequiredFieldException::create('proxy.id');

        $this->assertSame('proxy.id', $exception->getFieldPath());
        $this->assertSame('required', $exception->getRuleName());
        $this->assertStringContainsString('missing', $exception->getMessage());
    }

    public function testRequiredFieldExceptionWithCondition(): void
    {
        $exception = RequiredFieldException::create('network.default.interface', 'enable_wireguard == true');

        $this->assertSame('network.default.interface', $exception->getFieldPath());
        $this->assertSame('required', $exception->getRuleName());
        $this->assertStringContainsString('enable_wireguard == true', $exception->getMessage());
    }

    public function testConstraintViolationEnumException(): void
    {
        $exception = ConstraintViolationException::enum(
            'logging.log_level',
            'invalid',
            ['trace', 'debug', 'info', 'warn', 'error']
        );

        $this->assertSame('logging.log_level', $exception->getFieldPath());
        $this->assertSame('enum', $exception->getRuleName());
        $this->assertSame('invalid', $exception->getActualValue());
        $this->assertStringContainsString('trace', $exception->getMessage());
    }

    public function testConstraintViolationMinException(): void
    {
        $exception = ConstraintViolationException::min('proxy.jwks_cache_duration_hours', 0, 1);

        $this->assertSame('proxy.jwks_cache_duration_hours', $exception->getFieldPath());
        $this->assertSame('min', $exception->getRuleName());
        $this->assertSame(0, $exception->getActualValue());
        $this->assertSame(1, $exception->getExpectedConstraint());
    }

    public function testConstraintViolationMaxException(): void
    {
        $exception = ConstraintViolationException::max('proxy.jwks_cache_duration_hours', 200, 168);

        $this->assertSame('proxy.jwks_cache_duration_hours', $exception->getFieldPath());
        $this->assertSame('max', $exception->getRuleName());
        $this->assertSame(200, $exception->getActualValue());
        $this->assertSame(168, $exception->getExpectedConstraint());
    }

    public function testConstraintViolationMinItemsException(): void
    {
        $exception = ConstraintViolationException::minItems('pipelines.default.endpoints', 0, 1);

        $this->assertSame('pipelines.default.endpoints', $exception->getFieldPath());
        $this->assertSame('min_items', $exception->getRuleName());
    }

    public function testConstraintViolationMaxItemsException(): void
    {
        $exception = ConstraintViolationException::maxItems('pipelines.default.backends', 10, 5);

        $this->assertSame('pipelines.default.backends', $exception->getFieldPath());
        $this->assertSame('max_items', $exception->getRuleName());
    }

    public function testConstraintViolationPatternException(): void
    {
        $exception = ConstraintViolationException::pattern('network.my-network', 'my-network', '^[a-z0-9_-]+$');

        $this->assertSame('network.my-network', $exception->getFieldPath());
        $this->assertSame('pattern', $exception->getRuleName());
        $this->assertSame('my-network', $exception->getActualValue());
    }

    public function testSchemaLoadExceptionWithPath(): void
    {
        $exception = new SchemaLoadException('Invalid schema', '/path/to/schema.toml');

        $this->assertSame('Invalid schema', $exception->getMessage());
        $this->assertSame('/path/to/schema.toml', $exception->getSchemaPath());
    }
}
