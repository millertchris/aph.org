/**
 * Library components
 */
import { Label, Box, Icon, Button } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { PlaylistTitle, PlaylistDesc } from '../../components'
import { UploadThumb, WhiteLabelBanner } from '../../../../components'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { __ } from '@wordpress/i18n'

export function PlaylistSettings({
	modalID,
	playlist,
	setPlaylist,
	closeModal,
}) {
	const { thumbnail, description, title } = playlist
	/**
	 * Updates playlist title
	 *
	 * @return {void}
	 */
	const updatePlaylistTitle = (value) => {
		setPlaylist({ ...playlist, title: value })
	}

	/**
	 * Updates playlist description
	 *
	 * @return {void}
	 */
	const updatePlaylistDesc = (value) => {
		setPlaylist({ ...playlist, description: value })
	}

	/**
	 * Updates playlist thumbnail
	 *
	 * @return {void}
	 */
	const updatePlaylistThumb = (value) => {
		setPlaylist({ ...playlist, thumbnail: value })
	}

	const BoxHeaderClasses = classnames(
		'sui-box-header',
		'sui-flatten',
		'sui-content-center',
		'sui-spacing-top--60',
		'sui-spacing-sides--100'
	)

	return (
		<div
			id={`${modalID}-playlist-settings`}
			className="sui-modal-slide sui-active sui-loaded"
		>
			<Box>
				<Box.Header className={BoxHeaderClasses}>
					<WhiteLabelBanner
						src="modal/modal-header-videos.png"
						alt={__('Create a new playlist', 'wpmudev_vids')}
					/>

					<button
						onClick={closeModal}
						className={classnames(
							'sui-button-icon',
							'sui-button-float--right'
						)}
					>
						<Icon icon="close" size="md" />
						<span className="sui-screen-reader-text">
							{__('Close this dialog.', 'wpmudev_vids')}
						</span>
					</button>

					<h3
						id={`${modalID}-title`}
						className="sui-box-title sui-lg"
					>
						{__('Create a new playlist', 'wpmudev_vids')}
					</h3>

					<p id={`${modalID}-desc`} className="sui-description">
						{__(
							'Choose a title, description and custom thumbnail for your playlist and then start adding videos to it.',
							'wpmudev_vids'
						)}
					</p>
				</Box.Header>
				<Box.Body>
					{/* Playlist title */}
					<PlaylistTitle
						title={title}
						onChange={updatePlaylistTitle}
						modalID={modalID}
					/>

					{/* Playlist description */}
					<PlaylistDesc
						description={description}
						onChange={updatePlaylistDesc}
						modalID={modalID}
					/>

					<hr className="wpmudev-videos-break" />

					{/* Playlist thumbnail */}
					<div className="sui-form-field wpmudev-videos-field--thumbnail">
						<Label>
							{__('Playlist thumbnail image', 'wpmudev_vids')}
						</Label>
						<p className="sui-description">
							{__(
								"Add a custom thumbnail to your playlist, otherwise we'll just use the first video's thumbnail.",
								'wpmudev_vids'
							)}
						</p>
					</div>
					<UploadThumb
						thumbnail={thumbnail}
						onSelect={updatePlaylistThumb}
						modalID={modalID}
					/>
				</Box.Body>
				<Box.Footer>
					<Button type="ghost" onClick={closeModal}>
						{__('Cancel', 'wpmudev_vids')}
					</Button>
					<Box.Right>
						<Button
							disabled={!title}
							data-modal-slide={`${modalID}-playlist-videos`}
							data-modal-slide-intro="next"
						>
							{__('Continue', 'wpmudev_vids')}
						</Button>
					</Box.Right>
				</Box.Footer>
			</Box>
		</div>
	)
}

export default PlaylistSettings
