<?php
/**
 * Scores Display Shortcode Class
 *
 * Handles score display functionality
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ENNU_LIFE_PLUGIN_PATH . 'includes/shortcodes/class-base-shortcode.php';

class ENNU_Scores_Display_Shortcode extends ENNU_Base_Shortcode {

	protected $shortcode_name = 'ennu_scores_display';

	protected $default_attributes = array(
		'score_type'          => 'all',
		'show_interpretation' => 'true',
		'show_history'        => 'false',
		'chart_type'          => 'bar',
	);

	public function render( $atts, $content = null ) {
		if ( ! $this->validate_user_permissions() ) {
			return $this->render_error( 'You must be logged in to view scores.' );
		}

		$atts    = $this->parse_attributes( $atts );
		$user_id = get_current_user_id();

		$this->enqueue_assets();

		$scores_data = $this->get_scores_data( $user_id, $atts['score_type'] );

		return $this->get_template(
			'scores-display',
			array(
				'scores_data' => $scores_data,
				'user_id'     => $user_id,
				'attributes'  => $atts,
			)
		);
	}

	protected function enqueue_assets() {
		wp_enqueue_script(
			'ennu-scores-display',
			ENNU_LIFE_PLUGIN_URL . 'assets/js/scores-display.js',
			array(),
			ENNU_LIFE_PLUGIN_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-scores-display',
			ENNU_LIFE_PLUGIN_URL . 'assets/css/scores-display.css',
			array(),
			ENNU_LIFE_PLUGIN_VERSION
		);
	}

	private function get_scores_data( $user_id, $score_type ) {
		$data = array();

		if ( $score_type === 'all' || $score_type === 'ennu_life' ) {
			$data['ennu_life_score'] = array(
				'value'          => get_user_meta( $user_id, 'ennu_life_score', true ),
				'interpretation' => $this->get_score_interpretation( get_user_meta( $user_id, 'ennu_life_score', true ) ),
			);
		}

		if ( $score_type === 'all' || $score_type === 'pillars' ) {
			$pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
			if ( is_array( $pillar_scores ) ) {
				foreach ( $pillar_scores as $pillar => $score ) {
					$data['pillar_scores'][ $pillar ] = array(
						'value'          => $score,
						'interpretation' => $this->get_score_interpretation( $score ),
					);
				}
			}
		}

		if ( $score_type === 'all' || $score_type === 'history' ) {
			$data['score_history'] = get_user_meta( $user_id, 'ennu_life_score_history', true ) ?: array();
		}

		return $data;
	}

	private function get_score_interpretation( $score ) {
		if ( ! is_numeric( $score ) ) {
			return array(
				'level'       => 'Unknown',
				'color'       => '#666666',
				'description' => 'Score not available',
			);
		}

		return ENNU_Assessment_Scoring::get_score_interpretation( floatval( $score ) );
	}
}

new ENNU_Scores_Display_Shortcode();
