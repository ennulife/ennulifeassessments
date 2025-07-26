<?php
/**
 * Score Presentation Shortcode Class
 *
 * Handles beautiful score presentation displays for assessments and life scores
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license GPL-3.0+
 * @since 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ENNU_LIFE_PLUGIN_PATH . 'includes/shortcodes/class-base-shortcode.php';

class ENNU_Score_Presentation_Shortcode extends ENNU_Base_Shortcode {

	protected $shortcode_name = 'ennu_score_presentation';

	protected $default_attributes = array(
		'type'              => 'lifescore',
		'show_pillars'      => 'true',
		'show_history'      => 'false',
		'show_interpretation' => 'true',
		'animation'         => 'true',
		'size'              => 'medium',
		'theme'             => 'light',
	);

	public function render( $atts, $content = null ) {
		if ( ! $this->validate_user_permissions() ) {
			return $this->render_error( 'You must be logged in to view scores.' );
		}

		$atts    = $this->parse_attributes( $atts );
		$user_id = get_current_user_id();

		$this->enqueue_assets();

		$score_data = $this->get_score_data( $user_id, $atts['type'] );

		return $this->get_template(
			'score-presentation',
			array(
				'score_data' => $score_data,
				'user_id'    => $user_id,
				'attributes' => $atts,
			)
		);
	}

	protected function enqueue_assets() {
		wp_enqueue_script(
			'ennu-score-presentation',
			ENNU_LIFE_PLUGIN_URL . 'assets/js/score-presentation.js',
			array( 'jquery' ),
			'62.2.8',
			true
		);

		wp_enqueue_style(
			'ennu-score-presentation',
			ENNU_LIFE_PLUGIN_URL . 'assets/css/score-presentation.css',
			array(),
			'62.2.8'
		);

		// Localize script with AJAX data
		wp_localize_script(
			'ennu-score-presentation',
			'ennuScorePresentation',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ennu_score_presentation_nonce' ),
			)
		);
	}

	private function get_score_data( $user_id, $type ) {
		$data = array();

		switch ( $type ) {
			case 'lifescore':
				$data = $this->get_life_score_data( $user_id );
				break;
			case 'pmind':
			case 'pbody':
			case 'plifestyle':
			case 'paesthetics':
				$data = $this->get_pillar_score_data( $user_id, $type );
				break;
			default:
				$data = $this->get_assessment_score_data( $user_id, $type );
				break;
		}

		return $data;
	}

	private function get_life_score_data( $user_id ) {
		$life_score = get_user_meta( $user_id, 'ennu_life_score', true );
		$life_score = is_numeric( $life_score ) ? floatval( $life_score ) : 0;

		$pillar_scores = get_user_meta( $user_id, 'ennu_pillar_scores', true );
		$pillar_scores = is_array( $pillar_scores ) ? $pillar_scores : array();

		// Get historical data
		$score_history = get_user_meta( $user_id, 'ennu_life_score_history', true );
		$score_history = is_array( $score_history ) ? $score_history : array();

		return array(
			'type'           => 'lifescore',
			'title'          => 'ENNU Life Score',
			'score'          => $life_score,
			'interpretation' => $this->get_score_interpretation( $life_score ),
			'pillar_scores'  => $pillar_scores,
			'history'        => $score_history,
			'last_updated'   => get_user_meta( $user_id, 'ennu_life_score_last_updated', true ),
		);
	}

	private function get_pillar_score_data( $user_id, $pillar_type ) {
		$pillar_map = array(
			'pmind'      => 'mind',
			'pbody'      => 'body',
			'plifestyle' => 'lifestyle',
			'paesthetics' => 'aesthetics',
		);

		$pillar_key = $pillar_map[ $pillar_type ] ?? 'mind';
		$score      = get_user_meta( $user_id, 'ennu_pillar_' . $pillar_key . '_score', true );
		$score      = is_numeric( $score ) ? floatval( $score ) : 0;

		return array(
			'type'           => 'pillar',
			'pillar'         => $pillar_key,
			'title'          => ucfirst( $pillar_key ) . ' Pillar Score',
			'score'          => $score,
			'interpretation' => $this->get_score_interpretation( $score ),
			'last_updated'   => get_user_meta( $user_id, 'ennu_pillar_' . $pillar_key . '_last_updated', true ),
		);
	}

	private function get_assessment_score_data( $user_id, $assessment_type ) {
		$score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
		$score = is_numeric( $score ) ? floatval( $score ) : 0;

		// Get pillar scores from assessment
		$pillar_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_pillar_scores', true );
		$pillar_scores = is_array( $pillar_scores ) ? $pillar_scores : array();

		// Get historical scores
		$historical_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_historical_scores', true );
		$historical_scores = is_array( $historical_scores ) ? $historical_scores : array();

		// Get assessment title
		$assessment_titles = array(
			'weight_loss_assessment'         => 'Weight Loss Assessment',
			'hair_assessment'                => 'Hair Assessment',
			'skin_assessment'                => 'Skin Assessment',
			'ed_treatment_assessment'        => 'ED Treatment Assessment',
			'health_assessment'              => 'Health Assessment',
			'hormone_assessment'             => 'Hormone Assessment',
			'sleep_assessment'               => 'Sleep Assessment',
			'menopause_assessment'           => 'Menopause Assessment',
			'testosterone_assessment'        => 'Testosterone Assessment',
			'health_optimization_assessment' => 'Health Optimization Assessment',
		);

		$title = $assessment_titles[ $assessment_type ] ?? ucwords( str_replace( '_', ' ', $assessment_type ) );

		return array(
			'type'             => 'assessment',
			'assessment_type'  => $assessment_type,
			'title'            => $title,
			'score'            => $score,
			'interpretation'   => $this->get_score_interpretation( $score ),
			'pillar_scores'    => $pillar_scores,
			'history'          => $historical_scores,
			'last_updated'     => get_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', true ),
		);
	}

	private function get_score_interpretation( $score ) {
		if ( ! is_numeric( $score ) ) {
			return array(
				'level'       => 'Unknown',
				'color'       => '#666666',
				'description' => 'Score not available',
				'class'       => 'unknown',
			);
		}

		$score = floatval( $score );

		if ( $score >= 8.5 ) {
			return array(
				'level'       => 'Excellent',
				'color'       => '#10b981',
				'description' => 'Outstanding performance! You\'re in the top tier.',
				'class'       => 'excellent',
			);
		} elseif ( $score >= 7.0 ) {
			return array(
				'level'       => 'Good',
				'color'       => '#3b82f6',
				'description' => 'Good performance with room for improvement.',
				'class'       => 'good',
			);
		} elseif ( $score >= 5.5 ) {
			return array(
				'level'       => 'Fair',
				'color'       => '#f59e0b',
				'description' => 'Fair performance. Consider optimization strategies.',
				'class'       => 'fair',
			);
		} else {
			return array(
				'level'       => 'Needs Improvement',
				'color'       => '#ef4444',
				'description' => 'Focus on improvement strategies for better results.',
				'class'       => 'needs-improvement',
			);
		}
	}

	/**
	 * AJAX handler for getting score data
	 */
	public function ajax_get_score_data() {
		check_ajax_referer( 'ennu_score_presentation_nonce', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'User not logged in' );
		}

		$type    = sanitize_text_field( $_POST['type'] ?? 'lifescore' );
		$user_id = get_current_user_id();

		$score_data = $this->get_score_data( $user_id, $type );

		wp_send_json_success( $score_data );
	}
}

// Initialize the shortcode
new ENNU_Score_Presentation_Shortcode();

// Register AJAX handlers
add_action( 'wp_ajax_ennu_get_score_data', array( 'ENNU_Score_Presentation_Shortcode', 'ajax_get_score_data' ) ); 