<?php
/**
 * Health Optimization: Vector-to-Biomarker Map (COMPLETE - 103 Biomarkers)
 *
 * @package ENNU_Life
 * @version 64.0.0
 */

return array(
	'Heart Health'     => array( 
		// Core Cardiovascular
		'blood_pressure', 'heart_rate', 'cholesterol', 'triglycerides', 'hdl', 'ldl', 'vldl',
		// Advanced Cardiovascular
		'apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index', 'tmao', 'nmr_lipoprofile',
		// Metabolic Impact
		'glucose', 'hba1c', 'insulin', 'uric_acid', 'one_five_ag',
		// Blood Pressure Components
		'automated_or_manual_cuff',
		// Cardiovascular Risk Factors
		'apoe_genotype',
		// Blood Components
		'hemoglobin', 'hematocrit', 'rbc', 'wbc', 'platelets', 'mch', 'mchc', 'mcv', 'rdw'
	),
	'Cognitive Health' => array( 
		// Brain Health
		'apoe_genotype', 'ptau_217', 'beta_amyloid_ratio', 'gfap',
		// Cognitive Support
		'homocysteine', 'hs_crp', 'vitamin_d', 'vitamin_b12', 'folate', 'tsh', 'free_t3', 'free_t4',
		// Energy for Brain
		'ferritin', 'coq10', 'heavy_metals_panel',
		// Advanced Cognitive
		'arsenic', 'lead', 'mercury',
		// Genetic Factors
		'genotype'
	),
	'Hormones'         => array( 
		// Core Hormones
		'testosterone_free', 'testosterone_total', 'estradiol', 'progesterone', 'shbg', 'cortisol',
		// Thyroid Function
		'tsh', 't4', 't3', 'free_t3', 'free_t4',
		// Reproductive Hormones
		'lh', 'fsh', 'dhea', 'prolactin'
	),
	'Weight Loss'      => array( 
		// Metabolic Health
		'insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c', 'glycomark', 'uric_acid',
		// Weight Regulation
		'leptin', 'ghrelin', 'adiponectin', 'one_five_ag',
		// Physical Measurements
		'weight', 'bmi', 'body_fat_percent', 'waist_measurement', 'neck_measurement',
		// Body Composition
		'bioelectrical_impedance_or_caliper',
		// Weight Unit
		'kg'
	),
	'Strength'         => array( 
		// Performance Biomarkers
		'testosterone_free', 'testosterone_total', 'dhea', 'igf_1', 'creatine_kinase',
		// Physical Assessment
		'grip_strength',
		// Support Nutrients
		'vitamin_d', 'ferritin'
	),
	'Longevity'        => array( 
		// Aging Markers
		'telomere_length', 'nad', 'tac', 'mirna_486',
		// Cardiovascular Risk
		'lp_a', 'homocysteine', 'hs_crp', 'apob',
		// Metabolic Health
		'hba1c', 'uric_acid',
		// Performance
		'igf_1',
		// Gut Health
		'gut_microbiota_diversity',
		// Inflammation
		'il_6', 'il_18',
		// Kidney Function
		'gfr', 'bun', 'creatinine',
		// Lifetime Testing
		'once_lifetime'
	),
	'Energy'           => array( 
		// Core Energy
		'ferritin', 'vitamin_b12', 'vitamin_d', 'cortisol', 'tsh', 'free_t3',
		// Advanced Energy
		'coq10', 'nad', 'folate',
		// Physical Indicators
		'weight', 'bmi', 'body_fat_percent',
		// Toxicity Impact
		'arsenic', 'lead', 'mercury', 'heavy_metals_panel',
		// Temperature Regulation
		'temperature', 'oral_or_temporal_thermometer',
		// Liver Function
		'alt', 'ast', 'alkaline_phosphate', 'albumin',
		// Electrolytes
		'sodium', 'potassium', 'chloride', 'calcium', 'magnesium', 'carbon_dioxide',
		// Protein
		'protein'
	),
	'Libido'           => array( 
		// Sexual Hormones
		'testosterone_free', 'testosterone_total', 'estradiol', 'progesterone', 'prolactin', 'shbg',
		// Supporting Factors
		'cortisol', 'tsh', 'free_t3', 'free_t4', 'vitamin_d'
	),
);
