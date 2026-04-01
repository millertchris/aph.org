import PropTypes from 'prop-types'

export function Modal({ id, children, size }) {
	return (
		<div className={`sui-modal sui-modal-${size}`}>
			<div
				role="dialog"
				className="sui-modal-content sui-content-fade-out"
				aria-modal="true"
				id={id}
				aria-labelledby={`${id}-title`}
				aria-describedby={`${id}-title`}
			>
				{/* Modal script makes a focus action on this element when the modal is closed, if not provided it will cause an issue closing the modal  */}
				<span
					aria-hidden="true"
					style={{ display: 'none' }}
					id={`${id}-close`}
				></span>
				{children}
			</div>
		</div>
	)
}

Modal.defaultProps = {
	id: 'modal',
	children: '',
	size: 'md',
}

Modal.propTypes = {
	id: PropTypes.string,
	children: PropTypes.node,
	size: PropTypes.oneOf(['md', 'sm', 'lg']),
}

export default Modal
