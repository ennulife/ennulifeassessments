<?php
/**
 * ENNU LabCorp Locator
 * Find nearest LabCorp locations with map, hours, directions, and geolocation
 * Integrated within ENNU Life Assessments plugin
 * 
 * @package ENNU_Life
 * @since 78.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_LabCorp_Locator {

    /**
     * Initialize the LabCorp locator
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_ennu_get_nearest_labcorp', array( $this, 'get_nearest_labcorp' ) );
        add_action( 'wp_ajax_nopriv_ennu_get_nearest_labcorp', array( $this, 'get_nearest_labcorp' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue on dashboard page
        if ( ! is_page( 'dashboard' ) ) {
            return;
        }

        // Enqueue LabCorp locator CSS
        wp_enqueue_style( 
            'ennu-labcorp-locator', 
            ENNU_LIFE_PLUGIN_URL . 'assets/css/labcorp-locator.css', 
            array(), 
            ENNU_LIFE_VERSION 
        );

        // Enqueue Google Fonts
        wp_enqueue_style( 
            'ennu-locator-fonts', 
            'https://fonts.googleapis.com/css2?family=Poppins:wght@600&family=Inter:wght@400&display=swap', 
            array(), 
            null 
        );

        // Enqueue Google Maps if API key is set
        $api_key = get_option( 'ennu_google_maps_api_key' );
        if ( $api_key ) {
            wp_enqueue_script( 
                'google-maps', 
                "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places", 
                array(), 
                null, 
                true 
            );
        }

        // Enqueue locator script
        wp_enqueue_script( 
            'ennu-labcorp-locator', 
            ENNU_LIFE_PLUGIN_URL . 'assets/js/labcorp-locator.js', 
            array( 'jquery' ), 
            ENNU_LIFE_VERSION, 
            true 
        );

        // Localize script
        wp_localize_script(
            'ennu-labcorp-locator',
            'ennuLocator',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'ennu_locator_nonce' ),
                'api_key'  => $api_key
            )
        );
    }

    /**
     * Add settings page to ENNU Life menu
     */
    public function add_settings_page() {
        add_submenu_page(
            'ennu-life-dashboard',
            'LabCorp Locator Settings',
            'LabCorp Settings',
            'manage_options',
            'ennu-labcorp-settings',
            array( $this, 'settings_page' )
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'ennu_labcorp_settings', 'ennu_google_maps_api_key' );
    }

    /**
     * Settings page content
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>LabCorp Locator Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'ennu_labcorp_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Google Maps API Key</th>
                        <td>
                            <input type="text" name="ennu_google_maps_api_key" value="<?php echo esc_attr( get_option( 'ennu_google_maps_api_key' ) ); ?>" class="regular-text" />
                            <p class="description">
                                Enter your Google Maps API key with Places API and Maps JavaScript API enabled. 
                                <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">Get API Key</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * AJAX handler to get nearest LabCorp locations
     */
    public function get_nearest_labcorp() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_locator_nonce' ) ) {
            wp_send_json_error( 'Invalid security token' );
        }

        $api_key = get_option( 'ennu_google_maps_api_key' );
        if ( ! $api_key ) {
            wp_send_json_error( 'Google Maps API key not configured' );
        }

        $lat = floatval( sanitize_text_field( $_POST['lat'] ) );
        $lng = floatval( sanitize_text_field( $_POST['lng'] ) );

        if ( ! $lat || ! $lng ) {
            wp_send_json_error( 'Invalid coordinates' );
        }

        // Search for nearby LabCorp locations
        $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lat},{$lng}&keyword=Labcorp&rankby=distance&key={$api_key}";

        $response = wp_remote_get( esc_url_raw( $url ) );
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'Failed to fetch locations' );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( $body['status'] !== 'OK' || empty( $body['results'] ) ) {
            wp_send_json_error( 'No LabCorp locations found nearby' );
        }

        $locations = array();
        foreach ( array_slice( $body['results'], 0, 5 ) as $result ) {
            $place_id = $result['place_id'];
            $place_lat = $result['geometry']['location']['lat'];
            $place_lng = $result['geometry']['location']['lng'];
            $distance = $this->calculate_distance( $lat, $lng, $place_lat, $place_lng );

            // Fetch location details for hours and phone
            $details_url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=opening_hours,formatted_phone_number,website&key={$api_key}";
            $details_response = wp_remote_get( esc_url_raw( $details_url ) );
            
            $hours = 'Hours not available';
            $phone = '';
            $website = '';
            
            if ( ! is_wp_error( $details_response ) ) {
                $details_body = json_decode( wp_remote_retrieve_body( $details_response ), true );
                if ( isset( $details_body['result']['opening_hours']['weekday_text'] ) ) {
                    $hours = implode( '<br>', $details_body['result']['opening_hours']['weekday_text'] );
                }
                if ( isset( $details_body['result']['formatted_phone_number'] ) ) {
                    $phone = $details_body['result']['formatted_phone_number'];
                }
                if ( isset( $details_body['result']['website'] ) ) {
                    $website = $details_body['result']['website'];
                }
            }

            $locations[] = array(
                'name'     => sanitize_text_field( $result['name'] ),
                'address'  => sanitize_text_field( $result['vicinity'] ),
                'distance' => round( $distance, 2 ),
                'hours'    => wp_kses_post( $hours ),
                'phone'    => sanitize_text_field( $phone ),
                'website'  => esc_url( $website ),
                'lat'      => $place_lat,
                'lng'      => $place_lng,
                'rating'   => isset( $result['rating'] ) ? floatval( $result['rating'] ) : 0
            );
        }

        wp_send_json_success( array( 'locations' => $locations ) );
    }

    /**
     * Calculate distance between two points using Haversine formula
     */
    private function calculate_distance( $lat1, $lon1, $lat2, $lon2 ) {
        $earth_radius = 6371; // km
        $dlat = deg2rad( $lat2 - $lat1 );
        $dlon = deg2rad( $lon2 - $lon1 );
        $a = sin( $dlat / 2 ) * sin( $dlat / 2 ) + cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) * sin( $dlon / 2 ) * sin( $dlon / 2 );
        $c = 2 * atan2( sqrt( $a ), sqrt( 1 - $a ) );
        return $earth_radius * $c;
    }

    /**
     * Render the LabCorp locator HTML
     */
    public function render_locator() {
        $api_key = get_option( 'ennu_google_maps_api_key' );
        
        ob_start();
        ?>
        <div id="ennu-labcorp-locator" class="labcorp-locator-container">
            <div class="locator-header">
                <h3 class="locator-title">
                    <svg class="locator-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                    </svg>
                    Find Nearest LabCorp
                </h3>
                <p class="locator-subtitle">Locate nearby LabCorp facilities for convenient lab testing</p>
            </div>

            <?php if ( ! $api_key ) : ?>
                <div class="locator-notice">
                    <p><strong>Google Maps API key required.</strong> Please configure the API key in 
                    <a href="<?php echo admin_url( 'admin.php?page=ennu-labcorp-settings' ); ?>">LabCorp Settings</a> to use this feature.</p>
                </div>
            <?php else : ?>
                <div class="locator-search">
                    <div class="search-form">
                        <input type="text" id="locator-address" placeholder="Enter address or ZIP code (optional)" class="locator-input">
                        <div class="search-buttons">
                            <button id="locator-search-btn" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" fill="none"/>
                                    <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                Search
                            </button>
                            <button id="locator-geolocate-btn" class="btn btn-secondary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="currentColor"/>
                                </svg>
                                Use My Location
                            </button>
                        </div>
                    </div>
                </div>

                <div id="locator-map" class="locator-map"></div>
                <div id="locator-loading" class="locator-loading" style="display: none;">
                    <div class="loading-spinner"></div>
                    <p>Finding nearest LabCorp locations...</p>
                </div>
                <div id="locator-error" class="locator-error" style="display: none;"></div>
                <div id="locator-results" class="locator-results"></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the LabCorp locator
new ENNU_LabCorp_Locator();