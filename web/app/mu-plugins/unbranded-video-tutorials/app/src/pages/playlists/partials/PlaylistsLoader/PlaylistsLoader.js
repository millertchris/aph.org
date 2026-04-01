/**
 * Library dependencies
 */
import { Icon, IconButton } from '../../../../lib/components'

/**
 * External components
 */
import { __ } from '@wordpress/i18n'

export function PlaylistsLoader() {
	return (
		<div className="wpmudev-videos-loader sui-box">
			<div className="wpmudev-videos-loader--mask">
				<Icon icon="loader" animate={true}></Icon>
				{__('Loading...', 'wpmudev_vids')}
			</div>

			<div className="wpmudev-videos-loader--content" aria-hidden="true">
				<div className="wpmudev-videos-loader--checkbox"></div>

				<div className="wpmudev-videos-loader--image"></div>

				<div className="wpmudev-videos-loader--name">
					<span></span>
				</div>

				<div className="wpmudev-videos-loader--actions">
					<IconButton tag="div" outlined={false}>
						<span
							className="sui-icon-widget-settings-config"
							aria-hidden="true"
						></span>
					</IconButton>

					<IconButton tag="div" outlined={false}>
						<span
							className="sui-icon-chevron-down"
							aria-hidden="true"
						></span>
					</IconButton>
				</div>
			</div>
		</div>
	)
}

export default PlaylistsLoader
