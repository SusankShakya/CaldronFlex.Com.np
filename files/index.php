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
    <title>CaldronFlex - File Storage</title>
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
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .info {
            margin: 20px 0;
            padding: 15px;
            background-color: #e8f5e9;
            border-left: 4px solid #28a745;
        }
        .directory-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .directory-list ul {
            list-style-type: none;
            padding-left: 20px;
        }
        .directory-list li {
            margin: 5px 0;
        }
        .directory-list li:before {
            content: "📁 ";
        }
        code {
            background-color: #f1f1f1;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CaldronFlex File Storage</h1>
        
        <div class="info">
            <h2>Subdomain: files.caldronflex.com.np</h2>
            <p>This is the file storage service for CaldronFlex.</p>
            <p>Document Root: <code>d:/xamp/htdocs/CaldronFlex.Com.np/files</code></p>
        </div>
        
        <div class="directory-list">
            <h3>Existing Directories</h3>
            <ul>
                <?php
                $directories = ['general', 'profile_images', 'project_files', 'system', 'timeline_files'];
                foreach ($directories as $dir) {
                    if (is_dir($dir)) {
                        echo "<li>$dir/</li>";
                    }
                }
                ?>
            </ul>
        </div>
        
        <div class="session-info" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <h3>Session Information</h3>
            <p>Session ID: <code><?php echo session_id(); ?></code></p>
            <p>Session Name: <code><?php echo session_name(); ?></code></p>
            
            <?php if (isUserLoggedIn()): ?>
                <p style="color: green;">✓ User is logged in (ID: <?php echo getCurrentUserId(); ?>)</p>
            <?php else: ?>
                <p style="color: orange;">⚠ No user logged in</p>
            <?php endif; ?>
        </div>
        
        <div class="warning">
            <strong>⚠️ Security Note:</strong> This is a test page. In production, file storage should have proper access controls and authentication.
        </div>
        
        <div style="margin-top: 30px;">
            <h3>Test Links</h3>
            <ul>
                <li><a href="http://caldronflex.com.np">Main Store (caldronflex.com.np)</a></li>
                <li><a href="http://clients.caldronflex.com.np">CRM Platform (clients.caldronflex.com.np)</a></li>
                <li><a href="http://api.caldronflex.com.np">API Services (api.caldronflex.com.np)</a></li>
                <li><a href="http://bck.caldronflex.com.np">Backup Storage (bck.caldronflex.com.np)</a></li>
            </ul>
        </div>
    </div>
</body>
</html>