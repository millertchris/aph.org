/**
 * Library dependencies
 */
import {
	Box,
	ProgressBar,
	Button,
	IconButton,
} from '../../../../../lib/components'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import { WhiteLabelBanner } from '../../../../../components'
import { restGet } from '../../../../../helpers/api'
import { addNotice } from '../../../../../store/slices/notice'

/**
 * External dependencies
 */
import { useState, useEffect } from 'react'
import { useDispatch } from 'react-redux'
import PropTypes from 'prop-types'

export function ImportProgress({ modalID, close, setIsProcessing }) {
	const [progress, setProgress] = useState(0)

	const dispatch = useDispatch()

	useEffect(() => {
		// Update status every 5 seconds.
		const checking = setInterval(updateStatus, 5000)

		// clear setInterval
		return () => {
			clearInterval(checking)
		}
	}, [])

	/**
	 * Get saving status
	 *
	 * @return {void}
	 */
	const updateStatus = () => {
		restGet({
			path: 'data/import/status',
		})
			.then((response) => {
				if (response.success && response.data) {
					updateProgress(response.data)

					if (isCompleted(response.data)) {
						close()

						// Show success notice.
						dispatch(
							addNotice({
								message: __(
									'Settings successfully imported and configured.',
									'wpmudev_vids'
								),
							})
						)

						// Stop Processing
						setIsProcessing(false)
					}
				}
			})
			.catch((err) => {
				console.err(err)
			})
	}

	/**
	 * Update progress bar based on the data.
	 *
	 * @param {object} data Progress data
	 *
	 * @since 1.8.3
	 *
	 * @return {boolean}
	 */
	function updateProgress(data) {
		if (isCompleted(data)) {
			setProgress(100)
		} else {
			const progress = (data.completed / data.total) * 100

			setProgress(isNaN(progress) ? 0 : Math.round(progress))
		}
	}

	/**
	 * Check if the progress is completed.
	 *
	 * @param {object} data Progress data
	 *
	 * @since 1.8.3
	 *
	 * @return {boolean}
	 */
	function isCompleted(data) {
		return data.completed >= data.total
	}

	// Global Variables
	const {
		whitelabel: { hide_branding },
	} = window.ivtVars ?? {}

	return (
		<Box>
			<Box.Header className="sui-flatten sui-content-center sui-spacing-top--60">
				<IconButton
					className="sui-button-float--right"
					icon="close"
					onClick={close}
					size="md"
					outlined={false}
					label={__('Close this dialog.', 'wpmudev_vids')}
				/>

				<h3 id={`${modalID}-title`} className="sui-box-title sui-lg">
					{__('Import', 'wpmudev_vids')}
				</h3>

				<p id={`${modalID}-desc`} className="sui-description">
					{__(
						'Your import is in progress. Downloading thumbnails might take a bit longer depending on the file sizes and volume. Feel free to close this modal as your import will continue running in the background.',
						'wpmudev_vids'
					)}
				</p>
			</Box.Header>
			<Box.Body centerContent={true}>
				<ProgressBar
					description={__('Importing in progress...', 'wpmudev_vids')}
					progress={progress}
				/>
			</Box.Body>
			<Box.Footer isCentered={true} isFlatten={true}>
				<Button
					id={`${modalID}-import-continue`}
					onClick={() => {
						setIsProcessing(false)
						close()
					}}
				>
					{__('Continue in background', 'wpmudev_vids')}
				</Button>
			</Box.Footer>
			{/*{!hide_branding && (
				<WhiteLabelBanner
					src="summary/dashboard.png"
					imageClassName="sui-image sui-image-center"
					alt={__('Import', 'wpmudev_vids')}
			/>
			)}*/}
		</Box>
	)
}

ImportProgress.defaultProps = {
	modalID: 'Modal',
	close: () => null,
}

ImportProgress.propTypes = {
	modalID: PropTypes.string,
	close: PropTypes.func,
}

export default ImportProgress
