/**
 * Library dependencies
 */
import { Box, Icon, Table, IconButton } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { getVideos, getFilteredIds } from '../../../../store/slices/videos'
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
const { videos: videosPageUrl } = urls

export function Videos() {
	const videosObject = useSelector((state) => getVideos(state))

	const filtered = useSelector((state) => getFilteredIds(state))

	const [videosList, setVideosList] = useState([])

	const dispatch = useDispatch()

	// Initialize clipboard library
	useEffect(() => {
		new ClipboardJS('.ivt-copy-shortcode')
	}, [])

	// Sort videos
	useEffect(() => {
		// Init empty array.
		const videos = []

		// Only if it's an array.
		if (Array.isArray(filtered)) {
			// Get filtered ids in reverse order.
			const sorted = [...filtered].sort().reverse()

			sorted.forEach((id) => {
				if (videosObject[id]) {
					// Get the video object.
					videos.push(videosObject[id])
				}
			})
		}

		setVideosList(videos)
	}, [videosObject, filtered])

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

	/**
	 * Get shortcode of the current video.
	 *
	 * @return {string}
	 */
	const getShortCode = (video) => {
		if (video.video_type === 'custom') {
			return `[wpmudev-video video="${video.id}"]`
		} else {
			return `[wpmudev-video video="${video.video_slug}"]`
		}
	}

	const hasVideos = videosList.length > 0

	return (
		<Box>
			<Box.Header>
				<Box.Title>
					<Icon
						icon="animation-video"
						className="wpmudev-videos-custom-icon"
					/>
					{__('Videos', 'wpmudev_vids')}
				</Box.Title>
			</Box.Header>
			<Box.Body>
				<p>
					{__(
						'View all default video tutorials and upload your own custom videos in the Videos area.',
						'wpmudev_vids'
					)}
				</p>
				{/** Add condition */}
				{!hasVideos && (
					<p>
						<a
							role="button"
							className="sui-button sui-button-blue"
							href={`${videosPageUrl}#/create`}
						>
							<Icon icon="plus" />
							{__('Add Custom Video', 'wpmudev_vids')}
						</a>
					</p>
				)}
			</Box.Body>

			{hasVideos && (
				<Table isFlushed={true} className="wpmudev-videos-box-table">
					<Table.Thead>
						<Table.Tr>
							<Table.Th className="wpmudev-videos-row--name">
								{__('Recently added videos', 'wpmudev_vids')}
							</Table.Th>
							<Table.Th className="wpmudev-videos-row--options">
								<span className="sui-screen-reader-text">
									{__('Video options menu', 'wpmudev_vids')}
								</span>
							</Table.Th>
						</Table.Tr>
					</Table.Thead>
					<Table.Tbody>
						{videosList.map((video) => (
							<Table.Tr key={video.id}>
								<Table.Td className="sui-table-item-title wpmudev-videos-row--name">
									<div className="wpmudev-videos-name-wrapper">
										<ListThumb
											url={video.thumbnail?.url}
											className="video-thumb"
											isCustom={
												video.video_type === 'custom'
											}
											icon={video.video_slug}
										/>
										<a
											href={`${videosPageUrl}#/view/${video.id}`}
										>
											{video.video_title}
										</a>
									</div>
								</Table.Td>
								<Table.Td className="wpmudev-videos-row--options">
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
													href={`${videosPageUrl}#/edit/${video.id}`}
												>
													<Icon icon="pencil" />
													{video.video_type ===
													'custom'
														? __(
																'Edit video',
																'wpmudev_vids'
														  )
														: __(
																'Edit title',
																'wpmudev_vids'
														  )}
												</a>
											</li>

											<li>
												<button
													className="ivt-copy-shortcode"
													data-clipboard-text={getShortCode(
														video
													)}
													onClick={displayCopyNotice}
												>
													<Icon icon="code" />
													{__(
														'Copy Shortcode',
														'wpmudev_vids'
													)}
												</button>
											</li>

											<li>
												<a
													href={`${videosPageUrl}#/playlist/${video.id}`}
												>
													<Icon icon="list" />
													{__(
														'Add to Playlist',
														'wpmudev_vids'
													)}
												</a>
											</li>

											{video.video_type === 'custom' && (
												<li>
													<a
														href={`${videosPageUrl}#/delete/${video.id}`}
														className="wpmudev-videos-red"
													>
														<Icon icon="trash" />
														{__(
															'Delete',
															'wpmudev_vids'
														)}
													</a>
												</li>
											)}
										</ul>
									</div>
								</Table.Td>
							</Table.Tr>
						))}
					</Table.Tbody>
				</Table>
			)}

			{hasVideos && (
				<Box.Footer>
					<a
						role="button"
						className="sui-button sui-button-blue"
						href={`${videosPageUrl}#/create`}
					>
						<Icon icon="plus" />
						{__('Add Custom Video', 'wpmudev_vids')}
					</a>

					<div className="sui-actions-right">
						<a
							role="button"
							className="sui-button sui-button-ghost"
							href={videosPageUrl}
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

export default Videos
