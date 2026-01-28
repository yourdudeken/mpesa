# Contributing to M-PESA API Package

Thank you for considering contributing to the M-PESA API Package! This document provides guidelines for contributing to the project.

## Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/YOUR_USERNAME/mpesa.git`
3. Create a feature branch: `git checkout -b feature/your-feature-name`
4. Install dependencies: `composer install`
5. Make your changes
6. Run tests: `vendor/bin/phpunit`
7. Commit your changes (see commit message format below)
8. Push to your fork: `git push origin feature/your-feature-name`
9. Create a Pull Request

## Development Setup

### Requirements

- PHP 8.0 or higher
- Composer
- Git

### Installation

```bash
# Clone the repository
git clone https://github.com/yourdudeken/mpesa.git
cd mpesa

# Install dependencies
composer install

# Run tests
vendor/bin/phpunit
```

## Commit Message Format

We use [Conventional Commits](https://www.conventionalcommits.org/) for automated versioning and changelog generation.

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- **feat**: A new feature (triggers minor version bump)
- **fix**: A bug fix (triggers patch version bump)
- **docs**: Documentation only changes
- **style**: Changes that don't affect code meaning (formatting, etc.)
- **refactor**: Code change that neither fixes a bug nor adds a feature
- **perf**: Performance improvements
- **test**: Adding or updating tests
- **chore**: Maintenance tasks, dependency updates, etc.
- **ci**: Changes to CI/CD configuration

### Examples

```bash
# New feature
git commit -m "feat: add B2Pochi payment method support"

# Bug fix
git commit -m "fix: resolve STK push timeout issue"

# Documentation
git commit -m "docs: update installation instructions"

# Breaking change
git commit -m "feat!: redesign API interface

BREAKING CHANGE: Method signatures have changed. See migration guide."

# With scope
git commit -m "feat(stk): add retry mechanism for failed requests"
```

### Breaking Changes

For breaking changes, add `!` after the type or include `BREAKING CHANGE:` in the footer:

```bash
git commit -m "feat!: change authentication method

BREAKING CHANGE: OAuth2 is now required instead of basic auth"
```

## Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for all public methods
- Keep methods focused and concise

### Running Code Style Checks

```bash
# If PHP CS Fixer is installed
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Testing

All contributions must include tests.

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage
vendor/bin/phpunit --coverage-html coverage
```

### Writing Tests

- Place tests in the `tests/` directory
- Follow the existing test structure
- Test both success and failure scenarios
- Mock external API calls

Example:

```php
<?php

namespace Yourdudeken\Mpesa\Tests\Unit;

use Yourdudeken\Mpesa\Tests\TestCase;

class STKPushTest extends TestCase
{
    public function testSTKPushSuccess()
    {
        // Your test code
    }
    
    public function testSTKPushFailure()
    {
        // Your test code
    }
}
```

## Pull Request Process

1. **Update Documentation**: Update README.md and relevant docs if needed
2. **Add Tests**: Ensure your changes are covered by tests
3. **Run Tests**: All tests must pass
4. **Follow Commit Format**: Use conventional commits
5. **Update Changelog**: Not needed - automatically generated
6. **Describe Changes**: Provide a clear PR description

### PR Title Format

Use the same format as commit messages:

```
feat: add new payment method
fix: resolve callback handling issue
docs: improve installation guide
```

## Code Review

All submissions require review. We use GitHub pull requests for this purpose.

### Review Criteria

- Code quality and style
- Test coverage
- Documentation
- Backward compatibility
- Performance impact

## Reporting Bugs

### Before Submitting

1. Check existing issues
2. Verify it's not already fixed in the latest version
3. Collect relevant information

### Bug Report Template

```markdown
**Describe the bug**
A clear description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Initialize with '...'
2. Call method '...'
3. See error

**Expected behavior**
What you expected to happen.

**Actual behavior**
What actually happened.

**Environment:**
- PHP Version: [e.g., 8.1]
- Package Version: [e.g., 1.2.3]
- OS: [e.g., Ubuntu 22.04]

**Additional context**
Any other relevant information.
```

## Feature Requests

We welcome feature requests! Please:

1. Check if the feature already exists
2. Describe the use case clearly
3. Explain why it would be useful
4. Consider submitting a PR

## Questions?

- **Email**: kenmwendwamuthengi@gmail.com
- **Telegram**: [@yourdudeken](https://t.me/yourdudeken)
- **GitHub Issues**: For bug reports and feature requests

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Recognition

Contributors will be recognized in:
- GitHub contributors page
- Release notes (for significant contributions)
- README.md (for major contributions)

Thank you for contributing! 
