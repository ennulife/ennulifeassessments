<?php
/**
 * Field Type Mapper
 * 
 * Standardizes field type mapping across all HubSpot field creation systems
 *
 * @package ENNU_Life
 * @version 64.7.1
 */

class ENNU_Field_Type_Mapper {
	
	/**
	 * Map WordPress question types to HubSpot field types
	 */
	public static function map_question_type_to_hubspot_type($question_type) {
		$mapping = array(
			'text' => 'string',
			'textarea' => 'string',
			'email' => 'string',
			'url' => 'string',
			'number' => 'number',
			'select' => 'enumeration',
			'radio' => 'enumeration',
			'checkbox' => 'boolean',
			'date' => 'date',
			'tel' => 'string',
			'url' => 'string',
			'password' => 'string',
			'file' => 'string',
			'hidden' => 'string',
			'range' => 'number',
			'color' => 'string',
			'search' => 'string',
			'time' => 'string',
			'week' => 'string',
			'month' => 'string',
			'datetime-local' => 'string'
		);
		
		return isset($mapping[$question_type]) ? $mapping[$question_type] : 'string';
	}
	
	/**
	 * Map WordPress question types to HubSpot field types with validation
	 */
	public static function map_question_type_to_field_type($question_type) {
		$mapping = array(
			'text' => 'text',
			'textarea' => 'textarea',
			'email' => 'text',
			'url' => 'text',
			'number' => 'number',
			'select' => 'select',
			'radio' => 'radio',
			'checkbox' => 'booleancheckbox',
			'date' => 'date',
			'tel' => 'text',
			'password' => 'text',
			'file' => 'text',
			'hidden' => 'text',
			'range' => 'number',
			'color' => 'text',
			'search' => 'text',
			'time' => 'text',
			'week' => 'text',
			'month' => 'text',
			'datetime-local' => 'text'
		);
		
		return isset($mapping[$question_type]) ? $mapping[$question_type] : 'text';
	}
	
	/**
	 * Get field validation rules based on type
	 */
	public static function get_field_validation($field_type, $question_type = null) {
		$validation = array();
		
		switch ($field_type) {
			case 'string':
				$validation['maxLength'] = 255;
				if ($question_type === 'textarea') {
					$validation['maxLength'] = 1000;
				}
				break;
				
			case 'number':
				$validation['minValue'] = 0;
				$validation['maxValue'] = 999999;
				break;
				
			case 'enumeration':
				// Options will be set separately
				break;
				
			case 'boolean':
				// No validation needed for boolean
				break;
				
			case 'date':
				// No validation needed for date
				break;
		}
		
		return $validation;
	}
	
	/**
	 * Validate field data before creation
	 */
	public static function validate_field_data($field_data) {
		$errors = array();
		
		// Required fields
		$required_fields = array('name', 'label', 'type');
		foreach ($required_fields as $field) {
			if (empty($field_data[$field])) {
				$errors[] = "Missing required field: {$field}";
			}
		}
		
		// Validate field name format
		if (!empty($field_data['name'])) {
			if (!preg_match('/^[a-z][a-z0-9_]*$/', $field_data['name'])) {
				$errors[] = "Field name must start with a letter and contain only lowercase letters, numbers, and underscores";
			}
		}
		
		// Validate field type
		$valid_types = array('string', 'number', 'enumeration', 'boolean', 'date');
		if (!empty($field_data['type']) && !in_array($field_data['type'], $valid_types)) {
			$errors[] = "Invalid field type: {$field_data['type']}";
		}
		
		// Validate options for enumeration
		if (!empty($field_data['type']) && $field_data['type'] === 'enumeration') {
			if (empty($field_data['options']) || !is_array($field_data['options'])) {
				$errors[] = "Enumeration fields must have options array";
			}
		}
		
		return $errors;
	}
	
	/**
	 * Generate HubSpot property data
	 */
	public static function generate_property_data($field_name, $field_label, $field_type, $options = array(), $group_name = null) {
		$property_data = array(
			'name' => $field_name,
			'label' => $field_label,
			'type' => $field_type,
			'fieldType' => self::map_question_type_to_field_type($field_type),
			'groupName' => $group_name,
			'description' => "Auto-generated field: {$field_label}",
			'hasUniqueValue' => false,
			'hidden' => false,
			'options' => array()
		);
		
		// Add validation
		$validation = self::get_field_validation($field_type);
		if (!empty($validation)) {
			$property_data['modificationMetadata'] = array(
				'readOnlyValue' => false,
				'readOnlyDefinition' => false,
				'archivable' => true,
				'searchableInGlobalSearch' => true
			);
		}
		
		// Add options for enumeration
		if ($field_type === 'enumeration' && !empty($options)) {
			$property_data['options'] = $options;
		}
		
		return $property_data;
	}
	
	/**
	 * Sanitize field name for HubSpot
	 */
	public static function sanitize_field_name($name) {
		// Convert to lowercase
		$name = strtolower($name);
		
		// Replace spaces and special characters with underscores
		$name = preg_replace('/[^a-z0-9_]/', '_', $name);
		
		// Remove multiple consecutive underscores
		$name = preg_replace('/_+/', '_', $name);
		
		// Remove leading/trailing underscores
		$name = trim($name, '_');
		
		// Ensure it starts with a letter
		if (!preg_match('/^[a-z]/', $name)) {
			$name = 'field_' . $name;
		}
		
		return $name;
	}
	
	/**
	 * Generate field description
	 */
	public static function generate_field_description($question, $assessment_name = null) {
		$description = "Assessment question: {$question}";
		
		if ($assessment_name) {
			$description .= " (Assessment: {$assessment_name})";
		}
		
		return $description;
	}
} 