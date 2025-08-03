<?php
/**
 * HubSpot API Manager
 * 
 * Handles all HubSpot API authentication, credential management, and API calls
 *
 * @package ENNU_Life
 * @version 64.7.1
 */

class ENNU_HubSpot_API_Manager {
	
	/**
	 * API base URL
	 */
	private $api_base_url = 'https://api.hubapi.com';
	
	/**
	 * API credentials
	 */
	private $api_key;
	private $access_token;
	private $refresh_token;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->load_credentials();
	}
	
	/**
	 * Load API credentials from WordPress options
	 */
	private function load_credentials() {
		$this->api_key = get_option('ennu_hubspot_api_key');
		$this->access_token = get_option('ennu_hubspot_access_token');
		$this->refresh_token = get_option('ennu_hubspot_refresh_token');
	}
	
	/**
	 * Check if API credentials are configured
	 */
	public function has_credentials() {
		return !empty($this->api_key) || !empty($this->access_token);
	}
	
	/**
	 * Get API parameters for requests
	 */
	public function get_api_params() {
		$params = array(
			'timeout' => 30,
			'headers' => array(
				'Content-Type' => 'application/json',
				'User-Agent' => 'ENNU-Life-Plugin/64.7.1'
			)
		);
		
		if (!empty($this->access_token)) {
			$params['headers']['Authorization'] = 'Bearer ' . $this->access_token;
		} elseif (!empty($this->api_key)) {
			$params['headers']['Authorization'] = 'Bearer ' . $this->api_key;
		} else {
			return new WP_Error('no_credentials', 'No HubSpot API credentials configured');
		}
		
		return $params;
	}
	
	/**
	 * Test API connection
	 */
	public function test_connection() {
		$params = $this->get_api_params();
		
		if (is_wp_error($params)) {
			return $params;
		}
		
		$response = wp_remote_get($this->api_base_url . '/crm/v3/objects/contacts', $params);
		
		if (is_wp_error($response)) {
			return $response;
		}
		
		$status_code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		
		if ($status_code === 200) {
			return array('success' => true, 'message' => 'API connection successful');
		} else {
			return array('success' => false, 'message' => "API connection failed: {$status_code}", 'body' => $body);
		}
	}
	
	/**
	 * Refresh access token if needed
	 */
	public function refresh_token_if_needed() {
		if (empty($this->refresh_token)) {
			return false;
		}
		
		$response = wp_remote_post('https://api.hubapi.com/oauth/v1/token', array(
			'timeout' => 30,
			'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
			'body' => array(
				'grant_type' => 'refresh_token',
				'client_id' => get_option('ennu_hubspot_client_id'),
				'client_secret' => get_option('ennu_hubspot_client_secret'),
				'refresh_token' => $this->refresh_token
			)
		));
		
		if (is_wp_error($response)) {
			return false;
		}
		
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		
		if (isset($data['access_token'])) {
			update_option('ennu_hubspot_access_token', $data['access_token']);
			$this->access_token = $data['access_token'];
			return true;
		}
		
		return false;
	}
	
	/**
	 * Make API request with automatic token refresh
	 */
	public function api_request($endpoint, $method = 'GET', $data = null) {
		$params = $this->get_api_params();
		
		if (is_wp_error($params)) {
			return $params;
		}
		
		$url = $this->api_base_url . $endpoint;
		
		switch ($method) {
			case 'GET':
				$response = wp_remote_get($url, $params);
				break;
			case 'POST':
				$params['body'] = json_encode($data);
				$response = wp_remote_post($url, $params);
				break;
			case 'PUT':
				$params['method'] = 'PUT';
				$params['body'] = json_encode($data);
				$response = wp_remote_request($url, $params);
				break;
			case 'DELETE':
				$params['method'] = 'DELETE';
				$response = wp_remote_request($url, $params);
				break;
			default:
				return new WP_Error('invalid_method', 'Invalid HTTP method');
		}
		
		if (is_wp_error($response)) {
			return $response;
		}
		
		$status_code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		
		// If unauthorized, try to refresh token and retry once
		if ($status_code === 401 && $this->refresh_token_if_needed()) {
			$params = $this->get_api_params();
			if (!is_wp_error($params)) {
				$response = wp_remote_request($url, $params);
				if (!is_wp_error($response)) {
					$status_code = wp_remote_retrieve_response_code($response);
					$body = wp_remote_retrieve_body($response);
				}
			}
		}
		
		return array(
			'status_code' => $status_code,
			'body' => $body,
			'success' => $status_code >= 200 && $status_code < 300
		);
	}
	
	/**
	 * Get property groups
	 */
	public function get_property_groups($object_type = 'contacts') {
		$response = $this->api_request("/crm/v3/properties/{$object_type}/groups");
		
		if (is_wp_error($response)) {
			return $response;
		}
		
		if (!$response['success']) {
			return new WP_Error('api_error', "Failed to get property groups: {$response['status_code']}");
		}
		
		$data = json_decode($response['body'], true);
		return isset($data['results']) ? $data['results'] : array();
	}
	
	/**
	 * Create property group
	 */
	public function create_property_group($object_type, $group_name, $display_name = null) {
		$data = array(
			'name' => $group_name,
			'label' => $display_name ? $display_name : $group_name,
			'description' => "Property group for " . ($display_name ? $display_name : $group_name),
			'displayOrder' => 1
		);
		
		$response = $this->api_request("/crm/v3/properties/{$object_type}/groups", 'POST', $data);
		
		if (is_wp_error($response)) {
			return $response;
		}
		
		if (!$response['success']) {
			return new WP_Error('api_error', "Failed to create property group: {$response['status_code']}");
		}
		
		$data = json_decode($response['body'], true);
		return $data;
	}
	
	/**
	 * Get existing properties
	 */
	public function get_existing_properties($object_type = 'contacts') {
		$response = $this->api_request("/crm/v3/properties/{$object_type}");
		
		if (is_wp_error($response)) {
			return $response;
		}
		
		if (!$response['success']) {
			return new WP_Error('api_error', "Failed to get properties: {$response['status_code']}");
		}
		
		$data = json_decode($response['body'], true);
		return isset($data['results']) ? $data['results'] : array();
	}
	
	/**
	 * Create property
	 */
	public function create_property($object_type, $property_data) {
		$response = $this->api_request("/crm/v3/properties/{$object_type}", 'POST', $property_data);
		
		if (is_wp_error($response)) {
			return $response;
		}
		
		if (!$response['success']) {
			return new WP_Error('api_error', "Failed to create property: {$response['status_code']} - {$response['body']}");
		}
		
		$data = json_decode($response['body'], true);
		return $data;
	}
	
	/**
	 * Check if property exists
	 */
	public function property_exists($property_name, $object_type = 'contacts') {
		$properties = $this->get_existing_properties($object_type);
		
		if (is_wp_error($properties)) {
			return $properties;
		}
		
		foreach ($properties as $property) {
			if ($property['name'] === $property_name) {
				return true;
			}
		}
		
		return false;
	}
} 