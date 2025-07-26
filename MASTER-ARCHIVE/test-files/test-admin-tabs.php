<?php
/**
 * Test Admin Tabs Functionality
 * 
 * @package ENNU_Life
 * @version 64.2.3
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only run for admins
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>ENNU Admin Tabs Test</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 20px; }
		.test-container { max-width: 800px; margin: 0 auto; }
		.test-result { padding: 10px; margin: 10px 0; border-radius: 4px; }
		.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
		.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
		.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
		.tab-test { margin: 20px 0; }
	</style>
</head>
<body>
	<div class="test-container">
		<h1>ENNU Admin Tabs Test</h1>
		
		<div class="test-result info">
			<h3>Test Overview</h3>
			<p>This test verifies that the admin tab functionality is working correctly.</p>
		</div>

		<div class="tab-test">
			<h3>Tab Structure Test</h3>
			<div class="ennu-admin-tabs">
				<nav class="ennu-admin-tab-nav">
					<ul>
						<li><a href="#tab-1" class="ennu-admin-tab-active">Tab 1</a></li>
						<li><a href="#tab-2">Tab 2</a></li>
						<li><a href="#tab-3">Tab 3</a></li>
					</ul>
				</nav>
				
				<div id="tab-1" class="ennu-admin-tab-content ennu-admin-tab-active">
					<h4>Tab 1 Content</h4>
					<p>This is the first tab content. It should be visible by default.</p>
				</div>
				
				<div id="tab-2" class="ennu-admin-tab-content">
					<h4>Tab 2 Content</h4>
					<p>This is the second tab content. It should be hidden initially.</p>
				</div>
				
				<div id="tab-3" class="ennu-admin-tab-content">
					<h4>Tab 3 Content</h4>
					<p>This is the third tab content. It should be hidden initially.</p>
				</div>
			</div>
		</div>

		<div class="test-result info">
			<h3>Instructions</h3>
			<ol>
				<li>Click on "Tab 2" - the content should switch</li>
				<li>Click on "Tab 3" - the content should switch again</li>
				<li>Click on "Tab 1" - should return to first tab</li>
				<li>Check browser console for any JavaScript errors</li>
			</ol>
		</div>

		<div class="test-result info">
			<h3>Data Synchronization Status</h3>
			<?php
			$user_id = get_current_user_id();
			
			// Test centralized symptoms data source
			if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
				echo '<p><strong>✅ Centralized Symptoms Manager:</strong> Available</p>';
				
				$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
				echo '<p><strong>✅ Live Data Source:</strong> ' . count( $centralized_symptoms['symptoms'] ?? array() ) . ' symptoms found</p>';
				
				// Test old data source
				$old_symptoms = get_user_meta( $user_id, 'ennu_centralized_symptoms', true );
				echo '<p><strong>⚠️ Old Data Source:</strong> ' . ( is_array( $old_symptoms ) ? count( $old_symptoms ) : '0' ) . ' symptoms found</p>';
				
				if ( count( $centralized_symptoms['symptoms'] ?? array() ) !== ( is_array( $old_symptoms ) ? count( $old_symptoms ) : 0 ) ) {
					echo '<p><strong>🚨 DATA MISMATCH:</strong> The old and new data sources have different symptom counts!</p>';
				} else {
					echo '<p><strong>✅ Data Sources Match:</strong> Both sources have the same symptom count</p>';
				}
			} else {
				echo '<p><strong>❌ Centralized Symptoms Manager:</strong> Not available</p>';
			}
			?>
		</div>

		<div class="test-result info">
			<h3>JavaScript Status</h3>
			<div id="js-status">Checking JavaScript...</div>
		</div>
	</div>

	<script>
	// Test JavaScript functionality
	document.addEventListener('DOMContentLoaded', function() {
		const jsStatus = document.getElementById('js-status');
		
		// Check if tab elements exist
		const tabContainer = document.querySelector('.ennu-admin-tabs');
		const tabLinks = document.querySelectorAll('.ennu-admin-tab-nav a');
		const tabContents = document.querySelectorAll('.ennu-admin-tab-content');
		
		if (tabContainer && tabLinks.length > 0 && tabContents.length > 0) {
			jsStatus.innerHTML = '<strong>✅ Tab Elements Found:</strong> ' + tabLinks.length + ' tabs, ' + tabContents.length + ' content areas';
			
			// Test tab switching
			let tabSwitchCount = 0;
			tabLinks.forEach(function(link) {
				link.addEventListener('click', function(e) {
					e.preventDefault();
					tabSwitchCount++;
					
					const targetId = this.getAttribute('href');
					const targetContent = document.querySelector(targetId);
					
					if (targetContent) {
						// Hide all content
						tabContents.forEach(function(content) {
							content.style.display = 'none';
							content.classList.remove('ennu-admin-tab-active');
						});
						
						// Remove active from all links
						tabLinks.forEach(function(link) {
							link.classList.remove('ennu-admin-tab-active');
						});
						
						// Show target content
						targetContent.style.display = 'block';
						targetContent.classList.add('ennu-admin-tab-active');
						
						// Add active to clicked link
						this.classList.add('ennu-admin-tab-active');
						
						jsStatus.innerHTML = '<strong>✅ Tab Switching Working:</strong> Switched to ' + targetId + ' (Count: ' + tabSwitchCount + ')';
					} else {
						jsStatus.innerHTML = '<strong>❌ Tab Target Not Found:</strong> ' + targetId;
					}
				});
			});
		} else {
			jsStatus.innerHTML = '<strong>❌ Tab Elements Missing:</strong> Container: ' + !!tabContainer + ', Links: ' + tabLinks.length + ', Content: ' + tabContents.length;
		}
	});
	</script>
</body>
</html> 