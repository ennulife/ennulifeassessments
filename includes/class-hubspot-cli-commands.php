<?php
/**
 * HubSpot CLI Commands
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, ENNU Life Team
 * @license   GPL-3.0+
 * @since     64.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only load this class if WP-CLI is available
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * HubSpot CLI Commands Class
 *
 * Provides WP-CLI commands for bulk HubSpot operations including field creation,
 * object management, and data synchronization.
 *
 * @since 64.2.1
 */
class ENNU_HubSpot_CLI_Commands {

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
	 * Constructor
	 *
	 * @since 64.2.1
	 */
	public function __construct() {
		// Only initialize if WP-CLI is available
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}
		$this->init_api_params();
	}

	/**
	 * Initialize API parameters
	 *
	 * @since 64.2.1
	 */
	private function init_api_params() {
		// Only initialize if WP-CLI is available
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

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

		if ( empty( $access_token ) ) {
			WP_CLI::error( 'HubSpot access token not found. Please configure your HubSpot integration first.' );
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
	 * Create custom object fields in bulk
	 *
	 * ## OPTIONS
	 *
	 * <object_type>
	 * : The HubSpot custom object type ID (e.g., p_biomarkers)
	 *
	 * <fields_file>
	 * : Path to JSON file containing field definitions
	 *
	 * [--template=<template>]
	 * : Use predefined template (biomarkers, symptoms, assessments, health_goals)
	 *
	 * [--dry-run]
	 * : Validate fields without creating them
	 *
	 * [--force]
	 * : Skip validation and create fields directly
	 *
	 * ## EXAMPLES
	 *
	 *     # Create fields from JSON file
	 *     wp ennu hubspot create-fields p_biomarkers /path/to/fields.json
	 *
	 *     # Use predefined template
	 *     wp ennu hubspot create-fields p_biomarkers --template=biomarkers
	 *
	 *     # Validate fields without creating
	 *     wp ennu hubspot create-fields p_biomarkers /path/to/fields.json --dry-run
	 *
	 * @param array $args Command arguments
	 * @param array $assoc_args Command options
	 */
	public function create_fields( $args, $assoc_args ) {
		list( $object_type, $fields_file ) = $args;

		// Validate object type
		if ( ! preg_match( '/^p_[a-z_]+$/', $object_type ) ) {
			WP_CLI::error( 'Invalid object type. Must start with "p_" and contain only lowercase letters and underscores.' );
		}

		// Get fields from template or file
		if ( isset( $assoc_args['template'] ) ) {
			$fields = $this->get_template_fields( $assoc_args['template'] );
			if ( is_wp_error( $fields ) ) {
				WP_CLI::error( $fields->get_error_message() );
			}
		} else {
			if ( ! file_exists( $fields_file ) ) {
				WP_CLI::error( 'Fields file not found: ' . $fields_file );
			}

			$fields_data = file_get_contents( $fields_file );
			$fields      = json_decode( $fields_data, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				WP_CLI::error( 'Invalid JSON in fields file: ' . json_last_error_msg() );
			}
		}

		// Validate fields
		if ( ! isset( $assoc_args['force'] ) ) {
			WP_CLI::log( 'Validating field schema...' );
			$validation = $this->validate_field_schema( $fields );
			if ( is_wp_error( $validation ) ) {
				WP_CLI::error( 'Field validation failed: ' . $validation->get_error_message() );
			}
			WP_CLI::success( 'Field schema is valid.' );
		}

		// Dry run
		if ( isset( $assoc_args['dry-run'] ) ) {
			WP_CLI::log( 'DRY RUN - No fields will be created.' );
			WP_CLI::log( 'Would create ' . count( $fields ) . ' fields:' );
			foreach ( $fields as $field ) {
				WP_CLI::log( '  - ' . $field['name'] . ' (' . $field['type'] . ')' );
			}
			return;
		}

		// Create fields
		WP_CLI::log( 'Creating ' . count( $fields ) . ' fields...' );
		$progress = \WP_CLI\Utils\make_progress_bar( 'Creating fields', count( $fields ) );

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
				WP_CLI::warning( 'Failed to create field ' . $field['name'] . ': ' . $result->get_error_message() );
			} else {
				$results['success'][] = array(
					'field' => $field['name'],
					'id'    => $result,
				);
			}

			$progress->tick();
		}

		$progress->finish();

		// Display results
		WP_CLI::log( '' );
		WP_CLI::success( 'Successfully created ' . count( $results['success'] ) . ' fields.' );
		
		if ( ! empty( $results['errors'] ) ) {
			WP_CLI::warning( 'Failed to create ' . count( $results['errors'] ) . ' fields:' );
			foreach ( $results['errors'] as $error ) {
				WP_CLI::log( '  - ' . $error['field'] . ': ' . $error['error'] );
			}
		}
	}

	/**
	 * List custom objects in HubSpot
	 *
	 * ## OPTIONS
	 *
	 * [--format=<format>]
	 * : Output format (table, csv, json, count)
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # List all custom objects
	 *     wp ennu hubspot list-objects
	 *
	 *     # Export as JSON
	 *     wp ennu hubspot list-objects --format=json
	 *
	 * @param array $args Command arguments
	 * @param array $assoc_args Command options
	 */
	public function list_objects( $args, $assoc_args ) {
		WP_CLI::log( 'Fetching custom objects from HubSpot...' );

		$objects = $this->get_custom_objects();
		
		if ( is_wp_error( $objects ) ) {
			WP_CLI::error( $objects->get_error_message() );
		}

		if ( empty( $objects ) ) {
			WP_CLI::warning( 'No custom objects found in HubSpot.' );
			return;
		}

		$items = array();
		foreach ( $objects as $object ) {
			$items[] = array(
				'object_type_id' => $object['name'],
				'singular_label' => $object['labels']['singular'],
				'plural_label'   => $object['labels']['plural'],
			);
		}

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, array( 'object_type_id', 'singular_label', 'plural_label' ) );
	}

	/**
	 * List fields for a custom object
	 *
	 * ## OPTIONS
	 *
	 * <object_type>
	 * : The HubSpot custom object type ID
	 *
	 * [--format=<format>]
	 * : Output format (table, csv, json, count)
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # List fields for biomarker object
	 *     wp ennu hubspot list-fields p_biomarkers
	 *
	 * @param array $args Command arguments
	 * @param array $assoc_args Command options
	 */
	public function list_fields( $args, $assoc_args ) {
		list( $object_type ) = $args;

		WP_CLI::log( 'Fetching fields for object: ' . $object_type );

		$fields = $this->get_object_fields( $object_type );
		
		if ( is_wp_error( $fields ) ) {
			WP_CLI::error( $fields->get_error_message() );
		}

		if ( empty( $fields ) ) {
			WP_CLI::warning( 'No fields found for object: ' . $object_type );
			return;
		}

		$items = array();
		foreach ( $fields as $field ) {
			$items[] = array(
				'name'      => $field['name'],
				'label'     => $field['label'],
				'type'      => $field['type'],
				'fieldType' => $field['fieldType'],
				'group'     => $field['groupName'],
			);
		}

		\WP_CLI\Utils\format_items( $assoc_args['format'], $items, array( 'name', 'label', 'type', 'fieldType', 'group' ) );
	}

	/**
	 * Create a custom object in HubSpot
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : Object name (lowercase with underscores)
	 *
	 * <singular_label>
	 * : Singular label for the object
	 *
	 * <plural_label>
	 * : Plural label for the object
	 *
	 * [--primary-property=<property>]
	 * : Primary display property name
	 *
	 * [--secondary-properties=<properties>]
	 * : Comma-separated list of secondary display properties
	 *
	 * [--associated-objects=<objects>]
	 * : Comma-separated list of associated objects (CONTACT, COMPANY, DEAL)
	 *
	 * ## EXAMPLES
	 *
	 *     # Create biomarker object
	 *     wp ennu hubspot create-object biomarkers "Biomarker" "Biomarkers" --primary-property=biomarker_name --associated-objects=CONTACT
	 *
	 * @param array $args Command arguments
	 * @param array $assoc_args Command options
	 */
	public function create_object( $args, $assoc_args ) {
		list( $name, $singular_label, $plural_label ) = $args;

		// Validate name format
		if ( ! preg_match( '/^[a-z][a-z0-9_]*$/', $name ) ) {
			WP_CLI::error( 'Object name must be lowercase with underscores only.' );
		}

		$object_data = array(
			'name'        => $name,
			'labels'      => array(
				'singular' => $singular_label,
				'plural'   => $plural_label,
			),
			'properties'  => array(
				array(
					'name'        => $name . '_name',
					'label'       => ucwords( str_replace( '_', ' ', $name ) ) . ' Name',
					'type'        => 'string',
					'fieldType'   => 'text',
					'groupName'   => 'ennu_life_assessments',
					'description' => 'Primary name field for ' . $singular_label,
				),
			),
			'associatedObjects' => array( 'CONTACT' ),
		);

		// Add primary property
		if ( isset( $assoc_args['primary-property'] ) ) {
			$object_data['primaryDisplayProperty'] = $assoc_args['primary-property'];
		} else {
			$object_data['primaryDisplayProperty'] = $name . '_name';
		}

		// Add secondary properties
		if ( isset( $assoc_args['secondary-properties'] ) ) {
			$object_data['secondaryDisplayProperties'] = explode( ',', $assoc_args['secondary-properties'] );
		}

		// Add associated objects
		if ( isset( $assoc_args['associated-objects'] ) ) {
			$object_data['associatedObjects'] = explode( ',', $assoc_args['associated-objects'] );
		}

		WP_CLI::log( 'Creating custom object: ' . $name );

		$result = $this->create_custom_object( $object_data );
		
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::success( 'Custom object created successfully: ' . $result );
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
	 * Get fields for a custom object
	 *
	 * @since 64.2.1
	 * @param string $object_type Object type ID
	 * @return array|WP_Error
	 */
	private function get_object_fields( $object_type ) {
		$response = wp_remote_get( $this->api_base_url . '/crm/v3/properties/' . $object_type, $this->api_params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || ! isset( $data['results'] ) ) {
			return new WP_Error( 'api_error', 'Invalid response from HubSpot API' );
		}

		return $data['results'];
	}

	/**
	 * Create a custom object in HubSpot
	 *
	 * @since 64.2.1
	 * @param array $object_data Object definition
	 * @return string|WP_Error Object ID or error
	 */
	private function create_custom_object( $object_data ) {
		$params           = $this->api_params;
		$params['body']   = wp_json_encode( $object_data );
		$params['method'] = 'POST';

		$response = wp_remote_request( $this->api_base_url . '/crm/v3/schemas', $params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || isset( $data['error'] ) ) {
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Unknown error';
			return new WP_Error( 'api_error', $error_message );
		}

		return $data['objectTypeId'];
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
	 * Validate field schema
	 *
	 * @since 64.2.1
	 * @param array $fields Field definitions
	 * @return true|WP_Error
	 */
	private function validate_field_schema( $fields ) {
		$supported_field_types = array(
			'string'      => array( 'text', 'textarea' ),
			'number'      => array( 'number' ),
			'date'        => array( 'date' ),
			'enumeration' => array( 'select', 'radio', 'checkbox' ),
			'boolean'     => array( 'booleancheckbox' ),
		);

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
			if ( ! isset( $supported_field_types[ $field['type'] ] ) ) {
				return new WP_Error( 'validation_error', 'Unsupported field type: ' . $field['type'] );
			}

			// Check if fieldType is valid for the given type
			$valid_field_types = $supported_field_types[ $field['type'] ];
			if ( ! in_array( $field['fieldType'], $valid_field_types, true ) ) {
				return new WP_Error( 'validation_error', 'Invalid fieldType for type ' . $field['type'] . ': ' . $field['fieldType'] );
			}
		}

		return true;
	}

	/**
	 * Get predefined field templates
	 *
	 * @since 64.2.1
	 * @param string $template Template name
	 * @return array|WP_Error Field definitions or error
	 */
	private function get_template_fields( $template ) {
		$templates = array(
			'biomarkers'   => array(
				array( 'name' => 'biomarker_name', 'label' => 'Biomarker Name', 'type' => 'string', 'fieldType' => 'text' ),
				array( 'name' => 'biomarker_value', 'label' => 'Biomarker Value', 'type' => 'number', 'fieldType' => 'number' ),
				array( 'name' => 'biomarker_unit', 'label' => 'Unit', 'type' => 'string', 'fieldType' => 'text' ),
				array( 'name' => 'biomarker_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
				array( 'name' => 'biomarker_date', 'label' => 'Test Date', 'type' => 'date', 'fieldType' => 'date' ),
				array( 'name' => 'biomarker_notes', 'label' => 'Notes', 'type' => 'string', 'fieldType' => 'textarea' ),
			),
			'symptoms'     => array(
				array( 'name' => 'symptom_name', 'label' => 'Symptom Name', 'type' => 'string', 'fieldType' => 'text' ),
				array( 'name' => 'symptom_severity', 'label' => 'Severity', 'type' => 'enumeration', 'fieldType' => 'select' ),
				array( 'name' => 'symptom_frequency', 'label' => 'Frequency', 'type' => 'enumeration', 'fieldType' => 'select' ),
				array( 'name' => 'symptom_duration', 'label' => 'Duration', 'type' => 'string', 'fieldType' => 'text' ),
				array( 'name' => 'symptom_notes', 'label' => 'Notes', 'type' => 'string', 'fieldType' => 'textarea' ),
			),
			'assessments'  => array(
				array( 'name' => 'assessment_type', 'label' => 'Assessment Type', 'type' => 'enumeration', 'fieldType' => 'select' ),
				array( 'name' => 'assessment_score', 'label' => 'Score', 'type' => 'number', 'fieldType' => 'number' ),
				array( 'name' => 'assessment_date', 'label' => 'Assessment Date', 'type' => 'date', 'fieldType' => 'date' ),
				array( 'name' => 'assessment_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
				array( 'name' => 'assessment_completion', 'label' => 'Completion %', 'type' => 'number', 'fieldType' => 'number' ),
			),
			'health_goals' => array(
				array( 'name' => 'goal_type', 'label' => 'Goal Type', 'type' => 'enumeration', 'fieldType' => 'select' ),
				array( 'name' => 'goal_target', 'label' => 'Target Value', 'type' => 'string', 'fieldType' => 'text' ),
				array( 'name' => 'goal_deadline', 'label' => 'Deadline', 'type' => 'date', 'fieldType' => 'date' ),
				array( 'name' => 'goal_progress', 'label' => 'Progress %', 'type' => 'number', 'fieldType' => 'number' ),
				array( 'name' => 'goal_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
			),
		);

		if ( ! isset( $templates[ $template ] ) ) {
			return new WP_Error( 'invalid_template', 'Invalid template: ' . $template . '. Available templates: ' . implode( ', ', array_keys( $templates ) ) );
		}

		return $templates[ $template ];
	}
}

// Register WP-CLI commands
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'ennu hubspot', 'ENNU_HubSpot_CLI_Commands' );
} 