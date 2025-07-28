<?php
/**
 * HubSpot OAuth Debug Tool
 * 
 * This script helps diagnose and fix OAuth redirect URL issues
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
    <title>HubSpot OAuth Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-url { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #0073aa; }
        .copy-btn { background: #0073aa; color: white; padding: 8px 15px; border: none; border-radius: 3px; cursor: pointer; margin-left: 10px; }
        .copy-btn:hover { background: #005a87; }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background: white; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 3px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 3px; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 3px; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 3px; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
        .solution { background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa; }
        h1 { color: #0073aa; }
        h2 { color: #333; }
        .status-indicator { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; }
        .status-ok { background: #28a745; }
        .status-error { background: #dc3545; }
        .status-warning { background: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 HubSpot OAuth Debug Tool</h1>
        
        <div class="section">
            <h2>📊 Current Environment Analysis</h2>
            <?php
            $host = $_SERVER['HTTP_HOST'] ?? '';
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $current_url = $protocol . '://' . $host . $_SERVER['REQUEST_URI'];
            $is_local = in_array($host, array('localhost', '127.0.0.1', 'local.ennulife.com'));
            
            echo "<p><strong>Current Host:</strong> " . esc_html($host) . "</p>";
            echo "<p><strong>Protocol:</strong> " . esc_html($protocol) . "</p>";
            echo "<p><strong>Current URL:</strong> " . esc_html($current_url) . "</p>";
            echo "<p><strong>Environment:</strong> " . ($is_local ? '<span class="status-indicator status-warning"></span>Local Development' : '<span class="status-indicator status-ok"></span>Production') . "</p>";
            ?>
        </div>

        <div class="section">
            <h2>🎯 OAuth Redirect URL Configuration</h2>
            <?php
            // Get current OAuth handler redirect URI
            if (class_exists('ENNU_HubSpot_OAuth_Handler')) {
                $oauth_handler = new ENNU_HubSpot_OAuth_Handler();
                $reflection = new ReflectionClass($oauth_handler);
                $property = $reflection->getProperty('redirect_uri');
                $property->setAccessible(true);
                $handler_redirect = $property->getValue($oauth_handler);
                
                echo "<p><strong>Current OAuth Redirect URI:</strong> " . esc_html($handler_redirect) . "</p>";
                
                if ($is_local && strpos($handler_redirect, 'localhost') === false) {
                    echo '<div class="error">❌ ERROR: OAuth handler is configured for production but you\'re on localhost!</div>';
                } elseif (!$is_local && strpos($handler_redirect, 'localhost') !== false) {
                    echo '<div class="error">❌ ERROR: OAuth handler is configured for localhost but you\'re on production!</div>';
                } else {
                    echo '<div class="success">✅ OAuth handler redirect URI is correctly configured for this environment.</div>';
                }
            } else {
                echo '<div class="error">❌ ERROR: ENNU_HubSpot_OAuth_Handler class not found!</div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>🔧 HubSpot App Configuration URLs</h2>
            <p>Use these URLs in your HubSpot app's redirect URL configuration:</p>
            
            <?php
            $redirect_urls = array(
                'production' => 'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth',
                'localhost' => 'http://localhost/wp-admin/admin.php?page=ennu-hubspot-oauth',
                'redirectmeto' => 'https://redirectmeto.com/http://localhost/wp-admin/admin.php?page=ennu-hubspot-oauth'
            );
            
            foreach ($redirect_urls as $type => $url) {
                echo '<div class="test-url">';
                echo '<strong>' . ucfirst($type) . ':</strong> ';
                echo '<code>' . esc_html($url) . '</code>';
                echo '<button class="copy-btn" onclick="copyToClipboard(\'' . esc_js($url) . '\')">Copy</button>';
                echo '</div>';
            }
            ?>
        </div>

        <div class="section">
            <h2>🚀 Quick Fix Solutions</h2>
            
            <div class="solution">
                <h3>Option 1: Use RedirectMeTo (Recommended for Local Testing)</h3>
                <ol>
                    <li>Go to <a href="https://redirectmeto.com/" target="_blank">RedirectMeTo</a></li>
                    <li>Enter: <code>http://localhost/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
                    <li>Copy the generated HTTPS URL</li>
                    <li>Add this URL to your HubSpot app's redirect URLs</li>
                    <li>Test OAuth from localhost</li>
                </ol>
            </div>

            <div class="solution">
                <h3>Option 2: Add Localhost URL to HubSpot App</h3>
                <ol>
                    <li>Go to <a href="https://developers.hubspot.com/" target="_blank">HubSpot Developer Portal</a></li>
                    <li>Find your app and click on it</li>
                    <li>Go to "Auth" or "OAuth Settings"</li>
                    <li>Add this URL: <code>http://localhost/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
                    <li>Save the changes</li>
                    <li>Test OAuth from localhost</li>
                </ol>
            </div>

            <div class="solution">
                <h3>Option 3: Use ngrok for HTTPS Localhost</h3>
                <ol>
                    <li>Install ngrok: <code>npm install -g ngrok</code></li>
                    <li>Start tunnel: <code>ngrok http 80</code></li>
                    <li>Use the HTTPS URL provided by ngrok</li>
                    <li>Add that URL to your HubSpot app</li>
                </ol>
            </div>
        </div>

        <div class="section">
            <h2>🧪 Test Your OAuth Connection</h2>
            <p>Click these links to test if the OAuth pages are accessible:</p>
            <?php
            $test_pages = array(
                'ennu-hubspot-oauth' => 'HubSpot OAuth Page',
                'ennu-life-hubspot-booking' => 'HubSpot Booking Page',
                'ennu-hubspot-fields' => 'HubSpot Fields Page',
                'ennu-life' => 'ENNU Life Main Page'
            );
            
            foreach ($test_pages as $page => $description) {
                $test_url = admin_url('admin.php?page=' . $page);
                echo '<p><a href="' . esc_url($test_url) . '" target="_blank">' . esc_html($description) . '</a> - ' . esc_html($test_url) . '</p>';
            }
            ?>
        </div>

        <div class="section">
            <h2>📋 Debug Information</h2>
            <?php
            echo "<p><strong>WordPress Site URL:</strong> " . esc_html(get_site_url()) . "</p>";
            echo "<p><strong>WordPress Home URL:</strong> " . esc_html(get_home_url()) . "</p>";
            echo "<p><strong>Admin URL:</strong> " . esc_html(admin_url()) . "</p>";
            echo "<p><strong>Current Page:</strong> " . esc_html($_SERVER['REQUEST_URI']) . "</p>";
            echo "<p><strong>User Agent:</strong> " . esc_html($_SERVER['HTTP_USER_AGENT'] ?? 'Not set') . "</p>";
            ?>
        </div>

        <div class="section">
            <h2>⚠️ Common Issues & Solutions</h2>
            
            <div class="warning">
                <h3>Issue: "Authorization failed as the redirect URL doesn't match"</h3>
                <p><strong>Solution:</strong> Make sure the redirect URL in your HubSpot app exactly matches what your OAuth handler is using.</p>
            </div>

            <div class="warning">
                <h3>Issue: "localhost not allowed"</h3>
                <p><strong>Solution:</strong> Use RedirectMeTo or ngrok to create an HTTPS URL for localhost.</p>
            </div>

            <div class="warning">
                <h3>Issue: "HTTP not allowed"</h3>
                <p><strong>Solution:</strong> Use HTTPS URLs or a redirect service that provides HTTPS.</p>
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
    </script>
</body>
</html> 