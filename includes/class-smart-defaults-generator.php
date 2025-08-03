<?php
/**
 * ENNU Smart Defaults Generator
 *
 * Generates intelligent defaults for New Life Score calculation
 *
 * @package ENNU_Life_Assessments
 * @since 64.16.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Smart Defaults Generator Class
 *
 * @since 64.16.0
 */
class ENNU_Smart_Defaults_Generator {
	
	/**
	 * Health goals mapping
	 *
	 * @var array
	 */
	private $health_goals = array(
		'weight_loss' => array(
			'biomarkers' => array(
				'insulin' => array( 'target' => 5, 'range' => '3-8' ),
				'glucose' => array( 'target' => 85, 'range' => '70-100' ),
				'hba1c' => array( 'target' => 5.2, 'range' => '4.8-5.6' ),
				'leptin' => array( 'target' => 8, 'range' => '2-15' ),
			),
			'score_improvement' => 15,
		),
		'energy_optimization' => array(
			'biomarkers' => array(
				'vitamin_d' => array( 'target' => 50, 'range' => '30-80' ),
				'vitamin_b12' => array( 'target' => 600, 'range' => '200-900' ),
				'ferritin' => array( 'target' => 100, 'range' => '30-400' ),
				'thyroid_tsh' => array( 'target' => 2.5, 'range' => '0.4-4.0' ),
			),
			'score_improvement' => 12,
		),
		'mental_clarity' => array(
			'biomarkers' => array(
				'vitamin_d' => array( 'target' => 50, 'range' => '30-80' ),
				'omega_3' => array( 'target' => 8, 'range' => '4-12' ),
				'magnesium' => array( 'target' => 2.2, 'range' => '1.7-2.2' ),
				'zinc' => array( 'target' => 100, 'range' => '70-120' ),
			),
			'score_improvement' => 10,
		),
		'anti_aging' => array(
			'biomarkers' => array(
				'testosterone_total' => array( 'target' => 700, 'range' => '300-1000' ),
				'dhea' => array( 'target' => 200, 'range' => '100-400' ),
				'vitamin_d' => array( 'target' => 50, 'range' => '30-80' ),
				'homocysteine' => array( 'target' => 8, 'range' => '5-15' ),
			),
			'score_improvement' => 18,
		),
		'cardiovascular_health' => array(
			'biomarkers' => array(
				'cholesterol_total' => array( 'target' => 180, 'range' => '125-200' ),
				'cholesterol_hdl' => array( 'target' => 60, 'range' => '40-60' ),
				'cholesterol_ldl' => array( 'target' => 100, 'range' => '0-100' ),
				'triglycerides' => array( 'target' => 100, 'range' => '0-150' ),
			),
			'score_improvement' => 14,
		),
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_ennu_generate_smart_defaults', array( $this, 'ajax_generate_smart_defaults' ) );
		add_action( 'wp_ajax_nopriv_ennu_generate_smart_defaults', array( $this, 'ajax_generate_smart_defaults' ) );
	}
	
	/**
	 * Generate smart defaults for user
	 *
	 * @param int $user_id User ID
	 * @param array $health_goals Health goals
	 * @param float $current_score Current New Life Score
	 * @return array Smart defaults
	 */
	public function generate_smart_defaults( $user_id = null, $health_goals = array(), $current_score = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		if ( empty( $health_goals ) ) {
			$health_goals = $this->get_user_health_goals( $user_id );
		}
		
		if ( $current_score === 0 ) {
			$current_score = $this->get_current_new_life_score( $user_id );
		}
		
		// Check if user has real biomarker data
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$has_real_data = ! empty( $biomarker_data ) && is_array( $biomarker_data );
		
		if ( ! $has_real_data ) {
			// Return honest missing data notice instead of fake defaults
			$smart_defaults = array(
				'biomarker_targets' => array(),
				'projected_score' => null,
				'improvement_potential' => null,
				'recommendations' => array(),
				'data_status' => 'missing',
				'missing_data_notice' => array(
					'title' => 'Biomarker Data Required',
					'message' => 'Import your lab results to receive personalized biomarker targets and recommendations.',
					'action' => 'Import Lab Results',
					'icon' => '🔬',
					'priority' => 'high'
				)
			);
			
			// Save honest status to user meta
			update_user_meta( $user_id, 'ennu_smart_defaults', $smart_defaults );
			
			error_log( "ENNU Smart Defaults Generator: No real data available for user {$user_id} - returning missing data notice" );
			
			return $smart_defaults;
		}
		
		// Only generate targets from real data
		$smart_defaults = array(
			'biomarker_targets' => array(),
			'projected_score' => $current_score,
			'improvement_potential' => 0,
			'recommendations' => array(),
			'data_status' => 'available'
		);
		
		// Generate biomarker targets based on real data and health goals
		foreach ( $health_goals as $goal ) {
			if ( isset( $this->health_goals[ $goal ] ) ) {
				$goal_data = $this->health_goals[ $goal ];
				
				// Only add targets for biomarkers that user has real data for
				foreach ( $goal_data['biomarkers'] as $biomarker => $target_data ) {
					if ( isset( $biomarker_data[ $biomarker ] ) ) {
						$smart_defaults['biomarker_targets'][ $biomarker ] = array(
							'target' => $target_data['target'],
							'range' => $target_data['range'],
							'goal' => $goal,
							'priority' => $this->calculate_priority( $biomarker, $user_id ),
							'current_value' => $biomarker_data[ $biomarker ],
							'data_source' => 'user_lab_results'
						);
					}
				}
				
				// Only calculate improvement if we have real data to base it on
				if ( ! empty( $smart_defaults['biomarker_targets'] ) ) {
					$smart_defaults['projected_score'] += $goal_data['score_improvement'];
					$smart_defaults['improvement_potential'] += $goal_data['score_improvement'];
				}
			}
		}
		
		// Generate recommendations only if we have real data
		if ( ! empty( $smart_defaults['biomarker_targets'] ) ) {
			$smart_defaults['recommendations'] = $this->generate_recommendations( $health_goals, $current_score );
		} else {
			$smart_defaults['recommendations'] = array(
				array(
					'type' => 'data_required',
					'title' => 'Import Lab Results',
					'description' => 'Import your lab results to receive personalized recommendations based on your actual biomarker levels.',
					'priority' => 'high'
				)
			);
		}
		
		// Save smart defaults to user meta
		update_user_meta( $user_id, 'ennu_smart_defaults', $smart_defaults );
		
		error_log( "ENNU Smart Defaults Generator: Generated smart defaults for user {$user_id} based on real data" );
		
		return $smart_defaults;
	}
	
	/**
	 * Get user health goals
	 *
	 * @param int $user_id User ID
	 * @return array Health goals
	 */
	private function get_user_health_goals( $user_id ) {
		$goals = get_user_meta( $user_id, 'ennu_health_goals', true );
		
		if ( empty( $goals ) ) {
			// Default goals based on current health status
			$current_score = $this->get_current_new_life_score( $user_id );
			
			if ( $current_score < 50 ) {
				$goals = array( 'energy_optimization', 'mental_clarity' );
			} elseif ( $current_score < 70 ) {
				$goals = array( 'weight_loss', 'cardiovascular_health' );
			} else {
				$goals = array( 'anti_aging', 'cardiovascular_health' );
			}
		}
		
		return $goals;
	}
	
	/**
	 * Get current New Life Score
	 *
	 * @param int $user_id User ID
	 * @return float Current score
	 */
	private function get_current_new_life_score( $user_id ) {
		$score = get_user_meta( $user_id, 'ennu_new_life_score', true );
		return floatval( $score ) ?: 0;
	}
	
	/**
	 * Calculate biomarker priority
	 *
	 * @param string $biomarker Biomarker name
	 * @param int $user_id User ID
	 * @return int Priority (1-5, 5 being highest)
	 */
	private function calculate_priority( $biomarker, $user_id ) {
		$current_value = get_user_meta( $user_id, "ennu_biomarker_{$biomarker}", true );
		$target_value = $this->get_target_value( $biomarker );
		
		if ( empty( $current_value ) || empty( $target_value ) ) {
			return 3; // Medium priority if no data
		}
		
		$deviation = abs( $current_value - $target_value ) / $target_value;
		
		if ( $deviation > 0.5 ) {
			return 5; // High priority for significant deviation
		} elseif ( $deviation > 0.3 ) {
			return 4; // Medium-high priority
		} elseif ( $deviation > 0.1 ) {
			return 3; // Medium priority
		} else {
			return 2; // Low priority for minor deviation
		}
	}
	
	/**
	 * Get target value for biomarker
	 *
	 * @param string $biomarker Biomarker name
	 * @return float Target value
	 */
	private function get_target_value( $biomarker ) {
		foreach ( $this->health_goals as $goal_data ) {
			if ( isset( $goal_data['biomarkers'][ $biomarker ] ) ) {
				return $goal_data['biomarkers'][ $biomarker ]['target'];
			}
		}
		
		return 0;
	}
	
	/**
	 * Generate recommendations based on health goals and current score
	 *
	 * @param array $health_goals Health goals
	 * @param float $current_score Current score
	 * @return array Recommendations
	 */
	private function generate_recommendations( $health_goals, $current_score ) {
		$recommendations = array();
		
		// Only generate recommendations if we have real data
		if ( empty( $health_goals ) ) {
			return array(
				array(
					'type' => 'data_required',
					'title' => 'Complete Health Assessment',
					'description' => 'Complete health assessments to receive personalized recommendations.',
					'priority' => 'high'
				)
			);
		}
		
		foreach ( $health_goals as $goal ) {
			switch ( $goal ) {
				case 'weight_loss':
					$recommendations[] = array(
						'type' => 'lifestyle',
						'title' => 'Optimize Insulin Sensitivity',
						'description' => 'Focus on reducing refined carbohydrates and increasing physical activity to improve insulin sensitivity.',
						'priority' => 'high',
						'data_required' => array( 'insulin', 'glucose', 'hba1c' )
					);
					break;
					
				case 'energy_optimization':
					$recommendations[] = array(
						'type' => 'supplementation',
						'title' => 'Vitamin D Optimization',
						'description' => 'Consider vitamin D supplementation to reach optimal levels for energy and mood.',
						'priority' => 'medium',
						'data_required' => array( 'vitamin_d', 'vitamin_b12', 'ferritin' )
					);
					break;
					
				case 'mental_clarity':
					$recommendations[] = array(
						'type' => 'nutrition',
						'title' => 'Omega-3 Fatty Acids',
						'description' => 'Increase omega-3 fatty acid intake for improved cognitive function and mental clarity.',
						'priority' => 'medium',
						'data_required' => array( 'omega_3', 'vitamin_d', 'magnesium' )
					);
					break;
					
				case 'anti_aging':
					$recommendations[] = array(
						'type' => 'hormone',
						'title' => 'Testosterone Optimization',
						'description' => 'Work with your healthcare provider to optimize testosterone levels for vitality and anti-aging benefits.',
						'priority' => 'high',
						'data_required' => array( 'testosterone_total', 'dhea', 'vitamin_d' )
					);
					break;
					
				case 'cardiovascular_health':
					$recommendations[] = array(
						'type' => 'lifestyle',
						'title' => 'Cardiovascular Exercise',
						'description' => 'Incorporate regular cardiovascular exercise to improve cholesterol profile and heart health.',
						'priority' => 'high',
						'data_required' => array( 'cholesterol_total', 'cholesterol_hdl', 'cholesterol_ldl' )
					);
					break;
			}
		}
		
		// Add data requirement notice if no recommendations can be made
		if ( empty( $recommendations ) ) {
			$recommendations[] = array(
				'type' => 'data_required',
				'title' => 'Import Lab Results',
				'description' => 'Import your lab results to receive personalized recommendations based on your actual biomarker levels.',
				'priority' => 'high'
			);
		}
		
		return $recommendations;
	}
	
	/**
	 * AJAX handler for generating smart defaults
	 */
	public function ajax_generate_smart_defaults() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_smart_defaults' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$user_id = get_current_user_id();
		$health_goals = isset( $_POST['health_goals'] ) ? sanitize_text_field( $_POST['health_goals'] ) : array();
		$current_score = isset( $_POST['current_score'] ) ? floatval( $_POST['current_score'] ) : 0;
		
		$smart_defaults = $this->generate_smart_defaults( $user_id, $health_goals, $current_score );
		
		wp_send_json_success( $smart_defaults );
	}
	
	/**
	 * Initialize smart defaults generator
	 */
	public function init() {
		// Add admin menu for smart defaults
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Add shortcode for smart defaults
		add_shortcode( 'ennu_smart_defaults', array( $this, 'render_smart_defaults' ) );
		
		error_log( 'ENNU Smart Defaults Generator: Initialized successfully' );
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life-assessments',
			'Smart Defaults',
			'Smart Defaults',
			'manage_options',
			'ennu-smart-defaults',
			array( $this, 'admin_page' )
		);
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1>ENNU Smart Defaults Generator</h1>
			<p>Generate intelligent defaults for New Life Score calculation based on health goals.</p>
			
			<div class="smart-defaults-form">
				<h2>Generate Smart Defaults</h2>
				<form id="smart-defaults-form">
					<?php wp_nonce_field( 'ennu_smart_defaults', 'smart_defaults_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">Health Goals</th>
							<td>
								<label><input type="checkbox" name="health_goals[]" value="weight_loss"> Weight Loss</label><br>
								<label><input type="checkbox" name="health_goals[]" value="energy_optimization"> Energy Optimization</label><br>
								<label><input type="checkbox" name="health_goals[]" value="mental_clarity"> Mental Clarity</label><br>
								<label><input type="checkbox" name="health_goals[]" value="anti_aging"> Anti-Aging</label><br>
								<label><input type="checkbox" name="health_goals[]" value="cardiovascular_health"> Cardiovascular Health</label>
							</td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="Generate Smart Defaults">
					</p>
				</form>
			</div>
			
			<div id="smart-defaults-results" style="display: none;">
				<h2>Generated Smart Defaults</h2>
				<div id="smart-defaults-content"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#smart-defaults-form').on('submit', function(e) {
				e.preventDefault();
				
				var formData = new FormData(this);
				formData.append('action', 'ennu_generate_smart_defaults');
				formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_smart_defaults' ); ?>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.success) {
							$('#smart-defaults-content').html(JSON.stringify(response.data, null, 2));
							$('#smart-defaults-results').show();
						}
					}
				});
			});
		});
		</script>
		<?php
	}
	
	/**
	 * Render smart defaults shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string Rendered content
	 */
	public function render_smart_defaults( $atts ) {
		$user_id = get_current_user_id();
		
		if ( ! $user_id ) {
			return '<p>Please log in to view smart defaults.</p>';
		}
		
		$smart_defaults = get_user_meta( $user_id, 'ennu_smart_defaults', true );
		
		if ( empty( $smart_defaults ) ) {
			return '<p>No smart defaults generated yet. Please contact your healthcare provider.</p>';
		}
		
		ob_start();
		?>
		<div class="smart-defaults-display">
			<h3>Your Smart Defaults</h3>
			
			<div class="score-projection">
				<h4>Score Projection</h4>
				<p><strong>Current Score:</strong> <?php echo number_format( $smart_defaults['projected_score'] - $smart_defaults['improvement_potential'], 1 ); ?></p>
				<p><strong>Projected Score:</strong> <?php echo number_format( $smart_defaults['projected_score'], 1 ); ?></p>
				<p><strong>Improvement Potential:</strong> +<?php echo number_format( $smart_defaults['improvement_potential'], 1 ); ?> points</p>
			</div>
			
			<div class="biomarker-targets">
				<h4>Biomarker Targets</h4>
				<?php foreach ( $smart_defaults['biomarker_targets'] as $biomarker => $target_data ) : ?>
					<div class="biomarker-target">
						<strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $biomarker ) ) ); ?>:</strong>
						Target: <?php echo esc_html( $target_data['target'] ); ?>
						(<?php echo esc_html( $target_data['range'] ); ?>)
						- Priority: <?php echo esc_html( $target_data['priority'] ); ?>/5
					</div>
				<?php endforeach; ?>
			</div>
			
			<div class="recommendations">
				<h4>Recommendations</h4>
				<?php foreach ( $smart_defaults['recommendations'] as $recommendation ) : ?>
					<div class="recommendation">
						<strong><?php echo esc_html( $recommendation['title'] ); ?></strong>
						<p><?php echo esc_html( $recommendation['description'] ); ?></p>
						<span class="priority priority-<?php echo esc_attr( $recommendation['priority'] ); ?>">
							<?php echo esc_html( ucfirst( $recommendation['priority'] ) ); ?> Priority
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
