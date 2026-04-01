/**
 * Library dependencies
 */
import { WhiteLabelSummary, List } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { restGet } from '../../../../helpers/api'

/**
 * External dependencies
 */
import { useEffect, useState } from 'react'
import { __ } from '@wordpress/i18n'
import moment from 'moment'

const never = __('Never', 'wpmudev_vids')

// Global Variables
const { urls } = window.ivtVars ?? {}
const { videos: videosPageUrl } = urls ?? {}

export function SummaryBox() {
	const [total, setTotal] = useState(0)
	const [recent, setRecent] = useState(never)
	const [recentCustom, setRecentCustom] = useState(never)
	const [recentPlaylist, setRecentPlaylist] = useState()
	const [lastVideoTime, setLastVideoTime] = useState('')

	// on mount
	useEffect(() => {
		// Update summary stats
		updateStats()
	}, [])

	/**
	 * Set the summary stats from the API response.
	 *
	 * @returns {void}
	 */
	const setStats = (data) => {
		const recent = data.recent
		const counts = data.count

		// Last updated video.
		if (recent.updated_video.video_title) {
			setRecent(recent.updated_video)
		}

		// Last created video.
		if (recent.created_custom_video.video_title) {
			setRecentCustom(recent.created_custom_video)
		}

		// Recently created playlist.
		if (recent.created_playlist.title) {
			setRecentPlaylist(recent.created_playlist)
		}

		// Last video updated time.
		if (recent.updated_video.date) {
			const date = moment(recent.updated_video.date)

			// Format to human time.
			setLastVideoTime(date.format('MMMM D, YYYY [at] hh:mm a'))
		}

		// Total video count.
		if (counts.videos) {
			setTotal(counts.videos)
		}
	}

	/**
	 * Update the summary stats using the API.
	 *
	 * @returns {void}
	 */
	function updateStats() {
		restGet({
			path: 'summary',
		}).then((response) => {
			if (response.success && response.data) {
				setStats(response.data)
			}
		})
	}

	// Items
	const listItems = [
		{
			label: __('Recently updated video', 'wpmudev_vids'),
			details: recent.video_title,
			link: `${videosPageUrl}#/view/${recent.id}`,
		},
		{
			label: __('Recently created custom video', 'wpmudev_vids'),
			details: recentCustom.video_title,
			link: `${videosPageUrl}#/view/${recentCustom.id}`,
		},
		{
			label: __('Recently created playlist', 'wpmudev_vids'),
			details: recentPlaylist?.title,
		},
	]

	return (
		<WhiteLabelSummary>
			<WhiteLabelSummary.Segment>
				<WhiteLabelSummary.Details>
					<p className="sui-summary-large">{total}</p>
					<p className="sui-summary-sub">
						{__('Total videos', 'wpmudev_vids')}
					</p>

					{lastVideoTime === '' && (
						<span className="sui-summary-detail">{never}</span>
					)}

					{lastVideoTime !== '' && (
						<span className="sui-summary-detail">
							{lastVideoTime}
						</span>
					)}

					<span className="sui-summary-sub">
						{__('Last uploaded video', 'wpmudev_vids')}
					</span>
				</WhiteLabelSummary.Details>
			</WhiteLabelSummary.Segment>
			<WhiteLabelSummary.Segment>
				<List items={listItems} />
			</WhiteLabelSummary.Segment>
		</WhiteLabelSummary>
	)
}

export default SummaryBox
