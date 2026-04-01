/**
 * Library dependencies
 */
import { Input } from '../../../../lib/components'
import { useDebouncedValue } from '../../../../lib/hooks'

/**
 * Internal dependencies
 */
import { restGet } from '../../../../helpers/api'
import { setFiltered } from '../../../../store/slices/videos'

/**
 * External dependencies
 */
import { useState, useEffect } from 'react'
import { __ } from '@wordpress/i18n'
import { useDispatch } from 'react-redux'
import PropTypes from 'prop-types'

export function VideoSearchInput({ id }) {
	const dispatch = useDispatch()

	const [search, setSearch] = useState('')

	const [debouncedSearch, setDebouncedSearch] = useDebouncedValue({
		value: search,
		delay: 300,
	})

	useEffect(() => {
		searchVideos()
	}, [debouncedSearch])

	const updateSearch = (value) => {
		// Update search value
		setSearch(value)

		// Update debounced value
		setDebouncedSearch(value)
	}

	/**
	 * Search the videos and filter.
	 *
	 * Get the video ids using the search term
	 * and sync back to parent.
	 *
	 */
	const searchVideos = () => {
		restGet({
			path: 'videos',
			params: {
				search: search,
				field: 'ids',
			},
		}).then((response) => {
			if (response.success && response.data) {
				// Update the filtered ids.
				dispatch(setFiltered({ ids: response.data }))
			}
		})
	}

	return (
		<Input
			icon="magnifying-glass-search"
			value={search}
			onChange={updateSearch}
			placeholder={__('Search videos', 'wpmudev_videos')}
			id={id}
		/>
	)
}

VideoSearchInput.propTypes = {
	id: 'id',
}

VideoSearchInput.propTypes = {
	id: PropTypes.string,
}

export default VideoSearchInput
