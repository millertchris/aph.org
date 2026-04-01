import PropTypes from 'prop-types'
import { NavLink } from 'react-router-dom'

export function List({ items, linksTarget }) {
	return (
		<ul className="sui-list">
			{items.map((item) => (
				<li key={item.label}>
					<span className="sui-list-label" title={item.label}>
						{item.label}
					</span>
					<span className="sui-list-detail" v-html="recentlyCreated">
						{!!item.link && (
							<a href={item.link} target={linksTarget}>
								{item.details}
							</a>
						)}

						{!!item.navlink && (
							<NavLink to={item.navlink}>{item.details}</NavLink>
						)}

						{!item.link && !item.navlink && item.details}
					</span>
				</li>
			))}
		</ul>
	)
}

List.defaultProps = {
	items: [
		{
			label: 'Label 01',
			details: 'Label 01 Details',
		},
		{
			label: 'Label 02',
			details: 'Label 02 Details',
			link: 'https://wpmudev.com',
		},
	],
	linksTarget: '_self',
}

List.propTypes = {
	items: PropTypes.array,
	linksTarget: PropTypes.oneOf(['_self', '_blank', '_parent', '_top']),
}

export default List
