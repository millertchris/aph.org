import PropTypes from 'prop-types'
import { Icon } from '../'
import iconsList from '../../helpers/icons-list/icons-list'
import classnames from 'classnames'

export function Input({
	placeholder,
	value,
	onChange,
	onKeyUp,
	id,
	icon,
	className,
	disabled,
}) {
	const classes = classnames({
		'sui-form-control': true,
		[className]: !!className,
	})

	const inputEl = (
		<input
			className={classes}
			placeholder={placeholder}
			value={value}
			id={id}
			aria-labelledby={`${id}-label`}
			aria-describedby={`${id}-desc`}
			onChange={(e) => onChange(e.target.value)}
			disabled={disabled}
			onKeyUp={onKeyUp}
		/>
	)
	if (!!icon) {
		return (
			<div className="sui-control-with-icon">
				<Icon icon={icon} />
				{inputEl}
			</div>
		)
	}
	return inputEl
}

Input.defaultProps = {
	icon: '',
	placeholder: 'Placeholder Text...',
	value: '',
	onChange: () => null,
	id: 'input',
	disabled: false,
	onKeyUp: () => null,
}

Input.propTypes = {
	icon: PropTypes.oneOf(iconsList),
	placeholder: PropTypes.string,
	value: PropTypes.string,
	onChange: PropTypes.func,
	id: PropTypes.string,
	disabled: PropTypes.bool,
	onKeyUp: PropTypes.func,
}

export default Input
