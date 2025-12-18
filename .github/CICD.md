# CI/CD Pipeline Documentation

This repository uses GitHub Actions for continuous integration and automated releases.

## Workflows

### 1. CI Workflow (`.github/workflows/ci.yml`)

**Triggers:**
- Push to `main`, `dev`, or `develop` branches
- Pull requests to `main`, `dev`, or `develop` branches

**What it does:**
- Tests the package across multiple PHP versions (7.0 - 8.3)
- Validates `composer.json` and `composer.lock`
- Runs PHPUnit tests with code coverage
- Uploads coverage reports to Codecov
- Performs code quality checks with PHP CS Fixer

**Status Badge:**
```markdown
[![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml)
```

### 2. Release Workflow (`.github/workflows/release.yml`)

**Triggers:**
- Push to `main` or `master` branch (automatic semantic versioning)

**What it does:**
- Analyzes commit messages to determine version bump (major, minor, patch)
- Generates changelog automatically
- Creates a GitHub release with proper tags
- Updates `CHANGELOG.md` and `composer.json`
- Optionally triggers Packagist update

**Commit Message Format:**
Use [Conventional Commits](https://www.conventionalcommits.org/) format:

- `feat: add new feature` → Minor version bump (1.0.0 → 1.1.0)
- `fix: bug fix` → Patch version bump (1.0.0 → 1.0.1)
- `BREAKING CHANGE: description` → Major version bump (1.0.0 → 2.0.0)
- `chore: maintenance` → No version bump
- `docs: documentation` → No version bump

**Examples:**
```bash
git commit -m "feat: add B2Pochi payment method"
git commit -m "fix: resolve STK push timeout issue"
git commit -m "feat!: redesign API interface

BREAKING CHANGE: API method signatures have changed"
```

**Status Badge:**
```markdown
[![Release](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml)
```

### 3. Tag Release Workflow (`.github/workflows/tag-release.yml`)

**Triggers:**
- Manual tag creation (e.g., `v1.2.3`)

**What it does:**
- Builds the package
- Runs tests
- Creates a release archive (ZIP file)
- Creates a GitHub release with the archive
- Optionally triggers Packagist update

**Usage:**
```bash
# Create and push a tag
git tag v1.2.3
git push origin v1.2.3
```

## Setup Instructions

### 1. Enable GitHub Actions

GitHub Actions is enabled by default. The workflows will run automatically once you push them to your repository.

### 2. Configure Secrets (Optional)

For Packagist integration, add these secrets to your repository:

1. Go to **Settings** → **Secrets and variables** → **Actions**
2. Add the following secrets:
   - `PACKAGIST_USERNAME`: Your Packagist username
   - `PACKAGIST_TOKEN`: Your Packagist API token

**How to get Packagist API token:**
1. Log in to [Packagist.org](https://packagist.org/)
2. Go to **Profile** → **Settings** → **API Token**
3. Create a new token or use an existing one

### 3. Enable Codecov (Optional)

For code coverage reports:

1. Sign up at [Codecov.io](https://codecov.io/) with your GitHub account
2. Add your repository to Codecov
3. No additional configuration needed - the workflow handles uploads automatically

## Badges

Add these badges to your README.md:

```markdown
[![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml)
[![Release](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml)
[![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![Total Downloads](https://poser.pugx.org/yourdudeken/mpesa/downloads)](https://packagist.org/packages/yourdudeken/mpesa)
[![License](https://poser.pugx.org/yourdudeken/mpesa/license.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![PHP Version](https://img.shields.io/packagist/php-v/yourdudeken/mpesa.svg)](https://packagist.org/packages/yourdudeken/mpesa)
[![codecov](https://codecov.io/gh/yourdudeken/mpesa/branch/main/graph/badge.svg)](https://codecov.io/gh/yourdudeken/mpesa)
```

## Release Process

### Automatic Releases (Recommended)

1. Make your changes
2. Commit with conventional commit messages:
   ```bash
   git add .
   git commit -m "feat: add new payment method"
   git push origin main
   ```
3. The Release workflow automatically:
   - Determines the version number
   - Creates a GitHub release
   - Updates CHANGELOG.md
   - Creates a git tag
   - Triggers Packagist update

### Manual Releases

1. Create a tag manually:
   ```bash
   git tag v1.2.3
   git push origin v1.2.3
   ```
2. The Tag Release workflow creates the release

## Workflow Status

Check the status of your workflows:
- Go to **Actions** tab in your GitHub repository
- View running and completed workflows
- Click on any workflow run to see detailed logs

## Troubleshooting

### Tests Failing

1. Check the **Actions** tab for error details
2. Run tests locally: `vendor/bin/phpunit`
3. Fix issues and push again

### Release Not Created

1. Ensure you're pushing to `main` or `master` branch
2. Check commit message format (must follow Conventional Commits)
3. Verify the workflow ran in the **Actions** tab
4. Check for `[skip ci]` in commit message (this skips the workflow)

### Packagist Not Updating

1. Verify secrets are configured correctly
2. Check Packagist API token is valid
3. Ensure package is registered on Packagist
4. The workflow continues even if Packagist update fails

## Skip CI

To skip CI/CD workflows, add `[skip ci]` to your commit message:

```bash
git commit -m "docs: update README [skip ci]"
```

## Migration from Travis CI

The old `.travis.yml` file can be removed as it's been replaced by GitHub Actions:

```bash
git rm .travis.yml
git commit -m "chore: migrate from Travis CI to GitHub Actions"
git push origin main
```

## Additional Resources

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Semantic Release](https://semantic-release.gitbook.io/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Codecov Documentation](https://docs.codecov.com/)
