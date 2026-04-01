/**
 * Library dependencies
 */
import { Box, StaticNotice, Button, IconButton } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { VideoForm } from '../components'
import { updateVideo } from '../../../store/slices/videos'
import { addNotice } from '../../../store/slices/notice'
import { timeRegex } from '../../../utils'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from 'react'
import { useDispatch } from 'react-redux'

export function EditVideo({ modalID, closeModal, video }) {
	const [showError, setShowError] = useState(false)
	const [urlReady, setUrlReady] = useState(false)
	const [newVideo, setNewVideo] = useState({})
	const [isSaving, setIsSaving] = useState(false)
	const dispatch = useDispatch()

	// Update Url Ready
	useEffect(() => {
		setUrlReady(!!video.video_url)
	}, [video])

	useEffect(() => {
		// Update newVideo structure to correspond to VideoForm structure
		setNewVideo({
			id: video.id,
			host: video.video_host,
			url: video.video_url,
			title: video.video_title,
			start_enabled: video.video_start,
			end_enabled: video.video_end,
			start_time: video.video_start_time,
			end_time: video.video_end_time,
			playlists: video.playlists,
			thumbnail: video.thumbnail,
			duration: video.video_duration,
		})
	}, [video])

	const saveChanges = () => {
		setIsSaving(true)

		dispatch(
			updateVideo({
				id: newVideo.id,
				video: {
					url: newVideo.url,
					title: newVideo.title,
					start_enabled: newVideo.start_enabled,
					end_enabled: newVideo.end_enabled,
					start_time: newVideo.start_time,
					end_time: newVideo.end_time,
					thumbnail: newVideo.thumbnail.id,
					duration: newVideo.duration,
				},
			})
		)
			.then((response) => {
				if (response?.payload?.success) {
					// Display Notice
					dispatch(
						addNotice({
							message: sprintf(
								__(
									'“%s” updated successfully.',
									'wpmudev_vids'
								),
								newVideo.title
							),
						})
					)

					// Close the modal
					closeModal()
				} else {
					// Display Error Notice
					dispatch(
						addNotice({
							type: 'error',
							message: __('Updated failed: Please enter a valid URL and Title.', 'wpmudev_vids'),
						})
					)
				}
			})
			.finally(() => {
				setIsSaving(false)
			})
	}

	const btnDisabled = ( newVideo.start_enabled && ! timeRegex( newVideo.start_time ) )
		|| ( newVideo.end_enabled && ! timeRegex( newVideo.end_time ) );

	return (
		<Box>
			<Box.Header className="sui-flatter">
				<Box.Title id={modalID}>
					{__('Edit video', 'wpmudev_vids')}
				</Box.Title>

				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={closeModal}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>
			</Box.Header>

			{/** Display Error Notice */}
			{showError && (
				<Box.Body>
					<StaticNotice type="error">
						<p>
							{__(
								'The URL you have attached is invalid. Try again by copying the URL from your browser and pasting it into the input field below.',
								'wpmudev_vids'
							)}
						</p>
					</StaticNotice>
				</Box.Body>
			)}

			<VideoForm
				video={newVideo}
				setVideo={setNewVideo}
				modalID={modalID}
				urlReady={urlReady}
				setUrlReady={setUrlReady}
				setShowError={setShowError}
			/>

			<Box.Footer isCentered={true} isFlatten={true}>
				<Button
					onLoadingText={__('Saving Changes', 'wpmudev_vids')}
					isLoading={isSaving}
					onClick={saveChanges}
					disabled={btnDisabled}
				>
					{__('Save Changes', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default EditVideo
