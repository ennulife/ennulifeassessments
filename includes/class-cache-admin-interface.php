<?php
/**
 * Cache Admin Interface
 * 
 * Administrative interface for cache management, monitoring, and optimization.
 * Provides real-time cache statistics, manual cache controls, and performance insights.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Cache_Admin_Interface {
    
    private $cache_manager;
    
    public function __construct() {
        $this->setup_hooks();
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_ennu_cache_action', array($this, 'handle_cache_actions'));
        add_action('wp_ajax_ennu_cache_stats', array($this, 'get_cache_stats_ajax'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'ennu-life-assessments',
            'Cache Management',
            'Cache',
            'manage_options',
            'ennu-cache',
            array($this, 'render_cache_page')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ennu-cache') !== false) {
            wp_enqueue_script('ennu-cache-admin', plugin_dir_url(__FILE__) . '../assets/js/cache-admin.js', array('jquery'), '1.0.0', true);
            wp_enqueue_style('ennu-cache-admin', plugin_dir_url(__FILE__) . '../assets/css/cache-admin.css', array(), '1.0.0');
            
            wp_localize_script('ennu-cache-admin', 'ennuCache', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ennu_cache_nonce'),
                'strings' => array(
                    'confirm_flush' => __('Are you sure you want to flush all cache?', 'ennu-life-assessments'),
                    'confirm_warm' => __('Are you sure you want to warm the cache?', 'ennu-life-assessments'),
                    'cache_flushed' => __('Cache flushed successfully', 'ennu-life-assessments'),
                    'cache_warmed' => __('Cache warming started', 'ennu-life-assessments'),
                    'action_error' => __('An error occurred while performing the action', 'ennu-life-assessments')
                )
            ));
        }
    }
    
    /**
     * Render cache management page
     */
    public function render_cache_page() {
        $active_tab = $_GET['tab'] ?? 'dashboard';
        
        ?>
        <div class="wrap">
            <h1><?php _e('ENNU Cache Management', 'ennu-life-assessments'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=ennu-cache&tab=dashboard" class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Dashboard', 'ennu-life-assessments'); ?>
                </a>
                <a href="?page=ennu-cache&tab=statistics" class="nav-tab <?php echo $active_tab === 'statistics' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Statistics', 'ennu-life-assessments'); ?>
                </a>
                <a href="?page=ennu-cache&tab=management" class="nav-tab <?php echo $active_tab === 'management' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Management', 'ennu-life-assessments'); ?>
                </a>
                <a href="?page=ennu-cache&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Settings', 'ennu-life-assessments'); ?>
                </a>
            </nav>
            
            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'dashboard':
                        $this->render_dashboard_tab();
                        break;
                    case 'statistics':
                        $this->render_statistics_tab();
                        break;
                    case 'management':
                        $this->render_management_tab();
                        break;
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render dashboard tab
     */
    private function render_dashboard_tab() {
        global $ennu_cache;
        
        $cache_info = $ennu_cache ? $ennu_cache->get_cache_info() : array();
        $cache_stats = $ennu_cache ? $ennu_cache->get_cache_stats() : array();
        
        ?>
        <div class="cache-dashboard">
            <div class="cache-status-grid">
                <div class="cache-status-card">
                    <h3><?php _e('Cache Status', 'ennu-life-assessments'); ?></h3>
                    <div class="status-indicator <?php echo $cache_info['redis_available'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php if ($cache_info['redis_available']): ?>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('Redis Active', 'ennu-life-assessments'); ?>
                        <?php elseif ($cache_info['memcached_available']): ?>
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php _e('Memcached Active', 'ennu-life-assessments'); ?>
                        <?php else: ?>
                            <span class="dashicons dashicons-warning"></span>
                            <?php _e('Transients Only', 'ennu-life-assessments'); ?>
                        <?php endif; ?>
                    </div>
                    <p><?php printf(__('Active Client: %s', 'ennu-life-assessments'), ucfirst($cache_info['active_client'] ?? 'unknown')); ?></p>
                </div>
                
                <div class="cache-status-card">
                    <h3><?php _e('Overall Hit Rate', 'ennu-life-assessments'); ?></h3>
                    <div class="stat-number">
                        <?php echo $this->calculate_overall_hit_rate($cache_stats); ?>%
                    </div>
                    <p><?php _e('Cache effectiveness', 'ennu-life-assessments'); ?></p>
                </div>
                
                <div class="cache-status-card">
                    <h3><?php _e('Total Requests', 'ennu-life-assessments'); ?></h3>
                    <div class="stat-number">
                        <?php echo $this->calculate_total_requests($cache_stats); ?>
                    </div>
                    <p><?php _e('Cache requests today', 'ennu-life-assessments'); ?></p>
                </div>
                
                <div class="cache-status-card">
                    <h3><?php _e('Average Response Time', 'ennu-life-assessments'); ?></h3>
                    <div class="stat-number">
                        <?php echo $this->calculate_avg_response_time($cache_stats); ?>ms
                    </div>
                    <p><?php _e('Cache operation speed', 'ennu-life-assessments'); ?></p>
                </div>
            </div>
            
            <div class="cache-groups-overview">
                <h3><?php _e('Cache Groups Performance', 'ennu-life-assessments'); ?></h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Group', 'ennu-life-assessments'); ?></th>
                            <th><?php _e('Hit Rate', 'ennu-life-assessments'); ?></th>
                            <th><?php _e('Hits', 'ennu-life-assessments'); ?></th>
                            <th><?php _e('Misses', 'ennu-life-assessments'); ?></th>
                            <th><?php _e('Avg Time', 'ennu-life-assessments'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cache_stats as $group => $stats): ?>
                            <tr>
                                <td><?php echo esc_html($group); ?></td>
                                <td>
                                    <div class="hit-rate-bar">
                                        <div class="hit-rate-fill" style="width: <?php echo $stats['hit_rate']; ?>%"></div>
                                        <span><?php echo number_format($stats['hit_rate'], 1); ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo number_format($stats['hits']); ?></td>
                                <td><?php echo number_format($stats['misses']); ?></td>
                                <td><?php echo number_format($stats['avg_time'] * 1000, 2); ?>ms</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="cache-quick-actions">
                <h3><?php _e('Quick Actions', 'ennu-life-assessments'); ?></h3>
                <div class="quick-actions-grid">
                    <button class="button button-primary" onclick="ennuCache.warmCache()">
                        <?php _e('Warm Cache', 'ennu-life-assessments'); ?>
                    </button>
                    <button class="button button-secondary" onclick="ennuCache.flushCache()">
                        <?php _e('Flush All Cache', 'ennu-life-assessments'); ?>
                    </button>
                    <button class="button button-secondary" onclick="ennuCache.refreshStats()">
                        <?php _e('Refresh Statistics', 'ennu-life-assessments'); ?>
                    </button>
                    <button class="button button-secondary" onclick="ennuCache.exportStats()">
                        <?php _e('Export Statistics', 'ennu-life-assessments'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render statistics tab
     */
    private function render_statistics_tab() {
        ?>
        <div class="cache-statistics">
            <div class="stats-filters">
                <form method="get" action="">
                    <input type="hidden" name="page" value="ennu-cache" />
                    <input type="hidden" name="tab" value="statistics" />
                    
                    <select name="period">
                        <option value="today"><?php _e('Today', 'ennu-life-assessments'); ?></option>
                        <option value="week"><?php _e('Last 7 Days', 'ennu-life-assessments'); ?></option>
                        <option value="month"><?php _e('Last 30 Days', 'ennu-life-assessments'); ?></option>
                    </select>
                    
                    <select name="group">
                        <option value=""><?php _e('All Groups', 'ennu-life-assessments'); ?></option>
                        <option value="user_scores"><?php _e('User Scores', 'ennu-life-assessments'); ?></option>
                        <option value="assessment_data"><?php _e('Assessment Data', 'ennu-life-assessments'); ?></option>
                        <option value="biomarker_data"><?php _e('Biomarker Data', 'ennu-life-assessments'); ?></option>
                        <option value="health_goals"><?php _e('Health Goals', 'ennu-life-assessments'); ?></option>
                    </select>
                    
                    <button type="submit" class="button"><?php _e('Filter', 'ennu-life-assessments'); ?></button>
                </form>
            </div>
            
            <div class="stats-charts">
                <div class="chart-container">
                    <h4><?php _e('Hit Rate Trend', 'ennu-life-assessments'); ?></h4>
                    <canvas id="hitRateChart" width="400" height="200"></canvas>
                </div>
                
                <div class="chart-container">
                    <h4><?php _e('Response Time Trend', 'ennu-life-assessments'); ?></h4>
                    <canvas id="responseTimeChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="detailed-stats">
                <h4><?php _e('Detailed Statistics', 'ennu-life-assessments'); ?></h4>
                <?php $this->render_detailed_statistics(); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render management tab
     */
    private function render_management_tab() {
        ?>
        <div class="cache-management">
            <div class="management-section">
                <h3><?php _e('Cache Operations', 'ennu-life-assessments'); ?></h3>
                
                <div class="operation-grid">
                    <div class="operation-card">
                        <h4><?php _e('Flush Cache by Group', 'ennu-life-assessments'); ?></h4>
                        <select id="flush-group">
                            <option value=""><?php _e('Select Group', 'ennu-life-assessments'); ?></option>
                            <option value="user_scores"><?php _e('User Scores', 'ennu-life-assessments'); ?></option>
                            <option value="assessment_data"><?php _e('Assessment Data', 'ennu-life-assessments'); ?></option>
                            <option value="biomarker_data"><?php _e('Biomarker Data', 'ennu-life-assessments'); ?></option>
                            <option value="health_goals"><?php _e('Health Goals', 'ennu-life-assessments'); ?></option>
                            <option value="system_config"><?php _e('System Config', 'ennu-life-assessments'); ?></option>
                        </select>
                        <button class="button" onclick="ennuCache.flushGroup()">
                            <?php _e('Flush Group', 'ennu-life-assessments'); ?>
                        </button>
                    </div>
                    
                    <div class="operation-card">
                        <h4><?php _e('Warm Specific Cache', 'ennu-life-assessments'); ?></h4>
                        <select id="warm-type">
                            <option value=""><?php _e('Select Type', 'ennu-life-assessments'); ?></option>
                            <option value="user_scores"><?php _e('User Scores', 'ennu-life-assessments'); ?></option>
                            <option value="system_config"><?php _e('System Config', 'ennu-life-assessments'); ?></option>
                            <option value="assessment_config"><?php _e('Assessment Config', 'ennu-life-assessments'); ?></option>
                            <option value="biomarker_profiles"><?php _e('Biomarker Profiles', 'ennu-life-assessments'); ?></option>
                        </select>
                        <button class="button" onclick="ennuCache.warmSpecific()">
                            <?php _e('Warm Cache', 'ennu-life-assessments'); ?>
                        </button>
                    </div>
                    
                    <div class="operation-card">
                        <h4><?php _e('Cache Maintenance', 'ennu-life-assessments'); ?></h4>
                        <button class="button" onclick="ennuCache.cleanupExpired()">
                            <?php _e('Cleanup Expired', 'ennu-life-assessments'); ?>
                        </button>
                        <button class="button" onclick="ennuCache.optimizeCache()">
                            <?php _e('Optimize Cache', 'ennu-life-assessments'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="management-section">
                <h3><?php _e('Cache Monitoring', 'ennu-life-assessments'); ?></h3>
                
                <div class="monitoring-grid">
                    <div class="monitor-card">
                        <h4><?php _e('Memory Usage', 'ennu-life-assessments'); ?></h4>
                        <div class="memory-usage">
                            <?php $this->render_memory_usage(); ?>
                        </div>
                    </div>
                    
                    <div class="monitor-card">
                        <h4><?php _e('Cache Size', 'ennu-life-assessments'); ?></h4>
                        <div class="cache-size">
                            <?php $this->render_cache_size(); ?>
                        </div>
                    </div>
                    
                    <div class="monitor-card">
                        <h4><?php _e('Connection Status', 'ennu-life-assessments'); ?></h4>
                        <div class="connection-status">
                            <?php $this->render_connection_status(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings tab
     */
    private function render_settings_tab() {
        if (isset($_POST['save_cache_settings'])) {
            $this->save_cache_settings();
        }
        
        $settings = get_option('ennu_cache_settings', array());
        
        ?>
        <div class="cache-settings">
            <form method="post" action="">
                <?php wp_nonce_field('ennu_cache_settings', 'cache_settings_nonce'); ?>
                
                <h3><?php _e('Cache Configuration', 'ennu-life-assessments'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label for="default_ttl"><?php _e('Default TTL (seconds)', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="default_ttl" name="default_ttl" value="<?php echo esc_attr($settings['default_ttl'] ?? 3600); ?>" min="60" max="86400" /></td>
                    </tr>
                    <tr>
                        <th><label for="enable_cache_warming"><?php _e('Enable Cache Warming', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="checkbox" id="enable_cache_warming" name="enable_cache_warming" value="1" <?php checked($settings['enable_cache_warming'] ?? true); ?> /></td>
                    </tr>
                    <tr>
                        <th><label for="cache_warming_interval"><?php _e('Cache Warming Interval', 'ennu-life-assessments'); ?></label></th>
                        <td>
                            <select id="cache_warming_interval" name="cache_warming_interval">
                                <option value="hourly" <?php selected($settings['cache_warming_interval'] ?? 'twicedaily', 'hourly'); ?>><?php _e('Hourly', 'ennu-life-assessments'); ?></option>
                                <option value="twicedaily" <?php selected($settings['cache_warming_interval'] ?? 'twicedaily', 'twicedaily'); ?>><?php _e('Twice Daily', 'ennu-life-assessments'); ?></option>
                                <option value="daily" <?php selected($settings['cache_warming_interval'] ?? 'twicedaily', 'daily'); ?>><?php _e('Daily', 'ennu-life-assessments'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <h3><?php _e('Group TTL Settings', 'ennu-life-assessments'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label for="user_scores_ttl"><?php _e('User Scores TTL (seconds)', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="user_scores_ttl" name="user_scores_ttl" value="<?php echo esc_attr($settings['user_scores_ttl'] ?? 1800); ?>" min="300" max="7200" /></td>
                    </tr>
                    <tr>
                        <th><label for="assessment_data_ttl"><?php _e('Assessment Data TTL (seconds)', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="assessment_data_ttl" name="assessment_data_ttl" value="<?php echo esc_attr($settings['assessment_data_ttl'] ?? 3600); ?>" min="600" max="14400" /></td>
                    </tr>
                    <tr>
                        <th><label for="biomarker_data_ttl"><?php _e('Biomarker Data TTL (seconds)', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="biomarker_data_ttl" name="biomarker_data_ttl" value="<?php echo esc_attr($settings['biomarker_data_ttl'] ?? 7200); ?>" min="1800" max="28800" /></td>
                    </tr>
                    <tr>
                        <th><label for="system_config_ttl"><?php _e('System Config TTL (seconds)', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="system_config_ttl" name="system_config_ttl" value="<?php echo esc_attr($settings['system_config_ttl'] ?? 86400); ?>" min="3600" max="604800" /></td>
                    </tr>
                </table>
                
                <h3><?php _e('Redis/Memcached Settings', 'ennu-life-assessments'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th><label for="redis_host"><?php _e('Redis Host', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="text" id="redis_host" name="redis_host" value="<?php echo esc_attr($settings['redis_host'] ?? '127.0.0.1'); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="redis_port"><?php _e('Redis Port', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="redis_port" name="redis_port" value="<?php echo esc_attr($settings['redis_port'] ?? 6379); ?>" min="1" max="65535" /></td>
                    </tr>
                    <tr>
                        <th><label for="redis_password"><?php _e('Redis Password', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="password" id="redis_password" name="redis_password" value="<?php echo esc_attr($settings['redis_password'] ?? ''); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label for="redis_database"><?php _e('Redis Database', 'ennu-life-assessments'); ?></label></th>
                        <td><input type="number" id="redis_database" name="redis_database" value="<?php echo esc_attr($settings['redis_database'] ?? 0); ?>" min="0" max="15" /></td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="save_cache_settings" class="button-primary" value="<?php _e('Save Settings', 'ennu-life-assessments'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Helper methods for rendering statistics
     */
    private function calculate_overall_hit_rate($cache_stats) {
        $total_hits = 0;
        $total_requests = 0;
        
        foreach ($cache_stats as $stats) {
            $total_hits += $stats['hits'];
            $total_requests += $stats['hits'] + $stats['misses'];
        }
        
        return $total_requests > 0 ? number_format(($total_hits / $total_requests) * 100, 1) : 0;
    }
    
    private function calculate_total_requests($cache_stats) {
        $total = 0;
        
        foreach ($cache_stats as $stats) {
            $total += $stats['hits'] + $stats['misses'];
        }
        
        return number_format($total);
    }
    
    private function calculate_avg_response_time($cache_stats) {
        $total_time = 0;
        $total_requests = 0;
        
        foreach ($cache_stats as $stats) {
            $requests = $stats['hits'] + $stats['misses'];
            $total_time += $stats['total_time'];
            $total_requests += $requests;
        }
        
        return $total_requests > 0 ? number_format(($total_time / $total_requests) * 1000, 2) : 0;
    }
    
    private function render_detailed_statistics() {
        $period = $_GET['period'] ?? 'today';
        $group = $_GET['group'] ?? '';
        
        $stats = $this->get_historical_stats($period, $group);
        
        if (empty($stats)) {
            echo '<p>' . __('No statistics available for the selected period.', 'ennu-life-assessments') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Date', 'ennu-life-assessments') . '</th>';
        echo '<th>' . __('Group', 'ennu-life-assessments') . '</th>';
        echo '<th>' . __('Hits', 'ennu-life-assessments') . '</th>';
        echo '<th>' . __('Misses', 'ennu-life-assessments') . '</th>';
        echo '<th>' . __('Hit Rate', 'ennu-life-assessments') . '</th>';
        echo '<th>' . __('Avg Time', 'ennu-life-assessments') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        foreach ($stats as $date => $date_stats) {
            foreach ($date_stats as $group_name => $group_stats) {
                $total = $group_stats['hits'] + $group_stats['misses'];
                $hit_rate = $total > 0 ? ($group_stats['hits'] / $total) * 100 : 0;
                $avg_time = $total > 0 ? ($group_stats['total_time'] / $total) * 1000 : 0;
                
                echo '<tr>';
                echo '<td>' . esc_html($date) . '</td>';
                echo '<td>' . esc_html($group_name) . '</td>';
                echo '<td>' . number_format($group_stats['hits']) . '</td>';
                echo '<td>' . number_format($group_stats['misses']) . '</td>';
                echo '<td>' . number_format($hit_rate, 1) . '%</td>';
                echo '<td>' . number_format($avg_time, 2) . 'ms</td>';
                echo '</tr>';
            }
        }
        
        echo '</tbody></table>';
    }
    
    private function render_memory_usage() {
        global $ennu_cache;
        
        if (!$ennu_cache) {
            echo '<p>' . __('Cache manager not available', 'ennu-life-assessments') . '</p>';
            return;
        }
        
        $cache_info = $ennu_cache->get_cache_info();
        
        if (isset($cache_info['redis_info']['used_memory_human'])) {
            echo '<div class="memory-stat">';
            echo '<span class="label">' . __('Used Memory:', 'ennu-life-assessments') . '</span>';
            echo '<span class="value">' . esc_html($cache_info['redis_info']['used_memory_human']) . '</span>';
            echo '</div>';
        }
        
        if (isset($cache_info['memcached_stats'])) {
            $stats = current($cache_info['memcached_stats']);
            if ($stats) {
                echo '<div class="memory-stat">';
                echo '<span class="label">' . __('Used Memory:', 'ennu-life-assessments') . '</span>';
                echo '<span class="value">' . size_format($stats['bytes']) . '</span>';
                echo '</div>';
            }
        }
        
        if (!isset($cache_info['redis_info']) && !isset($cache_info['memcached_stats'])) {
            echo '<p>' . __('Memory usage not available for transient cache', 'ennu-life-assessments') . '</p>';
        }
    }
    
    private function render_cache_size() {
        global $wpdb;
        
        $transient_count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ennu_%'
        ");
        
        echo '<div class="cache-size-stat">';
        echo '<span class="label">' . __('Cached Items:', 'ennu-life-assessments') . '</span>';
        echo '<span class="value">' . number_format($transient_count) . '</span>';
        echo '</div>';
    }
    
    private function render_connection_status() {
        global $ennu_cache;
        
        if (!$ennu_cache) {
            echo '<div class="connection-status offline">';
            echo '<span class="dashicons dashicons-warning"></span>';
            echo __('Cache manager not initialized', 'ennu-life-assessments');
            echo '</div>';
            return;
        }
        
        $cache_info = $ennu_cache->get_cache_info();
        
        if ($cache_info['redis_available']) {
            echo '<div class="connection-status online">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo __('Redis Connected', 'ennu-life-assessments');
            echo '</div>';
        } elseif ($cache_info['memcached_available']) {
            echo '<div class="connection-status online">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo __('Memcached Connected', 'ennu-life-assessments');
            echo '</div>';
        } else {
            echo '<div class="connection-status fallback">';
            echo '<span class="dashicons dashicons-info"></span>';
            echo __('Using WordPress Transients', 'ennu-life-assessments');
            echo '</div>';
        }
    }
    
    /**
     * Get historical statistics
     */
    private function get_historical_stats($period, $group = '') {
        $stats = array();
        
        switch ($period) {
            case 'today':
                $date = date('Y-m-d');
                $daily_stats = get_option("ennu_cache_stats_{$date}", array());
                if (!empty($daily_stats)) {
                    $stats[$date] = $group ? array($group => $daily_stats[$group] ?? array()) : $daily_stats;
                }
                break;
                
            case 'week':
                for ($i = 0; $i < 7; $i++) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $daily_stats = get_option("ennu_cache_stats_{$date}", array());
                    if (!empty($daily_stats)) {
                        $stats[$date] = $group ? array($group => $daily_stats[$group] ?? array()) : $daily_stats;
                    }
                }
                break;
                
            case 'month':
                for ($i = 0; $i < 30; $i++) {
                    $date = date('Y-m-d', strtotime("-{$i} days"));
                    $daily_stats = get_option("ennu_cache_stats_{$date}", array());
                    if (!empty($daily_stats)) {
                        $stats[$date] = $group ? array($group => $daily_stats[$group] ?? array()) : $daily_stats;
                    }
                }
                break;
        }
        
        return $stats;
    }
    
    /**
     * Handle AJAX cache actions
     */
    public function handle_cache_actions() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'ennu_cache_nonce')) {
            wp_die('Invalid nonce');
        }
        
        global $ennu_cache;
        
        if (!$ennu_cache) {
            wp_send_json_error(array('message' => 'Cache manager not available'));
        }
        
        $action = sanitize_text_field($_POST['cache_action']);
        
        switch ($action) {
            case 'flush_all':
                $ennu_cache->flush();
                wp_send_json_success(array('message' => 'All cache flushed successfully'));
                break;
                
            case 'flush_group':
                $group = sanitize_text_field($_POST['group']);
                $ennu_cache->flush($group);
                wp_send_json_success(array('message' => "Cache group '{$group}' flushed successfully"));
                break;
                
            case 'warm_cache':
                $ennu_cache->warm_cache();
                wp_send_json_success(array('message' => 'Cache warming started'));
                break;
                
            case 'cleanup_expired':
                $ennu_cache->cleanup_expired_cache();
                wp_send_json_success(array('message' => 'Expired cache cleaned up'));
                break;
                
            default:
                wp_send_json_error(array('message' => 'Unknown action'));
        }
    }
    
    /**
     * Get cache statistics via AJAX
     */
    public function get_cache_stats_ajax() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'ennu_cache_nonce')) {
            wp_die('Invalid nonce');
        }
        
        global $ennu_cache;
        
        if (!$ennu_cache) {
            wp_send_json_error(array('message' => 'Cache manager not available'));
        }
        
        $stats = $ennu_cache->get_cache_stats();
        $info = $ennu_cache->get_cache_info();
        
        wp_send_json_success(array(
            'stats' => $stats,
            'info' => $info
        ));
    }
    
    /**
     * Save cache settings
     */
    private function save_cache_settings() {
        if (!wp_verify_nonce($_POST['cache_settings_nonce'], 'ennu_cache_settings')) {
            return;
        }
        
        $settings = array(
            'default_ttl' => intval($_POST['default_ttl'] ?? 3600),
            'enable_cache_warming' => isset($_POST['enable_cache_warming']),
            'cache_warming_interval' => sanitize_text_field($_POST['cache_warming_interval'] ?? 'twicedaily'),
            'user_scores_ttl' => intval($_POST['user_scores_ttl'] ?? 1800),
            'assessment_data_ttl' => intval($_POST['assessment_data_ttl'] ?? 3600),
            'biomarker_data_ttl' => intval($_POST['biomarker_data_ttl'] ?? 7200),
            'system_config_ttl' => intval($_POST['system_config_ttl'] ?? 86400),
            'redis_host' => sanitize_text_field($_POST['redis_host'] ?? '127.0.0.1'),
            'redis_port' => intval($_POST['redis_port'] ?? 6379),
            'redis_password' => sanitize_text_field($_POST['redis_password'] ?? ''),
            'redis_database' => intval($_POST['redis_database'] ?? 0)
        );
        
        update_option('ennu_cache_settings', $settings);
        
        wp_clear_scheduled_hook('ennu_cache_warm');
        if ($settings['enable_cache_warming']) {
            wp_schedule_event(time(), $settings['cache_warming_interval'], 'ennu_cache_warm');
        }
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Cache settings saved successfully.</p></div>';
        });
    }
}

if (is_admin()) {
    new ENNU_Cache_Admin_Interface();
}
