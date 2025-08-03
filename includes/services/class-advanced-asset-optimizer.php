<?php
/**
 * ENNU Advanced Asset Optimizer
 *
 * Advanced asset loading optimization and CDN integration
 *
 * @package ENNU_Life_Assessments
 * @since 64.17.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Advanced Asset Optimizer Class
 *
 * @since 64.17.0
 */
class ENNU_Advanced_Asset_Optimizer {
	
	/**
	 * CDN configuration
	 *
	 * @var array
	 */
	private $cdn_config = array(
		'enabled' => false,
		'url' => '',
		'assets' => array(),
	);
	
	/**
	 * Minification configuration
	 *
	 * @var array
	 */
	private $minification_config = array(
		'enabled' => false,
		'css' => true,
		'js' => true,
		'html' => false,
	);
	
	/**
	 * Compression configuration
	 *
	 * @var array
	 */
	private $compression_config = array(
		'enabled' => false,
		'gzip' => true,
		'brotli' => false,
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_ennu_optimize_assets', array( $this, 'optimize_assets' ) );
		add_action( 'wp_ajax_nopriv_ennu_optimize_assets', array( $this, 'optimize_assets' ) );
	}
	
	/**
	 * Initialize asset optimizer
	 */
	public function init() {
		// Load configuration
		$this->load_configuration();
		
		// Add asset optimization hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'optimize_enqueued_assets' ), 999 );
		add_action( 'admin_enqueue_scripts', array( $this, 'optimize_admin_assets' ), 999 );
		
		// Add output buffering for HTML minification
		if ( $this->minification_config['html'] ) {
			add_action( 'template_redirect', array( $this, 'start_output_buffering' ) );
		}
		
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		error_log( 'ENNU Advanced Asset Optimizer: Initialized successfully' );
	}
	
	/**
	 * Load configuration
	 */
	private function load_configuration() {
		$config = get_option( 'ennu_asset_optimizer_config', array() );
		
		if ( ! empty( $config ) ) {
			$this->cdn_config = wp_parse_args( $config['cdn'] ?? array(), $this->cdn_config );
			$this->minification_config = wp_parse_args( $config['minification'] ?? array(), $this->minification_config );
			$this->compression_config = wp_parse_args( $config['compression'] ?? array(), $this->compression_config );
		}
	}
	
	/**
	 * Optimize enqueued assets
	 */
	public function optimize_enqueued_assets() {
		global $wp_scripts, $wp_styles;
		
		if ( $this->minification_config['enabled'] ) {
			$this->minify_assets( $wp_scripts, $wp_styles );
		}
		
		if ( $this->cdn_config['enabled'] ) {
			$this->apply_cdn_urls( $wp_scripts, $wp_styles );
		}
		
		if ( $this->compression_config['enabled'] ) {
			$this->apply_compression_headers();
		}
	}
	
	/**
	 * Optimize admin assets
	 */
	public function optimize_admin_assets() {
		global $wp_scripts, $wp_styles;
		
		if ( $this->minification_config['enabled'] ) {
			$this->minify_assets( $wp_scripts, $wp_styles );
		}
		
		if ( $this->cdn_config['enabled'] ) {
			$this->apply_cdn_urls( $wp_scripts, $wp_styles );
		}
	}
	
	/**
	 * Minify assets
	 *
	 * @param WP_Scripts $scripts Scripts object
	 * @param WP_Styles $styles Styles object
	 */
	private function minify_assets( $scripts, $styles ) {
		if ( $this->minification_config['js'] ) {
			$this->minify_scripts( $scripts );
		}
		
		if ( $this->minification_config['css'] ) {
			$this->minify_styles( $styles );
		}
	}
	
	/**
	 * Minify scripts
	 *
	 * @param WP_Scripts $scripts Scripts object
	 */
	private function minify_scripts( $scripts ) {
		foreach ( $scripts->queue as $handle ) {
			$script = $scripts->registered[ $handle ];
			
			if ( $script->src && ! $script->extra['minified'] ) {
				$minified_src = $this->get_minified_asset_url( $script->src, 'js' );
				if ( $minified_src ) {
					$script->src = $minified_src;
					$script->extra['minified'] = true;
				}
			}
		}
	}
	
	/**
	 * Minify styles
	 *
	 * @param WP_Styles $styles Styles object
	 */
	private function minify_styles( $styles ) {
		foreach ( $styles->queue as $handle ) {
			$style = $styles->registered[ $handle ];
			
			if ( $style->src && ! $style->extra['minified'] ) {
				$minified_src = $this->get_minified_asset_url( $style->src, 'css' );
				if ( $minified_src ) {
					$style->src = $minified_src;
					$style->extra['minified'] = true;
				}
			}
		}
	}
	
	/**
	 * Get minified asset URL
	 *
	 * @param string $src Original asset URL
	 * @param string $type Asset type (js/css)
	 * @return string|false Minified asset URL or false
	 */
	private function get_minified_asset_url( $src, $type ) {
		// Check if asset is already minified
		if ( strpos( $src, '.min.' ) !== false ) {
			return false;
		}
		
		// Generate minified URL
		$minified_src = str_replace( ".{$type}", ".min.{$type}", $src );
		
		// Check if minified file exists
		$file_path = $this->get_asset_file_path( $minified_src );
		if ( file_exists( $file_path ) ) {
			return $minified_src;
		}
		
		// Try to create minified version
		$original_file_path = $this->get_asset_file_path( $src );
		if ( file_exists( $original_file_path ) ) {
			$minified_content = $this->minify_asset_content( file_get_contents( $original_file_path ), $type );
			if ( $minified_content && file_put_contents( $file_path, $minified_content ) ) {
				return $minified_src;
			}
		}
		
		return false;
	}
	
	/**
	 * Get asset file path
	 *
	 * @param string $src Asset URL
	 * @return string File path
	 */
	private function get_asset_file_path( $src ) {
		$parsed_url = parse_url( $src );
		$file_path = ABSPATH . ltrim( $parsed_url['path'], '/' );
		
		return $file_path;
	}
	
	/**
	 * Minify asset content
	 *
	 * @param string $content Asset content
	 * @param string $type Asset type (js/css)
	 * @return string|false Minified content or false
	 */
	private function minify_asset_content( $content, $type ) {
		if ( $type === 'js' ) {
			return $this->minify_javascript( $content );
		} elseif ( $type === 'css' ) {
			return $this->minify_css( $content );
		}
		
		return false;
	}
	
	/**
	 * Minify JavaScript
	 *
	 * @param string $content JavaScript content
	 * @return string Minified JavaScript
	 */
	private function minify_javascript( $content ) {
		// Remove comments
		$content = preg_replace( '/\/\*.*?\*\//s', '', $content );
		$content = preg_replace( '/\/\/.*$/m', '', $content );
		
		// Remove unnecessary whitespace
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = preg_replace( '/;\s*}/', '}', $content );
		$content = preg_replace( '/{\s*}/', '{}', $content );
		
		return trim( $content );
	}
	
	/**
	 * Minify CSS
	 *
	 * @param string $content CSS content
	 * @return string Minified CSS
	 */
	private function minify_css( $content ) {
		// Remove comments
		$content = preg_replace( '/\/\*.*?\*\//s', '', $content );
		
		// Remove unnecessary whitespace
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = preg_replace( '/;\s*}/', '}', $content );
		$content = preg_replace( '/{\s*}/', '{}', $content );
		
		return trim( $content );
	}
	
	/**
	 * Apply CDN URLs
	 *
	 * @param WP_Scripts $scripts Scripts object
	 * @param WP_Styles $styles Styles object
	 */
	private function apply_cdn_urls( $scripts, $styles ) {
		if ( empty( $this->cdn_config['url'] ) ) {
			return;
		}
		
		$cdn_url = rtrim( $this->cdn_config['url'], '/' );
		$site_url = get_site_url();
		
		// Apply CDN to scripts
		foreach ( $scripts->queue as $handle ) {
			$script = $scripts->registered[ $handle ];
			if ( $script->src && strpos( $script->src, $site_url ) === 0 ) {
				$script->src = str_replace( $site_url, $cdn_url, $script->src );
			}
		}
		
		// Apply CDN to styles
		foreach ( $styles->queue as $handle ) {
			$style = $styles->registered[ $handle ];
			if ( $style->src && strpos( $style->src, $site_url ) === 0 ) {
				$style->src = str_replace( $site_url, $cdn_url, $style->src );
			}
		}
	}
	
	/**
	 * Apply compression headers
	 */
	private function apply_compression_headers() {
		if ( $this->compression_config['gzip'] && ! headers_sent() ) {
			// Enable gzip compression
			if ( extension_loaded( 'zlib' ) ) {
				ini_set( 'zlib.output_compression', 1 );
			}
		}
		
		if ( $this->compression_config['brotli'] && ! headers_sent() ) {
			// Enable brotli compression
			if ( extension_loaded( 'brotli' ) ) {
				ini_set( 'brotli.output_compression', 1 );
			}
		}
	}
	
	/**
	 * Start output buffering for HTML minification
	 */
	public function start_output_buffering() {
		ob_start( array( $this, 'minify_html_output' ) );
	}
	
	/**
	 * Minify HTML output
	 *
	 * @param string $buffer HTML buffer
	 * @return string Minified HTML
	 */
	public function minify_html_output( $buffer ) {
		// Remove HTML comments (except IE conditional comments)
		$buffer = preg_replace( '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $buffer );
		
		// Remove unnecessary whitespace
		$buffer = preg_replace( '/\s+/', ' ', $buffer );
		$buffer = preg_replace( '/>\s+</', '><', $buffer );
		
		return trim( $buffer );
	}
	
	/**
	 * Get asset performance metrics
	 *
	 * @return array Performance metrics
	 */
	public function get_asset_performance_metrics() {
		global $wp_scripts, $wp_styles;
		
		$metrics = array(
			'total_scripts' => count( $wp_scripts->queue ),
			'total_styles' => count( $wp_styles->queue ),
			'minified_scripts' => 0,
			'minified_styles' => 0,
			'cdn_scripts' => 0,
			'cdn_styles' => 0,
			'total_size' => 0,
		);
		
		// Count minified scripts
		foreach ( $wp_scripts->queue as $handle ) {
			$script = $wp_scripts->registered[ $handle ];
			if ( isset( $script->extra['minified'] ) && $script->extra['minified'] ) {
				$metrics['minified_scripts']++;
			}
			if ( $this->cdn_config['enabled'] && strpos( $script->src, $this->cdn_config['url'] ) === 0 ) {
				$metrics['cdn_scripts']++;
			}
		}
		
		// Count minified styles
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[ $handle ];
			if ( isset( $style->extra['minified'] ) && $style->extra['minified'] ) {
				$metrics['minified_styles']++;
			}
			if ( $this->cdn_config['enabled'] && strpos( $style->src, $this->cdn_config['url'] ) === 0 ) {
				$metrics['cdn_styles']++;
			}
		}
		
		return $metrics;
	}
	
	/**
	 * AJAX handler for asset optimization
	 */
	public function optimize_assets() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_asset_optimization' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
		
		switch ( $action ) {
			case 'get_metrics':
				$result = $this->get_asset_performance_metrics();
				break;
				
			case 'update_config':
				$config = isset( $_POST['config'] ) ? $_POST['config'] : array();
				$result = $this->update_configuration( $config );
				break;
				
			case 'test_cdn':
				$result = $this->test_cdn_connection();
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
		$current_config = array(
			'cdn' => $this->cdn_config,
			'minification' => $this->minification_config,
			'compression' => $this->compression_config,
		);
		
		$updated_config = wp_parse_args( $config, $current_config );
		
		$result = update_option( 'ennu_asset_optimizer_config', $updated_config );
		
		if ( $result ) {
			$this->cdn_config = $updated_config['cdn'];
			$this->minification_config = $updated_config['minification'];
			$this->compression_config = $updated_config['compression'];
		}
		
		return array(
			'success' => $result,
			'config' => $updated_config,
		);
	}
	
	/**
	 * Test CDN connection
	 *
	 * @return array Test result
	 */
	private function test_cdn_connection() {
		if ( empty( $this->cdn_config['url'] ) ) {
			return array( 'error' => 'CDN URL not configured' );
		}
		
		$test_url = $this->cdn_config['url'] . '/wp-content/plugins/ennulifeassessments/assets/css/user-dashboard.css';
		$response = wp_remote_get( $test_url );
		
		if ( is_wp_error( $response ) ) {
			return array( 'error' => 'CDN connection failed: ' . $response->get_error_message() );
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		
		if ( $status_code === 200 ) {
			return array( 'success' => true, 'message' => 'CDN connection successful' );
		} else {
			return array( 'error' => "CDN connection failed with status code: {$status_code}" );
		}
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life-assessments',
			'Asset Optimizer',
			'Asset Optimizer',
			'manage_options',
			'ennu-asset-optimizer',
			array( $this, 'admin_page' )
		);
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		$metrics = $this->get_asset_performance_metrics();
		?>
		<div class="wrap">
			<h1>ENNU Advanced Asset Optimizer</h1>
			<p>Advanced asset loading optimization and CDN integration.</p>
			
			<div class="asset-metrics">
				<h2>Asset Performance Metrics</h2>
				<table class="widefat">
					<thead>
						<tr>
							<th>Metric</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Total Scripts</td>
							<td><?php echo esc_html( $metrics['total_scripts'] ); ?></td>
						</tr>
						<tr>
							<td>Total Styles</td>
							<td><?php echo esc_html( $metrics['total_styles'] ); ?></td>
						</tr>
						<tr>
							<td>Minified Scripts</td>
							<td><?php echo esc_html( $metrics['minified_scripts'] ); ?></td>
						</tr>
						<tr>
							<td>Minified Styles</td>
							<td><?php echo esc_html( $metrics['minified_styles'] ); ?></td>
						</tr>
						<tr>
							<td>CDN Scripts</td>
							<td><?php echo esc_html( $metrics['cdn_scripts'] ); ?></td>
						</tr>
						<tr>
							<td>CDN Styles</td>
							<td><?php echo esc_html( $metrics['cdn_styles'] ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div class="optimization-config">
				<h2>Optimization Configuration</h2>
				<form id="asset-optimizer-form">
					<?php wp_nonce_field( 'ennu_asset_optimization', 'asset_optimizer_nonce' ); ?>
					
					<h3>CDN Configuration</h3>
					<table class="form-table">
						<tr>
							<th scope="row">Enable CDN</th>
							<td>
								<label><input type="checkbox" name="config[cdn][enabled]" value="1" <?php checked( $this->cdn_config['enabled'] ); ?>> Enable CDN</label>
							</td>
						</tr>
						<tr>
							<th scope="row">CDN URL</th>
							<td>
								<input type="url" name="config[cdn][url]" value="<?php echo esc_attr( $this->cdn_config['url'] ); ?>" class="regular-text" placeholder="https://cdn.example.com">
							</td>
						</tr>
					</table>
					
					<h3>Minification Configuration</h3>
					<table class="form-table">
						<tr>
							<th scope="row">Enable Minification</th>
							<td>
								<label><input type="checkbox" name="config[minification][enabled]" value="1" <?php checked( $this->minification_config['enabled'] ); ?>> Enable Minification</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Minify CSS</th>
							<td>
								<label><input type="checkbox" name="config[minification][css]" value="1" <?php checked( $this->minification_config['css'] ); ?>> Minify CSS</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Minify JavaScript</th>
							<td>
								<label><input type="checkbox" name="config[minification][js]" value="1" <?php checked( $this->minification_config['js'] ); ?>> Minify JavaScript</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Minify HTML</th>
							<td>
								<label><input type="checkbox" name="config[minification][html]" value="1" <?php checked( $this->minification_config['html'] ); ?>> Minify HTML</label>
							</td>
						</tr>
					</table>
					
					<h3>Compression Configuration</h3>
					<table class="form-table">
						<tr>
							<th scope="row">Enable Compression</th>
							<td>
								<label><input type="checkbox" name="config[compression][enabled]" value="1" <?php checked( $this->compression_config['enabled'] ); ?>> Enable Compression</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Gzip Compression</th>
							<td>
								<label><input type="checkbox" name="config[compression][gzip]" value="1" <?php checked( $this->compression_config['gzip'] ); ?>> Enable Gzip</label>
							</td>
						</tr>
						<tr>
							<th scope="row">Brotli Compression</th>
							<td>
								<label><input type="checkbox" name="config[compression][brotli]" value="1" <?php checked( $this->compression_config['brotli'] ); ?>> Enable Brotli</label>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Update Configuration">
						<button type="button" id="test-cdn" class="button button-secondary">Test CDN Connection</button>
					</p>
				</form>
			</div>
			
			<div id="optimization-results" style="display: none;">
				<h2>Optimization Results</h2>
				<div id="results-content"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#asset-optimizer-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = new FormData(this);
				formData.append('action', 'ennu_optimize_assets');
				formData.append('action_type', 'update_config');
				formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_asset_optimization' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#optimization-results').show();
						}
					}
				});
			});
			
			$('#test-cdn').on('click', function() {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_optimize_assets',
						action_type: 'test_cdn',
						nonce: '<?php echo wp_create_nonce( 'ennu_asset_optimization' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#optimization-results').show();
						}
					}
				});
			});
		});
		</script>
		<?php
	}
} 