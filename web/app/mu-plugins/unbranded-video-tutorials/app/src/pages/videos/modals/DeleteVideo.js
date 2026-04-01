/**
 * Library dependencies
 */
import { Box, Button, IconButton } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { deleteVideo, initVideos } from '../../../store/slices/videos'
import { addNotice } from '../../../store/slices/notice'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useDispatch } from 'react-redux'
import { useState } from 'react'

export function DeleteVideo({ modal, video, closeModal }) {
	const dispatch = useDispatch()
	const [isDeleting, setIsDeleting] = useState(false)

	const { id } = video ?? {}

	/**
	 * Deletes the video upon confirmation
	 *
	 * @return {void}
	 */
	const onDelete = () => {
		setIsDeleting(true)

		dispatch(
			deleteVideo({
				id,
			})
		)
			.then((response) => {
				if (response?.payload?.success) {
					// Close the modal
					closeModal()

					//Add the notice
					dispatch(
						addNotice({
							message: __(
								'Video deleted successfully.',
								'wpmudev_vids'
							),
						})
					)
				} else {
					// Display Error message
					dispatch(
						addNotice({
							type: 'error',
							message: response?.error?.message,
						})
					)
				}
			})
			.finally(() => {
				setIsDeleting(false)
			})
	}

	return (
		<Box>
			<Box.Header className="sui-flatten sui-content-center sui-spacing-top--60">
				<IconButton
					outlined={false}
					className="sui-button-float--right"
					onClick={closeModal}
					id={`${modal}-close`}
					icon="close"
					label={__('Close this dialog.', 'wpmudev_vids')}
					tooltip={false}
				/>
				<Box.Title className="sui-lg" id={`${modal}-title`}>
					{__('Delete video', 'wpmudev_vids')}
				</Box.Title>

				<Box.Description id={`${modal}-title`}>
					{__(
						'Are you sure you want to delete the video?',
						'wpmudev_vids'
					)}
				</Box.Description>
			</Box.Header>

			<Box.Footer isFlatten={true} isCentered={true}>
				<Button
					onClick={closeModal}
					id={`${modal}-submit`}
					type="ghost"
				>
					{__('Cancel', 'wpmudev_vids')}
				</Button>
				<Button
					id={`${modal}-submit`}
					type="ghost"
					color="red"
					isLoading={isDeleting}
					onLoadingText={__('Deleting', 'wpmudev_vids')}
					onClick={onDelete}
				>
					{__('Delete', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default DeleteVideo
