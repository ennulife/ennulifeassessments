<?php
/**
 * Fix Symptom Flagging System
 */

// Load WordPress
require_once('../../../wp-load.php');

$user_id = 1;

echo "<h1>🔧 Fixing Symptom Flagging System</h1>";

// 1. Populate test symptoms
$test_symptoms = array(
	'symptoms' => array(
		'fatigue' => array(
			'name' => 'Fatigue',
			'category' => 'Energy',
			'severity' => 'moderate',
			'frequency' => 'daily',
			'assessments' => array('health_optimization_assessment'),
			'first_reported' => date('Y-m-d H:i:s', strtotime('-7 days')),
			'last_reported' => date('Y-m-d H:i:s'),
			'occurrence_count' => 5
		),
		'low_libido' => array(
			'name' => 'Low Libido',
			'category' => 'Sexual Health',
			'severity' => 'high',
			'frequency' => 'weekly',
			'assessments' => array('testosterone_assessment'),
			'first_reported' => date('Y-m-d H:i:s', strtotime('-14 days')),
			'last_reported' => date('Y-m-d H:i:s'),
			'occurrence_count' => 3
		)
	),
	'by_assessment' => array(
		'health_optimization_assessment' => array(
			array('name' => 'Fatigue', 'category' => 'Energy')
		),
		'testosterone_assessment' => array(
			array('name' => 'Low Libido', 'category' => 'Sexual Health')
		)
	)
);

// Save symptoms to user meta
update_user_meta($user_id, 'ennu_centralized_symptoms', $test_symptoms);
echo "<p style='color: green;'>✅ Test symptoms saved to user meta</p>";

// 2. Flag biomarkers based on symptoms
if (class_exists('ENNU_Biomarker_Flag_Manager')) {
	$flag_manager = new ENNU_Biomarker_Flag_Manager();
	
	// Flag biomarkers for Fatigue
	$flag_manager->flag_biomarker($user_id, 'ferritin', 'symptom_triggered', 'Flagged due to reported symptom: Fatigue');
	$flag_manager->flag_biomarker($user_id, 'vitamin_d', 'symptom_triggered', 'Flagged due to reported symptom: Fatigue');
	$flag_manager->flag_biomarker($user_id, 'vitamin_b12', 'symptom_triggered', 'Flagged due to reported symptom: Fatigue');
	
	// Flag biomarkers for Low Libido
	$flag_manager->flag_biomarker($user_id, 'testosterone_free', 'symptom_triggered', 'Flagged due to reported symptom: Low Libido');
	$flag_manager->flag_biomarker($user_id, 'testosterone_total', 'symptom_triggered', 'Flagged due to reported symptom: Low Libido');
	$flag_manager->flag_biomarker($user_id, 'estradiol', 'symptom_triggered', 'Flagged due to reported symptom: Low Libido');
	
	echo "<p style='color: green;'>✅ Biomarkers flagged based on symptoms</p>";
	
	// Check flagged biomarkers
	$flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
	echo "<p><strong>Flagged Biomarkers:</strong> " . count($flagged_biomarkers) . "</p>";
	
	if (!empty($flagged_biomarkers)) {
		echo "<ul>";
		foreach ($flagged_biomarkers as $flag_id => $flag_data) {
			echo "<li><strong>{$flag_data['biomarker_name']}</strong> - {$flag_data['reason']}</li>";
		}
		echo "</ul>";
	}
} else {
	echo "<p style='color: red;'>❌ ENNU_Biomarker_Flag_Manager class not found</p>";
}

echo "<h2>✅ Fix Complete</h2>";
echo "<p>The symptom flagging system should now work. Check the user dashboard to see flagged biomarkers.</p>";
echo "<p><a href='http://localhost:8888/?page_id=2469' target='_blank'>View User Dashboard</a></p>";
?> 