# Feature Development Workflow

## Prerequisites
- [ ] Feature request clearly defined
- [ ] Technical requirements documented
- [ ] Dependencies identified
- [ ] Implementation plan created

## Workflow Steps

### Phase 1: Planning
1. **Review Requirements**
   - Check `.project_rules/memory-banks/feature-memory/` for similar features
   - Verify against USER_RULES.md
   - Identify affected components

2. **Create Implementation Plan**
   - Copy `implementation-plan.mdc` template
   - Break down into atomic steps
   - Estimate time for each step
   - Identify risks and mitigations

3. **Architecture Review**
   - Check architecture-decisions.md
   - Propose new decisions if needed
   - Document in memory banks

### Phase 2: Implementation
4. **Setup Development Environment**
   - Create feature branch
   - Verify local environment matches production
   - Run existing tests

5. **Implement Core Logic**
   - Follow file-by-file approach
   - Write code following code-standards
   - Add inline documentation
   - Status: Update implementation-plan.mdc

6. **Add Database Changes**
   - Create migration files
   - Test rollback procedures
   - Document schema changes
   - Status: Update implementation-plan.mdc

7. **Implement UI Components**
   - Follow existing UI patterns
   - Ensure responsive design
   - Add accessibility features
   - Status: Update implementation-plan.mdc

8. **Add API Endpoints**
   - Follow RESTful conventions
   - Implement authentication
   - Add rate limiting if needed
   - Status: Update implementation-plan.mdc

### Phase 3: Testing
9. **Unit Testing**
   - Write tests for new functions
   - Ensure 80%+ coverage
   - Test edge cases

10. **Integration Testing**
    - Test component interactions
    - Verify API responses
    - Check database integrity

11. **User Acceptance Testing**
    - Test all user scenarios
    - Verify UI/UX requirements
    - Check performance metrics

### Phase 4: Documentation
12. **Update Technical Docs**
    - Add to feature-memory
    - Update API documentation
    - Document configuration changes

13. **Update User Docs**
    - Create user guides
    - Update help sections
    - Add tooltips/hints

### Phase 5: Deployment
14. **Pre-deployment Check**
    - Code review completed
    - All tests passing
    - Documentation updated
    - Backup created

15. **Deploy to Staging**
    - Deploy code
    - Run smoke tests
    - Monitor for errors

16. **Deploy to Production**
    - Follow deployment-workflow.md
    - Monitor metrics
    - Be ready to rollback

### Phase 6: Post-deployment
17. **Monitor and Validate**
    - Check error logs
    - Monitor performance
    - Gather user feedback

18. **Update Memory Banks**
    - Document lessons learned
    - Add new patterns discovered
    - Update error solutions

## Completion Checklist
- [ ] All implementation steps marked "Done"
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] Code reviewed and approved
- [ ] Deployed successfully
- [ ] Memory banks updated