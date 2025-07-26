# Deployment & DevOps Roadmap - ENNU Life Assessments

## Executive Summary

**Priority**: MEDIUM (Week 21-24)  
**Impact**: High - Reliability, scalability, maintainability  
**Focus**: CI/CD pipeline, environment management, monitoring

Based on comprehensive analysis of deployment requirements and modern DevOps practices, this roadmap establishes a robust deployment and DevOps framework to ensure reliable, scalable, and maintainable plugin deployment.

## DevOps Strategy Overview

### 1. Continuous Integration/Continuous Deployment (CI/CD)
**Goal**: Automated testing and deployment pipeline  
**Tools**: GitHub Actions, WordPress deployment tools  
**Benefits**: Faster releases, reduced errors, improved quality

### 2. Environment Management
**Goal**: Consistent environments across development, staging, and production  
**Tools**: Docker, WordPress multisite, environment-specific configurations  
**Benefits**: Reliable testing, easy rollbacks, consistent deployments

### 3. Monitoring and Logging
**Goal**: Comprehensive monitoring and alerting system  
**Tools**: WordPress monitoring plugins, custom logging, performance tracking  
**Benefits**: Proactive issue detection, performance optimization, user experience monitoring

### 4. Backup and Recovery
**Goal**: Automated backup and disaster recovery system  
**Tools**: WordPress backup plugins, database backups, file system backups  
**Benefits**: Data protection, quick recovery, business continuity

## Implementation Plan

### Week 21-22: CI/CD Pipeline Setup

#### Day 1-2: GitHub Actions Configuration
```yaml
# .github/workflows/ci-cd.yml
name: ENNU Life Assessments CI/CD

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:5.7
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress_test
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, intl, mysql, xml, curl, gd, zip
        coverage: xdebug
    
    - name: Setup WordPress
      run: |
        wp core download --path=wordpress --version=latest
        wp config create --path=wordpress --dbname=wordpress_test --dbuser=root --dbpass=root --dbhost=127.0.0.1
        wp core install --path=wordpress --url=localhost --title="Test Site" --admin_user=admin --admin_password=admin --admin_email=test@example.com
    
    - name: Install dependencies
      run: |
        composer install --no-interaction --prefer-dist --optimize-autoloader
        npm ci
    
    - name: Run PHPUnit tests
      run: |
        cd wordpress
        wp plugin install wordpress-importer --activate
        wp plugin install classic-editor --activate
        cd ..
        vendor/bin/phpunit --coverage-clover=coverage.xml
    
    - name: Run JavaScript tests
      run: npm test
    
    - name: Run security scan
      run: |
        composer require --dev phpstan/phpstan
        vendor/bin/phpstan analyse includes/ --level=8
    
    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        flags: unittests
        name: codecov-umbrella

  build:
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
    
    - name: Install dependencies
      run: |
        composer install --no-dev --optimize-autoloader
        npm ci
    
    - name: Build assets
      run: |
        npm run build
        npm run minify
    
    - name: Create deployment package
      run: |
        mkdir -p deployment
        cp -r includes/ deployment/
        cp -r assets/ deployment/
        cp -r templates/ deployment/
        cp ennu-life-plugin.php deployment/
        cp README.md deployment/
        cp CHANGELOG.md deployment/
        
        # Create zip file
        cd deployment
        zip -r ../ennu-life-assessments.zip .
        cd ..
    
    - name: Upload artifact
      uses: actions/upload-artifact@v3
      with:
        name: ennu-life-assessments
        path: ennu-life-assessments.zip

  deploy-staging:
    needs: build
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    environment: staging
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Download artifact
      uses: actions/download-artifact@v3
      with:
        name: ennu-life-assessments
    
    - name: Deploy to staging
      run: |
        # Deploy to staging server
        echo "Deploying to staging environment"
        # Add deployment commands here
    
    - name: Run staging tests
      run: |
        # Run automated tests on staging
        echo "Running staging tests"
        # Add staging test commands here

  deploy-production:
    needs: [build, deploy-staging]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    environment: production
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Download artifact
      uses: actions/download-artifact@v3
      with:
        name: ennu-life-assessments
    
    - name: Deploy to production
      run: |
        # Deploy to production server
        echo "Deploying to production environment"
        # Add deployment commands here
    
    - name: Run health checks
      run: |
        # Run health checks after deployment
        echo "Running health checks"
        # Add health check commands here
```

**Tasks**:
- [ ] Set up GitHub Actions workflow
- [ ] Configure automated testing
- [ ] Implement build process
- [ ] Add security scanning
- [ ] Create deployment pipeline

#### Day 3-4: WordPress Deployment Tools
```php
<?php
/**
 * Deployment Manager
 *
 * Handles plugin deployment and updates.
 *
 * @since 2.1.0
 */
class ENNU_Deployment_Manager {

    /**
     * Deployment configuration
     *
     * @var array
     */
    private $config;

    /**
     * Constructor
     */
    public function __construct() {
        $this->config = $this->load_deployment_config();
    }

    /**
     * Load deployment configuration
     *
     * @return array Configuration array.
     */
    private function load_deployment_config() {
        return array(
            'version' => ENNU_VERSION,
            'environments' => array(
                'development' => array(
                    'url' => 'http://localhost',
                    'debug' => true,
                    'cache' => false,
                ),
                'staging' => array(
                    'url' => 'https://staging.ennulife.com',
                    'debug' => true,
                    'cache' => true,
                ),
                'production' => array(
                    'url' => 'https://ennulife.com',
                    'debug' => false,
                    'cache' => true,
                ),
            ),
        );
    }

    /**
     * Deploy plugin
     *
     * @param string $environment Target environment.
     * @return bool Success status.
     */
    public function deploy( $environment ) {
        try {
            // Validate environment
            if ( ! isset( $this->config['environments'][ $environment ] ) ) {
                throw new Exception( "Invalid environment: {$environment}" );
            }

            // Pre-deployment checks
            $this->pre_deployment_checks( $environment );

            // Create backup
            $this->create_backup( $environment );

            // Deploy files
            $this->deploy_files( $environment );

            // Update database
            $this->update_database( $environment );

            // Post-deployment checks
            $this->post_deployment_checks( $environment );

            // Clear caches
            $this->clear_caches( $environment );

            return true;

        } catch ( Exception $e ) {
            error_log( "Deployment failed: " . $e->getMessage() );
            $this->rollback( $environment );
            return false;
        }
    }

    /**
     * Pre-deployment checks
     *
     * @param string $environment Target environment.
     */
    private function pre_deployment_checks( $environment ) {
        // Check disk space
        $free_space = disk_free_space( ABSPATH );
        if ( $free_space < 100 * 1024 * 1024 ) { // 100MB
            throw new Exception( 'Insufficient disk space for deployment' );
        }

        // Check database connectivity
        global $wpdb;
        if ( ! $wpdb->check_connection() ) {
            throw new Exception( 'Database connection failed' );
        }

        // Check file permissions
        $plugin_dir = plugin_dir_path( __FILE__ );
        if ( ! is_writable( $plugin_dir ) ) {
            throw new Exception( 'Plugin directory not writable' );
        }
    }

    /**
     * Create backup
     *
     * @param string $environment Target environment.
     */
    private function create_backup( $environment ) {
        $backup_dir = WP_CONTENT_DIR . '/backups/ennu-life-assessments/';
        wp_mkdir_p( $backup_dir );

        $backup_file = $backup_dir . 'backup-' . date( 'Y-m-d-H-i-s' ) . '.zip';
        
        $zip = new ZipArchive();
        if ( $zip->open( $backup_file, ZipArchive::CREATE ) === TRUE ) {
            // Add plugin files
            $plugin_dir = plugin_dir_path( __FILE__ );
            $this->add_directory_to_zip( $zip, $plugin_dir, 'ennu-life-assessments' );
            
            // Add database backup
            $this->add_database_backup( $zip );
            
            $zip->close();
        }
    }

    /**
     * Deploy files
     *
     * @param string $environment Target environment.
     */
    private function deploy_files( $environment ) {
        $source_dir = plugin_dir_path( __FILE__ );
        $target_dir = plugin_dir_path( __FILE__ );

        // Copy files
        $this->copy_directory( $source_dir, $target_dir );

        // Update version
        $this->update_version_file();
    }

    /**
     * Update database
     *
     * @param string $environment Target environment.
     */
    private function update_database( $environment ) {
        // Run database migrations
        $migrations = new ENNU_Database_Migrations();
        $migrations->run_migrations();

        // Update plugin options
        update_option( 'ennu_life_assessments_version', ENNU_VERSION );
        update_option( 'ennu_life_assessments_deployed_at', current_time( 'mysql' ) );
    }

    /**
     * Post-deployment checks
     *
     * @param string $environment Target environment.
     */
    private function post_deployment_checks( $environment ) {
        // Check plugin activation
        if ( ! is_plugin_active( 'ennu-life-assessments/ennu-life-plugin.php' ) ) {
            throw new Exception( 'Plugin not active after deployment' );
        }

        // Check database connectivity
        global $wpdb;
        if ( ! $wpdb->check_connection() ) {
            throw new Exception( 'Database connection failed after deployment' );
        }

        // Check file integrity
        $this->check_file_integrity();
    }

    /**
     * Clear caches
     *
     * @param string $environment Target environment.
     */
    private function clear_caches( $environment ) {
        // Clear WordPress cache
        wp_cache_flush();

        // Clear plugin cache
        if ( class_exists( 'ENNU_Cache_Manager' ) ) {
            $cache_manager = new ENNU_Cache_Manager();
            $cache_manager->clear_all();
        }

        // Clear external caches
        if ( function_exists( 'w3tc_flush_all' ) ) {
            w3tc_flush_all();
        }

        if ( function_exists( 'wp_cache_clear_cache' ) ) {
            wp_cache_clear_cache();
        }
    }

    /**
     * Rollback deployment
     *
     * @param string $environment Target environment.
     */
    private function rollback( $environment ) {
        // Find latest backup
        $backup_dir = WP_CONTENT_DIR . '/backups/ennu-life-assessments/';
        $backups = glob( $backup_dir . 'backup-*.zip' );
        
        if ( ! empty( $backups ) ) {
            $latest_backup = end( $backups );
            $this->restore_from_backup( $latest_backup );
        }
    }
}
```

**Tasks**:
- [ ] Create deployment manager
- [ ] Implement backup system
- [ ] Add rollback functionality
- [ ] Create health checks
- [ ] Add cache management

### Week 23-24: Environment Management and Monitoring

#### Day 1-2: Environment Management
```php
/**
 * Environment Manager
 *
 * Manages different environments and configurations.
 *
 * @since 2.1.0
 */
class ENNU_Environment_Manager {

    /**
     * Current environment
     *
     * @var string
     */
    private $current_environment;

    /**
     * Environment configurations
     *
     * @var array
     */
    private $configurations;

    /**
     * Constructor
     */
    public function __construct() {
        $this->detect_environment();
        $this->load_configurations();
        $this->apply_environment_config();
    }

    /**
     * Detect current environment
     */
    private function detect_environment() {
        if ( defined( 'ENNU_ENVIRONMENT' ) ) {
            $this->current_environment = ENNU_ENVIRONMENT;
        } elseif ( defined( 'WP_ENV' ) ) {
            $this->current_environment = WP_ENV;
        } elseif ( strpos( $_SERVER['HTTP_HOST'], 'localhost' ) !== false ) {
            $this->current_environment = 'development';
        } elseif ( strpos( $_SERVER['HTTP_HOST'], 'staging' ) !== false ) {
            $this->current_environment = 'staging';
        } else {
            $this->current_environment = 'production';
        }
    }

    /**
     * Load environment configurations
     */
    private function load_configurations() {
        $this->configurations = array(
            'development' => array(
                'debug' => true,
                'cache' => false,
                'logging' => true,
                'error_reporting' => E_ALL,
                'database' => array(
                    'prefix' => 'dev_',
                    'backup' => false,
                ),
                'performance' => array(
                    'minify' => false,
                    'compress' => false,
                ),
            ),
            'staging' => array(
                'debug' => true,
                'cache' => true,
                'logging' => true,
                'error_reporting' => E_ALL & ~E_NOTICE,
                'database' => array(
                    'prefix' => 'staging_',
                    'backup' => true,
                ),
                'performance' => array(
                    'minify' => true,
                    'compress' => true,
                ),
            ),
            'production' => array(
                'debug' => false,
                'cache' => true,
                'logging' => false,
                'error_reporting' => 0,
                'database' => array(
                    'prefix' => '',
                    'backup' => true,
                ),
                'performance' => array(
                    'minify' => true,
                    'compress' => true,
                ),
            ),
        );
    }

    /**
     * Apply environment configuration
     */
    private function apply_environment_config() {
        $config = $this->get_current_config();

        // Apply debug settings
        if ( $config['debug'] ) {
            define( 'WP_DEBUG', true );
            define( 'WP_DEBUG_LOG', true );
            define( 'WP_DEBUG_DISPLAY', false );
        } else {
            define( 'WP_DEBUG', false );
            define( 'WP_DEBUG_LOG', false );
            define( 'WP_DEBUG_DISPLAY', false );
        }

        // Apply error reporting
        error_reporting( $config['error_reporting'] );

        // Apply database settings
        $this->apply_database_config( $config['database'] );

        // Apply performance settings
        $this->apply_performance_config( $config['performance'] );
    }

    /**
     * Get current environment configuration
     *
     * @return array Configuration array.
     */
    public function get_current_config() {
        return $this->configurations[ $this->current_environment ];
    }

    /**
     * Get current environment
     *
     * @return string Environment name.
     */
    public function get_current_environment() {
        return $this->current_environment;
    }

    /**
     * Apply database configuration
     *
     * @param array $config Database configuration.
     */
    private function apply_database_config( $config ) {
        global $wpdb;
        
        if ( ! empty( $config['prefix'] ) ) {
            $wpdb->prefix = $config['prefix'];
        }
    }

    /**
     * Apply performance configuration
     *
     * @param array $config Performance configuration.
     */
    private function apply_performance_config( $config ) {
        if ( $config['minify'] ) {
            add_filter( 'ennu_minify_assets', '__return_true' );
        }

        if ( $config['compress'] ) {
            add_filter( 'ennu_compress_output', '__return_true' );
        }
    }
}

/**
 * Docker Configuration
 *
 * @since 2.1.0
 */
class ENNU_Docker_Manager {

    /**
     * Generate Docker configuration
     */
    public function generate_docker_config() {
        $docker_compose = $this->get_docker_compose_config();
        $dockerfile = $this->get_dockerfile_config();
        
        // Write docker-compose.yml
        file_put_contents( 'docker-compose.yml', $docker_compose );
        
        // Write Dockerfile
        file_put_contents( 'Dockerfile', $dockerfile );
    }

    /**
     * Get Docker Compose configuration
     *
     * @return string Docker Compose YAML.
     */
    private function get_docker_compose_config() {
        return <<<YAML
version: '3.8'

services:
  wordpress:
    build: .
    ports:
      - "8000:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
      WORDPRESS_DEBUG: 1
    volumes:
      - ./wordpress:/var/www/html
      - ./ennu-life-assessments:/var/www/html/wp-content/plugins/ennu-life-assessments
    depends_on:
      - db

  db:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: somewordpress
    volumes:
      - db_data:/var/lib/mysql

volumes:
  db_data: {}
YAML;
    }

    /**
     * Get Dockerfile configuration
     *
     * @return string Dockerfile content.
     */
    private function get_dockerfile_config() {
        return <<<DOCKERFILE
FROM wordpress:latest

# Install additional PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy plugin files
COPY . /var/www/html/wp-content/plugins/ennu-life-assessments/

# Install dependencies
RUN cd /var/www/html/wp-content/plugins/ennu-life-assessments && \
    composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/wp-content/plugins/ennu-life-assessments

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
DOCKERFILE;
    }
}
```

**Tasks**:
- [ ] Create environment manager
- [ ] Implement Docker configuration
- [ ] Add environment-specific settings
- [ ] Create development environment
- [ ] Add staging environment

#### Day 3-4: Monitoring and Logging
```php
/**
 * Monitoring and Logging System
 *
 * Handles monitoring, logging, and alerting.
 *
 * @since 2.1.0
 */
class ENNU_Monitoring_System {

    /**
     * Monitoring configuration
     *
     * @var array
     */
    private $config;

    /**
     * Constructor
     */
    public function __construct() {
        $this->config = $this->load_monitoring_config();
        $this->init_monitoring();
    }

    /**
     * Load monitoring configuration
     *
     * @return array Configuration array.
     */
    private function load_monitoring_config() {
        return array(
            'enabled' => true,
            'log_level' => 'info',
            'performance_monitoring' => true,
            'error_monitoring' => true,
            'security_monitoring' => true,
            'alerts' => array(
                'email' => get_option( 'admin_email' ),
                'slack' => get_option( 'ennu_slack_webhook' ),
            ),
        );
    }

    /**
     * Initialize monitoring
     */
    private function init_monitoring() {
        if ( ! $this->config['enabled'] ) {
            return;
        }

        // Performance monitoring
        if ( $this->config['performance_monitoring'] ) {
            add_action( 'wp_footer', array( $this, 'log_performance_metrics' ) );
            add_action( 'admin_footer', array( $this, 'log_performance_metrics' ) );
        }

        // Error monitoring
        if ( $this->config['error_monitoring'] ) {
            set_error_handler( array( $this, 'handle_error' ) );
            register_shutdown_function( array( $this, 'handle_fatal_error' ) );
        }

        // Security monitoring
        if ( $this->config['security_monitoring'] ) {
            add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );
            add_action( 'wp_login', array( $this, 'log_successful_login' ) );
        }
    }

    /**
     * Log performance metrics
     */
    public function log_performance_metrics() {
        $metrics = array(
            'page_load_time' => $this->get_page_load_time(),
            'memory_usage' => memory_get_usage( true ),
            'peak_memory' => memory_get_peak_usage( true ),
            'database_queries' => $this->get_database_query_count(),
            'url' => $_SERVER['REQUEST_URI'],
            'timestamp' => current_time( 'mysql' ),
        );

        $this->log( 'performance', $metrics );
    }

    /**
     * Handle error
     *
     * @param int    $errno Error number.
     * @param string $errstr Error string.
     * @param string $errfile Error file.
     * @param int    $errline Error line.
     */
    public function handle_error( $errno, $errstr, $errfile, $errline ) {
        $error_data = array(
            'type' => 'error',
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'url' => $_SERVER['REQUEST_URI'],
            'timestamp' => current_time( 'mysql' ),
        );

        $this->log( 'error', $error_data );

        // Send alert for critical errors
        if ( $errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR ) {
            $this->send_alert( 'Critical Error', $error_data );
        }
    }

    /**
     * Handle fatal error
     */
    public function handle_fatal_error() {
        $error = error_get_last();
        
        if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR ) ) ) {
            $error_data = array(
                'type' => 'fatal_error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'url' => $_SERVER['REQUEST_URI'],
                'timestamp' => current_time( 'mysql' ),
            );

            $this->log( 'fatal_error', $error_data );
            $this->send_alert( 'Fatal Error', $error_data );
        }
    }

    /**
     * Log failed login
     *
     * @param string $username Username.
     */
    public function log_failed_login( $username ) {
        $security_data = array(
            'type' => 'failed_login',
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => current_time( 'mysql' ),
        );

        $this->log( 'security', $security_data );

        // Check for brute force attempts
        $this->check_brute_force_attempts( $username );
    }

    /**
     * Log successful login
     *
     * @param string $username Username.
     */
    public function log_successful_login( $username ) {
        $security_data = array(
            'type' => 'successful_login',
            'username' => $username,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => current_time( 'mysql' ),
        );

        $this->log( 'security', $security_data );
    }

    /**
     * Log message
     *
     * @param string $level Log level.
     * @param array  $data Log data.
     */
    private function log( $level, $data ) {
        $log_file = WP_CONTENT_DIR . '/logs/ennu-life-assessments/' . $level . '.log';
        wp_mkdir_p( dirname( $log_file ) );

        $log_entry = date( 'Y-m-d H:i:s' ) . ' - ' . json_encode( $data ) . PHP_EOL;
        file_put_contents( $log_file, $log_entry, FILE_APPEND | LOCK_EX );
    }

    /**
     * Send alert
     *
     * @param string $title Alert title.
     * @param array  $data Alert data.
     */
    private function send_alert( $title, $data ) {
        // Email alert
        if ( ! empty( $this->config['alerts']['email'] ) ) {
            $this->send_email_alert( $title, $data );
        }

        // Slack alert
        if ( ! empty( $this->config['alerts']['slack'] ) ) {
            $this->send_slack_alert( $title, $data );
        }
    }

    /**
     * Send email alert
     *
     * @param string $title Alert title.
     * @param array  $data Alert data.
     */
    private function send_email_alert( $title, $data ) {
        $to = $this->config['alerts']['email'];
        $subject = 'ENNU Life Assessments Alert: ' . $title;
        $message = $this->format_alert_message( $title, $data );
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Send Slack alert
     *
     * @param string $title Alert title.
     * @param array  $data Alert data.
     */
    private function send_slack_alert( $title, $data ) {
        $webhook_url = $this->config['alerts']['slack'];
        $message = $this->format_slack_message( $title, $data );

        wp_remote_post( $webhook_url, array(
            'body' => json_encode( $message ),
            'headers' => array( 'Content-Type: application/json' ),
        ) );
    }

    /**
     * Get page load time
     *
     * @return float Load time in seconds.
     */
    private function get_page_load_time() {
        if ( defined( 'ENNU_START_TIME' ) ) {
            return microtime( true ) - ENNU_START_TIME;
        }
        return 0;
    }

    /**
     * Get database query count
     *
     * @return int Query count.
     */
    private function get_database_query_count() {
        global $wpdb;
        return $wpdb->num_queries;
    }

    /**
     * Check brute force attempts
     *
     * @param string $username Username.
     */
    private function check_brute_force_attempts( $username ) {
        $failed_attempts = get_transient( 'ennu_failed_logins_' . $username );
        
        if ( $failed_attempts === false ) {
            $failed_attempts = 1;
        } else {
            $failed_attempts++;
        }

        set_transient( 'ennu_failed_logins_' . $username, $failed_attempts, 300 ); // 5 minutes

        if ( $failed_attempts >= 5 ) {
            $this->send_alert( 'Brute Force Attempt Detected', array(
                'username' => $username,
                'attempts' => $failed_attempts,
                'ip' => $_SERVER['REMOTE_ADDR'],
            ) );
        }
    }
}
```

**Tasks**:
- [ ] Implement monitoring system
- [ ] Add performance tracking
- [ ] Create error monitoring
- [ ] Add security monitoring
- [ ] Implement alerting system

## DevOps Checklist

### CI/CD Pipeline
- [ ] GitHub Actions workflow configured
- [ ] Automated testing implemented
- [ ] Build process automated
- [ ] Deployment pipeline created
- [ ] Security scanning integrated

### Environment Management
- [ ] Development environment configured
- [ ] Staging environment set up
- [ ] Production environment optimized
- [ ] Docker configuration created
- [ ] Environment-specific settings applied

### Monitoring and Logging
- [ ] Performance monitoring implemented
- [ ] Error monitoring configured
- [ ] Security monitoring active
- [ ] Logging system operational
- [ ] Alerting system configured

### Backup and Recovery
- [ ] Automated backup system
- [ ] Database backup configured
- [ ] File system backup active
- [ ] Recovery procedures documented
- [ ] Disaster recovery plan

## Success Metrics

- **Deployment Success Rate**: 95%+ successful deployments
- **Rollback Time**: <5 minutes for rollback
- **Monitoring Coverage**: 100% of critical systems
- **Alert Response Time**: <15 minutes for critical alerts
- **Backup Success Rate**: 100% backup success rate

## Tools and Resources

### CI/CD Tools
- GitHub Actions for automation
- PHPUnit for testing
- Composer for dependency management
- WordPress deployment tools

### Monitoring Tools
- Custom monitoring system
- WordPress monitoring plugins
- Performance tracking tools
- Security monitoring tools

### Infrastructure Tools
- Docker for containerization
- WordPress multisite for environments
- Backup and recovery tools
- Monitoring and alerting tools

---

*This roadmap establishes a comprehensive DevOps framework to ensure reliable, scalable, and maintainable plugin deployment and operation.* 