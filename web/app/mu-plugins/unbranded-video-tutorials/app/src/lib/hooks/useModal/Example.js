import { useModal } from '.'
import { Modal, Box } from '../../components'

function Example() {
	const modalID = 'wpmudev-videos-data-reset-confirmation'

	const [openModal, closeModal] = useModal({
		id: modalID,
	})

	return (
		<>
			{/* Button to open the modal */}
			<button id={`${modalID}-opener`} onClick={openModal}>
				Open Modal
			</button>

			<Modal id={modalID} closeModal={closeModal}>
				<Box>
					<Box.Header className="sui-flatten sui-content-center sui-spacing-top--60">
						<button
							className="sui-button-icon sui-button-float--right"
							onClick={closeModal}
							id={`${modalID}-close`}
						>
							<span
								className="sui-icon-close sui-md"
								aria-hidden="true"
							></span>
							<span className="sui-screen-reader-text"></span>
						</button>

						<h3
							id={`${modalID}-title`}
							className="sui-box-title sui-lg"
						>
							Modal Title
						</h3>

						<p id={`${modalID}-desc`} className="sui-description">
							Modal Description
						</p>
					</Box.Header>
					<Box.Body>Content in the Body</Box.Body>
					<Box.Footer>Content in the Footer</Box.Footer>
				</Box>
			</Modal>
		</>
	)
}

export default Example
