Key Takeaways  
Comprehensive Solution • A complete all-in-one printing business management system integrating customer management, order processing, design workflow, inventory tracking, and e-commerce functionality using a unified Rise CRM platform with built-in Store module.  
Core Architecture • Built on Rise CRM as the single platform, using Rise CRM Store module for e-commerce on store.caldronflex.com.np, deployed on cPanel hosting with multiple subdomains for functional separation and simplified routing.  
Workflow Automation • Streamlined process from order submission through design phase, client review with visual annotation tools, automated approval triggers, production queue management, and invoice generation with WhatsApp/email notifications—all handled internally by Rise CRM modules.  
Technical Integration • Multi-format file handling (TIF/TIFF to JPG conversion), progressive upload capabilities, visual markup tools for client feedback, automated pricing calculations, and AR/VR preview capabilities planned as Rise CRM plugins.  
Business Operations • Support for organizational hierarchies, individual customers, and guest users with role-based permissions, custom pricing for complex items, credit management, and comprehensive audit trails for order tracking, all within Rise CRM.  

________________________________________________________________________________  

EXECUTIVE SUMMARY  
This document presents the finalized requirements for Caldron Flex’s comprehensive printing business management solution. The system will run entirely on Rise CRM—leveraging its core ERP, CRM, and workflow automation features—and use the built-in Rise CRM Store module for all storefront and e-commerce functionality. This unified approach removes the need for external platforms and API synchronization, simplifying deployment and maintenance while meeting all printing business requirements, including large-file handling, dynamic pricing, and future AR/VR enhancements as Rise CRM plugins.  

1. TECHNICAL ARCHITECTURE (FINALIZED)  
1.1 Primary Platform Strategy  
• Core System: Rise CRM (the single “brain”)  
• E-commerce: Rise CRM Store module for store.caldronflex.com.np  
• Database: MySQL with automated daily backups  
• File Storage: Progressive upload system supporting up to 500 MB files  
• Languages: Bilingual support (English/Nepali)  

1.2 Infrastructure Specifications  
• Hosting: cPanel (no Docker support)  
• Storage: 1.5 TB capacity  
• RAM: 3 GB allocated  
• Bandwidth: Unlimited  
• Expected Load: Medium (30 concurrent users, ~6 daily orders)  

1.3 Domain Structure  
• Main Application: app.caldronflex.com.np  
• E-commerce Store: store.caldronflex.com.np (served by Rise CRM)  
• Additional Subdomains: As needed for API, backups, admin interfaces  

2. USER MANAGEMENT & ROLES  
2.1 Staff Structure  
• Super Administrator: Full system access  
• Staff Admin: Complete operational access  
• Staff Helper: Configurable limited access  
• Task Assignment: Queue-based system where staff claim tasks  

2.2 Client Hierarchy  
• Organization Admin: Full organizational control  
• Organization Members: Permissions as defined by Org Admin  
• Individual Customers: Personal account management  
• Guest Users: Minimal registration (name + phone)  

2.3 Permission Management  
• Dynamic Role Creation: System admin can create/modify roles  
• Granular Permissions: Configurable access levels for all user types  
• Organizational Control: Org Admins manage their members’ permissions  

3. CORE WORKFLOW AUTOMATION  
3.1 Order Processing Pipeline  
Order Submission → Task Queue → Staff Claim → Design Phase →  
File Upload (TIF/TIFF) → Auto JPG Conversion → Client Review →  
Annotation/Feedback → Corrections → Final Approval →  
Production Queue → Completion → Invoice Generation → Payment Tracking  

3.2 File Management System  
• Upload Formats: JPEG, PDF, SVG, PSD, PNG, TIFF (max 500 MB)  
• Preview Generation: Automatic TIFF→JPG conversion  
• Client Upload: Reference images supported  
• Version Control: Original file + annotation overlay system  
• Future Integration: Adobe Photoshop API compatibility (Phase 2)  

3.3 Design Review Process  
• Visual Annotation: Comment and highlight tools in Rise CRM  
• Revision Limit: Staff-guided limit up to 5 correction rounds  
• Version Tracking: Source file plus annotation layers  
• Approval System: Digital sign-off with timestamp  

4. PRODUCT INFORMATION MANAGEMENT  
4.1 Pricing Structure  
• Fixed Pricing Products: Automated size/variant calculations  
• Custom Pricing: Manual quote workflow for complex items  
• Bulk Discounts: Configurable promotional pricing codes  
• Staff Override: Price adjustment capabilities  

4.2 Product Catalog  
1. Flex Banner: Rs 40–150/sq ft (size and material variants)  
2. Certificate Printing: Rs 99–299/piece (quality variants)  
3. Token of Love: Rs 250–4,000/piece (design variants)  
4. Photo Frame: Rs 349–3,000/piece (frame design variants)  
5. Stamps: Rs 300–500/piece (pre-inked, normal types)  
6. Metal Medals: Bundles or individual sale, same price per piece  
7. Custom Quote Items: Holding board, shield/trophy, ID cards  

4.3 Inventory Management  
• Mixed Level Tracking: Raw materials and finished goods  
• Flex/Banner Exception: Finished goods level only  
• Stock Alerts: Automated low-inventory warnings  
• No Barcode System: Manual tracking initially  

5. BUSINESS OPERATIONS  
5.1 Customer Management  
• Credit Facilities: Supported for organizations and regular customers  
• Payment Terms: Partial payments tracked  
• No Refund Policy: Post-approval process binding  
• Customer History: Preference and order tracking  

5.2 Communication System  
• WhatsApp Integration: Rise CRM proxy setup  
• Email Notifications: SMTP integration for status updates and invoices  
• Template System: Automated messaging for standard updates  

5.3 Operational Workflow  
• Rush Order Handling: Deadline-based priority flag  
• Complaint System: Ticketing within Rise CRM with escalation  

6. REPORTING & ANALYTICS  
6.1 Standard Reports  
• Daily: Order statuses, pending tasks  
• Weekly: Staff productivity, customer feedback  
• Monthly: Revenue, product performance  
• Financial: Configurable tax and GST reporting  

6.2 Future Analytics  
• Customer Insights: Phase 2 plugin  
• Predictive Analytics: Historical data patterns  

7. INTEGRATION REQUIREMENTS  
7.1 Current Integrations  
• WhatsApp Business API (proxy with fallback)  
• Email System: SMTP  
• File Storage: Cloud or cPanel subdomain  

7.2 Future Integrations  
• Adobe Photoshop API (Phase 2)  
• Mobile App API (Phase 3)  
• AR/VR Preview Plugin for Rise CRM (Phase 2)  

8. SECURITY & COMPLIANCE  
8.1 Data Protection  
• File Encryption: At rest and in transit  
• Backup Strategy: Automated daily backups  
• Access Control: Role-based with audit trails  

8.2 Audit Trail  
• Order Tracking: Full history from submission to delivery  
• Change Logging: Timestamped modifications  
• Payment Records: Cash/cheque transaction logging  

9. IMPLEMENTATION STRATEGY  
9.1 Platform Integration Approach  
1. Unified System: Rise CRM as core database and logic handler  
2. Store Module: Rise CRM Store for e-commerce functionality  
3. Workflow UI: Built entirely within Rise CRM framework  
4. Integration: Internal module integration—no external platform sync  

9.2 Implementation Phases  
Phase 1: Rise CRM Setup & Core Workflow (Months 1–2)  
Phase 2: Store Module Configuration & Product Variants (Months 3–4)  
Phase 3: Dynamic Pricing & Inventory Enhancements (Months 5–6)  
Phase 4: File Management & WhatsApp Proxy Integration (Months 7–8)  
Phase 5: Plugin Development—AR/VR, Mobile App, Analytics (Months 9+)  

10. RISK MITIGATION  
10.1 Technical Risks  
• cPanel Constraints: Simplified stack, fallback strategies  
• Large File Handling: Progressive uploads, cloud storage options  
• Proxy Reliability: Email fallback for WhatsApp notifications  

10.2 Business Risks  
• Staff Training: Progressive rollout and documentation  
• Workflow Disruption: Phased feature releases with rollback plans  

11. SUCCESS METRICS  
11.1 Operational Efficiency  
• Order Processing: 50% reduction in manual steps  
• File Handling: 90% automation in preview generation  
• Communication: 80% reduction in manual notifications  

11.2 Business Growth  
• Capacity: Support 3× current volume without new staff  
• Satisfaction: ≥95% first-proof approval rate  
• Revenue: 25% increase in throughput  

________________________________________________________________________________  

Rise CRM Store Module Capabilities  
• Product Listing & Catalog Management  
• Variant & Attribute Support  
• Shopping Cart & Checkout Flows  
• Order Management & Client Portal  
• Basic Discounts & Promotional Codes  
• Integration Hooks for Custom Pricing Workflows  

Enhancement Roadmap  
Phase 1: Product Variants System—size/type variants, UI enhancements  
Phase 2: Dynamic Pricing Engine—area-based rules, quote workflows  
Phase 3: Advanced Inventory—stock alerts, multi-location support  
Phase 4: File Management—annotation system, TIFF→JPG, version control  
Phase 5: Future Plugins—AR/VR viewer, mobile app API, advanced analytics  

Plugin Development Strategy  
• AR/VR Preview: Develop as Rise CRM plugin leveraging Three.js  
• Mobile App API: REST endpoints for client and staff mobile apps  
• Analytics Dashboard: Plugin generating operational insights from CRM data  

________________________________________________________________________________  