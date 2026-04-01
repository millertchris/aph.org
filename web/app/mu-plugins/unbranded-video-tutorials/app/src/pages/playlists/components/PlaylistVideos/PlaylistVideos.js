/**
 * Library dependencies
 */
import { Table, Box, Button } from '../../../../lib/components'
import { getVideos } from '../../../../store/slices/videos'

/**
 * Internal dependencies
 */
import { VideoRow } from './VideoRow'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import PropTypes from 'prop-types'
import { useSelector } from 'react-redux'

export function PlaylistVideos({
	playlist,
	setCurrentPlaylist,
	openVideosModal,
}) {
	const { videos: ids } = playlist

	const videos = useSelector((state) => getVideos(state, ids))

	const hasVideos = !!videos && videos.length > 0

	return (
		<Box>
			<Table>
				<Table.Thead>
					<Table.Tr>
						<Table.Th className="sui-table-item-title">
							{__('Video title', 'wpmudev_vids')}
						</Table.Th>
						<Table.Th className="wpmudev-videos-table--date">
							{__('Date added', 'wpmudev_vids')}
						</Table.Th>
						<Table.Th className="wpmudev-videos-table--actions">
							<span className="sui-screen-reader-text">
								{__('Row actions', 'wpmudev_vids')}
							</span>
						</Table.Th>
					</Table.Tr>
				</Table.Thead>

				<Table.Tbody>
					{hasVideos ? (
						videos.map((video) => (
							<VideoRow
								key={video.id}
								playlist={playlist}
								video={video}
							/>
						))
					) : (
						<Table.Tr>
							<Table.Td colSpan="3">
								{__('No videos assigned.', 'wpmudev_vids')}
							</Table.Td>
						</Table.Tr>
					)}
				</Table.Tbody>

				<Table.Tfoot>
					<Table.Tr>
						<Table.Td>
							<Button
								onClick={() => {
									setCurrentPlaylist(playlist)
									openVideosModal()
								}}
								color="blue"
								icon="plus"
							>
								{__('Add Video', 'wpmudev_vids')}
							</Button>
						</Table.Td>
					</Table.Tr>
				</Table.Tfoot>
			</Table>
		</Box>
	)
}

PlaylistVideos.defaultProps = {
	playlist: {},
	openVisibilityModal: () => null,
	setCurrentPlaylist: () => null,
}

PlaylistVideos.propTypes = {
	playlist: PropTypes.object,
	openVisibilityModal: PropTypes.func,
	setCurrentPlaylist: PropTypes.func,
}

export default PlaylistVideos
