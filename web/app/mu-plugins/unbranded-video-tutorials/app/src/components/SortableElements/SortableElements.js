/**
 * External dependencies
 */
import { useRef, useEffect } from 'react'
import PropTypes from 'prop-types'
import Sortable from 'sortablejs'

export function SortableElements({ children, handle, filter, onUpdate }) {
	const container = useRef()

	// Initialize sortable on mount
	useEffect(() => {
		initSortable()
	}, [])

	/**
	 * Initialize sortable element
	 */
	const initSortable = () => {
		Sortable.create(container.current, {
			handle,
			filter,
			animation: 150,
			onUpdate,
		})
	}

	return <div ref={container}>{children}</div>
}

SortableElements.defaultProps = {
	handle: '.sortable',
	filter: '',
}

SortableElements.propTypes = {
	handle: PropTypes.string,
	filter: PropTypes.string,
}

export default SortableElements
