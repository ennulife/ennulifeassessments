<?php
/**
 * ENNU Trends Visualization System
 * Provides comprehensive trend charts and data visualization for user dashboard
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Trends_Visualization_System {

    /**
     * Initialize trends visualization system
     */
    public static function init() {
        add_action( 'wp_ajax_ennu_get_trend_data', array( __CLASS__, 'handle_get_trend_data' ) );
        add_action( 'wp_ajax_ennu_get_biomarker_trends', array( __CLASS__, 'handle_get_biomarker_trends' ) );
        add_action( 'wp_ajax_ennu_get_score_trends', array( __CLASS__, 'handle_get_score_trends' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
        
        error_log('ENNU Trends Visualization System: Initialized');
    }

    /**
     * Enqueue visualization scripts
     */
    public static function enqueue_scripts() {
        if ( is_user_logged_in() ) {
            wp_enqueue_script(
                'ennu-trends-visualization',
                plugin_dir_url( __FILE__ ) . '../assets/js/trends-visualization.js',
                array( 'jquery' ),
                '62.2.8',
                true
            );

            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js',
                array(),
                '3.9.1',
                true
            );

            wp_localize_script( 'ennu-trends-visualization', 'ennuTrends', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'ennu_ajax_nonce' )
            ) );
        }
    }

    /**
     * Get My Trends tab content for dashboard
     *
     * @param int $user_id User ID
     * @return string HTML content
     */
    public static function get_my_trends_tab_content( $user_id ) {
        ob_start();
        ?>
        <div class="my-trends-container">
            <div class="trends-header">
                <h2>My Health Trends</h2>
                <p>Track your progress over time with comprehensive health metrics visualization</p>
            </div>

            <div class="trends-filters">
                <div class="filter-group">
                    <label for="trendsTimeRange">Time Range:</label>
                    <select id="trendsTimeRange" class="trends-filter">
                        <option value="30">Last 30 Days</option>
                        <option value="90" selected>Last 3 Months</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last Year</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="trendsCategory">Category:</label>
                    <select id="trendsCategory" class="trends-filter">
                        <option value="all" selected>All Metrics</option>
                        <option value="scores">Assessment Scores</option>
                        <option value="biomarkers">Biomarkers</option>
                        <option value="goals">Goal Progress</option>
                        <option value="symptoms">Symptoms</option>
                    </select>
                </div>
            </div>

            <div class="trends-grid">
                <div class="trend-card">
                    <div class="trend-card-header">
                        <h3>Life Score Progression</h3>
                        <div class="trend-indicator" id="lifeScoreTrend">
                            <span class="trend-value">--</span>
                            <span class="trend-direction">--</span>
                        </div>
                    </div>
                    <div class="trend-chart-container">
                        <canvas id="lifeScoreChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="trend-card">
                    <div class="trend-card-header">
                        <h3>Pillar Scores</h3>
                        <div class="pillar-legend">
                            <span class="legend-item mind">Mind</span>
                            <span class="legend-item body">Body</span>
                            <span class="legend-item lifestyle">Lifestyle</span>
                            <span class="legend-item aesthetics">Aesthetics</span>
                        </div>
                    </div>
                    <div class="trend-chart-container">
                        <canvas id="pillarScoresChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="trend-card">
                    <div class="trend-card-header">
                        <h3>Assessment Scores</h3>
                        <select id="assessmentSelect" class="assessment-selector">
                            <option value="all">All Assessments</option>
                            <option value="testosterone">Testosterone</option>
                            <option value="health">Health</option>
                            <option value="weight_loss">Weight Loss</option>
                            <option value="sleep">Sleep</option>
                        </select>
                    </div>
                    <div class="trend-chart-container">
                        <canvas id="assessmentScoresChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="trend-card">
                    <div class="trend-card-header">
                        <h3>Key Biomarkers</h3>
                        <select id="biomarkerSelect" class="biomarker-selector">
                            <option value="testosterone_total">Total Testosterone</option>
                            <option value="vitamin_d">Vitamin D</option>
                            <option value="thyroid_tsh">TSH</option>
                            <option value="cholesterol_total">Total Cholesterol</option>
                        </select>
                    </div>
                    <div class="trend-chart-container">
                        <canvas id="biomarkerChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="trend-card">
                    <div class="trend-card-header">
                        <h3>Goal Achievement</h3>
                        <div class="goal-summary" id="goalSummary">
                            <span class="goals-completed">0 Goals Achieved</span>
                        </div>
                    </div>
                    <div class="trend-chart-container">
                        <canvas id="goalProgressChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="trend-card">
                    <div class="trend-card-header">
                        <h3>Symptom Improvement</h3>
                        <div class="symptom-indicator" id="symptomTrend">
                            <span class="trend-value">--</span>
                            <span class="trend-label">Overall Improvement</span>
                        </div>
                    </div>
                    <div class="trend-chart-container">
                        <canvas id="symptomChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="trends-insights">
                <h3>Trend Insights</h3>
                <div class="insights-grid" id="trendsInsights">
                    <div class="insight-card loading">
                        <div class="insight-icon">📈</div>
                        <div class="insight-content">
                            <h4>Loading insights...</h4>
                            <p>Analyzing your health trends...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .my-trends-container {
                padding: 20px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .trends-header {
                text-align: center;
                margin-bottom: 30px;
            }

            .trends-header h2 {
                color: #2c3e50;
                margin-bottom: 10px;
            }

            .trends-filters {
                display: flex;
                gap: 20px;
                margin-bottom: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
                align-items: center;
                justify-content: center;
            }

            .filter-group {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .filter-group label {
                font-weight: 600;
                color: #495057;
                font-size: 0.9em;
            }

            .trends-filter, .assessment-selector, .biomarker-selector {
                padding: 8px 12px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                background: white;
                font-size: 0.9em;
            }

            .trends-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }

            .trend-card {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .trend-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #e9ecef;
            }

            .trend-card-header h3 {
                margin: 0;
                color: #2c3e50;
                font-size: 1.1em;
            }

            .trend-indicator {
                display: flex;
                align-items: center;
                gap: 5px;
            }

            .trend-value {
                font-weight: 600;
                font-size: 1.1em;
            }

            .trend-direction {
                font-size: 1.2em;
            }

            .trend-direction.up {
                color: #28a745;
            }

            .trend-direction.down {
                color: #dc3545;
            }

            .trend-direction.stable {
                color: #6c757d;
            }

            .pillar-legend {
                display: flex;
                gap: 10px;
                font-size: 0.8em;
            }

            .legend-item {
                padding: 2px 8px;
                border-radius: 12px;
                color: white;
                font-weight: 500;
            }

            .legend-item.mind { background: #007bff; }
            .legend-item.body { background: #28a745; }
            .legend-item.lifestyle { background: #ffc107; color: #212529; }
            .legend-item.aesthetics { background: #e83e8c; }

            .trend-chart-container {
                position: relative;
                height: 200px;
                margin-top: 10px;
            }

            .goal-summary {
                font-size: 0.9em;
                color: #495057;
            }

            .goals-completed {
                font-weight: 600;
                color: #28a745;
            }

            .symptom-indicator {
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                text-align: right;
            }

            .symptom-indicator .trend-value {
                font-size: 1.2em;
                color: #28a745;
            }

            .symptom-indicator .trend-label {
                font-size: 0.8em;
                color: #6c757d;
            }

            .trends-insights {
                background: white;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
            }

            .trends-insights h3 {
                margin-top: 0;
                color: #2c3e50;
                margin-bottom: 20px;
            }

            .insights-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 15px;
            }

            .insight-card {
                display: flex;
                gap: 15px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 6px;
                border-left: 4px solid #007bff;
            }

            .insight-card.positive {
                border-left-color: #28a745;
            }

            .insight-card.warning {
                border-left-color: #ffc107;
            }

            .insight-card.negative {
                border-left-color: #dc3545;
            }

            .insight-icon {
                font-size: 1.5em;
                flex-shrink: 0;
            }

            .insight-content h4 {
                margin: 0 0 5px 0;
                color: #2c3e50;
                font-size: 1em;
            }

            .insight-content p {
                margin: 0;
                color: #6c757d;
                font-size: 0.9em;
                line-height: 1.4;
            }

            .loading {
                opacity: 0.6;
            }

            @media (max-width: 768px) {
                .trends-grid {
                    grid-template-columns: 1fr;
                }
                
                .trends-filters {
                    flex-direction: column;
                    gap: 15px;
                }
                
                .filter-group {
                    width: 100%;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof initializeTrendsVisualization === 'function') {
                    initializeTrendsVisualization(<?php echo $user_id; ?>);
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle get trend data AJAX request
     */
    public static function handle_get_trend_data() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $user_id = get_current_user_id();
        $time_range = intval( $_POST['time_range'] ?? 90 );
        $category = sanitize_text_field( $_POST['category'] ?? 'all' );

        $trend_data = self::get_comprehensive_trend_data( $user_id, $time_range, $category );

        wp_send_json_success( $trend_data );
    }

    /**
     * Handle get biomarker trends AJAX request
     */
    public static function handle_get_biomarker_trends() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $user_id = get_current_user_id();
        $biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );
        $time_range = intval( $_POST['time_range'] ?? 90 );

        $biomarker_trends = self::get_biomarker_trend_data( $user_id, $biomarker, $time_range );

        wp_send_json_success( $biomarker_trends );
    }

    /**
     * Handle get score trends AJAX request
     */
    public static function handle_get_score_trends() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $user_id = get_current_user_id();
        $assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? 'all' );
        $time_range = intval( $_POST['time_range'] ?? 90 );

        $score_trends = self::get_score_trend_data( $user_id, $assessment_type, $time_range );

        wp_send_json_success( $score_trends );
    }

    /**
     * Get comprehensive trend data
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @param string $category Data category
     * @return array Trend data
     */
    private static function get_comprehensive_trend_data( $user_id, $time_range, $category ) {
        $trend_data = array(
            'life_score' => self::get_life_score_trends( $user_id, $time_range ),
            'pillar_scores' => self::get_pillar_score_trends( $user_id, $time_range ),
            'assessment_scores' => self::get_assessment_score_trends( $user_id, $time_range ),
            'goal_progress' => self::get_goal_progress_trends( $user_id, $time_range ),
            'symptom_trends' => self::get_symptom_trends( $user_id, $time_range ),
            'insights' => self::generate_trend_insights( $user_id, $time_range )
        );

        if ( $category !== 'all' ) {
            $filtered_data = array();
            switch ( $category ) {
                case 'scores':
                    $filtered_data = array(
                        'life_score' => $trend_data['life_score'],
                        'pillar_scores' => $trend_data['pillar_scores'],
                        'assessment_scores' => $trend_data['assessment_scores']
                    );
                    break;
                case 'biomarkers':
                    $filtered_data['biomarkers'] = self::get_all_biomarker_trends( $user_id, $time_range );
                    break;
                case 'goals':
                    $filtered_data['goal_progress'] = $trend_data['goal_progress'];
                    break;
                case 'symptoms':
                    $filtered_data['symptom_trends'] = $trend_data['symptom_trends'];
                    break;
            }
            $trend_data = $filtered_data;
        }

        return $trend_data;
    }

    /**
     * Get life score trends
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array Life score trend data
     */
    private static function get_life_score_trends( $user_id, $time_range ) {
        $score_history = get_user_meta( $user_id, 'ennu_life_score_history', true );
        if ( ! is_array( $score_history ) ) {
            return array( 'labels' => array(), 'data' => array(), 'trend' => 'stable' );
        }

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$time_range} days" ) );
        $filtered_history = array_filter( $score_history, function( $entry ) use ( $cutoff_date ) {
            return $entry['timestamp'] >= $cutoff_date;
        } );

        $labels = array();
        $data = array();

        foreach ( $filtered_history as $entry ) {
            $labels[] = date( 'M j', strtotime( $entry['timestamp'] ) );
            $data[] = floatval( $entry['score'] );
        }

        $trend = self::calculate_trend_direction( $data );

        return array(
            'labels' => $labels,
            'data' => $data,
            'trend' => $trend,
            'current_value' => end( $data ) ?: 0,
            'change' => self::calculate_percentage_change( $data )
        );
    }

    /**
     * Get pillar score trends
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array Pillar score trend data
     */
    private static function get_pillar_score_trends( $user_id, $time_range ) {
        $pillar_history = get_user_meta( $user_id, 'ennu_pillar_scores_history', true );
        if ( ! is_array( $pillar_history ) ) {
            return array( 'labels' => array(), 'datasets' => array() );
        }

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$time_range} days" ) );
        $filtered_history = array_filter( $pillar_history, function( $entry ) use ( $cutoff_date ) {
            return $entry['timestamp'] >= $cutoff_date;
        } );

        $labels = array();
        $pillar_data = array(
            'mind' => array(),
            'body' => array(),
            'lifestyle' => array(),
            'aesthetics' => array()
        );

        foreach ( $filtered_history as $entry ) {
            $labels[] = date( 'M j', strtotime( $entry['timestamp'] ) );
            $scores = $entry['scores'];
            
            $pillar_data['mind'][] = floatval( $scores['mind'] ?? 0 );
            $pillar_data['body'][] = floatval( $scores['body'] ?? 0 );
            $pillar_data['lifestyle'][] = floatval( $scores['lifestyle'] ?? 0 );
            $pillar_data['aesthetics'][] = floatval( $scores['aesthetics'] ?? 0 );
        }

        $datasets = array(
            array(
                'label' => 'Mind',
                'data' => $pillar_data['mind'],
                'borderColor' => '#007bff',
                'backgroundColor' => 'rgba(0, 123, 255, 0.1)',
                'tension' => 0.4
            ),
            array(
                'label' => 'Body',
                'data' => $pillar_data['body'],
                'borderColor' => '#28a745',
                'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                'tension' => 0.4
            ),
            array(
                'label' => 'Lifestyle',
                'data' => $pillar_data['lifestyle'],
                'borderColor' => '#ffc107',
                'backgroundColor' => 'rgba(255, 193, 7, 0.1)',
                'tension' => 0.4
            ),
            array(
                'label' => 'Aesthetics',
                'data' => $pillar_data['aesthetics'],
                'borderColor' => '#e83e8c',
                'backgroundColor' => 'rgba(232, 62, 140, 0.1)',
                'tension' => 0.4
            )
        );

        return array(
            'labels' => $labels,
            'datasets' => $datasets
        );
    }

    /**
     * Get assessment score trends
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array Assessment score trend data
     */
    private static function get_assessment_score_trends( $user_id, $time_range ) {
        $assessment_types = array( 'testosterone', 'health', 'weight_loss', 'sleep', 'menopause' );
        $trend_data = array();

        foreach ( $assessment_types as $type ) {
            $score_history = get_user_meta( $user_id, "ennu_{$type}_score_history", true );
            if ( ! is_array( $score_history ) ) {
                continue;
            }

            $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$time_range} days" ) );
            $filtered_history = array_filter( $score_history, function( $entry ) use ( $cutoff_date ) {
                return $entry['timestamp'] >= $cutoff_date;
            } );

            if ( empty( $filtered_history ) ) {
                continue;
            }

            $labels = array();
            $data = array();

            foreach ( $filtered_history as $entry ) {
                $labels[] = date( 'M j', strtotime( $entry['timestamp'] ) );
                $data[] = floatval( $entry['overall_score'] ?? 0 );
            }

            $trend_data[ $type ] = array(
                'labels' => $labels,
                'data' => $data,
                'trend' => self::calculate_trend_direction( $data ),
                'current_value' => end( $data ) ?: 0,
                'change' => self::calculate_percentage_change( $data )
            );
        }

        return $trend_data;
    }

    /**
     * Get biomarker trend data
     *
     * @param int $user_id User ID
     * @param string $biomarker Biomarker name
     * @param int $time_range Time range in days
     * @return array Biomarker trend data
     */
    private static function get_biomarker_trend_data( $user_id, $biomarker, $time_range ) {
        $biomarker_history = get_user_meta( $user_id, 'ennu_biomarker_history', true );
        if ( ! is_array( $biomarker_history ) || ! isset( $biomarker_history[ $biomarker ] ) ) {
            return array( 'labels' => array(), 'data' => array(), 'trend' => 'stable' );
        }

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$time_range} days" ) );
        $biomarker_data = $biomarker_history[ $biomarker ];
        
        $filtered_data = array_filter( $biomarker_data, function( $entry ) use ( $cutoff_date ) {
            return $entry['date_tested'] >= $cutoff_date;
        } );

        $labels = array();
        $data = array();

        foreach ( $filtered_data as $entry ) {
            $labels[] = date( 'M j', strtotime( $entry['date_tested'] ) );
            $data[] = floatval( $entry['value'] );
        }

        return array(
            'labels' => $labels,
            'data' => $data,
            'trend' => self::calculate_trend_direction( $data ),
            'current_value' => end( $data ) ?: 0,
            'change' => self::calculate_percentage_change( $data ),
            'reference_range' => $filtered_data ? end( $filtered_data )['reference_range'] ?? '' : ''
        );
    }

    /**
     * Get goal progress trends
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array Goal progress trend data
     */
    private static function get_goal_progress_trends( $user_id, $time_range ) {
        $goal_history = get_user_meta( $user_id, 'ennu_goal_progress_history', true );
        if ( ! is_array( $goal_history ) ) {
            return array( 'labels' => array(), 'datasets' => array() );
        }

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$time_range} days" ) );
        $filtered_history = array_filter( $goal_history, function( $entry ) use ( $cutoff_date ) {
            return $entry['timestamp'] >= $cutoff_date;
        } );

        $labels = array();
        $goal_data = array();

        foreach ( $filtered_history as $entry ) {
            $labels[] = date( 'M j', strtotime( $entry['timestamp'] ) );
            $progress = $entry['progress'];
            
            foreach ( $progress as $goal => $goal_progress ) {
                if ( ! isset( $goal_data[ $goal ] ) ) {
                    $goal_data[ $goal ] = array();
                }
                $goal_data[ $goal ][] = $goal_progress['progress_percentage'] ?? 0;
            }
        }

        $datasets = array();
        $colors = array( '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14' );
        $color_index = 0;

        foreach ( $goal_data as $goal => $data ) {
            $datasets[] = array(
                'label' => ucwords( str_replace( '_', ' ', $goal ) ),
                'data' => $data,
                'borderColor' => $colors[ $color_index % count( $colors ) ],
                'backgroundColor' => $colors[ $color_index % count( $colors ) ] . '20',
                'tension' => 0.4
            );
            $color_index++;
        }

        return array(
            'labels' => $labels,
            'datasets' => $datasets
        );
    }

    /**
     * Get symptom trends
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array Symptom trend data
     */
    private static function get_symptom_trends( $user_id, $time_range ) {
        $symptom_history = get_user_meta( $user_id, 'ennu_symptom_history', true );
        if ( ! is_array( $symptom_history ) ) {
            return array( 'labels' => array(), 'data' => array(), 'trend' => 'stable' );
        }

        $cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$time_range} days" ) );
        $filtered_history = array_filter( $symptom_history, function( $entry ) use ( $cutoff_date ) {
            return $entry['timestamp'] >= $cutoff_date;
        } );

        $labels = array();
        $data = array();

        foreach ( $filtered_history as $entry ) {
            $labels[] = date( 'M j', strtotime( $entry['timestamp'] ) );
            
            $total_severity = 0;
            $symptom_count = 0;
            
            foreach ( $entry['symptoms'] as $symptom => $severity ) {
                $total_severity += intval( $severity );
                $symptom_count++;
            }
            
            $average_severity = $symptom_count > 0 ? $total_severity / $symptom_count : 0;
            $data[] = 10 - $average_severity;
        }

        return array(
            'labels' => $labels,
            'data' => $data,
            'trend' => self::calculate_trend_direction( $data ),
            'current_value' => end( $data ) ?: 0,
            'change' => self::calculate_percentage_change( $data )
        );
    }

    /**
     * Get all biomarker trends
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array All biomarker trends
     */
    private static function get_all_biomarker_trends( $user_id, $time_range ) {
        $biomarker_history = get_user_meta( $user_id, 'ennu_biomarker_history', true );
        if ( ! is_array( $biomarker_history ) ) {
            return array();
        }

        $trends = array();
        foreach ( $biomarker_history as $biomarker => $history ) {
            $trends[ $biomarker ] = self::get_biomarker_trend_data( $user_id, $biomarker, $time_range );
        }

        return $trends;
    }

    /**
     * Generate trend insights
     *
     * @param int $user_id User ID
     * @param int $time_range Time range in days
     * @return array Trend insights
     */
    private static function generate_trend_insights( $user_id, $time_range ) {
        $insights = array();

        $life_score_trend = self::get_life_score_trends( $user_id, $time_range );
        if ( ! empty( $life_score_trend['data'] ) ) {
            $change = $life_score_trend['change'];
            if ( $change > 5 ) {
                $insights[] = array(
                    'type' => 'positive',
                    'icon' => '📈',
                    'title' => 'Life Score Improving',
                    'description' => "Your Life Score has increased by {$change}% over the selected period. Keep up the great work!"
                );
            } elseif ( $change < -5 ) {
                $insights[] = array(
                    'type' => 'warning',
                    'icon' => '📉',
                    'title' => 'Life Score Declining',
                    'description' => "Your Life Score has decreased by " . abs( $change ) . "%. Consider reviewing your health goals and strategies."
                );
            } else {
                $insights[] = array(
                    'type' => 'neutral',
                    'icon' => '📊',
                    'title' => 'Life Score Stable',
                    'description' => 'Your Life Score has remained relatively stable. Focus on consistency to see improvements.'
                );
            }
        }

        $goal_progress = self::get_goal_progress_trends( $user_id, $time_range );
        if ( ! empty( $goal_progress['datasets'] ) ) {
            $completed_goals = 0;
            foreach ( $goal_progress['datasets'] as $dataset ) {
                $latest_progress = end( $dataset['data'] );
                if ( $latest_progress >= 100 ) {
                    $completed_goals++;
                }
            }
            
            if ( $completed_goals > 0 ) {
                $insights[] = array(
                    'type' => 'positive',
                    'icon' => '🎯',
                    'title' => 'Goals Achieved',
                    'description' => "Congratulations! You've achieved {$completed_goals} health goal(s) during this period."
                );
            }
        }

        $symptom_trend = self::get_symptom_trends( $user_id, $time_range );
        if ( ! empty( $symptom_trend['data'] ) ) {
            $change = $symptom_trend['change'];
            if ( $change > 10 ) {
                $insights[] = array(
                    'type' => 'positive',
                    'icon' => '💪',
                    'title' => 'Symptoms Improving',
                    'description' => "Your overall symptom severity has improved by {$change}%. Your health interventions are working!"
                );
            }
        }

        if ( empty( $insights ) ) {
            $insights[] = array(
                'type' => 'neutral',
                'icon' => '📋',
                'title' => 'Keep Tracking',
                'description' => 'Continue logging your health data to see meaningful trends and insights over time.'
            );
        }

        return $insights;
    }

    /**
     * Calculate trend direction
     *
     * @param array $data Data points
     * @return string Trend direction (up, down, stable)
     */
    private static function calculate_trend_direction( $data ) {
        if ( count( $data ) < 2 ) {
            return 'stable';
        }

        $first_half = array_slice( $data, 0, intval( count( $data ) / 2 ) );
        $second_half = array_slice( $data, intval( count( $data ) / 2 ) );

        $first_avg = array_sum( $first_half ) / count( $first_half );
        $second_avg = array_sum( $second_half ) / count( $second_half );

        $change_percentage = ( ( $second_avg - $first_avg ) / $first_avg ) * 100;

        if ( $change_percentage > 5 ) {
            return 'up';
        } elseif ( $change_percentage < -5 ) {
            return 'down';
        } else {
            return 'stable';
        }
    }

    /**
     * Calculate percentage change
     *
     * @param array $data Data points
     * @return float Percentage change
     */
    private static function calculate_percentage_change( $data ) {
        if ( count( $data ) < 2 ) {
            return 0;
        }

        $first_value = reset( $data );
        $last_value = end( $data );

        if ( $first_value == 0 ) {
            return 0;
        }

        return round( ( ( $last_value - $first_value ) / $first_value ) * 100, 1 );
    }
}
