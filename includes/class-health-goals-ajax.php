<?php
/**
 * ENNU Life Health Goals AJAX Handler
 * Handles AJAX requests for health goals functionality with enhanced security
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Health_Goals_Ajax {
    
    public function __construct() {
        add_action( 'wp_ajax_ennu_save_health_goals', array( $this, 'handle_save_health_goals' ) );
        add_action( 'wp_ajax_ennu_get_health_goals', array( $this, 'handle_get_health_goals' ) );
    }
    
    public function handle_save_health_goals() {
        if ( ! ENNU_CSRF_Protection::verify_nonce( 'ennu_health_goals_nonce' ) ) {
            wp_die( 'Security check failed', 'Unauthorized', array( 'response' => 403 ) );
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
            return;
        }
        
        $user_id = get_current_user_id();
        $goals = ENNU_Input_Sanitizer::sanitize_array( $_POST['goals'] ?? array() );
        
        if ( ENNU_Data_Access_Control::can_user_modify_data( $user_id, 'health_goals' ) ) {
            update_user_meta( $user_id, 'ennu_global_health_goals', $goals );
            wp_send_json_success( array( 'message' => 'Health goals saved successfully' ) );
        } else {
            wp_send_json_error( 'Insufficient permissions' );
        }
    }
    
    public function handle_get_health_goals() {
        if ( ! ENNU_CSRF_Protection::verify_nonce( 'ennu_health_goals_nonce' ) ) {
            wp_die( 'Security check failed', 'Unauthorized', array( 'response' => 403 ) );
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
            return;
        }
        
        $user_id = get_current_user_id();
        
        if ( ENNU_Data_Access_Control::can_user_access_data( $user_id, 'health_goals' ) ) {
            $goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
            wp_send_json_success( array( 'goals' => $goals ?: array() ) );
        } else {
            wp_send_json_error( 'Insufficient permissions' );
        }
    }
    
    /**
     * Handle bulk health goals update
     */
    public function handle_update_health_goals() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_health_goals_nonce' ) ) {
            wp_send_json_error( array( 
                'message' => 'Security check failed',
                'code' => 'NONCE_FAILED'
            ) );
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 
                'message' => 'User not logged in',
                'code' => 'NOT_LOGGED_IN'
            ) );
        }
        
        $user_id = get_current_user_id();
        $new_goals = isset( $_POST['health_goals'] ) ? array_map( 'sanitize_text_field', $_POST['health_goals'] ) : array();
        
        $current_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $current_goals = is_array( $current_goals ) ? $current_goals : array();
        
        $allowed_goals = $this->get_allowed_health_goals();
        $validated_goals = array_intersect( $new_goals, array_keys( $allowed_goals ) );
        
        error_log( "ENNU Health Goals AJAX: User {$user_id} updating goals from [" . implode(', ', $current_goals) . "] to [" . implode(', ', $validated_goals) . "]" );
        
        $save_result = update_user_meta( $user_id, 'ennu_global_health_goals', $validated_goals );
        
        if ( $save_result !== false ) {
            $this->trigger_score_recalculation( $user_id );
            
            $added_goals = array_diff( $validated_goals, $current_goals );
            $removed_goals = array_diff( $current_goals, $validated_goals );
            
            wp_send_json_success( array(
                'message' => 'Health goals updated successfully',
                'goals' => $validated_goals,
                'goals_count' => count( $validated_goals ),
                'changes' => array(
                    'added' => $added_goals,
                        'goal_added' => __( 'Goal added successfully!', 'ennu-life' ),
                        'goal_removed' => __( 'Goal removed successfully!', 'ennu-life' ),
                        'network_error' => __( 'Network error occurred. Please check your connection.', 'ennu-life' ),
                    ),
                )
            );
        }
    }
    
    /**
     * Check if current page is the dashboard page
     */
    private function is_dashboard_page() {
        global $post;
        
        // Check if current post has the dashboard shortcode
        if ( $post && has_shortcode( $post->post_content, 'ennu-user-dashboard' ) ) {
            return true;
        }
        
        // Check if we're on a page that might have the shortcode (more flexible)
        if ( is_page() ) {
            // Get all pages with the dashboard shortcode
            $dashboard_pages = get_posts( array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_query' => array(),
                'posts_per_page' => -1,
                'suppress_filters' => false
            ) );
            
            foreach ( $dashboard_pages as $page ) {
                if ( has_shortcode( $page->post_content, 'ennu-user-dashboard' ) && $page->ID === get_the_ID() ) {
                    return true;
                }
            }
        }
        
        // Check if URL contains dashboard-related keywords
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if ( strpos( $current_url, 'dashboard' ) !== false || 
             strpos( $current_url, 'user-dashboard' ) !== false ||
             strpos( $current_url, 'ennu-dashboard' ) !== false ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle bulk health goals update
     */
    public function handle_update_health_goals() {
        ENNU_AJAX_Security::validate_ajax_request();
        
        // Security checks
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_health_goals_nonce' ) ) {
            wp_send_json_error( array( 
                'message' => 'Security check failed',
                'code' => 'NONCE_FAILED'
            ) );
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 
                'message' => 'User not logged in',
                'code' => 'NOT_LOGGED_IN'
            ) );
        }
        
        $user_id = get_current_user_id();
        $new_goals = isset( $_POST['health_goals'] ) ? array_map( 'sanitize_text_field', $_POST['health_goals'] ) : array();
        
        // Get current goals for comparison
        $current_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $current_goals = is_array( $current_goals ) ? $current_goals : array();
        
        // Validate goals against allowed options
        $allowed_goals = $this->get_allowed_health_goals();
        $validated_goals = array_intersect( $new_goals, array_keys( $allowed_goals ) );
        
        // Log the update attempt
        error_log( "ENNU Health Goals AJAX: User {$user_id} updating goals from [" . implode(', ', $current_goals) . "] to [" . implode(', ', $validated_goals) . "]" );
        
        // Save to user meta
        $save_result = update_user_meta( $user_id, 'ennu_global_health_goals', $validated_goals );
        
        if ( $save_result !== false ) {
            // Trigger score recalculation
            $this->trigger_score_recalculation( $user_id );
            
            // Calculate changes for user feedback
            $added_goals = array_diff( $validated_goals, $current_goals );
            $removed_goals = array_diff( $current_goals, $validated_goals );
            
            wp_send_json_success( array(
                'message' => 'Health goals updated successfully',
                'goals' => $validated_goals,
                'goals_count' => count( $validated_goals ),
                'changes' => array(
                    'added' => $added_goals,
                    'removed' => $removed_goals,
                    'total_changes' => count( $added_goals ) + count( $removed_goals ),
                ),
                'redirect_needed' => true,
                'allowed_goals' => array_keys( $allowed_goals ),
            ) );
        } else {
            error_log( "ENNU Health Goals AJAX: Failed to save goals for user {$user_id}" );
            wp_send_json_error( array( 
                'message' => 'Failed to save health goals',
                'code' => 'SAVE_FAILED'
            ) );
        }
    }
    
    /**
     * Handle single health goal toggle
     */
    public function handle_toggle_health_goal() {
        ENNU_AJAX_Security::validate_ajax_request();
        
        // Security checks
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_health_goals_nonce' ) ) {
            wp_send_json_error( array( 
                'message' => 'Security check failed',
                'code' => 'NONCE_FAILED'
            ) );
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 
                'message' => 'User not logged in',
                'code' => 'NOT_LOGGED_IN'
            ) );
        }
        
        $user_id = get_current_user_id();
        
        if ( empty( $_POST['goal'] ) || empty( $_POST['action'] ) ) {
            wp_send_json_error( array( 
                'message' => 'Missing required parameters',
                'code' => 'MISSING_PARAMS'
            ) );
        }
        
        $goal_to_toggle = sanitize_text_field( $_POST['goal'] );
        $action = sanitize_text_field( $_POST['action'] );
        
        // Validate goal format (alphanumeric with underscores only)
        if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $goal_to_toggle ) ) {
            wp_send_json_error( array( 
                'message' => 'Invalid goal format',
                'code' => 'INVALID_GOAL_FORMAT'
            ) );
        }
        
        // Validate action with strict whitelist
        if ( ! in_array( $action, array( 'add', 'remove' ), true ) ) {
            wp_send_json_error( array( 
                'message' => 'Invalid action specified',
                'code' => 'INVALID_ACTION'
            ) );
        }
        
        // Get current goals
        $current_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $current_goals = is_array( $current_goals ) ? $current_goals : array();
        
        // Validate goal
        $allowed_goals = $this->get_allowed_health_goals();
        if ( ! isset( $allowed_goals[$goal_to_toggle] ) ) {
            wp_send_json_error( array( 
                'message' => 'Invalid health goal',
                'code' => 'INVALID_GOAL',
                'goal' => $goal_to_toggle
            ) );
        }
        
        // Toggle goal
        $goals_changed = false;
        if ( $action === 'add' && ! in_array( $goal_to_toggle, $current_goals ) ) {
            $current_goals[] = $goal_to_toggle;
            $goals_changed = true;
        } elseif ( $action === 'remove' && in_array( $goal_to_toggle, $current_goals ) ) {
            $current_goals = array_diff( $current_goals, array( $goal_to_toggle ) );
            $goals_changed = true;
        }
        
        if ( ! $goals_changed ) {
            wp_send_json_error( array( 
                'message' => 'Goal was already in the requested state',
                'code' => 'NO_CHANGE_NEEDED',
                'goal' => $goal_to_toggle,
                'action' => $action
            ) );
        }
        
        // Save updated goals
        $save_result = update_user_meta( $user_id, 'ennu_global_health_goals', array_values( $current_goals ) );
        
        if ( $save_result !== false ) {
            error_log( "ENNU Health Goals AJAX: User {$user_id} {$action}ed goal '{$goal_to_toggle}'. Total goals: " . count( $current_goals ) );
            
            wp_send_json_success( array(
                'message' => $action === 'add' ? 'Goal added successfully' : 'Goal removed successfully',
                'goal' => $goal_to_toggle,
                'action' => $action,
                'total_goals' => count( $current_goals ),
                'current_goals' => array_values( $current_goals ),
                'goal_label' => $allowed_goals[$goal_to_toggle]['label'] ?? $goal_to_toggle,
            ) );
        } else {
            error_log( "ENNU Health Goals AJAX: Failed to {$action} goal '{$goal_to_toggle}' for user {$user_id}" );
            wp_send_json_error( array( 
                'message' => 'Failed to update health goal',
                'code' => 'SAVE_FAILED',
                'goal' => $goal_to_toggle,
                'action' => $action
            ) );
        }
    }
    
    /**
     * Get allowed health goals from system configuration
     */
    private function get_allowed_health_goals() {
        // Try health goals config first
        $health_goals_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
        if ( file_exists( $health_goals_config ) ) {
            $config = require $health_goals_config;
            if ( isset( $config['goal_definitions'] ) ) {
                return $config['goal_definitions'];
            }
        }
        
        // Fallback to welcome assessment options
        $welcome_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/welcome.php';
        if ( file_exists( $welcome_config ) ) {
            $config = require $welcome_config;
            if ( isset( $config['questions']['welcome_q3']['options'] ) ) {
                // Convert simple options to full definitions
                $simple_options = $config['questions']['welcome_q3']['options'];
                $full_definitions = array();
                foreach ( $simple_options as $key => $label ) {
                    $full_definitions[$key] = array(
                        'label' => $label,
                        'description' => $label,
                        'category' => 'General',
                    );
                }
                return $full_definitions;
            }
        }
        
        // Final fallback - default health goals
        return array(
            'longevity' => array(
                'label' => 'Longevity & Healthy Aging',
                'description' => 'Focus on extending healthy lifespan and aging gracefully',
                'category' => 'Wellness',
            ),
            'energy' => array(
                'label' => 'Improve Energy & Vitality',
                'description' => 'Boost daily energy levels and combat fatigue',
                'category' => 'Wellness',
            ),
            'strength' => array(
                'label' => 'Build Strength & Muscle',
                'description' => 'Build lean muscle mass and physical strength',
                'category' => 'Fitness',
            ),
            'libido' => array(
                'label' => 'Enhance Libido & Sexual Health',
                'description' => 'Enhance sexual health and performance',
                'category' => 'Men\'s Health',
            ),
            'weight_loss' => array(
                'label' => 'Achieve & Maintain Healthy Weight',
                'description' => 'Achieve and maintain a healthy weight',
                'category' => 'Fitness',
            ),
            'hormonal_balance' => array(
                'label' => 'Hormonal Balance',
                'description' => 'Optimize hormonal health and balance',
                'category' => 'Hormones',
            ),
            'cognitive_health' => array(
                'label' => 'Sharpen Cognitive Function',
                'description' => 'Sharpen memory and mental clarity',
                'category' => 'Mental Health',
            ),
            'heart_health' => array(
                'label' => 'Support Heart Health',
                'description' => 'Support cardiovascular health and function',
                'category' => 'Wellness',
            ),
            'aesthetics' => array(
                'label' => 'Improve Hair, Skin & Nails',
                'description' => 'Improve hair, skin, and overall appearance',
                'category' => 'Aesthetics',
            ),
            'sleep' => array(
                'label' => 'Improve Sleep Quality',
                'description' => 'Improve sleep quality and recovery',
                'category' => 'Wellness',
            ),
            'stress' => array(
                'label' => 'Reduce Stress & Improve Resilience',
                'description' => 'Reduce stress and improve resilience',
                'category' => 'Mental Health',
            ),
        );
    }
    
    /**
     * Trigger score recalculation after goals update
     */
    private function trigger_score_recalculation( $user_id ) {
        if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
            try {
                ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );
                error_log( "ENNU Health Goals AJAX: Successfully recalculated scores for user {$user_id}" );
            } catch ( Exception $e ) {
                error_log( "ENNU Health Goals AJAX: Error recalculating scores for user {$user_id}: " . $e->getMessage() );
            }
        } else {
            error_log( "ENNU Health Goals AJAX: ENNU_Assessment_Scoring class not found, skipping score recalculation" );
        }
    }
    
    /**
     * Get health goals statistics for admin
     */
    public function get_health_goals_stats() {
        global $wpdb;
        
        $stats = array(
            'total_users_with_goals' => 0,
            'most_popular_goals' => array(),
            'average_goals_per_user' => 0,
            'total_goal_selections' => 0,
        );
        
        // Get all users with health goals
        $users_with_goals = $wpdb->get_results( $wpdb->prepare("
            SELECT meta_value 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND meta_value != '' 
            AND meta_value != 'a:0:{}'
        ", 'ennu_global_health_goals' ) );
        
        $stats['total_users_with_goals'] = count( $users_with_goals );
        
        $goal_counts = array();
        $total_selections = 0;
        
        foreach ( $users_with_goals as $user_meta ) {
            $goals = maybe_unserialize( $user_meta->meta_value );
            if ( is_array( $goals ) ) {
                $total_selections += count( $goals );
                foreach ( $goals as $goal ) {
                    $goal_counts[$goal] = ( $goal_counts[$goal] ?? 0 ) + 1;
                }
            }
        }
        
        $stats['total_goal_selections'] = $total_selections;
        $stats['average_goals_per_user'] = $stats['total_users_with_goals'] > 0 
            ? round( $total_selections / $stats['total_users_with_goals'], 1 ) 
            : 0;
        
        // Sort goals by popularity
        arsort( $goal_counts );
        $stats['most_popular_goals'] = array_slice( $goal_counts, 0, 10, true );
        
        return $stats;
    }
}

// Initialize the AJAX handler
new ENNU_Health_Goals_Ajax();
        add_action( 'wp_ajax_nopriv_ennu_toggle_health_goal', array( $this, 'handle_toggle_health_goal' ) );
        
        // Enqueue scripts and localize AJAX data
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_health_goals_scripts' ) );
    }
    
    /**
     * Enqueue health goals AJAX scripts and data
     */
    public function enqueue_health_goals_scripts() {
        if ( is_user_logged_in() && $this->is_dashboard_page() ) {
            wp_enqueue_script(
                'ennu-health-goals-ajax',
                ENNU_LIFE_PLUGIN_URL . 'assets/js/health-goals-manager.js',
                array( 'jquery' ),
                ENNU_LIFE_VERSION,
                true
            );
            
            wp_localize_script(
                'ennu-health-goals-ajax',
                'ennuHealthGoalsAjax',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'ennu_health_goals_nonce' ),
                    'user_id' => get_current_user_id(),
                    'messages' => array(
                        'updating' => __( 'Updating your health goals...', 'ennu-life' ),
                        'success' => __( 'Your health goals have been updated successfully!', 'ennu-life' ),
                        'error' => __( 'Failed to update health goals. Please try again.', 'ennu-life' ),
                        'goal_added' => __( 'Goal added successfully!', 'ennu-life' ),
                        'goal_removed' => __( 'Goal removed successfully!', 'ennu-life' ),
                        'network_error' => __( 'Network error occurred. Please check your connection.', 'ennu-life' ),
                    ),
                )
            );
        }
    }
    
    /**
     * Check if current page is the dashboard page
     */
    private function is_dashboard_page() {
        global $post;
        
        // Check if current post has the dashboard shortcode
        if ( $post && has_shortcode( $post->post_content, 'ennu-user-dashboard' ) ) {
            return true;
        }
        
        // Check if we're on a page that might have the shortcode (more flexible)
        if ( is_page() ) {
            // Get all pages with the dashboard shortcode
            $dashboard_pages = get_posts( array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_query' => array(),
                'posts_per_page' => -1,
                'suppress_filters' => false
            ) );
            
            foreach ( $dashboard_pages as $page ) {
                if ( has_shortcode( $page->post_content, 'ennu-user-dashboard' ) && $page->ID === get_the_ID() ) {
                    return true;
                }
            }
        }
        
        // Check if URL contains dashboard-related keywords
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if ( strpos( $current_url, 'dashboard' ) !== false || 
             strpos( $current_url, 'user-dashboard' ) !== false ||
             strpos( $current_url, 'ennu-dashboard' ) !== false ) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Handle bulk health goals update
     */
    public function handle_update_health_goals() {
        // Security checks
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_health_goals_nonce' ) ) {
            wp_send_json_error( array( 
                'message' => 'Security check failed',
                'code' => 'NONCE_FAILED'
            ) );
        }
        
        $security_validator = ENNU_Security_Validator::get_instance();
        if ( ! $security_validator->rate_limit_check( 'update_health_goals', 10, 300 ) ) {
            wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
            return;
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 
                'message' => 'User not logged in',
                'code' => 'NOT_LOGGED_IN'
            ) );
        }
        
        $user_id = get_current_user_id();
        $new_goals = isset( $_POST['health_goals'] ) ? array_map( 'sanitize_text_field', $_POST['health_goals'] ) : array();
        
        // Get current goals for comparison
        $current_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $current_goals = is_array( $current_goals ) ? $current_goals : array();
        
        // Validate goals against allowed options
        $allowed_goals = $this->get_allowed_health_goals();
        $validated_goals = array_intersect( $new_goals, array_keys( $allowed_goals ) );
        
        // Log the update attempt
        error_log( "ENNU Health Goals AJAX: User {$user_id} updating goals from [" . implode(', ', $current_goals) . "] to [" . implode(', ', $validated_goals) . "]" );
        
        // Save to user meta
        $save_result = update_user_meta( $user_id, 'ennu_global_health_goals', $validated_goals );
        
        if ( $save_result !== false ) {
            // Trigger score recalculation
            $this->trigger_score_recalculation( $user_id );
            
            // Calculate changes for user feedback
            $added_goals = array_diff( $validated_goals, $current_goals );
            $removed_goals = array_diff( $current_goals, $validated_goals );
            
            wp_send_json_success( array(
                'message' => 'Health goals updated successfully',
                'goals' => $validated_goals,
                'goals_count' => count( $validated_goals ),
                'changes' => array(
                    'added' => $added_goals,
                    'removed' => $removed_goals,
                    'total_changes' => count( $added_goals ) + count( $removed_goals ),
                ),
                'redirect_needed' => true,
                'allowed_goals' => array_keys( $allowed_goals ),
            ) );
        } else {
            error_log( "ENNU Health Goals AJAX: Failed to save goals for user {$user_id}" );
            wp_send_json_error( array( 
                'message' => 'Failed to save health goals',
                'code' => 'SAVE_FAILED'
            ) );
        }
    }
    
    /**
     * Handle single health goal toggle
     */
    public function handle_toggle_health_goal() {
        // Security checks
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_health_goals_nonce' ) ) {
            wp_send_json_error( array( 
                'message' => 'Security check failed',
                'code' => 'NONCE_FAILED'
            ) );
        }
        
        $security_validator = ENNU_Security_Validator::get_instance();
        if ( ! $security_validator->rate_limit_check( 'toggle_health_goal', 20, 300 ) ) {
            wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
            return;
        }
        
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 
                'message' => 'User not logged in',
                'code' => 'NOT_LOGGED_IN'
            ) );
        }
        
        $user_id = get_current_user_id();
        $goal_to_toggle = sanitize_text_field( $_POST['goal'] );
        $action = sanitize_text_field( $_POST['action'] ); // 'add' or 'remove'
        
        // Validate action
        if ( ! in_array( $action, array( 'add', 'remove' ) ) ) {
            wp_send_json_error( array( 
                'message' => 'Invalid action specified',
                'code' => 'INVALID_ACTION'
            ) );
        }
        
        // Get current goals
        $current_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $current_goals = is_array( $current_goals ) ? $current_goals : array();
        
        // Validate goal
        $allowed_goals = $this->get_allowed_health_goals();
        if ( ! isset( $allowed_goals[$goal_to_toggle] ) ) {
            wp_send_json_error( array( 
                'message' => 'Invalid health goal',
                'code' => 'INVALID_GOAL',
                'goal' => $goal_to_toggle
            ) );
        }
        
        // Toggle goal
        $goals_changed = false;
        if ( $action === 'add' && ! in_array( $goal_to_toggle, $current_goals ) ) {
            $current_goals[] = $goal_to_toggle;
            $goals_changed = true;
        } elseif ( $action === 'remove' && in_array( $goal_to_toggle, $current_goals ) ) {
            $current_goals = array_diff( $current_goals, array( $goal_to_toggle ) );
            $goals_changed = true;
        }
        
        if ( ! $goals_changed ) {
            wp_send_json_error( array( 
                'message' => 'Goal was already in the requested state',
                'code' => 'NO_CHANGE_NEEDED',
                'goal' => $goal_to_toggle,
                'action' => $action
            ) );
        }
        
        // Save updated goals
        $save_result = update_user_meta( $user_id, 'ennu_global_health_goals', array_values( $current_goals ) );
        
        if ( $save_result !== false ) {
            error_log( "ENNU Health Goals AJAX: User {$user_id} {$action}ed goal '{$goal_to_toggle}'. Total goals: " . count( $current_goals ) );
            
            wp_send_json_success( array(
                'message' => $action === 'add' ? 'Goal added successfully' : 'Goal removed successfully',
                'goal' => $goal_to_toggle,
                'action' => $action,
                'total_goals' => count( $current_goals ),
                'current_goals' => array_values( $current_goals ),
                'goal_label' => $allowed_goals[$goal_to_toggle]['label'] ?? $goal_to_toggle,
            ) );
        } else {
            error_log( "ENNU Health Goals AJAX: Failed to {$action} goal '{$goal_to_toggle}' for user {$user_id}" );
            wp_send_json_error( array( 
                'message' => 'Failed to update health goal',
                'code' => 'SAVE_FAILED',
                'goal' => $goal_to_toggle,
                'action' => $action
            ) );
        }
    }
    
    /**
     * Get allowed health goals from system configuration
     */
    private function get_allowed_health_goals() {
        // Try health goals config first
        $health_goals_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
        if ( file_exists( $health_goals_config ) ) {
            $config = require $health_goals_config;
            if ( isset( $config['goal_definitions'] ) ) {
                return $config['goal_definitions'];
            }
        }
        
        // Fallback to welcome assessment options
        $welcome_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/welcome.php';
        if ( file_exists( $welcome_config ) ) {
            $config = require $welcome_config;
            if ( isset( $config['questions']['welcome_q3']['options'] ) ) {
                // Convert simple options to full definitions
                $simple_options = $config['questions']['welcome_q3']['options'];
                $full_definitions = array();
                foreach ( $simple_options as $key => $label ) {
                    $full_definitions[$key] = array(
                        'label' => $label,
                        'description' => $label,
                        'category' => 'General',
                    );
                }
                return $full_definitions;
            }
        }
        
        // Final fallback - default health goals
        return array(
            'longevity' => array(
                'label' => 'Longevity & Healthy Aging',
                'description' => 'Focus on extending healthy lifespan and aging gracefully',
                'category' => 'Wellness',
            ),
            'energy' => array(
                'label' => 'Improve Energy & Vitality',
                'description' => 'Boost daily energy levels and combat fatigue',
                'category' => 'Wellness',
            ),
            'strength' => array(
                'label' => 'Build Strength & Muscle',
                'description' => 'Build lean muscle mass and physical strength',
                'category' => 'Fitness',
            ),
            'libido' => array(
                'label' => 'Enhance Libido & Sexual Health',
                'description' => 'Enhance sexual health and performance',
                'category' => 'Men\'s Health',
            ),
            'weight_loss' => array(
                'label' => 'Achieve & Maintain Healthy Weight',
                'description' => 'Achieve and maintain a healthy weight',
                'category' => 'Fitness',
            ),
            'hormonal_balance' => array(
                'label' => 'Hormonal Balance',
                'description' => 'Optimize hormonal health and balance',
                'category' => 'Hormones',
            ),
            'cognitive_health' => array(
                'label' => 'Sharpen Cognitive Function',
                'description' => 'Sharpen memory and mental clarity',
                'category' => 'Mental Health',
            ),
            'heart_health' => array(
                'label' => 'Support Heart Health',
                'description' => 'Support cardiovascular health and function',
                'category' => 'Wellness',
            ),
            'aesthetics' => array(
                'label' => 'Improve Hair, Skin & Nails',
                'description' => 'Improve hair, skin, and overall appearance',
                'category' => 'Aesthetics',
            ),
            'sleep' => array(
                'label' => 'Improve Sleep Quality',
                'description' => 'Improve sleep quality and recovery',
                'category' => 'Wellness',
            ),
            'stress' => array(
                'label' => 'Reduce Stress & Improve Resilience',
                'description' => 'Reduce stress and improve resilience',
                'category' => 'Mental Health',
            ),
        );
    }
    
    /**
     * Trigger score recalculation after goals update
     */
    private function trigger_score_recalculation( $user_id ) {
        if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
            try {
                ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );
                error_log( "ENNU Health Goals AJAX: Successfully recalculated scores for user {$user_id}" );
            } catch ( Exception $e ) {
                error_log( "ENNU Health Goals AJAX: Error recalculating scores for user {$user_id}: " . $e->getMessage() );
            }
        } else {
            error_log( "ENNU Health Goals AJAX: ENNU_Assessment_Scoring class not found, skipping score recalculation" );
        }
    }
    
    /**
     * Get health goals statistics for admin
     */
    public function get_health_goals_stats() {
        global $wpdb;
        
        $stats = array(
            'total_users_with_goals' => 0,
            'most_popular_goals' => array(),
            'average_goals_per_user' => 0,
            'total_goal_selections' => 0,
        );
        
        // Get all users with health goals
        $users_with_goals = $wpdb->get_results( $wpdb->prepare("
            SELECT meta_value 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = %s 
            AND meta_value != '' 
            AND meta_value != 'a:0:{}'
        ", 'ennu_global_health_goals' ) );
        
        $stats['total_users_with_goals'] = count( $users_with_goals );
        
        $goal_counts = array();
        $total_selections = 0;
        
        foreach ( $users_with_goals as $user_meta ) {
            $goals = maybe_unserialize( $user_meta->meta_value );
            if ( is_array( $goals ) ) {
                $total_selections += count( $goals );
                foreach ( $goals as $goal ) {
                    $goal_counts[$goal] = ( $goal_counts[$goal] ?? 0 ) + 1;
                }
            }
        }
        
        $stats['total_goal_selections'] = $total_selections;
        $stats['average_goals_per_user'] = $stats['total_users_with_goals'] > 0 
            ? round( $total_selections / $stats['total_users_with_goals'], 1 ) 
            : 0;
        
        // Sort goals by popularity
        arsort( $goal_counts );
        $stats['most_popular_goals'] = array_slice( $goal_counts, 0, 10, true );
        
        return $stats;
    }
    
    private function get_allowed_health_goals() {
        return array(
            'weight_loss' => 'Weight Loss',
            'muscle_gain' => 'Muscle Gain',
            'energy_boost' => 'Energy Boost',
            'better_sleep' => 'Better Sleep',
            'stress_reduction' => 'Stress Reduction',
            'improved_focus' => 'Improved Focus',
            'hormone_balance' => 'Hormone Balance',
            'heart_health' => 'Heart Health',
            'immune_support' => 'Immune Support',
            'longevity' => 'Longevity'
        );
    }
    
    private function trigger_score_recalculation( $user_id ) {
        if ( class_exists( 'ENNU_Scoring_System' ) ) {
            ENNU_Scoring_System::calculate_and_save_all_user_scores( $user_id );
        }
    }
}     