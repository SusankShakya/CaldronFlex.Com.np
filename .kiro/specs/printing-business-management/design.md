# Design Document

## Overview

The Caldron Flex All-in-One Printing Business Management System is designed as a comprehensive solution that integrates customer management, order processing, design workflow, inventory tracking, and e-commerce functionality. The system uses Rise CRM as the primary platform with Bagisto for e-commerce, deployed on cPanel hosting to serve 30 concurrent users processing 6 daily orders.

The design follows a "core and satellite" architecture approach to avoid the complexity of merging multiple platforms, with Rise CRM serving as the central "brain" and Bagisto providing e-commerce capabilities through API integration.

## Architecture

### System Architecture Overview

```mermaid
graph TB
    subgraph "Client Layer"
        WEB[Web Browsers]
        MOB[Mobile Devices - Future]
    end
    
    subgraph "Application Layer"
        subgraph "Main Application - app.caldronflex.com.np"
            RISE[Rise CRM Core]
            CUSTOM[Custom Printing Modules]
            API[REST API Layer]
        end
        
        subgraph "E-commerce - store.caldronflex.com.np"
            BAGISTO[Bagisto Platform]
            STORE_API[Store API]
        end
        
        subgraph "File Management"
            UPLOAD[Progressive Upload System]
            CONVERT[TIFF to JPG Converter]
            STORAGE[File Storage System]
        end
    end
    
    subgraph "Communication Layer"
        WHATSAPP[WhatsApp Proxy Server]
        EMAIL[SMTP Email System]
        TEMPLATES[Message Templates]
    end
    
    subgraph "Data Layer"
        MYSQL[(MySQL Database)]
        BACKUP[(Daily Backup - bck.caldronflex.com.np)]
    end
    
    WEB --> RISE
    WEB --> BAGISTO
    MOB --> API
    
    RISE <--> API
    BAGISTO <--> STORE_API
    API <--> STORE_API
    
    RISE --> UPLOAD
    UPLOAD --> CONVERT
    CONVERT --> STORAGE
    
    RISE --> WHATSAPP
    RISE --> EMAIL
    WHATSAPP --> TEMPLATES
    EMAIL --> TEMPLATES
    
    RISE --> MYSQL
    BAGISTO --> MYSQL
    MYSQL --> BACKUP
```

### Platform Integration Strategy

**Primary Platform: Rise CRM**
- Serves as the central database and logic handler
- Manages core CRM, project management, and business operations
- Handles user authentication and role-based permissions
- Processes order workflow and task management

**E-commerce Platform: Bagisto**
- Powers the public-facing storefront at store.caldronflex.com.np
- Handles product catalog for standardized items
- Manages customer browsing and ordering experience
- Provides AR/VR capabilities for 3D product previews

**API Integration Layer**
- Custom-built REST API for two-way synchronization
- Real-time data sync between Rise CRM and Bagisto
- Maintains single source of truth for customers, inventory, and orders
- Supports future mobile app and external integrations

### Deployment Architecture

**cPanel Hosting Constraints**
- No Docker support - requires traditional PHP/MySQL stack
- 3GB RAM allocation with 1.5TB storage capacity
- Simplified architecture optimized for cPanel environment
- Progressive file upload system for large design files

**Subdomain Structure**
- `app.caldronflex.com.np`: Main application (Rise CRM)
- `store.caldronflex.com.np`: E-commerce storefront (Bagisto)
- `bck.caldronflex.com.np`: Automated backup storage
- Additional subdomains for load distribution as needed

## Components and Interfaces

### User Management Component

**Role-Based Access Control**
```mermaid
graph TD
    SUPER[Super Administrator] --> STAFF_ADMIN[Staff Admin]
    STAFF_ADMIN --> STAFF_HELPER[Staff Helper 1-2]
    
    ORG_ADMIN[Organization Admin] --> ORG_MEMBER[Organization Members]
    INDIVIDUAL[Individual Customer]
    GUEST[Guest User]
    
    SUPER --> |Full System Access| ALL_MODULES[All System Modules]
    STAFF_ADMIN --> |Operational Access| BUSINESS_MODULES[Business Modules]
    STAFF_HELPER --> |Limited Access| TASK_MODULES[Task & Design Modules]
    
    ORG_ADMIN --> |Manage Members| ORG_PERMISSIONS[Organization Permissions]
    ORG_MEMBER --> |Configurable Access| MEMBER_TASKS[Member Tasks]
    INDIVIDUAL --> |Personal Account| PERSONAL_ORDERS[Personal Orders]
    GUEST --> |Minimal Registration| GUEST_ORDERS[Guest Orders]
```

**User Interface Components**
- Dynamic role creation interface for administrators
- Granular permission configuration for staff and organization members
- Minimal registration flow for guest users (name + phone only)
- Unified customer profile management across platforms

### Order Processing Workflow Component

**Task Queue Management System**
```mermaid
stateDiagram-v2
    [*] --> OrderSubmission
    OrderSubmission --> TaskQueue: Auto-create task
    TaskQueue --> StaffClaim: Any staff can claim
    StaffClaim --> DesignPhase: Status: In Progress
    DesignPhase --> FileUpload: Upload TIFF/design files
    FileUpload --> AutoConvert: TIFF to JPG with watermark
    AutoConvert --> ClientReview: Notify client
    ClientReview --> AnnotationFeedback: Client adds comments/highlights
    AnnotationFeedback --> DesignCorrection: Designer works from original + annotations
    DesignCorrection --> ClientReview: Resubmit for review
    ClientReview --> FinalApproval: Client approves design
    FinalApproval --> ProductionQueue: Auto-move to production
    ProductionQueue --> TaskCompletion: Staff marks complete
    TaskCompletion --> InvoiceGeneration: Auto-generate invoice
    InvoiceGeneration --> PaymentTracking: Send WhatsApp/Email
    PaymentTracking --> OrderComplete: Payment recorded
    OrderComplete --> [*]
    
    note right of AnnotationFeedback
        Max 5 revisions (guidance only)
        Store as overlay, not new files
    end note
```

**Priority Management**
- Deadline-based automatic prioritization
- Visual "urgent" flag for rush orders
- Queue-based task claiming system (no manual assignment)
- Real-time status updates and notifications

### File Management and Design Proofing Component

**File Processing Pipeline**
```mermaid
flowchart LR
    UPLOAD[File Upload<br/>JPEG, PDF, SVG, PSD, PNG, TIFF<br/>Max 500MB] --> VALIDATE[File Validation]
    VALIDATE --> STORE[Store Original File]
    STORE --> CONVERT{TIFF File?}
    CONVERT -->|Yes| JPG_GEN[Generate JPG Preview<br/>with Watermark]
    CONVERT -->|No| PREVIEW[Generate Preview]
    JPG_GEN --> NOTIFY[Notify Client]
    PREVIEW --> NOTIFY
    NOTIFY --> ANNOTATION[Client Annotation Tool]
    ANNOTATION --> OVERLAY[Store as Annotation Overlay]
    OVERLAY --> DESIGNER[Designer Access<br/>Original + Annotations]
```

**Annotation System Design**
- Web-based annotation tool with pin placement
- Simple commenting system for client feedback
- Overlay storage system (no full file versioning)
- Watermarked previews for intellectual property protection

### Product Information Management Component

**Dynamic Pricing Engine**
```mermaid
graph TD
    PRODUCT[Product Selection] --> CATEGORY{Product Category}
    
    CATEGORY -->|Flex Banner| FLEX[Rs.40-150/sqft<br/>Variants: Normal, Sticker, Degradable]
    CATEGORY -->|Certificate| CERT[Rs.99-299/piece<br/>Quality variants]
    CATEGORY -->|Token of Love| TOKEN[Rs.250-4000/piece<br/>Frame design variants]
    CATEGORY -->|Photo Frame| FRAME[Rs.349-3000/piece<br/>Design variants]
    CATEGORY -->|Stamps| STAMP[Rs.300-500/piece<br/>Pre-ink, Normal types]
    CATEGORY -->|Metal Medals| MEDAL[Bundle or Individual<br/>Same price for Gold/Silver/Bronze]
    CATEGORY -->|Custom Items| CUSTOM[Manual Quote<br/>Holding Board, Shield/Trophy, ID cards]
    
    FLEX --> CALCULATE[Auto Calculate<br/>Size × Rate × Variant]
    CERT --> CALCULATE
    TOKEN --> CALCULATE
    FRAME --> CALCULATE
    STAMP --> CALCULATE
    MEDAL --> BUNDLE_CONFIG[Bundle/Individual Config]
    CUSTOM --> MANUAL_QUOTE[Staff Manual Quote]
    
    CALCULATE --> OVERRIDE{Staff Override?}
    BUNDLE_CONFIG --> OVERRIDE
    MANUAL_QUOTE --> OVERRIDE
    
    OVERRIDE -->|Yes| LOG[Log Price Change<br/>User + Timestamp]
    OVERRIDE -->|No| FINAL_PRICE[Final Price]
    LOG --> FINAL_PRICE
```

**Product Configuration Features**
- Annual price update capability
- Configurable product variants and attributes
- Staff price override with audit logging
- Future seasonal pricing with discount codes

### Inventory Management Component

**Mixed-Level Tracking System**
```mermaid
graph TB
    INVENTORY[Inventory Management] --> CATEGORY{Product Category}
    
    CATEGORY -->|Flex/Banner| FINISHED[Finished Goods Only<br/>Complex conversion process]
    CATEGORY -->|Other Products| MIXED[Raw Materials + Finished Goods]
    
    FINISHED --> TRACK_FG[Track Output Products]
    MIXED --> TRACK_RAW[Track Raw Components]
    MIXED --> TRACK_FINISHED[Track Configured Items]
    
    TRACK_FG --> ALERTS[Low Stock Alerts]
    TRACK_RAW --> ALERTS
    TRACK_FINISHED --> ALERTS
    
    ALERTS --> MANUAL_UPDATE[Manual Inventory Updates<br/>with Audit Trail]
    MANUAL_UPDATE --> REPORTS[Inventory Reports<br/>Status & Movement]
```

**Inventory Features**
- No barcode system initially (manual tracking)
- Automated low stock warnings
- Real-time inventory updates
- No integration with pricing calculations

### Communication System Component

**Multi-Channel Notification System**
```mermaid
graph TD
    TRIGGER[Status Change Trigger] --> NOTIFICATION{Notification Type}
    
    NOTIFICATION -->|Order Status| WHATSAPP[WhatsApp Proxy Server]
    NOTIFICATION -->|Invoice| EMAIL[Email with Attachment]
    NOTIFICATION -->|System Alert| BOTH[WhatsApp + Email]
    
    WHATSAPP --> PROXY[Self-hosted Proxy<br/>Cost-effective solution]
    EMAIL --> SMTP[SMTP Integration]
    
    PROXY --> TEMPLATE[Message Templates<br/>English/Nepali]
    SMTP --> TEMPLATE
    
    TEMPLATE --> FALLBACK{WhatsApp Failed?}
    FALLBACK -->|Yes| EMAIL_BACKUP[Email Fallback]
    FALLBACK -->|No| DELIVERY[Message Delivered]
    
    EMAIL_BACKUP --> DELIVERY
```

**Communication Features**
- Bilingual support (English/Nepali)
- Configurable message templates
- Automatic fallback mechanisms
- Customer preference management

## Data Models

### Core Entity Relationships

```mermaid
erDiagram
    USER ||--o{ ORDER : places
    USER ||--o{ ORGANIZATION_MEMBER : belongs_to
    ORGANIZATION ||--o{ ORGANIZATION_MEMBER : has
    ORDER ||--o{ TASK : contains
    TASK ||--o{ FILE : has
    TASK ||--o{ ANNOTATION : receives
    PRODUCT ||--o{ ORDER_ITEM : ordered
    INVENTORY ||--o{ PRODUCT : tracks
    INVOICE ||--|| ORDER : generated_for
    PAYMENT ||--o{ INVOICE : settles
    
    USER {
        int id PK
        string type "staff|org_admin|org_member|individual|guest"
        string name
        string email
        string phone
        json permissions
        timestamp created_at
    }
    
    ORGANIZATION {
        int id PK
        string name
        string contact_person
        json credit_settings
        timestamp created_at
    }
    
    ORDER {
        int id PK
        int customer_id FK
        string status "submitted|in_progress|review|approved|production|completed"
        decimal total_amount
        boolean is_urgent
        date deadline
        timestamp created_at
    }
    
    TASK {
        int id PK
        int order_id FK
        int assigned_staff_id FK
        string status "new|claimed|design|review|approved|production|completed"
        int revision_count
        timestamp claimed_at
    }
    
    FILE {
        int id PK
        int task_id FK
        string original_filename
        string file_path
        string file_type
        int file_size
        string preview_path
        timestamp uploaded_at
    }
    
    ANNOTATION {
        int id PK
        int task_id FK
        int file_id FK
        json annotation_data
        string comment
        timestamp created_at
    }
    
    PRODUCT {
        int id PK
        string name
        string category
        json pricing_rules
        json variants
        boolean requires_quote
        timestamp updated_at
    }
    
    INVENTORY {
        int id PK
        int product_id FK
        string tracking_level "raw|finished|both"
        int current_stock
        int minimum_threshold
        timestamp last_updated
    }
    
    INVOICE {
        int id PK
        int order_id FK
        decimal subtotal
        decimal tax_amount
        decimal total_amount
        decimal amount_paid
        decimal amount_due
        string status "pending|partial|paid"
        timestamp generated_at
    }
```

### File Management Data Structure

**File Storage Strategy**
- Original files stored securely with access controls
- Automatic preview generation for supported formats
- Annotation data stored as JSON overlay
- Version control through annotation history

**File Processing Workflow**
- Progressive upload for large files (up to 500MB)
- Automatic TIFF to JPG conversion with watermarking
- Secure file access based on user permissions
- Integration with future Adobe Photoshop API

## Error Handling

### System Error Management

**File Processing Errors**
- Upload failures: Retry mechanism with user notification
- Conversion errors: Fallback to manual processing
- Storage issues: Alternative storage location with alerts

**Integration Errors**
- WhatsApp proxy failures: Automatic email fallback
- Bagisto sync errors: Queue-based retry system
- Database connection issues: Connection pooling and retry logic

**User Experience Errors**
- Form validation with clear error messages
- File size/format restrictions with helpful guidance
- Permission denied scenarios with appropriate redirects

### Data Integrity Protection

**Transaction Management**
- Database transactions for critical operations
- Rollback mechanisms for failed processes
- Data validation at multiple layers

**Backup and Recovery**
- Automated daily backups to separate subdomain
- Point-in-time recovery capabilities
- Regular backup integrity verification

## Testing Strategy

### Testing Approach

**Unit Testing**
- Core business logic components
- File processing and conversion functions
- Pricing calculation algorithms
- User permission validation

**Integration Testing**
- Rise CRM and Bagisto API synchronization
- WhatsApp and email notification systems
- File upload and processing pipeline
- Payment and invoice generation workflow

**User Acceptance Testing**
- Staff workflow testing with actual printing scenarios
- Customer order placement and review process
- Multi-language interface testing
- Mobile responsiveness validation

**Performance Testing**
- 30 concurrent user load testing
- Large file upload performance
- Database query optimization
- cPanel hosting constraint validation

### Quality Assurance

**Security Testing**
- Role-based access control validation
- File access permission verification
- Data encryption testing
- SQL injection and XSS prevention

**Usability Testing**
- Staff task management workflow
- Customer design review and annotation process
- Multi-language interface usability
- Mobile device compatibility

**Reliability Testing**
- System uptime and availability
- Error recovery and fallback mechanisms
- Data backup and restoration procedures
- Communication system reliability

## Implementation Considerations

### cPanel Hosting Optimization

**Technical Constraints**
- No Docker support requires traditional LAMP stack
- Memory optimization for 3GB RAM limitation
- Efficient file storage management for 1.5TB capacity
- Database query optimization for performance

**Deployment Strategy**
- Staged deployment with rollback capabilities
- Separate file storage account for large design files
- Load distribution across multiple subdomains
- Future migration path to VPS hosting

### Scalability Planning

**Current Capacity**
- 30 concurrent users with 6 daily orders
- Support for 3x volume growth without additional staff
- Efficient task queue management
- Optimized database design for growth

**Future Expansion**
- Mobile app development within 3-4 months
- AR/VR integration within 1 year
- Adobe Photoshop API integration
- Advanced analytics and reporting capabilities

This design provides a comprehensive foundation for the Caldron Flex printing business management system, addressing all requirements while maintaining flexibility for future enhancements and scalability.