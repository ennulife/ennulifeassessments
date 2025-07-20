<?php
/**
 * ENNU Advanced Analytics Dashboard
 * Comprehensive admin analytics and reporting
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Advanced_Analytics {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_analytics_menu' ) );
        add_action( 'wp_ajax_ennu_analytics_data', array( $this, 'get_analytics_data' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_analytics_assets' ) );
    }
    
    /**
     * Add analytics menu to admin
     */
    public function add_analytics_menu() {
        add_submenu_page(
            'ennu-life-admin',
            __( 'Analytics Dashboard', 'ennu-life-assessments' ),
            __( 'Analytics', 'ennu-life-assessments' ),
            'manage_options',
            'ennu-analytics',
            array( $this, 'render_analytics_dashboard' )
        );
    }
    
    /**
     * Enqueue analytics assets
     */
    public function enqueue_analytics_assets() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        wp_enqueue_script(
            'ennu-analytics',
            plugin_dir_url( __FILE__ ) . '../assets/js/analytics-dashboard.js',
            array( 'jquery' ),
            '62.2.8',
            true
        );
        
        wp_enqueue_style(
            'ennu-analytics',
            plugin_dir_url( __FILE__ ) . '../assets/css/analytics-dashboard.css',
            array(),
            '62.2.8'
        );
        
        wp_localize_script( 'ennu-analytics', 'ennuAnalytics', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ennu_analytics_nonce' ),
            'strings' => array(
                'loading' => __( 'Loading analytics data...', 'ennu-life-assessments' ),
                'error' => __( 'Error loading data', 'ennu-life-assessments' ),
                'noData' => __( 'No data available', 'ennu-life-assessments' )
            )
        ) );
    }
    
    /**
     * Render analytics dashboard
     */
    public function render_analytics_dashboard() {
        $analytics_data = $this->get_comprehensive_analytics();
        
        include plugin_dir_path( __FILE__ ) . '../templates/admin/advanced-analytics-dashboard.php';
    }
    
    /**
     * Get comprehensive analytics data
     */
    public function get_comprehensive_analytics() {
        return array(
            'overview' => $this->get_overview_metrics(),
            'assessments' => $this->get_assessment_analytics(),
            'users' => $this->get_user_analytics(),
            'scores' => $this->get_score_analytics(),
            'conversion' => $this->get_conversion_analytics(),
            'performance' => $this->get_performance_metrics(),
            'trends' => $this->get_trend_data()
        );
    }
    
    /**
     * Get overview metrics
     */
    private function get_overview_metrics() {
        global $wpdb;
        
        $total_users = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
        $active_users = $wpdb->get_var( "
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_score' 
            AND meta_value != ''
        " );
        
        $total_assessments = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_assessment_completed'
        " );
        
        $avg_life_score = $wpdb->get_var( "
            SELECT AVG(CAST(meta_value AS DECIMAL(5,2))) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_life_score' 
            AND meta_value != ''
        " );
        
        return array(
            'total_users' => intval( $total_users ),
            'active_users' => intval( $active_users ),
            'total_assessments' => intval( $total_assessments ),
            'avg_life_score' => round( floatval( $avg_life_score ), 1 ),
            'engagement_rate' => $total_users > 0 ? round( ( $active_users / $total_users ) * 100, 1 ) : 0
        );
    }
    
    /**
     * Get assessment analytics
     */
    private function get_assessment_analytics() {
        global $wpdb;
        
        $assessment_types = array(
            'welcome', 'hair', 'health', 'skin', 'sleep', 
            'hormone', 'menopause', 'testosterone', 'weight_loss', 
            'ed_treatment', 'health_optimization'
        );
        
        $completion_data = array();
        $score_data = array();
        
        foreach ( $assessment_types as $type ) {
            $completions = $wpdb->get_var( $wpdb->prepare( "
                SELECT COUNT(*) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = %s
            ", "ennu_{$type}_assessment_completed" ) );
            
            $avg_score = $wpdb->get_var( $wpdb->prepare( "
                SELECT AVG(CAST(meta_value AS DECIMAL(5,2))) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = %s 
                AND meta_value != ''
            ", "ennu_{$type}_score" ) );
            
            $completion_data[ $type ] = intval( $completions );
            $score_data[ $type ] = round( floatval( $avg_score ), 1 );
        }
        
        return array(
            'completions' => $completion_data,
            'average_scores' => $score_data,
            'most_popular' => array_keys( $completion_data, max( $completion_data ) )[0] ?? 'welcome',
            'completion_rate' => $this->calculate_completion_rate()
        );
    }
    
    /**
     * Get user analytics
     */
    private function get_user_analytics() {
        global $wpdb;
        
        $user_registrations = $wpdb->get_results( "
            SELECT DATE(user_registered) as date, COUNT(*) as count
            FROM {$wpdb->users}
            WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(user_registered)
            ORDER BY date DESC
        " );
        
        $gender_distribution = $wpdb->get_results( "
            SELECT meta_value as gender, COUNT(*) as count
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'ennu_global_gender'
            GROUP BY meta_value
        " );
        
        $age_groups = $this->get_age_group_distribution();
        
        return array(
            'registrations' => $user_registrations,
            'gender_distribution' => $gender_distribution,
            'age_groups' => $age_groups,
            'retention_rate' => $this->calculate_retention_rate()
        );
    }
    
    /**
     * Get score analytics
     */
    private function get_score_analytics() {
        global $wpdb;
        
        $pillar_scores = array();
        $pillars = array( 'mind', 'body', 'lifestyle', 'aesthetics' );
        
        foreach ( $pillars as $pillar ) {
            $avg_score = $wpdb->get_var( $wpdb->prepare( "
                SELECT AVG(CAST(meta_value AS DECIMAL(5,2))) 
                FROM {$wpdb->usermeta} 
                WHERE meta_key = %s 
                AND meta_value != ''
            ", "ennu_pillar_{$pillar}_score" ) );
            
            $pillar_scores[ $pillar ] = round( floatval( $avg_score ), 1 );
        }
        
        $score_distribution = $this->get_score_distribution();
        $improvement_trends = $this->get_improvement_trends();
        
        return array(
            'pillar_averages' => $pillar_scores,
            'score_distribution' => $score_distribution,
            'improvement_trends' => $improvement_trends
        );
    }
    
    /**
     * Get conversion analytics
     */
    private function get_conversion_analytics() {
        global $wpdb;
        
        $consultation_bookings = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_consultation_booked'
        " );
        
        $biomarker_purchases = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key = 'ennu_biomarker_package_purchased'
        " );
        
        $total_active_users = $wpdb->get_var( "
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_score'
        " );
        
        return array(
            'consultation_bookings' => intval( $consultation_bookings ),
            'biomarker_purchases' => intval( $biomarker_purchases ),
            'consultation_conversion_rate' => $total_active_users > 0 ? 
                round( ( $consultation_bookings / $total_active_users ) * 100, 1 ) : 0,
            'biomarker_conversion_rate' => $total_active_users > 0 ? 
                round( ( $biomarker_purchases / $total_active_users ) * 100, 1 ) : 0
        );
    }
    
    /**
     * Get performance metrics
     */
    private function get_performance_metrics() {
        $performance_monitor = ENNU_Performance_Monitor::get_instance();
        
        return array(
            'average_load_time' => get_option( 'ennu_avg_load_time', 0 ),
            'database_queries' => get_option( 'ennu_avg_db_queries', 0 ),
            'memory_usage' => get_option( 'ennu_avg_memory_usage', 0 ),
            'cache_hit_rate' => get_option( 'ennu_cache_hit_rate', 0 ),
            'error_rate' => get_option( 'ennu_error_rate', 0 )
        );
    }
    
    /**
     * Get trend data
     */
    private function get_trend_data() {
        global $wpdb;
        
        $daily_assessments = $wpdb->get_results( "
            SELECT DATE(FROM_UNIXTIME(meta_value)) as date, COUNT(*) as count
            FROM {$wpdb->usermeta}
            WHERE meta_key LIKE 'ennu_%_assessment_completed'
            AND meta_value >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))
            GROUP BY DATE(FROM_UNIXTIME(meta_value))
            ORDER BY date DESC
        " );
        
        $score_trends = $this->get_score_trends();
        
        return array(
            'daily_assessments' => $daily_assessments,
            'score_trends' => $score_trends
        );
    }
    
    /**
     * Calculate completion rate
     */
    private function calculate_completion_rate() {
        global $wpdb;
        
        $started = $wpdb->get_var( "
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_started'
        " );
        
        $completed = $wpdb->get_var( "
            SELECT COUNT(DISTINCT user_id) 
            FROM {$wpdb->usermeta} 
            WHERE meta_key LIKE 'ennu_%_assessment_completed'
        " );
        
        return $started > 0 ? round( ( $completed / $started ) * 100, 1 ) : 0;
    }
    
    /**
     * Calculate retention rate
     */
    private function calculate_retention_rate() {
        global $wpdb;
        
        $users_30_days_ago = $wpdb->get_var( "
            SELECT COUNT(*) 
            FROM {$wpdb->users} 
            WHERE user_registered <= DATE_SUB(NOW(), INTERVAL 30 DAY)
        " );
        
        $active_users_from_30_days_ago = $wpdb->get_var( "
            SELECT COUNT(DISTINCT u.ID) 
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE u.user_registered <= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND um.meta_key LIKE 'ennu_%_score'
            AND FROM_UNIXTIME(um.meta_value) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        " );
        
        return $users_30_days_ago > 0 ? 
            round( ( $active_users_from_30_days_ago / $users_30_days_ago ) * 100, 1 ) : 0;
    }
    
    /**
     * Get age group distribution
     */
    private function get_age_group_distribution() {
        global $wpdb;
        
        $age_data = $wpdb->get_results( "
            SELECT meta_value as dob
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'ennu_global_date_of_birth'
            AND meta_value != ''
        " );
        
        $age_groups = array(
            '18-25' => 0,
            '26-35' => 0,
            '36-45' => 0,
            '46-55' => 0,
            '56-65' => 0,
            '65+' => 0
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
        
        $scores = $wpdb->get_col( "
            SELECT CAST(meta_value AS DECIMAL(5,2))
            FROM {$wpdb->usermeta}
            WHERE meta_key = 'ennu_life_score'
            AND meta_value != ''
        " );
        
        $distribution = array(
            '0-2' => 0,
            '2-4' => 0,
            '4-6' => 0,
            '6-8' => 0,
            '8-10' => 0
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
        
        $trends = $wpdb->get_results( "
            SELECT 
                user_id,
                meta_key,
                meta_value,
                FROM_UNIXTIME(meta_value) as date_recorded
            FROM {$wpdb->usermeta}
            WHERE meta_key LIKE 'ennu_%_score_history'
            ORDER BY user_id, date_recorded
        " );
        
        $improvement_data = array();
        
        foreach ( $trends as $trend ) {
            $user_id = $trend->user_id;
            $score_type = str_replace( array( 'ennu_', '_score_history' ), '', $trend->meta_key );
            
            if ( ! isset( $improvement_data[ $user_id ] ) ) {
                $improvement_data[ $user_id ] = array();
            }
            
            if ( ! isset( $improvement_data[ $user_id ][ $score_type ] ) ) {
                $improvement_data[ $user_id ][ $score_type ] = array();
            }
            
            $improvement_data[ $user_id ][ $score_type ][] = array(
                'score' => floatval( $trend->meta_value ),
                'date' => $trend->date_recorded
            );
        }
        
        return $improvement_data;
    }
    
    /**
     * Get score trends
     */
    private function get_score_trends() {
        global $wpdb;
        
        $trends = array();
        $pillars = array( 'mind', 'body', 'lifestyle', 'aesthetics' );
        
        foreach ( $pillars as $pillar ) {
            $pillar_trends = $wpdb->get_results( $wpdb->prepare( "
                SELECT 
                    DATE(FROM_UNIXTIME(meta_value)) as date,
                    AVG(CAST(meta_value AS DECIMAL(5,2))) as avg_score
                FROM {$wpdb->usermeta}
                WHERE meta_key = %s
                AND meta_value != ''
                AND FROM_UNIXTIME(meta_value) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(FROM_UNIXTIME(meta_value))
                ORDER BY date
            ", "ennu_pillar_{$pillar}_score" ) );
            
            $trends[ $pillar ] = $pillar_trends;
        }
        
        return $trends;
    }
    
    /**
     * AJAX handler for analytics data
     */
    public function get_analytics_data() {
        check_ajax_referer( 'ennu_analytics_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
        }
        
        $data_type = sanitize_text_field( $_POST['data_type'] ?? 'overview' );
        $analytics_data = $this->get_comprehensive_analytics();
        
        if ( isset( $analytics_data[ $data_type ] ) ) {
            wp_send_json_success( $analytics_data[ $data_type ] );
        } else {
            wp_send_json_error( array( 'message' => 'Invalid data type requested' ) );
        }
    }
}

ENNU_Advanced_Analytics::get_instance();
