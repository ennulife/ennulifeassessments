<?php
/**
 * ENNU Life Biomarker Integration Test
 * 
 * Tests the complete integration of all 103 biomarkers from AI medical specialists
 * 
 * @package ENNU_Life_Assessments
 * @version 64.1.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include the main plugin file to load all dependencies
require_once( plugin_dir_path( __FILE__ ) . 'ennu-life-plugin.php' );

class ENNU_Biomarker_Integration_Test {

	/**
	 * Run comprehensive integration test
	 */
	public static function run_test() {
		echo "<h1>🧬 ENNU Life Biomarker Integration Test</h1>";
		echo "<p><strong>Version:</strong> 64.1.0</p>";
		echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
		echo "<hr>";

		// Test 1: Load all biomarker ranges
		self::test_biomarker_loading();

		// Test 2: Verify all 103 biomarkers
		self::test_biomarker_coverage();

		// Test 3: Test range calculations
		self::test_range_calculations();

		// Test 4: Test age/gender adjustments
		self::test_demographic_adjustments();

		// Test 5: Test flag criteria
		self::test_flag_criteria();

		// Test 6: Test scoring algorithms
		self::test_scoring_algorithms();

		echo "<hr>";
		echo "<h2>✅ Integration Test Complete</h2>";
	}

	/**
	 * Test loading all biomarker ranges
	 */
	private static function test_biomarker_loading() {
		echo "<h2>Test 1: Biomarker Range Loading</h2>";

		try {
			$range_manager = new ENNU_Recommended_Range_Manager();
			$biomarker_config = $range_manager->get_biomarker_configuration();

			if ( is_array( $biomarker_config ) && ! empty( $biomarker_config ) ) {
				echo "<p style='color: green;'>✅ Successfully loaded biomarker configuration</p>";
				echo "<p><strong>Total Biomarkers Loaded:</strong> " . count( $biomarker_config ) . "</p>";
			} else {
				echo "<p style='color: red;'>❌ Failed to load biomarker configuration</p>";
			}

		} catch ( Exception $e ) {
			echo "<p style='color: red;'>❌ Error loading biomarker configuration: " . $e->getMessage() . "</p>";
		}
	}

	/**
	 * Test coverage of all 103 biomarkers
	 */
	private static function test_biomarker_coverage() {
		echo "<h2>Test 2: Biomarker Coverage (103 Total)</h2>";

		$range_manager = new ENNU_Recommended_Range_Manager();
		$biomarker_config = $range_manager->get_biomarker_configuration();

		// Expected biomarkers by category
		$expected_biomarkers = array(
			'Cardiovascular' => array(
				'blood_pressure', 'heart_rate', 'cholesterol', 'triglycerides', 'hdl', 'ldl', 'vldl',
				'apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index', 'tmao', 'nmr_lipoprofile',
				'glucose', 'hba1c', 'insulin', 'uric_acid', 'one_five_ag', 'automated_or_manual_cuff', 'apoe_genotype'
			),
			'Hematology' => array(
				'hemoglobin', 'hematocrit', 'rbc', 'wbc', 'platelets', 'mch', 'mchc', 'mcv', 'rdw'
			),
			'Neurology' => array(
				'ptau_217', 'beta_amyloid_ratio', 'gfap', 'vitamin_d', 'vitamin_b12', 'folate', 'tsh', 'free_t3', 'free_t4', 'genotype'
			),
			'Endocrinology' => array(
				'testosterone_free', 'testosterone_total', 'estradiol', 'progesterone', 'shbg', 'cortisol',
				't4', 't3', 'lh', 'fsh', 'dhea', 'prolactin'
			),
			'Health Coaching' => array(
				'fasting_insulin', 'homa_ir', 'glycomark', 'leptin', 'ghrelin', 'adiponectin',
				'weight', 'bmi', 'body_fat_percent', 'waist_measurement', 'neck_measurement',
				'bioelectrical_impedance_or_caliper', 'kg'
			),
			'Sports Medicine' => array(
				'igf_1', 'creatine_kinase', 'grip_strength'
			),
			'Gerontology' => array(
				'telomere_length', 'nad', 'tac', 'mirna_486', 'gut_microbiota_diversity', 'il_6', 'il_18', 'once_lifetime'
			),
			'Nephrology/Hepatology' => array(
				'gfr', 'bun', 'creatinine', 'alt', 'ast', 'alkaline_phosphate', 'albumin'
			),
			'General Practice' => array(
				'ferritin', 'coq10', 'heavy_metals_panel', 'arsenic', 'lead', 'mercury',
				'temperature', 'oral_or_temporal_thermometer', 'sodium', 'potassium', 'chloride',
				'calcium', 'magnesium', 'carbon_dioxide', 'protein'
			)
		);

		$total_expected = 0;
		$total_found = 0;
		$missing_biomarkers = array();

		foreach ( $expected_biomarkers as $category => $biomarkers ) {
			echo "<h3>$category</h3>";
			echo "<p><strong>Expected:</strong> " . count( $biomarkers ) . " biomarkers</p>";
			
			$found_count = 0;
			foreach ( $biomarkers as $biomarker ) {
				if ( isset( $biomarker_config[ $biomarker ] ) ) {
					$found_count++;
					$total_found++;
				} else {
					$missing_biomarkers[] = $biomarker;
				}
			}
			
			$total_expected += count( $biomarkers );
			echo "<p><strong>Found:</strong> $found_count biomarkers</p>";
			
			if ( $found_count === count( $biomarkers ) ) {
				echo "<p style='color: green;'>✅ All $category biomarkers found</p>";
			} else {
				echo "<p style='color: orange;'>⚠️ Missing " . ( count( $biomarkers ) - $found_count ) . " biomarkers</p>";
			}
		}

		echo "<h3>Coverage Summary</h3>";
		echo "<p><strong>Total Expected:</strong> $total_expected biomarkers</p>";
		echo "<p><strong>Total Found:</strong> $total_found biomarkers</p>";
		
		if ( $total_found === $total_expected ) {
			echo "<p style='color: green;'>✅ 100% Coverage Achieved!</p>";
		} else {
			echo "<p style='color: red;'>❌ Missing " . ( $total_expected - $total_found ) . " biomarkers</p>";
			echo "<p><strong>Missing Biomarkers:</strong> " . implode( ', ', $missing_biomarkers ) . "</p>";
		}
	}

	/**
	 * Test range calculations
	 */
	private static function test_range_calculations() {
		echo "<h2>Test 3: Range Calculations</h2>";

		$range_manager = new ENNU_Recommended_Range_Manager();
		
		// Test a few key biomarkers
		$test_biomarkers = array( 'testosterone_total', 'vitamin_d', 'tsh', 'glucose' );
		
		foreach ( $test_biomarkers as $biomarker ) {
			$range_data = $range_manager->get_recommended_range( $biomarker );
			
			if ( ! isset( $range_data['error'] ) ) {
				echo "<p style='color: green;'>✅ $biomarker: " . $range_data['display_name'] . "</p>";
				echo "<ul>";
				echo "<li>Optimal: " . $range_data['optimal_min'] . " - " . $range_data['optimal_max'] . " " . $range_data['unit'] . "</li>";
				echo "<li>Normal: " . $range_data['normal_min'] . " - " . $range_data['normal_max'] . " " . $range_data['unit'] . "</li>";
				echo "</ul>";
			} else {
				echo "<p style='color: red;'>❌ $biomarker: " . $range_data['error'] . "</p>";
			}
		}
	}

	/**
	 * Test age/gender adjustments
	 */
	private static function test_demographic_adjustments() {
		echo "<h2>Test 4: Age/Gender Adjustments</h2>";

		$range_manager = new ENNU_Recommended_Range_Manager();
		
		// Test testosterone with different demographics
		$test_cases = array(
			array( 'age' => 25, 'gender' => 'male', 'description' => 'Young Male' ),
			array( 'age' => 65, 'gender' => 'male', 'description' => 'Senior Male' ),
			array( 'age' => 35, 'gender' => 'female', 'description' => 'Adult Female' ),
		);

		foreach ( $test_cases as $test_case ) {
			$range_data = $range_manager->get_recommended_range( 'testosterone_total', $test_case );
			
			if ( ! isset( $range_data['error'] ) ) {
				echo "<p style='color: green;'>✅ " . $test_case['description'] . " Testosterone</p>";
				echo "<ul>";
				echo "<li>Optimal: " . $range_data['optimal_min'] . " - " . $range_data['optimal_max'] . " " . $range_data['unit'] . "</li>";
				echo "<li>Age Group: " . $range_data['age_group'] . "</li>";
				echo "<li>Gender: " . $range_data['gender'] . "</li>";
				echo "</ul>";
			} else {
				echo "<p style='color: red;'>❌ " . $test_case['description'] . ": " . $range_data['error'] . "</p>";
			}
		}
	}

	/**
	 * Test flag criteria
	 */
	private static function test_flag_criteria() {
		echo "<h2>Test 5: Flag Criteria</h2>";

		$range_manager = new ENNU_Recommended_Range_Manager();
		$biomarker_config = $range_manager->get_biomarker_configuration();

		$biomarkers_with_flags = 0;
		$total_biomarkers = count( $biomarker_config );

		foreach ( $biomarker_config as $biomarker_name => $config ) {
			if ( isset( $config['flag_criteria'] ) && ! empty( $config['flag_criteria'] ) ) {
				$biomarkers_with_flags++;
			}
		}

		echo "<p><strong>Biomarkers with Flag Criteria:</strong> $biomarkers_with_flags / $total_biomarkers</p>";
		
		if ( $biomarkers_with_flags === $total_biomarkers ) {
			echo "<p style='color: green;'>✅ All biomarkers have flag criteria</p>";
		} else {
			echo "<p style='color: orange;'>⚠️ " . ( $total_biomarkers - $biomarkers_with_flags ) . " biomarkers missing flag criteria</p>";
		}
	}

	/**
	 * Test scoring algorithms
	 */
	private static function test_scoring_algorithms() {
		echo "<h2>Test 6: Scoring Algorithms</h2>";

		$range_manager = new ENNU_Recommended_Range_Manager();
		$biomarker_config = $range_manager->get_biomarker_configuration();

		$biomarkers_with_scoring = 0;
		$total_biomarkers = count( $biomarker_config );

		foreach ( $biomarker_config as $biomarker_name => $config ) {
			if ( isset( $config['scoring_algorithm'] ) && ! empty( $config['scoring_algorithm'] ) ) {
				$biomarkers_with_scoring++;
			}
		}

		echo "<p><strong>Biomarkers with Scoring Algorithms:</strong> $biomarkers_with_scoring / $total_biomarkers</p>";
		
		if ( $biomarkers_with_scoring === $total_biomarkers ) {
			echo "<p style='color: green;'>✅ All biomarkers have scoring algorithms</p>";
		} else {
			echo "<p style='color: orange;'>⚠️ " . ( $total_biomarkers - $biomarkers_with_scoring ) . " biomarkers missing scoring algorithms</p>";
		}
	}
}

// Run the test if accessed directly
if ( isset( $_GET['run_test'] ) && current_user_can( 'manage_options' ) ) {
	ENNU_Biomarker_Integration_Test::run_test();
} else {
	echo "<h1>🧬 ENNU Life Biomarker Integration Test</h1>";
	echo "<p>To run the test, add <code>?run_test=1</code> to the URL and ensure you have admin privileges.</p>";
	echo "<p><a href='?run_test=1' class='button button-primary'>Run Integration Test</a></p>";
}
?> 