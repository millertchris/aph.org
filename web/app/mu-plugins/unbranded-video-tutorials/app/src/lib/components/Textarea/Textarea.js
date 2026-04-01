/**
 * External dependencies
 */
import PropTypes from 'prop-types'

export function Textarea({ id, value, placeholder, onChange }) {
	return (
		<textarea
			placeholder={placeholder}
			value={value}
			className="sui-form-control"
			id={id}
			aria-labelledby={`${id}-desc-label`}
			aria-describedby={`${id}-desc-label`}
			onChange={(e) => onChange(e.target.value)}
		/>
	)
}

Textarea.default = {
	id: '',
	value: 'Text area content',
	placeholder: 'Placeholder text form',
	onChange: () => null,
}

Textarea.propTypes = {
	id: PropTypes.string,
	value: PropTypes.string,
	placeholder: PropTypes.string,
	onChange: PropTypes.func,
}

export default Textarea
