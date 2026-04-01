/**
 * Library dependencies
 */
import { Icon } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { ListThumb } from '../../../../components'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { useNavigate } from 'react-router-dom'
import { __ } from '@wordpress/i18n'

export function VideoItem({ playlistId, video, selected, setSelected }) {
	const {
		id,
		video_title,
		thumbnail,
		video_type,
		video_slug,
		video_duration,
	} = video

	const navigate = useNavigate()

	/**
	 * Check if current video is the selected one.
	 *
	 * @returns {boolean}
	 */
	const isSelected = () => {
		return (
			playlistId === parseInt(selected.playlist) &&
			id === parseInt(selected.video)
		)
	}

	const url = thumbnail?.url || null

	const showPreview = () => {
		// Change route
		navigate(`/view/${playlistId}/${id}`)

		// Update selected object
		setSelected({
			playlist: playlistId,
			video: id,
		})
	}

	const btnClasses = classnames({
		'wpmudev-videos-section--video': true,
		'wpmudev-videos-active': isSelected(),
	})

	return (
		<button
			role="tab"
			className={btnClasses}
			tabIndex={isSelected() ? false : '-1'}
			aria-selected={isSelected() ? 'true' : 'false'}
			id={`tab--playlist-${playlistId}-video-${id}`}
			aria-controls={`tab-content--playlist-${playlistId}-video-${id}`}
			onClick={showPreview}
		>
			<span className="sui-screen-reader-text">
				{__('Click to open', 'wpmudev_vids')} {video_title}
			</span>
			<span
				className="wpmudev-videos-section--video-head"
				aria-hidden="true"
			>
				<span className="video-thumbnail">
					<span className="video-thumbnail-content">
						<ListThumb
							url={url}
							isCustom={'custom' === video_type}
							icon={video_slug}
							className="video-thumb"
							tag="span"
						/>

						<span className="video-thumbnail-name">
							{video_title}
						</span>
					</span>
				</span>

				<Icon icon="play" size="sm" />
			</span>

			<span
				className="wpmudev-videos-section--video-body"
				aria-hidden="true"
			>
				<span className="video-title">{video_title}</span>

				<span className="video-duration">{video_duration || ''}</span>
			</span>
		</button>
	)
}

export default VideoItem
