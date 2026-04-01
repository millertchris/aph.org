/**
 * Library dependencies
 */
import { Table, Checkbox, Icon } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { ListThumb } from '../../../../components'
import { unAssignVideo } from '../../../../store/slices/videos'
import {
	setPlaylist,
	isVideoSelected,
	toggleVideo,
} from '../../../../store/slices/playlists'

/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import moment from 'moment'
import { __ } from '@wordpress/i18n'
import { useState } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import classnames from 'classnames'

export function VideoRow({ playlist, video }) {
	const dispatch = useDispatch()

	const [isDeleting, setIsDeleting] = useState(false)

	const { id: playlistID } = playlist ?? {}
	const {
		id: videoID,
		video_slug,
		video_title,
		thunmbnail,
		date,
		video_type,
	} = video ?? {}

	const videoSelected = useSelector((state) =>
		isVideoSelected(state, { videoID, playlistID })
	)

	/**
	 * Get the video creation date as per the design.
	 *
	 * @returns {string}
	 */
	const getDate = () => {
		// Only if date set.
		if (date && '' !== date) {
			let formattedDate = moment(date)

			// Format to human time.
			return formattedDate.format('MMMM D/YYYY @ hh:mm A')
		} else {
			return ''
		}
	}

	/**
	 * Remove the deleted video from playlist and update playlists
	 *
	 * @return {void}
	 */
	const removeFromPlaylist = () => {
		const newPlaylist = {
			...playlist,
			videos: playlist.videos.filter((id) => id !== videoID),
		}

		dispatch(setPlaylist({ playlist: newPlaylist }))
	}

	/**
	 * Delete video
	 *
	 * @returns {void}
	 */
	const deleteVideoFromPlaylist = async () => {
		setIsDeleting(true)

		dispatch(unAssignVideo({ playlistID, videoID })).then((response) => {
			if (response?.payload?.success) {
				removeFromPlaylist()
			}
			setIsDeleting(false)
		})
	}

	const deleteButtonClasses = classnames({
		'sui-button-icon': true,
		'sui-tooltip': true,
		'sui-tooltip-top-right': true,
		'sui-button-onload': isDeleting,
	})

	/**
	 * Update selected videos list
	 *
	 * @return {void}
	 */
	const updateVideoSelection = () => {
		dispatch(toggleVideo({ videoID, playlistID }))
	}

	return (
		<Table.Tr>
			<Table.Td className="sui-table-item-title">
				<div className="wpmudev-videos-table--title">
					<Checkbox
						checked={videoSelected}
						onChange={updateVideoSelection}
						label={false}
						id={`wpmudev-playlists-${playlistID}-videos-item-${videoID}`}
					/>
					<ListThumb
						url={thunmbnail?.url}
						icon={video_slug}
						className="playlist-video-thumb"
						isCustom={'custom' === video_type}
					/>
					<span>{video_title}</span>
				</div>
			</Table.Td>
			<Table.Td className="wpmudev-videos-table--date">
				{getDate()}
			</Table.Td>
			<Table.Td className="wpmudev-videos-table--actions">
				<button
					type="button"
					role="button"
					className={deleteButtonClasses}
					data-tooltip={__(
						'Remove video from playlist',
						'wpmudev_vids'
					)}
					onClick={deleteVideoFromPlaylist}
				>
					<span className="sui-loading-text" aria-hidden="true">
						<Icon icon="trash" />
					</span>
					<Icon icon="loader" className="sui-loading" />
					<span className="sui-screen-reader-text">
						{__('Delete', 'wpmudev_vids')}
					</span>
				</button>
			</Table.Td>
		</Table.Tr>
	)
}

VideoRow.propTypes = {
	video: PropTypes.object,
	playlist: PropTypes.object,
}

export default VideoRow
