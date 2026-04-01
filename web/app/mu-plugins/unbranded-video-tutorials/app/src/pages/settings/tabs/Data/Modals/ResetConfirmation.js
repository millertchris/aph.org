/**
 * Library dependencies
 */
import { Box, Button, IconButton } from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { useResetData } from '../../../../../hooks'

export function ResetConfirmation({ modalID, closeModal }) {
	const { resetData, isProcessing } = useResetData({
		afterReset: closeModal,
	})

	return (
		<Box>
			<Box.Header className="sui-flatten sui-content-center sui-spacing-top--60">
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={closeModal}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={`${modalID}-title`} className="sui-box-title sui-lg">
					{__('Reset Plugin', 'wpmudev_vids')}
				</h3>

				<p id={`${modalID}-desc`} className="sui-description">
					{__(
						'Are you sure you want to reset the plugin to its default state?',
						'wpmudev_vids'
					)}
				</p>
			</Box.Header>
			<Box.Footer isFlatten={true} isCentered={true}>
				<Button type="ghost" onClick={closeModal}>
					{__('Cancel', 'wpmudev_vids')}
				</Button>
				<Button
					type="ghost"
					icon="undo"
					isLoading={isProcessing}
					onClick={resetData}
					color="red"
					onLoadingText={__('Resetting', 'wpmudev_vids')}
				>
					{__('Reset', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
		</Box>
	)
}

export default ResetConfirmation
