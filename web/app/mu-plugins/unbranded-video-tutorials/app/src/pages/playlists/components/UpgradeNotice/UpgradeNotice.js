/**
 * Library dependencies
 */
import { StaticNotice } from '../../../../lib/components'

/**
 * External dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { Interweave } from 'interweave'

/* When membership is not valid for videos */
export function UpgradeNotice() {
	const {
		urls: { videos },
	} = window.ivtVars
	return (
		<StaticNotice type="purple">
			<p>
				{__(
					"You don't have any custom videos available to add to this playlist. You can unlock WPMU DEV white-labeled video tutorials by activating a full membership. Click the button below for more information on the WPMU DEV pricing structure.",
					'wpmudev_vids'
				)}
			</p>
			<p style={{ display: 'none' }}>
				<Interweave
					content={sprintf(
						__(
							'<strong>Note:</strong> You can still <a href="%s">create and add custom videos</a> to this playlist.',
							'wmpudev_vids'
						),
						videos
					)}
				/>
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
