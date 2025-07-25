# PROJECT USER RULES

## Core Principles

### 1. Use 'project_rules' as the Knowledge Base
Always refer to 'project_rules' to understand the context of the project. Do not code anything outside of the context provided in the 'project_rules' folder. This folder serves as the knowledge base and contains the fundamental rules and guidelines that should always be followed. If something is unclear, check this folder before proceeding with any coding.

### 2. Verify Information
Always verify information from the context before presenting it. Do not make assumptions or speculate without clear evidence.

### 3. Follow 'implementation-plan.mdc' for Feature Development
When implementing a new feature, strictly follow the steps outlined in 'implementation-plan.mdc'. Every step is listed in sequence, and each must be completed in order. After completing each step, update 'implementation-plan.mdc' with the word "Done" and a two-line summary of what steps were taken. This ensures a clear work log, helping maintain transparency and tracking progress effectively.

## Development Guidelines

### 4. File-by-File Changes
Make changes file by file and give the user the chance to spot mistakes.

### 5. No Apologies
Never use apologies.

### 6. No Understanding Feedback
Avoid giving feedback about understanding in the comments or documentation.

### 7. No Whitespace Suggestions
Don't suggest whitespace changes.

### 8. No Summaries
Do not provide unnecessary summaries of changes made. Only summarize if the user explicitly asks for a brief overview after changes.

### 9. No Inventions
Don't invent changes other than what's explicitly requested.

### 10. No Unnecessary Confirmations
Don't ask for confirmation of information already provided in the context.

### 11. Preserve Existing Code
Don't remove unrelated code or functionalities. Pay attention to preserving existing structures.

### 12. Single Chunk Edits
Provide all edits in a single chunk instead of multiple-step instructions or explanations for the same file.

### 13. No Implementation Checks
Don't ask the user to verify implementations that are visible in the provided context. However, if a change affects functionality, provide an automated check or test instead of asking for manual verification.

### 14. No Unnecessary Updates
Don't suggest updates or changes to files when there are no actual modifications needed.

### 15. Provide Real File Links
Always provide links to the real files, not the context-generated file.

### 16. No Current Implementation
Don't discuss the current implementation unless the user asks for it or it is necessary to explain the impact of a requested change.

### 17. Check Context Generated File Content
Remember to check the context-generated file for the current file contents and implementations.

## Code Quality Standards

### 18. Use Explicit Variable Names
Prefer descriptive, explicit variable names over short, ambiguous ones to enhance code readability.

### 19. Follow Consistent Coding Style
Adhere to the existing coding style in the project for consistency.

### 20. Prioritize Performance
When suggesting changes, consider and prioritize code performance where applicable.

### 21. Security-First Approach
Always consider security implications when modifying or suggesting code changes.

### 22. Test Coverage
Suggest or include appropriate unit tests for new or modified code.

### 23. Error Handling
Implement robust error handling and logging where necessary.

### 24. Modular Design
Encourage modular design principles to improve code maintainability and reusability.

### 25. Version Compatibility
Ensure suggested changes are compatible with the project's specific language or framework versions. If a version conflict arises, suggest an alternative.

### 26. Avoid Magic Numbers
Replace hard-coded values with named constants to improve code clarity and maintainability.

### 27. Consider Edge Cases
When implementing logic, always consider and handle potential edge cases.

### 28. Use Assertions
Include assertions wherever possible to validate assumptions and catch potential errors early.