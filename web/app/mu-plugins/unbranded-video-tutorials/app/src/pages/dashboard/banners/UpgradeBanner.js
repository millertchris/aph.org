/**
 * Library dependencies
 */
import { Box, Button } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { ImageTag } from '../../../components'
import { restGet } from '../../../helpers/api'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from 'react'

// Global variables
const { user_name, whitelabel } = ivtVars ?? {}
const { hide_branding } = whitelabel ?? {}

export function UpgradeBanner() {
	const [isRefreshing, setIsRefreshing] = useState(false)

	/**
	 * Refresh the membership data using API.
	 *
	 * Reload the current page after refreshing the
	 * status.
	 *
	 * @return {null}
	 */
	const refreshData = () => {
		setIsRefreshing(true)

		restGet({
			path: 'actions',
			params: {
				action: 'refresh_membership',
			},
		})
			.then(() => {
				window.location.reload()
			})
			.finally(() => {
				setIsRefreshing(false)
			})
	}

	const banner_title = __('Get Integrated Video Tutorial Access', 'wpmudev_vids');
	return (
		<Box className="wpmudev-videos-box-message no-logo">
			<div className="video-sui-col-11 wpmudev-dashboard-message">
				<h2 className="wpmudev-videos-box-message--title">
				{banner_title}
				</h2>
				
				<Box.Description Tag="p">
					{sprintf(
						__(
							"Hey %s, it looks like you don't have access to the WPMU DEV white-labeled video tutorials due to your current membership status. You can gain access by activating a full membership. Click the button below for more information on the WPMU DEV pricing structure.",
							'wpmudev_vids'
						),
						user_name
					)}
				</Box.Description>
				<p>
				<Button
						onLoadingText={__('Refreshing Status', 'wpmudev_vids')}
						isLoading={isRefreshing}
						type="ghost"
						icon="refresh"
						onClick={refreshData}
					>
						{__('Refresh Status', 'wpmudev_vids')}
					</Button>

					<a
						target="_blank"
						href="https://wpmudev.com/project/unbranded-video-tutorials/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_banner_get"
						className="sui-button sui-button-purple"
					>
						{__('Get Full Membership', 'wpmudev_vids')}
					</a>
				</p>

			</div>
		</Box>
	)
}

export default UpgradeBanner
