/**
 * Library dependencies
 */
import { Header, Button, TopNotice, Modal } from '../../lib/components'
import { useModal } from '../../lib/hooks'

/**
 * Internal dependencies
 */
import { getCurrentNotice } from '../../store/slices/notice'
import { VideosBox, SummaryBox, EmptyBox } from './partials'
import { initPlaylists } from '../../store/slices/playlists'
import { initVideos } from '../../store/slices/videos'
import { PageFooter } from '../../components'
import { CreateCustomVideo } from './modals'
import { getFilteredIds } from '../../store/slices/videos'
import './styles/main.scss'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'
import { useEffect, useState } from 'react'
import { useLocation } from 'react-router-dom'
import { __ } from '@wordpress/i18n'

export function App() {
	const [search, setSearch] = useState('')

	const filtered = useSelector((state) => getFilteredIds(state))

	const currentNotice = useSelector((state) => getCurrentNotice(state))

	const dispatch = useDispatch()

	// Notice Data
	const { id, message, type, dismiss } = currentNotice

	// Add Custom Video Modal
	const customVideoModalId = 'wpmudev-videos-video-create-modal'

	const [openVideoModal, closeVideoModal] = useModal({
		id: customVideoModalId,
	})

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

	// Handeling routes coming from dashbaord
	useEffect(() => {
		const { pathname } = location

		if (pathname.slice(1) === 'create') {
			openVideoModal()
		}
	}, [])

	return (
		<div className="sui-wrap">
			{/** Add Custom Video Modal */}
			<Modal size="lg" id={customVideoModalId}>
				<CreateCustomVideo
					closeModal={closeVideoModal}
					modalID={customVideoModalId}
				/>
			</Modal>

			<Header
				showDocLink={!hide_doc_link}
				docLink="https://wpmudev.com/docs/wpmu-dev-plugins/integrated-video-tutorials/"
				docText={__('View Documentation', 'wpmudev_vids')}
				title={__('Videos', 'wpmudev_vids')}
				actionsLeft={
					<Button icon="plus" color="blue" onClick={openVideoModal}>
						{__('Add Custom Video', 'wpmudev_vids')}
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
			<SummaryBox search={search} setSearch={setSearch} />

			{/** When Search is empty */}
			{search && filtered.length === 0 && <EmptyBox search={search} />}

			{/* Videos Box */}
			{filtered.length != 0 && <VideosBox />}

			{/** Footer */}
			<PageFooter />
		</div>
	)
}

export default App
