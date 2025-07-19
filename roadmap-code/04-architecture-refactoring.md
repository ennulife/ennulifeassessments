# Architecture Refactoring Roadmap - ENNU Life Assessments

## Executive Summary

**Priority**: MEDIUM (Week 7-12)  
**Impact**: High - Code maintainability, scalability, performance  
**Current Issues**: Significant technical debt, poor separation of concerns, tight coupling

Based on comprehensive code analysis, the plugin architecture requires significant refactoring to improve maintainability, scalability, and adherence to modern development practices.

## Architecture Issues Identified

### 1. Class Structure Problems (HIGH)
**Files Affected**:
- All classes in `includes/`
- Main plugin file
- Template files

**Issues**:
- Monolithic classes with multiple responsibilities
- Tight coupling between components
- No dependency injection
- Poor separation of concerns

**Fix Priority**: HIGH
**Estimated Time**: 4-5 days

### 2. Service Layer Missing (HIGH)
**Files Affected**:
- All business logic classes
- Database operations
- Assessment processing

**Issues**:
- Business logic mixed with presentation logic
- No service layer abstraction
- Direct database access in controllers
- No transaction management

**Fix Priority**: HIGH
**Estimated Time**: 3-4 days

### 3. Configuration Management (MEDIUM)
**Files Affected**:
- Configuration files in `includes/config/`
- Assessment definitions
- Scoring algorithms

**Issues**:
- Hardcoded configuration values
- No centralized configuration management
- Difficult to modify settings
- No environment-specific configs

**Fix Priority**: MEDIUM
**Estimated Time**: 2-3 days

### 4. API Architecture (MEDIUM)
**Files Affected**:
- AJAX handlers
- External integrations
- Data exchange

**Issues**:
- No REST API structure
- Inconsistent data formats
- No API versioning
- Poor error handling

**Fix Priority**: MEDIUM
**Estimated Time**: 3-4 days

## Implementation Plan

### Week 7-8: Class Structure Refactoring

#### Day 1-2: Dependency Injection Implementation
```php
<?php
/**
 * Dependency Injection Container
 *
 * @since 2.1.0
 */
class ENNU_Container {

    /**
     * Container instance
     *
     * @var ENNU_Container
     */
    private static $instance = null;

    /**
     * Registered services
     *
     * @var array
     */
    private $services = array();

    /**
     * Get container instance
     *
     * @return ENNU_Container
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a service
     *
     * @param string   $name Service name.
     * @param callable $factory Service factory function.
     */
    public function register( $name, $factory ) {
        $this->services[ $name ] = $factory;
    }

    /**
     * Get a service
     *
     * @param string $name Service name.
     * @return mixed Service instance.
     */
    public function get( $name ) {
        if ( ! isset( $this->services[ $name ] ) ) {
            throw new Exception( "Service '{$name}' not found" );
        }

        $factory = $this->services[ $name ];
        return $factory( $this );
    }

    /**
     * Register core services
     */
    public function register_core_services() {
        // Database service
        $this->register( 'database', function( $container ) {
            return new ENNU_Database_Service();
        } );

        // Assessment service
        $this->register( 'assessment', function( $container ) {
            return new ENNU_Assessment_Service( $container->get( 'database' ) );
        } );

        // Scoring service
        $this->register( 'scoring', function( $container ) {
            return new ENNU_Scoring_Service( $container->get( 'database' ) );
        } );

        // Configuration service
        $this->register( 'config', function( $container ) {
            return new ENNU_Config_Service();
        } );
    }
}

/**
 * Base Service Class
 *
 * @since 2.1.0
 */
abstract class ENNU_Base_Service {

    /**
     * Container instance
     *
     * @var ENNU_Container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ENNU_Container $container Dependency container.
     */
    public function __construct( ENNU_Container $container ) {
        $this->container = $container;
    }

    /**
     * Get service from container
     *
     * @param string $name Service name.
     * @return mixed Service instance.
     */
    protected function get_service( $name ) {
        return $this->container->get( $name );
    }
}
```

**Tasks**:
- [ ] Implement dependency injection container
- [ ] Refactor classes to use dependency injection
- [ ] Remove tight coupling between classes
- [ ] Implement service interfaces
- [ ] Add proper error handling

#### Day 3-4: Service Layer Implementation
```php
/**
 * Assessment Service
 *
 * Handles all assessment-related business logic.
 *
 * @since 2.1.0
 */
class ENNU_Assessment_Service extends ENNU_Base_Service {

    /**
     * Database service
     *
     * @var ENNU_Database_Service
     */
    private $database;

    /**
     * Configuration service
     *
     * @var ENNU_Config_Service
     */
    private $config;

    /**
     * Constructor
     *
     * @param ENNU_Database_Service $database Database service.
     * @param ENNU_Config_Service   $config Configuration service.
     */
    public function __construct( ENNU_Database_Service $database, ENNU_Config_Service $config ) {
        $this->database = $database;
        $this->config   = $config;
    }

    /**
     * Create new assessment
     *
     * @param array $data Assessment data.
     * @return ENNU_Assessment Assessment object.
     * @throws ENNU_Assessment_Exception If creation fails.
     */
    public function create_assessment( $data ) {
        try {
            // Validate assessment data
            $this->validate_assessment_data( $data );

            // Create assessment object
            $assessment = new ENNU_Assessment( $data );

            // Save to database
            $assessment_id = $this->database->save_assessment( $assessment );

            if ( ! $assessment_id ) {
                throw new ENNU_Assessment_Exception( 'Failed to save assessment' );
            }

            $assessment->set_id( $assessment_id );

            // Trigger hooks
            do_action( 'ennu_assessment_created', $assessment );

            return $assessment;

        } catch ( Exception $e ) {
            throw new ENNU_Assessment_Exception( 'Assessment creation failed: ' . $e->getMessage() );
        }
    }

    /**
     * Get user assessments
     *
     * @param int $user_id User ID.
     * @param array $filters Assessment filters.
     * @return array Array of assessment objects.
     */
    public function get_user_assessments( $user_id, $filters = array() ) {
        $user_id = intval( $user_id );

        // Apply default filters
        $filters = wp_parse_args( $filters, array(
            'status' => 'active',
            'limit'  => 10,
            'offset' => 0,
        ) );

        // Get assessments from database
        $assessments_data = $this->database->get_user_assessments( $user_id, $filters );

        // Convert to assessment objects
        $assessments = array();
        foreach ( $assessments_data as $data ) {
            $assessments[] = new ENNU_Assessment( $data );
        }

        return $assessments;
    }

    /**
     * Process assessment submission
     *
     * @param array $submission_data Submission data.
     * @return ENNU_Assessment_Result Assessment result.
     */
    public function process_assessment_submission( $submission_data ) {
        try {
            // Validate submission
            $this->validate_submission_data( $submission_data );

            // Create assessment
            $assessment = $this->create_assessment( $submission_data );

            // Get scoring service
            $scoring_service = $this->get_service( 'scoring' );

            // Calculate scores
            $scores = $scoring_service->calculate_scores( $assessment );

            // Create result object
            $result = new ENNU_Assessment_Result( $assessment, $scores );

            // Save results
            $this->database->save_assessment_results( $result );

            // Trigger hooks
            do_action( 'ennu_assessment_processed', $result );

            return $result;

        } catch ( Exception $e ) {
            throw new ENNU_Assessment_Exception( 'Assessment processing failed: ' . $e->getMessage() );
        }
    }

    /**
     * Validate assessment data
     *
     * @param array $data Assessment data.
     * @throws ENNU_Assessment_Exception If validation fails.
     */
    private function validate_assessment_data( $data ) {
        $required_fields = array( 'user_id', 'type', 'data' );

        foreach ( $required_fields as $field ) {
            if ( ! isset( $data[ $field ] ) ) {
                throw new ENNU_Assessment_Exception( "Missing required field: {$field}" );
            }
        }

        // Validate assessment type
        $valid_types = $this->config->get( 'assessment_types' );
        if ( ! in_array( $data['type'], $valid_types, true ) ) {
            throw new ENNU_Assessment_Exception( "Invalid assessment type: {$data['type']}" );
        }
    }
}

/**
 * Assessment Entity Class
 *
 * @since 2.1.0
 */
class ENNU_Assessment {

    /**
     * Assessment ID
     *
     * @var int
     */
    private $id;

    /**
     * User ID
     *
     * @var int
     */
    private $user_id;

    /**
     * Assessment type
     *
     * @var string
     */
    private $type;

    /**
     * Assessment data
     *
     * @var array
     */
    private $data;

    /**
     * Assessment status
     *
     * @var string
     */
    private $status;

    /**
     * Created date
     *
     * @var string
     */
    private $created_at;

    /**
     * Constructor
     *
     * @param array $data Assessment data.
     */
    public function __construct( $data = array() ) {
        $this->hydrate( $data );
    }

    /**
     * Hydrate object with data
     *
     * @param array $data Assessment data.
     */
    private function hydrate( $data ) {
        if ( isset( $data['id'] ) ) {
            $this->id = intval( $data['id'] );
        }
        if ( isset( $data['user_id'] ) ) {
            $this->user_id = intval( $data['user_id'] );
        }
        if ( isset( $data['type'] ) ) {
            $this->type = sanitize_text_field( $data['type'] );
        }
        if ( isset( $data['data'] ) ) {
            $this->data = $data['data'];
        }
        if ( isset( $data['status'] ) ) {
            $this->status = sanitize_text_field( $data['status'] );
        }
        if ( isset( $data['created_at'] ) ) {
            $this->created_at = $data['created_at'];
        }
    }

    // Getters and setters
    public function get_id() { return $this->id; }
    public function set_id( $id ) { $this->id = intval( $id ); }
    public function get_user_id() { return $this->user_id; }
    public function get_type() { return $this->type; }
    public function get_data() { return $this->data; }
    public function get_status() { return $this->status; }
    public function get_created_at() { return $this->created_at; }
}
```

**Tasks**:
- [ ] Implement service layer architecture
- [ ] Create entity classes
- [ ] Separate business logic from presentation
- [ ] Implement proper error handling
- [ ] Add transaction management

### Week 9-10: Configuration and API Architecture

#### Day 1-2: Configuration Management
```php
/**
 * Configuration Service
 *
 * Handles all configuration management.
 *
 * @since 2.1.0
 */
class ENNU_Config_Service extends ENNU_Base_Service {

    /**
     * Configuration cache
     *
     * @var array
     */
    private $config_cache = array();

    /**
     * Configuration files directory
     *
     * @var string
     */
    private $config_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $this->config_dir = plugin_dir_path( __FILE__ ) . '../includes/config/';
        $this->load_configurations();
    }

    /**
     * Load all configurations
     */
    private function load_configurations() {
        $config_files = array(
            'assessments' => 'assessments/',
            'scoring'     => 'scoring/',
            'health'      => 'health-optimization/',
            'dashboard'   => 'dashboard/',
        );

        foreach ( $config_files as $key => $path ) {
            $this->config_cache[ $key ] = $this->load_config_directory( $path );
        }
    }

    /**
     * Load configuration directory
     *
     * @param string $path Configuration path.
     * @return array Configuration data.
     */
    private function load_config_directory( $path ) {
        $config_data = array();
        $full_path   = $this->config_dir . $path;

        if ( ! is_dir( $full_path ) ) {
            return $config_data;
        }

        $files = glob( $full_path . '*.php' );
        foreach ( $files as $file ) {
            $config = include $file;
            if ( is_array( $config ) ) {
                $config_data = array_merge( $config_data, $config );
            }
        }

        return $config_data;
    }

    /**
     * Get configuration value
     *
     * @param string $key Configuration key.
     * @param mixed  $default Default value.
     * @return mixed Configuration value.
     */
    public function get( $key, $default = null ) {
        $keys = explode( '.', $key );
        $value = $this->config_cache;

        foreach ( $keys as $k ) {
            if ( ! isset( $value[ $k ] ) ) {
                return $default;
            }
            $value = $value[ $k ];
        }

        return $value;
    }

    /**
     * Set configuration value
     *
     * @param string $key Configuration key.
     * @param mixed  $value Configuration value.
     */
    public function set( $key, $value ) {
        $keys = explode( '.', $key );
        $config = &$this->config_cache;

        foreach ( $keys as $k ) {
            if ( ! isset( $config[ $k ] ) ) {
                $config[ $k ] = array();
            }
            $config = &$config[ $k ];
        }

        $config = $value;
    }

    /**
     * Get environment-specific configuration
     *
     * @param string $key Configuration key.
     * @return mixed Configuration value.
     */
    public function get_env_config( $key ) {
        $env = defined( 'WP_ENV' ) ? WP_ENV : 'production';
        return $this->get( "environments.{$env}.{$key}" );
    }
}
```

**Tasks**:
- [ ] Implement centralized configuration management
- [ ] Add environment-specific configurations
- [ ] Create configuration validation
- [ ] Add configuration caching
- [ ] Implement configuration hot-reloading

#### Day 3-4: API Architecture Implementation
```php
/**
 * REST API Controller
 *
 * Handles REST API endpoints.
 *
 * @since 2.1.0
 */
class ENNU_REST_Controller extends WP_REST_Controller {

    /**
     * API namespace
     *
     * @var string
     */
    protected $namespace = 'ennu/v1';

    /**
     * Assessment service
     *
     * @var ENNU_Assessment_Service
     */
    private $assessment_service;

    /**
     * Constructor
     *
     * @param ENNU_Assessment_Service $assessment_service Assessment service.
     */
    public function __construct( ENNU_Assessment_Service $assessment_service ) {
        $this->assessment_service = $assessment_service;
    }

    /**
     * Register routes
     */
    public function register_routes() {
        // Assessments endpoint
        register_rest_route(
            $this->namespace,
            '/assessments',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_assessments' ),
                    'permission_callback' => array( $this, 'get_assessments_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_assessment' ),
                    'permission_callback' => array( $this, 'create_assessment_permissions_check' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                ),
            )
        );

        // Single assessment endpoint
        register_rest_route(
            $this->namespace,
            '/assessments/(?P<id>[\d]+)',
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_assessment' ),
                    'permission_callback' => array( $this, 'get_assessment_permissions_check' ),
                    'args'                => array(
                        'context' => $this->get_context_param( array( 'default' => 'view' ) ),
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_assessment' ),
                    'permission_callback' => array( $this, 'update_assessment_permissions_check' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_assessment' ),
                    'permission_callback' => array( $this, 'delete_assessment_permissions_check' ),
                ),
            )
        );
    }

    /**
     * Get assessments
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_assessments( $request ) {
        try {
            $user_id = get_current_user_id();
            $filters = array(
                'status' => $request->get_param( 'status' ),
                'limit'  => $request->get_param( 'per_page' ),
                'offset' => $request->get_param( 'offset' ),
            );

            $assessments = $this->assessment_service->get_user_assessments( $user_id, $filters );

            $data = array();
            foreach ( $assessments as $assessment ) {
                $data[] = $this->prepare_item_for_response( $assessment, $request );
            }

            return new WP_REST_Response( $data, 200 );

        } catch ( Exception $e ) {
            return new WP_Error( 'assessment_error', $e->getMessage(), array( 'status' => 500 ) );
        }
    }

    /**
     * Create assessment
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function create_assessment( $request ) {
        try {
            $assessment_data = $request->get_json_params();
            $assessment_data['user_id'] = get_current_user_id();

            $assessment = $this->assessment_service->create_assessment( $assessment_data );

            $response = $this->prepare_item_for_response( $assessment, $request );
            return new WP_REST_Response( $response, 201 );

        } catch ( Exception $e ) {
            return new WP_Error( 'assessment_creation_failed', $e->getMessage(), array( 'status' => 400 ) );
        }
    }

    /**
     * Prepare item for response
     *
     * @param ENNU_Assessment $assessment Assessment object.
     * @param WP_REST_Request $request Request object.
     * @return array Prepared item.
     */
    protected function prepare_item_for_response( $assessment, $request ) {
        return array(
            'id'         => $assessment->get_id(),
            'user_id'    => $assessment->get_user_id(),
            'type'       => $assessment->get_type(),
            'status'     => $assessment->get_status(),
            'created_at' => $assessment->get_created_at(),
            'data'       => $assessment->get_data(),
        );
    }

    /**
     * Get assessments permissions check
     *
     * @param WP_REST_Request $request Request object.
     * @return bool True if user can read assessments.
     */
    public function get_assessments_permissions_check( $request ) {
        return is_user_logged_in();
    }

    /**
     * Create assessment permissions check
     *
     * @param WP_REST_Request $request Request object.
     * @return bool True if user can create assessments.
     */
    public function create_assessment_permissions_check( $request ) {
        return is_user_logged_in();
    }
}
```

**Tasks**:
- [ ] Implement REST API structure
- [ ] Add proper API versioning
- [ ] Implement consistent data formats
- [ ] Add comprehensive error handling
- [ ] Create API documentation

### Week 11-12: Final Architecture Improvements

#### Day 1-2: Event System Implementation
```php
/**
 * Event System
 *
 * Handles plugin events and hooks.
 *
 * @since 2.1.0
 */
class ENNU_Event_System {

    /**
     * Event listeners
     *
     * @var array
     */
    private $listeners = array();

    /**
     * Add event listener
     *
     * @param string   $event Event name.
     * @param callable $callback Callback function.
     * @param int      $priority Priority.
     */
    public function add_listener( $event, $callback, $priority = 10 ) {
        if ( ! isset( $this->listeners[ $event ] ) ) {
            $this->listeners[ $event ] = array();
        }

        $this->listeners[ $event ][] = array(
            'callback' => $callback,
            'priority' => $priority,
        );

        // Sort by priority
        usort( $this->listeners[ $event ], function( $a, $b ) {
            return $a['priority'] - $b['priority'];
        } );
    }

    /**
     * Trigger event
     *
     * @param string $event Event name.
     * @param array  $data Event data.
     */
    public function trigger( $event, $data = array() ) {
        if ( ! isset( $this->listeners[ $event ] ) ) {
            return;
        }

        foreach ( $this->listeners[ $event ] as $listener ) {
            call_user_func( $listener['callback'], $data );
        }
    }
}
```

**Tasks**:
- [ ] Implement event system
- [ ] Add plugin hooks and filters
- [ ] Create event documentation
- [ ] Add event logging
- [ ] Implement event testing

#### Day 3-4: Caching and Performance
```php
/**
 * Cache Manager
 *
 * Handles caching for the plugin.
 *
 * @since 2.1.0
 */
class ENNU_Cache_Manager {

    /**
     * Cache group
     *
     * @var string
     */
    private $cache_group = 'ennu_assessments';

    /**
     * Default cache expiry
     *
     * @var int
     */
    private $default_expiry = 3600;

    /**
     * Get cached value
     *
     * @param string $key Cache key.
     * @return mixed Cached value or false.
     */
    public function get( $key ) {
        return wp_cache_get( $key, $this->cache_group );
    }

    /**
     * Set cached value
     *
     * @param string $key Cache key.
     * @param mixed  $value Value to cache.
     * @param int    $expiry Expiry time in seconds.
     * @return bool True on success.
     */
    public function set( $key, $value, $expiry = null ) {
        if ( null === $expiry ) {
            $expiry = $this->default_expiry;
        }

        return wp_cache_set( $key, $value, $this->cache_group, $expiry );
    }

    /**
     * Delete cached value
     *
     * @param string $key Cache key.
     * @return bool True on success.
     */
    public function delete( $key ) {
        return wp_cache_delete( $key, $this->cache_group );
    }

    /**
     * Clear all plugin cache
     */
    public function clear_all() {
        wp_cache_flush_group( $this->cache_group );
    }
}
```

**Tasks**:
- [ ] Implement caching system
- [ ] Add cache invalidation strategies
- [ ] Create cache monitoring
- [ ] Add cache performance metrics
- [ ] Implement cache warming

#### Day 5: Final Integration and Testing
```php
/**
 * Plugin Bootstrap
 *
 * Main plugin initialization.
 *
 * @since 2.1.0
 */
class ENNU_Plugin_Bootstrap {

    /**
     * Container instance
     *
     * @var ENNU_Container
     */
    private $container;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_container();
        $this->register_services();
        $this->init_components();
    }

    /**
     * Initialize container
     */
    private function init_container() {
        $this->container = ENNU_Container::get_instance();
    }

    /**
     * Register services
     */
    private function register_services() {
        $this->container->register_core_services();
    }

    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize REST API
        $assessment_service = $this->container->get( 'assessment' );
        $rest_controller = new ENNU_REST_Controller( $assessment_service );
        add_action( 'rest_api_init', array( $rest_controller, 'register_routes' ) );

        // Initialize shortcodes
        $shortcodes = new ENNU_Assessment_Shortcodes( $this->container );
        $shortcodes->init();

        // Initialize admin
        if ( is_admin() ) {
            $admin = new ENNU_Admin( $this->container );
            $admin->init();
        }
    }
}
```

**Tasks**:
- [ ] Integrate all components
- [ ] Add comprehensive testing
- [ ] Create deployment scripts
- [ ] Add monitoring and logging
- [ ] Create documentation

## Architecture Checklist

### Class Structure
- [ ] All classes follow single responsibility principle
- [ ] Dependency injection implemented
- [ ] No tight coupling between components
- [ ] Proper interfaces defined
- [ ] Error handling implemented

### Service Layer
- [ ] Business logic separated from presentation
- [ ] Service layer implemented
- [ ] Transaction management added
- [ ] Proper error handling
- [ ] Service interfaces defined

### Configuration Management
- [ ] Centralized configuration management
- [ ] Environment-specific configs
- [ ] Configuration validation
- [ ] Configuration caching
- [ ] Hot-reloading support

### API Architecture
- [ ] REST API structure implemented
- [ ] API versioning added
- [ ] Consistent data formats
- [ ] Comprehensive error handling
- [ ] API documentation created

## Success Criteria

- **Maintainability**: Code is easy to understand and modify
- **Scalability**: Architecture supports growth
- **Performance**: Optimized for speed and efficiency
- **Testability**: All components are testable
- **Documentation**: Complete architecture documentation

## Tools and Resources

### Development Tools
- PHPUnit for testing
- PHP_CodeSniffer for standards
- Composer for dependency management
- Git for version control

### Architecture Patterns
- Dependency Injection
- Service Layer
- Repository Pattern
- Event-Driven Architecture
- CQRS (Command Query Responsibility Segregation)

---

*This roadmap transforms the plugin architecture to be maintainable, scalable, and follow modern development practices.* 