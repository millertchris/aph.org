/**
 * Assets helper functions for admin.
 *
 * @since 1.8.0
 * @author Joel James <joel@incsub.com>
 *
 * Beehive, Copyright 2007-2019 Incsub (http://incsub.com).
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

/**
 * Get the full image url for admin.
 *
 * @param path Image name.
 *
 * @since 1.8.0
 *
 * @return {string | string}
 */
export function imageUrl(path) {
	const ivtUrl = window.ivtVars.urls.base

	return ivtUrl + 'app/assets/img/' + path
}

/**
 * Check if current member's membership is valid.
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function validMember() {
	return window.ivtVars.membership.valid > 0
}

/**
 * Check if current member's status if expired.
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function expiredMember() {
	return 'expired' === window.ivtVars.membership.status
}

/**
 * Check if Dash plugin is active.
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function dashActive() {
	return window.ivtVars.membership.dash_active > 0
}

/**
 * Check if Dash plugin is installed.
 *
 * @since 1.8.4
 *
 * @return {boolean}
 */
export function dashInstalled() {
	return window.ivtVars.membership.dash_installed > 0
}

/**
 * Check if Dash plugin is connected.
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function dashConnected() {
	return window.ivtVars.membership.dash_connected > 0
}

/**
 * Check if current environment is network admin.
 *
 * We will use the localized var from PHP.
 *
 * @since 1.8.6
 *
 * @return {boolean}
 */
export function isNetwork() {
	return window.ivtVars.flags.network > 0
}

/**
 * Check if branding is hidden.
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function hideBranding() {
	return isBool( window.ivtVars.whitelabel.hide_branding ) ? window.ivtVars.whitelabel.hide_branding : true;
}

/**
 * Check unbranded ( if branding is active but no custom logo set ).
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function isUnbranded() {
	return isBool( window.ivtVars.whitelabel.is_unbranded ) ? window.ivtVars.whitelabel.is_unbranded : true;
}

/**
 * Check White Label is activated (and custom logo set) in Dashboard plugin.
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
export function isRebranded() {
	return isBool( window.ivtVars.whitelabel.is_rebranded ) ? window.ivtVars.whitelabel.is_rebranded : true;
}

/**
 * Get the custom image url set in white branding option of Dashboard plugin.
 *
 * @since 1.8.0
 *
 * @return {string}
 */
export function customImageUrl() {
	return ! isEmpty( window.ivtVars.whitelabel.custom_image ) ? window.ivtVars.whitelabel.custom_image : '';
}

/**
 * Check if input is empty/null/false. Similar to PHP's empty().
 *
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
const isEmpty = (input) => {
	// Check if input is a boolean. If so, then if input value is true it is not considered empty, but if the input is false then it is considered empty.
	if (isBool(input)) {
		return ! input;
	}

	// If input is undefined or null, it is considered empty
	if (typeof input === 'undefined' || input === null) {
		return true;
	}

	// If input has a length property (e.g. strings, arrays) but the length is 0, then input is considered empty.
	// Note that objects do not have a length property. Check that case in next condition.
	if (typeof input.length !== 'undefined') {
		return input.length === 0;
	}

	// If input is an object and if it has no enumerable properties it is considered empty.
	if (typeof input === 'object') {
		return Object.keys(input).length === 0;
	}

	// For any other case the input is not considered empty.
	return false;
};

/**
 * Check if input is boolean.
 *
 *
 * @since 1.8.0
 *
 * @return {boolean}
 */
const isBool = (input)  => {
	return typeof input == "boolean";
}
