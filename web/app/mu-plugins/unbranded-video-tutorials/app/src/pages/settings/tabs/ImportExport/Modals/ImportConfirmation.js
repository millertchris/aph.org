/**
 * Library dependencies
 */
import {
	Button,
	Box,
	StaticNotice,
	BorderFrame,
	Toggle,
	IconButton,
} from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Configs } from '../Partials'
import { restPost } from '../../../../../helpers/api'

/**
 * External dependencies
 */
import { useState, useEffect } from 'react'
import PropTypes from 'prop-types'

const items = {
	videos: __('Videos', 'wpmudev_vids'),
	playlists: __('Playlists', 'wpmudev_vids'),
	display: __('Display Settings', 'wpmudev_vids'),
}

export function ImportConfirmation({ modalID, close, data, onImport }) {
	const [selected, setSelected] = useState([])
	const [includeThumbs, setIncludeThumbs] = useState(false)
	const [counts, setCounts] = useState({
		videos: 0,
		playlists: 0,
	})
	const [disableThumb, setDisableThumb] = useState(false)
	const [file, setFile] = useState(null)
	const [isProcessing, setProcessing] = useState(false)

	/**
	 * Send import data to backend
	 */
	const importData = () => {
		setProcessing(true)

		const formData = new FormData()

		// Append file.
		formData.append('file', file)

		// Append selected items.
		formData.append('selected', selected)

		// Should import thumbs.
		formData.append('thumb', includeThumbs)

		restPost({
			path: 'data/import',
			body: formData,
		})
			.then((response) => {
				if (response.success && response.data) {
					// callback on success
					onImport()
					setProcessing(false)
				}
			})
			.catch((error) => {
				console.error(error)
			})
	}

	/**
	 * Updates `selected` state
	 *
	 * @param {string} item to push to the selected array
	 *
	 * @returns {void}
	 */
	const addToSelected = (item) => {
		setSelected((prevSelected) => [...prevSelected, item])
	}

	/**
	 * Updates `counts` state
	 *
	 * @param {string} key
	 * @param {number} value
	 *
	 * @returns {void}
	 */
	const updateCounts = (key, value) => {
		setCounts((prevCounts) => ({ ...prevCounts, [key]: value }))
	}

	/**
	 * Resets the Modal
	 *
	 * @returns {void}
	 */
	const resetModal = () => {
		setFile(null)
		setIncludeThumbs(false)
		setSelected([])
		setCounts({
			videos: 0,
			playlists: 0,
		})
	}

	/**
	 * Set data for the confirmation modal.
	 *
	 * @param {object} data Data to import.
	 *
	 * @since 1.8.3
	 *
	 * @returns {void}
	 */
	const setModalData = () => {
		if (data.videos > 0) {
			// Set videos.
			addToSelected('videos')

			// Set count.
			updateCounts('videos', data.videos)
		}

		if (data.playlists > 0) {
			// Set playlists.
			addToSelected('playlists')

			// Set the count.
			updateCounts('playlists', data.playlists)
		}

		if (data.display) {
			addToSelected('display')
		}

		if (data.permissions) {
			addToSelected('permissions')
		}

		// Thumbnail needs to import.
		setIncludeThumbs(data.thumb)

		// Disable thumb option if not enabled.
		setDisableThumb(!data.thumb)

		// Set file.
		if (!!data.file) {
			setFile(data.file)
		}
	}

	useEffect(() => {
		// Reset Modal
		resetModal()

		// Set the new data
		setModalData()
	}, [data])

	return (
		<Box>
			<Box.Header className="sui-flatten sui-content-center sui-spacing-top--60">
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={close}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>
				<h3 id={`${modalID}-title`} className="sui-box-title sui-lg">
					{__('Import', 'wpmudev_vids')}
				</h3>
				<p id={`${modalID}-desc`} className="sui-description">
					{__(
						'To continue, select the configuration data to import below and click the import button to begin.',
						'wpmudev_vids'
					)}
				</p>
			</Box.Header>
			<Box.Body>
				<BorderFrame>
					<Configs
						id={modalID}
						items={items}
						selected={selected}
						onChange={setSelected}
						counts={counts}
					/>
				</BorderFrame>
				<div className="sui-form-field">
					<Toggle
						checked={includeThumbs}
						onChange={setIncludeThumbs}
						label={__('Import thumbnails', 'wpmudev_vids')}
						name="export[]"
						id={`${modalID}-items-thumb`}
						value="thumb"
						tooltip={__(
							'Include video and playlist thumbnails',
							'wpmudev_vids'
						)}
						disabled={disableThumb}
					/>
				</div>
				<StaticNotice>
					<p>
						{__(
							'Note: The selected configurations and data above will replace your current settings and data.',
							'wpmudev_vids'
						)}
					</p>
				</StaticNotice>
			</Box.Body>
			<Box.Footer>
				<Button color="gray" icon="cloud" onClick={close}>
					{__('Cancel', 'wpmudev_vids')}
				</Button>
				<Box.Right>
					<Button
						onClick={importData}
						isLoading={isProcessing}
						onLoadingText={__('Importing', 'wpmudev_vids')}
						type="ghost"
						disabled={selected.length === 0}
					>
						{__('Import', 'wpmudev_vids')}
					</Button>
				</Box.Right>
			</Box.Footer>
		</Box>
	)
}

ImportConfirmation.defaultProps = {
	modalID: 'modal',
	close: () => null,
	onImport: () => null,
}

ImportConfirmation.propTypes = {
	modalID: PropTypes.string.isRequired,
	close: PropTypes.func,
	onImport: PropTypes.func,
}

export default ImportConfirmation
