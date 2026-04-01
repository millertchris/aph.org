/**
 * External dependencies
 */
import PropTypes from 'prop-types'
import classnames from 'classnames'

export function ListThumb({ url, icon, tag, isCustom, hideIcon, className }) {
	/**
	 * Get the style to set the custom thumb.
	 *
	 * If url is empty, we don't have to
	 * set the styles.
	 *
	 */
	const getStyle = () => {
		if (url) {
			return {
				backgroundImage: `url(${url})`,
			}
		}
	}

	/**
	 * Get the icon for the thumb.
	 *
	 * If custom video, we need to use custom icon
	 * if the thumb is empty.
	 *
	 * @return {string}
	 */
	const getIcon = () => {
		if (isCustom && !url) {
			return 'custom'
		} else if (!isCustom) {
			return icon
		} else {
			return null
		}
	}

	const classes = classnames({
		'wpmudev-videos-list-thumb': true,
		dashicons: (!isCustom || !url) && !hideIcon,
		[className]: !!className,
	})

	const Tag = tag

	return (
		<Tag
			className={classes}
			aria-hidden={true}
			style={getStyle()}
			data-icon={getIcon()}
		/>
	)
}

ListThumb.defaultProps = {
	url: '',
	icon: 'custom',
	tag: 'div',
	hideIcon: false,
	className: '',
	isCustom: false,
}

ListThumb.propTypes = {
	url: PropTypes.string,
	icon: PropTypes.string,
	hideIcon: PropTypes.bool,
	tag: PropTypes.string,
	className: PropTypes.string,
	isCustom: PropTypes.bool,
}

export default ListThumb
