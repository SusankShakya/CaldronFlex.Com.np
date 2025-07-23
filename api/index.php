<?php
// Include shared session configuration
require_once '../shared-session-config.php';

// Start shared session
startSharedSession();

// Set JSON content type for API responses
header('Content-Type: application/json');

// Allow CORS for API access
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Sample API response
$response = [
    'status' => 'success',
    'message' => 'CaldronFlex API Service is running',
    'subdomain' => 'api.caldronflex.com.np',
    'version' => '1.0.0',
    'endpoints' => [
        '/api/v1/products' => 'Product management',
        '/api/v1/orders' => 'Order management',
        '/api/v1/customers' => 'Customer management',
        '/api/v1/auth' => 'Authentication'
    ],
    'session' => [
        'session_id' => session_id(),
        'session_name' => session_name(),
        'user_logged_in' => isUserLoggedIn(),
        'user_id' => getCurrentUserId()
    ],
    'server' => [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'document_root' => $_SERVER['DOCUMENT_ROOT']
    ]
];

// Output JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
?>