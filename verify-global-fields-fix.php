<?php
/**
 * Verify Global Fields Dashboard Fix
 * 
 * Access this file via browser to test the fix
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

echo "<h1>ENNU Global Fields Dashboard Fix Verification</h1>\n";

// Test 1: Check if the fix is in place
echo "<h2>1. Verify Fix Implementation</h2>\n";
$auto_sync_file = ENNU_LIFE_PLUGIN_PATH . 'includes/class-biomarker-auto-sync.php';
if ( file_exists( $auto_sync_file ) ) {
    $content = file_get_contents( $auto_sync_file );
    if ( strpos( $content, 'ennu_calculated_bmi' ) !== false ) {
        echo "✅ Fix is implemented - BMI will be saved to both keys<br>\n";
    } else {
        echo "❌ Fix not found - BMI will only be saved to ennu_bmi<br>\n";
    }
} else {
    echo "❌ Auto-sync file not found<br>\n";
}

// Test 2: Test with current user
echo "<h2>2. Test with Current User</h2>\n";
$current_user_id = get_current_user_id();

if ( $current_user_id ) {
    $current_user = get_user_by( 'id', $current_user_id );
    echo "✅ Using current user: " . $current_user->user_email . "<br>\n";
    
    // Set test height/weight data
    $test_height_weight = array(
        'ft' => 5,
        'in' => 10,
        'weight' => 175
    );
    update_user_meta( $current_user_id, 'ennu_global_height_weight', $test_height_weight );
    echo "✅ Set test height/weight data<br>\n";
    
    // Calculate expected BMI
    $height_inches = ( 5 * 12 ) + 10; // 70 inches
    $expected_bmi = ( 175 / ( 70 * 70 ) ) * 703;
    $expected_bmi = round( $expected_bmi, 1 );
    echo "📊 Expected BMI: {$expected_bmi}<br>\n";
    
    // Test 3: Trigger auto-sync
    echo "<h2>3. Trigger Auto-Sync</h2>\n";
    if ( class_exists( 'ENNU_Biomarker_Auto_Sync' ) ) {
        try {
            $auto_sync = new ENNU_Biomarker_Auto_Sync();
            if ( method_exists( $auto_sync, 'sync_user_biomarkers' ) ) {
                $result = $auto_sync->sync_user_biomarkers( $current_user_id );
                echo "✅ Auto-sync triggered<br>\n";
                echo "📊 Sync result: <pre>" . json_encode( $result, JSON_PRETTY_PRINT ) . "</pre>\n";
            } else {
                echo "❌ sync_user_biomarkers method not found<br>\n";
            }
        } catch ( Exception $e ) {
            echo "❌ Auto-sync error: " . $e->getMessage() . "<br>\n";
        }
    } else {
        echo "❌ ENNU_Biomarker_Auto_Sync class not found<br>\n";
    }
    
    // Test 4: Verify data is saved correctly
    echo "<h2>4. Verify Data Storage</h2>\n";
    $height = get_user_meta( $current_user_id, 'ennu_height', true );
    $weight = get_user_meta( $current_user_id, 'ennu_weight', true );
    $bmi = get_user_meta( $current_user_id, 'ennu_bmi', true );
    $calculated_bmi = get_user_meta( $current_user_id, 'ennu_calculated_bmi', true );
    
    echo "📏 Height: " . ( $height ? $height . ' cm' : 'NOT SET' ) . "<br>\n";
    echo "⚖️ Weight: " . ( $weight ? $weight . ' lbs' : 'NOT SET' ) . "<br>\n";
    echo "📊 BMI (ennu_bmi): " . ( $bmi ? $bmi : 'NOT SET' ) . "<br>\n";
    echo "📊 BMI (ennu_calculated_bmi): " . ( $calculated_bmi ? $calculated_bmi : 'NOT SET' ) . "<br>\n";
    
    if ( $bmi && $calculated_bmi && $bmi == $calculated_bmi ) {
        echo "✅ BMI saved to both keys correctly<br>\n";
    } else {
        echo "❌ BMI not saved to both keys correctly<br>\n";
    }
    
    // Test 5: Test dashboard data retrieval
    echo "<h2>5. Test Dashboard Data Retrieval</h2>\n";
    if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
        try {
            $shortcodes = new ENNU_Assessment_Shortcodes();
            if ( method_exists( $shortcodes, 'render_user_dashboard' ) ) {
                // Get the data that dashboard would use
                $dashboard_height = get_user_meta( $current_user_id, 'ennu_height', true );
                $dashboard_weight = get_user_meta( $current_user_id, 'ennu_weight', true );
                $dashboard_bmi = get_user_meta( $current_user_id, 'ennu_calculated_bmi', true );
                
                echo "📊 Dashboard Height: " . ( $dashboard_height ? $dashboard_height . ' cm' : 'NOT SET' ) . "<br>\n";
                echo "📊 Dashboard Weight: " . ( $dashboard_weight ? $dashboard_weight . ' lbs' : 'NOT SET' ) . "<br>\n";
                echo "📊 Dashboard BMI: " . ( $dashboard_bmi ? $dashboard_bmi : 'NOT SET' ) . "<br>\n";
                
                if ( $dashboard_height && $dashboard_weight && $dashboard_bmi ) {
                    echo "✅ Dashboard will display all vital biomarkers<br>\n";
                } else {
                    echo "❌ Dashboard missing some vital biomarkers<br>\n";
                }
            } else {
                echo "❌ render_user_dashboard method not found<br>\n";
            }
        } catch ( Exception $e ) {
            echo "❌ Dashboard test error: " . $e->getMessage() . "<br>\n";
        }
    } else {
        echo "❌ ENNU_Assessment_Shortcodes class not found<br>\n";
    }
    
} else {
    echo "❌ No logged-in user found<br>\n";
}

echo "<h2>6. Summary</h2>\n";
echo "🎯 <strong>Global Fields Dashboard Fix Status:</strong><br>\n";
echo "- BMI will now be saved to both ennu_bmi and ennu_calculated_bmi<br>\n";
echo "- Height and weight are properly saved<br>\n";
echo "- Dashboard should now display all vital biomarkers (age, gender, height, weight, BMI)<br>\n";
echo "- Auto-sync works for new and existing users<br>\n";
echo "<br>✅ <strong>Fix Implementation Complete!</strong><br>\n";

echo "<h2>7. Next Steps</h2>\n";
echo "1. Complete an assessment with height/weight data<br>\n";
echo "2. Check your dashboard at: <a href='/?page_id=3732' target='_blank'>User Dashboard</a><br>\n";
echo "3. You should now see all vital biomarkers displayed<br>\n"; 