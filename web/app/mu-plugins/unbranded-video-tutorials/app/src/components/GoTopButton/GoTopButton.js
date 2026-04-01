/**
 * Library dependencies
 */
import { Icon } from '../../lib/components'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { useState, useEffect } from 'react'
import { __ } from '@wordpress/i18n'

export function GoTopButton() {
	const [show, setShow] = useState(false)

	const classes = classnames({
		'wpmudev-videos-to-top': true,
		'wpmudev-videos-show': show,
	})

	useEffect(() => {
		const handleScroll = () => {
			const isShown =
				document.body.scrollTop > 20 ||
				document.documentElement.scrollTop > 20

			setShow(isShown)
		}

		// Add event listener when component mounts
		window.addEventListener('scroll', handleScroll)

		// Remove event listener when component unmounts
		return () => {
			window.removeEventListener('scroll', handleScroll)
		}
	}, [])

	/**
	 * When clicked, scroll to top smoothly.
	 *
	 * @since 1.8.0
	 */
	const scrollTop = () => {
		// Scroll to top.
		jQuery('html, body').animate({ scrollTop: 0 }, 'slow')

		return false
	}

	return (
		<div className={classes}>
			<div
				className="sui-tooltip sui-tooltip-top-right sui-constrained"
				style={{ '--toltip-width': '100px' }}
				data-tooltip={__('Go to top', 'wpmudev_vids')}
			>
				<button
					className="wpmudev-videos-button--to-top"
					onClick={scrollTop}
				>
					<Icon icon="chevron-up" size="sm" />
					<span className="sui-screen-reader-text">
						{__('Go to top', 'wpmudev_vids')}
					</span>
				</button>
			</div>
		</div>
	)
}

export default GoTopButton
