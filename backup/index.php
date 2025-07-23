<?php
// Include shared session configuration
require_once '../shared-session-config.php';

// Start shared session
startSharedSession();

// Basic security check - in production, implement proper authentication
$allowed = false;
if (isUserLoggedIn()) {
    // In production, check if user has admin/backup privileges
    $allowed = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaldronFlex - Backup Storage</title>
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
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .backup-info {
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
        .restricted {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CaldronFlex Backup Storage</h1>
        
        <div class="info">
            <h2>Subdomain: bck.caldronflex.com.np</h2>
            <p>This is the backup storage area for CaldronFlex.</p>
            <p>Document Root: <code>d:/xamp/htdocs/CaldronFlex.Com.np/backup</code></p>
        </div>
        
        <?php if (!$allowed): ?>
            <div class="restricted">
                <h3>🔒 Access Restricted</h3>
                <p>This area is restricted to authorized personnel only.</p>
                <p>Please log in with appropriate credentials to access backup functionality.</p>
            </div>
        <?php else: ?>
            <div class="backup-info">
                <h3>Backup Information</h3>
                <p>✓ You have access to backup functionality</p>
                <p>User ID: <?php echo getCurrentUserId(); ?></p>
                
                <h4>Available Backups:</h4>
                <ul>
                    <?php
                    // List directories in the backup folder
                    $backupDirs = glob('../backup_*', GLOB_ONLYDIR);
                    if (!empty($backupDirs)) {
                        foreach ($backupDirs as $dir) {
                            $dirName = basename($dir);
                            echo "<li><code>$dirName</code></li>";
                        }
                    } else {
                        echo "<li>No backups found</li>";
                    }
                    ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="security-notice">
            <h3>⚠️ Security Notice</h3>
            <ul>
                <li>This subdomain should be restricted to authorized IP addresses only</li>
                <li>Implement strong authentication before production use</li>
                <li>Consider using HTTPS for all backup operations</li>
                <li>Regular backup rotation and encryption are recommended</li>
            </ul>
        </div>
        
        <div class="session-info" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h3>Session Information</h3>
            <p>Session ID: <code><?php echo session_id(); ?></code></p>
            <p>Session Name: <code><?php echo session_name(); ?></code></p>
        </div>
        
        <div style="margin-top: 30px;">
            <h3>Test Links</h3>
            <ul>
                <li><a href="http://caldronflex.com.np">Main Store (caldronflex.com.np)</a></li>
                <li><a href="http://clients.caldronflex.com.np">CRM Platform (clients.caldronflex.com.np)</a></li>
                <li><a href="http://api.caldronflex.com.np">API Services (api.caldronflex.com.np)</a></li>
                <li><a href="http://files.caldronflex.com.np">File Storage (files.caldronflex.com.np)</a></li>
            </ul>
        </div>
    </div>
</body>
</html>