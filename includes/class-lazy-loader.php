<?php
/**
 * Lazy Loader Class for ENNU Life Assessments
 * 
 * Implements lazy loading pattern to improve plugin initialization performance
 * 
 * @package ENNU_Life
 * @since 64.70.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Lazy_Loader {
    
    /**
     * Core dependencies that must load immediately
     */
    private static $core_dependencies = [
        'includes/class-enhanced-database.php',
        'includes/class-enhanced-admin.php',
        'includes/class-assessment-shortcodes.php',
        'includes/class-scoring-system.php',
        'includes/class-health-goals-ajax.php'
    ];
    
    /**
     * Service dependencies that can be lazy loaded
     */
    private static $service_dependencies = [
        'includes/services/class-biomarker-service.php',
        'includes/services/class-assessment-service.php',
        'includes/services/class-ajax-handler.php',
        'includes/services/class-unified-scoring-service.php',
        'includes/services/class-assessment-rendering-service.php',
        'includes/services/class-data-validation-service.php',
        'includes/services/class-progressive-data-collector.php',
        'includes/services/class-unified-api-service.php'
    ];
    
    /**
     * Admin-only dependencies
     */
    private static $admin_dependencies = [
        'includes/class-hubspot-bulk-field-creator.php',
        'includes/class-hubspot-api-manager.php',
        'includes/class-hubspot-oauth-handler.php',
        'includes/class-hubspot-admin-page.php',
        'includes/class-csv-biomarker-importer.php',
        'includes/class-user-csv-import-shortcode.php',
        'includes/class-slack-admin.php'
    ];
    
    /**
     * Frontend-only dependencies
     */
    private static $frontend_dependencies = [
        'includes/class-shortcode-manager.php',
        'includes/class-form-handler.php'
    ];
    
    /**
     * Loaded classes cache
     */
    private static $loaded_classes = [];
    
    /**
     * Load core dependencies immediately
     */
    public static function load_core() {
        $start_time = microtime(true);
        
        foreach (self::$core_dependencies as $file) {
            self::load_file($file);
        }
        
        $load_time = microtime(true) - $start_time;
        if ($load_time > 0.5) {
            error_log(sprintf('ENNU Lazy Loader: Core loading took %.4fs', $load_time));
        }
    }
    
    /**
     * Load service dependencies on demand
     */
    public static function load_services() {
        // Only load if not already loaded
        if (!empty(self::$loaded_classes['services'])) {
            return;
        }
        
        $start_time = microtime(true);
        
        foreach (self::$service_dependencies as $file) {
            self::load_file($file);
        }
        
        self::$loaded_classes['services'] = true;
        
        $load_time = microtime(true) - $start_time;
        if ($load_time > 1.0) {
            error_log(sprintf('ENNU Lazy Loader: Services loading took %.4fs', $load_time));
        }
    }
    
    /**
     * Load admin dependencies only in admin context
     */
    public static function load_admin() {
        if (!is_admin()) {
            return;
        }
        
        // Only load if not already loaded
        if (!empty(self::$loaded_classes['admin'])) {
            return;
        }
        
        foreach (self::$admin_dependencies as $file) {
            self::load_file($file);
        }
        
        self::$loaded_classes['admin'] = true;
    }
    
    /**
     * Load frontend dependencies only on frontend
     */
    public static function load_frontend() {
        if (is_admin()) {
            return;
        }
        
        // Only load if not already loaded
        if (!empty(self::$loaded_classes['frontend'])) {
            return;
        }
        
        foreach (self::$frontend_dependencies as $file) {
            self::load_file($file);
        }
        
        self::$loaded_classes['frontend'] = true;
    }
    
    /**
     * Load a specific class on demand
     */
    public static function load_class($class_name) {
        // Convert class name to file path
        $file_name = 'class-' . str_replace('_', '-', strtolower(str_replace('ENNU_', '', $class_name))) . '.php';
        
        // Search in different directories
        $search_paths = [
            'includes/',
            'includes/services/',
            'includes/admin/',
            'includes/frontend/'
        ];
        
        foreach ($search_paths as $path) {
            $file = $path . $file_name;
            if (file_exists(ENNU_LIFE_PLUGIN_PATH . $file)) {
                self::load_file($file);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Load a file with caching
     */
    private static function load_file($file) {
        $full_path = ENNU_LIFE_PLUGIN_PATH . $file;
        
        // Check if already loaded
        if (isset(self::$loaded_classes[$file])) {
            return;
        }
        
        if (file_exists($full_path)) {
            require_once $full_path;
            self::$loaded_classes[$file] = true;
        } else {
            error_log('ENNU Lazy Loader: File not found - ' . $file);
        }
    }
    
    /**
     * Get loading statistics
     */
    public static function get_stats() {
        return [
            'loaded_files' => count(self::$loaded_classes),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * Preload classes for specific page/context
     */
    public static function preload_for_context($context) {
        switch ($context) {
            case 'assessment':
                self::load_class('ENNU_Assessment_Service');
                self::load_class('ENNU_Unified_Scoring_Service');
                self::load_class('ENNU_Biomarker_Service');
                break;
                
            case 'dashboard':
                self::load_class('ENNU_Enhanced_Dashboard_Manager');
                self::load_class('ENNU_Trends_Visualization_System');
                break;
                
            case 'admin_biomarkers':
                self::load_class('ENNU_Biomarker_Manager');
                self::load_class('ENNU_CSV_Biomarker_Importer');
                break;
                
            case 'hubspot':
                self::load_class('ENNU_HubSpot_API_Manager');
                self::load_class('ENNU_HubSpot_Bulk_Field_Creator');
                break;
        }
    }
}