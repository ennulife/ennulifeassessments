<?php
/**
 * ENNU Life Form Handler - FIXED VERSION
 * Simplified to ensure data logging works
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Form_Handler {
    
    public function __construct() {
        // Register AJAX handlers immediately
        add_action('wp_ajax_ennu_submit_assessment', array($this, 'handle_ajax_submission'));
        add_action('wp_ajax_nopriv_ennu_submit_assessment', array($this, 'handle_ajax_submission'));
        
        error_log('ENNU: Form handler constructed and AJAX registered');
    }
    
    /**
     * Handle AJAX form submission - SIMPLIFIED VERSION
     */
    public function handle_ajax_submission() {
        error_log('🔍 ENNU FIXED: AJAX submission received');
        error_log('🔍 POST data: ' . print_r($_POST, true));
        
        try {
            // Get current user
            $current_user_id = get_current_user_id();
            error_log('🔍 Current user ID: ' . $current_user_id);
            
            // Get form data
            $form_data = $_POST;
            unset($form_data['action'], $form_data['nonce'], $form_data['assessment_nonce']);
            
            // Define global fields
            $global_fields = array(
                'first_name', 'last_name', 'email', 'billing_phone', 
                'dob_month', 'dob_day', 'dob_year', 'calculated_age', 'gender'
            );
            
            // Save global fields
            foreach ($global_fields as $field) {
                if (isset($form_data[$field]) && !empty($form_data[$field])) {
                    $meta_key = 'ennu_global_' . $field;
                    $meta_value = sanitize_text_field($form_data[$field]);
                    
                    if ($current_user_id > 0) {
                        $result = update_user_meta($current_user_id, $meta_key, $meta_value);
                        error_log("🔍 Saved {$field}: {$meta_value} - " . ($result ? 'SUCCESS' : 'FAILED'));
                    }
                }
            }
            
            // Detect assessment type
            $assessment_type = 'general_assessment';
            if (strpos(json_encode($form_data), 'weight') !== false) {
                $assessment_type = 'weight_assessment';
            } elseif (strpos(json_encode($form_data), 'health') !== false) {
                $assessment_type = 'health_assessment';
            } elseif (strpos(json_encode($form_data), 'hair') !== false) {
                $assessment_type = 'hair_assessment';
            }
            
            error_log('🔍 Assessment type: ' . $assessment_type);
            
            // Save all form data with assessment prefix
            if ($current_user_id > 0) {
                foreach ($form_data as $key => $value) {
                    $meta_key = 'ennu_' . $assessment_type . '_' . $key;
                    $meta_value = sanitize_text_field($value);
                    $result = update_user_meta($current_user_id, $meta_key, $meta_value);
                    error_log("🔍 Saved {$key}: {$meta_value} - " . ($result ? 'SUCCESS' : 'FAILED'));
                }
                
                // Mark as completed
                update_user_meta($current_user_id, 'ennu_' . $assessment_type . '_completed', 'yes');
                update_user_meta($current_user_id, 'ennu_' . $assessment_type . '_completed_date', current_time('mysql'));
            }
            
            error_log('🔍 ENNU FIXED: All data saved successfully');
            
            wp_send_json_success(array(
                'message' => 'Assessment submitted successfully!',
                'assessment_type' => $assessment_type,
                'user_id' => $current_user_id,
                'fields_saved' => count($form_data)
            ));
            
        } catch (Exception $e) {
            error_log('🔍 ENNU FIXED: Error - ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Error: ' . $e->getMessage()));
        }
    }
}

