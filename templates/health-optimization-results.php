<?php
/**
 * Template for displaying health optimization results
 *
 * @version 62.1.57
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$report_data  = $report_data ?? array();
$symptom_data = $symptom_data ?? array();
?>

<div class="ennu-unified-container">
	<div class="starfield"></div>
	
	<div class="ennu-single-column">
		<!-- Header -->
		<div class="ennu-animate-in">
			<h1 class="ennu-title">Health Optimization Report</h1>
			<p class="ennu-subtitle">
				Your comprehensive health analysis and personalized optimization recommendations.
				</p>
			</div>

		<!-- Health Map Overview -->
		<div class="ennu-card ennu-animate-in ennu-animate-delay-1">
			<h2 class="ennu-section-title">Health Map Overview</h2>
			<div class="ennu-health-map-grid">
				<?php if ( isset( $report_data['vectors'] ) && is_array( $report_data['vectors'] ) ) : ?>
					<?php foreach ( $report_data['vectors'] as $index => $vector ) : ?>
						<div class="ennu-vector-card" data-color-index="<?php echo esc_attr( $index ); ?>">
							<div class="ennu-card-header">
								<h3 class="ennu-card-title"><?php echo esc_html( $vector['name'] ); ?></h3>
								<div class="ennu-vector-score"><?php echo esc_html( $vector['score'] ?? 'N/A' ); ?></div>
							</div>
							<div class="ennu-card-content">
								<p><?php echo esc_html( $vector['description'] ?? '' ); ?></p>
								
								<?php if ( isset( $vector['symptoms'] ) && ! empty( $vector['symptoms'] ) ) : ?>
									<div class="ennu-symptom-section">
										<h4>Symptoms Identified</h4>
										<ul class="ennu-symptom-list">
											<?php foreach ( $vector['symptoms'] as $symptom ) : ?>
												<li class="ennu-symptom-item"><?php echo esc_html( $symptom ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
								<?php endif; ?>
								
								<?php if ( isset( $vector['biomarkers'] ) && ! empty( $vector['biomarkers'] ) ) : ?>
									<div class="ennu-biomarker-section">
										<h4>Recommended Biomarkers</h4>
										<ul class="ennu-biomarker-list">
											<?php foreach ( $vector['biomarkers'] as $biomarker ) : ?>
												<li class="ennu-biomarker-item"><?php echo esc_html( $biomarker ); ?></li>
											<?php endforeach; ?>
										</ul>
				</div>
			<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- Action Plan -->
		<?php if ( isset( $report_data['action_plan'] ) && ! empty( $report_data['action_plan'] ) ) : ?>
			<div class="ennu-card ennu-animate-in ennu-animate-delay-2">
				<h2 class="ennu-section-title">Your Action Plan</h2>
				<div class="ennu-list">
					<?php foreach ( $report_data['action_plan'] as $action ) : ?>
						<div class="ennu-list-item">
							<div class="ennu-list-item-content">
								<div class="ennu-list-item-description">
									<span style="color: var(--accent-primary); margin-right: 8px;">→</span>
									<?php echo esc_html( $action ); ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Next Steps -->
		<div class="ennu-card ennu-animate-in ennu-animate-delay-3">
			<h2 class="ennu-section-title">Next Steps</h2>
			<div class="ennu-card-content">
				<p>Based on your health optimization assessment, we recommend scheduling a consultation with our health specialists to discuss your personalized treatment plan and biomarker testing options.</p>
			</div>
			<div class="ennu-btn-group">
				<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'book-health-optimization-consultation' ) ); ?>" class="ennu-btn ennu-btn-primary">
					Book Health Optimization Consultation
				</a>
				<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'dashboard' ) ); ?>" class="ennu-btn ennu-btn-secondary">
					View Dashboard
				</a>
			</div>
		</div>
	</div>
</div> 

<style>
/* Additional specific styles for health optimization results */
.ennu-health-map-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.ennu-vector-card {
	background: var(--card-bg);
	border: 1px solid var(--border-color);
	border-radius: 12px;
	padding: 20px;
	transition: all 0.3s ease;
}

.ennu-vector-card:hover {
	transform: translateY(-2px);
	box-shadow: var(--shadow-md);
}

.ennu-vector-card[data-color-index="0"] { border-left: 4px solid #34d399; }
.ennu-vector-card[data-color-index="1"] { border-left: 4px solid #60a5fa; }
.ennu-vector-card[data-color-index="2"] { border-left: 4px solid #f472b6; }
.ennu-vector-card[data-color-index="3"] { border-left: 4px solid #facc15; }
.ennu-vector-card[data-color-index="4"] { border-left: 4px solid #a78bfa; }
.ennu-vector-card[data-color-index="5"] { border-left: 4px solid #fb923c; }

.ennu-vector-score {
	font-weight: 700;
	color: var(--accent-primary);
	font-size: 1.1rem;
}

.ennu-symptom-section,
.ennu-biomarker-section {
	margin-top: 15px;
}

.ennu-symptom-section h4,
.ennu-biomarker-section h4 {
	font-size: 0.9rem;
	font-weight: 600;
	color: var(--text-dark);
	margin-bottom: 8px;
}

.ennu-symptom-list,
.ennu-biomarker-list {
	list-style: none;
	padding: 0;
	margin: 0;
}

.ennu-symptom-item,
.ennu-biomarker-item {
	font-size: 0.85rem;
	color: var(--text-light);
	margin-bottom: 4px;
	padding-left: 15px;
	position: relative;
}

.ennu-symptom-item::before,
.ennu-biomarker-item::before {
	content: '•';
	position: absolute;
	left: 0;
	color: var(--accent-primary);
}

@media (max-width: 768px) {
	.ennu-health-map-grid {
		grid-template-columns: 1fr;
	}
}
</style> 
