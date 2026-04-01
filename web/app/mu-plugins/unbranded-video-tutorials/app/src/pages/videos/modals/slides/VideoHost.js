/**
 * Library dependencies
 */
import { Box, Icon, Button } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../../components'
import { VideoHostsList } from '../../components'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { __ } from '@wordpress/i18n'
import PropTypes from 'prop-types'

export function VideoHost({ modalID, closeModal, next, video, setVideo }) {
	const classes = classnames('sui-modal-slide', 'sui-active', 'sui-loaded')

	const headerClasses = classnames(
		'sui-flatten sui-content-center',
		'sui-spacing-top--60',
		'sui-spacing-right--60',
		'sui-spacing-left--60'
	)

	const { host } = video ?? {}

	return (
		<div
			className={classes}
			data-modal-size="lg"
			id={`${modalID}-host-selector`}
		>
			<Box>
				<Box.Header className={headerClasses}>
					<WhiteLabelBanner
						src="modal/modal-header-videos.png"
						alt={__('Add Custom Video', 'wpmudev_vids')}
					/>

					<button
						onClick={closeModal}
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

					<h3
						id={`${modalID}-title`}
						className="sui-box-title sui-lg"
					>
						{__('Add Custom Video', 'wpmudev_vids')}
					</h3>

					<p id={`${modalID}-desc`} className="sui-description">
						{__(
							"Link to custom videos you've found, or uploaded on another video host. Choose from the available hosts below.",
							'wpmudev_vids'
						)}
					</p>
				</Box.Header>

				<VideoHostsList
					video={video}
					setVideo={setVideo}
					modalID={modalID}
				/>

				<Box.Footer isFlushed={true} isCentered={true} isFlatten={true}>
					<Button
						disabled={!host}
						data-modal-slide={next}
						data-modal-slide-intro="next"
					>
						{__('Continue', 'wpmudev_vids')}
					</Button>
				</Box.Footer>
			</Box>
		</div>
	)
}

VideoHost.propTypes = {
	modalID: PropTypes.string,
	closeModal: PropTypes.func,
	next: PropTypes.string,
	video: PropTypes.object,
}
