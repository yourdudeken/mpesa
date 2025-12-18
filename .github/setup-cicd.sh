#!/bin/bash

# CI/CD Setup Script for M-Pesa Package
# This script helps you configure GitHub secrets for CI/CD

set -e

echo "=========================================="
echo "M-Pesa Package CI/CD Setup"
echo "=========================================="
echo ""

# Check if GitHub CLI is installed
if ! command -v gh &> /dev/null; then
    echo "[ERROR] GitHub CLI (gh) is not installed."
    echo "Please install it from: https://cli.github.com/"
    echo ""
    echo "Alternatively, you can manually add secrets via:"
    echo "GitHub Repository -> Settings -> Secrets and variables -> Actions"
    exit 1
fi

echo "[OK] GitHub CLI found"
echo ""

# Check if user is authenticated
if ! gh auth status &> /dev/null; then
    echo "[ERROR] Not authenticated with GitHub CLI"
    echo "Please run: gh auth login"
    exit 1
fi

echo "[OK] Authenticated with GitHub"
echo ""

# Get repository information
REPO=$(gh repo view --json nameWithOwner -q .nameWithOwner)
echo "[INFO] Repository: $REPO"
echo ""

# Packagist setup
echo "=========================================="
echo "Packagist Configuration"
echo "=========================================="
echo ""
echo "To publish to Packagist, you need:"
echo "1. A Packagist account (https://packagist.org)"
echo "2. Your package submitted to Packagist"
echo "3. An API token from https://packagist.org/profile/"
echo ""

read -p "Do you want to configure Packagist secrets now? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Enter your Packagist username: " PACKAGIST_USERNAME
    read -sp "Enter your Packagist API token: " PACKAGIST_TOKEN
    echo ""
    
    # Set secrets
    echo "$PACKAGIST_USERNAME" | gh secret set PACKAGIST_USERNAME
    echo "$PACKAGIST_TOKEN" | gh secret set PACKAGIST_TOKEN
    
    echo "[OK] Packagist secrets configured"
else
    echo "[SKIP] Skipping Packagist configuration"
    echo "You can add these secrets later via:"
    echo "  gh secret set PACKAGIST_USERNAME"
    echo "  gh secret set PACKAGIST_TOKEN"
fi

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Commit your changes with conventional commit messages"
echo "2. Push to main branch to trigger CI/CD"
echo "3. Check Actions tab for workflow status"
echo ""
echo "Example commit:"
echo "  git add ."
echo "  git commit -m 'feat: add GitHub Actions CI/CD pipeline'"
echo "  git push origin main"
echo ""
echo "For more information, see .github/README.md"
echo ""
