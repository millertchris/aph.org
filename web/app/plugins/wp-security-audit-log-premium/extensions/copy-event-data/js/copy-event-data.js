/**
 * JavaScript for handling copy event data buttons.
 *
 * @since 5.6.0
 */
(function ($) {
	class WSALCopyEventData {
		// DOM Selectors
		$eventRow = $('#the-list > tr');

		constructor() {
			this.bindCopyButtons();

			$(
				'.wsal-copy-event-data-simple-button, .wsal-copy-event-data-extended-button'
			).darkTooltip({
				animation: 'fadeIn',
				gravity: 'east'
			});
		}

		/**
		 * Format date for display
		 *
		 * @param {string} dateStr - The original date string.
		 *
		 * @return {string} - The formatted date string.
		 *
		 * @since 5.6.0
		 */
		formatDateString(dateStr) {
			// Example input: "September 23, 2025 1:48:41.000 pm"
			const match = dateStr.match(/^(.+?)\s+(\d{1,2}:\d{2})/i);
			const ampmMatch = dateStr.match(/(am|pm)/i);

			if (match && ampmMatch) {
				const datePart = match[1].trim();
				const timePart = match[2].trim();
				const ampm = ampmMatch[1].toLowerCase();
				return `${datePart} - ${timePart} ${ampm}`;
			}
			return dateStr;
		}

		/**
		 * Returns the concatenated text of all child nodes of the given jQuery element,
		 * separated by spaces and trimmed.
		 *
		 * @param {jQuery} $element - The jQuery object to extract text from.
		 * @returns {string} - The text content with spaces preserved.
		 */
		getTextWithSpaces($element) {
			return $element
				.contents()
				.map(function () {
					return $(this).text();
				})
				.get()
				.join(' ')
				.replace(/\s\s+/g, ' ')
				.trim();
		}

		/**
		 * Get a simple formatted event summary.
		 *
		 * @param {array} data - Array with event data to format.
		 *
		 * @since 5.6.0
		 */
		getEventSummary(data) {
			const { date, username, email, message } = data;

			const formattedText = `${date} - ${username} (${email}) - ${message}`;

			return formattedText;
		}

		/**
		 * Get the event user information from the correct cell, used for event summary information.
		 *
		 * @param {jQuery} row - The jQuery object with the event row information we need.
		 *
		 * @returns {object} - The user data object with username and email.
		 *
		 * @since 5.6.0
		 */
		getUserData(row) {
			const searchUser = row.find('.user.column-user .search-user');
			const username = searchUser.data('user');
			const email = searchUser.data('user-email');

			return { username, email };
		}

		/**
		 * Get the event message from the correct cell, used for event summary information.
		 *
		 * @param {jQuery} row - The jQuery object with the event row information we need.
		 *
		 * @returns {string} - The cleaned event message text.
		 *
		 * @since 5.6.0
		 */
		getEventMessage(row) {
			const $mesg = row.find('.mesg.column-mesg');

			if (!$mesg.length) {
				return '';
			}

			const rawHtml = $mesg.html();

			if (!rawHtml) {
				return '';
			}

			const messageRowText = $(
				rawHtml
					.replace(/<a\b[^>]*>(.*?)<\/a>/gi, '')
					.replace(/<br\s*\/?>/gi, ' | ')
			)
				.text()
				.replace(/\s*\|\s*$/, '')
				.trim();

			return messageRowText;
		}

		getEventDate(row) {
			const dateRowText = this.getTextWithSpaces(row.find('.crtd.column-crtd'));
			const formattedDate = this.formatDateString(dateRowText);

			return formattedDate;
		}

		/**
		 * Format event details object into a readable string, ready to be copied to clipboard.
		 *
		 * @param {object} details - The event details object.
		 * @param {string} indent - The current indentation level, used for nested objects.
		 *
		 * @since 5.6.0
		 */
		formatDetails(details, indent = '') {
			let detailsString = '';
			for (const [key, value] of Object.entries(details)) {
				if (typeof value === 'object' && value !== null) {
					detailsString += `${indent}${key}:\n`;
					detailsString += this.formatDetails(value, indent + '  ');
				} else {
					detailsString += `${indent}${key}: "${value}"\n`;
				}
			}
			return detailsString;
		}

		/**
		 * Fetch full event details via AJAX.
		 *
		 * @param {number} event_id - The ID of the event to fetch.
		 * @param {function} callback - The callback function to execute with the details string.
		 *
		 * @since 5.6.0
		 */
		getEventDetails(event_id, callback) {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'get_event_details',
					nonce: wsalAuditLogArgs.viewerNonce,
					event_db_id: event_id
				},

				success: (response) => {
					if (response.success) {
						const details = response.data;

						let detailsString = '';
						if (
							details &&
							typeof details === 'object' &&
							!Array.isArray(details)
						) {
							detailsString = '\n\n' + this.formatDetails(details);
						}

						if (typeof callback === 'function') {
							callback(detailsString);
						}
					}
				},
				error: function (xhr, status, error) {
					console.error('AJAX Error:', error);
				}
			});
		}

		/**
		 * Show a success success icon after user clicked on a copy button.
		 *
		 * @param {jQuery} $button
		 *
		 * @since 5.6.0
		 */
		showCopiedIcon($button) {
			const successIcon = 'dashicons-yes';
			const timeout = 2000;
			const $icon = $button.find('span.dashicons');
			if ($icon.length) {
				$icon.addClass(successIcon);
				setTimeout(() => {
					$icon.removeClass(successIcon);
				}, timeout);
			}
		}

		bindCopyButtons() {
			const self = this;

			this.$eventRow.each(function () {
				const row = $(this);

				const simpleCopyButton = row.find(
					'.wsal-copy-event-data-simple-button'
				);

				const extendedCopyButton = row.find(
					'.wsal-copy-event-data-extended-button'
				);

				if (!simpleCopyButton.length && !extendedCopyButton.length) {
					return;
				}

				const loadingSpinner = extendedCopyButton.find('.wsal-copy-spinner');

				const event_id = extendedCopyButton.attr('event-id');

				const { username, email } = self.getUserData(row);

				const messageRowText = self.getEventMessage(row);

				const formattedDate = self.getEventDate(row);

				const copyData = {
					date: formattedDate,
					username: username,
					email: email,
					message: messageRowText
				};

				const eventSummary = self.getEventSummary(copyData);

				simpleCopyButton.on('click', function (e) {
					e.preventDefault();
					navigator.clipboard.writeText(eventSummary);
					self.showCopiedIcon($(this));
				});

				extendedCopyButton.on('click', function (e) {
					e.preventDefault();

					const $button = $(this);
					const $ext_copy_icon = $button.find('span.dashicons');

					$ext_copy_icon.hide();
					loadingSpinner.show();

					self.getEventDetails(event_id, function (detailsString) {
						const fullEventDetails = eventSummary + detailsString;

						if (!document.hasFocus()) {
							loadingSpinner.hide();
							$ext_copy_icon.show();
							return;
						}

						navigator.clipboard
							.writeText(fullEventDetails)
							.then(function () {
								self.showCopiedIcon($button);
							})
							.catch(function (err) {
								// For the moment let's not show errors here regarding this copy to clipboard event.
							})
							.finally(function () {
								loadingSpinner.hide();
								$ext_copy_icon.show();
							});
					});
				});
			});
		}
	}

	$(document).ready(function () {
		new WSALCopyEventData();
	});
})(jQuery);
