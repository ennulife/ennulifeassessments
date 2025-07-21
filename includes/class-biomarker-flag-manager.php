<?php
/**
 * ENNU Biomarker Flag Manager
 * Manages biomarker flagging system for medical providers
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Biomarker_Flag_Manager {

    /**
     * Biomarker manager instance
     */
    private $biomarker_manager;

    /**
     * Initialize biomarker flag manager
     */
    public function __construct() {
        if ( class_exists( 'ENNU_Biomarker_Manager' ) ) {
            $this->biomarker_manager = new ENNU_Biomarker_Manager();
        }
        
        add_action( 'ennu_biomarkers_imported', array( $this, 'auto_flag_biomarkers' ), 10, 2 );
        add_action( 'wp_ajax_ennu_flag_biomarker', array( $this, 'handle_flag_biomarker' ) );
        add_action( 'wp_ajax_ennu_remove_flag', array( $this, 'handle_remove_flag' ) );
        add_action( 'wp_ajax_ennu_get_flagged_biomarkers', array( $this, 'handle_get_flagged_biomarkers' ) );
        
        error_log('ENNU Biomarker Flag Manager: Initialized');
    }

    /**
     * Auto-flag biomarkers based on imported lab data
     *
     * @param int $user_id User ID
     * @param array $biomarker_data Imported biomarker data
     */
    public function auto_flag_biomarkers( $user_id, $biomarker_data ) {
        if ( empty( $biomarker_data ) || ! is_array( $biomarker_data ) ) {
            return;
        }

        foreach ( $biomarker_data as $biomarker_name => $data ) {
            if ( $this->should_auto_flag( $biomarker_name, $data ) ) {
                $this->flag_biomarker( $user_id, $biomarker_name, 'auto_flagged', $this->get_auto_flag_reason( $biomarker_name, $data ) );
            }
        }
    }

    /**
     * Flag a biomarker for medical attention
     *
     * @param int $user_id User ID
     * @param string $biomarker_name Biomarker name
     * @param string $flag_type Flag type (manual, auto_flagged, critical)
     * @param string $reason Reason for flagging
     * @param int $flagged_by User ID of person who flagged (optional)
     * @return bool Success status
     */
    public function flag_biomarker( $user_id, $biomarker_name, $flag_type = 'manual', $reason = '', $flagged_by = null ) {
        $flag_data = array(
            'user_id' => $user_id,
            'biomarker_name' => $biomarker_name,
            'flag_type' => $flag_type,
            'reason' => $reason,
            'flagged_by' => $flagged_by ?: get_current_user_id(),
            'flagged_at' => current_time( 'mysql' ),
            'status' => 'active'
        );

        $existing_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
        if ( ! is_array( $existing_flags ) ) {
            $existing_flags = array();
        }

        $flag_id = $biomarker_name . '_' . time();
        $existing_flags[ $flag_id ] = $flag_data;

        $success = update_user_meta( $user_id, 'ennu_biomarker_flags', $existing_flags );

        if ( $success ) {
            $this->log_flag_action( $user_id, $biomarker_name, 'flagged', $flag_data );
            do_action( 'ennu_biomarker_flagged', $user_id, $biomarker_name, $flag_data );
        }

        return $success;
    }

    /**
     * Remove flag from biomarker
     *
     * @param int $user_id User ID
     * @param string $biomarker_name Biomarker name
     * @param string $removal_reason Reason for removal
     * @param int $removed_by User ID of person who removed flag
     * @return bool Success status
     */
    public function remove_flag( $user_id, $biomarker_name, $removal_reason = '', $removed_by = null ) {
        $existing_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
        if ( ! is_array( $existing_flags ) ) {
            return false;
        }

        $flag_removed = false;
        foreach ( $existing_flags as $flag_id => $flag_data ) {
            if ( $flag_data['biomarker_name'] === $biomarker_name && $flag_data['status'] === 'active' ) {
                $existing_flags[ $flag_id ]['status'] = 'removed';
                $existing_flags[ $flag_id ]['removed_at'] = current_time( 'mysql' );
                $existing_flags[ $flag_id ]['removed_by'] = $removed_by ?: get_current_user_id();
                $existing_flags[ $flag_id ]['removal_reason'] = $removal_reason;
                $flag_removed = true;
            }
        }

        if ( $flag_removed ) {
            $success = update_user_meta( $user_id, 'ennu_biomarker_flags', $existing_flags );
            if ( $success ) {
                $this->log_flag_action( $user_id, $biomarker_name, 'removed', array( 'reason' => $removal_reason ) );
                do_action( 'ennu_biomarker_flag_removed', $user_id, $biomarker_name, $removal_reason );
            }
            return $success;
        }

        return false;
    }

    /**
     * Get flagged biomarkers for a user
     *
     * @param int $user_id User ID
     * @param string $status Flag status (active, removed, all)
     * @return array Flagged biomarkers
     */
    public function get_flagged_biomarkers( $user_id, $status = 'active' ) {
        $all_flags = get_user_meta( $user_id, 'ennu_biomarker_flags', true );
        if ( ! is_array( $all_flags ) ) {
            return array();
        }

        $filtered_flags = array();
        foreach ( $all_flags as $flag_id => $flag_data ) {
            if ( $status === 'all' || $flag_data['status'] === $status ) {
                $filtered_flags[ $flag_id ] = $flag_data;
            }
        }

        return $filtered_flags;
    }

    /**
     * Check if biomarker should be auto-flagged
     *
     * @param string $biomarker_name Biomarker name
     * @param array $data Biomarker data
     * @return bool Whether to auto-flag
     */
    private function should_auto_flag( $biomarker_name, $data ) {
        if ( ! isset( $data['value'] ) || ! isset( $data['status'] ) ) {
            return false;
        }

        $auto_flag_conditions = $this->get_auto_flag_conditions();
        
        if ( ! isset( $auto_flag_conditions[ $biomarker_name ] ) ) {
            return false;
        }

        $conditions = $auto_flag_conditions[ $biomarker_name ];
        $value = floatval( $data['value'] );
        $status = $data['status'];

        if ( $status === 'critical' ) {
            return true;
        }

        if ( isset( $conditions['critical_high'] ) && $value >= $conditions['critical_high'] ) {
            return true;
        }

        if ( isset( $conditions['critical_low'] ) && $value <= $conditions['critical_low'] ) {
            return true;
        }

        return false;
    }

    /**
     * Get auto-flag conditions for biomarkers
     *
     * @return array Auto-flag conditions
     */
    private function get_auto_flag_conditions() {
        return array(
            'testosterone_total' => array(
                'critical_low' => 200,
                'critical_high' => 1500
            ),
            'testosterone_free' => array(
                'critical_low' => 5,
                'critical_high' => 50
            ),
            'estradiol' => array(
                'critical_low' => 10,
                'critical_high' => 200
            ),
            'thyroid_tsh' => array(
                'critical_low' => 0.1,
                'critical_high' => 10
            ),
            'vitamin_d' => array(
                'critical_low' => 20,
                'critical_high' => 150
            ),
            'hemoglobin_a1c' => array(
                'critical_high' => 7.0
            ),
            'cholesterol_total' => array(
                'critical_high' => 300
            ),
            'triglycerides' => array(
                'critical_high' => 500
            )
        );
    }

    /**
     * Get auto-flag reason
     *
     * @param string $biomarker_name Biomarker name
     * @param array $data Biomarker data
     * @return string Flag reason
     */
    private function get_auto_flag_reason( $biomarker_name, $data ) {
        $value = floatval( $data['value'] );
        $status = $data['status'];
        $conditions = $this->get_auto_flag_conditions()[ $biomarker_name ] ?? array();

        if ( $status === 'critical' ) {
            return 'Lab result marked as critical';
        }

        if ( isset( $conditions['critical_high'] ) && $value >= $conditions['critical_high'] ) {
            return "Value {$value} exceeds critical high threshold of {$conditions['critical_high']}";
        }

        if ( isset( $conditions['critical_low'] ) && $value <= $conditions['critical_low'] ) {
            return "Value {$value} below critical low threshold of {$conditions['critical_low']}";
        }

        return 'Automatically flagged based on lab results';
    }

    /**
     * Log flag action
     *
     * @param int $user_id User ID
     * @param string $biomarker_name Biomarker name
     * @param string $action Action performed
     * @param array $data Additional data
     */
    private function log_flag_action( $user_id, $biomarker_name, $action, $data = array() ) {
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'user_id' => $user_id,
            'biomarker_name' => $biomarker_name,
            'action' => $action,
            'data' => $data,
            'performed_by' => get_current_user_id()
        );

        $existing_log = get_option( 'ennu_biomarker_flag_log', array() );
        $existing_log[] = $log_entry;

        update_option( 'ennu_biomarker_flag_log', array_slice( $existing_log, -1000 ) );
    }

    /**
     * Handle AJAX flag biomarker request
     */
    public function handle_flag_biomarker() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $user_id = intval( $_POST['user_id'] ?? 0 );
        $biomarker_name = sanitize_text_field( $_POST['biomarker_name'] ?? '' );
        $reason = sanitize_textarea_field( $_POST['reason'] ?? '' );

        if ( ! $user_id || ! $biomarker_name ) {
            wp_send_json_error( 'Missing required parameters' );
        }

        $success = $this->flag_biomarker( $user_id, $biomarker_name, 'manual', $reason );

        if ( $success ) {
            wp_send_json_success( array( 'message' => 'Biomarker flagged successfully' ) );
        } else {
            wp_send_json_error( 'Failed to flag biomarker' );
        }
    }

    /**
     * Handle AJAX remove flag request
     */
    public function handle_remove_flag() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $user_id = intval( $_POST['user_id'] ?? 0 );
        $biomarker_name = sanitize_text_field( $_POST['biomarker_name'] ?? '' );
        $reason = sanitize_textarea_field( $_POST['reason'] ?? '' );

        if ( ! $user_id || ! $biomarker_name ) {
            wp_send_json_error( 'Missing required parameters' );
        }

        $success = $this->remove_flag( $user_id, $biomarker_name, $reason );

        if ( $success ) {
            wp_send_json_success( array( 'message' => 'Flag removed successfully' ) );
        } else {
            wp_send_json_error( 'Failed to remove flag' );
        }
    }

    /**
     * Handle AJAX get flagged biomarkers request
     */
    public function handle_get_flagged_biomarkers() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $user_id = intval( $_POST['user_id'] ?? 0 );
        $status = sanitize_text_field( $_POST['status'] ?? 'active' );

        if ( ! $user_id ) {
            wp_send_json_error( 'Missing user ID' );
        }

        $flagged_biomarkers = $this->get_flagged_biomarkers( $user_id, $status );

        wp_send_json_success( array( 'flagged_biomarkers' => $flagged_biomarkers ) );
    }
}
