/**
 * Library dependencies
 */
import { Box, Checkbox, Select, Button } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import {
	getSelectedPlaylists,
	bulkDelete,
	initPlaylists,
	toggleAll,
	areAllSelected,
} from '../../../../store/slices/playlists'
import { initVideos } from '../../../../store/slices/videos'
import './style.scss'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from 'react'
import { useSelector, useDispatch } from 'react-redux'

export function BulkActions() {
	const [action, setAction] = useState('')

	const dispatch = useDispatch()

	const selectedPlaylists = useSelector((state) =>
		getSelectedPlaylists(state)
	)

	const allSelected = useSelector((state) => areAllSelected(state))

	const [isDeleting, setIsDeleting] = useState(false)

	const options = {
		delete: __('Delete', 'wpmudev_vids'),
	}

	/**
	 * Bulk Delete Selected playlists & videos
	 *
	 * @return {void}
	 */
	const applyActions = () => {
		setIsDeleting(true)

		dispatch(bulkDelete({ selected: selectedPlaylists }))
			.then((response) => {
				if (response.payload.success && response.payload.data) {
					// Reload playlists
					dispatch(initPlaylists())

					// Reload videos
					dispatch(initVideos())
				}
			})
			.finally(() => {
				setIsDeleting(false)
			})
	}

	/**
	 * Conditions to disable button
	 *
	 * @returns {boolean}
	 */
	const isDisabled = () => {
		return action === '' || Object.keys(selectedPlaylists).length === 0
	}

	/**
	 * Update checked status
	 *
	 * @returns {void}
	 */
	const updateAllSelection = (value) => {
		dispatch(toggleAll({ checked: value }))
	}

	return (
		<Box className="sui-box-sticky">
			<div className="sui-box-search">
				<label
					htmlFor="wpmudev-videos-playlists-bulk-check"
					className="sui-checkbox"
				>
					<Checkbox
						id="wpmudev-videos-playlists-bulk-check"
						label={false}
						checked={allSelected}
						onChange={updateAllSelection}
					/>
				</label>

				<Select
					value={action}
					onChange={setAction}
					placeholder={__('Bulk actions', 'wpmudev_vids')}
					options={options}
					id="wpmudev-videos-playlists-bulk-actions"
					isSmall={true}
					className="sui-select-inline"
					dataWidth="120px"
				/>

				<Button
					onLoadingText={__('Processing', 'wpmudev_vids')}
					icon="check"
					disabled={isDisabled()}
					onClick={applyActions}
					isLoading={isDeleting}
				>
					{__('Apply', 'wpmudev_vids')}
				</Button>
			</div>
		</Box>
	)
}

export default BulkActions
