# Emergency Fix Workflow

## When to Use
- Production is down or severely impacted
- Data loss risk
- Security vulnerability actively exploited
- Critical business function broken

## Immediate Actions
1. **Assess and Communicate**
   - Determine scope of impact
   - Notify stakeholders immediately
   - Create incident channel/call

2. **Mitigate if Possible**
   - Apply temporary fix
   - Disable affected feature
   - Redirect traffic if needed

## Emergency Fix Process

### Phase 1: Rapid Diagnosis (15 min max)
1. **Identify the Issue**
   - Check recent deployments
   - Review error logs
   - Look for pattern changes
   - Check external dependencies

2. **Find Root Cause**
   - Isolate the problem
   - Identify exact failure point
   - Determine minimal fix needed

### Phase 2: Quick Fix (30 min max)
3. **Implement Fix**
   - Make minimal change only
   - No refactoring or improvements
   - Focus on restoration
   - Comment with "EMERGENCY FIX"

4. **Fast Testing**
   - Test the specific issue
   - Quick smoke test
   - Verify no obvious breaks

### Phase 3: Emergency Deployment
5. **Deploy Immediately**
   - Skip normal review process
   - Deploy directly to production
   - Monitor deployment closely

6. **Verify Fix**
   - Confirm issue resolved
   - Check system stability
   - Monitor for side effects

### Phase 4: Stabilization
7. **Extended Monitoring**
   - Watch metrics for 30 mins
   - Check user reports
   - Monitor error rates

8. **Communication**
   - Update stakeholders
   - Notify users if needed
   - Document timeline

### Phase 5: Follow-up (Within 24 hours)
9. **Proper Fix**
   - Review emergency fix
   - Implement proper solution
   - Add tests
   - Follow normal workflow

10. **Post-Mortem**
    - Document incident
    - Identify prevention measures
    - Update monitoring
    - Share learnings

## Emergency Contact Protocol
1. **Primary Contact**: [DevOps Lead]
2. **Secondary Contact**: [Tech Lead]
3. **Escalation**: [CTO/Manager]

## Emergency Fix Documentation
```
## Incident: INC-XXXX
**Date/Time**: YYYY-MM-DD HH:MM
**Severity**: CRITICAL
**Duration**: [Downtime duration]

### Impact
- Users affected: [Number/Percentage]
- Features affected: [List]
- Data impact: [None/Partial/Full]

### Timeline
- HH:MM - Issue detected
- HH:MM - Team notified
- HH:MM - Root cause identified
- HH:MM - Fix deployed
- HH:MM - Issue resolved
- HH:MM - Monitoring normal

### Root Cause
[Technical explanation]

### Emergency Fix Applied
[What was changed]

### Permanent Fix Plan
[Proper solution to implement]

### Prevention Measures
[How to prevent recurrence]

### Lessons Learned
[What we learned from this incident]
```

## Remember
- **Speed over perfection** during emergency
- **Document everything** for post-mortem
- **Communicate frequently** with stakeholders
- **Follow up properly** after crisis