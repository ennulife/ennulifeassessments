<?php
/**
 * ENNU Dashboard Data Service
 * 
 * Centralized data fetching and caching for dashboard
 * 
 * @package ENNU_Life_Assessments
 * @version 64.55.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Dashboard_Data_Service {
    
    /**
     * Cache duration in seconds (15 minutes)
     */
    private $cache_duration = 900;
    
    /**
     * Get complete dashboard data for user
     * 
     * @param int $user_id User ID
     * @return array Dashboard data
     */
    public function get_dashboard_data($user_id) {
        if (!$user_id) {
            return $this->get_empty_data();
        }
        
        // Check cache first
        $cache_key = "ennu_dashboard_data_{$user_id}";
        $cached = get_transient($cache_key);
        
        if ($cached !== false && !$this->should_refresh_cache($user_id)) {
            return $cached;
        }
        
        // Fetch fresh data
        $data = array(
            'user_info' => $this->get_user_info($user_id),
            'scores' => $this->get_scores($user_id),
            'assessments' => $this->get_assessments($user_id),
            'biomarkers' => $this->get_biomarkers($user_id),
            'symptoms' => $this->get_symptoms($user_id),
            'goals' => $this->get_goals($user_id),
            'history' => $this->get_history($user_id),
            'recommendations' => $this->get_recommendations($user_id),
            'profile_completeness' => $this->get_profile_completeness($user_id),
            'timestamp' => current_time('timestamp')
        );
        
        // Cache the data
        set_transient($cache_key, $data, $this->cache_duration);
        
        return $data;
    }
    
    /**
     * Get user information
     */
    private function get_user_info($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return array();
        }
        
        // Get global fields
        $dob = get_user_meta($user_id, 'ennu_global_date_of_birth', true);
        $gender = get_user_meta($user_id, 'ennu_global_gender', true);
        $height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);
        
        // Calculate age if DOB exists
        $age = '';
        if (!empty($dob)) {
            $birthdate = new DateTime($dob);
            $today = new DateTime();
            $age_obj = $today->diff($birthdate);
            $age = $age_obj->y;
        }
        
        // Format height
        $height = '';
        if (is_array($height_weight) && !empty($height_weight['ft'])) {
            $height = $height_weight['ft'] . "' " . ($height_weight['in'] ?? 0) . '"';
        }
        
        // Get weight
        $weight = '';
        if (is_array($height_weight)) {
            $weight_value = $height_weight['weight'] ?? $height_weight['lbs'] ?? '';
            if ($weight_value) {
                $weight = $weight_value . ' lbs';
            }
        }
        
        // Calculate BMI
        $bmi = get_user_meta($user_id, 'ennu_calculated_bmi', true);
        
        return array(
            'id' => $user_id,
            'display_name' => $user->display_name,
            'email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'age' => $age,
            'gender' => $gender,
            'height' => $height,
            'weight' => $weight,
            'bmi' => $bmi,
            'date_of_birth' => $dob,
            'member_since' => $user->user_registered
        );
    }
    
    /**
     * Get user scores
     */
    private function get_scores($user_id) {
        // Get ENNU Life Score
        $ennu_life_score = get_user_meta($user_id, 'ennu_life_score', true);
        
        // Get pillar scores
        $pillar_scores = array();
        if (class_exists('ENNU_Scoring_System')) {
            $pillar_scores = ENNU_Scoring_System::calculate_average_pillar_scores($user_id);
        }
        
        // Get assessment-specific scores
        $assessment_scores = array();
        $assessment_types = $this->get_assessment_types();
        
        foreach ($assessment_types as $type) {
            $score_meta = get_user_meta($user_id, "ennu_assessment_score_{$type}", true);
            if ($score_meta) {
                $assessment_scores[$type] = $score_meta;
            }
        }
        
        return array(
            'ennu_life_score' => floatval($ennu_life_score),
            'pillar_scores' => $pillar_scores,
            'assessment_scores' => $assessment_scores,
            'last_updated' => get_user_meta($user_id, 'ennu_scores_last_updated', true)
        );
    }
    
    /**
     * Get user assessments data
     */
    private function get_assessments($user_id) {
        $assessments = array();
        $assessment_types = $this->get_assessment_types();
        
        foreach ($assessment_types as $type) {
            $assessment_data = get_user_meta($user_id, "ennu_assessment_responses_{$type}", true);
            $completed = !empty($assessment_data);
            
            $assessments[$type] = array(
                'type' => $type,
                'display_name' => $this->get_assessment_display_name($type),
                'completed' => $completed,
                'completion_date' => $completed ? ($assessment_data['timestamp'] ?? '') : '',
                'score' => $completed ? ($assessment_data['final_score'] ?? 0) : 0,
                'category_scores' => $completed ? ($assessment_data['category_scores'] ?? array()) : array(),
                'responses' => $completed ? ($assessment_data['responses'] ?? array()) : array()
            );
        }
        
        return $assessments;
    }
    
    /**
     * Get user biomarkers
     */
    private function get_biomarkers($user_id) {
        $biomarkers = array();
        
        // Get all biomarker data
        $biomarker_data = get_user_meta($user_id, 'ennu_biomarker_data', true);
        
        if (!is_array($biomarker_data)) {
            $biomarker_data = array();
        }
        
        // Get biomarker categories
        $categories = $this->get_biomarker_categories();
        
        foreach ($categories as $category => $biomarker_keys) {
            $biomarkers[$category] = array(
                'name' => $category,
                'biomarkers' => array()
            );
            
            foreach ($biomarker_keys as $key) {
                $value = $biomarker_data[$key] ?? null;
                
                if ($value !== null) {
                    $biomarkers[$category]['biomarkers'][$key] = array(
                        'key' => $key,
                        'name' => $this->get_biomarker_display_name($key),
                        'value' => $value['value'] ?? '',
                        'unit' => $value['unit'] ?? '',
                        'status' => $value['status'] ?? 'unknown',
                        'date' => $value['date'] ?? '',
                        'source' => $value['source'] ?? 'manual'
                    );
                }
            }
        }
        
        return $biomarkers;
    }
    
    /**
     * Get user symptoms
     */
    private function get_symptoms($user_id) {
        $symptoms = get_user_meta($user_id, 'ennu_symptoms_data', true);
        
        if (!is_array($symptoms)) {
            return array();
        }
        
        // Process symptoms data
        $processed = array();
        foreach ($symptoms as $symptom_key => $symptom_data) {
            $processed[] = array(
                'key' => $symptom_key,
                'name' => $this->get_symptom_display_name($symptom_key),
                'severity' => $symptom_data['severity'] ?? 0,
                'frequency' => $symptom_data['frequency'] ?? 'never',
                'duration' => $symptom_data['duration'] ?? '',
                'notes' => $symptom_data['notes'] ?? '',
                'last_reported' => $symptom_data['timestamp'] ?? ''
            );
        }
        
        return $processed;
    }
    
    /**
     * Get user health goals
     */
    private function get_goals($user_id) {
        $goals = get_user_meta($user_id, 'ennu_global_health_goals', true);
        
        if (!is_array($goals)) {
            return array();
        }
        
        // Process goals
        $processed = array();
        foreach ($goals as $goal) {
            $processed[] = array(
                'goal' => $goal,
                'display_name' => $this->get_goal_display_name($goal),
                'icon' => $this->get_goal_icon($goal),
                'selected' => true
            );
        }
        
        return $processed;
    }
    
    /**
     * Get user history
     */
    private function get_history($user_id) {
        return array(
            'score_history' => $this->get_score_history($user_id),
            'bmi_history' => $this->get_bmi_history($user_id),
            'assessment_history' => $this->get_assessment_history($user_id)
        );
    }
    
    /**
     * Get score history
     */
    private function get_score_history($user_id) {
        $history = get_user_meta($user_id, 'ennu_life_score_history', true);
        
        if (!is_array($history)) {
            // Create initial entry if no history exists
            $current_score = get_user_meta($user_id, 'ennu_life_score', true);
            if ($current_score) {
                $history = array(
                    array(
                        'score' => floatval($current_score),
                        'date' => current_time('mysql'),
                        'timestamp' => current_time('timestamp')
                    )
                );
            } else {
                $history = array();
            }
        }
        
        // Sort by date
        usort($history, function($a, $b) {
            return ($a['timestamp'] ?? 0) - ($b['timestamp'] ?? 0);
        });
        
        return $history;
    }
    
    /**
     * Get BMI history
     */
    private function get_bmi_history($user_id) {
        $history = get_user_meta($user_id, 'ennu_bmi_history', true);
        
        if (!is_array($history)) {
            // Create initial entry if no history exists
            $current_bmi = get_user_meta($user_id, 'ennu_calculated_bmi', true);
            if ($current_bmi) {
                $history = array(
                    array(
                        'bmi' => floatval($current_bmi),
                        'date' => current_time('mysql'),
                        'timestamp' => current_time('timestamp')
                    )
                );
            } else {
                $history = array();
            }
        }
        
        // Sort by date
        usort($history, function($a, $b) {
            return ($a['timestamp'] ?? 0) - ($b['timestamp'] ?? 0);
        });
        
        return $history;
    }
    
    /**
     * Get assessment history
     */
    private function get_assessment_history($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ennu_assessment_history';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            return array();
        }
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY completed_at DESC LIMIT 20",
            $user_id
        ), ARRAY_A);
        
        return $results ?: array();
    }
    
    /**
     * Get personalized recommendations
     */
    private function get_recommendations($user_id) {
        $recommendations = array();
        
        // Get scores
        $scores = $this->get_scores($user_id);
        $ennu_score = $scores['ennu_life_score'];
        
        // Generate recommendations based on score
        if ($ennu_score < 3) {
            $recommendations[] = array(
                'type' => 'assessment',
                'priority' => 'high',
                'title' => 'Complete Health Assessments',
                'description' => 'Start by completing your basic health assessments to establish your baseline.',
                'action' => 'take_assessment',
                'icon' => '📋'
            );
        }
        
        if ($ennu_score < 5) {
            $recommendations[] = array(
                'type' => 'biomarker',
                'priority' => 'medium',
                'title' => 'Upload Lab Results',
                'description' => 'Add your recent lab results to get personalized biomarker insights.',
                'action' => 'upload_labs',
                'icon' => '🔬'
            );
        }
        
        if ($ennu_score >= 5) {
            $recommendations[] = array(
                'type' => 'optimization',
                'priority' => 'low',
                'title' => 'Fine-tune Your Health',
                'description' => 'Focus on specific areas for optimization based on your assessment results.',
                'action' => 'view_insights',
                'icon' => '🎯'
            );
        }
        
        return $recommendations;
    }
    
    /**
     * Get profile completeness
     */
    private function get_profile_completeness($user_id) {
        if (class_exists('ENNU_Profile_Completeness_Tracker')) {
            return ENNU_Profile_Completeness_Tracker::get_completeness_for_display($user_id);
        }
        
        // Fallback calculation
        $completeness = 0;
        $total_fields = 10;
        $completed_fields = 0;
        
        // Check basic info
        if (get_user_meta($user_id, 'ennu_global_date_of_birth', true)) $completed_fields++;
        if (get_user_meta($user_id, 'ennu_global_gender', true)) $completed_fields++;
        if (get_user_meta($user_id, 'ennu_global_height_weight', true)) $completed_fields++;
        if (get_user_meta($user_id, 'ennu_global_health_goals', true)) $completed_fields++;
        
        // Check assessments
        $assessments = $this->get_assessments($user_id);
        $completed_assessments = array_filter($assessments, function($a) {
            return $a['completed'];
        });
        if (count($completed_assessments) > 0) $completed_fields += 2;
        
        // Check biomarkers
        $biomarkers = get_user_meta($user_id, 'ennu_biomarker_data', true);
        if (!empty($biomarkers)) $completed_fields += 2;
        
        // Check symptoms
        $symptoms = get_user_meta($user_id, 'ennu_symptoms_data', true);
        if (!empty($symptoms)) $completed_fields += 2;
        
        $completeness = round(($completed_fields / $total_fields) * 100);
        
        return array(
            'overall_percentage' => $completeness,
            'completed_sections' => $completed_fields,
            'total_sections' => $total_fields,
            'data_accuracy_level' => $this->get_accuracy_level($completeness)
        );
    }
    
    /**
     * Check if cache should be refreshed
     */
    private function should_refresh_cache($user_id) {
        // Check if user recently completed an assessment
        $last_assessment = get_user_meta($user_id, 'ennu_last_assessment_time', true);
        if ($last_assessment && (time() - $last_assessment) < 60) {
            return true;
        }
        
        // Check if biomarkers were recently updated
        $last_biomarker_update = get_user_meta($user_id, 'ennu_last_biomarker_update', true);
        if ($last_biomarker_update && (time() - $last_biomarker_update) < 60) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Clear user dashboard cache
     */
    public function clear_cache($user_id) {
        delete_transient("ennu_dashboard_data_{$user_id}");
    }
    
    /**
     * Get empty data structure
     */
    private function get_empty_data() {
        return array(
            'user_info' => array(),
            'scores' => array(
                'ennu_life_score' => 0,
                'pillar_scores' => array(),
                'assessment_scores' => array()
            ),
            'assessments' => array(),
            'biomarkers' => array(),
            'symptoms' => array(),
            'goals' => array(),
            'history' => array(
                'score_history' => array(),
                'bmi_history' => array(),
                'assessment_history' => array()
            ),
            'recommendations' => array(),
            'profile_completeness' => array(
                'overall_percentage' => 0,
                'completed_sections' => 0,
                'total_sections' => 10,
                'data_accuracy_level' => 'low'
            ),
            'timestamp' => current_time('timestamp')
        );
    }
    
    /**
     * Get assessment types
     */
    private function get_assessment_types() {
        return array(
            'testosterone',
            'weight_loss',
            'hormone',
            'menopause',
            'ed_treatment',
            'skin',
            'hair',
            'sleep',
            'health',
            'welcome',
            'health_optimization',
            'cognitive',
            'energy',
            'stress',
            'nutrition',
            'exercise'
        );
    }
    
    /**
     * Get assessment display name
     */
    private function get_assessment_display_name($type) {
        $names = array(
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
        
        return $names[$type] ?? ucwords(str_replace('_', ' ', $type));
    }
    
    /**
     * Get biomarker categories
     */
    private function get_biomarker_categories() {
        return array(
            'Metabolic' => array('glucose', 'hba1c', 'insulin', 'c_peptide'),
            'Lipids' => array('total_cholesterol', 'ldl_cholesterol', 'hdl_cholesterol', 'triglycerides', 'apob', 'lp_a'),
            'Hormones' => array('testosterone', 'estradiol', 'progesterone', 'dhea_s', 'cortisol'),
            'Thyroid' => array('tsh', 'free_t4', 'free_t3'),
            'Vitamins' => array('vitamin_d', 'vitamin_b12', 'folate'),
            'Minerals' => array('iron', 'ferritin', 'zinc', 'magnesium'),
            'Inflammation' => array('crp', 'hs_crp', 'esr', 'homocysteine'),
            'Kidney' => array('creatinine', 'bun', 'egfr', 'uric_acid'),
            'Liver' => array('alt', 'ast', 'alkaline_phosphatase', 'bilirubin', 'ggt'),
            'Blood' => array('hemoglobin', 'hematocrit', 'wbc', 'rbc', 'platelets')
        );
    }
    
    /**
     * Get biomarker display name
     */
    private function get_biomarker_display_name($key) {
        $names = array(
            'total_cholesterol' => 'Total Cholesterol',
            'ldl_cholesterol' => 'LDL Cholesterol',
            'hdl_cholesterol' => 'HDL Cholesterol',
            'triglycerides' => 'Triglycerides',
            'glucose' => 'Glucose',
            'hba1c' => 'HbA1c',
            'testosterone' => 'Testosterone',
            'vitamin_d' => 'Vitamin D'
        );
        
        return $names[$key] ?? ucwords(str_replace('_', ' ', $key));
    }
    
    /**
     * Get symptom display name
     */
    private function get_symptom_display_name($key) {
        return ucwords(str_replace('_', ' ', $key));
    }
    
    /**
     * Get goal display name
     */
    private function get_goal_display_name($goal) {
        $names = array(
            'weight_loss' => 'Weight Loss',
            'muscle_gain' => 'Muscle Gain',
            'energy' => 'Increase Energy',
            'sleep' => 'Better Sleep',
            'stress' => 'Reduce Stress',
            'nutrition' => 'Improve Nutrition',
            'fitness' => 'Enhance Fitness',
            'mental' => 'Mental Clarity',
            'hormones' => 'Balance Hormones',
            'longevity' => 'Longevity'
        );
        
        return $names[$goal] ?? ucwords(str_replace('_', ' ', $goal));
    }
    
    /**
     * Get goal icon
     */
    private function get_goal_icon($goal) {
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
        
        return $icons[$goal] ?? '🎯';
    }
    
    /**
     * Get accuracy level based on completeness
     */
    private function get_accuracy_level($percentage) {
        if ($percentage >= 90) return 'excellent';
        if ($percentage >= 75) return 'high';
        if ($percentage >= 60) return 'medium';
        if ($percentage >= 40) return 'moderate';
        return 'low';
    }
}