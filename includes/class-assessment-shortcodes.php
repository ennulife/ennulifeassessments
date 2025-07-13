<?php
/**
 * ENNU Life Assessment Shortcodes Class - Fixed Version
 * 
 * Handles all assessment shortcodes with proper security, performance,
 * and WordPress standards compliance.
 * 
 * @package ENNU_Life
 * @version 14.1.11
 * @author ENNU Life Development Team
 * @since 14.1.11
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 * ENNU Assessment Shortcodes Class
 * 
 * Provides secure, performant, and accessible assessment shortcodes
 * with Pixfort icon integration and proper WordPress standards.
 */
final class ENNU_Assessment_Shortcodes {
    
    /**
     * Assessment configurations
     * 
     * @var array
     */
    private $assessments = array();
    
    /**
     * All assessment questions, loaded from a config file.
     *
     * @var array
     */
    private $all_questions = array();
    
    /**
     * Template cache
     * 
     * @var array
     */
    private $template_cache = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_assessments();
        $this->load_all_questions(); // Load questions from the new config file
        $this->register_shortcodes();
        $this->setup_hooks();
    }
    
    /**
     * Initialize assessment configurations
     */
    private function init_assessments() {
        $this->assessments = array(
            'welcome_assessment' => array(
                'title' => __( 'Welcome Assessment', 'ennulifeassessments' ),
                'description' => __( 'Let\'s get to know you and your health goals.', 'ennulifeassessments' ),
                'questions' => 6,
                'theme_color' => '#5A67D8', // Indigo color
            ),
            'hair_assessment' => array(
                'title' => __( 'Hair Assessment', 'ennulifeassessments' ),
                'description' => __( 'Comprehensive hair health evaluation', 'ennulifeassessments' ),
                'questions' => 11,
                'theme_color' => '#667eea',
                'icon_set' => 'hair'
            ),
            'hair_restoration_assessment' => array(
                'title' => __( 'Hair Restoration Assessment', 'ennulifeassessments' ),
                'description' => __( 'Advanced hair restoration evaluation', 'ennulifeassessments' ),
                'questions' => 11,
                'theme_color' => '#764ba2',
                'icon_set' => 'restoration'
            ),
            'ed_treatment_assessment' => array(
                'title' => __( 'ED Treatment Assessment', 'ennulifeassessments' ),
                'description' => __( 'Confidential ED treatment evaluation', 'ennulifeassessments' ),
                'questions' => 12,
                'theme_color' => '#f093fb',
                'icon_set' => 'medical'
            ),
            'weight_loss_assessment' => array(
                'title' => __( 'Weight Loss Assessment', 'ennulifeassessments' ),
                'description' => __( 'Personalized weight management evaluation', 'ennulifeassessments' ),
                'questions' => 13,
                'theme_color' => '#4facfe',
                'icon_set' => 'fitness'
            ),
            'weight_loss_quiz' => array(
                'title' => __( 'Weight Loss Quiz', 'ennulifeassessments' ),
                'description' => __( 'Quick weight loss readiness quiz', 'ennulifeassessments' ),
                'questions' => 8,
                'theme_color' => '#43e97b',
                'icon_set' => 'quiz'
            ),
            'health_assessment' => array(
                'title' => __( 'Health Assessment', 'ennulifeassessments' ),
                'description' => __( 'Comprehensive health evaluation', 'ennulifeassessments' ),
                'questions' => 11,
                'theme_color' => '#fa709a',
                'icon_set' => 'health'
            ),
            'skin_assessment' => array(
                'title' => __( 'Skin Assessment', 'ennulifeassessments' ),
                'description' => __( 'Comprehensive skin health evaluation', 'ennulifeassessments' ),
                'questions' => 9,
                'theme_color' => '#a8edea',
                'icon_set' => 'skin'
            ),
            'advanced_skin_assessment' => array(
                'title' => __( 'Advanced Skin Assessment', 'ennulifeassessments' ),
                'description' => __( 'Detailed skin health analysis', 'ennulifeassessments' ),
                'questions' => 9,
                'theme_color' => '#a8edea',
                'icon_set' => 'skin'
            ),
            'skin_assessment_enhanced' => array(
                'title' => __( 'Skin Assessment Enhanced', 'ennulifeassessments' ),
                'description' => __( 'Enhanced skin evaluation', 'ennulifeassessments' ),
                'questions' => 8,
                'theme_color' => '#d299c2',
                'icon_set' => 'skincare'
            ),
            'hormone_assessment' => array(
                'title' => __( 'Hormone Assessment', 'ennulifeassessments' ),
                'description' => __( 'Comprehensive hormone evaluation', 'ennulifeassessments' ),
                'questions' => 12,
                'theme_color' => '#ffecd2',
                'icon_set' => 'hormone'
            )
        );
    }
    
    /**
     * Load all assessment questions from the centralized config file.
     */
    private function load_all_questions() {
        $questions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-questions.php';
        if ( file_exists( $questions_file ) ) {
            $this->all_questions = require $questions_file;
        }
    }
    
    /**
     * Register all assessment shortcodes
     */
    private function register_shortcodes() {
        // Register only the 5 core PRD-compliant assessment shortcodes
        $core_assessments = array(
            'welcome_assessment' => 'ennu-welcome-assessment',
            'hair_assessment' => 'ennu-hair-assessment',
            'ed_treatment_assessment' => 'ennu-ed-treatment-assessment',
            'weight_loss_assessment' => 'ennu-weight-loss-assessment',
            'health_assessment' => 'ennu-health-assessment',
            'skin_assessment' => 'ennu-skin-assessment'
        );
        
        foreach ( $core_assessments as $assessment_key => $shortcode_tag ) {
            if ( isset( $this->assessments[ $assessment_key ] ) ) {
                add_shortcode( $shortcode_tag, array( $this, 'render_assessment_shortcode' ) );
                error_log( "ENNU: Registered shortcode [{$shortcode_tag}] for {$assessment_key}" );
            } else {
                error_log( "ENNU: Warning - Assessment {$assessment_key} not found in configurations" );
            }
        }
        
        // Register results page shortcode
        add_shortcode( 'ennu-assessment-results', array( $this, 'render_results_page' ) );
        
        // Register chart page shortcode
        add_shortcode( 'ennu-assessment-chart', array( $this, 'render_chart_page' ) );
        
        // Register thank you page shortcodes
        $thank_you_shortcodes = array(
            'ennu-hair-results' => 'hair_assessment',
            'ennu-ed-results' => 'ed_treatment_assessment',
            'ennu-weight-loss-results' => 'weight_loss_assessment',
            'ennu-health-results' => 'health_assessment',
            'ennu-skin-results' => 'skin_assessment'
        );
        
        foreach ( $thank_you_shortcodes as $shortcode_tag => $assessment_type ) {
            add_shortcode( $shortcode_tag, array( $this, 'render_thank_you_page' ) );
            error_log( "ENNU: Registered thank you shortcode [{$shortcode_tag}] for {$assessment_type}" );
        }
        
        // Register user dashboard shortcode
        add_shortcode( 'ennu-user-dashboard', array( $this, 'render_user_dashboard' ) );
        
        error_log( "ENNU: Registered " . count( $core_assessments ) . " core assessment shortcodes + " . count( $thank_you_shortcodes ) . " thank you shortcodes + results page + user dashboard" );

        // In register_shortcodes()
        $details_shortcodes = [
            'ennu-hair-assessment-details',
            'ennu-ed-treatment-assessment-details',
            'ennu-weight-loss-assessment-details',
            'ennu-health-assessment-details',
            'ennu-skin-assessment-details',
        ];
        foreach ($details_shortcodes as $shortcode) {
            add_shortcode($shortcode, [$this, 'render_detailed_results_page']);
        }
    }
    
    /**
     * Setup WordPress hooks for AJAX and asset enqueuing.
     */
    private function setup_hooks() {
        add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_chart_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_results_styles' ) );
    }
    
    /**
     * Render assessment shortcode
     * 
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @param string $tag Shortcode tag
     * @return string
     */
    public function render_assessment_shortcode( $atts, $content = '', $tag = '' ) {
        // Extract assessment type from shortcode tag
        $assessment_type = str_replace( array( 'ennu-', '-' ), array( '', '_' ), $tag );
        
        // Validate assessment type
        if ( ! isset( $this->assessments[ $assessment_type ] ) ) {
            return $this->render_error_message( __( 'Invalid assessment type.', 'ennulifeassessments' ) );
        }
        
        // Parse attributes
        $atts = shortcode_atts( array(
            'theme' => 'default',
            'show_progress' => 'true',
            'auto_advance' => 'true',
            'cache' => 'true'
        ), $atts, $tag );
        
        // Check cache
        $cache_key = md5( $assessment_type . serialize( $atts ) );
        if ( $atts['cache'] === 'true' && isset( $this->template_cache[ $cache_key ] ) ) {
            return $this->template_cache[ $cache_key ];
        }
        
        try {
            // Render assessment
            $output = $this->render_assessment( $assessment_type, $atts );
            
            // Cache output
            if ( $atts['cache'] === 'true' ) {
                $this->template_cache[ $cache_key ] = $output;
            }
            
            return $output;
            
        } catch ( Exception $e ) {
            error_log( 'ENNU Assessment Error: ' . $e->getMessage() );
            return $this->render_error_message( __( 'Assessment temporarily unavailable.', 'ennulifeassessments' ) );
        }
    }
    
    /**
     * Render assessment HTML
     * 
     * @param string $assessment_type Assessment type
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_assessment( $assessment_type, $atts ) {
        $config = $this->assessments[ $assessment_type ];
        
        // Get current user data to pre-populate fields if logged in
        $current_user_data = array();
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $user_id = $user->ID;
            $current_user_data = array(
                // Get data from the most reliable source first (native WP_User object)
                'first_name'    => $user->first_name,
                'last_name'     => $user->last_name,
                'email'         => $user->user_email,
                // Fallback to user meta for other fields, using the correct global keys
                'billing_phone' => get_user_meta( $user_id, 'billing_phone', true ),
                'dob_combined'  => get_user_meta( $user_id, 'ennu_global_user_dob_combined', true ),
                'gender'        => get_user_meta( $user_id, 'ennu_global_gender', true ),
            );
        }

        // Start output buffering
        ob_start();
        
        // Include assessment template
        $template_file = $this->get_assessment_template( $assessment_type );
        if ( file_exists( $template_file ) ) {
            // Pass the current user data to the template
            include $template_file;
        } else {
            echo $this->render_default_assessment( $assessment_type, $config, $atts, $current_user_data );
        }
        
        return ob_get_clean();
    }
    
    /**
     * Get assessment template file path
     * 
     * @param string $assessment_type Assessment type
     * @return string
     */
    private function get_assessment_template( $assessment_type ) {
        $template_name = 'assessment-' . str_replace( '_', '-', $assessment_type ) . '.php';
        
        // Check theme directory first
        $theme_template = get_stylesheet_directory() . '/ennu-life/' . $template_name;
        if ( file_exists( $theme_template ) ) {
            return $theme_template;
        }
        
        // Check plugin templates directory
        if ( defined( 'ENNU_LIFE_PLUGIN_PATH' ) ) {
            $plugin_template = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
        
        return '';
    }
    
    /**
     * Render default assessment template
     * 
     * @param string $assessment_type Assessment type
     * @param array $config Assessment configuration
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_default_assessment( $assessment_type, $config, $atts, $current_user_data = array() ) {
        $current_user = wp_get_current_user();
        $nonce = wp_create_nonce( 'ennu_assessment_' . $assessment_type );
        
        // Get the actual questions to count them properly
        $questions = $this->get_assessment_questions( $assessment_type );
        $total_questions = count( $questions );
        
        ob_start();
        ?>
        <div class="ennu-assessment ennu-modern-assessment ennu-<?php echo esc_attr( $assessment_type ); ?>" 
             data-assessment="<?php echo esc_attr( $assessment_type ); ?>"
             data-theme="<?php echo esc_attr( $atts['theme'] ); ?>">
             
            <!-- Assessment Header -->
            <div class="assessment-header">
                <h1 class="assessment-title"><?php echo esc_html( $config['title'] ); ?></h1>
                <p class="assessment-description"><?php echo esc_html( $config['description'] ); ?></p>
                
                <?php if ( $atts['show_progress'] === 'true' ) : ?>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="ennu-progress-fill" data-progress="0"></div>
                    </div>
                    <div class="progress-text">
                        <span><?php esc_html_e( 'Question', 'ennulifeassessments' ); ?> 
                              <span id="currentStep" class="current-question">1</span> 
                              <?php esc_html_e( 'of', 'ennulifeassessments' ); ?> 
                              <span id="totalSteps" class="total-questions"><?php echo esc_html( $total_questions ); ?></span>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Assessment Form -->
            <form id="ennu-assessment-form-<?php echo esc_attr( $assessment_type ); ?>" class="assessment-form" data-assessment="<?php echo esc_attr( $assessment_type ); ?>" data-nonce="<?php echo wp_create_nonce('ennu_ajax_nonce'); ?>">
                <input type="hidden" name="action" value="ennu_submit_assessment">
                <input type="hidden" name="assessment_type" value="<?php echo esc_attr( $assessment_type ); ?>">
                
                <!-- Questions Container -->
                <div class="questions-container">
                    <?php echo $this->render_assessment_questions( $assessment_type, $config, $current_user_data ); ?>
                </div>
                
                <!-- Success Message -->
                <div class="assessment-success" style="display: none;">
                    <div class="success-icon">✓</div>
                    <h2><?php esc_html_e( 'Assessment Complete!', 'ennulifeassessments' ); ?></h2>
                    <p><?php esc_html_e( 'Thank you for completing your assessment. Your personalized results and recommendations will be sent to your email shortly.', 'ennulifeassessments' ); ?></p>
                    <div class="next-steps">
                        <h3><?php esc_html_e( 'What happens next?', 'ennulifeassessments' ); ?></h3>
                        <ul>
                            <li><?php esc_html_e( 'Our medical team will review your responses', 'ennulifeassessments' ); ?></li>
                            <li><?php esc_html_e( 'You\'ll receive personalized recommendations via email', 'ennulifeassessments' ); ?></li>
                            <li><?php esc_html_e( 'A specialist may contact you to discuss treatment options', 'ennulifeassessments' ); ?></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
        
        
        <!-- Assessment JavaScript Debug Info -->
        <script>
        // Assessment will be automatically initialized by ennu-assessment-modern.js
        // when DOM is ready and .ennu-assessment container is found
        </script>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Render assessment questions
     * 
     * @param string $assessment_type Assessment type
     * @param array $config Assessment configuration
     * @return string
     */
    private function render_assessment_questions( $assessment_type, $config, $current_user_data = array() ) {
        $questions = $this->get_assessment_questions( $assessment_type );
        $output = '';
        
        foreach ( $questions as $index => $question ) {
            $question_number = $index + 1;
            $output .= $this->render_question( $assessment_type, $question_number, $question, $config, $current_user_data );
        }
        
        return $output;
    }
    
    /**
     * Render individual question
     * 
     * @param string $assessment_type Assessment type
     * @param int $question_number Question number
     * @param array $question Question data
     * @param array $config Assessment configuration
     * @return string
     */
    private function render_question( $assessment_type, $question_number, $question, $config, $current_user_data = array() ) {
        // Safeguard: ensure we always have a user object and a pre-selected value for global fields
        $user = wp_get_current_user();
        $user_id = ( $user && isset( $user->ID ) ) ? intval( $user->ID ) : 0;

        // Determine pre-selected value for questions tied to a global key
        $pre_selected_value = array();
        if ( isset( $question['global_key'] ) && $user_id ) {
            $pre_selected_value = get_user_meta( $user_id, 'ennu_global_' . $question['global_key'], true );
        }

        $active_class = $question_number === 1 ? 'active' : '';
        
        // Generate simple IDs based on assessment type (e.g., hair_q1, skin_q2, etc.)
        $assessment_prefix = str_replace('_assessment', '', $assessment_type);
        $simple_question_id = $assessment_prefix . '_q' . $question_number;
        
        ob_start();
        ?>
        <div class="question-slide <?php echo esc_attr( $active_class ); ?>" 
             data-step="<?php echo esc_attr( $question_number ); ?>" 
             data-question="<?php echo esc_attr( $question_number ); ?>"
             data-question-key="<?php echo esc_attr( $simple_question_id ); ?>"
             data-question-type="<?php echo esc_attr( $question['type'] ); ?>">
            
            <div class="question-header">
                <h2 class="question-title"><?php echo esc_html( $question['title'] ); ?></h2>
                <?php if ( ! empty( $question['description'] ) ) : ?>
                    <p class="question-description"><?php echo esc_html( $question['description'] ); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ( isset( $question['type'] ) && $question['type'] === 'dob_dropdowns' ) : ?>
                <!-- DOB Dropdowns -->
                <?php
                $dob = $current_user_data['dob_combined'] ?? '';
                $dob_parts = !empty($dob) ? explode('-', $dob) : array('', '', '');
                $year_val = $dob_parts[0];
                $month_val = $dob_parts[1];
                $day_val = $dob_parts[2];
                ?>
                <div class="dob-container">
                    <?php if ( ! empty( $question['show_age'] ) ) : ?>
                        <div class="dob-age-display">
                            Current Age: <span class="calculated-age">--</span> years old
                        </div>
                    <?php endif; ?>
                
                    <div class="dob-dropdowns">
                        <div class="dob-field">
                        <label for="<?php echo esc_attr( $simple_question_id ); ?>_month" style="display: block; margin-bottom: 5px; font-weight: 500;">Month</label>
                        <select id="<?php echo esc_attr( $simple_question_id ); ?>_month" 
                                name="<?php echo esc_attr( $simple_question_id ); ?>_month" 
                                class="dob-dropdown dob-month" 
                                <?php echo ! empty( $question['required'] ) ? 'required' : ''; ?>>
                            <option value="">Select Month</option>
                            <option value="01" <?php selected($month_val, '01'); ?>>January</option>
                            <option value="02" <?php selected($month_val, '02'); ?>>February</option>
                            <option value="03" <?php selected($month_val, '03'); ?>>March</option>
                            <option value="04" <?php selected($month_val, '04'); ?>>April</option>
                            <option value="05" <?php selected($month_val, '05'); ?>>May</option>
                            <option value="06" <?php selected($month_val, '06'); ?>>June</option>
                            <option value="07" <?php selected($month_val, '07'); ?>>July</option>
                            <option value="08" <?php selected($month_val, '08'); ?>>August</option>
                            <option value="09" <?php selected($month_val, '09'); ?>>September</option>
                            <option value="10" <?php selected($month_val, '10'); ?>>October</option>
                            <option value="11" <?php selected($month_val, '11'); ?>>November</option>
                            <option value="12" <?php selected($month_val, '12'); ?>>December</option>
                        </select>
                    </div>
                    
                    <div class="dob-field">
                        <label for="<?php echo esc_attr( $simple_question_id ); ?>_day" style="display: block; margin-bottom: 5px; font-weight: 500;">Day</label>
                        <select id="<?php echo esc_attr( $simple_question_id ); ?>_day" 
                                name="<?php echo esc_attr( $simple_question_id ); ?>_day" 
                                class="dob-dropdown dob-day" 
                                <?php echo ! empty( $question['required'] ) ? 'required' : ''; ?>>
                            <option value="">Day</option>
                            <?php for ( $day = 1; $day <= 31; $day++ ) : ?>
                                <?php $day_str = sprintf( '%02d', $day ); ?>
                                <option value="<?php echo $day_str; ?>" <?php selected($day_val, $day_str); ?>><?php echo $day; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="dob-field">
                        <label for="<?php echo esc_attr( $simple_question_id ); ?>_year" style="display: block; margin-bottom: 5px; font-weight: 500;">Year</label>
                        <select id="<?php echo esc_attr( $simple_question_id ); ?>_year" 
                                name="<?php echo esc_attr( $simple_question_id ); ?>_year" 
                                class="dob-dropdown dob-year" 
                                <?php echo ! empty( $question['required'] ) ? 'required' : ''; ?>>
                            <option value="">Year</option>
                            <?php 
                            $current_year = date( 'Y' );
                            $start_year = $current_year - 100; // 100 years ago
                            $end_year = $current_year - 13;   // Minimum age 13
                            for ( $year = $end_year; $year >= $start_year; $year-- ) : 
                            ?>
                                <option value="<?php echo $year; ?>" <?php selected($year_val, $year); ?>><?php echo $year; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Hidden field to store the complete DOB with simple ID -->
                <input type="hidden" 
                       name="<?php echo esc_attr( $simple_question_id ); ?>" 
                       class="dob-combined" 
                       id="<?php echo esc_attr( $simple_question_id ); ?>" 
                       value="<?php echo esc_attr($dob); ?>"
                       data-question="<?php echo esc_attr( $question_number ); ?>">
                
                <!-- Hidden field to store calculated age -->
                <input type="hidden" 
                       name="user_age" 
                       class="calculated-age-field" 
                       id="user_age" 
                       data-question="<?php echo esc_attr( $question_number ); ?>">
                </div>
                       
            <?php elseif ( isset( $question['type'] ) && $question['type'] === 'multiselect' ) : ?>
                <!-- Multiselect Checkboxes -->
                <div class="answer-options multiselect-container">
                    <?php if ( ! empty( $question['options'] ) ) : ?>
                        <?php foreach ( $question['options'] as $option ) : ?>
                            <div class="answer-option">
                                <input type="checkbox" 
                                       id="<?php echo esc_attr( $simple_question_id . "_" . $option['value'] ); ?>" 
                                       name="<?php echo esc_attr( $simple_question_id ); ?>[]" 
                                       value="<?php echo esc_attr( $option['value'] ); ?>"
                                       class="ennu-checkbox-input"
                                       <?php if (is_array($pre_selected_value) && in_array($option['value'], $pre_selected_value)) echo 'checked'; ?>>
                                <label for="<?php echo esc_attr( $simple_question_id . "_" . $option['value'] ); ?>">
                                    <?php echo esc_html( $option['label'] ); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                       
            <?php elseif ( isset( $question['type'] ) && $question['type'] === 'height_weight' ) : ?>
                <div class="height-weight-container">
                    <?php foreach ( $question['fields'] as $key => $field ) : ?>
                        <div class="hw-field">
                            <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label>
                            <input type="<?php echo esc_attr($field['type']); ?>" 
                                   id="<?php echo esc_attr($key); ?>" 
                                   name="<?php echo esc_attr($key); ?>"
                                   min="<?php echo esc_attr($field['min']); ?>"
                                   max="<?php echo esc_attr($field['max']); ?>"
                                   class="ennu-hw-input"
                                   required>
                        </div>
                    <?php endforeach; ?>
                </div>
                       
            <?php elseif ( isset( $question['type'] ) && $question['type'] === 'contact_info' ) : ?>
                <!-- Contact Information Form -->
                <?php
                // Get current user data for auto-population
                $user = wp_get_current_user();
                $user_logged_in = $user->ID > 0;
                
                // Pre-populate values for logged-in users
                $first_name = $user_logged_in ? $user->first_name : '';
                $last_name = $user_logged_in ? $user->last_name : '';
                $email = $user_logged_in ? $user->user_email : '';
                $billing_phone = $user_logged_in ? get_user_meta($user->ID, 'billing_phone', true) : '';
                
                if ( $user_logged_in ) {
                    error_log( 'ENNU: Auto-populating contact fields for user ' . get_current_user_id() . ': ' . $first_name . ' ' . $last_name . ' (' . $email . ') - Phone: ' . $billing_phone );
                }
                ?>
                <div class="contact-fields">
                    <?php if ( $user_logged_in && ($first_name || $email) ) : ?>
                        <div class="user-info-notice" style="margin-bottom: 20px; padding: 15px; background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border-radius: 8px; border-left: 4px solid #28a745; font-size: 0.95em;">
                            <p style="margin: 0; color: #155724;">
                                <strong>✓ Welcome back!</strong> We've pre-filled your information. Please review and update if needed.
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php foreach ( $question['fields'] as $field ) : ?>
                        <?php
                        // Get the pre-populated value for this field
                        $field_value = '';
                        $input_class = 'ennu-contact-input'; // Define the class here
                        if ( $user_logged_in ) {
                            switch ( $field['name'] ) {
                                case 'first_name':
                                    $field_value = $first_name;
                                    break;
                                case 'last_name':
                                    $field_value = $last_name;
                                    break;
                                case 'email':
                                    $field_value = $email;
                                    break;
                                case 'billing_phone':
                                    $field_value = $billing_phone;
                                    break;
                                case 'height_ft':
                                    $field_value = get_user_meta($user->ID, 'ennu_global_height_ft', true);
                                    break;
                                case 'height_in':
                                    $field_value = get_user_meta($user->ID, 'ennu_global_height_in', true);
                                    break;
                                case 'weight_lbs':
                                    $field_value = get_user_meta($user->ID, 'ennu_global_weight_lbs', true);
                                    break;
                            }
                        }
                        ?>
                        <div class="contact-field">
                            <label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                            <input type="<?php echo esc_attr( $field['type'] ); ?>" 
                                   id="<?php echo esc_attr( $field['name'] ); ?>" 
                                   name="<?php echo esc_attr( $field['name'] ); ?>"
                                   value="<?php echo esc_attr( $field_value ); ?>"
                                   placeholder="<?php echo esc_attr( $field['label'] ); ?>"
                                   class="<?php echo esc_attr( $input_class ); ?> form-control"
                                   style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 16px;"
                                   <?php echo ! empty( $field['required'] ) ? 'required' : ''; ?>
                                   <?php if ( $user_logged_in && ! empty( $field_value ) ) : ?>
                                       data-auto-populated="true"
                                   <?php endif; ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
                       
            <?php else : ?>
               <?php
                $columns = 0;
                if ( isset( $question["options"] ) && is_array( $question["options"] ) ) {
                    $columns = count( $question["options"] );
                }
                $is_global_field = isset($question['global_key']);
                $pre_selected_value = null;
                if ($is_global_field) {
                    $meta_key = 'ennu_global_' . $question['global_key'];
                    $pre_selected_value = get_user_meta($user->ID, $meta_key, true);
                }
                ?>
                <div class="answer-options columns-<?php echo esc_attr( $columns ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
                    <?php if ( isset( $question["options"] ) && is_array( $question["options"] ) ) : foreach ( $question["options"] as $option ) : ?>
                        <div class="answer-option">
                            <input type="radio" 
                                   id="<?php echo esc_attr( $simple_question_id . "_" . $option["value"] ); ?>" 
                                   name="<?php echo esc_attr( $simple_question_id ); ?>" 
                                   value="<?php echo esc_attr( $option["value"] ); ?>" 
                                   class="ennu-radio-input"
                                   <?php checked( $pre_selected_value, $option['value'] ); ?>
                                   data-value="<?php echo esc_attr( $option["value"] ); ?>" />
                            <label for="<?php echo esc_attr( $simple_question_id . "_" . $option["value"] ); ?>" data-value="<?php echo esc_attr( $option["value"] ); ?>">
                                <?php echo esc_html( $option["label"] ); ?>
                            </label>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Navigation Buttons for each question -->
            <div class="question-navigation">
                <?php if ( $question_number > 1 ) : ?>
                    <button type="button" class="nav-button prev">← Previous</button>
                <?php endif; ?>
                
                <?php 
                // Check if this is the last question
                $total_questions = count($this->get_assessment_questions($assessment_type));
                $is_last_question = $question_number >= $total_questions;
                ?>
                
                <button type="button" class="nav-button next <?php echo esc_attr( $is_last_question ? 'submit' : '' ); ?>">
                    <?php 
                    if ( $is_last_question && isset( $question['type'] ) && $question['type'] === 'contact_info' && ! empty( $question['button_text'] ) ) {
                        echo esc_html( $question['button_text'] );
                    } elseif ( $is_last_question ) {
                        echo esc_html__( 'Create My Account', 'ennulifeassessments' );
                    } else {
                        echo esc_html__( 'Next →', 'ennulifeassessments' );
                    }
                    ?>
                </button>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get assessment questions configuration
     * 
     * @param string $assessment_type Assessment type
     * @return array
     */
    public function get_assessment_questions( $assessment_type ) {
        // Return the questions for the specified assessment type from the loaded array.
        return $this->all_questions[ $assessment_type ] ?? array();
    }
    
    /**
     * Handle assessment form submission via AJAX.
     *
     * This method provides security checks, data sanitization, validation,
     * and saves the assessment data to the database.
     */
    public function handle_assessment_submission() {
        // 1. Security Check: Verify AJAX nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'ennu_ajax_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ), 403 );
            return;
        }

        // 2. Get and Sanitize Data
        $form_data = $this->sanitize_assessment_data( $_POST );
        
        // 3. Validate Data
        $validation_result = $this->validate_assessment_data( $form_data );
        if ( is_wp_error( $validation_result ) ) {
            wp_send_json_error( array( 'message' => $validation_result->get_error_message(), 'code' => $validation_result->get_error_code() ), 400 );
            return;
        }

        // 4. Determine User ID (Create user if doesn't exist)
        $email = $form_data['email'];
        $user_id = email_exists( $email );

        if ( ! $user_id ) {
            // User does not exist, create a new one with all available details
            $password = wp_generate_password();
            $user_data = array(
                'user_login' => $email,
                'user_email' => $email,
                'user_pass'  => $password,
                'first_name' => $form_data['first_name'] ?? '',
                'last_name'  => $form_data['last_name'] ?? '',
            );
            $user_id = wp_insert_user( $user_data );

            if ( is_wp_error( $user_id ) ) {
                wp_send_json_error( array( 'message' => 'Could not create a new user account: ' . $user_id->get_error_message() ), 500 );
                return;
            }
            // Log the new user in
            wp_set_current_user( $user_id );
            wp_set_auth_cookie( $user_id );
        } else {
            // User already exists, check if they are logged in
            if ( !is_user_logged_in() || get_current_user_id() != $user_id ) {
                $login_url = wp_login_url( get_permalink() );
                wp_send_json_error( array(
                    'message' => 'An account with this email already exists. Please <a href="' . esc_url($login_url) . '">log in</a> to continue.',
                    'action' => 'login_required'
                ), 409 );
                return;
            }
        }

        // 5. Calculate and save BMI if applicable
        if (isset($form_data['height_ft']) && isset($form_data['height_in']) && isset($form_data['weight_lbs'])) {
            $height_in_total = (intval($form_data['height_ft']) * 12) + intval($form_data['height_in']);
            $weight_lbs = intval($form_data['weight_lbs']);
            if ($height_in_total > 0 && $weight_lbs > 0) {
                // BMI Formula: (weight_lbs / (height_in * height_in)) * 703
                $bmi = ($weight_lbs / ($height_in_total * $height_in_total)) * 703;
                update_user_meta($user_id, 'ennu_calculated_bmi', round($bmi, 1));
            }
        }

        // 6. Save Core & Global Data (New Centralized Handler)
        $this->update_core_user_data($user_id, $form_data);

        // 7. Save Assessment-Specific Data
        $this->save_assessment_specific_meta( $user_id, $form_data );

        // 8. Calculate and Save Scores
        if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
            $scores = ENNU_Assessment_Scoring::calculate_scores( $form_data['assessment_type'], $form_data );
            if ( $scores ) {
                // Save historical data
                $historical_scores_key = 'ennu_' . $form_data['assessment_type'] . '_historical_scores';
                $historical_scores = get_user_meta($user_id, $historical_scores_key, true);
                if (!is_array($historical_scores)) { $historical_scores = []; }
                $historical_scores[] = ['date' => current_time('timestamp'), 'score' => $scores['overall_score']];
                update_user_meta($user_id, $historical_scores_key, $historical_scores);

                // Update latest score for easy access
                update_user_meta($user_id, 'ennu_' . $form_data['assessment_type'] . '_calculated_score', $scores['overall_score']);
                $interpretation = ENNU_Assessment_Scoring::get_score_interpretation( $scores['overall_score'] );
                update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_interpretation', $interpretation );
                update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_category_scores', $scores['category_scores'] );

                // Store results in a transient for the results page to pick up.
                $results_data = array(
                    'type' => $form_data['assessment_type'],
                    'score' => $scores['overall_score'],
                    'interpretation' => $interpretation,
                    'title' => $this->assessments[$form_data['assessment_type']]['title'] ?? 'Assessment',
                    'category_scores' => $scores['category_scores'],
                    'answers' => $form_data // Pass the user's answers to the results page
                );
                set_transient( 'ennu_assessment_results_' . $user_id, $results_data, 3600 ); // 1 hour
            }
        }

        // 9. Send Notifications (optional)
        // $this->send_assessment_notification( $user_id, $form_data );

        // 10. Return Success Response
        wp_send_json_success( array(
            'message' => 'Assessment submitted successfully!',
            'redirect_url' => $this->get_thank_you_url( $form_data['assessment_type'] )
        ) );
    }

    /**
     * Sanitize all incoming assessment data from the $_POST array.
     *
     * @param array $post_data The raw $_POST data.
     * @return array The sanitized data.
     */
    private function sanitize_assessment_data( $post_data ) {
        $sanitized_data = array();
        foreach ( $post_data as $key => $value ) {
            if ( is_array( $value ) ) {
                $sanitized_data[ sanitize_key( $key ) ] = array_map( 'sanitize_text_field', $value );
            } else {
                $sanitized_data[ sanitize_key( $key ) ] = sanitize_text_field( $value );
            }
        }
        return $sanitized_data;
    }

    /**
     * Validate the sanitized assessment data.
     *
     * @param array $data The sanitized data.
     * @return true|WP_Error True if valid, WP_Error on failure.
     */
    private function validate_assessment_data( $data ) {
        if ( empty( $data['assessment_type'] ) ) {
            return new WP_Error( 'validation_failed_assessment_type', 'Assessment type is missing.' );
        }
        if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
            return new WP_Error( 'validation_failed_email', 'A valid email address is required.' );
        }
        return true;
    }

    /**
     * Save the assessment-specific data as user meta.
     *
     * @param int $user_id The ID of the user.
     * @param array $data The sanitized assessment data.
     */
    private function save_assessment_specific_meta( $user_id, $data ) {
        $assessment_type = $data['assessment_type'];
        $questions = $this->get_assessment_questions( $assessment_type );

        // Map simple question IDs to their definitions
        $question_map = array();
        foreach ($questions as $index => $q_def) {
            $assessment_prefix = str_replace('_assessment', '', $assessment_type);
            $simple_question_id = $assessment_prefix . '_q' . ($index + 1);
            $question_map[$simple_question_id] = $q_def;
        }
        
        // Save only assessment-specific fields
        foreach ( $data as $key => $value ) {
            if (isset($question_map[$key])) {
                $question_def = $question_map[$key];
                // Only save if it's NOT a global field
                if (!isset($question_def['global_key'])) {
                    $value_to_save = $value; // Save the raw value, which could be an array for multiselect
                    $meta_key = 'ennu_' . $assessment_type . '_' . $key;
                    update_user_meta( $user_id, $meta_key, $value_to_save );
                }
            }
        }
    }

    /**
     * Update Core User Data across WP Native, WC, and Global meta.
     *
     * @param int $user_id The ID of the user.
     * @param array $data The sanitized assessment data.
     */
    private function update_core_user_data($user_id, $data) {
        // 1. Update WP Native User Fields
        $user_data_update = array('ID' => $user_id);
        if (isset($data['first_name'])) {
            $user_data_update['first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $user_data_update['last_name'] = $data['last_name'];
        }
        if (count($user_data_update) > 1) {
            wp_update_user($user_data_update);
        }

        // 2. Update WooCommerce and other standard meta fields
        if (isset($data['first_name'])) {
            update_user_meta($user_id, 'billing_first_name', $data['first_name']);
        }
        if (isset($data['last_name'])) {
            update_user_meta($user_id, 'billing_last_name', $data['last_name']);
        }
        if (isset($data['email'])) {
            update_user_meta($user_id, 'billing_email', $data['email']);
        }
        if (isset($data['billing_phone'])) {
            update_user_meta($user_id, 'billing_phone', $data['billing_phone']);
        }

        // 3. Update Custom Global Meta Fields from $_POST data directly
        // These are fields that might not be part of the current assessment's questions
        // but should still be treated as global if submitted.
        $other_globals = [
            'health_goals',
            'height_ft',
            'height_in',
            'weight_lbs'
        ];

        foreach ($other_globals as $key) {
            if (isset($data[$key])) {
                $value_to_save = is_array($data[$key]) ? $data[$key] : sanitize_text_field($data[$key]);
                update_user_meta($user_id, 'ennu_global_' . $key, $value_to_save);
            }
        }
    }
    
    /**
     * Send assessment notification email
     * 
     * @param array $data Assessment data
     */
    private function send_assessment_notification( $data ) {
        $to = $data['contact_email'];
        $subject = sprintf( 
            __( 'Your %s Results - ENNU Life', 'ennulifeassessments' ),
            $this->assessments[ $data['assessment_type'] ]['title']
        );
        
        $message = $this->get_assessment_email_template( $data );
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ENNU Life <noreply@ennulife.com>'
        );
        
        wp_mail( $to, $subject, $message, $headers );
        
        // Also send admin notification
        $admin_email = get_option( 'admin_email' );
        $admin_subject = sprintf( 
            __( 'New %s Submission', 'ennulifeassessments' ),
            $this->assessments[ $data['assessment_type'] ]['title']
        );
        $admin_message = $this->get_admin_notification_template( $data );
        
        wp_mail( $admin_email, $admin_subject, $admin_message, $headers );
    }
    
    /**
     * Get assessment email template
     * 
     * @param array $data Assessment data
     * @return string
     */
    private function get_assessment_email_template( $data ) {
        $assessment_title = $this->assessments[ $data['assessment_type'] ]['title'];
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title><?php echo esc_html( $assessment_title ); ?> Results</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #667eea;">Thank you for completing your <?php echo esc_html( $assessment_title ); ?>!</h1>
                
                <p>Dear <?php echo esc_html( $data['contact_name'] ); ?>,</p>
                
                <p>Thank you for taking the time to complete your assessment. Our medical team will review your responses and provide personalized recommendations.</p>
                
                <h2>What happens next?</h2>
                <ul>
                    <li>Our medical team will review your responses within 24 hours</li>
                    <li>You'll receive personalized recommendations via email</li>
                    <li>A specialist may contact you to discuss treatment options</li>
                </ul>
                
                <p>If you have any questions, please don't hesitate to contact us.</p>
                
                <p>Best regards,<br>The ENNU Life Team</p>
                
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                <p style="font-size: 12px; color: #666;">
                    This email was sent to <?php echo esc_html( $data['contact_email'] ); ?> because you completed an assessment on our website.
                </p>
            </div>
        </body>
        </html>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get admin notification template
     * 
     * @param array $data Assessment data
     * @return string
     */
    private function get_admin_notification_template( $data ) {
        $assessment_title = $this->assessments[ $data['assessment_type'] ]['title'];
        
        ob_start();
        ?>
        <h2>New <?php echo esc_html( $assessment_title ); ?> Submission</h2>
        
        <p><strong>Contact Information:</strong></p>
        <ul>
            <li>Name: <?php echo esc_html( $data['contact_name'] ); ?></li>
            <li>Email: <?php echo esc_html( $data['contact_email'] ); ?></li>
            <li>Phone: <?php echo esc_html( $data['contact_phone'] ); ?></li>
        </ul>
        
        <p><strong>Assessment Answers:</strong></p>
        <ul>
            <?php foreach ( $data['answers'] as $question => $answer ) : ?>
                <li><?php echo esc_html( ucwords( str_replace( '_', ' ', $question ) ) ); ?>: <?php echo esc_html( $answer ); ?></li>
            <?php endforeach; ?>
        </ul>
        
        <p><strong>Submission Time:</strong> <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></p>
        <?php
        
        return ob_get_clean();
    }
    
    /**
     * Get thank you page URL - FINAL CORRECTED VERSION
     *
     * @param string $assessment_type Assessment type
     * @return string
     */
    private function get_thank_you_url( $assessment_type ) {
        // This array maps each assessment type to its dedicated results page URL.
        $thank_you_pages = array(
            'welcome_assessment'         => home_url('/welcome/'),
            'hair_assessment'         => home_url('/hair-assessment-results/'),
            'ed_treatment_assessment' => home_url('/ed-treatment-results/'),
            'weight_loss_assessment'  => home_url('/weight-loss-results/'),
            'health_assessment'       => home_url('/health-assessment-results/'),
            'skin_assessment'         => home_url('/skin-assessment-results/'),
            // Add a fallback for any other case
            'general'                 => home_url('/assessment-results/')
        );
    
        // Return the correct URL for the assessment_type, or the general fallback if not found.
        return $thank_you_pages[$assessment_type] ?? $thank_you_pages['general'];
    }
    
    /**
     * Render assessment results page
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_results_page( $atts = array() ) {
        ob_start();
        include ENNU_LIFE_PLUGIN_PATH . 'templates/assessment-results.php';
        return ob_get_clean();
    }
    
    /**
     * Render chart page
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_chart_page( $atts = array() ) {
        ob_start();
        include ENNU_LIFE_PLUGIN_PATH . 'templates/assessment-chart.php';
        return ob_get_clean();
    }
    
    /**
     * Enqueue Chart.js script if a page contains the chart shortcode.
     */
    public function enqueue_chart_scripts() {
        global $post;
        if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'ennu-assessment-chart' ) || has_shortcode( $post->post_content, 'ennu-user-dashboard' ) ) ) {
            wp_enqueue_script(
                'chartjs',
                ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js',
                array(),
                '4.4.0',
                true
            );
            error_log('ENNU: Chart.js enqueued for page with chart shortcode.');
            // existing Chart.js
            wp_enqueue_style( 'user-dashboard-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
        }
    }
    
    /**
     * Enqueue results page styles if a page contains a results shortcode.
     */
    public function enqueue_results_styles() {
        global $post;
        $results_shortcodes = array(
            'ennu-hair-results', 
            'ennu-ed-results', 
            'ennu-weight-loss-results', 
            'ennu-health-results', 
            'ennu-skin-results'
        );

        $found = false;
        if ( is_a( $post, 'WP_Post' ) ) {
            foreach ($results_shortcodes as $shortcode) {
                if ( has_shortcode( $post->post_content, $shortcode ) ) {
                    $found = true;
                    break;
                }
            }
        }

        if ($found) {
            wp_enqueue_style(
                'ennu-results-page-style',
                ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-results-page.css',
                array(),
                ENNU_LIFE_VERSION
            );
            wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.0', true );
        }

        // In enqueue_results_styles(), add check for details shortcodes to load styles
        // if ( has_shortcode($post->post_content, 'ennu-hair-assessment-details') || ... )
        // enqueue assessment-details-page.css and chart.js

        // In enqueue_results_styles()
        global $post;
        $is_details_page = false;
        $details_shortcodes = [
            'ennu-hair-assessment-details',
            'ennu-ed-treatment-assessment-details',
            'ennu-weight-loss-assessment-details',
            'ennu-health-assessment-details',
            'ennu-skin-assessment-details',
        ];
        if (is_a($post, 'WP_Post')) {
            foreach ($details_shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    $is_details_page = true;
                    break;
                }
            }
        }

        if ($is_details_page) {
            wp_enqueue_style('ennu-details-page-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-details-page.css', [], ENNU_LIFE_VERSION);
            wp_enqueue_script('chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', [], '4.4.0', true);
        }
    }
    
    /**
     * Render thank you page shortcode, now with dynamic results.
     * 
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @param string $tag Shortcode tag
     * @return string
     */
    public function render_thank_you_page( $atts, $content = '', $tag = '' ) {
        // 1. Get User and Transient Data
        $user_id = get_current_user_id();
        $results_transient = $user_id ? get_transient('ennu_assessment_results_' . $user_id) : false;

        // If results exist, show them and delete the transient.
        if ( $results_transient ) {
            delete_transient( 'ennu_assessment_results_' . $user_id );

            // --- Full Results Display Logic ---
            $assessment_type = $results_transient['type'];
            $score = $results_transient['score'];
            $interpretation_arr = $results_transient['interpretation'];
            $interpretation_key = strtolower($interpretation_arr['level'] ?? 'fair');
            $category_scores = $results_transient['category_scores'];
            $user_answers = $results_transient['answers'] ?? [];
            $bmi = get_user_meta($user_id, 'ennu_calculated_bmi', true);

            // 3. Get Personalized Content from Config
            $content_config_file = plugin_dir_path( __FILE__ ) . '../includes/config/results-content.php';
            $content_config = file_exists($content_config_file) ? require $content_config_file : array();
            $content_data = $content_config[$assessment_type] ?? $content_config['default'];
            $result_content = $content_data['score_ranges'][$interpretation_key] ?? $content_data['score_ranges']['fair'];
            $conditional_recs = $content_data['conditional_recommendations'] ?? [];

            $matched_recs = [];
            if (!empty($conditional_recs) && !empty($user_answers)) {
                foreach ($conditional_recs as $question_key => $answer_recs) {
                    $simple_question_id = ENNU_Question_Mapper::get_simple_id_from_semantic_key($assessment_type, $question_key);
                    if ($simple_question_id && isset($user_answers[$simple_question_id])) {
                        $user_answer = $user_answers[$simple_question_id];
                        if (isset($answer_recs[$user_answer])) {
                            $matched_recs[] = $answer_recs[$user_answer];
                        }
                    }
                }
            }

            ob_start();
            ?>
            <div class="ennu-results-page">
                <div class="ennu-results-main-panel">
                    <h1 class="ennu-results-title"><?php echo esc_html($content_data['title']); ?></h1>
                    
                    <?php if ($bmi) : ?>
                    <div class="ennu-bmi-card">
                        <h2 class="ennu-bmi-card-title"><?php echo esc_html__( 'Your Body Mass Index (BMI)', 'ennulifeassessments' ); ?></h2>
                        <div class="ennu-bmi-value"><?php echo esc_html($bmi); ?></div>
                        <p class="ennu-bmi-info"><?php echo esc_html__( 'BMI is a measure of body fat based on height and weight.', 'ennulifeassessments' ); ?> <a href="#"><?php echo esc_html__( 'Learn more', 'ennulifeassessments' ); ?></a></p>
                    </div>
                    <?php endif; ?>

                    <div class="ennu-score-card">
                        <h2 class="ennu-score-card-title"><?php echo esc_html($result_content['title']); ?></h2>
                        <div class="ennu-overall-score-display">
                            <div class="ennu-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?></div>
                            <div class="ennu-score-max">/ 10</div>
                        </div>
                        <p class="ennu-score-summary"><?php echo esc_html($result_content['summary']); ?></p>
                    </div>
                    
                    <div class="ennu-recommendations-card">
                        <h3><?php echo esc_html__( 'Your Personalized Recommendations', 'ennulifeassessments' ); ?></h3>
                        <ul>
                            <?php foreach ($result_content['recommendations'] as $rec) : ?>
                                <li><?php echo esc_html($rec); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?php echo esc_url($this->get_assessment_cta_url($assessment_type)); ?>" class="ennu-cta-button"><?php echo esc_html($result_content['cta']); ?></a>
                    </div>

                    <?php if (!empty($matched_recs)) : ?>
                    <div class="ennu-conditional-recs-card">
                        <h3><?php echo esc_html__( 'Specific Observations', 'ennulifeassessments' ); ?></h3>
                        <ul>
                            <?php foreach ($matched_recs as $rec) : ?>
                                <li><?php echo esc_html($rec); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="ennu-results-sidebar">
                    <div class="ennu-category-scores-card">
                        <h3><?php echo esc_html__( 'Score Breakdown', 'ennulifeassessments' ); ?></h3>
                        <?php if (!empty($category_scores)) : ?>
                            <ul class="ennu-category-list">
                                <?php foreach ($category_scores as $category => $cat_score) : ?>
                                    <li>
                                        <span class="ennu-category-label"><?php echo esc_html($category); ?></span>
                                        <div class="ennu-category-bar-bg">
                                            <div class="ennu-category-bar-fill" style="width: <?php echo esc_attr( (float)$cat_score * 10 ); ?>%;"></div>
                                        </div>
                                        <span class="ennu-category-score"><?php echo esc_html( number_format( $cat_score, 1 ) ); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p><?php echo esc_html__( 'Category score details are not available.', 'ennulifeassessments' ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        // --- NEW: Handle the "Empty State" when no transient is found ---
        
        // Map the shortcode tag (e.g., 'ennu-hair-results') to an assessment type
        $assessment_type_map = array(
            'ennu-hair-results' => 'hair_assessment',
            'ennu-ed-results' => 'ed_treatment_assessment',
            'ennu-weight-loss-results' => 'weight_loss_assessment',
            'ennu-health-results' => 'health_assessment',
            'ennu-skin-results' => 'skin_assessment'
        );
        $assessment_type = $assessment_type_map[$tag] ?? null;
        
        ob_start();
        ?>
        <div class="ennu-results-empty-state">
            <div class="empty-state-icon">!</div>
            <h2 class="empty-state-title"><?php echo esc_html__( 'Your results have been processed.', 'ennulifeassessments' ); ?></h2>
            
            <?php if ( is_user_logged_in() ): ?>
                <p class="empty-state-message">
                    <?php echo esc_html__( 'Your assessment results are saved to your account. You can review your complete history and progress in your private dashboard at any time.', 'ennulifeassessments' ); ?>
                </p>
                <div class="empty-state-actions">
                    <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="ennu-action-button primary"><?php echo esc_html__( 'View My Dashboard', 'ennulifeassessments' ); ?></a>
                    <?php if ($assessment_type): ?>
                        <a href="<?php echo esc_url( $this->get_assessment_page_url($assessment_type) ); ?>" class="ennu-action-button secondary"><?php echo esc_html__( 'Take Assessment Again', 'ennulifeassessments' ); ?></a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="empty-state-message">
                    <?php echo esc_html__( 'To save your results and track your progress over time, please log in or create an account. Results for guest users are only displayed once.', 'ennulifeassessments' ); ?>
                </p>
                <div class="empty-state-actions">
                    <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="ennu-action-button primary"><?php echo esc_html__( 'Login or Create Account', 'ennulifeassessments' ); ?></a>
                    <?php if ($assessment_type): ?>
                        <a href="<?php echo esc_url( $this->get_assessment_page_url($assessment_type) ); ?>" class="ennu-action-button secondary"><?php echo esc_html__( 'Take Assessment Again', 'ennulifeassessments' ); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Helper function to get the URL for the main assessment page.
     * @param string $assessment_type
     * @return string
     */
    private function get_assessment_page_url($assessment_type) {
        $assessment_pages = array(
            'hair_assessment'         => home_url('/hair-assessment/'),
            'ed_treatment_assessment' => home_url('/ed-treatment-assessment/'),
            'weight_loss_assessment'  => home_url('/weight-loss-assessment/'),
            'health_assessment'       => home_url('/health-assessment/'),
            'skin_assessment'         => home_url('/skin-assessment/'),
        );
        return $assessment_pages[$assessment_type] ?? home_url('/');
    }

    /**
     * Helper function to get the CTA URL for an assessment results page.
     * @param string $assessment_type
     * @return string
     */
    private function get_assessment_cta_url($assessment_type) {
        $cta_links = array(
            'hair_assessment'         => home_url('/book-hair-consultation/'),
            'ed_treatment_assessment' => home_url('/book-ed-consultation/'),
            'weight_loss_assessment'  => home_url('/book-weight-loss-consultation/'),
            'health_assessment'       => home_url('/book-health-consultation/'),
            'skin_assessment'         => home_url('/book-skin-consultation/'),
        );
        return $cta_links[$assessment_type] ?? home_url('/book-a-consultation/');
    }

    /**
     * Get thank you content data for assessment type
     */
    private function get_thank_you_content($assessment_type) {
        $content_map = array(
            'hair_assessment' => array(
                'title' => 'Your Hair Assessment is Complete!',
                'message' => 'Thank you for completing your hair health assessment. Our hair restoration specialists will review your responses to create a personalized hair growth plan tailored to your specific needs.',
                'next_steps' => 'Schedule a consultation with our hair restoration specialists to discuss your personalized treatment options and get started on your hair growth journey.',
                'benefits' => array(
                    'Personalized hair restoration strategy',
                    'Advanced treatment options (PRP, transplants, medications)',
                    'Hair growth timeline and realistic expectations',
                    'Customized pricing for your treatment plan'
                ),
                'button_text' => 'Book Your Hair Consultation',
                'consultation_url' => home_url('/book-hair-consultation/'),
                'contact_label' => 'Questions about hair restoration?',
                'phone' => '+1-800-ENNU-HAIR',
                'phone_display' => '(800) ENNU-HAIR',
                'email' => 'hair@ennulife.com',
                'icon' => '🦱',
                'color' => '#667eea',
                'bg_color' => '#f8f9ff',
                'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'shadow' => 'rgba(102, 126, 234, 0.3)',
                'info_bg' => 'linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%)'
            ),
            'ed_treatment_assessment' => array(
                'title' => 'Your ED Assessment is Complete!',
                'message' => 'Thank you for taking this important step. Our medical professionals will confidentially review your responses to recommend the most effective ED treatment options for you.',
                'next_steps' => 'Schedule a confidential consultation with our medical specialists to discuss your personalized treatment options in a discreet and professional environment.',
                'benefits' => array(
                    'Confidential medical consultation',
                    'FDA-approved treatment options',
                    'Discreet and professional care',
                    'Personalized treatment recommendations'
                ),
                'button_text' => 'Book Your Confidential Call',
                'consultation_url' => home_url('/book-ed-consultation/'),
                'contact_label' => 'Confidential questions?',
                'phone' => '+1-800-ENNU-MENS',
                'phone_display' => '(800) ENNU-MENS',
                'email' => 'confidential@ennulife.com',
                'icon' => '🔒',
                'color' => '#f093fb',
                'bg_color' => '#fef7ff',
                'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'shadow' => 'rgba(240, 147, 251, 0.3)',
                'info_bg' => 'linear-gradient(135deg, #fef7ff 0%, #fdf2ff 100%)',
                'extra_section' => '<div class="privacy-notice" style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border-radius: 8px; border-left: 4px solid #28a745; font-size: 0.95em;"><p><strong>🔒 Your Privacy is Protected:</strong> All consultations are completely confidential and HIPAA compliant. Your information is secure and private.</p></div>'
            ),
            'weight_loss_assessment' => array(
                'title' => 'Your Weight Loss Assessment is Complete!',
                'message' => 'Thank you for completing your weight management assessment. Our team will create a comprehensive weight loss plan designed specifically for your goals and lifestyle.',
                'next_steps' => 'Schedule a consultation with our weight loss specialists to discuss your personalized treatment options and start your transformation journey today.',
                'benefits' => array(
                    'Customized weight loss strategy',
                    'Medical weight loss options (Semaglutide, etc.)',
                    'Nutritional guidance and meal planning',
                    'Long-term success and maintenance plan'
                ),
                'button_text' => 'Book Your Weight Loss Call',
                'consultation_url' => home_url('/book-weight-loss-consultation/'),
                'contact_label' => 'Questions about weight loss?',
                'phone' => '+1-800-ENNU-SLIM',
                'phone_display' => '(800) ENNU-SLIM',
                'email' => 'weightloss@ennulife.com',
                'icon' => '⚖️',
                'color' => '#4facfe',
                'bg_color' => '#f0faff',
                'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'shadow' => 'rgba(79, 172, 254, 0.3)',
                'info_bg' => 'linear-gradient(135deg, #f0faff 0%, #e6f7ff 100%)'
            ),
            'health_assessment' => array(
                'title' => 'Your Health Assessment is Complete!',
                'message' => 'Thank you for completing your comprehensive health evaluation. Our healthcare team will review your responses to create a personalized wellness plan for optimal health.',
                'next_steps' => 'Schedule a consultation with our healthcare specialists to discuss your personalized wellness plan and optimize your overall health.',
                'benefits' => array(
                    'Comprehensive health evaluation',
                    'Preventive care recommendations',
                    'Hormone optimization options',
                    'Ongoing health monitoring plan'
                ),
                'button_text' => 'Book Your Health Consultation',
                'consultation_url' => home_url('/book-health-consultation/'),
                'contact_label' => 'Questions about health optimization?',
                'phone' => '+1-800-ENNU-HLTH',
                'phone_display' => '(800) ENNU-HLTH',
                'email' => 'health@ennulife.com',
                'icon' => '🏥',
                'color' => '#fa709a',
                'bg_color' => '#fff8fb',
                'gradient' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                'shadow' => 'rgba(250, 112, 154, 0.3)',
                'info_bg' => 'linear-gradient(135deg, #fff8fb 0%, #fef5f8 100%)'
            ),
            'skin_assessment' => array(
                'title' => 'Your Skin Assessment is Complete!',
                'message' => 'Thank you for completing your skin health evaluation. Our dermatology specialists will review your responses to create a personalized skincare and treatment plan.',
                'next_steps' => 'Schedule a consultation with our skincare specialists to discuss your personalized treatment options and achieve your skin goals.',
                'benefits' => array(
                    'Personalized skincare regimen',
                    'Advanced treatments (Botox, fillers, laser)',
                    'Professional product recommendations',
                    'Skin rejuvenation timeline'
                ),
                'button_text' => 'Book Your Skin Consultation',
                'consultation_url' => home_url('/book-skin-consultation/'),
                'contact_label' => 'Questions about skincare?',
                'phone' => '+1-800-ENNU-SKIN',
                'phone_display' => '(800) ENNU-SKIN',
                'email' => 'skin@ennulife.com',
                'icon' => '✨',
                'color' => '#a8edea',
                'bg_color' => '#f0fffe',
                'gradient' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                'shadow' => 'rgba(168, 237, 234, 0.3)',
                'info_bg' => 'linear-gradient(135deg, #f0fffe 0%, #edfffe 100%)'
            )
        );
        
        return isset($content_map[$assessment_type]) ? $content_map[$assessment_type] : $content_map['health_assessment'];
    }

    /**
     * Adjust color brightness
     * 
     * @param string $hex_color Hex color code
     * @param int $percent Brightness adjustment percentage
     * @return string
     */
    private function adjust_color_brightness( $hex_color, $percent ) {
        $hex_color = ltrim( $hex_color, '#' );
        
        if ( strlen( $hex_color ) === 3 ) {
            $hex_color = str_repeat( substr( $hex_color, 0, 1 ), 2 ) . 
                        str_repeat( substr( $hex_color, 1, 1 ), 2 ) . 
                        str_repeat( substr( $hex_color, 2, 1 ), 2 );
        }
        
        $r = hexdec( substr( $hex_color, 0, 2 ) );
        $g = hexdec( substr( $hex_color, 2, 2 ) );
        $b = hexdec( substr( $hex_color, 4, 2 ) );
        
        $r = max( 0, min( 255, $r + ( $r * $percent / 100 ) ) );
        $g = max( 0, min( 255, $g + ( $g * $percent / 100 ) ) );
        $b = max( 0, min( 255, $b + ( $b * $percent / 100 ) ) );
        
        return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) . 
                    str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) . 
                    str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
    }
    
    /**
     * Render error message
     * 
     * @param string $message Error message
     * @return string
     */
    private function render_error_message( $message ) {
        return sprintf(
            '<div class="ennu-assessment-error" style="padding: 20px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 8px; text-align: center;">
                <h3>%s</h3>
                <p>%s</p>
            </div>',
            esc_html__( 'Assessment Unavailable', 'ennulifeassessments' ),
            esc_html( $message )
        );
    }

    // New method at end of class
    public function render_user_dashboard( $atts = array() ) {
        $user_id = get_current_user_id();
        if ( !$user_id ) {
            return '<p>' . esc_html__( 'You must be logged in to view your dashboard.', 'ennulifeassessments' ) . '</p>';
        }
        ob_start();
        include ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php';
        return ob_get_clean();
    }

    // New method at end of class
    public function render_detailed_results_page($atts, $content = '', $tag = '') {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__( 'You must be logged in to view this page.', 'ennulifeassessments' ) . '</p>';
        }
        $assessment_type = str_replace(['ennu-', '-assessment-details'], '', $tag);
        
        // Pass assessment_type to the template
        ob_start();
        set_query_var('assessment_type', $assessment_type);
        include ENNU_LIFE_PLUGIN_PATH . 'templates/assessment-details-page.php';
        return ob_get_clean();
    }

    public static function get_trinity_pillar_map() {
        return [
            'mind' => [
                'Psychological Factors',
                'Treatment Motivation',
                'Stress & Mental Health',
                'Readiness for Change',
            ],
            'body' => [
                'Condition Severity',
                'Medical Factors',
                'Drug Interactions',
                'Genetic Factors',
                'Nutritional Support',
                'Internal Health',
            ],
            'lifestyle' => [
                'Physical Health',
                'Treatment History',
                'Progression Timeline',
                'Symptom Pattern',
                'Sleep & Recovery',
                'Preventive Health',
                'Lifestyle Choices',
                'Environmental Factors',
                'Skincare Habits',
            ],
            'aesthetics' => [
                'Hair Health Status',
                'Primary Skin Issue',
                'Skin Characteristics',
                'Motivation & Goals', // e.g., weight loss goals
                'Current Status', // e.g., current weight status
            ]
        ];
    }
}

// Initialize the class
new ENNU_Assessment_Shortcodes();