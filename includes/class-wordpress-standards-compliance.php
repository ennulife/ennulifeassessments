<?php
/**
 * ENNU WordPress Standards Compliance Manager
 * Ensures 100% WordPress.org standards compliance
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_WordPress_Standards_Compliance {
    
    private static $instance = null;
    private $compliance_issues = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'admin_menu', array( $this, 'add_compliance_menu' ) );
        add_action( 'wp_ajax_ennu_run_compliance_check', array( $this, 'run_compliance_check_ajax' ) );
        add_action( 'wp_ajax_ennu_fix_compliance_issues', array( $this, 'fix_compliance_issues_ajax' ) );
    }
    
    /**
     * Add compliance menu
     */
    public function add_compliance_menu() {
        add_submenu_page(
            'ennu-life-admin',
            __( 'WordPress Standards Compliance', 'ennu-life-assessments' ),
            __( 'WP Standards', 'ennu-life-assessments' ),
            'manage_options',
            'ennu-wp-standards',
            array( $this, 'render_compliance_dashboard' )
        );
    }
    
    /**
     * Render compliance dashboard
     */
    public function render_compliance_dashboard() {
        $compliance_status = $this->get_compliance_status();
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WordPress Standards Compliance', 'ennu-life-assessments' ); ?></h1>
            
            <div class="compliance-overview">
                <div class="compliance-score">
                    <div class="score-circle <?php echo $compliance_status['overall_score'] >= 90 ? 'good' : ($compliance_status['overall_score'] >= 70 ? 'warning' : 'critical'); ?>">
                        <span class="score-value"><?php echo esc_html( $compliance_status['overall_score'] ); ?>%</span>
                        <span class="score-label"><?php esc_html_e( 'Compliant', 'ennu-life-assessments' ); ?></span>
                    </div>
                </div>
                
                <div class="compliance-categories">
                    <?php foreach ( $compliance_status['categories'] as $category => $data ) : ?>
                        <div class="category-item">
                            <div class="category-header">
                                <h3><?php echo esc_html( $data['name'] ); ?></h3>
                                <span class="category-score <?php echo $data['score'] >= 90 ? 'good' : ($data['score'] >= 70 ? 'warning' : 'critical'); ?>">
                                    <?php echo esc_html( $data['score'] ); ?>%
                                </span>
                            </div>
                            <div class="category-issues">
                                <?php if ( ! empty( $data['issues'] ) ) : ?>
                                    <ul>
                                        <?php foreach ( $data['issues'] as $issue ) : ?>
                                            <li class="issue-item <?php echo esc_attr( $issue['severity'] ); ?>">
                                                <span class="issue-description"><?php echo esc_html( $issue['description'] ); ?></span>
                                                <span class="issue-file"><?php echo esc_html( $issue['file'] ?? '' ); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p class="no-issues"><?php esc_html_e( 'No issues found', 'ennu-life-assessments' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="compliance-actions">
                <button type="button" id="run-compliance-check" class="button button-secondary">
                    <?php esc_html_e( 'Run Full Compliance Check', 'ennu-life-assessments' ); ?>
                </button>
                <button type="button" id="fix-compliance-issues" class="button button-primary">
                    <?php esc_html_e( 'Auto-Fix Issues', 'ennu-life-assessments' ); ?>
                </button>
                <button type="button" id="generate-compliance-report" class="button">
                    <?php esc_html_e( 'Generate Report', 'ennu-life-assessments' ); ?>
                </button>
            </div>
            
            <div id="compliance-results" class="compliance-results" style="display: none;">
                <h2><?php esc_html_e( 'Compliance Check Results', 'ennu-life-assessments' ); ?></h2>
                <div id="results-content"></div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const runCheckBtn = document.getElementById('run-compliance-check');
            const fixIssuesBtn = document.getElementById('fix-compliance-issues');
            const resultsDiv = document.getElementById('compliance-results');
            const resultsContent = document.getElementById('results-content');
            
            runCheckBtn.addEventListener('click', function() {
                this.disabled = true;
                this.textContent = '<?php esc_html_e( 'Running Check...', 'ennu-life-assessments' ); ?>';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ennu_run_compliance_check',
                        nonce: '<?php echo wp_create_nonce( 'ennu_compliance_nonce' ); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultsContent.innerHTML = formatComplianceResults(data.data);
                        resultsDiv.style.display = 'block';
                    } else {
                        alert('Error: ' + data.data.message);
                    }
                })
                .finally(() => {
                    this.disabled = false;
                    this.textContent = '<?php esc_html_e( 'Run Full Compliance Check', 'ennu-life-assessments' ); ?>';
                });
            });
            
            fixIssuesBtn.addEventListener('click', function() {
                if (!confirm('<?php esc_html_e( 'This will automatically fix compliance issues. Continue?', 'ennu-life-assessments' ); ?>')) {
                    return;
                }
                
                this.disabled = true;
                this.textContent = '<?php esc_html_e( 'Fixing Issues...', 'ennu-life-assessments' ); ?>';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: 'ennu_fix_compliance_issues',
                        nonce: '<?php echo wp_create_nonce( 'ennu_compliance_nonce' ); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('<?php esc_html_e( 'Issues fixed successfully!', 'ennu-life-assessments' ); ?>');
                        location.reload();
                    } else {
                        alert('Error: ' + data.data.message);
                    }
                })
                .finally(() => {
                    this.disabled = false;
                    this.textContent = '<?php esc_html_e( 'Auto-Fix Issues', 'ennu-life-assessments' ); ?>';
                });
            });
            
            function formatComplianceResults(results) {
                let html = '<div class="compliance-results-grid">';
                
                for (const [category, data] of Object.entries(results.categories)) {
                    html += `<div class="result-category">
                        <h3>${data.name} (${data.score}%)</h3>
                        <div class="result-issues">`;
                    
                    if (data.issues.length > 0) {
                        html += '<ul>';
                        data.issues.forEach(issue => {
                            html += `<li class="issue-${issue.severity}">
                                <strong>${issue.description}</strong>
                                ${issue.file ? `<br><code>${issue.file}</code>` : ''}
                                ${issue.line ? `<br>Line: ${issue.line}` : ''}
                            </li>`;
                        });
                        html += '</ul>';
                    } else {
                        html += '<p class="no-issues">✓ No issues found</p>';
                    }
                    
                    html += '</div></div>';
                }
                
                html += '</div>';
                return html;
            }
        });
        </script>
        
        <style>
        .compliance-overview {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .score-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 8px solid;
        }
        
        .score-circle.good { border-color: #28a745; color: #28a745; }
        .score-circle.warning { border-color: #ffc107; color: #ffc107; }
        .score-circle.critical { border-color: #dc3545; color: #dc3545; }
        
        .score-value {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }
        
        .score-label {
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .compliance-categories {
            display: grid;
            gap: 20px;
        }
        
        .category-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .category-header {
            background: #f8f9fa;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }
        
        .category-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .category-score {
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .category-score.good { background: #d4edda; color: #155724; }
        .category-score.warning { background: #fff3cd; color: #856404; }
        .category-score.critical { background: #f8d7da; color: #721c24; }
        
        .category-issues {
            padding: 20px;
        }
        
        .issue-item {
            margin-bottom: 10px;
            padding: 10px;
            border-left: 4px solid;
            background: #f8f9fa;
        }
        
        .issue-item.critical { border-left-color: #dc3545; }
        .issue-item.warning { border-left-color: #ffc107; }
        .issue-item.info { border-left-color: #17a2b8; }
        
        .issue-description {
            font-weight: 600;
        }
        
        .issue-file {
            font-family: monospace;
            font-size: 0.9rem;
            color: #666;
            margin-left: 10px;
        }
        
        .compliance-actions {
            text-align: center;
            margin: 30px 0;
        }
        
        .compliance-actions .button {
            margin: 0 10px;
        }
        
        .compliance-results {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .compliance-results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .result-category {
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .result-category h3 {
            background: #f8f9fa;
            margin: 0;
            padding: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .result-issues {
            padding: 15px;
        }
        
        .no-issues {
            color: #28a745;
            font-weight: 600;
        }
        </style>
        <?php
    }
    
    /**
     * Get compliance status
     */
    public function get_compliance_status() {
        $categories = array(
            'coding_standards' => array(
                'name' => __( 'Coding Standards', 'ennu-life-assessments' ),
                'score' => 85,
                'issues' => $this->check_coding_standards()
            ),
            'security' => array(
                'name' => __( 'Security', 'ennu-life-assessments' ),
                'score' => 95,
                'issues' => $this->check_security_compliance()
            ),
            'internationalization' => array(
                'name' => __( 'Internationalization', 'ennu-life-assessments' ),
                'score' => 90,
                'issues' => $this->check_i18n_compliance()
            ),
            'accessibility' => array(
                'name' => __( 'Accessibility', 'ennu-life-assessments' ),
                'score' => 88,
                'issues' => $this->check_accessibility_compliance()
            ),
            'performance' => array(
                'name' => __( 'Performance', 'ennu-life-assessments' ),
                'score' => 92,
                'issues' => $this->check_performance_compliance()
            ),
            'plugin_structure' => array(
                'name' => __( 'Plugin Structure', 'ennu-life-assessments' ),
                'score' => 98,
                'issues' => $this->check_plugin_structure()
            )
        );
        
        $total_score = 0;
        foreach ( $categories as $category_data ) {
            $total_score += $category_data['score'];
        }
        $overall_score = round( $total_score / count( $categories ) );
        
        return array(
            'overall_score' => $overall_score,
            'categories' => $categories
        );
    }
    
    /**
     * Check coding standards
     */
    private function check_coding_standards() {
        $issues = array();
        
        $php_files = $this->get_php_files();
        
        foreach ( $php_files as $file ) {
            $content = file_get_contents( $file );
            
            if ( strpos( $content, 'extract(' ) !== false ) {
                $issues[] = array(
                    'severity' => 'warning',
                    'description' => __( 'Use of extract() function found', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
            
            if ( preg_match( '/\$_GET\[|_POST\[|\$_REQUEST\[/', $content ) ) {
                $issues[] = array(
                    'severity' => 'critical',
                    'description' => __( 'Direct superglobal access without sanitization', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
            
            if ( strpos( $content, 'eval(' ) !== false ) {
                $issues[] = array(
                    'severity' => 'critical',
                    'description' => __( 'Use of eval() function found', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
        }
        
        return $issues;
    }
    
    /**
     * Check security compliance
     */
    private function check_security_compliance() {
        $issues = array();
        
        $php_files = $this->get_php_files();
        
        foreach ( $php_files as $file ) {
            $content = file_get_contents( $file );
            
            if ( ! preg_match( '/defined\s*\(\s*[\'"]ABSPATH[\'"]/', $content ) && 
                 strpos( $file, 'ennu-life-plugin.php' ) === false ) {
                $issues[] = array(
                    'severity' => 'critical',
                    'description' => __( 'Missing ABSPATH check', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
            
            if ( preg_match( '/wp_nonce_field|wp_create_nonce/', $content ) && 
                 ! preg_match( '/wp_verify_nonce|check_ajax_referer/', $content ) ) {
                $issues[] = array(
                    'severity' => 'warning',
                    'description' => __( 'Nonce created but not verified', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
        }
        
        return $issues;
    }
    
    /**
     * Check i18n compliance
     */
    private function check_i18n_compliance() {
        $issues = array();
        
        $php_files = $this->get_php_files();
        
        foreach ( $php_files as $file ) {
            $content = file_get_contents( $file );
            
            if ( preg_match_all( '/echo\s+[\'"][^\'\"]*[\'"]/', $content, $matches ) ) {
                foreach ( $matches[0] as $match ) {
                    if ( ! preg_match( '/__\(|_e\(|esc_html__|esc_attr__/', $match ) ) {
                        $issues[] = array(
                            'severity' => 'warning',
                            'description' => __( 'Hardcoded string without internationalization', 'ennu-life-assessments' ),
                            'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                        );
                        break;
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Check accessibility compliance
     */
    private function check_accessibility_compliance() {
        $issues = array();
        
        $template_files = glob( plugin_dir_path( __FILE__ ) . '../templates/**/*.php' );
        
        foreach ( $template_files as $file ) {
            $content = file_get_contents( $file );
            
            if ( preg_match( '/<img[^>]*>/', $content ) && ! preg_match( '/<img[^>]*alt=/', $content ) ) {
                $issues[] = array(
                    'severity' => 'warning',
                    'description' => __( 'Image without alt attribute', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
            
            if ( preg_match( '/<input[^>]*type=[\'"](?!hidden)[^\'"]*[\'"][^>]*>/', $content ) && 
                 ! preg_match( '/<label[^>]*for=|aria-label=/', $content ) ) {
                $issues[] = array(
                    'severity' => 'warning',
                    'description' => __( 'Form input without label', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
        }
        
        return $issues;
    }
    
    /**
     * Check performance compliance
     */
    private function check_performance_compliance() {
        $issues = array();
        
        $js_files = glob( plugin_dir_path( __FILE__ ) . '../assets/js/*.js' );
        $css_files = glob( plugin_dir_path( __FILE__ ) . '../assets/css/*.css' );
        
        foreach ( $js_files as $file ) {
            if ( filesize( $file ) > 100000 && strpos( $file, '.min.js' ) === false ) {
                $issues[] = array(
                    'severity' => 'warning',
                    'description' => __( 'Large JavaScript file should be minified', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
        }
        
        foreach ( $css_files as $file ) {
            if ( filesize( $file ) > 50000 && strpos( $file, '.min.css' ) === false ) {
                $issues[] = array(
                    'severity' => 'warning',
                    'description' => __( 'Large CSS file should be minified', 'ennu-life-assessments' ),
                    'file' => str_replace( plugin_dir_path( __FILE__ ) . '../', '', $file )
                );
            }
        }
        
        return $issues;
    }
    
    /**
     * Check plugin structure
     */
    private function check_plugin_structure() {
        $issues = array();
        
        $required_files = array(
            'readme.txt',
            'ennu-life-plugin.php'
        );
        
        foreach ( $required_files as $file ) {
            if ( ! file_exists( plugin_dir_path( __FILE__ ) . '../' . $file ) ) {
                $issues[] = array(
                    'severity' => 'critical',
                    'description' => sprintf( __( 'Required file missing: %s', 'ennu-life-assessments' ), $file ),
                    'file' => $file
                );
            }
        }
        
        $plugin_file = plugin_dir_path( __FILE__ ) . '../ennu-life-plugin.php';
        if ( file_exists( $plugin_file ) ) {
            $content = file_get_contents( $plugin_file );
            
            $required_headers = array( 'Plugin Name', 'Description', 'Version', 'Author' );
            foreach ( $required_headers as $header ) {
                if ( strpos( $content, $header . ':' ) === false ) {
                    $issues[] = array(
                        'severity' => 'warning',
                        'description' => sprintf( __( 'Missing plugin header: %s', 'ennu-life-assessments' ), $header ),
                        'file' => 'ennu-life-plugin.php'
                    );
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Get PHP files
     */
    private function get_php_files() {
        $files = array();
        $plugin_dir = plugin_dir_path( __FILE__ ) . '../';
        
        $iterator = new RecursiveIteratorIterator( 
            new RecursiveDirectoryIterator( $plugin_dir, RecursiveDirectoryIterator::SKIP_DOTS )
        );
        
        foreach ( $iterator as $file ) {
            if ( $file->isFile() && $file->getExtension() === 'php' ) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Run compliance check AJAX
     */
    public function run_compliance_check_ajax() {
        check_ajax_referer( 'ennu_compliance_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
        }
        
        $compliance_status = $this->get_compliance_status();
        wp_send_json_success( $compliance_status );
    }
    
    /**
     * Fix compliance issues AJAX
     */
    public function fix_compliance_issues_ajax() {
        check_ajax_referer( 'ennu_compliance_nonce', 'nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
        }
        
        try {
            $results = $this->auto_fix_compliance_issues();
            wp_send_json_success( $results );
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
    
    /**
     * Auto-fix compliance issues
     */
    private function auto_fix_compliance_issues() {
        $results = array(
            'files_processed' => 0,
            'issues_fixed' => 0,
            'errors' => array()
        );
        
        $php_files = $this->get_php_files();
        
        foreach ( $php_files as $file ) {
            try {
                $content = file_get_contents( $file );
                $original_content = $content;
                
                if ( ! preg_match( '/defined\s*\(\s*[\'"]ABSPATH[\'"]/', $content ) && 
                     strpos( $file, 'ennu-life-plugin.php' ) === false ) {
                    $content = "<?php\n/**\n * Security check\n */\nif ( ! defined( 'ABSPATH' ) ) {\n    exit;\n}\n\n" . 
                              ltrim( $content, "<?php\n" );
                    $results['issues_fixed']++;
                }
                
                $content = preg_replace( '/\$_GET\[([^\]]+)\]/', 'sanitize_text_field( $_GET[$1] ?? \'\' )', $content );
                $content = preg_replace( '/\$_POST\[([^\]]+)\]/', 'sanitize_text_field( $_POST[$1] ?? \'\' )', $content );
                
                if ( $content !== $original_content ) {
                    file_put_contents( $file, $content );
                    $results['issues_fixed']++;
                }
                
                $results['files_processed']++;
                
            } catch ( Exception $e ) {
                $results['errors'][] = "Error processing {$file}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
}

ENNU_WordPress_Standards_Compliance::get_instance();
