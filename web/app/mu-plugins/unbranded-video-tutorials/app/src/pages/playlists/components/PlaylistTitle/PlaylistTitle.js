/**
 * Library depenedencies
 */
import { Label, Input } from '../../../../lib/components'

/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import { __ } from '@wordpress/i18n'

export function PlaylistTitle({ modalID, title, onChange }) {
	return (
		<div className="sui-form-field">
			<Label
				id={`${modalID}-playlist-title-label`}
				htmlFor={`${modalID}-playlist-title`}
			>
				{__('Playlist name', 'wpmudev_vids')}
			</Label>
			<Input
				placeholder={__('E.g. New playlist', 'wpmudev_vids')}
				id={`${modalID}-playlist-title`}
				value={title}
				onChange={onChange}
			/>
		</div>
	)
}

PlaylistTitle.defaultProps = {
	modalID: 'modal-id',
	title: 'Title',
	onChange: () => null,
}

PlaylistTitle.propTypes = {
	modalID: PropTypes.string,
	title: PropTypes.string,
	onChange: PropTypes.func,
}

export default PlaylistTitle
