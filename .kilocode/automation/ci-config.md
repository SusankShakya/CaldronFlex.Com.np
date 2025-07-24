# Continuous Integration Configuration

## GitHub Actions Workflow

### Main CI Pipeline
Create `.github/workflows/ci.yml`:
```yaml
name: CI Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  # Code Quality Checks
  code-quality:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          tools: composer, phpcs, phpstan
      
      - name: Install dependencies
        run: composer install --no-progress --prefer-dist
      
      - name: Run PHP linting
        run: find . -name "*.php" -not -path "./vendor/*" -exec php -l {} \;
      
      - name: Run PHPCS
        run: phpcs
      
      - name: Run PHPStan
        run: phpstan analyse
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '16'
      
      - name: Install npm dependencies
        run: npm ci
      
      - name: Run ESLint
        run: npx eslint . --ext .js,.jsx

  # Security Checks
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Run security checks
        uses: symfonycorp/security-checker-action@v3
      
      - name: OWASP Dependency Check
        uses: dependency-check/Dependency-Check_Action@main
        with:
          project: 'CaldronFlex'
          path: '.'
          format: 'HTML'

  # Testing
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: test_db
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          extensions: mbstring, xml, mysql
          coverage: xdebug
      
      - name: Install dependencies
        run: composer install --no-progress --prefer-dist
      
      - name: Run migrations
        run: php spark migrate
        env:
          DATABASE_URL: mysql://root:root@127.0.0.1:3306/test_db
      
      - name: Run PHPUnit tests
        run: vendor/bin/phpunit --coverage-clover coverage.xml
      
      - name: Check test coverage
        run: |
          coverage=$(grep -oP 'lines-covered="(\d+)"' coverage.xml | grep -oP '\d+' | head -1)
          total=$(grep -oP 'lines-valid="(\d+)"' coverage.xml | grep -oP '\d+' | head -1)
          percent=$((coverage * 100 / total))
          if [ $percent -lt 80 ]; then
            echo "Coverage is $percent%, which is below 80%"
            exit 1
          fi
          echo "Coverage is $percent%"

  # USER_RULES Compliance
  user-rules-check:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Python
        uses: actions/setup-python@v4
        with:
          python-version: '3.9'
      
      - name: Check USER_RULES compliance
        run: |
          python .kilocode/automation/scripts/check-user-rules.py $(find . -name "*.php" -o -name "*.js" | grep -v vendor | grep -v node_modules)
      
      - name: Check implementation plans
        run: |
          if [ -f "implementation-plan.mdc" ]; then
            # Check if plan is updated
            if ! grep -q "\[x\] Done" implementation-plan.mdc; then
              echo "Warning: No completed steps in implementation plan"
            fi
          fi

  # Build and Deploy (example)
  build:
    needs: [code-quality, security, test, user-rules-check]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Build application
        run: |
          composer install --no-dev --optimize-autoloader
          npm run build
      
      - name: Deploy to staging
        run: echo "Deploy to staging server"
        # Add actual deployment steps here
```

### Branch Protection Rules
Configure in GitHub repository settings:
1. Require pull request reviews (minimum 1)
2. Require status checks to pass:
   - code-quality
   - security
   - test
   - user-rules-check
3. Require branches to be up to date
4. Include administrators
5. Restrict who can push to matching branches

## GitLab CI Configuration

### Alternative for GitLab
Create `.gitlab-ci.yml`:
```yaml
stages:
  - quality
  - security
  - test
  - deploy

variables:
  MYSQL_ROOT_PASSWORD: root
  MYSQL_DATABASE: test_db

code_quality:
  stage: quality
  script:
    - composer install
    - vendor/bin/phpcs
    - vendor/bin/phpstan analyse
    - npm ci
    - npx eslint .

security_scan:
  stage: security
  script:
    - composer audit
    - npm audit

unit_tests:
  stage: test
  services:
    - mysql:8.0
  script:
    - composer install
    - php spark migrate
    - vendor/bin/phpunit --coverage-text
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'

deploy_staging:
  stage: deploy
  script:
    - echo "Deploy to staging"
  only:
    - develop
```

## Monitoring & Notifications

### Slack Integration
```yaml
- name: Notify Slack on Failure
  if: failure()
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    text: 'CI Pipeline Failed!'
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

### Email Notifications
Configure in repository settings for:
- Failed builds
- Security vulnerabilities
- Coverage drops below threshold