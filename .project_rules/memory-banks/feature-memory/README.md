# Feature Memory

This directory stores documentation for individual features implemented in the CaldronFlex system.

## Purpose
- Document feature specifications
- Track implementation decisions
- Record known issues and solutions
- Maintain feature-specific context

## Structure
Each feature should have its own markdown file named after the feature:
- `variant-management.md`
- `shared-sessions.md`
- `multi-tenant-routing.md`
- etc.

## Feature Documentation Template

```markdown
# [Feature Name]

**Date Created**: YYYY-MM-DD
**Last Updated**: YYYY-MM-DD
**Status**: [Active/Deprecated/In Development]

## Overview
Brief description of the feature and its purpose.

## Implementation Details
- Technical approach
- Key components involved
- Database schema changes
- API endpoints created

## Business Logic
- Rules and constraints
- Edge cases handled
- Validation requirements

## Known Issues
- Current limitations
- Workarounds in place
- Future improvements needed

## Related Files
- List of files that implement this feature
- Configuration files
- Database migrations

## Testing Considerations
- Test scenarios
- Edge cases to verify
- Performance benchmarks

## Maintenance Notes
- Common maintenance tasks
- Monitoring requirements
- Update procedures
```

## Best Practices
1. Create a new file for each major feature
2. Update documentation when feature changes
3. Include code snippets for complex logic
4. Cross-reference related features
5. Document both successes and failures