<?php
/**
 * ENNU Code Quality Manager
 *
 * PSR-12 standards implementation and code quality management
 *
 * @package ENNU_Life_Assessments
 * @since 64.18.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Code Quality Manager Class
 *
 * @since 64.18.0
 */
class ENNU_Code_Quality_Manager {
	
	/**
	 * Code quality configuration
	 *
	 * @var array
	 */
	private $quality_config = array(
		'psr12_enabled' => true,
		'documentation_required' => true,
		'code_coverage_minimum' => 80,
		'complexity_maximum' => 10,
		'line_length_maximum' => 120,
	);
	
	/**
	 * Quality metrics
	 *
	 * @var array
	 */
	private $quality_metrics = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_ennu_analyze_code_quality', array( $this, 'analyze_code_quality' ) );
		add_action( 'wp_ajax_nopriv_ennu_analyze_code_quality', array( $this, 'analyze_code_quality' ) );
	}
	
	/**
	 * Initialize code quality manager
	 */
	public function init() {
		// Load configuration
		$this->load_configuration();
		
		// Add code quality hooks
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Only evaluate code quality in the admin UI on the Code Quality page, never during AJAX/front-end
		add_action( 'admin_init', array( $this, 'maybe_run_code_quality_checks' ) );
		
		error_log( 'ENNU Code Quality Manager: Initialized successfully' );
	}

	/**
	 * Conditionally run code quality checks to avoid heavy processing on AJAX or front-end.
	 */
	public function maybe_run_code_quality_checks() {
		// Never run during AJAX to prevent excessive memory usage on admin-ajax.php
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Only run in wp-admin
		if ( ! is_admin() ) {
			return;
		}

		// Require admin capability
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only run automatically when viewing our Code Quality admin page
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'ennu-code-quality' !== $page ) {
			return;
		}

		$this->run_code_quality_checks();
	}
	
	/**
	 * Load configuration
	 */
	private function load_configuration() {
		$config = get_option( 'ennu_code_quality_config', array() );
		
		if ( ! empty( $config ) ) {
			$this->quality_config = wp_parse_args( $config, $this->quality_config );
		}
	}
	
	/**
	 * Run code quality checks
	 */
	public function run_code_quality_checks() {
		// Double-guard: if somehow called outside admin page or during AJAX, bail out
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$metrics = $this->analyze_codebase();
		$this->quality_metrics = $metrics;
		
		// Log summary only to avoid excessive debug log growth
		error_log( 'ENNU Code Quality Manager: Code quality analysis completed (files: ' . (int) $metrics['total_files'] . ', lines: ' . (int) $metrics['total_lines'] . ', psr12_violations: ' . (int) $metrics['psr12_violations'] . ')' );
	}
	
	/**
	 * Analyze codebase
	 *
	 * @return array Quality metrics
	 */
	public function analyze_codebase() {
		$metrics = array(
			'total_files' => 0,
			'total_lines' => 0,
			'classes' => 0,
			'methods' => 0,
			'functions' => 0,
			'psr12_violations' => 0,
			'documentation_coverage' => 0,
			'complexity_score' => 0,
			'code_quality_score' => 0,
			'issues' => array(),
		);
		
		$plugin_path = ENNU_LIFE_PLUGIN_PATH;
		$files = $this->get_php_files( $plugin_path );
		
		foreach ( $files as $file ) {
			$file_metrics = $this->analyze_file( $file );
			
			$metrics['total_files']++;
			$metrics['total_lines'] += $file_metrics['lines'];
			$metrics['classes'] += $file_metrics['classes'];
			$metrics['methods'] += $file_metrics['methods'];
			$metrics['functions'] += $file_metrics['functions'];
			$metrics['psr12_violations'] += $file_metrics['psr12_violations'];
			$metrics['complexity_score'] += $file_metrics['complexity'];
			
			// Add issues
			if ( ! empty( $file_metrics['issues'] ) ) {
				$metrics['issues'] = array_merge( $metrics['issues'], $file_metrics['issues'] );
			}
		}
		
		// Calculate averages
		if ( $metrics['total_files'] > 0 ) {
			$metrics['documentation_coverage'] = $this->calculate_documentation_coverage( $files );
			$metrics['complexity_score'] = $metrics['complexity_score'] / $metrics['total_files'];
			$metrics['code_quality_score'] = $this->calculate_quality_score( $metrics );
		}
		
		return $metrics;
	}
	
	/**
	 * Get PHP files
	 *
	 * @param string $directory Directory path
	 * @return array PHP files
	 */
	private function get_php_files( $directory ) {
		$files = array();
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $directory )
		);
		
		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$files[] = $file->getPathname();
			}
		}
		
		return $files;
	}
	
	/**
	 * Analyze file
	 *
	 * @param string $file File path
	 * @return array File metrics
	 */
	private function analyze_file( $file ) {
		$content = file_get_contents( $file );
		$lines = explode( "\n", $content );
		
		$metrics = array(
			'lines' => count( $lines ),
			'classes' => 0,
			'methods' => 0,
			'functions' => 0,
			'psr12_violations' => 0,
			'complexity' => 0,
			'issues' => array(),
		);
		
		// Count classes
		preg_match_all( '/class\s+\w+/', $content, $matches );
		$metrics['classes'] = count( $matches[0] );
		
		// Count methods
		preg_match_all( '/function\s+\w+/', $content, $matches );
		$metrics['methods'] = count( $matches[0] );
		
		// Count functions
		preg_match_all( '/function\s+\w+/', $content, $matches );
		$metrics['functions'] = count( $matches[0] );
		
		// Check PSR-12 violations
		$psr12_violations = $this->check_psr12_violations( $content );
		$metrics['psr12_violations'] = count( $psr12_violations );
		$metrics['issues'] = array_merge( $metrics['issues'], $psr12_violations );
		
		// Calculate complexity
		$metrics['complexity'] = $this->calculate_complexity( $content );
		
		return $metrics;
	}
	
	/**
	 * Check PSR-12 violations
	 *
	 * @param string $content File content
	 * @return array Violations
	 */
	private function check_psr12_violations( $content ) {
		$violations = array();
		
		// Check line length
		$lines = explode( "\n", $content );
		foreach ( $lines as $line_number => $line ) {
			if ( strlen( $line ) > $this->quality_config['line_length_maximum'] ) {
				$violations[] = array(
					'type' => 'psr12',
					'severity' => 'warning',
					'message' => "Line {$line_number}: Line exceeds maximum length of {$this->quality_config['line_length_maximum']} characters",
					'line' => $line_number + 1,
				);
			}
		}
		
		// Check indentation (tabs vs spaces)
		if ( preg_match( '/^[ ]+[^ ]/', $content, $matches ) ) {
			$violations[] = array(
				'type' => 'psr12',
				'severity' => 'error',
				'message' => 'File uses spaces for indentation instead of tabs',
				'line' => 0,
			);
		}
		
		// Check trailing whitespace
		if ( preg_match( '/[ \t]+$/', $content ) ) {
			$violations[] = array(
				'type' => 'psr12',
				'severity' => 'warning',
				'message' => 'File contains trailing whitespace',
				'line' => 0,
			);
		}
		
		// Check file ending
		if ( ! preg_match( '/\n$/', $content ) ) {
			$violations[] = array(
				'type' => 'psr12',
				'severity' => 'error',
				'message' => 'File does not end with a newline',
				'line' => 0,
			);
		}
		
		return $violations;
	}
	
	/**
	 * Calculate complexity
	 *
	 * @param string $content File content
	 * @return int Complexity score
	 */
	private function calculate_complexity( $content ) {
		$complexity = 1; // Base complexity
		
		// Count control structures
		$control_structures = array(
			'if', 'elseif', 'else',
			'for', 'foreach', 'while', 'do',
			'switch', 'case', 'default',
			'catch', 'finally',
			'&&', '||', 'and', 'or',
		);
		
		foreach ( $control_structures as $structure ) {
			$count = substr_count( $content, $structure );
			$complexity += $count;
		}
		
		return $complexity;
	}
	
	/**
	 * Calculate documentation coverage
	 *
	 * @param array $files Files to analyze
	 * @return float Documentation coverage percentage
	 */
	private function calculate_documentation_coverage( $files ) {
		$total_classes = 0;
		$documented_classes = 0;
		$total_methods = 0;
		$documented_methods = 0;
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			// Count classes
			preg_match_all( '/class\s+(\w+)/', $content, $class_matches );
			$total_classes += count( $class_matches[1] );
			
			// Count documented classes
			preg_match_all( '/\/\*\*.*?\*\/.*?class\s+(\w+)/s', $content, $doc_class_matches );
			$documented_classes += count( $doc_class_matches[1] );
			
			// Count methods
			preg_match_all( '/function\s+(\w+)/', $content, $method_matches );
			$total_methods += count( $method_matches[1] );
			
			// Count documented methods
			preg_match_all( '/\/\*\*.*?\*\/.*?function\s+(\w+)/s', $content, $doc_method_matches );
			$documented_methods += count( $doc_method_matches[1] );
		}
		
		$total_items = $total_classes + $total_methods;
		$documented_items = $documented_classes + $documented_methods;
		
		if ( $total_items === 0 ) {
			return 100.0;
		}
		
		return round( ( $documented_items / $total_items ) * 100, 2 );
	}
	
	/**
	 * Calculate quality score
	 *
	 * @param array $metrics Quality metrics
	 * @return float Quality score
	 */
	private function calculate_quality_score( $metrics ) {
		$score = 100;
		
		// Deduct for PSR-12 violations
		$score -= ( $metrics['psr12_violations'] * 2 );
		
		// Deduct for low documentation coverage
		if ( $metrics['documentation_coverage'] < 80 ) {
			$score -= ( 80 - $metrics['documentation_coverage'] );
		}
		
		// Deduct for high complexity
		if ( $metrics['complexity_score'] > $this->quality_config['complexity_maximum'] ) {
			$score -= ( $metrics['complexity_score'] - $this->quality_config['complexity_maximum'] ) * 5;
		}
		
		return max( 0, round( $score, 2 ) );
	}
	
	/**
	 * Generate code quality report
	 *
	 * @return array Quality report
	 */
	public function generate_quality_report() {
		$metrics = $this->quality_metrics;
		
		if ( empty( $metrics ) ) {
			$metrics = $this->analyze_codebase();
		}
		
		$report = array(
			'metrics' => $metrics,
			'recommendations' => $this->generate_recommendations( $metrics ),
			'compliance' => $this->check_compliance( $metrics ),
		);
		
		return $report;
	}
	
	/**
	 * Generate recommendations
	 *
	 * @param array $metrics Quality metrics
	 * @return array Recommendations
	 */
	private function generate_recommendations( $metrics ) {
		$recommendations = array();
		
		if ( $metrics['psr12_violations'] > 0 ) {
			$recommendations[] = 'Fix PSR-12 coding standard violations';
		}
		
		if ( $metrics['documentation_coverage'] < 80 ) {
			$recommendations[] = 'Improve code documentation coverage';
		}
		
		if ( $metrics['complexity_score'] > $this->quality_config['complexity_maximum'] ) {
			$recommendations[] = 'Reduce code complexity by refactoring complex methods';
		}
		
		if ( $metrics['code_quality_score'] < 80 ) {
			$recommendations[] = 'Overall code quality needs improvement';
		}
		
		return $recommendations;
	}
	
	/**
	 * Check compliance
	 *
	 * @param array $metrics Quality metrics
	 * @return array Compliance status
	 */
	private function check_compliance( $metrics ) {
		return array(
			'psr12_compliant' => $metrics['psr12_violations'] === 0,
			'documentation_compliant' => $metrics['documentation_coverage'] >= 80,
			'complexity_compliant' => $metrics['complexity_score'] <= $this->quality_config['complexity_maximum'],
			'overall_compliant' => $metrics['code_quality_score'] >= 80,
		);
	}
	
	/**
	 * AJAX handler for code quality analysis
	 */
	public function analyze_code_quality() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_code_quality_analysis' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
		
		switch ( $action ) {
			case 'analyze':
				$result = $this->generate_quality_report();
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
		$current_config = $this->quality_config;
		$updated_config = wp_parse_args( $config, $current_config );
		
		$result = update_option( 'ennu_code_quality_config', $updated_config );
		
		if ( $result ) {
			$this->quality_config = $updated_config;
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
			'Code Quality Manager',
			'Code Quality',
			'manage_options',
			'ennu-code-quality',
			array( $this, 'admin_page' )
		);
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		$report = $this->generate_quality_report();
		$metrics = $report['metrics'];
		$recommendations = $report['recommendations'];
		$compliance = $report['compliance'];
		?>
		<div class="wrap">
			<h1>ENNU Code Quality Manager</h1>
			<p>PSR-12 standards implementation and code quality management.</p>
			
			<div class="quality-metrics">
				<h2>Code Quality Metrics</h2>
				<table class="widefat">
					<thead>
						<tr>
							<th>Metric</th>
							<th>Value</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Total Files</td>
							<td><?php echo esc_html( $metrics['total_files'] ); ?></td>
							<td>-</td>
						</tr>
						<tr>
							<td>Total Lines</td>
							<td><?php echo esc_html( $metrics['total_lines'] ); ?></td>
							<td>-</td>
						</tr>
						<tr>
							<td>Classes</td>
							<td><?php echo esc_html( $metrics['classes'] ); ?></td>
							<td>-</td>
						</tr>
						<tr>
							<td>Methods</td>
							<td><?php echo esc_html( $metrics['methods'] ); ?></td>
							<td>-</td>
						</tr>
						<tr>
							<td>PSR-12 Violations</td>
							<td><?php echo esc_html( $metrics['psr12_violations'] ); ?></td>
							<td><?php echo $compliance['psr12_compliant'] ? '<span style="color: green;">✓ Compliant</span>' : '<span style="color: red;">✗ Non-compliant</span>'; ?></td>
						</tr>
						<tr>
							<td>Documentation Coverage</td>
							<td><?php echo esc_html( $metrics['documentation_coverage'] ); ?>%</td>
							<td><?php echo $compliance['documentation_compliant'] ? '<span style="color: green;">✓ Compliant</span>' : '<span style="color: red;">✗ Non-compliant</span>'; ?></td>
						</tr>
						<tr>
							<td>Complexity Score</td>
							<td><?php echo esc_html( round( $metrics['complexity_score'], 2 ) ); ?></td>
							<td><?php echo $compliance['complexity_compliant'] ? '<span style="color: green;">✓ Compliant</span>' : '<span style="color: red;">✗ Non-compliant</span>'; ?></td>
						</tr>
						<tr>
							<td>Code Quality Score</td>
							<td><?php echo esc_html( $metrics['code_quality_score'] ); ?>%</td>
							<td><?php echo $compliance['overall_compliant'] ? '<span style="color: green;">✓ Good</span>' : '<span style="color: orange;">⚠ Needs Improvement</span>'; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div class="quality-recommendations">
				<h2>Recommendations</h2>
				<?php if ( ! empty( $recommendations ) ) : ?>
					<ul>
						<?php foreach ( $recommendations as $recommendation ) : ?>
							<li><?php echo esc_html( $recommendation ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p>No recommendations at this time. Code quality is good!</p>
				<?php endif; ?>
			</div>
			
			<div class="quality-config">
				<h2>Quality Configuration</h2>
				<form id="code-quality-form">
					<?php wp_nonce_field( 'ennu_code_quality_analysis', 'code_quality_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">PSR-12 Enabled</th>
							<td>
								<label><input type="checkbox" name="config[psr12_enabled]" value="1" <?php checked( $this->quality_config['psr12_enabled'] ); ?>> Enable PSR-12 Standards</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Documentation Required</th>
							<td>
								<label><input type="checkbox" name="config[documentation_required]" value="1" <?php checked( $this->quality_config['documentation_required'] ); ?>> Require Documentation</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Code Coverage Minimum</th>
							<td>
								<input type="number" name="config[code_coverage_minimum]" value="<?php echo esc_attr( $this->quality_config['code_coverage_minimum'] ); ?>" min="0" max="100">%
							</td>
						</tr>
						<tr>
							<th scope="row">Complexity Maximum</th>
							<td>
								<input type="number" name="config[complexity_maximum]" value="<?php echo esc_attr( $this->quality_config['complexity_maximum'] ); ?>" min="1" max="20">
							</td>
						</tr>
						<tr>
							<th scope="row">Line Length Maximum</th>
							<td>
								<input type="number" name="config[line_length_maximum]" value="<?php echo esc_attr( $this->quality_config['line_length_maximum'] ); ?>" min="80" max="200"> characters
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Update Configuration">
						<button type="button" id="analyze-quality" class="button button-secondary">Analyze Code Quality</button>
					</p>
				</form>
			</div>
			
			<div id="quality-results" style="display: none;">
				<h2>Analysis Results</h2>
				<div id="results-content"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#code-quality-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = new FormData(this);
				formData.append('action', 'ennu_analyze_code_quality');
				formData.append('action_type', 'update_config');
				formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_code_quality_analysis' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#quality-results').show();
						}
					}
				});
			});
			
			$('#analyze-quality').on('click', function() {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_analyze_code_quality',
						action_type: 'analyze',
						nonce: '<?php echo wp_create_nonce( 'ennu_code_quality_analysis' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#quality-results').show();
						}
					}
				});
			});
		});
		</script>
		<?php
	}
} 