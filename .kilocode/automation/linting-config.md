# Linting Configuration

## PHP Linting

### PHP_CodeSniffer Configuration
Create `.phpcs.xml` in project root:
```xml
<?xml version="1.0"?>
<ruleset name="CaldronFlex">
    <description>CaldronFlex PHP Coding Standards</description>
    
    <!-- Paths to check -->
    <file>app</file>
    <file>public</file>
    <exclude-pattern>*/vendor/*</exclude-pattern>
    <exclude-pattern>*/cache/*</exclude-pattern>
    
    <!-- Rules -->
    <rule ref="PSR12"/>
    
    <!-- Custom rules per USER_RULES.md -->
    <rule ref="Generic.NamingConventions.UpperCaseConstantName"/>
    <rule ref="Generic.NamingConventions.CamelCapsFunctionName"/>
    <rule ref="Squiz.Commenting.FunctionComment"/>
    
    <!-- Enforce explicit variable names (Rule #18) -->
    <rule ref="Squiz.NamingConventions.ValidVariableName">
        <properties>
            <property name="minLength" value="3"/>
        </properties>
    </rule>
</ruleset>
```

### PHPStan Configuration
Create `phpstan.neon` in project root:
```neon
parameters:
    level: 6
    paths:
        - app
        - public
    excludePaths:
        - */vendor/*
        - */cache/*
    
    # Custom rules
    checkMissingIterableValueType: true
    checkGenericClassInNonGenericObjectType: true
    
    # Strict rules per USER_RULES.md
    reportUnmatchedIgnoredErrors: true
    treatPhpDocTypesAsCertain: false
```

## JavaScript Linting

### ESLint Configuration
Create `.eslintrc.js` in project root:
```javascript
module.exports = {
    "env": {
        "browser": true,
        "es2021": true,
        "jquery": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaVersion": 12,
        "sourceType": "module"
    },
    "rules": {
        // Enforce USER_RULES.md standards
        "no-var": "error",
        "prefer-const": "error",
        "no-unused-vars": "error",
        
        // Explicit naming (Rule #18)
        "id-length": ["error", { 
            "min": 2, 
            "exceptions": ["i", "j", "k", "x", "y", "z", "e"] 
        }],
        
        // No magic numbers (Rule #26)
        "no-magic-numbers": ["error", {
            "ignore": [0, 1, -1],
            "ignoreArrayIndexes": true,
            "enforceConst": true
        }],
        
        // Security
        "no-eval": "error",
        "no-implied-eval": "error",
        
        // Best practices
        "strict": ["error", "global"],
        "eqeqeq": ["error", "always"],
        "no-console": ["warn", { 
            "allow": ["warn", "error"] 
        }]
    }
};
```

## CSS/SCSS Linting

### Stylelint Configuration
Create `.stylelintrc.json` in project root:
```json
{
    "extends": "stylelint-config-standard",
    "rules": {
        "indentation": 4,
        "string-quotes": "single",
        "color-hex-case": "lower",
        "color-hex-length": "short",
        "selector-class-pattern": "^[a-z][a-z0-9-_]*$",
        "declaration-block-no-redundant-longhand-properties": true,
        "shorthand-property-no-redundant-values": true
    }
}
```

## SQL Linting

### SQLFluff Configuration
Create `.sqlfluff` in project root:
```ini
[sqlfluff]
dialect = mysql
sql_file_exts = .sql,.sql.j2,.dml,.ddl

[sqlfluff:indentation]
indented_joins = true
indented_using_on = true
tab_space_size = 4

[sqlfluff:layout:type:comma]
spacing_before = touch
line_position = trailing

[sqlfluff:rules]
tab_space_size = 4
max_line_length = 120
indent_unit = space
comma_style = trailing
capitalisation_policy = upper
```

## Integration

### VS Code Settings
Add to `.vscode/settings.json`:
```json
{
    "php.validate.executablePath": "/usr/bin/php",
    "phpcs.enable": true,
    "phpcs.standard": ".phpcs.xml",
    "eslint.enable": true,
    "eslint.autoFixOnSave": true,
    "editor.formatOnSave": true,
    "editor.codeActionsOnSave": {
        "source.fixAll.eslint": true
    }
}
```

### Git Pre-commit Integration
```bash
# Install pre-commit
pip install pre-commit

# Install the git hook scripts
pre-commit install

# Run against all files (initial setup)
pre-commit run --all-files