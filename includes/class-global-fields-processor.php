<?php
/**
 * ENNU Global Fields Processor
 * 
 * Automatically processes global fields from assessment submissions
 * and updates user meta and dashboard data
 * 
 * @package ENNU_Life_Assessments
 * @since 62.27.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Global_Fields_Processor {
    
    /**
     * Global field mappings
     * Using native WordPress user fields where possible
     */
    private static $global_field_mappings = array(
        // Native WordPress fields
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'email' => 'user_email',
        'billing_phone' => 'billing_phone',
        'phone' => 'billing_phone',
        
        // Custom global fields
        'date_of_birth' => 'ennu_global_date_of_birth',
        'dob' => 'ennu_global_date_of_birth',
        'birth_date' => 'ennu_global_date_of_birth',
        'gender' => 'ennu_global_gender',
        'sex' => 'ennu_global_gender',
        'height' => 'ennu_global_height_weight',
        'weight' => 'ennu_global_height_weight',
        'height_weight' => 'ennu_global_height_weight',
        'health_goals' => 'ennu_global_health_goals',
        'goals' => 'ennu_global_health_goals',
        'age' => 'ennu_global_age'
    );
    
    /**
     * Initialize the global fields processor
     */
    public static function init() {
        add_action( 'ennu_assessment_completed', array( __CLASS__, 'process_global_fields' ), 10, 2 );
        // Removed AJAX hooks to prevent interference with main AJAX handler
        // Global fields are now processed by the main AJAX handler
    }
    
    /**
     * Process global fields from assessment completion
     */
    public static function process_global_fields( $user_id, $assessment_data ) {
        if ( empty( $assessment_data['form_data'] ) ) {
            return;
        }
        
        $form_data = $assessment_data['form_data'];
        self::process_form_data( $user_id, $form_data );
    }
    
    /**
     * Process global fields from AJAX submission
     */
    public static function process_global_fields_from_ajax() {
        if ( ! isset( $_POST['user_id'] ) && ! isset( $_POST['email'] ) ) {
            return;
        }
        
        $user_id = self::get_user_id_from_request();
        if ( ! $user_id ) {
            return;
        }
        
        self::process_form_data( $user_id, $_POST );
    }
    
    /**
     * Process form data and extract global fields
     */
    public static function process_form_data( $user_id, $form_data ) {
        if ( ! is_array( $form_data ) ) {
            return;
        }
        
        $global_fields_updated = false;
        
        // Process each form field
        foreach ( $form_data as $field_name => $field_value ) {
            $global_field_key = self::get_global_field_key( $field_name );
            if ( $global_field_key ) {
                $processed_value = self::process_field_value( $user_id, $field_name, $field_value, $form_data );
                if ( $processed_value !== false ) {
                    update_user_meta( $user_id, $global_field_key, $processed_value );
                    $global_fields_updated = true;
                    
                    // Log the update
                    // REMOVED: error_log( "ENNU Global Fields: Updated {$global_field_key} for user {$user_id} with value: " . print_r( $processed_value, true ) );
                }
            }
        }
        
        // Process global fields from assessment definitions
        if ( isset( $form_data['assessment_type'] ) ) {
            $assessment_global_fields_updated = self::process_assessment_global_fields( $user_id, $form_data );
            if ( $assessment_global_fields_updated ) {
                $global_fields_updated = true;
            }
        }
        
        // If global fields were updated, refresh dashboard data
        if ( $global_fields_updated ) {
            self::refresh_dashboard_data( $user_id );
        }
    }
    
    /**
     * Process global fields from assessment definitions
     */
    private static function process_assessment_global_fields( $user_id, $form_data ) {
        $assessment_type = $form_data['assessment_type'];
        $global_fields_updated = false;
        
        // Get assessment questions from shortcodes class
        if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
            $shortcodes = new ENNU_Assessment_Shortcodes();
            $questions = $shortcodes->get_assessment_questions( $assessment_type );
            
            if ( is_array( $questions ) ) {
                foreach ( $questions as $question_id => $question_def ) {
                    if ( isset( $question_def['global_key'] ) ) {
                        $meta_key = 'ennu_global_' . $question_def['global_key'];
                        $value_to_save = null;
                        
                        // Process based on question type and form data
                        switch ( $question_def['type'] ) {
                            case 'dob_dropdowns':
                                // Handle DOB dropdowns
                                if ( isset( $form_data['ennu_global_date_of_birth'] ) && ! empty( $form_data['ennu_global_date_of_birth'] ) ) {
                                    $value_to_save = sanitize_text_field( $form_data['ennu_global_date_of_birth'] );
                                } elseif ( isset( $form_data['ennu_global_date_of_birth_month'], $form_data['ennu_global_date_of_birth_day'], $form_data['ennu_global_date_of_birth_year'] ) ) {
                                    $value_to_save = $form_data['ennu_global_date_of_birth_year'] . '-' . $form_data['ennu_global_date_of_birth_month'] . '-' . $form_data['ennu_global_date_of_birth_day'];
                                }
                                break;
                                
                            case 'height_weight':
                                // Handle height/weight fields
                                if ( isset( $form_data['height_ft'], $form_data['height_in'], $form_data['weight_lbs'] ) ) {
                                    $value_to_save = array(
                                        'ft' => intval( $form_data['height_ft'] ),
                                        'in' => intval( $form_data['height_in'] ),
                                        'weight' => floatval( $form_data['weight_lbs'] )
                                    );
                                }
                                break;
                                
                            default:
                                // Handle standard fields
                                if ( isset( $form_data[ $question_id ] ) ) {
                                    $value_to_save = $form_data[ $question_id ];
                                }
                                break;
                        }
                        
                        // Save the global field if we have a value
                        if ( $value_to_save !== null ) {
                            update_user_meta( $user_id, $meta_key, $value_to_save );
                            $global_fields_updated = true;
                            
                            // Log the update
                            // REMOVED: error_log( "ENNU Global Fields: Updated {$meta_key} for user {$user_id} from assessment {$assessment_type} with value: " . print_r( $value_to_save, true ) );
                        }
                    }
                }
            }
        }
        
        // Also process height/weight directly from form data if not handled by assessment definitions
        if ( isset( $form_data['height_ft'], $form_data['height_in'], $form_data['weight_lbs'] ) ) {
            $height_weight = array(
                'ft' => intval( $form_data['height_ft'] ),
                'in' => intval( $form_data['height_in'] ),
                'weight' => floatval( $form_data['weight_lbs'] )
            );
            update_user_meta( $user_id, 'ennu_global_height_weight', $height_weight );
            $global_fields_updated = true;
            
            // Log the update
            // REMOVED: error_log( "ENNU Global Fields: Updated ennu_global_height_weight for user {$user_id} with value: " . print_r( $height_weight, true ) );
        }
        
        return $global_fields_updated;
    }
    
    /**
     * Get global field key from field name
     */
    private static function get_global_field_key( $field_name ) {
        // Direct mapping
        if ( isset( self::$global_field_mappings[ $field_name ] ) ) {
            return self::$global_field_mappings[ $field_name ];
        }
        
        // Check for field names that start with global keys
        foreach ( self::$global_field_mappings as $key => $global_key ) {
            if ( strpos( $field_name, $key ) !== false ) {
                return $global_key;
            }
        }
        
        return false;
    }
    
    /**
     * Process field value based on field type
     */
    private static function process_field_value( $user_id, $field_name, $field_value, $form_data ) {
        // Skip empty values
        if ( empty( $field_value ) && $field_value !== '0' ) {
            return false;
        }
        
        // Process date of birth
        if ( in_array( $field_name, array( 'date_of_birth', 'dob', 'birth_date' ) ) ) {
            return self::process_date_of_birth( $field_value );
        }
        
        // Process gender
        if ( in_array( $field_name, array( 'gender', 'sex' ) ) ) {
            return self::process_gender( $field_value );
        }
        
        // Process height and weight
        if ( in_array( $field_name, array( 'height', 'weight', 'height_weight' ) ) ) {
            // Get user_id from form_data or use the one passed to process_form_data
            $user_id = isset( $form_data['user_id'] ) ? $form_data['user_id'] : get_current_user_id();
            return self::process_height_weight( $user_id, $field_name, $field_value, $form_data );
        }
        
        // Process health goals
        if ( in_array( $field_name, array( 'health_goals', 'goals' ) ) ) {
            return self::process_health_goals( $field_value );
        }
        
        // Process basic fields
        if ( in_array( $field_name, array( 'first_name', 'last_name', 'email', 'phone', 'billing_phone' ) ) ) {
            return sanitize_text_field( $field_value );
        }
        
        return sanitize_text_field( $field_value );
    }
    
    /**
     * Process date of birth
     */
    private static function process_date_of_birth( $value ) {
        // Handle various date formats
        $date_formats = array( 'Y-m-d', 'm/d/Y', 'd/m/Y', 'Y/m/d' );
        
        foreach ( $date_formats as $format ) {
            $date = DateTime::createFromFormat( $format, $value );
            if ( $date !== false ) {
                return $date->format( 'Y-m-d' );
            }
        }
        
        // If no valid format found, try to parse it
        $timestamp = strtotime( $value );
        if ( $timestamp !== false ) {
            return date( 'Y-m-d', $timestamp );
        }
        
        return false;
    }
    
    /**
     * Process gender
     */
    private static function process_gender( $value ) {
        $value = strtolower( trim( $value ) );
        
        $gender_mappings = array(
            'male' => 'male',
            'MALE' => 'male',
            'm' => 'male',
            'man' => 'male',
            'female' => 'female',
            'FEMALE' => 'female',
            'f' => 'female',
            'woman' => 'female'
        );
        
        return isset( $gender_mappings[ $value ] ) ? $gender_mappings[ $value ] : 'female';
    }
    
    /**
     * Process height and weight
     */
    private static function process_height_weight( $user_id, $field_name, $field_value, $form_data ) {
        $height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
        if ( ! is_array( $height_weight ) ) {
            $height_weight = array();
        }
        
        if ( $field_name === 'height' || strpos( $field_name, 'height' ) !== false ) {
            // Handle height in various formats
            if ( is_array( $field_value ) ) {
                $height_weight['ft'] = isset( $field_value['ft'] ) ? intval( $field_value['ft'] ) : 0;
                $height_weight['in'] = isset( $field_value['in'] ) ? intval( $field_value['in'] ) : 0;
            } else {
                // Try to parse height string
                $height_weight = self::parse_height_string( $field_value, $height_weight );
            }
        }
        
        if ( $field_name === 'weight' || strpos( $field_name, 'weight' ) !== false ) {
            $height_weight['weight'] = floatval( $field_value );
        }
        
        return $height_weight;
    }
    
    /**
     * Parse height string
     */
    private static function parse_height_string( $height_string, $height_weight ) {
        // Handle formats like "5'10"", "5 feet 10 inches", "5 ft 10 in"
        $height_string = strtolower( trim( $height_string ) );
        
        // Pattern for "5'10"" format
        if ( preg_match( '/(\d+)\'(\d+)\"/', $height_string, $matches ) ) {
            $height_weight['ft'] = intval( $matches[1] );
            $height_weight['in'] = intval( $matches[2] );
            return $height_weight;
        }
        
        // Pattern for "5 feet 10 inches" format
        if ( preg_match( '/(\d+)\s*(?:feet?|ft)\s*(\d+)\s*(?:inches?|in)/', $height_string, $matches ) ) {
            $height_weight['ft'] = intval( $matches[1] );
            $height_weight['in'] = intval( $matches[2] );
            return $height_weight;
        }
        
        // Pattern for "5 ft 10 in" format
        if ( preg_match( '/(\d+)\s*ft\s*(\d+)\s*in/', $height_string, $matches ) ) {
            $height_weight['ft'] = intval( $matches[1] );
            $height_weight['in'] = intval( $matches[2] );
            return $height_weight;
        }
        
        return $height_weight;
    }
    
    /**
     * Process health goals
     */
    private static function process_health_goals( $value ) {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }
        
        if ( is_string( $value ) ) {
            // Handle comma-separated values
            $goals = array_map( 'trim', explode( ',', $value ) );
            return array_map( 'sanitize_text_field', $goals );
        }
        
        return array();
    }
    
    /**
     * Get user ID from request
     */
    private static function get_user_id_from_request() {
        if ( isset( $_POST['user_id'] ) ) {
            return intval( $_POST['user_id'] );
        }
        
        if ( isset( $_POST['email'] ) ) {
            $user = get_user_by( 'email', sanitize_email( $_POST['email'] ) );
            return $user ? $user->ID : false;
        }
        
        return get_current_user_id();
    }
    
    /**
     * Refresh dashboard data after global fields update
     */
    private static function refresh_dashboard_data( $user_id ) {
        // Get current global fields
        $dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
        $gender = get_user_meta( $user_id, 'ennu_global_gender', true );
        $height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
        $health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        
        // Calculate age if DOB is available
        $age = null;
        if ( ! empty( $dob ) ) {
            $birth_date = new DateTime( $dob );
            $today = new DateTime();
            $age = $today->diff( $birth_date )->y;
        }
        
        // Prepare dashboard data
        $dashboard_data = array(
            'age' => $age,
            'gender' => $gender,
            'height_weight' => $height_weight,
            'health_goals' => $health_goals,
            'last_updated' => current_time( 'mysql' )
        );
        
        // Save dashboard data
        update_user_meta( $user_id, 'ennu_user_dashboard_data', $dashboard_data );
        
        // Log the dashboard refresh
        // REMOVED: error_log( "ENNU Global Fields: Refreshed dashboard data for user {$user_id}" );
        
        // Trigger action for other systems
        do_action( 'ennu_global_fields_updated', $user_id, $dashboard_data );
    }
    
    /**
     * Get all global fields for a user
     */
    public static function get_user_global_fields( $user_id ) {
        return array(
            'date_of_birth' => get_user_meta( $user_id, 'ennu_global_date_of_birth', true ),
            'gender' => get_user_meta( $user_id, 'ennu_global_gender', true ),
            'height_weight' => get_user_meta( $user_id, 'ennu_global_height_weight', true ),
            'health_goals' => get_user_meta( $user_id, 'ennu_global_health_goals', true ),
            'first_name' => get_user_meta( $user_id, 'ennu_global_first_name', true ),
            'last_name' => get_user_meta( $user_id, 'ennu_global_last_name', true ),
            'email' => get_user_meta( $user_id, 'ennu_global_email', true ),
            'phone' => get_user_meta( $user_id, 'ennu_global_billing_phone', true )
        );
    }
    
    /**
     * Check if user has global fields
     */
    public static function user_has_global_fields( $user_id ) {
        $global_fields = self::get_user_global_fields( $user_id );
        return ! empty( array_filter( $global_fields ) );
    }
} 