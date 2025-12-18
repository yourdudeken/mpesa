# GitHub CI/CD Setup for M-Pesa Package

This repository uses GitHub Actions for continuous integration, automated releases, and package publishing to Packagist.

## Workflows Overview

### 1. CI Workflow (`ci.yml`)
**Triggers:** Push and Pull Requests to `main` and `develop` branches

**Jobs:**
- **Tests**: Runs PHPUnit tests across multiple PHP versions (7.4, 8.0, 8.1, 8.2, 8.3)
- **Code Quality**: Checks code style using PHP CS Fixer
- **Security**: Runs composer security audit
- **Coverage**: Generates code coverage report and uploads to Codecov (PHP 8.2 only)

### 2. Release Workflow (`release.yml`)
**Triggers:** Push to `main` branch

**Jobs:**
- **Release**: 
  - Runs tests before release
  - Uses semantic-release to automatically version the package
  - Generates CHANGELOG.md
  - Creates GitHub releases with tags
  - Commits version changes back to the repository
  
- **Packagist**: 
  - Automatically notifies Packagist to update the package

### 3. Tag Release Workflow (`tag-release.yml`)
**Triggers:** Push of version tags (e.g., `v1.0.0`)

**Jobs:**
- **Build**: Creates release archives (.tar.gz and .zip)
- **GitHub Release**: Attaches archives to GitHub releases
- **Notify Packagist**: Updates Packagist with the new version

## Required GitHub Secrets

To enable full CI/CD functionality, you need to configure the following secrets in your GitHub repository:

### Packagist Integration
1. Go to your GitHub repository → Settings → Secrets and variables → Actions
2. Add the following secrets:

| Secret Name | Description | How to Get |
|-------------|-------------|------------|
| `PACKAGIST_USERNAME` | Your Packagist username | Your Packagist account username |
| `PACKAGIST_TOKEN` | Packagist API token | Generate at https://packagist.org/profile/ |

**Note:** `GITHUB_TOKEN` is automatically provided by GitHub Actions.

## Semantic Versioning

This project uses [Conventional Commits](https://www.conventionalcommits.org/) for automated versioning:

### Commit Message Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types and Version Bumps
- `fix:` - Patch release (0.0.x) - Bug fixes
- `feat:` - Minor release (0.x.0) - New features
- `BREAKING CHANGE:` - Major release (x.0.0) - Breaking changes
- `chore:`, `docs:`, `style:`, `refactor:`, `test:` - No version bump

### Examples
```bash
# Patch release (1.0.0 → 1.0.1)
git commit -m "fix: resolve authentication token expiry issue"

# Minor release (1.0.0 → 1.1.0)
git commit -m "feat: add B2Pochi transaction support"

# Major release (1.0.0 → 2.0.0)
git commit -m "feat: redesign API interface

BREAKING CHANGE: The Authenticator class constructor now requires configuration array"
```

## Publishing to Packagist

### Initial Setup
1. **Register on Packagist**: Go to https://packagist.org and create an account
2. **Submit Package**: Click "Submit" and enter your GitHub repository URL: `https://github.com/yourdudeken/mpesa`
3. **Generate API Token**: 
   - Go to https://packagist.org/profile/
   - Click "Show API Token" or "Create new token"
   - Copy the token
4. **Add GitHub Secrets**: Add `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` to your repository secrets

### Automatic Updates
Once configured, Packagist will automatically update when:
- A new release is created on the `main` branch
- A new version tag is pushed

### Manual Update
If needed, you can manually trigger a Packagist update:
```bash
curl -XPOST -H'content-type:application/json' \
  'https://packagist.org/api/update-package?username=YOUR_USERNAME&apiToken=YOUR_TOKEN' \
  -d'{"repository":{"url":"https://packagist.org/packages/yourdudeken/mpesa"}}'
```

## Workflow Triggers

### Continuous Integration (CI)
- Runs on every push to `main` or `develop`
- Runs on every pull request to `main` or `develop`

### Release
- Runs on every push to `main` (after CI passes)
- Automatically determines version based on commit messages
- Creates GitHub release with changelog
- Notifies Packagist

### Tag Release
- Runs when you manually create and push a version tag
- Example: `git tag v1.2.3 && git push origin v1.2.3`

## Development Workflow

### For Contributors
1. Create a feature branch from `develop`
2. Make changes and commit using conventional commit messages
3. Push and create a pull request to `develop`
4. CI will run tests automatically
5. After review and merge to `develop`, create PR to `main`

### For Maintainers
1. Merge approved PRs to `main`
2. The release workflow will automatically:
   - Determine the next version
   - Update CHANGELOG.md
   - Create a GitHub release
   - Tag the release
   - Notify Packagist

## Code Coverage

Code coverage reports are generated for PHP 8.2 and uploaded to Codecov. To view coverage:

1. Sign up at https://codecov.io
2. Connect your GitHub repository
3. Coverage reports will appear automatically after CI runs

Add the badge to your README:
```markdown
[![codecov](https://codecov.io/gh/yourdudeken/mpesa/branch/main/graph/badge.svg)](https://codecov.io/gh/yourdudeken/mpesa)
```

## Testing Locally

Before pushing, you can test locally:

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Validate composer.json
composer validate --strict

# Check code style (if configured)
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Troubleshooting

### Release not created
- Check commit messages follow conventional commits format
- Ensure `GITHUB_TOKEN` has proper permissions
- Check workflow logs in Actions tab

### Packagist not updating
- Verify `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` secrets are set correctly
- Check that the package is registered on Packagist
- Manually trigger update using the curl command above

### Tests failing
- Check PHP version compatibility
- Ensure all dependencies are properly installed
- Review test logs in the Actions tab

## Additional Resources

- [Semantic Release Documentation](https://semantic-release.gitbook.io/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Packagist Documentation](https://packagist.org/about)
