# Requirements Document

## Introduction

This document outlines the requirements for building a comprehensive All-in-One Printing Business Management System for Caldron Flex. Based on extensive client clarifications, the solution will use Rise CRM as the primary platform (confirmed by client as the "brain") with Bagisto integration for e-commerce functionality, deployed on cPanel hosting with expected capacity of 6 daily orders and 30 concurrent users. The system serves a customer base of 60% organizations, 30% individuals, and 10% guest users, with a staff structure of 1 Super Administrator, 1 Staff Admin, and 1-2 Staff Helpers.

## Requirements

### Requirement 1: Multi-Tier User Management System

**User Story:** As a business owner, I want a comprehensive user management system that supports different types of users (staff, organizations, individuals, guests) with configurable permissions, so that I can control access and maintain security while accommodating various client types.

#### Acceptance Criteria

1. WHEN a Super Administrator (1 person) logs in THEN the system SHALL provide full system access including user role management and system configuration
2. WHEN a Staff Admin (1 person) accesses the system THEN the system SHALL provide complete operational access to projects, clients, inventory, and invoicing
3. WHEN a Staff Helper (1-2 people) logs in THEN the system SHALL provide access to all functionality except purchase price viewing
4. WHEN an Organization Admin creates an account THEN the system SHALL allow them to manage organizational members and set member permissions
5. WHEN an Organization Member accesses the system THEN the system SHALL enforce permissions configured by their Organization Admin
6. WHEN an Individual Customer registers THEN the system SHALL create a personal account with order management capabilities
7. WHEN a Guest User places an order THEN the system SHALL require only name and phone number for minimal registration
8. WHEN managing user roles THEN the system SHALL support dynamic role creation with granular permissions for all user types

### Requirement 2: Queue-Based Order Processing Workflow

**User Story:** As a staff member, I want an automated workflow system with queue-based task assignment that manages orders from submission through completion, so that I can efficiently process custom printing jobs with clear status tracking.

#### Acceptance Criteria

1. WHEN a customer submits an order THEN the system SHALL automatically create a task in the general staff queue with deadline-based priority
2. WHEN any available staff member claims a task THEN the system SHALL assign the task and update status to "In Progress"
3. WHEN urgent orders are identified THEN the system SHALL provide visual "urgent" flag that places tasks at the top of the queue
4. WHEN a design file is uploaded THEN the system SHALL automatically convert TIFF files to JPG previews with watermarks
5. WHEN a client reviews a design THEN the system SHALL provide annotation tools for highlighting areas and adding comments
6. WHEN design corrections are submitted THEN the system SHALL store comments and annotations as overlay without creating new file versions
7. WHEN design revisions exceed guidance limit THEN the system SHALL track but not enforce the maximum 5 correction rounds per project
8. WHEN a client approves a design THEN the system SHALL automatically move the task to production queue
9. WHEN a task is completed THEN the system SHALL automatically generate an invoice and send WhatsApp/email notifications
10. WHEN payment is recorded THEN the system SHALL update payment status and mark order as complete

### Requirement 3: Advanced File Management and Design Proofing

**User Story:** As a designer, I want a robust file management system that handles multiple formats and provides clients with easy-to-use proofing tools, so that I can efficiently manage design files and collect client feedback.

#### Acceptance Criteria

1. WHEN files are uploaded THEN the system SHALL accept JPEG, PDF, SVG, PSD, PNG, and TIFF formats up to 500MB with progressive upload capabilities
2. WHEN TIFF files are uploaded THEN the system SHALL automatically generate watermarked JPG previews for client review
3. WHEN clients review designs THEN the system SHALL provide visual annotation tools for placing pins and adding simple comments
4. WHEN design revisions are requested THEN the system SHALL track but not enforce maximum 5 correction rounds per project as guidance only
5. WHEN storing file versions THEN the system SHALL maintain original files with annotation overlay system instead of storing full file versions
6. WHEN clients upload reference images THEN the system SHALL associate them with the corresponding order
7. WHEN generating previews THEN the system SHALL apply watermarks to protect intellectual property
8. WHEN future integration is needed THEN the system SHALL support Adobe Photoshop API compatibility

### Requirement 4: Dynamic Product Information Management with Specific Pricing

**User Story:** As a business manager, I want a flexible product catalog system with dynamic pricing capabilities and annual price updates, so that I can manage diverse printing products with different pricing models and handle custom quotes efficiently.

#### Acceptance Criteria

1. WHEN managing Flex Banners THEN the system SHALL calculate prices based on Rs.40-150/sqft with variant options (Normal, Sticker, Degradable)
2. WHEN processing Certificate orders THEN the system SHALL apply pricing of Rs.99-299/piece based on quality variants
3. WHEN handling Token of Love orders THEN the system SHALL price items at Rs.250-4000/piece based on frame design variants
4. WHEN managing Photo Frames THEN the system SHALL calculate prices from Rs.349-3000/piece based on design variants
5. WHEN processing Stamp orders THEN the system SHALL apply pricing of Rs.300-500/piece for Pre-ink and Normal types
6. WHEN configuring Metal Medals THEN the system SHALL support both bundle pricing (Gold/Silver/Bronze set) and individual piece pricing with same price for each color
7. WHEN handling custom quote items THEN the system SHALL allow manual price entry for Holding Boards, Shields/Trophies, and ID cards
8. WHEN staff override prices THEN the system SHALL log price changes with user authentication and timestamp
9. WHEN managing seasonal pricing THEN the system SHALL support manual discount codes for specific date ranges on certain products (future version)
10. WHEN updating prices THEN the system SHALL support annual price updates with bulk discount management and promotional pricing

### Requirement 5: Mixed-Level Inventory Management

**User Story:** As an operations manager, I want a mixed-level inventory tracking system that handles both raw materials and finished goods appropriately, so that I can maintain optimal stock levels and prevent production delays.

#### Acceptance Criteria

1. WHEN tracking Flex/Banner inventory THEN the system SHALL monitor finished goods level only due to complex raw material conversion process
2. WHEN managing other products THEN the system SHALL track both raw materials and finished goods levels
3. WHEN stock levels are low THEN the system SHALL generate automated low inventory warnings to staff
4. WHEN inventory changes occur THEN the system SHALL update stock levels in real-time
5. WHEN processing orders THEN the system SHALL check inventory availability before confirming orders
6. WHEN receiving new stock THEN the system SHALL allow manual inventory updates with audit trail
7. WHEN generating reports THEN the system SHALL provide inventory status and movement reports
8. WHEN calculating prices THEN the system SHALL NOT use inventory levels for automatic price calculation
9. WHEN managing inventory initially THEN the system SHALL support manual tracking without barcode system

### Requirement 6: Multi-Channel Communication System

**User Story:** As a customer service representative, I want automated communication capabilities across multiple channels with bilingual support, so that I can keep customers informed about their orders and maintain professional communication.

#### Acceptance Criteria

1. WHEN order status changes THEN the system SHALL send automated WhatsApp notifications via proxy server implementation
2. WHEN invoices are generated THEN the system SHALL automatically send email notifications with invoice attachments
3. WHEN system communications are sent THEN the system SHALL support both English and Nepali languages
4. WHEN WhatsApp service fails THEN the system SHALL fallback to email notifications automatically
5. WHEN customers need support THEN the system SHALL provide ticket system with staff escalation to admin without automatically pausing projects
6. WHEN sending notifications THEN the system SHALL use configurable message templates for standard updates
7. WHEN managing communication preferences THEN the system SHALL prioritize WhatsApp and email as primary communication channels
8. WHEN integrating WhatsApp THEN the system SHALL use proxy server rather than official API to avoid per-message costs

### Requirement 7: Financial Management and Payment Tracking

**User Story:** As an accountant, I want comprehensive financial management capabilities that handle cash and cheque payments with credit arrangements, so that I can track payments, manage credit, and maintain accurate financial records.

#### Acceptance Criteria

1. WHEN generating invoices THEN the system SHALL automatically calculate totals including configurable GST/VAT tax rates
2. WHEN processing payments THEN the system SHALL support cash and cheque payment methods only with no online payment gateways
3. WHEN handling partial payments THEN the system SHALL track amount paid versus remaining balance on invoices
4. WHEN managing organizational credit THEN the system SHALL track credit facilities for organizations and regular customers without credit limits
5. WHEN payment terms are configured THEN the system SHALL support flexible payment arrangements including cheque settlements
6. WHEN financial reports are needed THEN the system SHALL generate daily, weekly, and monthly revenue reports
7. WHEN audit trails are required THEN the system SHALL maintain complete transaction history with timestamps
8. WHEN handling refunds THEN the system SHALL not provide refund mechanisms as design approval is considered binding

### Requirement 8: E-commerce Integration with Bagisto

**User Story:** As a customer, I want to browse and purchase standardized products through an online store that integrates seamlessly with the main system, so that I can easily order common items without custom consultation.

#### Acceptance Criteria

1. WHEN customers visit store.caldronflex.com.np THEN the system SHALL provide full Bagisto e-commerce functionality
2. WHEN orders are placed in the store THEN the system SHALL synchronize data with Rise CRM via API in real-time
3. WHEN inventory changes occur THEN the system SHALL update both platforms simultaneously to maintain single source of truth
4. WHEN customer accounts are created THEN the system SHALL maintain unified customer profiles across platforms
5. WHEN products are managed THEN the system SHALL synchronize product catalogs between store and main system
6. WHEN payments are processed THEN the system SHALL integrate payment status with main order management system
7. WHEN AR/VR features are implemented THEN the system SHALL support 3D product previews using existing Bagisto AR/VR capabilities
8. WHEN integrating platforms THEN the system SHALL use robust custom-built API for two-way synchronization between Bagisto and Rise CRM

### Requirement 9: System Architecture and Performance

**User Story:** As a system administrator, I want a robust system architecture that performs well within cPanel hosting constraints and supports expected user loads, so that the system remains responsive and reliable.

#### Acceptance Criteria

1. WHEN deploying the system THEN it SHALL run efficiently on cPanel hosting without Docker support as a hard constraint
2. WHEN handling concurrent users THEN the system SHALL support 30 concurrent users without performance degradation
3. WHEN processing daily orders THEN the system SHALL handle expected load of 6 orders per day efficiently
4. WHEN managing file storage THEN the system SHALL utilize 1.5TB storage capacity with progressive upload system supporting up to 500MB files
5. WHEN system resources are utilized THEN the system SHALL operate within 3GB RAM allocation with unlimited bandwidth
6. WHEN backup is required THEN the system SHALL perform automated daily backups to bck.caldronflex.com.np subdomain
7. WHEN load distribution is needed THEN the system SHALL utilize multiple subdomains for functional separation
8. WHEN future scaling is needed THEN the system SHALL support migration to VPS hosting as business grows
9. WHEN managing large files THEN the system SHALL consider separate cPanel account for file storage with main application on primary account

### Requirement 10: Security and Compliance

**User Story:** As a security officer, I want comprehensive security measures that protect customer data and business information, so that the system maintains confidentiality and integrity of all stored information.

#### Acceptance Criteria

1. WHEN handling customer files THEN the system SHALL use standard encryption for client design files
2. WHEN managing user access THEN the system SHALL implement role-based permissions with comprehensive audit trails
3. WHEN storing sensitive data THEN the system SHALL encrypt data at rest and in transit
4. WHEN tracking system changes THEN the system SHALL maintain complete audit logs with timestamps for order tracking
5. WHEN user authentication occurs THEN the system SHALL enforce secure login procedures
6. WHEN file access is requested THEN the system SHALL verify user permissions before allowing access
7. WHEN data backup occurs THEN the system SHALL ensure automated daily backup security and integrity
8. WHEN tracking changes THEN the system SHALL log all modifications with timestamps for complete order history
9. WHEN managing payments THEN the system SHALL maintain secure cash/cheque transaction logging

### Requirement 11: Comprehensive Reporting and Analytics

**User Story:** As a business analyst, I want comprehensive reporting capabilities that provide insights into daily operations, staff productivity, and business performance, so that I can make data-driven decisions to grow the business.

#### Acceptance Criteria

1. WHEN generating daily reports THEN the system SHALL provide order status, completion rates, and pending tasks
2. WHEN creating weekly reports THEN the system SHALL show staff productivity and customer satisfaction metrics
3. WHEN producing monthly reports THEN the system SHALL analyze revenue performance and product performance
4. WHEN financial reporting is needed THEN the system SHALL provide configurable tax calculations and payment tracking
5. WHEN analyzing business growth THEN the system SHALL track customer insights for future implementation
6. WHEN measuring success THEN the system SHALL monitor 50% reduction in manual workflow steps
7. WHEN evaluating efficiency THEN the system SHALL track 90% automation in preview generation
8. WHEN assessing communication THEN the system SHALL measure 80% reduction in manual notifications

### Requirement 12: Future Integration and Extensibility

**User Story:** As a system architect, I want the system to support future integrations and extensibility, so that the business can grow and adapt to new technologies and requirements.

#### Acceptance Criteria

1. WHEN AR/VR features are needed THEN the system SHALL implement 3D modeling integration within 1 year using existing Bagisto capabilities
2. WHEN mobile access is required THEN the system SHALL support mobile app development within 3-4 months after initial launch
3. WHEN design software integration is needed THEN the system SHALL provide Adobe Photoshop API compatibility
4. WHEN external integrations are required THEN the system SHALL provide comprehensive REST API access
5. WHEN accounting integration is needed THEN the system SHALL support future accounting software integration
6. WHEN system expansion is required THEN the system SHALL support 3x current volume without additional staff
7. WHEN customer satisfaction is measured THEN the system SHALL achieve 95% approval rate on first design submission
8. WHEN revenue impact is evaluated THEN the system SHALL provide 25% increase in throughput capacity

### Requirement 13: Operational Workflow Management

**User Story:** As an operations manager, I want comprehensive workflow management that handles rush orders, quality control, and seasonal variations, so that I can maintain service quality and meet customer deadlines.

#### Acceptance Criteria

1. WHEN handling rush orders THEN the system SHALL implement deadline-based priority system with visual urgent flags
2. WHEN managing quality control THEN the system SHALL not require formal checkpoints initially but support design approval process
3. WHEN handling complaints THEN the system SHALL provide client ticket system with staff escalation to admin workflow
4. WHEN managing seasonal variations THEN the system SHALL provide system flexibility for volume increases
5. WHEN processing custom quotes THEN the system SHALL allow staff to create configured projects with fixed prices for items like Holding Boards
6. WHEN managing task assignment THEN the system SHALL support queue-based system where staff claim tasks rather than manual assignment
7. WHEN tracking project progress THEN the system SHALL provide complete history from submission to delivery
8. WHEN handling corrections THEN the system SHALL store original file with list of comments and annotations for designer reference