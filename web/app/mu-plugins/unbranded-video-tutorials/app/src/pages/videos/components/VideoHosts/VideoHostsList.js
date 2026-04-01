/**
 * External dependencies
 */
import classnames from 'classnames'

const { hosts } = window.ivtModuleVars ?? {}

export function VideoHostsList({ modalID, video, setVideo }) {
	const classes = classnames('sui-box-selectors', 'sui-box-selectors-col-2')

	const { host } = video ?? {}

	/**
	 * Update host key in the video object
	 *
	 * @return {void}
	 */
	const setHost = (event) => {
		setVideo({
			...video,
			host: event.target.value,
		})
	}

	return (
		<div className={classes}>
			<ul>
				{Object.keys(hosts).map((key) => (
					<li key={key}>
						<label
							htmlFor={`${modalID}-video-host-${key}`}
							className="sui-box-selector"
						>
							<input
								type="radio"
								id={`${modalID}-video-host-${key}`}
								name={`${modalID}-video-host-${key}`}
								value={key}
								checked={key === host}
								onChange={setHost}
							/>
							<span>
								{hosts[key].icon && (
									<i
										className={hosts[key].icon}
										aria-hidden="true"
									/>
								)}
								{hosts[key].name}
							</span>
						</label>
					</li>
				))}
			</ul>
		</div>
	)
}

export default VideoHostsList
