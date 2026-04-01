/**
 * Library dependencies
 */
import { StaticNotice } from '../../lib/components'

/**
 * Internal dependencies
 */
import { dashInstalled, dashActive } from '../../helpers/utils'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

export function DashNotice() {
	// Global Variables
	const {
		urls: { dash_install, plugins, dash_login, videos },
	} = window.ivtVars ?? {}

	/**
	 * Get labels and links for the notice.
	 *
	 * Based on the status of WPMUDEV Dash plugin status,
	 * set the labels and links for the notice.
	 *
	 * @return {*}
	 */
	function getLabels() {
		if (!dashInstalled()) {
			return {
				desc: __(
					'WPMU DEV videos are locked and you only have access to custom videos in Video Tutorials page. Install and log into the plugin to start setting up your video tutorials.',
					'wpmudev_vids'
				),
				button: __('Install Plugin', 'wpmudev_vids'),
				link: dash_install,
				target: '_blank',
			}
		} else if (!dashActive()) {
			return {
				desc: __(
					'WPMU DEV videos are locked and you only have access to custom videos in Video Tutorials page. Activate and log into the plugin to start setting up your video tutorials.',
					'wpmudev_vids'
				),
				button: __('Activate Plugin', 'wpmudev_vids'),
				link: plugins,
				target: '_self',
			}
		} else {
			return {
				desc: __(
					'WPMU DEV videos are locked and you only have access to custom videos in Video Tutorials page. Log into the plugin to begin setting up your video tutorials.',
					'wpmudev_vids'
				),
				button: __('Login', 'wpmudev_vids'),
				link: dash_login,
				target: '_self',
			}
		}
	}

	const labels = getLabels()

	return (
		<StaticNotice>
			<p>{labels.desc}</p>

			<p>
				<a
					className="sui-button sui-button-blue"
					target={labels.target}
					href={labels.link}
				>
					{labels.button}
				</a>
			</p>
		</StaticNotice>
	)
}

export default DashNotice
