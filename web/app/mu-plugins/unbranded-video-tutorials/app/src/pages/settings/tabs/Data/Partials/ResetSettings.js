/**
 * Library dependencies
 */
import { Button } from '../../../../../lib/components'
import { useModal } from '../../../../../lib/hooks'
import { Modal } from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { ResetConfirmation } from '../Modals/ResetConfirmation'

export function ResetSettings() {
	const confirmModal = 'wpmudev-videos-data-reset-confirmation'

	const [openModal, closeModal] = useModal({
		id: confirmModal,
	})

	return (
		<>
			<Modal id={confirmModal}>
				<ResetConfirmation
					modalID={confirmModal}
					closeModal={closeModal}
				/>
			</Modal>
			<Button
				icon="undo"
				type="ghost"
				id={`${confirmModal}-opener`}
				onClick={openModal}
				onLoadingText={__('Resetting', 'wpmudev_vids')}
			>
				{__('Reset', 'wpmudev_vids')}
			</Button>
			<p className="sui-description">
				{__(
					'Note: This will instantly revert all settings to their default states but will leave your data intact.',
					'wpmudev_vids'
				)}
			</p>
		</>
	)
}

export default ResetSettings
