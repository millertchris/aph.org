/**
 * Library dependencies
 */
import { Box, Button } from '../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * External dependencies
 */
import { useSelector, useDispatch } from 'react-redux'

/**
 * Internal dependencies
 */
import { saveOptions, getLoadingStatus } from '../../../store/slices/settings'
import { addNotice } from '../../../store/slices/notice'

export function TabsFooter() {
	const dispatch = useDispatch()

	const isSaving = useSelector((state) => getLoadingStatus(state))

	/**
	 * dspatch saveOptions action
	 *
	 * @returns {void}
	 */
	async function saveSettings() {
		dispatch(saveOptions()).then((response) => {
			if (response?.payload?.success) {
				// Success Notice
				dispatch(
					addNotice({
						message: __(
							'Changes were saved successfully.',
							'wpmudev_vids'
						),
					})
				)
			} else {
				// Error Notice
				dispatch(
					addNotice({
						message: __(
							'Sorry. Could not save the changes. Please try again.',
							'wpmudev_vids'
						),
						type: 'error',
					})
				)
			}
		})
	}

	return (
		<Box.Footer>
			<Box.Right>
				<Button
					color="blue"
					icon="save"
					isLoading={isSaving}
					onClick={saveSettings}
					onLoadingText={__('Saving Changes', 'wpmudev_vids')}
				>
					{__('Save Changes', 'wpmudev_vids')}
				</Button>
			</Box.Right>
		</Box.Footer>
	)
}

export default TabsFooter
