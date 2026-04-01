/**
 * Library dependencies
 */
import { Box, BorderFrame, Toggle } from '../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { SubMenu } from './Partials/SubMenu'
import { TabsFooter } from '../../components/TabsFooter'
import { setOption, getOption } from '../../../../store/slices/settings'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'

export function DisplaySettings() {
	const dispatch = useDispatch()

	const showMenu = useSelector((state) => getOption(state, 'show_menu'))

	const contextHelp = useSelector((state) =>
		getOption(state, 'contextual_help')
	)

	const updateShowMenu = (value) => {
		dispatch(setOption({ key: 'show_menu', value }))
	}

	const updateContextHelp = (value) => {
		dispatch(setOption({ key: 'contextual_help', value }))
	}

	return (
		<Box>
			<Box.Header>
				<Box.Title>{__('Display Settings', 'wpmudev_vids')}</Box.Title>
			</Box.Header>
			<Box.Body>
				<Box.Row isFlushed={true}>
					<Box.Col1>
						<Box.Label>
							{__('Tutorials Tab', 'wpmudev_vids')}
						</Box.Label>
						<Box.Description>
							{__(
								'Customize the default "Video Tutorials" tab title and position in the WordPress Admin menu.',
								'wpmudev_vids'
							)}
						</Box.Description>
					</Box.Col1>
					<Box.Col2>
						<div className="sui-form-field">
							<Toggle
								checked={!!showMenu}
								onChange={updateShowMenu}
								label={__(
									'Show the tutorials tab in the WP Admin sidebar',
									'wpmudev_vids'
								)}
								id="wpmudev-videos-settings-tutorials-tab"
							/>
						</div>
						{!!showMenu && (
							<BorderFrame>
								<SubMenu />
							</BorderFrame>
						)}
					</Box.Col2>
				</Box.Row>

				<Box.Row isFlushed={true}>
					<Box.Col1>
						<Box.Label>
							{__('Help Videos', 'wpmudev_vids')}
						</Box.Label>
						<Box.Description>
							{__(
								'This will add the appropriate video tutorials to the help dropdowns on WordPress admin screens.',
								'wpmudev_vids'
							)}
						</Box.Description>
					</Box.Col1>
					<Box.Col2>
						<div className="sui-form-field">
							<Toggle
								checked={!!contextHelp}
								onChange={updateContextHelp}
								label={__(
									'Add Videos to Contextual Help',
									'wpmudev_vids'
								)}
								id="wpmudev-videos-settings-help-videos"
							/>
						</div>
					</Box.Col2>
				</Box.Row>
			</Box.Body>
			<TabsFooter />
		</Box>
	)
}

export default DisplaySettings
