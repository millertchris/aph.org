/**
 * Library dependencies
 */
import { Box, Icon, Table, IconButton } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { getPlaylists } from '../../../../store/slices/playlists'
import { ListThumb } from '../../../../components'
import { addNotice } from '../../../../store/slices/notice'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useSelector, useDispatch } from 'react-redux'
import { useEffect, useState } from 'react'
import ClipboardJS from 'clipboard'

// Global variables
const { urls } = ivtVars ?? {}
const { playlists: playlistsPageUrl } = urls

export function Playlists() {
	const dispatch = useDispatch()

	const playlists = useSelector((state) => getPlaylists(state))

	const [sortedPlaylists, setSortedPlaylists] = useState([])

	// Initialize clipboard library
	useEffect(() => {
		new ClipboardJS('.ivt-copy-shortcode')
	}, [])

	// Sort the playlists
	useEffect(() => {
		// Init empty array.
		const sorted = []

		Object.keys(playlists)
			.sort() // Sort.
			.reverse() // Reverse the order.
			.forEach(function (id) {
				sorted.push(playlists[id])
			})

		setSortedPlaylists(sorted)
	}, [playlists])

	/**
	 * Displays a notice that shortcode was copied successfully
	 *
	 * @return {void}
	 */
	const displayCopyNotice = () => {
		dispatch(
			addNotice({
				message: __(
					'Shortcode has been copied successfully.',
					'wpmudev_vids'
				),
			})
		)
	}

	const hasPlaylists = sortedPlaylists.length > 0

	return (
		<Box>
			<Box.Header>
				<Box.Title>
					<Icon
						icon="playlist"
						className="wpmudev-videos-custom-icon"
					/>
					{__('Playlists', 'wpmudev_vids')}
				</Box.Title>
			</Box.Header>
			<Box.Body>
				<p>
					{__(
						'Create playlists of videos and display them around your WordPress Admin area to help your clients get to know WordPress.',
						'wpmudev_vids'
					)}
				</p>
				{/** Add condition */}
				{!hasPlaylists && (
					<p>
						<a
							role="button"
							className="sui-button sui-button-blue"
							href={`${playlistsPageUrl}#/create`}
						>
							<Icon icon="plus" />
							{__('Add Playlist', 'wpmudev_vids')}
						</a>
					</p>
				)}
			</Box.Body>

			{hasPlaylists && (
				<Table isFlushed={true} className="wpmudev-videos-box-table">
					<Table.Thead>
						<Table.Tr>
							<Table.Th className="wpmudev-videos-row--name">
								{__('Recently added playlists', 'wpmudev_vids')}
							</Table.Th>
							<Table.Th className="wpmudev-videos-row--options">
								<span className="sui-screen-reader-text">
									{__(
										'Playlist options menu',
										'wpmudev_vids'
									)}
								</span>
							</Table.Th>
						</Table.Tr>
					</Table.Thead>
					<Table.Tbody>
						{sortedPlaylists.map((playlist) => (
							<Table.Tr key={playlist.id}>
								<Table.Td className="sui-table-item-title wpmudev-videos-row--name">
									<div className="wpmudev-videos-name-wrapper">
										<ListThumb
											url={playlist.thumbnail?.url}
											hideIcon={true}
											className="playlist-thumb"
										/>
										<a
											href={`${playlistsPageUrl}#/edit/${playlist.id}`}
										>
											{playlist.title}
										</a>
									</div>
								</Table.Td>
								<Table.Td className="wpmudev-videos-name-wrapper">
									<div className="sui-dropdown">
										<IconButton
											className="sui-dropdown-anchor"
											icon="widget-settings-config"
											outlined={false}
											label={__(
												'Actions',
												'wpmudev_vids'
											)}
											aria-label={__(
												'Actions',
												'wpmudev_vids'
											)}
										/>

										<ul>
											<li>
												<a
													href={`${playlistsPageUrl}#/edit/${playlist.id}`}
												>
													<Icon icon="pencil" />
													{__('Edit', 'wpmudev_vids')}
												</a>
											</li>
											<li>
												<a
													href={`${playlistsPageUrl}#/videos/${playlist.id}`}
												>
													<Icon icon="align-justify" />
													{__(
														'Add videos',
														'wpmudev_vids'
													)}
												</a>
											</li>
											<li>
												<a
													href={`${playlistsPageUrl}#/settings/${playlist.id}`}
												>
													<Icon icon="widget-settings-config" />
													{__(
														'Visibility settings',
														'wpmudev_vids'
													)}
												</a>
											</li>
											<li>
												<button
													className="ivt-copy-shortcode"
													onClick={displayCopyNotice}
													data-clipboard-text={`[wpmudev-video group="${playlist.slug}"]`}
												>
													<Icon icon="code" />
													{__(
														'Copy shortcode',
														'wpmudev_vids'
													)}
												</button>
											</li>
										</ul>
									</div>
								</Table.Td>
							</Table.Tr>
						))}
					</Table.Tbody>
				</Table>
			)}

			{hasPlaylists && (
				<Box.Footer>
					<a
						role="button"
						className="sui-button sui-button-blue"
						href={`${playlistsPageUrl}#/create`}
					>
						<Icon icon="plus" />
						{__('Add Playlist', 'wpmudev_vids')}
					</a>

					<div className="sui-actions-right">
						<a
							role="button"
							className="sui-button sui-button-ghost"
							href={playlistsPageUrl}
						>
							<Icon icon="eye" />
							{__('View All', 'wpmudev_vids')}
						</a>
					</div>
				</Box.Footer>
			)}
		</Box>
	)
}

export default Playlists
