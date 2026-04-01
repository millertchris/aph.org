import { imageUrl } from '../../helpers/utils'
import PropTypes from 'prop-types'

export function ImageTag({ src, srcset, alt, className }) {
	/**
	 * Get the default image url.
	 *
	 * @since 1.8.0
	 *
	 * @returns {string}
	 */
	function get1X() {
		return imageUrl(src)
	}

	/**
	 * Get the 2x image path.
	 *
	 * @since 1.8.0
	 *
	 * @returns {string}
	 */
	function get2X() {
		if (srcset) {
			return imageUrl(src.replace(/(\.[\w\d_-]+)$/i, '@2x$1'))
		} else {
			return false
		}
	}

	return (
		<img
			src={get1X()}
			srcSet={`${get1X()} 1x, ${get2X()} 2x`}
			alt={alt}
			aria-hidden="true"
			className={className}
		/>
	)
}

ImageTag.defaultProps = {
	src: '',
	srcset: true,
	alt: 'Image Alt',
	className: '',
}

ImageTag.propTypes = {
	src: PropTypes.string.isRequired,
	srcset: PropTypes.bool,
	alt: PropTypes.string,
	className: PropTypes.string,
}

export default ImageTag
