<?php
/**
 * ENNU Life Template Loader
 * Handles loading of custom page templates
 */

if (!defined("ABSPATH")) {
    exit;
}

class ENNU_Life_Template_Loader {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_filter("template_include", array($this, "template_include"));
        add_action("wp_enqueue_scripts", array($this, "enqueue_template_assets"));
    }
    
    public function template_include($template) {
        global $post;
        
        if (!$post) {
            return $template;
        }
        
        // Check if this is an ENNU page
        $ennu_template_key = get_post_meta($post->ID, "_ennu_template_key", true);
        
        if ($ennu_template_key) {
            $plugin_template = $this->get_template_path($ennu_template_key);
            
            if ($plugin_template && file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    private function get_template_path($template_key) {
        return ENNU_LIFE_PLUGIN_PATH . "templates/" . $template_key . ".php";
    }
    
    public function enqueue_template_assets() {
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Check if this is an ENNU page
        $ennu_template_key = get_post_meta($post->ID, "_ennu_template_key", true);
        
        if ($ennu_template_key) {
            // Enqueue ENNU styles and scripts
            wp_enqueue_style(
                "ennu-main-style",
                ENNU_LIFE_PLUGIN_URL . "assets/css/ennu-main.css",
                array(),
                ENNU_LIFE_VERSION
            );
            
            wp_enqueue_script(
                "ennu-main-script",
                ENNU_LIFE_PLUGIN_URL . "assets/js/ennu-main.js",
                array("jquery"),
                ENNU_LIFE_VERSION,
                true
            );
            
            // Localize script for AJAX
            wp_localize_script("ennu-main-script", "ennuAjax", array(
                "ajaxurl" => admin_url("admin-ajax.php"),
                "nonce" => wp_create_nonce("ennu_ajax_nonce"),
                "templateKey" => $ennu_template_key
            ));
        }
    }
    
    public function load_template($template_name, $args = array()) {
        $template_path = ENNU_LIFE_PLUGIN_PATH . "templates/" . $template_name . ".php";
        
        if (file_exists($template_path)) {
            // Do not use extract(). It is a security risk.
            // Instead, make the variables available to the template in a structured way.
            $template_args = $args;
            
            ob_start();
            include $template_path;
            $output = ob_get_clean();
            
            // Apply do_shortcode to the output
            return do_shortcode($output);
        }
        
        return "<p>Template not found: " . esc_html($template_name) . "</p>";
    }
}



