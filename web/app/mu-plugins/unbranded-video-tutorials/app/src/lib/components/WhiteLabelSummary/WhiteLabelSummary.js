import { Box } from '../'
import PropTypes from 'prop-types'
import classnames from 'classnames'
import { isRebranded, isUnbranded, customImageUrl } from '../../../helpers/utils'
/*
 READE THE DOCUMENTATION ON STORYBOOK FOR THIS COMPONENT BEFORE USING IT
*/

export function WhiteLabelSummary({
	children,
	isSmall,
	isUnbranded,
	isRebranded,
	customImageUrl,
}) {

	const classes = classnames({
		'sui-summary': true,
		'sui-summary-sm': isSmall,
		'sui-unbranded': isUnbranded,
		'sui-rebranded': isRebranded,
	})

	/**
	 * Get the background image if white-labelled.
	 *
	 * @returns {Object}
	 */
	const reBrandedStyle = () => {
		if (isRebranded) {
			return {
				backgroundImage: `url(${customImageUrl})`
			}
		}
	}

	return (
		<Box className={classes}>
			<div
				style={reBrandedStyle()}
				className="sui-summary-image-space"
				aria-hidden={true}
			/>
			{children}
		</Box>
	)
}

WhiteLabelSummary.defaultProps = {
	children: '',
	isSmall: false,
	isUnbranded: isUnbranded(),
	isRebranded: isRebranded(),
	customImageUrl: customImageUrl(),
}

WhiteLabelSummary.propTypes = {
	children: PropTypes.node.isRequired,
	isSmall: PropTypes.bool,
	isUnbranded: PropTypes.bool,
	isRebranded: PropTypes.bool,
	customImageUrl: PropTypes.string,
}

/* Sub Components */

// Summary Segment
WhiteLabelSummary.Segment = ({ children = 'Segment Children' }) => {
	return <div className="sui-summary-segment">{children}</div>
}
WhiteLabelSummary.Segment.displayName = 'WhiteLabelSummary.Segment'

// Summary Details
WhiteLabelSummary.Details = ({ children = 'Details Children' }) => {
	return <div className="sui-summary-details">{children}</div>
}
WhiteLabelSummary.Details.displayName = 'WhiteLabelSummary.Details'

export default WhiteLabelSummary
