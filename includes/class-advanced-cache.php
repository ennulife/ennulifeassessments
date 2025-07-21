<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Advanced_Cache {
	private $cache_prefix       = 'ennu_';
	private $default_expiration = 3600;
	private $cache_groups       = array(
		'scores'       => 1800,
		'assessments'  => 3600,
		'biomarkers'   => 7200,
		'health_goals' => 1800,
		'user_data'    => 900,
	);

	public function __construct() {
		add_action( 'init', array( $this, 'init_cache_hooks' ) );
	}

	public function init_cache_hooks() {
		add_action( 'ennu_scores_updated', array( $this, 'invalidate_user_scores_cache' ) );
		add_action( 'ennu_health_goals_updated', array( $this, 'invalidate_health_goals_cache' ) );
		add_action( 'ennu_biomarkers_updated', array( $this, 'invalidate_biomarkers_cache' ) );
	}

	public function get( $key, $group = 'default' ) {
		$cache_key = $this->get_cache_key( $key, $group );

		if ( function_exists( 'wp_cache_get' ) ) {
			$cached = wp_cache_get( $cache_key, $group );
			if ( $cached !== false ) {
				return $cached;
			}
		}

		return get_transient( $cache_key );
	}

	public function set( $key, $data, $group = 'default', $expiration = null ) {
		$cache_key  = $this->get_cache_key( $key, $group );
		$expiration = $expiration ?: $this->get_group_expiration( $group );

		if ( function_exists( 'wp_cache_set' ) ) {
			wp_cache_set( $cache_key, $data, $group, $expiration );
		}

		set_transient( $cache_key, $data, $expiration );

		return true;
	}

	public function delete( $key, $group = 'default' ) {
		$cache_key = $this->get_cache_key( $key, $group );

		if ( function_exists( 'wp_cache_delete' ) ) {
			wp_cache_delete( $cache_key, $group );
		}

		delete_transient( $cache_key );

		return true;
	}

	public function flush_group( $group ) {
		if ( function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( $group );
		}

		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_' . $this->cache_prefix . $group . '_%'
			)
		);

		return true;
	}

	public function get_cache_stats() {
		$stats = array(
			'cache_hits'   => 0,
			'cache_misses' => 0,
			'cache_groups' => $this->cache_groups,
		);

		if ( function_exists( 'wp_cache_get_stats' ) ) {
			$wp_stats = wp_cache_get_stats();
			$stats    = array_merge( $stats, $wp_stats );
		}

		return $stats;
	}

	public function warm_cache( $user_id ) {
		$scoring_system = new ENNU_Assessment_Scoring();

		$user_scores = $scoring_system->get_user_scores( $user_id );
		$this->set( "user_scores_{$user_id}", $user_scores, 'scores' );

		$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		$this->set( "health_goals_{$user_id}", $health_goals, 'health_goals' );

		$biomarkers = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$this->set( "biomarkers_{$user_id}", $biomarkers, 'biomarkers' );

		return true;
	}

	public function invalidate_user_scores_cache( $user_id ) {
		$this->delete( "user_scores_{$user_id}", 'scores' );
		$this->delete( "pillar_scores_{$user_id}", 'scores' );
		$this->delete( "ennu_life_score_{$user_id}", 'scores' );
	}

	public function invalidate_health_goals_cache( $user_id ) {
		$this->delete( "health_goals_{$user_id}", 'health_goals' );
	}

	public function invalidate_biomarkers_cache( $user_id ) {
		$this->delete( "biomarkers_{$user_id}", 'biomarkers' );
	}

	private function get_cache_key( $key, $group ) {
		return $this->cache_prefix . $group . '_' . $key;
	}

	private function get_group_expiration( $group ) {
		return isset( $this->cache_groups[ $group ] ) ? $this->cache_groups[ $group ] : $this->default_expiration;
	}
}

new ENNU_Advanced_Cache();
