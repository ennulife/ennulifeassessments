<?php
/**
 * Unified Assessment Admin Interface
 * 
 * This class provides a unified admin interface for all assessments,
 * replacing the separate implementations with a shared system.
 *
 * @package ENNU_Life
 * @version 64.7.0
 */

class ENNU_Unified_Assessment_Admin {
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action('admin_menu', array($this, 'add_admin_menu'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		// DISABLED: Using original HubSpot Bulk Field Creator instead
		/*
		add_submenu_page(
			'ennu-life',
			__('HubSpot Field Management (Unified)', 'ennulifeassessments'),
			__('HubSpot Fields', 'ennulifeassessments'),
			'manage_options', // Changed from 'edit_posts' to 'manage_options' for proper admin access
			'ennu-life-unified-fields',
			array($this, 'admin_page')
		);
		*/
	}
	
	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts($hook) {
		if ('ennu-life_page_ennu-life-unified-fields' !== $hook) {
			return;
		}
		
		wp_localize_script('jquery', 'ennu_unified_ajax', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ennu_admin_nonce'),
		));
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		?>
		<div class="wrap">
			<h1>🔄 Unified Assessment Field Management</h1>
			
			<div class="notice notice-info">
				<p><strong>🎯 Unified System:</strong> All assessments now share the same infrastructure and resources, eliminating duplication and ensuring consistency.</p>
			</div>
			
			<!-- Assessment Overview Dashboard -->
			<div class="ennu-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin-bottom: 20px;">
				<h3 style="color: white; margin: 0 0 15px 0;">📊 Unified Assessment Overview</h3>
				<div class="ennu-section-content">
					<div id="unified-stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
						<!-- Stats will be loaded here via AJAX -->
						<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;">
							<div style="font-size: 24px; font-weight: bold;">🔄</div>
							<div>Loading Unified Statistics...</div>
						</div>
					</div>
					<div style="margin-top: 15px; text-align: center;">
						<button type="button" id="refresh-unified-stats" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
							🔄 Refresh Statistics
						</button>
					</div>
				</div>
			</div>
			
			<div class="ennu-unified-container">
				
				<!-- Shared Resources Section -->
				<div class="ennu-section">
					<h3>🌍 Shared Resources (Used Across ALL Assessments)</h3>
					<div class="ennu-section-content">
						<p><strong>Global Shared Fields:</strong> These 6 fields are automatically included in every assessment:</p>
						<ul>
							<li><strong>Gender</strong> - Demographic identification</li>
							<li><strong>Date of Birth</strong> - Age calculation</li>
							<li><strong>Height and Weight</strong> - BMI calculation</li>
							<li><strong>BMI</strong> - Calculated health metric</li>
							<li><strong>Age</strong> - Calculated demographic</li>
							<li><strong>Email Address</strong> - Primary contact</li>
						</ul>
						
						<p><strong>Assessment Metadata Fields:</strong> These 5 fields track assessment completion:</p>
						<ul>
							<li><strong>Assessment Type</strong> - Which assessment was taken</li>
							<li><strong>Assessment Date</strong> - When it was completed</li>
							<li><strong>Assessment Score</strong> - Overall score (0-10)</li>
							<li><strong>Assessment Attempt</strong> - Attempt number</li>
							<li><strong>Assessment Status</strong> - Completion status</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="create-shared-fields" class="button button-primary">
								🚀 Create Shared Fields (11 total)
							</button>
							<button type="button" id="delete-shared-fields" class="button button-secondary">🗑️ Delete Shared Fields</button>
						</div>
						
						<div id="shared-fields-status" class="ennu-status"></div>
					</div>
				</div>
				
				<!-- Individual Assessment Fields Section -->
				<div class="ennu-section">
					<h3>📋 Individual Assessment Fields</h3>
					<div class="ennu-section-content">
						<p>Create fields for specific assessments. Each assessment gets its own property group and includes both contact properties and historical tracking fields.</p>
						
						<div class="ennu-form-row">
							<label for="assessment-select">Select Assessment:</label>
							<select id="assessment-select" class="ennu-select">
								<option value="">Choose an assessment...</option>
								<option value="weight_loss">Weight Loss Assessment</option>
								<option value="hormone">Hormone Assessment</option>
								<option value="sleep">Sleep Assessment</option>
								<option value="health">Health Assessment</option>
								<option value="skin">Skin Assessment</option>
								<option value="ed_treatment">ED Treatment Assessment</option>
								<option value="menopause">Menopause Assessment</option>
								<option value="testosterone">Testosterone Assessment</option>
								<option value="consultation">Consultation Assessment</option>
							</select>
						</div>
						
						<div class="ennu-actions">
							<button type="button" id="preview-assessment-fields" class="button button-secondary">👁️ Preview Fields</button>
							<button type="button" id="create-assessment-fields" class="button button-primary" style="display:none;">🚀 Create Assessment Fields</button>
							<button type="button" id="delete-assessment-fields" class="button button-secondary">🗑️ Delete Assessment Fields</button>
						</div>
						
						<div id="assessment-preview" class="ennu-field-preview"></div>
						<div id="assessment-status" class="ennu-status"></div>
					</div>
				</div>
				
				<!-- Bulk Operations Section -->
				<div class="ennu-section">
					<h3>⚡ Bulk Operations</h3>
					<div class="ennu-section-content">
						<p><strong>Create All Assessment Fields:</strong> This will create fields for all 9 assessments at once.</p>
						
						<div class="ennu-actions">
							<button type="button" id="create-all-assessments" class="button button-primary">
								🚀 Create All Assessment Fields (9 assessments)
							</button>
							<button type="button" id="delete-all-assessments" class="button button-secondary">🗑️ Delete All Assessment Fields</button>
						</div>
						
						<div id="bulk-status" class="ennu-status"></div>
					</div>
				</div>
				
				<!-- System Information Section -->
				<div class="ennu-section">
					<h3>ℹ️ System Information</h3>
					<div class="ennu-section-content">
						<p><strong>Unified Architecture Benefits:</strong></p>
						<ul>
							<li>✅ <strong>Shared Resources:</strong> All assessments use the same field templates and infrastructure</li>
							<li>✅ <strong>Consistent Naming:</strong> Standardized field naming conventions across all assessments</li>
							<li>✅ <strong>Centralized Management:</strong> Single interface for managing all assessment fields</li>
							<li>✅ <strong>Historical Tracking:</strong> Automatic creation of historical tracking fields for all assessments</li>
							<li>✅ <strong>Metadata Standardization:</strong> Consistent assessment metadata across all types</li>
							<li>✅ <strong>Scalable Design:</strong> Easy to add new assessments without code duplication</li>
						</ul>
						
						<p><strong>Field Structure:</strong></p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions + shared global fields</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking + assessment metadata</li>
							<li><strong>Property Groups:</strong> Organized by assessment type for easy management</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		
		<style>
		.ennu-section {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 8px;
			margin-bottom: 20px;
			padding: 20px;
		}
		.ennu-section h3 {
			margin-top: 0;
			color: #333;
		}
		.ennu-section-content {
			margin-top: 15px;
		}
		.ennu-form-row {
			margin-bottom: 15px;
		}
		.ennu-form-row label {
			display: block;
			margin-bottom: 5px;
			font-weight: bold;
		}
		.ennu-select {
			width: 100%;
			max-width: 400px;
			padding: 8px;
			border: 1px solid #ddd;
			border-radius: 4px;
		}
		.ennu-actions {
			margin: 20px 0;
		}
		.ennu-actions .button {
			margin-right: 10px;
			margin-bottom: 10px;
		}
		.ennu-status {
			margin-top: 15px;
			padding: 10px;
			border-radius: 4px;
			display: none;
		}
		.ennu-status.success {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}
		.ennu-status.error {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}
		.ennu-field-preview {
			margin-top: 15px;
			padding: 15px;
			background: #f8f9fa;
			border: 1px solid #dee2e6;
			border-radius: 4px;
			display: none;
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			const nonce = ennu_unified_ajax.nonce;
			const ajaxurl = ennu_unified_ajax.ajaxurl;
			
			// Load unified statistics on page load
			loadUnifiedStatistics();
			
			// Refresh unified statistics
			$('#refresh-unified-stats').on('click', function() {
				loadUnifiedStatistics();
			});
			
			// Create shared fields
			$('#create-shared-fields').on('click', function() {
				if (!confirm('This will create 11 shared fields (6 global + 5 metadata) that are used across ALL assessments. Continue?')) {
					return;
				}
				
				const button = $(this);
				const status = $('#shared-fields-status');
				
				button.prop('disabled', true).text('Creating Shared Fields...');
				status.removeClass('success error').html('🔄 Creating shared fields...').show();
				
				$.post(ajaxurl, {
					action: 'ennu_create_unified_fields',
					nonce: nonce,
					assessment_type: 'shared'
				})
				.done(function(response) {
					if (response.success) {
						status.addClass('success').html('✅ ' + response.data.message);
					} else {
						status.addClass('error').html('❌ ' + response.data);
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Shared field creation failed');
				})
				.always(function() {
					button.prop('disabled', false).text('🚀 Create Shared Fields (11 total)');
				});
			});
			
			// Preview assessment fields
			$('#preview-assessment-fields').on('click', function() {
				const assessmentType = $('#assessment-select').val();
				if (!assessmentType) {
					alert('Please select an assessment first.');
					return;
				}
				
				const button = $(this);
				const preview = $('#assessment-preview');
				const createButton = $('#create-assessment-fields');
				
				button.prop('disabled', true).text('Loading Preview...');
				preview.hide();
				
				$.post(ajaxurl, {
					action: 'ennu_preview_unified_fields',
					nonce: nonce,
					assessment_type: assessmentType
				})
				.done(function(response) {
					if (response.success) {
						displayAssessmentPreview(response.data);
						createButton.show();
					} else {
						preview.addClass('error').html('❌ ' + response.data).show();
					}
				})
				.fail(function() {
					preview.addClass('error').html('❌ Preview failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('👁️ Preview Fields');
				});
			});
			
			// Create assessment fields
			$('#create-assessment-fields').on('click', function() {
				const assessmentType = $('#assessment-select').val();
				if (!assessmentType) {
					alert('Please select an assessment first.');
					return;
				}
				
				if (!confirm('This will create fields for the ' + $('#assessment-select option:selected').text() + '. Continue?')) {
					return;
				}
				
				const button = $(this);
				const status = $('#assessment-status');
				
				button.prop('disabled', true).text('Creating Assessment Fields...');
				status.removeClass('success error').html('🔄 Creating assessment fields...').show();
				
				$.post(ajaxurl, {
					action: 'ennu_create_unified_fields',
					nonce: nonce,
					assessment_type: assessmentType
				})
				.done(function(response) {
					if (response.success) {
						let statusHtml = '✅ ' + response.data.message;
						
						if (response.data.results) {
							statusHtml += '<br><br><strong>Results:</strong><br>';
							statusHtml += '• Contact Fields Created: ' + response.data.results.contacts.created + '<br>';
							statusHtml += '• Contact Fields Skipped: ' + response.data.results.contacts.skipped + '<br>';
							statusHtml += '• Custom Object Fields Created: ' + response.data.results.custom_object.created + '<br>';
							statusHtml += '• Custom Object Fields Skipped: ' + response.data.results.custom_object.skipped + '<br>';
							statusHtml += '• Errors: ' + (response.data.results.contacts.errors + response.data.results.custom_object.errors);
						}
						
						status.addClass('success').html(statusHtml).show();
					} else {
						status.addClass('error').html('❌ ' + response.data).show();
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Assessment field creation failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('🚀 Create Assessment Fields');
				});
			});
			
			// Create all assessments
			$('#create-all-assessments').on('click', function() {
				if (!confirm('This will create fields for ALL 9 assessments. This may take several minutes. Continue?')) {
					return;
				}
				
				const button = $(this);
				const status = $('#bulk-status');
				
				button.prop('disabled', true).text('Creating All Assessment Fields...');
				status.removeClass('success error').html('🔄 Creating fields for all assessments...').show();
				
				$.post(ajaxurl, {
					action: 'ennu_create_unified_fields',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						let statusHtml = '✅ ' + response.data.message;
						
						if (response.data.results) {
							statusHtml += '<br><br><strong>Results:</strong><br>';
							statusHtml += '• Contact Fields Created: ' + response.data.results.contacts.created + '<br>';
							statusHtml += '• Contact Fields Skipped: ' + response.data.results.contacts.skipped + '<br>';
							statusHtml += '• Custom Object Fields Created: ' + response.data.results.custom_object.created + '<br>';
							statusHtml += '• Custom Object Fields Skipped: ' + response.data.results.custom_object.skipped + '<br>';
							statusHtml += '• Errors: ' + (response.data.results.contacts.errors + response.data.results.custom_object.errors);
						}
						
						status.addClass('success').html(statusHtml).show();
					} else {
						status.addClass('error').html('❌ ' + response.data).show();
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Bulk field creation failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('🚀 Create All Assessment Fields (9 assessments)');
				});
			});
			
			// Load unified statistics
			function loadUnifiedStatistics() {
				const container = $('#unified-stats-container');
				
				$.post(ajaxurl, {
					action: 'ennu_get_unified_statistics',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						let html = '';
						$.each(response.data, function(type, stats) {
							html += '<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;">';
							html += '<div style="font-size: 18px; font-weight: bold;">' + stats.name + '</div>';
							html += '<div style="font-size: 14px;">Contact: ' + stats.contact_fields + '</div>';
							html += '<div style="font-size: 14px;">Custom: ' + stats.custom_object_fields + '</div>';
							html += '<div style="font-size: 12px; opacity: 0.8;">Total: ' + stats.total_fields + '</div>';
							html += '</div>';
						});
						container.html(html);
					} else {
						container.html('<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;"><div style="color: #ff6b6b;">❌ Failed to load statistics</div></div>');
					}
				})
				.fail(function() {
					container.html('<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;"><div style="color: #ff6b6b;">❌ Failed to load statistics</div></div>');
				});
			}
			
			// Display assessment preview
			function displayAssessmentPreview(data) {
				const preview = $('#assessment-preview');
				let html = '<h4>📋 ' + data.assessment + ' Field Preview</h4>';
				
				html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">';
				
				// Contact fields
				html += '<div><h5>👤 Contact Properties (' + data.total_contact_fields + ')</h5>';
				html += '<ul style="font-size: 12px; max-height: 200px; overflow-y: auto;">';
				$.each(data.contact_fields, function(i, field) {
					html += '<li><strong>' + field.name + '</strong>: ' + field.label + '</li>';
				});
				html += '</ul></div>';
				
				// Custom object fields
				html += '<div><h5>📊 Custom Object Fields (' + data.total_custom_object_fields + ')</h5>';
				html += '<ul style="font-size: 12px; max-height: 200px; overflow-y: auto;">';
				$.each(data.custom_object_fields, function(i, field) {
					html += '<li><strong>' + field.name + '</strong>: ' + field.label + '</li>';
				});
				html += '</ul></div>';
				
				html += '</div>';
				
				preview.removeClass('error').html(html).show();
			}
		});
		</script>
		<?php
	}
}

// Initialize the unified admin interface
new ENNU_Unified_Assessment_Admin(); 