<?php
/**
 * Dashboard Template - Peptide Therapy Assessment Results
 *
 * @package ENNU_Life_Assessments
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id = get_current_user_id();
if ( ! $user_id ) {
	return;
}

// Get assessment data
$assessment_data = get_user_meta( $user_id, 'ennu_assessment_responses_peptide-therapy', true );
$scores = get_user_meta( $user_id, 'ennu_peptide-therapy_scores', true );
$recommendations = get_user_meta( $user_id, 'ennu_peptide-therapy_recommendations', true );

if ( empty( $assessment_data ) ) {
	echo '<div class="ennu-no-assessment-data">';
	echo '<h3>No Peptide Therapy Assessment Data Available</h3>';
	echo '<p>Please complete the <a href="/peptide-therapy-assessment">Peptide Therapy Assessment</a> to see your personalized recommendations.</p>';
	echo '</div>';
	return;
}

// Define peptide descriptions
$peptide_info = array(
	'AOD-9604' => array(
		'name' => 'AOD-9604',
		'benefits' => 'Fat metabolism, weight loss, cartilage repair',
		'category' => 'Weight Management'
	),
	'BPC-157' => array(
		'name' => 'BPC-157',
		'benefits' => 'Tissue healing, gut health, injury recovery',
		'category' => 'Recovery & Healing'
	),
	'CJC-1295' => array(
		'name' => 'CJC-1295',
		'benefits' => 'Growth hormone release, muscle growth, fat loss',
		'category' => 'Hormonal Optimization'
	),
	'Gonadorelin' => array(
		'name' => 'Gonadorelin',
		'benefits' => 'Testosterone production, fertility, hormone balance',
		'category' => 'Hormonal Balance'
	),
	'HCG' => array(
		'name' => 'HCG',
		'benefits' => 'Testosterone support, weight loss, fertility',
		'category' => 'Hormonal Support'
	),
	'Ipamorelin' => array(
		'name' => 'Ipamorelin',
		'benefits' => 'Growth hormone release, sleep quality, recovery',
		'category' => 'Performance & Recovery'
	),
	'NAD+' => array(
		'name' => 'NAD+',
		'benefits' => 'Cellular energy, anti-aging, cognitive function',
		'category' => 'Anti-Aging & Vitality'
	),
	'PT-141' => array(
		'name' => 'PT-141',
		'benefits' => 'Sexual desire, arousal, intimacy enhancement',
		'category' => 'Sexual Health'
	),
	'Tesamorelin' => array(
		'name' => 'Tesamorelin',
		'benefits' => 'Abdominal fat reduction, IGF-1 increase, cognitive function',
		'category' => 'Metabolic Health'
	),
	'Tirzepatide' => array(
		'name' => 'Tirzepatide',
		'benefits' => 'Weight loss, blood sugar control, appetite regulation',
		'category' => 'Weight Management'
	)
);

// Get category scores
$category_scores = $scores['categories'] ?? array();
?>

<div class="ennu-peptide-therapy-dashboard">
	<div class="ennu-dashboard-header">
		<h2>Your Peptide Therapy Assessment Results</h2>
		<p class="assessment-date">Assessment Date: <?php echo date( 'F j, Y', strtotime( $assessment_data['timestamp'] ?? 'now' ) ); ?></p>
	</div>

	<!-- Overall Readiness Score -->
	<div class="ennu-readiness-score-section">
		<h3>Peptide Therapy Readiness Score</h3>
		<div class="score-display">
			<div class="overall-score">
				<span class="score-value"><?php echo number_format( $scores['overall'] ?? 0, 1 ); ?></span>
				<span class="score-label">/ 10</span>
			</div>
			<div class="score-interpretation">
				<?php
				$overall_score = $scores['overall'] ?? 0;
				if ( $overall_score >= 9.0 ) {
					echo '<span class="excellent">Excellent candidate for advanced peptide protocols</span>';
				} elseif ( $overall_score >= 7.5 ) {
					echo '<span class="good">Good candidate for targeted peptide therapy</span>';
				} elseif ( $overall_score >= 6.0 ) {
					echo '<span class="moderate">Moderate needs, focus on specific peptides</span>';
				} elseif ( $overall_score >= 4.5 ) {
					echo '<span class="fair">Address foundational health first</span>';
				} else {
					echo '<span class="needs-improvement">Medical consultation recommended before peptides</span>';
				}
				?>
			</div>
		</div>
	</div>

	<!-- Category Scores -->
	<div class="ennu-category-scores-section">
		<h3>Health Category Analysis</h3>
		<div class="category-scores-grid">
			<?php
			$categories = array(
				'Weight Management & Metabolism' => 'weight_management',
				'Recovery & Performance' => 'recovery_performance',
				'Hormonal Optimization' => 'hormonal_optimization',
				'Cognitive Enhancement' => 'cognitive_enhancement',
				'Anti-Aging & Longevity' => 'anti_aging',
				'Sexual Health & Vitality' => 'sexual_health',
				'Immune & Gut Health' => 'immune_gut'
			);
			
			foreach ( $categories as $display_name => $key ) :
				$score = $category_scores[ $key ] ?? 0;
				$percentage = ( $score / 10 ) * 100;
			?>
			<div class="category-score-item">
				<h4><?php echo esc_html( $display_name ); ?></h4>
				<div class="score-bar-container">
					<div class="score-bar" style="width: <?php echo $percentage; ?>%;">
						<span class="score-text"><?php echo number_format( $score, 1 ); ?></span>
					</div>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Recommended Peptides -->
	<div class="ennu-peptide-recommendations-section">
		<h3>Your Personalized Peptide Recommendations</h3>
		
		<?php if ( ! empty( $recommendations['primary'] ) ) : ?>
		<div class="recommendation-tier primary-tier">
			<h4>Primary Recommendations</h4>
			<p class="tier-description">Based on your highest scoring health categories</p>
			<div class="peptide-cards">
				<?php foreach ( $recommendations['primary'] as $peptide_key ) :
					if ( isset( $peptide_info[ $peptide_key ] ) ) :
						$peptide = $peptide_info[ $peptide_key ];
				?>
				<div class="peptide-card">
					<h5><?php echo esc_html( $peptide['name'] ); ?></h5>
					<p class="peptide-category"><?php echo esc_html( $peptide['category'] ); ?></p>
					<p class="peptide-benefits"><?php echo esc_html( $peptide['benefits'] ); ?></p>
				</div>
				<?php 
					endif;
				endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $recommendations['secondary'] ) ) : ?>
		<div class="recommendation-tier secondary-tier">
			<h4>Secondary Recommendations</h4>
			<p class="tier-description">Supporting peptides for comprehensive optimization</p>
			<div class="peptide-cards">
				<?php foreach ( $recommendations['secondary'] as $peptide_key ) :
					if ( isset( $peptide_info[ $peptide_key ] ) ) :
						$peptide = $peptide_info[ $peptide_key ];
				?>
				<div class="peptide-card">
					<h5><?php echo esc_html( $peptide['name'] ); ?></h5>
					<p class="peptide-category"><?php echo esc_html( $peptide['category'] ); ?></p>
					<p class="peptide-benefits"><?php echo esc_html( $peptide['benefits'] ); ?></p>
				</div>
				<?php 
					endif;
				endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $recommendations['optimization'] ) ) : ?>
		<div class="recommendation-tier optimization-tier">
			<h4>Advanced Optimization</h4>
			<p class="tier-description">For enhanced results and synergistic effects</p>
			<div class="peptide-cards">
				<?php foreach ( $recommendations['optimization'] as $peptide_key ) :
					if ( isset( $peptide_info[ $peptide_key ] ) ) :
						$peptide = $peptide_info[ $peptide_key ];
				?>
				<div class="peptide-card">
					<h5><?php echo esc_html( $peptide['name'] ); ?></h5>
					<p class="peptide-category"><?php echo esc_html( $peptide['category'] ); ?></p>
					<p class="peptide-benefits"><?php echo esc_html( $peptide['benefits'] ); ?></p>
				</div>
				<?php 
					endif;
				endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>

	<!-- Health Goals Alignment -->
	<?php 
	$health_goals = $assessment_data['responses']['primary_health_goals'] ?? array();
	if ( ! empty( $health_goals ) ) :
	?>
	<div class="ennu-health-goals-section">
		<h3>Your Health Goals</h3>
		<div class="health-goals-list">
			<?php
			$goal_labels = array(
				'weight_loss' => 'Lose weight and reduce body fat',
				'muscle_gain' => 'Build lean muscle mass',
				'energy_boost' => 'Increase energy levels',
				'cognitive_enhancement' => 'Improve mental clarity and focus',
				'anti_aging' => 'Anti-aging and longevity',
				'hormone_optimization' => 'Optimize hormone levels',
				'sexual_health' => 'Enhance sexual health and vitality',
				'recovery' => 'Improve recovery from exercise/injury',
				'sleep_quality' => 'Better sleep quality',
				'immune_support' => 'Strengthen immune system'
			);
			
			foreach ( $health_goals as $goal ) :
				if ( isset( $goal_labels[ $goal ] ) ) :
			?>
			<div class="health-goal-item">
				<span class="goal-icon">✓</span>
				<span class="goal-text"><?php echo esc_html( $goal_labels[ $goal ] ); ?></span>
			</div>
			<?php
				endif;
			endforeach;
			?>
		</div>
	</div>
	<?php endif; ?>

	<!-- Next Steps -->
	<div class="ennu-next-steps-section">
		<h3>Your Next Steps</h3>
		<ol class="next-steps-list">
			<li>Review your peptide recommendations with a healthcare provider</li>
			<li>Complete any recommended lab work to establish baselines</li>
			<li>Start with primary peptide recommendations</li>
			<li>Monitor progress and adjust protocols as needed</li>
			<li>Schedule a follow-up assessment in 3-6 months</li>
		</ol>
		
		<div class="action-buttons">
			<a href="/schedule-consultation" class="btn btn-primary">Schedule Consultation</a>
			<a href="/peptide-education" class="btn btn-secondary">Learn More About Peptides</a>
			<a href="/peptide-therapy-assessment" class="btn btn-outline">Retake Assessment</a>
		</div>
	</div>

	<!-- Important Notice -->
	<div class="ennu-notice-section">
		<h4>Important Notice</h4>
		<p>These recommendations are based on your assessment responses and are for educational purposes only. Peptide therapy should always be undertaken under the supervision of a qualified healthcare provider. Individual results may vary, and not all peptides may be suitable for everyone.</p>
	</div>
</div>

<style>
.ennu-peptide-therapy-dashboard {
	padding: 30px;
	background: #f8f9fa;
	border-radius: 10px;
	margin: 20px 0;
}

.ennu-dashboard-header {
	margin-bottom: 30px;
	text-align: center;
}

.ennu-dashboard-header h2 {
	color: #2c3e50;
	margin-bottom: 10px;
}

.assessment-date {
	color: #7f8c8d;
	font-size: 14px;
}

.ennu-readiness-score-section {
	background: white;
	padding: 30px;
	border-radius: 8px;
	margin-bottom: 30px;
	text-align: center;
}

.score-display {
	margin-top: 20px;
}

.overall-score {
	font-size: 48px;
	font-weight: bold;
	color: #3498db;
}

.score-value {
	font-size: 72px;
}

.score-label {
	font-size: 24px;
	color: #95a5a6;
}

.score-interpretation {
	margin-top: 15px;
	font-size: 18px;
}

.score-interpretation .excellent { color: #27ae60; }
.score-interpretation .good { color: #2ecc71; }
.score-interpretation .moderate { color: #f39c12; }
.score-interpretation .fair { color: #e67e22; }
.score-interpretation .needs-improvement { color: #e74c3c; }

.ennu-category-scores-section {
	background: white;
	padding: 30px;
	border-radius: 8px;
	margin-bottom: 30px;
}

.category-scores-grid {
	display: grid;
	gap: 20px;
	margin-top: 20px;
}

.category-score-item h4 {
	margin-bottom: 10px;
	color: #34495e;
	font-size: 16px;
}

.score-bar-container {
	background: #ecf0f1;
	height: 30px;
	border-radius: 15px;
	overflow: hidden;
	position: relative;
}

.score-bar {
	background: linear-gradient(90deg, #3498db, #2ecc71);
	height: 100%;
	display: flex;
	align-items: center;
	justify-content: flex-end;
	padding-right: 10px;
	transition: width 0.5s ease;
}

.score-text {
	color: white;
	font-weight: bold;
	font-size: 14px;
}

.ennu-peptide-recommendations-section {
	background: white;
	padding: 30px;
	border-radius: 8px;
	margin-bottom: 30px;
}

.recommendation-tier {
	margin-bottom: 30px;
}

.recommendation-tier h4 {
	color: #2c3e50;
	margin-bottom: 5px;
}

.tier-description {
	color: #7f8c8d;
	font-size: 14px;
	margin-bottom: 15px;
}

.peptide-cards {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
}

.peptide-card {
	background: #f8f9fa;
	padding: 20px;
	border-radius: 8px;
	border-left: 4px solid #3498db;
}

.peptide-card h5 {
	color: #2c3e50;
	margin-bottom: 10px;
	font-size: 18px;
}

.peptide-category {
	color: #3498db;
	font-size: 12px;
	text-transform: uppercase;
	margin-bottom: 8px;
}

.peptide-benefits {
	color: #7f8c8d;
	font-size: 14px;
	line-height: 1.4;
}

.ennu-health-goals-section {
	background: white;
	padding: 30px;
	border-radius: 8px;
	margin-bottom: 30px;
}

.health-goals-list {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 15px;
	margin-top: 20px;
}

.health-goal-item {
	display: flex;
	align-items: center;
	padding: 10px;
	background: #f8f9fa;
	border-radius: 5px;
}

.goal-icon {
	color: #27ae60;
	font-size: 18px;
	margin-right: 10px;
}

.goal-text {
	color: #34495e;
}

.ennu-next-steps-section {
	background: white;
	padding: 30px;
	border-radius: 8px;
	margin-bottom: 30px;
}

.next-steps-list {
	margin: 20px 0 30px 20px;
	line-height: 2;
	color: #34495e;
}

.action-buttons {
	display: flex;
	gap: 15px;
	flex-wrap: wrap;
}

.btn {
	padding: 12px 24px;
	border-radius: 5px;
	text-decoration: none;
	font-weight: bold;
	transition: all 0.3s;
	display: inline-block;
}

.btn-primary {
	background: #3498db;
	color: white;
}

.btn-primary:hover {
	background: #2980b9;
}

.btn-secondary {
	background: #2ecc71;
	color: white;
}

.btn-secondary:hover {
	background: #27ae60;
}

.btn-outline {
	background: transparent;
	color: #3498db;
	border: 2px solid #3498db;
}

.btn-outline:hover {
	background: #3498db;
	color: white;
}

.ennu-notice-section {
	background: #fef5e7;
	padding: 20px;
	border-radius: 8px;
	border-left: 4px solid #f39c12;
}

.ennu-notice-section h4 {
	color: #e67e22;
	margin-bottom: 10px;
}

.ennu-notice-section p {
	color: #7f8c8d;
	font-size: 14px;
	line-height: 1.6;
}

.ennu-no-assessment-data {
	background: white;
	padding: 40px;
	text-align: center;
	border-radius: 8px;
}

.ennu-no-assessment-data h3 {
	color: #34495e;
	margin-bottom: 15px;
}

.ennu-no-assessment-data p {
	color: #7f8c8d;
}

.ennu-no-assessment-data a {
	color: #3498db;
	text-decoration: none;
}

.ennu-no-assessment-data a:hover {
	text-decoration: underline;
}
</style>