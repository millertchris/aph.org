/**
 * Library components
 */
import { Box, Icon, Modal } from '../../../../lib/components'
import { useModal } from '../../../../lib/hooks'

/**
 * Internal dependencies
 */
import {
	getVideos,
	getFilteredIds,
	getLoadingStatus,
} from '../../../../store/slices/videos'
import { VideoRow } from '../../components/'
import {
	expiredMember,
	validMember,
	dashConnected,
} from '../../../../helpers/utils'
import {
	DashNotice,
	ExpiredNotice,
	UpgradeNotice,
	GoTopButton,
} from '../../../../components'
import { EditVideo, DeleteVideo, EditTitle, EditPlaylists } from '../../modals/'

/**
 * External Dependencies
 */
import { useState, useEffect } from 'react'
import { useSelector } from 'react-redux'
import classnames from 'classnames'
import { __ } from '@wordpress/i18n'
import ClipboardJS from 'clipboard'
import { useLocation } from 'react-router-dom'

export function VideosBox() {
	const [currentVideo, setCurrentVideo] = useState({})

	// videos list
	const [videos, setVideos] = useState({
		custom: [],
		default: [],
	})

	const [openedId, setOpenedId] = useState()

	const videosObject = useSelector((state) => getVideos(state))

	const filtered = useSelector((state) => getFilteredIds(state))

	const isLoading = useSelector((state) => getLoadingStatus(state))

	const videosHasInitiated = Object.keys(videosObject).length > 0

	// Edit Modal
	const editModalID = 'wpmudev-videos-video-edit-modal'

	const [openEditModal, closeEditModal] = useModal({
		id: editModalID,
	})

	// Delete Modal
	const deleteModalID = 'wpmudev-videos-video-delete-modal'

	const [openDeleteModal, closeDeleteModal] = useModal({
		id: deleteModalID,
	})

	// Title Modal
	const titleModalID = 'wpmudev-videos-video-title-modal'

	const [openTitleModal, closeTitleModal] = useModal({
		id: titleModalID,
	})

	// Playlists Modal
	const playlistsModalID = 'wpmudev-videos-video-playlist-modal'

	const [openPlaylistsModal, closePlaylistsModal] = useModal({
		id: playlistsModalID,
	})

	const location = useLocation()

	/**
	 * Get videos list & update the state
	 *
	 * We will use the filtered list to include
	 * search results.
	 * @return {void}
	 */
	const updateVideosList = () => {
		const videos = {
			default: [],
			custom: [],
		}

		// Get filtered ids in reverse order.
		const filteredCopy = [...filtered]
		const sorted = filteredCopy.sort().reverse()

		sorted.forEach((id) => {
			if (videosObject[id]) {
				const video = videosObject[id]
				// Get the video object.
				if ('default' === video.video_type) {
					// Default videos.
					videos.default.push(video)
				} else {
					// Custom videos.
					videos.custom.push(video)
				}
			}
		})

		setVideos(videos)
	}

	useEffect(() => {
		// Update videos list
		updateVideosList()
	}, [filtered, videosObject])

	const boxClasses = classnames({
		loading: isLoading,
	})

	// Initialize clipboard library
	useEffect(() => {
		new ClipboardJS('.ivt-copy-shortcode')
	}, [])

	/**
	 * Check if current membership needs an upgrade.
	 *
	 * @return {boolean}
	 */
	const shouldUpgrade = () => {
		return !expiredMember() && !validMember() && dashConnected()
	}

	let displayNotices = <></>

	if (shouldUpgrade()) {
		displayNotices = <UpgradeNotice />
	} else if (!shouldUpgrade() && expiredMember()) {
		displayNotices = <ExpiredNotice />
	} else {
		displayNotices = <DashNotice />
	}

	// should disable row for default videos
	const shouldDisableDefault = !validMember() || expiredMember()

	// Handle routes coming from dashboard page
	useEffect(() => {
		if (videosHasInitiated) {
			// regex to match with url pathname
			const pattern = /^(\/(view|edit|playlist|delete))\/(\d+)$/

			const matches = location.pathname.match(pattern)

			if (matches) {
				const [, , action, videoID] = matches

				// View Video: Open correspondant accordion
				if (action === 'view') {
					setOpenedId(Number(videoID))
					return
				}

				// Playlist: Open playlists modal
				if (videosObject[videoID] && action === 'playlist') {
					setCurrentVideo(videosObject[videoID])
					openPlaylistsModal()
					return
				}

				// edit: Open edit modal
				if (videosObject[videoID] && action === 'edit') {
					setCurrentVideo(videosObject[videoID])
					if (videosObject[videoID].video_type === 'custom') {
						// Edit modal for custom videos
						openEditModal()
					} else {
						// Edit title modal for default videos
						openTitleModal()
					}
				}

				// edit: Open delete confirmation modal
				if (videosObject[videoID] && action === 'delete') {
					setCurrentVideo(videosObject[videoID])
					if (videosObject[videoID].video_type === 'custom') {
						openDeleteModal()
					}
				}
			}
		}
	}, [videosHasInitiated])

	return (
		<>
			<Modal id={editModalID} size="lg">
				<EditVideo
					video={currentVideo}
					modalID={editModalID}
					closeModal={closeEditModal}
				/>
			</Modal>

			<Modal id={deleteModalID} size="sm">
				<DeleteVideo
					video={currentVideo}
					modalID={deleteModalID}
					closeModal={closeDeleteModal}
					setCurrentVideo={setCurrentVideo}
				/>
			</Modal>

			<Modal id={titleModalID} size="sm">
				<EditTitle
					video={currentVideo}
					modalID={titleModalID}
					closeModal={closeTitleModal}
					setCurrentVideo={setCurrentVideo}
				/>
			</Modal>

			<Modal id={playlistsModalID} size="sm">
				<EditPlaylists
					video={currentVideo}
					modalID={playlistsModalID}
					closeModal={closePlaylistsModal}
					setCurrentVideo={setCurrentVideo}
				/>
			</Modal>

			<Box id="wpmudev-videos-videos-list" className={boxClasses}>
				<p role="alert" className="loading-text" aria-live="polite">
					<Icon icon="loader" size="md" animate />
					{__('Loading...', 'wpmudev_vids')}
				</p>
				<Box.Header>
					<Box.Title>
						{__('Available Videos', 'wpmudev_vids')}
					</Box.Title>
				</Box.Header>
				<Box.Body>
					<p>
						{__(
							'Add, edit and view video tutorials all in one handy place.',
							'wpmudev_vids'
						)}
					</p>
				</Box.Body>

				<div className="wpmudev-videos-accordion">
					<div
						className="wpmudev-videos-accordion--header"
						aria-hidden="true"
					>
						<div className="wpmudev-videos-accordion--row">
							<p className="wpmudev-videos-accordion--col-large">
								{__('Video title', 'wpmudev_vids')}
							</p>
							<p className="wpmudev-videos-accordion--col-small">
								{__('Duration', 'wpmudev_vids')}
							</p>
						</div>
					</div>

					{/** Custom videos */}
					{videos.custom.length > 0 &&
						videos.custom.map((video) => (
							<VideoRow
								openedId={openedId}
								setOpenedId={setOpenedId}
								key={video.id}
								video={video}
								disabled={false}
								setCurrentVideo={setCurrentVideo}
								openEditModal={openEditModal}
								openDeleteModal={openDeleteModal}
								openPlaylistsModal={openPlaylistsModal}
							/>
						))}

					{/** Show notices */}
					{!validMember() && (
						<div className="wpmudev-videos-accordion--notice">
							{displayNotices}
						</div>
					)}

					{/** Default videos */}
					{videos.default.length > 0 &&
						videos.default.map((video) => (
							<VideoRow
								openedId={openedId}
								setOpenedId={setOpenedId}
								key={video.id}
								video={video}
								disabled={shouldDisableDefault}
								setCurrentVideo={setCurrentVideo}
								openDeleteModal={openDeleteModal}
								openTitleModal={openTitleModal}
								openPlaylistsModal={openPlaylistsModal}
							/>
						))}

					{/** Default videos */}
					<GoTopButton />
				</div>
			</Box>
		</>
	)
}

export default VideosBox
