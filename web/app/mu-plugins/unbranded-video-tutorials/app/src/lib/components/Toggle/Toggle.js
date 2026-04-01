import PropTypes from 'prop-types'

export function Toggle({
	label,
	onChange,
	checked,
	id,
	name,
	value,
	tooltip,
	disabled,
}) {
	return (
		<>
			<label htmlFor={id} className="sui-toggle">
				<input
					checked={checked}
					type="checkbox"
					id={id}
					aria-labelledby={`${id}-label`}
					aria-describedby={`${id}-tab-desc`}
					onChange={(e) => {
						onChange(e.target.checked)
					}}
					name={name}
					value={value}
					disabled={disabled}
				/>
				<span className="sui-toggle-slider" aria-hidden="true" />
				<span id={`${id}-label`} className="sui-toggle-label">
					{label}
					{!!tooltip && (
						<span className="sui-tooltip" data-tooltip={tooltip}>
							<span
								className="sui-icon-info"
								aria-hidden="true"
							></span>
						</span>
					)}
				</span>
			</label>
		</>
	)
}

Toggle.defaultProps = {
	label: 'toggle',
	id: 'Toggle',
	checked: false,
	onChange: () => null,
	name: '',
	value: false,
	tooltip: '',
	disabled: false,
}

Toggle.propTypes = {
	label: PropTypes.string,
	onChange: PropTypes.func,
	id: PropTypes.string.isRequired,
	checked: PropTypes.bool,
	name: PropTypes.string,
	value: PropTypes.oneOfType([PropTypes.bool, PropTypes.string]),
	tooltip: PropTypes.string,
	disabled: PropTypes.bool,
}

export default Toggle
