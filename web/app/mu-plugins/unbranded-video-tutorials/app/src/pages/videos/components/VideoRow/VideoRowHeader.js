/**
 * Library dependencies
 */
import { Icon, IconButton } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { ListThumb } from '../../../../components'
import { addNotice } from '../../../../store/slices/notice'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import PropTypes from 'prop-types'
import { useDispatch } from 'react-redux'
import { useNavigate } from 'react-router-dom'

export function VideoRowHeader({
	video,
	setOpenedId,
	openedId,
	disabled,
	openEditModal,
	setCurrentVideo,
	openDeleteModal,
	openTitleModal,
	openPlaylistsModal,
}) {
	const { id, video_title, thumbnail, video_slug, video_duration } =
		video ?? {}

	const dispatch = useDispatch()

	const navigate = useNavigate()

	// id of this accordion is opened
	const isOpened = openedId === id

	// Check if it is a custom video
	const isCustomVideo = video?.video_type === 'custom'

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
	const getShortCode = () => {
		if (isCustomVideo) {
			return `[wpmudev-video video="${id}"]`
		} else {
			return `[wpmudev-video video="${video_slug}"]`
		}
	}

	/**
	 * Open the accordion and changes route
	 *
	 * @return {void}
	 *
	 */
	const openAccordion = () => {
		// Change Route
		navigate(`/view/${id}`)

		// set opened id
		setOpenedId(id)
	}

	return (
		<div
			role="heading"
			className="wpmudev-videos-accordion--heading wpmudev-videos-accordion--row"
		>
			<div className="wpmudev-videos-accordion--col-large">
				{!isOpened && (
					<button
						className="wpmudev-videos-accordion--title"
						aria-expanded={false}
						id={`wpmudev-videos-accordion-item-${id}--label`}
						aria-controls={`wpmudev-videos-accordion-item-${id}`}
						onClick={openAccordion}
						disabled={disabled}
					>
						<ListThumb
							className="video-thumb"
							icon={video_slug}
							url={thumbnail?.url}
							isCustom={isCustomVideo}
						/>
						<Icon icon="play" size="sm" className="video-icon" />
						<span className="video-name">{video_title}</span>
					</button>
				)}

				{isOpened && (
					<>
						<p className="video-name">{video_title}</p>

						<p className="video-length">{video_duration}</p>
					</>
				)}
			</div>
			<div className="wpmudev-videos-accordion--col-small">
				<div className="wpmudev-videos-accordion--actions">
					{isOpened && (
						<>
							<IconButton
								icon="add-playlist"
								label={__('Add to Playlist', 'wpmudev_vids')}
								outlined={true}
								disabled={disabled}
								tooltip={__('Add to Playlist', 'wpmudev_vids')}
								onClick={() => {
									// Update current video state
									setCurrentVideo(video)

									// Open the playlists modal
									openPlaylistsModal()
								}}
							/>
							
							<IconButton
								icon="code"
								disabled={disabled}
								className="ivt-copy-shortcode"
								onClick={displayCopyNotice}
								label={__('Copy Shortcode', 'wpmudev_vids')}
								tooltip={__('Copy Shortcode', 'wpmudev_vids')}
								data-clipboard-text={getShortCode()}
							/>
							<IconButton
								icon="pencil"
								label={
									isCustomVideo
										? __('Edit video', 'wpmudev_vids')
										: __('Edit title', 'wpmudev_vids')
								}
								outlined={true}
								tooltip={
									isCustomVideo
										? __('Edit video', 'wpmudev_vids')
										: __('Edit title', 'wpmudev_vids')
								}
								onClick={() => {
									// Update current video state
									setCurrentVideo(video)

									// Open the Edit modal
									if (isCustomVideo) {
										openEditModal()
									} else {
										openTitleModal()
									}
								}}
							/>
						</>
					)}
					{!isOpened && (
						<>
							<p className="sui-description">
								{video_duration ||
									__('Unknown', 'wpmudev_vids')}
							</p>

							<IconButton
								icon="add-playlist"
								label={__('Add to Playlist', 'wpmudev_vids')}
								outlined={true}
								disabled={disabled}
								tooltip={__('Add to Playlist', 'wpmudev_vids')}
								onClick={() => {
									// Update current video state
									setCurrentVideo(video)

									// Open the playlists modal
									openPlaylistsModal()
								}}
							/>

							<div className="sui-dropdown">
								<IconButton
									className="sui-dropdown-anchor"
									icon="widget-settings-config"
									outlined={false}
									label={__('Video actions', 'wpmudev_vids')}
								/>

								<ul>
									{isCustomVideo && (
										<li>
											<button
												disabled={disabled}
												onClick={() => {
													// Update current video state
													setCurrentVideo(video)

													// Open the Edit modal
													openEditModal()
												}}
											>
												<span
													className="sui-icon-pencil"
													aria-hidden="true"
												></span>
												{__(
													'Edit video',
													'wpmudev_vids'
												)}
											</button>
										</li>
									)}

									{!isCustomVideo && (
										<li>
											<button
												disabled={disabled}
												onClick={() => {
													// Update current video state
													setCurrentVideo(video)

													// Open the title modal
													openTitleModal()
												}}
											>
												<span
													className="sui-icon-pencil"
													aria-hidden="true"
												></span>
												{__(
													'Edit title',
													'wpmudev_vids'
												)}
											</button>
										</li>
									)}
									<li>
										<button
											disabled={disabled}
											className="ivt-copy-shortcode"
											onClick={displayCopyNotice}
											data-clipboard-text={getShortCode()}
										>
											<span
												className="sui-icon-code"
												aria-hidden="true"
											></span>
											{__(
												'Copy Shortcode',
												'wpmudev_vids'
											)}
										</button>
									</li>

									{isCustomVideo && (
										<li>
											<button
												disabled={disabled}
												className="wpmudev-videos-red"
												onClick={() => {
													// Update current video state
													setCurrentVideo(video)

													// Open the delete modal
													openDeleteModal()
												}}
											>
												<span
													className="sui-icon-trash"
													aria-hidden="true"
												></span>
												{__('Delete', 'wpmudev_vids')}
											</button>
										</li>
									)}
								</ul>
							</div>
						</>
					)}
				</div>
			</div>
		</div>
	)
}

VideoRowHeader.propTypes = {
	video: PropTypes.object,
	setOpenedId: PropTypes.func,
	openedId: PropTypes.number,
	disabled: PropTypes.bool,
}

export default VideoRowHeader
