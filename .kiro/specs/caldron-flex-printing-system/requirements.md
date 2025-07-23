# Requirements Document

## Introduction

The Caldron Flex All-in-One Printing Business Management System is a comprehensive web application designed to digitize and automate the entire printing business workflow. The system will serve as the central nervous system for Caldron Flex, managing everything from client onboarding and order processing to design workflow, production management, and financial tracking. Built on Rise CRM as the primary platform with Bagisto integration for e-commerce, the system will streamline operations, improve customer experience, and provide a scalable foundation for business growth.

## Requirements

### Requirement 1: User Management and Authentication System

**User Story:** As a system administrator, I want to manage different types of users with role-based permissions, so that I can control access to system functionality based on user roles and responsibilities.

#### Acceptance Criteria

1. WHEN a user attempts to log in THEN the system SHALL authenticate them using existing Rise CRM authentication mechanisms
2. WHEN a super administrator creates user accounts THEN the system SHALL support the following user types: System Administrator, Staff Admin, Staff Helper, Organization Admin, Organization Member, Individual Customer, and Guest User
3. WHEN a Staff Admin configures Staff Helper permissions THEN the system SHALL allow granular permission control for all functionality except purchase price access
4. WHEN an Organization Admin manages their members THEN the system SHALL allow them to create, modify, and delete member accounts and set their permissions
5. WHEN a Guest User places an order THEN the system SHALL require only name and phone number for account creation
6. WHEN user roles are assigned THEN the system SHALL enforce role-based access controls throughout the application

### Requirement 2: Order Management and Workflow Automation

**User Story:** As a staff member, I want to manage the complete order lifecycle from submission to completion, so that I can efficiently process printing jobs and maintain clear visibility of order status.

#### Acceptance Criteria

1. WHEN a client submits an order THEN the system SHALL create a task in the general queue available for any staff member to claim
2. WHEN a staff member claims a task THEN the system SHALL automatically assign the task to them and update the status to "In Progress"
3. WHEN an order requires custom pricing THEN the system SHALL allow staff to manually create a configured project with fixed pricing
4. WHEN a task has an urgent flag THEN the system SHALL visually distinguish it and place it at the top of the queue
5. WHEN a task status changes THEN the system SHALL automatically trigger appropriate notifications to relevant parties
6. WHEN an order reaches completion THEN the system SHALL generate an invoice and mark the order as ready for collection
7. WHEN a task requires design revisions THEN the system SHALL track up to 5 revision rounds as guidance for staff

### Requirement 3: Design File Management and Proofing System

**User Story:** As a client, I want to upload design files and provide feedback on proofs, so that I can ensure my printing job meets my requirements before production.

#### Acceptance Criteria

1. WHEN a client uploads design files THEN the system SHALL accept JPEG, PDF, SVG, PSD, PNG, and TIFF formats up to 500MB per file
2. WHEN a TIFF file is uploaded THEN the system SHALL automatically convert it to JPG format with watermark for client preview
3. WHEN a client reviews a design proof THEN the system SHALL provide a web-based annotation tool for placing pins and comments
4. WHEN design corrections are requested THEN the system SHALL store the original file with annotation overlays rather than creating new file versions
5. WHEN a client approves a final design THEN the system SHALL mark the approval as binding with timestamp
6. WHEN files are stored THEN the system SHALL implement secure access controls and progressive upload capabilities
7. WHEN reference images are provided THEN the system SHALL allow clients to upload additional supporting materials

### Requirement 4: Product Catalog and Pricing Management

**User Story:** As a staff member, I want to manage product pricing and catalog information, so that I can provide accurate quotes and maintain consistent pricing across all orders.

#### Acceptance Criteria

1. WHEN products are configured THEN the system SHALL support the following categories: Flex Banner (Rs.40-150/sqft), Certificate Printing (Rs.99-299/piece), Token of Love (Rs.250-4000/piece), Photo Frame (Rs.349-3000/piece), Stamps (Rs.300-500/piece), and Metal Medals (configurable pricing)
2. WHEN Metal Medals are sold THEN the system SHALL support both bundle pricing (Gold/Silver/Bronze set) and individual pricing with same price for each color
3. WHEN custom products require quotes THEN the system SHALL allow manual quote generation for items like Holding Board, Shield/Trophy, and ID cards
4. WHEN pricing is calculated THEN the system SHALL support both automatic calculation based on size/variant and manual pricing override
5. WHEN staff override prices THEN the system SHALL maintain an audit trail of price changes
6. WHEN products have variants THEN the system SHALL support attribute-based pricing for different sizes, materials, and quality levels

### Requirement 5: Client Management and Communication System

**User Story:** As a staff member, I want to manage client relationships and communications, so that I can maintain clear communication throughout the order process and build long-term customer relationships.

#### Acceptance Criteria

1. WHEN client accounts are created THEN the system SHALL support three types: Organizations (with hierarchical members), Individual customers, and Guest users
2. WHEN organizations are managed THEN the system SHALL allow Organization Admins to control member permissions including "Can Submit Tasks" and "Can Approve Designs"
3. WHEN credit management is required THEN the system SHALL support credit facilities for organizations and regular customers without credit limits
4. WHEN payments are processed THEN the system SHALL track partial payments showing amount paid versus remaining balance
5. WHEN notifications are sent THEN the system SHALL use WhatsApp proxy server as primary method with email as fallback
6. WHEN communication templates are used THEN the system SHALL provide automated messaging for standard status updates
7. WHEN customer history is accessed THEN the system SHALL track preferences and order history for all client types

### Requirement 6: Inventory Management System

**User Story:** As a staff member, I want to track inventory levels for both raw materials and finished goods, so that I can ensure adequate stock levels and prevent production delays.

#### Acceptance Criteria

1. WHEN inventory is tracked THEN the system SHALL use a mixed model: finished goods level only for flex banners, and both raw materials and finished goods for other products
2. WHEN stock levels are low THEN the system SHALL generate automated warnings to staff
3. WHEN inventory is managed THEN the system SHALL not use inventory levels for automatic price calculations
4. WHEN flex banner inventory is tracked THEN the system SHALL track at finished goods level only due to complex raw material conversion process
5. WHEN other products are tracked THEN the system SHALL monitor both raw components and final configured items
6. WHEN barcode systems are considered THEN the system SHALL initially use manual inventory tracking without barcode integration

### Requirement 7: Financial Management and Invoicing

**User Story:** As a staff member, I want to generate invoices and track payments, so that I can maintain accurate financial records and ensure proper payment collection.

#### Acceptance Criteria

1. WHEN invoices are generated THEN the system SHALL include configurable tax calculations (GST/VAT)
2. WHEN payments are received THEN the system SHALL support cash and cheque payments only (no online payment gateways)
3. WHEN partial payments are made THEN the system SHALL track amount paid and display remaining amount due
4. WHEN credit customers make purchases THEN the system SHALL allow completion of projects with payment via cheque at completion
5. WHEN refunds are requested THEN the system SHALL not provide refund mechanisms as design approval is considered binding
6. WHEN financial reports are needed THEN the system SHALL generate daily, weekly, and monthly reports for revenue analysis

### Requirement 8: E-commerce Integration

**User Story:** As a customer, I want to purchase standardized printing products through an online store, so that I can easily order common items without custom design requirements.

#### Acceptance Criteria

1. WHEN the e-commerce store is accessed THEN the system SHALL provide a Bagisto-powered storefront at store.caldronflex.com.np
2. WHEN products are synchronized THEN the system SHALL maintain real-time sync of inventory, customers, and orders between Bagisto and Rise CRM
3. WHEN AR/VR features are used THEN the system SHALL leverage existing Bagisto AR/VR capabilities for product preview
4. WHEN store orders are placed THEN the system SHALL integrate with the main workflow system for processing
5. WHEN customer data is managed THEN the system SHALL maintain a single source of truth across both platforms

### Requirement 9: Reporting and Analytics

**User Story:** As a business owner, I want to access comprehensive reports and analytics, so that I can make informed decisions about business operations and growth.

#### Acceptance Criteria

1. WHEN daily reports are generated THEN the system SHALL include order status, completion rates, and pending tasks
2. WHEN weekly reports are accessed THEN the system SHALL provide staff productivity and customer satisfaction metrics
3. WHEN monthly reports are created THEN the system SHALL include revenue analysis and product performance data
4. WHEN financial reports are needed THEN the system SHALL support configurable tax calculations and payment tracking
5. WHEN audit trails are required THEN the system SHALL maintain complete history from order submission to delivery
6. WHEN performance metrics are tracked THEN the system SHALL monitor system efficiency and business growth indicators

### Requirement 10: System Integration and Technical Infrastructure

**User Story:** As a system administrator, I want to ensure the system operates reliably within our technical constraints, so that the business can depend on the system for daily operations.

#### Acceptance Criteria

1. WHEN the system is deployed THEN it SHALL operate on cPanel hosting without Docker support
2. WHEN system resources are allocated THEN it SHALL function within 3GB RAM, 1.5TB storage, and unlimited bandwidth constraints
3. WHEN concurrent users access the system THEN it SHALL support 30 concurrent users and 6 daily orders efficiently
4. WHEN backups are performed THEN the system SHALL implement automated daily backups to bck.caldronflex.com.np
5. WHEN APIs are accessed THEN the system SHALL provide RESTful APIs for future integrations
6. WHEN multiple languages are used THEN the system SHALL support both English and Nepali throughout the interface
7. WHEN system maintenance is required THEN it SHALL provide clear upgrade paths and maintain data integrity