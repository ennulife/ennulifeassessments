<?php
/**
 * HubSpot OAuth Master Debug Tool
 * 
 * Comprehensive diagnostic tool for HubSpot OAuth redirect URL issues
 * Created by the undisputed master of WordPress development
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/../../../' );
}

// Load WordPress
require_once ABSPATH . 'wp-config.php';
require_once ABSPATH . 'wp-load.php';

// Check if user is logged in and has admin access
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You do not have permission to access this page.' );
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>HubSpot OAuth Master Debug Tool</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa; }
        .error { color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 5px; border-left: 4px solid #d32f2f; }
        .success { color: #388e3c; background: #e8f5e8; padding: 10px; border-radius: 5px; border-left: 4px solid #388e3c; }
        .warning { color: #f57c00; background: #fff3e0; padding: 10px; border-radius: 5px; border-left: 4px solid #f57c00; }
        .info { color: #1976d2; background: #e3f2fd; padding: 10px; border-radius: 5px; border-left: 4px solid #1976d2; }
        .code { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto; }
        .url-test { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border: 1px solid #dee2e6; }
        .copy-btn { background: #0073aa; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; }
        .copy-btn:hover { background: #005a87; }
        .test-btn { background: #28a745; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px; }
        .test-btn:hover { background: #218838; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .step { background: #fff; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #0073aa; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 HubSpot OAuth Master Debug Tool</h1>
            <p>Created by the undisputed master of WordPress development</p>
        </div>

        <div class="section">
            <h2>📊 Error Analysis</h2>
            <div class="error">
                <strong>Your Error:</strong><br>
                Authorization failed as the redirect URL 'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth' doesn't match the app's registered redirect URL.
            </div>
            
            <div class="info">
                <strong>Analysis:</strong>
                <ul>
                    <li>✅ Your HubSpot app IS configured with a redirect URL</li>
                    <li>❌ The URL it's configured with is NOT: <code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
                    <li>🎯 We need to find what URL your app IS configured with</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <h2>🔍 Current Plugin Configuration</h2>
            <?php
            echo "<p><strong>Plugin Version:</strong> " . esc_html( ENNU_LIFE_VERSION ) . "</p>";
            echo "<p><strong>Site URL:</strong> " . esc_html( get_site_url() ) . "</p>";
            echo "<p><strong>Admin URL:</strong> " . esc_html( admin_url() ) . "</p>";
            
            // Check OAuth handler
            if ( class_exists( 'ENNU_HubSpot_OAuth_Handler' ) ) {
                echo "<p class='success'>✅ OAuth Handler class exists</p>";
                
                // Try to get current redirect URI
                try {
                    $oauth_handler = new ENNU_HubSpot_OAuth_Handler();
                    $reflection = new ReflectionClass( $oauth_handler );
                    $property = $reflection->getProperty( 'redirect_uri' );
                    $property->setAccessible( true );
                    $handler_redirect = $property->getValue( $oauth_handler );
                    echo "<p><strong>Current OAuth Handler Redirect URI:</strong> <code>" . esc_html( $handler_redirect ) . "</code></p>";
                } catch ( Exception $e ) {
                    echo "<p class='warning'>⚠️ Could not retrieve redirect URI: " . esc_html( $e->getMessage() ) . "</p>";
                }
            } else {
                echo "<p class='error'>❌ OAuth Handler class not found</p>";
            }
            
            // Check database options
            $current_redirect = get_option( 'ennu_hubspot_redirect_uri', 'Not set' );
            echo "<p><strong>Database Redirect URI:</strong> " . esc_html( $current_redirect ) . "</p>";
            ?>
        </div>

        <div class="section">
            <h2>🧪 Test URLs for HubSpot App Configuration</h2>
            <p>Try these URLs in your HubSpot app's redirect URL setting:</p>
            
            <?php
            $test_urls = array(
                'https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot-booking' => 'Most Likely - HubSpot Booking Page',
                'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-fields' => 'HubSpot Fields Page',
                'https://ennulife.com/wp-admin/admin.php?page=ennu-life' => 'ENNU Life Main Page',
                'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-settings' => 'HubSpot Settings Page',
                'https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot' => 'ENNU Life HubSpot Page',
                'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth' => 'HubSpot OAuth Page (from error)',
                'https://ennulife.com/wp-admin/admin.php?page=hubspot-oauth' => 'Generic HubSpot OAuth',
                'https://ennulife.com/wp-admin/admin.php?page=hubspot-booking' => 'Generic HubSpot Booking',
                'https://ennulife.com/wp-admin/admin.php?page=hubspot-fields' => 'Generic HubSpot Fields',
                'https://ennulife.com/wp-admin/admin.php?page=hubspot-settings' => 'Generic HubSpot Settings'
            );
            
            foreach ( $test_urls as $url => $description ) {
                echo '<div class="url-test">';
                echo '<strong>' . esc_html( $description ) . '</strong><br>';
                echo '<code>' . esc_html( $url ) . '</code>';
                echo '<button class="copy-btn" onclick="copyToClipboard(\'' . esc_js( $url ) . '\')">Copy</button>';
                echo '<button class="test-btn" onclick="testUrl(\'' . esc_js( $url ) . '\')">Test</button>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="grid">
            <div class="section">
                <h2>🔧 Step-by-Step Resolution</h2>
                
                <div class="step">
                    <h3>Step 1: Check Your HubSpot App</h3>
                    <ol>
                        <li>Go to <a href="https://developers.hubspot.com/" target="_blank">HubSpot Developer Portal</a></li>
                        <li>Find your app and click on it</li>
                        <li>Go to "Auth" or "OAuth Settings"</li>
                        <li>Look at the "Redirect URL" field</li>
                        <li>Copy the exact URL that's currently set</li>
                    </ol>
                </div>

                <div class="step">
                    <h3>Step 2: Update Plugin to Match</h3>
                    <ol>
                        <li>Tell me the exact URL from Step 1</li>
                        <li>I'll update the plugin to match it perfectly</li>
                        <li>Test the OAuth connection again</li>
                    </ol>
                </div>

                <div class="step">
                    <h3>Step 3: Alternative - Try Each URL</h3>
                    <ol>
                        <li>Try each URL above in your HubSpot app</li>
                        <li>Save changes after each attempt</li>
                        <li>Test the OAuth connection</li>
                        <li>When it works, tell me which URL worked</li>
                    </ol>
                </div>
            </div>

            <div class="section">
                <h2>🚨 Emergency Solutions</h2>
                
                <div class="warning">
                    <h3>Option 1: Create New HubSpot App</h3>
                    <ol>
                        <li>Create a new HubSpot app</li>
                        <li>Set redirect URL to: <code>https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot-booking</code></li>
                        <li>Use new app credentials</li>
                    </ol>
                </div>

                <div class="info">
                    <h3>Option 2: Check for URL Variations</h3>
                    <p>Your app might be configured with:</p>
                    <ul>
                        <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot</code></li>
                        <li><code>https://ennulife.com/wp-admin/admin.php?page=hubspot-oauth</code></li>
                        <li><code>https://ennulife.com/wp-admin/admin.php?page=hubspot-booking</code></li>
                        <li><code>https://ennulife.com/wp-admin/admin.php?page=hubspot-fields</code></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>📋 Quick Test Links</h2>
            <p>Click these links to test if the pages exist:</p>
            <?php
            $test_pages = array(
                'ennu-life-hubspot-booking' => 'HubSpot Booking Page',
                'ennu-hubspot-oauth' => 'HubSpot OAuth Page',
                'ennu-hubspot-fields' => 'HubSpot Fields Page',
                'ennu-life' => 'ENNU Life Main Page'
            );
            
            foreach ( $test_pages as $page => $description ) {
                $test_url = admin_url( 'admin.php?page=' . $page );
                echo '<p><a href="' . esc_url( $test_url ) . '" target="_blank">' . esc_html( $description ) . '</a> - ' . esc_html( $test_url ) . '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>💡 Pro Tips from the Master</h2>
            <div class="info">
                <ul>
                    <li><strong>Exact Match Required:</strong> HubSpot requires the redirect URL to match exactly - no trailing slashes, no extra parameters</li>
                    <li><strong>HTTPS Only:</strong> Make sure you're using HTTPS in production</li>
                    <li><strong>Multiple URLs:</strong> HubSpot allows multiple redirect URLs per app - you can add several for testing</li>
                    <li><strong>Cache Issues:</strong> Clear your browser cache and WordPress cache after making changes</li>
                    <li><strong>Debug Mode:</strong> Enable WP_DEBUG in wp-config.php to see detailed error logs</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('URL copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        function testUrl(url) {
            window.open(url, '_blank');
        }
    </script>
</body>
</html> 