/**
 * ENNU Life CSV Import JavaScript
 * Handles CSV file upload and import functionality
 */

jQuery(document).ready(function($) {
	
	// Form submission handler
	$('#ennu-csv-import-form').on('submit', function(e) {
		e.preventDefault();
		
		var $form = $(this);
		var $submitButton = $form.find('#submit');
		var $spinner = $form.find('.spinner');
		var $results = $('#ennu-import-results');
		var $summary = $('#ennu-import-summary');
		var $details = $('#ennu-import-details');
		
		// Validate form
		var userId = $form.find('select[name="user_id"]').val();
		var csvFile = $form.find('input[name="biomarker_csv"]')[0].files[0];
		
		if (!userId) {
			alert('Please select a user.');
			return;
		}
		
		if (!csvFile) {
			alert('Please select a CSV file.');
			return;
		}
		
		// Check file type
		if (!csvFile.name.toLowerCase().endsWith('.csv')) {
			alert('Please select a valid CSV file.');
			return;
		}
		
		// Show loading state
		$form.addClass('ennu-import-loading');
		$submitButton.prop('disabled', true);
		$spinner.addClass('is-active');
		$results.hide();
		
		// Prepare form data
		var formData = new FormData();
		formData.append('action', 'ennu_csv_import_biomarkers');
		formData.append('nonce', ennuCSVImport.nonce);
		formData.append('user_id', userId);
		formData.append('biomarker_csv', csvFile);
		
		// Add checkboxes
		if ($form.find('input[name="overwrite_existing"]').is(':checked')) {
			formData.append('overwrite_existing', '1');
		}
		
		if ($form.find('input[name="update_scores"]').is(':checked')) {
			formData.append('update_scores', '1');
		}
		
		// Send AJAX request
		$.ajax({
			url: ennuCSVImport.ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			timeout: 60000, // 60 seconds timeout
			success: function(response) {
				handleImportResponse(response, $summary, $details, $results);
			},
			error: function(xhr, status, error) {
				handleImportError(xhr, status, error, $summary, $results);
			},
			complete: function() {
				// Reset loading state
				$form.removeClass('ennu-import-loading');
				$submitButton.prop('disabled', false);
				$spinner.removeClass('is-active');
			}
		});
	});
	
	/**
	 * Handle successful import response
	 */
	function handleImportResponse(response, $summary, $details, $results) {
		if (response.success) {
			var data = response.data;
			
			// Show success summary
			$summary.removeClass('error').addClass('success').html(
				'<strong>✓ Import Successful!</strong><br>' +
				data.message + '<br>' +
				'Imported: ' + data.imported_count + ' biomarkers'
			);
			
			// Build details
			var detailsHtml = '';
			
			// Imported biomarkers
			if (data.imported_data && Object.keys(data.imported_data).length > 0) {
				detailsHtml += '<div class="import-detail-section">';
				detailsHtml += '<h4>Imported Biomarkers:</h4>';
				detailsHtml += '<ul class="import-detail-list">';
				
				$.each(data.imported_data, function(biomarker, info) {
					detailsHtml += '<li class="success">';
					detailsHtml += '<strong>' + biomarker + ':</strong> ' + info.value + ' ' + info.unit;
					detailsHtml += ' (Date: ' + info.date + ')';
					detailsHtml += '</li>';
				});
				
				detailsHtml += '</ul></div>';
			}
			
			// Warnings
			if (data.warnings && data.warnings.length > 0) {
				detailsHtml += '<div class="import-detail-section">';
				detailsHtml += '<h4>Warnings:</h4>';
				detailsHtml += '<ul class="import-detail-list">';
				
				$.each(data.warnings, function(index, warning) {
					detailsHtml += '<li class="warning">' + warning + '</li>';
				});
				
				detailsHtml += '</ul></div>';
			}
			
			// Import options
			detailsHtml += '<div class="import-detail-section">';
			detailsHtml += '<h4>Import Options:</h4>';
			detailsHtml += '<ul class="import-detail-list">';
			detailsHtml += '<li>Overwrite existing: ' + (data.overwritten ? 'Yes' : 'No') + '</li>';
			detailsHtml += '<li>Scores updated: ' + (data.scores_updated ? 'Yes' : 'No') + '</li>';
			detailsHtml += '</ul></div>';
			
			$details.html(detailsHtml);
			
		} else {
			// Show error
			$summary.removeClass('success').addClass('error').html(
				'<strong>✗ Import Failed</strong><br>' +
				(response.data ? response.data.message : 'Unknown error occurred')
			);
			$details.html('');
		}
		
		$results.show();
		
		// Scroll to results
		$('html, body').animate({
			scrollTop: $results.offset().top - 50
		}, 500);
	}
	
	/**
	 * Handle import error
	 */
	function handleImportError(xhr, status, error, $summary, $results) {
		var errorMessage = 'An error occurred during import.';
		
		if (status === 'timeout') {
			errorMessage = 'Import timed out. Please try with a smaller file or check your server settings.';
		} else if (xhr.responseJSON && xhr.responseJSON.data) {
			errorMessage = xhr.responseJSON.data;
		} else if (xhr.status === 413) {
			errorMessage = 'File too large. Please use a smaller CSV file.';
		} else if (xhr.status === 0) {
			errorMessage = 'Network error. Please check your connection and try again.';
		}
		
		$summary.removeClass('success').addClass('error').html(
			'<strong>✗ Import Failed</strong><br>' + errorMessage
		);
		
		$('#ennu-import-details').html('');
		$results.show();
	}
	
	/**
	 * File input change handler for validation
	 */
	$('input[name="biomarker_csv"]').on('change', function() {
		var file = this.files[0];
		var $fileInput = $(this);
		
		if (file) {
			// Check file size (max 5MB)
			if (file.size > 5 * 1024 * 1024) {
				alert('File size must be less than 5MB.');
				$fileInput.val('');
				return;
			}
			
			// Check file type
			if (!file.name.toLowerCase().endsWith('.csv')) {
				alert('Please select a valid CSV file.');
				$fileInput.val('');
				return;
			}
			
			// Show selected file name
			$fileInput.attr('title', 'Selected: ' + file.name);
		}
	});
	
	/**
	 * User selection change handler
	 */
	$('select[name="user_id"]').on('change', function() {
		var userId = $(this).val();
		if (userId) {
			// You could add AJAX call here to get user's existing biomarkers
			// and show them in a preview section
		}
	});
	
	/**
	 * Add drag and drop functionality
	 */
	var $fileInput = $('input[name="biomarker_csv"]');
	var $fileContainer = $fileInput.parent();
	
	// Prevent default drag behaviors
	$fileContainer.on('dragenter dragover', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).addClass('drag-over');
	});
	
	$fileContainer.on('dragleave', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).removeClass('drag-over');
	});
	
	$fileContainer.on('drop', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).removeClass('drag-over');
		
		var files = e.originalEvent.dataTransfer.files;
		if (files.length > 0) {
			$fileInput[0].files = files;
			$fileInput.trigger('change');
		}
	});
	
	// Add CSS for drag over state
	$('<style>')
		.prop('type', 'text/css')
		.html(`
			.drag-over input[type="file"] {
				border-color: #0073aa;
				background-color: #f0f8ff;
			}
		`)
		.appendTo('head');
	
	/**
	 * Add keyboard shortcuts
	 */
	$(document).on('keydown', function(e) {
		// Ctrl/Cmd + Enter to submit form
		if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
			$('#ennu-csv-import-form').submit();
		}
	});
	
	/**
	 * Add form validation feedback
	 */
	$('#ennu-csv-import-form').on('input change', function() {
		var $form = $(this);
		var $submitButton = $form.find('#submit');
		var userId = $form.find('select[name="user_id"]').val();
		var csvFile = $form.find('input[name="biomarker_csv"]')[0].files[0];
		
		if (userId && csvFile) {
			$submitButton.prop('disabled', false);
		} else {
			$submitButton.prop('disabled', true);
		}
	});
	
	// Initialize form state
	$('#ennu-csv-import-form').trigger('change');
}); 