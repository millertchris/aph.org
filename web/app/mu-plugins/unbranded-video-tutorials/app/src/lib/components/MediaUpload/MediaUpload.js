/**
 * Library dependencies
 */
import { Icon } from '../'

/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import classnames from 'classnames'

export function MediaUpload({
	id,
	label,
	mediaTitle,
	buttonText,
	onSelect,
	thumbnail,
	removeFileLabel,
}) {
	let uploader = null

	/**
	 * preview button background style.
	 */
	const previewBtnStyle = {
		backgroundImage: `url(${thumbnail.url})`,
	}

	/**
	 * Get uploaded media data from cache.
	 * @returns {object}
	 */
	const getData = () => uploader?.state()?.get('selection')?.first()?.toJSON()

	/**
	 * Get a new wp media object.
	 *
	 * @returns {object}
	 */
	const getMedia = () => {
		return wp
			.media({
				title: mediaTitle,
				library: {
					type: 'image',
				},
				button: {
					text: buttonText,
				},
				multiple: false,
			})
			.on('select', () => {
				onSelect({
					id: getData().id,
					file: getData().name,
					url: getData().url,
				})
			})
	}

	/**
	 * Open the media upload modal.
	 */
	const openMedia = () => {
		// Create new instance if not available.
		if (null === uploader) {
			uploader = getMedia()
		}

		uploader?.open()
	}

	/**
	 * Check if thumbnail is available.
	 *
	 * @returns {boolean}
	 */
	const hasThumbnail = () => {
		return thumbnail.id > 0 && thumbnail.file && thumbnail.url
	}

	/**
	 * Remove thumbnail data of playlist.
	 */
	const removeThumbnail = () => {
		const emptyThumb = {
			id: 0,
			file: '',
			url: '',
		}

		onSelect(emptyThumb)
	}

	const containerClasses = classnames({
		'sui-upload': true,
		'sui-has_file': !!thumbnail.url,
	})

	return (
		<div className={containerClasses} id={id}>
			<div className="sui-upload-image" aria-hidden="true">
				<div className="sui-image-mask"></div>
				{hasThumbnail ? (
					<div
						role="button"
						className="sui-image-preview"
						style={previewBtnStyle}
					></div>
				) : (
					<div role="button" className="sui-image-preview"></div>
				)}
			</div>
			<button className="sui-upload-button" onClick={openMedia}>
				<Icon icon="upload-cloud" />
				{label}
			</button>
			{hasThumbnail && (
				<div className="sui-upload-file">
					<span>{thumbnail.file}</span>
					<button
						onClick={removeThumbnail}
						aria-label={removeFileLabel}
					>
						<Icon icon="close" />
					</button>
				</div>
			)}
		</div>
	)
}

MediaUpload.defaultProps = {
	id: '',
	label: '$ Upload Media',
	mediaTitle: '$ Media Title',
	buttonText: '$ Button Text',
	onSelect: () => null,
	thumbnail: {},
	removeFileLabel: '$ Remove file lable',
}

MediaUpload.propTypes = {
	id: PropTypes.string,
	label: PropTypes.string.isRequired,
	mediaTitle: PropTypes.string.isRequired,
	buttonText: PropTypes.string.isRequired,
	onSelect: PropTypes.func,
	thumbnail: PropTypes.object,
	removeFileLabel: PropTypes.string,
}

export default MediaUpload
