<?php
/**
 * ENNU Life Enhanced Database Management Class
 * Handles individual field storage in both user meta and post meta
 * 
 * @package ENNU_Life
 * @version 14.1.11
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Life_Database {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Enhanced save assessment with individual field storage
     */
    public function save_assessment($assessment_type, $form_data, $scores = null, $user_id = null) {
        try {
            // Get user ID (use parameter if provided, otherwise current user)
            if ($user_id === null) {
                $user_id = get_current_user_id();
            }
            
            if (!$user_id) {
                throw new Exception('User ID not found. Cannot save assessment.');
            }

            // Sanitize assessment type
            $assessment_type = sanitize_text_field($assessment_type);
                // Extract contact fields from form_data for separate handling
            $contact_fields = [];
            $assessment_data_only = $form_data;

            // Define common contact field keys (adjust as per your form's actual field names)
            $common_contact_keys = ['name', 'email', 'mobile', 'full_name', 'phone'];

            foreach ($common_contact_keys as $key) {
                if (isset($assessment_data_only[$key])) {
                    $contact_fields[$key] = $assessment_data_only[$key];
                    unset($assessment_data_only[$key]); // Remove from assessment data
                }
            }

            // Update standard WordPress user fields with contact data
            if (!empty($contact_fields)) {
                $user_update_args = ["ID" => $user_id];
                if (isset($contact_fields["name"])) {
                    $name_parts = explode(" ", $contact_fields["name"], 2);
                    $user_update_args["first_name"] = $name_parts[0];
                    if (isset($name_parts[1])) {
                        $user_update_args["last_name"] = $name_parts[1];
                    }
                } else if (isset($contact_fields["full_name"])) {
                    $name_parts = explode(" ", $contact_fields["full_name"], 2);
                    $user_update_args["first_name"] = $name_parts[0];
                    if (isset($name_parts[1])) {
                        $user_update_args["last_name"] = $name_parts[1];
                    }
                }
                if (isset($contact_fields["email"])) {
                    $user_update_args["user_email"] = $contact_fields["email"];
                }
                // Add other contact fields to user meta if they don't map to standard WP fields
                foreach ($contact_fields as $key => $value) {
                    if (!in_array($key, ["name", "email", "mobile", "full_name", "phone"])) { // Already handled or not standard WP fields
                        update_user_meta($user_id, "ennu_contact_" . $key, $value);
                    }
                }
                wp_update_user($user_update_args);
            }

            // Save each individual assessment answer as separate user meta fields (excluding contact fields)
            $this->save_individual_fields(null, $user_id, $assessment_type, $assessment_data_only);
            
            // Process assessment results (still useful for calculating scores/recommendations)
            $results = $this->process_assessment_results($assessment_type, $assessment_data_only);

        }
        catch ( Exception $e ) {
            error_log( 'ENNU Database Error: ' . $e->getMessage() );
            return false;
        }
        return true;
    }

    /**
     * Saves individual assessment fields to user meta.
     * @param int|null $post_id Not used in this context, kept for compatibility.
     * @param int $user_id The ID of the user.
     * @param string $assessment_type The type of assessment (e.g., 'hair', 'skin').
     * @param array $form_data The assessment form data.
     */
    private function save_individual_fields($post_id, $user_id, $assessment_type, $form_data) {
        if (!$user_id) {
            return;
        }

        foreach ($form_data as $key => $value) {
            // Handle special cases for DOB and age fields
            if (strpos($key, ":") !== false) {
                list($field_name, $field_type) = explode(":", $key);
                if ($field_type === "dob_combined") {
                    update_user_meta($user_id, "user_dob_combined", sanitize_text_field($value));
                    continue;
                } else if ($field_type === "age") {
                    update_user_meta($user_id, "user_age", sanitize_text_field($value));
                    continue;
                }
            }

            // Determine the meta key based on the new naming convention
            $meta_key = sanitize_key($assessment_type . "_" . $key);
            update_user_meta($user_id, $meta_key, sanitize_text_field($value));
        }
    }

    /**
     * Processes assessment results (placeholder for future logic).
     * @param string $assessment_type The type of assessment.
     * @param array $form_data The assessment form data.
     * @return array Processed results.
     */
    private function process_assessment_results($assessment_type, $form_data) {
        // This function can be expanded to calculate scores, generate recommendations, etc.
        // For now, it simply returns the form data.
        return $form_data;
    }

}



