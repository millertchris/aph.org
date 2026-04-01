/**
 * Library components
 */
import { Box, Button, IconButton } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { addNotice } from '../../../store/slices/notice'
import {
	deletePlaylist,
	getLoadingStatus,
} from '../../../store/slices/playlists'

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import classnames from 'classnames'
import { useSelector, useDispatch } from 'react-redux'

export function DeletePlaylist({ playlist, closeModal, modalID }) {
	const { id } = playlist

	const dispatch = useDispatch()

	const isDeleting = useSelector((state) => getLoadingStatus(state))

	const boxHeaderClasses = classnames(
		'sui-box-header',
		'sui-flatten',
		'sui-content-center',
		'sui-spacing-top--60'
	)

	const videosCount = playlist?.videos?.length || 0

	/**
	 * Delete the playlist
	 *
	 * @return {void}
	 */
	const onDelete = () => {
		dispatch(deletePlaylist({ id })).then((response) => {
			const { payload } = response

			if (payload?.success === true) {
				// close the modal
				closeModal()

				// Show success notice
				dispatch(
					addNotice({
						message: __(
							'Playlist deleted successfully.',
							'wpmudev_vids'
						),
					})
				)
			} else {
				// Show error notice
				dispatch(
					addNotice({
						message: response.payload.data,
						type: 'error',
					})
				)
			}
		})
	}

	return (
		<Box>
			<Box.Header className={boxHeaderClasses}>
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={closeModal}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={modalID} className="sui-box-title sui-lg">
					{__('Delete playlist', 'wpmudev_vids')}
				</h3>

				<p id={`${modalID}-desc`} className="sui-description">
					{sprintf(
						__(
							'This playlist contains %d video(s). These videos will be unassigned from this playlist. Are you sure you want to delete this playlist?'
						),
						videosCount
					)}
				</p>
			</Box.Header>
			<Box.Footer isFlatten={true} isCentered={true}>
				<Button type="ghost" onClick={closeModal}>
					{__('Cancel', 'wpmudev_vids')}
				</Button>

				<Button
					id={`${modalID}-submit`}
					color="red"
					onClick={onDelete}
					onLoadingText={__('Deleting', 'wpmudev_vids')}
					disabled={isDeleting}
					isLoading={isDeleting}
					type="ghost"
				>
					{__('Delete', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default DeletePlaylist
