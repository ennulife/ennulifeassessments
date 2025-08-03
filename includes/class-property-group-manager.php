<?php
/**
 * Property Group Manager
 * 
 * Handles automatic property group creation and management for HubSpot
 *
 * @package ENNU_Life
 * @version 64.7.1
 */

class ENNU_Property_Group_Manager {
	
	/**
	 * Required property groups
	 */
	private $required_groups = array(
		'contacts' => array(
			'ennu_life_assessments' => 'ENNU Life Assessments',
			'weight_loss_assessment' => 'Weight Loss Assessment',
			'hormone_assessment' => 'Hormone Assessment',
			'sleep_assessment' => 'Sleep Assessment',
			'health_assessment' => 'Health Assessment',
			'skin_assessment' => 'Skin Assessment',
			'ed_treatment_assessment' => 'ED Treatment Assessment',
			'menopause_assessment' => 'Menopause Assessment',
			'testosterone_assessment' => 'Testosterone Assessment'
		),
		'2-47128703' => array(
			'ennu_life_assessments' => 'ENNU Life Assessments',
			'weight_loss_assessment' => 'Weight Loss Assessment',
			'hormone_assessment' => 'Hormone Assessment',
			'sleep_assessment' => 'Sleep Assessment',
			'health_assessment' => 'Health Assessment',
			'skin_assessment' => 'Skin Assessment',
			'ed_treatment_assessment' => 'ED Treatment Assessment',
			'menopause_assessment' => 'Menopause Assessment',
			'testosterone_assessment' => 'Testosterone Assessment'
		)
	);
	
	/**
	 * API Manager instance
	 */
	private $api_manager;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		if (class_exists('ENNU_HubSpot_API_Manager')) {
			$this->api_manager = new ENNU_HubSpot_API_Manager();
		}
	}
	
	/**
	 * Ensure all required property groups exist
	 */
	public function ensure_property_groups_exist($object_type = 'contacts') {
		if (!$this->api_manager) {
			return new WP_Error('no_api_manager', 'HubSpot API Manager not available');
		}
		
		$results = array(
			'created' => array(),
			'existing' => array(),
			'errors' => array()
		);
		
		// Get existing property groups
		$existing_groups = $this->api_manager->get_property_groups($object_type);
		
		if (is_wp_error($existing_groups)) {
			return $existing_groups;
		}
		
		$existing_group_names = array();
		foreach ($existing_groups as $group) {
			$existing_group_names[] = $group['name'];
		}
		
		// Check and create required groups
		if (isset($this->required_groups[$object_type])) {
			foreach ($this->required_groups[$object_type] as $group_name => $display_name) {
				if (in_array($group_name, $existing_group_names)) {
					$results['existing'][] = $group_name;
				} else {
					$result = $this->api_manager->create_property_group($object_type, $group_name, $display_name);
					
					if (is_wp_error($result)) {
						$results['errors'][] = array(
							'group' => $group_name,
							'error' => $result->get_error_message()
						);
					} else {
						$results['created'][] = $group_name;
					}
				}
			}
		}
		
		return $results;
	}
	
	/**
	 * Get property group for assessment
	 */
	public function get_assessment_property_group($assessment_type, $object_type = 'contacts') {
		$group_mapping = array(
			'weight_loss' => 'weight_loss_assessment',
			'hormone' => 'hormone_assessment',
			'sleep' => 'sleep_assessment',
			'health' => 'health_assessment',
			'skin' => 'skin_assessment',
			'ed_treatment' => 'ed_treatment_assessment',
			'menopause' => 'menopause_assessment',
			'testosterone' => 'testosterone_assessment'
		);
		
		if (isset($group_mapping[$assessment_type])) {
			return $group_mapping[$assessment_type];
		}
		
		// Default to main assessment group
		return 'ennu_life_assessments';
	}
	
	/**
	 * Check if property group exists
	 */
	public function property_group_exists($group_name, $object_type = 'contacts') {
		if (!$this->api_manager) {
			return false;
		}
		
		$groups = $this->api_manager->get_property_groups($object_type);
		
		if (is_wp_error($groups)) {
			return false;
		}
		
		foreach ($groups as $group) {
			if ($group['name'] === $group_name) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Create property group if it doesn't exist
	 */
	public function create_property_group_if_needed($group_name, $display_name, $object_type = 'contacts') {
		if ($this->property_group_exists($group_name, $object_type)) {
			return true;
		}
		
		if (!$this->api_manager) {
			return new WP_Error('no_api_manager', 'HubSpot API Manager not available');
		}
		
		$result = $this->api_manager->create_property_group($object_type, $group_name, $display_name);
		
		if (is_wp_error($result)) {
			return $result;
		}
		
		return true;
	}
	
	/**
	 * Get all property groups for object type
	 */
	public function get_property_groups($object_type = 'contacts') {
		if (!$this->api_manager) {
			return new WP_Error('no_api_manager', 'HubSpot API Manager not available');
		}
		
		return $this->api_manager->get_property_groups($object_type);
	}
	
	/**
	 * Validate property group structure
	 */
	public function validate_property_groups($object_type = 'contacts') {
		$validation_results = array();
		
		if (isset($this->required_groups[$object_type])) {
			foreach ($this->required_groups[$object_type] as $group_name => $display_name) {
				$exists = $this->property_group_exists($group_name, $object_type);
				
				$validation_results[$group_name] = array(
					'name' => $group_name,
					'display_name' => $display_name,
					'exists' => $exists,
					'status' => $exists ? 'ok' : 'missing'
				);
			}
		}
		
		return $validation_results;
	}
} 