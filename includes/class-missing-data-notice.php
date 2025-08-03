<?php
/**
 * ENNU Missing Data Notice Class
 * Handles notices for missing pillar scores and assessment data
 *
 * @package ENNU_Life
 * @version 64.35.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Missing_Data_Notice {

	/**
	 * Get pillar-specific missing data notice
	 *
	 * @param string $pillar The pillar name (Mind, Body, Lifestyle, Aesthetics)
	 * @return array Notice data with icon, title, and action
	 */
	public static function get_pillar_missing_notice( $pillar ) {
		$notices = array(
			'Mind' => array(
				'icon' => '🧠',
				'title' => 'Mental Health Score',
				'action' => 'Take Mental Health Assessment',
				'assessments' => array(
					'health-optimization' => 'Health Optimization Assessment',
					'hormone' => 'Hormone Assessment',
					'sleep' => 'Sleep Assessment'
				)
			),
			'Body' => array(
				'icon' => '💪',
				'title' => 'Physical Health Score',
				'action' => 'Take Physical Assessment',
				'assessments' => array(
					'health-optimization' => 'Health Optimization Assessment',
					'hormone' => 'Hormone Assessment',
					'testosterone' => 'Testosterone Assessment',
					'weight-loss' => 'Weight Loss Assessment'
				)
			),
			'Lifestyle' => array(
				'icon' => '🌱',
				'title' => 'Lifestyle Score',
				'action' => 'Take Lifestyle Assessment',
				'assessments' => array(
					'health-optimization' => 'Health Optimization Assessment',
					'weight-loss' => 'Weight Loss Assessment',
					'sleep' => 'Sleep Assessment'
				)
			),
			'Aesthetics' => array(
				'icon' => '✨',
				'title' => 'Aesthetics Score',
				'action' => 'Take Aesthetics Assessment',
				'assessments' => array(
					'skin' => 'Skin Assessment',
					'hair' => 'Hair Assessment',
					'weight-loss' => 'Weight Loss Assessment'
				)
			)
		);

		return $notices[ $pillar ] ?? array(
			'icon' => '📊',
			'title' => 'Score Not Available',
			'action' => 'Take Assessment',
			'assessments' => array()
		);
	}

	/**
	 * Get assessment recommendations for a pillar
	 *
	 * @param string $pillar The pillar name
	 * @return array Array of recommended assessments
	 */
	public static function get_pillar_assessment_recommendations( $pillar ) {
		$notice = self::get_pillar_missing_notice( $pillar );
		return $notice['assessments'] ?? array();
	}

	/**
	 * Generate modal content for missing pillar score
	 *
	 * @param string $pillar The pillar name
	 * @return string HTML content for modal
	 */
	public static function get_pillar_modal_content( $pillar ) {
		$notice = self::get_pillar_missing_notice( $pillar );
		$assessments = $notice['assessments'] ?? array();

		ob_start();
		?>
		<div class="missing-data-modal">
			<div class="modal-header">
				<h3><?php echo esc_html( $notice['title'] ); ?></h3>
				<p>Complete these assessments to generate your <?php echo esc_html( $pillar ); ?> score:</p>
			</div>
			<div class="modal-content">
				<div class="assessment-recommendations">
					<?php foreach ( $assessments as $assessment_key => $assessment_name ) : ?>
						<div class="assessment-recommendation">
							<a href="<?php echo esc_url( '?' . ENNU_UI_Constants::get_page_type( 'ASSESSMENTS' ) . '&assessment=' . $assessment_key ); ?>" class="assessment-link">
								<span class="assessment-name"><?php echo esc_html( $assessment_name ); ?></span>
								<span class="assessment-action">Start Assessment</span>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
} 