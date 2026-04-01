/**
 * Library dependencies
 */
import { Icon } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { addNotice } from '../../../../store/slices/notice'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import PropTypes from 'prop-types'
import { useDispatch } from 'react-redux'

export function DropDown({
	openEditModal,
	openVisibilityModal,
	setCurrentPlaylist,
	openVideosModal,
	openDeleteModal,
	playlist,
}) {
	const dispatch = useDispatch()

	const isCustom = 'custom' === playlist.playlist_type

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

	// Playlist shortcode
	const shortcode = `[wpmudev-video group="${playlist.slug}"]`

	return (
		<>
			<ul>
				<li>
					<button
						onClick={() => {
							setCurrentPlaylist(playlist)
							openEditModal()
						}}
					>
						<Icon icon="pencil" />
						{__('Edit', 'wpmudev_vids')}
					</button>
				</li>
				<li>
					<button
						onClick={() => {
							setCurrentPlaylist(playlist)
							openVideosModal()
						}}
					>
						<Icon icon="align-justify" />
						{__('Add videos', 'wpmudev_vids')}
					</button>
				</li>
				<li>
					<button
						onClick={() => {
							setCurrentPlaylist(playlist)
							openVisibilityModal()
						}}
					>
						<Icon icon="widget-settings-config" />
						{__('Visibility settings', 'wpmudev_vids')}
					</button>
				</li>
				<li>
					<button
						className="ivt-copy-shortcode"
						onClick={displayCopyNotice}
						data-clipboard-text={shortcode}
					>
						<Icon icon="code" />
						{__('Copy shortcode', 'wpmudev_vids')}
					</button>
				</li>
				{isCustom && (
					<li>
						<button
							onClick={() => {
								setCurrentPlaylist(playlist)
								openDeleteModal()
							}}
							className="wpmudev-videos-red"
						>
							<Icon icon="trash" />
							{__('Delete', 'wpmudev_vids')}
						</button>
					</li>
				)}
			</ul>
		</>
	)
}

DropDown.defaultProps = {
	openEditModal: () => null,
	openVisibilityModal: () => null,
	setCurrentPlaylist: () => null,
	openVideosModal: () => null,
	openDeleteModal: () => null,
	playlist: {},
}

DropDown.propTypes = {
	openEditModal: PropTypes.func,
	openVisibilityModal: PropTypes.func,
	setCurrentPlaylist: PropTypes.func,
	openVideosModal: PropTypes.func,
	openDeleteModal: PropTypes.func,
	playlist: PropTypes.object,
}

export default DropDown
