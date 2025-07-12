THIS SHOULD BE A LINTER ERROR<?php
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
        $this->register_shortcodes();
        $this->setup_hooks();
    }
    
    /**
     * Initialize assessment configurations
     */
    private function init_assessments() {
        $this->assessments = array(
            'welcome_assessment' => array(
                'title' => __( 'Welcome Assessment', 'ennu-life' ),
                'description' => __( 'Let\'s get to know you and your health goals.', 'ennu-life' ),
                'questions' => 6,
                'theme_color' => '#5A67D8', // Indigo color
            ),
            'hair_assessment' => array(
                'title' => __( 'Hair Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive hair health evaluation', 'ennu-life' ),
                'questions' => 11,
                'theme_color' => '#667eea',
                'icon_set' => 'hair'
            ),
            'hair_restoration_assessment' => array(
                'title' => __( 'Hair Restoration Assessment', 'ennu-life' ),
                'description' => __( 'Advanced hair restoration evaluation', 'ennu-life' ),
                'questions' => 11,
                'theme_color' => '#764ba2',
                'icon_set' => 'restoration'
            ),
            'ed_treatment_assessment' => array(
                'title' => __( 'ED Treatment Assessment', 'ennu-life' ),
                'description' => __( 'Confidential ED treatment evaluation', 'ennu-life' ),
                'questions' => 12,
                'theme_color' => '#f093fb',
                'icon_set' => 'medical'
            ),
            'weight_loss_assessment' => array(
                'title' => __( 'Weight Loss Assessment', 'ennu-life' ),
                'description' => __( 'Personalized weight management evaluation', 'ennu-life' ),
                'questions' => 13,
                'theme_color' => '#4facfe',
                'icon_set' => 'fitness'
            ),
            'weight_loss_quiz' => array(
                'title' => __( 'Weight Loss Quiz', 'ennu-life' ),
                'description' => __( 'Quick weight loss readiness quiz', 'ennu-life' ),
                'questions' => 8,
                'theme_color' => '#43e97b',
                'icon_set' => 'quiz'
            ),
            'health_assessment' => array(
                'title' => __( 'Health Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive health evaluation', 'ennu-life' ),
                'questions' => 11,
                'theme_color' => '#fa709a',
                'icon_set' => 'health'
            ),
            'skin_assessment' => array(
                'title' => __( 'Skin Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive skin health evaluation', 'ennu-life' ),
                'questions' => 9,
                'theme_color' => '#a8edea',
                'icon_set' => 'skin'
            ),
            'advanced_skin_assessment' => array(
                'title' => __( 'Advanced Skin Assessment', 'ennu-life' ),
                'description' => __( 'Detailed skin health analysis', 'ennu-life' ),
                'questions' => 9,
                'theme_color' => '#a8edea',
                'icon_set' => 'skin'
            ),
            'skin_assessment_enhanced' => array(
                'title' => __( 'Skin Assessment Enhanced', 'ennu-life' ),
                'description' => __( 'Enhanced skin evaluation', 'ennu-life' ),
                'questions' => 8,
                'theme_color' => '#d299c2',
                'icon_set' => 'skincare'
            ),
            'hormone_assessment' => array(
                'title' => __( 'Hormone Assessment', 'ennu-life' ),
                'description' => __( 'Comprehensive hormone evaluation', 'ennu-life' ),
                'questions' => 12,
                'theme_color' => '#ffecd2',
                'icon_set' => 'hormone'
            )
        );
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
        
        error_log( "ENNU: Registered " . count( $core_assessments ) . " core assessment shortcodes + " . count( $thank_you_shortcodes ) . " thank you shortcodes + results page" );
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assessment_assets' ) );
        // Register both action names for compatibility
        add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_ennu_form_submit', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_nopriv_ennu_form_submit', array( $this, 'handle_assessment_submission' ) );
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
            return $this->render_error_message( __( 'Invalid assessment type.', 'ennu-life' ) );
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
            return $this->render_error_message( __( 'Assessment temporarily unavailable.', 'ennu-life' ) );
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
        $current_user = wp_get_current_user();
        
        // Start output buffering
        ob_start();
        
        // Include assessment template
        $template_file = $this->get_assessment_template( $assessment_type );
        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo $this->render_default_assessment( $assessment_type, $config, $atts );
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
    private function render_default_assessment( $assessment_type, $config, $atts ) {
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
                        <span><?php esc_html_e( 'Question', 'ennu-life' ); ?> 
                              <span id="currentStep" class="current-question">1</span> 
                              <?php esc_html_e( 'of', 'ennu-life' ); ?> 
                              <span id="totalSteps" class="total-questions"><?php echo esc_html( $total_questions ); ?></span>
                        </span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Assessment Form -->
            <form class="assessment-form" data-assessment="<?php echo esc_attr( $assessment_type ); ?>">
                <?php wp_nonce_field( 'ennu_assessment_' . $assessment_type, 'assessment_nonce' ); ?>
                <input type="hidden" name="action" value="ennu_submit_assessment">
                <input type="hidden" name="assessment_type" value="<?php echo esc_attr( $assessment_type ); ?>">
                
                <!-- Questions Container -->
                <div class="questions-container">
                    <?php echo $this->render_assessment_questions( $assessment_type, $config ); ?>
                </div>
                
                <!-- Success Message -->
                <div class="assessment-success" style="display: none;">
                    <div class="success-icon">✓</div>
                    <h2><?php esc_html_e( 'Assessment Complete!', 'ennu-life' ); ?></h2>
                    <p><?php esc_html_e( 'Thank you for completing your assessment. Your personalized results and recommendations will be sent to your email shortly.', 'ennu-life' ); ?></p>
                    <div class="next-steps">
                        <h3><?php esc_html_e( 'What happens next?', 'ennu-life' ); ?></h3>
                        <ul>
                            <li><?php esc_html_e( 'Our medical team will review your responses', 'ennu-life' ); ?></li>
                            <li><?php esc_html_e( 'You\'ll receive personalized recommendations via email', 'ennu-life' ); ?></li>
                            <li><?php esc_html_e( 'A specialist may contact you to discuss treatment options', 'ennu-life' ); ?></li>
                        </ul>
                    </div>
                </div>
            </form>
        </div>
        
        
        <!-- Assessment JavaScript Debug Info -->
        <script>
        console.log('ENNU Assessment Debug Info:');
        console.log('Assessment Type:', '<?php echo esc_js( $assessment_type ); ?>');
        console.log('Total Questions:', <?php echo intval( $total_questions ); ?>);
        console.log('Questions Array:', <?php echo wp_json_encode( array_map( function( $q ) { return $q['title']; }, $questions ) ); ?>);
        
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
    private function render_assessment_questions( $assessment_type, $config ) {
        $questions = $this->get_assessment_questions( $assessment_type );
        $output = '';
        
        foreach ( $questions as $index => $question ) {
            $question_number = $index + 1;
            $output .= $this->render_question( $assessment_type, $question_number, $question, $config );
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
    private function render_question( $assessment_type, $question_number, $question, $config ) {
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
             data-question-type="<?php echo esc_attr( $question['type'] ?? 'single' ); ?>">
            
            <div class="question-header">
                <h2 class="question-title"><?php echo esc_html( $question['title'] ); ?></h2>
                <?php if ( ! empty( $question['description'] ) ) : ?>
                    <p class="question-description"><?php echo esc_html( $question['description'] ); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if ( isset( $question['type'] ) && $question['type'] === 'dob_dropdowns' ) : ?>
                <!-- DOB Dropdowns -->
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
                            <option value="01">January</option>
                            <option value="02">February</option>
                            <option value="03">March</option>
                            <option value="04">April</option>
                            <option value="05">May</option>
                            <option value="06">June</option>
                            <option value="07">July</option>
                            <option value="08">August</option>
                            <option value="09">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
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
                                <option value="<?php echo sprintf( '%02d', $day ); ?>"><?php echo $day; ?></option>
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
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Hidden field to store the complete DOB with simple ID -->
                <input type="hidden" 
                       name="<?php echo esc_attr( $simple_question_id ); ?>" 
                       class="dob-combined" 
                       id="<?php echo esc_attr( $simple_question_id ); ?>" 
                       data-question="<?php echo esc_attr( $question_number ); ?>">
                
                <!-- Hidden field to store calculated age -->
                <input type="hidden" 
                       name="user_age" 
                       class="calculated-age-field" 
                       id="user_age" 
                       data-question="<?php echo esc_attr( $question_number ); ?>">
                </div>
                       
            <?php elseif ( isset( $question['type'] ) && $question['type'] === 'contact_info' ) : ?>
                <!-- Contact Information Form -->
                <?php
                // Get current user data for auto-population
                $current_user = wp_get_current_user();
                $user_logged_in = is_user_logged_in();
                
                // Pre-populate values for logged-in users
                $first_name = '';
                $last_name = '';
                $email = '';
                $billing_phone = '';
                
                if ( $user_logged_in ) {
                    $first_name = $current_user->first_name;
                    $last_name = $current_user->last_name;
                    $email = $current_user->user_email;
                    
                    // Try to get billing phone from various user meta fields
                    $billing_phone = get_user_meta( $current_user->ID, 'billing_phone', true );
                    if ( empty( $billing_phone ) ) {
                        $billing_phone = get_user_meta( $current_user->ID, 'phone', true );
                    }
                    if ( empty( $billing_phone ) ) {
                        $billing_phone = get_user_meta( $current_user->ID, 'user_phone', true );
                    }
                    
                    error_log( 'ENNU: Auto-populating contact fields for user ' . $current_user->ID . ': ' . $first_name . ' ' . $last_name . ' (' . $email . ') - Phone: ' . $billing_phone );
                }
                ?>
                <div class="contact-fields">
                    <?php if ( $user_logged_in ) : ?>
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
                            }
                        }
                        ?>
                        <div class="contact-field">
                            <label for="<?php echo esc_attr( $field['name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
                            <input type="<?php echo esc_attr( $field['type'] ); ?>" 
                                   id="<?php echo esc_attr( $field['name'] ); ?>" 
                                   name="<?php echo esc_attr( $field['name'] ); ?>"
                                   value="<?php echo esc_attr( $field_value ); ?>"
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
                ?>
                <div class="answer-options" data-columns="<?php echo esc_attr( $columns ); ?>">
                    <?php if ( isset( $question["options"] ) && is_array( $question["options"] ) ) : foreach ( $question["options"] as $option ) : ?>
                        <div class="answer-option">
                            <input type="radio" 
                                   id="<?php echo esc_attr( $simple_question_id . "_" . $option["value"] ); ?>" 
                                   name="<?php echo esc_attr( $simple_question_id ); ?>" 
                                   value="<?php echo esc_attr( $option["value"] ); ?>" 
                                   class="ennu-radio-input"
                                   data-value="<?php echo esc_attr( $option['value'] ); ?>" />
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
                <?php else : ?>
                    <div style="width: 120px;"></div>
                <?php endif; ?>
                
                <?php 
                // Check if this is the last question
                $total_questions = count($this->get_assessment_questions($assessment_type));
                $is_last_question = $question_number >= $total_questions;
                ?>
                
                <button type="button" class="nav-button next <?php echo $is_last_question ? 'submit' : ''; ?>">
                    <?php 
                    if ( $is_last_question && isset( $question['type'] ) && $question['type'] === 'contact_info' && ! empty( $question['button_text'] ) ) {
                        echo esc_html( $question['button_text'] );
                    } elseif ( $is_last_question ) {
                        echo 'Create My Account';
                    } else {
                        echo 'Next →';
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
    private function get_assessment_questions( $assessment_type ) {
        switch ( $assessment_type ) {
// In get_assessment_questions()
        
        case 'welcome_assessment':
            return array(
            array(
                'title'       => __( 'What gender were you assigned at birth?', 'ennu-life' ),
                'description' => __( 'This helps us tailor our recommendations.', 'ennu-life' ),
                'type'        => 'single',
                'options'     => array(
                    array( 'value' => 'female', 'label' => __( 'Female', 'ennu-life' ) ),
                    array( 'value' => 'male', 'label' => __( 'Male', 'ennu-life' ) )
                )
            ),
            array(
                'title'       => __( 'What is your age?', 'ennu-life' ),
                'description' => __( 'Your age helps us understand your health profile.', 'ennu-life' ),
                'type' => 'dob_dropdowns',
                'field_name' => 'date_of_birth',
                'required' => true,
                'show_age' => true
            ),
            array(
                'title'       => __( 'What are your health goals?', 'ennu-life' ),
                'description' => __( 'Select all that apply. This helps us personalize your journey.', 'ennu-life' ),
                'type'        => 'multiselect',
                'options'     => array(
                    array( 'value' => 'live_longer', 'label' => __( 'Live longer', 'ennu-life' ) ),
                    array( 'value' => 'boost_energy', 'label' => __( 'Boost energy', 'ennu-life' ) ),
                    array( 'value' => 'improve_sleep', 'label' => __( 'Improve sleep', 'ennu-life' ) ),
                    array( 'value' => 'lose_weight', 'label' => __( 'Lose weight', 'ennu-life' ) ),
                    array( 'value' => 'build_muscle', 'label' => __( 'Build muscle', 'ennu-life' ) ),
                    array( 'value' => 'sharpen_focus', 'label' => __( 'Sharpen focus & memory', 'ennu-life' ) ),
                    array( 'value' => 'balance_hormones', 'label' => __( 'Balance hormones', 'ennu-life' ) ),
                    array( 'value' => 'improve_mood', 'label' => __( 'Improve mood', 'ennu-life' ) ),
                    array( 'value' => 'boost_libido', 'label' => __( 'Boost libido & performance', 'ennu-life' ) ),
                    array( 'value' => 'support_heart', 'label' => __( 'Support heart health', 'ennu-life' ) ),
                    array( 'value' => 'manage_menopause', 'label' => __( 'Manage menopause', 'ennu-life' ) ),
                    array( 'value' => 'increase_testosterone', 'label' => __( 'Increase testosterone', 'ennu-life' ) )
                )
            ),
                array(
                    'title'       => __( 'What\'s your first and last name?', 'ennu-life' ),
                    'type'        => 'contact_info',
                    'fields'      => array(
                        array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text' ),
                        array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text' )
                    )
                ),
                array(
                    'title'       => __( 'What’s your email address?', 'ennu-life' ),
                    'type'        => 'contact_info',
                    'fields'      => array(
                        array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email' )
                    )
                ),
                array(
                    'title'       => __( 'What’s your mobile number?', 'ennu-life' ),
                    'type'        => 'contact_info',
                    'fields'      => array(
                        array( 'name' => 'billing_phone', 'label' => 'Mobile Number', 'type' => 'tel' )
                    ),
                    'button_text' => 'Create My Account'
                )
            );
            
        case 'hair_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your date of birth?', 'ennu-life' ),
                        'description' => __( 'This helps us recommend age-appropriate hair treatments.', 'ennu-life' ),
                        'type' => 'dob_dropdowns',
                        'field_name' => 'date_of_birth',
                        'required' => true,
                        'show_age' => true
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Hair loss patterns can vary by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female' ),
                        )
                    ),
                    array(
                        'title' => __( 'What are your main hair concerns?', 'ennu-life' ),
                        'description' => __( 'Select your primary hair issue.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'thinning', 'label' => 'Thinning Hair' ),
                            array( 'value' => 'receding', 'label' => 'Receding Hairline' ),
                            array( 'value' => 'bald_spots', 'label' => 'Bald Spots' ),
                            array( 'value' => 'overall_loss', 'label' => 'Overall Hair Loss' )
                        )
                    ),
                    array(
                        'title' => __( 'How long have you noticed hair changes?', 'ennu-life' ),
                        'description' => __( 'Duration helps determine treatment approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'recent', 'label' => 'Less than 6 months' ),
                            array( 'value' => 'moderate', 'label' => '6 months - 2 years' ),
                            array( 'value' => 'long', 'label' => '2-5 years' ),
                            array( 'value' => 'very_long', 'label' => 'More than 5 years' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you rate the speed of hair loss?', 'ennu-life' ),
                        'description' => __( 'This helps determine urgency of treatment.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'slow', 'label' => 'Very Slow' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate' ),
                            array( 'value' => 'fast', 'label' => 'Fast' ),
                            array( 'value' => 'very_fast', 'label' => 'Very Fast' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have a family history of hair loss?', 'ennu-life' ),
                        'description' => __( 'Genetics play a major role in hair loss.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No Family History' ),
                            array( 'value' => 'mother', 'label' => 'Mother\'s Side' ),
                            array( 'value' => 'father', 'label' => 'Father\'s Side' ),
                            array( 'value' => 'both', 'label' => 'Both Sides' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current stress level?', 'ennu-life' ),
                        'description' => __( 'Stress can significantly impact hair health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low Stress' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate Stress' ),
                            array( 'value' => 'high', 'label' => 'High Stress' ),
                            array( 'value' => 'very_high', 'label' => 'Very High Stress' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your diet quality?', 'ennu-life' ),
                        'description' => __( 'Nutrition affects hair growth and strength.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Excellent' ),
                            array( 'value' => 'good', 'label' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you tried any hair loss treatments?', 'ennu-life' ),
                        'description' => __( 'Previous treatments help guide recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No Treatments' ),
                            array( 'value' => 'otc', 'label' => 'Over-the-Counter' ),
                            array( 'value' => 'prescription', 'label' => 'Prescription Meds' ),
                            array( 'value' => 'procedures', 'label' => 'Medical Procedures' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your hair restoration goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'stop_loss', 'label' => 'Stop Hair Loss' ),
                            array( 'value' => 'regrow', 'label' => 'Regrow Hair' ),
                            array( 'value' => 'thicken', 'label' => 'Thicken Hair' ),
                            array( 'value' => 'improve', 'label' => 'Overall Improvement' )
                        )
                    ),
                    array(
                        'title' => __( 'Contact Information', 'ennu-life' ),
                        'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennu-life' ),
                        'type' => 'contact_info',
                        'field_name' => 'contact_info',
                        'required' => true,
                        'fields' => array(
                            array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                            array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
                        ),
                        'button_text' => 'View My Assessment Results'
                    )
                );
                
            case 'ed_treatment_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your date of birth?', 'ennu-life' ),
                        'description' => __( 'Age helps determine the most appropriate treatment approach.', 'ennu-life' ),
                        'type' => 'dob_dropdowns',
                        'field_name' => 'date_of_birth',
                        'required' => true,
                        'show_age' => true
                    ),
                    array(
                        'title' => __( 'What\'s your relationship status?', 'ennu-life' ),
                        'description' => __( 'This helps us understand your treatment priorities.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'single', 'label' => 'Single' ),
                            array( 'value' => 'dating', 'label' => 'Dating' ),
                            array( 'value' => 'married', 'label' => 'Married/Partnered' ),
                            array( 'value' => 'divorced', 'label' => 'Divorced/Separated' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe the severity of your ED?', 'ennu-life' ),
                        'description' => __( 'This helps determine the most effective treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'mild', 'label' => 'Mild' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate' ),
                            array( 'value' => 'severe', 'label' => 'Severe' ),
                            array( 'value' => 'complete', 'label' => 'Complete' )
                        )
                    ),
                    array(
                        'title' => __( 'How long have you been experiencing symptoms?', 'ennu-life' ),
                        'description' => __( 'Duration affects treatment approach and expectations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'recent', 'label' => 'Less than 6 months' ),
                            array( 'value' => 'moderate', 'label' => '6 months - 2 years' ),
                            array( 'value' => 'long', 'label' => '2-5 years' ),
                            array( 'value' => 'very_long', 'label' => 'More than 5 years' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have any of these health conditions?', 'ennu-life' ),
                        'description' => __( 'Certain conditions affect treatment options and safety.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'None of these' ),
                            array( 'value' => 'diabetes', 'label' => 'Diabetes' ),
                            array( 'value' => 'heart', 'label' => 'Heart Disease' ),
                            array( 'value' => 'hypertension', 'label' => 'High Blood Pressure' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you tried any ED treatments before?', 'ennu-life' ),
                        'description' => __( 'Previous treatments help guide our recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No previous treatments' ),
                            array( 'value' => 'oral', 'label' => 'Oral medications' ),
                            array( 'value' => 'injections', 'label' => 'Injections' ),
                            array( 'value' => 'devices', 'label' => 'Vacuum devices' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you smoke or use tobacco?', 'ennu-life' ),
                        'description' => __( 'Smoking significantly affects blood flow and treatment effectiveness.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'never', 'label' => 'Never smoked' ),
                            array( 'value' => 'former', 'label' => 'Former smoker' ),
                            array( 'value' => 'occasional', 'label' => 'Occasional smoker' ),
                            array( 'value' => 'regular', 'label' => 'Regular smoker' )
                        )
                    ),
                    array(
                        'title' => __( 'How often do you exercise?', 'ennu-life' ),
                        'description' => __( 'Physical fitness affects blood flow and overall sexual health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'never', 'label' => 'Never' ),
                            array( 'value' => 'rarely', 'label' => 'Rarely' ),
                            array( 'value' => 'regularly', 'label' => 'Regularly' ),
                            array( 'value' => 'daily', 'label' => 'Daily' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current stress level?', 'ennu-life' ),
                        'description' => __( 'Stress is a major factor in erectile dysfunction.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate' ),
                            array( 'value' => 'high', 'label' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very High' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary treatment goal?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right treatment plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'restore', 'label' => 'Restore function' ),
                            array( 'value' => 'confidence', 'label' => 'Boost confidence' ),
                            array( 'value' => 'performance', 'label' => 'Improve performance' ),
                            array( 'value' => 'relationship', 'label' => 'Improve relationship' )
                        )
                    ),
                    array(
                        'title' => __( 'Are you currently taking any medications?', 'ennu-life' ),
                        'description' => __( 'Some medications can affect ED treatment options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No medications' ),
                            array( 'value' => 'blood_pressure', 'label' => 'Blood pressure meds' ),
                            array( 'value' => 'antidepressants', 'label' => 'Antidepressants' ),
                            array( 'value' => 'other', 'label' => 'Other medications' )
                        )
                    ),
                    array(
                        'title' => __( 'Contact Information', 'ennu-life' ),
                        'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennu-life' ),
                        'type' => 'contact_info',
                        'field_name' => 'contact_info',
                        'required' => true,
                        'fields' => array(
                            array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                            array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
                        ),
                        'button_text' => 'View My Assessment Results'
                    )
                );
                
            case 'weight_loss_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your date of birth?', 'ennu-life' ),
                        'description' => __( 'Age affects metabolism and weight loss approach.', 'ennu-life' ),
                        'type' => 'dob_dropdowns',
                        'field_name' => 'date_of_birth',
                        'required' => true,
                        'show_age' => true
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Weight loss strategies can vary by gender.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary weight loss goal?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the right plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'lose_10', 'label' => 'Lose 10-20 lbs' ),
                            array( 'value' => 'lose_30', 'label' => 'Lose 20-50 lbs' ),
                            array( 'value' => 'lose_50', 'label' => 'Lose 50+ lbs' ),
                            array( 'value' => 'maintain', 'label' => 'Maintain current weight' )
                        )
                    ),
                    array(
                        'title' => __( 'What motivates you most to lose weight?', 'ennu-life' ),
                        'description' => __( 'Understanding motivation helps maintain long-term success.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'health', 'label' => 'Health improvement' ),
                            array( 'value' => 'appearance', 'label' => 'Look better' ),
                            array( 'value' => 'confidence', 'label' => 'Boost confidence' ),
                            array( 'value' => 'energy', 'label' => 'More energy' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your target timeline?', 'ennu-life' ),
                        'description' => __( 'Realistic timelines lead to sustainable results.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => '3_months', 'label' => '3 months' ),
                            array( 'value' => '6_months', 'label' => '6 months' ),
                            array( 'value' => '1_year', 'label' => '1 year' ),
                            array( 'value' => 'no_rush', 'label' => 'No specific timeline' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your current eating habits?', 'ennu-life' ),
                        'description' => __( 'Current habits help determine the best approach.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'healthy', 'label' => 'Generally healthy' ),
                            array( 'value' => 'average', 'label' => 'Average/Mixed' ),
                            array( 'value' => 'poor', 'label' => 'Poor/Unhealthy' ),
                            array( 'value' => 'emotional', 'label' => 'Emotional eating' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your current activity level?', 'ennu-life' ),
                        'description' => __( 'Activity level affects weight loss strategy and timeline.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'sedentary', 'label' => 'Sedentary (desk job)' ),
                            array( 'value' => 'light', 'label' => 'Lightly active' ),
                            array( 'value' => 'moderate', 'label' => 'Moderately active' ),
                            array( 'value' => 'very_active', 'label' => 'Very active' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your biggest weight loss challenge?', 'ennu-life' ),
                        'description' => __( 'Identifying challenges helps create targeted solutions.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'cravings', 'label' => 'Food cravings' ),
                            array( 'value' => 'time', 'label' => 'Lack of time' ),
                            array( 'value' => 'motivation', 'label' => 'Staying motivated' ),
                            array( 'value' => 'knowledge', 'label' => 'Don\'t know what to do' )
                        )
                    ),
                    array(
                        'title' => __( 'Have you tried weight loss programs before?', 'ennu-life' ),
                        'description' => __( 'Previous experiences help avoid past mistakes.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No previous programs' ),
                            array( 'value' => 'diets', 'label' => 'Fad diets' ),
                            array( 'value' => 'programs', 'label' => 'Commercial programs' ),
                            array( 'value' => 'medical', 'label' => 'Medical supervision' )
                        )
                    ),
                    array(
                        'title' => __( 'Do you have any health conditions?', 'ennu-life' ),
                        'description' => __( 'Health conditions affect weight loss approach and safety.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'none', 'label' => 'No health conditions' ),
                            array( 'value' => 'diabetes', 'label' => 'Diabetes' ),
                            array( 'value' => 'thyroid', 'label' => 'Thyroid issues' ),
                            array( 'value' => 'other', 'label' => 'Other conditions' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your budget for weight loss support?', 'ennu-life' ),
                        'description' => __( 'Budget helps determine the best program options.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'minimal', 'label' => 'Minimal ($0-50/month)' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate ($50-150/month)' ),
                            array( 'value' => 'substantial', 'label' => 'Substantial ($150-300/month)' ),
                            array( 'value' => 'premium', 'label' => 'Premium ($300+/month)' )
                        )
                    ),
                    array(
                        'title' => __( 'What type of support do you prefer?', 'ennu-life' ),
                        'description' => __( 'Support style affects program success and satisfaction.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'self_guided', 'label' => 'Self-guided program' ),
                            array( 'value' => 'group', 'label' => 'Group support' ),
                            array( 'value' => 'coach', 'label' => 'Personal coach' ),
                            array( 'value' => 'medical', 'label' => 'Medical supervision' )
                        )
                    ),
                    array(
                        'title' => __( 'Contact Information', 'ennu-life' ),
                        'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennu-life' ),
                        'type' => 'contact_info',
                        'field_name' => 'contact_info',
                        'required' => true,
                        'fields' => array(
                            array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                            array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
                        ),
                        'button_text' => 'View My Assessment Results'
                    )
                );
                
            case 'health_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your date of birth?', 'ennu-life' ),
                        'description' => __( 'Age helps us provide age-appropriate health recommendations.', 'ennu-life' ),
                        'type' => 'dob_dropdowns',
                        'field_name' => 'date_of_birth',
                        'required' => true,
                        'show_age' => true
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Gender affects health risks and screening recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you rate your overall health?', 'ennu-life' ),
                        'description' => __( 'Your self-assessment helps guide our recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Excellent' ),
                            array( 'value' => 'good', 'label' => 'Good' ),
                            array( 'value' => 'fair', 'label' => 'Fair' ),
                            array( 'value' => 'poor', 'label' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'How are your energy levels?', 'ennu-life' ),
                        'description' => __( 'Energy levels can indicate various health issues.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'high', 'label' => 'High energy' ),
                            array( 'value' => 'normal', 'label' => 'Normal energy' ),
                            array( 'value' => 'low', 'label' => 'Low energy' ),
                            array( 'value' => 'very_low', 'label' => 'Very low energy' )
                        )
                    ),
                    array(
                        'title' => __( 'How often do you exercise?', 'ennu-life' ),
                        'description' => __( 'Physical activity is crucial for overall health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'daily', 'label' => 'Daily' ),
                            array( 'value' => 'few_times_week', 'label' => 'Few times per week' ),
                            array( 'value' => 'weekly', 'label' => 'Once a week' ),
                            array( 'value' => 'rarely', 'label' => 'Rarely/Never' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your diet?', 'ennu-life' ),
                        'description' => __( 'Diet quality significantly impacts health outcomes.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'very_healthy', 'label' => 'Very healthy' ),
                            array( 'value' => 'mostly_healthy', 'label' => 'Mostly healthy' ),
                            array( 'value' => 'average', 'label' => 'Average' ),
                            array( 'value' => 'poor', 'label' => 'Poor' )
                        )
                    ),
                    array(
                        'title' => __( 'How well do you sleep?', 'ennu-life' ),
                        'description' => __( 'Sleep quality affects every aspect of health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'excellent', 'label' => 'Sleep very well' ),
                            array( 'value' => 'good', 'label' => 'Sleep well' ),
                            array( 'value' => 'fair', 'label' => 'Sleep okay' ),
                            array( 'value' => 'poor', 'label' => 'Sleep poorly' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your stress level?', 'ennu-life' ),
                        'description' => __( 'Chronic stress impacts both physical and mental health.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'low', 'label' => 'Low stress' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate stress' ),
                            array( 'value' => 'high', 'label' => 'High stress' ),
                            array( 'value' => 'very_high', 'label' => 'Very high stress' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main health goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create a personalized plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'prevent_disease', 'label' => 'Prevent disease' ),
                            array( 'value' => 'lose_weight', 'label' => 'Lose weight' ),
                            array( 'value' => 'more_energy', 'label' => 'More energy' ),
                            array( 'value' => 'better_mood', 'label' => 'Better mood' )
                        )
                    ),
                    array(
                        'title' => __( 'What type of health assessment interests you most?', 'ennu-life' ),
                        'description' => __( 'This helps us prioritize your health screening recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'comprehensive', 'label' => 'Comprehensive checkup' ),
                            array( 'value' => 'preventive', 'label' => 'Preventive screening' ),
                            array( 'value' => 'specific', 'label' => 'Specific concern' ),
                            array( 'value' => 'wellness', 'label' => 'Wellness optimization' )
                        )
                    ),
                    array(
                        'title' => __( 'Contact Information', 'ennu-life' ),
                        'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennu-life' ),
                        'type' => 'contact_info',
                        'field_name' => 'contact_info',
                        'required' => true,
                        'fields' => array(
                            array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                            array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
                        ),
                        'button_text' => 'View My Assessment Results'
                    )
                );
                
            case 'skin_assessment':
                return array(
                    array(
                        'title' => __( 'What\'s your date of birth?', 'ennu-life' ),
                        'description' => __( 'Age helps determine appropriate skin treatments.', 'ennu-life' ),
                        'type' => 'dob_dropdowns',
                        'field_name' => 'date_of_birth',
                        'required' => true,
                        'show_age' => true
                    ),
                    array(
                        'title' => __( 'What\'s your gender?', 'ennu-life' ),
                        'description' => __( 'Gender affects skin characteristics and concerns.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'male', 'label' => 'Male' ),
                            array( 'value' => 'female', 'label' => 'Female' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your skin type?', 'ennu-life' ),
                        'description' => __( 'Knowing your skin type helps recommend the right products.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'normal', 'label' => 'Normal' ),
                            array( 'value' => 'oily', 'label' => 'Oily' ),
                            array( 'value' => 'dry', 'label' => 'Dry' ),
                            array( 'value' => 'combination', 'label' => 'Combination' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your primary skin concern?', 'ennu-life' ),
                        'description' => __( 'This helps us prioritize your treatment recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'acne', 'label' => 'Acne/Breakouts' ),
                            array( 'value' => 'wrinkles', 'label' => 'Wrinkles/Fine lines' ),
                            array( 'value' => 'dark_spots', 'label' => 'Dark spots' ),
                            array( 'value' => 'dullness', 'label' => 'Dullness/Uneven tone' )
                        )
                    ),
                    array(
                        'title' => __( 'How much sun exposure do you typically get?', 'ennu-life' ),
                        'description' => __( 'Sun exposure affects skin damage and treatment needs.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'minimal', 'label' => 'Minimal' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate' ),
                            array( 'value' => 'high', 'label' => 'High' ),
                            array( 'value' => 'very_high', 'label' => 'Very high' )
                        )
                    ),
                    array(
                        'title' => __( 'How would you describe your current skincare routine?', 'ennu-life' ),
                        'description' => __( 'Current routine affects our recommendations.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'extensive', 'label' => 'Extensive (5+ steps)' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate (3-4 steps)' ),
                            array( 'value' => 'basic', 'label' => 'Basic (1-2 steps)' ),
                            array( 'value' => 'none', 'label' => 'No routine' )
                        )
                    ),
                    array(
                        'title' => __( 'What\'s your budget for skincare?', 'ennu-life' ),
                        'description' => __( 'Budget helps us recommend appropriate products and treatments.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'budget', 'label' => 'Budget-friendly ($0-50/month)' ),
                            array( 'value' => 'moderate', 'label' => 'Moderate ($50-150/month)' ),
                            array( 'value' => 'premium', 'label' => 'Premium ($150-300/month)' ),
                            array( 'value' => 'luxury', 'label' => 'Luxury ($300+/month)' )
                        )
                    ),
                    array(
                        'title' => __( 'What are your main skincare goals?', 'ennu-life' ),
                        'description' => __( 'Understanding your goals helps create the perfect plan.', 'ennu-life' ),
                        'options' => array(
                            array( 'value' => 'clear_skin', 'label' => 'Clear, healthy skin' ),
                            array( 'value' => 'anti_aging', 'label' => 'Anti-aging' ),
                            array( 'value' => 'glow', 'label' => 'Radiant glow' ),
                            array( 'value' => 'maintenance', 'label' => 'Maintain current skin' )
                        )
                    ),
                    array(
                        'title' => __( 'Contact Information', 'ennu-life' ),
                        'description' => __( 'Please provide your contact details to receive your personalized assessment results.', 'ennu-life' ),
                        'type' => 'contact_info',
                        'field_name' => 'contact_info',
                        'required' => true,
                        'fields' => array(
                            array( 'name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'last_name', 'label' => 'Last Name', 'type' => 'text', 'required' => true ),
                            array( 'name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true ),
                            array( 'name' => 'billing_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true )
                        ),
                        'button_text' => 'View My Assessment Results'
                    )
                );
                
            default:
                return array();
        }
    }
    
    /**
     * Enqueue assessment assets
     */
    public function enqueue_assessment_assets() {
        // Only enqueue on pages with assessment shortcodes
        if ( ! $this->page_has_assessment_shortcode() ) {
            return;
        }
        
        // Define plugin URL if not already defined
        $plugin_url = defined( 'ENNU_LIFE_PLUGIN_URL' ) ? ENNU_LIFE_PLUGIN_URL : plugin_dir_url( __FILE__ );
        $plugin_version = defined( 'ENNU_LIFE_VERSION' ) ? ENNU_LIFE_VERSION : '1.0.0';
        
        // Enqueue main assessment CSS with cache busting
        wp_enqueue_style(
            'ennu-assessment-frontend',
            $plugin_url . 'assets/css/ennu-frontend-forms.css',
            array(),
            $plugin_version . '.' . time(), // Force cache refresh
            'all'
        );
        
        // Enqueue main assessment JavaScript with cache busting
        wp_enqueue_script(
            'ennu-assessment-frontend',
            $plugin_url . 'assets/js/ennu-frontend-forms.js',
            array('jquery'),
            $plugin_version . '.' . time(), // Force cache refresh
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('ennu-assessment-frontend', 'ennuAssessment', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ennu_ajax_nonce'),
            'version' => $plugin_version,
            'strings' => array(
                'loading' => __( 'Loading...', 'ennu-life' ),
                'error' => __( 'An error occurred. Please try again.', 'ennu-life' ),
                'success' => __( 'Assessment submitted successfully!', 'ennu-life' ),
                'required_field' => __( 'This field is required.', 'ennu-life' ),
                'invalid_email' => __( 'Please enter a valid email address.', 'ennu-life' )
            )
        ));
    }
    
    /**
     * Check if current page has assessment shortcode
     * 
     * @return bool
     */
    private function page_has_assessment_shortcode() {
        global $post;
        
        if ( ! $post || ! $post->post_content ) {
            return false;
        }
        
        // Check for any ENNU assessment shortcode
        return has_shortcode( $post->post_content, 'ennu-hair-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-hair-restoration-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-ed-treatment-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-weight-loss-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-weight-loss-quiz' ) ||
               has_shortcode( $post->post_content, 'ennu-health-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-skin-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-advanced-skin-assessment' ) ||
               has_shortcode( $post->post_content, 'ennu-skin-assessment-enhanced' ) ||
               has_shortcode( $post->post_content, 'ennu-hormone-assessment' );
    }


// In class-assessment-shortcodes.php, find and replace this entire function:
    
    /**
     * Handle assessment submission - FINAL VERSION with corrected redirect flow
     */
    public function handle_assessment_submission() {
        // --- Step 1: Basic validation and security checks ---
        $assessment_type = isset( $_POST['assessment_type'] ) ? sanitize_key( $_POST['assessment_type'] ) : '';
        if ( empty( $assessment_type ) ) {
            wp_send_json_error( array( 'message' => 'Assessment type not specified.' ) );
            return;
        }
    
        $nonce_field = 'assessment_nonce';
        $nonce_action = 'ennu_assessment_' . $assessment_type;
        
        // Check multiple nonce types for better compatibility
        $nonce_valid = false;
        
        // Check assessment-specific nonce first
        if (isset($_POST[$nonce_field]) && wp_verify_nonce($_POST[$nonce_field], $nonce_action)) {
            $nonce_valid = true;
        }
        // Check fallback nonce types
        elseif (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'ennu_ajax_nonce')) {
            $nonce_valid = true;
        }
        elseif (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'ennu_nonce')) {
            $nonce_valid = true;
        }
        
        if (!$nonce_valid) {
            error_log('ENNU: Nonce verification failed for assessment: ' . $assessment_type);
            error_log('ENNU: Available nonces: ' . print_r(array_keys($_POST), true));
            wp_send_json_error( array( 'message' => 'Security check failed. Please refresh and try again.' ) );
            return;
        }
    
        $assessment_data = $this->sanitize_assessment_data( $_POST );
        if ( ! $this->validate_assessment_data( $assessment_data ) ) {
            wp_send_json_error( array( 'message' => 'Please fill out all required fields.' ) );
            return;
        }
    
        // --- Step 2: Handle User Registration/Login for Logged-Out Users ---
        if ( ! is_user_logged_in() ) {
            $email = $assessment_data['contact_email'];
            $existing_user = get_user_by( 'email', $email );
    
            if ( $existing_user ) {
                $login_url = wp_login_url(wp_get_referer()); // Redirect back after login
                $error_message = 'An account with this email already exists. Please <a href="' . esc_url($login_url) . '" target="_blank" rel="noopener noreferrer">log in here</a> to continue.';
                wp_send_json_error( array( 'message' => $error_message ) );
                // This 'return' is correct, it stops execution for existing users.
                return;
    
            } else {
                $first_name = sanitize_text_field( $_POST['first_name'] ?? '' );
                $last_name = sanitize_text_field( $_POST['last_name'] ?? '' );
                $password = wp_generate_password( 12, true );
    
                $user_data = array(
                    'user_login'    => $email,
                    'user_email'    => $email,
                    'user_pass'     => $password,
                    'first_name'    => $first_name,
                    'last_name'     => $last_name,
                    'display_name'  => trim( $first_name . ' ' . $last_name ),
                    'role'          => 'subscriber',
                );
                
                $user_id = wp_insert_user( $user_data );
    
                if ( is_wp_error( $user_id ) ) {
                    wp_send_json_error( array( 'message' => 'Could not create your account: ' . $user_id->get_error_message() ) );
                    return;
                }
    
                wp_set_current_user( $user_id, $email );
                wp_set_auth_cookie( $user_id );
                do_action( 'wp_login', $email, get_user_by('id', $user_id) );
                
                // *** THE CRITICAL FIX IS HERE ***
                // The previous code was missing a `return;` statement inside the `is_wp_error` block
                // and did not have this block at all. After creating the user, the script
                // would just end. Now, it correctly proceeds to Step 3.
            }
        }
    
        // --- Step 3: Save Assessment Data and Send Response ---
        // This part of the code will now run for newly created users as well.
        try {
            $this->save_user_assessment_meta( $assessment_data );
            $this->send_assessment_notification( $assessment_data );
    
            wp_send_json_success( array( 
                'message' => __( 'Success! Creating your results...', 'ennu-life' ),
                'redirect_url' => $this->get_thank_you_url( $assessment_type )
            ) );
    
        } catch ( Exception $e ) {
            error_log( 'ENNU Assessment Submission Error: ' . $e->getMessage() );
            wp_send_json_error( array( 'message' => 'An error occurred while saving your assessment.' ) );
        }
    }


    /**
     * Sanitize assessment data
     * 
     * @param array $data Raw POST data
     * @return array
     */
    private function sanitize_assessment_data( $data ) {
        $sanitized = array(
            'assessment_type' => sanitize_key( $data['assessment_type'] ?? '' ),
            'contact_name' => '',
            'contact_email' => sanitize_email( trim( $data['email'] ?? '' ) ),
            'contact_phone' => preg_replace( '/[^0-9+\-\(\)\s]/', '', $data['billing_phone'] ?? '' ),
            'answers' => array()
        );
        
        // Build contact name from first and last name
        $first_name = sanitize_text_field( trim( $data['first_name'] ?? '' ) );
        $last_name = sanitize_text_field( trim( $data['last_name'] ?? '' ) );
        $sanitized['contact_name'] = trim( $first_name . ' ' . $last_name );
        
        // Sanitize question answers - handle simple format like hair_q1, skin_q2, etc.
        foreach ( $data as $key => $value ) {
       // --- EDIT 1: Handle array keys from multiselect checkboxes ---
        $clean_key = sanitize_key( str_replace('[]', '', $key) );
        
        // --- EDIT 2: Sanitize value whether it's a string or an array ---
        $clean_value = is_array($value) ? array_map('sanitize_text_field', $value) : sanitize_text_field( $value );
        
        // Skip empty values
        if ( empty( $clean_value ) ) {
            continue;
        }
        
        // --- EDIT 3: Add 'welcome' to the regex ---
        if ( preg_match( '/^(welcome|hair|skin|ed_treatment|weight_loss|health)_q\d+/', $clean_key ) ) {
            $sanitized["answers"][ $clean_key ] = $clean_value;
        }
            // Capture contact info fields
            elseif ( in_array( $clean_key, array( 'first_name', 'last_name', 'email', 'billing_phone' ) ) ) {
                $sanitized["answers"][ $clean_key ] = $clean_value;
            }
            // Capture other assessment related fields
            elseif ( in_array( $clean_key, array( 'user_age', 'user_dob_combined' ) ) ) {
                $sanitized["answers"][ $clean_key ] = $clean_value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Validate assessment data
     * 
     * @param array $data Sanitized assessment data
     * @return bool
     */
    private function validate_assessment_data( $data ) {
        // Check required fields
        if ( empty( $data['assessment_type'] ) || 
             empty( $data['contact_name'] ) || 
             empty( $data['contact_email'] ) ) {
            return false;
        }
        
        // Validate email format
        if ( ! is_email( $data['contact_email'] ) ) {
            return false;
        }
        
        // Validate name (no special characters except spaces, hyphens, apostrophes)
        if ( ! preg_match( '/^[a-zA-Z\s\-\'\.]+$/', $data['contact_name'] ) ) {
            return false;
        }
        
        // Check if assessment type is valid
        if ( ! isset( $this->assessments[ $data['assessment_type'] ] ) ) {
            return false;
        }
        
        // Validate minimum number of answers
        if ( count( $data['answers'] ) < 2 ) {
            return false;
        }
        
        return true;
    }
    

// In class-assessment-shortcodes.php, find and replace this entire function:
    
/**
 * Save assessment data to user meta - FINALIZED VERSION
 * * @param array $data Assessment data
 */
private function save_user_assessment_meta( $data ) {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        error_log('ENNU: Cannot save assessment data - no user ID available');
        return;
    }

    error_log('ENNU: Saving assessment data for user ID: ' . $user_id);
    error_log('ENNU: Assessment data: ' . print_r($data, true));

    $timestamp = current_time( 'timestamp' );
    $assessment_type = $data['assessment_type'];
    $submission_id = $assessment_type . '_' . $timestamp;

    // --- NEW: Define keys for global fields, mapping Welcome Assessment's unique order ---
    $dob_key_map = [ 'welcome_assessment' => 'welcome_q2' ];
    $gender_key_map = [ 'welcome_assessment' => 'welcome_q1' ];

    // For other assessments, default to q1 for DOB and q2 for Gender
    $dob_key = $dob_key_map[$assessment_type] ?? str_replace('_assessment', '', $assessment_type) . '_q1';
    $gender_key = $gender_key_map[$assessment_type] ?? str_replace('_assessment', '', $assessment_type) . '_q2';

    // --- CORE LOGIC CHANGE ---
    // Save individual answers, handling global and multiselect fields
    if ( isset( $data['answers'] ) && is_array( $data['answers'] ) ) {
        foreach ( $data['answers'] as $question_key => $answer_value ) {

            // If the current question is DOB or Gender, save it to a global meta key
            if ($question_key === $dob_key) {
                update_user_meta( $user_id, 'user_dob', $answer_value );
            } elseif ($question_key === $gender_key) {
                update_user_meta( $user_id, 'user_gender', $answer_value );
            } else {
                // Otherwise, save it as an assessment-specific meta key
                $meta_key = 'ennu_' . $assessment_type . '_' . $question_key;
                
                // NEW: Convert array from multi-select into a comma-separated string for saving
                $value_to_save = is_array($answer_value) ? implode(', ', $answer_value) : $answer_value;
                
                update_user_meta( $user_id, $meta_key, $value_to_save );
            }
        }
    }

    // Save general assessment metadata
    update_user_meta( $user_id, 'ennu_last_type', $assessment_type );
    update_user_meta( $user_id, 'ennu_last_submission_id', $submission_id );
    update_user_meta( $user_id, 'ennu_submission_' . $submission_id, $data );
}
    
    
    /**
     * Send assessment notification email
     * 
     * @param array $data Assessment data
     */
    private function send_assessment_notification( $data ) {
        $to = $data['contact_email'];
        $subject = sprintf( 
            __( 'Your %s Results - ENNU Life', 'ennu-life' ),
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
            __( 'New %s Submission', 'ennu-life' ),
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
    
// In class-assessment-shortcodes.php, find and replace this entire function:
    
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
        return isset( $thank_you_pages[$assessment_type] ) ? $thank_you_pages[$assessment_type] : $thank_you_pages['general'];
    }
    
    /**
     * Render thank you page shortcode
     * 
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @param string $tag Shortcode tag
     * @return string
     */
    public function render_thank_you_page( $atts, $content = '', $tag = '' ) {
        // Extract assessment type from shortcode tag
        $assessment_type_map = array(
            'ennu-welcome-results' => 'welcome_assessment',
            'ennu-hair-results' => 'hair_assessment',
            'ennu-ed-results' => 'ed_treatment_assessment',
            'ennu-weight-loss-results' => 'weight_loss_assessment',
            'ennu-health-results' => 'health_assessment',
            'ennu-skin-results' => 'skin_assessment'
        );
        
        $assessment_type = isset($assessment_type_map[$tag]) ? $assessment_type_map[$tag] : 'general';
        
        // Define assessment-specific content
        $content_data = $this->get_thank_you_content($assessment_type);
        
        // Start output buffering
        ob_start();
        ?>
        
        <div class="ennu-results-container">
            <div class="ennu-results-content <?php echo esc_attr($assessment_type); ?>-theme">
                <div class="success-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="<?php echo esc_attr($content_data['color']); ?>" stroke-width="2" fill="<?php echo esc_attr($content_data['bg_color']); ?>"/>
                        <path d="m9 12 2 2 4-4" stroke="<?php echo esc_attr($content_data['color']); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                
                <div class="thank-you-section">
                    <h1><?php echo esc_html($content_data['title']); ?></h1>
                    <p class="thank-you-message">
                        <?php echo esc_html($content_data['message']); ?>
                    </p>
                    
                    <div class="next-steps">
                        <h2>What's Next?</h2>
                        <p>
                            <?php echo esc_html($content_data['next_steps']); ?>
                        </p>
                        
                        <div class="consultation-cta">
                            <a href="<?php echo esc_url($content_data['consultation_url']); ?>" class="schedule-consultation-btn">
                                <span class="btn-icon"><?php echo $content_data['icon']; ?></span>
                                <?php echo esc_html($content_data['button_text']); ?>
                            </a>
                        </div>
                        
                        <div class="additional-info">
                            <h3>What to expect in your consultation:</h3>
                            <ul>
                                <?php foreach ($content_data['benefits'] as $benefit): ?>
                                    <li><?php echo esc_html($benefit); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <?php if (isset($content_data['extra_section'])): ?>
                            <div class="extra-section">
                                <?php echo $content_data['extra_section']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="contact-info">
                            <p><strong><?php echo esc_html($content_data['contact_label']); ?></strong> Call us at <a href="tel:<?php echo esc_attr($content_data['phone']); ?>"><?php echo esc_html($content_data['phone_display']); ?></a> or email <a href="mailto:<?php echo esc_attr($content_data['email']); ?>"><?php echo esc_html($content_data['email']); ?></a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .ennu-results-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            font-family: Arial, sans-serif;
        }

        .ennu-results-content {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            text-align: center;
            border-top: 5px solid <?php echo esc_attr($content_data['color']); ?>;
        }

        .success-icon {
            margin-bottom: 30px;
            animation: fadeInScale 0.6s ease-out;
        }

        @keyframes fadeInScale {
            0% { opacity: 0; transform: scale(0.5); }
            100% { opacity: 1; transform: scale(1); }
        }

        .thank-you-section h1 {
            color: <?php echo esc_attr($content_data['color']); ?>;
            font-size: 2.5em;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .thank-you-message {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .next-steps {
            margin-top: 40px;
        }

        .next-steps h2 {
            color: <?php echo esc_attr($content_data['color']); ?>;
            font-size: 1.8em;
            margin-bottom: 20px;
        }

        .consultation-cta {
            margin: 30px 0;
        }

        .schedule-consultation-btn {
            display: inline-block;
            background: <?php echo esc_attr($content_data['gradient']); ?>;
            color: white;
            padding: 18px 35px;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px <?php echo esc_attr($content_data['shadow']); ?>;
        }

        .schedule-consultation-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px <?php echo esc_attr($content_data['shadow']); ?>;
            color: white;
            text-decoration: none;
        }

        .btn-icon {
            margin-right: 10px;
            font-size: 1.2em;
        }

        .additional-info {
            margin-top: 40px;
            text-align: left;
            background: <?php echo esc_attr($content_data['info_bg']); ?>;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid <?php echo esc_attr($content_data['color']); ?>;
        }

        .additional-info h3 {
            color: <?php echo esc_attr($content_data['color']); ?>;
            margin-bottom: 15px;
        }

        .additional-info ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .additional-info li {
            margin: 12px 0;
            color: #555;
            line-height: 1.5;
        }

        .contact-info {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.95em;
        }

        .contact-info a {
            color: <?php echo esc_attr($content_data['color']); ?>;
            text-decoration: none;
            font-weight: bold;
        }

        .contact-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .ennu-results-content {
                padding: 25px;
            }
            
            .thank-you-section h1 {
                font-size: 2em;
            }
            
            .schedule-consultation-btn {
                padding: 15px 28px;
                font-size: 1em;
            }
        }
        </style>
        
        <?php
        return ob_get_clean();
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
                'button_text' => 'Schedule Your Hair Consultation',
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
                'button_text' => 'Schedule Your Confidential Consultation',
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
                'button_text' => 'Schedule Your Weight Loss Consultation',
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
                'button_text' => 'Schedule Your Health Consultation',
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
                'button_text' => 'Schedule Your Skin Consultation',
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
     * Render assessment results page
     * 
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_results_page( $atts = array() ) {
        // Check if results data exists (implementation depends on your storage method)
        return '<div class="ennu-results">Results will be displayed here</div>';
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
            esc_html__( 'Assessment Unavailable', 'ennu-life' ),
            esc_html( $message )
        );
    }
}

// Initialize the class
new ENNU_Assessment_Shortcodes();