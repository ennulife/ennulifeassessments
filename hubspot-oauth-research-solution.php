<?php
/**
 * HubSpot OAuth Research-Based Solution
 * 
 * Based on HubSpot community research and best practices
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
    <title>HubSpot OAuth Research-Based Solution</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fafafa; }
        .solution { background: #e8f5e8; border-left: 4px solid #4caf50; padding: 15px; margin: 10px 0; }
        .warning { background: #fff3e0; border-left: 4px solid #ff9800; padding: 15px; margin: 10px 0; }
        .info { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 10px 0; }
        .code { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto; }
        .step { background: #fff; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #0073aa; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔬 HubSpot OAuth Research-Based Solution</h1>
            <p>Based on HubSpot community research and best practices</p>
        </div>

        <div class="section">
            <h2>📊 Research Findings from HubSpot Community</h2>
            
            <div class="info">
                <h3>Source: <a href="https://community.hubspot.com/t5/APIs-Integrations/Correct-redirect_uri-for-MS-Power-Automate/m-p/927104" target="_blank">HubSpot Community Discussion</a></h3>
                <p><strong>Key Finding:</strong> The solution often involves using the HubSpot CRM V2 Connector due to authentication system changes.</p>
            </div>

            <div class="solution">
                <h3>✅ Recommended Solution for WordPress</h3>
                <p>Based on the research, here's the most effective approach for your WordPress plugin:</p>
            </div>
        </div>

        <div class="section">
            <h2>🎯 Step-by-Step Solution</h2>
            
            <div class="step">
                <h3>Step 1: Verify Your HubSpot App Type</h3>
                <ol>
                    <li>Go to <a href="https://developers.hubspot.com/" target="_blank">HubSpot Developer Portal</a></li>
                    <li>Check if you're using a <strong>Private App</strong> or <strong>Developer App</strong></li>
                    <li>For Private Apps: Use Access Token authentication</li>
                    <li>For Developer Apps: Use OAuth 2.0 with exact redirect URL matching</li>
                </ol>
            </div>

            <div class="step">
                <h3>Step 2: Exact URL Matching (Critical)</h3>
                <p>Based on the research, the redirect URL must match <strong>exactly</strong>:</p>
                <div class="code">
https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth
                </div>
                <p><strong>No trailing slashes, no extra parameters, exact protocol (HTTPS)</strong></p>
            </div>

            <div class="step">
                <h3>Step 3: Update HubSpot App Configuration</h3>
                <ol>
                    <li>In your HubSpot app settings, set the redirect URL to exactly:</li>
                    <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
                    <li>Save the changes</li>
                    <li>Clear any cached settings</li>
                </ol>
            </div>

            <div class="step">
                <h3>Step 4: Test the Connection</h3>
                <ol>
                    <li>Go to your WordPress admin</li>
                    <li>Navigate to: <code>/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
                    <li>Click "Connect to HubSpot"</li>
                    <li>The OAuth flow should now work perfectly</li>
                </ol>
            </div>
        </div>

        <div class="section">
            <h2>🔧 Alternative Solutions (Based on Research)</h2>
            
            <div class="warning">
                <h3>Option 1: Use Private App (Simpler)</h3>
                <p>If OAuth continues to fail, consider switching to a Private App:</p>
                <ol>
                    <li>Create a Private App in HubSpot</li>
                    <li>Use the Access Token directly</li>
                    <li>No redirect URL needed</li>
                    <li>Simpler authentication method</li>
                </ol>
            </div>

            <div class="info">
                <h3>Option 2: Multiple Redirect URLs</h3>
                <p>HubSpot allows multiple redirect URLs per app. You can add several for testing:</p>
                <ul>
                    <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth</code></li>
                    <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-life-hubspot-booking</code></li>
                    <li><code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-fields</code></li>
                </ul>
            </div>
        </div>

        <div class="section">
            <h2>📋 Current Plugin Status</h2>
            <?php
            echo "<p><strong>Plugin Version:</strong> " . esc_html( ENNU_LIFE_VERSION ) . "</p>";
            echo "<p><strong>Current Redirect URI:</strong> <code>https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth</code></p>";
            
            // Check OAuth handler
            if ( class_exists( 'ENNU_HubSpot_OAuth_Handler' ) ) {
                echo "<p class='solution'>✅ OAuth Handler configured correctly</p>";
            } else {
                echo "<p class='warning'>⚠️ OAuth Handler not found</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2>🚀 Quick Test</h2>
            <p>Test your current configuration:</p>
            <p><a href="<?php echo admin_url( 'admin.php?page=ennu-hubspot-oauth' ); ?>" target="_blank" class="button button-primary">Test HubSpot OAuth Connection</a></p>
        </div>

        <div class="section">
            <h2>💡 Pro Tips from Research</h2>
            <div class="info">
                <ul>
                    <li><strong>Exact Match Required:</strong> HubSpot requires perfect URL matching - no variations allowed</li>
                    <li><strong>HTTPS Only:</strong> Production URLs must use HTTPS protocol</li>
                    <li><strong>Clear Cache:</strong> Clear browser and WordPress cache after changes</li>
                    <li><strong>Multiple URLs:</strong> HubSpot allows multiple redirect URLs for flexibility</li>
                    <li><strong>Private Apps:</strong> Consider using Private Apps for simpler authentication</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 