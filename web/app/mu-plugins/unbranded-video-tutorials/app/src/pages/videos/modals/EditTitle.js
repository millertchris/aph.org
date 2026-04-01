/**
 * Libraries dependecies
 */
import { Box, IconButton, Input, Button } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { updateVideo } from '../../../store/slices/videos'
import { addNotice } from '../../../store/slices/notice'

/**
 * Libraries dependecies
 */
import classnames from 'classnames'
import { __ } from '@wordpress/i18n'
import { useState } from 'react'
import { WhiteLabelBanner } from '../../../components'
import { useDispatch } from 'react-redux'

export function EditTitle({ modalID, closeModal, video, setCurrentVideo }) {
	const [isSaving, setIsSaving] = useState(false)

	const dispatch = useDispatch()

	const headerClasses = classnames(
		'sui-flatten',
		'sui-content-center',
		'sui-spacing-top--60'
	)

	const imageClasses = classnames('sui-image', 'sui-image-center')

	const { id, video_title } = video ?? {}

	/**
	 * On title change
	 *
	 * @return {void}
	 */
	const onTitleChange = (value) => {
		setCurrentVideo({
			...video,
			video_title: value,
		})
	}

	/**
	 * saveChanges
	 *
	 * @return {void}
	 */
	const saveChanges = () => {
		setIsSaving(true)

		dispatch(
			updateVideo({
				id,
				video: {
					title: video_title,
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
									'Video title is changed to “%s”.',
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
							message: __(
								'Sorry. Could not update the video title.',
								'wpmudev_vids'
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
					{__('Video title', 'wpmudev_vids')}
				</Box.Title>

				<Box.Description id={`${modalID}-title`}>
					{__('Change the title of the video below.', 'wpmudev_vids')}
				</Box.Description>

				<div className="sui-form-field">
					<Input
						id="wpmudev-videos-video-title-form-title-input"
						placeholder={__('Video title', 'wpmudev_vids')}
						value={video_title}
						onChange={onTitleChange}
					/>
				</div>

				<Button
					color="blue"
					onLoadingText={__('Saving Changes', 'wpmudev_vids')}
					isLoading={isSaving}
					onClick={saveChanges}
				>
					{__('Save Changes', 'wpmudev_vids')}
				</Button>
			</Box.Header>

			<Box.Footer isFlatten={true} isCentered={true} />
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

export default EditTitle
