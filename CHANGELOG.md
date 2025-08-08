# Changelog

All notable changes to the Statamic S3 Filesystem Driver will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of Statamic S3 Filesystem Driver
- Support for storing all Statamic content types in AWS S3
- Custom filesystem driver using Laravel's filesystem abstraction
- Complete store implementations for:
  - Collections
  - Entries
  - Globals
  - Taxonomies
  - Terms
  - Assets
  - Asset Containers
  - Navigations
  - Navigation Trees
- Built-in caching layer for performance optimization
- Configurable content type drivers
- Laravel Vapor compatibility
- Comprehensive documentation and examples
- Test suite with PHPUnit
- Environment configuration examples
- Vapor deployment configuration examples

### Features
- Maintains Statamic's native flat-file structure
- No database migrations required
- Drop-in replacement for Statamic's file-based stores
- Flexible configuration per content type
- Performance caching with configurable TTL
- S3 path prefixing support
- Proper change detection using S3 object metadata
- Full integration with Statamic's Stache system

### Documentation
- Complete installation and configuration guide
- Laravel Vapor deployment instructions
- Troubleshooting section
- Performance optimization recommendations
- Migration guide from local file storage

## [1.0.0] - TBD

Initial release.