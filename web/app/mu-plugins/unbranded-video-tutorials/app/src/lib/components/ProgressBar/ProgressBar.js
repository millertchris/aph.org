import PropTypes from 'prop-types'

export function ProgressBar({ progress, description, showLoader }) {
	const confinedProgress = Math.max(0, Math.min(100, progress))

	return (
		<>
			<div className="sui-progress-block">
				<div className="sui-progress">
					{showLoader && (
						<span className="sui-progress-icon" aria-hidden="true">
							<i className="sui-icon-loader sui-loading"></i>
						</span>
					)}
					<span className="sui-progress-text">
						{confinedProgress}%
					</span>
					<div className="sui-progress-bar" aria-hidden="true">
						<span style={{ width: `${confinedProgress}%` }}></span>
					</div>
				</div>
			</div>
			<p className="sui-description">{description}</p>
		</>
	)
}

ProgressBar.defaultProps = {
	progress: 0,
	description: 'In Progress...',
	showLoader: true,
}

ProgressBar.propTypes = {
	progress: PropTypes.number,
	description: PropTypes.string,
	showLoader: PropTypes.bool,
}

export default ProgressBar
