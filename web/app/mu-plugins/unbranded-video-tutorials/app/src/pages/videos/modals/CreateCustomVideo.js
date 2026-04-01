/**
 * Internal dependencies
 */
import { VideoHost, VideoUrl, VideoPlaylists } from './slides'

/**
 * External dependencies
 */
import { useState } from 'react'

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

export function CreateCustomVideo({ modalID, closeModal }) {
	const [video, setVideo] = useState(emptyVideo)

	// This variable is used to check if the video url is valid
	const [urlReady, setUrlReady] = useState(false)

	/**
	 * Close the modal and resets Video State
	 *
	 * @return {null}
	 */
	const closeAndReset = () => {
		// set url
		setUrlReady(false)

		// Reset Video State
		setVideo(emptyVideo)

		// Close the modal
		closeModal()
	}

	return (
		<>
			<VideoHost
				video={video}
				setVideo={setVideo}
				modalID={modalID}
				closeModal={closeAndReset}
				next={`${modalID}-url-form`}
			/>

			<VideoUrl
				video={video}
				setVideo={setVideo}
				modalID={modalID}
				closeModal={closeAndReset}
				prev={`${modalID}-host-selector`}
				next={`${modalID}-playlist-form`}
				urlReady={urlReady}
				setUrlReady={setUrlReady}
			/>

			<VideoPlaylists
				video={video}
				setVideo={setVideo}
				prev={`${modalID}-url-form`}
				modalID={modalID}
				closeModal={closeAndReset}
				emptyVideo={emptyVideo}
				setUrlReady={setUrlReady}
			/>
		</>
	)
}

export default CreateCustomVideo
