# JavaScript Coding Standards

## General Rules
1. **ES6+**: Use modern JavaScript features
2. **Strict Mode**: Always use 'use strict'
3. **Semicolons**: Always use semicolons
4. **Indentation**: 4 spaces

## Naming Conventions

### Variables and Functions
```javascript
// camelCase for variables and functions
let userName = 'John';
let isUserLoggedIn = true;

function calculateTotalPrice() { }
const getUserData = () => { };
```

### Constants
```javascript
// UPPER_SNAKE_CASE for constants
const MAX_UPLOAD_SIZE = 5242880; // 5MB
const API_ENDPOINT = '/api/v1';
```

### Classes
```javascript
// PascalCase for classes
class ShoppingCart { }
class UserPreferencesManager { }
```

## Code Structure

### Module Pattern
```javascript
// Use module pattern for organization
const UserModule = (function() {
    'use strict';
    
    // Private variables
    let userList = [];
    
    // Private functions
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // Public API
    return {
        addUser: function(user) {
            if (validateEmail(user.email)) {
                userList.push(user);
                return true;
            }
            return false;
        },
        
        getUsers: function() {
            return [...userList]; // Return copy
        }
    };
})();
```

### jQuery Usage
```javascript
// Cache jQuery selections
const $form = $('#user-form');
const $submitButton = $form.find('.submit-button');

// Use event delegation
$(document).on('click', '.dynamic-button', function(e) {
    e.preventDefault();
    const $button = $(this);
    // Handle click
});

// Chain when possible
$('.status-message')
    .fadeIn(300)
    .delay(3000)
    .fadeOut(300);
```

## Best Practices

### 1. Variable Declarations
```javascript
// Use const by default, let when reassignment needed
const userRole = 'admin';
let loginAttempts = 0;

// Never use var
var oldStyle = 'avoid'; // Bad
```

### 2. Functions
```javascript
// Use arrow functions for callbacks
const numbers = [1, 2, 3];
const doubled = numbers.map(n => n * 2);

// Use regular functions for methods
const userManager = {
    users: [],
    
    addUser: function(user) {
        this.users.push(user);
    }
};

// Default parameters
function createUser(name, role = 'member') {
    return { name, role };
}
```

### 3. Error Handling
```javascript
// Always handle potential errors
try {
    const response = await fetch('/api/users');
    const data = await response.json();
    return data;
} catch (error) {
    console.error('Failed to fetch users:', error);
    showErrorMessage('Unable to load users');
    return [];
}
```

### 4. Async Operations
```javascript
// Use async/await over promises when possible
async function loadUserData(userId) {
    try {
        const user = await fetchUser(userId);
        const preferences = await fetchUserPreferences(userId);
        
        return {
            ...user,
            preferences
        };
    } catch (error) {
        handleError(error);
        return null;
    }
}

// Promise chaining when appropriate
fetchUser(userId)
    .then(user => fetchUserPreferences(user.id))
    .then(preferences => updateUI(preferences))
    .catch(error => handleError(error));
```

### 5. DOM Manipulation
```javascript
// Build HTML safely
function createUserCard(user) {
    const card = document.createElement('div');
    card.className = 'user-card';
    
    // Use textContent for user data (prevents XSS)
    const nameEl = document.createElement('h3');
    nameEl.textContent = user.name;
    
    card.appendChild(nameEl);
    return card;
}

// Batch DOM updates
const fragment = document.createDocumentFragment();
users.forEach(user => {
    fragment.appendChild(createUserCard(user));
});
container.appendChild(fragment);
```

### 6. Event Handling
```javascript
// Remove event listeners when needed
function attachEventListeners() {
    const button = document.getElementById('submit');
    
    function handleClick(e) {
        e.preventDefault();
        // Handle submit
    }
    
    button.addEventListener('click', handleClick);
    
    // Return cleanup function
    return () => {
        button.removeEventListener('click', handleClick);
    };
}
```

## Common Patterns

### Debouncing
```javascript
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Usage
const searchInput = document.getElementById('search');
searchInput.addEventListener('input', debounce(function(e) {
    performSearch(e.target.value);
}, 300));
```

### State Management
```javascript
const AppState = {
    data: {
        user: null,
        cart: []
    },
    
    listeners: [],
    
    setState(updates) {
        this.data = { ...this.data, ...updates };
        this.notify();
    },
    
    subscribe(listener) {
        this.listeners.push(listener);
        return () => {
            this.listeners = this.listeners.filter(l => l !== listener);
        };
    },
    
    notify() {
        this.listeners.forEach(listener => listener(this.data));
    }
};
```

## jQuery Specific

### AJAX Requests
```javascript
// Use jQuery AJAX with proper error handling
$.ajax({
    url: '/api/users',
    method: 'POST',
    data: JSON.stringify(userData),
    contentType: 'application/json',
    headers: {
        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
    }
})
.done(function(response) {
    showSuccessMessage('User created successfully');
    updateUserList(response.user);
})
.fail(function(xhr, status, error) {
    console.error('Request failed:', error);
    showErrorMessage('Failed to create user');
})
.always(function() {
    hideLoadingIndicator();
});
```

## Don'ts
1. Don't pollute global scope
2. Don't use `eval()`
3. Don't modify native prototypes
4. Don't use `==`, use `===`
5. Don't ignore errors
6. Don't use inline JavaScript in HTML