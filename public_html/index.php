<?php
// Include shared session configuration
require_once '../shared-session-config.php';

// Start shared session
startSharedSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaldronFlex - Main Store</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .info {
            margin: 20px 0;
            padding: 15px;
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
        }
        .session-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        code {
            background-color: #f1f1f1;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CaldronFlex Main Store</h1>
        
        <div class="info">
            <h2>Subdomain: caldronflex.com.np</h2>
            <p>This is the main store frontend for CaldronFlex printing business.</p>
            <p>Document Root: <code>d:/xamp/htdocs/CaldronFlex.Com.np/public_html</code></p>
        </div>
        
        <div class="session-info">
            <h3>Session Information</h3>
            <p>Session ID: <code><?php echo session_id(); ?></code></p>
            <p>Session Name: <code><?php echo session_name(); ?></code></p>
            <p>Session Save Path: <code><?php echo session_save_path(); ?></code></p>
            <p>Cookie Domain: <code><?php echo ini_get('session.cookie_domain'); ?></code></p>
            
            <?php if (isUserLoggedIn()): ?>
                <p style="color: green;">✓ User is logged in (ID: <?php echo getCurrentUserId(); ?>)</p>
            <?php else: ?>
                <p style="color: orange;">⚠ No user logged in</p>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px;">
            <h3>Test Links</h3>
            <ul>
                <li><a href="http://clients.caldronflex.com.np">CRM Platform (clients.caldronflex.com.np)</a></li>
                <li><a href="http://api.caldronflex.com.np">API Services (api.caldronflex.com.np)</a></li>
                <li><a href="http://files.caldronflex.com.np">File Storage (files.caldronflex.com.np)</a></li>
                <li><a href="http://bck.caldronflex.com.np">Backup Storage (bck.caldronflex.com.np)</a></li>
            </ul>
        </div>
        
        <div style="margin-top: 30px;">
            <h3>PHP Information</h3>
            <p>PHP Version: <?php echo phpversion(); ?></p>
            <p>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        </div>
    </div>
</body>
</html>