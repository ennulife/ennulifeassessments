<?php
/**
 * ENNU Goal Progression Tracker
 *
 * Tracks "Good → Better → Best" improvements and goal achievement
 *
 * @package ENNU_Life_Assessments
 * @since 64.16.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Goal Progression Tracker Class
 *
 * @since 64.16.0
 */
class ENNU_Goal_Progression_Tracker {
	
	/**
	 * Goal progression levels
	 *
	 * @var array
	 */
	private $progression_levels = array(
		'good' => array(
			'min_score' => 0,
			'max_score' => 60,
			'label' => 'Good',
			'color' => '#ff6b6b',
			'description' => 'Basic health optimization',
		),
		'better' => array(
			'min_score' => 61,
			'max_score' => 80,
			'label' => 'Better',
			'color' => '#4ecdc4',
			'description' => 'Enhanced health performance',
		),
		'best' => array(
			'min_score' => 81,
			'max_score' => 100,
			'label' => 'Best',
			'color' => '#45b7d1',
			'description' => 'Optimal health achievement',
		),
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_ennu_track_goal_progression', array( $this, 'ajax_track_goal_progression' ) );
		add_action( 'wp_ajax_nopriv_ennu_track_goal_progression', array( $this, 'ajax_track_goal_progression' ) );
		add_action( 'wp_ajax_ennu_get_progression_data', array( $this, 'get_progression_data' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_progression_data', array( $this, 'get_progression_data' ) );
	}
	
	/**
	 * Track goal progression for user
	 *
	 * @param int $user_id User ID
	 * @param float $current_score Current New Life Score
	 * @param array $assessment_data Assessment data
	 * @return array Progression data
	 */
	public function track_goal_progression( $user_id = null, $current_score = 0, $assessment_data = array() ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		
		if ( $current_score === 0 ) {
			$current_score = $this->get_current_new_life_score( $user_id );
		}
		
		$progression_data = array(
			'current_level' => $this->get_current_level( $current_score ),
			'previous_level' => $this->get_previous_level( $user_id ),
			'progress_percentage' => $this->calculate_progress_percentage( $current_score ),
			'improvement_potential' => $this->calculate_improvement_potential( $current_score ),
			'goal_achievements' => $this->track_goal_achievements( $user_id, $assessment_data ),
			'milestones' => $this->get_milestones( $user_id ),
			'last_updated' => current_time( 'mysql' ),
		);
		
		// Save progression data
		update_user_meta( $user_id, 'ennu_goal_progression', $progression_data );
		
		// Check for level improvements
		$level_improvement = $this->check_level_improvement( $user_id, $progression_data );
		if ( $level_improvement ) {
			$this->send_improvement_notification( $user_id, $level_improvement );
		}
		
		// REMOVED: error_log( "ENNU Goal Progression Tracker: Tracked progression for user {$user_id}" );
		
		return $progression_data;
	}
	
	/**
	 * Get current progression level
	 *
	 * @param float $score Current score
	 * @return string Current level
	 */
	private function get_current_level( $score ) {
		foreach ( $this->progression_levels as $level => $level_data ) {
			if ( $score >= $level_data['min_score'] && $score <= $level_data['max_score'] ) {
				return $level;
			}
		}
		
		return 'good'; // Default level
	}
	
	/**
	 * Get previous level
	 *
	 * @param int $user_id User ID
	 * @return string Previous level
	 */
	private function get_previous_level( $user_id ) {
		$progression_data = get_user_meta( $user_id, 'ennu_goal_progression', true );
		return isset( $progression_data['current_level'] ) ? $progression_data['current_level'] : 'good';
	}
	
	/**
	 * Calculate progress percentage within current level
	 *
	 * @param float $score Current score
	 * @return float Progress percentage
	 */
	private function calculate_progress_percentage( $score ) {
		$current_level = $this->get_current_level( $score );
		$level_data = $this->progression_levels[ $current_level ];
		
		$level_range = $level_data['max_score'] - $level_data['min_score'];
		$score_position = $score - $level_data['min_score'];
		
		return ( $score_position / $level_range ) * 100;
	}
	
	/**
	 * Calculate improvement potential
	 *
	 * @param float $score Current score
	 * @return float Improvement potential
	 */
	private function calculate_improvement_potential( $score ) {
		$current_level = $this->get_current_level( $score );
		$level_data = $this->progression_levels[ $current_level ];
		
		return $level_data['max_score'] - $score;
	}
	
	/**
	 * Track goal achievements
	 *
	 * @param int $user_id User ID
	 * @param array $assessment_data Assessment data
	 * @return array Goal achievements
	 */
	private function track_goal_achievements( $user_id, $assessment_data = array() ) {
		$achievements = array();
		
		// Track assessment completion achievements
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$achievements['assessments_completed'] = count( $completed_assessments );
		
		// Track biomarker optimization achievements
		$optimized_biomarkers = $this->get_optimized_biomarkers( $user_id );
		$achievements['biomarkers_optimized'] = count( $optimized_biomarkers );
		
		// Track score improvement achievements
		$score_improvements = $this->get_score_improvements( $user_id );
		$achievements['score_improvements'] = $score_improvements;
		
		// Track consistency achievements
		$consistency_score = $this->calculate_consistency_score( $user_id );
		$achievements['consistency_score'] = $consistency_score;
		
		return $achievements;
	}
	
	/**
	 * Get completed assessments
	 *
	 * @param int $user_id User ID
	 * @return array Completed assessments
	 */
	private function get_completed_assessments( $user_id ) {
		$assessment_types = array(
			'testosterone', 'health', 'weight_loss', 'sleep', 'stress',
			'nutrition', 'fitness', 'cognitive', 'longevity', 'menopause'
		);
		
		$completed = array();
		
		foreach ( $assessment_types as $assessment_type ) {
			$assessment_data = get_user_meta( $user_id, "ennu_{$assessment_type}_assessment", true );
			if ( ! empty( $assessment_data ) ) {
				$completed[] = $assessment_type;
			}
		}
		
		return $completed;
	}
	
	/**
	 * Get optimized biomarkers
	 *
	 * @param int $user_id User ID
	 * @return array Optimized biomarkers
	 */
	private function get_optimized_biomarkers( $user_id ) {
		$biomarkers = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
		$optimized = array();
		
		if ( ! empty( $biomarkers ) ) {
			foreach ( $biomarkers as $biomarker => $data ) {
				if ( isset( $data['status'] ) && $data['status'] === 'optimal' ) {
					$optimized[] = $biomarker;
				}
			}
		}
		
		return $optimized;
	}
	
	/**
	 * Get score improvements
	 *
	 * @param int $user_id User ID
	 * @return array Score improvements
	 */
	private function get_score_improvements( $user_id ) {
		$score_history = get_user_meta( $user_id, 'ennu_score_history', true );
		$improvements = array();
		
		if ( ! empty( $score_history ) && count( $score_history ) > 1 ) {
			$previous_score = $score_history[ count( $score_history ) - 2 ]['score'];
			$current_score = $score_history[ count( $score_history ) - 1 ]['score'];
			
			$improvements['total_improvement'] = $current_score - $previous_score;
			$improvements['percentage_improvement'] = ( ( $current_score - $previous_score ) / $previous_score ) * 100;
		}
		
		return $improvements;
	}
	
	/**
	 * Calculate consistency score
	 *
	 * @param int $user_id User ID
	 * @return float Consistency score
	 */
	private function calculate_consistency_score( $user_id ) {
		$assessment_history = get_user_meta( $user_id, 'ennu_assessment_history', true );
		$consistency_score = 0;
		
		if ( ! empty( $assessment_history ) ) {
			$total_assessments = count( $assessment_history );
			$consistent_months = 0;
			
			// Calculate consistency based on regular assessment completion
			foreach ( $assessment_history as $assessment ) {
				if ( isset( $assessment['completed'] ) && $assessment['completed'] ) {
					$consistent_months++;
				}
			}
			
			$consistency_score = ( $consistent_months / $total_assessments ) * 100;
		}
		
		return $consistency_score;
	}
	
	/**
	 * Get milestones for user
	 *
	 * @param int $user_id User ID
	 * @return array Milestones
	 */
	private function get_milestones( $user_id ) {
		$milestones = array(
			'first_assessment' => array(
				'title' => 'First Assessment',
				'description' => 'Complete your first health assessment',
				'achieved' => false,
				'date_achieved' => null,
			),
			'five_assessments' => array(
				'title' => 'Assessment Explorer',
				'description' => 'Complete 5 different assessments',
				'achieved' => false,
				'date_achieved' => null,
			),
			'ten_assessments' => array(
				'title' => 'Health Master',
				'description' => 'Complete all 10 assessments',
				'achieved' => false,
				'date_achieved' => null,
			),
			'good_level' => array(
				'title' => 'Good Health',
				'description' => 'Achieve "Good" health level',
				'achieved' => false,
				'date_achieved' => null,
			),
			'better_level' => array(
				'title' => 'Better Health',
				'description' => 'Achieve "Better" health level',
				'achieved' => false,
				'date_achieved' => null,
			),
			'best_level' => array(
				'title' => 'Best Health',
				'description' => 'Achieve "Best" health level',
				'achieved' => false,
				'date_achieved' => null,
			),
		);
		
		// Check milestone achievements
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$current_score = $this->get_current_new_life_score( $user_id );
		$current_level = $this->get_current_level( $current_score );
		
		// First assessment milestone
		if ( count( $completed_assessments ) >= 1 ) {
			$milestones['first_assessment']['achieved'] = true;
			$milestones['first_assessment']['date_achieved'] = current_time( 'mysql' );
		}
		
		// Five assessments milestone
		if ( count( $completed_assessments ) >= 5 ) {
			$milestones['five_assessments']['achieved'] = true;
			$milestones['five_assessments']['date_achieved'] = current_time( 'mysql' );
		}
		
		// Ten assessments milestone
		if ( count( $completed_assessments ) >= 10 ) {
			$milestones['ten_assessments']['achieved'] = true;
			$milestones['ten_assessments']['date_achieved'] = current_time( 'mysql' );
		}
		
		// Level milestones
		if ( $current_level === 'good' || $current_level === 'better' || $current_level === 'best' ) {
			$milestones['good_level']['achieved'] = true;
			$milestones['good_level']['date_achieved'] = current_time( 'mysql' );
		}
		
		if ( $current_level === 'better' || $current_level === 'best' ) {
			$milestones['better_level']['achieved'] = true;
			$milestones['better_level']['date_achieved'] = current_time( 'mysql' );
		}
		
		if ( $current_level === 'best' ) {
			$milestones['best_level']['achieved'] = true;
			$milestones['best_level']['date_achieved'] = current_time( 'mysql' );
		}
		
		return $milestones;
	}
	
	/**
	 * Check for level improvement
	 *
	 * @param int $user_id User ID
	 * @param array $progression_data Progression data
	 * @return array|false Level improvement data or false
	 */
	private function check_level_improvement( $user_id, $progression_data ) {
		$previous_level = $progression_data['previous_level'];
		$current_level = $progression_data['current_level'];
		
		if ( $current_level !== $previous_level ) {
			$level_order = array( 'good', 'better', 'best' );
			$previous_index = array_search( $previous_level, $level_order );
			$current_index = array_search( $current_level, $level_order );
			
			if ( $current_index > $previous_index ) {
				return array(
					'from_level' => $previous_level,
					'to_level' => $current_level,
					'improvement' => $current_index - $previous_index,
					'date' => current_time( 'mysql' ),
				);
			}
		}
		
		return false;
	}
	
	/**
	 * Send improvement notification
	 *
	 * @param int $user_id User ID
	 * @param array $improvement_data Improvement data
	 */
	private function send_improvement_notification( $user_id, $improvement_data ) {
		$user = get_user_by( 'id', $user_id );
		$from_level = $this->progression_levels[ $improvement_data['from_level'] ]['label'];
		$to_level = $this->progression_levels[ $improvement_data['to_level'] ]['label'];
		
		$subject = "Congratulations! You've improved to {$to_level} level!";
		$message = "Dear {$user->display_name},\n\n";
		$message .= "Congratulations! You've successfully improved from {$from_level} to {$to_level} level!\n\n";
		$message .= "This represents a significant improvement in your health optimization journey.\n";
		$message .= "Keep up the great work!\n\n";
		$message .= "Best regards,\nENNU Health Team";
		
		wp_mail( $user->user_email, $subject, $message );
		
		// REMOVED: error_log( "ENNU Goal Progression Tracker: Sent improvement notification to user {$user_id}" );
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
	 * AJAX handler for tracking goal progression
	 */
	public function ajax_track_goal_progression() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_goal_progression' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$user_id = get_current_user_id();
		$current_score = isset( $_POST['current_score'] ) ? floatval( $_POST['current_score'] ) : 0;
		$assessment_data = isset( $_POST['assessment_data'] ) ? $_POST['assessment_data'] : array();
		
		$progression_data = $this->track_goal_progression( $user_id, $current_score, $assessment_data );
		
		wp_send_json_success( $progression_data );
	}
	
	/**
	 * AJAX handler for getting progression data
	 */
	public function get_progression_data() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_goal_progression' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$user_id = get_current_user_id();
		$progression_data = get_user_meta( $user_id, 'ennu_goal_progression', true );
		
		if ( empty( $progression_data ) ) {
			$current_score = $this->get_current_new_life_score( $user_id );
			$progression_data = $this->track_goal_progression( $user_id, $current_score );
		}
		
		wp_send_json_success( $progression_data );
	}
	
	/**
	 * Initialize goal progression tracker
	 */
	public function init() {
		// Add admin menu for goal progression
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Add shortcode for goal progression
		add_shortcode( 'ennu_goal_progression', array( $this, 'render_goal_progression' ) );
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'ENNU Goal Progression Tracker: Initialized successfully' );
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life-assessments',
			'Goal Progression',
			'Goal Progression',
			'manage_options',
			'ennu-goal-progression',
			array( $this, 'admin_page' )
		);
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1>ENNU Goal Progression Tracker</h1>
			<p>Track "Good → Better → Best" improvements and goal achievements.</p>
			
			<div class="goal-progression-stats">
				<h2>Progression Statistics</h2>
				<?php
				$users = get_users( array( 'role' => 'subscriber' ) );
				$total_users = count( $users );
				$good_users = 0;
				$better_users = 0;
				$best_users = 0;
				
				foreach ( $users as $user ) {
					$progression_data = get_user_meta( $user->ID, 'ennu_goal_progression', true );
					if ( ! empty( $progression_data ) ) {
						$level = $progression_data['current_level'];
						switch ( $level ) {
							case 'good':
								$good_users++;
								break;
							case 'better':
								$better_users++;
								break;
							case 'best':
								$best_users++;
								break;
						}
					}
				}
				?>
				
				<table class="widefat">
					<thead>
						<tr>
							<th>Level</th>
							<th>Users</th>
							<th>Percentage</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Good</td>
							<td><?php echo $good_users; ?></td>
							<td><?php echo $total_users > 0 ? round( ( $good_users / $total_users ) * 100, 1 ) : 0; ?>%</td>
						</tr>
						<tr>
							<td>Better</td>
							<td><?php echo $better_users; ?></td>
							<td><?php echo $total_users > 0 ? round( ( $better_users / $total_users ) * 100, 1 ) : 0; ?>%</td>
						</tr>
						<tr>
							<td>Best</td>
							<td><?php echo $best_users; ?></td>
							<td><?php echo $total_users > 0 ? round( ( $best_users / $total_users ) * 100, 1 ) : 0; ?>%</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Render goal progression shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string Rendered content
	 */
	public function render_goal_progression( $atts ) {
		$user_id = get_current_user_id();
		
		if ( ! $user_id ) {
			return '<p>Please log in to view goal progression.</p>';
		}
		
		$progression_data = get_user_meta( $user_id, 'ennu_goal_progression', true );
		
		if ( empty( $progression_data ) ) {
			$current_score = $this->get_current_new_life_score( $user_id );
			$progression_data = $this->track_goal_progression( $user_id, $current_score );
		}
		
		ob_start();
		?>
		<div class="goal-progression-display">
			<h3>Your Goal Progression</h3>
			
			<div class="current-level">
				<h4>Current Level: <?php echo esc_html( ucfirst( $progression_data['current_level'] ) ); ?></h4>
				<?php
				$level_data = $this->progression_levels[ $progression_data['current_level'] ];
				?>
				<p><?php echo esc_html( $level_data['description'] ); ?></p>
				<div class="progress-bar">
					<div class="progress-fill" style="width: <?php echo esc_attr( $progression_data['progress_percentage'] ); ?>%; background-color: <?php echo esc_attr( $level_data['color'] ); ?>;"></div>
				</div>
				<p>Progress: <?php echo esc_html( round( $progression_data['progress_percentage'], 1 ) ); ?>%</p>
			</div>
			
			<div class="goal-achievements">
				<h4>Goal Achievements</h4>
				<ul>
					<li>Assessments Completed: <?php echo esc_html( $progression_data['goal_achievements']['assessments_completed'] ); ?></li>
					<li>Biomarkers Optimized: <?php echo esc_html( $progression_data['goal_achievements']['biomarkers_optimized'] ); ?></li>
					<li>Consistency Score: <?php echo esc_html( round( $progression_data['goal_achievements']['consistency_score'], 1 ) ); ?>%</li>
				</ul>
			</div>
			
			<div class="milestones">
				<h4>Milestones</h4>
				<?php foreach ( $progression_data['milestones'] as $milestone_key => $milestone ) : ?>
					<div class="milestone <?php echo $milestone['achieved'] ? 'achieved' : 'pending'; ?>">
						<strong><?php echo esc_html( $milestone['title'] ); ?></strong>
						<p><?php echo esc_html( $milestone['description'] ); ?></p>
						<?php if ( $milestone['achieved'] ) : ?>
							<span class="achieved-date">Achieved: <?php echo esc_html( $milestone['date_achieved'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
