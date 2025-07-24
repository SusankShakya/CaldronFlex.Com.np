# Automation Guide

## Overview
This guide defines automated verification systems that enforce project rules, standards, and workflows automatically.

## Automation Layers

### 1. Pre-Commit Hooks
Local checks that run before code is committed.
- `pre-commit-config.yaml` - Pre-commit hook configuration
- `git-hooks/` - Custom git hooks

### 2. Continuous Integration
Automated checks that run on every push/PR.
- `ci-config.md` - CI/CD pipeline configuration
- `github-actions/` - GitHub Actions workflows

### 3. Linting & Static Analysis
Code quality enforcement tools.
- `linting-config.md` - Linter configurations
- `phpstan.neon` - PHP static analysis
- `eslint.config.js` - JavaScript linting

### 4. Automated Testing
Test execution and coverage enforcement.
- `test-automation.md` - Test automation setup
- `coverage-requirements.md` - Coverage thresholds

### 5. Security Scanning
Automated security vulnerability detection.
- `security-scanning.md` - Security tool configuration
- `dependency-check.md` - Dependency scanning

## Implementation Priority
1. Pre-commit hooks (immediate feedback)
2. Linting (code quality)
3. Testing (functionality)
4. CI/CD (integration)
5. Security (vulnerability prevention)

## Rule Enforcement
All automated checks enforce the rules defined in:
- `.kilocode/USER_RULES.md`
- `.kilocode/code-standards/`
- `.kilocode/workflows/`