/**
 * Library dependencies
 */
import { Icon, IconButton } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { VideoItem, VideoPlayer } from '../'
import { getVideos } from '../../../../store/slices/videos'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { useState, useRef, useEffect } from 'react'
import { useSelector } from 'react-redux'

export function SinglePlaylist({ playlist, selected, setSelected }) {
	const [disableRight, setDisableRight] = useState(false)
	const [disableLeft, setDisableLeft] = useState(false)
	const [fullView, setFullView] = useState(false)

	const { id, title, description, videos } = playlist

	const videosList = useRef()

	const videosObject = useSelector((state) => getVideos(state, videos))

	/**
	 * Check if the current section is selected.
	 *
	 * If the selected video is within the current section (playlist).
	 *
	 * @returns {boolean}
	 */
	const isSelected = () => {
		return (
			id === parseInt(selected.playlist) &&
			playlist.videos.includes(parseInt(selected.video))
		)
	}

	const wrapperClasses = classnames({
		'wpmudev-videos-section--block': true,
		'wpmudev-videos-active': isSelected(),
	})

	const btnPrevClasses = classnames(
		'sui-button-icon',
		'sui-button-blue',
		'prev'
	)

	const btnNextClasses = classnames(
		'sui-button-icon',
		'sui-button-blue',
		'next'
	)

	const videoSectionClasses = classnames({
		'wpmudev-videos-section--videos': true,
		'wpmudev-videos-sm': fullView,
	})

	/**
	 * Toggle the full view flag.
	 *
	 * @returns {void}
	 *
	 */
	const toggleFullView = () => {
		setFullView(!fullView)
	}

	/**
	 * Update the arrows if required.
	 *
	 * If there is a scope for scrolling, show arrows.
	 */
	const updateArrows = () => {
		const el = videosList.current
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
		// Videos list.
		const el = videosList.current

		// Scroll.
		el.scroll({
			left: el.scrollLeft + offset,
			behavior: 'auto',
		})

		// Update the arrows.
		updateArrows()
	}

	// Update arrows on mout
	useEffect(() => {
		updateArrows()

		// Handle resize event.
		window.addEventListener('resize', updateArrows)

		return () => {
			// Handle resize event.
			window.removeEventListener('resize', updateArrows)
		}
	}, [])

	return (
		<div
			tabIndex="0"
			id={`section-playlist-${id}`}
			className={wrapperClasses}
		>
			<div className="wpmudev-videos-section--videos-list-header">
				<div className="wpmudev-videos-section--videos-list-content">
					<h2 className="wpmudev-videos-section--block-title">
						{title}
					</h2>
					{description && (
						<p className="wpmudev-videos-section--block-description">
							{description}
						</p>
					)}
				</div>
				<div
					className="wpmudev-videos-section--videos-list-navigator"
					aria-hidden="true"
				>
					<button
						className={btnPrevClasses}
						disabled={disableRight}
						onClick={() => slideMenu(-40)}
					>
						<Icon icon="chevron-left" size="sm" />
					</button>
					<button
						className={btnNextClasses}
						disabled={disableLeft}
						onClick={() => slideMenu(40)}
					>
						<Icon icon="chevron-right" size="sm" />
					</button>
				</div>
			</div>

			<div className={videoSectionClasses}>
				<IconButton
					className="wpmudev-videos-section--videos-list-handler"
					aria-hidden={true}
					icon="chevron-right"
					tabIndex="-1"
					outlined={false}
					onClick={toggleFullView}
				/>
				<div
					role="tablist"
					className="wpmudev-videos-section--videos-list"
					ref={videosList}
				>
					{Object.keys(videosObject).map((videoID) => (
						<VideoItem
							key={videoID}
							setSelected={setSelected}
							selected={selected}
							playlistId={id}
							video={videosObject[videoID]}
						/>
					))}
				</div>

				{isSelected() && (
					<VideoPlayer
						videoId={selected.video}
						playlistId={id}
						isSelected={isSelected()}
						setSelected={setSelected}
					/>
				)}
			</div>
		</div>
	)
}

export default SinglePlaylist
