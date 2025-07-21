<?php
/**
 * Recommendation Engine Configuration
 *
 * This file contains the rules and text for generating personalized recommendations.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

return array(
    'low_score_threshold' => 5.5,
    'recommendations' => array(
        'default_low_score' => 'Your score for {category} is an area for improvement. We recommend focusing on lifestyle and nutritional support to improve your score.',
        'health_goals' => array(
            'longevity' => 'To support your goal of Longevity, consider exploring our advanced biomarker testing to get a deeper understanding of your cellular health.',
            'energy' => 'To boost your energy levels, we recommend focusing on sleep quality and mitochondrial support.',
            // ... other health goal recommendations
        ),
        'triggered_vectors' => array(
            'Heart Health' => 'Your symptoms indicate a potential issue with Heart Health. We strongly recommend consulting with a healthcare professional and considering a full cardiovascular workup.',
            'Cognitive Health' => 'To support your Cognitive Health, we recommend exploring our brain health protocols and ensuring adequate intake of essential fatty acids.',
            // ... other vector recommendations
        ),
    ),
); 