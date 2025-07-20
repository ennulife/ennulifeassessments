<?php
/**
 * AI/ML Manager
 * 
 * Handles artificial intelligence and machine learning functionality
 * Provides personalized recommendations, predictive analytics, and AI-powered insights
 * 
 * @package ENNU_Life_Assessments
 * @subpackage AI_ML
 * @since 62.2.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_AI_ML_Manager {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * AI/ML enabled flag
     */
    private $ai_ml_enabled = false;
    
    /**
     * Machine learning models cache
     */
    private $models_cache = array();
    
    /**
     * Prediction cache
     */
    private $prediction_cache = array();
    
    /**
     * AI service providers
     */
    private $ai_providers = array();
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->ai_ml_enabled = get_option('ennu_ai_ml_enabled', false);
        
        if ($this->ai_ml_enabled) {
            $this->init_hooks();
            $this->init_ai_providers();
            $this->load_ml_models();
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'setup_ai_context'));
        add_action('wp_loaded', array($this, 'initialize_ml_models'));
        
        add_action('ennu_assessment_completed', array($this, 'process_assessment_ai'), 10, 2);
        add_action('ennu_biomarker_updated', array($this, 'update_health_predictions'), 10, 2);
        add_action('ennu_health_goal_created', array($this, 'generate_goal_recommendations'), 10, 2);
        
        add_filter('ennu_dashboard_recommendations', array($this, 'get_personalized_recommendations'), 10, 2);
        add_filter('ennu_assessment_suggestions', array($this, 'get_assessment_suggestions'), 10, 2);
        add_filter('ennu_biomarker_insights', array($this, 'generate_biomarker_insights'), 10, 2);
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_ennu_train_model', array($this, 'handle_train_model'));
        add_action('wp_ajax_ennu_get_predictions', array($this, 'handle_get_predictions'));
        add_action('wp_ajax_ennu_ai_insights', array($this, 'handle_ai_insights'));
        
        add_action('ennu_daily_ai_training', array($this, 'daily_model_training'));
        add_action('ennu_weekly_ai_optimization', array($this, 'weekly_model_optimization'));
    }
    
    /**
     * Initialize AI service providers
     */
    private function init_ai_providers() {
        $this->ai_providers = array(
            'openai' => array(
                'enabled' => get_option('ennu_openai_enabled', false),
                'api_key' => get_option('ennu_openai_api_key', ''),
                'model' => get_option('ennu_openai_model', 'gpt-3.5-turbo'),
                'endpoint' => 'https://api.openai.com/v1/chat/completions'
            ),
            'local_ml' => array(
                'enabled' => get_option('ennu_local_ml_enabled', true),
                'models_path' => WP_CONTENT_DIR . '/uploads/ennu-ml-models/',
                'python_path' => get_option('ennu_python_path', '/usr/bin/python3')
            ),
            'tensorflow' => array(
                'enabled' => get_option('ennu_tensorflow_enabled', false),
                'model_server' => get_option('ennu_tensorflow_server', 'http://localhost:8501'),
                'models' => array('health_predictor', 'recommendation_engine', 'anomaly_detector')
            )
        );
    }
    
    /**
     * Load machine learning models
     */
    private function load_ml_models() {
        $this->models_cache = array(
            'health_predictor' => $this->load_health_prediction_model(),
            'recommendation_engine' => $this->load_recommendation_model(),
            'anomaly_detector' => $this->load_anomaly_detection_model(),
            'sentiment_analyzer' => $this->load_sentiment_analysis_model(),
            'trend_forecaster' => $this->load_trend_forecasting_model()
        );
    }
    
    /**
     * Setup AI context
     */
    public function setup_ai_context() {
        if (!$this->ai_ml_enabled) {
            return;
        }
        
        $this->setup_user_ai_profile();
        $this->load_personalization_data();
        $this->initialize_recommendation_engine();
    }
    
    /**
     * Initialize ML models
     */
    public function initialize_ml_models() {
        if (!$this->ai_ml_enabled) {
            return;
        }
        
        foreach ($this->models_cache as $model_name => $model) {
            if ($model && method_exists($this, 'warm_up_' . $model_name)) {
                call_user_func(array($this, 'warm_up_' . $model_name));
            }
        }
    }
    
    /**
     * Process assessment with AI
     */
    public function process_assessment_ai($user_id, $assessment_data) {
        if (!$this->ai_ml_enabled) {
            return;
        }
        
        $insights = $this->generate_assessment_insights($user_id, $assessment_data);
        
        $this->update_user_ai_profile($user_id, $assessment_data);
        
        $recommendations = $this->generate_personalized_recommendations($user_id, $assessment_data);
        
        $this->store_ai_analysis($user_id, array(
            'assessment_insights' => $insights,
            'recommendations' => $recommendations,
            'timestamp' => current_time('mysql')
        ));
        
        $this->update_health_predictions($user_id, $assessment_data);
    }
    
    /**
     * Generate assessment insights using AI
     */
    private function generate_assessment_insights($user_id, $assessment_data) {
        $insights = array();
        
        $pattern_analysis = $this->analyze_assessment_patterns($user_id, $assessment_data);
        $insights['patterns'] = $pattern_analysis;
        
        $anomalies = $this->detect_assessment_anomalies($user_id, $assessment_data);
        $insights['anomalies'] = $anomalies;
        
        $suggestions = $this->generate_improvement_suggestions($user_id, $assessment_data);
        $insights['suggestions'] = $suggestions;
        
        $trends = $this->predict_health_trends($user_id, $assessment_data);
        $insights['trends'] = $trends;
        
        return $insights;
    }
    
    /**
     * Analyze assessment patterns
     */
    private function analyze_assessment_patterns($user_id, $assessment_data) {
        $historical_data = $this->get_user_assessment_history($user_id);
        
        if (empty($historical_data)) {
            return array('message' => 'Insufficient data for pattern analysis');
        }
        
        $patterns = array();
        
        $pillar_trends = $this->analyze_pillar_trends($historical_data, $assessment_data);
        $patterns['pillar_trends'] = $pillar_trends;
        
        $frequency_patterns = $this->analyze_assessment_frequency($historical_data);
        $patterns['frequency'] = $frequency_patterns;
        
        $improvement_patterns = $this->analyze_improvement_patterns($historical_data);
        $patterns['improvements'] = $improvement_patterns;
        
        $seasonal_patterns = $this->analyze_seasonal_patterns($historical_data);
        $patterns['seasonal'] = $seasonal_patterns;
        
        return $patterns;
    }
    
    /**
     * Detect assessment anomalies
     */
    private function detect_assessment_anomalies($user_id, $assessment_data) {
        $anomaly_model = $this->models_cache['anomaly_detector'];
        
        if (!$anomaly_model) {
            return array('message' => 'Anomaly detection model not available');
        }
        
        $anomalies = array();
        
        $features = $this->prepare_anomaly_features($user_id, $assessment_data);
        
        $anomaly_scores = $this->run_anomaly_detection($features);
        
        foreach ($anomaly_scores as $feature => $score) {
            if ($score > 0.7) { // High anomaly threshold
                $anomalies[] = array(
                    'feature' => $feature,
                    'score' => $score,
                    'severity' => $this->get_anomaly_severity($score),
                    'description' => $this->get_anomaly_description($feature, $score),
                    'recommendations' => $this->get_anomaly_recommendations($feature, $score)
                );
            }
        }
        
        return $anomalies;
    }
    
    /**
     * Generate improvement suggestions
     */
    private function generate_improvement_suggestions($user_id, $assessment_data) {
        $suggestions = array();
        
        $score_analysis = $this->analyze_score_gaps($assessment_data);
        
        foreach ($score_analysis as $pillar => $analysis) {
            if ($analysis['gap'] > 10) { // Significant improvement opportunity
                $pillar_suggestions = $this->generate_pillar_suggestions($pillar, $analysis);
                $suggestions[$pillar] = $pillar_suggestions;
            }
        }
        
        $lifestyle_suggestions = $this->generate_lifestyle_suggestions($user_id, $assessment_data);
        $suggestions['lifestyle'] = $lifestyle_suggestions;
        
        $biomarker_suggestions = $this->generate_biomarker_suggestions($user_id, $assessment_data);
        $suggestions['biomarkers'] = $biomarker_suggestions;
        
        return $suggestions;
    }
    
    /**
     * Predict health trends
     */
    private function predict_health_trends($user_id, $assessment_data) {
        $trend_model = $this->models_cache['trend_forecaster'];
        
        if (!$trend_model) {
            return array('message' => 'Trend forecasting model not available');
        }
        
        $trends = array();
        
        $historical_data = $this->get_user_assessment_history($user_id, 12); // Last 12 assessments
        
        if (count($historical_data) < 3) {
            return array('message' => 'Insufficient data for trend prediction');
        }
        
        $trend_features = $this->prepare_trend_features($historical_data, $assessment_data);
        
        foreach (array('physical', 'mental', 'emotional', 'spiritual') as $pillar) {
            $pillar_trends = $this->predict_pillar_trends($pillar, $trend_features);
            $trends[$pillar] = $pillar_trends;
        }
        
        $overall_trajectory = $this->predict_overall_trajectory($trend_features);
        $trends['overall'] = $overall_trajectory;
        
        return $trends;
    }
    
    /**
     * Get personalized recommendations
     */
    public function get_personalized_recommendations($recommendations, $user_id) {
        if (!$this->ai_ml_enabled) {
            return $recommendations;
        }
        
        $recommendation_model = $this->models_cache['recommendation_engine'];
        
        if (!$recommendation_model) {
            return $recommendations;
        }
        
        $user_profile = $this->get_user_ai_profile($user_id);
        
        $assessment_history = $this->get_user_assessment_history($user_id);
        
        $biomarker_data = $this->get_user_biomarker_data($user_id);
        
        $ai_recommendations = $this->generate_ai_recommendations($user_profile, $assessment_history, $biomarker_data);
        
        $personalized_recommendations = array_merge($recommendations, $ai_recommendations);
        
        $ranked_recommendations = $this->rank_recommendations($personalized_recommendations, $user_profile);
        
        return $ranked_recommendations;
    }
    
    /**
     * Generate AI recommendations
     */
    private function generate_ai_recommendations($user_profile, $assessment_history, $biomarker_data) {
        $recommendations = array();
        
        $health_recommendations = $this->generate_health_optimization_recommendations($user_profile, $assessment_history);
        $recommendations['health_optimization'] = $health_recommendations;
        
        $lifestyle_recommendations = $this->generate_ai_lifestyle_recommendations($user_profile, $biomarker_data);
        $recommendations['lifestyle'] = $lifestyle_recommendations;
        
        $timing_recommendations = $this->generate_assessment_timing_recommendations($assessment_history);
        $recommendations['assessment_timing'] = $timing_recommendations;
        
        $goal_recommendations = $this->generate_goal_setting_recommendations($user_profile, $assessment_history);
        $recommendations['goal_setting'] = $goal_recommendations;
        
        $intervention_recommendations = $this->generate_intervention_recommendations($user_profile, $biomarker_data);
        $recommendations['interventions'] = $intervention_recommendations;
        
        return $recommendations;
    }
    
    /**
     * Generate biomarker insights
     */
    public function generate_biomarker_insights($insights, $biomarker_data) {
        if (!$this->ai_ml_enabled) {
            return $insights;
        }
        
        $ai_insights = array();
        
        $pattern_insights = $this->analyze_biomarker_patterns($biomarker_data);
        $ai_insights['patterns'] = $pattern_insights;
        
        $anomaly_insights = $this->detect_biomarker_anomalies($biomarker_data);
        $ai_insights['anomalies'] = $anomaly_insights;
        
        $correlation_insights = $this->analyze_biomarker_correlations($biomarker_data);
        $ai_insights['correlations'] = $correlation_insights;
        
        $trend_insights = $this->predict_biomarker_trends($biomarker_data);
        $ai_insights['trends'] = $trend_insights;
        
        $optimization_insights = $this->generate_biomarker_optimization_suggestions($biomarker_data);
        $ai_insights['optimization'] = $optimization_insights;
        
        return array_merge($insights, $ai_insights);
    }
    
    /**
     * Update health predictions
     */
    public function update_health_predictions($user_id, $data) {
        if (!$this->ai_ml_enabled) {
            return;
        }
        
        $health_model = $this->models_cache['health_predictor'];
        
        if (!$health_model) {
            return;
        }
        
        $features = $this->prepare_prediction_features($user_id, $data);
        
        $predictions = $this->generate_health_predictions($features);
        
        $this->store_health_predictions($user_id, $predictions);
        
        $this->check_prediction_alerts($user_id, $predictions);
    }
    
    /**
     * Generate health predictions
     */
    private function generate_health_predictions($features) {
        $predictions = array();
        
        $risk_predictions = $this->predict_health_risks($features);
        $predictions['risks'] = $risk_predictions;
        
        $improvement_predictions = $this->predict_improvement_opportunities($features);
        $predictions['improvements'] = $improvement_predictions;
        
        $timing_predictions = $this->predict_intervention_timing($features);
        $predictions['timing'] = $timing_predictions;
        
        $goal_predictions = $this->predict_goal_achievement($features);
        $predictions['goals'] = $goal_predictions;
        
        return $predictions;
    }
    
    /**
     * Train machine learning models
     */
    public function train_models($model_type = 'all') {
        if (!$this->ai_ml_enabled) {
            return false;
        }
        
        $training_results = array();
        
        if ($model_type === 'all' || $model_type === 'health_predictor') {
            $training_results['health_predictor'] = $this->train_health_prediction_model();
        }
        
        if ($model_type === 'all' || $model_type === 'recommendation_engine') {
            $training_results['recommendation_engine'] = $this->train_recommendation_model();
        }
        
        if ($model_type === 'all' || $model_type === 'anomaly_detector') {
            $training_results['anomaly_detector'] = $this->train_anomaly_detection_model();
        }
        
        if ($model_type === 'all' || $model_type === 'sentiment_analyzer') {
            $training_results['sentiment_analyzer'] = $this->train_sentiment_analysis_model();
        }
        
        if ($model_type === 'all' || $model_type === 'trend_forecaster') {
            $training_results['trend_forecaster'] = $this->train_trend_forecasting_model();
        }
        
        return $training_results;
    }
    
    /**
     * Load health prediction model
     */
    private function load_health_prediction_model() {
        $model_path = $this->ai_providers['local_ml']['models_path'] . 'health_predictor.pkl';
        
        if (!file_exists($model_path)) {
            return $this->create_initial_health_prediction_model();
        }
        
        return $this->load_pickle_model($model_path);
    }
    
    /**
     * Load recommendation model
     */
    private function load_recommendation_model() {
        $model_path = $this->ai_providers['local_ml']['models_path'] . 'recommendation_engine.pkl';
        
        if (!file_exists($model_path)) {
            return $this->create_initial_recommendation_model();
        }
        
        return $this->load_pickle_model($model_path);
    }
    
    /**
     * Load anomaly detection model
     */
    private function load_anomaly_detection_model() {
        $model_path = $this->ai_providers['local_ml']['models_path'] . 'anomaly_detector.pkl';
        
        if (!file_exists($model_path)) {
            return $this->create_initial_anomaly_detection_model();
        }
        
        return $this->load_pickle_model($model_path);
    }
    
    /**
     * Load sentiment analysis model
     */
    private function load_sentiment_analysis_model() {
        $model_path = $this->ai_providers['local_ml']['models_path'] . 'sentiment_analyzer.pkl';
        
        if (!file_exists($model_path)) {
            return $this->create_initial_sentiment_analysis_model();
        }
        
        return $this->load_pickle_model($model_path);
    }
    
    /**
     * Load trend forecasting model
     */
    private function load_trend_forecasting_model() {
        $model_path = $this->ai_providers['local_ml']['models_path'] . 'trend_forecaster.pkl';
        
        if (!file_exists($model_path)) {
            return $this->create_initial_trend_forecasting_model();
        }
        
        return $this->load_pickle_model($model_path);
    }
    
    /**
     * Get user AI profile
     */
    private function get_user_ai_profile($user_id) {
        $profile = get_user_meta($user_id, 'ennu_ai_profile', true);
        
        if (empty($profile)) {
            $profile = $this->create_default_ai_profile($user_id);
        }
        
        return $profile;
    }
    
    /**
     * Create default AI profile
     */
    private function create_default_ai_profile($user_id) {
        $profile = array(
            'user_id' => $user_id,
            'preferences' => array(
                'recommendation_frequency' => 'weekly',
                'insight_level' => 'intermediate',
                'focus_areas' => array('physical', 'mental'),
                'notification_preferences' => array('email', 'dashboard')
            ),
            'learning_style' => 'visual',
            'engagement_level' => 'medium',
            'health_goals' => array(),
            'risk_tolerance' => 'moderate',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        
        update_user_meta($user_id, 'ennu_ai_profile', $profile);
        
        return $profile;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_submenu_page(
            'ennu-life-assessments',
            __('AI/ML Management', 'ennu-life-assessments'),
            __('AI/ML', 'ennu-life-assessments'),
            'manage_options',
            'ennu-ai-ml',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('AI/ML Management', 'ennu-life-assessments'); ?></h1>
            
            <div class="ennu-ai-ml-admin">
                <div class="ai-status">
                    <h2><?php _e('AI/ML Status', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_ai_status(); ?>
                </div>
                
                <div class="model-management">
                    <h2><?php _e('Model Management', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_model_management(); ?>
                </div>
                
                <div class="ai-insights">
                    <h2><?php _e('AI Insights Dashboard', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_ai_insights_dashboard(); ?>
                </div>
                
                <div class="ai-settings">
                    <h2><?php _e('AI/ML Settings', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_ai_settings(); ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render AI status
     */
    private function render_ai_status() {
        $status = array(
            'ai_enabled' => $this->ai_ml_enabled,
            'models_loaded' => count(array_filter($this->models_cache)),
            'providers_active' => count(array_filter($this->ai_providers, function($provider) {
                return $provider['enabled'];
            })),
            'last_training' => get_option('ennu_last_model_training', 'Never'),
            'prediction_accuracy' => get_option('ennu_prediction_accuracy', 'N/A')
        );
        
        ?>
        <div class="ai-status-grid">
            <div class="status-card">
                <h3><?php _e('AI/ML Status', 'ennu-life-assessments'); ?></h3>
                <p class="status-<?php echo $status['ai_enabled'] ? 'enabled' : 'disabled'; ?>">
                    <?php echo $status['ai_enabled'] ? __('Enabled', 'ennu-life-assessments') : __('Disabled', 'ennu-life-assessments'); ?>
                </p>
            </div>
            
            <div class="status-card">
                <h3><?php _e('Models Loaded', 'ennu-life-assessments'); ?></h3>
                <p><?php echo esc_html($status['models_loaded']); ?>/5</p>
            </div>
            
            <div class="status-card">
                <h3><?php _e('Active Providers', 'ennu-life-assessments'); ?></h3>
                <p><?php echo esc_html($status['providers_active']); ?>/3</p>
            </div>
            
            <div class="status-card">
                <h3><?php _e('Last Training', 'ennu-life-assessments'); ?></h3>
                <p><?php echo esc_html($status['last_training']); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render model management
     */
    private function render_model_management() {
        ?>
        <div class="model-management-controls">
            <button type="button" class="button button-primary" id="train-all-models">
                <?php _e('Train All Models', 'ennu-life-assessments'); ?>
            </button>
            <button type="button" class="button" id="validate-models">
                <?php _e('Validate Models', 'ennu-life-assessments'); ?>
            </button>
            <button type="button" class="button" id="export-models">
                <?php _e('Export Models', 'ennu-life-assessments'); ?>
            </button>
        </div>
        
        <div class="models-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Model', 'ennu-life-assessments'); ?></th>
                        <th><?php _e('Status', 'ennu-life-assessments'); ?></th>
                        <th><?php _e('Accuracy', 'ennu-life-assessments'); ?></th>
                        <th><?php _e('Last Trained', 'ennu-life-assessments'); ?></th>
                        <th><?php _e('Actions', 'ennu-life-assessments'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->models_cache as $model_name => $model): ?>
                    <tr>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $model_name))); ?></td>
                        <td>
                            <span class="status-<?php echo $model ? 'loaded' : 'not-loaded'; ?>">
                                <?php echo $model ? __('Loaded', 'ennu-life-assessments') : __('Not Loaded', 'ennu-life-assessments'); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(get_option('ennu_' . $model_name . '_accuracy', 'N/A')); ?></td>
                        <td><?php echo esc_html(get_option('ennu_' . $model_name . '_last_trained', 'Never')); ?></td>
                        <td>
                            <button type="button" class="button button-small train-model" data-model="<?php echo esc_attr($model_name); ?>">
                                <?php _e('Train', 'ennu-life-assessments'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render AI insights dashboard
     */
    private function render_ai_insights_dashboard() {
        ?>
        <div class="ai-insights-dashboard">
            <div class="insights-grid">
                <div class="insight-card">
                    <h3><?php _e('User Engagement Insights', 'ennu-life-assessments'); ?></h3>
                    <div id="engagement-insights"></div>
                </div>
                
                <div class="insight-card">
                    <h3><?php _e('Health Trend Predictions', 'ennu-life-assessments'); ?></h3>
                    <div id="trend-predictions"></div>
                </div>
                
                <div class="insight-card">
                    <h3><?php _e('Recommendation Effectiveness', 'ennu-life-assessments'); ?></h3>
                    <div id="recommendation-effectiveness"></div>
                </div>
                
                <div class="insight-card">
                    <h3><?php _e('Anomaly Detection Results', 'ennu-life-assessments'); ?></h3>
                    <div id="anomaly-results"></div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render AI settings
     */
    private function render_ai_settings() {
        $ai_enabled = get_option('ennu_ai_ml_enabled', false);
        $openai_enabled = get_option('ennu_openai_enabled', false);
        $local_ml_enabled = get_option('ennu_local_ml_enabled', true);
        $tensorflow_enabled = get_option('ennu_tensorflow_enabled', false);
        
        ?>
        <form method="post" action="options.php">
            <?php settings_fields('ennu_ai_ml_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable AI/ML', 'ennu-life-assessments'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="ennu_ai_ml_enabled" value="1" <?php checked($ai_enabled); ?> />
                            <?php _e('Enable AI/ML functionality', 'ennu-life-assessments'); ?>
                        </label>
                        <p class="description"><?php _e('Enable artificial intelligence and machine learning features.', 'ennu-life-assessments'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('OpenAI Integration', 'ennu-life-assessments'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="ennu_openai_enabled" value="1" <?php checked($openai_enabled); ?> />
                            <?php _e('Enable OpenAI integration', 'ennu-life-assessments'); ?>
                        </label>
                        <br><br>
                        <label for="ennu_openai_api_key"><?php _e('API Key:', 'ennu-life-assessments'); ?></label>
                        <input type="password" id="ennu_openai_api_key" name="ennu_openai_api_key" value="<?php echo esc_attr(get_option('ennu_openai_api_key', '')); ?>" class="regular-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Local ML', 'ennu-life-assessments'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="ennu_local_ml_enabled" value="1" <?php checked($local_ml_enabled); ?> />
                            <?php _e('Enable local machine learning', 'ennu-life-assessments'); ?>
                        </label>
                        <p class="description"><?php _e('Use local Python-based machine learning models.', 'ennu-life-assessments'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('TensorFlow Serving', 'ennu-life-assessments'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="ennu_tensorflow_enabled" value="1" <?php checked($tensorflow_enabled); ?> />
                            <?php _e('Enable TensorFlow Serving integration', 'ennu-life-assessments'); ?>
                        </label>
                        <br><br>
                        <label for="ennu_tensorflow_server"><?php _e('Server URL:', 'ennu-life-assessments'); ?></label>
                        <input type="url" id="ennu_tensorflow_server" name="ennu_tensorflow_server" value="<?php echo esc_attr(get_option('ennu_tensorflow_server', 'http://localhost:8501')); ?>" class="regular-text" />
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }
    
    /**
     * Handle train model AJAX
     */
    public function handle_train_model() {
        check_ajax_referer('ennu_ai_ml_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $model_type = sanitize_text_field($_POST['model_type']);
        
        $result = $this->train_models($model_type);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Model training completed', 'results' => $result));
        } else {
            wp_send_json_error(array('message' => 'Model training failed'));
        }
    }
    
    /**
     * Handle get predictions AJAX
     */
    public function handle_get_predictions() {
        check_ajax_referer('ennu_ai_ml_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $user_id = intval($_POST['user_id']);
        $prediction_type = sanitize_text_field($_POST['prediction_type']);
        
        $predictions = $this->get_user_predictions($user_id, $prediction_type);
        
        wp_send_json_success(array('predictions' => $predictions));
    }
    
    /**
     * Handle AI insights AJAX
     */
    public function handle_ai_insights() {
        check_ajax_referer('ennu_ai_ml_nonce', 'nonce');
        
        if (!current_user_can('read')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }
        
        $insight_type = sanitize_text_field($_POST['insight_type']);
        
        $insights = $this->get_ai_insights($insight_type);
        
        wp_send_json_success(array('insights' => $insights));
    }
    
    /**
     * Daily model training
     */
    public function daily_model_training() {
        if (!$this->ai_ml_enabled) {
            return;
        }
        
        $this->train_models('all');
        
        $this->update_model_performance_metrics();
        
        $this->cleanup_old_predictions();
        
        update_option('ennu_last_model_training', current_time('mysql'));
    }
    
    /**
     * Weekly model optimization
     */
    public function weekly_model_optimization() {
        if (!$this->ai_ml_enabled) {
            return;
        }
        
        $this->optimize_model_hyperparameters();
        
        $this->validate_model_performance();
        
        $this->generate_model_performance_reports();
    }
    
    /**
     * Get user predictions
     */
    private function get_user_predictions($user_id, $prediction_type = 'all') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_ai_predictions';
        
        $where_clause = "user_id = %d";
        $params = array($user_id);
        
        if ($prediction_type !== 'all') {
            $where_clause .= " AND prediction_type = %s";
            $params[] = $prediction_type;
        }
        
        $predictions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE $where_clause ORDER BY created_at DESC LIMIT 10",
            $params
        ));
        
        return $predictions;
    }
    
    /**
     * Store AI analysis
     */
    private function store_ai_analysis($user_id, $analysis_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_ai_analysis';
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'analysis_type' => 'assessment',
                'analysis_data' => json_encode($analysis_data),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s')
        );
    }
    
    /**
     * Store health predictions
     */
    private function store_health_predictions($user_id, $predictions) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_ai_predictions';
        
        foreach ($predictions as $prediction_type => $prediction_data) {
            $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'prediction_type' => $prediction_type,
                    'prediction_data' => json_encode($prediction_data),
                    'confidence_score' => $prediction_data['confidence'] ?? 0.5,
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%s', '%s', '%f', '%s')
            );
        }
    }
    
    /**
     * Create database tables for AI/ML
     */
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $analysis_table = $wpdb->prefix . 'ennu_ai_analysis';
        $analysis_sql = "CREATE TABLE $analysis_table (
            analysis_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            analysis_type varchar(50) NOT NULL,
            analysis_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (analysis_id),
            KEY user_id (user_id),
            KEY analysis_type (analysis_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $predictions_table = $wpdb->prefix . 'ennu_ai_predictions';
        $predictions_sql = "CREATE TABLE $predictions_table (
            prediction_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            prediction_type varchar(50) NOT NULL,
            prediction_data longtext,
            confidence_score decimal(3,2) DEFAULT 0.50,
            accuracy_score decimal(3,2) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            validated_at datetime DEFAULT NULL,
            PRIMARY KEY (prediction_id),
            KEY user_id (user_id),
            KEY prediction_type (prediction_type),
            KEY confidence_score (confidence_score),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $models_table = $wpdb->prefix . 'ennu_ai_models';
        $models_sql = "CREATE TABLE $models_table (
            model_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            model_name varchar(100) NOT NULL,
            model_type varchar(50) NOT NULL,
            model_version varchar(20) DEFAULT '1.0',
            model_path varchar(255),
            performance_metrics longtext,
            training_data_size int DEFAULT 0,
            last_trained datetime DEFAULT NULL,
            status enum('active','inactive','training') DEFAULT 'inactive',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (model_id),
            UNIQUE KEY model_name (model_name),
            KEY model_type (model_type),
            KEY status (status),
            KEY last_trained (last_trained)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($analysis_sql);
        dbDelta($predictions_sql);
        dbDelta($models_sql);
    }
}

ENNU_AI_ML_Manager::get_instance();
