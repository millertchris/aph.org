/**
 * Library dependencies
 */
import {
	Box,
	Select,
	Button,
	Label,
	StaticNotice,
	IconButton,
} from '../../../lib/components'

/**
 * Internal dependencies
 */
import { UserRoles } from '../../../components'
import { getOption } from '../../../store/slices/settings'
import { savePlaylist, getLoadingStatus } from '../../../store/slices/playlists'
import { addNotice } from '../../../store/slices/notice'

/**
 * External Depnedencies
 */
import { __ } from '@wordpress/i18n'
import { useSelector, useDispatch } from 'react-redux'
import { Interweave } from 'interweave'

// Global Variables
const { locations } = window.ivtModuleVars ?? {}
const {
	urls: { settings },
} = window.ivtVars ?? {}

export function VisibilitySettings({
	playlist,
	setCurrentPlaylist,
	closeModal,
	modalID,
}) {
	const dispatch = useDispatch()

	const { id, playlist_roles, playlist_locations } = playlist ?? {}

	const contextualHelp = useSelector((state) =>
		getOption(state, 'contextual_help')
	)

	const isSaving = useSelector((state) => getLoadingStatus(state))

	/**
	 * Update playlist state with new roles
	 */
	const updatePlaylistRoles = (roles) => {
		const newPlaylist = { ...playlist, playlist_roles: roles }
		setCurrentPlaylist(newPlaylist)
	}

	/**
	 * Update playlist locations
	 */
	const updatePlaylistLocations = (locations) => {
		const newPlaylist = { ...playlist, playlist_locations: locations }
		setCurrentPlaylist(newPlaylist)
	}

	/**
	 * Save Changes
	 */
	const saveChanges = () => {
		dispatch(
			savePlaylist({
				id,
				playlist: {
					locations: playlist_locations,
					roles: playlist_roles,
				},
			})
		).then((response) => {
			if (response?.payload?.success) {
				// close the modal
				closeModal()

				// Show Succssful Notice
				dispatch(
					addNotice({
						message: __(
							'Playlist settings updated successfully.',
							'wpmudev_vids'
						),
					})
				)
			} else {
				// Show Error Notice
				dispatch(
					addNotice({
						message: __(
							'Playlist settings update failed.',
							'wpmudev_vids'
						),
						type: 'error',
					})
				)
			}
		})
	}

	return (
		<Box>
			<Box.Header>
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={closeModal}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={`${modalID}-title`} className="sui-box-title">
					{__('Visibility settings', 'wpmudev_vids')}
				</h3>
			</Box.Header>
			<Box.Body>
				<p>
					{__(
						'Control which user roles are able to see this playlist, in addition to where it will be displayed as an available WordPress widget',
						'wpmudev_vids'
					)}
				</p>
				<Box.Row>
					<Box.Col2>
						<Box.Label>
							{__('Permissions', 'wpmudev_vids')}
						</Box.Label>
						<Box.Description>
							{__(
								'Choose which user roles will be able to view this playlist.',
								'wpmudev_vids'
							)}
						</Box.Description>
						<UserRoles
							rolesValues={playlist_roles}
							onChange={updatePlaylistRoles}
						/>
					</Box.Col2>
				</Box.Row>
				<Box.Row>
					<Box.Col2>
						<Box.Label>{__('Location', 'wpmudev_vids')}</Box.Label>
						<Box.Description>
							{__(
								'Choose which default locations will show this video playlist to the user roles selected above.',
								'wpmudev_vids'
							)}
						</Box.Description>
						<div
							className="sui-form-field"
							v-if="showLocationSettings"
						>
							{!!contextualHelp ? (
								<>
									<Label htmlFor="wpmudev-videos-playlist-location">
										{__(
											'Choose WP Admin pages',
											'wpmudev_vids'
										)}
									</Label>
									<Select
										id="wpmudev-videos-playlist-location"
										labelID="wpmudev-videos-playlist-location-label"
										options={locations}
										multiple={true}
										value={playlist_locations}
										placeholder={__(
											'Choose WP Admin pages',
											'wpmudev_vids'
										)}
										onChange={updatePlaylistLocations}
										parentElement={modalID}
									/>
								</>
							) : (
								<StaticNotice>
									<p>
										<Interweave
											content={sprintf(
												__(
													'To configure locations for this playlist, please enable the <a href="%s">Add Videos to Contextual Help</a> feature on the Settings page.',
													'wpmudev_vid'
												),
												settings
											)}
										/>
									</p>
								</StaticNotice>
							)}
						</div>
					</Box.Col2>
				</Box.Row>
			</Box.Body>
			<Box.Footer isCentered={true}>
				<Button
					isLoading={isSaving}
					disabled={isSaving}
					onClick={saveChanges}
					onLoadingText={__('Saving Changes', 'wpmudev_vids')}
					color="blue"
				>
					{__('Save Changes', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default VisibilitySettings
