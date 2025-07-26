<?php
/**
 * ENNU Life Assessments - Symptom-Biomarker Correlations
 *
 * Comprehensive mapping of symptoms to recommended biomarkers
 * Based on clinical guidelines and medical research
 *
 * @package ENNU_Life
 * @version 62.7.0
 */

return array(
	// Energy & Fatigue
	'Fatigue'                     => array(
		// Core Energy Biomarkers
		'ferritin', 'vitamin_d', 'vitamin_b12', 'cortisol', 'tsh', 'free_t3', 'free_t4',
		// Physical Indicators
		'weight', 'bmi', 'body_fat_percent',
		// Advanced Energy
		'coq10', 'nad', 'folate',
		// Toxicity Impact
		'arsenic', 'lead', 'mercury', 'heavy_metals_panel',
		// Metabolic Health
		'glucose', 'hba1c', 'insulin',
		// Cardiovascular Impact
		'blood_pressure', 'heart_rate'
	),

	// Mental Health
	'Anxiety'                     => array(
		'Cortisol',
		'Testosterone',
		'Vitamin D',
		'Magnesium',
		'TSH',
		'Free T3',
		'Free T4',
		'Estradiol',
		'Progesterone',
	),

	'Depression'                  => array(
		'Vitamin D',
		'Vitamin B12',
		'Folate',
		'Cortisol',
		'Testosterone',
		'TSH',
		'Free T3',
		'Free T4',
		'Omega-3',
		'Magnesium',
		'Iron',
	),

	'Brain Fog'                   => array(
		// Brain Health Markers
		'apoe_genotype', 'ptau_217', 'beta_amyloid_ratio', 'gfap',
		// Cognitive Support
		'vitamin_b12', 'vitamin_d', 'homocysteine', 'tsh', 'free_t3', 'free_t4',
		// Energy for Brain
		'ferritin', 'coq10', 'heavy_metals_panel',
		// Metabolic Impact
		'glucose', 'hba1c', 'cortisol'
	),

	'Memory Problems'             => array(
		// Brain Health Markers
		'apoe_genotype', 'ptau_217', 'beta_amyloid_ratio', 'gfap',
		// Cognitive Support
		'vitamin_b12', 'vitamin_d', 'homocysteine', 'tsh', 'free_t3', 'free_t4',
		// Energy for Brain
		'ferritin', 'coq10', 'folate',
		// Metabolic Impact
		'glucose', 'hba1c'
	),

	// Sexual Health
	'Low Libido'                  => array(
		'Testosterone',
		'Free Testosterone',
		'Estradiol',
		'Prolactin',
		'SHBG',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
	),

	'Erectile Dysfunction'        => array(
		'Testosterone',
		'Free Testosterone',
		'Estradiol',
		'Prolactin',
		'SHBG',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Lipid Panel',
		'Glucose',
		'HbA1c',
	),

	// Weight & Metabolism
	'Weight Gain'                 => array(
		// Physical Measurements
		'weight', 'bmi', 'body_fat_percent', 'waist_measurement', 'neck_measurement',
		// Metabolic Health
		'insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c', 'glycomark', 'uric_acid',
		// Weight Regulation
		'leptin', 'ghrelin', 'adiponectin', 'one_five_ag',
		// Hormonal Impact
		'testosterone_free', 'testosterone_total', 'cortisol', 'tsh', 'free_t3', 'free_t4'
	),

	'Abdominal Fat Gain'          => array(
		// Physical Measurements
		'weight', 'bmi', 'body_fat_percent', 'waist_measurement', 'neck_measurement',
		// Metabolic Health
		'insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c', 'glycomark', 'uric_acid',
		// Weight Regulation
		'leptin', 'ghrelin', 'adiponectin', 'one_five_ag',
		// Hormonal Impact
		'testosterone_free', 'testosterone_total', 'cortisol', 'tsh', 'free_t3', 'free_t4',
		// Inflammation
		'hs_crp'
	),

	'Slow Metabolism'             => array(
		// Thyroid Function
		'tsh', 't4', 't3', 'free_t3', 'free_t4',
		// Metabolic Health
		'insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c',
		// Weight Regulation
		'leptin', 'ghrelin', 'adiponectin',
		// Hormonal Impact
		'testosterone_free', 'testosterone_total', 'cortisol',
		// Support Nutrients
		'vitamin_d', 'vitamin_b12'
	),

	'Blood Glucose Dysregulation' => array(
		// Metabolic Health
		'glucose', 'hba1c', 'insulin', 'fasting_insulin', 'homa_ir', 'glycomark',
		// Weight Regulation
		'leptin', 'ghrelin', 'adiponectin',
		// Cardiovascular Impact
		'cholesterol', 'triglycerides', 'hdl', 'ldl', 'hs_crp',
		// Hormonal Impact
		'cortisol'
	),

	// Sleep
	'Sleep Problems'              => array(
		'Cortisol',
		'Melatonin',
		'Testosterone',
		'Estradiol',
		'Progesterone',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'Magnesium',
	),

	'Insomnia'                    => array(
		'Cortisol',
		'Melatonin',
		'Testosterone',
		'Estradiol',
		'Progesterone',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'Magnesium',
	),

	// Physical Symptoms
	'Joint Pain'                  => array(
		'Vitamin D',
		'CRP',
		'ESR',
		'Uric Acid',
		'Calcium',
		'Magnesium',
		'Omega-3',
		'Collagen Markers',
		'Rheumatoid Factor',
		'ANA',
	),

	'Muscle Weakness'             => array(
		// Performance Biomarkers
		'testosterone_free', 'testosterone_total', 'igf_1', 'creatine_kinase', 'grip_strength',
		// Support Nutrients
		'vitamin_d', 'vitamin_b12', 'ferritin',
		// Thyroid Function
		'tsh', 'free_t3', 'free_t4',
		// Physical Indicators
		'weight', 'bmi', 'body_fat_percent'
	),

	'Muscle Loss'                 => array(
		// Performance Biomarkers
		'testosterone_free', 'testosterone_total', 'igf_1', 'creatine_kinase', 'grip_strength',
		// Support Nutrients
		'vitamin_d', 'vitamin_b12', 'ferritin',
		// Physical Indicators
		'weight', 'bmi', 'body_fat_percent'
	),

	'Poor Exercise Tolerance'     => array(
		// Performance Biomarkers
		'igf_1', 'creatine_kinase', 'grip_strength',
		// Energy Support
		'ferritin', 'vitamin_d', 'vitamin_b12',
		// Hormonal Impact
		'testosterone_free', 'testosterone_total', 'cortisol',
		// Thyroid Function
		'tsh', 'free_t3', 'free_t4',
		// Cardiovascular Health
		'blood_pressure', 'heart_rate'
	),

	// Cardiovascular
	'Chest Pain'                  => array(
		// Core Cardiovascular
		'blood_pressure', 'heart_rate', 'cholesterol', 'triglycerides', 'hdl', 'ldl',
		// Advanced Cardiovascular
		'apob', 'hs_crp', 'homocysteine', 'lp_a', 'omega_3_index', 'tmao', 'nmr_lipoprofile',
		// Metabolic Impact
		'glucose', 'hba1c', 'insulin',
		// Physical Indicators
		'weight', 'bmi', 'body_fat_percent'
	),

	'High Blood Pressure'         => array(
		'Renin',
		'Aldosterone',
		'Cortisol',
		'Catecholamines',
		'Lipid Panel',
		'CRP',
		'Homocysteine',
		'Uric Acid',
		'Blood Pressure',
	),

	'Palpitations'                => array(
		'Cortisol',
		'Catecholamines',
		'TSH',
		'Free T3',
		'Free T4',
		'Electrolytes',
		'Magnesium',
		'Blood Pressure',
		'Glucose',
	),

	// Respiratory
	'Shortness of Breath'         => array(
		'Hemoglobin',
		'Iron',
		'Ferritin',
		'BNP',
		'CRP',
		'Blood Pressure',
		'Glucose',
		'Cortisol',
	),

	// Digestive
	'Digestive Issues'            => array(
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'B12',
		'Iron',
		'Magnesium',
		'Celiac Panel',
		'Inflammatory Markers',
	),

	'Bloating'                    => array(
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'B12',
		'Iron',
		'Magnesium',
		'Celiac Panel',
		'Inflammatory Markers',
	),

	// Skin & Hair
	'Hair Loss'                   => array(
		'Testosterone',
		'Estradiol',
		'TSH',
		'Free T3',
		'Free T4',
		'Iron',
		'Ferritin',
		'Vitamin D',
		'B12',
		'Zinc',
		'Biotin',
	),

	'Skin Changes'                => array(
		'Testosterone',
		'Estradiol',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'B12',
		'Zinc',
		'Omega-3',
	),

	'Acne'                        => array(
		'Testosterone',
		'Estradiol',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'Zinc',
		'Omega-3',
	),

	// Temperature Regulation
	'Hot Flashes'                 => array(
		'Estradiol',
		'Progesterone',
		'FSH',
		'LH',
		'Testosterone',
		'DHEA-S',
		'TSH',
		'Free T3',
		'Free T4',
	),

	'Night Sweats'                => array(
		'Estradiol',
		'Progesterone',
		'FSH',
		'LH',
		'Testosterone',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
	),

	'Temperature Sensitivity'     => array(
		'TSH',
		'Free T3',
		'Free T4',
		'Testosterone',
		'Estradiol',
		'Cortisol',
		'Iron',
		'Vitamin D',
	),

	// Reproductive Health (Female)
	'Irregular Periods'           => array(
		'Estradiol',
		'Progesterone',
		'FSH',
		'LH',
		'Testosterone',
		'Prolactin',
		'TSH',
		'Free T3',
		'Free T4',
		'AMH',
		'Inhibin B',
	),

	'Heavy Periods'               => array(
		'Estradiol',
		'Progesterone',
		'FSH',
		'LH',
		'Testosterone',
		'Iron',
		'Ferritin',
		'TSH',
		'Free T3',
		'Free T4',
	),

	'Infertility'                 => array(
		'FSH',
		'LH',
		'Estradiol',
		'Progesterone',
		'Testosterone',
		'Prolactin',
		'AMH',
		'Inhibin B',
		'TSH',
		'Free T3',
		'Free T4',
	),

	// Neurological
	'Severe Headache'             => array(
		'CRP',
		'Homocysteine',
		'Blood Pressure',
		'Glucose',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Vitamin D',
		'B12',
	),

	'Lightheadedness'             => array(
		'Hemoglobin',
		'Iron',
		'Ferritin',
		'B12',
		'Electrolytes',
		'Cortisol',
		'Blood Pressure',
		'Glucose',
	),

	'Poor Balance'                => array(
		'Vitamin D',
		'B12',
		'Iron',
		'Calcium',
		'Magnesium',
		'Testosterone',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
	),

	// Recovery & Healing
	'Slow Recovery'               => array(
		'Testosterone',
		'IGF-1',
		'Creatine Kinase',
		'CRP',
		'Vitamin D',
		'B12',
		'Iron',
		'Protein Markers',
		'Cortisol',
	),

	'Prolonged Soreness'          => array(
		'Testosterone',
		'IGF-1',
		'Creatine Kinase',
		'CRP',
		'Vitamin D',
		'B12',
		'Iron',
		'Protein Markers',
		'Cortisol',
	),

	// Swelling & Fluid Retention
	'Swelling'                    => array(
		'BNP',
		'Albumin',
		'Creatinine',
		'eGFR',
		'Electrolytes',
		'CRP',
		'TSH',
		'Free T3',
		'Free T4',
		'Cortisol',
	),

	// Mood & Behavior
	'Irritability'                => array(
		'Cortisol',
		'Testosterone',
		'Estradiol',
		'Progesterone',
		'TSH',
		'Free T3',
		'Free T4',
		'Blood Sugar',
		'Magnesium',
		'Vitamin D',
		'B12',
	),

	'Mood Swings'                 => array(
		'Cortisol',
		'Testosterone',
		'Estradiol',
		'Progesterone',
		'TSH',
		'Free T3',
		'Free T4',
		'Blood Sugar',
		'Magnesium',
		'Vitamin D',
		'B12',
	),

	// Concentration & Focus
	'Difficulty Concentrating'    => array(
		'Vitamin B12',
		'Vitamin D',
		'Homocysteine',
		'TSH',
		'Free T3',
		'Free T4',
		'Cortisol',
		'Iron',
		'Ferritin',
		'Glucose',
		'HbA1c',
	),

	// Appetite & Eating
	'Poor Appetite'               => array(
		'TSH',
		'Free T3',
		'Free T4',
		'Cortisol',
		'Testosterone',
		'Zinc',
		'Iron',
		'Vitamin D',
		'B12',
	),

	'Strong Cravings'             => array(
		'Insulin',
		'Glucose',
		'HbA1c',
		'Cortisol',
		'Testosterone',
		'Estradiol',
		'Serotonin',
		'Magnesium',
		'Zinc',
	),

	'Emotional Eating'            => array(
		'Cortisol',
		'Testosterone',
		'Estradiol',
		'Serotonin',
		'Insulin',
		'Glucose',
		'HbA1c',
		'Magnesium',
	),
);
