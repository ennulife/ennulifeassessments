<?php
/**
 * Redis Cache Manager
 *
 * Advanced caching system with Redis/Memcached integration for high-performance
 * data caching, session management, and cache warming strategies.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Redis_Cache_Manager {

	private $redis_client;
	private $memcached_client;
	private $cache_prefix;
	private $default_ttl;
	private $cache_stats;
	private $cache_groups;

	public function __construct() {
		$this->cache_prefix = 'ennu_' . get_current_blog_id() . '_';
		$this->default_ttl  = 3600; // 1 hour
		$this->cache_stats  = array();
		$this->init_cache_groups();
		$this->init_cache_clients();
		$this->setup_hooks();
	}

	/**
	 * Initialize cache groups with different TTL settings
	 */
	private function init_cache_groups() {
		$this->cache_groups = array(
			'user_scores'     => array(
				'ttl'  => 1800, // 30 minutes
				'tags' => array( 'user_data', 'scores' ),
			),
			'assessment_data' => array(
				'ttl'  => 3600, // 1 hour
				'tags' => array( 'assessments', 'user_data' ),
			),
			'biomarker_data'  => array(
				'ttl'  => 7200, // 2 hours
				'tags' => array( 'biomarkers', 'user_data' ),
			),
			'health_goals'    => array(
				'ttl'  => 1800, // 30 minutes
				'tags' => array( 'goals', 'user_data' ),
			),
			'system_config'   => array(
				'ttl'  => 86400, // 24 hours
				'tags' => array( 'config', 'system' ),
			),
			'api_responses'   => array(
				'ttl'  => 900, // 15 minutes
				'tags' => array( 'api', 'external' ),
			),
			'page_cache'      => array(
				'ttl'  => 1800, // 30 minutes
				'tags' => array( 'pages', 'frontend' ),
			),
			'query_cache'     => array(
				'ttl'  => 600, // 10 minutes
				'tags' => array( 'database', 'queries' ),
			),
		);
	}

	/**
	 * Initialize cache clients
	 */
	private function init_cache_clients() {
		if ( class_exists( 'Redis' ) && $this->is_redis_available() ) {
			$this->redis_client = new Redis();
			try {
				$redis_host     = defined( 'ENNU_REDIS_HOST' ) ? ENNU_REDIS_HOST : '127.0.0.1';
				$redis_port     = defined( 'ENNU_REDIS_PORT' ) ? ENNU_REDIS_PORT : 6379;
				$redis_password = defined( 'ENNU_REDIS_PASSWORD' ) ? ENNU_REDIS_PASSWORD : null;

				$this->redis_client->connect( $redis_host, $redis_port );

				if ( $redis_password ) {
					$this->redis_client->auth( $redis_password );
				}

				$this->redis_client->select( defined( 'ENNU_REDIS_DB' ) ? ENNU_REDIS_DB : 0 );

				$this->redis_client->ping();

			} catch ( Exception $e ) {
				error_log( 'ENNU Redis connection failed: ' . $e->getMessage() );
				$this->redis_client = null;
			}
		}

		if ( ! $this->redis_client && class_exists( 'Memcached' ) && $this->is_memcached_available() ) {
			$this->memcached_client = new Memcached();
			try {
				$memcached_host = defined( 'ENNU_MEMCACHED_HOST' ) ? ENNU_MEMCACHED_HOST : '127.0.0.1';
				$memcached_port = defined( 'ENNU_MEMCACHED_PORT' ) ? ENNU_MEMCACHED_PORT : 11211;

				$this->memcached_client->addServer( $memcached_host, $memcached_port );

				$this->memcached_client->getVersion();

			} catch ( Exception $e ) {
				error_log( 'ENNU Memcached connection failed: ' . $e->getMessage() );
				$this->memcached_client = null;
			}
		}
	}

	/**
	 * Setup WordPress hooks
	 */
	private function setup_hooks() {
		add_action( 'init', array( $this, 'init_cache_warming' ) );
		add_action( 'wp_loaded', array( $this, 'start_cache_monitoring' ) );
		add_action( 'ennu_cache_warm', array( $this, 'warm_cache' ) );
		add_action( 'ennu_cache_cleanup', array( $this, 'cleanup_expired_cache' ) );

		add_action( 'ennu_user_scores_updated', array( $this, 'invalidate_user_cache' ), 10, 1 );
		add_action( 'ennu_assessment_submitted', array( $this, 'invalidate_assessment_cache' ), 10, 1 );
		add_action( 'ennu_biomarkers_updated', array( $this, 'invalidate_biomarker_cache' ), 10, 1 );
		add_action( 'ennu_health_goals_updated', array( $this, 'invalidate_health_goals_cache' ), 10, 1 );

		if ( ! wp_next_scheduled( 'ennu_cache_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'ennu_cache_cleanup' );
		}

		if ( ! wp_next_scheduled( 'ennu_cache_warm' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'ennu_cache_warm' );
		}
	}

	/**
	 * Get cached data
	 */
	public function get( $key, $group = 'default' ) {
		$cache_key  = $this->build_cache_key( $key, $group );
		$start_time = microtime( true );

		$data = null;
		$hit  = false;

		if ( $this->redis_client ) {
			try {
				$cached_data = $this->redis_client->get( $cache_key );
				if ( $cached_data !== false ) {
					$data = maybe_unserialize( $cached_data );
					$hit  = true;
				}
			} catch ( Exception $e ) {
				error_log( 'Redis get error: ' . $e->getMessage() );
			}
		}

		if ( ! $hit && $this->memcached_client ) {
			try {
				$cached_data = $this->memcached_client->get( $cache_key );
				if ( $cached_data !== false ) {
					$data = $cached_data;
					$hit  = true;
				}
			} catch ( Exception $e ) {
				error_log( 'Memcached get error: ' . $e->getMessage() );
			}
		}

		if ( ! $hit ) {
			$data = get_transient( $cache_key );
			$hit  = ( $data !== false );
		}

		$this->record_cache_stat( $group, $hit ? 'hit' : 'miss', microtime( true ) - $start_time );

		return $data;
	}

	/**
	 * Set cached data
	 */
	public function set( $key, $data, $group = 'default', $ttl = null ) {
		$cache_key = $this->build_cache_key( $key, $group );
		$ttl       = $ttl ?: $this->get_group_ttl( $group );

		$success = false;

		if ( $this->redis_client ) {
			try {
				$serialized_data = maybe_serialize( $data );
				$success         = $this->redis_client->setex( $cache_key, $ttl, $serialized_data );

				$this->add_cache_tags( $cache_key, $group );

			} catch ( Exception $e ) {
				error_log( 'Redis set error: ' . $e->getMessage() );
			}
		}

		if ( $this->memcached_client ) {
			try {
				$this->memcached_client->set( $cache_key, $data, $ttl );
				$success = true;
			} catch ( Exception $e ) {
				error_log( 'Memcached set error: ' . $e->getMessage() );
			}
		}

		set_transient( $cache_key, $data, $ttl );

		return $success;
	}

	/**
	 * Delete cached data
	 */
	public function delete( $key, $group = 'default' ) {
		$cache_key = $this->build_cache_key( $key, $group );

		if ( $this->redis_client ) {
			try {
				$this->redis_client->del( $cache_key );
				$this->remove_cache_tags( $cache_key, $group );
			} catch ( Exception $e ) {
				error_log( 'Redis delete error: ' . $e->getMessage() );
			}
		}

		if ( $this->memcached_client ) {
			try {
				$this->memcached_client->delete( $cache_key );
			} catch ( Exception $e ) {
				error_log( 'Memcached delete error: ' . $e->getMessage() );
			}
		}

		delete_transient( $cache_key );

		return true;
	}

	/**
	 * Flush cache by group or tags
	 */
	public function flush( $group = null, $tags = null ) {
		if ( $group ) {
			$this->flush_group( $group );
		} elseif ( $tags ) {
			$this->flush_by_tags( $tags );
		} else {
			$this->flush_all();
		}
	}

	/**
	 * Flush cache group
	 */
	private function flush_group( $group ) {
		$pattern = $this->cache_prefix . $group . '_*';

		if ( $this->redis_client ) {
			try {
				$keys = $this->redis_client->keys( $pattern );
				if ( ! empty( $keys ) ) {
					$this->redis_client->del( $keys );
				}
			} catch ( Exception $e ) {
				error_log( 'Redis flush group error: ' . $e->getMessage() );
			}
		}

		global $wpdb;
		$wpdb->query(
			$wpdb->prepare(
				"
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE %s 
            OR option_name LIKE %s
        ",
				'_transient_' . $pattern,
				'_transient_timeout_' . $pattern
			)
		);
	}

	/**
	 * Flush cache by tags
	 */
	private function flush_by_tags( $tags ) {
		if ( ! is_array( $tags ) ) {
			$tags = array( $tags );
		}

		if ( $this->redis_client ) {
			try {
				foreach ( $tags as $tag ) {
					$tag_key = $this->cache_prefix . 'tag_' . $tag;
					$keys    = $this->redis_client->smembers( $tag_key );

					if ( ! empty( $keys ) ) {
						$this->redis_client->del( $keys );
						$this->redis_client->del( $tag_key );
					}
				}
			} catch ( Exception $e ) {
				error_log( 'Redis flush by tags error: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Flush all cache
	 */
	private function flush_all() {
		if ( $this->redis_client ) {
			try {
				$this->redis_client->flushDB();
			} catch ( Exception $e ) {
				error_log( 'Redis flush all error: ' . $e->getMessage() );
			}
		}

		if ( $this->memcached_client ) {
			try {
				$this->memcached_client->flush();
			} catch ( Exception $e ) {
				error_log( 'Memcached flush all error: ' . $e->getMessage() );
			}
		}

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$this->cache_prefix}%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_{$this->cache_prefix}%'" );
	}

	/**
	 * Cache warming for frequently accessed data
	 */
	public function warm_cache() {
		$this->warm_user_scores_cache();

		$this->warm_system_config_cache();

		$this->warm_assessment_cache();

		$this->warm_biomarker_cache();
	}

	/**
	 * Warm user scores cache
	 */
	private function warm_user_scores_cache() {
		$active_users = get_users(
			array(
				'meta_key'     => 'last_activity',
				'meta_value'   => date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ),
				'meta_compare' => '>',
				'number'       => 100, // Limit to prevent memory issues
			)
		);

		foreach ( $active_users as $user ) {
			$cache_key = "user_scores_{$user->ID}";

			if ( ! $this->get( $cache_key, 'user_scores' ) ) {
				if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
					$scoring_system = new ENNU_Assessment_Scoring();
					$scores         = $scoring_system->get_user_scores( $user->ID );

					if ( $scores ) {
						$this->set( $cache_key, $scores, 'user_scores' );
					}
				}
			}
		}
	}

	/**
	 * Warm system configuration cache
	 */
	private function warm_system_config_cache() {
		$config_keys = array(
			'pillar_map',
			'health_goals',
			'biomarker_profiles',
			'symptom_map',
			'penalty_matrix',
		);

		foreach ( $config_keys as $config_key ) {
			if ( ! $this->get( $config_key, 'system_config' ) ) {
				$config_file = ENNU_PLUGIN_DIR . "includes/config/{$config_key}.php";
				if ( file_exists( $config_file ) ) {
					$config_data = include $config_file;
					$this->set( $config_key, $config_data, 'system_config' );
				}
			}
		}
	}

	/**
	 * Warm assessment cache
	 */
	private function warm_assessment_cache() {
		$assessment_types = array(
			'welcome',
			'hair',
			'health',
			'weight_loss',
			'skin',
			'sleep',
			'hormone',
			'menopause',
			'testosterone',
			'ed_treatment',
			'health_optimization',
		);

		foreach ( $assessment_types as $type ) {
			$cache_key = "assessment_config_{$type}";

			if ( ! $this->get( $cache_key, 'assessment_data' ) ) {
				$config = $this->load_assessment_config( $type );
				if ( $config ) {
					$this->set( $cache_key, $config, 'assessment_data' );
				}
			}
		}
	}

	/**
	 * Warm biomarker cache
	 */
	private function warm_biomarker_cache() {
		$cache_key = 'biomarker_profiles';

		if ( ! $this->get( $cache_key, 'biomarker_data' ) ) {
			// Use the new orchestrator instead of old config file
			$manager = new ENNU_Recommended_Range_Manager();
			$biomarker_data = $manager->get_biomarker_configuration();
			if ( is_array( $biomarker_data ) ) {
				$this->set( $cache_key, $biomarker_data, 'biomarker_data' );
			}
		}
	}

	/**
	 * Load assessment configuration
	 */
	private function load_assessment_config( $type ) {
		return array(
			'type'      => $type,
			'questions' => array(),
			'scoring'   => array(),
			'loaded_at' => time(),
		);
	}

	/**
	 * Cache invalidation methods
	 */
	public function invalidate_user_cache( $user_id ) {
		$this->delete( "user_scores_{$user_id}", 'user_scores' );
		$this->delete( "user_assessments_{$user_id}", 'assessment_data' );
		$this->delete( "user_biomarkers_{$user_id}", 'biomarker_data' );
		$this->delete( "user_health_goals_{$user_id}", 'health_goals' );
	}

	public function invalidate_assessment_cache( $user_id ) {
		$this->delete( "user_assessments_{$user_id}", 'assessment_data' );
		$this->delete( "user_scores_{$user_id}", 'user_scores' );
	}

	public function invalidate_biomarker_cache( $user_id ) {
		$this->delete( "user_biomarkers_{$user_id}", 'biomarker_data' );
		$this->delete( "user_scores_{$user_id}", 'user_scores' );
	}

	public function invalidate_health_goals_cache( $user_id ) {
		$this->delete( "user_health_goals_{$user_id}", 'health_goals' );
		$this->delete( "user_scores_{$user_id}", 'user_scores' );
	}

	/**
	 * Helper methods
	 */
	private function build_cache_key( $key, $group ) {
		return $this->cache_prefix . $group . '_' . $key;
	}

	private function get_group_ttl( $group ) {
		return $this->cache_groups[ $group ]['ttl'] ?? $this->default_ttl;
	}

	private function add_cache_tags( $cache_key, $group ) {
		if ( ! $this->redis_client || ! isset( $this->cache_groups[ $group ]['tags'] ) ) {
			return;
		}

		try {
			foreach ( $this->cache_groups[ $group ]['tags'] as $tag ) {
				$tag_key = $this->cache_prefix . 'tag_' . $tag;
				$this->redis_client->sadd( $tag_key, $cache_key );
			}
		} catch ( Exception $e ) {
			error_log( 'Redis add cache tags error: ' . $e->getMessage() );
		}
	}

	private function remove_cache_tags( $cache_key, $group ) {
		if ( ! $this->redis_client || ! isset( $this->cache_groups[ $group ]['tags'] ) ) {
			return;
		}

		try {
			foreach ( $this->cache_groups[ $group ]['tags'] as $tag ) {
				$tag_key = $this->cache_prefix . 'tag_' . $tag;
				$this->redis_client->srem( $tag_key, $cache_key );
			}
		} catch ( Exception $e ) {
			error_log( 'Redis remove cache tags error: ' . $e->getMessage() );
		}
	}

	private function record_cache_stat( $group, $type, $time ) {
		if ( ! isset( $this->cache_stats[ $group ] ) ) {
			$this->cache_stats[ $group ] = array(
				'hits'       => 0,
				'misses'     => 0,
				'total_time' => 0,
			);
		}

		$this->cache_stats[ $group ][ $type === 'hit' ? 'hits' : 'misses' ]++;
		$this->cache_stats[ $group ]['total_time'] += $time;
	}

	private function is_redis_available() {
		return class_exists( 'Redis' ) && ! defined( 'ENNU_DISABLE_REDIS' );
	}

	private function is_memcached_available() {
		return class_exists( 'Memcached' ) && ! defined( 'ENNU_DISABLE_MEMCACHED' );
	}

	/**
	 * Get cache statistics
	 */
	public function get_cache_stats() {
		$stats = $this->cache_stats;

		foreach ( $stats as $group => &$group_stats ) {
			$total                   = $group_stats['hits'] + $group_stats['misses'];
			$group_stats['hit_rate'] = $total > 0 ? ( $group_stats['hits'] / $total ) * 100 : 0;
			$group_stats['avg_time'] = $total > 0 ? $group_stats['total_time'] / $total : 0;
		}

		return $stats;
	}

	/**
	 * Get cache info
	 */
	public function get_cache_info() {
		$info = array(
			'redis_available'     => (bool) $this->redis_client,
			'memcached_available' => (bool) $this->memcached_client,
			'active_client'       => 'transients',
		);

		if ( $this->redis_client ) {
			$info['active_client'] = 'redis';
			try {
				$info['redis_info'] = $this->redis_client->info();
			} catch ( Exception $e ) {
				$info['redis_error'] = $e->getMessage();
			}
		} elseif ( $this->memcached_client ) {
			$info['active_client'] = 'memcached';
			try {
				$info['memcached_stats'] = $this->memcached_client->getStats();
			} catch ( Exception $e ) {
				$info['memcached_error'] = $e->getMessage();
			}
		}

		return $info;
	}

	/**
	 * Cleanup expired cache entries
	 */
	public function cleanup_expired_cache() {

		global $wpdb;
		$wpdb->query(
			"
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_timeout_{$this->cache_prefix}%' 
            AND option_value < UNIX_TIMESTAMP()
        "
		);

		$wpdb->query(
			"
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_{$this->cache_prefix}%' 
            AND option_name NOT IN (
                SELECT REPLACE(option_name, '_transient_timeout_', '_transient_') 
                FROM {$wpdb->options} 
                WHERE option_name LIKE '_transient_timeout_{$this->cache_prefix}%'
            )
        "
		);
	}

	/**
	 * Initialize cache warming
	 */
	public function init_cache_warming() {
		if ( ! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron() ) {
			wp_schedule_single_event( time() + 60, 'ennu_cache_warm' );
		}
	}

	/**
	 * Start cache monitoring
	 */
	public function start_cache_monitoring() {
		add_action( 'shutdown', array( $this, 'record_cache_usage' ) );
	}

	/**
	 * Record cache usage for monitoring
	 */
	public function record_cache_usage() {
		$stats = $this->get_cache_stats();

		$today       = date( 'Y-m-d' );
		$daily_stats = get_option( "ennu_cache_stats_{$today}", array() );

		foreach ( $stats as $group => $group_stats ) {
			if ( ! isset( $daily_stats[ $group ] ) ) {
				$daily_stats[ $group ] = array(
					'hits'       => 0,
					'misses'     => 0,
					'total_time' => 0,
				);
			}

			$daily_stats[ $group ]['hits']       += $group_stats['hits'];
			$daily_stats[ $group ]['misses']     += $group_stats['misses'];
			$daily_stats[ $group ]['total_time'] += $group_stats['total_time'];
		}

		update_option( "ennu_cache_stats_{$today}", $daily_stats );
	}
}

if ( class_exists( 'ENNU_Redis_Cache_Manager' ) ) {
	global $ennu_cache;
	$ennu_cache = new ENNU_Redis_Cache_Manager();
}
