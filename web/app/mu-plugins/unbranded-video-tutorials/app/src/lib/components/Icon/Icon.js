import PropTypes from 'prop-types'
import classnames from 'classnames'
import iconsList from '../../helpers/icons-list/icons-list'

export function Icon({ icon, size, animate, className }) {
	const classes = classnames({
		[`sui-icon-${icon}`]: !!icon,
		[`sui-${size}`]: !!size,
		'sui-loading': animate,
		[className]: !!className,
	})

	return <span aria-hidden="true" className={classes}></span>
}

Icon.propTypes = {
	icon: PropTypes.oneOf(iconsList),
	size: PropTypes.oneOf(['', 'sm', 'md', 'lg', 'xl']),
	animate: PropTypes.bool,
	className: PropTypes.string,
}

Icon.defaultProps = {
	icon: 'check',
	size: '',
	animate: false,
	className: '',
}

export default Icon
