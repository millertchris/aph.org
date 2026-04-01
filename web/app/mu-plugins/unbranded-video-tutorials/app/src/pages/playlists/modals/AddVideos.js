/**
 * Library dependencies
 */
import { Box, IconButton, Button } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../components'
import { savePlaylist, getLoadingStatus } from '../../../store/slices/playlists'
import { addNotice } from '../../../store/slices/notice'
import {
	UpgradeNotice,
	RenewNotice,
	VideoSearchInput,
	VideosList,
} from '../components'
import {
	expiredMember,
	validMember,
	dashConnected,
} from '../../../helpers/utils'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'
import { useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'

export function AddVideos({
	modalID,
	closeModal,
	playlist,
	setCurrentPlaylist,
}) {
	const dispatch = useDispatch()

	const { id, videos } = playlist ?? {}

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
		!expiredMember() && !validMember() && dashConnected() && ( typeof videoCount === 'undefined' || videoCount <= 0 )

	/**
	 * Check if we need to show the membership expired notice.
	 */
	const showExpiredNotice =
		expiredMember() && dashConnected() && ( typeof videoCount === 'undefined' || videoCount <= 0 )

	/**
	 * Save Changes
	 */
	const saveChanges = () => {
		dispatch(
			savePlaylist({
				id,
				playlist: {
					videos,
				},
			})
		).then((response) => {
			if (response?.payload?.success) {
				// close the modal
				closeModal()

				// Show Successful Notice
				dispatch(
					addNotice({
						message: __(
							'Playlist videos updated successfully.',
							'wpmudev_vids'
						),
					})
				)
			} else {
				// show error message
				dispatch(
					addNotice({
						message: __(
							'Could not update the playlist videos. Please try again.',
							'wpmudev_vids'
						),
						type: 'error',
					})
				)
			}
		})
	}

	return (
		<Box>
			<button className="sui-screen-reader-text" onClick={closeModal}>
				{__('Close this dialog.', 'wpmudev_vids')}
			</button>
			<Box.Header className={BoxHeaderClasses}>
				<WhiteLabelBanner
					src="modal/modal-header-videos.png"
					alt={__('Add videos', 'wpmudev_vids')}
				/>
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={closeModal}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={`${modalID}-title`} className="sui-box-title sui-lg">
					{__('Add videos', 'wpmudev_vids')}
				</h3>
				<p id={`${modalID}-desc`} className="sui-description">
					{__(
						'Choose which videos you want to assign to this playlist. You can add as many as you like.',
						'wpmudev_vids'
					)}
				</p>
			</Box.Header>
			<Box.Body>
				{/* Video Search Input  */}
				<VideoSearchInput setVideoCount={setVideoCount} />

				{/* When membership is not valid */}
				{showUpgradeNotice && <><br /><UpgradeNotice /></>}

				{/* When membership is expired */}
				{!showUpgradeNotice && showExpiredNotice &&  <><br /><RenewNotice /></>}
			</Box.Body>

			<VideosList
				setCurrentPlaylist={setCurrentPlaylist}
				playlist={playlist}
				setVideoCount={setVideoCount}
			/>
			<Box.Footer>
				<Button type="ghost" onClick={closeModal}>
					{__('Cancel', 'wpmudev_vids')}
				</Button>
				<Box.Right>
					<Button
						isLoading={isSaving}
						disabled={isSaving}
						onClick={saveChanges}
						onLoadingText={__('Adding Videos', 'wpmudev_vids')}
					>
						{__('Add videos', 'wpmudev_vids')}
					</Button>
				</Box.Right>
			</Box.Footer>
		</Box>
	)
}

export default AddVideos
