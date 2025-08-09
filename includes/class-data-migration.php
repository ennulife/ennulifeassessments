<?php
/**
 * Data Migration Service for ENNU Life Assessments
 * 
 * Handles migration between legacy and new data formats
 * Ensures backward compatibility while moving to new structure
 * 
 * @package ENNU_Life
 * @since 64.70.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Data_Migration {
    
    /**
     * Version tracking for migrations
     */
    const MIGRATION_VERSION_KEY = 'ennu_data_migration_version';
    const CURRENT_VERSION = '2.0.0';
    
    /**
     * Instance
     */
    private static $instance = null;
    
    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'check_and_run_migrations'), 5);
    }
    
    /**
     * Check if migrations are needed and run them
     */
    public function check_and_run_migrations() {
        $current_version = get_option(self::MIGRATION_VERSION_KEY, '0.0.0');
        
        if (version_compare($current_version, self::CURRENT_VERSION, '<')) {
            $this->run_migrations($current_version);
            update_option(self::MIGRATION_VERSION_KEY, self::CURRENT_VERSION);
        }
    }
    
    /**
     * Run necessary migrations based on version
     */
    private function run_migrations($from_version) {
        error_log('ENNU Data Migration: Starting migration from version ' . $from_version);
        
        // Migration 1.0.0: Fix assessment data formats
        if (version_compare($from_version, '1.0.0', '<')) {
            $this->migrate_assessment_data_format();
        }
        
        // Migration 1.1.0: Fix biomarker data formats
        if (version_compare($from_version, '1.1.0', '<')) {
            $this->migrate_biomarker_data_format();
        }
        
        // Migration 1.2.0: Fix symptom data formats
        if (version_compare($from_version, '1.2.0', '<')) {
            $this->migrate_symptom_data_format();
        }
        
        // Migration 2.0.0: Consolidate data structures
        if (version_compare($from_version, '2.0.0', '<')) {
            $this->consolidate_data_structures();
        }
        
        error_log('ENNU Data Migration: Completed migration to version ' . self::CURRENT_VERSION);
    }
    
    /**
     * Migrate assessment data from legacy to new format
     */
    private function migrate_assessment_data_format() {
        global $wpdb;
        
        // Get all users with assessment data
        $users = $wpdb->get_results("
            SELECT DISTINCT user_id 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_assessment_responses_%'
        ");
        
        foreach ($users as $user) {
            $this->migrate_user_assessment_data($user->user_id);
        }
    }
    
    /**
     * Migrate individual user's assessment data
     */
    private function migrate_user_assessment_data($user_id) {
        $assessment_types = [
            'hair', 'weight_loss', 'health', 'skin', 'hormone',
            'sleep', 'testosterone', 'menopause', 'ed_treatment',
            'health_optimization', 'welcome'
        ];
        
        foreach ($assessment_types as $type) {
            $meta_key = 'ennu_assessment_responses_' . $type;
            $data = get_user_meta($user_id, $meta_key, true);
            
            if (!empty($data)) {
                $migrated_data = $this->convert_assessment_format($data, $type);
                if ($migrated_data !== $data) {
                    update_user_meta($user_id, $meta_key, $migrated_data);
                    
                    // Also store in new format key for dual support
                    $new_key = 'ennu_assessment_data_' . $type;
                    update_user_meta($user_id, $new_key, $migrated_data);
                }
            }
        }
    }
    
    /**
     * Convert assessment data to unified format
     */
    private function convert_assessment_format($data, $assessment_type) {
        // Handle different data formats
        $unified_format = [
            'version' => '2.0',
            'assessment_type' => $assessment_type,
            'timestamp' => $data['timestamp'] ?? current_time('mysql'),
            'responses' => [],
            'scores' => [],
            'pillar_scores' => [],
            'metadata' => []
        ];
        
        // Handle legacy format with 'calculated_score'
        if (isset($data['calculated_score'])) {
            $unified_format['scores']['overall'] = $data['calculated_score'];
            $unified_format['scores']['legacy'] = true;
        }
        
        // Handle new format with 'score'
        if (isset($data['score'])) {
            $unified_format['scores']['overall'] = $data['score'];
        }
        
        // Handle pillar scores
        if (isset($data['pillar_scores'])) {
            $unified_format['pillar_scores'] = $data['pillar_scores'];
        } elseif (isset($data['pillars'])) {
            $unified_format['pillar_scores'] = $data['pillars'];
        }
        
        // Handle responses
        if (isset($data['responses'])) {
            $unified_format['responses'] = $data['responses'];
        } elseif (isset($data['form_data'])) {
            $unified_format['responses'] = $data['form_data'];
        }
        
        // Handle category scores
        if (isset($data['category_scores'])) {
            $unified_format['scores']['categories'] = $data['category_scores'];
        }
        
        // Preserve any additional data
        $unified_format['metadata']['original_format'] = $data;
        
        return $unified_format;
    }
    
    /**
     * Migrate biomarker data format
     */
    private function migrate_biomarker_data_format() {
        global $wpdb;
        
        $users = $wpdb->get_results("
            SELECT DISTINCT user_id 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_biomarker%'
        ");
        
        foreach ($users as $user) {
            $this->migrate_user_biomarker_data($user->user_id);
        }
    }
    
    /**
     * Migrate individual user's biomarker data
     */
    private function migrate_user_biomarker_data($user_id) {
        // Get all biomarker-related meta
        $biomarker_data = get_user_meta($user_id, 'ennu_biomarker_data', true);
        $user_biomarkers = get_user_meta($user_id, 'ennu_user_biomarkers', true);
        
        if (empty($biomarker_data) && empty($user_biomarkers)) {
            return;
        }
        
        // Merge and standardize data
        $unified_biomarkers = [];
        
        // Process ennu_biomarker_data
        if (!empty($biomarker_data) && is_array($biomarker_data)) {
            foreach ($biomarker_data as $biomarker_name => $data) {
                $unified_biomarkers[$biomarker_name] = $this->standardize_biomarker_entry($data);
            }
        }
        
        // Process ennu_user_biomarkers (may override)
        if (!empty($user_biomarkers) && is_array($user_biomarkers)) {
            foreach ($user_biomarkers as $biomarker_name => $data) {
                $standardized = $this->standardize_biomarker_entry($data);
                if (!isset($unified_biomarkers[$biomarker_name]) || 
                    strtotime($standardized['test_date']) > strtotime($unified_biomarkers[$biomarker_name]['test_date'])) {
                    $unified_biomarkers[$biomarker_name] = $standardized;
                }
            }
        }
        
        // Save unified data
        if (!empty($unified_biomarkers)) {
            update_user_meta($user_id, 'ennu_unified_biomarkers', $unified_biomarkers);
        }
    }
    
    /**
     * Standardize biomarker entry format
     */
    private function standardize_biomarker_entry($data) {
        return [
            'value' => $data['value'] ?? $data['result'] ?? null,
            'unit' => $data['unit'] ?? '',
            'test_date' => $data['test_date'] ?? $data['date'] ?? current_time('mysql'),
            'source' => $data['source'] ?? 'manual',
            'status' => $data['status'] ?? null,
            'reference_range' => $data['reference_range'] ?? null,
            'notes' => $data['notes'] ?? ''
        ];
    }
    
    /**
     * Migrate symptom data format
     */
    private function migrate_symptom_data_format() {
        global $wpdb;
        
        $users = $wpdb->get_results("
            SELECT DISTINCT user_id 
            FROM {$wpdb->usermeta} 
            WHERE meta_key IN ('ennu_symptoms', 'ennu_centralized_symptoms')
        ");
        
        foreach ($users as $user) {
            $this->migrate_user_symptom_data($user->user_id);
        }
    }
    
    /**
     * Migrate individual user's symptom data
     */
    private function migrate_user_symptom_data($user_id) {
        $old_symptoms = get_user_meta($user_id, 'ennu_symptoms', true);
        $centralized_symptoms = get_user_meta($user_id, 'ennu_centralized_symptoms', true);
        
        if (empty($old_symptoms) && empty($centralized_symptoms)) {
            return;
        }
        
        $unified_symptoms = [
            'version' => '2.0',
            'symptoms' => [],
            'by_assessment' => [],
            'by_category' => [],
            'last_updated' => current_time('mysql')
        ];
        
        // Process old format
        if (!empty($old_symptoms) && is_array($old_symptoms)) {
            foreach ($old_symptoms as $symptom => $data) {
                $unified_symptoms['symptoms'][$symptom] = [
                    'name' => $symptom,
                    'severity' => $data['severity'] ?? 'moderate',
                    'frequency' => $data['frequency'] ?? 'sometimes',
                    'first_reported' => $data['date'] ?? current_time('mysql'),
                    'assessments' => $data['assessments'] ?? []
                ];
            }
        }
        
        // Merge with centralized (newer takes precedence)
        if (!empty($centralized_symptoms) && is_array($centralized_symptoms)) {
            if (isset($centralized_symptoms['symptoms'])) {
                $unified_symptoms['symptoms'] = array_merge(
                    $unified_symptoms['symptoms'],
                    $centralized_symptoms['symptoms']
                );
            }
            if (isset($centralized_symptoms['by_assessment'])) {
                $unified_symptoms['by_assessment'] = $centralized_symptoms['by_assessment'];
            }
            if (isset($centralized_symptoms['by_category'])) {
                $unified_symptoms['by_category'] = $centralized_symptoms['by_category'];
            }
        }
        
        // Save unified data
        if (!empty($unified_symptoms['symptoms'])) {
            update_user_meta($user_id, 'ennu_unified_symptoms', $unified_symptoms);
        }
    }
    
    /**
     * Consolidate all data structures
     */
    private function consolidate_data_structures() {
        // This final migration ensures all data is in the unified format
        $this->cleanup_duplicate_data();
        $this->optimize_data_storage();
    }
    
    /**
     * Clean up duplicate data entries
     */
    private function cleanup_duplicate_data() {
        global $wpdb;
        
        // Remove duplicate assessment entries
        $wpdb->query("
            DELETE m1 FROM {$wpdb->usermeta} m1
            INNER JOIN {$wpdb->usermeta} m2
            WHERE m1.umeta_id < m2.umeta_id
            AND m1.user_id = m2.user_id
            AND m1.meta_key = m2.meta_key
            AND m1.meta_key LIKE 'ennu_assessment_%'
        ");
    }
    
    /**
     * Optimize data storage
     */
    private function optimize_data_storage() {
        // Clear old transients
        global $wpdb;
        $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_ennu_%'
            AND option_value < " . (time() - DAY_IN_SECONDS)
        );
    }
    
    /**
     * Get migration status
     */
    public static function get_migration_status() {
        return [
            'current_version' => get_option(self::MIGRATION_VERSION_KEY, '0.0.0'),
            'target_version' => self::CURRENT_VERSION,
            'is_migrated' => version_compare(
                get_option(self::MIGRATION_VERSION_KEY, '0.0.0'),
                self::CURRENT_VERSION,
                '>='
            )
        ];
    }
    
    /**
     * Force re-run migrations (for debugging)
     */
    public static function force_migration() {
        delete_option(self::MIGRATION_VERSION_KEY);
        $instance = self::get_instance();
        $instance->check_and_run_migrations();
    }
}