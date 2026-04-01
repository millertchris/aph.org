<?php
/**
 * Build-in notification settings of the plugin
 *
 * @package wsal
 *
 * @since 5.1.1
 */

use WSAL\Helpers\WP_Helper;
use WSAL\Views\Notifications;
use WSAL\Helpers\Settings_Helper;
use WSAL\Helpers\Settings\Settings_Builder;
use WSAL\Extensions\Helpers\Notification_Helper;

$built_in_notifications = (array) Settings_Helper::get_option_value( Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME, array() );

$defaults = '';
if ( Notifications::is_default_mail_set() ) {
	$current_default_mail = Notifications::get_default_mail();
	$defaults            .= esc_html__( ' Currently default email is set to: ', 'wp-security-audit-log' ) . $current_default_mail;
} else {
	$defaults .= Notification_Helper::no_default_email_is_set();
}

if ( Notifications::is_default_twilio_set() ) {
	$current_default_twilio = Notifications::get_default_twilio();
	$defaults              .= esc_html__( ' Currently default phone is set to: ', 'wp-security-audit-log' ) . $current_default_twilio;
} else {
	$defaults .= Notification_Helper::no_default_phone_is_set();
}

if ( Notifications::is_default_slack_set() ) {
	$current_default_twilio = Notifications::get_default_slack();
	$defaults              .= esc_html__( ' Currently default slack channel is set to: ', 'wp-security-audit-log' ) . $current_default_twilio;
} else {
	$defaults .= Notification_Helper::no_default_slack_is_set();
}

$notifications = array();
foreach ( $built_in_notifications as $name => $value ) {
	$notifications[ 'notification_' . $name ] = $value;
}
unset( $built_in_notifications );

Settings_Builder::set_current_options( array_merge( $notifications, Notifications::get_global_notifications_setting() ) );

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Activity log highlights', 'wp-security-audit-log' ),
		'id'            => 'built-in-notification-settings-tab',
		'type'          => 'tab-title',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);


$settings_url = \add_query_arg(
	array(
		'page' => Notifications::get_safe_view_name(),
	),
	\network_admin_url( 'admin.php' )
) . '#wsal-options-tab-notification-settings';

// phpcs:disable
/* @premium:start */
// phpcs:enable

if ( ! Settings_Helper::get_boolean_option_value( 'notification-modal-dismissed', false ) ) {
	?>
	<script>
		!(function(root, factory) {
		if (typeof define === 'function' && define.amd) {
		define(['jquery'], function($) {
			return factory(root, $);
		});
		} else if (typeof exports === 'object') {
		factory(root, require('jquery'));
		} else {
		factory(root, root.jQuery || root.Zepto);
		}
	})(this, function(global, $) {

		'use strict';

		/**
		* Name of the plugin
		* @private
		* @const
		* @type {String}
		*/
		var PLUGIN_NAME = 'remodal';

		/**
		* Namespace for CSS and events
		* @private
		* @const
		* @type {String}
		*/
		var NAMESPACE = global.REMODAL_GLOBALS && global.REMODAL_GLOBALS.NAMESPACE || PLUGIN_NAME;

		/**
		* Animationstart event with vendor prefixes
		* @private
		* @const
		* @type {String}
		*/
		var ANIMATIONSTART_EVENTS = $.map(
		['animationstart', 'webkitAnimationStart', 'MSAnimationStart', 'oAnimationStart'],

		function(eventName) {
			return eventName + '.' + NAMESPACE;
		}

		).join(' ');

		/**
		* Animationend event with vendor prefixes
		* @private
		* @const
		* @type {String}
		*/
		var ANIMATIONEND_EVENTS = $.map(
		['animationend', 'webkitAnimationEnd', 'MSAnimationEnd', 'oAnimationEnd'],

		function(eventName) {
			return eventName + '.' + NAMESPACE;
		}

		).join(' ');

		/**
		* Default settings
		* @private
		* @const
		* @type {Object}
		*/
		var DEFAULTS = $.extend({
		hashTracking: true,
		closeOnConfirm: true,
		closeOnCancel: true,
		closeOnEscape: true,
		closeOnOutsideClick: true,
		modifier: '',
		appendTo: null
		}, global.REMODAL_GLOBALS && global.REMODAL_GLOBALS.DEFAULTS);

		/**
		* States of the Remodal
		* @private
		* @const
		* @enum {String}
		*/
		var STATES = {
		CLOSING: 'closing',
		CLOSED: 'closed',
		OPENING: 'opening',
		OPENED: 'opened'
		};

		/**
		* Reasons of the state change.
		* @private
		* @const
		* @enum {String}
		*/
		var STATE_CHANGE_REASONS = {
		CONFIRMATION: 'confirmation',
		CANCELLATION: 'cancellation'
		};

		/**
		* Is animation supported?
		* @private
		* @const
		* @type {Boolean}
		*/
		var IS_ANIMATION = (function() {
		var style = document.createElement('div').style;

		return style.animationName !== undefined ||
			style.WebkitAnimationName !== undefined ||
			style.MozAnimationName !== undefined ||
			style.msAnimationName !== undefined ||
			style.OAnimationName !== undefined;
		})();

		/**
		* Is iOS?
		* @private
		* @const
		* @type {Boolean}
		*/
		var IS_IOS = /iPad|iPhone|iPod/.test(navigator.platform);

		/**
		* Current modal
		* @private
		* @type {Remodal}
		*/
		var current;

		/**
		* Scrollbar position
		* @private
		* @type {Number}
		*/
		var scrollTop;

		/**
		* Returns an animation duration
		* @private
		* @param {jQuery} $elem
		* @returns {Number}
		*/
		function getAnimationDuration($elem) {
		if (
			IS_ANIMATION &&
			$elem.css('animation-name') === 'none' &&
			$elem.css('-webkit-animation-name') === 'none' &&
			$elem.css('-moz-animation-name') === 'none' &&
			$elem.css('-o-animation-name') === 'none' &&
			$elem.css('-ms-animation-name') === 'none'
		) {
			return 0;
		}

		var duration = $elem.css('animation-duration') ||
			$elem.css('-webkit-animation-duration') ||
			$elem.css('-moz-animation-duration') ||
			$elem.css('-o-animation-duration') ||
			$elem.css('-ms-animation-duration') ||
			'0s';

		var delay = $elem.css('animation-delay') ||
			$elem.css('-webkit-animation-delay') ||
			$elem.css('-moz-animation-delay') ||
			$elem.css('-o-animation-delay') ||
			$elem.css('-ms-animation-delay') ||
			'0s';

		var iterationCount = $elem.css('animation-iteration-count') ||
			$elem.css('-webkit-animation-iteration-count') ||
			$elem.css('-moz-animation-iteration-count') ||
			$elem.css('-o-animation-iteration-count') ||
			$elem.css('-ms-animation-iteration-count') ||
			'1';

		var max;
		var len;
		var num;
		var i;

		duration = duration.split(', ');
		delay = delay.split(', ');
		iterationCount = iterationCount.split(', ');

		// The 'duration' size is the same as the 'delay' size
		for (i = 0, len = duration.length, max = Number.NEGATIVE_INFINITY; i < len; i++) {
			num = parseFloat(duration[i]) * parseInt(iterationCount[i], 10) + parseFloat(delay[i]);

			if (num > max) {
			max = num;
			}
		}

		return max;
		}

		/**
		* Returns a scrollbar width
		* @private
		* @returns {Number}
		*/
		function getScrollbarWidth() {
		if ($(document).height() <= $(window).height()) {
			return 0;
		}

		var outer = document.createElement('div');
		var inner = document.createElement('div');
		var widthNoScroll;
		var widthWithScroll;

		outer.style.visibility = 'hidden';
		outer.style.width = '100px';
		document.body.appendChild(outer);

		widthNoScroll = outer.offsetWidth;

		// Force scrollbars
		outer.style.overflow = 'scroll';

		// Add inner div
		inner.style.width = '100%';
		outer.appendChild(inner);

		widthWithScroll = inner.offsetWidth;

		// Remove divs
		outer.parentNode.removeChild(outer);

		return widthNoScroll - widthWithScroll;
		}

		/**
		* Locks the screen
		* @private
		*/
		function lockScreen() {
		if (IS_IOS) {
			return;
		}

		var $html = $('html');
		var lockedClass = namespacify('is-locked');
		var paddingRight;
		var $body;

		if (!$html.hasClass(lockedClass)) {
			$body = $(document.body);

			// Zepto does not support '-=', '+=' in the `css` method
			paddingRight = parseInt($body.css('padding-right'), 10) + getScrollbarWidth();

			$body.css('padding-right', paddingRight + 'px');
			$html.addClass(lockedClass);
		}
		}

		/**
		* Unlocks the screen
		* @private
		*/
		function unlockScreen() {
		if (IS_IOS) {
			return;
		}

		var $html = $('html');
		var lockedClass = namespacify('is-locked');
		var paddingRight;
		var $body;

		if ($html.hasClass(lockedClass)) {
			$body = $(document.body);

			// Zepto does not support '-=', '+=' in the `css` method
			paddingRight = parseInt($body.css('padding-right'), 10) - getScrollbarWidth();

			$body.css('padding-right', paddingRight + 'px');
			$html.removeClass(lockedClass);
		}
		}

		/**
		* Sets a state for an instance
		* @private
		* @param {Remodal} instance
		* @param {STATES} state
		* @param {Boolean} isSilent If true, Remodal does not trigger events
		* @param {String} Reason of a state change.
		*/
		function setState(instance, state, isSilent, reason) {

		var newState = namespacify('is', state);
		var allStates = [namespacify('is', STATES.CLOSING),
						namespacify('is', STATES.OPENING),
						namespacify('is', STATES.CLOSED),
						namespacify('is', STATES.OPENED)].join(' ');

		instance.$bg
			.removeClass(allStates)
			.addClass(newState);

		instance.$overlay
			.removeClass(allStates)
			.addClass(newState);

		instance.$wrapper
			.removeClass(allStates)
			.addClass(newState);

		instance.$modal
			.removeClass(allStates)
			.addClass(newState);

		instance.state = state;
		!isSilent && instance.$modal.trigger({
			type: state,
			reason: reason
		}, [{ reason: reason }]);
		}

		/**
		* Synchronizes with the animation
		* @param {Function} doBeforeAnimation
		* @param {Function} doAfterAnimation
		* @param {Remodal} instance
		*/
		function syncWithAnimation(doBeforeAnimation, doAfterAnimation, instance) {
		var runningAnimationsCount = 0;

		var handleAnimationStart = function(e) {
			if (e.target !== this) {
			return;
			}

			runningAnimationsCount++;
		};

		var handleAnimationEnd = function(e) {
			if (e.target !== this) {
			return;
			}

			if (--runningAnimationsCount === 0) {

			// Remove event listeners
			$.each(['$bg', '$overlay', '$wrapper', '$modal'], function(index, elemName) {
				instance[elemName].off(ANIMATIONSTART_EVENTS + ' ' + ANIMATIONEND_EVENTS);
			});

			doAfterAnimation();
			}
		};

		$.each(['$bg', '$overlay', '$wrapper', '$modal'], function(index, elemName) {
			instance[elemName]
			.on(ANIMATIONSTART_EVENTS, handleAnimationStart)
			.on(ANIMATIONEND_EVENTS, handleAnimationEnd);
		});

		doBeforeAnimation();

		// If the animation is not supported by a browser or its duration is 0
		if (
			getAnimationDuration(instance.$bg) === 0 &&
			getAnimationDuration(instance.$overlay) === 0 &&
			getAnimationDuration(instance.$wrapper) === 0 &&
			getAnimationDuration(instance.$modal) === 0
		) {

			// Remove event listeners
			$.each(['$bg', '$overlay', '$wrapper', '$modal'], function(index, elemName) {
			instance[elemName].off(ANIMATIONSTART_EVENTS + ' ' + ANIMATIONEND_EVENTS);
			});

			doAfterAnimation();
		}
		}

		/**
		* Closes immediately
		* @private
		* @param {Remodal} instance
		*/
		function halt(instance) {
		if (instance.state === STATES.CLOSED) {
			return;
		}

		$.each(['$bg', '$overlay', '$wrapper', '$modal'], function(index, elemName) {
			instance[elemName].off(ANIMATIONSTART_EVENTS + ' ' + ANIMATIONEND_EVENTS);
		});

		instance.$bg.removeClass(instance.settings.modifier);
		instance.$overlay.removeClass(instance.settings.modifier).hide();
		instance.$wrapper.hide();
		unlockScreen();
		setState(instance, STATES.CLOSED, true);
		}

		/**
		* Parses a string with options
		* @private
		* @param str
		* @returns {Object}
		*/
		function parseOptions(str) {
		var obj = {};
		var arr;
		var len;
		var val;
		var i;

		// Remove spaces before and after delimiters
		str = str.replace(/\s*:\s*/g, ':').replace(/\s*,\s*/g, ',');

		// Parse a string
		arr = str.split(',');
		for (i = 0, len = arr.length; i < len; i++) {
			arr[i] = arr[i].split(':');
			val = arr[i][1];

			// Convert a string value if it is like a boolean
			if (typeof val === 'string' || val instanceof String) {
			val = val === 'true' || (val === 'false' ? false : val);
			}

			// Convert a string value if it is like a number
			if (typeof val === 'string' || val instanceof String) {
			val = !isNaN(val) ? +val : val;
			}

			obj[arr[i][0]] = val;
		}

		return obj;
		}

		/**
		* Generates a string separated by dashes and prefixed with NAMESPACE
		* @private
		* @param {...String}
		* @returns {String}
		*/
		function namespacify() {
		var result = NAMESPACE;

		for (var i = 0; i < arguments.length; ++i) {
			result += '-' + arguments[i];
		}

		return result;
		}

		/**
		* Handles the hashchange event
		* @private
		* @listens hashchange
		*/
		function handleHashChangeEvent() {
		var id = location.hash.replace('#', '');
		var instance;
		var $elem;

		if (!id) {

			// Check if we have currently opened modal and animation was completed
			if (current && current.state === STATES.OPENED && current.settings.hashTracking) {
			current.close();
			}
		} else {

			// Catch syntax error if your hash is bad
			try {
			$elem = $(
				'[data-' + PLUGIN_NAME + '-id="' + id + '"]'
			);
			} catch (err) {}

			if ($elem && $elem.length) {
			instance = $[PLUGIN_NAME].lookup[$elem.data(PLUGIN_NAME)];

			if (instance && instance.settings.hashTracking) {
				instance.open();
			}
			}

		}
		}

		/**
		* Remodal constructor
		* @constructor
		* @param {jQuery} $modal
		* @param {Object} options
		*/
		function Remodal($modal, options) {
		var $body = $(document.body);
		var $appendTo = $body;
		var remodal = this;

		remodal.settings = $.extend({}, DEFAULTS, options);
		remodal.index = $[PLUGIN_NAME].lookup.push(remodal) - 1;
		remodal.state = STATES.CLOSED;

		remodal.$overlay = $('.' + namespacify('overlay'));

		if (remodal.settings.appendTo !== null && remodal.settings.appendTo.length) {
			$appendTo = $(remodal.settings.appendTo);
		}

		if (!remodal.$overlay.length) {
			remodal.$overlay = $('<div>').addClass(namespacify('overlay') + ' ' + namespacify('is', STATES.CLOSED)).hide();
			$appendTo.append(remodal.$overlay);
		}

		remodal.$bg = $('.' + namespacify('bg')).addClass(namespacify('is', STATES.CLOSED));

		remodal.$modal = $modal
			.addClass(
			NAMESPACE + ' ' +
			namespacify('is-initialized') + ' ' +
			remodal.settings.modifier + ' ' +
			namespacify('is', STATES.CLOSED))
			.attr('tabindex', '-1');

		remodal.$wrapper = $('<div>')
			.addClass(
			namespacify('wrapper') + ' ' +
			remodal.settings.modifier + ' ' +
			namespacify('is', STATES.CLOSED))
			.hide()
			.append(remodal.$modal);
		$appendTo.append(remodal.$wrapper);

		// Add the event listener for the close button
		remodal.$wrapper.on('click.' + NAMESPACE, '[data-' + PLUGIN_NAME + '-action="close"]', function(e) {
			e.preventDefault();

			remodal.close();
		});

		// Add the event listener for the cancel button
		remodal.$wrapper.on('click.' + NAMESPACE, '[data-' + PLUGIN_NAME + '-action="cancel"]', function(e) {
			e.preventDefault();

			remodal.$modal.trigger(STATE_CHANGE_REASONS.CANCELLATION);

			if (remodal.settings.closeOnCancel) {
			remodal.close(STATE_CHANGE_REASONS.CANCELLATION);
			}
		});

		// Add the event listener for the confirm button
		remodal.$wrapper.on('click.' + NAMESPACE, '[data-' + PLUGIN_NAME + '-action="confirm"]', function(e) {
			e.preventDefault();

			remodal.$modal.trigger(STATE_CHANGE_REASONS.CONFIRMATION);

			if (remodal.settings.closeOnConfirm) {
			remodal.close(STATE_CHANGE_REASONS.CONFIRMATION);
			}
		});

		// Add the event listener for the overlay
		remodal.$wrapper.on('click.' + NAMESPACE, function(e) {
			var $target = $(e.target);

			if (!$target.hasClass(namespacify('wrapper'))) {
			return;
			}

			if (remodal.settings.closeOnOutsideClick) {
			remodal.close();
			}
		});
		}

		/**
		* Opens a modal window
		* @public
		*/
		Remodal.prototype.open = function() {
		var remodal = this;
		var id;

		// Check if the animation was completed
		if (remodal.state === STATES.OPENING || remodal.state === STATES.CLOSING) {
			return;
		}

		id = remodal.$modal.attr('data-' + PLUGIN_NAME + '-id');

		if (id && remodal.settings.hashTracking) {
			scrollTop = $(window).scrollTop();
			location.hash = id;
		}

		if (current && current !== remodal) {
			halt(current);
		}

		current = remodal;
		lockScreen();
		remodal.$bg.addClass(remodal.settings.modifier);
		remodal.$overlay.addClass(remodal.settings.modifier).show();
		remodal.$wrapper.show().scrollTop(0);
		remodal.$modal.focus();

		syncWithAnimation(
			function() {
			setState(remodal, STATES.OPENING);
			},

			function() {
			setState(remodal, STATES.OPENED);
			},

			remodal);
		};

		/**
		* Closes a modal window
		* @public
		* @param {String} reason
		*/
		Remodal.prototype.close = function(reason) {
		var remodal = this;

		// Check if the animation was completed
		if (remodal.state === STATES.OPENING || remodal.state === STATES.CLOSING || remodal.state === STATES.CLOSED) {
			return;
		}

		if (
			remodal.settings.hashTracking &&
			remodal.$modal.attr('data-' + PLUGIN_NAME + '-id') === location.hash.substr(1)
		) {
			location.hash = '';
			$(window).scrollTop(scrollTop);
		}

		syncWithAnimation(
			function() {
			setState(remodal, STATES.CLOSING, false, reason);
			},

			function() {
			remodal.$bg.removeClass(remodal.settings.modifier);
			remodal.$overlay.removeClass(remodal.settings.modifier).hide();
			remodal.$wrapper.hide();
			unlockScreen();

			setState(remodal, STATES.CLOSED, false, reason);
			},

			remodal);
		};

		/**
		* Returns a current state of a modal
		* @public
		* @returns {STATES}
		*/
		Remodal.prototype.getState = function() {
		return this.state;
		};

		/**
		* Destroys a modal
		* @public
		*/
		Remodal.prototype.destroy = function() {
		var lookup = $[PLUGIN_NAME].lookup;
		var instanceCount;

		halt(this);
		this.$wrapper.remove();

		delete lookup[this.index];
		instanceCount = $.grep(lookup, function(instance) {
			return !!instance;
		}).length;

		if (instanceCount === 0) {
			this.$overlay.remove();
			this.$bg.removeClass(
			namespacify('is', STATES.CLOSING) + ' ' +
			namespacify('is', STATES.OPENING) + ' ' +
			namespacify('is', STATES.CLOSED) + ' ' +
			namespacify('is', STATES.OPENED));
		}
		};

		/**
		* Special plugin object for instances
		* @public
		* @type {Object}
		*/
		$[PLUGIN_NAME] = {
		lookup: []
		};

		/**
		* Plugin constructor
		* @constructor
		* @param {Object} options
		* @returns {JQuery}
		*/
		$.fn[PLUGIN_NAME] = function(opts) {
		var instance;
		var $elem;

		this.each(function(index, elem) {
			$elem = $(elem);

			if ($elem.data(PLUGIN_NAME) == null) {
			instance = new Remodal($elem, opts);
			$elem.data(PLUGIN_NAME, instance.index);

			if (
				instance.settings.hashTracking &&
				$elem.attr('data-' + PLUGIN_NAME + '-id') === location.hash.substr(1)
			) {
				instance.open();
			}
			} else {
			instance = $[PLUGIN_NAME].lookup[$elem.data(PLUGIN_NAME)];
			}
		});

		return instance;
		};

		$(document).ready(function() {

		// data-remodal-target opens a modal window with the special Id
		$(document).on('click', '[data-' + PLUGIN_NAME + '-target]', function(e) {
			e.preventDefault();

			var elem = e.currentTarget;
			var id = elem.getAttribute('data-' + PLUGIN_NAME + '-target');
			var $target = $('[data-' + PLUGIN_NAME + '-id="' + id + '"]');

			$[PLUGIN_NAME].lookup[$target.data(PLUGIN_NAME)].open();
		});

		// Auto initialization of modal windows
		// They should have the 'remodal' class attribute
		// Also you can write the `data-remodal-options` attribute to pass params into the modal
		$(document).find('.' + NAMESPACE).each(function(i, container) {
			var $container = $(container);
			var options = $container.data(PLUGIN_NAME + '-options');

			if (!options) {
			options = {};
			} else if (typeof options === 'string' || options instanceof String) {
			options = parseOptions(options);
			}

			$container[PLUGIN_NAME](options);
		});

		// Handles the keydown event
		$(document).on('keydown.' + NAMESPACE, function(e) {
			if (current && current.settings.closeOnEscape && current.state === STATES.OPENED && e.keyCode === 27) {
			current.close();
			}
		});

		// Handles the hashchange event
		$(window).on('hashchange.' + NAMESPACE, handleHashChangeEvent);
		});
	});
	</script>
	<style>
		/*
		*  Remodal - v1.1.1
		*  Responsive, lightweight, fast, synchronized with CSS animations, fully customizable modal window plugin with declarative configuration and hash tracking.
		*  http://vodkabears.github.io/remodal/
		*
		*  Made by Ilya Makarov
		*  Under MIT License
		*/

			/* ==========================================================================
				Remodal's default mobile first theme
				========================================================================== */

			/* Default theme styles for the background */

			.remodal-bg.remodal-is-opening,
			.remodal-bg.remodal-is-opened {
				-webkit-filter: blur(3px);
				filter: blur(3px);
			}

			/* Default theme styles of the overlay */

			.remodal-overlay {
				background: rgba(43, 46, 56, 0.9);
			}

			.remodal-overlay.remodal-is-opening,
			.remodal-overlay.remodal-is-closing {
				-webkit-animation-duration: 0.3s;
				animation-duration: 0.3s;
				-webkit-animation-fill-mode: forwards;
				animation-fill-mode: forwards;
			}

			.remodal-overlay.remodal-is-opening {
				-webkit-animation-name: remodal-overlay-opening-keyframes;
				animation-name: remodal-overlay-opening-keyframes;
			}

			.remodal-overlay.remodal-is-closing {
				-webkit-animation-name: remodal-overlay-closing-keyframes;
				animation-name: remodal-overlay-closing-keyframes;
			}

			/* Default theme styles of the wrapper */

			.remodal-wrapper {
				padding: 10px 10px 0;
			}

			/* Default theme styles of the modal dialog */

			.remodal {
				box-sizing: border-box;
				width: 100%;
				margin-bottom: 10px;
				padding: 35px;

				-webkit-transform: translate3d(0, 0, 0);
				transform: translate3d(0, 0, 0);

				color: #2b2e38;
				background: #fff;
			}

			.remodal.remodal-is-opening,
			.remodal.remodal-is-closing {
				-webkit-animation-duration: 0.3s;
				animation-duration: 0.3s;
				-webkit-animation-fill-mode: forwards;
				animation-fill-mode: forwards;
			}

			.remodal.remodal-is-opening {
				-webkit-animation-name: remodal-opening-keyframes;
				animation-name: remodal-opening-keyframes;
			}

			.remodal.remodal-is-closing {
				-webkit-animation-name: remodal-closing-keyframes;
				animation-name: remodal-closing-keyframes;
			}

			/* Vertical align of the modal dialog */

			.remodal,
			.remodal-wrapper:after {
				vertical-align: middle;
			}

			/* Close button */

			.remodal-close {
				position: absolute;
				top: 0;
				right: 0;

				display: block;
				overflow: visible;

				width: 35px;
				height: 35px;
				margin: 0;
				padding: 0;

				cursor: pointer;
				-webkit-transition: color 0.2s;
				transition: color 0.2s;
				text-decoration: none;

				color: #95979c;
				border: 0;
				outline: 0;
				background: transparent;
			}

			.remodal-close:hover,
			.remodal-close:focus {
				color: #2b2e38;
			}

			.remodal-close:before {
				font-family: Arial, "Helvetica CY", "Nimbus Sans L", sans-serif !important;
				font-size: 25px;
				line-height: 35px;

				position: absolute;
				top: 0;
				left: 0;

				display: block;

				width: 35px;

				content: "\00d7";
				text-align: center;
			}

			/* Dialog buttons */

			.remodal-confirm,
			.remodal-cancel {
				font: inherit;

				display: inline-block;
				overflow: visible;

				min-width: 110px;
				margin: 0;
				padding: 12px;

				cursor: pointer;
				-webkit-transition: background 0.2s;
				transition: background 0.2s;
				text-align: center;
				vertical-align: middle;
				text-decoration: none;

				border: 0;
				outline: 0;
			}

			.remodal-confirm {
				color: #fff;
				background: #81c784;
			}

			.remodal-confirm:hover,
			.remodal-confirm:focus {
				background: #66bb6a;
			}

			.remodal-cancel {
				color: #fff;
				background: #e57373;
			}

			.remodal-cancel:hover,
			.remodal-cancel:focus {
				background: #ef5350;
			}

			/* Remove inner padding and border in Firefox 4+ for the button tag. */

			.remodal-confirm::-moz-focus-inner,
			.remodal-cancel::-moz-focus-inner,
			.remodal-close::-moz-focus-inner {
				padding: 0;

				border: 0;
			}

			/* Keyframes
				========================================================================== */

			@-webkit-keyframes remodal-opening-keyframes {
				from {
				-webkit-transform: scale(1.05);
				transform: scale(1.05);

				opacity: 0;
				}
				to {
				-webkit-transform: none;
				transform: none;

				opacity: 1;

				-webkit-filter: blur(0);
				filter: blur(0);
				}
			}

			@keyframes remodal-opening-keyframes {
				from {
				-webkit-transform: scale(1.05);
				transform: scale(1.05);

				opacity: 0;
				}
				to {
				-webkit-transform: none;
				transform: none;

				opacity: 1;

				-webkit-filter: blur(0);
				filter: blur(0);
				}
			}

			@-webkit-keyframes remodal-closing-keyframes {
				from {
				-webkit-transform: scale(1);
				transform: scale(1);

				opacity: 1;
				}
				to {
				-webkit-transform: scale(0.95);
				transform: scale(0.95);

				opacity: 0;

				-webkit-filter: blur(0);
				filter: blur(0);
				}
			}

			@keyframes remodal-closing-keyframes {
				from {
				-webkit-transform: scale(1);
				transform: scale(1);

				opacity: 1;
				}
				to {
				-webkit-transform: scale(0.95);
				transform: scale(0.95);

				opacity: 0;

				-webkit-filter: blur(0);
				filter: blur(0);
				}
			}

			@-webkit-keyframes remodal-overlay-opening-keyframes {
				from {
				opacity: 0;
				}
				to {
				opacity: 1;
				}
			}

			@keyframes remodal-overlay-opening-keyframes {
				from {
				opacity: 0;
				}
				to {
				opacity: 1;
				}
			}

			@-webkit-keyframes remodal-overlay-closing-keyframes {
				from {
				opacity: 1;
				}
				to {
				opacity: 0;
				}
			}

			@keyframes remodal-overlay-closing-keyframes {
				from {
				opacity: 1;
				}
				to {
				opacity: 0;
				}
			}

			/* Media queries
				========================================================================== */

			@media only screen and (min-width: 641px) {
				.remodal {
				max-width: 800px;
				}
			}

			/* IE8
				========================================================================== */

			.lt-ie9 .remodal-overlay {
				background: #2b2e38;
			}

			.lt-ie9 .remodal {
				width: 700px;
			}
			/* ==========================================================================
				Remodal's necessary styles
				========================================================================== */

			/* Hide scroll bar */

			html.remodal-is-locked {
				overflow: hidden;

				touch-action: none;
			}

			/* Anti FOUC */

			.remodal,
			[data-remodal-id] {
				display: none;
			}

			/* Necessary styles of the overlay */

			.remodal-overlay {
				position: fixed;
				z-index: 9999;
				top: -5000px;
				right: -5000px;
				bottom: -5000px;
				left: -5000px;

				display: none;
			}

			/* Necessary styles of the wrapper */

			.remodal-wrapper {
				position: fixed;
				z-index: 10000;
				top: 0;
				right: 0;
				bottom: 0;
				left: 0;

				display: none;
				overflow: auto;

				text-align: center;

				-webkit-overflow-scrolling: touch;
			}

			.remodal-wrapper:after {
				display: inline-block;

				height: 100%;
				margin-left: -0.05em;

				content: "";
			}

			/* Fix iPad, iPhone glitches */

			.remodal-overlay,
			.remodal-wrapper {
				backface-visibility: hidden;
			}

			/* Necessary styles of the modal dialog */

			.remodal {
				position: relative;

				outline: none;

				text-size-adjust: 100%;
			}

			.remodal-is-initialized {
				/* Disable Anti-FOUC */
				display: inline-block;
			}

	</style>
		<div class="remodal" data-remodal-id="wsal_reset_settings">
			<button data-remodal-action="close" class="remodal-close"></button>
			<h2><?php esc_html_e( 'Let’s Get Your Notifications Set Up', 'wp-security-audit-log' ); ?></h2>
			<p><?php esc_html_e( 'In this Notifications section, you can configure the plugin to send alerts via email, Slack, and, or SMS when specific user actions or website changes occur.', 'wp-security-audit-log' ); ?></p>
			<p><?php esc_html_e( 'To get started, make sure the default notification email address is set. If you plan to use Slack or SMS notifications, you’ll also need to configure a Slack channel and a mobile number—these are optional and can be set up later.', 'wp-security-audit-log' ); ?></p>
			<p><?php esc_html_e( 'If you’ve already completed the setup wizard, your email address should be ready—this is just a quick reminder to review your settings.', 'wp-security-audit-log' ); ?></p>
			<p><?php esc_html_e( 'Click Continue to be redirected to the Notifications settings page and confirm everything is in place.', 'wp-security-audit-log' ); ?></p>
			<br>
			<input type="hidden" id="wsal-reset-settings-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wsal-reset-settings' ) ); ?>">
			<button onclick="window.location.href=('<?php echo \esc_url( $settings_url ); ?> ');  location.reload(); " data-remodal-action="confirm" class="remodal-confirm"><?php esc_html_e( 'Continue' ); ?></button>
		</div>

		<a id="modshow" style="display:none" href="#" data-remodal-target="wsal_reset_settings">Call</a>

		<script>
					window.addEventListener("load", () => {
						setTimeout(function () {
		
			jQuery('#modshow').click()
		
	}, 1000);
						
					});

					</script>
	<?php
}

Settings_Helper::set_boolean_option_value( 'notification-modal-dismissed', true, false );

// phpcs:disable
/* @premium:end */
// phpcs:enable

Settings_Builder::build_option(
	array(
		'id'      => 'general-settings-tab',
		'type'    => 'html',
		'content' => '<p>' . \wp_sprintf(
			esc_html__( 'Use this section to configure a daily or weekly activity log summary, or both. This gives you a regular overview of key events.', 'wp-security-audit-log' ) . '</p>' .

			'<p>' . esc_html__( 'Customize your summary notifications by selecting which highlights to include and specifying the email address(es) where the summaries should be sent. This helps you stay informed about important activity on your site without needing to review the full log.', 'wp-security-audit-log' ) . '</p>'
		),
	)
);

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Daily Activity log highlights email', 'wp-security-audit-log' ),
		'id'            => 'daily-summary-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Send me a summary of what happens every day. ', 'wp-security-audit-log' ),
		'id'            => 'notification_daily_summary_notification',
		'toggle'        => '#notification_daily_email_address-item, #notification_daily_send_now_ajax-item, #notification_daily_send_empty_summary_emails-item',
		'type'          => 'checkbox',
		'default'       => false,
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	Notification_Helper::email_settings_array( 'notification_daily_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__(
			'Send empty summary emails ',
			'wp-security-audit-log'
		),
		'id'            => 'notification_daily_send_empty_summary_emails',
		'type'          => 'checkbox',
		'default'       => false,
		'hint'          => esc_html__( 'Do you want to receive an email even if there are no event IDs that match the criteria for the periodic reports? ', 'wp-security-audit-log' ),
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'id'            => 'send_daily_notification_nonce',
		'type'          => 'hidden',
		'default'       => \wp_create_nonce( Notifications::BUILT_IN_SEND_NOW_NONCE_NAME ),
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

if ( isset( $notifications['notification_daily_email_address'] ) && ! empty( $notifications['notification_daily_email_address'] ) ) {

	Settings_Builder::build_option(
		array(
			'add_label'     => true,
			'id'            => 'notification_daily_send_now_ajax',
			'type'          => 'button',
			'default'       => esc_html__( 'Send test report now (one day data)', 'wp-security-audit-log' ),
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
}
// ---- WEEKLY summary notifications

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Weekly Activity log highlights email', 'wp-security-audit-log' ),
		'id'            => 'weekly-summary-notification-settings',
		'type'          => 'header',
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Send me a summary of what happens every week. ', 'wp-security-audit-log' ),
		'id'            => 'notification_weekly_summary_notification',
		'toggle'        => '#notification_weekly_email_address-item, #notification_weekly_send_now_ajax-item, #notification_weekly_send_empty_summary_emails-item',
		'type'          => 'checkbox',
		'default'       => true,
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	Notification_Helper::email_settings_array( 'notification_weekly_email_address', Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME )
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__(
			'Send empty summary emails ',
			'wp-security-audit-log'
		),
		'id'            => 'notification_weekly_send_empty_summary_emails',
		'type'          => 'checkbox',
		'default'       => false,
		'hint'          => esc_html__( 'Do you want to receive an email even if there are no event IDs that match the criteria for the periodic reports? ', 'wp-security-audit-log' ),
		'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
	)
);

if ( isset( $notifications['notification_weekly_email_address'] ) && ! empty( $notifications['notification_weekly_email_address'] ) ) {

	Settings_Builder::build_option(
		array(
			'add_label'     => true,
			'id'            => 'notification_weekly_send_now_ajax',
			'type'          => 'button',
			'default'       => esc_html__( 'Send test report now (one day data)', 'wp-security-audit-log' ),
			'settings_name' => Notifications::BUILT_IN_NOTIFICATIONS_SETTINGS_NAME,
		)
	);
}

// Sections include.

// phpcs:disable
/* @premium:start */
// phpcs:enable
Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Include these in the highlights email', 'wp-security-audit-log' ),
		'id'            => 'notification-summary-default-settings',
		'type'          => 'header',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

		Settings_Builder::build_option(
			array(
				'text' => '<span style="display:block; margin-bottom:1em;">' . esc_html__( 'In this section you can choose which types of activity highlights to include in the daily or weekly summary email. These selections determine the focus of the summary email, helping you stay informed about the most relevant activity on your website.', 'wp-security-audit-log' ),
				'type' => 'hint',
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'User logins: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_user_logins',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);


		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Failed logins: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_failed_logins',
				'type'          => 'checkbox',
				'toggle'        => '#notification_wrong_password-item, #notification_summary_wrong_username-item',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Wrong password: ', 'wp-security-audit-log' ),
				'id'            => 'notification_wrong_password',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Login attempt with wrong username: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_wrong_username',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Password changes: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_password_changes',
				'type'          => 'checkbox',
				'toggle'        => '#notification_summary_password_user_change_own_password-item, #notification_summary_password_user_change_other_password-item',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'User changed its own password: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_password_user_change_own_password',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'User changed other user password: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_password_user_change_other_password',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Plugins activity: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_plugins_activity',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);

		Settings_Builder::build_option(
			array(
				'name'          => esc_html__( 'Website system settings changes: ', 'wp-security-audit-log' ),
				'id'            => 'notification_summary_system_activity',
				'type'          => 'checkbox',
				'default'       => true,
				'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
			)
		);
		?>
<div id="notification_summary_content_wrap">
	<?php
	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Content changes: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_content_changes',
			'type'          => 'checkbox',
			'toggle'        => '#notification_summary_published_posts-item, #notification_summary_deleted_posts-item, #notification_summary_changed_posts-item, #notification_summary_status_changed_posts-item',
			'default'       => true,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'List of published posts: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_published_posts',
			'type'          => 'checkbox',
			'default'       => true,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'List of deleted and trashed posts: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_deleted_posts',
			'type'          => 'checkbox',
			'default'       => false,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'List of changes in posts: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_changed_posts',
			'type'          => 'checkbox',
			'default'       => false,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'List of status changed posts: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_status_changed_posts',
			'type'          => 'checkbox',
			'default'       => false,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
	?>
</div>
<?php
if ( WP_Helper::is_multisite() ) {
	Settings_Builder::build_option(
		array(
			'title'         => esc_html__( 'Multisite Activity log highlights email: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_multisite_activity',
			'type'          => 'header',
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);

	Settings_Builder::build_option(
		array(
			'name'          => esc_html__( 'Send summary per individual site: ', 'wp-security-audit-log' ),
			'id'            => 'notification_summary_multisite_individual_site',
			'type'          => 'checkbox',
			'toggle'        => '#notification_summary_content_wrap',
			'default'       => true,
			'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
		)
	);
}

Settings_Builder::build_option(
	array(
		'title'         => esc_html__( 'Should the events of each section be included in the summary email?', 'wp-security-audit-log' ),
		'id'            => 'notification-summary-include-occurrences',
		'type'          => 'header',
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Include events', 'wp-security-audit-log' ),
		'id'            => 'notification_events_included',
		'type'          => 'checkbox',
		'default'       => false,
		'toggle'        => '#notification_summary_number_of_events_included-item',
		'hint'          => esc_html__( 'By default, only the total numbers of events per category are reported in the activity log highlight email. Use the below settings to also include some or all of the events from each category. IMPORTANT: The process of including all events in the email can be a very resource intensive process, and it all depends on how many events do you usually have in the activity log.', 'wp-security-audit-log' ),
		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);

Settings_Builder::build_option(
	array(
		'name'          => esc_html__( 'Number of events to include', 'wp-security-audit-log' ),
		'id'            => 'notification_summary_number_of_events_included',
		'type'          => 'radio',
		'options'       => array(
			'10' => esc_html__( 'Last 10 events', 'wp-security-audit-log' ),
			'1'  => esc_html__( 'All of the events', 'wp-security-audit-log' ),
		),

		'settings_name' => Notifications::NOTIFICATIONS_SETTINGS_NAME,
	)
);
// phpcs:disable
/* @premium:end */
// phpcs:enable
?>

<input type="hidden" name="<?php echo Notifications::NOTIFICATIONS_SETTINGS_NAME;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>[]" value="0" />
