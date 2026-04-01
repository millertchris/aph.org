import { useRef, useEffect } from 'react'
import PropTypes from 'prop-types'
import classnames from 'classnames'

export function Accordion({ className, children }) {
	const accordionEl = useRef()

	const classes = classnames({
		'sui-accordion': true,
		'sui-accordion-block': true,
		[className]: !!className,
	})

	useEffect(() => {
		// Intialize accordion.
		SUI.suiAccordion(accordionEl.current)
	}, [accordionEl.current])

	return (
		<div ref={accordionEl} className={classes}>
			{children}
		</div>
	)
}

Accordion.defaultProps = {
	className: '',
	children: '',
}

Accordion.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node,
}

// Accordion Item
Accordion.Item = ({ id, className, children, ...restProps }) => {
	const classes = classnames({
		'sui-accordion-item': true,
		[className]: !!className,
	})

	return (
		<div id={id} className={classes} {...restProps}>
			{children}
		</div>
	)
}
Accordion.Item.displayName = 'Accordion.Item'

Accordion.Item.defaultProps = {
	id: 'item-id',
	className: '',
	children: '',
}

Accordion.Item.propTypes = {
	id: PropTypes.string,
	className: PropTypes.string,
	children: PropTypes.node,
}

// Accordion Header
Accordion.Header = ({ className, children, ...restProps }) => {
	const classes = classnames({
		'sui-accordion-item-header': true,
		[className]: !!className,
	})

	return (
		<div className={classes} {...restProps}>
			{children}
		</div>
	)
}
Accordion.Header.displayName = 'Accordion.Header'

Accordion.Header.defaultProps = {
	className: '',
	children: '',
}

Accordion.Header.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node,
}

// Accordion Title
Accordion.Title = ({ className, children }) => {
	const classes = classnames({
		'sui-accordion-item-title': true,
		'sui-trim-title': true,
		[className]: !!className,
	})

	return <div className={classes}>{children}</div>
}
Accordion.Title.displayName = 'Accordion.Title'

Accordion.Title.defaultProps = {
	className: '',
	children: '',
}

Accordion.Title.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node,
}

// Accordion Date
Accordion.Date = ({ className, children }) => {
	const classes = classnames({
		'sui-accordion-item-date': true,
		[className]: !!className,
	})

	return <div className={classes}>{children}</div>
}
Accordion.Date.displayName = 'Accordion.Date'

Accordion.Date.defaultProps = {
	className: '',
	children: '',
}

Accordion.Date.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node,
}

// Accordion AutoCol
Accordion.AutoCol = ({ className, children }) => {
	const classes = classnames({
		'sui-accordion-col-auto': true,
		[className]: !!className,
	})

	return <div className={classes}>{children}</div>
}

Accordion.AutoCol.displayName = 'Accordion.AutoCol'

Accordion.AutoCol.defaultProps = {
	className: '',
	children: '',
}

Accordion.AutoCol.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node,
}

// Accordion Body
Accordion.Body = ({ className, children }) => {
	const classes = classnames({
		'sui-accordion-item-body': true,
		[className]: !!className,
	})

	return <div className={classes}>{children}</div>
}

Accordion.Body.displayName = 'Accordion.Body'

Accordion.Body.defaultProps = {
	className: '',
	children: '',
}

Accordion.Body.propTypes = {
	className: PropTypes.string,
	children: PropTypes.node,
}
export default Accordion
