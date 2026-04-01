/**
 * Library dependencies
 */
import { Label, Input, Button } from '../../../../lib/components'

/**
 * Internal dependencies
 */
import { restGet } from '../../../../helpers/api'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from 'react'

export function VideoUrlField({
	modalID,
	url,
	urlReady,
	setVideoUrl,
	updating,
	reset,
	getUrlEmbed,
}) {
	return (
		<>
			<Label
				htmlFor={`${modalID}-video-url`}
				id={`${modalID}-video-url-label`}
			>
				{__('Video URL', 'wpmudev_vids')}
			</Label>

			<div className="sui-with-button sui-with-button-inside">
				<Input
					disabled={urlReady}
					id={`${modalID}-video-url`}
					placeholder=""
					value={url}
					onChange={(val) => {
						if (!val) {
							reset()
						} else {
							setVideoUrl(val)
						}
					}}
				/>

				{urlReady && (
					<Button onClick={reset}>
						{__('Clear', 'wpmudev_vids')}
					</Button>
				)}

				{!urlReady && (
					<Button
						disabled={!url}
						aria-live="polite"
						isLoading={updating}
						onClick={getUrlEmbed}
						onLoadingText={__('Adding', 'wpmudev_vids')}
					>
						{__('Add Video', 'wpmudev_vids')}
					</Button>
				)}
			</div>
		</>
	)
}

export default VideoUrlField
