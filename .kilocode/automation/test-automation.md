# Test Automation Configuration

## PHPUnit Configuration

Create `phpunit.xml` in project root:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         stopOnFailure="false"
         executionOrder="random"
         failOnRisky="true"
         failOnWarning="true"
         cacheDirectory=".phpunit.cache"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutChangesToGlobalState="true">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>

    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">app</directory>
        </include>
        <exclude>
            <directory>app/Views</directory>
            <directory>app/Config</directory>
        </exclude>
        <report>
            <clover outputFile="coverage.xml"/>
            <html outputDirectory="coverage-html"/>
            <text outputFile="coverage.txt" showOnlySummary="true"/>
        </report>
    </coverage>

    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_DRIVER" value="sync"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
    </php>
</phpunit>
```

## Jest Configuration (JavaScript)

Create `jest.config.js`:
```javascript
module.exports = {
    testEnvironment: 'jsdom',
    
    // File patterns
    testMatch: [
        '**/tests/**/*.test.js',
        '**/tests/**/*.spec.js'
    ],
    
    // Coverage configuration
    collectCoverage: true,
    collectCoverageFrom: [
        'assets/js/**/*.js',
        '!assets/js/vendor/**',
        '!**/node_modules/**'
    ],
    coverageThreshold: {
        global: {
            branches: 80,
            functions: 80,
            lines: 80,
            statements: 80
        }
    },
    coverageReporters: ['text', 'lcov', 'html'],
    
    // Setup files
    setupFilesAfterEnv: ['<rootDir>/tests/setup.js'],
    
    // Module paths
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/assets/js/$1',
        '\\.(css|less|scss|sass)$': 'identity-obj-proxy'
    },
    
    // Transform files
    transform: {
        '^.+\\.js$': 'babel-jest'
    },
    
    // Ignore patterns
    testPathIgnorePatterns: [
        '/node_modules/',
        '/vendor/'
    ]
};
```

## Test Runners

### Parallel Test Execution
```json
{
    "scripts": {
        "test": "npm run test:unit && npm run test:integration",
        "test:unit": "jest --testPathPattern=unit",
        "test:integration": "jest --testPathPattern=integration --runInBand",
        "test:watch": "jest --watch",
        "test:coverage": "jest --coverage",
        "test:php": "vendor/bin/phpunit",
        "test:php:unit": "vendor/bin/phpunit --testsuite=Unit",
        "test:php:integration": "vendor/bin/phpunit --testsuite=Integration",
        "test:all": "npm run test && npm run test:php"
    }
}
```

### Continuous Test Running
```bash
# PHP - using phpunit-watcher
composer require spatie/phpunit-watcher --dev

# Create .phpunit-watcher.yml
watch:
  directories:
    - app
    - tests
  fileMask: '*.php'
ignore:
  directories:
    - vendor
  patterns:
    - '*Test.php'

# Run watcher
vendor/bin/phpunit-watcher watch
```

## E2E Test Automation

### Playwright Configuration
Create `playwright.config.js`:
```javascript
module.exports = {
    testDir: './tests/e2e',
    timeout: 30000,
    
    use: {
        baseURL: process.env.BASE_URL || 'http://localhost:8080',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
        trace: 'on-first-retry'
    },
    
    projects: [
        {
            name: 'chromium',
            use: { browserName: 'chromium' }
        },
        {
            name: 'firefox',
            use: { browserName: 'firefox' }
        }
    ],
    
    reporter: [
        ['html', { outputFolder: 'playwright-report' }],
        ['junit', { outputFile: 'test-results/e2e-junit.xml' }]
    ]
};
```

## Test Data Management

### Database Seeding for Tests
```php
// tests/TestSeeder.php
class TestSeeder
{
    public static function seed()
    {
        // Create test users
        DB::table('users')->insert([
            [
                'id' => 1,
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ],
            [
                'id' => 2,
                'email' => 'user@test.com',
                'password' => bcrypt('password'),
                'role' => 'user'
            ]
        ]);
        
        // Create test data
        factory(Product::class, 10)->create();
        factory(Order::class, 5)->create();
    }
    
    public static function cleanup()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        DB::table('products')->truncate();
        DB::table('orders')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
```

## Coverage Requirements

### Enforcement Rules
```php
// tests/CoverageCheck.php
class CoverageCheck
{
    const MINIMUM_COVERAGE = 80;
    const CRITICAL_PATHS = [
        'App\Services\PaymentService' => 100,
        'App\Services\AuthService' => 100,
        'App\Models\User' => 90
    ];
    
    public static function verify($coverageFile)
    {
        $xml = simplexml_load_file($coverageFile);
        
        // Check overall coverage
        $metrics = $xml->project->metrics;
        $coverage = ($metrics['coveredstatements'] / $metrics['statements']) * 100;
        
        if ($coverage < self::MINIMUM_COVERAGE) {
            throw new Exception("Coverage {$coverage}% is below minimum " . self::MINIMUM_COVERAGE . "%");
        }
        
        // Check critical paths
        foreach (self::CRITICAL_PATHS as $class => $required) {
            $classCoverage = self::getClassCoverage($xml, $class);
            if ($classCoverage < $required) {
                throw new Exception("{$class} coverage {$classCoverage}% is below required {$required}%");
            }
        }
    }
}
```

## Test Reporting

### HTML Reports
```bash
# Generate comprehensive test report
npm run test:coverage
vendor/bin/phpunit --coverage-html coverage-report

# Merge coverage reports
npx nyc merge coverage-php coverage-js coverage-combined
npx nyc report --reporter=html --report-dir=coverage-combined-html
```

### Dashboard Integration
```yaml
# Send results to test dashboard
- name: Upload test results
  uses: actions/upload-artifact@v3
  with:
    name: test-results
    path: |
      coverage-report/
      test-results/
      playwright-report/