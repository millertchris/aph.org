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
import { TabsFooter } from '../../components/TabsFooter'
import { DataPreserve, ResetSettings } from './Partials'

export function Data() {
	return (
		<Box>
			<Box.Header>
				<Box.Title>{__('Data', 'wpmudev_vids')}</Box.Title>
			</Box.Header>
			<Box.Body>
				<Box.Row>
					<Box.Col2>
						{__(
							'Control what to do with your settings and data. Data includes Videos, Playlists, and other pieces of information stored over time.',
							'wpmudev_vids'
						)}
					</Box.Col2>
				</Box.Row>
				<Box.Row>
					<Box.Col1>
						<Box.Label>
							{__('Uninstallation', 'wpmudev_vids')}
						</Box.Label>
						<Box.Description>
							{__(
								'When you uninstall this plugin, what do you want to do with your settings and stored data?',
								'wpmudev_vids'
							)}
						</Box.Description>
					</Box.Col1>
					<Box.Col2>
						<DataPreserve />
					</Box.Col2>
				</Box.Row>
				<Box.Row>
					<Box.Col1>
						<Box.Label>
							{__('Reset Settings', 'wpmudev_vids')}
						</Box.Label>
						<Box.Description>
							{__(
								'Needing to start fresh? Use this button to roll back to the default settings.',
								'wpmudev_vids'
							)}
						</Box.Description>
					</Box.Col1>
					<Box.Col2>
						<ResetSettings />
					</Box.Col2>
				</Box.Row>
			</Box.Body>
			<TabsFooter />
		</Box>
	)
}

export default Data
