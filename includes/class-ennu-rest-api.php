<?php
/**
 * ENNU REST API Controller
 * Provides comprehensive REST API endpoints for all ENNU Life functionality
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_REST_API {

    /**
     * API namespace
     */
    const NAMESPACE = 'ennu/v1';

    /**
     * Initialize REST API
     */
    public static function init() {
        add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
        add_filter( 'rest_authentication_errors', array( __CLASS__, 'authenticate_request' ) );
        
        error_log('ENNU REST API: Initialized');
    }

    /**
     * Register all API routes
     */
    public static function register_routes() {
        register_rest_route( self::NAMESPACE, '/assessments', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_assessments' ),
            'permission_callback' => array( __CLASS__, 'check_api_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/assessments/(?P<type>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_assessment' ),
            'permission_callback' => array( __CLASS__, 'check_api_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/assessments/(?P<type>[a-zA-Z0-9_-]+)/submit', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'submit_assessment' ),
            'permission_callback' => array( __CLASS__, 'check_api_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/users/(?P<id>\d+)/scores', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_user_scores' ),
            'permission_callback' => array( __CLASS__, 'check_user_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/users/(?P<id>\d+)/biomarkers', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_user_biomarkers' ),
            'permission_callback' => array( __CLASS__, 'check_user_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/users/(?P<id>\d+)/biomarkers', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'update_user_biomarkers' ),
            'permission_callback' => array( __CLASS__, 'check_medical_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/users/(?P<id>\d+)/goals', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_user_goals' ),
            'permission_callback' => array( __CLASS__, 'check_user_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/users/(?P<id>\d+)/goals', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'update_user_goals' ),
            'permission_callback' => array( __CLASS__, 'check_user_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/biomarkers/flag', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'flag_biomarker' ),
            'permission_callback' => array( __CLASS__, 'check_medical_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/biomarkers/unflag', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'unflag_biomarker' ),
            'permission_callback' => array( __CLASS__, 'check_medical_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/biomarkers/flagged', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_flagged_biomarkers' ),
            'permission_callback' => array( __CLASS__, 'check_medical_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/lab-data/import', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'import_lab_data' ),
            'permission_callback' => array( __CLASS__, 'check_lab_import_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/lab-data/validate', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'validate_lab_data' ),
            'permission_callback' => array( __CLASS__, 'check_lab_import_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/users/(?P<id>\d+)/trends', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_user_trends' ),
            'permission_callback' => array( __CLASS__, 'check_user_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/medical-staff', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_medical_staff' ),
            'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/medical-staff/assign-role', array(
            'methods' => 'POST',
            'callback' => array( __CLASS__, 'assign_medical_role' ),
            'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/analytics/overview', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_analytics_overview' ),
            'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
        ) );

        register_rest_route( self::NAMESPACE, '/analytics/users', array(
            'methods' => 'GET',
            'callback' => array( __CLASS__, 'get_user_analytics' ),
            'permission_callback' => array( __CLASS__, 'check_admin_permissions' ),
        ) );
    }

    /**
     * Check API permissions
     *
     * @param WP_REST_Request $request Request object
     * @return bool Whether user has permissions
     */
    public static function check_api_permissions( $request ) {
        return is_user_logged_in() || self::validate_api_key( $request );
    }

    /**
     * Check user permissions
     *
     * @param WP_REST_Request $request Request object
     * @return bool Whether user has permissions
     */
    public static function check_user_permissions( $request ) {
        $user_id = $request->get_param( 'id' );
        $current_user_id = get_current_user_id();

        if ( $current_user_id == $user_id ) {
            return true;
        }

        if ( class_exists( 'ENNU_Medical_Role_Manager' ) ) {
            return ENNU_Medical_Role_Manager::user_has_medical_access( $current_user_id );
        }

        return current_user_can( 'manage_options' );
    }

    /**
     * Check medical permissions
     *
     * @param WP_REST_Request $request Request object
     * @return bool Whether user has permissions
     */
    public static function check_medical_permissions( $request ) {
        if ( class_exists( 'ENNU_Medical_Role_Manager' ) ) {
            return ENNU_Medical_Role_Manager::user_has_medical_access();
        }

        return current_user_can( 'manage_options' );
    }

    /**
     * Check lab import permissions
     *
     * @param WP_REST_Request $request Request object
     * @return bool Whether user has permissions
     */
    public static function check_lab_import_permissions( $request ) {
        if ( class_exists( 'ENNU_Medical_Role_Manager' ) ) {
            return ENNU_Medical_Role_Manager::user_can_import_lab_data();
        }

        return current_user_can( 'manage_options' );
    }

    /**
     * Check admin permissions
     *
     * @param WP_REST_Request $request Request object
     * @return bool Whether user has permissions
     */
    public static function check_admin_permissions( $request ) {
        return current_user_can( 'manage_options' );
    }

    /**
     * Validate API key
     *
     * @param WP_REST_Request $request Request object
     * @return bool Whether API key is valid
     */
    private static function validate_api_key( $request ) {
        $api_key = $request->get_header( 'X-ENNU-API-Key' );
        if ( ! $api_key ) {
            $api_key = $request->get_param( 'api_key' );
        }

        if ( ! $api_key ) {
            return false;
        }

        $valid_api_keys = get_option( 'ennu_api_keys', array() );
        return in_array( $api_key, $valid_api_keys, true );
    }

    /**
     * Authenticate request
     *
     * @param WP_Error|null|bool $result Authentication result
     * @return WP_Error|null|bool Modified result
     */
    public static function authenticate_request( $result ) {
        if ( ! empty( $result ) ) {
            return $result;
        }

        $request = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
        if ( strpos( $request, '/ennu/v1/' ) !== 0 ) {
            return $result;
        }

        $api_key = $_SERVER['HTTP_X_ENNU_API_KEY'] ?? $_GET['api_key'] ?? '';
        if ( $api_key && self::validate_api_key_direct( $api_key ) ) {
            return true;
        }

        return $result;
    }

    /**
     * Validate API key directly
     *
     * @param string $api_key API key
     * @return bool Whether API key is valid
     */
    private static function validate_api_key_direct( $api_key ) {
        $valid_api_keys = get_option( 'ennu_api_keys', array() );
        return in_array( $api_key, $valid_api_keys, true );
    }

    /**
     * Get assessments
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_assessments( $request ) {
        $assessments = array(
            'testosterone' => array( 'title' => 'Testosterone Assessment', 'description' => 'Comprehensive testosterone health evaluation' ),
            'menopause' => array( 'title' => 'Menopause Assessment', 'description' => 'Menopause symptoms and hormone evaluation' ),
            'ed_treatment' => array( 'title' => 'ED Treatment Assessment', 'description' => 'Erectile dysfunction evaluation and treatment options' ),
            'weight_loss' => array( 'title' => 'Weight Loss Assessment', 'description' => 'Comprehensive weight management evaluation' ),
            'longevity' => array( 'title' => 'Longevity Assessment', 'description' => 'Longevity and healthy aging evaluation' ),
            'hair' => array( 'title' => 'Hair Health Assessment', 'description' => 'Hair loss and scalp health evaluation' ),
            'skin' => array( 'title' => 'Skin Health Assessment', 'description' => 'Skin health and anti-aging evaluation' ),
            'health' => array( 'title' => 'General Health Assessment', 'description' => 'Comprehensive health and wellness evaluation' ),
            'sleep' => array( 'title' => 'Sleep Assessment', 'description' => 'Sleep quality and disorders evaluation' ),
        );

        return rest_ensure_response( array( 'assessments' => $assessments ) );
    }

    /**
     * Get specific assessment
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_assessment( $request ) {
        $assessment_type = $request->get_param( 'type' );
        
        $assessment_config_file = ENNU_LIFE_PLUGIN_DIR . "includes/config/assessments/{$assessment_type}.php";
        
        if ( ! file_exists( $assessment_config_file ) ) {
            return new WP_Error( 'assessment_not_found', 'Assessment not found', array( 'status' => 404 ) );
        }

        $assessment_config = include $assessment_config_file;
        
        return rest_ensure_response( array( 'assessment' => $assessment_config ) );
    }

    /**
     * Submit assessment
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function submit_assessment( $request ) {
        $assessment_type = $request->get_param( 'type' );
        $form_data = $request->get_json_params();

        if ( empty( $form_data ) ) {
            return new WP_Error( 'invalid_data', 'No form data provided', array( 'status' => 400 ) );
        }

        if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
            $shortcodes = new ENNU_Assessment_Shortcodes();
            $result = $shortcodes->process_assessment_submission( $assessment_type, $form_data );
            
            if ( is_wp_error( $result ) ) {
                return $result;
            }

            return rest_ensure_response( array( 'result' => $result ) );
        }

        return new WP_Error( 'processing_error', 'Assessment processing not available', array( 'status' => 500 ) );
    }

    /**
     * Get user scores
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_user_scores( $request ) {
        $user_id = $request->get_param( 'id' );
        
        $scores = array(
            'life_score' => get_user_meta( $user_id, 'ennu_life_score', true ),
            'pillar_scores' => get_user_meta( $user_id, 'ennu_pillar_scores', true ),
            'assessment_scores' => array()
        );

        $assessment_types = array( 'testosterone', 'menopause', 'ed_treatment', 'weight_loss', 'longevity', 'hair', 'skin', 'health', 'sleep' );
        
        foreach ( $assessment_types as $type ) {
            $assessment_scores = get_user_meta( $user_id, "ennu_{$type}_scores", true );
            if ( ! empty( $assessment_scores ) ) {
                $scores['assessment_scores'][ $type ] = $assessment_scores;
            }
        }

        return rest_ensure_response( $scores );
    }

    /**
     * Get user biomarkers
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_user_biomarkers( $request ) {
        $user_id = $request->get_param( 'id' );
        
        $biomarkers = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
        if ( ! is_array( $biomarkers ) ) {
            $biomarkers = array();
        }

        $flagged_biomarkers = array();
        if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
            $flag_manager = new ENNU_Biomarker_Flag_Manager();
            $flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $user_id );
        }

        return rest_ensure_response( array(
            'biomarkers' => $biomarkers,
            'flagged' => $flagged_biomarkers
        ) );
    }

    /**
     * Update user biomarkers
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function update_user_biomarkers( $request ) {
        $user_id = $request->get_param( 'id' );
        $biomarker_data = $request->get_json_params();

        if ( empty( $biomarker_data ) ) {
            return new WP_Error( 'invalid_data', 'No biomarker data provided', array( 'status' => 400 ) );
        }

        $success = update_user_meta( $user_id, 'ennu_user_biomarkers', $biomarker_data );

        if ( $success ) {
            return rest_ensure_response( array( 'message' => 'Biomarkers updated successfully' ) );
        } else {
            return new WP_Error( 'update_failed', 'Failed to update biomarkers', array( 'status' => 500 ) );
        }
    }

    /**
     * Get user goals
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_user_goals( $request ) {
        $user_id = $request->get_param( 'id' );
        
        $goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $goal_progress = array();
        
        if ( class_exists( 'ENNU_Goal_Progression_Tracker' ) ) {
            $goal_progress = get_user_meta( $user_id, 'ennu_goal_progress_history', true );
        }

        return rest_ensure_response( array(
            'goals' => $goals ?: array(),
            'progress' => $goal_progress ?: array()
        ) );
    }

    /**
     * Update user goals
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function update_user_goals( $request ) {
        $user_id = $request->get_param( 'id' );
        $goals_data = $request->get_json_params();

        if ( empty( $goals_data ) ) {
            return new WP_Error( 'invalid_data', 'No goals data provided', array( 'status' => 400 ) );
        }

        $success = update_user_meta( $user_id, 'ennu_global_health_goals', $goals_data );

        if ( $success ) {
            return rest_ensure_response( array( 'message' => 'Goals updated successfully' ) );
        } else {
            return new WP_Error( 'update_failed', 'Failed to update goals', array( 'status' => 500 ) );
        }
    }

    /**
     * Flag biomarker
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function flag_biomarker( $request ) {
        $data = $request->get_json_params();
        
        if ( ! class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
            return new WP_Error( 'feature_unavailable', 'Biomarker flagging not available', array( 'status' => 500 ) );
        }

        $flag_manager = new ENNU_Biomarker_Flag_Manager();
        $success = $flag_manager->flag_biomarker( 
            $data['user_id'], 
            $data['biomarker_name'], 
            $data['flag_type'], 
            $data['reason'] 
        );

        if ( $success ) {
            return rest_ensure_response( array( 'message' => 'Biomarker flagged successfully' ) );
        } else {
            return new WP_Error( 'flag_failed', 'Failed to flag biomarker', array( 'status' => 500 ) );
        }
    }

    /**
     * Get user trends
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_user_trends( $request ) {
        $user_id = $request->get_param( 'id' );
        $time_range = $request->get_param( 'time_range' ) ?: 90;
        $category = $request->get_param( 'category' ) ?: 'all';

        if ( ! class_exists( 'ENNU_Trends_Visualization_System' ) ) {
            return new WP_Error( 'feature_unavailable', 'Trends visualization not available', array( 'status' => 500 ) );
        }

        $reflection = new ReflectionClass( 'ENNU_Trends_Visualization_System' );
        $method = $reflection->getMethod( 'get_comprehensive_trend_data' );
        $method->setAccessible( true );
        
        $trend_data = $method->invoke( null, $user_id, $time_range, $category );

        return rest_ensure_response( array( 'trends' => $trend_data ) );
    }

    /**
     * Get analytics overview
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public static function get_analytics_overview( $request ) {
        $analytics = array(
            'total_users' => count_users()['total_users'],
            'total_assessments' => self::get_total_assessments_count(),
            'active_users_30_days' => self::get_active_users_count( 30 ),
            'flagged_biomarkers' => self::get_flagged_biomarkers_count(),
            'medical_staff_count' => self::get_medical_staff_count(),
            'recent_activity' => self::get_recent_activity()
        );

        return rest_ensure_response( $analytics );
    }

    /**
     * Get total assessments count
     *
     * @return int Total assessments count
     */
    private static function get_total_assessments_count() {
        global $wpdb;
        
        $count = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_scores'
        " );

        return intval( $count );
    }

    /**
     * Get active users count
     *
     * @param int $days Number of days
     * @return int Active users count
     */
    private static function get_active_users_count( $days ) {
        global $wpdb;
        
        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        
        $count = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_last_activity' 
            AND meta_value >= %s
        ", $cutoff_date ) );

        return intval( $count );
    }

    /**
     * Get flagged biomarkers count
     *
     * @return int Flagged biomarkers count
     */
    private static function get_flagged_biomarkers_count() {
        global $wpdb;
        
        $count = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_flagged_biomarkers'
        " );

        return intval( $count );
    }

    /**
     * Get medical staff count
     *
     * @return int Medical staff count
     */
    private static function get_medical_staff_count() {
        if ( class_exists( 'ENNU_Medical_Role_Manager' ) ) {
            $medical_staff = ENNU_Medical_Role_Manager::get_medical_staff();
            return count( $medical_staff );
        }

        return 0;
    }

    /**
     * Get recent activity
     *
     * @return array Recent activity
     */
    private static function get_recent_activity() {
        if ( class_exists( 'ENNU_Medical_Role_Manager' ) ) {
            return ENNU_Medical_Role_Manager::get_medical_audit_log( 10 );
        }

        return array();
    }
}
