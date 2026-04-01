import PropTypes from 'prop-types'
import classnames from 'classnames'
import { Icon } from '../'

export function StaticNotice({ type, children }) {
	// component classes
	const classes = classnames({
		'sui-notice': true,
		'sui-notice-info': type === 'info',
		'sui-notice-error': type === 'error',
		'sui-notice-success': type === 'success',
		'sui-notice-warning': type === 'warning',
		'sui-notice-purple': type === 'purple',
	})

	return (
		<div className={classes}>
			<div className="sui-notice-content">
				<div className="sui-notice-message">
					<Icon icon="info" className="sui-notice-icon" />
					{children}
				</div>
			</div>
		</div>
	)
}

StaticNotice.defaultProps = {
	children: 'Notice Text',
	type: 'info',
}

StaticNotice.propTypes = {
	type: PropTypes.oneOf(['success', 'warning', 'error', 'info', 'purple']),
	children: PropTypes.node,
}

export default StaticNotice
