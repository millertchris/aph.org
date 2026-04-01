/**
 * Library dependencies
 */
import { Header, SideNav, TopNotice } from '../../lib/components'

/**
 * Internal dependencies
 */
import { PageFooter } from '../../components'
import { Data } from './tabs/Data'
import { DisplaySettings } from './tabs/DisplaySettings'
import { ImportExport } from './tabs/ImportExport'
import { Permissions } from './tabs/Permissions'
import { getCurrentNotice } from '../../store/slices/notice'
import './styles/main.scss'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * External dependencies
 */
import { useSelector } from 'react-redux'

export function App() {
	const currentNotice = useSelector((state) => getCurrentNotice(state))

	const { id, message, type, dismiss } = currentNotice
	const isMultisite = window.ivtVars.flags.multisite ?? false

	// Global Variables
	const {
		whitelabel: { hide_doc_link },
	} = window.ivtVars ?? {}

	return (
		<div className="sui-wrap">
			<Header
				showDocLink={!hide_doc_link}
				docLink="https://wpmudev.com/docs/wpmu-dev-plugins/integrated-video-tutorials/"
				docText={__('View Documentation', 'wpmudev_vids')}
				title={__('Settings', 'wpmudev_vids')}
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

			<SideNav
				paths={[
					{
						title: __('Display Settings', 'wpmudev_vids'),
						location: '/',
						element: <DisplaySettings />,
					},
					// On multisite we don't need Permissions tab. Only network admin will have access.
					... ! isMultisite ? [
						{
							title: __('Permissions', 'wpmudev_vids'),
							location: '/permissions',
							element: <Permissions />,
						}
					] : [],
					{
						title: __('Import / Export', 'wpmudev_vids'),
						location: '/import-export',
						element: <ImportExport />,
					},
					{
						title: __('Data', 'wpmudev_vids'),
						location: '/data',
						element: <Data />,
					},
				]}
			/>
			<PageFooter />
		</div>
	)
}

export default App
