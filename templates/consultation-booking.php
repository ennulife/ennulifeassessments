<?php
/**
 * Template for displaying consultation booking pages - Bio-Metric Canvas Consultation Overture
 * This template is now a "dumb" component. All data fetching and processing
 * is handled in the `render_consultation_shortcode` method.
 *
 * @version 64.6.38
 * @see ENNU_Assessment_Shortcodes::render_consultation_shortcode()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All data is passed in via the $data variable, which is extracted by ennu_load_template().
// This removes the need for local variables with null coalescing.

// Defensive checks for required variables
$consultation_type     = $consultation_type ?? '';
$consultation_config   = $consultation_config ?? array();
$embed_code           = $embed_code ?? '';
$meeting_type         = $meeting_type ?? '';
$user_data            = $user_data ?? array();
$pre_populate_fields  = $pre_populate_fields ?? array( 'firstname', 'lastname', 'email' );
$shortcode_instance   = $shortcode_instance ?? null;

if ( empty( $shortcode_instance ) || ! is_object( $shortcode_instance ) ) {
	echo '<div class="ennu-error">ERROR: Shortcode instance is missing. Please contact support.</div>';
	return;
}

// Defensive check for consultation_type
if ( empty( $consultation_type ) ) {
	echo '<div class="ennu-error">ERROR: Consultation type is missing. Please contact support.</div>';
	return;
}

// Defensive check for consultation_config
if ( empty( $consultation_config ) ) {
	echo '<div class="ennu-error">ERROR: Consultation configuration is missing. Please contact support.</div>';
	return;
}

// Hardcoded HubSpot embed code for reliable functionality
$embed_code = '<!-- Start of Meetings Embed Script -->
    <div class="meetings-iframe-container" data-src="https://meetings.hubspot.com/lescobar2/ennulife?embed=true"></div>
    <script type="text/javascript" src="https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js"></script>
  <!-- End of Meetings Embed Script -->';

?>

<?php
// Prepare header data for universal header
$header_data = array(
	'display_name' => '',
	'age' => '',
	'gender' => '',
	'height' => '',
	'weight' => '',
	'bmi' => '',
	'show_vital_stats' => false, // Don't show vital stats on consultation page
	'show_theme_toggle' => true,
	'page_title' => $shortcode_instance->get_dynamic_consultation_title( $consultation_type, $consultation_config ),
	'page_subtitle' => $consultation_config['description'] ?? '',
	'show_logo' => true,
	'logo_color' => 'black',
	'logo_size' => 'medium'
);

// Load the universal header component
ennu_load_template( 'universal-header', $header_data );
?>

<div class="ennu-consultation-dashboard" data-theme="light">

	<main class="consultation-main-content">
			
			<!-- HubSpot Meeting Embed - Directly beneath subtitle -->
			<?php if ( ! empty( $embed_code ) ) : ?>
				<div class="consultation-embed-container"
					data-consultation-type="<?php echo esc_attr( $consultation_type ); ?>"
					data-meeting-type="<?php echo esc_attr( $meeting_type ); ?>"
					data-user-data="<?php echo esc_attr( json_encode( $user_data ) ); ?>"
					data-pre-populate="<?php echo esc_attr( json_encode( $pre_populate_fields ) ); ?>">
					<?php echo $embed_code; ?>
				</div>
			<?php else : ?>
				<div class="consultation-placeholder">
					<div class="placeholder-icon">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="64" height="64">
							<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
							<line x1="16" y1="2" x2="16" y2="6"/>
							<line x1="8" y1="2" x2="8" y2="6"/>
							<line x1="3" y1="10" x2="21" y2="10"/>
						</svg>
					</div>
					<h3>Booking Calendar Not Configured</h3>
					<p>Please configure the HubSpot calendar embed for this consultation type in the admin settings.</p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=ennu-life-hubspot-booking' ) ); ?>" class="btn btn-primary">
						Configure Booking Settings
					</a>
				</div>
			<?php endif; ?>

		<!-- Hero Card -->
		<div class="consultation-hero-card ennu-animate-in" style="background: <?php echo esc_attr( $consultation_config['gradient'] ?? $consultation_config['color'] ?? '#10b981' ); ?>;">
			<div class="hero-card-content">
				<div class="consultation-icon">
					<?php echo wp_kses_post( $consultation_config['icon'] ?? '' ); ?>
				</div>
				<div class="hero-text">
					<h2 class="hero-title">Ready to Transform Your Health?</h2>
					<p class="hero-description">Schedule your personalized consultation with our expert team and take the first step towards optimal wellness.</p>
				</div>
			</div>
		</div>

		<!-- Benefits Section -->
		<?php if ( ! empty( $consultation_config['benefits'] ) && is_array( $consultation_config['benefits'] ) ) : ?>
			<div class="consultation-benefits-card ennu-animate-in ennu-animate-delay-1">
				<h2 class="section-title">What to Expect from Your Consultation</h2>
				<div class="benefits-grid">
					<?php foreach ( $consultation_config['benefits'] as $index => $benefit ) : ?>
						<div class="benefit-item">
							<div class="benefit-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
									<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
									<polyline points="22,4 12,14.01 9,11.01"/>
								</svg>
							</div>
							<div class="benefit-text"><?php echo esc_html( $benefit ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Contact Section -->
		<?php if ( ! empty( $consultation_config['contact_label'] ) || ! empty( $consultation_config['phone'] ) || ! empty( $consultation_config['email'] ) ) : ?>
			<div class="consultation-contact-card ennu-animate-in ennu-animate-delay-3">
				<?php if ( ! empty( $consultation_config['contact_label'] ) ) : ?>
					<h3 class="section-title"><?php echo esc_html( $consultation_config['contact_label'] ); ?></h3>
				<?php endif; ?>
				<div class="contact-grid">
					<?php if ( ! empty( $consultation_config['phone'] ) ) : ?>
						<div class="contact-item">
							<div class="contact-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
									<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
								</svg>
							</div>
							<div class="contact-details">
								<div class="contact-label">Phone</div>
								<div class="contact-value">
									<a href="tel:<?php echo esc_attr( $consultation_config['phone'] ); ?>"><?php echo esc_html( $consultation_config['phone_display'] ?? $consultation_config['phone'] ); ?></a>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $consultation_config['email'] ) ) : ?>
						<div class="contact-item">
							<div class="contact-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
									<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
									<polyline points="22,6 12,13 2,6"/>
								</svg>
							</div>
							<div class="contact-details">
								<div class="contact-label">Email</div>
								<div class="contact-value">
									<a href="mailto:<?php echo esc_attr( $consultation_config['email'] ); ?>"><?php echo esc_html( $consultation_config['email'] ); ?></a>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $consultation_config['extra_section'] ) ) : ?>
					<div class="extra-section">
						<?php echo wp_kses_post( $consultation_config['extra_section'] ); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		</main>
		</div>
<style>
/* ENNU Consultation Dashboard - Modern Design System */
.ennu-consultation-dashboard {
	min-height: 100vh;
	background: var(--base-bg, #f8fafc);
	color: var(--text-color, #1a202c);
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
	line-height: 1.6;
	position: relative;
	overflow-x: hidden;
}

/* CSS Variables for Theme System */
.ennu-consultation-dashboard[data-theme="light"] {
	--base-bg: #f8fafc;
	--card-bg: rgba(255, 255, 255, 0.8);
	--glass-bg: rgba(255, 255, 255, 0.1);
	--glass-border: rgba(255, 255, 255, 0.2);
	--text-color: #1a202c;
	--text-secondary: #4a5568;
	--text-muted: #718096;
	--border-color: #e2e8f0;
	--accent-primary: #10b981;
	--accent-secondary: #059669;
	--shadow-color: rgba(0, 0, 0, 0.1);
	--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
	--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
	--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
	--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
	--rounded-sm: 0.125rem;
	--rounded-md: 0.375rem;
	--rounded-lg: 0.5rem;
	--rounded-xl: 0.75rem;
	--rounded-2xl: 1rem;
	--rounded-full: 9999px;
}

.ennu-consultation-dashboard[data-theme="dark"] {
	--base-bg: #0f172a;
	--card-bg: rgba(30, 41, 59, 0.8);
	--glass-bg: rgba(30, 41, 59, 0.1);
	--glass-border: rgba(148, 163, 184, 0.2);
	--text-color: #f1f5f9;
	--text-secondary: #cbd5e1;
	--text-muted: #94a3b8;
	--border-color: #334155;
	--accent-primary: #10b981;
	--accent-secondary: #059669;
	--shadow-color: rgba(0, 0, 0, 0.3);
	--shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
	--shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
	--shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
	--shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
}

/* Theme Toggle */
.theme-toggle-container {
	position: fixed;
	top: 2rem;
	right: 2rem;
	z-index: 1000;
}

.theme-toggle {
	background: var(--card-bg);
	border: 1px solid var(--border-color);
	border-radius: var(--rounded-full);
	padding: 0.5rem;
	cursor: pointer;
	transition: all 0.3s ease;
	backdrop-filter: blur(10px);
	box-shadow: var(--shadow-md);
}

.theme-toggle:hover {
	transform: scale(1.05);
	box-shadow: var(--shadow-lg);
}

.toggle-track {
	width: 3rem;
	height: 1.5rem;
	background: var(--accent-primary);
	border-radius: var(--rounded-full);
	position: relative;
	transition: background 0.3s ease;
}

.toggle-thumb {
	width: 1.25rem;
	height: 1.25rem;
	background: white;
	border-radius: var(--rounded-full);
	position: absolute;
	top: 0.125rem;
	left: 0.125rem;
	transition: transform 0.3s ease;
	display: flex;
	align-items: center;
	justify-content: center;
}

.theme-toggle[data-theme="dark"] .toggle-thumb {
	transform: translateX(1.5rem);
}

.toggle-icon {
	width: 0.75rem;
	height: 0.75rem;
	color: var(--accent-primary);
}

.sun-icon {
	display: block;
}

.moon-icon {
	display: none;
}

.theme-toggle[data-theme="dark"] .sun-icon {
	display: none;
}

.theme-toggle[data-theme="dark"] .moon-icon {
	display: block;
}

/* Main Content */
.consultation-main-content {
	max-width: 1200px;
	margin: 0 auto;
	padding: 2rem;
}

/* Welcome Section */
.consultation-welcome-section {
	text-align: center;
	margin-bottom: 3rem;
	padding: 2rem;
	background: var(--card-bg);
	border-radius: var(--rounded-xl);
	backdrop-filter: blur(10px);
	box-shadow: var(--shadow-lg);
	border: 1px solid var(--glass-border);
}

.consultation-logo-container {
	margin-bottom: 1.5rem;
}

.consultation-logo {
	height: 60px;
	width: auto;
	transition: all 0.3s ease;
}

.light-mode-logo {
	display: block;
}

.dark-mode-logo {
	display: none;
}

.ennu-consultation-dashboard[data-theme="dark"] .light-mode-logo {
	display: none;
}

.ennu-consultation-dashboard[data-theme="dark"] .dark-mode-logo {
	display: block;
}

.consultation-title {
	font-size: 2.5rem;
	font-weight: 700;
	line-height: 1.2;
	color: var(--text-color);
	text-shadow: 0 2px 4px var(--shadow-color);
	background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
	background-clip: text;
	transition: all 0.3s ease;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	margin: 1rem 0;
	opacity: 0;
	animation: fadeInUp 0.8s ease forwards;
	position: relative;
	display: inline-block;
}

.consultation-title::after {
	content: '';
	position: absolute;
	bottom: -0.5rem;
	left: 50%;
	transform: translateX(-50%);
	width: 3rem;
	height: 3px;
	background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
	border-radius: var(--rounded-full);
}

.consultation-subtitle {
	font-size: 1.125rem;
	color: var(--text-secondary);
	margin-bottom: 2rem;
	opacity: 0;
	animation: fadeInUp 0.8s ease 0.2s forwards;
}

/* Embed Container */
.consultation-embed-container {
	margin-top: 2rem;
	padding: 1.5rem;
	background: var(--card-bg);
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-md);
	border: 1px solid var(--border-color);
	opacity: 0;
	animation: fadeInUp 0.8s ease 0.4s forwards;
}

.consultation-placeholder {
	text-align: center;
	padding: 3rem 2rem;
	background: var(--card-bg);
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-md);
	border: 2px dashed var(--border-color);
	margin-top: 2rem;
}

.placeholder-icon {
	margin-bottom: 1rem;
	color: var(--text-muted);
}

.placeholder-icon svg {
	width: 64px;
	height: 64px;
}

/* Hero Card */
.consultation-hero-card {
	background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
	border-radius: var(--rounded-xl);
	padding: 2rem;
	margin-bottom: 2rem;
	box-shadow: var(--shadow-lg);
	opacity: 0;
	animation: fadeInUp 0.8s ease 0.6s forwards;
}

.hero-card-content {
	display: flex;
	align-items: center;
	gap: 2rem;
}

.consultation-icon {
	flex-shrink: 0;
}

.consultation-icon svg {
	width: 48px;
	height: 48px;
	color: white;
}

.hero-text {
	flex: 1;
}

.hero-title {
	font-size: 1.75rem;
	font-weight: 600;
	color: white;
	margin-bottom: 0.5rem;
}

.hero-description {
	color: rgba(255, 255, 255, 0.9);
	font-size: 1.125rem;
}

/* Benefits Section */
.consultation-benefits-card {
	background: var(--card-bg);
	border-radius: var(--rounded-xl);
	padding: 2rem;
	margin-bottom: 2rem;
	box-shadow: var(--shadow-lg);
	border: 1px solid var(--glass-border);
	backdrop-filter: blur(10px);
	opacity: 0;
	animation: fadeInUp 0.8s ease 0.8s forwards;
}

.section-title {
	font-size: 1.5rem;
	font-weight: 600;
	color: var(--text-color);
	margin-bottom: 1.5rem;
	text-align: center;
}

.benefits-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 1.5rem;
}

.benefit-item {
	display: flex;
	align-items: flex-start;
	gap: 1rem;
	padding: 1rem;
	background: var(--glass-bg);
	border-radius: var(--rounded-lg);
	border: 1px solid var(--glass-border);
	transition: all 0.3s ease;
}

.benefit-item:hover {
	transform: translateY(-2px);
	box-shadow: var(--shadow-md);
}

.benefit-icon {
	flex-shrink: 0;
	color: var(--accent-primary);
}

.benefit-text {
	color: var(--text-color);
	font-weight: 500;
}

/* Contact Section */
.consultation-contact-card {
	background: var(--card-bg);
	border-radius: var(--rounded-xl);
	padding: 2rem;
	box-shadow: var(--shadow-lg);
	border: 1px solid var(--glass-border);
	backdrop-filter: blur(10px);
	opacity: 0;
	animation: fadeInUp 0.8s ease 1s forwards;
}

.contact-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 1.5rem;
}

.contact-item {
	display: flex;
	align-items: center;
	gap: 1rem;
	padding: 1rem;
	background: var(--glass-bg);
	border-radius: var(--rounded-lg);
	border: 1px solid var(--glass-border);
	transition: all 0.3s ease;
}

.contact-item:hover {
	transform: translateY(-2px);
	box-shadow: var(--shadow-md);
}

.contact-icon {
	flex-shrink: 0;
	color: var(--accent-primary);
}

.contact-details {
	flex: 1;
}

.contact-label {
	font-size: 0.875rem;
	color: var(--text-muted);
	margin-bottom: 0.25rem;
}

.contact-value a {
	color: var(--text-color);
	text-decoration: none;
	font-weight: 500;
	transition: color 0.3s ease;
}

.contact-value a:hover {
	color: var(--accent-primary);
}

.extra-section {
	margin-top: 1.5rem;
	padding-top: 1.5rem;
	border-top: 1px solid var(--border-color);
	color: var(--text-secondary);
}

/* Animations */
@keyframes fadeInUp {
	from {
		opacity: 0;
		transform: translateY(30px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.ennu-animate-in {
	opacity: 0;
	animation: fadeInUp 0.8s ease forwards;
}

.ennu-animate-delay-1 {
	animation-delay: 0.2s;
}

.ennu-animate-delay-2 {
	animation-delay: 0.4s;
}

.ennu-animate-delay-3 {
	animation-delay: 0.6s;
}

/* Responsive Design */
@media (max-width: 768px) {
	.consultation-main-content {
		padding: 1rem;
	}
	
	.consultation-title {
		font-size: 2rem;
	}
	
	.hero-card-content {
		flex-direction: column;
		text-align: center;
		gap: 1rem;
	}
	
	.benefits-grid {
		grid-template-columns: 1fr;
	}
	
	.contact-grid {
		grid-template-columns: 1fr;
	}
	
	.theme-toggle-container {
		top: 1rem;
		right: 1rem;
	}
}

/* Button Styles */
.btn {
	display: inline-block;
	padding: 0.75rem 1.5rem;
	border-radius: var(--rounded-lg);
	text-decoration: none;
	font-weight: 500;
	transition: all 0.3s ease;
	border: none;
	cursor: pointer;
}

.btn-primary {
	background: var(--accent-primary);
	color: white;
}

.btn-primary:hover {
	background: var(--accent-secondary);
	transform: translateY(-1px);
	box-shadow: var(--shadow-md);
}

/* Error Styles */
.ennu-error {
	background: #fee2e2;
	border: 1px solid #fecaca;
	color: #dc2626;
	padding: 1rem;
	border-radius: var(--rounded-lg);
	margin: 1rem 0;
	text-align: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Theme toggle functionality
	const themeToggle = document.getElementById('consultation-theme-toggle');
	const dashboard = document.querySelector('.ennu-consultation-dashboard');
	
	if (themeToggle && dashboard) {
		themeToggle.addEventListener('click', function() {
			const currentTheme = dashboard.getAttribute('data-theme');
			const newTheme = currentTheme === 'light' ? 'dark' : 'light';
			
			dashboard.setAttribute('data-theme', newTheme);
			themeToggle.setAttribute('data-theme', newTheme);
			
			// Store theme preference
			localStorage.setItem('ennu-consultation-theme', newTheme);
		});
		
		// Load saved theme preference
		const savedTheme = localStorage.getItem('ennu-consultation-theme');
		if (savedTheme) {
			dashboard.setAttribute('data-theme', savedTheme);
			themeToggle.setAttribute('data-theme', savedTheme);
		}
	}
});
</script>