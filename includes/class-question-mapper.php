<?php
/**
 * ENNU Question Mapper
 * Maps form data to scoring system format
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Question_Mapper {

	/**
	 * Map form data to scoring system format
	 *
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Raw form data
	 * @return array Mapped data for scoring system
	 */
	public static function map_form_data_to_scoring( $assessment_type, $form_data ) {
		if ( empty( $form_data ) || empty( $assessment_type ) ) {
			return array();
		}

		if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
			$definitions    = ENNU_Assessment_Scoring::get_all_definitions();
			$assessment_def = $definitions[ $assessment_type ] ?? array();
		} else {
			return $form_data; // Fallback to raw data
		}

		$mapped_data = array();

		foreach ( $form_data as $field_key => $field_value ) {
			$clean_key                 = str_replace( $assessment_type . '_', '', $field_key );
			$mapped_data[ $clean_key ] = $field_value;
		}

		return $mapped_data;
	}

	/**
	 * Get field mapping for specific assessment type
	 *
	 * @param string $assessment_type Assessment type
	 * @return array Field mappings
	 */
	public static function get_field_mappings( $assessment_type ) {
		$mappings = array();

		$common_mappings = array(
			'age'    => 'user_age',
			'gender' => 'user_gender',
			'height' => 'user_height',
			'weight' => 'user_weight',
		);

		switch ( $assessment_type ) {
			case 'welcome_assessment':
				$mappings = array_merge(
					$common_mappings,
					array(
						'health_goals'     => 'selected_health_goals',
						'current_concerns' => 'primary_concerns',
					)
				);
				break;

			case 'health_assessment':
				$mappings = array_merge(
					$common_mappings,
					array(
						'symptoms'    => 'reported_symptoms',
						'medications' => 'current_medications',
					)
				);
				break;

			default:
				$mappings = $common_mappings;
				break;
		}

		return $mappings;
	}
}
