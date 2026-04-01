/**
 * Library dependencies
 */

import { IconButton } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { VideoIframe } from '../../../../components'
import { fetchEmbed, getEmbed } from '../../../../store/slices/videos'

/**
 * External dependencies
 */
import { useEffect } from 'react'
import { useDispatch, useSelector } from 'react-redux'
import PropTypes from 'prop-types'
import { __ } from '@wordpress/i18n'
import { useNavigate } from 'react-router-dom'

export function VideoRowBody({ video, setOpenedId, disabled }) {
	const { id, video_type } = video ?? {}

	const dispatch = useDispatch()

	const navigate = useNavigate()

	const embed = useSelector((state) => getEmbed(state, id))

	useEffect(() => {
		// If embed is not available in store
		if (!embed) {
			dispatch(fetchEmbed({ id }))
		}
	}, [])

	/**
	 * Close accordion and change route
	 *
	 * @return {null}
	 */
	const closeAccordion = () => {
		// set opened id
		setOpenedId(null)

		// Change route
		navigate('/')
	}

	return (
		<div
			role="region"
			className="wpmudev-videos-accordion--region"
			id={`wpmudev-videos-accordion-item-${id}`}
			aria-labelledby={`wpmudev-videos-accordion-item-${id}--label`}
		>
			<IconButton
				icon="close"
				outlined={false}
				className="wpmudev-videos-show_desktop"
				disabled={disabled}
				onClick={closeAccordion}
				label={__('Close this region', 'wpmudev_vids')}
				tooltip={__('Close video', 'wpmudev_vids')}
			/>

			<IconButton
				icon="close"
				className="wpmudev-videos-show_mobile"
				onClick={closeAccordion}
				disabled={disabled}
				label={__('Close this region', 'wpmudev_vids')}
				tooltip={__('Close video', 'wpmudev_vids')}
			/>

			{/* Video iframe */}
			<VideoIframe embed={embed?.html} type={video_type} />
		</div>
	)
}

VideoRowBody.propTypes = {
	video: PropTypes.object,
	setOpenedId: PropTypes.func,
	disabled: PropTypes.bool,
}

export default VideoRowBody
