/**
 * ENNU Life Enhanced Admin JavaScript
 * Handles admin tabs and AJAX actions for user profile management.
 *
 * @package ENNU_Life
 * @version 64.6.1
 */
jQuery( document ).ready(
	function ($) {
		'use strict';

		/**
		 * Initialize admin tabs
		 */
		function initAdminTabs() {
			const tabContainers = $( '.ennu-admin-tabs' );
			if ( ! tabContainers.length) {
				return;
			}

			tabContainers.each(
				function () {
					const container   = $( this );
					const tabLinks    = container.find( '.ennu-admin-tab-nav a' );
					const tabContents = container.find( '.ennu-admin-tab-content' );

					if ( ! tabLinks.length || ! tabContents.length) {
						return;
					}

					// Handle hash on page load
					if (window.location.hash) {
						const targetTab = $( window.location.hash );
						if (targetTab.length && targetTab.hasClass( 'ennu-admin-tab-content' )) {
							tabContents.removeClass( 'ennu-admin-tab-active' ).hide();
							tabLinks.removeClass( 'ennu-admin-tab-active' );

							targetTab.addClass( 'ennu-admin-tab-active' ).show();
							$( 'a[href="' + window.location.hash + '"]' ).addClass( 'ennu-admin-tab-active' );
						}
					}

					tabLinks.on(
						'click',
						function (e) {
							e.preventDefault();
							const targetId = $( this ).attr( 'href' );

							tabContents.removeClass( 'ennu-admin-tab-active' ).hide();
							tabLinks.removeClass( 'ennu-admin-tab-active' );

							$( targetId ).addClass( 'ennu-admin-tab-active' ).show();
							$( this ).addClass( 'ennu-admin-tab-active' );

							if (history.pushState) {
								history.pushState( null, null, targetId );
							} else {
								window.location.hash = targetId;
							}
						}
					);
				}
			);
		}

		/**
		 * Display an admin notice.
		 * @param {string} message The message to display.
		 * @param {string} type The type of notice (success, error, warning, info).
		 */
		function showAdminNotice(message, type = 'success') {
			const noticeHtml = `
            <div class="notice notice-${type} is-dismissible" style="margin-top: 20px;">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>`;
			$( '.wrap h1' ).first().after( noticeHtml );

			$( '.notice.is-dismissible .notice-dismiss' ).on(
				'click',
				function () {
					$( this ).closest( '.notice' ).fadeOut(
						300,
						function () {
							$( this ).remove();
						}
					);
				}
			);
		}

		/**
		 * Handle AJAX requests for symptom management buttons.
		 * @param {string} action The AJAX action to perform.
		 * @param {jQuery} button The button element that was clicked.
		 */
		function handleSymptomAction(action, button) {
			const userId       = button.data( 'user-id' );
			const originalText = button.text();

			button.prop( 'disabled', true ).text( ennu_admin_ajax.strings.updating || 'Processing...' );

			$.ajax(
				{
					url: ennu_admin_ajax.ajax_url,
					type: 'POST',
					data: {
						action: action,
						user_id: userId,
						nonce: ennu_admin_ajax.nonce
					},
					success: function (response) {
						if (response.success) {
							showAdminNotice( response.data.message || ennu_admin_ajax.strings.success || 'Success!', 'success' );
							// Optionally reload the page to see updated data.
							// location.reload();
						} else {
							showAdminNotice( response.data.message || ennu_admin_ajax.strings.error || 'An error occurred.', 'error' );
						}
					},
					error: function (jqXHR, textStatus, errorThrown) {
						showAdminNotice( 'An unexpected server error occurred: ' + errorThrown, 'error' );
					},
					complete: function () {
						button.prop( 'disabled', false ).text( originalText );
					}
				}
			);
		}

		// --- Event Handlers ---

		// Manually Recalculate Symptoms button
		$( '#ennu-update-centralized-symptoms' ).on(
			'click',
			function () {
				handleSymptomAction( 'ennu_update_centralized_symptoms', $( this ) );
			}
		);

		// Clear Symptom History button
		$( '#ennu-clear-symptom-history' ).on(
			'click',
			function () {
				if (window.confirm( 'Are you sure you want to permanently clear the symptom history for this user? This action cannot be undone.' )) {
					handleSymptomAction( 'ennu_clear_symptom_history', $( this ) );
				}
			}
		);

		// Initialize all components
		initAdminTabs();
	}
);    