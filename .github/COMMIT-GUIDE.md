# Quick Reference: Commit Messages & Versioning

## Conventional Commits Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

## Commit Types & Version Impact

| Type | Version Bump | Example | Result |
|------|-------------|---------|--------|
| `feat` | **Minor** (1.0.0 → 1.1.0) | `feat: add B2Pochi support` | New feature |
| `fix` | **Patch** (1.0.0 → 1.0.1) | `fix: resolve timeout issue` | Bug fix |
| `perf` | **Patch** (1.0.0 → 1.0.1) | `perf: optimize API calls` | Performance |
| `feat!` or `BREAKING CHANGE` | **Major** (1.0.0 → 2.0.0) | `feat!: redesign API` | Breaking change |
| `docs` | **No bump** | `docs: update README` | Documentation |
| `style` | **No bump** | `style: format code` | Code style |
| `refactor` | **Patch** (1.0.0 → 1.0.1) | `refactor: simplify logic` | Code refactor |
| `test` | **No bump** | `test: add unit tests` | Tests |
| `chore` | **No bump** | `chore: update deps` | Maintenance |
| `ci` | **No bump** | `ci: update workflow` | CI/CD |

## Quick Examples

###  Good Commit Messages

```bash
# New feature (minor version bump)
git commit -m "feat: add transaction reversal method"

# Bug fix (patch version bump)
git commit -m "fix: handle null response from API"

# Performance improvement (patch version bump)
git commit -m "perf: cache authentication tokens"

# Breaking change (major version bump)
git commit -m "feat!: change STK push method signature

BREAKING CHANGE: phoneNumber parameter is now required"

# With scope
git commit -m "feat(stk): add retry mechanism for failed requests"

# Documentation (no version bump)
git commit -m "docs: add B2B usage examples"

# Multiple paragraphs
git commit -m "feat: add webhook signature verification

This adds support for verifying M-Pesa webhook signatures
to ensure callbacks are authentic.

Closes #123"
```

###  Bad Commit Messages

```bash
# Too vague
git commit -m "update code"
git commit -m "fix bug"
git commit -m "changes"

# Wrong format
git commit -m "Added new feature"  # Should be lowercase
git commit -m "Fix: bug"           # Type should be lowercase
git commit -m "feature: new API"   # Should be "feat" not "feature"
```

## Scopes (Optional)

Scopes help organize commits by component:

- `stk` - STK Push related
- `b2c` - B2C related
- `b2b` - B2B related
- `c2b` - C2B related
- `auth` - Authentication
- `config` - Configuration
- `docs` - Documentation
- `tests` - Testing

Example:
```bash
git commit -m "feat(stk): add timeout configuration option"
git commit -m "fix(b2c): correct amount validation"
```

## Breaking Changes

### Method 1: Using `!`
```bash
git commit -m "feat!: change API authentication method"
```

### Method 2: Using footer
```bash
git commit -m "feat: redesign callback handling

BREAKING CHANGE: Callback URLs must now use HTTPS.
HTTP callbacks are no longer supported."
```

## Multi-line Commits

```bash
git commit -m "feat: add comprehensive error handling

- Add custom exception classes
- Improve error messages
- Add retry logic for network errors

This improves the developer experience when handling
API errors and makes debugging easier.

Closes #45
Refs #67"
```

## Skip CI

To skip running CI/CD workflows:

```bash
git commit -m "docs: fix typo [skip ci]"
git commit -m "chore: update README [skip ci]"
```

## Release Examples

### Scenario 1: Bug Fix Release
```bash
# Current version: 1.2.3
git commit -m "fix: resolve STK push timeout"
git push origin main
# New version: 1.2.4
```

### Scenario 2: New Feature Release
```bash
# Current version: 1.2.4
git commit -m "feat: add B2Pochi payment support"
git push origin main
# New version: 1.3.0
```

### Scenario 3: Breaking Change Release
```bash
# Current version: 1.3.0
git commit -m "feat!: redesign configuration structure

BREAKING CHANGE: Configuration array structure has changed.
See migration guide in docs/MIGRATION.md"
git push origin main
# New version: 2.0.0
```

### Scenario 4: Multiple Commits
```bash
# Current version: 2.0.0
git commit -m "feat: add transaction status caching"
git commit -m "fix: correct date formatting"
git commit -m "docs: update installation guide"
git push origin main
# New version: 2.1.0 (highest bump wins: feat > fix > docs)
```

## Workflow Summary

```
1. Make changes → 2. Commit with format → 3. Push to branch
                                              ↓
                                    4. CI runs tests
                                              ↓
                                    5. Create PR (optional)
                                              ↓
                                    6. Merge to main
                                              ↓
                        7. Release workflow analyzes commits
                                              ↓
                        8. Version bumped & release created
```

## Tips

1. **Be descriptive** - Explain what and why, not how
2. **Use imperative mood** - "add feature" not "added feature"
3. **Keep subject under 50 chars** - Be concise
4. **Use body for details** - Explain complex changes
5. **Reference issues** - Use "Closes #123" or "Fixes #456"
6. **One logical change per commit** - Keep commits focused

## Checking Your Commit

Before pushing, verify your commit message:

```bash
# View last commit message
git log -1 --pretty=%B

# Amend if needed
git commit --amend -m "feat: correct commit message"
```

## Resources

- [Conventional Commits](https://www.conventionalcommits.org/)
- [Semantic Versioning](https://semver.org/)
- [Keep a Changelog](https://keepachangelog.com/)

---

**Remember: Good commit messages = Automatic releases!**
