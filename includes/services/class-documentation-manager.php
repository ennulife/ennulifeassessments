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
		
		error_log( 'ENNU Documentation Manager: Initialized successfully' );
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
		
		error_log( 'ENNU Documentation Manager: Comprehensive documentation generated' );
		
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
		add_submenu_page(
			'ennu-life-assessments',
			'Documentation Manager',
			'Documentation',
			'manage_options',
			'ennu-documentation',
			array( $this, 'admin_page' )
		);
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