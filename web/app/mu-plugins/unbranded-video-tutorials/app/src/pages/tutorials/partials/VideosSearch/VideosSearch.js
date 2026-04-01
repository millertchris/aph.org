/**
 * Library dependencies
 */
import { Box } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { VideoSearchForm } from '../../../../components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'

export function VideosSearch({ search, setSearch, onSearch, onClear }) {
	return (
		<Box.Body>
			<p>
				{__(
					'Use these in-depth video tutorials to master all aspects of the WordPress platform.',
					'wpmudev_vids'
				)}
			</p>
			<VideoSearchForm
				search={search}
				onSearch={onSearch}
				onClear={onClear}
				setSearch={setSearch}
				onLoadingText={__('Searching', 'wpmudev_vids')}
			/>
		</Box.Body>
	)
}

export default VideosSearch
