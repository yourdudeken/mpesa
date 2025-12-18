# CI/CD Documentation

## Overview

This repository uses GitHub Actions for continuous integration, automated releases, and package publishing to Packagist.

## Workflows

### CI Workflow (ci.yml)
- **Trigger**: Push/PR to `main` or `develop` branches
- **Purpose**: Run tests on PHP 7.4, 8.0, 8.1, 8.2, 8.3
- **Features**: Code quality checks, security audits, code coverage

### Release Workflow (release.yml)
- **Trigger**: Push to `main` branch
- **Purpose**: Automated semantic versioning and releases
- **Features**: 
  - Determines version from commit messages
  - Generates CHANGELOG.md
  - Creates GitHub releases with tags
  - Creates version branches (v1.0.0, v1.0.1, etc.)
  - Notifies Packagist

### Tag Release Workflow (tag-release.yml)
- **Trigger**: Push of version tags (v*.*.*)
- **Purpose**: Build release archives
- **Features**: Creates .tar.gz and .zip archives, attaches to GitHub releases

## Setup

### Required Secrets

Configure these secrets in GitHub repository settings (Settings > Secrets and variables > Actions):

- `PACKAGIST_USERNAME` - Your Packagist username
- `PACKAGIST_TOKEN` - API token from https://packagist.org/profile/

### Using the Setup Script

```bash
./.github/setup-cicd.sh
```

## Semantic Versioning

This project uses Conventional Commits for automated versioning.

### Commit Message Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Version Bumps

| Commit Type | Version Change | Example |
|-------------|----------------|---------|
| `fix:` | Patch (0.0.X) | `fix: resolve authentication timeout` |
| `feat:` | Minor (0.X.0) | `feat: add B2Pochi support` |
| `BREAKING CHANGE:` | Major (X.0.0) | `feat!: redesign API` |
| `chore:`, `docs:`, `style:`, `refactor:`, `test:` | No change | `chore: update dependencies` |

### Examples

```bash
# Patch release (1.0.0 -> 1.0.1)
git commit -m "fix: resolve authentication issue"

# Minor release (1.0.0 -> 1.1.0)
git commit -m "feat: add B2Pochi transaction support"

# Major release (1.0.0 -> 2.0.0)
git commit -m "feat!: redesign API interface

BREAKING CHANGE: Constructor signature changed"
```

## Workflow Execution

1. Push to `main` with conventional commit
2. CI workflow runs tests on all PHP versions
3. Release workflow analyzes commits and determines version
4. Creates version branch (e.g., `v1.0.2`)
5. Updates CHANGELOG.md
6. Creates GitHub release with tag
7. Notifies Packagist

## Testing Locally

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Validate composer.json
composer validate --strict
```

## Packagist Setup

1. Create account at https://packagist.org
2. Submit package: https://github.com/yourdudeken/mpesa
3. Generate API token at https://packagist.org/profile/
4. Add secrets to GitHub repository

## Monitoring

- **GitHub Actions**: https://github.com/yourdudeken/mpesa/actions
- **Packagist**: https://packagist.org/packages/yourdudeken/mpesa
- **Releases**: https://github.com/yourdudeken/mpesa/releases

## Troubleshooting

### Release Not Created
- Check commit messages follow conventional commits format
- Verify workflow logs in Actions tab

### Packagist Not Updating
- Verify secrets are configured correctly
- Check package is registered on Packagist
- Manual trigger:
```bash
curl -XPOST -H'content-type:application/json' \
  'https://packagist.org/api/update-package?username=USERNAME&apiToken=TOKEN' \
  -d'{"repository":{"url":"https://packagist.org/packages/yourdudeken/mpesa"}}'
```

### Tests Failing
- Check PHP version compatibility
- Review test logs in Actions tab
- Run tests locally: `composer test`

## Additional Resources

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Release](https://semantic-release.gitbook.io/)
- [GitHub Actions](https://docs.github.com/en/actions)
- [Packagist](https://packagist.org/about)
