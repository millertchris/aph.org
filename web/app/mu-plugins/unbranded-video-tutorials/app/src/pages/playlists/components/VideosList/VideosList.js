/**
 * Library dependencies
 */
import { Table } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { getFilteredIds, getVideos } from '../../../../store/slices/videos'
import { VideoListRow } from '../'
import { dashConnected, validMember } from '../../../../helpers/utils'

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { useSelector } from 'react-redux'
import { useEffect, useState } from 'react'
import PropTypes from 'prop-types'

export function VideosList({ playlist, setVideoCount, setCurrentPlaylist }) {
	// list of added videos to the playlist
	const { videos } = playlist ?? {}

	// Number of videos added
	const { length } = videos ?? []

	// Displayed count
	const [displayedCount, setDisplayedCount] = useState(0)

	// Filtered Videos Ids
	const filteredIds = useSelector((state) => getFilteredIds(state))

	// Videos objects list
	const filteredVideos = useSelector((state) => getVideos(state, filteredIds))

	// Videos Provided for members
	const defaultVideos = filteredVideos.filter(
		(video) => video.video_type === 'default'
	)

	// Custom videos added by admin
	const customVideos = filteredVideos.filter(
		(video) => video.video_type !== 'default'
	)

	// Update displayed count
	useEffect(() => {
		let count = length

		if (!showDefaultVideos()) {
			// Videos without defaultVideos
			const displayedVideos = videos?.filter(
				(item) => !defaultVideos.some((obj) => obj.id === item)
			)

			// Count without default videos
			count = displayedVideos?.length

			setDisplayedCount(count)
		} else {
			setDisplayedCount(count)
		}
	}, [videos])

	// Update videos count variable
	useEffect(() => {
		let count = 0
		if (showDefaultVideos()) {
			count = videos?.default?.length + videos?.custom?.length
			setVideoCount(count)
		} else {
			count = videos?.custom?.length
			setVideoCount(count)
		}
	}, [defaultVideos, customVideos])

	/**
	 * Check if current membership is valid.
	 *
	 * Default videos are available only if current
	 * membership is valid.
	 *
	 * @return {boolean}
	 */
	const showDefaultVideos = () => {
		return validMember() && dashConnected()
	}

	/**
	 * Check if videos are not empty.
	 *
	 * @since 1.8.2
	 *
	 * @return {boolean}
	 */
	const videosAvailable = () => {
		return (
			(defaultVideos.length && showDefaultVideos()) || customVideos.length
		)
	}

	return (
		<>
			{/* Display table only if there're videos available  */}
			{videosAvailable() ? (
				<Table isFlushed={true} className="wpmudev-videos-table-videos">
					<Table.Thead>
						<Table.Tr>
							<Table.Th className="wpmudev-videos-table-videos--cell-left">
								{__('Videos', 'wpmudev_vids')}
							</Table.Th>
							<Table.Th
								className="wpmudev-videos-table-videos--cell-right"
								aria-live="assertive"
							>
								{sprintf(
									__('%s videos selected', 'wpmudev_vids'),
									displayedCount
								)}
							</Table.Th>
						</Table.Tr>
					</Table.Thead>
					<Table.Tbody>
						{/* Display Custom Videos  */}
						{customVideos.map((video) => (
							<VideoListRow
								playlist={playlist}
								key={video.id}
								video={video}
								setCurrentPlaylist={setCurrentPlaylist}
							/>
						))}

						{/* Display Default videos when membership is valid  */}
						{showDefaultVideos() &&
							defaultVideos.map((video) => (
								<VideoListRow
									playlist={playlist}
									key={video.id}
									video={video}
									setCurrentPlaylist={setCurrentPlaylist}
								/>
							))}
					</Table.Tbody>
				</Table>
			) : (
				// Display Nothing if there're no videos
				''
			)}
		</>
	)
}

VideosList.defaultProps = {
	playlist: {},
	setVideoCount: () => null,
	setCurrentPlaylist: () => null,
}

VideosList.propTypes = {
	playlist: PropTypes.object,
	setVideoCount: PropTypes.func,
	setCurrentPlaylist: PropTypes.func,
}

export default VideosList
