/**
 * Library dependencies
 */
import { Table, Button } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { ListThumb } from '../../../../components'

/**
 * External dependencies
 */
import { useState } from 'react'
import { __ } from '@wordpress/i18n'

export function VideoListRow({ video, playlist, setCurrentPlaylist }) {
	const [buttonIsHovered, setButtonIsHovered] = useState(false)

	let { videos } = playlist ?? {}

	const { id, video_slug, thumbnail, video_title, video_type } = video ?? {}

	const { url } = thumbnail ?? {}

	/**
	 * Check if the current video is already added.
	 *
	 * @returns {boolean}
	 */
	const isAdded = () => {
		return videos?.includes(id)
	}

	/**
	 * Remove a video from the playlist.
	 *
	 * @returns {void}
	 */
	const removeVideo = () => {
		const newPlaylist = {
			...playlist,
			videos: videos.filter((id) => id !== video.id),
		}

		setCurrentPlaylist(newPlaylist)
	}

	/**
	 * Adds the video to the playlist
	 *
	 * @returns {void}
	 */
	const addVideo = () => {
		const newPlaylist = {
			...playlist,
		}

		if (!videos?.includes(video?.id)) {
			newPlaylist['videos'] = [...videos, video?.id]
		}

		setCurrentPlaylist(newPlaylist)
	}

	return (
		<Table.Tr aria-live="polite">
			<Table.Td className="wpmudev-videos-table-videos--cell-left sui-table-item-title">
				<ListThumb
					url={url}
					icon={video_slug}
					className="playlist-video-thumb-wide"
					isCustom={'custom' === video_type}
				/>
				{video_title}
			</Table.Td>

			{/* on mobile only  */}
			{isAdded() && (
				<Table.Td className="wpmudev-videos-table-videos--cell-right wpmudev-videos-table-videos--show-mobiles">
					<Button disabled={true} icon="check">
						{__('Added', 'wpmudev_vids')}
					</Button>
					<Button
						icon="trash"
						color="red"
						type="ghost"
						onClick={removeVideo}
					>
						{__('Remove', 'wpmudev_vids')}
					</Button>
				</Table.Td>
			)}

			{/* Desktop */}
			{isAdded() && (
				<Table.Td className="wpmudev-videos-table-videos--cell-right wpmudev-videos-table-videos--show-desktop">
					{buttonIsHovered && (
						<Button
							onMouseLeave={() => setButtonIsHovered(false)}
							color="red"
							icon="trash"
							onClick={removeVideo}
						>
							{__('Remove', 'wpmudev_vids')}
						</Button>
					)}

					{!buttonIsHovered && (
						<Button
							onMouseEnter={() => setButtonIsHovered(true)}
							icon="check"
						>
							{__('Added', 'wpmudev_vids')}
						</Button>
					)}
				</Table.Td>
			)}

			{/* Default */}
			{!isAdded() && (
				<Table.Td className="wpmudev-videos-table-videos--cell-right">
					<Button onClick={addVideo} icon="plus" type="ghost">
						{__('Add', 'wpmudev_vids')}
					</Button>
				</Table.Td>
			)}
		</Table.Tr>
	)
}

export default VideoListRow
