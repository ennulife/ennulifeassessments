<?php
/**
 * HubSpot Bulk Field Creator
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, ENNU Life Team
 * @license   GPL-3.0+
 * @since     64.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HubSpot Bulk Field Creator Class
 *
 * Provides comprehensive functionality for bulk creating custom object fields in HubSpot
 * with support for various field types, validation, and error handling.
 *
 * @since 64.2.1
 */
class ENNU_HubSpot_Bulk_Field_Creator {

	/**
	 * HubSpot API base URL
	 *
	 * @var string
	 */
	private $api_base_url = 'https://api.hubapi.com';

	/**
	 * API authentication parameters
	 *
	 * @var array
	 */
	private $api_params = array();

	/**
	 * Supported field types
	 *
	 * @var array
	 */
	private $supported_field_types = array(
		'string'      => array(
			'fieldTypes' => array( 'text', 'textarea', 'text' ),
			'validation' => array( 'maxLength' => 255 ),
		),
		'number'      => array(
			'fieldTypes' => array( 'number' ),
			'validation' => array( 'minValue', 'maxValue' ),
		),
		'date'        => array(
			'fieldTypes' => array( 'date' ),
			'validation' => array(),
		),
		'enumeration' => array(
			'fieldTypes' => array( 'select', 'radio', 'checkbox' ),
			'validation' => array( 'options' => 'required' ),
		),
		'boolean'     => array(
			'fieldTypes' => array( 'booleancheckbox' ),
			'validation' => array(),
		),
	);

	/**
	 * Constructor
	 *
	 * @since 64.2.1
	 */
	public function __construct() {
		$this->init_api_params();
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ennu_bulk_create_hubspot_fields', array( $this, 'ajax_bulk_create_fields' ) );
		add_action( 'wp_ajax_ennu_get_hubspot_objects', array( $this, 'ajax_get_objects' ) );
		add_action( 'wp_ajax_ennu_validate_field_schema', array( $this, 'ajax_validate_schema' ) );
	}

	/**
	 * Initialize API parameters
	 *
	 * @since 64.2.1
	 */
	private function init_api_params() {
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		
		if ( empty( $access_token ) ) {
			// Try to get from WP Fusion if available
			if ( function_exists( 'wp_fusion' ) && wp_fusion() ) {
				$crm = wp_fusion()->crm;
				if ( 'hubspot' === $crm->slug ) {
					$access_token = $crm->get_access_token();
				}
			}
		}

		$this->api_params = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'timeout' => 30,
		);
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @since 64.2.1
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'ennu-life_page_ennu-hubspot-fields' !== $hook ) {
			return;
		}
		
		wp_localize_script( 'jquery', 'ennu_hubspot_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ennu_hubspot_nonce' ),
		) );
	}

	/**
	 * Add admin menu
	 *
	 * @since 64.2.1
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life',
			__( 'HubSpot Bulk Field Creator', 'ennulifeassessments' ),
			__( 'HubSpot Fields', 'ennulifeassessments' ),
			'edit_posts', // Changed from 'manage_options' to 'edit_posts' for broader access
			'ennu-hubspot-fields',
			array( $this, 'admin_page' )
		);
	}

	/**
	 * Admin page HTML
	 *
	 * @since 64.2.1
	 */
	public function admin_page() {
		// Check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'Sorry, you are not allowed to access this page.', 'ennulifeassessments' ) );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'HubSpot Bulk Field Creator', 'ennulifeassessments' ); ?></h1>
			
			<div class="ennu-hubspot-field-creator">
				<div class="ennu-field-creator-container">
					<!-- Object Selection -->
					<div class="ennu-section">
						<h2><?php esc_html_e( '1. Select Custom Object', 'ennulifeassessments' ); ?></h2>
						<select id="ennu-object-selector" class="ennu-select">
							<option value=""><?php esc_html_e( 'Loading objects...', 'ennulifeassessments' ); ?></option>
						</select>
						<button type="button" id="ennu-refresh-objects" class="button">
							<?php esc_html_e( 'Refresh Objects', 'ennulifeassessments' ); ?>
						</button>
					</div>

					<!-- Field Configuration -->
					<div class="ennu-section">
						<h2><?php esc_html_e( '2. Configure Fields', 'ennulifeassessments' ); ?></h2>
						
						<div class="ennu-field-templates">
							<h3><?php esc_html_e( 'Quick Templates', 'ennulifeassessments' ); ?></h3>
							<button type="button" class="button ennu-template-btn" data-template="biomarkers">
								<?php esc_html_e( 'Biomarker Fields', 'ennulifeassessments' ); ?>
							</button>
							<button type="button" class="button ennu-template-btn" data-template="symptoms">
								<?php esc_html_e( 'Symptom Fields', 'ennulifeassessments' ); ?>
							</button>
							<button type="button" class="button ennu-template-btn" data-template="assessments">
								<?php esc_html_e( 'Assessment Fields', 'ennulifeassessments' ); ?>
							</button>
							<button type="button" class="button ennu-template-btn" data-template="health_goals">
								<?php esc_html_e( 'Health Goals Fields', 'ennulifeassessments' ); ?>
							</button>
						</div>

						<div class="ennu-field-builder">
							<div id="ennu-fields-container">
								<!-- Dynamic field rows will be added here -->
							</div>
							
							<button type="button" id="ennu-add-field" class="button button-secondary">
								<?php esc_html_e( '+ Add Field', 'ennulifeassessments' ); ?>
							</button>
						</div>
					</div>

					<!-- Validation & Creation -->
					<div class="ennu-section">
						<h2><?php esc_html_e( '3. Validate & Create', 'ennulifeassessments' ); ?></h2>
						
						<div class="ennu-validation-results" id="ennu-validation-results" style="display: none;">
							<!-- Validation results will appear here -->
						</div>

						<button type="button" id="ennu-validate-schema" class="button button-secondary">
							<?php esc_html_e( 'Validate Schema', 'ennulifeassessments' ); ?>
						</button>
						
						<button type="button" id="ennu-create-fields" class="button button-primary" disabled>
							<?php esc_html_e( 'Create Fields', 'ennulifeassessments' ); ?>
						</button>
					</div>

					<!-- Progress & Results -->
					<div class="ennu-section">
						<div id="ennu-progress" style="display: none;">
							<div class="ennu-progress-bar">
								<div class="ennu-progress-fill"></div>
							</div>
							<div class="ennu-progress-text"></div>
						</div>
						
						<div id="ennu-results" style="display: none;">
							<!-- Results will appear here -->
						</div>
					</div>
				</div>
			</div>
		</div>

		<style>
		.ennu-hubspot-field-creator {
			max-width: 1200px;
			margin: 20px 0;
		}
		
		.ennu-section {
			background: #fff;
			border: 1px solid #ccd0d4;
			border-radius: 4px;
			padding: 20px;
			margin-bottom: 20px;
		}
		
		.ennu-field-templates {
			margin-bottom: 20px;
		}
		
		.ennu-template-btn {
			margin-right: 10px !important;
			margin-bottom: 10px !important;
		}
		
		.ennu-field-row {
			background: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 4px;
			padding: 15px;
			margin-bottom: 10px;
			display: grid;
			grid-template-columns: 2fr 1fr 1fr 1fr auto;
			gap: 10px;
			align-items: center;
		}
		
		.ennu-field-row input,
		.ennu-field-row select {
			width: 100%;
		}
		
		.ennu-remove-field {
			background: #dc3232 !important;
			color: white !important;
			border: none !important;
			padding: 5px 10px !important;
			border-radius: 3px !important;
			cursor: pointer !important;
		}
		
		.ennu-progress-bar {
			width: 100%;
			height: 20px;
			background: #f0f0f1;
			border-radius: 10px;
			overflow: hidden;
			margin: 10px 0;
		}
		
		.ennu-progress-fill {
			height: 100%;
			background: #0073aa;
			width: 0%;
			transition: width 0.3s ease;
		}
		
		.ennu-validation-results {
			padding: 15px;
			margin: 15px 0;
			border-radius: 4px;
		}
		
		.ennu-validation-success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}
		
		.ennu-validation-error {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}
		
		.ennu-results {
			background: #fff;
			border: 1px solid #ccd0d4;
			border-radius: 4px;
			padding: 20px;
		}
		
		.ennu-success-item {
			color: #46b450;
			margin: 5px 0;
		}
		
		.ennu-error-item {
			color: #dc3232;
			margin: 5px 0;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			let selectedObject = '';
			let fieldSchema = [];
			
			// Load objects on page load
			loadObjects();
			
			// Object selector change
			$('#ennu-object-selector').on('change', function() {
				selectedObject = $(this).val();
				$('#ennu-create-fields').prop('disabled', !selectedObject);
			});
			
			// Refresh objects
			$('#ennu-refresh-objects').on('click', loadObjects);
			
			// Add field
			$('#ennu-add-field').on('click', addFieldRow);
			
			// Template buttons
			$('.ennu-template-btn').on('click', function() {
				const template = $(this).data('template');
				loadTemplate(template);
			});
			
			// Validate schema
			$('#ennu-validate-schema').on('click', validateSchema);
			
			// Create fields
			$('#ennu-create-fields').on('click', createFields);
			
			function loadObjects() {
				$.ajax({
					url: ennu_hubspot_ajax.ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_get_hubspot_objects',
						nonce: ennu_hubspot_ajax.nonce
					},
					success: function(response) {
						if (response.success) {
							const select = $('#ennu-object-selector');
							select.empty();
							select.append('<option value="">Select a custom object...</option>');
							
							response.data.forEach(function(obj) {
								select.append(`<option value="${obj.name}">${obj.labels.singular}</option>`);
							});
						} else {
							alert('Error loading objects: ' + response.data);
						}
					},
					error: function() {
						alert('Failed to load objects. Please check your HubSpot connection.');
					}
				});
			}
			
			function addFieldRow() {
				const fieldRow = `
					<div class="ennu-field-row">
						<input type="text" placeholder="Field Name (e.g., biomarker_value)" class="ennu-field-name">
						<input type="text" placeholder="Field Label (e.g., Biomarker Value)" class="ennu-field-label">
						<select class="ennu-field-type">
							<option value="string">String</option>
							<option value="number">Number</option>
							<option value="date">Date</option>
							<option value="enumeration">Enumeration</option>
							<option value="boolean">Boolean</option>
						</select>
						<select class="ennu-field-fieldtype">
							<option value="text">Text</option>
							<option value="textarea">Textarea</option>
							<option value="number">Number</option>
							<option value="date">Date</option>
							<option value="select">Select</option>
							<option value="radio">Radio</option>
							<option value="checkbox">Checkbox</option>
							<option value="booleancheckbox">Boolean</option>
						</select>
						<button type="button" class="ennu-remove-field">×</button>
					</div>
				`;
				$('#ennu-fields-container').append(fieldRow);
			}
			
			function loadTemplate(template) {
				$('#ennu-fields-container').empty();
				
				const templates = {
					biomarkers: [
						{ name: 'biomarker_name', label: 'Biomarker Name', type: 'string', fieldType: 'text' },
						{ name: 'biomarker_value', label: 'Biomarker Value', type: 'number', fieldType: 'number' },
						{ name: 'biomarker_unit', label: 'Unit', type: 'string', fieldType: 'text' },
						{ name: 'biomarker_status', label: 'Status', type: 'enumeration', fieldType: 'select' },
						{ name: 'biomarker_date', label: 'Test Date', type: 'date', fieldType: 'date' },
						{ name: 'biomarker_notes', label: 'Notes', type: 'string', fieldType: 'textarea' }
					],
					symptoms: [
						{ name: 'symptom_name', label: 'Symptom Name', type: 'string', fieldType: 'text' },
						{ name: 'symptom_severity', label: 'Severity', type: 'enumeration', fieldType: 'select' },
						{ name: 'symptom_frequency', label: 'Frequency', type: 'enumeration', fieldType: 'select' },
						{ name: 'symptom_duration', label: 'Duration', type: 'string', fieldType: 'text' },
						{ name: 'symptom_notes', label: 'Notes', type: 'string', fieldType: 'textarea' }
					],
					assessments: [
						{ name: 'assessment_type', label: 'Assessment Type', type: 'enumeration', fieldType: 'select' },
						{ name: 'assessment_score', label: 'Score', type: 'number', fieldType: 'number' },
						{ name: 'assessment_date', label: 'Assessment Date', type: 'date', fieldType: 'date' },
						{ name: 'assessment_status', label: 'Status', type: 'enumeration', fieldType: 'select' },
						{ name: 'assessment_completion', label: 'Completion %', type: 'number', fieldType: 'number' }
					],
					health_goals: [
						{ name: 'goal_type', label: 'Goal Type', type: 'enumeration', fieldType: 'select' },
						{ name: 'goal_target', label: 'Target Value', type: 'string', fieldType: 'text' },
						{ name: 'goal_deadline', label: 'Deadline', type: 'date', fieldType: 'date' },
						{ name: 'goal_progress', label: 'Progress %', type: 'number', fieldType: 'number' },
						{ name: 'goal_status', label: 'Status', type: 'enumeration', fieldType: 'select' }
					]
				};
				
				const fields = templates[template] || [];
				fields.forEach(function(field) {
					const fieldRow = `
						<div class="ennu-field-row">
							<input type="text" value="${field.name}" class="ennu-field-name">
							<input type="text" value="${field.label}" class="ennu-field-label">
							<select class="ennu-field-type">
								<option value="string" ${field.type === 'string' ? 'selected' : ''}>String</option>
								<option value="number" ${field.type === 'number' ? 'selected' : ''}>Number</option>
								<option value="date" ${field.type === 'date' ? 'selected' : ''}>Date</option>
								<option value="enumeration" ${field.type === 'enumeration' ? 'selected' : ''}>Enumeration</option>
								<option value="boolean" ${field.type === 'boolean' ? 'selected' : ''}>Boolean</option>
							</select>
							<select class="ennu-field-fieldtype">
								<option value="text" ${field.fieldType === 'text' ? 'selected' : ''}>Text</option>
								<option value="textarea" ${field.fieldType === 'textarea' ? 'selected' : ''}>Textarea</option>
								<option value="number" ${field.fieldType === 'number' ? 'selected' : ''}>Number</option>
								<option value="date" ${field.fieldType === 'date' ? 'selected' : ''}>Date</option>
								<option value="select" ${field.fieldType === 'select' ? 'selected' : ''}>Select</option>
								<option value="radio" ${field.fieldType === 'radio' ? 'selected' : ''}>Radio</option>
								<option value="checkbox" ${field.fieldType === 'checkbox' ? 'selected' : ''}>Checkbox</option>
								<option value="booleancheckbox" ${field.fieldType === 'booleancheckbox' ? 'selected' : ''}>Boolean</option>
							</select>
							<button type="button" class="ennu-remove-field">×</button>
						</div>
					`;
					$('#ennu-fields-container').append(fieldRow);
				});
			}
			
			// Remove field
			$(document).on('click', '.ennu-remove-field', function() {
				$(this).closest('.ennu-field-row').remove();
			});
			
			function validateSchema() {
				const fields = collectFields();
				if (fields.length === 0) {
					alert('Please add at least one field.');
					return;
				}
				
				$.ajax({
					url: ennu_hubspot_ajax.ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_validate_field_schema',
						nonce: ennu_hubspot_ajax.nonce,
						fields: fields
					},
					success: function(response) {
						const results = $('#ennu-validation-results');
						results.show();
						
						if (response.success) {
							results.removeClass('ennu-validation-error').addClass('ennu-validation-success');
							results.html('<strong>✓ Schema is valid!</strong> Ready to create fields.');
							$('#ennu-create-fields').prop('disabled', false);
							fieldSchema = fields;
						} else {
							results.removeClass('ennu-validation-success').addClass('ennu-validation-error');
							results.html('<strong>✗ Validation failed:</strong><br>' + response.data);
							$('#ennu-create-fields').prop('disabled', true);
						}
					},
					error: function() {
						alert('Validation failed. Please try again.');
					}
				});
			}
			
			function createFields() {
				if (!selectedObject || fieldSchema.length === 0) {
					alert('Please select an object and validate the schema first.');
					return;
				}
				
				$('#ennu-progress').show();
				$('#ennu-results').hide();
				$('#ennu-create-fields').prop('disabled', true);
				
				$.ajax({
					url: ennu_hubspot_ajax.ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_bulk_create_hubspot_fields',
						nonce: ennu_hubspot_ajax.nonce,
						object_type: selectedObject,
						fields: fieldSchema
					},
					success: function(response) {
						$('#ennu-progress').hide();
						$('#ennu-results').show();
						$('#ennu-create-fields').prop('disabled', false);
						
						const results = $('#ennu-results');
						if (response.success) {
							results.html('<h3>✓ Fields Created Successfully!</h3>' + response.data);
						} else {
							results.html('<h3>✗ Error Creating Fields</h3><p>' + response.data + '</p>');
						}
					},
					error: function() {
						$('#ennu-progress').hide();
						$('#ennu-results').show();
						$('#ennu-create-fields').prop('disabled', false);
						$('#ennu-results').html('<h3>✗ Network Error</h3><p>Failed to create fields. Please try again.</p>');
					}
				});
			}
			
			function collectFields() {
				const fields = [];
				$('.ennu-field-row').each(function() {
					const row = $(this);
					const field = {
						name: row.find('.ennu-field-name').val().trim(),
						label: row.find('.ennu-field-label').val().trim(),
						type: row.find('.ennu-field-type').val(),
						fieldType: row.find('.ennu-field-fieldtype').val()
					};
					
					if (field.name && field.label) {
						fields.push(field);
					}
				});
				return fields;
			}
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for getting HubSpot objects
	 *
	 * @since 64.2.1
	 */
	public function ajax_get_objects() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		$objects = $this->get_custom_objects();
		
		if ( is_wp_error( $objects ) ) {
			wp_send_json_error( $objects->get_error_message() );
		}

		wp_send_json_success( $objects );
	}

	/**
	 * AJAX handler for validating field schema
	 *
	 * @since 64.2.1
	 */
	public function ajax_validate_schema() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		$fields = isset( $_POST['fields'] ) ? $this->sanitize_fields( $_POST['fields'] ) : array();
		
		if ( empty( $fields ) ) {
			wp_send_json_error( 'No fields provided' );
		}

		$validation = $this->validate_field_schema( $fields );
		
		if ( is_wp_error( $validation ) ) {
			wp_send_json_error( $validation->get_error_message() );
		}

		wp_send_json_success( 'Schema is valid' );
	}

	/**
	 * AJAX handler for bulk creating fields
	 *
	 * @since 64.2.1
	 */
	public function ajax_bulk_create_fields() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Unauthorized' );
		}

		$object_type = sanitize_text_field( $_POST['object_type'] );
		$fields      = isset( $_POST['fields'] ) ? $this->sanitize_fields( $_POST['fields'] ) : array();

		if ( empty( $object_type ) || empty( $fields ) ) {
			wp_send_json_error( 'Missing required parameters' );
		}

		$results = $this->bulk_create_fields( $object_type, $fields );
		
		if ( is_wp_error( $results ) ) {
			wp_send_json_error( $results->get_error_message() );
		}

		wp_send_json_success( $this->format_results( $results ) );
	}

	/**
	 * Get custom objects from HubSpot
	 *
	 * @since 64.2.1
	 * @return array|WP_Error
	 */
	private function get_custom_objects() {
		$response = wp_remote_get( $this->api_base_url . '/crm/v3/schemas', $this->api_params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || ! isset( $data['results'] ) ) {
			return new WP_Error( 'api_error', 'Invalid response from HubSpot API' );
		}

		// Filter for custom objects only
		$custom_objects = array();
		foreach ( $data['results'] as $object ) {
			if ( isset( $object['objectTypeId'] ) && strpos( $object['objectTypeId'], 'p_' ) === 0 ) {
				$custom_objects[] = array(
					'name'   => $object['objectTypeId'],
					'labels' => $object['labels'],
				);
			}
		}

		return $custom_objects;
	}

	/**
	 * Validate field schema
	 *
	 * @since 64.2.1
	 * @param array $fields Field definitions
	 * @return true|WP_Error
	 */
	private function validate_field_schema( $fields ) {
		foreach ( $fields as $field ) {
			// Check required fields
			if ( empty( $field['name'] ) || empty( $field['label'] ) || empty( $field['type'] ) || empty( $field['fieldType'] ) ) {
				return new WP_Error( 'validation_error', 'All fields must have name, label, type, and fieldType' );
			}

			// Validate field name format
			if ( ! preg_match( '/^[a-z][a-z0-9_]*$/', $field['name'] ) ) {
				return new WP_Error( 'validation_error', 'Field name must be lowercase with underscores only: ' . $field['name'] );
			}

			// Check if field type is supported
			if ( ! isset( $this->supported_field_types[ $field['type'] ] ) ) {
				return new WP_Error( 'validation_error', 'Unsupported field type: ' . $field['type'] );
			}

			// Check if fieldType is valid for the given type
			$valid_field_types = $this->supported_field_types[ $field['type'] ]['fieldTypes'];
			if ( ! in_array( $field['fieldType'], $valid_field_types, true ) ) {
				return new WP_Error( 'validation_error', 'Invalid fieldType for type ' . $field['type'] . ': ' . $field['fieldType'] );
			}
		}

		return true;
	}

	/**
	 * Bulk create fields in HubSpot
	 *
	 * @since 64.2.1
	 * @param string $object_type Object type ID
	 * @param array  $fields Field definitions
	 * @return array|WP_Error
	 */
	private function bulk_create_fields( $object_type, $fields ) {
		$results = array(
			'success' => array(),
			'errors'  => array(),
		);

		foreach ( $fields as $field ) {
			$result = $this->create_single_field( $object_type, $field );
			
			if ( is_wp_error( $result ) ) {
				$results['errors'][] = array(
					'field' => $field['name'],
					'error' => $result->get_error_message(),
				);
			} else {
				$results['success'][] = array(
					'field' => $field['name'],
					'id'    => $result,
				);
			}
		}

		return $results;
	}

	/**
	 * Create a single field in HubSpot
	 *
	 * @since 64.2.1
	 * @param string $object_type Object type ID
	 * @param array  $field Field definition
	 * @return string|WP_Error Field ID or error
	 */
	private function create_single_field( $object_type, $field ) {
		$field_data = array(
			'name'        => $field['name'],
			'label'       => $field['label'],
			'type'        => $field['type'],
			'fieldType'   => $field['fieldType'],
			'groupName'   => 'ennu_life_assessments',
			'description' => 'Field created by ENNU Life Assessments plugin',
		);

		// Add field-specific properties
		if ( 'enumeration' === $field['type'] ) {
			$field_data['options'] = array(
				array(
					'label'        => 'Option 1',
					'value'        => 'option_1',
					'displayOrder' => 1,
				),
			);
		}

		$params           = $this->api_params;
		$params['body']   = wp_json_encode( $field_data );
		$params['method'] = 'POST';

		$url      = $this->api_base_url . '/crm/v3/properties/' . $object_type;
		$response = wp_remote_request( $url, $params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || isset( $data['error'] ) ) {
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Unknown error';
			return new WP_Error( 'api_error', $error_message );
		}

		return $field['name'];
	}

	/**
	 * Sanitize field data
	 *
	 * @since 64.2.1
	 * @param array $fields Raw field data
	 * @return array Sanitized field data
	 */
	private function sanitize_fields( $fields ) {
		$sanitized = array();
		
		foreach ( $fields as $field ) {
			$sanitized[] = array(
				'name'      => sanitize_key( $field['name'] ),
				'label'     => sanitize_text_field( $field['label'] ),
				'type'      => sanitize_text_field( $field['type'] ),
				'fieldType' => sanitize_text_field( $field['fieldType'] ),
			);
		}
		
		return $sanitized;
	}

	/**
	 * Format results for display
	 *
	 * @since 64.2.1
	 * @param array $results Results array
	 * @return string Formatted HTML
	 */
	private function format_results( $results ) {
		$html = '<div class="ennu-results">';
		
		if ( ! empty( $results['success'] ) ) {
			$html .= '<h4>✓ Successfully Created Fields:</h4>';
			$html .= '<ul>';
			foreach ( $results['success'] as $field ) {
				$html .= '<li class="ennu-success-item">' . esc_html( $field['field'] ) . '</li>';
			}
			$html .= '</ul>';
		}
		
		if ( ! empty( $results['errors'] ) ) {
			$html .= '<h4>✗ Failed to Create Fields:</h4>';
			$html .= '<ul>';
			foreach ( $results['errors'] as $field ) {
				$html .= '<li class="ennu-error-item">' . esc_html( $field['field'] ) . ': ' . esc_html( $field['error'] ) . '</li>';
			}
			$html .= '</ul>';
		}
		
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Get predefined field templates
	 *
	 * @since 64.2.1
	 * @return array Field templates
	 */
	public function get_field_templates() {
		return array(
			'biomarkers'   => array(
				'name'        => 'Biomarker Fields',
				'description' => 'Standard biomarker tracking fields',
				'fields'      => array(
					array( 'name' => 'biomarker_name', 'label' => 'Biomarker Name', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'biomarker_value', 'label' => 'Biomarker Value', 'type' => 'number', 'fieldType' => 'number' ),
					array( 'name' => 'biomarker_unit', 'label' => 'Unit', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'biomarker_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'biomarker_date', 'label' => 'Test Date', 'type' => 'date', 'fieldType' => 'date' ),
					array( 'name' => 'biomarker_notes', 'label' => 'Notes', 'type' => 'string', 'fieldType' => 'textarea' ),
				),
			),
			'symptoms'     => array(
				'name'        => 'Symptom Fields',
				'description' => 'Symptom tracking and severity fields',
				'fields'      => array(
					array( 'name' => 'symptom_name', 'label' => 'Symptom Name', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'symptom_severity', 'label' => 'Severity', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'symptom_frequency', 'label' => 'Frequency', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'symptom_duration', 'label' => 'Duration', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'symptom_notes', 'label' => 'Notes', 'type' => 'string', 'fieldType' => 'textarea' ),
				),
			),
			'assessments'  => array(
				'name'        => 'Assessment Fields',
				'description' => 'Assessment completion and scoring fields',
				'fields'      => array(
					array( 'name' => 'assessment_type', 'label' => 'Assessment Type', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'assessment_score', 'label' => 'Score', 'type' => 'number', 'fieldType' => 'number' ),
					array( 'name' => 'assessment_date', 'label' => 'Assessment Date', 'type' => 'date', 'fieldType' => 'date' ),
					array( 'name' => 'assessment_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'assessment_completion', 'label' => 'Completion %', 'type' => 'number', 'fieldType' => 'number' ),
				),
			),
			'health_goals' => array(
				'name'        => 'Health Goals Fields',
				'description' => 'Health goal tracking and progress fields',
				'fields'      => array(
					array( 'name' => 'goal_type', 'label' => 'Goal Type', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'goal_target', 'label' => 'Target Value', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'goal_deadline', 'label' => 'Deadline', 'type' => 'date', 'fieldType' => 'date' ),
					array( 'name' => 'goal_progress', 'label' => 'Progress %', 'type' => 'number', 'fieldType' => 'number' ),
					array( 'name' => 'goal_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
				),
			),
		);
	}
} 