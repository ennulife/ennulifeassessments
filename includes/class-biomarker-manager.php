<?php
/**
 * ENNU Life Biomarker Data Manager
 * Handles lab data import, storage, and doctor recommendations
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Biomarker_Manager {
    
    public static function import_lab_results( $user_id, $lab_data ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to import lab data' );
        }
        
        $validated_data = self::validate_lab_data( $lab_data );
        
        if ( is_wp_error( $validated_data ) ) {
            return $validated_data;
        }
        
        update_user_meta( $user_id, 'ennu_biomarker_data', $validated_data );
        update_user_meta( $user_id, 'ennu_lab_import_date', current_time( 'mysql' ) );
        
        ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );
        
        error_log( 'ENNU Biomarker Manager: Imported lab results for user ' . $user_id );
        
        return array(
            'success' => true,
            'biomarkers_imported' => count( $validated_data ),
            'import_date' => current_time( 'mysql' )
        );
    }
    
    public static function add_doctor_recommendations( $user_id, $recommendations ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to add recommendations' );
        }
        
        $existing_recommendations = get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
        if ( ! is_array( $existing_recommendations ) ) {
            $existing_recommendations = array();
        }
        
        $new_recommendation = array(
            'date' => current_time( 'mysql' ),
            'doctor_id' => get_current_user_id(),
            'recommendations' => $recommendations,
            'biomarker_targets' => $recommendations['biomarker_targets'] ?? array(),
            'lifestyle_advice' => $recommendations['lifestyle_advice'] ?? '',
            'follow_up_date' => $recommendations['follow_up_date'] ?? null
        );
        
        $existing_recommendations[] = $new_recommendation;
        
        update_user_meta( $user_id, 'ennu_doctor_recommendations', $existing_recommendations );
        
        return array(
            'success' => true,
            'recommendation_id' => count( $existing_recommendations ) - 1
        );
    }
    
    public static function get_user_biomarkers( $user_id ) {
        return get_user_meta( $user_id, 'ennu_biomarker_data', true );
    }
    
    public static function get_doctor_recommendations( $user_id ) {
        return get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
    }
    
    private static function validate_lab_data( $lab_data ) {
        if ( ! is_array( $lab_data ) ) {
            return new WP_Error( 'invalid_format', 'Lab data must be an array' );
        }
        
        $validated_data = array();
        
        foreach ( $lab_data as $biomarker_name => $biomarker_info ) {
            if ( ! is_array( $biomarker_info ) || ! isset( $biomarker_info['value'] ) ) {
                continue;
            }
            
            $validated_data[$biomarker_name] = array(
                'value' => floatval( $biomarker_info['value'] ),
                'unit' => sanitize_text_field( $biomarker_info['unit'] ?? '' ),
                'reference_range' => sanitize_text_field( $biomarker_info['reference_range'] ?? '' ),
                'test_date' => sanitize_text_field( $biomarker_info['test_date'] ?? current_time( 'mysql' ) ),
                'lab_name' => sanitize_text_field( $biomarker_info['lab_name'] ?? '' )
            );
        }
        
        return $validated_data;
    }
    
    public static function calculate_new_life_score_projection( $user_id ) {
        $current_biomarkers = self::get_user_biomarkers( $user_id );
        $doctor_recommendations = self::get_doctor_recommendations( $user_id );
        
        if ( empty( $current_biomarkers ) || empty( $doctor_recommendations ) ) {
            return null;
        }
        
        $latest_recommendations = end( $doctor_recommendations );
        $biomarker_targets = $latest_recommendations['biomarker_targets'] ?? array();
        
        $projected_biomarkers = $current_biomarkers;
        foreach ( $biomarker_targets as $biomarker_name => $target_value ) {
            if ( isset( $projected_biomarkers[$biomarker_name] ) ) {
                $projected_biomarkers[$biomarker_name]['value'] = $target_value;
            }
        }
        
        if ( class_exists( 'ENNU_Objective_Engine' ) ) {
            $current_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
            
            $objective_engine = new ENNU_Objective_Engine( $projected_biomarkers );
            $projected_scores = $objective_engine->apply_biomarker_actuality_adjustments( $current_scores );
            
            $projected_life_score = array_sum( $projected_scores ) / count( $projected_scores );
            
            return array(
                'projected_pillar_scores' => $projected_scores,
                'projected_life_score' => $projected_life_score,
                'improvement_potential' => $projected_life_score - (get_user_meta( $user_id, 'ennu_life_score', true ) ?? 0)
            );
        }
        
        return null;
    }
    
    public static function get_biomarker_recommendations( $user_id ) {
        $user_symptoms = ENNU_Assessment_Scoring::get_symptom_data_for_user( $user_id );
        $biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
        
        if ( ! file_exists( $biomarker_map_file ) ) {
            return array();
        }
        
        $biomarker_map = require $biomarker_map_file;
        $recommended_biomarkers = array();
        
        $all_symptoms = array();
        foreach ( $user_symptoms as $assessment_type => $symptoms ) {
            if ( is_array( $symptoms ) ) {
                $all_symptoms = array_merge( $all_symptoms, $symptoms );
            }
        }
        
        $symptom_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
        if ( file_exists( $symptom_map_file ) ) {
            $symptom_map = require $symptom_map_file;
            
            foreach ( $all_symptoms as $symptom ) {
                if ( isset( $symptom_map[$symptom] ) ) {
                    foreach ( $symptom_map[$symptom] as $category => $weight_data ) {
                        if ( isset( $biomarker_map[$category] ) ) {
                            $recommended_biomarkers = array_merge( $recommended_biomarkers, $biomarker_map[$category] );
                        }
                    }
                }
            }
        }
        
        return array_unique( $recommended_biomarkers );
    }
}
||||||| f31b4df
=======
<?php
/**
 * ENNU Life Assessments - Biomarker Manager
 * 
 * Handles biomarker data storage, validation, and correlation tracking
 * 
 * @package ENNU_Life
 * @version 62.7.0
 */

class ENNU_Biomarker_Manager {
    
    /**
     * Biomarker data structure
     */
    private $biomarker_schema = array(
        'value' => '',
        'unit' => '',
        'reference_range_low' => '',
        'reference_range_high' => '',
        'reference_range_unit' => '',
        'date_tested' => '',
        'lab_name' => '',
        'lab_id' => '',
        'test_method' => '',
        'status' => '', // low, normal, high, critical
        'notes' => '',
        'timestamp' => '',
        'source' => '', // manual, lab_import, api
        'confidence_score' => 0,
        'loinc_code' => '',
        'specimen_type' => '',
        'biomarker_type' => ''
    );
    
    /**
     * Expiration rules for different biomarkers (in days)
     */
    private $expiration_rules = array(
        'testosterone' => 90,
        'cortisol' => 30,
        'vitamin_d' => 180,
        'thyroid_panel' => 90,
        'lipid_panel' => 365,
        'glucose' => 30,
        'hba1c' => 90,
        'insulin' => 90,
        'ferritin' => 180,
        'vitamin_b12' => 180,
        'folate' => 180,
        'homocysteine' => 90,
        'hs_crp' => 90,
        'default' => 90
    );
    
    /**
     * Save biomarker data for a user
     * 
     * @param int $user_id User ID
     * @param string $biomarker_name Biomarker name
     * @param array $data Biomarker data
     * @return bool Success status
     */
    public function save_biomarker_data($user_id, $biomarker_name, $data) {
        try {
            // Validate user
            if (!$user_id || !get_user_by('ID', $user_id)) {
                throw new Exception('Invalid user ID');
            }
            
            // Sanitize biomarker name
            $biomarker_name = sanitize_text_field($biomarker_name);
            
            // Validate and sanitize data
            $sanitized_data = $this->validate_and_sanitize_biomarker_data($data);
            
            // Add timestamp
            $sanitized_data['timestamp'] = current_time('mysql');
            
            // Get existing data
            $existing_data = $this->get_user_biomarkers($user_id, $biomarker_name);
            if (!is_array($existing_data)) {
                $existing_data = array();
            }
            
            // Add new entry
            $existing_data[] = $sanitized_data;
            
            // Store in user meta
            $meta_key = 'ennu_biomarker_' . $biomarker_name;
            $success = update_user_meta($user_id, $meta_key, $existing_data);
            
            if ($success) {
                // Update last test date
                update_user_meta($user_id, 'ennu_biomarker_' . $biomarker_name . '_last_tested', $sanitized_data['date_tested']);
                
                // Trigger action for other systems
                do_action('ennu_biomarker_updated', $user_id, $biomarker_name, $sanitized_data);
                
                error_log("ENNU Biomarker Manager: Saved biomarker data for user {$user_id}, biomarker {$biomarker_name}");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("ENNU Biomarker Manager Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get biomarker data for a user
     * 
     * @param int $user_id User ID
     * @param string $biomarker_name Optional specific biomarker
     * @return array Biomarker data
     */
    public function get_user_biomarkers($user_id, $biomarker_name = null) {
        if (!$user_id) {
            return array();
        }
        
        if ($biomarker_name) {
            // Get specific biomarker
            $meta_key = 'ennu_biomarker_' . sanitize_text_field($biomarker_name);
            $data = get_user_meta($user_id, $meta_key, true);
            return is_array($data) ? $data : array();
        } else {
            // Get all biomarkers
            $all_biomarkers = array();
            $user_meta = get_user_meta($user_id);
            
            foreach ($user_meta as $key => $value) {
                if (strpos($key, 'ennu_biomarker_') === 0 && $key !== 'ennu_biomarker_' . $biomarker_name . '_last_tested') {
                    $biomarker_name = str_replace('ennu_biomarker_', '', $key);
                    $data = maybe_unserialize($value[0]);
                    if (is_array($data)) {
                        $all_biomarkers[$biomarker_name] = $data;
                    }
                }
            }
            
            return $all_biomarkers;
        }
    }
    
    /**
     * Get latest biomarker test for a user
     * 
     * @param int $user_id User ID
     * @param string $biomarker_name Biomarker name
     * @return array|null Latest test data or null
     */
    public function get_latest_biomarker_test($user_id, $biomarker_name) {
        $biomarker_data = $this->get_user_biomarkers($user_id, $biomarker_name);
        
        if (empty($biomarker_data)) {
            return null;
        }
        
        // Sort by date_tested (newest first)
        usort($biomarker_data, function($a, $b) {
            return strtotime($b['date_tested']) - strtotime($a['date_tested']);
        });
        
        return $biomarker_data[0];
    }
    
    /**
     * Check if biomarker test is outdated
     * 
     * @param array $biomarker_data Biomarker test data
     * @return bool True if outdated
     */
    public function is_test_outdated($biomarker_data) {
        if (empty($biomarker_data['date_tested'])) {
            return true;
        }
        
        $test_date = strtotime($biomarker_data['date_tested']);
        $current_date = time();
        $days_since_test = ($current_date - $test_date) / DAY_IN_SECONDS;
        
        // Get expiration rule for this biomarker
        $biomarker_name = $this->get_biomarker_name_from_data($biomarker_data);
        $expiration_days = $this->expiration_rules[$biomarker_name] ?? $this->expiration_rules['default'];
        
        return $days_since_test > $expiration_days;
    }
    
    /**
     * Validate and sanitize biomarker data
     * 
     * @param array $data Raw biomarker data
     * @return array Sanitized data
     */
    private function validate_and_sanitize_biomarker_data($data) {
        $sanitized = array();
        
        // Required fields
        $required_fields = array('value', 'unit', 'date_tested');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Sanitize each field
        $sanitized['value'] = floatval($data['value']);
        $sanitized['unit'] = sanitize_text_field($data['unit']);
        $sanitized['reference_range_low'] = isset($data['reference_range_low']) ? floatval($data['reference_range_low']) : '';
        $sanitized['reference_range_high'] = isset($data['reference_range_high']) ? floatval($data['reference_range_high']) : '';
        $sanitized['reference_range_unit'] = isset($data['reference_range_unit']) ? sanitize_text_field($data['reference_range_unit']) : '';
        $sanitized['date_tested'] = sanitize_text_field($data['date_tested']);
        $sanitized['lab_name'] = isset($data['lab_name']) ? sanitize_text_field($data['lab_name']) : '';
        $sanitized['lab_id'] = isset($data['lab_id']) ? sanitize_text_field($data['lab_id']) : '';
        $sanitized['test_method'] = isset($data['test_method']) ? sanitize_text_field($data['test_method']) : '';
        $sanitized['notes'] = isset($data['notes']) ? sanitize_textarea_field($data['notes']) : '';
        $sanitized['source'] = isset($data['source']) ? sanitize_text_field($data['source']) : 'manual';
        $sanitized['confidence_score'] = isset($data['confidence_score']) ? intval($data['confidence_score']) : 0;
        $sanitized['loinc_code'] = isset($data['loinc_code']) ? sanitize_text_field($data['loinc_code']) : '';
        $sanitized['specimen_type'] = isset($data['specimen_type']) ? sanitize_text_field($data['specimen_type']) : '';
        $sanitized['biomarker_type'] = isset($data['biomarker_type']) ? sanitize_text_field($data['biomarker_type']) : '';
        
        // Determine status based on reference ranges
        $sanitized['status'] = $this->determine_biomarker_status($sanitized);
        
        return $sanitized;
    }
    
    /**
     * Determine biomarker status based on reference ranges
     * 
     * @param array $data Biomarker data
     * @return string Status (low, normal, high, critical)
     */
    private function determine_biomarker_status($data) {
        if (empty($data['reference_range_low']) || empty($data['reference_range_high'])) {
            return 'unknown';
        }
        
        $value = $data['value'];
        $low = $data['reference_range_low'];
        $high = $data['reference_range_high'];
        
        if ($value < $low) {
            return 'low';
        } elseif ($value > $high) {
            return 'high';
        } else {
            return 'normal';
        }
    }
    
    /**
     * Get biomarker name from data
     * 
     * @param array $biomarker_data Biomarker data
     * @return string Biomarker name
     */
    private function get_biomarker_name_from_data($biomarker_data) {
        // This would need to be implemented based on how biomarker names are stored
        // For now, return a default
        return 'default';
    }
    
    /**
     * Get biomarker recommendations based on symptoms
     * 
     * @param int $user_id User ID
     * @return array Recommendations
     */
    public function get_biomarker_recommendations($user_id) {
        $recommendations = array();
        
        // Get user symptoms
        $symptoms = $this->get_user_symptoms($user_id);
        
        // Get existing biomarkers
        $existing_biomarkers = $this->get_user_biomarkers($user_id);
        
        // Get symptom-biomarker correlations
        $correlations = $this->get_symptom_biomarker_correlations();
        
        foreach ($symptoms as $symptom) {
            if (isset($correlations[$symptom])) {
                foreach ($correlations[$symptom] as $biomarker) {
                    $latest_test = $this->get_latest_biomarker_test($user_id, $biomarker);
                    
                    if (!$latest_test || $this->is_test_outdated($latest_test)) {
                        $recommendations[] = array(
                            'biomarker' => $biomarker,
                            'reason' => $symptom,
                            'priority' => $this->calculate_priority($symptom, $biomarker),
                            'last_tested' => $latest_test ? $latest_test['date_tested'] : null,
                            'status' => $latest_test ? $latest_test['status'] : 'not_tested'
                        );
                    }
                }
            }
        }
        
        // Sort by priority
        usort($recommendations, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        return $recommendations;
    }
    
    /**
     * Get user symptoms from centralized symptoms system
     * 
     * @param int $user_id User ID
     * @return array Symptoms
     */
    private function get_user_symptoms($user_id) {
        $centralized_symptoms = get_user_meta($user_id, 'ennu_centralized_symptoms', true);
        
        if (!is_array($centralized_symptoms)) {
            return array();
        }
        
        $symptoms = array();
        foreach ($centralized_symptoms as $category => $symptom_list) {
            if (is_array($symptom_list)) {
                foreach ($symptom_list as $symptom => $data) {
                    $symptoms[] = $symptom;
                }
            }
        }
        
        return $symptoms;
    }
    
    /**
     * Get symptom-biomarker correlations
     * 
     * @return array Correlations
     */
    private function get_symptom_biomarker_correlations() {
        // This would load from a configuration file or database
        // For now, return a basic mapping
        return array(
            'Fatigue' => array('Testosterone', 'Vitamin D', 'Vitamin B12', 'Iron', 'Cortisol'),
            'Anxiety' => array('Cortisol', 'Testosterone', 'Vitamin D', 'Magnesium'),
            'Low Libido' => array('Testosterone', 'Estradiol', 'Prolactin'),
            'Weight Gain' => array('Testosterone', 'Cortisol', 'Insulin', 'Thyroid Panel'),
            'Brain Fog' => array('Vitamin B12', 'Vitamin D', 'Homocysteine', 'Thyroid Panel'),
            'Sleep Problems' => array('Cortisol', 'Melatonin', 'Testosterone'),
            'Joint Pain' => array('Vitamin D', 'CRP', 'ESR', 'Uric Acid'),
            'Depression' => array('Vitamin D', 'Vitamin B12', 'Cortisol', 'Testosterone')
        );
    }
    
    /**
     * Calculate recommendation priority
     * 
     * @param string $symptom Symptom name
     * @param string $biomarker Biomarker name
     * @return int Priority score
     */
    private function calculate_priority($symptom, $biomarker) {
        // Base priority scores
        $symptom_priorities = array(
            'Fatigue' => 5,
            'Anxiety' => 4,
            'Low Libido' => 3,
            'Weight Gain' => 4,
            'Brain Fog' => 4,
            'Sleep Problems' => 3,
            'Joint Pain' => 2,
            'Depression' => 5
        );
        
        $biomarker_priorities = array(
            'Testosterone' => 5,
            'Vitamin D' => 4,
            'Cortisol' => 4,
            'Thyroid Panel' => 4,
            'Vitamin B12' => 3,
            'Iron' => 3,
            'Insulin' => 3,
            'CRP' => 2
        );
        
        $symptom_priority = $symptom_priorities[$symptom] ?? 1;
        $biomarker_priority = $biomarker_priorities[$biomarker] ?? 1;
        
        return $symptom_priority * $biomarker_priority;
    }
    
    /**
     * Import lab results from CSV
     * 
     * @param int $user_id User ID
     * @param string $file_path CSV file path
     * @return array Import results
     */
    public function import_lab_results_csv($user_id, $file_path) {
        $results = array(
            'success' => 0,
            'failed' => 0,
            'errors' => array()
        );
        
        if (!file_exists($file_path)) {
            $results['errors'][] = 'File not found: ' . $file_path;
            return $results;
        }
        
        $handle = fopen($file_path, 'r');
        if (!$handle) {
            $results['errors'][] = 'Could not open file: ' . $file_path;
            return $results;
        }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                $biomarker_data = $this->parse_csv_row($header, $data);
                if ($this->save_biomarker_data($user_id, $biomarker_data['biomarker_name'], $biomarker_data)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }
        }
        
        fclose($handle);
        return $results;
    }
    
    /**
     * Parse CSV row into biomarker data
     * 
     * @param array $header CSV header
     * @param array $row CSV row data
     * @return array Biomarker data
     */
    private function parse_csv_row($header, $row) {
        $data = array();
        
        // Map CSV columns to biomarker fields
        $column_mapping = array(
            'biomarker_name' => 'Biomarker',
            'value' => 'Value',
            'unit' => 'Unit',
            'reference_range_low' => 'Reference Low',
            'reference_range_high' => 'Reference High',
            'date_tested' => 'Date Tested',
            'lab_name' => 'Lab Name',
            'status' => 'Status'
        );
        
        foreach ($column_mapping as $field => $column_name) {
            $index = array_search($column_name, $header);
            if ($index !== false && isset($row[$index])) {
                $data[$field] = $row[$index];
            }
        }
        
        if (empty($data['biomarker_name'])) {
            throw new Exception('Missing biomarker name in CSV row');
        }
        
        $biomarker_name = $data['biomarker_name'];
        unset($data['biomarker_name']);
        
        return array(
            'biomarker_name' => $biomarker_name,
            'data' => $data
        );
    }
}
