/**
 * Library dependencies
 */
import { Select, Input, Label } from '../../../../../lib/components'

/**
 * Internal dependencies
 */
import { setOption, getOption } from '../../../../../store/slices/settings'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'
import { __ } from '@wordpress/i18n'

export function SubMenu() {
	const options = window.ivtModuleVars.menu_locations || []

	const dispatch = useDispatch()

	const menuLocation = useSelector((state) =>
		getOption(state, 'menu_location')
	)

	const menuTitle = useSelector((state) =>
		getOption(state, 'menu_title', __('Video Tutorials', 'wpmudev_vids'))
	)

	const updateMenuLocation = (value) => {
		dispatch(setOption({ key: 'menu_location', value }))
	}

	const updateMenuTitle = (value) => {
		dispatch(setOption({ key: 'menu_title', value }))
	}

	return (
		<>
			<div className="sui-form-field">
				<Label
					htmlFor="wpmudev-videos-settings-tutorials-position"
					id="wpmudev-videos-settings-tutorials-position-label"
				>
					{__(
						'Choose the location of the videos page in the admin menu.',
						'wpmudev_vids'
					)}
				</Label>
				<Select
					options={options}
					value={menuLocation}
					onChange={updateMenuLocation}
					id="wpmudev-videos-settings-tutorials-position"
					labelID="wpmudev-videos-settings-tutorials-position-label"
					placeholder={__('Select item', 'wpmudev_vids')}
				/>
			</div>
			<div className="sui-form-field">
				<Label
					htmlFor="wpmudev-videos-settings-tutorials-title"
					id="wpmudev-videos-settings-tutorials-title-label"
				>
					{__(
						'Show the tutorials tab in the WP Admin sidebar',
						'wpmudev_vids'
					)}
				</Label>
				<Input
					value={menuTitle || ''}
					onChange={updateMenuTitle}
					id="wpmudev-videos-settings-tutorials-title"
					placeholder={__('Video Tutorials', 'wpmudev_vids')}
				/>
			</div>
		</>
	)
}

export default SubMenu
