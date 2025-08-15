<?php
/**
 * ENNU Analytics Service
 * Extracted from monolithic Enhanced Admin class
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Analytics_Service {

	private $cache;

	public function __construct() {
		$this->cache = new ENNU_Score_Cache();
	}

	/**
	 * Get comprehensive system statistics
	 */
	public function get_system_stats() {
		$cache_key = 'ennu_system_stats';
		$cached    = $this->cache->get( $cache_key );

		if ( $cached !== false ) {
			return $cached;
		}

		$stats = array(
			'total_users'               => $this->get_total_users(),
			'active_users'              => $this->get_active_users(),
			'monthly_assessments'       => $this->get_monthly_assessments(),
			'completion_rates'          => $this->get_completion_rates(),
			'popular_assessments'       => $this->get_popular_assessments(),
			'health_goals_distribution' => $this->get_health_goals_distribution(),
			'performance_metrics'       => $this->get_performance_metrics(),
		);

		$this->cache->set( $cache_key, $stats, 900 );
		return $stats;
	}

	/**
	 * Get total registered users
	 */
	private function get_total_users() {
		return (int) count_users()['total_users'];
	}

	/**
	 * Get active users (with at least one assessment)
	 */
	private function get_active_users() {
		global $wpdb;

		$meta_keys = array(
			'ennu_welcome_calculated_score',
			'ennu_hair_calculated_score',
			'ennu_health_calculated_score',
			'ennu_skin_calculated_score',
			'ennu_sleep_calculated_score',
			'ennu_hormone_calculated_score',
			'ennu_testosterone_calculated_score',
			'ennu_menopause_calculated_score',
			'ennu_weight_loss_calculated_score',
			'ennu_peptide-therapy_calculated_score',
		);

		$placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );
		$query        = $wpdb->prepare(
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
             WHERE meta_key IN ($placeholders) AND meta_value != ''",
			$meta_keys
		);

		return (int) $wpdb->get_var( $query );
	}

	/**
	 * Get monthly assessment count
	 */
	private function get_monthly_assessments() {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE %s AND meta_value != '' 
             AND CAST(meta_value AS SIGNED) > 0 
             AND user_id IN (
                 SELECT ID FROM {$wpdb->users} 
                 WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
             )",
				'ennu_%_calculated_score'
			)
		);
	}

	/**
	 * Get completion rates by assessment type
	 */
	private function get_completion_rates() {
		$assessments = array( 'welcome', 'hair', 'health', 'skin', 'sleep', 'hormone', 'menopause', 'testosterone', 'weight_loss', 'health_optimization', 'peptide-therapy' );
		$rates       = array();

		foreach ( $assessments as $assessment ) {
			$rates[ $assessment ] = $this->get_assessment_completion_rate( $assessment );
		}

		return $rates;
	}

	/**
	 * Get completion rate for specific assessment
	 */
	private function get_assessment_completion_rate( $assessment_type ) {
		global $wpdb;

		$total_users = $this->get_total_users();
		$completed   = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key = %s AND meta_value != ''",
				'ennu_' . $assessment_type . '_calculated_score'
			)
		);

		return $total_users > 0 ? round( ( $completed / $total_users ) * 100, 1 ) : 0;
	}

	/**
	 * Get most popular assessments
	 */
	private function get_popular_assessments() {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT 
                REPLACE(REPLACE(meta_key, 'ennu_', ''), '_calculated_score', '') as assessment_type,
                COUNT(*) as count
             FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE %s AND meta_value != ''
             GROUP BY meta_key 
             ORDER BY count DESC 
             LIMIT 5",
				'ennu_%_calculated_score'
			)
		);

		return $results;
	}

	/**
	 * Get health goals distribution
	 */
	private function get_health_goals_distribution() {
		global $wpdb;

		$users_with_goals = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->usermeta} 
             WHERE meta_key = %s AND meta_value != '' AND meta_value != 'a:0:{}'",
				'ennu_global_health_goals'
			)
		);

		$distribution = array();
		foreach ( $users_with_goals as $user_meta ) {
			$goals = maybe_unserialize( $user_meta->meta_value );
			if ( is_array( $goals ) ) {
				foreach ( $goals as $goal ) {
					$distribution[ $goal ] = isset( $distribution[ $goal ] ) ? $distribution[ $goal ] + 1 : 1;
				}
			}
		}

		arsort( $distribution );
		return $distribution;
	}

	/**
	 * Get performance metrics
	 */
	private function get_performance_metrics() {
		return array(
			'cache_hit_rate'    => $this->cache->get_hit_rate(),
			'average_load_time' => $this->get_average_load_time(),
			'database_queries'  => $this->get_query_count(),
			'memory_usage'      => $this->get_memory_usage(),
		);
	}

	/**
	 * Get average page load time
	 */
	private function get_average_load_time() {
		return get_option( 'ennu_avg_load_time', 0 );
	}

	/**
	 * Get database query count
	 */
	private function get_query_count() {
		global $wpdb;
		return $wpdb->num_queries;
	}

	/**
	 * Get current memory usage
	 */
	private function get_memory_usage() {
		return round( memory_get_usage( true ) / 1024 / 1024, 2 );
	}

	/**
	 * Generate analytics report
	 */
	public function generate_report( $date_range = '30 days' ) {
		$stats = $this->get_system_stats();

		return array(
			'generated_at'    => current_time( 'mysql' ),
			'date_range'      => $date_range,
			'summary'         => array(
				'total_users'         => $stats['total_users'],
				'active_users'        => $stats['active_users'],
				'engagement_rate'     => $stats['total_users'] > 0 ? round( ( $stats['active_users'] / $stats['total_users'] ) * 100, 1 ) : 0,
				'monthly_assessments' => $stats['monthly_assessments'],
			),
			'detailed_stats'  => $stats,
			'recommendations' => $this->get_recommendations( $stats ),
		);
	}

	/**
	 * Get recommendations based on analytics
	 */
	private function get_recommendations( $stats ) {
		$recommendations = array();

		$engagement_rate = $stats['total_users'] > 0 ? ( $stats['active_users'] / $stats['total_users'] ) * 100 : 0;

		if ( $engagement_rate < 50 ) {
			$recommendations[] = 'Consider improving user onboarding to increase engagement rate';
		}

		if ( $stats['monthly_assessments'] < $stats['active_users'] ) {
			$recommendations[] = 'Encourage users to complete more assessments with targeted campaigns';
		}

		if ( isset( $stats['performance_metrics']['average_load_time'] ) && $stats['performance_metrics']['average_load_time'] > 3 ) {
			$recommendations[] = 'Optimize page load times to improve user experience';
		}

		return $recommendations;
	}
}
