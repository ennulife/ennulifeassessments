<?php
/**
 * ENNU Internationalization Manager
 * Handles i18n and l10n for the plugin
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Internationalization {
    
    private static $instance = null;
    private $text_domain = 'ennu-life-assessments';
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'register_strings' ) );
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/../languages'
        );
    }
    
    /**
     * Register translatable strings
     */
    public function register_strings() {
        __( 'Welcome Assessment', $this->text_domain );
        __( 'Hair Assessment', $this->text_domain );
        __( 'Health Assessment', $this->text_domain );
        __( 'Skin Assessment', $this->text_domain );
        __( 'Sleep Assessment', $this->text_domain );
        __( 'Hormone Assessment', $this->text_domain );
        __( 'Menopause Assessment', $this->text_domain );
        __( 'Testosterone Assessment', $this->text_domain );
        __( 'Weight Loss Assessment', $this->text_domain );
        __( 'ED Treatment Assessment', $this->text_domain );
        __( 'Health Optimization Assessment', $this->text_domain );
        
        __( 'Mind', $this->text_domain );
        __( 'Body', $this->text_domain );
        __( 'Lifestyle', $this->text_domain );
        __( 'Aesthetics', $this->text_domain );
        
        __( 'Dashboard', $this->text_domain );
        __( 'My Results', $this->text_domain );
        __( 'My Biomarkers', $this->text_domain );
        __( 'My New Life', $this->text_domain );
        __( 'Book Consultation', $this->text_domain );
        __( 'Take Assessment', $this->text_domain );
        __( 'View Details', $this->text_domain );
        __( 'Retake Assessment', $this->text_domain );
        
        __( 'Lose Weight', $this->text_domain );
        __( 'Build Muscle', $this->text_domain );
        __( 'Improve Sleep', $this->text_domain );
        __( 'Reduce Stress', $this->text_domain );
        __( 'Boost Energy', $this->text_domain );
        __( 'Enhance Focus', $this->text_domain );
        __( 'Better Skin', $this->text_domain );
        __( 'Hair Growth', $this->text_domain );
        __( 'Hormone Balance', $this->text_domain );
        __( 'Heart Health', $this->text_domain );
        __( 'Longevity', $this->text_domain );
        
        __( 'Assessment completed successfully', $this->text_domain );
        __( 'Scores calculated and saved', $this->text_domain );
        __( 'Error processing assessment', $this->text_domain );
        __( 'Please try again', $this->text_domain );
        __( 'Loading...', $this->text_domain );
        __( 'Saving...', $this->text_domain );
    }
    
    /**
     * Get localized script data
     */
    public function get_script_data() {
        return array(
            'loading' => __( 'Loading...', $this->text_domain ),
            'saving' => __( 'Saving...', $this->text_domain ),
            'error' => __( 'An error occurred', $this->text_domain ),
            'success' => __( 'Success!', $this->text_domain ),
            'confirm' => __( 'Are you sure?', $this->text_domain ),
            'cancel' => __( 'Cancel', $this->text_domain ),
            'save' => __( 'Save', $this->text_domain ),
            'delete' => __( 'Delete', $this->text_domain ),
        );
    }
    
    /**
     * Generate .pot file for translations
     */
    public function generate_pot_file() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }
        
        $pot_file = plugin_dir_path( __FILE__ ) . '../languages/ennu-life-assessments.pot';
        
        $pot_content = '# ENNU Life Assessments Translation Template
# Copyright (C) 2025 ENNU Life
# This file is distributed under the GPL v2 or later.
msgid ""
msgstr ""
"Project-Id-Version: ENNU Life Assessments 62.2.8\n"
"Report-Msgid-Bugs-To: support@ennulife.com\n"
"POT-Creation-Date: ' . date( 'Y-m-d H:i:s+0000' ) . '\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Language-Team: ENNU Life <support@ennulife.com>\n"
"Text-Domain: ennu-life-assessments\n"

';
        
        return file_put_contents( $pot_file, $pot_content );
    }
}

ENNU_Internationalization::get_instance();
