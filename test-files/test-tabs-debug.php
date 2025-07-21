<?php
/**
 * Test file to debug tab functionality
 */

// Include WordPress
require_once '../../../wp-load.php';

// Check if user is logged in
if ( ! is_user_logged_in() ) {
	wp_die( 'Please log in to view this page.' );
}

$current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Tab Debug Test</title>
	<style>
		.my-story-tab-nav {
			background: #f5f5f5;
			padding: 1rem;
			border-radius: 8px;
			margin-bottom: 1rem;
		}
		
		.my-story-tab-nav ul {
			list-style: none;
			padding: 0;
			margin: 0;
			display: flex;
			gap: 1rem;
		}
		
		.my-story-tab-nav a {
			padding: 0.5rem 1rem;
			text-decoration: none;
			color: #333;
			border-radius: 4px;
			transition: background-color 0.3s;
		}
		
		.my-story-tab-nav a:hover {
			background-color: #e0e0e0;
		}
		
		.my-story-tab-nav a.my-story-tab-active {
			background-color: #007cba;
			color: white;
		}
		
		.my-story-tab-content {
			display: none;
			padding: 2rem;
			background: white;
			border: 1px solid #ddd;
			border-radius: 8px;
			min-height: 200px;
		}
		
		.my-story-tab-content.my-story-tab-active {
			display: block;
		}
		
		.debug-info {
			background: #f0f0f0;
			padding: 1rem;
			margin: 1rem 0;
			border-radius: 4px;
			font-family: monospace;
		}
	</style>
</head>
<body>
	<h1>Tab Debug Test</h1>
	
	<div class="debug-info">
		<strong>Debug Info:</strong><br>
		User: <?php echo esc_html( $current_user->display_name ); ?><br>
		Time: <?php echo date( 'Y-m-d H:i:s' ); ?><br>
		JavaScript Status: <span id="js-status">Checking...</span>
	</div>
	
	<div class="my-story-tabs">
		<nav class="my-story-tab-nav">
			<ul>
				<li><a href="#tab-my-assessments" class="my-story-tab-active">My Assessments</a></li>
				<li><a href="#tab-my-symptoms">My Symptoms</a></li>
				<li><a href="#tab-my-biomarkers">My Biomarkers</a></li>
				<li><a href="#tab-my-new-life">My New Life</a></li>
			</ul>
		</nav>
		
		<!-- Tab 1: My Assessments -->
		<div id="tab-my-assessments" class="my-story-tab-content my-story-tab-active">
			<h3>My Assessments</h3>
			<p>This is the assessments tab content.</p>
		</div>
		
		<!-- Tab 2: My Symptoms -->
		<div id="tab-my-symptoms" class="my-story-tab-content">
			<h3>My Symptoms</h3>
			<p>This is the symptoms tab content.</p>
		</div>
		
		<!-- Tab 3: My Biomarkers -->
		<div id="tab-my-biomarkers" class="my-story-tab-content">
			<h3>My Biomarkers</h3>
			<p>This is the biomarkers tab content.</p>
		</div>
		
		<!-- Tab 4: My New Life -->
		<div id="tab-my-new-life" class="my-story-tab-content">
			<h3>My New Life</h3>
			<p>This is the new life tab content.</p>
		</div>
	</div>
	
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			console.log('DOM loaded, initializing tabs...');
			
			// Update debug status
			document.getElementById('js-status').textContent = 'JavaScript loaded';
			
			// Tab switching functionality
			const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
			const tabContents = document.querySelectorAll('.my-story-tab-content');
			
			console.log('Found tab links:', tabLinks.length);
			console.log('Found tab contents:', tabContents.length);
			
			tabLinks.forEach(link => {
				link.addEventListener('click', function(e) {
					e.preventDefault();
					console.log('Tab clicked:', this.getAttribute('href'));
					
					// Remove active class from all tabs and contents
					tabLinks.forEach(l => l.classList.remove('my-story-tab-active'));
					tabContents.forEach(c => c.classList.remove('my-story-tab-active'));
					
					// Add active class to clicked tab
					this.classList.add('my-story-tab-active');
					
					// Show corresponding content
					const targetId = this.getAttribute('href').substring(1);
					const targetContent = document.getElementById(targetId);
					if (targetContent) {
						targetContent.classList.add('my-story-tab-active');
						console.log('Activated tab:', targetId);
					} else {
						console.error('Target content not found:', targetId);
					}
				});
			});
			
			// Show first tab by default
			if (tabLinks.length > 0) {
				console.log('Activating first tab by default');
				tabLinks[0].click();
			}
		});
	</script>
</body>
</html> 
