jQuery(document).ready(function($) {
    $('#ennu-clear-data').on('click', function() {
        if (confirm(ennu_admin.confirm_msg)) {
            var userId = $(this).data('user-id');
            $.ajax({
                url: ennu_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_clear_user_data',
                    user_id: userId,
                    nonce: ennu_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data);
                    }
                },
                error: function() {
                    alert('AJAX error - please try again.');
                }
            });
        }
    });

    // v57.0.7: Refactored and corrected tab logic
    const $tabContainer = $(".ennu-admin-tabs");
    if ($tabContainer.length) {
        const $tabLinks = $tabContainer.find(".ennu-admin-tab-nav a");
        const $tabContents = $tabContainer.find(".ennu-admin-tab-content");

        $tabLinks.on("click", function (event) {
            event.preventDefault();

            const $this = $(this);
            const targetId = $this.attr("href");

            // Deactivate all tabs and content
            $tabLinks.removeClass("ennu-admin-tab-active");
            $tabContents.removeClass("ennu-admin-tab-active");

            // Activate the clicked tab and corresponding content
            $this.addClass("ennu-admin-tab-active");
            $(targetId).addClass("ennu-admin-tab-active");
        });
    }

	// --- v57.1.0: Admin Actions ---
	const $spinner = $(".ennu-admin-action-buttons .spinner");

	$('#ennu-recalculate-scores').on('click', function() {
		const $button = $(this);
		const userId = $button.data('user-id');

		if (confirm('Are you sure you want to recalculate all scores for this user? This will overwrite their current calculated scores.')) {
			$button.prop('disabled', true);
			$spinner.addClass('is-active');

			$.post(ajaxurl, {
				action: 'ennu_recalculate_all_scores',
				user_id: userId,
				nonce: ennuAdmin.nonce
			}).done(function(response) {
				if (response.success) {
					alert('Scores recalculated successfully!');
					location.reload();
				} else {
					alert('An error occurred: ' + response.data.message);
				}
			}).fail(function() {
				alert('A server error occurred.');
			}).always(function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		}
	});

	$('#ennu-clear-all-data').on('click', function() {
		const $button = $(this);
		const userId = $button.data('user-id');

		if (confirm('WARNING: Are you absolutely sure you want to clear ALL assessment data for this user? This action cannot be undone.')) {
			$button.prop('disabled', true);
			$spinner.addClass('is-active');

			$.post(ajaxurl, {
				action: 'ennu_clear_all_assessment_data',
				user_id: userId,
				nonce: ennuAdmin.nonce
			}).done(function(response) {
				if (response.success) {
					alert('All assessment data cleared successfully!');
					location.reload();
				} else {
					alert('An error occurred: ' + response.data.message);
				}
			}).fail(function() {
				alert('A server error occurred.');
			}).always(function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		}
	});

	$('.ennu-clear-single-assessment-data').on('click', function() {
		const $button = $(this);
		const userId = $button.data('user-id');
		const assessmentKey = $button.data('assessment-key');

		if (confirm('Are you sure you want to clear the data for the ' + assessmentKey.replace(/_/g, " ") + ' assessment? This action cannot be undone.')) {
			$button.prop('disabled', true);
			$spinner.addClass('is-active');

			$.post(ajaxurl, {
				action: 'ennu_clear_single_assessment_data',
				user_id: userId,
				assessment_key: assessmentKey,
				nonce: ennuAdmin.nonce
			}).done(function(response) {
				if (response.success) {
					alert(assessmentKey.replace(/_/g, " ") + ' data cleared successfully!');
					location.reload();
				} else {
					alert('An error occurred: ' + response.data.message);
				}
			}).fail(function() {
				alert('A server error occurred.');
			}).always(function() {
				$button.prop('disabled', false);
				$spinner.removeClass('is-active');
			});
		}
	});
}); 