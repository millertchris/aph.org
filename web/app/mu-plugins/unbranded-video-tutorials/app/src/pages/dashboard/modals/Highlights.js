/**
 * Library dependencies
 */
import { Box, Button, Icon } from '../../../lib/components'

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

export function Highlights({ modalID, closeModal }) {
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
				action: 'dismiss_welcome',
			},
		})
	}

	return (
		<Box>
			<Box.Header className={headerClasses}>
				<WhiteLabelBanner
					src="welcome/new-videos.png"
					alt={__('Introducing New Videos!', 'wpmudev_vids')}
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
					{__('Introducing New Videos!', 'wpmudev_vids')}
				</h3>

				<p id={`${modalID}-desc`} className="sui-description">
					{sprintf(
						__(
							'Hey, %s! WordPress is always evolving with new features and improvements, and our job is to keep you abreast of those changes. So, we’ve added new videos (Site Health, Export and Erase Personal Data, Import and Export, Lists, Categories, and Editing Images) to our Video Tutorials. Take a look!',
							'wpmudev_vids'
						),
						user_name
					)}
				</p>
			</Box.Header>

			<Box.Footer isFlatten={true} isCentered={true}>
				<Button onClick={dismiss}>
					{__('Got it', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default Highlights
