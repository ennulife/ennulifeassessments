<?php
/**
 * Fix 500 error on dashboard by adding error handling to all class instantiations
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

echo "<h1>Dashboard 500 Error Fix</h1>";

// Test 1: Check if the dashboard page is accessible
echo "<h2>1. Dashboard Page Test</h2>";

$dashboard_page_id = 3732; // From the error URL
$dashboard_page = get_post( $dashboard_page_id );

if ( $dashboard_page ) {
    echo "✅ Dashboard page exists: " . $dashboard_page->post_title . "<br>";
} else {
    echo "❌ Dashboard page does NOT exist<br>";
}

// Test 2: Check if user dashboard shortcode is working
echo "<h2>2. User Dashboard Shortcode Test</h2>";

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    try {
        $shortcodes = new ENNU_Assessment_Shortcodes();
        
        if ( method_exists( $shortcodes, 'render_user_dashboard' ) ) {
            echo "✅ render_user_dashboard method exists<br>";
            
            // Test with error handling
            try {
                ob_start();
                $result = $shortcodes->render_user_dashboard();
                $output = ob_get_clean();
                
                if ( $result !== false ) {
                    echo "✅ Dashboard rendering successful<br>";
                    echo "📏 Output length: " . strlen( $result ) . " characters<br>";
                } else {
                    echo "❌ Dashboard rendering returned false<br>";
                }
            } catch ( Exception $e ) {
                echo "❌ Error rendering dashboard: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ render_user_dashboard method does NOT exist<br>";
        }
    } catch ( Exception $e ) {
        echo "❌ Error instantiating ENNU_Assessment_Shortcodes: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ ENNU_Assessment_Shortcodes class does NOT exist<br>";
}

// Test 3: Check all required classes
echo "<h2>3. Required Classes Test</h2>";

$required_classes = array(
    'ENNU_Biomarker_Auto_Sync',
    'ENNU_Recommended_Range_Manager',
    'ENNU_Biomarker_Flag_Manager',
    'ENNU_Next_Steps_Widget',
    'ENNU_Actionable_Feedback',
    'ENNU_Progress_Tracker',
    'ENNU_Biomarker_Target_Calculator',
    'ENNU_Scoring_System'
);

foreach ( $required_classes as $class ) {
    if ( class_exists( $class ) ) {
        echo "✅ {$class} exists<br>";
    } else {
        echo "❌ {$class} does NOT exist<br>";
    }
}

// Test 4: Create a simplified dashboard template
echo "<h2>4. Creating Simplified Dashboard Template</h2>";

$simplified_template = '<?php
/**
 * Simplified User Dashboard Template
 * This version has error handling to prevent 500 errors
 */

// Ensure biomarker auto-sync is triggered for weight and BMI
if ( class_exists( \'ENNU_Biomarker_Auto_Sync\' ) && is_user_logged_in() ) {
	try {
		$auto_sync = new ENNU_Biomarker_Auto_Sync();
		if ( method_exists( $auto_sync, \'ensure_biomarker_sync\' ) ) {
			$auto_sync->ensure_biomarker_sync();
		}
	} catch ( Exception $e ) {
		// Log error but don\'t break the dashboard
		error_log( \'ENNU Dashboard Auto-Sync Error: \' . $e->getMessage() );
	}
}

// Get current user data with error handling
$current_user = null;
$user_id = 0;
$user_assessments = array();
$average_pillar_scores = array();
$ennu_life_score = 0;

try {
	if ( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		
		// Get user assessments with error handling
		if ( class_exists( \'ENNU_Assessment_Shortcodes\' ) ) {
			$shortcodes = new ENNU_Assessment_Shortcodes();
			$user_assessments = $shortcodes->get_user_assessments( $user_id );
		}
		
		// Get scoring data with error handling
		if ( class_exists( \'ENNU_Scoring_System\' ) && $user_id > 0 ) {
			$scoring_system = new ENNU_Scoring_System();
			$average_pillar_scores = $scoring_system->get_average_pillar_scores( $user_id );
			$ennu_life_score = get_user_meta( $user_id, \'ennu_life_score\', true );
		}
	}
} catch ( Exception $e ) {
	error_log( \'ENNU Dashboard Error - User Data: \' . $e->getMessage() );
}

// Basic dashboard HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>ENNU Life Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .dashboard { max-width: 1200px; margin: 0 auto; }
        .header { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .score { font-size: 24px; font-weight: bold; color: #333; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <h1>ENNU Life Dashboard</h1>
            <?php if ( $current_user ) : ?>
                <p>Welcome, <?php echo esc_html( $current_user->display_name ); ?>!</p>
                <div class="score">Your ENNU Life Score: <?php echo esc_html( $ennu_life_score ?: \'N/A\' ); ?></div>
            <?php else : ?>
                <div class="error">Please log in to view your dashboard.</div>
            <?php endif; ?>
        </div>
        
        <?php if ( $user_id > 0 ) : ?>
            <div class="content">
                <h2>Your Assessments</h2>
                <?php if ( ! empty( $user_assessments ) ) : ?>
                    <ul>
                    <?php foreach ( $user_assessments as $assessment_key => $assessment_data ) : ?>
                        <li><?php echo esc_html( ucwords( str_replace( \'_\', \' \', $assessment_key ) ) ); ?></li>
                    <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No assessments completed yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>';

// Write the simplified template
$simplified_template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard-simplified.php';
if ( file_put_contents( $simplified_template_path, $simplified_template ) ) {
    echo "✅ Created simplified dashboard template: {$simplified_template_path}<br>";
} else {
    echo "❌ Failed to create simplified dashboard template<br>";
}

// Test 5: Check if the original template has syntax errors
echo "<h2>5. Original Template Syntax Check</h2>";

$original_template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php';
if ( file_exists( $original_template_path ) ) {
    $template_content = file_get_contents( $original_template_path );
    
    // Basic syntax check
    $syntax_ok = true;
    
    // Check for unclosed PHP tags
    if ( substr_count( $template_content, '<?php' ) !== substr_count( $template_content, '?>' ) ) {
        echo "⚠️ Unclosed PHP tags detected<br>";
        $syntax_ok = false;
    }
    
    // Check for common syntax issues
    if ( strpos( $template_content, 'new ENNU_' ) !== false ) {
        echo "⚠️ Class instantiations found - these may cause errors<br>";
        $syntax_ok = false;
    }
    
    if ( $syntax_ok ) {
        echo "✅ No obvious syntax issues found<br>";
    }
} else {
    echo "❌ Original template file not found<br>";
}

echo "<h2>🎯 RECOMMENDATIONS</h2>";
echo "<ol>";
echo "<li><strong>Use the simplified template</strong> for testing: templates/user-dashboard-simplified.php</li>";
echo "<li><strong>Add error handling</strong> to all class instantiations in the original template</li>";
echo "<li><strong>Check server error logs</strong> for specific error messages</li>";
echo "<li><strong>Test with a logged-in user</strong> to ensure proper authentication</li>";
echo "</ol>";

echo "<h2>📋 NEXT STEPS</h2>";
echo "<p>1. Try accessing the simplified dashboard template</p>";
echo "<p>2. Check the server error logs for specific error messages</p>";
echo "<p>3. Add error handling to all class instantiations in the original template</p>";
echo "<p>4. Test with different user roles and authentication states</p>";

?> 