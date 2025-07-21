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
		'Testosterone',
		'Vitamin D',
		'Vitamin B12',
		'Iron',
		'Ferritin',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
		'Glucose',
		'HbA1c',
		'Insulin',
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

	'Memory Problems'             => array(
		'Vitamin B12',
		'Vitamin D',
		'Homocysteine',
		'TSH',
		'Free T3',
		'Free T4',
		'ApoE Genotype',
		'Glucose',
		'HbA1c',
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
		'Testosterone',
		'Cortisol',
		'Insulin',
		'TSH',
		'Free T3',
		'Free T4',
		'Glucose',
		'HbA1c',
		'Leptin',
		'Adiponectin',
	),

	'Abdominal Fat Gain'          => array(
		'Testosterone',
		'Cortisol',
		'Insulin',
		'TSH',
		'Free T3',
		'Free T4',
		'Glucose',
		'HbA1c',
		'Leptin',
		'Adiponectin',
		'CRP',
	),

	'Slow Metabolism'             => array(
		'TSH',
		'Free T3',
		'Free T4',
		'Testosterone',
		'Cortisol',
		'Insulin',
		'Leptin',
		'Adiponectin',
		'Vitamin D',
		'Vitamin B12',
	),

	'Blood Glucose Dysregulation' => array(
		'Glucose',
		'HbA1c',
		'Insulin',
		'C-Peptide',
		'Leptin',
		'Adiponectin',
		'Lipid Panel',
		'CRP',
		'Cortisol',
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
		'Testosterone',
		'IGF-1',
		'Creatine Kinase',
		'Vitamin D',
		'B12',
		'Iron',
		'Protein Markers',
		'Creatinine',
		'TSH',
		'Free T3',
		'Free T4',
	),

	'Muscle Loss'                 => array(
		'Testosterone',
		'IGF-1',
		'Creatine Kinase',
		'Myostatin',
		'Vitamin D',
		'B12',
		'Iron',
		'Protein Markers',
	),

	'Poor Exercise Tolerance'     => array(
		'VO2 Max',
		'Lactate Threshold',
		'Hemoglobin',
		'Iron',
		'Ferritin',
		'Testosterone',
		'Cortisol',
		'TSH',
		'Free T3',
		'Free T4',
	),

	// Cardiovascular
	'Chest Pain'                  => array(
		'CRP',
		'Homocysteine',
		'Lipid Panel',
		'ApoB',
		'Lp(a)',
		'Troponin',
		'BNP',
		'Blood Pressure',
		'Glucose',
		'HbA1c',
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
