# Detailed PRD Analysis for Caldron Flex System

## Key Architectural Decisions from PRD

### Platform Strategy Evolution
- **Initial Consideration**: Ever Gauzy as core ERP "brain"
- **Final Decision**: Rise CRM as the unified platform for all business operations
- **E-commerce**: Rise CRM Store module for store.caldronflex.com.np
- **Architecture**: Unified Rise CRM platform with built-in Store module, eliminating the need for separate platforms

### Rise CRM Store Module Assessment and Roadmap
- **Current Capabilities**: Leverage existing Store controller, Items model, Orders model for product listing, cart management, and order processing.
- **Identified Gaps**: Product variants, dynamic pricing engine, advanced inventory tracking, AR/VR preview system, bulk discount management.
- **Enhancement Roadmap**: Phase 1 – Product Variants; Phase 2 – Dynamic Pricing; Phase 3 – Advanced Inventory; Phase 4 – File Management; Phase 5 – Future Plugins.
- **Single-Platform Benefits**: Simplified architecture, unified data, streamlined deployment, reduced maintenance overhead.
- **Migration Strategy**: Update documentation to retire dual-platform references, configure Rise CRM Store module enhancements to meet all e-commerce requirements.

## Detailed User Management Requirements

### Staff Structure (Specific Numbers)
- **Super Administrator**: 1 person, full system access
- **Staff Admin**: 1 person, complete operational access
- **Staff Helper**: 1-2 people, configurable limited access
- **Key Permission**: Staff Helper has access to all functionality EXCEPT purchase price

### Client Distribution (Specific Percentages)
- **Organizations**: 60% of customer base
- **Individual Customers**: 30% of customer base  
- **Guest Users**: 10% of customer base

## Specific Product Catalog with Exact Pricing

### Fixed Price Products
1. **Flex Banner**: Rs.40-150/sqft
   - Variants: Normal, Sticker, Degradable
   - Inventory: Finished goods level only (complex conversion process)

2. **Certificate Printing**: Rs.99-299/piece
   - Based on quality variants

3. **Token of Love**: Rs.250-4000/piece
   - Based on frame design variants

4. **Photo Frame**: Rs.349-3000/piece
   - Based on design variants

5. **Stamps**: Rs.300-500/piece
   - Types: Pre-ink, Normal

6. **Metal Medals**: Configurable pricing
   - Bundle: Gold/Silver/Bronze set
   - Individual: Same price for each color (Gold, Silver, Bronze)

### Custom Quote Products
- Holding Board
- Shield/Trophy
- ID cards
- Process: Manual quote generation with staff approval

## Workflow Specifications

### File Management Details
- **Supported Formats**: JPEG, PDF, SVG, PSD, PNG, TIFF
- **Maximum Size**: 500MB per file
- **Conversion**: Automatic TIFF to JPG with watermark
- **Version Control**: Original file + annotation overlay (NOT full file versions)
- **Revision Limit**: Maximum 5 correction rounds (guidance only, not enforced)

### Task Assignment System
- **Queue Type**: General queue where any staff can claim tasks
- **Priority**: Deadline-based with visual "urgent" flag capability
- **No Manual Assignment**: Staff self-assign from queue

### Communication Requirements
- **Primary**: WhatsApp via proxy server (client prefers over official API due to cost)
- **Secondary**: Email notifications
- **Fallback**: Email when WhatsApp fails
- **Languages**: English and Nepali support
- **Templates**: Automated messaging for standard updates

## Financial Management Specifics

### Payment Methods
- **Accepted**: Cash and Cheque ONLY
- **No Online Payments**: Explicitly out of scope
- **Partial Payments**: Supported with tracking of amount paid vs. remaining
- **Credit Management**: For organizations and regular customers
- **No Credit Limits**: Client confirmed not needed

### Tax and Pricing
- **Tax**: Configurable GST/VAT on invoices
- **Price Override**: Staff can manually adjust prices with audit trail
- **Seasonal Pricing**: Manual discount codes for date ranges (postponed to next version)
- **No Bulk Discounts**: Not needed currently

## Inventory Management Model

### Mixed-Level Tracking
- **Flex/Banner**: Finished goods only (due to complex raw material conversion)
- **Other Products**: Both raw materials and finished goods
- **No Barcode System**: Manual tracking initially
- **Alerts**: Low stock warnings
- **No Pricing Integration**: Inventory levels don't affect pricing calculations

## Integration and Future Features

### Current Integrations
- **WhatsApp**: Proxy server (not official API)
- **Email**: SMTP for notifications
- **File Storage**: Progressive upload system

### Future Roadmap (Confirmed Timelines)
- **AR/VR**: Within 1 year (planned as Rise CRM plugin)
- **Mobile App**: 3-4 months after initial launch
- **Adobe Photoshop**: API integration planned
- **Accounting Software**: Future integration confirmed

## Operational Requirements

### Reporting Needs
- **Daily**: Order status, completion rates, pending tasks
- **Weekly**: Staff productivity, customer satisfaction
- **Monthly**: Revenue analysis, product performance
- **Financial**: Configurable tax calculations

### Support System
- **Complaint Handling**: Client ticket system with staff escalation to admin
- **No Automatic Pause**: Tickets don't automatically pause projects
- **Resolution**: Staff → Admin escalation workflow

### Quality Control
- **No Formal Checkpoints**: Initially not required
- **Design Approval**: Binding once client approves (no refunds)
- **Rush Orders**: Deadline-based priority with urgent flagging

## Success Metrics (Specific Targets)
- **Order Processing**: 50% reduction in manual workflow steps
- **File Management**: 90% automation in preview generation
- **Customer Communication**: 80% reduction in manual notifications
- **Order Capacity**: Support 3x current volume without additional staff
- **Customer Satisfaction**: 95% approval rate on first design submission
- **Revenue Impact**: 25% increase in throughput capacity

## Risk Mitigation Strategies

### Technical Risks
- **cPanel Limitations**: Simplified architecture required
- **File Size Management**: Progressive upload with cloud storage integration
- **WhatsApp Reliability**: Email fallback system essential

### Business Risks
- **Staff Training**: Gradual rollout with comprehensive training
- **Customer Adoption**: Maintain alternative communication during transition
- **Workflow Disruption**: Phased implementation with rollback capabilities

## Implementation Phases

### Phase 1: Foundation (Months 1-2)
- Rise CRM setup and customization
- Basic user management
- Core workflow implementation
- File upload and preview system

### Phase 2: Business Logic (Months 3-4)
- Product catalog with dynamic pricing
- Order management and task assignment
- Design workflow with correction system
- Basic reporting dashboard

### Phase 3: Rise CRM Store Module Enhancement (Months 5-6)
- Implement product variants system for flex banners and other products.
- Develop dynamic pricing engine with area-based and custom quote calculations.
- Enhance advanced inventory tracking with stock alerts and multi-location support.
- Update Store controller and views for variant selection and pricing rules.
- Strengthen client portal integration and order processing workflows.

### Phase 4: Optimization (Months 7-8)
- Performance optimization
- Advanced reporting and analytics
- User experience improvements
- System testing and bug fixes

### Phase 5: Future Rise CRM Plugins (Months 9+)
- AR/VR preview system as a Rise CRM plugin
- Mobile app API development for iOS and Android
- Advanced analytics and reporting plugin
- Integration of third-party accounting and Photoshop APIs