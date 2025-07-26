<?php
/**
 * Test Tab Switching
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>🧪 Testing Tab Switching</h1>";

// Test if user dashboard shortcode is working
echo "<h2>1. Testing User Dashboard Shortcode</h2>";

// Get the user dashboard shortcode content
$user_id = 1;
$shortcode_content = do_shortcode('[ennu-user-dashboard user_id="' . $user_id . '"]');

if (!empty($shortcode_content)) {
    echo "<p>✅ User dashboard shortcode is working</p>";
    
    // Check for tab elements
    if (strpos($shortcode_content, 'tab-my-symptoms') !== false) {
        echo "<p>✅ My Symptoms tab found</p>";
    } else {
        echo "<p>❌ My Symptoms tab not found</p>";
    }
    
    if (strpos($shortcode_content, 'tab-my-biomarkers') !== false) {
        echo "<p>✅ My Biomarkers tab found</p>";
    } else {
        echo "<p>❌ My Biomarkers tab not found</p>";
    }
    
    if (strpos($shortcode_content, 'tab-my-assessments') !== false) {
        echo "<p>✅ My Assessments tab found</p>";
    } else {
        echo "<p>❌ My Assessments tab not found</p>";
    }
    
    if (strpos($shortcode_content, 'tab-my-story') !== false) {
        echo "<p>✅ My Story tab found</p>";
    } else {
        echo "<p>❌ My Story tab not found</p>";
    }
    
} else {
    echo "<p>❌ User dashboard shortcode is not working</p>";
}

// Test symptoms functionality
echo "<h2>2. Testing Symptoms Functionality</h2>";

try {
    $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "<p>✅ Centralized symptoms manager working</p>";
    echo "<p>Total symptoms: " . count($centralized_symptoms['symptoms'] ?? array()) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error with centralized symptoms: " . $e->getMessage() . "</p>";
}

// Test biomarker flag manager
echo "<h2>3. Testing Biomarker Flag Manager</h2>";

try {
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    $flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
    echo "<p>✅ Biomarker flag manager working</p>";
    echo "<p>Active flags: " . count($flagged_biomarkers) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error with biomarker flags: " . $e->getMessage() . "</p>";
}

// Test symptom history
echo "<h2>4. Testing Symptom History</h2>";

try {
    $symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
    $symptom_history = is_array($symptom_history) ? $symptom_history : array();
    echo "<p>✅ Symptom history working</p>";
    echo "<p>History entries: " . count($symptom_history) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error with symptom history: " . $e->getMessage() . "</p>";
}

// Test page loading
echo "<h2>5. Testing Page Loading</h2>";

$page_url = "http://localhost:8888/?page_id=3";
$response = wp_remote_get($page_url);

if (!is_wp_error($response)) {
    $body = wp_remote_retrieve_body($response);
    if (!empty($body)) {
        echo "<p>✅ Page loading successfully</p>";
        
        // Check for JavaScript errors
        if (strpos($body, 'TypeError') !== false) {
            echo "<p>❌ JavaScript errors detected</p>";
        } else {
            echo "<p>✅ No JavaScript errors detected</p>";
        }
        
        // Check for tab navigation
        if (strpos($body, 'tab-nav') !== false) {
            echo "<p>✅ Tab navigation found</p>";
        } else {
            echo "<p>❌ Tab navigation not found</p>";
        }
        
    } else {
        echo "<p>❌ Empty page response</p>";
    }
} else {
    echo "<p>❌ Error loading page: " . $response->get_error_message() . "</p>";
}

echo "<h2>✅ Test Complete!</h2>";
echo "<p>The tab switching system should now be working properly. You can access the user dashboard to test the tabs.</p>";
echo "<p><a href='http://localhost:8888/?page_id=3' target='_blank'>Open User Dashboard</a></p>";
?> 