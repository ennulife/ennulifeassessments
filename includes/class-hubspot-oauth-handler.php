<?php
/**
 * HubSpot OAuth 2.0 Handler
 *
 * Modern OAuth 2.0 implementation for HubSpot integration
 * Supports both localhost and production environments
 *
 * @package   ENNU Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     3.37.14
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ENNU_HubSpot_OAuth_Handler class
 */
class ENNU_HubSpot_OAuth_Handler {

    /**
     * OAuth configuration
     */
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $authorization_url = 'https://app.hubspot.com/oauth/authorize';
    private $token_url = 'https://api.hubapi.com/oauth/v1/token';
    private $api_base_url = 'https://api.hubapi.com';

    /**
     * Constructor
     */
    public function __construct() {
        // DISABLED OAuth flow - using direct API token instead
        // The OAuth flow was causing "invalid app client_id" errors
        // We're using the working API token directly: 
        
        // Set the working API token directly
        update_option( 'ennu_hubspot_access_token', '' );
        
        // Disable OAuth menu and hooks to prevent connection errors
        // add_action( 'init', array( $this, 'handle_oauth_callback' ) );
        // add_action( 'admin_menu', array( $this, 'add_oauth_menu' ), 20 );
        // add_action( 'wp_ajax_ennu_test_hubspot_connection', array( $this, 'test_connection' ) );
        // add_action( 'wp_ajax_ennu_revoke_hubspot_access', array( $this, 'revoke_access' ) );
        
        error_log( 'ENNU Life Plugin: OAuth flow disabled - using direct API token instead to prevent connection errors' );
    }

    /**
     * Dynamically determine redirect URI based on environment
     */
    private function get_redirect_uri() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $is_local = in_array( $host, array( 'localhost', '127.0.0.1', 'local.ennulife.com' ) );
        
        if ( $is_local ) {
            return 'http://localhost/wp-admin/admin.php?page=ennu-hubspot-oauth';
        } else {
            // Use the exact redirect URI from your HubSpot app configuration
            return 'https://ennulife.com/wp-admin/admin.php?page=ennu-hubspot-oauth';
        }
    }

    /**
     * Add OAuth menu page
     */
    public function add_oauth_menu() {
        add_submenu_page(
            'ennu-life',
            'HubSpot OAuth',
            'HubSpot OAuth',
            'manage_options',
            'ennu-hubspot-oauth',
            array( $this, 'render_oauth_page' )
        );
    }

    /**
     * Render OAuth page
     */
    public function render_oauth_page() {
        $access_token = get_option( 'ennu_hubspot_access_token' );
        $refresh_token = get_option( 'ennu_hubspot_refresh_token' );
        $token_expires = get_option( 'ennu_hubspot_token_expires' );
        
        echo '<div class="wrap">';
        echo '<h1>🔗 HubSpot OAuth Configuration</h1>';
        
        // Check if credentials are configured
        $client_id = get_option( 'ennu_hubspot_client_id', $this->client_id );
        $client_secret = get_option( 'ennu_hubspot_client_secret', $this->client_secret );
        
        if ( $client_id && $client_secret ) {
            echo '<div class="notice notice-success"><p>✅ HubSpot OAuth credentials are configured and ready</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>❌ HubSpot OAuth credentials are not configured</p></div>';
        }
        
        // Check if we have valid tokens
        if ( $access_token && $refresh_token ) {
            $is_expired = $token_expires && time() > $token_expires;
            if ( $is_expired ) {
                echo '<div class="notice notice-warning"><p>⚠️ Access token has expired. Please re-authorize.</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>✅ HubSpot OAuth is connected and active</p></div>';
            }
        } else {
            echo '<div class="notice notice-warning"><p>⚠️ Not connected to HubSpot. Please authorize below.</p></div>';
        }
        
        // Configuration Status Section
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2><span style="color: #0073aa;">📁</span> Configuration Status</h2>';
        echo '<table class="form-table">';
        echo '<tr><th>Client ID:</th><td>' . ( $client_id ? '<span style="color: #28a745;">✅ Configured</span>' : '<span style="color: #dc3545;">❌ Not configured</span>' ) . '</td></tr>';
        echo '<tr><th>Client Secret:</th><td>' . ( $client_secret ? '<span style="color: #28a745;">✅ Configured</span>' : '<span style="color: #dc3545;">❌ Not configured</span>' ) . '</td></tr>';
        echo '<tr><th>Redirect URI:</th><td><code>' . esc_html( $this->redirect_uri ) . '</code></td></tr>';
        echo '<tr><th>Access Token:</th><td>' . ( $access_token ? '<span style="color: #28a745;">✅ Active</span>' : '<span style="color: #dc3545;">❌ Not available</span>' ) . '</td></tr>';
        echo '<tr><th>Refresh Token:</th><td>' . ( $refresh_token ? '<span style="color: #28a745;">✅ Available</span>' : '<span style="color: #dc3545;">❌ Not available</span>' ) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        // OAuth Credentials Section
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2><span style="color: #0073aa;">🔑</span> OAuth Credentials</h2>';
        echo '<p>Your current HubSpot OAuth credentials:</p>';
        echo '<table class="form-table">';
        echo '<tr><th>Client ID:</th><td><code>' . esc_html( $client_id ?: 'Not configured' ) . '</code></td></tr>';
        echo '<tr><th>Client Secret:</th><td><code>' . ( $client_secret ? '••••••••••••••••••••••••••••••••' : 'Not configured' ) . '</code></td></tr>';
        echo '</table>';
        echo '<p><em>These credentials are stored in WordPress options and can be updated via the admin settings.</em></p>';
        echo '</div>';
        
        // Authorization Section
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2><span style="color: #0073aa;">🔐</span> Authorization</h2>';
        
        if ( $access_token && !$this->is_token_expired() ) {
            echo '<p><span style="color: #28a745;">✅ You are authorized with HubSpot</span></p>';
            echo '<p><button id="test-connection" class="button button-primary">Test Connection</button> ';
            echo '<button id="revoke-access" class="button">Revoke Access</button></p>';
        } else {
            echo '<p><span style="color: #dc3545;">❌ Not authorized with HubSpot</span></p>';
            echo '<p><a href="' . esc_url( $this->get_authorization_url() ) . '" class="button button-primary">Authorize with HubSpot</a></p>';
        }
        echo '</div>';
        
        // JavaScript for AJAX actions
        echo '<script>
        jQuery(document).ready(function($) {
            $("#test-connection").on("click", function() {
                $.post(ajaxurl, {
                    action: "ennu_test_hubspot_connection",
                    nonce: "' . wp_create_nonce( 'ennu_hubspot_nonce' ) . '"
                }, function(response) {
                    if (response.success) {
                        alert("✅ Connection test successful!");
                    } else {
                        alert("❌ Connection test failed: " + response.data);
                    }
                });
            });
            
            $("#revoke-access").on("click", function() {
                if (confirm("Are you sure you want to revoke HubSpot access?")) {
                    $.post(ajaxurl, {
                        action: "ennu_revoke_hubspot_access",
                        nonce: "' . wp_create_nonce( 'ennu_hubspot_nonce' ) . '"
                    }, function(response) {
                        if (response.success) {
                            alert("✅ Access revoked successfully!");
                            location.reload();
                        } else {
                            alert("❌ Failed to revoke access: " + response.data);
                        }
                    });
                }
            });
        });
        </script>';
        
        echo '</div>';
    }

    /**
     * Handle OAuth callback
     */
    public function handle_oauth_callback() {
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'ennu-hubspot-oauth' ) {
            return;
        }
        
        if ( isset( $_GET['code'] ) ) {
            $this->exchange_code_for_token( $_GET['code'] );
        }
    }

    /**
     * Get authorization URL
     */
    private function get_authorization_url() {
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'contacts',
            'response_type' => 'code'
        );
        
        return $this->authorization_url . '?' . http_build_query( $params );
    }

    /**
     * Exchange authorization code for access token
     */
    private function exchange_code_for_token( $code ) {
        $response = wp_remote_post( $this->token_url, array(
            'body' => array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'code' => $code
            )
        ) );
        
        if ( is_wp_error( $response ) ) {
            error_log( 'ENNU Life Plugin: OAuth token exchange failed: ' . $response->get_error_message() );
            return;
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['access_token'] ) ) {
            update_option( 'ennu_hubspot_access_token', $body['access_token'] );
            update_option( 'ennu_hubspot_refresh_token', $body['refresh_token'] );
            update_option( 'ennu_hubspot_token_expires', time() + $body['expires_in'] );
            
            error_log( 'ENNU Life Plugin: OAuth tokens saved successfully' );
            
            // Redirect to remove the code parameter
            wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-oauth&oauth_success=1' ) );
            exit;
        } else {
            error_log( 'ENNU Life Plugin: OAuth token exchange failed: ' . print_r( $body, true ) );
        }
    }

    /**
     * Refresh access token
     */
    private function refresh_access_token() {
        $refresh_token = get_option( 'ennu_hubspot_refresh_token' );
        
        if ( ! $refresh_token ) {
            return false;
        }
        
        $response = wp_remote_post( $this->token_url, array(
            'body' => array(
                'grant_type' => 'refresh_token',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $refresh_token
            )
        ) );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( isset( $body['access_token'] ) ) {
            update_option( 'ennu_hubspot_access_token', $body['access_token'] );
            update_option( 'ennu_hubspot_token_expires', time() + $body['expires_in'] );
            return true;
        }
        
        return false;
    }

    /**
     * Check if token is expired
     */
    private function is_token_expired() {
        $expires = get_option( 'ennu_hubspot_token_expires' );
        return $expires && time() > $expires;
    }

    /**
     * Get valid access token
     */
    public function get_access_token() {
        if ( $this->is_token_expired() ) {
            if ( ! $this->refresh_access_token() ) {
                return false;
            }
        }
        
        return get_option( 'ennu_hubspot_access_token' );
    }

    /**
     * Get refresh token from HubSpot app
     *
     * @return string|false
     */
    private function get_refresh_token_from_hubspot() {
        // Try to get refresh token from HubSpot API
        $response = wp_remote_get( $this->api_base_url . '/oauth/v1/access-tokens/' . get_option( 'ennu_hubspot_access_token' ), array(
            'headers' => array(
                'Authorization' => 'Bearer ' . get_option( 'ennu_hubspot_access_token' ),
                'Content-Type'  => 'application/json',
            ),
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'ENNU Life: Failed to get refresh token from HubSpot - ' . $response->get_error_message() );
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $data && isset( $data['refresh_token'] ) ) {
            error_log( 'ENNU Life: Found refresh token from HubSpot API' );
            return $data['refresh_token'];
        }

        error_log( 'ENNU Life: No refresh token found in HubSpot API response' );
        return false;
    }


    /**
     * Test HubSpot connection
     */
    public function test_connection() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        $access_token = $this->get_access_token();
        
        if ( ! $access_token ) {
            wp_send_json_error( 'No valid access token available' );
        }

        // Test API call to HubSpot
        $response = wp_remote_get( 'https://api.hubapi.com/crm/v3/objects/contacts?limit=1', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            )
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'Connection error: ' . $response->get_error_message() );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        
        if ( $status_code === 200 ) {
            wp_send_json_success( 'HubSpot OAuth connection successful!' );
        } else {
            $body = json_decode( wp_remote_retrieve_body( $response ), true );
            $error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
            wp_send_json_error( 'Connection failed: ' . $error_message );
        }
    }

    /**
     * Revoke HubSpot access
     */
    public function revoke_access() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Insufficient permissions' );
        }

        // Clear all OAuth tokens
        delete_option( 'ennu_hubspot_access_token' );
        delete_option( 'ennu_hubspot_refresh_token' );
        delete_option( 'ennu_hubspot_token_expires' );
        
        wp_send_json_success( 'Access revoked successfully' );
    }

    /**
     * Make authenticated API request
     */
    public function make_api_request( $endpoint, $method = 'GET', $data = null ) {
        $access_token = $this->get_access_token();
        
        if ( ! $access_token ) {
            return new WP_Error( 'no_token', 'No valid access token available' );
        }

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            )
        );

        if ( $data && $method !== 'GET' ) {
            $args['body'] = json_encode( $data );
        }

        $response = wp_remote_request( 'https://api.hubapi.com' . $endpoint, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $status_code >= 200 && $status_code < 300 ) {
            return $body;
        } else {
            return new WP_Error( 'api_error', isset( $body['message'] ) ? $body['message'] : 'API request failed' );
        }
    }
} 