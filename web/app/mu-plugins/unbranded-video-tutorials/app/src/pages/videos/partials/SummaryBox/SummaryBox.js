/**
 * Library dependencies
 */
import { WhiteLabelSummary, List } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { restGet } from '../../../../helpers/api'
import { VideoSearchForm } from '../../../../components'

/**
 * External dependencies
 */
import { useEffect, useState } from 'react'
import { __ } from '@wordpress/i18n'

const never = __('Never', 'wpmudev_vids')

const { hosts } = window.ivtModuleVars ?? {}

export function SummaryBox({ search, setSearch }) {
	const [total, setTotal] = useState(0)
	const [recent, setRecent] = useState(never)
	const [recentCustom, setRecentCustom] = useState(never)
	const [host, setHost] = useState('')

	// on mount
	useEffect(() => {
		// Update summary stats
		updateStats()
	}, [])

	/**
	 * Get the host name from key.
	 *
	 * @param {string} host Host key.
	 *
	 * @since 1.8.0
	 *
	 * @return {string}
	 */
	const getHostName = () => {
		return hosts?.[host]?.name || never
	}

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

		// Recent video host.
		if (recent.created_custom_video.video_host) {
			const { video_host } = recent.created_custom_video

			setHost(video_host)
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
			details: recent?.video_title,
			navlink: `/view/${recent?.id}`,
		},
		{
			label: __('Recent video host', 'wpmudev_vids'),
			details: getHostName(),
		},
		{
			label: __('Recent custom video', 'wpmudev_vids'),
			details: recentCustom.video_title,
			navlink: `/view/${recentCustom?.id}`,
		},
	]

	return (
		<WhiteLabelSummary>
			<WhiteLabelSummary.Segment>
				<WhiteLabelSummary.Details>
					<p className="sui-summary-large">{total}</p>
					<p className="sui-summary-sub">
						{__('Videos', 'wpmudev_vids')}
					</p>

					{/** Search Form */}
					<VideoSearchForm search={search} setSearch={setSearch} />
				</WhiteLabelSummary.Details>
			</WhiteLabelSummary.Segment>
			<WhiteLabelSummary.Segment>
				<List items={listItems} />
			</WhiteLabelSummary.Segment>
		</WhiteLabelSummary>
	)
}

export default SummaryBox
