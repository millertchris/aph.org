import PropTypes from 'prop-types'
import classnames from 'classnames'
import iconsList from '../../helpers/icons-list/icons-list'

export function Button({
	color,
	children,
	icon,
	isLoading,
	onLoadingText,
	onClick,
	onMouseEnter,
	onMouseLeave,
	role,
	id,
	disabled,
	className,
	isLarge,
	type,
	...restProps
}) {
	const classes = classnames({
		'sui-button': true,
		[`sui-button-${color}`]: 'default' !== color,
		'sui-button-onload-text': isLoading && onLoadingText,
		'sui-button-onload': isLoading && !onLoadingText,
		'sui-button-lg': isLarge,
		'sui-button-ghost': 'ghost' === type,
		[className]: !!className,
	})

	return (
		<button
			id={id}
			role={role}
			aria-live="polite"
			type="button"
			className={classes}
			onClick={onClick}
			onMouseEnter={onMouseEnter}
			onMouseLeave={onMouseLeave}
			disabled={disabled}
			{...restProps}
		>
			{onLoadingText && (
				<>
					<span className="sui-button-text-default">
						{!!icon && (
							<i
								className={`sui-icon-${icon}`}
								aria-hidden="true"
							></i>
						)}
						{children}
					</span>
					<span className="sui-button-text-onload">
						<i
							className="sui-icon-loader sui-loading"
							aria-hidden="true"
						></i>
						{onLoadingText}
					</span>
				</>
			)}

			{!onLoadingText && (
				<>
					<span className="sui-loading-text">{children}</span>
					<i
						className="sui-icon-loader sui-loading"
						aria-hidden="true"
					></i>
				</>
			)}
		</button>
	)
}

Button.defaultProps = {
	color: 'default',
	children: 'button',
	icon: '',
	isLoading: false,
	onLoadingText: 'Loading',
	id: '',
	role: 'button',
	onClick: () => null,
	onMouseEnter: () => null,
	onMouseLeave: () => null,
	disabled: false,
	className: '',
	isLarge: false,
	type: '',
}

Button.propTypes = {
	icon: PropTypes.oneOf(iconsList),
	children: PropTypes.string,
	isLoading: PropTypes.bool,
	onLoadingText: PropTypes.oneOfType([PropTypes.string, PropTypes.bool]),
	id: PropTypes.string,
	role: PropTypes.string,
	onClick: PropTypes.func,
	disabled: PropTypes.bool,
	className: PropTypes.string,
	onMouseEnter: PropTypes.func,
	onMouseLeave: PropTypes.func,
	isLarge: PropTypes.bool,
	color: PropTypes.oneOf([
		'default',
		'gray',
		'blue',
		'green',
		'red',
		'orange',
		'yellow',
		'purple',
		'white',
	]),
	type: PropTypes.oneOf(['', 'ghost']),
}

export default Button
