<?php
/**
 * Advanced Analytics Dashboard Template
 * Comprehensive admin analytics and reporting interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$overview    = $analytics_data['overview'] ?? array();
$assessments = $analytics_data['assessments'] ?? array();
$users       = $analytics_data['users'] ?? array();
$scores      = $analytics_data['scores'] ?? array();
$conversion  = $analytics_data['conversion'] ?? array();
$performance = $analytics_data['performance'] ?? array();
$trends      = $analytics_data['trends'] ?? array();
?>

<div class="wrap ennu-analytics-dashboard">
	<h1><?php esc_html_e( 'ENNU Life Analytics Dashboard', 'ennu-life-assessments' ); ?></h1>
	
	<!-- Overview Cards -->
	<div class="analytics-overview-grid">
		<div class="analytics-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Total Users', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<div class="metric-value"><?php echo esc_html( number_format( $overview['total_users'] ?? 0 ) ); ?></div>
				<div class="metric-label"><?php esc_html_e( 'Registered Users', 'ennu-life-assessments' ); ?></div>
			</div>
		</div>
		
		<div class="analytics-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Active Users', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<div class="metric-value"><?php echo esc_html( number_format( $overview['active_users'] ?? 0 ) ); ?></div>
				<div class="metric-label"><?php echo esc_html( ( $overview['engagement_rate'] ?? 0 ) . '%' ); ?> <?php esc_html_e( 'engagement', 'ennu-life-assessments' ); ?></div>
			</div>
		</div>
		
		<div class="analytics-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Total Assessments', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<div class="metric-value"><?php echo esc_html( number_format( $overview['total_assessments'] ?? 0 ) ); ?></div>
				<div class="metric-label"><?php esc_html_e( 'Completed', 'ennu-life-assessments' ); ?></div>
			</div>
		</div>
		
		<div class="analytics-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Average Life Score', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<div class="metric-value"><?php echo esc_html( number_format( $overview['avg_life_score'] ?? 0, 1 ) ); ?></div>
				<div class="metric-label"><?php esc_html_e( 'Out of 10', 'ennu-life-assessments' ); ?></div>
			</div>
		</div>
	</div>
	
	<!-- Charts Section -->
	<div class="analytics-charts-grid">
		<!-- Assessment Completions Chart -->
		<div class="analytics-card chart-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Assessment Completions', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<canvas id="assessmentCompletionsChart"></canvas>
			</div>
		</div>
		
		<!-- Pillar Scores Chart -->
		<div class="analytics-card chart-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Average Pillar Scores', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<canvas id="pillarScoresChart"></canvas>
			</div>
		</div>
		
		<!-- User Registration Trends -->
		<div class="analytics-card chart-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'User Registration Trends', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<canvas id="registrationTrendsChart"></canvas>
			</div>
		</div>
		
		<!-- Conversion Metrics -->
		<div class="analytics-card chart-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Conversion Metrics', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<div class="conversion-metrics">
					<div class="conversion-item">
						<div class="conversion-label"><?php esc_html_e( 'Consultation Bookings', 'ennu-life-assessments' ); ?></div>
						<div class="conversion-value"><?php echo esc_html( $conversion['consultation_bookings'] ?? 0 ); ?></div>
						<div class="conversion-rate"><?php echo esc_html( ( $conversion['consultation_conversion_rate'] ?? 0 ) . '%' ); ?></div>
					</div>
					<div class="conversion-item">
						<div class="conversion-label"><?php esc_html_e( 'Biomarker Purchases', 'ennu-life-assessments' ); ?></div>
						<div class="conversion-value"><?php echo esc_html( $conversion['biomarker_purchases'] ?? 0 ); ?></div>
						<div class="conversion-rate"><?php echo esc_html( ( $conversion['biomarker_conversion_rate'] ?? 0 ) . '%' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Performance Metrics -->
	<div class="analytics-card">
		<div class="card-header">
			<h3><?php esc_html_e( 'Performance Metrics', 'ennu-life-assessments' ); ?></h3>
		</div>
		<div class="card-content">
			<div class="performance-grid">
				<div class="performance-item">
					<div class="performance-label"><?php esc_html_e( 'Average Load Time', 'ennu-life-assessments' ); ?></div>
					<div class="performance-value"><?php echo esc_html( number_format( $performance['average_load_time'] ?? 0, 2 ) ); ?>s</div>
				</div>
				<div class="performance-item">
					<div class="performance-label"><?php esc_html_e( 'Database Queries', 'ennu-life-assessments' ); ?></div>
					<div class="performance-value"><?php echo esc_html( number_format( $performance['database_queries'] ?? 0 ) ); ?></div>
				</div>
				<div class="performance-item">
					<div class="performance-label"><?php esc_html_e( 'Memory Usage', 'ennu-life-assessments' ); ?></div>
					<div class="performance-value"><?php echo esc_html( number_format( $performance['memory_usage'] ?? 0 / 1024 / 1024, 1 ) ); ?>MB</div>
				</div>
				<div class="performance-item">
					<div class="performance-label"><?php esc_html_e( 'Cache Hit Rate', 'ennu-life-assessments' ); ?></div>
					<div class="performance-value"><?php echo esc_html( number_format( $performance['cache_hit_rate'] ?? 0, 1 ) ); ?>%</div>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Detailed Tables -->
	<div class="analytics-tables-grid">
		<!-- Assessment Details -->
		<div class="analytics-card table-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'Assessment Details', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Assessment Type', 'ennu-life-assessments' ); ?></th>
							<th><?php esc_html_e( 'Completions', 'ennu-life-assessments' ); ?></th>
							<th><?php esc_html_e( 'Average Score', 'ennu-life-assessments' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $assessments['completions'] ) ) : ?>
							<?php foreach ( $assessments['completions'] as $type => $completions ) : ?>
								<tr>
									<td><?php echo esc_html( ucwords( str_replace( '_', ' ', $type ) ) ); ?></td>
									<td><?php echo esc_html( number_format( $completions ) ); ?></td>
									<td><?php echo esc_html( number_format( $assessments['average_scores'][ $type ] ?? 0, 1 ) ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="3"><?php esc_html_e( 'No assessment data available', 'ennu-life-assessments' ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- User Demographics -->
		<div class="analytics-card table-card">
			<div class="card-header">
				<h3><?php esc_html_e( 'User Demographics', 'ennu-life-assessments' ); ?></h3>
			</div>
			<div class="card-content">
				<div class="demographics-grid">
					<div class="demographic-section">
						<h4><?php esc_html_e( 'Gender Distribution', 'ennu-life-assessments' ); ?></h4>
						<?php if ( ! empty( $users['gender_distribution'] ) ) : ?>
							<?php foreach ( $users['gender_distribution'] as $gender_data ) : ?>
								<div class="demographic-item">
									<span class="demographic-label"><?php echo esc_html( ucfirst( $gender_data->gender ) ); ?></span>
									<span class="demographic-value"><?php echo esc_html( number_format( $gender_data->count ) ); ?></span>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					
					<div class="demographic-section">
						<h4><?php esc_html_e( 'Age Groups', 'ennu-life-assessments' ); ?></h4>
						<?php if ( ! empty( $users['age_groups'] ) ) : ?>
							<?php foreach ( $users['age_groups'] as $age_group => $count ) : ?>
								<div class="demographic-item">
									<span class="demographic-label"><?php echo esc_html( $age_group ); ?></span>
									<span class="demographic-value"><?php echo esc_html( number_format( $count ) ); ?></span>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	const assessmentData = <?php echo json_encode( $assessments['completions'] ?? array() ); ?>;
	const pillarData = <?php echo json_encode( $scores['pillar_averages'] ?? array() ); ?>;
	const registrationData = <?php echo json_encode( $users['registrations'] ?? array() ); ?>;
	
	if (document.getElementById('assessmentCompletionsChart')) {
		new Chart(document.getElementById('assessmentCompletionsChart'), {
			type: 'bar',
			data: {
				labels: Object.keys(assessmentData).map(key => key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
				datasets: [{
					label: '<?php esc_html_e( 'Completions', 'ennu-life-assessments' ); ?>',
					data: Object.values(assessmentData),
					backgroundColor: 'rgba(52, 152, 219, 0.8)',
					borderColor: 'rgba(52, 152, 219, 1)',
					borderWidth: 1
				}]
			},
			options: {
				responsive: true,
				scales: {
					y: {
						beginAtZero: true
					}
				}
			}
		});
	}
	
	if (document.getElementById('pillarScoresChart')) {
		new Chart(document.getElementById('pillarScoresChart'), {
			type: 'radar',
			data: {
				labels: Object.keys(pillarData).map(key => key.charAt(0).toUpperCase() + key.slice(1)),
				datasets: [{
					label: '<?php esc_html_e( 'Average Score', 'ennu-life-assessments' ); ?>',
					data: Object.values(pillarData),
					backgroundColor: 'rgba(46, 204, 113, 0.2)',
					borderColor: 'rgba(46, 204, 113, 1)',
					borderWidth: 2
				}]
			},
			options: {
				responsive: true,
				scales: {
					r: {
						beginAtZero: true,
						max: 10
					}
				}
			}
		});
	}
	
	if (document.getElementById('registrationTrendsChart') && registrationData.length > 0) {
		new Chart(document.getElementById('registrationTrendsChart'), {
			type: 'line',
			data: {
				labels: registrationData.map(item => item.date),
				datasets: [{
					label: '<?php esc_html_e( 'New Registrations', 'ennu-life-assessments' ); ?>',
					data: registrationData.map(item => item.count),
					backgroundColor: 'rgba(155, 89, 182, 0.2)',
					borderColor: 'rgba(155, 89, 182, 1)',
					borderWidth: 2,
					fill: true
				}]
			},
			options: {
				responsive: true,
				scales: {
					y: {
						beginAtZero: true
					}
				}
			}
		});
	}
});
</script>

<style>
.ennu-analytics-dashboard {
	max-width: 1400px;
}

.analytics-overview-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.analytics-charts-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.analytics-tables-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}

.analytics-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 8px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
	padding: 15px 20px;
	border-bottom: 1px solid #eee;
	background: #f9f9f9;
}

.card-header h3 {
	margin: 0;
	font-size: 16px;
	font-weight: 600;
}

.card-content {
	padding: 20px;
}

.metric-value {
	font-size: 2.5rem;
	font-weight: 700;
	color: #2c3e50;
	line-height: 1;
}

.metric-label {
	font-size: 0.9rem;
	color: #7f8c8d;
	margin-top: 5px;
}

.chart-card .card-content {
	height: 300px;
	position: relative;
}

.conversion-metrics {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
}

.conversion-item {
	text-align: center;
	padding: 20px;
	background: #f8f9fa;
	border-radius: 6px;
}

.conversion-label {
	font-size: 0.9rem;
	color: #6c757d;
	margin-bottom: 10px;
}

.conversion-value {
	font-size: 2rem;
	font-weight: 700;
	color: #2c3e50;
}

.conversion-rate {
	font-size: 1.1rem;
	color: #28a745;
	font-weight: 600;
}

.performance-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
	gap: 20px;
}

.performance-item {
	text-align: center;
	padding: 15px;
	background: #f8f9fa;
	border-radius: 6px;
}

.performance-label {
	font-size: 0.9rem;
	color: #6c757d;
	margin-bottom: 8px;
}

.performance-value {
	font-size: 1.5rem;
	font-weight: 700;
	color: #2c3e50;
}

.demographics-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 30px;
}

.demographic-section h4 {
	margin: 0 0 15px 0;
	font-size: 1.1rem;
	color: #2c3e50;
}

.demographic-item {
	display: flex;
	justify-content: space-between;
	padding: 8px 0;
	border-bottom: 1px solid #eee;
}

.demographic-label {
	font-weight: 500;
}

.demographic-value {
	font-weight: 600;
	color: #3498db;
}

@media (max-width: 768px) {
	.analytics-overview-grid,
	.analytics-charts-grid,
	.analytics-tables-grid {
		grid-template-columns: 1fr;
	}
	
	.chart-card .card-content {
		height: 250px;
	}
	
	.conversion-metrics,
	.performance-grid,
	.demographics-grid {
		grid-template-columns: 1fr;
	}
}
</style>
