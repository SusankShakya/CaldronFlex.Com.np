# Testing Standards

## Testing Philosophy
- **Test Behavior, Not Implementation**: Focus on what, not how
- **Readable Tests**: Tests are documentation
- **Fast and Reliable**: Tests should run quickly and consistently
- **Isolated**: Tests should not depend on each other

## Test Coverage Requirements
- **New Code**: Minimum 80% coverage
- **Critical Paths**: 100% coverage
- **Bug Fixes**: Must include regression test
- **Refactoring**: Maintain or improve coverage

## Test Types

### 1. Unit Tests
Test individual components in isolation.

#### PHP Example
```php
class UserServiceTest extends TestCase
{
    private $userService;
    private $mockUserRepository;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->mockUserRepository = $this->createMock(UserRepository::class);
        $this->userService = new UserService($this->mockUserRepository);
    }
    
    public function testCreateUserWithValidData()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $expectedUser = new User($userData);
        
        $this->mockUserRepository
            ->expects($this->once())
            ->method('create')
            ->with($userData)
            ->willReturn($expectedUser);
        
        // Act
        $result = $this->userService->createUser($userData);
        
        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->getName());
        $this->assertEquals('john@example.com', $result->getEmail());
    }
    
    public function testCreateUserWithInvalidEmail()
    {
        // Arrange
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email'
        ];
        
        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid email format');
        $this->userService->createUser($userData);
    }
}
```

#### JavaScript Example
```javascript
describe('ShoppingCart', () => {
    let cart;
    
    beforeEach(() => {
        cart = new ShoppingCart();
    });
    
    describe('addItem', () => {
        it('should add item to cart', () => {
            // Arrange
            const item = {
                id: 1,
                name: 'Product',
                price: 10.00,
                quantity: 2
            };
            
            // Act
            cart.addItem(item);
            
            // Assert
            expect(cart.getItems()).toHaveLength(1);
            expect(cart.getTotal()).toBe(20.00);
        });
        
        it('should throw error for invalid quantity', () => {
            // Arrange
            const item = {
                id: 1,
                name: 'Product',
                price: 10.00,
                quantity: -1
            };
            
            // Act & Assert
            expect(() => cart.addItem(item)).toThrow('Quantity must be positive');
        });
    });
});
```

### 2. Integration Tests
Test component interactions.

```php
class UserApiTest extends TestCase
{
    use DatabaseTransactions;
    
    public function testCreateUserEndpoint()
    {
        // Arrange
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'securepassword123'
        ];
        
        // Act
        $response = $this->postJson('/api/users', $userData);
        
        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at'
                ]
            ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com'
        ]);
    }
}
```

### 3. End-to-End Tests
Test complete user workflows.

```javascript
describe('User Registration Flow', () => {
    it('should allow new user to register and login', async () => {
        // Navigate to registration
        await page.goto('/register');
        
        // Fill registration form
        await page.fill('#name', 'Test User');
        await page.fill('#email', 'test@example.com');
        await page.fill('#password', 'SecurePass123!');
        await page.fill('#password_confirmation', 'SecurePass123!');
        
        // Submit form
        await page.click('#register-button');
        
        // Verify redirect to dashboard
        await page.waitForURL('/dashboard');
        expect(await page.textContent('.welcome-message')).toContain('Welcome, Test User');
        
        // Logout
        await page.click('#logout-button');
        
        // Login with created credentials
        await page.goto('/login');
        await page.fill('#email', 'test@example.com');
        await page.fill('#password', 'SecurePass123!');
        await page.click('#login-button');
        
        // Verify successful login
        await page.waitForURL('/dashboard');
        expect(await page.isVisible('.user-menu')).toBe(true);
    });
});
```

## Test Naming Conventions

### Test Method Names
```php
// PHP: test{MethodName}{Scenario}{ExpectedResult}
public function testCalculateTotalWithDiscountReturnsCorrectAmount() { }
public function testCreateUserWithDuplicateEmailThrowsException() { }

// JavaScript: should {expected behavior} when {condition}
it('should return user data when valid ID provided', () => {});
it('should throw error when user not found', () => {});
```

### Test File Organization
```
tests/
├── Unit/
│   ├── Services/
│   │   ├── UserServiceTest.php
│   │   └── OrderServiceTest.php
│   ├── Models/
│   │   └── UserModelTest.php
│   └── Helpers/
│       └── StringHelperTest.php
├── Integration/
│   ├── Api/
│   │   └── UserApiTest.php
│   └── Database/
│       └── UserRepositoryTest.php
└── E2E/
    ├── UserFlowTest.js
    └── CheckoutFlowTest.js
```

## Test Data Management

### Fixtures
```php
// Create reusable test data
class UserFixture
{
    public static function create(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'member',
            'status' => 'active'
        ], $overrides);
    }
}

// Usage
$adminUser = UserFixture::create(['role' => 'admin']);
```

### Database Seeding
```php
class TestSeeder extends Seeder
{
    public function run()
    {
        // Create predictable test data
        User::create([
            'id' => 1,
            'email' => 'admin@test.com',
            'role' => 'admin'
        ]);
        
        // Create random test data
        User::factory()->count(10)->create();
    }
}
```

## Mocking Best Practices

### Mock External Services
```php
// Mock external API calls
$mockHttpClient = $this->createMock(HttpClient::class);
$mockHttpClient->method('post')
    ->willReturn(new Response(200, ['user_id' => 123]));

// Mock file system
$mockFileSystem = $this->createMock(FileSystem::class);
$mockFileSystem->method('exists')
    ->willReturn(true);
```

### Don't Mock What You Don't Own
```php
// Bad: Mocking framework classes
$mockRequest = $this->createMock(Request::class);

// Good: Create wrapper
interface RequestInterface {
    public function input(string $key);
}

class RequestWrapper implements RequestInterface {
    public function input(string $key) {
        return request()->input($key);
    }
}
```

## Continuous Integration Checks
1. **All Tests Pass**: No failures allowed
2. **Coverage Thresholds**: Meet minimum requirements
3. **No Skipped Tests**: Without valid reason
4. **Performance**: Tests complete within time limit
5. **Flaky Test Detection**: Identify and fix unreliable tests

## Test Documentation
Each test should clearly indicate:
- What is being tested
- Why it's important
- What the expected behavior is
- Any special setup required