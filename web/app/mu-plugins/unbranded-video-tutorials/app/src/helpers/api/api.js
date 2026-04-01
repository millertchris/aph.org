/**
 * API related helper functions for admin.
 *
 * @since 1.8.0
 * @author Joel James <joel@incsub.com>
 *
 * Copyright 2007-2019 Incsub (http://incsub.com).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import apiFetch from '@wordpress/api-fetch'

// Global Variables
const {
	rest: { nonce, base },
} = window.ivtVars ?? {}

// Setup middlewares.
apiFetch.use(apiFetch.createNonceMiddleware(nonce))
apiFetch.use(apiFetch.createRootURLMiddleware(base))

/**
 * Send API rest GET request using apiFetch.
 *
 * This is a wrapper function to include nonce and
 * our custom route base url.
 *
 * @param {object} options apiFetch options.
 *
 * @since 1.8.0
 *
 * @return {Promise}
 **/
export function restGet(options) {
	options = options || {}

	options.method = 'GET'

	// Add param support.
	if (options.params) {
		const urlParams = new URLSearchParams(Object.entries(options.params))

		options.path = options.path + '?' + urlParams
	}

	return apiFetch(options)
}

/**
 * Send API rest POST request using apiFetch.
 *
 * @param {object} options apiFetch options.
 *
 * @since 1.8.0
 *
 * @return {Promise}
 **/
export function restPost(options) {
	options = options || {}

	options.method = 'POST'

	return apiFetch(options)
}

/**
 * Send API rest DELETE request using apiFetch.
 *
 * @param {object} options apiFetch options.
 *
 * @since 1.8.0
 *
 * @return {Promise}
 **/
export function restDelete(options) {
	options = options || {}

	options.method = 'DELETE'

	return apiFetch(options)
}
