# Dynamic Pricing Engine - Complete Implementation

## Overview
The Dynamic Pricing Engine has been fully implemented for the CaldronFlex Rise CRM system. This feature enables flexible, rule-based pricing with support for customer-specific rates, quantity/area-based adjustments, date-based pricing, variant integration, and custom quotes.

## Implementation Summary

### Database Schema
- **pricing_rules**: Stores pricing rules with various criteria
- **pricing_rule_items**: Links rules to specific items
- **pricing_rule_clients**: Links rules to specific clients
- **custom_quotes**: Manages custom quote requests
- **custom_quote_items**: Stores quote item details

### Backend Components

#### Models
1. **Pricing_rules_model.php** - Manages pricing rules CRUD operations
2. **Custom_quotes_model.php** - Handles custom quote management
3. **Variant_combinations_model.php** - Existing model integrated for variant pricing

#### Libraries & Services
1. **Pricing_engine.php** - Core pricing calculation logic with:
   - Rule-based price calculation
   - Variant modifier support
   - Customer-specific pricing
   - Caching for performance
   - Price range generation

2. **Price_calculator_service.php** - Service layer orchestrating:
   - Price calculations
   - Rule application
   - Custom quote creation
   - Variant integration

#### Controllers
1. **Pricing_rules.php** - Admin interface for managing pricing rules
2. **Custom_quotes.php** - Custom quote management interface
3. **Items.php** - Enhanced with pricing endpoints:
   - `calculate_price()` - Real-time price calculation
   - `get_price_breakdown()` - Detailed price breakdown
   - `get_applicable_rules()` - Show applied rules
   - `create_custom_quote()` - Generate custom quotes
   - `get_item_variants()` - Fetch item variants for pricing
   - `get_variant_price()` - Calculate variant-specific pricing

### Frontend Components

#### Views
1. **Pricing Rules Management**
   - `pricing_rules/index.php` - List view with filters
   - `pricing_rules/modal_form.php` - Create/edit form

2. **Custom Quotes**
   - `custom_quotes/index.php` - Quote list with status filters
   - `custom_quotes/modal_form.php` - Quote creation form
   - `custom_quotes/view.php` - Detailed quote view

3. **Price Calculator**
   - `items/price_calculator.php` - Interactive price calculator component

#### JavaScript
1. **price_calculator.js** - Real-time price calculation with:
   - AJAX-based calculations
   - Variant selection handling
   - Price range display
   - Staff-only exact pricing
   - Integration with area calculator

2. **area_calculator.js** - Visual area input tool with:
   - Canvas-based drawing
   - Shape tools (rectangle, polygon, circle)
   - Real-time area calculation
   - Unit conversion support
   - Undo/redo functionality

## Key Features

### 1. Dynamic Pricing Rules
- **Rule Types**: Base rate, percentage discount, fixed discount
- **Criteria**: Client-specific, quantity-based, area-based, date-based
- **Priority System**: Rules applied in priority order
- **Status Control**: Active/inactive rules

### 2. Price Display Strategy
- **Customers**: See price range (±15% by default)
- **Staff**: See exact calculated price and can adjust final price
- **Transparency**: "Final price determined at invoice" messaging

### 3. Variant Integration
- Supports individual variant price modifiers
- Handles variant combinations with specific pricing
- Stock status integration
- Real-time variant price updates

### 4. Custom Quotes
- Request custom pricing for complex requirements
- Approval workflow with status tracking
- Quote validity periods
- Conversion to orders/invoices

### 5. Visual Area Calculator
- Interactive drawing tools
- Multiple shape support
- Measurement accuracy
- Integration with price calculator

## Security Measures
- CSRF protection on all forms
- Input validation and sanitization
- Role-based access control
- XSS prevention in outputs
- SQL injection prevention with parameterized queries

## Performance Optimizations
- Rule caching system
- Efficient query optimization
- Lazy loading of variants
- Minimal AJAX requests
- Client-side calculation where possible

## Integration Points
1. **Items Module**: Seamless integration with existing item management
2. **Variants System**: Full support for variant-based pricing
3. **Clients Module**: Customer-specific pricing rules
4. **Orders/Invoices**: Price application at checkout
5. **Reports**: Pricing analytics and insights

## Usage Examples

### Creating a Pricing Rule
```php
// Volume discount for specific client
$rule = array(
    'name' => '20% off for orders over 100 units',
    'rule_type' => 'percentage_discount',
    'discount_value' => 20,
    'min_quantity' => 100,
    'client_id' => 5,
    'priority' => 1,
    'status' => 1
);
```

### Calculating Price
```javascript
// Real-time price calculation
PriceCalculator.calculate({
    item_id: 10,
    client_id: 5,
    quantity: 150,
    area: 250.5,
    variants: {1: "1_red", 2: "2_large"}
});
```

### Visual Area Input
```javascript
// Initialize area calculator
var areaCalc = new AreaCalculator('container-id');
areaCalc.setUnit('sqft');
areaCalc.on('areaChanged', function(area) {
    $('#area-input').val(area);
});
```

## Testing Checklist
- [x] Pricing rule CRUD operations
- [x] Price calculation accuracy
- [x] Variant price integration
- [x] Custom quote workflow
- [x] Area calculator functionality
- [x] Permission checks
- [x] Input validation
- [x] Cross-browser compatibility

## Future Enhancements
1. Bulk pricing import/export
2. Advanced rule conditions (combinations)
3. Promotional pricing campaigns
4. Price history tracking
5. A/B testing for pricing strategies
6. API endpoints for external integrations

## Troubleshooting

### Common Issues
1. **Price not calculating**: Check if item has base price set
2. **Rules not applying**: Verify rule status and priority
3. **Variants not showing**: Ensure variants are active
4. **Area calculator not loading**: Check browser console for JS errors

### Debug Mode
Enable debug logging in Pricing_engine.php:
```php
private $debug = true; // Set to true for detailed logs
```

## File Locations
- Backend: `clients/app/`
  - Models: `Models/`
  - Controllers: `Controllers/`
  - Libraries: `Libraries/`
  - Services: `Services/`
  - Views: `Views/`
- Frontend: `clients/assets/js/`
- Database: `clients/app/Database/Migrations/`

## Dependencies
- CodeIgniter 4 framework
- jQuery 3.x
- Bootstrap 4.x
- Select2
- Canvas API (for area calculator)

## Compliance
- Follows Rise CRM coding standards
- Implements security best practices
- Maintains backward compatibility
- Supports multi-language via language files
- Responsive design for all screen sizes

---

**Implementation Date**: February 2025
**Version**: 1.0.0
**Status**: Complete and Production-Ready