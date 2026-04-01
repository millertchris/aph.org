import { useEffect, useCallback, useState } from 'react'

export function useModal({
	id = 'wpmudev-videos-modal',
	hasOverlayMask = true,
	onOpen = () => null,
	onClose = () => null,
}) {
	const [isOpen, setIsOpen] = useState(false)

	useEffect(() => {
		// Initialize modal.
		SUI?.modalDialog()
	}, [])

	/**
	 * Open the current modal.
	 *
	 * Open the SUI initialized modal using the modal ID.
	 *
	 */
	const openModal = useCallback(() => {
		SUI?.openModal(id, `${id}-close`, `${id}-opener`, hasOverlayMask)

		// call on Open function
		onOpen(id)

		// set isOpen to true
		setIsOpen(true)
	}, [])

	/**
	 * Close the current modal.
	 *
	 * SUI will close the active modal.
	 *
	 */
	const closeModal = useCallback(() => {
		SUI?.closeModal()

		// call onClose function
		onClose(id)

		// set isOpen to false
		setIsOpen(false)
	}, [])

	return [openModal, closeModal, isOpen]
}

export default useModal
