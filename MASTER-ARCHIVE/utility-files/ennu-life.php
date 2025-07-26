// Add test endpoint for instant workflow
add_action('init', function() {
    if (isset($_GET['test_instant_workflow']) && current_user_can('manage_options')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-test-instant-workflow.php';
        ENNU_Test_Instant_Workflow::run_test();
        exit;
    }
}); 