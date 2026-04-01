/**
 * Library dependencies
 */
import { Box, Icon, IconButton } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../components'
import { restGet } from '../../../helpers/api'
import { dashInstalled, dashActive } from '../../../helpers/utils'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'

// Global Variables
const { user_name, urls } = window.ivtVars ?? {}
const { plugins, dash_login } = urls ?? {}

const headerClasses = classnames(
	'sui-flatten',
	'sui-spacing-top--60',
	'sui-content-center',
	'install-dashboard-modal'
)

export function DashModal({ modalID, closeModal }) {
	/**
	 * Dismiss the modal and set the flag.
	 *
	 * @return {void}
	 */
	const dismiss = () => {
		// Make sure to set the flag.
		restGet({
			path: 'actions',
			params: {
				action: 'dismiss_dash',
			},
		})

		// Manually remove modal class from html element
		document
			.getElementsByTagName('html')[0]
			.classList.remove('sui-has-modal')

		// Close the modal.
		closeModal()
	}

	/**
	 * Get labels and links for the banner.
	 *
	 * Based on the status of WPMUDEV Dash plugin status,
	 * set the labels and links for the banner.
	 *
	 * @return {object}
	 */
	const getLabels = () => {
		if (!dashInstalled()) {
			return {
				title: __('Install WPMU DEV Dashboard', 'wpmudev_vids'),
				desc: sprintf(
					__(
						"%s, welcome to Integrated Video Tutorials - the best tutorials plugin for WordPress. It looks like you don't have the WPMU DEV Dashboard plugin installed, which you'll need to access the videos API. Install and log into the plugin to start setting up your video tutorials.",
						'wpmudev_vids'
					),
					user_name
				),
				button: __('Install Plugin', 'wpmudev_vids'),
				link: 'https://wpmudev.com/project/wpmu-dev-dashboard/',
				target: '_blank',
			}
		} else if (!dashActive()) {
			return {
				title: __('Activate WPMU DEV Dashboard', 'wpmudev_vids'),
				desc: sprintf(
					__(
						"Great, %s! The WPMU DEV Dashboard plugin is installed, but not activated yet. You'll need to activate it to access our videos API and set up your video tutorials.",
						'wpmudev_vids'
					),
					user_name
				),
				button: __('Activate Plugin', 'wpmudev_vids'),
				link: plugins,
				target: '_self',
			}
		} else {
			return {
				title: __('Login to WPMU DEV Dashboard', 'wpmudev_vids'),
				desc: sprintf(
					__(
						"%s, welcome to Integrated Video Tutorials - the best tutorials plugin for WordPress. It looks like you haven't logged into the WPMU DEV Dashboard plugin, which you'll need to do to access the videos API. Log into the plugin to begin setting up your video tutorials."
					),
					user_name
				),
				button: __('Login', 'wpmudev_vids'),
				link: dash_login,
				target: '_self',
			}
		}
	}

	return (
		<Box>
			<Box.Header className={headerClasses}>
				<WhiteLabelBanner
					src="modal/ivt-main-modal-header.png"
					alt={__(getLabels().title, 'wpmudev_vids')}
				/>

				<IconButton
					outlined={false}
					className="sui-button-float--right"
					onClick={dismiss}
					id={`${modalID}-close`}
					icon="close"
					size="md"
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={`${modalID}-title`} className="sui-box-title sui-lg">
					{getLabels().title}
				</h3>

				<p id={`${modalID}-desc`} className="sui-description">
					{getLabels().desc}
				</p>
			</Box.Header>

			<Box.Footer isFlatten={true} isCentered={true}>
				<a
					className="sui-button sui-button-blue"
					target={getLabels().target}
					href={getLabels().link}
				>
					<Icon icon="wpmudev-logo" />
					{getLabels().button}
				</a>
			</Box.Footer>
		</Box>
	)
}

export default DashModal
