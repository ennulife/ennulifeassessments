<?php
/**
 * ENNU Life Template Loader
 * Handles loading of custom page templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Life_Template_Loader {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_template_assets' ) );
	}

	public function template_include( $template ) {
		global $post;

		if ( ! $post ) {
			return $template;
		}

		// Check if this is an ENNU page
		$ennu_template_key = get_post_meta( $post->ID, '_ennu_template_key', true );

		if ( $ennu_template_key ) {
			$plugin_template = $this->get_template_path( $ennu_template_key );

			if ( $plugin_template && file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	private function get_template_path( $template_key ) {
		return ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_key . '.php';
	}

	public function enqueue_template_assets() {
		global $post;

		if ( ! $post ) {
			return;
		}

		// Check if this is an ENNU page
		$ennu_template_key = get_post_meta( $post->ID, '_ennu_template_key', true );

		if ( $ennu_template_key ) {
			// Enqueue ENNU styles and scripts
			wp_enqueue_style(
				'ennu-main-style',
				ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-main.css',
				array(),
				ENNU_LIFE_VERSION
			);

			wp_enqueue_script(
				'ennu-main-script',
				ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-main.js',
				array( 'jquery' ),
				ENNU_LIFE_VERSION,
				true
			);

			// Localize script for AJAX
			wp_localize_script(
				'ennu-main-script',
				'ennuAjax',
				array(
					'ajaxurl'     => admin_url( 'admin-ajax.php' ),
					'nonce'       => wp_create_nonce( 'ennu_ajax_nonce' ),
					'templateKey' => $ennu_template_key,
				)
			);

			wp_enqueue_style(
				'ennu-logo-style',
				ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-logo.css',
				array(),
				ENNU_LIFE_VERSION
			);
		}
	}

	public function load_template( $template_name, $args = array() ) {
		$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name . '.php';

		if ( file_exists( $template_path ) ) {
			// Do not use extract(). It is a security risk.
			// Instead, make the variables available to the template in a structured way.
			$template_args = $args;

			ob_start();
			include $template_path;
			$output = ob_get_clean();

			// Apply do_shortcode to the output
			return do_shortcode( $output );
		}

		return '<p>Template not found: ' . esc_html( $template_name ) . '</p>';
	}
}

/**
 * Render the ENNU Life logo with options.
 *
 * @param array $args {
 *     @type string $color   'white' or 'black'.
 *     @type string $size    'small', 'medium', 'large', or custom px (e.g. '120px').
 *     @type string $link    URL to link to (default: home_url('/')).
 *     @type string $alt     Alt text for the image.
 *     @type string $class   Extra CSS classes.
 * }
 */
function ennu_render_logo( $args = array() ) {
    $defaults = array(
        'color' => 'white',
        'size'  => 'medium',
        'link'  => home_url( '/' ),
        'alt'   => 'ENNU Life',
        'class' => '',
    );
    $args = wp_parse_args( $args, $defaults );
    $color = $args['color'] === 'black' ? 'black' : 'white';
    $size_class = 'ennu-logo--' . esc_attr( $args['size'] );
    $img_src = ENNU_LIFE_PLUGIN_URL . 'assets/img/ennu-logo-' . $color . '.png';
    $classes = trim( 'ennu-logo ' . $size_class . ' ' . $args['class'] );
    $img = sprintf(
        '<img src="%s" alt="%s" class="%s" loading="lazy" />',
        esc_url( $img_src ),
        esc_attr( $args['alt'] ),
        esc_attr( $classes )
    );
    if ( $args['link'] ) {
        printf('<a href="%s" class="ennu-logo-link" aria-label="%s">%s</a>', esc_url( $args['link'] ), esc_attr( $args['alt'] ), $img );
    } else {
        echo $img;
    }
}



