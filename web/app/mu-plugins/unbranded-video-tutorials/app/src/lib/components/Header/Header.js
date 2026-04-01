import PropTypes from 'prop-types'
import { Icon } from '../'

export function Header({
	title,
	docLink,
	showDocLink,
	actionsRight,
	actionsLeft,
	docText,
}) {
	return (
		<>
			<div className="sui-header">
				<h1 className="sui-header-title">{title}</h1>
				{!!actionsLeft && (
					<div className="sui-actions-left">{actionsLeft}</div>
				)}
				{!!actionsRight && (
					<div className="sui-actions-right">
						{actionsRight}

						{showDocLink && (
							<a
								className="sui-button sui-button-ghost"
								href={docLink}
								target="_blank"
							>
								<Icon icon="academy" />
								{docText}
							</a>
						)}
					</div>
				)}
			</div>
		</>
	)
}

Header.defaultProps = {
	title: 'Title',
	actionsRight: true,
	actionsLeft: true,
	showDocLink: true,
	docLink: '#',
	docText: 'Here goes doc text',
}

Header.propTypes = {
	title: PropTypes.string.isRequired,
	showDocLink: PropTypes.bool,
	actionsRight: PropTypes.oneOfType([ PropTypes.bool, PropTypes.string, PropTypes.element]),
	actionsLeft: PropTypes.oneOfType([PropTypes.bool, PropTypes.string, PropTypes.element]),
	docText: PropTypes.string,
	docLink: PropTypes.string,
}

export default Header
