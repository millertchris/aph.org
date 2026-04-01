import classnames from 'classnames'

export function Box({ children, className, id = '' }) {
	return (
		<div
			id={id}
			className={classnames({
				'sui-box': true,
				[className]: !!className,
			})}
		>
			{children}
		</div>
	)
}

/** Those are children components that you can use freely inside the box compoonent, check storybook for an example */

// Box Header
Box.Header = ({ children, className }) => {
	const classes = classnames({
		'sui-box-header': true,
		[className]: !!className,
	})
	return <div className={classes}>{children}</div>
}
Box.Header.displayName = 'Box.Header'

// Box Title
Box.Title = ({ children = 'Box Title', className, ...restProps }) => {
	const classes = classnames({
		'sui-box-title': true,
		[className]: !!className,
	})
	return (
		<h3 className={classes} {...restProps}>
			{children}
		</h3>
	)
}
Box.Title.displayName = 'Box.Title'

// Box Body
Box.Body = ({ children, centerContent = false }) => {
	return (
		<div
			className={classnames({
				'sui-box-body': true,
				'sui-content-center': centerContent,
			})}
		>
			{children}
		</div>
	)
}
Box.Body.displayName = 'Box.Body'

// Box Row
Box.Row = ({ children, isFlushed = false }) => {
	return (
		<div
			className={classnames({
				'sui-box-settings-row': true,
				'sui-flushed': isFlushed,
			})}
		>
			{children}
		</div>
	)
}
Box.Row.displayName = 'Box.Row'

// Box Column 1: with a maximum width of 200px
Box.Col1 = ({ children }) => {
	return <div className="sui-box-settings-col-1">{children}</div>
}
Box.Col1.displayName = 'Box.Col1'

// Box Column 2: Takes available space.
Box.Col2 = ({ children, id = '' }) => {
	return (
		<div id={id} className="sui-box-settings-col-2">
			{children}
		</div>
	)
}
Box.Col2.displayName = 'Box.Col2'

// Box Label
Box.Label = ({ children = 'Label' }) => {
	return <span className="sui-settings-label">{children}</span>
}
Box.Label.displayName = 'Box.Label'

// Box Description
Box.Description = ({
	children = 'Description Goes Here!',
	Tag = 'span',
	...restProps
}) => (
	<Tag className="sui-description" {...restProps}>
		{children}
	</Tag>
)
Box.Description.displayName = 'Box.Description'

// Box Footer
Box.Footer = ({
	children,
	isFlushed = false,
	isCentered = false,
	isFlatten = false,
}) => {
	return (
		<div
			className={classnames({
				'sui-box-footer': true,
				'sui-flushed': isFlushed,
				'sui-content-center': isCentered,
				'sui-flatten': isFlatten,
			})}
		>
			{children}
		</div>
	)
}
Box.Footer.displayName = 'Box.Footer'

// Box Right
Box.Right = ({ children }) => {
	return <div className="sui-actions-right">{children}</div>
}
Box.Right.displayName = 'Box.Right'

export default Box
