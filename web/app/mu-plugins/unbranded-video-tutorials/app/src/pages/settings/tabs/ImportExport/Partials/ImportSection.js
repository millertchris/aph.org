/**
 * Library dependencies
 */
import { Box, FileUpload, Button, Modal } from '../../../../../lib/components'
import { useModal } from '../../../../../lib/hooks'

/**
 * Internal dependencies
 */
import { ImportConfirmation, ImportProgress } from '../Modals'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * External dependencies
 */
import { useState, useEffect } from 'react'

export function ImportSection() {
	const [file, setFile] = useState(null)
	const [videos, setVideos] = useState(0)
	const [playlists, setPlaylists] = useState(0)
	const [display, setDisplay] = useState(false)
	const [permissions, setPermissions] = useState(false)
	const [thumb, setThumb] = useState(false)
	const [isProcessing, setIsProcessing] = useState(false)

	// Modals
	const confirmModalID = 'wpmudev-videos-import-confirmation'
	const processModalID = 'wpmudev-videos-import-progress'

	const [openConfirmModal, closeConfirmModal] = useModal({
		id: confirmModalID,
	})

	const [, closeProcessModal] = useModal({
		id: processModalID,
	})

	/**
	 * Check if a settings group is selected
	 *
	 * @param {object} data Settings data.
	 * @param {string} type Settings type
	 *
	 * @since 1.8.3
	 *
	 * @returns {boolean}
	 */
	const isSelected = (data, type) => {
		const fields = {
			permissions: ['roles'],
			display: [
				'show_menu',
				'menu_title',
				'menu_location',
				'contextual_help',
			],
		}

		if (fields[type]) {
			return fields[type].some((key) => Object.keys(data).includes(key))
		}

		return false
	}

	/**
	 * Set the count and data details.
	 *
	 * Read the uploaded json file and check the content
	 * and update the state accordigly
	 *
	 * @param {object} event Event.
	 *
	 * @returns {void}
	 */
	const setData = (event) => {
		try {
			// Parse the json data.
			let data = JSON.parse(event.target.result)

			// Set the values from json.
			setThumb(data.thumb)
			setVideos(Object.keys(data.videos).length)
			setPlaylists(Object.keys(data.playlists).length)
			setDisplay(isSelected(data.settings, 'display'))
			setPermissions(isSelected(data.settings, 'permissions'))
		} catch (e) {
			console.error(e)
		}
	}

	/**
	 * Replaces the confirmation modal with the progress modal
	 *
	 * @returns {void}
	 */
	const onImportData = () => {
		SUI.replaceModal(
			processModalID,
			`${confirmModalID}-opener`,
			`${processModalID}-import-continue`
		)

		// Mounts ProcessModal and start checking progress
		setIsProcessing(true)
	}

	// operations upon file upload
	useEffect(() => {
		if (!!file) {
			const reader = new FileReader()
			reader.readAsText(file)
			reader.onload = setData
		} else {
			// reset
			setThumb(false)
			setVideos(0)
			setPlaylists(0)
			setDisplay(false)
			setPermissions(false)
		}
	}, [file])

	return (
		<>
			<Modal id={confirmModalID}>
				<ImportConfirmation
					close={closeConfirmModal}
					modalID={confirmModalID}
					data={{
						videos,
						display,
						permissions,
						thumb,
						playlists,
						file,
					}}
					onImport={onImportData}
				/>
			</Modal>

			<Modal id={processModalID}>
				{isProcessing && (
					<ImportProgress
						setIsProcessing={setIsProcessing}
						close={closeProcessModal}
						modalID={processModalID}
					/>
				)}
			</Modal>

			<Box.Row>
				<Box.Col1>
					<Box.Label>{__('Import', 'wpmudev_vids')}</Box.Label>
					<Box.Description>
						{__(
							'Use this tool to import the Integrated Video Tutorial configurations and data from another site.',
							'wpmudev_vids'
						)}
					</Box.Description>
				</Box.Col1>
				<Box.Col2 id="import-configs">
					<h4 className="sui-settings-label">
						{__('Import Configurations', 'wpmudev_vids')}
					</h4>
					<p className="sui-description">
						{__(
							'Import an Integrated Video Tutorial configuration and data file.',
							'wpmudev_vids'
						)}
					</p>
					<div className="sui-form-field">
						<div className="wpmudev-videos-upload-import">
							<FileUpload
								label={__('Upload file', 'wpmudev_vids')}
								removeLabel={__('Remove file', 'wpmudev_vids')}
								accept="application/JSON"
								onChange={setFile}
							/>
							<Button
								color="blue"
								disabled={!file}
								id={`${confirmModalID}-opener`}
								icon="download-cloud"
								onClick={openConfirmModal}
							>
								{__('Import', 'wpmudev_vids')}
							</Button>
						</div>
						<p className="sui-description">
							{__(
								'Choose a JSON (.json) file to import the configurations.',
								'wpmudev_vids'
							)}
						</p>
					</div>
				</Box.Col2>
			</Box.Row>
		</>
	)
}
