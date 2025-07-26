<?php
/**
 * Uninstall Script for ENNU Life Assessments
 *
 * This file is executed when the plugin is uninstalled via WordPress admin.
 * It removes all plugin data, options, and user meta created by the plugin.
 *
 * @package ENNU_Life_Assessments
 * @version 62.2.9
 * @since 62.2.9
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

class ENNU_Uninstaller {

	public static function uninstall() {
		global $wpdb;

		self::remove_plugin_options();
		self::remove_user_meta();
		self::remove_custom_tables();
		self::clear_transients();
		self::remove_capabilities();
	}

	private static function remove_plugin_options() {
		$options_to_remove = array(
			'ennu_life_version',
			'ennu_life_settings',
			'ennu_assessment_settings',
			'ennu_scoring_settings',
			'ennu_biomarker_settings',
			'ennu_health_goals_settings',
			'ennu_plugin_activated',
			'ennu_database_version',
			'ennu_cache_settings',
		);

		foreach ( $options_to_remove as $option ) {
			delete_option( $option );
			delete_site_option( $option );
		}
	}

	private static function remove_user_meta() {
		global $wpdb;

		$meta_keys = array(
			'ennu_life_score',
			'ennu_mind_pillar_score',
			'ennu_body_pillar_score',
			'ennu_lifestyle_pillar_score',
			'ennu_aesthetics_pillar_score',
			'ennu_assessment_data',
			'ennu_health_goals',
			'ennu_biomarker_data',
			'ennu_symptom_data',
			'ennu_user_preferences',
			'ennu_dashboard_settings',
		);

		foreach ( $meta_keys as $meta_key ) {
			$wpdb->delete(
				$wpdb->usermeta,
				array( 'meta_key' => $meta_key ),
				array( '%s' )
			);
		}
	}

	private static function remove_custom_tables() {
		global $wpdb;

		$tables_to_remove = array(
			$wpdb->prefix . 'ennu_assessments',
			$wpdb->prefix . 'ennu_biomarkers',
			$wpdb->prefix . 'ennu_symptoms',
			$wpdb->prefix . 'ennu_health_goals',
			$wpdb->prefix . 'ennu_user_scores',
		);

		foreach ( $tables_to_remove as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}
	}

	private static function clear_transients() {
		global $wpdb;

		$wpdb->query(
			"DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_ennu_%' 
             OR option_name LIKE '_transient_timeout_ennu_%'"
		);

		if ( is_multisite() ) {
			$wpdb->query(
				"DELETE FROM {$wpdb->sitemeta} 
                 WHERE meta_key LIKE '_site_transient_ennu_%' 
                 OR meta_key LIKE '_site_transient_timeout_ennu_%'"
			);
		}
	}

	private static function remove_capabilities() {
		$roles = array( 'administrator', 'editor', 'author' );

		foreach ( $roles as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				$role->remove_cap( 'manage_ennu_assessments' );
				$role->remove_cap( 'view_ennu_reports' );
				$role->remove_cap( 'edit_ennu_settings' );
			}
		}
	}
}

ENNU_Uninstaller::uninstall();
