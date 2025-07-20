<?php
/**
 * ENNU Life Biomarker Admin Interface
 * Handles lab data import, doctor recommendations, and biomarker management
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Biomarker_Admin {
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_biomarker_admin_page' ) );
        add_action( 'wp_ajax_ennu_import_lab_data', array( $this, 'handle_lab_data_import' ) );
        add_action( 'wp_ajax_ennu_save_doctor_targets', array( $this, 'handle_doctor_targets' ) );
        add_action( 'wp_ajax_ennu_save_biomarker_data', array( $this, 'handle_biomarker_data_save' ) );
        add_action( 'wp_ajax_ennu_get_user_biomarkers', array( $this, 'handle_get_user_biomarkers' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }
    
    public function add_biomarker_admin_page() {
        add_submenu_page(
            'ennu-life',
            __( 'Lab Data Management', 'ennulifeassessments' ),
            __( 'Lab Data', 'ennulifeassessments' ),
            'manage_options',
            'ennu-lab-data',
            array( $this, 'render_lab_data_page' )
        );
    }
    
    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'ennu-lab-data' ) === false ) {
            return;
        }
        
        wp_enqueue_style( 'ennu-biomarker-admin', ENNU_LIFE_PLUGIN_URL . 'assets/css/biomarker-admin.css', array(), ENNU_LIFE_VERSION );
        wp_enqueue_script( 'ennu-biomarker-admin', ENNU_LIFE_PLUGIN_URL . 'assets/js/biomarker-admin.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
        
        wp_localize_script( 'ennu-biomarker-admin', 'ennuBiomarkerAdmin', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ennu_biomarker_admin_nonce' ),
        ) );
    }
    
    public function render_lab_data_page() {
        $users = get_users( array( 'meta_key' => 'ennu_life_score' ) );
        $biomarker_config = $this->get_biomarker_config();
        
        ?>
        <div class="wrap ennu-biomarker-admin">
            <h1><?php esc_html_e( 'Lab Data Management', 'ennulifeassessments' ); ?></h1>
            
            <div class="ennu-admin-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#tab-import" class="nav-tab nav-tab-active"><?php esc_html_e( 'Import Lab Data', 'ennulifeassessments' ); ?></a>
                    <a href="#tab-targets" class="nav-tab"><?php esc_html_e( 'Doctor Targets', 'ennulifeassessments' ); ?></a>
                    <a href="#tab-overview" class="nav-tab"><?php esc_html_e( 'Biomarker Overview', 'ennulifeassessments' ); ?></a>
                </nav>
                
                <!-- Tab 1: Import Lab Data -->
                <div id="tab-import" class="tab-content active">
                    <h2><?php esc_html_e( 'Import Lab Data', 'ennulifeassessments' ); ?></h2>
                    
                    <form id="ennu-lab-import-form" enctype="multipart/form-data">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Select User', 'ennulifeassessments' ); ?></th>
                                <td>
                                    <select name="user_id" required>
                                        <option value=""><?php esc_html_e( 'Select a user...', 'ennulifeassessments' ); ?></option>
                                        <?php foreach ( $users as $user ) : ?>
                                            <option value="<?php echo esc_attr( $user->ID ); ?>">
                                                <?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Import Method', 'ennulifeassessments' ); ?></th>
                                <td>
                                    <label><input type="radio" name="import_method" value="csv" checked> <?php esc_html_e( 'CSV Upload', 'ennulifeassessments' ); ?></label><br>
                                    <label><input type="radio" name="import_method" value="manual"> <?php esc_html_e( 'Manual Entry', 'ennulifeassessments' ); ?></label>
                                </td>
                            </tr>
                            <tr class="csv-upload-row">
                                <th scope="row"><?php esc_html_e( 'CSV File', 'ennulifeassessments' ); ?></th>
                                <td>
                                    <input type="file" name="lab_data_csv" accept=".csv" />
                                    <p class="description"><?php esc_html_e( 'Upload a CSV file with biomarker data. Format: biomarker_name, value, unit, date', 'ennulifeassessments' ); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <div class="manual-entry-section" style="display: none;">
                            <h3><?php esc_html_e( 'Manual Biomarker Entry', 'ennulifeassessments' ); ?></h3>
                            <div id="biomarker-entries">
                                <?php $this->render_biomarker_entry_form( $biomarker_config ); ?>
                            </div>
                            <button type="button" id="add-biomarker-entry" class="button"><?php esc_html_e( 'Add Another Biomarker', 'ennulifeassessments' ); ?></button>
                        </div>
                        
                        <?php wp_nonce_field( 'ennu_biomarker_admin_nonce', 'nonce' ); ?>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Import Lab Data', 'ennulifeassessments' ); ?>" />
                        </p>
                    </form>
                </div>
                
                <!-- Tab 2: Doctor Targets -->
                <div id="tab-targets" class="tab-content">
                    <h2><?php esc_html_e( 'Doctor Target Values', 'ennulifeassessments' ); ?></h2>
                    
                    <form id="ennu-doctor-targets-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e( 'Select User', 'ennulifeassessments' ); ?></th>
                                <td>
                                    <select name="user_id" id="targets-user-select" required>
                                        <option value=""><?php esc_html_e( 'Select a user...', 'ennulifeassessments' ); ?></option>
                                        <?php foreach ( $users as $user ) : ?>
                                            <option value="<?php echo esc_attr( $user->ID ); ?>">
                                                <?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        
                        <div id="doctor-targets-section" style="display: none;">
                            <h3><?php esc_html_e( 'Set Target Values', 'ennulifeassessments' ); ?></h3>
                            <div id="targets-container"></div>
                        </div>
                        
                        <?php wp_nonce_field( 'ennu_biomarker_admin_nonce', 'nonce' ); ?>
                        <p class="submit">
                            <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Target Values', 'ennulifeassessments' ); ?>" />
                        </p>
                    </form>
                </div>
                
                <!-- Tab 3: Biomarker Overview -->
                <div id="tab-overview" class="tab-content">
                    <h2><?php esc_html_e( 'Biomarker Overview', 'ennulifeassessments' ); ?></h2>
                    
                    <div class="biomarker-stats">
                        <?php $this->render_biomarker_statistics(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_biomarker_entry_form( $biomarker_config ) {
        ?>
        <div class="biomarker-entry">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Biomarker', 'ennulifeassessments' ); ?></th>
                    <td>
                        <select name="biomarker_name[]" required>
                            <option value=""><?php esc_html_e( 'Select biomarker...', 'ennulifeassessments' ); ?></option>
                            <?php foreach ( $biomarker_config as $key => $biomarker ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>">
                                    <?php echo esc_html( $biomarker['name'] . ' (' . $biomarker['unit'] . ')' ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Value', 'ennulifeassessments' ); ?></th>
                    <td><input type="number" step="0.01" name="biomarker_value[]" required /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Test Date', 'ennulifeassessments' ); ?></th>
                    <td><input type="date" name="test_date[]" required /></td>
                </tr>
            </table>
            <button type="button" class="remove-biomarker-entry button"><?php esc_html_e( 'Remove', 'ennulifeassessments' ); ?></button>
        </div>
        <?php
    }
    
    private function render_biomarker_statistics() {
        $users_with_biomarkers = get_users( array( 'meta_key' => 'ennu_biomarker_data' ) );
        $total_biomarkers = 0;
        $biomarker_counts = array();
        
        foreach ( $users_with_biomarkers as $user ) {
            $biomarker_data = get_user_meta( $user->ID, 'ennu_biomarker_data', true );
            if ( is_array( $biomarker_data ) ) {
                $total_biomarkers += count( $biomarker_data );
                foreach ( array_keys( $biomarker_data ) as $biomarker ) {
                    $biomarker_counts[ $biomarker ] = ( $biomarker_counts[ $biomarker ] ?? 0 ) + 1;
                }
            }
        }
        
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo esc_html( count( $users_with_biomarkers ) ); ?></h3>
                <p><?php esc_html_e( 'Users with Lab Data', 'ennulifeassessments' ); ?></p>
            </div>
            <div class="stat-card">
                <h3><?php echo esc_html( $total_biomarkers ); ?></h3>
                <p><?php esc_html_e( 'Total Biomarker Records', 'ennulifeassessments' ); ?></p>
            </div>
            <div class="stat-card">
                <h3><?php echo esc_html( count( $biomarker_counts ) ); ?></h3>
                <p><?php esc_html_e( 'Unique Biomarkers Tracked', 'ennulifeassessments' ); ?></p>
            </div>
        </div>
        
        <?php if ( ! empty( $biomarker_counts ) ) : ?>
            <h3><?php esc_html_e( 'Most Common Biomarkers', 'ennulifeassessments' ); ?></h3>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Biomarker', 'ennulifeassessments' ); ?></th>
                        <th><?php esc_html_e( 'Users Tested', 'ennulifeassessments' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    arsort( $biomarker_counts );
                    foreach ( array_slice( $biomarker_counts, 0, 10 ) as $biomarker => $count ) :
                    ?>
                        <tr>
                            <td><?php echo esc_html( ucwords( str_replace( '_', ' ', $biomarker ) ) ); ?></td>
                            <td><?php echo esc_html( $count ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <?php
    }
    
    public function handle_lab_data_import() {
        ENNU_AJAX_Security::validate_ajax_request();
        
        check_ajax_referer( 'ennu_biomarker_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
        }
        
        $user_id = intval( $_POST['user_id'] ?? 0 );
        $import_method = sanitize_text_field( $_POST['import_method'] ?? '' );
        
        if ( ! $user_id || $user_id <= 0 ) {
            wp_send_json_error( array( 'message' => 'Invalid user ID.' ) );
        }
        
        if ( ! get_userdata( $user_id ) ) {
            wp_send_json_error( array( 'message' => 'User does not exist.' ) );
        }
        
        $allowed_import_methods = array( 'csv', 'manual' );
        if ( ! in_array( $import_method, $allowed_import_methods, true ) ) {
            wp_send_json_error( array( 'message' => 'Invalid import method.' ) );
        }
        
        if ( $import_method === 'csv' ) {
            $result = $this->process_csv_import( $user_id );
        } else {
            $result = $this->process_manual_import( $user_id );
        }
        
        if ( $result['success'] ) {
            do_action( 'ennu_biomarker_imported', $user_id, $result['data'] );
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }
    
    public function handle_doctor_targets() {
        ENNU_AJAX_Security::validate_ajax_request();
        
        check_ajax_referer( 'ennu_biomarker_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
        }
        
        $user_id = intval( $_POST['user_id'] ?? 0 );
        $targets = $_POST['targets'] ?? array();
        
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => 'Invalid user ID.' ) );
        }
        
        $sanitized_targets = array();
        foreach ( $targets as $biomarker => $target_value ) {
            if ( ! empty( $target_value ) ) {
                $sanitized_targets[ sanitize_key( $biomarker ) ] = floatval( $target_value );
            }
        }
        
        update_user_meta( $user_id, 'ennu_doctor_targets', $sanitized_targets );
        
        $import_history = get_user_meta( $user_id, 'ennu_lab_import_history', true ) ?: array();
        $import_history[] = array(
            'import_date' => current_time( 'mysql' ),
            'import_type' => 'doctor_targets',
            'targets_set' => count( $sanitized_targets ),
            'imported_by' => get_current_user_id(),
            'processing_status' => 'completed'
        );
        update_user_meta( $user_id, 'ennu_lab_import_history', $import_history );
        
        wp_send_json_success( array( 'message' => 'Doctor targets saved successfully.' ) );
    }
    
    private function process_csv_import( $user_id ) {
        if ( ! isset( $_FILES['lab_data_csv'] ) || $_FILES['lab_data_csv']['error'] !== UPLOAD_ERR_OK ) {
            return array( 'success' => false, 'message' => 'CSV file upload failed.' );
        }
        
        $csv_file = $_FILES['lab_data_csv']['tmp_name'];
        $biomarker_data = array();
        $errors = array();
        
        if ( ( $handle = fopen( $csv_file, 'r' ) ) !== false ) {
            $header = fgetcsv( $handle );
            
            while ( ( $row = fgetcsv( $handle ) ) !== false ) {
                if ( count( $row ) >= 4 ) {
                    $biomarker_name = sanitize_key( $row[0] );
                    $value = floatval( $row[1] );
                    $unit = sanitize_text_field( $row[2] );
                    $test_date = sanitize_text_field( $row[3] );
                    
                    if ( $this->validate_biomarker_data( $biomarker_name, $value, $unit ) ) {
                        $biomarker_data[ $biomarker_name ] = array(
                            'value' => $value,
                            'unit' => $unit,
                            'test_date' => $test_date,
                            'status' => $this->determine_biomarker_status( $biomarker_name, $value ),
                            'import_date' => current_time( 'mysql' ),
                            'import_method' => 'csv'
                        );
                    } else {
                        $errors[] = "Invalid data for biomarker: {$biomarker_name}";
                    }
                }
            }
            fclose( $handle );
        }
        
        if ( ! empty( $biomarker_data ) ) {
            $existing_data = get_user_meta( $user_id, 'ennu_biomarker_data', true ) ?: array();
            $merged_data = array_merge( $existing_data, $biomarker_data );
            update_user_meta( $user_id, 'ennu_biomarker_data', $merged_data );
            
            $this->update_import_history( $user_id, 'csv_upload', count( $biomarker_data ), $errors );
            
            return array(
                'success' => true,
                'message' => sprintf( 'Successfully imported %d biomarkers.', count( $biomarker_data ) ),
                'data' => $biomarker_data,
                'errors' => $errors
            );
        }
        
        return array( 'success' => false, 'message' => 'No valid biomarker data found in CSV.' );
    }
    
    private function process_manual_import( $user_id ) {
        $biomarker_names = $_POST['biomarker_name'] ?? array();
        $biomarker_values = $_POST['biomarker_value'] ?? array();
        $test_dates = $_POST['test_date'] ?? array();
        
        $biomarker_data = array();
        $biomarker_config = $this->get_biomarker_config();
        
        for ( $i = 0; $i < count( $biomarker_names ); $i++ ) {
            $biomarker_name = sanitize_key( $biomarker_names[ $i ] );
            $value = floatval( $biomarker_values[ $i ] );
            $test_date = sanitize_text_field( $test_dates[ $i ] );
            
            if ( isset( $biomarker_config[ $biomarker_name ] ) && $value > 0 ) {
                $config = $biomarker_config[ $biomarker_name ];
                $biomarker_data[ $biomarker_name ] = array(
                    'value' => $value,
                    'unit' => $config['unit'],
                    'test_date' => $test_date,
                    'status' => $this->determine_biomarker_status( $biomarker_name, $value ),
                    'import_date' => current_time( 'mysql' ),
                    'import_method' => 'manual'
                );
            }
        }
        
        if ( ! empty( $biomarker_data ) ) {
            $existing_data = get_user_meta( $user_id, 'ennu_biomarker_data', true ) ?: array();
            $merged_data = array_merge( $existing_data, $biomarker_data );
            update_user_meta( $user_id, 'ennu_biomarker_data', $merged_data );
            
            $this->update_import_history( $user_id, 'manual_entry', count( $biomarker_data ), array() );
            
            return array(
                'success' => true,
                'message' => sprintf( 'Successfully saved %d biomarkers.', count( $biomarker_data ) ),
                'data' => $biomarker_data
            );
        }
        
        return array( 'success' => false, 'message' => 'No valid biomarker data provided.' );
    }
    
    private function get_biomarker_config() {
        $core_biomarkers_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/ennu-life-core-biomarkers.php';
        $advanced_biomarkers_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/advanced-biomarker-addons.php';
        
        $biomarkers = array();
        
        if ( file_exists( $core_biomarkers_file ) ) {
            $core_config = require $core_biomarkers_file;
            if ( isset( $core_config['biomarkers'] ) ) {
                $biomarkers = array_merge( $biomarkers, $core_config['biomarkers'] );
            }
        }
        
        if ( file_exists( $advanced_biomarkers_file ) ) {
            $advanced_config = require $advanced_biomarkers_file;
            if ( isset( $advanced_config['biomarkers'] ) ) {
                $biomarkers = array_merge( $biomarkers, $advanced_config['biomarkers'] );
            }
        }
        
        return $biomarkers;
    }
    
    private function validate_biomarker_data( $biomarker_name, $value, $unit ) {
        $biomarker_config = $this->get_biomarker_config();
        
        if ( ! isset( $biomarker_config[ $biomarker_name ] ) ) {
            return false;
        }
        
        if ( $value <= 0 ) {
            return false;
        }
        
        $expected_unit = $biomarker_config[ $biomarker_name ]['unit'] ?? '';
        if ( $expected_unit && $unit !== $expected_unit ) {
            return false;
        }
        
        return true;
    }
    
    private function determine_biomarker_status( $biomarker_name, $value ) {
        $biomarker_config = $this->get_biomarker_config();
        
        if ( ! isset( $biomarker_config[ $biomarker_name ] ) ) {
            return 'unknown';
        }
        
        $config = $biomarker_config[ $biomarker_name ];
        $ranges = $config['ranges'] ?? array();
        
        if ( isset( $ranges['optimal'] ) ) {
            $optimal = $ranges['optimal'];
            if ( $value >= $optimal['min'] && $value <= $optimal['max'] ) {
                return 'optimal';
            }
        }
        
        if ( isset( $ranges['suboptimal'] ) ) {
            $suboptimal = $ranges['suboptimal'];
            if ( $value >= $suboptimal['min'] && $value <= $suboptimal['max'] ) {
                return 'suboptimal';
            }
        }
        
        return 'poor';
    }
    
    private function update_import_history( $user_id, $import_type, $biomarkers_imported, $errors ) {
        $import_history = get_user_meta( $user_id, 'ennu_lab_import_history', true ) ?: array();
        
        $import_history[] = array(
            'import_date' => current_time( 'mysql' ),
            'import_type' => $import_type,
            'biomarkers_imported' => $biomarkers_imported,
            'imported_by' => get_current_user_id(),
            'validation_errors' => $errors,
            'processing_status' => 'completed'
        );
        
        update_user_meta( $user_id, 'ennu_lab_import_history', $import_history );
    }
    
    public function handle_biomarker_data_save() {
        ENNU_AJAX_Security::validate_ajax_request();
        
        check_ajax_referer( 'ennu_biomarker_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
        }
        
        wp_send_json_success( array( 'message' => 'Biomarker data saved successfully.' ) );
    }
    
    public function handle_get_user_biomarkers() {
        ENNU_AJAX_Security::validate_ajax_request();
        
        check_ajax_referer( 'ennu_biomarker_admin_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions.' ) );
        }
        
        $user_id = intval( $_POST['user_id'] ?? 0 );
        if ( ! $user_id ) {
            wp_send_json_error( array( 'message' => 'Invalid user ID.' ) );
        }
        
        $biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true ) ?: array();
        $doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true ) ?: array();
        
        wp_send_json_success( array(
            'biomarkers' => $biomarker_data,
            'targets' => $doctor_targets
        ) );
    }
}

new ENNU_Biomarker_Admin();
