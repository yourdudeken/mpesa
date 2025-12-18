# 1.0.0 (2025-12-18)


### Bug Fixes

* remove `mixed` type hint for PHP 7.4 compatibility, configure GitHub Actions release permissions, and add fixes documentation. ([497d89e](https://github.com/yourdudeken/mpesa/commit/497d89e277060f1d9818a3f97d36dd3cd95d4b56))
* remove `object` type hints from `RuleCollection` methods and add PHPDoc for PHP 7.4 compatibility. ([37181c7](https://github.com/yourdudeken/mpesa/commit/37181c7e1e72186edcdb3991445d1f6bdd343643))


### Features

* Add API setup, PHP 8.3 compatibility, and quick reference documentation, alongside type hinting for `RuleCollection` and core configuration updates. ([a62992d](https://github.com/yourdudeken/mpesa/commit/a62992db280e4203dd7da5d0b5b26c3abe93d04b))
* Add automated release workflow with semantic-release configuration and update CI job name. ([9e7d166](https://github.com/yourdudeken/mpesa/commit/9e7d16673a01786b1ee10b7ac5b7336c7e95a1db))
* add comprehensive documentation for M-Pesa Reversal, AccountBalance, B2C, B2B, C2B, TransactionStatus, and B2Pochi ([4afa3ff](https://github.com/yourdudeken/mpesa/commit/4afa3fff4a4ffd011c07dbb5e042b2d57b3b9e29))
* Add scripts for managing dual production and sandbox API servers, including setup, start, stop, and documentation. ([a4d1c5c](https://github.com/yourdudeken/mpesa/commit/a4d1c5c2d4bc18bdcbd45ebe736cf8d0f2c46f26))
* centralize M-Pesa configuration parameters, enable dynamic callback URLs, and add cURL examples and a simplified config guide. ([80fd1b1](https://github.com/yourdudeken/mpesa/commit/80fd1b194f5636265070294ca19edcaf4cabe767))
* Implement a merchant portal with CRUD operations, authentication, and associated setup scripts and documentation. ([3ebeb71](https://github.com/yourdudeken/mpesa/commit/3ebeb718fc8bda80eb5ac69f9f95021c74b194fd))
* Implement a new M-Pesa API with routing, authentication, CORS, rate limiting middleware, and M-Pesa service controllers. ([364ade9](https://github.com/yourdudeken/mpesa/commit/364ade93bb61bc0a456161abe7dfac9fcc4f968c))
* Implement B2Pochi M-Pesa payment, add a comprehensive M-Pesa Postman collection, and update API controllers and documentation. ([cc1b6fc](https://github.com/yourdudeken/mpesa/commit/cc1b6fca5b91dcd41221ebff87d5eadeb97da603))
* Implement comprehensive authentication with login/signup, API key auth, and login throttling, add merchant editing, and remove obsolete documentation and setup scripts. ([a334aac](https://github.com/yourdudeken/mpesa/commit/a334aac68c956fa1cd1ef2e883d11de0f0b0f7ff))
* implement GitHub Actions CI with composer validation and PSR-4 autoload checks, and add a changelog. ([cbd898a](https://github.com/yourdudeken/mpesa/commit/cbd898a0ac21e8d51b4435be80900f1ffa751689))
* Implement Mpesa API integration with new controller and service classes, adjusting composer namespaces and service provider setup. ([5225cc7](https://github.com/yourdudeken/mpesa/commit/5225cc788aadb37adeb7f7f5f7a653c7143c72a5))
* Introduce a new validation system, update core components, and add CI/CD workflows and documentation. ([4b5c279](https://github.com/yourdudeken/mpesa/commit/4b5c279657e49c3f13f2c8470543ff33ae1354ec))
* Restructure project to `src/Mpesa` namespace, refactor validation, and add composer, tests, and documentation. ([aa94a3d](https://github.com/yourdudeken/mpesa/commit/aa94a3d6b9087d5a930f037fcee7b50ac955c960))
* Reworked project into an M-Pesa Merchant Portal, removing API key middleware, updating M-Pesa routes, and adding a changelog. ([c8ba478](https://github.com/yourdudeken/mpesa/commit/c8ba478d89eba8b1ca34365699cbc810a8197c2e))
* Update C2B register endpoint to v2 and standardize response type configuration from on_timeout. ([2e1e679](https://github.com/yourdudeken/mpesa/commit/2e1e67905143ca2495d3e52d77ba49cafd3454b6))
* Update PHP and dev dependency versions, add .gitignore, and generate composer.lock. ([ac175f2](https://github.com/yourdudeken/mpesa/commit/ac175f2e386fb21b69e9327abd2b8123ef9e1da0))

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-12-18

### Fixed
- PSR-4 autoload compliance for Installer class
- CI enforcement for composer validation

## [1.0.0] - 2025-12-17

### Added
- Initial stable release
- Complete M-Pesa API integration
- STK Push (Lipa na M-Pesa Online)
- B2C (Business to Customer)
- B2B (Business to Business)
- B2Pochi (Business to Pochi)
- C2B (Customer to Business)
- Transaction Status
- Account Balance
- Reversal
- Comprehensive documentation
