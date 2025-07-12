<?php
/**
 * ENNU Life Comprehensive Assessment Display Class
 * 
 * Displays ALL assessment fields in user profiles, including empty fields,
 * hidden system fields, and developer-friendly field IDs.
 * 
 * @package ENNU_Life
 * @version 24.2.0
 * @author Manus - World's Greatest WordPress Developer
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Comprehensive_Assessment_Display {
    
    /**
     * Display comprehensive assessment section with ALL possible fields
     * 
     * @param int $user_id WordPress user ID
     * @param string $assessment_type Assessment type (e.g., 'hair', 'weight_loss')
     * @param string $assessment_label Human-readable assessment name
     */
    public static function display_comprehensive_section($user_id, $assessment_type, $assessment_label) {
        try {
            // Clear any transients for this assessment to ensure fresh data
            delete_transient('ennu_questions_' . $assessment_type);
            delete_transient('ennu_user_meta_' . $user_id);
            
            echo '<div class="ennu-comprehensive-section ennu-profile-section">';
            echo '<h3>' . esc_html($assessment_label) . ' - Complete Field Reference</h3>';
            
            // Get all possible questions for this assessment
            $questions = self::get_all_assessment_questions($assessment_type);
            
            if (empty($questions)) {
                error_log('ENNU: No questions found for type: ' . $assessment_type);
                echo '<p class="ennu-no-questions">No questions defined for this assessment type. Displaying raw data instead.</p>';
                self::display_fallback_raw_data($user_id, $assessment_type);
            } else {
                // Normal display with score and questions
                self::display_assessment_score($user_id, $assessment_type);
                self::display_assessment_metadata($user_id, $assessment_type);
                self::display_all_questions($user_id, $assessment_type, $questions);
                self::display_assessment_system_fields($user_id, $assessment_type);
            }

            self::display_legacy_fields($user_id);
            
            echo '</div>';
        } catch (Exception $e) {
            error_log('ENNU Admin Display Error: ' . $e->getMessage() . ' for type: ' . $assessment_type);
            echo '<div class="ennu-error-message">Error loading assessment data: ' . esc_html($e->getMessage()) . '</div>';
        }
    }
    
    /**
     * Display assessment score in a prominent block.
     */
    private static function display_assessment_score($user_id, $assessment_type) {
        $score = get_user_meta($user_id, 'ennu_' . $assessment_type . '_calculated_score', true);
        $interpretation = get_user_meta($user_id, 'ennu_' . $assessment_type . '_score_interpretation', true);

        if ($score) {
            echo '<div class="ennu-score-block">';
            echo '<h4>Assessment Score</h4>';
            echo '<div class="ennu-score-value">' . esc_html($score) . '</div>';
            echo '<div class="ennu-score-interpretation">' . esc_html($interpretation) . '</div>';
            echo '</div>';
        }
    }

    /**
     * Display assessment metadata (completion status, dates, etc.)
     */
    private static function display_assessment_metadata($user_id, $assessment_type) {
        $all_meta = get_user_meta($user_id);
        echo '<div class="ennu-metadata-section">';
        echo '<h4>Assessment Metadata</h4>';
        
        $metadata_fields = array(
            "ennu_{$assessment_type}_completion_status" => 'Completion Status',
            "ennu_{$assessment_type}_completion_date" => 'Completion Date',
            "ennu_{$assessment_type}_submission_timestamp" => 'Submission Timestamp',
            "ennu_{$assessment_type}_form_version" => 'Form Version Used',
            "ennu_{$assessment_type}_total_time_spent" => 'Total Time Spent (seconds)',
            "ennu_{$assessment_type}_retry_count" => 'Number of Retries'
        );
        
        foreach ($metadata_fields as $field_id => $field_name) {
            $value = $all_meta[$field_id][0] ?? '';
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">' . ($field_id === "ennu_{$assessment_type}_calculated_score" ? 'Not calculated yet' : 'Not set') . '</span>';
            
            echo '<div class="ennu-field-row ennu-metadata-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_name) . ':</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Display all questions with their field IDs and current values
     */
    private static function display_all_questions($user_id, $assessment_type, $questions) {
        $all_meta = get_user_meta($user_id);
        echo '<div class="ennu-questions-section">';
        echo '<h4>All Assessment Questions & Responses</h4>';
        
        foreach ($questions as $index => $question) {
            $question_number = $index + 1;
            
            // Generate field ID based on question type
            $field_id = self::generate_field_id($assessment_type, $question_number, $question);
            
            // Get value from batched meta
            $value = $all_meta[$field_id][0] ?? '';
            error_log('ENNU Display Debug: Fetching [' . $field_id . '] = ' . ($value ?: 'empty'));
            
            // Display question
            self::display_question_field($question_number, $question, $field_id, $value);
            
            // For special question types, display additional fields
            if (isset($question['type'])) {
                switch ($question['type']) {
                    case 'dob_dropdowns':
                        self::display_dob_fields($user_id, $assessment_type, $question_number, $all_meta);
                        break;
                    case 'contact_info':
                        self::display_contact_info_fields($user_id, $question);
                        break;
                    case 'multiselect':
                        self::display_multiselect_field($user_id, $field_id, $question, $value);
                        break;
                }
            }
        }
        echo '</div>';
    }
    
    /**
     * Display individual question field
     */
    private static function display_question_field($question_number, $question, $field_id, $value) {
        $question_title = wp_trim_words($question['title'], 10);
        $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not answered</span>';
        
        echo '<div class="ennu-field-row ennu-question-field">';
        echo '<div class="ennu-field-label">' . esc_html($question_title) . '</div>';
        echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
        echo '<div class="ennu-field-value">' . $display_value;
        if (!empty($question['description'])) {
            echo '<em class="ennu-field-description">' . esc_html($question['description']) . '</em>';
        }
        echo '</div>';
        echo '</div>';
        
        // Show available options if it's a choice question
        if (!empty($question['options'])) {
            echo '<div class="ennu-field-row ennu-question-options">';
            echo '<div class="ennu-field-label">Available Options:</div>';
            echo '<div class="ennu-field-id">--</div>';
            echo '<div class="ennu-field-value">';
            foreach ($question['options'] as $option) {
                echo '<span class="ennu-option-badge">' . esc_html($option['label']) . ' (' . esc_html($option['value']) . ')</span> ';
            }
            echo '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Display date of birth fields
     */
    private static function display_dob_fields($user_id, $assessment_type, $question_number, $all_meta) {
        $assessment_prefix = str_replace('_assessment', '', $assessment_type);
        $base_id = $assessment_prefix . '_q' . $question_number;
        
        $dob_fields = array(
            $base_id . '_month' => 'Birth Month',
            $base_id . '_day' => 'Birth Day', 
            $base_id . '_year' => 'Birth Year',
            $base_id => 'Combined Date of Birth',
            'user_age' => 'Calculated Age'
        );
        
        foreach ($dob_fields as $field_id => $field_name) {
            $value = $all_meta[$field_id][0] ?? '';
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not provided</span>';
            
            echo '<div class="ennu-field-row ennu-dob-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_name) . ':</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Display contact info fields
     */
    private static function display_contact_info_fields($user_id, $question) {
        if (empty($question['fields'])) {
            return;
        }
        
        foreach ($question['fields'] as $field) {
            // Use the global meta key for contact info
            $field_id = 'ennu_global_' . $field['name'];
            $field_name = $field['label'];
            $value = get_user_meta($user_id, $field_id, true);
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not provided</span>';
            
            echo '<div class="ennu-field-row ennu-contact-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_name) . ':</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Display multiselect field with all selected values
     */
    private static function display_multiselect_field($user_id, $field_id, $question, $value) {
        // Multiselect values might be stored as arrays or serialized
        $selected_values = maybe_unserialize($value);
        if (!is_array($selected_values)) {
            $selected_values = !empty($value) ? array($value) : array();
        }
        
        echo '<div class="ennu-field-row ennu-multiselect-field">';
        echo '<div class="ennu-field-label">Selected Values:</div>';
        echo '<div class="ennu-field-id">' . esc_html($field_id) . '_selected</div>';
        echo '<div class="ennu-field-value">';
        
        if (!empty($selected_values)) {
            foreach ($selected_values as $selected) {
                echo '<span class="ennu-selected-badge">' . esc_html($selected) . '</span> ';
            }
        } else {
            echo '<span class="ennu-empty-value">No selections made</span>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Display assessment-specific system fields
     */
    private static function display_assessment_system_fields($user_id, $assessment_type) {
        echo '<div class="ennu-system-fields-section">';
        echo '<h4>Hidden System Fields (Assessment-Specific)</h4>';
        
        $system_fields = array(
            "ennu_{$assessment_type}_ip_address" => 'IP Address at Submission',
            "ennu_{$assessment_type}_user_agent" => 'User Agent String',
            "ennu_{$assessment_type}_referrer_url" => 'Referrer URL',
            "ennu_{$assessment_type}_utm_source" => 'UTM Source',
            "ennu_{$assessment_type}_utm_medium" => 'UTM Medium',
            "ennu_{$assessment_type}_utm_campaign" => 'UTM Campaign',
            "ennu_{$assessment_type}_session_id" => 'Session ID',
            "ennu_{$assessment_type}_device_type" => 'Device Type',
            "ennu_{$assessment_type}_browser" => 'Browser',
            "ennu_{$assessment_type}_os" => 'Operating System',
            "ennu_{$assessment_type}_screen_resolution" => 'Screen Resolution',
            "ennu_{$assessment_type}_timezone" => 'User Timezone',
            "ennu_{$assessment_type}_language" => 'Browser Language',
            "ennu_{$assessment_type}_page_load_time" => 'Page Load Time (ms)',
            "ennu_{$assessment_type}_form_interaction_time" => 'Form Interaction Time (seconds)',
            "ennu_{$assessment_type}_abandonment_count" => 'Form Abandonment Count',
            "ennu_{$assessment_type}_error_count" => 'Error Count During Submission',
            "ennu_{$assessment_type}_validation_failures" => 'Validation Failure Count',
            "ennu_{$assessment_type}_a_b_test_group" => 'A/B Test Group',
            "ennu_{$assessment_type}_conversion_funnel_stage" => 'Conversion Funnel Stage',
            "ennu_{$assessment_type}_lead_score" => 'Lead Score',
            "ennu_{$assessment_type}_engagement_score" => 'Engagement Score',
            "ennu_{$assessment_type}_data_quality_score" => 'Data Quality Score',
            "ennu_{$assessment_type}_recommendation_engine_version" => 'Recommendation Engine Version',
            "ennu_{$assessment_type}_personalization_profile" => 'Personalization Profile ID'
        );
        
        foreach ($system_fields as $field_id => $field_name) {
            $value = get_user_meta($user_id, $field_id, true);
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not tracked</span>';
            
            echo '<div class="ennu-field-row ennu-system-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_name) . ':</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '<em class="ennu-field-description">' . esc_html($field_name) . ' Description</em></div>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Generate field ID based on assessment type and question
     */
    private static function generate_field_id($assessment_type, $question_number, $question) {
        // Remove '_assessment' suffix for cleaner IDs
        $assessment_prefix = str_replace('_assessment', '', $assessment_type);
        
        // Default: assessment_prefix + q + number (e.g., hair_q1, weight_q2)
        $question_key = $assessment_prefix . '_q' . $question_number;

        // Construct the full meta key to match the saving logic
        return 'ennu_' . $assessment_type . '_' . $question_key;
    }
    
    /**
     * Get all assessment questions for a given assessment type
     */
    private static function get_all_assessment_questions( $assessment_type ) {
        // Robust normalization
        $assessment_type = trim(strtolower($assessment_type));
        if (strpos($assessment_type, '_assessment') === false) {
            $assessment_type .= '_assessment';
        }
        error_log('ENNU: Normalized type for questions: ' . $assessment_type);

        // Load questions from the centralized configuration file.
        $questions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-questions.php';
        if ( file_exists( $questions_file ) ) {
            $all_questions = require $questions_file;
            return $all_questions[ $assessment_type ] ?? array();
        }

        return array();
    }
    
    /**
     * Display global user fields with comprehensive field reference
     */
    public static function display_global_fields_comprehensive($user_id) {
        // Fetch the user data object
        $user_data = get_userdata($user_id);
        if (!$user_data) {
            return;
        }

        echo '<div class="ennu-comprehensive-section ennu-profile-section">';
        echo '<h3>Global User Data - Complete Field Reference</h3>';
        echo '<p class="ennu-section-description">These fields persist across all assessments and are automatically populated in future forms.</p>';
        
        // Define the global fields, mapping them to the correct data source (user object or user meta)
        $global_fields = array(
            // Core Identity Fields from WP_User object
            'first_name' => array(
                'name' => 'First Name',
                'category' => 'Core Identity',
                'description' => 'User\'s first name, stored in wp_users table.',
                'source' => 'user_object'
            ),
            'last_name' => array(
                'name' => 'Last Name', 
                'category' => 'Core Identity',
                'description' => 'User\'s last name, stored in wp_users table.',
                'source' => 'user_object'
            ),
            'user_email' => array(
                'name' => 'Email Address',
                'category' => 'Core Identity', 
                'description' => 'Primary email for communications, stored in wp_users table.',
                'source' => 'user_object'
            ),
            
            // Global fields from usermeta
            'billing_phone' => array(
                'name' => 'Phone Number',
                'category' => 'Core Identity',
                'description' => 'Primary phone number for contact and billing.',
                'source' => 'meta'
            ),
            'ennu_global_user_dob_combined' => array(
                'name' => 'Date of Birth',
                'category' => 'Date of Birth',
                'description' => 'User\'s date of birth.',
                'source' => 'meta'
            ),
            'ennu_global_gender' => array(
                'name' => 'Gender',
                'category' => 'Demographics',
                'description' => 'User\'s gender.',
                'source' => 'meta'
            )
        );
        
        // Group fields by category
        $categories = array();
        foreach ($global_fields as $field_key => $field_data) {
            $categories[$field_data['category']][] = array(
                'key' => $field_key,
                'data' => $field_data
            );
        }
        
        // Display each category
        foreach ($categories as $category_name => $fields) {
            echo '<div class="ennu-field-category">';
            echo '<h4 class="ennu-category-title">' . esc_html($category_name) . '</h4>';
            
            foreach ($fields as $field) {
                $field_key = $field['key'];
                $field_data = $field['data'];
                $value = '';

                // Get value from the correct source
                if ($field_data['source'] === 'user_object') {
                    $value = $user_data->$field_key;
                } else { // source is 'meta'
                    $value = get_user_meta($user_id, $field_key, true);
                }
                
                $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not provided</span>';
                
                echo '<div class="ennu-field-row ennu-global-field">';
                echo '<div class="ennu-field-label">' . esc_html($field_data['name']) . '</div>';
                echo '<div class="ennu-field-id">Source: ' . esc_html($field_data['source']) . ' | Key: ' . esc_html($field_key) . '</div>';
                echo '<div class="ennu-field-value">' . $display_value . '<em class="ennu-field-description">' . esc_html($field_data['description']) . '</em></div>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Display global system fields that apply to all assessments
     */
    public static function display_global_system_fields($user_id) {
        echo '<div class="ennu-comprehensive-section ennu-profile-section">';
        echo '<h3>Global System Fields - Complete Reference</h3>';
        echo '<p class="ennu-section-description">These hidden fields track user behavior and system interactions across all assessments.</p>';
        
        $global_system_fields = array(
            // User Tracking
            'ennu_system_first_visit' => array(
                'name' => 'First Visit Timestamp',
                'category' => 'User Tracking',
                'description' => 'When user first visited the site'
            ),
            'ennu_system_last_activity' => array(
                'name' => 'Last Activity Timestamp',
                'category' => 'User Tracking',
                'description' => 'Most recent user activity'
            ),
            'ennu_system_total_sessions' => array(
                'name' => 'Total Sessions',
                'category' => 'User Tracking',
                'description' => 'Number of distinct sessions'
            ),
            'ennu_system_total_page_views' => array(
                'name' => 'Total Page Views',
                'category' => 'User Tracking',
                'description' => 'Total pages viewed across all sessions'
            ),
            
            // Device & Browser
            'ennu_system_primary_device' => array(
                'name' => 'Primary Device Type',
                'category' => 'Device & Browser',
                'description' => 'Most commonly used device type'
            ),
            'ennu_system_primary_browser' => array(
                'name' => 'Primary Browser',
                'category' => 'Device & Browser',
                'description' => 'Most commonly used browser'
            ),
            'ennu_system_primary_os' => array(
                'name' => 'Primary Operating System',
                'category' => 'Device & Browser',
                'description' => 'Most commonly used operating system'
            ),
            
            // Marketing Attribution
            'ennu_system_original_utm_source' => array(
                'name' => 'Original UTM Source',
                'category' => 'Marketing Attribution',
                'description' => 'UTM source from first visit'
            ),
            'ennu_system_original_utm_medium' => array(
                'name' => 'Original UTM Medium',
                'category' => 'Marketing Attribution',
                'description' => 'UTM medium from first visit'
            ),
            'ennu_system_original_utm_campaign' => array(
                'name' => 'Original UTM Campaign',
                'category' => 'Marketing Attribution',
                'description' => 'UTM campaign from first visit'
            ),
            'ennu_system_original_referrer' => array(
                'name' => 'Original Referrer',
                'category' => 'Marketing Attribution',
                'description' => 'Referrer URL from first visit'
            ),
            
            // Engagement Metrics
            'ennu_system_engagement_score' => array(
                'name' => 'Overall Engagement Score',
                'category' => 'Engagement Metrics',
                'description' => 'Calculated engagement score across all interactions'
            ),
            'ennu_system_conversion_probability' => array(
                'name' => 'Conversion Probability',
                'category' => 'Engagement Metrics',
                'description' => 'Calculated probability of conversion'
            ),
            'ennu_system_lead_score' => array(
                'name' => 'Lead Score',
                'category' => 'Engagement Metrics',
                'description' => 'Overall lead scoring value'
            ),
            
            // Assessment Completion
            'ennu_system_assessments_completed' => array(
                'name' => 'Total Assessments Completed',
                'category' => 'Assessment Completion',
                'description' => 'Number of assessments completed'
            ),
            'ennu_system_assessments_started' => array(
                'name' => 'Total Assessments Started',
                'category' => 'Assessment Completion',
                'description' => 'Number of assessments started (including incomplete)'
            ),
            'ennu_system_completion_rate' => array(
                'name' => 'Assessment Completion Rate',
                'category' => 'Assessment Completion',
                'description' => 'Percentage of started assessments that were completed'
            ),
            
            // Technical Data
            'ennu_system_ip_addresses' => array(
                'name' => 'IP Address History',
                'category' => 'Technical Data',
                'description' => 'List of IP addresses used (serialized array)'
            ),
            'ennu_system_user_agents' => array(
                'name' => 'User Agent History',
                'category' => 'Technical Data',
                'description' => 'List of user agents used (serialized array)'
            ),
            'ennu_system_screen_resolutions' => array(
                'name' => 'Screen Resolution History',
                'category' => 'Technical Data',
                'description' => 'List of screen resolutions used (serialized array)'
            )
        );
        
        // Group fields by category
        $categories = array();
        foreach ($global_system_fields as $field_id => $field_data) {
            $categories[$field_data['category']][] = array(
                'id' => $field_id,
                'data' => $field_data
            );
        }
        
        // Display each category
        foreach ($categories as $category_name => $fields) {
            echo '<div class="ennu-field-category ennu-system-category">';
            echo '<h4 class="ennu-category-title">' . esc_html($category_name) . '</h4>';
            
            foreach ($fields as $field) {
                $field_id = $field['id'];
                $field_data = $field['data'];
                $value = get_user_meta($user_id, $field_id, true);
                $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not tracked</span>';
                
                echo '<div class="ennu-field-row ennu-system-field">';
                echo '<div class="ennu-field-label">' . esc_html($field_data['name']) . '</div>';
                echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
                echo '<div class="ennu-field-value">' . $display_value . '<em class="ennu-field-description">' . esc_html($field_data['description']) . '</em></div>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Display legacy or uncategorized ENNU fields
     */
    private static function display_legacy_fields($user_id) {
        $all_meta = get_user_meta($user_id);
        $legacy_fields = array();

        foreach ($all_meta as $key => $value) {
            if (strpos($key, 'ennu_') === 0 && strpos($key, '_assessment_') === false && !in_array($key, ['ennu_global_', 'ennu_system_'])) { // Filter for unprefixed/legacy
                $legacy_fields[$key] = $value[0] ?? '';
            }
        }

        if (empty($legacy_fields)) {
            return;
        }

        echo '<div class="ennu-legacy-section">';
        echo '<h4>Legacy/Uncategorized Data</h4>';
        foreach ($legacy_fields as $field_id => $value) {
            $display_value = !empty($value) ? esc_html($value) : '<span class="ennu-empty-value">Not set</span>';
            echo '<div class="ennu-field-row ennu-legacy-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_id) . ':</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Fallback to display raw meta data if questions are empty
     */
    private static function display_fallback_raw_data($user_id, $assessment_type) {
        $all_meta = get_user_meta($user_id);
        $prefix = 'ennu_' . $assessment_type . '_';
        echo '<div class="ennu-fallback-section">';
        echo '<h4>Raw Saved Data (Fallback)</h4>';
        $found = false;
        foreach ($all_meta as $key => $value_arr) {
            if (strpos($key, $prefix) === 0) {
                $found = true;
                $clean_key = str_replace($prefix, '', $key);
                $value = $value_arr[0] ?? '';
                $display_value = !empty($value) ? esc_html($value) : '<span class="ennu-empty-value">Not set</span>';
                echo '<div class="ennu-field-row">';
                echo '<div class="ennu-field-label">' . esc_html($clean_key) . ' (' . esc_html($key) . '):</div>';
                echo '<div class="ennu-field-value">' . $display_value . '</div>';
                echo '</div>';
            }
        }
        if (!$found) {
            echo '<p>No raw data found for this assessment.</p>';
        }
        echo '</div>';
    }
}

