<?php
/**
 * ENNU Dashboard Helper Functions
 * 
 * Extracted from user-dashboard.php template for better organization
 * 
 * @package ENNU_Life_Assessments
 * @version 64.55.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Dashboard_Helpers {
    
    /**
     * Get category icon for assessment
     */
    public static function get_category_icon($category) {
        $icons = array(
            'Mind' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/></svg>',
            'Body' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>',
            'Lifestyle' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
            'Aesthetics' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>'
        );
        
        return isset($icons[$category]) ? $icons[$category] : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>';
    }
    
    /**
     * Get biomarker category
     */
    public static function get_biomarker_category($biomarker_key) {
        $categories = array(
            // Metabolic Panel
            'glucose' => 'Metabolic',
            'hba1c' => 'Metabolic',
            'insulin' => 'Metabolic',
            'c_peptide' => 'Metabolic',
            
            // Lipid Panel
            'total_cholesterol' => 'Lipids',
            'ldl_cholesterol' => 'Lipids',
            'hdl_cholesterol' => 'Lipids',
            'triglycerides' => 'Lipids',
            'apob' => 'Lipids',
            'lp_a' => 'Lipids',
            
            // Hormones
            'testosterone' => 'Hormones',
            'estradiol' => 'Hormones',
            'progesterone' => 'Hormones',
            'dhea_s' => 'Hormones',
            'cortisol' => 'Hormones',
            
            // Thyroid
            'tsh' => 'Thyroid',
            'free_t4' => 'Thyroid',
            'free_t3' => 'Thyroid',
            
            // Vitamins & Minerals
            'vitamin_d' => 'Vitamins',
            'vitamin_b12' => 'Vitamins',
            'folate' => 'Vitamins',
            'iron' => 'Minerals',
            'ferritin' => 'Minerals',
            'zinc' => 'Minerals',
            'magnesium' => 'Minerals',
            
            // Inflammation
            'crp' => 'Inflammation',
            'hs_crp' => 'Inflammation',
            'esr' => 'Inflammation',
            'homocysteine' => 'Inflammation',
            
            // Kidney Function
            'creatinine' => 'Kidney',
            'bun' => 'Kidney',
            'egfr' => 'Kidney',
            'uric_acid' => 'Kidney',
            
            // Liver Function
            'alt' => 'Liver',
            'ast' => 'Liver',
            'alkaline_phosphatase' => 'Liver',
            'bilirubin' => 'Liver',
            'ggt' => 'Liver',
            
            // Blood Count
            'hemoglobin' => 'Blood',
            'hematocrit' => 'Blood',
            'wbc' => 'Blood',
            'rbc' => 'Blood',
            'platelets' => 'Blood',
            'mcv' => 'Blood',
            'mch' => 'Blood',
            'mchc' => 'Blood'
        );
        
        return isset($categories[$biomarker_key]) ? $categories[$biomarker_key] : 'Other';
    }
    
    /**
     * Get relative time string
     */
    public static function get_relative_time($timestamp) {
        if (empty($timestamp)) {
            return 'Never';
        }
        
        $time_diff = time() - strtotime($timestamp);
        
        if ($time_diff < 60) {
            return 'Just now';
        } elseif ($time_diff < 3600) {
            $minutes = floor($time_diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($time_diff < 86400) {
            $hours = floor($time_diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($time_diff < 604800) {
            $days = floor($time_diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } elseif ($time_diff < 2419200) {
            $weeks = floor($time_diff / 604800);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', strtotime($timestamp));
        }
    }
    
    /**
     * Get assessment display name
     */
    public static function get_assessment_display_name($assessment_type) {
        $display_names = array(
            'testosterone' => 'Testosterone Optimization',
            'weight_loss' => 'Weight Management',
            'hormone' => 'Hormone Balance',
            'menopause' => 'Menopause Support',
            'ed_treatment' => 'ED Treatment',
            'skin' => 'Skin Health',
            'hair' => 'Hair Health',
            'sleep' => 'Sleep Quality',
            'health' => 'General Health',
            'welcome' => 'Welcome Assessment',
            'health_optimization' => 'Health Optimization',
            'cognitive' => 'Cognitive Function',
            'energy' => 'Energy Vitality',
            'stress' => 'Stress Management',
            'nutrition' => 'Nutritional Balance',
            'exercise' => 'Exercise & Fitness'
        );
        
        return isset($display_names[$assessment_type]) ? $display_names[$assessment_type] : ucwords(str_replace('_', ' ', $assessment_type));
    }
    
    /**
     * Get assessment display info from source
     */
    public static function get_assessment_display_info($source) {
        $assessment_info = array(
            'testosterone_assessment' => array(
                'name' => 'Testosterone Optimization',
                'icon' => '💪',
                'color' => '#3b82f6'
            ),
            'weight_loss_assessment' => array(
                'name' => 'Weight Management',
                'icon' => '⚖️',
                'color' => '#10b981'
            ),
            'hormone_assessment' => array(
                'name' => 'Hormone Balance',
                'icon' => '🔬',
                'color' => '#8b5cf6'
            ),
            'menopause_assessment' => array(
                'name' => 'Menopause Support',
                'icon' => '🌸',
                'color' => '#ec4899'
            ),
            'ed_treatment_assessment' => array(
                'name' => 'ED Treatment',
                'icon' => '🏥',
                'color' => '#06b6d4'
            ),
            'skin_assessment' => array(
                'name' => 'Skin Health',
                'icon' => '✨',
                'color' => '#f59e0b'
            ),
            'hair_assessment' => array(
                'name' => 'Hair Health',
                'icon' => '💇',
                'color' => '#84cc16'
            ),
            'sleep_assessment' => array(
                'name' => 'Sleep Quality',
                'icon' => '😴',
                'color' => '#6366f1'
            ),
            'health_assessment' => array(
                'name' => 'General Health',
                'icon' => '❤️',
                'color' => '#ef4444'
            ),
            'welcome_assessment' => array(
                'name' => 'Welcome Assessment',
                'icon' => '👋',
                'color' => '#f97316'
            ),
            'health_optimization_assessment' => array(
                'name' => 'Health Optimization',
                'icon' => '🎯',
                'color' => '#0ea5e9'
            )
        );
        
        $key = str_replace('-', '_', $source);
        
        if (isset($assessment_info[$key])) {
            return $assessment_info[$key];
        }
        
        return array(
            'name' => ucwords(str_replace(array('_', '-'), ' ', str_replace('_assessment', '', $source))),
            'icon' => '📋',
            'color' => '#6b7280'
        );
    }
    
    /**
     * Render biomarker measurement component
     */
    public static function render_biomarker_measurement($biomarker_key, $value, $unit, $status = 'normal', $user_age = null, $user_gender = null) {
        if (!class_exists('ENNU_Recommended_Range_Manager')) {
            return '<div class="biomarker-error">Range manager not available</div>';
        }
        
        $range_manager = new ENNU_Recommended_Range_Manager();
        $ranges = $range_manager->get_biomarker_ranges($biomarker_key, $user_age, $user_gender);
        
        if (!$ranges) {
            return '<div class="biomarker-error">No ranges available for ' . esc_html($biomarker_key) . '</div>';
        }
        
        $critical_min = $ranges['critical_low'] ?? 0;
        $critical_max = $ranges['critical_high'] ?? 100;
        $normal_min = $ranges['normal_low'] ?? 20;
        $normal_max = $ranges['normal_high'] ?? 80;
        $optimal_min = $ranges['optimal_low'] ?? 40;
        $optimal_max = $ranges['optimal_high'] ?? 60;
        
        $percentage = self::calculate_percentage_position($value, $critical_min, $critical_max);
        
        ob_start();
        ?>
        <div class="biomarker-measurement" data-biomarker-id="<?php echo esc_attr($biomarker_key); ?>">
            <div class="biomarker-header">
                <div class="biomarker-name"><?php echo esc_html(ucwords(str_replace('_', ' ', $biomarker_key))); ?></div>
                <div class="biomarker-value <?php echo esc_attr('status-' . $status); ?>">
                    <?php echo esc_html($value . ' ' . $unit); ?>
                </div>
            </div>
            
            <div class="biomarker-range-ruler-container" 
                 data-critical-min="<?php echo esc_attr($critical_min); ?>"
                 data-critical-max="<?php echo esc_attr($critical_max); ?>"
                 data-normal-min="<?php echo esc_attr($normal_min); ?>"
                 data-normal-max="<?php echo esc_attr($normal_max); ?>"
                 data-optimal-min="<?php echo esc_attr($optimal_min); ?>"
                 data-optimal-max="<?php echo esc_attr($optimal_max); ?>"
                 data-unit="<?php echo esc_attr($unit); ?>">
                
                <div class="biomarker-range-ruler">
                    <div class="biomarker-range-segment critical-low" data-start="0" data-end="<?php echo esc_attr(self::calculate_percentage_position($normal_min, $critical_min, $critical_max)); ?>"></div>
                    <div class="biomarker-range-segment normal" data-start="<?php echo esc_attr(self::calculate_percentage_position($normal_min, $critical_min, $critical_max)); ?>" data-end="<?php echo esc_attr(self::calculate_percentage_position($normal_max, $critical_min, $critical_max)); ?>">
                        <div class="biomarker-range-segment optimal" data-start="<?php echo esc_attr(self::calculate_percentage_position($optimal_min, $critical_min, $critical_max)); ?>" data-end="<?php echo esc_attr(self::calculate_percentage_position($optimal_max, $critical_min, $critical_max)); ?>"></div>
                    </div>
                    <div class="biomarker-range-segment critical-high" data-start="<?php echo esc_attr(self::calculate_percentage_position($normal_max, $critical_min, $critical_max)); ?>" data-end="100"></div>
                    
                    <div class="biomarker-current-dot" data-position="<?php echo esc_attr($percentage); ?>">
                        <div class="biomarker-current-tooltip">
                            <div class="tooltip-value"><?php echo esc_html($value . ' ' . $unit); ?></div>
                            <div class="tooltip-status"><?php echo esc_html(ucfirst($status)); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="biomarker-dynamic-tooltip">
                    <div class="tooltip-range-line"></div>
                    <div class="tooltip-value-line"></div>
                </div>
            </div>
            
            <div class="biomarker-footer">
                <button class="biomarker-info-icon" aria-label="View details">ℹ️</button>
                <?php if ($status !== 'optimal'): ?>
                    <button class="biomarker-flag-icon" aria-label="Needs attention">🚩</button>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Calculate percentage position for biomarker value
     */
    public static function calculate_percentage_position($value, $min, $max) {
        if ($max <= $min) return 50;
        
        $percentage = (($value - $min) / ($max - $min)) * 100;
        return max(0, min(100, $percentage));
    }
    
    /**
     * Get status color
     */
    public static function get_status_color($status) {
        $colors = array(
            'optimal' => '#10b981',
            'normal' => '#3b82f6',
            'suboptimal' => '#f59e0b',
            'poor' => '#ef4444',
            'critical' => '#dc2626'
        );
        
        return isset($colors[$status]) ? $colors[$status] : '#6b7280';
    }
    
    /**
     * Format biomarker value with unit
     */
    public static function format_biomarker_value($value, $unit) {
        if (is_numeric($value)) {
            $formatted = number_format($value, 2);
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $formatted . ' ' . $unit;
        }
        return $value . ' ' . $unit;
    }
    
    /**
     * Get biomarker trend icon
     */
    public static function get_trend_icon($trend) {
        switch ($trend) {
            case 'improving':
                return '📈';
            case 'declining':
                return '📉';
            case 'stable':
                return '➡️';
            default:
                return '❓';
        }
    }
    
    /**
     * Get health goal icon
     */
    public static function get_goal_icon($goal_type) {
        $icons = array(
            'weight_loss' => '⚖️',
            'muscle_gain' => '💪',
            'energy' => '⚡',
            'sleep' => '😴',
            'stress' => '🧘',
            'nutrition' => '🥗',
            'fitness' => '🏃',
            'mental' => '🧠',
            'hormones' => '🔬',
            'longevity' => '🎯'
        );
        
        return isset($icons[$goal_type]) ? $icons[$goal_type] : '🎯';
    }
    
    /**
     * Get priority badge
     */
    public static function get_priority_badge($priority) {
        $badges = array(
            'critical' => '<span class="priority-badge critical">🔴 Critical</span>',
            'high' => '<span class="priority-badge high">🟠 High</span>',
            'medium' => '<span class="priority-badge medium">🟡 Medium</span>',
            'low' => '<span class="priority-badge low">🟢 Low</span>'
        );
        
        return isset($badges[$priority]) ? $badges[$priority] : '';
    }
    
    /**
     * Get assessment completion percentage
     */
    public static function get_completion_percentage($completed, $total) {
        if ($total == 0) return 0;
        return round(($completed / $total) * 100);
    }
    
    /**
     * Format date for display
     */
    public static function format_dashboard_date($date) {
        if (empty($date)) return 'N/A';
        
        $timestamp = strtotime($date);
        $today = strtotime('today');
        $yesterday = strtotime('yesterday');
        
        if ($timestamp >= $today) {
            return 'Today at ' . date('g:i A', $timestamp);
        } elseif ($timestamp >= $yesterday) {
            return 'Yesterday at ' . date('g:i A', $timestamp);
        } else {
            return date('M j, Y', $timestamp);
        }
    }
}