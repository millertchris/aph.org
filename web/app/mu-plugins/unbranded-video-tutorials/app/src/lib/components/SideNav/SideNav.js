import { NavLink, Route, Routes } from 'react-router-dom'
import PropTypes from 'prop-types'
import { MobileNav } from '../MobileNav'

export function SideNav({ paths, Tag }) {
	return (
		<div className="sui-row-with-sidenav">
			<div className="sui-sidenav">
				<ul className="sui-vertical-tabs sui-sidenav-hide-md">
					{paths.map((path) => (
						<NavLink key={path.location} to={path.location}>
							{({ isActive }) => (
								<Tag
									className={
										isActive
											? 'current sui-vertical-tab'
											: 'sui-vertical-tab'
									}
								>
									{path.title}
								</Tag>
							)}
						</NavLink>
					))}
				</ul>
				<MobileNav paths={paths} />
			</div>
			<Routes>
				{paths.map((path) => (
					<Route
						exact
						key={path.location}
						path={path.location}
						element={path.element}
					/>
				))}
			</Routes>
		</div>
	)
}

SideNav.defaultProps = {
	paths: [
		{
			title: 'Display Settings',
			location: '/',
			element: 'Display Settings Page',
		},
		{
			title: 'data',
			location: '/data',
			element: 'Data Page',
		},
	],
	Tag: 'li',
}

SideNav.propTypes = {
	paths: PropTypes.array,
}

export default SideNav
