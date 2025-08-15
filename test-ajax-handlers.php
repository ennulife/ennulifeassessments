<?php
/**
 * Test AJAX Handlers
 * Tests if weight and health goals AJAX handlers are working properly
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in and admin
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please login as admin.');
}

$current_user_id = get_current_user_id();

?>
<!DOCTYPE html>
<html>
<head>
    <title>AJAX Handler Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #ddd; }
        .result { margin: 10px 0; padding: 10px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>AJAX Handler Test</h1>
    
    <div class="test-section">
        <h2>1. Check User Meta Values</h2>
        <?php
        $weight = get_user_meta($current_user_id, 'weight', true);
        $target_weight = get_user_meta($current_user_id, 'target_weight', true);
        $health_goals = get_user_meta($current_user_id, 'ennu_global_health_goals', true);
        ?>
        <div class="result">
            <strong>Current Weight:</strong> <?php echo $weight ?: 'Not set'; ?><br>
            <strong>Target Weight:</strong> <?php echo $target_weight ?: 'Not set'; ?><br>
            <strong>Health Goals:</strong> <?php echo is_array($health_goals) ? implode(', ', $health_goals) : 'None'; ?>
        </div>
    </div>
    
    <div class="test-section">
        <h2>2. Test Weight Save AJAX</h2>
        <button onclick="testWeightSave()">Test Save Weight (150 lbs)</button>
        <button onclick="testTargetWeightSave()">Test Save Target Weight (140 lbs)</button>
        <div id="weight-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>3. Test Health Goals AJAX</h2>
        <button onclick="testHealthGoals()">Test Save Health Goals (weight_loss, energy)</button>
        <div id="goals-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h2>4. Check AJAX Actions Registration</h2>
        <?php
        global $wp_filter;
        
        $weight_action = isset($wp_filter['wp_ajax_ennu_save_weight']) ? 'Registered' : 'NOT REGISTERED';
        $goals_action = isset($wp_filter['wp_ajax_ennu_update_health_goals']) ? 'Registered' : 'NOT REGISTERED';
        ?>
        <div class="result">
            <strong>ennu_save_weight:</strong> <?php echo $weight_action; ?><br>
            <strong>ennu_update_health_goals:</strong> <?php echo $goals_action; ?>
        </div>
    </div>
    
    <div class="test-section">
        <h2>5. Check Classes Loaded</h2>
        <?php
        $weight_class = class_exists('ENNU_Weight_Ajax_Handler') ? 'Loaded' : 'NOT LOADED';
        $goals_class = class_exists('ENNU_Health_Goals_Ajax') ? 'Loaded' : 'NOT LOADED';
        ?>
        <div class="result">
            <strong>ENNU_Weight_Ajax_Handler:</strong> <?php echo $weight_class; ?><br>
            <strong>ENNU_Health_Goals_Ajax:</strong> <?php echo $goals_class; ?>
        </div>
    </div>

    <script>
    function testWeightSave() {
        const data = new FormData();
        data.append('action', 'ennu_save_weight');
        data.append('nonce', '<?php echo wp_create_nonce("ennu_save_weight"); ?>');
        data.append('user_id', '<?php echo $current_user_id; ?>');
        data.append('field', 'current_weight');
        data.append('value', '150');
        
        jQuery('#weight-result').html('Testing weight save...');
        
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                jQuery('#weight-result').html('<div class="success">Success! Weight saved: ' + JSON.stringify(result.data) + '</div>');
                setTimeout(() => location.reload(), 2000);
            } else {
                jQuery('#weight-result').html('<div class="error">Error: ' + (result.data || 'Unknown error') + '</div>');
            }
        })
        .catch(error => {
            jQuery('#weight-result').html('<div class="error">Network error: ' + error + '</div>');
        });
    }
    
    function testTargetWeightSave() {
        const data = new FormData();
        data.append('action', 'ennu_save_weight');
        data.append('nonce', '<?php echo wp_create_nonce("ennu_save_weight"); ?>');
        data.append('user_id', '<?php echo $current_user_id; ?>');
        data.append('field', 'target_weight');
        data.append('value', '140');
        
        jQuery('#weight-result').html('Testing target weight save...');
        
        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                jQuery('#weight-result').html('<div class="success">Success! Target weight saved: ' + JSON.stringify(result.data) + '</div>');
                setTimeout(() => location.reload(), 2000);
            } else {
                jQuery('#weight-result').html('<div class="error">Error: ' + (result.data || 'Unknown error') + '</div>');
            }
        })
        .catch(error => {
            jQuery('#weight-result').html('<div class="error">Network error: ' + error + '</div>');
        });
    }
    
    function testHealthGoals() {
        jQuery.ajax({
            url: '<?php echo admin_url("admin-ajax.php"); ?>',
            type: 'POST',
            data: {
                action: 'ennu_update_health_goals',
                nonce: '<?php echo wp_create_nonce("ennu_health_goals_nonce"); ?>',
                health_goals: ['weight_loss', 'energy']
            },
            success: function(response) {
                if (response.success) {
                    jQuery('#goals-result').html('<div class="success">Success! Goals saved: ' + JSON.stringify(response.data) + '</div>');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    jQuery('#goals-result').html('<div class="error">Error: ' + (response.data.message || 'Unknown error') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                jQuery('#goals-result').html('<div class="error">Network error: ' + error + '</div>');
            }
        });
    }
    </script>
</body>
</html>