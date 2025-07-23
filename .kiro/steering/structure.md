# Project Structure & Organization

## Repository Layout

```
/
├── .kiro/                          # Kiro AI assistant configuration
│   ├── steering/                   # AI guidance rules
│   └── specs/                      # Feature specifications
├── .PRD/                           # Product Requirements Documentation
├── bagisto/                        # E-commerce platform (Laravel)
├── rise-crm-3.9.3/               # Primary CRM platform (CodeIgniter)
├── project_rules/                  # Development guidelines
└── implementation-plan.mdc         # Implementation roadmap
```

## Rise CRM Structure (Primary Platform)

```
rise-crm-3.9.3/
├── app/
│   ├── Controllers/               # Business logic controllers
│   ├── Models/                    # Database models
│   │   ├── Users_model.php        # User management
│   │   ├── Clients_model.php      # Client/customer management
│   │   ├── Projects_model.php     # Project workflow
│   │   ├── Tasks_model.php        # Task management
│   │   ├── Orders_model.php       # Order processing
│   │   ├── Invoices_model.php     # Invoice generation
│   │   └── Items_model.php        # Product catalog
│   ├── Views/                     # UI templates
│   └── Config/                    # Configuration files
├── assets/                        # Static assets (CSS, JS, images)
├── files/                         # File uploads and storage
├── system/                        # CodeIgniter framework
└── writable/                      # Logs and cache
```

## Bagisto Structure (E-commerce)

```
bagisto/
├── app/                           # Laravel application
├── packages/Webkul/               # Bagisto core packages
│   ├── Admin/                     # Admin interface
│   ├── Customer/                  # Customer management
│   ├── Product/                   # Product catalog
│   ├── Sales/                     # Order management
│   ├── Inventory/                 # Stock management
│   ├── Payment/                   # Payment processing
│   └── Shop/                      # Frontend storefront
├── public/                        # Web accessible files
├── resources/                     # Views, assets, language files
├── storage/                       # File storage and logs
└── database/                      # Migrations and seeders
```

## Custom Extensions Structure

### Rise CRM Extensions
```
rise-crm-3.9.3/app/
├── Controllers/
│   ├── Printing_workflow.php     # Custom printing workflow
│   ├── Design_annotation.php     # File annotation system
│   ├── Whatsapp_integration.php  # WhatsApp proxy
│   └── File_conversion.php       # TIFF to JPG conversion
├── Models/
│   ├── Design_files_model.php    # Design file management
│   ├── Annotations_model.php     # Client annotations
│   ├── Credit_management_model.php # Organization credit
│   └── Queue_tasks_model.php     # Task queue system
└── Views/printing/                # Printing-specific UI
```

### Bagisto Extensions
```
bagisto/packages/Webkul/
├── PrintingProducts/              # Custom printing products
│   ├── Models/                    # Product models
│   ├── Controllers/               # Product controllers
│   └── Resources/                 # Views and assets
├── DynamicPricing/                # Size-based pricing
├── ARViewer/                      # AR/VR product preview
└── APISync/                       # Rise CRM synchronization
```

## File Organization Conventions

### Naming Conventions
- **Controllers**: PascalCase with descriptive names
- **Models**: Lowercase with underscores, ending in `_model.php`
- **Views**: Lowercase with underscores
- **Database tables**: Lowercase with underscores
- **API endpoints**: RESTful naming with kebab-case

### Directory Structure Rules
- Keep platform-specific code in respective directories
- Shared utilities in common directories
- Custom extensions clearly separated from core code
- Configuration files environment-specific

## Database Organization

### Shared Tables (MySQL)
- `users` - User authentication and roles
- `customers` - Client information (synced)
- `products` - Product catalog (synced)
- `orders` - Order data (synced)

### Rise CRM Specific Tables
- `projects` - Printing projects
- `tasks` - Task queue and assignments
- `design_files` - File management
- `annotations` - Client feedback
- `credit_accounts` - Organization credit

### Bagisto Specific Tables
- Standard e-commerce tables
- `product_variants` - Size/material variants
- `ar_models` - AR/VR file associations

## API Structure

### Synchronization Endpoints
```
/api/v1/
├── sync/
│   ├── customers          # Customer data sync
│   ├── products           # Product catalog sync
│   ├── orders             # Order synchronization
│   └── inventory          # Stock level sync
├── printing/
│   ├── tasks              # Task management
│   ├── designs            # Design file operations
│   └── annotations        # Client feedback
└── communication/
    ├── whatsapp           # WhatsApp messaging
    └── notifications      # Email notifications
```

## Development Workflow

### Code Organization
1. **Extend existing models** rather than creating new ones
2. **Use existing authentication** systems from both platforms
3. **Follow framework conventions** (CodeIgniter for Rise, Laravel for Bagisto)
4. **Maintain backward compatibility** with existing features

### File Management
- Large files stored outside web root
- Progressive upload for 500MB+ files
- Automatic cleanup of temporary files
- Secure access controls for client files

### Integration Points
- API authentication between platforms
- Real-time data synchronization
- Shared session management
- Unified user experience across subdomains