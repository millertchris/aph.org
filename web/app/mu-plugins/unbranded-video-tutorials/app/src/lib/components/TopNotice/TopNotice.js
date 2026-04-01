import { useEffect } from 'react'
import classnames from 'classnames'
import PropTypes from 'prop-types'

export function TopNotice(props) {
	const { type, dismiss, message, id, label } = props

	const defaultNotice = {
		id: 'top-notice',
		message: '',
		options: {
			type: 'success',
			autoclose: {
				show: false,
				timeout: 3000,
			},
			dismiss: {
				show: false,
				label: label,
			},
		},
	}

	useEffect(() => {
		if (message) {
			// Setup notice options.
			SUI.notice()

			const notice = setupNotice()

			// Now open the notice.
			SUI.openNotice(id, notice.message, notice.options)
		}
	}, [message, dismiss, type, id])

	// component classes
	const classes = classnames({
		'sui-notice': true,
		'sui-notice-info': type === 'info',
		'sui-notice-error': type === 'error',
		'sui-notice-success': type === 'success',
		'sui-notice-warning': type === 'warning',
	})

	/**
	 * Setup notice properties from props.
	 *
	 * @param {string, boolean, string}
	 *
	 * @returns {object}
	 */
	const setupNotice = () => {
		const notice = { ...defaultNotice }

		// Set id.
		notice.id = id

		// Set type.
		notice.options.type = type

		// Is message dismissible.
		notice.options.dismiss.show = dismiss

		// If not dismissible, auto close.
		notice.options.autoclose.show = !dismiss

		// Set notice text.
		notice.message = `<p>${message}</p>`

		return notice
	}

	return (
		<div role="alert" id={id} className={classes} aria-live="assertive" />
	)
}

TopNotice.defaultProps = {
	type: 'success',
	message: 'The notice message',
	label: '',
	dismiss: true,
	id: 'top-notice',
}

TopNotice.propTypes = {
	type: PropTypes.oneOf(['success', 'warning', 'error', 'info']),
	message: PropTypes.string,
	dismiss: PropTypes.bool,
	label: PropTypes.string,
	id: PropTypes.string,
}

export default TopNotice
