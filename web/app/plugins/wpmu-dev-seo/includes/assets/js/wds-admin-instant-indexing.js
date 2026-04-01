(function ($) {
	window.Wds = window.Wds || {};

	const init = () => {
		window.Wds.hook_conditionals();
		window.Wds.hook_toggleables();
		window.Wds.media_item_selector($('#organization_logo'));
		window.Wds.vertical_tabs();

		document.addEventListener('click', async (e) => {
			if (e.target.matches('.wds-activate-instant-indexing-component')) {
				await changeInstantIndexingStatus(e, 1);
			}
			if (
				e.target.closest('.wds-deactivate-instant-indexing-component')
			) {
				await changeInstantIndexingStatus(e, 0);
			}

			if (e.target.matches('.wds-indexnow-generate-key')) {
				await generateIndexNowKey(e);
			}

			if (e.target.matches('.wds-indexnow-submit-urls')) {
				await submitUrls(e);
			}

			if (e.target.matches('.wds-submission-pagination')) {
				e.preventDefault();
				const perPage =
					document.querySelector('.wds-indexing-per-page')?.value ||
					10;
				await fetchSubmissionHistory(e.target.dataset.page, perPage);
			}
			if (e.target.matches('#wds-clear-submissions')) {
				e.preventDefault();
				await clearSubmissions();
			}
			if (e.target.matches('.wds-submit-urls')) {
				e.preventDefault();
				document
					.querySelector('[data-target="tab_submit_url"]')
					.click();
			}
		});

		document.addEventListener('keyup', (e) => {
			if (e.target.matches('#wds-indexnow-urls')) {
				validateInput(e.target);
			}
		});
		$('.wds-indexing-per-page').on('change', function () {
			fetchSubmissionHistory(1, $(this).val()).then((r) =>
				console.log(r)
			);
		});
	};

	const ajaxRequest = async (action, data) => {
		try {
			const response = await fetch(ajaxurl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action,
					_wds_nonce: _wds_instant_indexing.nonce,
					...data,
				}),
			});

			return await response.json();
		} catch (error) {
			console.error(`Error with action ${action}:`, error);
			throw error;
		}
	};

	const validateInput = (textarea) => {
		const urls = textarea.value.trim();
		const formField = textarea.closest('.sui-form-field');
		let errorMessageContainer =
			formField.querySelector('.sui-error-message');

		const error = validateUrls(urls);

		if (error) {
			textarea.classList.add('sui-form-control-error');
			formField.classList.add('sui-form-field-error');
			if (!errorMessageContainer) {
				errorMessageContainer = document.createElement('p');
				errorMessageContainer.className = 'sui-error-message';
				formField.appendChild(errorMessageContainer);
			}
			errorMessageContainer.textContent = `Error: ${error}`;
		} else {
			textarea.classList.remove('sui-form-control-error');
			formField.classList.remove('sui-form-field-error');
			if (errorMessageContainer) {
				errorMessageContainer.remove();
			}
		}
	};

	const validateUrls = (urls) => {
		if (!urls) return Wds.l10n('instant_indexing', 'empty_url');

		const urlPattern = /^(https?:\/\/[^\s$.?#].[^\s]*)$/i;
		const urlArray = urls.split('\n').filter(Boolean);

		if (urlArray.length > 100)
			return Wds.l10n('instant_indexing', 'limit');

		const invalidUrls = urlArray.filter(
			(url) => !urlPattern.test(url.trim())
		);

		if (invalidUrls.length > 0)
			return Wds.l10n('instant_indexing', 'invalid');

		return '';
	};

	const submitUrls = async (e) => {
		e.preventDefault();
		e.stopPropagation();

		const submitButton = e.target;
		const textarea = document.querySelector('#wds-indexnow-urls');
		const urls = textarea.value.trim();
		const validationError = validateUrls(urls);

		if (validationError) {
			validateInput(textarea);
			return;
		}
		const urlArr = urls
			.split('\n')
			.map((url) => url.trim())
			.filter((url) => url !== '');

		submitButton.classList.add('sui-button-onload-text');

		try {
			const response = await fetch(_wds_instant_indexing.rest_api, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': _wds_instant_indexing.rest_nonce,
				},
				body: JSON.stringify({ urls: urlArr }),
			});

			const result = await response.json();
			if (result.success) {
				Wds.show_floating_message(
					'wds-url-manually-updated',
					result.message,
					result.status
				);
				textarea.value = '';
				submitButton.classList.remove('sui-button-onload-text');
			} else {
				const errorMessage =
					result.message ||
					result.data?.message ||
					Wds.l10n('instant_indexing', 'wrong');

				if (errorMessage.includes('Rate limit exceeded')) {
					showError(
						textarea,
						Wds.l10n('instant_indexing', 'rate_limit')
					);
				} else {
					showError(textarea, errorMessage);
				}
				submitButton.classList.remove('sui-button-onload-text');
			}
		} catch (error) {
			showError(textarea, Wds.l10n('instant_indexing', 'wrong'));
			submitButton.classList.remove('sui-button-onload-text');
		}
	};

	const showError = (textarea, message) => {
		const formField = textarea.closest('.sui-form-field');
		let errorMessageContainer =
			formField.querySelector('.sui-error-message');

		if (!errorMessageContainer) {
			errorMessageContainer = document.createElement('div');
			errorMessageContainer.className = 'sui-error-message';
			formField.appendChild(errorMessageContainer);
		}

		textarea.classList.add('sui-form-control-error');
		formField.classList.add('sui-form-field-error');
		errorMessageContainer.textContent = `Error: ${message}`;
	};

	const changeInstantIndexingStatus = async (e, status) => {
		e.preventDefault();
		e.stopPropagation();

		const button = e.target;
		button.classList.add('disabled');

		try {
			await ajaxRequest('wds_change_instant_indexing_status', { status });
			// Redirect to Settings tab after activation
			if (status === 1) {
				const url = new URL(window.location.href);
				url.searchParams.set('tab', 'tab_settings');
				window.location.href = url.toString();
			} else {
				window.location.reload();
			}
		} catch (error) {
			console.error('Error changing Instant Indexing status:', error);
		}
	};

	const generateIndexNowKey = async (e) => {
		e.preventDefault();
		e.stopPropagation();

		try {
			const result = await ajaxRequest('wds_generate_indexnow_key', {});

			if (result.success) {
				document.querySelector('.smartcrawl-indexnow-key').value =
					result.data.api_key;
				document.querySelector(
					'.smartcrawl-indexnow-key-location'
				).value = result.data.api_key_location;
			}
		} catch (error) {
			console.error('Error generating IndexNow key:', error);
		}
	};
	const fetchSubmissionHistory = async (page, perPage) => {
		try {
			const result = await ajaxRequest('wds_fetch_submission_history', {
				page,
				results_per_page: perPage,
			});

			if (result.success) {
				document.querySelector('.wds-submission-history').innerHTML =
					result.data.html;
				document.querySelector('.sui-pagination').innerHTML =
					result.data.pagination;
			}
		} catch (error) {
			console.error('Error fetching submission history:', error);
		}
	};
	const clearSubmissions = async () => {
		try {
			const result = await ajaxRequest('wds_clear_submissions', {});

			if (result.success) {
				window.location.reload();
			}
		} catch (error) {
			console.error('Error clearing submissions:', error);
		}
	};
	$(init);
})(jQuery);
