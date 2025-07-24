# Bug Fix Workflow

## Emergency Assessment
- **Severity**: Critical / High / Medium / Low
- **Impact**: Number of users affected
- **Workaround**: Available? Yes / No

## Workflow Steps

### Phase 1: Investigation
1. **Reproduce the Bug**
   - Document exact steps
   - Capture error messages
   - Note environment details
   - Check `.project_rules/memory-banks/error-solutions/`

2. **Root Cause Analysis**
   - Review recent changes
   - Check logs and metrics
   - Identify affected code
   - Document findings

### Phase 2: Solution Design
3. **Plan the Fix**
   - Identify minimal change needed
   - Consider side effects
   - Plan testing approach
   - Review similar past fixes

4. **Create Test Case**
   - Write failing test first
   - Cover the exact bug scenario
   - Add edge case tests

### Phase 3: Implementation
5. **Implement Fix**
   - Make minimal necessary changes
   - Follow USER_RULES.md
   - Preserve existing functionality
   - Comment the fix reason

6. **Verify Fix**
   - Run the failing test (should pass)
   - Run full test suite
   - Test manually
   - Check for regressions

### Phase 4: Documentation
7. **Document the Fix**
   - Update error-solutions/common-errors.md
   - Add prevention guidelines
   - Document in code comments
   - Update related documentation

### Phase 5: Deployment
8. **Deploy Fix**
   - Follow emergency-fix-workflow.md if critical
   - Otherwise use standard deployment
   - Monitor closely post-deployment

### Phase 6: Follow-up
9. **Post-mortem**
   - Document what went wrong
   - Identify prevention measures
   - Update monitoring/alerts
   - Share learnings with team

## Bug Fix Template
```
## Bug ID: BUG-XXXX
**Date**: YYYY-MM-DD
**Reporter**: [Name]
**Severity**: [Critical/High/Medium/Low]

### Description
[What is happening]

### Expected Behavior
[What should happen]

### Steps to Reproduce
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Root Cause
[Technical explanation]

### Solution
[What was changed and why]

### Testing
[How it was tested]

### Prevention
[How to prevent similar issues]