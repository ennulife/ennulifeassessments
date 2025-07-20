<?php
/**
 * Cache Integration
 * 
 * Integrates advanced caching with existing ENNU systems including
 * scoring system, assessment data, biomarkers, and health goals.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Cache_Integration {
    
    private $cache_manager;
    
    public function __construct() {
        global $ennu_cache;
        $this->cache_manager = $ennu_cache;
        $this->setup_hooks();
    }
    
    /**
     * Setup WordPress hooks for cache integration
     */
    private function setup_hooks() {
        add_filter('ennu_get_user_scores', array($this, 'cache_user_scores'), 10, 2);
        add_action('ennu_user_scores_calculated', array($this, 'store_user_scores'), 10, 2);
        
        add_filter('ennu_get_assessment_data', array($this, 'cache_assessment_data'), 10, 2);
        add_action('ennu_assessment_data_updated', array($this, 'store_assessment_data'), 10, 2);
        
        add_filter('ennu_get_biomarker_data', array($this, 'cache_biomarker_data'), 10, 2);
        add_action('ennu_biomarker_data_updated', array($this, 'store_biomarker_data'), 10, 2);
        
        add_filter('ennu_get_health_goals', array($this, 'cache_health_goals'), 10, 2);
        add_action('ennu_health_goals_updated', array($this, 'store_health_goals'), 10, 2);
        
        add_filter('ennu_get_system_config', array($this, 'cache_system_config'), 10, 2);
        add_action('ennu_system_config_updated', array($this, 'invalidate_system_config'), 10, 1);
        
        add_filter('ennu_api_response', array($this, 'cache_api_response'), 10, 3);
        add_action('pre_get_posts', array($this, 'cache_query_results'), 10, 1);
        
        add_action('template_redirect', array($this, 'maybe_cache_page'));
        add_action('wp_footer', array($this, 'store_page_cache'));
        
        add_action('save_post', array($this, 'invalidate_related_cache'), 10, 1);
        add_action('user_register', array($this, 'invalidate_user_cache'), 10, 1);
        add_action('profile_update', array($this, 'invalidate_user_cache'), 10, 1);
    }
    
    /**
     * Cache user scores with intelligent invalidation
     */
    public function cache_user_scores($scores, $user_id) {
        if (!$this->cache_manager || $scores !== null) {
            return $scores;
        }
        
        $cache_key = "user_scores_{$user_id}";
        $cached_scores = $this->cache_manager->get($cache_key, 'user_scores');
        
        if ($cached_scores !== false) {
            return $cached_scores;
        }
        
        return null; // Let the original function calculate scores
    }
    
    /**
     * Store calculated user scores in cache
     */
    public function store_user_scores($scores, $user_id) {
        if (!$this->cache_manager || empty($scores)) {
            return;
        }
        
        $cache_key = "user_scores_{$user_id}";
        
        $cache_data = array(
            'scores' => $scores,
            'calculated_at' => time(),
            'user_id' => $user_id,
            'version' => '1.0'
        );
        
        $this->cache_manager->set($cache_key, $cache_data, 'user_scores');
        
        $this->cache_aggregated_scores($user_id, $scores);
    }
    
    /**
     * Cache assessment data with version control
     */
    public function cache_assessment_data($data, $assessment_id) {
        if (!$this->cache_manager || $data !== null) {
            return $data;
        }
        
        $cache_key = "assessment_data_{$assessment_id}";
        $cached_data = $this->cache_manager->get($cache_key, 'assessment_data');
        
        if ($cached_data !== false) {
            if ($this->is_assessment_cache_valid($cached_data, $assessment_id)) {
                return $cached_data['data'];
            } else {
                $this->cache_manager->delete($cache_key, 'assessment_data');
            }
        }
        
        return null;
    }
    
    /**
     * Store assessment data with metadata
     */
    public function store_assessment_data($data, $assessment_id) {
        if (!$this->cache_manager || empty($data)) {
            return;
        }
        
        $cache_key = "assessment_data_{$assessment_id}";
        
        $cache_data = array(
            'data' => $data,
            'cached_at' => time(),
            'assessment_id' => $assessment_id,
            'checksum' => md5(serialize($data))
        );
        
        $this->cache_manager->set($cache_key, $cache_data, 'assessment_data');
    }
    
    /**
     * Cache biomarker data with intelligent refresh
     */
    public function cache_biomarker_data($data, $user_id) {
        if (!$this->cache_manager || $data !== null) {
            return $data;
        }
        
        $cache_key = "biomarker_data_{$user_id}";
        $cached_data = $this->cache_manager->get($cache_key, 'biomarker_data');
        
        if ($cached_data !== false) {
            $last_update = get_user_meta($user_id, 'biomarker_last_update', true);
            
            if ($cached_data['last_update'] >= $last_update) {
                return $cached_data['data'];
            } else {
                $this->cache_manager->delete($cache_key, 'biomarker_data');
            }
        }
        
        return null;
    }
    
    /**
     * Store biomarker data with update tracking
     */
    public function store_biomarker_data($data, $user_id) {
        if (!$this->cache_manager || empty($data)) {
            return;
        }
        
        $cache_key = "biomarker_data_{$user_id}";
        $last_update = get_user_meta($user_id, 'biomarker_last_update', true);
        
        $cache_data = array(
            'data' => $data,
            'cached_at' => time(),
            'user_id' => $user_id,
            'last_update' => $last_update ?: time()
        );
        
        $this->cache_manager->set($cache_key, $cache_data, 'biomarker_data');
    }
    
    /**
     * Cache health goals with dependency tracking
     */
    public function cache_health_goals($goals, $user_id) {
        if (!$this->cache_manager || $goals !== null) {
            return $goals;
        }
        
        $cache_key = "health_goals_{$user_id}";
        $cached_goals = $this->cache_manager->get($cache_key, 'health_goals');
        
        if ($cached_goals !== false) {
            return $cached_goals['data'];
        }
        
        return null;
    }
    
    /**
     * Store health goals with metadata
     */
    public function store_health_goals($goals, $user_id) {
        if (!$this->cache_manager || empty($goals)) {
            return;
        }
        
        $cache_key = "health_goals_{$user_id}";
        
        $cache_data = array(
            'data' => $goals,
            'cached_at' => time(),
            'user_id' => $user_id,
            'goals_count' => is_array($goals) ? count($goals) : 0
        );
        
        $this->cache_manager->set($cache_key, $cache_data, 'health_goals');
    }
    
    /**
     * Cache system configuration with version control
     */
    public function cache_system_config($config, $config_type) {
        if (!$this->cache_manager || $config !== null) {
            return $config;
        }
        
        $cache_key = "system_config_{$config_type}";
        $cached_config = $this->cache_manager->get($cache_key, 'system_config');
        
        if ($cached_config !== false) {
            $config_file = $this->get_config_file_path($config_type);
            if ($config_file && file_exists($config_file)) {
                $file_mtime = filemtime($config_file);
                if ($cached_config['file_mtime'] >= $file_mtime) {
                    return $cached_config['data'];
                } else {
                    $this->cache_manager->delete($cache_key, 'system_config');
                }
            }
        }
        
        return null;
    }
    
    /**
     * Store system configuration with file tracking
     */
    public function store_system_config($config, $config_type) {
        if (!$this->cache_manager || empty($config)) {
            return;
        }
        
        $cache_key = "system_config_{$config_type}";
        $config_file = $this->get_config_file_path($config_type);
        
        $cache_data = array(
            'data' => $config,
            'cached_at' => time(),
            'config_type' => $config_type,
            'file_mtime' => $config_file && file_exists($config_file) ? filemtime($config_file) : time()
        );
        
        $this->cache_manager->set($cache_key, $cache_data, 'system_config');
    }
    
    /**
     * Cache API responses with TTL management
     */
    public function cache_api_response($response, $endpoint, $params) {
        if (!$this->cache_manager || $response !== null) {
            return $response;
        }
        
        $cache_key = "api_response_" . md5($endpoint . serialize($params));
        $cached_response = $this->cache_manager->get($cache_key, 'api_responses');
        
        if ($cached_response !== false) {
            return $cached_response['data'];
        }
        
        return null;
    }
    
    /**
     * Store API response with metadata
     */
    public function store_api_response($response, $endpoint, $params) {
        if (!$this->cache_manager || empty($response)) {
            return;
        }
        
        $cache_key = "api_response_" . md5($endpoint . serialize($params));
        
        $cache_data = array(
            'data' => $response,
            'cached_at' => time(),
            'endpoint' => $endpoint,
            'params_hash' => md5(serialize($params))
        );
        
        $this->cache_manager->set($cache_key, $cache_data, 'api_responses');
    }
    
    /**
     * Cache database query results
     */
    public function cache_query_results($query) {
        if (!$this->cache_manager || !$query->is_main_query()) {
            return;
        }
        
        if (!$this->should_cache_query($query)) {
            return;
        }
        
        $cache_key = "query_" . md5(serialize($query->query_vars));
        $cached_results = $this->cache_manager->get($cache_key, 'query_cache');
        
        if ($cached_results !== false) {
            $query->posts = $cached_results['posts'];
            $query->post_count = $cached_results['post_count'];
            $query->found_posts = $cached_results['found_posts'];
            $query->max_num_pages = $cached_results['max_num_pages'];
            
            add_filter('posts_pre_query', function() use ($cached_results) {
                return $cached_results['posts'];
            }, 10, 1);
        } else {
            add_action('wp', function() use ($query, $cache_key) {
                if ($query->have_posts()) {
                    $cache_data = array(
                        'posts' => $query->posts,
                        'post_count' => $query->post_count,
                        'found_posts' => $query->found_posts,
                        'max_num_pages' => $query->max_num_pages,
                        'cached_at' => time()
                    );
                    
                    $this->cache_manager->set($cache_key, $cache_data, 'query_cache');
                }
            });
        }
    }
    
    /**
     * Page caching for frontend
     */
    public function maybe_cache_page() {
        if (!$this->cache_manager || is_admin() || is_user_logged_in()) {
            return;
        }
        
        if (!$this->should_cache_page()) {
            return;
        }
        
        $cache_key = "page_" . md5($_SERVER['REQUEST_URI']);
        $cached_page = $this->cache_manager->get($cache_key, 'page_cache');
        
        if ($cached_page !== false) {
            echo $cached_page['content'];
            exit;
        }
        
        ob_start();
    }
    
    /**
     * Store page cache
     */
    public function store_page_cache() {
        if (!$this->cache_manager || is_admin() || is_user_logged_in()) {
            return;
        }
        
        if (!$this->should_cache_page()) {
            return;
        }
        
        $content = ob_get_contents();
        if ($content) {
            $cache_key = "page_" . md5($_SERVER['REQUEST_URI']);
            
            $cache_data = array(
                'content' => $content,
                'cached_at' => time(),
                'uri' => $_SERVER['REQUEST_URI']
            );
            
            $this->cache_manager->set($cache_key, $cache_data, 'page_cache');
        }
    }
    
    /**
     * Cache invalidation methods
     */
    public function invalidate_related_cache($post_id) {
        if (!$this->cache_manager) {
            return;
        }
        
        $this->cache_manager->flush('page_cache');
        
        $this->cache_manager->flush('query_cache');
        
        $post = get_post($post_id);
        if ($post && $post->post_author) {
            $this->invalidate_user_cache($post->post_author);
        }
    }
    
    public function invalidate_user_cache($user_id) {
        if (!$this->cache_manager) {
            return;
        }
        
        $this->cache_manager->delete("user_scores_{$user_id}", 'user_scores');
        $this->cache_manager->delete("user_assessments_{$user_id}", 'assessment_data');
        $this->cache_manager->delete("user_biomarkers_{$user_id}", 'biomarker_data');
        $this->cache_manager->delete("user_health_goals_{$user_id}", 'health_goals');
    }
    
    public function invalidate_system_config($config_type) {
        if (!$this->cache_manager) {
            return;
        }
        
        $this->cache_manager->delete("system_config_{$config_type}", 'system_config');
    }
    
    /**
     * Cache aggregated scores for dashboard
     */
    private function cache_aggregated_scores($user_id, $scores) {
        if (!$this->cache_manager) {
            return;
        }
        
        $aggregated = array(
            'overall_score' => $this->calculate_overall_score($scores),
            'pillar_averages' => $this->calculate_pillar_averages($scores),
            'trend_data' => $this->calculate_trend_data($user_id, $scores),
            'last_updated' => time()
        );
        
        $cache_key = "aggregated_scores_{$user_id}";
        $this->cache_manager->set($cache_key, $aggregated, 'user_scores');
    }
    
    /**
     * Helper methods
     */
    private function is_assessment_cache_valid($cached_data, $assessment_id) {
        $assessment_modified = get_post_modified_time('U', true, $assessment_id);
        return $cached_data['cached_at'] >= $assessment_modified;
    }
    
    private function get_config_file_path($config_type) {
        $config_files = array(
            'pillar_map' => ENNU_PLUGIN_DIR . 'includes/config/scoring/pillar-map.php',
            'health_goals' => ENNU_PLUGIN_DIR . 'includes/config/scoring/health-goals.php',
            'biomarker_profiles' => ENNU_PLUGIN_DIR . 'includes/config/ennu-life-core-biomarkers.php',
            'symptom_map' => ENNU_PLUGIN_DIR . 'includes/config/health-optimization/symptom-map.php',
            'penalty_matrix' => ENNU_PLUGIN_DIR . 'includes/config/health-optimization/penalty-matrix.php'
        );
        
        return $config_files[$config_type] ?? null;
    }
    
    private function should_cache_query($query) {
        $cacheable_post_types = array('post', 'page', 'assessment');
        $post_type = $query->get('post_type');
        
        if ($post_type && !in_array($post_type, $cacheable_post_types)) {
            return false;
        }
        
        if ($query->is_search()) {
            return false;
        }
        
        if (is_admin()) {
            return false;
        }
        
        return true;
    }
    
    private function should_cache_page() {
        if (is_admin()) {
            return false;
        }
        
        if (is_user_logged_in()) {
            return false;
        }
        
        if (is_search()) {
            return false;
        }
        
        if (is_404()) {
            return false;
        }
        
        return is_page() || is_single() || is_home() || is_front_page();
    }
    
    private function calculate_overall_score($scores) {
        if (empty($scores) || !is_array($scores)) {
            return 0;
        }
        
        $total = 0;
        $count = 0;
        
        foreach ($scores as $pillar => $score) {
            if (is_numeric($score)) {
                $total += $score;
                $count++;
            }
        }
        
        return $count > 0 ? round($total / $count, 2) : 0;
    }
    
    private function calculate_pillar_averages($scores) {
        if (empty($scores) || !is_array($scores)) {
            return array();
        }
        
        $averages = array();
        
        foreach ($scores as $pillar => $score) {
            if (is_numeric($score)) {
                $averages[$pillar] = round($score, 2);
            }
        }
        
        return $averages;
    }
    
    private function calculate_trend_data($user_id, $current_scores) {
        $historical_scores = get_user_meta($user_id, 'historical_scores', true);
        
        if (empty($historical_scores) || !is_array($historical_scores)) {
            return array();
        }
        
        $trends = array();
        
        foreach ($current_scores as $pillar => $current_score) {
            if (isset($historical_scores[$pillar]) && is_numeric($current_score)) {
                $previous_score = end($historical_scores[$pillar]);
                $trends[$pillar] = array(
                    'current' => $current_score,
                    'previous' => $previous_score,
                    'change' => $current_score - $previous_score,
                    'direction' => $current_score > $previous_score ? 'up' : ($current_score < $previous_score ? 'down' : 'stable')
                );
            }
        }
        
        return $trends;
    }
}

if (class_exists('ENNU_Cache_Integration')) {
    new ENNU_Cache_Integration();
}
