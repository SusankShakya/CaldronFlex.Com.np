# Technology Stack

**Last Updated**: 2025-07-23

## Core Technologies

### Backend
- **PHP 7.4+** - Primary server-side language
- **CodeIgniter 4** - PHP framework for the main application
- **MySQL 5.7+** - Primary database system
- **Apache 2.4** - Web server with mod_rewrite enabled

### Frontend
- **HTML5/CSS3** - Markup and styling
- **JavaScript (ES6+)** - Client-side scripting
- **jQuery 3.x** - DOM manipulation and AJAX
- **Bootstrap 4.x** - CSS framework
- **Font Awesome** - Icon library

### Development Tools
- **Composer** - PHP dependency management
- **npm/yarn** - JavaScript package management
- **Git** - Version control

## Framework Versions

### RISE CRM Platform (clients/)
- **Version**: 3.9.4
- **Framework**: Custom MVC based on CodeIgniter
- **PHP Requirements**: 7.4 - 8.2
- **Key Dependencies**:
  - PHPMailer for email
  - TCPDF for PDF generation
  - PHPSpreadsheet for Excel operations
  - Pusher for real-time features

### Main Store (public_html/)
- **Framework**: CodeIgniter 4.x
- **Template Engine**: PHP native
- **Session Handler**: File-based with custom configuration

## Database Architecture
- **Engine**: InnoDB (for transaction support)
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Naming Convention**: lowercase_with_underscores

## Third-Party Services

### Payment Processing
- Stripe API
- PayPal SDK
- Local payment gateways

### Communication
- SMTP/Email services
- SMS gateway integration
- Push notification services

### Storage
- Local file system
- CDN for static assets (optional)

## Server Requirements

### Minimum Requirements
- PHP 7.4+ with extensions:
  - mysqli
  - curl
  - gd
  - mbstring
  - openssl
  - json
  - xml
  - zip
- MySQL 5.7+
- Apache 2.4+ with mod_rewrite
- 2GB RAM minimum
- 10GB storage minimum

### Recommended Setup
- PHP 8.1
- MySQL 8.0
- 4GB+ RAM
- SSD storage
- SSL certificate
- Redis/Memcached for caching

## Security Considerations
- HTTPS enforced
- CSRF protection enabled
- XSS prevention measures
- SQL injection protection via prepared statements
- File upload restrictions
- Session security configurations

## Performance Optimizations
- OpCache enabled
- Query caching
- Asset minification
- Lazy loading for images
- Database indexing strategy
- CDN for static resources

## Monitoring & Logging
- Error logging to files
- Database query logging (development only)
- Access logs via Apache
- Performance monitoring tools integration ready