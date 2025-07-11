<?php
/**
 * ENNU Life WooCommerce Integration
 * Auto-creates products and handles e-commerce functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_WooCommerce_Integration {
    
    private static $instance = null;
    private $product_definitions = array();
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Only initialize if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            return;
        }
        
        add_action('wp_ajax_ennu_create_products', array($this, 'ajax_create_products'));
        add_action('wp_ajax_ennu_reset_products', array($this, 'ajax_reset_products'));
        add_action('wp_ajax_ennu_rebuild_products', array($this, 'ajax_rebuild_products'));
        add_action('wp_ajax_ennu_delete_products', array($this, 'ajax_delete_products'));
        
        $this->init_product_definitions();
        $this->init_woocommerce_hooks();
    }
    
    private function is_woocommerce_active() {
        return class_exists('WooCommerce') && function_exists('WC');
    }
    
    private function init_product_definitions() {
        $this->product_definitions = array(
            'health-assessment-599' => array(
                'name' => 'Comprehensive Health Assessment',
                'price' => 599.00,
                'description' => 'Complete health evaluation with professional consultation and personalized recommendations.',
                'short_description' => 'Professional health assessment with detailed analysis.',
                'category' => 'Health Assessments',
                'type' => 'simple',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-HA-599'
            ),
            'ed-treatment-consultation' => array(
                'name' => 'ED Treatment Consultation',
                'price' => 299.00,
                'description' => 'Confidential consultation for erectile dysfunction treatment options.',
                'short_description' => 'Private ED treatment consultation.',
                'category' => 'Medical Consultations',
                'type' => 'simple',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-ED-299'
            ),
            'hair-restoration-package' => array(
                'name' => 'Hair Restoration Assessment Package',
                'price' => 399.00,
                'description' => 'Complete hair loss evaluation and restoration treatment planning.',
                'short_description' => 'Professional hair restoration assessment.',
                'category' => 'Aesthetic Services',
                'type' => 'simple',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-HR-399'
            ),
            'weight-loss-program' => array(
                'name' => 'Personalized Weight Loss Program',
                'price' => 499.00,
                'description' => 'Customized weight loss program with ongoing support and monitoring.',
                'short_description' => 'Personalized weight management program.',
                'category' => 'Weight Management',
                'type' => 'simple',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-WL-499'
            ),
            'skin-assessment-premium' => array(
                'name' => 'Premium Skin Assessment',
                'price' => 349.00,
                'description' => 'Advanced dermatological assessment with treatment recommendations.',
                'short_description' => 'Professional skin analysis and treatment planning.',
                'category' => 'Dermatology',
                'type' => 'simple',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-SA-349'
            ),
            'membership-basic' => array(
                'name' => 'ENNU Basic Membership',
                'price' => 99.00,
                'description' => 'Monthly membership with access to basic health assessments and resources.',
                'short_description' => 'Basic monthly health membership.',
                'category' => 'Memberships',
                'type' => 'subscription',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-MB-99'
            ),
            'membership-premium' => array(
                'name' => 'ENNU Premium Membership',
                'price' => 199.00,
                'description' => 'Premium monthly membership with full access to all assessments and priority support.',
                'short_description' => 'Premium monthly health membership.',
                'category' => 'Memberships',
                'type' => 'subscription',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-MP-199'
            ),
            'wellness-consultation' => array(
                'name' => 'Wellness Consultation',
                'price' => 199.00,
                'description' => 'Comprehensive wellness consultation with lifestyle recommendations.',
                'short_description' => 'Professional wellness consultation.',
                'category' => 'Wellness Services',
                'type' => 'simple',
                'virtual' => true,
                'downloadable' => false,
                'sku' => 'ENNU-WC-199'
            )
        );
    }
    
    public function ajax_create_products() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce is not installed or activated');
        }
        
        $results = array(
            'products_created' => 0,
            'products_updated' => 0,
            'errors' => array(),
            'success_products' => array()
        );
        
        foreach ($this->product_definitions as $product_key => $product_data) {
            $result = $this->create_or_update_product($product_key, $product_data);
            
            if ($result['success']) {
                if ($result['action'] === 'created') {
                    $results['products_created']++;
                } else {
                    $results['products_updated']++;
                }
                $results['success_products'][] = $product_data['name'];
            } else {
                $results['errors'][] = $product_data['name'] . ': ' . $result['message'];
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(
                'Product creation complete! Created: %d products, Updated: %d products',
                $results['products_created'],
                $results['products_updated']
            ),
            'details' => $results
        ));
    }
    
    public function ajax_delete_products() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce is not installed');
        }
        
        $deleted_count = 0;
        $errors = array();
        
        foreach ($this->product_definitions as $product_key => $product_data) {
            $product_id = wc_get_product_id_by_sku($product_data['sku']);
            
            if ($product_id) {
                $result = wp_delete_post($product_id, true);
                if ($result) {
                    $deleted_count++;
                } else {
                    $errors[] = 'Failed to delete: ' . $product_data['name'];
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf('Deleted %d products successfully', $deleted_count),
            'deleted_count' => $deleted_count,
            'errors' => $errors
        ));
    }
    
    private function create_or_update_product($product_key, $product_data) {
        try {
            // Check if product already exists by SKU
            $existing_product_id = wc_get_product_id_by_sku($product_data['sku']);
            
            if ($existing_product_id) {
                // Update existing product
                $product = wc_get_product($existing_product_id);
                if (!$product) {
                    return array(
                        'success' => false,
                        'message' => 'Could not load existing product'
                    );
                }
            } else {
                // Create new product
                $product = new WC_Product_Simple();
            }
            
            // Set product data
            $product->set_name($product_data['name']);
            $product->set_description($product_data['description']);
            $product->set_short_description($product_data['short_description']);
            $product->set_sku($product_data['sku']);
            $product->set_regular_price($product_data['price']);
            $product->set_virtual($product_data['virtual']);
            $product->set_downloadable($product_data['downloadable']);
            $product->set_status('publish');
            $product->set_catalog_visibility('visible');
            
            // Save product
            $product_id = $product->save();
            
            if (!$product_id) {
                return array(
                    'success' => false,
                    'message' => 'Failed to save product'
                );
            }
            
            // Set product category
            $this->set_product_category($product_id, $product_data['category']);
            
            // Add custom meta
            update_post_meta($product_id, '_ennu_product_key', $product_key);
            update_post_meta($product_id, '_ennu_auto_created', current_time('mysql'));
            
            return array(
                'success' => true,
                'action' => $existing_product_id ? 'updated' : 'created',
                'product_id' => $product_id
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    private function set_product_category($product_id, $category_name) {
        // Get or create category
        $category = get_term_by('name', $category_name, 'product_cat');
        
        if (!$category) {
            $category_result = wp_insert_term($category_name, 'product_cat');
            if (!is_wp_error($category_result)) {
                $category_id = $category_result['term_id'];
            } else {
                return false;
            }
        } else {
            $category_id = $category->term_id;
        }
        
        // Assign category to product
        wp_set_object_terms($product_id, array($category_id), 'product_cat');
        
        return true;
    }
    
    public function get_product_definitions() {
        return $this->product_definitions;
    }
    
    public function get_products_status() {
        if (!$this->is_woocommerce_active()) {
            return array(
                'woocommerce_active' => false,
                'message' => 'WooCommerce is not installed or activated'
            );
        }
        
        $status = array(
            'woocommerce_active' => true,
            'products' => array(),
            'total_products' => count($this->product_definitions),
            'existing_products' => 0
        );
        
        foreach ($this->product_definitions as $product_key => $product_data) {
            $product_id = wc_get_product_id_by_sku($product_data['sku']);
            $exists = $product_id ? true : false;
            
            if ($exists) {
                $status['existing_products']++;
                $product = wc_get_product($product_id);
                $product_url = $product ? $product->get_permalink() : '';
                $edit_url = get_edit_post_link($product_id);
            } else {
                $product_url = '';
                $edit_url = '';
            }
            
            $status['products'][] = array(
                'name' => $product_data['name'],
                'sku' => $product_data['sku'],
                'price' => $product_data['price'],
                'exists' => $exists,
                'url' => $product_url,
                'edit_url' => $edit_url
            );
        }
        
        return $status;
    }
    
    /**
     * Get recommended products based on assessment results
     */
    public function get_recommended_products($assessment_type, $assessment_results = array()) {
        $recommendations = array();
        
        switch ($assessment_type) {
            case 'ed-treatment-assessment':
                $recommendations[] = 'ed-treatment-consultation';
                if (isset($assessment_results['severity']) && $assessment_results['severity'] === 'severe') {
                    $recommendations[] = 'membership-premium';
                } else {
                    $recommendations[] = 'membership-basic';
                }
                break;
                
            case 'weight-loss-assessment':
                $recommendations[] = 'weight-loss-program';
                $recommendations[] = 'wellness-consultation';
                if (isset($assessment_results['bmi']) && $assessment_results['bmi'] > 30) {
                    $recommendations[] = 'membership-premium';
                } else {
                    $recommendations[] = 'membership-basic';
                }
                break;
                
            case 'health-assessment':
                $recommendations[] = 'health-assessment-599';
                $recommendations[] = 'wellness-consultation';
                $recommendations[] = 'membership-basic';
                break;
                
            case 'skin-assessment-enhanced':
                $recommendations[] = 'skin-assessment-premium';
                $recommendations[] = 'wellness-consultation';
                break;
                
            case 'hair-restoration-assessment':
                $recommendations[] = 'hair-restoration-package';
                $recommendations[] = 'membership-basic';
                break;
                
            case 'hormone-assessment':
                $recommendations[] = 'wellness-consultation';
                $recommendations[] = 'membership-premium';
                break;
                
            default:
                $recommendations[] = 'wellness-consultation';
                $recommendations[] = 'membership-basic';
                break;
        }
        
        return $this->get_products_by_keys($recommendations);
    }
    
    /**
     * Get WooCommerce products by product keys
     */
    private function get_products_by_keys($product_keys) {
        $products = array();
        
        foreach ($product_keys as $key) {
            if (isset($this->product_definitions[$key])) {
                $product_data = $this->product_definitions[$key];
                $product_id = wc_get_product_id_by_sku($product_data['sku']);
                
                if ($product_id) {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        $products[] = array(
                            'id' => $product_id,
                            'name' => $product->get_name(),
                            'price' => $product->get_price(),
                            'url' => $product->get_permalink(),
                            'add_to_cart_url' => wc_get_cart_url() . '?add-to-cart=' . $product_id,
                            'description' => $product->get_short_description(),
                            'key' => $key
                        );
                    }
                }
            }
        }
        
        return $products;
    }
    
    /**
     * Add assessment results to cart as metadata
     */
    public function add_assessment_to_cart($product_key, $assessment_data = array()) {
        if (!$this->is_woocommerce_active()) {
            return false;
        }
        
        if (!isset($this->product_definitions[$product_key])) {
            return false;
        }
        
        $product_data = $this->product_definitions[$product_key];
        $product_id = wc_get_product_id_by_sku($product_data['sku']);
        
        if (!$product_id) {
            return false;
        }
        
        // Add to cart with assessment data
        $cart_item_data = array(
            'assessment_data' => $assessment_data,
            'assessment_type' => $assessment_data['type'] ?? '',
            'assessment_id' => $assessment_data['id'] ?? ''
        );
        
        return WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
    }
    
    /**
     * Display assessment data in cart and checkout
     */
    public function display_assessment_data_in_cart($item_data, $cart_item) {
        if (isset($cart_item['assessment_data'])) {
            $assessment_data = $cart_item['assessment_data'];
            
            $item_data[] = array(
                'key' => 'Assessment Type',
                'value' => ucwords(str_replace('-', ' ', $assessment_data['type'] ?? 'General'))
            );
            
            if (isset($assessment_data['score'])) {
                $item_data[] = array(
                    'key' => 'Assessment Score',
                    'value' => $assessment_data['score'] . '/100'
                );
            }
            
            if (isset($assessment_data['id'])) {
                $item_data[] = array(
                    'key' => 'Assessment ID',
                    'value' => $assessment_data['id']
                );
            }
        }
        
        return $item_data;
    }
    
    /**
     * Save assessment data to order
     */
    public function save_assessment_data_to_order($order_id, $posted_data, $order) {
        $order_items = $order->get_items();
        
        foreach ($order_items as $item_id => $item) {
            $cart_item_data = $item->get_meta('_cart_item_data');
            
            if ($cart_item_data && isset($cart_item_data['assessment_data'])) {
                $assessment_data = $cart_item_data['assessment_data'];
                
                // Save assessment data as order item meta
                $item->add_meta_data('_assessment_type', $assessment_data['type'] ?? '');
                $item->add_meta_data('_assessment_id', $assessment_data['id'] ?? '');
                $item->add_meta_data('_assessment_score', $assessment_data['score'] ?? '');
                $item->add_meta_data('_assessment_data', wp_json_encode($assessment_data));
                $item->save();
                
                // Save to order meta as well
                $order->add_meta_data('_has_assessment_data', 'yes');
                $order->add_meta_data('_assessment_type_' . $item_id, $assessment_data['type'] ?? '');
                $order->save();
            }
        }
    }
    
    /**
     * Initialize WooCommerce hooks
     */
    public function init_woocommerce_hooks() {
        if (!$this->is_woocommerce_active()) {
            return;
        }
        
        // Cart and checkout hooks
        add_filter('woocommerce_get_item_data', array($this, 'display_assessment_data_in_cart'), 10, 2);
        add_action('woocommerce_checkout_create_order', array($this, 'save_assessment_data_to_order'), 10, 3);
        
        // Order completion hooks
        add_action('woocommerce_order_status_completed', array($this, 'handle_order_completion'), 10, 1);
        add_action('woocommerce_order_status_processing', array($this, 'handle_order_processing'), 10, 1);
    }
    
    /**
     * Handle order completion
     */
    public function handle_order_completion($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        // Send completion email with assessment results
        $customer_email = $order->get_billing_email();
        $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
        
        if ($customer_email && class_exists('ENNU_Life_Email_System')) {
            $email_system = new ENNU_Life_Email_System();
            
            // Check if order has assessment data
            if ($order->get_meta('_has_assessment_data') === 'yes') {
                $this->send_assessment_purchase_confirmation($order, $customer_email, $customer_name);
            }
        }
    }
    
    /**
     * Handle order processing
     */
    public function handle_order_processing($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        // Trigger any immediate processing needed
        do_action('ennu_order_processing', $order_id, $order);
    }
    
    /**
     * Send assessment purchase confirmation
     */
    private function send_assessment_purchase_confirmation($order, $customer_email, $customer_name) {
        $subject = 'Your ENNU Life Service Purchase Confirmation';
        
        $message = "Dear " . $customer_name . ",\n\n";
        $message .= "Thank you for your purchase! Your order has been completed successfully.\n\n";
        $message .= "ORDER DETAILS:\n";
        $message .= str_repeat("-", 30) . "\n";
        $message .= "Order ID: " . $order->get_order_number() . "\n";
        $message .= "Order Date: " . $order->get_date_created()->format('F j, Y') . "\n";
        $message .= "Total: " . $order->get_formatted_order_total() . "\n\n";
        
        $message .= "SERVICES PURCHASED:\n";
        $message .= str_repeat("-", 30) . "\n";
        
        foreach ($order->get_items() as $item) {
            $message .= "• " . $item->get_name() . "\n";
            
            $assessment_type = $item->get_meta('_assessment_type');
            if ($assessment_type) {
                $message .= "  Based on: " . ucwords(str_replace('-', ' ', $assessment_type)) . "\n";
            }
        }
        
        $message .= "\nOur team will contact you within 24-48 hours to schedule your consultation.\n\n";
        $message .= "Thank you for choosing ENNU Life for your health and wellness journey!\n\n";
        $message .= "Best regards,\n";
        $message .= "The ENNU Life Team";
        
        if (class_exists('ENNU_Life_Email_System')) {
            $email_system = new ENNU_Life_Email_System();
            $email_system->send_email($customer_email, $subject, $message, 'purchase_confirmation');
        }
    }
}

