<?php
/**
 * ENNU Life Enhanced Database Management Class - Bulletproof Edition
 * 
 * Integrates advanced caching, performance optimization, and bulletproof
 * error handling for zero-issue deployment.
 * 
 * @package ENNU_Life
 * @version 23.1.0
 * @author Manus - World's Greatest WordPress Developer
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies - with safety check
$cache_file = dirname(__FILE__) . '/class-score-cache.php';
if (file_exists($cache_file)) {
    require_once $cache_file;
}

class ENNU_Life_Enhanced_Database {
    
    private static $instance = null;
    
    /**
     * Performance monitoring
     * 
     * @var array
     */
    private $performance_log = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Enhanced save assessment with intelligent caching
     */
    public function save_assessment($assessment_type, $form_data, $scores = null, $user_id = null) {
        $start_time = microtime(true);
        
        try {
            // Get user ID (use parameter if provided, otherwise current user)
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            
            if (!$user_id) {
                throw new Exception('User ID not found. Cannot save assessment.');
            }

            // Sanitize assessment type
            $assessment_type = sanitize_text_field($assessment_type);
            
            // Extract contact fields from form_data for separate handling
            $contact_fields = [];
            $assessment_data_only = $form_data;

            // Define common contact field keys
            $common_contact_keys = ['name', 'email', 'mobile', 'full_name', 'phone'];

            foreach ($common_contact_keys as $key) {
                if (isset($assessment_data_only[$key])) {
                    $contact_fields[$key] = $assessment_data_only[$key];
                    unset($assessment_data_only[$key]);
                }
            }

            // Update standard WordPress user fields with contact data
            if (!empty($contact_fields)) {
                $this->update_user_contact_fields($user_id, $contact_fields);
            }

            // Save individual assessment fields
            $this->save_individual_fields(null, $user_id, $assessment_type, $assessment_data_only);
            
            // Invalidate cache for this user and assessment
            ENNU_Score_Cache::invalidate_cache($user_id, $assessment_type);
            
            // Calculate and cache new scores
            $score_data = $this->calculate_and_store_scores($assessment_type, $assessment_data_only, null, $user_id);
            
            // Update user journey timestamps
            $this->update_user_journey_timestamps($user_id, $assessment_type);
            
            // Register fields with available systems
            $this->register_integration_fields($user_id, $assessment_type, $assessment_data_only);
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('save_assessment', $execution_time, $user_id);
            
            return true;
            
        } catch (Exception $e) {
            error_log('ENNU Enhanced Database Error: ' . $e->getMessage());
            
            // Log performance even on error
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('save_assessment_error', $execution_time, $user_id);
            
            return false;
        }
    }
    
    /**
     * Enhanced score calculation with caching
     */
    public function calculate_and_store_scores($assessment_type, $form_data, $scores = null, $user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        // Check cache first
        $cached_score = ENNU_Score_Cache::get_cached_score($user_id, $assessment_type);
        if ($cached_score !== false) {
            return $cached_score['score_data'];
        }
        
        $start_time = microtime(true);
        
        try {
            // Get assessment data if not provided
            if (empty($form_data)) {
                $form_data = $this->get_user_assessment_data($user_id, $assessment_type);
            }
            
            if (empty($form_data)) {
                return false;
            }
            
            // Calculate scores using existing scoring system
            if (!class_exists('ENNU_Scoring_System')) {
                require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-scoring-system.php';
            }
            
            // Include question mapper for proper data transformation
            if (!class_exists('ENNU_Question_Mapper')) {
                require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-question-mapper.php';
            }
            
            // Map form data to scoring system format
            $mapped_data = ENNU_Question_Mapper::map_form_data_to_scoring($assessment_type, $form_data);
            
            $scoring_system = new ENNU_Scoring_System();
            $calculated_scores = $scoring_system->calculate_assessment_score($assessment_type, $mapped_data);
            
            if (!$calculated_scores) {
                throw new Exception("Failed to calculate scores for {$assessment_type}");
            }
            
            // Store calculated scores
            $score_data = array(
                'overall_score' => $calculated_scores['overall_score'],
                'category_scores' => $calculated_scores['category_scores'],
                'interpretation' => $this->get_score_interpretation($calculated_scores['overall_score']),
                'calculated_at' => current_time('mysql'),
                'assessment_type' => $assessment_type
            );
            
            // Save to user meta
            update_user_meta($user_id, $assessment_type . '_calculated_score', $score_data['overall_score']);
            update_user_meta($user_id, $assessment_type . '_category_scores', $score_data['category_scores']);
            update_user_meta($user_id, $assessment_type . '_score_interpretation', $score_data['interpretation']);
            update_user_meta($user_id, $assessment_type . '_score_calculated_at', $score_data['calculated_at']);
            
            // Cache the results
            ENNU_Score_Cache::cache_score($user_id, $assessment_type, $score_data);
            
            // Update overall health metrics
            $this->update_overall_health_metrics($user_id);
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('calculate_scores', $execution_time, $user_id);
            
            return $score_data;
            
        } catch (Exception $e) {
            error_log('ENNU Score Calculation Error: ' . $e->getMessage());
            
            // Log performance even on error
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('calculate_scores_error', $execution_time, $user_id);
            
            return false;
        }
    }
    
    /**
     * Get user assessment data with caching
     */
    public function get_user_assessment_data($user_id, $assessment_type) {
        $cache_key = "user_data_{$user_id}_{$assessment_type}";
        
        // Check memory cache
        static $data_cache = array();
        if (isset($data_cache[$cache_key])) {
            return $data_cache[$cache_key];
        }
        
        $start_time = microtime(true);
        
        try {
            global $wpdb;
            
            // Get all user meta for this assessment type
            $meta_data = $wpdb->get_results($wpdb->prepare("
                SELECT meta_key, meta_value 
                FROM {$wpdb->usermeta} 
                WHERE user_id = %d 
                AND meta_key LIKE %s
            ", $user_id, $assessment_type . '_%'));
            
            $assessment_data = array();
            
            foreach ($meta_data as $meta) {
                // Remove assessment type prefix from key
                $clean_key = str_replace($assessment_type . '_', '', $meta->meta_key);
                $assessment_data[$clean_key] = $meta->meta_value;
            }
            
            // Cache the result
            $data_cache[$cache_key] = $assessment_data;
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('get_user_data', $execution_time, $user_id);
            
            return $assessment_data;
            
        } catch (Exception $e) {
            error_log('ENNU Get User Data Error: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Update user contact fields efficiently
     */
    private function update_user_contact_fields($user_id, $contact_fields) {
        try {
            $user_update_args = array("ID" => $user_id);
            
            // Handle name fields
            if (isset($contact_fields["name"]) || isset($contact_fields["full_name"])) {
                $name = isset($contact_fields["name"]) ? $contact_fields["name"] : $contact_fields["full_name"];
                $name_parts = explode(" ", $name, 2);
                $user_update_args["first_name"] = $name_parts[0];
                if (isset($name_parts[1])) {
                    $user_update_args["last_name"] = $name_parts[1];
                }
            }
            
            // Handle email
            if (isset($contact_fields["email"])) {
                $user_update_args["user_email"] = sanitize_email($contact_fields["email"]);
            }
            
            // Update user
            $result = wp_update_user($user_update_args);
            
            if (is_wp_error($result)) {
                throw new Exception('Failed to update user: ' . $result->get_error_message());
            }
            
            // Handle other contact fields
            foreach ($contact_fields as $key => $value) {
                if (!in_array($key, ["name", "email", "full_name"])) {
                    update_user_meta($user_id, "ennu_contact_" . $key, sanitize_text_field($value));
                }
            }
            
        } catch (Exception $e) {
            error_log('ENNU Update Contact Fields Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Save individual fields with validation
     */
    private function save_individual_fields($post_id, $user_id, $assessment_type, $form_data) {
        if (!$user_id || empty($form_data)) {
            return;
        }

        try {
            foreach ($form_data as $key => $value) {
                // Handle special cases for DOB and age fields
                if (strpos($key, ":") !== false) {
                    list($field_name, $field_type) = explode(":", $key);
                    if ($field_type === "dob_combined") {
                        update_user_meta($user_id, "user_dob_combined", sanitize_text_field($value));
                        continue;
                    } else if ($field_type === "age") {
                        update_user_meta($user_id, "user_age", sanitize_text_field($value));
                        continue;
                    }
                }

                // Determine the meta key based on the naming convention
                $meta_key = sanitize_key($assessment_type . "_" . $key);
                
                // Sanitize value based on type
                $sanitized_value = $this->sanitize_field_value($value);
                
                update_user_meta($user_id, $meta_key, $sanitized_value);
            }
            
        } catch (Exception $e) {
            error_log('ENNU Save Individual Fields Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Sanitize field value based on type
     */
    private function sanitize_field_value($value) {
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        } else if (is_email($value)) {
            return sanitize_email($value);
        } else if (is_numeric($value)) {
            return floatval($value);
        } else {
            return sanitize_text_field($value);
        }
    }
    
    /**
     * Register fields with integration systems
     */
    private function register_integration_fields($user_id, $assessment_type, $form_data) {
        try {
            // Register with WP Fusion if available
            if (function_exists('wp_fusion') && wp_fusion()) {
                $this->register_wp_fusion_fields($user_id, $assessment_type, $form_data);
            }
            
            // Register with HubSpot if available
            if (defined('HUBSPOT_API_KEY')) {
                $this->register_hubspot_fields($user_id, $assessment_type, $form_data);
            }
            
            // Store in WordPress options as fallback
            $this->register_wordpress_fields($assessment_type, $form_data);
            
        } catch (Exception $e) {
            error_log('ENNU Register Integration Fields Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Register fields with WP Fusion
     */
    private function register_wp_fusion_fields($user_id, $assessment_type, $form_data) {
        if (!function_exists('wp_fusion') || !wp_fusion()) {
            return;
        }
        
        try {
            $contact_fields = wp_fusion()->settings->get('contact_fields', array());
            
            foreach ($form_data as $key => $value) {
                $field_key = $assessment_type . '_' . $key;
                
                if (!isset($contact_fields[$field_key])) {
                    $contact_fields[$field_key] = array(
                        'active' => true,
                        'label' => ucwords(str_replace('_', ' ', $key)),
                        'type' => 'text',
                        'group' => 'ENNU ' . ucwords(str_replace('_', ' ', $assessment_type))
                    );
                }
            }
            
            wp_fusion()->settings->set('contact_fields', $contact_fields);
            
        } catch (Exception $e) {
            error_log('ENNU WP Fusion Registration Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Register fields with HubSpot (placeholder for future implementation)
     */
    private function register_hubspot_fields($user_id, $assessment_type, $form_data) {
        // Future HubSpot integration
        error_log('ENNU: HubSpot field registration called (not yet implemented)');
    }
    
    /**
     * Register fields in WordPress options
     */
    private function register_wordpress_fields($assessment_type, $form_data) {
        $wp_fields = get_option('ennu_registered_fields', array());
        
        foreach ($form_data as $key => $value) {
            $field_key = $assessment_type . '_' . $key;
            
            if (!isset($wp_fields[$field_key])) {
                $wp_fields[$field_key] = array(
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'type' => 'text',
                    'assessment' => $assessment_type,
                    'registered_at' => current_time('mysql')
                );
            }
        }
        
        update_option('ennu_registered_fields', $wp_fields);
    }
    
    /**
     * Update user journey timestamps
     */
    private function update_user_journey_timestamps($user_id, $assessment_type) {
        $timestamp = current_time('mysql');
        
        // Update assessment-specific timestamp
        update_user_meta($user_id, $assessment_type . '_completed_at', $timestamp);
        update_user_meta($user_id, $assessment_type . '_last_updated', $timestamp);
        
        // Update overall journey timestamps
        update_user_meta($user_id, 'ennu_last_assessment_completed', $assessment_type);
        update_user_meta($user_id, 'ennu_last_activity', $timestamp);
        
        // Count completed assessments
        $completed_count = $this->count_completed_assessments($user_id);
        update_user_meta($user_id, 'ennu_completed_assessments_count', $completed_count);
    }
    
    /**
     * Count completed assessments for user
     */
    private function count_completed_assessments($user_id) {
        $assessments = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment');
        $completed = 0;
        
        foreach ($assessments as $assessment) {
            if (get_user_meta($user_id, $assessment . '_q1', true)) {
                $completed++;
            }
        }
        
        return $completed;
    }
    
    /**
     * Update overall health metrics
     */
    private function update_overall_health_metrics($user_id) {
        try {
            $assessments = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment');
            $total_score = 0;
            $count = 0;
            
            foreach ($assessments as $assessment) {
                $score = get_user_meta($user_id, $assessment . '_calculated_score', true);
                if ($score && is_numeric($score)) {
                    $total_score += floatval($score);
                    $count++;
                }
            }
            
            if ($count > 0) {
                $overall_score = $total_score / $count;
                update_user_meta($user_id, 'ennu_overall_health_score', $overall_score);
                update_user_meta($user_id, 'ennu_overall_health_interpretation', $this->get_score_interpretation($overall_score));
                update_user_meta($user_id, 'ennu_health_metrics_updated_at', current_time('mysql'));
            }
            
        } catch (Exception $e) {
            error_log('ENNU Update Health Metrics Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Get score interpretation
     */
    private function get_score_interpretation($score) {
        if ($score >= 8.0) {
            return 'Excellent';
        } elseif ($score >= 6.0) {
            return 'Good';
        } elseif ($score >= 4.0) {
            return 'Fair';
        } elseif ($score >= 2.0) {
            return 'Needs Attention';
        } else {
            return 'Critical';
        }
    }
    
    /**
     * Log performance metrics
     */
    private function log_performance($operation, $execution_time, $user_id = null) {
        $this->performance_log[] = array(
            'operation' => $operation,
            'execution_time' => $execution_time,
            'user_id' => $user_id,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true)
        );
        
        // Log slow operations
        if ($execution_time > 1.0) {
            error_log("ENNU Performance Warning: {$operation} took {$execution_time}s for user {$user_id}");
        }
    }
    
    /**
     * Get performance statistics
     */
    public function get_performance_stats() {
        if (empty($this->performance_log)) {
            return array();
        }
        
        $total_time = 0;
        $operations = array();
        
        foreach ($this->performance_log as $log) {
            $total_time += $log['execution_time'];
            
            if (!isset($operations[$log['operation']])) {
                $operations[$log['operation']] = array(
                    'count' => 0,
                    'total_time' => 0,
                    'avg_time' => 0
                );
            }
            
            $operations[$log['operation']]['count']++;
            $operations[$log['operation']]['total_time'] += $log['execution_time'];
            $operations[$log['operation']]['avg_time'] = $operations[$log['operation']]['total_time'] / $operations[$log['operation']]['count'];
        }
        
        return array(
            'total_operations' => count($this->performance_log),
            'total_execution_time' => $total_time,
            'operations' => $operations
        );
    }
}

