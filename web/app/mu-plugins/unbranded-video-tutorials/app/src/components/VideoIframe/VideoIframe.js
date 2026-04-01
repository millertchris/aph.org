/**
 * External dependencies
 */
import lazyframe from 'lazyframe'
import classnames from 'classnames'
import { useEffect } from 'react'

export function VideoIframe({ embed, type }) {
	/**
	 * Enable lazy load for custom videos
	 *
	 * Only for videos with custom thumbnail.
	 *
	 */
	const initLazyFrame = () => {
		lazyframe('.lazyframe', {
			lazyload: false,
		})
	}

	// Initialize Lazyframe
	useEffect(() => {
		initLazyFrame()
	}, [embed])

	const classes = classnames({
		'wpmudev-video-container': true,
		'video-iframe': true,
		'wpmudev-video-default': type === 'default',
	})

	return (
		<div className={classes} dangerouslySetInnerHTML={{ __html: embed }} />
	)
}

export default VideoIframe
