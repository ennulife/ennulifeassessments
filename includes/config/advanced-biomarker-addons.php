<?php
/**
 * Advanced Biomarker Addons Configuration
 *
 * @package ENNU_Life
 * @version 62.1.17
 * @description All 30 advanced biomarker addons with pricing, packages, and health vector mappings
 */

return array(
	'Advanced Hormones'       => array(
		'estradiol_e2' => array(
			'unit'              => 'pg/mL',
			'range'             => '12.5-166',
			'optimal_range'     => 'varies_by_age_gender',
			'suboptimal_range'  => 'varies_by_age_gender',
			'poor_range'        => 'varies_by_age_gender',
			'price'             => 89,
			'package'           => 'Hormone Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Hormones' => 0.9,
				'Libido'   => 0.8,
			),
			'pillar_impact'     => array( 'Body', 'Mind' ),
			'frequency'         => 'quarterly',
			'description'       => 'Primary estrogen hormone for women, important for reproductive health and bone density',
		),
		'progesterone' => array(
			'unit'              => 'ng/mL',
			'range'             => '0.1-0.8',
			'optimal_range'     => 'varies_by_age_gender',
			'suboptimal_range'  => 'varies_by_age_gender',
			'poor_range'        => 'varies_by_age_gender',
			'price'             => 79,
			'package'           => 'Hormone Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Hormones' => 0.8,
				'Libido'   => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Mind' ),
			'frequency'         => 'quarterly',
			'description'       => 'Key hormone for menstrual cycle regulation and pregnancy support',
		),
		'shbg'         => array(
			'unit'              => 'nmol/L',
			'range'             => '10-80',
			'optimal_range'     => 'varies_by_age_gender',
			'suboptimal_range'  => 'varies_by_age_gender',
			'poor_range'        => 'varies_by_age_gender',
			'price'             => 69,
			'package'           => 'Hormone Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Hormones' => 0.7,
				'Libido'   => 0.7,
				'Energy'   => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Mind' ),
			'frequency'         => 'quarterly',
			'description'       => 'Sex hormone binding globulin that regulates hormone availability',
		),
		'cortisol'     => array(
			'unit'              => 'µg/dL',
			'range'             => '6.2-19.4',
			'optimal_range'     => '6.2-15.0',
			'suboptimal_range'  => '15.1-19.4',
			'poor_range'        => '<6.2_or_>19.4',
			'price'             => 89,
			'package'           => 'Hormone Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Hormones'    => 0.8,
				'Energy'      => 0.8,
				'Weight Loss' => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Primary stress hormone that affects metabolism and energy levels',
		),
		'free_t3'      => array(
			'unit'              => 'pg/mL',
			'range'             => '2.3-4.2',
			'optimal_range'     => '2.8-3.8',
			'suboptimal_range'  => '2.3-2.8_or_3.8-4.2',
			'poor_range'        => '<2.3_or_>4.2',
			'price'             => 79,
			'package'           => 'Hormone Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Energy'           => 0.8,
				'Cognitive Health' => 0.7,
				'Weight Loss'      => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Active thyroid hormone that regulates metabolism and energy',
		),
		'free_t4'      => array(
			'unit'              => 'ng/dL',
			'range'             => '0.8-1.8',
			'optimal_range'     => '1.0-1.6',
			'suboptimal_range'  => '0.8-1.0_or_1.6-1.8',
			'poor_range'        => '<0.8_or_>1.8',
			'price'             => 79,
			'package'           => 'Hormone Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Energy'           => 0.7,
				'Cognitive Health' => 0.6,
				'Weight Loss'      => 0.5,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Thyroid hormone precursor that converts to active T3',
		),
	),

	'Advanced Cardiovascular' => array(
		'apob'          => array(
			'unit'              => 'mg/dL',
			'range'             => '<100',
			'optimal_range'     => '<80',
			'suboptimal_range'  => '80-100',
			'poor_range'        => '≥100',
			'price'             => 99,
			'package'           => 'Cardiovascular Health',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Heart Health' => 0.9,
				'Longevity'    => 0.8,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Apolipoprotein B - more accurate predictor of cardiovascular risk than LDL',
		),
		'hs_crp'        => array(
			'unit'              => 'mg/L',
			'range'             => '<3.0',
			'optimal_range'     => '<1.0',
			'suboptimal_range'  => '1.0-3.0',
			'poor_range'        => '≥3.0',
			'price'             => 89,
			'package'           => 'Cardiovascular Health',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Heart Health'     => 0.9,
				'Longevity'        => 0.8,
				'Cognitive Health' => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'High-sensitivity C-reactive protein - marker of inflammation and cardiovascular risk',
		),
		'homocysteine'  => array(
			'unit'              => 'µmol/L',
			'range'             => '<15',
			'optimal_range'     => '<10',
			'suboptimal_range'  => '10-15',
			'poor_range'        => '≥15',
			'price'             => 99,
			'package'           => 'Cardiovascular Health',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Heart Health'     => 0.8,
				'Longevity'        => 0.8,
				'Cognitive Health' => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Amino acid that when elevated increases cardiovascular and cognitive risk',
		),
		'lp_a'          => array(
			'unit'              => 'mg/dL',
			'range'             => '<30',
			'optimal_range'     => '<20',
			'suboptimal_range'  => '20-30',
			'poor_range'        => '≥30',
			'price'             => 129,
			'package'           => 'Cardiovascular Health',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Heart Health' => 0.9,
				'Longevity'    => 0.8,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Lipoprotein(a) - genetic risk factor for cardiovascular disease',
		),
		'omega_3_index' => array(
			'unit'              => '%',
			'range'             => '>8',
			'optimal_range'     => '>8',
			'suboptimal_range'  => '6-8',
			'poor_range'        => '<6',
			'price'             => 149,
			'package'           => 'Cardiovascular Health',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Heart Health'     => 0.8,
				'Cognitive Health' => 0.7,
				'Longevity'        => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Percentage of omega-3 fatty acids in red blood cell membranes',
		),
	),

	'Advanced Longevity'      => array(
		'telomere_length'          => array(
			'unit'              => 'kb',
			'range'             => 'varies_by_age',
			'optimal_range'     => 'varies_by_age',
			'suboptimal_range'  => 'varies_by_age',
			'poor_range'        => 'varies_by_age',
			'price'             => 299,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Longevity' => 0.9,
				'Energy'    => 0.6,
			),
			'pillar_impact'     => array( 'Lifestyle' ),
			'frequency'         => 'annually',
			'description'       => 'Length of telomeres - protective caps on chromosomes that shorten with age',
		),
		'nad_plus'                 => array(
			'unit'              => 'µM',
			'range'             => 'varies_by_age',
			'optimal_range'     => 'varies_by_age',
			'suboptimal_range'  => 'varies_by_age',
			'poor_range'        => 'varies_by_age',
			'price'             => 199,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Longevity' => 0.9,
				'Energy'    => 0.8,
			),
			'pillar_impact'     => array( 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Nicotinamide adenine dinucleotide - coenzyme essential for cellular energy production',
		),
		'tac'                      => array(
			'unit'              => 'mM',
			'range'             => 'varies_by_age',
			'optimal_range'     => 'varies_by_age',
			'suboptimal_range'  => 'varies_by_age',
			'poor_range'        => 'varies_by_age',
			'price'             => 149,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Longevity' => 0.8,
				'Energy'    => 0.6,
			),
			'pillar_impact'     => array( 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Total Antioxidant Capacity - measures overall antioxidant defense system',
		),
		'uric_acid'                => array(
			'unit'              => 'mg/dL',
			'range'             => '3.4-7.0',
			'optimal_range'     => '3.4-6.0',
			'suboptimal_range'  => '6.1-7.0',
			'poor_range'        => '<3.4_or_>7.0',
			'price'             => 79,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Longevity' => 0.7,
				'Energy'    => 0.5,
			),
			'pillar_impact'     => array( 'Body' ),
			'frequency'         => 'quarterly',
			'description'       => 'Waste product that can indicate metabolic health and inflammation',
		),
		'gut_microbiota_diversity' => array(
			'unit'              => 'index',
			'range'             => 'varies_by_individual',
			'optimal_range'     => 'high_diversity',
			'suboptimal_range'  => 'moderate_diversity',
			'poor_range'        => 'low_diversity',
			'price'             => 399,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'stool_sample',
			'health_vectors'    => array(
				'Longevity' => 0.8,
				'Energy'    => 0.6,
			),
			'pillar_impact'     => array( 'Lifestyle' ),
			'frequency'         => 'annually',
			'description'       => 'Diversity of gut bacteria - crucial for immune function and overall health',
		),
		'mirna_486'                => array(
			'unit'              => 'expression',
			'range'             => 'varies_by_age',
			'optimal_range'     => 'varies_by_age',
			'suboptimal_range'  => 'varies_by_age',
			'poor_range'        => 'varies_by_age',
			'price'             => 249,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Longevity' => 0.8,
				'Energy'    => 0.5,
			),
			'pillar_impact'     => array( 'Lifestyle' ),
			'frequency'         => 'annually',
			'description'       => 'MicroRNA-486 - biomarker for aging and cellular health',
		),
	),

	'Advanced Performance'    => array(
		'creatine_kinase' => array(
			'unit'              => 'U/L',
			'range'             => '30-200',
			'optimal_range'     => '30-150',
			'suboptimal_range'  => '151-200',
			'poor_range'        => '<30_or_>200',
			'price'             => 89,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Strength' => 0.8,
				'Energy'   => 0.6,
			),
			'pillar_impact'     => array( 'Body' ),
			'frequency'         => 'quarterly',
			'description'       => 'Enzyme that indicates muscle damage and recovery status',
		),
		'il_6'            => array(
			'unit'              => 'pg/mL',
			'range'             => '<3.0',
			'optimal_range'     => '<1.0',
			'suboptimal_range'  => '1.0-3.0',
			'poor_range'        => '≥3.0',
			'price'             => 99,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Strength'  => 0.7,
				'Longevity' => 0.7,
			),
			'pillar_impact'     => array( 'Body' ),
			'frequency'         => 'quarterly',
			'description'       => 'Interleukin-6 - inflammatory cytokine that affects muscle and overall health',
		),
		'grip_strength'   => array(
			'unit'              => 'kg',
			'range'             => 'varies_by_age_gender',
			'optimal_range'     => 'varies_by_age_gender',
			'suboptimal_range'  => 'varies_by_age_gender',
			'poor_range'        => 'varies_by_age_gender',
			'price'             => 49,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'hand_dynamometer',
			'health_vectors'    => array(
				'Strength'  => 0.9,
				'Longevity' => 0.7,
			),
			'pillar_impact'     => array( 'Body' ),
			'frequency'         => 'quarterly',
			'description'       => 'Hand grip strength - predictor of overall muscle strength and longevity',
		),
		'il_18'           => array(
			'unit'              => 'pg/mL',
			'range'             => '<200',
			'optimal_range'     => '<100',
			'suboptimal_range'  => '100-200',
			'poor_range'        => '≥200',
			'price'             => 119,
			'package'           => 'Longevity & Performance',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Strength'  => 0.6,
				'Longevity' => 0.7,
			),
			'pillar_impact'     => array( 'Body' ),
			'frequency'         => 'quarterly',
			'description'       => 'Interleukin-18 - inflammatory marker associated with muscle aging',
		),
	),

	'Advanced Cognitive'      => array(
		'apoe_genotype' => array(
			'unit'              => 'genotype',
			'range'             => 'E2/E2, E2/E3, E2/E4, E3/E3, E3/E4, E4/E4',
			'optimal_range'     => 'E2/E2, E2/E3, E3/E3',
			'suboptimal_range'  => 'E2/E4, E3/E4',
			'poor_range'        => 'E4/E4',
			'price'             => 199,
			'package'           => 'Cognitive & Energy',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'genetic_testing',
			'health_vectors'    => array(
				'Cognitive Health' => 0.9,
				'Longevity'        => 0.7,
			),
			'pillar_impact'     => array( 'Mind' ),
			'frequency'         => 'once_lifetime',
			'description'       => 'Apolipoprotein E genotype - genetic risk factor for cognitive decline',
		),
	),

	'Advanced Energy'         => array(
		'coq10'              => array(
			'unit'              => 'µg/mL',
			'range'             => '0.5-2.0',
			'optimal_range'     => '1.0-2.0',
			'suboptimal_range'  => '0.5-1.0',
			'poor_range'        => '<0.5_or_>2.0',
			'price'             => 129,
			'package'           => 'Cognitive & Energy',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Energy'       => 0.8,
				'Heart Health' => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Coenzyme Q10 - essential for cellular energy production and heart health',
		),
		'heavy_metals_panel' => array(
			'unit'              => 'varied',
			'range'             => 'varies_by_metal',
			'optimal_range'     => 'below_threshold',
			'suboptimal_range'  => 'elevated',
			'poor_range'        => 'toxic_levels',
			'price'             => 199,
			'package'           => 'Cognitive & Energy',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Energy'           => 0.7,
				'Cognitive Health' => 0.8,
			),
			'pillar_impact'     => array( 'Body', 'Mind' ),
			'frequency'         => 'annually',
			'description'       => 'Comprehensive panel testing for toxic heavy metals (lead, mercury, arsenic, etc.)',
		),
		'ferritin'           => array(
			'unit'              => 'ng/mL',
			'range'             => 'varies_by_age_gender',
			'optimal_range'     => 'varies_by_age_gender',
			'suboptimal_range'  => 'varies_by_age_gender',
			'poor_range'        => 'varies_by_age_gender',
			'price'             => 79,
			'package'           => 'Cognitive & Energy',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Energy'           => 0.8,
				'Cognitive Health' => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Mind' ),
			'frequency'         => 'quarterly',
			'description'       => 'Iron storage protein - crucial for oxygen transport and energy production',
		),
		'folate'             => array(
			'unit'              => 'ng/mL',
			'range'             => '>2.0',
			'optimal_range'     => '>4.0',
			'suboptimal_range'  => '2.0-4.0',
			'poor_range'        => '<2.0',
			'price'             => 79,
			'package'           => 'Cognitive & Energy',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Energy'           => 0.7,
				'Cognitive Health' => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Mind' ),
			'frequency'         => 'quarterly',
			'description'       => 'B-vitamin essential for DNA synthesis and cognitive function',
		),
	),

	'Advanced Metabolic'      => array(
		'fasting_insulin' => array(
			'unit'              => 'µIU/mL',
			'range'             => '3-25',
			'optimal_range'     => '3-15',
			'suboptimal_range'  => '16-25',
			'poor_range'        => '<3_or_>25',
			'price'             => 89,
			'package'           => 'Metabolic Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'fasting_blood_draw',
			'health_vectors'    => array(
				'Weight Loss' => 0.9,
				'Energy'      => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Fasting insulin levels - indicator of insulin resistance and metabolic health',
		),
		'homa_ir'         => array(
			'unit'              => 'index',
			'range'             => '<2.0',
			'optimal_range'     => '<1.0',
			'suboptimal_range'  => '1.0-2.0',
			'poor_range'        => '≥2.0',
			'price'             => 69,
			'package'           => 'Metabolic Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'calculated_from_glucose_insulin',
			'health_vectors'    => array(
				'Weight Loss' => 0.9,
				'Energy'      => 0.7,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Homeostatic Model Assessment of Insulin Resistance - calculated insulin resistance index',
		),
		'leptin'          => array(
			'unit'              => 'ng/mL',
			'range'             => 'varies_by_bmi',
			'optimal_range'     => 'varies_by_bmi',
			'suboptimal_range'  => 'varies_by_bmi',
			'poor_range'        => 'varies_by_bmi',
			'price'             => 99,
			'package'           => 'Metabolic Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Weight Loss' => 0.8,
				'Energy'      => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Satiety hormone that regulates appetite and metabolism',
		),
		'ghrelin'         => array(
			'unit'              => 'pg/mL',
			'range'             => 'varies_by_individual',
			'optimal_range'     => 'varies_by_individual',
			'suboptimal_range'  => 'varies_by_individual',
			'poor_range'        => 'varies_by_individual',
			'price'             => 119,
			'package'           => 'Metabolic Optimization',
			'source'            => 'Advanced Lab Partners',
			'collection_method' => 'specialized_blood_draw',
			'health_vectors'    => array(
				'Weight Loss' => 0.7,
				'Energy'      => 0.6,
			),
			'pillar_impact'     => array( 'Body', 'Lifestyle' ),
			'frequency'         => 'quarterly',
			'description'       => 'Hunger hormone that stimulates appetite and food intake',
		),
	),
);
