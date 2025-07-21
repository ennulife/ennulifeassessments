<?php
/**
 * Test file to debug assessment submission
 */

// Include WordPress
require_once '../../../wp-load.php';

// Check if user is logged in
if ( ! is_user_logged_in() ) {
	wp_die( 'Please log in to view this page.' );
}

$current_user = wp_get_current_user();
$nonce        = wp_create_nonce( 'ennu_ajax_nonce' );

?>
<!DOCTYPE html>
<html>
<head>
	<title>Assessment Submission Test</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 20px; }
		.test-form { max-width: 600px; margin: 0 auto; }
		.form-group { margin-bottom: 15px; }
		label { display: block; margin-bottom: 5px; font-weight: bold; }
		input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
		button { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
		button:hover { background: #005a87; }
		.result { margin-top: 20px; padding: 15px; border-radius: 4px; }
		.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
		.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
		.loading { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
	</style>
</head>
<body>
	<div class="test-form">
		<h1>Assessment Submission Test</h1>
		<p>Testing the assessment submission AJAX endpoint.</p>
		
		<form id="test-assessment-form">
			<div class="form-group">
				<label for="assessment_type">Assessment Type:</label>
				<select id="assessment_type" name="assessment_type" required>
					<option value="">Select Assessment Type</option>
					<option value="hair_assessment">Hair Assessment</option>
					<option value="health_optimization_assessment">Health Optimization Assessment</option>
					<option value="ed_treatment_assessment">ED Treatment Assessment</option>
				</select>
			</div>
			
			<div class="form-group">
				<label for="email">Email:</label>
				<input type="email" id="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required>
			</div>
			
			<div class="form-group">
				<label for="first_name">First Name:</label>
				<input type="text" id="first_name" name="first_name" value="<?php echo esc_attr( $current_user->first_name ); ?>" required>
			</div>
			
			<div class="form-group">
				<label for="last_name">Last Name:</label>
				<input type="text" id="last_name" name="last_name" value="<?php echo esc_attr( $current_user->last_name ); ?>" required>
			</div>
			
			<div class="form-group">
				<label for="hair_q1">Gender (for hair assessment):</label>
				<select id="hair_q1" name="hair_q1">
					<option value="">Select Gender</option>
					<option value="male">Male</option>
					<option value="female">Female</option>
				</select>
			</div>
			
			<div class="form-group">
				<label for="hair_q2">Hair Concerns (for hair assessment):</label>
				<select id="hair_q2" name="hair_q2">
					<option value="">Select Concern</option>
					<option value="thinning">Thinning</option>
					<option value="balding">Balding</option>
					<option value="slow_growth">Slow Growth</option>
				</select>
			</div>
			
			<input type="hidden" name="action" value="ennu_submit_assessment">
			<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
			
			<button type="submit">Submit Assessment</button>
		</form>
		
		<div id="result" class="result" style="display: none;"></div>
	</div>

	<script>
		document.getElementById('test-assessment-form').addEventListener('submit', function(e) {
			e.preventDefault();
			
			const form = e.target;
			const resultDiv = document.getElementById('result');
			
			// Show loading
			resultDiv.className = 'result loading';
			resultDiv.style.display = 'block';
			resultDiv.innerHTML = 'Submitting assessment...';
			
			// Prepare form data
			const formData = new FormData(form);
			
			// Make AJAX request
			fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				body: formData,
				credentials: 'same-origin'
			})
			.then(response => {
				console.log('Response status:', response.status);
				return response.json();
			})
			.then(data => {
				console.log('Response data:', data);
				
				if (data.success) {
					resultDiv.className = 'result success';
					resultDiv.innerHTML = `
						<h3>Success!</h3>
						<p>Message: ${data.data.message || 'Assessment submitted successfully'}</p>
						<p>Redirect URL: ${data.data.redirect_url || 'None'}</p>
						<p>User ID: ${data.data.user_id || 'None'}</p>
						<pre>${JSON.stringify(data, null, 2)}</pre>
					`;
				} else {
					resultDiv.className = 'result error';
					resultDiv.innerHTML = `
						<h3>Error!</h3>
						<p>Message: ${data.data.message || 'Unknown error'}</p>
						<p>Code: ${data.data.code || 'None'}</p>
						<pre>${JSON.stringify(data, null, 2)}</pre>
					`;
				}
			})
			.catch(error => {
				console.error('Fetch error:', error);
				resultDiv.className = 'result error';
				resultDiv.innerHTML = `
					<h3>Network Error!</h3>
					<p>Error: ${error.message}</p>
					<pre>${error.stack}</pre>
				`;
			});
		});
		
		// Test AJAX endpoint availability
		console.log('Testing AJAX endpoint availability...');
		fetch('/wp-admin/admin-ajax.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: 'action=ennu_submit_assessment&test=1'
		})
		.then(response => response.text())
		.then(text => {
			console.log('AJAX endpoint response:', text);
		})
		.catch(error => {
			console.error('AJAX endpoint test failed:', error);
		});
	</script>
</body>
</html> 
