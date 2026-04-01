/**
 * Library dependencies
 */
import { Header, TopNotice, Modal } from '../../lib/components'
import { useModal } from '../../lib/hooks'

/**
 * Internal dependencies
 */
import { getCurrentNotice } from '../../store/slices/notice'
import { SummaryBox, Videos, Playlists } from './partials'
import { initPlaylists } from '../../store/slices/playlists'
import { ExpiredBanner, UpgradeBanner, DashBanner } from './banners'
import { initVideos } from '../../store/slices/videos'
import { validMember, expiredMember, dashConnected, dashActive } from '../../helpers/utils'
import { PageFooter } from '../../components'
import { Highlights, ExpiredModal, UpgradeModal, DashModal } from './modals'
import { getOption } from '../../store/slices/settings'
import './styles/main.scss'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'

// Global Variables
const {
	whitelabel: { hide_doc_link },
} = window.ivtVars ?? {}

export function App() {
	const dispatch = useDispatch()

	const currentNotice = useSelector((state) => getCurrentNotice(state))

	// Notice Data
	const { id, message, type, dismiss } = currentNotice

	const dismissWelcomeNotice = useSelector((state) =>
		getOption(state, 'dismiss_welcome_notice', 0)
	)

	const dismissDashNotice = useSelector((state) =>
		getOption(state, 'dismiss_dash_notice', 0)
	)

	/**
	 * Check if current membership needs an upgrade.
	 *
	 * @return {boolean}
	 */
	const shouldUpgrade = () => {
		return !expiredMember() && !validMember() && dashConnected()
	}

	/**
	 * Check if welcome modal is required.
	 *
	 * @return {boolean}
	 */
	const showWelcome = () => {
		return dismissWelcomeNotice <= 0
	}

	/**
	 * Check if dash modal can be shown.
	 *
	 * @return {boolean}
	 */
	const canShowModal = () => {
		return dismissDashNotice <= 0
	}

	/**
	 * Check if Dash plugin modal is required.
	 *
	 * @return {boolean}
	 */
	const showDashModal = () => {
		return !validMember() && canShowModal()
	}

	/**
	 * Check if we need to show expired membership modal.
	 *
	 * @return {boolean}
	 */
	const showExpiredModal = () => {
		return expiredMember() && dashActive() && canShowModal()
	}

	/**
	 * Check if we need to show upgrade membership modal.
	 *
	 * @return {boolean}
	 */
	const showUpgradeModal = () => {
		return shouldUpgrade() && canShowModal()
	}

	/**
	 * Load data on mount
	 */
	useEffect(() => {
		// Load videos
		dispatch(initVideos({ count: 5, type: validMember() ? '' : 'custom' }))

		// Load Playlists
		dispatch(initPlaylists({ count: 5 }))
	}, [])

	// Highlights Modal
	const highlightsModalId = 'wpmudev-videos-welcome-highlight'

	const [openHighlightsModal, closeHighlightsModal] = useModal({
		id: highlightsModalId,
	})

	// Expired Modal
	const expiredModalId = 'wpmudev-videos-expired-account'

	const [openExpiredModal, closeExpiredModal] = useModal({
		id: expiredModalId,
	})

	// Upgrade Modal
	const upgradeModalId = 'wpmudev-videos-upgrade-account'

	const [openUpgradeModal, closeUpgradeModal] = useModal({
		id: upgradeModalId,
	})

	// Dash Modal
	const dashModalId = 'wpmudev-videos-install-activate'

	const [openDashModal, closeDashModal] = useModal({
		id: dashModalId,
	})

	// Open modals according to conditions
	useEffect(() => {
		if (showWelcome()) {
			openHighlightsModal()
		} else if (showExpiredModal()) {
			openExpiredModal()
		} else if (showUpgradeModal()) {
			openUpgradeModal()
		} else if (showDashModal()) {
			openDashModal()
		}
	}, [])

	return (
		<div className="sui-wrap">
			<Modal id={highlightsModalId}>
				<Highlights
					modalID={highlightsModalId}
					closeModal={closeHighlightsModal}
				/>
			</Modal>
			<Modal id={expiredModalId}>
				<ExpiredModal
					modalID={expiredModalId}
					closeModal={closeExpiredModal}
				/>
			</Modal>
			<Modal id={upgradeModalId}>
				<UpgradeModal
					modalID={upgradeModalId}
					closeModal={closeUpgradeModal}
				/>
			</Modal>
			<Modal id={dashModalId}>
				<DashModal modalID={dashModalId} closeModal={closeDashModal} />
			</Modal>
			<Header
				showDocLink={!hide_doc_link}
				docLink="https://wpmudev.com/docs/wpmu-dev-plugins/integrated-video-tutorials/"
				docText={__('View Documentation', 'wpmudev_vids')}
				title={__('Dashboard', 'wpmudev_vids')}
			/>
			{/* Expired Memebership Banner */}
			{expiredMember() && <ExpiredBanner />}
			{/** Upgrade membership banner */}
			{!expiredMember() && shouldUpgrade() && <UpgradeBanner />}
			{/** Dash plugin install banner */}
			{!(!expiredMember() && shouldUpgrade()) && !validMember() && (
				<DashBanner />
			)}
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
			{/** Videos and Playlists */}
			<div className="sui-row">
				<div className="sui-col-md-6">
					{/* Videos section */}
					<Videos />
					{/* Videos end */}
				</div>

				<div className="sui-col-md-6">
					{/* Playlists section */}
					<Playlists />
					{/* playlists end */}
				</div>
			</div>
			{/** Footer */}
			<PageFooter />
		</div>
	)
}

export default App
