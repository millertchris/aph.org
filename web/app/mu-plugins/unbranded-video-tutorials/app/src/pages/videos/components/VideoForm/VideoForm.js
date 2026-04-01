/**
 * Library dependencies
 */
import { Input } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { restGet } from '../../../../helpers/api'
import { VideoUrlField, VideoSettings } from '../'
import { VideoIframe } from '../../../../components'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { useState, useEffect } from 'react'
import { sprintf, __ } from '@wordpress/i18n'

// Empty video data structure
const emptyVideo = {
	host: '',
	url: '',
	title: '',
	start_enabled: 0,
	end_enabled: 0,
	start_time: '',
	end_time: '',
	playlists: [],
	thumbnail: {
		id: 0,
		file: '',
		url: '',
	},
	duration: '',
	embed: '',
}

export function VideoForm({
	video,
	setVideo,
	setShowError,
	modalID,
	urlReady,
	setUrlReady,
}) {
	const [updating, setUpdating] = useState(false)

	const selectorsClasses = classnames(
		'sui-box-selectors',
		'sui-box-selectors-col-2'
	)

	const {
		title,
		url,
		host,
		thumbnail,
		start_enabled,
		end_enabled,
		start_time,
		end_time,
		embed,
		id,
		duration,
	} = video ?? {}

	// Get url embed
	useEffect(() => {
		if (urlReady) {
			getUrlEmbed()
		}
	}, [id])

	/**
	 * Resets video state but keep needed data
	 *
	 * @return {void}
	 */
	const reset = () => {
		setVideo({
			...emptyVideo,
			host: video.host,
			id: video.id,
			playlists: video.palylists,
		})
	}

	/**
	 * Update url in the video state
	 *
	 * @return {void}
	 */
	const setVideoUrl = (value) => {
		setVideo({
			...video,
			url: value,
		})
	}

	/**
	 * Set video title
	 *
	 * @return {void}
	 */
	const setVideoTitle = (value) => {
		setVideo({
			...video,
			title: value,
		})
	}

	/**
	 * Update the video data using embed.
	 *
	 * Once the embed is updated, set that
	 * to video object.
	 *
	 */
	const setVideoEmbed = (data) => {
		let newVideo = { ...video }

		// Set the embed.
		newVideo.embed = data.html

		// Update the title.
		if (data.title && video.title === '') {
			newVideo.title = data.title
		}

		// Set the duration.
		if (data.duration) {
			newVideo.duration = data.duration
		}

		setVideo(newVideo)
	}

	/**
	 * Update the embed using the URL.
	 *
	 * We need to pass the custom thumbnail and
	 * start/end time strings.
	 *
	 */
	const getUrlEmbed = () => {
		if (updating || url === '') {
			return
		}

		setUpdating(true)

		restGet({
			path: 'videos/embed',
			params: {
				url,
				host,
				width: 300,
				thumbnail: thumbnail?.id || 0,
				start_enabled,
				end_enabled,
				start_time,
				end_time,
			},
		})
			.then((response) => {
				if (response.success && response.data) {
					// Update the embed.
					setVideoEmbed(response.data)
					setUrlReady(true)
					setShowError(false)
				} else {
					setShowError(false)
				}
			})
			.catch((err) => {
				setUrlReady(false)
				setShowError(true)
			})
			.finally(() => {
				setUpdating(false)
			})
	}

	/**
	 * Should we show video duration.
	 *
	 * @since 1.8.0
	 *
	 * @return {boolean}
	 */
	const showDuration = () => {
		return !['', '00.00', 0, '0'].includes(duration)
	}

	return (
		<>
			{/** Video Url Section */}
			<div className={selectorsClasses}>
				<div className="sui-form-field">
					<VideoUrlField
						url={url}
						updating={updating}
						urlReady={urlReady}
						setVideoUrl={setVideoUrl}
						getUrlEmbed={getUrlEmbed}
						reset={() => {
							setUrlReady(false)
							setShowError(false)
							reset()
						}}
					/>
				</div>

				{/** VideoIframe title and duration */}
				{urlReady && (
					<div className="sui-row">
						{/* Video Iframe */}
						<div className="sui-col-md-6 sui-col-sm-12">
							<VideoIframe embed={embed} type="custom" />
						</div>

						{/* Video Title & Duration */}
						<div className="sui-col-md-6 sui-col-sm-12">
							<div className="sui-form-field">
								<Input
									value={title}
									onChange={setVideoTitle}
									id={`${modalID}-video-title`}
								/>

								{showDuration() && (
									<span className="sui-description">
										{sprintf(
											__('Duration %s', 'wpmudev_vids'),
											duration
										)}
									</span>
								)}
							</div>
						</div>
					</div>
				)}
			</div>

			{/** Display Settings Section */}
			{urlReady && (
				<VideoSettings
					video={video}
					setVideo={setVideo}
					modalID={modalID}
				/>
			)}
		</>
	)
}

VideoForm.defaultProps = {
	video: {},
	setShowError: () => null,
}

export default VideoForm
