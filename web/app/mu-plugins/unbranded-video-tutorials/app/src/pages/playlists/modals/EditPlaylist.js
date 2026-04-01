/**
 * Library dependencies
 */
import { Label, Box, Button, IconButton } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { savePlaylist, getLoadingStatus } from '../../../store/slices/playlists'
import { addNotice } from '../../../store/slices/notice'
import { PlaylistTitle, PlaylistDesc } from '../components'

/**
 * External dependencies
 */
import { sprintf, __ } from '@wordpress/i18n'
import { useDispatch, useSelector } from 'react-redux'
import { UploadThumb } from '../../../components'

export function EditPlaylist({
	playlist,
	modalID,
	closeModal,
	setCurrentPlaylist,
}) {
	const dispatch = useDispatch()

	const isSaving = useSelector((state) => getLoadingStatus(state))

	const { title, description, thumbnail } = playlist ?? {}

	/**
	 * Updates playlist title
	 *
	 * @return {void}
	 */
	const updatePlaylistTitle = (value) => {
		setCurrentPlaylist({ ...playlist, title: value })
	}

	/**
	 * Updates playlist description
	 *
	 * @return {void}
	 */
	const updatePlaylistDesc = (value) => {
		setCurrentPlaylist({ ...playlist, description: value })
	}

	/**
	 * Updates playlist thumbnail
	 *
	 * @return {void}
	 */
	const updatePlaylistThumb = (value) => {
		setCurrentPlaylist({ ...playlist, thumbnail: value })
	}

	/**
	 * Save Changes
	 *
	 * @return {void}
	 */
	const saveChanges = () => {
		const { id, title, description, thumbnail } = playlist ?? {}

		dispatch(
			savePlaylist({
				id,
				playlist: {
					title,
					description,
					thumbnail: thumbnail.id,
				},
			})
		).then((response) => {
			if (response?.payload?.success) {
				// Close the modal
				closeModal()

				// Show Succssful Notice
				dispatch(
					addNotice({
						message: sprintf(
							__(
								'Playlist “%s” updated successfully.',
								'wpmudev_vids'
							),
							response?.payload?.data?.title
						),
					})
				)
			} else {
				// Show Error Notice
				dispatch(
					addNotice({
						message: __(
							'Updating playlist failed..',
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
			<Box.Header>
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={closeModal}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={`${modalID}-title`} className="sui-box-title">
					{__('Edit Playlist', 'wpmudev_vids')}
				</h3>
			</Box.Header>
			<Box.Body>
				<Box.Row>
					<Box.Col2>
						<PlaylistTitle
							title={title}
							onChange={updatePlaylistTitle}
							modalID={modalID}
						/>

						<PlaylistDesc
							description={description}
							onChange={updatePlaylistDesc}
							modalID={modalID}
						/>
					</Box.Col2>
				</Box.Row>
				<Box.Row>
					<Box.Col2>
						<div className="sui-form-field wpmudev-videos-field--thumbnail">
							<Label>
								{__('Playlist thumbnail image', 'wpmudev_vids')}
							</Label>
							<p className="sui-description">
								{__(
									"Add a custom thumbnail to your playlist, otherwise we'll just use the first video's thumbnail.",
									'wpmudev_vids'
								)}
							</p>
						</div>
						<UploadThumb
							thumbnail={
								Array.isArray(thumbnail) ? {} : thumbnail
							}
							onSelect={updatePlaylistThumb}
							modalID={modalID}
						/>
					</Box.Col2>
				</Box.Row>
			</Box.Body>
			<Box.Footer isFlatten={true} isCentered={true}>
				<Button
					color="blue"
					isLoading={isSaving}
					disabled={isSaving}
					onLoadingText={__('Saving Changes', 'wpmudev_vids')}
					onClick={saveChanges}
				>
					{__('Save Changes', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default EditPlaylist
