<?php
/**
 * Advanced Analytics & Reporting System
 *
 * Comprehensive analytics system with user behavior tracking, conversion analysis,
 * business intelligence features, A/B testing framework, and advanced reporting.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Advanced_Analytics {

	private static $instance = null;
	private $analytics_data  = array();
	private $session_id;
	private $user_id;
	private $tracking_enabled = true;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->session_id = $this->generate_session_id();
		$this->user_id    = get_current_user_id();
		$this->setup_hooks();
		$this->init_tracking();
	}

	/**
	 * Setup WordPress hooks for analytics tracking
	 */
	private function setup_hooks() {
		add_action( 'wp_head', array( $this, 'add_tracking_script' ) );
		add_action( 'wp_footer', array( $this, 'track_page_view' ) );
		add_action( 'wp_login', array( $this, 'track_user_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'track_user_logout' ) );
		add_action( 'user_register', array( $this, 'track_user_registration' ) );

		add_action( 'ennu_assessment_started', array( $this, 'track_assessment_start' ), 10, 2 );
		add_action( 'ennu_assessment_completed', array( $this, 'track_assessment_completion' ), 10, 3 );
		add_action( 'ennu_assessment_abandoned', array( $this, 'track_assessment_abandonment' ), 10, 2 );

		add_action( 'ennu_biomarker_updated', array( $this, 'track_biomarker_update' ), 10, 2 );
		add_action( 'ennu_health_goal_created', array( $this, 'track_health_goal_creation' ), 10, 2 );
		add_action( 'ennu_health_goal_completed', array( $this, 'track_health_goal_completion' ), 10, 2 );

		add_action( 'ennu_dashboard_viewed', array( $this, 'track_dashboard_view' ) );
		add_action( 'ennu_score_viewed', array( $this, 'track_score_view' ), 10, 2 );
		add_action( 'ennu_report_downloaded', array( $this, 'track_report_download' ), 10, 2 );

		add_action( 'wp_ajax_ennu_track_event', array( $this, 'handle_track_event' ) );
		add_action( 'wp_ajax_nopriv_ennu_track_event', array( $this, 'handle_track_event' ) );
		add_action( 'wp_ajax_ennu_get_analytics', array( $this, 'get_analytics_data' ) );

		add_action( 'admin_menu', array( $this, 'add_analytics_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		add_action( 'ennu_process_analytics_data', array( $this, 'process_analytics_data' ) );
		add_action( 'ennu_generate_analytics_reports', array( $this, 'generate_scheduled_reports' ) );

		add_action( 'ennu_cleanup_analytics_data', array( $this, 'cleanup_old_analytics_data' ) );
	}

	/**
	 * Initialize tracking system
	 */
	private function init_tracking() {
		$this->tracking_enabled = get_option( 'ennu_analytics_enabled', true );

		if ( ! wp_next_scheduled( 'ennu_process_analytics_data' ) ) {
			wp_schedule_event( time(), 'hourly', 'ennu_process_analytics_data' );
		}

		if ( ! wp_next_scheduled( 'ennu_generate_analytics_reports' ) ) {
			wp_schedule_event( time(), 'daily', 'ennu_generate_analytics_reports' );
		}

		if ( ! wp_next_scheduled( 'ennu_cleanup_analytics_data' ) ) {
			wp_schedule_event( time(), 'weekly', 'ennu_cleanup_analytics_data' );
		}

		$this->create_analytics_tables();
	}

	/**
	 * Create analytics database tables
	 */
	private function create_analytics_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$events_table = $wpdb->prefix . 'ennu_analytics_events';
		$events_sql   = "CREATE TABLE IF NOT EXISTS $events_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            event_type varchar(100) NOT NULL,
            event_category varchar(100) NOT NULL,
            event_action varchar(100) NOT NULL,
            event_label varchar(255) DEFAULT NULL,
            event_value decimal(10,2) DEFAULT NULL,
            page_url text DEFAULT NULL,
            referrer_url text DEFAULT NULL,
            user_agent text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            metadata longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY session_id (session_id),
            KEY user_id (user_id),
            KEY event_type (event_type),
            KEY timestamp (timestamp)
        ) $charset_collate;";

		$sessions_table = $wpdb->prefix . 'ennu_analytics_sessions';
		$sessions_sql   = "CREATE TABLE IF NOT EXISTS $sessions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            session_id varchar(255) NOT NULL UNIQUE,
            user_id bigint(20) DEFAULT NULL,
            start_time datetime DEFAULT CURRENT_TIMESTAMP,
            end_time datetime DEFAULT NULL,
            duration int(11) DEFAULT NULL,
            page_views int(11) DEFAULT 0,
            events_count int(11) DEFAULT 0,
            device_type varchar(50) DEFAULT NULL,
            browser varchar(100) DEFAULT NULL,
            os varchar(100) DEFAULT NULL,
            country varchar(100) DEFAULT NULL,
            city varchar(100) DEFAULT NULL,
            entry_page text DEFAULT NULL,
            exit_page text DEFAULT NULL,
            conversion_events int(11) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY session_id (session_id),
            KEY user_id (user_id),
            KEY start_time (start_time)
        ) $charset_collate;";

		$user_analytics_table = $wpdb->prefix . 'ennu_user_analytics';
		$user_analytics_sql   = "CREATE TABLE IF NOT EXISTS $user_analytics_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            total_sessions int(11) DEFAULT 0,
            total_page_views int(11) DEFAULT 0,
            total_time_spent int(11) DEFAULT 0,
            assessments_started int(11) DEFAULT 0,
            assessments_completed int(11) DEFAULT 0,
            assessments_abandoned int(11) DEFAULT 0,
            biomarkers_updated int(11) DEFAULT 0,
            health_goals_created int(11) DEFAULT 0,
            health_goals_completed int(11) DEFAULT 0,
            reports_downloaded int(11) DEFAULT 0,
            last_activity datetime DEFAULT NULL,
            engagement_score decimal(5,2) DEFAULT 0,
            conversion_rate decimal(5,2) DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id),
            KEY last_activity (last_activity),
            KEY engagement_score (engagement_score)
        ) $charset_collate;";

		$ab_testing_table = $wpdb->prefix . 'ennu_ab_testing';
		$ab_testing_sql   = "CREATE TABLE IF NOT EXISTS $ab_testing_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            test_name varchar(255) NOT NULL,
            variant varchar(100) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(255) DEFAULT NULL,
            assigned_at datetime DEFAULT CURRENT_TIMESTAMP,
            converted tinyint(1) DEFAULT 0,
            converted_at datetime DEFAULT NULL,
            conversion_value decimal(10,2) DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY test_name (test_name),
            KEY variant (variant),
            KEY user_id (user_id),
            KEY session_id (session_id),
            KEY assigned_at (assigned_at)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $events_sql );
		dbDelta( $sessions_sql );
		dbDelta( $user_analytics_sql );
		dbDelta( $ab_testing_sql );
	}

	/**
	 * Get overview metrics
	 */
	private function get_overview_metrics() {
		global $wpdb;

		$total_users  = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		$active_users = $wpdb->get_var(
			"
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_score' 
            AND meta_value != ''
        "
		);

		$total_assessments = $wpdb->get_var(
			"
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_assessment_completed'
        "
		);

		$avg_life_score = $wpdb->get_var(
			"
            SELECT AVG(CAST(meta_value AS DECIMAL(5,2))) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_life_score' 
            AND meta_value != ''
        "
		);

		return array(
			'total_users'       => intval( $total_users ),
			'active_users'      => intval( $active_users ),
			'total_assessments' => intval( $total_assessments ),
			'avg_life_score'    => round( floatval( $avg_life_score ), 1 ),
			'engagement_rate'   => $total_users > 0 ? round( ( $active_users / $total_users ) * 100, 1 ) : 0,
		);
	}

	/**
	 * Get assessment analytics
	 */
	private function get_assessment_analytics() {
		global $wpdb;

		$assessment_types = array(
			'welcome',
			'hair',
			'health',
			'skin',
			'sleep',
			'hormone',
			'menopause',
			'testosterone',
			'weight_loss',
			'ed_treatment',
			'health_optimization',
		);

		$completion_data = array();
		$score_data      = array();

		foreach ( $assessment_types as $type ) {
			$completions = $wpdb->get_var(
				$wpdb->prepare(
					"
                SELECT COUNT(*) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = %s
            ",
					"ennu_{$type}_assessment_completed"
				)
			);

			$avg_score = $wpdb->get_var(
				$wpdb->prepare(
					"
                SELECT AVG(CAST(meta_value AS DECIMAL(5,2))) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = %s 
                AND meta_value != ''
            ",
					"ennu_{$type}_score"
				)
			);

			$completion_data[ $type ] = intval( $completions );
			$score_data[ $type ]      = round( floatval( $avg_score ), 1 );
		}

		return array(
			'completions'     => $completion_data,
			'average_scores'  => $score_data,
			'most_popular'    => array_keys( $completion_data, max( $completion_data ) )[0] ?? 'welcome',
			'completion_rate' => $this->calculate_completion_rate(),
		);
	}

	/**
	 * Get user analytics
	 */
	private function get_user_analytics() {
		global $wpdb;

		$user_registrations = $wpdb->get_results(
			"
            SELECT DATE(user_registered) as date, COUNT(*) as count
            FROM {$wpdb->users}
            WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(user_registered)
            ORDER BY date DESC
        "
		);

		$gender_distribution = $wpdb->get_results(
			"
            SELECT meta_value as gender, COUNT(*) as count
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'ennu_global_gender'
            GROUP BY meta_value
        "
		);

		$age_groups = $this->get_age_group_distribution();

		return array(
			'registrations'       => $user_registrations,
			'gender_distribution' => $gender_distribution,
			'age_groups'          => $age_groups,
			'retention_rate'      => $this->calculate_retention_rate(),
		);
	}

	/**
	 * Get score analytics
	 */
	private function get_score_analytics() {
		global $wpdb;

		$pillar_scores = array();
		$pillars       = array( 'mind', 'body', 'lifestyle', 'aesthetics' );

		foreach ( $pillars as $pillar ) {
			$avg_score = $wpdb->get_var(
				$wpdb->prepare(
					"
                SELECT AVG(CAST(meta_value AS DECIMAL(5,2))) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = %s 
                AND meta_value != ''
            ",
					"ennu_pillar_{$pillar}_score"
				)
			);

			$pillar_scores[ $pillar ] = round( floatval( $avg_score ), 1 );
		}

		$score_distribution = $this->get_score_distribution();
		$improvement_trends = $this->get_improvement_trends();

		return array(
			'pillar_averages'    => $pillar_scores,
			'score_distribution' => $score_distribution,
			'improvement_trends' => $improvement_trends,
		);
	}

	/**
	 * Get conversion analytics
	 */
	private function get_conversion_analytics() {
		global $wpdb;

		$consultation_bookings = $wpdb->get_var(
			"
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_consultation_booked'
        "
		);

		$biomarker_purchases = $wpdb->get_var(
			"
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_biomarker_package_purchased'
        "
		);

		$total_active_users = $wpdb->get_var(
			"
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_score'
        "
		);

		return array(
			'consultation_bookings'        => intval( $consultation_bookings ),
			'biomarker_purchases'          => intval( $biomarker_purchases ),
			'consultation_conversion_rate' => $total_active_users > 0 ?
				round( ( $consultation_bookings / $total_active_users ) * 100, 1 ) : 0,
			'biomarker_conversion_rate'    => $total_active_users > 0 ?
				round( ( $biomarker_purchases / $total_active_users ) * 100, 1 ) : 0,
		);
	}

	/**
	 * Get performance metrics
	 */
	private function get_performance_metrics() {
		$performance_monitor = ENNU_Performance_Monitor::get_instance();

		return array(
			'average_load_time' => get_option( 'ennu_avg_load_time', 0 ),
			'database_queries'  => get_option( 'ennu_avg_db_queries', 0 ),
			'memory_usage'      => get_option( 'ennu_avg_memory_usage', 0 ),
			'cache_hit_rate'    => get_option( 'ennu_cache_hit_rate', 0 ),
			'error_rate'        => get_option( 'ennu_error_rate', 0 ),
		);
	}

	/**
	 * Get trend data
	 */
	private function get_trend_data() {
		global $wpdb;

		$daily_assessments = $wpdb->get_results(
			"
            SELECT DATE(FROM_UNIXTIME(meta_value)) as date, COUNT(*) as count
            FROM {$wpdb->usermeta}
            WHERE meta_key LIKE 'ennu_%_assessment_completed'
            AND meta_value >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))
            GROUP BY DATE(FROM_UNIXTIME(meta_value))
            ORDER BY date DESC
        "
		);

		$score_trends = $this->get_score_trends();

		return array(
			'daily_assessments' => $daily_assessments,
			'score_trends'      => $score_trends,
		);
	}

	/**
	 * Calculate completion rate
	 */
	private function calculate_completion_rate() {
		global $wpdb;

		$started = $wpdb->get_var(
			"
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_started'
        "
		);

		$completed = $wpdb->get_var(
			"
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_assessment_completed'
        "
		);

		return $started > 0 ? round( ( $completed / $started ) * 100, 1 ) : 0;
	}

	/**
	 * Calculate retention rate
	 */
	private function calculate_retention_rate() {
		global $wpdb;

		$users_30_days_ago = $wpdb->get_var(
			"
            SELECT COUNT(*) 
            FROM {$wpdb->users} 
            WHERE user_registered <= DATE_SUB(NOW(), INTERVAL 30 DAY)
        "
		);

		$active_users_from_30_days_ago = $wpdb->get_var(
			"
            SELECT COUNT(DISTINCT u.ID) 
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE u.user_registered <= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND um.meta_key LIKE 'ennu_%_score'
            AND FROM_UNIXTIME(um.meta_value) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        "
		);

		return $users_30_days_ago > 0 ?
			round( ( $active_users_from_30_days_ago / $users_30_days_ago ) * 100, 1 ) : 0;
	}

	/**
	 * Get age group distribution
	 */
	private function get_age_group_distribution() {
		global $wpdb;

		$age_data = $wpdb->get_results(
			"
            SELECT meta_value as dob
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'ennu_global_date_of_birth'
            AND meta_value != ''
        "
		);

		$age_groups = array(
			'18-25' => 0,
			'26-35' => 0,
			'36-45' => 0,
			'46-55' => 0,
			'56-65' => 0,
			'65+'   => 0,
		);

		foreach ( $age_data as $data ) {
			$age = date_diff( date_create( $data->dob ), date_create( 'today' ) )->y;

			if ( $age >= 18 && $age <= 25 ) {
				$age_groups['18-25']++;
			} elseif ( $age >= 26 && $age <= 35 ) {
				$age_groups['26-35']++;
			} elseif ( $age >= 36 && $age <= 45 ) {
				$age_groups['36-45']++;
			} elseif ( $age >= 46 && $age <= 55 ) {
				$age_groups['46-55']++;
			} elseif ( $age >= 56 && $age <= 65 ) {
				$age_groups['56-65']++;
			} elseif ( $age > 65 ) {
				$age_groups['65+']++;
			}
		}

		return $age_groups;
	}

	/**
	 * Get score distribution
	 */
	private function get_score_distribution() {
		global $wpdb;

		$scores = $wpdb->get_col(
			"
            SELECT CAST(meta_value AS DECIMAL(5,2))
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'ennu_life_score'
            AND meta_value != ''
        "
		);

		$distribution = array(
			'0-2'  => 0,
			'2-4'  => 0,
			'4-6'  => 0,
			'6-8'  => 0,
			'8-10' => 0,
		);

		foreach ( $scores as $score ) {
			if ( $score >= 0 && $score < 2 ) {
				$distribution['0-2']++;
			} elseif ( $score >= 2 && $score < 4 ) {
				$distribution['2-4']++;
			} elseif ( $score >= 4 && $score < 6 ) {
				$distribution['4-6']++;
			} elseif ( $score >= 6 && $score < 8 ) {
				$distribution['6-8']++;
			} elseif ( $score >= 8 && $score <= 10 ) {
				$distribution['8-10']++;
			}
		}

		return $distribution;
	}

	/**
	 * Get improvement trends
	 */
	private function get_improvement_trends() {
		global $wpdb;

		$trends = $wpdb->get_results(
			"
            SELECT 
                user_id,
                meta_key,
                meta_value,
                FROM_UNIXTIME(meta_value) as date_recorded
            FROM {$wpdb->usermeta}
            WHERE meta_key LIKE 'ennu_%_score_history'
            ORDER BY user_id, date_recorded
        "
		);

		$improvement_data = array();

		foreach ( $trends as $trend ) {
			$user_id    = $trend->user_id;
			$score_type = str_replace( array( 'ennu_', '_score_history' ), '', $trend->meta_key );

			if ( ! isset( $improvement_data[ $user_id ] ) ) {
				$improvement_data[ $user_id ] = array();
			}

			if ( ! isset( $improvement_data[ $user_id ][ $score_type ] ) ) {
				$improvement_data[ $user_id ][ $score_type ] = array();
			}

			$improvement_data[ $user_id ][ $score_type ][] = array(
				'score' => floatval( $trend->meta_value ),
				'date'  => $trend->date_recorded,
			);
		}

		return $improvement_data;
	}

	/**
	 * Get score trends
	 */
	private function get_score_trends() {
		global $wpdb;

		$trends  = array();
		$pillars = array( 'mind', 'body', 'lifestyle', 'aesthetics' );

		foreach ( $pillars as $pillar ) {
			$pillar_trends = $wpdb->get_results(
				$wpdb->prepare(
					"
                SELECT 
                    DATE(FROM_UNIXTIME(meta_value)) as date,
                    AVG(CAST(meta_value AS DECIMAL(5,2))) as avg_score
                FROM {$wpdb->usermeta}
                WHERE meta_key = %s
                AND meta_value != ''
                AND FROM_UNIXTIME(meta_value) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(FROM_UNIXTIME(meta_value))
                ORDER BY date
            ",
					"ennu_pillar_{$pillar}_score"
				)
			);

			$trends[ $pillar ] = $pillar_trends;
		}

		return $trends;
	}

	/**
	 * Track page view
	 */
	public function track_page_view() {
		if ( ! $this->tracking_enabled ) {
			return;
		}

		$this->track_event(
			'page_view',
			'navigation',
			'page_view',
			get_the_title(),
			0,
			array(
				'url'       => $_SERVER['REQUEST_URI'],
				'title'     => get_the_title(),
				'post_type' => get_post_type(),
				'is_mobile' => wp_is_mobile(),
			)
		);
	}

	/**
	 * Track user login
	 */
	public function track_user_login( $user_login, $user ) {
		$this->user_id = $user->ID;
		$this->track_event(
			'user_action',
			'authentication',
			'login',
			$user_login,
			0,
			array(
				'user_id'   => $user->ID,
				'user_role' => implode( ',', $user->roles ),
			)
		);
	}

	/**
	 * Track user logout
	 */
	public function track_user_logout() {
		$this->track_event( 'user_action', 'authentication', 'logout', '', 0 );
	}

	/**
	 * Track user registration
	 */
	public function track_user_registration( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		$this->track_event(
			'conversion',
			'registration',
			'user_registered',
			$user->user_login,
			1,
			array(
				'user_id'             => $user_id,
				'registration_method' => 'standard',
			)
		);
	}

	/**
	 * Track assessment start
	 */
	public function track_assessment_start( $assessment_type, $user_id ) {
		$this->track_event(
			'assessment',
			'engagement',
			'assessment_started',
			$assessment_type,
			0,
			array(
				'assessment_type' => $assessment_type,
				'user_id'         => $user_id,
			)
		);
	}

	/**
	 * Track assessment completion
	 */
	public function track_assessment_completion( $assessment_type, $user_id, $score ) {
		$this->track_event(
			'conversion',
			'assessment',
			'assessment_completed',
			$assessment_type,
			$score,
			array(
				'assessment_type' => $assessment_type,
				'user_id'         => $user_id,
				'score'           => $score,
			)
		);
	}

	/**
	 * Track assessment abandonment
	 */
	public function track_assessment_abandonment( $assessment_type, $user_id ) {
		$this->track_event(
			'engagement',
			'assessment',
			'assessment_abandoned',
			$assessment_type,
			0,
			array(
				'assessment_type' => $assessment_type,
				'user_id'         => $user_id,
			)
		);
	}

	/**
	 * Track biomarker update
	 */
	public function track_biomarker_update( $biomarker_type, $user_id ) {
		$this->track_event(
			'engagement',
			'biomarker',
			'biomarker_updated',
			$biomarker_type,
			0,
			array(
				'biomarker_type' => $biomarker_type,
				'user_id'        => $user_id,
			)
		);
	}

	/**
	 * Track health goal creation
	 */
	public function track_health_goal_creation( $goal_type, $user_id ) {
		$this->track_event(
			'engagement',
			'health_goal',
			'goal_created',
			$goal_type,
			0,
			array(
				'goal_type' => $goal_type,
				'user_id'   => $user_id,
			)
		);
	}

	/**
	 * Track health goal completion
	 */
	public function track_health_goal_completion( $goal_type, $user_id ) {
		$this->track_event(
			'conversion',
			'health_goal',
			'goal_completed',
			$goal_type,
			1,
			array(
				'goal_type' => $goal_type,
				'user_id'   => $user_id,
			)
		);
	}

	/**
	 * Track dashboard view
	 */
	public function track_dashboard_view() {
		$this->track_event(
			'engagement',
			'dashboard',
			'dashboard_viewed',
			'',
			0,
			array(
				'user_id' => $this->user_id,
			)
		);
	}

	/**
	 * Track score view
	 */
	public function track_score_view( $score_type, $user_id ) {
		$this->track_event(
			'engagement',
			'score',
			'score_viewed',
			$score_type,
			0,
			array(
				'score_type' => $score_type,
				'user_id'    => $user_id,
			)
		);
	}

	/**
	 * Track report download
	 */
	public function track_report_download( $report_type, $user_id ) {
		$this->track_event(
			'conversion',
			'report',
			'report_downloaded',
			$report_type,
			1,
			array(
				'report_type' => $report_type,
				'user_id'     => $user_id,
			)
		);
	}

	/**
	 * Core event tracking method
	 */
	private function track_event( $event_type, $category, $action, $label = '', $value = 0, $metadata = array() ) {
		if ( ! $this->tracking_enabled ) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_analytics_events';

		$event_data = array(
			'session_id'     => $this->session_id,
			'user_id'        => $this->user_id,
			'event_type'     => $event_type,
			'event_category' => $category,
			'event_action'   => $action,
			'event_label'    => $label,
			'event_value'    => $value,
			'page_url'       => $_SERVER['REQUEST_URI'] ?? '',
			'referrer_url'   => $_SERVER['HTTP_REFERER'] ?? '',
			'user_agent'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'ip_address'     => $this->get_client_ip(),
			'metadata'       => json_encode( $metadata ),
		);

		$result = $wpdb->insert(
			$table_name,
			$event_data,
			array( '%s', '%d', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			$this->update_user_analytics( $event_data );
			$this->update_session_data();
		}
	}

	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}

		return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}

	/**
	 * Update session data
	 */
	private function update_session_data() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_analytics_sessions';

		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE session_id = %s",
				$this->session_id
			)
		);

		if ( $session ) {
			$wpdb->update(
				$table_name,
				array(
					'end_time'     => current_time( 'mysql' ),
					'events_count' => $session->events_count + 1,
				),
				array( 'session_id' => $this->session_id )
			);
		} else {
			$wpdb->insert(
				$table_name,
				array(
					'session_id'   => $this->session_id,
					'user_id'      => $this->user_id,
					'start_time'   => current_time( 'mysql' ),
					'events_count' => 1,
					'device_type'  => wp_is_mobile() ? 'mobile' : 'desktop',
					'entry_page'   => $_SERVER['REQUEST_URI'] ?? '',
				)
			);
		}
	}

	/**
	 * Render user analytics page
	 */
	public function render_user_analytics() {
		?>
		<div class="wrap">
			<h1>User Analytics</h1>
			<div class="ennu-user-analytics">
				<p>Comprehensive user behavior analytics and engagement metrics.</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render A/B testing page
	 */
	public function render_ab_testing() {
		?>
		<div class="wrap">
			<h1>A/B Testing</h1>
			<div class="ennu-ab-testing">
				<p>A/B testing framework for conversion optimization.</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render analytics reports page
	 */
	public function render_analytics_reports() {
		?>
		<div class="wrap">
			<h1>Analytics Reports</h1>
			<div class="ennu-analytics-reports">
				<p>Comprehensive analytics reports and business intelligence.</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render analytics settings page
	 */
	public function render_analytics_settings() {
		?>
		<div class="wrap">
			<h1>Analytics Settings</h1>
			<div class="ennu-analytics-settings">
				<p>Configure analytics tracking and reporting settings.</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Process analytics data (scheduled task)
	 */
	public function process_analytics_data() {
		// Process and aggregate analytics data
		error_log( 'ENNU Analytics: Processing analytics data' );
	}

	/**
	 * Generate scheduled reports
	 */
	public function generate_scheduled_reports() {
		error_log( 'ENNU Analytics: Generating scheduled reports' );
	}

	/**
	 * Cleanup old analytics data
	 */
	public function cleanup_old_analytics_data() {
		$retention_days = get_option( 'ennu_analytics_retention_days', 365 );

		if ( $retention_days <= 0 ) {
			return; // Never delete if retention is disabled
		}

		global $wpdb;

		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		$events_table     = $wpdb->prefix . 'ennu_analytics_events';
		$sessions_table   = $wpdb->prefix . 'ennu_analytics_sessions';
		$ab_testing_table = $wpdb->prefix . 'ennu_ab_testing';

		$deleted_events = $wpdb->query( $wpdb->prepare( "DELETE FROM $events_table WHERE timestamp < %s", $cutoff_date ) );

		$deleted_sessions = $wpdb->query( $wpdb->prepare( "DELETE FROM $sessions_table WHERE start_time < %s", $cutoff_date ) );

		$deleted_ab_tests = $wpdb->query( $wpdb->prepare( "DELETE FROM $ab_testing_table WHERE assigned_at < %s", $cutoff_date ) );

		error_log( "ENNU Analytics Cleanup: Deleted {$deleted_events} events, {$deleted_sessions} sessions, {$deleted_ab_tests} A/B test records older than {$retention_days} days" );
	}

	/**
	 * AJAX handler for analytics data
	 */
	public function get_analytics_data() {
		check_ajax_referer( 'ennu_analytics_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$data_type      = sanitize_text_field( $_POST['data_type'] ?? 'overview' );
		$analytics_data = $this->get_dashboard_analytics();

		if ( isset( $analytics_data[ $data_type ] ) ) {
			wp_send_json_success( $analytics_data[ $data_type ] );
		} else {
			wp_send_json_error( array( 'message' => 'Invalid data type requested' ) );
		}
	}
}

ENNU_Advanced_Analytics::get_instance();
