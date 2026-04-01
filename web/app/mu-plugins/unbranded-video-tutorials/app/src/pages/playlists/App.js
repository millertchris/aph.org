/**
 * Library dependencies
 */
import { Header, Button, TopNotice, Modal } from '../../lib/components'
import { useModal } from '../../lib/hooks'

/**
 * Internal dependencies
 */
import { getCurrentNotice } from '../../store/slices/notice'
import { SummaryBox, Playlists } from './partials'
import { expiredMember, validMember, dashConnected } from '../../helpers/utils/'
import { initPlaylists } from '../../store/slices/playlists'
import { initVideos } from '../../store/slices/videos'
import './styles/main.scss'
import { CreatePlaylist } from './modals'
import { BulkActions } from './partials/BulkActions'
import { PageFooter } from '../../components'

import { UpgradeNotice, ExpiredNotice, DashNotice } from './components'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'
import { useEffect } from 'react'
import { useLocation } from 'react-router-dom'
import { __ } from '@wordpress/i18n'

export function App() {
	const currentNotice = useSelector((state) => getCurrentNotice(state))

	const dispatch = useDispatch()

	// Notice Data
	const { id, message, type, dismiss } = currentNotice

	// CreatePlaylist Modal
	const playlistModalID = 'wpmudev-videos-playlist-create-modal'

	const [openPlaylistModal, closePlaylistModal] = useModal({
		id: playlistModalID,
	})

	/**
	 * Check if current membership needs an upgrade.
	 *
	 * @return {boolean}
	 */
	function shouldUpgrade() {
		return !expiredMember() && !validMember() && dashConnected()
	}

	/**
	 * Load data on mount
	 */
	useEffect(() => {
		// Load videos
		dispatch(initVideos())

		// Load Playlists
		dispatch(initPlaylists())
	}, [])

	// Global Variables
	const {
		whitelabel: { hide_doc_link },
	} = window.ivtVars ?? {}

	const location = useLocation()

	// Handling routes coming from dashboard
	useEffect(() => {
		const { pathname } = location

		if (pathname.slice(1) === 'create') {
			openPlaylistModal()
		}
	}, [])

	return (
		<div className="sui-wrap">
			{/** Create new playlist modals  */}
			<Modal size="lg" id={playlistModalID}>
				<CreatePlaylist
					closeModal={closePlaylistModal}
					modalID={playlistModalID}
				/>
			</Modal>
			<Header
				showDocLink={!hide_doc_link}
				docLink="https://wpmudev.com/docs/wpmu-dev-plugins/integrated-video-tutorials/"
				docText={__('View Documentation', 'wpmudev_vids')}
				title={__('Playlists', 'wpmudev_vids')}
				actionsLeft={
					<Button
						onClick={openPlaylistModal}
						icon="plus"
						color="blue"
					>
						{__('Create Playlist', 'wpmudev_vids')}
					</Button>
				}
			/>
			{/* Displays Notifications */}
			<div className="sui-floating-notices">
				<TopNotice
					id={id}
					dismiss={dismiss}
					message={message}
					type={type}
					label={__('Dismiss this notice', 'wpmudev_vids')}
				/>
			</div>
			{/* Summary Box */}
			<SummaryBox />

			{/** Notices */}
			{shouldUpgrade() && <UpgradeNotice />}
			{!shouldUpgrade() && expiredMember() && <ExpiredNotice />}
			{!shouldUpgrade() && !expiredMember() && !validMember() && (
				<DashNotice />
			)}
			{/** Bulk Actions */}
			<BulkActions />
			{/** Playlists */}
			<Playlists />
			{/** Footer */}
			<PageFooter />
		</div>
	)
}

export default App
