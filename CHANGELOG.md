# [1.4.0](https://github.com/yourdudeken/mpesa/compare/v1.3.0...v1.4.0) (2026-01-25)


### Bug Fixes

* standardize Mpesa API parameter casing and enhance callback URL fallback logic. ([2261fcf](https://github.com/yourdudeken/mpesa/commit/2261fcf30a17c7d00ff450316af06483cc0720ef))


### Features

* Add a complete full-stack M-Pesa payment system example application with frontend, backend, and comprehensive documentation. ([43084d4](https://github.com/yourdudeken/mpesa/commit/43084d4e8c249ddfbab5d97d543087894b39a37e))
* Add B2Pochi Pay class to autoloader and remove redundant validation rule. ([062740c](https://github.com/yourdudeken/mpesa/commit/062740c9b09dae4983c29416fc525473cd4a5bf6))
* Add M-Pesa test configuration files, including `.env.example` and `mpesa.php`. ([6bad41d](https://github.com/yourdudeken/mpesa/commit/6bad41d88f78e3a05bd0b1b3d47946fbdf155cc7))
* enhance configuration flexibility with global callback fallbacks and clearer parameter defaults across M-Pesa API documentation. ([e93cfe4](https://github.com/yourdudeken/mpesa/commit/e93cfe4d40bc1c45522f285eb18c16cead6fce91))
* Generalize frontend form handling to support all M-Pesa transaction types and add complete implementation documentation. ([9f05aaf](https://github.com/yourdudeken/mpesa/commit/9f05aafb3077fa83eaddf7b589cc91da96140bf1))
* gracefully handle database unavailability in payment API, update M-Pesa configurations, and add testing utilities with documentation. ([b345b04](https://github.com/yourdudeken/mpesa/commit/b345b0457f9c875f7b87918cb533c6e9c0682896))
* Implement comprehensive M-Pesa callback handling for various transaction types and add STK status functionality. ([c4ca43c](https://github.com/yourdudeken/mpesa/commit/c4ca43ce73c7fa60dc7f6a63a0f5bbce18ec2617))
* refactor configuration loading to support environment variables, .env files, and a layered merging strategy. ([d461774](https://github.com/yourdudeken/mpesa/commit/d461774cca9d8b3d2ef0cb47342954871896d987))
* Standardize B2C/B2B/B2Pochi API parameters to PascalCase and enhance C2B simulate by making BillRefNumber optional and allowing CommandID selection. ([4ebf73c](https://github.com/yourdudeken/mpesa/commit/4ebf73c7e2f14e2054b0006fa00101a52a883b45))
* Standardize C2B API parameter casing and enable configuration of CommandID and ResponseType. ([c709239](https://github.com/yourdudeken/mpesa/commit/c70923945f9432d1390e5b6e1086b340ec376b6f))

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
