<?php
/**
 * ENNU Life Biomarker Panels Management Class
 *
 * @package ENNU_Life
 * @version 2.1
 * @description Manages biomarker panel configuration and provides easy access to panel data
 * @date 2025-07-22
 */

class ENNU_Biomarker_Panels {
    
    private static $instance = null;
    private $panels_config = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_panels_config();
    }
    
    /**
     * Load the panels configuration from the config file
     */
    private function load_panels_config() {
        $config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/biomarker-panels.php';
        if (file_exists($config_file)) {
            $this->panels_config = include $config_file;
        } else {
            // Fallback configuration if file doesn't exist
            $this->panels_config = $this->get_fallback_config();
        }
    }
    
    /**
     * Get fallback configuration if main config file is missing
     */
    private function get_fallback_config() {
        return array(
            'panels' => array(
                'foundation_panel' => array(
                    'name' => 'The Foundation Panel',
                    'price' => 599,
                    'biomarker_count' => 50,
                    'included_in_membership' => true
                )
            ),
            'pricing_summary' => array(
                'total_biomarkers' => 50,
                'total_value' => 599,
                'membership_price' => 147
            )
        );
    }
    
    /**
     * Get all panels configuration
     */
    public function get_all_panels() {
        return isset($this->panels_config['panels']) ? $this->panels_config['panels'] : array();
    }
    
    /**
     * Get a specific panel by key
     */
    public function get_panel($panel_key) {
        $panels = $this->get_all_panels();
        return isset($panels[$panel_key]) ? $panels[$panel_key] : null;
    }
    
    /**
     * Get panel pricing summary
     */
    public function get_pricing_summary() {
        return isset($this->panels_config['pricing_summary']) ? $this->panels_config['pricing_summary'] : array();
    }
    
    /**
     * Get business model configuration
     */
    public function get_business_model() {
        return isset($this->panels_config['business_model']) ? $this->panels_config['business_model'] : array();
    }
    
    /**
     * Get total system value
     */
    public function get_total_system_value() {
        $pricing_summary = $this->get_pricing_summary();
        return isset($pricing_summary['total_value']) ? $pricing_summary['total_value'] : 0;
    }
    
    /**
     * Get total biomarker count
     */
    public function get_total_biomarker_count() {
        $pricing_summary = $this->get_pricing_summary();
        return isset($pricing_summary['total_biomarkers']) ? $pricing_summary['total_biomarkers'] : 0;
    }
    
    /**
     * Get foundation panel value
     */
    public function get_foundation_panel_value() {
        $pricing_summary = $this->get_pricing_summary();
        return isset($pricing_summary['foundation_panel_value']) ? $pricing_summary['foundation_panel_value'] : 599;
    }
    
    /**
     * Get membership price
     */
    public function get_membership_price() {
        $pricing_summary = $this->get_pricing_summary();
        return isset($pricing_summary['membership_price']) ? $pricing_summary['membership_price'] : 147;
    }
    
    /**
     * Get panels included in membership
     */
    public function get_membership_panels() {
        $panels = $this->get_all_panels();
        $membership_panels = array();
        
        foreach ($panels as $key => $panel) {
            if (isset($panel['included_in_membership']) && $panel['included_in_membership']) {
                $membership_panels[$key] = $panel;
            }
        }
        
        return $membership_panels;
    }
    
    /**
     * Get addon panels (not included in membership)
     */
    public function get_addon_panels() {
        $panels = $this->get_all_panels();
        $addon_panels = array();
        
        foreach ($panels as $key => $panel) {
            if (!isset($panel['included_in_membership']) || !$panel['included_in_membership']) {
                $addon_panels[$key] = $panel;
            }
        }
        
        return $addon_panels;
    }
    
    /**
     * Get panel by biomarker
     */
    public function get_panel_by_biomarker($biomarker_key) {
        $panels = $this->get_all_panels();
        
        foreach ($panels as $panel_key => $panel) {
            if (isset($panel['biomarkers']) && in_array($biomarker_key, $panel['biomarkers'])) {
                return array(
                    'panel_key' => $panel_key,
                    'panel' => $panel
                );
            }
        }
        
        return null;
    }
    
    /**
     * Get all biomarkers in a panel
     */
    public function get_panel_biomarkers($panel_key) {
        $panel = $this->get_panel($panel_key);
        return isset($panel['biomarkers']) ? $panel['biomarkers'] : array();
    }
    
    /**
     * Get panel price
     */
    public function get_panel_price($panel_key) {
        $panel = $this->get_panel($panel_key);
        return isset($panel['price']) ? $panel['price'] : 0;
    }
    
    /**
     * Get panel name
     */
    public function get_panel_name($panel_key) {
        $panel = $this->get_panel($panel_key);
        return isset($panel['name']) ? $panel['name'] : '';
    }
    
    /**
     * Get panel description
     */
    public function get_panel_description($panel_key) {
        $panel = $this->get_panel($panel_key);
        return isset($panel['description']) ? $panel['description'] : '';
    }
    
    /**
     * Check if panel is included in membership
     */
    public function is_panel_included_in_membership($panel_key) {
        $panel = $this->get_panel($panel_key);
        return isset($panel['included_in_membership']) ? $panel['included_in_membership'] : false;
    }
    
    /**
     * Get formatted price for display
     */
    public function get_formatted_price($price) {
        return '$' . number_format($price);
    }
    
    /**
     * Get panel display data for UI
     */
    public function get_panel_display_data($panel_key) {
        $panel = $this->get_panel($panel_key);
        if (!$panel) {
            return null;
        }
        
        return array(
            'name' => $panel['name'],
            'display_name' => isset($panel['display_name']) ? $panel['display_name'] : $panel['name'],
            'description' => $panel['description'],
            'price' => $panel['price'],
            'formatted_price' => $this->get_formatted_price($panel['price']),
            'biomarker_count' => $panel['biomarker_count'],
            'included_in_membership' => isset($panel['included_in_membership']) ? $panel['included_in_membership'] : false,
            'purpose' => isset($panel['purpose']) ? $panel['purpose'] : '',
            'long_description' => isset($panel['long_description']) ? $panel['long_description'] : ''
        );
    }
    
    /**
     * Get all panels display data for UI
     */
    public function get_all_panels_display_data() {
        $panels = $this->get_all_panels();
        $display_data = array();
        
        foreach ($panels as $key => $panel) {
            $display_data[$key] = $this->get_panel_display_data($key);
        }
        
        return $display_data;
    }
    
    /**
     * Get membership tiers
     */
    public function get_membership_tiers() {
        $business_model = $this->get_business_model();
        return isset($business_model['membership_tiers']) ? $business_model['membership_tiers'] : array();
    }
    
    /**
     * Get membership tier by key
     */
    public function get_membership_tier($tier_key) {
        $tiers = $this->get_membership_tiers();
        return isset($tiers[$tier_key]) ? $tiers[$tier_key] : null;
    }
    
    /**
     * Reload configuration (useful for testing or updates)
     */
    public function reload_config() {
        $this->load_panels_config();
    }
} 