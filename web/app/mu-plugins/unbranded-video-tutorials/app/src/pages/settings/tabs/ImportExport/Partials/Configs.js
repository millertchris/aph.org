/**
 * Library dependencies
 */
import { Checkbox } from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { isNetwork } from '../../../../../helpers/utils'

const items = {
	videos: __('Videos', 'wpmudev_vids'),
	playlists: __('Playlists', 'wpmudev_vids'),
	display: __('Display Settings', 'wpmudev_vids'),
}

// Permission settings not required on multisite.
if (!isNetwork()) {
	items['permissions'] = __('Permissions', 'wpmudev_vids')
}

export function Configs({ onChange, selected, id, counts }) {
	/**
	 * Check if all items are selected.
	 *
	 * @param {list} list list of items to check upon
	 * @returns {boolean}
	 */
	const allSelected = (list) => {
		let itemsList = Object.keys(items)

		return itemsList.every((key) => list.includes(key))
	}

	/**
	 * On sub items check change.
	 *
	 * If all sub items are selected, select
	 * all options also.
	 *
	 */
	function toggleItem(key, checkit) {
		let newSelected = [...selected]
		if (checkit) {
			newSelected.push(key)
			if (allSelected(newSelected)) {
				newSelected.push('all')
			}
		} else {
			newSelected = newSelected.filter((el) => el !== key && el !== 'all')
		}
		onChange(newSelected)
	}

	/**
	 * Toggle all select checkbox items.
	 *
	 * When select checkbox is selected, check all
	 * sub items in the list.
	 *
	 */
	const toggleAll = () => {
		if (!selected.includes('all')) {
			// Get all items as selected.
			let newSelected = Object.keys(items)

			// Push all option.
			newSelected.push('all')

			// Update state.
			onChange(newSelected)
		} else {
			// Uncheck all items.
			onChange([])
		}
	}

	return (
		<div
			role="group"
			aria-labelledby="export-options-label"
			className="sui-form-field"
		>
			<Checkbox
				isStacked
				checked={allSelected(selected)}
				onChange={toggleAll}
				id={`${id}-items-all`}
				label={__('All', 'wpmudev_vids')}
				value="all"
				className="sui-checkbox-sm"
			/>

			{Object.keys(items).map((key) => (
				<Checkbox
					key={key}
					onChange={(checkit) => toggleItem(key, checkit)}
					checked={selected.includes(key)}
					id={`${id}-items-${key}`}
					name="export[]"
					label={items[key]}
					value={key}
					className="sui-checkbox-sm"
					tag={counts?.[key]}
				/>
			))}
		</div>
	)
}

export default Configs
