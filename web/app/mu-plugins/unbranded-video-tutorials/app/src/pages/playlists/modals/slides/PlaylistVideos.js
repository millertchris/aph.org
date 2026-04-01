/**
 * Library dependencies
 */
import {
	Box,
	StaticNotice,
	Button,
	IconButton,
} from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../../components'
import { addNotice } from '../../../../store/slices/notice'
import {
	UpgradeNotice,
	ExpiredNotice,
	VideoSearchInput,
	VideosList,
} from '../../components'
import {
	expiredMember,
	validMember,
	dashConnected,
} from '../../../../helpers/utils'
import {
	getLoadingStatus,
	createPlaylist,
} from '../../../../store/slices/playlists'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'
import { useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'

export function PlaylistVideos({
	modalID,
	closeModal,
	playlist,
	setPlaylist,
	errorMessage,
	setErrorMessage,
}) {
	const dispatch = useDispatch()

	const { title, description, videos, locations, thumbnail } = playlist ?? {}

	const [videoCount, setVideoCount] = useState(0)

	const isSaving = useSelector((state) => getLoadingStatus(state))

	const BoxHeaderClasses = classnames(
		'sui-box-header',
		'sui-flatten',
		'sui-content-center',
		'sui-spacing-top--60',
		'sui-spacing-sides--100'
	)

	/**
	 * Check if we need to show the membership upgrade notice.
	 */
	const showUpgradeNotice =
		!expiredMember() && !validMember() && dashConnected() && videoCount <= 0

	/**
	 * Check if we need to show the membership expired notice.
	 */
	const showExpiredNotice =
		expiredMember() && dashConnected() && videoCount <= 0

	/**
	 * Get error message.
	 *
	 * @since 1.8.4
	 *
	 * @return {string}
	 */
	const getErrorMessage = () => {
		return !!errorMessage
			? errorMessage
			: __(
					'Could not create the playlist. Please try again.',
					'wpmudev_vids'
			  )
	}

	/**
	 * Scroll to top of the modal.
	 *
	 * Use this to show error messages after scrolling
	 * down for the form submit.
	 *
	 * @since 1.8.4
	 */
	const scrollTop = () => {
		document
			.getElementById('wpmudev-videos-playlist-create-modal')
			.scrollIntoView({
				behavior: 'smooth',
				block: 'start',
				inline: 'nearest',
			})
	}

	/**
	 * Create the playlist
	 */
	const submitPlaylist = () => {
		dispatch(
			createPlaylist({
				playlist: {
					title,
					description,
					videos: videos || [],
					locations: locations || [],
					thumbnail: thumbnail.id || 0,
				},
			})
		).then((response) => {
			const {
				payload: { success, data },
			} = response

			if (success === true) {
				// close the modal
				closeModal()

				// Show Successful Notice
				dispatch(
					addNotice({
						message: __(
							'Playlist created successfully.',
							'wpmudev_vids'
						),
						dismiss: true,
					})
				)
			} else if (data) {
				// If error message available from reponse.
				if (data?.params) {
					const error = Object.values(data.params)[0]
					setErrorMessage(error)

					// Scroll top to see the error message
					scrollTop()
				}
			}
		})
	}

	return (
		<div id={`${modalID}-playlist-videos`} className="sui-modal-slide">
			<Box>
				<Button className="sui-screen-reader-text" onClick={closeModal}>
					{__('Close this dialog.', 'wpmudev_vids')}
				</Button>
				<Box.Header className={BoxHeaderClasses}>
					<WhiteLabelBanner
						src="modal/modal-header-videos.png"
						alt={__('Create a new playlist', 'wpmudev_vids')}
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
						data-modal-slide={`${modalID}-playlist-settings`}
						data-modal-slide-intro="back"
						size="md"
						outlined={false}
						label={__('Go back to previous slide.', 'wpmudev_vids')}
					/>

					<h3
						id={`${modalID}-title`}
						className="sui-box-title sui-lg"
					>
						{__('Create a new playlist', 'wpmudev_vids')}
					</h3>

					<p id={`${modalID}-desc`} className="sui-description">
						{__(
							'Choose which videos you want to assign to this playlist. You can add as many as you like.',
							'wpmudev_vids'
						)}
					</p>
				</Box.Header>
				<Box.Body>
					{/* Show creation error  */}
					{!!errorMessage && (
						<StaticNotice type="error">
							<p>{getErrorMessage()}</p>
						</StaticNotice>
					)}

					{/* Video Search Input  */}
					<VideoSearchInput setVideoCount={setVideoCount} />

					{/* When membership is not valid */}
					{showUpgradeNotice && <UpgradeNotice />}

					{/* When membership is expired */}
					{!showUpgradeNotice && showExpiredNotice && (
						<ExpiredNotice />
					)}
				</Box.Body>

				<VideosList
					setCurrentPlaylist={setPlaylist}
					playlist={playlist}
					setVideoCount={setVideoCount}
				/>
				<Box.Footer>
					<Button
						type="ghost"
						data-modal-slide-intro="back"
						data-modal-slide={`${modalID}-playlist-settings`}
					>
						{__('Back', 'wpmudev_vids')}
					</Button>
					<Box.Right>
						<Button
							isLoading={isSaving}
							disabled={isSaving}
							onClick={submitPlaylist}
							onLoadingText={__(
								'Creating Playlist',
								'wpmudev_vids'
							)}
						>
							{__('Create Playlist', 'wpmudev_vids')}
						</Button>
					</Box.Right>
				</Box.Footer>
			</Box>
		</div>
	)
}

export default PlaylistVideos
