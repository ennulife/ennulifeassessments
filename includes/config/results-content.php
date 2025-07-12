<?php
/**
 * ENNU Life Results Page Content Configuration
 *
 * This file centralizes all personalized content displayed on the results page,
 * making it easy to update messaging for different scores and assessments.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'hair_assessment' => array(
        'title' => 'Your Hair Health Assessment Results',
        'score_ranges' => array(
            'excellent' => array(
                'title' => 'Excellent Hair Health',
                'summary' => 'Your results indicate excellent hair health. Your current regimen and lifestyle are providing a strong foundation.',
                'recommendations' => array('Maintain your current routine.', 'Continue to protect your hair from excessive heat and sun.', 'Consider adding a nourishing hair serum to optimize scalp health.'),
                'cta' => 'Explore Our Maintenance Products'
            ),
            'good' => array(
                'title' => 'Good Hair Health',
                'summary' => 'Your results show good overall hair health, with some minor areas for improvement.',
                'recommendations' => array('Incorporate a weekly deep conditioning treatment.', 'Ensure a diet rich in biotin and iron.', 'Review our targeted treatments for any specific concerns.'),
                'cta' => 'Discover Targeted Treatments'
            ),
            'fair' => array(
                'title' => 'Fair Hair Health',
                'summary' => 'Your hair health is fair, but there are clear opportunities for improvement to prevent future issues.',
                'recommendations' => array('Switch to a sulfate-free shampoo and conditioner.', 'We strongly recommend a consultation to address underlying issues.', 'Consider our Hair Restoration Starter Kit.'),
                'cta' => 'Schedule a Consultation'
            ),
            'needs_attention' => array(
                'title' => 'Your Hair Needs Attention',
                'summary' => 'Your results indicate that your hair health requires attention to address active thinning or loss.',
                'recommendations' => array('It is highly recommended to speak with one of our specialists.', 'Avoid harsh chemical treatments and tight hairstyles.', 'Our medical-grade treatments can help significantly.'),
                'cta' => 'Book a Priority Consultation'
            ),
        )
    ),
    'skin_assessment' => array(
        'title' => 'Your Skin Health Assessment Results',
        'score_ranges' => array(
            'excellent' => array(
                'title' => 'Excellent Skin Health',
                'summary' => 'Congratulations! Your skin is healthy, balanced, and well-cared-for.',
                'recommendations' => array('Maintain your current routine.', 'Always use a broad-spectrum SPF 30+ daily.', 'Introduce antioxidants like Vitamin C to maintain your glow.'),
                'cta' => 'Shop Antioxidant Serums'
            ),
            'good' => array(
                'title' => 'Good Skin Health',
                'summary' => 'Your skin is in good shape, with a few areas that could be optimized.',
                'recommendations' => array('Consider adding a retinol product to your nighttime routine.', 'Ensure you are exfoliating 1-2 times per week.', 'A targeted eye cream can help with fine lines.'),
                'cta' => 'Explore Our Retinol Products'
            ),
            'fair' => array(
                'title' => 'Fair Skin Health',
                'summary' => 'Your results suggest your skin could benefit from a more targeted skincare regimen.',
                'recommendations' => array('A consultation with our skincare expert is recommended.', 'Focus on hydration and a consistent cleansing routine.', 'Avoid products with high concentrations of alcohol.'),
                'cta' => 'Schedule a Skincare Consultation'
            ),
            'needs_attention' => array(
                'title' => 'Your Skin Needs Attention',
                'summary' => 'Your skin is showing signs of stress, damage, or active concerns that require attention.',
                'recommendations' => array('We strongly advise a consultation to create a personalized treatment plan.', 'Our clinical-grade products can help address your specific concerns.', 'Do not try multiple new products at once.'),
                'cta' => 'Book a Priority Consultation'
            ),
        )
    ),
    // Add default/fallback content
    'default' => array(
        'title' => 'Your Assessment Results',
        'score_ranges' => array(
            'excellent' => array(
                'title' => 'Excellent Results',
                'summary' => 'Your assessment results are excellent. You are on the right track with your health and wellness.',
                'recommendations' => array('Maintain your current positive habits.', 'Continue monitoring your health regularly.'),
                'cta' => 'Explore Our Wellness Products'
            ),
            'good' => array(
                'title' => 'Good Results',
                'summary' => 'Your assessment results are good, with some opportunities for optimization.',
                'recommendations' => array('Consider focusing on the areas where your scores were lower.', 'Our specialists can help you create a targeted plan.'),
                'cta' => 'View Our Health Plans'
            ),
            'fair' => array(
                'title' => 'Fair Results',
                'summary' => 'Your results indicate that there are several key areas to focus on for improvement.',
                'recommendations' => array('We recommend a consultation with one of our experts to guide you.', 'Small, consistent changes can make a big difference.'),
                'cta' => 'Schedule a Consultation'
            ),
            'needs_attention' => array(
                'title' => 'Your Health Needs Attention',
                'summary' => 'Your results show some areas that require immediate attention to improve your wellness.',
                'recommendations' => array('We strongly recommend speaking with a specialist to address these results.', 'Our team is here to support you with a personalized plan.'),
                'cta' => 'Book a Priority Consultation'
            ),
        )
    )
); 