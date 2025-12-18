# Quick Reference: CI/CD Workflows

##  Quick Start

### 1. Configure Secrets
```bash
# Using GitHub CLI
gh secret set PACKAGIST_USERNAME
gh secret set PACKAGIST_TOKEN

# Or run the setup script
./.github/setup-cicd.sh
```

### 2. Commit with Conventional Commits
```bash
# Bug fix (patch: 1.0.0 → 1.0.1)
git commit -m "fix: resolve authentication timeout issue"

# New feature (minor: 1.0.0 → 1.1.0)
git commit -m "feat: add B2Pochi transaction support"

# Breaking change (major: 1.0.0 → 2.0.0)
git commit -m "feat: redesign API interface

BREAKING CHANGE: Constructor signature changed"
```

### 3. Push to Trigger CI/CD
```bash
git push origin main
```

##  Workflow Triggers

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| **CI** | Push/PR to `main` or `develop` | Run tests, code quality checks |
| **Release** | Push to `main` | Create release, update Packagist |
| **Tag Release** | Push tag `v*.*.*` | Build archives, create GitHub release |

##  Conventional Commit Types

| Type | Version Bump | Example |
|------|--------------|---------|
| `fix:` | Patch (0.0.X) | `fix: resolve null pointer exception` |
| `feat:` | Minor (0.X.0) | `feat: add new payment method` |
| `BREAKING CHANGE:` | Major (X.0.0) | `feat!: redesign API` |
| `chore:` | None | `chore: update dependencies` |
| `docs:` | None | `docs: update README` |
| `style:` | None | `style: format code` |
| `refactor:` | None | `refactor: simplify logic` |
| `test:` | None | `test: add unit tests` |
| `perf:` | Patch | `perf: optimize query` |

##  Release Process

### Automatic (Recommended)
1. Merge PR to `main` with conventional commits
2. GitHub Actions automatically:
   - Determines version from commits
   - Updates CHANGELOG.md
   - Creates GitHub release
   - Tags the release
   - Notifies Packagist

### Manual Tag Release
```bash
# Create and push a tag
git tag v1.2.3
git push origin v1.2.3

# This triggers the tag-release workflow
```

##  Testing Locally

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Validate composer.json
composer validate --strict

# Check code style
vendor/bin/php-cs-fixer fix --dry-run --diff
```

##  Packagist Setup

### First Time Setup
1. Create account at https://packagist.org
2. Submit package: https://github.com/yourdudeken/mpesa
3. Generate API token at https://packagist.org/profile/
4. Add secrets to GitHub (see step 1 above)

### Verify Auto-Update
- Check webhook at: https://packagist.org/packages/yourdudeken/mpesa
- Should show "GitHub Service Hook" as active

##  Monitoring

### GitHub Actions
- View workflow runs: https://github.com/yourdudeken/mpesa/actions
- Check workflow status badges in README

### Packagist
- Package page: https://packagist.org/packages/yourdudeken/mpesa
- Download stats and version info

### Code Coverage
- Codecov: https://codecov.io/gh/yourdudeken/mpesa
- Coverage reports generated on each CI run

##  Troubleshooting

### Release Not Created
```bash
# Check commit messages
git log --oneline

# Ensure conventional commit format
# Bad:  "updated feature"
# Good: "feat: add new feature"
```

### Packagist Not Updating
```bash
# Manual trigger
curl -XPOST -H'content-type:application/json' \
  'https://packagist.org/api/update-package?username=USERNAME&apiToken=TOKEN' \
  -d'{"repository":{"url":"https://packagist.org/packages/yourdudeken/mpesa"}}'
```

### Tests Failing
```bash
# Run locally to debug
composer test

# Check specific PHP version
docker run -v $(pwd):/app -w /app php:8.2-cli composer test
```

##  Resources

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Release](https://semantic-release.gitbook.io/)
- [GitHub Actions](https://docs.github.com/en/actions)
- [Packagist](https://packagist.org/about)

##  Best Practices

1. **Always use conventional commits** for automatic versioning
2. **Write descriptive commit messages** with body and footer
3. **Run tests locally** before pushing
4. **Create PRs to develop** first, then merge to main
5. **Review CHANGELOG.md** after each release
6. **Monitor workflow runs** in GitHub Actions
7. **Keep dependencies updated** via Dependabot

##  Need Help?

- Check workflow logs in GitHub Actions
- Review `.github/CICD_SETUP.md` for detailed documentation
- Open an issue on GitHub
