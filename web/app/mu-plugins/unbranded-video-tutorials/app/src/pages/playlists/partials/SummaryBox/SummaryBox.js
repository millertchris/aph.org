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

const initialRecentState = __('Never', 'wpmudev_vids')

export function SummaryBox() {
	const [count, setCount] = useState(0)
	const [recentlyUpdated, setRecentlyUpdated] = useState(initialRecentState)
	const [recentlyCreated, setRecentlyCreated] = useState(initialRecentState)

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
	function setStats(data) {
		let recent = data.recent
		let counts = data.count

		// Last updated playlist.
		if (recent.updated_playlist.title) {
			setRecentlyUpdated(recent.updated_playlist.title)
		}

		// Last created playlist.
		if (recent.created_playlist.title) {
			setRecentlyCreated(recent.created_playlist.title)
		}

		// Total playlist count.
		if (counts.playlists) {
			setCount(counts.playlists)
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
			label: __('Recently created playlist', 'wpmudev_vids'),
			details: recentlyCreated,
		},
		{
			label: __('Recently updated playlist', 'wpmudev_vids'),
			details: recentlyUpdated,
		},
	]

	return (
		<WhiteLabelSummary isSmall={true}>
			<WhiteLabelSummary.Segment>
				<WhiteLabelSummary.Details>
					<span className="sui-summary-large">{count}</span>
					<span className="sui-summary-sub">
						{__('Playlists', 'wpmudev_vids')}
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
