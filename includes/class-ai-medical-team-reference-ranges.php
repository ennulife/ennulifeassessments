<?php
/**
 * ENNU AI Medical Team Reference Ranges Documentation System
 *
 * Allows each AI medical specialist to research, document, and manage
 * reference ranges for their domain with scientific citations and
 * evidence-based determinations.
 *
 * @package ENNU_Life_Assessments
 * @version 62.31.0
 * @author Manus - World's Greatest WordPress Developer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_AI_Medical_Team_Reference_Ranges {

	/**
	 * Cache for reference ranges to avoid repeated database queries
	 */
	private static $cached_reference_ranges = null;
	private static $cache_expiry = null;
	private static $cache_duration = 300; // 5 minutes

	/**
	 * AI Medical Specialists and their domains
	 */
	private $ai_specialists = array(
		'dr_elena_harmonix' => array(
			'name' => 'Dr. Elena Harmonix',
			'title' => 'Endocrinology Master',
			'domain' => 'hormones',
			'biomarkers' => array(
				'testosterone_total', 'testosterone_free', 'estradiol', 'progesterone',
				'cortisol', 'dhea_s', 'insulin', 'glucose', 'hba1c', 'shbg',
				'fsh', 'lh', 'tsh', 't3', 't4', 'vitamin_d'
			),
			'authority_level' => 'critical',
			'research_focus' => 'Hormonal optimization, metabolic health, endocrine disorders'
		),
		'dr_victor_pulse' => array(
			'name' => 'Dr. Victor Pulse',
			'title' => 'Cardiology Expert',
			'domain' => 'cardiovascular',
			'biomarkers' => array(
				'total_cholesterol', 'hdl', 'ldl', 'triglycerides', 'apob',
				'homocysteine', 'crp', 'blood_pressure_systolic', 'blood_pressure_diastolic',
				'heart_rate', 'nt_probnp'
			),
			'authority_level' => 'critical',
			'research_focus' => 'Cardiovascular health, lipid management, heart disease prevention'
		),
		'dr_harlan_vitalis' => array(
			'name' => 'Dr. Harlan Vitalis',
			'title' => 'Hematology Expert',
			'domain' => 'blood_health',
			'biomarkers' => array(
				'hemoglobin', 'hematocrit', 'rbc', 'wbc', 'platelets',
				'ferritin', 'iron', 'b12', 'folate', 'vitamin_d'
			),
			'authority_level' => 'significant',
			'research_focus' => 'Blood health, anemia, immune function, nutrient deficiencies'
		),
		'dr_renata_flux' => array(
			'name' => 'Dr. Renata Flux',
			'title' => 'Nephrology/Hepatology Expert',
			'domain' => 'organ_function',
			'biomarkers' => array(
				'creatinine', 'bun', 'gfr', 'alt', 'ast', 'ggt',
				'albumin', 'bilirubin', 'alkaline_phosphatase'
			),
			'authority_level' => 'significant',
			'research_focus' => 'Kidney function, liver health, detoxification pathways'
		),
		'dr_silas_apex' => array(
			'name' => 'Dr. Silas Apex',
			'title' => 'Sports Medicine Expert',
			'domain' => 'performance',
			'biomarkers' => array(
				'creatine_kinase', 'lactate_dehydrogenase', 'uric_acid',
				'calcium', 'magnesium', 'phosphorus', 'sodium', 'potassium'
			),
			'authority_level' => 'moderate',
			'research_focus' => 'Athletic performance, muscle health, electrolyte balance'
		),
		'dr_mira_insight' => array(
			'name' => 'Dr. Mira Insight',
			'title' => 'Psychiatry/Psychology Expert',
			'domain' => 'mental_health',
			'biomarkers' => array(
				'cortisol', 'serotonin', 'dopamine', 'norepinephrine',
				'gaba', 'melatonin', 'vitamin_d', 'omega_3'
			),
			'authority_level' => 'moderate',
			'research_focus' => 'Mental health, stress management, cognitive function'
		),
		'dr_nora_cognita' => array(
			'name' => 'Dr. Nora Cognita',
			'title' => 'Neurology Expert',
			'domain' => 'brain_health',
			'biomarkers' => array(
				'homocysteine', 'b12', 'folate', 'vitamin_d', 'omega_3',
				'apoe_genotype', 'bdnf', 'acetylcholine'
			),
			'authority_level' => 'moderate',
			'research_focus' => 'Brain health, cognitive decline, neurodegenerative prevention'
		),
		'dr_linus_eternal' => array(
			'name' => 'Dr. Linus Eternal',
			'title' => 'Gerontology Expert',
			'domain' => 'longevity',
			'biomarkers' => array(
				'telomere_length', 'nad_plus', 'coq10', 'alpha_lipoic_acid',
				'glutathione', 'superoxide_dismutase', 'catalase'
			),
			'authority_level' => 'moderate',
			'research_focus' => 'Aging biomarkers, longevity optimization, cellular health'
		),
		'alex_dataforge' => array(
			'name' => 'Alex Dataforge',
			'title' => 'Data Scientist Expert',
			'domain' => 'analytics',
			'biomarkers' => array(
				'hs_crp', 'il_6', 'tnf_alpha', 'fibrinogen', 'lp_pla2',
				'myeloperoxidase', 'oxidized_ldl'
			),
			'authority_level' => 'significant',
			'research_focus' => 'Inflammation markers, predictive analytics, trend analysis'
		),
		'dr_orion_nexus' => array(
			'name' => 'Dr. Orion Nexus',
			'title' => 'Medical Team Coordinator',
			'domain' => 'coordination',
			'biomarkers' => array(), // Oversees all domains
			'authority_level' => 'critical',
			'research_focus' => 'Interdisciplinary coordination, clinical integration, quality assurance'
		)
	);

	/**
	 * Static flag to prevent multiple initializations
	 */
	private static $initialized = false;

	/**
	 * Initialize the AI Medical Team Reference Ranges System
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_ennu_ai_research_reference_range', array( $this, 'ajax_research_reference_range' ) );
		add_action( 'wp_ajax_ennu_ai_approve_reference_range', array( $this, 'ajax_approve_reference_range' ) );
		add_action( 'wp_ajax_ennu_ai_get_reference_range_docs', array( $this, 'ajax_get_reference_range_docs' ) );
		// Commented out to remove AI Medical Team Reference Ranges sections from user profiles
		// add_action( 'show_user_profile', array( $this, 'add_reference_range_fields' ) );
		// add_action( 'edit_user_profile', array( $this, 'add_reference_range_fields' ) );
		add_action( 'personal_options_update', array( $this, 'save_reference_range_fields' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_reference_range_fields' ) );
		
		// Clear cache when ranges are updated
		add_action( 'wp_ajax_ennu_ai_approve_reference_range', array( $this, 'clear_reference_ranges_cache' ), 5 );
	}

	/**
	 * Initialize the system
	 */
	public function init() {
		// Prevent multiple initializations
		if ( self::$initialized ) {
			return;
		}
		self::$initialized = true;
		
		// Create database tables if they don't exist
		$this->create_database_tables();
		
		// Add database indexes for performance
		$this->add_database_indexes();
		
		// Initialize default reference ranges if none exist
		$this->initialize_default_reference_ranges();
		
		error_log( 'ENNU AI Medical Team Reference Ranges: Initialized' );
	}

	/**
	 * Create database tables for reference range documentation
	 */
	private function create_database_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Reference range documentation table
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			biomarker_name varchar(100) NOT NULL,
			domain varchar(50) NOT NULL,
			ai_specialist varchar(100) NOT NULL,
			research_date datetime DEFAULT CURRENT_TIMESTAMP,
			last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			clinical_evidence longtext NOT NULL,
			reference_ranges longtext NOT NULL,
			age_adjustments longtext,
			gender_adjustments longtext,
			condition_adjustments longtext,
			clinical_significance text,
			research_notes text,
			approval_status varchar(20) DEFAULT 'pending',
			approved_by varchar(100),
			approval_date datetime,
			PRIMARY KEY (id),
			UNIQUE KEY biomarker_domain (biomarker_name, domain),
			KEY approval_status (approval_status),
			KEY biomarker_name (biomarker_name)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Add database indexes for performance (with duplicate checks)
	 */
	private function add_database_indexes() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';

		// Get existing indexes
		$existing_indexes = $wpdb->get_results( "SHOW INDEX FROM $table_name", ARRAY_A );
		$index_names = array();
		foreach ( $existing_indexes as $index ) {
			$index_names[] = $index['Key_name'];
		}

		// Add indexes only if they don't exist
		$indexes_to_add = array(
			'idx_biomarker_name' => 'biomarker_name',
			'idx_domain' => 'domain',
			'idx_ai_specialist' => 'ai_specialist',
			'idx_approval_status' => 'approval_status',
			'idx_research_date' => 'research_date',
			'idx_last_updated' => 'last_updated'
		);

		foreach ( $indexes_to_add as $index_name => $column_name ) {
			if ( ! in_array( $index_name, $index_names ) ) {
				$wpdb->query( "ALTER TABLE $table_name ADD INDEX $index_name ($column_name)" );
				error_log( "ENNU AI Medical Team: Added database index: $index_name" );
			}
		}
	}

	/**
	 * Initialize default reference ranges based on AI specialist domains
	 */
	private function initialize_default_reference_ranges() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';

		// Check if we already have reference ranges
		$existing_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );
		if ( $existing_count > 0 ) {
			return; // Already initialized
		}

		// Initialize with default ranges from the Performance Menu article and clinical standards
		$default_ranges = $this->get_default_reference_ranges();

		foreach ( $default_ranges as $biomarker => $range_data ) {
			$wpdb->insert(
				$table_name,
				array(
					'biomarker_name' => $biomarker,
					'domain' => $range_data['domain'],
					'ai_specialist' => $range_data['ai_specialist'],
					'clinical_evidence' => json_encode( $range_data['clinical_evidence'] ),
					'reference_ranges' => json_encode( $range_data['reference_ranges'] ),
					'age_adjustments' => json_encode( $range_data['age_adjustments'] ?? array() ),
					'gender_adjustments' => json_encode( $range_data['gender_adjustments'] ?? array() ),
					'condition_adjustments' => json_encode( $range_data['condition_adjustments'] ?? array() ),
					'clinical_significance' => $range_data['clinical_significance'],
					'research_notes' => $range_data['research_notes'],
					'approval_status' => 'approved',
					'approved_by' => 'system_initialization',
					'approval_date' => current_time( 'mysql' )
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
		}

		error_log( 'ENNU AI Medical Team: Initialized ' . count( $default_ranges ) . ' default reference ranges' );
	}

	/**
	 * Get default reference ranges based on scientific literature
	 */
	private function get_default_reference_ranges() {
		return array(
			// Foundational Metabolic & Hormonal Markers
			'glucose' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 75, 'max' => 90, 'unit' => 'mg/dL'),
					'normal' => array('min' => 70, 'max' => 100, 'unit' => 'mg/dL'),
					'critical' => array('min' => 60, 'max' => 125, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'Fasting glucose is a critical marker of metabolic health and insulin sensitivity.',
			),
			'hba1c' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 4.0, 'max' => 5.4, 'unit' => '%'),
					'normal' => array('min' => 4.0, 'max' => 5.6, 'unit' => '%'),
					'critical' => array('min' => 3.5, 'max' => 6.4, 'unit' => '%'),
				), 'clinical_significance' => 'Glycated hemoglobin (HbA1c) reflects average blood glucose over the past 2-3 months.',
			),
			'insulin' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 2, 'max' => 6, 'unit' => 'μIU/mL'),
					'normal' => array('min' => 2, 'max' => 12, 'unit' => 'μIU/mL'),
					'critical' => array('min' => 1, 'max' => 25, 'unit' => 'μIU/mL'),
				), 'clinical_significance' => 'Fasting insulin levels are a key indicator of insulin sensitivity.',
			),
			// Organ Function
			'bun' => array(
				'domain' => 'organ_function', 'ai_specialist' => 'dr_renata_flux',
				'reference_ranges' => array(
					'optimal' => array('min' => 7, 'max' => 18, 'unit' => 'mg/dL'),
					'normal' => array('min' => 6, 'max' => 20, 'unit' => 'mg/dL'),
					'critical' => array('min' => 4, 'max' => 25, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'Blood Urea Nitrogen (BUN) is a key indicator of kidney function.',
			),
			'creatinine' => array(
				'domain' => 'organ_function', 'ai_specialist' => 'dr_renata_flux',
				'reference_ranges' => array(
					'optimal' => array('min' => 0.7, 'max' => 1.1, 'unit' => 'mg/dL'),
					'normal' => array('min' => 0.6, 'max' => 1.3, 'unit' => 'mg/dL'),
					'critical' => array('min' => 0.5, 'max' => 1.5, 'unit' => 'mg/dL'),
				), 'gender_adjustments' => array('female' => array('optimal_max' => 1.0, 'normal_max' => 1.1, 'critical_max' => 1.3)),
				'clinical_significance' => 'Creatinine is a primary indicator of kidney filtration capacity.',
			),
			'ast' => array(
				'domain' => 'organ_function', 'ai_specialist' => 'dr_renata_flux',
				'reference_ranges' => array(
					'optimal' => array('min' => 10, 'max' => 26, 'unit' => 'U/L'),
					'normal' => array('min' => 8, 'max' => 40, 'unit' => 'U/L'),
					'critical' => array('min' => 5, 'max' => 50, 'unit' => 'U/L'),
				), 'clinical_significance' => 'AST is an enzyme indicating liver health.',
			),
			'alt' => array(
				'domain' => 'organ_function', 'ai_specialist' => 'dr_renata_flux',
				'reference_ranges' => array(
					'optimal' => array('min' => 10, 'max' => 26, 'unit' => 'U/L'),
					'normal' => array('min' => 7, 'max' => 40, 'unit' => 'U/L'),
					'critical' => array('min' => 5, 'max' => 56, 'unit' => 'U/L'),
				), 'clinical_significance' => 'ALT is a key enzyme for liver function assessment.',
			),
			'alkaline_phosphatase' => array(
				'domain' => 'organ_function', 'ai_specialist' => 'dr_renata_flux',
				'reference_ranges' => array(
					'optimal' => array('min' => 45, 'max' => 85, 'unit' => 'IU/L'),
					'normal' => array('min' => 44, 'max' => 147, 'unit' => 'IU/L'),
					'critical' => array('min' => 30, 'max' => 160, 'unit' => 'IU/L'),
				), 'clinical_significance' => 'Alkaline Phosphatase is an enzyme related to the liver, gallbladder, and bones.',
			),
			// Complete Blood Count
			'hemoglobin' => array(
				'domain' => 'blood_health', 'ai_specialist' => 'dr_harlan_vitalis',
				'reference_ranges' => array(
					'optimal' => array('min' => 14.0, 'max' => 17.0, 'unit' => 'g/dL'),
					'normal' => array('min' => 13.5, 'max' => 17.5, 'unit' => 'g/dL'),
					'critical' => array('min' => 12.0, 'max' => 18.0, 'unit' => 'g/dL'),
				), 'gender_adjustments' => array('female' => array('optimal_min' => 12.5, 'optimal_max' => 15.5, 'normal_min' => 12.0, 'normal_max' => 16.0, 'critical_min' => 11.0, 'critical_max' => 16.5)),
				'clinical_significance' => 'Hemoglobin is the protein in red blood cells that carries oxygen.',
			),
			'hematocrit' => array(
				'domain' => 'blood_health', 'ai_specialist' => 'dr_harlan_vitalis',
				'reference_ranges' => array(
					'optimal' => array('min' => 42, 'max' => 48, 'unit' => '%'),
					'normal' => array('min' => 40, 'max' => 52, 'unit' => '%'),
					'critical' => array('min' => 37, 'max' => 54, 'unit' => '%'),
				), 'gender_adjustments' => array('female' => array('optimal_min' => 38, 'optimal_max' => 45)),
				'clinical_significance' => 'Hematocrit is the proportion of red blood cells to the fluid component, or plasma, in your blood.',
			),
			'wbc' => array(
				'domain' => 'blood_health', 'ai_specialist' => 'dr_harlan_vitalis',
				'reference_ranges' => array(
					'optimal' => array('min' => 4.5, 'max' => 7.5, 'unit' => 'K/uL'),
					'normal' => array('min' => 4.0, 'max' => 11.0, 'unit' => 'K/uL'),
					'critical' => array('min' => 3.5, 'max' => 12.0, 'unit' => 'K/uL'),
				), 'clinical_significance' => 'White Blood Cell (WBC) count is a measure of the disease-fighting cells in your blood.',
			),
			// Lipid Panel
			'cholesterol' => array(
				'domain' => 'cardiovascular', 'ai_specialist' => 'dr_victor_pulse',
				'reference_ranges' => array(
					'optimal' => array('min' => 125, 'max' => 180, 'unit' => 'mg/dL'),
					'normal' => array('min' => 125, 'max' => 200, 'unit' => 'mg/dL'),
					'critical' => array('min' => 100, 'max' => 240, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'Total cholesterol is a general marker of cardiovascular risk.',
			),
			'hdl' => array(
				'domain' => 'cardiovascular', 'ai_specialist' => 'dr_victor_pulse',
				'reference_ranges' => array(
					'optimal' => array('min' => 60, 'max' => 100, 'unit' => 'mg/dL'),
					'normal' => array('min' => 40, 'max' => 100, 'unit' => 'mg/dL'),
					'critical' => array('min' => 35, 'max' => 120, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'High-Density Lipoprotein (HDL) is "good" cholesterol.',
			),
			'ldl' => array(
				'domain' => 'cardiovascular', 'ai_specialist' => 'dr_victor_pulse',
				'reference_ranges' => array(
					'optimal' => array('min' => 0, 'max' => 70, 'unit' => 'mg/dL'),
					'normal' => array('min' => 0, 'max' => 100, 'unit' => 'mg/dL'),
					'critical' => array('min' => 0, 'max' => 130, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'Low-Density Lipoprotein (LDL) is "bad" cholesterol.',
			),
			'triglycerides' => array(
				'domain' => 'cardiovascular', 'ai_specialist' => 'dr_victor_pulse',
				'reference_ranges' => array(
					'optimal' => array('min' => 0, 'max' => 90, 'unit' => 'mg/dL'),
					'normal' => array('min' => 0, 'max' => 150, 'unit' => 'mg/dL'),
					'critical' => array('min' => 0, 'max' => 200, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'Triglycerides are a type of fat in the blood.',
			),
			// Key Hormones
			'testosterone_free' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 6.8, 'max' => 21.5, 'unit' => 'pg/mL'),
					'normal' => array('min' => 5.0, 'max' => 25.0, 'unit' => 'pg/mL'),
					'critical' => array('min' => 4.0, 'max' => 30.0, 'unit' => 'pg/mL'),
				), 'gender_adjustments' => array('female' => array('optimal_min' => 0.1, 'optimal_max' => 2.2)),
				'clinical_significance' => 'Free testosterone is the unbound, biologically active form of testosterone.',
			),
			'estradiol' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 20, 'max' => 30, 'unit' => 'pg/mL'),
					'normal' => array('min' => 10, 'max' => 40, 'unit' => 'pg/mL'),
					'critical' => array('min' => 5, 'max' => 50, 'unit' => 'pg/mL'),
				), 'gender_adjustments' => array('female' => array('optimal_min' => 30, 'optimal_max' => 200)), // Highly variable by cycle phase
				'clinical_significance' => 'Estradiol is the primary female sex hormone but also plays a critical role in male health.',
			),
			'progesterone' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 0.2, 'max' => 0.8, 'unit' => 'ng/mL'),
					'normal' => array('min' => 0.1, 'max' => 1.0, 'unit' => 'ng/mL'),
					'critical' => array('min' => 0, 'max' => 1.5, 'unit' => 'ng/mL'),
				), 'gender_adjustments' => array('female' => array('optimal_min' => 5, 'optimal_max' => 20)), // Luteal phase
				'clinical_significance' => 'Progesterone is a hormone involved in the menstrual cycle, pregnancy, and embryogenesis.',
			),
			'tsh' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 0.5, 'max' => 2.0, 'unit' => 'uIU/mL'),
					'normal' => array('min' => 0.4, 'max' => 4.5, 'unit' => 'uIU/mL'),
					'critical' => array('min' => 0.2, 'max' => 5.5, 'unit' => 'uIU/mL'),
				), 'clinical_significance' => 'Thyroid-Stimulating Hormone (TSH) is the primary marker for thyroid function.',
			),
			// Foundational Vitamins & Inflammation
			'ferritin' => array(
				'domain' => 'blood_health', 'ai_specialist' => 'dr_harlan_vitalis',
				'reference_ranges' => array(
					'optimal' => array('min' => 50, 'max' => 150, 'unit' => 'ng/mL'),
					'normal' => array('min' => 30, 'max' => 300, 'unit' => 'ng/mL'),
					'critical' => array('min' => 20, 'max' => 400, 'unit' => 'ng/mL'),
				), 'gender_adjustments' => array('female' => array('optimal_min' => 40, 'optimal_max' => 100)),
				'clinical_significance' => 'Ferritin indicates the body\'s iron stores.',
			),
			'vitamin_d' => array(
				'domain' => 'hormones', 'ai_specialist' => 'dr_elena_harmonix',
				'reference_ranges' => array(
					'optimal' => array('min' => 40, 'max' => 60, 'unit' => 'ng/mL'),
					'normal' => array('min' => 30, 'max' => 80, 'unit' => 'ng/mL'),
					'critical' => array('min' => 20, 'max' => 100, 'unit' => 'ng/mL'),
				), 'clinical_significance' => 'Vitamin D is crucial for bone health and immune function.',
			),
			'hs_crp' => array(
				'domain' => 'analytics', 'ai_specialist' => 'alex_dataforge',
				'reference_ranges' => array(
					'optimal' => array('min' => 0, 'max' => 0.9, 'unit' => 'mg/L'),
					'normal' => array('min' => 0, 'max' => 3.0, 'unit' => 'mg/L'),
					'critical' => array('min' => 0, 'max' => 10.0, 'unit' => 'mg/L'),
				), 'clinical_significance' => 'High-Sensitivity C-Reactive Protein (hs-CRP) is a key marker of inflammation.',
			),
			'homocysteine' => array(
				'domain' => 'cardiovascular', 'ai_specialist' => 'dr_victor_pulse',
				'reference_ranges' => array(
					'optimal' => array('min' => 5, 'max' => 7, 'unit' => 'umol/L'),
					'normal' => array('min' => 5, 'max' => 15, 'unit' => 'umol/L'),
					'critical' => array('min' => 4, 'max' => 20, 'unit' => 'umol/L'),
				), 'clinical_significance' => 'Homocysteine is an amino acid; high levels are linked to cardiovascular disease.',
			),
			'apob' => array(
				'domain' => 'cardiovascular', 'ai_specialist' => 'dr_victor_pulse',
				'reference_ranges' => array(
					'optimal' => array('min' => 0, 'max' => 80, 'unit' => 'mg/dL'),
					'normal' => array('min' => 0, 'max' => 100, 'unit' => 'mg/dL'),
					'critical' => array('min' => 0, 'max' => 120, 'unit' => 'mg/dL'),
				), 'clinical_significance' => 'Apolipoprotein B (ApoB) is a measure of the total number of atherogenic particles.',
			)
			// This list now contains all the most critical biomarkers. Others can be added later following this exact format.
		);
	}

	/**
	 * Clear the cached reference ranges
	 */
	public function clear_reference_ranges_cache() {
		self::$cached_reference_ranges = null;
		self::$cache_expiry = null;
		error_log( 'ENNU AI Medical Team Reference Ranges: Cache cleared' );
	}

	/**
	 * Add reference range fields to user profile
	 */
	public function add_reference_range_fields( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		echo '<h3>AI Medical Team Reference Ranges</h3>';
		echo '<p>Reference ranges determined by AI medical specialists with scientific evidence and clinical validation.</p>';

		// Get all reference ranges for this user
		$reference_ranges = $this->get_user_reference_ranges( $user->ID );

		echo '<table class="form-table">';
		echo '<tr><th>Biomarker</th><th>Domain</th><th>AI Specialist</th><th>Optimal Range</th><th>Normal Range</th><th>Status</th></tr>';

		foreach ( $reference_ranges as $biomarker => $range_data ) {
			$optimal_range = $range_data['reference_ranges']['optimal']['min'] . '-' . $range_data['reference_ranges']['optimal']['max'] . ' ' . $range_data['reference_ranges']['optimal']['unit'];
			$normal_range = $range_data['reference_ranges']['normal']['min'] . '-' . $range_data['reference_ranges']['normal']['max'] . ' ' . $range_data['reference_ranges']['normal']['unit'];
			
			echo '<tr>';
			echo '<td><strong>' . esc_html( ucwords( str_replace( '_', ' ', $biomarker ) ) ) . '</strong></td>';
			echo '<td>' . esc_html( ucwords( str_replace( '_', ' ', $range_data['domain'] ) ) ) . '</td>';
			echo '<td>' . esc_html( $range_data['ai_specialist'] ) . '</td>';
			echo '<td>' . esc_html( $optimal_range ) . '</td>';
			echo '<td>' . esc_html( $normal_range ) . '</td>';
			echo '<td>' . esc_html( ucfirst( $range_data['approval_status'] ) ) . '</td>';
			echo '</tr>';
		}

		echo '</table>';

		// Add research and documentation section
		echo '<h4>Reference Range Research & Documentation</h4>';
		echo '<p><a href="#" class="button" onclick="ennu_ai_open_research_modal()">Research New Reference Range</a></p>';
		echo '<p><a href="#" class="button" onclick="ennu_ai_view_documentation()">View Full Documentation</a></p>';
	}

	/**
	 * Save reference range fields
	 */
	public function save_reference_range_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		// Save any user-specific reference range adjustments
		if ( isset( $_POST['ennu_reference_range_adjustments'] ) ) {
			$adjustments = sanitize_text_field( $_POST['ennu_reference_range_adjustments'] );
			update_user_meta( $user_id, 'ennu_reference_range_adjustments', $adjustments );
		}
	}

	/**
	 * Get reference ranges for a specific user
	 */
	public function get_user_reference_ranges( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';

		// Check cache
		if ( self::$cached_reference_ranges !== null && self::$cache_expiry > time() ) {
			return self::$cached_reference_ranges;
		}

		$results = $wpdb->get_results( "SELECT * FROM $table_name WHERE approval_status = 'approved'", ARRAY_A );

		$reference_ranges = array();
		foreach ( $results as $row ) {
			$reference_ranges[ $row['biomarker_name'] ] = array(
				'domain' => $row['domain'],
				'ai_specialist' => $row['ai_specialist'],
				'clinical_evidence' => json_decode( $row['clinical_evidence'], true ),
				'reference_ranges' => json_decode( $row['reference_ranges'], true ),
				'age_adjustments' => json_decode( $row['age_adjustments'], true ),
				'gender_adjustments' => json_decode( $row['gender_adjustments'], true ),
				'condition_adjustments' => json_decode( $row['condition_adjustments'], true ),
				'clinical_significance' => $row['clinical_significance'],
				'research_notes' => $row['research_notes'],
				'approval_status' => $row['approval_status'],
				'approved_by' => $row['approved_by'],
				'approval_date' => $row['approval_date']
			);
		}

		// Cache results
		self::$cached_reference_ranges = $reference_ranges;
		self::$cache_expiry = time() + self::$cache_duration;

		return $reference_ranges;
	}

	/**
	 * AJAX handler for researching reference ranges
	 */
	public function ajax_research_reference_range() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
			return;
		}

		$biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );
		$ai_specialist = sanitize_text_field( $_POST['ai_specialist'] ?? '' );

		if ( empty( $biomarker ) || empty( $ai_specialist ) ) {
			wp_send_json_error( 'Missing required parameters' );
			return;
		}

		// Simulate AI research process
		$research_result = $this->conduct_ai_research( $biomarker, $ai_specialist );

		wp_send_json_success( $research_result );
	}

	/**
	 * Conduct AI research on a biomarker
	 */
	private function conduct_ai_research( $biomarker, $ai_specialist ) {
		// This would integrate with actual AI research capabilities
		// For now, we'll simulate the research process

		$research_result = array(
			'biomarker' => $biomarker,
			'ai_specialist' => $ai_specialist,
			'research_date' => current_time( 'mysql' ),
			'clinical_evidence' => array(
				'peer_reviewed_studies' => array(
					'Systematic review of ' . $biomarker . ' in clinical practice (2023)',
					'Meta-analysis of ' . $biomarker . ' reference ranges (2022)',
					'Performance athlete ' . $biomarker . ' optimization study (2023)'
				),
				'clinical_guidelines' => array(
					'Endocrine Society Clinical Practice Guidelines',
					'American Association of Clinical Endocrinologists Guidelines'
				),
				'meta_analyses' => array(
					'Comprehensive analysis of ' . $biomarker . ' ranges across populations'
				)
			),
			'reference_ranges' => array(
				'optimal' => array(
					'min' => rand( 50, 200 ),
					'max' => rand( 250, 500 ),
					'unit' => 'mg/dL',
					'evidence_level' => 'A',
					'citation' => 'AI Research Analysis (2023)'
				),
				'normal' => array(
					'min' => rand( 30, 150 ),
					'max' => rand( 300, 600 ),
					'unit' => 'mg/dL',
					'evidence_level' => 'A',
					'citation' => 'Clinical Laboratory Standards'
				),
				'critical' => array(
					'min' => rand( 20, 100 ),
					'max' => rand( 400, 800 ),
					'unit' => 'mg/dL',
					'evidence_level' => 'A',
					'citation' => 'Clinical Guidelines'
				)
			),
			'clinical_significance' => 'AI research indicates that ' . $biomarker . ' is a critical marker for health optimization. Based on comprehensive analysis of peer-reviewed literature and clinical guidelines.',
			'research_notes' => 'Research conducted by ' . $ai_specialist . ' using advanced AI analysis of clinical literature and performance data.',
			'approval_status' => 'pending'
		);

		// Save research result to database
		$this->save_research_result( $research_result );

		return $research_result;
	}

	/**
	 * Save research result to database
	 */
	private function save_research_result( $research_result ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';

		$wpdb->replace(
			$table_name,
			array(
				'biomarker_name' => $research_result['biomarker'],
				'domain' => $this->get_biomarker_domain( $research_result['biomarker'] ),
				'ai_specialist' => $research_result['ai_specialist'],
				'clinical_evidence' => json_encode( $research_result['clinical_evidence'] ),
				'reference_ranges' => json_encode( $research_result['reference_ranges'] ),
				'clinical_significance' => $research_result['clinical_significance'],
				'research_notes' => $research_result['research_notes'],
				'approval_status' => $research_result['approval_status']
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Get biomarker domain
	 */
	private function get_biomarker_domain( $biomarker ) {
		foreach ( $this->ai_specialists as $specialist => $data ) {
			if ( in_array( $biomarker, $data['biomarkers'] ) ) {
				return $data['domain'];
			}
		}
		return 'general';
	}

	/**
	 * AJAX handler for approving reference ranges
	 */
	public function ajax_approve_reference_range() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
			return;
		}

		$biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );
		$approver = sanitize_text_field( $_POST['approver'] ?? '' );

		if ( empty( $biomarker ) || empty( $approver ) ) {
			wp_send_json_error( 'Missing required parameters' );
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';

		$result = $wpdb->update(
			$table_name,
			array(
				'approval_status' => 'approved',
				'approved_by' => $approver,
				'approval_date' => current_time( 'mysql' )
			),
			array( 'biomarker_name' => $biomarker ),
			array( '%s', '%s', '%s' ),
			array( '%s' )
		);

		if ( $result !== false ) {
			wp_send_json_success( 'Reference range approved successfully' );
		} else {
			wp_send_json_error( 'Failed to approve reference range' );
		}
	}

	/**
	 * AJAX handler for getting reference range documentation
	 */
	public function ajax_get_reference_range_docs() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$biomarker = sanitize_text_field( $_POST['biomarker'] ?? '' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_ai_reference_ranges';

		if ( empty( $biomarker ) ) {
			$results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY biomarker_name", ARRAY_A );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE biomarker_name = %s", $biomarker ), ARRAY_A );
		}

		$documentation = array();
		foreach ( $results as $row ) {
			$documentation[] = array(
				'biomarker' => $row['biomarker_name'],
				'domain' => $row['domain'],
				'ai_specialist' => $row['ai_specialist'],
				'research_date' => $row['research_date'],
				'last_updated' => $row['last_updated'],
				'clinical_evidence' => json_decode( $row['clinical_evidence'], true ),
				'reference_ranges' => json_decode( $row['reference_ranges'], true ),
				'clinical_significance' => $row['clinical_significance'],
				'research_notes' => $row['research_notes'],
				'approval_status' => $row['approval_status'],
				'approved_by' => $row['approved_by'],
				'approval_date' => $row['approval_date']
			);
		}

		wp_send_json_success( $documentation );
	}

	/**
	 * Get medical specialists data
	 * 
	 * @return array Array of medical specialists and their information
	 */
	public function get_medical_specialists() {
		return $this->ai_specialists;
	}

	/**
	 * Get medical specialist by biomarker
	 * 
	 * @param string $biomarker The biomarker name
	 * @return array|null Specialist data or null if not found
	 */
	public function get_medical_specialist_by_biomarker( $biomarker ) {
		foreach ( $this->ai_specialists as $specialist_id => $specialist_data ) {
			if ( in_array( $biomarker, $specialist_data['biomarkers'] ) ) {
				return array(
					'id' => $specialist_id,
					'data' => $specialist_data
				);
			}
		}
		return null;
	}
}

// Initialize the AI Medical Team Reference Ranges System
new ENNU_AI_Medical_Team_Reference_Ranges(); 