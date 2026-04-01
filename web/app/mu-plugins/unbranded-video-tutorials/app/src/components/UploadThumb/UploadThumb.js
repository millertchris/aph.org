/**
 * Library dependencies
 */
import { MediaUpload } from '../../lib/components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import PropTypes from 'prop-types'

export function UploadThumb({ thumbnail, onSelect, modalID }) {
	return (
		<>
			<MediaUpload
				thumbnail={thumbnail}
				onSelect={onSelect}
				id={`${modalID}-playlist-thumbnail`}
				mediaTitle="Media"
				buttonText={__('Set as thumbnail', 'wpmudev_vids')}
				label={__('Upload image', 'wpmudev_vids')}
				removeFileLabel={__('Remove file', 'wpmudev_vids')}
			/>
		</>
	)
}

UploadThumb.defaultProps = {
	thumbnial: {},
	onSelect: () => null,
	modalID: 'modal-id',
}

UploadThumb.propTypes = {
	thumbnial: PropTypes.object,
	onSelect: PropTypes.func,
	modalID: PropTypes.string,
}

export default UploadThumb
