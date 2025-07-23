# CaldronFlex Multi-Subdomain Setup Instructions

## Overview
This document provides instructions for completing the setup of the CaldronFlex multi-subdomain architecture after the automated setup has been completed.

## Directory Structure Created
```
d:/xamp/htdocs/CaldronFlex.Com.np/
├── public_html/          (main store - caldronflex.com.np)
├── clients/              (CRM platform - clients.caldronflex.com.np)
├── api/                  (API services - api.caldronflex.com.np)
├── files/                (file storage - files.caldronflex.com.np)
├── backup/               (backup storage - bck.caldronflex.com.np)
├── sessions/             (shared session storage)
├── apache-vhosts/        (virtual host configurations)
└── backup_2025-07-23_18-54-36/  (backup of original Rise CRM)
```

## Manual Configuration Steps

### 1. Update Windows Hosts File

Add the following entries to your Windows hosts file for local testing:

**Location:** `C:\Windows\System32\drivers\etc\hosts`

**Note:** You need to edit this file as Administrator.

Add these lines:
```
# CaldronFlex Local Development
127.0.0.1    caldronflex.com.np
127.0.0.1    www.caldronflex.com.np
127.0.0.1    clients.caldronflex.com.np
127.0.0.1    api.caldronflex.com.np
127.0.0.1    files.caldronflex.com.np
127.0.0.1    bck.caldronflex.com.np
```

### 2. Configure Apache Virtual Hosts

1. Open XAMPP Apache configuration:
   - File: `d:\xamp\apache\conf\extra\httpd-vhosts.conf`

2. Add the contents from `apache-vhosts/caldronflex-vhosts.conf` to this file.

3. Ensure the following line is uncommented in `d:\xamp\apache\conf\httpd.conf`:
   ```
   Include conf/extra/httpd-vhosts.conf
   ```

4. If using mod_headers for API CORS, ensure this module is enabled in `httpd.conf`:
   ```
   LoadModule headers_module modules/mod_headers.so
   ```

### 3. Create Apache Log Directory

Create the logs directory if it doesn't exist:
```
d:\xamp\apache\logs\
```

### 4. Restart Apache

After making these changes:
1. Stop Apache in XAMPP Control Panel
2. Start Apache in XAMPP Control Panel

### 5. Configure Rise CRM

Since Rise CRM is in pre-installation state, you'll need to:

1. Navigate to: http://clients.caldronflex.com.np
2. You'll be redirected to the installation page
3. Complete the Rise CRM installation process
4. Update the Rise CRM configuration to use the shared session settings

### 6. Test the Setup

Visit each subdomain to verify they're working:

1. **Main Store:** http://caldronflex.com.np
   - Should show the CaldronFlex main store test page

2. **CRM Platform:** http://clients.caldronflex.com.np
   - Should redirect to Rise CRM installation or show Rise CRM if installed

3. **API Service:** http://api.caldronflex.com.np
   - Should return JSON response with API information

4. **File Storage:** http://files.caldronflex.com.np
   - Should show the file storage test page

5. **Backup Storage:** http://bck.caldronflex.com.np
   - Should show the backup storage page (restricted access)

## Session Sharing Implementation

To implement session sharing in your PHP applications:

1. Include the shared session configuration at the beginning of your PHP files:
   ```php
   require_once '/path/to/shared-session-config.php';
   startSharedSession();
   ```

2. The configuration ensures:
   - Sessions are shared across all subdomains
   - Session cookies use `.caldronflex.com.np` domain
   - Sessions are stored in the central `sessions/` directory

## Security Recommendations

1. **HTTPS:** Configure SSL certificates for all subdomains in production
2. **Access Control:** Restrict access to backup subdomain by IP
3. **File Permissions:** Set appropriate permissions on the sessions directory
4. **Authentication:** Implement proper authentication for sensitive areas

## Troubleshooting

### If subdomains don't resolve:
- Verify hosts file entries are saved
- Flush DNS cache: `ipconfig /flushdns`
- Check Apache error logs: `d:\xamp\apache\logs\error.log`

### If sessions aren't shared:
- Check session save path permissions
- Verify cookie domain in browser developer tools
- Ensure all applications use the shared session configuration

### If Apache won't start:
- Check for port conflicts (80, 443)
- Verify virtual host syntax: `d:\xamp\apache\bin\httpd -S`
- Check Apache error logs

## Next Steps

1. Complete Rise CRM installation at http://clients.caldronflex.com.np
2. Implement authentication and authorization
3. Develop the main store frontend
4. Create API endpoints
5. Set up automated backup procedures

## Files Created

- `shared-session-config.php` - Shared session configuration
- `apache-vhosts/caldronflex-vhosts.conf` - Apache virtual host configurations
- Test index.php files in each subdomain directory
- This instruction file

## Backup Information

A complete backup of the original Rise CRM installation has been created at:
`d:/xamp/htdocs/CaldronFlex.Com.np/backup_2025-07-23_18-54-36/`

---

For additional support or questions, refer to the project documentation or contact the development team.