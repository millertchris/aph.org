/**
 * Library components
 */
import { Box, Button } from '../../../lib/components'

/**
 * Internal components
 */
import { ImageTag } from '../../../components'
import { restGet } from '../../../helpers/api'

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { useState } from 'react'

// Global variables
const { user_name, whitelabel } = ivtVars ?? {}
const { hide_branding } = whitelabel ?? {}

export function ExpiredBanner() {
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

	const banner_title = __('Membership Expired', 'wpmudev_vids');

	return (

		<Box className="wpmudev-videos-box-message no-logo">
			<div className="video-sui-col-11 wpmudev-dashboard-message">
				<h2 className="wpmudev-videos-box-message--title">
				{banner_title}
				</h2>
				
				<Box.Description Tag="p">
					{sprintf(
						__(
							"%s, welcome to Integrated Video Tutorials - the best video tutorials plugin for WordPress. It looks like your WPMU DEV subscription has expired. To continue displaying WPMU DEV's white label tutorial videos to your users, renew your membership today.",
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
						target="__blank"
						href="https://wpmudev.com/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_banner_renew"
						className="sui-button sui-button-purple"
					>
						{__('Renew Membership', 'wpmudev_vids')}
					</a>
				</p>

			</div>
		</Box>
	)
}

export default ExpiredBanner
