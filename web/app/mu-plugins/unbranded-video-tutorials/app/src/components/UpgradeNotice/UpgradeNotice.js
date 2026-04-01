/**
 * Library dependencies
 */
import { StaticNotice } from '../../lib/components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

export function UpgradeNotice() {
	return (
		<StaticNotice type="purple">
			<p>
				{__(
					'WPMU DEV Videos are locked due to your current membership status. You can unlock WPMU DEV white-labeled video tutorials by activating a full membership. Click the button below for more information on the WPMU DEV pricing structure.',
					'wpmudev_vids'
				)}
			</p>
			<p>
				<a
					href="https://wpmudev.com/project/unbranded-video-tutorials/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_notice_upgrade"
					target="_blank"
					className="sui-button sui-button-purple"
				>
					{__('Get Full Membership', 'wpmudev_vids')}
				</a>
			</p>
		</StaticNotice>
	)
}

export default UpgradeNotice
