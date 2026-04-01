/**
 * Internal dependencies
 */
import {
	getPlaylists,
	getLoadingStatus as getPlaylistsLoading,
	getFiltered,
} from '../../../../store/slices/playlists'
import { getFilteredIds, getVideos } from '../../../../store/slices/videos'
import { getLoadingStatus } from '../../../../store/slices/videos'
import { MenuLoader, VideosLoader } from '../../components'
import { StickyMenu } from '../../components/'
import { SinglePlaylist } from '../../components'
import { validMember } from '../../../../helpers/utils'

/**
 * External dependencies
 */
import { useSelector } from 'react-redux'
import { useState, useEffect } from 'react'
import { __, sprintf } from '@wordpress/i18n'
import classnames from 'classnames'
import { useParams } from 'react-router-dom'

export function VideosList({ searchMsgType, hideMessage, resultsCount }) {
	// Url parameters for current opened video
	const { action, playlist, video } = useParams()

	// Selcted video state based on url params
	const [selected, setSelected] = useState({
		playlist: '',
		video: '',
	})

	// We always use sorted playlists
	const [playlistsLists, setPlaylistsLists] = useState([])

	// Playlists to display in the sticky menu
	const [displayedPlaylists, setDisplayedPlaylists] = useState([])

	// Playlists object from the store
	const playlists = useSelector((state) => getPlaylists(state))

	// Filtered playlists ids
	const filteredPlaylists = useSelector((state) => getFiltered(state))

	// VideosObject from the store
	const videosObject = useSelector((state) => getVideos(state))

	// Filtered videos ids from the store
	const filteredVideosIds = useSelector((state) => getFilteredIds(state))

	const areVideosLoading = useSelector((state) => getLoadingStatus(state))

	const arePlaylistsLoading = useSelector((state) =>
		getPlaylistsLoading(state)
	)

	// When both videos and playlists are loading
	const isLoading = areVideosLoading && arePlaylistsLoading

	// To show default videos or not
	const showDefault = validMember()

	// Set selected state based on url params
	useEffect(() => {
		if (action !== '' && parseInt(playlist) > 0 && parseInt(video) > 0) {
			setSelected({
				playlist,
				video,
			})
		}
	}, [])

	// Set playlistsLists on mount
	useEffect(() => {
		const newPlaylists = []

		filteredPlaylists.forEach((id) => {
			if (playlists[id]) {
				newPlaylists.push(playlists[id])
			}
		})
		setPlaylistsLists(newPlaylists)
	}, [playlists, filteredPlaylists])

	// Set displayedPlaylists on mount
	useEffect(() => {
		const filtered = []

		// Only if there're videos to display
		if (filteredVideosIds.length > 0) {
			// Loop through all sorted playlists
			playlistsLists.forEach((playlist) => {
				// Videos ids that can be displayed based on certain conditions
				const displayedVideos = []

				if (playlist.videos && playlist.videos.length > 0) {
					// Loop through each video id in the playlist
					playlist.videos.forEach((videoID) => {
						// Check if video is available in filtered list.
						if (filteredVideosIds.includes(videoID)) {
							{
								// Check if the use is authorized to see the video
								if (
									videosObject[videoID].video_type ===
										'custom' ||
									(videosObject[videoID].video_type ===
										'default' &&
										showDefault)
								) {
									displayedVideos.push(videoID)
								}
							}
						}
					})
				}
				// Add the playlist with the new filtered videos if it has at least one video that fullfill the conditions
				if (displayedVideos.length > 0) {
					filtered.push({
						...playlist,
						videos: displayedVideos,
					})
				}
			})
		}

		// Update the state
		setDisplayedPlaylists(filtered)
	}, [filteredVideosIds, playlistsLists, filteredVideosIds])

	const wrapperClasses = classnames({
		'wpmudev-videos-section': true,
		loading: isLoading,
	})

	return (
		<div className={wrapperClasses}>
			{isLoading && <MenuLoader />}
			{!isLoading && <StickyMenu playlists={displayedPlaylists} />}

			<div
				role="alert"
				aria-live="polite"
				className="sui-screen-reader-text"
				hidden={hideMessage}
			>
				{!hideMessage && searchMsgType === 'clear' && (
					<p>{__('Search cleared successfully.', 'wpmudev_vids')}</p>
				)}

				{!hideMessage && searchMsgType === 'search' && (
					<p>
						{sprintf(
							__(
								'We found %s videos for your search.',
								'wpmudev_vids'
							),
							resultsCount
						)}
					</p>
				)}
			</div>

			{isLoading && <VideosLoader />}

			{!isLoading &&
				displayedPlaylists.map((playlist) => (
					<SinglePlaylist
						selected={selected}
						setSelected={setSelected}
						key={playlist.id}
						playlist={playlist}
					/>
				))}
		</div>
	)
}

export default VideosList
