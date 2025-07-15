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
	'hair_assessment'         => array(
		'title'                       => 'Your Hair Health Assessment Results',
		'score_ranges'                => array(
			'excellent'       => array(
				'title'           => 'Excellent Hair Health',
				'summary'         => 'Your results indicate excellent hair health. Your current regimen and lifestyle are providing a strong foundation.',
				'recommendations' => array( 'Maintain your current routine.', 'Continue to protect your hair from excessive heat and sun.', 'Consider adding a nourishing hair serum to optimize scalp health.' ),
				'cta'             => 'Explore Our Maintenance Products',
			),
			'good'            => array(
				'title'           => 'Good Hair Health',
				'summary'         => 'Your results show good overall hair health, with some minor areas for improvement.',
				'recommendations' => array( 'Incorporate a weekly deep conditioning treatment.', 'Ensure a diet rich in biotin and iron.', 'Review our targeted treatments for any specific concerns.' ),
				'cta'             => 'Discover Targeted Treatments',
			),
			'fair'            => array(
				'title'           => 'Fair Hair Health',
				'summary'         => 'Your hair health is fair, but there are clear opportunities for improvement to prevent future issues.',
				'recommendations' => array( 'Switch to a sulfate-free shampoo and conditioner.', 'We strongly recommend a consultation to address underlying issues.', 'Consider our Hair Restoration Starter Kit.' ),
				'cta'             => 'Schedule a Consultation',
			),
			'needs_attention' => array(
				'title'           => 'Your Hair Needs Attention',
				'summary'         => 'Your results indicate that your hair health requires attention to address active thinning or loss.',
				'recommendations' => array( 'It is highly recommended to speak with one of our specialists.', 'Avoid harsh chemical treatments and tight hairstyles.', 'Our medical-grade treatments can help significantly.' ),
				'cta'             => 'Book a Priority Consultation',
			),
		),
		'conditional_recommendations' => array(
			'family_history' => array(
				'both'   => 'We noticed you have a family history of hair loss on both sides. This is a significant factor, and we strongly recommend a consultation to discuss preventative treatments.',
				'father' => 'A family history on your father\'s side is a key indicator. Proactive treatment can be very effective.',
				'mother' => 'A family history on your mother\'s side is a key indicator. Proactive treatment can be very effective.',
			),
			'stress_level'   => array(
				'high'      => 'High stress levels can significantly impact hair health. We recommend exploring stress management techniques alongside any hair treatment.',
				'very_high' => 'Very high stress is a critical factor for hair loss. It is essential to address stress levels for any hair treatment to be effective.',
			),
			'speed'          => array(
				'fast'      => 'You indicated that your hair loss is progressing quickly. We recommend scheduling a consultation soon to address this.',
				'very_fast' => 'You indicated that your hair loss is progressing very quickly. It is critical to schedule a consultation as soon as possible to prevent further loss.',
			),
		),
	),
	'ed_treatment_assessment' => array(
		'title'                       => 'Your ED Treatment Assessment Results',
		'score_ranges'                => array(
			'excellent'       => array(
				'title'           => 'Excellent Profile for Treatment',
				'summary'         => 'Your profile indicates you are a strong candidate for treatment with a high likelihood of success.',
				'recommendations' => array( 'Maintain a healthy lifestyle, including regular exercise and a balanced diet.', 'Our specialists can help you find the most suitable treatment to meet your goals.', 'Continue to monitor your health with regular check-ups.' ),
				'cta'             => 'Explore Treatment Options',
			),
			'good'            => array(
				'title'           => 'Good Profile for Treatment',
				'summary'         => 'You are a good candidate for ED treatment. Addressing a few underlying factors can improve outcomes.',
				'recommendations' => array( 'Focus on stress management and ensuring adequate sleep.', 'A consultation can help pinpoint the most effective treatment for your specific situation.', 'Consider discussing your overall health with our specialists.' ),
				'cta'             => 'Discuss Your Options',
			),
			'fair'            => array(
				'title'           => 'Good Candidate, with Considerations',
				'summary'         => 'You are a candidate for treatment, but some health factors need to be considered to ensure safety and effectiveness.',
				'recommendations' => array( 'A consultation is essential to discuss your health history and any potential medication interactions.', 'Lifestyle changes may be recommended alongside medical treatment.', 'Our team will create a plan that is safe for you.' ),
				'cta'             => 'Schedule a Medical Consultation',
			),
			'needs_attention' => array(
				'title'           => 'Requires a Medical Consultation',
				'summary'         => 'Your results indicate underlying health factors that require a thorough medical review before proceeding with treatment.',
				'recommendations' => array( 'It is critical to have a full consultation with a specialist before starting any treatment.', 'Please be prepared to discuss your full medical history and current medications.', 'Our primary goal is to ensure your safety and well-being.' ),
				'cta'             => 'Book a Priority Medical Review',
			),
		),
		'conditional_recommendations' => array(
			'health_conditions' => array(
				'diabetes'     => 'We noted you have diabetes, which is a significant factor in ED. Managing blood sugar is a critical part of a successful treatment plan.',
				'heart'        => 'Because you have heart disease, a full medical consultation is required to ensure any ED treatment is safe for you.',
				'hypertension' => 'High blood pressure can impact ED and its treatment. We will need to review your current medications and health status.',
			),
			'stress_level'      => array(
				'high'      => 'High stress is a major contributor to ED. We recommend incorporating stress-reduction techniques into your daily routine.',
				'very_high' => 'You indicated very high stress levels, which can be a primary cause of ED. It\'s essential to address this for long-term success.',
			),
			'medications'       => array(
				'antidepressants' => 'Some antidepressants can affect sexual function. A consultation is important to review your current medications.',
				'blood_pressure'  => 'Blood pressure medications can be related to ED. Our medical team will help you find a safe and effective treatment plan.',
			),
		),
	),
	'weight_loss_assessment'  => array(
		'title'                       => 'Your Weight Loss Assessment Results',
		'score_ranges'                => array(
			'excellent'       => array(
				'title'           => 'Strong Foundation for Success',
				'summary'         => 'You have a strong foundation of healthy habits and a positive mindset, which are key for successful and sustainable weight management.',
				'recommendations' => array( 'Focus on consistency and fine-tuning your current routine.', 'Our programs can help you optimize your results and break through plateaus.', 'Consider adding strength training to boost your metabolism.' ),
				'cta'             => 'Explore Advanced Programs',
			),
			'good'            => array(
				'title'           => 'Good Foundation, Ready for Progress',
				'summary'         => 'You have several key habits in place. With a structured plan, you are well-positioned to make significant progress.',
				'recommendations' => array( 'A personalized nutrition and exercise plan can accelerate your results.', 'Let\'s identify and address any hidden barriers to your success.', 'Our medical weight loss options can provide an effective boost.' ),
				'cta'             => 'Get Your Personalized Plan',
			),
			'fair'            => array(
				'title'           => 'Ready for a Structured Change',
				'summary'         => 'Your results show you are ready for a change, and a structured approach will be essential for your success.',
				'recommendations' => array( 'A consultation is the best first step to build a foundational plan that works for you.', 'We recommend focusing on small, sustainable changes to your diet and activity levels.', 'Our team can provide the accountability and support you need.' ),
				'cta'             => 'Schedule Your Consultation',
			),
			'needs_attention' => array(
				'title'           => 'A Comprehensive Plan is Needed',
				'summary'         => 'Your assessment indicates that several key areas require attention to build a foundation for successful weight loss.',
				'recommendations' => array( 'A medical consultation is highly recommended to create a safe and effective plan.', 'We will work with you to address lifestyle factors like sleep and stress.', 'Our comprehensive programs are designed to support you every step of the way.' ),
				'cta'             => 'Book a Comprehensive Review',
			),
		),
		'conditional_recommendations' => array(
			'diet_quality'       => array(
				'unhealthy' => 'You mentioned your diet is mostly unhealthy. This is the most critical area to address. Focusing on whole foods and reducing processed items is the first step.',
			),
			'exercise_frequency' => array(
				'never' => 'You noted that you currently do not exercise. Introducing even 15-20 minutes of walking each day can have a significant positive impact.',
			),
			'sleep_quality'      => array(
				'less_5' => 'Getting less than 5 hours of sleep can significantly impact the hormones that control hunger and metabolism. Prioritizing sleep is key.',
			),
		),
	),
	'health_assessment'       => array(
		'title'                       => 'Your General Health Assessment Results',
		'score_ranges'                => array(
			'excellent'       => array(
				'title'           => 'Excellent Health Foundation',
				'summary'         => 'You have excellent foundational health habits. Your lifestyle choices are actively contributing to your long-term wellness.',
				'recommendations' => array( 'Continue your great work.', 'Consider advanced health tracking or bloodwork to optimize further.', 'Explore our longevity and peak performance programs.' ),
				'cta'             => 'View Peak Performance Programs',
			),
			'good'            => array(
				'title'           => 'Good Health Habits in Place',
				'summary'         => 'You have a solid foundation for good health. A few targeted adjustments could yield significant improvements.',
				'recommendations' => array( 'Let\'s identify which lifestyle factors can be optimized for the biggest impact.', 'A consultation can help create a plan to take your health to the next level.', 'Focus on consistency in your diet and exercise routines.' ),
				'cta'             => 'Optimize Your Health',
			),
			'fair'            => array(
				'title'           => 'Opportunities for Improvement',
				'summary'         => 'Your results show that there are clear opportunities to improve your daily habits and enhance your overall wellness.',
				'recommendations' => array( 'We recommend focusing on one or two key areas to start, such as sleep or nutrition.', 'A consultation can provide a clear, structured path forward.', 'Small, consistent steps are the key to long-term success.' ),
				'cta'             => 'Schedule a Wellness Consultation',
			),
			'needs_attention' => array(
				'title'           => 'Key Areas Need Attention',
				'summary'         => 'Your assessment has highlighted some key areas that require attention to build a stronger foundation for your health.',
				'recommendations' => array( 'We strongly recommend a consultation to discuss these results and create a plan.', 'Addressing foundational pillars like sleep and stress is a critical first step.', 'Our team is here to provide the support and guidance you need.' ),
				'cta'             => 'Book a Health Review',
			),
		),
		'conditional_recommendations' => array(
			'exercise_frequency' => array(
				'rarely' => 'You mentioned that you rarely or never exercise. Regular physical activity is one of the most powerful things you can do for your health.',
			),
			'sleep_quality'      => array(
				'poor' => 'You indicated poor sleep quality. Restful sleep is essential for recovery, energy, and overall health. This is a critical area to address.',
			),
			'preventive_care'    => array(
				'never' => 'Regular check-ups are key to preventive health. We recommend scheduling a visit with a primary care physician.',
			),
		),
	),
	'skin_assessment'         => array(
		'title'                       => 'Your Skin Health Assessment Results',
		'score_ranges'                => array(
			'excellent'       => array(
				'title'           => 'Excellent Skin Health',
				'summary'         => 'Congratulations! Your skin is healthy, balanced, and well-cared-for.',
				'recommendations' => array( 'Maintain your current routine.', 'Always use a broad-spectrum SPF 30+ daily.', 'Introduce antioxidants like Vitamin C to maintain your glow.' ),
				'cta'             => 'Shop Antioxidant Serums',
			),
			'good'            => array(
				'title'           => 'Good Skin Health',
				'summary'         => 'Your skin is in good shape, with a few areas that could be optimized.',
				'recommendations' => array( 'Consider adding a retinol product to your nighttime routine.', 'Ensure you are exfoliating 1-2 times per week.', 'A targeted eye cream can help with fine lines.' ),
				'cta'             => 'Explore Our Retinol Products',
			),
			'fair'            => array(
				'title'           => 'Fair Skin Health',
				'summary'         => 'Your results suggest your skin could benefit from a more targeted skincare regimen.',
				'recommendations' => array( 'A consultation with our skincare expert is recommended.', 'Focus on hydration and a consistent cleansing routine.', 'Avoid products with high concentrations of alcohol.' ),
				'cta'             => 'Schedule a Skincare Consultation',
			),
			'needs_attention' => array(
				'title'           => 'Your Skin Needs Attention',
				'summary'         => 'Your skin is showing signs of stress, damage, or active concerns that require attention.',
				'recommendations' => array( 'We strongly advise a consultation to create a personalized treatment plan.', 'Our clinical-grade products can help address your specific concerns.', 'Do not try multiple new products at once.' ),
				'cta'             => 'Book a Priority Consultation',
			),
		),
		'conditional_recommendations' => array(
			'sun_exposure'     => array(
				'daily_no_spf' => 'You indicated daily sun exposure without using sunscreen. This is the single most significant factor in skin aging and increases your risk for skin cancer. Consistent, daily SPF use is critical.',
			),
			'skincare_routine' => array(
				'none' => 'You mentioned you don\'t currently have a skincare routine. Starting with a simple "cleanse, moisturize, and SPF" routine can make a huge difference.',
			),
			'primary_concern'  => array(
				'acne'     => 'For active acne and blemishes, a targeted approach is often needed. We can help with prescription-grade solutions.',
				'wrinkles' => 'Addressing fine lines and wrinkles can be done effectively with ingredients like retinoids and peptides. A consultation can determine the best strength for you.',
			),
		),
	),
	// Add default/fallback content
	'default'                 => array(
		'title'        => 'Your Assessment Results',
		'score_ranges' => array(
			'excellent'       => array(
				'title'           => 'Excellent Results',
				'summary'         => 'Your assessment results are excellent. You are on the right track with your health and wellness.',
				'recommendations' => array( 'Maintain your current positive habits.', 'Continue monitoring your health regularly.' ),
				'cta'             => 'Explore Our Wellness Products',
			),
			'good'            => array(
				'title'           => 'Good Results',
				'summary'         => 'Your assessment results are good, with some opportunities for optimization.',
				'recommendations' => array( 'Consider focusing on the areas where your scores were lower.', 'Our specialists can help you create a targeted plan.' ),
				'cta'             => 'View Our Health Plans',
			),
			'fair'            => array(
				'title'           => 'Fair Results',
				'summary'         => 'Your results indicate that there are several key areas to focus on for improvement.',
				'recommendations' => array( 'We recommend a consultation with one of our experts to guide you.', 'Small, consistent changes can make a big difference.' ),
				'cta'             => 'Schedule a Consultation',
			),
			'needs_attention' => array(
				'title'           => 'Your Health Needs Attention',
				'summary'         => 'Your results show some areas that require immediate attention to improve your wellness.',
				'recommendations' => array( 'We strongly recommend speaking with a specialist to address these results.', 'Our team is here to support you with a personalized plan.' ),
				'cta'             => 'Book a Priority Consultation',
			),
		),
	),
);
