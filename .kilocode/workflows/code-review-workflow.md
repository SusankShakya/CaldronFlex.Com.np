# Code Review Workflow

## Review Preparation
- [ ] Code complete and tested
- [ ] Self-review performed
- [ ] USER_RULES.md compliance checked
- [ ] Documentation updated

## Review Checklist

### 1. Code Quality
- [ ] **Naming**: Variables and functions have explicit, descriptive names
- [ ] **Structure**: Code follows modular design principles
- [ ] **Complexity**: No overly complex functions (split if needed)
- [ ] **DRY**: No unnecessary code duplication
- [ ] **Comments**: Complex logic is well-commented

### 2. Functionality
- [ ] **Requirements**: All requirements implemented
- [ ] **Edge Cases**: Edge cases handled properly
- [ ] **Error Handling**: Robust error handling in place
- [ ] **Performance**: No obvious performance issues
- [ ] **Security**: No security vulnerabilities introduced

### 3. Standards Compliance
- [ ] **Coding Style**: Matches project conventions
- [ ] **File Organization**: Files in correct locations
- [ ] **Dependencies**: No unnecessary dependencies added
- [ ] **Compatibility**: Works with supported versions

### 4. Testing
- [ ] **Test Coverage**: New code has tests
- [ ] **Test Quality**: Tests are meaningful
- [ ] **All Tests Pass**: No broken tests
- [ ] **Manual Testing**: Feature tested manually

### 5. Documentation
- [ ] **Code Comments**: Inline documentation present
- [ ] **API Docs**: API changes documented
- [ ] **User Docs**: User-facing changes documented
- [ ] **Memory Banks**: Relevant updates made

## Review Process

### For Reviewers
1. **Understand Context**
   - Read the implementation plan
   - Understand the problem being solved
   - Check related issues/features

2. **Review Systematically**
   - Start with high-level structure
   - Then dive into implementation details
   - Check against checklist above

3. **Provide Constructive Feedback**
   - Be specific about issues
   - Suggest improvements
   - Acknowledge good practices
   - Ask questions if unclear

4. **categorize Feedback**
   - **Must Fix**: Blocks approval
   - **Should Fix**: Important but not blocking
   - **Consider**: Suggestions for improvement
   - **Nitpick**: Minor style issues

### For Authors
1. **Respond to All Comments**
   - Address or acknowledge each point
   - Explain reasoning for decisions
   - Ask for clarification if needed

2. **Make Required Changes**
   - Fix all "Must Fix" items
   - Consider "Should Fix" items
   - Document why if not implementing suggestion

3. **Request Re-review**
   - After making changes
   - Summarize what was changed
   - Point out any concerns

## Approval Criteria
- [ ] All "Must Fix" items addressed
- [ ] Tests passing
- [ ] Documentation complete
- [ ] No unresolved discussions
- [ ] Follows USER_RULES.md