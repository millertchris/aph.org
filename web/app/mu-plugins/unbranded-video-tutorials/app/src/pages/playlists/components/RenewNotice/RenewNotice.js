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
export function RenewNotice() {
	const {
		urls: { videos },
	} = window.ivtVars
	return (
		<StaticNotice type="purple">
			<p>
				{__(
					"You don't have any custom videos available to add to this playlist. To unlock WPMU DEV tutorial videos you'll need to renew your membership. ",
					'wpmudev_vids'
				)}

				<Interweave
					content={sprintf(
						__(
							'Note: You can still <a href="%s">create and add custom videos</a> to this playlist.',
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
					{__('Renew Membership', 'wpmudev_vids')}
				</a>
			</p>
		</StaticNotice>
	)
}

export default RenewNotice
