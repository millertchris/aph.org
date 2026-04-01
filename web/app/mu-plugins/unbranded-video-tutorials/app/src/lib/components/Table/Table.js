/**
 * External dependencies
 */
import classnames from 'classnames'
import PropTypes from 'prop-types'

// Table
export function Table({ isFlushed, children, className }) {
	const classes = classnames({
		'sui-table': true,
		'sui-table-flushed': isFlushed,
		[className]: !!className,
	})
	return <table className={classes}>{children}</table>
}

Table.defaultProps = {
	isFlushed: true,
	children: '',
	className: '',
}

Table.propTypes = {
	isFlushed: PropTypes.bool,
	children: PropTypes.node,
	className: PropTypes.string,
}

/**
 * Sub Components
 */
Table.Thead = (props) => <thead {...props}>{props.children}</thead>
Table.Tbody = (props) => <tbody {...props}>{props.children}</tbody>
Table.Tr = (props) => <tr {...props}>{props.children}</tr>
Table.Td = (props) => <td {...props}>{props.children}</td>
Table.Th = (props) => <th {...props}>{props.children}</th>
Table.Tfoot = (props) => <tfoot {...props}>{props.children}</tfoot>

// Display names
Table.Thead.displayName = 'Table.Thead'
Table.Tbody.displayName = 'Table.Tbody'
Table.Tr.displayName = 'Table.Tr'
Table.Td.displayName = 'Table.Td'
Table.Th.displayName = 'Table.Th'
Table.Tfoot.displayName = 'Table.Tfoot'

export default Table
