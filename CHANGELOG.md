# [1.3.0](https://github.com/yourdudeken/mpesa/compare/v1.2.0...v1.3.0) (2026-01-18)


### Features

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

# [1.2.0](https://github.com/yourdudeken/mpesa/compare/v1.1.0...v1.2.0) (2025-12-20)


### Features

* Add bulk consumer credentials and rename security_credential to initiator_password. ([d019f2d](https://github.com/yourdudeken/mpesa/commit/d019f2d816df633f50cc6d9a32e0eab104d1d1e8))

# [1.1.0](https://github.com/yourdudeken/mpesa/compare/v1.0.0...v1.1.0) (2025-12-18)


### Features

* Implement comprehensive CI/CD pipeline with GitHub Actions, semantic-release, and development guidelines. ([2f087b8](https://github.com/yourdudeken/mpesa/commit/2f087b8dc74eb347ce1566b9d3e1d5c6cce4aeb2))

# [1.0.0](https://github.com/yourdudeken/mpesa/compare/v1.0.0) (2025-12-17)

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
