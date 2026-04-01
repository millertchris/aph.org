/**
 * Internal dependencies
 */
import { getVideos } from '../../../../store/slices/videos'
import { VideoRowHeader } from './VideoRowHeader'
import { VideoRowBody } from './VideoRowBody'
/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'

export function VideoRow({
	video,
	openedId,
	setOpenedId,
	disabled,
	openEditModal,
	setCurrentVideo,
	openDeleteModal,
	openTitleModal,
	openPlaylistsModal,
}) {
	const { id } = video ?? {}

	const classes = classnames({
		'wpmudev-videos-accordion--group': true,
		'wpmudev-videos-active': openedId === id,
		'wpmudev-videos-disabled': disabled,
	})

	const isOpened = id === openedId

	return (
		<div className={classes}>
			{/** Accordion Header */}
			<VideoRowHeader
				openedId={openedId}
				setOpenedId={setOpenedId}
				video={video}
				disabled={disabled}
				openEditModal={openEditModal}
				setCurrentVideo={setCurrentVideo}
				openDeleteModal={openDeleteModal}
				openTitleModal={openTitleModal}
				openPlaylistsModal={openPlaylistsModal}
			/>

			{/** Accordion Body */}
			{isOpened && (
				<VideoRowBody
					setOpenedId={setOpenedId}
					openedId={openedId}
					video={video}
					disabled={disabled}
				/>
			)}
		</div>
	)
}

VideoRow.propTypes = {
	video: PropTypes.object,
	openedId: PropTypes.number,
	setOpenedId: PropTypes.func,
}

export default VideoRow
