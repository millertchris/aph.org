/**
 * Library dependencies
 */
import { Box } from '../../../../lib/components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

export function Header() {
	return (
		<Box.Header>
			<Box.Title id="wpmudev-videos-navigation--label">
				{__('Video Tutorials', 'wpmudev_vids')}
			</Box.Title>
		</Box.Header>
	)
}

export default Header
