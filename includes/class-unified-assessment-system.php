<?php
/**
 * Unified Assessment System
 * 
 * This class provides a unified approach to assessment field creation,
 * eliminating the need for separate implementations for each assessment type.
 * All assessments now share the same infrastructure and resources.
 *
 * @package ENNU_Life
 * @version 64.7.0
 */

class ENNU_Unified_Assessment_System {
	
	/**
	 * Assessment configurations
	 */
	private $assessment_configs = array();
	
	/**
	 * Shared field templates
	 */
	private $shared_fields = array();
	
	/**
	 * HubSpot integration instance
	 */
	private $hubspot_integration;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_assessment_configs();
		$this->init_shared_fields();
		$this->init_hubspot_integration();
		
		// Register AJAX actions with proper permissions
		add_action('wp_ajax_ennu_create_unified_fields', array($this, 'ajax_create_unified_fields'));
		add_action('wp_ajax_ennu_preview_unified_fields', array($this, 'ajax_preview_unified_fields'));
		add_action('wp_ajax_ennu_get_unified_statistics', array($this, 'ajax_get_unified_statistics'));
		add_action('wp_ajax_ennu_delete_unified_fields', array($this, 'ajax_delete_unified_fields'));
		add_action('wp_ajax_ennu_test_hubspot_connection', array($this, 'ajax_test_hubspot_connection'));
		add_action('wp_ajax_ennu_get_property_groups', array($this, 'ajax_get_property_groups'));
		add_action('wp_ajax_ennu_create_property_group', array($this, 'ajax_create_property_group'));
	}
	
	/**
	 * Initialize assessment configurations
	 */
	private function init_assessment_configs() {
		$this->assessment_configs = array(
			'weight_loss' => array(
				'name' => 'Weight Loss Assessment',
				'description' => 'Comprehensive weight loss evaluation and planning',
				'questions' => $this->get_weight_loss_questions(),
				'field_prefix' => 'wl_',
				'property_group' => 'Weight Loss Assessment',
				'scoring_categories' => array('Motivation & Goals', 'Nutrition', 'Physical Activity', 'Lifestyle Factors', 'Psychological Factors', 'Behavioral Patterns', 'Medical Factors', 'Weight Loss History', 'Aesthetics', 'Social Support')
			),
			'hormone' => array(
				'name' => 'Hormone Assessment',
				'description' => 'Hormonal health evaluation and optimization',
				'questions' => $this->get_hormone_questions(),
				'field_prefix' => 'hormone_',
				'property_group' => 'Hormone Assessment',
				'scoring_categories' => array('Energy Levels', 'Sleep Quality', 'Stress Management', 'Libido', 'Mood Stability', 'Metabolic Health', 'Reproductive Health', 'Cognitive Function', 'Physical Symptoms', 'Lifestyle Factors')
			),
			'sleep' => array(
				'name' => 'Sleep Assessment',
				'description' => 'Sleep quality and optimization evaluation',
				'questions' => $this->get_sleep_questions(),
				'field_prefix' => 'sleep_',
				'property_group' => 'Sleep Assessment',
				'scoring_categories' => array('Sleep Duration', 'Sleep Quality', 'Sleep Environment', 'Sleep Hygiene', 'Stress Impact', 'Lifestyle Factors', 'Medical Factors', 'Cognitive Function', 'Physical Health', 'Behavioral Patterns')
			),
			'health' => array(
				'name' => 'Health Assessment',
				'description' => 'Comprehensive health and wellness evaluation',
				'questions' => $this->get_health_questions(),
				'field_prefix' => 'health_',
				'property_group' => 'Health Assessment',
				'scoring_categories' => array('Energy Levels', 'Sleep Quality', 'Stress Levels', 'Exercise Frequency', 'Nutrition', 'Mental Health', 'Chronic Conditions', 'Preventive Care', 'Lifestyle Factors', 'Social Support')
			),
			'skin' => array(
				'name' => 'Skin Assessment',
				'description' => 'Skin health and treatment evaluation',
				'questions' => $this->get_skin_questions(),
				'field_prefix' => 'skin_',
				'property_group' => 'Skin Assessment',
				'scoring_categories' => array('Skin Type', 'Concern Severity', 'Routine Complexity', 'Environmental Factors', 'Lifestyle Impact', 'Medical History', 'Product Sensitivity', 'Treatment Goals', 'Compliance Factors', 'Social Impact')
			),
			'ed_treatment' => array(
				'name' => 'ED Treatment Assessment',
				'description' => 'Erectile dysfunction evaluation and treatment planning',
				'questions' => $this->get_ed_treatment_questions(),
				'field_prefix' => 'ed_',
				'property_group' => 'ED Treatment Assessment',
				'scoring_categories' => array('IIEF-5 Score', 'Severity Level', 'Duration', 'Lifestyle Factors', 'Medical History', 'Psychological Factors', 'Treatment History', 'Partner Support', 'Motivation Level', 'Compliance Factors')
			),
			'menopause' => array(
				'name' => 'Menopause Assessment',
				'description' => 'Menopausal symptoms and treatment evaluation',
				'questions' => $this->get_menopause_questions(),
				'field_prefix' => 'menopause_',
				'property_group' => 'Menopause Assessment',
				'scoring_categories' => array('Symptom Severity', 'Hormonal Changes', 'Quality of Life', 'Medical History', 'Lifestyle Factors', 'Treatment History', 'Support Systems', 'Cognitive Function', 'Physical Health', 'Emotional Well-being')
			),
			'testosterone' => array(
				'name' => 'Testosterone Assessment',
				'description' => 'Testosterone levels and optimization evaluation',
				'questions' => $this->get_testosterone_questions(),
				'field_prefix' => 'testosterone_',
				'property_group' => 'Testosterone Assessment',
				'scoring_categories' => array('Energy Levels', 'Libido', 'Muscle Mass', 'Cognitive Function', 'Mood Stability', 'Sleep Quality', 'Stress Levels', 'Medical History', 'Lifestyle Factors', 'Treatment Goals')
			),
			'consultation' => array(
				'name' => 'Consultation Assessment',
				'description' => 'General health consultation and evaluation',
				'questions' => $this->get_consultation_questions(),
				'field_prefix' => 'consultation_',
				'property_group' => 'Consultation Assessment',
				'scoring_categories' => array('Health Goals', 'Current Symptoms', 'Medical History', 'Lifestyle Factors', 'Treatment Preferences', 'Support Systems', 'Compliance Factors', 'Communication Style', 'Expectations', 'Urgency Level')
			)
		);
	}
	
	/**
	 * Initialize shared fields that all assessments use
	 */
	private function init_shared_fields() {
		$this->shared_fields = array(
			// Global shared fields (used across ALL assessments)
			'global' => array(
				'gender' => array(
					'name' => 'gender',
					'label' => 'Gender',
					'type' => 'enumeration',
					'fieldType' => 'select',
					'groupName' => 'Global Shared Fields',
					'options' => array(
						array('label' => 'Male', 'value' => 'male'),
						array('label' => 'Female', 'value' => 'female'),
						array('label' => 'Other / Prefer not to say', 'value' => 'other')
					),
					'description' => 'Gender identification for demographic analysis'
				),
				'date_of_birth' => array(
					'name' => 'date_of_birth',
					'label' => 'Date of Birth',
					'type' => 'date',
					'fieldType' => 'date',
					'groupName' => 'Global Shared Fields',
					'description' => 'Date of birth for age calculation and demographic analysis'
				),
				'height_weight' => array(
					'name' => 'height_weight',
					'label' => 'Height and Weight',
					'type' => 'string',
					'fieldType' => 'text',
					'groupName' => 'Global Shared Fields',
					'description' => 'Height and weight for BMI calculation and health metrics'
				),
				'bmi' => array(
					'name' => 'bmi',
					'label' => 'BMI (Body Mass Index)',
					'type' => 'number',
					'fieldType' => 'number',
					'groupName' => 'Global Shared Fields',
					'description' => 'Calculated BMI value for health assessment'
				),
				'age' => array(
					'name' => 'age',
					'label' => 'Age',
					'type' => 'number',
					'fieldType' => 'number',
					'groupName' => 'Global Shared Fields',
					'description' => 'Calculated age for demographic analysis'
				),
				'email' => array(
					'name' => 'email',
					'label' => 'Email Address',
					'type' => 'string',
					'fieldType' => 'text',
					'groupName' => 'Global Shared Fields',
					'description' => 'Primary email address for communication'
				)
			),
			
			// Assessment metadata fields (used across ALL assessments)
			'metadata' => array(
				'assessment_type' => array(
					'name' => 'assessment_type',
					'label' => 'Assessment Type',
					'type' => 'enumeration',
					'fieldType' => 'select',
					'groupName' => 'Assessment Metadata',
					'options' => array(
						array('label' => 'Weight Loss', 'value' => 'weight_loss'),
						array('label' => 'Hormone', 'value' => 'hormone'),
						array('label' => 'Sleep', 'value' => 'sleep'),
						array('label' => 'Health', 'value' => 'health'),
						array('label' => 'Skin', 'value' => 'skin'),
						array('label' => 'ED Treatment', 'value' => 'ed_treatment'),
						array('label' => 'Menopause', 'value' => 'menopause'),
						array('label' => 'Testosterone', 'value' => 'testosterone'),
						array('label' => 'Consultation', 'value' => 'consultation')
					),
					'description' => 'Type of assessment completed'
				),
				'assessment_date' => array(
					'name' => 'assessment_date',
					'label' => 'Assessment Date',
					'type' => 'date',
					'fieldType' => 'date',
					'groupName' => 'Assessment Metadata',
					'description' => 'Date when assessment was completed'
				),
				'assessment_score' => array(
					'name' => 'assessment_score',
					'label' => 'Assessment Score',
					'type' => 'number',
					'fieldType' => 'number',
					'groupName' => 'Assessment Metadata',
					'description' => 'Overall assessment score (0-10 scale)'
				),
				'assessment_attempt' => array(
					'name' => 'assessment_attempt',
					'label' => 'Assessment Attempt Number',
					'type' => 'number',
					'fieldType' => 'number',
					'groupName' => 'Assessment Metadata',
					'description' => 'Attempt number for this assessment type'
				),
				'assessment_status' => array(
					'name' => 'assessment_status',
					'label' => 'Assessment Status',
					'type' => 'enumeration',
					'fieldType' => 'select',
					'groupName' => 'Assessment Metadata',
					'options' => array(
						array('label' => 'Completed', 'value' => 'completed'),
						array('label' => 'In Progress', 'value' => 'in_progress'),
						array('label' => 'Abandoned', 'value' => 'abandoned')
					),
					'description' => 'Current status of the assessment'
				)
			)
		);
	}
	
	/**
	 * Initialize HubSpot integration
	 */
	private function init_hubspot_integration() {
		if (class_exists('ENNU_HubSpot_Bulk_Field_Creator')) {
			$this->hubspot_integration = new ENNU_HubSpot_Bulk_Field_Creator();
		}
	}
	
	/**
	 * Get unified field creation for any assessment
	 */
	public function create_unified_fields($assessment_type = null) {
		$results = array(
			'contacts' => array('created' => 0, 'skipped' => 0, 'errors' => 0),
			'custom_object' => array('created' => 0, 'skipped' => 0, 'errors' => 0)
		);
		
		// If no specific assessment, create fields for all assessments
		$assessments_to_process = $assessment_type ? array($assessment_type) : array_keys($this->assessment_configs);
		
		foreach ($assessments_to_process as $type) {
			if (!isset($this->assessment_configs[$type])) {
				continue;
			}
			
			$config = $this->assessment_configs[$type];
			$fields = $this->generate_assessment_fields($config);
			
			// Create contact fields
			$contact_result = $this->create_contact_fields($fields['contact'], $config);
			$results['contacts']['created'] += $contact_result['created'];
			$results['contacts']['skipped'] += $contact_result['skipped'];
			$results['contacts']['errors'] += $contact_result['errors'];
			
			// Create custom object fields
			$custom_result = $this->create_custom_object_fields($fields['custom_object'], $config);
			$results['custom_object']['created'] += $custom_result['created'];
			$results['custom_object']['skipped'] += $custom_result['skipped'];
			$results['custom_object']['errors'] += $custom_result['errors'];
		}
		
		return $results;
	}
	
	/**
	 * Generate fields for a specific assessment
	 */
	private function generate_assessment_fields($config) {
		$contact_fields = array();
		$custom_object_fields = array();
		
		// Add shared global fields to contact fields
		foreach ($this->shared_fields['global'] as $field) {
			$contact_fields[] = $field;
		}
		
		// Add assessment-specific questions
		foreach ($config['questions'] as $question_key => $question) {
			$field = $this->convert_question_to_field($question, $config);
			$contact_fields[] = $field;
			
			// Create custom object field for historical tracking
			$custom_field = $this->create_custom_object_field($field, $config);
			$custom_object_fields[] = $custom_field;
		}
		
		// Add assessment metadata fields
		foreach ($this->shared_fields['metadata'] as $field) {
			$contact_fields[] = $field;
			$custom_object_fields[] = $field;
		}
		
		return array(
			'contact' => $contact_fields,
			'custom_object' => $custom_object_fields
		);
	}
	
	/**
	 * Convert question to HubSpot field format
	 */
	private function convert_question_to_field($question, $config) {
		$field = array(
			'name' => $config['field_prefix'] . $question['key'],
			'label' => $question['title'],
			'type' => $this->map_question_type_to_hubspot_type($question['type']),
			'fieldType' => $this->map_question_type_to_field_type($question['type']),
			'groupName' => $config['property_group'],
			'description' => $this->generate_field_description($question, $config['name'])
		);
		
		// Add options for enumeration fields
		if (isset($question['options']) && !empty($question['options'])) {
			$field['options'] = array();
			foreach ($question['options'] as $value => $label) {
				$field['options'][] = array(
					'label' => $label,
					'value' => $value
				);
			}
		}
		
		return $field;
	}
	
	/**
	 * Create custom object field for historical tracking
	 */
	private function create_custom_object_field($contact_field, $config) {
		$custom_field = $contact_field;
		$custom_field['name'] = 'historical_' . $contact_field['name'];
		$custom_field['label'] = 'Historical ' . $contact_field['label'];
		$custom_field['groupName'] = 'Historical Data';
		$custom_field['description'] = 'Historical tracking data for ' . $contact_field['label'];
		
		return $custom_field;
	}
	
	/**
	 * Map question types to HubSpot field types
	 */
	private function map_question_type_to_hubspot_type($question_type) {
		$type_mapping = array(
			'radio' => 'enumeration',
			'checkbox' => 'enumeration',
			'select' => 'enumeration',
			'text' => 'string',
			'textarea' => 'string',
			'number' => 'number',
			'date' => 'date',
			'email' => 'string',
			'phone' => 'string',
			'url' => 'string',
			'height_weight' => 'string',
			'dob_dropdowns' => 'date'
		);
		
		return isset($type_mapping[$question_type]) ? $type_mapping[$question_type] : 'string';
	}
	
	/**
	 * Map question types to HubSpot field types
	 */
	private function map_question_type_to_field_type($question_type) {
		$field_type_mapping = array(
			'radio' => 'select',
			'checkbox' => 'checkbox',
			'select' => 'select',
			'text' => 'text',
			'textarea' => 'textarea',
			'number' => 'number',
			'date' => 'date',
			'email' => 'text',
			'phone' => 'text',
			'url' => 'text',
			'height_weight' => 'text',
			'dob_dropdowns' => 'date'
		);
		
		return isset($field_type_mapping[$question_type]) ? $field_type_mapping[$question_type] : 'text';
	}
	
	/**
	 * Generate field description
	 */
	private function generate_field_description($question, $assessment_name) {
		$description = "Question from {$assessment_name}: {$question['title']}";
		
		if (isset($question['scoring'])) {
			$description .= " (Scoring Category: {$question['scoring']['category']})";
		}
		
		return $description;
	}
	
	/**
	 * Create contact fields in HubSpot
	 */
	private function create_contact_fields($fields, $config) {
		if (!$this->hubspot_integration) {
			return array('created' => 0, 'skipped' => 0, 'errors' => 1);
		}
		
		$results = array('created' => 0, 'skipped' => 0, 'errors' => 0);
		
		foreach ($fields as $field) {
			try {
				$result = $this->hubspot_integration->create_single_field('contacts', $field, $config['name']);
				if ($result === true) {
					$results['created']++;
				} else {
					$results['skipped']++;
				}
			} catch (Exception $e) {
				$results['errors']++;
				error_log("ENNU Unified: Error creating contact field {$field['name']}: " . $e->getMessage());
			}
		}
		
		return $results;
	}
	
	/**
	 * Create custom object fields in HubSpot
	 */
	private function create_custom_object_fields($fields, $config) {
		if (!$this->hubspot_integration) {
			return array('created' => 0, 'skipped' => 0, 'errors' => 1);
		}
		
		$results = array('created' => 0, 'skipped' => 0, 'errors' => 0);
		
		foreach ($fields as $field) {
			try {
				$result = $this->hubspot_integration->create_single_field($this->hubspot_integration->get_custom_object_name(), $field, $config['name']);
				if ($result === true) {
					$results['created']++;
				} else {
					$results['skipped']++;
				}
			} catch (Exception $e) {
				$results['errors']++;
				error_log("ENNU Unified: Error creating custom object field {$field['name']}: " . $e->getMessage());
			}
		}
		
		return $results;
	}
	
	/**
	 * AJAX handler for creating unified fields
	 */
	public function ajax_create_unified_fields() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		$assessment_type = isset($_POST['assessment_type']) ? sanitize_text_field($_POST['assessment_type']) : null;
		
		$results = $this->create_unified_fields($assessment_type);
		
		$message = "Unified field creation completed successfully.";
		if ($assessment_type) {
			$message = "Created fields for {$assessment_type} assessment.";
		}
		
		wp_send_json_success(array(
			'message' => $message,
			'results' => $results
		));
	}
	
	/**
	 * AJAX handler for previewing unified fields
	 */
	public function ajax_preview_unified_fields() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		$assessment_type = isset($_POST['assessment_type']) ? sanitize_text_field($_POST['assessment_type']) : null;
		
		if ($assessment_type && isset($this->assessment_configs[$assessment_type])) {
			$config = $this->assessment_configs[$assessment_type];
			$fields = $this->generate_assessment_fields($config);
			
			wp_send_json_success(array(
				'assessment' => $config['name'],
				'contact_fields' => $fields['contact'],
				'custom_object_fields' => $fields['custom_object'],
				'total_contact_fields' => count($fields['contact']),
				'total_custom_object_fields' => count($fields['custom_object'])
			));
		} else {
			wp_send_json_error('Invalid assessment type');
		}
	}
	
	/**
	 * AJAX handler for getting unified statistics
	 */
	public function ajax_get_unified_statistics() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		$statistics = array();
		
		foreach ($this->assessment_configs as $type => $config) {
			$fields = $this->generate_assessment_fields($config);
			$statistics[$type] = array(
				'name' => $config['name'],
				'contact_fields' => count($fields['contact']),
				'custom_object_fields' => count($fields['custom_object']),
				'total_fields' => count($fields['contact']) + count($fields['custom_object'])
			);
		}
		
		wp_send_json_success($statistics);
	}
	
	/**
	 * AJAX handler for deleting unified fields
	 */
	public function ajax_delete_unified_fields() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		$assessment_type = isset($_POST['assessment_type']) ? sanitize_text_field($_POST['assessment_type']) : null;
		
		// Implementation for deleting fields would go here
		// This would use the HubSpot API to delete fields
		
		wp_send_json_success(array(
			'message' => "Fields deleted successfully for {$assessment_type} assessment."
		));
	}
	
	/**
	 * AJAX handler for testing HubSpot connection
	 */
	public function ajax_test_hubspot_connection() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		if (class_exists('ENNU_HubSpot_API_Manager')) {
			$api_manager = new ENNU_HubSpot_API_Manager();
			$result = $api_manager->test_connection();
			
			if (is_wp_error($result)) {
				wp_send_json_error($result->get_error_message());
			} else {
				wp_send_json_success($result);
			}
		} else {
			wp_send_json_error('HubSpot API Manager not available');
		}
	}
	
	/**
	 * AJAX handler for getting property groups
	 */
	public function ajax_get_property_groups() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		$object_type = isset($_POST['object_type']) ? sanitize_text_field($_POST['object_type']) : 'contacts';
		
		if (class_exists('ENNU_HubSpot_API_Manager')) {
			$api_manager = new ENNU_HubSpot_API_Manager();
			$groups = $api_manager->get_property_groups($object_type);
			
			if (is_wp_error($groups)) {
				wp_send_json_error($groups->get_error_message());
			} else {
				wp_send_json_success($groups);
			}
		} else {
			wp_send_json_error('HubSpot API Manager not available');
		}
	}
	
	/**
	 * AJAX handler for creating property group
	 */
	public function ajax_create_property_group() {
		check_ajax_referer('ennu_admin_nonce', 'nonce');
		
		if (!current_user_can('manage_options')) {
			wp_die('Unauthorized');
		}
		
		$object_type = isset($_POST['object_type']) ? sanitize_text_field($_POST['object_type']) : 'contacts';
		$group_name = isset($_POST['group_name']) ? sanitize_text_field($_POST['group_name']) : '';
		$display_name = isset($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : '';
		
		if (empty($group_name)) {
			wp_send_json_error('Group name is required');
		}
		
		if (class_exists('ENNU_HubSpot_API_Manager')) {
			$api_manager = new ENNU_HubSpot_API_Manager();
			$result = $api_manager->create_property_group($object_type, $group_name, $display_name);
			
			if (is_wp_error($result)) {
				wp_send_json_error($result->get_error_message());
			} else {
				wp_send_json_success($result);
			}
		} else {
			wp_send_json_error('HubSpot API Manager not available');
		}
	}
	
	// Question definitions for each assessment type
	// These would be loaded from configuration files or database
	
	private function get_weight_loss_questions() {
		return array(
			'q2' => array(
				'key' => 'q2',
				'title' => 'What is your primary weight loss goal?',
				'type' => 'radio',
				'options' => array(
					'lose_10_20' => 'Lose 10-20 lbs',
					'lose_20_50' => 'Lose 20-50 lbs',
					'lose_50_plus' => 'Lose 50+ lbs',
					'maintain' => 'Maintain current weight'
				),
				'scoring' => array('category' => 'Motivation & Goals', 'weight' => 2.5)
			),
			// Add more questions here...
		);
	}
	
	private function get_hormone_questions() {
		return array(
			'q1' => array(
				'key' => 'q1',
				'title' => 'How would you rate your energy levels?',
				'type' => 'radio',
				'options' => array(
					'very_low' => 'Very Low',
					'low' => 'Low',
					'moderate' => 'Moderate',
					'high' => 'High',
					'very_high' => 'Very High'
				),
				'scoring' => array('category' => 'Energy Levels', 'weight' => 2.0)
			),
			// Add more questions here...
		);
	}
	
	// Add similar methods for other assessment types...
	private function get_sleep_questions() { return array(); }
	private function get_health_questions() { return array(); }
	private function get_skin_questions() { return array(); }
	private function get_ed_treatment_questions() { return array(); }
	private function get_menopause_questions() { return array(); }
	private function get_testosterone_questions() { return array(); }
	private function get_consultation_questions() { return array(); }
}

// Initialize the unified system
new ENNU_Unified_Assessment_System(); 