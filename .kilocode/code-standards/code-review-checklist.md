# Code Review Checklist

## Pre-Review Requirements
- [ ] Code compiles without warnings
- [ ] All tests pass
- [ ] No merge conflicts
- [ ] implementation-plan.mdc updated

## General Code Quality

### 1. Readability & Maintainability
- [ ] **Variable Names**: Descriptive and explicit (USER_RULES.md #18)
- [ ] **Function Names**: Clear intent, verb-based
- [ ] **Code Structure**: Logical organization, proper indentation
- [ ] **Comments**: Present where needed, accurate, and helpful
- [ ] **No Dead Code**: Removed commented-out code and unused functions

### 2. Logic & Functionality
- [ ] **Requirements Met**: All acceptance criteria satisfied
- [ ] **Edge Cases**: Handled appropriately (USER_RULES.md #27)
- [ ] **Error Handling**: Robust error handling (USER_RULES.md #23)
- [ ] **No Magic Numbers**: Constants used instead (USER_RULES.md #26)
- [ ] **Assertions**: Used where appropriate (USER_RULES.md #28)

### 3. Performance
- [ ] **Efficient Algorithms**: No obvious O(n²) where O(n) would work
- [ ] **Database Queries**: Optimized, using indexes
- [ ] **Caching**: Implemented where beneficial
- [ ] **Resource Management**: Connections/files properly closed
- [ ] **No N+1 Queries**: Eager loading used appropriately

### 4. Security (USER_RULES.md #21)
- [ ] **Input Validation**: All user inputs validated
- [ ] **SQL Injection**: Prepared statements used
- [ ] **XSS Prevention**: Output properly escaped
- [ ] **CSRF Protection**: Tokens implemented
- [ ] **Authentication**: Properly implemented
- [ ] **Authorization**: Access controls in place
- [ ] **Sensitive Data**: Not logged or exposed

### 5. Testing (USER_RULES.md #22)
- [ ] **Unit Tests**: New functionality covered
- [ ] **Integration Tests**: Key paths tested
- [ ] **Test Quality**: Tests are meaningful, not just coverage
- [ ] **Edge Cases Tested**: Boundary conditions covered
- [ ] **Mocking**: External dependencies properly mocked

### 6. Documentation
- [ ] **Code Comments**: Complex logic explained
- [ ] **Function Documentation**: PHPDoc/JSDoc present
- [ ] **API Documentation**: Endpoints documented
- [ ] **README Updates**: If applicable
- [ ] **Memory Banks**: Updated with learnings

## Language-Specific Checks

### PHP
- [ ] **Type Hints**: Used where possible
- [ ] **Namespace**: Properly organized
- [ ] **PSR Standards**: Followed
- [ ] **No Deprecated Functions**: Using current APIs

### JavaScript
- [ ] **Strict Mode**: Enabled
- [ ] **Async Handling**: Promises/async-await used correctly
- [ ] **Event Listeners**: Properly attached and removed
- [ ] **Memory Leaks**: No obvious leaks

### Database
- [ ] **Migrations**: Reversible
- [ ] **Indexes**: On foreign keys and searched columns
- [ ] **Data Types**: Appropriate for use case
- [ ] **Constraints**: Foreign keys, unique, not null

## Final Checks
- [ ] **No Debug Code**: console.log, var_dump removed
- [ ] **Configuration**: Environment-specific values not hardcoded
- [ ] **Backwards Compatibility**: Existing functionality preserved
- [ ] **Dependencies**: Necessary and up-to-date
- [ ] **License Compliance**: Third-party licenses checked

## Review Response Template
```markdown
## Review Status: [Approved/Needs Changes/Rejected]

### Strengths
- [What was done well]

### Must Fix (Blocking)
1. [Critical issue that must be resolved]

### Should Fix (Important)
1. [Important but not blocking]

### Consider (Suggestions)
1. [Nice to have improvements]

### Questions
1. [Clarification needed]
```

## Automated Checks to Implement
1. **Linting**: PHP_CodeSniffer, ESLint
2. **Static Analysis**: PHPStan, Psalm
3. **Security Scanning**: OWASP dependency check
4. **Code Coverage**: Minimum 80% for new code
5. **Complexity Analysis**: Cyclomatic complexity < 10