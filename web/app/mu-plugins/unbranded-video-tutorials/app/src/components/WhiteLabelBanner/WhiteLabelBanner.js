import PropTypes from 'prop-types'
import { ImageTag } from '../'
import classnames from 'classnames'

export function WhiteLabelBanner({
	src,
	alt,
	srcset,
	className,
	imageClassName,
}) {
	const classes = classnames({
		'sui-box-banner': true,
		className: !!className,
	})

	return (
		<figure className={classes}>
			<ImageTag
				src={src}
				srcSet={srcset}
				alt={alt}
				className={imageClassName}
			/>
		</figure>
	)
}

WhiteLabelBanner.defaultProps = {
	src: '',
	alt: 'Image Alt',
	className: '',
	srcset: true,
	imageClassName: '',
}

WhiteLabelBanner.propTypes = {
	src: PropTypes.string.isRequired,
	alt: PropTypes.string,
	className: PropTypes.string,
	srcset: PropTypes.bool,
	imageClassName: PropTypes.string,
}

export default WhiteLabelBanner
