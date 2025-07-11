<?php
/**
 * ENNU Life Form Handler - Minimal Working Version with User Meta Saving
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Life_Form_Handler {
    
    public function __construct() {
        // Log that the form handler is being constructed
        error_log('ENNU: Form Handler constructor called - registering AJAX actions');
        
        // Standard WordPress AJAX handlers
        add_action('wp_ajax_ennu_form_submit', array($this, 'handle_ajax_submission'));
        add_action('wp_ajax_nopriv_ennu_form_submit', array($this, 'handle_ajax_submission'));
        
        // Add a simple test handler to verify AJAX is working
        add_action('wp_ajax_ennu_test', array($this, 'test_ajax_handler'));
        add_action('wp_ajax_nopriv_ennu_test', array($this, 'test_ajax_handler'));
        
        // Add custom endpoint to bypass WordPress AJAX completely
        add_action('init', array($this, 'add_custom_endpoint'));
        add_action('template_redirect', array($this, 'handle_custom_endpoint'));
        
        error_log('ENNU: AJAX actions and custom endpoint registered successfully');
    }
    
    /**
     * Add custom endpoint for form submission
     */
    public function add_custom_endpoint() {
        add_rewrite_rule('^ennu-submit/?$', 'index.php?ennu_submit=1', 'top');
        add_rewrite_tag('%ennu_submit%', '([^&]+)');
    }
    
    /**
     * Handle custom endpoint requests
     */
    public function handle_custom_endpoint() {
        if (get_query_var('ennu_submit')) {
            error_log('ENNU: Custom endpoint called - bypassing WordPress AJAX');
            
            // Set JSON headers
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Process the form submission using the same logic
                $this->handle_ajax_submission();
            } else {
                wp_send_json_error(array('message' => 'Only POST requests allowed'));
            }
            
            exit;
        }
    }
    
    /**
     * Simple test AJAX handler
     */
    public function test_ajax_handler() {
        error_log('ENNU: Test AJAX handler called successfully');
        wp_send_json_success(array('message' => 'AJAX is working!'));
    }
    
    /**
     * Handle AJAX form submission
     */
    public function handle_ajax_submission() {
        error_log('ENNU v22.5: Enhanced form submission received');
        
        try {
            // Enhanced nonce validation - MUST be strict
            if (!isset($_POST["nonce"]) || !wp_verify_nonce($_POST["nonce"], "ennu_ajax_nonce")) {
                error_log("ENNU ERROR: Nonce verification failed or missing. Possible CSRF attack.");
                wp_send_json_error(array("message" => "Security check failed. Please refresh the page and try again."));
                return;
            }
            error_log("ENNU: Nonce validation successful");            
            // Get form data
            $form_data = $_POST;
            unset($form_data['action'], $form_data['nonce']);
            
            if (empty($form_data)) {
                error_log('ENNU ERROR: No form data received');
                wp_send_json_error(array('message' => 'No data received'));
                return;
            }
            
            error_log('ENNU: Form data received (' . count($form_data) . ' fields): ' . print_r(array_keys($form_data), true));
            
            // Enhanced validation for required fields
            $required_fields = array('first_name', 'last_name', 'email');
            $missing_fields = array();
            
            foreach ($required_fields as $field) {
                if (empty($form_data[$field]) || trim($form_data[$field]) === '') {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                error_log('ENNU ERROR: Missing required fields: ' . implode(', ', $missing_fields));
                wp_send_json_error(array(
                    'message' => 'Missing required fields: ' . implode(', ', $missing_fields),
                    'missing_fields' => $missing_fields
                ));
                return;
            }
            
            // Validate email format
            if (!is_email($form_data['email'])) {
                error_log('ENNU ERROR: Invalid email format: ' . $form_data['email']);
                wp_send_json_error(array('message' => 'Invalid email address format'));
                return;
            }
            
            // Detect assessment type
            $assessment_type = $this->detect_assessment_type($form_data);
            error_log('ENNU: Detected assessment type: ' . $assessment_type);
            
            if (empty($assessment_type)) {
                error_log('ENNU ERROR: Could not detect assessment type');
                wp_send_json_error(array('message' => 'Could not determine assessment type'));
                return;
            }
            
            // Get or create user
            $user_id = $this->get_or_create_user($form_data);
            error_log('ENNU: User ID result: ' . ($user_id ? $user_id : 'FAILED'));
            
            if (!$user_id) {
                error_log('ENNU ERROR: Failed to get or create user');
                wp_send_json_error(array('message' => 'Failed to create user account'));
                return;
            }
            
            // Save data with assessment-specific prefix
            $saved_fields = 0;
            $failed_fields = array();
            
            foreach ($form_data as $key => $value) {
                // Skip empty values and system fields
                if (empty($value) || in_array($key, array('action', 'nonce', 'assessment_type'))) {
                    continue;
                }
                
                // Handle array values (multiple selections)
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                
                // Create standardized meta key with 'ennu_' prefix
                $clean_key = sanitize_key($key);
                $clean_assessment_type = str_replace('-', '_', $assessment_type);
                $meta_key = 'ennu_' . $clean_assessment_type . '_' . $clean_key;
                $sanitized_value = sanitize_text_field($value);
                
                // Save the field value
                $result = update_user_meta($user_id, $meta_key, $sanitized_value);
                
                if ($result !== false) {
                    $saved_fields++;
                    error_log('ENNU: Saved field: ' . $meta_key . ' = ' . $sanitized_value);
                    
                    // Also save metadata for admin display
                    update_user_meta($user_id, $meta_key . '_date', current_time('mysql'));
                    $field_label = ucwords(str_replace('_', ' ', $clean_key));
                    update_user_meta($user_id, $meta_key . '_label', $field_label);
                } else {
                    $failed_fields[] = $meta_key;
                    error_log('ENNU WARNING: Failed to save field: ' . $meta_key . ' = ' . $sanitized_value);
                }
            }
            
            // Save completion status and metadata
            $completion_meta_key = 'ennu_' . str_replace('-', '_', $assessment_type) . '_completion_status';
            update_user_meta($user_id, $completion_meta_key, 'completed');
            update_user_meta($user_id, $completion_meta_key . '_date', current_time('mysql'));
            update_user_meta($user_id, 'ennu_' . str_replace('-', '_', $assessment_type) . '_submission_date', current_time('mysql'));
            update_user_meta($user_id, 'ennu_' . str_replace('-', '_', $assessment_type) . '_version', ENNU_LIFE_VERSION);
            
            error_log('ENNU: Successfully saved ' . $saved_fields . ' fields for user ' . $user_id . ' (assessment: ' . $assessment_type . ')');
            
            if (!empty($failed_fields)) {
                error_log('ENNU WARNING: Failed to save ' . count($failed_fields) . ' fields: ' . implode(', ', $failed_fields));
            }
            
            // Verify data was saved by checking what's in the database
            $this->verify_user_meta_save($user_id, $assessment_type);
            
            // Get redirect URL
            $redirect_url = $this->get_assessment_results_url($assessment_type);
            error_log('ENNU: Redirect URL: ' . $redirect_url);
            
            // Send success response
            $response_data = array(
                'message' => 'Assessment submitted successfully!',
                'redirect_url' => $redirect_url,
                'assessment_type' => $assessment_type,
                'user_id' => $user_id,
                'fields_saved' => $saved_fields
            );
            
            error_log('ENNU: Sending success response: ' . print_r($response_data, true));
            wp_send_json_success($response_data);
            
        } catch (Exception $e) {
            error_log('ENNU: Error in form submission: ' . $e->getMessage());
            wp_send_json_error(array('message' => 'Submission failed: ' . $e->getMessage()));
        } catch (Error $e) {
            error_log('ENNU: FATAL ERROR in form handler: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => 'System error during submission: ' . $e->getMessage(),
                'error_type' => 'fatal_error'
            ));
        }
    }
    
    /**
     * Get existing user or create new user based on email
     */
    private function get_or_create_user($form_data) {
        // Check if user is already logged in
        $user_id = get_current_user_id();
        if ($user_id) {
            error_log('ENNU: User is logged in with ID: ' . $user_id);
            
            // Update user profile with any new information from the form
            $this->update_logged_in_user_profile($user_id, $form_data);
            
            return $user_id;
        }
        
        // Look for email in form data
        $email = '';
        if (isset($form_data['email'])) {
            $email = sanitize_email($form_data['email']);
        }
        
        if (empty($email)) {
            // No email provided, can't create user
            error_log('ENNU: No email provided for user creation');
            return false;
        }
        
        // Check if user already exists
        $existing_user = get_user_by('email', $email);
        if ($existing_user) {
            error_log('ENNU: Found existing user with email: ' . $email . ' (ID: ' . $existing_user->ID . ')');
            return $existing_user->ID;
        }
        
        // Create new user
        $first_name = isset($form_data['first_name']) ? sanitize_text_field($form_data['first_name']) : '';
        $last_name = isset($form_data['last_name']) ? sanitize_text_field($form_data['last_name']) : '';
        $username = $email; // Use email as username
        $auto_password = wp_generate_password(12, false); // Generate 12-character password without special chars
        
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => trim($first_name . ' ' . $last_name),
            'user_pass' => $auto_password,
            'role' => 'subscriber'
        );
        
        error_log('ENNU: Creating new user account for: ' . $email . ' with auto-generated password');
        
        $new_user_id = wp_insert_user($user_data);
        
        if (is_wp_error($new_user_id)) {
            error_log('ENNU: Failed to create user - ' . $new_user_id->get_error_message());
            return false;
        }
        
        // Send welcome email with login credentials
        $this->send_welcome_email($new_user_id, $email, $auto_password, $first_name);
        
        error_log('ENNU: Created new user with ID: ' . $new_user_id . ' for email: ' . $email . ' - Welcome email sent');
        return $new_user_id;
    }
    
    /**
     * Update logged-in user's profile with form data
     */
    private function update_logged_in_user_profile($user_id, $form_data) {
        $current_user = get_userdata($user_id);
        $updates_made = array();
        
        // Check and update first name
        if (isset($form_data['first_name']) && !empty($form_data['first_name'])) {
            $new_first_name = sanitize_text_field($form_data['first_name']);
            if ($current_user->first_name !== $new_first_name) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $new_first_name
                ));
                $updates_made[] = 'first_name: ' . $new_first_name;
            }
        }
        
        // Check and update last name
        if (isset($form_data['last_name']) && !empty($form_data['last_name'])) {
            $new_last_name = sanitize_text_field($form_data['last_name']);
            if ($current_user->last_name !== $new_last_name) {
                wp_update_user(array(
                    'ID' => $user_id,
                    'last_name' => $new_last_name
                ));
                $updates_made[] = 'last_name: ' . $new_last_name;
            }
        }
        
        // Check and update email (be careful with this)
        if (isset($form_data['email']) && !empty($form_data['email'])) {
            $new_email = sanitize_email($form_data['email']);
            if ($current_user->user_email !== $new_email) {
                // Only update if the new email is not already taken by another user
                $existing_user = get_user_by('email', $new_email);
                if (!$existing_user || $existing_user->ID === $user_id) {
                    wp_update_user(array(
                        'ID' => $user_id,
                        'user_email' => $new_email
                    ));
                    $updates_made[] = 'email: ' . $new_email;
                } else {
                    error_log('ENNU: Cannot update email to ' . $new_email . ' - already taken by user ID: ' . $existing_user->ID);
                }
            }
        }
        
        // Check and update billing phone
        if (isset($form_data['billing_phone']) && !empty($form_data['billing_phone'])) {
            $new_phone = sanitize_text_field($form_data['billing_phone']);
            $current_phone = get_user_meta($user_id, 'billing_phone', true);
            if ($current_phone !== $new_phone) {
                update_user_meta($user_id, 'billing_phone', $new_phone);
                $updates_made[] = 'billing_phone: ' . $new_phone;
            }
        }
        
        if (!empty($updates_made)) {
            error_log('ENNU: Updated logged-in user profile (ID: ' . $user_id . '): ' . implode(', ', $updates_made));
        } else {
            error_log('ENNU: No profile updates needed for logged-in user (ID: ' . $user_id . ')');
        }
    }
    
    /**
     * Send welcome email to newly created user
     */
    private function send_welcome_email($user_id, $email, $password, $first_name) {
        $site_name = get_bloginfo('name');
        $login_url = wp_login_url();
        
        $subject = 'Welcome to ' . $site_name . ' - Your Account Details';
        
        $message = "
Hello " . $first_name . ",

Thank you for completing your health assessment! We've created a secure account for you to access your results and track your progress.

Your Account Details:
• Email: " . $email . "
• Password: " . $password . "
• Login URL: " . $login_url . "

What's Next:
1. Log in to your account using the credentials above
2. Review your personalized assessment results
3. Schedule a consultation with our healthcare professionals
4. Access your secure health dashboard anytime

For your security, we recommend changing your password after your first login.

If you have any questions, please don't hesitate to contact our support team.

Best regards,
The " . $site_name . " Team

---
This is an automated message. Please do not reply to this email.
        ";
        
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <noreply@' . parse_url(home_url(), PHP_URL_HOST) . '>'
        );
        
        $email_sent = wp_mail($email, $subject, $message, $headers);
        
        if ($email_sent) {
            error_log('ENNU: Welcome email sent successfully to: ' . $email);
            
            // Store that welcome email was sent
            update_user_meta($user_id, 'ennu_welcome_email_sent', current_time('mysql'));
        } else {
            error_log('ENNU: Failed to send welcome email to: ' . $email);
        }
        
        return $email_sent;
    }
    
    /**
     * Debug function to verify user meta is being saved
     */
    private function verify_user_meta_save($user_id, $assessment_type) {
        error_log('ENNU: Verifying user meta for user ' . $user_id . ', assessment: ' . $assessment_type);
        
        $all_meta = get_user_meta($user_id);
        $assessment_meta = array();
        $clean_assessment_type = str_replace('-', '_', $assessment_type);
        
        foreach ($all_meta as $key => $value) {
            if (strpos($key, 'ennu_' . $clean_assessment_type) === 0) {
                $assessment_meta[$key] = $value;
            }
        }
        
        error_log('ENNU: Found ' . count($assessment_meta) . ' assessment meta fields for ' . $assessment_type . ':');
        foreach ($assessment_meta as $key => $value) {
            $val = is_array($value) ? $value[0] : $value;
            error_log('ENNU: ' . $key . ' = ' . $val);
        }
        
        // Also check if user exists and has basic info
        $user = get_userdata($user_id);
        if ($user) {
            error_log('ENNU: User verified - ID: ' . $user_id . ', Email: ' . $user->user_email . ', Name: ' . $user->first_name . ' ' . $user->last_name);
        } else {
            error_log('ENNU ERROR: User ID ' . $user_id . ' not found in database!');
        }
        
        return $assessment_meta;
    }
    
    /**
     * Get assessment-specific results URL
     */
    private function get_assessment_results_url($assessment_type) {
        $urls = array(
            'welcome_assessment' => home_url('/welcome/'),
            'hair_assessment' => home_url('/hair-assessment-results/'),
            'ed_treatment_assessment' => home_url('/ed-treatment-results/'),
            'weight_loss_assessment' => home_url('/weight-loss-results/'),
            'health_assessment' => home_url('/health-assessment-results/'),
            'skin_assessment' => home_url('/skin-assessment-results/'),
            'general_assessment' => home_url('/welcome/')
        );
        
        return isset($urls[$assessment_type]) ? $urls[$assessment_type] : $urls['welcome_assessment'];
    }
    
    /**
     * Detect assessment type from form data
     */
    private function detect_assessment_type($form_data) {
        // First, look for explicit assessment type indicator
        if (isset($form_data['assessment_type'])) {
            return sanitize_text_field($form_data['assessment_type']);
        }
        
        // Log form data keys for debugging
        error_log('ENNU: Detecting assessment type from keys: ' . implode(', ', array_keys($form_data)));
        
        // Check for specific question patterns in form field names
        $all_keys = implode(' ', array_keys($form_data));
        $all_keys_lower = strtolower($all_keys);
        
        // Welcome Assessment Detection
        if (strpos($all_keys_lower, 'live_longer') !== false || 
            strpos($all_keys_lower, 'boost_energy') !== false ||
            isset($form_data['question_welcome_1']) ||
            isset($form_data['question_welcome_assessment_1'])) {
            error_log('ENNU: Detected Welcome assessment');
            return 'welcome_assessment';
        }
        
        // Hair Assessment Detection
        if (strpos($all_keys_lower, 'hair') !== false || 
            strpos($all_keys_lower, 'scalp') !== false ||
            strpos($all_keys_lower, 'balding') !== false ||
            strpos($all_keys_lower, 'thinning') !== false ||
            isset($form_data['question_hair_1']) ||
            isset($form_data['question_hair_assessment_1'])) {
            error_log('ENNU: Detected HAIR assessment');
            return 'hair_assessment';
        }
        
        
        // ED Treatment Assessment Detection
        if (strpos($all_keys_lower, 'ed_treatment') !== false ||
            strpos($all_keys_lower, 'erectile') !== false ||
            strpos($all_keys_lower, 'performance') !== false ||
            isset($form_data['question_ed_1']) ||
            isset($form_data['question_ed_treatment_1']) ||
            isset($form_data['question_ed_treatment_assessment_1'])) {
            error_log('ENNU: Detected ED TREATMENT assessment');
            return 'ed_treatment_assessment';
        }
        
        // Weight Loss Assessment Detection
        if (strpos($all_keys_lower, 'weight') !== false ||
            strpos($all_keys_lower, 'diet') !== false ||
            strpos($all_keys_lower, 'exercise') !== false ||
            strpos($all_keys_lower, 'bmi') !== false ||
            isset($form_data['question_weight_1']) ||
            isset($form_data['question_weight_loss_1']) ||
            isset($form_data['question_weight_loss_assessment_1'])) {
            error_log('ENNU: Detected WEIGHT LOSS assessment');
            return 'weight_loss_assessment';
        }
        
        // Skin Assessment Detection
        if (strpos($all_keys_lower, 'skin') !== false ||
            strpos($all_keys_lower, 'acne') !== false ||
            strpos($all_keys_lower, 'wrinkle') !== false ||
            strpos($all_keys_lower, 'skincare') !== false ||
            isset($form_data['question_skin_1']) ||
            isset($form_data['question_skin_assessment_1'])) {
            error_log('ENNU: Detected SKIN assessment');
            return 'skin_assessment';
        }
        
        // Health Assessment Detection (check this last as it's most generic)
        if (strpos($all_keys_lower, 'health') !== false ||
            strpos($all_keys_lower, 'wellness') !== false ||
            strpos($all_keys_lower, 'medical') !== false ||
            isset($form_data['question_health_1']) ||
            isset($form_data['question_health_assessment_1'])) {
            error_log('ENNU: Detected HEALTH assessment');
            return 'health_assessment';
        }
        
        // Fallback: try to detect from URL referrer if available
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referrer = strtolower($_SERVER['HTTP_REFERER']);
            if (strpos($referrer, 'registration') !== false) return 'welcome_assessment';
            if (strpos($referrer, 'hair') !== false) return 'hair_assessment';
            if (strpos($referrer, 'ed') !== false) return 'ed_treatment_assessment';
            if (strpos($referrer, 'weight') !== false) return 'weight_loss_assessment';
            if (strpos($referrer, 'skin') !== false) return 'skin_assessment';
            if (strpos($referrer, 'health') !== false) return 'health_assessment';
        }
        
        error_log('ENNU: Could not detect assessment type, defaulting to general_assessment');
        return 'general_assessment';
    }
}

// Initialize the form handler
new ENNU_Life_Form_Handler();

