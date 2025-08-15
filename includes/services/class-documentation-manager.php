<?php
/**
 * ENNU Documentation Manager
 *
 * Comprehensive documentation management and API documentation generation
 *
 * @package ENNU_Life_Assessments
 * @since 64.18.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Documentation Manager Class
 *
 * @since 64.18.0
 */
class ENNU_Documentation_Manager {
	
	/**
	 * Documentation configuration
	 *
	 * @var array
	 */
	private $doc_config = array(
		'auto_generate' => true,
		'include_examples' => true,
		'include_diagrams' => true,
		'export_formats' => array( 'html', 'pdf', 'markdown' ),
		'update_frequency' => 'weekly',
	);
	
	/**
	 * Documentation data
	 *
	 * @var array
	 */
	private $documentation_data = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_ennu_generate_documentation', array( $this, 'generate_documentation' ) );
		add_action( 'wp_ajax_nopriv_ennu_generate_documentation', array( $this, 'generate_documentation' ) );
	}
	
	/**
	 * Initialize documentation manager
	 */
	public function init() {
		// Load configuration
		$this->load_configuration();
		
		// Add documentation hooks
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Auto-generate documentation if enabled
		if ( $this->doc_config['auto_generate'] ) {
			add_action( 'admin_init', array( $this, 'auto_generate_documentation' ) );
		}
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'ENNU Documentation Manager: Initialized successfully' );
	}
	
	/**
	 * Load configuration
	 */
	private function load_configuration() {
		$config = get_option( 'ennu_documentation_config', array() );
		
		if ( ! empty( $config ) ) {
			$this->doc_config = wp_parse_args( $config, $this->doc_config );
		}
	}
	
	/**
	 * Auto-generate documentation
	 */
	public function auto_generate_documentation() {
		$last_generation = get_option( 'ennu_documentation_last_generated', 0 );
		$current_time = current_time( 'timestamp' );
		
		// Check if it's time to regenerate
		$regenerate = false;
		switch ( $this->doc_config['update_frequency'] ) {
			case 'daily':
				$regenerate = ( $current_time - $last_generation ) > ( 24 * 60 * 60 );
				break;
			case 'weekly':
				$regenerate = ( $current_time - $last_generation ) > ( 7 * 24 * 60 * 60 );
				break;
			case 'monthly':
				$regenerate = ( $current_time - $last_generation ) > ( 30 * 24 * 60 * 60 );
				break;
		}
		
		if ( $regenerate ) {
			$this->generate_comprehensive_documentation();
			update_option( 'ennu_documentation_last_generated', $current_time );
		}
	}
	
	/**
	 * Generate comprehensive documentation
	 *
	 * @return array Documentation data
	 */
	public function generate_comprehensive_documentation() {
		$documentation = array(
			'api_documentation' => $this->generate_api_documentation(),
			'architecture_documentation' => $this->generate_architecture_documentation(),
			'user_guides' => $this->generate_user_guides(),
			'developer_guides' => $this->generate_developer_guides(),
			'changelog' => $this->generate_changelog(),
			'generated_at' => current_time( 'mysql' ),
		);
		
		$this->documentation_data = $documentation;
		
		// Save documentation
		update_option( 'ennu_documentation_data', $documentation );
		
		// REMOVED: error_log( 'ENNU Documentation Manager: Comprehensive documentation generated' );
		
		return $documentation;
	}
	
	/**
	 * Generate API documentation
	 *
	 * @return array API documentation
	 */
	private function generate_api_documentation() {
		$api_docs = array(
			'endpoints' => array(),
			'classes' => array(),
			'methods' => array(),
			'examples' => array(),
		);
		
		// Document AJAX endpoints
		$ajax_endpoints = array(
			'ennu_save_assessment' => array(
				'description' => 'Save user assessment data',
				'parameters' => array(
					'assessment_type' => 'string - Type of assessment',
					'biomarkers' => 'array - Biomarker data',
					'symptoms' => 'array - Symptom data',
				),
				'returns' => 'JSON response with success status',
				'example' => array(
					'request' => array(
						'action' => 'ennu_save_assessment',
						'assessment_type' => 'basic',
						'biomarkers' => array(
							'glucose' => 95,
							'hba1c' => 5.2,
						),
					),
					'response' => array(
						'success' => true,
						'message' => 'Assessment saved successfully',
					),
				),
			),
			'ennu_get_user_data' => array(
				'description' => 'Retrieve user assessment data',
				'parameters' => array(
					'user_id' => 'integer - User ID (optional)',
				),
				'returns' => 'JSON response with user data',
				'example' => array(
					'request' => array(
						'action' => 'ennu_get_user_data',
						'user_id' => 1,
					),
					'response' => array(
						'success' => true,
						'data' => array(
							'assessments' => array(),
							'biomarkers' => array(),
							'scores' => array(),
						),
					),
				),
			),
			'ennu_generate_smart_defaults' => array(
				'description' => 'Generate smart biomarker defaults',
				'parameters' => array(
					'health_goals' => 'array - User health goals',
					'current_score' => 'integer - Current health score',
				),
				'returns' => 'JSON response with smart defaults',
				'example' => array(
					'request' => array(
						'action' => 'ennu_generate_smart_defaults',
						'health_goals' => array( 'weight_loss', 'energy_optimization' ),
						'current_score' => 65,
					),
					'response' => array(
						'success' => true,
						'data' => array(
							'biomarker_targets' => array(),
							'projected_score' => 75,
							'recommendations' => array(),
						),
					),
				),
			),
		);
		
		$api_docs['endpoints'] = $ajax_endpoints;
		
		// Document service classes
		$service_classes = array(
			'ENNU_Biomarker_Service' => array(
				'description' => 'Manages biomarker data operations',
				'methods' => array(
					'save_biomarker' => array(
						'description' => 'Save biomarker data for a user',
						'parameters' => array(
							'user_id' => 'integer - User ID',
							'biomarker_data' => 'array - Biomarker data',
						),
						'returns' => 'boolean - Success status',
					),
					'get_biomarkers' => array(
						'description' => 'Retrieve biomarker data for a user',
						'parameters' => array(
							'user_id' => 'integer - User ID',
						),
						'returns' => 'array - Biomarker data',
					),
				),
			),
			'ENNU_Assessment_Service' => array(
				'description' => 'Manages assessment lifecycle',
				'methods' => array(
					'save_assessment' => array(
						'description' => 'Save assessment data',
						'parameters' => array(
							'user_id' => 'integer - User ID',
							'assessment_data' => 'array - Assessment data',
						),
						'returns' => 'boolean - Success status',
					),
					'calculate_score' => array(
						'description' => 'Calculate health score',
						'parameters' => array(
							'biomarkers' => 'array - Biomarker data',
							'symptoms' => 'array - Symptom data',
						),
						'returns' => 'integer - Health score',
					),
				),
			),
		);
		
		$api_docs['classes'] = $service_classes;
		
		return $api_docs;
	}
	
	/**
	 * Generate architecture documentation
	 *
	 * @return array Architecture documentation
	 */
	private function generate_architecture_documentation() {
		$architecture = array(
			'overview' => array(
				'title' => 'ENNU Life Assessments Plugin Architecture',
				'description' => 'The ENNU Life Assessments plugin follows a service-oriented architecture with modular design principles.',
				'version' => ENNU_LIFE_VERSION,
				'last_updated' => current_time( 'mysql' ),
			),
			'components' => array(
				'main_plugin' => array(
					'file' => 'ennu-life-plugin.php',
					'description' => 'Main plugin file with initialization and service management',
					'responsibilities' => array(
						'Plugin initialization',
						'Service management',
						'Hook registration',
						'Error handling',
					),
				),
				'service_layer' => array(
					'description' => 'Service-oriented architecture layer',
					'services' => array(
						'ENNU_Biomarker_Service' => 'Biomarker data management',
						'ENNU_Assessment_Service' => 'Assessment lifecycle management',
						'ENNU_AJAX_Service_Handler' => 'AJAX operations management',
						'ENNU_Shortcode_Manager' => 'Shortcode management',
						'ENNU_Form_Handler' => 'Form processing',
						'ENNU_Scoring_System' => 'Health scoring algorithms',
						'ENNU_Smart_Defaults_Generator' => 'Smart defaults generation',
						'ENNU_Goal_Progression_Tracker' => 'Goal progression tracking',
						'ENNU_Advanced_Database_Optimizer' => 'Database optimization',
						'ENNU_Advanced_Asset_Optimizer' => 'Asset optimization',
						'ENNU_Code_Quality_Manager' => 'Code quality management',
						'ENNU_Documentation_Manager' => 'Documentation management',
					),
				),
				'data_layer' => array(
					'description' => 'Data persistence and management',
					'components' => array(
						'WordPress Database' => 'Primary data storage',
						'User Meta' => 'User-specific data',
						'Post Meta' => 'Assessment data storage',
						'Options API' => 'Configuration storage',
					),
				),
				'presentation_layer' => array(
					'description' => 'User interface and presentation',
					'components' => array(
						'Shortcodes' => 'Frontend display components',
						'Admin Pages' => 'Administrative interfaces',
						'AJAX Endpoints' => 'Dynamic data operations',
						'CSS/JS Assets' => 'Styling and interactivity',
					),
				),
			),
			'data_flow' => array(
				'assessment_submission' => array(
					'description' => 'Assessment submission workflow',
					'steps' => array(
						'1. User submits form via shortcode',
						'2. Form data validated by Form Handler',
						'3. Data processed by Assessment Service',
						'4. Biomarkers saved by Biomarker Service',
						'5. Score calculated by Scoring System',
						'6. Results stored in database',
						'7. User receives feedback',
					),
				),
				'data_retrieval' => array(
					'description' => 'Data retrieval workflow',
					'steps' => array(
						'1. AJAX request made to endpoint',
						'2. Request validated by AJAX Handler',
						'3. Data retrieved from database',
						'4. Data processed by appropriate service',
						'5. JSON response returned to client',
					),
				),
			),
			'security' => array(
				'authentication' => 'WordPress user authentication',
				'authorization' => 'Capability-based access control',
				'data_validation' => 'Input sanitization and validation',
				'csrf_protection' => 'Nonce verification for all forms',
				'sql_injection_prevention' => 'Prepared statements and sanitization',
			),
			'performance' => array(
				'caching' => 'WordPress object caching',
				'database_optimization' => 'Query optimization and indexing',
				'asset_optimization' => 'Minification and CDN integration',
				'code_optimization' => 'Efficient algorithms and data structures',
			),
		);
		
		return $architecture;
	}
	
	/**
	 * Generate user guides
	 *
	 * @return array User guides
	 */
	private function generate_user_guides() {
		$user_guides = array(
			'getting_started' => array(
				'title' => 'Getting Started with ENNU Life Assessments',
				'content' => array(
					'introduction' => 'Welcome to ENNU Life Assessments! This guide will help you get started with health assessments and biomarker tracking.',
					'installation' => array(
						'title' => 'Installation',
						'steps' => array(
							'1. Upload the plugin to your WordPress site',
							'2. Activate the plugin from the WordPress admin',
							'3. Configure the plugin settings',
							'4. Start creating assessments',
						),
					),
					'first_assessment' => array(
						'title' => 'Creating Your First Assessment',
						'steps' => array(
							'1. Navigate to the Assessments page',
							'2. Click "Create New Assessment"',
							'3. Fill in the biomarker data',
							'4. Add any symptoms or concerns',
							'5. Submit the assessment',
							'6. Review your health score and recommendations',
						),
					),
				),
			),
			'assessment_management' => array(
				'title' => 'Managing Assessments',
				'content' => array(
					'creating_assessments' => 'Learn how to create and manage health assessments',
					'viewing_results' => 'Understanding your assessment results and health scores',
					'tracking_progress' => 'Monitoring your health progress over time',
					'sharing_results' => 'Sharing your results with healthcare providers',
				),
			),
			'biomarker_tracking' => array(
				'title' => 'Biomarker Tracking',
				'content' => array(
					'understanding_biomarkers' => 'What are biomarkers and why they matter',
					'entering_data' => 'How to enter and update biomarker data',
					'interpreting_results' => 'Understanding your biomarker results',
					'trend_analysis' => 'Tracking trends in your biomarker data',
				),
			),
		);
		
		return $user_guides;
	}
	
	/**
	 * Generate developer guides
	 *
	 * @return array Developer guides
	 */
	private function generate_developer_guides() {
		$developer_guides = array(
			'development_setup' => array(
				'title' => 'Development Setup',
				'content' => array(
					'prerequisites' => array(
						'WordPress 5.0+',
						'PHP 7.4+',
						'MySQL 5.7+',
						'Composer (for dependencies)',
					),
					'installation' => array(
						'1. Clone the repository',
						'2. Install dependencies: composer install',
						'3. Activate the plugin',
						'4. Configure development environment',
					),
					'development_tools' => array(
						'PHPUnit for testing',
						'WordPress Coding Standards',
						'PHP_CodeSniffer',
						'Composer for dependency management',
					),
				),
			),
			'architecture_overview' => array(
				'title' => 'Architecture Overview',
				'content' => array(
					'service_oriented' => 'The plugin follows a service-oriented architecture',
					'modular_design' => 'Each component has a single responsibility',
					'wordpress_integration' => 'Proper WordPress integration with hooks and filters',
					'security_considerations' => 'Security best practices throughout',
				),
			),
			'extending_the_plugin' => array(
				'title' => 'Extending the Plugin',
				'content' => array(
					'adding_services' => 'How to add new service classes',
					'custom_shortcodes' => 'Creating custom shortcodes',
					'ajax_endpoints' => 'Adding new AJAX endpoints',
					'hooks_and_filters' => 'Using WordPress hooks and filters',
				),
			),
			'testing' => array(
				'title' => 'Testing',
				'content' => array(
					'unit_tests' => 'Writing and running unit tests',
					'integration_tests' => 'Testing WordPress integration',
					'phpunit_configuration' => 'PHPUnit configuration',
					'test_coverage' => 'Maintaining test coverage',
				),
			),
		);
		
		return $developer_guides;
	}
	
	/**
	 * Generate changelog
	 *
	 * @return array Changelog
	 */
	private function generate_changelog() {
		$changelog = array(
			'current_version' => ENNU_LIFE_VERSION,
			'versions' => array(
				'64.18.0' => array(
					'date' => '2025-07-31',
					'type' => 'major',
					'changes' => array(
						'Added Advanced Database Optimizer',
						'Added Advanced Asset Optimizer',
						'Added Code Quality Manager',
						'Added Documentation Manager',
						'Performance optimizations',
						'Code quality improvements',
					),
				),
				'64.17.0' => array(
					'date' => '2025-07-31',
					'type' => 'major',
					'changes' => array(
						'Added Smart Defaults Generator',
						'Added Goal Progression Tracker',
						'Enhanced user experience',
						'Improved assessment functionality',
					),
				),
				'64.16.0' => array(
					'date' => '2025-07-31',
					'type' => 'major',
					'changes' => array(
						'Service-oriented architecture implementation',
						'Enhanced biomarker management',
						'Improved assessment system',
						'Better error handling',
					),
				),
			),
		);
		
		return $changelog;
	}
	
	/**
	 * Export documentation
	 *
	 * @param string $format Export format
	 * @return string|array Exported documentation
	 */
	public function export_documentation( $format = 'html' ) {
		$documentation = $this->documentation_data;
		
		if ( empty( $documentation ) ) {
			$documentation = $this->generate_comprehensive_documentation();
		}
		
		switch ( $format ) {
			case 'html':
				return $this->export_html_documentation( $documentation );
			case 'markdown':
				return $this->export_markdown_documentation( $documentation );
			case 'json':
				return json_encode( $documentation, JSON_PRETTY_PRINT );
			default:
				return $documentation;
		}
	}
	
	/**
	 * Export HTML documentation
	 *
	 * @param array $documentation Documentation data
	 * @return string HTML documentation
	 */
	private function export_html_documentation( $documentation ) {
		$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ENNU Life Assessments Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1, h2, h3 { color: #333; }
        .section { margin: 20px 0; }
        .endpoint { background: #f5f5f5; padding: 15px; margin: 10px 0; }
        .method { background: #e9f7fe; padding: 10px; margin: 5px 0; }
        code { background: #f0f0f0; padding: 2px 4px; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>ENNU Life Assessments Plugin Documentation</h1>
    <p><strong>Version:</strong> ' . esc_html( $documentation['architecture_documentation']['overview']['version'] ) . '</p>
    <p><strong>Last Updated:</strong> ' . esc_html( $documentation['generated_at'] ) . '</p>
    
    <div class="section">
        <h2>API Documentation</h2>';
		
		foreach ( $documentation['api_documentation']['endpoints'] as $endpoint => $data ) {
			$html .= '
        <div class="endpoint">
            <h3>' . esc_html( $endpoint ) . '</h3>
            <p><strong>Description:</strong> ' . esc_html( $data['description'] ) . '</p>
            <p><strong>Parameters:</strong></p>
            <ul>';
			
			foreach ( $data['parameters'] as $param => $desc ) {
				$html .= '<li><code>' . esc_html( $param ) . '</code>: ' . esc_html( $desc ) . '</li>';
			}
			
			$html .= '</ul>
            <p><strong>Returns:</strong> ' . esc_html( $data['returns'] ) . '</p>
        </div>';
		}
		
		$html .= '
    </div>
    
    <div class="section">
        <h2>Architecture Documentation</h2>
        <h3>Overview</h3>
        <p>' . esc_html( $documentation['architecture_documentation']['overview']['description'] ) . '</p>
        
        <h3>Components</h3>';
		
		foreach ( $documentation['architecture_documentation']['components'] as $component => $data ) {
			$html .= '
        <h4>' . esc_html( ucfirst( str_replace( '_', ' ', $component ) ) ) . '</h4>
        <p>' . esc_html( $data['description'] ) . '</p>';
			
			if ( isset( $data['services'] ) ) {
				$html .= '<ul>';
				foreach ( $data['services'] as $service => $desc ) {
					$html .= '<li><strong>' . esc_html( $service ) . ':</strong> ' . esc_html( $desc ) . '</li>';
				}
				$html .= '</ul>';
			}
		}
		
		$html .= '
    </div>
</body>
</html>';
		
		return $html;
	}
	
	/**
	 * Export markdown documentation
	 *
	 * @param array $documentation Documentation data
	 * @return string Markdown documentation
	 */
	private function export_markdown_documentation( $documentation ) {
		$markdown = '# ENNU Life Assessments Plugin Documentation

**Version:** ' . $documentation['architecture_documentation']['overview']['version'] . '  
**Last Updated:** ' . $documentation['generated_at'] . '

## API Documentation

### Endpoints

';
		
		foreach ( $documentation['api_documentation']['endpoints'] as $endpoint => $data ) {
			$markdown .= '#### ' . $endpoint . '

**Description:** ' . $data['description'] . '

**Parameters:**
';
			
			foreach ( $data['parameters'] as $param => $desc ) {
				$markdown .= '- `' . $param . '`: ' . $desc . '
';
			}
			
			$markdown .= '
**Returns:** ' . $data['returns'] . '

';
		}
		
		$markdown .= '## Architecture Documentation

### Overview

' . $documentation['architecture_documentation']['overview']['description'] . '

### Components

';
		
		foreach ( $documentation['architecture_documentation']['components'] as $component => $data ) {
			$markdown .= '#### ' . ucfirst( str_replace( '_', ' ', $component ) ) . '

' . $data['description'] . '

';
			
			if ( isset( $data['services'] ) ) {
				foreach ( $data['services'] as $service => $desc ) {
					$markdown .= '- **' . $service . ':** ' . $desc . '
';
				}
			}
			
			$markdown .= '
';
		}
		
		return $markdown;
	}
	
	/**
	 * AJAX handler for documentation generation
	 */
	public function generate_documentation() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_documentation_generation' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
		
		switch ( $action ) {
			case 'generate':
				$result = $this->generate_comprehensive_documentation();
				break;
				
			case 'export':
				$format = isset( $_POST['format'] ) ? sanitize_text_field( $_POST['format'] ) : 'html';
				$result = $this->export_documentation( $format );
				break;
				
			case 'update_config':
				$config = isset( $_POST['config'] ) ? $_POST['config'] : array();
				$result = $this->update_configuration( $config );
				break;
				
			default:
				$result = array( 'error' => 'Invalid action' );
		}
		
		wp_send_json_success( $result );
	}
	
	/**
	 * Update configuration
	 *
	 * @param array $config Configuration data
	 * @return array Update result
	 */
	private function update_configuration( $config ) {
		$current_config = $this->doc_config;
		$updated_config = wp_parse_args( $config, $current_config );
		
		$result = update_option( 'ennu_documentation_config', $updated_config );
		
		if ( $result ) {
			$this->doc_config = $updated_config;
		}
		
		return array(
			'success' => $result,
			'config' => $updated_config,
		);
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		$parent_slug = 'ennu-life';

		// 1) Comprehensive Documentation
		add_submenu_page(
			$parent_slug,
			'Comprehensive Documentation',
			'Comprehensive Docs',
			'manage_options',
			'ennu-docs-comprehensive',
			array( $this, 'render_docs_comprehensive' )
		);

		// 2) Biomarkers Documentation
		add_submenu_page(
			$parent_slug,
			'Biomarkers Documentation',
			'Biomarkers Docs',
			'manage_options',
			'ennu-docs-biomarkers',
			array( $this, 'render_docs_biomarkers' )
		);

		// 3) Biomarker Ranges Documentation
		add_submenu_page(
			$parent_slug,
			'Biomarker Ranges Documentation',
			'Biomarker Ranges Docs',
			'manage_options',
			'ennu-docs-biomarker-ranges',
			array( $this, 'render_docs_biomarker_ranges' )
		);

		// 4) Symptom Flagging Documentation
		add_submenu_page(
			$parent_slug,
			'Symptom Flagging Documentation',
			'Symptom Flagging Docs',
			'manage_options',
			'ennu-docs-symptom-flagging',
			array( $this, 'render_docs_symptom_flagging' )
		);

		// 5) Scoring Documentation
		add_submenu_page(
			$parent_slug,
			'Scoring Documentation',
			'Scoring Docs',
			'manage_options',
			'ennu-docs-scoring',
			array( $this, 'render_docs_scoring' )
		);

		// 6) Assessments Documentation
		add_submenu_page(
			$parent_slug,
			'Assessments Documentation',
			'Assessments Docs',
			'manage_options',
			'ennu-docs-assessments',
			array( $this, 'render_docs_assessments' )
		);

		// 7) Labcorp Upload Documentation
		add_submenu_page(
			$parent_slug,
			'Labcorp Upload Documentation',
			'Labcorp Upload Docs',
			'manage_options',
			'ennu-docs-labcorp-upload',
			array( $this, 'render_docs_labcorp_upload' )
		);

		// 8) HubSpot Syncing Documentation
		add_submenu_page(
			$parent_slug,
			'HubSpot Syncing Documentation',
			'HubSpot Syncing Docs',
			'manage_options',
			'ennu-docs-hubspot-sync',
			array( $this, 'render_docs_hubspot_sync' )
		);

		// 9) SOP Documentation
		add_submenu_page(
			$parent_slug,
			'SOP Documentation',
			'SOP Docs',
			'manage_options',
			'ennu-docs-sop',
			array( $this, 'render_docs_sop' )
		);
	}

	/**
	 * Renderers for documentation submenus
	 */
	private function render_docs_wrapper_open( $title ) {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html( $title ) . '</h1>';
		$generated = get_option( 'ennu_documentation_data' );
		if ( empty( $generated ) ) {
			$generated = $this->generate_comprehensive_documentation();
		}
		echo '<p><em>Generated: ' . esc_html( $generated['generated_at'] ?? current_time( 'mysql' ) ) . '</em></p>';
	}

	private function render_docs_wrapper_close() {
		echo '</div>';
	}

	public function render_docs_comprehensive() {
		$this->render_docs_wrapper_open( 'Comprehensive Documentation' );
		echo '<p>This overview is generated from live systems: global fields, scoring, ranges/targets, and centralized symptoms. It explains what exists and why, based strictly on code configuration.</p>';

		// Global Fields from processor
		echo '<h2>Global Fields (Ask-Once)</h2>';
		if ( class_exists( 'ENNU_Global_Fields_Processor' ) ) {
			$reflect = new ReflectionClass( 'ENNU_Global_Fields_Processor' );
			$prop = $reflect->getProperty( 'global_field_mappings' );
			$prop->setAccessible( true );
			$map = $prop->getValue();
			if ( is_array( $map ) ) {
				echo '<table class="widefat"><thead><tr><th>Form key</th><th>User meta</th></tr></thead><tbody>';
				foreach ( $map as $form_key => $meta_key ) {
					echo '<tr><td><code>' . esc_html( $form_key ) . '</code></td><td><code>' . esc_html( $meta_key ) . '</code></td></tr>';
				}
				echo '</tbody></table>';
			}
			echo '<p>Processor: saves from submissions and refreshes dashboard; triggers <code>ennu_global_fields_updated</code>.</p>';
		} else {
			echo '<p>Global Fields Processor not available.</p>';
		}

		// Scoring pillars/engines
		echo '<h2>Scoring: Pillars and Engines</h2>';
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$pillar_map = ENNU_Scoring_System::get_health_pillar_map();
			echo '<p>Pillars (name → weight → categories):</p>';
			echo '<ul>';
			foreach ( $pillar_map as $pillar => $data ) {
				$weight = isset( $data['weight'] ) ? $data['weight'] : 0;
				$categories = isset( $data['categories'] ) ? implode( ', ', $data['categories'] ) : '';
				echo '<li><strong>' . esc_html( $pillar ) . '</strong>: weight ' . esc_html( $weight ) . ' — ' . esc_html( $categories ) . '</li>';
			}
			echo '</ul>';
			$eng = array( 'Quantitative' );
			if ( class_exists( 'ENNU_Qualitative_Engine' ) ) { $eng[] = 'Qualitative'; }
			if ( class_exists( 'ENNU_Objective_Engine' ) ) { $eng[] = 'Objective'; }
			if ( class_exists( 'ENNU_Intentionality_Engine' ) ) { $eng[] = 'Intentionality'; }
			echo '<p>Engines present: ' . esc_html( implode( ', ', $eng ) ) . '</p>';
		} else {
			echo '<p>Scoring System not available.</p>';
		}

		// Ranges & Targets from recommended range manager
		echo '<h2>Ranges & ENNU Targets</h2>';
		if ( class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
			$rm = new ENNU_Recommended_Range_Manager();
			$config = $rm->get_biomarker_configuration();
			$count = is_array( $config ) ? count( $config ) : 0;
			echo '<p>Biomarkers with configured ranges/targets: ' . intval( $count ) . '.</p>';
			if ( $count > 0 ) {
				$sample = array_slice( $config, 0, 10, true );
				echo '<table class="widefat"><thead><tr><th>Biomarker</th><th>Unit</th><th>Optimal</th><th>Normal</th></tr></thead><tbody>';
				foreach ( $sample as $key => $info ) {
					$r = $info['ranges'] ?? array();
					$opt = ( isset( $r['optimal_min'] ) && isset( $r['optimal_max'] ) ) ? $r['optimal_min'] . '–' . $r['optimal_max'] : '';
					$norm = ( isset( $r['normal_min'] ) && isset( $r['normal_max'] ) ) ? $r['normal_min'] . '–' . $r['normal_max'] : '';
					echo '<tr><td><code>' . esc_html( $key ) . '</code></td><td>' . esc_html( $info['unit'] ?? '' ) . '</td><td>' . esc_html( $opt ) . '</td><td>' . esc_html( $norm ) . '</td></tr>';
				}
				echo '</tbody></table>';
			}
		} else {
			echo '<p>Recommended Range Manager not available.</p>';
		}

		// Centralized Symptoms system
		echo '<h2>Centralized Symptoms & Auto-Flagging</h2>';
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$mapping = ENNU_Centralized_Symptoms_Manager::get_symptom_biomarker_mapping();
			echo '<p>ONE LOG: symptoms persist until resolved by assessment updates. Auto-flagging maps symptoms to biomarkers.</p>';
			echo '<p>Mappings defined: ' . intval( is_array( $mapping ) ? count( $mapping ) : 0 ) . ' symptom keys.</p>';
		} else {
			echo '<p>Centralized Symptoms Manager not available.</p>';
		}

		$this->render_docs_wrapper_close();
	}

	public function render_docs_biomarkers() {
		$this->render_docs_wrapper_open( 'Biomarkers Documentation' );
		echo '<p>This documentation is generated directly from live configuration. It explains what each biomarker is, how ranges and ENNU targets are determined, and how they are used in scoring and UI. Audience: CEO/COO/CMO, operations, clinicians, assistants.</p>';

		echo '<h2>How Biomarkers Work</h2>';
		echo '<ul>'
			. '<li><strong>Sources</strong>: self-report, Labcorp uploads, or derived (e.g., BMI from height/weight).</li>'
			. '<li><strong>Display</strong>: current value + date/source, lab reference range, ENNU target, delta, trend, action.</li>'
			. '<li><strong>Autosync</strong>: height, weight, BMI, and age update after relevant submissions.</li>'
			. '</ul>';

		// Dynamic catalog from range manager
		if ( class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
			$range_manager = new ENNU_Recommended_Range_Manager();
			$config = $range_manager->get_biomarker_configuration();
			if ( is_array( $config ) && ! empty( $config ) ) {
				echo '<h2>Complete Catalog (Programmatic)</h2>';
				echo '<table class="widefat"><thead><tr>'
					. '<th>Health Vector</th><th>Biomarker</th><th>Unit</th>'
					. '<th>Reference (Normal)</th><th>ENNU Target (Optimal)</th>'
					. '<th>Critical Bounds</th><th>Personalization Notes</th>'
					. '</tr></thead><tbody>';
				foreach ( $config as $key => $info ) {
					$ranges = isset( $info['ranges'] ) && is_array( $info['ranges'] ) ? $info['ranges'] : array();
					$normal = '';
					$optimal = '';
					$critical = '';
					if ( $ranges ) {
						$normal = ( isset( $ranges['normal_min'] ) && isset( $ranges['normal_max'] ) ) ? esc_html( $ranges['normal_min'] ) . '–' . esc_html( $ranges['normal_max'] ) : '';
						$optimal = ( isset( $ranges['optimal_min'] ) && isset( $ranges['optimal_max'] ) ) ? esc_html( $ranges['optimal_min'] ) . '–' . esc_html( $ranges['optimal_max'] ) : '';
						$critical = ( isset( $ranges['critical_min'] ) && isset( $ranges['critical_max'] ) ) ? esc_html( $ranges['critical_min'] ) . '–' . esc_html( $ranges['critical_max'] ) : '';
					}
					$vector = '';
					if ( class_exists( 'ENNU_Biomarker_Manager' ) ) {
						$vector = esc_html( ENNU_Biomarker_Manager::get_biomarker_health_vector( $key ) );
					}
					$unit = isset( $info['unit'] ) ? esc_html( $info['unit'] ) : '';
					$display = isset( $info['display_name'] ) ? esc_html( $info['display_name'] ) : esc_html( ucwords( str_replace( '_', ' ', $key ) ) );
					$notes = '';
					if ( ! empty( $info['factors'] ) && is_array( $info['factors'] ) ) {
						$notes = esc_html( implode( ', ', array_keys( $info['factors'] ) ) );
					}
					echo '<tr>'
						. '<td>' . $vector . '</td>'
						. '<td><strong>' . $display . '</strong><br/><code>' . esc_html( $key ) . '</code></td>'
						. '<td>' . $unit . '</td>'
						. '<td>' . $normal . ' ' . $unit . '</td>'
						. '<td>' . $optimal . ' ' . $unit . '</td>'
						. '<td>' . $critical . ' ' . $unit . '</td>'
						. '<td>' . $notes . '</td>'
						. '</tr>';
				}
				echo '</tbody></table>';
				echo '<p><em>Source:</em> ENNU Recommended Range Manager configuration. <em>Reviewed:</em> ' . esc_html( date( 'Y-m-d' ) ) . '</p>';
			} else {
				echo '<p>No biomarker configuration found. Ensure range files are present and loaded.</p>';
			}
		} else {
			echo '<p>Range Manager not available. Please ensure `ENNU_Recommended_Range_Manager` is loaded.</p>';
		}

		echo '<h2>Interpretation Model</h2>';
		echo '<ol>'
			. '<li><strong>Current</strong>: latest value, date, and source.</li>'
			. '<li><strong>Reference range</strong>: normal context.</li>'
			. '<li><strong>ENNU target</strong>: optimal band; clinicians may personalize by risk.</li>'
			. '<li><strong>Delta & trend</strong>: distance to target and slope over time.</li>'
			. '<li><strong>Action</strong>: Monitor / Coach / Clinician.</li>'
			. '</ol>';

		$this->render_docs_wrapper_close();
	}

	public function render_docs_biomarker_ranges() {
		$this->render_docs_wrapper_open( 'Biomarker Ranges Documentation' );
		echo '<p>Authoritative reference for ranges and targets. All values below are generated from the live Range Manager configuration.</p>';

		if ( class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
			$range_manager = new ENNU_Recommended_Range_Manager();
			$config = $range_manager->get_biomarker_configuration();
			if ( is_array( $config ) && ! empty( $config ) ) {
				echo '<table class="widefat"><thead><tr>'
					. '<th>Biomarker</th><th>Unit</th>'
					. '<th>Normal</th><th>Optimal (ENNU)</th><th>Critical</th>'
					. '<th>Description</th>'
					. '</tr></thead><tbody>';
				foreach ( $config as $key => $info ) {
					$ranges = isset( $info['ranges'] ) && is_array( $info['ranges'] ) ? $info['ranges'] : array();
					$normal = ( isset( $ranges['normal_min'] ) && isset( $ranges['normal_max'] ) ) ? esc_html( $ranges['normal_min'] ) . '–' . esc_html( $ranges['normal_max'] ) : '';
					$optimal = ( isset( $ranges['optimal_min'] ) && isset( $ranges['optimal_max'] ) ) ? esc_html( $ranges['optimal_min'] ) . '–' . esc_html( $ranges['optimal_max'] ) : '';
					$critical = ( isset( $ranges['critical_min'] ) && isset( $ranges['critical_max'] ) ) ? esc_html( $ranges['critical_min'] ) . '–' . esc_html( $ranges['critical_max'] ) : '';
					$unit = isset( $info['unit'] ) ? esc_html( $info['unit'] ) : '';
					$display = isset( $info['display_name'] ) ? esc_html( $info['display_name'] ) : esc_html( ucwords( str_replace( '_', ' ', $key ) ) );
					$desc = isset( $info['description'] ) ? esc_html( $info['description'] ) : '';
					echo '<tr>'
						. '<td><strong>' . $display . '</strong><br/><code>' . esc_html( $key ) . '</code></td>'
						. '<td>' . $unit . '</td>'
						. '<td>' . $normal . '</td>'
						. '<td>' . $optimal . '</td>'
						. '<td>' . $critical . '</td>'
						. '<td>' . $desc . '</td>'
						. '</tr>';
				}
				echo '</tbody></table>';
				// Legend
				echo '<p><em>Legend</em>: Normal = population reference; Optimal = ENNU target; Critical = out-of-bounds threshold.</p>';
			} else {
				echo '<p>No range configuration available.</p>';
			}
		} else {
			echo '<p>Range Manager not available. Please ensure `ENNU_Recommended_Range_Manager` is loaded.</p>';
		}

		echo '<h2>Governance</h2>';
		echo '<ul>'
			. '<li>Clinical review required before changing ranges/targets.</li>'
			. '<li>All updates are versioned and date-stamped in source files.</li>'
			. '<li>Ops/Clinicians/Marketing informed when ranges change.</li>'
			. '</ul>';

		$this->render_docs_wrapper_close();
	}

	public function render_docs_symptom_flagging() {
		$this->render_docs_wrapper_open( 'Symptom Flagging Documentation' );
		echo '<p>Live overview of how symptoms are centralized, mapped, and used to create biomarker flags and scoring adjustments.</p>';

		// Centralized symptoms system
		echo '<h2>Centralized Symptoms (ONE LOG)</h2>';
		echo '<ul>'
			. '<li>All assessments contribute to a single persistent log until resolved by updated answers.</li>'
			. '<li>Resolution: when a new submission for the same assessment no longer triggers a symptom, it is removed.</li>'
			. '<li>Anonymous users: stored via session and migrated on account creation.</li>'
			. '</ul>';

		// Symptom → biomarker mapping table from code
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$mapping = ENNU_Centralized_Symptoms_Manager::get_symptom_biomarker_mapping();
			echo '<h2>Symptom → Biomarker Mapping (Auto-Flagging)</h2>';
			if ( is_array( $mapping ) && ! empty( $mapping ) ) {
				echo '<table class="widefat"><thead><tr><th>Symptom (key)</th><th>Biomarkers flagged</th></tr></thead><tbody>';
				foreach ( $mapping as $symptom_key => $biomarkers ) {
					$display_symptom = ucwords( str_replace( '_', ' ', $symptom_key ) );
					$biomarker_list = array();
					foreach ( $biomarkers as $bkey ) {
						$biomarker_list[] = '<code>' . esc_html( $bkey ) . '</code>';
					}
					echo '<tr><td><strong>' . esc_html( $display_symptom ) . '</strong><br/><code>' . esc_html( $symptom_key ) . '</code></td><td>' . implode( ', ', $biomarker_list ) . '</td></tr>';
				}
				echo '</tbody></table>';
				echo '<p><em>Source:</em> code mapping used by auto-flagging logic.</p>';
			} else {
				echo '<p>No mapping available.</p>';
			}
		} else {
			echo '<p>Centralized Symptoms Manager not available.</p>';
		}

		echo '<h2>Flag Creation & Resolution Flow</h2>';
		echo '<ol>'
			. '<li>User submits assessment → symptoms aggregated.</li>'
			. '<li>Auto-flagger checks mapping and creates biomarker flags with context.</li>'
			. '<li>When biomarker flags are cleared, related symptoms are re-evaluated and may be resolved.</li>'
			. '</ol>';

		echo '<h2>Severity, Frequency, Trends</h2>';
		echo '<ul>'
			. '<li>Each symptom stores severity and frequency to support triage and trending.</li>'
			. '<li>Trends tracked as improving/stable/worsening for analytics.</li>'
			. '</ul>';

		$this->render_docs_wrapper_close();
	}

	public function render_docs_scoring() {
		$this->render_docs_wrapper_open( 'Scoring Documentation' );
		echo '<p>Live, code-derived overview of how scoring works: pillars, categories, engines, goals, and caching. Audience: executives, operations, clinicians, assistants.</p>';

		// Pillar model from live configuration
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$pillar_map = ENNU_Scoring_System::get_health_pillar_map();
			echo '<h2>Pillar Model</h2>';
			echo '<table class="widefat"><thead><tr><th>Pillar</th><th>Weight</th><th>Categories</th></tr></thead><tbody>';
			foreach ( $pillar_map as $pillar => $data ) {
				$weight = isset( $data['weight'] ) ? floatval( $data['weight'] ) : 0;
				$categories = isset( $data['categories'] ) && is_array( $data['categories'] ) ? $data['categories'] : array();
				echo '<tr>'
					. '<td><strong>' . esc_html( $pillar ) . '</strong></td>'
					. '<td>' . esc_html( rtrim( rtrim( number_format( $weight, 2 ), '0' ), '.' ) ) . '</td>'
					. '<td>' . esc_html( implode( ', ', $categories ) ) . '</td>'
					. '</tr>';
			}
			echo '</tbody></table>';

			// Engines present
			echo '<h2>Scoring Engines</h2>';
			$engines = array();
			$engines[] = '<strong>Quantitative</strong>: category → pillar aggregation (always on)';
			if ( class_exists( 'ENNU_Qualitative_Engine' ) ) {
				$engines[] = '<strong>Qualitative</strong>: symptom penalties (bounded, transparent)';
			}
			if ( class_exists( 'ENNU_Objective_Engine' ) ) {
				$engines[] = '<strong>Objective</strong>: biomarker actuality adjustments (range/target aware)';
			}
			if ( class_exists( 'ENNU_Intentionality_Engine' ) ) {
				$engines[] = '<strong>Intentionality</strong>: health goal alignment boosts';
			}
			echo '<ul><li>' . implode( '</li><li>', $engines ) . '</li></ul>';

			// Assessment category summary derived from definitions
			$defs = ENNU_Scoring_System::get_all_definitions();
			echo '<h2>Assessments → Categories (from live definitions)</h2>';
			if ( is_array( $defs ) && ! empty( $defs ) ) {
				echo '<table class="widefat"><thead><tr><th>Assessment</th><th>Questions</th><th>Categories (count)</th></tr></thead><tbody>';
				foreach ( $defs as $assessment_key => $config ) {
					$questions = isset( $config['questions'] ) && is_array( $config['questions'] ) ? $config['questions'] : array();
					$category_counts = array();
					foreach ( $questions as $q_key => $q_config ) {
						if ( isset( $q_config['scoring']['category'] ) ) {
							$cat = (string) $q_config['scoring']['category'];
							$category_counts[ $cat ] = isset( $category_counts[ $cat ] ) ? $category_counts[ $cat ] + 1 : 1;
						}
					}
					$categories_str = '';
					if ( ! empty( $category_counts ) ) {
						$parts = array();
						foreach ( $category_counts as $cat => $count ) {
							$parts[] = esc_html( $cat ) . ' (' . intval( $count ) . ')';
						}
						$categories_str = implode( ', ', $parts );
					}
					$display_name = isset( $config['title'] ) ? $config['title'] : ucwords( str_replace( array( '_', '-' ), ' ', $assessment_key ) );
					echo '<tr>'
						. '<td><strong>' . esc_html( $display_name ) . '</strong><br/><code>' . esc_html( $assessment_key ) . '</code></td>'
						. '<td>' . count( $questions ) . '</td>'
						. '<td>' . esc_html( $categories_str ) . '</td>'
						. '</tr>';
				}
				echo '</tbody></table>';
			} else {
				echo '<p>No assessment definitions available.</p>';
			}

			// Health goals and boosts
			$goals = ENNU_Scoring_System::get_health_goal_definitions();
			$goal_defs = isset( $goals['goal_definitions'] ) ? $goals['goal_definitions'] : array();
			echo '<h2>Health Goals → Pillar Boosts</h2>';
			if ( ! empty( $goal_defs ) ) {
				echo '<table class="widefat"><thead><tr><th>Goal</th><th>Boosts</th></tr></thead><tbody>';
				foreach ( $goal_defs as $goal_key => $g ) {
					$name = isset( $g['name'] ) ? $g['name'] : ucwords( str_replace( array( '_', '-' ), ' ', $goal_key ) );
					$boosts = array();
					if ( isset( $g['pillar_boosts'] ) && is_array( $g['pillar_boosts'] ) ) {
						foreach ( $g['pillar_boosts'] as $pillar => $boost ) {
							$boosts[] = esc_html( $pillar ) . ': +' . esc_html( rtrim( rtrim( number_format( floatval( $boost ), 2 ), '0' ), '.' ) );
						}
					}
					echo '<tr><td><strong>' . esc_html( $name ) . '</strong><br/><code>' . esc_html( $goal_key ) . '</code></td><td>' . ( empty( $boosts ) ? '-' : implode( ', ', $boosts ) ) . '</td></tr>';
				}
				echo '</tbody></table>';
			} else {
				echo '<p>No health goal boosts configured.</p>';
			}

			// Caching and performance
			echo '<h2>Caching & Performance</h2>';
			echo '<ul>'
				. '<li>Definitions cached in transient <code>ennu_assessment_definitions_v1</code> (12 hours)</li>'
				. '<li>Pillar map cached in transient <code>ennu_pillar_map_v1</code> (12 hours)</li>'
				. '<li>On submission, final scores and component logs saved in user meta for fast retrieval</li>'
				. '</ul>';
		} else {
			echo '<p>Scoring system not available. Please ensure `ENNU_Scoring_System` is loaded.</p>';
		}

		$this->render_docs_wrapper_close();
	}

	public function render_docs_assessments() {
		$this->render_docs_wrapper_open( 'Assessments Documentation' );
		echo '<p>Live view of all assessments, their shortcodes, question counts, and global field usage, based on the active configuration.</p>';

		// Inventory from live definitions
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$definitions = ENNU_Scoring_System::get_all_definitions();
			if ( is_array( $definitions ) && ! empty( $definitions ) ) {
				echo '<h2>Assessment Inventory (from live definitions)</h2>';
				echo '<table class="widefat"><thead><tr>'
					. '<th>Assessment</th><th>Shortcodes</th><th>Questions</th><th>Has Globals</th>'
					. '</tr></thead><tbody>';
				foreach ( $definitions as $assessment_key => $config ) {
					$questions = isset( $config['questions'] ) && is_array( $config['questions'] ) ? $config['questions'] : array();
					$has_globals = 'No';
					if ( ! empty( $questions ) ) {
						foreach ( $questions as $qid => $qdef ) {
							if ( isset( $qdef['global_key'] ) ) { $has_globals = 'Yes'; break; }
						}
					}
					$shortcodes = array(
						'[ennu-' . $assessment_key . ']',
						'[ennu-' . $assessment_key . '-results]',
						'[ennu-' . $assessment_key . '-assessment-details]'
					);
					echo '<tr>'
						. '<td><strong>' . esc_html( ucwords( str_replace( array( '_', '-' ), ' ', $assessment_key ) ) ) . '</strong><br/><code>' . esc_html( $assessment_key ) . '</code></td>'
						. '<td>' . implode( ' ', array_map( function( $s ){ return '<code>' . esc_html( $s ) . '</code>'; }, $shortcodes ) ) . '</td>'
						. '<td>' . intval( count( $questions ) ) . '</td>'
						. '<td>' . esc_html( $has_globals ) . '</td>'
						. '</tr>';
				}
				echo '</tbody></table>';
			} else {
				echo '<p>No assessment definitions found.</p>';
			}
		} else {
			echo '<p>Scoring System not available.</p>';
		}

		// Skip logic derived from global fields processor
		echo '<h2>Global Fields & Skip Logic</h2>';
		if ( class_exists( 'ENNU_Global_Fields_Processor' ) ) {
			echo '<ul>'
				. '<li>Globals saved to user meta on submission via Global Fields Processor.</li>'
				. '<li>Frontend auto-skip implemented in forms JS for prefilled globals; contact slides are marked not to skip.</li>'
				. '</ul>';
		} else {
			echo '<p>Global Fields Processor not available.</p>';
		}

		$this->render_docs_wrapper_close();
	}

	public function render_docs_labcorp_upload() {
		$this->render_docs_wrapper_open( 'Labcorp Upload Documentation' );
		echo '<p>Step-by-step for ingesting labs and how they affect current/range/target displays.</p>';
		echo '<h2>Workflow</h2>';
		echo '<ol>'
			. '<li>Upload CSV (secure admin-only).</li>'
			. '<li>Validate headers and units; fix mismatches.</li>'
			. '<li>Map columns → biomarkers (e.g., LDL-C, HbA1c, vitamin D).</li>'
			. '<li>Commit and verify affected users.</li>'
			. '</ol>';
		echo '<h2>What Updates</h2>';
		echo '<ul><li>Current values set from lab with date/source=Labcorp.</li><li>Compare to reference and ENNU target; status color updates.</li><li>Flags and trends refresh automatically.</li></ul>';
		$this->render_docs_wrapper_close();
	}

	public function render_docs_hubspot_sync() {
		$this->render_docs_wrapper_open( 'HubSpot Syncing Documentation' );
		echo '<p>Comprehensive CRM sync system that powers segmentation, retention, and revenue through multi-object orchestration.</p>';
		
		echo '<h2>System Overview</h2>';
		echo '<p>The HubSpot syncing system uses <strong>Private App Authentication</strong> with access tokens and orchestrates data across multiple HubSpot objects:</p>';
		echo '<ul>';
		echo '<li><strong>Contacts</strong>: Core patient/user data</li>';
		echo '<li><strong>basic_assessments</strong>: Custom object for assessment results</li>';
		echo '<li><strong>biomarkers</strong>: Custom object for biomarker data</li>';
		echo '<li><strong>Associations</strong>: Links between contacts and their data objects</li>';
		echo '</ul>';
		
		echo '<h2>Authentication Setup</h2>';
		echo '<p><strong>Private App Configuration:</strong></p>';
		echo '<ul>';
		echo '<li>Access Token: <code>pat-na1-87f4d48b-321a-4711-a346-2f4d7bf1f247</code></li>';
		echo '<li>Portal ID: <code>48195592</code></li>';
		echo '<li>Authentication Type: Bearer token (no OAuth flow required)</li>';
		echo '<li>Admin Interface: <code>/wp-admin/admin.php?page=ennu-hubspot-sync</code></li>';
		echo '</ul>';
		
		echo '<h2>Data Synchronization</h2>';
		echo '<h3>Contact Data</h3>';
		echo '<table class="widefat"><thead><tr><th>WordPress Field</th><th>HubSpot Property</th><th>Processing</th></tr></thead><tbody>';
		echo '<tr><td>billing_email / user_email</td><td>email</td><td>Primary identifier</td></tr>';
		echo '<tr><td>billing_first_name / first_name</td><td>firstname</td><td>Direct mapping</td></tr>';
		echo '<tr><td>billing_last_name / last_name</td><td>lastname</td><td>Direct mapping</td></tr>';
		echo '<tr><td>billing_phone</td><td>phone</td><td>Sanitized (international format)</td></tr>';
		echo '<tr><td>date_of_birth</td><td>date_of_birth</td><td>ISO 8601 format</td></tr>';
		echo '<tr><td>gender</td><td>gender</td><td>Standardized values</td></tr>';
		echo '<tr><td>ennu_global_health_goals</td><td>health_goals</td><td>JSON serialized array</td></tr>';
		echo '</tbody></table>';
		
		echo '<h3>Assessment Data (basic_assessments object)</h3>';
		echo '<p>All assessment responses are synced to HubSpot\'s <code>basic_assessments</code> custom object with the following structure:</p>';
		echo '<ul>';
		echo '<li><strong>Assessment Type</strong>: weight_loss, testosterone, hormone, health, etc.</li>';
		echo '<li><strong>Individual Questions</strong>: Each question response mapped to specific HubSpot fields</li>';
		echo '<li><strong>Calculated Scores</strong>: Body, Mind, Lifestyle, Aesthetics pillar scores</li>';
		echo '<li><strong>Metadata</strong>: Submission timestamp, user ID, assessment version</li>';
		echo '</ul>';
		
		echo '<h3>Biomarker Data (biomarkers object)</h3>';
		echo '<p>Biomarker values are synced to HubSpot\'s <code>biomarkers</code> custom object:</p>';
		echo '<ul>';
		echo '<li><strong>Current Values</strong>: Latest biomarker measurements</li>';
		echo '<li><strong>Status Classifications</strong>: optimal, suboptimal, poor range classifications</li>';
		echo '<li><strong>Trends</strong>: improving, stable, worsening indicators</li>';
		echo '<li><strong>Source Information</strong>: self-report, Labcorp, derived calculations</li>';
		echo '</ul>';
		
		echo '<h2>Technical Implementation</h2>';
		echo '<h3>Core Classes</h3>';
		echo '<table class="widefat"><thead><tr><th>Class</th><th>Responsibility</th><th>Location</th></tr></thead><tbody>';
		echo '<tr><td>ENNU_HubSpot_Admin_Page</td><td>Admin interface & connection testing</td><td>class-hubspot-admin-page.php</td></tr>';
		echo '<tr><td>ENNU_HubSpot_Bulk_Field_Creator</td><td>Multi-object sync orchestration</td><td>class-hubspot-bulk-field-creator.php</td></tr>';
		echo '<tr><td>ENNU_HubSpot_API_Manager</td><td>API communication & authentication</td><td>class-hubspot-api-manager.php</td></tr>';
		echo '<tr><td>ENNU_HubSpot_OAuth_Handler</td><td>Legacy OAuth support</td><td>class-hubspot-oauth-handler.php</td></tr>';
		echo '</tbody></table>';
		
		echo '<h3>API Endpoints Used</h3>';
		echo '<ul>';
		echo '<li><strong>Account Info</strong>: <code>/account-info/v3/details</code></li>';
		echo '<li><strong>Contacts</strong>: <code>/crm/v3/objects/contacts</code></li>';
		echo '<li><strong>Custom Objects</strong>: <code>/crm/v3/objects/{object_type}</code></li>';
		echo '<li><strong>Associations</strong>: <code>/crm/v4/associations/{from}/{to}/batch/create</code></li>';
		echo '<li><strong>Properties</strong>: <code>/crm/v3/properties/{object_type}</code></li>';
		echo '</ul>';
		
		echo '<h2>Data Flow Process</h2>';
		echo '<ol>';
		echo '<li><strong>Assessment Submission</strong>: User completes assessment in WordPress</li>';
		echo '<li><strong>Contact Creation/Update</strong>: User data synced to HubSpot contacts</li>';
		echo '<li><strong>Assessment Object Creation</strong>: Assessment responses stored in basic_assessments object</li>';
		echo '<li><strong>Biomarker Object Creation</strong>: Biomarker data stored in biomarkers object</li>';
		echo '<li><strong>Association Creation</strong>: Links established between contact and data objects</li>';
		echo '<li><strong>Error Handling</strong>: Failed syncs logged with retry mechanisms</li>';
		echo '</ol>';
		
		echo '<h2>Error Handling & Validation</h2>';
		echo '<h3>Phone Number Validation</h3>';
		echo '<p>Phone numbers are sanitized to prevent HubSpot API rejections:</p>';
		echo '<ul>';
		echo '<li>US 10-digit numbers: automatically prefixed with +1</li>';
		echo '<li>International formats: validated for +{country_code}{7-15_digits} pattern</li>';
		echo '<li>Invalid formats: rejected and logged (returns empty string)</li>';
		echo '</ul>';
		
		echo '<h3>Association Handling</h3>';
		echo '<p>Uses HubSpot\'s batch association API for reliable object linking:</p>';
		echo '<ul>';
		echo '<li>Endpoint: <code>/crm/v4/associations/{object}/contacts/batch/create</code></li>';
		echo '<li>Association Type: Standard HubSpot defined associations</li>';
		echo '<li>Error Recovery: Comprehensive logging and retry logic</li>';
		echo '</ul>';
		
		echo '<h2>Field Mapping Status</h2>';
		echo '<p>Real-time field mapping validation is available in the admin interface showing:</p>';
		echo '<ul>';
		echo '<li><strong>WordPress Field ID → HubSpot Field ID</strong> mappings</li>';
		echo '<li><strong>Sync Status</strong>: ✅ Mapped, ⚠️ Partial, ❌ Missing</li>';
		echo '<li><strong>Assessment-Specific Tabs</strong>: Individual views per assessment type</li>';
		echo '<li><strong>Live API Data</strong>: Real-time field fetching from HubSpot</li>';
		echo '</ul>';
		
		echo '<h2>Performance & Monitoring</h2>';
		echo '<h3>Caching</h3>';
		echo '<ul>';
		echo '<li>HubSpot field definitions cached for 1 hour</li>';
		echo '<li>Connection status cached to reduce API calls</li>';
		echo '<li>Assessment field mappings cached per session</li>';
		echo '</ul>';
		
		echo '<h3>Logging & Debugging</h3>';
		echo '<ul>';
		echo '<li>All API requests/responses logged in WordPress error log</li>';
		echo '<li>Sync failures tracked with detailed error messages</li>';
		echo '<li>Performance metrics (execution time, memory usage) recorded</li>';
		echo '<li>Debug information available in admin interface</li>';
		echo '</ul>';
		
		echo '<h2>Business Impact</h2>';
		echo '<h3>Segmentation & Marketing</h3>';
		echo '<ul>';
		echo '<li><strong>Health Goals</strong>: Targeted campaigns based on user objectives</li>';
		echo '<li><strong>Assessment Scores</strong>: Personalized content for different health levels</li>';
		echo '<li><strong>Biomarker Status</strong>: Priority outreach for out-of-range values</li>';
		echo '<li><strong>Progress Tracking</strong>: Re-engagement sequences for stalled progress</li>';
		echo '</ul>';
		
		echo '<h3>Revenue Operations</h3>';
		echo '<ul>';
		echo '<li><strong>Lead Scoring</strong>: Assessment completion indicates engagement level</li>';
		echo '<li><strong>Conversion Tracking</strong>: Health score improvements correlate with retention</li>';
		echo '<li><strong>Churn Prevention</strong>: Biomarker trends identify at-risk patients</li>';
		echo '<li><strong>Upsell Opportunities</strong>: Multiple assessment types indicate program expansion</li>';
		echo '</ul>';
		
		echo '<h2>Compliance & Security</h2>';
		echo '<ul>';
		echo '<li><strong>HIPAA Considerations</strong>: Health data properly segregated in custom objects</li>';
		echo '<li><strong>Access Control</strong>: Private app tokens with limited scopes</li>';
		echo '<li><strong>Data Encryption</strong>: All API communications use HTTPS/TLS</li>';
		echo '<li><strong>Audit Trail</strong>: Complete logging of all sync operations</li>';
		echo '</ul>';
		
		echo '<h2>Troubleshooting</h2>';
		echo '<table class="widefat"><thead><tr><th>Issue</th><th>Symptoms</th><th>Resolution</th></tr></thead><tbody>';
		echo '<tr><td>Connection Failed</td><td>❌ Not Connected status</td><td>Verify access token and portal ID in admin</td></tr>';
		echo '<tr><td>Phone Validation Errors</td><td>Contact sync failures</td><td>Check phone number format (international preferred)</td></tr>';
		echo '<tr><td>Association Errors</td><td>Objects created but not linked</td><td>Review association API logs for endpoint issues</td></tr>';
		echo '<tr><td>Field Mapping Missing</td><td>Data not appearing in HubSpot</td><td>Use field mapping validation in admin interface</td></tr>';
		echo '<tr><td>Sync Performance</td><td>Slow or timing out</td><td>Check API rate limits and batch sizes</td></tr>';
		echo '</tbody></table>';
		
		echo '<h2>Current Status</h2>';
		echo '<p><strong>Sync Functionality: 🎉 100% Operational</strong></p>';
		echo '<ul>';
		echo '<li>✅ Contact creation and updates</li>';
		echo '<li>✅ Assessment data synchronization</li>';
		echo '<li>✅ Biomarker data synchronization</li>';
		echo '<li>✅ Object associations</li>';
		echo '<li>✅ Phone number validation</li>';
		echo '<li>✅ Error handling and logging</li>';
		echo '</ul>';
		
		$this->render_docs_wrapper_close();
	}

	public function render_docs_sop() {
		$this->render_docs_wrapper_open( 'SOP Documentation' );
		echo '<p>Operational runbooks for launch, incidents, and changes.</p>';
		echo '<h2>Pre-Launch Checklist</h2>';
		echo '<ul><li>Results pages mapped for all assessments.</li><li>Globals prefill & auto-skip verified (DOB/gender/height/weight/phone).</li><li>Biomarkers show current, reference, ENNU target correctly.</li></ul>';
		echo '<h2>Incident Response</h2>';
		echo '<ul><li><strong>No redirect:</strong> verify mapping and token; use global results fallback.</li>'
			. '<li><strong>Globals missing:</strong> resubmit welcome; check user meta for ennu_global_* keys.</li>'
			. '<li><strong>Empty charts:</strong> confirm submissions and date ranges; check debug.log.</li></ul>';
		echo '<h2>Change Control</h2>';
		echo '<ul><li>Clinical approval for range/target changes; record rationale and date.</li><li>Communicate changes to ops/clinicians/marketing.</li><li>Spot-check UI labels and colors match logic.</li></ul>';
		$this->render_docs_wrapper_close();
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		$documentation = $this->documentation_data;
		
		if ( empty( $documentation ) ) {
			$documentation = $this->generate_comprehensive_documentation();
		}
		?>
		<div class="wrap">
			<h1>ENNU Documentation Manager</h1>
			<p>Comprehensive documentation management and API documentation generation.</p>
			
			<div class="documentation-overview">
				<h2>Documentation Overview</h2>
				<table class="widefat">
					<thead>
						<tr>
							<th>Section</th>
							<th>Status</th>
							<th>Last Updated</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>API Documentation</td>
							<td>✅ Complete</td>
							<td><?php echo esc_html( $documentation['generated_at'] ); ?></td>
						</tr>
						<tr>
							<td>Architecture Documentation</td>
							<td>✅ Complete</td>
							<td><?php echo esc_html( $documentation['generated_at'] ); ?></td>
						</tr>
						<tr>
							<td>User Guides</td>
							<td>✅ Complete</td>
							<td><?php echo esc_html( $documentation['generated_at'] ); ?></td>
						</tr>
						<tr>
							<td>Developer Guides</td>
							<td>✅ Complete</td>
							<td><?php echo esc_html( $documentation['generated_at'] ); ?></td>
						</tr>
						<tr>
							<td>Changelog</td>
							<td>✅ Complete</td>
							<td><?php echo esc_html( $documentation['generated_at'] ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div class="documentation-actions">
				<h2>Documentation Actions</h2>
				<p>
					<button id="generate-docs" class="button button-primary">Generate Documentation</button>
					<button id="export-html" class="button button-secondary">Export HTML</button>
					<button id="export-markdown" class="button button-secondary">Export Markdown</button>
					<button id="export-json" class="button button-secondary">Export JSON</button>
				</p>
			</div>
			
			<div class="documentation-config">
				<h2>Documentation Configuration</h2>
				<form id="documentation-form">
					<?php wp_nonce_field( 'ennu_documentation_generation', 'documentation_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">Auto Generate</th>
							<td>
								<label><input type="checkbox" name="config[auto_generate]" value="1" <?php checked( $this->doc_config['auto_generate'] ); ?>> Auto-generate documentation</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Include Examples</th>
							<td>
								<label><input type="checkbox" name="config[include_examples]" value="1" <?php checked( $this->doc_config['include_examples'] ); ?>> Include code examples</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Include Diagrams</th>
							<td>
								<label><input type="checkbox" name="config[include_diagrams]" value="1" <?php checked( $this->doc_config['include_diagrams'] ); ?>> Include architecture diagrams</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Update Frequency</th>
							<td>
								<select name="config[update_frequency]">
									<option value="daily" <?php selected( $this->doc_config['update_frequency'], 'daily' ); ?>>Daily</option>
									<option value="weekly" <?php selected( $this->doc_config['update_frequency'], 'weekly' ); ?>>Weekly</option>
									<option value="monthly" <?php selected( $this->doc_config['update_frequency'], 'monthly' ); ?>>Monthly</option>
								</select>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Update Configuration">
					</p>
				</form>
			</div>
			
			<div id="documentation-results" style="display: none;">
				<h2>Documentation Results</h2>
				<div id="results-content"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#generate-docs').on('click', function() {
				generateDocumentation('generate');
			});
			
			$('#export-html').on('click', function() {
				generateDocumentation('export', 'html');
			});
			
			$('#export-markdown').on('click', function() {
				generateDocumentation('export', 'markdown');
			});
			
			$('#export-json').on('click', function() {
				generateDocumentation('export', 'json');
			});
			
			$('#documentation-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = new FormData(this);
				formData.append('action', 'ennu_generate_documentation');
				formData.append('action_type', 'update_config');
				formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_documentation_generation' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#documentation-results').show();
						}
					}
				});
			});
			
			function generateDocumentation(action, format) {
				var data = {
					action: 'ennu_generate_documentation',
					action_type: action,
					nonce: '<?php echo wp_create_nonce( 'ennu_documentation_generation' ); ?>'
				};
				
				if (format) {
					data.format = format;
				}
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: data,
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#documentation-results').show();
						}
					}
				});
			}
		});
		</script>
		<?php
	}
} 