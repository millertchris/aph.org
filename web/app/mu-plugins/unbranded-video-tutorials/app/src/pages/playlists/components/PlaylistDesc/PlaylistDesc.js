/**
 * Library dependencies
 */
import { Label, Textarea } from '../../../../lib/components'

/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { __ } from '@wordpress/i18n'

export function PlaylistDesc({ modalID, onChange, description }) {
	return (
		<div className="sui-form-field">
			<Label
				htmlFor={`${modalID}-playlist-desc`}
				id={`${modalID}-playlist-desc-label`}
			>
				{__('Playlist description', 'wpmudev_vids')}
			</Label>
			<Textarea
				id={`${modalID}-playlist-desc`}
				aria-labelledby={`${modalID}-playlist-desc-label`}
				aria-describedby={`${modalID}-playlist-desc-desc`}
				value={description}
				onChange={onChange}
			/>
		</div>
	)
}

PlaylistDesc.defaultProps = {
	modalID: 'modal-id',
	description: 'description',
	onChange: () => null,
}

PlaylistDesc.propTypes = {
	modalID: PropTypes.string,
	description: PropTypes.string,
	onChange: PropTypes.func,
}

export default PlaylistDesc
