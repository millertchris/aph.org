/**
 * Library dependencies
 */
import { Box } from '../../lib/components'

/**
 * Internal dependencies
 */
import { PageFooter } from '../../components/PageFooter'
import { Header, VideosSearch, VideosList } from './partials'
import './styles/main.scss'
import { initPlaylists } from '../../store/slices/playlists'
import { initVideos } from '../../store/slices/videos'

/**
 * External dependencies
 */
import { useDispatch } from 'react-redux'
import { useEffect, useState } from 'react'
import { Routes, Route } from 'react-router-dom'

export function App() {
	const [search, setSearch] = useState('')
	const [searchMsgType, setSearchMsgType] = useState('clear')
	const [resultsCount, setResultsCount] = useState(0)
	const [hideMessage, setHideMessage] = useState(true)

	const dispatch = useDispatch()

	// Load playlists and videos on mount
	useEffect(() => {
		dispatch(initPlaylists())
		dispatch(initVideos())
	}, [])

	/**
	 * When getting search results
	 *
	 * @return {void}
	 */
	const showAlert = () => {
		// Show message
		setHideMessage(false)

		// Hide again after 3 seconds.
		setTimeout(() => {
			setHideMessage(true)
		}, 3000)
	}

	/**
	 * When getting search results
	 *
	 * @return {voind}
	 */
	const onSearch = (ids) => {
		setResultsCount(ids.length)
		setSearchMsgType('search')

		// Show Alert
		showAlert()
	}

	/**
	 * When search is cleared
	 *
	 * @return {void}
	 */
	const onClear = () => {
		setSearchMsgType('clear')

		// Show Alert
		showAlert()
	}

	return (
		<div className="sui-wrap">
			<Box>
				<Header />
				<VideosSearch
					onSearch={onSearch}
					onClear={onClear}
					search={search}
					setSearch={setSearch}
				/>
				<Routes>
					<Route
						exact
						path="/:action?/:playlist?/:video?"
						element={
							<VideosList
								searchMsgType={searchMsgType}
								hideMessage={hideMessage}
								resultsCount={resultsCount}
							/>
						}
					/>
				</Routes>
			</Box>
			<PageFooter />
		</div>
	)
}

export default App
