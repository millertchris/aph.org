import React from 'react'
import PropTypes from 'prop-types'

export function BorderFrame({ children }) {
	return <div className="sui-border-frame">{children}</div>
}

BorderFrame.defaultProps = {
	children: <div>Hi</div>,
}

BorderFrame.propTypes = {
	children: PropTypes.oneOfType([
		PropTypes.string,
		PropTypes.element,
		PropTypes.array,
	]),
}
export default BorderFrame
