# Badges for README.md

Add these badges to your main README.md file to show the status of your CI/CD pipelines:

## GitHub Actions Badges

```markdown
[![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml)
[![Release](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml)
[![GitHub release (latest by date)](https://img.shields.io/github/v/release/yourdudeken/mpesa)](https://github.com/yourdudeken/mpesa/releases)
[![GitHub](https://img.shields.io/github/license/yourdudeken/mpesa)](LICENSE.txt)
```

## Packagist Badges

```markdown
[![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable)](https://packagist.org/packages/yourdudeken/mpesa)
[![Total Downloads](https://poser.pugx.org/yourdudeken/mpesa/downloads)](https://packagist.org/packages/yourdudeken/mpesa)
[![License](https://poser.pugx.org/yourdudeken/mpesa/license)](https://packagist.org/packages/yourdudeken/mpesa)
[![PHP Version Require](https://poser.pugx.org/yourdudeken/mpesa/require/php)](https://packagist.org/packages/yourdudeken/mpesa)
```

## Code Quality Badges

```markdown
[![codecov](https://codecov.io/gh/yourdudeken/mpesa/branch/main/graph/badge.svg)](https://codecov.io/gh/yourdudeken/mpesa)
[![Maintainability](https://api.codeclimate.com/v1/badges/YOUR_BADGE_ID/maintainability)](https://codeclimate.com/github/yourdudeken/mpesa/maintainability)
```

## All Badges Combined

```markdown
[![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml)
[![Release](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml/badge.svg)](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml)
[![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable)](https://packagist.org/packages/yourdudeken/mpesa)
[![Total Downloads](https://poser.pugx.org/yourdudeken/mpesa/downloads)](https://packagist.org/packages/yourdudeken/mpesa)
[![License](https://poser.pugx.org/yourdudeken/mpesa/license)](https://packagist.org/packages/yourdudeken/mpesa)
[![PHP Version Require](https://poser.pugx.org/yourdudeken/mpesa/require/php)](https://packagist.org/packages/yourdudeken/mpesa)
[![codecov](https://codecov.io/gh/yourdudeken/mpesa/branch/main/graph/badge.svg)](https://codecov.io/gh/yourdudeken/mpesa)
```

## Preview

Once added to your README.md, the badges will look like this:

![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg)
![Release](https://github.com/yourdudeken/mpesa/actions/workflows/release.yml/badge.svg)
![Latest Stable Version](https://poser.pugx.org/yourdudeken/mpesa/v/stable)
![Total Downloads](https://poser.pugx.org/yourdudeken/mpesa/downloads)
![License](https://poser.pugx.org/yourdudeken/mpesa/license)
![PHP Version Require](https://poser.pugx.org/yourdudeken/mpesa/require/php)

## Setup Instructions

1. Copy the badges you want from above
2. Paste them at the top of your README.md file (after the title)
3. For Codecov badge, sign up at https://codecov.io and connect your repository
4. For Code Climate badge, sign up at https://codeclimate.com and get your badge ID

## Customization

You can customize badge styles by adding `?style=` parameter:
- `?style=flat` - Flat style (default)
- `?style=flat-square` - Flat square style
- `?style=for-the-badge` - For the badge style
- `?style=plastic` - Plastic style
- `?style=social` - Social style

Example:
```markdown
[![CI](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml/badge.svg?style=flat-square)](https://github.com/yourdudeken/mpesa/actions/workflows/ci.yml)
```
