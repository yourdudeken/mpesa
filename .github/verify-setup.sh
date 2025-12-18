#!/bin/bash

# CI/CD Setup Verification Script
# This script helps verify that your CI/CD pipeline is properly configured

set -e

echo " M-Pesa Package CI/CD Setup Verification"
echo "=========================================="
echo ""

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo " Error: Not a git repository"
    exit 1
fi

echo " Git repository detected"

# Check for GitHub Actions workflows
echo ""
echo " Checking GitHub Actions workflows..."

if [ -f ".github/workflows/ci.yml" ]; then
    echo "   CI workflow found"
else
    echo "   CI workflow missing"
fi

if [ -f ".github/workflows/release.yml" ]; then
    echo "   Release workflow found"
else
    echo "   Release workflow missing"
fi

if [ -f ".github/workflows/tag-release.yml" ]; then
    echo "   Tag release workflow found"
else
    echo "   Tag release workflow missing"
fi

# Check for semantic-release configuration
echo ""
echo " Checking semantic-release configuration..."

if [ -f ".releaserc.json" ]; then
    echo "   .releaserc.json found"
else
    echo "   .releaserc.json missing"
fi

# Check for CHANGELOG
echo ""
echo " Checking CHANGELOG..."

if [ -f "CHANGELOG.md" ]; then
    echo "   CHANGELOG.md found"
else
    echo "   CHANGELOG.md missing"
fi

# Check for CONTRIBUTING guide
echo ""
echo " Checking CONTRIBUTING guide..."

if [ -f "CONTRIBUTING.md" ]; then
    echo "   CONTRIBUTING.md found"
else
    echo "    CONTRIBUTING.md not found (optional)"
fi

# Check composer.json
echo ""
echo " Checking composer.json..."

if [ -f "composer.json" ]; then
    echo "   composer.json found"
    
    # Validate composer.json
    if command -v composer &> /dev/null; then
        if composer validate --quiet 2>/dev/null; then
            echo "   composer.json is valid"
        else
            echo "    composer.json validation warnings (run 'composer validate' for details)"
        fi
    else
        echo "    Composer not installed, skipping validation"
    fi
else
    echo "   composer.json missing"
fi

# Check PHPUnit configuration
echo ""
echo " Checking PHPUnit configuration..."

if [ -f "phpunit.xml" ]; then
    echo "   phpunit.xml found"
else
    echo "    phpunit.xml not found"
fi

# Check for tests
echo ""
echo " Checking tests..."

if [ -d "tests" ]; then
    TEST_COUNT=$(find tests -name "*Test.php" | wc -l)
    echo "   Tests directory found ($TEST_COUNT test files)"
else
    echo "   Tests directory missing"
fi

# Check README badges
echo ""
echo " Checking README badges..."

if [ -f "README.md" ]; then
    if grep -q "github.com/yourdudeken/mpesa/actions/workflows/ci.yml" README.md; then
        echo "   CI badge found in README"
    else
        echo "    CI badge not found in README"
    fi
    
    if grep -q "github.com/yourdudeken/mpesa/actions/workflows/release.yml" README.md; then
        echo "   Release badge found in README"
    else
        echo "    Release badge not found in README"
    fi
else
    echo "   README.md missing"
fi

# Check git remote
echo ""
echo " Checking git remote..."

REMOTE=$(git remote get-url origin 2>/dev/null || echo "")
if [ -n "$REMOTE" ]; then
    echo "   Git remote configured: $REMOTE"
    
    if [[ $REMOTE == *"github.com"* ]]; then
        echo "   GitHub remote detected"
    else
        echo "    Remote is not GitHub (GitHub Actions requires GitHub)"
    fi
else
    echo "    No git remote configured"
fi

# Check current branch
echo ""
echo " Checking current branch..."

BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "")
if [ -n "$BRANCH" ]; then
    echo "   Current branch: $BRANCH"
    
    if [ "$BRANCH" = "main" ] || [ "$BRANCH" = "master" ]; then
        echo "   On release branch"
    else
        echo "    Not on main/master branch (releases trigger from main/master)"
    fi
fi

# Summary
echo ""
echo "=========================================="
echo " Summary"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Commit and push your changes:"
echo "   git add ."
echo "   git commit -m \"feat: setup CI/CD pipeline\""
echo "   git push origin $BRANCH"
echo ""
echo "2. The CI workflow will run automatically on push"
echo ""
echo "3. To create a release, push to main/master with a conventional commit:"
echo "   git commit -m \"feat: add new feature\""
echo "   git push origin main"
echo ""
echo "4. (Optional) Configure Packagist secrets in GitHub:"
echo "   Settings → Secrets → Actions → New repository secret"
echo "   - PACKAGIST_USERNAME"
echo "   - PACKAGIST_TOKEN"
echo ""
echo "5. View workflow runs at:"
echo "   https://github.com/yourdudeken/mpesa/actions"
echo ""
echo "   For more information, see .github/CICD.md"
echo ""
