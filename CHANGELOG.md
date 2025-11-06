# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-11-05

### Added

- Initial release of Harmony TOML Validator
- Schema loading and parsing from TOML DSL files
- Complete validation rule system:
  - Type validation for all TOML types (string, integer, boolean, float, array, table)
  - Required field enforcement with conditional requirements (required_if)
  - Enum constraint validation
  - Numeric min/max bounds checking
  - Array length validation with item type checking
  - Regex pattern validation
- Pattern-based table matching (e.g., `network.*`)
- Comprehensive exception hierarchy with detailed error context
- Full field path reporting in dot notation (e.g., `network.default.bind_port`)
- TomlValidator public API:
  - `validateFile(configPath, schemaPath)` - Validate file against schema
  - `validateContent(content, schemaPath)` - Validate TOML string against schema
  - `validateArray(array, schema)` - Validate parsed array against schema
- Complete test suite with 23 tests (unit and integration)
- Test fixtures for valid and invalid configurations
- Comprehensive documentation and README with examples
- PHPStan level 8 static analysis compliance

### Limitations

- Nested field names (e.g., `options.path`) require special handling not yet implemented
- Schema versioning and migration support planned for future releases
- Advanced conditional logic beyond simple `==` comparisons

[0.1.0]: https://github.com/runbeam/harmony-toml-validator/releases/tag/0.1.0
