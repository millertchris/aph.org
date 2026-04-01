/**
 * Library dependencies
 */
import {
	Box,
	BorderFrame,
	Button,
	Toggle,
	Label,
} from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { Configs } from './Configs'

/**
 * External dependencies
 */
import { useState, useRef } from 'react'

export function ExportSection() {
	const [selected, setSelected] = useState([])
	const [includeThumbs, setIncludeThumbs] = useState(false)
	const [exporting, setExporting] = useState(false)

	const exportForm = useRef()

	/**
	 * Process the export button click.
	 *
	 * @returns {void}
	 *
	 */
	const processExport = () => {
		// Show processing.
		setExporting(true)

		// Submit the form.
		exportForm.current.submit()

		// Remove processing.
		setTimeout(() => {
			setExporting(false)
		}, 2000)
	}

	return (
		<Box.Row>
			<Box.Col1>
				<Box.Label>{__('Export', 'wpmudev_vids')}</Box.Label>
				<Box.Description>
					{__(
						'Use this tool to export the IVT configurations and data to another site.',
						'wpmudev_vids'
					)}
				</Box.Description>
			</Box.Col1>
			<Box.Col2 id="export-configs">
				<h4 className="sui-settings-label">
					{__('Export Configurations', 'wpmudev_vids')}
				</h4>
				<BorderFrame>
					<form method="post" ref={exportForm}>
						<input type="hidden" name="ivt-action" value="export" />
						<input type="hidden" name="ivt-export" value="1" />
						<input
							type="hidden"
							name="export-nonce"
							value={window.ivtModuleVars.export_nonce}
						/>
						<div className="sui-form-field wpmudev-videos-export-options">
							<Label id="export-options-label">
								{__('Choose export options', 'wpmudev_vids')}
							</Label>

							<p className="sui-description">
								{__(
									'Select configurations or data to export or check All to export all settings.',
									'wpmudev_vids'
								)}
							</p>
							<Configs
								id="export-config"
								onChange={setSelected}
								selected={selected}
							/>
						</div>
						<div
							role="group"
							className="sui-form-field wpmudev-videos-export-options"
						>
							<p
								className="sui-label"
								style={{ margin: '0 0 10px' }}
							>
								{__('Export thumbnails', 'wpmudev_vids')}
							</p>
							<Toggle
								checked={includeThumbs}
								onChange={setIncludeThumbs}
								label={__(
									'Include video and playlist thumbnails in export.',
									'wpmudev_vids'
								)}
								name="export[]"
								id="export-config-items-thumb"
								value="thumb"
							/>
						</div>
					</form>
				</BorderFrame>
				<Button
					onClick={processExport}
					isLoading={exporting}
					disabled={selected.length === 0}
					icon="upload-cloud"
					onLoadingText={__('Exporting', 'wpmudev_vids')}
				>
					{__('Export', 'wpmudev_vids')}
				</Button>
			</Box.Col2>
		</Box.Row>
	)
}
