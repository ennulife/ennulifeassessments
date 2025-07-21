<?php
/**
 * ENNU Progressive Data Collector
 * Extends Smart Recommendation Engine for progressive data collection
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-smart-recommendation-engine.php';

class ENNU_Progressive_Data_Collector extends ENNU_Smart_Recommendation_Engine {

    /**
     * Initialize progressive data collection hooks
     */
    public static function init() {
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        add_action( 'wp_ajax_ennu_save_progressive_data', array( __CLASS__, 'handle_progressive_data_save' ) );
        add_action( 'wp_ajax_nopriv_ennu_save_progressive_data', array( __CLASS__, 'handle_progressive_data_save' ) );
        add_action( 'wp_ajax_ennu_get_next_questions', array( __CLASS__, 'handle_get_next_questions' ) );
        add_action( 'wp_ajax_nopriv_ennu_get_next_questions', array( __CLASS__, 'handle_get_next_questions' ) );
    }

    /**
     * Enqueue progressive data collection scripts
     */
    public static function enqueue_scripts() {
        wp_enqueue_script(
            'ennu-progressive-data-collector',
            plugin_dir_url( __FILE__ ) . '../assets/js/progressive-data-collector.js',
            array( 'jquery' ),
            '62.2.8',
            true
        );

        wp_localize_script( 'ennu-progressive-data-collector', 'ennuProgressiveData', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ennu_progressive_data_nonce' ),
            'strings' => array(
                'saving' => __( 'Saving...', 'ennu-life' ),
                'saved' => __( 'Saved!', 'ennu-life' ),
                'error' => __( 'Error saving data', 'ennu-life' ),
                'loading' => __( 'Loading next questions...', 'ennu-life' )
            )
        ) );
    }

    /**
     * Handle progressive data save via AJAX
     */
    public static function handle_progressive_data_save() {
        check_ajax_referer( 'ennu_progressive_data_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => 'User not logged in' ) );
        }

        $field_name = sanitize_text_field( $_POST['field_name'] ?? '' );
        $field_value = sanitize_text_field( $_POST['field_value'] ?? '' );
        $assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? '' );

        if ( empty( $field_name ) ) {
            wp_send_json_error( array( 'message' => 'Field name required' ) );
        }

        $result = self::save_progressive_field( $user_id, $field_name, $field_value, $assessment_type );

        if ( $result ) {
            $next_questions = self::get_next_recommended_questions( $user_id, $assessment_type );
            
            wp_send_json_success( array(
                'message' => 'Data saved successfully',
                'next_questions' => $next_questions,
                'completeness' => self::calculate_section_completeness( $user_id, $assessment_type )
            ) );
        } else {
            wp_send_json_error( array( 'message' => 'Failed to save data' ) );
        }
    }

    /**
     * Handle get next questions via AJAX
     */
    public static function handle_get_next_questions() {
        check_ajax_referer( 'ennu_progressive_data_nonce', 'nonce' );

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => 'User not logged in' ) );
        }

        $assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? '' );
        $current_section = sanitize_text_field( $_POST['current_section'] ?? '' );

        $next_questions = self::get_next_recommended_questions( $user_id, $assessment_type, $current_section );

        wp_send_json_success( array(
            'questions' => $next_questions,
            'completeness' => self::calculate_section_completeness( $user_id, $assessment_type )
        ) );
    }

    /**
     * Save progressive field data
     *
     * @param int $user_id User ID
     * @param string $field_name Field name
     * @param mixed $field_value Field value
     * @param string $assessment_type Assessment type
     * @return bool Success status
     */
    private static function save_progressive_field( $user_id, $field_name, $field_value, $assessment_type ) {
        $allowed_fields = self::get_allowed_progressive_fields();
        
        if ( ! in_array( $field_name, $allowed_fields, true ) ) {
            return false;
        }

        $meta_key = 'ennu_' . $field_name;
        $result = update_user_meta( $user_id, $meta_key, $field_value );

        self::log_progressive_data_collection( $user_id, $field_name, $assessment_type );

        do_action( 'ennu_progressive_data_updated', $user_id, $field_name, $field_value, $assessment_type );

        return $result !== false;
    }

    /**
     * Get allowed progressive fields
     *
     * @return array Allowed field names
     */
    private static function get_allowed_progressive_fields() {
        return array(
            'age',
            'gender',
            'height',
            'weight',
            'activity_level',
            'sleep_hours',
            'stress_level',
            'health_goals',
            'medical_conditions',
            'medications',
            'supplements',
            'exercise_frequency',
            'diet_type',
            'smoking_status',
            'alcohol_consumption',
            'family_history'
        );
    }

    /**
     * Get next recommended questions based on user data
     *
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @param string $current_section Current section
     * @return array Next recommended questions
     */
    private static function get_next_recommended_questions( $user_id, $assessment_type = '', $current_section = '' ) {
        $user_data = self::get_user_progressive_data( $user_id );
        $question_flow = self::get_progressive_question_flow();
        
        $next_questions = array();
        $max_questions = 3; // Limit to 3 questions at a time
        
        foreach ( $question_flow as $section => $questions ) {
            if ( count( $next_questions ) >= $max_questions ) {
                break;
            }
            
            foreach ( $questions as $question_key => $question_config ) {
                if ( count( $next_questions ) >= $max_questions ) {
                    break;
                }
                
                if ( isset( $user_data[ $question_key ] ) && ! empty( $user_data[ $question_key ] ) ) {
                    continue;
                }
                
                if ( ! empty( $assessment_type ) && ! self::is_question_relevant( $question_key, $assessment_type ) ) {
                    continue;
                }
                
                if ( ! self::check_question_dependencies( $question_config, $user_data ) ) {
                    continue;
                }
                
                $next_questions[] = array(
                    'key' => $question_key,
                    'section' => $section,
                    'title' => $question_config['title'],
                    'type' => $question_config['type'],
                    'options' => $question_config['options'] ?? array(),
                    'priority' => $question_config['priority'] ?? 'medium',
                    'estimated_time' => $question_config['estimated_time'] ?? '30 seconds'
                );
            }
        }
        
        usort( $next_questions, function( $a, $b ) {
            $priority_order = array( 'critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4 );
            return $priority_order[ $a['priority'] ] - $priority_order[ $b['priority'] ];
        });
        
        return $next_questions;
    }

    /**
     * Get progressive question flow configuration
     *
     * @return array Question flow configuration
     */
    private static function get_progressive_question_flow() {
        return array(
            'basic_demographics' => array(
                'age' => array(
                    'title' => 'What is your age?',
                    'type' => 'number',
                    'priority' => 'critical',
                    'estimated_time' => '15 seconds'
                ),
                'gender' => array(
                    'title' => 'What is your gender?',
                    'type' => 'radio',
                    'options' => array(
                        'male' => 'Male',
                        'female' => 'Female',
                        'non_binary' => 'Non-binary',
                        'prefer_not_to_say' => 'Prefer not to say'
                    ),
                    'priority' => 'critical',
                    'estimated_time' => '15 seconds'
                ),
                'height' => array(
                    'title' => 'What is your height?',
                    'type' => 'height_input',
                    'priority' => 'high',
                    'estimated_time' => '30 seconds'
                ),
                'weight' => array(
                    'title' => 'What is your current weight?',
                    'type' => 'weight_input',
                    'priority' => 'high',
                    'estimated_time' => '30 seconds'
                )
            ),
            'lifestyle_factors' => array(
                'activity_level' => array(
                    'title' => 'How would you describe your activity level?',
                    'type' => 'radio',
                    'options' => array(
                        'sedentary' => 'Sedentary (little to no exercise)',
                        'light' => 'Light (light exercise 1-3 days/week)',
                        'moderate' => 'Moderate (moderate exercise 3-5 days/week)',
                        'active' => 'Active (hard exercise 6-7 days/week)',
                        'very_active' => 'Very Active (very hard exercise, physical job)'
                    ),
                    'priority' => 'high',
                    'estimated_time' => '30 seconds'
                ),
                'sleep_hours' => array(
                    'title' => 'How many hours of sleep do you typically get per night?',
                    'type' => 'radio',
                    'options' => array(
                        'less_than_5' => 'Less than 5 hours',
                        '5_6' => '5-6 hours',
                        '7_8' => '7-8 hours',
                        '9_plus' => '9+ hours'
                    ),
                    'priority' => 'medium',
                    'estimated_time' => '20 seconds'
                ),
                'stress_level' => array(
                    'title' => 'How would you rate your current stress level?',
                    'type' => 'radio',
                    'options' => array(
                        'very_low' => 'Very Low',
                        'low' => 'Low',
                        'moderate' => 'Moderate',
                        'high' => 'High',
                        'very_high' => 'Very High'
                    ),
                    'priority' => 'medium',
                    'estimated_time' => '20 seconds'
                )
            ),
            'health_background' => array(
                'medical_conditions' => array(
                    'title' => 'Do you have any current medical conditions?',
                    'type' => 'multiselect',
                    'options' => array(
                        'none' => 'None',
                        'diabetes' => 'Diabetes',
                        'hypertension' => 'High Blood Pressure',
                        'heart_disease' => 'Heart Disease',
                        'thyroid' => 'Thyroid Disorder',
                        'other' => 'Other'
                    ),
                    'priority' => 'medium',
                    'estimated_time' => '45 seconds'
                ),
                'medications' => array(
                    'title' => 'Are you currently taking any medications?',
                    'type' => 'textarea',
                    'priority' => 'low',
                    'estimated_time' => '60 seconds',
                    'dependencies' => array( 'medical_conditions' )
                )
            )
        );
    }

    /**
     * Check if question is relevant for assessment type
     *
     * @param string $question_key Question key
     * @param string $assessment_type Assessment type
     * @return bool Whether question is relevant
     */
    private static function is_question_relevant( $question_key, $assessment_type ) {
        $relevance_map = array(
            'testosterone' => array( 'age', 'gender', 'height', 'weight', 'activity_level', 'stress_level', 'sleep_hours' ),
            'menopause' => array( 'age', 'gender', 'height', 'weight', 'stress_level', 'sleep_hours', 'medical_conditions' ),
            'weight_loss' => array( 'age', 'gender', 'height', 'weight', 'activity_level', 'diet_type', 'exercise_frequency' ),
            'longevity' => array( 'age', 'gender', 'activity_level', 'stress_level', 'sleep_hours', 'medical_conditions', 'family_history' )
        );
        
        if ( ! isset( $relevance_map[ $assessment_type ] ) ) {
            return true; // If no specific mapping, assume relevant
        }
        
        return in_array( $question_key, $relevance_map[ $assessment_type ], true );
    }

    /**
     * Check question dependencies
     *
     * @param array $question_config Question configuration
     * @param array $user_data User data
     * @return bool Whether dependencies are met
     */
    private static function check_question_dependencies( $question_config, $user_data ) {
        if ( empty( $question_config['dependencies'] ) ) {
            return true;
        }
        
        foreach ( $question_config['dependencies'] as $dependency ) {
            if ( empty( $user_data[ $dependency ] ) ) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get user progressive data
     *
     * @param int $user_id User ID
     * @return array User progressive data
     */
    private static function get_user_progressive_data( $user_id ) {
        $allowed_fields = self::get_allowed_progressive_fields();
        $user_data = array();
        
        foreach ( $allowed_fields as $field ) {
            $value = get_user_meta( $user_id, 'ennu_' . $field, true );
            if ( ! empty( $value ) ) {
                $user_data[ $field ] = $value;
            }
        }
        
        return $user_data;
    }

    /**
     * Calculate section completeness
     *
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @return array Completeness data
     */
    private static function calculate_section_completeness( $user_id, $assessment_type ) {
        $user_data = self::get_user_progressive_data( $user_id );
        $question_flow = self::get_progressive_question_flow();
        
        $completeness = array();
        
        foreach ( $question_flow as $section => $questions ) {
            $total_questions = count( $questions );
            $completed_questions = 0;
            
            foreach ( $questions as $question_key => $question_config ) {
                if ( isset( $user_data[ $question_key ] ) && ! empty( $user_data[ $question_key ] ) ) {
                    $completed_questions++;
                }
            }
            
            $percentage = $total_questions > 0 ? intval( round( ( $completed_questions / $total_questions ) * 100 ) ) : 0;
            
            $completeness[ $section ] = array(
                'completed' => $completed_questions,
                'total' => $total_questions,
                'percentage' => $percentage
            );
        }
        
        return $completeness;
    }

    /**
     * Log progressive data collection
     *
     * @param int $user_id User ID
     * @param string $field_name Field name
     * @param string $assessment_type Assessment type
     */
    private static function log_progressive_data_collection( $user_id, $field_name, $assessment_type ) {
        $log_entry = array(
            'user_id' => $user_id,
            'field_name' => $field_name,
            'assessment_type' => $assessment_type,
            'timestamp' => current_time( 'mysql' ),
            'session_id' => session_id()
        );
        
        $recent_activity = get_transient( 'ennu_progressive_data_activity_' . $user_id ) ?: array();
        $recent_activity[] = $log_entry;
        
        // Keep only last 50 entries
        if ( count( $recent_activity ) > 50 ) {
            $recent_activity = array_slice( $recent_activity, -50 );
        }
        
        set_transient( 'ennu_progressive_data_activity_' . $user_id, $recent_activity, DAY_IN_SECONDS );
    }
}

ENNU_Progressive_Data_Collector::init();
