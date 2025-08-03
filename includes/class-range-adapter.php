<?php
/**
 * ENNU Range Adapter
 * Provides a consistent interface for retrieving biomarker ranges, acting as a bridge
 * to the authoritative AI Medical Team database system.
 *
 * @package ENNU_Life_Assessments
 * @version 64.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Range_Adapter {

	/**
	 * Get the recommended range for a biomarker, adapting the new DB system to the old format.
	 *
	 * @param string $biomarker_name The biomarker name.
	 * @param array $user_data User demographic data (age, gender, etc.).
	 * @return array Recommended range data.
	 */
	public static function get_recommended_range( $biomarker_name, $user_data = array() ) {
		if ( ! class_exists( 'ENNU_AI_Medical_Team_Reference_Ranges' ) ) {
			return array( 'error' => 'AI Medical Team data system is unavailable.' );
		}

		$ai_system = new ENNU_AI_Medical_Team_Reference_Ranges();
		$all_ranges = $ai_system->get_user_reference_ranges( $user_data['user_id'] ?? 0 );

		if ( ! isset( $all_ranges[ $biomarker_name ] ) ) {
			return array( 'error' => 'Biomarker range not found in AI Medical Team database.' );
		}

		$db_data = $all_ranges[ $biomarker_name ];
		$ranges = $db_data['reference_ranges'];

		// This is where we would apply age/gender adjustments from the DB data
		// For now, we will use the default ranges.

		return array(
			'biomarker'    => $biomarker_name,
			'display_name' => ucwords( str_replace( '_', ' ', $biomarker_name ) ),
			'unit'         => $ranges['optimal']['unit'] ?? 'N/A',
			'optimal_min'  => $ranges['optimal']['min'] ?? null,
			'optimal_max'  => $ranges['optimal']['max'] ?? null,
			'normal_min'   => $ranges['normal']['min'] ?? null,
			'normal_max'   => $ranges['normal']['max'] ?? null,
			'critical_min' => $ranges['critical']['min'] ?? null,
			'critical_max' => $ranges['critical']['max'] ?? null,
			'description'  => $db_data['clinical_significance'] ?? 'No description available.',
		);
	}
} 