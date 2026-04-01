/**
 * Library dependencies
 */
import { Box, Icon } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../components'
import { restGet } from '../../../helpers/api'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'

// Global Variables
const { user_name } = window.ivtVars ?? {}

const headerClasses = classnames(
	'sui-flatten',
	'sui-spacing-top--60',
	'sui-content-center'
)

export function UpgradeModal({ modalID, closeModal }) {
	/**
	 * Dismiss the modal and set the flag.
	 *
	 * @return {void}
	 */
	const dismiss = () => {
		// Close the modal.
		closeModal()

		// Manually remove modal class from html element
		document
			.getElementsByTagName('html')[0]
			.classList.remove('sui-has-modal')

		// Make sure to set the flag.
		restGet({
			path: 'actions',
			params: {
				action: 'dismiss_dash',
			},
		})
	}

	return (
		<Box>
			<Box.Header className={headerClasses}>
				<WhiteLabelBanner
					src="modal/wpmudev-modal-header.png"
					alt={__(
						'Get Integrated Video Tutorial Access',
						'wpmudev_vids'
					)}
				/>

				<button
					onClick={dismiss}
					className={classnames(
						'sui-button-icon',
						'sui-button-float--right'
					)}
				>
					<Icon icon="close" size="md" />
					<span className="sui-screen-reader-text">
						{__('Close this dialog.', 'wpmudev_vids')}
					</span>
				</button>

				<h3 id={`${modalID}-title`} className="sui-box-title sui-lg">
					{__('Get Integrated Video Tutorial Access', 'wpmudev_vids')}
				</h3>

				<p id={`${modalID}-desc`} className="sui-description">
					{sprintf(
						__(
							"Hey %s, it looks like you don't have access to the WPMU DEV white-labeled video tutorials due to your current membership status. You can gain access by activating a full membership. Click the button below for more information on the WPMU DEV pricing structure.",
							'wpmudev_vids'
						),
						user_name
					)}
				</p>
			</Box.Header>

			<Box.Footer isFlatten={true} isCentered={true}>
				<a
					id="wpmudev-videos-upgrade-account-button"
					className="sui-button sui-button-purple"
					target="_blank"
					href="https://wpmudev.com/project/unbranded-video-tutorials/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_modal_upgrade"
				>
					{__('Get Full Membership', 'wpmudev_vids')}
				</a>
			</Box.Footer>
		</Box>
	)
}

export default UpgradeModal
