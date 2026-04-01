/**
 * Internal dependencies
 */
import { UploadThumb } from '../../../../components'
import TimeForm from './TimeForm'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

export function VideoSettings({ video, setVideo, modalID }) {
	const { thumbnail } = video ?? {}

	/**
	 * Set Video Thumbnial
	 *
	 * @return {void}
	 */
	const setVideoThumbnail = (value) => {
		setVideo({
			...video,
			thumbnail: value,
		})
	}

	return (
		<div className="sui-box-body">
			<p className="sui-settings-label">
				{__('Settings', 'wpmudev_vids')}
			</p>

			<p className="sui-description">
				{__('Customize the duration of the video.', 'wpmudev_vids')}
			</p>

			<TimeForm modalID={modalID} video={video} setVideo={setVideo} />

			<p className="sui-settings-label"></p>

			<p className="sui-settings-label">
				{__('Thumbnail image', 'wpmudev_vids')}
			</p>

			<p className="sui-description">
				{__('Add a custom thumbnail to your video.', 'wpmudev_vids')}
			</p>
			<div className="sui-form-field">
				<UploadThumb
					thumbnail={Array.isArray(thumbnail) ? {} : thumbnail}
					onSelect={setVideoThumbnail}
				/>
			</div>
		</div>
	)
}
export default VideoSettings
