# Development Environment Setup Guide

## Prerequisites Installation

### 1. Install PHP 8.2+ with Required Extensions

**For Windows (using Chocolatey):**
```powershell
# Install Chocolatey if not already installed
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# Install PHP with extensions
choco install php --version=8.2.0
choco install composer
```

**For Windows (Manual Installation):**
1. Download PHP 8.2+ from https://windows.php.net/download/
2. Extract to C:\php
3. Add C:\php to system PATH
4. Copy php.ini-development to php.ini
5. Enable required extensions in php.ini

**Required PHP Extensions:**
- mysqli
- curl
- mbstring
- intl
- json
- mysqlnd
- xml
- gd
- zlib
- pdo_mysql
- openssl
- fileinfo

### 2. Install MySQL/MariaDB

**Using XAMPP (Recommended for Development):**
```powershell
choco install xampp-81
```

**Or install MySQL separately:**
```powershell
choco install mysql
```

### 3. Install Composer (PHP Package Manager)

```powershell
choco install composer
```

## Platform Setup

### Rise CRM 3.9.3 Setup

1. **Database Configuration:**
   - Create database: `caldron_flex_crm`
   - Import initial schema from `rise-crm-3.9.3/install/database.sql`

2. **Environment Configuration:**
   - Copy configuration template to `.env` file
   - Configure database connection
   - Set application URL

3. **File Permissions:**
   - Ensure `writable/` directory is writable
   - Ensure `files/` directory is writable

### Rise CRM Store Module Setup

1. **Enable Store Module:**
   - In Rise CRM admin panel, navigate to **Modules** and enable **Store**.

2. **Configure Store Settings:**
   - Business details (name, address, contact)
   - Currency, tax rates, shipping methods

3. **Environment Variables:**
   - In `.env`, add or update:
     ```
     STORE_ENABLED=true
     STORE_BASE_URL=https://store.caldronflex.com.np
     ```

4. **Verify Module:**
   - Ensure **Store** appears in the CRM sidebar and product listing is accessible.

## Database Configuration

### Database Setup

- Create single database: `caldron_flex_db`
- Import initial schema from `rise-crm-3.9.3/install/database.sql`

### Database Connection Settings

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=caldron_flex_db
DB_USERNAME=caldron_user
DB_PASSWORD=secure_password
```

## Development Server Setup

### Rise CRM Development Server

```bash
cd rise-crm-3.9.3
php spark serve --host=localhost --port=8080
```
Access at: http://localhost:8080

## Subdomain Routing Configuration

For store.caldronflex.com.np:
1. **DNS:** Create an A record pointing `store.caldronflex.com.np` to your server IP.
2. **Web Server Virtual Host:**
   - Point `server_name store.caldronflex.com.np` to the `public/` directory of Rise CRM.

Sample Nginx configuration:
```nginx
server {
    listen 80;
    server_name store.caldronflex.com.np;
    root /var/www/rise-crm-3.9.3/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## File Upload Configuration

### PHP Configuration for Large Files

Edit `php.ini`:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
```

### Web Server Configuration

For Apache (`.htaccess` in `public/`):
```apache
php_value upload_max_filesize 500M
php_value post_max_size 500M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 512M
```

## WhatsApp Proxy Integration Setup

1. **Install Proxy Service:** Set up a WhatsApp proxy (e.g., open-wa or official Business API gateway).
2. **Environment Variables:** In `.env`, add:
   ```
   WHATSAPP_PROXY_URL=https://your-proxy-url.com
   WHATSAPP_API_KEY=your_api_key_here
   ```
3. **Configure in CRM:** Update `app/Config/WhatsApp.php`:
   ```php
   public $proxyUrl = getenv('WHATSAPP_PROXY_URL');
   public $apiKey    = getenv('WHATSAPP_API_KEY');
   ```
4. **Restart Server:** Restart PHP-FPM or web server to apply changes.

## Verification Steps

1. **PHP Installation:**
   ```bash
   php --version
   php -m | grep -E "(mysqli|curl|mbstring|intl|json|gd)"
   ```
2. **Database Connection:**
   ```bash
   mysql -u caldron_user -p caldron_flex_db
   ```
3. **Rise CRM Installation:**
   - Navigate to http://localhost:8080
   - Complete installation wizard and verify admin login.
4. **Store Module Verification:**
   - In CRM admin, ensure **Store** module loads and product list is visible.

## Next Steps

After successful setup:
1. Configure subdomain routing for `app.caldronflex.com.np` and `store.caldronflex.com.np`.
2. Configure Rise CRM Store module settings (currency, tax, shipping).
3. Configure WhatsApp proxy integration.
4. Implement file processing and printing workflow services.
5. Set up automated backup and disaster recovery system.

## Troubleshooting

### Common Issues:

1. **PHP not found:** Ensure PHP is in system PATH.
2. **Extension missing:** Enable required extensions in `php.ini` and restart server.
3. **Database connection failed:** Verify credentials and that MySQL/MariaDB service is running.
4. **Permission denied:** Ensure `writable/` and `files/` directories are writable by the web server.
5. **Memory limit exceeded:** Increase `memory_limit` in `php.ini`.

### Log Locations:

- Rise CRM: `writable/logs/`
- PHP: Check `error_log` location in `php.ini`