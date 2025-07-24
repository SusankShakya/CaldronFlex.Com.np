# Common Errors and Solutions

## Error Catalog

### E001: Session Not Persisting Across Subdomains
**Error**: User login not maintained when switching between platforms
**Solution**: 
```php
// Ensure session cookie domain is set correctly
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '.caldronflex.com.np',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
```
**Prevention**: Always use shared-session-config.php

### E002: File Upload Path Issues
**Error**: Files not accessible after upload
**Solution**: Use centralized file storage with proper path resolution
**Prevention**: Always use the file helper functions

## Error Template
```
### EXXX: [Error Description]
**Error**: [Full error message or symptoms]
**Context**: [When this error occurs]
**Solution**: [Step-by-step solution]
**Code Example**: [If applicable]
**Prevention**: [How to avoid this error]
**Related**: [Links to related errors or documentation]