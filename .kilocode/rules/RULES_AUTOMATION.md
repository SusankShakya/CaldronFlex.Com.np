# Rules Automation System

## Overview
This directory contains automated rule enforcement systems that ensure all project rules are followed consistently across the codebase.
also remember to to auto write what you have done with specific in the memory bank (automatically)
## Rule Categories

### 1. Code Quality Rules
- Variable naming conventions
- Function complexity limits
- Code documentation requirements
- Magic number prevention

### 2. Security Rules
- Input validation requirements
- Authentication checks
- SQL injection prevention
- XSS protection

### 3. Performance Rules
- Query optimization
- Caching requirements
- Resource usage limits
- API rate limiting

### 4. Workflow Rules
- Commit message format
- Branch naming conventions
- PR requirements
- Code review process

## Automation Components

### Rule Checkers
- `rule-checker.py` - Main rule validation script
- `security-rules.py` - Security-specific checks
- `performance-rules.py` - Performance validation
- `workflow-rules.sh` - Workflow enforcement

### Configuration Files
- `rules-config.json` - Rule definitions and thresholds
- `exceptions.json` - Allowed exceptions to rules
- `custom-rules.yaml` - Project-specific rules

### Integration Points
1. Pre-commit hooks
2. CI/CD pipelines
3. IDE plugins
4. Code review tools

## Usage

### Manual Check
```bash
python .kilocode/rules/rule-checker.py --all
```

### Specific Rule Check
```bash
python .kilocode/rules/rule-checker.py --rule=variable-naming
```

### Generate Report
```bash
python .kilocode/rules/rule-checker.py --report=html
```

## Rule Enforcement Levels

### Error (Blocking)
- Security vulnerabilities
- Critical performance issues
- Syntax errors
- Missing required documentation

### Warning (Non-blocking)
- Code style violations
- Minor performance issues
- Documentation improvements
- Best practice suggestions

### Info (Informational)
- Code metrics
- Complexity scores
- Coverage reports
- Improvement suggestions