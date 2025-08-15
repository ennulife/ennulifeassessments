<?php
/**
 * Weight AJAX Handler
 * Handles inline editing of weight values
 * 
 * @package ENNU_Life
 * @since 77.2.0
 */

class ENNU_Weight_Ajax_Handler {
    
    /**
     * Initialize the handler
     */
    public function __construct() {
        add_action('wp_ajax_ennu_save_weight', array($this, 'save_weight'));
        add_action('wp_ajax_nopriv_ennu_save_weight', array($this, 'save_weight'));
    }
    
    /**
     * Save weight value via AJAX
     */
    public function save_weight() {
        // Verify nonce - accept both specific and dashboard nonces
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'ennu_save_weight') || 
                          wp_verify_nonce($_POST['nonce'], 'ennu_dashboard_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid security token');
            return;
        }
        
        // Get and validate data
        $user_id = intval($_POST['user_id']);
        $field = sanitize_text_field($_POST['field']);
        $value = floatval($_POST['value']);
        
        // Check if user can edit this data
        if ($user_id !== get_current_user_id() && !current_user_can('edit_users')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        // Validate field name
        if (!in_array($field, array('current_weight', 'target_weight'))) {
            wp_send_json_error('Invalid field');
            return;
        }
        
        // Validate value range
        if ($value < 50 || $value > 1000) {
            wp_send_json_error('Weight must be between 50 and 1000 lbs');
            return;
        }
        
        // Save the value
        if ($field === 'current_weight') {
            // Update weight in user meta
            $update_result = update_user_meta($user_id, 'weight', $value);
            
            // CRITICAL: Also update the composite height_weight field that the dashboard uses
            $height_weight_data = get_user_meta($user_id, 'ennu_global_height_weight', true);
            if (!is_array($height_weight_data)) {
                $height_weight_data = array();
            }
            $height_weight_data['weight'] = $value;
            $height_weight_data['lbs'] = $value; // Both keys for compatibility
            update_user_meta($user_id, 'ennu_global_height_weight', $height_weight_data);
            
            // Also update the global weight fields
            update_user_meta($user_id, 'ennu_global_weight', $value);
            update_user_meta($user_id, 'ennu_global_weight_lbs', $value);
            
            // Log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("ENNU Weight Save: User $user_id, Field: weight, Value: $value, Result: " . ($update_result ? 'success' : 'failed'));
            }
            
            // Calculate and update BMI if height is available
            $height = get_user_meta($user_id, 'height', true);
            if ($height) {
                // Extract numeric value from height (e.g., "5'10\"" becomes 70 inches)
                $height_inches = $this->parse_height_to_inches($height);
                if ($height_inches > 0) {
                    $bmi = ($value / ($height_inches * $height_inches)) * 703;
                    update_user_meta($user_id, 'bmi', round($bmi, 1));
                    update_user_meta($user_id, 'ennu_calculated_bmi', round($bmi, 1));
                    update_user_meta($user_id, 'ennu_global_bmi', round($bmi, 1));
                }
            }
            
            // Store weight history
            $this->update_weight_history($user_id, $value);
            
        } else if ($field === 'target_weight') {
            $update_result = update_user_meta($user_id, 'target_weight', $value);
            
            // Log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log("ENNU Weight Save: User $user_id, Field: target_weight, Value: $value, Result: " . ($update_result ? 'success' : 'failed'));
            }
            
            // Calculate and store target BMI
            $height = get_user_meta($user_id, 'height', true);
            if ($height) {
                $height_inches = $this->parse_height_to_inches($height);
                if ($height_inches > 0) {
                    $target_bmi = ($value / ($height_inches * $height_inches)) * 703;
                    update_user_meta($user_id, 'target_bmi', round($target_bmi, 1));
                }
            }
        }
        
        // Return success
        wp_send_json_success(array(
            'message' => 'Weight saved successfully',
            'field' => $field,
            'value' => $value
        ));
    }
    
    /**
     * Parse height string to inches
     */
    private function parse_height_to_inches($height_str) {
        // Handle format like "5'10\"" or "5 ft 10 in"
        if (preg_match('/(\d+)[\'ft\s]+(\d+)/', $height_str, $matches)) {
            $feet = intval($matches[1]);
            $inches = intval($matches[2]);
            return ($feet * 12) + $inches;
        }
        
        // Handle just inches
        if (preg_match('/^(\d+)/', $height_str, $matches)) {
            return intval($matches[1]);
        }
        
        return 0;
    }
    
    /**
     * Update weight history with smart recalculation
     */
    private function update_weight_history($user_id, $new_weight) {
        $history = get_user_meta($user_id, 'weight_history', true);
        if (!is_array($history)) {
            $history = array();
        }
        
        // Get current height for BMI calculations
        $height = get_user_meta($user_id, 'height', true);
        $height_inches = $this->parse_height_to_inches($height);
        
        // Get the previous weight to calculate the change
        $previous_weight = null;
        if (!empty($history)) {
            $last_entry = end($history);
            $previous_weight = $last_entry['weight'];
        }
        
        // Check if we're adding today's entry or updating it
        $today = current_time('Y-m-d');
        $today_index = -1;
        foreach ($history as $index => $entry) {
            if (isset($entry['date']) && $entry['date'] === $today) {
                $today_index = $index;
                break;
            }
        }
        
        // Calculate BMI for new weight
        $new_bmi = null;
        if ($height_inches > 0) {
            $new_bmi = ($new_weight / ($height_inches * $height_inches)) * 703;
            $new_bmi = round($new_bmi, 1);
        }
        
        // If we have an entry for today, update it
        if ($today_index >= 0) {
            $history[$today_index]['weight'] = $new_weight;
            $history[$today_index]['bmi'] = $new_bmi;
            $history[$today_index]['timestamp'] = current_time('timestamp');
        } else {
            // Add new entry for today
            $history[] = array(
                'date' => $today,
                'weight' => $new_weight,
                'bmi' => $new_bmi,
                'timestamp' => current_time('timestamp')
            );
        }
        
        // Only recalculate BMI values, don't modify historical weights
        if ($height_inches > 0) {
            // Just update BMI calculations for existing weights
            foreach ($history as $index => &$entry) {
                // Skip if this is today's entry (already has correct BMI)
                if ($entry['date'] === $today) continue;
                
                // Keep historical weight unchanged, just recalculate BMI
                if (isset($entry['weight']) && is_numeric($entry['weight'])) {
                    $entry['bmi'] = round(($entry['weight'] / ($height_inches * $height_inches)) * 703, 1);
                }
            }
        }
        
        // Keep only last 12 entries
        if (count($history) > 12) {
            $history = array_slice($history, -12);
        }
        
        // Save both weight history and BMI history
        update_user_meta($user_id, 'weight_history', $history);
        
        // Also update BMI history for compatibility
        $bmi_history = array();
        foreach ($history as $entry) {
            if (isset($entry['bmi'])) {
                $bmi_history[] = array(
                    'date' => $entry['date'],
                    'value' => $entry['bmi'],
                    'timestamp' => $entry['timestamp']
                );
            }
        }
        update_user_meta($user_id, 'ennu_bmi_history', $bmi_history);
        
        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Weight history updated for user $user_id: " . count($history) . " entries");
        }
    }
}

// Initialize the handler
new ENNU_Weight_Ajax_Handler();