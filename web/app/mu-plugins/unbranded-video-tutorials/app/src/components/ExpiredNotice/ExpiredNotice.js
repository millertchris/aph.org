/**
 * Library dependencies
 */
import { StaticNotice } from '../../lib/components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

export function ExpiredNotice() {
	return (
		<StaticNotice type="purple">
			<p>
				{__(
					"WPMU DEV Videos are locked. To continue displaying WPMU DEV's white label tutorial videos to your customers, renew your membership today.",
					'wpmudev_vids'
				)}
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
