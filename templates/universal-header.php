<?php
/**
 * Universal Header Component for ENNU Life
 * 
 * This component provides a consistent header across all ENNU Life pages
 * including user dashboard, assessment results, and other pages.
 * 
 * @package ENNU_Life
 * @version 64.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Variables are already extracted by ennu_load_template function
// No need to extract again

// Default values for header elements
$display_name = $display_name ?? '';
$age = $age ?? '';
$gender = $gender ?? '';
$height = $height ?? '';
$weight = $weight ?? '';
$bmi = $bmi ?? '';
$show_vital_stats = $show_vital_stats ?? true;
$show_theme_toggle = $show_theme_toggle ?? true;
$page_title = $page_title ?? '';
$page_subtitle = $page_subtitle ?? '';
$show_logo = $show_logo ?? true;
$logo_color = $logo_color ?? 'white';
$logo_size = $logo_size ?? 'medium';

// Get current user if not provided
if ( empty( $display_name ) && is_user_logged_in() ) {
	$current_user = wp_get_current_user();
	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$last_name = get_user_meta( $current_user->ID, 'last_name', true );
	$display_name = trim( $first_name . ' ' . $last_name );
	if ( empty( $display_name ) ) {
		$display_name = $current_user->display_name ?? $current_user->user_login ?? 'User';
	}
}

// Get vital stats from user meta if not provided
if ( is_user_logged_in() && $show_vital_stats ) {
	$current_user = wp_get_current_user();
	if ( empty( $age ) ) {
		$age = get_user_meta( $current_user->ID, 'ennu_global_age', true );
		if ( empty( $age ) ) {
			$age = get_user_meta( $current_user->ID, 'ennu_global_exact_age', true );
		}
		if ( empty( $age ) ) {
			$age = get_user_meta( $current_user->ID, 'age', true );
		}
	}
	if ( empty( $gender ) ) {
		$gender = get_user_meta( $current_user->ID, 'ennu_global_gender', true );
		if ( empty( $gender ) ) {
			$gender = get_user_meta( $current_user->ID, 'gender', true );
		}
	}
	if ( empty( $height ) ) {
		$height = get_user_meta( $current_user->ID, 'ennu_global_height', true );
		if ( empty( $height ) ) {
			$height = get_user_meta( $current_user->ID, 'height', true );
		}
	}
	if ( empty( $weight ) ) {
		$weight = get_user_meta( $current_user->ID, 'ennu_global_weight', true );
		if ( empty( $weight ) ) {
			$weight = get_user_meta( $current_user->ID, 'weight', true );
		}
	}
	if ( empty( $bmi ) ) {
		$bmi = get_user_meta( $current_user->ID, 'ennu_global_bmi', true );
		if ( empty( $bmi ) ) {
			$bmi = get_user_meta( $current_user->ID, 'bmi', true );
		}
	}
}
?>

<!-- Universal ENNU Header Component -->
<div class="ennu-universal-header">
	

	
	<!-- Theme Toggle (Optional) -->
	<?php if ( $show_theme_toggle ) : ?>
	<div class="ennu-theme-toggle">
		<button class="ennu-theme-btn" id="theme-toggle" aria-label="Toggle theme">
			<svg class="ennu-theme-icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<circle cx="12" cy="12" r="5"/>
				<line x1="12" y1="1" x2="12" y2="3"/>
				<line x1="12" y1="21" x2="12" y2="23"/>
				<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
				<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
				<line x1="1" y1="12" x2="3" y2="12"/>
				<line x1="21" y1="12" x2="23" y2="12"/>
				<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
				<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
			</svg>
			<svg class="ennu-theme-icon moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
			</svg>
		</button>
	</div>
	<?php endif; ?>

	<!-- Main Header Content -->
	<div class="ennu-header-content">
		
		<!-- Logo Section -->
		<?php if ( $show_logo ) : ?>
		<div class="ennu-header-logo">
			<?php if ( function_exists( 'ennu_render_logo' ) ) : ?>
				<?php
				ennu_render_logo(
					array(
						'color' => $logo_color,
						'size'  => $logo_size,
						'link'  => home_url( '/' ),
						'alt'   => 'ENNU Life',
						'class' => 'ennu-header-logo-img',
					)
				);
				?>
			<?php else : ?>
				<!-- Fallback logo if function not available -->
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ennu-header-logo-link">
					<img src="<?php echo esc_url( ENNU_LIFE_PLUGIN_URL . 'assets/img/ennu-logo-' . $logo_color . '.png' ); ?>" 
						 alt="ENNU Life" 
						 class="ennu-header-logo-img ennu-logo--<?php echo esc_attr( $logo_size ); ?>" />
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<!-- Welcome Section -->
		<div class="ennu-header-welcome">
			<?php if ( ! empty( $page_title ) ) : ?>
				<h1 class="ennu-header-title" id="dynamic-greeting" data-page-type="<?php echo ! empty( $display_name ) ? 'results' : 'default'; ?>"><?php echo esc_html( $page_title ); ?></h1>
			<?php elseif ( ! empty( $display_name ) ) : ?>
				<h1 class="ennu-header-title" id="dynamic-greeting" data-page-type="dashboard"><?php echo esc_html( $display_name ); ?>'s Biometric Canvas</h1>
			<?php endif; ?>
			
			<?php if ( ! empty( $page_subtitle ) ) : ?>
				<p class="ennu-header-subtitle"><?php echo esc_html( $page_subtitle ); ?></p>
			<?php else : ?>
				<p class="ennu-header-subtitle">Track your progress and discover personalized insights for optimal health.</p>
			<?php endif; ?>
		</div>

		<!-- Vital Statistics Display -->
		<?php if ( $show_vital_stats && ( ! empty( $age ) || ! empty( $gender ) || ! empty( $height ) || ! empty( $weight ) || ! empty( $bmi ) ) ) : ?>
		<div class="ennu-header-vital-stats">
			<?php if ( ! empty( $age ) ) : ?>
			<div class="ennu-vital-stat-item">
				<span class="ennu-vital-stat-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
						<circle cx="12" cy="7" r="4"/>
					</svg>
				</span>
				<span class="ennu-vital-stat-value"><?php echo esc_html( $age ); ?> years</span>
			</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $gender ) ) : ?>
			<div class="ennu-vital-stat-item">
				<span class="ennu-vital-stat-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<circle cx="12" cy="12" r="10"/>
						<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
					</svg>
				</span>
				<span class="ennu-vital-stat-value"><?php echo esc_html( $gender ); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $height ) ) : ?>
			<div class="ennu-vital-stat-item">
				<span class="ennu-vital-stat-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
						<path d="M3 6h18M3 12h18M3 18h18"/>
					</svg>
				</span>
				<span class="ennu-vital-stat-value"><?php echo esc_html( $height ); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $weight ) ) : ?>
			<div class="ennu-vital-stat-item">
				<span class="ennu-vital-stat-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
						<path d="M12 6v6M8 12h8"/>
					</svg>
				</span>
				<span class="ennu-vital-stat-value"><?php echo esc_html( $weight ); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $bmi ) ) : ?>
			<div class="ennu-vital-stat-item">
				<span class="ennu-vital-stat-icon">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
						<path d="M3 3h18v18H3z"/>
						<path d="M9 9h6v6H9z"/>
						<path d="M12 3v18M3 12h18"/>
					</svg>
				</span>
				<span class="ennu-vital-stat-value">BMI: <?php echo esc_html( $bmi ); ?></span>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- Dynamic Greeting Script -->
<?php if ( ! empty( $display_name ) ) : ?>
<script>
// Dynamic greeting system using user's local time
(function() {
	function getLocalTimeGreeting(displayName, pageType = 'dashboard') {
		const hour = new Date().getHours();
		
		// Morning variations (5 AM - 12 PM)
		const morningGreetings = {
			dashboard: [
			`Good morning, ${displayName}`,
			`Morning, ${displayName}`,
			`Rise and shine, ${displayName}`,
			`Good morning, ${displayName}`,
			`Morning, ${displayName}`
			],
			results: [
				`Good morning, ${displayName}! Your results are ready`,
				`Morning, ${displayName}! Here are your results`,
				`Rise and shine, ${displayName}! Your assessment results`,
				`Good morning, ${displayName}! Your personalized results`,
				`Morning, ${displayName}! Your health insights await`
			]
		};
		
		// Afternoon variations (12 PM - 5 PM)
		const afternoonGreetings = {
			dashboard: [
			`Good afternoon, ${displayName}`,
			`Afternoon, ${displayName}`,
			`Good afternoon, ${displayName}`,
			`Afternoon, ${displayName}`,
			`Good afternoon, ${displayName}`
			],
			results: [
				`Good afternoon, ${displayName}! Your results are ready`,
				`Afternoon, ${displayName}! Here are your results`,
				`Good afternoon, ${displayName}! Your assessment results`,
				`Afternoon, ${displayName}! Your personalized results`,
				`Good afternoon, ${displayName}! Your health insights await`
			]
		};
		
		// Evening variations (5 PM - 9 PM)
		const eveningGreetings = {
			dashboard: [
			`Good evening, ${displayName}`,
			`Evening, ${displayName}`,
			`Good evening, ${displayName}`,
			`Evening, ${displayName}`,
			`Good evening, ${displayName}`
			],
			results: [
				`Good evening, ${displayName}! Your results are ready`,
				`Evening, ${displayName}! Here are your results`,
				`Good evening, ${displayName}! Your assessment results`,
				`Evening, ${displayName}! Your personalized results`,
				`Good evening, ${displayName}! Your health insights await`
			]
		};
		
		// Night variations (9 PM - 12 AM)
		const nightGreetings = {
			dashboard: [
			`Good evening, ${displayName}`,
			`Evening, ${displayName}`,
			`Good evening, ${displayName}`,
			`Evening, ${displayName}`,
			`Good evening, ${displayName}`
			],
			results: [
				`Good evening, ${displayName}! Your results are ready`,
				`Evening, ${displayName}! Here are your results`,
				`Good evening, ${displayName}! Your assessment results`,
				`Evening, ${displayName}! Your personalized results`,
				`Good evening, ${displayName}! Your health insights await`
			]
		};
		
		// Late night variations (12 AM - 5 AM)
		const lateNightGreetings = {
			dashboard: [
			`Still up, ${displayName}?`,
			`Late night, ${displayName}`,
			`Night owl, ${displayName}`,
			`Late night, ${displayName}`,
			`Still awake, ${displayName}?`
			],
			results: [
				`Still up, ${displayName}? Your results are ready`,
				`Late night, ${displayName}! Here are your results`,
				`Night owl, ${displayName}! Your assessment results`,
				`Late night, ${displayName}! Your personalized results`,
				`Still awake, ${displayName}? Your health insights await`
			]
		};
		
		// Select greeting based on user's local time and page type
		let greetings;
		if (hour >= 5 && hour < 12) {
			greetings = morningGreetings[pageType] || morningGreetings.dashboard;
		} else if (hour >= 12 && hour < 17) {
			greetings = afternoonGreetings[pageType] || afternoonGreetings.dashboard;
		} else if (hour >= 17 && hour < 21) {
			greetings = eveningGreetings[pageType] || eveningGreetings.dashboard;
		} else if (hour >= 21 && hour < 24) {
			greetings = nightGreetings[pageType] || nightGreetings.dashboard;
		} else {
			greetings = lateNightGreetings[pageType] || lateNightGreetings.dashboard;
		}
		
		// Randomly select one greeting
		return greetings[Math.floor(Math.random() * greetings.length)];
	}
	
	// Update the greeting when page loads
	document.addEventListener('DOMContentLoaded', function() {
		const greetingElement = document.getElementById('dynamic-greeting');
		if (greetingElement) {
			const displayName = '<?php echo esc_js( $display_name ); ?>';
			const pageType = greetingElement.getAttribute('data-page-type') || 'dashboard';
			
			// Update greeting for both dashboard and results pages
			greetingElement.textContent = getLocalTimeGreeting(displayName, pageType);
		}
	});
})();
</script>
<?php endif; ?> 