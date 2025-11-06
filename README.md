# Harmony TOML Validator

PHP library for validating TOML configuration files against Harmony DSL schemas. This package provides a cross-language compatible validator that matches the schema validation implemented in harmony-proxy (Rust).

## Installation

```bash
composer require aurabx/harmony-toml-validator
```

## Requirements

- PHP ^8.3 - Uses typed properties, named parameters, and modern type system
- yosymfony/toml ^1.0 - TOML parsing library

## Quick Start

### Validate a Configuration File

```php
use Runbeam\HarmonyTomlValidator\TomlValidator;
use Runbeam\HarmonyTomlValidator\Exceptions\ValidationException;

$validator = new TomlValidator();

try {
    $validator->validateFile(
        '/path/to/config.toml',
        '/path/to/harmony-config-schema.toml'
    );
    echo "✓ Configuration is valid\n";
} catch (ValidationException $e) {
    echo "✗ Validation failed\n";
    echo "Field: " . $e->getFieldPath() . "\n";
    echo "Error: " . $e->getFormattedError() . "\n";
}
```

### Validate TOML Content

```php
$content = <<<'TOML'
[proxy]
id = "gateway-01"
log_level = "info"
TOML;

try {
    $validator->validateContent($content, '/path/to/schema.toml');
    echo "✓ Configuration is valid\n";
} catch (ValidationException $e) {
    echo "✗ Validation failed: " . $e->getMessage() . "\n";
}
```

## Schema Format

This validator uses the Harmony DSL schema format (TOML-based) that defines:

- **Tables** - Configuration sections (e.g., `[proxy]`, `[network.default]`)
- **Fields** - Table properties with type and constraint information
- **Types** - string, integer, boolean, float, array, table
- **Constraints** - enum, min/max, array bounds, regex patterns, required fields

### Example Schema

```toml
[[table]]
name = "proxy"
required = true
description = "Core proxy configuration"

[[table.field]]
name = "id"
type = "string"
required = true
description = "Unique proxy identifier"

[[table.field]]
name = "log_level"
type = "string"
required = false
default = "error"
enum = ["trace", "debug", "info", "warn", "error"]
description = "Logging verbosity level"
```

## Validation Coverage

### Type Checking
- All TOML types: string, integer, boolean, float, array, table
- Type mismatches throw `TypeValidationException`

### Required Fields
- `required = true` - Field must be present
- `required_if = "condition"` - Conditional requirements (e.g., `backend == 's3'`)
- Missing required fields throw `RequiredFieldException`

### Constraints
- `enum = [...]` - Value must be in allowed list
- `min` / `max` - Numeric bounds checking
- `min_items` / `max_items` - Array length validation
- `pattern` - Regex pattern matching
- Constraint violations throw `ConstraintViolationException`

### Pattern-Based Tables
- Tables with `pattern = true` match multiple instances (e.g., `network.*` matches `network.default`, `network.management`)
- Supports `pattern_constraint` for regex validation of table names

## Exception Hierarchy

All validation errors are exceptions extending `ValidationException` with detailed context:

```php
try {
    $validator->validateFile($config, $schema);
} catch (ValidationException $e) {
    $e->getFieldPath();           // Dotted path: "network.default.bind_port"
    $e->getRuleName();            // Rule that failed: "min", "enum", "required"
    $e->getActualValue();         // The value that failed validation
    $e->getExpectedConstraint();  // What was expected
    $e->getMessage();             // Short error message
    $e->getFormattedError();      // Formatted error with full context
}
```

### Specialized Exceptions

- **`TypeValidationException`** - Type mismatches
- **`RequiredFieldException`** - Missing required fields
- **`ConstraintViolationException`** - Enum, min/max, pattern violations
- **`SchemaLoadException`** - Schema file errors

## Development

### Running Tests

```bash
# Run all tests (unit + integration)
composer test

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration
```

### Static Analysis

```bash
# Run PHPStan at level 8
composer analyse

# Run both analysis and tests
composer check
```

### Code Coverage

```bash
# Generate HTML and text coverage reports
composer test:coverage
# HTML report available in coverage/ directory
```

## Architecture

### Core Components

1. **SchemaLoader** - Parses schema TOML files into internal representation
2. **SchemaDefinition/SchemaTable/SchemaField** - Internal schema data structures
3. **ValidationContext** - Tracks validation state and field paths
4. **Validator Rules** - Individual validation rule implementations
5. **TomlValidator** - Main orchestrator that coordinates validation

### Validation Flow

1. Load schema file using `SchemaLoader`
2. Parse TOML configuration using yosymfony/toml
3. Iterate through schema tables and fields
4. Apply validation rules to configuration values
5. Collect errors and report first validation exception

## Limitations

- Nested field names (e.g., `options.path`) require special handling not yet implemented
- Currently validates table-level and field-level constraints
- For advanced conditional logic, consider extending `RequiredValidator`

## Related Projects

- **harmony-dsl** - Schema definitions and DSL specification
- **harmony-proxy** - Rust-based gateway that consumes validated configurations
- **Runbeam Cloud API** - Uses this library for configuration validation

## Testing

```bash
composer test
```

## License

MIT
