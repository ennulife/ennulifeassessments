<?php
/**
 * Health Pillar Map Configuration
 * 
 * Maps assessment categories to the four health pillars:
 * - Mind: Cognitive function, mental clarity, mood stability
 * - Body: Cardiovascular health, metabolic function, hormonal balance
 * - Lifestyle: Exercise frequency, nutrition quality, sleep patterns
 * - Aesthetics: Skin health, body composition, physical appearance
 *
 * @package ENNU_Life
 * @version 64.48.0
 */

return array(
	'Mind' => array(
		'categories' => array(
			'cognitive_function',
			'mental_clarity',
			'mood_stability',
			'stress_management',
			'mental_health',
			'cognitive_performance',
			'emotional_wellbeing',
			'mental_focus',
			'cognitive_health',
			'mental_energy',
			'cognitive_clarity',
			'mental_stamina',
			'cognitive_balance',
			'mental_resilience',
			'cognitive_vitality'
		),
		'weight' => 0.25,
	),
	'Body' => array(
		'categories' => array(
			'cardiovascular_health',
			'metabolic_function',
			'hormonal_balance',
			'immune_function',
			'physical_health',
			'cardiovascular_performance',
			'metabolic_efficiency',
			'hormonal_optimization',
			'immune_resilience',
			'physical_vitality',
			'cardiovascular_efficiency',
			'metabolic_health',
			'hormonal_wellness',
			'immune_strength',
			'physical_energy',
			'cardiovascular_fitness',
			'metabolic_balance',
			'hormonal_harmony',
			'immune_vitality',
			'physical_stamina'
		),
		'weight' => 0.35,
	),
	'Lifestyle' => array(
		'categories' => array(
			'exercise_frequency',
			'nutrition_quality',
			'sleep_patterns',
			'stress_management',
			'lifestyle_habits',
			'exercise_consistency',
			'nutrition_balance',
			'sleep_quality',
			'stress_resilience',
			'lifestyle_optimization',
			'exercise_intensity',
			'nutrition_adequacy',
			'sleep_duration',
			'stress_recovery',
			'lifestyle_sustainability',
			'exercise_variety',
			'nutrition_timing',
			'sleep_consistency',
			'stress_adaptation',
			'lifestyle_integration'
		),
		'weight' => 0.25,
	),
	'Aesthetics' => array(
		'categories' => array(
			'skin_health',
			'body_composition',
			'physical_appearance',
			'skin_vitality',
			'body_toning',
			'physical_attractiveness',
			'skin_clarity',
			'body_definition',
			'physical_presence',
			'skin_radiance',
			'body_strength',
			'physical_confidence',
			'skin_health_optimization',
			'body_composition_balance',
			'physical_appearance_enhancement',
			'skin_wellness',
			'body_fitness',
			'physical_vitality',
			'skin_beauty',
			'body_wellness'
		),
		'weight' => 0.15,
	),
); 