import { useState, useEffect } from 'react';
/**
 * Library dependencies
 */
import { Checkbox, Input } from '../../../../lib/components'

/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n'
import classnames from 'classnames'
import { timeRegex } from '../../../../utils'

export function TimeForm({ video, setVideo, modalID }) {
	const { start_enabled, end_enabled, end_time, start_time } = video ?? {};
	const [ startTimeError, setStartTimeError ] = useState( false );
	const [ endTimeError, setEndTimeError ] = useState( false );

	useEffect(() => {
		if ( start_enabled ) {
			validateTime( start_time, setStartTimeError );
		}
		if ( end_enabled ) {
			validateTime(end_time, setEndTimeError);
		}
	}, [ start_enabled, end_enabled, start_time, end_time ] );

	const validateTime = ( time, setError ) => {
		if ( time && ! timeRegex( time ) ) {
			setError( true );
		} else {
			setError( false );
		}
	};

	const setEnabled = ({ type = 'start', value }) => {
		const finalValue = value ? 1 : 0;

		setVideo({
			...video,
			[`${type}_enabled`]: finalValue,
		})
	};

	const setTime = ({ type = 'start', value }) => {
		validateTime( value, type === 'start' ? setStartTimeError : setEndTimeError );

		setVideo({
			...video,
			[`${type}_time`]: value,
		})
	};

	return (
		<>
			<div className={classnames('sui-form-field', { 'sui-form-field-error': startTimeError })}>
				<span className="sui-field-prefix">
					<Checkbox
						checked={!!start_enabled}
						label={__('Start at:', 'wpmudev_vids')}
						id={`${modalID}-video-start`}
						onChange={(value) =>
							setEnabled({ type: 'start', value })
						}
					/>
					<Input
						id="start-time"
						placeholder="00:00"
						value={start_time}
						checked={end_enabled}
						disabled={!start_enabled}
						onChange={(value) => setTime({ type: 'start', value })}
						className={classnames(
							'sui-input-sm',
							'sui-field-has-prefix'
						)}
					/>
					{ startTimeError && (
						<span id="error-start-time" className="sui-error-message" role="alert">
							{ __('Invalid time format.', 'wpmudev_vids') }
						</span>
					)}
				</span>
			</div>
			{typeof video.host !== 'undefined' && video.host === 'youtube' && (
			<div className={classnames('sui-form-field', { 'sui-form-field-error': endTimeError })}>
				<span className="sui-field-prefix">
					<Checkbox
						label={__('End at:', 'wpmudev_vids')}
						id={`${modalID}-video-end`}
						checked={!!end_enabled}
						onChange={(value) => setEnabled({ type: 'end', value })}
					/>
					<Input
						id="end-time"
						placeholder="00:00"
						value={end_time}
						disabled={!end_enabled}
						onChange={(value) => setTime({ type: 'end', value })}
						className={classnames(
							'sui-input-sm',
							'sui-field-has-prefix'
						)}
					/>
					{ endTimeError && (
						<span id="error-end-time" className="sui-error-message" role="alert">
							{ __('Invalid time format.', 'wpmudev_vids') }
						</span>
					) }
				</span>
			</div>
			)}
		</>
	)
}

export default TimeForm
