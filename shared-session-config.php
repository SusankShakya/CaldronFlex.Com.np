<?php
/**
 * Shared Session Configuration for CaldronFlex.com.np
 * 
 * This file configures session settings to enable session sharing
 * across all subdomains of caldronflex.com.np
 * 
 * Include this file at the beginning of your PHP scripts
 * or add these settings to your php.ini configuration
 */

// Set session cookie domain to share across subdomains
ini_set('session.cookie_domain', '.caldronflex.com.np');

// Set session save path
ini_set('session.save_path', 'd:/xamp/htdocs/CaldronFlex.Com.np/sessions');

// Set session cookie parameters
ini_set('session.cookie_lifetime', 0); // Session cookie expires when browser closes
ini_set('session.cookie_path', '/'); // Cookie available across entire domain
ini_set('session.cookie_secure', false); // Set to true when using HTTPS
ini_set('session.cookie_httponly', true); // Prevent JavaScript access to session cookie
ini_set('session.cookie_samesite', 'Lax'); // CSRF protection

// Set session name (optional, but recommended for consistency)
session_name('CALDRONFLEX_SESSION');

// Additional security settings
ini_set('session.use_strict_mode', 1); // Reject uninitialized session IDs
ini_set('session.use_cookies', 1); // Use cookies to store session ID
ini_set('session.use_only_cookies', 1); // Only use cookies (no URL parameters)
ini_set('session.use_trans_sid', 0); // Don't pass session ID in URLs

// Session garbage collection settings
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);
ini_set('session.gc_maxlifetime', 3600); // 1 hour

/**
 * Helper function to start a shared session
 */
function startSharedSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Apply all session configurations before starting
        session_start();
    }
}

/**
 * Helper function to check if user is logged in across subdomains
 */
function isUserLoggedIn() {
    startSharedSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Helper function to get current user ID
 */
function getCurrentUserId() {
    startSharedSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Helper function to set user session data
 */
function setUserSession($userId, $userData = []) {
    startSharedSession();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_data'] = $userData;
    $_SESSION['login_time'] = time();
}

/**
 * Helper function to clear user session
 */
function clearUserSession() {
    startSharedSession();
    
    // Clear session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

// Usage instructions comment
/*
 * To use this shared session configuration:
 * 
 * 1. Include this file at the beginning of your PHP scripts:
 *    require_once '/path/to/shared-session-config.php';
 * 
 * 2. Start a shared session:
 *    startSharedSession();
 * 
 * 3. Check if user is logged in:
 *    if (isUserLoggedIn()) {
 *        // User is logged in
 *    }
 * 
 * 4. Set user session after login:
 *    setUserSession($userId, ['name' => 'John Doe', 'email' => 'john@example.com']);
 * 
 * 5. Clear session on logout:
 *    clearUserSession();
 */