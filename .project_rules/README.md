# Project Rules - Caldron Flex Printing Business Management System

## Project Context

This project involves building a comprehensive All-in-One Printing Business Management System for Caldron Flex using Rise CRM as the unified platform with built-in Store module.

## Core Architecture Decisions

### Platform Strategy
- **Primary Platform**: Rise CRM (confirmed by client as the "brain")
- **E-commerce Platform**: Rise CRM Store module for store.caldronflex.com.np
- **Architecture**: Unified platform with Rise CRM Store module
- **Hosting**: cPanel (no Docker support) - hard constraint

### Technical Constraints
- **RAM**: 3GB allocated
- **Storage**: 1.5TB capacity
- **Bandwidth**: Unlimited
- **Expected Load**: 30 concurrent users, 6 daily orders
- **Backup**: Automated daily backups to bck.caldronflex.com.np

## Existing Platform Analysis

### Rise CRM Existing Functionality
Based on analysis of rise-crm-3.9.3 codebase:

**Core Models Available:**
- Users_model: Complete user management with roles, authentication, client relationships
- Clients_model: Client/lead management with groups, status tracking, financial data
- Projects_model: Project management with status, members, tasks integration
- Tasks_model: Task management with assignments, status, priorities, milestones
- Orders_model: Order processing with items, status, tax calculations
- Invoices_model: Invoice generation and payment tracking
- Items_model: Product/service catalog management
- Roles_model: Role-based permission system

**Key Features Already Available:**
- User authentication and role management
- Client/lead management with organizational structure
- Project and task management system
- Order processing workflow
- Invoice generation and payment tracking
- File management system
- Notification system
- Custom fields support
- Multi-language support framework

### Rise CRM Store Module Existing Functionality
Based on analysis of Rise CRM Store module:

**Core Components Available:**
- Store controller: Handles product listing, cart operations, and checkout flows
- Items_model: Manages product catalog with core attributes (name, SKU, price, description)
- Orders_model: Processes orders, status tracking, tax and fee calculations
- Cart functionality: Add, update, remove items; save for later; session persistence
- Client portal integration: Customers access order history, reorder, and view invoices

**Key Features Already Available:**
- Basic storefront with product browsing and search
- Shopping cart with session-based persistence
- Checkout process with order confirmation emails
- Customer account area for order management
- Basic discount and coupon application
- Integration with Rise CRM notifications and email templates

## Implementation Strategy

### Leverage Existing Functionality
Instead of building from scratch, the implementation should:
1. **Extend Rise CRM** for printing-specific workflows
2. **Enhance Store Module** within Rise CRM for advanced e-commerce features
3. **Build internal module integrations** rather than external API bridges
4. **Add printing-specific modules** to the unified platform

### Custom Development Areas
Focus custom development on:
- Design file management and annotation system
- TIFF to JPG conversion with watermarking
- Queue-based task assignment system
- WhatsApp proxy integration
- Printing-specific product pricing models
- Credit management for organizations

## Development Rules

### Code Organization
- Follow existing Rise CRM patterns and coding standards
- Use existing model structures where possible
- Extend rather than replace core functionality
- Maintain compatibility with existing features

### Database Strategy
- Extend existing tables where possible
- Create new tables only for printing-specific data
- Maintain referential integrity across modules
- Use existing migration patterns

### API Development
- Utilize internal Rise CRM Store module endpoints for store operations
- Leverage existing authentication and permission systems
- Implement robust error handling and logging within the CRM
- Ensure data integrity and consistency across modules

### File Management
- Leverage existing file handling in Rise CRM
- Extend for printing-specific file types
- Implement secure file access controls
- Use progressive upload for large files

## Specific Customizations Required

### Rise CRM Extensions
1. **Task Management**: Extend for queue-based claiming system
2. **File Management**: Add TIFF/JPG conversion and annotation
3. **User Roles**: Add printing-specific roles (Staff Helper permissions)
4. **Order Processing**: Customize for printing workflow
5. **Communication**: Add WhatsApp proxy integration

### Rise CRM Store Module Enhancements
1. **Product Variants System**: Implement variants for size, material, and color
2. **Dynamic Pricing Engine**: Area-based pricing calculations and custom quotes
3. **Advanced Inventory Tracking**: Stock alerts and multi-location support
4. **AR/VR Preview Plugin**: AR/VR product visualization plugin development

### New Modules Required
1. **Design Annotation System**: Web-based annotation tool
2. **File Conversion Service**: TIFF to JPG with watermarking
3. **WhatsApp Integration**: Proxy server communication
4. **Credit Management**: Organization credit tracking
5. **API Module Integration**: Internal store operations and reporting

## Quality Standards

### Security
- Use existing authentication systems
- Implement proper access controls
- Secure file handling and storage
- Audit trail for all operations

### Performance
- Optimize for cPanel hosting constraints
- Efficient database queries
- Proper caching strategies
- File optimization for large uploads

### Maintainability
- Follow existing code patterns
- Comprehensive documentation
- Proper error handling
- Automated testing where possible

## Rise CRM Store Module Capabilities & Roadmap

### Current Capabilities
- Store controller for product browsing and cart management
- Items_model for basic product catalog
- Orders_model for order creation and status tracking
- Cart functionality with add/update/remove operations
- Client portal integration for order and invoice access

### Enhancement Roadmap
- **Product Variants System**: Support for size, material, and type variants
- **Dynamic Pricing Engine**: Area-based pricing and custom quote workflows
- **Advanced Inventory Management**: Stock alerts and multi-location tracking
- **File Management Enhancements**: Annotation integration and file conversions
- **AR/VR Product Preview**: Develop Rise CRM plugin for immersive visualization
- **Theme & UI Customization**: Enhance storefront look and feel