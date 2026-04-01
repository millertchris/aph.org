import { Select } from '../'
import { useState, useEffect } from 'react'
import PropTypes from 'prop-types'
import { useNavigate } from 'react-router-dom'

export function MobileNav({ selected, paths }) {
	// Currently selected path
	const [current, setCurrent] = useState(selected)

	const navigate = useNavigate()

	let selectOptions = {}

	paths.forEach((el) => {
		selectOptions[el.location] = el.title
	})

	/**
	 * Change path
	 *
	 * @return {void}
	 *
	 */
	const changePath = (path) => {
		// Update state
		setCurrent(path)

		// Change path
		navigate(path)
	}

	return (
		<div className="sui-sidenav-hide-lg">
			<Select
				value={current}
				options={selectOptions}
				onChange={changePath}
				className="sui-mobile-nav"
			/>
		</div>
	)
}

MobileNav.propTypes = {
	paths: PropTypes.array,
	selected: PropTypes.string,
}

MobileNav.defaultProps = {
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
	selected: '/',
}

export default MobileNav
