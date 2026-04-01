import PropTypes from 'prop-types'
import classnames from 'classnames'

export function RadioTabs({ options, value, onChange }) {
	return (
		<div className="sui-side-tabs">
			<div className="sui-tabs-menu">
				{options.map((option) => {
					const classes = classnames({
						'sui-tab-item': true,
						active: value === option.value,
					})

					return (
						<label
							key={option.id}
							htmlFor={option.id}
							id={`${option.id}-label`}
							className={classes}
						>
							<input
								onClick={() => {
									onChange(option.value)
								}}
								value={value === option.value}
								id={option.id}
								type="button"
							/>
							{option.label}
						</label>
					)
				})}
			</div>
		</div>
	)
}

RadioTabs.defaultProps = {
	options: [
		{ label: 'option 1', value: 'option-1', id: 'option-1-id' },
		{ label: 'option 2', value: 'option-2', id: 'option-2-id' },
		{ label: 'option 3', value: 'option-3', id: 'option-3-id' },
	],
	value: 'option-2',
	onChange: () => null,
}

RadioTabs.propTypes = {
	options: PropTypes.array.isRequired,
	value: PropTypes.string.isRequired,
	onChange: PropTypes.func.isRequired,
}

export default RadioTabs
