<?php
/**
 * Weight History Initializer
 * Creates initial weight history for users without any
 * 
 * @package ENNU_Life
 * @since 77.3.0
 */

class ENNU_Weight_History_Initializer {
    
    /**
     * Initialize weight history for a user if they don't have any
     * 
     * @param int $user_id User ID
     * @return array Weight history
     */
    public static function ensure_weight_history($user_id) {
        $history = get_user_meta($user_id, 'weight_history', true);
        
        // If history exists and has entries, return it
        if (is_array($history) && !empty($history)) {
            return $history;
        }
        
        // Get current weight from various sources
        $current_weight = self::get_current_weight($user_id);
        
        if (!$current_weight) {
            return array();
        }
        
        // Get height for BMI calculations
        $height = get_user_meta($user_id, 'height', true);
        $height_inches = self::parse_height_to_inches($height);
        
        // Create initial history with realistic progression
        $history = array();
        
        // Create initial history with just today's weight
        // We'll only create one entry since we don't have historical data
        $history = array();
        
        // Calculate BMI for current weight
        $bmi_at_point = null;
        if ($height_inches > 0) {
            $bmi_at_point = ($current_weight / ($height_inches * $height_inches)) * 703;
            $bmi_at_point = round($bmi_at_point, 1);
        }
        
        // Add today's entry
        $history[] = array(
            'date' => date('Y-m-d'),
            'weight' => round($current_weight, 1),
            'bmi' => $bmi_at_point,
            'timestamp' => time()
        );
        
        // Save the history
        update_user_meta($user_id, 'weight_history', $history);
        
        // Also create BMI history for compatibility
        $bmi_history = array();
        foreach ($history as $entry) {
            if (isset($entry['bmi']) && $entry['bmi']) {
                $bmi_history[] = array(
                    'date' => $entry['date'],
                    'value' => $entry['bmi'],
                    'timestamp' => $entry['timestamp']
                );
            }
        }
        
        if (!empty($bmi_history)) {
            update_user_meta($user_id, 'ennu_bmi_history', $bmi_history);
        }
        
        return $history;
    }
    
    /**
     * Get current weight from various sources
     */
    private static function get_current_weight($user_id) {
        // Try different weight fields in order of preference
        $weight_sources = array(
            'weight',
            'ennu_global_weight',
            'ennu_global_weight_lbs'
        );
        
        foreach ($weight_sources as $meta_key) {
            $weight = get_user_meta($user_id, $meta_key, true);
            if ($weight && is_numeric($weight)) {
                return floatval($weight);
            }
        }
        
        // Try composite field
        $height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);
        if (is_array($height_weight)) {
            if (isset($height_weight['weight']) && is_numeric($height_weight['weight'])) {
                return floatval($height_weight['weight']);
            }
            if (isset($height_weight['lbs']) && is_numeric($height_weight['lbs'])) {
                return floatval($height_weight['lbs']);
            }
        }
        
        return null;
    }
    
    /**
     * Parse height string to inches
     */
    private static function parse_height_to_inches($height_str) {
        if (empty($height_str)) {
            return 0;
        }
        
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
     * Batch initialize for all users without history
     */
    public static function batch_initialize_all_users() {
        $users = get_users(array(
            'fields' => 'ID',
            'number' => -1
        ));
        
        $initialized = 0;
        foreach ($users as $user_id) {
            $history = get_user_meta($user_id, 'weight_history', true);
            if (empty($history)) {
                self::ensure_weight_history($user_id);
                $initialized++;
            }
        }
        
        return $initialized;
    }
}