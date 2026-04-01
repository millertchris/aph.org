/**
 * Library dependencies
 */
import { RadioTabs } from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { setOption, getOption } from '../../../../../store/slices/settings'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'

export function DataPreserve() {
	const dispatch = useDispatch()

	const keepSettings = useSelector((state) =>
		getOption(state, 'keep_settings')
	)

	const keepData = useSelector((state) => getOption(state, 'keep_data'))

	const updateKeepSettings = (value) => {
		let finalValue = '0'

		if (value === 'preserve') {
			finalValue = '1'
		}

		dispatch(setOption({ key: 'keep_settings', value: finalValue }))
	}

	const updateKeepData = (value) => {
		let finalValue = '0'

		if (value === 'keep') {
			finalValue = '1'
		}

		dispatch(setOption({ key: 'keep_data', value: finalValue }))
	}

	const settingsOptions = [
		{
			id: 'wpmudev-videos-settings-keep',
			value: 'preserve',
			label: __('Preserve', 'wpmudev_vids'),
		},
		{
			id: 'wpmudev-videos-settings-reset',
			value: 'reset',
			label: __('Reset', 'wpmudev_vids'),
		},
	]

	const dataOptions = [
		{
			id: 'wpmudev-videos-data-keep',
			value: 'keep',
			label: __('Keep', 'wpmudev_vids'),
		},
		{
			id: 'wpmudev-videos-data-remove',
			value: 'remove',
			label: __('Remove', 'wpmudev_vids'),
		},
	]

	return (
		<div className="sui-form-field">
			<h4 className="sui-settings-label">
				{__('Settings', 'wpmudev_vids')}
			</h4>
			<span className="sui-description side-tabs-description">
				{__(
					'Choose whether to save your settings for next time, or reset them.',
					'wpmudev_vids'
				)}
			</span>
			<RadioTabs
				value={keepSettings === '1' ? 'preserve' : 'reset'}
				options={settingsOptions}
				onChange={(val) => updateKeepSettings(val)}
			/>

			<h4 className="sui-settings-label">{__('Data', 'wpmudev_vids')}</h4>
			<span className="sui-description side-tabs-description">
				{__(
					'Choose whether to keep or remove Videos and Playlists data.',
					'wpmudev_vids'
				)}
			</span>
			<RadioTabs
				value={keepData === '1' ? 'keep' : 'remove'}
				options={dataOptions}
				onChange={(val) => updateKeepData(val)}
			/>
		</div>
	)
}

export default DataPreserve
