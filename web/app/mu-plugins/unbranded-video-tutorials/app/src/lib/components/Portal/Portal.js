import PropTypes from 'prop-types'
import { createPortal } from 'react-dom'

export const Portal = ({ container, children }) => {
	// container must be an HTML DOM element to be able to call createPortal
	if (container instanceof Element) return createPortal(children, container)
	return null
}

Portal.propTypes = {
	container: PropTypes.instanceOf(Element),
	children: PropTypes.element,
}

Portal.displayName = 'Portal'
