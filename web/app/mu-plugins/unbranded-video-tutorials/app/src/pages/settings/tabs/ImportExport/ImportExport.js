/**
 * Library dependencies
 */
import { Box } from '../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { ImportSection, ExportSection } from './Partials'

export function ImportExport() {
	return (
		<Box>
			<Box.Header>
				<Box.Title>{__('Import / Export', 'wpmudev_vids')}</Box.Title>
			</Box.Header>
			<Box.Body>
				<ImportSection />
				<ExportSection />
			</Box.Body>
		</Box>
	)
}

export default ImportExport
