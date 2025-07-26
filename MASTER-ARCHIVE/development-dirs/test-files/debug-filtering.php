<?php
// Debug script to check the filtering issue
require_once '/Applications/MAMP/htdocs/wp-config.php';
require_once __DIR__ . '/ennu-life-plugin.php';

$plugin      = ENNU_Life_Enhanced_Plugin::get_instance();
$definitions = $plugin->get_shortcode_handler()->get_all_assessment_definitions();

echo "=== FILTERING DEBUG ===\n";
echo 'Available assessment keys: ' . implode( ', ', array_keys( $definitions ) ) . "\n\n";

// This is the NEW logic from the admin settings page (FIXED)
$assessment_menu_order = array(
	'hair'                => 'hair',
	'ed-treatment'        => 'ed-treatment',
	'weight-loss'         => 'weight-loss',
	'health'              => 'health',
	'health-optimization' => 'health-optimization',
	'skin'                => 'skin',
	'hormone'             => 'hormone',
	'testosterone'        => 'testosterone',
	'menopause'           => 'menopause',
	'sleep'               => 'sleep',
);

echo "Expected keys in menu order (FIXED):\n";
foreach ( $assessment_menu_order as $slug => $key ) {
	echo "  $slug => $key\n";
}

echo "\nFiltering results (FIXED):\n";
$filtered_assessments = array();
foreach ( $assessment_menu_order as $slug => $key ) {
	if ( in_array( $key, array_keys( $definitions ) ) ) {
		$filtered_assessments[ $slug ] = $key;
		echo "✓ $slug => $key (FOUND)\n";
	} else {
		echo "✗ $slug => $key (NOT FOUND)\n";
	}
}

echo "\nFinal filtered assessments: " . count( $filtered_assessments ) . "\n";
foreach ( $filtered_assessments as $slug => $key ) {
	echo "  $slug => $key\n";
}

// Show what pages would be created
echo "\n=== PAGES THAT WOULD BE CREATED ===\n";
foreach ( $filtered_assessments as $slug => $key ) {
	echo "Form: assessments/$slug\n";
	echo "Results: assessments/$slug/results\n";
	echo "Details: assessments/$slug/details\n";
	echo "Consultation: assessments/$slug/consultation\n";
	echo "---\n";
}


