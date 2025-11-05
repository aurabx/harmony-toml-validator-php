# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Development Commands

### Testing
- `composer test` - Run full PHPUnit test suite (both unit and integration tests)
- `composer test:coverage` - Generate HTML coverage report in `coverage/` and display text summary
- `vendor/bin/phpunit --testsuite=Unit` - Run unit tests only
- `vendor/bin/phpunit --testsuite=Integration` - Run integration tests only

### Static Analysis
- `composer analyse` - Run PHPStan static analysis at level 8 with 512M memory limit
- `composer check` - Run both static analysis and tests

### Installing Dependencies
- `composer install` - Install all dependencies (required before running any other commands)

## Project Architecture

### Purpose and Ecosystem Context

This is a PHP library for validating TOML configuration files against Harmony DSL schemas. It's part of the broader Harmony ecosystem:

- **harmony-proxy** (Rust) - Gateway that consumes validated configurations
- **harmony-dsl** (Schemas) - TOML-based schema definitions that are language-agnostic
- **Runbeam Cloud API** (PHP) - Uses this library to validate configurations before deployment
- **This Library** - Provides PHP-based validation matching the Rust implementation

The validator prevents invalid configurations from being deployed by catching errors before they reach the gateway.

### Schema Types

The library validates two types of Harmony configuration files:

- **harmony-config-schema.toml** - Main gateway configuration (network settings, logging, storage, services)
- **harmony-pipeline-schema.toml** - Pipeline routing configuration (endpoints, middleware, backends)

### DSL Schema Format

Schemas are written in a TOML-based DSL that is:
- **Language-agnostic** - Same schema used by Rust and PHP implementations
- **Self-documenting** - Every table and field includes descriptions
- **Pattern-based** - Supports wildcard table names (e.g., `network.*` matches `network.default`, `network.management`)
- **Rich constraints** - Enums, min/max values, array lengths, regex patterns, conditional requirements

Example schema snippet:
```toml
[[table]]
name = "proxy"
required = true

[[table.field]]
name = "id"
type = "string"
required = true

[[table.field]]
name = "log_level"
type = "string"
required = false
default = "error"
enum = ["trace", "debug", "info", "warn", "error"]
```

## Validation Flow

### Schema Processing
1. Load schema files (TOML DSL format) that define structure and rules
2. `TomlValidator` class (to be implemented) parses schemas
3. Validator applies rules to configuration TOML files
4. Returns detailed validation errors with field paths and constraint violations

### Validation Coverage

**Type System:**
- `string`, `integer`, `boolean`, `float`, `array`, `table`

**Constraint Rules:**
- `enum` - Value must be in allowed list
- `min`/`max` - Numeric bounds checking
- `min_items`/`max_items` - Array length validation
- `pattern` - Regex pattern matching for field names and values
- `required` - Field must exist
- `required_if` - Conditional requirements (e.g., "port required if enable_wireguard == true")

**Pattern Matching:**
- Table names with wildcards (`network.*`) match multiple instances
- Validates `network.default`, `network.management` against single `network.*` schema definition

### Error Reporting

All validation exceptions include:
- **Field path** - Exact location (e.g., `network.default.tcp_config.bind_port`)
- **Rule name** - Which constraint failed (e.g., `min`, `enum`, `required`)
- **Actual value** - The value that failed validation
- **Expected constraint** - What was expected (e.g., minimum value, allowed enum values)
- **Formatted error** - Human-readable error message via `getFormattedError()`

## Exception Hierarchy

### Base Exception

`ValidationException` - Base class for all validation errors. Captures:
- Field path showing exact location of error
- Rule name that was violated
- Actual value that failed
- Expected constraint
- `getFormattedError()` method for detailed output

### Specialized Exceptions

**`TypeValidationException`**
- For type mismatches (e.g., expected string, got integer)
- Static factory: `TypeValidationException::create($fieldPath, $expectedType, $actualValue)`

**`RequiredFieldException`**
- For missing required fields
- Supports conditional requirements
- Static factory: `RequiredFieldException::create($fieldPath, ?$condition)`

**`ConstraintViolationException`**
- For rule violations with static factory methods:
  - `ConstraintViolationException::enum($fieldPath, $actualValue, $allowedValues)`
  - `ConstraintViolationException::min($fieldPath, $actualValue, $min)`
  - `ConstraintViolationException::max($fieldPath, $actualValue, $max)`
  - `ConstraintViolationException::minItems($fieldPath, $actualCount, $minItems)`
  - `ConstraintViolationException::maxItems($fieldPath, $actualCount, $maxItems)`
  - `ConstraintViolationException::pattern($fieldPath, $actualValue, $pattern)`

**`SchemaLoadException`**
- For schema parsing and loading errors
- Captures schema file path for debugging

## Key Implementation Notes

### Requirements and Dependencies

- **PHP 8.3+** - Uses named parameters, readonly properties, modern type system
- **yosymfony/toml ^1.0** - TOML parsing library
- **PHPStan level 8** - Strictest static analysis (512M memory limit)
- **PHPUnit ^11.0** - Testing framework

### Project Structure

```
src/
├── Exceptions/          # Complete exception hierarchy (implemented)
│   ├── ValidationException.php
│   ├── TypeValidationException.php
│   ├── RequiredFieldException.php
│   ├── ConstraintViolationException.php
│   └── SchemaLoadException.php
└── Rules/              # Directory prepared for validation rule implementations

tests/
├── Unit/               # Unit tests
├── Integration/        # Integration tests
└── Fixtures/           # Example schemas and configs
    ├── schemas/
    │   ├── harmony-config-schema.toml    # Reference schema for main config
    │   └── harmony-pipeline-schema.toml  # Reference schema for pipelines
    ├── valid/          # Valid config examples (to be added)
    └── invalid/        # Invalid config examples (to be added)

tmp/                    # Temporary files (gitignored)
```

### Development Status

⚠️ **Early-stage project**: Core `TomlValidator` classes and rule implementations are still to be implemented. Exception hierarchy and project structure are complete.

### Schema Compatibility Notes

- Schemas support backward compatibility features (e.g., `network.*.http` aliased to `tcp_config`)
- Version field in schema header (`schema.version`) tracks DSL evolution
- Validators should handle schema version differences gracefully
- Field aliases allow old configuration files to work with new schemas

### Testing Strategy

- **Unit tests** (`tests/Unit/`) - Test individual validation rules and exception behavior
- **Integration tests** (`tests/Integration/`) - Test full validation flow with real schema and config files
- **Fixtures** (`tests/Fixtures/`) - Maintain example schemas from harmony-dsl project and test cases

When implementing new features, ensure both valid and invalid test cases are covered using fixtures.
