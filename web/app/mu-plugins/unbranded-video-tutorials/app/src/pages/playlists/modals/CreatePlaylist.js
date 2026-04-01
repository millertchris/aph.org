/**
 * Internal dependencies
 */
import { PlaylistSettings, PlaylistVideos } from './slides'

/**
 * External dependencies
 */
import { useState } from 'react'

// Playlist data structure
const emptyPlaylist = {
	title: '',
	description: '',
	videos: [],
	locations: [],
	thumbnail: {
		id: 0,
		file: '',
		url: '',
	},
	type: 'custom',
}

export function CreatePlaylist({ modalID, closeModal }) {
	const [playlist, setPlaylist] = useState(emptyPlaylist)

	const [errorMessage, setErrorMessage] = useState('')

	/**
	 * close and resets modal state
	 *
	 * @return {void}
	 */
	const closeAndReset = () => {
		// Reset state
		setPlaylist(emptyPlaylist)
		setErrorMessage('')

		// Close the modal
		closeModal()
	}

	return (
		<>
			<PlaylistSettings
				modalID={modalID}
				closeModal={closeAndReset}
				playlist={playlist}
				setPlaylist={setPlaylist}
			/>

			<PlaylistVideos
				modalID={modalID}
				playlist={playlist}
				setPlaylist={setPlaylist}
				closeModal={closeAndReset}
				errorMessage={errorMessage}
				setErrorMessage={setErrorMessage}
			/>
		</>
	)
}

export default CreatePlaylist
