import PropTypes from 'prop-types'
import classnames from 'classnames'

export function Checkbox({
	id,
	label,
	disabled,
	checked,
	onChange,
	isStacked,
	name,
	value,
	className,
	tag,
}) {
	const classes = classnames({
		'sui-checkbox': true,
		'sui-checkbox-stacked': isStacked,
		[className]: !!className,
	})

	return (
		<>
			<label className={classes} htmlFor={id}>
				<input
					type="checkbox"
					disabled={disabled}
					checked={checked}
					onChange={(e) => onChange(e.target.checked)}
					id={id}
					name={name}
					aria-labelledby={`${id}-label`}
					value={value}
				/>
				<span aria-hidden="true"></span>
				{!!label && (
					<span id={`${id}-label`}>
						{label}{' '}
						{!!tag && (
							<span className="sui-tag sui-tag-sm">{tag}</span>
						)}
					</span>
				)}
			</label>
		</>
	)
}

Checkbox.defaultProps = {
	id: 'checkbox-id',
	label: 'label',
	disabled: false,
	checked: false,
	isStacked: false,
	name: '',
	value: '',
	className: '',
	tag: '',
	readOnly: false,
}

Checkbox.propTypes = {
	id: PropTypes.string,
	label: PropTypes.oneOfType([PropTypes.string, PropTypes.bool]),
	onChange: PropTypes.func,
	disabled: PropTypes.bool,
	checked: PropTypes.bool,
	isStacked: PropTypes.bool,
	name: PropTypes.string,
	value: PropTypes.string,
	className: PropTypes.string,
	tag: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
	readOnly: PropTypes.bool,
}

export default Checkbox
