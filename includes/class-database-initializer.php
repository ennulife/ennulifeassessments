<?php
/**
 * Database Initializer
 * 
 * Ensures all required database options are properly initialized
 *
 * @package ENNU_Life
 * @version 64.7.1
 */

class ENNU_Database_Initializer {
	
	/**
	 * Required options with default values
	 */
	private $required_options = array(
		'ennu_hubspot_api_key' => '',
		'ennu_hubspot_access_token' => '',
		'ennu_hubspot_refresh_token' => '',
		'ennu_hubspot_client_id' => '',
		'ennu_hubspot_client_secret' => '',
		'ennu_hubspot_failed_syncs' => array(),
		'ennu_registered_fields' => array(),
		'ennu_hubspot_property_groups' => array(),
		'ennu_hubspot_custom_objects' => array(),
		'ennu_hubspot_field_mappings' => array(),
		'ennu_hubspot_sync_settings' => array(
			'auto_sync' => true,
			'sync_on_assessment_completion' => true,
			'sync_on_user_registration' => true,
			'retry_failed_syncs' => true,
			'max_retry_attempts' => 3
		)
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action('init', array($this, 'initialize_options'));
	}
	
	/**
	 * Initialize all required options
	 */
	public function initialize_options() {
		foreach ($this->required_options as $option_name => $default_value) {
			if (get_option($option_name) === false) {
				add_option($option_name, $default_value);
				// REMOVED: error_log("ENNU Database Initializer: Created option '{$option_name}' with default value");
			}
		}
	}
	
	/**
	 * Validate all options
	 */
	public function validate_options() {
		$validation_results = array();
		
		foreach ($this->required_options as $option_name => $default_value) {
			$option_value = get_option($option_name);
			
			if ($option_value === false) {
				$validation_results[$option_name] = array(
					'status' => 'missing',
					'message' => 'Option does not exist'
				);
			} else {
				$validation_results[$option_name] = array(
					'status' => 'exists',
					'value_type' => gettype($option_value),
					'is_empty' => empty($option_value)
				);
			}
		}
		
		return $validation_results;
	}
	
	/**
	 * Reset all options to defaults
	 */
	public function reset_options() {
		foreach ($this->required_options as $option_name => $default_value) {
			update_option($option_name, $default_value);
		}
		
		// REMOVED: error_log('ENNU Database Initializer: Reset all options to default values');
	}
	
	/**
	 * Get option value with validation
	 */
	public function get_option($option_name, $default = null) {
		$value = get_option($option_name, $default);
		
		if ($value === false && isset($this->required_options[$option_name])) {
			// Option doesn't exist, create it with default value
			$default_value = $this->required_options[$option_name];
			add_option($option_name, $default_value);
			return $default_value;
		}
		
		return $value;
	}
	
	/**
	 * Set option value with validation
	 */
	public function set_option($option_name, $value) {
		if (isset($this->required_options[$option_name])) {
			update_option($option_name, $value);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if HubSpot credentials are configured
	 */
	public function has_hubspot_credentials() {
		$api_key = $this->get_option('ennu_hubspot_api_key');
		$access_token = $this->get_option('ennu_hubspot_access_token');
		
		return !empty($api_key) || !empty($access_token);
	}
	
	/**
	 * Get HubSpot sync settings
	 */
	public function get_sync_settings() {
		return $this->get_option('ennu_hubspot_sync_settings', array());
	}
	
	/**
	 * Update HubSpot sync settings
	 */
	public function update_sync_settings($settings) {
		$current_settings = $this->get_sync_settings();
		$updated_settings = array_merge($current_settings, $settings);
		
		return $this->set_option('ennu_hubspot_sync_settings', $updated_settings);
	}
	
	/**
	 * Log failed sync
	 */
	public function log_failed_sync($user_id, $assessment_type, $error_message) {
		$failed_syncs = $this->get_option('ennu_hubspot_failed_syncs', array());
		
		$failed_syncs[] = array(
			'user_id' => $user_id,
			'assessment_type' => $assessment_type,
			'error_message' => $error_message,
			'timestamp' => current_time('mysql'),
			'retry_count' => 0
		);
		
		// Keep only last 100 failed syncs
		if (count($failed_syncs) > 100) {
			$failed_syncs = array_slice($failed_syncs, -100);
		}
		
		return $this->set_option('ennu_hubspot_failed_syncs', $failed_syncs);
	}
	
	/**
	 * Get failed syncs
	 */
	public function get_failed_syncs() {
		return $this->get_option('ennu_hubspot_failed_syncs', array());
	}
	
	/**
	 * Clear failed syncs
	 */
	public function clear_failed_syncs() {
		return $this->set_option('ennu_hubspot_failed_syncs', array());
	}
} 