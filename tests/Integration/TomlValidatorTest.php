<?php

declare(strict_types=1);

namespace Runbeam\HarmonyTomlValidator\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Runbeam\HarmonyTomlValidator\TomlValidator;
use Runbeam\HarmonyTomlValidator\Exceptions\ValidationException;

final class TomlValidatorTest extends TestCase
{
    private TomlValidator $validator;
    private string $schemaPath;
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->validator = new TomlValidator();
        $this->fixturesPath = __DIR__ . '/../Fixtures';
        $this->schemaPath = $this->fixturesPath . '/schemas/harmony-config-schema.toml';
    }

    public function testValidateMinimalValidConfig(): void
    {
        $configPath = $this->fixturesPath . '/valid/harmony-config-minimal.toml';
        
        // Should not throw
        $this->validator->validateFile($configPath, $this->schemaPath);
        $this->assertTrue(true);
    }

    public function testValidateMissingRequiredTable(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/proxy.*missing/i');
        
        $configPath = $this->fixturesPath . '/invalid/harmony-config-missing-proxy.toml';
        $this->validator->validateFile($configPath, $this->schemaPath);
    }

    public function testValidateInvalidType(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/type/i');
        
        $configPath = $this->fixturesPath . '/invalid/harmony-config-invalid-type.toml';
        $this->validator->validateFile($configPath, $this->schemaPath);
    }

    public function testValidateInvalidEnumValue(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/enum|allowed/i');
        
        $configPath = $this->fixturesPath . '/invalid/harmony-config-invalid-enum.toml';
        $this->validator->validateFile($configPath, $this->schemaPath);
    }

    public function testValidateValueBelowMinimum(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/minimum|below/i');
        
        $configPath = $this->fixturesPath . '/invalid/harmony-config-invalid-min.toml';
        $this->validator->validateFile($configPath, $this->schemaPath);
    }

    public function testValidateValueAboveMaximum(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/maximum|exceeds/i');
        
        $configPath = $this->fixturesPath . '/invalid/harmony-config-invalid-max.toml';
        $this->validator->validateFile($configPath, $this->schemaPath);
    }

    public function testValidateNonexistentConfigFile(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/not found/i');
        
        $this->validator->validateFile('/nonexistent/config.toml', $this->schemaPath);
    }

    public function testValidatePipelineMinimalConfig(): void
    {
        $validator = new TomlValidator();
        $configPath = $this->fixturesPath . '/valid/harmony-pipeline-minimal.toml';
        $schemaPath = $this->fixturesPath . '/schemas/harmony-pipeline-schema.toml';
        
        // Should not throw
        $validator->validateFile($configPath, $schemaPath);
        $this->assertTrue(true);
    }

    public function testValidateWithStringContent(): void
    {
        $content = <<<'TOML'
[proxy]
id = "gateway-01"
TOML;
        
        // Should not throw
        $this->validator->validateContent($content, $this->schemaPath);
        $this->assertTrue(true);
    }

    public function testValidateInvalidTomlSyntax(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageMatches('/TOML/i');
        
        $content = <<<'TOML'
[proxy
id = "gateway-01"
TOML;
        
        $this->validator->validateContent($content, $this->schemaPath);
    }

    public function testExceptionContainsFieldPath(): void
    {
        try {
            $configPath = $this->fixturesPath . '/invalid/harmony-config-invalid-enum.toml';
            $this->validator->validateFile($configPath, $this->schemaPath);
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->getFieldPath());
            $this->assertStringContainsString('.', $e->getFieldPath());
            return;
        }
        $this->fail('Expected ValidationException to be thrown');
    }
}
