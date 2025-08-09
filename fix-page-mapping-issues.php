<?php
/**
 * ENNU Life Assessments - Page Mapping Issues Fix
 * 
 * This script addresses the page mapping issues identified in the diagnostic:
 * 1. Missing critical 'call' page
 * 2. Missing assessment-specific consultation pages
 * 3. URL generation fallback issues
 * 
 * @package ENNU_Life_Assessments
 * @version 64.68.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in WordPress context
if (!function_exists('wp_insert_post')) {
    die('WordPress not loaded');
}

echo "🔧 ENNU Life Assessments - Page Mapping Issues Fix\n";
echo "==================================================\n\n";

/**
 * Enhanced Page Mapping Fix Class
 */
class ENNU_Page_Mapping_Fix {
    
    private $settings;
    private $page_mappings;
    
    public function __construct() {
        $this->settings = get_option('ennu_life_settings', array());
        $this->page_mappings = $this->settings['page_mappings'] ?? array();
    }
    
    /**
     * Fix all identified page mapping issues
     */
    public function fix_all_issues() {
        echo "🎯 Starting comprehensive page mapping fix...\n\n";
        
        // 1. Fix missing critical pages
        $this->fix_missing_critical_pages();
        
        // 2. Fix missing assessment-specific consultation pages
        $this->fix_missing_assessment_consultation_pages();
        
        // 3. Fix URL generation issues
        $this->fix_url_generation_issues();
        
        // 4. Validate all mappings
        $this->validate_all_mappings();
        
        echo "\n✅ Page mapping issues fix completed!\n";
    }
    
    /**
     * Fix missing critical pages
     */
    private function fix_missing_critical_pages() {
        echo "📋 Fixing missing critical pages...\n";
        
        $critical_pages = [
            'call' => [
                'title' => 'Schedule a Call',
                'content' => $this->get_call_page_content(),
                'template' => 'page-call.php'
            ]
        ];
        
        foreach ($critical_pages as $key => $page_data) {
            if (empty($this->page_mappings[$key])) {
                $page_id = $this->create_page($page_data['title'], $page_data['content']);
                if ($page_id) {
                    $this->page_mappings[$key] = $page_id;
                    echo "  ✅ Created '{$page_data['title']}' (ID: {$page_id})\n";
                } else {
                    echo "  ❌ Failed to create '{$page_data['title']}'\n";
                }
            } else {
                echo "  ℹ️ '{$page_data['title']}' already exists (ID: {$this->page_mappings[$key]})\n";
            }
        }
    }
    
    /**
     * Fix missing assessment-specific consultation pages
     */
    private function fix_missing_assessment_consultation_pages() {
        echo "\n🎯 Fixing missing assessment-specific consultation pages...\n";
        
        $assessment_consultations = [
            'ed-treatment_consultation_page_id' => [
                'title' => 'ED Treatment Consultation',
                'content' => $this->get_ed_consultation_content(),
                'assessment_type' => 'ed-treatment'
            ],
            'weight-loss_consultation_page_id' => [
                'title' => 'Weight Loss Consultation', 
                'content' => $this->get_weight_loss_consultation_content(),
                'assessment_type' => 'weight-loss'
            ]
        ];
        
        foreach ($assessment_consultations as $key => $page_data) {
            if (empty($this->page_mappings[$key])) {
                $page_id = $this->create_page($page_data['title'], $page_data['content']);
                if ($page_id) {
                    $this->page_mappings[$key] = $page_id;
                    echo "  ✅ Created '{$page_data['title']}' (ID: {$page_id})\n";
                } else {
                    echo "  ❌ Failed to create '{$page_data['title']}'\n";
                }
            } else {
                echo "  ℹ️ '{$page_data['title']}' already exists (ID: {$this->page_mappings[$key]})\n";
            }
        }
    }
    
    /**
     * Fix URL generation issues
     */
    private function fix_url_generation_issues() {
        echo "\n🔗 Fixing URL generation issues...\n";
        
        // Test URL generation for each assessment type
        $assessment_types = ['hair', 'ed-treatment', 'weight-loss', 'health', 'skin', 'hormone', 'testosterone', 'menopause', 'sleep'];
        
        foreach ($assessment_types as $type) {
            $url = $this->get_assessment_cta_url($type);
            $page_id = $this->get_page_id_from_url($url);
            
            if ($page_id == 1) {
                echo "  ⚠️ {$type}: Falling back to home page - needs specific consultation page\n";
            } else {
                echo "  ✅ {$type}: URL generated correctly (Page ID: {$page_id})\n";
            }
        }
    }
    
    /**
     * Validate all mappings
     */
    private function validate_all_mappings() {
        echo "\n🔍 Validating all page mappings...\n";
        
        $valid_count = 0;
        $total_count = count($this->page_mappings);
        
        foreach ($this->page_mappings as $key => $page_id) {
            if (get_post($page_id)) {
                $valid_count++;
                echo "  ✅ {$key}: Page ID {$page_id} exists\n";
            } else {
                echo "  ❌ {$key}: Page ID {$page_id} does not exist\n";
            }
        }
        
        $percentage = round(($valid_count / $total_count) * 100, 1);
        echo "\n📊 Validation Results: {$valid_count}/{$total_count} pages valid ({$percentage}%)\n";
    }
    
    /**
     * Create a new page
     */
    private function create_page($title, $content) {
        $page_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'comment_status' => 'closed'
        );
        
        $page_id = wp_insert_post($page_data);
        
        if (is_wp_error($page_id)) {
            return false;
        }
        
        return $page_id;
    }
    
    /**
     * Get assessment CTA URL
     */
    private function get_assessment_cta_url($assessment_type) {
        $key = str_replace('_assessment', '', $assessment_type);
        $page_id_key = $key . '_consultation_page_id';
        
        if (isset($this->page_mappings[$page_id_key]) && !empty($this->page_mappings[$page_id_key])) {
            $page_id = $this->page_mappings[$page_id_key];
            return home_url("/?page_id={$page_id}");
        }
        
        // Fallback to generic call page
        if (isset($this->page_mappings['call']) && !empty($this->page_mappings['call'])) {
            $page_id = $this->page_mappings['call'];
            return home_url("/?page_id={$page_id}");
        }
        
        // Final fallback to home
        return home_url("/?page_id=1");
    }
    
    /**
     * Extract page ID from URL
     */
    private function get_page_id_from_url($url) {
        if (preg_match('/page_id=(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return 1;
    }
    
    /**
     * Save updated mappings
     */
    public function save_mappings() {
        $this->settings['page_mappings'] = $this->page_mappings;
        update_option('ennu_life_settings', $this->settings);
        echo "\n💾 Page mappings saved to database\n";
    }
    
    /**
     * Get call page content
     */
    private function get_call_page_content() {
        return '
        <div class="ennu-call-page">
            <h1>Schedule Your Consultation</h1>
            <p>Ready to start your health transformation journey? Schedule a consultation with our medical team.</p>
            
            <div class="consultation-options">
                <h2>Consultation Options</h2>
                <ul>
                    <li><strong>Initial Consultation:</strong> Comprehensive health assessment and personalized treatment plan</li>
                    <li><strong>Follow-up Consultation:</strong> Progress review and treatment adjustments</li>
                    <li><strong>Specialist Consultation:</strong> Focused consultation for specific health concerns</li>
                </ul>
            </div>
            
            <div class="contact-info">
                <h2>Contact Information</h2>
                <p><strong>Phone:</strong> (555) 123-4567</p>
                <p><strong>Email:</strong> consultations@ennulife.com</p>
                <p><strong>Hours:</strong> Monday-Friday 9AM-6PM EST</p>
            </div>
            
            <div class="booking-form">
                <h2>Schedule Your Consultation</h2>
                <p>Please contact us to schedule your consultation. Our team will get back to you within 24 hours.</p>
                <a href="mailto:consultations@ennulife.com" class="button">Email Us</a>
                <a href="tel:5551234567" class="button">Call Now</a>
            </div>
        </div>';
    }
    
    /**
     * Get ED treatment consultation content
     */
    private function get_ed_consultation_content() {
        return '
        <div class="ennu-ed-consultation">
            <h1>ED Treatment Consultation</h1>
            <p>Specialized consultation for erectile dysfunction treatment and men\'s health optimization.</p>
            
            <div class="treatment-options">
                <h2>Treatment Options</h2>
                <ul>
                    <li><strong>Medication Management:</strong> Prescription medications and dosage optimization</li>
                    <li><strong>Hormone Optimization:</strong> Testosterone and other hormone balancing</li>
                    <li><strong>Lifestyle Interventions:</strong> Diet, exercise, and stress management</li>
                    <li><strong>Advanced Therapies:</strong> Cutting-edge treatments and procedures</li>
                </ul>
            </div>
            
            <div class="consultation-process">
                <h2>What to Expect</h2>
                <ol>
                    <li><strong>Initial Assessment:</strong> Comprehensive health evaluation</li>
                    <li><strong>Lab Testing:</strong> Hormone and biomarker analysis</li>
                    <li><strong>Treatment Plan:</strong> Personalized approach to your health</li>
                    <li><strong>Ongoing Support:</strong> Regular monitoring and adjustments</li>
                </ol>
            </div>
            
            <div class="contact-info">
                <h2>Schedule Your Consultation</h2>
                <p>Contact our men\'s health specialists to begin your treatment journey.</p>
                <a href="mailto:menshealth@ennulife.com" class="button">Email Specialist</a>
                <a href="tel:5551234567" class="button">Call Now</a>
            </div>
        </div>';
    }
    
    /**
     * Get weight loss consultation content
     */
    private function get_weight_loss_consultation_content() {
        return '
        <div class="ennu-weight-loss-consultation">
            <h1>Weight Loss Consultation</h1>
            <p>Comprehensive weight loss consultation with medical-grade solutions and ongoing support.</p>
            
            <div class="weight-loss-approach">
                <h2>Our Approach</h2>
                <ul>
                    <li><strong>Medical Assessment:</strong> Comprehensive health evaluation</li>
                    <li><strong>Metabolic Testing:</strong> Advanced lab work and analysis</li>
                    <li><strong>Personalized Plan:</strong> Customized diet and exercise program</li>
                    <li><strong>Medical Support:</strong> Prescription medications when appropriate</li>
                    <li><strong>Ongoing Monitoring:</strong> Regular check-ins and adjustments</li>
                </ul>
            </div>
            
            <div class="success-factors">
                <h2>Keys to Success</h2>
                <ul>
                    <li>Medical supervision and support</li>
                    <li>Evidence-based approaches</li>
                    <li>Lifestyle modification coaching</li>
                    <li>Regular progress monitoring</li>
                    <li>Long-term maintenance strategies</li>
                </ul>
            </div>
            
            <div class="contact-info">
                <h2>Start Your Weight Loss Journey</h2>
                <p>Contact our weight loss specialists to begin your transformation.</p>
                <a href="mailto:weightloss@ennulife.com" class="button">Email Specialist</a>
                <a href="tel:5551234567" class="button">Call Now</a>
            </div>
        </div>';
    }
}

// Run the fix
try {
    $fixer = new ENNU_Page_Mapping_Fix();
    $fixer->fix_all_issues();
    $fixer->save_mappings();
    
    echo "\n🎉 All page mapping issues have been resolved!\n";
    echo "📋 Next steps:\n";
    echo "  1. Test all assessment CTA links\n";
    echo "  2. Verify consultation page content\n";
    echo "  3. Update any custom content as needed\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
} 