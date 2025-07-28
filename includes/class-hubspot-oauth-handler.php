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

    /**
     * Constructor
     */
    public function __construct() {
        // Hard-coded OAuth credentials (will not change)
        $this->client_id = '959bd865-5a24-4a43-a8bf-05a69c537938';
        $this->client_secret = '56cc5735-c274-4e43-99d4-3660d816a624';
        $this->redirect_uri = $this->get_redirect_uri();
        
        // Setup hooks
        add_action( 'init', array( $this, 'handle_oauth_callback' ) );
        add_action( 'admin_menu', array( $this, 'add_oauth_menu' ), 20 );
        add_action( 'wp_ajax_ennu_test_hubspot_connection', array( $this, 'test_connection' ) );
        add_action( 'wp_ajax_ennu_revoke_hubspot_access', array( $this, 'revoke_access' ) );
        
        error_log( 'ENNU Life Plugin: Initialized ENNU_HubSpot_OAuth_Handler with hard-coded credentials and redirect URI: ' . $this->redirect_uri );
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
        
        // Hard-coded credentials are always available
        echo '<div class="notice notice-success"><p>✅ HubSpot OAuth credentials are hard-coded and ready</p></div>';
        
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
        
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2>📋 Configuration Status</h2>';
        echo '<table class="form-table">';
        echo '<tr><th>Client ID:</th><td>✅ Hard-coded and ready</td></tr>';
        echo '<tr><th>Client Secret:</th><td>✅ Hard-coded and ready</td></tr>';
        echo '<tr><th>Redirect URI:</th><td><code>' . esc_html( $this->redirect_uri ) . '</code></td></tr>';
        echo '<tr><th>Access Token:</th><td>' . ( $access_token ? '✅ Active' : '❌ Not available' ) . '</td></tr>';
        echo '<tr><th>Refresh Token:</th><td>' . ( $refresh_token ? '✅ Available' : '❌ Not available' ) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        // Hard-coded credentials info
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2>🔑 OAuth Credentials</h2>';
        echo '<p>Your HubSpot OAuth credentials are hard-coded and ready to use:</p>';
        echo '<table class="form-table">';
        echo '<tr><th>Client ID:</th><td><code>' . esc_html( $this->client_id ) . '</code></td></tr>';
        echo '<tr><th>Client Secret:</th><td><code>••••••••••••••••••••••••••••••••</code></td></tr>';
        echo '</table>';
        echo '<p><em>These credentials are securely hard-coded and will not change.</em></p>';
        echo '</div>';
        
        // Authorization section (credentials are always available)
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2>🔐 Authorization</h2>';
        
        if ( $access_token && !$this->is_token_expired() ) {
            echo '<p>✅ You are authorized with HubSpot</p>';
            echo '<p><a href="#" id="test-connection" class="button button-primary">Test Connection</a></p>';
            echo '<p><a href="#" id="revoke-access" class="button button-secondary">Revoke Access</a></p>';
        } else {
            echo '<p>You need to authorize this application with HubSpot:</p>';
            echo '<p><a href="' . esc_url( $this->get_authorization_url() ) . '" class="button button-primary">Authorize with HubSpot</a></p>';
        }
        echo '</div>';
        
        // Setup instructions
        echo '<div class="card" style="max-width: 800px; margin-top: 20px;">';
        echo '<h2>📖 Setup Instructions</h2>';
        echo '<ol>';
        echo '<li>Go to <a href="https://developers.hubspot.com/" target="_blank">HubSpot Developer Portal</a></li>';
        echo '<li>Create a new app or select an existing one</li>';
        echo '<li>Go to "Auth" settings</li>';
        echo '<li>Add this redirect URI: <code>' . esc_html( $this->redirect_uri ) . '</code></li>';
        echo '<li>Copy your Client ID and Client Secret</li>';
        echo '<li>Enter them in the form above</li>';
        echo '<li>Click "Authorize with HubSpot"</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '<div id="test-results" style="margin-top: 20px;"></div>';
        echo '</div>';
        
        // Add JavaScript
        echo '<script>
        jQuery(document).ready(function($) {
            $("#test-connection").on("click", function(e) {
                e.preventDefault();
                $("#test-results").html("<p>Testing connection...</p>");
                
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: "ennu_test_hubspot_connection",
                        nonce: "' . wp_create_nonce( 'ennu_hubspot_test' ) . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#test-results").html("<div class=\'notice notice-success\'><p>" + response.data + "</p></div>");
                        } else {
                            $("#test-results").html("<div class=\'notice notice-error\'><p>" + response.data + "</p></div>");
                        }
                    }
                });
            });
            
            $("#revoke-access").on("click", function(e) {
                e.preventDefault();
                if (confirm("Are you sure you want to revoke HubSpot access?")) {
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "ennu_revoke_hubspot_access",
                            nonce: "' . wp_create_nonce( 'ennu_hubspot_revoke' ) . '"
                        },
                        success: function(response) {
                            if (response.success) {
                                alert("Access revoked successfully!");
                                location.reload();
                            } else {
                                alert("Error: " + response.data);
                            }
                        }
                    });
                }
            });
        });
        </script>';
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
     * Test HubSpot connection
     */
    public function test_connection() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_test' ) ) {
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
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_revoke' ) ) {
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