/**
 * Library dependencies
 */
import { StaticNotice } from '../../../../lib/components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { Interweave } from 'interweave'

/* When no custom videos are found and Dash plugin is inactive */
export function ExpiredNotice() {
	const {
		urls: { videos },
	} = window.ivtVars

	return (
		<StaticNotice type="purple">
			<p>
				<Interweave
					content={sprintf(
						__(
							"WPMU DEV Videos are locked. To continue displaying WPMU DEV's white label tutorial videos to your customers, renew your membership today.",
							'wmpudev_vids'
						),
						videos
					)}
				/>
			</p>
			<p>
				<a
					href="https://wpmudev.com/?utm_source=integrated_video_tutorials&utm_medium=plugin&utm_campaign=integrated_video_tutorials_notice_renew"
					target="_blank"
					className="sui-button sui-button-purple"
				>
					{__('Renew Membership', 'wpmudev_vids')}
				</a>
			</p>
		</StaticNotice>
	)
}

export default ExpiredNotice
