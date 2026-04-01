/**
 * Library dependencies
 */
import { Icon } from '../../../../lib/components'

/**
 * External dependencies
 */
import { useState, useEffect, useRef } from 'react'
import classnames from 'classnames'

export function StickyMenu({ playlists }) {
	const [sticky, setSticky] = useState(false)
	const [disableRight, setDisableRight] = useState(true)
	const [disableLeft, setDisableLeft] = useState(false)
	const [offsetTop, setOffsetTop] = useState(0)

	const menuRef = useRef()

	const wrapperRef = useRef()

	const navClasses = classnames({
		'wpmudev-videos-section--menu-group': true,
		'wpmudev-videos-sticky': sticky,
	})

	/**
	 * Set the offset position of the menu.
	 */
	useEffect(() => {
		// Menu element.
		const menu = wrapperRef.current

		const rect = menu.getBoundingClientRect()
		const scrollTop =
			window.pageYOffset || document.documentElement.scrollTop

		setOffsetTop(rect.top + scrollTop)
	}, [])

	useEffect(() => {
		// Start listening to scroll and resize events only after offsetTop is set
		if (offsetTop > 0) {
			// Handle scroll event.
			window.addEventListener('scroll', handleScroll)

			// Handle resize event.
			window.addEventListener('resize', updateArrows)

			// Remove event listners when unmount
			return () => {
				window.removeEventListener('scroll', handleScroll)
				window.removeEventListener('resize', updateArrows)
			}
		}
	}, [offsetTop])

	/**
	 * Handle the window scroll event.
	 *
	 * Check if we need to apply sticky class.
	 *
	 * @return {void}
	 */
	const handleScroll = () => {
		// When the page offset is higher than menu.
		setSticky(window.pageYOffset > offsetTop)
	}

	/**
	 * Update navigation arrows when required.
	 *
	 * Call when widnow is resized, items changed.
	 *
	 * @return {void}
	 */
	const updateArrows = () => {
		// Menu element.
		const el = menuRef.current
		const maxScroll = el.scrollWidth - el.clientWidth

		setDisableRight(el.scrollLeft <= 0)
		setDisableLeft(el.scrollLeft >= maxScroll)
	}

	/**
	 * Slide menu elements to new position.
	 *
	 * We slide 30px on each click.
	 *
	 * @param {int} offset Slide position value.
	 *
	 */
	const slideMenu = (offset) => {
		// Menu element.
		const el = menuRef.current

		// Scroll.
		el.scroll({
			left: el.scrollLeft + offset,
			behavior: 'auto',
		})

		// Update the arrows.
		updateArrows()
	}

	/**
	 * Get offset of the element.
	 *
	 * @param {object} el Element.
	 *
	 * @return {*}
	 */
	const getElementOffset = (el) => {
		let top = 0
		let left = 0
		let element = document.getElementById(el)

		if (element !== null) {
			do {
				top += element.offsetTop || 0
				left += element.offsetLeft || 0
				element = element.offsetParent
			} while (element)
		}

		return {
			top,
			left,
		}
	}

	/**
	 * Scroll to a section in the page.
	 *
	 * @param {string} id Element ID.
	 * @param {object} event Event.
	 *
	 */
	const goToSection = (id, event) => {
		const blockId = `section-playlist-${id}`
		const blockPos = getElementOffset(blockId)
		const floatNav = 120

		window.scrollTo({
			top: blockPos.top - floatNav,
			left: 0,
			behavior: 'smooth',
		})

		let i
		let navEl = event.target
		let navLi = navEl.parentElement.parentElement.querySelectorAll('li > a')

		for (i = 0; i < navLi.length; ++i) {
			if (navLi[i].classList.contains('active')) {
				navLi[i].classList.remove('active')
			}
		}

		navEl.classList.add('active')

		event.preventDefault()
	}

	return (
		<div
			role="navigation"
			aria-labelledby="wpmudev-videos-navigation--label"
			aria-hidden="true"
			className={navClasses}
			ref={wrapperRef}
		>
			<div className="wpmudev-videos-section--menu-nav">
				<button
					className="prev"
					disabled={disableRight}
					onClick={() => slideMenu(-20)}
				>
					<Icon icon="chevron-left" size="md" />
				</button>
				<button
					className="next"
					disabled={disableLeft}
					onClick={() => slideMenu(20)}
				>
					<Icon icon="chevron-right" size="md" />
				</button>
			</div>

			<ul
				ref={menuRef}
				role="menu"
				className="wpmudev-videos-section--menu"
			>
				{playlists.map((playlist) => (
					<li role="none" key={playlist.id}>
						<a
							role="menuitem"
							className={2 === playlist.id ? 'active' : ''}
							onClick={(event) => goToSection(playlist.id, event)}
						>
							{playlist.title}
						</a>
					</li>
				))}
			</ul>
		</div>
	)
}

export default StickyMenu
