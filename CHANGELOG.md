# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-01-25

### Added
- Hierarchical configuration system with intelligent fallbacks
- Universal callback handler support for STK, C2B, B2C, and B2B
- STK Status Query endpoint support
- B2Pochi payment method support
- Interactive testing dashboard with real-time callback logging
- Command-aware field pruning for C2B simulations (Buy Goods support)
- Detailed error reporting in API responses

### Fixed
- Improved validation rule naming consistency (maxlength)
- Unified exception hierarchy (ConfigurationException now extends MpesaException)
- Autoloading support for B2Pochi class
- C2B simulation schema validation errors for Buy Goods commands
- Robust JSON parsing for callback logs in the example dashboard

### Changed
- Refactored all payment handlers to use smart defaults and optional callback URLs
- Updated internal configuration with standard Safaricom defaults

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
