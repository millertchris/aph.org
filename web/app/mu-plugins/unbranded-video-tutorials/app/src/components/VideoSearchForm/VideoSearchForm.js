/**
 * Library dependencies
 */
import { Label, Input, IconButton, Button, Icon } from '../../lib/components'

/**
 * Internal dependencies
 */
import { getLoadingStatus } from '../../store/slices/videos'
import { searchVideos } from '../../store/slices/videos'

/**
 * External dependencies
 */
import classnames from 'classnames'
import { useDispatch, useSelector } from 'react-redux'
import { useState } from 'react'
import { __ } from '@wordpress/i18n'

export function VideoSearchForm({
	search,
	setSearch,
	onClear,
	onSearch,
	onLoadingText = false,
}) {
	const dispatch = useDispatch()

	const [showClearIcon, setShowClearIcon] = useState(false)

	const isSearching = useSelector((state) => getLoadingStatus(state))

	/**
	 * do search
	 *
	 * @return {void}
	 */
	const doSearch = () => {
		dispatch(
			searchVideos({
				search,
			})
		).then((result) => {
			const ids = result?.payload?.data || []

			if (typeof onSearch === 'function') {
				onSearch(ids)
			}
		})

		setShowClearIcon(true)
	}

	/**
	 * Clear Search
	 *
	 * @return {void}
	 */
	const clearSearch = () => {
		setSearch('')

		dispatch(
			searchVideos({
				search: '',
			})
		)

		// Call the callback if provided
		if (typeof onClear === 'function') {
			onClear()
		}

		setShowClearIcon(false)
	}

	/**
	 * Do search when hitting enter
	 *
	 * @return {void}
	 */
	const handleInputKeyUp = (event) => {
		if (event.key === 'Enter') {
			doSearch()
		}
	}

	return (
		<div className="sui-form-field">
			<Label isScreenReader={true} htmlFor="wpmudev-videos-search">
				{__('Search videos', 'wpmudev_vids')}
			</Label>
			<div className="sui-with-button">
				<div
					className={classnames(
						'sui-with-button',
						'sui-with-button-inside'
					)}
				>
					<div className="sui-control-with-icon">
						<Input
							placeholder={__('Search videos', 'wpmudev_vids')}
							id="wpmudev-videos-search"
							value={search}
							onChange={setSearch}
							onKeyUp={handleInputKeyUp}
						/>
						<Icon icon="magnifying-glass-search" size="md" />
					</div>
					{showClearIcon && (
						<IconButton
							outlined={false}
							icon="close"
							size="sm"
							label={__('Clear search', 'wpmudev_vids')}
							onClick={clearSearch}
						/>
					)}
				</div>
				<Button
					onLoadingText={onLoadingText}
					isLoading={isSearching}
					color="blue"
					isLarge={true}
					disabled={!search}
					onClick={doSearch}
				>
					{__('Search', 'wpmudev_vids')}
				</Button>
			</div>
		</div>
	)
}

export default VideoSearchForm
