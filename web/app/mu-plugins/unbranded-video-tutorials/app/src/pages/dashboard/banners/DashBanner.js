/**
 * Library dependencies
 */
import { Box, Icon } from '../../../lib/components'

/**
 * Internal dependencies
 */
import { dashInstalled, dashActive, dashConnected } from '../../../helpers/utils'
import { ImageTag } from '../../../components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Use setState
 */
import { useState } from 'react'

// Global variables
const { urls, whitelabel } = window.ivtVars ?? {}
const { plugins, dash_login } = urls ?? {}
const { hide_branding } = whitelabel ?? {}

export function DashBanner() {
	const [showLogo, setShowLogo] = useState(false)

	if ( dashInstalled() && dashActive() && dashConnected() ) {
		return
	}

	const disconnectedTitle = __('Activate WPMU DEV Dashboard', 'wpmudev_vids');
	
	/**
	 * Get labels and links for the banner.
	 *
	 * Based on the status of WPMUDEV Dash plugin status,
	 * set the labels and links for the banner.
	 *
	 * @return {object}
	 */
	const getLabels = () => {
		if (!dashInstalled()) {
			return {
				title: __('Install WPMU DEV Dashboard', 'wpmudev_vids'),
				desc: __(
					"You don't have the WPMU DEV Dashboard plugin, which you'll need to access the videos API. WPMU DEV videos are locked, and you only have access to custom videos on the Video Tutorials page. Install and log in to the dashboard to unlock the complete library of WPMU DEV video tutorials.",
					'wpmudev_vids'
				),
				button: __('Install Plugin', 'wpmudev_vids'),
				link: 'https://wpmudev.com/project/wpmu-dev-dashboard/',
				target: '_blank',
			}
		} else if (!dashActive()) {
			return {
				title: disconnectedTitle,
				desc: __(
					'The WPMU DEV Dashboard plugin is installed but not activated. Activate the plugin to unlock the complete library of WPMU DEV video tutorials.',
					'wpmudev_vids'
				),
				button: __('Activate Plugin', 'wpmudev_vids'),
				link: plugins,
				target: '_self',
			}
		} else {
			if ( ! dashConnected() ) {
				if ( ! showLogo ) {
					setShowLogo( true );
				}

				return {
					title: __('Login to WPMU DEV Dashboard', 'wpmudev_vids'),
					desc: __(
						"You haven't logged into the WPMU DEV Dashboard plugin installed, which you'll need to access the videos API. WPMU DEV videos are locked, and you only have access to custom videos on the Video Tutorials page. Log into the WPMU DEV dashboard to unlock the complete library of WPMU DEV video tutorials.",
						'wpmudev_vids'
					),
					button: __('Login into Dashboard', 'wpmudev_vids'),
					link: dash_login,
					target: '_self',
				}
			}
		}
	}

	return (
		<Box className={`wpmudev-videos-box-message ${showLogo ? 'video-sui-row' : 'no-logo' }`}>
			{showLogo && (
				<div className='video-sui-col-1 wpmudev-dashboard-logo'>
				{!hide_branding && (
					<ImageTag
						src="banner/wpmudev-logo.png"
						alt={''}
						className="sui-image sui-image-center"
					/>
				)}
				</div>
			)}

			<div className="video-sui-col-11 wpmudev-dashboard-message">
				<h2 className="wpmudev-videos-box-message--title">
					{getLabels().title}
				</h2>
				<p className="sui-description">{getLabels().desc}</p>

				<p>
					<a
						className="sui-button sui-button-blue"
						href={getLabels().link}
						target={getLabels().target}
					>
						{!showLogo && <Icon icon="wpmudev-logo" />}
						{getLabels().button}
					</a>
				</p>
			</div>
		</Box>
	)
}

export default DashBanner
