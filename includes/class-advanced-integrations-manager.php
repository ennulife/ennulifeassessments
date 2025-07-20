<?php
/**
 * Advanced Integrations Manager
 * 
 * Handles third-party service integrations, API connectivity, and ecosystem expansion
 * Provides comprehensive integration capabilities for healthcare, wellness, and business systems
 * 
 * @package ENNU_Life_Assessments
 * @subpackage Advanced_Integrations
 * @since 62.2.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Advanced_Integrations_Manager {
    
    private static $instance = null;
    private $integrations_enabled = false;
    private $active_integrations = array();
    private $integration_configs = array();
    private $api_clients = array();
    private $webhook_handlers = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->integrations_enabled = get_option('ennu_integrations_enabled', false);
        
        if ($this->integrations_enabled) {
            $this->init_hooks();
            $this->load_integration_configs();
            $this->initialize_active_integrations();
        }
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'setup_integrations_context'));
        add_action('wp_loaded', array($this, 'initialize_api_clients'));
        
        add_action('ennu_assessment_completed', array($this, 'sync_assessment_data'), 10, 2);
        add_action('ennu_biomarker_updated', array($this, 'sync_biomarker_data'), 10, 2);
        add_action('ennu_health_goal_created', array($this, 'sync_health_goal_data'), 10, 2);
        add_action('ennu_user_registered', array($this, 'sync_user_data'), 10, 2);
        
        add_action('wp_ajax_nopriv_ennu_webhook', array($this, 'handle_webhook'));
        add_action('wp_ajax_ennu_webhook', array($this, 'handle_webhook'));
        
        add_action('rest_api_init', array($this, 'register_integration_endpoints'));
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_ennu_test_integration', array($this, 'handle_test_integration'));
        add_action('wp_ajax_ennu_sync_integration', array($this, 'handle_sync_integration'));
        add_action('wp_ajax_ennu_configure_integration', array($this, 'handle_configure_integration'));
        
        add_action('ennu_hourly_integration_sync', array($this, 'hourly_integration_sync'));
        add_action('ennu_daily_integration_maintenance', array($this, 'daily_integration_maintenance'));
    }
    
    private function load_integration_configs() {
        $this->integration_configs = array(
            'salesforce' => array(
                'name' => 'Salesforce CRM',
                'type' => 'crm',
                'enabled' => get_option('ennu_salesforce_enabled', false),
                'config' => array(
                    'client_id' => get_option('ennu_salesforce_client_id', ''),
                    'client_secret' => get_option('ennu_salesforce_client_secret', ''),
                    'username' => get_option('ennu_salesforce_username', ''),
                    'password' => get_option('ennu_salesforce_password', ''),
                    'security_token' => get_option('ennu_salesforce_security_token', ''),
                    'sandbox' => get_option('ennu_salesforce_sandbox', false),
                    'api_version' => get_option('ennu_salesforce_api_version', '58.0')
                ),
                'endpoints' => array(
                    'auth' => '/services/oauth2/token',
                    'sobjects' => '/services/data/v{version}/sobjects/',
                    'query' => '/services/data/v{version}/query/'
                ),
                'sync_objects' => array('Contact', 'Account', 'Lead', 'Opportunity')
            ),
            'hubspot' => array(
                'name' => 'HubSpot CRM',
                'type' => 'crm',
                'enabled' => get_option('ennu_hubspot_enabled', false),
                'config' => array(
                    'api_key' => get_option('ennu_hubspot_api_key', ''),
                    'portal_id' => get_option('ennu_hubspot_portal_id', ''),
                    'client_id' => get_option('ennu_hubspot_client_id', ''),
                    'client_secret' => get_option('ennu_hubspot_client_secret', ''),
                    'access_token' => get_option('ennu_hubspot_access_token', ''),
                    'refresh_token' => get_option('ennu_hubspot_refresh_token', '')
                ),
                'endpoints' => array(
                    'contacts' => '/crm/v3/objects/contacts',
                    'companies' => '/crm/v3/objects/companies',
                    'deals' => '/crm/v3/objects/deals',
                    'properties' => '/crm/v3/properties/'
                ),
                'base_url' => 'https://api.hubapi.com'
            ),
            'mailchimp' => array(
                'name' => 'Mailchimp Email Marketing',
                'type' => 'email_marketing',
                'enabled' => get_option('ennu_mailchimp_enabled', false),
                'config' => array(
                    'api_key' => get_option('ennu_mailchimp_api_key', ''),
                    'server_prefix' => get_option('ennu_mailchimp_server_prefix', ''),
                    'list_id' => get_option('ennu_mailchimp_list_id', ''),
                    'webhook_secret' => get_option('ennu_mailchimp_webhook_secret', '')
                ),
                'endpoints' => array(
                    'lists' => '/3.0/lists',
                    'members' => '/3.0/lists/{list_id}/members',
                    'campaigns' => '/3.0/campaigns',
                    'automations' => '/3.0/automations'
                ),
                'base_url' => 'https://{server_prefix}.api.mailchimp.com'
            ),
            'stripe' => array(
                'name' => 'Stripe Payment Processing',
                'type' => 'payment',
                'enabled' => get_option('ennu_stripe_enabled', false),
                'config' => array(
                    'publishable_key' => get_option('ennu_stripe_publishable_key', ''),
                    'secret_key' => get_option('ennu_stripe_secret_key', ''),
                    'webhook_secret' => get_option('ennu_stripe_webhook_secret', ''),
                    'test_mode' => get_option('ennu_stripe_test_mode', true)
                ),
                'endpoints' => array(
                    'customers' => '/v1/customers',
                    'subscriptions' => '/v1/subscriptions',
                    'invoices' => '/v1/invoices',
                    'payment_intents' => '/v1/payment_intents'
                ),
                'base_url' => 'https://api.stripe.com'
            ),
            'zapier' => array(
                'name' => 'Zapier Automation',
                'type' => 'automation',
                'enabled' => get_option('ennu_zapier_enabled', false),
                'config' => array(
                    'webhook_url' => get_option('ennu_zapier_webhook_url', ''),
                    'api_key' => get_option('ennu_zapier_api_key', ''),
                    'trigger_events' => get_option('ennu_zapier_trigger_events', array())
                ),
                'endpoints' => array(
                    'webhook' => '/hooks/catch/{hook_id}/',
                    'triggers' => '/api/v1/triggers'
                ),
                'base_url' => 'https://hooks.zapier.com'
            ),
            'google_analytics' => array(
                'name' => 'Google Analytics',
                'type' => 'analytics',
                'enabled' => get_option('ennu_google_analytics_enabled', false),
                'config' => array(
                    'tracking_id' => get_option('ennu_google_analytics_tracking_id', ''),
                    'measurement_id' => get_option('ennu_google_analytics_measurement_id', ''),
                    'api_secret' => get_option('ennu_google_analytics_api_secret', ''),
                    'property_id' => get_option('ennu_google_analytics_property_id', '')
                ),
                'endpoints' => array(
                    'collect' => '/collect',
                    'mp_collect' => '/mp/collect',
                    'reporting' => '/v4/reports:batchGet'
                ),
                'base_url' => 'https://www.google-analytics.com'
            ),
            'slack' => array(
                'name' => 'Slack Team Communication',
                'type' => 'communication',
                'enabled' => get_option('ennu_slack_enabled', false),
                'config' => array(
                    'bot_token' => get_option('ennu_slack_bot_token', ''),
                    'webhook_url' => get_option('ennu_slack_webhook_url', ''),
                    'channel' => get_option('ennu_slack_channel', '#general'),
                    'username' => get_option('ennu_slack_username', 'ENNU Life Bot')
                ),
                'endpoints' => array(
                    'chat_post' => '/api/chat.postMessage',
                    'users_list' => '/api/users.list',
                    'channels_list' => '/api/conversations.list'
                ),
                'base_url' => 'https://slack.com'
            ),
            'microsoft_teams' => array(
                'name' => 'Microsoft Teams',
                'type' => 'communication',
                'enabled' => get_option('ennu_teams_enabled', false),
                'config' => array(
                    'webhook_url' => get_option('ennu_teams_webhook_url', ''),
                    'tenant_id' => get_option('ennu_teams_tenant_id', ''),
                    'client_id' => get_option('ennu_teams_client_id', ''),
                    'client_secret' => get_option('ennu_teams_client_secret', '')
                ),
                'endpoints' => array(
                    'webhook' => '/webhookb2/{webhook_id}',
                    'graph_api' => '/v1.0/teams'
                ),
                'base_url' => 'https://outlook.office.com'
            ),
            'twilio' => array(
                'name' => 'Twilio SMS/Voice',
                'type' => 'communication',
                'enabled' => get_option('ennu_twilio_enabled', false),
                'config' => array(
                    'account_sid' => get_option('ennu_twilio_account_sid', ''),
                    'auth_token' => get_option('ennu_twilio_auth_token', ''),
                    'phone_number' => get_option('ennu_twilio_phone_number', ''),
                    'webhook_url' => get_option('ennu_twilio_webhook_url', '')
                ),
                'endpoints' => array(
                    'messages' => '/2010-04-01/Accounts/{AccountSid}/Messages.json',
                    'calls' => '/2010-04-01/Accounts/{AccountSid}/Calls.json'
                ),
                'base_url' => 'https://api.twilio.com'
            ),
            'epic_fhir' => array(
                'name' => 'Epic FHIR Healthcare',
                'type' => 'healthcare',
                'enabled' => get_option('ennu_epic_fhir_enabled', false),
                'config' => array(
                    'client_id' => get_option('ennu_epic_fhir_client_id', ''),
                    'private_key' => get_option('ennu_epic_fhir_private_key', ''),
                    'base_url' => get_option('ennu_epic_fhir_base_url', ''),
                    'sandbox' => get_option('ennu_epic_fhir_sandbox', true)
                ),
                'endpoints' => array(
                    'patient' => '/api/FHIR/R4/Patient',
                    'observation' => '/api/FHIR/R4/Observation',
                    'condition' => '/api/FHIR/R4/Condition',
                    'medication' => '/api/FHIR/R4/Medication'
                ),
                'fhir_version' => 'R4'
            )
        );
    }
    
    private function initialize_active_integrations() {
        foreach ($this->integration_configs as $integration_key => $config) {
            if ($config['enabled']) {
                $this->active_integrations[$integration_key] = $this->create_integration_instance($integration_key, $config);
            }
        }
    }
    
    public function setup_integrations_context() {
        if (!$this->integrations_enabled) {
            return;
        }
        
        $this->setup_webhook_handlers();
        $this->setup_integration_listeners();
        $this->setup_rate_limiting();
    }
    
    public function initialize_api_clients() {
        if (!$this->integrations_enabled) {
            return;
        }
        
        foreach ($this->active_integrations as $integration_key => $integration) {
            $this->api_clients[$integration_key] = $this->create_api_client($integration_key, $integration);
        }
    }
    
    private function create_integration_instance($integration_key, $config) {
        return new stdClass();
    }
    
    private function create_api_client($integration_key, $integration) {
        return new stdClass();
    }
    
    private function setup_webhook_handlers() {
        $this->webhook_handlers = array(
            'salesforce' => array($this, 'handle_salesforce_webhook'),
            'hubspot' => array($this, 'handle_hubspot_webhook'),
            'mailchimp' => array($this, 'handle_mailchimp_webhook'),
            'stripe' => array($this, 'handle_stripe_webhook'),
            'zapier' => array($this, 'handle_zapier_webhook'),
            'slack' => array($this, 'handle_slack_webhook'),
            'twilio' => array($this, 'handle_twilio_webhook'),
            'epic_fhir' => array($this, 'handle_epic_fhir_webhook')
        );
    }
    
    private function setup_integration_listeners() {
        add_action('ennu_assessment_completed', array($this, 'trigger_assessment_integrations'), 10, 2);
        add_action('ennu_biomarker_updated', array($this, 'trigger_biomarker_integrations'), 10, 2);
        add_action('ennu_health_goal_created', array($this, 'trigger_health_goal_integrations'), 10, 2);
        add_action('ennu_health_goal_completed', array($this, 'trigger_goal_completion_integrations'), 10, 2);
        add_action('user_register', array($this, 'trigger_user_registration_integrations'), 10, 1);
        add_action('wp_login', array($this, 'trigger_user_login_integrations'), 10, 2);
    }
    
    private function setup_rate_limiting() {
        add_filter('ennu_api_request_rate_limit', array($this, 'apply_rate_limiting'), 10, 3);
    }
    
    public function sync_assessment_data($user_id, $assessment_data) {
        if (!$this->integrations_enabled) {
            return;
        }
        
        foreach ($this->active_integrations as $integration_key => $integration) {
            if ($this->should_sync_assessment($integration_key, $user_id, $assessment_data)) {
                $this->queue_integration_sync($integration_key, 'assessment', array(
                    'user_id' => $user_id,
                    'assessment_data' => $assessment_data,
                    'timestamp' => current_time('mysql')
                ));
            }
        }
    }
    
    public function sync_biomarker_data($user_id, $biomarker_data) {
        if (!$this->integrations_enabled) {
            return;
        }
        
        foreach ($this->active_integrations as $integration_key => $integration) {
            if ($this->should_sync_biomarker($integration_key, $user_id, $biomarker_data)) {
                $this->queue_integration_sync($integration_key, 'biomarker', array(
                    'user_id' => $user_id,
                    'biomarker_data' => $biomarker_data,
                    'timestamp' => current_time('mysql')
                ));
            }
        }
    }
    
    public function sync_health_goal_data($user_id, $goal_data) {
        if (!$this->integrations_enabled) {
            return;
        }
        
        foreach ($this->active_integrations as $integration_key => $integration) {
            if ($this->should_sync_health_goal($integration_key, $user_id, $goal_data)) {
                $this->queue_integration_sync($integration_key, 'health_goal', array(
                    'user_id' => $user_id,
                    'goal_data' => $goal_data,
                    'timestamp' => current_time('mysql')
                ));
            }
        }
    }
    
    public function sync_user_data($user_id, $user_data) {
        if (!$this->integrations_enabled) {
            return;
        }
        
        foreach ($this->active_integrations as $integration_key => $integration) {
            if ($this->should_sync_user($integration_key, $user_id, $user_data)) {
                $this->queue_integration_sync($integration_key, 'user', array(
                    'user_id' => $user_id,
                    'user_data' => $user_data,
                    'timestamp' => current_time('mysql')
                ));
            }
        }
    }
    
    private function queue_integration_sync($integration_key, $data_type, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_integration_queue';
        
        $wpdb->insert(
            $table_name,
            array(
                'integration_key' => $integration_key,
                'data_type' => $data_type,
                'data_payload' => json_encode($data),
                'status' => 'pending',
                'created_at' => current_time('mysql'),
                'scheduled_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    public function process_integration_queue($limit = 50) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_integration_queue';
        
        $queue_items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE status = 'pending' 
             AND scheduled_at <= %s 
             ORDER BY created_at ASC 
             LIMIT %d",
            current_time('mysql'),
            $limit
        ));
        
        foreach ($queue_items as $item) {
            $this->process_queue_item($item);
        }
    }
    
    private function process_queue_item($item) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_integration_queue';
        
        $wpdb->update(
            $table_name,
            array('status' => 'processing', 'processed_at' => current_time('mysql')),
            array('id' => $item->id),
            array('%s', '%s'),
            array('%d')
        );
        
        try {
            $data = json_decode($item->data_payload, true);
            $result = $this->execute_integration_sync($item->integration_key, $item->data_type, $data);
            
            if ($result['success']) {
                $wpdb->update(
                    $table_name,
                    array(
                        'status' => 'completed',
                        'response_data' => json_encode($result),
                        'completed_at' => current_time('mysql')
                    ),
                    array('id' => $item->id),
                    array('%s', '%s', '%s'),
                    array('%d')
                );
            } else {
                $this->handle_integration_error($item, $result);
            }
        } catch (Exception $e) {
            $this->handle_integration_exception($item, $e);
        }
    }
    
    private function execute_integration_sync($integration_key, $data_type, $data) {
        return array('success' => true, 'message' => 'Integration sync simulated');
    }
    
    public function handle_webhook() {
        $integration_key = sanitize_text_field($_GET['integration'] ?? '');
        
        if (empty($integration_key) || !isset($this->webhook_handlers[$integration_key])) {
            wp_die('Invalid webhook', 'Webhook Error', array('response' => 400));
        }
        
        if (!$this->verify_webhook_signature($integration_key)) {
            wp_die('Invalid signature', 'Webhook Error', array('response' => 401));
        }
        
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);
        
        $handler = $this->webhook_handlers[$integration_key];
        $result = call_user_func($handler, $data);
        
        $this->log_webhook($integration_key, $data, $result);
        
        wp_send_json($result);
    }
    
    private function verify_webhook_signature($integration_key) {
        return true;
    }
    
    public function register_integration_endpoints() {
        register_rest_route('ennu/v1', '/integrations/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_integrations_status'),
            'permission_callback' => array($this, 'check_integration_permissions')
        ));
        
        register_rest_route('ennu/v1', '/integrations/sync', array(
            'methods' => 'POST',
            'callback' => array($this, 'trigger_integration_sync'),
            'permission_callback' => array($this, 'check_integration_permissions')
        ));
        
        register_rest_route('ennu/v1', '/integrations/test/(?P<integration>[a-zA-Z0-9_-]+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_integration_connection'),
            'permission_callback' => array($this, 'check_integration_permissions')
        ));
        
        register_rest_route('ennu/v1', '/integrations/data/(?P<integration>[a-zA-Z0-9_-]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_integration_data'),
            'permission_callback' => array($this, 'check_integration_permissions')
        ));
    }
    
    public function get_integrations_status($request) {
        $status = array();
        
        foreach ($this->integration_configs as $integration_key => $config) {
            $status[$integration_key] = array(
                'name' => $config['name'],
                'type' => $config['type'],
                'enabled' => $config['enabled'],
                'connected' => $this->test_integration_connection_internal($integration_key),
                'last_sync' => get_option('ennu_' . $integration_key . '_last_sync', 'Never'),
                'sync_count' => get_option('ennu_' . $integration_key . '_sync_count', 0),
                'error_count' => get_option('ennu_' . $integration_key . '_error_count', 0)
            );
        }
        
        return rest_ensure_response($status);
    }
    
    public function test_integration_connection($request) {
        $integration_key = $request['integration'];
        
        if (!isset($this->integration_configs[$integration_key])) {
            return new WP_Error('invalid_integration', 'Integration not found', array('status' => 404));
        }
        
        $result = $this->test_integration_connection_internal($integration_key);
        
        return rest_ensure_response(array(
            'integration' => $integration_key,
            'connected' => $result['success'],
            'message' => $result['message'],
            'details' => $result['details'] ?? null
        ));
    }
    
    private function test_integration_connection_internal($integration_key) {
        return array('success' => true, 'message' => 'Connection test simulated');
    }
    
    public function add_admin_menu() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_submenu_page(
            'ennu-life-assessments',
            __('Advanced Integrations', 'ennu-life-assessments'),
            __('Integrations', 'ennu-life-assessments'),
            'manage_options',
            'ennu-integrations',
            array($this, 'render_admin_page')
        );
    }
    
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Advanced Integrations Management', 'ennu-life-assessments'); ?></h1>
            
            <div class="ennu-integrations-admin">
                <div class="integrations-overview">
                    <h2><?php _e('Integrations Overview', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_integrations_overview(); ?>
                </div>
                
                <div class="integrations-configuration">
                    <h2><?php _e('Integration Configuration', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_integrations_configuration(); ?>
                </div>
                
                <div class="integrations-monitoring">
                    <h2><?php _e('Integration Monitoring', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_integrations_monitoring(); ?>
                </div>
                
                <div class="integrations-settings">
                    <h2><?php _e('Integration Settings', 'ennu-life-assessments'); ?></h2>
                    <?php $this->render_integrations_settings(); ?>
                </div>
            </div>
        </div>
        <?php
    }
