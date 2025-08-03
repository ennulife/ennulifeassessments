<?php
/**
 * ENNU Assessment Constants - Single Source of Truth
 *
 * @package   WP Fusion
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.25.0
 */

/**
 * ENNU Assessment Constants Class
 * 
 * This class provides a single source of truth for all assessment naming conventions.
 * All assessment keys, shortcodes, meta keys, and URLs should use these constants.
 */
class ENNU_Assessment_Constants {

	/**
	 * Assessment keys in their canonical format (underscores)
	 * This is the single source of truth for all assessment naming
	 */
	const ASSESSMENT_KEYS = array(
		'welcome_assessment',
		'hair_assessment',
		'ed_treatment_assessment',
		'weight_loss_assessment',
		'health_assessment',
		'health_optimization_assessment',
		'skin_assessment',
		'hormone_assessment',
		'sleep_assessment',
		'menopause_assessment',
		'testosterone_assessment',
	);

	/**
	 * Get assessment key in canonical format (underscores)
	 *
	 * @param string $input_key The input key in any format
	 * @return string The canonical assessment key
	 */
	public static function get_canonical_key( $input_key ) {
		// Normalize input key
		$normalized = str_replace( array( '-', ' ' ), '_', strtolower( $input_key ) );
		
		// Remove common suffixes
		$normalized = str_replace( array( '_assessment', '_quiz' ), '', $normalized );
		$normalized = $normalized . '_assessment';
		
		// Validate against known keys
		if ( in_array( $normalized, self::ASSESSMENT_KEYS ) ) {
			return $normalized;
		}
		
		// Fallback: try to match partial keys
		foreach ( self::ASSESSMENT_KEYS as $canonical_key ) {
			$partial_key = str_replace( '_assessment', '', $canonical_key );
			if ( $normalized === $partial_key . '_assessment' || $normalized === $partial_key ) {
				return $canonical_key;
			}
		}
		
		// If no match found, return the normalized input
		return $normalized;
	}

	/**
	 * Get assessment key in shortcode format (hyphens)
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @return string The shortcode format key
	 */
	public static function get_shortcode_key( $canonical_key ) {
		return str_replace( '_', '-', $canonical_key );
	}

	/**
	 * Get assessment key in URL format (hyphens)
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @return string The URL format key
	 */
	public static function get_url_key( $canonical_key ) {
		return str_replace( '_', '-', $canonical_key );
	}

	/**
	 * Get assessment key in meta key format (underscores)
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @return string The meta key format
	 */
	public static function get_meta_key( $canonical_key ) {
		return $canonical_key; // Already in correct format
	}

	/**
	 * Get full meta key with prefix
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @param string $suffix The meta key suffix (e.g., 'calculated_score', 'completed')
	 * @return string The full meta key
	 */
	public static function get_full_meta_key( $canonical_key, $suffix ) {
		return 'ennu_' . $canonical_key . '_' . $suffix;
	}

	/**
	 * Get shortcode tag
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @return string The shortcode tag
	 */
	public static function get_shortcode_tag( $canonical_key ) {
		return 'ennu-' . self::get_shortcode_key( $canonical_key );
	}

	/**
	 * Get details shortcode tag
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @return string The details shortcode tag
	 */
	public static function get_details_shortcode_tag( $canonical_key ) {
		return 'ennu-' . self::get_shortcode_key( $canonical_key ) . '-assessment-details';
	}

	/**
	 * Convert any format to canonical format
	 *
	 * @param string $input The input in any format
	 * @return string The canonical format
	 */
	public static function normalize_to_canonical( $input ) {
		return self::get_canonical_key( $input );
	}

	/**
	 * Validate if an assessment key is valid
	 *
	 * @param string $key The key to validate
	 * @return bool True if valid
	 */
	public static function is_valid_assessment_key( $key ) {
		$canonical = self::get_canonical_key( $key );
		return in_array( $canonical, self::ASSESSMENT_KEYS );
	}

	/**
	 * Get all assessment keys in a specific format
	 *
	 * @param string $format The format ('canonical', 'shortcode', 'url', 'meta')
	 * @return array Array of keys in the specified format
	 */
	public static function get_all_keys_in_format( $format = 'canonical' ) {
		$keys = array();
		
		foreach ( self::ASSESSMENT_KEYS as $canonical_key ) {
			switch ( $format ) {
				case 'canonical':
					$keys[] = $canonical_key;
					break;
				case 'shortcode':
					$keys[] = self::get_shortcode_key( $canonical_key );
					break;
				case 'url':
					$keys[] = self::get_url_key( $canonical_key );
					break;
				case 'meta':
					$keys[] = self::get_meta_key( $canonical_key );
					break;
			}
		}
		
		return $keys;
	}

	/**
	 * Get assessment display name
	 *
	 * @param string $canonical_key The canonical assessment key
	 * @return string The display name
	 */
	public static function get_display_name( $canonical_key ) {
		$display_names = array(
			'welcome_assessment' => 'Welcome Assessment',
			'hair_assessment' => 'Hair Assessment',
			'ed_treatment_assessment' => 'ED Treatment Assessment',
			'weight_loss_assessment' => 'Weight Loss Assessment',
			'health_assessment' => 'Health Assessment',
			'health_optimization_assessment' => 'Health Optimization Assessment',
			'skin_assessment' => 'Skin Assessment',
			'hormone_assessment' => 'Hormone Assessment',
			'sleep_assessment' => 'Sleep Assessment',
			'menopause_assessment' => 'Menopause Assessment',
			'testosterone_assessment' => 'Testosterone Assessment',
		);
		
		return $display_names[ $canonical_key ] ?? ucwords( str_replace( '_', ' ', $canonical_key ) );
	}
} 