# Security Checklist

## Authentication & Authorization

### Authentication
- [ ] **Strong Password Policy**: Minimum 8 chars, complexity requirements
- [ ] **Password Hashing**: Using bcrypt/argon2 (never MD5/SHA1)
- [ ] **Session Management**: Secure session configuration
- [ ] **Remember Me**: Implemented securely with rotating tokens
- [ ] **Brute Force Protection**: Rate limiting on login attempts
- [ ] **Account Lockout**: Temporary lockout after failed attempts
- [ ] **Multi-Factor Authentication**: Available for sensitive accounts

### Authorization
- [ ] **Role-Based Access Control**: Properly implemented
- [ ] **Permission Checks**: On every protected resource
- [ ] **Privilege Escalation**: Not possible through parameter tampering
- [ ] **Default Deny**: Explicitly grant permissions
- [ ] **Admin Interfaces**: Additional protection layers

## Input Validation & Sanitization

### Input Validation
- [ ] **Server-Side Validation**: Never trust client-side only
- [ ] **Type Checking**: Verify data types
- [ ] **Length Limits**: Enforce maximum lengths
- [ ] **Format Validation**: Email, phone, URL formats
- [ ] **Business Logic Validation**: Valid values for context

### SQL Injection Prevention
```php
// Bad - Direct concatenation
$query = "SELECT * FROM users WHERE email = '" . $_POST['email'] . "'";

// Good - Prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $_POST['email']);

// Good - Query builder
$user = DB::table('users')
    ->where('email', $request->input('email'))
    ->first();
```

### XSS Prevention
```php
// Output encoding
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// In templates
{{ $userInput }} // Laravel Blade (auto-escapes)
<?= esc($userInput) ?> // CodeIgniter

// For JavaScript context
<script>
    var userName = <?= json_encode($userName) ?>;
</script>
```

## Data Protection

### Sensitive Data Handling
- [ ] **Encryption at Rest**: Database encryption for sensitive fields
- [ ] **Encryption in Transit**: HTTPS everywhere
- [ ] **PII Protection**: Personal data properly secured
- [ ] **Credit Card Data**: PCI compliance if storing
- [ ] **Password Storage**: Never in plain text
- [ ] **API Keys**: Stored securely, not in code

### File Upload Security
```php
// Validate file type
$allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
$extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($extension, $allowedTypes)) {
    throw new ValidationException('Invalid file type');
}

// Validate MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);

$allowedMimes = [
    'image/jpeg',
    'image/png',
    'application/pdf'
];

if (!in_array($mimeType, $allowedMimes)) {
    throw new ValidationException('Invalid file content');
}

// Generate safe filename
$safeFilename = bin2hex(random_bytes(16)) . '.' . $extension;

// Store outside web root
$uploadPath = '/var/uploads/' . $safeFilename;
```

## CSRF Protection

### Implementation
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// In forms
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

// Validate token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    throw new SecurityException('CSRF token mismatch');
}
```

## Security Headers

### Required Headers
```php
// Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'");

// Prevent clickjacking
header("X-Frame-Options: SAMEORIGIN");

// XSS Protection
header("X-XSS-Protection: 1; mode=block");

// Prevent MIME sniffing
header("X-Content-Type-Options: nosniff");

// Referrer Policy
header("Referrer-Policy: strict-origin-when-cross-origin");

// HSTS
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
```

## API Security

### API Authentication
- [ ] **API Keys**: Unique per client
- [ ] **OAuth2**: For third-party access
- [ ] **JWT**: Properly implemented with expiration
- [ ] **Rate Limiting**: Prevent abuse
- [ ] **API Versioning**: Maintain compatibility

### API Best Practices
```php
// Rate limiting
$rateLimiter = new RateLimiter();
if (!$rateLimiter->attempt($apiKey, 100)) { // 100 requests per hour
    return response()->json(['error' => 'Rate limit exceeded'], 429);
}

// Input validation
$validator = Validator::make($request->all(), [
    'email' => 'required|email',
    'amount' => 'required|numeric|min:0|max:10000'
]);

// Response filtering
return response()->json([
    'user' => $user->only(['id', 'name', 'email']) // Don't expose sensitive fields
]);
```

## Error Handling

### Safe Error Messages
```php
// Bad - Exposes internal details
catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()], 500);
}

// Good - Generic message for users
catch (Exception $e) {
    log_error($e->getMessage()); // Log full details
    return response()->json(['error' => 'An error occurred'], 500);
}
```

## Logging & Monitoring

### Security Logging
- [ ] **Failed Login Attempts**: Track and alert
- [ ] **Permission Denials**: Monitor unauthorized access attempts
- [ ] **Data Access**: Audit trail for sensitive data
- [ ] **Configuration Changes**: Track all system changes
- [ ] **Anomaly Detection**: Unusual patterns

### What to Log
```php
// Security event logging
SecurityLogger::log([
    'event' => 'failed_login',
    'user_id' => $userId,
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'timestamp' => now(),
    'details' => [
        'attempts' => $attemptCount,
        'account_locked' => $isLocked
    ]
]);
```

## Third-Party Dependencies

### Dependency Management
- [ ] **Known Vulnerabilities**: Regularly scan with tools
- [ ] **Updates**: Keep dependencies current
- [ ] **Minimal Dependencies**: Only what's necessary
- [ ] **License Compliance**: Verify licenses
- [ ] **Integrity Checks**: Verify package integrity

## Deployment Security

### Environment Configuration
- [ ] **Debug Mode**: Disabled in production
- [ ] **Error Display**: Disabled in production
- [ ] **Directory Listing**: Disabled
- [ ] **Version Disclosure**: Hidden
- [ ] **Default Credentials**: All changed
- [ ] **Unnecessary Services**: Disabled

### Server Hardening
- [ ] **Firewall Rules**: Properly configured
- [ ] **SSH Access**: Key-based only
- [ ] **Regular Updates**: OS and software patches
- [ ] **Backup Encryption**: Backups are encrypted
- [ ] **Monitoring**: Security monitoring active

## Regular Security Tasks
1. **Weekly**: Review security logs
2. **Monthly**: Update dependencies
3. **Quarterly**: Security audit
4. **Annually**: Penetration testing
5. **Ongoing**: Security training for team