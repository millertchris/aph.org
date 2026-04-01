/**
 * Library dependencies
 */
import {
	Box,
	Icon,
	StaticNotice,
	Select,
	Button,
	Label,
	IconButton,
} from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../../components'
import { getPlaylists } from '../../../../store/slices/playlists'
import { createVideo } from '../../../../store/slices/videos'
import { addNotice } from '../../../../store/slices/notice'

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import classnames from 'classnames'
import { useState, useEffect } from 'react'
import { useSelector, useDispatch } from 'react-redux'

export function VideoPlaylists({
	modalID,
	closeModal,
	video,
	setVideo,
	prev,
	emptyVideo,
	setUrlReady,
}) {
	const {
		playlists: videoPlaylists,
		url,
		host,
		title,
		start_enabled,
		end_enabled,
		start_time,
		end_time,
		thumbnail,
		duration,
	} = video
	const [showError, setShowError] = useState(false)
	const [formattedPlaylists, setFormattedPlaylists] = useState({})
	const dispatch = useDispatch()

	const [isPublishing, setIsPublishing] = useState(false)

	const playlists = useSelector((state) => getPlaylists(state))

	const headerClasses = classnames(
		'sui-flatten sui-content-center',
		'sui-spacing-top--60',
		'sui-spacing-right--60',
		'sui-spacing-left--60'
	)

	/**
	 * Format the playlist data to Select2 format.
	 *
	 * Select2 accept id and text as option object.
	 *
	 * @return {object}
	 */
	const formatPlaylists = () => {
		let list = {}

		// Format playlists.
		if (Object.keys(playlists).length > 0) {
			Object.keys(playlists).forEach((id) => {
				list = { ...list, [id]: playlists?.[id]?.title }
			})
		}

		setFormattedPlaylists(list)
	}

	useEffect(() => {
		formatPlaylists()
	}, [playlists])

	/**
	 * Update selected playlists
	 *
	 * @return {void}
	 */
	const updateSelectedPlaylists = (selected) => {
		const newVideo = { ...video, playlists: selected }
		setVideo(newVideo)
	}

	/**
	 * Publish Video
	 *
	 * @return {void}
	 */
	const publishVideo = () => {
		setIsPublishing(true)

		dispatch(
			createVideo({
				video: {
					playlists: videoPlaylists,
					url,
					host,
					title,
					start_enabled,
					end_enabled,
					start_time,
					end_time,
					thumbnail: thumbnail.id || 0,
					duration,
				},
			})
		)
			.then((response) => {
				if (response?.payload?.success) {
					// Close the modal
					closeModal()

					const video = response?.payload?.data

					// Show success notice
					const message = video?.view_link
						? __(
								'Custom video added successfully. You can <a href="%s">view the video</a> on the Video Tutorials page.',
								'wpmudev_vids'
						  )
						: __(
								'Custom video added successfully. Assign it to a playlist so the video becomes available on the Video Tutorials page.',
								'wpmudev_vids'
						  )

					// Add Success notice
					dispatch(
						addNotice({
							message: sprintf(message, video?.view_link),
							dismiss: true,
						})
					)

					// Empty the video object
					setVideo(emptyVideo)

					setUrlReady(false)
				} else {
					setShowError(true)
				}
			})
			.finally(() => {
				setIsPublishing(false)
			})
	}

	return (
		<div
			id={`${modalID}-playlist-form`}
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
							'To finish adding this video, assign it to a playlist.',
							'wpmudev_vids'
						)}
					</p>
				</Box.Header>

				<Box.Body>
					{/** Error Notice Goes Here */}
					{showError && (
						<StaticNotice type="error">
							{__(
								'There was an error publishing the video. Please try again.',
								'wpmudev_vids'
							)}
						</StaticNotice>
					)}
					<StaticNotice type="info">
						<p>
							{__(
								"Note: We recommend adding your custom video to an existing playlist so it's available on the Video Tutorials page.",
								'wpmudev_vids'
							)}
						</p>
					</StaticNotice>

					<div className="sui-form-field">
						<Label
							className="sui-label"
							htmlFor={`${modalID}-playlist`}
							id={`${modalID}-playlist-label`}
						>
							{__('Choose playlist', 'wpmudev_vids')}
						</Label>

						<Select
							parentElement="wpmudev-videos-video-create-modal"
							id={`${modalID}-playlist`}
							value={videoPlaylists}
							onChange={updateSelectedPlaylists}
							options={formattedPlaylists}
							labelID={`${modalID}-playlist-label`}
							multiple={true}
							placeholder={__('Choose playlist', 'wpmudev_vids')}
						/>
					</div>
				</Box.Body>

				<Box.Footer isCentered={true} isFlatten={true}>
					<Button
						onLoadingText={__('Publishing', 'wpmudev_vids')}
						onClick={publishVideo}
						isLoading={isPublishing}
					>
						{__('Publish', 'wpmudev_vids')}
					</Button>
				</Box.Footer>
			</Box>
		</div>
	)
}

export default VideoPlaylists
