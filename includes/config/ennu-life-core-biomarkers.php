<?php
/**
 * ENNU Life Core Biomarkers Configuration
 *
 * @package ENNU_Life
 * @version 62.1.17
 * @description All 50 ENNU Life Core biomarkers with reference ranges, units, and health vector mappings
 */

return array(
    'Physical Measurements' => array(
        'weight' => array(
            'unit' => 'lbs',
            'range' => 'optimal',
            'optimal_range' => 'varies_by_height_age_gender',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'in_person_or_at_home',
            'health_vectors' => array('Weight Loss' => 0.8, 'Strength' => 0.6),
            'pillar_impact' => array('Lifestyle', 'Body'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'bmi' => array(
            'unit' => 'kg/m²',
            'range' => '18.5-24.9',
            'optimal_range' => '18.5-24.9',
            'suboptimal_range' => '25.0-29.9',
            'poor_range' => '≥30.0',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'calculated_from_weight_height',
            'health_vectors' => array('Weight Loss' => 0.9, 'Heart Health' => 0.7),
            'pillar_impact' => array('Lifestyle', 'Body'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'body_fat_percent' => array(
            'unit' => '%',
            'range' => '10-20',
            'optimal_range' => '10-20',
            'suboptimal_range' => '21-30',
            'poor_range' => '>30',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'bioelectrical_impedance_or_caliper',
            'health_vectors' => array('Weight Loss' => 0.9, 'Strength' => 0.7),
            'pillar_impact' => array('Lifestyle', 'Body'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'waist_measurement' => array(
            'unit' => 'inches',
            'range' => 'optimal',
            'optimal_range' => '<35_inches_women_<40_inches_men',
            'suboptimal_range' => '35-40_women_40-45_men',
            'poor_range' => '>40_women_>45_men',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'tape_measurement',
            'health_vectors' => array('Weight Loss' => 0.8, 'Heart Health' => 0.8),
            'pillar_impact' => array('Lifestyle', 'Body'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'neck_measurement' => array(
            'unit' => 'inches',
            'range' => 'optimal',
            'optimal_range' => '<14_inches_women_<17_inches_men',
            'suboptimal_range' => '14-16_women_17-19_men',
            'poor_range' => '>16_women_>19_men',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'tape_measurement',
            'health_vectors' => array('Weight Loss' => 0.6, 'Heart Health' => 0.7),
            'pillar_impact' => array('Lifestyle', 'Body'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'blood_pressure' => array(
            'unit' => 'mmHg',
            'range' => '<120/80',
            'optimal_range' => '<120/80',
            'suboptimal_range' => '120-129/80-84',
            'poor_range' => '≥130/85',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'automated_or_manual_cuff',
            'health_vectors' => array('Heart Health' => 0.9, 'Weight Loss' => 0.7),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'heart_rate' => array(
            'unit' => 'bpm',
            'range' => '60-100',
            'optimal_range' => '60-80',
            'suboptimal_range' => '81-100',
            'poor_range' => '<60_or_>100',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'pulse_measurement',
            'health_vectors' => array('Heart Health' => 0.8, 'Energy' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        ),
        'temperature' => array(
            'unit' => '°F',
            'range' => '97.8-99.0',
            'optimal_range' => '98.0-98.6',
            'suboptimal_range' => '97.8-98.0_or_98.6-99.0',
            'poor_range' => '<97.8_or_>99.0',
            'source' => 'ENNU Physical Assessment',
            'collection_method' => 'oral_or_temporal_thermometer',
            'health_vectors' => array('Energy' => 0.7, 'Longevity' => 0.5),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'monthly',
            'required_for' => array('Basic Membership', 'Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Basic Metabolic Panel' => array(
        'glucose' => array(
            'unit' => 'mg/dL',
            'range' => '70-100',
            'optimal_range' => '70-85',
            'suboptimal_range' => '86-100',
            'poor_range' => '<70_or_>100',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'fasting_blood_draw',
            'health_vectors' => array('Weight Loss' => 0.8, 'Energy' => 0.7, 'Heart Health' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'hba1c' => array(
            'unit' => '%',
            'range' => '<5.7',
            'optimal_range' => '<5.5',
            'suboptimal_range' => '5.5-5.7',
            'poor_range' => '≥5.7',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Weight Loss' => 0.9, 'Longevity' => 0.8, 'Heart Health' => 0.7),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'bun' => array(
            'unit' => 'mg/dL',
            'range' => '7-20',
            'optimal_range' => '7-15',
            'suboptimal_range' => '16-20',
            'poor_range' => '<7_or_>20',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.7),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'creatinine' => array(
            'unit' => 'mg/dL',
            'range' => '0.7-1.3',
            'optimal_range' => '0.7-1.1',
            'suboptimal_range' => '1.2-1.3',
            'poor_range' => '<0.7_or_>1.3',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.8),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'gfr' => array(
            'unit' => 'mL/min/1.73m²',
            'range' => '>90',
            'optimal_range' => '>90',
            'suboptimal_range' => '60-89',
            'poor_range' => '<60',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'calculated_from_creatinine',
            'health_vectors' => array('Energy' => 0.7, 'Longevity' => 0.9),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'bun_creatinine_ratio' => array(
            'unit' => 'ratio',
            'range' => '10-20',
            'optimal_range' => '10-15',
            'suboptimal_range' => '16-20',
            'poor_range' => '<10_or_>20',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'calculated_ratio',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.7),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'sodium' => array(
            'unit' => 'mEq/L',
            'range' => '135-145',
            'optimal_range' => '136-142',
            'suboptimal_range' => '135-136_or_142-145',
            'poor_range' => '<135_or_>145',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Heart Health' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'potassium' => array(
            'unit' => 'mEq/L',
            'range' => '3.5-5.0',
            'optimal_range' => '3.8-4.5',
            'suboptimal_range' => '3.5-3.8_or_4.5-5.0',
            'poor_range' => '<3.5_or_>5.0',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Heart Health' => 0.7),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Electrolytes' => array(
        'chloride' => array(
            'unit' => 'mEq/L',
            'range' => '96-106',
            'optimal_range' => '98-104',
            'suboptimal_range' => '96-98_or_104-106',
            'poor_range' => '<96_or_>106',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.5, 'Heart Health' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'carbon_dioxide' => array(
            'unit' => 'mEq/L',
            'range' => '22-28',
            'optimal_range' => '23-27',
            'suboptimal_range' => '22-23_or_27-28',
            'poor_range' => '<22_or_>28',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.5, 'Heart Health' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'calcium' => array(
            'unit' => 'mg/dL',
            'range' => '8.5-10.5',
            'optimal_range' => '8.8-10.2',
            'suboptimal_range' => '8.5-8.8_or_10.2-10.5',
            'poor_range' => '<8.5_or_>10.5',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Strength' => 0.7, 'Energy' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'magnesium' => array(
            'unit' => 'mg/dL',
            'range' => '1.7-2.2',
            'optimal_range' => '1.8-2.1',
            'suboptimal_range' => '1.7-1.8_or_2.1-2.2',
            'poor_range' => '<1.7_or_>2.2',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.7, 'Strength' => 0.6),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Protein Panel' => array(
        'protein' => array(
            'unit' => 'g/dL',
            'range' => '6.0-8.3',
            'optimal_range' => '6.5-8.0',
            'suboptimal_range' => '6.0-6.5_or_8.0-8.3',
            'poor_range' => '<6.0_or_>8.3',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Strength' => 0.6, 'Energy' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'albumin' => array(
            'unit' => 'g/dL',
            'range' => '3.4-5.4',
            'optimal_range' => '3.8-5.0',
            'suboptimal_range' => '3.4-3.8_or_5.0-5.4',
            'poor_range' => '<3.4_or_>5.4',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Strength' => 0.6, 'Energy' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Liver Function' => array(
        'alkaline_phosphate' => array(
            'unit' => 'U/L',
            'range' => '44-147',
            'optimal_range' => '44-100',
            'suboptimal_range' => '101-147',
            'poor_range' => '<44_or_>147',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'ast' => array(
            'unit' => 'U/L',
            'range' => '10-40',
            'optimal_range' => '10-30',
            'suboptimal_range' => '31-40',
            'poor_range' => '<10_or_>40',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'alt' => array(
            'unit' => 'U/L',
            'range' => '7-56',
            'optimal_range' => '7-40',
            'suboptimal_range' => '41-56',
            'poor_range' => '<7_or_>56',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Complete Blood Count' => array(
        'wbc' => array(
            'unit' => 'K/µL',
            'range' => '4.5-11.0',
            'optimal_range' => '5.0-10.0',
            'suboptimal_range' => '4.5-5.0_or_10.0-11.0',
            'poor_range' => '<4.5_or_>11.0',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.7, 'Longevity' => 0.6),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'rbc' => array(
            'unit' => 'M/µL',
            'range' => '4.5-5.9',
            'optimal_range' => '4.7-5.7',
            'suboptimal_range' => '4.5-4.7_or_5.7-5.9',
            'poor_range' => '<4.5_or_>5.9',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.7, 'Longevity' => 0.6),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'hemoglobin' => array(
            'unit' => 'g/dL',
            'range' => '13.5-17.5',
            'optimal_range' => '14.0-17.0',
            'suboptimal_range' => '13.5-14.0_or_17.0-17.5',
            'poor_range' => '<13.5_or_>17.5',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.8, 'Longevity' => 0.6),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'hematocrit' => array(
            'unit' => '%',
            'range' => '41-50',
            'optimal_range' => '42-48',
            'suboptimal_range' => '41-42_or_48-50',
            'poor_range' => '<41_or_>50',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.7, 'Longevity' => 0.6),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'mcv' => array(
            'unit' => 'fL',
            'range' => '80-100',
            'optimal_range' => '85-95',
            'suboptimal_range' => '80-85_or_95-100',
            'poor_range' => '<80_or_>100',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'mch' => array(
            'unit' => 'pg',
            'range' => '27-33',
            'optimal_range' => '28-32',
            'suboptimal_range' => '27-28_or_32-33',
            'poor_range' => '<27_or_>33',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'mchc' => array(
            'unit' => 'g/dL',
            'range' => '32-36',
            'optimal_range' => '33-35',
            'suboptimal_range' => '32-33_or_35-36',
            'poor_range' => '<32_or_>36',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'rdw' => array(
            'unit' => '%',
            'range' => '11.5-14.5',
            'optimal_range' => '12.0-14.0',
            'suboptimal_range' => '11.5-12.0_or_14.0-14.5',
            'poor_range' => '<11.5_or_>14.5',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'platelets' => array(
            'unit' => 'K/µL',
            'range' => '150-450',
            'optimal_range' => '200-400',
            'suboptimal_range' => '150-200_or_400-450',
            'poor_range' => '<150_or_>450',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.6, 'Longevity' => 0.5),
            'pillar_impact' => array('Body'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Lipid Panel' => array(
        'cholesterol' => array(
            'unit' => 'mg/dL',
            'range' => '<200',
            'optimal_range' => '<180',
            'suboptimal_range' => '180-200',
            'poor_range' => '≥200',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'fasting_blood_draw',
            'health_vectors' => array('Heart Health' => 0.8, 'Weight Loss' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'triglycerides' => array(
            'unit' => 'mg/dL',
            'range' => '<150',
            'optimal_range' => '<100',
            'suboptimal_range' => '100-150',
            'poor_range' => '≥150',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'fasting_blood_draw',
            'health_vectors' => array('Heart Health' => 0.8, 'Weight Loss' => 0.7),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'hdl' => array(
            'unit' => 'mg/dL',
            'range' => '≥40',
            'optimal_range' => '≥60',
            'suboptimal_range' => '40-59',
            'poor_range' => '<40',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'fasting_blood_draw',
            'health_vectors' => array('Heart Health' => 0.9, 'Longevity' => 0.7),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'vldl' => array(
            'unit' => 'mg/dL',
            'range' => '5-40',
            'optimal_range' => '5-30',
            'suboptimal_range' => '31-40',
            'poor_range' => '<5_or_>40',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'fasting_blood_draw',
            'health_vectors' => array('Heart Health' => 0.7, 'Weight Loss' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'ldl' => array(
            'unit' => 'mg/dL',
            'range' => '<100',
            'optimal_range' => '<70',
            'suboptimal_range' => '70-100',
            'poor_range' => '≥100',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'fasting_blood_draw',
            'health_vectors' => array('Heart Health' => 0.9, 'Weight Loss' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Hormones' => array(
        'testosterone_free' => array(
            'unit' => 'pg/mL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Hormones' => 0.9, 'Libido' => 0.9, 'Strength' => 0.8),
            'pillar_impact' => array('Body', 'Mind'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'testosterone_total' => array(
            'unit' => 'ng/dL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Hormones' => 0.9, 'Libido' => 0.8, 'Strength' => 0.8),
            'pillar_impact' => array('Body', 'Mind'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'lh' => array(
            'unit' => 'mIU/mL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Hormones' => 0.8, 'Libido' => 0.6),
            'pillar_impact' => array('Body', 'Mind'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'fsh' => array(
            'unit' => 'mIU/mL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Hormones' => 0.8, 'Libido' => 0.6),
            'pillar_impact' => array('Body', 'Mind'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'dhea' => array(
            'unit' => 'µg/dL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Hormones' => 0.7, 'Energy' => 0.6, 'Strength' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'prolactin' => array(
            'unit' => 'ng/mL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Hormones' => 0.7, 'Libido' => 0.6),
            'pillar_impact' => array('Body', 'Mind'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Thyroid' => array(
        'vitamin_d' => array(
            'unit' => 'ng/mL',
            'range' => '30-100',
            'optimal_range' => '40-80',
            'suboptimal_range' => '30-40_or_80-100',
            'poor_range' => '<30_or_>100',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.8, 'Cognitive Health' => 0.7, 'Strength' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        'tsh' => array(
            'unit' => 'µIU/mL',
            'range' => '0.4-4.0',
            'optimal_range' => '1.0-2.5',
            'suboptimal_range' => '0.4-1.0_or_2.5-4.0',
            'poor_range' => '<0.4_or_>4.0',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.8, 'Cognitive Health' => 0.7, 'Weight Loss' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        't4' => array(
            'unit' => 'ng/dL',
            'range' => '0.8-1.8',
            'optimal_range' => '1.0-1.6',
            'suboptimal_range' => '0.8-1.0_or_1.6-1.8',
            'poor_range' => '<0.8_or_>1.8',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.7, 'Cognitive Health' => 0.6, 'Weight Loss' => 0.5),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        ),
        't3' => array(
            'unit' => 'pg/mL',
            'range' => '2.3-4.2',
            'optimal_range' => '2.8-3.8',
            'suboptimal_range' => '2.3-2.8_or_3.8-4.2',
            'poor_range' => '<2.3_or_>4.2',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Energy' => 0.7, 'Cognitive Health' => 0.6, 'Weight Loss' => 0.5),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    ),
    
    'Performance' => array(
        'igf_1' => array(
            'unit' => 'ng/mL',
            'range' => 'varies_by_age_gender',
            'optimal_range' => 'varies_by_age_gender',
            'suboptimal_range' => 'varies_by_age_gender',
            'poor_range' => 'varies_by_age_gender',
            'source' => 'ENNU Life Labs',
            'collection_method' => 'blood_draw',
            'health_vectors' => array('Strength' => 0.8, 'Longevity' => 0.7, 'Energy' => 0.6),
            'pillar_impact' => array('Body', 'Lifestyle'),
            'frequency' => 'quarterly',
            'required_for' => array('Comprehensive Diagnostic', 'Premium Membership')
        )
    )
); 