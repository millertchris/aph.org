/**
 * JavaScript for Event Notes functionality in wp-admin
 *
 * @since 5.5.0
 */
(function ($) {
	class WSALEventNotesModal {
		// DOM Selectors
		$addNoteModal = $('#wsal-event-notes');

		$form = $('#wsal-note-form');
		$saveNoteBtn = $('#wsal-save-note-btn');
		$editNoteBtn = $('#wsal-edit-note-btn');
		$deleteNoteBtn = $('#wsal-delete-note-btn');
		$responseNotice = $('[data-response-notice]');

		// State
		currentEventId = null;

		constructor() {
			this.getTranslations();
			this.initDialog();
			this.bindEvents();

			$('.wsal-add-note').darkTooltip({
				animation: 'fadeIn',
				gravity: 'east',
				size: 'medium',
			});
		}

		getTranslations() {
			const { __ } = wp.i18n;

			this.btnText = {
				save: __('Save Note', 'wp-security-audit-log'),
				saving: __('Saving...', 'wp-security-audit-log'),
				delete: __('Delete', 'wp-security-audit-log'),
				deleting: __('Deleting...', 'wp-security-audit-log'),
				cancel: __('Cancel', 'wp-security-audit-log'),
				addNote: __('Add Note', 'wp-security-audit-log'),
				editNote: __('Edit Note', 'wp-security-audit-log'),
			};

			this.content = {
				modalTitle: __('Event Note', 'wp-security-audit-log'),
				noteSaved: __('Note saved successfully.', 'wp-security-audit-log'),
				noteDeleted: __('Note deleted successfully.', 'wp-security-audit-log'),
				genericError: __(
					'An error occurred. Please try again.',
					'wp-security-audit-log'
				),
				savingError: __(
					'An error occurred while saving the note. Please try again.',
					'wp-security-audit-log'
				),
				deleteError: __(
					'An error occurred while deleting the note. Please try again.',
					'wp-security-audit-log'
				),
				networkError: __(
					'A network error occurred. Please check your connection and try again.',
					'wp-security-audit-log'
				),
				deleteConfirm: __(
					'Are you sure you want to delete this note?',
					'wp-security-audit-log'
				),
				tooltipForAdd: __('Add a note to this event', 'wp-security-audit-log'),
				tooltipForEdit: __(
					'This event has a note. Click to view or edit.',
					'wp-security-audit-log'
				),
			};
		}

		initDialog() {
			this.$addNoteModal.dialog({
				title: this.content.modalTitle,
				autoOpen: false,
				draggable: false,
				width: 'auto',
				modal: true,
				resizable: false,
				closeOnEscape: true,
				minHeight: 'auto',
				height: 'auto',
				position: {
					my: 'center',
					at: 'center',
					of: window,
				},
				open: () => {
					$('body').css('overflow', 'hidden');
				},
				close: () => {
					$('body').css('overflow', '');
					this.$form[0].reset();
				},
			});

			/**
			 * Close dialog when clicking on the overlay
			 */
			$(document).on(
				'click',
				'.toplevel_page_wsal-auditlog .ui-widget-overlay',
				() => {
					this.$addNoteModal.dialog('close');
				}
			);
		}

		showErrorMessage(message = this.content.genericError) {
			this.$responseNotice.text(message).addClass('wsal-error-notice').show();
		}

		getNoteFromDB(callback) {
			const nonce = $('#wsal-notes-nonce').val();

			// Clear any previous error notices.
			this.$responseNotice.text('').removeClass('wsal-error-notice');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wsal_get_note_of_event',
					nonce: nonce,
					event_db_id: this.currentEventId,
				},
				success: (data) => {
					if (!data.success) {
						callback(data.data.note || '');

						/**
						 * Edit modal while showing this error, prevent user from overwriting their note in this context
						 */
						$('#wsal-note-text').prop('disabled', true);
						$('#wsal-note-text').css('display', 'none');
						$('.wsal-event-notes-actions').css('display', 'none');
						$('.wsal-event-notes-actions + small').css('display', 'none');

						const errorMsg = data.data || this.content.genericError;
						this.showErrorMessage(errorMsg);
						return;
					}

					if (typeof callback === 'function') {
						callback(data.data.note || '');
					}
				},
				error: () => {
					if (typeof callback === 'function') {
						callback('');
					}
				},
			});
		}

		bindEvents() {
			// Open modal on .wsal-add-note click
			$('#the-list tr .wsal-add-note').on('click', (e) => {
				e.preventDefault();

				// Return true if the element has the attribute 'data-has-db-note'
				const hasNote = $(e.currentTarget).is('[data-has-db-note]');

				this.currentEventId = $(e.currentTarget).data('event-db-id') || null;

				if (hasNote) {
					this.getNoteFromDB(
						function (note) {
							$('#wsal-note-text').val(note);
							this.$addNoteModal.dialog('open');
							this.$deleteNoteBtn.css('display', '');
						}.bind(this)
					);
				} else {
					this.$addNoteModal.dialog('open');
				}
			});

			this.$saveNoteBtn.on('click', (e) => {
				e.preventDefault();
				this.saveNote();
			});

			this.$deleteNoteBtn.on('click', (e) => {
				e.preventDefault();
				this.deleteNote();
			});
		}

		/**
		 * Shows a loading state on a button by animating its opacity, disabling it, and changing its text.
		 *
		 * @param {string} selector - jQuery selector for the button element
		 * @param {string} loadingText - Text to display on the button during loading state
		 * @param {string} status - 'start' to initiate loading state, 'stop' to end it
		 *
		 * @since 5.5.0
		 */
		loadingStateToggle(selector, loadingText, status) {
			let intervalId;
			const $el = $(selector);

			return new Promise((resolve) => {
				if (status === 'start') {
					let isDim = false;

					$el.prop('disabled', true);
					$el.text(loadingText);

					intervalId = setInterval(() => {
						isDim = !isDim;
						$el.stop(true).animate({ opacity: isDim ? 0.8 : 1 }, 300);
					}, 200);

					$el.data('wsalLoadingInterval', intervalId);
				} else if (status === 'stop') {
					const intervalId = $el.data('wsalLoadingInterval');
					if (intervalId) {
						clearInterval(intervalId);
						$el.removeData('wsalLoadingInterval');
					}

					$el.prop('disabled', false);

					$el.text(loadingText);
					$el.stop(true).animate({ opacity: 1 }, 300);

					resolve();
				}
			});
		}

		deleteNote() {
			if (confirm(this.content.deleteConfirm)) {
				this.deleteNoteInDb();
			}
		}

		deleteNoteInDb() {
			const nonce = $('#wsal-notes-nonce').val();

			this.loadingStateToggle(
				this.$deleteNoteBtn,
				this.btnText.deleting,
				'start'
			);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wsal_delete_note_of_event',
					nonce: nonce,
					event_db_id: this.currentEventId,
				},
				success: (data) => {
					if (!data.success) {
						const errorMsg = data.data || this.content.deleteError;
						this.showErrorMessage(errorMsg);
						return;
					}

					this.$responseNotice
						.text(this.content.noteDeleted)
						.addClass('wsal-success-notice')
						.delay(1000)
						.fadeOut(300, () => {
							this.$responseNotice
								.removeClass('wsal-success-notice')
								.text('')
								.show();
						});

					setTimeout(() => {
						this.$addNoteModal.dialog('close');
						this.$form[0].reset();

						const $thisNoteButton = $(
							`#the-list tr .wsal-add-note[data-event-db-id="${this.currentEventId}"]`
						);

						// Add data-has-db-note attribute to the .wsal-add-note button
						$thisNoteButton.removeAttr('data-has-db-note');

						// Update icon to indicate that a note now exists.
						$thisNoteButton
							.find('.wsal-note-icon')
							.removeClass('dashicons-edit')
							.addClass('dashicons-plus');

						// Update button text to "Add Note" - not using .text() to preserve the icon span
						$thisNoteButton
							.contents()
							.filter(function () {
								return this.nodeType === 3;
							})
							.replaceWith(this.btnText.addNote);

						// Update tooltip to indicate that a note now exists. data-darktooltip
						$thisNoteButton.attr(
							'data-darktooltip',
							this.content.tooltipForAdd
						);

						// Re-initialize darkTooltip to update the tooltip content
						$thisNoteButton.darkTooltip({
							animation: 'fadeIn',
							gravity: 'east',
							size: 'medium',
						});
					}, 1500); // 1.5 seconds delay to allow users to see the success message
				},
				error: () => {
					this.showErrorMessage(this.content.networkError);
				},

				complete: () => {
					this.loadingStateToggle(
						this.$deleteNoteBtn,
						this.btnText.delete,
						'stop'
					);
				},
			});
		}

		saveNote() {
			const note = $('#wsal-note-text').val();
			const nonce = $('#wsal-notes-nonce').val();

			this.loadingStateToggle(this.$saveNoteBtn, this.btnText.saving, 'start');

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wsal_save_note_to_event',
					note: note,
					nonce: nonce,
					event_db_id: this.currentEventId,
				},
				success: (data) => {
					if (!data.success) {
						const errorMsg = data.data || this.content.savingError;
						this.showErrorMessage(errorMsg);
						return;
					}

					this.$responseNotice
						.text(this.content.noteSaved)
						.addClass('wsal-success-notice')
						.delay(1000)
						.fadeOut(300, () => {
							this.$responseNotice
								.removeClass('wsal-success-notice')
								.text('')
								.show();
						});

					// Show the delete button after saving a note.
					this.$deleteNoteBtn.css('display', '');

					setTimeout(() => {
						this.$addNoteModal.dialog('close');
						this.$form[0].reset();

						const $thisNoteButton = $(
							`#the-list tr .wsal-add-note[data-event-db-id="${this.currentEventId}"]`
						);

						// Add data-has-db-note attribute to the .wsal-add-note button
						$thisNoteButton.attr('data-has-db-note', '');

						// Update icon to indicate that a note now exists.
						$thisNoteButton
							.find('.wsal-note-icon')
							.removeClass('dashicons-plus')
							.addClass('dashicons-edit');

						// Update button text to "Edit Note" - not using .text() to preserve the icon span
						$thisNoteButton
							.contents()
							.filter(function () {
								return this.nodeType === 3;
							})
							.replaceWith(this.btnText.editNote);

						// Update tooltip to indicate that a note now exists. data-darktooltip
						$thisNoteButton.attr(
							'data-darktooltip',
							this.content.tooltipForEdit
						);

						// Re-initialize darkTooltip to update the tooltip content
						$thisNoteButton.darkTooltip({
							animation: 'fadeIn',
							gravity: 'east',
							size: 'medium',
						});
					}, 1500); // 1.5 seconds delay to allow users to see the success message
				},
				error: () => {
					this.showErrorMessage(this.content.networkError);
				},
				complete: () => {
					this.loadingStateToggle(this.$saveNoteBtn, this.btnText.save, 'stop');
				},
			});
		}
	}

	$(document).ready(function () {
		new WSALEventNotesModal();
	});
})(jQuery);
