<?php
/**
 * HubSpot OAuth Test Script
 * 
 * Simple test to verify OAuth handler functionality
 */

// Load WordPress
require_once dirname( __FILE__ ) . '/../../../wp-config.php';
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

// Check if user is logged in and has admin access
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You do not have permission to access this page.' );
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>HubSpot OAuth Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 HubSpot OAuth Test</h1>
        
        <?php
        // Test 1: Check if OAuth handler class exists
        echo '<div class="test-section info">';
        echo '<h3>Test 1: Class Existence</h3>';
        if ( class_exists( 'ENNU_HubSpot_OAuth_Handler' ) ) {
            echo '<p class="success">✅ ENNU_HubSpot_OAuth_Handler class exists</p>';
        } else {
            echo '<p class="error">❌ ENNU_HubSpot_OAuth_Handler class not found</p>';
        }
        echo '</div>';
        
        // Test 2: Check if OAuth handler is instantiated
        echo '<div class="test-section info">';
        echo '<h3>Test 2: Handler Instantiation</h3>';
        try {
            $oauth_handler = new ENNU_HubSpot_OAuth_Handler();
            echo '<p class="success">✅ OAuth handler instantiated successfully</p>';
            
            // Test 3: Check redirect URI
            $reflection = new ReflectionClass( $oauth_handler );
            $property = $reflection->getProperty( 'redirect_uri' );
            $property->setAccessible( true );
            $redirect_uri = $property->getValue( $oauth_handler );
            echo '<p><strong>Redirect URI:</strong> <code>' . esc_html( $redirect_uri ) . '</code></p>';
            
        } catch ( Exception $e ) {
            echo '<p class="error">❌ Failed to instantiate OAuth handler: ' . esc_html( $e->getMessage() ) . '</p>';
        }
        echo '</div>';
        
        // Test 4: Check stored credentials
        echo '<div class="test-section info">';
        echo '<h3>Test 3: Stored Credentials</h3>';
        $client_id = get_option( 'ennu_hubspot_client_id' );
        $client_secret = get_option( 'ennu_hubspot_client_secret' );
        $access_token = get_option( 'ennu_hubspot_access_token' );
        $refresh_token = get_option( 'ennu_hubspot_refresh_token' );
        
        echo '<p><strong>Client ID:</strong> ' . ( $client_id ? '✅ Set' : '❌ Not set' ) . '</p>';
        echo '<p><strong>Client Secret:</strong> ' . ( $client_secret ? '✅ Set' : '❌ Not set' ) . '</p>';
        echo '<p><strong>Access Token:</strong> ' . ( $access_token ? '✅ Set' : '❌ Not set' ) . '</p>';
        echo '<p><strong>Refresh Token:</strong> ' . ( $refresh_token ? '✅ Set' : '❌ Not set' ) . '</p>';
        echo '</div>';
        
        // Test 5: Check menu registration
        echo '<div class="test-section info">';
        echo '<h3>Test 4: Menu Registration</h3>';
        global $submenu;
        $menu_exists = false;
        if ( isset( $submenu['ennu-life'] ) ) {
            foreach ( $submenu['ennu-life'] as $item ) {
                if ( $item[2] === 'ennu-hubspot-oauth' ) {
                    $menu_exists = true;
                    break;
                }
            }
        }
        echo '<p><strong>OAuth Menu:</strong> ' . ( $menu_exists ? '✅ Registered' : '❌ Not registered' ) . '</p>';
        echo '</div>';
        
        // Test 6: Environment detection
        echo '<div class="test-section info">';
        echo '<h3>Test 5: Environment Detection</h3>';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $is_local = in_array( $host, array( 'localhost', '127.0.0.1', 'local.ennulife.com' ) );
        echo '<p><strong>Current Host:</strong> ' . esc_html( $host ) . '</p>';
        echo '<p><strong>Environment:</strong> ' . ( $is_local ? 'Local Development' : 'Production' ) . '</p>';
        echo '</div>';
        ?>
        
        <div class="test-section">
            <h3>🔗 Quick Links</h3>
            <p><a href="<?php echo admin_url( 'admin.php?page=ennu-hubspot-oauth' ); ?>" target="_blank">HubSpot OAuth Configuration</a></p>
            <p><a href="<?php echo admin_url( 'admin.php?page=ennu-life' ); ?>" target="_blank">ENNU Life Dashboard</a></p>
        </div>
        
        <div class="test-section">
            <h3>📋 Next Steps</h3>
            <ol>
                <li>Go to <a href="https://developers.hubspot.com/" target="_blank">HubSpot Developer Portal</a></li>
                <li>Create a new app or select an existing one</li>
                <li>Add the redirect URI shown above to your app's OAuth settings</li>
                <li>Copy your Client ID and Client Secret</li>
                <li>Configure them in the HubSpot OAuth page</li>
                <li>Test the connection</li>
            </ol>
        </div>
    </div>
</body>
</html> 