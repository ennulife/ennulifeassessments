<?php
/**
 * HubSpot Redirect URL Debug Script
 * 
 * This script helps identify the correct redirect URL for HubSpot OAuth
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
    <title>HubSpot Redirect URL Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-url { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .copy-btn { background: #0073aa; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>🔍 HubSpot Redirect URL Debug Tool</h1>
    
    <div class="section">
        <h2>📋 Current Plugin Configuration</h2>
        <?php
        // Get current redirect URI from options
        $current_redirect = get_option( 'ennu_hubspot_redirect_uri', 'Not set' );
        echo "<p><strong>Current Redirect URI in Plugin:</strong> " . esc_html( $current_redirect ) . "</p>";
        
        // Get OAuth handler redirect URI
        if ( class_exists( 'ENNU_HubSpot_OAuth_Handler' ) ) {
            $oauth_handler = new ENNU_HubSpot_OAuth_Handler();
            $reflection = new ReflectionClass( $oauth_handler );
            $property = $reflection->getProperty( 'redirect_uri' );
            $property->setAccessible( true );
            $handler_redirect = $property->getValue( $oauth_handler );
            echo "<p><strong>OAuth Handler Redirect URI:</strong> " . esc_html( $handler_redirect ) . "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>🧪 Test URLs for HubSpot App Configuration</h2>
        <p>Try these URLs in your HubSpot app's redirect URL setting:</p>
        
        <?php
        $test_urls = array(
            'https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot-booking',
            'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth',
            'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-fields',
            'https://ennulife.com/wp-admin/admin.php?page=ennu-life',
            'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-settings',
            'https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot',
            'https://ennulife.com/wp-admin/admin.php?page=hubspot-oauth',
            'https://ennulife.com/wp-admin/admin.php?page=hubspot-booking',
            'https://ennulife.com/wp-admin/admin.php?page=hubspot-fields',
            'https://ennulife.com/wp-admin/admin.php?page=hubspot-settings'
        );
        
        foreach ( $test_urls as $index => $url ) {
            echo '<div class="test-url">';
            echo '<strong>Option ' . ( $index + 1 ) . ':</strong> ';
            echo '<code>' . esc_html( $url ) . '</code>';
            echo '<button class="copy-btn" onclick="copyToClipboard(\'' . esc_js( $url ) . '\')">Copy</button>';
            echo '</div>';
        }
        ?>
    </div>

    <div class="section">
        <h2>🔧 Manual Test Instructions</h2>
        <ol>
            <li>Go to your HubSpot Developer Portal: <a href="https://developers.hubspot.com/" target="_blank">https://developers.hubspot.com/</a></li>
            <li>Find your app and click on it</li>
            <li>Go to "Auth" or "OAuth Settings"</li>
            <li>Try each of the URLs above in the "Redirect URL" field</li>
            <li>Save the changes</li>
            <li>Test the OAuth connection from your WordPress admin</li>
            <li>Let me know which URL works!</li>
        </ol>
    </div>

    <div class="section">
        <h2>📊 Current WordPress Admin URLs</h2>
        <?php
        echo "<p><strong>Site URL:</strong> " . esc_html( get_site_url() ) . "</p>";
        echo "<p><strong>Admin URL:</strong> " . esc_html( admin_url() ) . "</p>";
        echo "<p><strong>Current Page:</strong> " . esc_html( $_SERVER['REQUEST_URI'] ) . "</p>";
        ?>
    </div>

    <div class="section">
        <h2>🎯 Quick Test</h2>
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