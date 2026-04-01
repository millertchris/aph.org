/**
 * Library dependencies
 */
import { Icon } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { VideoIframe } from '../../../../components'
import { getVideos } from '../../../../store/slices/videos'
import { restGet } from '../../../../helpers/api'

/**
 * External dependencies
 */
import { useState, useEffect } from 'react'
import { useSelector } from 'react-redux'
import { useNavigate } from 'react-router-dom'
import { __ } from '@wordpress/i18n'

export function VideoPlayer({ videoId, playlistId, isSelected, setSelected }) {
	const [embed, setEmbed] = useState()
	const [isUpdating, setIsUpdating] = useState(false)

	const video = useSelector((state) => getVideos(state, videoId))

	const { id, video_title, video_type } = video ?? {}

	const navigate = useNavigate()

	// Get embed on mount and when video id changes
	useEffect(() => {
		if (id) {
			getEmbed()
		}
	}, [id])

	/**
	 * get embed.
	 *
	 * We need to pass the custom thumbnail and
	 * start/end time strings.
	 *
	 */
	const getEmbed = async () => {
		// return if already updating.
		if (isUpdating) {
			return
		}

		setIsUpdating(true)

		restGet({
			path: `videos/${id}/embed`,
		}).then((response) => {
			if (response.success && response.data) {
				// Update the embed.
				setEmbed(response.data)

				setIsUpdating(false)
			}
		})
	}

	/**
	 * Handle the video preview hide event.
	 */
	const hidePreview = () => {
		navigate('/')

		setSelected({
			playlist: '',
			video: '',
		})
	}

	return (
		<div
			role="tabpanel"
			className="wpmudev-videos-section--videos-panel"
			aria-live="polite"
			tabindex="0"
			hidden={!isSelected}
			id={`tab-content--playlist-${playlistId}-video-${id}`}
			aria-labelledby={
				isSelected ? `tab--playlist-${playlistId}-video-${id}` : ''
			}
		>
			{isSelected && (
				<>
					<h3 className="video-title">{video_title}</h3>
					<VideoIframe embed={embed?.html} type={video_type} />

					<button className="video-close" onClick={hidePreview}>
						<Icon icon="close" size="sm" />
						<span className="sui-screen-reader-text">
							{__('Close video', 'wpmudev_vids')}
						</span>
					</button>
				</>
			)}
		</div>
	)
}

export default VideoPlayer
