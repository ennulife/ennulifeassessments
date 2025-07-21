<?php
/**
 * Multi-Tenant Manager
 *
 * Handles multi-tenant functionality for enterprise deployments
 * Provides data isolation, tenant-specific configurations, and scalable architecture
 *
 * @package ENNU_Life_Assessments
 * @subpackage Multi_Tenant
 * @since 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Multi_Tenant_Manager {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Current tenant ID
	 */
	private $current_tenant_id = null;

	/**
	 * Tenant configuration cache
	 */
	private $tenant_config_cache = array();

	/**
	 * Multi-tenancy enabled flag
	 */
	private $multi_tenancy_enabled = false;

	/**
	 * Get singleton instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->multi_tenancy_enabled = get_option( 'ennu_multi_tenancy_enabled', false );

		if ( $this->multi_tenancy_enabled ) {
			$this->init_hooks();
			$this->detect_current_tenant();
		}
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'setup_tenant_context' ) );
		add_action( 'wp_loaded', array( $this, 'validate_tenant_access' ) );
		add_filter( 'pre_get_posts', array( $this, 'filter_posts_by_tenant' ) );
		add_filter( 'pre_get_users', array( $this, 'filter_users_by_tenant' ) );
		add_action( 'user_register', array( $this, 'assign_user_to_tenant' ) );
		add_action( 'wp_login', array( $this, 'validate_tenant_login' ), 10, 2 );

		add_filter( 'posts_where', array( $this, 'filter_posts_where_clause' ), 10, 2 );
		add_filter( 'users_pre_query', array( $this, 'filter_users_query' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_ennu_create_tenant', array( $this, 'handle_create_tenant' ) );
		add_action( 'wp_ajax_ennu_update_tenant', array( $this, 'handle_update_tenant' ) );
		add_action( 'wp_ajax_ennu_delete_tenant', array( $this, 'handle_delete_tenant' ) );
	}

	/**
	 * Detect current tenant based on domain, subdomain, or path
	 */
	private function detect_current_tenant() {
		$detection_method = get_option( 'ennu_tenant_detection_method', 'subdomain' );

		switch ( $detection_method ) {
			case 'domain':
				$this->current_tenant_id = $this->detect_tenant_by_domain();
				break;
			case 'subdomain':
				$this->current_tenant_id = $this->detect_tenant_by_subdomain();
				break;
			case 'path':
				$this->current_tenant_id = $this->detect_tenant_by_path();
				break;
			case 'header':
				$this->current_tenant_id = $this->detect_tenant_by_header();
				break;
			default:
				$this->current_tenant_id = 1; // Default tenant
		}

		if ( ! $this->tenant_exists( $this->current_tenant_id ) ) {
			$this->current_tenant_id = 1; // Fallback to default tenant
		}
	}

	/**
	 * Detect tenant by domain
	 */
	private function detect_tenant_by_domain() {
		$domain = $_SERVER['HTTP_HOST'] ?? '';

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$tenant = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT tenant_id FROM $table_name WHERE domain = %s AND status = 'active'",
				$domain
			)
		);

		return $tenant ? intval( $tenant->tenant_id ) : 1;
	}

	/**
	 * Detect tenant by subdomain
	 */
	private function detect_tenant_by_subdomain() {
		$host      = $_SERVER['HTTP_HOST'] ?? '';
		$subdomain = explode( '.', $host )[0];

		if ( in_array( $subdomain, array( 'www', 'mail', 'ftp', 'admin' ) ) ) {
			return 1;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$tenant = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT tenant_id FROM $table_name WHERE subdomain = %s AND status = 'active'",
				$subdomain
			)
		);

		return $tenant ? intval( $tenant->tenant_id ) : 1;
	}

	/**
	 * Detect tenant by path
	 */
	private function detect_tenant_by_path() {
		$path          = $_SERVER['REQUEST_URI'] ?? '';
		$path_segments = explode( '/', trim( $path, '/' ) );

		if ( empty( $path_segments[0] ) ) {
			return 1;
		}

		$tenant_slug = $path_segments[0];

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$tenant = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT tenant_id FROM $table_name WHERE slug = %s AND status = 'active'",
				$tenant_slug
			)
		);

		return $tenant ? intval( $tenant->tenant_id ) : 1;
	}

	/**
	 * Detect tenant by HTTP header
	 */
	private function detect_tenant_by_header() {
		$tenant_header = $_SERVER['HTTP_X_TENANT_ID'] ?? '';

		if ( empty( $tenant_header ) ) {
			return 1;
		}

		return intval( $tenant_header );
	}

	/**
	 * Check if tenant exists
	 */
	private function tenant_exists( $tenant_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE tenant_id = %d AND status = 'active'",
				$tenant_id
			)
		);

		return intval( $count ) > 0;
	}

	/**
	 * Setup tenant context
	 */
	public function setup_tenant_context() {
		if ( ! $this->multi_tenancy_enabled || ! $this->current_tenant_id ) {
			return;
		}

		$this->load_tenant_configuration();

		$this->apply_tenant_branding();

		$this->set_database_context();
	}

	/**
	 * Load tenant configuration
	 */
	private function load_tenant_configuration() {
		if ( isset( $this->tenant_config_cache[ $this->current_tenant_id ] ) ) {
			return $this->tenant_config_cache[ $this->current_tenant_id ];
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenant_config';

		$config = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT config_key, config_value FROM $table_name WHERE tenant_id = %d",
				$this->current_tenant_id
			),
			ARRAY_A
		);

		$tenant_config = array();
		foreach ( $config as $item ) {
			$tenant_config[ $item['config_key'] ] = maybe_unserialize( $item['config_value'] );
		}

		$this->tenant_config_cache[ $this->current_tenant_id ] = $tenant_config;

		return $tenant_config;
	}

	/**
	 * Apply tenant-specific branding
	 */
	private function apply_tenant_branding() {
		$config = $this->load_tenant_configuration();

		if ( ! empty( $config['logo_url'] ) ) {
			add_filter(
				'get_custom_logo',
				function() use ( $config ) {
					return '<img src="' . esc_url( $config['logo_url'] ) . '" alt="' . esc_attr( $config['name'] ?? 'Logo' ) . '">';
				}
			);
		}

		if ( ! empty( $config['primary_color'] ) ) {
			add_action(
				'wp_head',
				function() use ( $config ) {
					echo '<style>:root { --ennu-primary-color: ' . esc_attr( $config['primary_color'] ) . '; }</style>';
				}
			);
		}

		if ( ! empty( $config['site_title'] ) ) {
			add_filter(
				'bloginfo',
				function( $output, $show ) use ( $config ) {
					if ( $show === 'name' ) {
						return $config['site_title'];
					}
					return $output;
				},
				10,
				2
			);
		}
	}

	/**
	 * Set database context for tenant isolation
	 */
	private function set_database_context() {
		add_filter( 'wp_insert_post_data', array( $this, 'add_tenant_to_post_data' ), 10, 2 );
		add_action( 'wp_insert_post', array( $this, 'add_tenant_meta_to_post' ), 10, 3 );
	}

	/**
	 * Validate tenant access
	 */
	public function validate_tenant_access() {
		if ( ! $this->multi_tenancy_enabled ) {
			return;
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$user_tenant_id = get_user_meta( $user_id, 'ennu_tenant_id', true );

		if ( is_super_admin( $user_id ) ) {
			return;
		}

		if ( $user_tenant_id && intval( $user_tenant_id ) !== $this->current_tenant_id ) {
			wp_die( __( 'Access denied. You do not have permission to access this tenant.', 'ennu-life-assessments' ) );
		}
	}

	/**
	 * Filter posts by tenant
	 */
	public function filter_posts_by_tenant( $query ) {
		if ( ! $this->multi_tenancy_enabled || is_admin() ) {
			return $query;
		}

		if ( ! $query->is_main_query() ) {
			return $query;
		}

		$meta_query   = $query->get( 'meta_query' ) ?: array();
		$meta_query[] = array(
			'key'     => 'ennu_tenant_id',
			'value'   => $this->current_tenant_id,
			'compare' => '=',
		);

		$query->set( 'meta_query', $meta_query );

		return $query;
	}

	/**
	 * Filter users by tenant
	 */
	public function filter_users_by_tenant( $query ) {
		if ( ! $this->multi_tenancy_enabled || is_super_admin() ) {
			return $query;
		}

		$meta_query   = $query->get( 'meta_query' ) ?: array();
		$meta_query[] = array(
			'key'     => 'ennu_tenant_id',
			'value'   => $this->current_tenant_id,
			'compare' => '=',
		);

		$query->set( 'meta_query', $meta_query );

		return $query;
	}

	/**
	 * Add tenant ID to post data
	 */
	public function add_tenant_to_post_data( $data, $postarr ) {
		return $data;
	}

	/**
	 * Add tenant meta to post
	 */
	public function add_tenant_meta_to_post( $post_id, $post, $update ) {
		if ( ! $this->multi_tenancy_enabled ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || $post->post_status === 'auto-draft' ) {
			return;
		}

		update_post_meta( $post_id, 'ennu_tenant_id', $this->current_tenant_id );
	}

	/**
	 * Assign user to tenant on registration
	 */
	public function assign_user_to_tenant( $user_id ) {
		if ( ! $this->multi_tenancy_enabled ) {
			return;
		}

		update_user_meta( $user_id, 'ennu_tenant_id', $this->current_tenant_id );
	}

	/**
	 * Validate tenant login
	 */
	public function validate_tenant_login( $user_login, $user ) {
		if ( ! $this->multi_tenancy_enabled ) {
			return;
		}

		$user_tenant_id = get_user_meta( $user->ID, 'ennu_tenant_id', true );

		if ( is_super_admin( $user->ID ) ) {
			return;
		}

		if ( $user_tenant_id && intval( $user_tenant_id ) !== $this->current_tenant_id ) {
			wp_logout();
			wp_die( __( 'Login failed. You do not have permission to access this tenant.', 'ennu-life-assessments' ) );
		}
	}

	/**
	 * Filter posts WHERE clause for tenant isolation
	 */
	public function filter_posts_where_clause( $where, $query ) {
		if ( ! $this->multi_tenancy_enabled || is_admin() ) {
			return $where;
		}

		global $wpdb;

		if ( $query->is_main_query() && ! $query->is_admin ) {
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.ID IN (
                    SELECT post_id FROM {$wpdb->postmeta} 
                    WHERE meta_key = 'ennu_tenant_id' 
                    AND meta_value = %d
                )",
				$this->current_tenant_id
			);
		}

		return $where;
	}

	/**
	 * Filter users query for tenant isolation
	 */
	public function filter_users_query( $results, $query ) {
		if ( ! $this->multi_tenancy_enabled || is_super_admin() ) {
			return $results;
		}

		return $results;
	}

	/**
	 * Get current tenant ID
	 */
	public function get_current_tenant_id() {
		return $this->current_tenant_id;
	}

	/**
	 * Get tenant configuration
	 */
	public function get_tenant_config( $key = null, $default = null ) {
		$config = $this->load_tenant_configuration();

		if ( $key === null ) {
			return $config;
		}

		return isset( $config[ $key ] ) ? $config[ $key ] : $default;
	}

	/**
	 * Set tenant configuration
	 */
	public function set_tenant_config( $key, $value, $tenant_id = null ) {
		if ( $tenant_id === null ) {
			$tenant_id = $this->current_tenant_id;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenant_config';

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT config_id FROM $table_name WHERE tenant_id = %d AND config_key = %s",
				$tenant_id,
				$key
			)
		);

		$serialized_value = maybe_serialize( $value );

		if ( $existing ) {
			$wpdb->update(
				$table_name,
				array( 'config_value' => $serialized_value ),
				array(
					'tenant_id'  => $tenant_id,
					'config_key' => $key,
				),
				array( '%s' ),
				array( '%d', '%s' )
			);
		} else {
			$wpdb->insert(
				$table_name,
				array(
					'tenant_id'    => $tenant_id,
					'config_key'   => $key,
					'config_value' => $serialized_value,
				),
				array( '%d', '%s', '%s' )
			);
		}

		unset( $this->tenant_config_cache[ $tenant_id ] );
	}

	/**
	 * Create database tables for multi-tenancy
	 */
	public function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$tenants_table = $wpdb->prefix . 'ennu_tenants';
		$tenants_sql   = "CREATE TABLE $tenants_table (
            tenant_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(100) NOT NULL,
            domain varchar(255) DEFAULT NULL,
            subdomain varchar(100) DEFAULT NULL,
            status enum('active','inactive','suspended') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (tenant_id),
            UNIQUE KEY slug (slug),
            UNIQUE KEY domain (domain),
            UNIQUE KEY subdomain (subdomain),
            KEY status (status)
        ) $charset_collate;";

		$config_table = $wpdb->prefix . 'ennu_tenant_config';
		$config_sql   = "CREATE TABLE $config_table (
            config_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tenant_id bigint(20) unsigned NOT NULL,
            config_key varchar(255) NOT NULL,
            config_value longtext,
            PRIMARY KEY (config_id),
            UNIQUE KEY tenant_config (tenant_id, config_key),
            KEY tenant_id (tenant_id),
            KEY config_key (config_key)
        ) $charset_collate;";

		$tenant_users_table = $wpdb->prefix . 'ennu_tenant_users';
		$tenant_users_sql   = "CREATE TABLE $tenant_users_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            tenant_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            role varchar(50) DEFAULT 'member',
            status enum('active','inactive','pending') DEFAULT 'active',
            joined_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY tenant_user (tenant_id, user_id),
            KEY tenant_id (tenant_id),
            KEY user_id (user_id),
            KEY role (role),
            KEY status (status)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $tenants_sql );
		dbDelta( $config_sql );
		dbDelta( $tenant_users_sql );

		$default_tenant = $wpdb->get_var( "SELECT COUNT(*) FROM $tenants_table" );
		if ( intval( $default_tenant ) === 0 ) {
			$wpdb->insert(
				$tenants_table,
				array(
					'tenant_id' => 1,
					'name'      => 'Default Tenant',
					'slug'      => 'default',
					'status'    => 'active',
				),
				array( '%d', '%s', '%s', '%s' )
			);
		}
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_submenu_page(
			'ennu-life-assessments',
			__( 'Multi-Tenant Management', 'ennu-life-assessments' ),
			__( 'Multi-Tenant', 'ennu-life-assessments' ),
			'manage_options',
			'ennu-multi-tenant',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Multi-Tenant Management', 'ennu-life-assessments' ); ?></h1>
			
			<div class="ennu-multi-tenant-admin">
				<div class="tenant-controls">
					<button type="button" class="button button-primary" id="create-tenant-btn">
						<?php _e( 'Create New Tenant', 'ennu-life-assessments' ); ?>
					</button>
				</div>
				
				<div class="tenant-list">
					<h2><?php _e( 'Existing Tenants', 'ennu-life-assessments' ); ?></h2>
					<?php $this->render_tenant_list(); ?>
				</div>
				
				<div class="tenant-settings">
					<h2><?php _e( 'Multi-Tenancy Settings', 'ennu-life-assessments' ); ?></h2>
					<?php $this->render_tenant_settings(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render tenant list
	 */
	private function render_tenant_list() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$tenants = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );

		if ( empty( $tenants ) ) {
			echo '<p>' . __( 'No tenants found.', 'ennu-life-assessments' ) . '</p>';
			return;
		}

		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . __( 'ID', 'ennu-life-assessments' ) . '</th>';
		echo '<th>' . __( 'Name', 'ennu-life-assessments' ) . '</th>';
		echo '<th>' . __( 'Slug', 'ennu-life-assessments' ) . '</th>';
		echo '<th>' . __( 'Domain', 'ennu-life-assessments' ) . '</th>';
		echo '<th>' . __( 'Status', 'ennu-life-assessments' ) . '</th>';
		echo '<th>' . __( 'Created', 'ennu-life-assessments' ) . '</th>';
		echo '<th>' . __( 'Actions', 'ennu-life-assessments' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $tenants as $tenant ) {
			echo '<tr>';
			echo '<td>' . esc_html( $tenant->tenant_id ) . '</td>';
			echo '<td>' . esc_html( $tenant->name ) . '</td>';
			echo '<td>' . esc_html( $tenant->slug ) . '</td>';
			echo '<td>' . esc_html( $tenant->domain ?: $tenant->subdomain ?: '-' ) . '</td>';
			echo '<td><span class="status-' . esc_attr( $tenant->status ) . '">' . esc_html( ucfirst( $tenant->status ) ) . '</span></td>';
			echo '<td>' . esc_html( date( 'Y-m-d H:i', strtotime( $tenant->created_at ) ) ) . '</td>';
			echo '<td>';
			echo '<button type="button" class="button button-small edit-tenant" data-tenant-id="' . esc_attr( $tenant->tenant_id ) . '">' . __( 'Edit', 'ennu-life-assessments' ) . '</button> ';
			if ( $tenant->tenant_id != 1 ) { // Don't allow deleting default tenant
				echo '<button type="button" class="button button-small button-link-delete delete-tenant" data-tenant-id="' . esc_attr( $tenant->tenant_id ) . '">' . __( 'Delete', 'ennu-life-assessments' ) . '</button>';
			}
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Render tenant settings
	 */
	private function render_tenant_settings() {
		$multi_tenancy_enabled = get_option( 'ennu_multi_tenancy_enabled', false );
		$detection_method      = get_option( 'ennu_tenant_detection_method', 'subdomain' );

		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'ennu_multi_tenant_settings' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Enable Multi-Tenancy', 'ennu-life-assessments' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="ennu_multi_tenancy_enabled" value="1" <?php checked( $multi_tenancy_enabled ); ?> />
							<?php _e( 'Enable multi-tenant functionality', 'ennu-life-assessments' ); ?>
						</label>
						<p class="description"><?php _e( 'Warning: Enabling this will affect how data is displayed and accessed.', 'ennu-life-assessments' ); ?></p>
					</td>
				</tr>
				
				<tr>
					<th scope="row"><?php _e( 'Tenant Detection Method', 'ennu-life-assessments' ); ?></th>
					<td>
						<select name="ennu_tenant_detection_method">
							<option value="subdomain" <?php selected( $detection_method, 'subdomain' ); ?>><?php _e( 'Subdomain (tenant.example.com)', 'ennu-life-assessments' ); ?></option>
							<option value="domain" <?php selected( $detection_method, 'domain' ); ?>><?php _e( 'Domain (tenant.com)', 'ennu-life-assessments' ); ?></option>
							<option value="path" <?php selected( $detection_method, 'path' ); ?>><?php _e( 'Path (example.com/tenant)', 'ennu-life-assessments' ); ?></option>
							<option value="header" <?php selected( $detection_method, 'header' ); ?>><?php _e( 'HTTP Header (X-Tenant-ID)', 'ennu-life-assessments' ); ?></option>
						</select>
						<p class="description"><?php _e( 'Choose how tenants are identified.', 'ennu-life-assessments' ); ?></p>
					</td>
				</tr>
			</table>
			
			<?php submit_button(); ?>
		</form>
		<?php
	}

	/**
	 * Handle create tenant AJAX
	 */
	public function handle_create_tenant() {
		check_ajax_referer( 'ennu_multi_tenant_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$name      = sanitize_text_field( $_POST['name'] );
		$slug      = sanitize_title( $_POST['slug'] );
		$domain    = sanitize_text_field( $_POST['domain'] );
		$subdomain = sanitize_text_field( $_POST['subdomain'] );

		if ( empty( $name ) || empty( $slug ) ) {
			wp_send_json_error( array( 'message' => 'Name and slug are required' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$result = $wpdb->insert(
			$table_name,
			array(
				'name'      => $name,
				'slug'      => $slug,
				'domain'    => $domain ?: null,
				'subdomain' => $subdomain ?: null,
				'status'    => 'active',
			),
			array( '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			wp_send_json_success(
				array(
					'message'   => 'Tenant created successfully',
					'tenant_id' => $wpdb->insert_id,
				)
			);
		} else {
			wp_send_json_error( array( 'message' => 'Failed to create tenant' ) );
		}
	}

	/**
	 * Handle update tenant AJAX
	 */
	public function handle_update_tenant() {
		check_ajax_referer( 'ennu_multi_tenant_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$tenant_id = intval( $_POST['tenant_id'] );
		$name      = sanitize_text_field( $_POST['name'] );
		$slug      = sanitize_title( $_POST['slug'] );
		$domain    = sanitize_text_field( $_POST['domain'] );
		$subdomain = sanitize_text_field( $_POST['subdomain'] );
		$status    = sanitize_text_field( $_POST['status'] );

		if ( empty( $name ) || empty( $slug ) ) {
			wp_send_json_error( array( 'message' => 'Name and slug are required' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$result = $wpdb->update(
			$table_name,
			array(
				'name'      => $name,
				'slug'      => $slug,
				'domain'    => $domain ?: null,
				'subdomain' => $subdomain ?: null,
				'status'    => $status,
			),
			array( 'tenant_id' => $tenant_id ),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		if ( $result !== false ) {
			wp_send_json_success( array( 'message' => 'Tenant updated successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to update tenant' ) );
		}
	}

	/**
	 * Handle delete tenant AJAX
	 */
	public function handle_delete_tenant() {
		check_ajax_referer( 'ennu_multi_tenant_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		$tenant_id = intval( $_POST['tenant_id'] );

		if ( $tenant_id === 1 ) {
			wp_send_json_error( array( 'message' => 'Cannot delete default tenant' ) );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_tenants';

		$result = $wpdb->delete(
			$table_name,
			array( 'tenant_id' => $tenant_id ),
			array( '%d' )
		);

		if ( $result ) {
			$config_table = $wpdb->prefix . 'ennu_tenant_config';
			$wpdb->delete( $config_table, array( 'tenant_id' => $tenant_id ), array( '%d' ) );

			wp_send_json_success( array( 'message' => 'Tenant deleted successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to delete tenant' ) );
		}
	}
}

ENNU_Multi_Tenant_Manager::get_instance();
