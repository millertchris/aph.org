/**
 * Library dependencies
 */
import {
	Accordion,
	Checkbox,
	Icon,
	Portal,
	IconButton,
} from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { DropDown, PlaylistVideos } from '../../components'
import {
	isPlaylistSelected,
	togglePlaylist,
} from '../../../../store/slices/playlists'
import { ListThumb } from '../../../../components'

/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { __ } from '@wordpress/i18n'
import React, { useRef, useState, useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'

export function PlaylistItem({
	playlist,
	openEditModal,
	setCurrentPlaylist,
	openVisibilityModal,
	openVideosModal,
	openDeleteModal,
}) {
	const { id, title, description, thumbnail } = playlist

	const dispatch = useDispatch()

	const playlistSelected = useSelector((state) =>
		isPlaylistSelected(state, id)
	)

	const [checkboxPortal, setCheckboxPortal] = useState()

	const portalRef = useRef()

	/**
	 * Select or deselect a playlist
	 *
	 * @return {void}
	 */
	const updatePlaylistSelection = (val) => {
		dispatch(togglePlaylist({ id, checked: val }))
	}

	useEffect(() => {
		setCheckboxPortal(portalRef.current)
	}, [portalRef])

	return (
		<>
			<Portal container={checkboxPortal}>
				<Checkbox
					label={false}
					checked={playlistSelected}
					onChange={updatePlaylistSelection}
					id={`wpmudev-playlists-item-${id}`}
				/>
			</Portal>
			<Accordion.Item
				className="wpmudev-videos-accordion"
				id={`wpmudev-videos-playlist-item-${id}`}
				data-id={id}
			>
				{/* Item Header  */}
				<Accordion.Header>
					<Accordion.Title>
						<span
							className="sui-icon-drag sui-md sortable"
							aria-hidden="true"
						/>

						{/* the checkbox renders here */}
						<div ref={portalRef} />

						{/** Thumbnail */}
						<ListThumb
							url={thumbnail?.url}
							className="playlist-thumb"
							hideIcon={true}
						/>
						{title}
					</Accordion.Title>
					<Accordion.Date>{description}</Accordion.Date>
					<Accordion.AutoCol>
						<div className="sui-dropdown sui-accordion-item-action">
							<IconButton
								className="sui-dropdown-anchor"
								icon="widget-settings-config"
								outlined={false}
							/>
							<DropDown
								playlist={playlist}
								openEditModal={openEditModal}
								openVisibilityModal={openVisibilityModal}
								setCurrentPlaylist={setCurrentPlaylist}
								openVideosModal={openVideosModal}
								openDeleteModal={openDeleteModal}
							/>
						</div>

						<IconButton
							className="sui-accordion-open-indicator"
							icon="chevron-down"
							aria-label={__('Open Item', 'wpmudev_vids')}
							label={__('Open Item', 'wpmudev_vids')}
							outlined={false}
						/>
					</Accordion.AutoCol>
				</Accordion.Header>

				{/* Item Body  */}
				<Accordion.Body>
					<PlaylistVideos
						openVideosModal={openVideosModal}
						setCurrentPlaylist={setCurrentPlaylist}
						playlist={playlist}
					/>
				</Accordion.Body>
			</Accordion.Item>
		</>
	)
}

PlaylistItem.defaultProps = {
	playlist: {},
	editModalID: '',
	openEditModal: () => null,
	closeEditModal: () => null,
	openDeleteModal: () => null,
	setCurrentPlaylist: () => null,
}

PlaylistItem.propTypes = {
	playlist: PropTypes.object,
	editModalID: PropTypes.string,
	openEditModal: PropTypes.func,
	closeEditModal: PropTypes.func,
	openDeleteModal: PropTypes.func,
	setCurrentPlaylist: PropTypes.func,
}

export default React.memo(PlaylistItem)
