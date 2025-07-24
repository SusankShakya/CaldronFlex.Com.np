# PHP Coding Standards

## General Rules
1. **PHP Version**: Target PHP 7.4+ compatibility
2. **File Format**: UTF-8 without BOM
3. **Line Endings**: LF (Unix-style)
4. **Indentation**: 4 spaces (no tabs)

## Naming Conventions

### Classes
```php
// PascalCase for classes
class UserPreferences { }
class OrderItemProcessor { }
```

### Methods and Functions
```php
// camelCase for methods
public function getUserPreferences() { }
private function validateInput() { }

// snake_case for procedural functions (legacy compatibility)
function get_user_data() { }
```

### Variables
```php
// Explicit, descriptive names (USER_RULES.md #18)
$userEmail = 'user@example.com';  // Good
$e = 'user@example.com';          // Bad

$isUserActive = true;             // Good  
$flag = true;                     // Bad

$totalOrderAmount = 150.50;       // Good
$amt = 150.50;                    // Bad
```

### Constants
```php
// UPPER_SNAKE_CASE for constants
define('MAX_LOGIN_ATTEMPTS', 5);
const DEFAULT_TIMEZONE = 'UTC';
```

## Code Structure

### Class Organization
```php
class ExampleClass 
{
    // 1. Constants
    const STATUS_ACTIVE = 1;
    
    // 2. Properties (public, protected, private)
    public $publicProperty;
    protected $protectedProperty;
    private $privateProperty;
    
    // 3. Constructor
    public function __construct() { }
    
    // 4. Public methods
    public function publicMethod() { }
    
    // 5. Protected methods
    protected function protectedMethod() { }
    
    // 6. Private methods
    private function privateMethod() { }
}
```

### Method Structure
```php
/**
 * Calculate order total with tax and discounts
 * 
 * @param float $subtotal Order subtotal
 * @param float $taxRate Tax rate as decimal
 * @param float $discountAmount Discount amount
 * @return float Total amount
 */
public function calculateOrderTotal($subtotal, $taxRate, $discountAmount) 
{
    // Validate inputs
    if ($subtotal < 0 || $taxRate < 0 || $discountAmount < 0) {
        throw new InvalidArgumentException('Values cannot be negative');
    }
    
    // Calculate tax
    $taxAmount = $subtotal * $taxRate;
    
    // Apply discount
    $afterDiscount = $subtotal - $discountAmount;
    
    // Calculate final total
    $total = $afterDiscount + $taxAmount;
    
    return round($total, 2);
}
```

## Best Practices

### 1. Error Handling (USER_RULES.md #23)
```php
// Always handle errors appropriately
try {
    $result = $this->performDatabaseOperation();
} catch (DatabaseException $e) {
    log_error('Database operation failed: ' . $e->getMessage());
    throw new ServiceException('Unable to complete operation');
}
```

### 2. Security (USER_RULES.md #21)
```php
// Always sanitize user input
$userInput = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

// Use prepared statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $userInput);

// Validate and escape output
echo htmlspecialchars($userData, ENT_QUOTES, 'UTF-8');
```

### 3. Database Queries
```php
// Use query builders or prepared statements
$users = $this->db->table('users')
    ->where('status', 'active')
    ->where('created_at', '>', $date)
    ->orderBy('name')
    ->get();

// Avoid raw queries unless necessary
// If raw query needed, always use parameter binding
```

### 4. Avoid Magic Numbers (USER_RULES.md #26)
```php
// Bad
if ($loginAttempts > 5) { }

// Good
const MAX_LOGIN_ATTEMPTS = 5;
if ($loginAttempts > self::MAX_LOGIN_ATTEMPTS) { }
```

### 5. Comments and Documentation
```php
// Single line comments for clarification
$taxAmount = $subtotal * 0.08; // 8% sales tax

/*
 * Multi-line comments for complex logic
 * This calculation handles special cases where...
 */

/**
 * PHPDoc for all public methods and classes
 * @param string $email User email address
 * @return User|null User object or null if not found
 */
```

## CodeIgniter 4 Specific

### Controllers
```php
namespace App\Controllers;

use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function index()
    {
        // Return view with data
        return view('users/index', [
            'users' => $this->userModel->findAll()
        ]);
    }
}
```

### Models
```php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'status'];
    protected $validationRules = [
        'email' => 'required|valid_email|is_unique[users.email]'
    ];
}
```

## Common Patterns

### Singleton Pattern (when needed)
```php
class ConfigManager 
{
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $this->loadConfiguration();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### Repository Pattern
```php
class UserRepository 
{
    private $model;
    
    public function __construct(UserModel $model) {
        $this->model = $model;
    }
    
    public function findActiveUsers() {
        return $this->model->where('status', 'active')->findAll();
    }
}
```

## Don'ts
1. Don't use `goto`
2. Don't use `eval()` unless absolutely necessary
3. Don't suppress errors with `@`
4. Don't use deprecated functions
5. Don't mix logic and presentation
6. Don't hardcode sensitive data