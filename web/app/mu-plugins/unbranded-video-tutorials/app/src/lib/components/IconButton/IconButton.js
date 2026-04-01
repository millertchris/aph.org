import PropTypes from 'prop-types'
import classnames from 'classnames'
import iconsList from '../../helpers/icons-list/icons-list'
import { Icon } from '../'
export function IconButton({
	onClick,
	className,
	tooltip,
	outlined,
	color,
	label,
	disabled,
	icon,
	size,
	tag,
	...restProps
}) {
	const classes = classnames({
		'sui-button-icon': true,
		'sui-tooltip': !!tooltip,
		'sui-button-outlined': outlined,
		[`sui-button-${color}`]: color,
		[className]: !!className,
	})
	const Tag = tag

	return (
		<Tag
			className={classes}
			data-tooltip={tooltip || ''}
			disabled={disabled}
			onClick={onClick}
			{...restProps}
		>
			{!!icon && <Icon icon={icon} aria-hidden="true" size={size} />}
			<span className="sui-screen-reader-text">{label}</span>
		</Tag>
	)
}

IconButton.propTypes = {
	label: PropTypes.string,
	icon: PropTypes.oneOf(iconsList),
	outlined: PropTypes.bool,
	tooltip: PropTypes.oneOfType([PropTypes.string, PropTypes.bool]),
	disabled: PropTypes.bool,
	className: PropTypes.string,
	color: PropTypes.oneOf([
		'default',
		'blue',
		'green',
		'red',
		'orange',
		'yellow',
		'purple',
		'white',
	]),
	tag: PropTypes.string,
}

IconButton.defaultProps = {
	label: '',
	icon: 'wpmudev-logo',
	color: 'default',
	outlined: true,
	tooltip: false,
	disabled: false,
	className: '',
	tag: 'button',
	onClick: () => {},
}

export default IconButton
