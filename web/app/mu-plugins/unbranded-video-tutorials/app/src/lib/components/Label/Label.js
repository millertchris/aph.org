/**
 * External Depnedencies
 */
import PropTypes from 'prop-types'
import classnames from 'classnames'

export function Label({ children, id, htmlFor, className, isScreenReader }) {
	const classes = classnames({
		'sui-label': !isScreenReader,
		'sui-screen-reader-text': isScreenReader,
		[className]: !className,
	})

	return (
		<label className={classes} htmlFor={htmlFor} id={id}>
			{children}
		</label>
	)
}

Label.defaultProps = {
	children: 'Label Text',
	id: '',
	htmlFor: '',
	isScreenReader: false,
}

Label.propTypes = {
	children: PropTypes.string,
	id: PropTypes.string,
	htmlFor: PropTypes.string,
	isScreenReader: PropTypes.bool,
}

export default Label
