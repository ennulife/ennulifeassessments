<?php
/**
 * Template: Executive Health Summary (Analytics Dashboard)
 *
 * A self-contained, jaw-dropping dashboard for viewing user analytics.
 * All styles and scripts are included to prevent conflicts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Data is passed from the rendering function in class-enhanced-admin.php
// $current_user, $ennu_life_score, $age, $gender, $dob, $average_pillar_scores
?>

<style>
/* --- Master Styles --- */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

:root {
	--bg-dark: #1a1d2e;
	--card-dark: #242847;
	--primary-glow: #6a89cc;
	--text-primary: #ffffff;
	--text-secondary: #a9b3d0;
	--border-color: #3b4278;
}

#wpbody-content {
	background-color: var(--bg-dark);
}

.ennu-analytics-dashboard {
	font-family: 'Inter', sans-serif;
	color: var(--text-primary);
	padding-top: 20px;
}
.ennu-analytics-dashboard h1 {
	font-size: 28px;
	font-weight: 700;
	margin-bottom: 30px;
	color: var(--text-primary);
}

/* --- Hero Section --- */
.analytics-hero {
	display: flex;
	gap: 30px;
	align-items: stretch;
	margin-bottom: 30px;
}

.hero-card {
	background: var(--card-dark);
	padding: 30px;
	border-radius: 16px;
	border: 1px solid var(--border-color);
}

.life-score-card {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-align: center;
}

.life-score-card .score-value {
	font-size: 72px;
	font-weight: 800;
	line-height: 1;
	color: var(--primary-glow);
	text-shadow: 0 0 15px rgba(106, 137, 204, 0.5);
}

.life-score-card .score-label {
	font-size: 14px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-top: 10px;
	color: var(--text-secondary);
}

.profile-summary-card {
	flex: 2;
}

.profile-summary-card h2 {
	font-size: 22px;
	font-weight: 600;
	color: var(--text-primary);
	margin: 0 0 20px 0;
}

.profile-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 20px;
}

.profile-item {
	background: rgba(0,0,0,0.2);
	padding: 15px;
	border-radius: 10px;
}

.profile-item .label {
	font-size: 13px;
	color: var(--text-secondary);
	margin-bottom: 5px;
}

.profile-item .value {
	font-size: 18px;
	font-weight: 600;
	color: var(--text-primary);
}

/* --- Pillar Gauges --- */
.pillar-gauges {
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: 30px;
}

.pillar-gauge-card {
	background: var(--card-dark);
	padding: 25px;
	border-radius: 16px;
	border: 1px solid var(--border-color);
	text-align: center;
}

.pillar-gauge-card .chart-container {
	position: relative;
	height: 150px;
	margin-bottom: 15px;
}

.pillar-gauge-card h3 {
	font-size: 16px;
	font-weight: 600;
	color: var(--text-primary);
	margin: 0;
}
</style>

<div class="wrap ennu-analytics-dashboard ennu-logo-pattern-bg">
	<?php
	if ( function_exists( 'ennu_render_logo' ) ) {
		echo '<div class="ennu-logo-container">';
		ennu_render_logo([
			'color' => 'black',
			'size' => 'medium',
			'link' => admin_url('admin.php?page=ennu-life'),
			'alt' => 'ENNU Life',
			'class' => ''
		]);
		echo '</div>';
	}
	?>
	<h1>Executive Health Summary</h1>

	<div class="analytics-hero">
		<div class="hero-card life-score-card">
			<div class="score-value"><?php echo esc_html( number_format( $ennu_life_score, 1 ) ); ?></div>
			<div class="score-label">ENNU Life Score</div>
		</div>
		<div class="hero-card profile-summary-card">
			<h2><?php echo esc_html( $current_user->display_name ); ?></h2>
			<div class="profile-grid">
				<div class="profile-item">
					<div class="label">Age</div>
					<div class="value"><?php echo esc_html( $age ); ?></div>
				</div>
				<div class="profile-item">
					<div class="label">Gender</div>
					<div class="value"><?php echo esc_html( $gender ); ?></div>
				</div>
				<div class="profile-item">
					<div class="label">Date of Birth</div>
					<div class="value"><?php echo esc_html( $dob ); ?></div>
				</div>
			</div>
		</div>
	</div>

	<div class="pillar-gauges">
		<?php foreach ( $average_pillar_scores as $pillar => $score ) : ?>
			<div class="pillar-gauge-card">
				<div class="chart-container">
					<canvas id="pillar-chart-<?php echo esc_attr( strtolower( $pillar ) ); ?>"></canvas>
				</div>
				<h3><?php echo esc_html( $pillar ); ?></h3>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	const pillarScores = <?php echo json_encode( $average_pillar_scores ); ?>;
	const pillarColors = {
		'Mind': '#8e44ad',
		'Body': '#2980b9',
		'Lifestyle': '#27ae60',
		'Aesthetics': '#f39c12'
	};

	Object.keys(pillarScores).forEach(pillar => {
		const score = pillarScores[pillar];
		const canvas = document.getElementById(`pillar-chart-${pillar.toLowerCase()}`);
		if (!canvas) return;

		new Chart(canvas, {
			type: 'doughnut',
			data: {
				datasets: [{
					data: [score, 10 - score],
					backgroundColor: [pillarColors[pillar] || '#7f8c8d', '#3a3f6c'],
					borderColor: [pillarColors[pillar] || '#7f8c8d', '#3a3f6c'],
					borderWidth: 1,
					circumference: 180,
					rotation: 270,
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				cutout: '70%',
				plugins: {
					tooltip: { enabled: false }
				}
			}
		});

		const scoreText = document.createElement('div');
		scoreText.style.position = 'absolute';
		scoreText.style.top = '60%';
		scoreText.style.left = '50%';
		scoreText.style.transform = 'translate(-50%, -50%)';
		scoreText.style.fontSize = '36px';
		scoreText.style.fontWeight = '800';
		scoreText.style.color = pillarColors[pillar] || '#7f8c8d';
		scoreText.innerHTML = score.toFixed(1);
		canvas.parentNode.appendChild(scoreText);
	});
});
</script> 
