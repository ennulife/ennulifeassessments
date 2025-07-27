<?php
/**
 * Hematology Biomarker Reference Ranges
 * 
 * @package ENNU_Life
 * @specialist Dr. Harlan Vitalis
 * @domain Hematology
 * @biomarkers 9
 * @version 1.0.0
 * @date 2025-01-27
 */

return array(
    'hemoglobin' => array(
        'display_name' => 'Hemoglobin',
        'unit' => 'g/dL',
        'description' => 'Oxygen-carrying protein in red blood cells essential for tissue oxygenation',
        'ranges' => array(
            'optimal_min' => 13.5,
            'optimal_max' => 17.5,
            'normal_min' => 12.0,
            'normal_max' => 18.0,
            'critical_min' => 8.0,
            'critical_max' => 22.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 13.0, 'optimal_max' => 17.0),
            'adult' => array('optimal_min' => 13.5, 'optimal_max' => 17.5),
            'senior' => array('optimal_min' => 12.5, 'optimal_max' => 16.5),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 14.0, 'optimal_max' => 17.5),
            'female' => array('optimal_min' => 12.3, 'optimal_max' => 15.3),
        ),
        'clinical_significance' => 'Hemoglobin is the primary oxygen-carrying protein in red blood cells. Low levels indicate anemia and reduced oxygen delivery to tissues, while high levels may indicate polycythemia or dehydration.',
        'risk_factors' => array('Iron deficiency', 'B12 deficiency', 'Chronic disease', 'Blood loss', 'Genetic disorders', 'Kidney disease', 'Pregnancy'),
        'optimization_recommendations' => array('Iron-rich diet', 'B12 supplementation', 'Treat underlying conditions', 'Monitor for blood loss', 'Regular blood tests', 'Address nutritional deficiencies'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'pallor', 'shortness_of_breath', 'dizziness', 'weakness', 'headache'),
            'range_triggers' => array('below_12_g_dl' => 'moderate', 'below_8_g_dl' => 'high', 'above_18_g_dl' => 'moderate', 'above_22_g_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain hemoglobin >13.5 g/dL', 'Address underlying causes of anemia'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Guidelines for Anemia Management',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: Hemoglobin and Anemia', 'Lancet Haematol 2023: Blood Biomarkers'),
            'evidence_level' => 'A',
        ),
    ),
    'hematocrit' => array(
        'display_name' => 'Hematocrit',
        'unit' => '%',
        'description' => 'Percentage of blood volume occupied by red blood cells indicating blood concentration',
        'ranges' => array(
            'optimal_min' => 40,
            'optimal_max' => 50,
            'normal_min' => 36,
            'normal_max' => 52,
            'critical_min' => 30,
            'critical_max' => 60,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 39, 'optimal_max' => 49),
            'adult' => array('optimal_min' => 40, 'optimal_max' => 50),
            'senior' => array('optimal_min' => 38, 'optimal_max' => 48),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 42, 'optimal_max' => 52),
            'female' => array('optimal_min' => 37, 'optimal_max' => 47),
        ),
        'clinical_significance' => 'Hematocrit reflects the proportion of blood volume occupied by red blood cells. It is directly related to hemoglobin and indicates blood concentration and oxygen-carrying capacity.',
        'risk_factors' => array('Iron deficiency', 'Dehydration', 'Chronic disease', 'Blood loss', 'Altitude', 'Smoking', 'Polycythemia'),
        'optimization_recommendations' => array('Iron supplementation', 'Adequate hydration', 'Address underlying causes', 'Regular monitoring', 'Lifestyle modifications'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'shortness_of_breath', 'dizziness', 'headache', 'dehydration'),
            'range_triggers' => array('below_36_percent' => 'moderate', 'below_30_percent' => 'high', 'above_52_percent' => 'moderate', 'above_60_percent' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain hematocrit 40-50%', 'Optimize hydration status'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'WHO 2023 Blood Cell Reference Ranges',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: Hematocrit and Blood Volume', 'J Hematol 2023: Blood Cell Analysis'),
            'evidence_level' => 'A',
        ),
    ),
    'rbc' => array(
        'display_name' => 'Red Blood Cell Count',
        'unit' => 'million/μL',
        'description' => 'Number of red blood cells per microliter indicating bone marrow function',
        'ranges' => array(
            'optimal_min' => 4.5,
            'optimal_max' => 5.9,
            'normal_min' => 4.0,
            'normal_max' => 6.2,
            'critical_min' => 3.5,
            'critical_max' => 7.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 4.3, 'optimal_max' => 5.7),
            'adult' => array('optimal_min' => 4.5, 'optimal_max' => 5.9),
            'senior' => array('optimal_min' => 4.2, 'optimal_max' => 5.6),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 4.7, 'optimal_max' => 6.1),
            'female' => array('optimal_min' => 4.2, 'optimal_max' => 5.4),
        ),
        'clinical_significance' => 'RBC count indicates bone marrow function and erythropoiesis. Low counts suggest anemia or bone marrow suppression, while high counts may indicate polycythemia or dehydration.',
        'risk_factors' => array('Iron deficiency', 'B12 deficiency', 'Chronic disease', 'Blood loss', 'Bone marrow disorders', 'Kidney disease', 'Medications'),
        'optimization_recommendations' => array('Iron-rich diet', 'B12 supplementation', 'Address underlying causes', 'Regular monitoring', 'Bone marrow evaluation if needed'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'pale_skin', 'weakness', 'shortness_of_breath'),
            'range_triggers' => array('below_4_million_ul' => 'moderate', 'below_3_5_million_ul' => 'high', 'above_6_2_million_ul' => 'moderate', 'above_7_million_ul' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain RBC count 4.5-5.9 million/μL', 'Optimize bone marrow function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'EHA 2023 Erythropoiesis Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: RBC Count and Bone Marrow Function', 'Hematology 2023: Erythropoiesis'),
            'evidence_level' => 'A',
        ),
    ),
    'wbc' => array(
        'display_name' => 'White Blood Cell Count',
        'unit' => 'thousand/μL',
        'description' => 'Number of white blood cells indicating immune function and inflammatory status',
        'ranges' => array(
            'optimal_min' => 4.5,
            'optimal_max' => 11.0,
            'normal_min' => 4.0,
            'normal_max' => 12.0,
            'critical_min' => 3.0,
            'critical_max' => 15.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 4.0, 'optimal_max' => 10.5),
            'adult' => array('optimal_min' => 4.5, 'optimal_max' => 11.0),
            'senior' => array('optimal_min' => 4.0, 'optimal_max' => 11.5),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 4.5, 'optimal_max' => 11.0),
            'female' => array('optimal_min' => 4.0, 'optimal_max' => 10.5),
        ),
        'clinical_significance' => 'WBC count reflects immune function and inflammatory status. Elevated levels may indicate infection, inflammation, or hematologic disorders, while low levels suggest immune suppression.',
        'risk_factors' => array('Infection', 'Inflammation', 'Stress', 'Smoking', 'Obesity', 'Autoimmune conditions', 'Medications', 'Cancer'),
        'optimization_recommendations' => array('Address underlying causes', 'Anti-inflammatory diet', 'Stress management', 'Regular exercise', 'Immune support'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'fever', 'infection', 'inflammation', 'weakness'),
            'range_triggers' => array('below_4_thousand_ul' => 'moderate', 'below_3_thousand_ul' => 'high', 'above_12_thousand_ul' => 'moderate', 'above_15_thousand_ul' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain WBC count 4.5-11.0 thousand/μL', 'Optimize immune function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Immune Function Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: WBC and Immune Function', 'J Immunol 2023: Leukocyte Biology'),
            'evidence_level' => 'A',
        ),
    ),
    'platelets' => array(
        'display_name' => 'Platelet Count',
        'unit' => 'thousand/μL',
        'description' => 'Number of platelets essential for blood clotting and hemostasis',
        'ranges' => array(
            'optimal_min' => 150,
            'optimal_max' => 400,
            'normal_min' => 130,
            'normal_max' => 450,
            'critical_min' => 100,
            'critical_max' => 600,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 140, 'optimal_max' => 390),
            'adult' => array('optimal_min' => 150, 'optimal_max' => 400),
            'senior' => array('optimal_min' => 140, 'optimal_max' => 410),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 150, 'optimal_max' => 400),
            'female' => array('optimal_min' => 140, 'optimal_max' => 390),
        ),
        'clinical_significance' => 'Platelets are essential for blood clotting and hemostasis. Low levels increase bleeding risk, while high levels may indicate inflammation, infection, or myeloproliferative disorders.',
        'risk_factors' => array('Medications', 'Autoimmune conditions', 'Infection', 'Cancer', 'Liver disease', 'Genetic factors', 'Pregnancy'),
        'optimization_recommendations' => array('Address underlying causes', 'Medication review', 'Regular monitoring', 'Specialist consultation', 'Lifestyle modifications'),
        'flag_criteria' => array(
            'symptom_triggers' => array('bleeding', 'bruising', 'fatigue', 'nosebleeds', 'gum_bleeding'),
            'range_triggers' => array('below_130_thousand_ul' => 'moderate', 'below_100_thousand_ul' => 'high', 'above_450_thousand_ul' => 'moderate', 'above_600_thousand_ul' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain platelet count 150-400 thousand/μL', 'Optimize hemostatic function'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Platelet Disorders Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: Platelets and Hemostasis', 'Thromb Haemost 2023: Platelet Function'),
            'evidence_level' => 'A',
        ),
    ),
    'mch' => array(
        'display_name' => 'Mean Corpuscular Hemoglobin',
        'unit' => 'pg',
        'description' => 'Average amount of hemoglobin per red blood cell indicating cell quality',
        'ranges' => array(
            'optimal_min' => 27,
            'optimal_max' => 33,
            'normal_min' => 26,
            'normal_max' => 34,
            'critical_min' => 24,
            'critical_max' => 36,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 26, 'optimal_max' => 32),
            'adult' => array('optimal_min' => 27, 'optimal_max' => 33),
            'senior' => array('optimal_min' => 26, 'optimal_max' => 32),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 27, 'optimal_max' => 33),
            'female' => array('optimal_min' => 26, 'optimal_max' => 32),
        ),
        'clinical_significance' => 'MCH indicates the quality and hemoglobin content of red blood cells. Low levels suggest iron deficiency anemia, while high levels may indicate macrocytic anemia.',
        'risk_factors' => array('Iron deficiency', 'B12 deficiency', 'Folate deficiency', 'Chronic disease', 'Poor diet', 'Blood loss'),
        'optimization_recommendations' => array('Iron supplementation', 'B12 supplementation', 'Folate supplementation', 'Address underlying causes', 'Nutritional optimization'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'pale_skin', 'weakness', 'shortness_of_breath'),
            'range_triggers' => array('below_26_pg' => 'moderate', 'below_24_pg' => 'high', 'above_34_pg' => 'moderate', 'above_36_pg' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain MCH 27-33 pg', 'Optimize red blood cell quality'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Anemia Classification Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: MCH and Anemia', 'Hematology 2023: Red Cell Indices'),
            'evidence_level' => 'A',
        ),
    ),
    'mchc' => array(
        'display_name' => 'Mean Corpuscular Hemoglobin Concentration',
        'unit' => 'g/dL',
        'description' => 'Concentration of hemoglobin in red blood cells indicating cell density',
        'ranges' => array(
            'optimal_min' => 32,
            'optimal_max' => 36,
            'normal_min' => 31,
            'normal_max' => 37,
            'critical_min' => 30,
            'critical_max' => 38,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 31, 'optimal_max' => 35),
            'adult' => array('optimal_min' => 32, 'optimal_max' => 36),
            'senior' => array('optimal_min' => 31, 'optimal_max' => 35),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 32, 'optimal_max' => 36),
            'female' => array('optimal_min' => 31, 'optimal_max' => 35),
        ),
        'clinical_significance' => 'MCHC reflects the concentration of hemoglobin in red blood cells. Low levels indicate hypochromic anemia (iron deficiency), while high levels may suggest spherocytosis or dehydration.',
        'risk_factors' => array('Iron deficiency', 'Chronic disease', 'Poor diet', 'Blood loss', 'Genetic disorders', 'Dehydration'),
        'optimization_recommendations' => array('Iron supplementation', 'Iron-rich diet', 'Address underlying causes', 'Adequate hydration', 'Nutritional optimization'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'pale_skin', 'weakness', 'dehydration'),
            'range_triggers' => array('below_31_g_dl' => 'moderate', 'below_30_g_dl' => 'high', 'above_37_g_dl' => 'moderate', 'above_38_g_dl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain MCHC 32-36 g/dL', 'Optimize hemoglobin concentration'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Iron Deficiency Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: MCHC and Iron Status', 'Hematology 2023: Hemoglobin Concentration'),
            'evidence_level' => 'A',
        ),
    ),
    'mcv' => array(
        'display_name' => 'Mean Corpuscular Volume',
        'unit' => 'fL',
        'description' => 'Average size of red blood cells helping classify anemia types',
        'ranges' => array(
            'optimal_min' => 80,
            'optimal_max' => 100,
            'normal_min' => 75,
            'normal_max' => 105,
            'critical_min' => 70,
            'critical_max' => 110,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 78, 'optimal_max' => 98),
            'adult' => array('optimal_min' => 80, 'optimal_max' => 100),
            'senior' => array('optimal_min' => 78, 'optimal_max' => 98),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 80, 'optimal_max' => 100),
            'female' => array('optimal_min' => 78, 'optimal_max' => 98),
        ),
        'clinical_significance' => 'MCV indicates red blood cell size and helps classify anemia types. Microcytic anemia (low MCV) suggests iron deficiency, while macrocytic anemia (high MCV) suggests B12/folate deficiency.',
        'risk_factors' => array('Iron deficiency', 'B12 deficiency', 'Folate deficiency', 'Chronic disease', 'Poor diet', 'Genetic factors'),
        'optimization_recommendations' => array('Iron supplementation', 'B12 supplementation', 'Folate supplementation', 'Address underlying causes', 'Nutritional optimization'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'pale_skin', 'weakness', 'neurological_symptoms'),
            'range_triggers' => array('below_75_fl' => 'moderate', 'below_70_fl' => 'high', 'above_105_fl' => 'moderate', 'above_110_fl' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain MCV 80-100 fL', 'Optimize red blood cell size'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Anemia Classification Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: MCV and Anemia Types', 'Hematology 2023: Red Cell Morphology'),
            'evidence_level' => 'A',
        ),
    ),
    'rdw' => array(
        'display_name' => 'Red Cell Distribution Width',
        'unit' => '%',
        'description' => 'Variation in red blood cell size indicating anemia heterogeneity',
        'ranges' => array(
            'optimal_min' => 11.5,
            'optimal_max' => 14.5,
            'normal_min' => 11.0,
            'normal_max' => 15.0,
            'critical_min' => 10.0,
            'critical_max' => 16.0,
        ),
        'age_adjustments' => array(
            'young' => array('optimal_min' => 11.0, 'optimal_max' => 14.0),
            'adult' => array('optimal_min' => 11.5, 'optimal_max' => 14.5),
            'senior' => array('optimal_min' => 11.0, 'optimal_max' => 14.0),
        ),
        'gender_adjustments' => array(
            'male' => array('optimal_min' => 11.5, 'optimal_max' => 14.5),
            'female' => array('optimal_min' => 11.0, 'optimal_max' => 14.0),
        ),
        'clinical_significance' => 'RDW indicates variation in red blood cell size. Elevated levels suggest iron deficiency, mixed anemias, or other conditions causing red cell size heterogeneity.',
        'risk_factors' => array('Iron deficiency', 'B12 deficiency', 'Folate deficiency', 'Chronic disease', 'Blood loss', 'Mixed nutritional deficiencies'),
        'optimization_recommendations' => array('Iron supplementation', 'B12 supplementation', 'Folate supplementation', 'Address underlying causes', 'Comprehensive nutritional support'),
        'flag_criteria' => array(
            'symptom_triggers' => array('fatigue', 'pale_skin', 'weakness', 'mixed_symptoms'),
            'range_triggers' => array('above_15_percent' => 'moderate', 'above_16_percent' => 'high', 'below_11_percent' => 'moderate', 'below_10_percent' => 'high'),
        ),
        'scoring_algorithm' => array('optimal_score' => 10, 'suboptimal_score' => 7, 'poor_score' => 4, 'critical_score' => 1),
        'target_setting' => array(
            'improvement_targets' => array('Maintain RDW 11.5-14.5%', 'Optimize red cell uniformity'),
            'timeframes' => array('immediate' => '1 week', 'short_term' => '1 month', 'long_term' => '3-6 months'),
        ),
        'sources' => array(
            'primary' => 'ASH 2023 Red Cell Indices Guidelines',
            'secondary' => array('https://www.ahealthacademy.com/biomarkers/', 'Blood 2023: RDW and Anemia Heterogeneity', 'Hematology 2023: Red Cell Variation'),
            'evidence_level' => 'A',
        ),
    ),
); 