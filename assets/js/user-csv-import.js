/**
 * ENNU Life User CSV Import JavaScript
 * Frontend functionality for user biomarker import shortcode
 */

jQuery(document).ready(function($) {
	
	// Form submission handler
	$('#ennu-user-csv-import-form').on('submit', function(e) {
		e.preventDefault();
		
		var $form = $(this);
		var $submitButton = $form.find('.ennu-import-btn');
		var $spinner = $form.find('.ennu-spinner');
		var $results = $('#ennu-user-import-results');
		var $summary = $('#ennu-user-import-summary');
		var $details = $('#ennu-user-import-details');
		
		// Validate form
		var csvFile = $form.find('input[name="user_csv_file"]')[0].files[0];
		
		if (!csvFile) {
			showError('Please select a CSV file.');
			return;
		}
		
		// Check file type
		if (!csvFile.name.toLowerCase().endsWith('.csv')) {
			showError('Please select a valid CSV file.');
			return;
		}
		
		// Check file size (5MB limit)
		if (csvFile.size > 5 * 1024 * 1024) {
			showError('File size must be less than 5MB.');
			return;
		}
		
		// Show loading state
		$form.addClass('ennu-import-loading');
		$submitButton.prop('disabled', true);
		$spinner.show();
		$results.hide();
		
		// Prepare form data
		var formData = new FormData();
		formData.append('action', 'ennu_user_csv_import');
		formData.append('nonce', ennuUserCSVImport.nonce);
		formData.append('user_csv_file', csvFile);
		
		// Add checkboxes
		if ($form.find('input[name="overwrite_existing"]').is(':checked')) {
			formData.append('overwrite_existing', '1');
		}
		
		if ($form.find('input[name="update_scores"]').is(':checked')) {
			formData.append('update_scores', '1');
		}
		
		// Send AJAX request
		$.ajax({
			url: ennuUserCSVImport.ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			timeout: 60000, // 60 seconds timeout
			success: function(response) {
				handleUserImportResponse(response, $summary, $details, $results);
			},
			error: function(xhr, status, error) {
				handleUserImportError(xhr, status, error, $summary, $results);
			},
			complete: function() {
				// Reset loading state
				$form.removeClass('ennu-import-loading');
				$submitButton.prop('disabled', false);
				$spinner.hide();
			}
		});
	});
	
	/**
	 * Handle successful import response
	 */
	function handleUserImportResponse(response, $summary, $details, $results) {
		if (response.success) {
			var data = response.data;
			
			// Show success summary
			$summary.removeClass('error').addClass('success').html(
				'<strong>✅ ' + ennuUserCSVImport.strings.success + '</strong><br>' +
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
				'<strong>❌ ' + ennuUserCSVImport.strings.error + '</strong><br>' +
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
	function handleUserImportError(xhr, status, error, $summary, $results) {
		var errorMessage = 'An error occurred during import.';
		
		if (status === 'timeout') {
			errorMessage = 'Import timed out. Please try with a smaller file or check your connection.';
		} else if (xhr.responseJSON && xhr.responseJSON.data) {
			errorMessage = xhr.responseJSON.data;
		} else if (xhr.status === 413) {
			errorMessage = 'File too large. Please use a smaller CSV file.';
		} else if (xhr.status === 0) {
			errorMessage = 'Network error. Please check your connection and try again.';
		}
		
		$summary.removeClass('success').addClass('error').html(
			'<strong>❌ ' + ennuUserCSVImport.strings.error + '</strong><br>' + errorMessage
		);
		
		$('#ennu-user-import-details').html('');
		$results.show();
	}
	
	/**
	 * Show error message
	 */
	function showError(message) {
		// Create a temporary error message
		var $errorDiv = $('<div class="ennu-error-message" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0;">' + message + '</div>');
		$('#ennu-user-csv-import-form').prepend($errorDiv);
		
		// Remove after 5 seconds
		setTimeout(function() {
			$errorDiv.fadeOut(function() {
				$(this).remove();
			});
		}, 5000);
	}
	
	/**
	 * File input change handler for validation
	 */
	$('input[name="user_csv_file"]').on('change', function() {
		var file = this.files[0];
		var $fileInput = $(this);
		var $formGroup = $fileInput.closest('.ennu-form-group');
		
		// Remove previous error states
		$formGroup.removeClass('error success');
		$formGroup.find('.ennu-error-message').remove();
		
		if (file) {
			// Check file size (5MB)
			if (file.size > 5 * 1024 * 1024) {
				$formGroup.addClass('error');
				$formGroup.append('<div class="ennu-error-message">File size must be less than 5MB.</div>');
				$fileInput.val('');
				return;
			}
			
			// Check file type
			if (!file.name.toLowerCase().endsWith('.csv')) {
				$formGroup.addClass('error');
				$formGroup.append('<div class="ennu-error-message">Please select a valid CSV file.</div>');
				$fileInput.val('');
				return;
			}
			
			// Show success state
			$formGroup.addClass('success');
			
			// Show selected file name
			$fileInput.attr('title', 'Selected: ' + file.name);
		}
	});
	
	/**
	 * Add drag and drop functionality
	 */
	var $fileInput = $('input[name="user_csv_file"]');
	var $fileContainer = $fileInput.closest('.ennu-form-group');
	
	// Prevent default drag behaviors
	$fileContainer.on('dragenter dragover', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).addClass('ennu-drag-over');
	});
	
	$fileContainer.on('dragleave', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).removeClass('ennu-drag-over');
	});
	
	$fileContainer.on('drop', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).removeClass('ennu-drag-over');
		
		var files = e.originalEvent.dataTransfer.files;
		if (files.length > 0) {
			$fileInput[0].files = files;
			$fileInput.trigger('change');
		}
	});
	
	/**
	 * Add keyboard shortcuts
	 */
	$(document).on('keydown', function(e) {
		// Ctrl/Cmd + Enter to submit form
		if ((e.ctrlKey || e.metaKey) && e.keyCode === 13) {
			$('#ennu-user-csv-import-form').submit();
		}
	});
	
	/**
	 * Add form validation feedback
	 */
	$('#ennu-user-csv-import-form').on('input change', function() {
		var $form = $(this);
		var $submitButton = $form.find('.ennu-import-btn');
		var csvFile = $form.find('input[name="user_csv_file"]')[0].files[0];
		
		if (csvFile) {
			$submitButton.prop('disabled', false);
		} else {
			$submitButton.prop('disabled', true);
		}
	});
	
	/**
	 * Add file upload progress indicator
	 */
	function addProgressBar() {
		var $fileInput = $('input[name="user_csv_file"]');
		var $formGroup = $fileInput.closest('.ennu-form-group');
		
		if ($formGroup.find('.ennu-upload-progress').length === 0) {
			var progressHtml = '<div class="ennu-upload-progress"><div class="ennu-upload-progress-bar"></div></div>';
			$formGroup.append(progressHtml);
		}
	}
	
	/**
	 * Update progress bar
	 */
	function updateProgress(percent) {
		$('.ennu-upload-progress-bar').css('width', percent + '%');
	}
	
	/**
	 * Remove progress bar
	 */
	function removeProgressBar() {
		$('.ennu-upload-progress').remove();
	}
	
	/**
	 * Add success message after import
	 */
	function showSuccessMessage(message) {
		var $successDiv = $('<div class="ennu-success-message" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 15px 0; text-align: center; font-weight: 600;">' + message + '</div>');
		$('.ennu-user-csv-import-container').prepend($successDiv);
		
		// Remove after 8 seconds
		setTimeout(function() {
			$successDiv.fadeOut(function() {
				$(this).remove();
			});
		}, 8000);
	}
	
	/**
	 * Add copy to clipboard functionality for sample CSV
	 */
	$('.ennu-sample-csv').on('click', function() {
		var sampleText = $(this).find('pre').text();
		
		// Create temporary textarea to copy text
		var $temp = $('<textarea>');
		$('body').append($temp);
		$temp.val(sampleText).select();
		document.execCommand('copy');
		$temp.remove();
		
		// Show feedback
		var $feedback = $('<div style="position: fixed; top: 20px; right: 20px; background: #27ae60; color: white; padding: 10px 15px; border-radius: 4px; z-index: 9999;">Sample CSV copied to clipboard!</div>');
		$('body').append($feedback);
		
		setTimeout(function() {
			$feedback.fadeOut(function() {
				$(this).remove();
			});
		}, 2000);
	});
	
	/**
	 * Add hover effect to sample CSV
	 */
	$('.ennu-sample-csv').hover(
		function() {
			$(this).css('cursor', 'pointer');
			$(this).append('<div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 3px; font-size: 0.8em;">Click to copy</div>');
		},
		function() {
			$(this).css('cursor', 'default');
			$(this).find('div').last().remove();
		}
	);
	
	/**
	 * Initialize form state
	 */
	$('#ennu-user-csv-import-form').trigger('change');
	
	/**
	 * Add accessibility improvements
	 */
	$('.ennu-checkbox-label').on('keydown', function(e) {
		if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
			e.preventDefault();
			$(this).find('input[type="checkbox"]').click();
		}
	});
	
	/**
	 * Add focus management
	 */
	$('#ennu-user-csv-import-form').on('submit', function() {
		// Store the submit button for later focus
		window.lastFocusedElement = $('.ennu-import-btn')[0];
	});
	
	// Restore focus after AJAX completion
	$(document).ajaxComplete(function() {
		if (window.lastFocusedElement) {
			window.lastFocusedElement.focus();
			window.lastFocusedElement = null;
		}
	});
}); 