<?php
/**
 * Template for displaying transient state messages with proper styling
 * This template ensures that any transient state or debug messages are properly styled
 *
 * @version 64.4.13
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get data passed from the shortcode
$transient_message = $transient_message ?? 'Processing Results...';
$transient_type = $transient_type ?? 'loading'; // loading, success, error, warning
$transient_actions = $transient_actions ?? array();
$transient_progress = $transient_progress ?? 0;
$assessment_type = $assessment_type ?? '';
$user_id = $user_id ?? get_current_user_id();

// Get URLs
$dashboard_url = ennu_life()->get_shortcodes()->get_dashboard_url();
$retake_url = $assessment_type ? ennu_life()->get_shortcodes()->get_assessment_page_url( $assessment_type ) : $dashboard_url;

// Set up status messages based on type
$status_messages = array(
	'loading' => array(
		'title' => __( 'Processing Your Results', 'ennulifeassessments' ),
		'message' => __( 'We are analyzing your assessment data and generating your personalized health insights. This may take a few moments.', 'ennulifeassessments' ),
		'icon' => '⏳'
	),
	'success' => array(
		'title' => __( 'Results Ready', 'ennulifeassessments' ),
		'message' => __( 'Your assessment results have been processed successfully. Your personalized health insights are now available.', 'ennulifeassessments' ),
		'icon' => '✅'
	),
	'error' => array(
		'title' => __( 'Processing Error', 'ennulifeassessments' ),
		'message' => __( 'We encountered an issue while processing your results. Please try again or contact support if the problem persists.', 'ennulifeassessments' ),
		'icon' => '⚠️'
	),
	'warning' => array(
		'title' => __( 'Results Pending', 'ennulifeassessments' ),
		'message' => __( 'Your results are being processed. Please wait a moment or refresh the page to check the status.', 'ennulifeassessments' ),
		'icon' => '🔄'
	)
);

$current_status = $status_messages[ $transient_type ] ?? $status_messages['loading'];
?>

<div class="ennu-unified-container" data-theme="light">
	<div class="ennu-grid">
		<!-- Sidebar -->
		<aside class="ennu-sidebar">
			<!-- Logo -->
			<?php if ( function_exists( 'ennu_render_logo' ) ) : ?>
				<div class="ennu-logo-container" style="text-align: center; margin-bottom: 30px;">
					<?php
					ennu_render_logo(
						array(
							'color' => 'white',
							'size'  => 'medium',
							'link'  => home_url( '/' ),
							'alt'   => 'ENNU Life',
							'class' => '',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<!-- Status Indicator -->
			<div class="ennu-glass-card">
				<h3 class="ennu-section-title"><?php echo esc_html__( 'Status', 'ennulifeassessments' ); ?></h3>
				<div class="ennu-card-content">
					<div class="transient-state-status transient-state-<?php echo esc_attr( $transient_type ); ?>">
						<?php echo esc_html( ucfirst( $transient_type ) ); ?>
					</div>
					
					<?php if ( $transient_progress > 0 ) : ?>
						<div class="transient-state-progress">
							<div class="transient-state-progress-bar" style="width: <?php echo esc_attr( $transient_progress ); ?>%;"></div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</aside>

		<!-- Main Content -->
		<main class="ennu-main-content">
			<div class="transient-state-container">
				<!-- Status Icon -->
				<div class="transient-state-icon <?php echo esc_attr( $transient_type ); ?>">
					<?php echo esc_html( $current_status['icon'] ); ?>
				</div>

				<!-- Status Header -->
				<h1 class="transient-state-header">
					<?php echo esc_html( $current_status['title'] ); ?>
				</h1>

				<!-- Status Message -->
				<div class="transient-state-content">
					<p><?php echo esc_html( $current_status['message'] ); ?></p>
					
					<?php if ( ! empty( $transient_message ) && $transient_message !== $current_status['message'] ) : ?>
						<p class="transient-state-details">
							<?php echo esc_html( $transient_message ); ?>
						</p>
					<?php endif; ?>
				</div>

				<!-- Progress Information -->
				<?php if ( $transient_type === 'loading' ) : ?>
					<div class="transient-state-grid">
						<div class="transient-state-card">
							<h4><?php echo esc_html__( 'Data Analysis', 'ennulifeassessments' ); ?></h4>
							<p><?php echo esc_html__( 'Processing your assessment responses and calculating scores', 'ennulifeassessments' ); ?></p>
						</div>
						<div class="transient-state-card">
							<h4><?php echo esc_html__( 'Health Insights', 'ennulifeassessments' ); ?></h4>
							<p><?php echo esc_html__( 'Generating personalized recommendations and insights', 'ennulifeassessments' ); ?></p>
						</div>
						<div class="transient-state-card">
							<h4><?php echo esc_html__( 'Report Generation', 'ennulifeassessments' ); ?></h4>
							<p><?php echo esc_html__( 'Creating your comprehensive health report', 'ennulifeassessments' ); ?></p>
						</div>
					</div>
				<?php endif; ?>

				<!-- Action Buttons -->
				<div class="transient-state-actions">
					<?php if ( $transient_type === 'success' ) : ?>
						<a href="<?php echo esc_url( $dashboard_url ); ?>" class="btn">
							<?php echo esc_html__( 'View My Dashboard', 'ennulifeassessments' ); ?>
						</a>
						<a href="<?php echo esc_url( $retake_url ); ?>" class="btn btn-secondary">
							<?php echo esc_html__( 'Take Assessment Again', 'ennulifeassessments' ); ?>
						</a>
					<?php elseif ( $transient_type === 'error' ) : ?>
						<a href="<?php echo esc_url( $retake_url ); ?>" class="btn">
							<?php echo esc_html__( 'Try Again', 'ennulifeassessments' ); ?>
						</a>
						<a href="<?php echo esc_url( $dashboard_url ); ?>" class="btn btn-secondary">
							<?php echo esc_html__( 'Go to Dashboard', 'ennulifeassessments' ); ?>
						</a>
					<?php elseif ( $transient_type === 'warning' ) : ?>
						<button onclick="window.location.reload();" class="btn">
							<?php echo esc_html__( 'Refresh Page', 'ennulifeassessments' ); ?>
						</button>
						<a href="<?php echo esc_url( $dashboard_url ); ?>" class="btn btn-secondary">
							<?php echo esc_html__( 'Go to Dashboard', 'ennulifeassessments' ); ?>
						</a>
					<?php else : ?>
						<!-- Loading state - show refresh option -->
						<button onclick="window.location.reload();" class="btn btn-secondary">
							<?php echo esc_html__( 'Refresh Status', 'ennulifeassessments' ); ?>
						</button>
					<?php endif; ?>
				</div>

				<!-- Additional Information -->
				<?php if ( $transient_type === 'loading' ) : ?>
					<div class="transient-state-info" style="margin-top: var(--spacing-4); padding: var(--spacing-3); background: rgba(59, 130, 246, 0.1); border-radius: var(--rounded-md); border: 1px solid rgba(59, 130, 246, 0.2);">
						<p style="margin: 0; font-size: 0.875rem; color: #3b82f6;">
							<strong><?php echo esc_html__( 'Note:', 'ennulifeassessments' ); ?></strong>
							<?php echo esc_html__( 'If this page doesn\'t update automatically, please refresh to check your results status.', 'ennulifeassessments' ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<script>
// Auto-refresh for loading states
<?php if ( $transient_type === 'loading' ) : ?>
setTimeout(function() {
	window.location.reload();
}, 5000); // Refresh every 5 seconds
<?php endif; ?>

// Add some interactive feedback
document.addEventListener('DOMContentLoaded', function() {
	const progressBar = document.querySelector('.transient-state-progress-bar');
	if (progressBar && <?php echo $transient_progress; ?> === 0) {
		// Animate progress bar if no progress is set
		let progress = 0;
		const interval = setInterval(function() {
			progress += Math.random() * 15;
			if (progress >= 90) {
				progress = 90; // Don't go to 100% until actually complete
				clearInterval(interval);
			}
			progressBar.style.width = progress + '%';
		}, 1000);
	}
});
</script> 