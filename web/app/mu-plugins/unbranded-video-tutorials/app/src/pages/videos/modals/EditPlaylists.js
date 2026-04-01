/**
 * Libraries dependecies
 */
import { Box, IconButton, Button, Checkbox } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { updateVideo } from '../../../store/slices/videos'
import { addNotice } from '../../../store/slices/notice'
import { getPlaylists, getFiltered } from '../../../store/slices/playlists'

/**
 * Libraries dependecies
 */
import classnames from 'classnames'
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from 'react'
import { WhiteLabelBanner } from '../../../components'
import { useDispatch, useSelector } from 'react-redux'

export function EditPlaylists({ modalID, closeModal, video, setCurrentVideo }) {
	const [isSaving, setIsSaving] = useState(false)
	const [playlists, setPlaylists] = useState([])

	const dispatch = useDispatch()

	const { playlists: videoPlaylists } = video ?? {}

	const playlistsObject = useSelector((state) => getPlaylists(state))

	const filtered = useSelector((state) => getFiltered(state))

	const headerClasses = classnames(
		'sui-flatten',
		'sui-content-center',
		'sui-spacing-top--60'
	)

	const labelClasses = classnames(
		'sui-checkbox',
		'sui-checkbox-sm',
		'sui-checkbox-stacked'
	)

	const imageClasses = classnames('sui-image', 'sui-image-center')

	const { id, video_title } = video ?? {}

	useEffect(() => {
		const newPlaylists = []
		if (Array.isArray(filtered)) {
			filtered.forEach((id) => {
				if (playlistsObject[id]) {
					newPlaylists.push(playlistsObject[id])
				}
			})
		}

		setPlaylists(newPlaylists)
	}, [playlistsObject, filtered])

	/**
	 * Checkes if video is added to playlist
	 *
	 * @argument {id} id playlist id
	 *
	 * @return {boolean}
	 */
	const isPlaylistChecked = (id) => {
		if (Array.isArray(videoPlaylists)) {
			return videoPlaylists.includes(id)
		}

		return false
	}

	/**
	 * Toggles Playlist
	 *
	 * @argument {id} id playlist id
	 *
	 * @return {null}
	 *
	 */
	const togglePlaylist = (id, value) => {
		if (value) {
			// Add the playlist id
			setCurrentVideo({
				...video,
				playlists: [...videoPlaylists, id],
			})
		} else {
			// Remove the playlist id
			setCurrentVideo({
				...video,
				playlists: videoPlaylists.filter((el) => id !== el),
			})
		}
	}

	/**
	 * Check if we need to disable the submit button
	 */
	const isSubmitDisabled = () => {
		if (Array.isArray(videoPlaylists)) {
			return videoPlaylists.length === 0
		}

		return false
	}

	/**
	 * Submit to backend
	 *
	 * @return {null}
	 */
	const saveChanges = () => {
		setIsSaving(true)

		dispatch(
			updateVideo({
				id,
				video: {
					playlist: videoPlaylists,
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
									'Playlists of "%s" was successfully updated.',
									'wpmudev_vids'
								),
								video_title
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
							message: sprintf(
								__('Updating “%s” failed.', 'wpmudev_vids'),
								video_title
							),
						})
					)
				}
			})
			.finally(() => {
				setIsSaving(false)
			})
	}

	return (
		<Box>
			<Box.Header className={headerClasses}>
				<IconButton
					outlined={false}
					className="sui-button-float--right"
					onClick={closeModal}
					id={`${modalID}-close`}
					icon="close"
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<Box.Title className="sui-lg" id={`${modalID}-title`}>
					{__('Add to playlist', 'wpmudev_vids')}
				</Box.Title>

				<Box.Description id={`${modalID}-title`}>
					{sprintf(
						__(
							'Choose which playlists you want to add the "%s" video to. It will be available for people to watch if they can access the playlist.',
							'wpmudev_vids'
						),
						video_title
					)}
				</Box.Description>
			</Box.Header>

			<Box.Body>
				<div className="sui-form-field">
					{playlists.map((playlist) => (
						<Checkbox
							id={`${modalID}-playlist-${playlist.id}`}
							className="sui-checkbox-sm"
							isStacked={true}
							checked={isPlaylistChecked(playlist.id)}
							key={playlist.id}
							label={playlist.title}
							onChange={(value) =>
								togglePlaylist(playlist.id, value)
							}
						/>
					))}
				</div>
			</Box.Body>

			<Box.Footer isFlatten={true} isCentered={true}>
				<Button
					color="blue"
					onLoadingText={__('Updating playlist', 'wpmudev_vids')}
					isLoading={isSaving}
					disabled={isSubmitDisabled()}
					onClick={saveChanges}
				>
					{__('Add to Playlist', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
			{/*
			<WhiteLabelBanner
				src="summary/dashboard.png"
				alt={__('Video title', 'wpmudev_vids')}
				imageClassName={imageClasses}
			/>
			*/}
		</Box>
	)
}

export default EditPlaylists
