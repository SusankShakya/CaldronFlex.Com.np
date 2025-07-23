# Technology Stack & Build System

## Core Architecture

### Primary Platforms
- **Rise CRM 3.9.3**: Core business logic and workflow management (CodeIgniter 4 framework)
- **Bagisto**: E-commerce platform for store.caldronflex.com.np (Laravel 11 framework)
- **Integration**: API-based synchronization between platforms

### Tech Stack

#### Rise CRM (Primary Platform)
- **Framework**: CodeIgniter 4
- **Language**: PHP 8.1+
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap
- **File Handling**: Built-in file management system

#### Bagisto (E-commerce)
- **Framework**: Laravel 11
- **Language**: PHP 8.2+
- **Frontend**: Vue.js, Vite build system
- **Database**: MySQL (shared with Rise CRM)
- **Features**: Multi-language, payment gateways, inventory management

### Hosting Constraints
- **Environment**: cPanel hosting (NO Docker support)
- **Resources**: 3GB RAM, 1.5TB Storage, unlimited bandwidth
- **Deployment**: Traditional LAMP stack deployment
- **Backup**: Automated daily backups to bck.caldronflex.com.np

### Key Dependencies

#### Rise CRM Extensions Needed
- TIFF to JPG conversion library
- WhatsApp proxy integration
- File annotation system
- Queue-based task management

#### Bagisto Customizations
- Printing-specific product types
- Dynamic pricing for size-based products
- AR/VR file support (already available)
- API endpoints for Rise CRM sync

## Common Commands

### Development Setup
```bash
# Rise CRM setup
php spark serve
php spark migrate
php spark db:seed

# Bagisto setup
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
```

### File Operations
```bash
# File permissions for cPanel
chmod 755 folders
chmod 644 files
chown user:user files

# Large file handling
# Configure php.ini for 500MB uploads
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
```

### Database Management
```bash
# MySQL backup (daily automated)
mysqldump -u user -p database > backup.sql

# Sync between platforms
php artisan sync:customers
php artisan sync:products
php artisan sync:orders
```

## Integration Requirements

### API Development
- RESTful APIs for platform synchronization
- Real-time data sync for customers, products, orders
- Secure authentication between platforms
- Error handling and logging

### File Management
- Progressive upload system for large files
- TIFF to JPG conversion with watermarking
- Secure file access controls
- Version control for design files

### Communication Systems
- WhatsApp proxy server integration
- Email SMTP configuration
- Template-based notifications
- Bilingual message support

## Performance Considerations

### cPanel Optimization
- Efficient database queries
- Proper caching strategies
- File optimization for large uploads
- Memory management for 3GB constraint

### Security
- Role-based access controls
- Secure file handling
- API authentication
- Audit trails for all operations