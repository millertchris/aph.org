/**
 * Library dependencies
 */
import { Accordion, Modal } from '../../../../lib/components'
import { useModal } from '../../../../lib/hooks'
import ClipboardJS from 'clipboard'
import { SortableElements } from '../../../../components/'

/**
 * Internal dependencies
 */
import {
	getPlaylists,
	getFiltered,
	getLoadingStatus,
	orderChange,
} from '../../../../store/slices/playlists'
import { PlaylistItem } from './PlaylistItem'
import {
	EditPlaylist,
	VisibilitySettings,
	AddVideos,
	DeletePlaylist,
} from '../../modals'
import { PlaylistsLoader } from '../'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'
import { useState, useEffect } from 'react'
import { useLocation } from 'react-router-dom'

export function Playlists() {
	// dispatch function
	const dispatch = useDispatch()

	// Filtered List
	const [filteredList, setFilteredList] = useState([])

	// Playlist being edited
	const [currentPlaylist, setCurrentPlaylist] = useState({})

	// Checked playlists and videos
	const [selected, setSelected] = useState({})

	// playlists object
	const playlists = useSelector((state) => getPlaylists(state))

	// playlist ids to filter playlists
	const filtered = useSelector((state) => getFiltered(state))

	// Loading state
	const isLoading = useSelector((state) => getLoadingStatus(state))

	// Edit Modal
	const editModalID = 'wpmudev-videos-playlist-edit-modal'
	const [openEditModal, closeEditModal] = useModal({
		id: editModalID,
		onClose: () => setCurrentPlaylist({}),
	})

	// Visibility Modal
	const visibilityModalID = 'wpmudev-videos-playlist-settings-modal'
	const [openVisibilityModal, closeVisibilityModal] = useModal({
		id: visibilityModalID,
		onClose: () => setCurrentPlaylist({}),
	})

	// Videos Modal
	const videosModalID = 'wpmudev-videos-playlist-videos-modal'
	const [openVideosModal, closeVideosModal] = useModal({
		id: videosModalID,
		onClose: () => setCurrentPlaylist({}),
	})

	// Delete Modal
	const deleteModalID = 'wpmudev-videos-playlist-delete-modal'
	const [openDeleteModal, closeDeleteModal] = useModal({
		id: deleteModalID,
		onClose: () => setCurrentPlaylist({}),
	})

	useEffect(() => {
		const newFilteredList = []

		// Only if filtered is an array.
		if (Array.isArray(filtered)) {
			filtered.forEach((id) => {
				if (playlists[id]) {
					newFilteredList.push(playlists[id])
				}
			})
		}

		setFilteredList(newFilteredList)
	}, [playlists, filtered])

	// Initialize clipboard library
	useEffect(() => {
		new ClipboardJS('.ivt-copy-shortcode')
	}, [])

	/**
	 * Handle order change of the playlist.
	 *
	 * @param {object} event Event
	 *
	 */
	const onChangeOrder = (event) => {
		// Get moved playlist ID.
		const playlistID = event.item.getAttribute('data-id')

		dispatch(
			orderChange({
				playlistID,
				oldIndex: event.oldIndex,
				newIndex: event.newIndex,
			})
		)
	}

	const playlistsHasInitiated = filteredList.length > 0

	const location = useLocation()

	// Handling routes coming from dashboard page
	useEffect(() => {
		if (playlistsHasInitiated) {
			const { pathname } = location

			// regex to match with url pathname for multiple actions
			const pattern = /^(\/(videos|edit|settings))\/(\d+)$/

			const matches = pathname.match(pattern)

			if (matches) {
				const [, , action, playlistID] = matches

				// videos: open add videos model
				if (playlists[playlistID] && action === 'videos') {
					setCurrentPlaylist(playlists[playlistID])
					openVideosModal()
					return
				}
				// edit: Open edit modal
				if (playlists[playlistID] && action === 'edit') {
					setCurrentPlaylist(playlists[playlistID])
					openEditModal()
				}

				// settings: Open visibility settings modal
				if (playlists[playlistID] && action === 'settings') {
					setCurrentPlaylist(playlists[playlistID])
					openVisibilityModal()
				}
			}
		}
	}, [playlistsHasInitiated])

	return (
		<>
			{/* Edit Modal  */}
			<Modal size="lg" id={editModalID}>
				<EditPlaylist
					modalID={editModalID}
					openModal={openEditModal}
					closeModal={closeEditModal}
					playlist={currentPlaylist}
					setCurrentPlaylist={setCurrentPlaylist}
				/>
			</Modal>

			{/* Visibility Modal */}
			<Modal id={visibilityModalID} size="lg">
				<VisibilitySettings
					modalID={visibilityModalID}
					closeModal={closeVisibilityModal}
					playlist={currentPlaylist}
					setCurrentPlaylist={setCurrentPlaylist}
				/>
			</Modal>

			{/* AddVideo Modal */}
			<Modal id={videosModalID} size="lg">
				<AddVideos
					modalID={videosModalID}
					closeModal={closeVideosModal}
					playlist={currentPlaylist}
					setCurrentPlaylist={setCurrentPlaylist}
				/>
			</Modal>

			{/* Delete Modal */}
			<Modal id={deleteModalID} size="sm">
				<DeletePlaylist
					modalID={deleteModalID}
					closeModal={closeDeleteModal}
					playlist={currentPlaylist}
				/>
			</Modal>

			{/** Loading component */}
			{isLoading && <PlaylistsLoader />}

			{!isLoading && (
				<Accordion>
					<SortableElements onUpdate={onChangeOrder}>
						{filteredList?.map((playlist) => (
							<PlaylistItem
								key={playlist.id}
								openEditModal={openEditModal}
								openVisibilityModal={openVisibilityModal}
								openVideosModal={openVideosModal}
								openDeleteModal={openDeleteModal}
								playlist={playlist}
								selected={selected}
								setSelected={setSelected}
								setCurrentPlaylist={setCurrentPlaylist}
							/>
						))}
					</SortableElements>
				</Accordion>
			)}
		</>
	)
}

export default Playlists
