/**
 * Library dependencies
 */
import {
	Box,
	Icon,
	StaticNotice,
	Button,
	IconButton,
} from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../../components'
import { VideoForm } from '../../components/'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'
import { useState } from 'react'
import { timeRegex } from '../../../../utils'

export function VideoUrl({
	modalID,
	closeModal,
	video,
	setVideo,
	prev,
	next,
	urlReady,
	setUrlReady,
}) {
	const [showError, setShowError] = useState(false)

	const headerClasses = classnames(
		'sui-flatten sui-content-center',
		'sui-spacing-top--60',
		'sui-spacing-right--60',
		'sui-spacing-left--60'
	)

	const timeReady = ( video.start_enabled && ! timeRegex( video.start_time ) )
		|| ( video.end_enabled && ! timeRegex( video.end_time ) );

	return (
		<div
			id={`${modalID}-url-form`}
			className="sui-modal-slide"
			data-modal-size="lg"
		>
			<Box>
				<Box.Header className={headerClasses}>
					<WhiteLabelBanner
						src="modal/modal-header-videos.png"
						alt={__('Add Custom Video', 'wpmudev_vids')}
					/>
					<IconButton
						className="sui-button-float--right"
						icon="close"
						onClick={closeModal}
						size="md"
						outlined={false}
						label={__('Close this dialog.', 'wpmudev_vids')}
					/>

					<IconButton
						className="sui-button-float--left"
						icon="chevron-left"
						data-modal-slide={prev}
						data-modal-slide-intro="back"
						size="md"
						outlined={false}
						label={__('Go back to previous slide.', 'wpmudev_vids')}
					/>

					<h3
						id={`${modalID}-title`}
						className="sui-box-title sui-lg"
					>
						{__('Add Custom Video', 'wpmudev_vids')}
					</h3>

					<p id={`${modalID}-desc`} className="sui-description">
						{__(
							'Copy and paste the video URL from your browser into the input field below.',
							'wpmudev_vids'
						)}
					</p>

					{showError && (
						<StaticNotice type="error">
							<p className='sui-content-left'>
								{__(
									'The URL you have attached is invalid. Try again by copying the URL from your browser and pasting it into the input field below.',
									'wpmudev_vids'
								)}
							</p>
						</StaticNotice>
					)}
				</Box.Header>

				<VideoForm
					video={video}
					setVideo={setVideo}
					modalID={modalID}
					urlReady={urlReady}
					setUrlReady={setUrlReady}
					setShowError={setShowError}
				/>

				<Box.Footer isCentered={true} isFlatten={true}>
					<Button
						data-modal-slide-intro="next"
						data-modal-slide={next}
						disabled={!urlReady || timeReady}
					>
						{__('Continue', 'wpmudev_vids')}
					</Button>
				</Box.Footer>
			</Box>
		</div>
	)
}

export default VideoUrl
