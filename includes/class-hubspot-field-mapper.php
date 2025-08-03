<?php
/**
 * HubSpot Field Mapper - Comprehensive Field Mapping Solution
 * 
 * Maps WordPress field names to actual HubSpot field names
 * 
 * @package ENNU Life Assessments
 * @since 64.18.0
 */

class ENNU_HubSpot_Field_Mapper {
    
    /**
     * Field mapping from WordPress to HubSpot
     */
    private static $field_mapping = array(
        // Global fields mapping
        'ennuage' => 'ennu_global_age',
        'ennuweight' => 'ennu_global_weight',
        'ennugender' => 'ennu_global_gender',
        'ennudateofbirth' => 'ennu_global_date_of_birth',
        'ennuheight' => 'ennu_global_height',
        'ennubmi' => 'ennu_global_bmi',
        
        // Life score fields mapping
        'ennu_life_score' => 'ennu_life_score',
        'ennu_pillar_mind_score' => 'ennu_pillar_mind_score',
        'ennu_pillar_body_score' => 'ennu_pillar_body_score',
        'ennu_pillar_lifestyle_score' => 'ennu_pillar_lifestyle_score',
        'ennu_pillar_aesthetics_score' => 'ennu_pillar_aesthetics_score',
        
        // Weight Loss assessment mapping
        'wl_overall_score' => 'wl_overall_score',
        'wl_mind_score' => 'wl_pillar_mind_score',
        'wl_body_score' => 'wl_pillar_body_score',
        'wl_lifestyle_score' => 'wl_pillar_lifestyle_score',
        'wl_aesthetics_score' => 'wl_pillar_aesthetics_score',
        
        // Weight Loss category scores
        'wl_category_behavioral_patterns_score' => 'wl_category_behavioral_patterns_score',
        'wl_category_lifestyle_factors_score' => 'wl_category_lifestyle_factors_score',
        'wl_category_medical_factors_score' => 'wl_category_medical_factors_score',
        'wl_category_motivation_goals_score' => 'wl_category_motivation_goals_score',
        'wl_category_nutrition_score' => 'wl_category_nutrition_score',
        'wl_category_physical_activity_score' => 'wl_category_physical_activity_score',
        'wl_category_psychological_factors_score' => 'wl_category_psychological_factors_score',
        'wl_category_social_support_score' => 'wl_category_social_support_score',
        'wl_category_weight_loss_history_score' => 'wl_category_weight_loss_history_score',
        
        // Weight Loss questions (these are already correct)
        'wl_q2' => 'wl_q2',
        'wl_q3' => 'wl_q3',
        'wl_q4' => 'wl_q4',
        'wl_q5' => 'wl_q5',
        'wl_q6' => 'wl_q6',
        'wl_q7' => 'wl_q7',
        'wl_q8' => 'wl_q8',
        'wl_q9' => 'wl_q9',
        'wl_q10' => 'wl_q10',
        'wl_q11' => 'wl_q11',
        'wl_q12' => 'wl_q12',
        'wl_q13' => 'wl_q13',
        
        // Custom object fields (these are already correct)
        'assessment_type' => 'assessment_type',
        'user_email' => 'user_email',
        'user_id' => 'user_id',
        'submitted_at' => 'submitted_at',
        'overall_score' => 'overall_score'
    );
    
    /**
     * Reverse mapping from HubSpot to WordPress
     */
    private static $reverse_mapping = array();
    
    /**
     * Initialize the field mapper
     */
    public static function init() {
        // Build reverse mapping
        self::$reverse_mapping = array_flip(self::$field_mapping);
    }
    
    /**
     * Map WordPress field name to HubSpot field name
     *
     * @param string $wordpress_field WordPress field name
     * @return string HubSpot field name
     */
    public static function map_to_hubspot($wordpress_field) {
        return isset(self::$field_mapping[$wordpress_field]) 
            ? self::$field_mapping[$wordpress_field] 
            : $wordpress_field;
    }
    
    /**
     * Map HubSpot field name to WordPress field name
     *
     * @param string $hubspot_field HubSpot field name
     * @return string WordPress field name
     */
    public static function map_to_wordpress($hubspot_field) {
        return isset(self::$reverse_mapping[$hubspot_field]) 
            ? self::$reverse_mapping[$hubspot_field] 
            : $hubspot_field;
    }
    
    /**
     * Map an array of WordPress fields to HubSpot fields
     *
     * @param array $wordpress_fields Array of WordPress field names
     * @return array Array of HubSpot field names
     */
    public static function map_array_to_hubspot($wordpress_fields) {
        $hubspot_fields = array();
        foreach ($wordpress_fields as $field) {
            $hubspot_fields[] = self::map_to_hubspot($field);
        }
        return $hubspot_fields;
    }
    
    /**
     * Map an array of data from WordPress field names to HubSpot field names
     *
     * @param array $data Array with WordPress field names as keys
     * @return array Array with HubSpot field names as keys
     */
    public static function map_data_to_hubspot($data) {
        $mapped_data = array();
        foreach ($data as $wordpress_field => $value) {
            $hubspot_field = self::map_to_hubspot($wordpress_field);
            $mapped_data[$hubspot_field] = $value;
        }
        return $mapped_data;
    }
    
    /**
     * Get all mapped field names
     *
     * @return array Array of all mapped field names
     */
    public static function get_all_mapped_fields() {
        return self::$field_mapping;
    }
    
    /**
     * Get all HubSpot field names
     *
     * @return array Array of all HubSpot field names
     */
    public static function get_all_hubspot_fields() {
        return array_values(self::$field_mapping);
    }
    
    /**
     * Get all WordPress field names
     *
     * @return array Array of all WordPress field names
     */
    public static function get_all_wordpress_fields() {
        return array_keys(self::$field_mapping);
    }
    
    /**
     * Check if a field has a mapping
     *
     * @param string $field_name Field name to check
     * @return bool True if field has mapping
     */
    public static function has_mapping($field_name) {
        return isset(self::$field_mapping[$field_name]);
    }
    
    /**
     * Get mapping statistics
     *
     * @return array Mapping statistics
     */
    public static function get_mapping_stats() {
        return array(
            'total_mappings' => count(self::$field_mapping),
            'wordpress_fields' => count(array_keys(self::$field_mapping)),
            'hubspot_fields' => count(array_unique(array_values(self::$field_mapping))),
            'global_fields' => count(array_filter(array_keys(self::$field_mapping), function($field) {
                return strpos($field, 'ennu') === 0;
            })),
            'assessment_fields' => count(array_filter(array_keys(self::$field_mapping), function($field) {
                return strpos($field, 'wl_') === 0;
            }))
        );
    }
    
    /**
     * Validate field mapping
     *
     * @return array Validation results
     */
    public static function validate_mapping() {
        $results = array(
            'valid' => true,
            'errors' => array(),
            'warnings' => array()
        );
        
        // Check for duplicate HubSpot field names
        $hubspot_fields = array_values(self::$field_mapping);
        $duplicates = array_diff_assoc($hubspot_fields, array_unique($hubspot_fields));
        
        if (!empty($duplicates)) {
            $results['valid'] = false;
            $results['errors'][] = 'Duplicate HubSpot field names found: ' . implode(', ', $duplicates);
        }
        
        // Check for empty field names
        foreach (self::$field_mapping as $wordpress_field => $hubspot_field) {
            if (empty($wordpress_field)) {
                $results['valid'] = false;
                $results['errors'][] = 'Empty WordPress field name found';
            }
            if (empty($hubspot_field)) {
                $results['valid'] = false;
                $results['errors'][] = 'Empty HubSpot field name found';
            }
        }
        
        return $results;
    }
}

// Initialize the field mapper
ENNU_HubSpot_Field_Mapper::init(); 