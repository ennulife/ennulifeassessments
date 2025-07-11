<?php
/**
 * ENNU Life Admin Class - Final Version with Global Fields
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Admin {
    
    public function __construct() {
        add_action('show_user_profile', array($this, 'show_user_assessment_fields'));
        add_action('edit_user_profile', array($this, 'show_user_assessment_fields'));
        add_action('personal_options_update', array($this, 'save_user_assessment_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_assessment_fields'));
    }

    /**
     * Safely get the question structure for an assessment.
     */
    private function get_assessment_structure($assessment_type) {
        if (!class_exists('ENNU_Assessment_Shortcodes')) {
            require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-shortcodes.php';
        }
        $shortcodes_instance = new ENNU_Assessment_Shortcodes();
        $reflection = new ReflectionMethod('ENNU_Assessment_Shortcodes', 'get_assessment_questions');
        $reflection->setAccessible(true);
        return $reflection->invoke($shortcodes_instance, $assessment_type);
    }
    
    /**
     * Main function to display all custom ENNU Life sections on the user profile.
     */
    public function show_user_assessment_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) return;
        wp_nonce_field('ennu_user_profile_update', 'ennu_assessment_nonce');
        
        echo '<h2>ENNU Life Global Information</h2>';
        echo '<p><em>This information is shared across all assessments.</em></p>';
        echo '<table class="form-table">';
        $this->display_global_fields($user->ID);
        echo '</table>';

        echo '<h2>Assessment-Specific Answers</h2>';
        $assessment_types = [
            'welcome_assessment' => 'Welcome Assessment',
            'hair_assessment' => 'Hair Assessment',
            'skin_assessment' => 'Skin Assessment',
            'health_assessment' => 'Health Assessment',
            'weight_loss_assessment' => 'Weight Loss Assessment',
            'ed_treatment_assessment' => 'ED Treatment Assessment'
        ];

        foreach ($assessment_types as $type => $label) {
            $this->display_single_assessment_section($user->ID, $type, $label);
        }
    }
    
    /**
     * Renders the editable fields for global user data (DOB and Gender).
     */
    private function display_global_fields($user_id){
        $this->render_text_field('user_dob', 'Date of Birth (YYYY-MM-DD)', get_user_meta($user_id, 'user_dob', true));
        
        $gender_options = [
            ['value' => 'male', 'label' => 'Male'],
            ['value' => 'female', 'label' => 'Female'],
        ];
        $this->render_radio_field('user_gender', 'Gender', get_user_meta($user_id, 'user_gender', true), $gender_options);
    }

    /**
     * Displays the specific, non-global questions for a single assessment type.
     */
    private function display_single_assessment_section($user_id, $assessment_type, $assessment_label) {
        $questions = $this->get_assessment_structure($assessment_type);
        if (empty($questions)) return;

        $has_specific_answers = false;
        ob_start();

        echo '<table class="form-table">';
        foreach ($questions as $index => $question) {
            // --- KEY CHANGE: Skip global fields (q1=DOB, q2=Gender) and the contact info block ---
            if ($index < 2 || (isset($question['type']) && $question['type'] === 'contact_info')) {
                continue;
            }
            
            $has_specific_answers = true;
            $assessment_prefix = str_replace('_assessment', '', $assessment_type);
            $simple_question_id = $assessment_prefix . '_q' . ($index + 1);
            $meta_key = 'ennu_' . $assessment_type . '_' . $simple_question_id;
            $current_value = get_user_meta($user_id, $meta_key, true);

// --- THIS IS THE DISPLAY FIX ---
            // Check for the multiselect type first.
            if (isset($question['type']) && $question['type'] === 'multiselect') {
                $this->render_checkbox_field($meta_key, $question['title'], $current_value, $question['options']);
            } 
            // Handle single-choice radio buttons.
            elseif (!empty($question['options']) && is_array($question['options'])) {
                $this->render_radio_field($meta_key, $question['title'], $current_value, $question['options']);
            } 
            // Fallback for any other question type.
            else {
                $this->render_text_field($meta_key, $question['title'], $current_value);
            }
        }
        echo '</table>';
        
        $output = ob_get_clean();

        // Only show the heading for assessments that have specific answers.
        if ($has_specific_answers) {
            echo '<h3 style="margin-top: 2em; margin-bottom: 1em;">' . esc_html($assessment_label) . '</h3>';
            echo $output;
        }
    }

    private function render_text_field($meta_key, $label, $current_value) {
        echo '<tr><th><label for="' . esc_attr($meta_key) . '">' . esc_html($label) . '</label></th>';
        echo '<td><input type="text" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($current_value) . '" class="regular-text" /></td></tr>';
    }

    private function render_radio_field($meta_key, $label, $current_value, $options) {
        echo '<tr><th>' . esc_html($label) . '</th><td><fieldset><legend class="screen-reader-text"><span>' . esc_html($label) . '</span></legend>';
        foreach ($options as $option) {
            echo '<label style="display: block; margin-bottom: 5px;"><input type="radio" name="' . esc_attr($meta_key) . '" value="' . esc_attr($option['value']) . '" ' . checked($current_value, $option['value'], false) . ' /> ' . esc_html($option['label']) . '</label>';
        }
        echo '</fieldset></td></tr>';
    }
    
    private function render_checkbox_field($meta_key, $label, $current_values, $options) {
        // The saved value is a comma-separated string, so we convert it to an array for easy checking.
        $saved_options = array_map('trim', explode(',', $current_values));
    
        echo '<tr><th>' . esc_html($label) . '</th><td><fieldset><legend class="screen-reader-text"><span>' . esc_html($label) . '</span></legend>';
    
        foreach ($options as $option) {
            $is_checked = in_array($option['value'], $saved_options);
            echo '<label style="display: block; margin-bottom: 5px;">
                      <input type="checkbox" name="' . esc_attr($meta_key) . '[]" value="' . esc_attr($option['value']) . '" ' . checked($is_checked, true, false) . ' /> ' 
                      . esc_html($option['label']) . 
                 '</label>';
        }
    
        echo '</fieldset></td></tr>';
    }

    /**
     * Saves all custom fields from the user profile page.
     */
    public function save_user_assessment_fields($user_id) {
        if (!isset($_POST['ennu_assessment_nonce']) || !wp_verify_nonce($_POST['ennu_assessment_nonce'], 'ennu_user_profile_update')) return;
        if (!current_user_can('edit_user', $user_id)) return;

        // --- KEY CHANGE: Save the new global fields first ---
        if (isset($_POST['user_dob'])) {
            update_user_meta($user_id, 'user_dob', sanitize_text_field($_POST['user_dob']));
        }
        if (isset($_POST['user_gender'])) {
            update_user_meta($user_id, 'user_gender', sanitize_text_field($_POST['user_gender']));
        }
        
        // Now, loop through and save only the assessment-specific fields
        $assessment_types = ['welcome_assessment', 'hair_assessment', 'skin_assessment', 'health_assessment', 'weight_loss_assessment', 'ed_treatment_assessment'];
        foreach ($assessment_types as $type) {
            $questions = $this->get_assessment_structure($type);
            if (empty($questions)) continue;

            foreach ($questions as $index => $question) {
                // Skip saving global fields from this loop
                if ($index < 2 || (isset($question['type']) && $question['type'] === 'contact_info')) {
                    continue;
                }
                
                $assessment_prefix = str_replace('_assessment', '', $type);
                $simple_question_id = $assessment_prefix . '_q' . ($index + 1);
                $meta_key = 'ennu_' . $type . '_' . $simple_question_id;

                if (isset($_POST[$meta_key])) {
                    // Check if the submitted data is an array (from our new checkboxes).
                    if (is_array($_POST[$meta_key])) {
                        // Sanitize each selected value in the array.
                        $sanitized_values = array_map('sanitize_text_field', $_POST[$meta_key]);
                        // Join the array into a single, comma-separated string to save in the database.
                        $value_to_save = implode(', ', $sanitized_values);
                    } else {
                        // If it's not an array, just sanitize the single value as before.
                        $value_to_save = sanitize_text_field($_POST[$meta_key]);
                    }
                    // Update the user meta with the correctly formatted value.
                    update_user_meta($user_id, $meta_key, $value_to_save);
                }
            }
        }
    }
}