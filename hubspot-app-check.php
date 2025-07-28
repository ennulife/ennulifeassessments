<?php
/**
 * HubSpot App Configuration Checker
 * 
 * This script helps identify what redirect URL your HubSpot app expects
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
    <title>HubSpot App Configuration Checker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
        .code { background: #f0f0f0; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 HubSpot App Configuration Analysis</h1>
    
    <div class="section">
        <h2>📊 Error Analysis</h2>
        <p>Based on your error message:</p>
        <div class="code">
Authorization failed as the redirect URL 'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth' doesn't match the app's registered redirect URL.
        </div>
        
        <p><strong>Analysis:</strong></p>
        <ul>
            <li>✅ Your HubSpot app IS configured with a redirect URL</li>
            <li>❌ The URL it's configured with is NOT: <code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
            <li>🎯 We need to find what URL your app IS configured with</li>
        </ul>
    </div>

    <div class="section">
        <h2>🔧 Most Likely Solutions</h2>
        
        <h3>Option 1: Check Your HubSpot App Settings</h3>
        <ol>
            <li>Go to <a href="https://developers.hubspot.com/" target="_blank">HubSpot Developer Portal</a></li>
            <li>Find your app</li>
            <li>Look at the "Redirect URL" field</li>
            <li>Tell me what URL is currently set there</li>
        </ol>
        
        <h3>Option 2: Common HubSpot App Redirect URLs</h3>
        <p>Try these URLs in your HubSpot app (most likely candidates):</p>
        <ul>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot-booking</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-fields</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-life</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-settings</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot</code></li>
        </ul>
        
        <h3>Option 3: Check for URL Variations</h3>
        <p>Your app might be configured with:</p>
        <ul>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=hubspot-oauth</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=hubspot-booking</code></li>
            <li><code>https://ennulife.com/wp-admin/admin.php?page=hubspot-fields</code></li>
        </ul>
    </div>

    <div class="section">
        <h2>🎯 Quick Test Method</h2>
        <p>Here's the fastest way to find the correct URL:</p>
        <ol>
            <li>Go to your HubSpot app settings</li>
            <li>Copy the current redirect URL</li>
            <li>Tell me what it is</li>
            <li>I'll update the plugin to match it exactly</li>
        </ol>
        
        <p><strong>OR</strong></p>
        <ol>
            <li>Try each URL above in your HubSpot app</li>
            <li>Test the OAuth connection after each change</li>
            <li>When it works, tell me which URL worked</li>
        </ol>
    </div>

    <div class="section">
        <h2>📋 Current Plugin Status</h2>
        <?php
        echo "<p><strong>Plugin Version:</strong> " . esc_html( ENNU_LIFE_VERSION ) . "</p>";
        echo "<p><strong>Site URL:</strong> " . esc_html( get_site_url() ) . "</p>";
        echo "<p><strong>Admin URL:</strong> " . esc_html( admin_url() ) . "</p>";
        
        // Check if OAuth handler exists
        if ( class_exists( 'ENNU_HubSpot_OAuth_Handler' ) ) {
            echo "<p class='success'>✅ OAuth Handler class exists</p>";
        } else {
            echo "<p class='error'>❌ OAuth Handler class not found</p>";
        }
        
        // Check current redirect URI
        $current_redirect = get_option( 'ennu_hubspot_redirect_uri', 'Not set' );
        echo "<p><strong>Current Redirect URI:</strong> " . esc_html( $current_redirect ) . "</p>";
        ?>
    </div>

    <div class="section">
        <h2>🚨 Emergency Fix</h2>
        <p>If you can't find your HubSpot app settings, try this:</p>
        <ol>
            <li>Create a new HubSpot app</li>
            <li>Set the redirect URL to: <code>https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot-booking</code></li>
            <li>Use the new app's credentials</li>
        </ol>
    </div>
</body>
</html> 