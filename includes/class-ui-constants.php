<?php
/**
 * ENNU UI Constants - Single Source of Truth for URLs and Links
 *
 * @package   WP Fusion
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.29.0
 */

/**
 * ENNU UI Constants Class
 * 
 * This class provides a single source of truth for all URLs used in redirects,
 * href attributes, and linked buttons throughout the plugin.
 */
class ENNU_UI_Constants {

	/**
	 * Page type constants for URL generation using page_id format
	 * These map to the actual page mappings from admin settings
	 */
	const PAGE_TYPES = array(
		'DASHBOARD'                    => 'dashboard',
		'ASSESSMENTS'                  => 'assessments',
		'CALL'                         => 'call',
		'CONTACT'                      => 'contact',
		'LOGIN'                        => 'login',
		'REGISTRATION'                 => 'registration',
		'BOOK_HEALTH_CONSULTATION'     => 'book-health-consultation',
		'BOOK_HAIR_CONSULTATION'       => 'book-hair-consultation',
		'BOOK_ED_CONSULTATION'         => 'book-ed-consultation',
		'BOOK_WEIGHT_LOSS_CONSULTATION' => 'book-weight-loss-consultation',
		'BOOK_SKIN_CONSULTATION'       => 'book-skin-consultation',
		'BOOK_HEALTH_OPTIMIZATION_CONSULTATION' => 'book-health-optimization-consultation',
		'BOOK_HORMONE_CONSULTATION'    => 'book-hormone-consultation',
		'BOOK_SLEEP_CONSULTATION'      => 'book-sleep-consultation',
		'BOOK_MENOPAUSE_CONSULTATION'  => 'book-menopause-consultation',
		'BOOK_TESTOSTERONE_CONSULTATION' => 'book-testosterone-consultation',
		'BOOK_WELCOME_CONSULTATION'    => 'book-welcome-consultation',
		'COMPREHENSIVE_DIAGNOSTICS'    => 'comprehensive-diagnostics',
		'MEMBERSHIP_YEARLY'            => 'membership-yearly',
		'MEMBERSHIP_MONTHLY'           => 'membership-monthly',
	);

	/**
	 * Assessment-specific page mappings
	 * These are dynamically generated based on assessment type
	 */
	const ASSESSMENT_PAGE_SUFFIXES = array(
		'DETAILS'      => '-assessment-details',
		'CONSULTATION' => '-consultation',
	);

	/**
	 * Button text constants
	 */
	const BUTTON_TEXT = array(
		'START_ASSESSMENT'             => 'Start Assessment',
		'RETAKE_ASSESSMENT'            => 'Retake Assessment',
		'TAKE_ASSESSMENTS'             => 'Take Assessments',
		'BOOK_CONSULTATION'            => 'Book Consultation',
		'SPEAK_WITH_EXPERT'           => 'Speak with Expert',
		'VIEW_DETAILS'                 => 'View Details',
		'BACK_TO_DASHBOARD'            => 'Back to Dashboard',
		'CONTACT_SUPPORT'              => 'Contact Support',
		'SAVE_GOALS'                   => 'Save Goals',
		'EDIT_GOALS'                   => 'Edit Goals',
		'UPDATE_SYMPTOMS'              => 'Update Symptoms',
		'CLEAR_HISTORY'                => 'Clear History',
		'EXTRACT_FROM_ASSESSMENTS'     => 'Extract from Assessments',
	);

	/**
	 * Link text constants
	 */
	const LINK_TEXT = array(
		'LEARN_MORE'                   => 'Learn More',
		'VIEW_ALL_ASSESSMENTS'         => 'View All Assessments',
		'BOOK_APPOINTMENT'             => 'Book Appointment',
		'CONTACT_US'                   => 'Contact Us',
		'SUPPORT'                      => 'Support',
		'PRIVACY_POLICY'               => 'Privacy Policy',
		'TERMS_OF_SERVICE'             => 'Terms of Service',
	);

	/**
	 * Tab constants
	 */
	const TAB_IDS = array(
		'MY_ASSESSMENTS'               => 'tab-my-assessments',
		'MY_BIOMARKERS'                => 'tab-my-biomarkers',
		'MY_SYMPTOMS'                  => 'tab-my-symptoms',
		'MY_INSIGHTS'                  => 'tab-my-insights',
		'MY_STORY'                     => 'tab-my-story',
		'PDF_UPLOAD'                   => 'tab-pdf-upload',
	);

	/**
	 * Get page type by constant
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant name.
	 * @return string|null The page type or null if not found.
	 */
	public static function get_page_type( $constant ) {
		$page_key = self::PAGE_TYPES[ $constant ] ?? null;
		if ( ! $page_key ) {
			return null;
		}
		
		// Get the actual page ID from admin settings (selected pages, not created pages)
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		$page_id = $page_mappings[ $page_key ] ?? null;
		
		if ( $page_id ) {
			return "page_id={$page_id}";
		}
		
		// Fallback to the page key if no mapping found
		return $page_key;
	}

	/**
	 * Get assessment-specific page URL
	 *
	 * @since 64.31.0
	 *
	 * @param string $assessment_type The assessment type (e.g., 'hair', 'health').
	 * @param string $page_type The page type ('DETAILS' or 'CONSULTATION').
	 * @return string|null The page URL or null if not found.
	 */
	public static function get_assessment_page_url( $assessment_type, $page_type ) {
		$suffix = self::ASSESSMENT_PAGE_SUFFIXES[ $page_type ] ?? null;
		if ( ! $suffix ) {
			return null;
		}
		
		$page_key = $assessment_type . $suffix;
		// Get the actual page ID from admin settings (selected pages, not created pages)
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		$page_id = $page_mappings[ $page_key ] ?? null;
		
		if ( $page_id ) {
			return "page_id={$page_id}";
		}
		
		// Fallback to generic assessment page
		return self::get_page_type( 'ASSESSMENTS' );
	}

	/**
	 * Get button text by constant
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant name.
	 * @return string|null The button text or null if not found.
	 */
	public static function get_button_text( $constant ) {
		return self::BUTTON_TEXT[ $constant ] ?? null;
	}

	/**
	 * Get link text by constant
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant name.
	 * @return string|null The link text or null if not found.
	 */
	public static function get_link_text( $constant ) {
		return self::LINK_TEXT[ $constant ] ?? null;
	}

	/**
	 * Get tab ID by constant
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant name.
	 * @return string|null The tab ID or null if not found.
	 */
	public static function get_tab_id( $constant ) {
		return self::TAB_IDS[ $constant ] ?? null;
	}

	/**
	 * Get all page types
	 *
	 * @since 64.29.0
	 *
	 * @return array All page types.
	 */
	public static function get_all_page_types() {
		return self::PAGE_TYPES;
	}

	/**
	 * Get all button texts
	 *
	 * @since 64.29.0
	 *
	 * @return array All button texts.
	 */
	public static function get_all_button_texts() {
		return self::BUTTON_TEXT;
	}

	/**
	 * Get all link texts
	 *
	 * @since 64.29.0
	 *
	 * @return array All link texts.
	 */
	public static function get_all_link_texts() {
		return self::LINK_TEXT;
	}

	/**
	 * Get all tab IDs
	 *
	 * @since 64.29.0
	 *
	 * @return array All tab IDs.
	 */
	public static function get_all_tab_ids() {
		return self::TAB_IDS;
	}

	/**
	 * Validate if a page type exists
	 *
	 * @since 64.29.0
	 *
	 * @param string $page_type The page type to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_page_type( $page_type ) {
		return in_array( $page_type, self::PAGE_TYPES, true );
	}

	/**
	 * Validate if a button text constant exists
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_button_text_constant( $constant ) {
		return array_key_exists( $constant, self::BUTTON_TEXT );
	}

	/**
	 * Validate if a link text constant exists
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_link_text_constant( $constant ) {
		return array_key_exists( $constant, self::LINK_TEXT );
	}

	/**
	 * Validate if a tab ID constant exists
	 *
	 * @since 64.29.0
	 *
	 * @param string $constant The constant to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_tab_id_constant( $constant ) {
		return array_key_exists( $constant, self::TAB_IDS );
	}

}
